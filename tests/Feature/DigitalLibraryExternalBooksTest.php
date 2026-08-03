<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\DigitalLibrary\Livewire\FreeBooksSearch;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Services\ExternalBookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalLibraryExternalBooksTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
        $this->admin = User::where('email', 'admin@ollmchs.ac.ke')->first() ?? User::factory()->create();
        $this->actingAs($this->admin);
    }

    protected function fakeGutenbergResponse(): void
    {
        Http::fake([
            'gutendex.com/*' => Http::response([
                'results' => [
                    [
                        'id' => 84,
                        'title' => 'Frankenstein; Or, The Modern Prometheus',
                        'authors' => [['name' => 'Shelley, Mary Wollstonecraft']],
                        'subjects' => ['Science fiction', 'Monsters -- Fiction'],
                        'languages' => ['en'],
                        'copyright' => false,
                        'download_count' => 12345,
                        'formats' => [
                            'text/html; charset=utf-8' => 'https://www.gutenberg.org/files/84/84-h/84-h.htm',
                            'image/jpeg' => 'https://www.gutenberg.org/cache/epub/84/pg84.cover.medium.jpg',
                            'application/epub+zip' => 'https://www.gutenberg.org/ebooks/84.epub3.images',
                        ],
                    ],
                    [
                        'id' => 100,
                        'title' => 'Copyrighted Book',
                        'authors' => [['name' => 'Some Author']],
                        'subjects' => [],
                        'languages' => ['en'],
                        'copyright' => true,
                        'formats' => ['text/html; charset=utf-8' => 'https://example.com/copyrighted'],
                    ],
                    [
                        'id' => 200,
                        'title' => 'No HTML Format',
                        'authors' => [],
                        'subjects' => [],
                        'languages' => ['en'],
                        'copyright' => false,
                        'formats' => ['application/x-mobipocket-ebook' => 'https://example.com/book.mobi'],
                    ],
                ],
            ]),
        ]);
    }

    protected function fakeGoogleBooksResponse(): void
    {
        Http::fake([
            'googleapis.com/books/*' => Http::response([
                'items' => [
                    [
                        'id' => 'abc123',
                        'volumeInfo' => [
                            'title' => 'Nursing the Sick',
                            'authors' => ['Jane Doe'],
                            'categories' => ['Medical'],
                            'language' => 'en',
                            'publisher' => 'Free Press',
                            'publishedDate' => '1920',
                            'description' => 'A public domain nursing text.',
                            'imageLinks' => ['thumbnail' => 'https://example.com/thumb.jpg'],
                            'identifiers' => [
                                ['type' => 'ISBN_13', 'identifier' => '9781234567897'],
                            ],
                        ],
                        'accessInfo' => [
                            'viewability' => 'FULL_PUBLIC_DOMAIN',
                            'publicDomain' => true,
                            'webReaderLink' => 'https://books.google.com/books?id=abc123',
                        ],
                    ],
                    [
                        'id' => 'noaccess',
                        'volumeInfo' => ['title' => 'Paid Book'],
                        'accessInfo' => ['viewability' => 'NO_PAGES'],
                    ],
                ],
            ]),
        ]);
    }

    public function test_gutenberg_search_normalizes_and_excludes_copyrighted(): void
    {
        $this->fakeGutenbergResponse();

        $results = app(ExternalBookService::class)->search('frankenstein', 'gutenberg');

        $this->assertCount(1, $results);
        $this->assertSame('Frankenstein; Or, The Modern Prometheus', $results[0]['title']);
        $this->assertSame('gutenberg', $results[0]['provider']);
        $this->assertSame('https://www.gutenberg.org/files/84/84-h/84-h.htm', $results[0]['read_url']);
        $this->assertSame('https://www.gutenberg.org/cache/epub/84/pg84.cover.medium.jpg', $results[0]['cover_url']);
        $this->assertSame(['Shelley, Mary Wollstonecraft'], $results[0]['authors']);
        $this->assertSame(['Science fiction', 'Monsters -- Fiction'], $results[0]['subjects']);
    }

    public function test_google_books_search_only_returns_full_view(): void
    {
        $this->fakeGoogleBooksResponse();

        $results = app(ExternalBookService::class)->search('nursing', 'google_books');

        $this->assertCount(1, $results);
        $this->assertSame('Nursing the Sick', $results[0]['title']);
        $this->assertSame('google_books', $results[0]['provider']);
        $this->assertSame('https://books.google.com/books?id=abc123', $results[0]['read_url']);
        $this->assertSame('9781234567897', $results[0]['isbn']);
        $this->assertSame('Jane Doe', $results[0]['authors'][0]);
    }

    public function test_search_with_short_query_returns_empty(): void
    {
        Http::assertNothingSent();

        $results = app(ExternalBookService::class)->search('a', 'gutenberg');

        $this->assertSame([], $results);
    }

    public function test_free_books_page_loads(): void
    {
        $response = $this->get(route('digital-library.free-books'));

        $response->assertOk();
        $response->assertSee('Free Online Books');
        $response->assertSee('Project Gutenberg');
    }

    public function test_livewire_search_populates_results(): void
    {
        $this->fakeGutenbergResponse();

        Livewire::test(FreeBooksSearch::class)
            ->set('query', 'frankenstein')
            ->set('provider', 'gutenberg')
            ->call('search')
            ->assertSet('hasSearched', true)
            ->assertSet('error', null)
            ->assertCount('results', 1)
            ->assertSet('results.0.title', 'Frankenstein; Or, The Modern Prometheus');
    }

    public function test_save_to_library_creates_external_asset(): void
    {
        $this->fakeGutenbergResponse();

        Livewire::test(FreeBooksSearch::class)
            ->set('query', 'frankenstein')
            ->set('provider', 'gutenberg')
            ->call('search')
            ->call('saveToLibrary', 0)
            ->assertDispatched('notify');

        $this->assertDatabaseHas('digital_assets', [
            'title' => 'Frankenstein; Or, The Modern Prometheus',
            'file_type' => 'ebook',
            'access_level' => 'public',
            'is_external' => 1,
            'source_url' => 'https://www.gutenberg.org/files/84/84-h/84-h.htm',
        ]);
    }

    public function test_save_to_library_prevents_duplicates(): void
    {
        $this->fakeGutenbergResponse();

        Livewire::test(FreeBooksSearch::class)
            ->set('query', 'frankenstein')
            ->set('provider', 'gutenberg')
            ->call('search')
            ->call('saveToLibrary', 0)
            ->call('search')
            ->call('saveToLibrary', 0)
            ->assertDispatched('notify');

        $this->assertSame(1, DigitalAsset::where('source_url', 'https://www.gutenberg.org/files/84/84-h/84-h.htm')->count());
    }

    public function test_save_to_library_requires_upload_permission(): void
    {
        $this->fakeGutenbergResponse();

        $user = User::factory()->create();
        Livewire::actingAs($user)
            ->test(FreeBooksSearch::class)
            ->set('query', 'frankenstein')
            ->set('provider', 'gutenberg')
            ->call('search')
            ->call('saveToLibrary', 0)
            ->assertForbidden();
    }

    public function test_external_asset_reader_renders_iframe(): void
    {
        $asset = $this->createExternalAsset();

        $response = $this->get(route('digital-library.read', $asset));

        $response->assertOk();
        $response->assertSee('https://www.gutenberg.org/files/84/84-h/84-h.htm', false);
        $response->assertSee('Open in new tab');
        $response->assertDontSee('pdf.min.js');
    }

    public function test_file_controller_redirects_external_asset_to_source(): void
    {
        $asset = $this->createExternalAsset();

        $response = $this->get(route('digital-library.file', $asset));

        $response->assertRedirect('https://www.gutenberg.org/files/84/84-h/84-h.htm');
    }

    public function test_local_asset_file_still_serves_file(): void
    {
        $path = 'digital-library/pdf/local-'.Str::random(6).'.pdf';
        Storage::disk('public')->put($path, 'PDF test content');

        $asset = DigitalAsset::create([
            'title' => 'Local PDF',
            'slug' => 'local-pdf-'.Str::random(6),
            'file_path' => $path,
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'file_size' => 100,
            'access_level' => 'public',
            'is_active' => true,
            'is_external' => false,
        ]);

        $response = $this->get(route('digital-library.file', $asset));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        Storage::disk('public')->delete($path);
    }

    protected function createExternalAsset(): DigitalAsset
    {
        return DigitalAsset::create([
            'title' => 'Frankenstein',
            'slug' => 'frankenstein-'.Str::random(6),
            'description' => 'Public domain novel',
            'file_path' => 'https://www.gutenberg.org/files/84/84-h/84-h.htm',
            'file_type' => 'ebook',
            'mime_type' => 'text/html',
            'file_extension' => 'html',
            'author' => 'Shelley, Mary Wollstonecraft',
            'publisher' => 'Project Gutenberg',
            'language' => 'en',
            'access_level' => 'public',
            'allow_download' => false,
            'allow_printing' => false,
            'is_active' => true,
            'is_external' => true,
            'source_url' => 'https://www.gutenberg.org/files/84/84-h/84-h.htm',
            'uploaded_by' => $this->admin->id,
        ]);
    }
}
