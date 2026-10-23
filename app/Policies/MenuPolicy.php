<?php

namespace App\Policies;

class MenuPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'menus';
    }
}
