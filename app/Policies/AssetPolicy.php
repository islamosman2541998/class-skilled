<?php

namespace App\Policies;

class AssetPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'media';
    }
}
