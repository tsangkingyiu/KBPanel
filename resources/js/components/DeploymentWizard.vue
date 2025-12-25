<template>
  <div class="deployment-wizard">
    <div class="wizard-header">
      <h2>Deploy New Project</h2>
      <div class="wizard-steps">
        <div 
          v-for="(step, index) in steps" 
          :key="index"
          class="wizard-step"
          :class="{ active: currentStep === index, completed: currentStep > index }"
        >
          <div class="step-number">{{ index + 1 }}</div>
          <div class="step-label">{{ step }}</div>
        </div>
      </div>
    </div>

    <div class="wizard-content">
      <!-- Step 1: Project Type -->
      <div v-show="currentStep === 0" class="step-content">
        <h3>Select Project Type</h3>
        <div class="project-types">
          <div 
            class="project-type-card"
            :class="{ selected: form.type === 'laravel' }"
            @click="form.type = 'laravel'"
          >
            <i class="fab fa-laravel fa-3x"></i>
            <h4>Laravel</h4>
            <p>Deploy a Laravel application</p>
            <div class="version-select" v-if="form.type === 'laravel'">
              <label>Laravel Version:</label>
              <select v-model="form.laravelVersion" class="form-control">
                <option value="12">Laravel 12</option>
                <option value="11">Laravel 11</option>
                <option value="10">Laravel 10</option>
                <option value="9">Laravel 9</option>
                <option value="8">Laravel 8</option>
              </select>
            </div>
          </div>

          <div 
            class="project-type-card"
            :class="{ selected: form.type === 'wordpress' }"
            @click="form.type = 'wordpress'"
          >
            <i class="fab fa-wordpress fa-3x"></i>
            <h4>WordPress</h4>
            <p>Deploy a WordPress site</p>
          </div>
        </div>
      </div>

      <!-- Step 2: Project Details -->
      <div v-show="currentStep === 1" class="step-content">
        <h3>Project Details</h3>
        <div class="form-group">
          <label for="projectName">Project Name</label>
          <input 
            type="text" 
            id="projectName"
            v-model="form.name" 
            class="form-control"
            placeholder="my-awesome-project"
          />
        </div>

        <div class="form-group">
          <label for="domain">Domain</label>
          <input 
            type="text" 
            id="domain"
            v-model="form.domain" 
            class="form-control"
            placeholder="example.com"
          />
        </div>

        <div class="form-group">
          <label for="phpVersion">PHP Version</label>
          <select v-model="form.phpVersion" id="phpVersion" class="form-control">
            <option value="8.3">PHP 8.3</option>
            <option value="8.2" selected>PHP 8.2</option>
            <option value="8.1">PHP 8.1</option>
            <option value="8.0">PHP 8.0</option>
            <option value="7.4">PHP 7.4</option>
          </select>
        </div>

        <div class="form-group">
          <label for="webServer">Web Server</label>
          <select v-model="form.webServer" id="webServer" class="form-control">
            <option value="apache">Apache</option>
            <option value="nginx">Nginx</option>
          </select>
        </div>
      </div>

      <!-- Step 3: Database Configuration -->
      <div v-show="currentStep === 2" class="step-content">
        <h3>Database Configuration</h3>
        
        <div class="form-check mb-3">
          <input 
            type="checkbox" 
            id="createDatabase"
            v-model="form.createDatabase" 
            class="form-check-input"
          />
          <label for="createDatabase" class="form-check-label">
            Create database for this project
          </label>
        </div>

        <div v-if="form.createDatabase">
          <div class="form-group">
            <label for="dbName">Database Name</label>
            <input 
              type="text" 
              id="dbName"
              v-model="form.dbName" 
              class="form-control"
              placeholder="project_db"
            />
          </div>

          <div class="form-group">
            <label for="dbUser">Database User</label>
            <input 
              type="text" 
              id="dbUser"
              v-model="form.dbUser" 
              class="form-control"
              placeholder="db_user"
            />
          </div>

          <div class="form-group">
            <label for="dbPassword">Database Password</label>
            <input 
              type="password" 
              id="dbPassword"
              v-model="form.dbPassword" 
              class="form-control"
            />
            <small class="form-text">Leave blank to auto-generate</small>
          </div>
        </div>
      </div>

      <!-- Step 4: Additional Options -->
      <div v-show="currentStep === 3" class="step-content">
        <h3>Additional Options</h3>

        <div class="form-check mb-3">
          <input 
            type="checkbox" 
            id="enableStaging"
            v-model="form.enableStaging" 
            class="form-check-input"
          />
          <label for="enableStaging" class="form-check-label">
            Create staging environment
          </label>
        </div>

        <div class="form-check mb-3">
          <input 
            type="checkbox" 
            id="enableSsl"
            v-model="form.enableSsl" 
            class="form-check-input"
          />
          <label for="enableSsl" class="form-check-label">
            Enable SSL (Let's Encrypt)
          </label>
        </div>

        <div class="form-check mb-3">
          <input 
            type="checkbox" 
            id="gitIntegration"
            v-model="form.gitIntegration" 
            class="form-check-input"
          />
          <label for="gitIntegration" class="form-check-label">
            Set up Git integration
          </label>
        </div>

        <div v-if="form.gitIntegration" class="mt-3">
          <div class="form-group">
            <label for="gitRepo">Git Repository URL</label>
            <input 
              type="text" 
              id="gitRepo"
              v-model="form.gitRepo" 
              class="form-control"
              placeholder="https://github.com/username/repo.git"
            />
          </div>

          <div class="form-group">
            <label for="gitBranch">Branch</label>
            <input 
              type="text" 
              id="gitBranch"
              v-model="form.gitBranch" 
              class="form-control"
              value="main"
            />
          </div>
        </div>
      </div>

      <!-- Step 5: Review & Deploy -->
      <div v-show="currentStep === 4" class="step-content">
        <h3>Review & Deploy</h3>
        <div class="review-section">
          <div class="review-item">
            <strong>Project Type:</strong>
            <span>{{ form.type === 'laravel' ? 'Laravel ' + form.laravelVersion : 'WordPress' }}</span>
          </div>
          <div class="review-item">
            <strong>Project Name:</strong>
            <span>{{ form.name }}</span>
          </div>
          <div class="review-item">
            <strong>Domain:</strong>
            <span>{{ form.domain }}</span>
          </div>
          <div class="review-item">
            <strong>PHP Version:</strong>
            <span>{{ form.phpVersion }}</span>
          </div>
          <div class="review-item">
            <strong>Web Server:</strong>
            <span>{{ form.webServer }}</span>
          </div>
          <div class="review-item" v-if="form.createDatabase">
            <strong>Database:</strong>
            <span>{{ form.dbName }}</span>
          </div>
          <div class="review-item" v-if="form.enableStaging">
            <strong>Staging:</strong>
            <span class="badge badge-success">Enabled</span>
          </div>
          <div class="review-item" v-if="form.enableSsl">
            <strong>SSL:</strong>
            <span class="badge badge-success">Enabled</span>
          </div>
        </div>

        <div v-if="deploying" class="deployment-progress">
          <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 :style="{ width: deploymentProgress + '%' }">
              {{ deploymentProgress }}%
            </div>
          </div>
          <p class="text-center mt-2">{{ deploymentStatus }}</p>
        </div>
      </div>
    </div>

    <div class="wizard-footer">
      <button 
        @click="prevStep" 
        class="btn btn-secondary"
        :disabled="currentStep === 0 || deploying"
      >
        Previous
      </button>
      
      <button 
        v-if="currentStep < steps.length - 1"
        @click="nextStep" 
        class="btn btn-primary"
        :disabled="!canProceed"
      >
        Next
      </button>

      <button 
        v-else
        @click="deploy" 
        class="btn btn-success"
        :disabled="deploying || !canProceed"
      >
        <span v-if="!deploying">Deploy Project</span>
        <span v-else>
          <i class="fas fa-spinner fa-spin"></i> Deploying...
        </span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DeploymentWizard',

  data() {
    return {
      currentStep: 0,
      steps: ['Project Type', 'Details', 'Database', 'Options', 'Deploy'],
      deploying: false,
      deploymentProgress: 0,
      deploymentStatus: '',
      form: {
        type: 'laravel',
        laravelVersion: '12',
        name: '',
        domain: '',
        phpVersion: '8.2',
        webServer: 'apache',
        createDatabase: true,
        dbName: '',
        dbUser: '',
        dbPassword: '',
        enableStaging: false,
        enableSsl: true,
        gitIntegration: false,
        gitRepo: '',
        gitBranch: 'main'
      }
    };
  },

  computed: {
    canProceed() {
      switch (this.currentStep) {
        case 0:
          return !!this.form.type;
        case 1:
          return this.form.name && this.form.domain;
        case 2:
          if (!this.form.createDatabase) return true;
          return this.form.dbName && this.form.dbUser;
        case 3:
          if (!this.form.gitIntegration) return true;
          return this.form.gitRepo;
        case 4:
          return true;
        default:
          return false;
      }
    }
  },

  watch: {
    'form.name'(val) {
      if (!this.form.dbName || this.form.dbName === '') {
        this.form.dbName = val.replace(/[^a-zA-Z0-9]/g, '_');
      }
      if (!this.form.dbUser || this.form.dbUser === '') {
        this.form.dbUser = val.substring(0, 16).replace(/[^a-zA-Z0-9]/g, '_');
      }
    }
  },

  methods: {
    nextStep() {
      if (this.currentStep < this.steps.length - 1 && this.canProceed) {
        this.currentStep++;
      }
    },

    prevStep() {
      if (this.currentStep > 0) {
        this.currentStep--;
      }
    },

    async deploy() {
      this.deploying = true;
      this.deploymentProgress = 0;

      try {
        // Simulate deployment steps
        this.deploymentStatus = 'Creating project structure...';
        await this.updateProgress(20);

        this.deploymentStatus = 'Setting up Docker container...';
        await this.updateProgress(40);

        if (this.form.createDatabase) {
          this.deploymentStatus = 'Creating database...';
          await this.updateProgress(60);
        }

        if (this.form.gitIntegration) {
          this.deploymentStatus = 'Cloning Git repository...';
          await this.updateProgress(75);
        }

        this.deploymentStatus = 'Configuring web server...';
        await this.updateProgress(85);

        if (this.form.enableSsl) {
          this.deploymentStatus = 'Generating SSL certificate...';
          await this.updateProgress(95);
        }

        // Actual API call
        const response = await axios.post('/api/projects/deploy', this.form);
        
        this.deploymentProgress = 100;
        this.deploymentStatus = 'Deployment complete!';

        setTimeout(() => {
          this.$emit('deployed', response.data);
          window.location.href = `/projects/${response.data.id}`;
        }, 1000);

      } catch (error) {
        console.error('Deployment error:', error);
        alert('Deployment failed: ' + (error.response?.data?.message || error.message));
        this.deploying = false;
      }
    },

    updateProgress(target) {
      return new Promise(resolve => {
        const interval = setInterval(() => {
          if (this.deploymentProgress >= target) {
            clearInterval(interval);
            resolve();
          } else {
            this.deploymentProgress += 1;
          }
        }, 50);
      });
    }
  }
};
</script>

<style scoped>
.deployment-wizard {
  max-width: 900px;
  margin: 0 auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.wizard-header {
  padding: 2rem;
  border-bottom: 1px solid #e0e0e0;
}

.wizard-header h2 {
  margin: 0 0 1.5rem 0;
}

.wizard-steps {
  display: flex;
  justify-content: space-between;
}

.wizard-step {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.wizard-step:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 20px;
  left: 60%;
  right: -40%;
  height: 2px;
  background: #e0e0e0;
  z-index: -1;
}

.wizard-step.completed:not(:last-child)::after {
  background: #4caf50;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.wizard-step.active .step-number {
  background: #2196f3;
  color: white;
}

.wizard-step.completed .step-number {
  background: #4caf50;
  color: white;
}

.step-label {
  font-size: 0.875rem;
  color: #666;
}

.wizard-content {
  padding: 2rem;
  min-height: 400px;
}

.step-content h3 {
  margin-bottom: 1.5rem;
}

.project-types {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.project-type-card {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.project-type-card:hover {
  border-color: #2196f3;
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.project-type-card.selected {
  border-color: #2196f3;
  background: #e3f2fd;
}

.project-type-card i {
  color: #2196f3;
  margin-bottom: 1rem;
}

.version-select {
  margin-top: 1rem;
  text-align: left;
}

.review-section {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 1.5rem;
}

.review-item {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e0e0e0;
}

.review-item:last-child {
  border-bottom: none;
}

.deployment-progress {
  margin-top: 2rem;
}

.wizard-footer {
  padding: 1.5rem 2rem;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-check {
  display: flex;
  align-items: center;
}

.form-check-input {
  margin-right: 0.5rem;
}
</style>