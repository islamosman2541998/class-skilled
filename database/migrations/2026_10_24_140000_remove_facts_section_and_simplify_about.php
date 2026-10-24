<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Owner request (2026-10-24): the facts bar under the hero is removed, and the
 * about section keeps a title, text and one image (no short facts, no second image).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('home_sections')->where('type', 'facts')->delete();
        DB::table('language_lines')->where('group', 'enums')->where('key', 'section_type.facts')->delete();

        foreach (DB::table('home_sections')->where('type', 'about')->get(['id', 'data']) as $row) {
            $data = json_decode((string) $row->data, true) ?: [];
            $data['image_asset_id'] ??= ((array) ($data['image_asset_ids'] ?? []))[0] ?? null;
            unset($data['image_asset_ids'], $data['meta']);

            DB::table('home_sections')->where('id', $row->id)->update(['data' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        // Content removal is not reversible; re-add the section from the dashboard if needed.
    }
};
