@php
    /*
    |--------------------------------------------------------------------------
    | Usuario y estado 2FA
    |--------------------------------------------------------------------------
    */

    $usuario = $this->user;

    $twoFactorActivo = (bool) $this->enabled;

    $enProcesoConfirmacion =
        $twoFactorActivo
        && $showingConfirmation;


    /*
    |--------------------------------------------------------------------------
    | Clave manual
    |--------------------------------------------------------------------------
    |
    | Solo se descifra cuando Jetstream decidió mostrar
    | la configuración 2FA.
    |
    */

    $setupKey = null;

    if (
        $twoFactorActivo
        && $showingQrCode
        && !empty($usuario->two_factor_secret)
    ) {
        try {
            $setupKey = decrypt(
                $usuario->two_factor_secret
            );
        } catch (\Throwable $exception) {
            $setupKey = null;

            report(
                $exception
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Códigos de recuperación
    |--------------------------------------------------------------------------
    |
    | No se descifran mientras Jetstream no haya autorizado
    | explícitamente su visualización.
    |
    | El acceso a showingRecoveryCodes está protegido mediante
    | x-confirms-password en esta misma vista.
    |
    */

    $recoveryCodes = [];

    if (
        $twoFactorActivo
        && $showingRecoveryCodes
        && !empty($usuario->two_factor_recovery_codes)
    ) {
        try {
            $recoveryCodes = json_decode(
                decrypt(
                    $usuario->two_factor_recovery_codes
                ),
                true
            ) ?: [];
        } catch (\Throwable $exception) {
            $recoveryCodes = [];

            report(
                $exception
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Archivo de descarga
    |--------------------------------------------------------------------------
    */

    $nombreArchivoCodigos =
        'remembermind-codigos-recuperacion-'
        . now()->format('Y-m-d')
        . '.txt';
@endphp


<section x-data="{
        /*
        |--------------------------------------------------------------------------
        | Datos sensibles autorizados por el servidor
        |--------------------------------------------------------------------------
        */

        setupKey: @js($setupKey),

        recoveryCodes:
            @js(array_values($recoveryCodes)),

        nombreArchivo:
            @js($nombreArchivoCodigos),


        /*
        |--------------------------------------------------------------------------
        | Estado visual
        |--------------------------------------------------------------------------
        */

        claveVisible: false,

        codigosVisibles: false,

        codigoOtp:
            @js((string) ($this->code ?? '')),

        copiado: null,

        mensajeTemporal: null,

        descargando: false,


        /*
        |--------------------------------------------------------------------------
        | OTP
        |--------------------------------------------------------------------------
        */

        limpiarOtp(valor) {
            return String(valor ?? '')
                .replace(/\D/g, '')
                .slice(0, 6);
        },

        actualizarOtp(event) {
            this.codigoOtp =
                this.limpiarOtp(
                    event.target.value
                );

            event.target.value =
                this.codigoOtp;

            this.$wire.$set(
                'code',
                this.codigoOtp,
                false
            );
        },

        get otpCompleto() {
            return (
                this.codigoOtp.length === 6
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Mostrar / ocultar secretos
        |--------------------------------------------------------------------------
        */

        alternarClave() {
            this.claveVisible =
                !this.claveVisible;
        },

        mostrarCodigos() {
            this.codigosVisibles = true;
        },

        ocultarCodigos() {
            this.codigosVisibles = false;

            this.copiado = null;
        },


        /*
        |--------------------------------------------------------------------------
        | Notificaciones locales
        |--------------------------------------------------------------------------
        */

        notificar(texto) {
            this.mensajeTemporal =
                texto;

            setTimeout(() => {
                this.mensajeTemporal = null;
            }, 3500);
        },

        marcarCopiado(clave) {
            this.copiado = clave;

            setTimeout(() => {
                if (
                    this.copiado === clave
                ) {
                    this.copiado = null;
                }
            }, 2500);
        },


        /*
        |--------------------------------------------------------------------------
        | Portapapeles
        |--------------------------------------------------------------------------
        */

        async copiarTexto(
            texto,
            clave = 'general'
        ) {
            if (!texto) {
                return;
            }

            try {
                if (
                    navigator.clipboard
                    &&
                    window.isSecureContext
                ) {
                    await navigator.clipboard.writeText(
                        texto
                    );
                } else {
                    this.copiarTextoFallback(
                        texto
                    );
                }

                this.marcarCopiado(
                    clave
                );

                this.notificar(
                    'Copiado al portapapeles.'
                );
            } catch (error) {
                try {
                    this.copiarTextoFallback(
                        texto
                    );

                    this.marcarCopiado(
                        clave
                    );

                    this.notificar(
                        'Copiado al portapapeles.'
                    );
                } catch (fallbackError) {
                    this.notificar(
                        'No fue posible copiar automáticamente.'
                    );
                }
            }
        },

        copiarTextoFallback(texto) {
            const area =
                document.createElement(
                    'textarea'
                );

            area.value = texto;

            area.setAttribute(
                'readonly',
                ''
            );

            area.style.position =
                'fixed';

            area.style.opacity =
                '0';

            area.style.pointerEvents =
                'none';

            document.body.appendChild(
                area
            );

            area.select();

            const copiado =
                document.execCommand(
                    'copy'
                );

            document.body.removeChild(
                area
            );

            if (!copiado) {
                throw new Error(
                    'No se pudo copiar.'
                );
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Copiar clave manual
        |--------------------------------------------------------------------------
        */

        copiarClave() {
            if (!this.setupKey) {
                return;
            }

            this.copiarTexto(
                this.setupKey,
                'setup-key'
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Copiar códigos
        |--------------------------------------------------------------------------
        */

        copiarCodigo(
            codigo,
            indice
        ) {
            if (!codigo) {
                return;
            }

            this.copiarTexto(
                codigo,
                `codigo-${indice}`
            );
        },

        copiarTodosLosCodigos() {
            if (
                this.recoveryCodes.length === 0
            ) {
                return;
            }

            const contenido =
                this.recoveryCodes.join(
                    '\n'
                );

            this.copiarTexto(
                contenido,
                'todos'
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Descargar códigos TXT
        |--------------------------------------------------------------------------
        */

        descargarCodigos() {
            if (
                this.recoveryCodes.length === 0
            ) {
                return;
            }

            this.descargando = true;

            try {
                const encabezado = [
                    'REMEMBERMIND',
                    'CÓDIGOS DE RECUPERACIÓN - AUTENTICACIÓN EN DOS PASOS',
                    '',
                    'IMPORTANTE:',
                    '- Cada código debe utilizarse una sola vez.',
                    '- Guarda este archivo en un lugar privado y seguro.',
                    '- No compartas estos códigos con otras personas.',
                    '- Si sospechas que fueron expuestos, genera códigos nuevos.',
                    '',
                    'CÓDIGOS:',
                    ''
                ];

                const contenido = [
                    ...encabezado,
                    ...this.recoveryCodes.map(
                        (codigo, indice) =>
                            `${indice + 1}. ${codigo}`
                    ),
                    '',
                    'Generado desde RememberMind.'
                ].join('\n');

                const blob =
                    new Blob(
                        [contenido],
                        {
                            type:
                                'text/plain;charset=utf-8'
                        }
                    );

                const url =
                    URL.createObjectURL(
                        blob
                    );

                const enlace =
                    document.createElement(
                        'a'
                    );

                enlace.href = url;

                enlace.download =
                    this.nombreArchivo;

                document.body.appendChild(
                    enlace
                );

                enlace.click();

                document.body.removeChild(
                    enlace
                );

                URL.revokeObjectURL(
                    url
                );

                this.notificar(
                    'Archivo de recuperación descargado.'
                );
            } finally {
                this.descargando = false;
            }
        }
    }" class="
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
                        ph-device-mobile
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

                    <h2 class="
                            font-outfit
                            text-lg
                            font-extrabold
                            text-titulo
                        ">
                        Autenticación en dos pasos
                    </h2>


                    @if (
                            $twoFactorActivo
                            && !$enProcesoConfirmacion
                        )

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

                            Activada
                        </span>

                    @elseif ($enProcesoConfirmacion)

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
                                        ph-clock
                                    "></i>

                            Configuración pendiente
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
                            <span class="
                                        h-1.5
                                        w-1.5
                                        rounded-full
                                        bg-meta
                                    "></span>

                            Desactivada
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
                    Añade una segunda comprobación de identidad
                    al iniciar sesión, además de tu contraseña.
                </p>

            </div>

        </div>


        {{-- Estado gráfico --}}
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
            " @class([
                'text-estado-exito' =>
                    $twoFactorActivo
                    && !$enProcesoConfirmacion,

                'text-estado-advertencia' =>
                    $enProcesoConfirmacion,

                'text-meta' =>
                    !$twoFactorActivo,
            ])>

            @if (
                    $twoFactorActivo
                    && !$enProcesoConfirmacion
                )

                <i class="
                            ph-bold
                            ph-shield-check
                        "></i>

                Protección adicional activa

            @elseif ($enProcesoConfirmacion)

                <i class="
                            ph-bold
                            ph-hourglass
                        "></i>

                Falta confirmar

            @else

                <i class="
                            ph-bold
                            ph-shield
                        "></i>

                Protección básica

            @endif

        </div>

    </header>


    {{-- ============================================================
    MENSAJE LOCAL
    ============================================================= --}}
    <div x-cloak x-show="mensajeTemporal" x-transition class="
            mx-5
            mb-5
            flex
            items-center
            gap-2
            rounded-xl
            border border-estado-exitoBorde
            bg-estado-exitoBg
            px-4
            py-3
            text-xs
            font-bold
            text-estado-exito
        " role="status" aria-live="polite">

        <i class="
                ph-bold
                ph-check-circle
            "></i>

        <span x-text="mensajeTemporal"></span>

    </div>


    {{-- ============================================================
    2FA DESACTIVADO
    ============================================================= --}}
    @if (!$twoFactorActivo)

        <div class="
                    border-t
                    border-borde-suave
                    p-5
                ">

            <div class="
                        grid
                        gap-5
                        lg:grid-cols-[1fr_0.85fr]
                    ">

                {{-- Explicación --}}
                <div>

                    <h3 class="
                                font-outfit
                                text-base
                                font-extrabold
                                text-titulo
                            ">
                        Refuerza la seguridad de tu cuenta
                    </h3>


                    <p class="
                                mt-2
                                max-w-2xl
                                text-sm
                                font-medium
                                leading-6
                                text-meta
                            ">
                        Al activar esta función, después de ingresar
                        tu contraseña necesitarás un código temporal
                        generado por una aplicación autenticadora.
                    </p>


                    <div class="
                                mt-5
                                grid
                                gap-3
                                sm:grid-cols-3
                            ">

                        {{-- 1 --}}
                        <article class="
                                    rounded-2xl
                                    border border-borde-suave
                                    bg-fondo-cardSuave
                                    p-4
                                ">

                            <div class="
                                        flex
                                        h-9
                                        w-9
                                        items-center
                                        justify-center
                                        rounded-xl
                                        bg-fondo-card
                                        font-black
                                        text-boton-acento
                                    ">
                                1
                            </div>


                            <p class="
                                        mt-3
                                        text-sm
                                        font-bold
                                        text-titulo
                                    ">
                                Activa
                            </p>


                            <p class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        leading-5
                                        text-meta
                                    ">
                                Confirma primero tu contraseña.
                            </p>

                        </article>


                        {{-- 2 --}}
                        <article class="
                                    rounded-2xl
                                    border border-borde-suave
                                    bg-fondo-cardSuave
                                    p-4
                                ">

                            <div class="
                                        flex
                                        h-9
                                        w-9
                                        items-center
                                        justify-center
                                        rounded-xl
                                        bg-fondo-card
                                        font-black
                                        text-boton-acento
                                    ">
                                2
                            </div>


                            <p class="
                                        mt-3
                                        text-sm
                                        font-bold
                                        text-titulo
                                    ">
                                Vincula
                            </p>


                            <p class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        leading-5
                                        text-meta
                                    ">
                                Escanea el código QR
                                con tu autenticador.
                            </p>

                        </article>


                        {{-- 3 --}}
                        <article class="
                                    rounded-2xl
                                    border border-borde-suave
                                    bg-fondo-cardSuave
                                    p-4
                                ">

                            <div class="
                                        flex
                                        h-9
                                        w-9
                                        items-center
                                        justify-center
                                        rounded-xl
                                        bg-fondo-card
                                        font-black
                                        text-boton-acento
                                    ">
                                3
                            </div>


                            <p class="
                                        mt-3
                                        text-sm
                                        font-bold
                                        text-titulo
                                    ">
                                Confirma
                            </p>


                            <p class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        leading-5
                                        text-meta
                                    ">
                                Ingresa el código temporal
                                generado por la aplicación.
                            </p>

                        </article>

                    </div>

                </div>


                {{-- Beneficio --}}
                <aside class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-5
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
                                    bg-fondo-card
                                    text-boton-acento
                                ">
                            <i class="
                                        ph-bold
                                        ph-shield-check
                                        text-xl
                                    "></i>
                        </div>


                        <div>

                            <p class="
                                        font-bold
                                        text-titulo
                                    ">
                                ¿Por qué activarla?
                            </p>

                            <p class="
                                        mt-1
                                        text-sm
                                        font-medium
                                        leading-6
                                        text-meta
                                    ">
                                Si alguien obtiene tu contraseña,
                                todavía necesitará el código temporal
                                de tu autenticador para ingresar.
                            </p>

                        </div>

                    </div>


                    <div class="
                                mt-5
                                flex
                                items-start
                                gap-2
                                rounded-xl
                                border border-borde-suave
                                bg-fondo-card
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
                            Por seguridad, RememberMind solicitará
                            tu contraseña antes de iniciar
                            la configuración.
                        </p>

                    </div>


                    <div class="mt-5">

                        <x-confirms-password wire:then="enableTwoFactorAuthentication" title="Confirmar identidad"
                            content="Ingresa tu contraseña para autorizar la activación de la autenticación en dos pasos."
                            button="Continuar">

                            <button type="button" wire:loading.attr="disabled" wire:target="enableTwoFactorAuthentication"
                                class="
                                        rm-btn-accent
                                        w-full
                                        justify-center
                                    ">

                                <span wire:loading.remove wire:target="enableTwoFactorAuthentication" class="
                                            inline-flex
                                            items-center
                                            gap-2
                                        ">
                                    <i class="
                                                ph-bold
                                                ph-shield-plus
                                            "></i>

                                    Activar autenticación en dos pasos
                                </span>


                                <span wire:loading wire:target="enableTwoFactorAuthentication" class="
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

                        </x-confirms-password>

                    </div>

                </aside>

            </div>

        </div>


        {{-- ============================================================
        CONFIGURACIÓN / CONFIRMACIÓN
        ============================================================= --}}
    @elseif ($enProcesoConfirmacion)

        <div class="
                    border-t
                    border-borde-suave
                    p-5
                ">

            {{-- Advertencia --}}
            <div class="
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
                        Configuración pendiente
                    </p>


                    <p class="
                                mt-1
                                text-sm
                                font-medium
                                leading-6
                                text-meta
                            ">
                        La autenticación en dos pasos todavía
                        no estará completamente configurada
                        hasta que ingreses correctamente
                        un código generado por tu aplicación autenticadora.
                    </p>

                </div>

            </div>


            <div class="
                        grid
                        gap-5
                        xl:grid-cols-[0.9fr_1.1fr]
                    ">

                {{-- =================================================
                QR
                ================================================== --}}
                <section class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-5
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
                                        ph-qr-code
                                    "></i>
                        </div>


                        <div>

                            <p class="
                                        font-outfit
                                        text-base
                                        font-extrabold
                                        text-titulo
                                    ">
                                1. Escanea el código QR
                            </p>


                            <p class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        leading-5
                                        text-meta
                                    ">
                                Abre tu aplicación autenticadora
                                y agrega una nueva cuenta
                                mediante este QR.
                            </p>

                        </div>

                    </div>


                    @if ($showingQrCode)

                            <div class="
                                            mt-5
                                            flex
                                            justify-center
                                        ">

                                <div class="
                                                inline-flex
                                                rounded-2xl
                                                border border-borde-suave
                                                bg-fondo-card
                                                p-4
                                                shadow-card
                                            ">
                                    {!!
                        $usuario
                            ->twoFactorQrCodeSvg()
                                            !!}
                                </div>

                            </div>

                    @endif


                    {{-- Clave manual --}}
                    @if ($setupKey)

                        <div class="
                                        mt-5
                                        rounded-xl
                                        border border-borde-suave
                                        bg-fondo-card
                                        p-4
                                    ">

                            <div class="
                                            flex
                                            flex-wrap
                                            items-center
                                            justify-between
                                            gap-2
                                        ">

                                <div>

                                    <p class="
                                                    text-[10px]
                                                    font-black
                                                    uppercase
                                                    tracking-wider
                                                    text-meta
                                                ">
                                        Clave manual
                                    </p>


                                    <p class="
                                                    mt-1
                                                    text-xs
                                                    font-medium
                                                    text-meta
                                                ">
                                        Úsala solo si no puedes escanear el QR.
                                    </p>

                                </div>


                                <button type="button" @click="alternarClave()" class="rm-btn-ghost">

                                    <i class="ph-bold" :class="
                                                    claveVisible
                                                        ? 'ph-eye-slash'
                                                        : 'ph-eye'
                                                "></i>

                                    <span x-text="
                                                    claveVisible
                                                        ? 'Ocultar'
                                                        : 'Mostrar'
                                                "></span>

                                </button>

                            </div>


                            <div class="
                                            mt-3
                                            flex
                                            items-center
                                            gap-2
                                        ">

                                <code class="
                                                min-w-0
                                                flex-1
                                                overflow-x-auto
                                                rounded-xl
                                                bg-fondo-panel
                                                px-3
                                                py-2.5
                                                font-mono
                                                text-xs
                                                font-bold
                                                tracking-wider
                                                text-apoyo
                                            ">
                                            <span
                                                x-show="!claveVisible"
                                            >
                                                ••••••••••••••••••••••••••••••••
                                            </span>

                                            <span
                                                x-cloak
                                                x-show="claveVisible"
                                                x-text="setupKey"
                                            ></span>
                                        </code>


                                <button type="button" @click="copiarClave()" class="rm-btn-icon" title="Copiar clave"
                                    aria-label="Copiar clave manual">

                                    <i class="ph-bold" :class="
                                                    copiado === 'setup-key'
                                                        ? 'ph-check'
                                                        : 'ph-copy'
                                                "></i>

                                </button>

                            </div>


                            <div class="
                                            mt-3
                                            flex
                                            items-start
                                            gap-2
                                            text-[11px]
                                            font-semibold
                                            leading-5
                                            text-meta
                                        ">

                                <i class="
                                                ph-bold
                                                ph-warning
                                                mt-0.5
                                                shrink-0
                                            "></i>

                                <p>
                                    Esta clave permite configurar
                                    el segundo factor.
                                    No la compartas ni la envíes
                                    por mensajería.
                                </p>

                            </div>

                        </div>

                    @endif

                </section>


                {{-- =================================================
                OTP
                ================================================== --}}
                <section class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-5
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
                                        ph-password
                                    "></i>
                        </div>


                        <div>

                            <p class="
                                        font-outfit
                                        text-base
                                        font-extrabold
                                        text-titulo
                                    ">
                                2. Confirma el código temporal
                            </p>


                            <p class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        leading-5
                                        text-meta
                                    ">
                                Introduce el código de 6 dígitos
                                que aparece en tu aplicación autenticadora.
                            </p>

                        </div>

                    </div>


                    <div class="mt-6">

                        <label for="code" class="rm-label">
                            Código de verificación

                            <span class="text-estado-peligro" aria-hidden="true">
                                *
                            </span>
                        </label>


                        <input id="code" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
                            maxlength="6" x-model="codigoOtp" @input="actualizarOtp($event)" class="
                                    rm-input
                                    mt-1
                                    text-center
                                    font-mono
                                    text-xl
                                    font-black
                                    tracking-[0.35em]
                                " placeholder="000000" autofocus>


                        <x-input-error for="code" class="mt-2" />


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
                                Código temporal de 6 dígitos
                            </span>


                            <span :class="
                                        otpCompleto
                                            ? 'text-estado-exito'
                                            : 'text-meta'
                                    ">
                                <span x-text="
                                            codigoOtp.length
                                        "></span>

                                / 6
                            </span>

                        </div>

                    </div>


                    <div class="
                                mt-5
                                flex
                                items-start
                                gap-2
                                rounded-xl
                                border border-borde-suave
                                bg-fondo-card
                                p-3
                                text-xs
                                font-semibold
                                leading-5
                                text-meta
                            ">

                        <i class="
                                    ph-bold
                                    ph-info
                                    mt-0.5
                                    shrink-0
                                    text-boton-acento
                                "></i>

                        <p>
                            Estos códigos cambian periódicamente.
                            Si el código vence, espera al siguiente
                            e inténtalo nuevamente.
                        </p>

                    </div>


                    <div class="
                                mt-6
                                flex
                                flex-wrap
                                justify-end
                                gap-2
                            ">

                        {{-- Cancelar --}}
                        <x-confirms-password wire:then="disableTwoFactorAuthentication" title="Cancelar configuración"
                            content="Ingresa tu contraseña para confirmar que deseas cancelar la configuración de autenticación en dos pasos."
                            button="Cancelar configuración">

                            <button type="button" wire:loading.attr="disabled" class="rm-btn-secondary">
                                <i class="
                                            ph-bold
                                            ph-x
                                        "></i>

                                Cancelar
                            </button>

                        </x-confirms-password>


                        {{-- Confirmar --}}
                        <x-confirms-password wire:then="confirmTwoFactorAuthentication" title="Confirmar autenticación"
                            content="Ingresa tu contraseña para autorizar la confirmación final de la autenticación en dos pasos."
                            button="Confirmar">

                            <button type="button" :disabled="!otpCompleto" wire:loading.attr="disabled"
                                wire:target="confirmTwoFactorAuthentication" class="
                                        rm-btn-accent
                                        disabled:cursor-not-allowed
                                        disabled:opacity-50
                                    ">

                                <span wire:loading.remove wire:target="confirmTwoFactorAuthentication" class="
                                            inline-flex
                                            items-center
                                            gap-2
                                        ">
                                    <i class="
                                                ph-bold
                                                ph-check-circle
                                            "></i>

                                    Confirmar activación
                                </span>


                                <span wire:loading wire:target="confirmTwoFactorAuthentication" class="
                                            items-center
                                            gap-2
                                        ">
                                    <i class="
                                                ph-bold
                                                ph-circle-notch
                                                animate-spin
                                            "></i>

                                    Confirmando...
                                </span>

                            </button>

                        </x-confirms-password>

                    </div>

                </section>

            </div>

        </div>


        {{-- ============================================================
        2FA ACTIVADO
        ============================================================= --}}
    @else

        <div class="
                    border-t
                    border-borde-suave
                    p-5
                ">

            {{-- Estado activo --}}
            <div class="
                        flex
                        flex-col
                        gap-4
                        rounded-2xl
                        border border-estado-exitoBorde
                        bg-estado-exitoBg
                        p-4
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
                                text-estado-exito
                            ">
                        <i class="
                                    ph-bold
                                    ph-shield-check
                                "></i>
                    </div>


                    <div>

                        <p class="
                                    text-sm
                                    font-black
                                    text-titulo
                                ">
                            Autenticación en dos pasos activa
                        </p>


                        <p class="
                                    mt-1
                                    max-w-2xl
                                    text-sm
                                    font-medium
                                    leading-6
                                    text-meta
                                ">
                            Al iniciar sesión podrás necesitar
                            el código temporal generado
                            por tu aplicación autenticadora.
                        </p>

                    </div>

                </div>


                <span class="
                            inline-flex
                            items-center
                            gap-1.5
                            self-start
                            rounded-full
                            bg-fondo-card
                            px-3
                            py-1.5
                            text-xs
                            font-black
                            text-estado-exito
                        ">
                    <span class="
                                h-2
                                w-2
                                rounded-full
                                bg-estado-exito
                            "></span>

                    Protegida
                </span>

            </div>


            {{-- =====================================================
            CÓDIGOS NO SOLICITADOS
            ====================================================== --}}
            @if (!$showingRecoveryCodes)

                <div class="
                                mt-5
                                grid
                                gap-4
                                lg:grid-cols-2
                            ">

                    {{-- Recovery codes --}}
                    <article class="
                                    rounded-2xl
                                    border border-borde-suave
                                    bg-fondo-cardSuave
                                    p-5
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
                                                ph-key
                                            "></i>
                            </div>


                            <div>

                                <p class="
                                                font-bold
                                                text-titulo
                                            ">
                                    Códigos de recuperación
                                </p>


                                <p class="
                                                mt-1
                                                text-sm
                                                font-medium
                                                leading-6
                                                text-meta
                                            ">
                                    Permiten recuperar el acceso
                                    si pierdes tu dispositivo
                                    o no puedes utilizar
                                    tu aplicación autenticadora.
                                </p>

                            </div>

                        </div>


                        <div class="
                                        mt-4
                                        flex
                                        items-start
                                        gap-2
                                        rounded-xl
                                        border border-borde-suave
                                        bg-fondo-card
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
                                Antes de acceder a los códigos,
                                RememberMind solicitará nuevamente
                                tu contraseña.
                            </p>

                        </div>


                        <div class="mt-5">

                            <x-confirms-password wire:then="showRecoveryCodes" title="Acceder a códigos de recuperación"
                                content="Ingresa tu contraseña para autorizar el acceso a tus códigos de recuperación. Estos códigos son información sensible."
                                button="Autorizar acceso">

                                <button type="button" wire:loading.attr="disabled" wire:target="showRecoveryCodes"
                                    class="rm-btn-secondary">

                                    <span wire:loading.remove wire:target="showRecoveryCodes" class="
                                                    inline-flex
                                                    items-center
                                                    gap-2
                                                ">
                                        <i class="
                                                        ph-bold
                                                        ph-lock-key-open
                                                    "></i>

                                        Acceder a códigos
                                    </span>


                                    <span wire:loading wire:target="showRecoveryCodes" class="
                                                    items-center
                                                    gap-2
                                                ">
                                        <i class="
                                                        ph-bold
                                                        ph-circle-notch
                                                        animate-spin
                                                    "></i>

                                        Verificando...
                                    </span>

                                </button>

                            </x-confirms-password>

                        </div>

                    </article>


                    {{-- Desactivar --}}
                    <article class="
                                    rounded-2xl
                                    border border-borde-suave
                                    bg-fondo-cardSuave
                                    p-5
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
                                            text-estado-peligro
                                        ">
                                <i class="
                                                ph-bold
                                                ph-shield-slash
                                            "></i>
                            </div>


                            <div>

                                <p class="
                                                font-bold
                                                text-titulo
                                            ">
                                    Desactivar protección
                                </p>


                                <p class="
                                                mt-1
                                                text-sm
                                                font-medium
                                                leading-6
                                                text-meta
                                            ">
                                    Al desactivar esta función,
                                    tu cuenta volverá a depender
                                    únicamente de la contraseña
                                    durante el inicio de sesión.
                                </p>

                            </div>

                        </div>


                        <div class="
                                        mt-4
                                        flex
                                        items-start
                                        gap-2
                                        rounded-xl
                                        border border-borde-suave
                                        bg-fondo-card
                                        p-3
                                        text-xs
                                        font-semibold
                                        leading-5
                                        text-meta
                                    ">

                            <i class="
                                            ph-bold
                                            ph-warning
                                            mt-0.5
                                            shrink-0
                                            text-estado-peligro
                                        "></i>

                            <p>
                                Esta operación requiere
                                confirmar tu contraseña.
                            </p>

                        </div>


                        <div class="mt-5">

                            <x-confirms-password wire:then="disableTwoFactorAuthentication"
                                title="Desactivar autenticación en dos pasos"
                                content="Ingresa tu contraseña para confirmar que deseas desactivar esta protección adicional."
                                button="Desactivar">

                                <button type="button" wire:loading.attr="disabled" wire:target="disableTwoFactorAuthentication"
                                    class="rm-btn-danger">

                                    <span wire:loading.remove wire:target="disableTwoFactorAuthentication" class="
                                                    inline-flex
                                                    items-center
                                                    gap-2
                                                ">
                                        <i class="
                                                        ph-bold
                                                        ph-shield-slash
                                                    "></i>

                                        Desactivar 2FA
                                    </span>


                                    <span wire:loading wire:target="disableTwoFactorAuthentication" class="
                                                    items-center
                                                    gap-2
                                                ">
                                        <i class="
                                                        ph-bold
                                                        ph-circle-notch
                                                        animate-spin
                                                    "></i>

                                        Desactivando...
                                    </span>

                                </button>

                            </x-confirms-password>

                        </div>

                    </article>

                </div>


                {{-- =====================================================
                CÓDIGOS AUTORIZADOS
                ====================================================== --}}
            @else

                <section class="
                                mt-5
                                overflow-hidden
                                rounded-2xl
                                border border-borde-suave
                                bg-fondo-cardSuave
                            ">

                    {{-- Header --}}
                    <div class="
                                    flex
                                    flex-col
                                    gap-4
                                    border-b
                                    border-borde-suave
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
                                                ph-key
                                            "></i>
                            </div>


                            <div>

                                <h3 class="
                                                font-outfit
                                                text-base
                                                font-extrabold
                                                text-titulo
                                            ">
                                    Códigos de recuperación
                                </h3>


                                <p class="
                                                mt-1
                                                max-w-xl
                                                text-xs
                                                font-medium
                                                leading-5
                                                text-meta
                                            ">
                                    Acceso autorizado.
                                    Los códigos permanecen ocultos
                                    hasta que decidas mostrarlos.
                                </p>

                            </div>

                        </div>


                        <div class="
                                        flex
                                        flex-wrap
                                        gap-2
                                    ">

                            <button type="button" @click="
                                            codigosVisibles
                                                ? ocultarCodigos()
                                                : mostrarCodigos()
                                        " class="rm-btn-secondary">

                                <i class="ph-bold" :class="
                                                codigosVisibles
                                                    ? 'ph-eye-slash'
                                                    : 'ph-eye'
                                            "></i>

                                <span x-text="
                                                codigosVisibles
                                                    ? 'Ocultar códigos'
                                                    : 'Mostrar códigos'
                                            "></span>

                            </button>

                        </div>

                    </div>


                    {{-- Advertencia --}}
                    <div class="
                                    m-5
                                    flex
                                    items-start
                                    gap-3
                                    rounded-xl
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
                                Información altamente sensible
                            </p>


                            <p class="
                                            mt-1
                                            text-xs
                                            font-semibold
                                            leading-5
                                            text-meta
                                        ">
                                Cada código permite recuperar el acceso
                                a tu cuenta. No los compartas,
                                no los envíes por mensajería
                                y evita almacenarlos en lugares públicos.
                            </p>

                        </div>

                    </div>


                    {{-- =================================================
                    CÓDIGOS
                    ================================================== --}}
                    <div class="px-5 pb-5">

                        @if (count($recoveryCodes) > 0)

                            <div class="
                                                grid
                                                gap-3
                                                sm:grid-cols-2
                                            ">

                                @foreach (
                                        $recoveryCodes as $indice => $recoveryCode
                                    )

                                    <div class="
                                                            flex
                                                            items-center
                                                            gap-3
                                                            rounded-xl
                                                            border border-borde-suave
                                                            bg-fondo-card
                                                            px-4
                                                            py-3
                                                        ">

                                        <div class="
                                                                flex
                                                                h-8
                                                                w-8
                                                                shrink-0
                                                                items-center
                                                                justify-center
                                                                rounded-lg
                                                                bg-fondo-cardSuave
                                                                text-[10px]
                                                                font-black
                                                                text-meta
                                                            ">
                                            {{
                                    str_pad(
                                        $indice + 1,
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    )
                                                            }}
                                        </div>


                                        <code class="
                                                                min-w-0
                                                                flex-1
                                                                truncate
                                                                font-mono
                                                                text-sm
                                                                font-bold
                                                                tracking-wide
                                                                text-apoyo
                                                            ">

                                                            <span
                                                                x-show="!codigosVisibles"
                                                            >
                                                                ••••••••••••••••
                                                            </span>


                                                            <span
                                                                x-cloak
                                                                x-show="codigosVisibles"
                                                            >
                                                                {{ $recoveryCode }}
                                                            </span>

                                                        </code>


                                        <button type="button" x-cloak x-show="codigosVisibles" @click="
                                                                copiarCodigo(
                                                                    @js($recoveryCode),
                                                                    {{ $indice }}
                                                                )
                                                            " class="rm-btn-icon" title="Copiar código"
                                            aria-label="Copiar código de recuperación {{ $indice + 1 }}">

                                            <i class="ph-bold" :class="
                                                                    copiado ===
                                                                        'codigo-{{ $indice }}'
                                                                        ? 'ph-check'
                                                                        : 'ph-copy'
                                                                "></i>

                                        </button>

                                    </div>

                                @endforeach

                            </div>


                            {{-- =============================================
                            ACCIONES DE CÓDIGOS
                            ============================================== --}}
                            <div class="
                                                mt-5
                                                flex
                                                flex-col
                                                gap-4
                                                border-t
                                                border-borde-suave
                                                pt-5
                                                lg:flex-row
                                                lg:items-center
                                                lg:justify-between
                                            ">

                                <div class="
                                                    flex
                                                    items-start
                                                    gap-2
                                                    text-xs
                                                    font-semibold
                                                    leading-5
                                                    text-meta
                                                ">

                                    <i class="
                                                        ph-bold
                                                        ph-info
                                                        mt-0.5
                                                        shrink-0
                                                        text-boton-acento
                                                    "></i>

                                    <p>
                                        Si generas códigos nuevos,
                                        los códigos actuales dejarán
                                        de ser válidos.
                                    </p>

                                </div>


                                <div class="
                                                    flex
                                                    flex-wrap
                                                    gap-2
                                                ">

                                    {{-- Copiar todos --}}
                                    <button type="button" x-show="codigosVisibles" @click="
                                                        copiarTodosLosCodigos()
                                                    " class="rm-btn-secondary">

                                        <i class="ph-bold" :class="
                                                            copiado === 'todos'
                                                                ? 'ph-check'
                                                                : 'ph-copy'
                                                        "></i>

                                        <span x-text="
                                                            copiado === 'todos'
                                                                ? 'Copiados'
                                                                : 'Copiar todos'
                                                        "></span>

                                    </button>


                                    {{-- Descargar --}}
                                    <button type="button" x-show="codigosVisibles" @click="
                                                        descargarCodigos()
                                                    " :disabled="descargando" class="
                                                        rm-btn-secondary
                                                        disabled:cursor-not-allowed
                                                        disabled:opacity-50
                                                    ">

                                        <i class="
                                                            ph-bold
                                                            ph-download-simple
                                                        "></i>

                                        Descargar .txt
                                    </button>


                                    {{-- Regenerar --}}
                                    <x-confirms-password wire:then="regenerateRecoveryCodes" title="Generar nuevos códigos"
                                        content="Ingresa tu contraseña para autorizar la generación de nuevos códigos de recuperación. Los códigos actuales dejarán de funcionar."
                                        button="Regenerar códigos">

                                        <button type="button" wire:loading.attr="disabled" wire:target="regenerateRecoveryCodes"
                                            class="rm-btn-secondary">

                                            <span wire:loading.remove wire:target="regenerateRecoveryCodes" class="
                                                                inline-flex
                                                                items-center
                                                                gap-2
                                                            ">
                                                <i class="
                                                                    ph-bold
                                                                    ph-arrows-clockwise
                                                                "></i>

                                                Regenerar
                                            </span>


                                            <span wire:loading wire:target="regenerateRecoveryCodes" class="
                                                                items-center
                                                                gap-2
                                                            ">
                                                <i class="
                                                                    ph-bold
                                                                    ph-circle-notch
                                                                    animate-spin
                                                                "></i>

                                                Generando...
                                            </span>

                                        </button>

                                    </x-confirms-password>

                                </div>

                            </div>

                        @else

                            <div class="
                                                rounded-xl
                                                border border-borde-suave
                                                bg-fondo-card
                                                p-5
                                                text-center
                                            ">

                                <i class="
                                                    ph-bold
                                                    ph-warning-circle
                                                    text-2xl
                                                    text-estado-advertencia
                                                "></i>


                                <p class="
                                                    mt-2
                                                    text-sm
                                                    font-bold
                                                    text-titulo
                                                ">
                                    No fue posible cargar los códigos
                                </p>


                                <p class="
                                                    mt-1
                                                    text-xs
                                                    font-medium
                                                    text-meta
                                                ">
                                    Puedes generar un nuevo conjunto
                                    de códigos de recuperación.
                                </p>


                                <div class="mt-4">

                                    <x-confirms-password wire:then="regenerateRecoveryCodes" title="Generar códigos de recuperación"
                                        content="Ingresa tu contraseña para generar un nuevo conjunto de códigos de recuperación."
                                        button="Generar códigos">

                                        <button type="button" class="rm-btn-secondary">
                                            <i class="
                                                                ph-bold
                                                                ph-arrows-clockwise
                                                            "></i>

                                            Generar códigos
                                        </button>

                                    </x-confirms-password>

                                </div>

                            </div>

                        @endif

                    </div>

                </section>


                {{-- Desactivar incluso mientras códigos están cargados --}}
                <div class="
                                mt-4
                                flex
                                justify-end
                            ">

                    <x-confirms-password wire:then="disableTwoFactorAuthentication"
                        title="Desactivar autenticación en dos pasos"
                        content="Ingresa tu contraseña para confirmar que deseas desactivar la autenticación en dos pasos."
                        button="Desactivar">

                        <button type="button" wire:loading.attr="disabled" wire:target="disableTwoFactorAuthentication"
                            class="rm-btn-danger">
                            <i class="
                                            ph-bold
                                            ph-shield-slash
                                        "></i>

                            Desactivar 2FA
                        </button>

                    </x-confirms-password>

                </div>

            @endif

        </div>

    @endif


    {{-- ============================================================
    PIE DE SEGURIDAD
    ============================================================= --}}
    <footer class="
            flex
            flex-col
            gap-2
            border-t
            border-borde-suave
            bg-fondo-cardSuave
            px-5
            py-4
            text-xs
            font-semibold
            leading-5
            text-meta
            sm:flex-row
            sm:items-center
            sm:justify-between
        ">

        <div class="
                flex
                items-center
                gap-2
            ">
            <i class="
                    ph-bold
                    ph-lock-key
                    text-boton-acento
                "></i>

            Operaciones sensibles protegidas
            mediante confirmación de identidad.
        </div>


        <div class="
                flex
                items-center
                gap-2
            ">
            <i class="
                    ph-bold
                    ph-shield-check
                    text-boton-acento
                "></i>

            RememberMind
        </div>

    </footer>

</section>