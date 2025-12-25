<template>
  <div class="git-integration">
    <div class="card">
      <div class="card-header">
        <h4><i class="fab fa-git-alt"></i> Git Integration</h4>
      </div>

      <div class="card-body">
        <!-- Repository Connection Status -->
        <div v-if="repository" class="repository-info">
          <div class="repo-header">
            <div class="repo-details">
              <h5><i class="fab fa-github"></i> {{ repository.url }}</h5>
              <p class="text-muted">Branch: <strong>{{ repository.branch }}</strong></p>
            </div>
            <button @click="disconnectRepo" class="btn btn-sm btn-danger">
              <i class="fas fa-unlink"></i> Disconnect
            </button>
          </div>

          <div class="repo-stats">
            <div class="stat">
              <label>Last Commit:</label>
              <span>{{ repository.last_commit_hash?.substring(0, 7) || 'N/A' }}</span>
            </div>
            <div class="stat">
              <label>Last Pulled:</label>
              <span>{{ formatDate(repository.last_pulled_at) }}</span>
            </div>
            <div class="stat">
              <label>Auto Deploy:</label>
              <span>
                <span class="badge" :class="repository.auto_deploy ? 'badge-success' : 'badge-secondary'">
                  {{ repository.auto_deploy ? 'Enabled' : 'Disabled' }}
                </span>
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="git-actions">
            <button @click="pullChanges" class="btn btn-primary" :disabled="pulling">
              <i class="fas fa-download" :class="{ 'fa-spin': pulling }"></i>
              {{ pulling ? 'Pulling...' : 'Pull Latest Changes' }}
            </button>
            <button @click="showCommitHistory" class="btn btn-secondary">
              <i class="fas fa-history"></i> Commit History
            </button>
            <button @click="showBranches" class="btn btn-secondary">
              <i class="fas fa-code-branch"></i> Branches
            </button>
            <button @click="toggleAutoDeploy" class="btn btn-info">
              <i class="fas fa-sync-alt"></i>
              {{ repository.auto_deploy ? 'Disable' : 'Enable' }} Auto-Deploy
            </button>
          </div>

          <!-- Commit History -->
          <div v-if="showHistory" class="commit-history">
            <h5>Recent Commits</h5>
            <div v-if="loadingCommits" class="text-center">
              <i class="fas fa-spinner fa-spin"></i> Loading commits...
            </div>
            <div v-else class="commits-list">
              <div v-for="commit in commits" :key="commit.sha" class="commit-item">
                <div class="commit-header">
                  <code>{{ commit.sha.substring(0, 7) }}</code>
                  <span class="commit-author">{{ commit.author }}</span>
                  <span class="commit-date">{{ formatDate(commit.date) }}</span>
                </div>
                <p class="commit-message">{{ commit.message }}</p>
                <div class="commit-actions">
                  <button @click="viewDiff(commit.sha)" class="btn btn-sm btn-link">
                    <i class="fas fa-code"></i> View Changes
                  </button>
                  <button @click="deployCommit(commit.sha)" class="btn btn-sm btn-link">
                    <i class="fas fa-rocket"></i> Deploy This Version
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Branches List -->
          <div v-if="showBranchesList" class="branches-list">
            <h5>Branches</h5>
            <div v-if="loadingBranches" class="text-center">
              <i class="fas fa-spinner fa-spin"></i> Loading branches...
            </div>
            <div v-else>
              <div v-for="branch in branches" :key="branch.name" class="branch-item">
                <span class="branch-name">
                  <i class="fas fa-code-branch"></i> {{ branch.name }}
                </span>
                <span v-if="branch.name === repository.branch" class="badge badge-primary">Current</span>
                <button 
                  v-else
                  @click="switchBranch(branch.name)" 
                  class="btn btn-sm btn-primary"
                >
                  Switch
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Connect Repository Form -->
        <div v-else class="connect-repo-form">
          <p class="text-muted">Connect your Git repository to enable automated deployments and version control.</p>
          
          <div class="form-group">
            <label for="repoUrl">Repository URL</label>
            <input 
              type="text" 
              id="repoUrl"
              v-model="form.url" 
              class="form-control"
              placeholder="https://github.com/username/repo.git"
            />
          </div>

          <div class="form-group">
            <label for="repoBranch">Branch</label>
            <input 
              type="text" 
              id="repoBranch"
              v-model="form.branch" 
              class="form-control"
              placeholder="main"
            />
          </div>

          <div class="form-group">
            <label for="accessToken">Access Token (optional)</label>
            <input 
              type="password" 
              id="accessToken"
              v-model="form.access_token" 
              class="form-control"
              placeholder="For private repositories"
            />
            <small class="form-text text-muted">
              Required for private repositories. Create a personal access token with repo permissions.
            </small>
          </div>

          <div class="form-check mb-3">
            <input 
              type="checkbox" 
              id="autoDeploy"
              v-model="form.auto_deploy" 
              class="form-check-input"
            />
            <label for="autoDeploy" class="form-check-label">
              Enable automatic deployment on push
            </label>
          </div>

          <button @click="connectRepo" class="btn btn-success" :disabled="connecting">
            <i class="fas fa-link" :class="{ 'fa-spin': connecting }"></i>
            {{ connecting ? 'Connecting...' : 'Connect Repository' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Diff Viewer Modal -->
    <div v-if="showDiffModal" class="modal" @click.self="showDiffModal = false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Changes - {{ currentCommitSha }}</h5>
            <button @click="showDiffModal = false" class="close">&times;</button>
          </div>
          <div class="modal-body">
            <pre v-if="diffContent" class="diff-content">{{ diffContent }}</pre>
            <div v-else class="text-center">
              <i class="fas fa-spinner fa-spin"></i> Loading diff...
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GitIntegration',

  props: {
    projectId: {
      type: [Number, String],
      required: true
    }
  },

  data() {
    return {
      repository: null,
      commits: [],
      branches: [],
      showHistory: false,
      showBranchesList: false,
      loadingCommits: false,
      loadingBranches: false,
      pulling: false,
      connecting: false,
      showDiffModal: false,
      diffContent: '',
      currentCommitSha: '',
      form: {
        url: '',
        branch: 'main',
        access_token: '',
        auto_deploy: false
      }
    };
  },

  mounted() {
    this.loadRepository();
  },

  methods: {
    async loadRepository() {
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/git`);
        this.repository = response.data;
      } catch (error) {
        if (error.response?.status !== 404) {
          console.error('Failed to load repository:', error);
        }
      }
    },

    async connectRepo() {
      this.connecting = true;
      try {
        const response = await axios.post(`/api/projects/${this.projectId}/git/connect`, this.form);
        this.repository = response.data;
        this.$emit('connected', this.repository);
      } catch (error) {
        alert('Failed to connect repository: ' + (error.response?.data?.message || error.message));
      } finally {
        this.connecting = false;
      }
    },

    async disconnectRepo() {
      if (!confirm('Are you sure you want to disconnect this repository?')) return;
      
      try {
        await axios.delete(`/api/projects/${this.projectId}/git`);
        this.repository = null;
        this.$emit('disconnected');
      } catch (error) {
        alert('Failed to disconnect repository: ' + (error.response?.data?.message || error.message));
      }
    },

    async pullChanges() {
      this.pulling = true;
      try {
        const response = await axios.post(`/api/projects/${this.projectId}/git/pull`);
        alert('Successfully pulled latest changes');
        this.repository = response.data;
        this.$emit('pulled', this.repository);
      } catch (error) {
        alert('Failed to pull changes: ' + (error.response?.data?.message || error.message));
      } finally {
        this.pulling = false;
      }
    },

    async showCommitHistory() {
      this.showHistory = !this.showHistory;
      if (this.showHistory && this.commits.length === 0) {
        this.loadingCommits = true;
        try {
          const response = await axios.get(`/api/projects/${this.projectId}/git/commits`);
          this.commits = response.data;
        } catch (error) {
          alert('Failed to load commits: ' + (error.response?.data?.message || error.message));
        } finally {
          this.loadingCommits = false;
        }
      }
    },

    async showBranches() {
      this.showBranchesList = !this.showBranchesList;
      if (this.showBranchesList && this.branches.length === 0) {
        this.loadingBranches = true;
        try {
          const response = await axios.get(`/api/projects/${this.projectId}/git/branches`);
          this.branches = response.data;
        } catch (error) {
          alert('Failed to load branches: ' + (error.response?.data?.message || error.message));
        } finally {
          this.loadingBranches = false;
        }
      }
    },

    async switchBranch(branchName) {
      if (!confirm(`Switch to branch "${branchName}"? This will pull the latest code from this branch.`)) return;
      
      try {
        const response = await axios.post(`/api/projects/${this.projectId}/git/branch`, {
          branch: branchName
        });
        this.repository = response.data;
        alert(`Switched to branch "${branchName}"`);
      } catch (error) {
        alert('Failed to switch branch: ' + (error.response?.data?.message || error.message));
      }
    },

    async toggleAutoDeploy() {
      try {
        const response = await axios.patch(`/api/projects/${this.projectId}/git/auto-deploy`, {
          auto_deploy: !this.repository.auto_deploy
        });
        this.repository = response.data;
      } catch (error) {
        alert('Failed to update auto-deploy: ' + (error.response?.data?.message || error.message));
      }
    },

    async viewDiff(sha) {
      this.currentCommitSha = sha;
      this.showDiffModal = true;
      this.diffContent = '';
      
      try {
        const response = await axios.get(`/api/projects/${this.projectId}/git/diff/${sha}`);
        this.diffContent = response.data;
      } catch (error) {
        this.diffContent = 'Failed to load diff: ' + error.message;
      }
    },

    async deployCommit(sha) {
      if (!confirm(`Deploy commit ${sha.substring(0, 7)}? This will revert your project to this version.`)) return;
      
      try {
        await axios.post(`/api/projects/${this.projectId}/git/deploy/${sha}`);
        alert('Deployment initiated');
      } catch (error) {
        alert('Failed to deploy: ' + (error.response?.data?.message || error.message));
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'Never';
      return new Date(dateString).toLocaleString();
    }
  }
};
</script>

<style scoped>
.git-integration {
  margin: 1rem 0;
}

.repository-info {
  padding: 1rem;
}

.repo-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.repo-details h5 {
  margin: 0;
  color: #333;
}

.repo-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
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

.stat span {
  font-weight: 500;
}

.git-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.commit-history,
.branches-list {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
}

.commits-list {
  max-height: 500px;
  overflow-y: auto;
}

.commit-item {
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background: #f8f9fa;
}

.commit-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.commit-header code {
  background: #fff;
  padding: 0.25rem 0.5rem;
  border-radius: 3px;
}

.commit-author {
  font-weight: 500;
}

.commit-date {
  color: #666;
  font-size: 0.875rem;
  margin-left: auto;
}

.commit-message {
  margin: 0.5rem 0;
  color: #333;
}

.commit-actions {
  display: flex;
  gap: 0.5rem;
}

.branch-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background: #f8f9fa;
}

.branch-name {
  font-weight: 500;
}

.connect-repo-form {
  padding: 1rem;
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
  max-width: 1000px;
}

.modal-content {
  background: white;
  border-radius: 8px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1rem;
  overflow-y: auto;
}

.diff-content {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 4px;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
}

.close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}
</style>