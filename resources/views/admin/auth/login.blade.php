<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ $appSettings['app_name'] ?? 'Admin' }}</title>
    
    {{-- Vite CSS --}}
    @vite(['resources/css/landing.css'])
    
    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen">
    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-700 via-green-600 to-green-800 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-64 h-64 border-2 border-white rounded-full"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 border-2 border-white rounded-full"></div>
                <div class="absolute top-1/2 left-1/3 w-48 h-48 border-2 border-white rounded-full"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-20 w-full">
                <!-- Logo -->
                <div class="flex items-center gap-3 mb-8">
                    @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                        <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-14 h-14 rounded-xl shadow-lg">
                    @else
                        <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-white text-2xl font-bold">{{ substr($appSettings['app_name'] ?? 'F', 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="text-white text-2xl font-bold">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</span>
                </div>

                <!-- Main Text -->
                <h1 class="text-4xl xl:text-5xl font-bold text-white leading-tight mb-6">
                    Welcome to<br>
                    <span class="text-green-200">Admin Portal</span>
                </h1>
                
                <p class="text-lg text-green-100 mb-10 max-w-md">
                    {{ $appSettings['app_description'] ?? 'Connect & Collaborate' }}. Manage your platform, users, and content from one powerful dashboard.
                </p>

                <!-- Features List -->
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                            <i class="ph-bold ph-users text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white font-medium">User Management</p>
                            <p class="text-green-200 text-sm">Manage all users and permissions</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                            <i class="ph-bold ph-chart-line-up text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white font-medium">Analytics Dashboard</p>
                            <p class="text-green-200 text-sm">Real-time insights and reports</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                            <i class="ph-bold ph-gear-six text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white font-medium">System Settings</p>
                            <p class="text-green-200 text-sm">Configure your application</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex gap-8 mt-12 pt-8 border-t border-white/20">
                    <div>
                        <p class="text-3xl font-bold text-white">250K+</p>
                        <p class="text-green-200 text-sm">Active Users</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-white">10M+</p>
                        <p class="text-green-200 text-sm">Messages</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-white">99.9%</p>
                        <p class="text-green-200 text-sm">Uptime</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
                    @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                        <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-12 h-12 rounded-xl">
                    @else
                        <div class="w-12 h-12 bg-green-700 rounded-xl flex items-center justify-center">
                            <span class="text-white text-xl font-bold">{{ substr($appSettings['app_name'] ?? 'A', 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="text-gray-900 text-xl font-bold">{{ $appSettings['app_name'] ?? 'Admin' }}</span>
                </div>

                <!-- Back to Home -->
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-green-700 text-sm font-medium mb-8 transition-colors">
                    <i class="ph ph-arrow-left"></i>
                    Back to Home
                </a>

                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Sign in to Admin</h2>
                    <p class="text-gray-500">Enter your credentials to access the dashboard</p>
                </div>

                <!-- Alert Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-xl flex items-start gap-3">
                        <i class="ph-bold ph-check-circle text-green-600 text-xl mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-green-800">Success</p>
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                        <i class="ph-bold ph-warning-circle text-red-600 text-xl mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-red-800">Error</p>
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ph ph-envelope text-gray-400"></i>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 focus:bg-white transition-all text-sm @error('email') border-red-300 bg-red-50 @enderror"
                                   placeholder="admin@example.com"
                                   required
                                   autofocus>
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="ph ph-warning-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ph ph-lock text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   id="password" 
                                   name="password"
                                   class="w-full pl-11 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 focus:bg-white transition-all text-sm @error('password') border-red-300 bg-red-50 @enderror"
                                   placeholder="Enter your password"
                                   required>
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <i class="ph ph-eye text-gray-400 hover:text-gray-600 transition-colors" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="ph ph-warning-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-green-700 hover:text-green-800 font-medium">Forgot password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-green-700 hover:bg-green-800 text-white font-semibold rounded-xl transition-all shadow-lg shadow-green-700/30 hover:shadow-xl hover:-translate-y-0.5">
                        <i class="ph-bold ph-sign-in text-lg"></i>
                        Sign In
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-gray-50 text-gray-500">Secure Login</span>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="bg-gray-100 rounded-xl p-4 flex items-start gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="ph-bold ph-shield-check text-green-700"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Protected Access</p>
                        <p class="text-xs text-gray-500 mt-1">This area is restricted to authorized administrators only. All login attempts are logged.</p>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-400 mt-8">
                    &copy; {{ date('Y') }} {{ $appSettings['app_name'] ?? 'Admin' }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('ph-eye');
                toggleIcon.classList.add('ph-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('ph-eye-slash');
                toggleIcon.classList.add('ph-eye');
            }
        }
    </script>
</body>
</html>
