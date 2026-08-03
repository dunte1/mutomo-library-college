<?php

namespace App\Modules\DigitalLibrary\Livewire;

use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Services\ExternalBookService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class FreeBooksSearch extends Component
{
    public string $query = '';

    public string $provider = 'gutenberg';

    public array $results = [];

    public bool $hasSearched = false;

    public bool $searching = false;

    public ?string $error = null;

    public function search(): void
    {
        $this->validate(['query' => 'required|string|min:2', 'provider' => 'required|in:gutenberg,google_books']);

        $this->searching = true;
        $this->error = null;
        $this->hasSearched = true;

        try {
            $this->results = app(ExternalBookService::class)->search($this->query, $this->provider);

            if (empty($this->results)) {
                $this->error = 'No free, full-text books found for that search. Try different keywords.';
            }
        } catch (\Throwable $e) {
            Log::warning('External book search failed', ['provider' => $this->provider, 'error' => $e->getMessage()]);

            $this->results = [];

            if ($e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429) {
                $this->error = $this->provider === 'google_books'
                    ? 'Google Books is rate-limiting requests. Try again later, or ask an admin to set a GOOGLE_BOOKS_API_KEY.'
                    : 'Too many requests. Please wait a moment and try again.';
            } else {
                $this->error = 'The free library service is temporarily unavailable. Please try again later.';
            }
        } finally {
            $this->searching = false;
        }
    }

    public function clear(): void
    {
        $this->query = '';
        $this->results = [];
        $this->hasSearched = false;
        $this->error = null;
    }

    public function saveToLibrary(int $index): void
    {
        abort_unless(auth()->user()->can('upload-digital-assets'), 403);

        $book = $this->results[$index] ?? null;

        if (! $book || empty($book['read_url'])) {
            return;
        }

        $exists = DigitalAsset::where('source_url', $book['read_url'])->exists();

        if ($exists) {
            $this->dispatch('notify', type: 'warning', message: 'This book is already in your library.');
            return;
        }

        DigitalAsset::create([
            'title' => $book['title'],
            'slug' => Str::slug($book['title']).'-'.Str::random(6),
            'description' => $book['description'],
            'file_path' => $book['read_url'],
            'file_type' => 'ebook',
            'mime_type' => 'text/html',
            'file_extension' => 'html',
            'cover_image' => $book['cover_url'],
            'author' => $book['authors'] ? implode(', ', $book['authors']) : null,
            'publisher' => $book['publisher'],
            'isbn' => $book['isbn'],
            'language' => $book['languages'][0] ?? 'en',
            'keywords' => $book['subjects'],
            'access_level' => 'public',
            'allow_download' => false,
            'allow_printing' => false,
            'is_active' => true,
            'is_external' => true,
            'source_url' => $book['read_url'],
            'uploaded_by' => auth()->id(),
        ]);

        $this->dispatch('notify', type: 'success', message: '"'.$book['title'].'" added to the library.');
    }

    public function render()
    {
        return view('digital-library::livewire.free-books-search', [
            'providers' => ExternalBookService::PROVIDERS,
        ])->layout('layouts.app');
    }
}
