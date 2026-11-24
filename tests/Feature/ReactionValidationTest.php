<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReactionValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::ensureDirectoryExists(storage_path());
        File::put(storage_path('installed'), 'installed');
    }

    private function makeComment(): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $manga = Manga::create([
            'title' => 'Test Manga',
            'slug' => 'test-manga-' . uniqid(),
            'status' => 'ongoing',
            'user_id' => $user->id,
        ]);
        $comment = Comment::create([
            'user_id' => $user->id,
            'commentable_id' => $manga->id,
            'commentable_type' => Manga::class,
            'content' => 'A valid comment content',
        ]);
        return [$user, $comment];
    }

    public function test_reaction_rejects_invalid_type(): void
    {
        [$user, $comment] = $this->makeComment();

        $response = $this->actingAs($user)->postJson(route('comments.reaction.toggle', $comment), [
            'type' => 99,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('comments_reactions', 0);
    }

    public function test_reaction_accepts_valid_type(): void
    {
        [$user, $comment] = $this->makeComment();

        $response = $this->actingAs($user)->postJson(route('comments.reaction.toggle', $comment), [
            'type' => 1,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('comments_reactions', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'type' => 1,
        ]);
    }
}
