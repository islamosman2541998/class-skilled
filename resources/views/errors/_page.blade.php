@extends('layouts.site')

@section('content')
    @include('errors.partials.message', ['code' => $code])
@endsection
