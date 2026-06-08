<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Onboarding' }} &mdash; Tel-U Cup</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#f4f7f6]">
    <main class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mx-auto mb-5 max-w-5xl">
                <x-alert type="success">{{ session('success') }}</x-alert>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-auto mb-5 max-w-5xl">
                <x-alert type="error">{{ session('error') }}</x-alert>
            </div>
        @endif

        @if (session('warning'))
            <div class="mx-auto mb-5 max-w-5xl">
                <x-alert type="warning">{{ session('warning') }}</x-alert>
            </div>
        @endif

        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
