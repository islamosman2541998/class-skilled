<?php

namespace App\Services;

use App\Enums\AssetKind;
use App\Enums\EmbedProvider;
use App\Models\Asset;
use App\Settings\MediaSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\Conversions\FileManipulator;

/**
 * Single entry point for adding files to the media library.
 * Images are downscaled/re-encoded with GD (EXIF stripped, auto-oriented)
 * before medialibrary generates the WebP conversions synchronously.
 */
class AssetUploader
{
    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public const VIDEO_MIMES = ['video/mp4', 'video/webm'];

    public const DOCUMENT_MIMES = ['application/pdf'];

    public function __construct(
        private readonly ImageManager $images,
        private readonly MediaSettings $settings,
    ) {}

    /**
     * Detect the kind from the (sniffed) MIME type and store the file.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function upload(UploadedFile|string $file, array $attributes = []): Asset
    {
        $mime = $this->source($file)['mime'];

        return match (true) {
            in_array($mime, self::IMAGE_MIMES, true) => $this->uploadImage($file, $attributes),
            in_array($mime, self::VIDEO_MIMES, true) => $this->uploadVideo($file, $attributes),
            in_array($mime, self::DOCUMENT_MIMES, true) => $this->uploadDocument($file, $attributes),
            default => throw ValidationException::withMessages(['file' => __('admin.media.unsupported_type')]),
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function uploadImage(UploadedFile|string $file, array $attributes = []): Asset
    {
        $source = $this->source($file);
        $this->assertMime($source['mime'], self::IMAGE_MIMES);
        $this->assertSize($source['size'], $this->settings->max_image_mb);

        [$path, $width, $height, $size] = $this->processImage($source['path'], $source['mime']);

        return DB::transaction(function () use ($attributes, $source, $path, $width, $height, $size) {
            $asset = Asset::create(array_merge([
                'kind' => AssetKind::Image,
                'original_name' => $source['name'],
                'mime' => $source['mime'],
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'uploaded_by' => auth()->id(),
            ], $attributes));

            $this->attachFile($asset, $path, $source['name'], false);

            return $asset;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes  may include duration and poster_asset_id (provided by the browser)
     */
    public function uploadVideo(UploadedFile|string $file, array $attributes = []): Asset
    {
        $source = $this->source($file);
        $this->assertMime($source['mime'], self::VIDEO_MIMES);
        $this->assertSize($source['size'], $this->settings->max_video_mb);

        return DB::transaction(function () use ($file, $attributes, $source) {
            $asset = Asset::create(array_merge([
                'kind' => AssetKind::Video,
                'original_name' => $source['name'],
                'mime' => $source['mime'],
                'size' => $source['size'],
                'uploaded_by' => auth()->id(),
            ], $attributes));

            $this->attachFile($asset, $source['path'], $source['name'], is_string($file));

            return $asset;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function uploadDocument(UploadedFile|string $file, array $attributes = []): Asset
    {
        $source = $this->source($file);
        $this->assertMime($source['mime'], self::DOCUMENT_MIMES);
        $this->assertSize($source['size'], max($this->settings->max_image_mb, 20));

        return DB::transaction(function () use ($file, $attributes, $source) {
            $asset = Asset::create(array_merge([
                'kind' => AssetKind::Document,
                'original_name' => $source['name'],
                'mime' => $source['mime'],
                'size' => $source['size'],
                'uploaded_by' => auth()->id(),
            ], $attributes));

            $this->attachFile($asset, $source['path'], $source['name'], is_string($file));

            return $asset;
        });
    }

    /**
     * YouTube / Vimeo embed. No outbound request is made: the poster is an image asset.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createEmbed(string $url, ?int $posterAssetId, array $attributes = []): Asset
    {
        $parsed = static::parseEmbedUrl($url);

        if ($parsed === null) {
            throw ValidationException::withMessages(['url' => __('admin.media.invalid_embed')]);
        }

        if ($posterAssetId === null) {
            throw ValidationException::withMessages(['poster' => __('admin.media.poster_required')]);
        }

        return Asset::create(array_merge([
            'kind' => AssetKind::Embed,
            'external_url' => $url,
            'provider' => $parsed['provider'],
            'external_id' => $parsed['id'],
            'poster_asset_id' => $posterAssetId,
            'uploaded_by' => auth()->id(),
        ], $attributes));
    }

    /**
     * Swap the file of an existing asset (same id, same usages); conversions are regenerated.
     */
    public function replace(Asset $asset, UploadedFile|string $file): Asset
    {
        $source = $this->source($file);
        $allowed = match ($asset->kind) {
            AssetKind::Image => self::IMAGE_MIMES,
            AssetKind::Video => self::VIDEO_MIMES,
            AssetKind::Document => self::DOCUMENT_MIMES,
            AssetKind::Embed => [],
        };
        $this->assertMime($source['mime'], $allowed);

        $path = $source['path'];
        $preserve = is_string($file);
        $width = $height = null;
        $size = $source['size'];

        if ($asset->kind === AssetKind::Image) {
            $this->assertSize($size, $this->settings->max_image_mb);
            [$path, $width, $height, $size] = $this->processImage($source['path'], $source['mime']);
            $preserve = false;
        } else {
            $this->assertSize($size, $asset->kind === AssetKind::Video ? $this->settings->max_video_mb : 20);
        }

        $asset->update([
            'original_name' => $source['name'],
            'mime' => $source['mime'],
            'size' => $size,
            'width' => $width,
            'height' => $height,
        ]);

        $this->attachFile($asset, $path, $source['name'], $preserve);

        return $asset->refresh();
    }

    /**
     * Rebuild the WebP conversions of an image asset (e.g. after a focal change).
     */
    public function regenerateConversions(Asset $asset): void
    {
        $media = $asset->file();

        if ($media === null || ! $asset->isImage()) {
            return;
        }

        app(FileManipulator::class)
            ->createDerivedFiles($media);
    }

    /**
     * @return array{provider: EmbedProvider, id: string}|null
     */
    public static function parseEmbedUrl(string $url): ?array
    {
        $url = trim($url);

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $m)) {
            return ['provider' => EmbedProvider::Youtube, 'id' => $m[1]];
        }

        if (preg_match('~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d{6,12})~i', $url, $m)) {
            return ['provider' => EmbedProvider::Vimeo, 'id' => $m[1]];
        }

        return null;
    }

    /**
     * @return array{path: string, name: string, mime: string, size: int}
     */
    private function source(UploadedFile|string $file): array
    {
        if ($file instanceof UploadedFile) {
            return [
                'path' => (string) $file->getRealPath(),
                'name' => $file->getClientOriginalName(),
                'mime' => (string) $file->getMimeType(),
                'size' => (int) $file->getSize(),
            ];
        }

        if (! is_file($file)) {
            throw ValidationException::withMessages(['file' => __('admin.media.unsupported_type')]);
        }

        return [
            'path' => $file,
            'name' => basename($file),
            'mime' => (string) mime_content_type($file),
            'size' => (int) filesize($file),
        ];
    }

    /**
     * @param  list<string>  $allowed
     */
    private function assertMime(string $mime, array $allowed): void
    {
        if (! in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages(['file' => __('admin.media.unsupported_type')]);
        }
    }

    private function assertSize(int $bytes, int $maxMb): void
    {
        if ($bytes > $maxMb * 1024 * 1024) {
            throw ValidationException::withMessages(['file' => __('admin.media.too_large', ['max' => $maxMb])]);
        }
    }

    /**
     * Auto-orient, downscale to the configured max dimension and re-encode (drops EXIF).
     *
     * @return array{0: string, 1: int, 2: int, 3: int}
     */
    private function processImage(string $path, string $mime): array
    {
        $max = max(320, $this->settings->max_image_dimension);
        $image = $this->images->read($path);
        $image->scaleDown($max, $max);

        $encoded = match ($mime) {
            'image/png' => $image->toPng(),
            'image/webp' => $image->toWebp(90),
            default => $image->toJpeg(88),
        };

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $target = tempnam(sys_get_temp_dir(), 'csimg').'.'.$extension;
        $encoded->save($target);

        return [$target, $image->width(), $image->height(), (int) filesize($target)];
    }

    private function attachFile(Asset $asset, string $path, string $originalName, bool $preserveOriginal): void
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: pathinfo($originalName, PATHINFO_EXTENSION));
        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME), '-', 'en') ?: 'file';

        $adder = $asset->addMedia($path)
            ->usingName(pathinfo($originalName, PATHINFO_FILENAME))
            ->usingFileName(Str::limit($base, 80, '').'.'.$extension);

        if ($preserveOriginal) {
            $adder->preservingOriginal();
        }

        $adder->toMediaCollection('file');
    }
}
