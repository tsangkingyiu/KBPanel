@extends('layouts.user')

@section('page-title', 'Project Monitoring')
@section('page-subtitle', 'Real-time monitoring of your projects')

@section('content')
<div class="space-y-6">
    <!-- Project Selector -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <label for="project-select" class="block text-sm font-medium text-gray-700 mb-2">Select Project to Monitor</label>
        <select id="project-select" onchange="loadProjectMetrics(this.value)"
                class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @foreach($projects as $project)
                <option value="{{ $project->id }}" {{ $selectedProject && $selectedProject->id == $project->id ? 'selected' : '' }}>
                    {{ $project->name }} - {{ $project->domain }}
                </option>
            @endforeach
        </select>
    </div>

    @if($selectedProject)
    <!-- Real-time Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $metrics['status'] ?? 'Online' }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">CPU Usage</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1" id="cpu-usage">{{ $metrics['cpu'] ?? '0' }}%</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Memory Usage</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1" id="memory-usage">{{ $metrics['memory'] ?? '0' }}%</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Response Time</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1" id="response-time">{{ $metrics['response_time'] ?? '0' }}ms</p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- CPU & Memory Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resource Usage (Last 24 Hours)</h3>
            <canvas id="resourceChart" height="250"></canvas>
        </div>

        <!-- Request Rate Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Rate (Last 24 Hours)</h3>
            <canvas id="requestChart" height="250"></canvas>
        </div>
    </div>

    <!-- Response Time Distribution -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Response Time Distribution</h3>
        <canvas id="responseTimeChart" height="150"></canvas>
    </div>

    <!-- Recent Logs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Recent Logs</h3>
            <div class="flex space-x-2">
                <button onclick="filterLogs('all')" class="px-3 py-1 text-sm rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">All</button>
                <button onclick="filterLogs('error')" class="px-3 py-1 text-sm rounded-lg text-gray-700 hover:bg-gray-100">Errors</button>
                <button onclick="filterLogs('warning')" class="px-3 py-1 text-sm rounded-lg text-gray-700 hover:bg-gray-100">Warnings</button>
                <button onclick="filterLogs('info')" class="px-3 py-1 text-sm rounded-lg text-gray-700 hover:bg-gray-100">Info</button>
            </div>
        </div>
        <div class="p-6 font-mono text-sm bg-gray-900 text-gray-100 max-h-96 overflow-y-auto" id="logs-container">
            @foreach($logs ?? [] as $log)
                <div class="mb-2 hover:bg-gray-800 p-2 rounded log-entry {{ $log['level'] }}">
                    <span class="text-gray-400">{{ $log['timestamp'] }}</span>
                    <span class="ml-2 font-semibold 
                        @if($log['level'] === 'error') text-red-400
                        @elseif($log['level'] === 'warning') text-yellow-400
                        @elseif($log['level'] === 'info') text-blue-400
                        @else text-gray-400
                        @endif">
                        [{{ strtoupper($log['level']) }}]
                    </span>
                    <span class="ml-2">{{ $log['message'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Alerts & Notifications -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Active Alerts</h3>
        </div>
        <div class="p-6">
            @if(isset($alerts) && count($alerts) > 0)
                @foreach($alerts as $alert)
                    <div class="mb-4 p-4 rounded-lg border-l-4 
                        @if($alert['severity'] === 'critical') bg-red-50 border-red-500
                        @elseif($alert['severity'] === 'warning') bg-yellow-50 border-yellow-500
                        @else bg-blue-50 border-blue-500
                        @endif">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $alert['title'] }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $alert['message'] }}</p>
                                <p class="text-xs text-gray-500 mt-2">{{ $alert['time'] }}</p>
                            </div>
                            <button onclick="dismissAlert({{ $alert['id'] }})" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 text-center py-8">No active alerts</p>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
let resourceChart, requestChart, responseTimeChart;

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    startRealTimeUpdates();
});

function initializeCharts() {
    // Resource Usage Chart
    const resourceCtx = document.getElementById('resourceChart');
    if (resourceCtx) {
        resourceChart = new Chart(resourceCtx, {
            type: 'line',
            data: {
                labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                datasets: [
                    {
                        label: 'CPU %',
                        data: @json($chartData['cpu'] ?? []),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Memory %',
                        data: @json($chartData['memory'] ?? []),
                        borderColor: 'rgb(147, 51, 234)',
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }

    // Request Rate Chart
    const requestCtx = document.getElementById('requestChart');
    if (requestCtx) {
        requestChart = new Chart(requestCtx, {
            type: 'bar',
            data: {
                labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                datasets: [{
                    label: 'Requests/min',
                    data: @json($chartData['requests'] ?? []),
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Response Time Chart
    const responseCtx = document.getElementById('responseTimeChart');
    if (responseCtx) {
        responseTimeChart = new Chart(responseCtx, {
            type: 'line',
            data: {
                labels: Array.from({length: 60}, (_, i) => `${i}m`),
                datasets: [{
                    label: 'Response Time (ms)',
                    data: @json($chartData['response_time'] ?? []),
                    borderColor: 'rgb(234, 179, 8)',
                    backgroundColor: 'rgba(234, 179, 8, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function startRealTimeUpdates() {
    setInterval(() => {
        const projectId = document.getElementById('project-select').value;
        if (projectId) {
            fetchMetrics(projectId);
        }
    }, 5000); // Update every 5 seconds
}

function fetchMetrics(projectId) {
    fetch(`/user/monitoring/${projectId}/metrics`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('cpu-usage').textContent = data.cpu + '%';
            document.getElementById('memory-usage').textContent = data.memory + '%';
            document.getElementById('response-time').textContent = data.response_time + 'ms';
        });
}

function loadProjectMetrics(projectId) {
    window.location.href = `/user/monitoring?project=${projectId}`;
}

function filterLogs(level) {
    const logs = document.querySelectorAll('.log-entry');
    logs.forEach(log => {
        if (level === 'all' || log.classList.contains(level)) {
            log.style.display = 'block';
        } else {
            log.style.display = 'none';
        }
    });
}

function dismissAlert(alertId) {
    fetch(`/user/monitoring/alerts/${alertId}/dismiss`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(() => location.reload());
}
</script>
@endsection
