<?php

namespace App\Http\Controllers\Comments;

use App\Models\Comment;
use App\Models\CommentReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReactionController
{
  /**
   * Toggle like/dislike for a comment.
   *
   * @param  \App\Models\Comment  $comment
   * @return \Illuminate\Http\JsonResponse
   */
  public function toggleReaction(Request $request, Comment $comment)
  {
    $validated = $request->validate([
      "type" => "required|integer|in:1,2",
    ]);

    $userId = auth()->id();
    $type = (int) $validated["type"];

    $reaction = CommentReaction::where("user_id", $userId)
      ->where("comment_id", $comment->id)
      ->first();

    if ($reaction) {
      if ((int) $reaction->type === $type) {
        $reaction->delete();
        $this->forgetCommentCache($comment);
        return response()->json([
          "message" => "success",
          "type" => $type === 1 ? "removing" : "removing",
          "action" => "removed",
        ]);
      }

      $reaction->type = $type;
      $reaction->save();
      $this->forgetCommentCache($comment);
      return response()->json([
        "message" => "success",
        "type" => $type === 1 ? "adding" : "adding",
        "action" => "switched",
        "toggle" => true,
      ]);
    }

    $reaction = new CommentReaction();
    $reaction->user_id = $userId;
    $reaction->comment_id = $comment->id;
    $reaction->type = $type;
    $reaction->save();

    $this->forgetCommentCache($comment);

    return response()->json([
      "message" => "success",
      "type" => $type === 1 ? "adding" : "adding",
      "action" => "added",
    ]);
  }

  private function forgetCommentCache(Comment $comment): void
  {
    $content = $comment->commentable;
    if ($content) {
      $slug = $content->slug ?? $content->id;
      Cache::forget("comments_" . $slug . $content->id);
    }
  }
}
