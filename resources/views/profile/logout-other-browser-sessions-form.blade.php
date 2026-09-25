@php
    /*
    |--------------------------------------------------------------------------
    | Sesiones disponibles
    |--------------------------------------------------------------------------
    |
    | Jetstream proporciona:
    |
    | - agent
    | - ip_address
    | - is_current_device
    | - last_active
    |
    | No mostramos ubicación, ciudad o modelo del equipo porque esos datos
    | no forman parte del componente estándar.
    |
    */

    $sesiones = collect($this->sessions);

    $totalSesiones = $sesiones->count();

    $sesionesActuales = $sesiones
        ->filter(
            fn($session) =>
                (bool) $session->is_current_device
        )
        ->count();

    $otrasSesiones = $sesiones
        ->filter(
            fn($session) =>
                !(bool) $session->is_current_device
        )
        ->count();

    $hayOtrasSesiones =
        $otrasSesiones > 0;
@endphp


<section x-data="{
        /*
        |--------------------------------------------------------------------------
        | Listado
        |--------------------------------------------------------------------------
        */

        mostrarTodas:
            {{ $totalSesiones <= 4 ? 'true' : 'false' }},


        /*
        |--------------------------------------------------------------------------
        | Modal de confirmación
        |--------------------------------------------------------------------------
        */

        passwordLocal: '',

        mostrarPassword: false,

        capsLock: false,


        /*
        |--------------------------------------------------------------------------
        | Utilidades
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

        prepararConfirmacion() {
            this.passwordLocal = '';

            this.mostrarPassword = false;

            this.capsLock = false;

            this.$wire.$set(
                'password',
                '',
                false
            );

            this.$nextTick(() => {
                setTimeout(() => {
                    this.$refs
                        .passwordSesion
                        ?.focus();
                }, 250);
            });
        },

        sincronizarPassword() {
            this.$wire.$set(
                'password',
                this.passwordLocal,
                false
            );
        },

        cancelarConfirmacion() {
            this.passwordLocal = '';

            this.mostrarPassword = false;

            this.capsLock = false;

            this.$wire.$set(
                'password',
                '',
                false
            );
        },

        confirmarCierre() {
            if (
                this.passwordLocal.length === 0
            ) {
                this.$refs
                    .passwordSesion
                    ?.focus();

                return;
            }

            this.sincronizarPassword();

            this.$wire
                .logoutOtherBrowserSessions();
        }
    }" x-on:confirming-logout-other-browser-sessions.window="
        prepararConfirmacion()
    " class="
        panel-institucional
        overflow-hidden
        rounded-[1.4rem]
        border border-borde-suave
    ">

    {{-- ============================================================
    CABECERA
    ============================================================= --}}
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
                        ph-devices
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
                        Sesiones y dispositivos
                    </h2>


                    @if ($otrasSesiones > 0)

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
                            <span class="
                                        h-1.5
                                        w-1.5
                                        rounded-full
                                        bg-estado-advertencia
                                    "></span>

                            {{ $otrasSesiones }}
                            {{ $otrasSesiones === 1 ? 'sesión adicional' : 'sesiones adicionales' }}
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

                            Solo este dispositivo
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
                    Revisa los navegadores y dispositivos
                    donde tu cuenta mantiene una sesión activa
                    y cierra accesos que ya no necesites.
                </p>

            </div>

        </div>


        {{-- Resumen --}}
        <div class="
                flex
                items-center
                gap-2
                self-start
                rounded-full
                border border-borde-suave
                bg-fondo-cardSuave
                px-3
                py-1.5
                text-xs
                font-black
                text-apoyo
            ">
            <i class="
                    ph-bold
                    ph-monitor
                    text-boton-acento
                "></i>

            {{ $totalSesiones }}
            {{ $totalSesiones === 1 ? 'sesión registrada' : 'sesiones registradas' }}
        </div>

    </header>


    {{-- ============================================================
    MENSAJE DE ÉXITO
    ============================================================= --}}
    <x-action-message on="loggedOut" class="
            mx-5
            mb-5
        ">

        <div class="
                flex
                items-start
                gap-3
                rounded-2xl
                border border-estado-exitoBorde
                bg-estado-exitoBg
                p-4
            ">

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
                    Otras sesiones cerradas
                </p>


                <p class="
                        mt-1
                        text-xs
                        font-semibold
                        leading-5
                        text-meta
                    ">
                    Se cerraron correctamente las demás sesiones
                    de tu cuenta. Esta sesión permanece activa.
                </p>

            </div>

        </div>

    </x-action-message>


    {{-- ============================================================
    EXPLICACIÓN DE SEGURIDAD
    ============================================================= --}}
    <div class="
            border-t
            border-borde-suave
            p-5
        ">

        <div class="
                grid
                gap-4
                md:grid-cols-3
            ">

            {{-- Total --}}
            <article class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-cardSuave
                    p-4
                    transition
                    duration-200
                    hover:-translate-y-0.5
                    hover:shadow-card
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
                                ph-devices
                            "></i>
                    </div>


                    <div>

                        <p class="rm-label-soft">
                            Sesiones registradas
                        </p>


                        <p class="
                                mt-1
                                font-outfit
                                text-2xl
                                font-extrabold
                                text-titulo
                            ">
                            {{ $totalSesiones }}
                        </p>

                    </div>

                </div>

            </article>


            {{-- Actual --}}
            <article class="
                    rounded-2xl
                    border border-estado-exitoBorde
                    bg-estado-exitoBg
                    p-4
                    transition
                    duration-200
                    hover:-translate-y-0.5
                    hover:shadow-card
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
                                ph-check-circle
                            "></i>
                    </div>


                    <div>

                        <p class="rm-label-soft">
                            Dispositivo actual
                        </p>


                        <p class="
                                mt-1
                                font-outfit
                                text-2xl
                                font-extrabold
                                text-titulo
                            ">
                            {{ $sesionesActuales }}
                        </p>

                    </div>

                </div>

            </article>


            {{-- Otras --}}
            <article @class([
                '
                                    rounded-2xl
                                    border
                                    p-4
                                    transition
                                    duration-200
                                    hover:-translate-y-0.5
                                    hover:shadow-card
                                ',

                'border-estado-advertenciaBorde bg-estado-advertenciaBg' =>
                    $otrasSesiones > 0,

                'border-borde-suave bg-fondo-cardSuave' =>
                    $otrasSesiones === 0,
            ])>

                <div class="
                        flex
                        items-start
                        gap-3
                    ">

                    <div @class([
                        '
                                                    flex
                                                    h-10
                                                    w-10
                                                    shrink-0
                                                    items-center
                                                    justify-center
                                                    rounded-xl
                                                    bg-fondo-card
                                                ',

                        'text-estado-advertencia' =>
                            $otrasSesiones > 0,

                        'text-meta' =>
                            $otrasSesiones === 0,
                    ])>
                        <i class="
                                ph-bold
                                ph-browsers
                            "></i>
                    </div>


                    <div>

                        <p class="rm-label-soft">
                            Otras sesiones
                        </p>


                        <p class="
                                mt-1
                                font-outfit
                                text-2xl
                                font-extrabold
                                text-titulo
                            ">
                            {{ $otrasSesiones }}
                        </p>

                    </div>

                </div>

            </article>

        </div>


        {{-- Explicación --}}
        <div class="
                mt-4
                flex
                items-start
                gap-3
                rounded-2xl
                border border-borde-suave
                bg-fondo-cardSuave
                p-4
            ">

            <i class="
                    ph-bold
                    ph-info
                    mt-0.5
                    shrink-0
                    text-lg
                    text-boton-acento
                "></i>


            <div>

                <p class="
                        text-sm
                        font-bold
                        text-titulo
                    ">
                    ¿Qué información muestra RememberMind?
                </p>


                <p class="
                        mt-1
                        text-xs
                        font-medium
                        leading-5
                        text-meta
                    ">
                    Se muestran únicamente los datos disponibles
                    de la sesión: plataforma, navegador,
                    dirección IP, dispositivo actual y última actividad.
                    El listado puede no representar absolutamente
                    todos los accesos históricos de la cuenta.
                </p>

            </div>

        </div>

    </div>


    {{-- ============================================================
    LISTADO DE SESIONES
    ============================================================= --}}
    <div class="
            border-t
            border-borde-suave
            bg-fondo-cardSuave
            p-5
        ">

        <div class="
                mb-4
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            ">

            <div>

                <h3 class="
                        font-outfit
                        text-base
                        font-extrabold
                        text-titulo
                    ">
                    Actividad de sesiones
                </h3>


                <p class="
                        mt-1
                        text-xs
                        font-medium
                        text-meta
                    ">
                    Verifica que reconozcas los dispositivos
                    que mantienen acceso a tu cuenta.
                </p>

            </div>


            @if ($totalSesiones > 4)

                <button type="button" @click="
                            mostrarTodas =
                                !mostrarTodas
                        " class="rm-btn-ghost">

                    <i class="ph-bold" :class="
                                mostrarTodas
                                    ? 'ph-caret-up'
                                    : 'ph-caret-down'
                            "></i>


                    <span x-text="
                                mostrarTodas
                                    ? 'Mostrar menos'
                                    : 'Mostrar todas'
                            "></span>

                </button>

            @endif

        </div>


        {{-- ========================================================
        HAY SESIONES
        ========================================================= --}}
        @if ($totalSesiones > 0)

            <div class="
                        space-y-3
                    ">

                @foreach ($sesiones as $session)

                    @php
                        $esActual =
                            (bool) $session->is_current_device;

                        $esEscritorio =
                            (bool) $session->agent->isDesktop();

                        $plataforma =
                            $session->agent->platform()
                            ?: 'Plataforma desconocida';

                        $navegador =
                            $session->agent->browser()
                            ?: 'Navegador desconocido';

                        $ip =
                            $session->ip_address
                            ?: 'IP no disponible';

                        $ultimaActividad =
                            $session->last_active
                            ?: 'Sin información';

                        $mostrarInicialmente =
                            $loop->index < 4
                            || $esActual;
                    @endphp


                    <article x-data="{
                                    detalles:
                                        @js($esActual)
                                }" x-show="
                                    mostrarTodas
                                    || @js($mostrarInicialmente)
                                " x-transition:enter="
                                    transition
                                    ease-out
                                    duration-300
                                " x-transition:enter-start="
                                    opacity-0
                                    translate-y-2
                                " x-transition:enter-end="
                                    opacity-100
                                    translate-y-0
                                " x-transition:leave="
                                    transition
                                    ease-in
                                    duration-150
                                " x-transition:leave-start="
                                    opacity-100
                                " x-transition:leave-end="
                                    opacity-0
                                    -translate-y-1
                                " wire:key="
                                    session-{{ $loop->index }}-{{ md5((string) $ip) }}
                                " @class([
                                    '
                                                        group
                                                        overflow-hidden
                                                        rounded-2xl
                                                        border
                                                        transition-all
                                                        duration-200
                                                    ',

                                    '
                                                        border-estado-exitoBorde
                                                        bg-estado-exitoBg
                                                    ' => $esActual,

                                    '
                                                        border-borde-suave
                                                        bg-fondo-card
                                                        hover:-translate-y-0.5
                                                        hover:shadow-card
                                                    ' => !$esActual,
                                ])>

                        {{-- =================================================
                        RESUMEN
                        ================================================== --}}
                        <div class="
                                        flex
                                        flex-col
                                        gap-4
                                        p-4
                                        sm:flex-row
                                        sm:items-center
                                        sm:justify-between
                                    ">

                            <div class="
                                            flex
                                            min-w-0
                                            items-center
                                            gap-3
                                        ">

                                {{-- Dispositivo --}}
                                <div @class([
                                    '
                                                                    flex
                                                                    h-12
                                                                    w-12
                                                                    shrink-0
                                                                    items-center
                                                                    justify-center
                                                                    rounded-2xl
                                                                    transition-transform
                                                                    duration-200
                                                                    group-hover:scale-105
                                                                ',

                                    '
                                                                    bg-fondo-card
                                                                    text-estado-exito
                                                                ' => $esActual,

                                    '
                                                                    bg-fondo-cardSuave
                                                                    text-boton-acento
                                                                ' => !$esActual,
                                ])>

                                    @if ($esEscritorio)

                                        <i class="
                                                            ph-bold
                                                            ph-desktop-tower
                                                            text-xl
                                                        "></i>

                                    @else

                                        <i class="
                                                            ph-bold
                                                            ph-device-mobile
                                                            text-xl
                                                        "></i>

                                    @endif

                                </div>


                                <div class="min-w-0">

                                    <div class="
                                                    flex
                                                    flex-wrap
                                                    items-center
                                                    gap-2
                                                ">

                                        <p class="
                                                        truncate
                                                        text-sm
                                                        font-black
                                                        text-titulo
                                                    ">
                                            {{ $plataforma }}
                                            ·
                                            {{ $navegador }}
                                        </p>


                                        @if ($esActual)

                                            <span class="
                                                                inline-flex
                                                                items-center
                                                                gap-1.5
                                                                rounded-full
                                                                bg-fondo-card
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
                                                                    animate-pulse
                                                                    rounded-full
                                                                    bg-estado-exito
                                                                "></span>

                                                Este dispositivo
                                            </span>

                                        @else

                                            <span class="
                                                                inline-flex
                                                                items-center
                                                                gap-1.5
                                                                rounded-full
                                                                border border-borde-suave
                                                                bg-fondo-cardSuave
                                                                px-2.5
                                                                py-1
                                                                text-[10px]
                                                                font-black
                                                                uppercase
                                                                tracking-wider
                                                                text-meta
                                                            ">
                                                Otra sesión
                                            </span>

                                        @endif

                                    </div>


                                    <div class="
                                                    mt-1
                                                    flex
                                                    flex-wrap
                                                    items-center
                                                    gap-x-3
                                                    gap-y-1
                                                    text-xs
                                                    font-medium
                                                    text-meta
                                                ">

                                        <span class="
                                                        inline-flex
                                                        items-center
                                                        gap-1
                                                    ">
                                            <i class="
                                                            ph-bold
                                                            ph-globe-simple
                                                        "></i>

                                            {{ $ip }}
                                        </span>


                                        @if (!$esActual)

                                            <span class="
                                                                inline-flex
                                                                items-center
                                                                gap-1
                                                            ">
                                                <i class="
                                                                    ph-bold
                                                                    ph-clock
                                                                "></i>

                                                {{ $ultimaActividad }}
                                            </span>

                                        @else

                                            <span class="
                                                                inline-flex
                                                                items-center
                                                                gap-1
                                                                font-bold
                                                                text-estado-exito
                                                            ">
                                                <i class="
                                                                    ph-bold
                                                                    ph-check
                                                                "></i>

                                                Sesión que estás utilizando
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </div>


                            <button type="button" @click="
                                            detalles =
                                                !detalles
                                        " class="
                                            rm-btn-ghost
                                            shrink-0
                                        ">

                                <i class="ph-bold" :class="
                                                detalles
                                                    ? 'ph-caret-up'
                                                    : 'ph-caret-down'
                                            "></i>


                                <span x-text="
                                                detalles
                                                    ? 'Ocultar detalles'
                                                    : 'Ver detalles'
                                            "></span>

                            </button>

                        </div>


                        {{-- =================================================
                        DETALLE EXPANDIBLE
                        ================================================== --}}
                        <div x-cloak x-show="detalles" x-transition:enter="
                                        transition
                                        ease-out
                                        duration-200
                                    " x-transition:enter-start="
                                        opacity-0
                                        -translate-y-1
                                    " x-transition:enter-end="
                                        opacity-100
                                        translate-y-0
                                    " x-transition:leave="
                                        transition
                                        ease-in
                                        duration-150
                                    " x-transition:leave-start="
                                        opacity-100
                                    " x-transition:leave-end="
                                        opacity-0
                                    " class="
                                        border-t
                                        border-borde-suave
                                        px-4
                                        py-4
                                    ">

                            <div class="
                                            grid
                                            gap-3
                                            sm:grid-cols-2
                                            lg:grid-cols-4
                                        ">

                                {{-- Tipo --}}
                                <div class="
                                                rounded-xl
                                                border border-borde-suave
                                                bg-fondo-cardSuave
                                                p-3
                                            ">
                                    <p class="rm-label-soft">
                                        Tipo de dispositivo
                                    </p>

                                    <p class="
                                                    mt-1
                                                    text-sm
                                                    font-bold
                                                    text-titulo
                                                ">
                                        {{
                    $esEscritorio
                    ? 'Computadora'
                    : 'Dispositivo móvil'
                                                }}
                                    </p>
                                </div>


                                {{-- Plataforma --}}
                                <div class="
                                                rounded-xl
                                                border border-borde-suave
                                                bg-fondo-cardSuave
                                                p-3
                                            ">
                                    <p class="rm-label-soft">
                                        Plataforma
                                    </p>

                                    <p class="
                                                    mt-1
                                                    text-sm
                                                    font-bold
                                                    text-titulo
                                                ">
                                        {{ $plataforma }}
                                    </p>
                                </div>


                                {{-- Navegador --}}
                                <div class="
                                                rounded-xl
                                                border border-borde-suave
                                                bg-fondo-cardSuave
                                                p-3
                                            ">
                                    <p class="rm-label-soft">
                                        Navegador
                                    </p>

                                    <p class="
                                                    mt-1
                                                    text-sm
                                                    font-bold
                                                    text-titulo
                                                ">
                                        {{ $navegador }}
                                    </p>
                                </div>


                                {{-- IP --}}
                                <div class="
                                                rounded-xl
                                                border border-borde-suave
                                                bg-fondo-cardSuave
                                                p-3
                                            ">
                                    <p class="rm-label-soft">
                                        Dirección IP
                                    </p>

                                    <p class="
                                                    mt-1
                                                    break-all
                                                    font-mono
                                                    text-sm
                                                    font-bold
                                                    text-titulo
                                                ">
                                        {{ $ip }}
                                    </p>
                                </div>

                            </div>


                            <div @class([
                                '
                                                            mt-3
                                                            flex
                                                            items-start
                                                            gap-2
                                                            rounded-xl
                                                            border
                                                            p-3
                                                            text-xs
                                                            font-semibold
                                                            leading-5
                                                        ',

                                '
                                                            border-estado-exitoBorde
                                                            bg-estado-exitoBg
                                                            text-estado-exito
                                                        ' => $esActual,

                                '
                                                            border-borde-suave
                                                            bg-fondo-cardSuave
                                                            text-meta
                                                        ' => !$esActual,
                            ])>

                                @if ($esActual)

                                    <i class="
                                                        ph-bold
                                                        ph-check-circle
                                                        mt-0.5
                                                        shrink-0
                                                    "></i>

                                    <p>
                                        Esta es la sesión desde la que
                                        estás utilizando RememberMind.
                                        No se cerrará cuando selecciones
                                        “Cerrar otras sesiones”.
                                    </p>

                                @else

                                    <i class="
                                                        ph-bold
                                                        ph-clock-counter-clockwise
                                                        mt-0.5
                                                        shrink-0
                                                        text-boton-acento
                                                    "></i>

                                    <p>
                                        Última actividad registrada:
                                        <strong>
                                            {{ $ultimaActividad }}
                                        </strong>.
                                        Si no reconoces esta sesión,
                                        puedes cerrar todas las demás
                                        desde el control de seguridad inferior.
                                    </p>

                                @endif

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>


            {{-- ========================================================
            SIN SESIONES
            ========================================================= --}}
        @else

            <div class="
                        rounded-2xl
                        border border-dashed
                        border-borde-suave
                        bg-fondo-card
                        p-8
                        text-center
                    ">

                <div class="
                            mx-auto
                            flex
                            h-14
                            w-14
                            items-center
                            justify-center
                            rounded-2xl
                            bg-fondo-cardSuave
                            text-boton-acento
                        ">
                    <i class="
                                ph-bold
                                ph-devices
                                text-2xl
                            "></i>
                </div>


                <h4 class="
                            mt-4
                            font-outfit
                            text-base
                            font-extrabold
                            text-titulo
                        ">
                    No hay sesiones disponibles
                </h4>


                <p class="
                            mx-auto
                            mt-2
                            max-w-md
                            text-sm
                            font-medium
                            leading-6
                            text-meta
                        ">
                    No se encontraron registros de sesiones
                    para mostrar en este momento.
                </p>

            </div>

        @endif

    </div>


    {{-- ============================================================
    ZONA DE SEGURIDAD
    ============================================================= --}}
    <div class="
            border-t
            border-borde-suave
            p-5
        ">

        <div class="
                rounded-2xl
                border border-borde-suave
                bg-fondo-cardSuave
                p-5
            ">

            <div class="
                    flex
                    flex-col
                    gap-5
                    lg:flex-row
                    lg:items-center
                    lg:justify-between
                ">

                <div class="
                        flex
                        items-start
                        gap-3
                    ">

                    <div @class([
                        '
                                                    flex
                                                    h-11
                                                    w-11
                                                    shrink-0
                                                    items-center
                                                    justify-center
                                                    rounded-xl
                                                    bg-fondo-card
                                                ',

                        'text-estado-advertencia' =>
                            $hayOtrasSesiones,

                        'text-estado-exito' =>
                            !$hayOtrasSesiones,
                    ])>

                        <i class="
                                ph-bold
                                ph-sign-out
                                text-xl
                            "></i>

                    </div>


                    <div>

                        <h4 class="
                                font-outfit
                                text-base
                                font-extrabold
                                text-titulo
                            ">
                            Cerrar otras sesiones
                        </h4>


                        @if ($hayOtrasSesiones)

                            <p class="
                                        mt-1
                                        max-w-2xl
                                        text-sm
                                        font-medium
                                        leading-6
                                        text-meta
                                    ">
                                Actualmente existen
                                <strong class="text-titulo">
                                    {{ $otrasSesiones }}
                                    {{ $otrasSesiones === 1 ? 'sesión adicional' : 'sesiones adicionales' }}
                                </strong>
                                asociadas a tu cuenta.
                                Puedes cerrarlas todas sin afectar
                                este dispositivo.
                            </p>

                        @else

                            <p class="
                                        mt-1
                                        max-w-2xl
                                        text-sm
                                        font-medium
                                        leading-6
                                        text-meta
                                    ">
                                No existen otras sesiones activas
                                que necesiten cerrarse.
                                Esta sesión es la única registrada.
                            </p>

                        @endif

                    </div>

                </div>


                @if ($hayOtrasSesiones)

                    <button type="button" wire:click="confirmLogout" wire:loading.attr="disabled"
                        wire:target="confirmLogout" class="
                                rm-btn-danger
                                shrink-0
                            ">

                        <span wire:loading.remove wire:target="confirmLogout" class="
                                    inline-flex
                                    items-center
                                    gap-2
                                ">
                            <i class="
                                        ph-bold
                                        ph-sign-out
                                    "></i>

                            Cerrar otras sesiones
                        </span>


                        <span wire:loading wire:target="confirmLogout" class="
                                    items-center
                                    gap-2
                                ">
                            <i class="
                                        ph-bold
                                        ph-circle-notch
                                        animate-spin
                                    "></i>

                            Preparando...
                        </span>

                    </button>

                @else

                    <div class="
                                inline-flex
                                shrink-0
                                items-center
                                gap-2
                                rounded-xl
                                border border-estado-exitoBorde
                                bg-estado-exitoBg
                                px-4
                                py-2.5
                                text-xs
                                font-black
                                text-estado-exito
                            ">
                        <i class="
                                    ph-bold
                                    ph-check-circle
                                "></i>

                        Sin acciones pendientes
                    </div>

                @endif

            </div>


            @if ($hayOtrasSesiones)

                <div class="
                            mt-4
                            flex
                            items-start
                            gap-2
                            rounded-xl
                            border border-estado-advertenciaBorde
                            bg-estado-advertenciaBg
                            p-3
                            text-xs
                            font-semibold
                            leading-5
                            text-meta
                        ">

                    <i class="
                                ph-bold
                                ph-warning-circle
                                mt-0.5
                                shrink-0
                                text-estado-advertencia
                            "></i>


                    <p>
                        Si no reconoces alguna sesión,
                        además de cerrarla te recomendamos
                        cambiar tu contraseña y revisar
                        la autenticación en dos pasos.
                    </p>

                </div>

            @endif

        </div>

    </div>


    {{-- ============================================================
    MODAL: CERRAR OTRAS SESIONES
    ============================================================= --}}
    <x-dialog-modal wire:model.live="confirmingLogout" maxWidth="md">

        {{-- ========================================================
        TÍTULO
        ========================================================= --}}
        <x-slot name="title">

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
                        bg-estado-peligroBg
                        text-estado-peligro
                    ">
                    <i class="
                            ph-bold
                            ph-sign-out
                            text-xl
                        "></i>
                </div>


                <div>

                    <div class="
                            flex
                            flex-wrap
                            items-center
                            gap-2
                        ">

                        <span class="
                                font-outfit
                                text-lg
                                font-extrabold
                                text-titulo
                            ">
                            Cerrar otras sesiones
                        </span>


                        <span class="
                                inline-flex
                                items-center
                                gap-1.5
                                rounded-full
                                border border-estado-peligroBorde
                                bg-estado-peligroBg
                                px-2.5
                                py-1
                                text-[10px]
                                font-black
                                uppercase
                                tracking-wider
                                text-estado-peligro
                            ">
                            <i class="
                                    ph-bold
                                    ph-warning
                                "></i>

                            Acción sensible
                        </span>

                    </div>


                    <p class="
                            mt-1
                            text-xs
                            font-medium
                            text-meta
                        ">
                        Confirmación de seguridad
                    </p>

                </div>

            </div>

        </x-slot>


        {{-- ========================================================
        CONTENIDO
        ========================================================= --}}
        <x-slot name="content">

            <div class="space-y-5">

                {{-- Explicación --}}
                <div class="
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
                            ¿Cerrar las demás sesiones?
                        </p>


                        <p class="
                                mt-1
                                text-sm
                                font-medium
                                leading-6
                                text-meta
                            ">
                            Se cerrarán
                            <strong class="text-titulo">
                                {{ $otrasSesiones }}
                                {{ $otrasSesiones === 1 ? 'sesión' : 'sesiones' }}
                            </strong>
                            registradas en otros navegadores
                            o dispositivos.
                        </p>


                        <p class="
                                mt-2
                                text-xs
                                font-semibold
                                leading-5
                                text-meta
                            ">
                            La sesión que estás utilizando actualmente
                            permanecerá abierta.
                        </p>

                    </div>

                </div>


                {{-- Contraseña --}}
                <div>

                    <label for="session_password" class="rm-label">
                        Contraseña actual

                        <span class="text-estado-peligro" aria-hidden="true">
                            *
                        </span>
                    </label>


                    <div class="relative mt-1">

                        <input id="session_password" :type="
                                mostrarPassword
                                    ? 'text'
                                    : 'password'
                            " x-model="passwordLocal" @input="
                                sincronizarPassword()
                            " @keydown="
                                detectarCapsLock(
                                    $event
                                )
                            " @keyup="
                                detectarCapsLock(
                                    $event
                                )
                            " @keydown.enter.prevent="
                                confirmarCierre()
                            " @blur="
                                capsLock = false
                            " autocomplete="current-password" placeholder="Ingresa tu contraseña"
                            x-ref="passwordSesion" class="
                                rm-input
                                !mt-0
                                pr-12
                            ">


                        <button type="button" @click="
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
                                rounded-r-xl
                                text-meta
                                transition-colors
                                hover:text-boton-acento
                            " :aria-label="
                                mostrarPassword
                                    ? 'Ocultar contraseña'
                                    : 'Mostrar contraseña'
                            " :title="
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


                    <x-input-error for="password" class="mt-2" />


                    {{-- Caps Lock --}}
                    <div x-cloak x-show="capsLock" x-transition class="
                            mt-2
                            flex
                            items-center
                            gap-2
                            rounded-lg
                            border border-estado-advertenciaBorde
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


                    <div class="
                            mt-3
                            flex
                            items-start
                            gap-2
                            text-[11px]
                            font-medium
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
                            Solicitamos tu contraseña para impedir
                            que otra persona con acceso temporal
                            a tu sesión pueda cerrar tus dispositivos.
                        </p>

                    </div>

                </div>

            </div>

        </x-slot>


        {{-- ========================================================
        PIE
        ========================================================= --}}
        <x-slot name="footer">

            <div class="
                    flex
                    w-full
                    flex-col-reverse
                    gap-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
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
                            ph-shield-check
                            text-boton-acento
                        "></i>

                    Operación protegida
                </div>


                <div class="
                        flex
                        flex-wrap
                        justify-end
                        gap-2
                    ">

                    {{-- Cancelar --}}
                    <button type="button" @click="
                            cancelarConfirmacion()
                        " wire:click="
                            $toggle('confirmingLogout')
                        " wire:loading.attr="disabled" wire:target="
                            logoutOtherBrowserSessions
                        " class="
                            rm-btn-secondary
                            disabled:cursor-not-allowed
                            disabled:opacity-50
                        ">
                        <i class="
                                ph-bold
                                ph-x
                            "></i>

                        Cancelar
                    </button>


                    {{-- Confirmar --}}
                    <button type="button" @click="
                            confirmarCierre()
                        " :disabled="
                            passwordLocal.length === 0
                        " wire:loading.attr="disabled" wire:target="
                            logoutOtherBrowserSessions
                        " class="
                            rm-btn-danger
                            disabled:cursor-not-allowed
                            disabled:opacity-50
                        ">

                        <span wire:loading.remove wire:target="
                                logoutOtherBrowserSessions
                            " class="
                                inline-flex
                                items-center
                                gap-2
                            ">
                            <i class="
                                    ph-bold
                                    ph-sign-out
                                "></i>

                            Cerrar otras sesiones
                        </span>


                        <span wire:loading wire:target="
                                logoutOtherBrowserSessions
                            " class="
                                items-center
                                gap-2
                            ">
                            <i class="
                                    ph-bold
                                    ph-circle-notch
                                    animate-spin
                                "></i>

                            Cerrando sesiones...
                        </span>

                    </button>

                </div>

            </div>

        </x-slot>

    </x-dialog-modal>

</section>