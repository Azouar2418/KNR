<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deduplicate daily view rows before adding unique index.
        $duplicates = DB::table('views')
            ->select('model', 'key', 'created_at', DB::raw('MIN(id) as keep_id'), DB::raw('SUM(views) as total_views'))
            ->groupBy('model', 'key', 'created_at')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('views')
                ->where('id', $duplicate->keep_id)
                ->update(['views' => $duplicate->total_views]);

            DB::table('views')
                ->where('model', $duplicate->model)
                ->where('key', $duplicate->key)
                ->where('created_at', $duplicate->created_at)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('views', function (Blueprint $table) {
            $table->unique(['model', 'key', 'created_at'], 'views_model_key_created_at_unique');
        });
    }

    public function down(): void
    {
        Schema::table('views', function (Blueprint $table) {
            $table->dropUnique('views_model_key_created_at_unique');
        });
    }
};
