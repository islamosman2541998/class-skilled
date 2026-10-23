<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum DataSource: string
{
    use HasLabel;

    case Manual = 'manual';
    case All = 'all';
    case Featured = 'featured';
    case Latest = 'latest';
}
