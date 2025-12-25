<template>
  <div class="backup-manager">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-save"></i> Backup Manager</h4>
        <button @click="showCreateModal = true" class="btn btn-primary">
          <i class="fas fa-plus"></i> Create Backup
        </button>
      </div>

      <div class="card-body">
        <!-- Backup Statistics -->
        <div class="backup-stats">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-database"></i></div>
            <div class="stat-content">
              <label>Total Backups</label>
              <div class="value">{{ backups.length }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-hdd"></i></div>
            <div class="stat-content">
              <label>Total Size</label>
              <div class="value">{{ formatBytes(totalSize) }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
              <label>Last Backup</label>
              <div class="value">{{ lastBackupDate }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
              <label>Successful</label>
              <div class="value">{{ successfulBackups }}</div>
            </div>
          </div>
        </div>

        <!-- Backup List -->
        <div v-if="backups.length > 0" class="backups-section">
          <div class="section-header">
            <h5>Backups</h5>
            <div class="filters">
              <select v-model="filterType" class="form-control form-control-sm">
                <option value="all">All Types</option>
                <option value="full">Full Backup</option>
                <option value="database">Database Only</option>
                <option value="files">Files Only</option>
              </select>
            </div>
          </div>

          <div class="backups-table">
            <table class="table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Size</th>
                  <th>Created</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="backup in filteredBackups" :key="backup.id">
                  <td>
                    <i class="fas" :class="getBackupIcon(backup.backup_type)"></i>
                    {{ backup.name || getBackupName(backup) }}
                  </td>
                  <td>
                    <span class="badge" :class="getTypeClass(backup.backup_type)">
                      {{ backup.backup_type }}
                    </span>
                  </td>
                  <td>{{ formatBytes(backup.size_mb * 1024 * 1024) }}</td>
                  <td>{{ formatDate(backup.created_at) }}</td>
                  <td>
                    <span class="badge" :class="getStatusClass(backup.status)">
                      {{ backup.status }}
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button 
                        @click="downloadBackup(backup.id)" 
                        class="btn btn-sm btn-primary"
                        title="Download"
                        :disabled="backup.status !== 'completed'"
                      >
                        <i class="fas fa-download"></i>
                      </button>
                      <button 
                        @click="confirmRestore(backup)" 
                        class="btn btn-sm btn-warning"
                        title="Restore"
                        :disabled="backup.status !== 'completed'"
                      >
                        <i class="fas fa-undo"></i>
                      </button>
                      <button 
                        @click="deleteBackup(backup.id)" 
                        class="btn btn-sm btn-danger"
                        title="Delete"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- No Backups Message -->
        <div v-else class="no-backups">
          <i class="fas fa-save fa-4x text-muted mb-3"></i>
          <p class="text-muted">No backups found for this project.</p>
          <button @click="showCreateModal = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Your First Backup
          </button>
        </div>

        <!-- Automatic Backup Schedule -->
        <div class="schedule-section">
          <h5>Automatic Backup Schedule</h5>
          <div class="form-check mb-2">
            <input 
              type="checkbox" 
              id="enableAutoBackup"
              v-model="autoBackup.enabled" 
              class="form-check-input"
              @change="updateAutoBackup"
            />
            <label for="enableAutoBackup" class="form-check-label">
              Enable automatic backups
            </label>
          </div>

          <div v-if="autoBackup.enabled" class="auto-backup-settings">
            <div class="form-group">
              <label>Frequency</label>
              <select v-model="autoBackup.frequency" class="form-control" @change="updateAutoBackup">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>

            <div class="form-group">
              <label>Backup Type</label>
              <select v-model="autoBackup.type" class="form-control" @change="updateAutoBackup">
                <option value="full">Full Backup</option>
                <option value="database">Database Only</option>
                <option value="files">Files Only</option>
              </select>
            </div>

            <div class="form-group">
              <label>Retention (days)</label>
              <input 
                type="number" 
                v-model="autoBackup.retention" 
                class="form-control"
                min="1"
                max="90"
                @change="updateAutoBackup"
              />
              <small class="form-text text-muted">Backups older than this will be automatically deleted</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Backup Modal -->
    <div v-if="showCreateModal" class="modal" @click.self="showCreateModal = false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Create Backup</h5>
            <button @click="showCreateModal = false" class="close">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="backupName">Backup Name (optional)</label>
              <input 
                type="text" 
                id="backupName"
                v-model="createForm.name" 
                class="form-control"
                placeholder="e.g., Before major update"
              />
            </div>

            <div class="form-group">
              <label>Backup Type</label>
              <div class="backup-type-options">
                <div 
                  class="backup-type-card"
                  :class="{ selected: createForm.type === 'full' }"
                  @click="createForm.type = 'full'"
                >
                  <i class="fas fa-database fa-2x"></i>
                  <h6>Full Backup</h6>
                  <p>Database + Files</p>
                </div>

                <div 
                  class="backup-type-card"
                  :class="{ selected: createForm.type === 'database' }"
                  @click="createForm.type = 'database'"
                >
                  <i class="fas fa-database fa-2x"></i>
                  <h6>Database Only</h6>
                  <p>SQL Export</p>
                </div>

                <div 
                  class="backup-type-card"
                  :class="{ selected: createForm.type === 'files' }"
                  @click="createForm.type = 'files'"
                >
                  <i class="fas fa-folder fa-2x"></i>
                  <h6>Files Only</h6>
                  <p>Application Files</p>
                </div>
              </div>
            </div>

            <div v-if="creating" class="backup-progress">
              <div class="progress">
                <div 
                  class="progress-bar progress-bar-striped progress-bar-animated"
                  :style="{ width: createProgress + '%' }"
                >
                  {{ createProgress }}%
                </div>
              </div>
              <p class="text-center mt-2">{{ createStatus }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="createBackup" class="btn btn-primary" :disabled="creating">
              <i class="fas fa-save" :class="{ 'fa-spin': creating }"></i>
              {{ creating ? 'Creating...' : 'Create Backup' }}
            </button>
            <button @click="showCreateModal = false" class="btn btn-secondary" :disabled="creating">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div v-if="showRestoreModal" class="modal" @click.self="showRestoreModal = false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Restore Backup</h5>
            <button @click="showRestoreModal = false" class="close">&times;</button>
          </div>
          <div class="modal-body">
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle"></i>
              <strong>Warning!</strong> This will overwrite your current data with the backup.
            </div>
            <p>Are you sure you want to restore this backup?</p>
            <p><strong>Backup:</strong> {{ restoreBackup?.name || getBackupName(restoreBackup) }}</p>
            <p><strong>Type:</strong> {{ restoreBackup?.backup_type }}</p>
            <p><strong>Created:</strong> {{ formatDate(restoreBackup?.created_at) }}</p>

            <div v-if="restoring" class="restore-progress">
              <div class="progress">
                <div 
                  class="progress-bar progress-bar-striped progress-bar-animated"
                  :style="{ width: restoreProgress + '%' }"
                >
                  {{ restoreProgress }}%
                </div>
              </div>
              <p class="text-center mt-2">{{ restoreStatus }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="restoreBackupConfirmed" class="btn btn-danger" :disabled="restoring">
              <i class="fas fa-undo" :class="{ 'fa-spin': restoring }"></i>
              {{ restoring ? 'Restoring...' : 'Yes, Restore Backup' }}
            </button>
            <button @click="showRestoreModal = false" class="btn btn-secondary" :disabled="restoring">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BackupManager',

  props: {
    projectId: {
      type: [Number, String],
      required: true
    }
  },

  data() {
    return {
      backups: [],
      showCreateModal: false,
      showRestoreModal: false,
      restoreBackup: null,
      creating: false,
      restoring: false,
      createProgress: 0,
      restoreProgress: 0,
      createStatus: '',
      restoreStatus: '',
      filterType: 'all',
      autoBackup: {
        enabled: false,
        frequency: 'daily',
        type: 'full',
        retention: 30
      },
      createForm: {
        name: '',
        type: 'full'
      }
    };
  },

  computed: {
    filteredBackups() {
      if (this.filterType === 'all') return this.backups;
      return this.backups.filter(b => b.backup_type === this.filterType);
    },
    totalSize() {
      return this.backups.reduce((sum, b) => sum + (b.size_mb * 1024 * 1024), 0);
    },
    lastBackupDate() {
      if (this.backups.length === 0) return 'Never';
      const latest = this.backups[0];
      return this.formatDate(latest.created_at);
    },
    successfulBackups() {
      return this.backups.filter(b => b.status === 'completed').length;
    }
  },

  mounted() {
    this.loadBackups();
    this.loadAutoBackupSettings();
  },

  methods: {
    async loadBackups() {
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/backups`);
        this.backups = response.data;
      } catch (error) {
        console.error('Failed to load backups:', error);
      }
    },

    async loadAutoBackupSettings() {
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/backups/auto`);
        this.autoBackup = response.data;
      } catch (error) {
        console.error('Failed to load auto backup settings:', error);
      }
    },

    async createBackup() {
      this.creating = true;
      this.createProgress = 0;

      try {
        this.createStatus = 'Preparing backup...';
        await this.updateCreateProgress(20);

        this.createStatus = 'Creating backup...';
        const response = await axios.post(`/api/projects/${this.projectId}/backups`, this.createForm);
        
        this.createProgress = 100;
        this.createStatus = 'Backup created!';

        setTimeout(() => {
          this.backups.unshift(response.data);
          this.showCreateModal = false;
          this.createForm = { name: '', type: 'full' };
          this.$emit('created', response.data);
        }, 1000);

      } catch (error) {
        alert('Failed to create backup: ' + (error.response?.data?.message || error.message));
      } finally {
        this.creating = false;
      }
    },

    confirmRestore(backup) {
      this.restoreBackup = backup;
      this.showRestoreModal = true;
    },

    async restoreBackupConfirmed() {
      this.restoring = true;
      this.restoreProgress = 0;

      try {
        this.restoreStatus = 'Restoring backup...';
        await this.updateRestoreProgress(50);

        await axios.post(`/api/projects/${this.projectId}/backups/${this.restoreBackup.id}/restore`);
        
        this.restoreProgress = 100;
        this.restoreStatus = 'Restore complete!';

        setTimeout(() => {
          this.showRestoreModal = false;
          alert('Backup restored successfully!');
          this.$emit('restored', this.restoreBackup);
        }, 1000);

      } catch (error) {
        alert('Failed to restore backup: ' + (error.response?.data?.message || error.message));
      } finally {
        this.restoring = false;
      }
    },

    async downloadBackup(backupId) {
      try {
        const response = await axios.get(
          `/api/projects/${this.projectId}/backups/${backupId}/download`,
          { responseType: 'blob' }
        );

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `backup_${backupId}_${Date.now()}.tar.gz`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        alert('Failed to download backup: ' + (error.response?.data?.message || error.message));
      }
    },

    async deleteBackup(backupId) {
      if (!confirm('Delete this backup? This action cannot be undone.')) return;

      try {
        await axios.delete(`/api/projects/${this.projectId}/backups/${backupId}`);
        this.backups = this.backups.filter(b => b.id !== backupId);
        this.$emit('deleted', backupId);
      } catch (error) {
        alert('Failed to delete backup: ' + (error.response?.data?.message || error.message));
      }
    },

    async updateAutoBackup() {
      try {
        await axios.put(`/api/projects/${this.projectId}/backups/auto`, this.autoBackup);
      } catch (error) {
        alert('Failed to update auto backup settings: ' + (error.response?.data?.message || error.message));
      }
    },

    updateCreateProgress(target) {
      return new Promise(resolve => {
        const interval = setInterval(() => {
          if (this.createProgress >= target) {
            clearInterval(interval);
            resolve();
          } else {
            this.createProgress += 2;
          }
        }, 50);
      });
    },

    updateRestoreProgress(target) {
      return new Promise(resolve => {
        const interval = setInterval(() => {
          if (this.restoreProgress >= target) {
            clearInterval(interval);
            resolve();
          } else {
            this.restoreProgress += 2;
          }
        }, 50);
      });
    },

    getBackupIcon(type) {
      switch (type) {
        case 'full': return 'fa-database';
        case 'database': return 'fa-database';
        case 'files': return 'fa-folder';
        default: return 'fa-save';
      }
    },

    getTypeClass(type) {
      switch (type) {
        case 'full': return 'badge-primary';
        case 'database': return 'badge-info';
        case 'files': return 'badge-warning';
        default: return 'badge-secondary';
      }
    },

    getStatusClass(status) {
      switch (status) {
        case 'completed': return 'badge-success';
        case 'failed': return 'badge-danger';
        case 'in_progress': return 'badge-warning';
        default: return 'badge-secondary';
      }
    },

    getBackupName(backup) {
      const date = new Date(backup.created_at);
      return `${backup.backup_type}_${date.toISOString().split('T')[0]}`;
    },

    formatBytes(bytes) {
      if (bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleString();
    }
  }
};
</script>

<style scoped>
.backup-manager {
  margin: 1rem 0;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.backup-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: center;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  background: #2196f3;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-content {
  flex: 1;
}

.stat-content label {
  display: block;
  font-size: 0.875rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.stat-content .value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.backups-table {
  overflow-x: auto;
}

.table {
  width: 100%;
  margin-bottom: 0;
}

.action-buttons {
  display: flex;
  gap: 0.25rem;
}

.no-backups {
  text-align: center;
  padding: 3rem;
}

.schedule-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e0e0e0;
}

.auto-backup-settings {
  max-width: 600px;
  margin-top: 1rem;
}

.backup-type-options {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.backup-type-card {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.backup-type-card:hover {
  border-color: #2196f3;
  transform: translateY(-2px);
}

.backup-type-card.selected {
  border-color: #2196f3;
  background: #e3f2fd;
}

.backup-type-card i {
  color: #2196f3;
  margin-bottom: 0.5rem;
}

.backup-type-card h6 {
  margin: 0.5rem 0;
}

.backup-type-card p {
  margin: 0;
  font-size: 0.875rem;
  color: #666;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-dialog {
  width: 90%;
  max-width: 600px;
}

.modal-content {
  background: white;
  border-radius: 8px;
}

.modal-header,
.modal-footer {
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1.5rem;
}

.close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.progress {
  height: 30px;
  margin-bottom: 1rem;
}
</style>