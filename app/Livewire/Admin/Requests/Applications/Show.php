<?php

namespace App\Livewire\Admin\Requests\Applications;

use App\Livewire\Admin\Requests\RequestShow;
use App\Models\JobApplication;

class Show extends RequestShow
{
    protected function modelClass(): string
    {
        return JobApplication::class;
    }

    public function routeKey(): string
    {
        return 'applications';
    }
}
