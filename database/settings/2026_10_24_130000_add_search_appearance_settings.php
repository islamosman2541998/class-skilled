<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * How the site appears in Google results: site name (+ alternate), home page
 * title and description, the icon next to the result and the organisation logo.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.search_site_name', ['ar' => 'كلاس سكيلد للمقاولات العامة', 'en' => 'Class Skilled General Contracting']);
        $this->migrator->add('seo.search_alternate_name', ['ar' => 'كلاس سكيلد', 'en' => 'Class Skilled']);
        $this->migrator->add('seo.home_title', [
            'ar' => 'كلاس سكيلد للمقاولات العامة | مقاولات وتشطيبات في أبوظبي',
            'en' => 'Class Skilled General Contracting | Contracting & finishing in Abu Dhabi',
        ]);
        $this->migrator->add('seo.home_description', [
            'ar' => 'شركة مقاولات عامة في أبوظبي: إنشاء الفلل والمباني، التشطيبات الداخلية والخارجية، أرضيات الإيبوكسي، العزل وتجهيز استوديوهات التصوير. اطلب عرض سعر.',
            'en' => 'General contractor in Abu Dhabi: villas and buildings, interior and exterior finishing, epoxy flooring, insulation and film studio fit-out. Request a quote.',
        ]);
        $this->migrator->add('seo.search_icon_asset_id', null);
        $this->migrator->add('seo.search_logo_asset_id', null);
    }
};
