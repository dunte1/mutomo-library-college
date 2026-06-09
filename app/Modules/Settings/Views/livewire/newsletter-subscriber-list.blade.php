<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 items-center gap-4">
            <x-text-input
                wire:model.live.debounce="search"
                placeholder="Search subscribers..."
                class="max-w-xs"
            />
            <select
                wire:model.live="filterStatus"
                class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="unsubscribed">Unsubscribed</option>
            </select>
        </div>
        <button
            wire:click="exportCsv"
            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm table-mobile-cards">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Subscribed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($subscribers as $subscriber)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $subscriber->email }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $subscriber->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $subscriber->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($subscriber->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Unsubscribed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button
                                wire:click="delete({{ $subscriber->id }})"
                                wire:confirm="Delete this subscriber?"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                            No subscribers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $subscribers->links() }}
    </div>
</div>
