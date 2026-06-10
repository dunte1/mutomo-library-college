<?php

use Livewire\Volt\Component;

?>

<aside
    x-data="{ collapsed: false, mobileOpen: false }"
    :class="collapsed ? 'w-20' : 'w-64'"
    x-bind:data-collapsed="collapsed.toString()"
    class="hidden md:flex flex-col bg-primary-800 border-r border-primary-900 transition-all duration-300 ease-in-out"
    role="navigation" aria-label="Main navigation"
>
    <div class="flex items-center justify-between h-16 px-4 border-b border-primary-700/50">
        <a href="<?php echo e(route('dashboard')); ?>" wire:navigate class="flex items-center gap-3 sidebar-logo-link">
            <?php $logoPath = null; try { $logoPath = \App\Modules\Settings\Models\Setting::value('site_logo'); } catch (\Throwable $e) {} ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoPath): ?>
                <div class="w-9 h-9 rounded-xl shrink-0 overflow-hidden bg-white/10 flex items-center justify-center">
                    <img src="<?php echo e(asset('storage/' . $logoPath)); ?>" alt="<?php echo e(config('app.name')); ?>" loading="lazy" class="w-full h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <span class="text-sm font-bold text-white whitespace-nowrap sidebar-hidden-when-collapsed">
                <?php echo e(config('app.name')); ?>

            </span>
        </a>
        <button @click="collapsed = !collapsed" class="p-1.5 rounded-lg hover:bg-white/10 text-primary-200" aria-label="Toggle sidebar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto scrollbar-thin p-3 space-y-1" aria-label="Sidebar links">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|admin|librarian|assistant-librarian|finance-officer|ict-admin|department-head')): ?>
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard')): ?>
            <a href="<?php echo e(route('dashboard')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('dashboard') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-books')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Catalog</p>
            </div>
            <a href="<?php echo e(route('catalog.books.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.books.index') || request()->routeIs('catalog.books.show') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Books Catalog</span>
            </a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-categories')): ?>
            <a href="<?php echo e(route('catalog.categories')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.categories*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Categories</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-authors')): ?>
            <a href="<?php echo e(route('catalog.authors')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.authors*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Authors</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-publishers')): ?>
            <a href="<?php echo e(route('catalog.publishers')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.publishers*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Publishers</span>
            </a>
            <?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('catalog.inventory')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-inventory')): ?>
            <a href="<?php echo e(route('catalog.inventory')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.inventory*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Inventory</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('catalog.new-arrivals')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-new-arrivals')): ?>
            <a href="<?php echo e(route('catalog.new-arrivals')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.new-arrivals*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">New Arrivals</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['import-books', 'create-books'])): ?>
            <a href="<?php echo e(route('catalog.books.bulk-upload')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.books.bulk-upload') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Import Books</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('export-books')): ?>
            <a href="<?php echo e(route('catalog.books.index', ['export' => 'csv'])); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.books.index') && request()->export ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Export Books</span>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-circulation')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Circulation</p>
            </div>
            <a href="<?php echo e(route('circulation.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.index') && !request()->has('tab') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Overview</span>
            </a>
            <div class="space-y-1">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('borrow-books')): ?>
            <a href="<?php echo e(route('circulation.issue')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.issue') ? 'sidebar-link-active' : ''); ?> ml-4"
                >
                <span class="text-sm">Issue Book</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('return-books')): ?>
            <a href="<?php echo e(route('circulation.return')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.return') ? 'sidebar-link-active' : ''); ?> ml-4"
                >
                <span class="text-sm">Return Book</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('renew-books')): ?>
            <a href="<?php echo e(route('circulation.index', ['tab' => 'active'])); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.index') && request()->tab === 'active' ? 'sidebar-link-active' : ''); ?> ml-4"
                >
                <span class="text-sm">Renew Book</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-reservations')): ?>
            <a href="<?php echo e(route('circulation.reservations')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.reservations*') ? 'sidebar-link-active' : ''); ?> ml-4"
                >
                <span class="text-sm">Reservations</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-waitlists')): ?>
            <a href="<?php echo e(route('circulation.waitlists')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.waitlists*') ? 'sidebar-link-active' : ''); ?> ml-4"
                >
                <span class="text-sm">Waitlists</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-borrows')): ?>
            <a href="<?php echo e(route('circulation.index', ['tab' => 'borrows'])); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.index') && request()->query('tab') === 'borrows' ? 'sidebar-link-active' : ''); ?> ml-4"
                >
                <span class="text-sm">Borrows / Loans</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('override-due-dates')): ?>
            <a href="<?php echo e(route('circulation.override-due-dates')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.override-due-dates*') ? 'sidebar-link-active' : ''); ?> ml-4"
                >
                <span class="text-sm">Override Due Dates</span>
            </a>
            <?php endif; ?>
            </div>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-members')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Members</p>
            </div>
            <a href="<?php echo e(route('members.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('members.index') || request()->routeIs('members.create') || request()->routeIs('members.edit') || request()->routeIs('members.show') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Members List</span>
            </a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-library-cards')): ?>
            <a href="<?php echo e(route('members.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('members.card*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Library Cards</span>
            </a>
            <?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('members.requests')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-membership-requests')): ?>
            <a href="<?php echo e(route('members.requests')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('members.requests*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Membership Requests</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('members.suspensions')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('suspend-members')): ?>
            <a href="<?php echo e(route('members.suspensions')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('members.suspensions*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Suspensions</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-digital-assets')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Digital Library</p>
            </div>
            <a href="<?php echo e(route('digital-library.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('digital-library.index') || request()->routeIs('digital-library.show') || request()->routeIs('digital-library.read') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Assets</span>
            </a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('upload-digital-assets')): ?>
            <a href="<?php echo e(route('digital-library.upload')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('digital-library.upload') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Upload Asset</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-recommendations')): ?>
            <a href="<?php echo e(route('digital-library.recommendations')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('digital-library.recommendations') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Recommendations</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-assignments')): ?>
            <a href="<?php echo e(route('assignments.teacher')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('assignments.teacher') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Reading Assignments</span>
            </a>
            <?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('digital-library.downloads')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('download-digital-assets')): ?>
            <a href="<?php echo e(route('digital-library.downloads')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('digital-library.downloads*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Downloads</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('digital-library.categories')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-digital-categories')): ?>
            <a href="<?php echo e(route('digital-library.categories')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('digital-library.categories*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Digital Categories</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-financial-reports')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Finance</p>
            </div>
            <a href="<?php echo e(route('finance.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.index') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Overview</span>
            </a>
            <div class="space-y-1">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-transactions')): ?>
            <a href="<?php echo e(route('finance.transactions')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.transactions*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Transactions</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-fines')): ?>
            <a href="<?php echo e(route('finance.fines')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.fines*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Fines</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('collect-payments')): ?>
            <a href="<?php echo e(route('finance.collect-payments')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.collect-payments*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Collect Payments</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('generate-invoices')): ?>
            <a href="<?php echo e(route('finance.invoices')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.invoices*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Invoices</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('generate-receipts')): ?>
            <a href="<?php echo e(route('finance.receipts')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.receipts*') || request()->routeIs('finance.receipt*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Receipts</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('process-refunds')): ?>
            <a href="<?php echo e(route('finance.refunds')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.refunds*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Refunds</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-analytics')): ?>
            <a href="<?php echo e(route('finance.analytics')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.analytics*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Analytics</span>
            </a>
            <?php endif; ?>
            </div>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['view-subscriptions', 'manage-subscriptions'])): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Subscriptions</p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('admin.subscriptions.index')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-subscriptions')): ?>
            <a href="<?php echo e(route('admin.subscriptions.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('admin.subscriptions.index') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>All Subscriptions</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('admin.subscriptions.plans')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-pricing')): ?>
            <a href="<?php echo e(route('admin.subscriptions.plans')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('admin.subscriptions.plans*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Plans</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-reports')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Reports</p>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('reports.dashboard')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-reports')): ?>
            <a href="<?php echo e(route('reports.dashboard')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('reports.dashboard') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('reports.catalog')): ?>
            <a href="<?php echo e(route('reports.catalog')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('reports.catalog*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Catalog Reports</span>
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('reports.circulation')): ?>
            <a href="<?php echo e(route('reports.circulation')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('reports.circulation*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Circulation Reports</span>
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('reports.members')): ?>
            <a href="<?php echo e(route('reports.members')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('reports.members*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Member Reports</span>
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('reports.digital-library')): ?>
            <a href="<?php echo e(route('reports.digital-library')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('reports.digital-library*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Digital Library Reports</span>
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('generate-reports')): ?>
            <a href="<?php echo e(route('finance.reports')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.reports*') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Finance Reports</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('schedule-reports')): ?>
            <a href="<?php echo e(route('finance.reports', ['tab' => 'schedule'])); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('finance.reports') && request()->tab === 'schedule' ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Scheduled Reports</span>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            
            <div class="pt-3">
                <p class="sidebar-group-label">Communication &amp; Engagement</p>
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-announcements')): ?>
            <a href="<?php echo e(route('communication.announcements.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('communication.announcements*') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span>Announcements</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-bulletins')): ?>
            <a href="<?php echo e(route('communication.bulletins.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('communication.bulletins*') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <span>Bulletins</span>
            </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-events')): ?>
            <a href="<?php echo e(route('communication.events.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('communication.events*') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Events</span>
            </a>
            <?php endif; ?>
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-messages')): ?>
            <div class="ml-4 mt-1 space-y-0.5">
                <p class="text-xs font-semibold text-primary-300 uppercase tracking-wider px-3 py-1">Messaging</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('communication.messages.index')): ?>
                <a href="<?php echo e(route('communication.messages.index')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('communication.messages.index') || request()->routeIs('communication.messages.show') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Inbox</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send-messages')): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('communication.messages.create')): ?>
                <a href="<?php echo e(route('communication.messages.create')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('communication.messages.create') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Compose Message</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?>
                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-broadcasts')): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('communication.broadcasts')): ?>
                <a href="<?php echo e(route('communication.broadcasts')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('communication.broadcasts*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Broadcast Messages</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-templates')): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('communication.templates.index')): ?>
                <a href="<?php echo e(route('communication.templates.index')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('communication.templates*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Message Templates</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?>
                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-message-logs')): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('communication.messages.logs')): ?>
                <a href="<?php echo e(route('communication.messages.logs')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('communication.messages.logs*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Message Logs</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['send-notifications', 'view-notification-logs'])): ?>
            <div class="ml-4 mt-2 space-y-0.5">
                <p class="text-xs font-semibold text-primary-300 uppercase tracking-wider px-3 py-1">Notifications</p>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send-notifications')): ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('notifications.send')): ?>
                <a href="<?php echo e(route('notifications.send')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('notifications.send') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Send Notifications</span>
                </a>
                <?php elseif(\Illuminate\Support\Facades\Route::has('settings.notifications')): ?>
                <a href="<?php echo e(route('settings.notifications')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.notifications') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Send Notifications</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-notification-logs')): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('notifications.index')): ?>
                <a href="<?php echo e(route('notifications.index')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('notifications.*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Notification Logs</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['view-programs', 'manage-programs'])): ?>
            <a href="<?php echo e(route('settings.programs')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('settings.programs*') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Programs</span>
            </a>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-settings')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Administration</p>
            </div>

            
            <div class="ml-4 mt-1 space-y-0.5">
                <p class="text-xs font-semibold text-primary-300 uppercase tracking-wider px-3 py-1">Settings</p>
                <a href="<?php echo e(route('settings.general')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.general') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">General</span>
                </a>
                <a href="<?php echo e(route('settings.circulation')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.circulation') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Circulation</span>
                </a>
                <a href="<?php echo e(route('settings.digital-library')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.digital-library') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Digital Library</span>
                </a>
                <a href="<?php echo e(route('settings.email')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.email') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Email</span>
                </a>
                <a href="<?php echo e(route('settings.localization')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.localization') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Localization</span>
                </a>
                <a href="<?php echo e(route('settings.appearance')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.appearance') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Appearance</span>
                </a>
                <a href="<?php echo e(route('settings.ai-settings')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.ai-settings') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">AI Settings</span>
                </a>
                <a href="<?php echo e(route('settings.subscriptions')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.subscriptions') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Subscription Pricing</span>
                </a>
                <a href="<?php echo e(route('settings.auth-carousel')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.auth-carousel') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Auth Carousel</span>
                </a>
                <a href="<?php echo e(route('settings.landing-page')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.landing-page') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Landing Page</span>
                </a>
                <a href="<?php echo e(route('settings.features')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.features*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Features</span>
                </a>
                <a href="<?php echo e(route('settings.why-choose-us')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.why-choose-us*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Why Choose Us</span>
                </a>
                <a href="<?php echo e(route('settings.testimonials')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.testimonials*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Testimonials</span>
                </a>
                <a href="<?php echo e(route('settings.newsletter-subscribers')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.newsletter-subscribers') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Newsletter Subscribers</span>
                </a>
                <a href="<?php echo e(route('settings.maintenance')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.maintenance') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Maintenance</span>
                </a>
            </div>

            
            <div class="ml-4 mt-2 space-y-0.5">
                <p class="text-xs font-semibold text-primary-300 uppercase tracking-wider px-3 py-1">User Management</p>
                <a href="<?php echo e(route('settings.users')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.users*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Users</span>
                </a>
                <a href="<?php echo e(route('settings.roles')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.roles*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Roles</span>
                </a>
                <a href="<?php echo e(route('settings.access-levels')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.access-levels*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Access Levels</span>
                </a>
                <a href="<?php echo e(route('settings.departments')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.departments*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Departments</span>
                </a>
                
                <a href="<?php echo e(route('settings.programs')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.programs*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Programs</span>
                </a>
            </div>

            
            <div class="ml-4 mt-2 space-y-0.5">
                <p class="text-xs font-semibold text-primary-300 uppercase tracking-wider px-3 py-1">Security</p>
                <a href="<?php echo e(route('settings.security.dashboard')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.security.dashboard') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Security Dashboard</span>
                </a>
                <a href="<?php echo e(route('settings.audit-logs')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.audit-logs') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Audit Logs</span>
                </a>
                <a href="<?php echo e(route('settings.system-logs')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.system-logs') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">System Logs</span>
                </a>
            </div>

            
            <div class="ml-4 mt-2 space-y-0.5">
                <p class="text-xs font-semibold text-primary-300 uppercase tracking-wider px-3 py-1">System</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('settings.system-health')): ?>
                <a href="<?php echo e(route('settings.system-health')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.system-health') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">System Health</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('settings.backup')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.backup') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Backup</span>
                </a>
                <a href="<?php echo e(route('settings.notifications')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.notifications') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Notification Settings</span>
                </a>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('settings.queue-monitor')): ?>
                <a href="<?php echo e(route('settings.queue-monitor')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.queue-monitor*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Queue Monitor</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('settings.cache')): ?>
                <a href="<?php echo e(route('settings.cache')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.cache*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Cache Management</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('settings.storage')): ?>
                <a href="<?php echo e(route('settings.storage')); ?>" wire:navigate
                    class="sidebar-link-sub <?php echo e(request()->routeIs('settings.storage*') ? 'sidebar-link-sub-active' : ''); ?>">
                    <span class="text-sm">Storage Manager</span>
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-audit-logs')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->denies('manage-settings')): ?>
            <a href="<?php echo e(route('settings.audit-logs')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('settings.audit-logs') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Audit Logs</span>
            </a>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'student|lecturer')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard')): ?>
            <div>
                <p class="sidebar-group-label">Overview</p>
            </div>
            <a href="<?php echo e(route('dashboard')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('dashboard') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-books')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Catalog</p>
            </div>
            <a href="<?php echo e(route('catalog.books.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('catalog.books*') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Browse Books</span>
            </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-digital-assets')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Digital Library</p>
            </div>
            <a href="<?php echo e(route('digital-library.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('digital-library.*') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Digital Library</span>
            </a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-recommendations')): ?>
            <a href="<?php echo e(route('digital-library.recommendations')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('digital-library.recommendations') ? 'sidebar-link-active' : ''); ?> ml-4"
               >
                <span class="text-sm">Recommendations</span>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-borrows')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">My Borrowings</p>
            </div>
            <a href="<?php echo e(route('circulation.index')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('circulation.index') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Personal Borrowings</span>
            </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-assignments')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">My Assignments</p>
            </div>
            <a href="<?php echo e(route('assignments.my')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('assignments.my') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Assignments</span>
            </a>
            <?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('subscriptions.my')): ?>
            <div class="pt-3">
                <p class="sidebar-group-label">Subscription</p>
            </div>
            <a href="<?php echo e(route('subscriptions.my')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('subscriptions.my') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>My Subscription</span>
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="pt-3">
                <p class="sidebar-group-label">My Account</p>
            </div>
            <a href="<?php echo e(route('profile')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('profile') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>My Profile</span>
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Route::has('settings.notifications')): ?>
            <a href="<?php echo e(route('settings.notifications')); ?>" wire:navigate
                class="sidebar-link <?php echo e(request()->routeIs('settings.notifications') ? 'sidebar-link-active' : ''); ?>"
               >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>Notifications</span>
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </nav>

    <div class="p-3 border-t border-primary-700/50">
        <button wire:click="logout"
            class="sidebar-link w-full text-primary-200 hover:text-white hover:bg-primary-700/50"
            aria-label="Logout">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Logout</span>
        </button>
    </div>
</aside>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\resources\views\livewire/layout/sidebar.blade.php ENDPATH**/ ?>