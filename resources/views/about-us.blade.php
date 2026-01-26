<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>About Us - {{ $appSettings['app_name'] ?? 'Farmers Network' }}</title>
    @vite(['resources/css/landing.css'])
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-50">
    @include('partials.public-nav')

    <!-- Hero -->
    <section class="bg-green-700 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <i class="ph-bold ph-users-three text-5xl mb-4"></i>
            <h1 class="text-4xl font-bold mb-4">About Us</h1>
            <p class="text-lg text-green-100 max-w-2xl mx-auto">Connecting farmers worldwide through technology</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Mission -->
            <div class="bg-white rounded-xl p-8 border border-gray-100 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                <p class="text-gray-600 mb-4">
                    At {{ $appSettings['app_name'] ?? 'Farmers Network' }}, we believe that farmers deserve the best tools to connect, collaborate, and grow together. Our mission is to empower the global agricultural community with cutting-edge communication technology.
                </p>
                <p class="text-gray-600">
                    We're building more than just a messaging app – we're creating a platform where farmers can share knowledge, support each other, and build thriving communities that transcend geographical boundaries.
                </p>
            </div>

            <!-- Story -->
            <div class="bg-white rounded-xl p-8 border border-gray-100 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Story</h2>
                <p class="text-gray-600 mb-4">
                    Founded in 2024, {{ $appSettings['app_name'] ?? 'Farmers Network' }} started with a simple idea: farmers need better ways to communicate and collaborate. What began as a small project has grown into a global platform serving over 250,000 farmers across 120 countries.
                </p>
                <p class="text-gray-600">
                    Our team combines expertise in agriculture, technology, and community building to create solutions that truly serve the farming community's needs.
                </p>
            </div>

            <!-- Values -->
            <div class="bg-white rounded-xl p-8 border border-gray-100 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Our Values</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="ph-bold ph-users text-green-700"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">Community First</h3>
                        </div>
                        <p class="text-gray-600 text-sm">We prioritize the needs of our farming community in every decision we make.</p>
                    </div>

                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="ph-bold ph-shield-check text-blue-600"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">Privacy & Security</h3>
                        </div>
                        <p class="text-gray-600 text-sm">Your data and conversations are protected with industry-leading encryption.</p>
                    </div>

                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="ph-bold ph-lightbulb text-purple-600"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">Innovation</h3>
                        </div>
                        <p class="text-gray-600 text-sm">We continuously improve and innovate to serve farmers better.</p>
                    </div>

                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="ph-bold ph-globe text-orange-600"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">Global Reach</h3>
                        </div>
                        <p class="text-gray-600 text-sm">Connecting farmers across borders and cultures worldwide.</p>
                    </div>
                </div>
            </div>

            <!-- Team -->
            <div class="bg-white rounded-xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Our Team</h2>
                <p class="text-gray-600 mb-6">
                    We're a diverse team of engineers, designers, and agricultural experts passionate about making a difference in the farming community. Our team members come from farming backgrounds and understand the challenges farmers face every day.
                </p>
                <div class="bg-green-50 rounded-lg p-6 border border-green-100">
                    <p class="text-gray-700 font-medium mb-2">Want to join us?</p>
                    <p class="text-gray-600 text-sm mb-4">We're always looking for talented people who share our passion.</p>
                    <a href="/careers" class="inline-flex items-center gap-2 text-green-700 font-medium hover:underline">
                        View open positions →
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
