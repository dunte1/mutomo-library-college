@section('title', 'Digital Library')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Digital Library</h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('digital-library.free-books') }}" wire:navigate
               class="btn-secondary inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Free Online Books
            </a>
            @can('upload-digital-assets')
                <a href="{{ route('digital-library.upload') }}" wire:navigate
                   class="btn-primary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload Asset
                </a>
            @endcan
        </div>
    </div>

    <div class="card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search assets..."
                       class="input-field w-full">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
                <select wire:model.live="type" class="input-field w-full">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Category</label>
                <select wire:model.live="categoryId" class="input-field w-full">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Access Level</label>
                <select wire:model.live="accessLevel" class="input-field w-full">
                    <option value="">All Levels</option>
                    @foreach(\App\Modules\DigitalLibrary\Models\DigitalAsset::accessLevelOptions() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($assets->isEmpty())
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg">No digital assets found</p>
            @can('upload-digital-assets')
                <a href="{{ route('digital-library.upload') }}" wire:navigate class="btn-primary mt-4 inline-block">Upload First Asset</a>
            @endcan
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($assets as $asset)
                <div class="card hover:shadow-lg transition-shadow duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <div class="p-2 rounded-lg
                            @if($asset->file_type === 'pdf' || $asset->file_type === 'ebook') bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400
                            @elseif($asset->file_type === 'video') bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400
                            @elseif($asset->file_type === 'audio') bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400
                            @else bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                            @endif">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if(in_array($asset->file_type, ['pdf', 'ebook']))
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                @elseif($asset->file_type === 'video')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif($asset->file_type === 'audio')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                @endif
                            </svg>
                        </div>
                        <div class="flex gap-1">
                            @if($asset->access_level === 'public')
                                <span class="badge badge-success">Public</span>
                            @elseif($asset->access_level === 'restricted')
                                <span class="badge badge-warning">Restricted</span>
                            @else
                                <span class="badge badge-danger">Private</span>
                            @endif
                        </div>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2">{{ $asset->title }}</h3>
                    @if($asset->author)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $asset->author }}</p>
                    @endif
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                        {{ strtoupper($asset->file_extension ?? $asset->file_type) }}
                        @if($asset->file_size)
                            · {{ round($asset->file_size / 1024 / 1024, 1) }} MB
                        @endif
                        @if($asset->times_viewed)
                            · {{ $asset->times_viewed }} views
                        @endif
                    </p>
                    @if($asset->book)
                        <a href="{{ route('catalog.books.show', $asset->book) }}" wire:navigate
                           class="text-xs inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 hover:underline mb-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Also in catalog
                        </a>
                    @endif
                    <div class="flex gap-2">
                        <a href="{{ route('digital-library.show', $asset) }}" wire:navigate
                           class="btn-primary text-sm flex-1 text-center">View</a>
                        @if($asset->allow_download)
                             <a href="{{ route('digital-library.show', $asset) }}" wire:navigate
                                class="btn-secondary text-sm px-3 text-center">Download</a>
                         @endif
                        @can('delete-digital-assets')
                            <button wire:click="delete({{ $asset->id }})" wire:confirm="Permanently delete this asset and its files?" class="btn-danger text-sm px-3">Delete</button>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $assets->links() }}
        </div>
    @endif
</div>
