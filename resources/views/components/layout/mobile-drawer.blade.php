<template x-teleport="body">
    <div x-show="mobileMenuOpen"
         x-cloak
         class="fixed inset-0 z-50 md:hidden"
         role="dialog" aria-modal="true" aria-label="Navigation menu"
         @keydown.escape.window="mobileMenuOpen = false">
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
            <div class="flex items-center justify-between h-16 px-4 border-b border-white/[0.08] shrink-0">
                <a href="{{ route('dashboard') }}" wire:navigate @click="mobileMenuOpen = false" class="flex items-center gap-3">
                    @php $logoPath = null; try { $logoPath = \App\Modules\Settings\Models\Setting::value('site_logo'); } catch (\Throwable $e) {} @endphp
                    @if($logoPath)
                        <div class="w-9 h-9 rounded-xl shrink-0 overflow-hidden bg-white/10 flex items-center justify-center">
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ config('app.name') }}" loading="lazy" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    @endif
                    <span class="text-sm font-bold text-white">{{ config('app.name') }}</span>
                </a>
                <button @click="mobileMenuOpen = false" class="p-2 rounded-lg hover:bg-white/10 text-primary-200 transition-colors" aria-label="Close menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto scrollbar-thin p-3 space-y-0.5" aria-label="Mobile navigation links">
                @include('components.layout.nav-items')
            </nav>
            <div class="p-3 border-t border-white/[0.08] space-y-2">
                @php
                    $user = auth()->user();
                    $userName = $user?->name ?? 'User';
                    $userRole = $user?->roles->first()?->name ?? 'User';
                    $userInitials = strtoupper(substr($userName, 0, 2));
                @endphp
                <div class="flex items-center gap-3 px-3 py-2 rounded-lg">
                    @if($user && $user->avatar)
                        <div class="w-9 h-9 rounded-full shrink-0 overflow-hidden bg-white/10">
                            <img src="{{ $user->avatar }}" alt="{{ $userName }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-full shrink-0 bg-primary-600/80 flex items-center justify-center text-white text-xs font-bold ring-2 ring-white/10">
                            {{ $userInitials }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white truncate">{{ $userName }}</p>
                        <p class="text-xs text-primary-300/60 truncate">{{ $userRole }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile') }}" wire:navigate @click="mobileMenuOpen = false"
                       class="sidebar-link flex-1 justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-sm">Profile</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="sidebar-link w-full justify-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="text-sm">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
