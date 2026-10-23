@extends('layouts.site')

@section('hide_cta', '1')

@php
    $settings = app(\App\Settings\ContactSettings::class);
    $whatsapp = $site->whatsappUrl();
    $email = $site->emails()[0] ?? null;
    $mapUrl = $settings->map_lat && $settings->map_lng ? 'https://www.google.com/maps?q='.$settings->map_lat.','.$settings->map_lng : null;
    $embed = $settings->map_embed_url ?: ($settings->map_lat && $settings->map_lng ? 'https://maps.google.com/maps?q='.$settings->map_lat.','.$settings->map_lng.'&z=15&output=embed' : null);
@endphp

@section('content')
    @include('site.partials.page-header', ['title' => __('contact.title'), 'lead' => __('contact.lead'), 'crumbs' => [__('contact.title') => null]])

    <section class="section !pt-10" aria-labelledby="channels-title">
        <div class="container-site">
            <h2 id="channels-title" class="sr-only">{{ __('contact.channels') }}</h2>
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal="stagger">
                @if ($phones = $site->phones())
                    <li class="rounded-[20px] border border-line bg-white p-5">
                        <p class="t-small font-bold text-muted">{{ __('site.call_us') }}</p>
                        @foreach ($phones as $phone)
                            <a href="{{ $phone['href'] }}" class="mt-2 flex min-h-8 items-center gap-2 font-bold text-navy" data-track="call_click"><x-heroicon-m-phone class="size-4 text-sky" aria-hidden="true" /><bdi dir="ltr">{{ $phone['label'] }}</bdi></a>
                        @endforeach
                    </li>
                @endif
                @if ($whatsapp)
                    <li class="rounded-[20px] border border-line bg-white p-5">
                        <p class="t-small font-bold text-muted">{{ __('site.whatsapp') }}</p>
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="mt-2 flex min-h-8 items-center gap-2 font-bold text-navy" data-track="whatsapp_click"><x-heroicon-m-chat-bubble-oval-left class="size-4 text-sky" aria-hidden="true" />{{ __('site.whatsapp_us') }}</a>
                    </li>
                @endif
                @if ($email)
                    <li class="rounded-[20px] border border-line bg-white p-5">
                        <p class="t-small font-bold text-muted">{{ __('site.email') }}</p>
                        <a href="mailto:{{ $email }}" class="mt-2 flex min-h-8 items-center gap-2 font-bold break-all text-navy" data-track="email_click"><x-heroicon-m-envelope class="size-4 shrink-0 text-sky" aria-hidden="true" /><bdi dir="ltr">{{ $email }}</bdi></a>
                    </li>
                @endif
                @if ($site->address() !== '')
                    <li class="rounded-[20px] border border-line bg-white p-5">
                        <p class="t-small font-bold text-muted">{{ __('contact.office') }}</p>
                        <p class="mt-2 text-[14.5px] font-semibold text-ink">{{ $site->address() }}</p>
                        @if ($mapUrl)
                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="text-link mt-2 text-[14px]">{{ __('contact.open_map') }}</a>
                        @endif
                    </li>
                @endif
            </ul>

            <div class="mt-12 grid gap-10 lg:grid-cols-[minmax(0,1fr)_400px]">
                <div>
                    <h2 class="t-section mb-6">{{ __('contact.form_title') }}</h2>
                    <livewire:site.contact-form />
                </div>
                <div class="space-y-6">
                    @if ($embed)
                        <div x-data="{ load: false }" class="relative aspect-[4/3] overflow-hidden rounded-[20px] border border-line bg-paper">
                            <template x-if="load">
                                <iframe src="{{ $embed }}" class="absolute inset-0 size-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('contact.map_title') }}"></iframe>
                            </template>
                            <button type="button" x-show="!load" @click="load = true" class="absolute inset-0 grid place-items-center gap-2 text-navy">
                                <span class="grid justify-items-center gap-2">
                                    <x-heroicon-o-map-pin class="size-8 text-sky" aria-hidden="true" />
                                    <span class="btn btn-secondary btn-sm">{{ __('contact.load_map') }}</span>
                                </span>
                            </button>
                        </div>
                    @endif
                    @if ($hours = $site->workingHours())
                        <div class="rounded-[20px] border border-line bg-white p-5">
                            <h2 class="t-card text-navy">{{ __('site.working_hours') }}</h2>
                            <table class="mt-3 w-full text-[14.5px]">
                                @foreach ($hours as $row)
                                    <tr class="border-t border-line first:border-0">
                                        <th scope="row" class="py-2.5 text-start font-semibold text-muted">{{ $row['days'] }}</th>
                                        <td class="py-2.5 text-end font-bold">{{ $row['hours'] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
