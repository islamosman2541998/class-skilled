<?php

namespace App\Livewire\Admin\Requests\Quotes;

use App\Livewire\Admin\Requests\RequestShow;
use App\Models\QuoteRequest;

class Show extends RequestShow
{
    protected function modelClass(): string
    {
        return QuoteRequest::class;
    }

    public function routeKey(): string
    {
        return 'quotes';
    }
}
