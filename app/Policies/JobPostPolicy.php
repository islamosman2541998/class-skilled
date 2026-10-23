<?php

namespace App\Policies;

class JobPostPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'job_posts';
    }
}
