<?php

namespace Database\Seeders;

use App\Enums\DataSource;
use App\Enums\SectionBackground;
use App\Enums\SectionType;
use Illuminate\Support\Str;

/**
 * Default home page sections and copy (_design/content/content.md).
 * Images are attached by DemoContentSeeder (or later from the dashboard).
 *
 * `data` schema per type is documented in PROGRESS.md (phase 01).
 */
class DefaultHomeSections
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $quote = ['type' => 'system_page', 'route' => 'quote'];

        return [
            [
                'type' => SectionType::Hero,
                'data' => [
                    'autoplay' => 6,
                    'show_arrows' => true,
                    'show_progress' => true,
                    'slides' => [
                        self::slide(
                            ['ar' => 'نبني مشاريع تتكلم عنا', 'en' => 'Projects that speak for us'],
                            ['ar' => 'شركة مقاولات عامة مصنفة من بلدية مدينة أبوظبي، ننفذ من المعاينة حتى التسليم.', 'en' => 'A general contractor classified by Abu Dhabi City Municipality, from site visit to handover.'],
                            [['ar' => 'اطلب عرض سعر', 'en' => 'Request a quote'], $quote],
                            [['ar' => 'شاهد مشاريعنا', 'en' => 'View our projects'], ['type' => 'system_page', 'route' => 'projects.index']],
                            ['ar' => 'تجهيز استوديو تصوير، أبوظبي', 'en' => 'Film studio fit-out, Abu Dhabi'],
                        ),
                        self::slide(
                            ['ar' => 'تشطيبات بتفاصيل دقيقة', 'en' => 'Finishing with precise detail'],
                            ['ar' => 'من الأسقف والتجاليد إلى الإضاءة المخفية والأرضيات.', 'en' => 'From ceilings and wall cladding to concealed lighting and floors.'],
                            [['ar' => 'خدمات التشطيب', 'en' => 'Finishing services'], ['type' => 'system_page', 'route' => 'services.index']],
                            [['ar' => 'اطلب عرض سعر', 'en' => 'Request a quote'], $quote],
                            ['ar' => 'تشطيب مكاتب إدارية', 'en' => 'Office finishing'],
                        ),
                        self::slide(
                            ['ar' => 'هياكل خرسانية بمعايير واضحة', 'en' => 'Concrete structures to clear standards'],
                            ['ar' => 'فلل ومبانٍ سكنية وتجارية بجدول زمني ملتزم.', 'en' => 'Villas, residential and commercial buildings on a committed schedule.'],
                            [['ar' => 'إنشاء الفلل والمباني', 'en' => 'Villas & buildings'], ['type' => 'system_page', 'route' => 'services.index']],
                            [['ar' => 'اطلب عرض سعر', 'en' => 'Request a quote'], $quote],
                            ['ar' => 'أعمال سقف خرساني', 'en' => 'Concrete slab works'],
                        ),
                    ],
                ],
            ],
            [
                'type' => SectionType::About,
                'title' => ['ar' => 'شريك حقيقي في نجاح مشروعك', 'en' => 'A true partner in your success'],
                'cta_label' => ['ar' => 'تعرّف علينا', 'en' => 'About us'],
                'cta_link' => null,
                'data' => [
                    'text' => [
                        'ar' => 'نسعى لأن نُحدث فرقًا ملموسًا في قطاع البناء، وننقل مفهوم المقاول من منفّذ للأعمال إلى شريك حقيقي في النجاح.',
                        'en' => 'We aim to make a real difference in construction, moving the contractor from someone who executes to a genuine partner in success.',
                    ],
                    'image_asset_id' => null,
                ],
            ],
            [
                'type' => SectionType::Services,
                'title' => ['ar' => 'خدماتنا', 'en' => 'Our services'],
                'subtitle' => ['ar' => 'من الأساسات حتى التشطيب النهائي والتجهيزات الخاصة.', 'en' => 'From foundations to final finishing and specialist fit-out.'],
                'cta_label' => ['ar' => 'كل الخدمات', 'en' => 'All services'],
                'cta_link' => ['type' => 'system_page', 'route' => 'services.index'],
                'source' => DataSource::All,
                'items_limit' => 8,
                'background' => SectionBackground::Dark,
                'data' => ['item_ids' => [], 'display' => 'slider', 'autoplay' => false, 'bg_asset_id' => null],
            ],
            [
                'type' => SectionType::FeatureBand,
                'title' => ['ar' => 'استوديوهات التصوير والديكورات السينمائية', 'en' => 'Film studios & production sets'],
                'subtitle' => ['ar' => 'ننفذ مواقع التصوير من الهيكل حتى آخر تفصيلة، مع الالتزام بمواعيد الإنتاج الضيقة.', 'en' => 'We deliver filming locations from the structure to the last detail, on tight production schedules.'],
                'cta_label' => ['ar' => 'تفاصيل الخدمة', 'en' => 'Service details'],
                'cta_link' => ['type' => 'system_page', 'route' => 'services.index'],
                'background' => SectionBackground::Dark,
                'data' => [
                    'bullets' => [
                        ['ar' => 'هياكل التراس وتعليق الإضاءة', 'en' => 'Truss structures and lighting rigs'],
                        ['ar' => 'بناء الديكورات والسِّتّات', 'en' => 'Set and décor construction'],
                        ['ar' => 'العزل الصوتي للقاعات', 'en' => 'Acoustic insulation for halls'],
                        ['ar' => 'التركيب والفك حسب جدول التصوير', 'en' => 'Installation and strike to the shooting schedule'],
                    ],
                    'image_asset_ids' => [],
                ],
            ],
            [
                'type' => SectionType::Projects,
                'title' => ['ar' => 'من مشاريعنا', 'en' => 'Selected projects'],
                'subtitle' => ['ar' => 'أعمال نفذناها في أبوظبي، من التشطيب إلى الإنشاءات والاستوديوهات.', 'en' => 'Work we delivered in Abu Dhabi, from finishing to construction and studios.'],
                'cta_label' => ['ar' => 'كل المشاريع', 'en' => 'All projects'],
                'cta_link' => ['type' => 'system_page', 'route' => 'projects.index'],
                'source' => DataSource::Latest,
                'items_limit' => 8,
                'background' => SectionBackground::Paper,
                'data' => ['item_ids' => [], 'display' => 'slider', 'autoplay' => false, 'show_filters' => true, 'bg_asset_id' => null],
            ],
            [
                'type' => SectionType::WhyUs,
                'title' => ['ar' => 'لماذا كلاس سكيلد', 'en' => 'Why Class Skilled'],
                'subtitle' => ['ar' => 'معايير نلتزم بها في كل مشروع، مهما كان حجمه.', 'en' => 'Standards we keep on every project, whatever its size.'],
                'data' => [
                    'items' => [
                        ['title' => ['ar' => 'الجودة', 'en' => 'Quality'], 'text' => ['ar' => 'مواد معتمدة وتنفيذ يراجعه مهندس مسؤول في كل مرحلة.', 'en' => 'Approved materials, reviewed by a responsible engineer at every stage.']],
                        ['title' => ['ar' => 'الالتزام بالمواعيد', 'en' => 'On schedule'], 'text' => ['ar' => 'جدول زمني واضح من اليوم الأول، وتحديثات منتظمة عن سير العمل.', 'en' => 'A clear timeline from day one, with regular progress updates.']],
                        ['title' => ['ar' => 'السلامة', 'en' => 'Safety'], 'text' => ['ar' => 'فرق مدربة وإجراءات سلامة مطبقة في كل موقع.', 'en' => 'Trained crews and safety procedures on every site.']],
                        ['title' => ['ar' => 'الشفافية', 'en' => 'Transparency'], 'text' => ['ar' => 'عرض سعر مفصل بالبنود والكميات، بدون تكاليف مخفية.', 'en' => 'Itemised quotations with quantities and no hidden costs.']],
                    ],
                ],
            ],
            [
                'type' => SectionType::Process,
                'title' => ['ar' => 'كيف نعمل', 'en' => 'How we work'],
                'subtitle' => ['ar' => 'أربع خطوات واضحة من أول زيارة حتى التسليم.', 'en' => 'Four clear steps from the first visit to handover.'],
                'background' => SectionBackground::Paper,
                'data' => [
                    'steps' => [
                        ['title' => ['ar' => 'المعاينة', 'en' => 'Site visit'], 'text' => ['ar' => 'زيارة الموقع وفهم المطلوب وأخذ المقاسات.', 'en' => 'We visit, understand the brief and measure.']],
                        ['title' => ['ar' => 'عرض السعر', 'en' => 'Quotation'], 'text' => ['ar' => 'عرض مفصل بالبنود والكميات ومدة التنفيذ.', 'en' => 'Itemised, with quantities and duration.']],
                        ['title' => ['ar' => 'التنفيذ', 'en' => 'Execution'], 'text' => ['ar' => 'فريق عمل ومهندس مسؤول وتقارير متابعة.', 'en' => 'A dedicated crew, a responsible engineer and progress reports.']],
                        ['title' => ['ar' => 'التسليم والضمان', 'en' => 'Handover & warranty'], 'text' => ['ar' => 'استلام نهائي وشهادة ضمان للأعمال.', 'en' => 'Final handover and a warranty certificate.']],
                    ],
                ],
            ],
            [
                'type' => SectionType::Testimonials,
                'title' => ['ar' => 'آراء العملاء', 'en' => 'What clients say'],
                'is_visible' => false,
                'source' => DataSource::All,
                'items_limit' => 8,
                'background' => SectionBackground::DarkPhoto,
                'data' => ['item_ids' => [], 'display' => 'slider', 'autoplay' => false, 'bg_asset_id' => null],
            ],
            [
                'type' => SectionType::Clients,
                'title' => ['ar' => 'عملاؤنا وشركاؤنا', 'en' => 'Clients & partners'],
                'is_visible' => false,
                'source' => DataSource::All,
                'items_limit' => 12,
                'background' => SectionBackground::White,
                'data' => ['item_ids' => [], 'display' => 'slider', 'autoplay' => true, 'bg_asset_id' => null],
            ],
            [
                'type' => SectionType::Posts,
                'title' => ['ar' => 'أحدث المقالات', 'en' => 'Latest articles'],
                'subtitle' => ['ar' => 'نصائح عملية قبل أن تبدأ مشروعك.', 'en' => 'Practical advice before you start your project.'],
                'cta_label' => ['ar' => 'كل المقالات', 'en' => 'All articles'],
                'cta_link' => ['type' => 'system_page', 'route' => 'posts.index'],
                'source' => DataSource::Latest,
                'items_limit' => 6,
                'background' => SectionBackground::White,
                'data' => ['item_ids' => [], 'display' => 'slider', 'autoplay' => false, 'bg_asset_id' => null],
            ],
            [
                'type' => SectionType::Cta,
                'title' => ['ar' => 'عندك مشروع؟ ابدأ بطلب عرض سعر', 'en' => 'Have a project? Start with a quote request'],
                'subtitle' => ['ar' => 'أرسل تفاصيل مشروعك ومخططاته، ويتواصل معك فريقنا لتحديد موعد المعاينة.', 'en' => 'Send your details and drawings and our team will contact you to book a site visit.'],
                'background' => SectionBackground::Dark,
                'data' => [
                    'primary' => ['label' => ['ar' => 'اطلب عرض سعر', 'en' => 'Request a quote'], 'link' => $quote],
                    'secondary' => ['label' => ['ar' => 'راسلنا على واتساب', 'en' => 'Message us on WhatsApp'], 'link' => ['type' => 'whatsapp']],
                ],
            ],
        ];
    }

    /**
     * @param  array{ar: string, en: string}  $title
     * @param  array{ar: string, en: string}  $text
     * @param  array{0: array{ar: string, en: string}, 1: array<string, mixed>}  $primary
     * @param  array{0: array{ar: string, en: string}, 1: array<string, mixed>}  $secondary
     * @param  array{ar: string, en: string}  $caption
     * @return array<string, mixed>
     */
    private static function slide(array $title, array $text, array $primary, array $secondary, array $caption): array
    {
        return [
            'id' => (string) Str::uuid(),
            'image_asset_id' => null,
            'mobile_image_asset_id' => null,
            'video_asset_id' => null,
            'title' => $title,
            'text' => $text,
            'primary' => ['label' => $primary[0], 'link' => $primary[1]],
            'secondary' => ['label' => $secondary[0], 'link' => $secondary[1]],
            'caption' => $caption,
            'overlay' => 45,
        ];
    }
}
