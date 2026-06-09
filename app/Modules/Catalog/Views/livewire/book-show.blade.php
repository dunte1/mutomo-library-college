@section('title', 'Book Details')
<div>
    {{-- Page header --}}
    <div class="mb-6">
        <div class="flex items-start gap-3">
            <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-ghost p-2 mt-0.5 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl md:text-2xl font-bold text-surface-900 dark:text-white leading-tight">{{ $book->title }}</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1 truncate">
                    {{ $book->authors->pluck('name')->implode(', ') ?: 'Unknown Author' }}
                    @if($book->publication_year) &middot; {{ $book->publication_year }} @endif
                </p>
            </div>
            @can('edit-books')
                <a href="{{ route('catalog.books.edit', $book->id) }}" wire:navigate class="btn-outline btn-sm shrink-0">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            @endcan
        </div>
    </div>

    {{-- ================================================================
         MOBILE BOOK HERO — cover + summary + CTA (visible only on mobile)
         ================================================================ --}}
    <div class="md:hidden mb-4">
        <div class="card overflow-hidden">
            {{-- Cover strip --}}
            <div class="flex gap-4 p-4">
                <div class="w-24 shrink-0">
                    <div class="aspect-[3/4] rounded-xl bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center overflow-hidden shadow-soft">
                        @if($book->cover_image)
                            <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" loading="lazy" class="w-full h-full object-cover">
                        @else
                            <svg class="w-8 h-8 text-primary-300 dark:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        @endif
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    {{-- Quick stats --}}
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="text-center">
                            <p class="text-lg font-bold text-surface-900 dark:text-white">{{ $book->total_copies }}</p>
                            <p class="text-[10px] text-surface-500 uppercase tracking-wide">Copies</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $book->available_count }}</p>
                            <p class="text-[10px] text-surface-500 uppercase tracking-wide">Available</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $book->borrowedCopies()->count() }}</p>
                            <p class="text-[10px] text-surface-500 uppercase tracking-wide">Borrowed</p>
                        </div>
                    </div>
                    {{-- Availability badge --}}
                    @if($book->available_count > 0)
                        <span class="badge-success text-xs">{{ $book->available_count }} available</span>
                    @else
                        <span class="badge-warning text-xs">All copies borrowed</span>
                    @endif
                    {{-- Category + Language + Digital badges --}}
                    <div class="flex flex-wrap gap-1 mt-2">
                        @if($book->category)
                            <span class="badge-info text-[10px]">{{ $book->category->name }}</span>
                        @endif
                        <span class="badge-neutral text-[10px]">{{ strtoupper($book->language) }}</span>
                        @if($book->digitalAssets()->active()->exists())
                            <span class="badge-success text-[10px]">eBook</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Mobile CTA --}}
            @auth
                <div class="px-4 pb-4 space-y-2">
                    @php $digitalAssets = $book->digitalAssets()->active()->get(); @endphp
                    @if($digitalAssets->isNotEmpty())
                        <a href="{{ route('digital-library.show', $digitalAssets->first()) }}" wire:navigate class="btn-outline w-full justify-center text-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Read Online
                        </a>
                    @endif
                    @if($hasActiveReservation)
                        <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-sm text-primary-700 dark:text-primary-300 text-center">
                            You have an active hold on this book.
                        </div>
                    @elseif($canReserve)
                        @if($reserveSuccess)
                            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-sm text-emerald-700 dark:text-emerald-300 mb-2">{{ $reserveSuccess }}</div>
                        @endif
                        @if($reserveError)
                            <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-sm text-red-700 dark:text-red-300 mb-2">{{ $reserveError }}</div>
                        @endif
                        <button wire:click="placeHold" class="btn-primary w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                            Place Hold
                        </button>
                    @elseif($book->available_count > 0)
                        <a href="{{ route('circulation.issue') }}" wire:navigate class="btn-primary w-full justify-center">
                            Borrow This Book
                        </a>
                    @else
                        <div class="p-3 rounded-xl bg-surface-100 dark:bg-surface-800 text-sm text-surface-500 dark:text-surface-400 text-center">
                            All copies are currently borrowed.
                        </div>
                    @endif
                </div>
            @endauth
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Book Details --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Book Details</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">ISBN</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->isbn ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Category</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->category?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Publisher</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->publisher?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Edition</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->edition ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Language</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ strtoupper($book->language) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Pages</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->pages ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Year</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->publication_year ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">Dewey Decimal</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->dewey_decimal ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider">LC Classification</dt>
                            <dd class="text-sm font-medium text-surface-900 dark:text-white mt-1">{{ $book->lc_classification ?? 'N/A' }}</dd>
                        </div>
                    </dl>

                    @if($book->description)
                        <div class="mt-6 pt-6 border-t border-surface-100 dark:border-surface-700">
                            <h4 class="text-sm font-medium text-surface-900 dark:text-white mb-2">Description</h4>
                            <p class="text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{{ $book->description }}</p>
                        </div>
                    @endif

                    @if($book->subjects->isNotEmpty())
                        <div class="mt-6 pt-6 border-t border-surface-100 dark:border-surface-700">
                            <h4 class="text-sm font-medium text-surface-900 dark:text-white mb-2">Subjects</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($book->subjects as $subject)
                                    <span class="badge-info">{{ $subject->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Copies Inventory --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Copies Inventory</h3>
                </div>
                <div class="overflow-x-auto table-mobile-cards">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="table-header">Barcode</th>
                                <th class="table-header">Location</th>
                                <th class="table-header">Status</th>
                                <th class="table-header">Condition</th>
                                <th class="table-header">Acquired</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($copies as $copy)
                                <tr>
                                    <td class="table-cell font-mono text-xs">{{ $copy->barcode }}</td>
                                    <td class="table-cell">{{ $copy->shelf_location ?? 'N/A' }}</td>
                                    <td class="table-cell">
                                        @switch($copy->status)
                                            @case('available') <span class="badge-success">Available</span> @break
                                            @case('borrowed') <span class="badge-warning">Borrowed</span> @break
                                            @case('reserved') <span class="badge-info">Reserved</span> @break
                                            @case('damaged') <span class="badge-danger">Damaged</span> @break
                                            @case('lost') <span class="badge-danger">Lost</span> @break
                                            @default <span class="badge-neutral">{{ $copy->status }}</span>
                                        @endswitch
                                    </td>
                                    <td class="table-cell capitalize">{{ $copy->condition }}</td>
                                    <td class="table-cell">{{ $copy->acquired_at?->format('M d, Y') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="table-cell text-center text-surface-400 py-8">No copies registered</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Reviews --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">
                        Reviews
                        @if($book->reviews_count > 0)
                            <span class="text-sm font-normal text-surface-500">({{ $book->average_rating }} avg &middot; {{ $book->reviews_count }} reviews)</span>
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    @auth
                        <div class="mb-6 p-4 rounded-xl bg-surface-50 dark:bg-surface-800/50">
                            <h4 class="text-sm font-medium text-surface-900 dark:text-white mb-3">
                                {{ $userReview ? 'Update Your Review' : 'Write a Review' }}
                            </h4>
                            <div class="flex items-center gap-2 mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" wire:click="$set('reviewRating', {{ $i }})" class="p-1 touch-action-manipulation">
                                        <svg class="w-7 h-7 {{ $i <= $reviewRating ? 'text-amber-400' : 'text-surface-300 dark:text-surface-600' }}" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            <textarea wire:model="reviewText" rows="3"
                                class="input-field w-full"
                                placeholder="Share your thoughts about this book..."></textarea>
                            <button wire:click="submitReview" class="btn-primary btn-sm mt-2 w-full sm:w-auto justify-center">
                                {{ $userReview ? 'Update Review' : 'Submit Review' }}
                            </button>
                        </div>
                    @endauth

                    @forelse($reviews as $review)
                        <div class="flex items-start gap-3 py-3 border-b border-surface-100 dark:border-surface-700 last:border-0">
                            <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-primary-600 dark:text-primary-400">{{ substr($review->user->name ?? '?', 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-medium text-surface-900 dark:text-white">{{ $review->user->name ?? 'Anonymous' }}</span>
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-surface-200 dark:text-surface-700' }}" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-surface-400">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                @if($review->review)
                                    <p class="text-sm text-surface-600 dark:text-surface-400 mt-1">{{ $review->review }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-surface-500 dark:text-surface-400 text-center py-6">
                            No reviews yet. Be the first to review this book!
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar (desktop only) --}}
        <div class="space-y-6 hidden md:block">
            {{-- Cover --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Cover</h3>
                </div>
                <div class="card-body">
                    <div class="aspect-[3/4] rounded-xl bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center overflow-hidden">
                        @if($book->cover_image)
                            <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" loading="lazy" class="w-full h-full object-cover">
                        @else
                            <svg class="w-16 h-16 text-primary-300 dark:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Digital Availability --}}
            @php $digitalAssets = $book->digitalAssets()->active()->get(); @endphp
            @if($digitalAssets->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Digital Copies</h3>
                </div>
                <div class="card-body space-y-2">
                    @foreach($digitalAssets as $da)
                        <a href="{{ route('digital-library.show', $da) }}" wire:navigate
                           class="flex items-center gap-2 p-2 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors group">
                            <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-900 dark:text-white group-hover:text-primary-600 truncate">{{ $da->title }}</p>
                                <p class="text-xs text-surface-500">{{ strtoupper($da->file_extension ?? $da->file_type) }} &middot; {{ round($da->file_size / 1024 / 1024, 1) }} MB</p>
                            </div>
                            <svg class="w-4 h-4 text-surface-400 group-hover:text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Summary + CTA --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-surface-900 dark:text-white">Summary</h3>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Total Copies</span>
                        <span class="text-sm font-semibold text-surface-900 dark:text-white">{{ $book->total_copies }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Available</span>
                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $book->available_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-surface-500 dark:text-surface-400">Borrowed</span>
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">{{ $book->borrowedCopies()->count() }}</span>
                    </div>
                    @if($digitalAssets->isNotEmpty())
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-surface-500 dark:text-surface-400">Digital Copy</span>
                            <span class="badge-success text-xs">{{ $digitalAssets->count() }} available</span>
                        </div>
                    @endif

                    @auth
                        <div class="pt-3 border-t border-surface-100 dark:border-surface-700 space-y-2">
                            @if($digitalAssets->isNotEmpty())
                                <a href="{{ route('digital-library.show', $digitalAssets->first()) }}" wire:navigate class="btn-outline w-full justify-center text-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                    </svg>
                                    Read Online
                                </a>
                            @endif
                            @if($hasActiveReservation)
                                <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-sm text-primary-700 dark:text-primary-300">
                                    You have an active hold on this book.
                                </div>
                            @elseif($canReserve)
                                @if($reserveSuccess)
                                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-sm text-emerald-700 dark:text-emerald-300 mb-2">{{ $reserveSuccess }}</div>
                                @endif
                                @if($reserveError)
                                    <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-sm text-red-700 dark:text-red-300 mb-2">{{ $reserveError }}</div>
                                @endif
                                <button wire:click="placeHold" class="btn-primary w-full justify-center text-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    </svg>
                                    Place Hold
                                </button>
                            @elseif($book->available_count > 0)
                                <a href="{{ route('circulation.issue') }}" wire:navigate class="btn-primary w-full justify-center text-sm">
                                    Borrow This Book
                                </a>
                            @else
                                <div class="p-3 rounded-xl bg-surface-100 dark:bg-surface-800 text-sm text-surface-500 dark:text-surface-400 text-center">
                                    All copies are currently borrowed. Place a hold to reserve a copy.
                                </div>
                            @endif
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Extra bottom padding on mobile for the bottom nav --}}
    <div class="h-4 md:hidden"></div>
</div>
