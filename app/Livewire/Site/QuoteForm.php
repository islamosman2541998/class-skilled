<?php

namespace App\Livewire\Site;

use App\Enums\SiteEventType;
use App\Livewire\Site\Concerns\WithSpamProtection;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Rules\SafeUpload;
use App\Services\RequestIntake;
use App\Settings\FormSettings;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Four-step quote request: project → location & size → drawings → contact.
 * Each step validates on "next"; files go to the private disk on submit.
 *
 * @property-read Collection<int, Service> $services
 * @property-read array<string, list<string>> $options
 */
class QuoteForm extends Component
{
    use WithFileUploads;
    use WithSpamProtection;

    public const STEPS = 4;

    public const MAX_FILES = 10;

    public int $step = 1;

    #[Locked]
    public ?string $number = null;

    public ?int $service_id = null;

    public string $facility_type = '';

    public string $project_state = '';

    public string $description = '';

    public string $emirate = '';

    public string $area_text = '';

    public string $area_m2 = '';

    public bool $area_unknown = false;

    public string $start_when = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $files = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $newFiles = [];

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $preferred_contact = 'call';

    public function mount(): void
    {
        $preselect = (int) request()->query('service');

        if ($preselect && $this->services->contains('id', $preselect)) {
            $this->service_id = $preselect;
        }
    }

    #[Computed]
    public function services(): Collection
    {
        return Service::query()->active()->translated()->ordered()->get(['id', 'title', 'slug']);
    }

    /**
     * Choice lists from Settings → Forms in the current locale (index => label).
     *
     * @return array<string, list<string>>
     */
    #[Computed]
    public function options(): array
    {
        $settings = app(FormSettings::class);
        $pick = fn (array $items) => array_values(array_map(fn ($item) => Translate::pick($item), $items));

        return [
            'facility_types' => $pick($settings->facility_types),
            'project_states' => $pick($settings->project_states),
            'emirates' => $pick($settings->emirates),
            'start_options' => $pick($settings->start_options),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function stepRules(int $step): array
    {
        $settings = app(FormSettings::class);
        $index = fn (string $list) => ['required', 'integer', Rule::in(array_keys($this->options[$list]))];

        return match ($step) {
            1 => [
                'service_id' => ['required', 'integer', Rule::in($this->services->pluck('id')->all())],
                'facility_type' => $index('facility_types'),
                'project_state' => $index('project_states'),
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            2 => [
                'emirate' => $index('emirates'),
                'area_text' => ['nullable', 'string', 'max:150'],
                'area_m2' => $this->area_unknown ? ['nullable'] : ['required', 'numeric', 'min:1', 'max:1000000'],
                'start_when' => $index('start_options'),
            ],
            3 => [
                'files' => ['array', 'max:'.self::MAX_FILES],
                'files.*' => ['file', 'extensions:'.implode(',', $settings->allowed_extensions), 'max:'.($settings->max_upload_mb * 1024), new SafeUpload],
            ],
            default => [
                'name' => ['required', 'string', 'min:2', 'max:120'],
                'phone' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
                'email' => ['nullable', 'email', 'max:150'],
                'preferred_contact' => ['required', Rule::in(['call', 'whatsapp'])],
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'service_id' => __('quote.service'),
            'facility_type' => __('quote.facility_type'),
            'project_state' => __('quote.project_state'),
            'description' => __('quote.description'),
            'emirate' => __('quote.emirate'),
            'area_text' => __('quote.area_text'),
            'area_m2' => __('quote.area_m2'),
            'start_when' => __('quote.start_when'),
            'files.*' => __('quote.files'),
            'name' => __('forms.name'),
            'phone' => __('forms.phone'),
            'email' => __('forms.email'),
            'preferred_contact' => __('quote.preferred_contact'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        $settings = app(FormSettings::class);

        return [
            'phone.regex' => __('forms.phone_invalid'),
            'files.*.extensions' => __('forms.file_type'),
            'files.*.max' => __('forms.file_size', ['max' => $settings->max_upload_mb]),
        ];
    }

    /**
     * New uploads are checked one by one and appended to the list.
     */
    public function updatedNewFiles(): void
    {
        $settings = app(FormSettings::class);
        $this->resetErrorBag('newFiles.*');

        $this->validate([
            'newFiles.*' => ['file', 'extensions:'.implode(',', $settings->allowed_extensions), 'max:'.($settings->max_upload_mb * 1024), new SafeUpload],
        ], [
            'newFiles.*.extensions' => __('forms.file_type'),
            'newFiles.*.max' => __('forms.file_size', ['max' => $settings->max_upload_mb]),
        ]);

        $this->files = array_slice([...$this->files, ...$this->newFiles], 0, self::MAX_FILES);
        $this->newFiles = [];

        $this->dispatch('toast', type: 'success', message: __('quote.files_uploaded'));
    }

    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function next(): void
    {
        $this->validate($this->stepRules($this->step));
        $this->step = min(self::STEPS, $this->step + 1);
        $this->dispatch('quote-step', step: $this->step);
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->dispatch('quote-step', step: $this->step);
    }

    public function goTo(int $step): void
    {
        // Only completed steps can be revisited.
        if ($step >= 1 && $step < $this->step) {
            $this->step = $step;
        }
    }

    public function submit(RequestIntake $intake): void
    {
        foreach (range(1, self::STEPS) as $step) {
            try {
                $this->validate($this->stepRules($step));
            } catch (ValidationException $e) {
                $this->step = $step;

                throw $e;
            }
        }

        if (! $this->passesSpamChecks('quote')) {
            return;
        }

        $ar = fn (string $list, string $index): ?string => $index === '' ? null : Translate::pick(app(FormSettings::class)->{$list}[(int) $index] ?? [], 'ar');

        $quote = QuoteRequest::query()->create([
            ...$intake->baseAttributes(),
            'name' => trim($this->name),
            'phone' => trim($this->phone),
            'email' => trim($this->email) ?: null,
            'service_id' => $this->service_id,
            'facility_type' => $ar('facility_types', $this->facility_type),
            'project_state' => $ar('project_states', $this->project_state),
            'emirate' => $ar('emirates', $this->emirate),
            'area_text' => trim($this->area_text) ?: null,
            'area_m2' => $this->area_unknown || $this->area_m2 === '' ? null : $this->area_m2,
            'start_when' => $ar('start_options', $this->start_when),
            'description' => trim($this->description) ?: null,
            'preferred_contact' => $this->preferred_contact,
        ]);

        foreach ($this->files as $file) {
            $quote->addMedia($file->getRealPath())
                ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->usingFileName(Str::random(10).'.'.strtolower($file->getClientOriginalExtension()))
                ->toMediaCollection('attachments');
        }

        $intake->finalize($quote->refresh(), SiteEventType::QuoteSubmit);

        $this->number = (string) $quote->number;
        $this->files = [];
        $this->dispatch('toast', type: 'success', message: __('quote.success_toast'));
    }

    public function render(): View
    {
        return view('livewire.site.quote-form', [
            'settings' => app(FormSettings::class),
        ]);
    }
}
