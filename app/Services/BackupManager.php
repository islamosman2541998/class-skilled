<?php

namespace App\Services;

use App\Settings\SystemSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

/**
 * Best-effort backups for shared hosting: a pure-PHP SQL dump (chunked, no
 * mysqldump) zipped with optional public media into storage/app/backups.
 * Keeps the newest N archives (SystemSettings::backup_keep).
 */
class BackupManager
{
    public function directory(): string
    {
        return storage_path('app/backups');
    }

    public function create(bool $withMedia = false): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is required.');
        }

        // Give web requests room for the archive; the CLI has no limit and must keep none.
        if (! app()->runningInConsole()) {
            @set_time_limit(300);
        }
        File::ensureDirectoryExists($this->directory());

        $name = 'backup-'.now()->format('Y-m-d-His').'.zip';
        $sqlPath = $this->directory().DIRECTORY_SEPARATOR.'database-'.now()->format('Ymd-His').'.sql';

        $this->dumpDatabase($sqlPath);

        $zip = new ZipArchive;
        $zip->open($this->directory().DIRECTORY_SEPARATOR.$name, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($sqlPath, 'database.sql');

        if ($withMedia) {
            $root = storage_path('app/public');

            foreach (File::allFiles($root) as $file) {
                $zip->addFile($file->getPathname(), 'public/'.str_replace('\\', '/', $file->getRelativePathname()));
            }
        }

        $zip->close();
        @unlink($sqlPath);

        $this->prune();

        return $name;
    }

    /**
     * @return list<array{name: string, size: int, date: int}>
     */
    public function list(): array
    {
        if (! is_dir($this->directory())) {
            return [];
        }

        $files = [];

        foreach (File::files($this->directory()) as $file) {
            if ($file->getExtension() === 'zip') {
                $files[] = ['name' => $file->getFilename(), 'size' => (int) $file->getSize(), 'date' => (int) $file->getMTime()];
            }
        }

        usort($files, fn ($a, $b) => $b['date'] <=> $a['date']);

        return $files;
    }

    public function path(string $name): ?string
    {
        if (! preg_match('/^backup-[\d-]+\.zip$/', $name)) {
            return null;
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.$name;

        return is_file($path) ? $path : null;
    }

    public function delete(string $name): bool
    {
        $path = $this->path($name);

        return $path !== null && @unlink($path);
    }

    private function prune(): void
    {
        $keep = max(1, app(SystemSettings::class)->backup_keep);

        foreach (array_slice($this->list(), $keep) as $old) {
            $this->delete($old['name']);
        }
    }

    private function dumpDatabase(string $path): void
    {
        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new RuntimeException('Cannot write '.$path);
        }

        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $pdo = $connection->getPdo();

        fwrite($handle, '-- Class Skilled backup '.now()->toIso8601String()."\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($this->tables() as $table) {
            if ($driver === 'mysql') {
                $create = (array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n".array_values($create)[1].";\n\n");
            }

            DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($handle, $table, $pdo): void {
                $values = [];

                foreach ($rows as $row) {
                    $cells = array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), (array) $row);
                    $values[] = '('.implode(',', $cells).')';
                }

                if ($values !== []) {
                    $columns = '`'.implode('`,`', array_keys((array) $rows->first())).'`';
                    fwrite($handle, "INSERT INTO `{$table}` ({$columns}) VALUES\n".implode(",\n", $values).";\n");
                }
            });

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        $tables = [];

        foreach (DB::connection()->getSchemaBuilder()->getTables() as $table) {
            $tables[] = (string) $table['name'];
        }

        return $tables;
    }
}
