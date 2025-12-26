@extends('layouts.user')

@section('page-title', $project->name)
@section('page-subtitle', $project->domain)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Project Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Project Status</h3>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($project->status === 'active') bg-green-100 text-green-800
                    @elseif($project->status === 'stopped') bg-red-100 text-red-800
                    @elseif($project->status === 'deploying') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    <span class="w-2 h-2 mr-2 rounded-full animate-pulse
                        @if($project->status === 'active') bg-green-600
                        @elseif($project->status === 'stopped') bg-red-600
                        @elseif($project->status === 'deploying') bg-yellow-600
                        @else bg-gray-600 @endif"></span>
                    {{ ucfirst($project->status) }}
                </span>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Type</p>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($project->type) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">PHP Version</p>
                    <p class="text-sm font-medium text-gray-900">{{ $project->php_version }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Web Server</p>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($project->web_server) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Created</p>
                    <p class="text-sm font-medium text-gray-900">{{ $project->created_at->diffForHumans() }}</p>
                </div>
            </div>
            
            <div class="mt-6 flex flex-wrap gap-2">
                @if($project->status === 'active')
                <form action="{{ route('user.projects.stop', $project) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium transition">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
                        </svg>
                        Stop
                    </button>
                </form>
                @else
                <form action="{{ route('user.projects.start', $project) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium transition">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                        </svg>
                        Start
                    </button>
                </form>
                @endif
                
                <form action="{{ route('user.projects.restart', $project) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Restart
                    </button>
                </form>
                
                <a href="http://{{ $project->domain }}" target="_blank" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm font-medium transition">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Visit Site
                </a>
            </div>
        </div>
        
        <!-- Quick Actions Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button class="tab-btn active px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600" data-tab="overview">Overview</button>
                    <button class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="deployments">Deployments</button>
                    <button class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="logs">Logs</button>
                    <button class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="settings">Settings</button>
                </nav>
            </div>
            
            <!-- Overview Tab -->
            <div id="overview-tab" class="tab-content p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Resource Usage (Last 24h)</h4>
                <canvas id="projectResourceChart" height="100"></canvas>
                
                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Disk Usage</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($project->disk_usage ?? 0) }}</p>
                        <p class="text-xs text-gray-500">MB</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Uptime</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $project->uptime_percent ?? 0 }}%</p>
                        <p class="text-xs text-gray-500">Last 30 days</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Requests</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($project->requests_24h ?? 0) }}</p>
                        <p class="text-xs text-gray-500">Last 24h</p>
                    </div>
                </div>
            </div>
            
            <!-- Deployments Tab -->
            <div id="deployments-tab" class="tab-content p-6 hidden">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-md font-semibold text-gray-900">Deployment History</h4>
                    @if($project->git_repository_id)
                    <form action="{{ route('user.projects.deploy', $project) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                            Deploy Now
                        </button>
                    </form>
                    @endif
                </div>
                
                <div class="space-y-3">
                    @forelse($project->deployments()->latest()->take(10)->get() ?? [] as $deployment)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                @if($deployment->status === 'success') bg-green-100
                                @elseif($deployment->status === 'failed') bg-red-100
                                @else bg-yellow-100 @endif">
                                @if($deployment->status === 'success')
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                @elseif($deployment->status === 'failed')
                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-yellow-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                @endif
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $deployment->commit_message ?? 'Manual deployment' }}</p>
                                <p class="text-xs text-gray-500">{{ $deployment->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium px-2 py-1 rounded
                            @if($deployment->status === 'success') bg-green-100 text-green-800
                            @elseif($deployment->status === 'failed') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($deployment->status) }}
                        </span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-8">No deployments yet</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Logs Tab -->
            <div id="logs-tab" class="tab-content p-6 hidden">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Recent Logs</h4>
                <div class="bg-gray-900 rounded-lg p-4 font-mono text-xs text-green-400 max-h-96 overflow-y-auto">
                    <div id="logContent">
                        <div class="opacity-75">Loading logs...</div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content p-6 hidden">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Project Settings</h4>
                <form action="{{ route('user.projects.update', $project) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                        <input type="text" name="name" value="{{ $project->name }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">PHP Version</label>
                        <select name="php_version" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="7.4" @if($project->php_version === '7.4') selected @endif>PHP 7.4</option>
                            <option value="8.0" @if($project->php_version === '8.0') selected @endif>PHP 8.0</option>
                            <option value="8.1" @if($project->php_version === '8.1') selected @endif>PHP 8.1</option>
                            <option value="8.2" @if($project->php_version === '8.2') selected @endif>PHP 8.2</option>
                            <option value="8.3" @if($project->php_version === '8.3') selected @endif>PHP 8.3</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Web Server</label>
                        <select name="web_server" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="apache" @if($project->web_server === 'apache') selected @endif>Apache</option>
                            <option value="nginx" @if($project->web_server === 'nginx') selected @endif>Nginx</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">Save Changes</button>
                    </div>
                </form>
                
                <hr class="my-6">
                
                <div>
                    <h4 class="text-md font-semibold text-red-600 mb-2">Danger Zone</h4>
                    <p class="text-sm text-gray-600 mb-4">Permanently delete this project and all its data. This action cannot be undone.</p>
                    <form action="{{ route('user.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure? This will permanently delete all project data.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                            Delete Project
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
            <div class="space-y-2">
                <a href="{{ route('user.file-manager.index', $project) }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">File Manager</span>
                </a>
                
                <a href="{{ route('user.database.manager', $project) }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Database Manager</span>
                </a>
                
                <a href="{{ route('user.terminal.ssh', $project) }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">SSH Terminal</span>
                </a>
                
                <a href="{{ route('user.backups.index', ['project' => $project]) }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Backups</span>
                </a>
                
                @if($project->has_staging)
                <a href="{{ route('user.staging.index', ['project' => $project]) }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Staging Environment</span>
                </a>
                @endif
                
                <a href="{{ route('user.ssl.index', ['project' => $project]) }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">SSL Certificate</span>
                </a>
            </div>
        </div>
        
        <!-- Database Info -->
        @if($project->database)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Database</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Name:</span>
                    <span class="font-medium text-gray-900">{{ $project->database->db_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Type:</span>
                    <span class="font-medium text-gray-900">{{ $project->database->db_type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Size:</span>
                    <span class="font-medium text-gray-900">{{ number_format($project->database->size_mb ?? 0) }} MB</span>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Git Info -->
        @if($project->gitRepository)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Git Repository</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-gray-500">Repository:</span>
                    <p class="font-medium text-gray-900 mt-1 break-all">{{ $project->gitRepository->repository_url }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Branch:</span>
                    <p class="font-medium text-gray-900 mt-1">{{ $project->gitRepository->branch }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Last Pull:</span>
                    <p class="font-medium text-gray-900 mt-1">{{ $project->gitRepository->last_pulled_at?->diffForHumans() ?? 'Never' }}</p>
                </div>
                <a href="{{ route('user.git.repositories', $project) }}" class="block text-center mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">
                    Manage Repository
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Update button styles
            tabBtns.forEach(b => {
                b.classList.remove('active', 'border-blue-600', 'text-blue-600');
                b.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.add('active', 'border-blue-600', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500');
            
            // Update content visibility
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            // Load logs if logs tab is clicked
            if (tabName === 'logs') {
                loadLogs();
            }
        });
    });
    
    // Resource Chart
    const ctx = document.getElementById('projectResourceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [
                    {
                        label: 'CPU %',
                        data: @json($cpuData ?? []),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3
                    },
                    {
                        label: 'Memory (MB)',
                        data: @json($memoryData ?? []),
                        borderColor: 'rgb(168, 85, 247)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    function loadLogs() {
        const logContent = document.getElementById('logContent');
        // Simulate loading logs - in production, this would fetch from API
        logContent.innerHTML = '<div class="opacity-75">[' + new Date().toISOString() + '] Application started...</div>';
    }
});
</script>
@endpush
@endsection