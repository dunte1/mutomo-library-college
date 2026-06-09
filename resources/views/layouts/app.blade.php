<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', config('app.name') . ' - Library Management System')">
    <meta name="keywords" content="@yield('meta_keywords', 'library, books, OLLMCHS, education, digital library, Kenya')">
    <meta name="theme-color" content="#153168">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icons/icon-152.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/icon-72.png">
    <link rel="apple-touch-icon" sizes="96x96" href="/icons/icon-96.png">
    <link rel="apple-touch-icon" sizes="128x128" href="/icons/icon-128.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/icon-144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="384x384" href="/icons/icon-384.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512.png">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'OLLMCHS Library') }}</title>

    <meta property="og:title" content="@yield('title', 'Dashboard') | {{ config('app.name', 'OLLMCHS Library') }}">
    <meta property="og:description" content="@yield('meta_description', config('app.name') . ' - Library Management System')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'Dashboard') | {{ config('app.name', 'OLLMCHS Library') }}">
    <meta name="twitter:description" content="@yield('meta_description', config('app.name') . ' - Library Management System')">

    @php
        $faviconPath = null;
        try { $faviconPath = \App\Modules\Settings\Models\Setting::value('favicon'); } catch (\Throwable $e) {}
    @endphp
    @if($faviconPath)
        <link rel="icon" type="image/{{ str_ends_with($faviconPath, '.svg') ? 'svg+xml' : (str_ends_with($faviconPath, '.ico') ? 'x-icon' : 'png') }}" href="{{ asset('storage/' . $faviconPath) }}">
        <meta property="og:image" content="{{ asset('storage/' . $faviconPath) }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='14' font-size='14'>📚</text></svg>">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased text-surface-700 dark:text-surface-200 bg-surface-50 dark:bg-surface-900"
      x-data="{ mobileMenuOpen: false }"
      @toggle-sidebar.window="mobileMenuOpen = !mobileMenuOpen"
      @keydown.escape.window="mobileMenuOpen = false">

    <div class="flex h-screen overflow-hidden">
        <livewire:layout.sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">
            <livewire:layout.header />

            <main class="flex-1 overflow-y-auto scrollbar-thin p-4 md:p-6 lg:p-8 pb-24 md:pb-6 lg:pb-8">
                @if (isset($header))
                    <div class="page-header flex items-center justify-between">
                        <div>
                            <h1 class="page-title">{{ $header }}</h1>
                            @if (isset($subtitle))
                                <p class="page-subtitle">{{ $subtitle }}</p>
                            @endif
                        </div>
                        @if (isset($actions))
                            <div class="shrink-0">{{ $actions }}</div>
                        @endif
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @include('components.layout.mobile-drawer')
    @include('components.layout.mobile-bottom-nav')

    <livewire:layout.notifications />

    @livewire('global-search')

    <script>
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                Livewire.dispatch('openGlobalSearch');
            }
            if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                e.preventDefault();
                Livewire.dispatch('openGlobalSearch');
            }
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
