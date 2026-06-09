<div>
    {{-- Already imported banner --}}
    @if($completed)
        <div class="card">
            <div class="card-body text-center py-10">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Import Completed</h3>
                <p class="text-surface-500 dark:text-surface-400 mb-2">
                    <span class="font-medium text-success-600 dark:text-success-400">{{ $successCount }} member(s)</span> created successfully
                    @if($errorCount > 0)
                        , <span class="font-medium text-danger-600 dark:text-danger-400">{{ $errorCount }} error(s)</span> encountered
                    @endif
                </p>

                @if(!empty($importErrors))
                <div class="mt-6 max-w-2xl mx-auto text-left">
                    <h4 class="text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">Error Details</h4>
                    <div class="bg-danger-50 dark:bg-danger-900/10 rounded-lg border border-danger-200 dark:border-danger-800 max-h-48 overflow-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-danger-100 dark:bg-danger-900/30 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-danger-700 dark:text-danger-300">Row</th>
                                    <th class="px-3 py-2 text-left font-medium text-danger-700 dark:text-danger-300">Name</th>
                                    <th class="px-3 py-2 text-left font-medium text-danger-700 dark:text-danger-300">Email</th>
                                    <th class="px-3 py-2 text-left font-medium text-danger-700 dark:text-danger-300">Error</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-danger-100 dark:divide-danger-900/20">
                                @foreach($importErrors as $error)
                                <tr class="hover:bg-danger-50 dark:hover:bg-danger-900/20">
                                    <td class="px-3 py-2 text-danger-600 dark:text-danger-400">{{ $error['row'] }}</td>
                                    <td class="px-3 py-2 text-surface-700 dark:text-surface-300">{{ $error['name'] }}</td>
                                    <td class="px-3 py-2 text-surface-600 dark:text-surface-400">{{ $error['email'] }}</td>
                                    <td class="px-3 py-2 text-danger-600 dark:text-danger-400">{{ $error['errors'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <div class="flex items-center justify-center gap-3 mt-6">
                    <button type="button" wire:click="$set('completed', false)" class="btn-primary btn-sm">
                        Import Another File
                    </button>
                    <a href="{{ route('members.index') }}" wire:navigate class="btn-outline btn-sm">
                        View Members
                    </a>
                </div>
            </div>
        </div>
    @else
    <div class="space-y-6">
        {{-- Upload Section --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Upload CSV File</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-start gap-3 p-4 bg-info-50 dark:bg-info-900/20 rounded-lg border border-info-200 dark:border-info-800">
                    <svg class="w-5 h-5 text-info-600 dark:text-info-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-info-800 dark:text-info-200">CSV Format Requirements</p>
                        <ul class="text-xs text-info-600 dark:text-info-300 mt-1 space-y-0.5 list-disc list-inside">
                            <li>Required columns: <strong>first_name, last_name, email</strong></li>
                            <li>Optional columns: phone, admission_number, id_number, gender, date_of_birth, year_of_study, department, program, membership_type, address</li>
                            <li>Valid membership types: student, teacher, staff, external (defaults to student)</li>
                            <li>Each row creates a member <strong>and</strong> an auto-generated login account</li>
                            <li>Max file size: 2 MB</li>
                        </ul>
                    </div>
                </div>

                <label class="upload-zone cursor-pointer @error('csvFile') border-danger-500 @enderror">
                    <input type="file" wire:model="csvFile" accept=".csv,.txt" class="upload-zone-input">
                    <div class="text-center">
                        @if($csvFile)
                            <svg class="w-10 h-10 mx-auto mb-2 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $csvFile->getClientOriginalName() }}</p>
                            <p class="text-xs text-surface-500 mt-0.5">{{ number_format($csvFile->getSize() / 1024, 1) }} KB</p>
                        @else
                            <svg class="w-10 h-10 mx-auto mb-2 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm font-medium text-surface-700 dark:text-surface-300">Drop your CSV file here, or click to browse</p>
                            <p class="text-xs text-surface-500 mt-1">CSV files only · Max 2 MB</p>
                        @endif
                    </div>
                </label>
                @error('csvFile') <p class="text-danger-500 text-xs mt-1">{{ $message }}</p> @enderror

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('members.bulk.template') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium inline-flex items-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download CSV Template
                    </a>

                    @if($csvFile && !$preview)
                        <button type="button" wire:click="parse" wire:loading.attr="disabled"
                                class="btn-primary btn-sm">
                            <span wire:loading.remove wire:target="parse">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Preview & Validate
                                </span>
                            </span>
                            <span wire:loading wire:target="parse" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Parsing...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Preview Section --}}
        @if($preview)
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Preview</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-surface-500">
                            {{ count($parsedRows) }} valid row(s)
                            @if(count($importErrors) > 0)
                                · <span class="text-danger-500">{{ count($importErrors) }} error(s)</span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(!empty($parsedRows))
                    <div class="overflow-x-auto max-h-80 overflow-y-auto table-mobile-cards">
                        <table class="w-full text-sm">
                            <thead class="bg-surface-50 dark:bg-surface-800/50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Admission</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">Phone</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                                @foreach($parsedRows as $index => $row)
                                <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors">
                                    <td class="px-4 py-2.5 text-surface-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2.5 font-medium text-surface-900 dark:text-white">{{ $row['first_name'] }} {{ $row['last_name'] }}</td>
                                    <td class="px-4 py-2.5 text-surface-600 dark:text-surface-400">{{ $row['email'] }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $row['membership_type'] === 'student' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                            {{ $row['membership_type'] === 'teacher' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                            {{ $row['membership_type'] === 'staff' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                            {{ $row['membership_type'] === 'external' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}">
                                            {{ ucfirst($row['membership_type']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-surface-600 dark:text-surface-400">{{ $row['admission_number'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-surface-600 dark:text-surface-400">{{ $row['phone'] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Import Errors --}}
                    @if(!empty($importErrors))
                    <div class="border-t border-surface-200 dark:border-surface-700">
                        <div class="px-4 py-3 bg-danger-50 dark:bg-danger-900/10">
                            <p class="text-xs font-medium text-danger-600 dark:text-danger-400 mb-2">
                                <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ count($importErrors) }} row(s) with errors — will be skipped
                            </p>
                            <div class="max-h-32 overflow-y-auto space-y-1">
                                @foreach($importErrors as $error)
                                <p class="text-xs text-danger-600 dark:text-danger-400">
                                    <strong>Row {{ $error['row'] }}</strong> ({{ $error['name'] }}): {{ $error['errors'] }}
                                </p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                @if(!empty($parsedRows))
                <div class="card-footer flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('preview', false)" class="btn-outline btn-sm">
                        Cancel
                    </button>
                    <button type="button" wire:click="import" wire:loading.attr="disabled"
                            class="btn-primary btn-sm">
                        <span wire:loading.remove wire:target="import">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import {{ count($parsedRows) }} Member(s)
                            </span>
                        </span>
                        <span wire:loading wire:target="import" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Importing {{ sprintf('%d / %d', $successCount, count($importErrors) + count($parsedRows)) }}...
                        </span>
                    </button>
                </div>
                @endif
            </div>
        @endif
    </div>
    @endif
</div>
