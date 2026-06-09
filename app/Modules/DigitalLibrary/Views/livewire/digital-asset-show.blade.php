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
                        @if($asset->file_extension === 'pdf')
                            <button @click="$dispatch('open-modal', { name: 'flipbook-modal' })"
                                    class="btn-primary inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View Online
                            </button>
                        @elseif($asset->allow_download)
                            <a href="{{ Storage::url($asset->file_path) }}" target="_blank"
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
                            <source src="{{ Storage::url($asset->file_path) }}" type="{{ $asset->mime_type }}">
                        </video>
                    @elseif($asset->file_type === 'audio')
                        <audio controls class="w-full" preload="metadata">
                            <source src="{{ Storage::url($asset->file_path) }}" type="{{ $asset->mime_type }}">
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

@if($asset->file_extension === 'pdf')
    {{-- 3D Flip Book Modal --}}
    <div x-data="flipBook('{{ asset('storage/' . $asset->file_path) }}')"
         x-on:open-modal.window="if ($event.detail.name === 'flipbook-modal') open()"
         x-on:keydown.escape.window="close()"
         x-on:keydown.left.prevent="prevPage()"
         x-on:keydown.right.prevent="nextPage()"
         x-show="show"
         x-cloak
          class="fixed inset-0 z-[100] flex flex-col bg-surface-900"
         style="display: none;">
        {{-- Toolbar --}}
        <div class="flex items-center justify-between px-4 py-2 bg-surface-900 border-b border-surface-800 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="close()" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors" title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <span class="text-sm font-medium text-white truncate max-w-md">{{ $asset->title }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="zoomOut" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors" title="Zoom Out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </button>
                <span class="text-xs text-surface-400 w-12 text-center" x-text="Math.round(zoom * 100) + '%'"></span>
                <button @click="zoomIn" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors" title="Zoom In">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
                <button @click="zoom = 1" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors text-xs" title="Reset Zoom">1:1</button>
                <button @click="toggleView()" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors" x-bind:title="singlePage ? 'Two-page view' : 'Single-page view'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!singlePage" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        <path x-show="singlePage" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="w-px h-5 bg-surface-700 mx-1"></div>
                <button @click="toggleFullscreen" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors" title="Fullscreen">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Reader area --}}
        <div class="flex-1 flex items-start justify-center bg-surface-900 overflow-auto relative" x-ref="readerContainer">
            {{-- Loading --}}
            <div x-show="loading" x-cloak
                 class="absolute inset-0 bg-surface-900/90 flex items-center justify-center z-50"
                 style="display: none;">
                <div class="text-center">
                    <svg class="animate-spin w-10 h-10 text-primary-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <p class="text-sm text-surface-400" x-text="loadingText"></p>
                </div>
            </div>
            <div x-show="error" class="absolute inset-0 bg-surface-900 flex items-center justify-center z-40">
                <div class="text-center">
                    <svg class="w-12 h-12 text-accent-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-surface-400" x-text="error"></p>
                </div>
            </div>

            {{-- Single page view --}}
            <div x-show="singlePage" class="flex items-center justify-center min-h-full w-full p-4">
                <canvas x-ref="singleCanvas"></canvas>
            </div>

            {{-- Book spread view --}}
            <div x-show="!singlePage" class="flex items-center justify-center min-h-full w-full p-4">
                <div class="relative flex" x-ref="spread">
                    {{-- Left page --}}
                    <div class="relative overflow-hidden cursor-pointer"
                         @click="prevPage()"
                         :style="pageStyle">
                        <canvas x-ref="leftCanvas"></canvas>
                        <div class="absolute inset-0 hover:bg-gradient-to-r hover:from-black/10 hover:to-transparent transition-colors"></div>
                    </div>

                    {{-- Spine --}}
                    <div class="w-2 bg-gradient-to-r from-surface-600 via-surface-500 to-surface-600 rounded-full mx-1 self-stretch"></div>

                    {{-- Right page (flipping mechanism) --}}
                    <div class="relative overflow-hidden"
                         x-ref="rightZone"
                         :style="pageStyle">
                        {{-- The flipping card --}}
                        <div class="flip-card w-full h-full"
                             :class="{ 'flipping-forward': flipForward }"
                             @click="nextPage()">
                            <div class="flip-card-inner">
                                <div class="flip-front">
                                    <canvas x-ref="rightCanvas"></canvas>
                                </div>
                                <div class="flip-back">
                                    <canvas x-ref="backCanvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="absolute inset-0 hover:bg-gradient-to-l hover:from-black/10 hover:to-transparent transition-colors pointer-events-none"></div>
                    </div>
                </div>
            </div>

            {{-- Page turn arrows --}}
            <button @click="prevPage()"
                    class="fixed left-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-surface-900/80 text-surface-400 hover:text-white hover:bg-surface-800 transition-all opacity-0 hover:opacity-100 z-10"
                    :class="{ 'hidden': !show }">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button @click="nextPage()"
                    class="fixed right-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-surface-900/80 text-surface-400 hover:text-white hover:bg-surface-800 transition-all opacity-0 hover:opacity-100 z-10"
                    :class="{ 'hidden': !show }">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-4 py-2 bg-surface-900 border-t border-surface-800 shrink-0">
            <div class="flex items-center gap-3">
                <button @click="prevPage()" :disabled="singlePage ? currentPage <= 1 : currentPage - 2 < 1"
                        class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <span class="text-sm text-surface-400">
                    <span x-show="!singlePage">Page <span x-text="currentPage"></span><span class="text-surface-600"> – </span><span x-text="Math.min(currentPage + 1, totalPages)"></span></span>
                    <span x-show="singlePage">Page <span x-text="currentPage"></span></span>
                    <span> of <span x-text="totalPages"></span></span>
                </span>
                <button @click="nextPage()" :disabled="singlePage ? currentPage >= totalPages : currentPage + 1 >= totalPages"
                        class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-800 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-3 flex-1 max-w-md mx-4">
                <div class="flex-1 bg-surface-800 rounded-full h-1.5">
                    <div class="bg-primary-500 rounded-full h-1.5 transition-all duration-300"
                         :style="{ width: progress + '%' }"></div>
                </div>
                <span class="text-xs text-surface-400 w-10 text-right" x-text="progress + '%'"></span>
            </div>
            <div class="text-xs text-surface-500">{{ strtoupper($asset->file_extension ?? $asset->file_type) }} &middot; {{ round($asset->file_size / 1024 / 1024, 1) }} MB</div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        function flipBook(fileUrl) {
            let _doc = null;

            return {
                pdfDoc: null,
                currentPage: 1,
                totalPages: 0,
                zoom: 1,
                show: false,
                loading: false,
                loadingText: '',
                error: null,
                flipForward: false,
                flipping: false,
                progress: 0,
                tempCanvas: null,
                pageW: 400,
                pageH: 560,
                singlePage: false,

                async open() {
                    this.show = true;
                    if (!_doc) {
                        this.loading = true;
                        this.loadingText = 'Loading document...';
                        await this.$nextTick();
                        try {
                            const loadingTask = pdfjsLib.getDocument(fileUrl);
                            const pdf = await loadingTask.promise;
                            _doc = pdf;
                            this.pdfDoc = pdf;
                            this.totalPages = pdf.numPages;
                            this.progress = 0;
                            await this.reRender(true);
                            this.loading = false;
                        } catch (err) {
                            this.error = 'Failed to load document.';
                            this.loading = false;
                            console.error('FlipBook error:', err);
                        }
                    }
                },
                close() { this.show = false; },

                init() {
                    this.$watch('zoom', () => {
                        if (_doc) this.reRender(true);
                    });
                },

                toggleView() {
                    this.singlePage = !this.singlePage;
                    if (_doc) this.reRender(true);
                },

                async reRender(showLoading) {
                    if (!_doc) return;
                    if (showLoading) {
                        this.loading = true;
                        this.loadingText = 'Rendering...';
                    }
                    this.error = null;
                    try {
                        if (this.singlePage) {
                            const canvas = this.$refs.singleCanvas;
                            // singleCanvas ref already validated in condition
                            if (canvas) {
                                await this.renderPage(canvas, this.currentPage, true);
                            } else {
                                this.error = 'Canvas not found.';
                            }
                        } else {
                            await this.renderSpread(this.currentPage);
                        }
                    } catch (err) {
                        this.error = 'Failed to render page.';
                        console.error('reRender error:', err);
                    } finally {
                        this.loading = false;
                    }
                },

                setPageSize(w, h) {
                    this.pageW = w;
                    this.pageH = h;
                },

                get pageStyle() {
                    return { width: this.pageW + 'px', height: this.pageH + 'px' };
                },

                async renderPage(canvasRef, pageNum, setSize) {
                    if (!_doc || pageNum < 1 || pageNum > this.totalPages) {
                        if (canvasRef) {
                            const ctx = canvasRef.getContext('2d');
                            ctx.clearRect(0, 0, canvasRef.width, canvasRef.height);
                        }
                        return;
                    }
                    const page = await _doc.getPage(pageNum);
                    const container = this.$refs.readerContainer;
                    const maxW = container ? container.clientWidth - 40 : 400;
                    const maxH = container ? container.clientHeight - 20 : 560;
                    const viewport = page.getViewport({ scale: 1 });
                    let scale = this.singlePage
                        ? Math.min(maxH / viewport.height, maxW / viewport.width)
                        : Math.min(maxH / viewport.height, ((maxW - 100) / 2) / viewport.width);
                    scale *= this.zoom;
                    const scaled = page.getViewport({ scale });
                    const offscreen = document.createElement('canvas');
                    offscreen.width = scaled.width;
                    offscreen.height = scaled.height;
                    await page.render({ canvasContext: offscreen.getContext('2d'), viewport: scaled }).promise;
                    const canvas = canvasRef;
                    if (canvas.width !== scaled.width) canvas.width = scaled.width;
                    if (canvas.height !== scaled.height) canvas.height = scaled.height;
                    canvas.getContext('2d').drawImage(offscreen, 0, 0);
                    if (setSize) {
                        this.setPageSize(scaled.width, scaled.height);
                    }
                    return { width: scaled.width, height: scaled.height };
                },

                async renderSpread(startPage) {
                    const results = await Promise.all([
                        this.renderPage(this.$refs.leftCanvas, startPage, true),
                        this.renderPage(this.$refs.rightCanvas, startPage + 1),
                    ]);
                    if (results[0]) {
                        this.setPageSize(results[0].width, results[0].height);
                    }
                },

                async nextPage() {
                    if (this.flipping) return;
                    if (this.singlePage) {
                        if (this.currentPage >= this.totalPages) return;
                        this.currentPage++;
                        await this.reRender();
                        this.updateProgress();
                    } else {
                        if (this.currentPage + 2 > this.totalPages) return;
                        this.flipping = true;
                        this.flipForward = true;

                        const nextLeft = this.currentPage + 2;
                        const nextRight = this.currentPage + 3;

                        await this.renderPage(this.$refs.backCanvas, nextLeft);

                        this.tempCanvas = document.createElement('canvas');
                        if (nextRight <= this.totalPages) {
                            await this.renderPage(this.tempCanvas, nextRight);
                        }

                        await new Promise(r => setTimeout(r, 700));

                        this.currentPage += 2;

                        const leftCtx = this.$refs.leftCanvas.getContext('2d');
                        const backCtx = this.$refs.backCanvas.getContext('2d');
                        const rightCtx = this.$refs.rightCanvas.getContext('2d');
                        leftCtx.clearRect(0, 0, this.$refs.leftCanvas.width, this.$refs.leftCanvas.height);
                        leftCtx.drawImage(this.$refs.backCanvas, 0, 0);
                        rightCtx.clearRect(0, 0, this.$refs.rightCanvas.width, this.$refs.rightCanvas.height);

                        if (this.tempCanvas && nextRight <= this.totalPages) {
                            rightCtx.drawImage(this.tempCanvas, 0, 0);
                        }

                        this.flipForward = false;
                        this.flipping = false;
                        this.updateProgress();
                    }
                },

                async prevPage() {
                    if (this.flipping) return;
                    if (this.singlePage) {
                        if (this.currentPage <= 1) return;
                        this.currentPage--;
                        await this.reRender();
                        this.updateProgress();
                    } else {
                        if (this.currentPage - 2 < 1) return;
                        this.currentPage -= 2;
                        await this.renderSpread(this.currentPage);
                        this.updateProgress();
                    }
                },

                updateProgress() {
                    const pct = this.totalPages > 0 ? Math.round((this.currentPage / this.totalPages) * 100) : 0;
                    this.progress = Math.min(pct, 100);
                    @this.call('updateProgress', this.progress, this.currentPage);
                },

                zoomIn() { this.zoom = Math.min(this.zoom + 0.5, 5); },
                zoomOut() { this.zoom = Math.max(this.zoom - 0.5, 0.25); },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        this.$refs.readerContainer.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .flip-card {
            perspective: 1500px;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .flip-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transform-origin: left center;
            transform-style: preserve-3d;
            transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .flip-card.flipping-forward .flip-card-inner {
            transform: perspective(1500px) rotateY(-180deg);
        }
        .flip-front, .flip-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            overflow: hidden;
        }
        .flip-front {
            z-index: 2;
        }
        .flip-back {
            transform: rotateY(180deg);
            z-index: 1;
        }
    </style>
@endif
</div>
