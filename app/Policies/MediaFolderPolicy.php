<?php

namespace App\Policies;

class MediaFolderPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'media';
    }
}
