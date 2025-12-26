@extends('layouts.user')

@section('page-title', 'Create Staging Environment')
@section('page-subtitle', 'Set up a new staging environment for testing')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">New Staging Environment</h3>
            <p class="text-sm text-gray-600 mt-1">Create an isolated environment for testing changes before deploying to production</p>
        </div>

        <form method="POST" action="{{ route('user.staging.store') }}" class="p-6 space-y-6">
            @csrf

            <!-- Project Selection -->
            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Source Project <span class="text-red-500">*</span>
                </label>
                <select name="project_id" id="project_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a project...</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }} ({{ $project->type }})
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">The project you want to create a staging environment for</p>
            </div>

            <!-- Environment Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Environment Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       placeholder="e.g., Feature Testing, QA Environment"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Subdomain -->
            <div>
                <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-2">
                    Subdomain <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center">
                    <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}" required
                           placeholder="feature-test"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-700">
                        .staging.kbpanel.dev
                    </span>
                </div>
                @error('subdomain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Only lowercase letters, numbers, and hyphens allowed</p>
            </div>

            <!-- Git Branch -->
            <div>
                <label for="branch" class="block text-sm font-medium text-gray-700 mb-2">
                    Git Branch <span class="text-red-500">*</span>
                </label>
                <input type="text" name="branch" id="branch" value="{{ old('branch', 'develop') }}" required
                       placeholder="develop"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('branch')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">The Git branch to deploy to this staging environment</p>
            </div>

            <!-- PHP Version -->
            <div>
                <label for="php_version" class="block text-sm font-medium text-gray-700 mb-2">
                    PHP Version <span class="text-red-500">*</span>
                </label>
                <select name="php_version" id="php_version" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="8.3" {{ old('php_version', '8.3') == '8.3' ? 'selected' : '' }}>PHP 8.3</option>
                    <option value="8.2" {{ old('php_version') == '8.2' ? 'selected' : '' }}>PHP 8.2</option>
                    <option value="8.1" {{ old('php_version') == '8.1' ? 'selected' : '' }}>PHP 8.1</option>
                    <option value="8.0" {{ old('php_version') == '8.0' ? 'selected' : '' }}>PHP 8.0</option>
                </select>
                @error('php_version')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Auto Deploy -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="auto_deploy" id="auto_deploy" value="1" 
                           {{ old('auto_deploy') ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="ml-3">
                    <label for="auto_deploy" class="text-sm font-medium text-gray-700">
                        Enable Auto-Deploy
                    </label>
                    <p class="text-sm text-gray-500">Automatically deploy when changes are pushed to the selected branch</p>
                </div>
            </div>

            <!-- Environment Variables -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Environment Variables (Optional)
                </label>
                <div id="env-vars-container" class="space-y-2">
                    <div class="flex gap-2 env-var-row">
                        <input type="text" name="env_keys[]" placeholder="Key" 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="text" name="env_values[]" placeholder="Value" 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" onclick="this.parentElement.remove()" 
                                class="px-3 py-2 text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addEnvVar()" 
                        class="mt-2 text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Another Variable
                </button>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('user.staging.index') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create Staging Environment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addEnvVar() {
    const container = document.getElementById('env-vars-container');
    const row = document.createElement('div');
    row.className = 'flex gap-2 env-var-row';
    row.innerHTML = `
        <input type="text" name="env_keys[]" placeholder="Key" 
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <input type="text" name="env_values[]" placeholder="Value" 
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <button type="button" onclick="this.parentElement.remove()" 
                class="px-3 py-2 text-red-600 hover:text-red-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    `;
    container.appendChild(row);
}
</script>
@endsection
