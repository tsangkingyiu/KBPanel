@extends('layouts.user')

@section('title', 'Deploy WordPress Site')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('user.projects.create') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Deploy WordPress Site</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Set up your WordPress installation</p>
            </div>
        </div>
    </div>

    <form action="{{ route('user.deployments.store') }}" method="POST" id="wordpressForm">
        @csrf
        <input type="hidden" name="type" value="wordpress">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 space-y-8">
            <!-- Site Information -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Site Information</h2>
                <div class="space-y-4">
                    <div>
                        <label for="site_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Title *</label>
                        <input type="text" id="site_title" name="site_title" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="My WordPress Site">
                        @error('site_title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Domain *</label>
                        <input type="text" id="domain" name="domain" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="myblog.com">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your domain or subdomain</p>
                        @error('domain')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <!-- Admin Account -->
            <section class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">WordPress Admin Account</h2>
                <div class="space-y-4">
                    <div>
                        <label for="admin_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Username *</label>
                        <input type="text" id="admin_username" name="admin_username" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="admin">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Do not use 'admin' for security reasons</p>
                    </div>

                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Password *</label>
                        <div class="relative">
                            <input type="password" id="admin_password" name="admin_password" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <button type="button" onclick="generatePassword('admin_password')" 
                                    class="absolute right-2 top-2 px-3 py-1 text-sm bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                Generate
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Email *</label>
                        <input type="email" id="admin_email" name="admin_email" required value="{{ auth()->user()->email }}"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="admin@example.com">
                    </div>
                </div>
            </section>

            <!-- Database Configuration -->
            <section class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Database Configuration</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">A new MySQL database will be automatically created for your WordPress site.</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="db_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database Name *</label>
                        <input type="text" id="db_name" name="db_name" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="wp_database">
                    </div>

                    <div>
                        <label for="db_user" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database User *</label>
                        <input type="text" id="db_user" name="db_user" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="wp_user">
                    </div>

                    <div>
                        <label for="db_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database Password *</label>
                        <div class="relative">
                            <input type="password" id="db_password" name="db_password" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <button type="button" onclick="generatePassword('db_password')" 
                                    class="absolute right-2 top-2 px-3 py-1 text-sm bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                Generate
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Advanced Settings -->
            <section class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Advanced Settings</h2>
                
                <div class="space-y-4">
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

                        <label class="flex items-center">
                            <input type="checkbox" name="install_woocommerce" value="1"
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Install WooCommerce</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="auto_updates" value="1" checked
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable automatic WordPress updates</span>
                        </label>
                    </div>
                </div>
            </section>

            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium mb-1">What happens next?</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Latest WordPress will be downloaded and installed</li>
                            <li>Database and user will be created automatically</li>
                            <li>SSL certificate will be generated if enabled</li>
                            <li>Your site will be accessible within minutes</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" id="deployButton"
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Deploy WordPress Site
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function generatePassword(inputId) {
    const length = 16;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    document.getElementById(inputId).value = password;
    document.getElementById(inputId).type = 'text';
    setTimeout(() => {
        document.getElementById(inputId).type = 'password';
    }, 2000);
}

// Auto-generate database name and user from site title
document.getElementById('site_title').addEventListener('input', function(e) {
    const value = e.target.value.toLowerCase()
        .replace(/[^a-z0-9]/g, '_')
        .replace(/_+/g, '_')
        .substring(0, 20);
    
    if (value) {
        if (!document.getElementById('db_name').value) {
            document.getElementById('db_name').value = 'wp_' + value;
        }
        if (!document.getElementById('db_user').value) {
            document.getElementById('db_user').value = 'wp_' + value;
        }
    }
});

// Form submission
document.getElementById('wordpressForm').addEventListener('submit', function(e) {
    const button = document.getElementById('deployButton');
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Deploying...';
});
</script>
@endpush