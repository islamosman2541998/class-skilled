<?php

namespace App\Policies;

class ContactMessagePolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'contact_messages';
    }
}
