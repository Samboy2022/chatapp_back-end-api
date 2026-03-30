<footer class="bg-gray-900 text-gray-300 py-16 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute top-0 left-0 w-64 h-64 bg-green-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-green-600 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- About -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                        <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-8 h-8 rounded-lg">
                    @else
                        <div class="w-8 h-8 bg-green-700 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ substr($appSettings['app_name'] ?? 'F', 0, 1) }}</span>
                        </div>
                    @endif
                    <h3 class="text-white font-bold text-lg">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</h3>
                </div>
                <p class="text-sm text-gray-400 mb-4">{{ $appSettings['app_description'] ?? 'Connect & Collaborate with farmers worldwide' }}</p>
                <div class="flex space-x-3">
                    <a href="{{ $appSettings['social_facebook'] ?? '#' }}" class="w-9 h-9 bg-gray-800 hover:bg-green-700 rounded-lg flex items-center justify-center transition-all hover:scale-110">
                        <i class="ph-bold ph-facebook-logo text-lg"></i>
                    </a>
                    <a href="{{ $appSettings['social_twitter'] ?? '#' }}" class="w-9 h-9 bg-gray-800 hover:bg-green-700 rounded-lg flex items-center justify-center transition-all hover:scale-110">
                        <i class="ph-bold ph-twitter-logo text-lg"></i>
                    </a>
                    <a href="{{ $appSettings['social_instagram'] ?? '#' }}" class="w-9 h-9 bg-gray-800 hover:bg-green-700 rounded-lg flex items-center justify-center transition-all hover:scale-110">
                        <i class="ph-bold ph-instagram-logo text-lg"></i>
                    </a>
                    <a href="{{ $appSettings['social_linkedin'] ?? '#' }}" class="w-9 h-9 bg-gray-800 hover:bg-green-700 rounded-lg flex items-center justify-center transition-all hover:scale-110">
                        <i class="ph-bold ph-linkedin-logo text-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-link text-green-500"></i>
                    Quick Links
                </h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/#features" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Features
                    </a></li>
                    <li><a href="/#download" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Download
                    </a></li>
                    <li><a href="/about-us" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        About Us
                    </a></li>
                    <li><a href="/contact" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Contact
                    </a></li>
                    <li><a href="/careers" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Careers
                    </a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-book-open text-green-500"></i>
                    Resources
                </h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/help-center" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Help Center
                    </a></li>
                    <li><a href="/privacy-policy" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Privacy Policy
                    </a></li>
                    <li><a href="/terms-of-service" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Terms of Service
                    </a></li>
                    <li><a href="/press" class="hover:text-green-400 transition-colors flex items-center gap-2 group">
                        <i class="ph ph-caret-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        Press Kit
                    </a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-envelope text-green-500"></i>
                    Get in Touch
                </h4>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2">
                        <i class="ph ph-map-pin text-green-500 mt-0.5"></i>
                        <span class="text-gray-400">{{ $appSettings['contact_address'] ?? '123 Farm Street, Agriculture City' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="ph ph-envelope text-green-500"></i>
                        <a href="mailto:{{ $appSettings['contact_email'] ?? 'hello@farmersnetwork.com' }}" class="text-gray-400 hover:text-green-400 transition-colors">
                            {{ $appSettings['contact_email'] ?? 'hello@farmersnetwork.com' }}
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="ph ph-phone text-green-500"></i>
                        <a href="tel:{{ $appSettings['contact_phone'] ?? '+1234567890' }}" class="text-gray-400 hover:text-green-400 transition-colors">
                            {{ $appSettings['contact_phone'] ?? '+1 (234) 567-890' }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-400 text-center md:text-left">
                    &copy; {{ date('Y') }} {{ $appSettings['app_name'] ?? 'Farmers Network' }}. All rights reserved.
                </p>
                <div class="flex items-center gap-6 text-sm">
                    <a href="/privacy-policy" class="text-gray-400 hover:text-green-400 transition-colors">Privacy</a>
                    <a href="/terms-of-service" class="text-gray-400 hover:text-green-400 transition-colors">Terms</a>
                    <a href="/help-center" class="text-gray-400 hover:text-green-400 transition-colors">Support</a>
                </div>
            </div>
        </div>
    </div>
</footer>
