@extends('layouts.admin')

@section('page-title', 'System Monitoring')

@section('content')
<div class="space-y-6">
    {{-- Real-Time Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">CPU Usage</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ $metrics['cpu'] ?? 0 }}%</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <canvas id="cpuSparkline" height="50"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Memory Usage</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ $metrics['memory'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $metrics['memory_used'] ?? 0 }}GB / {{ $metrics['memory_total'] ?? 0 }}GB</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <canvas id="memorySparkline" height="50"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Disk Usage</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ $metrics['disk'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $metrics['disk_used'] ?? 0 }}GB / {{ $metrics['disk_total'] ?? 0 }}GB</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-4">
                <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: {{ $metrics['disk'] ?? 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Containers</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ $metrics['containers'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $metrics['containers_running'] ?? 0 }} running</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- System Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resource Trends (Last Hour)</h3>
            <canvas id="resourceTrendsChart" height="300"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Network Traffic</h3>
            <canvas id="networkChart" height="300"></canvas>
        </div>
    </div>

    {{-- Docker Containers --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Docker Containers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Container</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CPU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Memory</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uptime</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($containers ?? [] as $container)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $container['name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $container['image'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $container['status'] === 'running' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($container['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $container['cpu'] ?? 0 }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $container['memory'] ?? 0 }} MB
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $container['uptime'] ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No containers running
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Resource Trends Chart
    const trendsCtx = document.getElementById('resourceTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendData['labels'] ?? []) !!},
            datasets: [
                {
                    label: 'CPU %',
                    data: {!! json_encode($trendData['cpu'] ?? []) !!},
                    borderColor: 'rgb(234, 179, 8)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Memory %',
                    data: {!! json_encode($trendData['memory'] ?? []) !!},
                    borderColor: 'rgb(168, 85, 247)',
                    tension: 0.4,
                    fill: false
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

    // Sparklines
    const cpuSparkCtx = document.getElementById('cpuSparkline').getContext('2d');
    new Chart(cpuSparkCtx, {
        type: 'line',
        data: {
            labels: Array(20).fill(''),
            datasets: [{
                data: {!! json_encode($sparklineData['cpu'] ?? array_fill(0, 20, 0)) !!},
                borderColor: 'rgb(234, 179, 8)',
                borderWidth: 2,
                fill: false,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
</script>
@endpush
@endsection