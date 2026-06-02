<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appSettings['app_name'] ?? 'Farmers Network' }} — Connect & Grow</title>
    <meta name="description" content="{{ $appSettings['app_description'] ?? 'Connect with farmers worldwide. Share knowledge, get market insights, and grow together.' }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
                    colors: {
                        brand: { 50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d' }
                    }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #16a34a; border-radius: 3px; }
        
        .nav-link { position: relative; }
        .nav-link::after { content:''; position:absolute; bottom:-4px; left:0; width:0; height:2px; background:#16a34a; border-radius:2px; transition:width .2s ease; }
        .nav-link:hover::after { width:100%; }
        
        @media (prefers-reduced-motion: reduce) { * { animation-duration: .01ms !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body class="bg-white font-sans antialiased text-slate-800 selection:bg-brand-200 selection:text-brand-900">
    <!-- NAV -->
    <nav id="nav" class="fixed inset-x-0 top-0 z-50 transition-all duration-300 bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 bg-brand-600 rounded-xl flex items-center justify-center transition-transform group-hover:scale-105">
                        <i class="ph-bold ph-plant text-white text-lg"></i>
                    </div>
                    <span class="font-bold text-slate-900 text-lg tracking-tight">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</span>
                </a>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 py-1">Features</a>
                    <a href="#how-it-works" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 py-1">How It Works</a>
                    <a href="#community" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 py-1">Community</a>
                    <a href="#stories" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 py-1">Stories</a>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <a href="/admin/login" class="text-sm font-medium text-slate-600 hover:text-brand-700 px-3 py-2">Admin</a>
                    <a href="#download" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        Get App
                    </a>
                </div>
                <button id="menuBtn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="ph ph-list text-xl text-slate-700" id="menuIcon"></i>
                </button>
            </div>
        </div>
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-slate-100 shadow-lg">
            <div class="px-4 py-4 space-y-1">
                <a href="#features" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">Features</a>
                <a href="#how-it-works" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">How It Works</a>
                <a href="#community" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">Community</a>
                <a href="#stories" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">Stories</a>
                <a href="#download" class="block px-4 py-3 mt-2 rounded-xl text-sm font-semibold text-white bg-brand-600 text-center">Download Now</a>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="relative min-h-[90vh] flex items-center pt-24 pb-16 overflow-hidden bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-brand-100 text-brand-800 rounded-full text-xs font-semibold mb-6">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                        </span>
                        {{ $appSettings['landing_hero_badge'] ?? 'Trusted by 250,000+ farmers worldwide' }}
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6">
                        @php
                            $title = $appSettings['landing_hero_title'] ?? 'Where Farmers Connect, Share & Grow';
                            $hl = $appSettings['landing_hero_highlight'] ?? 'Farmers';
                            echo str_replace($hl, '<span class="text-brand-600">' . $hl . '</span>', $title);
                        @endphp
                    </h1>

                    <p class="text-lg text-slate-500 mb-10 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                        {{ $appSettings['landing_hero_description'] ?? 'Connect with fellow farmers, get real-time market tips, and build your agricultural network. Simple messaging built for the field.' }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
                        <a href="#download" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-base transition-colors">
                            {{ $appSettings['landing_hero_cta_primary'] ?? 'Join the Community' }}
                        </a>
                        <a href="#features" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-colors text-base">
                            {{ $appSettings['landing_hero_cta_secondary'] ?? 'See How It Works' }}
                        </a>
                    </div>

                    <!-- Social proof -->
                    <div class="flex items-center justify-center lg:justify-start gap-3">
                        <div class="flex -space-x-2">
                            @foreach(['bg-slate-200','bg-slate-300','bg-slate-400','bg-slate-500','bg-slate-600'] as $c)
                            <div class="w-8 h-8 rounded-full border-2 border-white {{ $c }} flex items-center justify-center text-white text-[10px] font-bold">
                                <i class="ph-fill ph-user"></i>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-left">
                            <div class="flex items-center gap-1">
                                @for($i=0;$i<5;$i++)<i class="ph-fill ph-star text-brand-500 text-sm"></i>@endfor
                                <span class="text-sm font-bold text-slate-900 ml-1">4.9</span>
                            </div>
                            <p class="text-xs text-slate-500">from 12,000+ reviews</p>
                        </div>
                    </div>
                </div>
                <!-- Visual -->
                <div class="relative flex justify-center lg:justify-end">
                    <img src="{{ $appSettings['landing_hero_image'] ?? 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&h=450&fit=crop' }}" 
                         alt="Farmers" class="rounded-3xl w-full max-w-lg object-cover aspect-[4/3] shadow-md border border-slate-100">
                </div>
            </div>
        </div>
    </section>

    <!-- TRUSTED BY -->
    <section class="py-12 bg-white border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs font-semibold text-slate-400 uppercase tracking-widest mb-8">Trusted by agricultural organizations</p>
            <div class="flex flex-wrap justify-center items-center gap-8 lg:gap-16 opacity-60">
                @foreach(['Agritech Global','FarmCoop','GreenFields','CropConnect','AgriLink','HarvestHub'] as $org)
                <div class="flex items-center gap-2 text-base font-bold text-slate-500 hover:text-slate-800 transition-colors cursor-default">
                    <i class="ph-fill ph-plant"></i> {{ $org }}
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="how-it-works" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Get Started in 3 Steps</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Join thousands of farmers already connecting and growing together.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                @php $steps=[
                    ['icon'=>'ph-user-plus','title'=>'Create Account','desc'=>'Sign up in seconds with your phone number. No complicated forms.'],
                    ['icon'=>'ph-users-three','title'=>'Join Communities','desc'=>'Find groups for your crops, region, or farming style.'],
                    ['icon'=>'ph-share-network','title'=>'Start Sharing','desc'=>'Share tips, ask questions, and grow your network.']
                ]; @endphp
                @foreach($steps as $i=>$step)
                <div class="text-center group">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-50 text-brand-600 rounded-2xl mb-6 group-hover:bg-brand-50 transition-colors">
                        <i class="ph-bold {{$step['icon']}} text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">{{$step['title']}}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{$step['desc']}}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features" class="py-24 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">{{ $appSettings['landing_features_title'] ?? 'Everything You Need to Connect' }}</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">{{ $appSettings['landing_features_description'] ?? 'Our platform provides all the tools you need to communicate, collaborate, and grow your farming network.' }}</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @php $features=[
                    ['t'=>'Real-Time Chat','i'=>'ph-chat-circle-dots','d'=>'Instant messaging with farmers worldwide. Share photos, documents, and voice messages.'],
                    ['t'=>'Video Calls','i'=>'ph-video-camera','d'=>'High-quality video calling for remote consultations and virtual farm tours.'],
                    ['t'=>'Communities','i'=>'ph-users-three','d'=>'Join groups based on crops, regions, or farming techniques. Learn from experts.'],
                    ['t'=>'Market Insights','i'=>'ph-trend-up','d'=>'Real-time crop prices and market trends to help you make informed decisions.'],
                    ['t'=>'Secure & Private','i'=>'ph-shield-check','d'=>'End-to-end encryption ensures your conversations and data remain private.'],
                    ['t'=>'Weather Updates','i'=>'ph-cloud-sun','d'=>'Localized weather forecasts and alerts to help plan your farming activities.']
                ]; @endphp
                @for($i=0;$i<6;$i++)
                @php
                    $ft=$appSettings['landing_feature_'.($i+1).'_title']??$features[$i]['t'];
                    $fi=$appSettings['landing_feature_'.($i+1).'_icon']??$features[$i]['i'];
                    $fd=$appSettings['landing_feature_'.($i+1).'_description']??$features[$i]['d'];
                @endphp
                <div class="bg-white rounded-2xl p-8 border border-slate-100 hover:border-slate-200 transition-colors">
                    <div class="w-12 h-12 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="ph-bold {{$fi}} text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">{{$ft}}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{$fd}}</p>
                </div>
                @endfor
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section id="community" class="py-20 bg-brand-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">{{ $appSettings['landing_community_title'] ?? 'Join Our Growing Community' }}</h2>
                <p class="text-brand-100/80 text-lg max-w-2xl mx-auto">{{ $appSettings['landing_community_description'] ?? 'Thousands of farmers trust our platform every day to connect and collaborate.' }}</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-4">
                @php $stats=[
                    ['icon'=>'ph-users','val'=>$appSettings['landing_stat_users']??'250K+','lab'=>$appSettings['landing_stat_users_label']??'Active Users'],
                    ['icon'=>'ph-chat-circle-dots','val'=>$appSettings['landing_stat_messages']??'10M+','lab'=>$appSettings['landing_stat_messages_label']??'Messages Sent'],
                    ['icon'=>'ph-globe','val'=>$appSettings['landing_stat_countries']??'120+','lab'=>$appSettings['landing_stat_countries_label']??'Countries'],
                    ['icon'=>'ph-star','val'=>$appSettings['landing_stat_rating']??'4.9','lab'=>$appSettings['landing_stat_rating_label']??'App Store Rating']
                ]; @endphp
                @foreach($stats as $s)
                <div class="text-center">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold {{$s['icon']}} text-xl"></i>
                    </div>
                    <p class="text-4xl font-bold mb-1">{{$s['val']}}</p>
                    <p class="text-brand-100/60 text-sm font-medium">{{$s['lab']}}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="stories" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">What Farmers Say</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Real stories from real farmers using our platform every day.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
                @php $testimonials=[
                    ['name'=>'John Mwangi','role'=>'Maize Farmer, Kenya','text'=>'This app changed how I sell my crops. I connected with buyers directly and got better prices. The community tips saved my harvest last season!'],
                    ['name'=>'Amina Ibrahim','role'=>'Rice Farmer, Nigeria','text'=>'The weather alerts are incredibly accurate. I was able to plan my planting schedule perfectly. The video call feature helps me consult experts remotely.'],
                    ['name'=>'Peter Ochieng','role'=>'Coffee Farmer, Uganda','text'=>'Being part of the coffee growers community has been invaluable. We share best practices and support each other. My yield has increased by 40%!']
                ]; @endphp
                @foreach($testimonials as $i=>$t)
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100">
                    <div class="flex items-center gap-0.5 mb-4">
                        @for($s=0;$s<5;$s++)<i class="ph-fill ph-star text-brand-500 text-sm"></i>@endfor
                    </div>
                    <p class="text-slate-600 leading-relaxed mb-6 text-sm">"{{$t['text']}}"</p>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">{{$t['name']}}</p>
                        <p class="text-xs text-slate-500">{{$t['role']}}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- DOWNLOAD -->
    <section id="download" class="py-24 bg-slate-50 border-t border-slate-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="grid md:grid-cols-2">
                    <div class="p-10 lg:p-14 flex flex-col justify-center">
                        <h2 class="text-3xl font-bold text-slate-900 mb-4">{{ $appSettings['landing_download_title'] ?? 'Download Today' }}</h2>
                        <p class="text-slate-500 mb-8 leading-relaxed">{{ $appSettings['landing_download_description'] ?? 'Get started in minutes. Download our app and join the largest agricultural community in the world.' }}</p>
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <a href="{{ $appSettings['landing_appstore_url'] ?? '#' }}" class="inline-flex items-center justify-center gap-3 px-6 py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl transition-colors">
                                <i class="ph-bold ph-apple-logo text-2xl"></i>
                                <div class="text-left">
                                    <p class="text-[10px] text-slate-300 uppercase tracking-wider">Download on the</p>
                                    <p class="text-base font-bold leading-tight">App Store</p>
                                </div>
                            </a>
                            <a href="{{ $appSettings['landing_playstore_url'] ?? '#' }}" class="inline-flex items-center justify-center gap-3 px-6 py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl transition-colors">
                                <i class="ph-bold ph-google-play-logo text-2xl"></i>
                                <div class="text-left">
                                    <p class="text-[10px] text-slate-300 uppercase tracking-wider">Get it on</p>
                                    <p class="text-base font-bold leading-tight">Google Play</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="bg-brand-50 p-10 lg:p-14 flex items-center justify-center">
                        <img src="{{ $appSettings['landing_download_image'] ?? 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400&h=500&fit=crop' }}" 
                             alt="App" class="rounded-2xl shadow-lg max-w-[220px]">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="py-16 bg-white text-center border-t border-slate-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-4">Ready to grow with us?</h2>
            <p class="text-slate-500 mb-8 text-lg">Join {{ $appSettings['landing_stat_users'] ?? '250,000+' }} farmers already on the platform.</p>
            <a href="#download" class="inline-flex items-center gap-2 px-8 py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-colors">
                Get Started Free
            </a>
        </div>
    </section>

    @include('partials.footer')

    <script>
        // Navbar scroll
        const nav = document.getElementById('nav');
        window.addEventListener('scroll',()=>{
            if(window.scrollY>10){
                nav.classList.add('shadow-sm');
            }else{
                nav.classList.remove('shadow-sm');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a=>{
            a.addEventListener('click',function(e){
                e.preventDefault();
                const t=document.querySelector(this.getAttribute('href'));
                if(t) window.scrollTo({top:t.getBoundingClientRect().top+window.scrollY-80,behavior:'smooth'});
                document.getElementById('mobileMenu').classList.add('hidden');
                document.getElementById('menuIcon').className='ph ph-list text-xl text-slate-700';
            });
        });

        // Mobile menu
        const menuBtn=document.getElementById('menuBtn');
        const mobileMenu=document.getElementById('mobileMenu');
        const menuIcon=document.getElementById('menuIcon');
        menuBtn.addEventListener('click',()=>{
            mobileMenu.classList.toggle('hidden');
            menuIcon.className=mobileMenu.classList.contains('hidden')?'ph ph-list text-xl text-slate-700':'ph ph-x text-xl text-slate-700';
        });
    </script>
</body>
</html>
