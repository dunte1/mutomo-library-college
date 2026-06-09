@section('title', 'Testimonials')
<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Testimonials</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Manage landing page testimonials with approval workflow</p>
            </div>
            <a href="{{ route('settings.testimonials.create') }}" wire:navigate class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Testimonial
            </a>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search testimonials..."
                        class="input-field pl-9">
                </div>
                <select wire:model.live="filterStatus" class="input-field w-44">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="overflow-x-auto table-mobile-cards">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="table-header w-16">Order</th>
                        <th class="table-header">Author</th>
                        <th class="table-header">Role</th>
                        <th class="table-header">Content</th>
                        <th class="table-header">Rating</th>
                        <th class="table-header">Status</th>
                        <th class="table-header w-48">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $testimonial)
                        <tr>
                            <td class="table-cell">
                                <div class="flex items-center gap-1">
                                    <button wire:click="moveUp({{ $testimonial->id }})" class="p-1 hover:text-primary-600 transition-colors" title="Move up">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <span class="text-sm font-mono text-surface-500 w-6 text-center">{{ $testimonial->sort_order }}</span>
                                    <button wire:click="moveDown({{ $testimonial->id }})" class="p-1 hover:text-primary-600 transition-colors" title="Move down">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="table-cell font-medium text-surface-900 dark:text-white">{{ $testimonial->author_name }}</td>
                            <td class="table-cell">{{ $testimonial->author_role ?? '—' }}</td>
                            <td class="table-cell max-w-sm truncate">{{ $testimonial->content }}</td>
                            <td class="table-cell">
                                @if($testimonial->rating)
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-surface-300 dark:text-surface-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                @else
                                    <span class="text-surface-300 dark:text-surface-600">—</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                @if($testimonial->status === 'approved')
                                    <span class="badge-success">Approved</span>
                                @elseif($testimonial->status === 'rejected')
                                    <span class="badge-danger">Rejected</span>
                                @else
                                    <span class="badge-warning">Pending</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    @if($testimonial->status === 'pending')
                                        <button wire:click="approve({{ $testimonial->id }})" class="btn-sm btn-success" title="Approve">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button wire:click="reject({{ $testimonial->id }})" class="btn-sm btn-danger" title="Reject">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                    <a href="{{ route('settings.testimonials.edit', $testimonial->id) }}" wire:navigate class="btn-sm btn-outline">Edit</a>
                                    <button wire:click="delete({{ $testimonial->id }})" wire:confirm="Delete this testimonial?" class="btn-sm btn-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-cell text-center text-surface-400 py-12">No testimonials found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($testimonials->hasPages())
            <div class="p-4 border-t border-surface-100 dark:border-surface-700">
                {{ $testimonials->links() }}
            </div>
        @endif
    </div>
</div>
