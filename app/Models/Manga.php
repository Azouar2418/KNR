<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\View;
use App\Models\Slider;
use App\Models\Chapter;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Manga
 *
 */
class Manga extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    "title",
    "slug",
    "description",
    "author",
    "artist",
    "official_links",
    "track_links",
    "alternative_titles",
    "cover",
    "views",
    "rate",
    "likes",
    "user_id",
    "releaseDate",
    "status",
  ];
  public function user()
  {
    return $this->belongsTo(User::class, "user_id");
  }

  public function taxables()
  {
    return $this->morphMany(Taxable::class, "taxable");
  }

  public function chapters()
  {
    return $this->hasMany(Chapter::class, "manga_id");
  }

  public function genres()
  {
    return $this->morphToMany(Taxonomy::class, "taxable")->where("type", "genre");
  }

  public function statuses()
  {
    return $this->morphToMany(Taxonomy::class, "taxable")->where("type", "status");
  }

  public function types()
  {
    return $this->morphToMany(Taxonomy::class, "taxable")->where("type", "type");
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
  }

  public function addView()
  {
    $today = Carbon::today();

    $updated = View::where([
      "model" => Manga::class,
      "key" => $this->id,
      "created_at" => $today,
    ])->increment("views");

    if ($updated) {
      return View::where([
        "model" => Manga::class,
        "key" => $this->id,
        "created_at" => $today,
      ])->first();
    }

    try {
      return View::create([
        "model" => Manga::class,
        "key" => $this->id,
        "views" => 1,
        "created_at" => $today,
      ]);
    } catch (\Illuminate\Database\QueryException $e) {
      View::where([
        "model" => Manga::class,
        "key" => $this->id,
        "created_at" => $today,
      ])->increment("views");

      return View::where([
        "model" => Manga::class,
        "key" => $this->id,
        "created_at" => $today,
      ])->first();
    }
  }

  public static function getTotalViews()
  {
    return View::where("model", self::class)->sum("views");
  }

  public function bookmarks()
  {
    return $this->morphMany(Bookmark::class, "bookmarkable");
  }

  public function slider()
  {
    return $this->morphMany(Slider::class, "slidable");
  }
}
