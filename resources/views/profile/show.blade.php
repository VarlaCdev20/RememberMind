<x-sistema-layout>
    @php
        /*
        |--------------------------------------------------------------------------
        | Usuario autenticado y relaciones necesarias
        |--------------------------------------------------------------------------
        |
        | Esta vista únicamente consulta datos.
        | Las operaciones sensibles continúan delegadas a Livewire / Fortify.
        |
        */

        $usuario = auth()->user();

        $usuario->loadMissing([
            'roles',
            'areaInstitucional',
            'horariosSalud',
            'horariosAdmin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Identidad visible
        |--------------------------------------------------------------------------
        */

        $nombreCompleto = trim(
            collect([
                $usuario->nombres,
                $usuario->ap_paterno,
                $usuario->ap_materno,
            ])
                ->filter()
                ->implode(' ')
        );

        $nombreCompleto = $nombreCompleto !== ''
            ? $nombreCompleto
            : 'Usuario RememberMind';


        /*
        |--------------------------------------------------------------------------
        | Rol institucional
        |--------------------------------------------------------------------------
        |
        | No mostramos códigos internos.
        | Transformamos los nombres técnicos de roles en etiquetas legibles.
        |
        */

        $rolPrincipal = mb_strtoupper(
            trim(
                (string) (
                    $usuario->rol_principal
                    ?? $usuario->roles->first()?->name
                    ?? ''
                )
            ),
            'UTF-8'
        );

        $rolVisible = match ($rolPrincipal) {
            'SUPERADMINISTRADOR' => 'Superadministrador',
            'ADMINISTRADOR' => 'Administrador',
            'MEDICO GENERAL/GERIATRA' => 'Médico / Geriatra',
            'PSICOLOGO/A' => 'Psicólogo/a',
            'ENFERMEROS' => 'Enfermería',
            'NUTRICIONISTA' => 'Nutricionista',
            'FISIOTERAPEUTA' => 'Fisioterapeuta',
            'PEDAGOGO' => 'Pedagogo/a',
            'VOLUNTARIO' => 'Voluntario/a',
            'FAMILIAR' => 'Familiar',
            default => $rolPrincipal !== ''
                ? \Illuminate\Support\Str::headline(
                    mb_strtolower($rolPrincipal, 'UTF-8')
                )
                : ($usuario->categoria_institucional ?? 'Usuario'),
        };


        /*
        |--------------------------------------------------------------------------
        | Área institucional
        |--------------------------------------------------------------------------
        */

        $areaVisible = $usuario->areaInstitucional?->nombre
            ?? 'Sin área asignada';


        /*
        |--------------------------------------------------------------------------
        | Tipo de vinculación
        |--------------------------------------------------------------------------
        */

        $tipoVinculacion = $usuario->tipo_vinculacion
            ? \Illuminate\Support\Str::headline(
                mb_strtolower(
                    $usuario->tipo_vinculacion,
                    'UTF-8'
                )
            )
            : match ($usuario->tipo_personal) {
                'salud' => 'Personal de salud',
                'admin' => 'Personal administrativo',
                default => 'Sin definir',
            };


        /*
        |--------------------------------------------------------------------------
        | Estado de la cuenta
        |--------------------------------------------------------------------------
        */

        $estadoNormalizado = mb_strtoupper(
            trim((string) $usuario->estado),
            'UTF-8'
        );

        $estadoActivo = $estadoNormalizado === 'ACTIVO';


        /*
        |--------------------------------------------------------------------------
        | Acceso al sistema
        |--------------------------------------------------------------------------
        |
        | Se aceptan valores históricos para evitar mostrar una cuenta
        | válida como bloqueada si existen registros antiguos con "SI".
        |
        */

        $accesoNormalizado = mb_strtoupper(
            trim((string) $usuario->acceso_sistema),
            'UTF-8'
        );

        $accesoHabilitado = in_array(
            $accesoNormalizado,
            [
                'HABILITADO',
                'SI',
                'SÍ',
                'ACTIVO',
                '1',
            ],
            true
        );


        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        */

        $twoFactorActivo = !empty($usuario->two_factor_secret);

        $debeCambiarPassword = (bool) $usuario->debe_cambiar_password;

        $passwordActualizada = $usuario->password_changed_at
            ? $usuario->password_changed_at
                ->locale('es')
                ->translatedFormat('d M Y · H:i')
            : null;

        $ultimoAcceso = $usuario->ultimo_acceso
            ? $usuario->ultimo_acceso
                ->locale('es')
                ->translatedFormat('d M Y · H:i')
            : null;

        $ultimoAccesoRelativo = $usuario->ultimo_acceso
            ? $usuario->ultimo_acceso
                ->locale('es')
                ->diffForHumans()
            : null;


        /*
        |--------------------------------------------------------------------------
        | Saludo contextual
        |--------------------------------------------------------------------------
        */

        $horaActual = now()->hour;

        $saludoPerfil = match (true) {
            $horaActual >= 5 && $horaActual < 12 => 'Buenos días',
            $horaActual >= 12 && $horaActual < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        $fechaActual = now()
            ->locale('es')
            ->translatedFormat('l, d \d\e F');


        /*
        |--------------------------------------------------------------------------
        | Mensajes institucionales
        |--------------------------------------------------------------------------
        |
        | La frase se selecciona mediante usuario + fecha.
        |
        | Así:
        | - cada persona puede recibir una frase diferente;
        | - cambia cada día;
        | - no cambia durante cada render de Livewire;
        | - no requiere base de datos.
        |
        */

        $frasesPerfil = [
            [
                'icono' => 'ph-heart',
                'titulo' => 'Cuidado con propósito',
                'texto' => 'Cada acción cotidiana puede convertirse en una forma de acompañar, cuidar y generar confianza.',
            ],
            [
                'icono' => 'ph-brain',
                'titulo' => 'Memoria y bienestar',
                'texto' => 'Acompañar también significa comprender la historia, las capacidades y las necesidades de cada persona.',
            ],
            [
                'icono' => 'ph-hands-clapping',
                'titulo' => 'Trabajo en equipo',
                'texto' => 'El cuidado integral se construye cuando cada profesional aporta desde su experiencia y responsabilidad.',
            ],
            [
                'icono' => 'ph-heartbeat',
                'titulo' => 'Atención humana',
                'texto' => 'La calidad del cuidado también está en los pequeños detalles, la escucha y el trato respetuoso.',
            ],
            [
                'icono' => 'ph-users-three',
                'titulo' => 'Acompañamiento',
                'texto' => 'Cada registro y cada seguimiento contribuyen a una atención más organizada, segura y personalizada.',
            ],
            [
                'icono' => 'ph-leaf',
                'titulo' => 'Bienestar',
                'texto' => 'Promover bienestar significa cuidar la salud, la autonomía, los vínculos y la dignidad de cada residente.',
            ],
            [
                'icono' => 'ph-shield-check',
                'titulo' => 'Compromiso profesional',
                'texto' => 'La responsabilidad fortalece la confianza de residentes, familias y de todo el equipo institucional.',
            ],
            [
                'icono' => 'ph-sun',
                'titulo' => 'Cada día cuenta',
                'texto' => 'Una atención cercana y organizada puede hacer que cada jornada sea más segura y significativa.',
            ],
            [
                'icono' => 'ph-hand-heart',
                'titulo' => 'Cuidar es acompañar',
                'texto' => 'El respeto, la paciencia y la empatía forman parte esencial de una atención verdaderamente integral.',
            ],
            [
                'icono' => 'ph-sparkle',
                'titulo' => 'Nuestro propósito',
                'texto' => 'Tecnología y cuidado se unen para apoyar decisiones más claras y una atención centrada en la persona.',
            ],
            [
                'icono' => 'ph-flower-lotus',
                'titulo' => 'Dignidad siempre',
                'texto' => 'Toda persona merece ser escuchada, respetada y acompañada reconociendo su historia y sus decisiones.',
            ],
            [
                'icono' => 'ph-tree',
                'titulo' => 'Construimos confianza',
                'texto' => 'La constancia, la comunicación y el respeto fortalecen los vínculos que hacen posible un mejor cuidado.',
            ],
            [
                'icono' => 'ph-chat-circle-dots',
                'titulo' => 'Escuchar también es cuidar',
                'texto' => 'Una conversación atenta puede revelar necesidades que ningún formulario podría mostrar por sí solo.',
            ],
            [
                'icono' => 'ph-smiley',
                'titulo' => 'Pequeños gestos',
                'texto' => 'Una palabra amable, una explicación clara y unos minutos de atención también forman parte del cuidado.',
            ],
            [
                'icono' => 'ph-check-circle',
                'titulo' => 'Información que protege',
                'texto' => 'Registrar correctamente la información ayuda a tomar mejores decisiones y dar continuidad a la atención.',
            ],
            [
                'icono' => 'ph-puzzle-piece',
                'titulo' => 'Cada profesional aporta',
                'texto' => 'El bienestar integral nace de múltiples miradas trabajando con un mismo propósito.',
            ],
            [
                'icono' => 'ph-handshake',
                'titulo' => 'Cuidamos en equipo',
                'texto' => 'Compartir información relevante y coordinar responsabilidades mejora la experiencia de quienes acompañamos.',
            ],
            [
                'icono' => 'ph-compass',
                'titulo' => 'Decisiones con sentido',
                'texto' => 'Los buenos datos orientan el trabajo, pero la persona siempre permanece en el centro de cada decisión.',
            ],
        ];

        $semillaFrase = (
            (string) $usuario->cod_usu
            . '|'
            . now()->format('Y-m-d')
        );

        $indiceFrase = abs(
            crc32($semillaFrase)
        ) % count($frasesPerfil);

        $frasePerfil = $frasesPerfil[$indiceFrase];


        /*
        |--------------------------------------------------------------------------
        | Horarios institucionales
        |--------------------------------------------------------------------------
        */

        $ordenDias = [
            'LUNES' => 1,
            'MARTES' => 2,
            'MIERCOLES' => 3,
            'MIÉRCOLES' => 3,
            'JUEVES' => 4,
            'VIERNES' => 5,
            'SABADO' => 6,
            'SÁBADO' => 6,
            'DOMINGO' => 7,
        ];

        $normalizarDia = static function (?string $dia): string {
            $dia = mb_strtoupper(
                trim((string) $dia),
                'UTF-8'
            );

            return str_replace(
                ['Á', 'É', 'Í', 'Ó', 'Ú'],
                ['A', 'E', 'I', 'O', 'U'],
                $dia
            );
        };

        $diaActual = match (now()->dayOfWeekIso) {
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIERCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO',
            7 => 'DOMINGO',
        };

        $coleccionHorarios = $usuario->tipo_personal === 'salud'
            ? $usuario->horariosSalud
            : $usuario->horariosAdmin;

        $horarios = $coleccionHorarios
            ->where('estado', 'ACTIVO')
            ->sortBy(function ($horario) use ($ordenDias, $normalizarDia) {
                $dia = $normalizarDia(
                    $horario->dia_semana
                );

                $orden = $ordenDias[$dia] ?? 99;

                $inicio = $horario->hora_inicio
                    ? \Carbon\Carbon::parse(
                        $horario->hora_inicio
                    )->format('H:i')
                    : '99:99';

                return sprintf(
                    '%02d-%s',
                    $orden,
                    $inicio
                );
            })
            ->values();

        $horariosHoy = $horarios
            ->filter(
                fn($horario) =>
                    $normalizarDia(
                        $horario->dia_semana
                    ) === $diaActual
            )
            ->values();

        $cantidadHorarios = $horarios->count();


        /*
        |--------------------------------------------------------------------------
        | Dirección resumida
        |--------------------------------------------------------------------------
        */

        $partesDireccion = collect([
            $usuario->calle
            ? trim(
                $usuario->calle
                . (
                    $usuario->nro_domicilio
                    ? ' N.º ' . $usuario->nro_domicilio
                    : ''
                )
            )
            : null,

            $usuario->zona,
            $usuario->ciudad,
        ])->filter();

        $direccionResumen = $partesDireccion->isNotEmpty()
            ? $partesDireccion->implode(' · ')
            : ($usuario->direccion ?: 'Sin domicilio registrado');


        /*
        |--------------------------------------------------------------------------
        | Contacto
        |--------------------------------------------------------------------------
        */

        $telefonoVisible = $usuario->telefono
            ? trim(
                ($usuario->codigo_telefono
                    ? $usuario->codigo_telefono . ' '
                    : ''
                )
                . $usuario->telefono
            )
            : 'Sin teléfono registrado';


        /*
        |--------------------------------------------------------------------------
        | Emergencia
        |--------------------------------------------------------------------------
        */

        $contactoEmergenciaVisible = trim(
            collect([
                $usuario->contacto_emergencia,
                $usuario->ap_paterno_emergencia,
                $usuario->ap_materno_emergencia,
            ])
                ->filter()
                ->implode(' ')
        );

        $contactoEmergenciaVisible = $contactoEmergenciaVisible !== ''
            ? $contactoEmergenciaVisible
            : 'Sin contacto registrado';


        /*
        |--------------------------------------------------------------------------
        | Datos básicos disponibles
        |--------------------------------------------------------------------------
        |
        | Solo se utiliza como referencia visual.
        | No representa una evaluación administrativa.
        |
        */

        $datosPersonalesCompletos = collect([
            $usuario->telefono,
            $usuario->calle ?: $usuario->direccion,
            $usuario->ciudad,
            $usuario->contacto_emergencia,
            $usuario->parentesco_emergencia,
            $usuario->celular_emergencia,
        ])->filter()->count();

        $totalDatosPersonales = 6;

        $porcentajeDatos = (int) round(
            ($datosPersonalesCompletos / $totalDatosPersonales) * 100
        );
    @endphp


    <div x-data="{
            tabs: ['personal', 'institucional', 'seguridad'],

            tabPerfil:
                sessionStorage.getItem('remembermind-profile-tab')
                || 'personal',

            cambiarTab(tab) {
                if (!this.tabs.includes(tab)) {
                    tab = 'personal';
                }

                this.tabPerfil = tab;

                sessionStorage.setItem(
                    'remembermind-profile-tab',
                    tab
                );

                this.$nextTick(() => {
                    const contenido = document.getElementById(
                        'perfil-panel-' + tab
                    );

                    if (contenido) {
                        contenido.focus({
                            preventScroll: true
                        });
                    }
                });
            },

            siguienteTab() {
                const indice = this.tabs.indexOf(this.tabPerfil);

                this.cambiarTab(
                    this.tabs[
                        (indice + 1) % this.tabs.length
                    ]
                );
            },

            anteriorTab() {
                const indice = this.tabs.indexOf(this.tabPerfil);

                this.cambiarTab(
                    this.tabs[
                        (indice - 1 + this.tabs.length)
                        % this.tabs.length
                    ]
                );
            },
        }" x-init="
            if (!tabs.includes(tabPerfil)) {
                cambiarTab('personal');
            }
        " class="mx-auto max-w-7xl space-y-5">

        {{-- =========================================================
        CABECERA GENERAL
        ========================================================== --}}
        <section class="
                card-interactiva
                relative
                overflow-hidden
                rounded-[2rem]
                border border-borde-suave
                p-5
                sm:p-6
                lg:p-7
            ">

            {{-- Decoración sutil --}}
            <div aria-hidden="true" class="
                    pointer-events-none
                    absolute
                    -right-20
                    -top-20
                    h-64
                    w-64
                    rounded-full
                    bg-boton-acento/5
                "></div>

            <div aria-hidden="true" class="
                    pointer-events-none
                    absolute
                    -bottom-24
                    left-1/3
                    h-56
                    w-56
                    rounded-full
                    bg-estado-exito/5
                "></div>


            <div class="
                    relative
                    grid
                    gap-6
                    lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]
                    lg:items-center
                ">

                {{-- =================================================
                IDENTIDAD
                ================================================== --}}
                <div class="
                        flex
                        min-w-0
                        flex-col
                        gap-5
                        sm:flex-row
                        sm:items-center
                    ">

                    {{-- Foto --}}
                    <div class="relative w-fit shrink-0">

                        <img src="{{ $usuario->profile_photo_url }}" alt="Fotografía de {{ $nombreCompleto }}" class="
                                h-24
                                w-24
                                rounded-[1.6rem]
                                border-4
                                border-fondo-card
                                object-cover
                                shadow-card
                                sm:h-28
                                sm:w-28
                            ">


                        {{-- Estado discreto --}}
                        @if ($estadoActivo && $accesoHabilitado)
                            <span title="Cuenta activa" aria-label="Cuenta activa" class="
                                        absolute
                                        -bottom-1
                                        -right-1
                                        flex
                                        h-8
                                        w-8
                                        items-center
                                        justify-center
                                        rounded-full
                                        border-4
                                        border-fondo-card
                                        bg-estado-exito
                                        text-inverso
                                        shadow-card
                                    ">
                                <i class="
                                            ph-bold
                                            ph-check
                                            text-xs
                                        "></i>
                            </span>
                        @endif

                    </div>


                    {{-- Información principal --}}
                    <div class="min-w-0">

                        <div class="
                                mb-3
                                flex
                                flex-wrap
                                items-center
                                gap-2
                            ">
                            <span class="badge-mint">
                                CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                            </span>

                            <span class="rm-badge-neutral">
                                {{ $rolVisible }}
                            </span>
                        </div>


                        <div class="
                                flex
                                items-center
                                gap-2
                            ">
                            <div class="
                                    flex
                                    h-8
                                    w-8
                                    shrink-0
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-boton-acento
                                    text-inverso
                                ">
                                <i class="
                                        ph-bold
                                        ph-user-circle
                                    "></i>
                            </div>

                            <span class="
                                    text-xs
                                    font-black
                                    uppercase
                                    tracking-[0.18em]
                                    text-boton-acento
                                ">
                                Mi perfil
                            </span>
                        </div>


                        <h1 class="
                                mt-2
                                font-outfit
                                text-2xl
                                font-extrabold
                                tracking-tight
                                text-titulo
                                sm:text-3xl
                            ">
                            {{ $saludoPerfil }},
                            <span class="text-boton-acento">
                                {{ $usuario->nombres ?: $nombreCompleto }}
                            </span>
                        </h1>


                        <p class="
                                mt-1
                                truncate
                                text-base
                                font-bold
                                text-apoyo
                            ">
                            {{ $nombreCompleto }}
                        </p>


                        <p class="
                                mt-2
                                max-w-2xl
                                text-sm
                                font-medium
                                leading-6
                                text-meta
                            ">
                            Consulta tu información personal,
                            revisa tu asignación institucional
                            y administra las opciones de seguridad
                            de tu cuenta.
                        </p>


                        <div class="
                                mt-3
                                flex
                                flex-wrap
                                items-center
                                gap-x-4
                                gap-y-2
                                text-xs
                                font-bold
                                text-meta
                            ">
                            <span class="
                                    inline-flex
                                    items-center
                                    gap-1.5
                                ">
                                <i class="
                                        ph-bold
                                        ph-calendar-blank
                                        text-boton-acento
                                    "></i>

                                {{ ucfirst($fechaActual) }}
                            </span>

                            <span class="
                                    inline-flex
                                    items-center
                                    gap-1.5
                                ">
                                <i class="
                                        ph-bold
                                        ph-buildings
                                        text-boton-acento
                                    "></i>

                                {{ $areaVisible }}
                            </span>
                        </div>

                    </div>

                </div>


                {{-- =================================================
                MENSAJE DEL DÍA
                ================================================== --}}
                <aside class="
                        relative
                        overflow-hidden
                        rounded-[1.5rem]
                        border border-borde-suave
                        bg-fondo-cardSuave
                        p-5
                    ">

                    <div aria-hidden="true" class="
                            pointer-events-none
                            absolute
                            -right-10
                            -top-10
                            h-32
                            w-32
                            rounded-full
                            bg-boton-acento/5
                        "></div>


                    <div class="relative">

                        <div class="
                                flex
                                items-start
                                justify-between
                                gap-4
                            ">

                            <div class="
                                    flex
                                    items-center
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
                                        shadow-card
                                    ">
                                    <i class="
                                            ph-bold
                                            {{ $frasePerfil['icono'] }}
                                            text-xl
                                        "></i>
                                </div>


                                <div>
                                    <p class="
                                            text-[10px]
                                            font-black
                                            uppercase
                                            tracking-[0.18em]
                                            text-meta
                                        ">
                                        Mensaje para hoy
                                    </p>

                                    <h2 class="
                                            mt-0.5
                                            font-outfit
                                            text-base
                                            font-extrabold
                                            text-titulo
                                        ">
                                        {{ $frasePerfil['titulo'] }}
                                    </h2>
                                </div>
                            </div>


                            <i aria-hidden="true" class="
                                    ph-fill
                                    ph-quotes
                                    text-3xl
                                    text-boton-acento/30
                                "></i>

                        </div>


                        <p class="
                                mt-4
                                text-sm
                                font-medium
                                leading-6
                                text-apoyo
                            ">
                            {{ $frasePerfil['texto'] }}
                        </p>


                        <div class="
                                mt-4
                                flex
                                items-center
                                gap-2
                                border-t
                                border-borde-suave
                                pt-3
                            ">
                            <i class="
                                    ph-bold
                                    ph-heart
                                    text-boton-acento
                                "></i>

                            <span class="
                                    text-[10px]
                                    font-black
                                    uppercase
                                    tracking-wider
                                    text-meta
                                ">
                                RememberMind · Cuidado, memoria y dignidad
                            </span>
                        </div>

                    </div>

                </aside>

            </div>
        </section>


        {{-- =========================================================
        NAVEGACIÓN
        ========================================================== --}}
        <nav aria-label="Secciones del perfil" role="tablist" class="
                sticky
                top-[4.5rem]
                z-20
                grid
                overflow-hidden
                rounded-2xl
                border border-borde-suave
                bg-fondo-cardSuave
                shadow-card
                backdrop-blur-xl
                sm:grid-cols-3
            " @keydown.right.prevent="siguienteTab()" @keydown.left.prevent="anteriorTab()">

            {{-- Personal --}}
            <button id="perfil-tab-personal" type="button" role="tab" aria-controls="perfil-panel-personal"
                @click="cambiarTab('personal')" :aria-selected="tabPerfil === 'personal'"
                :tabindex="tabPerfil === 'personal' ? 0 : -1" :class="
                    tabPerfil === 'personal'
                        ? 'bg-boton-acento text-inverso shadow-card'
                        : 'text-apoyo hover:bg-fondo-hover'
                " class="
                    flex
                    items-center
                    justify-center
                    gap-2.5
                    px-4
                    py-3.5
                    text-sm
                    font-black
                    transition-colors
                ">
                <i class="
                        ph-bold
                        ph-user-circle
                        text-lg
                    "></i>

                <span>
                    Información personal
                </span>
            </button>


            {{-- Institucional --}}
            <button id="perfil-tab-institucional" type="button" role="tab" aria-controls="perfil-panel-institucional"
                @click="cambiarTab('institucional')" :aria-selected="tabPerfil === 'institucional'"
                :tabindex="tabPerfil === 'institucional' ? 0 : -1" :class="
                    tabPerfil === 'institucional'
                        ? 'bg-boton-acento text-inverso shadow-card'
                        : 'text-apoyo hover:bg-fondo-hover'
                " class="
                    flex
                    items-center
                    justify-center
                    gap-2.5
                    border-y
                    border-borde-suave
                    px-4
                    py-3.5
                    text-sm
                    font-black
                    transition-colors
                    sm:border-x
                    sm:border-y-0
                ">
                <i class="
                        ph-bold
                        ph-buildings
                        text-lg
                    "></i>

                <span>
                    Información institucional
                </span>
            </button>


            {{-- Seguridad --}}
            <button id="perfil-tab-seguridad" type="button" role="tab" aria-controls="perfil-panel-seguridad"
                @click="cambiarTab('seguridad')" :aria-selected="tabPerfil === 'seguridad'"
                :tabindex="tabPerfil === 'seguridad' ? 0 : -1" :class="
                    tabPerfil === 'seguridad'
                        ? 'bg-boton-acento text-inverso shadow-card'
                        : 'text-apoyo hover:bg-fondo-hover'
                " class="
                    flex
                    items-center
                    justify-center
                    gap-2.5
                    px-4
                    py-3.5
                    text-sm
                    font-black
                    transition-colors
                ">
                <i class="
                        ph-bold
                        ph-shield-check
                        text-lg
                    "></i>

                <span>
                    Seguridad y acceso
                </span>
            </button>

        </nav>


        {{-- =========================================================
        INFORMACIÓN PERSONAL
        ========================================================== --}}
        <section id="perfil-panel-personal" x-cloak x-show="tabPerfil === 'personal'"
            x-transition.opacity.duration.150ms role="tabpanel" aria-labelledby="perfil-tab-personal" tabindex="-1"
            class="space-y-4 outline-none">

            {{-- Introducción --}}
            <article class="
                    panel-institucional
                    rounded-[1.4rem]
                    border border-borde-suave
                    p-5
                ">

                <div class="
                        flex
                        flex-col
                        gap-4
                        lg:flex-row
                        lg:items-center
                        lg:justify-between
                    ">

                    <div>
                        <div class="
                                flex
                                items-center
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
                                        ph-user-circle
                                        text-xl
                                    "></i>
                            </div>

                            <div>
                                <h2 class="
                                        font-outfit
                                        text-xl
                                        font-extrabold
                                        text-titulo
                                    ">
                                    Información personal
                                </h2>

                                <p class="
                                        mt-0.5
                                        text-sm
                                        font-medium
                                        text-meta
                                    ">
                                    Consulta tu identidad y administra únicamente
                                    los datos personales que tienes autorizados.
                                </p>
                            </div>
                        </div>
                    </div>


                    {{-- Estado general de datos --}}
                    <div class="
                            min-w-[240px]
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            px-4
                            py-3
                        ">
                        <div class="
                                flex
                                items-center
                                justify-between
                                gap-4
                            ">
                            <div>
                                <p class="
                                        text-[10px]
                                        font-black
                                        uppercase
                                        tracking-wider
                                        text-meta
                                    ">
                                    Datos de contacto
                                </p>

                                <p class="
                                        mt-1
                                        text-sm
                                        font-bold
                                        text-titulo
                                    ">
                                    {{ $porcentajeDatos }}% registrado
                                </p>
                            </div>

                            <div class="
                                    flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-fondo-card
                                    text-boton-acento
                                ">
                                <i class="
                                        ph-bold
                                        ph-checklist
                                    "></i>
                            </div>
                        </div>


                        <div class="
                                mt-3
                                h-1.5
                                overflow-hidden
                                rounded-full
                                bg-fondo-card
                            ">
                            <div class="
                                    h-full
                                    rounded-full
                                    bg-boton-acento
                                    transition-all
                                " style="width: {{ $porcentajeDatos }}%"></div>
                        </div>
                    </div>

                </div>


                {{-- Resumen no editable --}}
                <div class="
                        mt-5
                        grid
                        gap-3
                        md:grid-cols-3
                    ">

                    <div class="
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
                            <i class="
                                    ph-bold
                                    ph-phone
                                    mt-0.5
                                    text-lg
                                    text-boton-acento
                                "></i>

                            <div class="min-w-0">
                                <p class="
                                        text-[10px]
                                        font-black
                                        uppercase
                                        tracking-wider
                                        text-meta
                                    ">
                                    Teléfono
                                </p>

                                <p class="
                                        mt-1
                                        truncate
                                        text-sm
                                        font-bold
                                        text-titulo
                                    ">
                                    {{ $telefonoVisible }}
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="
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
                            <i class="
                                    ph-bold
                                    ph-map-pin
                                    mt-0.5
                                    text-lg
                                    text-boton-acento
                                "></i>

                            <div class="min-w-0">
                                <p class="
                                        text-[10px]
                                        font-black
                                        uppercase
                                        tracking-wider
                                        text-meta
                                    ">
                                    Domicilio
                                </p>

                                <p class="
                                        mt-1
                                        line-clamp-2
                                        text-sm
                                        font-bold
                                        leading-5
                                        text-titulo
                                    ">
                                    {{ $direccionResumen }}
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="
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
                            <i class="
                                    ph-bold
                                    ph-users-three
                                    mt-0.5
                                    text-lg
                                    text-boton-acento
                                "></i>

                            <div class="min-w-0">
                                <p class="
                                        text-[10px]
                                        font-black
                                        uppercase
                                        tracking-wider
                                        text-meta
                                    ">
                                    Contacto de emergencia
                                </p>

                                <p class="
                                        mt-1
                                        truncate
                                        text-sm
                                        font-bold
                                        text-titulo
                                    ">
                                    {{ $contactoEmergenciaVisible }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </article>


            {{-- =====================================================
            COMPONENTE DE INFORMACIÓN PERSONAL
            ====================================================== --}}
            @if (
                    Laravel\Fortify\Features::canUpdateProfileInformation()
                )

                <div class="
                            overflow-hidden
                            rounded-[1.4rem]
                        ">
                    @livewire(
                        'profile.update-profile-information-form'
                    )
                </div>

            @else

                <article class="
                            panel-institucional
                            rounded-[1.4rem]
                            border border-borde-suave
                            p-5
                        ">
                    <div class="
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
                                    ph-lock-simple
                                    mt-0.5
                                    text-xl
                                    text-boton-acento
                                "></i>

                        <div>
                            <p class="
                                        font-bold
                                        text-titulo
                                    ">
                                Información personal protegida
                            </p>

                            <p class="
                                        mt-1
                                        text-sm
                                        font-medium
                                        leading-6
                                        text-meta
                                    ">
                                La edición directa de información personal
                                no se encuentra habilitada para esta cuenta.
                            </p>
                        </div>
                    </div>
                </article>

            @endif

        </section>


        {{-- =========================================================
        INFORMACIÓN INSTITUCIONAL
        ========================================================== --}}
        <section id="perfil-panel-institucional" x-cloak x-show="tabPerfil === 'institucional'"
            x-transition.opacity.duration.150ms role="tabpanel" aria-labelledby="perfil-tab-institucional" tabindex="-1"
            class="space-y-4 outline-none">

            {{-- Asignación --}}
            <article class="
                    panel-institucional
                    rounded-[1.4rem]
                    border border-borde-suave
                    p-5
                ">

                <div class="
                        flex
                        flex-col
                        gap-4
                        lg:flex-row
                        lg:items-start
                        lg:justify-between
                    ">

                    <div>
                        <div class="
                                flex
                                items-center
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
                                        ph-buildings
                                        text-xl
                                    "></i>
                            </div>

                            <div>
                                <h2 class="
                                        font-outfit
                                        text-xl
                                        font-extrabold
                                        text-titulo
                                    ">
                                    Información institucional
                                </h2>

                                <p class="
                                        mt-0.5
                                        text-sm
                                        font-medium
                                        text-meta
                                    ">
                                    Consulta tu asignación y horario
                                    dentro del centro geriátrico.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="
                            flex
                            items-start
                            gap-2
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            px-4
                            py-3
                            text-xs
                            font-semibold
                            leading-5
                            text-meta
                        ">
                        <i class="
                                ph-bold
                                ph-lock-simple
                                mt-0.5
                                shrink-0
                                text-boton-acento
                            "></i>

                        <span>
                            Los cambios en esta sección
                            son gestionados por Administración.
                        </span>
                    </div>

                </div>


                {{-- Tarjetas --}}
                <div class="
                        mt-5
                        grid
                        gap-4
                        md:grid-cols-3
                    ">

                    {{-- Rol --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-10
                                w-10
                                items-center
                                justify-center
                                rounded-xl
                                bg-fondo-card
                                text-boton-acento
                            ">
                            <i class="
                                    ph-bold
                                    ph-identification-badge
                                "></i>
                        </div>

                        <p class="
                                text-[11px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Rol institucional
                        </p>

                        <p class="
                                mt-1
                                font-bold
                                text-titulo
                            ">
                            {{ $rolVisible }}
                        </p>
                    </div>


                    {{-- Área --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-10
                                w-10
                                items-center
                                justify-center
                                rounded-xl
                                bg-fondo-card
                                text-boton-acento
                            ">
                            <i class="
                                    ph-bold
                                    ph-users-three
                                "></i>
                        </div>

                        <p class="
                                text-[11px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Área institucional
                        </p>

                        <p class="
                                mt-1
                                font-bold
                                leading-5
                                text-titulo
                            ">
                            {{ $areaVisible }}
                        </p>
                    </div>


                    {{-- Vinculación --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-10
                                w-10
                                items-center
                                justify-center
                                rounded-xl
                                bg-fondo-card
                                text-boton-acento
                            ">
                            <i class="
                                    ph-bold
                                    ph-briefcase
                                "></i>
                        </div>

                        <p class="
                                text-[11px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Tipo de vinculación
                        </p>

                        <p class="
                                mt-1
                                font-bold
                                text-titulo
                            ">
                            {{ $tipoVinculacion }}
                        </p>
                    </div>

                </div>

            </article>


            {{-- Resumen horario --}}
            <article class="
                    panel-institucional
                    rounded-[1.4rem]
                    border border-borde-suave
                    p-5
                ">

                <div class="
                        flex
                        flex-col
                        gap-4
                        sm:flex-row
                        sm:items-start
                        sm:justify-between
                    ">

                    <div>
                        <h2 class="
                                flex
                                items-center
                                gap-2
                                font-outfit
                                text-xl
                                font-extrabold
                                text-titulo
                            ">
                            <i class="
                                    ph-bold
                                    ph-clock
                                    text-boton-acento
                                "></i>

                            Horario asignado
                        </h2>

                        <p class="
                                mt-1
                                text-sm
                                font-medium
                                text-meta
                            ">
                            Jornadas institucionales
                            actualmente registradas como activas.
                        </p>
                    </div>


                    @if ($cantidadHorarios > 0)
                                    <span class="
                                                inline-flex
                                                items-center
                                                gap-1.5
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
                                                    ph-calendar-check
                                                    text-boton-acento
                                                "></i>

                                        {{ $cantidadHorarios }}

                                        {{
                        $cantidadHorarios === 1
                        ? 'jornada activa'
                        : 'jornadas activas'
                                            }}
                                    </span>
                    @endif

                </div>


                {{-- Hoy --}}
                @if ($horariosHoy->isNotEmpty())
                            <div class="
                                        mt-5
                                        rounded-2xl
                                        border border-estado-exitoBorde
                                        bg-estado-exitoBg
                                        p-4
                                    ">
                                <div class="
                                            flex
                                            flex-wrap
                                            items-center
                                            justify-between
                                            gap-3
                                        ">
                                    <div class="
                                                flex
                                                items-center
                                                gap-3
                                            ">
                                        <div class="
                                                    flex
                                                    h-10
                                                    w-10
                                                    items-center
                                                    justify-center
                                                    rounded-xl
                                                    bg-fondo-card
                                                    text-estado-exito
                                                ">
                                            <i class="
                                                        ph-bold
                                                        ph-calendar-check
                                                    "></i>
                                        </div>

                                        <div>
                                            <p class="
                                                        text-[10px]
                                                        font-black
                                                        uppercase
                                                        tracking-wider
                                                        text-estado-exito
                                                    ">
                                                Jornada de hoy
                                            </p>

                                            <p class="
                                                        mt-0.5
                                                        text-sm
                                                        font-bold
                                                        text-titulo
                                                    ">
                                                Tienes
                                                {{ $horariosHoy->count() }}

                                                {{
                    $horariosHoy->count() === 1
                    ? 'horario activo'
                    : 'horarios activos'
                                                    }}
                                                para hoy.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                @endif


                @if ($horarios->isEmpty())

                    <div class="
                                mt-5
                                rounded-2xl
                                border border-dashed border-borde-suave
                                bg-fondo-cardSuave
                                p-7
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
                                    bg-fondo-card
                                    text-meta
                                ">
                            <i class="
                                        ph-bold
                                        ph-calendar-x
                                        text-2xl
                                    "></i>
                        </div>

                        <p class="
                                    mt-3
                                    font-bold
                                    text-titulo
                                ">
                            Sin horario activo
                        </p>

                        <p class="
                                    mx-auto
                                    mt-1
                                    max-w-md
                                    text-sm
                                    font-medium
                                    leading-6
                                    text-meta
                                ">
                            Actualmente no tienes una jornada activa
                            registrada en el sistema.
                        </p>
                    </div>

                @else

                    <div class="
                                mt-5
                                overflow-hidden
                                rounded-2xl
                                border border-borde-suave
                            ">

                        <div class="overflow-x-auto">

                            <table class="
                                        min-w-full
                                        text-left
                                        text-sm
                                    ">

                                <thead class="
                                            bg-fondo-panel
                                            text-[11px]
                                            font-black
                                            uppercase
                                            tracking-wider
                                            text-meta
                                        ">
                                    <tr>
                                        <th class="px-4 py-3">
                                            Día
                                        </th>

                                        <th class="px-4 py-3">
                                            Turno
                                        </th>

                                        <th class="px-4 py-3">
                                            Inicio
                                        </th>

                                        <th class="px-4 py-3">
                                            Fin
                                        </th>

                                        <th class="px-4 py-3">
                                            Estado
                                        </th>
                                    </tr>
                                </thead>


                                <tbody class="
                                            divide-y
                                            divide-borde-suave
                                            bg-fondo-cardSuave
                                        ">
                                    @foreach ($horarios as $horario)
                                                            @php
                                                                $esHoy =
                                                                    $normalizarDia(
                                                                        $horario->dia_semana
                                                                    ) === $diaActual;

                                                                $diaVisible =
                                                                    \Illuminate\Support\Str::headline(
                                                                        mb_strtolower(
                                                                            (string) $horario->dia_semana,
                                                                            'UTF-8'
                                                                        )
                                                                    );

                                                                $turnoVisible = $horario->turno
                                                                    ? \Illuminate\Support\Str::headline(
                                                                        mb_strtolower(
                                                                            (string) $horario->turno,
                                                                            'UTF-8'
                                                                        )
                                                                    )
                                                                    : 'Sin turno';

                                                                $horaInicio = $horario->hora_inicio
                                                                    ? \Carbon\Carbon::parse(
                                                                        $horario->hora_inicio
                                                                    )->format('H:i')
                                                                    : '--:--';

                                                                $horaFin = $horario->hora_fin
                                                                    ? \Carbon\Carbon::parse(
                                                                        $horario->hora_fin
                                                                    )->format('H:i')
                                                                    : '--:--';
                                                            @endphp

                                                            <tr class="
                                                                            transition-colors
                                                                            {{ $esHoy
                                        ? 'bg-estado-exitoBg/40'
                                        : ''
                                                                            }}
                                                                        ">
                                                                <td class="
                                                                                px-4
                                                                                py-3
                                                                                font-bold
                                                                                text-titulo
                                                                            ">
                                                                    <div class="
                                                                                    flex
                                                                                    items-center
                                                                                    gap-2
                                                                                ">
                                                                        {{ $diaVisible }}

                                                                        @if ($esHoy)
                                                                            <span class="
                                                                                                rounded-full
                                                                                                bg-estado-exitoBg
                                                                                                px-2
                                                                                                py-0.5
                                                                                                text-[9px]
                                                                                                font-black
                                                                                                uppercase
                                                                                                tracking-wider
                                                                                                text-estado-exito
                                                                                            ">
                                                                                Hoy
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </td>


                                                                <td class="
                                                                                px-4
                                                                                py-3
                                                                                font-semibold
                                                                                text-apoyo
                                                                            ">
                                                                    {{ $turnoVisible }}
                                                                </td>


                                                                <td class="
                                                                                px-4
                                                                                py-3
                                                                                font-semibold
                                                                                text-apoyo
                                                                            ">
                                                                    {{ $horaInicio }}
                                                                </td>


                                                                <td class="
                                                                                px-4
                                                                                py-3
                                                                                font-semibold
                                                                                text-apoyo
                                                                            ">
                                                                    {{ $horaFin }}
                                                                </td>


                                                                <td class="px-4 py-3">
                                                                    <span class="
                                                                                    inline-flex
                                                                                    items-center
                                                                                    gap-1.5
                                                                                    rounded-full
                                                                                    bg-estado-exitoBg
                                                                                    px-2.5
                                                                                    py-1
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

                                                                        Activo
                                                                    </span>
                                                                </td>

                                                            </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>
                    </div>

                @endif

            </article>

        </section>


        {{-- =========================================================
        SEGURIDAD Y ACCESO
        ========================================================== --}}
        <section id="perfil-panel-seguridad" x-cloak x-show="tabPerfil === 'seguridad'"
            x-transition.opacity.duration.150ms role="tabpanel" aria-labelledby="perfil-tab-seguridad" tabindex="-1"
            class="space-y-4 outline-none">

            {{-- Estado general --}}
            <article class="
                    panel-institucional
                    rounded-[1.4rem]
                    border border-borde-suave
                    p-5
                ">

                <div class="
                        flex
                        flex-col
                        gap-4
                        lg:flex-row
                        lg:items-start
                        lg:justify-between
                    ">

                    <div>
                        <div class="
                                flex
                                items-center
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
                                        ph-shield-check
                                        text-xl
                                    "></i>
                            </div>

                            <div>
                                <h2 class="
                                        font-outfit
                                        text-xl
                                        font-extrabold
                                        text-titulo
                                    ">
                                    Seguridad y acceso
                                </h2>

                                <p class="
                                        mt-0.5
                                        text-sm
                                        font-medium
                                        text-meta
                                    ">
                                    Revisa el estado de tu cuenta
                                    y administra tus mecanismos de seguridad.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="
                            inline-flex
                            items-center
                            gap-2
                            rounded-full
                            px-3
                            py-1.5
                            text-xs
                            font-black
                            {{
    $estadoActivo && $accesoHabilitado
    ? 'bg-estado-exitoBg text-estado-exito'
    : 'bg-estado-peligroBg text-estado-peligro'
                            }}
                        ">
                        <span class="
                                h-2
                                w-2
                                rounded-full
                                {{
    $estadoActivo && $accesoHabilitado
    ? 'bg-estado-exito'
    : 'bg-estado-peligro'
                                }}
                            "></span>

                        {{
    $estadoActivo && $accesoHabilitado
    ? 'Cuenta operativa'
    : 'Revisar acceso'
                        }}
                    </div>

                </div>


                {{-- Aviso contraseña temporal --}}
                @if ($debeCambiarPassword)
                    <div class="
                                mt-5
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
                                Debes actualizar tu contraseña
                            </p>

                            <p class="
                                        mt-1
                                        text-sm
                                        font-medium
                                        leading-6
                                        text-meta
                                    ">
                                Tu cuenta tiene pendiente un cambio de contraseña.
                                Utiliza la sección de contraseña ubicada más abajo
                                para completar esta actualización.
                            </p>
                        </div>
                    </div>
                @endif


                {{-- Indicadores --}}
                <dl class="
                        mt-5
                        grid
                        gap-4
                        sm:grid-cols-2
                        xl:grid-cols-5
                    ">

                    {{-- Cuenta --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-9
                                w-9
                                items-center
                                justify-center
                                rounded-xl
                                bg-fondo-card
                            ">
                            <i class="
                                    ph-bold
                                    ph-user-check
                                    {{
    $estadoActivo
    ? 'text-estado-exito'
    : 'text-estado-peligro'
                                    }}
                                "></i>
                        </div>

                        <dt class="
                                text-[10px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Estado
                        </dt>

                        <dd class="mt-1">
                            <span class="
                                    text-sm
                                    font-bold
                                    {{
    $estadoActivo
    ? 'text-estado-exito'
    : 'text-estado-peligro'
                                    }}
                                ">
                                {{
    $estadoActivo
    ? 'Cuenta activa'
    : 'Cuenta inactiva'
                                }}
                            </span>
                        </dd>
                    </div>


                    {{-- Acceso --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-9
                                w-9
                                items-center
                                justify-center
                                rounded-xl
                                bg-fondo-card
                            ">
                            <i class="
                                    ph-bold
                                    ph-sign-in
                                    {{
    $accesoHabilitado
    ? 'text-estado-exito'
    : 'text-estado-peligro'
                                    }}
                                "></i>
                        </div>

                        <dt class="
                                text-[10px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Acceso
                        </dt>

                        <dd class="
                                mt-1
                                text-sm
                                font-bold
                                {{
    $accesoHabilitado
    ? 'text-estado-exito'
    : 'text-estado-peligro'
                                }}
                            ">
                            {{
    $accesoHabilitado
    ? 'Habilitado'
    : 'Bloqueado'
                            }}
                        </dd>
                    </div>


                    {{-- Correo --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-9
                                w-9
                                items-center
                                justify-center
                                rounded-xl
                                bg-fondo-card
                                text-boton-acento
                            ">
                            <i class="
                                    ph-bold
                                    ph-envelope-simple
                                "></i>
                        </div>

                        <dt class="
                                text-[10px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Correo de acceso
                        </dt>

                        <dd class="
                                mt-1
                                break-all
                                text-sm
                                font-bold
                                text-titulo
                            ">
                            {{ $usuario->correo }}
                        </dd>
                    </div>


                    {{-- 2FA --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-9
                                w-9
                                items-center
                                justify-center
                                rounded-xl
                                bg-fondo-card
                            ">
                            <i class="
                                    ph-bold
                                    ph-device-mobile
                                    {{
    $twoFactorActivo
    ? 'text-estado-exito'
    : 'text-meta'
                                    }}
                                "></i>
                        </div>

                        <dt class="
                                text-[10px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Verificación en dos pasos
                        </dt>

                        <dd class="
                                mt-1
                                text-sm
                                font-bold
                                {{
    $twoFactorActivo
    ? 'text-estado-exito'
    : 'text-apoyo'
                                }}
                            ">
                            {{
    $twoFactorActivo
    ? 'Activada'
    : 'No activada'
                            }}
                        </dd>
                    </div>


                    {{-- Último acceso --}}
                    <div class="
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            p-4
                        ">
                        <div class="
                                mb-3
                                flex
                                h-9
                                w-9
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

                        <dt class="
                                text-[10px]
                                font-black
                                uppercase
                                tracking-wider
                                text-meta
                            ">
                            Último acceso
                        </dt>

                        <dd class="
                                mt-1
                                text-sm
                                font-bold
                                text-titulo
                            ">
                            {{ $ultimoAcceso ?? 'Sin registro' }}
                        </dd>

                        @if ($ultimoAccesoRelativo)
                            <p class="
                                        mt-1
                                        text-[10px]
                                        font-semibold
                                        text-meta
                                    ">
                                {{ ucfirst($ultimoAccesoRelativo) }}
                            </p>
                        @endif
                    </div>

                </dl>


                {{-- Información administrativa --}}
                <div class="
                        mt-4
                        flex
                        items-start
                        gap-2
                        rounded-2xl
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
                            ph-info
                            mt-0.5
                            shrink-0
                            text-boton-acento
                        "></i>

                    <p>
                        El correo institucional, el estado de la cuenta
                        y la habilitación de acceso son administrados
                        por la institución.
                    </p>
                </div>

            </article>


            {{-- Última modificación de contraseña --}}
            @if ($passwordActualizada)
                <div class="
                            flex
                            items-center
                            gap-3
                            rounded-2xl
                            border border-borde-suave
                            bg-fondo-cardSuave
                            px-4
                            py-3
                            text-xs
                            font-semibold
                            text-meta
                        ">
                    <i class="
                                ph-bold
                                ph-key
                                text-boton-acento
                            "></i>

                    <span>
                        Último cambio de contraseña:
                        <strong class="text-apoyo">
                            {{ $passwordActualizada }}
                        </strong>
                    </span>
                </div>
            @endif


            {{-- =====================================================
            CONTRASEÑA
            ====================================================== --}}
            @if (
                    Laravel\Fortify\Features::enabled(
                        Laravel\Fortify\Features::updatePasswords()
                    )
                )
                <div class="
                            overflow-hidden
                            rounded-[1.4rem]
                            border border-borde-suave
                            bg-fondo-cardSuave
                            shadow-card
                        ">
                    @livewire(
                        'profile.update-password-form'
                    )
                </div>
            @endif


            {{-- =====================================================
            DOBLE FACTOR
            ====================================================== --}}
            @if (
                    Laravel\Fortify\Features::canManageTwoFactorAuthentication()
                )
                <div class="
                            overflow-hidden
                            rounded-[1.4rem]
                            border border-borde-suave
                            bg-fondo-cardSuave
                            shadow-card
                        ">
                    @livewire(
                        'profile.two-factor-authentication-form'
                    )
                </div>
            @endif


            {{-- =====================================================
            SESIONES ACTIVAS
            ====================================================== --}}
            <div class="
                    overflow-hidden
                    rounded-[1.4rem]
                    border border-borde-suave
                    bg-fondo-cardSuave
                    shadow-card
                ">
                @livewire(
                    'profile.logout-other-browser-sessions-form'
                )
            </div>

        </section>


        {{-- =========================================================
        PIE DEL PERFIL
        ========================================================== --}}
        <footer class="
                flex
                flex-col
                gap-3
                rounded-2xl
                border border-borde-suave
                bg-fondo-cardSuave
                px-4
                py-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            ">

            <div class="
                    flex
                    items-center
                    gap-2
                    text-xs
                    font-semibold
                    text-meta
                ">
                <i class="
                        ph-bold
                        ph-shield-check
                        text-boton-acento
                    "></i>

                <span>
                    Perfil institucional protegido por RememberMind.
                </span>
            </div>


            <div class="
                    flex
                    items-center
                    gap-2
                    text-[10px]
                    font-black
                    uppercase
                    tracking-wider
                    text-meta
                ">
                <i class="
                        ph-bold
                        ph-heart
                        text-boton-acento
                    "></i>

                Cuidado · Memoria · Dignidad
            </div>

        </footer>

    </div>
</x-sistema-layout>