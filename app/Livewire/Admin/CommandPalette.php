<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Services\AdminNavigation;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Ctrl/Cmd+K global search across requests and content (permission-aware),
 * plus quick navigation to dashboard screens.
 */
class CommandPalette extends Component
{
    public string $query = '';

    /**
     * @return array<string, list<array{title: string, meta: string, url: string}>>
     */
    #[Computed]
    public function results(): array
    {
        $term = trim($this->query);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $user = auth()->user();
        $groups = [];

        $navigation = [];

        foreach (app(AdminNavigation::class)->tree($user) as $entry) {
            foreach ($entry['items'] ?? [$entry] as $item) {
                if (mb_stripos($item['label'], $term) !== false) {
                    $navigation[] = ['title' => $item['label'], 'meta' => $entry['label'], 'url' => $item['url']];
                }
            }
        }

        if ($navigation !== []) {
            $groups['navigation'] = array_slice($navigation, 0, 5);
        }

        $requests = [
            'quotes' => [QuoteRequest::class, 'view quote_requests', 'admin.quotes.show', ['number', 'name', 'phone', 'email']],
            'messages' => [ContactMessage::class, 'view contact_messages', 'admin.messages.show', ['name', 'phone', 'email', 'subject']],
            'applications' => [JobApplication::class, 'view job_applications', 'admin.applications.show', ['name', 'phone', 'email']],
        ];

        foreach ($requests as $group => [$class, $permission, $route, $fields]) {
            if (! $user->can($permission) || ! Route::has($route)) {
                continue;
            }

            $rows = Search::apply($class::query(), $term, $fields)->latest()->limit(5)->get();
            $groups[$group] = $rows->map(fn (Model $row) => [
                'title' => trim(($row->getAttribute('number') ? $row->getAttribute('number').' — ' : '').$row->getAttribute('name')),
                'meta' => (string) $row->getAttribute('phone'),
                'url' => route($route, $row),
            ])->all();
        }

        $content = [
            'projects' => [Project::class, 'view projects', 'admin.projects.edit'],
            'services' => [Service::class, 'view services', 'admin.services.edit'],
            'pages' => [Page::class, 'view pages', 'admin.pages.edit'],
            'posts' => [Post::class, 'view posts', 'admin.posts.edit'],
        ];

        foreach ($content as $group => [$class, $permission, $route]) {
            if (! $user->can($permission) || ! Route::has($route)) {
                continue;
            }

            $rows = Search::apply($class::query(), $term, [], ['title'])->latest('updated_at')->limit(5)->get();
            $groups[$group] = $rows->map(fn (Model $row) => [
                'title' => (string) $row->getTranslation('title', app()->getLocale(), false) ?: (string) $row->getTranslation('title', 'ar', false),
                'meta' => '',
                'url' => route($route, $row),
            ])->all();
        }

        return array_filter($groups);
    }

    public function render(): View
    {
        return view('livewire.admin.command-palette');
    }
}
