<?php

namespace App\Services;

use App\Enums\RequestType;
use App\Models\Asset;
use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\RequestStatus;
use App\Models\Service;
use App\Models\SiteEvent;
use App\Settings\CompanySettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Numbers for the dashboard home, per period (7 / 30 / 90 days) compared with
 * the previous period of the same length. Each widget is cached for 5 minutes.
 */
class DashboardStats
{
    public const PERIODS = [7, 30, 90];

    public const TTL = 300;

    public static function period(int $days): int
    {
        return in_array($days, self::PERIODS, true) ? $days : 30;
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: Carbon} [previous start, current start, now]
     */
    public function range(int $days): array
    {
        $start = today()->subDays($days - 1);

        return [$start->copy()->subDays($days), $start, now()];
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function remember(string $key, int $days, callable $callback): mixed
    {
        return Cache::remember('dashboard.'.$key.'.'.$days.'.'.app()->getLocale(), self::TTL, $callback);
    }

    /**
     * @return array<string, array{value: int|string, previous: int|float, current: int|float}>
     */
    public function stats(int $days): array
    {
        return $this->remember('stats', $days, function () use ($days) {
            [$prev, $start, $now] = $this->range($days);
            $wonId = RequestStatus::query()->where('request_type', RequestType::Quote->value)->where('key', 'won')->value('id');

            $count = fn (Carbon $from, Carbon $to) => QuoteRequest::query()->whereBetween('created_at', [$from, $to])->count();
            $won = fn (Carbon $from, Carbon $to) => $wonId ? QuoteRequest::query()->whereBetween('created_at', [$from, $to])->where('status_id', $wonId)->count() : 0;
            $clicks = fn (Carbon $from, Carbon $to) => SiteEvent::query()->whereIn('type', ['whatsapp_click', 'call_click'])->whereBetween('created_at', [$from, $to])->count();
            $visits = fn (Carbon $from, Carbon $to) => (int) DB::table('page_views_daily')->whereBetween('date', [$from->toDateString(), $to->toDateString()])->sum('views');

            $quotes = [$count($start, $now), $count($prev, $start->copy()->subSecond())];
            $wins = [$won($start, $now), $won($prev, $start->copy()->subSecond())];
            $rate = fn (int $w, int $q) => $q > 0 ? round($w / $q * 100, 1) : 0.0;

            return [
                'quotes' => ['value' => $quotes[0], 'current' => $quotes[0], 'previous' => $quotes[1]],
                'won' => ['value' => $wins[0].' · '.$rate($wins[0], $quotes[0]).'%', 'current' => $rate($wins[0], $quotes[0]), 'previous' => $rate($wins[1], $quotes[1])],
                'clicks' => ['value' => $clicks($start, $now), 'current' => $clicks($start, $now), 'previous' => $clicks($prev, $start->copy()->subSecond())],
                'visits' => ['value' => $visits($start, $now), 'current' => $visits($start, $now), 'previous' => $visits($prev, $start->copy()->subDay())],
            ];
        });
    }

    /**
     * Delta label and trend for a stat ("+12%", up/down/flat).
     *
     * @return array{0: string, 1: string}
     */
    public static function delta(float $current, float $previous): array
    {
        if ($previous == 0.0) {
            return [$current > 0 ? '+100%' : '0%', $current > 0 ? 'up' : 'flat'];
        }

        $change = round(($current - $previous) / $previous * 100);

        return [($change > 0 ? '+' : '').$change.'%', $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat')];
    }

    /**
     * Requests and visits per day for the chart.
     *
     * @return array{labels: list<string>, requests: list<int>, visits: list<int>, current: list<bool>}
     */
    public function daily(int $days): array
    {
        return $this->remember('daily', $days, function () use ($days) {
            [, $start] = $this->range($days);
            $requests = collect();

            foreach ([QuoteRequest::class, ContactMessage::class, JobApplication::class] as $model) {
                $model::query()->toBase()->where('created_at', '>=', $start)
                    ->selectRaw('DATE(created_at) as day, count(*) as total')->groupBy('day')->get()
                    ->each(fn ($row) => $requests[$row->day] = ($requests[$row->day] ?? 0) + (int) $row->total);
            }

            $visits = DB::table('page_views_daily')->where('date', '>=', $start->toDateString())
                ->selectRaw('date, sum(views) as total')->groupBy('date')->pluck('total', 'date');

            $labels = $requestSeries = $visitSeries = $current = [];
            $weekStart = today()->startOfWeek(Carbon::SATURDAY);

            for ($day = $start->copy(); $day->lte(today()); $day->addDay()) {
                $key = $day->toDateString();
                $labels[] = $day->locale(app()->getLocale())->translatedFormat($days > 31 ? 'j M' : 'D j');
                $requestSeries[] = (int) ($requests[$key] ?? 0);
                $visitSeries[] = (int) ($visits[$key] ?? 0);
                $current[] = $day->gte($weekStart);
            }

            return ['labels' => $labels, 'requests' => $requestSeries, 'visits' => $visitSeries, 'current' => $current];
        });
    }

    /**
     * Quote requests per service in the period (most requested first).
     *
     * @return list<array{label: string, total: int}>
     */
    public function byService(int $days): array
    {
        return $this->remember('by-service', $days, function () use ($days) {
            [, $start] = $this->range($days);
            $rows = QuoteRequest::query()->toBase()->where('created_at', '>=', $start)
                ->selectRaw('service_id, count(*) as total')->groupBy('service_id')->orderByDesc('total')->limit(8)->get();
            $names = Service::query()->whereIn('id', $rows->pluck('service_id')->filter())->get()->keyBy('id');

            return $rows->map(fn ($row) => [
                'label' => $row->service_id && $names->has($row->service_id) ? (string) $names[$row->service_id]->getTranslation('title', app()->getLocale()) : __('admin.dashboard.no_service'),
                'total' => (int) $row->total,
            ])->values()->all();
        });
    }

    /**
     * @return Collection<int, QuoteRequest>
     */
    public function latestQuotes(): Collection
    {
        return QuoteRequest::query()->with(['status', 'service'])->latest()->limit(6)->get();
    }

    /**
     * Items that need someone's attention (counts + links).
     *
     * @return list<array{label: string, count: int, url: string|null, tone: string}>
     */
    public function attention(): array
    {
        return $this->remember('attention', 0, function () {
            $defaultQuote = RequestStatus::defaultFor(RequestType::Quote)?->id;
            $missingEnglish = collect([Service::class, Project::class, Post::class, Page::class])
                ->sum(fn (string $model) => $model::query()->where(fn ($q) => $q->whereNull('title->en')->orWhere('title->en', ''))->count());

            $items = [
                ['label' => __('admin.dashboard.unassigned_quotes'), 'count' => QuoteRequest::query()->whereNull('assigned_to')->when($defaultQuote, fn ($q) => $q->where('status_id', $defaultQuote))->count(), 'url' => route('admin.quotes.index', ['f' => ['assignee' => 'none']]), 'tone' => 'amber'],
                ['label' => __('admin.dashboard.unread_messages'), 'count' => ContactMessage::query()->unread()->count(), 'url' => route('admin.messages.index', ['f' => ['unread' => '1']]), 'tone' => 'sky'],
                ['label' => __('admin.dashboard.unread_applications'), 'count' => JobApplication::query()->unread()->count(), 'url' => route('admin.applications.index', ['f' => ['unread' => '1']]), 'tone' => 'sky'],
                ['label' => __('admin.dashboard.missing_alt_ar'), 'count' => Asset::query()->missingAlt('ar')->count(), 'url' => route('admin.media.index'), 'tone' => 'violet'],
                ['label' => __('admin.dashboard.missing_alt_en'), 'count' => Asset::query()->missingAlt('en')->count(), 'url' => route('admin.media.index'), 'tone' => 'violet'],
                ['label' => __('admin.dashboard.missing_english'), 'count' => (int) $missingEnglish, 'url' => route('admin.seo.health'), 'tone' => 'gray'],
                ['label' => __('admin.dashboard.expiring_certificates'), 'count' => Certificate::query()->whereNotNull('expires_at')->whereBetween('expires_at', [today(), today()->addDays(90)])->count(), 'url' => route('admin.certificates.index'), 'tone' => 'red'],
            ];

            return array_values(array_filter($items, fn (array $item) => $item['count'] > 0));
        });
    }

    /**
     * Trade licence and classification expiry dates (from Settings → Company).
     *
     * @return list<array{label: string, date: string, days: int}>
     */
    public function licences(): array
    {
        $company = app(CompanySettings::class);
        $rows = [];

        foreach (['licence_expiry' => 'admin.dashboard.licence_expiry', 'classification_expiry' => 'admin.dashboard.classification_expiry'] as $field => $label) {
            if (filled($company->{$field})) {
                $date = Carbon::parse((string) $company->{$field});
                $rows[] = ['label' => __($label), 'date' => $date->toDateString(), 'days' => (int) today()->diffInDays($date, false)];
            }
        }

        return $rows;
    }

    /**
     * Most viewed pages in the period.
     *
     * @return list<array{path: string, views: int}>
     */
    public function topPages(int $days): array
    {
        return $this->remember('top-pages', $days, function () use ($days) {
            [, $start] = $this->range($days);

            return DB::table('page_views_daily')->where('date', '>=', $start->toDateString())
                ->selectRaw('path, sum(views) as views')->groupBy('path')->orderByDesc('views')->limit(8)->get()
                ->map(fn ($row) => ['path' => (string) $row->path, 'views' => (int) $row->views])->all();
        });
    }
}
