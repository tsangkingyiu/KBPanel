@extends('layouts.user')

@section('title', 'Deploy Laravel Application')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('user.projects.create') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Deploy Laravel Application</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Configure your Laravel project settings</p>
            </div>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center flex-1">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">
                    1
                </div>
                <div class="flex-1 h-1 mx-2 bg-gray-300 dark:bg-gray-600">
                    <div id="progress-bar-1" class="h-full bg-blue-600" style="width: 0%"></div>
                </div>
            </div>
            <div class="flex items-center flex-1">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 font-semibold" id="step-2">
                    2
                </div>
                <div class="flex-1 h-1 mx-2 bg-gray-300 dark:bg-gray-600">
                    <div id="progress-bar-2" class="h-full bg-blue-600" style="width: 0%"></div>
                </div>
            </div>
            <div class="flex items-center flex-1">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 font-semibold" id="step-3">
                    3
                </div>
                <div class="flex-1 h-1 mx-2 bg-gray-300 dark:bg-gray-600">
                    <div id="progress-bar-3" class="h-full bg-blue-600" style="width: 0%"></div>
                </div>
            </div>
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 font-semibold" id="step-4">
                4
            </div>
        </div>
        <div class="flex justify-between mt-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Basic Info</span>
            <span>Configuration</span>
            <span>Git (Optional)</span>
            <span>Review</span>
        </div>
    </div>

    <form action="{{ route('user.deployments.store') }}" method="POST" id="deploymentForm">
        @csrf
        <input type="hidden" name="type" value="laravel">

        <!-- Step 1: Basic Information -->
        <div id="step-1-content" class="step-content bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Basic Information</h2>
            
            <div class="space-y-6">
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Name *</label>
                    <input type="text" id="project_name" name="project_name" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="my-laravel-app">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lowercase letters, numbers, and hyphens only</p>
                    @error('project_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Domain *</label>
                    <input type="text" id="domain" name="domain" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="example.com">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your domain or subdomain</p>
                    @error('domain')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="laravel_version" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Laravel Version *</label>
                    <select id="laravel_version" name="laravel_version" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="12.x">Laravel 12.x (Latest)</option>
                        <option value="11.x">Laravel 11.x</option>
                        <option value="10.x">Laravel 10.x</option>
                    </select>
                    @error('laravel_version')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button type="button" onclick="nextStep(1)" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Next: Configuration
                    <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Configuration -->
        <div id="step-2-content" class="step-content bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Configuration</h2>
            
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="php_version" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">PHP Version *</label>
                        <select id="php_version" name="php_version" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="8.3">PHP 8.3</option>
                            <option value="8.2" selected>PHP 8.2 (Recommended)</option>
                            <option value="8.1">PHP 8.1</option>
                            <option value="8.0">PHP 8.0</option>
                        </select>
                    </div>

                    <div>
                        <label for="web_server" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Web Server *</label>
                        <select id="web_server" name="web_server" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="apache" selected>Apache (Recommended)</option>
                            <option value="nginx">Nginx</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Database Configuration</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="db_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database Name *</label>
                            <input type="text" id="db_name" name="db_name" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="laravel_db">
                        </div>

                        <div>
                            <label for="db_user" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database User *</label>
                            <input type="text" id="db_user" name="db_user" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="laravel_user">
                        </div>

                        <div>
                            <label for="db_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database Password *</label>
                            <div class="relative">
                                <input type="password" id="db_password" name="db_password" required
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <button type="button" onclick="generatePassword()" 
                                        class="absolute right-2 top-2 px-3 py-1 text-sm bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                    Generate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Options</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="enable_ssl" value="1" checked
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable SSL (Let's Encrypt)</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="enable_staging" value="1"
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Create staging environment</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-8">
                <button type="button" onclick="prevStep(2)" 
                        class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Previous
                </button>
                <button type="button" onclick="nextStep(2)" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Next: Git Setup
                    <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Git Integration (Optional) -->
        <div id="step-3-content" class="step-content bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Git Integration (Optional)</h2>
            
            <div class="space-y-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        Connect your Git repository for automatic deployments and version control.
                    </p>
                </div>

                <div>
                    <label for="git_repository" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Repository URL</label>
                    <input type="url" id="git_repository" name="git_repository"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="https://github.com/username/repository.git">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave empty to skip Git integration</p>
                </div>

                <div>
                    <label for="git_branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Branch</label>
                    <input type="text" id="git_branch" name="git_branch" value="main"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="main">
                </div>

                <div>
                    <label for="git_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Access Token (if private)</label>
                    <input type="password" id="git_token" name="git_token"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="ghp_xxxxxxxxxxxx">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Required for private repositories</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="auto_deploy" value="1"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable automatic deployment on push</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-between mt-8">
                <button type="button" onclick="prevStep(3)" 
                        class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Previous
                </button>
                <button type="button" onclick="nextStep(3)" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Next: Review
                    <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 4: Review and Deploy -->
        <div id="step-4-content" class="step-content bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 hidden">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Review and Deploy</h2>
            
            <div id="review-summary" class="space-y-6">
                <!-- Summary will be populated by JavaScript -->
            </div>

            <div class="flex justify-between mt-8">
                <button type="button" onclick="prevStep(4)" 
                        class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Previous
                </button>
                <button type="submit" id="deployButton"
                        class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Deploy Laravel Application
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;

function nextStep(step) {
    // Validate current step
    if (!validateStep(step)) return;
    
    // Hide current step
    document.getElementById(`step-${step}-content`).classList.add('hidden');
    
    // Show next step
    currentStep = step + 1;
    document.getElementById(`step-${currentStep}-content`).classList.remove('hidden');
    
    // Update progress
    updateProgress();
    
    // Update review if at final step
    if (currentStep === 4) {
        updateReview();
    }
    
    // Scroll to top
    window.scrollTo(0, 0);
}

function prevStep(step) {
    // Hide current step
    document.getElementById(`step-${step}-content`).classList.add('hidden');
    
    // Show previous step
    currentStep = step - 1;
    document.getElementById(`step-${currentStep}-content`).classList.remove('hidden');
    
    // Update progress
    updateProgress();
    
    // Scroll to top
    window.scrollTo(0, 0);
}

function validateStep(step) {
    const stepContent = document.getElementById(`step-${step}-content`);
    const inputs = stepContent.querySelectorAll('input[required], select[required]');
    
    for (let input of inputs) {
        if (!input.value.trim()) {
            input.focus();
            alert('Please fill in all required fields');
            return false;
        }
    }
    return true;
}

function updateProgress() {
    for (let i = 1; i <= 4; i++) {
        const stepCircle = document.getElementById(`step-${i}`);
        const progressBar = document.getElementById(`progress-bar-${i}`);
        
        if (i < currentStep) {
            stepCircle.classList.remove('bg-gray-300', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
            stepCircle.classList.add('bg-blue-600', 'text-white');
            if (progressBar) progressBar.style.width = '100%';
        } else if (i === currentStep) {
            stepCircle.classList.remove('bg-gray-300', 'dark:bg-gray-600', 'text-gray-600', 'dark:text-gray-400');
            stepCircle.classList.add('bg-blue-600', 'text-white');
        }
    }
}

function updateReview() {
    const formData = new FormData(document.getElementById('deploymentForm'));
    const reviewHTML = `
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Project Configuration</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Project Name:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">${formData.get('project_name')}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Domain:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">${formData.get('domain')}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Laravel Version:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">${formData.get('laravel_version')}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">PHP Version:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">${formData.get('php_version')}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Web Server:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">${formData.get('web_server')}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Database:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">${formData.get('db_name')}</dd>
                </div>
            </dl>
        </div>
        ${formData.get('git_repository') ? `
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Git Configuration</h3>
            <dl class="space-y-2">
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Repository:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white break-all">${formData.get('git_repository')}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600 dark:text-gray-400">Branch:</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">${formData.get('git_branch') || 'main'}</dd>
                </div>
            </dl>
        </div>
        ` : ''}
    `;
    document.getElementById('review-summary').innerHTML = reviewHTML;
}

function generatePassword() {
    const length = 16;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    document.getElementById('db_password').value = password;
}

// Form submission
document.getElementById('deploymentForm').addEventListener('submit', function(e) {
    const button = document.getElementById('deployButton');
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Deploying...';
});
</script>
@endpush