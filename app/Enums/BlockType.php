<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum BlockType: string
{
    use HasLabel;

    case RichText = 'rich_text';
    case MediaText = 'media_text';
    case Gallery = 'gallery';
    case Video = 'video';
    case Cards = 'cards';
    case Faq = 'faq';
    case Cta = 'cta';
    case Files = 'files';
    case Quote = 'quote';
    case TwoColumns = 'two_columns';
    case ValuesList = 'values_list';
}
