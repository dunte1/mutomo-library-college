@section('title', 'Library Card - ' . $member->full_name)
<div>
    <x-slot name="header">Library Card</x-slot>
    <x-slot name="subtitle">{{ $member->full_name }} &middot; {{ $member->member_id }}</x-slot>

    <style>
      @import url('https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&family=Poppins:wght@600;700&display=swap');
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Card Area --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Card Preview --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Library Card</h3>
                    <div class="flex flex-wrap items-center gap-2">
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

                <div class="card-body py-8 overflow-x-auto">
                    @if($card)
                        <div class="min-w-max flex flex-col items-center gap-6">
                        @include('members::partials.library-card-face', [
                            'card' => $card,
                            'member' => $member,
                            'cardBranding' => $cardBranding,
                            'displaySettings' => $displaySettings,
                            'cardAuthority' => $cardAuthority,
                            'previewPhotoPath' => $passportPhoto?->getRealPath(),
                        ])

                        {{-- Download Button --}}
                        @can('view-library-cards')
                            <div class="mt-2">
                                <button wire:click="downloadCard" class="btn-outline btn-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Card (PDF)
                                </button>
                            </div>
                        @endcan
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
                                @foreach($history as $hist)
                                    <tr>
                                        <td class="table-cell font-mono text-xs font-medium">{{ $hist->card_number }}</td>
                                        <td class="table-cell">
                                            @switch($hist->status)
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
                                        <td class="table-cell text-sm">{{ $hist->issued_at->format('d M Y') }}</td>
                                        <td class="table-cell text-sm">{{ $hist->expires_at?->format('d M Y') ?? '—' }}</td>
                                        <td class="table-cell text-sm">{{ $hist->issuer?->name ?? '—' }}</td>
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

            {{-- Passport Photo Upload --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Passport Photo</h3>
                </div>
                <div class="card-body space-y-3">
                    <p class="text-xs text-surface-500 dark:text-surface-400">
                        Upload a passport photo for the library card. This will also be used as the member's profile photo.
                    </p>
                    <div x-data="{
                        cropping: false,
                        dragOver: false,
                        fileSelected(file) {
                            if (!file) return;
                            this.cropping = true;
                            this.$nextTick(async () => {
                                const img = this.$refs.photoCropImage;
                                img.src = await __readFileAsDataURL(file);
                                img.onload = () => { __initCropper(img, 148 / 178); };
                            });
                        },
                        pickFromInput(event) {
                            this.fileSelected(event.target.files[0]);
                            event.target.value = '';
                        },
                        onDrop(event) {
                            this.dragOver = false;
                            this.fileSelected(event.dataTransfer.files[0]);
                        },
                        async confirmCrop() {
                            const blob = await __getCroppedBlob('image/jpeg');
                            if (!blob) return;
                            const croppedFile = new File([blob], 'passport.jpg', { type: 'image/jpeg' });
                            await $wire.upload('passportPhoto', croppedFile);
                            this.cropping = false;
                            __destroyCropper();
                        },
                        cancelCrop() {
                            this.cropping = false;
                            __destroyCropper();
                        }
                    }">
                        <div class="border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-colors"
                             :class="dragOver ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'border-surface-300 dark:border-surface-600 hover:border-primary-400'"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="onDrop"
                             @click="$refs.photoInput.click()">
                            @if($passportPhoto)
                                <img src="{{ $passportPhoto->temporaryUrl() }}" class="w-24 h-28 object-cover rounded-lg mx-auto mb-2 shadow-sm">
                            @elseif($member->photo)
                                <img src="{{ Storage::url($member->photo) }}" class="w-24 h-28 object-cover rounded-lg mx-auto mb-2 shadow-sm">
                            @else
                                <svg class="w-10 h-10 mx-auto text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                            <p class="text-xs text-surface-500 mt-1">
                                @if($passportPhoto)
                                    {{ $passportPhoto->getClientOriginalName() }}
                                @else
                                    Drag &amp; drop a photo here or click to browse
                                @endif
                            </p>
                            <p class="text-[10px] text-surface-400 mt-1">Auto-cropped to the ID frame &middot; max 2MB</p>
                        </div>
                        <input type="file" x-ref="photoInput" x-on:change="pickFromInput" accept="image/*" class="hidden">
                        <div wire:loading wire:target="passportPhoto" class="text-xs text-primary-600 mt-2">Uploading...</div>
                        @error("passportPhoto") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                        @if($passportPhoto)
                            <div class="flex items-center gap-2 mt-3">
                                <button wire:click="$set('passportPhoto', null)" class="btn-sm btn-outline text-xs">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Remove
                                </button>
                                <span class="text-xs text-emerald-600">Ready to use on card</span>
                            </div>
                        @endif

                        <div x-show="cropping" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @keydown.escape.window="cancelCrop">
                            <div class="bg-white dark:bg-surface-800 rounded-2xl shadow-2xl max-w-3xl w-full mx-4 overflow-hidden" @click.outside="cancelCrop">
                                <div class="p-4 border-b border-surface-200 dark:border-surface-700 flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white">Crop Passport Photo</h3>
                                    <button type="button" @click="cancelCrop" class="p-1 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="p-4 bg-surface-100 dark:bg-surface-900/50">
                                    <img x-ref="photoCropImage" class="max-w-full max-h-[60vh] mx-auto">
                                </div>
                                <div class="p-4 border-t border-surface-200 dark:border-surface-700 flex justify-end gap-3">
                                    <button type="button" @click="cancelCrop" class="btn-secondary">Cancel</button>
                                    <button type="button" @click="confirmCrop" class="btn-primary">Apply Crop</button>
                                </div>
                            </div>
                        </div>
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
