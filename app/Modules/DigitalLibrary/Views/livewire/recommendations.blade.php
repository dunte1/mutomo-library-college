@section('title', 'Recommendations')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Your Recommendations</h1>

    @if(!empty($predictiveAlert) && !empty($predictiveAlert[0]))
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <h4 class="font-medium text-amber-800 dark:text-amber-200">Library Insight</h4>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">{{ $predictiveAlert[0] }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex gap-2 flex-wrap">
        <button wire:click="$set('tab', 'all')"
                class="px-4 py-2 text-sm rounded-lg transition-colors duration-150
                {{ $tab === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
            All
        </button>
        @foreach(\App\Modules\DigitalLibrary\Models\Recommendation::typeOptions() as $key => $label)
            <button wire:click="$set('tab', '{{ $key }}')"
                    class="px-4 py-2 text-sm rounded-lg transition-colors duration-150
                    {{ $tab === $key ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @forelse($recommendations as $recommendation)
        <div class="card p-4 flex items-start gap-4">
            @if($recommendation->book)
                <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <a href="{{ route('catalog.books.show', $recommendation->book) }}" wire:navigate
                           class="hover:text-primary-600">{{ $recommendation->book->title }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $recommendation->reason }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-primary-600 font-medium">
                            Score: {{ $recommendation->score * 100 }}%
                        </span>
                        <span class="badge
                            @if($recommendation->type === 'similar_book') badge-info
                            @elseif($recommendation->type === 'new_arrival') badge-success
                            @else badge-warning
                            @endif">
                            {{ \App\Modules\DigitalLibrary\Models\Recommendation::typeOptions()[$recommendation->type] ?? $recommendation->type }}
                        </span>
                    </div>
                </div>
            @elseif($recommendation->digitalAsset)
                <div class="p-2 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <a href="{{ route('digital-library.show', $recommendation->digitalAsset) }}" wire:navigate
                           class="hover:text-primary-600">{{ $recommendation->digitalAsset->title }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $recommendation->reason }}</p>
                </div>
            @endif
        </div>
    @empty
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">Borrow some books to get personalized recommendations</p>
                            <a href="{{ route('catalog.books.index') }}" wire:navigate class="btn-primary mt-4 inline-block">Browse Catalog</a>
        </div>
    @endforelse
</div>
