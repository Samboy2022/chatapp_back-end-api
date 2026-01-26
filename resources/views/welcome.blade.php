<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appSettings['app_name'] ?? 'Farmers Network' }} - {{ $appSettings['app_description'] ?? 'Connect & Collaborate' }}</title>
    
    {{-- Vite CSS --}}
    @vite(['resources/css/landing.css'])
    
    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-2">
                    @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                        <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-8 h-8 rounded-lg">
                    @else
                        <div class="w-8 h-8 bg-green-700 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ substr($appSettings['app_name'] ?? 'F', 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="font-bold text-gray-900 text-lg">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-sm font-medium text-gray-600 hover:text-green-700 transition-colors">Features</a>
                    <a href="#stats" class="text-sm font-medium text-gray-600 hover:text-green-700 transition-colors">Community</a>
                    <a href="#download" class="text-sm font-medium text-gray-600 hover:text-green-700 transition-colors">Download</a>
                </div>

                <!-- CTA Button -->
                <div class="flex items-center space-x-3">
                    <a href="#download" class="flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white text-sm font-medium rounded-xl transition-all shadow-lg shadow-green-700/20">
                        <i class="ph ph-download-simple"></i>
                        Download Now
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-20 left-10 w-72 h-72 bg-green-200 rounded-full blur-3xl opacity-30 animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-green-300 rounded-full blur-3xl opacity-20 animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-100 rounded-full text-green-700 text-sm font-medium mb-6">
                        <i class="ph-fill ph-star"></i>
                        {{ $appSettings['landing_hero_badge'] ?? 'Trusted by 250,000+ farmers worldwide' }}
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                        @php
                            $title = $appSettings['landing_hero_title'] ?? 'Connect, Collaborate & Grow Together';
                            $highlight = $appSettings['landing_hero_highlight'] ?? 'Grow Together';
                            if ($highlight) {
                                $title = str_replace($highlight, '<span class="text-green-700">' . $highlight . '</span>', $title);
                            }
                        @endphp
                        {!! $title !!}
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 max-w-xl mx-auto lg:mx-0">
                        {{ $appSettings['landing_hero_description'] ?? 'Transform your farming experience with our cutting-edge communication platform. Connect with farmers worldwide, share knowledge, and build thriving agricultural communities.' }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#download" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-green-700 hover:bg-green-800 text-white font-semibold rounded-xl transition-all shadow-lg shadow-green-700/30 hover:shadow-xl hover:-translate-y-0.5">
                            <i class="ph-bold ph-download-simple text-lg"></i>
                            {{ $appSettings['landing_hero_cta_primary'] ?? 'Get Started Free' }}
                        </a>
                        <a href="#features" class="flex items-center justify-center gap-2 px-6 py-3.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-green-300 hover:text-green-700 transition-all">
                            <i class="ph ph-play-circle text-lg"></i>
                            {{ $appSettings['landing_hero_cta_secondary'] ?? 'Explore Features' }}
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center justify-center lg:justify-start gap-8 mt-12 pt-8 border-t border-gray-100">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $appSettings['landing_stat_users'] ?? '250K+' }}</p>
                            <p class="text-sm text-gray-500">{{ $appSettings['landing_stat_users_label'] ?? 'Active Users' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $appSettings['landing_stat_countries'] ?? '120+' }}</p>
                            <p class="text-sm text-gray-500">{{ $appSettings['landing_stat_countries_label'] ?? 'Countries' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $appSettings['landing_stat_rating'] ?? '4.9' }}</p>
                            <p class="text-sm text-gray-500">{{ $appSettings['landing_stat_rating_label'] ?? 'App Rating' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Hero Visual -->
                <div class="relative">
                    <div class="relative z-10">
                        <img src="{{ $appSettings['landing_hero_image'] ?? 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=600&h=500&fit=crop' }}" 
                             alt="Farming Community" 
                             class="rounded-2xl shadow-2xl w-full">
                    </div>
                    <!-- Floating Cards -->
                    <div class="absolute -top-4 -left-4 bg-white rounded-xl shadow-lg p-4 animate-float z-20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="ph-bold ph-users text-green-700"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">New Members</p>
                                <p class="text-lg font-bold text-gray-900">+1,234</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -right-4 bg-white rounded-xl shadow-lg p-4 animate-float-delay z-20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="ph-bold ph-chat-circle-dots text-green-700"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Messages Today</p>
                                <p class="text-lg font-bold text-gray-900">45.2K</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-100 rounded-full text-green-700 text-sm font-medium mb-4">
                    <i class="ph-bold ph-sparkle"></i>
                    {{ $appSettings['landing_features_badge'] ?? 'Powerful Features' }}
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">{{ $appSettings['landing_features_title'] ?? 'Everything You Need to Connect' }}</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $appSettings['landing_features_description'] ?? 'Our platform provides all the tools you need to communicate, collaborate, and grow your farming network.' }}</p>
            </div>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @for($i = 1; $i <= 6; $i++)
                @if(isset($appSettings["landing_feature_{$i}_title"]))
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-700 transition-colors">
                        <i class="ph-bold {{ $appSettings["landing_feature_{$i}_icon"] ?? 'ph-sparkle' }} text-2xl text-green-700 group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $appSettings["landing_feature_{$i}_title"] }}</h3>
                    <p class="text-gray-600 text-sm">{{ $appSettings["landing_feature_{$i}_description"] }}</p>
                </div>
                @endif
                @endfor
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="py-20 bg-green-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">{{ $appSettings['landing_community_title'] ?? 'Join Our Growing Community' }}</h2>
                <p class="text-lg text-green-100 max-w-2xl mx-auto">{{ $appSettings['landing_community_description'] ?? 'Thousands of farmers trust our platform every day to connect and collaborate.' }}</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/20">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold ph-users text-3xl text-white"></i>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1">{{ $appSettings['landing_stat_users'] ?? '250K+' }}</p>
                    <p class="text-green-100 text-sm">{{ $appSettings['landing_stat_users_label'] ?? 'Active Users' }}</p>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/20">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold ph-chat-circle-dots text-3xl text-white"></i>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1">{{ $appSettings['landing_stat_messages'] ?? '10M+' }}</p>
                    <p class="text-green-100 text-sm">{{ $appSettings['landing_stat_messages_label'] ?? 'Messages Sent' }}</p>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/20">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold ph-globe text-3xl text-white"></i>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1">{{ $appSettings['landing_stat_countries'] ?? '120+' }}</p>
                    <p class="text-green-100 text-sm">{{ $appSettings['landing_stat_countries_label'] ?? 'Countries' }}</p>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/20">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold ph-star text-3xl text-white"></i>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1">{{ $appSettings['landing_stat_rating'] ?? '4.9' }}</p>
                    <p class="text-green-100 text-sm">{{ $appSettings['landing_stat_rating_label'] ?? 'App Store Rating' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section id="download" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <div class="grid lg:grid-cols-2">
                    <!-- Content -->
                    <div class="p-8 lg:p-12 flex flex-col justify-center">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-100 rounded-full text-green-700 text-sm font-medium mb-6 w-fit">
                            <i class="ph-bold ph-device-mobile"></i>
                            {{ $appSettings['landing_download_badge'] ?? 'Available on all platforms' }}
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                            {{ $appSettings['landing_download_title'] ?? 'Download Today' }}
                        </h2>
                        <p class="text-lg text-gray-600 mb-8">
                            {{ $appSettings['landing_download_description'] ?? 'Get started in minutes. Download our app and join the largest agricultural community in the world.' }}
                        </p>
 
                        <!-- Download Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ $appSettings['landing_appstore_url'] ?? '#' }}" class="flex items-center gap-4 px-6 py-4 bg-gray-900 hover:bg-gray-800 text-white rounded-xl transition-all group">
                                <i class="ph-bold ph-apple-logo text-3xl"></i>
                                <div class="text-left">
                                    <p class="text-xs text-gray-400">Download on the</p>
                                    <p class="text-lg font-semibold">App Store</p>
                                </div>
                            </a>
                            <a href="{{ $appSettings['landing_playstore_url'] ?? '#' }}" class="flex items-center gap-4 px-6 py-4 bg-gray-900 hover:bg-gray-800 text-white rounded-xl transition-all group">
                                <i class="ph-bold ph-google-play-logo text-3xl"></i>
                                <div class="text-left">
                                    <p class="text-xs text-gray-400">Get it on</p>
                                    <p class="text-lg font-semibold">Google Play</p>
                                </div>
                            </a>
                        </div>
 
                        <p class="text-sm text-gray-500 mt-6">
                            <i class="ph ph-check-circle text-green-600"></i>
                            {{ $appSettings['landing_download_note'] ?? 'Free to download • No credit card required' }}
                        </p>
                    </div>
 
                    <!-- Image -->
                    <div class="relative bg-gradient-to-br from-green-600 to-green-800 p-8 lg:p-12 flex items-center justify-center">
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute top-10 left-10 w-32 h-32 border-2 border-white rounded-full"></div>
                            <div class="absolute bottom-10 right-10 w-48 h-48 border-2 border-white rounded-full"></div>
                        </div>
                        <img src="{{ $appSettings['landing_download_image'] ?? 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400&h=500&fit=crop' }}" 
                             alt="Mobile App" 
                             class="relative z-10 rounded-2xl shadow-2xl max-w-xs">
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('bg-white', 'shadow-md');
                nav.classList.remove('bg-white/80');
            } else {
                nav.classList.remove('shadow-md');
                nav.classList.add('bg-white/80');
            }
        });
    </script>
</body>
</html>

