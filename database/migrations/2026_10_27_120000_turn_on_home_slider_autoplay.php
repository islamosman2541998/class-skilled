<?php

use App\Services\SiteCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Owner request: every home slider moves on its own. Turns the per-section "autoplay" toggle on for the
 * card sliders (the hero keeps its own seconds setting); it can still be switched off per section.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('home_sections')->where('type', '!=', 'hero')->get(['id', 'data'])->each(function (object $section): void {
            $data = json_decode((string) $section->data, true) ?: [];

            if (array_key_exists('autoplay', $data) && $data['autoplay'] !== true) {
                $data['autoplay'] = true;
                DB::table('home_sections')->where('id', $section->id)->update(['data' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
            }
        });

        app(SiteCache::class)->flush();
    }

    public function down(): void
    {
        // The previous per-section choices are not kept.
    }
};
