<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MangaModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeManga(string $slug = 'one-piece'): Manga
    {
        $user = User::factory()->create();

        return Manga::create([
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'status' => 'ongoing',
            'user_id' => $user->id,
        ]);
    }

    public function test_core_tables_are_migrated(): void
    {
        foreach (['users', 'mangas', 'chapters', 'comments', 'bookmarks', 'pages', 'views', 'taxonomies', 'taxables', 'plugins'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_manga_has_many_chapters(): void
    {
        $manga = $this->makeManga();

        foreach ([1, 2, 3] as $number) {
            Chapter::create([
                'title' => "Chapter {$number}",
                'chapter_number' => $number,
                'manga_id' => $manga->id,
                'user_id' => $manga->user_id,
            ]);
        }

        $this->assertCount(3, $manga->chapters);
    }

    public function test_manga_slug_must_be_unique(): void
    {
        $this->makeManga('naruto');

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->makeManga('naruto');
    }

    public function test_add_view_increments_the_same_daily_counter(): void
    {
        $manga = $this->makeManga();

        $manga->addView();
        $manga->addView();
        $manga->addView();

        $this->assertSame(3, (int) $manga->totalViews());
    }
}
