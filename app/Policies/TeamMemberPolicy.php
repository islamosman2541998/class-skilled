<?php

namespace App\Policies;

class TeamMemberPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'team_members';
    }
}
