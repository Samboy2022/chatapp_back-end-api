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
        html{scroll-behavior:smooth}
        ::-webkit-scrollbar{width:6px}
        ::-webkit-scrollbar-track{background:#f8fafc}
        ::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#16a34a,#15803d);border-radius:3px}
        .reveal{opacity:0;transform:translateY(40px);transition:all .8s cubic-bezier(.4,0,.2,1)}
        .reveal.active{opacity:1;transform:translateY(0)}
        .delay-1{transition-delay:.1s}.delay-2{transition-delay:.2s}.delay-3{transition-delay:.3s}.delay-4{transition-delay:.4s}.delay-5{transition-delay:.5s}.delay-6{transition-delay:.6s}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-18px)}}
        @keyframes pulse-glow{0%,100%{opacity:.25}50%{opacity:.5}}
        @keyframes blob{0%,100%{border-radius:60%40%30%70%/60%30%70%40%}25%{border-radius:30%60%70%40%/50%60%30%60%}50%{border-radius:50%60%30%60%/30%60%70%40%}}
        .animate-float{animation:float 6s ease-in-out infinite}
        .animate-float-delay{animation:float 6s ease-in-out infinite;animation-delay:1.5s}
        .animate-pulse-glow{animation:pulse-glow 4s ease-in-out infinite}
        .animate-blob{animation:blob 8s ease-in-out infinite}
        .nav-link{position:relative}
        .nav-link::after{content:'';position:absolute;bottom:-4px;left:0;width:0;height:2px;background:linear-gradient(90deg,#16a34a,#22c55e);border-radius:2px;transition:width .3s ease}
        .nav-link:hover::after{width:100%}
        .btn-shine{position:relative;overflow:hidden}
        .btn-shine::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .6s ease}
        .btn-shine:hover::before{left:100%}
        .feature-card{transition:all .4s cubic-bezier(.4,0,.2,1)}
        .feature-card:hover{transform:translateY(-8px);box-shadow:0 25px 50px -12px rgba(22,163,74,.12)}
        .feature-card:hover .feat-icon{transform:scale(1.1) rotate(4deg)}
        .feat-icon{transition:all .4s cubic-bezier(.4,0,.2,1)}
        @media (prefers-reduced-motion:reduce){*{animation-duration:.01ms!important;transition-duration:.01ms!important}}
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">
    <!-- NAV -->
    <nav id="nav" class="fixed inset-x-0 top-0 z-50 transition-all duration-500 bg-white/70 backdrop-blur-xl border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-brand-600 to-brand-800 rounded-xl flex items-center justify-center shadow-lg shadow-brand-600/20 group-hover:scale-105 transition-transform">
                        <i class="ph-bold ph-plant text-white text-lg"></i>
                    </div>
                    <span class="font-bold text-slate-900 text-lg tracking-tight">{{ $appSettings['app_name'] ?? 'Farmers Network' }}</span>
                </a>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 transition-colors py-1">Features</a>
                    <a href="#how-it-works" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 transition-colors py-1">How It Works</a>
                    <a href="#community" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 transition-colors py-1">Community</a>
                    <a href="#stories" class="nav-link text-sm font-medium text-slate-600 hover:text-brand-700 transition-colors py-1">Stories</a>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <a href="/admin/login" class="text-sm font-medium text-slate-600 hover:text-brand-700 px-3 py-2 transition-colors">Admin</a>
                    <a href="#download" class="btn-shine inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-800 hover:to-brand-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-700/25 transition-all hover:-translate-y-0.5">
                        <i class="ph-bold ph-download-simple"></i> Get App
                    </a>
                </div>
                <button id="menuBtn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="ph ph-list text-xl text-slate-700" id="menuIcon"></i>
                </button>
            </div>
        </div>
        <div id="mobileMenu" class="hidden md:hidden bg-white/95 backdrop-blur-xl border-t border-slate-100">
            <div class="px-4 py-4 space-y-1">
                <a href="#features" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">Features</a>
                <a href="#how-it-works" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">How It Works</a>
                <a href="#community" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">Community</a>
                <a href="#stories" class="block px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">Stories</a>
                <a href="#download" class="block px-4 py-3 mt-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-brand-700 to-brand-600 text-center">Download Now</a>
            </div>
        </div>
    </nav>
    <!-- HERO -->
    <section class="relative min-h-screen flex items-center pt-20 pb-16 overflow-hidden">
        <!-- Background blobs -->
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute -top-20 -left-20 w-[600px] h-[600px] bg-brand-200/40 rounded-full blur-[120px] animate-pulse-glow"></div>
            <div class="absolute -bottom-40 -right-20 w-[700px] h-[700px] bg-emerald-200/30 rounded-full blur-[140px] animate-pulse-glow" style="animation-delay:2s"></div>
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 w-[900px] h-[900px] bg-lime-100/20 rounded-full blur-[180px] animate-pulse-glow" style="animation-delay:4s"></div>
            <div class="absolute inset-0 opacity-[0.025]" style="background-image:radial-gradient(circle,#16a34a 1px,transparent 1px);background-size:48px 48px"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Text -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-200/60 rounded-full text-amber-800 text-sm font-semibold mb-6">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        {{ $appSettings['landing_hero_badge'] ?? 'Trusted by 250,000+ farmers worldwide' }}
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-slate-900 tracking-tight leading-[1.05] mb-6">
                        @php
                            $title = $appSettings['landing_hero_title'] ?? 'Where Farmers Connect, Share & Grow';
                            $hl = $appSettings['landing_hero_highlight'] ?? 'Farmers';
                            echo str_replace($hl, '<span class="bg-gradient-to-r from-brand-600 to-emerald-500 bg-clip-text text-transparent">' . $hl . '</span>', $title);
                        @endphp
                    </h1>

                    <p class="text-lg sm:text-xl text-slate-500 mb-3 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        {{ $appSettings['landing_hero_subheadline'] ?? 'Share crops, ideas & success with your farming community' }}
                    </p>
                    <p class="text-base text-slate-400 mb-10 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                        {{ $appSettings['landing_hero_description'] ?? 'Connect with fellow farmers, get real-time market tips, and build your agricultural network. Simple messaging built for the field.' }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
                        <a href="#download" class="btn-shine group inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-800 hover:to-brand-700 text-white font-bold rounded-2xl shadow-xl shadow-brand-700/20 text-base transition-all hover:-translate-y-0.5">
                            <i class="ph-bold ph-download-simple text-lg"></i>
                            {{ $appSettings['landing_hero_cta_primary'] ?? 'Join the Community' }}
                        </a>
                        <a href="#features" class="group inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-2xl hover:border-brand-400 hover:text-brand-700 hover:shadow-lg transition-all text-base">
                            <i class="ph-bold ph-play-circle text-lg text-brand-600"></i>
                            {{ $appSettings['landing_hero_cta_secondary'] ?? 'See How It Works' }}
                        </a>
                    </div>

                    <!-- Social proof -->
                    <div class="flex items-center justify-center lg:justify-start gap-4">
                        <div class="flex -space-x-2.5">
                            @foreach(['bg-brand-400','bg-emerald-400','bg-amber-400','bg-sky-400','bg-violet-400'] as $c)
                            <div class="w-9 h-9 rounded-full border-2 border-white {{ $c }} flex items-center justify-center text-white text-[10px] font-bold shadow-sm">
                                <i class="ph-fill ph-user"></i>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-left">
                            <div class="flex items-center gap-0.5">
                                @for($i=0;$i<5;$i++)<i class="ph-fill ph-star text-amber-400 text-xs"></i>@endfor
                                <span class="text-sm font-bold text-slate-900 ml-1">4.9</span>
                            </div>
                            <p class="text-[11px] text-slate-500">from 12,000+ reviews</p>
                        </div>
                    </div>
                </div>
                <!-- Visual -->
                <div class="relative lg:h-[580px] flex items-center justify-center">
                    <div class="relative z-10">
                        <div class="absolute inset-0 bg-gradient-to-br from-brand-500/15 to-emerald-500/15 rounded-[2.5rem] transform rotate-2 scale-105"></div>
                        <img src="{{ $appSettings['landing_hero_image'] ?? 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&h=450&fit=crop' }}" 
                             alt="Farmers" class="relative rounded-[2rem] shadow-2xl w-full max-w-md lg:max-w-lg object-cover aspect-[4/3]">
                    </div>
                    <!-- Float card 1 -->
                    <div class="animate-float absolute -top-4 -left-4 lg:left-0 bg-white rounded-2xl shadow-xl p-4 z-20 border border-slate-100 w-[170px]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="ph-bold ph-leaf text-white"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 font-medium">New Farmers</p>
                                <p class="text-lg font-extrabold text-slate-900">+1,234</p>
                            </div>
                        </div>
                        <div class="mt-1.5 flex items-center gap-1">
                            <i class="ph-fill ph-trend-up text-brand-500 text-[10px]"></i>
                            <span class="text-[10px] text-brand-600 font-semibold">+12% this week</span>
                        </div>
                    </div>
                    <!-- Float card 2 -->
                    <div class="animate-float-delay absolute -bottom-4 -right-4 lg:right-0 bg-white rounded-2xl shadow-xl p-4 z-20 border border-slate-100 w-[170px]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="ph-bold ph-chat-circle-dots text-white"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 font-medium">Tips Shared</p>
                                <p class="text-lg font-extrabold text-slate-900">45.2K</p>
                            </div>
                        </div>
                        <div class="mt-1.5 flex items-center gap-1">
                            <i class="ph-fill ph-trend-up text-brand-500 text-[10px]"></i>
                            <span class="text-[10px] text-brand-600 font-semibold">+8% today</span>
                        </div>
                    </div>
                    <!-- Float card 3 -->
                    <div class="animate-float absolute top-1/2 -right-6 lg:-right-10 bg-white rounded-2xl shadow-xl p-3 z-20 border border-slate-100 hidden lg:block" style="animation-delay:3s">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-sky-400 to-indigo-500 rounded-lg flex items-center justify-center">
                                <i class="ph-bold ph-video-camera text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-[9px] text-slate-500">Video Calls</p>
                                <p class="text-xs font-bold text-slate-900">Live Now</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- TRUSTED BY -->
    <section class="py-10 bg-white border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs font-semibold text-slate-400 uppercase tracking-[0.2em] mb-8">Trusted by agricultural organizations</p>
            <div class="flex flex-wrap justify-center items-center gap-8 lg:gap-14 opacity-40 grayscale hover:grayscale-0 transition-all duration-700">
                @foreach(['Agritech Global','FarmCoop','GreenFields','CropConnect','AgriLink','HarvestHub'] as $org)
                <div class="flex items-center gap-2 text-base font-bold text-slate-600 hover:text-brand-700 transition-colors">
                    <i class="ph-fill ph-plant text-brand-600"></i> {{ $org }}
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="how-it-works" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-brand-50 border border-brand-100 rounded-full text-brand-700 text-sm font-semibold mb-4">
                    <i class="ph-bold ph-steps"></i> Simple & Easy
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">Get Started in 3 Steps</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Join thousands of farmers already connecting and growing together.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                @php $steps=[
                    ['icon'=>'ph-user-plus','color'=>'from-blue-500 to-blue-600','title'=>'Create Account','desc'=>'Sign up in seconds with your phone number. No complicated forms.'],
                    ['icon'=>'ph-users-three','color'=>'from-brand-500 to-emerald-600','title'=>'Join Communities','desc'=>'Find groups for your crops, region, or farming style.'],
                    ['icon'=>'ph-share-network','color'=>'from-amber-500 to-orange-600','title'=>'Start Sharing','desc'=>'Share tips, ask questions, and grow your network.']
                ]; @endphp
                @foreach($steps as $i=>$step)
                <div class="relative text-center reveal delay-{{$i+1}}">
                    @if($i<2)<div class="hidden md:block absolute top-16 left-[60%] w-full h-0.5 bg-gradient-to-r from-brand-200 to-transparent"></div>@endif
                    <div class="relative inline-flex mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br {{$step['color']}} rounded-2xl flex items-center justify-center shadow-xl rotate-3 hover:rotate-0 transition-transform duration-300">
                            <i class="ph-bold {{$step['icon']}} text-3xl text-white"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg">{{$i+1}}</div>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">{{$step['title']}}</h3>
                    <p class="text-slate-500 leading-relaxed text-sm">{{$step['desc']}}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- FEATURES -->
    <section id="features" class="py-24 bg-slate-50/60 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-100/30 rounded-full blur-[100px] -translate-y-1/2"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-brand-50 border border-brand-100 rounded-full text-brand-700 text-sm font-semibold mb-4">
                    <i class="ph-bold ph-sparkle"></i> {{ $appSettings['landing_features_badge'] ?? 'Powerful Features' }}
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">{{ $appSettings['landing_features_title'] ?? 'Everything You Need to Connect' }}</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">{{ $appSettings['landing_features_description'] ?? 'Our platform provides all the tools you need to communicate, collaborate, and grow your farming network.' }}</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php $features=[
                    ['t'=>'Real-Time Chat','i'=>'ph-chat-circle-dots','c'=>'from-brand-500 to-emerald-600','d'=>'Instant messaging with farmers worldwide. Share photos, documents, and voice messages.'],
                    ['t'=>'Video Calls','i'=>'ph-video-camera','c'=>'from-blue-500 to-indigo-600','d'=>'High-quality video calling for remote consultations and virtual farm tours.'],
                    ['t'=>'Communities','i'=>'ph-users-three','c'=>'from-amber-500 to-orange-600','d'=>'Join groups based on crops, regions, or farming techniques. Learn from experts.'],
                    ['t'=>'Market Insights','i'=>'ph-trend-up','c'=>'from-violet-500 to-purple-600','d'=>'Real-time crop prices and market trends to help you make informed decisions.'],
                    ['t'=>'Secure & Private','i'=>'ph-shield-check','c'=>'from-rose-500 to-pink-600','d'=>'End-to-end encryption ensures your conversations and data remain private.'],
                    ['t'=>'Weather Updates','i'=>'ph-cloud-sun','c'=>'from-cyan-500 to-teal-600','d'=>'Localized weather forecasts and alerts to help plan your farming activities.']
                ]; @endphp
                @for($i=0;$i<6;$i++)
                @php
                    $ft=$appSettings['landing_feature_'.($i+1).'_title']??$features[$i]['t'];
                    $fi=$appSettings['landing_feature_'.($i+1).'_icon']??$features[$i]['i'];
                    $fd=$appSettings['landing_feature_'.($i+1).'_description']??$features[$i]['d'];
                    $fc=$features[$i]['c'];
                @endphp
                <div class="feature-card reveal delay-{{($i%3)+1}} bg-white rounded-2xl p-7 border border-slate-100 hover:border-brand-200 shadow-sm">
                    <div class="feat-icon w-13 h-13 bg-gradient-to-br {{$fc}} rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-brand-500/10">
                        <i class="ph-bold {{$fi}} text-2xl text-white"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">{{$ft}}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{$fd}}</p>
                </div>
                @endfor
            </div>
        </div>
    </section>
    <!-- STATS -->
    <section id="community" class="py-24 bg-gradient-to-br from-brand-800 via-brand-700 to-emerald-800 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 left-10 w-64 h-64 bg-white/5 rounded-full blur-3xl animate-pulse-glow"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl animate-pulse-glow" style="animation-delay:2s"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-4">{{ $appSettings['landing_community_title'] ?? 'Join Our Growing Community' }}</h2>
                <p class="text-lg text-brand-100/70 max-w-2xl mx-auto">{{ $appSettings['landing_community_description'] ?? 'Thousands of farmers trust our platform every day to connect and collaborate.' }}</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php $stats=[
                    ['icon'=>'ph-users','val'=>$appSettings['landing_stat_users']??'250K+','lab'=>$appSettings['landing_stat_users_label']??'Active Users','d'=>'100'],
                    ['icon'=>'ph-chat-circle-dots','val'=>$appSettings['landing_stat_messages']??'10M+','lab'=>$appSettings['landing_stat_messages_label']??'Messages Sent','d'=>'200'],
                    ['icon'=>'ph-globe','val'=>$appSettings['landing_stat_countries']??'120+','lab'=>$appSettings['landing_stat_countries_label']??'Countries','d'=>'300'],
                    ['icon'=>'ph-star','val'=>$appSettings['landing_stat_rating']??'4.9','lab'=>$appSettings['landing_stat_rating_label']??'App Store Rating','d'=>'400']
                ]; @endphp
                @foreach($stats as $s)
                <div class="reveal delay-{{$s['d']}} bg-white/10 backdrop-blur-md rounded-2xl p-6 lg:p-8 text-center border border-white/10 hover:bg-white/15 hover:-translate-y-1 transition-all duration-300">
                    <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold {{$s['icon']}} text-2xl text-white"></i>
                    </div>
                    <p class="text-4xl lg:text-5xl font-extrabold text-white mb-1">{{$s['val']}}</p>
                    <p class="text-brand-100/60 text-sm font-medium">{{$s['lab']}}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="stories" class="py-24 bg-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.015]" style="background-image:radial-gradient(circle,#16a34a 1px,transparent 1px);background-size:32px 32px"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-100 rounded-full text-amber-700 text-sm font-semibold mb-4">
                    <i class="ph-bold ph-quotes"></i> Farmer Stories
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">What Farmers Say</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Real stories from real farmers using our platform every day.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                @php $testimonials=[
                    ['name'=>'John Mwangi','role'=>'Maize Farmer, Kenya','av'=>'JM','c'=>'from-brand-500 to-emerald-600','text'=>'This app changed how I sell my crops. I connected with buyers directly and got better prices. The community tips saved my harvest last season!'],
                    ['name'=>'Amina Ibrahim','role'=>'Rice Farmer, Nigeria','av'=>'AI','c'=>'from-amber-500 to-orange-600','text'=>'The weather alerts are incredibly accurate. I was able to plan my planting schedule perfectly. The video call feature helps me consult experts remotely.'],
                    ['name'=>'Peter Ochieng','role'=>'Coffee Farmer, Uganda','av'=>'PO','c'=>'from-blue-500 to-indigo-600','text'=>'Being part of the coffee growers community has been invaluable. We share best practices and support each other. My yield has increased by 40%!']
                ]; @endphp
                @foreach($testimonials as $i=>$t)
                <div class="reveal delay-{{($i+1)*2}} bg-slate-50 rounded-2xl p-6 lg:p-8 border border-slate-100 hover:border-brand-200 hover:shadow-lg transition-all">
                    <div class="flex items-center gap-0.5 mb-4">
                        @for($s=0;$s<5;$s++)<i class="ph-fill ph-star text-amber-400 text-sm"></i>@endfor
                    </div>
                    <p class="text-slate-600 leading-relaxed mb-6 text-sm">"{{$t['text']}}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-gradient-to-br {{$t['c']}} rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-md">{{$t['av']}}</div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{$t['name']}}</p>
                            <p class="text-xs text-slate-500">{{$t['role']}}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- DOWNLOAD -->
    <section id="download" class="py-24 bg-slate-50 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-20 right-20 w-[400px] h-[400px] bg-brand-200/20 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-20 left-20 w-[300px] h-[300px] bg-emerald-200/20 rounded-full blur-[80px]"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/40 overflow-hidden border border-slate-100">
                <div class="grid lg:grid-cols-2">
                    <div class="p-8 lg:p-14 flex flex-col justify-center">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-brand-50 border border-brand-100 rounded-full text-brand-700 text-sm font-semibold mb-6 w-fit">
                            <i class="ph-bold ph-device-mobile"></i> {{ $appSettings['landing_download_badge'] ?? 'Available on all platforms' }}
                        </div>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">{{ $appSettings['landing_download_title'] ?? 'Download Today' }}</h2>
                        <p class="text-lg text-slate-500 mb-10 leading-relaxed">{{ $appSettings['landing_download_description'] ?? 'Get started in minutes. Download our app and join the largest agricultural community in the world.' }}</p>
                        <div class="flex flex-col sm:flex-row gap-4 mb-8">
                            <a href="{{ $appSettings['landing_appstore_url'] ?? '#' }}" class="group inline-flex items-center gap-4 px-6 py-4 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl transition-all hover:-translate-y-1 hover:shadow-xl">
                                <i class="ph-bold ph-apple-logo text-3xl group-hover:scale-110 transition-transform"></i>
                                <div class="text-left">
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">Download on the</p>
                                    <p class="text-lg font-bold leading-tight">App Store</p>
                                </div>
                            </a>
                            <a href="{{ $appSettings['landing_playstore_url'] ?? '#' }}" class="group inline-flex items-center gap-4 px-6 py-4 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl transition-all hover:-translate-y-1 hover:shadow-xl">
                                <i class="ph-bold ph-google-play-logo text-3xl group-hover:scale-110 transition-transform"></i>
                                <div class="text-left">
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">Get it on</p>
                                    <p class="text-lg font-bold leading-tight">Google Play</p>
                                </div>
                            </a>
                        </div>
                        <div class="flex flex-wrap items-center gap-5 text-sm text-slate-500">
                            <span class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-brand-500"></i> Free to download</span>
                            <span class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-brand-500"></i> No credit card</span>
                            <span class="flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-brand-500"></i> Cancel anytime</span>
                        </div>
                    </div>
                    <div class="relative bg-gradient-to-br from-brand-600 via-brand-700 to-emerald-800 p-8 lg:p-14 flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute top-10 left-10 w-32 h-32 border border-white/10 rounded-full" style="animation:rotate-slow 25s linear infinite"></div>
                            <div class="absolute bottom-10 right-10 w-48 h-48 border border-white/10 rounded-full" style="animation:rotate-slow 30s linear infinite reverse"></div>
                        </div>
                        <img src="{{ $appSettings['landing_download_image'] ?? 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400&h=500&fit=crop' }}" 
                             alt="App" class="relative z-10 rounded-3xl shadow-2xl max-w-[260px] lg:max-w-xs hover:scale-105 hover:rotate-1 transition-transform duration-500">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="py-16 bg-gradient-to-r from-brand-700 to-emerald-800 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.07]" style="background-image:url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-4">Ready to grow with us?</h2>
            <p class="text-brand-100 mb-8 text-lg">Join {{ $appSettings['landing_stat_users'] ?? '250,000+' }} farmers already on the platform.</p>
            <a href="#download" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-brand-700 font-bold rounded-2xl hover:bg-brand-50 hover:shadow-xl transition-all shadow-lg">
                <i class="ph-bold ph-rocket-launch"></i> Get Started Free
            </a>
        </div>
    </section>
    @include('partials.footer')

    <script>
        // Scroll reveal
        const revealEls = document.querySelectorAll('.reveal');
        const revealObs = new IntersectionObserver((entries)=>{
            entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('active'); revealObs.unobserve(e.target); } });
        },{threshold:0.1,rootMargin:'0px 0px -40px 0px'});
        revealEls.forEach(el=>revealObs.observe(el));

        // Navbar scroll
        const nav = document.getElementById('nav');
        window.addEventListener('scroll',()=>{
            if(window.scrollY>50){
                nav.classList.add('bg-white/95','shadow-md');
                nav.classList.remove('bg-white/70');
            }else{
                nav.classList.remove('bg-white/95','shadow-md');
                nav.classList.add('bg-white/70');
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
