<?php

namespace App\Helpers;
use Carbon\Carbon;
use App\Models\Manga;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
  /**
   * Remember a value in the cache. Pass null as $cacheExpiration to use the
   * default of three hours.
   */
  public static function remember($cacheKey, $cacheExpiration, $callback)
  {
    if (!$cacheExpiration) {
      $cacheExpiration = Carbon::now()->addHours(3);
    }

    return Cache::remember($cacheKey, $cacheExpiration, $callback);
  }

  public static function forgetCache($key = null)
  {
    if ($key) {
      Cache::forget($key);
    } else {
      $cacheKeys = ["popular_mangas", "slider_mangas", "latest_mangas"];

      foreach ($cacheKeys as $cacheKey) {
        Cache::forget($cacheKey);
      }

      static::clearCachedChaptersMangas();
    }
  }

  public static function clearCachedChaptersMangas()
  {
    $itemsPerPage = 24;
    $totalMangas = Manga::whereHas("chapters")->count();
    $totalPages = ceil($totalMangas / $itemsPerPage);

    for ($page = 1; $page <= $totalPages; $page++) {
      $cacheKey = "chapters_page_" . $page;
      Cache::forget($cacheKey);
    }
  }
}
