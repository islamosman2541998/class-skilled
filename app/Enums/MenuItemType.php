<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum MenuItemType: string
{
    use HasLabel;

    case Home = 'home';
    case Page = 'page';
    case SystemPage = 'system_page';
    case Model = 'model';
    case External = 'external';
    case Heading = 'heading';
    case DynamicGroup = 'dynamic_group';
}
