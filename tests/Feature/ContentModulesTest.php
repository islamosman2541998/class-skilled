<?php

use App\Livewire\Admin\Content\Pages\Form as PageForm;
use App\Livewire\Admin\Content\Pages\Index as PageIndex;
use App\Models\Asset;
use App\Models\Page;
use App\Models\Redirect;
use App\Services\AssetUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

function demoImage(): Asset
{
    return app(AssetUploader::class)->uploadImage(UploadedFile::fake()->image('img.jpg', 800, 600));
}

test('pages are created with blocks, main media and ordered attachments', function () {
    $this->actingAs(userWithRole('editor'));
    [$a, $b, $c] = [demoImage(), demoImage(), demoImage()];

    $component = Livewire::test(PageForm::class)
        ->set('form.title.ar', 'من نحن')
        ->set('form.title.en', 'About us')
        ->set('form.excerpt.ar', 'نبذة')
        ->set('form.hero_type', 'image')
        ->set('form.hero.image', $a->id)
        ->call('addBlock', 'rich_text')
        ->set('blocks.0.data.html.ar', '<p>مرحبا <script>x</script></p>')
        ->call('addBlock', 'values_list')
        ->call('addRepeaterItem', 'blocks.1.data.items', ['title' => ['ar' => 'الجودة', 'en' => 'Quality'], 'text' => ['ar' => '', 'en' => '']])
        ->set('form.att.images', [$c->id, $b->id])
        ->set('form.att_titles.'.$c->id, ['ar' => 'عنوان', 'en' => ''])
        ->call('publish')
        ->assertHasNoErrors();

    $page = Page::query()->latest('id')->first();
    $component->assertRedirect(route('admin.pages.edit', $page->id));

    expect($page->getTranslation('slug', 'ar'))->toBe('من-نحن')
        ->and($page->getTranslation('slug', 'en'))->toBe('about-us')
        ->and($page->isPublished())->toBeTrue()
        ->and($page->blocks[0]['data']['html']['ar'])->toBe('<p>مرحبا </p>')
        ->and($page->blocks[1]['data']['items'][0])->not->toHaveKey('_key')
        ->and($page->firstAsset('hero_image')?->id)->toBe($a->id)
        ->and($page->assetsIn('att_images')->pluck('id')->all())->toBe([$c->id, $b->id]);

    $meta = json_decode((string) $page->assetsIn('att_images')->first()->pivot->meta, true);
    expect($meta['title']['ar'])->toBe('عنوان');
});

test('editing a page slug creates a redirect and block validation applies', function () {
    $this->actingAs(userWithRole('editor'));
    $page = Page::create(['title' => ['ar' => 'صفحة', 'en' => 'Page'], 'status' => 'published']);

    Livewire::test(PageForm::class, ['id' => $page->id])
        ->set('form.slug.en', 'new page')
        ->call('save')
        ->assertHasNoErrors();

    expect($page->fresh()->getTranslation('slug', 'en'))->toBe('new-page')
        ->and(Redirect::where('from_path', '/en/page')->value('to_path'))->toBe('/en/new-page');

    Livewire::test(PageForm::class, ['id' => $page->id])
        ->call('addBlock', 'gallery')
        ->call('save')
        ->assertHasErrors('blocks.0.data.asset_ids');

    Livewire::test(PageForm::class)
        ->set('form.title.ar', 'أخرى')
        ->set('form.slug.ar', 'صفحة')
        ->call('save')
        ->assertHasErrors('form.slug.ar');
});

test('scheduled pages stay hidden until their date', function () {
    $this->actingAs(userWithRole('editor'));

    Livewire::test(PageForm::class)
        ->set('form.title.ar', 'مجدولة')
        ->set('published_at', now()->addDay()->format('Y-m-d H:i'))
        ->call('publish');

    $page = Page::query()->where('title->ar', 'مجدولة')->first();
    expect($page->is_scheduled)->toBeTrue()->and(Page::published()->whereKey($page->id)->exists())->toBeFalse();
});

test('the pages list filters, toggles and runs bulk actions', function () {
    $this->actingAs(userWithRole('editor'));
    $draft = Page::create(['title' => ['ar' => 'مسودة'], 'status' => 'draft']);
    $published = Page::create(['title' => ['ar' => 'منشورة', 'en' => 'Published'], 'status' => 'published']);

    Livewire::test(PageIndex::class)
        ->call('setFilter', 'status', 'draft')
        ->assertSee('مسودة')->assertDontSee('منشورة')
        ->call('clearFilters')
        ->call('setFilter', 'missing', '1')
        ->assertSee('مسودة')->assertDontSee('Published')
        ->call('clearFilters')
        ->call('toggle', $draft->id, 'published')
        ->set('selected', [(string) $published->id])
        ->call('bulk', 'unpublish');

    expect($draft->fresh()->isPublished())->toBeTrue()
        ->and($published->fresh()->isPublished())->toBeFalse();
});

test('sales users cannot edit content', function () {
    $this->actingAs(userWithRole('sales'))->get(route('admin.pages.index'))->assertForbidden();
    $this->actingAs(userWithRole('sales'))->get(route('admin.pages.create'))->assertForbidden();
    $this->actingAs(userWithRole('editor'))->get(route('admin.pages.create'))->assertOk();
});
