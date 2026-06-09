<?php $__env->startSection('title', 'Settings'); ?>
<div>
     <?php $__env->slot('header', null, []); ?> Settings <?php $__env->endSlot(); ?>
     <?php $__env->slot('subtitle', null, []); ?> Configure your library management system <?php $__env->endSlot(); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route($group['route'])); ?>" wire:navigate
               class="card p-6 hover:shadow-lg transition-shadow duration-200 group">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 p-3 rounded-xl
                        <?php switch($group['color']):
                            case ('primary'): ?> bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 <?php break; ?>
                            <?php case ('emerald'): ?> bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 <?php break; ?>
                            <?php case ('purple'): ?> bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 <?php break; ?>
                            <?php case ('amber'): ?> bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 <?php break; ?>
                            <?php case ('red'): ?> bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 <?php break; ?>
                            <?php case ('blue'): ?> bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 <?php break; ?>
                            <?php case ('cyan'): ?> bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 <?php break; ?>
                            <?php case ('indigo'): ?> bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 <?php break; ?>
                            <?php case ('pink'): ?> bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400 <?php break; ?>
                            <?php default: ?> bg-surface-50 dark:bg-surface-700 text-surface-600 dark:text-surface-400
                        <?php endswitch; ?>">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php echo $group['icon']; ?>

                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-surface-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                            <?php echo e($group['title']); ?>

                        </h3>
                        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1 leading-relaxed">
                            <?php echo e($group['description']); ?>

                        </p>
                    </div>
                    <svg class="w-5 h-5 text-surface-300 dark:text-surface-600 group-hover:text-primary-500 transition-colors shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\app\Modules\Settings\Providers/../Views/livewire/settings-dashboard.blade.php ENDPATH**/ ?>