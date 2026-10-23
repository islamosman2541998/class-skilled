<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum DynamicSource: string
{
    use HasLabel;

    case Services = 'services';
    case Projects = 'projects';
    case ProjectCategories = 'project_categories';
    case Posts = 'posts';
    case PostCategories = 'post_categories';
    case Pages = 'pages';
}
