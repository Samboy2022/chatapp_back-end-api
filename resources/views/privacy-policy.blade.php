<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Privacy Policy - {{ $appSettings['app_name'] ?? 'Farmers Network' }}</title>
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
            <i class="ph-bold ph-shield-check text-5xl mb-4"></i>
            <h1 class="text-4xl font-bold mb-4">Privacy Policy</h1>
            <p class="text-lg text-green-100">Last updated: {{ date('F d, Y') }}</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-8 border border-gray-100 prose prose-green max-w-none">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Introduction</h2>
                <p class="text-gray-600 mb-6">
                    At {{ $appSettings['app_name'] ?? 'Farmers Network' }}, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our mobile application and services.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Information We Collect</h2>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Personal Information</h3>
                <p class="text-gray-600 mb-4">We may collect personal information that you provide to us, including:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Name and profile information</li>
                    <li>Phone number and email address</li>
                    <li>Profile photos and status updates</li>
                    <li>Messages and media you send through our service</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mb-3">Usage Information</h3>
                <p class="text-gray-600 mb-4">We automatically collect certain information when you use our app:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Device information (model, operating system, unique identifiers)</li>
                    <li>Log information (IP address, access times, app features used)</li>
                    <li>Location information (with your permission)</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">How We Use Your Information</h2>
                <p class="text-gray-600 mb-4">We use the information we collect to:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Provide, maintain, and improve our services</li>
                    <li>Process and deliver messages and media</li>
                    <li>Send you technical notices and support messages</li>
                    <li>Respond to your comments and questions</li>
                    <li>Protect against fraudulent or illegal activity</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Data Security</h2>
                <p class="text-gray-600 mb-6">
                    We implement industry-standard security measures to protect your information. All messages are encrypted end-to-end, meaning only you and the recipient can read them. We cannot access the content of your messages.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Data Retention</h2>
                <p class="text-gray-600 mb-6">
                    We retain your information for as long as necessary to provide our services and comply with legal obligations. You can delete your account at any time, which will remove your personal information from our systems.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Your Rights</h2>
                <p class="text-gray-600 mb-4">You have the right to:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Access and update your personal information</li>
                    <li>Delete your account and data</li>
                    <li>Opt-out of certain data collection</li>
                    <li>Request a copy of your data</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Third-Party Services</h2>
                <p class="text-gray-600 mb-6">
                    We may use third-party services for analytics and infrastructure. These services have their own privacy policies and we encourage you to review them.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Children's Privacy</h2>
                <p class="text-gray-600 mb-6">
                    Our service is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Changes to This Policy</h2>
                <p class="text-gray-600 mb-6">
                    We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Contact Us</h2>
                <p class="text-gray-600 mb-4">
                    If you have questions about this Privacy Policy, please contact us:
                </p>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="text-gray-600">Email: privacy@{{ strtolower(str_replace(' ', '', $appSettings['app_name'] ?? 'farmersnetwork')) }}.com</p>
                    <p class="text-gray-600">Address: [Your Company Address]</p>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
