<?php

namespace App\Console\Commands;

use App\Support\SiteCheck;
use Illuminate\Console\Command;

class SiteCheckCommand extends Command
{
    protected $signature = 'site:check';

    protected $description = 'Check the hosting environment (PHP, extensions, folders, storage link, database, cache, mail)';

    public function handle(SiteCheck $check): int
    {
        $results = $check->run();

        $this->table(['Group', 'Check', 'Status', 'Detail'], array_map(fn (array $row) => [
            $row['group'],
            $row['label'],
            match ($row['status']) {
                'ok' => '<info>OK</info>',
                'warn' => '<comment>WARN</comment>',
                default => '<error>FAIL</error>',
            },
            $row['detail'],
        ], $results));

        if (SiteCheck::failed($results)) {
            $this->error('Some checks failed.');

            return self::FAILURE;
        }

        $this->info('Environment looks good.');

        return self::SUCCESS;
    }
}
