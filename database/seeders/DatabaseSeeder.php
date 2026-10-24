<?php

namespace Database\Seeders;

use App\Services\SiteCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // One transaction and one cache flush: much faster on slow disks.
        app(SiteCache::class)->withoutFlushing(fn () => DB::transaction(function (): void {
            $this->call(EssentialSeeder::class);

            if (app()->environment('local')) {
                $this->call(DemoContentSeeder::class);
                $this->call(DemoActivitySeeder::class);
            }
        }));
    }
}
