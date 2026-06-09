<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $editing ? 'Edit Plan' : 'Create Plan' }}</h2>
            </div>

            <form wire:submit="save" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan Name</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full input-field">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                        <select wire:model="type" class="mt-1 block w-full input-field">
                            <option value="individual">Individual</option>
                            <option value="school">School</option>
                        </select>
                        @error('type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Cycle</label>
                        <select wire:model="billingCycle" class="mt-1 block w-full input-field">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        @error('billingCycle') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                        <input type="number" wire:model="price" step="0.01" min="0" class="mt-1 block w-full input-field">
                        @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                        <input type="text" wire:model="currency" maxlength="3" class="mt-1 block w-full input-field">
                        @error('currency') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea wire:model="description" rows="3" class="mt-1 block w-full input-field"></textarea>
                    @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Features (one per line)</label>
                    <textarea wire:model="features" rows="5" class="mt-1 block w-full input-field" placeholder="Unlimited book borrows&#10;Access to digital library&#10;Priority support"></textarea>
                    @error('features') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
                        <input type="number" wire:model="sortOrder" min="0" class="mt-1 block w-full input-field">
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 dark:border-gray-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="btn-primary">{{ $editing ? 'Update Plan' : 'Create Plan' }}</button>
                    <a href="{{ route('admin.subscriptions.plans') }}" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
