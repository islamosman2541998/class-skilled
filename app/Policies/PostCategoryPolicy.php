<?php

namespace App\Policies;

class PostCategoryPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'post_categories';
    }
}
