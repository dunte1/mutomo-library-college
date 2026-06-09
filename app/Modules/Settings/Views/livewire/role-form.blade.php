<div>
    <x-header :title="$isEditing ? 'Edit Role' : 'Create Role'"
        :subtitle="$isEditing ? 'Update role name and permissions' : 'Define a new role with specific permissions'">
        <x-slot:actions>
            <x-btn :href="route('settings.roles')" wire:navigate>Back to Roles</x-btn>
        </x-slot:actions>
    </x-header>

    <x-card>
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-field label="Role Name" required>
                    <x-input wire:model="name" placeholder="e.g., senior-librarian" />
                    <p class="text-xs text-gray-500 mt-1">Use lowercase with hyphens (e.g., assistant-librarian)</p>
                    <x-input-error for="name" />
                </x-field>

                <x-field label="Guard Name" required>
                    <x-select wire:model="guard_name">
                        <option value="web">Web</option>
                        <option value="api">API</option>
                    </x-select>
                    <x-input-error for="guard_name" />
                </x-field>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Permissions
                    <span class="text-xs text-gray-500 ml-2">({{ count($selectedPermissions) }} selected)</span>
                </label>

                <div class="space-y-4">
                    @foreach($groupedPermissions as $group => $perms)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800">
                                <span class="font-medium text-sm text-gray-700 dark:text-gray-300 capitalize">{{ $group }}</span>
                                <button type="button" wire:click="toggleGroup('{{ $group }}')"
                                    class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                    Toggle All
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-2 p-4">
                                @foreach($perms as $perm)
                                    <label class="flex items-center gap-2 cursor-pointer hover:text-primary-600">
                                        <input type="checkbox" value="{{ $perm->id }}"
                                            wire:model="selectedPermissions"
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500" />
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $perm->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <x-input-error for="selectedPermissions" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-btn :href="route('settings.roles')" wire:navigate>Cancel</x-btn>
                <x-btn primary type="submit">
                    {{ $isEditing ? 'Update Role' : 'Create Role' }}
                </x-btn>
            </div>
        </form>
    </x-card>
</div>
