<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatWave - Modern Messaging Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-600 rounded-lg p-2">
                        <i class="fas fa-comments text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">ChatWave</h1>
                        <p class="text-sm text-gray-600">Messaging Platform</p>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#features" class="text-gray-600 hover:text-blue-600 transition-colors">Features</a>
                    <a href="#stats" class="text-gray-600 hover:text-blue-600 transition-colors">Stats</a>
                    <a href="#download" class="text-gray-600 hover:text-blue-600 transition-colors">Download</a>
                    <a href="{{ route('admin.dashboard') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Admin Panel
                    </a>
                </div>
                <div class="md:hidden">
                    <button class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="text-white">
                    <h1 class="text-4xl lg:text-6xl font-bold mb-6">
                        Connect, Chat & Share with
                        <span class="block text-yellow-300">Everyone</span>
                    </h1>
                    <p class="text-lg lg:text-xl mb-8 opacity-90 max-w-lg">
                        Experience the future of messaging with our modern, secure, and feature-rich chat platform.
                        Connect with friends, family, and colleagues like never before.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <button class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            <i class="fas fa-download mr-2"></i>Download App
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="bg-blue-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-400 transition-colors">
                            <i class="fas fa-cog mr-2"></i>Admin Panel
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 pt-8 border-t border-white border-opacity-20">
                        <div class="text-center">
                            <div class="text-2xl font-bold mb-1">{{ number_format($stats['total_users'] ?? 0) }}+</div>
                            <div class="text-sm opacity-75">Active Users</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold mb-1">{{ number_format($stats['total_messages'] ?? 0) }}+</div>
                            <div class="text-sm opacity-75">Messages Sent</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold mb-1">{{ number_format($stats['total_chats'] ?? 0) }}+</div>
                            <div class="text-sm opacity-75">Active Chats</div>
                        </div>
                    </div>
                </div>

                <!-- Phone Mockup -->
                <div class="relative">
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-3xl p-8 max-w-sm mx-auto">
                        <div class="space-y-4">
                            <div class="bg-white bg-opacity-20 rounded-2xl p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">S</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white text-sm font-medium">Sarah</p>
                                        <p class="text-white text-xs opacity-75">Hey! How are you doing? 😊</p>
                                    </div>
                                    <span class="text-white text-xs opacity-50">2m</span>
                                </div>
                            </div>

                            <div class="bg-blue-500 rounded-2xl p-4 ml-12">
                                <p class="text-white text-sm">I'm great! Just checking out this new chat app 🚀</p>
                            </div>

                            <div class="bg-white bg-opacity-20 rounded-2xl p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">M</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white text-sm font-medium">Mike</p>
                                        <p class="text-white text-xs opacity-75">The video calls are crystal clear! 📹</p>
                                    </div>
                                    <span class="text-white text-xs opacity-50">1m</span>
                                </div>
                            </div>

                            <div class="bg-blue-500 rounded-2xl p-4 ml-12">
                                <p class="text-white text-sm">And the file sharing is so fast ⚡</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Powerful Features</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Everything you need for modern communication</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature Cards -->
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-3">Real-time Messaging</h3>
                    <p class="text-gray-600 text-center">Send and receive messages instantly with real-time delivery and read receipts.</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-video text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-3">Video & Voice Calls</h3>
                    <p class="text-gray-600 text-center">High-quality video and voice calling with crystal clear audio and HD video.</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-3">End-to-End Security</h3>
                    <p class="text-gray-600 text-center">Your conversations are protected with industry-standard encryption.</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-3">Group Chats</h3>
                    <p class="text-gray-600 text-center">Create group conversations with up to 256 members and advanced admin controls.</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-3">File Sharing</h3>
                    <p class="text-gray-600 text-center">Share photos, videos, documents, and any file type up to 100MB instantly.</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="bg-pink-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-circle text-pink-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-3">Status Updates</h3>
                    <p class="text-gray-600 text-center">Share your moments with 24-hour disappearing status updates and stories.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="py-16 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Growing Community</h2>
                <p class="text-lg text-gray-600">Join millions of users worldwide</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                    <div class="text-4xl font-bold text-blue-600 mb-2">{{ number_format($stats['total_users'] ?? 0) }}+</div>
                    <div class="text-gray-600 font-medium mb-1">Active Users</div>
                    <div class="text-sm text-gray-500">People using ChatWave daily</div>
                </div>

                <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                    <div class="text-4xl font-bold text-green-600 mb-2">{{ number_format($stats['total_messages'] ?? 0) }}+</div>
                    <div class="text-gray-600 font-medium mb-1">Messages Sent</div>
                    <div class="text-sm text-gray-500">Messages exchanged every day</div>
                </div>

                <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                    <div class="text-4xl font-bold text-purple-600 mb-2">{{ number_format($stats['total_chats'] ?? 0) }}+</div>
                    <div class="text-gray-600 font-medium mb-1">Active Chats</div>
                    <div class="text-sm text-gray-500">Ongoing conversations</div>
                </div>

                <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                    <div class="text-4xl font-bold text-orange-600 mb-2">99.9%</div>
                    <div class="text-gray-600 font-medium mb-1">Uptime</div>
                    <div class="text-sm text-gray-500">Reliable service 24/7</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section id="download" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Download ChatWave Today</h2>
                    <p class="text-lg text-gray-600 mb-8">
                        Get started with ChatWave and experience the future of messaging.
                        Available on all platforms with seamless synchronization.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center">
                            <i class="fab fa-apple mr-2"></i>App Store
                        </button>
                        <button class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center">
                            <i class="fab fa-google-play mr-2"></i>Google Play
                        </button>
                        <button class="bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors flex items-center justify-center">
                            <i class="fas fa-globe mr-2"></i>Web App
                        </button>
                    </div>
                </div>

                <div class="text-center">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-3xl p-8 inline-block">
                        <i class="fas fa-mobile-alt text-white text-8xl"></i>
                    </div>
                    <p class="text-gray-600 mt-4">Available on all devices</p>
                </div>
            </div>
        </div>
    </section>

    <!-- API Section -->
    <section class="py-16 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Developer Resources</h2>
            <p class="text-lg text-gray-600 mb-8">Build powerful applications with our comprehensive API</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="docs/api-documentation/new-api-docs/index.html" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-file-alt mr-2"></i>View API Documentation
                </a>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                    <i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <!-- Brand -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="bg-blue-600 rounded-lg p-2">
                            <i class="fas fa-comments text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">ChatWave</h3>
                            <p class="text-gray-400 text-sm">Modern messaging platform</p>
                        </div>
                    </div>
                    <p class="text-gray-400 mb-4">
                        The modern messaging platform that connects people worldwide with
                        secure, reliable, and feature-rich communication tools.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-linkedin text-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Product -->
                <div>
                    <h4 class="font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#download" class="hover:text-white transition-colors">Download</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="docs/api-documentation/new-api-docs/index.html" class="hover:text-white transition-colors">API</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Community</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Status</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Security</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    © {{ date('Y') }} ChatWave. All rights reserved. |
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Admin Panel</a>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
