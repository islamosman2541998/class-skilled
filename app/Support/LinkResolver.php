<?php

namespace App\Support;

use App\Settings\ContactSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * The link object reused by menus, CTAs and hero buttons:
 * `{type: system_page|page|model|external|anchor|whatsapp|phone, route?, query?,
 *   model_type?, model_id?, url?, anchor?, target_blank?}`.
 *
 * `url()` returns null when the target is missing or not public; callers then
 * hide the element.
 */
class LinkResolver
{
    /** Public routes that can be linked without parameters. */
    public const SYSTEM_ROUTES = [
        'home', 'services.index', 'projects.index', 'posts.index', 'careers.index',
        'quote', 'contact', 'faq', 'search',
    ];

    public const TYPES = ['system_page', 'page', 'model', 'external', 'anchor', 'whatsapp', 'phone'];

    /** @var array<string, Model|null> */
    private static array $models = [];

    /**
     * @param  array<string, mixed>|null  $link
     */
    public static function url(?array $link, ?string $locale = null): ?string
    {
        if ($link === null || ! in_array($link['type'] ?? null, self::TYPES, true)) {
            return null;
        }

        $locale ??= app()->getLocale();

        return match ($link['type']) {
            'system_page' => self::systemUrl($link, $locale),
            'page', 'model' => self::modelUrl($link, $locale),
            'external' => self::externalUrl((string) ($link['url'] ?? '')),
            'anchor' => filled($link['anchor'] ?? null) ? '#'.ltrim((string) $link['anchor'], '#') : null,
            'whatsapp' => self::whatsappUrl($locale),
            'phone' => self::phoneUrl(),
        };
    }

    /**
     * A resolved button `{label, url, target_blank}` from `{label{ar,en}, link}` or null.
     *
     * @return array{label: string, url: string, target_blank: bool}|null
     */
    public static function button(mixed $value, ?string $locale = null): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $link = is_array($value['link'] ?? null) ? $value['link'] : null;
        $url = self::url($link, $locale);
        $label = Translate::pick($value['label'] ?? [], $locale);

        if ($url === null || $label === '') {
            return null;
        }

        return ['label' => $label, 'url' => $url, 'target_blank' => (bool) ($link['target_blank'] ?? false)];
    }

    /**
     * Keep only the keys that matter for the link type.
     *
     * @return array<string, mixed>|null
     */
    public static function normalize(mixed $link): ?array
    {
        if (! is_array($link) || ! in_array($link['type'] ?? null, self::TYPES, true)) {
            return null;
        }

        $clean = ['type' => $link['type'], 'target_blank' => (bool) ($link['target_blank'] ?? false)];

        switch ($link['type']) {
            case 'system_page':
                if (! in_array($link['route'] ?? null, self::SYSTEM_ROUTES, true)) {
                    return null;
                }
                $clean['route'] = $link['route'];
                if (! empty($link['query']) && is_array($link['query'])) {
                    $clean['query'] = array_map('strval', $link['query']);
                }
                break;
            case 'page':
            case 'model':
                if (empty($link['model_type']) || empty($link['model_id'])) {
                    return null;
                }
                $clean['model_type'] = (string) $link['model_type'];
                $clean['model_id'] = (int) $link['model_id'];
                break;
            case 'external':
                if (self::externalUrl((string) ($link['url'] ?? '')) === null) {
                    return null;
                }
                $clean['url'] = trim((string) $link['url']);
                break;
            case 'anchor':
                $clean['anchor'] = ltrim(trim((string) ($link['anchor'] ?? '')), '#');
                break;
        }

        return $clean;
    }

    public static function forgetMemo(): void
    {
        self::$models = [];
    }

    /**
     * @param  array<string, mixed>  $link
     */
    private static function systemUrl(array $link, string $locale): ?string
    {
        $route = $link['route'] ?? null;

        if (! in_array($route, self::SYSTEM_ROUTES, true)) {
            return null;
        }

        $url = LocalizedRoute::url($route, $locale);
        $query = is_array($link['query'] ?? null) ? array_filter($link['query'], 'filled') : [];

        return $query === [] ? $url : $url.'?'.http_build_query($query);
    }

    /**
     * @param  array<string, mixed>  $link
     */
    private static function modelUrl(array $link, string $locale): ?string
    {
        $model = self::model((string) ($link['model_type'] ?? ''), (int) ($link['model_id'] ?? 0));

        return $model !== null && self::isPublic($model) && method_exists($model, 'localizedUrl')
            ? $model->localizedUrl($locale)
            : null;
    }

    public static function model(string $type, int $id): ?Model
    {
        $key = $type.':'.$id;

        if (array_key_exists($key, self::$models)) {
            return self::$models[$key];
        }

        $class = Relation::getMorphedModel($type);

        if ($class === null || ! is_subclass_of($class, Model::class) || $id <= 0) {
            return self::$models[$key] = null;
        }

        return self::$models[$key] = $class::query()->find($id);
    }

    public static function isPublic(Model $model): bool
    {
        if (method_exists($model, 'isPublished') && ! $model->isPublished()) {
            return false;
        }

        $attributes = $model->getAttributes();

        if (array_key_exists('is_active', $attributes) && ! $model->getAttribute('is_active')) {
            return false;
        }

        return true;
    }

    private static function externalUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~^(https?://|mailto:|tel:)~i', $url)) {
            return $url;
        }

        return null;
    }

    public static function whatsappUrl(?string $locale = null, ?string $message = null): ?string
    {
        $settings = app(ContactSettings::class);
        $number = preg_replace('/\D+/', '', (string) $settings->whatsapp_number);

        if ($number === '' || $number === null) {
            return null;
        }

        $text = $message ?? Translate::pick($settings->whatsapp_message, $locale);

        return 'https://wa.me/'.$number.($text !== '' ? '?text='.rawurlencode($text) : '');
    }

    public static function phoneUrl(?string $phone = null): ?string
    {
        $phone ??= app(ContactSettings::class)->phones[0] ?? null;
        $digits = preg_replace('/[^\d+]/', '', (string) $phone);

        return $digits ? 'tel:'.$digits : null;
    }
}
