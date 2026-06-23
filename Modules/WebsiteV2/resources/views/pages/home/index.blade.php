@extends('website-v2::layouts.frontend')

@section('title', $siteName ?? 'INAFO Pharma V2')

@section('content')
    @livewire('website-v2.home.home-list')
@endsection
