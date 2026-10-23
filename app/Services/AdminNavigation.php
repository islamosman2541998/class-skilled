<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Sidebar tree filtered by existing routes and permissions, plus the unread
 * request counts shown as badges (cached 60 s).
 */
class AdminNavigation
{
    public const BADGES_CACHE_KEY = 'admin.badges';

    /**
     * @return list<array<string, mixed>>
     */
    public function tree(User $user): array
    {
        $tree = [];
        $counts = $this->counts();

        foreach ((array) config('admin.navigation') as $entry) {
            if (isset($entry['group'])) {
                $items = array_values(array_filter(
                    array_map(fn (array $item) => $this->item($item, $user, $counts), $entry['items']),
                ));

                if ($items !== []) {
                    $active = collect($items)->contains('active', true);
                    $tree[] = [
                        'group' => $entry['group'],
                        'label' => __('admin.'.$entry['label']),
                        'open' => $active || ($entry['open'] ?? true),
                        'items' => $items,
                    ];
                }

                continue;
            }

            if ($item = $this->item($entry, $user, $counts)) {
                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, int>  $counts
     * @return array<string, mixed>|null
     */
    private function item(array $item, User $user, array $counts): ?array
    {
        if (! Route::has($item['route']) || (isset($item['can']) && ! $user->can($item['can']))) {
            return null;
        }

        return [
            'label' => __('admin.'.$item['label']),
            'url' => route($item['route']),
            'active' => request()->routeIs($item['active'] ?? $item['route']),
            'badge' => isset($item['badge']) ? ($counts[$item['badge']] ?? 0) : 0,
        ];
    }

    /**
     * @return array{quotes: int, messages: int, applications: int}
     */
    public function counts(): array
    {
        return Cache::remember(self::BADGES_CACHE_KEY, 60, fn () => [
            'quotes' => QuoteRequest::query()->whereNull('read_at')->count(),
            'messages' => ContactMessage::query()->whereNull('read_at')->count(),
            'applications' => JobApplication::query()->whereNull('read_at')->count(),
        ]);
    }

    public static function forgetCounts(): void
    {
        Cache::forget(self::BADGES_CACHE_KEY);
    }
}
