<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookIsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3-4-1: 正常なISBNで、Google Books APIから書籍情報が取得できる
     */
    public function test_fetches_book_info_from_google_books_api(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '吾輩は猫である',
                            'authors' => ['夏目漱石'],
                            'description' => 'テスト説明',
                            'publishedDate' => '1905-01-01',
                            'imageLinks' => ['thumbnail' => 'https://example.com/image.jpg'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784101010014');

        $response->assertStatus(200)
            ->assertJson([
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'description' => 'テスト説明',
                'image_url' => 'https://example.com/image.jpg',
                'published_date' => '1905-01-01',
            ]);
    }

    /**
     * 3-4-2: 該当書籍がない場合、エラーメッセージが返る
     */
    public function test_returns_error_when_book_not_found(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'totalItems' => 0,
                'items' => [],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784999999999');

        $response->assertStatus(200)
            ->assertJson(['error' => '該当する書籍が見つかりませんでした。']);
    }

    /**
     * 3-4-3: 外部APIがエラーを返した場合、適切なエラーメッセージが返る
     */
    public function test_returns_error_when_external_api_fails(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784101010014');

        $response->assertStatus(200)
            ->assertJson(['error' => '該当する書籍が見つかりませんでした。']);
    }

    /**
     * 3-4-4: 著者が複数人の場合、カンマ区切りで結合される
     */
    public function test_combines_multiple_authors_with_comma(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '共著本',
                            'authors' => ['著者A', '著者B'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784000000001');

        $response->assertStatus(200)
            ->assertJson(['author' => '著者A, 著者B']);
    }

    /**
     * 3-4-5: 出版日が年のみの場合、1月1日で補完される
     */
    public function test_supplements_published_date_with_year_only(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト本',
                            'publishedDate' => '2020',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784000000002');

        $response->assertStatus(200)
            ->assertJson(['published_date' => '2020-01-01']);
    }

    /**
     * 3-4-6: 出版日が年月のみの場合、1日で補完される
     */
    public function test_supplements_published_date_with_year_and_month_only(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト本',
                            'publishedDate' => '2020-05',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784000000003');

        $response->assertStatus(200)
            ->assertJson(['published_date' => '2020-05-01']);
    }
}
