<?php

namespace App\Policies;

class PostPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'posts';
    }
}
