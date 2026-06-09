<?php $__env->startSection('title', 'Catalog'); ?>
<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Books Catalog</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Browse, search, and manage library books</p>
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-books')): ?>
                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('catalog.books.bulk-upload')); ?>" wire:navigate class="btn-outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Bulk Upload
                    </a>
                    <a href="<?php echo e(route('catalog.books.create')); ?>" wire:navigate class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Book
                    </a>
                </div>
            <?php endif; ?>
            <button wire:click="exportCsv" class="btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by title, author, ISBN..."
                            class="input-field pl-9">
                    </div>
                </div>
                <div>
                    <select wire:model.live="categoryId" class="input-field">
                        <option value="">All Categories</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $category->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($child->id); ?>">&nbsp;&nbsp;<?php echo e($child->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <select wire:model.live="authorId" class="input-field">
                        <option value="">All Authors</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $authors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $author): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($author->id); ?>"><?php echo e($author->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mt-3">
                <div class="flex-1 min-w-[150px]">
                    <select wire:model.live="publisherId" class="input-field">
                        <option value="">All Publishers</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $publishers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $publisher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($publisher->id); ?>"><?php echo e($publisher->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <select wire:model.live="subjectId" class="input-field">
                        <option value="">All Subjects</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <select wire:model.live="sort" class="input-field">
                        <option value="title">Sort: Title</option>
                        <option value="publication_year">Sort: Year</option>
                        <option value="created_at">Sort: Newest</option>
                    </select>
                </div>
                <button wire:click="clearFilters" class="btn-ghost text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('catalog.books.show', $book->id)); ?>" wire:navigate class="card group hover:shadow-soft-lg transition-all duration-200 hover:-translate-y-0.5">
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-20 rounded-xl bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center shrink-0 overflow-hidden">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($book->cover_image): ?>
                                <img src="<?php echo e(Storage::url($book->cover_image)); ?>" alt="<?php echo e($book->title); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-8 h-8 text-primary-500 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-surface-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                                <?php echo e($book->title); ?>

                            </h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($book->subtitle): ?>
                                <p class="text-xs text-surface-500 dark:text-surface-400 truncate"><?php echo e($book->subtitle); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                                <?php echo e($book->authors->pluck('name')->implode(', ') ?: 'Unknown Author'); ?>

                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="badge-info"><?php echo e($book->category?->name ?? 'Uncategorized'); ?></span>
                                <span class="badge-neutral"><?php echo e($book->language); ?></span>
                            </div>
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-surface-100 dark:border-surface-700">
                                <span class="text-xs text-surface-500 dark:text-surface-400">
                                    <?php echo e($book->total_copies); ?> copy(ies)
                                </span>
                                <div class="flex items-center gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($book->digitalAssets()->exists()): ?>
                                        <span class="badge-success text-[10px]">eBook</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="text-xs font-medium <?php echo e($book->available_count > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'); ?>">
                                        <?php echo e($book->available_count); ?> available
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full">
                <div class="card p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">No Books Found</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Try adjusting your search or filter criteria.</p>
                    <button wire:click="clearFilters" class="btn-primary">Clear Filters</button>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-6">
        <?php echo e($books->links()); ?>

    </div>
</div>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\app\Modules\Catalog\Providers/../Views/livewire/book-list.blade.php ENDPATH**/ ?>