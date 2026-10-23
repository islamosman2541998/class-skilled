<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum EmbedProvider: string
{
    use HasLabel;

    case Youtube = 'youtube';
    case Vimeo = 'vimeo';
}
