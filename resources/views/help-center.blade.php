<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Help Center - {{ $appSettings['app_name'] ?? 'Farmers Network' }}</title>
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
            <i class="ph-bold ph-question text-5xl mb-4"></i>
            <h1 class="text-4xl font-bold mb-4">Help Center</h1>
            <p class="text-lg text-green-100 max-w-2xl mx-auto">Find answers to common questions and get support</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Search -->
            <div class="mb-12">
                <div class="relative">
                    <input type="text" placeholder="Search for help..." class="w-full px-6 py-4 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    <i class="ph-bold ph-magnifying-glass absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>
                </div>
            </div>

            <!-- FAQ Categories -->
            <div class="grid md:grid-cols-2 gap-6 mb-12">
                <div class="bg-white rounded-xl p-6 border border-gray-100 hover:border-green-200 transition-all">
                    <i class="ph-bold ph-user-circle text-3xl text-green-700 mb-3"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Getting Started</h3>
                    <p class="text-gray-600 text-sm mb-4">Learn how to set up your account and start connecting</p>
                    <a href="#" class="text-green-700 text-sm font-medium hover:underline">View articles →</a>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-100 hover:border-green-200 transition-all">
                    <i class="ph-bold ph-chat-circle-dots text-3xl text-blue-600 mb-3"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Messaging</h3>
                    <p class="text-gray-600 text-sm mb-4">Everything about chats, groups, and media sharing</p>
                    <a href="#" class="text-green-700 text-sm font-medium hover:underline">View articles →</a>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-100 hover:border-green-200 transition-all">
                    <i class="ph-bold ph-shield-check text-3xl text-purple-600 mb-3"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Privacy & Security</h3>
                    <p class="text-gray-600 text-sm mb-4">Keep your account and data safe</p>
                    <a href="#" class="text-green-700 text-sm font-medium hover:underline">View articles →</a>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-100 hover:border-green-200 transition-all">
                    <i class="ph-bold ph-wrench text-3xl text-orange-600 mb-3"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Troubleshooting</h3>
                    <p class="text-gray-600 text-sm mb-4">Fix common issues and technical problems</p>
                    <a href="#" class="text-green-700 text-sm font-medium hover:underline">View articles →</a>
                </div>
            </div>

            <!-- Popular Questions -->
            <div class="bg-white rounded-xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Popular Questions</h2>
                <div class="space-y-4">
                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-900">How do I create an account?</span>
                            <i class="ph-bold ph-caret-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="text-gray-600 text-sm mt-3 pb-3">Download the app, enter your phone number, and verify it with the code sent to you. Then set up your profile and you're ready to go!</p>
                    </details>

                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-900">How do I join a group?</span>
                            <i class="ph-bold ph-caret-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="text-gray-600 text-sm mt-3 pb-3">You can join groups through invite links shared by group admins, or search for public groups in the app's discover section.</p>
                    </details>

                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-900">Is my data encrypted?</span>
                            <i class="ph-bold ph-caret-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="text-gray-600 text-sm mt-3 pb-3">Yes! All messages are protected with end-to-end encryption, meaning only you and the recipient can read them.</p>
                    </details>

                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-900">How do I delete my account?</span>
                            <i class="ph-bold ph-caret-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="text-gray-600 text-sm mt-3 pb-3">Go to Settings > Account > Delete Account. Note that this action is permanent and cannot be undone.</p>
                    </details>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="mt-12 bg-green-50 rounded-xl p-8 text-center border border-green-100">
                <i class="ph-bold ph-headset text-4xl text-green-700 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Still need help?</h3>
                <p class="text-gray-600 mb-6">Our support team is here to assist you</p>
                <a href="/contact" class="inline-flex items-center gap-2 px-6 py-3 bg-green-700 hover:bg-green-800 text-white font-medium rounded-xl transition-all">
                    <i class="ph-bold ph-envelope"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
