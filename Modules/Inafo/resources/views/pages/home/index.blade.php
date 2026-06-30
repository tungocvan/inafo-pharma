@extends('inafo::layouts.frontend')

@section('title', $siteName ?? config('inafo.inafo.brand_name', 'INAFO Pharma'))

@section('content')
    @livewire('inafo.home.home-page')
@endsection
