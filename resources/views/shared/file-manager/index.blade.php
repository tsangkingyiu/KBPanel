@extends('layouts.user')

@section('page-title', 'File Manager')
@section('page-subtitle', $project->name)

@section('content')
<div class="space-y-6">
    <!-- Toolbar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <!-- Breadcrumb -->
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="?path=/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Root
                        </a>
                    </li>
                    @foreach($pathParts as $index => $part)
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="?path={{ implode('/', array_slice($pathParts, 0, $index + 1)) }}" 
                               class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                                {{ $part }}
                            </a>
                        </div>
                    </li>
                    @endforeach
                </ol>
            </nav>

            <!-- Actions -->
            <div class="flex items-center space-x-2">
                <button onclick="uploadFile()" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload
                </button>
                <button onclick="createFolder()" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    New Folder
                </button>
                <button onclick="createFile()" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New File
                </button>
            </div>
        </div>
    </div>

    <!-- File List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if($currentPath !== '/')
                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="navigateUp()">
                        <td class="px-6 py-4"></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-900">..</span>
                            </div>
                        </td>
                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500">Parent Directory</td>
                    </tr>
                    @endif

                    @foreach($files as $file)
                    <tr class="hover:bg-gray-50 transition-colors {{ $file['type'] === 'directory' ? 'cursor-pointer' : '' }}" 
                        @if($file['type'] === 'directory') onclick="navigateTo('{{ $file['path'] }}')" @endif>
                        <td class="px-6 py-4">
                            <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500" 
                                   onclick="event.stopPropagation()">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($file['type'] === 'directory')
                                    <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $file['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $file['type'] === 'directory' ? '-' : formatBytes($file['size']) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $file['modified']->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">
                            {{ $file['permissions'] }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium space-x-2" onclick="event.stopPropagation()">
                            @if($file['type'] === 'file')
                                <button onclick="editFile('{{ $file['path'] }}')" class="text-blue-600 hover:text-blue-900">Edit</button>
                                <button onclick="downloadFile('{{ $file['path'] }}')" class="text-green-600 hover:text-green-900">Download</button>
                            @endif
                            <button onclick="renameItem('{{ $file['path'] }}')" class="text-yellow-600 hover:text-yellow-900">Rename</button>
                            <button onclick="deleteItem('{{ $file['path'] }}')" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    @endforeach

                    @if(count($files) === 0)
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                            <p class="text-gray-500">This folder is empty</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Storage Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900">Storage Usage</h3>
            <span class="text-sm text-gray-600">{{ formatBytes($storageUsed) }} / {{ formatBytes($storageLimit) }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($storageUsed / $storageLimit) * 100 }}%"></div>
        </div>
    </div>
</div>

<script>
function navigateTo(path) {
    window.location.href = `?path=${encodeURIComponent(path)}`;
}

function navigateUp() {
    const currentPath = '{{ $currentPath }}';
    const parts = currentPath.split('/').filter(p => p);
    parts.pop();
    const newPath = '/' + parts.join('/');
    navigateTo(newPath);
}

function uploadFile() {
    alert('Upload file dialog - to be implemented with Vue.js component');
}

function createFolder() {
    const name = prompt('Enter folder name:');
    if (name) {
        // Send create folder request
    }
}

function createFile() {
    const name = prompt('Enter file name:');
    if (name) {
        // Send create file request
    }
}

function editFile(path) {
    window.location.href = `/user/projects/{{ $project->id }}/files/edit?path=${encodeURIComponent(path)}`;
}

function downloadFile(path) {
    window.location.href = `/user/projects/{{ $project->id }}/files/download?path=${encodeURIComponent(path)}`;
}

function renameItem(path) {
    const newName = prompt('Enter new name:');
    if (newName) {
        // Send rename request
    }
}

function deleteItem(path) {
    if (confirm('Are you sure you want to delete this item?')) {
        // Send delete request
    }
}
</script>
@endsection
