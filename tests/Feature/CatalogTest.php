<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'admin@ollmchs.ac.ke')->first() ?? User::factory()->create();
    }

    public function test_books_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('catalog.books.index'));
        $response->assertOk();
    }

    public function test_book_can_be_viewed(): void
    {
        $book = Book::first();
        if (!$book) {
            $this->markTestSkipped('No books seeded');
        }
        $response = $this->actingAs($this->user)->get(route('catalog.books.show', $book));
        $response->assertOk();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $response = $this->get(route('catalog.books.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_api_returns_books(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/books');
        $response->assertOk();
    }

    public function test_api_login_validates_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'wrong@email.com',
            'password' => 'wrong',
        ]);
        $response->assertUnprocessable();
    }

    public function test_api_login_succeeds(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }
}
