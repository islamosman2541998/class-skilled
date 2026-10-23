<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DashboardWidget;
use App\Models\Faq;
use App\Models\JobPost;
use App\Models\Post;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\RequestStatus;
use App\Models\Service;
use App\Models\SiteEvent;
use App\Services\Sitemap;
use App\Support\LocalizedRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

/**
 * @return array<string, mixed>
 */
function jsonLd(string $html): array
{
    preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);
    $data = json_decode($m[1] ?? '', true);

    expect($data)->toBeArray()->and($data['@context'] ?? null)->toBe('https://schema.org');

    return collect($data['@graph'])->keyBy('@type')->all();
}

test('pages expose canonical, hreflang with x-default and Open Graph tags', function () {
    $service = Service::create(['title' => ['ar' => 'العزل المائي', 'en' => 'Waterproofing'], 'excerpt' => ['ar' => 'وصف الخدمة']]);
    $arabicOnly = Service::create(['title' => ['ar' => 'خدمة عربية فقط']]);

    $html = $this->get($service->localizedUrl('ar'))->assertOk()->getContent();

    expect($html)->toContain('<link rel="canonical" href="'.$service->localizedUrl('ar').'">')
        ->toContain('hreflang="en" href="'.$service->localizedUrl('en').'"')
        ->toContain('hreflang="x-default" href="'.$service->localizedUrl('ar').'"')
        ->toContain('<meta property="og:title" content="العزل المائي |')
        ->toContain('<meta name="twitter:card"');

    $this->get($arabicOnly->localizedUrl('ar'))->assertOk()->assertDontSee('rel="alternate" hreflang="en"', false);

    $this->get(LocalizedRoute::url('search', 'ar').'?q=test')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

test('json-ld describes the company, breadcrumbs and each page type', function () {
    $service = Service::create(['title' => ['ar' => 'التشطيبات', 'en' => 'Finishing']]);
    $service->faqs()->create(['question' => ['ar' => 'كم المدة؟'], 'answer' => ['ar' => 'حسب المساحة']]);
    $post = Post::create(['title' => ['ar' => 'مقال', 'en' => 'Article'], 'body' => ['ar' => '<p>نص</p>'], 'status' => 'published', 'published_at' => now()->subDay()]);
    $job = JobPost::create(['title' => ['ar' => 'مهندس', 'en' => 'Engineer'], 'job_type' => 'contract', 'status' => 'published', 'published_at' => now()->subDay(), 'closes_at' => now()->addMonth()]);
    $project = Project::create(['title' => ['ar' => 'مشروع', 'en' => 'Project'], 'status' => 'published', 'published_at' => now()->subDay()]);
    Faq::create(['question' => ['ar' => 'سؤال عام؟'], 'answer' => ['ar' => 'جواب']]);

    $home = jsonLd($this->get('/')->getContent());
    expect($home['GeneralContractor']['address']['addressCountry'])->toBe('AE')
        ->and($home['GeneralContractor']['identifier']['value'])->toBe('CN-5898460')
        ->and($home['WebSite']['potentialAction']['@type'])->toBe('SearchAction');

    $serviceLd = jsonLd($this->get($service->localizedUrl('ar'))->getContent());
    expect($serviceLd)->toHaveKeys(['Service', 'FAQPage', 'BreadcrumbList'])
        ->and($serviceLd['BreadcrumbList']['itemListElement'])->toHaveCount(3);

    expect(jsonLd($this->get($post->localizedUrl('ar'))->getContent())['Article']['datePublished'])->not->toBeEmpty();
    $posting = jsonLd($this->get($job->localizedUrl('ar'))->getContent())['JobPosting'];
    expect($posting['employmentType'])->toBe('CONTRACTOR')->and($posting['validThrough'])->not->toBeEmpty()
        ->and($posting['jobLocation']['address']['addressCountry'])->toBe('AE');
    expect(jsonLd($this->get($project->localizedUrl('ar'))->getContent()))->toHaveKey('CreativeWork');
    expect(jsonLd($this->get(LocalizedRoute::url('faq', 'ar'))->getContent()))->toHaveKey('FAQPage');
});

test('sitemaps list published items with alternates and skip drafts and noindex', function () {
    $published = Project::create(['title' => ['ar' => 'منشور', 'en' => 'Published'], 'status' => 'published', 'published_at' => now()->subDay()]);
    Project::create(['title' => ['ar' => 'مسودة', 'en' => 'Draft'], 'status' => 'draft']);
    $hidden = Project::create(['title' => ['ar' => 'مخفي', 'en' => 'Hidden'], 'status' => 'published', 'published_at' => now()->subDay()]);
    $hidden->seo()->create(['robots' => 'noindex']);

    File::deleteDirectory(app(Sitemap::class)->directory());
    expect(file_get_contents($this->get('/sitemap.xml')->assertOk()->baseResponse->getFile()->getPathname()))->toContain('sitemaps/projects.xml');

    $xml = (string) file_get_contents($this->get('/sitemaps/projects.xml')->assertOk()->baseResponse->getFile()->getPathname());
    expect($xml)->toContain(Sitemap::encode((string) $published->localizedUrl('ar')))
        ->toContain('hreflang="en" href="'.$published->localizedUrl('en').'"')
        ->not->toContain('draft')
        ->not->toContain(Sitemap::encode((string) $hidden->localizedUrl('ar')));

    $this->get('/sitemaps/unknown.xml')->assertNotFound();
});

test('robots.txt ends with the sitemap', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'));
});

test('the beacon counts page views and clicks, ignores bots and is throttled', function () {
    $browser = ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile'];

    $this->postJson('/t', ['type' => 'page_view', 'path' => '/الخدمات?x=1', 'locale' => 'ar'], $browser)->assertNoContent();
    $this->postJson('/t', ['type' => 'page_view', 'path' => '/الخدمات', 'locale' => 'ar'], $browser)->assertNoContent();
    $this->postJson('/t', ['type' => 'whatsapp_click', 'path' => '/', 'locale' => 'en'], $browser)->assertNoContent();
    $this->postJson('/t', ['type' => 'page_view', 'path' => '/'], ['User-Agent' => 'Googlebot/2.1'])->assertNoContent();
    $this->postJson('/t', ['type' => 'drop table', 'path' => '/'], $browser)->assertNoContent();

    expect(DB::table('page_views_daily')->where('path', '/الخدمات')->value('views'))->toBe(2)
        ->and(DB::table('page_views_daily')->count())->toBe(1)
        ->and(SiteEvent::query()->where('type', 'whatsapp_click')->first()?->device)->toBe('mobile');

    RateLimiter::clear('');
    foreach (range(1, 60) as $i) {
        $this->postJson('/t', ['type' => 'page_view', 'path' => '/'], $browser);
    }
    $this->postJson('/t', ['type' => 'page_view', 'path' => '/'], $browser)->assertStatus(429);
});

test('the dashboard home shows real numbers per period', function () {
    $won = RequestStatus::query()->where('request_type', 'quote')->where('key', 'won')->firstOrFail();
    $service = Service::create(['title' => ['ar' => 'إيبوكسي', 'en' => 'Epoxy']]);
    QuoteRequest::create(['name' => 'أ', 'phone' => '1', 'locale' => 'ar', 'service_id' => $service->id, 'status_id' => $won->id]);
    QuoteRequest::create(['name' => 'ب', 'phone' => '2', 'locale' => 'ar', 'service_id' => $service->id]);
    QuoteRequest::create(['name' => 'ج', 'phone' => '3', 'locale' => 'ar'])->forceFill(['created_at' => now()->subDays(20)])->save();
    SiteEvent::query()->create(['type' => 'call_click', 'path' => '/']);
    DB::table('page_views_daily')->insert(['date' => today()->toDateString(), 'path' => '/المشاريع', 'locale' => 'ar', 'views' => 42]);

    $this->actingAs(userWithRole('admin'));

    Livewire::test(Dashboard::class)->assertOk()->call('setPeriod', 7)->assertSet('period', 7)->call('setPeriod', 99)->assertSet('period', 30);

    $stats = Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'stats', 'period' => 7])->viewData('stats');
    expect($stats['quotes']['value'])->toBe(2)
        ->and($stats['won']['value'])->toBe('1 · 50%')
        ->and($stats['clicks']['value'])->toBe(1)
        ->and($stats['visits']['value'])->toBe(42);

    expect(Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'stats', 'period' => 30])->viewData('stats')['quotes']['value'])->toBe(3);
    expect(Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'services', 'period' => 7])->viewData('rows')[0])->toBe(['label' => 'إيبوكسي', 'total' => 2]);
    Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'pages', 'period' => 7])->assertSee('/المشاريع')->assertSee('42');
    Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'latest', 'period' => 7])->assertSee('QR-');
    Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'attention', 'period' => 7])->assertSee(__('admin.dashboard.licence_expiry'));
    Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'chart', 'period' => 7])->assertSee('canvas', false);
});

test('editors see the dashboard without request widgets', function () {
    $this->actingAs(userWithRole('editor'));

    Livewire::test(Dashboard::class)->assertOk()->assertDontSee('name="latest"', false);
    $stats = Livewire::withoutLazyLoading()->test(DashboardWidget::class, ['name' => 'stats', 'period' => 30]);
    $stats->assertDontSee(__('admin.dashboard.stat_quotes'))->assertSee(__('admin.dashboard.stat_visits'));
});
