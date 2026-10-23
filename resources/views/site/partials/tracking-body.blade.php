@if (app()->isProduction())
    @php($tracking = app(\App\Settings\TrackingSettings::class))
    @if ($tracking->gtm_id)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ urlencode($tracking->gtm_id) }}" height="0" width="0" style="display:none;visibility:hidden" title=""></iframe></noscript>
    @endif
    {{-- Custom body code entered by an administrator in the dashboard. --}}
    {!! $tracking->body_scripts !!}
@endif
