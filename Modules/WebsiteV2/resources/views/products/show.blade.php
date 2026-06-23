@extends('website-v2::layouts.frontend')

@section('title', 'Product detail')

@section('content')
    @livewire('website-v2.products.product-detail', ['slug' => $slug])
@endsection
