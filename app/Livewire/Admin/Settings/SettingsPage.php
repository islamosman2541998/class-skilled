<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\WithRepeaters;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Livewire\Admin\Concerns\WithTranslatableForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\LaravelSettings\Settings;

/**
 * Base for one settings group screen: `$data` mirrors the settings class
 * properties, `rules()` validates `data.*`, save() persists + flushes caches
 * (SettingsSaved → SiteCache::flush) and logs activity.
 */
#[Layout('layouts.admin')]
abstract class SettingsPage extends Component
{
    use WithRepeaters;
    use WithToasts;
    use WithTranslatableForm;

    /** Screens shown in the settings tab bar: key => [route, permission]. */
    public const GROUPS = [
        'general' => ['admin.settings.general', 'manage settings'],
        'company' => ['admin.settings.company', 'manage settings'],
        'contact' => ['admin.settings.contact', 'manage settings'],
        'social' => ['admin.settings.social', 'manage settings'],
        'notifications' => ['admin.settings.notifications', 'manage settings'],
        'forms' => ['admin.settings.forms', 'manage settings'],
        'media' => ['admin.settings.media', 'manage settings'],
        'tracking' => ['admin.settings.tracking', 'manage settings'],
        'maintenance' => ['admin.settings.maintenance', 'manage settings'],
        'system' => ['admin.settings.system', 'manage settings'],
    ];

    /** @var array<string, mixed> */
    public array $data = [];

    /**
     * @return class-string<Settings>
     */
    abstract protected function settingsClass(): string;

    abstract public function group(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function rules(): array;

    protected function permission(): string
    {
        return 'manage settings';
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can($this->permission()), 403);
        $this->data = $this->load(app($this->settingsClass())->toArray());
    }

    /**
     * Shape stored values for the form (e.g. add repeater keys).
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function load(array $values): array
    {
        return $values;
    }

    /**
     * Shape form values for storage.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        return $data;
    }

    protected function afterSave(Settings $settings): void {}

    /**
     * @return array<string, string>
     */
    protected function attributes(): array
    {
        return [];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can($this->permission()), 403);
        $this->validate($this->rules(), [], [...$this->fieldNames(array_keys($this->rules())), ...$this->attributes()]);

        $settings = app($this->settingsClass());
        $values = $this->prepare($this->stripRepeaterKeys($this->data));
        $settings->fill(array_intersect_key($values, $settings->toArray()));
        $settings->save();

        $this->afterSave($settings);

        activity()->causedBy(auth()->user())->event('updated')
            ->withProperties(['attributes' => $values])
            ->log('Settings:'.$this->group());

        $this->dispatch('form-saved');
        $this->toast('success', __('admin.settings.saved'));
    }

    /**
     * Readable names for "data.field" / "data.field.ar|en" keys, taken from the field labels in lang/<locale>/admin.php,
     * e.g. "data.home_title.en" => "Home page title (English)".
     *
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    protected function fieldNames(array $keys): array
    {
        $names = [];

        foreach ($keys as $key) {
            $parts = explode('.', $key);

            if ($parts[0] !== 'data' || ! isset($parts[1])) {
                continue;
            }

            $label = collect(['admin.'.$this->group().'.'.$parts[1], 'admin.'.$this->group().'.search.'.$parts[1], 'admin.settings.fields.'.$parts[1]])
                ->first(fn (string $k) => is_string(trans($k)) && trans($k) !== $k);

            if ($label === null) {
                continue;
            }

            $locale = count($parts) === 3 && in_array($parts[2], ['ar', 'en'], true) ? $parts[2] : null;
            $names[$key] = __($label).($locale ? ' ('.($locale === 'ar' ? __('admin.common.arabic') : 'English').')' : '');
        }

        return $names;
    }

    /**
     * Translatable pair rules prefixed with "data.".
     *
     * @return array<string, mixed>
     */
    protected function pair(string $key, bool $required = false, int $max = 255): array
    {
        return $this->pairRules('data.'.$key, $required, $max);
    }

    /**
     * Normalise a list of `{ar, en}` repeater items, dropping empty ones.
     *
     * @param  array<int, mixed>  $items
     * @return list<array{ar: string, en: string}>
     */
    protected function pairList(array $items): array
    {
        $list = [];

        foreach ($items as $item) {
            $pair = ['ar' => trim((string) ($item['ar'] ?? '')), 'en' => trim((string) ($item['en'] ?? ''))];

            if ($pair['ar'] !== '' || $pair['en'] !== '') {
                $list[] = $pair;
            }
        }

        return $list;
    }

    /**
     * Repeater of plain strings: [{_key, value}] ⇄ ['…'].
     *
     * @param  array<int, mixed>  $items
     * @return list<string>
     */
    protected function stringList(array $items): array
    {
        return array_values(array_filter(array_map(
            fn ($item) => trim((string) (is_array($item) ? ($item['value'] ?? '') : $item)),
            $items,
        ), fn (string $value) => $value !== ''));
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        $tabs = [];

        foreach (self::GROUPS as $key => [$route, $permission]) {
            if ($key === 'tracking' && ! auth()->user()?->hasRole('admin')) {
                continue;
            }

            if (auth()->user()?->can($permission) && Route::has($route)) {
                $tabs[$key] = route($route);
            }
        }

        return $tabs;
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [];
    }

    public function render(): View
    {
        return view('livewire.admin.settings.'.$this->group(), $this->viewData())
            ->title(__('admin.settings.groups.'.$this->group()));
    }
}
