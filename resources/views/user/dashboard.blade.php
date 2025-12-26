@extends('layouts.user')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your projects and resources')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Projects Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Projects</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_projects'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">of {{ $stats['project_limit'] ?? 10 }} limit</p>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Disk Usage Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Disk Usage</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['disk_used'] ?? 0) }} MB</p>
                <p class="text-sm text-gray-500 mt-1">of {{ number_format($stats['disk_quota'] ?? 5000) }} MB</p>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-purple-600" style="width: {{ min(100, ($stats['disk_used'] ?? 0) / ($stats['disk_quota'] ?? 1) * 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Active Deployments Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Deployments</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_deployments'] ?? 0 }}</p>
                <p class="text-sm text-green-600 mt-1">{{ $stats['running_projects'] ?? 0 }} running</p>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Backups Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Backups</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_backups'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $stats['backup_size_mb'] ?? 0 }} MB total</p>
            </div>
            <div class="p-3 bg-orange-50 rounded-lg">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Resource Usage Chart -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Resource Usage (Last 24 Hours)</h3>
        <div class="flex space-x-2">
            <button class="px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100" data-period="24h">24h</button>
            <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 rounded-md" data-period="7d">7d</button>
            <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 rounded-md" data-period="30d">30d</button>
        </div>
    </div>
    <canvas id="resourceChart" height="80"></canvas>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Projects -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Recent Projects</h3>
            <a href="{{ route('user.projects.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($recentProjects ?? [] as $project)
            <div class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900">{{ $project->name }}</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="inline-flex items-center">
                                @if($project->type === 'laravel')
                                <svg class="w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.642 5.43a.364.364 0 0 1 .014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 0 1-.188.326L9.93 23.949a.316.316 0 0 1-.066.027c-.008.002-.016.008-.024.01a.348.348 0 0 1-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 0 1-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 0 1 .023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 0 1 .375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 0 1 .024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.013-.012.027-.023.041-.032h.001l4.513-2.598a.375.375 0 0 1 .375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.01.02.018.04.024.06.006.01.01.02.013.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.039-.01-.012-.021-.023-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 0 1-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4 mr-1 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M21.469 0.014c-0.028-0.006-0.056-0.009-0.084-0.009-0.001 0-0.003 0-0.004 0h-18.759c-0.029 0-0.057 0.003-0.084 0.009l0.003-0c-0.017 0.003-0.032 0.008-0.046 0.015l0.002-0.001c-0.043 0.018-0.079 0.043-0.108 0.074l-0 0c-0.029 0.032-0.050 0.071-0.062 0.114l-0 0.002c-0.010 0.039-0.015 0.084-0.015 0.130 0 0.001 0 0.002 0 0.003v-0 23.342c-0.000 0.002-0.000 0.004-0.000 0.006 0 0.046 0.006 0.091 0.016 0.133l-0.001-0.004c0.012 0.046 0.033 0.086 0.062 0.119l-0-0c0.029 0.032 0.065 0.057 0.106 0.074l0.002 0.001c0.043 0.018 0.093 0.027 0.145 0.027 0.001 0 0.003 0 0.004-0h18.759c0.001 0 0.003 0 0.004 0 0.052 0 0.102-0.010 0.147-0.028l-0.003 0.001c0.043-0.018 0.079-0.043 0.108-0.074l0-0c0.029-0.032 0.050-0.071 0.062-0.114l0-0.002c0.010-0.039 0.015-0.084 0.015-0.130 0-0.001 0-0.002-0-0.003v0-23.342c0.000-0.002 0.000-0.004 0.000-0.006 0-0.046-0.006-0.091-0.016-0.133l0.001 0.004c-0.012-0.046-0.033-0.086-0.062-0.119l0 0c-0.029-0.032-0.065-0.057-0.106-0.074l-0.002-0.001c-0.012-0.005-0.026-0.009-0.041-0.013l-0.001-0zM12.084 13.539l-5.664 5.664-1.555-1.555 5.664-5.664 1.555 1.555zM19.875 5.748l-1.555 1.555-5.664-5.664 1.555-1.555 5.664 5.664z"/>
                                </svg>
                                @endif
                                {{ ucfirst($project->type) }}
                            </span>
                            <span class="mx-2">•</span>
                            <span>{{ $project->domain }}</span>
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($project->status === 'active') bg-green-100 text-green-800
                            @elseif($project->status === 'stopped') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($project->status) }}
                        </span>
                        <a href="{{ route('user.projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No projects yet</p>
                <a href="{{ route('user.projects.create') }}" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Create Your First Project
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
        </div>
        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
            @forelse($recentActivity ?? [] as $activity)
            <div class="px-6 py-4 hover:bg-gray-50">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            @if($activity->type === 'deployment') bg-blue-100
                            @elseif($activity->type === 'backup') bg-green-100
                            @elseif($activity->type === 'error') bg-red-100
                            @else bg-gray-100 @endif">
                            @if($activity->type === 'deployment')
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            @elseif($activity->type === 'backup')
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $activity->message }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center">
                <p class="text-sm text-gray-500">No recent activity</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Resource Usage Chart
    const ctx = document.getElementById('resourceChart');
    if (ctx) {
        const resourceChart = new Chart(ctx, {
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
});
</script>
@endpush
@endsection