<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum ActivityType: string
{
    use HasLabel;

    case Note = 'note';
    case Status = 'status';
    case Assign = 'assign';
    case System = 'system';
    case Email = 'email';
}
