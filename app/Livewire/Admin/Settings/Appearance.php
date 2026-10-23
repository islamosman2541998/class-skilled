<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Asset;
use App\Services\Appearance as AppearanceService;
use App\Settings\AppearanceSettings;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

/**
 * Fonts, base size, Arabic line height and brand colours with a live preview.
 */
class Appearance extends SettingsPage
{
    public const COLOR_KEYS = ['navy', 'sky', 'deep', 'ink', 'muted', 'paper', 'line'];

    public function group(): string
    {
        return 'appearance';
    }

    protected function permission(): string
    {
        return 'manage appearance';
    }

    protected function settingsClass(): string
    {
        return AppearanceSettings::class;
    }

    public function tabs(): array
    {
        return [];
    }

    protected function rules(): array
    {
        $rules = [
            'data.heading_font' => ['required', Rule::in(array_keys(AppearanceService::FONTS))],
            'data.body_font' => ['required', Rule::in(array_keys(AppearanceService::FONTS))],
            'data.base_font_size' => ['required', Rule::in(['15', '15.5', '16', 15, 15.5, 16])],
            'data.line_height_ar' => ['required', Rule::in(['1.7', '1.8', '1.85', '1.9', 1.7, 1.8, 1.85, 1.9])],
        ];

        foreach (self::COLOR_KEYS as $key) {
            $rules['data.colors.'.$key] = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        return $rules;
    }

    protected function prepare(array $data): array
    {
        $data['base_font_size'] = (float) $data['base_font_size'];
        $data['line_height_ar'] = (float) $data['line_height_ar'];
        $data['colors'] = array_map(fn ($hex) => strtoupper((string) $hex), $data['colors']);

        return $data;
    }

    public function restoreColors(): void
    {
        $this->data['colors'] = AppearanceService::DEFAULT_COLORS;
    }

    /**
     * Contrast warnings (WCAG AA 4.5:1) for the live colours.
     *
     * @return list<string>
     */
    public function warnings(): array
    {
        $colors = (array) ($this->data['colors'] ?? []);
        $warnings = [];
        $sky = AppearanceService::contrast((string) ($colors['sky'] ?? ''), (string) ($colors['deep'] ?? ''));
        $ink = AppearanceService::contrast((string) ($colors['ink'] ?? ''), '#FFFFFF');

        if ($sky < 4.5) {
            $warnings[] = __('admin.appearance.contrast_sky', ['ratio' => $sky]);
        }

        if ($ink < 4.5) {
            $warnings[] = __('admin.appearance.contrast_ink', ['ratio' => $ink]);
        }

        return $warnings;
    }

    public function previewCss(): string
    {
        return str_replace(':root', '#cs-preview', app(AppearanceService::class)->css($this->data));
    }

    protected function viewData(): array
    {
        $general = app(GeneralSettings::class);
        $logos = Asset::query()->with('media')->whereIn('id', array_filter([$general->logo_asset_id, $general->logo_white_asset_id, $general->favicon_asset_id]))->get()->keyBy('id');

        return [
            'logos' => [
                'logo' => $logos->get((int) $general->logo_asset_id),
                'logo_white' => $logos->get((int) $general->logo_white_asset_id),
                'favicon' => $logos->get((int) $general->favicon_asset_id),
            ],
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.settings.appearance', $this->viewData())->title(__('admin.nav.appearance'));
    }
}
