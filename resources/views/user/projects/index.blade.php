@extends('layouts.user')

@section('page-title', 'My Projects')
@section('page-subtitle', 'Manage all your web hosting projects')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div class="flex items-center space-x-4">
        <div class="relative">
            <input type="text" id="searchProjects" 
                   placeholder="Search projects..." 
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        
        <select id="filterType" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Types</option>
            <option value="laravel">Laravel</option>
            <option value="wordpress">WordPress</option>
        </select>
        
        <select id="filterStatus" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="stopped">Stopped</option>
            <option value="deploying">Deploying</option>
            <option value="error">Error</option>
        </select>
    </div>
    
    <a href="{{ route('user.projects.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        New Project
    </a>
</div>

<!-- Projects Grid View -->
<div id="projectsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($projects ?? [] as $project)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition project-card" 
         data-type="{{ $project->type }}" 
         data-status="{{ $project->status }}">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    @if($project->type === 'laravel')
                    <div class="p-2 bg-red-50 rounded-lg">
                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.642 5.43a.364.364 0 0 1 .014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 0 1-.188.326L9.93 23.949a.316.316 0 0 1-.066.027c-.008.002-.016.008-.024.01a.348.348 0 0 1-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 0 1-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 0 1 .023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 0 1 .375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 0 1 .024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.013-.012.027-.023.041-.032h.001l4.513-2.598a.375.375 0 0 1 .375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.01.02.018.04.024.06.006.01.01.02.013.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.039-.01-.012-.021-.023-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 0 1-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"/>
                        </svg>
                    </div>
                    @else
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21.469 0.014c-0.028-0.006-0.056-0.009-0.084-0.009-0.001 0-0.003 0-0.004 0h-18.759c-0.029 0-0.057 0.003-0.084 0.009l0.003-0c-0.017 0.003-0.032 0.008-0.046 0.015l0.002-0.001c-0.043 0.018-0.079 0.043-0.108 0.074l-0 0c-0.029 0.032-0.050 0.071-0.062 0.114l-0 0.002c-0.010 0.039-0.015 0.084-0.015 0.130 0 0.001 0 0.002 0 0.003v-0 23.342c-0.000 0.002-0.000 0.004-0.000 0.006 0 0.046 0.006 0.091 0.016 0.133l-0.001-0.004c0.012 0.046 0.033 0.086 0.062 0.119l-0-0c0.029 0.032 0.065 0.057 0.106 0.074l0.002 0.001c0.043 0.018 0.093 0.027 0.145 0.027 0.001 0 0.003 0 0.004-0h18.759c0.001 0 0.003 0 0.004 0 0.052 0 0.102-0.010 0.147-0.028l-0.003 0.001c0.043-0.018 0.079-0.043 0.108-0.074l0-0c0.029-0.032 0.050-0.071 0.062-0.114l0-0.002c0.010-0.039 0.015-0.084 0.015-0.130 0-0.001 0-0.002-0-0.003v0-23.342c0.000-0.002 0.000-0.004 0.000-0.006 0-0.046-0.006-0.091-0.016-0.133l0.001 0.004c-0.012-0.046-0.033-0.086-0.062-0.119l0 0c-0.029-0.032-0.065-0.057-0.106-0.074l-0.002-0.001c-0.012-0.005-0.026-0.009-0.041-0.013l-0.001-0z"/>
                        </svg>
                    </div>
                    @endif
                </div>
                
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($project->status === 'active') bg-green-100 text-green-800
                    @elseif($project->status === 'stopped') bg-red-100 text-red-800
                    @elseif($project->status === 'deploying') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($project->status) }}
                </span>
            </div>
            
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $project->name }}</h3>
            <p class="text-sm text-gray-600 mb-4">
                <a href="http://{{ $project->domain }}" target="_blank" class="hover:text-blue-600 flex items-center">
                    {{ $project->domain }}
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            </p>
            
            <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                <span>{{ ucfirst($project->type) }} @if($project->laravel_version){{ $project->laravel_version }}@endif</span>
                <span>PHP {{ $project->php_version }}</span>
                <span>{{ $project->web_server }}</span>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                    <span>Disk Usage</span>
                    <span class="font-medium">{{ number_format($project->disk_usage ?? 0) }} MB</span>
                </div>
                <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600" style="width: {{ min(100, ($project->disk_usage ?? 0) / 1000 * 100) }}%"></div>
                </div>
            </div>
            
            <div class="mt-4 flex space-x-2">
                <a href="{{ route('user.projects.show', $project) }}" 
                   class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Manage
                </a>
                
                @if($project->status === 'active')
                <form action="{{ route('user.projects.stop', $project) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 hover:bg-red-50 transition">
                        Stop
                    </button>
                </form>
                @else
                <form action="{{ route('user.projects.start', $project) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 border border-green-300 rounded-md text-sm font-medium text-green-700 hover:bg-green-50 transition">
                        Start
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full">
        <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No projects found</h3>
            <p class="mt-2 text-sm text-gray-500">Get started by creating your first project.</p>
            <div class="mt-6">
                <a href="{{ route('user.projects.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Project
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if(isset($projects) && $projects->hasPages())
<div class="mt-6">
    {{ $projects->links() }}
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchProjects');
    const filterType = document.getElementById('filterType');
    const filterStatus = document.getElementById('filterStatus');
    const projectCards = document.querySelectorAll('.project-card');
    
    function filterProjects() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = filterType.value;
        const selectedStatus = filterStatus.value;
        
        projectCards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const type = card.dataset.type;
            const status = card.dataset.status;
            
            const matchesSearch = text.includes(searchTerm);
            const matchesType = !selectedType || type === selectedType;
            const matchesStatus = !selectedStatus || status === selectedStatus;
            
            if (matchesSearch && matchesType && matchesStatus) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', filterProjects);
    filterType.addEventListener('change', filterProjects);
    filterStatus.addEventListener('change', filterProjects);
});
</script>
@endpush
@endsection