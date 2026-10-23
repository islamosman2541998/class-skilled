<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum ProjectStatus: string
{
    use HasLabel;

    case Completed = 'completed';
    case Ongoing = 'ongoing';
}
