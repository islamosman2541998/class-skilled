<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum HeroMediaType: string
{
    use HasLabel;

    case Image = 'image';
    case Video = 'video';
    case Embed = 'embed';
    case Pdf = 'pdf';
    case None = 'none';
}
