<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum MenuLocation: string
{
    use HasLabel;

    case Header = 'header';
    case Topbar = 'topbar';
    case Footer1 = 'footer_1';
    case Footer2 = 'footer_2';
    case Footer3 = 'footer_3';
    case FooterLegal = 'footer_legal';
    case Mobile = 'mobile';
}
