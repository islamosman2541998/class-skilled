<?php

namespace App\Livewire\Site\Concerns;

use App\Services\RequestIntake;
use Livewire\Attributes\Locked;

/**
 * Honeypot field, minimum fill time (server-set, locked timestamp) and the
 * optional Turnstile token for the public Livewire forms.
 */
trait WithSpamProtection
{
    /** Hidden field humans never fill. */
    public string $website = '';

    #[Locked]
    public int $startedAt = 0;

    public ?string $turnstileToken = null;

    public function mountWithSpamProtection(): void
    {
        $this->startedAt = now()->getTimestamp();
    }

    /**
     * Refuses the submission with a toast + form error when a check fails.
     */
    protected function passesSpamChecks(string $form): bool
    {
        $reason = app(RequestIntake::class)->reject($form, $this->website, $this->startedAt, $this->turnstileToken);

        if ($reason === null) {
            return true;
        }

        $this->addError('form', __($reason));
        $this->dispatch('toast', type: 'error', message: __($reason));

        return false;
    }
}
