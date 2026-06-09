
<?php
    use App\Models\User;
    use App\Modules\Assignments\Models\ReadingAssignment;
    use App\Modules\Catalog\Models\Book;
    use App\Modules\Circulation\Models\BorrowRecord;
    use App\Modules\Circulation\Models\Fine;
    use App\Modules\Circulation\Models\Reservation;
    use App\Modules\DigitalLibrary\Models\ReadingHistory;
    use App\Modules\DigitalLibrary\Services\DigitalLibraryService;

    $user = auth()->user();
    $isStaff = $user->hasAnyRole(['super-admin', 'admin', 'librarian', 'assistant-librarian', 'finance-officer']);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isStaff): ?>
    
    <?php
        $totalBooks = Book::count();
        $activeBorrows = BorrowRecord::whereNull('returned_at')->count();
        $overdueBooks = BorrowRecord::whereNull('returned_at')->where('due_at', '<', now())->count();
        $recentBorrows = BorrowRecord::with('user', 'copy.book')->latest()->take(5)->get();
        $monthlyBorrows = BorrowRecord::selectRaw("strftime('%m', created_at) as month, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')->orderBy('month')->pluck('total', 'month');
        $categoryCounts = Book::selectRaw('category_id, count(*) as total')
            ->whereNotNull('category_id')
            ->groupBy('category_id')->orderByDesc('total')->take(5)
            ->with('category')->get()
            ->mapWithKeys(fn($item) => [$item->category?->name ?? 'Uncategorized' => $item->total]);

        $canCreateAssignments = $user->can('create-assignments');
        if ($canCreateAssignments) {
            $myAssignments = ReadingAssignment::forTeacher($user->id);
            $totalAssignments = (clone $myAssignments)->count();
            $viewedAssignments = (clone $myAssignments)->whereNotNull('viewed_at')->count();
            $completedAssignments = (clone $myAssignments)->where('status', ReadingAssignment::STATUS_COMPLETED)->count();
        }
    ?>

    <?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('header', null, []); ?> Dashboard <?php $__env->endSlot(); ?>
         <?php $__env->slot('subtitle', null, []); ?> Welcome back, <?php echo e($user->name); ?>. Here's your library overview. <?php $__env->endSlot(); ?>

        <div class="stat-carousel mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Total Books</p>
                    <div class="stat-icon bg-gradient-to-br from-primary-500 to-primary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e(number_format($totalBooks)); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Across all categories</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Active Borrows</p>
                    <div class="stat-icon bg-gradient-to-br from-secondary-500 to-secondary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e(number_format($activeBorrows)); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Currently checked out</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Overdue Books</p>
                    <div class="stat-icon bg-gradient-to-br from-amber-500 to-amber-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e(number_format($overdueBooks)); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Requires attention</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Registered Members</p>
                    <div class="stat-icon bg-gradient-to-br from-accent-500 to-accent-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e(number_format(User::count())); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Active library members</p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canCreateAssignments): ?>
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Assignments</p>
                    <div class="stat-icon bg-gradient-to-br from-primary-500 to-accent-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e($totalAssignments); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                    <?php echo e($viewedAssignments); ?> viewed &middot; <?php echo e($completedAssignments); ?> completed
                </p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button @click="open = !open" class="collapsible-trigger md:hidden p-1 -ml-1" :class="{ 'open': open }">
                                <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <h3 class="font-semibold text-surface-900 dark:text-white">Recent Activity</h3>
                        </div>
                        <a href="<?php echo e(route('circulation.index')); ?>" wire:navigate class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                    </div>
                    <div class="card-body" x-show="open">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentBorrows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                <span class="text-xs font-bold text-primary-600 dark:text-primary-400"><?php echo e(substr($borrow->user->name ?? '?', 0, 1)); ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white truncate">
                                    <?php echo e($borrow->user->name ?? 'Unknown'); ?> borrowed
                                    <span class="text-primary-600 dark:text-primary-400"><?php echo e($borrow->copy->book->title ?? 'Unknown Book'); ?></span>
                                </p>
                                <p class="text-xs text-surface-500"><?php echo e($borrow->created_at->diffForHumans()); ?></p>
                            </div>
                            <span class="badge <?php if($borrow->due_at && $borrow->due_at->isPast()): ?> badge-danger <?php else: ?> badge-info <?php endif; ?> text-xs">
                                <?php echo e($borrow->due_at ? $borrow->due_at->diffForHumans() : 'No due date'); ?>

                            </span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-surface-500 dark:text-surface-400 text-center py-8">
                            No recent activity to display.
                        </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="quick-actions-desktop">
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Quick Actions</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body space-y-3" x-show="open">
                        <a href="<?php echo e(route('catalog.books.create')); ?>" wire:navigate class="btn-primary w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Book
                        </a>
                        <a href="<?php echo e(route('circulation.issue')); ?>" wire:navigate class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Issue Book
                        </a>
                        <a href="<?php echo e(route('circulation.return')); ?>" wire:navigate class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Return Book
                        </a>
                        <a href="<?php echo e(route('finance.reports')); ?>" wire:navigate class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generate Report
                        </a>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overdueBooks > 0): ?>
                <div class="card mt-4 border-accent-200 dark:border-accent-800" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-accent-600 dark:text-accent-400">Overdue Alerts</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        <p class="text-sm text-surface-500 dark:text-surface-400">
                            <?php echo e($overdueBooks); ?> book<?php echo e($overdueBooks !== 1 ? 's' : ''); ?> currently overdue.
                            <a href="<?php echo e(route('circulation.index')); ?>" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline">View details</a>
                        </p>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card" x-data="{ open: true }">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Borrowing Trends (6 Months)</h3>
                    <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                        <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
                <div class="card-body h-48 md:h-64" x-show="open">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($monthlyBorrows->isNotEmpty()): ?>
                    <div class="flex items-end gap-2 h-32 md:h-48">
                        <?php $max = max($monthlyBorrows->toArray()) ?: 1; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $monthlyBorrows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex-1 flex flex-col items-center gap-1 h-full">
                            <span class="text-xs font-medium text-surface-600 dark:text-surface-400"><?php echo e($count); ?></span>
                            <div class="w-full bg-primary-200 dark:bg-primary-900/30 rounded-t-lg flex-1 min-h-0 w-full" style="max-height: <?php echo e(($count / $max) * 100); ?>%">
                                <div class="w-full h-full bg-primary-500 dark:bg-primary-400 rounded-t-lg opacity-80 hover:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-xs text-surface-500"><?php echo e(DateTime::createFromFormat('!m', $month)->format('M')); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="h-full flex items-center justify-center">
                        <p class="text-sm text-surface-400 dark:text-surface-500">No borrowing data yet</p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="card" x-data="{ open: true }">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Top Categories</h3>
                    <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                        <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
                <div class="card-body h-64" x-show="open">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categoryCounts->isNotEmpty()): ?>
                    <div class="space-y-3">
                        <?php $catMax = max($categoryCounts->toArray()) ?: 1; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categoryCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-surface-700 dark:text-surface-300"><?php echo e($category); ?></span>
                                <span class="font-medium text-surface-900 dark:text-white"><?php echo e($count); ?></span>
                            </div>
                            <div class="w-full bg-surface-200 dark:bg-surface-700 rounded-full h-2.5">
                                <div class="bg-primary-500 dark:bg-primary-400 h-2.5 rounded-full transition-all" style="width: <?php echo e(($count / $catMax) * 100); ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="h-full flex items-center justify-center">
                        <p class="text-sm text-surface-400 dark:text-surface-500">No categories yet</p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div x-data="{ fabOpen: false }" class="md:hidden">
            <div x-show="fabOpen" x-cloak
                 @click="fabOpen = false"
                 class="fab-menu-backdrop" :class="{ 'show': fabOpen }"></div>
            <div class="fab-menu" :class="{ 'show': fabOpen }">
                <div class="fab-menu-item" style="transition-delay: 0.05s">
                    <span class="fab-label">Add New Book</span>
                    <a href="<?php echo e(route('catalog.books.create')); ?>" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #1E4FA3, #153168);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </a>
                </div>
                <div class="fab-menu-item" style="transition-delay: 0.1s">
                    <span class="fab-label">Issue Book</span>
                    <a href="<?php echo e(route('circulation.issue')); ?>" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #059669, #047857);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </a>
                </div>
                <div class="fab-menu-item" style="transition-delay: 0.15s">
                    <span class="fab-label">Return Book</span>
                    <a href="<?php echo e(route('circulation.return')); ?>" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #D62839, #b91c1c);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </a>
                </div>
            </div>
            <button @click="fabOpen = !fabOpen"
                    class="fab" :class="{ 'active': fabOpen }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php else: ?>
    
    <?php
        $activeBorrows = BorrowRecord::with('copy.book')
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->latest('borrowed_at')
            ->get();

        $overdueBorrows = $activeBorrows->filter(fn($b) => $b->isOverdue());
        $readingHistory = BorrowRecord::with('copy.book')
            ->where('user_id', $user->id)
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->take(5)
            ->get();

        $pendingFines = Fine::with('borrowRecord.copy.book')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();
        $totalFines = $pendingFines->sum(fn($f) => $f->outstanding_balance);

        $activeReservations = Reservation::with('book')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('reserved_at')
            ->get();

        $digitalProgress = ReadingHistory::with('asset')
            ->where('user_id', $user->id)
            ->where('progress', '<', 100)
            ->whereNotNull('last_read_at')
            ->latest('last_read_at')
            ->take(5)
            ->get();
    ?>

    <?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('header', null, []); ?> My Dashboard <?php $__env->endSlot(); ?>
         <?php $__env->slot('subtitle', null, []); ?> Welcome back, <?php echo e($user->name); ?>. Here's your library activity. <?php $__env->endSlot(); ?>

        
        <div class="stat-carousel mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Currently Borrowed</p>
                    <div class="stat-icon bg-gradient-to-br from-secondary-500 to-secondary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e($activeBorrows->count()); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overdueBorrows->count() > 0): ?>
                        <span class="text-red-500"><?php echo e($overdueBorrows->count()); ?> overdue</span>
                    <?php else: ?>
                        All returned on time
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Reservations</p>
                    <div class="stat-icon bg-gradient-to-br from-accent-500 to-accent-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e($activeReservations->count()); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">Active holds</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Pending Fines</p>
                    <div class="stat-icon bg-gradient-to-br from-amber-500 to-amber-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white">$<?php echo e(number_format($totalFines, 2)); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1"><?php echo e($pendingFines->count()); ?> unpaid</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-surface-500 dark:text-surface-400">Digital Reading</p>
                    <div class="stat-icon bg-gradient-to-br from-primary-500 to-primary-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-surface-900 dark:text-white"><?php echo e($digitalProgress->count()); ?></p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">In progress</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <div class="lg:col-span-2">
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button @click="open = !open" class="collapsible-trigger md:hidden p-1 -ml-1" :class="{ 'open': open }">
                                <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <h3 class="font-semibold text-surface-900 dark:text-white">Currently Borrowed</h3>
                        </div>
                        <a href="<?php echo e(route('circulation.index')); ?>" wire:navigate class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Circulation</a>
                    </div>
                    <div class="card-body" x-show="open">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $activeBorrows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="w-10 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white truncate">
                                    <?php echo e($borrow->copy?->book?->title ?? 'Unknown'); ?>

                                </p>
                                <p class="text-xs text-surface-500">
                                    Borrowed <?php echo e($borrow->borrowed_at->diffForHumans()); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($borrow->due_at): ?> &middot; Due <?php echo e($borrow->due_at->format('M d, Y')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            </div>
                            <span class="badge <?php echo e($borrow->isOverdue() ? 'badge-danger' : 'badge-info'); ?> text-xs">
                                <?php echo e($borrow->isOverdue() ? $borrow->daysOverdue() . ' days overdue' : ($borrow->due_at ? $borrow->due_at->diffForHumans(null, true) . ' left' : 'No due date')); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$borrow->isOverdue() && $borrow->renewal_count < $borrow->max_renewals): ?>
                                <button wire:click="<?php echo e($borrow->id); ?>" class="text-xs text-primary-600 dark:text-primary-400 hover:underline shrink-0"
                                        @click="fetch('<?php echo e(route('circulation.renew', $borrow->id)); ?>', {method:'POST', headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}}).then(() => { location.reload() }).catch(e => {})">
                                    Renew
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-surface-500 dark:text-surface-400 text-center py-8">
                            You haven't borrowed any books yet.
                            <a href="<?php echo e(route('catalog.books.index')); ?>" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline block mt-1">Browse the catalog</a>
                        </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeReservations->isNotEmpty()): ?>
                <div class="card mt-6" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">My Reservations</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="w-10 h-12 rounded-lg bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white"><?php echo e($reservation->book->title ?? 'Unknown'); ?></p>
                                <p class="text-xs text-surface-500">Reserved <?php echo e($reservation->reserved_at->diffForHumans()); ?></p>
                            </div>
                            <span class="badge badge-warning text-xs">Pending</span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($readingHistory->isNotEmpty()): ?>
                <div class="card mt-6" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Reading History</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $readingHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white"><?php echo e($borrow->copy?->book?->title ?? 'Unknown'); ?></p>
                                <p class="text-xs text-surface-500">Returned <?php echo e($borrow->returned_at->diffForHumans()); ?></p>
                            </div>
                            <span class="badge badge-success text-xs">Returned</span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($digitalProgress->isNotEmpty()): ?>
                <div class="card" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Continue Reading</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body space-y-3" x-show="open">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $digitalProgress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('digital-library.read', $history->asset)); ?>" wire:navigate
                           class="flex items-center gap-3 p-2 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white truncate"><?php echo e($history->asset->title ?? 'Unknown'); ?></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex-1 bg-surface-200 dark:bg-surface-700 rounded-full h-1.5">
                                        <div class="bg-primary-500 rounded-full h-1.5" style="width: <?php echo e($history->progress); ?>%"></div>
                                    </div>
                                    <span class="text-xs text-surface-500"><?php echo e($history->progress); ?>%</span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingFines->isNotEmpty()): ?>
                <div class="card mt-4 border-accent-200 dark:border-accent-800" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-accent-600 dark:text-accent-400">Pending Fines</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body" x-show="open">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pendingFines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between py-2 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="min-w-0">
                                <p class="text-sm text-surface-900 dark:text-white truncate"><?php echo e($fine->reason); ?></p>
                                <p class="text-xs text-surface-500">Assessed <?php echo e($fine->assessed_at->diffForHumans()); ?></p>
                            </div>
                            <span class="text-sm font-semibold text-red-500">$<?php echo e(number_format($fine->outstanding_balance, 2)); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex items-center justify-between pt-3 mt-2 border-t border-surface-100 dark:border-surface-700">
                            <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Total Outstanding</span>
                            <span class="text-lg font-bold text-red-500">$<?php echo e(number_format($totalFines, 2)); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="card mt-4" x-data="{ open: true }">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Quick Links</h3>
                        <button @click="open = !open" class="collapsible-trigger md:hidden p-1" :class="{ 'open': open }">
                            <svg class="collapse-icon w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="card-body space-y-2" x-show="open">
                        <a href="<?php echo e(route('catalog.books.index')); ?>" wire:navigate class="btn-outline w-full justify-center text-sm">
                            Browse Catalog
                        </a>
                        <a href="<?php echo e(route('digital-library.index')); ?>" wire:navigate class="btn-outline w-full justify-center text-sm">
                            Digital Library
                        </a>
                        <a href="<?php echo e(route('profile')); ?>" wire:navigate class="btn-outline w-full justify-center text-sm">
                            My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-data="{ fabOpen: false }" class="md:hidden">
            <div x-show="fabOpen" x-cloak
                 @click="fabOpen = false"
                 class="fab-menu-backdrop" :class="{ 'show': fabOpen }"></div>
            <div class="fab-menu" :class="{ 'show': fabOpen }">
                <div class="fab-menu-item" style="transition-delay: 0.05s">
                    <span class="fab-label">Browse Catalog</span>
                    <a href="<?php echo e(route('catalog.books.index')); ?>" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #1E4FA3, #153168);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </a>
                </div>
                <div class="fab-menu-item" style="transition-delay: 0.1s">
                    <span class="fab-label">Digital Library</span>
                    <a href="<?php echo e(route('digital-library.index')); ?>" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #059669, #047857);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </a>
                </div>
                <div class="fab-menu-item" style="transition-delay: 0.15s">
                    <span class="fab-label">My Profile</span>
                    <a href="<?php echo e(route('profile')); ?>" wire:navigate class="fab-btn" style="background: linear-gradient(135deg, #D62839, #b91c1c);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                </div>
            </div>
            <button @click="fabOpen = !fabOpen"
                    class="fab" :class="{ 'active': fabOpen }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\resources\views/dashboard.blade.php ENDPATH**/ ?>