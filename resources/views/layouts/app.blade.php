<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">

 <title>{{ config('app.name', 'RememberMind') }}</title>


 <script src="https://unpkg.com/@phosphor-icons/web"></script>

 @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])

 @livewireStyles
</head>

<body class="relative min-h-screen overflow-x-hidden bg-[var(--rm-bg-app)] antialiased">
    <div class="rm-texture-dots pointer-events-none fixed inset-0 z-0"></div>
    <div class="rm-mouse-light pointer-events-none fixed inset-0 z-0"></div>
    <div class="relative z-10 min-h-screen">
 <x-banner />

 <div class="min-h-screen">
 <main>
 {{ $slot }}
 </main>
 </div>

 <x-ui.sweetalert />

 @stack('modals')

 @livewireScripts
</body>
</html>
