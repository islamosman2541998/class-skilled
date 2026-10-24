<?php

namespace Database\Seeders;

use App\Enums\DataSource;
use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Enums\SectionType;
use App\Models\Asset;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\JobPost;
use App\Models\MediaFolder;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\User;
use App\Services\AssetUploader;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use Illuminate\Database\Seeder;

/**
 * Local/staging demo content built from _design/ (photos, PDFs, approved copy).
 * Items marked [sample] in content.md are placeholders, never real client work.
 */
class DemoContentSeeder extends Seeder
{
    /** @var array<string, Asset> */
    private array $photos = [];

    /** @var array<string, Asset> */
    private array $docs = [];

    /** @var array<int, Service> */
    private array $services = [];

    /** @var array<string, ProjectCategory> */
    private array $categories = [];

    public function run(AssetUploader $uploader): void
    {
        if (Service::query()->exists()) {
            $this->command?->info('Demo content already present, skipped.');

            return;
        }

        $this->importPhotos($uploader);
        $this->importDocuments($uploader);
        $this->brandSettings($uploader);
        $this->services();
        $this->projects();
        $this->posts();
        $this->faqs();
        $this->certificates();
        $this->jobs();
        $aboutPage = $this->aboutPage();
        $this->homeSections($aboutPage);
        $this->menus($aboutPage);
    }

    private function importPhotos(AssetUploader $uploader): void
    {
        $folder = MediaFolder::query()->create(['name' => ['ar' => 'المشاريع', 'en' => 'Projects']]);

        $alts = [
            'studio-1' => ['تجهيز استوديو تصوير سينمائي في أبوظبي', 'Film studio fit-out in Abu Dhabi', 'center'],
            'studio-2' => ['هياكل الإضاءة المعلقة في استوديو تصوير', 'Suspended lighting rig in a film studio', 'top'],
            'studio-3' => ['بناء ديكورات موقع تصوير', 'Building a film set', 'center'],
            'office' => ['مكتب إداري بعد أعمال التشطيب', 'Administrative office after finishing works', 'center'],
            'office-2' => ['تشطيب مكاتب إدارية بإضاءة مخفية', 'Office finishing with concealed lighting', 'center'],
            'stairs' => ['درج بإضاءة مخفية وواجهة زجاجية', 'Staircase with concealed lighting and a glass facade', 'center'],
            'corridor' => ['ممر داخلي بتجاليد خشبية', 'Interior corridor with wood cladding', 'center'],
            'slab-1' => ['صب سقف خرساني لمبنى تجاري', 'Concrete slab pour for a commercial building', 'center'],
            'slab-2' => ['أعمال سقف خرساني وحديد تسليح', 'Concrete slab and reinforcement works', 'center'],
            'rebar' => ['أعمال حديد التسليح في الموقع', 'Rebar works on site', 'center'],
            'concrete' => ['أعمال الخرسانة والعزل', 'Concrete and insulation works', 'center'],
            'paving' => ['تبليط ساحة خارجية', 'Outdoor courtyard paving', 'center'],
            'helmet' => ['خوذة السلامة في موقع العمل', 'Safety helmet on site', 'center'],
        ];

        foreach ($alts as $name => [$ar, $en, $focal]) {
            $path = base_path('_design/photos/'.$name.'.jpg');

            if (! is_file($path)) {
                continue;
            }

            $this->photos[$name] = $uploader->uploadImage($path, [
                'folder_id' => $folder->id,
                'alt' => ['ar' => $ar, 'en' => $en],
                'title' => ['ar' => $ar, 'en' => $en],
                'focal' => $focal,
            ]);
        }
    }

    private function importDocuments(AssetUploader $uploader): void
    {
        $folder = MediaFolder::query()->create(['name' => ['ar' => 'الوثائق', 'en' => 'Documents']]);

        $docs = [
            'licence' => ['business-license-CN-5898460.pdf', 'الرخصة التجارية CN-5898460', 'Trade licence CN-5898460'],
            'classification' => ['classification-certificate-CN-5898460.pdf', 'شهادة التصنيف – الفئة الخامسة، مقاولات المباني', 'Classification certificate – Fifth category, Building Contracting'],
            'profile' => ['company-profile-web.pdf', 'بروفايل الشركة', 'Company profile'],
        ];

        foreach ($docs as $key => [$file, $ar, $en]) {
            $path = base_path('_design/docs/'.$file);

            if (is_file($path)) {
                $this->docs[$key] = $uploader->uploadDocument($path, [
                    'folder_id' => $folder->id,
                    'title' => ['ar' => $ar, 'en' => $en],
                ]);
            }
        }
    }

    private function brandSettings(AssetUploader $uploader): void
    {
        $folder = MediaFolder::query()->create(['name' => ['ar' => 'الهوية', 'en' => 'Brand']]);
        $ids = [];

        foreach (['logo' => 'logo.png', 'logo_white' => 'logo-white.png', 'mark' => 'mark.png'] as $key => $file) {
            $path = base_path('_design/brand/'.$file);

            if (is_file($path)) {
                $ids[$key] = $uploader->uploadImage($path, [
                    'folder_id' => $folder->id,
                    'alt' => ['ar' => 'شعار كلاس سكيلد', 'en' => 'Class Skilled logo'],
                ])->id;
            }
        }

        $general = app(GeneralSettings::class);
        $general->logo_asset_id = $ids['logo'] ?? null;
        $general->logo_white_asset_id = $ids['logo_white'] ?? null;
        $general->favicon_asset_id = $ids['mark'] ?? null;
        $general->save();

        if (isset($this->photos['studio-1'])) {
            $seo = app(SeoSettings::class);
            $seo->default_og_asset_id = $this->photos['studio-1']->id;
            $seo->save();
        }
    }

    private function services(): void
    {
        $rows = [
            1 => ['إنشاء الفلل والمباني السكنية', 'Villas & residential buildings', 'من الأساسات حتى التسليم، بمواصفات واضحة وجدول زمني ملتزم.', 'From foundations to handover, with clear specifications and a committed schedule.', 'slab-2'],
            2 => ['المباني التجارية والحكومية', 'Commercial & government buildings', 'تنفيذ المباني التجارية والمشاريع العامة وفق اشتراطات الجهات المختصة.', 'Commercial buildings and public projects built to authority requirements.', 'slab-1'],
            3 => ['التشطيبات والديكورات الداخلية والخارجية', 'Interior & exterior finishing', 'جبس ودهانات وأرضيات وواجهات وأعمال خشبية بتشطيب نهائي دقيق.', 'Gypsum, paint, flooring, facades and joinery with a precise final finish.', 'office'],
            4 => ['أرضيات الإيبوكسي', 'Epoxy flooring', 'للمستودعات والمعارض والمواقف والمطابخ التجارية، بسماكات وأنظمة متعددة.', 'For warehouses, showrooms, car parks and commercial kitchens, in several systems.', null],
            5 => ['عزل الأرضيات والأسطح', 'Floor & roof insulation', 'عزل مائي وحراري للأسطح والأرضيات يحمي المبنى لسنوات.', 'Waterproofing and thermal insulation that protects the building for years.', 'concrete'],
            6 => ['العزل الصوتي', 'Acoustic insulation', 'للاستوديوهات وقاعات الاجتماعات والمساكن، بقياس وتنفيذ مدروس.', 'For studios, meeting rooms and homes, measured and installed with care.', 'studio-3'],
            7 => ['تجهيز استوديوهات التصوير السينمائي', 'Film studio fit-out', 'هياكل الإضاءة وبناء الديكورات وتجهيز مواقع التصوير بالكامل.', 'Lighting rigs, set construction and complete preparation of filming locations.', 'studio-1'],
            8 => ['البنية التحتية والخرسانة والترميمات', 'Infrastructure, concrete & renovation', 'أعمال الخرسانة والمرافق والتوسعات والترميمات الإنشائية.', 'Concrete, utilities, extensions and structural renovation works.', 'rebar'],
        ];

        foreach ($rows as $index => [$ar, $en, $summaryAr, $summaryEn, $photo]) {
            $isStudio = $index === 7;

            $service = Service::query()->create([
                'title' => ['ar' => $ar, 'en' => $en],
                'summary' => ['ar' => $summaryAr, 'en' => $summaryEn],
                'excerpt' => ['ar' => $summaryAr, 'en' => $summaryEn],
                'is_featured' => in_array($index, [1, 3, 7], true),
                'is_active' => true,
                'sort' => $index,
                'scope' => $isStudio ? [
                    'ar' => ['هياكل التراس المعدنية وتعليق وحدات الإضاءة', 'بناء الديكورات والسِّتّات بالخشب والجبس والزجاج', 'العزل الصوتي للقاعات وغرف التحكم', 'الدهانات والتشطيبات الخاصة بالتصوير', 'الفك والنقل والتخزين بعد انتهاء التصوير'],
                    'en' => ['Steel truss structures and lighting rig suspension', 'Sets and décor built in timber, gypsum and glass', 'Acoustic insulation for halls and control rooms', 'Paint and special finishes for filming', 'Strike, transport and storage after the shoot'],
                ] : null,
                'stages' => $isStudio ? [
                    'ar' => ['استلام التصميم وجدول التصوير من فريق الإنتاج', 'معاينة الموقع وحصر الكميات وتقديم عرض السعر', 'التصنيع والتركيب في الموقع بإشراف مهندس', 'التسليم والمتابعة أثناء التصوير ثم الفك'],
                    'en' => ['Receive the design and shooting schedule from the production team', 'Site survey, quantity take-off and quotation', 'Fabrication and on-site installation supervised by an engineer', 'Handover, support during the shoot, then strike'],
                ] : null,
                'blocks' => $isStudio ? [[
                    'type' => 'rich_text',
                    'data' => ['html' => [
                        'ar' => '<p>ننفذ مواقع التصوير من الهيكل حتى آخر تفصيلة، بالتنسيق مع فريق الإنتاج ومدير التصوير، ونلتزم بمواعيد الإنتاج الضيقة دون التنازل عن السلامة.</p>',
                        'en' => '<p>We build filming locations from the structure to the last detail, coordinating with the production team and the director of photography, and we keep to tight production schedules without compromising safety.</p>',
                    ]],
                ]] : [],
            ]);

            if ($photo !== null && isset($this->photos[$photo])) {
                $service->syncAssets('cover', [$this->photos[$photo]->id]);
            }

            if ($isStudio) {
                $service->syncAssets('gallery', array_filter([
                    $this->photos['studio-1']->id ?? null,
                    $this->photos['studio-2']->id ?? null,
                    $this->photos['studio-3']->id ?? null,
                ]));

                $service->faqs()->create([
                    'question' => ['ar' => 'هل تتولون الفك بعد انتهاء التصوير؟', 'en' => 'Do you handle the strike after filming ends?'],
                    'answer' => ['ar' => 'نعم، يمكن إضافة الفك والنقل والتخزين ضمن نطاق العقد، ويتم جدولته حسب آخر يوم تصوير.', 'en' => 'Yes. Strike, transport and storage can be included in the contract and scheduled around the last shooting day.'],
                    'is_active' => true,
                ]);
            }

            $this->services[$index] = $service;
        }
    }

    private function projects(): void
    {
        $categories = [
            'studio' => ['تجهيز استوديوهات', 'Studio fit-out'],
            'finishing' => ['تشطيبات وديكورات', 'Finishing & décor'],
            'construction' => ['إنشاءات وخرسانة', 'Construction & concrete'],
            'infrastructure' => ['بنية تحتية', 'Infrastructure'],
        ];

        foreach ($categories as $key => [$ar, $en]) {
            $this->categories[$key] = ProjectCategory::query()->create(['name' => ['ar' => $ar, 'en' => $en], 'is_active' => true]);
        }

        $projects = [
            ['تجهيز استوديو تصوير سينمائي', 'Film studio fit-out', 'studio', [7, 6], ['studio-1', 'studio-2'], 'مصفح، أبوظبي', 'Mussafah, Abu Dhabi', 2026, 850, '7 أسابيع', '7 weeks', true],
            ['تشطيب مكاتب إدارية', 'Administrative office finishing', 'finishing', [3], ['office', 'office-2', 'stairs', 'corridor'], 'أبوظبي', 'Abu Dhabi', 2025, 620, '10 أسابيع', '10 weeks', true],
            ['تركيب هياكل الإضاءة لاستوديو', 'Lighting rig installation for a studio', 'studio', [7], ['studio-2'], 'مصفح، أبوظبي', 'Mussafah, Abu Dhabi', 2026, null, '3 أسابيع', '3 weeks', false],
            ['درج بإضاءة مخفية وواجهة زجاجية', 'Staircase with concealed lighting and a glass facade', 'finishing', [3], ['stairs'], 'أبوظبي', 'Abu Dhabi', 2025, null, '4 أسابيع', '4 weeks', false],
            ['صب سقف خرساني لمبنى تجاري', 'Concrete slab for a commercial building', 'construction', [2, 8], ['slab-1'], 'مصفح، أبوظبي', 'Mussafah, Abu Dhabi', 2025, 1200, '5 أسابيع', '5 weeks', true],
            ['أعمال حديد التسليح', 'Reinforcement steel works', 'construction', [1, 8], ['rebar', 'slab-2'], 'أبوظبي', 'Abu Dhabi', 2025, 900, '6 أسابيع', '6 weeks', false],
            ['تبليط ساحة خارجية', 'Outdoor courtyard paving', 'infrastructure', [8], ['paving'], 'مصفح، أبوظبي', 'Mussafah, Abu Dhabi', 2026, 1500, '4 أسابيع', '4 weeks', false],
            ['ممر داخلي بتجاليد خشبية', 'Interior corridor with wood cladding', 'finishing', [3], ['corridor'], 'أبوظبي', 'Abu Dhabi', 2026, 140, '3 أسابيع', '3 weeks', false],
            ['بناء ديكورات موقع تصوير', 'Film set construction', 'studio', [7, 6], ['studio-3'], 'مصفح، أبوظبي', 'Mussafah, Abu Dhabi', 2026, 400, '5 أسابيع', '5 weeks', true],
        ];

        foreach ($projects as $index => [$ar, $en, $category, $serviceIndexes, $photos, $locAr, $locEn, $year, $area, $durAr, $durEn, $featured]) {
            $project = Project::query()->create([
                'title' => ['ar' => $ar, 'en' => $en],
                'category_id' => $this->categories[$category]->id,
                'location' => ['ar' => $locAr, 'en' => $locEn],
                'area_m2' => $area,
                'duration' => ['ar' => $durAr, 'en' => $durEn],
                'year' => $year,
                'project_status' => 'completed',
                'excerpt' => [
                    'ar' => 'مشروع نموذجي يوضح أسلوبنا في التنفيذ: '.$ar.' في '.$locAr.'.',
                    'en' => 'A sample project showing how we work: '.strtolower($en).' in '.$locEn.'.',
                ],
                'scope' => [
                    'ar' => ['المعاينة وحصر الكميات', 'التوريد والتنفيذ بإشراف مهندس', 'الاستلام النهائي والتسليم'],
                    'en' => ['Site survey and quantity take-off', 'Supply and execution supervised by an engineer', 'Final inspection and handover'],
                ],
                'blocks' => [[
                    'type' => 'rich_text',
                    'data' => ['html' => [
                        'ar' => '<p>نص تجريبي: يُستبدل بوصف المشروع الحقيقي من لوحة التحكم، مع ذكر التحديات والحلول والمواد المستخدمة.</p>',
                        'en' => '<p>Sample copy: replace with the real project description from the dashboard, covering challenges, solutions and materials.</p>',
                    ]],
                ]],
                'is_featured' => $featured,
                'status' => 'published',
                'published_at' => now()->subDays(30 - $index * 3),
                'sort' => $index + 1,
            ]);

            $project->services()->sync(array_map(fn (int $i) => $this->services[$i]->id, $serviceIndexes));

            $ids = array_values(array_filter(array_map(fn (string $p) => $this->photos[$p]->id ?? null, $photos)));

            if ($ids !== []) {
                $project->syncAssets('cover', [$ids[0]]);
                $project->syncAssets('gallery', $ids);
            }
        }
    }

    private function posts(): void
    {
        $categories = [
            'tips' => PostCategory::query()->create(['name' => ['ar' => 'نصائح البناء', 'en' => 'Construction tips']]),
            'insulation' => PostCategory::query()->create(['name' => ['ar' => 'العزل', 'en' => 'Insulation']]),
            'finishing' => PostCategory::query()->create(['name' => ['ar' => 'التشطيبات', 'en' => 'Finishing']]),
        ];

        $author = User::query()->first();

        $posts = [
            ['الفرق بين أنواع أرضيات الإيبوكسي ومتى تستخدم كل نوع', 'Epoxy floor types and when to use each', 'finishing', 'paving',
                'تختلف أنظمة الإيبوكسي في السماكة ومقاومة الاحتكاك والمواد الكيميائية، واختيار النظام المناسب يبدأ من طبيعة الاستخدام.',
                'Epoxy systems differ in thickness and resistance to abrasion and chemicals; choosing the right one starts with how the floor will be used.'],
            ['عزل الأسطح قبل الصيف: ما الذي تحتاج معرفته', 'Roof insulation before summer: what you need to know', 'insulation', 'concrete',
                'العزل المائي والحراري للأسطح يقلل تسرب المياه ويخفض استهلاك التكييف، والتوقيت المناسب يصنع فرقًا في جودة التنفيذ.',
                'Roof waterproofing and thermal insulation reduce leaks and air-conditioning costs, and timing makes a difference to the result.'],
            ['كيف تقرأ عرض سعر مقاولات بشكل صحيح', 'How to read a contracting quotation properly', 'tips', 'helmet',
                'عرض السعر الجيد مفصل بالبنود والكميات والمواصفات ومدة التنفيذ، وهذه أهم النقاط التي يجب مراجعتها قبل التوقيع.',
                'A good quotation is itemised with quantities, specifications and duration; here are the points to check before you sign.'],
            ['العزل الصوتي لقاعات الاجتماعات: من أين تبدأ', 'Acoustic insulation for meeting rooms: where to start', 'insulation', 'studio-3',
                'تبدأ معالجة الصوت بقياس مصادر الضوضاء وتحديد الهدف: منع انتقال الصوت أو تحسين وضوحه داخل القاعة.',
                'Acoustic treatment starts with measuring noise sources and defining the goal: stopping sound transfer or improving clarity inside the room.'],
            ['تجاليد الخشب في المكاتب: الخامات والصيانة', 'Wood cladding in offices: materials and care', 'finishing', 'corridor',
                'تضيف تجاليد الخشب دفئًا للمكاتب، لكن اختيار الخامة المناسبة للرطوبة والاستخدام يحدد عمرها وشكلها مع الوقت.',
                'Wood cladding adds warmth to offices, but choosing a material suited to humidity and use decides how it ages.'],
            ['لماذا تظهر الشقوق في الأسقف الخرسانية', 'Why cracks appear in concrete slabs', 'tips', 'slab-1',
                'معظم الشقوق في الأسقف الخرسانية لها أسباب معروفة مثل الانكماش وسوء المعالجة، ويمكن تجنب أغلبها من مرحلة الصب.',
                'Most cracks in concrete slabs have known causes such as shrinkage and poor curing, and most can be avoided at the pouring stage.'],
        ];

        foreach ($posts as $index => [$ar, $en, $category, $photo, $leadAr, $leadEn]) {
            $post = Post::query()->create([
                'title' => ['ar' => $ar, 'en' => $en],
                'excerpt' => ['ar' => $leadAr, 'en' => $leadEn],
                'body' => [
                    'ar' => '<p>'.$leadAr.'</p><h2>نقاط أساسية</h2><ul><li>حدد الاستخدام والأحمال المتوقعة قبل اختيار المواد.</li><li>اطلب مواصفات مكتوبة وكميات واضحة في عرض السعر.</li><li>تأكد من وجود مهندس مسؤول يتابع التنفيذ.</li></ul><h2>الخلاصة</h2><p>نص تجريبي يُستبدل بمحتوى المقال الكامل من لوحة التحكم.</p>',
                    'en' => '<p>'.$leadEn.'</p><h2>Key points</h2><ul><li>Define the use and expected loads before choosing materials.</li><li>Ask for written specifications and clear quantities in the quotation.</li><li>Make sure a responsible engineer supervises the work.</li></ul><h2>In short</h2><p>Sample copy to be replaced with the full article from the dashboard.</p>',
                ],
                'post_category_id' => $categories[$category]->id,
                'author_id' => $author?->id,
                'is_featured' => $index === 0,
                'status' => 'published',
                'published_at' => now()->subDays(40 - $index * 6),
            ]);

            if (isset($this->photos[$photo])) {
                $post->syncAssets('cover', [$this->photos[$photo]->id]);
            }
        }
    }

    private function faqs(): void
    {
        $faqs = [
            ['هل تقدمون معاينة قبل عرض السعر؟', 'Do you visit the site before quoting?', 'نعم، نحدد موعد معاينة لأخذ المقاسات وفهم المطلوب قبل تقديم عرض سعر مفصل.', 'Yes. We book a site visit to take measurements and understand the brief before sending a detailed quotation.'],
            ['في أي مناطق تعملون؟', 'Which areas do you cover?', 'مقرنا في مصفح، أبوظبي، ونعمل في أبوظبي وباقي الإمارات حسب طبيعة المشروع.', 'We are based in Mussafah, Abu Dhabi, and work across Abu Dhabi and the other emirates depending on the project.'],
            ['هل تقدمون ضمانًا على الأعمال؟', 'Do you provide a warranty?', 'نعم، نسلم شهادة ضمان للأعمال حسب نوع العمل والمواد المستخدمة.', 'Yes. We issue a warranty certificate based on the type of work and the materials used.'],
            ['كم يستغرق إعداد عرض السعر؟', 'How long does a quotation take?', 'عادة خلال أيام قليلة من المعاينة واستلام المخططات.', 'Usually a few days after the site visit and receiving the drawings.'],
        ];

        foreach ($faqs as [$qAr, $qEn, $aAr, $aEn]) {
            Faq::query()->create([
                'question' => ['ar' => $qAr, 'en' => $qEn],
                'answer' => ['ar' => $aAr, 'en' => $aEn],
                'is_active' => true,
            ]);
        }
    }

    private function certificates(): void
    {
        $licence = Certificate::query()->create([
            'name' => ['ar' => 'الرخصة التجارية', 'en' => 'Trade licence'],
            'number' => 'CN-5898460',
            'authority' => ['ar' => 'دائرة التنمية الاقتصادية – أبوظبي', 'en' => 'Abu Dhabi Department of Economic Development'],
            'issued_at' => '2026-04-22',
            'expires_at' => '2029-05-11',
            'show_on_site' => true,
        ]);

        $classification = Certificate::query()->create([
            'name' => ['ar' => 'شهادة التصنيف – الفئة الخامسة، مقاولات المباني', 'en' => 'Classification certificate – Fifth category, Building Contracting'],
            'number' => 'D12-2026-5898460-LI1',
            'authority' => ['ar' => 'بلدية مدينة أبوظبي', 'en' => 'Abu Dhabi City Municipality'],
            'issued_at' => '2026-03-26',
            'expires_at' => '2028-03-25',
            'show_on_site' => true,
        ]);

        if (isset($this->docs['licence'])) {
            $licence->syncAssets('file', [$this->docs['licence']->id]);
        }

        if (isset($this->docs['classification'])) {
            $classification->syncAssets('file', [$this->docs['classification']->id]);
        }
    }

    private function jobs(): void
    {
        $jobs = [
            ['مهندس موقع مدني', 'Civil site engineer', 'المشاريع', 'Projects', 'متابعة التنفيذ في الموقع وضبط الجودة والجدول الزمني والتنسيق مع الاستشاري.', 'Supervise site works, quality and schedule, and coordinate with the consultant.',
                ['بكالوريوس هندسة مدنية', 'خبرة 3 سنوات على الأقل في الإمارات', 'رخصة قيادة إماراتية'], ['BSc in civil engineering', 'At least 3 years of UAE experience', 'UAE driving licence']],
            ['فورمان أعمال تشطيبات', 'Finishing works foreman', 'التنفيذ', 'Operations', 'إدارة فرق التشطيب وتوزيع الأعمال اليومية ومتابعة الجودة.', 'Lead finishing crews, plan daily work and check quality.',
                ['خبرة 5 سنوات في أعمال التشطيب', 'قراءة المخططات المعمارية', 'إجادة العربية أو الإنجليزية'], ['5 years of finishing experience', 'Able to read architectural drawings', 'Arabic or English']],
            ['فني أرضيات إيبوكسي', 'Epoxy flooring technician', 'التنفيذ', 'Operations', 'تجهيز الأسطح وتنفيذ أنظمة الإيبوكسي المختلفة وفق المواصفات.', 'Prepare surfaces and apply epoxy systems to specification.',
                ['خبرة عملية في أنظمة الإيبوكسي', 'الالتزام بإجراءات السلامة'], ['Hands-on epoxy experience', 'Commitment to safety procedures']],
            ['مسؤول مبيعات ومتابعة عملاء', 'Sales & client follow-up officer', 'المبيعات', 'Sales', 'التواصل مع العملاء ومتابعة الطلبات وتنسيق مواعيد المعاينة.', 'Contact clients, follow up requests and book site visits.',
                ['خبرة في مبيعات المقاولات أو مواد البناء', 'مهارات تواصل ممتازة', 'رخصة قيادة إماراتية'], ['Experience in contracting or building-materials sales', 'Excellent communication skills', 'UAE driving licence']],
        ];

        foreach ($jobs as $index => [$ar, $en, $depAr, $depEn, $descAr, $descEn, $reqAr, $reqEn]) {
            JobPost::query()->create([
                'title' => ['ar' => $ar, 'en' => $en],
                'department' => ['ar' => $depAr, 'en' => $depEn],
                'job_type' => 'full_time',
                'location' => ['ar' => 'أبوظبي', 'en' => 'Abu Dhabi'],
                'description' => ['ar' => '<p>'.$descAr.'</p>', 'en' => '<p>'.$descEn.'</p>'],
                'requirements' => ['ar' => $reqAr, 'en' => $reqEn],
                'status' => 'published',
                'published_at' => now()->subDays(10 + $index),
                'sort' => $index + 1,
            ]);
        }
    }

    private function aboutPage(): Page
    {
        $page = Page::query()->create([
            'title' => ['ar' => 'من نحن', 'en' => 'About us'],
            'slug' => ['ar' => 'من-نحن', 'en' => 'about-us'],
            'excerpt' => [
                'ar' => 'كلاس سكيلد للمقاولات العامة، شركة مصنفة من بلدية مدينة أبوظبي، تعمل كشريك حقيقي في نجاح مشاريع عملائها.',
                'en' => 'Class Skilled General Contracting, classified by Abu Dhabi City Municipality, works as a true partner in its clients’ success.',
            ],
            'hero_type' => 'image',
            'status' => 'published',
            'published_at' => now(),
            'blocks' => [
                ['type' => 'rich_text', 'data' => ['html' => [
                    'ar' => '<p>تأسست كلاس سكيلد للمقاولات العامة في أبوظبي عام 2025، ومقرها مصفح. نعمل في إنشاء الفلل والمباني، والتشطيبات، وأرضيات الإيبوكسي، والعزل، وتجهيز استوديوهات التصوير، ونضع ثقة العميل فوق كل اعتبار.</p>',
                    'en' => '<p>Class Skilled General Contracting was founded in Abu Dhabi in 2025 and is based in Mussafah. We build villas and buildings and deliver finishing, epoxy flooring, insulation and film studio fit-out, and we put client trust first.</p>',
                ]]],
                ['type' => 'two_columns', 'data' => [
                    'left' => [
                        'title' => ['ar' => 'رؤيتنا', 'en' => 'Our vision'],
                        'html' => [
                            'ar' => '<p>أن نُحدث فرقًا ملموسًا في قطاع البناء، ونعيد تعريف مفهوم "المقاول" من منفّذ إلى شريك حقيقي في النجاح. نطمح أن نكون في طليعة الشركات التي ترفع معايير الأداء في الإمارات وخارجها.</p>',
                            'en' => '<p>To make a tangible difference in construction and redefine the contractor from an executor to a true partner in success, leading the companies that raise performance standards in the UAE and beyond.</p>',
                        ],
                        'dark' => false,
                    ],
                    'right' => [
                        'title' => ['ar' => 'رسالتنا', 'en' => 'Our mission'],
                        'html' => [
                            'ar' => '<p>بناء مشاريع تتكلم عنا، وعلاقات طويلة الأمد مع عملائنا، عبر مشاريع تجمع بين الجودة العالية والالتزام الكامل. نضع ثقة العميل فوق كل اعتبار، ونهتم بالتفاصيل الصغيرة بقدر اهتمامنا بالصورة الكبيرة.</p>',
                            'en' => '<p>To build projects that speak for us and long-term relationships with our clients, through work that combines high quality with full commitment. Client trust comes first, and we care about small details as much as the big picture.</p>',
                        ],
                        'dark' => true,
                    ],
                ]],
                ['type' => 'values_list', 'data' => [
                    'title' => ['ar' => 'قيمنا', 'en' => 'Our values'],
                    'items' => [
                        ['title' => ['ar' => 'الجودة', 'en' => 'Quality'], 'text' => ['ar' => 'مواد معتمدة ومراجعة في كل مرحلة', 'en' => 'Approved materials, reviewed at every stage']],
                        ['title' => ['ar' => 'الالتزام', 'en' => 'Commitment'], 'text' => ['ar' => 'بالمواعيد والمواصفات المتفق عليها', 'en' => 'To agreed schedules and specifications']],
                        ['title' => ['ar' => 'الثقة', 'en' => 'Trust'], 'text' => ['ar' => 'وضوح في الأسعار والتواصل', 'en' => 'Clear pricing and communication']],
                        ['title' => ['ar' => 'الدقة', 'en' => 'Precision'], 'text' => ['ar' => 'في التنفيذ والتفاصيل الصغيرة', 'en' => 'In execution and the small details']],
                        ['title' => ['ar' => 'الشراكة', 'en' => 'Partnership'], 'text' => ['ar' => 'نعمل كجزء من فريق العميل', 'en' => 'We work as part of the client’s team']],
                    ],
                ]],
            ],
            'attachments_config' => [
                'files' => ['visible' => true, 'title' => ['ar' => 'ملفات الشركة', 'en' => 'Company files'], 'display' => 'list'],
                'videos' => ['visible' => false, 'title' => ['ar' => 'فيديو', 'en' => 'Videos'], 'display' => 'grid'],
                'images' => ['visible' => true, 'title' => ['ar' => 'من مواقع العمل', 'en' => 'From our sites'], 'display' => 'grid', 'columns' => 3],
            ],
        ]);

        if (isset($this->photos['office'])) {
            $page->syncAssets('hero_image', [$this->photos['office']->id]);
        }

        $page->syncAssets('att_files', array_values(array_filter([
            $this->docs['profile']->id ?? null,
            $this->docs['licence']->id ?? null,
            $this->docs['classification']->id ?? null,
        ])));

        $page->syncAssets('att_images', array_values(array_filter(array_map(
            fn (string $name) => $this->photos[$name]->id ?? null,
            ['studio-1', 'office-2', 'slab-1', 'stairs', 'rebar', 'paving'],
        ))));

        return $page;
    }

    private function homeSections(Page $aboutPage): void
    {
        $photo = fn (string $name) => $this->photos[$name]->id ?? null;

        foreach (HomeSection::query()->get() as $section) {
            $data = $section->data ?? [];

            switch ($section->type) {
                case SectionType::Hero:
                    foreach (['studio-1', 'office', 'slab-2'] as $i => $name) {
                        if (isset($data['slides'][$i])) {
                            $data['slides'][$i]['image_asset_id'] = $photo($name);
                        }
                    }
                    break;
                case SectionType::About:
                    $data['image_asset_id'] = $photo('office-2');
                    $section->cta_link = ['type' => 'page', 'model_type' => 'page', 'model_id' => $aboutPage->id];
                    break;
                case SectionType::FeatureBand:
                    $data['image_asset_ids'] = array_values(array_filter([$photo('studio-3'), $photo('studio-2'), $photo('studio-1')]));
                    $section->cta_link = ['type' => 'model', 'model_type' => 'service', 'model_id' => $this->services[7]->id];
                    break;
                case SectionType::Testimonials:
                    $data['bg_asset_id'] = $photo('concrete');
                    break;
                default:
                    break;
            }

            $section->data = $data;
            $section->save();
        }
    }

    private function menus(Page $aboutPage): void
    {
        $header = Menu::query()->where('location', MenuLocation::Header->value)->first();

        if ($header === null) {
            return;
        }

        $items = $header->rootItems()->get();
        $order = [];

        foreach ($items as $item) {
            $order[] = $item;

            if ($item->type === MenuItemType::Home) {
                $order[] = $header->items()->create(['type' => MenuItemType::Page, 'linkable_type' => 'page', 'linkable_id' => $aboutPage->id]);
            }

            if ($item->type === MenuItemType::DynamicGroup && $item->system_route === 'projects.index') {
                $order[] = $header->items()->create([
                    'type' => MenuItemType::Model,
                    'label' => ['ar' => 'الاستوديوهات', 'en' => 'Studios'],
                    'linkable_type' => 'service',
                    'linkable_id' => $this->services[7]->id,
                ]);
            }

            if ($item->type === MenuItemType::DynamicGroup && $item->system_route === 'services.index') {
                $item->update(['dynamic_mode' => DataSource::Manual]);

                foreach (array_values($this->services) as $sort => $service) {
                    $item->entries()->create(['entry_type' => 'service', 'entry_id' => $service->id, 'sort' => $sort + 1]);
                }
            }
        }

        foreach ($order as $index => $item) {
            $item->update(['sort' => $index + 1]);
        }

        $footer = Menu::query()->where('location', MenuLocation::Footer1->value)->first();

        if ($footer !== null) {
            $footer->items()->update(['sort' => \DB::raw('sort + 2')]);
            $footer->items()->create(['type' => MenuItemType::Page, 'linkable_type' => 'page', 'linkable_id' => $aboutPage->id, 'sort' => 1]);
            $footer->items()->create(['type' => MenuItemType::Model, 'label' => ['ar' => 'الاستوديوهات', 'en' => 'Studios'], 'linkable_type' => 'service', 'linkable_id' => $this->services[7]->id, 'sort' => 2]);
        }
    }
}
