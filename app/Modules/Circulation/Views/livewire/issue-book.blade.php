@section('title', 'Issue Book')
<div>
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('circulation.index') }}" wire:navigate class="btn-ghost p-2 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-surface-900 dark:text-white">Issue Book</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Check out a book to a library member</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-6">
            {{-- Scan Barcode -- large mobile-optimised input --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Scan Barcode</h3>
                </div>
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-surface-400 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <input type="text" wire:model="barcode" wire:keydown.enter="searchByBarcode"
                                placeholder="Scan or enter barcode..."
                                class="input-field pl-9 md:pl-3 w-full text-base" autofocus inputmode="text"
                                aria-label="Barcode input">
                        </div>
                        <button wire:click="searchByBarcode" class="btn-primary min-h-[48px] justify-center h-12">
                            <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="hidden md:inline">Search</span>
                        </button>
                    </div>
                </div>
            </div>

            @if ($message)
                <div class="p-4 rounded-xl text-sm {{ $messageType === 'error' ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-200' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200' }}">
                    {{ $message }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Search Member</h3>
                </div>
                <div class="card-body">
                    @if($selectedUser)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20">
                            <div>
                                <p class="font-medium text-surface-900 dark:text-white">{{ $selectedUser->name }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">{{ $selectedUser->email }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">{{ $selectedUser->department?->name ?? 'N/A' }}</p>
                            </div>
                            <button wire:click="resetSelection" class="btn-ghost btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <input type="text" wire:model.live.debounce.300ms="searchUser" placeholder="Search by name, email, admission/employee ID..."
                            class="input-field">
                        @if(count($searchResults) > 0)
                            <div class="mt-2 border border-surface-200 dark:border-surface-700 rounded-xl divide-y divide-surface-100 dark:divide-surface-700">
                                @foreach($searchResults as $user)
                                    <button wire:click="selectUser({{ $user->id }})" class="w-full text-left px-4 py-3 hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors">
                                        <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-surface-500">{{ $user->email }} · {{ $user->department?->name ?? 'N/A' }}</p>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Search Book</h3>
                </div>
                <div class="card-body">
                    @if($selectedCopy)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20">
                            <div>
                                <p class="font-medium text-surface-900 dark:text-white">{{ $selectedCopy->book->title }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Barcode: {{ $selectedCopy->barcode }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Location: {{ $selectedCopy->shelf_location ?? 'N/A' }}</p>
                            </div>
                            <button wire:click="resetSelection" class="btn-ghost btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <input type="text" wire:model.live.debounce.300ms="searchBook" placeholder="Search by title or ISBN..."
                            class="input-field">
                        @if(count($copyResults) > 0)
                            <div class="mt-2 border border-surface-200 dark:border-surface-700 rounded-xl divide-y divide-surface-100 dark:divide-surface-700">
                                @foreach($copyResults as $copy)
                                    <button wire:click="selectCopy({{ $copy->id }})" class="w-full text-left px-4 py-3 hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors">
                                        <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $copy->book->title }}</p>
                                        <p class="text-xs text-surface-500">Barcode: {{ $copy->barcode }} · Location: {{ $copy->shelf_location ?? 'N/A' }}</p>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Issue Summary</h3>
                </div>
                <div class="card-body space-y-4">
                    @if($selectedUser && $selectedCopy)
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                            <h4 class="font-medium text-emerald-800 dark:text-emerald-300 mb-2">Ready to Issue</h4>
                            <div class="space-y-2 text-sm text-emerald-700 dark:text-emerald-400">
                                <p><strong>Member:</strong> {{ $selectedUser->name }}</p>
                                <p><strong>Book:</strong> {{ $selectedCopy->book->title }}</p>
                                <p><strong>Barcode:</strong> {{ $selectedCopy->barcode }}</p>
                                <p><strong>Due Date:</strong> {{ now()->addDays(app(\App\Modules\Circulation\Services\BorrowingService::class)->getBorrowDuration($selectedUser))->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <button wire:click="issue" class="btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="issue">
                            <svg wire:loading.remove wire:target="issue" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg wire:loading wire:target="issue" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span wire:loading.remove wire:target="issue">Confirm Issue</span>
                            <span wire:loading wire:target="issue">Issuing...</span>
                        </button>
                    @else
                        <div class="text-center py-8 text-surface-400 dark:text-surface-500">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <p>Select a member and a book to issue</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
