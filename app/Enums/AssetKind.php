<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum AssetKind: string
{
    use HasLabel;

    case Image = 'image';
    case Video = 'video';
    case Document = 'document';
    case Embed = 'embed';
}
