<div>
    @if ($sent)
        <div class="rounded-[16px] bg-white p-6 text-center" role="status">
            <span class="mx-auto grid size-12 place-items-center rounded-full bg-sky text-deep"><x-heroicon-o-check class="size-6" aria-hidden="true" /></span>
            <h3 class="t-card mt-4 text-navy">{{ __('forms.application_sent_title') }}</h3>
            <p class="mt-2 text-muted">{{ __('forms.application_sent_text') }}</p>
        </div>
    @elseif ($this->closed())
        <p class="rounded-[12px] bg-white p-5 text-muted">{{ __('forms.job_closed') }}</p>
    @else
        <form wire:submit="submit" class="mt-5 grid gap-5 sm:grid-cols-2" novalidate>
            <div class="absolute -start-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                <label>{{ __('forms.optional') }} <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>
            @error('form')
                <p class="rounded-[12px] bg-[#fdecea] px-4 py-3 text-[14px] font-semibold text-[#b42318] sm:col-span-2" role="alert">{{ $message }}</p>
            @enderror

            @include('site.partials.form-field', ['prefix' => 'a', 'model' => 'name', 'label' => __('forms.name'), 'required' => true, 'max' => 120])
            @include('site.partials.form-field', ['prefix' => 'a', 'model' => 'phone', 'label' => __('forms.phone'), 'type' => 'tel', 'ltr' => true, 'required' => true, 'max' => 25])
            @include('site.partials.form-field', ['prefix' => 'a', 'model' => 'email', 'label' => __('forms.email'), 'type' => 'email', 'ltr' => true, 'required' => true])
            @include('site.partials.form-field', ['prefix' => 'a', 'model' => 'specialty', 'label' => __('forms.specialty')])
            @include('site.partials.form-field', ['prefix' => 'a', 'model' => 'message', 'label' => __('forms.message'), 'type' => 'textarea', 'rows' => 4, 'class' => 'sm:col-span-2'])

            <div class="sm:col-span-2" x-data="{ progress: 0, uploading: false }" x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress">
                <label for="a-cv" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('forms.cv') }} <span class="text-[#b42318]" aria-hidden="true">*</span></label>
                <input id="a-cv" type="file" accept=".pdf,application/pdf" wire:model="cv" class="block w-full rounded-[12px] border border-dashed border-line bg-white p-3 text-[14px] file:me-3 file:rounded-full file:border-0 file:bg-navy file:px-4 file:py-2 file:font-bold file:text-white" aria-describedby="a-cv-hint" @error('cv') aria-invalid="true" @enderror>
                <p id="a-cv-hint" class="t-small mt-1 text-muted">{{ __('forms.cv_hint', ['max' => $maxMb]) }}</p>
                <div x-show="uploading" x-cloak class="mt-2 h-2 overflow-hidden rounded-full bg-line" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"><div class="h-full bg-sky" :style="`width: ${progress}%`"></div></div>
                @error('cv') <p class="mt-1.5 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
            </div>

            @if ($siteKey = \App\Support\Turnstile::siteKey())
                <div class="sm:col-span-2">@include('site.partials.turnstile', ['siteKey' => $siteKey])</div>
            @endif

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submit, cv">
                    <span wire:loading.remove wire:target="submit">{{ __('forms.apply') }}</span>
                    <span wire:loading wire:target="submit">{{ __('forms.sending') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
