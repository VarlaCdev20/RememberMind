<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mi perfil · {{ config('app.name', 'RememberMind') }}</title>
    @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-fondo-app font-outfit text-titulo antialiased">
    @php
        $usuario = auth()->user();
        $usuario->loadMissing(['roles', 'personal', 'contactos']);
        $persona = $usuario->personal ?? $usuario->contactos->first();
        $nombre = $persona
            ? trim("{$persona->nombres} {$persona->apellido_paterno} {$persona->apellido_materno}")
            : $usuario->correo;
    @endphp

    <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6">
        <header class="rounded-3xl border border-borde-suave bg-fondo-card p-6 shadow-card">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-boton-acento">RememberMind</p>
                    <h1 class="mt-1 text-2xl font-extrabold">Mi perfil</h1>
                    <p class="mt-1 text-sm font-medium text-meta">{{ $nombre }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($usuario->roles as $rol)
                        <span class="rounded-full bg-fondo-cardSuave px-3 py-1 text-xs font-black text-apoyo">
                            {{ $rol->name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-borde-suave bg-fondo-cardSuave p-4">
                    <dt class="text-xs font-black uppercase tracking-wider text-meta">Correo</dt>
                    <dd class="mt-1 break-all text-sm font-bold">{{ $usuario->correo }}</dd>
                </div>
                <div class="rounded-2xl border border-borde-suave bg-fondo-cardSuave p-4">
                    <dt class="text-xs font-black uppercase tracking-wider text-meta">Estado</dt>
                    <dd class="mt-1 text-sm font-bold">{{ $usuario->estado }}</dd>
                </div>
                <div class="rounded-2xl border border-borde-suave bg-fondo-cardSuave p-4">
                    <dt class="text-xs font-black uppercase tracking-wider text-meta">Código</dt>
                    <dd class="mt-1 text-sm font-bold">{{ $usuario->cod_usuario }}</dd>
                </div>
            </dl>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('dashboard') }}" class="rm-btn-secondary">Volver al panel</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rm-btn-secondary">Cerrar sesión</button>
                </form>
            </div>
        </header>

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <section class="overflow-hidden rounded-3xl border border-borde-suave bg-fondo-card shadow-card">
                @livewire('profile.update-password-form')
            </section>
        @endif

        <section class="overflow-hidden rounded-3xl border border-borde-suave bg-fondo-card shadow-card">
            @livewire('profile.logout-other-browser-sessions-form')
        </section>
    </main>

    @livewireScripts
</body>
</html>
