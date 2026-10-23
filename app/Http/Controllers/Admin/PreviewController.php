<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\LocalizedRoute;
use App\Support\Preview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;

/**
 * Opens a signed preview link: remembers the item in the session and
 * redirects to its public URL in the requested locale.
 */
class PreviewController extends Controller
{
    public function __invoke(string $type, int $id, string $locale): RedirectResponse
    {
        $class = Relation::getMorphedModel($type);
        abort_unless($class !== null && is_subclass_of($class, Model::class), 404);

        $model = $class::query()->findOrFail($id);
        abort_unless(method_exists($model, 'localizedUrl'), 404);

        Preview::allow($type, $id);

        $url = $model->localizedUrl($locale) ?? $model->localizedUrl('ar') ?? LocalizedRoute::url('home', $locale);

        return redirect()->to($url);
    }
}
