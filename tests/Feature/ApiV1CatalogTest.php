<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use App\Modules\DigitalLibrary\Models\DigitalAssetCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('super-admin');
        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('student');
    }

    protected function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->admin->createToken('test')->plainTextToken];
    }

    // ===== BOOKS =====

    public function test_books_list(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/books");

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'total',
            ]);
    }

    public function test_books_empty_search(): void
    {
        $response = $this->getJson("{$this->baseUrl}/books/search?q=nonexistentxyz");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_books_search_requires_min_length(): void
    {
        $response = $this->getJson("{$this->baseUrl}/books/search?q=x");

        $response->assertUnprocessable();
    }

    public function test_book_detail(): void
    {
        $book = Book::first();
        if (! $book) {
            $this->markTestSkipped('No books seeded');
        }

        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/books/{$book->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'isbn', 'slug'],
            ]);
    }

    public function test_book_detail_denied_without_permission(): void
    {
        // Create user with NO role at all (no permissions)
        $noPermUser = User::factory()->create();
        $token = $noPermUser->createToken('test')->plainTextToken;

        $book = Book::first();
        if (! $book) {
            $this->markTestSkipped('No books seeded');
        }

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("{$this->baseUrl}/books/{$book->id}");

        // User has no roles and no permissions -> 403
        $response->assertForbidden();
    }

    // ===== CATEGORIES =====

    public function test_categories_list(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/categories");

        $response->assertOk();
    }

    public function test_category_detail(): void
    {
        $category = Category::first();
        if (! $category) {
            $this->markTestSkipped('No categories seeded');
        }

        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/categories/{$category->id}");

        $response->assertOk()->assertJsonStructure(['data' => ['id', 'name', 'slug']]);
    }

    // ===== AUTHORS =====

    public function test_authors_list(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/authors");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_author_detail(): void
    {
        $author = Author::first();
        if (! $author) {
            $this->markTestSkipped('No authors seeded');
        }

        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/authors/{$author->id}");

        $response->assertOk()->assertJsonStructure(['data' => ['id', 'name']]);
    }

    // ===== PUBLISHERS =====

    public function test_publishers_list(): void
    {
        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/publishers");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_publisher_detail(): void
    {
        $publisher = Publisher::first();
        if (! $publisher) {
            $this->markTestSkipped('No publishers seeded');
        }

        $response = $this->withHeaders($this->headers())
            ->getJson("{$this->baseUrl}/publishers/{$publisher->id}");

        $response->assertOk()->assertJsonStructure(['data' => ['id', 'name']]);
    }
}
