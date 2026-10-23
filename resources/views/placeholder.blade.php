@extends('layouts.'.$layout)

@section('content')
    <h1 class="font-heading text-page font-extrabold">{{ __('setup.title') }}</h1>
    <dl class="mt-6 space-y-3">
        <div>
            <dt class="font-bold">{{ __('setup.route') }}</dt>
            <dd data-setup-route><bdi dir="ltr">{{ $routeName }}</bdi></dd>
        </div>
        <div>
            <dt class="font-bold">{{ __('setup.locale') }}</dt>
            <dd data-setup-locale>{{ app()->getLocale() }}</dd>
        </div>
        <div>
            <dt class="font-bold">{{ __('setup.direction') }}</dt>
            <dd data-setup-direction>{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}</dd>
        </div>
    </dl>
@endsection
