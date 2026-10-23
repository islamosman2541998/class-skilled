<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesPublicModels;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\SchemaBuilder;
use App\Services\SeoManager;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    use ResolvesPublicModels;

    public function index(SeoManager $seo): View
    {
        $seo->title(__('posts.title'))->description(__('posts.lead'));

        return view('site.pages.posts.index', ['category' => null]);
    }

    public function category(string $slug): View
    {
        /** @var PostCategory $category */
        $category = $this->findPublic(PostCategory::query()->with('seo'), $slug);

        return view('site.pages.posts.index', ['category' => $category]);
    }

    public function show(SeoManager $seo, SchemaBuilder $schema, string $slug): View
    {
        /** @var Post $post */
        $post = $this->findPublic(Post::query()->with(['category', 'assets.media', 'seo']), $slug);
        $seo->type('article')->schema($schema->article($post));

        return view('site.pages.posts.show', [
            'post' => $post,
            'preview' => $this->preview,
            'related' => Post::query()->published()->translated()
                ->whereKeyNot($post->id)
                ->when($post->post_category_id, fn ($q) => $q->orderByRaw('post_category_id = ? desc', [$post->post_category_id]))
                ->with(['category', 'assets.media'])
                ->latest('published_at')->limit(6)->get(),
        ]);
    }
}
