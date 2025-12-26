@extends('layouts.user')

@section('title', 'Create New Project')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('user.projects.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Project</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Deploy a new Laravel or WordPress application</p>
            </div>
        </div>
    </div>

    <!-- Project Type Selection -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Select Project Type</h2>
        
        <form method="GET" action="{{ route('user.deployments.select') }}" id="projectTypeForm">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Laravel Option -->
                <label for="type-laravel" class="cursor-pointer">
                    <input type="radio" id="type-laravel" name="type" value="laravel" class="hidden peer" required>
                    <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-blue-500 peer-checked:border-blue-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all">
                        <div class="flex items-center justify-center mb-4">
                            <div class="p-4 bg-red-100 dark:bg-red-900 rounded-full">
                                <svg class="w-12 h-12 text-red-600 dark:text-red-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-2">Laravel Application</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Modern PHP framework for web artisans. Perfect for APIs and full-stack applications.</p>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                PHP 8.2 Support
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                MySQL Database
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Git Integration
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Staging Environment
                            </div>
                        </div>
                    </div>
                </label>

                <!-- WordPress Option -->
                <label for="type-wordpress" class="cursor-pointer">
                    <input type="radio" id="type-wordpress" name="type" value="wordpress" class="hidden peer" required>
                    <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-blue-500 peer-checked:border-blue-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all">
                        <div class="flex items-center justify-center mb-4">
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-12 h-12 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.158 12.786L9.46 20.625c1.355.295 2.785.456 4.254.456 1.749 0 3.429-.261 5.007-.737a.386.386 0 0 1-.061-.11l-6.502-7.448zm-5.417-.645L3.46 3.898c-1.647 2.013-2.645 4.567-2.645 7.342 0 3.735 1.77 7.058 4.518 9.163l3.408-8.262zm14.512-6.468c-.996-1.6-2.497-2.762-4.273-3.323a11.73 11.73 0 0 0-4.48-.265c1.414 1.866 2.519 4.109 3.176 6.488l1.577 5.269 5-7.169zM12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-2">WordPress Site</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Popular CMS for blogs and websites. Easy to manage with plugins and themes.</p>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Latest WordPress
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Plugin Support
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Auto SSL
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Easy Backups
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            @error('type')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <!-- Continue Button -->
            <div class="mt-8 flex justify-end">
                <button type="submit" 
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 inline-flex items-center">
                    Continue
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Info Section -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200">What happens next?</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>After selecting your project type, you'll be guided through a step-by-step wizard to configure:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Project name and domain</li>
                        <li>PHP version and web server (Apache/Nginx)</li>
                        <li>Database configuration</li>
                        <li>SSL certificate setup</li>
                        <li>Git repository connection (optional)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Form validation and enhancement
    document.getElementById('projectTypeForm').addEventListener('submit', function(e) {
        const selectedType = document.querySelector('input[name="type"]:checked');
        if (!selectedType) {
            e.preventDefault();
            alert('Please select a project type');
        }
    });
</script>
@endpush