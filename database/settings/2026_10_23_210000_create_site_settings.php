<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Default values for every settings group (CLAUDE.md §1 and _design/content/content.md).
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->addGroup('general', [
            'site_name' => ['ar' => 'كلاس سكيلد', 'en' => 'Class Skilled'],
            'tagline' => ['ar' => 'للمقاولات العامة', 'en' => 'General Contracting'],
            'footer_about' => [
                'ar' => 'شركة مقاولات عامة في أبوظبي، مصنفة في مقاولات المباني من بلدية مدينة أبوظبي.',
                'en' => 'A general contracting company in Abu Dhabi, classified for building contracting by Abu Dhabi City Municipality.',
            ],
            'logo_asset_id' => null,
            'logo_white_asset_id' => null,
            'favicon_asset_id' => null,
            'default_locale' => 'ar',
        ]);

        $this->addGroup('company', [
            'legal_name' => [
                'ar' => 'كلاس سكيلد للمقاولات العامة ذ.م.م – ش.ش.و',
                'en' => 'Class Skilled General Contracting L.L.C – S.P.C',
            ],
            'trade_licence_no' => 'CN-5898460',
            'licence_expiry' => '2029-05-11',
            'licence_authority' => ['ar' => 'دائرة التنمية الاقتصادية – أبوظبي', 'en' => 'Abu Dhabi Department of Economic Development'],
            'classification' => ['ar' => 'الفئة الخامسة، مقاولات المباني', 'en' => 'Fifth category, Building Contracting'],
            'classification_authority' => ['ar' => 'بلدية مدينة أبوظبي', 'en' => 'Abu Dhabi City Municipality'],
            'classification_licence_no' => 'D12-2026-5898460-LI1',
            'classification_expiry' => '2028-03-25',
            'adcci_no' => '8800105771',
            'founded_year' => 2025,
        ]);

        $this->addGroup('contact', [
            'phones' => ['+971 58 982 0017', '+971 52 764 9086'],
            'whatsapp_number' => '+971 52 764 9086',
            'whatsapp_message' => [
                'ar' => 'مرحبًا، أود الاستفسار عن خدماتكم.',
                'en' => 'Hello, I would like to ask about your services.',
            ],
            'emails' => ['classskilled@gmail.com'],
            'address' => [
                'ar' => 'مصفح M44، مبنى A، مكتب 10، أبوظبي، الإمارات العربية المتحدة',
                'en' => 'Mussafah M44, Building A, Office 10, Abu Dhabi, UAE',
            ],
            'map_lat' => 24.3486,
            'map_lng' => 54.5036,
            'map_embed_url' => null,
            'working_hours' => [
                [
                    'days' => ['ar' => 'السبت إلى الخميس', 'en' => 'Saturday to Thursday'],
                    'hours' => ['ar' => '8:00 ص إلى 6:00 م', 'en' => '8:00 am to 6:00 pm'],
                ],
            ],
        ]);

        $this->addGroup('social', [
            'instagram' => null, 'facebook' => null, 'linkedin' => null, 'x' => null,
            'tiktok' => null, 'youtube' => null, 'snapchat' => null,
        ]);

        $this->addGroup('appearance', [
            'heading_font' => 'cairo',
            'body_font' => 'tajawal',
            'base_font_size' => 15.5,
            'line_height_ar' => 1.85,
            'colors' => [
                'navy' => '#2B4168', 'deep' => '#0C122B', 'sky' => '#4FC3EE', 'ink' => '#24324D',
                'muted' => '#5D6A80', 'line' => '#E8ECF1', 'paper' => '#F5F7FA',
            ],
        ]);

        $this->addGroup('seo', [
            'title_suffix' => ['ar' => 'كلاس سكيلد للمقاولات العامة', 'en' => 'Class Skilled General Contracting'],
            'default_description' => [
                'ar' => 'شركة مقاولات عامة في أبوظبي مصنفة من بلدية مدينة أبوظبي: إنشاء الفلل والمباني، التشطيبات، أرضيات الإيبوكسي، العزل وتجهيز استوديوهات التصوير.',
                'en' => 'A general contractor in Abu Dhabi classified by Abu Dhabi City Municipality: villas and buildings, finishing, epoxy flooring, insulation and film studio fit-out.',
            ],
            'default_og_asset_id' => null,
            'google_verification' => null,
            'bing_verification' => null,
            'robots_txt' => "User-agent: *\nDisallow: /بحث\nDisallow: /en/search\n",
            'schema_extra' => [],
        ]);

        $this->addGroup('tracking', [
            'ga4_id' => null, 'gtm_id' => null, 'meta_pixel_id' => null,
            'head_scripts' => null, 'body_scripts' => null,
        ]);

        $this->addGroup('notifications', [
            'quote_emails' => ['classskilled@gmail.com'],
            'contact_emails' => ['classskilled@gmail.com'],
            'careers_emails' => ['classskilled@gmail.com'],
            'from_name' => 'Class Skilled',
            'autoreply_enabled' => true,
            'autoreply_subject' => [
                'ar' => 'استلمنا طلبك رقم {number}',
                'en' => 'We received your request {number}',
            ],
            'autoreply_body' => [
                'ar' => "مرحبًا {name}،\n\nشكرًا لتواصلك مع كلاس سكيلد. استلمنا طلبك رقم {number} وسيتواصل معك فريقنا قريبًا لتحديد الخطوات التالية.",
                'en' => "Hello {name},\n\nThank you for contacting Class Skilled. We received your request {number} and our team will contact you shortly about the next steps.",
            ],
        ]);

        $this->addGroup('forms', [
            'emirates' => [
                ['ar' => 'أبوظبي', 'en' => 'Abu Dhabi'],
                ['ar' => 'دبي', 'en' => 'Dubai'],
                ['ar' => 'الشارقة', 'en' => 'Sharjah'],
                ['ar' => 'عجمان', 'en' => 'Ajman'],
                ['ar' => 'أم القيوين', 'en' => 'Umm Al Quwain'],
                ['ar' => 'رأس الخيمة', 'en' => 'Ras Al Khaimah'],
                ['ar' => 'الفجيرة', 'en' => 'Fujairah'],
            ],
            'facility_types' => [
                ['ar' => 'فيلا', 'en' => 'Villa'],
                ['ar' => 'مبنى سكني', 'en' => 'Residential building'],
                ['ar' => 'مكتب', 'en' => 'Office'],
                ['ar' => 'محل أو معرض', 'en' => 'Shop or showroom'],
                ['ar' => 'مستودع أو مصنع', 'en' => 'Warehouse or factory'],
                ['ar' => 'استوديو تصوير', 'en' => 'Film studio'],
                ['ar' => 'مبنى حكومي', 'en' => 'Government building'],
                ['ar' => 'أخرى', 'en' => 'Other'],
            ],
            'project_states' => [
                ['ar' => 'أرض فارغة / مشروع جديد', 'en' => 'Empty plot / new project'],
                ['ar' => 'هيكل قائم', 'en' => 'Existing structure'],
                ['ar' => 'مبنى قائم يحتاج تشطيب', 'en' => 'Existing building needing finishing'],
                ['ar' => 'ترميم أو تعديل', 'en' => 'Renovation or alteration'],
            ],
            'start_options' => [
                ['ar' => 'فورًا', 'en' => 'Immediately'],
                ['ar' => 'خلال شهر', 'en' => 'Within a month'],
                ['ar' => 'خلال 3 أشهر', 'en' => 'Within 3 months'],
                ['ar' => 'لم أحدد بعد', 'en' => 'Not decided yet'],
            ],
            'contact_subjects' => [
                ['ar' => 'استفسار عام', 'en' => 'General enquiry'],
                ['ar' => 'استفسار عن خدمة', 'en' => 'Service enquiry'],
                ['ar' => 'متابعة طلب', 'en' => 'Follow up on a request'],
                ['ar' => 'موردون وشراكات', 'en' => 'Suppliers & partnerships'],
            ],
            'max_upload_mb' => 10,
            'allowed_extensions' => ['pdf', 'dwg', 'jpg', 'jpeg', 'png', 'webp'],
            'turnstile_site_key' => null,
            'turnstile_secret' => null,
            'honeypot_enabled' => true,
        ]);

        $this->addGroup('media', [
            'max_image_mb' => 10,
            'max_video_mb' => 60,
            'max_image_dimension' => 2560,
            'webp_quality' => 80,
        ]);

        $this->addGroup('maintenance', [
            'enabled' => false,
            'message' => [
                'ar' => 'نعمل حاليًا على تحديث الموقع، وسنعود قريبًا.',
                'en' => 'We are updating the website and will be back shortly.',
            ],
            'allowed_ips' => [],
        ]);

        $this->addGroup('system', [
            'backup_keep' => 5,
            'admin_session_minutes' => 120,
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function addGroup(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            $this->migrator->add($group.'.'.$key, $value);
        }
    }
};
