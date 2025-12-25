<template>
    <div class="dashboard">
        <h1>KBPanel Dashboard</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Active Projects</h3>
                <p class="stat-number">{{ stats.activeProjects }}</p>
            </div>
            <div class="stat-card">
                <h3>Total Deployments</h3>
                <p class="stat-number">{{ stats.totalDeployments }}</p>
            </div>
            <div class="stat-card">
                <h3>Disk Usage</h3>
                <p class="stat-number">{{ stats.diskUsage }}GB</p>
            </div>
            <div class="stat-card">
                <h3>Memory Usage</h3>
                <p class="stat-number">{{ stats.memoryUsage }}%</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Dashboard',
    data() {
        return {
            stats: {
                activeProjects: 0,
                totalDeployments: 0,
                diskUsage: 0,
                memoryUsage: 0
            }
        }
    },
    mounted() {
        this.loadStats();
    },
    methods: {
        loadStats() {
            // TODO: Load from API
            axios.get('/api/dashboard/stats')
                .then(response => {
                    this.stats = response.data;
                })
                .catch(error => {
                    console.error('Failed to load stats:', error);
                });
        }
    }
}
</script>

<style scoped>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #2563eb;
}
</style>
