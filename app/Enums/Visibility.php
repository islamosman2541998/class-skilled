<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum Visibility: string
{
    use HasLabel;

    case All = 'all';
    case Desktop = 'desktop';
    case Mobile = 'mobile';
}
