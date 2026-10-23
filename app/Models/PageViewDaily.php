<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property Carbon $date
 * @property string $path
 * @property string $locale
 * @property int $views
 */
class PageViewDaily extends Model
{
    protected $table = 'page_views_daily';

    public $timestamps = false;

    protected $fillable = ['date', 'path', 'locale', 'views'];

    protected function casts(): array
    {
        return ['date' => 'date', 'views' => 'integer'];
    }

    public static function hit(string $path, string $locale, ?Carbon $date = null): void
    {
        $day = ($date ?? now())->toDateString();
        $path = mb_substr($path, 0, 255);

        DB::table('page_views_daily')->upsert(
            [['date' => $day, 'path' => $path, 'locale' => $locale, 'views' => 1]],
            ['date', 'path', 'locale'],
            ['views' => DB::raw('views + 1')],
        );
    }
}
