<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact - {{ $appSettings['app_name'] ?? 'Farmers Network' }}</title>
    @vite(['resources/css/landing.css'])
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-50">
    @include('partials.public-nav')

    <!-- Hero -->
    <section class="bg-green-700 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <i class="ph-bold ph-envelope text-5xl mb-4"></i>
            <h1 class="text-4xl font-bold mb-4">Contact Us</h1>
            <p class="text-lg text-green-100 max-w-2xl mx-auto">Get in touch with our team</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Contact Form -->
                <div class="bg-white rounded-xl p-8 border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a message</h2>
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input type="text" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Your name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="your@email.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                            <input type="text" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="How can we help?">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Your message..."></textarea>
                        </div>
                        <button type="submit" class="w-full px-6 py-3.5 bg-green-700 hover:bg-green-800 text-white font-bold rounded-full transition-all shadow-md hover:shadow-lg">
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="ph-bold ph-envelope text-green-700 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Email</h3>
                                <p class="text-gray-600 text-sm">support@{{ strtolower(str_replace(' ', '', $appSettings['app_name'] ?? 'farmersnetwork')) }}.com</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="ph-bold ph-map-pin text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Address</h3>
                                <p class="text-gray-600 text-sm">[Your Company Address]<br>City, State, ZIP</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="ph-bold ph-clock text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Business Hours</h3>
                                <p class="text-gray-600 text-sm">Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday - Sunday: Closed</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                        <h3 class="font-bold text-gray-900 mb-3">Follow Us</h3>
                        <div class="flex gap-3">
                            <a href="#" class="w-10 h-10 bg-white hover:bg-green-700 hover:text-white rounded-lg flex items-center justify-center transition-all border border-green-200">
                                <i class="ph-bold ph-facebook-logo"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-white hover:bg-green-700 hover:text-white rounded-lg flex items-center justify-center transition-all border border-green-200">
                                <i class="ph-bold ph-twitter-logo"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-white hover:bg-green-700 hover:text-white rounded-lg flex items-center justify-center transition-all border border-green-200">
                                <i class="ph-bold ph-instagram-logo"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-white hover:bg-green-700 hover:text-white rounded-lg flex items-center justify-center transition-all border border-green-200">
                                <i class="ph-bold ph-linkedin-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</body>
</html>
