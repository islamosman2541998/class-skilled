<?php

namespace App\Livewire\Admin\Content;

use App\Models\Project;
use App\Models\Testimonial;

/**
 * Real client testimonials only.
 */
class Testimonials extends SimpleResource
{
    protected function modelClass(): string
    {
        return Testimonial::class;
    }

    public function titleKey(): string
    {
        return 'admin.nav.testimonials';
    }

    public function singularKey(): string
    {
        return 'admin.content.singular.testimonial';
    }

    public function fields(): array
    {
        return [
            ['key' => 'name', 'type' => 'pair', 'label' => 'admin.testimonials.name', 'required' => true, 'max' => 120],
            ['key' => 'company', 'type' => 'pair', 'label' => 'admin.testimonials.company', 'max' => 120],
            ['key' => 'body', 'type' => 'pair_textarea', 'label' => 'admin.testimonials.body', 'required' => true, 'max' => 1500, 'rows' => 4],
            ['key' => 'rating', 'type' => 'rating', 'label' => 'admin.testimonials.rating', 'min' => 1, 'maxValue' => 5],
            ['key' => 'project_id', 'type' => 'select', 'label' => 'admin.testimonials.project', 'options' => Project::query()->ordered()->get()->mapWithKeys(fn (Project $p) => [$p->id => $p->getTranslation('title', 'ar')])->all()],
            ['key' => 'photo', 'type' => 'asset', 'collection' => 'photo', 'kind' => 'image', 'label' => 'admin.testimonials.photo'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'type' => 'title', 'label' => 'admin.testimonials.name', 'asset' => 'photo'],
            ['key' => 'company', 'label' => 'admin.testimonials.company'],
            ['key' => 'rating', 'label' => 'admin.testimonials.rating'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }
}
