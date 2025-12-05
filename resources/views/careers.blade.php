<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Careers - {{ $appSettings['app_name'] ?? 'Farmers Network' }}</title>
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
            <i class="ph-bold ph-briefcase text-5xl mb-4"></i>
            <h1 class="text-4xl font-bold mb-4">Join Our Team</h1>
            <p class="text-lg text-green-100 max-w-2xl mx-auto">Help us build the future of agricultural communication</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Why Join -->
            <div class="bg-white rounded-xl p-8 border border-gray-100 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Why Join Us?</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="ph-bold ph-rocket text-green-700 text-3xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">Make an Impact</h3>
                        <p class="text-gray-600 text-sm">Help millions of farmers connect and grow</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="ph-bold ph-users-three text-blue-600 text-3xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">Great Team</h3>
                        <p class="text-gray-600 text-sm">Work with talented, passionate people</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="ph-bold ph-chart-line text-purple-600 text-3xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">Grow Your Career</h3>
                        <p class="text-gray-600 text-sm">Continuous learning and development</p>
                    </div>
                </div>
            </div>

            <!-- Open Positions -->
            <div class="bg-white rounded-xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Open Positions</h2>
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-green-300 transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg">Senior Backend Engineer</h3>
                                <p class="text-gray-600 text-sm">Engineering • Full-time • Remote</p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">New</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Build scalable backend systems for our messaging platform. Experience with Laravel, Redis, and WebSockets required.</p>
                        <a href="#" class="text-green-700 font-medium text-sm hover:underline">View Details →</a>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-6 hover:border-green-300 transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg">Mobile Developer (React Native)</h3>
                                <p class="text-gray-600 text-sm">Engineering • Full-time • Remote</p>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Create beautiful mobile experiences for iOS and Android. Strong React Native and mobile development skills needed.</p>
                        <a href="#" class="text-green-700 font-medium text-sm hover:underline">View Details →</a>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-6 hover:border-green-300 transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg">Product Designer</h3>
                                <p class="text-gray-600 text-sm">Design • Full-time • Remote</p>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Design intuitive user experiences for our platform. Experience with Figma and user research required.</p>
                        <a href="#" class="text-green-700 font-medium text-sm hover:underline">View Details →</a>
                    </div>
                </div>

                <div class="mt-8 text-center bg-gray-50 rounded-lg p-6">
                    <p class="text-gray-600 mb-4">Don't see a position that fits?</p>
                    <a href="/contact" class="inline-flex items-center gap-2 px-6 py-3 bg-green-700 hover:bg-green-800 text-white font-medium rounded-lg transition-all">
                        Send us your resume
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
