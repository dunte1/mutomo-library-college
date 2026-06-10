@section('title', 'Digital Asset')
<div>
    <div class="max-w-5xl mx-auto space-y-6">
    <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('digital-library.index') }}" wire:navigate class="hover:text-primary-600">Digital Library</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-800 dark:text-gray-200 truncate">{{ $asset->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $asset->title }}</h1>
                        @if($asset->author)
                            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $asset->author }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @if($asset->access_level === 'public')
                            <span class="badge badge-success">Public</span>
                        @elseif($asset->access_level === 'restricted')
                            <span class="badge badge-warning">Restricted</span>
                        @else
                            <span class="badge badge-danger">Private</span>
                        @endif
                    </div>
                </div>

                @if($asset->description)
                    <p class="text-gray-700 dark:text-gray-300 mb-4">{{ $asset->description }}</p>
                @endif

                @if($aiSummary)
                    <div class="bg-primary-50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800 rounded-lg p-3">
                        <p class="text-sm text-primary-800 dark:text-primary-200">
                            <span class="font-semibold">AI Summary:</span> {{ $aiSummary }}
                        </p>
                    </div>
                @endif

                <div class="flex flex-wrap gap-3 mt-4">
                    @if(in_array($asset->file_type, ['pdf', 'ebook', 'lecture_note', 'presentation']))
                        @if($asset->allow_download)
                            <a href="{{ route('digital-library.file', $asset) }}" target="_blank"
                               class="btn-primary inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View Online
                            </a>
                        @else
                            <a href="{{ route('digital-library.read', $asset) }}" wire:navigate
                               class="btn-primary inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Read Online
                            </a>
                        @endif
                    @elseif($asset->file_type === 'video')
                        <video controls class="w-full rounded-lg" preload="metadata">
                            <source src="{{ route('digital-library.file', $asset) }}" type="{{ $asset->mime_type }}">
                        </video>
                    @elseif($asset->file_type === 'audio')
                        <audio controls class="w-full" preload="metadata">
                            <source src="{{ route('digital-library.file', $asset) }}" type="{{ $asset->mime_type }}">
                        </audio>
                    @endif

                    @if($asset->allow_download)
                        <button wire:click="download"
                                class="btn-secondary inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download ({{ round($asset->file_size / 1024 / 1024, 1) }} MB)
                        </button>
                    @endif
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Citation</h2>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach(['apa', 'mla', 'chicago'] as $style)
                        <button wire:click="setCitationStyle('{{ $style }}')"
                                class="px-3 py-1 text-sm rounded-md transition-colors duration-150
                                {{ $citationStyle === $style
                                    ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/20 dark:text-primary-300'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ strtoupper($style) }}
                        </button>
                    @endforeach
                </div>

                @if($citation)
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 mb-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $citation }}</p>
                    </div>
                @endif

                <button wire:click="generateCitation" class="text-sm btn-primary">
                    Generate {{ strtoupper($citationStyle) }} Citation
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-4">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Details</h3>
                <dl class="space-y-2 text-sm">
                    @if($asset->publisher)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Publisher</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $asset->publisher }}</dd>
                        </div>
                    @endif
                    @if($asset->publication_year)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Year</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $asset->publication_year }}</dd>
                        </div>
                    @endif
                    @if($asset->isbn)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">ISBN</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $asset->isbn }}</dd>
                        </div>
                    @endif
                    @if($asset->book)
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500">Physical Book</dt>
                            <dd>
                                <a href="{{ route('catalog.books.show', $asset->book) }}" wire:navigate
                                   class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium">
                                    View in Catalog
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Available</dt>
                            <dd class="text-emerald-600 dark:text-emerald-400 font-medium text-sm">
                                {{ $asset->book->available_count }} copy(ies)
                            </dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Type</dt>
                        <dd class="text-gray-800 dark:text-gray-200">{{ strtoupper($asset->file_extension ?? $asset->file_type) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Size</dt>
                        <dd class="text-gray-800 dark:text-gray-200">{{ round($asset->file_size / 1024 / 1024, 1) }} MB</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Views</dt>
                        <dd class="text-gray-800 dark:text-gray-200">{{ $asset->times_viewed }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Downloads</dt>
                        <dd class="text-gray-800 dark:text-gray-200">{{ $asset->times_downloaded }}</dd>
                    </div>
                </dl>
            </div>

            @if($tags)
                <div class="card p-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Tags</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($tags as $tag)
                            <span class="px-2 py-0.5 text-xs rounded-full
                                bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card p-4">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Reading Progress</h3>
                @auth
                    @php
                        $history = \App\Modules\DigitalLibrary\Models\ReadingHistory::where('user_id', auth()->id())
                            ->where('digital_asset_id', $asset->id)
                            ->first();
                    @endphp
                    @if($history)
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Progress</span>
                                <span class="text-gray-800 dark:text-gray-200">{{ $history->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-primary-600 rounded-full h-2 transition-all"
                                     style="width: {{ $history->progress }}%"></div>
                            </div>
                            @if($history->last_page)
                                <p class="text-xs text-gray-500">Last page: {{ $history->last_page }}</p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No reading history yet</p>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</div>


</div>
