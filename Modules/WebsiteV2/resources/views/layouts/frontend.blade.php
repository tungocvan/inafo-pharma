<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $websiteV2Settings = app(\Modules\WebsiteV2\Services\SettingsService::class);
        $favicon = $websiteV2Settings->get('site_favicon');
    @endphp
    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
    @endif
    <title>@yield('title', 'HOMEPAGE')</title>
    {!! $websiteV2Settings->get('header_script') !!}
    <script>
        window.CHAT_CONFIG_HOST = "{{ env('NODEJS_SERVER_URL') }}";
        window.CHAT_CONFIG_PORT = "{{ env('NODEJS_SERVER_PORT') ?? 6001 }}";
    </script>
    @vite(['resources/css/tailwind.css', 'resources/js/tailwind.js'])
    @yield('css')
    @stack('styles')
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

    @include('website-v2::partials.header')

    <main class="py-8 w-full flex-grow">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

    @include('website-v2::partials.footer')
    @stack('scripts')
    @livewireScripts
</body>

</html>
