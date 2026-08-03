@section('title', 'Reader')
<div class="h-screen flex flex-col bg-surface-900 overflow-hidden"
     @if(!$asset->is_external) x-data="reader()" x-init="init('{{ $fileUrl }}')" @endif>
    {{-- Toolbar --}}
    <div class="flex items-center justify-between px-4 py-2 bg-surface-800 border-b border-surface-700 shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('digital-library.show', $asset) }}" wire:navigate class="text-surface-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </a>
            <span class="text-sm font-medium text-white truncate max-w-xs">{{ $asset->title }}</span>
            @if($this->isReadOnly)
                <span class="px-2 py-0.5 text-xs rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30">Read Only</span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if($asset->is_external)
                <a href="{{ $asset->source_url }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-surface-700 text-white hover:bg-surface-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Open in new tab
                </a>
            @else
                {{-- Zoom controls --}}
            <button wire:click="zoomOut" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-700 transition-colors" title="Zoom Out">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </button>
            <span class="text-xs text-surface-400 w-12 text-center">{{ round($zoom * 100) }}%</span>
            <button wire:click="zoomIn" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-700 transition-colors" title="Zoom In">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <button wire:click="zoomReset" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-700 transition-colors text-xs" title="Reset Zoom">1:1</button>

            <div class="w-px h-5 bg-surface-700 mx-1"></div>

            {{-- Page navigation --}}
            <button wire:click="prevPage" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-700 transition-colors" title="Previous Page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="flex items-center gap-1">
                <input type="number" wire:model.lazy="currentPage" wire:change="goToPage($event.target.value)"
                       class="w-12 text-center bg-surface-700 border border-surface-600 rounded text-xs text-white py-1 px-1"
                       min="1" max="{{ max($totalPages, 1) }}">
                <span class="text-xs text-surface-400">/ <span x-text="totalPages || 1"></span></span>
            </div>
            <button wire:click="nextPage" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-700 transition-colors" title="Next Page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div class="w-px h-5 bg-surface-700 mx-1"></div>

            {{-- Progress --}}
            <div class="flex items-center gap-2">
                <div class="w-24 bg-surface-700 rounded-full h-1.5">
                    <div class="bg-primary-500 rounded-full h-1.5 transition-all" style="width: {{ $progress }}%"></div>
                </div>
                <span class="text-xs text-surface-400 w-10">{{ $progress }}%</span>
            </div>

            <div class="w-px h-5 bg-surface-700 mx-1"></div>

            {{-- Fullscreen --}}
            <button @click="toggleFullscreen" class="p-1.5 rounded-lg text-surface-400 hover:text-white hover:bg-surface-700 transition-colors" title="Fullscreen">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
            </button>
            @endif
        </div>
    </div>

    {{-- Reader area --}}
    @if($asset->is_external)
        <div class="flex-1 relative bg-surface-950">
            <iframe src="{{ $asset->source_url }}" class="w-full h-full border-0" title="{{ $asset->title }}"
                    loading="lazy"></iframe>
        </div>
    @else
    <div class="flex-1 flex overflow-hidden relative">
        {{-- Canvas area --}}
        <div class="flex-1 flex items-center justify-center overflow-auto bg-surface-950 relative"
             x-ref="readerContainer"
             @keydown.left.prevent="prevPage"
             @keydown.right.prevent="nextPage"
             tabindex="0"
             wire:ignore>
            <div class="relative" :style="{ transform: 'scale(' + zoom + ')', transformOrigin: 'center center' }">
                <canvas x-ref="pdfCanvas" class="shadow-2xl"></canvas>
            </div>

            {{-- Page turn arrows --}}
            <button @click="prevPage"
                    class="absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-surface-800/80 text-surface-400 hover:text-white hover:bg-surface-700 transition-all opacity-0 hover:opacity-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button @click="nextPage"
                    class="absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-surface-800/80 text-surface-400 hover:text-white hover:bg-surface-700 transition-all opacity-0 hover:opacity-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    {{-- Loading overlay --}}
    @if(!$asset->is_external)
    <div x-show="loading" x-cloak
         class="absolute inset-0 bg-surface-900/80 flex items-center justify-center z-50">
        <div class="text-center">
            <svg class="animate-spin w-10 h-10 text-primary-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <p class="text-sm text-surface-400" x-text="loadingText">Loading document...</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        function reader() {
            return {
                pdfDoc: null,
                currentPage: @entangle('currentPage'),
                totalPages: 0,
                zoom: @entangle('zoom'),
                loading: false,
                loadingText: 'Loading document...',

                init(fileUrl) {
                    this.loading = true;
                    this.loadingText = 'Loading document...';

                    const loadingTask = pdfjsLib.getDocument(fileUrl);
                    loadingTask.promise.then((pdf) => {
                        this.pdfDoc = pdf;
                        this.totalPages = pdf.numPages;
                        this.renderPage(this.currentPage);
                        this.loading = false;

                        @this.set('totalPages', pdf.numPages);
                        @this.updateProgress && @this.updateProgress();
                    }).catch((err) => {
                        this.loadingText = 'Failed to load document.';
                        this.loading = false;
                        console.error('PDF.js error:', err);
                    });

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowLeft') this.prevPage();
                        if (e.key === 'ArrowRight') this.nextPage();
                    });
                },

                renderPage(pageNum) {
                    if (!this.pdfDoc) return;
                    this.loading = true;
                    this.loadingText = `Loading page ${pageNum}...`;

                    this.pdfDoc.getPage(pageNum).then((page) => {
                        const container = this.$refs.readerContainer;
                        const maxWidth = container.clientWidth - 80;
                        const maxHeight = container.clientHeight - 80;

                        const viewport = page.getViewport({ scale: 1.5 });
                        let scale = 1;
                        if (viewport.width > maxWidth) scale = maxWidth / viewport.width;
                        if ((viewport.height * scale) > maxHeight) scale = maxHeight / viewport.height;
                        scale = Math.min(scale, 2);

                        const scaledViewport = page.getViewport({ scale });

                        const canvas = this.$refs.pdfCanvas;
                        canvas.width = scaledViewport.width;
                        canvas.height = scaledViewport.height;

                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: scaledViewport,
                        };

                        page.render(renderContext).promise.then(() => {
                            this.loading = false;
                        });
                    });
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.renderPage(this.currentPage);
                        @this.call('nextPage');
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.renderPage(this.currentPage);
                        @this.call('prevPage');
                    }
                },

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
        @media print {
            @if(!$asset->allow_printing)
                body { display: none !important; }
            @endif
        }
    </style>
    @if(!$asset->allow_printing)
        <script>
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 'P')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    alert('Printing is disabled for this document.');
                }
            });
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });
        </script>
    @endif
    @endif
</div>
