<x-guest-layout>
    <main class="rm-shell rm-auth-page rm-auth-page--recovery">
        <x-auth.access-card label="Actualizar contraseña temporal" aria-labelledby="password-inicial-titulo">
            <h1 id="password-inicial-titulo" class="text-2xl font-bold">Crea tu contraseña personal</h1>
            <p class="text-rm-secondary">Tu cuenta está activa. Antes de acceder a los módulos, reemplaza la contraseña temporal que recibiste.</p>
            <form method="POST" action="{{ route('password.inicial.guardar') }}" class="rm-auth-form" x-data="{ enviando: false }" @submit="if(enviando){$event.preventDefault()} else {enviando=true}">
                @csrf
                <div><label class="rm-label" for="password-temporal">Contraseña temporal actual</label><input id="password-temporal" class="rm-input w-full" name="current_password" type="password" required autocomplete="current-password">@error('current_password','updatePassword')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</div>
                <div><label class="rm-label" for="password-personal">Nueva contraseña</label><input id="password-personal" class="rm-input w-full" name="password" type="password" minlength="{{ $politica['min'] }}" maxlength="{{ $politica['max'] }}" autocomplete="new-password" required aria-describedby="password-personal-ayuda"><p id="password-personal-ayuda" class="rm-help">Entre {{ $politica['min'] }} y {{ $politica['max'] }} caracteres, con mayúscula, minúscula, número y símbolo. Debe ser diferente de la temporal.</p>@error('password','updatePassword')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</div>
                <div><label class="rm-label" for="password-personal-confirmacion">Confirma la nueva contraseña</label><input id="password-personal-confirmacion" class="rm-input w-full" name="password_confirmation" type="password" autocomplete="new-password" required></div>
                <button type="submit" class="rm-btn-primary w-full" :disabled="enviando"><span x-text="enviando ? 'Actualizando…' : 'Actualizar y continuar'">Actualizar y continuar</span></button>
            </form>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="rm-btn-secondary w-full" type="submit">Cerrar sesión</button></form>
        </x-auth.access-card>
    </main>
</x-guest-layout>
