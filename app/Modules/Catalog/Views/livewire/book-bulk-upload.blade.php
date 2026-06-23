@section('title', 'Bulk Import')
<div>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-ghost p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Bulk Upload Books</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Import multiple books at once using a CSV file</p>
            </div>
        </div>
    </div>

    @if($step === 3)
        <div class="card">
            <div class="card-body text-center py-12">
                @if($failed === 0)
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Import Complete</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">{{ $imported }} book(s) imported successfully.</p>
                @else
                    <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Import Completed with Errors</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">{{ $imported }} book(s) imported, {{ $failed }} failed.</p>
                    @if(!empty($failedRows))
                        <div class="mt-4 text-left max-w-lg mx-auto">
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 space-y-3">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">Failed rows:</p>
                                @foreach($failedRows as $fail)
                                    <div class="text-xs text-red-700 dark:text-red-300">
                                        <span class="font-semibold">{{ $fail['title'] }}</span>:
                                        <ul class="list-disc list-inside mt-1">
                                            @foreach($fail['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
                <div class="mt-6 flex items-center justify-center gap-3">
                    <button wire:click="resetUpload" class="btn-primary">Upload Another File</button>
                    <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-outline">View Books</a>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">
                    {{ $step === 1 ? 'Upload CSV File' : 'Review & Confirm' }}
                </h3>
            </div>
            <div class="card-body">
                @if($step === 1)
                    <div class="space-y-4">
                        <div class="bg-surface-50 dark:bg-surface-700/50 rounded-xl p-4 text-sm text-surface-600 dark:text-surface-400 space-y-2">
                            <p class="font-medium text-surface-900 dark:text-white">File Format Requirements:</p>
                            <p>Upload a CSV or Excel (.xlsx/.xls) file with a header row containing the following columns:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li><code class="text-primary-600 dark:text-primary-400">title</code> (required) - Book title</li>
                                <li><code>isbn</code> - ISBN number</li>
                                <li><code>authors</code> - Comma-separated author names</li>
                                <li><code>category</code> - Category name (matches existing)</li>
                                <li><code>publisher</code> - Publisher name (matches existing)</li>
                                <li><code>language</code> - Language code (en, sw, fr, etc.)</li>
                                <li><code>pages</code> - Number of pages</li>
                                <li><code>publication_year</code> - Year of publication</li>
                                <li><code>edition</code> - Edition info</li>
                                <li><code>copies_count</code> - Number of copies (default: 1)</li>
                                <li><code>shelf_location</code> - Shelf location</li>
                                <li><code>price</code> - Book price</li>
                            </ul>
                        </div>

                        <div>
                            <label class="label">File * (CSV or Excel)</label>
                            <input type="file" wire:model="file" accept=".csv,.txt,.xlsx,.xls" class="input-field">
                            @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="file" class="mt-2 text-sm text-primary-600 dark:text-primary-400">
                                Processing file...
                            </div>
                            <p class="text-xs text-surface-500 dark:text-surface-400 mt-2">
                                <button type="button" wire:click="downloadTemplate" class="text-primary-600 hover:text-primary-800 underline">
                                    Download template
                                </button>
                                to fill in your data, then upload.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-outline">Cancel</a>
                            <button wire:click="parse" class="btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="parse">Preview Import</span>
                                <span wire:loading wire:target="parse">
                                    <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Parsing...
                                </span>
                            </button>
                        </div>
                    </div>
                @elseif($step === 2)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-surface-600 dark:text-surface-400">
                                Found <strong class="text-surface-900 dark:text-white">{{ count($preview) }}</strong> book(s) to import.
                            </p>
                            <span class="text-xs text-surface-500">Scroll horizontally to see all columns</span>
                        </div>

                        <div class="overflow-x-auto table-mobile-cards">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="table-header">#</th>
                                        <th class="table-header">Title</th>
                                        <th class="table-header">ISBN</th>
                                        <th class="table-header">Authors</th>
                                        <th class="table-header">Category</th>
                                        <th class="table-header">Publisher</th>
                                        <th class="table-header">Copies</th>
                                        <th class="table-header">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($preview as $row)
                                        <tr class="{{ !empty($row['_errors']) ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                            <td class="table-cell text-surface-500">{{ $row['row'] }}</td>
                                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $row['title'] }}</td>
                                            <td class="table-cell">{{ $row['isbn'] ?: '—' }}</td>
                                            <td class="table-cell">{{ $row['authors'] ?: '—' }}</td>
                                            <td class="table-cell">{{ $row['category'] ?: '—' }}</td>
                                            <td class="table-cell">{{ $row['publisher'] ?: '—' }}</td>
                                            <td class="table-cell">{{ $row['copies_count'] }}</td>
                                            <td class="table-cell">
                                                @if(!empty($row['_errors']))
                                                    <span class="badge-danger" title="{{ implode(', ', $row['_errors']) }}">Error</span>
                                                @else
                                                    <span class="badge-success">Ready</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-100 dark:border-surface-700">
                            <button wire:click="resetUpload" class="btn-outline">Back</button>
                            <button wire:click="import" class="btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="import">
                                    Import {{ count($preview) }} Book(s)
                                </span>
                                <span wire:loading wire:target="import">
                                    <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Importing...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
