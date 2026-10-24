<?php

namespace App\Livewire\Admin\Seo;

use App\Livewire\Admin\Settings\SettingsPage;
use App\Services\FaviconGenerator;
use App\Services\Sitemap;
use App\Settings\SeoSettings;
use Illuminate\Contracts\View\View;

class Settings extends SettingsPage
{
    public string $schemaJson = '';

    public function group(): string
    {
        return 'seo';
    }

    protected function permission(): string
    {
        return 'manage seo';
    }

    protected function settingsClass(): string
    {
        return SeoSettings::class;
    }

    public function tabs(): array
    {
        return [];
    }

    public function mount(): void
    {
        parent::mount();
        $this->schemaJson = $this->data['schema_extra'] ? (string) json_encode($this->data['schema_extra'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    }

    protected function rules(): array
    {
        return [
            ...$this->pair('title_suffix', false, 80),
            ...$this->pair('search_site_name', true, 70),
            ...$this->pair('search_alternate_name', false, 70),
            ...$this->pair('home_title', true, 90),
            ...$this->pair('home_description', true, 170),
            'data.search_icon_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'data.search_logo_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            ...$this->pair('default_description', false, 300),
            'data.default_og_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'data.google_verification' => ['nullable', 'string', 'max:120'],
            'data.bing_verification' => ['nullable', 'string', 'max:120'],
            'data.robots_txt' => ['nullable', 'string', 'max:5000'],
            'schemaJson' => ['nullable', 'json'],
        ];
    }

    protected function attributes(): array
    {
        return ['schemaJson' => __('admin.seo.schema_extra')];
    }

    protected function prepare(array $data): array
    {
        foreach (['default_og_asset_id', 'search_icon_asset_id', 'search_logo_asset_id'] as $key) {
            $data[$key] = ($data[$key] ?? null) ? (int) $data[$key] : null;
        }
        $data['schema_extra'] = filled($this->schemaJson) ? (array) json_decode($this->schemaJson, true) : [];
        $data['robots_txt'] = str_replace("\r\n", "\n", (string) ($data['robots_txt'] ?? ''));

        foreach (['google_verification', 'bing_verification'] as $key) {
            $data[$key] = filled($data[$key] ?? null) ? trim((string) $data[$key]) : null;
        }

        return $data;
    }

    protected function afterSave(\Spatie\LaravelSettings\Settings $settings): void
    {
        // The icon next to Google results is the favicon.
        app(FaviconGenerator::class)->refresh();
    }

    public function regenerateSitemap(): void
    {
        abort_unless(auth()->user()?->can('manage seo'), 403);
        app(Sitemap::class)->regenerate();
        $this->dispatch('toast', type: 'success', message: __('admin.seo.sitemap_regenerated'));
    }

    public function render(): View
    {
        return view('livewire.admin.seo.settings')->title(__('admin.nav.seo_settings'));
    }
}
