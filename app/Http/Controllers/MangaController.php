<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MangaController extends Controller
{
  /**
   * View all mangas.
   *
   * @return \Illuminate\View\View
   */
  public function index(Request $request)
  {
    $resultQuery = Manga::query();

    if ($request->filled("title")) {
      $resultQuery->where("title", "like", "%{$request->input("title")}%");
    }

    if ($request->filled("type")) {
      $resultQuery->whereHas("types", function ($query) use ($request) {
        $query->where("title", "like", "%{$request->input("type")}%");
      });
    }

    if ($request->filled("status")) {
      $resultQuery->whereHas("statuses", function ($query) use ($request) {
        $query->where("title", "like", "%{$request->input("status")}%");
      });
    }

    if ($request->filled("genre")) {
      $genres = $request->input("genre");

      if (!is_array($genres)) {
        $genres = [$genres];
      }

      $resultQuery->where(function ($query) use ($genres) {
        foreach ($genres as $genre) {
          $query->whereHas("genres", function ($subQuery) use ($genre) {
            $subQuery->where("title", "=", $genre);
          });
        }
      });
    }

    $mangas = $resultQuery
      ->with(['types:id,title', 'statuses:id,title', 'genres:id,title,slug'])
      ->latest()
      ->fastPaginate(24)
      ->through(function ($manga) {
        $manga->type_title = $manga->types->first()->title ?? null;
        $manga->status_title = $manga->statuses->first()->title ?? null;
        $manga->genres = $manga->genres->take(5)->values();
        return $manga;
      });

    return view("pages.manga-list", compact("mangas"));
  }

  /**
   * View a single manga.
   *
   * @param  \App\Models\Manga  $manga
   * @return \Illuminate\View\View
   */
  public function show(Manga $manga)
  {
    if ($manga->status == "private") {
      if (
        !auth()->check() ||
        !auth()
          ->user()
          ->can("view_mangas")
      ) {
        abort(403);
      }
    }

    $manga->addView();
    $cachedManga = Cache::remember("manga_query_" . $manga->id, Carbon::now()->addHours(3), function () use ($manga) {
      $manga->views = $manga->totalViews();
      $manga->type_title = $manga->types()->first()->title ?? null;
      $manga->status_title = $manga->statuses()->first()->title ?? null;
      $manga->genres = $manga
        ->genres()
        ->select(["title", "slug"])
        ->get();
      $manga->bookmarks_count = $manga->bookmarks()->count();
      return $manga;
    });

    $comments = $this->getCachedComments($manga);
    $manga = $cachedManga;

    //
    $chapters = $manga->chapters();
    $manga->chapters_number = $chapters->count();
    $manga->first_chapter = $chapters->select(["chapter_number"])->first()->chapter_number ?? null;
    $manga->chapters = $chapters
      ->select(["id", "chapter_number", "created_at"])
      ->orderBy("chapter_number", "desc")
      ->fastPaginate(100);

    return view("pages.manga", compact("manga", "comments"));
  }

  private function getCachedComments(Manga $manga)
  {
    $comments = Cache::remember("comments_" . $manga->slug . $manga->id, Carbon::now()->addHours(3), function () use ($manga) {
      return $manga->comments->map(function ($comment) {
        $comment->sum_count = $comment->likesSum();
        $comment->user_avatar = $comment->user->avatar;
        return $comment;
      })->values();
    });

    if (auth()->check()) {
      $reactions = auth()
        ->user()
        ->reactions()
        ->whereIn("comment_id", $comments->pluck("id"))
        ->get(["comment_id", "type"])
        ->keyBy("comment_id");

      $comments = $comments->map(function ($comment) use ($reactions) {
        $reaction = $reactions->get($comment->id);
        $comment->liked = $reaction && (int) $reaction->type === 1 ? 1 : 0;
        $comment->disliked = $reaction && (int) $reaction->type === 2 ? 1 : 0;
        return $comment;
      });
    } else {
      $comments = $comments->map(function ($comment) {
        $comment->liked = null;
        $comment->disliked = null;
        return $comment;
      });
    }

    return $comments->sortByDesc("sum_count")->values();
  }

  /**
   * Search for mangas.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\View\View
   */
  public function search(Request $request)
  {
    $request->validate([
      "title" => "required",
    ]);

    return view("projects", [
      "mangas" => Manga::where("title", "like", "%" . $request->input("title") . "%")
        ->orderBy("created_at")
        ->fastPaginate(12),
    ]);
  }

  /**
   * Redirect to a random manga.
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function random()
  {
    $randomManga = Manga::inRandomOrder()->first();

    if ($randomManga) {
      return redirect("/manga/" . $randomManga->slug);
    }

    // Handle the case where no random manga is found
    return redirect()
      ->route("home")
      ->with("error", "No random mangas available.");
  }
}
