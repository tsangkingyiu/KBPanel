<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'KBPanel') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            {{-- Logo --}}
            <div class="text-center">
                <h2 class="text-4xl font-bold text-blue-600">KBPanel</h2>
                <p class="mt-2 text-sm text-gray-600">Multi-Tenant Web Hosting Control Panel</p>
            </div>

            {{-- Login Card --}}
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-6">
                    <h3 class="text-2xl font-semibold text-gray-900 text-center">Sign in to your account</h3>
                </div>

                {{-- Session Status --}}
                @if(session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Errors --}}
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <p class="font-medium">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               value="{{ old('email') }}"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-colors @error('email') border-red-500 @enderror">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-colors @error('password') border-red-500 @enderror">
                    </div>

                    {{-- Remember Me & Forgot Password --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>

                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    {{-- Submit Button --}}
                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Sign in
                        </button>
                    </div>
                </form>

                {{-- Register Link --}}
                @if(Route::has('register'))
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-800">
                                Sign up
                            </a>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} KBPanel. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>