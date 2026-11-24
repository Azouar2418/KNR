<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ChapterAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::ensureDirectoryExists(storage_path());
        File::put(storage_path('installed'), 'installed');
    }

    public function test_user_cannot_update_chapter_they_do_not_own_with_edit_own_permission(): void
    {
        Permission::create(['name' => 'edit_own_chapters', 'guard_name' => 'web']);

        $owner = User::factory()->create(['email_verified_at' => now()]);
        $attacker = User::factory()->create(['email_verified_at' => now()]);
        $attacker->givePermissionTo('edit_own_chapters');

        $manga = Manga::create([
            'title' => 'Manga',
            'slug' => 'manga-' . uniqid(),
            'status' => 'ongoing',
            'user_id' => $owner->id,
        ]);

        $chapter = Chapter::create([
            'title' => 'Ch 1',
            'chapter_number' => 1,
            'manga_id' => $manga->id,
            'user_id' => $owner->id,
            'content' => [],
        ]);

        $response = $this->actingAs($attacker)->put(route('dashboard.chapters.update', $chapter), [
            'title' => 'Hacked',
            'manga_id' => $manga->id,
            'chapter_number' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'title' => 'Ch 1',
        ]);
    }
}
