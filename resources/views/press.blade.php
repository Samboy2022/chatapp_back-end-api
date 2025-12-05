<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Press - {{ $appSettings['app_name'] ?? 'Farmers Network' }}</title>
    @vite(['resources/css/landing.css'])
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
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
                <a href="/" class="text-sm font-medium text-gray-600 hover:text-green-700">Back to Home</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="bg-green-700 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <i class="ph-bold ph-newspaper text-5xl mb-4"></i>
            <h1 class="text-4xl font-bold mb-4">Press & Media</h1>
            <p class="text-lg text-green-100 max-w-2xl mx-auto">Latest news and media resources</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Press Kit -->
            <div class="bg-white rounded-xl p-8 border border-gray-100 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Press Kit</h2>
                <p class="text-gray-600 mb-6">Download our brand assets and media resources</p>
                <div class="grid md:grid-cols-3 gap-4">
                    <a href="#" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-green-300 transition-all">
                        <i class="ph-bold ph-image text-green-700 text-2xl"></i>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">Logos</p>
                            <p class="text-gray-500 text-xs">PNG, SVG</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-green-300 transition-all">
                        <i class="ph-bold ph-file-text text-blue-600 text-2xl"></i>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">Brand Guidelines</p>
                            <p class="text-gray-500 text-xs">PDF</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-green-300 transition-all">
                        <i class="ph-bold ph-images text-purple-600 text-2xl"></i>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">Screenshots</p>
                            <p class="text-gray-500 text-xs">High-res</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Latest News -->
            <div class="bg-white rounded-xl p-8 border border-gray-100 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Latest News</h2>
                <div class="space-y-6">
                    <div class="border-b border-gray-100 pb-6">
                        <p class="text-sm text-gray-500 mb-2">{{ date('F d, Y') }}</p>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $appSettings['app_name'] ?? 'Farmers Network' }} Reaches 250,000 Active Users</h3>
                        <p class="text-gray-600 text-sm mb-3">Our platform continues to grow as more farmers worldwide discover the power of connected agriculture.</p>
                        <a href="#" class="text-green-700 font-medium text-sm hover:underline">Read more →</a>
                    </div>

                    <div class="border-b border-gray-100 pb-6">
                        <p class="text-sm text-gray-500 mb-2">{{ date('F d, Y', strtotime('-30 days')) }}</p>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">New Features: Video Calls and Group Communities</h3>
                        <p class="text-gray-600 text-sm mb-3">We're excited to announce major new features that make collaboration even easier.</p>
                        <a href="#" class="text-green-700 font-medium text-sm hover:underline">Read more →</a>
                    </div>

                    <div class="pb-6">
                        <p class="text-sm text-gray-500 mb-2">{{ date('F d, Y', strtotime('-60 days')) }}</p>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Expanding to 120 Countries Worldwide</h3>
                        <p class="text-gray-600 text-sm mb-3">Our mission to connect farmers globally reaches new milestones.</p>
                        <a href="#" class="text-green-700 font-medium text-sm hover:underline">Read more →</a>
                    </div>
                </div>
            </div>

            <!-- Media Contact -->
            <div class="bg-green-50 rounded-xl p-8 border border-green-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Media Inquiries</h2>
                <p class="text-gray-600 mb-6">For press inquiries, interviews, or media requests, please contact our press team:</p>
                <div class="bg-white rounded-lg p-6 border border-green-200">
                    <p class="text-gray-900 font-medium mb-2">Press Contact</p>
                    <p class="text-gray-600 text-sm">Email: press@{{ strtolower(str_replace(' ', '', $appSettings['app_name'] ?? 'farmersnetwork')) }}.com</p>
                    <p class="text-gray-600 text-sm">Phone: [Your Phone Number]</p>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
