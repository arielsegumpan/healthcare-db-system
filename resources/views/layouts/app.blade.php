<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="application-name" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        <link rel="icon" href="{{ asset('imgs/csav-logo.png') }}" type="image/png">
        <!-- Fonts -->
        {{-- <link rel="preconnect" href="https://fonts.bunny.net"> --}}
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @vite(['resources/css/app.css'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased dark:bg-neutral-800">
        @include('navigation-menu')
        <main>
            {{ $slot }}
        </main>

         <livewire:partials.footer />

        @stack('modals')

        @livewireScripts
        @vite(['resources/js/app.js'])
    </body>
</html>
