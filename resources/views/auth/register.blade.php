<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name', 'KBPanel') }}</title>
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

            {{-- Register Card --}}
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-6">
                    <h3 class="text-2xl font-semibold text-gray-900 text-center">Create your account</h3>
                </div>

                {{-- Errors --}}
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <p class="font-medium mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input id="name" name="name" type="text" autocomplete="name" required 
                               value="{{ old('name') }}"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-colors @error('name') border-red-500 @enderror">
                    </div>

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
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-colors @error('password') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                    </div>

                    {{-- Password Confirmation --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-colors">
                    </div>

                    {{-- Terms & Conditions --}}
                    <div class="flex items-start">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 hover:text-blue-800">Terms of Service</a> and <a href="#" class="text-blue-600 hover:text-blue-800">Privacy Policy</a>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Create Account
                        </button>
                    </div>
                </form>

                {{-- Login Link --}}
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-800">
                            Sign in
                        </a>
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} KBPanel. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>