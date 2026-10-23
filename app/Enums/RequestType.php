<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum RequestType: string
{
    use HasLabel;

    case Quote = 'quote';
    case Message = 'message';
    case Application = 'application';
}
