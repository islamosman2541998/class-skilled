<?php

use App\Enums\HeroMediaType;
use App\Livewire\Site\PostsIndex;
use App\Livewire\Site\ProjectsIndex;
use App\Livewire\Site\SearchPage;
use App\Models\Faq;
use App\Models\JobPost;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Services\AssetUploader;
use App\Support\LocalizedRoute;
use App\Support\Preview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function publishedProject(array $attributes = []): Project
{
    return Project::create(array_merge([
        'title' => ['ar' => 'مشروع تجريبي', 'en' => 'Demo project'],
        'location' => ['ar' => 'أبوظبي', 'en' => 'Abu Dhabi'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ], $attributes));
}

test('services index and detail render in both locales; inactive services are 404', function () {
    $service = Service::create(['title' => ['ar' => 'العزل الحراري', 'en' => 'Thermal insulation'], 'excerpt' => ['ar' => 'ملخص'], 'scope' => ['ar' => ['بند أول'], 'en' => ['First item']]]);
    $service->faqs()->create(['question' => ['ar' => 'سؤال الخدمة؟', 'en' => 'Service question?'], 'answer' => ['ar' => 'جواب', 'en' => 'Answer']]);
    $hidden = Service::create(['title' => ['ar' => 'مخفية', 'en' => 'Hidden'], 'is_active' => false]);

    $this->get(LocalizedRoute::url('services.index', 'ar'))->assertOk()->assertSee('العزل الحراري')->assertDontSee('مخفية');
    $this->get($service->localizedUrl('ar'))->assertOk()
        ->assertSee('بند أول')
        ->assertSee('سؤال الخدمة؟')
        ->assertSee('?service='.$service->id, false)
        ->assertSee('hreflang="en" href="'.$service->localizedUrl('en').'"', false);
    $this->get($hidden->localizedUrl('ar'))->assertNotFound();

    $this->refreshApplicationWithLocale('en');
    $service = Service::create(['title' => ['ar' => 'العزل الحراري', 'en' => 'Thermal insulation'], 'scope' => ['ar' => ['بند أول'], 'en' => ['First item']]]);
    $this->get($service->localizedUrl('en'))->assertOk()->assertSee('Thermal insulation')->assertSee('First item');
});

test('service "our work" gallery slides images and videos with the custom player', function () {
    Storage::fake('public');
    $service = Service::create(['title' => ['ar' => 'أرضيات الإيبوكسي', 'en' => 'Epoxy flooring']]);
    $uploader = app(AssetUploader::class);
    $poster = $uploader->uploadImage(UploadedFile::fake()->image('poster.jpg', 900, 600));
    $video = $uploader->uploadVideo(UploadedFile::fake()->create('site.mp4', 200, 'video/mp4'), ['duration' => 95, 'poster_asset_id' => $poster->id]);
    $service->syncAssets('gallery', [
        $uploader->uploadImage(UploadedFile::fake()->image('a.jpg', 900, 600))->id,
        $video->id,
        $uploader->createEmbed('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $poster->id)->id,
    ]);

    $this->get($service->localizedUrl('ar'))->assertOk()
        ->assertSee(__('services.gallery'))
        ->assertSee('x-data="mediaGallery', false)
        ->assertSee('x-data="videoPlayer"', false)
        ->assertSee('mg-thumbs', false)
        ->assertSee('x-ref="next"', false)
        ->assertSee($video->url(), false)
        ->assertSee('1:35')
        ->assertSee(__('media.play').': ')
        ->assertSee('dQw4w9WgXcQ', false)
        ->assertSee('role="dialog"', false);
});

test('drafts are 404 unless a signed preview is active', function () {
    $draft = publishedProject(['status' => 'draft', 'published_at' => null]);

    $this->get($draft->localizedUrl('ar'))->assertNotFound();

    $this->withSession([Preview::SESSION_KEY => ['project:'.$draft->id => time() + 600]])
        ->get($draft->localizedUrl('ar'))
        ->assertOk()
        ->assertSee(__('site.preview_ribbon'))
        ->assertSee('noindex', false);
});

test('scheduled posts stay hidden until their time', function () {
    $post = Post::create(['title' => ['ar' => 'مقال مجدول', 'en' => 'Scheduled'], 'body' => ['ar' => '<p>نص</p>'], 'status' => 'published', 'published_at' => now()->addHour()]);

    $this->get($post->localizedUrl('ar'))->assertNotFound();

    $this->travel(2)->hours();
    $this->get($post->localizedUrl('ar'))->assertOk()->assertSee('مقال مجدول');
});

test('arabic-only items are not reachable in english and the language switch falls back to home', function () {
    $project = publishedProject(['title' => ['ar' => 'مشروع عربي فقط']]);

    expect($project->localizedUrl('en'))->toBeNull();

    $this->get($project->localizedUrl('ar'))->assertOk()
        ->assertSee('href="'.url('/en').'" hreflang="en"', false)
        ->assertDontSee('rel="alternate" hreflang="en"', false);
    $this->get('/en/projects/'.$project->getTranslation('slug', 'ar'))->assertNotFound();
});

test('project detail shows facts, gallery lightbox and related projects', function () {
    Storage::fake('public');
    $category = ProjectCategory::create(['name' => ['ar' => 'تشطيبات', 'en' => 'Finishing']]);
    $project = publishedProject(['category_id' => $category->id, 'area_m2' => '850.00']);
    $other = publishedProject(['title' => ['ar' => 'مشروع مشابه', 'en' => 'Similar'], 'category_id' => $category->id]);
    $uploader = app(AssetUploader::class);
    $project->syncAssets('gallery', [
        $uploader->uploadImage(UploadedFile::fake()->image('a.jpg', 900, 600))->id,
        $uploader->uploadImage(UploadedFile::fake()->image('b.jpg', 900, 600))->id,
        $uploader->createEmbed('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $uploader->uploadImage(UploadedFile::fake()->image('poster.jpg', 900, 600))->id)->id,
    ]);

    $this->get($project->localizedUrl('ar'))->assertOk()
        ->assertSee('x-data="lightbox"', false)
        ->assertSee('x-data="gallerySlider', false)
        ->assertSee('gallery-thumbs', false)
        ->assertSee('x-ref="next"', false)
        ->assertSee(__('lightbox.play_video'))
        ->assertSee('dQw4w9WgXcQ', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('850 م²')
        ->assertSee('مشروع مشابه')
        ->assertSee(__('projects.similar_cta'));

    expect($other->id)->toBeGreaterThan(0);
});

test('projects index filters by category and service, sorts and loads more', function () {
    $studio = ProjectCategory::create(['name' => ['ar' => 'استوديوهات', 'en' => 'Studios']]);
    $finishing = ProjectCategory::create(['name' => ['ar' => 'تشطيبات', 'en' => 'Finishing']]);
    $service = Service::create(['title' => ['ar' => 'إيبوكسي', 'en' => 'Epoxy']]);

    foreach (range(1, 10) as $i) {
        publishedProject(['title' => ['ar' => 'استوديو '.$i, 'en' => 'Studio '.$i], 'category_id' => $studio->id, 'published_at' => now()->subDays($i)]);
    }
    $epoxy = publishedProject(['title' => ['ar' => 'أرضية مصنع', 'en' => 'Factory floor'], 'category_id' => $finishing->id, 'published_at' => now()->subDays(30)]);
    $epoxy->services()->attach($service);

    Livewire::test(ProjectsIndex::class)
        ->assertSee('استوديو 1')
        ->assertDontSee('استوديو 10')
        ->call('loadMore')
        ->assertSee('استوديو 10')
        ->call('setCategory', $finishing->id)
        ->assertSee('أرضية مصنع')
        ->assertDontSee('استوديو 1')
        ->call('setCategory', null)
        ->set('service', $service->id)
        ->assertSee('أرضية مصنع')
        ->assertDontSee('استوديو 2')
        ->set('service', null)
        ->set('sort', 'oldest')
        ->assertSeeHtmlInOrder(['wire:key="p-'.$epoxy->id.'"', 'wire:key="p-'.($epoxy->id - 1).'"'])
        ->set('search', 'مصنع')
        ->assertSee('أرضية مصنع')
        ->assertDontSee('استوديو 3');

    // The category route preselects its category.
    $this->get($finishing->localizedUrl('ar'))->assertOk()->assertSee('أرضية مصنع')->assertDontSee('استوديو 4');
});

test('posts index searches and filters by category with a featured post on top', function () {
    $tips = PostCategory::create(['name' => ['ar' => 'نصائح', 'en' => 'Tips']]);
    Post::create(['title' => ['ar' => 'دليل العزل المائي', 'en' => 'Waterproofing guide'], 'is_featured' => true, 'post_category_id' => $tips->id, 'status' => 'published', 'published_at' => now()->subDay()]);
    Post::create(['title' => ['ar' => 'اختيار البلاط', 'en' => 'Choosing tiles'], 'status' => 'published', 'published_at' => now()->subDays(2)]);

    Livewire::test(PostsIndex::class)
        ->assertSee(__('posts.featured'))
        ->assertSee('دليل العزل المائي')
        ->assertSee('اختيار البلاط')
        ->set('search', 'البلاط')
        ->assertSee('اختيار البلاط')
        ->assertDontSee('دليل العزل المائي')
        ->set('search', '')
        ->call('setCategory', $tips->id)
        ->assertSee('دليل العزل المائي')
        ->assertDontSee('اختيار البلاط');
});

test('search page groups results, highlights matches and needs two characters', function () {
    Service::create(['title' => ['ar' => 'عزل الأسطح', 'en' => 'Roof insulation']]);
    publishedProject(['title' => ['ar' => 'عزل سطح فيلا', 'en' => 'Villa roof insulation']]);

    Livewire::test(SearchPage::class)
        ->set('query', 'ع')
        ->assertSee(__('search.min_chars'))
        ->set('query', 'عزل')
        ->assertSee(__('search.group_services'))
        ->assertSee(__('search.group_projects'))
        ->assertSeeHtml('<mark')
        ->set('query', 'غير موجود إطلاقا')
        ->assertSee(__('search.no_results'));

    $this->get(LocalizedRoute::url('search', 'ar').'?q=عزل')->assertOk()->assertSee('عزل الأسطح');
});

test('custom pages render main media, blocks and attachments in order', function () {
    Storage::fake('public');
    $uploader = app(AssetUploader::class);
    $page = Page::create([
        'title' => ['ar' => 'من نحن', 'en' => 'About us'],
        'excerpt' => ['ar' => 'نبذة'],
        'hero_type' => HeroMediaType::Image,
        'blocks' => [['type' => 'rich_text', 'data' => ['html' => ['ar' => '<p>محتوى الصفحة</p>', 'en' => '']]]],
        'attachments_config' => [
            'files' => ['visible' => true, 'title' => ['ar' => 'الملفات'], 'display' => 'list'],
            'images' => ['visible' => true, 'title' => ['ar' => 'الصور'], 'display' => 'grid', 'columns' => 3],
        ],
        'status' => 'published',
    ]);
    $page->syncAssets('hero_image', [$uploader->uploadImage(UploadedFile::fake()->image('hero.jpg', 1600, 900))->id]);
    $page->syncAssets('att_files', [$uploader->uploadDocument(UploadedFile::fake()->create('profile.pdf', 120, 'application/pdf'))->id]);
    $page->syncAssets('att_images', [$uploader->uploadImage(UploadedFile::fake()->image('g.jpg', 800, 600))->id]);

    $this->get($page->localizedUrl('ar'))->assertOk()
        ->assertSeeInOrder(['fetchpriority="high"', 'محتوى الصفحة', 'الملفات', 'PDF', 'الصور', 'x-data="lightbox"'], false);
});

test('faq, careers and closed jobs', function () {
    Faq::create(['question' => ['ar' => 'هل تقدمون ضمانًا؟', 'en' => 'Warranty?'], 'answer' => ['ar' => 'نعم', 'en' => 'Yes']]);
    $open = JobPost::create(['title' => ['ar' => 'مهندس موقع', 'en' => 'Site engineer'], 'job_type' => 'full_time', 'status' => 'published', 'published_at' => now()->subDay()]);
    $closed = JobPost::create(['title' => ['ar' => 'محاسب', 'en' => 'Accountant'], 'job_type' => 'full_time', 'status' => 'published', 'published_at' => now()->subMonth(), 'closes_at' => now()->subWeek()]);

    $this->get(LocalizedRoute::url('faq', 'ar'))->assertOk()->assertSee('هل تقدمون ضمانًا؟');
    $this->get(LocalizedRoute::url('careers.index', 'ar'))->assertOk()->assertSee('مهندس موقع')->assertDontSee('محاسب');
    $this->get($open->localizedUrl('ar'))->assertOk()->assertSee(__('careers.apply_title'));
    $this->get($closed->localizedUrl('ar'))->assertStatus(410)->assertSee(__('careers.view_open_jobs'));
});

test('seeded legal pages render', function () {
    $pages = Page::query()->published()->get();

    expect($pages)->not->toBeEmpty();

    foreach ($pages as $page) {
        $this->get($page->localizedUrl('ar'))->assertOk();
    }
});
