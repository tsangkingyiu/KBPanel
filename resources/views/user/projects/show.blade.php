@extends('layouts.user')

@section('title', $project->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('user.projects.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $project->name }}</h1>
                <span class="ml-4 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $project->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                    {{ ucfirst($project->status) }}
                </span>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                <a href="https://{{ $project->domain }}" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400">
                    {{ $project->domain }}
                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('user.file-manager.index', $project) }}" 
               class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                File Manager
            </a>
            <a href="{{ route('user.terminal.ssh', $project) }}" 
               class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                SSH Terminal
            </a>
            <a href="{{ route('user.database.manager', $project) }}" 
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                </svg>
                Database
            </a>
        </div>
    </div>

    <!-- Resource Monitoring Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">CPU Usage</h3>
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $project->cpu_usage ?? 0 }}%</p>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->cpu_usage ?? 0 }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Memory</h3>
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $project->memory_usage ?? 0 }}</p>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">MB</span>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(($project->memory_usage ?? 0) / 10, 100) }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Disk Space</h3>
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format(($project->disk_usage ?? 0) / 1024, 1) }}</p>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">GB</span>
            </div>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">of {{ auth()->user()->disk_quota / 1024 }} GB</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Uptime</h3>
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex items-baseline">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $uptime ?? '99.9' }}%</p>
            </div>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Last 30 days</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-t-lg shadow-md">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button onclick="switchTab('overview')" id="tab-overview" 
                        class="tab-button active px-6 py-4 text-sm font-medium border-b-2">
                    Overview
                </button>
                <button onclick="switchTab('deployments')" id="tab-deployments" 
                        class="tab-button px-6 py-4 text-sm font-medium border-b-2">
                    Deployments
                </button>
                <button onclick="switchTab('git')" id="tab-git" 
                        class="tab-button px-6 py-4 text-sm font-medium border-b-2">
                    Git
                </button>
                <button onclick="switchTab('ssl')" id="tab-ssl" 
                        class="tab-button px-6 py-4 text-sm font-medium border-b-2">
                    SSL
                </button>
                <button onclick="switchTab('backups')" id="tab-backups" 
                        class="tab-button px-6 py-4 text-sm font-medium border-b-2">
                    Backups
                </button>
                <button onclick="switchTab('settings')" id="tab-settings" 
                        class="tab-button px-6 py-4 text-sm font-medium border-b-2">
                    Settings
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="bg-white dark:bg-gray-800 rounded-b-lg shadow-md p-6">
        <!-- Overview Tab -->
        <div id="content-overview" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Project Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Project Details</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Type</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($project->type) }}</dd>
                        </div>
                        @if($project->type === 'laravel')
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Laravel Version</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->laravel_version ?? 'N/A' }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">PHP Version</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->php_version ?? '8.2' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Web Server</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($project->web_server ?? 'apache') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Container ID</dt>
                            <dd class="text-sm font-mono text-gray-900 dark:text-white">{{ substr($project->docker_container_id ?? 'N/A', 0, 12) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Created</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Quick Actions -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($project->has_staging)
                        <a href="{{ route('user.staging.show', $project) }}" 
                           class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Manage Staging</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">View and sync staging environment</p>
                            </div>
                        </a>
                        @else
                        <a href="{{ route('user.staging.create', $project) }}" 
                           class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Create Staging</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Set up staging environment</p>
                            </div>
                        </a>
                        @endif
                        
                        <a href="{{ route('user.backups.create', $project) }}" 
                           class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Create Backup</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Backup files and database</p>
                            </div>
                        </a>

                        <form action="{{ route('user.projects.restart', $project) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900 dark:text-white">Restart Container</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Restart Docker container</p>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Tab Contents (Deployments, Git, SSL, Backups, Settings) -->
        <div id="content-deployments" class="tab-content hidden">
            <p class="text-gray-600 dark:text-gray-400">Deployment history and controls will be displayed here.</p>
        </div>

        <div id="content-git" class="tab-content hidden">
            <p class="text-gray-600 dark:text-gray-400">Git repository integration will be displayed here.</p>
        </div>

        <div id="content-ssl" class="tab-content hidden">
            <p class="text-gray-600 dark:text-gray-400">SSL certificate management will be displayed here.</p>
        </div>

        <div id="content-backups" class="tab-content hidden">
            <p class="text-gray-600 dark:text-gray-400">Backup history and restore options will be displayed here.</p>
        </div>

        <div id="content-settings" class="tab-content hidden">
            <p class="text-gray-600 dark:text-gray-400">Project settings and configuration will be displayed here.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
        button.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400');
    activeButton.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
}

// Initialize tab styling
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-button').forEach(button => {
        if (!button.classList.contains('active')) {
            button.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400', 'hover:text-gray-900', 'dark:hover:text-white', 'hover:border-gray-300', 'dark:hover:border-gray-600');
        } else {
            button.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400');
        }
    });
});
</script>
@endpush