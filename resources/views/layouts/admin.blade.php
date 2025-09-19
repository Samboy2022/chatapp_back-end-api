<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - ChatWave</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Icons8 Illustrations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        whatsapp: {
                            primary: '#25D366',
                            light: '#DCF8C6',
                            dark: '#128C7E',
                            text: '#075E54'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .sidebar-gradient {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        }
        .whatsapp-shadow {
            box-shadow: 0 4px 6px -1px rgba(37, 211, 102, 0.1), 0 2px 4px -1px rgba(37, 211, 102, 0.06);
        }
        .nav-item-active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 3px solid #DCF8C6;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <nav class="w-64 sidebar-gradient text-white shadow-xl">
            <div class="flex flex-col h-full">
                <!-- Logo Section -->
                <div class="p-6 border-b border-white border-opacity-20">
                    <div class="flex items-center space-x-3">
                        <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                            <i class="fab fa-whatsapp text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">ChatWave</h3>
                            <p class="text-xs text-green-100">Admin Panel</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="flex-1 py-4 custom-scrollbar overflow-y-auto">
                    <ul class="space-y-1 px-4">
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.chats.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.chats.index') }}">
                                <i class="fas fa-comments"></i>
                                <span>Chats</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.messages.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.messages.index') }}">
                                <i class="fas fa-envelope"></i>
                                <span>Messages</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.statuses.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.statuses.index') }}">
                                <i class="fas fa-circle"></i>
                                <span>Status Updates</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.calls.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.calls.index') }}">
                                <i class="fas fa-phone"></i>
                                <span>Calls</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.reports.index') }}">
                                <i class="fas fa-chart-bar"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                        <!-- <li>
                            <a class="nav-link {{ request()->routeIs('admin.api-documentation.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.api-documentation.index') }}">
                                <i class="fas fa-code"></i>
                                <span>API Docs</span>
                            </a>
                        </li> -->
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.realtime-settings.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.realtime-settings.index') }}">
                                <i class="fas fa-broadcast-tower"></i>
                                <span>Realtime</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'nav-item-active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200"
                               href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Bottom Section -->
                <div class="p-4 border-t border-white border-opacity-20">
                    <!-- Website Link -->
                    <!-- <a href="{{ route('home') }}"
                       target="_blank"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 transition-all duration-200 mb-2">
                        <i class="fas fa-external-link-alt"></i>
                        <span>View Website</span>
                    </a> -->

                    <!-- User Profile -->
                    @if(session('admin_user'))
                    <div class="bg-white bg-opacity-10 rounded-lg p-3 mb-3">
                        <div class="flex items-center space-x-3">
                            @if(session('admin_user')['avatar_url'])
                                <img src="{{ session('admin_user')['avatar_url'] }}"
                                     alt="Admin"
                                     class="w-10 h-10 rounded-full">
                            @else
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ session('admin_user')['name'] }}</div>
                                <div class="text-xs text-green-100">{{ session('admin_user')['email'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-white text-opacity-80 hover:text-white hover:bg-red-500 hover:bg-opacity-20 transition-all duration-200">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 overflow-y-auto">
            <div class="container mx-auto px-6 py-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-gray-600 mt-2">Manage your ChatWave application</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        @yield('toolbar-buttons')
                        <!-- Mobile Menu Toggle (for responsive design) -->
                        <button class="md:hidden bg-whatsapp-primary text-white p-2 rounded-lg">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800">{{ session('success') }}</p>
                            </div>
                            <div class="ml-auto">
                                <button type="button" class="text-green-400 hover:text-green-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-800">{{ session('error') }}</p>
                            </div>
                            <div class="ml-auto">
                                <button type="button" class="text-red-400 hover:text-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-400"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-red-800">Please fix the following errors:</p>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="ml-auto">
                                <button type="button" class="text-red-400 hover:text-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Breadcrumb (optional) -->
                @yield('breadcrumb')

                <!-- Main Content Area -->
                <div class="space-y-6">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Chart.js for dashboard charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom JavaScript for enhanced UX -->
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Remove active class from all links
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('nav-item-active'));
                // Add active class to clicked link
                if (this.getAttribute('href') !== '#') {
                    this.classList.add('nav-item-active');
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Mobile sidebar toggle (for responsive design)
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        }

        // Add loading states for forms
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                        submitBtn.disabled = true;
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>