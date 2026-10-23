<?php

namespace App\Http\Controllers\Site;

use App\Enums\SiteEventType;
use App\Http\Controllers\Controller;
use App\Models\SiteEvent;
use App\Support\VisitorSource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Beacon endpoint (`navigator.sendBeacon('/t')`): page views are counted per
 * day/path/locale, contact clicks are stored as site events. Client-side, so
 * it keeps working with the full-page response cache. Bots are ignored.
 */
class TrackController extends Controller
{
    public const CLICK_TYPES = ['whatsapp_click', 'call_click', 'email_click'];

    public function __invoke(Request $request): Response
    {
        $userAgent = (string) $request->userAgent();

        if ($userAgent === '' || preg_match('/bot|crawl|spider|slurp|preview|headless|lighthouse|monitor|curl|wget|python|http-client/i', $userAgent)) {
            return response()->noContent();
        }

        $payload = json_decode((string) $request->getContent(), true);
        $payload = is_array($payload) ? $payload : $request->all();

        $type = (string) ($payload['type'] ?? '');
        $locale = in_array($payload['locale'] ?? null, ['ar', 'en'], true) ? $payload['locale'] : 'ar';
        $path = $this->path((string) ($payload['path'] ?? '/'));

        if ($type === 'page_view') {
            DB::table('page_views_daily')->upsert(
                [['date' => today()->toDateString(), 'path' => $path, 'locale' => $locale, 'views' => 1]],
                ['date', 'path', 'locale'],
                ['views' => DB::raw('views + 1')],
            );
        } elseif (in_array($type, self::CLICK_TYPES, true)) {
            SiteEvent::query()->create([
                'type' => SiteEventType::from($type),
                'path' => $path,
                'locale' => $locale,
                'device' => VisitorSource::device($userAgent),
                'utm' => VisitorSource::forRequest($request)['utm'] ?? null,
            ]);
        }

        return response()->noContent();
    }

    /**
     * Decoded path only (no query, no host), trimmed to the column size.
     */
    private function path(string $value): string
    {
        $path = rawurldecode((string) parse_url($value, PHP_URL_PATH));
        $path = '/'.trim($path, '/');

        return mb_substr($path, 0, 250);
    }
}
