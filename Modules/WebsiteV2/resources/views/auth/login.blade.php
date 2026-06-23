@extends('website-v2::layouts.frontend')

@section('title', 'Login')

@section('content')
    <div class="max-w-md mx-auto py-12 px-4 sm:px-0">
        @livewire('website-v2.auth.login-form')
    </div>
@endsection
