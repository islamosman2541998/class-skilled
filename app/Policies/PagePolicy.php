<?php

namespace App\Policies;

class PagePolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'pages';
    }
}
