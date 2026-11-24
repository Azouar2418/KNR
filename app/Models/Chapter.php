<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\View;
use App\Models\Manga;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Chapter
 *
 */
class Chapter extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = ["chapter_number", "title", "manga_id", "user_id", "content"];

  protected $casts = [
    "content" => "array",
  ];

  public function user()
  {
    return $this->belongsTo(User::class, "user_id");
  }

  public function manga()
  {
    return $this->belongsTo(Manga::class, "manga_id");
  }

  public function comments()
  {
    return $this->morphMany(Comment::class, "commentable");
  }

  public function views()
  {
    return $this->hasMany(View::class, "key")->where("model", self::class);
  }

  public function totalViews()
  {
    if (array_key_exists("views_sum", $this->attributes)) {
      return (int) ($this->attributes["views_sum"] ?? 0);
    }

    return View::where("model", self::class)
      ->where("key", $this->id)
      ->sum("views");
    // return $this->views->sum("views");
  }

  public function addView()
  {
    $today = Carbon::today();

    $updated = View::where([
      "model" => Chapter::class,
      "key" => $this->id,
      "created_at" => $today,
    ])->increment("views");

    if ($updated) {
      return View::where([
        "model" => Chapter::class,
        "key" => $this->id,
        "created_at" => $today,
      ])->first();
    }

    try {
      return View::create([
        "model" => Chapter::class,
        "key" => $this->id,
        "views" => 1,
        "created_at" => $today,
      ]);
    } catch (\Illuminate\Database\QueryException $e) {
      View::where([
        "model" => Chapter::class,
        "key" => $this->id,
        "created_at" => $today,
      ])->increment("views");

      return View::where([
        "model" => Chapter::class,
        "key" => $this->id,
        "created_at" => $today,
      ])->first();
    }
  }

  public static function getTotalViews()
  {
    return View::where("model", self::class)->sum("views");
  }
}
