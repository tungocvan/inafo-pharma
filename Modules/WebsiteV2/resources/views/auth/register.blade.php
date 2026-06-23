@extends('website-v2::layouts.frontend')

@section('title', 'Register')

@section('content')
    <div class="max-w-md mx-auto py-12 px-4 sm:px-0">
        @livewire('website-v2.auth.register-form')
    </div>
@endsection
