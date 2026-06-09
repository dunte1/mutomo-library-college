<?php $__env->startSection('title', 'My Assignments'); ?>
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">My Assignments &amp; Recommendations</h1>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('message')): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 sm:p-4 text-sm sm:text-base text-green-700 dark:text-green-300">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex gap-2 flex-wrap">
        <button wire:click="$set('tab', 'all')"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm rounded-lg transition-colors duration-150
            <?php echo e($tab === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400'); ?>">
            All
        </button>
        <button wire:click="$set('tab', 'pending')"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm rounded-lg transition-colors duration-150
            <?php echo e($tab === 'pending' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400'); ?>">
            Pending
        </button>
        <button wire:click="$set('tab', 'completed')"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm rounded-lg transition-colors duration-150
            <?php echo e($tab === 'completed' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400'); ?>">
            Completed
        </button>
        <button wire:click="$set('tab', 'overdue')"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm rounded-lg transition-colors duration-150
            <?php echo e($tab === 'overdue' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400'); ?>">
            Overdue
        </button>
    </div>

    
    <div class="flex gap-2 flex-wrap">
        <select wire:model.live="typeFilter" class="input w-full sm:w-auto text-sm">
            <option value="">All Types</option>
            <option value="assignment">Assignments</option>
            <option value="recommendation">Recommendations</option>
        </select>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card p-3 sm:p-4 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white truncate"><?php echo e($assignment->title); ?></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            Assigned by <span class="font-medium"><?php echo e($assignment->teacher->name); ?></span>
                            &middot; <?php echo e($assignment->created_at->diffForHumans()); ?>

                        </p>
                    </div>
                    <span class="badge shrink-0 whitespace-nowrap
                        <?php if($assignment->type === 'assignment'): ?> badge-info
                        <?php else: ?> badge-warning
                        <?php endif; ?>">
                        <?php echo e(ucfirst($assignment->type)); ?>

                    </span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->description): ?>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 line-clamp-2"><?php echo e($assignment->description); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="flex flex-wrap gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->book): ?>
                        <a href="<?php echo e(route('catalog.books.show', $assignment->book)); ?>" wire:navigate
                           class="text-xs inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 hover:underline truncate max-w-full">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span class="truncate"><?php echo e($assignment->book->title); ?></span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->digitalAsset): ?>
                        <a href="<?php echo e(route('digital-library.show', $assignment->digitalAsset)); ?>" wire:navigate
                           class="text-xs inline-flex items-center gap-1 px-2 py-1 rounded bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 hover:underline truncate max-w-full">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="truncate"><?php echo e($assignment->digitalAsset->title); ?></span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-1">
                    <div class="flex items-center flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="badge whitespace-nowrap
                            <?php if($assignment->status === 'pending'): ?> badge-warning
                            <?php elseif($assignment->status === 'in_progress'): ?> badge-info
                            <?php elseif($assignment->status === 'completed'): ?> badge-success
                            <?php elseif($assignment->status === 'overdue'): ?> badge-danger
                            <?php else: ?> badge-neutral
                            <?php endif; ?>">
                            <?php echo e(str_replace('_', ' ', ucfirst($assignment->status))); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->due_date): ?>
                            <span class="whitespace-nowrap">Due: <?php echo e($assignment->due_date->format('M d, Y h:i A')); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($assignment->status, ['pending', 'in_progress'])): ?>
                        <button wire:click="markComplete(<?php echo e($assignment->id); ?>)"
                                wire:confirm="Mark this as completed?"
                                class="btn-sm btn-primary w-full sm:w-auto text-center">
                            Mark Complete
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="sm:col-span-2 card p-8 sm:p-12 text-center">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">No assignments or recommendations yet.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignments->hasPages()): ?>
    <div class="mt-4">
        <?php echo e($assignments->links()); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\app\Modules\Assignments\Providers/../Views/livewire/student-assignments.blade.php ENDPATH**/ ?>