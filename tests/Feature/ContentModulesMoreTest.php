<?php

use App\Enums\SectionType;
use App\Livewire\Admin\Content\Certificates;
use App\Livewire\Admin\Content\Faqs;
use App\Livewire\Admin\Content\HeroSlide;
use App\Livewire\Admin\Content\HomeSections;
use App\Livewire\Admin\Content\Jobs\Form as JobForm;
use App\Livewire\Admin\Content\Pages\Form as PageForm;
use App\Livewire\Admin\Content\Posts\Form as PostForm;
use App\Livewire\Admin\Content\ProjectCategories;
use App\Livewire\Admin\Content\Projects\Form as ProjectForm;
use App\Livewire\Admin\Content\Projects\Index as ProjectIndex;
use App\Livewire\Admin\Content\Services\Form as ServiceForm;
use App\Models\Asset;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\JobPost;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Services\AssetUploader;
use App\Services\HomeSectionResolver;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

function img(): Asset
{
    return app(AssetUploader::class)->uploadImage(UploadedFile::fake()->image('i.jpg', 640, 480));
}

test('services save scope, stages, gallery and inline faqs', function () {
    $this->actingAs(userWithRole('editor'));
    $cover = img();

    Livewire::test(ServiceForm::class)
        ->set('form.title.ar', 'أرضيات الإيبوكسي')
        ->set('form.summary.ar', 'ملخص')
        ->set('form.cover', $cover->id)
        ->call('addRepeaterItem', 'form.scope', ['ar' => 'تجهيز السطح', 'en' => 'Surface prep'])
        ->call('addRepeaterItem', 'form.stages', ['ar' => 'معاينة', 'en' => ''])
        ->call('addRepeaterItem', 'form.faqs', ['id' => null, 'q' => ['ar' => 'سؤال؟', 'en' => ''], 'a' => ['ar' => 'جواب', 'en' => '']])
        ->call('save')
        ->assertHasNoErrors();

    $service = Service::query()->latest('id')->first();
    expect($service->workScope('en'))->toBe(['Surface prep'])
        ->and($service->stagesList('ar'))->toBe(['معاينة'])
        ->and($service->faqs()->count())->toBe(1)
        ->and($service->firstAsset('cover')?->id)->toBe($cover->id);

    Livewire::test(ServiceForm::class, ['id' => $service->id])
        ->call('removeRepeaterItem', 'form.faqs', 0)
        ->call('save');
    expect($service->faqs()->count())->toBe(0);
});

test('projects save services, before/after pairs and appear in the list', function () {
    $this->actingAs(userWithRole('editor'));
    $category = ProjectCategory::create(['name' => ['ar' => 'تشطيبات']]);
    $service = Service::create(['title' => ['ar' => 'تشطيب']]);
    [$before, $after] = [img(), img()];

    Livewire::test(ProjectForm::class)
        ->set('form.title.ar', 'مكتب إداري')
        ->set('form.category_id', $category->id)
        ->set('form.service_ids', [(string) $service->id])
        ->set('form.area_m2', 120)
        ->call('addRepeaterItem', 'form.pairs', ['before' => $before->id, 'after' => $after->id])
        ->call('publish')
        ->assertHasNoErrors();

    $project = Project::query()->latest('id')->first();
    expect($project->services->pluck('id')->all())->toBe([$service->id])
        ->and($project->beforeAfterPairs()[0]['after']->id)->toBe($after->id);

    Livewire::test(ProjectIndex::class)
        ->call('setFilter', 'category', (string) $category->id)
        ->assertSee('مكتب إداري')
        ->call('export')->assertFileDownloaded();
});

test('posts compute reading time and jobs save requirements', function () {
    $this->actingAs(userWithRole('editor'));

    Livewire::test(PostForm::class)
        ->set('form.title.ar', 'مقال')
        ->set('form.body.ar', '<p>'.str_repeat('كلمة ', 450).'</p>')
        ->call('publish')
        ->assertHasNoErrors();
    expect(Post::query()->latest('id')->first()->reading_minutes)->toBe(3);

    Livewire::test(JobForm::class)
        ->set('form.title.ar', 'مهندس موقع')
        ->set('form.description.ar', '<p>وصف</p>')
        ->call('addRepeaterItem', 'form.requirements', ['ar' => 'خبرة 3 سنوات', 'en' => '3 years'])
        ->set('form.closes_at', now()->addMonth()->format('Y-m-d'))
        ->call('publish')
        ->assertHasNoErrors();
    $job = JobPost::query()->latest('id')->first();
    expect($job->requirementsList('en'))->toBe(['3 years'])->and($job->isOpen())->toBeTrue();
});

test('simple resources create, validate and filter', function () {
    $this->actingAs(userWithRole('editor'));
    $service = Service::create(['title' => ['ar' => 'خدمة']]);

    Livewire::test(Faqs::class)
        ->call('create')
        ->call('save')->assertHasErrors('form.question.ar')
        ->set('form.question.ar', 'هل تقدمون ضمانًا؟')
        ->set('form.answer.ar', 'نعم')
        ->set('form.service_id', (string) $service->id)
        ->call('save')->assertHasNoErrors()
        ->call('setFilter', 'scope', (string) $service->id)
        ->assertSee('هل تقدمون ضمانًا؟');
    expect(Faq::first()->faqable_id)->toBe($service->id);

    Livewire::test(ProjectCategories::class)
        ->call('create')
        ->set('form.name.ar', 'استوديوهات')
        ->set('form.name.en', 'Studios')
        ->call('save')->assertHasNoErrors();
    expect(ProjectCategory::where('name->en', 'Studios')->first()->getTranslation('slug', 'en'))->toBe('studios');

    Certificate::create(['name' => ['ar' => 'رخصة'], 'expires_at' => now()->addDays(30)]);
    Livewire::test(Certificates::class)
        ->call('setFilter', 'expiry', 'soon')
        ->assertSee('رخصة')
        ->assertSee(__('admin.certificates.expires_soon'));
});

test('home sections are edited, reordered and resolved per type', function () {
    $this->actingAs(userWithRole('editor'));
    $service = Service::create(['title' => ['ar' => 'خدمة أ', 'en' => 'Service A'], 'is_active' => true]);
    $section = HomeSection::query()->where('type', 'services')->first();

    Livewire::test(HomeSections::class)
        ->call('edit', $section->id)
        ->set('section.source', 'manual')
        ->set('section.data.item_ids', [(string) $service->id])
        ->set('section.title.en', 'Our services')
        ->call('save')
        ->assertHasNoErrors();

    $resolved = collect(app(HomeSectionResolver::class)->resolve('en'))->keyBy('type');
    expect($resolved['services']['title'])->toBe('Our services')
        ->and($resolved['services']['items']->pluck('id')->all())->toBe([$service->id])
        ->and($resolved)->not->toHaveKey('facts')
        ->and($resolved['process']['steps'])->toHaveCount(4)
        ->and($resolved['why_us']['items'])->toHaveCount(4)
        ->and($resolved['cta']['primary']['url'])->toContain('/en/request-a-quote')
        ->and($resolved)->not->toHaveKey('hero') // no slide images in the test database
        ->and($resolved)->not->toHaveKey('testimonials'); // hidden by default

    $ids = HomeSection::query()->orderBy('sort')->pluck('id')->reverse()->values()->all();
    Livewire::test(HomeSections::class)->call('reorder', $ids);
    expect(HomeSection::query()->orderBy('sort')->first()->id)->toBe($ids[0]);

    Livewire::test(HomeSections::class)->call('add', SectionType::Stats->value);
    expect(HomeSection::query()->where('type', 'stats')->first()->is_visible)->toBeFalse();
});

test('hero slides are added and edited on their own page, then reordered and deleted', function () {
    $this->actingAs(userWithRole('editor'));
    $hero = HomeSection::query()->where('type', 'hero')->first();
    $hero->forceFill(['data' => [...$hero->data, 'slides' => []]])->save();
    $first = img();
    $second = img();

    $this->get(route('admin.home-sections.slides.create', $hero->id))->assertOk();

    Livewire::test(HeroSlide::class, ['section' => $hero->id])
        ->call('save')
        ->assertHasErrors(['slide.image_asset_id', 'slide.title.ar']);

    Livewire::test(HeroSlide::class, ['section' => $hero->id])
        ->set('slide.image_asset_id', $first->id)
        ->set('slide.title.ar', 'شريحة أولى')
        ->set('slide.primary.label.ar', 'اطلب عرض سعر')
        ->set('slide.primary.link', ['type' => 'system_page', 'route' => 'quote'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.home-sections.index', ['edit' => $hero->id]));

    Livewire::test(HeroSlide::class, ['section' => $hero->id])
        ->set('slide.image_asset_id', $second->id)
        ->set('slide.title.ar', 'شريحة ثانية')
        ->call('save')
        ->assertHasNoErrors();

    $slides = $hero->fresh()->data['slides'];
    expect($slides)->toHaveCount(2)
        ->and($slides[0]['id'])->toBeString()
        ->and($slides[0]['image_asset_id'])->toBe($first->id)
        ->and($slides[0]['primary']['link']['route'])->toBe('quote');

    $this->get(route('admin.home-sections.slides.edit', [$hero->id, $slides[0]['id']]))->assertOk();
    $this->get(route('admin.home-sections.slides.edit', [$hero->id, 'missing']))->assertNotFound();

    Livewire::test(HeroSlide::class, ['section' => $hero->id, 'slideKey' => $slides[0]['id']])
        ->assertSet('slide.title.ar', 'شريحة أولى')
        ->set('slide.title.en', 'First slide')
        ->set('slide.image_asset_id', $second->id)
        ->call('save')
        ->assertHasNoErrors();

    $slides = $hero->fresh()->data['slides'];
    expect($slides)->toHaveCount(2)
        ->and($slides[0]['title']['en'])->toBe('First slide')
        ->and($slides[0]['image_asset_id'])->toBe($second->id);

    Livewire::test(HomeSections::class)->call('edit', $hero->id)->call('reorderSlides', [$slides[1]['id'], $slides[0]['id']]);
    expect(array_column($hero->fresh()->data['slides'], 'id'))->toBe([$slides[1]['id'], $slides[0]['id']]);

    Livewire::test(HomeSections::class)->call('edit', $hero->id)->call('deleteSlide', $slides[1]['id']);
    expect(array_column($hero->fresh()->data['slides'], 'id'))->toBe([$slides[0]['id']]);
});

test('seeded demo content opens and saves in every form without validation errors', function () {
    $this->seed(DemoContentSeeder::class);
    $this->actingAs(userWithRole('admin'));

    foreach ([PageForm::class => Page::class, ServiceForm::class => Service::class, ProjectForm::class => Project::class, PostForm::class => Post::class, JobForm::class => JobPost::class] as $form => $model) {
        foreach ($model::query()->pluck('id') as $id) {
            Livewire::test($form, ['id' => $id])->call('save')->assertHasNoErrors();
        }
    }

    foreach (HomeSection::query()->pluck('id') as $id) {
        Livewire::test(HomeSections::class)->call('edit', $id)->call('save')->assertHasNoErrors();
    }
})->group('slow');
