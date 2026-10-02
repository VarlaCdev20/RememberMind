<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 <head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <meta name="description" content="Acceso institucional al sistema de seguimiento cognitivo del Centro Geriátrico Los Almendros.">
 <meta name="robots" content="noindex, nofollow">

 <title>Iniciar Sesión | Centro Geriátrico Los Almendros — RememberMind</title>

 <!-- Identidad institucional, sin iconos emoji. -->
 <link rel="icon" type="image/png" href="{{ asset('storage/imagenes/LOGO.png') }}">

 <!-- Scripts de la aplicación (Vite compila Inter, Outfit, Alpine, etc.) -->
 @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])

 <!-- Phosphor Icons (igual que welcome.blade.php) -->
 <script src="https://unpkg.com/@phosphor-icons/web"></script>

 <!-- Livewire Styles -->
 @livewireStyles
 </head>
 <body class="relative min-h-screen overflow-x-hidden bg-[var(--rm-bg-app)] antialiased">
        <div class="rm-texture-dots pointer-events-none fixed inset-0 z-0"></div>
        <div class="rm-mouse-light pointer-events-none fixed inset-0 z-0"></div>
        <div class="relative z-10 min-h-screen">
 {{ $slot }}
        </div>

 <x-ui.sweetalert />
 @livewireScripts
 </body>
</html>
