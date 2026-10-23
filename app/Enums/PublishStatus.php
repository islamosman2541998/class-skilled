<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum PublishStatus: string
{
    use HasLabel;

    case Draft = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';
}
