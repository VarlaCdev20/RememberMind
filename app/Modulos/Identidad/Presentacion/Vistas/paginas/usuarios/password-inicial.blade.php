<x-guest-layout>
    <main class="rm-shell rm-auth-page rm-auth-page--recovery">
        <x-auth.access-card label="Activar acceso por correo" aria-labelledby="password-inicial-titulo">
            <h1 id="password-inicial-titulo" class="text-2xl font-bold">Configura tu acceso desde el correo</h1>
            <p class="text-rm-secondary">Tu cuenta tiene pendiente la contraseña inicial. Abre el correo de bienvenida y pulsa «Crear mi contraseña ahora».</p>
            <p class="rm-help">Si ya elegiste una contraseña personal, este paso no debe repetirse. El enlace permite completar la configuración sin cambiar tu rol.</p>
            @error('correo')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
            <form method="POST" action="{{ route('password.inicial.guardar') }}" class="rm-auth-form" x-data="{ enviando: false }" @submit="if(enviando){$event.preventDefault()} else {enviando=true}">
                @csrf
                <button type="submit" class="rm-btn rm-btn-primary w-full" :disabled="enviando"><i class="ph ph-envelope-simple" aria-hidden="true"></i><span x-text="enviando ? 'Solicitando enlace…' : 'Enviar un nuevo enlace a mi correo'">Enviar un nuevo enlace a mi correo</span></button>
                <p class="rm-help">El mensaje se envía únicamente al correo registrado en tu cuenta. Al enviarlo se cierra esta sesión; después entra con la contraseña que elijas desde el correo.</p>
            </form>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="rm-btn rm-btn-secondary w-full" type="submit">Volver al inicio de sesión</button></form>
        </x-auth.access-card>
    </main>
</x-guest-layout>