@extends('layouts.admin')

@section('page-title', 'User Resource Monitoring')

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search users..." 
                   class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <select name="sort" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="disk" {{ request('sort') === 'disk' ? 'selected' : '' }}>Disk Usage</option>
                <option value="projects" {{ request('sort') === 'projects' ? 'selected' : '' }}>Project Count</option>
                <option value="cpu" {{ request('sort') === 'cpu' ? 'selected' : '' }}>CPU Usage</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                Apply
            </button>
        </form>
    </div>

    {{-- User Resources Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Projects</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disk Usage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CPU Avg</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RAM Avg</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bandwidth (30d)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users ?? [] as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" 
                                     class="h-8 w-8 rounded-full" alt="{{ $user->name }}">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->projects_count ?? 0 }} / {{ $user->project_limit ?? 10 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($user->total_disk_usage ?? 0) }} MB</div>
                            <div class="w-32 bg-gray-200 rounded-full h-2 mt-1">
                                @php
                                    $diskPercent = $user->disk_quota > 0 ? min(100, ($user->total_disk_usage / $user->disk_quota) * 100) : 0;
                                @endphp
                                <div class="h-2 rounded-full transition-all
                                    {{ $diskPercent > 90 ? 'bg-red-500' : ($diskPercent > 70 ? 'bg-yellow-500' : 'bg-blue-500') }}" 
                                     style="width: {{ $diskPercent }}%"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ number_format($diskPercent, 1) }}% of {{ number_format($user->disk_quota) }} MB</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->avg_cpu ?? 0 }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->avg_memory ?? 0 }} MB
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($user->bandwidth_30d ?? 0, 2) }} GB
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-900">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No users found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($users) && $users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection