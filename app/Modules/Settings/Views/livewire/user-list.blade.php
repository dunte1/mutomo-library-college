<div>
    <x-header title="Users" subtitle="Manage system users and their roles" />

    <x-stats-bar :stats="[
        ['label' => 'Total Users', 'value' => $stats['total']],
        ['label' => 'Active', 'value' => $stats['active']],
        ['label' => 'Admins', 'value' => $stats['admins']],
        ['label' => 'Librarians', 'value' => $stats['librarians']],
    ]" />

    <x-filter-bar>
        <x-input icon="search" placeholder="Search name, email, ID..." wire:model.live.debounce="search" />
        <x-select wire:model.live="role">
            <option value="">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
            @endforeach
        </x-select>
        <x-select wire:model.live="status">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </x-select>
        @if($search || $role || $status)
            <x-btn text-xs wire:click="clearFilters">Clear</x-btn>
        @endif
        <x-slot:actions>
            <x-btn primary icon="plus" :href="route('settings.users.create')" wire:navigate>Add User</x-btn>
        </x-slot:actions>
    </x-filter-bar>

    <x-card>
        <x-table>
            <x-thead>
                <x-tr>
                    <x-th>Name</x-th>
                    <x-th>Email</x-th>
                    <x-th>Roles</x-th>
                    <x-th>Status</x-th>
                    <x-th>Last Login</x-th>
                    <x-th></x-th>
                </x-tr>
            </x-thead>
            <x-tbody>
                @forelse($users as $user)
                    <x-tr wire:key="{{ $user->id }}">
                        <x-td>
                            <div class="flex items-center gap-3">
                                <x-avatar :src="$user->avatar" :alt="$user->name" size="md" />
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->admission_number ?? $user->employee_id ?? '—' }}</div>
                                </div>
                            </div>
                        </x-td>
                        <x-td>{{ $user->email }}</x-td>
                        <x-td>
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    <x-badge>{{ $role->name }}</x-badge>
                                @endforeach
                            </div>
                        </x-td>
                        <x-td>
                            <x-badge :variant="$user->is_active ? 'success' : 'danger'">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </x-td>
                        <x-td>
                            <span class="text-xs text-gray-500">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Never' }}
                            </span>
                        </x-td>
                        <x-td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <x-btn sm :href="route('settings.users.edit', $user->id)" wire:navigate>Edit</x-btn>
                                <x-btn sm :variant="$user->is_active ? 'warning' : 'success'"
                                    wire:click="toggleActive({{ $user->id }})"
                                    wire:confirm="Toggle user status?">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </x-btn>
                            </div>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td colspan="6">
                            <x-empty-state icon="users" title="No users found"
                                description="No users match your search criteria." />
                        </x-td>
                    </x-tr>
                @endforelse
            </x-tbody>
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
