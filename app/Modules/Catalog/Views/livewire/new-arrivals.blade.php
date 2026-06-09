<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">New Arrivals</h1>
            <p class="page-subtitle">Recently added books and resources</p>
        </div>
        <div class="text-sm text-surface-500 dark:text-surface-400">
            <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $recentCount }}</span> added this week
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex gap-2">
            <button wire:click="$set('period', '7')" class="btn-sm {{ $period === '7' ? 'btn-primary' : 'btn-secondary' }}">7 days</button>
            <button wire:click="$set('period', '30')" class="btn-sm {{ $period === '30' ? 'btn-primary' : 'btn-secondary' }}">30 days</button>
            <button wire:click="$set('period', '90')" class="btn-sm {{ $period === '90' ? 'btn-primary' : 'btn-secondary' }}">90 days</button>
        </div>
        <select wire:model.live="categoryId" class="input w-full md:w-64">
            <option value="">All Categories</option>
            @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
            @if($category->children->count())
                @foreach($category->children as $child)
                <option value="{{ $child->id }}">&mdash; {{ $child->name }}</option>
                @endforeach
            @endif
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($books as $book)
        <div class="card hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex gap-4">
                    @if($book->cover_image)
                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-20 h-28 object-cover rounded-lg shrink-0">
                    @else
                    <div class="w-20 h-28 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-surface-900 dark:text-white truncate">
                            <a href="{{ route('catalog.books.show', $book->id) }}" wire:navigate class="hover:text-primary-600 dark:hover:text-primary-400">
                                {{ $book->title }}
                            </a>
                        </h3>
                        @if($book->authors->count())
                        <p class="text-sm text-surface-500 dark:text-surface-400 truncate">
                            {{ $book->authors->pluck('name')->implode(', ') }}
                        </p>
                        @endif
                        <div class="flex items-center gap-2 mt-2 text-xs text-surface-400">
                            <span>{{ $book->created_at->format('M d, Y') }}</span>
                            @if($book->category)
                            <span>&middot;</span>
                            <span class="badge badge-primary">{{ $book->category->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-surface-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-surface-300 dark:text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <p>No new arrivals found for this period.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $books->links() }}
    </div>
</div>
