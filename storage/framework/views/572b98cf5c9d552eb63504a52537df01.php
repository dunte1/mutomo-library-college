<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show): ?>
        <div class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh]"
            x-data x-on:keydown.escape.window="$wire.close()"
            x-init="$el.querySelector('input')?.focus()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="close"></div>
            <div class="relative w-full max-w-2xl mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text"
                        wire:model.live="query"
                        placeholder="Search books, members, digital assets..."
                        class="flex-1 bg-transparent border-0 outline-none text-gray-900 dark:text-white placeholder-gray-400 text-lg">
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($results['books'])): ?>
                            <div class="px-4 pt-3 pb-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Books</p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results['books']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($item['url']); ?>" wire:navigate wire:click="close"
                                    class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <svg class="w-5 h-5 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo e($item['title']); ?></p>
                                        <p class="text-xs text-gray-500 truncate"><?php echo e($item['subtitle']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($results['members'])): ?>
                            <div class="px-4 pt-3 pb-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Members</p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results['members']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($item['url']); ?>" wire:navigate wire:click="close"
                                    class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo e($item['title']); ?></p>
                                        <p class="text-xs text-gray-500 truncate"><?php echo e($item['subtitle']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($results['assets'])): ?>
                            <div class="px-4 pt-3 pb-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Digital Assets</p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results['assets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($item['url']); ?>" wire:navigate wire:click="close"
                                    class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo e($item['title']); ?></p>
                                        <p class="text-xs text-gray-500 truncate"><?php echo e($item['subtitle']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($results['users'])): ?>
                            <div class="px-4 pt-3 pb-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Users</p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results['users']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($item['url']); ?>" wire:navigate wire:click="close"
                                    class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo e($item['title']); ?></p>
                                        <p class="text-xs text-gray-500 truncate"><?php echo e($item['subtitle']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php $totalResults = collect($results)->flatten(1)->count(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$totalResults && strlen($this->query) >= 2): ?>
                            <div class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No results found for "<?php echo e($this->query); ?>"</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php elseif(strlen($this->query) < 2): ?>
                        <div class="px-5 py-12 text-center">
                            <p class="text-sm text-gray-400">Type at least 2 characters to search</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-4 text-xs text-gray-400">
                        <span><kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs">↑↓</kbd> Navigate</span>
                        <span><kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs">Esc</kbd> Close</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\app\Modules\Settings\Providers/../Views/livewire/global-search.blade.php ENDPATH**/ ?>