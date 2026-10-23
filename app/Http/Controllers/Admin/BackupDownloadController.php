<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Signed, permission-checked download of a backup archive.
 */
class BackupDownloadController extends Controller
{
    public function __invoke(string $name, BackupManager $backups): BinaryFileResponse
    {
        abort_unless(auth()->user()?->can('manage backups'), 403);
        $path = $backups->path($name);
        abort_if($path === null, 404);

        return response()->download($path);
    }
}
