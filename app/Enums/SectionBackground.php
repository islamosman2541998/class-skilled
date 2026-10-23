<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum SectionBackground: string
{
    use HasLabel;

    case White = 'white';
    case Paper = 'paper';
    case Dark = 'dark';
    case DarkPhoto = 'dark_photo';
}
