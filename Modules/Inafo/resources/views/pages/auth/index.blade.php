@extends('inafo::layouts.frontend')

@section('title', ($mode === 'register' ? 'Dang ky' : 'Dang nhap') . ' - ' . ($siteName ?? config('inafo.inafo.brand_name', 'INAFO Pharma')))

@section('content')
    <div class="min-h-screen bg-[#F8F9FA]">
        @livewire('inafo.auth.auth-page', ['mode' => $mode])
    </div>
@endsection
