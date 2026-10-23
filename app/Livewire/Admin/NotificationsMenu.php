<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Services\AdminNavigation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Top bar bell: latest unread visitor requests the user may see.
 */
class NotificationsMenu extends Component
{
    public bool $loaded = false;

    public function load(): void
    {
        $this->loaded = true;
    }

    #[Computed]
    public function count(): int
    {
        $user = auth()->user();
        $counts = app(AdminNavigation::class)->counts();

        return ($user?->can('view quote_requests') ? $counts['quotes'] : 0)
            + ($user?->can('view contact_messages') ? $counts['messages'] : 0)
            + ($user?->can('view job_applications') ? $counts['applications'] : 0);
    }

    /**
     * @return Collection<int, array{label: string, title: string, time: string, url: string|null}>
     */
    #[Computed]
    public function items(): Collection
    {
        if (! $this->loaded) {
            return collect();
        }

        $user = auth()->user();
        $items = collect();

        $sources = [
            [QuoteRequest::class, 'view quote_requests', 'quotes', 'admin.quotes.show', fn (QuoteRequest $r) => $r->number.' — '.$r->name],
            [ContactMessage::class, 'view contact_messages', 'messages', 'admin.messages.show', fn (ContactMessage $r) => $r->name],
            [JobApplication::class, 'view job_applications', 'applications', 'admin.applications.show', fn (JobApplication $r) => $r->name],
        ];

        foreach ($sources as [$class, $permission, $label, $route, $title]) {
            if (! $user?->can($permission)) {
                continue;
            }

            foreach ($class::query()->whereNull('read_at')->latest()->limit(5)->get() as $row) {
                $items->push([
                    'label' => __('admin.nav.'.$label),
                    'title' => $title($row),
                    'time' => $row->created_at?->diffForHumans() ?? '',
                    'sort' => $row->created_at ? $row->created_at->getTimestamp() : 0,
                    'url' => Route::has($route) ? route($route, $row) : null,
                ]);
            }
        }

        return $items->sortByDesc('sort')->take(8)->values();
    }

    public function render(): View
    {
        return view('livewire.admin.notifications-menu');
    }
}
