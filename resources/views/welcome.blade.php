<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RememberMind</title>
    @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])
</head>
<body class="min-h-screen bg-fondo-app text-parrafo antialiased">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-16">
        <section class="w-full rounded-3xl border border-borde bg-fondo-card p-8 shadow-card md:p-12">
            <p class="text-sm font-semibold uppercase tracking-widest text-boton-acento">Centro geriátrico</p>
            <h1 class="mt-3 text-4xl font-bold text-titulo md:text-5xl">RememberMind</h1>
            <p class="mt-5 max-w-2xl text-lg text-apoyo">Gestión institucional, clínica y de cuidados centrada en cada residente.</p>
            <div class="mt-8">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex rounded-xl bg-boton-principal px-5 py-3 font-semibold text-boton-principalTexto">Ir al panel</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex rounded-xl bg-boton-principal px-5 py-3 font-semibold text-boton-principalTexto">Iniciar sesión</a>
                @endauth
            </div>
        </section>
    </main>
</body>
</html>
