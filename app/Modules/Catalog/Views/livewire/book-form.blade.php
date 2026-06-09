@section('title', 'Book Form')
<div>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-ghost p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white">
                    {{ $isEditing ? 'Edit Book' : 'Add New Book' }}
                </h2>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                    {{ $isEditing ? 'Update book details and information' : 'Add a new book to the library catalog' }}
                </p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Basic Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label">Title *</label>
                        <input type="text" wire:model="title" class="input-field" placeholder="Book title">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="label">Subtitle</label>
                        <input type="text" wire:model="subtitle" class="input-field" placeholder="Book subtitle">
                    </div>

                    <div>
                        <label class="label">ISBN</label>
                        <input type="text" wire:model="isbn" class="input-field" placeholder="ISBN-10">
                    </div>

                    <div>
                        <label class="label">Edition</label>
                        <input type="text" wire:model="edition" class="input-field" placeholder="1st Edition">
                    </div>

                    <div>
                        <label class="label">Volume</label>
                        <input type="text" wire:model="volume" class="input-field" placeholder="Vol. 1">
                    </div>

                    <div>
                        <label class="label">Language</label>
                        <select wire:model="language" class="input-field">
                            <option value="en">English</option>
                            <option value="sw">Swahili</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="es">Spanish</option>
                            <option value="ar">Arabic</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Pages</label>
                        <input type="number" wire:model="pages" class="input-field" placeholder="Number of pages">
                    </div>

                    <div>
                        <label class="label">Publication Year</label>
                        <input type="number" wire:model="publication_year" class="input-field" placeholder="e.g. 2024">
                    </div>

                    <div>
                        <label class="label">Series</label>
                        <input type="text" wire:model="series" class="input-field" placeholder="Series name">
                    </div>
                </div>

                <div>
                    <label class="label">Description</label>
                    <textarea wire:model="description" rows="4" class="input-field" placeholder="Book description..."></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Classification</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Category</label>
                        <select wire:model="category_id" class="input-field">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @foreach($category->children as $child)
                                    <option value="{{ $child->id }}">&nbsp;&nbsp;{{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label">Publisher</label>
                        <select wire:model="publisher_id" class="input-field">
                            <option value="">Select Publisher</option>
                            @foreach($allPublishers as $publisher)
                                <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label">Dewey Decimal</label>
                        <input type="text" wire:model="dewey_decimal" class="input-field" placeholder="e.g. 610.73">
                    </div>

                    <div>
                        <label class="label">LC Classification</label>
                        <input type="text" wire:model="lc_classification" class="input-field" placeholder="e.g. RT1-120">
                    </div>
                </div>

                <div>
                    <label class="label">Authors</label>
                    <select wire:model="authors" multiple class="input-field h-32">
                        @foreach($allAuthors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-surface-400 mt-1">Hold Ctrl/Cmd to select multiple</p>
                </div>

                <div>
                    <label class="label">Subjects</label>
                    <select wire:model="subjects" multiple class="input-field h-32">
                        @foreach($allSubjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-surface-400 mt-1">Hold Ctrl/Cmd to select multiple</p>
                </div>
            </div>
        </div>

        @unless($isEditing)
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Copies</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Number of Copies</label>
                        <input type="number" wire:model="copies_count" min="1" max="100" class="input-field">
                    </div>

                    <div>
                        <label class="label">Shelf Location</label>
                        <input type="text" wire:model="shelf_location" class="input-field" placeholder="e.g. A-12-3">
                    </div>
                </div>
            </div>
        </div>
        @endunless

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Pricing</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Price (KES)</label>
                        <input type="number" wire:model="price" step="0.01" class="input-field" placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-surface-900 dark:text-white">Cover Image</h3>
            </div>
            <div class="card-body">
                {{-- Preview: existing or newly uploaded --}}
                @if($cover_image)
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-16 h-20 rounded-lg overflow-hidden border-2 border-primary-200 shadow-sm bg-surface-100 flex-shrink-0">
                            <img src="{{ $cover_image->temporaryUrl() }}" alt="New cover preview" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-surface-900 dark:text-white">New cover selected</p>
                            <p class="text-xs text-surface-500">Upload will apply on save</p>
                        </div>
                    </div>
                @elseif($isEditing && $existingCoverUrl)
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-16 h-20 rounded-lg overflow-hidden border-2 border-surface-200 shadow-sm bg-surface-100 flex-shrink-0">
                            <img src="{{ $existingCoverUrl }}" alt="Current cover" loading="lazy" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-surface-900 dark:text-white">Current cover</p>
                            <p class="text-xs text-surface-500">Upload a new one to replace it</p>
                        </div>
                    </div>
                @endif

                <label class="upload-zone">
                    <svg class="upload-zone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="upload-zone-text">
                        {{ $cover_image ? 'Replace cover image' : ($isEditing ? 'Replace cover image' : 'Upload cover image') }}
                    </span>
                    <span class="upload-zone-hint">JPG, PNG, WEBP · max 2 MB</span>
                    <input type="file" wire:model="cover_image" accept="image/*" class="upload-zone-input">
                </label>
                @error('cover_image') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 mobile-form-actions">
            <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Book' : 'Add Book' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
