<template>
  <div class="staging-manager">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-flask"></i> Staging Environment</h4>
      </div>

      <div class="card-body">
        <!-- Staging Status -->
        <div v-if="staging" class="staging-info">
          <div class="staging-header">
            <div class="staging-details">
              <h5>
                <a :href="'https://' + staging.subdomain" target="_blank" class="staging-link">
                  {{ staging.subdomain }}
                  <i class="fas fa-external-link-alt"></i>
                </a>
              </h5>
              <span class="badge" :class="staging.status === 'active' ? 'badge-success' : 'badge-secondary'">
                {{ staging.status }}
              </span>
            </div>
            <div class="staging-actions-header">
              <button @click="openStaging" class="btn btn-sm btn-primary">
                <i class="fas fa-external-link-alt"></i> Open
              </button>
              <button @click="confirmDelete" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>

          <div class="staging-stats">
            <div class="stat">
              <label>Created:</label>
              <span>{{ formatDate(staging.created_at) }}</span>
            </div>
            <div class="stat">
              <label>Last Synced:</label>
              <span>{{ formatDate(staging.last_synced_at) }}</span>
            </div>
            <div class="stat">
              <label>Container:</label>
              <code>{{ staging.docker_container_id?.substring(0, 12) }}</code>
            </div>
            <div class="stat">
              <label>Port:</label>
              <span>{{ staging.port }}</span>
            </div>
          </div>

          <!-- Sync Options -->
          <div class="sync-section">
            <h5>Synchronization</h5>
            <p class="text-muted">Sync data between production and staging environments</p>

            <div class="sync-options">
              <div class="form-check">
                <input 
                  type="checkbox" 
                  id="syncFiles"
                  v-model="syncOptions.files" 
                  class="form-check-input"
                />
                <label for="syncFiles" class="form-check-label">
                  Application Files
                </label>
              </div>

              <div class="form-check">
                <input 
                  type="checkbox" 
                  id="syncDatabase"
                  v-model="syncOptions.database" 
                  class="form-check-input"
                />
                <label for="syncDatabase" class="form-check-label">
                  Database
                </label>
              </div>

              <div class="form-check">
                <input 
                  type="checkbox" 
                  id="syncEnv"
                  v-model="syncOptions.env" 
                  class="form-check-input"
                />
                <label for="syncEnv" class="form-check-label">
                  Environment Variables
                </label>
              </div>

              <div class="form-check">
                <input 
                  type="checkbox" 
                  id="syncUploads"
                  v-model="syncOptions.uploads" 
                  class="form-check-input"
                />
                <label for="syncUploads" class="form-check-label">
                  Uploaded Files/Media
                </label>
              </div>
            </div>

            <div class="sync-actions">
              <button @click="syncToStaging" class="btn btn-primary" :disabled="syncing">
                <i class="fas fa-arrow-right" :class="{ 'fa-spin': syncing }"></i>
                {{ syncing ? 'Syncing...' : 'Sync Production → Staging' }}
              </button>
              
              <button @click="confirmPushToProduction" class="btn btn-warning" :disabled="syncing">
                <i class="fas fa-arrow-left"></i>
                Push Staging → Production
              </button>
            </div>
          </div>

          <!-- Comparison View -->
          <div class="comparison-section">
            <h5>Environment Comparison</h5>
            <div class="comparison-grid">
              <div class="comparison-col">
                <h6>Production</h6>
                <div class="comparison-item">
                  <label>PHP Version:</label>
                  <span>{{ productionInfo.phpVersion }}</span>
                </div>
                <div class="comparison-item">
                  <label>Laravel Version:</label>
                  <span>{{ productionInfo.laravelVersion }}</span>
                </div>
                <div class="comparison-item">
                  <label>Database Size:</label>
                  <span>{{ productionInfo.dbSize }} MB</span>
                </div>
                <div class="comparison-item">
                  <label>Files Count:</label>
                  <span>{{ formatNumber(productionInfo.filesCount) }}</span>
                </div>
              </div>

              <div class="comparison-divider">
                <i class="fas fa-exchange-alt"></i>
              </div>

              <div class="comparison-col">
                <h6>Staging</h6>
                <div class="comparison-item">
                  <label>PHP Version:</label>
                  <span>{{ stagingInfo.phpVersion }}</span>
                </div>
                <div class="comparison-item">
                  <label>Laravel Version:</label>
                  <span>{{ stagingInfo.laravelVersion }}</span>
                </div>
                <div class="comparison-item">
                  <label>Database Size:</label>
                  <span>{{ stagingInfo.dbSize }} MB</span>
                </div>
                <div class="comparison-item">
                  <label>Files Count:</label>
                  <span>{{ formatNumber(stagingInfo.filesCount) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- SSH Access -->
          <div class="ssh-section">
            <h5>SSH Access</h5>
            <div class="ssh-command">
              <code>{{ sshCommand }}</code>
              <button @click="copyToClipboard(sshCommand)" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-copy"></i> Copy
              </button>
            </div>
            <button @click="openTerminal" class="btn btn-secondary mt-2">
              <i class="fas fa-terminal"></i> Open Web Terminal
            </button>
          </div>
        </div>

        <!-- Create Staging Form -->
        <div v-else class="create-staging-form">
          <div class="text-center mb-4">
            <i class="fas fa-flask fa-4x text-muted mb-3"></i>
            <p class="text-muted">No staging environment exists for this project.</p>
            <p class="text-muted">Create a staging environment to test changes before deploying to production.</p>
          </div>

          <div class="form-group">
            <label for="subdomain">Staging Subdomain</label>
            <div class="input-group">
              <input 
                type="text" 
                id="subdomain"
                v-model="createForm.subdomain" 
                class="form-control"
                placeholder="staging"
              />
              <span class="input-group-text">.{{ projectDomain }}</span>
            </div>
            <small class="form-text text-muted">This will be your staging URL</small>
          </div>

          <div class="form-check mb-3">
            <input 
              type="checkbox" 
              id="cloneData"
              v-model="createForm.cloneData" 
              class="form-check-input"
            />
            <label for="cloneData" class="form-check-label">
              Clone production data (files + database)
            </label>
          </div>

          <div class="form-check mb-3">
            <input 
              type="checkbox" 
              id="autoSsl"
              v-model="createForm.autoSsl" 
              class="form-check-input"
            />
            <label for="autoSsl" class="form-check-label">
              Generate SSL certificate
            </label>
          </div>

          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Make sure your DNS is configured to point {{ createForm.subdomain }}.{{ projectDomain }} to this server.
          </div>

          <button @click="createStaging" class="btn btn-success" :disabled="creating">
            <i class="fas fa-plus" :class="{ 'fa-spin': creating }"></i>
            {{ creating ? 'Creating...' : 'Create Staging Environment' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Sync Progress Modal -->
    <div v-if="showSyncProgress" class="modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Synchronizing...</h5>
          </div>
          <div class="modal-body">
            <div class="progress mb-3">
              <div 
                class="progress-bar progress-bar-striped progress-bar-animated"
                :style="{ width: syncProgress + '%' }"
              >
                {{ syncProgress }}%
              </div>
            </div>
            <p class="text-center">{{ syncStatus }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StagingManager',

  props: {
    projectId: {
      type: [Number, String],
      required: true
    },
    projectDomain: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      staging: null,
      creating: false,
      syncing: false,
      showSyncProgress: false,
      syncProgress: 0,
      syncStatus: '',
      syncOptions: {
        files: true,
        database: true,
        env: false,
        uploads: true
      },
      createForm: {
        subdomain: 'staging',
        cloneData: true,
        autoSsl: true
      },
      productionInfo: {
        phpVersion: 'N/A',
        laravelVersion: 'N/A',
        dbSize: 0,
        filesCount: 0
      },
      stagingInfo: {
        phpVersion: 'N/A',
        laravelVersion: 'N/A',
        dbSize: 0,
        filesCount: 0
      }
    };
  },

  computed: {
    sshCommand() {
      if (!this.staging) return '';
      return `ssh -p ${this.staging.port} root@${this.staging.subdomain}`;
    }
  },

  mounted() {
    this.loadStaging();
  },

  methods: {
    async loadStaging() {
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/staging`);
        this.staging = response.data.staging;
        this.productionInfo = response.data.production;
        this.stagingInfo = response.data.staging_info;
      } catch (error) {
        if (error.response?.status !== 404) {
          console.error('Failed to load staging:', error);
        }
      }
    },

    async createStaging() {
      if (!this.createForm.subdomain) {
        alert('Please enter a subdomain');
        return;
      }

      this.creating = true;
      try {
        const response = await axios.post(`/api/projects/${this.projectId}/staging`, this.createForm);
        this.staging = response.data;
        this.$emit('created', this.staging);
        alert('Staging environment created successfully!');
      } catch (error) {
        alert('Failed to create staging: ' + (error.response?.data?.message || error.message));
      } finally {
        this.creating = false;
      }
    },

    async syncToStaging() {
      if (!confirm('Sync production data to staging? This will overwrite staging data.')) return;

      this.syncing = true;
      this.showSyncProgress = true;
      this.syncProgress = 0;

      try {
        this.syncStatus = 'Preparing sync...';
        await this.updateSyncProgress(10);

        if (this.syncOptions.database) {
          this.syncStatus = 'Syncing database...';
          await this.updateSyncProgress(40);
        }

        if (this.syncOptions.files) {
          this.syncStatus = 'Syncing application files...';
          await this.updateSyncProgress(70);
        }

        if (this.syncOptions.uploads) {
          this.syncStatus = 'Syncing uploads...';
          await this.updateSyncProgress(90);
        }

        const response = await axios.post(`/api/projects/${this.projectId}/staging/sync`, this.syncOptions);
        
        this.syncProgress = 100;
        this.syncStatus = 'Sync complete!';
        
        setTimeout(() => {
          this.showSyncProgress = false;
          this.staging = response.data;
          this.loadStaging();
        }, 1000);

      } catch (error) {
        alert('Sync failed: ' + (error.response?.data?.message || error.message));
        this.showSyncProgress = false;
      } finally {
        this.syncing = false;
      }
    },

    async confirmPushToProduction() {
      const confirmed = confirm(
        'Push staging to production? This will overwrite production data. Are you absolutely sure?'
      );
      
      if (!confirmed) return;

      const doubleConfirm = prompt(
        'Type "CONFIRM" to push staging to production:'
      );

      if (doubleConfirm !== 'CONFIRM') {
        alert('Operation cancelled');
        return;
      }

      this.pushToProduction();
    },

    async pushToProduction() {
      this.syncing = true;
      this.showSyncProgress = true;
      this.syncProgress = 0;

      try {
        this.syncStatus = 'Pushing to production...';
        await this.updateSyncProgress(30);

        const response = await axios.post(`/api/projects/${this.projectId}/staging/push`, this.syncOptions);
        
        this.syncProgress = 100;
        this.syncStatus = 'Push complete!';
        
        setTimeout(() => {
          this.showSyncProgress = false;
          alert('Staging pushed to production successfully!');
        }, 1000);

      } catch (error) {
        alert('Push failed: ' + (error.response?.data?.message || error.message));
        this.showSyncProgress = false;
      } finally {
        this.syncing = false;
      }
    },

    async confirmDelete() {
      if (!confirm('Delete staging environment? This action cannot be undone.')) return;

      try {
        await axios.delete(`/api/projects/${this.projectId}/staging`);
        this.staging = null;
        this.$emit('deleted');
      } catch (error) {
        alert('Failed to delete staging: ' + (error.response?.data?.message || error.message));
      }
    },

    openStaging() {
      window.open('https://' + this.staging.subdomain, '_blank');
    },

    openTerminal() {
      this.$emit('open-terminal', this.staging.id);
    },

    updateSyncProgress(target) {
      return new Promise(resolve => {
        const interval = setInterval(() => {
          if (this.syncProgress >= target) {
            clearInterval(interval);
            resolve();
          } else {
            this.syncProgress += 1;
          }
        }, 30);
      });
    },

    copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
      });
    },

    formatDate(dateString) {
      if (!dateString) return 'Never';
      return new Date(dateString).toLocaleString();
    },

    formatNumber(num) {
      return new Intl.NumberFormat().format(num);
    }
  }
};
</script>

<style scoped>
.staging-manager {
  margin: 1rem 0;
}

.staging-info {
  padding: 1rem;
}

.staging-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.staging-link {
  color: #2196f3;
  text-decoration: none;
}

.staging-link:hover {
  text-decoration: underline;
}

.staging-actions-header {
  display: flex;
  gap: 0.5rem;
}

.staging-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.stat {
  display: flex;
  flex-direction: column;
}

.stat label {
  font-size: 0.875rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.stat span,
.stat code {
  font-weight: 500;
}

.sync-section,
.comparison-section,
.ssh-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e0e0e0;
}

.sync-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin: 1.5rem 0;
}

.sync-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.comparison-grid {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 2rem;
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
}

.comparison-col h6 {
  margin-bottom: 1rem;
  color: #333;
}

.comparison-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #dee2e6;
}

.comparison-item:last-child {
  border-bottom: none;
}

.comparison-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #2196f3;
}

.ssh-section {
  margin-bottom: 2rem;
}

.ssh-command {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
  border: 1px solid #dee2e6;
}

.ssh-command code {
  flex: 1;
  font-family: 'Courier New', monospace;
}

.create-staging-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
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
  max-width: 500px;
}

.modal-content {
  background: white;
  border-radius: 8px;
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.modal-body {
  padding: 1.5rem;
}

.progress {
  height: 30px;
}
</style>