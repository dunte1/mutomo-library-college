@section('title', 'Manage Assignments')
<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">Reading Assignments &amp; Recommendations</h1>
        <button wire:click="$set('showForm', true)" class="btn-primary flex items-center justify-center gap-2 w-full sm:w-auto">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Assignment
        </button>
    </div>

    @if(session('message'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 sm:p-4 text-sm sm:text-base text-green-700 dark:text-green-300">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 sm:p-4 text-sm sm:text-base text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Create/Edit Form --}}
    @if($showForm)
    <div class="card p-4 sm:p-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ $editing ? 'Edit Assignment' : 'New Reading Assignment' }}
        </h2>
        <form wire:submit="{{ $editing ? 'update' : 'create' }}" class="space-y-4">
            {{-- Assign To --}}
            @if(!$editing)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign To</label>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 mb-3">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="assignTo" value="individual">
                        Individual Student
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="assignTo" value="program">
                        Entire Program
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="assignTo" value="department">
                        Entire Department
                    </label>
                </div>

                @if($assignTo === 'individual')
                    <select wire:model="student_id" class="input w-full">
                        <option value="">Select student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @elseif($assignTo === 'program')
                    <select wire:model="program_id" class="input w-full">
                        <option value="">Select program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                    @error('program_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @elseif($assignTo === 'department')
                    <select wire:model="department_id" class="input w-full">
                        <option value="">Select department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @endif
            </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student *</label>
                    <select wire:model="student_id" class="input w-full">
                        <option value="">Select student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                    <select wire:model="type" class="input w-full">
                        <option value="assignment">Assignment</option>
                        <option value="recommendation">Recommendation</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                    <input wire:model="due_date" type="datetime-local" class="input w-full">
                    @error('due_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
                    <input wire:model="title" type="text" class="input w-full" placeholder="e.g. Read Chapter 5 - Data Structures">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="input w-full" placeholder="Additional details about this assignment..."></textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Book (optional)</label>
                    <select wire:model="book_id" class="input w-full">
                        <option value="">No book selected</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}">{{ $book->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Digital Asset (optional)</label>
                    <select wire:model="digital_asset_id" class="input w-full">
                        <option value="">No digital asset selected</option>
                        @foreach($digitalAssets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <input wire:model="notes" type="text" class="input w-full" placeholder="Private notes for yourself">
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 pt-2">
                <button type="submit" class="btn-primary w-full sm:w-auto">
                    {{ $editing ? 'Update' : ($assignTo === 'individual' ? 'Create' : 'Assign to Group') }}
                </button>
                <button type="button" wire:click="$set('showForm', false)" class="btn-secondary w-full sm:w-auto">
                    Cancel
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Progress Modal --}}
    @if($progressAssignment)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center" wire:click.self="closeProgress" wire:poll.5s>
        <div class="bg-white dark:bg-gray-900 rounded-t-2xl sm:rounded-xl shadow-2xl max-w-3xl w-full max-h-[85vh] sm:max-h-[80vh] overflow-y-auto mt-auto sm:mt-0">
            <div class="sticky top-0 bg-white dark:bg-gray-900 z-10 p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Progress: {{ $progressAssignment->title }}</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">
                        Assigned {{ $progressAssignment->created_at->diffForHumans() }}
                        @if($progressAssignment->program) &middot; Program: {{ $progressAssignment->program->name }} @endif
                        @if($progressAssignment->department) &middot; Department: {{ $progressAssignment->department->name }} @endif
                    </p>
                </div>
                <button wire:click="closeProgress" class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 -m-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4 sm:p-6">
                @if($progressStats->isEmpty())
                    <p class="text-gray-500 text-center py-4">No progress data available.</p>
                @else
                    {{-- Mobile card list --}}
                    <div class="sm:hidden space-y-3">
                        @foreach($progressStats as $stat)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $stat['student_name'] }}</span>
                                    <span class="badge shrink-0 ml-2
                                        @if($stat['status'] === 'pending') badge-warning
                                        @elseif($stat['status'] === 'in_progress') badge-info
                                        @elseif($stat['status'] === 'completed') badge-success
                                        @elseif($stat['status'] === 'overdue') badge-danger
                                        @else badge-neutral
                                        @endif">
                                        {{ str_replace('_', ' ', ucfirst($stat['status'])) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <div>
                                        <span class="block text-gray-400 dark:text-gray-500">Viewed</span>
                                        {{ $stat['viewed_at'] ? $stat['viewed_at']->format('M d, Y h:i A') : 'Not viewed' }}
                                    </div>
                                    <div>
                                        <span class="block text-gray-400 dark:text-gray-500">Completed</span>
                                        {{ $stat['completed_at'] ? $stat['completed_at']->format('M d, Y h:i A') : '-' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Desktop table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Student</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Status</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Viewed</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Completed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($progressStats as $stat)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 text-gray-900 dark:text-white font-medium whitespace-nowrap">{{ $stat['student_name'] }}</td>
                                        <td class="px-4 py-3">
                                            <span class="badge whitespace-nowrap
                                                @if($stat['status'] === 'pending') badge-warning
                                                @elseif($stat['status'] === 'in_progress') badge-info
                                                @elseif($stat['status'] === 'completed') badge-success
                                                @elseif($stat['status'] === 'overdue') badge-danger
                                                @else badge-neutral
                                                @endif">
                                                {{ str_replace('_', ' ', ucfirst($stat['status'])) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $stat['viewed_at'] ? $stat['viewed_at']->format('M d, Y h:i A') : 'Not viewed' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $stat['completed_at'] ? $stat['completed_at']->format('M d, Y h:i A') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @php
                        $total = $progressStats->count();
                        $viewed = $progressStats->where('viewed_at', '!=', null)->count();
                        $completed = $progressStats->where('status', 'completed')->count();
                    @endphp
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-center text-sm">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                            <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $total }}</div>
                            <div class="text-gray-500 dark:text-gray-400">Total Students</div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                            <div class="text-xl sm:text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $viewed }}</div>
                            <div class="text-blue-600 dark:text-blue-400">Viewed</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                            <div class="text-xl sm:text-2xl font-bold text-green-700 dark:text-green-300">{{ $completed }}</div>
                            <div class="text-green-600 dark:text-green-400">Completed</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
        <select wire:model.live="typeFilter" class="input w-full sm:w-auto text-sm">
            <option value="">All Types</option>
            <option value="assignment">Assignments</option>
            <option value="recommendation">Recommendations</option>
        </select>
        <select wire:model.live="statusFilter" class="input w-full sm:w-auto text-sm">
            <option value="">All Statuses</option>
            @foreach(\App\Modules\Assignments\Models\ReadingAssignment::statusOptions() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <input wire:model.live.debounce.300ms="search" type="text" class="input w-full text-sm" placeholder="Search by title or student...">
    </div>

    {{-- Assignments List --}}
    <div class="card">
        {{-- Desktop table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm min-w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Title &amp; Student</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Type</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Due Date</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($assignments as $assignment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white truncate">{{ $assignment->title }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $assignment->student->name }}
                                            @if($assignment->program) &middot; {{ $assignment->program->name }} @endif
                                            @if($assignment->department) &middot; {{ $assignment->department->name }} @endif
                                        </div>
                                        @if($assignment->book || $assignment->digitalAsset)
                                            <div class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">
                                                @if($assignment->book) Book: {{ $assignment->book->title }} @endif
                                                @if($assignment->book && $assignment->digitalAsset) &middot; @endif
                                                @if($assignment->digitalAsset) Digital: {{ $assignment->digitalAsset->title }} @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="shrink-0 text-xs text-gray-400 dark:text-gray-500 pt-0.5">
                                        @if($assignment->viewed_at)
                                            <span title="Viewed {{ $assignment->viewed_at->format('M d, Y h:i A') }}">&#10003;</span>
                                        @else
                                            <span title="Not viewed yet">&ndash;</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="badge whitespace-nowrap {{ $assignment->type === 'assignment' ? 'badge-info' : 'badge-warning' }}">
                                    {{ ucfirst($assignment->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="badge whitespace-nowrap
                                    @if($assignment->status === 'pending') badge-warning
                                    @elseif($assignment->status === 'in_progress') badge-info
                                    @elseif($assignment->status === 'completed') badge-success
                                    @elseif($assignment->status === 'overdue') badge-danger
                                    @else badge-neutral
                                    @endif">
                                    {{ str_replace('_', ' ', ucfirst($assignment->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap text-xs">
                                {{ $assignment->due_date ? $assignment->due_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <button wire:click="showProgress({{ $assignment->id }})" title="View progress" class="p-1.5 rounded-lg text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 dark:text-primary-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="edit({{ $assignment->id }})" title="Edit" class="p-1.5 rounded-lg text-secondary-600 hover:bg-secondary-50 dark:hover:bg-secondary-900/20 dark:text-secondary-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="delete({{ $assignment->id }})" wire:confirm="Are you sure?" title="Delete" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 dark:text-red-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No assignments found. Create your first one!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($assignments as $assignment)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 dark:text-white truncate">{{ $assignment->title }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $assignment->student->name }}
                                @if($assignment->program) &middot; {{ $assignment->program->name }} @endif
                                @if($assignment->department) &middot; {{ $assignment->department->name }} @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($assignment->viewed_at)
                                <span class="text-xs text-green-600 dark:text-green-400" title="Viewed {{ $assignment->viewed_at->format('M d, Y h:i A') }}">&#10003;</span>
                            @else
                                <span class="text-xs text-gray-400" title="Not viewed">&ndash;</span>
                            @endif
                            <span class="badge whitespace-nowrap {{ $assignment->type === 'assignment' ? 'badge-info' : 'badge-warning' }}">
                                {{ substr($assignment->type, 0, 4) }}
                            </span>
                        </div>
                    </div>

                    @if($assignment->book || $assignment->digitalAsset)
                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                            @if($assignment->book)
                                <span>Book: {{ $assignment->book->title }}</span>
                            @endif
                            @if($assignment->digitalAsset)
                                <span>Digital: {{ $assignment->digitalAsset->title }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span class="badge whitespace-nowrap
                            @if($assignment->status === 'pending') badge-warning
                            @elseif($assignment->status === 'in_progress') badge-info
                            @elseif($assignment->status === 'completed') badge-success
                            @elseif($assignment->status === 'overdue') badge-danger
                            @else badge-neutral
                            @endif">
                            {{ str_replace('_', ' ', ucfirst($assignment->status)) }}
                        </span>
                        <span>Due: {{ $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'No due date' }}</span>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button wire:click="showProgress({{ $assignment->id }})" class="btn-sm btn-primary flex-1 text-center">Progress</button>
                        <button wire:click="edit({{ $assignment->id }})" class="btn-sm btn-secondary flex-1 text-center">Edit</button>
                        <button wire:click="delete({{ $assignment->id }})" wire:confirm="Are you sure?" class="btn-sm btn-danger flex-1 text-center">Delete</button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No assignments found. Create your first one!
                </div>
            @endforelse
        </div>

        @if($assignments->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
</div>
