<?php

namespace App\Livewire\Site;

use App\Enums\SiteEventType;
use App\Livewire\Site\Concerns\WithSpamProtection;
use App\Models\ContactMessage;
use App\Services\RequestIntake;
use App\Settings\FormSettings;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ContactForm extends Component
{
    use WithSpamProtection;

    #[Locked]
    public bool $sent = false;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    /**
     * @return list<string>
     */
    public function subjects(): array
    {
        return array_values(array_map(fn ($item) => Translate::pick($item), app(FormSettings::class)->contact_subjects));
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:25', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:150'],
            'subject' => ['nullable', 'integer', Rule::in(array_keys($this->subjects()))],
            'message' => ['required', 'string', 'min:5', 'max:3000'],
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
            'subject' => __('forms.subject'),
            'message' => __('forms.message'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return ['phone.regex' => __('forms.phone_invalid')];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['name', 'phone', 'email', 'message'], true)) {
            $this->validateOnly($property);
        }
    }

    public function submit(RequestIntake $intake): void
    {
        $this->validate();

        if (! $this->passesSpamChecks('contact')) {
            return;
        }

        $message = ContactMessage::query()->create([
            ...$intake->baseAttributes(),
            'name' => trim($this->name),
            'phone' => trim($this->phone) ?: null,
            'email' => trim($this->email) ?: null,
            'subject' => $this->subject === '' ? null : Translate::pick(app(FormSettings::class)->contact_subjects[(int) $this->subject] ?? [], 'ar'),
            'message' => trim($this->message),
        ]);

        $intake->finalize($message, SiteEventType::ContactSubmit);

        $this->sent = true;
        $this->dispatch('toast', type: 'success', message: __('contact.sent_title'));
    }

    public function again(): void
    {
        $this->reset(['name', 'phone', 'email', 'subject', 'message']);
        $this->sent = false;
        $this->startedAt = now()->getTimestamp();
    }

    public function render(): View
    {
        return view('livewire.site.contact-form', ['subjects' => $this->subjects()]);
    }
}
