<template>
  <div class="resource-monitor">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-chart-line"></i> Resource Monitor</h4>
        <div class="monitor-controls">
          <select v-model="timeRange" class="form-control form-control-sm">
            <option value="1h">Last Hour</option>
            <option value="6h">Last 6 Hours</option>
            <option value="24h" selected>Last 24 Hours</option>
            <option value="7d">Last 7 Days</option>
          </select>
          <button @click="refreshData" class="btn btn-sm btn-primary" :disabled="loading">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Current Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card cpu">
            <div class="stat-icon">
              <i class="fas fa-microchip"></i>
            </div>
            <div class="stat-content">
              <label>CPU Usage</label>
              <div class="stat-value">{{ currentStats.cpu }}%</div>
              <div class="stat-bar">
                <div class="stat-fill" :style="{ width: currentStats.cpu + '%', background: getColor(currentStats.cpu) }"></div>
              </div>
            </div>
          </div>

          <div class="stat-card memory">
            <div class="stat-icon">
              <i class="fas fa-memory"></i>
            </div>
            <div class="stat-content">
              <label>Memory Usage</label>
              <div class="stat-value">{{ currentStats.memory }} MB</div>
              <div class="stat-bar">
                <div class="stat-fill" :style="{ width: memoryPercent + '%', background: getColor(memoryPercent) }"></div>
              </div>
              <small class="text-muted">{{ memoryPercent }}% of {{ maxMemory }} MB</small>
            </div>
          </div>

          <div class="stat-card disk">
            <div class="stat-icon">
              <i class="fas fa-hdd"></i>
            </div>
            <div class="stat-content">
              <label>Disk Usage</label>
              <div class="stat-value">{{ currentStats.disk }} MB</div>
              <div class="stat-bar">
                <div class="stat-fill" :style="{ width: diskPercent + '%', background: getColor(diskPercent) }"></div>
              </div>
              <small class="text-muted">{{ diskPercent }}% of {{ diskQuota }} MB</small>
            </div>
          </div>

          <div class="stat-card bandwidth">
            <div class="stat-icon">
              <i class="fas fa-network-wired"></i>
            </div>
            <div class="stat-content">
              <label>Bandwidth</label>
              <div class="stat-value">{{ formatBytes(currentStats.bandwidth) }}</div>
              <div class="stat-detail">
                <span><i class="fas fa-arrow-down"></i> {{ formatBytes(currentStats.bandwidthIn) }}</span>
                <span><i class="fas fa-arrow-up"></i> {{ formatBytes(currentStats.bandwidthOut) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Historical Charts -->
        <div class="charts-section">
          <div class="chart-container">
            <h5>CPU Usage Over Time</h5>
            <canvas ref="cpuChart"></canvas>
          </div>

          <div class="chart-container">
            <h5>Memory Usage Over Time</h5>
            <canvas ref="memoryChart"></canvas>
          </div>
        </div>

        <!-- Request Statistics -->
        <div class="requests-section">
          <h5>Request Statistics</h5>
          <div class="requests-grid">
            <div class="request-stat">
              <label>Total Requests</label>
              <div class="value">{{ formatNumber(requestStats.total) }}</div>
            </div>
            <div class="request-stat">
              <label>Success Rate</label>
              <div class="value success">{{ requestStats.successRate }}%</div>
            </div>
            <div class="request-stat">
              <label>Avg Response Time</label>
              <div class="value">{{ requestStats.avgResponseTime }} ms</div>
            </div>
            <div class="request-stat">
              <label>Error Rate</label>
              <div class="value" :class="requestStats.errorRate > 5 ? 'error' : 'warning'">
                {{ requestStats.errorRate }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Alerts -->
        <div v-if="alerts.length > 0" class="alerts-section">
          <h5>Resource Alerts</h5>
          <div v-for="alert in alerts" :key="alert.id" class="alert" :class="'alert-' + alert.type">
            <i class="fas" :class="getAlertIcon(alert.type)"></i>
            <span>{{ alert.message }}</span>
            <button @click="dismissAlert(alert.id)" class="btn-close"><i class="fas fa-times"></i></button>
          </div>
        </div>

        <!-- Uptime Status -->
        <div class="uptime-section">
          <h5>Uptime Status</h5>
          <div class="uptime-info">
            <div class="uptime-stat">
              <label>Current Uptime</label>
              <div class="value">{{ formatUptime(uptime.current) }}</div>
            </div>
            <div class="uptime-stat">
              <label>30-Day Uptime</label>
              <div class="value">{{ uptime.thirtyDay }}%</div>
            </div>
            <div class="uptime-stat">
              <label>Last Restart</label>
              <div class="value">{{ formatDate(uptime.lastRestart) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';

export default {
  name: 'ResourceMonitor',

  props: {
    projectId: {
      type: [Number, String],
      required: true
    }
  },

  data() {
    return {
      loading: false,
      timeRange: '24h',
      maxMemory: 2048,
      diskQuota: 10240,
      currentStats: {
        cpu: 0,
        memory: 0,
        disk: 0,
        bandwidth: 0,
        bandwidthIn: 0,
        bandwidthOut: 0
      },
      requestStats: {
        total: 0,
        successRate: 0,
        avgResponseTime: 0,
        errorRate: 0
      },
      uptime: {
        current: 0,
        thirtyDay: 99.9,
        lastRestart: null
      },
      historicalData: {
        timestamps: [],
        cpu: [],
        memory: []
      },
      alerts: [],
      cpuChart: null,
      memoryChart: null,
      refreshInterval: null
    };
  },

  computed: {
    memoryPercent() {
      return Math.round((this.currentStats.memory / this.maxMemory) * 100);
    },
    diskPercent() {
      return Math.round((this.currentStats.disk / this.diskQuota) * 100);
    }
  },

  mounted() {
    this.loadResourceData();
    this.initCharts();
    this.startAutoRefresh();
  },

  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
    if (this.cpuChart) this.cpuChart.destroy();
    if (this.memoryChart) this.memoryChart.destroy();
  },

  watch: {
    timeRange() {
      this.loadResourceData();
    }
  },

  methods: {
    async loadResourceData() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/resources`, {
          params: { range: this.timeRange }
        });

        this.currentStats = response.data.current;
        this.requestStats = response.data.requests;
        this.uptime = response.data.uptime;
        this.historicalData = response.data.historical;
        this.alerts = response.data.alerts || [];
        this.maxMemory = response.data.limits.memory || 2048;
        this.diskQuota = response.data.limits.disk || 10240;

        this.updateCharts();
      } catch (error) {
        console.error('Failed to load resource data:', error);
      } finally {
        this.loading = false;
      }
    },

    refreshData() {
      this.loadResourceData();
    },

    startAutoRefresh() {
      this.refreshInterval = setInterval(() => {
        this.loadResourceData();
      }, 30000); // Refresh every 30 seconds
    },

    initCharts() {
      const chartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 3,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100
          }
        }
      };

      // CPU Chart
      this.cpuChart = new Chart(this.$refs.cpuChart, {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
            label: 'CPU %',
            data: [],
            borderColor: '#2196f3',
            backgroundColor: 'rgba(33, 150, 243, 0.1)',
            fill: true,
            tension: 0.4
          }]
        },
        options: chartOptions
      });

      // Memory Chart
      this.memoryChart = new Chart(this.$refs.memoryChart, {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
            label: 'Memory %',
            data: [],
            borderColor: '#4caf50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          ...chartOptions,
          scales: {
            y: {
              beginAtZero: true,
              max: this.maxMemory
            }
          }
        }
      });
    },

    updateCharts() {
      if (this.cpuChart && this.historicalData.timestamps) {
        this.cpuChart.data.labels = this.historicalData.timestamps;
        this.cpuChart.data.datasets[0].data = this.historicalData.cpu;
        this.cpuChart.update();
      }

      if (this.memoryChart && this.historicalData.timestamps) {
        this.memoryChart.data.labels = this.historicalData.timestamps;
        this.memoryChart.data.datasets[0].data = this.historicalData.memory;
        this.memoryChart.update();
      }
    },

    getColor(percent) {
      if (percent < 70) return '#4caf50';
      if (percent < 85) return '#ff9800';
      return '#f44336';
    },

    getAlertIcon(type) {
      switch (type) {
        case 'danger': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        case 'info': return 'fa-info-circle';
        default: return 'fa-bell';
      }
    },

    dismissAlert(alertId) {
      this.alerts = this.alerts.filter(a => a.id !== alertId);
    },

    formatBytes(bytes) {
      if (bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    formatNumber(num) {
      return new Intl.NumberFormat().format(num);
    },

    formatUptime(seconds) {
      const days = Math.floor(seconds / 86400);
      const hours = Math.floor((seconds % 86400) / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      
      if (days > 0) return `${days}d ${hours}h ${minutes}m`;
      if (hours > 0) return `${hours}h ${minutes}m`;
      return `${minutes}m`;
    },

    formatDate(dateString) {
      if (!dateString) return 'Never';
      return new Date(dateString).toLocaleString();
    }
  }
};
</script>

<style scoped>
.resource-monitor {
  margin: 1rem 0;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.monitor-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-card.cpu .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.memory .stat-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-card.disk .stat-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-card.bandwidth .stat-icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

.stat-content {
  flex: 1;
}

.stat-content label {
  display: block;
  font-size: 0.875rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 0.5rem;
}

.stat-bar {
  height: 8px;
  background: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.stat-fill {
  height: 100%;
  transition: width 0.3s ease;
}

.stat-detail {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  color: #666;
}

.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.chart-container {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1.5rem;
}

.chart-container h5 {
  margin: 0 0 1rem 0;
}

.requests-section,
.uptime-section {
  margin-bottom: 2rem;
}

.requests-section h5,
.uptime-section h5 {
  margin-bottom: 1rem;
}

.requests-grid,
.uptime-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.request-stat,
.uptime-stat {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
}

.request-stat label,
.uptime-stat label {
  display: block;
  font-size: 0.875rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.request-stat .value,
.uptime-stat .value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
}

.value.success { color: #4caf50; }
.value.warning { color: #ff9800; }
.value.error { color: #f44336; }

.alerts-section {
  margin-bottom: 2rem;
}

.alerts-section h5 {
  margin-bottom: 1rem;
}

.alert {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.alert-danger {
  background: #ffebee;
  color: #c62828;
  border-left: 4px solid #f44336;
}

.alert-warning {
  background: #fff3e0;
  color: #e65100;
  border-left: 4px solid #ff9800;
}

.alert-info {
  background: #e3f2fd;
  color: #1565c0;
  border-left: 4px solid #2196f3;
}

.alert span {
  flex: 1;
}

.btn-close {
  background: none;
  border: none;
  cursor: pointer;
  opacity: 0.6;
}

.btn-close:hover {
  opacity: 1;
}
</style>