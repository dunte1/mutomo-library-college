@section('title', 'Collect Payments')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Collect Payments</h1>

    <div class="card p-4">
        <div class="max-w-md">
            <label class="label">Search Member</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, email, or admission number..."
                   class="input-field w-full">
        </div>

        @if(strlen($search) >= 2 && $users->isNotEmpty())
            <div class="mt-3 border rounded-lg dark:border-gray-700 divide-y dark:divide-gray-700">
                @foreach($users as $user)
                    <button wire:click="selectUser({{ $user->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300 font-medium text-sm">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-800 dark:text-gray-200">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }} @if($user->admission_number) &bull; {{ $user->admission_number }} @endif</div>
                        </div>
                    </button>
                @endforeach
            </div>
        @elseif(strlen($search) >= 2)
            <p class="mt-3 text-sm text-gray-400">No members found.</p>
        @endif
    </div>

    @if($selectedUserId)
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $selectedUserName }}</h2>
                    <p class="text-sm text-gray-500">Outstanding fines</p>
                </div>
                <button wire:click="$set('selectedUserId', null)" class="text-sm text-gray-400 hover:text-gray-600">&times; Clear</button>
            </div>

            @if(count($outstandingFines) > 0)
                <div class="overflow-x-auto table-mobile-cards">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                                <th class="py-2 px-3">Type</th>
                                <th class="py-2 px-3">Book</th>
                                <th class="py-2 px-3 text-right">Amount (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outstandingFines as $fine)
                                <tr class="border-t dark:border-gray-700">
                                    <td class="py-2 px-3"><span class="badge">{{ ucwords(str_replace('_', ' ', $fine['type'])) }}</span></td>
                                    <td class="py-2 px-3 text-gray-600 dark:text-gray-400">
                                        {{ $fine['borrow_record']['book_copy']['book']['title'] ?? 'N/A' }}
                                    </td>
                                    <td class="py-2 px-3 text-right font-medium text-gray-800 dark:text-gray-200">{{ number_format($fine['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="border-t dark:border-gray-700 font-semibold">
                                <td colspan="2" class="py-2 px-3 text-gray-800 dark:text-gray-200">Total</td>
                                <td class="py-2 px-3 text-right text-gray-800 dark:text-gray-200">KES {{ number_format($totalAmount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 border-t dark:border-gray-700 pt-4 space-y-4">
                    <div>
                        <label class="label">Amount (KES)</label>
                        <input type="number" step="0.01" wire:model="amount" class="input-field w-full max-w-xs">
                        @error('amount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Payment Method</label>
                        <select wire:model="paymentMethod" class="input-field w-full max-w-xs">
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Reference (optional)</label>
                        <input type="text" wire:model="reference" class="input-field w-full max-w-xs" placeholder="Receipt/transaction ref">
                    </div>
                    <div>
                        <button wire:click="payAll" class="btn-primary">Collect Payment</button>
                    </div>
                </div>
            @else
                <p class="text-gray-400 text-sm">No outstanding fines for this member.</p>
            @endif
        </div>
    @endif
</div>
