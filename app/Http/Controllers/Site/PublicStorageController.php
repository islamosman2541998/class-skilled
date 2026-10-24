<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Fallback for hosts where `php artisan storage:link` is not possible: serves
 * files of the public disk at /storage/… (registered only when public/storage
 * does not exist). Only known media types are served; never scripts.
 */
class PublicStorageController extends Controller
{
    public const TYPES = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif',
        'svg' => 'image/svg+xml', 'ico' => 'image/x-icon', 'pdf' => 'application/pdf', 'mp4' => 'video/mp4',
        'webm' => 'video/webm', 'txt' => 'text/plain',
    ];

    public function __invoke(string $path): BinaryFileResponse
    {
        $disk = Storage::disk('public');
        $path = str_replace('\\', '/', $path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        abort_if(str_contains($path, '..') || ! isset(self::TYPES[$extension]) || ! $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => self::TYPES[$extension],
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
