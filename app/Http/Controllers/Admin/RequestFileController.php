<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Signed, permission-checked access to visitor uploads on the private disk
 * (`?inline=1` for the preview modal, otherwise a download).
 */
class RequestFileController extends Controller
{
    public function __invoke(Request $request, Media $media): StreamedResponse
    {
        $owner = $media->model;

        abort_unless($owner instanceof QuoteRequest || $owner instanceof JobApplication || $owner instanceof ContactMessage, 404);
        Gate::authorize('view', $owner);

        $disk = $media->disk;
        $path = $media->getPathRelativeToRoot();
        $name = $media->name.'.'.pathinfo($media->file_name, PATHINFO_EXTENSION);
        $inline = $request->boolean('inline') && (str_starts_with((string) $media->mime_type, 'image/') || $media->mime_type === 'application/pdf');

        $storage = Storage::disk($disk);
        abort_unless($storage->exists($path), 404);

        return $inline
            ? $storage->response($path, $name, ['Content-Type' => (string) $media->mime_type, 'X-Content-Type-Options' => 'nosniff'], 'inline')
            : $storage->download($path, $name, ['X-Content-Type-Options' => 'nosniff']);
    }
}
