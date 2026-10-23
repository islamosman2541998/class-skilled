<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\TranslationLoader\LanguageLine;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'title' => ['ar' => 'إعداد المشروع', 'en' => 'Project setup'],
            'route' => ['ar' => 'اسم المسار', 'en' => 'Route name'],
            'locale' => ['ar' => 'اللغة الحالية', 'en' => 'Current locale'],
            'direction' => ['ar' => 'اتجاه الصفحة', 'en' => 'Page direction'],
        ] as $key => $text) {
            LanguageLine::updateOrCreate(
                ['group' => 'setup', 'key' => $key],
                ['text' => $text],
            );
        }
    }
}
