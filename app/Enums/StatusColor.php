<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum StatusColor: string
{
    use HasLabel;

    case Sky = 'sky';
    case Amber = 'amber';
    case Violet = 'violet';
    case Navy = 'navy';
    case Green = 'green';
    case Red = 'red';
    case Gray = 'gray';
}
