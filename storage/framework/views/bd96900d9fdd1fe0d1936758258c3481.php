<?php $__env->startSection('title', 'Manage Assignments'); ?>
<div class="space-y-4 sm:space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">Reading Assignments &amp; Recommendations</h1>
        <button wire:click="$set('showForm', true)" class="btn-primary flex items-center justify-center gap-2 w-full sm:w-auto">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Assignment
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('message')): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 sm:p-4 text-sm sm:text-base text-green-700 dark:text-green-300">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 sm:p-4 text-sm sm:text-base text-red-700 dark:text-red-300">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
    <div class="card p-4 sm:p-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <?php echo e($editing ? 'Edit Assignment' : 'New Reading Assignment'); ?>

        </h2>
        <form wire:submit="<?php echo e($editing ? 'update' : 'create'); ?>" class="space-y-4">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$editing): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign To</label>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 mb-3">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="assignTo" value="individual">
                        Individual Student
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="assignTo" value="program">
                        Entire Program
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="assignTo" value="department">
                        Entire Department
                    </label>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignTo === 'individual'): ?>
                    <select wire:model="student_id" class="input w-full">
                        <option value="">Select student</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($student->id); ?>"><?php echo e($student->name); ?> (<?php echo e($student->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php elseif($assignTo === 'program'): ?>
                    <select wire:model="program_id" class="input w-full">
                        <option value="">Select program</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($program->id); ?>"><?php echo e($program->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['program_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php elseif($assignTo === 'department'): ?>
                    <select wire:model="department_id" class="input w-full">
                        <option value="">Select department</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php else: ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student *</label>
                    <select wire:model="student_id" class="input w-full">
                        <option value="">Select student</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($student->id); ?>"><?php echo e($student->name); ?> (<?php echo e($student->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                    <select wire:model="type" class="input w-full">
                        <option value="assignment">Assignment</option>
                        <option value="recommendation">Recommendation</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                    <input wire:model="due_date" type="datetime-local" class="input w-full">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
                    <input wire:model="title" type="text" class="input w-full" placeholder="e.g. Read Chapter 5 - Data Structures">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="input w-full" placeholder="Additional details about this assignment..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Book (optional)</label>
                    <select wire:model="book_id" class="input w-full">
                        <option value="">No book selected</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($book->id); ?>"><?php echo e($book->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Digital Asset (optional)</label>
                    <select wire:model="digital_asset_id" class="input w-full">
                        <option value="">No digital asset selected</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $digitalAssets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($asset->id); ?>"><?php echo e($asset->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <input wire:model="notes" type="text" class="input w-full" placeholder="Private notes for yourself">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 pt-2">
                <button type="submit" class="btn-primary w-full sm:w-auto">
                    <?php echo e($editing ? 'Update' : ($assignTo === 'individual' ? 'Create' : 'Assign to Group')); ?>

                </button>
                <button type="button" wire:click="$set('showForm', false)" class="btn-secondary w-full sm:w-auto">
                    Cancel
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progressAssignment): ?>
    <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center" wire:click.self="closeProgress" wire:poll.5s>
        <div class="bg-white dark:bg-gray-900 rounded-t-2xl sm:rounded-xl shadow-2xl max-w-3xl w-full max-h-[85vh] sm:max-h-[80vh] overflow-y-auto mt-auto sm:mt-0">
            <div class="sticky top-0 bg-white dark:bg-gray-900 z-10 p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Progress: <?php echo e($progressAssignment->title); ?></h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">
                        Assigned <?php echo e($progressAssignment->created_at->diffForHumans()); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progressAssignment->program): ?> &middot; Program: <?php echo e($progressAssignment->program->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progressAssignment->department): ?> &middot; Department: <?php echo e($progressAssignment->department->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>
                <button wire:click="closeProgress" class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 -m-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4 sm:p-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($progressStats->isEmpty()): ?>
                    <p class="text-gray-500 text-center py-4">No progress data available.</p>
                <?php else: ?>
                    
                    <div class="sm:hidden space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $progressStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white text-sm truncate"><?php echo e($stat['student_name']); ?></span>
                                    <span class="badge shrink-0 ml-2
                                        <?php if($stat['status'] === 'pending'): ?> badge-warning
                                        <?php elseif($stat['status'] === 'in_progress'): ?> badge-info
                                        <?php elseif($stat['status'] === 'completed'): ?> badge-success
                                        <?php elseif($stat['status'] === 'overdue'): ?> badge-danger
                                        <?php else: ?> badge-neutral
                                        <?php endif; ?>">
                                        <?php echo e(str_replace('_', ' ', ucfirst($stat['status']))); ?>

                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <div>
                                        <span class="block text-gray-400 dark:text-gray-500">Viewed</span>
                                        <?php echo e($stat['viewed_at'] ? $stat['viewed_at']->format('M d, Y h:i A') : 'Not viewed'); ?>

                                    </div>
                                    <div>
                                        <span class="block text-gray-400 dark:text-gray-500">Completed</span>
                                        <?php echo e($stat['completed_at'] ? $stat['completed_at']->format('M d, Y h:i A') : '-'); ?>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Student</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Status</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Viewed</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Completed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $progressStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 text-gray-900 dark:text-white font-medium whitespace-nowrap"><?php echo e($stat['student_name']); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="badge whitespace-nowrap
                                                <?php if($stat['status'] === 'pending'): ?> badge-warning
                                                <?php elseif($stat['status'] === 'in_progress'): ?> badge-info
                                                <?php elseif($stat['status'] === 'completed'): ?> badge-success
                                                <?php elseif($stat['status'] === 'overdue'): ?> badge-danger
                                                <?php else: ?> badge-neutral
                                                <?php endif; ?>">
                                                <?php echo e(str_replace('_', ' ', ucfirst($stat['status']))); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            <?php echo e($stat['viewed_at'] ? $stat['viewed_at']->format('M d, Y h:i A') : 'Not viewed'); ?>

                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            <?php echo e($stat['completed_at'] ? $stat['completed_at']->format('M d, Y h:i A') : '-'); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                        $total = $progressStats->count();
                        $viewed = $progressStats->where('viewed_at', '!=', null)->count();
                        $completed = $progressStats->where('status', 'completed')->count();
                    ?>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-center text-sm">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                            <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($total); ?></div>
                            <div class="text-gray-500 dark:text-gray-400">Total Students</div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                            <div class="text-xl sm:text-2xl font-bold text-blue-700 dark:text-blue-300"><?php echo e($viewed); ?></div>
                            <div class="text-blue-600 dark:text-blue-400">Viewed</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                            <div class="text-xl sm:text-2xl font-bold text-green-700 dark:text-green-300"><?php echo e($completed); ?></div>
                            <div class="text-green-600 dark:text-green-400">Completed</div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
        <select wire:model.live="typeFilter" class="input w-full sm:w-auto text-sm">
            <option value="">All Types</option>
            <option value="assignment">Assignments</option>
            <option value="recommendation">Recommendations</option>
        </select>
        <select wire:model.live="statusFilter" class="input w-full sm:w-auto text-sm">
            <option value="">All Statuses</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Modules\Assignments\Models\ReadingAssignment::statusOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <input wire:model.live.debounce.300ms="search" type="text" class="input w-full text-sm" placeholder="Search by title or student...">
    </div>

    
    <div class="card">
        
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm min-w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Title &amp; Student</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Type</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Due Date</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white truncate"><?php echo e($assignment->title); ?></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            <?php echo e($assignment->student->name); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->program): ?> &middot; <?php echo e($assignment->program->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->department): ?> &middot; <?php echo e($assignment->department->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->book || $assignment->digitalAsset): ?>
                                            <div class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->book): ?> Book: <?php echo e($assignment->book->title); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->book && $assignment->digitalAsset): ?> &middot; <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->digitalAsset): ?> Digital: <?php echo e($assignment->digitalAsset->title); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="shrink-0 text-xs text-gray-400 dark:text-gray-500 pt-0.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->viewed_at): ?>
                                            <span title="Viewed <?php echo e($assignment->viewed_at->format('M d, Y h:i A')); ?>">&#10003;</span>
                                        <?php else: ?>
                                            <span title="Not viewed yet">&ndash;</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="badge whitespace-nowrap <?php echo e($assignment->type === 'assignment' ? 'badge-info' : 'badge-warning'); ?>">
                                    <?php echo e(ucfirst($assignment->type)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="badge whitespace-nowrap
                                    <?php if($assignment->status === 'pending'): ?> badge-warning
                                    <?php elseif($assignment->status === 'in_progress'): ?> badge-info
                                    <?php elseif($assignment->status === 'completed'): ?> badge-success
                                    <?php elseif($assignment->status === 'overdue'): ?> badge-danger
                                    <?php else: ?> badge-neutral
                                    <?php endif; ?>">
                                    <?php echo e(str_replace('_', ' ', ucfirst($assignment->status))); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap text-xs">
                                <?php echo e($assignment->due_date ? $assignment->due_date->format('M d, Y') : '-'); ?>

                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <button wire:click="showProgress(<?php echo e($assignment->id); ?>)" title="View progress" class="p-1.5 rounded-lg text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 dark:text-primary-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="edit(<?php echo e($assignment->id); ?>)" title="Edit" class="p-1.5 rounded-lg text-secondary-600 hover:bg-secondary-50 dark:hover:bg-secondary-900/20 dark:text-secondary-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="delete(<?php echo e($assignment->id); ?>)" wire:confirm="Are you sure?" title="Delete" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 dark:text-red-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No assignments found. Create your first one!
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 dark:text-white truncate"><?php echo e($assignment->title); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                <?php echo e($assignment->student->name); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->program): ?> &middot; <?php echo e($assignment->program->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->department): ?> &middot; <?php echo e($assignment->department->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->viewed_at): ?>
                                <span class="text-xs text-green-600 dark:text-green-400" title="Viewed <?php echo e($assignment->viewed_at->format('M d, Y h:i A')); ?>">&#10003;</span>
                            <?php else: ?>
                                <span class="text-xs text-gray-400" title="Not viewed">&ndash;</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="badge whitespace-nowrap <?php echo e($assignment->type === 'assignment' ? 'badge-info' : 'badge-warning'); ?>">
                                <?php echo e(substr($assignment->type, 0, 4)); ?>

                            </span>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->book || $assignment->digitalAsset): ?>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->book): ?>
                                <span>Book: <?php echo e($assignment->book->title); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->digitalAsset): ?>
                                <span>Digital: <?php echo e($assignment->digitalAsset->title); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span class="badge whitespace-nowrap
                            <?php if($assignment->status === 'pending'): ?> badge-warning
                            <?php elseif($assignment->status === 'in_progress'): ?> badge-info
                            <?php elseif($assignment->status === 'completed'): ?> badge-success
                            <?php elseif($assignment->status === 'overdue'): ?> badge-danger
                            <?php else: ?> badge-neutral
                            <?php endif; ?>">
                            <?php echo e(str_replace('_', ' ', ucfirst($assignment->status))); ?>

                        </span>
                        <span>Due: <?php echo e($assignment->due_date ? $assignment->due_date->format('M d, Y') : 'No due date'); ?></span>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button wire:click="showProgress(<?php echo e($assignment->id); ?>)" class="btn-sm btn-primary flex-1 text-center">Progress</button>
                        <button wire:click="edit(<?php echo e($assignment->id); ?>)" class="btn-sm btn-secondary flex-1 text-center">Edit</button>
                        <button wire:click="delete(<?php echo e($assignment->id); ?>)" wire:confirm="Are you sure?" class="btn-sm btn-danger flex-1 text-center">Delete</button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No assignments found. Create your first one!
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignments->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            <?php echo e($assignments->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Lab IX\Documents\proj\ollmchs-library\app\Modules\Assignments\Providers/../Views/livewire/teacher-assignments.blade.php ENDPATH**/ ?>