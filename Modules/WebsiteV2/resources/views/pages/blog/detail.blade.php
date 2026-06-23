@extends('website-v2::layouts.frontend')

@section('title', 'Blog')

@section('content')
    @livewire('website-v2.post.post-detail', ['slug' => $slug])
@endsection
