<div>
    @if($subscribed)
        <div class="{{ $theme === 'hero' ? 'bg-white/10 ring-1 ring-white/20' : 'bg-green-50 border border-green-200' }} rounded-xl p-4 text-center">
            <svg class="w-8 h-8 {{ $theme === 'hero' ? 'text-green-300' : 'text-green-500' }} mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="{{ $theme === 'hero' ? 'text-white' : 'text-green-800' }} text-sm font-medium">Thank you for subscribing!</p>
            <p class="{{ $theme === 'hero' ? 'text-primary-200' : 'text-green-600' }} text-xs mt-1">Stay tuned for library updates.</p>
        </div>
    @else
        <form wire:submit="subscribe" class="{{ $theme === 'hero' ? 'flex flex-col sm:flex-row gap-3 max-w-md mx-auto' : 'space-y-3' }}">
            <div class="flex-1">
                <input type="email" wire:model="email" placeholder="{{ $theme === 'hero' ? 'Enter your email address' : 'Your email address' }}" required
                    class="{{ $theme === 'hero' ? 'w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-primary-300/60 text-sm focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent backdrop-blur-sm' : 'w-full px-3 py-2.5 text-xs rounded-xl border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-surface-100 placeholder-surface-400 dark:placeholder-surface-500 focus:border-primary-500 focus:ring-primary-500 transition-colors' }}">
                @error('email') <p class="{{ $theme === 'hero' ? 'text-red-300' : 'text-red-600' }} text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled"
                class="{{ $theme === 'hero' ? 'px-6 py-3 rounded-xl bg-white text-primary-700 font-semibold text-sm hover:bg-primary-50 transition-colors shadow-soft whitespace-nowrap disabled:opacity-50' : 'w-full px-3 py-2.5 text-xs font-semibold rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-colors disabled:opacity-50' }}">
                <span wire:loading.remove>Subscribe</span>
                <span wire:loading>Subscribing...</span>
            </button>
        </form>
        @if($error)
            <p class="text-red-500 text-xs mt-2">{{ $error }}</p>
        @endif
    @endif
</div>
