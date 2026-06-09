@section('title', 'Upload Asset')
<div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Upload Digital Asset</h1>

    <form wire:submit="save" class="space-y-6">
        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-medium text-gray-800 dark:text-gray-200">File</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">File *</label>
                    <input type="file" wire:model="file"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                  file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                  file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100
                                  dark:file:bg-primary-900/20 dark:file:text-primary-300"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mp3,.csv,.json,.epub,.txt">
                    @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="file" class="mt-2 text-sm text-primary-600">Uploading...</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Cover Image</label>
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <input type="file" wire:model="coverImage"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                          file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                          file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100
                                          dark:file:bg-primary-900/20 dark:file:text-primary-300">
                            @error('coverImage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="coverImage" class="mt-2 text-sm text-primary-600">Uploading cover...</div>
                            <p class="mt-1 text-xs text-gray-400">JPEG, PNG, WebP. Max 2MB.</p>
                        </div>
                        @if ($coverImage)
                            <div class="shrink-0">
                                <img src="{{ $coverImage->temporaryUrl() }}"
                                     class="w-20 h-28 rounded-xl object-cover shadow-soft border border-gray-200">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-medium text-gray-800 dark:text-gray-200">Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Title *</label>
                    <input type="text" wire:model="title" class="input-field w-full">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="input-field w-full"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Category</label>
                    <select wire:model="categoryId" class="input-field w-full">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Author</label>
                    <input type="text" wire:model="author" class="input-field w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Publisher</label>
                    <input type="text" wire:model="publisher" class="input-field w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">ISBN</label>
                    <input type="text" wire:model="isbn" class="input-field w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Publication Year</label>
                    <input type="number" wire:model="publicationYear" min="1900" max="2099" class="input-field w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Language</label>
                    <select wire:model="language" class="input-field w-full">
                        <option value="en">English</option>
                        <option value="sw">Swahili</option>
                        <option value="fr">French</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Keywords (comma-separated)</label>
                    <input type="text" wire:model="keywords" placeholder="e.g. anatomy, physiology, medical"
                           class="input-field w-full">
                </div>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-medium text-gray-800 dark:text-gray-200">Link to Book</h2>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Physical Book (optional)</label>
                <select wire:model="bookId" class="input-field w-full">
                    <option value="">Not linked to a book</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->isbn ?? 'no ISBN' }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Link this digital file to a physical book in the catalog.</p>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-medium text-gray-800 dark:text-gray-200">Access & Permissions</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Access Level</label>
                    <select wire:model="accessLevel" class="input-field w-full">
                        @foreach(\App\Modules\DigitalLibrary\Models\DigitalAsset::accessLevelOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" wire:model="allowDownload" id="allowDownload"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="allowDownload" class="text-sm text-gray-700 dark:text-gray-300">Allow Download</label>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" wire:model="allowPrinting" id="allowPrinting"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="allowPrinting" class="text-sm text-gray-700 dark:text-gray-300">Allow Printing</label>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Upload Asset</span>
                <span wire:loading>Uploading...</span>
            </button>
            <a href="{{ route('digital-library.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
