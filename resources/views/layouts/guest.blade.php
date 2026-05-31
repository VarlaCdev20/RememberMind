<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Acceso institucional al sistema de seguimiento cognitivo del CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.">
        <meta name="robots" content="noindex, nofollow">

        <title>Iniciar Sesión | CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — RememberMind</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">

        <!-- Scripts de la aplicación (Vite compila Inter, Outfit, Alpine, etc.) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Phosphor Icons (igual que welcome.blade.php) -->
        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        <!-- Livewire Styles -->
        @livewireStyles
    </head>
    <body class="antialiased">
        {{ $slot }}

        <x-ui.sweetalert />
        @livewireScripts
    </body>
</html>
