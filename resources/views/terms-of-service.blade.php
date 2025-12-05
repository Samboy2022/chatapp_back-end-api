<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terms of Service - {{ $appSettings['app_name'] ?? 'Farmers Network' }}</title>
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
            <i class="ph-bold ph-file-text text-5xl mb-4"></i>
            <h1 class="text-4xl font-bold mb-4">Terms of Service</h1>
            <p class="text-lg text-green-100">Last updated: {{ date('F d, Y') }}</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-8 border border-gray-100 prose prose-green max-w-none">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Agreement to Terms</h2>
                <p class="text-gray-600 mb-6">
                    By accessing or using {{ $appSettings['app_name'] ?? 'Farmers Network' }}, you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using this service.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Use License</h2>
                <p class="text-gray-600 mb-4">
                    Permission is granted to temporarily use {{ $appSettings['app_name'] ?? 'Farmers Network' }} for personal, non-commercial purposes. This license shall automatically terminate if you violate any of these restrictions.
                </p>
                <p class="text-gray-600 mb-4">Under this license you may not:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Modify or copy the materials</li>
                    <li>Use the materials for any commercial purpose</li>
                    <li>Attempt to reverse engineer any software</li>
                    <li>Remove any copyright or proprietary notations</li>
                    <li>Transfer the materials to another person</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">User Accounts</h2>
                <p class="text-gray-600 mb-4">When you create an account, you must provide accurate and complete information. You are responsible for:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Maintaining the security of your account</li>
                    <li>All activities that occur under your account</li>
                    <li>Notifying us immediately of any unauthorized use</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Acceptable Use</h2>
                <p class="text-gray-600 mb-4">You agree not to use our service to:</p>
                <ul class="list-disc pl-6 text-gray-600 mb-6 space-y-2">
                    <li>Violate any laws or regulations</li>
                    <li>Infringe on intellectual property rights</li>
                    <li>Transmit harmful or malicious code</li>
                    <li>Harass, abuse, or harm others</li>
                    <li>Spam or send unsolicited messages</li>
                    <li>Impersonate others or misrepresent your identity</li>
                    <li>Collect user information without consent</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Content</h2>
                <p class="text-gray-600 mb-6">
                    You retain ownership of content you submit to our service. By posting content, you grant us a license to use, modify, and display that content in connection with operating the service. You are responsible for the content you post and must ensure you have the right to share it.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Intellectual Property</h2>
                <p class="text-gray-600 mb-6">
                    The service and its original content, features, and functionality are owned by {{ $appSettings['app_name'] ?? 'Farmers Network' }} and are protected by international copyright, trademark, and other intellectual property laws.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Termination</h2>
                <p class="text-gray-600 mb-6">
                    We may terminate or suspend your account and access to the service immediately, without prior notice, for any reason, including breach of these Terms. Upon termination, your right to use the service will immediately cease.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Disclaimer</h2>
                <p class="text-gray-600 mb-6">
                    The service is provided "as is" without warranties of any kind, either express or implied. We do not warrant that the service will be uninterrupted, secure, or error-free.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Limitation of Liability</h2>
                <p class="text-gray-600 mb-6">
                    In no event shall {{ $appSettings['app_name'] ?? 'Farmers Network' }} be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the service.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Changes to Terms</h2>
                <p class="text-gray-600 mb-6">
                    We reserve the right to modify these terms at any time. We will notify users of any material changes. Your continued use of the service after changes constitutes acceptance of the new terms.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Contact Information</h2>
                <p class="text-gray-600 mb-4">
                    For questions about these Terms of Service, please contact us:
                </p>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="text-gray-600">Email: legal@{{ strtolower(str_replace(' ', '', $appSettings['app_name'] ?? 'farmersnetwork')) }}.com</p>
                    <p class="text-gray-600">Address: [Your Company Address]</p>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
