@php
    $usuario = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | Política visual de contraseña
    |--------------------------------------------------------------------------
    |
    | Cuando UpdateUserPassword exponga passwordPolicy(), la interfaz utilizará
    | automáticamente esa misma política.
    |
    | El fallback permite que esta vista pueda cargarse incluso antes de
    | modificar la Action/trait del backend.
    |
    */

    $accionPassword = \App\Actions\Identidad\Fortify\UpdateUserPassword::class;

    $politicaPassword = method_exists(
        $accionPassword,
        'passwordPolicy'
    )
        ? $accionPassword::passwordPolicy()
        : [
            'min' => 12,
            'max' => 64,
            'mayuscula' => true,
            'minuscula' => true,
            'numero' => true,
            'simbolo' => true,
        ];


    /*
    |--------------------------------------------------------------------------
    | Información actual
    |--------------------------------------------------------------------------
    */

    $ultimaActualizacionPassword = $usuario->password_changed_at
        ? $usuario->password_changed_at
            ->locale('es')
            ->translatedFormat(
                'd \d\e F \d\e Y · H:i'
            )
        : null;

    $debeCambiarPassword =
        (bool) $usuario->debe_cambiar_password;
@endphp


<form wire:submit="updatePassword" x-data="{
        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        */

        politica: @js($politicaPassword),

        editando: @js($debeCambiarPassword),

        guardado: false,

        actual: '',
        nueva: '',
        confirmacion: '',

        mostrarActual: false,
        mostrarNueva: false,
        mostrarConfirmacion: false,

        capsLock: false,


        /*
        |--------------------------------------------------------------------------
        | Requisitos propios de la nueva contraseña
        |--------------------------------------------------------------------------
        */

        get longitud() {
            return [...this.nueva].length;
        },

        get longitudValida() {
            return (
                this.longitud >= this.politica.min
                &&
                this.longitud <= this.politica.max
            );
        },

        get tieneMayuscula() {
            if (!this.politica.mayuscula) {
                return true;
            }

            return /\p{Lu}/u.test(
                this.nueva
            );
        },

        get tieneMinuscula() {
            if (!this.politica.minuscula) {
                return true;
            }

            return /\p{Ll}/u.test(
                this.nueva
            );
        },

        get tieneNumero() {
            if (!this.politica.numero) {
                return true;
            }

            return /\p{N}/u.test(
                this.nueva
            );
        },

        get tieneSimbolo() {
            if (!this.politica.simbolo) {
                return true;
            }

            return /[^\p{L}\p{N}\s]/u.test(
                this.nueva
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Comparaciones
        |--------------------------------------------------------------------------
        */

        get diferenteActual() {
            if (
                this.actual.length === 0
                ||
                this.nueva.length === 0
            ) {
                return false;
            }

            return this.actual !== this.nueva;
        },

        get confirmacionIngresada() {
            return this.confirmacion.length > 0;
        },

        get coincideConfirmacion() {
            return (
                this.confirmacionIngresada
                &&
                this.nueva === this.confirmacion
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Validaciones agrupadas
        |--------------------------------------------------------------------------
        */

        get reglasPasswordCumplidas() {
            return [
                this.longitudValida,
                this.tieneMayuscula,
                this.tieneMinuscula,
                this.tieneNumero,
                this.tieneSimbolo,
            ].filter(Boolean).length;
        },

        get totalReglasPassword() {
            return 5;
        },

        get porcentajePassword() {
            return Math.round(
                (
                    this.reglasPasswordCumplidas
                    /
                    this.totalReglasPassword
                ) * 100
            );
        },

        get requisitosTotalesCumplidos() {
            return [
                this.actual.length > 0,
                this.longitudValida,
                this.tieneMayuscula,
                this.tieneMinuscula,
                this.tieneNumero,
                this.tieneSimbolo,
                this.diferenteActual,
                this.coincideConfirmacion,
            ].filter(Boolean).length;
        },

        get totalRequisitos() {
            return 8;
        },

        get porcentajeTotal() {
            return Math.round(
                (
                    this.requisitosTotalesCumplidos
                    /
                    this.totalRequisitos
                ) * 100
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Fortaleza estructural
        |--------------------------------------------------------------------------
        |
        | No intenta calcular entropía criptográfica.
        | Solo representa cuánto cumple la política institucional.
        |
        */

        get nivelFortaleza() {
            const cumplidas =
                this.reglasPasswordCumplidas;

            if (
                this.nueva.length === 0
            ) {
                return 'Sin evaluar';
            }

            if (cumplidas <= 1) {
                return 'Muy débil';
            }

            if (cumplidas === 2) {
                return 'Débil';
            }

            if (cumplidas === 3) {
                return 'Aceptable';
            }

            if (cumplidas === 4) {
                return 'Buena';
            }

            return 'Fuerte';
        },

        get descripcionFortaleza() {
            if (
                this.nueva.length === 0
            ) {
                return 'Comienza a escribir la nueva contraseña.';
            }

            if (
                this.reglasPasswordCumplidas < 3
            ) {
                return 'Todavía faltan varios requisitos de seguridad.';
            }

            if (
                this.reglasPasswordCumplidas < 5
            ) {
                return 'La contraseña está mejorando, pero aún no está completa.';
            }

            return 'La contraseña cumple la estructura de seguridad requerida.';
        },


        /*
        |--------------------------------------------------------------------------
        | Habilitación final
        |--------------------------------------------------------------------------
        */

        get puedeGuardar() {
            return (
                this.actual.length > 0
                &&
                this.longitudValida
                &&
                this.tieneMayuscula
                &&
                this.tieneMinuscula
                &&
                this.tieneNumero
                &&
                this.tieneSimbolo
                &&
                this.diferenteActual
                &&
                this.coincideConfirmacion
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Estado textual
        |--------------------------------------------------------------------------
        */

        get estadoFormulario() {
            if (
                this.actual.length === 0
                &&
                this.nueva.length === 0
                &&
                this.confirmacion.length === 0
            ) {
                return 'Pendiente';
            }

            if (this.puedeGuardar) {
                return 'Listo para validar';
            }

            return 'Incompleto';
        },


        /*
        |--------------------------------------------------------------------------
        | Bloq Mayús
        |--------------------------------------------------------------------------
        */

        detectarCapsLock(event) {
            if (
                typeof event.getModifierState
                === 'function'
            ) {
                this.capsLock =
                    event.getModifierState(
                        'CapsLock'
                    );
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Apertura
        |--------------------------------------------------------------------------
        */

        abrirEdicion() {
            this.guardado = false;
            this.editando = true;

            this.$nextTick(() => {
                document
                    .getElementById(
                        'current_password'
                    )
                    ?.focus();
            });
        },


        /*
        |--------------------------------------------------------------------------
        | Limpieza
        |--------------------------------------------------------------------------
        */

        limpiarCampos() {
            this.actual = '';
            this.nueva = '';
            this.confirmacion = '';

            this.mostrarActual = false;
            this.mostrarNueva = false;
            this.mostrarConfirmacion = false;

            this.capsLock = false;

            this.$wire.$set(
                'state.current_password',
                '',
                false
            );

            this.$wire.$set(
                'state.password',
                '',
                false
            );

            this.$wire.$set(
                'state.password_confirmation',
                '',
                false
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Cancelar
        |--------------------------------------------------------------------------
        */

        cancelar() {
            this.guardado = false;

            this.limpiarCampos();

            @if (!$debeCambiarPassword)
                this.editando = false;
            @endif
        },


        /*
        |--------------------------------------------------------------------------
        | Guardado exitoso
        |--------------------------------------------------------------------------
        */

        finalizarGuardado() {
            this.limpiarCampos();

            this.editando = false;
            this.guardado = true;

            setTimeout(() => {
                this.guardado = false;
            }, 5000);
        }
    }" x-on:saved.window="finalizarGuardado()" class="w-full">

    <section class="
            panel-institucional
            overflow-hidden
            rounded-[1.4rem]
            border border-borde-suave
        ">

        {{-- =========================================================
        CABECERA
        ========================================================== --}}
        <header class="
                flex
                flex-col
                gap-4
                p-5
                sm:flex-row
                sm:items-start
                sm:justify-between
            ">

            <div class="
                    flex
                    items-start
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
                        bg-fondo-cardSuave
                        text-boton-acento
                    ">
                    <i class="
                            ph-bold
                            ph-key
                            text-xl
                        "></i>
                </div>


                <div class="min-w-0">

                    <div class="
                            flex
                            flex-wrap
                            items-center
                            gap-2
                        ">

                        <h2 class="
                                font-outfit
                                text-lg
                                font-extrabold
                                text-titulo
                            ">
                            Contraseña
                        </h2>


                        @if ($debeCambiarPassword)

                            <span class="
                                        inline-flex
                                        items-center
                                        gap-1.5
                                        rounded-full
                                        bg-estado-advertenciaBg
                                        px-2.5
                                        py-1
                                        text-[10px]
                                        font-black
                                        uppercase
                                        tracking-wider
                                        text-estado-advertencia
                                    ">
                                <i class="
                                            ph-bold
                                            ph-warning-circle
                                        "></i>

                                Cambio requerido
                            </span>

                        @else

                            <span class="
                                        inline-flex
                                        items-center
                                        gap-1.5
                                        rounded-full
                                        bg-estado-exitoBg
                                        px-2.5
                                        py-1
                                        text-[10px]
                                        font-black
                                        uppercase
                                        tracking-wider
                                        text-estado-exito
                                    ">
                                <span class="
                                            h-1.5
                                            w-1.5
                                            rounded-full
                                            bg-estado-exito
                                        "></span>

                                Configurada
                            </span>

                        @endif

                    </div>


                    <p class="
                            mt-1
                            max-w-2xl
                            text-sm
                            font-medium
                            leading-6
                            text-meta
                        ">
                        Protege el acceso a tu cuenta institucional
                        mediante una contraseña segura y exclusiva
                        para RememberMind.
                    </p>

                </div>

            </div>


            <button x-cloak x-show="!editando" type="button" @click="abrirEdicion()" class="rm-btn-secondary shrink-0">
                <i class="
                        ph-bold
                        ph-pencil-simple
                    "></i>

                Cambiar contraseña
            </button>

        </header>


        {{-- =========================================================
        MENSAJE EXITOSO
        ========================================================== --}}
        <div x-cloak x-show="guardado" x-transition class="
                mx-5
                mb-5
                flex
                items-start
                gap-3
                rounded-2xl
                border border-estado-exitoBorde
                bg-estado-exitoBg
                p-4
            " role="status" aria-live="polite">

            <i class="
                    ph-bold
                    ph-check-circle
                    mt-0.5
                    shrink-0
                    text-xl
                    text-estado-exito
                "></i>


            <div>

                <p class="
                        text-sm
                        font-black
                        text-estado-exito
                    ">
                    Contraseña actualizada
                </p>

                <p class="
                        mt-1
                        text-xs
                        font-semibold
                        leading-5
                        text-meta
                    ">
                    Tu nueva contraseña fue registrada correctamente.
                </p>

            </div>

        </div>


        {{-- =========================================================
        ESTADO ACTUAL
        ========================================================== --}}
        <div x-show="!editando" class="
                border-t
                border-borde-suave
                p-5
            ">

            <div class="
                    grid
                    gap-4
                    sm:grid-cols-2
                ">

                {{-- Estado --}}
                <article class="
                        rounded-2xl
                        border border-borde-suave
                        bg-fondo-cardSuave
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
                                    ph-shield-check
                                "></i>
                        </div>


                        <div>

                            <p class="rm-label-soft">
                                Estado
                            </p>

                            <p class="
                                    mt-1
                                    text-sm
                                    font-bold
                                    text-titulo
                                ">
                                Contraseña configurada
                            </p>

                            <p class="
                                    mt-1
                                    text-xs
                                    font-medium
                                    text-meta
                                ">
                                Protege el acceso
                                a tu cuenta institucional.
                            </p>

                        </div>

                    </div>

                </article>


                {{-- Último cambio --}}
                <article class="
                        rounded-2xl
                        border border-borde-suave
                        bg-fondo-cardSuave
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
                                text-boton-acento
                            ">
                            <i class="
                                    ph-bold
                                    ph-clock-counter-clockwise
                                "></i>
                        </div>


                        <div>

                            <p class="rm-label-soft">
                                Última actualización
                            </p>

                            <p class="
                                    mt-1
                                    text-sm
                                    font-bold
                                    text-titulo
                                ">
                                {{
    $ultimaActualizacionPassword
    ?: 'Sin fecha registrada'
                                }}
                            </p>


                            @if ($usuario->password_changed_at)

                                                    <p class="
                                                                mt-1
                                                                text-xs
                                                                font-medium
                                                                text-meta
                                                            ">
                                                        {{
                                ucfirst(
                                    $usuario
                                        ->password_changed_at
                                        ->locale('es')
                                        ->diffForHumans()
                                )
                                                            }}
                                                    </p>

                            @endif

                        </div>

                    </div>

                </article>

            </div>


            <div class="
                    mt-4
                    flex
                    items-start
                    gap-2
                    rounded-xl
                    border border-borde-suave
                    bg-fondo-cardSuave
                    p-3
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
                        text-boton-acento
                    "></i>


                <p>
                    RememberMind nunca muestra la contraseña
                    almacenada. Para modificarla debes conocer
                    tu contraseña actual.
                </p>

            </div>

        </div>


        {{-- =========================================================
        CAMBIO OBLIGATORIO
        ========================================================== --}}
        @if ($debeCambiarPassword)

            <div class="
                        mx-5
                        mb-5
                        flex
                        items-start
                        gap-3
                        rounded-2xl
                        border border-estado-advertenciaBorde
                        bg-estado-advertenciaBg
                        p-4
                    ">

                <i class="
                            ph-bold
                            ph-warning-circle
                            mt-0.5
                            shrink-0
                            text-xl
                            text-estado-advertencia
                        "></i>


                <div>

                    <p class="
                                text-sm
                                font-black
                                text-titulo
                            ">
                        Debes cambiar tu contraseña
                    </p>


                    <p class="
                                mt-1
                                text-sm
                                font-medium
                                leading-6
                                text-meta
                            ">
                        La contraseña asignada a tu cuenta
                        requiere actualización. Debes establecer
                        una contraseña personal que cumpla
                        todos los requisitos de seguridad.
                    </p>

                </div>

            </div>

        @endif


        {{-- =========================================================
        FORMULARIO
        ========================================================== --}}
        <div x-cloak x-show="editando" x-transition.opacity.duration.150ms class="
                border-t
                border-borde-suave
                bg-fondo-cardSuave
                p-5
            ">

            {{-- Introducción --}}
            <div class="
                    mb-5
                    flex
                    flex-col
                    gap-4
                    sm:flex-row
                    sm:items-start
                    sm:justify-between
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
                            text-boton-acento
                        ">
                        <i class="
                                ph-bold
                                ph-lock-key
                            "></i>
                    </div>


                    <div>

                        <h3 class="
                                font-outfit
                                text-base
                                font-extrabold
                                text-titulo
                            ">
                            Establecer nueva contraseña
                        </h3>


                        <p class="
                                mt-1
                                max-w-xl
                                text-xs
                                font-medium
                                leading-5
                                text-meta
                            ">
                            Los requisitos se verifican
                            inmediatamente mientras escribes.
                            Todos deben cumplirse para habilitar
                            la actualización.
                        </p>

                    </div>

                </div>


                <div class="
                        inline-flex
                        items-center
                        gap-2
                        self-start
                        rounded-full
                        border border-borde-suave
                        bg-fondo-card
                        px-3
                        py-1.5
                        text-xs
                        font-black
                    " :class="
                        puedeGuardar
                            ? 'text-estado-exito'
                            : 'text-meta'
                    ">

                    <span class="
                            h-2
                            w-2
                            rounded-full
                        " :class="
                            puedeGuardar
                                ? 'bg-estado-exito'
                                : 'bg-meta'
                        "></span>

                    <span x-text="estadoFormulario"></span>

                </div>

            </div>


            {{-- Bloq Mayús --}}
            <div x-cloak x-show="capsLock" x-transition class="
                    mb-4
                    flex
                    items-center
                    gap-2
                    rounded-xl
                    border border-estado-advertenciaBorde
                    bg-estado-advertenciaBg
                    px-3
                    py-2.5
                    text-xs
                    font-bold
                    text-estado-advertencia
                " role="status">
                <i class="
                        ph-bold
                        ph-arrow-fat-line-up
                    "></i>

                Bloq Mayús está activado.
            </div>


            {{-- =====================================================
            CAMPOS
            ====================================================== --}}
            <div class="
                    grid
                    gap-5
                    xl:grid-cols-3
                ">

                {{-- =================================================
                CONTRASEÑA ACTUAL
                ================================================== --}}
                <div>

                    <label for="current_password" class="rm-label">
                        Contraseña actual

                        <span class="text-estado-peligro" aria-hidden="true">
                            *
                        </span>
                    </label>


                    <div class="relative mt-1">

                        <input id="current_password" :type="
                                mostrarActual
                                    ? 'text'
                                    : 'password'
                            " x-model="actual" wire:model="state.current_password" @keydown="detectarCapsLock($event)"
                            @keyup="detectarCapsLock($event)" @blur="capsLock = false" autocomplete="current-password"
                            class="
                                rm-input
                                !mt-0
                                pr-12
                            " placeholder="Ingresa tu contraseña actual" required>


                        <button type="button" @click="
                                mostrarActual =
                                    !mostrarActual
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
                                mostrarActual
                                    ? 'Ocultar contraseña actual'
                                    : 'Mostrar contraseña actual'
                            " :title="
                                mostrarActual
                                    ? 'Ocultar contraseña'
                                    : 'Mostrar contraseña'
                            ">

                            <i class="ph-bold" :class="
                                    mostrarActual
                                        ? 'ph-eye-slash'
                                        : 'ph-eye'
                                "></i>

                        </button>

                    </div>


                    <x-input-error for="current_password" class="mt-2" />


                    <div class="
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
                                ph-shield-check
                                mt-0.5
                                shrink-0
                            "></i>

                        <span>
                            Se comprobará de forma segura
                            al actualizar.
                        </span>
                    </div>

                </div>


                {{-- =================================================
                NUEVA CONTRASEÑA
                ================================================== --}}
                <div>

                    <label for="password" class="rm-label">
                        Nueva contraseña

                        <span class="text-estado-peligro" aria-hidden="true">
                            *
                        </span>
                    </label>


                    <div class="relative mt-1">

                        <input id="password" :type="
                                mostrarNueva
                                    ? 'text'
                                    : 'password'
                            " x-model="nueva" wire:model="state.password" @keydown="detectarCapsLock($event)"
                            @keyup="detectarCapsLock($event)" @blur="capsLock = false" autocomplete="new-password"
                            minlength="{{ $politicaPassword['min'] }}" maxlength="{{ $politicaPassword['max'] }}" class="
                                rm-input
                                !mt-0
                                pr-12
                            " placeholder="Crea una contraseña segura" required>


                        <button type="button" @click="
                                mostrarNueva =
                                    !mostrarNueva
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
                                mostrarNueva
                                    ? 'Ocultar nueva contraseña'
                                    : 'Mostrar nueva contraseña'
                            " :title="
                                mostrarNueva
                                    ? 'Ocultar contraseña'
                                    : 'Mostrar contraseña'
                            ">

                            <i class="ph-bold" :class="
                                    mostrarNueva
                                        ? 'ph-eye-slash'
                                        : 'ph-eye'
                                "></i>

                        </button>

                    </div>


                    <x-input-error for="password" class="mt-2" />


                    <div class="
                            mt-2
                            flex
                            items-center
                            justify-between
                            gap-3
                            text-[11px]
                            font-semibold
                        ">

                        <span class="text-meta">
                            <span x-text="longitud"></span>
                            /
                            {{ $politicaPassword['max'] }}
                            caracteres
                        </span>


                        <span :class="
                                reglasPasswordCumplidas === 5
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            " x-text="nivelFortaleza"></span>

                    </div>

                </div>


                {{-- =================================================
                CONFIRMACIÓN
                ================================================== --}}
                <div>

                    <label for="password_confirmation" class="rm-label">
                        Confirmar contraseña

                        <span class="text-estado-peligro" aria-hidden="true">
                            *
                        </span>
                    </label>


                    <div class="relative mt-1">

                        <input id="password_confirmation" :type="
                                mostrarConfirmacion
                                    ? 'text'
                                    : 'password'
                            " x-model="confirmacion" wire:model="state.password_confirmation"
                            @keydown="detectarCapsLock($event)" @keyup="detectarCapsLock($event)"
                            @blur="capsLock = false" autocomplete="new-password" class="
                                rm-input
                                !mt-0
                                pr-12
                            " placeholder="Repite la nueva contraseña" required>


                        <button type="button" @click="
                                mostrarConfirmacion =
                                    !mostrarConfirmacion
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
                                mostrarConfirmacion
                                    ? 'Ocultar confirmación'
                                    : 'Mostrar confirmación'
                            ">

                            <i class="ph-bold" :class="
                                    mostrarConfirmacion
                                        ? 'ph-eye-slash'
                                        : 'ph-eye'
                                "></i>

                        </button>

                    </div>


                    <x-input-error for="password_confirmation" class="mt-2" />


                    <div x-cloak x-show="confirmacionIngresada" class="
                            mt-2
                            flex
                            items-center
                            gap-1.5
                            text-[11px]
                            font-semibold
                        " :class="
                            coincideConfirmacion
                                ? 'text-estado-exito'
                                : 'text-estado-peligro'
                        ">

                        <i class="ph-bold" :class="
                                coincideConfirmacion
                                    ? 'ph-check-circle'
                                    : 'ph-x-circle'
                            "></i>

                        <span x-text="
                                coincideConfirmacion
                                    ? 'Las contraseñas coinciden.'
                                    : 'Las contraseñas no coinciden.'
                            "></span>

                    </div>

                </div>

            </div>


            {{-- =====================================================
            EVALUACIÓN DE SEGURIDAD
            ====================================================== --}}
            <section class="
                    mt-6
                    overflow-hidden
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                " aria-labelledby="requisitos-password">

                {{-- Header --}}
                <div class="
                        flex
                        flex-col
                        gap-4
                        border-b
                        border-borde-suave
                        p-4
                        sm:flex-row
                        sm:items-center
                        sm:justify-between
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
                                bg-fondo-cardSuave
                                text-boton-acento
                            ">
                            <i class="
                                    ph-bold
                                    ph-shield-check
                                "></i>
                        </div>


                        <div>

                            <h4 id="requisitos-password" class="
                                    text-sm
                                    font-black
                                    text-titulo
                                ">
                                Requisitos obligatorios
                            </h4>


                            <p class="
                                    mt-0.5
                                    text-xs
                                    font-medium
                                    leading-5
                                    text-meta
                                ">
                                Todos los requisitos deben aparecer
                                como completados antes de guardar.
                            </p>

                        </div>

                    </div>


                    <div class="
                            min-w-[150px]
                            text-left
                            sm:text-right
                        ">

                        <p class="
                                text-sm
                                font-black
                                text-titulo
                            ">
                            <span x-text="
                                    requisitosTotalesCumplidos
                                "></span>

                            /

                            <span x-text="
                                    totalRequisitos
                                "></span>
                        </p>


                        <p class="
                                mt-0.5
                                text-[10px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            requisitos cumplidos
                        </p>

                    </div>

                </div>


                {{-- Progreso --}}
                <div class="
                        h-2
                        overflow-hidden
                        bg-fondo-cardSuave
                    " role="progressbar" aria-label="Cumplimiento de requisitos de contraseña"
                    :aria-valuenow="porcentajeTotal" aria-valuemin="0" aria-valuemax="100">

                    <div class="
                            h-full
                            bg-boton-acento
                            transition-all
                            duration-300
                        " :style="
                            `width: ${porcentajeTotal}%`
                        "></div>

                </div>


                {{-- Fortaleza --}}
                <div class="
                        border-b
                        border-borde-suave
                        bg-fondo-cardSuave
                        p-4
                    ">

                    <div class="
                            flex
                            flex-col
                            gap-3
                            sm:flex-row
                            sm:items-center
                            sm:justify-between
                        ">

                        <div>

                            <p class="
                                    text-xs
                                    font-black
                                    uppercase
                                    tracking-wider
                                    text-meta
                                ">
                                Fortaleza estructural
                            </p>


                            <p class="
                                    mt-1
                                    text-sm
                                    font-bold
                                    text-titulo
                                " x-text="
                                    nivelFortaleza
                                "></p>


                            <p class="
                                    mt-1
                                    text-xs
                                    font-medium
                                    text-meta
                                " x-text="
                                    descripcionFortaleza
                                "></p>

                        </div>


                        <div class="
                                flex
                                gap-1
                            " aria-hidden="true">

                            <template x-for="nivel in 5" :key="nivel">

                                <span class="
                                        h-2
                                        w-8
                                        rounded-full
                                        transition
                                    " :class="
                                        reglasPasswordCumplidas >= nivel
                                            ? 'bg-boton-acento'
                                            : 'bg-borde-suave'
                                    "></span>

                            </template>

                        </div>

                    </div>

                </div>


                {{-- Checklist --}}
                <div class="
                        grid
                        gap-2
                        p-4
                        sm:grid-cols-2
                        xl:grid-cols-4
                    ">

                    {{-- Contraseña actual ingresada --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            actual.length > 0
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                actual.length > 0
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                actual.length > 0
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            Contraseña actual ingresada.
                        </span>

                    </div>


                    {{-- Longitud --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            longitudValida
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                longitudValida
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                longitudValida
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            Entre
                            {{ $politicaPassword['min'] }}
                            y
                            {{ $politicaPassword['max'] }}
                            caracteres.
                        </span>

                    </div>


                    {{-- Mayúscula --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            tieneMayuscula
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                tieneMayuscula
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                tieneMayuscula
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            Al menos una letra mayúscula.
                        </span>

                    </div>


                    {{-- Minúscula --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            tieneMinuscula
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                tieneMinuscula
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                tieneMinuscula
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            Al menos una letra minúscula.
                        </span>

                    </div>


                    {{-- Número --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            tieneNumero
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                tieneNumero
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                tieneNumero
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            Al menos un número.
                        </span>

                    </div>


                    {{-- Símbolo --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            tieneSimbolo
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                tieneSimbolo
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                tieneSimbolo
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            Al menos un símbolo
                            como ! @ # $ % & * ? _
                        </span>

                    </div>


                    {{-- Diferente de la actual --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            diferenteActual
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                diferenteActual
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                diferenteActual
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            Diferente de la contraseña actual.
                        </span>

                    </div>


                    {{-- Confirmación --}}
                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border
                            p-3
                            transition-colors
                        " :class="
                            coincideConfirmacion
                                ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                : 'border-borde-suave bg-fondo-cardSuave'
                        ">

                        <i class="
                                ph-bold
                                mt-0.5
                                shrink-0
                            " :class="
                                coincideConfirmacion
                                    ? 'ph-check-circle text-estado-exito'
                                    : 'ph-circle text-meta'
                            "></i>


                        <span class="
                                text-xs
                                font-semibold
                                leading-5
                            " :class="
                                coincideConfirmacion
                                    ? 'text-estado-exito'
                                    : 'text-meta'
                            ">
                            La confirmación coincide.
                        </span>

                    </div>

                </div>

            </section>


            {{-- =====================================================
            RECOMENDACIONES
            ====================================================== --}}
            <div class="
                    mt-5
                    grid
                    gap-3
                    md:grid-cols-3
                ">

                <article class="
                        flex
                        items-start
                        gap-3
                        rounded-xl
                        border border-borde-suave
                        bg-fondo-card
                        p-3
                    ">

                    <i class="
                            ph-bold
                            ph-user-minus
                            mt-0.5
                            shrink-0
                            text-boton-acento
                        "></i>


                    <p class="
                            text-xs
                            font-semibold
                            leading-5
                            text-meta
                        ">
                        Evita nombres, documentos,
                        fechas de nacimiento o datos
                        personales fáciles de adivinar.
                    </p>

                </article>


                <article class="
                        flex
                        items-start
                        gap-3
                        rounded-xl
                        border border-borde-suave
                        bg-fondo-card
                        p-3
                    ">

                    <i class="
                            ph-bold
                            ph-arrows-clockwise
                            mt-0.5
                            shrink-0
                            text-boton-acento
                        "></i>


                    <p class="
                            text-xs
                            font-semibold
                            leading-5
                            text-meta
                        ">
                        No reutilices esta contraseña
                        en correo electrónico, redes
                        sociales u otros sistemas.
                    </p>

                </article>


                <article class="
                        flex
                        items-start
                        gap-3
                        rounded-xl
                        border border-borde-suave
                        bg-fondo-card
                        p-3
                    ">

                    <i class="
                            ph-bold
                            ph-eye-slash
                            mt-0.5
                            shrink-0
                            text-boton-acento
                        "></i>


                    <p class="
                            text-xs
                            font-semibold
                            leading-5
                            text-meta
                        ">
                        No compartas tu contraseña.
                        Administración nunca debería
                        solicitártela directamente.
                    </p>

                </article>

            </div>


            {{-- =====================================================
            LISTO
            ====================================================== --}}
            <div x-cloak x-show="puedeGuardar" x-transition class="
                    mt-5
                    flex
                    items-start
                    gap-3
                    rounded-xl
                    border border-estado-exitoBorde
                    bg-estado-exitoBg
                    p-4
                " role="status">

                <i class="
                        ph-bold
                        ph-check-circle
                        mt-0.5
                        shrink-0
                        text-xl
                        text-estado-exito
                    "></i>


                <div>

                    <p class="
                            text-sm
                            font-black
                            text-estado-exito
                        ">
                        Requisitos completados
                    </p>


                    <p class="
                            mt-1
                            text-xs
                            font-semibold
                            leading-5
                            text-meta
                        ">
                        La nueva contraseña ya cumple
                        todos los requisitos verificables.
                        Al guardar se comprobará que
                        tu contraseña actual sea correcta.
                    </p>

                </div>

            </div>


            {{-- =====================================================
            ACCIONES
            ====================================================== --}}
            <div class="
                    mt-5
                    flex
                    flex-col-reverse
                    gap-3
                    border-t
                    border-borde-suave
                    pt-5
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                ">

                <div class="
                        text-xs
                        font-medium
                        leading-5
                        text-meta
                    ">
                    <span x-show="!puedeGuardar">
                        Completa todos los requisitos
                        para habilitar el guardado.
                    </span>

                    <span x-cloak x-show="puedeGuardar" class="
                            inline-flex
                            items-center
                            gap-1.5
                            font-bold
                            text-estado-exito
                        ">
                        <i class="
                                ph-bold
                                ph-check-circle
                            "></i>

                        Lista para actualizar.
                    </span>
                </div>


                <div class="
                        flex
                        flex-wrap
                        justify-end
                        gap-2
                    ">

                    @if (!$debeCambiarPassword)

                        <button type="button" @click="cancelar()" wire:loading.attr="disabled" wire:target="updatePassword"
                            class="rm-btn-secondary">
                            <i class="
                                        ph-bold
                                        ph-x
                                    "></i>

                            Cancelar
                        </button>

                    @endif


                    <button type="submit" :disabled="!puedeGuardar" wire:loading.attr="disabled"
                        wire:target="updatePassword" class="
                            rm-btn-accent
                            disabled:cursor-not-allowed
                            disabled:opacity-50
                        ">

                        <span wire:loading.remove wire:target="updatePassword" class="
                                inline-flex
                                items-center
                                gap-2
                            ">
                            <i class="
                                    ph-bold
                                    ph-shield-check
                                "></i>

                            Actualizar contraseña
                        </span>


                        <span wire:loading wire:target="updatePassword" class="
                                items-center
                                gap-2
                            ">
                            <i class="
                                    ph-bold
                                    ph-circle-notch
                                    animate-spin
                                "></i>

                            Validando...
                        </span>

                    </button>

                </div>

            </div>

        </div>

    </section>

</form>