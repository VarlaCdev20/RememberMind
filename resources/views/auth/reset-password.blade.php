@php
    /*
    |--------------------------------------------------------------------------
    | Política de contraseña
    |--------------------------------------------------------------------------
    |
    | Si ya existe passwordPolicy() en nuestra Action, usamos esa configuración.
    | El fallback permite que la vista siga funcionando mientras se sincroniza
    | el backend.
    |
    */

    $passwordAction =
        \App\Actions\Identidad\Fortify\ResetUserPassword::class;

    $policySource =
        \App\Actions\Identidad\Fortify\UpdateUserPassword::class;

    $politica = method_exists(
        $policySource,
        'passwordPolicy'
    )
        ? $policySource::passwordPolicy()
        : [
            'min' => 12,
            'max' => 64,
            'mayuscula' => true,
            'minuscula' => true,
            'numero' => true,
            'simbolo' => true,
        ];

    $correo =
        old(
            'correo',
            $request->correo
        );
@endphp


<x-guest-layout>

    <main class="
            min-h-screen
            bg-fondo-panel
            px-4
            py-8
            sm:px-6
        ">

        <div class="
                mx-auto
                flex
                min-h-[calc(100vh-4rem)]
                w-full
                max-w-lg
                items-center
                justify-center
            ">

            <section x-data="{
                    /*
                    |--------------------------------------------------------------------------
                    | Política
                    |--------------------------------------------------------------------------
                    */

                    politica:
                        @js($politica),


                    /*
                    |--------------------------------------------------------------------------
                    | Campos
                    |--------------------------------------------------------------------------
                    */

                    password: '',

                    confirmation: '',


                    /*
                    |--------------------------------------------------------------------------
                    | Estado visual
                    |--------------------------------------------------------------------------
                    */

                    mostrarPassword: false,

                    mostrarConfirmation: false,

                    capsLock: false,

                    enviando: false,


                    /*
                    |--------------------------------------------------------------------------
                    | Comprobaciones
                    |--------------------------------------------------------------------------
                    */

                    get longitud() {
                        return [
                            ...this.password
                        ].length;
                    },

                    get cumpleLongitud() {
                        return (
                            this.longitud
                            >= this.politica.min
                            &&
                            this.longitud
                            <= this.politica.max
                        );
                    },

                    get cumpleMayuscula() {
                        return (
                            !this.politica.mayuscula
                            ||
                            /\p{Lu}/u.test(
                                this.password
                            )
                        );
                    },

                    get cumpleMinuscula() {
                        return (
                            !this.politica.minuscula
                            ||
                            /\p{Ll}/u.test(
                                this.password
                            )
                        );
                    },

                    get cumpleNumero() {
                        return (
                            !this.politica.numero
                            ||
                            /\p{N}/u.test(
                                this.password
                            )
                        );
                    },

                    get cumpleSimbolo() {
                        return (
                            !this.politica.simbolo
                            ||
                            /[^\p{L}\p{N}\s]/u.test(
                                this.password
                            )
                        );
                    },

                    get confirmacionCoincide() {
                        return (
                            this.confirmation.length > 0
                            &&
                            this.password
                            === this.confirmation
                        );
                    },

                    get passwordValida() {
                        return (
                            this.cumpleLongitud
                            &&
                            this.cumpleMayuscula
                            &&
                            this.cumpleMinuscula
                            &&
                            this.cumpleNumero
                            &&
                            this.cumpleSimbolo
                        );
                    },

                    get puedeEnviar() {
                        return (
                            this.passwordValida
                            &&
                            this.confirmacionCoincide
                            &&
                            !this.enviando
                        );
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Fortaleza estructural
                    |--------------------------------------------------------------------------
                    */

                    get reglasCumplidas() {
                        return [
                            this.cumpleLongitud,
                            this.cumpleMayuscula,
                            this.cumpleMinuscula,
                            this.cumpleNumero,
                            this.cumpleSimbolo
                        ].filter(Boolean).length;
                    },

                    get progreso() {
                        if (
                            this.password.length === 0
                        ) {
                            return 0;
                        }

                        return Math.round(
                            (
                                this.reglasCumplidas
                                / 5
                            ) * 100
                        );
                    },

                    get estadoPassword() {
                        if (
                            this.password.length === 0
                        ) {
                            return 'Sin evaluar';
                        }

                        if (
                            this.reglasCumplidas <= 2
                        ) {
                            return 'Muy débil';
                        }

                        if (
                            this.reglasCumplidas === 3
                        ) {
                            return 'Débil';
                        }

                        if (
                            this.reglasCumplidas === 4
                        ) {
                            return 'Aceptable';
                        }

                        return 'Segura';
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Bloq Mayús
                    |--------------------------------------------------------------------------
                    */

                    detectarCapsLock(event) {
                        if (
                            typeof event.getModifierState
                            !== 'function'
                        ) {
                            return;
                        }

                        this.capsLock =
                            event.getModifierState(
                                'CapsLock'
                            );
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Envío
                    |--------------------------------------------------------------------------
                    */

                    enviar(event) {
                        if (
                            !this.puedeEnviar
                        ) {
                            event.preventDefault();

                            return;
                        }

                        this.enviando =
                            true;
                    }
                }" class="
                    w-full
                    overflow-hidden
                    rounded-[1.6rem]
                    border
                    border-borde-suave
                    bg-fondo-card
                    shadow-card
                ">

                {{-- ========================================================
                CABECERA
                ========================================================= --}}
                <header class="
                        border-b
                        border-borde-suave
                        px-6
                        py-6
                        text-center
                    ">

                    <div class="
                            mx-auto
                            flex
                            h-12
                            w-12
                            items-center
                            justify-center
                            rounded-2xl
                            bg-fondo-cardSuave
                            text-boton-acento
                        ">
                        <i class="
                                ph-bold
                                ph-key
                                text-2xl
                            "></i>
                    </div>


                    <h1 class="
                            mt-3
                            font-outfit
                            text-xl
                            font-extrabold
                            text-titulo
                        ">
                        Crear nueva contraseña
                    </h1>


                    <p class="
                            mx-auto
                            mt-1
                            max-w-md
                            text-sm
                            font-medium
                            leading-6
                            text-meta
                        ">
                        Define una nueva contraseña segura
                        para recuperar el acceso a RememberMind.
                    </p>

                </header>


                {{-- ========================================================
                ERRORES
                ========================================================= --}}
                @if ($errors->any())

                    <div class="
                                mx-6
                                mt-5
                                flex
                                items-start
                                gap-3
                                rounded-xl
                                border
                                border-estado-peligroBorde
                                bg-estado-peligroBg
                                p-3
                            ">

                        <i class="
                                    ph-bold
                                    ph-warning-circle
                                    mt-0.5
                                    shrink-0
                                    text-estado-peligro
                                "></i>


                        <div>

                            <p class="
                                        text-xs
                                        font-black
                                        text-estado-peligro
                                    ">
                                No pudimos restablecer la contraseña
                            </p>


                            <ul class="
                                        mt-1
                                        space-y-1
                                        text-xs
                                        font-medium
                                        leading-5
                                        text-meta
                                    ">

                                @foreach (
                                        $errors->all()
                                        as $error
                                    )

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                @endif


                {{-- ========================================================
                FORMULARIO
                ========================================================= --}}
                <form method="POST" action="{{
    route(
        'password.update'
    )
                    }}" x-on:submit="
                        enviar($event)
                    ">
                    @csrf


                    {{-- Token --}}
                    <input type="hidden" name="token" value="{{
    $request
        ->route(
            'token'
        )
                        }}">


                    <div class="
                            space-y-5
                            px-6
                            py-6
                        ">

                        {{-- =================================================
                        CORREO
                        ================================================== --}}
                        <div>

                            <label for="correo" class="rm-label">
                                Correo electrónico
                            </label>


                            <div class="
                                    relative
                                    mt-1
                                ">

                                <input id="correo" type="email" name="correo" value="{{
    $correo
                                    }}" readonly required autocomplete="username" class="
                                        rm-input
                                        !mt-0
                                        cursor-default
                                        pr-11
                                        opacity-80
                                    ">


                                <div class="
                                        pointer-events-none
                                        absolute
                                        inset-y-0
                                        right-0
                                        flex
                                        w-11
                                        items-center
                                        justify-center
                                        text-meta
                                    ">
                                    <i class="
                                            ph-bold
                                            ph-lock-simple
                                        "></i>
                                </div>

                            </div>


                            <p class="
                                    mt-2
                                    flex
                                    items-start
                                    gap-1.5
                                    text-[11px]
                                    font-medium
                                    leading-5
                                    text-meta
                                ">
                                <i class="
                                        ph-bold
                                        ph-info
                                        mt-0.5
                                        shrink-0
                                    "></i>

                                El enlace de recuperación
                                pertenece a esta cuenta.
                            </p>

                        </div>


                        {{-- =================================================
                        NUEVA CONTRASEÑA
                        ================================================== --}}
                        <div>

                            <div class="
                                    flex
                                    items-center
                                    justify-between
                                    gap-3
                                ">

                                <label for="password" class="rm-label">
                                    Nueva contraseña

                                    <span class="
                                            text-estado-peligro
                                        ">
                                        *
                                    </span>
                                </label>


                                <span class="
                                        text-[11px]
                                        font-bold
                                        text-meta
                                    ">
                                    <span x-text="
                                            longitud
                                        "></span>

                                    /
                                    <span>
                                        {{
    $politica['max']
                                        }}
                                    </span>
                                </span>

                            </div>


                            <div class="
                                    relative
                                    mt-1
                                ">

                                <input id="password" name="password" :type="
                                        mostrarPassword
                                            ? 'text'
                                            : 'password'
                                    " x-model="
                                        password
                                    " x-on:keydown="
                                        detectarCapsLock(
                                            $event
                                        )
                                    " x-on:keyup="
                                        detectarCapsLock(
                                            $event
                                        )
                                    " x-on:blur="
                                        capsLock = false
                                    " minlength="{{
    $politica['min']
                                    }}" maxlength="{{
    $politica['max']
                                    }}" required autocomplete="new-password" class="
                                        rm-input
                                        !mt-0
                                        pr-12
                                    " placeholder="
                                        Crea una contraseña segura
                                    ">


                                <button type="button" x-on:click="
                                        mostrarPassword =
                                            !mostrarPassword
                                    " class="
                                        absolute
                                        inset-y-0
                                        right-0
                                        flex
                                        w-11
                                        items-center
                                        justify-center
                                        text-meta
                                        transition-colors
                                        hover:text-boton-acento
                                    " :aria-label="
                                        mostrarPassword
                                            ? 'Ocultar contraseña'
                                            : 'Mostrar contraseña'
                                    ">

                                    <i class="ph-bold" :class="
                                            mostrarPassword
                                                ? 'ph-eye-slash'
                                                : 'ph-eye'
                                        "></i>

                                </button>

                            </div>


                            {{-- Caps Lock --}}
                            <div x-cloak x-show="
                                    capsLock
                                " x-transition class="
                                    mt-2
                                    flex
                                    items-center
                                    gap-2
                                    rounded-lg
                                    border
                                    border-estado-advertenciaBorde
                                    bg-estado-advertenciaBg
                                    px-3
                                    py-2
                                    text-xs
                                    font-bold
                                    text-estado-advertencia
                                ">

                                <i class="
                                        ph-bold
                                        ph-arrow-fat-line-up
                                    "></i>

                                Bloq Mayús está activado.

                            </div>


                            {{-- =================================================
                            FORTALEZA
                            ================================================== --}}
                            <div class="
                                    mt-3
                                ">

                                <div class="
                                        flex
                                        items-center
                                        justify-between
                                        gap-3
                                    ">

                                    <span class="
                                            text-[11px]
                                            font-semibold
                                            text-meta
                                        ">
                                        Seguridad de la contraseña
                                    </span>


                                    <span class="
                                            text-[11px]
                                            font-black
                                        " :class="
                                            passwordValida
                                                ? 'text-estado-exito'
                                                : 'text-meta'
                                        " x-text="
                                            estadoPassword
                                        "></span>

                                </div>


                                <div class="
                                        mt-2
                                        h-1.5
                                        overflow-hidden
                                        rounded-full
                                        bg-fondo-panel
                                    ">

                                    <div class="
                                            h-full
                                            rounded-full
                                            bg-boton-acento
                                            transition-all
                                            duration-300
                                        " :class="
                                            passwordValida
                                                ? 'bg-estado-exito'
                                                : 'bg-boton-acento'
                                        " :style="
                                            `width:
                                            ${progreso}%`
                                        "></div>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                        REQUISITOS
                        ================================================== --}}
                        <div class="
                                rounded-2xl
                                border
                                border-borde-suave
                                bg-fondo-cardSuave
                                p-4
                            ">

                            <p class="
                                    text-xs
                                    font-black
                                    text-titulo
                                ">
                                La contraseña debe contener
                            </p>


                            <div class="
                                    mt-3
                                    grid
                                    gap-2
                                    sm:grid-cols-2
                                ">

                                {{-- Longitud --}}
                                <div class="
                                        flex
                                        items-center
                                        gap-2
                                        text-xs
                                        font-semibold
                                    " :class="
                                        cumpleLongitud
                                            ? 'text-estado-exito'
                                            : 'text-meta'
                                    ">

                                    <i class="ph-bold" :class="
                                            cumpleLongitud
                                                ? 'ph-check-circle'
                                                : 'ph-circle'
                                        "></i>

                                    {{
    $politica['min']
                                    }}–{{
    $politica['max']
                                    }} caracteres

                                </div>


                                {{-- Mayúscula --}}
                                <div class="
                                        flex
                                        items-center
                                        gap-2
                                        text-xs
                                        font-semibold
                                    " :class="
                                        cumpleMayuscula
                                            ? 'text-estado-exito'
                                            : 'text-meta'
                                    ">

                                    <i class="ph-bold" :class="
                                            cumpleMayuscula
                                                ? 'ph-check-circle'
                                                : 'ph-circle'
                                        "></i>

                                    Una mayúscula

                                </div>


                                {{-- Minúscula --}}
                                <div class="
                                        flex
                                        items-center
                                        gap-2
                                        text-xs
                                        font-semibold
                                    " :class="
                                        cumpleMinuscula
                                            ? 'text-estado-exito'
                                            : 'text-meta'
                                    ">

                                    <i class="ph-bold" :class="
                                            cumpleMinuscula
                                                ? 'ph-check-circle'
                                                : 'ph-circle'
                                        "></i>

                                    Una minúscula

                                </div>


                                {{-- Número --}}
                                <div class="
                                        flex
                                        items-center
                                        gap-2
                                        text-xs
                                        font-semibold
                                    " :class="
                                        cumpleNumero
                                            ? 'text-estado-exito'
                                            : 'text-meta'
                                    ">

                                    <i class="ph-bold" :class="
                                            cumpleNumero
                                                ? 'ph-check-circle'
                                                : 'ph-circle'
                                        "></i>

                                    Un número

                                </div>


                                {{-- Símbolo --}}
                                <div class="
                                        flex
                                        items-center
                                        gap-2
                                        text-xs
                                        font-semibold
                                    " :class="
                                        cumpleSimbolo
                                            ? 'text-estado-exito'
                                            : 'text-meta'
                                    ">

                                    <i class="ph-bold" :class="
                                            cumpleSimbolo
                                                ? 'ph-check-circle'
                                                : 'ph-circle'
                                        "></i>

                                    Un símbolo

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                        CONFIRMACIÓN
                        ================================================== --}}
                        <div>

                            <label for="password_confirmation" class="rm-label">
                                Confirmar nueva contraseña

                                <span class="
                                        text-estado-peligro
                                    ">
                                    *
                                </span>
                            </label>


                            <div class="
                                    relative
                                    mt-1
                                ">

                                <input id="password_confirmation" name="password_confirmation" :type="
                                        mostrarConfirmation
                                            ? 'text'
                                            : 'password'
                                    " x-model="
                                        confirmation
                                    " maxlength="{{
    $politica['max']
                                    }}" required autocomplete="new-password" class="
                                        rm-input
                                        !mt-0
                                        pr-12
                                    " placeholder="
                                        Repite la nueva contraseña
                                    ">


                                <button type="button" x-on:click="
                                        mostrarConfirmation =
                                            !mostrarConfirmation
                                    " class="
                                        absolute
                                        inset-y-0
                                        right-0
                                        flex
                                        w-11
                                        items-center
                                        justify-center
                                        text-meta
                                        transition-colors
                                        hover:text-boton-acento
                                    " :aria-label="
                                        mostrarConfirmation
                                            ? 'Ocultar confirmación'
                                            : 'Mostrar confirmación'
                                    ">

                                    <i class="ph-bold" :class="
                                            mostrarConfirmation
                                                ? 'ph-eye-slash'
                                                : 'ph-eye'
                                        "></i>

                                </button>

                            </div>


                            {{-- Match --}}
                            <div x-show="
                                    confirmation.length > 0
                                " class="
                                    mt-2
                                    flex
                                    items-center
                                    gap-2
                                    text-xs
                                    font-bold
                                " :class="
                                    confirmacionCoincide
                                        ? 'text-estado-exito'
                                        : 'text-estado-peligro'
                                ">

                                <i class="ph-bold" :class="
                                        confirmacionCoincide
                                            ? 'ph-check-circle'
                                            : 'ph-x-circle'
                                    "></i>


                                <span x-text="
                                        confirmacionCoincide
                                            ? 'Las contraseñas coinciden'
                                            : 'Las contraseñas no coinciden'
                                    "></span>

                            </div>

                        </div>


                        {{-- =================================================
                        BOTÓN
                        ================================================== --}}
                        <button type="submit" :disabled="
                                !puedeEnviar
                            " class="
                                rm-btn-accent
                                w-full
                                justify-center
                                py-3
                                disabled:cursor-not-allowed
                                disabled:opacity-50
                            ">

                            <span x-show="
                                    !enviando
                                " class="
                                    inline-flex
                                    items-center
                                    gap-2
                                ">

                                <i class="
                                        ph-bold
                                        ph-key
                                    "></i>

                                Restablecer contraseña

                            </span>


                            <span x-cloak x-show="
                                    enviando
                                " class="
                                    inline-flex
                                    items-center
                                    gap-2
                                ">

                                <i class="
                                        ph-bold
                                        ph-circle-notch
                                        animate-spin
                                    "></i>

                                Actualizando...

                            </span>

                        </button>


                        {{-- =================================================
                        VOLVER
                        ================================================== --}}
                        <div class="
                                text-center
                            ">

                            <a href="{{
    route(
        'login'
    )
                                }}" class="
                                    inline-flex
                                    items-center
                                    gap-1.5
                                    text-xs
                                    font-bold
                                    text-meta
                                    transition-colors
                                    hover:text-boton-acento
                                ">

                                <i class="
                                        ph-bold
                                        ph-arrow-left
                                    "></i>

                                Volver al inicio de sesión

                            </a>

                        </div>

                    </div>

                </form>


                {{-- ========================================================
                PIE
                ========================================================= --}}
                <footer class="
                        flex
                        items-center
                        gap-2
                        border-t
                        border-borde-suave
                        bg-fondo-cardSuave
                        px-6
                        py-4
                        text-[11px]
                        font-medium
                        leading-5
                        text-meta
                    ">

                    <i class="
                            ph-bold
                            ph-shield-check
                            shrink-0
                            text-boton-acento
                        "></i>


                    El cambio solo se realizará si el enlace
                    de recuperación continúa siendo válido.

                </footer>

            </section>

        </div>

    </main>

</x-guest-layout>