<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum JobType: string
{
    use HasLabel;

    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
}
