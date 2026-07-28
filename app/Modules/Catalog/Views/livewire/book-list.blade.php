@section('title', 'Catalog')
<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">Books Catalog</h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Browse, search, and manage library books</p>
            </div>
            @can('create-books')
                <div class="flex items-center gap-2">
                    <a href="{{ route('catalog.books.bulk-upload') }}" wire:navigate class="btn-outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Bulk Upload
                    </a>
                    <a href="{{ route('catalog.books.create') }}" wire:navigate class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Book
                    </a>
                </div>
            @endcan
            <button wire:click="exportCsv" class="btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by title, author, ISBN..."
                            class="input-field pl-9">
                    </div>
                </div>
                <div>
                    <select wire:model.live="categoryId" class="input-field">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @foreach($category->children as $child)
                                <option value="{{ $child->id }}">&nbsp;&nbsp;{{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="authorId" class="input-field">
                        <option value="">All Authors</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mt-3">
                <div class="flex-1 min-w-[150px]">
                    <select wire:model.live="publisherId" class="input-field">
                        <option value="">All Publishers</option>
                        @foreach($publishers as $publisher)
                            <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <select wire:model.live="subjectId" class="input-field">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="sort" class="input-field">
                        <option value="title">Sort: Title</option>
                        <option value="publication_year">Sort: Year</option>
                        <option value="created_at">Sort: Newest</option>
                    </select>
                </div>
                <button wire:click="clearFilters" class="btn-ghost text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($books as $book)
            <a href="{{ route('catalog.books.show', $book->id) }}" wire:navigate class="card group hover:shadow-soft-lg transition-all duration-200 hover:-translate-y-0.5">
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-20 rounded-xl bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center shrink-0 overflow-hidden">
                            @if($book->cover_image)
                                <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-primary-500 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-surface-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                                {{ $book->title }}
                            </h3>
                            @if($book->subtitle)
                                <p class="text-xs text-surface-500 dark:text-surface-400 truncate">{{ $book->subtitle }}</p>
                            @endif
                            <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                                {{ $book->authors->pluck('name')->implode(', ') ?: 'Unknown Author' }}
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="badge-info">{{ $book->category?->name ?? 'Uncategorized' }}</span>
                                <span class="badge-neutral">{{ $book->language }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-surface-100 dark:border-surface-700">
                                <span class="text-xs text-surface-500 dark:text-surface-400">
                                    {{ $book->total_copies }} copy(ies)
                                </span>
                                <div class="flex items-center gap-2">
                                    @if($book->digitalAssets()->exists())
                                        <span class="badge-success text-[10px]">eBook</span>
                                    @endif
                                    <span class="text-xs font-medium {{ $book->available_count > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $book->available_count }} available
                                    </span>
                                </div>
                            </div>
                            @can('delete-books')
                                <div class="mt-2 pt-2 border-t border-surface-100 dark:border-surface-700">
                                    <button wire:click.stop="delete({{ $book->id }})" wire:confirm="Permanently delete this book? This action cannot be undone." class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <div class="card p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">No Books Found</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Try adjusting your search or filter criteria.</p>
                    <button wire:click="clearFilters" class="btn-primary">Clear Filters</button>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $books->links() }}
    </div>
</div>
