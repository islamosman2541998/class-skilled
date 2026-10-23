<?php

namespace App\Livewire\Admin\Content\Pages;

use App\Livewire\Admin\Content\ContentIndex;
use App\Models\ContentModel;
use App\Models\MenuItem;
use App\Models\Page;

class Index extends ContentIndex
{
    protected function modelClass(): string
    {
        return Page::class;
    }

    protected function view(): string
    {
        return 'livewire.admin.content.pages.index';
    }

    protected function titleKey(): string
    {
        return 'admin.nav.pages';
    }

    protected function eagerLoads(): array
    {
        return ['parent'];
    }

    protected function exportColumns(): array
    {
        return [
            __('admin.content.fields.title').' (ar)' => fn (ContentModel $p) => $p->getTranslation('title', 'ar'),
            __('admin.content.fields.title').' (en)' => fn (ContentModel $p) => $p->getTranslation('title', 'en', false),
            __('admin.common.status') => fn (ContentModel $p) => $p->getAttribute('status')?->label(),
            __('admin.common.updated_at') => fn (ContentModel $p) => $p->getAttribute('updated_at')?->format('Y-m-d H:i'),
        ];
    }

    protected function viewData(): array
    {
        return [
            'inMenus' => MenuItem::query()->where('linkable_type', 'page')->pluck('linkable_id')->countBy()->all(),
        ];
    }
}
