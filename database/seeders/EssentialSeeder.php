<?php

namespace Database\Seeders;

use App\Enums\DataSource;
use App\Enums\MenuLocation;
use App\Enums\RequestType;
use App\Enums\SectionBackground;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Page;
use App\Models\RequestStatus;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\TranslationLoader\LanguageLine;

/**
 * Production-safe defaults. Idempotent: existing rows (and edits made in the
 * dashboard) are never overwritten.
 */
class EssentialSeeder extends Seeder
{
    public function run(): void
    {
        $this->permissions();
        $this->adminUser();
        $this->requestStatuses();
        $this->uiStrings();
        $this->legalPages();
        $this->menus();
        $this->homeSections();
    }

    private function permissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (Permissions::roles() as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function adminUser(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD are empty: no administrator was created.');

            return;
        }

        $user = User::query()->firstOrCreate(['email' => $email], [
            'name' => config('admin.name') ?: 'Administrator',
            'password' => $password,
            'locale' => 'ar',
            'is_active' => true,
        ]);

        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
        $user->assignRole('admin');
    }

    private function requestStatuses(): void
    {
        $statuses = [
            RequestType::Quote->value => [
                ['new', 'جديد', 'New', 'sky', true, false],
                ['contacted', 'تم التواصل', 'Contacted', 'amber', false, false],
                ['visit', 'معاينة', 'Site visit', 'violet', false, false],
                ['quote_sent', 'تم إرسال العرض', 'Quote sent', 'navy', false, false],
                ['won', 'تم التعاقد', 'Won', 'green', false, true],
                ['lost', 'لم يتم', 'Lost', 'red', false, true],
                ['cancelled', 'ملغي', 'Cancelled', 'gray', false, true],
            ],
            RequestType::Message->value => [
                ['new', 'جديدة', 'New', 'sky', true, false],
                ['read', 'مقروءة', 'Read', 'amber', false, false],
                ['replied', 'تم الرد', 'Replied', 'green', false, false],
                ['closed', 'مغلقة', 'Closed', 'gray', false, true],
            ],
            RequestType::Application->value => [
                ['new', 'جديد', 'New', 'sky', true, false],
                ['shortlisted', 'قائمة مختصرة', 'Shortlisted', 'amber', false, false],
                ['interview', 'مقابلة', 'Interview', 'violet', false, false],
                ['accepted', 'مقبول', 'Accepted', 'green', false, true],
                ['rejected', 'مرفوض', 'Rejected', 'red', false, true],
            ],
        ];

        foreach ($statuses as $type => $rows) {
            foreach ($rows as $index => [$key, $ar, $en, $color, $default, $final]) {
                RequestStatus::query()->firstOrCreate(
                    ['request_type' => $type, 'key' => $key],
                    ['name' => ['ar' => $ar, 'en' => $en], 'color' => $color, 'is_default' => $default, 'is_final' => $final, 'sort' => $index + 1],
                );
            }
        }
    }

    private function uiStrings(): void
    {
        foreach (self::defaultStrings() as $group => $lines) {
            foreach ($lines as $key => $text) {
                LanguageLine::query()->firstOrCreate(['group' => $group, 'key' => $key], ['text' => $text]);
            }
        }
    }

    /**
     * @return array<string, array<string, array{ar: string, en: string}>>
     */
    public static function defaultStrings(): array
    {
        return require database_path('seeders/data/ui_strings.php');
    }

    private function legalPages(): void
    {
        $note = [
            'ar' => '<p>هذا نص مبدئي يحتاج إلى مراجعة قانونية قبل النشر النهائي.</p>',
            'en' => '<p>This is placeholder copy that needs legal review before final publication.</p>',
        ];

        $pages = [
            'privacy' => [
                'title' => ['ar' => 'سياسة الخصوصية', 'en' => 'Privacy policy'],
                'slug' => ['ar' => 'سياسة-الخصوصية', 'en' => 'privacy-policy'],
                'excerpt' => ['ar' => 'كيف نجمع بياناتك ونستخدمها ونحميها.', 'en' => 'How we collect, use and protect your data.'],
                'body' => [
                    'ar' => '<h2>البيانات التي نجمعها</h2><p>نجمع البيانات التي ترسلها عبر نماذج الموقع (الاسم، الهاتف، البريد الإلكتروني، تفاصيل المشروع والملفات المرفقة) لغرض الرد على طلبك فقط.</p><h2>كيف نستخدم البيانات</h2><p>نستخدم بياناتك للتواصل معك بخصوص طلبك، ولا نبيعها أو نشاركها مع أي طرف ثالث إلا إذا طلب القانون ذلك.</p><h2>ملفات تعريف الارتباط</h2><p>يستخدم الموقع ملفات تعريف ارتباط ضرورية لتشغيله وقياس الزيارات بشكل مجمّع.</p><h2>التواصل</h2><p>لأي استفسار عن بياناتك، تواصل معنا عبر صفحة التواصل.</p>',
                    'en' => '<h2>Data we collect</h2><p>We collect the details you send through the website forms (name, phone, email, project details and attached files) only to respond to your request.</p><h2>How we use it</h2><p>We use your data to contact you about your request. We do not sell or share it with third parties unless required by law.</p><h2>Cookies</h2><p>The site uses essential cookies to operate and to measure visits in aggregate.</p><h2>Contact</h2><p>For any question about your data, contact us through the contact page.</p>',
                ],
            ],
            'terms' => [
                'title' => ['ar' => 'الشروط والأحكام', 'en' => 'Terms and conditions'],
                'slug' => ['ar' => 'الشروط-والأحكام', 'en' => 'terms-and-conditions'],
                'excerpt' => ['ar' => 'شروط استخدام موقع كلاس سكيلد.', 'en' => 'Terms of use of the Class Skilled website.'],
                'body' => [
                    'ar' => '<h2>استخدام الموقع</h2><p>المعلومات المنشورة على الموقع للتعريف بالشركة وخدماتها، ولا تمثل عرض سعر ملزمًا.</p><h2>عروض الأسعار</h2><p>تصدر عروض الأسعار بعد المعاينة وتخضع للعقد الموقع بين الطرفين.</p><h2>الملكية الفكرية</h2><p>جميع الصور والنصوص على الموقع مملوكة للشركة ولا يجوز إعادة استخدامها دون إذن.</p>',
                    'en' => '<h2>Use of the site</h2><p>Information on this website introduces the company and its services and is not a binding quotation.</p><h2>Quotations</h2><p>Quotations are issued after a site visit and are governed by the contract signed by both parties.</p><h2>Intellectual property</h2><p>All photos and text on this site belong to the company and may not be reused without permission.</p>',
                ],
            ],
        ];

        foreach ($pages as $page) {
            if (Page::query()->where('slug->ar', $page['slug']['ar'])->exists()) {
                continue;
            }

            Page::query()->create([
                'title' => $page['title'],
                'slug' => $page['slug'],
                'excerpt' => $page['excerpt'],
                'hero_type' => 'none',
                'blocks' => [['type' => 'rich_text', 'data' => ['html' => $page['body']]], ['type' => 'rich_text', 'data' => ['html' => $note]]],
                'status' => 'published',
                'published_at' => now(),
                'show_in_sitemap' => true,
            ]);
        }
    }

    private function menus(): void
    {
        foreach (MenuLocation::cases() as $location) {
            if (Menu::query()->where('location', $location->value)->exists()) {
                continue;
            }

            $menu = Menu::query()->create([
                'location' => $location,
                'name' => [
                    'ar' => __('enums.menu_location.'.$location->value, [], 'ar'),
                    'en' => __('enums.menu_location.'.$location->value, [], 'en'),
                ],
                'use_header_menu' => $location === MenuLocation::Mobile,
                'is_active' => true,
            ]);

            foreach (DefaultMenus::items($location) as $index => $item) {
                $this->createMenuItem($menu, $item, $index + 1);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function createMenuItem(Menu $menu, array $item, int $sort, ?int $parentId = null): void
    {
        $children = $item['children'] ?? [];
        unset($item['children']);

        $created = $menu->items()->create(array_merge([
            'parent_id' => $parentId,
            'visibility' => 'all',
            'is_active' => true,
            'sort' => $sort,
        ], $item));

        foreach ($children as $index => $child) {
            $this->createMenuItem($menu, $child, $index + 1, $created->id);
        }
    }

    private function homeSections(): void
    {
        if (HomeSection::query()->exists()) {
            return;
        }

        foreach (DefaultHomeSections::all() as $index => $section) {
            HomeSection::query()->create(array_merge([
                'source' => DataSource::All,
                'background' => SectionBackground::White,
                'is_visible' => true,
                'sort' => $index + 1,
            ], $section));
        }
    }
}
