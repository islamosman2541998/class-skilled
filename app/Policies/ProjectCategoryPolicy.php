<?php

namespace App\Policies;

class ProjectCategoryPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'project_categories';
    }
}
