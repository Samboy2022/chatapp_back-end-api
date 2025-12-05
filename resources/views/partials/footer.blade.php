<footer class="bg-gray-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- Brand -->
            <div class="lg:col-span-1">
                <a href="/" class="flex items-center space-x-2 mb-4">
                    @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                        <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-8 h-8 rounded-lg">
                    @else
                        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ substr($appSettings['app_name'] ?? 'F', 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="font-bold text-lg">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</span>
                </a>
                <p class="text-gray-400 text-sm mb-4">Connecting the agricultural community worldwide. Share knowledge, collaborate, and grow together.</p>
                <div class="flex gap-3">
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
                        <i class="ph-bold ph-facebook-logo"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
                        <i class="ph-bold ph-twitter-logo"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
                        <i class="ph-bold ph-instagram-logo"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
                        <i class="ph-bold ph-linkedin-logo"></i>
                    </a>
                </div>
            </div>

            <!-- Product Links -->
            <div>
                <h4 class="font-semibold mb-4">Product</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="/#features" class="hover:text-white transition-colors">Features</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
                    <li><a href="/#download" class="hover:text-white transition-colors">Download</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Updates</a></li>
                </ul>
            </div>

            <!-- Company Links -->
            <div>
                <h4 class="font-semibold mb-4">Company</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="/about-us" class="hover:text-white transition-colors">About Us</a></li>
                    <li><a href="/careers" class="hover:text-white transition-colors">Careers</a></li>
                    <li><a href="/press" class="hover:text-white transition-colors">Press</a></li>
                    <li><a href="/contact" class="hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>

            <!-- Support Links -->
            <div>
                <h4 class="font-semibold mb-4">Support</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="/help-center" class="hover:text-white transition-colors">Help Center</a></li>
                    <li><a href="/privacy-policy" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="/terms-of-service" class="hover:text-white transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Status</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-gray-400">
                &copy; {{ date('Y') }} {{ $appSettings['app_name'] ?? 'Farmers Network' }}. All rights reserved.
            </p>
            <div class="flex items-center gap-6 text-sm text-gray-400">
                <a href="/privacy-policy" class="hover:text-white transition-colors">Privacy</a>
                <a href="/terms-of-service" class="hover:text-white transition-colors">Terms</a>
                <a href="#" class="hover:text-white transition-colors">Cookies</a>
            </div>
        </div>
    </div>
</footer>
