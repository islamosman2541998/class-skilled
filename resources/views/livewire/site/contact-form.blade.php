<div>
    @if ($sent)
        <div class="rounded-[20px] border border-line bg-white p-8 text-center" role="status">
            <span class="mx-auto grid size-12 place-items-center rounded-full bg-sky text-deep"><x-heroicon-o-check class="size-6" aria-hidden="true" /></span>
            <h3 class="t-section mt-4">{{ __('contact.sent_title') }}</h3>
            <p class="t-lead mx-auto mt-2">{{ __('contact.sent_text') }}</p>
            <button type="button" class="btn btn-secondary mt-6" wire:click="again">{{ __('contact.send_another') }}</button>
        </div>
    @else
        <form wire:submit="submit" class="grid gap-5 rounded-[20px] border border-line bg-white p-5 sm:grid-cols-2 sm:p-7" novalidate>
            <div class="absolute -start-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                <label>{{ __('forms.optional') }} <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>
            @error('form')
                <p class="rounded-[12px] bg-[#fdecea] px-4 py-3 text-[14px] font-semibold text-[#b42318] sm:col-span-2" role="alert">{{ $message }}</p>
            @enderror

            @include('site.partials.form-field', ['prefix' => 'c', 'model' => 'name', 'label' => __('forms.name'), 'required' => true, 'max' => 120])
            @include('site.partials.form-field', ['prefix' => 'c', 'model' => 'phone', 'label' => __('forms.phone'), 'type' => 'tel', 'ltr' => true, 'max' => 25])
            @include('site.partials.form-field', ['prefix' => 'c', 'model' => 'email', 'label' => __('forms.email'), 'type' => 'email', 'ltr' => true])
            <div>
                <label for="c-subject" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('forms.subject') }}</label>
                <select id="c-subject" wire:model="subject" class="h-12 w-full rounded-[12px] border border-line bg-white px-4 text-[15px] focus:border-sky focus:outline-none">
                    <option value="">{{ __('forms.select') }}</option>
                    @foreach ($subjects as $i => $label)
                        <option value="{{ $i }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @include('site.partials.form-field', ['prefix' => 'c', 'model' => 'message', 'label' => __('forms.message'), 'type' => 'textarea', 'required' => true, 'class' => 'sm:col-span-2'])

            @if ($siteKey = \App\Support\Turnstile::siteKey())
                <div class="sm:col-span-2">@include('site.partials.turnstile', ['siteKey' => $siteKey])</div>
            @endif

            <div class="flex flex-col items-start gap-3 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="t-small text-muted">{{ __('messages.privacy_note') }}</p>
                <button type="submit" class="btn btn-primary shrink-0" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">{{ __('contact.send') }}</span>
                    <span wire:loading wire:target="submit">{{ __('forms.sending') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
