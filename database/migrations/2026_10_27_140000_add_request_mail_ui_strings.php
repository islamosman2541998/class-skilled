<?php

use Database\Seeders\EssentialSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Spatie\TranslationLoader\LanguageLine;

/**
 * UI strings for the redesigned request e-mails (group `mail`) on databases seeded before them.
 * Existing rows are left as edited in the dashboard.
 */
return new class extends Migration
{
    private const KEYS = [
        'type_quote', 'type_message', 'type_application', 'action_call', 'action_whatsapp', 'action_reply',
        'section_contact', 'section_details_quote', 'section_details_message', 'section_details_application',
        'attachments', 'attachments_attached', 'attachments_in_dashboard', 'section_more', 'meta_page', 'meta_language',
        'meta_device', 'device_mobile', 'device_tablet', 'device_desktop', 'language_ar', 'language_en',
        'autoreply_heading', 'reference', 'your_request', 'contact_us', 'autoreply_reason',
    ];

    public function up(): void
    {
        $strings = EssentialSeeder::defaultStrings()['mail'] ?? [];

        foreach (self::KEYS as $key) {
            if (isset($strings[$key])) {
                LanguageLine::query()->firstOrCreate(['group' => 'mail', 'key' => $key], ['text' => $strings[$key]]);
            }
        }

        foreach (['ar', 'en'] as $locale) {
            Cache::forget(LanguageLine::getCacheKey('mail', $locale));
        }
    }

    public function down(): void
    {
        LanguageLine::query()->where('group', 'mail')->whereIn('key', self::KEYS)->delete();
    }
};
