<?php

namespace App\Livewire\Admin\Requests\Messages;

use App\Livewire\Admin\Requests\RequestShow;
use App\Models\ContactMessage;

class Show extends RequestShow
{
    protected function modelClass(): string
    {
        return ContactMessage::class;
    }

    public function routeKey(): string
    {
        return 'messages';
    }
}
