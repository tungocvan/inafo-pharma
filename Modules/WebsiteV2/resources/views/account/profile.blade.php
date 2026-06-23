@extends('website-v2::layouts.frontend')

@section('title', 'Profile')

@section('content')
    <h1>Profile</h1>
    <p>{{ $profile->email ?? '' }}</p>
@endsection
