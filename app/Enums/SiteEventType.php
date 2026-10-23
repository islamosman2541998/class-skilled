<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum SiteEventType: string
{
    use HasLabel;

    case WhatsappClick = 'whatsapp_click';
    case CallClick = 'call_click';
    case EmailClick = 'email_click';
    case QuoteSubmit = 'quote_submit';
    case ContactSubmit = 'contact_submit';
    case ApplicationSubmit = 'application_submit';
}
