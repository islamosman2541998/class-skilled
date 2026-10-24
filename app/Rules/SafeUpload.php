<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Visitor upload check by content, not only by extension: the MIME type
 * sniffed by fileinfo must match the extension family (images, PDF, DWG).
 */
class SafeUpload implements ValidationRule
{
    /** Extension => accepted sniffed MIME types. */
    public const MIMES = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'dwg' => ['image/vnd.dwg', 'image/x-dwg', 'application/acad', 'application/x-acad', 'application/autocad_dwg', 'application/dwg', 'application/x-dwg', 'application/octet-stream'],
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $mime = (string) $value->getMimeType();

        if (! in_array($mime, self::MIMES[$extension] ?? [], true)) {
            $fail(__('forms.file_type'));
        }
    }
}
