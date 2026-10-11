@php
    /*
    |--------------------------------------------------------------------------
    | Mensaje de error localizado
    |--------------------------------------------------------------------------
    |
    | Fortify puede devolver estas cadenas originales en inglés.
    | Las normalizamos aquí para que esta pantalla nunca las muestre
    | directamente al usuario.
    |
    */

    $errorOriginal = $errors->first();

    $mensajeError = match ($errorOriginal) {
        'The provided two factor authentication code was invalid.' =>
            'El código de autenticación ingresado no es válido o ha expirado.',

        'The provided two factor recovery code was invalid.' =>
            'El código de recuperación ingresado no es válido.',

        default =>
            $errorOriginal,
    };
@endphp


<x-guest-layout>

    <style>
        /*
        |--------------------------------------------------------------------------
        | Animaciones locales del challenge 2FA
        |--------------------------------------------------------------------------
        */

        @keyframes rm-otp-digit-in {
            0% {
                opacity: 0;
                transform: translateY(7px) scale(.82);
            }

            65% {
                transform: translateY(-2px) scale(1.06);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }


        @keyframes rm-otp-shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-6px);
            }

            40% {
                transform: translateX(6px);
            }

            60% {
                transform: translateX(-4px);
            }

            80% {
                transform: translateX(4px);
            }
        }


        @keyframes rm-vault-turn {
            0% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(45deg);
            }

            50% {
                transform: rotate(90deg);
            }

            75% {
                transform: rotate(135deg);
            }

            100% {
                transform: rotate(180deg);
            }
        }


        @keyframes rm-vault-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(.94);
            }
        }


        .rm-otp-digit-enter {
            animation: rm-otp-digit-in .22s ease-out both;
        }


        .rm-otp-error {
            animation: rm-otp-shake .34s ease-in-out;
        }


        .rm-vault-turn {
            animation:
                rm-vault-turn .9s cubic-bezier(.45, 0, .2, 1) infinite alternate;
        }


        .rm-vault-pulse {
            animation:
                rm-vault-pulse 1.15s ease-in-out infinite;
        }


        @media (prefers-reduced-motion: reduce) {

            .rm-otp-digit-enter,
            .rm-otp-error,
            .rm-vault-turn,
            .rm-vault-pulse {
                animation: none !important;
            }
        }
    </style>


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
                max-w-xl
                items-center
                justify-center
            ">

            <section x-data="{
                    /*
                    |--------------------------------------------------------------------------
                    | Estado
                    |--------------------------------------------------------------------------
                    */

                    recovery: false,

                    codigo: '',

                    recoveryCode: '',

                    enviando: false,

                    fase: 'entrada',

                    errorServidor:
                        @js(
                            $errors->has('code')
                            || $errors->has('recovery_code')
                        ),


                    /*
                    |--------------------------------------------------------------------------
                    | Inicio
                    |--------------------------------------------------------------------------
                    */

                    init() {
                        if (!this.errorServidor) {
                            return;
                        }

                        this.$nextTick(() => {
                            const wrapper =
                                this.$refs.otpWrapper;

                            if (!wrapper) {
                                return;
                            }

                            wrapper.classList.add(
                                'rm-otp-error'
                            );

                            setTimeout(() => {
                                wrapper.classList.remove(
                                    'rm-otp-error'
                                );
                            }, 380);
                        });
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Código OTP
                    |--------------------------------------------------------------------------
                    */

                    normalizarCodigo(event) {
                        if (this.enviando) {
                            return;
                        }

                        this.codigo = String(
                            event.target.value || ''
                        )
                            .replace(/\D/g, '')
                            .slice(0, 6);

                        event.target.value =
                            this.codigo;

                        this.errorServidor =
                            false;

                        this.fase =
                            'entrada';
                    },


                    pegarCodigo(event) {
                        if (this.enviando) {
                            event.preventDefault();
                            return;
                        }

                        const texto = String(
                            event.clipboardData
                                ?.getData('text')
                            || ''
                        )
                            .replace(/\D/g, '')
                            .slice(0, 6);

                        if (!texto) {
                            return;
                        }

                        event.preventDefault();

                        this.codigo =
                            texto;

                        this.$refs.otpInput.value =
                            texto;

                        this.errorServidor =
                            false;

                        this.fase =
                            'entrada';
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Cambio de método
                    |--------------------------------------------------------------------------
                    */

                    usarRecuperacion() {
                        if (this.enviando) {
                            return;
                        }

                        this.recovery =
                            true;

                        this.codigo =
                            '';

                        this.fase =
                            'entrada';

                        this.errorServidor =
                            false;

                        this.$nextTick(() => {
                            this.$refs
                                .recoveryInput
                                ?.focus();
                        });
                    },


                    usarAutenticador() {
                        if (this.enviando) {
                            return;
                        }

                        this.recovery =
                            false;

                        this.recoveryCode =
                            '';

                        this.fase =
                            'entrada';

                        this.errorServidor =
                            false;

                        this.$nextTick(() => {
                            this.$refs
                                .otpInput
                                ?.focus();
                        });
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Estados calculados
                    |--------------------------------------------------------------------------
                    */

                    get codigoCompleto() {
                        return (
                            this.codigo.length
                            === 6
                        );
                    },


                    get recuperacionCompleta() {
                        return (
                            this.recoveryCode
                                .trim()
                                .length > 0
                        );
                    },


                    get puedeEnviar() {
                        return this.recovery
                            ? this.recuperacionCompleta
                            : this.codigoCompleto;
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Movimiento de la combinación
                    |--------------------------------------------------------------------------
                    */

                    desplazamiento(indice) {
                        const movimientos = [
                            138,
                            82,
                            28,
                            -28,
                            -82,
                            -138
                        ];

                        return movimientos[
                            indice - 1
                        ];
                    },


                    rotacion(indice) {
                        const rotaciones = [
                            7,
                            4,
                            2,
                            -2,
                            -4,
                            -7
                        ];

                        return rotaciones[
                            indice - 1
                        ];
                    },


                    estiloTarjeta(indice) {
                        if (
                            this.fase
                            !== 'cerrando'
                        ) {
                            return '';
                        }

                        return `
                            transform:
                                translateX(
                                    ${this.desplazamiento(indice)}px
                                )
                                translateY(5px)
                                rotate(
                                    ${this.rotacion(indice)}deg
                                )
                                scale(.88);

                            opacity: .18;

                            transition:
                                transform .52s
                                    cubic-bezier(.22,.8,.25,1),
                                opacity .34s ease;

                            transition-delay:
                                ${(indice - 1) * 25}ms;
                        `;
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Envío
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANTE:
                    |
                    | Los campos NO se deshabilitan antes de enviar.
                    |
                    | Se utilizan como readonly durante la animación
                    | para impedir modificaciones, pero siguen formando
                    | parte del POST que recibe Fortify.
                    |
                    */

                    enviar(event) {
                        if (this.enviando) {
                            event.preventDefault();
                            return;
                        }

                        if (!this.puedeEnviar) {
                            event.preventDefault();

                            if (this.recovery) {
                                this.$refs
                                    .recoveryInput
                                    ?.focus();
                            } else {
                                this.$refs
                                    .otpInput
                                    ?.focus();
                            }

                            return;
                        }

                        event.preventDefault();

                        this.enviando =
                            true;


                        /*
                         * Código de recuperación.
                         */
                        if (this.recovery) {
                            this.fase =
                                'verificando';

                            setTimeout(() => {
                                this.$refs.form.submit();
                            }, 280);

                            return;
                        }


                        /*
                         * OTP normal.
                         *
                         * 1. Las seis tarjetas convergen.
                         * 2. Aparece la caja fuerte.
                         * 3. Se envía el código real a Fortify.
                         */

                        this.fase =
                            'cerrando';

                        setTimeout(() => {
                            this.fase =
                                'verificando';
                        }, 470);

                        setTimeout(() => {
                            this.$refs.form.submit();
                        }, 760);
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
                                ph-shield-check
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
                        Verificación en dos pasos
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
                        Ingresa el código temporal de tu aplicación
                        autenticadora para continuar a RememberMind.
                    </p>

                </header>


                {{-- ========================================================
                ERROR
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
                                No pudimos verificar el código.
                            </p>


                            @if ($mensajeError)

                                <p class="
                                                mt-1
                                                text-xs
                                                font-medium
                                                leading-5
                                                text-meta
                                            ">
                                    {{ $mensajeError }}
                                </p>

                            @endif

                        </div>

                    </div>

                @endif


                {{-- ========================================================
                FORMULARIO
                ========================================================= --}}
                <form method="POST" action="{{ route('two-factor.login') }}" x-ref="form" x-on:submit="
                        enviar($event)
                    ">
                    @csrf


                    <div class="
                            px-6
                            py-7
                        ">

                        {{-- =================================================
                        OTP
                        ================================================== --}}
                        <div x-show="
                                !recovery
                            " x-transition:enter="
                                transition
                                ease-out
                                duration-250
                            " x-transition:enter-start="
                                opacity-0
                                translate-x-3
                            " x-transition:enter-end="
                                opacity-100
                                translate-x-0
                            ">

                            {{-- =============================================
                            ENTRADA
                            ============================================== --}}
                            <div x-show="
                                    fase !== 'verificando'
                                ">

                                <div class="text-center">

                                    <p class="
                                            text-sm
                                            font-bold
                                            text-titulo
                                        ">
                                        Código del autenticador
                                    </p>


                                    <p class="
                                            mt-1
                                            text-xs
                                            font-medium
                                            text-meta
                                        ">
                                        Introduce los 6 dígitos.
                                    </p>

                                </div>


                                {{-- =========================================
                                SEIS POSICIONES
                                ========================================== --}}
                                <div x-ref="otpWrapper" class="
                                        relative
                                        mx-auto
                                        mt-6
                                        max-w-md
                                    " x-on:click="
                                        fase === 'entrada'
                                        &&
                                        !enviando
                                        &&
                                        $refs.otpInput.focus()
                                    ">

                                    {{-- =================================================
                                    INPUT REAL
                                    =================================================
                                    IMPORTANTE:
                                    readonly sí se envía.
                                    disabled NO se envía.
                                    ================================================== --}}
                                    <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*"
                                        maxlength="6" autocomplete="one-time-code" autofocus x-ref="otpInput"
                                        x-model="codigo" x-on:input="
                                            normalizarCodigo($event)
                                        " x-on:paste="
                                            pegarCodigo($event)
                                        " :readonly="
                                            enviando
                                        " class="
                                            absolute
                                            inset-0
                                            z-20
                                            h-full
                                            w-full
                                            cursor-text
                                            opacity-0
                                        " aria-label="
                                            Código de autenticación
                                            de seis dígitos
                                        ">


                                    <div class="
                                            grid
                                            grid-cols-6
                                            gap-2
                                            sm:gap-3
                                        " aria-hidden="true">

                                        <template x-for="
                                                indice in 6
                                            " :key="
                                                indice
                                            ">

                                            <div class="
                                                    relative
                                                    flex
                                                    aspect-square
                                                    min-h-[52px]
                                                    items-center
                                                    justify-center
                                                    rounded-xl
                                                    border-2
                                                    bg-fondo-cardSuave
                                                    font-mono
                                                    text-xl
                                                    font-black
                                                    transition-all
                                                    duration-200
                                                    sm:text-2xl
                                                " :style="
                                                    estiloTarjeta(
                                                        indice
                                                    )
                                                " :class="{
                                                    'border-borde-suave text-meta':
                                                        codigo.length < indice
                                                        &&
                                                        codigo.length !== indice - 1,

                                                    'border-boton-acento scale-[1.035] bg-fondo-card text-boton-acento shadow-card':
                                                        codigo.length === indice - 1
                                                        &&
                                                        fase === 'entrada',

                                                    'border-boton-acento bg-fondo-card text-titulo':
                                                        codigo.length >= indice
                                                        &&
                                                        fase === 'entrada',

                                                    'border-estado-peligroBorde bg-estado-peligroBg':
                                                        errorServidor
                                                }">

                                                {{-- Dígito --}}
                                                <span x-show="
                                                        codigo.length
                                                        >= indice
                                                    " x-text="
                                                        codigo[
                                                            indice - 1
                                                        ] || ''
                                                    " class="
                                                        rm-otp-digit-enter
                                                    "></span>


                                                {{-- Posición vacía --}}
                                                <span x-show="
                                                        codigo.length
                                                        < indice
                                                    " class="
                                                        h-2
                                                        w-2
                                                        rounded-full
                                                        bg-borde-suave
                                                    " :class="
                                                        codigo.length
                                                            === indice - 1
                                                            &&
                                                            fase === 'entrada'
                                                            ? 'animate-pulse bg-boton-acento'
                                                            : ''
                                                    "></span>

                                            </div>

                                        </template>

                                    </div>

                                </div>


                                {{-- =========================================
                                ESTADO
                                ========================================== --}}
                                <div class="
                                        mt-4
                                        text-center
                                    ">

                                    <p class="
                                            text-xs
                                            font-bold
                                            transition-colors
                                            duration-200
                                        " :class="
                                            codigoCompleto
                                                ? 'text-estado-exito'
                                                : 'text-meta'
                                        ">

                                        <template x-if="
                                                !codigoCompleto
                                                &&
                                                fase === 'entrada'
                                            ">

                                            <span>
                                                <span x-text="
                                                        codigo.length
                                                    "></span>

                                                de 6 dígitos
                                            </span>

                                        </template>


                                        <template x-if="
                                                codigoCompleto
                                                &&
                                                fase === 'entrada'
                                            ">

                                            <span>
                                                Combinación completa ·
                                                lista para verificar
                                            </span>

                                        </template>


                                        <template x-if="
                                                fase === 'cerrando'
                                            ">

                                            <span>
                                                Cerrando combinación...
                                            </span>

                                        </template>

                                    </p>

                                </div>

                            </div>


                            {{-- =============================================
                            VERIFICANDO
                            ============================================== --}}
                            <div x-cloak x-show="
                                    fase === 'verificando'
                                " x-transition:enter="
                                    transition
                                    ease-out
                                    duration-300
                                " x-transition:enter-start="
                                    opacity-0
                                    scale-90
                                " x-transition:enter-end="
                                    opacity-100
                                    scale-100
                                " class="
                                    py-5
                                    text-center
                                ">

                                <p class="
                                        font-outfit
                                        text-base
                                        font-extrabold
                                        text-titulo
                                    ">
                                    Verificando tu código
                                </p>


                                <p class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-meta
                                    ">
                                    Estamos confirmando tu identidad.
                                </p>


                                {{-- Caja fuerte --}}
                                <div class="
                                        rm-vault-pulse
                                        relative
                                        mx-auto
                                        mt-7
                                        flex
                                        h-20
                                        w-20
                                        items-center
                                        justify-center
                                        rounded-[1.4rem]
                                        border-2
                                        border-boton-acento
                                        bg-fondo-cardSuave
                                        text-boton-acento
                                        shadow-card
                                    ">

                                    <div class="
                                            rm-vault-turn
                                            absolute
                                            inset-3
                                            rounded-full
                                            border-2
                                            border-borde-suave
                                        ">

                                        <span class="
                                                absolute
                                                left-1/2
                                                top-1
                                                h-2
                                                w-0.5
                                                -translate-x-1/2
                                                rounded-full
                                                bg-boton-acento
                                            "></span>

                                    </div>


                                    <i class="
                                            ph-bold
                                            ph-lock-key
                                            relative
                                            z-10
                                            text-xl
                                        "></i>

                                </div>


                                {{-- Puntos --}}
                                <div class="
                                        mt-6
                                        flex
                                        justify-center
                                        gap-1.5
                                    " aria-hidden="true">

                                    <span class="
                                            h-1.5
                                            w-1.5
                                            animate-bounce
                                            rounded-full
                                            bg-boton-acento
                                        "></span>


                                    <span class="
                                            h-1.5
                                            w-1.5
                                            animate-bounce
                                            rounded-full
                                            bg-boton-acento
                                            [animation-delay:120ms]
                                        "></span>


                                    <span class="
                                            h-1.5
                                            w-1.5
                                            animate-bounce
                                            rounded-full
                                            bg-boton-acento
                                            [animation-delay:240ms]
                                        "></span>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                        RECUPERACIÓN
                        ================================================== --}}
                        <div x-cloak x-show="
                                recovery
                            " x-transition:enter="
                                transition
                                ease-out
                                duration-250
                            " x-transition:enter-start="
                                opacity-0
                                -translate-x-3
                            " x-transition:enter-end="
                                opacity-100
                                translate-x-0
                            ">

                            <div x-show="
                                    fase !== 'verificando'
                                ">

                                <div class="text-center">

                                    <div class="
                                            mx-auto
                                            flex
                                            h-11
                                            w-11
                                            items-center
                                            justify-center
                                            rounded-xl
                                            bg-fondo-cardSuave
                                            text-boton-acento
                                        ">
                                        <i class="
                                                ph-bold
                                                ph-key
                                                text-xl
                                            "></i>
                                    </div>


                                    <p class="
                                            mt-3
                                            text-sm
                                            font-bold
                                            text-titulo
                                        ">
                                        Código de recuperación
                                    </p>


                                    <p class="
                                            mt-1
                                            text-xs
                                            font-medium
                                            leading-5
                                            text-meta
                                        ">
                                        Utiliza uno de los códigos
                                        que guardaste al activar
                                        la autenticación en dos pasos.
                                    </p>

                                </div>


                                <div class="mt-6">

                                    <label for="recovery_code" class="rm-label">
                                        Código de recuperación
                                    </label>


                                    {{-- =============================================
                                    CORREGIDO:
                                    readonly en lugar de disabled.
                                    ============================================== --}}
                                    <input id="recovery_code" name="recovery_code" type="text" x-ref="recoveryInput"
                                        x-model.trim="
                                            recoveryCode
                                        " x-on:input="
                                            errorServidor = false
                                        " :readonly="
                                            enviando
                                        " autocomplete="one-time-code" spellcheck="false" class="
                                            rm-input
                                            mt-1
                                            font-mono
                                            tracking-wider
                                        " placeholder="
                                            Ingresa tu código
                                        ">


                                    <p class="
                                            mt-2
                                            text-[11px]
                                            font-medium
                                            leading-5
                                            text-meta
                                        ">
                                        Úsalo únicamente si no tienes
                                        acceso a tu aplicación autenticadora.
                                    </p>

                                </div>

                            </div>


                            {{-- =============================================
                            VERIFICANDO RECUPERACIÓN
                            ============================================== --}}
                            <div x-cloak x-show="
                                    fase === 'verificando'
                                " x-transition class="
                                    py-8
                                    text-center
                                ">

                                <div class="
                                        mx-auto
                                        flex
                                        h-16
                                        w-16
                                        items-center
                                        justify-center
                                        rounded-2xl
                                        bg-fondo-cardSuave
                                        text-boton-acento
                                    ">

                                    <i class="
                                            ph-bold
                                            ph-circle-notch
                                            animate-spin
                                            text-2xl
                                        "></i>

                                </div>


                                <p class="
                                        mt-4
                                        font-bold
                                        text-titulo
                                    ">
                                    Verificando código
                                </p>

                            </div>

                        </div>


                        {{-- =================================================
                        BOTÓN PRINCIPAL
                        ================================================== --}}
                        <button type="submit" x-show="
                                fase === 'entrada'
                            " :disabled="
                                !puedeEnviar
                                || enviando
                            " class="
                                rm-btn-accent
                                mt-7
                                w-full
                                justify-center
                                py-3
                                disabled:cursor-not-allowed
                                disabled:opacity-50
                            ">

                            <i class="
                                    ph-bold
                                    ph-shield-check
                                "></i>

                            Verificar e ingresar

                        </button>


                        {{-- =================================================
                        CAMBIO DE MÉTODO
                        ================================================== --}}
                        <div x-show="
                                fase === 'entrada'
                            " class="
                                mt-5
                                text-center
                            ">

                            <button type="button" x-show="
                                    !recovery
                                " x-on:click="
                                    usarRecuperacion()
                                " class="
                                    inline-flex
                                    items-center
                                    gap-2
                                    text-xs
                                    font-bold
                                    text-boton-acento
                                    transition
                                    hover:opacity-75
                                ">

                                <i class="
                                        ph-bold
                                        ph-key
                                    "></i>

                                Usar código de recuperación

                            </button>


                            <button type="button" x-cloak x-show="
                                    recovery
                                " x-on:click="
                                    usarAutenticador()
                                " class="
                                    inline-flex
                                    items-center
                                    gap-2
                                    text-xs
                                    font-bold
                                    text-boton-acento
                                    transition
                                    hover:opacity-75
                                ">

                                <i class="
                                        ph-bold
                                        ph-arrow-left
                                    "></i>

                                Volver al autenticador

                            </button>

                        </div>

                    </div>

                </form>


                {{-- ========================================================
                PIE
                ========================================================= --}}
                <footer class="
                        flex
                        items-center
                        justify-between
                        gap-3
                        border-t
                        border-borde-suave
                        bg-fondo-cardSuave
                        px-6
                        py-4
                    ">

                    <div class="
                            flex
                            items-center
                            gap-2
                            text-[11px]
                            font-semibold
                            text-meta
                        ">

                        <i class="
                                ph-bold
                                ph-lock-key
                                text-boton-acento
                            "></i>

                        Acceso protegido

                    </div>


                    <a href="{{ route('login') }}" class="
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

                        Volver

                    </a>

                </footer>

            </section>

        </div>

    </main>

</x-guest-layout>