@extends('layouts.admin')

@section('page-title', 'Server Configuration')

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Server Info --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Server Information</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-600">Hostname</p>
                <p class="font-medium text-gray-900">{{ $serverInfo['hostname'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600">OS Version</p>
                <p class="font-medium text-gray-900">{{ $serverInfo['os'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600">PHP Version</p>
                <p class="font-medium text-gray-900">{{ PHP_VERSION }}</p>
            </div>
            <div>
                <p class="text-gray-600">Laravel Version</p>
                <p class="font-medium text-gray-900">{{ app()->version() }}</p>
            </div>
            <div>
                <p class="text-gray-600">Docker Version</p>
                <p class="font-medium text-gray-900">{{ $serverInfo['docker_version'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600">Server Uptime</p>
                <p class="font-medium text-gray-900">{{ $serverInfo['uptime'] ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- PHP Configuration --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">PHP Configuration</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.server.update') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Upload Size</label>
                    <input type="text" name="upload_max_filesize" value="{{ old('upload_max_filesize', config('kbpanel.php.upload_max_filesize', '256M')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">e.g., 256M, 512M</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max POST Size</label>
                    <input type="text" name="post_max_size" value="{{ old('post_max_size', config('kbpanel.php.post_max_size', '256M')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">e.g., 256M, 512M</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Memory Limit</label>
                    <input type="text" name="memory_limit" value="{{ old('memory_limit', config('kbpanel.php.memory_limit', '512M')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Execution Time</label>
                    <input type="text" name="max_execution_time" value="{{ old('max_execution_time', config('kbpanel.php.max_execution_time', '300')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Seconds</p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Save PHP Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Docker Configuration --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Docker Defaults</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.docker.update') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default PHP Version</label>
                    <select name="default_php_version" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="8.2" {{ config('kbpanel.defaults.phpversion') === '8.2' ? 'selected' : '' }}>PHP 8.2</option>
                        <option value="8.3" {{ config('kbpanel.defaults.phpversion') === '8.3' ? 'selected' : '' }}>PHP 8.3</option>
                        <option value="8.1" {{ config('kbpanel.defaults.phpversion') === '8.1' ? 'selected' : '' }}>PHP 8.1</option>
                        <option value="8.0" {{ config('kbpanel.defaults.phpversion') === '8.0' ? 'selected' : '' }}>PHP 8.0</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Web Server</label>
                    <select name="default_web_server" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="nginx">Nginx</option>
                        <option value="apache">Apache</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Docker Network Name</label>
                <input type="text" name="docker_network" value="{{ config('kbpanel.docker.network', 'kbpanel-net') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Save Docker Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Backup Settings --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Backup Configuration</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.backup.update') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Backup Retention (Days)</label>
                <input type="number" name="backup_retention" value="{{ config('kbpanel.backup.retention_days', 30) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Automatic Backup Schedule</label>
                <select name="backup_schedule" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="disabled">Disabled</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Save Backup Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection