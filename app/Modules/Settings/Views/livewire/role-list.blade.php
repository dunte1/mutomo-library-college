<div>
    <x-header title="Roles & Permissions" subtitle="Manage access control roles and their permissions">
        <x-slot:actions>
            <x-btn primary icon="plus" :href="route('settings.roles.create')" wire:navigate>Create Role</x-btn>
        </x-slot:actions>
    </x-header>

    <x-stats-bar :stats="[
        ['label' => 'Total Roles', 'value' => $roles->total()],
        ['label' => 'Permissions', 'value' => $permissionCount],
    ]" />

    <x-filter-bar>
        <x-input icon="search" placeholder="Search roles..." wire:model.live.debounce="search" />
    </x-filter-bar>

    <x-card>
        <x-table>
            <x-thead>
                <x-tr>
                    <x-th>Role Name</x-th>
                    <x-th>Guard</x-th>
                    <x-th>Permissions</x-th>
                    <x-th>Users</x-th>
                    <x-th></x-th>
                </x-tr>
            </x-thead>
            <x-tbody>
                @forelse($roles as $role)
                    <x-tr wire:key="{{ $role->id }}">
                        <x-td>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($role->name) }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $role->id }}</div>
                        </x-td>
                        <x-td><x-badge>{{ $role->guard_name }}</x-badge></x-td>
                        <x-td>
                            <div class="flex flex-wrap gap-1 max-w-md">
                                @foreach($role->permissions->take(5) as $perm)
                                    <x-badge variant="info" class="text-xs">{{ $perm->name }}</x-badge>
                                @endforeach
                                @if($role->permissions->count() > 5)
                                    <x-badge variant="default">+{{ $role->permissions->count() - 5 }} more</x-badge>
                                @endif
                            </div>
                        </x-td>
                        <x-td>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ \Spatie\Permission\Models\Role::findByName($role->name)?->users->count() ?? 0 }}
                            </span>
                        </x-td>
                        <x-td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <x-btn sm :href="route('settings.roles.edit', $role->id)" wire:navigate>Edit</x-btn>
                                @if(!in_array($role->name, ['super-admin', 'admin']))
                                    <x-btn sm variant="danger"
                                        wire:click="delete({{ $role->id }})"
                                        wire:confirm="Delete this role? Users with this role will lose its permissions.">
                                        Delete
                                    </x-btn>
                                @endif
                            </div>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td colspan="5">
                            <x-empty-state icon="shield" title="No roles found"
                                description="Create your first role to start managing permissions." />
                        </x-td>
                    </x-tr>
                @endforelse
            </x-tbody>
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $roles->links() }}
    </div>
</div>
