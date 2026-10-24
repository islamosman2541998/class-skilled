<?php

namespace Database\Seeders;

use App\Enums\DataSource;
use App\Enums\DynamicSource;
use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Models\Page;

/**
 * Default menu items per location (production-safe: system pages, dynamic
 * groups and the seeded legal pages). Demo content adds About / Studios links.
 */
class DefaultMenus
{
    /**
     * Menu name; for footer columns it is the visible column title.
     *
     * @return array{ar: string, en: string}
     */
    public static function name(MenuLocation $location): array
    {
        return match ($location) {
            MenuLocation::Header => ['ar' => 'القائمة الرئيسية', 'en' => 'Main menu'],
            MenuLocation::Topbar => ['ar' => 'روابط الشريط العلوي', 'en' => 'Top bar links'],
            MenuLocation::Footer1 => ['ar' => 'الشركة', 'en' => 'Company'],
            MenuLocation::Footer2 => ['ar' => 'الخدمات', 'en' => 'Services'],
            MenuLocation::Footer3 => ['ar' => 'روابط مفيدة', 'en' => 'Useful links'],
            MenuLocation::FooterLegal => ['ar' => 'روابط قانونية', 'en' => 'Legal'],
            MenuLocation::Mobile => ['ar' => 'قائمة الجوال', 'en' => 'Mobile menu'],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function items(MenuLocation $location): array
    {
        return match ($location) {
            MenuLocation::Header => [
                ['type' => MenuItemType::Home],
                [
                    'type' => MenuItemType::DynamicGroup,
                    'label' => ['ar' => 'الخدمات', 'en' => 'Services'],
                    'system_route' => 'services.index',
                    'dynamic_source' => DynamicSource::Services,
                    'dynamic_mode' => DataSource::All,
                    'dynamic_limit' => 8,
                    'show_all_link' => true,
                    'show_descriptions' => true,
                ],
                [
                    'type' => MenuItemType::DynamicGroup,
                    'label' => ['ar' => 'المشاريع', 'en' => 'Projects'],
                    'system_route' => 'projects.index',
                    'dynamic_source' => DynamicSource::ProjectCategories,
                    'dynamic_mode' => DataSource::All,
                    'dynamic_limit' => 8,
                    'show_all_link' => true,
                    'show_descriptions' => false,
                ],
                ['type' => MenuItemType::SystemPage, 'system_route' => 'posts.index'],
                ['type' => MenuItemType::SystemPage, 'system_route' => 'contact'],
            ],
            MenuLocation::Footer1 => [
                ['type' => MenuItemType::SystemPage, 'system_route' => 'projects.index'],
                ['type' => MenuItemType::SystemPage, 'system_route' => 'posts.index'],
                ['type' => MenuItemType::SystemPage, 'system_route' => 'careers.index'],
                ['type' => MenuItemType::SystemPage, 'system_route' => 'faq'],
                ['type' => MenuItemType::SystemPage, 'system_route' => 'contact'],
            ],
            MenuLocation::Footer2 => [
                [
                    'type' => MenuItemType::DynamicGroup,
                    'label' => ['ar' => 'الخدمات', 'en' => 'Services'],
                    'system_route' => 'services.index',
                    'dynamic_source' => DynamicSource::Services,
                    'dynamic_mode' => DataSource::All,
                    'dynamic_limit' => 12,
                    'show_all_link' => false,
                    'show_descriptions' => false,
                ],
            ],
            MenuLocation::FooterLegal => self::legalItems(),
            default => [],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function legalItems(): array
    {
        $items = [];

        foreach (['سياسة-الخصوصية', 'الشروط-والأحكام'] as $slug) {
            $page = Page::query()->where('slug->ar', $slug)->first();

            if ($page !== null) {
                $items[] = ['type' => MenuItemType::Page, 'linkable_type' => 'page', 'linkable_id' => $page->id];
            }
        }

        return $items;
    }
}
