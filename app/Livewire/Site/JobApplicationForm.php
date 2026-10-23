<?php

namespace App\Livewire\Site;

use App\Enums\SiteEventType;
use App\Livewire\Site\Concerns\WithSpamProtection;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Services\RequestIntake;
use App\Settings\FormSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Application for a job (job page) or a general application (careers page). CV = PDF on the private disk.
 */
class JobApplicationForm extends Component
{
    use WithFileUploads;
    use WithSpamProtection;

    #[Locked]
    public ?int $jobId = null;

    #[Locked]
    public bool $sent = false;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $specialty = '';

    public string $message = '';

    public ?TemporaryUploadedFile $cv = null;

    public function mount(?int $jobId = null): void
    {
        $this->jobId = $jobId;
    }

    private function job(): ?JobPost
    {
        return $this->jobId ? JobPost::query()->find($this->jobId) : null;
    }

    public function closed(): bool
    {
        $job = $this->job();

        return $this->jobId !== null && ($job === null || ! $job->isOpen());
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'email' => ['required', 'email', 'max:150'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'message' => ['nullable', 'string', 'max:3000'],
            'cv' => ['required', 'file', 'mimes:pdf', 'extensions:pdf', 'max:'.(app(FormSettings::class)->max_upload_mb * 1024)],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('forms.name'),
            'phone' => __('forms.phone'),
            'email' => __('forms.email'),
            'specialty' => __('forms.specialty'),
            'message' => __('forms.message'),
            'cv' => __('forms.cv'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'phone.regex' => __('forms.phone_invalid'),
            'cv.mimes' => __('forms.file_type'),
            'cv.extensions' => __('forms.file_type'),
            'cv.max' => __('forms.file_size', ['max' => app(FormSettings::class)->max_upload_mb]),
        ];
    }

    public function updatedCv(): void
    {
        $this->validateOnly('cv');
    }

    public function submit(RequestIntake $intake): void
    {
        if ($this->closed()) {
            $this->addError('form', __('forms.job_closed'));

            return;
        }

        $this->validate();

        if (! $this->passesSpamChecks('application') || $this->cv === null) {
            return;
        }

        $application = JobApplication::query()->create([
            ...$intake->baseAttributes(),
            'job_post_id' => $this->jobId,
            'name' => trim($this->name),
            'phone' => trim($this->phone),
            'email' => trim($this->email),
            'specialty' => trim($this->specialty) ?: null,
            'message' => trim($this->message) ?: null,
        ]);

        $application->addMedia($this->cv->getRealPath())
            ->usingName(pathinfo($this->cv->getClientOriginalName(), PATHINFO_FILENAME))
            ->usingFileName(Str::random(10).'.pdf')
            ->toMediaCollection('cv');

        $intake->finalize($application, SiteEventType::ApplicationSubmit);

        $this->sent = true;
        $this->cv = null;
        $this->dispatch('toast', type: 'success', message: __('forms.application_sent_title'));
    }

    public function render(): View
    {
        return view('livewire.site.job-application-form', [
            'maxMb' => app(FormSettings::class)->max_upload_mb,
        ]);
    }
}
