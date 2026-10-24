<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\RequestType;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\HomeSection;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Redirect;
use App\Models\RequestStatus;
use App\Models\Service;
use App\Models\SiteEvent;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\VisitorRequest;
use App\Settings\FormSettings;
use App\Support\Translate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Local demo data for every dashboard screen: team users, testimonials,
 * clients, team, quote requests / messages / applications with their activity,
 * site analytics (visits and clicks for 90 days) and redirects.
 * Deterministic (fixed random seed) and skipped when requests already exist.
 */
class DemoActivitySeeder extends Seeder
{
    private const PASSWORD = 'ChangeMe-2026!';

    /** @var array<string, User> */
    private array $users = [];

    public function run(): void
    {
        if (QuoteRequest::query()->withTrashed()->exists()) {
            $this->command?->info('Demo activity already present, skipped.');

            return;
        }

        mt_srand(2026);

        $this->teamUsers();
        $this->testimonials();
        $this->clients();
        $this->team();
        $this->quotes();
        $this->messages();
        $this->applications();
        $this->analytics();
        $this->redirects();

        HomeSection::query()->whereIn('type', ['testimonials', 'clients'])->update(['is_visible' => true]);
    }

    private function teamUsers(): void
    {
        $this->users['admin'] = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->orderBy('id')->firstOrFail();

        foreach ([
            'sales' => ['أحمد الشامسي', 'sales@class-skilled.test'],
            'sales2' => ['مريم الكعبي', 'sales2@class-skilled.test'],
            'editor' => ['خالد منصور', 'editor@class-skilled.test'],
        ] as $key => [$name, $email]) {
            $user = User::query()->firstOrCreate(['email' => $email], ['name' => $name, 'password' => self::PASSWORD, 'locale' => 'ar', 'is_active' => true]);
            $user->syncRoles([str_starts_with($key, 'sales') ? 'sales' : 'editor']);
            $user->forceFill(['last_login_at' => now()->subHours(mt_rand(2, 90))])->saveQuietly();
            $this->users[$key] = $user;
        }
    }

    private function testimonials(): void
    {
        $projects = Project::query()->pluck('id')->all();
        $rows = [
            ['م. سعيد الظاهري', 'مالك فيلا — خليفة سيتي', 'Villa owner — Khalifa City', 'التزام واضح بالجدول الزمني ومهندس موقع متابع يوميًا. استلمنا الفيلا في الموعد وبجودة تشطيب ممتازة.', 'Clear commitment to the schedule and a site engineer on top of things every day. We received the villa on time with excellent finishing.'],
            ['شركة لمسة للإنتاج الفني', 'استوديو تصوير — مصفح', 'Film studio — Mussafah', 'نفّذوا ديكورات التصوير وهياكل الإضاءة في وقت قياسي، مع مرونة كبيرة في التعديلات أثناء العمل.', 'They built our sets and lighting rigs in record time and handled changes during the work with real flexibility.'],
            ['فاطمة الحمادي', 'مكتب إداري — أبوظبي', 'Office — Abu Dhabi', 'التشطيبات الخشبية والإضاءة المخفية خرجت أجمل من التصميم. فريق محترم ونظيف في الموقع.', 'The wood finishes and hidden lighting turned out better than the design. A respectful, tidy team on site.'],
            ['م. يوسف النقبي', 'مستودع — آيكاد', 'Warehouse — ICAD', 'أرضيات الإيبوكسي ممتازة ومقاومة للحركة الثقيلة، والعرض كان واضحًا بالبنود والكميات.', 'The epoxy floors are excellent under heavy traffic, and the quotation was clear with itemised quantities.'],
            ['عبدالله المرزوقي', 'مبنى سكني — الشامخة', 'Residential building — Al Shamkha', 'عزل الأسطح حل مشكلة التسريب نهائيًا. خدمة ما بعد التسليم ممتازة.', 'The roof insulation solved our leaks for good. Excellent after-handover service.'],
        ];

        foreach ($rows as $i => [$name, $companyAr, $companyEn, $bodyAr, $bodyEn]) {
            Testimonial::query()->create([
                'name' => ['ar' => $name, 'en' => $name],
                'company' => ['ar' => $companyAr, 'en' => $companyEn],
                'body' => ['ar' => $bodyAr, 'en' => $bodyEn],
                'rating' => 5,
                'project_id' => $projects[$i] ?? null,
                'is_active' => true,
                'sort' => $i + 1,
            ]);
        }
    }

    private function clients(): void
    {
        foreach ([
            ['مجموعة الواحة العقارية', 'Al Waha Real Estate Group'],
            ['لمسة للإنتاج الفني', 'Lamsa Art Production'],
            ['النخبة للتطوير', 'Al Nokhba Development'],
            ['بيت الخليج للمقاولات', 'Gulf House Contracting'],
            ['استوديوهات الصحراء', 'Desert Studios'],
            ['مدارس المستقبل الخاصة', 'Future Private Schools'],
            ['مستودعات الإمارات اللوجستية', 'Emirates Logistics Warehouses'],
            ['مركز الريم الطبي', 'Al Reem Medical Centre'],
        ] as $i => [$ar, $en]) {
            Client::query()->create(['name' => ['ar' => $ar, 'en' => $en], 'is_active' => true, 'sort' => $i + 1]);
        }
    }

    private function team(): void
    {
        foreach ([
            ['م. عمر حسان', 'Eng. Omar Hassan', 'مدير المشاريع', 'Projects Manager'],
            ['م. ريم سليمان', 'Eng. Reem Suleiman', 'مهندسة موقع', 'Site Engineer'],
            ['سامي العلي', 'Sami Al Ali', 'مشرف تشطيبات', 'Finishing Supervisor'],
            ['نورة الكتبي', 'Noura Al Ketbi', 'مسؤولة العملاء', 'Client Relations'],
        ] as $i => [$nameAr, $nameEn, $roleAr, $roleEn]) {
            TeamMember::query()->create([
                'name' => ['ar' => $nameAr, 'en' => $nameEn],
                'role' => ['ar' => $roleAr, 'en' => $roleEn],
                'is_active' => true,
                'sort' => $i + 1,
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function person(int $i): array
    {
        $first = ['محمد', 'أحمد', 'سالم', 'خليفة', 'راشد', 'سعيد', 'عبدالله', 'حمدان', 'مريم', 'فاطمة', 'عائشة', 'نورة', 'هند', 'يوسف', 'ماجد', 'سيف'];
        $last = ['الشامسي', 'المزروعي', 'الكعبي', 'الظاهري', 'النعيمي', 'الحمادي', 'المهيري', 'القبيسي', 'المنصوري', 'البلوشي', 'السويدي', 'العامري'];
        $name = $first[$i % count($first)].' '.$last[($i * 7) % count($last)];
        $phone = '+971 5'.[0, 2, 4, 5, 6, 8][$i % 6].' '.mt_rand(100, 999).' '.mt_rand(1000, 9999);

        return [$name, $phone];
    }

    /**
     * @return array<string, mixed>
     */
    private function source(): array
    {
        $pages = ['/', '/الخدمات', '/المشاريع', '/طلب-عرض-سعر', '/en/services', '/تواصل-معنا'];
        $utm = [null, null, ['source' => 'google', 'medium' => 'cpc', 'campaign' => 'villas-abudhabi'], ['source' => 'instagram', 'medium' => 'social'], null, ['source' => 'facebook', 'medium' => 'social', 'campaign' => 'studio-fitout']];
        $referrers = [null, 'https://www.google.com/', null, 'https://www.instagram.com/', null];

        return array_filter([
            'page' => $pages[mt_rand(0, count($pages) - 1)],
            'landing' => $pages[mt_rand(0, 2)],
            'referrer' => $referrers[mt_rand(0, count($referrers) - 1)],
            'utm' => $utm[mt_rand(0, count($utm) - 1)],
            'device' => ['mobile', 'mobile', 'mobile', 'desktop', 'tablet'][mt_rand(0, 4)],
        ]);
    }

    private function at(int $daysAgo): Carbon
    {
        $at = now()->subDays($daysAgo)->setTime(mt_rand(8, 21), mt_rand(0, 59));

        // Never in the future (requests "from today" happened earlier today).
        return $at->isFuture() ? now()->subMinutes(mt_rand(5, 240)) : $at;
    }

    /**
     * Status changes, notes and assignment after creation (timeline in the dashboard).
     */
    private function followUp(VisitorRequest $request, RequestType $type, Carbon $createdAt, string $statusKey, ?User $assignee, bool $read): void
    {
        $statuses = RequestStatus::query()->for($type)->get()->keyBy('key');
        $default = $statuses->firstWhere('is_default', true);
        $status = $statuses->get($statusKey) ?? $default;

        DB::table('request_activities')->insert([
            'subject_type' => $request->getMorphClass(), 'subject_id' => $request->getKey(), 'user_id' => null,
            'type' => ActivityType::System->value, 'body' => null,
            'meta' => json_encode(['event' => 'created', 'source' => $request->getAttribute('source')], JSON_UNESCAPED_UNICODE),
            'created_at' => $createdAt, 'updated_at' => $createdAt,
        ]);

        $when = $createdAt->copy();
        $actor = $assignee ?? $this->users['admin'];

        if ($assignee) {
            $when->addHours(mt_rand(1, 6));
            DB::table('request_activities')->insert([
                'subject_type' => $request->getMorphClass(), 'subject_id' => $request->getKey(), 'user_id' => $this->users['admin']->id,
                'type' => ActivityType::Assign->value, 'body' => null, 'meta' => json_encode(['to' => $assignee->name], JSON_UNESCAPED_UNICODE),
                'created_at' => $when, 'updated_at' => $when,
            ]);
        }

        if ($status && $default && $status->id !== $default->id) {
            $when->addHours(mt_rand(2, 30));
            DB::table('request_activities')->insert([
                'subject_type' => $request->getMorphClass(), 'subject_id' => $request->getKey(), 'user_id' => $actor->id,
                'type' => ActivityType::Status->value, 'body' => null,
                'meta' => json_encode(['from' => $default->getTranslation('name', 'ar'), 'to' => $status->getTranslation('name', 'ar')], JSON_UNESCAPED_UNICODE),
                'created_at' => $when, 'updated_at' => $when,
            ]);

            $notes = [
                'تم الاتصال بالعميل وتحديد موعد المعاينة يوم الأحد.',
                'العميل طلب إضافة بند العزل الحراري للسطح.',
                'تم إرسال العرض عبر واتساب والبريد.',
                'العميل يقارن مع عرضين آخرين، متابعة بعد أسبوع.',
                'تمت المعاينة، المساحة الفعلية أكبر من المذكورة.',
            ];
            $when->addHours(mt_rand(1, 20));
            DB::table('request_activities')->insert([
                'subject_type' => $request->getMorphClass(), 'subject_id' => $request->getKey(), 'user_id' => $actor->id,
                'type' => ActivityType::Note->value, 'body' => $notes[mt_rand(0, count($notes) - 1)], 'meta' => null,
                'created_at' => $when, 'updated_at' => $when,
            ]);
        }

        if ($when->isFuture()) {
            $when = now()->subMinutes(mt_rand(1, 30));
        }

        $request->forceFill([
            'status_id' => $status?->id,
            'assigned_to' => $assignee?->id,
            'read_at' => $read ? $createdAt->copy()->addHours(mt_rand(1, 12)) : null,
            'created_at' => $createdAt,
            'updated_at' => $when,
        ])->saveQuietly();
    }

    private function quotes(): void
    {
        $settings = app(FormSettings::class);
        $pick = fn (array $items) => Translate::pick($items[mt_rand(0, count($items) - 1)], 'ar');
        $services = Service::query()->pluck('id')->all();
        $descriptions = [
            'فيلا دورين مع ملحق، نحتاج تشطيب كامل داخلي وخارجي.',
            'تجهيز استوديو تصوير بمساحة كبيرة مع هياكل إضاءة وعزل صوتي.',
            'أرضيات إيبوكسي لمستودع تخزين مع خطوط إرشادية.',
            'عزل سطح مبنى سكني 4 طوابق بسبب تسريب مياه.',
            'ترميم مكتب إداري وتغيير الأرضيات والأسقف المعلقة.',
            null,
        ];
        // [days ago, status key, assignee key|null, read]
        $plan = [
            [0, 'new', null, false], [0, 'new', null, false], [1, 'new', 'sales', false], [1, 'contacted', 'sales', true],
            [2, 'new', null, false], [3, 'visit', 'sales2', true], [4, 'quote_sent', 'sales', true], [5, 'contacted', 'sales2', true],
            [6, 'won', 'sales', true], [8, 'quote_sent', 'sales2', true], [10, 'lost', 'sales', true], [12, 'won', 'sales2', true],
            [14, 'visit', 'sales', true], [17, 'new', null, true], [20, 'won', 'sales', true], [23, 'cancelled', 'sales2', true],
            [26, 'quote_sent', 'sales', true], [29, 'won', 'sales2', true], [33, 'lost', 'sales', true], [38, 'won', 'sales', true],
            [44, 'contacted', 'sales2', true], [51, 'won', 'sales', true], [58, 'lost', 'sales2', true], [66, 'won', 'sales', true],
            [74, 'quote_sent', 'sales2', true], [83, 'won', 'sales', true],
        ];
        $attachment = base_path('_design/docs/letterhead-ar.pdf');
        $photos = glob(base_path('_design/photos/*.jpg')) ?: [];

        foreach ($plan as $i => [$days, $status, $assignee, $read]) {
            [$name, $phone] = $this->person($i);
            $unknownArea = $i % 5 === 3;

            /** @var QuoteRequest $quote */
            $quote = QuoteRequest::query()->create([
                'name' => $name,
                'phone' => $phone,
                'email' => $i % 3 === 0 ? null : 'client'.($i + 1).'@example.com',
                'service_id' => $services[$i % max(1, count($services))] ?? null,
                'facility_type' => $pick($settings->facility_types),
                'project_state' => $pick($settings->project_states),
                'emirate' => $i % 4 === 0 ? 'دبي' : 'أبوظبي',
                'area_text' => ['مصفح', 'خليفة سيتي', 'الشامخة', 'محمد بن زايد', 'آيكاد', 'الريم'][$i % 6],
                'area_m2' => $unknownArea ? null : (string) [180, 250, 320, 450, 600, 850, 1200, 2400][$i % 8],
                'start_when' => $pick($settings->start_options),
                'description' => $descriptions[$i % count($descriptions)],
                'preferred_contact' => $i % 2 ? 'whatsapp' : 'call',
                'locale' => $i % 4 === 1 ? 'en' : 'ar',
                'source' => $this->source(),
                'ip_hash' => hash('sha256', 'demo-'.$i),
            ]);

            if ($i % 3 === 0 && is_file($attachment)) {
                $quote->addMedia($attachment)->preservingOriginal()->usingName('مخطط-الدور-الأرضي')->usingFileName('plan-'.($i + 1).'.pdf')->toMediaCollection('attachments');
            }

            if ($i % 4 === 0 && $photos !== []) {
                $quote->addMedia($photos[$i % count($photos)])->preservingOriginal()->usingName('صورة-الموقع')->usingFileName('site-'.($i + 1).'.jpg')->toMediaCollection('attachments');
            }

            $this->followUp($quote, RequestType::Quote, $this->at($days), $status, $assignee ? $this->users[$assignee] : null, $read);

            if ($status === 'visit' || $status === 'quote_sent') {
                $quote->forceFill(['follow_up_at' => now()->addDays(mt_rand(1, 6))])->saveQuietly();
            }
        }
    }

    private function messages(): void
    {
        $subjects = app(FormSettings::class)->contact_subjects;
        $texts = [
            'السلام عليكم، هل تقومون بأعمال الصيانة الدورية للفلل؟',
            'أرغب في معرفة مدة تنفيذ أرضيات الإيبوكسي لمستودع 800 متر.',
            'Hello, do you provide acoustic insulation for meeting rooms?',
            'نحن مورّدون لمواد العزل ونرغب في التعاون معكم.',
            'أرسلت طلب عرض سعر الأسبوع الماضي ولم يصلني رد، رقم الطلب QR-0004.',
            'هل يمكن زيارة أحد مشاريعكم المنفذة للاطلاع على جودة التشطيب؟',
            'Do you work outside Abu Dhabi, for example in Al Ain?',
            'أحتاج تجهيز ديكور لتصوير إعلان تلفزيوني خلال أسبوعين.',
            'استفسار عن تكلفة عزل سطح فيلا تقريبًا.',
            'شكرًا على سرعة التنفيذ في مشروعنا الأخير.',
        ];
        $plan = [[0, 'new', false], [1, 'new', false], [2, 'read', true], [3, 'replied', true], [5, 'new', false], [8, 'replied', true], [12, 'closed', true], [19, 'replied', true], [27, 'closed', true], [41, 'closed', true]];

        foreach ($plan as $i => [$days, $status, $read]) {
            [$name, $phone] = $this->person($i + 40);
            $english = str_starts_with($texts[$i], 'Hello') || str_starts_with($texts[$i], 'Do ');

            /** @var ContactMessage $message */
            $message = ContactMessage::query()->create([
                'name' => $name,
                'phone' => $i % 4 === 2 ? null : $phone,
                'email' => 'visitor'.($i + 1).'@example.com',
                'subject' => Translate::pick($subjects[$i % count($subjects)], 'ar'),
                'message' => $texts[$i],
                'locale' => $english ? 'en' : 'ar',
                'source' => $this->source(),
                'ip_hash' => hash('sha256', 'demo-msg-'.$i),
            ]);

            $this->followUp($message, RequestType::Message, $this->at($days), $status, $read ? $this->users['sales'] : null, $read);
        }
    }

    private function applications(): void
    {
        $jobs = JobPost::query()->pluck('id')->all();
        $cv = base_path('_design/docs/letterhead-en.pdf');
        $specialties = ['مهندس مدني', 'مشرف موقع', 'فني عزل', 'نجار تشطيبات', 'كهربائي', 'محاسب', 'مصمم داخلي', 'فني إيبوكسي'];
        $plan = [[0, 'new', false], [2, 'new', false], [4, 'shortlisted', true], [7, 'interview', true], [11, 'new', true], [16, 'rejected', true], [22, 'accepted', true], [35, 'rejected', true]];

        foreach ($plan as $i => [$days, $status, $read]) {
            [$name, $phone] = $this->person($i + 70);

            /** @var JobApplication $application */
            $application = JobApplication::query()->create([
                'job_post_id' => $i % 4 === 3 ? null : ($jobs[$i % max(1, count($jobs))] ?? null),
                'name' => $name,
                'phone' => $phone,
                'email' => 'candidate'.($i + 1).'@example.com',
                'specialty' => $specialties[$i % count($specialties)],
                'message' => $i % 2 ? 'خبرة '.mt_rand(3, 12).' سنوات في الإمارات، متاح للانضمام فورًا.' : null,
                'locale' => 'ar',
                'source' => $this->source(),
                'ip_hash' => hash('sha256', 'demo-app-'.$i),
            ]);

            if (is_file($cv)) {
                $application->addMedia($cv)->preservingOriginal()->usingName('السيرة-الذاتية')->usingFileName('cv-'.($i + 1).'.pdf')->toMediaCollection('cv');
            }

            $this->followUp($application, RequestType::Application, $this->at($days), $status, $read ? $this->users['admin'] : null, $read);
        }
    }

    /**
     * 90 days of page views and contact clicks for the dashboard charts.
     */
    private function analytics(): void
    {
        $paths = [
            ['/', 'ar', 60], ['/en', 'en', 18], ['/الخدمات', 'ar', 22], ['/المشاريع', 'ar', 26], ['/طلب-عرض-سعر', 'ar', 14],
            ['/تواصل-معنا', 'ar', 9], ['/المقالات', 'ar', 7], ['/en/services', 'en', 6], ['/en/projects', 'en', 7],
            ['/الوظائف', 'ar', 5], ['/الأسئلة-الشائعة', 'ar', 4], ['/من-نحن', 'ar', 8],
        ];
        $rows = [];

        for ($day = 89; $day >= 0; $day--) {
            $date = today()->subDays($day);
            $trend = 0.6 + (89 - $day) / 89 * 0.8;
            $weekend = in_array($date->dayOfWeek, [Carbon::FRIDAY], true) ? 0.7 : 1.0;

            foreach ($paths as [$path, $locale, $base]) {
                $views = (int) round($base * $trend * $weekend * (mt_rand(70, 130) / 100));

                if ($views > 0) {
                    $rows[] = ['date' => $date->toDateString(), 'path' => $path, 'locale' => $locale, 'views' => $views];
                }
            }

            $clicks = (int) round(mt_rand(2, 7) * $trend);
            for ($c = 0; $c < $clicks; $c++) {
                $type = ['whatsapp_click', 'whatsapp_click', 'call_click', 'email_click'][mt_rand(0, 3)];
                $at = $date->copy()->setTime(mt_rand(8, 22), mt_rand(0, 59));
                SiteEvent::query()->insert([
                    'type' => $type, 'path' => $paths[mt_rand(0, 5)][0], 'locale' => mt_rand(0, 4) ? 'ar' : 'en',
                    'device' => mt_rand(0, 3) ? 'mobile' : 'desktop', 'utm' => null, 'created_at' => $at,
                ]);
            }
        }

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('page_views_daily')->insert($chunk);
        }

        foreach (QuoteRequest::query()->get(['created_at', 'source', 'locale']) as $quote) {
            SiteEvent::query()->insert([
                'type' => 'quote_submit', 'path' => '/طلب-عرض-سعر', 'locale' => $quote->locale,
                'device' => $quote->source['device'] ?? 'mobile', 'utm' => null, 'created_at' => $quote->created_at,
            ]);
        }
    }

    private function redirects(): void
    {
        foreach ([
            ['/old-services', '/الخدمات', 301, 37],
            ['/portfolio', '/المشاريع', 301, 112],
            ['/careers.html', '/الوظائف', 301, 9],
        ] as [$from, $to, $code, $hits]) {
            $redirect = Redirect::query()->create(['from_path' => $from, 'to_path' => $to, 'status_code' => $code, 'is_auto' => false]);
            $redirect->forceFill(['hits' => $hits, 'last_hit_at' => now()->subDays(mt_rand(0, 6))])->saveQuietly();
        }
    }
}
