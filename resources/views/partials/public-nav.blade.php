<nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-lg border-b border-gray-100 transition-all" id="mainNav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="/" class="flex items-center space-x-2 group">
                @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                    <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-8 h-8 rounded-lg transition-transform group-hover:scale-110">
                @else
                    <div class="w-8 h-8 bg-green-700 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110">
                        <span class="text-white font-bold text-sm">{{ substr($appSettings['app_name'] ?? 'F', 0, 1) }}</span>
                    </div>
                @endif
                <span class="font-bold text-gray-900 text-lg">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="/" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all">
                    Home
                </a>
                <a href="/about-us" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all">
                    About
                </a>
                <a href="/help-center" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all">
                    Help
                </a>
                <a href="/contact" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all">
                    Contact
                </a>
                <div class="w-px h-6 bg-gray-200 mx-2"></div>
                <a href="/admin/login" class="px-5 py-2 text-sm font-semibold text-green-700 hover:bg-green-50 rounded-full border border-green-200 transition-all whitespace-nowrap">
                    Admin
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn" class="md:hidden p-2 text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all">
                <i class="ph-bold ph-list text-xl"></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden pb-4 pt-2 border-t border-gray-100 mt-2">
            <div class="flex flex-col space-y-1">
                <a href="/" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all whitespace-nowrap">
                    Home
                </a>
                <a href="/about-us" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all whitespace-nowrap">
                    About Us
                </a>
                <a href="/help-center" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all whitespace-nowrap">
                    Help Center
                </a>
                <a href="/contact" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-full transition-all whitespace-nowrap">
                    Contact
                </a>
                <div class="border-t border-gray-100 my-2"></div>
                <a href="/admin/login" class="px-4 py-2.5 text-sm font-medium text-green-700 hover:bg-green-50 rounded-full border border-green-200 transition-all text-center whitespace-nowrap">
                    Admin Login
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer for fixed nav -->
<div class="h-16"></div>

<script>
    // Mobile menu toggle
    document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
        
        // Toggle icon
        const icon = this.querySelector('i');
        if (menu.classList.contains('hidden')) {
            icon.classList.remove('ph-x');
            icon.classList.add('ph-list');
        } else {
            icon.classList.remove('ph-list');
            icon.classList.add('ph-x');
        }
    });

    // Navbar scroll effect
    let lastScroll = 0;
    const nav = document.getElementById('mainNav');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            nav.classList.add('shadow-md');
            nav.classList.remove('bg-white/90');
            nav.classList.add('bg-white');
        } else {
            nav.classList.remove('shadow-md');
            nav.classList.remove('bg-white');
            nav.classList.add('bg-white/90');
        }
        
        lastScroll = currentScroll;
    });

    // Highlight active page
    const currentPath = window.location.pathname;
    document.querySelectorAll('nav a').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('text-green-700', 'bg-green-50');
            link.classList.remove('text-gray-600');
        }
    });
</script>
