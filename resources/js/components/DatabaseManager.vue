<template>
  <div class="database-manager">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-database"></i> Database Manager</h4>
      </div>

      <div class="card-body">
        <!-- Database List -->
        <div v-if="databases.length > 0" class="databases-section">
          <div class="section-header">
            <h5>Your Databases</h5>
            <button @click="showCreateForm = true" class="btn btn-sm btn-primary">
              <i class="fas fa-plus"></i> Create New Database
            </button>
          </div>

          <div class="databases-grid">
            <div v-for="db in databases" :key="db.id" class="database-card">
              <div class="db-header">
                <div class="db-info">
                  <h6>{{ db.db_name }}</h6>
                  <span class="badge" :class="db.status === 'active' ? 'badge-success' : 'badge-secondary'">
                    {{ db.status }}
                  </span>
                </div>
                <div class="db-actions">
                  <button 
                    @click="openPhpMyAdmin(db.id)" 
                    class="btn btn-sm btn-info"
                    title="Open phpMyAdmin"
                  >
                    <i class="fas fa-external-link-alt"></i>
                  </button>
                  <button 
                    @click="deleteDatabase(db.id)" 
                    class="btn btn-sm btn-danger"
                    title="Delete Database"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>

              <div class="db-details">
                <div class="detail-item">
                  <label>Type:</label>
                  <span>{{ db.db_type }}</span>
                </div>
                <div class="detail-item">
                  <label>User:</label>
                  <span>{{ db.db_user }}</span>
                </div>
                <div class="detail-item">
                  <label>Port:</label>
                  <span>{{ db.port }}</span>
                </div>
                <div class="detail-item">
                  <label>Size:</label>
                  <span>{{ db.size_mb ? db.size_mb + ' MB' : 'N/A' }}</span>
                </div>
              </div>

              <div class="db-connection">
                <button @click="toggleConnectionInfo(db.id)" class="btn btn-sm btn-link">
                  <i class="fas" :class="showConnection === db.id ? 'fa-eye-slash' : 'fa-eye'"></i>
                  {{ showConnection === db.id ? 'Hide' : 'Show' }} Connection Details
                </button>
              </div>

              <div v-if="showConnection === db.id" class="connection-details">
                <div class="detail-row">
                  <label>Host:</label>
                  <code>{{ db.container_id || 'kbpanel_db' }}</code>
                  <button @click="copyToClipboard(db.container_id || 'kbpanel_db')" class="btn-copy">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
                <div class="detail-row">
                  <label>Database:</label>
                  <code>{{ db.db_name }}</code>
                  <button @click="copyToClipboard(db.db_name)" class="btn-copy">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
                <div class="detail-row">
                  <label>Username:</label>
                  <code>{{ db.db_user }}</code>
                  <button @click="copyToClipboard(db.db_user)" class="btn-copy">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
                <div class="detail-row">
                  <label>Port:</label>
                  <code>{{ db.port }}</code>
                  <button @click="copyToClipboard(String(db.port))" class="btn-copy">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
              </div>

              <div class="db-actions-extended">
                <button @click="exportDatabase(db.id)" class="btn btn-sm btn-secondary">
                  <i class="fas fa-download"></i> Export SQL
                </button>
                <button @click="showImportModal(db.id)" class="btn btn-sm btn-secondary">
                  <i class="fas fa-upload"></i> Import SQL
                </button>
                <button @click="showBackupModal(db.id)" class="btn btn-sm btn-secondary">
                  <i class="fas fa-save"></i> Backup
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- No Databases Message -->
        <div v-else class="no-databases">
          <i class="fas fa-database fa-3x text-muted mb-3"></i>
          <p class="text-muted">No databases found for this project.</p>
          <button @click="showCreateForm = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Your First Database
          </button>
        </div>

        <!-- Create Database Form -->
        <div v-if="showCreateForm" class="create-db-form">
          <h5>Create New Database</h5>
          
          <div class="form-group">
            <label for="dbType">Database Type</label>
            <select v-model="createForm.db_type" id="dbType" class="form-control">
              <option value="mysql">MySQL 8.0</option>
              <option value="mariadb">MariaDB 10.11</option>
            </select>
          </div>

          <div class="form-group">
            <label for="dbName">Database Name</label>
            <input 
              type="text" 
              id="dbName"
              v-model="createForm.db_name" 
              class="form-control"
              placeholder="my_database"
              @input="sanitizeDbName"
            />
            <small class="form-text text-muted">Only alphanumeric characters and underscores allowed</small>
          </div>

          <div class="form-group">
            <label for="dbUser">Database User</label>
            <input 
              type="text" 
              id="dbUser"
              v-model="createForm.db_user" 
              class="form-control"
              placeholder="db_user"
            />
          </div>

          <div class="form-group">
            <label for="dbPassword">Database Password</label>
            <div class="input-group">
              <input 
                :type="showPassword ? 'text' : 'password'"
                id="dbPassword"
                v-model="createForm.db_password" 
                class="form-control"
              />
              <button 
                @click="showPassword = !showPassword" 
                class="btn btn-outline-secondary"
                type="button"
              >
                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
              <button @click="generatePassword" class="btn btn-outline-primary" type="button">
                <i class="fas fa-random"></i> Generate
              </button>
            </div>
          </div>

          <div class="form-actions">
            <button @click="createDatabase" class="btn btn-success" :disabled="creating">
              <i class="fas fa-plus" :class="{ 'fa-spin': creating }"></i>
              {{ creating ? 'Creating...' : 'Create Database' }}
            </button>
            <button @click="showCreateForm = false" class="btn btn-secondary">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showImport" class="modal" @click.self="showImport = false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Import SQL File</h5>
            <button @click="showImport = false" class="close">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="sqlFile">Select SQL File</label>
              <input 
                type="file" 
                id="sqlFile"
                ref="sqlFile"
                accept=".sql,.sql.gz"
                class="form-control"
              />
            </div>
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle"></i>
              Warning: This will execute the SQL statements in the selected file. Make sure you trust the source.
            </div>
          </div>
          <div class="modal-footer">
            <button @click="importDatabase" class="btn btn-primary" :disabled="importing">
              <i class="fas fa-upload" :class="{ 'fa-spin': importing }"></i>
              {{ importing ? 'Importing...' : 'Import' }}
            </button>
            <button @click="showImport = false" class="btn btn-secondary">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DatabaseManager',

  props: {
    projectId: {
      type: [Number, String],
      required: true
    }
  },

  data() {
    return {
      databases: [],
      showCreateForm: false,
      showConnection: null,
      showPassword: false,
      creating: false,
      importing: false,
      showImport: false,
      currentDbId: null,
      createForm: {
        db_type: 'mysql',
        db_name: '',
        db_user: '',
        db_password: ''
      }
    };
  },

  mounted() {
    this.loadDatabases();
  },

  methods: {
    async loadDatabases() {
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/databases`);
        this.databases = response.data;
      } catch (error) {
        console.error('Failed to load databases:', error);
      }
    },

    async createDatabase() {
      if (!this.createForm.db_name || !this.createForm.db_user || !this.createForm.db_password) {
        alert('Please fill in all required fields');
        return;
      }

      this.creating = true;
      try {
        const response = await axios.post(`/api/projects/${this.projectId}/databases`, this.createForm);
        this.databases.push(response.data);
        this.showCreateForm = false;
        this.resetCreateForm();
        this.$emit('created', response.data);
      } catch (error) {
        alert('Failed to create database: ' + (error.response?.data?.message || error.message));
      } finally {
        this.creating = false;
      }
    },

    async deleteDatabase(dbId) {
      if (!confirm('Are you sure you want to delete this database? This action cannot be undone!')) return;

      try {
        await axios.delete(`/api/projects/${this.projectId}/databases/${dbId}`);
        this.databases = this.databases.filter(db => db.id !== dbId);
        this.$emit('deleted', dbId);
      } catch (error) {
        alert('Failed to delete database: ' + (error.response?.data?.message || error.message));
      }
    },

    async exportDatabase(dbId) {
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/databases/${dbId}/export`, {
          responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `database_${dbId}_${Date.now()}.sql`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        alert('Failed to export database: ' + (error.response?.data?.message || error.message));
      }
    },

    showImportModal(dbId) {
      this.currentDbId = dbId;
      this.showImport = true;
    },

    async importDatabase() {
      const file = this.$refs.sqlFile.files[0];
      if (!file) {
        alert('Please select a SQL file');
        return;
      }

      this.importing = true;
      const formData = new FormData();
      formData.append('sql_file', file);

      try {
        await axios.post(
          `/api/projects/${this.projectId}/databases/${this.currentDbId}/import`,
          formData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        alert('Database imported successfully');
        this.showImport = false;
      } catch (error) {
        alert('Failed to import database: ' + (error.response?.data?.message || error.message));
      } finally {
        this.importing = false;
      }
    },

    openPhpMyAdmin(dbId) {
      const db = this.databases.find(d => d.id === dbId);
      if (!db) return;

      // Open phpMyAdmin in new window with authentication token
      window.open(`/phpmyadmin?db=${db.db_name}&token=${db.access_token}`, '_blank');
    },

    toggleConnectionInfo(dbId) {
      this.showConnection = this.showConnection === dbId ? null : dbId;
    },

    copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        // Show temporary notification
        alert('Copied to clipboard!');
      });
    },

    generatePassword() {
      const length = 16;
      const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
      let password = '';
      for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
      }
      this.createForm.db_password = password;
    },

    sanitizeDbName() {
      this.createForm.db_name = this.createForm.db_name.replace(/[^a-zA-Z0-9_]/g, '');
    },

    resetCreateForm() {
      this.createForm = {
        db_type: 'mysql',
        db_name: '',
        db_user: '',
        db_password: ''
      };
    },

    showBackupModal(dbId) {
      alert('Backup functionality coming soon!');
    }
  }
};
</script>

<style scoped>
.database-manager {
  margin: 1rem 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.databases-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

.database-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1.5rem;
  background: #f8f9fa;
}

.db-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.db-info h6 {
  margin: 0 0 0.5rem 0;
}

.db-actions {
  display: flex;
  gap: 0.5rem;
}

.db-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-item label {
  font-size: 0.875rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.detail-item span {
  font-weight: 500;
}

.connection-details {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 1rem;
  margin: 1rem 0;
}

.detail-row {
  display: flex;
  align-items: center;
  margin-bottom: 0.75rem;
}

.detail-row label {
  width: 100px;
  font-weight: 500;
  margin-bottom: 0;
}

.detail-row code {
  flex: 1;
  background: #f8f9fa;
  padding: 0.25rem 0.5rem;
  border-radius: 3px;
}

.btn-copy {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  margin-left: 0.5rem;
}

.db-actions-extended {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
}

.no-databases {
  text-align: center;
  padding: 3rem;
}

.create-db-form {
  margin-top: 2rem;
  padding: 1.5rem;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  background: #f8f9fa;
}

.form-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
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
  padding: 1rem;
}

.close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}
</style>