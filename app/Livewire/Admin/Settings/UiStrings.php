<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\WithDataTable;
use App\Livewire\Admin\Concerns\WithToasts;
use Database\Seeders\EssentialSeeder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\TranslationLoader\LanguageLine;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public site UI strings (`language_lines`): search, group / missing-English
 * filters, inline AR/EN editing, reset to default, JSON import/export.
 */
#[Layout('layouts.admin')]
class UiStrings extends Component
{
    use WithDataTable;
    use WithFileUploads;
    use WithToasts;

    public ?int $editing = null;

    /** @var array{ar: string, en: string} */
    public array $text = ['ar' => '', 'en' => ''];

    /** @var UploadedFile|null */
    public $importFile = null;

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->can('manage ui strings'), 403);
    }

    protected function baseQuery(): Builder
    {
        return LanguageLine::query();
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.addcslashes($term, '%_\\').'%';
        $query->where('key', 'like', $like)
            ->orWhere('group', 'like', $like)
            ->orWhere('text->ar', 'like', $like)
            ->orWhere('text->en', 'like', $like);
    }

    protected function applyFilters(Builder $query): void
    {
        if (filled($this->filters['group'] ?? null)) {
            $query->where('group', (string) $this->filters['group']);
        }

        if (($this->filters['missing'] ?? null) === '1') {
            $query->where(fn (Builder $q) => $q->whereNull('text->en')->orWhere('text->en', ''));
        }
    }

    protected function sortableColumns(): array
    {
        return ['key' => 'key', 'group' => 'group', 'updated' => 'updated_at'];
    }

    protected function defaultSort(): array
    {
        return ['group', 'asc'];
    }

    protected function exportName(): string
    {
        return 'ui-strings';
    }

    protected function exportColumns(): array
    {
        return [
            'group' => fn (Model $l) => $l->getAttribute('group'),
            'key' => fn (Model $l) => $l->getAttribute('key'),
            'ar' => fn (Model $l) => $l->getAttribute('text')['ar'] ?? '',
            'en' => fn (Model $l) => $l->getAttribute('text')['en'] ?? '',
        ];
    }

    public function edit(int $id): void
    {
        $line = LanguageLine::query()->findOrFail($id);
        $this->editing = $line->id;
        $this->text = ['ar' => (string) ($line->text['ar'] ?? ''), 'en' => (string) ($line->text['en'] ?? '')];
        $this->resetErrorBag();
    }

    public function cancel(): void
    {
        $this->editing = null;
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $this->validate([
            'text.ar' => ['required', 'string', 'max:2000'],
            'text.en' => ['nullable', 'string', 'max:2000'],
        ], [], ['text.ar' => __('admin.common.arabic'), 'text.en' => 'English']);

        $line = LanguageLine::query()->findOrFail((int) $this->editing);
        $line->text = array_filter(['ar' => trim($this->text['ar']), 'en' => trim($this->text['en'])], fn ($v) => $v !== '');
        $line->save();

        activity()->causedBy(auth()->user())->performedOn($line)->event('updated')->withProperties(['attributes' => $line->text])->log('LanguageLine');
        $this->editing = null;
        unset($this->rows);
        $this->toast('success', __('admin.common.saved'));
    }

    public function resetDefault(int $id): void
    {
        $this->authorizeAccess();
        $line = LanguageLine::query()->findOrFail($id);
        $default = EssentialSeeder::defaultStrings()[$line->group][$line->key] ?? null;

        if ($default === null) {
            $this->toast('warning', __('admin.ui_strings.no_default'));

            return;
        }

        $line->text = $default;
        $line->save();
        $this->editing = null;
        unset($this->rows);
        $this->toast('success', __('admin.ui_strings.reset_done'));
    }

    public function hasDefault(string $group, string $key): bool
    {
        return isset(EssentialSeeder::defaultStrings()[$group][$key]);
    }

    public function exportJson(): StreamedResponse
    {
        $this->authorizeAccess();
        $lines = LanguageLine::query()->orderBy('group')->orderBy('key')->get(['group', 'key', 'text']);
        $payload = [];

        foreach ($lines as $line) {
            $payload[$line->group][$line->key] = $line->text;
        }

        return response()->streamDownload(
            fn () => print (json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'ui-strings-'.now()->format('Y-m-d').'.json',
            ['Content-Type' => 'application/json'],
        );
    }

    public function updatedImportFile(): void
    {
        $this->authorizeAccess();
        $this->validate(['importFile' => ['required', 'file', 'max:2048']]);

        $json = json_decode((string) file_get_contents($this->importFile->getRealPath()), true);
        $this->importFile = null;

        if (! is_array($json)) {
            $this->toast('error', __('admin.ui_strings.invalid_json'));

            return;
        }

        $count = 0;

        foreach ($json as $group => $lines) {
            if (! is_array($lines)) {
                continue;
            }

            foreach ($lines as $key => $text) {
                if (! is_array($text) || blank($text['ar'] ?? null)) {
                    continue;
                }

                $line = LanguageLine::query()->firstOrNew(['group' => (string) $group, 'key' => (string) $key]);
                $line->text = array_filter(['ar' => (string) $text['ar'], 'en' => (string) ($text['en'] ?? '')], fn ($v) => $v !== '');
                $line->save();
                $count++;
            }
        }

        unset($this->rows);
        $this->toast('success', __('admin.ui_strings.imported', ['count' => $count]));
    }

    /**
     * Highlight :placeholders and {{swiper}} tokens in a string for display.
     */
    public static function highlight(string $text): string
    {
        $escaped = e($text);

        return (string) preg_replace('/(:[a-z_]+|\{\{[a-zA-Z]+\}\}|\{[a-z_]+\})/', '<mark class="rounded bg-sky-100 px-0.5 text-[var(--c-navy)]">$1</mark>', $escaped);
    }

    public function render(): View
    {
        return view('livewire.admin.settings.ui-strings', [
            'groups' => LanguageLine::query()->distinct()->orderBy('group')->pluck('group', 'group')->all(),
        ])->title(__('admin.nav.ui_strings'));
    }
}
