<?php

use Database\Seeders\EssentialSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\TranslationLoader\LanguageLine;

/**
 * UI strings for the media gallery video player (group `media`) on databases seeded before it existed.
 * Existing rows are left as edited in the dashboard.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (EssentialSeeder::defaultStrings()['media'] ?? [] as $key => $text) {
            LanguageLine::query()->firstOrCreate(['group' => 'media', 'key' => $key], ['text' => $text]);
        }
    }

    public function down(): void
    {
        LanguageLine::query()->where('group', 'media')->delete();
    }
};
