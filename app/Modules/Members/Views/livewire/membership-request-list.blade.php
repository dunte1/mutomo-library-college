<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Membership Requests</h1>
            <p class="page-subtitle">Pending membership registration requests</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <input type="text" wire:model.live.debounce="search" placeholder="Search by name or email..." class="input w-full md:w-96">
            </div>

            <div class="overflow-x-auto table-mobile-cards">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $user)
                        <tr>
                            <td class="font-medium">{{ $user->name }}</td>
                            <td class="text-sm text-surface-500">{{ $user->email }}</td>
                            <td class="text-sm text-surface-500">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($user->hasVerifiedEmail())
                                <span class="badge badge-success">Verified</span>
                                @else
                                <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button wire:click="approve({{ $user->id }})" wire:confirm="Approve this membership request?" class="btn-sm btn-success">
                                        Approve
                                    </button>
                                    <button wire:click="reject({{ $user->id }})" wire:confirm="Reject this membership request?" class="btn-sm btn-danger">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-surface-400">No pending membership requests.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
