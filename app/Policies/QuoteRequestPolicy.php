<?php

namespace App\Policies;

class QuoteRequestPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'quote_requests';
    }
}
