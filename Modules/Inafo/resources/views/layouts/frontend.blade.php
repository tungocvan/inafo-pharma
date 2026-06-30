<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('inafo.inafo.brand_name', 'INAFO Pharma'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    @vite(['resources/css/tailwind.css', 'resources/js/tailwind.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen bg-[#F8F9FA] font-['Public_Sans'] text-[#222222] antialiased">
    @yield('content')

    @livewireScripts
    @stack('scripts')
</body>
</html>
