<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Owner request: the home services section has no background photo (solid dark navy).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('home_sections')->where('type', 'services')->get(['id', 'background', 'data'])->each(function (object $section): void {
            $data = json_decode((string) $section->data, true) ?: [];
            $data['bg_asset_id'] = null;

            DB::table('home_sections')->where('id', $section->id)->update([
                'background' => $section->background === 'dark_photo' ? 'dark' : $section->background,
                'data' => json_encode($data),
            ]);
        });
    }

    public function down(): void
    {
        // The removed photo is not restored.
    }
};
