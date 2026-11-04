<x-guest-layout>
    <main class="rm-shell rm-auth-page rm-auth-page--recovery">
        <x-auth.access-card label="Recuperación de contraseña" aria-labelledby="recuperar-titulo" x-data="{ enviando: false }" x-on:pageshow.window="enviando = false">
            <header class="rm-auth-heading">
                <h1 id="recuperar-titulo" class="text-2xl font-bold">Recuperar contraseña</h1>
                <p>Solicita un enlace para crear tu nueva contraseña.</p>
            </header>
            @if(session('status'))
                <div class="rm-auth-feedback rm-auth-feedback--success !block space-y-2" role="status" tabindex="-1" data-recovery-status>
                    <h2 class="font-semibold">Solicitud recibida</h2>
                    <p class="text-sm">{{ session('status') }}</p>
                    <p class="text-sm">Revisa también la carpeta Spam.</p>
                </div>
            @endif
            <form method="POST" action="{{ route('password.email') }}" class="rm-auth-form" x-on:submit="if (enviando) { $event.preventDefault(); } else { enviando = true; }">
                @csrf
                <div>
                    <label for="recuperar-correo" class="rm-label">Correo de tu cuenta</label>
                    <div class="rm-auth-control">
                        <i class="ph ph-envelope-simple rm-auth-control__icon" aria-hidden="true"></i>
                        <input id="recuperar-correo" class="rm-input w-full" type="email" name="correo" placeholder="nombre@correo.com" value="{{ old('correo') }}" maxlength="120" required autofocus autocomplete="username" aria-describedby="recuperar-ayuda @error('correo') recuperar-error @enderror" aria-invalid="{{ $errors->has('correo') ? 'true' : 'false' }}">
                    </div>
                    <p id="recuperar-ayuda" class="rm-help">El correo con el que inicias sesión.</p>
                    @error('correo')<p id="recuperar-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="rm-btn rm-btn-primary w-full" x-bind:disabled="enviando" x-bind:aria-busy="enviando"><span x-show="!enviando">Solicitar instrucciones</span><span x-show="enviando" x-cloak>Procesando solicitud…</span></button>
            </form>
            <a class="rm-auth-link rm-auth-return" href="{{ route('login') }}"><i class="ph ph-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión</a>
            <details class="rm-auth-help">
                <summary>¿No recibes el correo?</summary>
                <ul><li>Revisa tu bandeja de entrada y Spam.</li><li>Espera {{ config('auth.passwords.'.config('fortify.passwords').'.throttle', 60) }} segundos antes de volver a solicitarlo.</li><li>Utiliza el enlace más reciente; dura {{ config('auth.passwords.'.config('fortify.passwords').'.expire', 60) }} minutos.</li><li>Si no recuerdas tu correo, consulta con Administración.</li></ul>
            </details>
        </x-auth.access-card>
    </main>
</x-guest-layout>
