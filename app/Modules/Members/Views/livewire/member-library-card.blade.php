@section('title', 'My Library Card')
<div>
    <x-slot name="header">My Library Card</x-slot>
    <x-slot name="subtitle">View and manage your library card</x-slot>

    <style>
      @import url('https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&family=Poppins:wght@600;700&display=swap');
    </style>

    <div class="max-w-4xl mx-auto">
        @if($member && $card)
            @php
                $primary = $cardBranding['card_primary_color'];
                $secondary = $cardBranding['card_secondary_color'];
                $tertiary = $cardBranding['card_tertiary_color'];
                $textColor = $cardBranding['card_text_color'];
                $accentColor = $cardBranding['card_accent_color'];
                $cardLogo = $cardBranding['card_logo'] ?: null;
                $siteName = $displaySettings['site_name'] ?? config('app.name');
                $shortName = strtoupper(explode(' ', $siteName)[0] ?? 'OLLMCHS');
                $motto = $displaySettings['library_motto'] ?? 'Learn • Discover • Succeed';
                $phone = $displaySettings['library_phone'] ?? '';
                $email = $displaySettings['library_email'] ?? '';
                $website = $displaySettings['library_website'] ?? '';
                $address = $displaySettings['library_address'] ?? '';
            @endphp
            @php
                $photoUrl = null;
                if ($card->passport_photo && file_exists(storage_path('app/public/' . $card->passport_photo))) {
                    $photoUrl = storage_path('app/public/' . $card->passport_photo);
                } elseif ($member->photo && file_exists(storage_path('app/public/' . $member->photo))) {
                    $photoUrl = storage_path('app/public/' . $member->photo);
                }
            @endphp

            <div class="space-y-6">
                {{-- Card Preview --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Library Card</h3>
                        <button wire:click="downloadCard" class="btn-outline btn-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </button>
                    </div>

                    <div class="card-body py-8 overflow-x-auto">
                        <div class="min-w-max flex flex-col items-center gap-6">

                        @include('members::partials.library-card-face', [
                            'card' => $card,
                            'member' => $member,
                            'cardBranding' => $cardBranding,
                            'displaySettings' => $displaySettings,
                            'cardAuthority' => $cardAuthority,
                        ])

                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-surface-900 dark:text-white">Card Actions</h3>
                    </div>
                    <div class="card-body space-y-3">
                        <button wire:click="downloadCard" class="btn-outline w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Card (PDF)
                        </button>
                        <button wire:click="reportLost" wire:confirm="Are you sure you want to report your card as lost? Please contact the library for a replacement."
                            class="btn-danger w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Report Card Lost
                        </button>
                    </div>
                </div>

                {{-- Expiry Warning --}}
                @if($card->expires_at && $card->expires_at->diffInDays(now()) <= 30)
                    <div class="card border-amber-300 dark:border-amber-600">
                        <div class="card-body">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Card Expiring Soon</p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                        Your library card expires on {{ $card->expires_at->format('d M Y') }}.
                                        Please visit the library to renew your membership.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        @else
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <h3 class="text-lg font-medium text-surface-900 dark:text-white mb-2">No Library Card</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">
                        @if($member)
                            You don't have a library card yet. Please contact the library to get one issued.
                        @else
                            No member account found. Please register at the library to get started.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
