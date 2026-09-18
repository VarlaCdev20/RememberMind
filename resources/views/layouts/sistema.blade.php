<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RememberMind') }}</title>
    @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-fondo-app font-outfit text-titulo antialiased">
    <header class="border-b border-borde-suave bg-fondo-card">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-black text-boton-acento">RememberMind</a>
            <nav class="flex flex-wrap gap-2 text-sm font-bold">
                <a class="rounded-xl px-3 py-2 hover:bg-fondo-hover" href="{{ route('dashboard') }}">Panel</a>
                @can('residentes.ver')<a class="rounded-xl px-3 py-2 hover:bg-fondo-hover" href="{{ route('admin.residentes.index') }}">Residentes</a>@endcan
                @can('preadmisiones.ver')<a class="rounded-xl px-3 py-2 hover:bg-fondo-hover" href="{{ route('admin.preadmisiones.index') }}">Preadmisiones</a>@endcan
                @can('actividades.ver')<a class="rounded-xl px-3 py-2 hover:bg-fondo-hover" href="{{ route('admin.actividades.index') }}">Actividades</a>@endcan
                @can('alertas.ver')<a class="rounded-xl px-3 py-2 hover:bg-fondo-hover" href="{{ route('admin.alertas.index') }}">Alertas</a>@endcan
            </nav>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('profile.show') }}">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="rm-btn-secondary" type="submit">Salir</button></form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-8">{{ $slot }}</main>
    @livewireScripts
</body>
</html>
