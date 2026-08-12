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
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
                    colors: {
                        brand: { 50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d',950:'#052e16' }
                    }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #16a34a, #15803d); border-radius: 4px; }
        
        .nav-link { position: relative; }
        .nav-link::after { content:''; position:absolute; bottom:-4px; left:0; width:0; height:2px; background:#16a34a; border-radius:2px; transition:width .3s ease; }
        .nav-link:hover::after { width:100%; }

        .cta-shadow {
            box-shadow: 0 20px 40px -15px rgba(22, 163, 74, 0.4);
        }
        
        @media (prefers-reduced-motion: reduce) { * { animation-duration: .01ms !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body class="bg-white font-sans antialiased text-slate-800 selection:bg-brand-200 selection:text-brand-900">
    <!-- NAV -->
    <nav id="nav" class="fixed inset-x-0 top-0 z-50 transition-all duration-300 bg-white/95 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-brand-600 rounded-full flex items-center justify-center transition-transform group-hover:scale-105 shadow-md shadow-brand-600/20">
                        <i class="ph-bold ph-plant text-white text-xl"></i>
                    </div>
                    <span class="font-extrabold text-slate-900 text-xl tracking-tight">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</span>
                </a>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="nav-link text-sm font-semibold text-slate-600 hover:text-brand-700 py-1 transition-colors">Features</a>
                    <a href="#how-it-works" class="nav-link text-sm font-semibold text-slate-600 hover:text-brand-700 py-1 transition-colors">How It Works</a>
                    <a href="#community" class="nav-link text-sm font-semibold text-slate-600 hover:text-brand-700 py-1 transition-colors">Community</a>
                    <a href="#stories" class="nav-link text-sm font-semibold text-slate-600 hover:text-brand-700 py-1 transition-colors">Stories</a>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <a href="/admin/login" class="inline-flex items-center justify-center px-5 py-2.5 rounded-full text-sm font-semibold text-slate-700 border border-slate-200 hover:border-brand-300 hover:text-brand-700 hover:bg-brand-50/50 transition-all shadow-sm whitespace-nowrap">
                        Admin
                    </a>
                    <a href="#download" class="inline-flex items-center justify-center px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-full transition-all shadow-md shadow-brand-600/20 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap">
                        Get App
                    </a>
                </div>
                <button id="menuBtn" class="md:hidden p-2.5 rounded-full hover:bg-slate-100 transition-colors">
                    <i class="ph ph-list text-2xl text-slate-700" id="menuIcon"></i>
                </button>
            </div>
        </div>
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-slate-100 shadow-xl px-4 py-4 space-y-2">
            <a href="#features" class="block px-5 py-3 rounded-full text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Features</a>
            <a href="#how-it-works" class="block px-5 py-3 rounded-full text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">How It Works</a>
            <a href="#community" class="block px-5 py-3 rounded-full text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Community</a>
            <a href="#stories" class="block px-5 py-3 rounded-full text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Stories</a>
            <a href="/admin/login" class="block px-5 py-3 rounded-full text-sm font-semibold text-slate-700 border border-slate-200 text-center hover:bg-slate-50 transition-colors whitespace-nowrap">Admin Login</a>
            <a href="#download" class="block px-6 py-3.5 mt-3 rounded-full text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 text-center transition-colors shadow-md whitespace-nowrap">Download Now</a>
        </div>
    </nav>

    <!-- HERO SECTION (CENTER ALIGNED WITH 3 MOBILE APP SCREEN FRAMES) -->
    <section class="relative min-h-[95vh] flex items-center pt-28 pb-24 overflow-hidden bg-gradient-to-b from-slate-50 via-white to-slate-50">
        <!-- Ambient background blur circles -->
        <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[40rem] h-[25rem] bg-brand-200/40 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-emerald-100/50 rounded-full blur-3xl -z-10 pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-brand-100/80 border border-brand-200 text-brand-900 rounded-full text-xs sm:text-sm font-semibold mb-8 shadow-sm backdrop-blur-sm mx-auto">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-600"></span>
                </span>
                <span>{{ $appSettings['landing_hero_badge'] ?? 'Trusted by 250,000+ farmers worldwide' }}</span>
            </div>

            <!-- Headline -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-slate-900 tracking-tight leading-[1.1] mb-6 max-w-4xl mx-auto">
                @php
                    $title = $appSettings['landing_hero_title'] ?? 'Where Farmers Connect, Share & Grow';
                    $hl = $appSettings['landing_hero_highlight'] ?? 'Farmers';
                    echo str_replace($hl, '<span class="text-brand-600 bg-clip-text">' . $hl . '</span>', $title);
                @endphp
            </h1>

            <!-- Description -->
            <p class="text-lg sm:text-xl text-slate-600 mb-10 max-w-2xl mx-auto leading-relaxed font-normal">
                {{ $appSettings['landing_hero_description'] ?? 'Connect with fellow farmers, get real-time market tips, and build your agricultural network. Simple messaging built for the field.' }}
            </p>

            <!-- Non-breaking Call to Action Buttons without icons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12 w-full max-w-xl mx-auto">
                <a href="#download" class="inline-flex items-center justify-center px-8 sm:px-10 py-4 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-extrabold rounded-full text-base sm:text-lg transition-all shadow-lg shadow-brand-600/30 hover:shadow-xl hover:-translate-y-0.5 whitespace-nowrap w-full sm:w-auto">
                    {{ $appSettings['landing_hero_cta_primary'] ?? 'Join the Community' }}
                </a>
                <a href="#features" class="inline-flex items-center justify-center px-8 sm:px-10 py-4 bg-white border-2 border-slate-200 text-slate-700 font-extrabold rounded-full hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all text-base sm:text-lg shadow-sm hover:shadow-md whitespace-nowrap w-full sm:w-auto">
                    {{ $appSettings['landing_hero_cta_secondary'] ?? 'See How It Works' }}
                </a>
            </div>

            <!-- Social proof -->
            <div class="flex items-center justify-center gap-4 mb-16">
                <div class="flex -space-x-3">
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-300 flex items-center justify-center text-slate-700 text-xs font-bold shadow-sm">
                        <i class="ph-fill ph-user text-sm"></i>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-400 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        <i class="ph-fill ph-user text-sm"></i>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-brand-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        <i class="ph-fill ph-user text-sm"></i>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-brand-700 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        <i class="ph-fill ph-user text-sm"></i>
                    </div>
                </div>
                <div class="text-left">
                    <div class="flex items-center gap-1">
                        @for($i=0;$i<5;$i++)<i class="ph-fill ph-star text-amber-400 text-base"></i>@endfor
                        <span class="text-sm font-extrabold text-slate-900 ml-1">4.9/5</span>
                    </div>
                    <p class="text-xs font-medium text-slate-500">From over 12,000+ verified reviews</p>
                </div>
            </div>

            <!-- 3 MOBILE APP SCREENSHOT FRAMES SHOWCASE (CENTER ALIGNED) -->
            <div class="relative pt-6 max-w-5xl mx-auto flex items-center justify-center">
                <div class="flex items-center justify-center gap-2 sm:gap-4 md:gap-6 relative w-full">
                    
                    <!-- Left Phone Frame -->
                    <div class="hidden sm:block relative -mr-6 md:-mr-10 z-10 transform -rotate-6 hover:rotate-0 transition-all duration-700 scale-90 md:scale-95 group">
                        <div class="w-48 sm:w-56 md:w-64 rounded-[2.2rem] md:rounded-[2.6rem] border-[6px] md:border-[8px] border-slate-900 bg-slate-900 overflow-hidden shadow-2xl relative">
                            <!-- Camera Notch -->
                            <div class="absolute top-2 left-1/2 -translate-x-1/2 w-16 h-3.5 bg-slate-900 rounded-full z-30"></div>
                            <!-- Screen Content -->
                            <img src="{{ $appSettings['landing_hero_app_screen_1'] ?? 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400&h=800&fit=crop' }}" 
                                 alt="App Screen 1" class="w-full h-[400px] md:h-[460px] object-cover rounded-[1.8rem] md:rounded-[2.2rem] group-hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>

                    <!-- Middle Main Phone Frame -->
                    <div class="relative z-30 transform hover:scale-[1.03] transition-all duration-700 group">
                        <div class="w-64 sm:w-72 md:w-80 rounded-[2.6rem] md:rounded-[3.2rem] border-[8px] md:border-[10px] border-slate-950 bg-slate-950 overflow-hidden shadow-[0_25px_60px_-15px_rgba(0,0,0,0.35)] relative">
                            <!-- Dynamic Island / Camera Notch -->
                            <div class="absolute top-2.5 left-1/2 -translate-x-1/2 w-24 h-4 bg-slate-950 rounded-full z-30 flex items-center justify-end px-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-800"></div>
                            </div>
                            <!-- Screen Content -->
                            <img src="{{ $appSettings['landing_hero_app_screen_2'] ?? 'https://images.unsplash.com/photo-1551650975-87deedd944c3?w=400&h=800&fit=crop' }}" 
                                 alt="App Screen 2 Main" class="w-full h-[460px] md:h-[530px] object-cover rounded-[2.2rem] md:rounded-[2.7rem] group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <!-- Floating Activity Badges -->
                        <div class="absolute -bottom-4 -left-8 bg-white/95 backdrop-blur-md p-3.5 rounded-full shadow-xl border border-slate-100 items-center gap-3 hidden md:flex px-5 z-40">
                            <div class="w-9 h-9 bg-brand-100 rounded-full flex items-center justify-center text-brand-700">
                                <i class="ph-bold ph-chat-teardrop-dots text-lg"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Live Activity</p>
                                <p class="text-xs font-extrabold text-slate-900">10M+ Messages</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Phone Frame -->
                    <div class="hidden sm:block relative -ml-6 md:-ml-10 z-10 transform rotate-6 hover:rotate-0 transition-all duration-700 scale-90 md:scale-95 group">
                        <div class="w-48 sm:w-56 md:w-64 rounded-[2.2rem] md:rounded-[2.6rem] border-[6px] md:border-[8px] border-slate-900 bg-slate-900 overflow-hidden shadow-2xl relative">
                            <!-- Camera Notch -->
                            <div class="absolute top-2 left-1/2 -translate-x-1/2 w-16 h-3.5 bg-slate-900 rounded-full z-30"></div>
                            <!-- Screen Content -->
                            <img src="{{ $appSettings['landing_hero_app_screen_3'] ?? 'https://images.unsplash.com/photo-1526498460520-4c246339dccb?w=400&h=800&fit=crop' }}" 
                                 alt="App Screen 3" class="w-full h-[400px] md:h-[460px] object-cover rounded-[1.8rem] md:rounded-[2.2rem] group-hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- TRUSTED BY -->
    <section class="py-12 bg-white border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs font-bold text-slate-400 uppercase tracking-widest mb-8">Trusted by agricultural organizations worldwide</p>
            <div class="flex flex-wrap justify-center items-center gap-8 lg:gap-16 opacity-70">
                @foreach(['Agritech Global','FarmCoop','GreenFields','CropConnect','AgriLink','HarvestHub'] as $org)
                <div class="flex items-center gap-2 text-base font-bold text-slate-600 hover:text-brand-700 transition-colors cursor-default">
                    <i class="ph-fill ph-plant text-brand-600"></i> {{ $org }}
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="how-it-works" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="px-4 py-1.5 bg-brand-100 text-brand-800 rounded-full text-xs font-bold uppercase tracking-wider">Simple Setup</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 mt-4 mb-4 tracking-tight">Get Started in 3 Easy Steps</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Join thousands of farmers already connecting and growing together.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                @php $steps=[
                    ['icon'=>'ph-user-plus','title'=>'1. Create Account','desc'=>'Sign up in seconds with your phone number. No complicated forms required.'],
                    ['icon'=>'ph-users-three','title'=>'2. Join Communities','desc'=>'Find specialized groups for your specific crops, regional location, or farming style.'],
                    ['icon'=>'ph-share-network','title'=>'3. Start Sharing','desc'=>'Share tips, ask questions, access market insights, and grow your network.']
                ]; @endphp
                @foreach($steps as $i=>$step)
                <div class="text-center group p-8 rounded-3xl bg-slate-50/70 border border-slate-100 hover:bg-white hover:shadow-xl hover:border-slate-200 transition-all duration-300">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white text-brand-600 rounded-full mb-6 group-hover:bg-brand-600 group-hover:text-white shadow-md transition-all duration-300">
                        <i class="ph-bold {{$step['icon']}} text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">{{$step['title']}}</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">{{$step['desc']}}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features" class="py-24 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="px-4 py-1.5 bg-brand-100 text-brand-800 rounded-full text-xs font-bold uppercase tracking-wider">Powerful Tools</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 mt-4 mb-4 tracking-tight">{{ $appSettings['landing_features_title'] ?? 'Everything You Need to Connect' }}</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">{{ $appSettings['landing_features_description'] ?? 'Our platform provides all the tools you need to communicate, collaborate, and grow your farming network.' }}</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @php $features=[
                    ['t'=>'Real-Time Chat','i'=>'ph-chat-circle-dots','d'=>'Instant messaging with farmers worldwide. Share photos, documents, and voice messages seamlessly.'],
                    ['t'=>'Video Calls','i'=>'ph-video-camera','d'=>'High-quality video calling for remote consultation with agricultural experts and virtual farm tours.'],
                    ['t'=>'Communities','i'=>'ph-users-three','d'=>'Join groups based on crops, regions, or farming techniques. Learn from seasoned experts.'],
                    ['t'=>'Market Insights','i'=>'ph-trend-up','d'=>'Real-time crop market prices and agricultural trends to help you negotiate better sales.'],
                    ['t'=>'Secure & Private','i'=>'ph-shield-check','d'=>'End-to-end encryption ensures your farm conversations and financial details remain private.'],
                    ['t'=>'Weather Updates','i'=>'ph-cloud-sun','d'=>'Localized weather forecasts and climate alerts to help plan your planting and harvesting.']
                ]; @endphp
                @for($i=0;$i<6;$i++)
                @php
                    $ft=$appSettings['landing_feature_'.($i+1).'_title']??$features[$i]['t'];
                    $fi=$appSettings['landing_feature_'.($i+1).'_icon']??$features[$i]['i'];
                    $fd=$appSettings['landing_feature_'.($i+1).'_description']??$features[$i]['d'];
                @endphp
                <div class="bg-white rounded-3xl p-8 border border-slate-100 hover:border-brand-200 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-14 h-14 bg-brand-50 text-brand-600 rounded-full flex items-center justify-center mb-6 group-hover:bg-brand-600 group-hover:text-white transition-colors duration-300">
                        <i class="ph-bold {{$fi}} text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">{{$ft}}</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">{{$fd}}</p>
                </div>
                @endfor
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section id="community" class="py-20 bg-brand-900 text-white relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-600/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <span class="px-4 py-1.5 bg-brand-800 text-brand-200 border border-brand-700 rounded-full text-xs font-bold uppercase tracking-wider">Global Reach</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold mt-4 mb-4 tracking-tight">{{ $appSettings['landing_community_title'] ?? 'Join Our Growing Community' }}</h2>
                <p class="text-brand-100/80 text-lg max-w-2xl mx-auto">{{ $appSettings['landing_community_description'] ?? 'Thousands of farmers trust our platform every day to connect and collaborate.' }}</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6">
                @php $stats=[
                    ['icon'=>'ph-users','val'=>$appSettings['landing_stat_users']??'250K+','lab'=>$appSettings['landing_stat_users_label']??'Active Users'],
                    ['icon'=>'ph-chat-circle-dots','val'=>$appSettings['landing_stat_messages']??'10M+','lab'=>$appSettings['landing_stat_messages_label']??'Messages Sent'],
                    ['icon'=>'ph-globe','val'=>$appSettings['landing_stat_countries']??'120+','lab'=>$appSettings['landing_stat_countries_label']??'Countries'],
                    ['icon'=>'ph-star','val'=>$appSettings['landing_stat_rating']??'4.9','lab'=>$appSettings['landing_stat_rating_label']??'App Store Rating']
                ]; @endphp
                @foreach($stats as $s)
                <div class="text-center p-6 bg-white/5 rounded-3xl border border-white/10 backdrop-blur-sm">
                    <div class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold {{$s['icon']}} text-2xl text-brand-300"></i>
                    </div>
                    <p class="text-4xl sm:text-5xl font-black mb-2 tracking-tight">{{$s['val']}}</p>
                    <p class="text-brand-200/80 text-sm font-semibold uppercase tracking-wider">{{$s['lab']}}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="stories" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="px-4 py-1.5 bg-brand-100 text-brand-800 rounded-full text-xs font-bold uppercase tracking-wider">Success Stories</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 mt-4 mb-4 tracking-tight">What Farmers Say</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Real stories from real farmers using our platform every single day.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
                @php $testimonials=[
                    ['name'=>'John Mwangi','role'=>'Maize Farmer, Kenya','text'=>'This app changed how I sell my crops. I connected with buyers directly and got better prices. The community tips saved my harvest last season!'],
                    ['name'=>'Amina Ibrahim','role'=>'Rice Farmer, Nigeria','text'=>'The weather alerts are incredibly accurate. I was able to plan my planting schedule perfectly. The video call feature helps me consult experts remotely.'],
                    ['name'=>'Peter Ochieng','role'=>'Coffee Farmer, Uganda','text'=>'Being part of the coffee growers community has been invaluable. We share best practices and support each other. My yield has increased by 40%!']
                ]; @endphp
                @foreach($testimonials as $i=>$t)
                <div class="bg-slate-50/80 rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-1 mb-4">
                        @for($s=0;$s<5;$s++)<i class="ph-fill ph-star text-amber-400 text-base"></i>@endfor
                    </div>
                    <p class="text-slate-700 leading-relaxed mb-6 text-sm sm:text-base font-normal">"{{$t['text']}}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-sm shadow-md">
                            {{ substr($t['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-900 text-sm">{{$t['name']}}</p>
                            <p class="text-xs text-slate-500 font-medium">{{$t['role']}}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- DOWNLOAD SECTION (REDUCED HEIGHT CARD, NO IMAGE) -->
    <section id="download" class="py-12 sm:py-16 bg-slate-50 border-t border-slate-100 relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-white via-white to-brand-50/40 rounded-3xl shadow-lg border border-slate-200/80 p-8 sm:p-10 text-center">
                <div class="inline-flex items-center px-4 py-1.5 bg-brand-100 text-brand-800 rounded-full text-xs font-bold uppercase tracking-wider mb-4 mx-auto whitespace-nowrap">
                    Mobile Application
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-3 tracking-tight">{{ $appSettings['landing_download_title'] ?? 'Download Farmers Network Today' }}</h2>
                <p class="text-slate-600 mb-8 leading-relaxed text-sm sm:text-base max-w-xl mx-auto">{{ $appSettings['landing_download_description'] ?? 'Get started in minutes. Download our app and join the largest agricultural community in the world.' }}</p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-md mx-auto">
                    <a href="{{ $appSettings['landing_appstore_url'] ?? '#' }}" class="inline-flex items-center justify-center px-8 py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-extrabold rounded-full transition-all shadow-md hover:shadow-lg text-sm sm:text-base whitespace-nowrap w-full sm:w-auto">
                        App Store
                    </a>
                    <a href="{{ $appSettings['landing_playstore_url'] ?? '#' }}" class="inline-flex items-center justify-center px-8 py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-extrabold rounded-full transition-all shadow-md hover:shadow-lg text-sm sm:text-base whitespace-nowrap w-full sm:w-auto">
                        Google Play
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- EXTENDED CALL TO ACTION BANNER -->
    <section class="py-20 sm:py-24 bg-gradient-to-br from-brand-950 via-brand-900 to-slate-950 text-white text-center relative overflow-hidden">
        <!-- Ambient background lights -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute -top-32 -left-32 w-[30rem] h-[30rem] bg-brand-500 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-[30rem] h-[30rem] bg-emerald-400 rounded-full blur-3xl"></div>
        </div>
        
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="inline-flex items-center px-5 py-2 bg-brand-800/60 border border-brand-500/30 text-brand-200 rounded-full text-xs sm:text-sm font-bold uppercase tracking-widest mb-8 backdrop-blur-md shadow-inner whitespace-nowrap">
                Take The Next Step
            </div>
            
            <h2 class="text-3xl sm:text-5xl font-black mb-6 tracking-tight text-white leading-tight max-w-4xl mx-auto">
                Ready to Grow Your Agricultural Network?
            </h2>
            
            <p class="text-slate-300 mb-10 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed font-light">
                Join over <span class="text-white font-bold underline decoration-brand-400 decoration-2 underline-offset-4">{{ $appSettings['landing_stat_users'] ?? '250,000+' }}</span> farmers using our app for real-time messaging, expert knowledge, and market success.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-xl mx-auto">
                <a href="#download" class="inline-flex items-center justify-center px-8 sm:px-10 py-4 bg-brand-500 hover:bg-brand-400 active:bg-brand-600 text-slate-950 font-black rounded-full transition-all text-base sm:text-lg shadow-xl whitespace-nowrap w-full sm:w-auto">
                    Get Started Free Today
                </a>
                <a href="/admin/login" class="inline-flex items-center justify-center px-8 py-4 bg-white/15 hover:bg-white/25 text-white font-bold rounded-full border border-white/20 transition-all text-base whitespace-nowrap w-full sm:w-auto">
                    Admin Login
                </a>
            </div>
            
            <div class="mt-8 text-xs sm:text-sm text-slate-300 font-medium flex items-center justify-center gap-6 flex-wrap">
                <span class="flex items-center gap-1.5"><i class="ph-bold ph-check-circle text-brand-400 text-lg"></i> No Credit Card Required</span>
                <span class="flex items-center gap-1.5"><i class="ph-bold ph-check-circle text-brand-400 text-lg"></i> 100% Free Download</span>
                <span class="flex items-center gap-1.5"><i class="ph-bold ph-check-circle text-brand-400 text-lg"></i> Instant Setup</span>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <script>
        // Navbar scroll shadow
        const nav = document.getElementById('nav');
        window.addEventListener('scroll',()=>{
            if(window.scrollY>10){
                nav.classList.add('shadow-md');
            }else{
                nav.classList.remove('shadow-md');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a=>{
            a.addEventListener('click',function(e){
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                const t=document.querySelector(targetId);
                if(t) window.scrollTo({top:t.getBoundingClientRect().top+window.scrollY-80,behavior:'smooth'});
                document.getElementById('mobileMenu').classList.add('hidden');
                document.getElementById('menuIcon').className='ph ph-list text-2xl text-slate-700';
            });
        });

        // Mobile menu toggle
        const menuBtn=document.getElementById('menuBtn');
        const mobileMenu=document.getElementById('mobileMenu');
        const menuIcon=document.getElementById('menuIcon');
        menuBtn.addEventListener('click',()=>{
            mobileMenu.classList.toggle('hidden');
            menuIcon.className=mobileMenu.classList.contains('hidden')?'ph ph-list text-2xl text-slate-700':'ph ph-x text-2xl text-slate-700';
        });
    </script>
</body>
</html>

