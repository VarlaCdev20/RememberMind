<x-guest-layout>

    <style>
        {!! file_get_contents(
    resource_path(
        'frontend/styles/modules/auth-login.css'
    )
) !!}
    </style>


    <div x-data="{
            /*
            |--------------------------------------------------------------------------
            | Panel
            |--------------------------------------------------------------------------
            */

            panel: 'login',


            /*
            |--------------------------------------------------------------------------
            | Login
            |--------------------------------------------------------------------------
            */

            showPassword: false,

            isSubmitting: false,

            correo: @js(old('correo')),

            password: '',

            correoTouched: false,

            passwordTouched: false,


            /*
            |--------------------------------------------------------------------------
            | Recuperación
            |--------------------------------------------------------------------------
            */

            recoverCorreo: @js(old('correo')),

            recoverTouched: false,

            recoverSubmitting: false,

            recoverySent: false,

            recoveryFailed: false,


            /*
            |--------------------------------------------------------------------------
            | Toasts
            |--------------------------------------------------------------------------
            */

            toasts: [],


            addToast(
                message,
                type = 'error'
            ) {
                const id =
                    Date.now()
                    + Math.random();

                this.toasts.push({
                    id,
                    message,
                    type
                });

                setTimeout(
                    () => {
                        this.removeToast(
                            id
                        );
                    },
                    4500
                );
            },


            removeToast(id) {
                this.toasts =
                    this.toasts.filter(
                        toast =>
                            toast.id !== id
                    );
            },


            /*
            |--------------------------------------------------------------------------
            | Session Storage
            |--------------------------------------------------------------------------
            |
            | Permite saber si una respuesta del servidor pertenece
            | al formulario de recuperación o al login.
            |
            */

            markRecoveryPending() {
                try {
                    sessionStorage.setItem(
                        'rm-password-recovery-pending',
                        '1'
                    );
                } catch (error) {
                    //
                }
            },


            hasRecoveryPending() {
                try {
                    return (
                        sessionStorage.getItem(
                            'rm-password-recovery-pending'
                        ) === '1'
                    );
                } catch (error) {
                    return false;
                }
            },


            clearRecoveryPending() {
                try {
                    sessionStorage.removeItem(
                        'rm-password-recovery-pending'
                    );
                } catch (error) {
                    //
                }
            },


            /*
            |--------------------------------------------------------------------------
            | Validación login
            |--------------------------------------------------------------------------
            */

            get correoError() {
                if (!this.correoTouched) {
                    return '';
                }

                const value =
                    this.correo.trim();

                if (!value) {
                    return 'Ingresa tu correo institucional.';
                }

                const regex =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (
                    !regex.test(
                        value
                    )
                ) {
                    return 'El correo debe tener un formato válido.';
                }

                if (
                    value.length > 120
                ) {
                    return 'El correo no debe superar los 120 caracteres.';
                }

                return '';
            },


            get passwordError() {
                if (!this.passwordTouched) {
                    return '';
                }

                if (!this.password) {
                    return 'Ingresa tu contraseña.';
                }

                if (
                    this.password.length > 255
                ) {
                    return 'La contraseña ingresada no es válida.';
                }

                return '';
            },


            submitLogin(event) {
                this.correoTouched =
                    true;

                this.passwordTouched =
                    true;

                this.correo =
                    this.correo
                        .trim()
                        .toLowerCase();

                if (
                    this.correoError
                    ||
                    this.passwordError
                ) {
                    event.preventDefault();

                    if (
                        this.correoError
                    ) {
                        this.addToast(
                            this.correoError,
                            'error'
                        );
                    }

                    if (
                        this.passwordError
                    ) {
                        this.addToast(
                            this.passwordError,
                            'error'
                        );
                    }

                    return;
                }

                this.clearRecoveryPending();

                this.isSubmitting =
                    true;
            },


            /*
            |--------------------------------------------------------------------------
            | Validación recuperación
            |--------------------------------------------------------------------------
            */

            get recoverCorreoValido() {
                const value =
                    this.recoverCorreo
                        .trim();

                if (!value) {
                    return false;
                }

                if (
                    value.length > 120
                ) {
                    return false;
                }

                const regex =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                return regex.test(
                    value
                );
            },


            get recoverCorreoError() {
                if (!this.recoverTouched) {
                    return '';
                }

                const value =
                    this.recoverCorreo
                        .trim();

                if (!value) {
                    return 'Ingresa tu correo institucional.';
                }

                const regex =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (
                    !regex.test(
                        value
                    )
                ) {
                    return 'El correo debe tener un formato válido.';
                }

                if (
                    value.length > 120
                ) {
                    return 'El correo no debe superar los 120 caracteres.';
                }

                return '';
            },


            get recoverCanSubmit() {
                return (
                    this.recoverCorreoValido
                    &&
                    !this.recoverSubmitting
                );
            },


            /*
            |--------------------------------------------------------------------------
            | Navegación entre paneles
            |--------------------------------------------------------------------------
            */

            abrirRecuperacion() {
                /*
                 * Si ya escribió su correo en login,
                 * lo llevamos automáticamente a recuperación.
                 */
                if (
                    this.correo.trim()
                ) {
                    this.recoverCorreo =
                        this.correo.trim();
                }

                this.recoverTouched =
                    false;

                this.recoverSubmitting =
                    false;

                this.recoverySent =
                    false;

                this.recoveryFailed =
                    false;

                this.panel =
                    'recover';

                this.$nextTick(() => {
                    setTimeout(
                        () => {
                            this.$refs
                                .recoverCorreo
                                ?.focus();
                        },
                        350
                    );
                });
            },


            volverLogin() {
                if (
                    this.recoverSubmitting
                ) {
                    return;
                }

                /*
                 * Conservamos el correo escrito
                 * para facilitar volver a iniciar sesión.
                 */
                if (
                    this.recoverCorreo.trim()
                ) {
                    this.correo =
                        this.recoverCorreo.trim();
                }

                this.panel =
                    'login';

                this.recoverTouched =
                    false;

                this.recoverySent =
                    false;

                this.recoveryFailed =
                    false;

                this.clearRecoveryPending();

                this.$nextTick(() => {
                    setTimeout(
                        () => {
                            this.$refs
                                .loginCorreo
                                ?.focus();
                        },
                        250
                    );
                });
            },


            /*
            |--------------------------------------------------------------------------
            | Enviar recuperación
            |--------------------------------------------------------------------------
            */

            submitRecovery(event) {
                this.recoverTouched =
                    true;

                this.recoverySent =
                    false;

                this.recoveryFailed =
                    false;

                this.recoverCorreo =
                    this.recoverCorreo
                        .trim()
                        .toLowerCase();

                if (
                    !this.recoverCanSubmit
                ) {
                    event.preventDefault();

                    this.addToast(
                        this.recoverCorreoError
                        || 'Ingresa un correo institucional válido.',
                        'error'
                    );

                    this.$refs
                        .recoverCorreo
                        ?.focus();

                    return;
                }

                this.markRecoveryPending();

                this.recoverSubmitting =
                    true;
            }
        }" x-init="
            const recoveryPending =
                hasRecoveryPending();


            @if ($errors->any())

                if (recoveryPending) {
                    panel =
                        'recover';

                    recoverTouched =
                        true;

                    recoverSubmitting =
                        false;

                    recoveryFailed =
                        true;

                    addToast(
                        'No pudimos procesar la solicitud. Verifica el correo e inténtalo nuevamente.',
                        'error'
                    );

                    clearRecoveryPending();
                } else {
                    addToast(
                        'Verifica tu correo o contraseña e inténtalo nuevamente.',
                        'error'
                    );
                }

            @endif


            @if (session('status'))

                if (recoveryPending) {
                    panel =
                        'recover';

                    recoverySent =
                        true;

                    recoverSubmitting =
                        false;

                    recoveryFailed =
                        false;

                    addToast(
                        'Solicitud de recuperación procesada correctamente.',
                        'success'
                    );

                    clearRecoveryPending();
                } else {
                    addToast(
                        @js(session('status')),
                        'success'
                    );
                }

            @endif
        " class="
            relative
            min-h-screen
            overflow-hidden
            bg-fondo-app
            font-sans
            text-titulo
        ">

        {{-- ================================================================
        TEXTURAS DE FONDO
        ================================================================= --}}
        <div class="
                auth-noise
                pointer-events-none
                fixed
                inset-0
                z-[60]
                opacity-[0.16]
                mix-blend-overlay
            "></div>

        <div class="
                auth-dots
                pointer-events-none
                fixed
                inset-0
                z-0
                opacity-[0.10]
            "></div>


        {{-- ================================================================
        DECORACIÓN
        ================================================================= --}}
        <div class="
                pointer-events-none
                absolute
                left-[-14rem]
                top-[-16rem]
                h-[36rem]
                w-[36rem]
                rounded-full
                bg-boton-acento/10
                blur-[110px]
            "></div>

        <div class="
                pointer-events-none
                absolute
                right-[-16rem]
                bottom-[-16rem]
                h-[38rem]
                w-[38rem]
                rounded-full
                bg-modulo-salud/10
                blur-[120px]
            "></div>

        <div class="
                pointer-events-none
                absolute
                right-[24%]
                top-[10%]
                h-[28rem]
                w-[28rem]
                rounded-full
                bg-modulo-cognitivo/10
                blur-[120px]
            "></div>


        {{-- ================================================================
        TOASTS
        ================================================================= --}}
        <div class="
                fixed
                right-4
                top-5
                z-[9999]
                flex
                w-[calc(100%-2rem)]
                max-w-sm
                flex-col
                gap-3
                sm:right-6
            ">

            <template x-for="
                    toast in toasts
                " :key="
                    toast.id
                ">

                <div x-transition:enter="
                        transition
                        ease-out
                        duration-300
                    " x-transition:enter-start="
                        translate-x-8
                        opacity-0
                        scale-95
                    " x-transition:enter-end="
                        translate-x-0
                        opacity-100
                        scale-100
                    " x-transition:leave="
                        transition
                        ease-in
                        duration-200
                    " x-transition:leave-start="
                        translate-x-0
                        opacity-100
                        scale-100
                    " x-transition:leave-end="
                        translate-x-8
                        opacity-0
                        scale-95
                    " class="
                        flex
                        items-start
                        gap-3
                        rounded-2xl
                        border
                        p-3
                        shadow-panel
                        backdrop-blur-xl
                    " :class="
                        toast.type === 'success'
                            ? 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito'
                            : 'border-estado-peligroBorde bg-estado-peligroBg text-estado-peligro'
                    ">

                    <div class="
                            flex
                            h-9
                            w-9
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                        " :class="
                            toast.type === 'success'
                                ? 'bg-estado-exitoBg'
                                : 'bg-estado-peligroBg'
                        ">

                        <i class="
                                ph-bold
                                text-xl
                            " :class="
                                toast.type === 'success'
                                    ? 'ph-check-circle'
                                    : 'ph-warning-circle'
                            "></i>

                    </div>


                    <p class="
                            pt-1
                            text-sm
                            font-semibold
                            leading-5
                        " x-text="
                            toast.message
                        "></p>


                    <button type="button" @click="
                            removeToast(
                                toast.id
                            )
                        " class="
                            ml-auto
                            rounded-xl
                            p-1
                            opacity-60
                            transition
                            hover:bg-fondo-card/50
                            hover:opacity-100
                        ">

                        <i class="
                                ph-bold
                                ph-x
                            "></i>

                    </button>

                </div>

            </template>

        </div>


        {{-- ================================================================
        CONTENEDOR
        ================================================================= --}}
        <main class="
                relative
                z-10
                flex
                min-h-screen
                items-center
                justify-center
                px-4
                py-6
                sm:py-8
            ">

            <section class="
                    relative
                    grid
                    w-full
                    max-w-4xl
                    overflow-hidden
                    rounded-[2rem]
                    border
                    border-borde-suave
                    bg-[var(--surface-card-soft)]
                    shadow-panel
                    backdrop-blur-xl
                    lg:grid-cols-[1.02fr_0.98fr]
                ">

                {{-- ========================================================
                PANEL INSTITUCIONAL
                ========================================================= --}}
                <aside class="
                        relative
                        hidden
                        min-h-[470px]
                        overflow-hidden
                        border-r
                        border-borde-suave
                        bg-[var(--surface-card-warm)]
                        p-6
                        lg:block
                    ">

                    <div class="
                            pointer-events-none
                            absolute
                            -left-32
                            top-16
                            h-[30rem]
                            w-[30rem]
                            rounded-full
                            bg-boton-acento/10
                            blur-[90px]
                        "></div>


                    <div class="
                            pointer-events-none
                            absolute
                            -right-44
                            top-0
                            h-full
                            w-[72%]
                            rounded-l-[60%]
                            bg-fondo-app/55
                        "></div>


                    <div class="
                            pointer-events-none
                            absolute
                            bottom-[-8rem]
                            right-12
                            h-[24rem]
                            w-[24rem]
                            rounded-full
                            bg-modulo-salud/10
                            blur-[90px]
                        "></div>


                    {{-- Marca --}}
                    <div class="
                            relative
                            z-10
                            flex
                            items-center
                            gap-4
                        ">

                        <img src="{{
    asset(
        'storage/imagenes/LOGO.png'
    )
                            }}" alt="
                                Centro Geriátrico
                                Jardín de los Recuerdos
                            " class="
                                h-12
                                w-auto
                                object-contain
                                drop-shadow-md
                            ">


                        <div>

                            <h1 class="
                                    font-outfit
                                    text-lg
                                    font-extrabold
                                    leading-tight
                                    text-titulo
                                ">
                                CENTRO GERIÁTRICO
                                <br>
                                JARDÍN DE LOS RECUERDOS
                            </h1>


                            <p class="
                                    mt-1
                                    text-[0.68rem]
                                    font-bold
                                    uppercase
                                    tracking-[0.22em]
                                    text-meta
                                ">
                                Portal institucional
                            </p>

                        </div>

                    </div>


                    {{-- Presentación --}}
                    <div class="
                            relative
                            z-10
                            mt-10
                        ">

                        <span class="
                                inline-flex
                                items-center
                                gap-2
                                rounded-xl
                                border
                                border-boton-acento/25
                                bg-[var(--surface-card)]
                                px-3
                                py-1.5
                                text-xs
                                font-bold
                                uppercase
                                tracking-wider
                                text-boton-acento
                            ">

                            <span class="
                                    h-2
                                    w-2
                                    rounded-full
                                    bg-boton-acento
                                "></span>

                            Portal institucional

                        </span>


                        <h2 class="
                                mt-5
                                max-w-xl
                                font-outfit
                                text-4xl
                                font-bold
                                leading-tight
                                tracking-tight
                                text-titulo
                                text-shadow-soft
                            ">
                            Cuidado,
                            <span class="text-boton-acento">
                                memoria
                            </span>
                            y dignidad.
                        </h2>


                        <p class="
                                mt-5
                                max-w-lg
                                text-base
                                font-semibold
                                leading-7
                                text-apoyo
                            ">
                            Un espacio seguro para acompañar
                            el bienestar, registrar el seguimiento
                            y proteger la memoria de nuestros
                            adultos mayores.
                        </p>

                    </div>


                    {{-- Cards --}}
                    <div class="
                            relative
                            z-10
                            mt-8
                            grid
                            grid-cols-2
                            gap-4
                        ">

                        <div class="
                                rounded-2xl
                                border
                                border-borde-suave
                                bg-[var(--surface-card)]
                                p-4
                                shadow-card
                                backdrop-blur-md
                                transition
                                duration-300
                                hover:-translate-y-1
                                hover:shadow-cardHover
                            ">

                            <i class="
                                    ph-fill
                                    ph-heartbeat
                                    mb-3
                                    block
                                    text-3xl
                                    text-boton-acento
                                "></i>


                            <h3 class="
                                    font-outfit
                                    text-lg
                                    font-bold
                                    text-titulo
                                ">
                                Bienestar
                            </h3>


                            <p class="
                                    mt-1
                                    text-sm
                                    font-semibold
                                    leading-5
                                    text-meta
                                ">
                                Atención cálida,
                                cercana y humana.
                            </p>

                        </div>


                        <div class="
                                rounded-2xl
                                border
                                border-borde-suave
                                bg-[var(--surface-card)]
                                p-4
                                shadow-card
                                backdrop-blur-md
                                transition
                                duration-300
                                hover:-translate-y-1
                                hover:shadow-cardHover
                            ">

                            <i class="
                                    ph-fill
                                    ph-brain
                                    mb-3
                                    block
                                    text-3xl
                                    text-modulo-salud
                                "></i>


                            <h3 class="
                                    font-outfit
                                    text-lg
                                    font-bold
                                    text-titulo
                                ">
                                Memoria
                            </h3>


                            <p class="
                                    mt-1
                                    text-sm
                                    font-semibold
                                    leading-5
                                    text-meta
                                ">
                                Seguimiento cognitivo preventivo.
                            </p>

                        </div>

                    </div>


                    <i class="
                            ph-fill
                            ph-leaf
                            float-auth
                            absolute
                            bottom-8
                            left-8
                            text-5xl
                            text-modulo-salud/45
                        "></i>


                    <i class="
                            ph-fill
                            ph-heart
                            float-auth
                            absolute
                            right-20
                            top-[42%]
                            text-3xl
                            text-boton-acento/30
                        "></i>


                    <i class="
                            ph-fill
                            ph-flower
                            float-auth
                            absolute
                            bottom-24
                            right-28
                            text-5xl
                            text-modulo-cognitivo/35
                        "></i>

                </aside>


                {{-- ========================================================
                PANEL DERECHO
                ========================================================= --}}
                <section class="
                        relative
                        min-h-[470px]
                        overflow-hidden
                        bg-[var(--surface-card-soft)]
                        px-6
                        py-6
                        sm:px-7
                        lg:px-8
                    ">

                    <div class="
                            pointer-events-none
                            absolute
                            -right-24
                            -top-24
                            h-64
                            w-64
                            rounded-full
                            bg-boton-acento/12
                            blur-[80px]
                        "></div>


                    <div class="
                            pointer-events-none
                            absolute
                            -bottom-24
                            left-0
                            h-64
                            w-64
                            rounded-full
                            bg-modulo-salud/10
                            blur-[80px]
                        "></div>


                    <div class="
                            relative
                            z-10
                            flex
                            h-full
                            min-h-[430px]
                            items-start
                            pt-2
                            lg:pt-4
                        ">

                        <div class="
                                relative
                                w-full
                            ">

                            {{-- =================================================
                            LOGIN
                            ================================================== --}}
                            <div x-cloak x-show="
                                    panel === 'login'
                                " x-transition:enter="
                                    transition
                                    ease-out
                                    duration-500
                                " x-transition:enter-start="
                                    opacity-0
                                    translate-x-8
                                    scale-[0.98]
                                " x-transition:enter-end="
                                    opacity-100
                                    translate-x-0
                                    scale-100
                                " x-transition:leave="
                                    transition
                                    ease-in
                                    duration-300
                                    absolute
                                    inset-0
                                " x-transition:leave-start="
                                    opacity-100
                                    translate-x-0
                                    scale-100
                                " x-transition:leave-end="
                                    opacity-0
                                    -translate-x-8
                                    scale-[0.98]
                                " class="
                                    w-full
                                ">

                                {{-- Encabezado --}}
                                <div class="mb-6">

                                    <div class="
                                            mb-4
                                            flex
                                            h-14
                                            w-14
                                            items-center
                                            justify-center
                                            rounded-[1.4rem]
                                            bg-boton-acento
                                            text-inverso
                                            shadow-[0_14px_30px_rgba(233,122,95,0.30)]
                                        ">

                                        <i class="
                                                ph-bold
                                                ph-lock-key
                                                text-3xl
                                            "></i>

                                    </div>


                                    <h2 class="
                                            font-outfit
                                            text-3xl
                                            font-bold
                                            tracking-tight
                                            text-titulo
                                            text-shadow-soft
                                        ">
                                        Iniciar sesión
                                    </h2>


                                    <p class="
                                            mt-2
                                            max-w-md
                                            text-sm
                                            font-semibold
                                            leading-6
                                            text-apoyo
                                        ">
                                        Accede al portal institucional
                                        de seguimiento y cuidado.
                                    </p>

                                </div>


                                {{-- Error login --}}
                                @if ($errors->any())

                                    <div x-show="
                                                !hasRecoveryPending()
                                            " class="
                                                mb-4
                                                rounded-2xl
                                                border
                                                border-estado-peligroBorde
                                                bg-estado-peligroBg
                                                p-3
                                                text-sm
                                                font-semibold
                                                text-estado-peligro
                                            ">
                                        Verifica tu correo o contraseña
                                        e inténtalo nuevamente.
                                    </div>

                                @endif


                                {{-- =================================================
                                FORM LOGIN
                                ================================================== --}}
                                <form method="POST" action="{{
    route(
        'login'
    )
                                    }}" class="
                                        space-y-4
                                    " @submit="
                                        submitLogin(
                                            $event
                                        )
                                    ">
                                    @csrf


                                    {{-- Correo --}}
                                    <div>

                                        <label for="correo" class="
                                                mb-2
                                                block
                                                text-sm
                                                font-bold
                                                text-titulo
                                            ">
                                            Correo institucional
                                        </label>


                                        <div class="relative">

                                            <input id="correo" type="email" name="correo" x-ref="loginCorreo" x-model="
                                                    correo
                                                " @input="
                                                    correoTouched = true;
                                                " @blur="
                                                    correoTouched = true
                                                " required maxlength="120" autofocus autocomplete="username"
                                                placeholder="
                                                    admin@jardindelosrecuerdos.org
                                                " class="
                                                    w-full
                                                    rounded-2xl
                                                    border
                                                    border-borde-suave
                                                    bg-fondo-input
                                                    px-4
                                                    py-2.5
                                                    pr-12
                                                    text-sm
                                                    font-semibold
                                                    text-titulo
                                                    placeholder:text-placeholder
                                                    outline-none
                                                    shadow-inner
                                                    transition
                                                    duration-300
                                                    focus:border-borde-focus
                                                    focus:ring-2
                                                    focus:ring-[var(--color-input-ring-focus)]
                                                " :class="{
                                                    'border-estado-peligro focus:border-estado-peligro':
                                                        correoError,

                                                    'border-estado-exito':
                                                        correoTouched
                                                        &&
                                                        correo
                                                        &&
                                                        !correoError
                                                }">


                                            <span x-cloak x-show="
                                                    correoTouched
                                                    &&
                                                    correo
                                                " class="
                                                    pointer-events-none
                                                    absolute
                                                    inset-y-0
                                                    right-4
                                                    flex
                                                    items-center
                                                ">

                                                <i class="
                                                        ph-bold
                                                        text-xl
                                                    " :class="
                                                        correoError
                                                            ? 'ph-x-circle text-estado-peligro'
                                                            : 'ph-check-circle text-estado-exito'
                                                    "></i>

                                            </span>

                                        </div>


                                        <p x-cloak x-show="
                                                correoError
                                            " x-text="
                                                correoError
                                            " class="
                                                mt-2
                                                text-sm
                                                font-semibold
                                                text-estado-peligro
                                            "></p>

                                    </div>


                                    {{-- Password --}}
                                    <div>

                                        <label for="password" class="
                                                mb-2
                                                block
                                                text-sm
                                                font-bold
                                                text-titulo
                                            ">
                                            Contraseña
                                        </label>


                                        <div class="relative">

                                            <input id="password" :type="
                                                    showPassword
                                                        ? 'text'
                                                        : 'password'
                                                " name="password" x-model="
                                                    password
                                                " @input="
                                                    passwordTouched = true
                                                " @blur="
                                                    passwordTouched = true
                                                " required maxlength="255" autocomplete="current-password" placeholder="
                                                    Ingresa tu contraseña
                                                " class="
                                                    w-full
                                                    rounded-2xl
                                                    border
                                                    border-borde-suave
                                                    bg-fondo-input
                                                    px-4
                                                    py-2.5
                                                    pr-20
                                                    text-sm
                                                    font-semibold
                                                    text-titulo
                                                    placeholder:text-placeholder
                                                    outline-none
                                                    shadow-inner
                                                    transition
                                                    duration-300
                                                    focus:border-borde-focus
                                                    focus:ring-2
                                                    focus:ring-[var(--color-input-ring-focus)]
                                                " :class="{
                                                    'border-estado-peligro focus:border-estado-peligro':
                                                        passwordError,

                                                    'border-estado-exito':
                                                        passwordTouched
                                                        &&
                                                        password
                                                        &&
                                                        !passwordError
                                                }">


                                            <div class="
                                                    absolute
                                                    inset-y-0
                                                    right-4
                                                    flex
                                                    items-center
                                                    gap-2
                                                ">

                                                <span x-cloak x-show="
                                                        passwordTouched
                                                        &&
                                                        password
                                                    ">

                                                    <i class="
                                                            ph-bold
                                                            text-xl
                                                        " :class="
                                                            passwordError
                                                                ? 'ph-x-circle text-estado-peligro'
                                                                : 'ph-check-circle text-estado-exito'
                                                        "></i>

                                                </span>


                                                <button type="button" @click="
                                                        showPassword =
                                                            !showPassword
                                                    " class="
                                                        rounded-xl
                                                        p-1
                                                        text-meta
                                                        transition
                                                        hover:bg-fondo-hover
                                                        hover:text-boton-acento
                                                    " :aria-label="
                                                        showPassword
                                                            ? 'Ocultar contraseña'
                                                            : 'Mostrar contraseña'
                                                    ">

                                                    <i class="
                                                            ph-bold
                                                            text-xl
                                                        " :class="
                                                            showPassword
                                                                ? 'ph-eye-slash'
                                                                : 'ph-eye'
                                                        "></i>

                                                </button>

                                            </div>

                                        </div>


                                        <p x-cloak x-show="
                                                passwordError
                                            " x-text="
                                                passwordError
                                            " class="
                                                mt-2
                                                text-sm
                                                font-semibold
                                                text-estado-peligro
                                            "></p>

                                    </div>


                                    {{-- Recordarme / recuperación --}}
                                    <div class="
                                            flex
                                            flex-col
                                            gap-3
                                            sm:flex-row
                                            sm:items-center
                                            sm:justify-between
                                        ">

                                        <label class="
                                                flex
                                                cursor-pointer
                                                items-center
                                                gap-2
                                            ">

                                            <input id="remember_me" type="checkbox" name="remember" class="
                                                    h-4
                                                    w-4
                                                    rounded
                                                    border-borde-suave
                                                    bg-fondo-input
                                                    text-boton-acento
                                                    focus:ring-borde-focus/30
                                                ">

                                            <span class="
                                                    text-sm
                                                    font-semibold
                                                    text-apoyo
                                                ">
                                                Recordarme
                                            </span>

                                        </label>


                                        <button type="button" @click="
                                                abrirRecuperacion()
                                            " class="
                                                text-left
                                                text-sm
                                                font-bold
                                                text-boton-acento
                                                transition
                                                hover:text-titulo
                                                hover:underline
                                                sm:text-right
                                            ">
                                            ¿Olvidaste tu contraseña?
                                        </button>

                                    </div>


                                    {{-- Entrar --}}
                                    <button type="submit" :disabled="
                                            isSubmitting
                                        " class="
                                            group
                                            relative
                                            w-full
                                            overflow-hidden
                                            rounded-2xl
                                            bg-boton-acento
                                            px-4
                                            py-2.5
                                            text-sm
                                            font-extrabold
                                            text-inverso
                                            shadow-[0_15px_35px_rgba(233,122,95,0.30)]
                                            transition
                                            duration-300
                                            hover:-translate-y-0.5
                                            hover:bg-boton-acentoHover
                                            hover:shadow-[0_18px_40px_rgba(233,122,95,0.38)]
                                            active:scale-[0.98]
                                            disabled:cursor-not-allowed
                                            disabled:opacity-70
                                        ">

                                        <span x-show="
                                                !isSubmitting
                                            " class="
                                                relative
                                                z-10
                                                flex
                                                items-center
                                                justify-center
                                                gap-3
                                            ">

                                            Entrar al Portal

                                            <i class="
                                                    ph-bold
                                                    ph-arrow-right
                                                    transition
                                                    group-hover:translate-x-1
                                                "></i>

                                        </span>


                                        <span x-cloak x-show="
                                                isSubmitting
                                            " class="
                                                relative
                                                z-10
                                                flex
                                                items-center
                                                justify-center
                                                gap-3
                                            ">

                                            <i class="
                                                    ph-bold
                                                    ph-circle-notch
                                                    animate-spin
                                                "></i>

                                            Ingresando...

                                        </span>

                                    </button>

                                </form>

                            </div>


                            {{-- =================================================
                            RECUPERAR ACCESO
                            ================================================== --}}
                            <div x-cloak x-show="
                                    panel === 'recover'
                                " x-transition:enter="
                                    transition
                                    ease-out
                                    duration-500
                                " x-transition:enter-start="
                                    opacity-0
                                    translate-x-8
                                    scale-[0.98]
                                " x-transition:enter-end="
                                    opacity-100
                                    translate-x-0
                                    scale-100
                                " x-transition:leave="
                                    transition
                                    ease-in
                                    duration-300
                                    absolute
                                    inset-0
                                " x-transition:leave-start="
                                    opacity-100
                                    translate-x-0
                                    scale-100
                                " x-transition:leave-end="
                                    opacity-0
                                    -translate-x-8
                                    scale-[0.98]
                                " class="
                                    w-full
                                ">

                                {{-- Volver --}}
                                <button type="button" @click="
                                        volverLogin()
                                    " :disabled="
                                        recoverSubmitting
                                    " class="
                                        mb-5
                                        inline-flex
                                        items-center
                                        gap-2
                                        rounded-xl
                                        border
                                        border-borde-suave
                                        bg-fondo-input
                                        px-3
                                        py-2
                                        text-sm
                                        font-bold
                                        text-titulo
                                        shadow-card
                                        transition
                                        duration-200
                                        hover:-translate-x-0.5
                                        hover:text-boton-acento
                                        disabled:cursor-not-allowed
                                        disabled:opacity-50
                                    ">

                                    <i class="
                                            ph-bold
                                            ph-arrow-left
                                        "></i>

                                    Volver

                                </button>


                                {{-- Encabezado --}}
                                <div class="mb-6">

                                    <div class="
                                            mb-4
                                            flex
                                            h-14
                                            w-14
                                            items-center
                                            justify-center
                                            rounded-[1.4rem]
                                            bg-boton-acento
                                            text-inverso
                                            shadow-[0_14px_30px_rgba(233,122,95,0.30)]
                                            transition
                                            duration-300
                                        " :class="
                                            recoverSubmitting
                                                ? 'scale-95'
                                                : ''
                                        ">

                                        <i class="
                                                ph-bold
                                                text-3xl
                                            " :class="
                                                recoverSubmitting
                                                    ? 'ph-paper-plane-tilt animate-pulse'
                                                    : 'ph-key-return'
                                            "></i>

                                    </div>


                                    <h2 class="
                                            font-outfit
                                            text-3xl
                                            font-bold
                                            tracking-tight
                                            text-titulo
                                            text-shadow-soft
                                        ">
                                        Recuperar acceso
                                    </h2>


                                    <p class="
                                            mt-2
                                            max-w-md
                                            text-sm
                                            font-semibold
                                            leading-6
                                            text-apoyo
                                        ">
                                        Ingresa tu correo institucional
                                        y te enviaremos las instrucciones
                                        para crear una nueva contraseña.
                                    </p>

                                </div>


                                {{-- =================================================
                                SUCCESS
                                ================================================== --}}
                                <div x-cloak x-show="
                                        recoverySent
                                    " x-transition:enter="
                                        transition
                                        ease-out
                                        duration-300
                                    " x-transition:enter-start="
                                        opacity-0
                                        translate-y-2
                                        scale-[0.98]
                                    " x-transition:enter-end="
                                        opacity-100
                                        translate-y-0
                                        scale-100
                                    " class="
                                        mb-5
                                        rounded-2xl
                                        border
                                        border-estado-exitoBorde
                                        bg-estado-exitoBg
                                        p-4
                                    ">

                                    <div class="
                                            flex
                                            items-start
                                            gap-3
                                        ">

                                        <div class="
                                                flex
                                                h-10
                                                w-10
                                                shrink-0
                                                items-center
                                                justify-center
                                                rounded-xl
                                                bg-fondo-card
                                                text-estado-exito
                                            ">

                                            <i class="
                                                    ph-bold
                                                    ph-envelope-simple-open
                                                    text-xl
                                                "></i>

                                        </div>


                                        <div>

                                            <p class="
                                                    text-sm
                                                    font-black
                                                    text-titulo
                                                ">
                                                Solicitud procesada
                                            </p>


                                            <p class="
                                                    mt-1
                                                    text-xs
                                                    font-semibold
                                                    leading-5
                                                    text-meta
                                                ">
                                                Si el correo corresponde
                                                a una cuenta institucional,
                                                recibirás un enlace para
                                                continuar con la recuperación.
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                {{-- =================================================
                                ERROR RECOVERY
                                ================================================== --}}
                                <div x-cloak x-show="
                                        recoveryFailed
                                    " x-transition class="
                                        mb-5
                                        rounded-2xl
                                        border
                                        border-estado-peligroBorde
                                        bg-estado-peligroBg
                                        p-4
                                    ">

                                    <div class="
                                            flex
                                            items-start
                                            gap-3
                                        ">

                                        <i class="
                                                ph-bold
                                                ph-warning-circle
                                                mt-0.5
                                                shrink-0
                                                text-xl
                                                text-estado-peligro
                                            "></i>


                                        <div>

                                            <p class="
                                                    text-sm
                                                    font-black
                                                    text-titulo
                                                ">
                                                No pudimos procesar la solicitud
                                            </p>


                                            <p class="
                                                    mt-1
                                                    text-xs
                                                    font-semibold
                                                    leading-5
                                                    text-meta
                                                ">
                                                Verifica que el correo tenga
                                                un formato válido e inténtalo
                                                nuevamente.
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                {{-- =================================================
                                INFO
                                ================================================== --}}
                                <div class="
                                        mb-5
                                        rounded-2xl
                                        border
                                        border-borde-suave
                                        bg-[var(--surface-card)]
                                        p-4
                                        shadow-card
                                        backdrop-blur-md
                                    ">

                                    <div class="
                                            flex
                                            gap-3
                                        ">

                                        <div class="
                                                flex
                                                h-11
                                                w-11
                                                shrink-0
                                                items-center
                                                justify-center
                                                rounded-xl
                                                bg-estado-exitoBg
                                                text-estado-exito
                                            ">

                                            <i class="
                                                    ph-bold
                                                    ph-shield-check
                                                    text-2xl
                                                "></i>

                                        </div>


                                        <div>

                                            <h3 class="
                                                    font-bold
                                                    text-titulo
                                                ">
                                                Recuperación segura
                                            </h3>


                                            <p class="
                                                    mt-1
                                                    text-sm
                                                    font-semibold
                                                    leading-5
                                                    text-meta
                                                ">
                                                Usa el correo registrado
                                                por administración.
                                                Si no lo recuerdas,
                                                comunícate con el administrador.
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                {{-- =================================================
                                FORM RECOVERY
                                ================================================== --}}
                                <form method="POST" action="{{
    route(
        'password.email'
    )
                                    }}" class="
                                        space-y-4
                                    " @submit="
                                        submitRecovery(
                                            $event
                                        )
                                    ">
                                    @csrf


                                    <div>

                                        <label for="recover_correo" class="
                                                mb-2
                                                block
                                                text-sm
                                                font-bold
                                                text-titulo
                                            ">
                                            Correo institucional
                                        </label>


                                        <div class="relative">

                                            <input id="recover_correo" type="email" name="correo" x-ref="recoverCorreo"
                                                x-model="
                                                    recoverCorreo
                                                " @input="
                                                    recoverTouched = true;
                                                    recoverySent = false;
                                                    recoveryFailed = false;
                                                " @blur="
                                                    recoverTouched = true
                                                " required maxlength="120" autocomplete="username" placeholder="
                                                    Ingresa tu correo institucional
                                                " class="
                                                    w-full
                                                    rounded-2xl
                                                    border
                                                    border-borde-suave
                                                    bg-fondo-input
                                                    px-4
                                                    py-2.5
                                                    pr-12
                                                    text-sm
                                                    font-semibold
                                                    text-titulo
                                                    placeholder:text-placeholder
                                                    outline-none
                                                    shadow-inner
                                                    transition
                                                    duration-300
                                                    focus:border-borde-focus
                                                    focus:ring-2
                                                    focus:ring-[var(--color-input-ring-focus)]
                                                " :class="{
                                                    'border-estado-peligro focus:border-estado-peligro':
                                                        recoverCorreoError,

                                                    'border-estado-exito':
                                                        recoverTouched
                                                        &&
                                                        recoverCorreo
                                                        &&
                                                        !recoverCorreoError
                                                }">


                                            <span x-cloak x-show="
                                                    recoverTouched
                                                    &&
                                                    recoverCorreo
                                                " x-transition class="
                                                    pointer-events-none
                                                    absolute
                                                    inset-y-0
                                                    right-4
                                                    flex
                                                    items-center
                                                ">

                                                <i class="
                                                        ph-bold
                                                        text-xl
                                                    " :class="
                                                        recoverCorreoError
                                                            ? 'ph-x-circle text-estado-peligro'
                                                            : 'ph-check-circle text-estado-exito'
                                                    "></i>

                                            </span>

                                        </div>


                                        <p x-cloak x-show="
                                                recoverCorreoError
                                            " x-transition x-text="
                                                recoverCorreoError
                                            " class="
                                                mt-2
                                                text-sm
                                                font-semibold
                                                text-estado-peligro
                                            "></p>

                                    </div>


                                    {{-- Botón --}}
                                    <button type="submit" :disabled="
                                            !recoverCanSubmit
                                        " class="
                                            group
                                            relative
                                            w-full
                                            overflow-hidden
                                            rounded-2xl
                                            bg-boton-principal
                                            px-4
                                            py-2.5
                                            text-sm
                                            font-extrabold
                                            text-boton-principalTexto
                                            shadow-[0_15px_35px_rgba(47,62,92,0.24)]
                                            transition
                                            duration-300
                                            hover:-translate-y-0.5
                                            hover:bg-boton-principalHover
                                            active:scale-[0.98]
                                            disabled:cursor-not-allowed
                                            disabled:opacity-60
                                        ">

                                        <span x-show="
                                                !recoverSubmitting
                                            " class="
                                                relative
                                                z-10
                                                flex
                                                items-center
                                                justify-center
                                                gap-2
                                            ">

                                            <i class="
                                                    ph-bold
                                                    ph-paper-plane-tilt
                                                "></i>


                                            <span x-text="
                                                    recoverySent
                                                        ? 'Reenviar instrucciones'
                                                        : 'Enviar instrucciones'
                                                "></span>


                                            <i class="
                                                    ph-bold
                                                    ph-arrow-right
                                                    transition-transform
                                                    duration-200
                                                    group-hover:translate-x-1
                                                "></i>

                                        </span>


                                        <span x-cloak x-show="
                                                recoverSubmitting
                                            " class="
                                                relative
                                                z-10
                                                flex
                                                items-center
                                                justify-center
                                                gap-2
                                            ">

                                            <i class="
                                                    ph-bold
                                                    ph-circle-notch
                                                    animate-spin
                                                "></i>

                                            Enviando instrucciones...

                                        </span>

                                    </button>

                                </form>


                                {{-- =================================================
                                PIE RECOVERY
                                ================================================== --}}
                                <div class="
                                        mt-5
                                        flex
                                        items-start
                                        justify-center
                                        gap-2
                                        text-center
                                        text-xs
                                        font-semibold
                                        leading-5
                                        text-meta
                                    ">

                                    <i class="
                                            ph-bold
                                            ph-lock-key
                                            mt-0.5
                                            shrink-0
                                        "></i>


                                    <p>
                                        La interfaz no muestra información
                                        privada sobre la existencia de una cuenta.
                                        Revisa también la carpeta de spam.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </section>

        </main>

    </div>

</x-guest-layout>