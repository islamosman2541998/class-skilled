@php
    $labels = [1 => __('quote.step_project'), 2 => __('quote.step_location'), 3 => __('quote.step_files'), 4 => __('quote.step_contact')];
    $options = $this->options;
    $selectedService = $this->services->firstWhere('id', $service_id);
    $whatsapp = app(\App\Services\SiteContext::class)->whatsappUrl();
    $phoneUrl = app(\App\Services\SiteContext::class)->primaryPhoneUrl();
    $input = 'h-12 w-full rounded-[12px] border bg-white px-4 text-[15px] focus:border-sky focus:outline-none';
@endphp
<div>
    @if ($number)
        {{-- Success screen --}}
        <div class="mx-auto max-w-2xl rounded-[20px] border border-line bg-white p-8 text-center lg:p-12" role="status">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-sky text-deep"><x-heroicon-o-check class="size-7" aria-hidden="true" /></span>
            <h2 class="t-section mt-5">{{ __('quote.success_title') }}</h2>
            <p class="mt-3 font-heading text-[18px] font-bold text-navy"><bdi dir="ltr">{{ __('quote.success_number', ['number' => $number]) }}</bdi></p>
            <p class="t-lead mx-auto mt-3">{{ __('quote.success_next') }}</p>
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-primary">{{ __('site.whatsapp_us') }}</a>
                @endif
                <a href="{{ app(\App\Services\SiteContext::class)->homeUrl() }}" class="btn btn-secondary">{{ __('site.back_home') }}</a>
            </div>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-10">
            <div class="min-w-0">
                {{-- Stepper --}}
                <ol class="mb-6 grid grid-cols-4 gap-2" aria-label="{{ __('quote.step', ['current' => $step, 'total' => 4]) }}">
                    @foreach ($labels as $n => $label)
                        <li>
                            <button type="button" @if ($n < $step) wire:click="goTo({{ $n }})" @else disabled @endif class="group flex w-full flex-col items-start gap-2 text-start" @if ($n === $step) aria-current="step" @endif>
                                <span @class(['h-1.5 w-full rounded-full', 'bg-sky' => $n <= $step, 'bg-line' => $n > $step])></span>
                                <span class="flex items-center gap-2">
                                    <span @class([
                                        'grid size-7 shrink-0 place-items-center rounded-full text-[13px] font-extrabold',
                                        'bg-navy text-white' => $n < $step,
                                        'bg-sky text-deep' => $n === $step,
                                        'bg-paper text-muted' => $n > $step,
                                    ])>
                                        @if ($n < $step)<x-heroicon-m-check class="size-4" aria-hidden="true" />@else{{ $n }}@endif
                                    </span>
                                    <span @class(['hidden text-[13.5px] font-bold sm:inline', 'text-navy' => $n <= $step, 'text-muted' => $n > $step])>{{ $label }}</span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ol>
                <p class="t-small mb-4 text-muted sm:hidden">{{ __('quote.step', ['current' => $step, 'total' => 4]) }} — <span class="font-bold text-navy">{{ $labels[$step] }}</span></p>

                <form wire:submit="{{ $step < 4 ? 'next' : 'submit' }}" class="rounded-[20px] border border-line bg-white p-5 sm:p-7" novalidate>
                    {{-- Honeypot --}}
                    <div class="absolute -start-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                        <label>{{ __('forms.optional') }} <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    @error('form')
                        <p class="mb-5 rounded-[12px] bg-[#fdecea] px-4 py-3 text-[14px] font-semibold text-[#b42318]" role="alert">{{ $message }}</p>
                    @enderror

                    @if ($step === 1)
                        <fieldset>
                            <legend class="t-card text-navy">{{ __('quote.service') }} <span class="text-[#b42318]" aria-hidden="true">*</span></legend>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($this->services as $service)
                                    <label class="cursor-pointer">
                                        <input type="radio" class="peer sr-only" wire:model.live="service_id" value="{{ $service->id }}" name="service_id">
                                        <span class="chip peer-checked:border-navy peer-checked:bg-navy peer-checked:text-white peer-focus-visible:outline-3 peer-focus-visible:outline-sky">{{ $service->title }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('service_id') <p class="mt-2 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                        </fieldset>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            @foreach (['facility_type' => 'facility_types', 'project_state' => 'project_states'] as $field => $list)
                                <div>
                                    <label for="q-{{ $field }}" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('quote.'.$field) }} <span class="text-[#b42318]" aria-hidden="true">*</span></label>
                                    <select id="q-{{ $field }}" wire:model="{{ $field }}" @class([$input, 'border-[#b42318]' => $errors->has($field), 'border-line' => ! $errors->has($field)]) @error($field) aria-invalid="true" aria-describedby="q-{{ $field }}-error" @enderror>
                                        <option value="">{{ __('forms.select') }}</option>
                                        @foreach ($options[$list] as $i => $label)
                                            <option value="{{ $i }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error($field) <p id="q-{{ $field }}-error" class="mt-1.5 text-[13.5px] text-[#b42318]">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5">
                            <label for="q-description" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('quote.description') }} <span class="font-normal text-muted">({{ __('forms.optional') }})</span></label>
                            <textarea id="q-description" wire:model="description" rows="4" maxlength="2000" class="w-full rounded-[12px] border border-line bg-white px-4 py-3 text-[15px] focus:border-sky focus:outline-none" aria-describedby="q-description-hint"></textarea>
                            <p id="q-description-hint" class="t-small mt-1 text-muted">{{ __('quote.description_hint') }}</p>
                        </div>
                    @elseif ($step === 2)
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="q-emirate" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('quote.emirate') }} <span class="text-[#b42318]" aria-hidden="true">*</span></label>
                                <select id="q-emirate" wire:model.live="emirate" @class([$input, 'border-[#b42318]' => $errors->has('emirate'), 'border-line' => ! $errors->has('emirate')])>
                                    <option value="">{{ __('forms.select') }}</option>
                                    @foreach ($options['emirates'] as $i => $label)
                                        <option value="{{ $i }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('emirate') <p class="mt-1.5 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="q-area-text" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('quote.area_text') }}</label>
                                <input id="q-area-text" type="text" wire:model.blur="area_text" maxlength="150" class="{{ $input }} border-line">
                            </div>
                            <div>
                                <label for="q-area" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('quote.area_m2') }}</label>
                                <input id="q-area" type="number" inputmode="decimal" min="1" step="any" dir="ltr" wire:model.blur="area_m2" @disabled($area_unknown) @class([$input, 'text-start disabled:bg-paper', 'border-[#b42318]' => $errors->has('area_m2'), 'border-line' => ! $errors->has('area_m2')])>
                                <label class="mt-2 inline-flex min-h-11 cursor-pointer items-center gap-2 text-[14px]">
                                    <input type="checkbox" wire:model.live="area_unknown" class="size-4 accent-[var(--c-navy)]"> {{ __('quote.area_unknown') }}
                                </label>
                                @error('area_m2') <p class="mt-1 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="q-start" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('quote.start_when') }} <span class="text-[#b42318]" aria-hidden="true">*</span></label>
                                <select id="q-start" wire:model.live="start_when" @class([$input, 'border-[#b42318]' => $errors->has('start_when'), 'border-line' => ! $errors->has('start_when')])>
                                    <option value="">{{ __('forms.select') }}</option>
                                    @foreach ($options['start_options'] as $i => $label)
                                        <option value="{{ $i }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('start_when') <p class="mt-1.5 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @elseif ($step === 3)
                        <div
                            x-data="{ over: false, progress: 0, uploading: false }"
                            x-on:livewire-upload-start="uploading = true; progress = 0"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                            @dragover.prevent="over = true" @dragleave.prevent="over = false"
                            @drop.prevent="over = false; $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change', { bubbles: true }))"
                        >
                            <label for="q-files" class="flex cursor-pointer flex-col items-center gap-3 rounded-[16px] border-2 border-dashed px-5 py-10 text-center transition" :class="over ? 'border-sky bg-sky/10' : 'border-line bg-paper'">
                                <x-heroicon-o-arrow-up-tray class="size-8 text-navy" aria-hidden="true" />
                                <span class="max-w-md text-[14.5px] text-ink">{{ __('quote.files_hint', ['types' => strtoupper(implode(', ', $settings->allowed_extensions)), 'max' => $settings->max_upload_mb]) }}</span>
                                <span class="btn btn-secondary btn-sm">{{ __('quote.files_choose') }}</span>
                                <input id="q-files" x-ref="input" type="file" multiple class="sr-only" wire:model="newFiles" accept="{{ collect($settings->allowed_extensions)->map(fn ($e) => '.'.$e)->implode(',') }}">
                            </label>
                            <div x-show="uploading" x-cloak class="mt-4" role="status">
                                <p class="t-small mb-1 text-muted">{{ __('forms.uploading') }} <bdi x-text="progress + '%'"></bdi></p>
                                <div class="h-2 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full bg-sky transition-all" :style="`width: ${progress}%`"></div></div>
                            </div>
                            @error('newFiles.*') <p class="mt-3 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                            @error('files') <p class="mt-3 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror

                            @if ($files)
                                <ul class="mt-5 divide-y divide-line rounded-[14px] border border-line">
                                    @foreach ($files as $i => $file)
                                        <li class="flex items-center gap-3 px-4 py-3" wire:key="file-{{ $i }}-{{ $file->getFilename() }}">
                                            <span class="grid h-9 w-11 shrink-0 place-items-center rounded-[8px] bg-paper text-[11px] font-extrabold text-navy">{{ strtoupper($file->getClientOriginalExtension()) }}</span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-[14px] font-semibold" dir="ltr">{{ $file->getClientOriginalName() }}</span>
                                                <span class="t-small text-muted"><bdi dir="ltr">{{ number_format($file->getSize() / 1048576, 1) }} MB</bdi></span>
                                            </span>
                                            <button type="button" class="grid size-10 place-items-center rounded-full text-muted hover:bg-paper hover:text-[#b42318]" wire:click="removeFile({{ $i }})" aria-label="{{ __('quote.file_remove') }}: {{ $file->getClientOriginalName() }}">
                                                <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <p class="t-small mt-4 text-muted">{{ __('quote.files_optional') }}</p>
                        </div>
                    @else
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="q-name" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('forms.name') }} <span class="text-[#b42318]" aria-hidden="true">*</span></label>
                                <input id="q-name" type="text" autocomplete="name" wire:model.blur="name" maxlength="120" @class([$input, 'border-[#b42318]' => $errors->has('name'), 'border-line' => ! $errors->has('name')])>
                                @error('name') <p class="mt-1.5 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="q-phone" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('forms.phone') }} <span class="text-[#b42318]" aria-hidden="true">*</span></label>
                                <input id="q-phone" type="tel" inputmode="tel" autocomplete="tel" dir="ltr" placeholder="{{ __('forms.phone_hint') }}" wire:model.blur="phone" maxlength="25" @class([$input, 'text-start', 'border-[#b42318]' => $errors->has('phone'), 'border-line' => ! $errors->has('phone')])>
                                @error('phone') <p class="mt-1.5 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="q-email" class="mb-1.5 block text-[14px] font-bold text-ink">{{ __('forms.email_optional') }}</label>
                                <input id="q-email" type="email" inputmode="email" autocomplete="email" dir="ltr" wire:model.blur="email" maxlength="150" @class([$input, 'text-start', 'border-[#b42318]' => $errors->has('email'), 'border-line' => ! $errors->has('email')])>
                                @error('email') <p class="mt-1.5 text-[13.5px] text-[#b42318]" role="alert">{{ $message }}</p> @enderror
                            </div>
                            <fieldset class="sm:col-span-2">
                                <legend class="mb-2 text-[14px] font-bold text-ink">{{ __('quote.preferred_contact') }}</legend>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['call' => __('quote.prefer_call'), 'whatsapp' => __('quote.prefer_whatsapp')] as $value => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" class="peer sr-only" wire:model="preferred_contact" value="{{ $value }}" name="preferred_contact">
                                            <span class="chip peer-checked:border-navy peer-checked:bg-navy peer-checked:text-white">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>
                        @if ($siteKey = \App\Support\Turnstile::siteKey())
                            @include('site.partials.turnstile', ['siteKey' => $siteKey])
                        @endif
                        <p class="t-small mt-5 text-muted">{{ __('quote.consent') }} {{ __('messages.privacy_note') }}</p>
                    @endif

                    {{-- Step buttons (sticky above the mobile bottom bar) --}}
                    <div class="sticky bottom-[84px] z-10 -mx-5 mt-7 flex items-center justify-between gap-3 border-t border-line bg-white/95 px-5 pt-4 pb-1 backdrop-blur sm:static sm:mx-0 sm:bg-transparent sm:px-0 lg:bottom-0">
                        @if ($step > 1)
                            <button type="button" class="btn btn-secondary" wire:click="back">{{ __('quote.back') }}</button>
                        @else
                            <span></span>
                        @endif
                        <button type="submit" class="btn btn-primary min-w-36" wire:loading.attr="disabled" wire:target="next, submit, newFiles">
                            <span wire:loading.remove wire:target="next, submit">{{ $step < 4 ? __('quote.next') : __('quote.submit') }}</span>
                            <span wire:loading wire:target="next, submit">{{ __('forms.sending') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                @if ($step >= 2)
                    <div class="rounded-[20px] border border-line bg-white p-5">
                        <h2 class="t-card text-navy">{{ __('quote.summary') }}</h2>
                        <dl class="mt-3 space-y-2 text-[14px]">
                            @foreach (array_filter([
                                __('quote.service') => $selectedService?->title,
                                __('quote.facility_type') => $facility_type !== '' ? ($options['facility_types'][(int) $facility_type] ?? null) : null,
                                __('quote.project_state') => $project_state !== '' ? ($options['project_states'][(int) $project_state] ?? null) : null,
                                __('quote.emirate') => $emirate !== '' ? ($options['emirates'][(int) $emirate] ?? null) : null,
                                __('quote.area_m2') => $area_unknown ? __('quote.area_unknown') : ($area_m2 !== '' ? $area_m2 : null),
                                __('quote.files') => $files ? count($files) : null,
                            ]) as $label => $value)
                                <div class="flex justify-between gap-3"><dt class="text-muted">{{ $label }}</dt><dd class="text-end font-bold"><bdi>{{ $value }}</bdi></dd></div>
                            @endforeach
                        </dl>
                    </div>
                @endif
                <div class="on-dark rounded-[20px] bg-deep p-5 text-[#C9D3E3]">
                    <h2 class="t-card text-white">{{ __('quote.direct_contact') }}</h2>
                    <div class="mt-4 grid gap-2">
                        @if ($whatsapp)
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-primary">{{ __('site.whatsapp_us') }}</a>
                        @endif
                        @if ($phoneUrl)
                            <a href="{{ $phoneUrl }}" class="btn btn-ghost-light">{{ __('site.call_us') }}</a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
