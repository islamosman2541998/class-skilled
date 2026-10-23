<?php

namespace App\Policies;

class JobApplicationPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'job_applications';
    }
}
