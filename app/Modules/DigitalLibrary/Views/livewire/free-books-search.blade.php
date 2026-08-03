@section('title', 'Free Online Books')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Free Online Books</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Search public-domain books you can read online for free, courtesy of Project Gutenberg and Google Books.
            </p>
        </div>
        <a href="{{ route('digital-library.index') }}" wire:navigate class="btn-secondary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Library
        </a>
    </div>

    <div class="card p-4">
        <form wire:submit="search" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Search free books</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model="query" placeholder="e.g. nursing, first aid, anatomy..."
                           class="input-field w-full pl-9">
                </div>
                @error('query') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="sm:w-56">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Source</label>
                <select wire:model="provider" class="input-field w-full">
                    <option value="gutenberg">Project Gutenberg</option>
                    <option value="google_books">Google Books</option>
                </select>
            </div>
            <div class="sm:self-end">
                <button type="submit" class="btn-primary w-full sm:w-auto inline-flex items-center justify-center gap-2" :disabled="searching" wire:loading.class="opacity-60">
                    <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <svg wire:loading class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Search
                </button>
            </div>
        </form>
        @if($hasSearched && !$searching)
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                {{ count($results) }} result(s) for "{{ $query }}" ·
                <button wire:click="clear" class="text-primary-600 dark:text-primary-400 hover:underline">Clear search</button>
            </p>
        @endif
    </div>

    @if($error)
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg">{{ $error }}</p>
        </div>
    @elseif(!$hasSearched)
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg">Search for a subject, title, or author</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">All books are public domain — free to read for everyone.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($results as $index => $book)
                <div class="card hover:shadow-lg transition-shadow duration-200 flex flex-col">
                    <div class="flex items-start gap-3 mb-3">
                        @if($book['cover_url'])
                            <img src="{{ $book['cover_url'] }}" alt="{{ $book['title'] }} cover"
                                 class="w-16 h-24 object-cover rounded-md shadow bg-gray-100 dark:bg-gray-800" loading="lazy">
                        @else
                            <div class="w-16 h-24 rounded-md bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-500 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-2 leading-snug">{{ $book['title'] }}</h3>
                            @if($book['authors'])
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ implode(', ', $book['authors']) }}</p>
                            @endif
                            <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider
                                {{ $book['provider'] === 'gutenberg' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' }}">
                                {{ $book['provider'] === 'gutenberg' ? 'Project Gutenberg' : 'Google Books' }}
                            </span>
                        </div>
                    </div>
                    @if($book['subjects'])
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach(array_slice($book['subjects'], 0, 3) as $subject)
                                <span class="px-2 py-0.5 text-[10px] rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">{{ $subject }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if($book['publication_year'])
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                            @if($book['provider'] === 'gutenberg')
                                {{ $book['download_count'] }} downloads
                            @else
                                Published {{ $book['publication_year'] }}
                            @endif
                        </p>
                    @endif
                    <div class="flex gap-2 mt-auto pt-1">
                        <a href="{{ $book['read_url'] }}" target="_blank" rel="noopener noreferrer"
                           class="btn-primary text-sm flex-1 text-center">Read Online</a>
                        @can('upload-digital-assets')
                            <button wire:click="saveToLibrary({{ $index }})"
                                    wire:confirm="Add '{{ $book['title'] }}' to your library?"
                                    class="btn-secondary text-sm px-3">Save</button>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
