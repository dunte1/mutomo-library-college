<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $landing['seo_meta_description'] ?: 'OLLMCHS Library Management System — Our Lady of Lourdes Mutomo College of Health Sciences | Modern library management for healthcare education' }}">
    @if($landing['seo_meta_keywords'])
    <meta name="keywords" content="{{ $landing['seo_meta_keywords'] }}">
    @endif
    <meta name="robots" content="{{ $landing['seo_robots'] }}">
    <meta name="theme-color" content="#1E4FA3">

    <title>{{ $landing['seo_meta_title'] ?: config('app.name') }} | Library Management System</title>

    @if($landing['seo_canonical_url'])
    <link rel="canonical" href="{{ $landing['seo_canonical_url'] }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $landing['seo_og_title'] ?: $landing['seo_meta_title'] ?: config('app.name') }}">
    <meta property="og:description" content="{{ $landing['seo_og_description'] ?: $landing['seo_meta_description'] ?: 'OLLMCHS Library Management System' }}">
    @if($landing['seo_canonical_url'])
    <meta property="og:url" content="{{ $landing['seo_canonical_url'] }}">
    @endif

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $landing['seo_twitter_title'] ?: $landing['seo_og_title'] ?: $landing['seo_meta_title'] ?: config('app.name') }}">
    <meta name="twitter:description" content="{{ $landing['seo_twitter_description'] ?: $landing['seo_og_description'] ?: $landing['seo_meta_description'] ?: 'OLLMCHS Library Management System' }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-surface-50 dark:bg-surface-900 overflow-x-hidden">
    <div class="relative min-h-screen flex flex-col">

        {{-- Brand accent strip --}}
        <div class="h-1 bg-gradient-to-r from-primary-600 via-secondary-500 to-accent-500"></div>

        {{-- Background blobs --}}
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-primary-200/30 dark:bg-primary-800/20 blur-3xl animate-blob"></div>
            <div class="absolute top-60 -right-40 w-[600px] h-[600px] rounded-full bg-secondary-200/20 dark:bg-secondary-800/15 blur-3xl animate-blob-delayed"></div>
            <div class="absolute -bottom-40 left-1/3 w-[400px] h-[400px] rounded-full bg-accent-200/15 dark:bg-accent-800/10 blur-3xl animate-blob-slow"></div>
        </div>

        {{-- Announcement bar --}}
        <div class="bg-gradient-to-r from-primary-800 via-primary-700 to-secondary-800 text-white text-xs sm:text-sm py-2 px-4 overflow-hidden">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 sm:gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-white/10 text-white/80 text-[10px] font-semibold uppercase tracking-wider shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Open
                </span>
                <span class="text-white/90 font-medium truncate">{{ $display['opening_hours'] ?: 'Mon-Fri: 8:00 AM - 5:00 PM' }}</span>
                <span class="hidden sm:inline text-white/50">|</span>
                <span class="hidden sm:inline text-white/70 truncate">{{ $landing['hero_quote'] }}</span>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="sticky top-0 z-50 backdrop-blur-xl bg-white/70 dark:bg-surface-900/70 border-b border-surface-200/50 dark:border-surface-700/50">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 py-3">
                <a href="/" class="flex items-center gap-3 shrink-0 group">
                    <div class="relative w-9 h-9 sm:w-10 sm:h-10">
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-primary-600 to-primary-500 shadow-soft group-hover:shadow-soft-lg transition-shadow duration-300"></div>
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm sm:text-base font-bold text-surface-900 dark:text-white leading-tight">{{ config('app.name') }}</span>
                        <span class="text-[10px] sm:text-xs text-surface-500 dark:text-surface-400 leading-tight hidden sm:block">Library Management System</span>
                    </div>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('login') }}" wire:navigate class="btn-ghost btn-sm text-xs font-medium text-surface-600 dark:text-surface-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Catalog
                    </a>
                    @if(\Illuminate\Support\Facades\Route::has('subscriptions.plans'))
                    <a href="{{ route('subscriptions.plans') }}" wire:navigate class="btn-ghost btn-sm text-xs font-medium text-surface-600 dark:text-surface-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Subscription Plans
                    </a>
                    @endif
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate class="btn-primary btn-sm sm:btn">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="btn-ghost btn-sm sm:btn text-xs sm:text-sm">Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" wire:navigate class="btn-primary btn-sm sm:btn text-xs sm:text-sm">Get Started</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <main class="flex-1">
            {{-- Hero --}}
            @if($landing['hero_visible'])
            <section class="relative px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16 md:pt-20 pb-16 sm:pb-20 md:pb-24">
                <div class="max-w-5xl mx-auto text-center">

                    {{-- Badge --}}
                    <div class="animate-fade-in-up mb-6 sm:mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 border border-primary-200 dark:border-primary-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                            {{ $landing['hero_badge_text'] }}
                        </span>
                    </div>

                    {{-- Logo mark --}}
                    <div class="animate-fade-in-up">
                        <div class="relative inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 mb-6 sm:mb-8">
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-primary-600 to-primary-500 shadow-soft-lg animate-pulse-slow"></div>
                            <div class="absolute inset-0.5 rounded-2xl bg-white/10 backdrop-blur-sm"></div>
                            <div class="relative w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Heading --}}
                    <h1 class="animate-fade-in-up text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-surface-900 dark:text-white mb-3 sm:mb-4 leading-tight tracking-tight">
                        {{ $landing['hero_title'] ?: config('app.name') }}
                    </h1>

                    {{-- Institution name with gradient --}}
                    <p class="animate-fade-in-up-delayed text-base sm:text-lg md:text-xl font-medium mb-3 bg-gradient-to-r from-primary-600 via-secondary-600 to-accent-600 dark:from-primary-400 dark:via-secondary-400 dark:to-accent-400 bg-clip-text text-transparent">
                        {{ $landing['hero_subtitle'] }}
                    </p>

                    {{-- Tagline --}}
                    <p class="animate-fade-in-up-delayed text-sm sm:text-base text-surface-500 dark:text-surface-400 mb-1 max-w-2xl mx-auto leading-relaxed px-2">
                        {{ $landing['hero_description'] }}
                    </p>
                    <p class="animate-fade-in-up-delayed text-sm sm:text-base text-surface-400 dark:text-surface-500 max-w-xl mx-auto leading-relaxed px-2 italic">
                        "{{ $landing['hero_quote'] }}"
                    </p>

                    {{-- CTAs --}}
                    <div class="animate-fade-in-up-delayed-2 flex flex-wrap justify-center gap-3 sm:gap-4 mt-8 sm:mt-10 mb-16 sm:mb-20">
                        <a href="{{ url($landing['hero_primary_cta_url']) }}" wire:navigate class="btn-primary px-6 sm:px-8 py-3 sm:py-3.5 text-sm sm:text-base shadow-soft-lg hover:shadow-soft-lg hover:-translate-y-0.5 transition-all duration-200 group">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 shrink-0 group-hover:translate-x-0.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            {{ $landing['hero_primary_cta_text'] }}
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ url($landing['hero_secondary_cta_url']) }}" wire:navigate class="btn-outline px-6 sm:px-8 py-3 sm:py-3.5 text-sm sm:text-base border-surface-300 dark:border-surface-600 hover:border-primary-300 dark:hover:border-primary-600 hover:text-primary-700 dark:hover:text-primary-300 hover:-translate-y-0.5 transition-all duration-200">
                                {{ $landing['hero_secondary_cta_text'] }}
                            </a>
                        @endif
                    </div>

                    {{-- Feature cards --}}
                    <div class="animate-fade-in-up-delayed-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5 md:gap-6 text-left max-w-5xl mx-auto">
                        @forelse($features as $feature)
                        <div class="group card p-5 sm:p-6 hover:shadow-soft-lg hover:-translate-y-1 transition-all duration-300 cursor-default relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-primary-500 to-primary-300"></div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/40 dark:to-primary-800/20 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform duration-300 text-primary-600 dark:text-primary-400 text-lg sm:text-xl">
                                {{ $feature->icon }}
                            </div>
                            <h3 class="font-semibold text-surface-900 dark:text-white mb-1.5 sm:mb-2 text-sm sm:text-base">{{ $feature->title }}</h3>
                            <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 leading-relaxed">{{ $feature->description }}</p>
                        </div>
                        @empty
                        <div class="sm:col-span-2 md:col-span-3 text-center py-8 text-surface-400 text-sm">
                            No features available yet.
                        </div>
                        @endforelse
                    </div>

                </div>
            </section>
            @endif

            {{-- Why Choose OLLMCHS Library --}}
            <section class="relative px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="max-w-5xl mx-auto">
                    <div class="text-center mb-10 sm:mb-12">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-secondary-100 text-secondary-700 dark:bg-secondary-900/40 dark:text-secondary-300 border border-secondary-200 dark:border-secondary-700 mb-4">
                            Why Choose Us
                        </span>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-surface-900 dark:text-white">Designed for Health Sciences Education</h2>
                        <p class="text-sm sm:text-base text-surface-500 dark:text-surface-400 mt-3 max-w-2xl mx-auto">Everything you need in a modern library, tailored for healthcare students and professionals.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                        @forelse($whyChooseUs as $item)
                        <div class="group card p-6 sm:p-8 hover:shadow-soft-lg transition-all duration-300 relative overflow-hidden">
                            <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-primary-100/50 dark:bg-primary-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/40 dark:to-primary-800/20 flex items-center justify-center mb-4 text-xl">
                                    {{ $item->icon }}
                                </div>
                                <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">{{ $item->title }}</h3>
                                <p class="text-sm text-surface-500 dark:text-surface-400 leading-relaxed">{{ $item->description }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="md:col-span-2 text-center py-8 text-surface-400 text-sm">
                            No items available yet.
                        </div>
                        @endforelse
                    </div>
                </div>
            </section>

            {{-- Testimonials / Social Proof --}}
            <section class="relative px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="max-w-5xl mx-auto">
                    <div class="text-center mb-10 sm:mb-12">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 border border-primary-200 dark:border-primary-700 mb-4">
                            Testimonials
                        </span>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-surface-900 dark:text-white">What Our Users Say</h2>
                        <p class="text-sm sm:text-base text-surface-500 dark:text-surface-400 mt-3 max-w-2xl mx-auto">Hear from students and faculty who use OLLMCHS Library every day.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @forelse($testimonials as $testimonial)
                        <div class="card p-6 relative">
                            <div class="absolute top-4 right-4 text-primary-200 dark:text-primary-800">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-200 to-primary-100 dark:from-primary-800 dark:to-primary-700 flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold text-sm">{{ $testimonial->initials }}</div>
                                <div>
                                    <p class="font-semibold text-surface-900 dark:text-white text-sm">{{ $testimonial->author_name }}</p>
                                    <p class="text-xs text-surface-500 dark:text-surface-400">{{ $testimonial->author_role }}</p>
                                </div>
                            </div>
                            <p class="text-sm text-surface-600 dark:text-surface-300 leading-relaxed italic">"{{ $testimonial->content }}"</p>
                        </div>
                        @empty
                        <div class="md:col-span-3 text-center py-8 text-surface-400 text-sm">
                            No testimonials available yet.
                        </div>
                        @endforelse
                    </div>
                </div>
            </section>

            {{-- Featured Books --}}
            @if($landing['featured_books_visible'] && $featuredBooks->isNotEmpty())
            <section class="relative px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="max-w-5xl mx-auto">
                    <div class="text-center mb-10 sm:mb-12">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 border border-primary-200 dark:border-primary-700 mb-4">
                            {{ $landing['featured_books_badge'] }}
                        </span>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-surface-900 dark:text-white">{{ $landing['featured_books_heading'] }}</h2>
                        <p class="text-sm sm:text-base text-surface-500 dark:text-surface-400 mt-3 max-w-2xl mx-auto">{{ $landing['featured_books_description'] }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach($featuredBooks as $book)
                        <div class="group card p-5 hover:shadow-soft-lg hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-primary-500 to-primary-300"></div>
                            @if($book->cover_image)
                            <div class="w-full h-40 rounded-lg overflow-hidden mb-4 bg-surface-100 dark:bg-surface-800">
                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            @else
                            <div class="w-full h-40 rounded-lg mb-4 bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center">
                                <svg class="w-12 h-12 text-primary-400 dark:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            @endif
                            <h3 class="font-semibold text-surface-900 dark:text-white text-sm sm:text-base mb-1 line-clamp-2">{{ $book->title }}</h3>
                            @if($book->authors->isNotEmpty())
                            <p class="text-xs text-surface-500 dark:text-surface-400 mb-2">{{ $book->authors->pluck('name')->implode(', ') }}</p>
                            @endif
                            @if($book->category)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400">{{ $book->category->name }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            {{-- Stats --}}
            @if($landing['stats_visible'])
            <section class="relative px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="max-w-5xl mx-auto">
                    <div class="card p-6 sm:p-8 md:p-10 border-t-4 border-t-primary-500">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 divide-y sm:divide-y-0 sm:divide-x divide-surface-200 dark:divide-surface-700">
                            <div class="text-center pt-4 sm:pt-0">
                                <p class="text-2xl sm:text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['resources'] > 0 ? number_format($stats['resources']) . '+' : '0' }}</p>
                                <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 mt-1 font-medium">{{ $landing['stats_resource_label'] }}</p>
                                <p class="text-[10px] sm:text-xs text-surface-400 dark:text-surface-500 mt-0.5">{{ $landing['stats_resource_subtext'] }}</p>
                            </div>
                            <div class="text-center pt-4 sm:pt-0">
                                <p class="text-2xl sm:text-3xl font-bold text-secondary-600 dark:text-secondary-400">{{ $stats['members'] > 0 ? number_format($stats['members']) . '+' : '0' }}</p>
                                <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 mt-1 font-medium">{{ $landing['stats_member_label'] }}</p>
                                <p class="text-[10px] sm:text-xs text-surface-400 dark:text-surface-500 mt-0.5">{{ $landing['stats_member_subtext'] }}</p>
                            </div>
                            <div class="text-center pt-4 sm:pt-0">
                                <p class="text-2xl sm:text-3xl font-bold text-accent-600 dark:text-accent-400">{{ number_format($stats['borrowsToday']) }}</p>
                                <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 mt-1 font-medium">{{ $landing['stats_borrow_label'] }}</p>
                                <p class="text-[10px] sm:text-xs text-surface-400 dark:text-surface-500 mt-0.5">{{ $landing['stats_borrow_subtext'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            {{-- Featured Digital Assets --}}
            @if($landing['featured_digital_assets_visible'] && $featuredDigitalAssets->isNotEmpty())
            <section class="relative px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="max-w-5xl mx-auto">
                    <div class="text-center mb-10 sm:mb-12">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-secondary-100 text-secondary-700 dark:bg-secondary-900/40 dark:text-secondary-300 border border-secondary-200 dark:border-secondary-700 mb-4">
                            {{ $landing['featured_digital_assets_badge'] }}
                        </span>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-surface-900 dark:text-white">{{ $landing['featured_digital_assets_heading'] }}</h2>
                        <p class="text-sm sm:text-base text-surface-500 dark:text-surface-400 mt-3 max-w-2xl mx-auto">{{ $landing['featured_digital_assets_description'] }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach($featuredDigitalAssets as $asset)
                        <div class="group card p-5 hover:shadow-soft-lg hover:-translate-y-1 transition-all duration-300 relative overflow-hidden border-t-4 border-t-secondary-500">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-secondary-100 to-secondary-50 dark:from-secondary-900/40 dark:to-secondary-800/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-secondary-600 dark:text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-surface-900 dark:text-white text-sm sm:text-base mb-1">{{ $asset->title }}</h3>
                            @if($asset->author)
                            <p class="text-xs text-surface-500 dark:text-surface-400 mb-2">{{ $asset->author }}</p>
                            @endif
                            <p class="text-xs text-surface-500 dark:text-surface-400 leading-relaxed line-clamp-2">{{ $asset->description }}</p>
                            <div class="flex items-center gap-2 mt-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-secondary-100 dark:bg-secondary-900/40 text-secondary-700 dark:text-secondary-300">{{ ucfirst($asset->file_type) }}</span>
                                @if($asset->allow_download)
                                <span class="text-[10px] text-surface-400 dark:text-surface-500">Downloadable</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            {{-- Newsletter & Stay Connected --}}
            @if($landing['newsletter_visible'])
            <section class="relative px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="max-w-5xl mx-auto">
                    <div class="card p-8 sm:p-10 md:p-12 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 border-0 overflow-hidden relative">
                        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        <div class="relative text-center max-w-xl mx-auto">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-sm mb-6 ring-1 ring-white/20">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ $landing['newsletter_heading'] }}</h3>
                            <p class="text-primary-200/80 text-sm sm:text-base mb-6 max-w-md mx-auto">{{ $landing['newsletter_description'] }}</p>
                            <livewire:newsletter-subscribe theme="hero" wire:key="nl-hero" />
                            <p class="text-primary-300/50 text-xs mt-4">{{ $landing['newsletter_disclaimer'] }}</p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            {{-- Mobile App Promo --}}
            @if($landing['mobile_visible'])
            <section class="relative px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
                <div class="max-w-5xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 border border-primary-200 dark:border-primary-700 mb-4">
                                {{ $landing['mobile_badge'] }}
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-bold text-surface-900 dark:text-white mb-4">{{ $landing['mobile_heading'] }}</h2>
                            <p class="text-sm sm:text-base text-surface-500 dark:text-surface-400 leading-relaxed mb-6">{{ $landing['mobile_description'] }}</p>
                            <div class="flex flex-wrap gap-3">
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 text-xs font-medium">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Browse & Search
                                </div>
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 text-xs font-medium">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Digital Library
                                </div>
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 text-xs font-medium">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    M-Pesa Payments
                                </div>
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 text-xs font-medium">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Notifications
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="card p-6 sm:p-8 border-t-4 border-t-primary-500">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/40 dark:to-primary-800/20 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-surface-900 dark:text-white">Quick Stats</p>
                                        <p class="text-xs text-surface-500 dark:text-surface-400">Real-time library overview</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-surface-50 dark:bg-surface-800/50">
                                        <span class="text-xs text-surface-500 dark:text-surface-400">Available Online</span>
                                        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">24/7</span>
                                    </div>
                                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-surface-50 dark:bg-surface-800/50">
                                        <span class="text-xs text-surface-500 dark:text-surface-400">Mobile Friendly</span>
                                        <span class="text-sm font-semibold text-secondary-600 dark:text-secondary-400">Yes</span>
                                    </div>
                                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-surface-50 dark:bg-surface-800/50">
                                        <span class="text-xs text-surface-500 dark:text-surface-400">Data Security</span>
                                        <span class="text-sm font-semibold text-accent-600 dark:text-accent-400">Encrypted</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

        </main>

        {{-- Footer --}}
        <footer class="border-t border-surface-200 dark:border-surface-700 bg-white/80 dark:bg-surface-900/80 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Main footer grid --}}
                <div class="py-10 sm:py-12">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 sm:gap-10">

                        {{-- Column 1: Brand --}}
                        <div class="sm:col-span-2 lg:col-span-1">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-500 flex items-center justify-center shadow-soft">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-surface-900 dark:text-white">{{ $display['site_name'] }}</span>
                            </div>
                            <p class="text-xs text-surface-500 dark:text-surface-400 leading-relaxed mb-4">{{ $display['site_description'] ?: 'Empowering health sciences education through seamless access to knowledge resources.' }}</p>
                            {{-- Social icons --}}
                            <div class="flex items-center gap-2.5">
                                @if ($footer['footer_facebook_url'])
                                <a href="{{ $footer['footer_facebook_url'] }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-surface-500 dark:text-surface-400 hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-primary-900/30 dark:hover:text-primary-400 transition-all duration-200" aria-label="Facebook">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                @endif
                                @if ($footer['footer_twitter_url'])
                                <a href="{{ $footer['footer_twitter_url'] }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-surface-500 dark:text-surface-400 hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-primary-900/30 dark:hover:text-primary-400 transition-all duration-200" aria-label="Twitter/X">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                </a>
                                @endif
                                @if ($footer['footer_instagram_url'])
                                <a href="{{ $footer['footer_instagram_url'] }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-surface-500 dark:text-surface-400 hover:bg-secondary-100 hover:text-secondary-600 dark:hover:bg-secondary-900/30 dark:hover:text-secondary-400 transition-all duration-200" aria-label="Instagram">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                </a>
                                @endif
                                @if ($footer['footer_youtube_url'])
                                <a href="{{ $footer['footer_youtube_url'] }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-surface-500 dark:text-surface-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-all duration-200" aria-label="YouTube">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                                @endif
                                @if ($footer['footer_linkedin_url'])
                                <a href="{{ $footer['footer_linkedin_url'] }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-surface-500 dark:text-surface-400 hover:bg-primary-100 hover:text-primary-600 dark:hover:bg-primary-900/30 dark:hover:text-primary-400 transition-all duration-200" aria-label="LinkedIn">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- Column 2: Quick Links --}}
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-widest text-surface-600 dark:text-surface-400 mb-4">Quick Links</h4>
                            <ul class="space-y-2.5">
                                <li><a href="{{ route('login') }}" class="text-xs text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Sign In
                                </a></li>
                                @if (Route::has('register'))
                                <li><a href="{{ route('register') }}" class="text-xs text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Create Account
                                </a></li>
                                @endif
                                <li><a href="{{ route('login') }}" class="text-xs text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Browse Catalog
                                </a></li>
                                @if(\Illuminate\Support\Facades\Route::has('subscriptions.plans'))
                                <li><a href="{{ route('subscriptions.plans') }}" wire:navigate class="text-xs text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Subscription Plans
                                </a></li>
                                @endif
                                <li><a href="{{ route('privacy') }}" class="text-xs text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Privacy Policy
                                </a></li>
                                <li><a href="{{ route('terms') }}" class="text-xs text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Terms of Service
                                </a></li>
                            </ul>
                        </div>

                        {{-- Column 3: Library Hours & Contact --}}
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-widest text-surface-600 dark:text-surface-400 mb-4">Library Hours</h4>
                            <div class="space-y-2 text-xs text-surface-500 dark:text-surface-400 mb-5">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $display['opening_hours'] ?: 'Mon-Fri: 8:00 AM - 5:00 PM' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-secondary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    @if ($display['library_address'])
                                        <span>{{ $display['library_address'] }}</span>
                                    @else
                                        <span>Mutomo, Kitui County, Kenya</span>
                                    @endif
                                </div>
                            </div>

                            <h4 class="text-xs font-semibold uppercase tracking-widest text-surface-600 dark:text-surface-400 mb-4">Contact Us</h4>
                            <ul class="space-y-2.5">
                                @if ($display['library_email'])
                                <li><a href="mailto:{{ $display['library_email'] }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $display['library_email'] }}
                                </a></li>
                                @endif
                                @if ($display['library_phone'])
                                <li><a href="tel:{{ $display['library_phone'] }}" class="text-xs text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $display['library_phone'] }}
                                </a></li>
                                @endif
                            </ul>
                        </div>

                        {{-- Column 4: Newsletter --}}
                        @if($footer['footer_newsletter_visible'])
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-widest text-surface-600 dark:text-surface-400 mb-4">Newsletter</h4>
                            <p class="text-xs text-surface-500 dark:text-surface-400 leading-relaxed mb-4">Get the latest updates on new arrivals, library events, and health sciences research.</p>
                            <livewire:newsletter-subscribe theme="footer" wire:key="nl-footer" />
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Bottom bar --}}
                <div class="border-t border-surface-200 dark:border-surface-700 py-5 sm:py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-[10px] sm:text-xs text-surface-400 dark:text-surface-500">
                        &copy; {{ date('Y') }} {{ $display['site_name'] }}. {{ $footer['footer_copyright'] ?: 'All rights reserved.' }}
                    </p>
                    <div class="flex items-center gap-3 sm:gap-4">
                        <span class="text-[10px] text-surface-400 dark:text-surface-500 hidden sm:inline">Made with care for health sciences education</span>
                        <a href="{{ route('privacy') }}" class="text-[10px] sm:text-xs text-surface-400 dark:text-surface-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Privacy Policy</a>
                        <a href="{{ route('terms') }}" class="text-[10px] sm:text-xs text-surface-400 dark:text-surface-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
