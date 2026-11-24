<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\View;

class AdsController extends Controller
{
    public function redirect(Ad $ad)
    {
        if (!$ad->is_active) {
            abort(404);
        }

        $link = $ad->link;
        if (!is_string($link) || !preg_match('/\Ahttps?:\/\/[^\s<>"\']+\z/i', $link)) {
            abort(404);
        }

        View::create([
            'model' => Ad::class,
            'key' => $ad->id,
            'views' => 1,
        ]);

        return redirect()->away($link);
    }
}
