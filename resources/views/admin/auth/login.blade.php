<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ChatWave Admin - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <!-- Header -->
    <div class="fixed top-0 left-0 right-0 bg-white shadow-sm border-b border-gray-200 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-500 text-white p-2 rounded-lg">
                        <i class="fas fa-comments text-xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">ChatWave Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Secure Admin Portal</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full max-w-md mt-16">
        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-8 py-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-4">
                    <i class="fas fa-shield-alt text-2xl text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-blue-100">Sign in to your admin account</p>
            </div>

            <!-- Form -->
            <div class="px-8 py-8">
                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <span class="text-green-800 text-sm">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-400 mr-3"></i>
                            <span class="text-red-800 text-sm">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-300 @enderror"
                                   placeholder="Enter your email"
                                   required
                                   autofocus>
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
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
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password') border-red-300 @enderror"
                                   placeholder="Enter your password"
                                   required>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2 shadow-sm">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Sign In to Admin Panel</span>
                        </button>
                    </div>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <span class="text-sm font-medium text-gray-700">Demo Credentials</span>
                    </div>
                    <div class="text-xs text-gray-600 space-y-1">
                        <div><strong>Email:</strong> admin@chatwave.com</div>
                        <div><strong>Password:</strong> password</div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="mt-6 text-center">
                    <div class="flex items-center justify-center space-x-2 text-sm text-gray-500">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure Admin Access Only</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
                ChatWave Admin Panel • Version 1.0
            </p>
        </div>
    </div>
</body>
</html> 