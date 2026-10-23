<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesPublicModels;
use App\Models\JobPost;
use App\Services\SchemaBuilder;
use App\Services\SeoManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class CareerController extends Controller
{
    use ResolvesPublicModels;

    public function index(SeoManager $seo): View
    {
        $seo->title(__('careers.title'))->description(__('careers.lead'));

        return view('site.pages.careers.index', [
            'jobs' => JobPost::query()->open()->translated()->ordered()->latest('published_at')->get(),
        ]);
    }

    public function show(SeoManager $seo, string $slug): View|Response
    {
        $job = JobPost::query()->with('seo')->where('slug->'.app()->getLocale(), $slug)->first();

        // A job that was published but has closed gets a friendly "no longer available" page.
        if ($job !== null && $job->isPublished() && ! $job->isOpen()) {
            $seo->title(__('careers.closed_title'))->noindex(true);

            return response()->view('site.pages.careers.closed', ['job' => $job], 410);
        }

        /** @var JobPost $job */
        $job = $this->findPublic(JobPost::query()->with('seo'), $slug);
        $seo->schema(app(SchemaBuilder::class)->jobPosting($job));

        return view('site.pages.careers.show', ['job' => $job, 'preview' => $this->preview]);
    }
}
