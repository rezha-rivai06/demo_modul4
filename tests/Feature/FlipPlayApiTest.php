<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlipPlayApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_list_and_search_media_with_eager_loaded_users(): void
    {
        $listResponse = $this->getJson('/api/media');
        $listResponse->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'type', 'genre', 'rating', 'release_year', 'users'],
                ],
            ]);

        $searchResponse = $this->getJson('/api/media?search=Frieren');
        $searchResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Sousou no Frieren');
    }

    public function test_can_show_media_detail_with_users(): void
    {
        $response = $this->getJson('/api/media/1');

        $response->assertOk()
            ->assertJsonPath('data.title', 'Sousou no Frieren')
            ->assertJsonPath('data.total_episodes', 28)
            ->assertJsonPath('data.studio', 'Madhouse')
            ->assertJsonMissingPath('data.duration_minutes')
            ->assertJsonCount(1, 'data.users')
            ->assertJsonPath('data.users.0.name', 'Alice');
    }

    public function test_can_create_update_and_delete_media(): void
    {
        $createResponse = $this->postJson('/api/media', [
            'title' => 'Inception',
            'type' => 'movie',
            'genre' => 'Sci-Fi, Action',
            'rating' => 8.8,
            'release_year' => 2010,
            'duration_minutes' => 148,
            'director' => 'Christopher Nolan',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Inception')
            ->assertJsonPath('data.duration_minutes', 148)
            ->assertJsonMissingPath('data.total_episodes');

        $mediaId = $createResponse->json('data.id');

        // Update sebagian field tanpa mengubah title tidak boleh terkena validasi unique
        $updateResponse = $this->patchJson("/api/media/{$mediaId}", [
            'rating' => 9.0,
            'genre' => 'Sci-Fi, Thriller',
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.rating', 9)
            ->assertJsonPath('data.genre', 'Sci-Fi, Thriller');

        $deleteResponse = $this->deleteJson("/api/media/{$mediaId}");
        $deleteResponse->assertOk()
            ->assertJsonPath('message', 'Media berhasil dihapus');

        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    }

    public function test_validation_errors_return_422_for_empty_or_duplicate_title(): void
    {
        $emptyTitle = $this->postJson('/api/media', [
            'title' => '',
            'type' => 'movie',
            'genre' => 'Drama',
            'rating' => 8.0,
            'release_year' => 2022,
            'duration_minutes' => 100,
            'director' => 'Test',
        ]);

        $emptyTitle->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $duplicateTitle = $this->postJson('/api/media', [
            'title' => 'Sousou no Frieren',
            'type' => 'anime',
            'genre' => 'Adventure',
            'rating' => 9.1,
            'release_year' => 2023,
            'total_episodes' => 28,
            'studio' => 'Madhouse',
        ]);

        $duplicateTitle->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $missingMovieFields = $this->postJson('/api/media', [
            'title' => 'Dune Part Two',
            'type' => 'movie',
            'genre' => 'Sci-Fi',
            'rating' => 8.6,
            'release_year' => 2024,
        ]);

        $missingMovieFields->assertStatus(422)
            ->assertJsonValidationErrors(['duration_minutes', 'director']);
    }

    public function test_user_collection_attach_duplicate_409_view_and_detach(): void
    {
        $bob = User::where('email', 'bob@flipplay.test')->firstOrFail();
        $spiritedAway = Media::where('title', 'Spirited Away')->firstOrFail();

        // 1. Tambah media ke koleksi Bob (attach)
        $attachResponse = $this->postJson("/api/users/{$bob->id}/collection", [
            'media_id' => $spiritedAway->id,
        ]);

        $attachResponse->assertCreated()
            ->assertJsonCount(1, 'data.media')
            ->assertJsonPath('data.media.0.title', 'Spirited Away');

        // 2. Tambah media yang sama 2x -> 409 Conflict
        $duplicateAttach = $this->postJson("/api/users/{$bob->id}/collection", [
            'media_id' => $spiritedAway->id,
        ]);

        $duplicateAttach->assertStatus(409)
            ->assertJsonPath('message', 'Media sudah ada di koleksi pengguna');

        // 3. Lihat koleksi Bob
        $showCollection = $this->getJson("/api/users/{$bob->id}/collection");
        $showCollection->assertOk()
            ->assertJsonCount(1, 'data.media');

        // 4. Hapus media dari koleksi Bob (detach)
        $detachResponse = $this->deleteJson("/api/users/{$bob->id}/collection/{$spiritedAway->id}");
        $detachResponse->assertOk()
            ->assertJsonCount(0, 'data.media');

        // 5. Hapus lagi media yang sudah tidak ada di koleksi -> 404 Not Found
        $detachMissing = $this->deleteJson("/api/users/{$bob->id}/collection/{$spiritedAway->id}");
        $detachMissing->assertNotFound();
    }

    public function test_nonexistent_media_returns_404(): void
    {
        $this->getJson('/api/media/9999')->assertNotFound();
    }
}
