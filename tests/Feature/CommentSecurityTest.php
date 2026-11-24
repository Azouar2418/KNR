<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CommentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::ensureDirectoryExists(storage_path());
        File::put(storage_path('installed'), 'installed');
    }

    private function makeManga(User $user): Manga
    {
        return Manga::create([
            'title' => 'Test Manga',
            'slug' => 'test-manga-' . uniqid(),
            'status' => 'ongoing',
            'user_id' => $user->id,
        ]);
    }

    public function test_comment_store_rejects_arbitrary_commentable_type(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $manga = $this->makeManga($user);

        $response = $this->actingAs($user)->post(route('comments.store'), [
            'comment' => 'This is a valid comment body.',
            'type' => User::class,
            'key' => $user->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_store_accepts_allowed_manga_type(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $manga = $this->makeManga($user);

        $response = $this->actingAs($user)->post(route('comments.store'), [
            'comment' => 'This is a valid comment body.',
            'type' => Manga::class,
            'key' => $manga->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_type' => Manga::class,
            'commentable_id' => $manga->id,
        ]);
    }
}
