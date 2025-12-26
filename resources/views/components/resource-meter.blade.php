@php
    $user = auth()->user();
    $projects = $user->projects ?? collect();
    $diskUsed = $projects->sum('disk_usage') ?? 0;
    $diskQuota = $user->disk_quota ?? 5000;
    $diskPercent = $diskQuota > 0 ? min(100, ($diskUsed / $diskQuota) * 100) : 0;
    $projectCount = $projects->count();
    $projectLimit = $user->project_limit ?? 10;
@endphp

<div class="flex items-center space-x-4 text-sm">
    <!-- Disk Usage -->
    <div class="flex items-center space-x-2">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
        </svg>
        <div>
            <p class="text-xs text-gray-500">Disk</p>
            <div class="flex items-center space-x-2">
                <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full transition-all duration-300
                        {{ $diskPercent > 90 ? 'bg-red-600' : ($diskPercent > 70 ? 'bg-yellow-500' : 'bg-blue-600') }}"
                        style="width: {{ $diskPercent }}%"></div>
                </div>
                <span class="text-xs font-medium text-gray-700">{{ number_format($diskUsed) }}/{{ number_format($diskQuota) }} MB</span>
            </div>
        </div>
    </div>

    <!-- Project Count -->
    <div class="flex items-center space-x-2 pl-4 border-l border-gray-200">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
        </svg>
        <div>
            <p class="text-xs text-gray-500">Projects</p>
            <p class="text-sm font-medium text-gray-700">{{ $projectCount }}/{{ $projectLimit }}</p>
        </div>
    </div>
</div>