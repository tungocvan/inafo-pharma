@extends('website-v2::layouts.frontend')

@section('title', 'Order Detail')

@section('content')
    <div class="bg-gray-50 min-h-screen py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @livewire('website-v2.account.order-detail', ['code' => $code])
        </div>
    </div>
@endsection
