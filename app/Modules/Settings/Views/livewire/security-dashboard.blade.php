<div>
    <x-header title="Security Dashboard" subtitle="Monitor login activity, user sessions, and security events" />

    <x-stats-bar :stats="[
        ['label' => 'Active Today', 'value' => $activeToday, 'color' => 'success'],
        ['label' => 'Failed Today', 'value' => $failedToday, 'color' => 'danger'],
        ['label' => '2FA Enabled', 'value' => $twoFactorEnabled],
        ['label' => 'Inactive Users', 'value' => $inactiveUsers, 'color' => 'warning'],
        ['label' => 'Total Users', 'value' => $totalUsers],
    ]" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-card title="Quick Actions">
            <div class="space-y-3">
                <a href="{{ route('settings.users') }}" wire:navigate
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">Manage Users</p>
                        <p class="text-sm text-gray-500">Create, edit, and manage system users</p>
                    </div>
                </a>
                <a href="{{ route('settings.roles') }}" wire:navigate
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">Roles & Permissions</p>
                        <p class="text-sm text-gray-500">Configure access control roles</p>
                    </div>
                </a>
                <a href="{{ route('settings.security') }}" wire:navigate
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">Security Settings</p>
                        <p class="text-sm text-gray-500">Password policies, 2FA, session timeout</p>
                    </div>
                </a>
                <a href="{{ route('settings.audit-logs') }}" wire:navigate
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">Audit Logs</p>
                        <p class="text-sm text-gray-500">View system activity logs</p>
                    </div>
                </a>
            </div>
        </x-card>

        <x-card title="Security Summary">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Users</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Active Today</span>
                    <span class="font-semibold text-green-600 dark:text-green-400">{{ $activeToday }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Failed Login Attempts Today</span>
                    <span class="font-semibold text-red-600 dark:text-red-400">{{ $failedToday }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">2FA Enabled</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $twoFactorEnabled }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Inactive Accounts</span>
                    <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $inactiveUsers }}</span>
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Recent Login Activity">
        <x-filter-bar>
            <x-input icon="search" placeholder="Search user..." wire:model.live.debounce="search" />
            <x-select wire:model.live="eventType">
                <option value="">All Events</option>
                <option value="success">Successful</option>
                <option value="failed">Failed</option>
            </x-select>
        </x-filter-bar>

        <x-table>
            <x-thead>
                <x-tr>
                    <x-th>User</x-th>
                    <x-th>IP Address</x-th>
                    <x-th>Device</x-th>
                    <x-th>Status</x-th>
                    <x-th>Time</x-th>
                </x-tr>
            </x-thead>
            <x-tbody>
                @forelse($recentActivity as $log)
                    <x-tr wire:key="{{ $log->id }}">
                        <x-td>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log->user?->name ?? 'Unknown' }}</span>
                            </div>
                        </x-td>
                        <x-td><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ $log->ip_address }}</code></x-td>
                        <x-td>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $log->device ?? $log->browser ?? '—' }}
                            </span>
                        </x-td>
                        <x-td>
                            <x-badge :variant="$log->is_successful ? 'success' : 'danger'">
                                {{ $log->is_successful ? 'Success' : 'Failed' }}
                            </x-badge>
                        </x-td>
                        <x-td>
                            <span class="text-sm text-gray-500">{{ $log->login_at?->diffForHumans() ?? '—' }}</span>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td colspan="5">
                            <x-empty-state icon="shield" title="No login activity"
                                description="Login events will appear here as users access the system." />
                        </x-td>
                    </x-tr>
                @endforelse
            </x-tbody>
        </x-table>

        @if($recentActivity->hasPages())
            <div class="mt-4">
                {{ $recentActivity->links() }}
            </div>
        @endif
    </x-card>
</div>
