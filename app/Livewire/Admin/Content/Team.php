<?php

namespace App\Livewire\Admin\Content;

use App\Models\TeamMember;

class Team extends SimpleResource
{
    protected function modelClass(): string
    {
        return TeamMember::class;
    }

    public function titleKey(): string
    {
        return 'admin.nav.team';
    }

    public function singularKey(): string
    {
        return 'admin.content.singular.team_member';
    }

    public function fields(): array
    {
        return [
            ['key' => 'name', 'type' => 'pair', 'label' => 'admin.team.name', 'required' => true, 'max' => 120],
            ['key' => 'role', 'type' => 'pair', 'label' => 'admin.team.role', 'max' => 120],
            ['key' => 'bio', 'type' => 'pair_textarea', 'label' => 'admin.team.bio', 'max' => 1500],
            ['key' => 'photo', 'type' => 'asset', 'collection' => 'photo', 'kind' => 'image', 'label' => 'admin.team.photo'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'type' => 'title', 'label' => 'admin.team.name', 'asset' => 'photo'],
            ['key' => 'role', 'label' => 'admin.team.role'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }
}
