<?php

namespace App\Modules\DigitalLibrary\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExternalBookService
{
    protected const CACHE_TTL = 86400;

    public const PROVIDERS = ['gutenberg', 'google_books', 'open_library'];

    public function search(string $query, string $provider = 'gutenberg', int $perPage = 12): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return [];
        }

        return match ($provider) {
            'google_books' => $this->searchGoogleBooks($query, $perPage),
            'open_library' => $this->searchOpenLibrary($query, $perPage),
            default => $this->searchGutenberg($query, $perPage),
        };
    }

    public function searchGutenberg(string $query, int $perPage = 12): array
    {
        return $this->cached('gutenberg', $query, function () use ($query, $perPage) {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get('https://gutendex.com/books', [
                    'search' => $query,
                    'languages' => 'en',
                ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('results', []))
                ->filter(fn (array $book) => ($book['copyright'] ?? true) !== true)
                ->filter(fn (array $book) => ! empty($book['formats']))
                ->map(function (array $book) {
                    $formats = $book['formats'] ?? [];

                    $coverUrl = null;
                    foreach (['image/jpeg', 'image/png'] as $mime) {
                        if (! empty($formats[$mime])) {
                            $coverUrl = $formats[$mime];
                            break;
                        }
                    }

                    $readUrl = $formats['text/html; charset=utf-8']
                        ?? $formats['text/html']
                        ?? $formats['text/plain; charset=utf-8']
                        ?? $formats['text/plain; charset=us-ascii']
                        ?? $formats['text/plain']
                        ?? null;

                    if (! $readUrl) {
                        return null;
                    }

                    return [
                        'provider' => 'gutenberg',
                        'external_id' => $book['id'] ?? null,
                        'title' => $book['title'] ?? 'Untitled',
                        'authors' => collect($book['authors'] ?? [])->pluck('name')->all(),
                        'subjects' => $book['subjects'] ?? [],
                        'languages' => $book['languages'] ?? [],
                        'copyright' => $book['copyright'] ?? null,
                        'cover_url' => $coverUrl,
                        'read_url' => $readUrl,
                        'source_url' => $book['formats']['text/html; charset=utf-8']
                            ?? $book['formats']['text/html']
                            ?? $readUrl,
                        'download_url' => $formats['application/epub+zip']
                            ?? $formats['application/x-mobipocket-ebook']
                            ?? $formats['application/pdf']
                            ?? null,
                        'description' => null,
                        'isbn' => null,
                        'publisher' => 'Project Gutenberg',
                        'publication_year' => null,
                        'download_count' => $book['download_count'] ?? 0,
                    ];
                })
                ->filter()
                ->slice(0, $perPage)
                ->values()
                ->all();
        });
    }

    public function searchGoogleBooks(string $query, int $perPage = 12): array
    {
        return $this->cached('google_books', $query, function () use ($query, $perPage) {
            $params = [
                'q' => $query,
                'maxResults' => min($perPage, 40),
                'printType' => 'books',
            ];

            $apiKey = config('services.google_books.key');
            if ($apiKey) {
                $params['key'] = $apiKey;
            }

            $response = Http::timeout(10)
                ->acceptJson()
                ->get('https://www.googleapis.com/books/v1/volumes', $params);

            if ($response->status() === 429) {
                throw new \Illuminate\Http\Client\RequestException($response);
            }

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('items', []))
                ->map(function (array $item) {
                    $volume = $item['volumeInfo'] ?? [];
                    $access = $item['accessInfo'] ?? [];

                    $viewability = $access['viewability'] ?? 'NO_PAGES';
                    if (! in_array($viewability, ['FULL_PUBLIC_DOMAIN', 'ALL_PAGES'])) {
                        return null;
                    }

                    $readUrl = $access['webReaderLink']
                        ?? $access['textViewLink']
                        ?? $volume['previewLink']
                        ?? null;

                    if (! $readUrl) {
                        return null;
                    }

                    $identifiers = collect($volume['identifiers'] ?? []);
                    $isbn = $identifiers->firstWhere('type', 'ISBN_13')['identifier']
                        ?? $identifiers->firstWhere('type', 'ISBN_10')['identifier']
                        ?? null;

                    return [
                        'provider' => 'google_books',
                        'external_id' => $item['id'] ?? null,
                        'title' => $volume['title'] ?? 'Untitled',
                        'authors' => $volume['authors'] ?? [],
                        'subjects' => $volume['categories'] ?? [],
                        'languages' => [$volume['language'] ?? 'en'],
                        'copyright' => ! ($access['publicDomain'] ?? false),
                        'cover_url' => $volume['imageLinks']['thumbnail']
                            ?? $volume['imageLinks']['smallThumbnail']
                            ?? null,
                        'read_url' => $readUrl,
                        'source_url' => $readUrl,
                        'download_url' => $access['epub']['downloadLink'] ?? null,
                        'description' => $volume['description'] ?? null,
                        'isbn' => $isbn,
                        'publisher' => $volume['publisher'] ?? null,
                        'publication_year' => $volume['publishedDate'] ?? null,
                        'download_count' => 0,
                    ];
                })
                ->filter()
                ->slice(0, $perPage)
                ->values()
                ->all();
        });
    }

    public function searchOpenLibrary(string $query, int $perPage = 12): array
    {
        return $this->cached('open_library', $query, function () use ($query, $perPage) {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get('https://openlibrary.org/search.json', [
                    'q' => $query,
                    'limit' => min($perPage * 4, 100),
                    'fields' => 'title,author_name,first_publish_year,subject,cover_i,ia,key,ebook_access,publisher',
                    'language' => 'eng',
                ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('docs', []))
                ->map(function (array $doc) {
                    $access = $doc['ebook_access'] ?? 'no_ebook';
                    if (! in_array($access, ['full', 'public', 'borrow', 'borrowable'])) {
                        return null;
                    }

                    $iaIdentifier = $doc['ia'][0] ?? null;

                    $readUrl = $iaIdentifier
                        ? 'https://archive.org/details/'.$iaIdentifier
                        : 'https://openlibrary.org'.$doc['key'];

                    return [
                        'provider' => 'open_library',
                        'external_id' => $doc['key'] ?? null,
                        'title' => $doc['title'] ?? 'Untitled',
                        'authors' => $doc['author_name'] ?? [],
                        'subjects' => $doc['subject'] ?? [],
                        'languages' => ['en'],
                        'copyright' => ! in_array($access, ['full', 'public']),
                        'cover_url' => isset($doc['cover_i'])
                            ? 'https://covers.openlibrary.org/b/id/'.$doc['cover_i'].'-M.jpg'
                            : null,
                        'read_url' => $readUrl,
                        'source_url' => $readUrl,
                        'download_url' => $iaIdentifier
                            ? 'https://archive.org/download/'.$iaIdentifier
                            : null,
                        'description' => null,
                        'isbn' => null,
                        'publisher' => $doc['publisher'][0] ?? null,
                        'publication_year' => $doc['first_publish_year'] ?? null,
                        'download_count' => 0,
                    ];
                })
                ->filter()
                ->slice(0, $perPage)
                ->values()
                ->all();
        });
    }

    protected function cached(string $provider, string $query, callable $callback): array
    {
        $key = 'external_books:'.$provider.':'.md5(mb_strtolower($query));

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $results = $callback();

        if (! empty($results)) {
            Cache::put($key, $results, self::CACHE_TTL);
        }

        return $results;
    }
}
