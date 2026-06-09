<template x-teleport="body">
    <div x-show="mobileMenuOpen"
         x-cloak
         class="fixed inset-0 z-50 md:hidden"
         role="dialog" aria-modal="true" aria-label="Navigation menu">
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             class="mobile-drawer-overlay">
        </div>
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-transform ease-out duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition-transform ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             @click.away="mobileMenuOpen = false"
             class="mobile-drawer-panel">
            <div class="flex items-center justify-between h-16 px-4 border-b border-primary-700/50 shrink-0">
                <a href="{{ route('dashboard') }}" wire:navigate @click="mobileMenuOpen = false" class="flex items-center gap-3">
                    @php $logoPath = null; try { $logoPath = \App\Modules\Settings\Models\Setting::value('site_logo'); } catch (\Throwable $e) {} @endphp
                    @if($logoPath)
                        <div class="w-9 h-9 rounded-xl shrink-0 overflow-hidden bg-white/10 flex items-center justify-center">
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ config('app.name') }}" loading="lazy" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    @endif
                    <span class="text-sm font-bold text-white">{{ config('app.name') }}</span>
                </a>
                <button @click="mobileMenuOpen = false" class="p-2 rounded-xl hover:bg-white/10 text-primary-200 transition-colors" aria-label="Close menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto scrollbar-thin p-3 space-y-1" aria-label="Mobile navigation links">
                @include('components.layout.nav-items')
            </nav>
            <div class="p-3 border-t border-primary-700/50 space-y-2">
                <button @click="$dispatch('toggle-dark-mode')"
                        class="sidebar-link w-full justify-start">
                    <svg x-show="!document.documentElement.classList.contains('dark')" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg x-show="document.documentElement.classList.contains('dark')" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-sm font-medium" x-text="document.documentElement.classList.contains('dark') ? 'Light Mode' : 'Dark Mode'"></span>
                </button>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="sidebar-link w-full justify-start">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="text-sm font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
