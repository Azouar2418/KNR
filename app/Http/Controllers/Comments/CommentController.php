<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
  private const ALLOWED_COMMENTABLES = [
    Manga::class,
    Chapter::class,
  ];

  public function store(Request $request)
  {
    $validated = $request->validate([
      "comment" => "required|min:10|max:500",
      "type" => "required|string",
      "key" => "required|integer",
    ]);

    $commentableType = $validated["type"];
    if (!in_array($commentableType, self::ALLOWED_COMMENTABLES, true)) {
      return back()->with("error", __("Invalid commentable type."));
    }

    $content = $commentableType::findOrFail($validated["key"]);

    $comment = new Comment();
    $comment->user_id = auth()->id();
    $comment->content = $validated["comment"];
    $comment->commentable_type = $commentableType;
    $comment->commentable_id = $content->id;
    $comment->save();

    $slug = $content->slug ?? $content->id;
    Cache::forget("comments_" . $slug . $content->id);

    return back()->with("success", __("Comment has been commented"));
  }

  public function delete(Comment $comment)
  {
    if (
      $comment->user_id == auth()->id() ||
      auth()
        ->user()
        ->can("delete_comments")
    ) {
      $comment->delete();
      return back()->with("success", __("Comment deleted successfully"));
    }

    abort(403);
  }
}
