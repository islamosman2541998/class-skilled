<?php

namespace App\Livewire\Admin\Content;

use App\Models\Client;

class Clients extends SimpleResource
{
    protected function modelClass(): string
    {
        return Client::class;
    }

    public function titleKey(): string
    {
        return 'admin.nav.clients';
    }

    public function singularKey(): string
    {
        return 'admin.content.singular.client';
    }

    public function fields(): array
    {
        return [
            ['key' => 'name', 'type' => 'pair', 'label' => 'admin.clients.name', 'required' => true, 'max' => 120],
            ['key' => 'logo', 'type' => 'asset', 'collection' => 'logo', 'kind' => 'image', 'label' => 'admin.clients.logo', 'required' => true],
            ['key' => 'url', 'type' => 'url', 'label' => 'admin.clients.url'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'type' => 'title', 'label' => 'admin.clients.name', 'asset' => 'logo'],
            ['key' => 'url', 'label' => 'admin.clients.url'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }
}
