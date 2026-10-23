<?php

namespace App\Livewire\Admin\Content;

use App\Models\Certificate;
use App\Models\ContentModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Licences & certificates; expiry within 90 days is amber, expired is red.
 */
class Certificates extends SimpleResource
{
    protected function modelClass(): string
    {
        return Certificate::class;
    }

    public function titleKey(): string
    {
        return 'admin.nav.certificates';
    }

    public function singularKey(): string
    {
        return 'admin.content.singular.certificate';
    }

    public function fields(): array
    {
        return [
            ['key' => 'name', 'type' => 'pair', 'label' => 'admin.certificates.name', 'required' => true, 'max' => 200],
            ['key' => 'number', 'type' => 'text', 'label' => 'admin.certificates.number', 'max' => 100, 'ltr' => true],
            ['key' => 'authority', 'type' => 'pair', 'label' => 'admin.certificates.authority', 'max' => 200],
            ['key' => 'issued_at', 'type' => 'date', 'label' => 'admin.certificates.issued_at'],
            ['key' => 'expires_at', 'type' => 'date', 'label' => 'admin.certificates.expires_at'],
            ['key' => 'file', 'type' => 'asset', 'collection' => 'file', 'kind' => 'document', 'label' => 'admin.certificates.file'],
            ['key' => 'image', 'type' => 'asset', 'collection' => 'image', 'kind' => 'image', 'label' => 'admin.certificates.image'],
            ['key' => 'verify_url', 'type' => 'url', 'label' => 'admin.certificates.verify_url'],
            ['key' => 'show_on_site', 'type' => 'toggle', 'label' => 'admin.certificates.show_on_site'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'type' => 'title', 'label' => 'admin.certificates.name'],
            ['key' => 'number', 'label' => 'admin.certificates.number'],
            ['key' => 'expires_at', 'type' => 'html', 'label' => 'admin.certificates.expires_at'],
            ['key' => 'show_on_site', 'type' => 'toggle', 'label' => 'admin.certificates.show_on_site'],
        ];
    }

    protected function applyModuleFilters(Builder $query): void
    {
        match ($this->filters['expiry'] ?? '') {
            'soon' => $query->whereDate('expires_at', '>=', today())->whereDate('expires_at', '<=', today()->addDays(90)),
            'expired' => $query->whereDate('expires_at', '<', today()),
            default => null,
        };
    }

    public function filterChips(): array
    {
        return [['expiry', __('admin.certificates.expiry'), ['soon' => __('admin.certificates.expires_soon'), 'expired' => __('admin.certificates.expired')]]];
    }

    public function cell(ContentModel $row, string $key): string
    {
        if ($key === 'expires_at' && $row instanceof Certificate) {
            if ($row->expires_at === null) {
                return '—';
            }

            $date = e($row->expires_at->format('Y-m-d'));

            return match (true) {
                $row->isExpired() => '<span class="a-badge a-badge-red">'.$date.' · '.e(__('admin.certificates.expired')).'</span>',
                $row->expiresSoon() => '<span class="a-badge a-badge-amber">'.$date.' · '.e(__('admin.certificates.expires_soon')).'</span>',
                default => '<span class="a-badge a-badge-green">'.$date.'</span>',
            };
        }

        return parent::cell($row, $key);
    }
}
