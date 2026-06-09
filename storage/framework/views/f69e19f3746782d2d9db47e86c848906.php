<nav class="mobile-bottom-nav md:hidden" aria-label="Bottom navigation"
     x-data="{
         currentPath: window.location.pathname,
         init() {
             document.addEventListener('livewire:navigated', () => {
                 this.currentPath = window.location.pathname;
             });
         },
         isActive(pattern) {
             const p = this.currentPath.replace(/\/+$/, '') || '/';
             if (pattern === 'dashboard') return p === '<?php echo e(parse_url(route('dashboard'), PHP_URL_PATH)); ?>' || p === '/';
             if (pattern === 'catalog') return p.startsWith('<?php echo e(parse_url(route('catalog.books.index'), PHP_URL_PATH)); ?>');
             if (pattern === 'circulation') return p.startsWith('<?php echo e(parse_url(route('circulation.index'), PHP_URL_PATH)); ?>');
             if (pattern === 'notifications') return p.startsWith('<?php echo e(parse_url(route('notifications.index'), PHP_URL_PATH)); ?>');
             if (pattern === 'menu') return p.startsWith('<?php echo e(parse_url(route('settings.index'), PHP_URL_PATH)); ?>') || p.startsWith('<?php echo e(parse_url(route('profile'), PHP_URL_PATH)); ?>');
             return false;
         }
     }">
    <div class="mobile-bottom-nav-inner">
        <a href="<?php echo e(route('dashboard')); ?>" wire:navigate
           :class="{ 'active': isActive('dashboard') }"
           class="mobile-bottom-nav-item ripple">
            <div class="nav-indicator" aria-hidden="true"></div>
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="nav-label">Dashboard</span>
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-books')): ?>
        <a href="<?php echo e(route('catalog.books.index')); ?>" wire:navigate
           :class="{ 'active': isActive('catalog') }"
           class="mobile-bottom-nav-item ripple">
            <div class="nav-indicator" aria-hidden="true"></div>
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="nav-label">Library</span>
        </a>
        <?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|admin|librarian|assistant-librarian|finance-officer')): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-circulation')): ?>
        <a href="<?php echo e(route('circulation.index')); ?>" wire:navigate
           :class="{ 'active': isActive('circulation') }"
           class="mobile-bottom-nav-item ripple">
            <div class="nav-indicator" aria-hidden="true"></div>
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span class="nav-label">Borrowings</span>
        </a>
        <?php endif; ?>
        <?php else: ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-borrows')): ?>
        <a href="<?php echo e(route('circulation.index')); ?>" wire:navigate
           :class="{ 'active': isActive('circulation') }"
           class="mobile-bottom-nav-item ripple">
            <div class="nav-indicator" aria-hidden="true"></div>
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span class="nav-label">Borrowings</span>
        </a>
        <?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <a href="<?php echo e(route('notifications.index')); ?>" wire:navigate
           :class="{ 'active': isActive('notifications') }"
           class="mobile-bottom-nav-item ripple">
            <div class="nav-indicator" aria-hidden="true"></div>
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="nav-label">Notifications</span>
        </a>
        <button @click="$dispatch('toggle-sidebar')"
                :class="{ 'active': isActive('menu') }"
                class="mobile-bottom-nav-item ripple">
            <div class="nav-indicator" aria-hidden="true"></div>
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="nav-label">Menu</span>
        </button>
    </div>
</nav>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\resources\views/components/layout/mobile-bottom-nav.blade.php ENDPATH**/ ?>