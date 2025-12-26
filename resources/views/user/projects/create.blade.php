@extends('layouts.user')

@section('page-title', 'Create New Project')
@section('page-subtitle', 'Deploy a new Laravel or WordPress application')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Choose Your Application Type</h3>
            <p class="text-sm text-gray-600">Select the type of project you want to deploy</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Laravel Option -->
            <div id="laravelOption" class="project-type-card cursor-pointer border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 hover:bg-blue-50 transition" data-type="laravel">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-red-50 rounded-lg">
                        <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.642 5.43a.364.364 0 0 1 .014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 0 1-.188.326L9.93 23.949a.316.316 0 0 1-.066.027c-.008.002-.016.008-.024.01a.348.348 0 0 1-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 0 1-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 0 1 .023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 0 1 .375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 0 1 .024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.013-.012.027-.023.041-.032h.001l4.513-2.598a.375.375 0 0 1 .375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.01.02.018.04.024.06.006.01.01.02.013.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.039-.01-.012-.021-.023-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 0 1-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 ml-3">Laravel Application</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Deploy a new Laravel application with automatic setup, database provisioning, and one-click deployment</p>
                <ul class="text-xs text-gray-500 space-y-1">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Multiple Laravel versions (8.x - 12.x)
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Automatic Composer & NPM setup
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Git integration & auto-deploy
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Staging environment support
                    </li>
                </ul>
            </div>
            
            <!-- WordPress Option -->
            <div id="wordpressOption" class="project-type-card cursor-pointer border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 hover:bg-blue-50 transition" data-type="wordpress">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21.469 0.014c-0.028-0.006-0.056-0.009-0.084-0.009-0.001 0-0.003 0-0.004 0h-18.759c-0.029 0-0.057 0.003-0.084 0.009l0.003-0c-0.017 0.003-0.032 0.008-0.046 0.015l0.002-0.001c-0.043 0.018-0.079 0.043-0.108 0.074l-0 0c-0.029 0.032-0.050 0.071-0.062 0.114l-0 0.002c-0.010 0.039-0.015 0.084-0.015 0.130 0 0.001 0 0.002 0 0.003v-0 23.342c-0.000 0.002-0.000 0.004-0.000 0.006 0 0.046 0.006 0.091 0.016 0.133l-0.001-0.004c0.012 0.046 0.033 0.086 0.062 0.119l-0-0c0.029 0.032 0.065 0.057 0.106 0.074l0.002 0.001c0.043 0.018 0.093 0.027 0.145 0.027 0.001 0 0.003 0 0.004-0h18.759c0.001 0 0.003 0 0.004 0 0.052 0 0.102-0.010 0.147-0.028l-0.003 0.001c0.043-0.018 0.079-0.043 0.108-0.074l0-0c0.029-0.032 0.050-0.071 0.062-0.114l0-0.002c0.010-0.039 0.015-0.084 0.015-0.130 0-0.001 0-0.002-0-0.003v0-23.342c0.000-0.002 0.000-0.004 0.000-0.006 0-0.046-0.006-0.091-0.016-0.133l0.001 0.004c-0.012-0.046-0.033-0.086-0.062-0.119l0 0c-0.029-0.032-0.065-0.057-0.106-0.074l-0.002-0.001c-0.012-0.005-0.026-0.009-0.041-0.013l-0.001-0z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 ml-3">WordPress Site</h4>
                </div>
                <p class="text-sm text-gray-600 mb-4">Deploy WordPress with automatic installation, database setup, and SSL certificate configuration</p>
                <ul class="text-xs text-gray-500 space-y-1">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Latest WordPress version
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        One-click installation
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Automatic SSL with Let's Encrypt
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        WP-CLI pre-installed
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="text-center">
            <p class="text-sm text-gray-600">Need help choosing? <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Compare application types</a></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectTypeCards = document.querySelectorAll('.project-type-card');
    
    projectTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            
            if (type === 'laravel') {
                window.location.href = '{{ route("user.deployments.laravel") }}';
            } else if (type === 'wordpress') {
                window.location.href = '{{ route("user.deployments.wordpress") }}';
            }
        });
        
        card.addEventListener('mouseenter', function() {
            this.classList.add('scale-105');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('scale-105');
        });
    });
});
</script>
@endpush
@endsection