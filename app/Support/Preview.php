<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/**
 * Draft previews: the dashboard hands out a short-lived signed URL; opening it
 * stores "type:id" in the session and redirects to the public page, which then
 * shows the item even when unpublished (with a preview ribbon, noindex).
 */
class Preview
{
    public const SESSION_KEY = 'preview_items';

    public static function url(Model $model, ?string $locale = null): string
    {
        return URL::temporarySignedRoute('admin.preview', now()->addMinutes(30), [
            'type' => $model->getMorphClass(),
            'id' => $model->getKey(),
            'locale' => $locale ?? 'ar',
        ]);
    }

    public static function allow(string $type, int $id): void
    {
        $items = (array) session(self::SESSION_KEY, []);
        $items[$type.':'.$id] = time() + 1800;
        session([self::SESSION_KEY => $items]);
    }

    public static function allows(Model $model): bool
    {
        $expires = ((array) session(self::SESSION_KEY, []))[$model->getMorphClass().':'.$model->getKey()] ?? 0;

        return $expires > time();
    }
}
