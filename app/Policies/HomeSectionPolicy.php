<?php

namespace App\Policies;

class HomeSectionPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'home_sections';
    }
}
