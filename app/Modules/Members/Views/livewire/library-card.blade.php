@section('title', 'Library Card - ' . $member->full_name)
<div>
    <x-slot name="header">Library Card</x-slot>
    <x-slot name="subtitle">{{ $member->full_name }} &middot; {{ $member->member_id }}</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Card Area --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Card Preview --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Library Card</h3>
                    <div class="flex items-center gap-2">
                        @can('manage-library-cards')
                            @if(!$card)
                                <button wire:click="generateCard" wire:confirm="Generate a new library card for this member?"
                                    class="btn-primary btn-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Generate Card
                                </button>
                            @else
                                <button wire:click="reissueCard" wire:confirm="Reissue card? The current card will be marked as replaced."
                                    class="btn-outline btn-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reissue
                                </button>
                                <button wire:click="markAsLost" wire:confirm="Mark this card as lost?"
                                    class="btn-sm btn-danger">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    Mark Lost
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>

                <div class="card-body flex justify-center py-8">
                    @if($card)
                        <div class="w-[320px] bg-gradient-to-br from-primary-800 to-primary-900 rounded-2xl shadow-xl overflow-hidden border border-primary-700">
                            {{-- Card Header --}}
                            <div class="px-6 py-4 border-b border-primary-700/50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span class="text-sm font-semibold text-white">LIBRARY CARD</span>
                                    </div>
                                    <span class="text-xs text-primary-300 font-mono">{{ config('app.name') }}</span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="px-6 py-4 space-y-4">
                                {{-- Photo and Name --}}
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-white/10 flex items-center justify-center shrink-0 border border-white/20">
                                        @php
                                            $photoUrl = null;
                                            if ($card->passport_photo && file_exists(storage_path('app/public/' . $card->passport_photo))) {
                                                $photoUrl = storage_path('app/public/' . $card->passport_photo);
                                            } elseif ($member->photo && file_exists(storage_path('app/public/' . $member->photo))) {
                                                $photoUrl = storage_path('app/public/' . $member->photo);
                                            }
                                        @endphp
                                        @if($photoUrl)
                                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($photoUrl)) }}"
                                                 alt="{{ $member->full_name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xl font-bold text-white">
                                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-lg font-bold text-white truncate">{{ $member->full_name }}</h4>
                                        <p class="text-xs text-primary-300">{{ $member->membership_type }}</p>
                                    </div>
                                </div>

                                {{-- Card Number --}}
                                <div>
                                    <p class="text-xs text-primary-300 uppercase tracking-wider mb-1">Card Number</p>
                                    <p class="text-sm font-mono font-bold text-white tracking-wider">{{ $card->card_number }}</p>
                                </div>

                                {{-- Member Details --}}
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <p class="text-primary-300">Member ID</p>
                                        <p class="text-white font-medium">{{ $member->member_id }}</p>
                                    </div>
                                    <div>
                                        <p class="text-primary-300">Department</p>
                                        <p class="text-white font-medium">{{ $member->department?->name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-primary-300">Issued</p>
                                        <p class="text-white font-medium">{{ $card->issued_at->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-primary-300">Expires</p>
                                        <p class="text-white font-medium">{{ $card->expires_at?->format('d M Y') ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                {{-- Barcode --}}
                                <div class="pt-2 border-t border-primary-700/50">
                                    <div class="bg-white rounded-lg p-2 text-center">
                                        <p class="font-mono text-xs text-gray-800 tracking-widest">{{ $card->barcode }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- QR Code --}}
                            @if($card->qr_code)
                                <div class="px-6 pb-4 flex justify-center">
                                    <div class="bg-white rounded-lg p-2 inline-block">
                                        <div class="w-16 h-16 flex items-center justify-center">
                                            {!! $card->qr_code !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Card Footer --}}
                            <div class="px-6 py-3 bg-primary-900/50 border-t border-primary-700/50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @can('view-library-cards')
                                            <button wire:click="downloadCard"
                                                class="text-xs text-primary-300 hover:text-white transition-colors flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Download
                                            </button>
                                        @endcan
                                    </div>
                                    <span class="text-[10px] text-primary-400">Present this card for library services</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            <h3 class="text-lg font-medium text-surface-900 dark:text-white mb-2">No Library Card</h3>
                            <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">
                                This member does not have a library card yet.
                            </p>
                            @can('manage-library-cards')
                                <button wire:click="generateCard" wire:confirm="Generate a new library card?"
                                    class="btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Generate Card
                                </button>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card History --}}
            @if($history->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Card History</h3>
                    </div>
                    <div class="overflow-x-auto table-mobile-cards">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="table-header">Card Number</th>
                                    <th class="table-header">Status</th>
                                    <th class="table-header">Issued</th>
                                    <th class="table-header">Expires</th>
                                    <th class="table-header">Issued By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $card)
                                    <tr>
                                        <td class="table-cell font-mono text-xs font-medium">{{ $card->card_number }}</td>
                                        <td class="table-cell">
                                            @switch($card->status)
                                                @case('active')
                                                    <span class="badge-success">Active</span>
                                                    @break
                                                @case('lost')
                                                    <span class="badge-danger">Lost</span>
                                                    @break
                                                @case('replaced')
                                                    <span class="badge-warning">Replaced</span>
                                                    @break
                                                @case('expired')
                                                    <span class="badge-neutral">Expired</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="table-cell text-sm">{{ $card->issued_at->format('d M Y') }}</td>
                                        <td class="table-cell text-sm">{{ $card->expires_at?->format('d M Y') ?? '—' }}</td>
                                        <td class="table-cell text-sm">{{ $card->issuer?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Stats --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Card Statistics</h3>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Total Cards</span>
                        <span class="text-sm font-semibold text-surface-900 dark:text-white">{{ $cardStats['total'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Active</span>
                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $cardStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Lost</span>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $cardStats['lost'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Replaced</span>
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">{{ $cardStats['replaced'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Issued This Month</span>
                        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $cardStats['issued_this_month'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Actions</h3>
                </div>
                <div class="card-body space-y-3">
                    <a href="{{ route('members.show', $member->id) }}" wire:navigate class="btn-outline w-full justify-center btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Member Details
                    </a>
                    @can('edit-members')
                        <a href="{{ route('members.edit', $member->id) }}" wire:navigate class="btn-outline w-full justify-center btn-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Member
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Verification Info --}}
            @if($card)
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Verification</h3>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-xs text-surface-500 dark:text-surface-400 mb-2">Scan QR code to verify</p>
                        @if($card->qr_code)
                            <div class="bg-white rounded-lg p-2 inline-block mx-auto">
                                <div class="w-24 h-24 flex items-center justify-center">
                                    {!! $card->qr_code !!}
                                </div>
                            </div>
                        @endif
                        <p class="text-xs text-surface-400 mt-2 font-mono break-all">{{ $card->card_number }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
