<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Suspensions</h1>
            <p class="page-subtitle">Manage suspended members</p>
        </div>
        <div class="text-sm text-surface-500">
            <span class="font-semibold text-red-600">{{ $stats['total'] }}</span> suspended
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <input type="text" wire:model.live.debounce="search" placeholder="Search by name or member ID..." class="input w-full md:w-96">
            </div>

            <div class="overflow-x-auto table-mobile-cards">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Member ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Suspended Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                        <tr>
                            <td class="font-mono text-sm">{{ $member->member_id }}</td>
                            <td class="font-medium">
                                <a href="{{ route('members.show', $member->id) }}" wire:navigate class="hover:text-primary-600">
                                    {{ $member->first_name }} {{ $member->last_name }}
                                </a>
                            </td>
                            <td class="text-sm text-surface-500">{{ $member->user?->email ?? '—' }}</td>
                            <td class="text-sm text-surface-500">{{ $member->updated_at->format('M d, Y') }}</td>
                            <td>
                                @can('reinstate-members')
                                <button wire:click="confirmReinstate({{ $member->id }})" class="btn-sm btn-success">
                                    Reinstate
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-surface-400">No suspended members found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $members->links() }}
            </div>
        </div>
    </div>

    @if($reinstateMemberId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('reinstateMemberId', null)">
        <div class="card w-full max-w-md mx-4">
            <div class="card-body">
                <h3 class="text-lg font-semibold mb-2">Confirm Reinstate</h3>
                <p class="text-surface-500 mb-4">Are you sure you want to reinstate this member? They will regain full access to library services.</p>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('reinstateMemberId', null)" class="btn-sm btn-secondary">Cancel</button>
                    <button wire:click="reinstate" class="btn-sm btn-success">Reinstate Member</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
