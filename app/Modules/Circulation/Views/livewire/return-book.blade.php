@section('title', 'Return Book')
<div>
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('circulation.index') }}" wire:navigate class="btn-ghost p-2 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-surface-900 dark:text-white">Return Book</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Process book returns and check for fines</p>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto space-y-6">
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
                            placeholder="Scan or enter book barcode..."
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

        @if($record)
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Borrow Details</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="label">Member</p>
                            <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $record->user->name }}</p>
                            <p class="text-xs text-surface-500">{{ $record->user->email }}</p>
                        </div>
                        <div>
                            <p class="label">Book</p>
                            <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $record->bookCopy->book->title }}</p>
                            <p class="text-xs text-surface-500">Barcode: {{ $record->bookCopy->barcode }}</p>
                        </div>
                        <div>
                            <p class="label">Borrowed Date</p>
                            <p class="text-sm text-surface-900 dark:text-white">{{ $record->borrowed_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="label">Due Date</p>
                            <p class="text-sm {{ $record->isOverdue() ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-surface-900 dark:text-white' }}">
                                {{ $record->due_at->format('M d, Y') }}
                                @if($record->isOverdue())
                                    ({{ $record->daysOverdue() }} days overdue)
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($record->isOverdue())
                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                <strong>Overdue!</strong> This book is {{ $record->daysOverdue() }} days overdue.
                                A fine of KES {{ number_format($record->daysOverdue() * 50, 2) }} will be applied.
                            </p>
                        </div>
                    @endif

                    <div>
                        <label class="label">Book Condition</label>
                        <select wire:model="condition" class="input-field">
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor (damage will be noted)</option>
                        </select>
                    </div>

                    @if($showConfirm)
                    <button wire:click="confirmReturn" class="btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="confirmReturn">
                        <svg wire:loading wire:target="confirmReturn" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span wire:loading.remove wire:target="confirmReturn">Return Book</span>
                        <span wire:loading wire:target="confirmReturn">Processing...</span>
                    </button>
                    @endif
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-8 text-surface-400 dark:text-surface-500">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <p>Scan a book barcode to process return</p>
                </div>
            </div>
        @endif
    </div>
</div>
