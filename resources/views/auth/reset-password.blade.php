<x-guest-layout>
    <main class="rm-shell rm-auth-page rm-auth-page--recovery">
        <x-auth.access-card label="Nueva contraseña" aria-labelledby="restablecer-titulo" x-data="{ password: '', confirmation: '', mostrar: false, confirmarVisible: false, enviando: false }" x-on:pageshow.window="enviando = false">
            <header class="rm-auth-heading">
                <h1 id="restablecer-titulo" class="text-2xl font-bold">{{ $enlaceValido && !session('password_reset_link_invalid') ? 'Crear una contraseña nueva' : 'Necesitas un enlace nuevo' }}</h1>
            </header>
            @if(!$enlaceValido || session('password_reset_link_invalid'))
                <div class="rm-auth-feedback rm-auth-feedback--info !block space-y-2" role="alert" data-reset-link-invalid>
                    <p>{{ __('passwords.token') }}</p>
                </div>
                <a class="rm-btn rm-btn-primary w-full" href="{{ route('password.request') }}">Solicitar un enlace nuevo</a>
            @else
                <p class="text-sm text-rm-secondary">Elige una contraseña diferente de la anterior.</p>
                <form method="POST" action="{{ route('password.update') }}" class="rm-auth-form" data-reset-password-form x-on:submit="if (enviando) { $event.preventDefault(); } else { enviando = true; }">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                    <div>
                        <label for="reset-correo" class="rm-label">Correo de tu cuenta</label>
                        <input id="reset-correo" type="email" name="correo" value="{{ $correo }}" readonly required autocomplete="username" class="rm-input w-full" @error('correo') aria-describedby="reset-correo-error" @enderror aria-invalid="{{ $errors->has('correo') ? 'true' : 'false' }}">
                        @error('correo')<p id="reset-correo-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="reset-password" class="rm-label">Nueva contraseña</label>
                        <div class="rm-auth-control rm-auth-control--password">
                            <i class="ph ph-lock-simple rm-auth-control__icon" aria-hidden="true"></i>
                            <input id="reset-password" name="password" type="password" x-bind:type="mostrar ? 'text' : 'password'" x-model="password" class="rm-input w-full min-w-0" autocomplete="new-password" required minlength="{{ $politica['min'] }}" maxlength="{{ $politica['max'] }}" aria-describedby="reset-requisitos @error('password') reset-password-error @enderror" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                            <button type="button" class="rm-auth-control__toggle" x-on:click="mostrar = !mostrar" x-bind:aria-pressed="mostrar" aria-controls="reset-password" x-bind:aria-label="mostrar ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'"><i class="ph" x-bind:class="mostrar ? 'ph-eye-slash' : 'ph-eye'" aria-hidden="true"></i></button>
                        </div>
                        @error('password')<p id="reset-password-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
                        @php
                            $requisitos = [];
                            if ($politica['mayuscula']) $requisitos[] = 'mayúscula';
                            if ($politica['minuscula']) $requisitos[] = 'minúscula';
                            if ($politica['numero']) $requisitos[] = 'número';
                            if ($politica['simbolo']) $requisitos[] = 'símbolo';
                        @endphp
                        <p id="reset-requisitos" class="rm-help">Entre {{ $politica['min'] }} y {{ $politica['max'] }} caracteres.@if($requisitos) Incluye {{ implode(', ', $requisitos) }}.@endif</p>
                    </div>
                    <div>
                        <label for="reset-confirmation" class="rm-label">Repite la nueva contraseña</label>
                        <div class="rm-auth-control rm-auth-control--password">
                            <i class="ph ph-lock-simple rm-auth-control__icon" aria-hidden="true"></i>
                            <input id="reset-confirmation" name="password_confirmation" type="password" x-bind:type="confirmarVisible ? 'text' : 'password'" x-model="confirmation" class="rm-input w-full min-w-0" autocomplete="new-password" required maxlength="{{ $politica['max'] }}" aria-describedby="reset-coincidencia @error('password_confirmation') reset-confirmation-error @enderror" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}">
                            <button type="button" class="rm-auth-control__toggle" x-on:click="confirmarVisible = !confirmarVisible" x-bind:aria-pressed="confirmarVisible" aria-controls="reset-confirmation" x-bind:aria-label="confirmarVisible ? 'Ocultar confirmación' : 'Mostrar confirmación'"><i class="ph" x-bind:class="confirmarVisible ? 'ph-eye-slash' : 'ph-eye'" aria-hidden="true"></i></button>
                        </div>
                        <p id="reset-coincidencia" class="rm-help" aria-live="polite" x-show="confirmation.length > 0" x-cloak x-text="password === confirmation ? 'Las contraseñas coinciden.' : 'Las contraseñas todavía no coinciden.'"></p>
                        @error('password_confirmation')<p id="reset-confirmation-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="rm-btn rm-btn-primary w-full" x-bind:disabled="enviando" x-bind:aria-busy="enviando"><span x-show="!enviando">Guardar nueva contraseña</span><span x-show="enviando" x-cloak>Guardando contraseña…</span></button>
                </form>
            @endif
            <a class="rm-auth-link rm-auth-return" href="{{ route('login') }}"><i class="ph ph-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión</a>
        </x-auth.access-card>
    </main>
</x-guest-layout>
