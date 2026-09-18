@php
$modulos = [
    // --- RESIDENTES Y ADMISIONES ---
    [
        'id' => 'expedientes',
        'categoria' => 'residentes',
        'titulo' => 'Padrón de Residentes',
        'subtitulo' => 'Expedientes, censos y estados de adultos mayores',
        'icono' => 'ph-users-four',
        'color' => 'azul',
        'route' => 'admin.adultos-mayores.index',
    ],
    [
        'id' => 'preadmisiones',
        'categoria' => 'residentes',
        'titulo' => 'Preadmisiones',
        'subtitulo' => 'Solicitudes y evaluaciones de ingreso',
        'icono' => 'ph-user-plus',
        'color' => 'azul',
        'route' => 'admin.admisiones.preadmisiones',
    ],
    [
        'id' => 'habitaciones',
        'categoria' => 'residentes',
        'titulo' => 'Habitaciones y Camas',
        'subtitulo' => 'Censo de ocupación y asignación de plazas',
        'icono' => 'ph-bed',
        'color' => 'azul',
        'route' => 'admin.habitaciones.index',
    ],
    [
        'id' => 'familia',
        'categoria' => 'residentes',
        'titulo' => 'Red Familiar y Apoyo',
        'subtitulo' => 'Familiares de referencia y tutores legales',
        'icono' => 'ph-users-three',
        'color' => 'azul',
        'route' => 'admin.familia-social.resumen',
    ],

    // --- MEDICINA Y FARMACOLOGÍA ---
    [
        'id' => 'medico_dash',
        'categoria' => 'medico',
        'titulo' => 'Dashboard Médico',
        'subtitulo' => 'Panel clínico geriátrico y consultas',
        'icono' => 'ph-stethoscope',
        'color' => 'esmeralda',
        'route' => 'admin.medico.dashboard',
    ],
    [
        'id' => 'medico_val',
        'categoria' => 'medico',
        'titulo' => 'Valoraciones Médicas',
        'subtitulo' => 'Historias clínicas y evolución médica',
        'icono' => 'ph-clipboard-text',
        'color' => 'esmeralda',
        'route' => 'admin.medico.valoraciones',
    ],
    [
        'id' => 'medico_signos',
        'categoria' => 'medico',
        'titulo' => 'Signos Vitales',
        'subtitulo' => 'Monitoreo de constantes biométricas',
        'icono' => 'ph-heartbeat',
        'color' => 'esmeralda',
        'route' => 'admin.medico.signos-vitales',
    ],
    [
        'id' => 'ficha_medica',
        'categoria' => 'medico',
        'titulo' => 'Ficha Médica de Residentes',
        'subtitulo' => 'Expediente clínico integral y evolución médica',
        'icono' => 'ph-address-book',
        'color' => 'esmeralda',
        'route' => 'admin.medico.pacientes.observacion',
    ],
    [
        'id' => 'medicacion_ordenes',
        'categoria' => 'medico',
        'titulo' => 'Medicación Prescrita',
        'subtitulo' => 'Órdenes médicas, recetas y pautas activas',
        'icono' => 'ph-pill',
        'color' => 'esmeralda',
        'route' => 'admin.salud-seguimiento.medicacion.index',
    ],
    [
        'id' => 'medicacion_admin',
        'categoria' => 'medico',
        'titulo' => 'Administraciones',
        'subtitulo' => 'Kardex y registro de dosis aplicadas',
        'icono' => 'ph-syringe',
        'color' => 'esmeralda',
        'route' => 'admin.salud-seguimiento.administracion.index',
    ],
    [
        'id' => 'alertas_clinicas',
        'categoria' => 'medico',
        'titulo' => 'Alertas Clínicas',
        'subtitulo' => 'Consola central de alertas y eventos urgentes',
        'icono' => 'ph-warning-octagon',
        'color' => 'rojo',
        'route' => 'admin.alertas-clinicas.index',
    ],

    // --- ENFERMERÍA Y CUIDADOS ---
    [
        'id' => 'enf_dash',
        'categoria' => 'enfermeria',
        'titulo' => 'Dashboard de Turno',
        'subtitulo' => 'Supervisión de guardia operativa activa',
        'icono' => 'ph-first-aid',
        'color' => 'teal',
        'route' => 'admin.enfermeria.dashboard',
    ],
    [
        'id' => 'enf_pacientes',
        'categoria' => 'enfermeria',
        'titulo' => 'Pacientes en Cuidado',
        'subtitulo' => 'Distribución y alcance de residentes por turno',
        'icono' => 'ph-user-list',
        'color' => 'teal',
        'route' => 'admin.enfermeria.pacientes',
    ],
    [
        'id' => 'enf_agenda',
        'categoria' => 'enfermeria',
        'titulo' => 'Agenda de Cuidados',
        'subtitulo' => 'Cronograma diario de atenciones programadas',
        'icono' => 'ph-calendar-check',
        'color' => 'teal',
        'route' => 'admin.enfermeria.agenda',
    ],
    [
        'id' => 'enf_turnos',
        'categoria' => 'enfermeria',
        'titulo' => 'Turnos de Enfermería',
        'subtitulo' => 'Catálogo y programación horaria de turnos',
        'icono' => 'ph-clock-clockwise',
        'color' => 'teal',
        'route' => 'admin.turnos-enfermeria.index',
    ],
    [
        'id' => 'enf_asignaciones',
        'categoria' => 'enfermeria',
        'titulo' => 'Asignación de Pacientes',
        'subtitulo' => 'Vinculación de adultos a turnos de enfermería',
        'icono' => 'ph-arrows-left-right',
        'color' => 'teal',
        'route' => 'admin.asignacion-turno.index',
    ],
    [
        'id' => 'enf_tareas',
        'categoria' => 'enfermeria',
        'titulo' => 'Planes y Tareas',
        'subtitulo' => 'Planes de cuidados y tareas asistenciales',
        'icono' => 'ph-check-square-offset',
        'color' => 'teal',
        'route' => 'admin.enfermeria.tareas',
    ],
    [
        'id' => 'enf_registros',
        'categoria' => 'enfermeria',
        'titulo' => 'Cuidados e Incidentes',
        'subtitulo' => 'Seguimiento diario y registro de incidencias',
        'icono' => 'ph-notepad',
        'color' => 'teal',
        'route' => 'admin.enfermeria.registros',
    ],
    [
        'id' => 'enf_pase',
        'categoria' => 'enfermeria',
        'titulo' => 'Pase y Relevo de Turno',
        'subtitulo' => 'Traspaso estructurado de novedades clínicas',
        'icono' => 'ph-arrows-clockwise',
        'color' => 'teal',
        'route' => 'admin.enfermeria.pase-turno',
    ],
    [
        'id' => 'enf_ficha',
        'categoria' => 'enfermeria',
        'titulo' => 'Ficha de Cuidados',
        'subtitulo' => 'Expediente clínico integral y evolución de enfermería',
        'icono' => 'ph-folder-user',
        'color' => 'teal',
        'route' => 'admin.salud-seguimiento.ficha.index',
    ],
    [
        'id' => 'enf_kardex',
        'categoria' => 'enfermeria',
        'titulo' => 'Kardex y Administraciones',
        'subtitulo' => 'Registro y confirmación de fármacos aplicados',
        'icono' => 'ph-syringe',
        'color' => 'teal',
        'route' => 'admin.salud-seguimiento.administracion.index',
    ],
    [
        'id' => 'enf_valoracion',
        'categoria' => 'enfermeria',
        'titulo' => 'Valoraciones Iniciales',
        'subtitulo' => 'Tamizaje de enfermería al ingreso del residente',
        'icono' => 'ph-clipboard-text',
        'color' => 'teal',
        'route' => 'admin.admision.valoracion-enfermeria',
    ],
    [
        'id' => 'enf_reportes',
        'categoria' => 'enfermeria',
        'titulo' => 'Reportes de Enfermería',
        'subtitulo' => 'Evolución por periodos, balance y cuidados aplicados',
        'icono' => 'ph-chart-line-up',
        'color' => 'teal',
        'route' => 'admin.enfermeria.reportes',
    ],

    // --- ESPECIALIDADES Y BIENESTAR ---
    [
        'id' => 'psico_dash',
        'categoria' => 'especialidades',
        'titulo' => 'Panel de Psicología',
        'subtitulo' => 'Salud mental, cognitiva y emocional',
        'icono' => 'ph-brain',
        'color' => 'purpura',
        'route' => 'admin.psicologia.dashboard',
    ],
    [
        'id' => 'psico_cog',
        'categoria' => 'especialidades',
        'titulo' => 'Evaluación Cognitiva',
        'subtitulo' => 'Minimental, cribado y deterioro cognitivo',
        'icono' => 'ph-puzzle-piece',
        'color' => 'purpura',
        'route' => 'admin.psicologia.evaluacion.cognitiva',
    ],
    [
        'id' => 'psico_afec',
        'categoria' => 'especialidades',
        'titulo' => 'Evaluación Afectiva',
        'subtitulo' => 'Escalas de depresión y estado afectivo',
        'icono' => 'ph-smiley',
        'color' => 'purpura',
        'route' => 'admin.psicologia.evaluacion.afectiva',
    ],
    [
        'id' => 'nutri_val',
        'categoria' => 'especialidades',
        'titulo' => 'Valoración Nutricional',
        'subtitulo' => 'Evaluación dietética y requerimientos',
        'icono' => 'ph-fork-knife',
        'color' => 'ambar',
        'route' => 'admin.nutricion.valoracion',
    ],
    [
        'id' => 'nutri_seg',
        'categoria' => 'especialidades',
        'titulo' => 'Seguimiento Nutricional',
        'subtitulo' => 'Control de curvas de peso, talla e IMC',
        'icono' => 'ph-chart-line-up',
        'color' => 'ambar',
        'route' => 'admin.nutricion.seguimiento',
    ],
    [
        'id' => 'actividades',
        'categoria' => 'especialidades',
        'titulo' => 'Actividades y Talleres',
        'subtitulo' => 'Talleres recreativos y de estimulación',
        'icono' => 'ph-sparkle',
        'color' => 'ambar',
        'route' => 'admin.actividades.index',
    ],
    [
        'id' => 'voluntariado',
        'categoria' => 'especialidades',
        'titulo' => 'Voluntariado',
        'subtitulo' => 'Comunidad de apoyo y voluntariado',
        'icono' => 'ph-hand-heart',
        'color' => 'ambar',
        'route' => 'admin.voluntariado.index',
    ],

    // --- ADMINISTRACIÓN, PERSONAL Y AUDITORÍA ---
    [
        'id' => 'adm_areas_atencion',
        'categoria' => 'administracion',
        'titulo' => 'Centro de Áreas de Atención',
        'subtitulo' => 'Organización integral de áreas por roles y vistas',
        'icono' => 'ph-squares-four',
        'color' => 'indigo',
        'route' => 'admin.areas-atencion.index',
    ],
    [
        'id' => 'adm_usuarios',
        'categoria' => 'administracion',
        'titulo' => 'Directorio de Usuarios',
        'subtitulo' => 'Cuentas de usuario, accesos y seguridad',
        'icono' => 'ph-user-gear',
        'color' => 'indigo',
        'route' => 'admin.usuarios.index',
    ],
    [
        'id' => 'adm_roles',
        'categoria' => 'administracion',
        'titulo' => 'Roles y Permisos',
        'subtitulo' => 'Matriz de facultades y control de acceso',
        'icono' => 'ph-shield-check',
        'color' => 'indigo',
        'route' => 'admin.roles-permisos.index',
    ],
    [
        'id' => 'adm_personal',
        'categoria' => 'administracion',
        'titulo' => 'Personal Institucional',
        'subtitulo' => 'Padrón de empleados y recursos humanos',
        'icono' => 'ph-identification-badge',
        'color' => 'indigo',
        'route' => 'admin.personal-institucional',
    ],
    [
        'id' => 'adm_turnos_asig',
        'categoria' => 'administracion',
        'titulo' => 'Horarios y Asignaciones',
        'subtitulo' => 'Programación horaria de personal',
        'icono' => 'ph-calendar',
        'color' => 'indigo',
        'route' => 'admin.turnos-asignaciones.index',
    ],
    [
        'id' => 'adm_reportes',
        'categoria' => 'administracion',
        'titulo' => 'Centro de Reportes',
        'subtitulo' => 'Informes institucionales, clínicos y PDF',
        'icono' => 'ph-file-text',
        'color' => 'indigo',
        'route' => 'admin.reportes.institucional.preview',
    ],
    [
        'id' => 'adm_bitacora',
        'categoria' => 'administracion',
        'titulo' => 'Bitácora y Auditoría',
        'subtitulo' => 'Trazabilidad forense y registro de actividad',
        'icono' => 'ph-scroll',
        'color' => 'indigo',
        'route' => 'admin.bitacora.index',
    ],
];

// Estilos por paleta
$estilosPaleta = [
    'azul' => [
        'bg_icono' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/60',
        'hover_border' => 'hover:border-blue-300 dark:hover:border-blue-700',
        'glow' => 'hover:shadow-blue-500/10',
    ],
    'esmeralda' => [
        'bg_icono' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/60',
        'hover_border' => 'hover:border-emerald-300 dark:hover:border-emerald-700',
        'glow' => 'hover:shadow-emerald-500/10',
    ],
    'rojo' => [
        'bg_icono' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60',
        'hover_border' => 'hover:border-rose-300 dark:hover:border-rose-700',
        'glow' => 'hover:shadow-rose-500/10',
    ],
    'teal' => [
        'bg_icono' => 'bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-900/60',
        'hover_border' => 'hover:border-teal-300 dark:hover:border-teal-700',
        'glow' => 'hover:shadow-teal-500/10',
    ],
    'purpura' => [
        'bg_icono' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-900/60',
        'hover_border' => 'hover:border-purple-300 dark:hover:border-purple-700',
        'glow' => 'hover:shadow-purple-500/10',
    ],
    'ambar' => [
        'bg_icono' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60',
        'hover_border' => 'hover:border-amber-300 dark:hover:border-amber-700',
        'glow' => 'hover:shadow-amber-500/10',
    ],
    'indigo' => [
        'bg_icono' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/60',
        'hover_border' => 'hover:border-indigo-300 dark:hover:border-indigo-700',
        'glow' => 'hover:shadow-indigo-500/10',
    ],
];
@endphp

<section
    x-data="{
        search: '',
        tabActivo: 'todos',
        totalModulos: {{ count($modulos) }},
        matches(mod) {
            const matchesTab = this.tabActivo === 'todos' || mod.categoria === this.tabActivo;
            if (!matchesTab) return false;
            if (!this.search.trim()) return true;
            const term = this.search.toLowerCase().trim();
            return mod.titulo.toLowerCase().includes(term) ||
                   mod.subtitulo.toLowerCase().includes(term) ||
                   mod.categoria.toLowerCase().includes(term);
        }
    }"
    class="card-interactiva borde-verde-suave rounded-[2rem] border bg-fondo-panel p-5 sm:p-6 shadow-sm space-y-5 backdrop-blur-xl"
    aria-label="Centro de Mando y Vistas del Sistema"
>
    {{-- CABECERA DEL HUB --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-borde pb-5">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento font-black border border-boton-acento/20 text-base shadow-2xs">
                    <i class="ph-bold ph-squares-four"></i>
                </span>
                <h2 class="text-lg font-black text-titulo tracking-tight">
                    Centro de Mando Institucional
                </h2>
                <span class="rm-badge-neutral text-[10.5px]">
                    {{ count($modulos) }} Vistas Operativas
                </span>
            </div>
            <p class="text-xs text-apoyo leading-relaxed">
                Acceso directo e instantáneo a todos los módulos y pantallas del sistema para supervisión y operación.
            </p>
        </div>

        {{-- BUSCADOR INSTANTÁNEO --}}
        <div class="relative w-full lg:w-80 shrink-0">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-apoyo">
                <i class="ph-bold ph-magnifying-glass text-sm"></i>
            </div>
            <input
                type="text"
                x-model="search"
                placeholder="Buscar módulo (ej. Medicación, Preadmisiones, Camas)..."
                class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-borde bg-fondo-card text-xs font-semibold text-titulo placeholder:text-apoyo/80 focus:outline-none focus:ring-2 focus:ring-boton-acento/30 focus:border-boton-acento transition shadow-2xs"
            />
            <button
                type="button"
                x-show="search"
                @click="search = ''"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-apoyo hover:text-titulo transition cursor-pointer"
                title="Limpiar búsqueda"
            >
                <i class="ph-bold ph-x text-xs"></i>
            </button>
        </div>
    </div>

    {{-- BARRA DE PESTAÑAS / FILTROS POR CATEGORÍA --}}
    <div class="flex flex-wrap items-center gap-2 pt-0.5">
        <button
            type="button"
            @click="tabActivo = 'todos'"
            :class="tabActivo === 'todos'
                ? 'bg-boton-principal text-boton-principalTexto shadow-sm font-black'
                : 'bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-hover border border-borde font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
            <i class="ph-bold ph-grid-four text-sm"></i>
            <span>Todos</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full opacity-80"
                  :class="tabActivo === 'todos' ? 'bg-white/20 text-white' : 'bg-fondo-hover text-apoyo'">
                {{ count($modulos) }}
            </span>
        </button>

        <button
            type="button"
            @click="tabActivo = 'residentes'"
            :class="tabActivo === 'residentes'
                ? 'bg-blue-600 text-white shadow-sm font-black'
                : 'bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-hover border border-borde font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
            <i class="ph-bold ph-users-four text-sm"></i>
            <span>Residentes y Camas</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full opacity-80"
                  :class="tabActivo === 'residentes' ? 'bg-white/20 text-white' : 'bg-fondo-hover text-apoyo'">
                4
            </span>
        </button>

        <button
            type="button"
            @click="tabActivo = 'medico'"
            :class="tabActivo === 'medico'
                ? 'bg-emerald-600 text-white shadow-sm font-black'
                : 'bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-hover border border-borde font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
            <i class="ph-bold ph-stethoscope text-sm"></i>
            <span>Medicina y Farmacia</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full opacity-80"
                  :class="tabActivo === 'medico' ? 'bg-white/20 text-white' : 'bg-fondo-hover text-apoyo'">
                7
            </span>
        </button>

        <button
            type="button"
            @click="tabActivo = 'enfermeria'"
            :class="tabActivo === 'enfermeria'
                ? 'bg-teal-600 text-white shadow-sm font-black'
                : 'bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-hover border border-borde font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
            <i class="ph-bold ph-first-aid text-sm"></i>
            <span>Enfermería y Cuidados</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full opacity-80"
                  :class="tabActivo === 'enfermeria' ? 'bg-white/20 text-white' : 'bg-fondo-hover text-apoyo'">
                12
            </span>
        </button>

        <button
            type="button"
            @click="tabActivo = 'especialidades'"
            :class="tabActivo === 'especialidades'
                ? 'bg-purple-600 text-white shadow-sm font-black'
                : 'bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-hover border border-borde font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
            <i class="ph-bold ph-brain text-sm"></i>
            <span>Especialidades</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full opacity-80"
                  :class="tabActivo === 'especialidades' ? 'bg-white/20 text-white' : 'bg-fondo-hover text-apoyo'">
                7
            </span>
        </button>

        <button
            type="button"
            @click="tabActivo = 'administracion'"
            :class="tabActivo === 'administracion'
                ? 'bg-indigo-600 text-white shadow-sm font-black'
                : 'bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-hover border border-borde font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
            <i class="ph-bold ph-shield-check text-sm"></i>
            <span>Administración y Auditoría</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full opacity-80"
                  :class="tabActivo === 'administracion' ? 'bg-white/20 text-white' : 'bg-fondo-hover text-apoyo'">
                7
            </span>
        </button>
    </div>

    {{-- GRID DE BOTONES / TARJETAS DE ACCESO DIRECTO --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
        @foreach($modulos as $mod)
            @php
                $paleta = $estilosPaleta[$mod['color']] ?? $estilosPaleta['indigo'];
                $routeExists = Route::has($mod['route']);
                $url = $routeExists ? route($mod['route']) : '#';
            @endphp

            <div
                x-show="matches({
                    id: '{{ $mod['id'] }}',
                    categoria: '{{ $mod['categoria'] }}',
                    titulo: '{{ addslashes($mod['titulo']) }}',
                    subtitulo: '{{ addslashes($mod['subtitulo']) }}'
                })"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <a
                    wire:navigate
                    href="{{ $url }}"
                    class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover {{ $paleta['hover_border'] }} hover:shadow-md transition-all duration-300 hover:-translate-y-0.5"
                    title="{{ $mod['titulo'] }}: {{ $mod['subtitulo'] }}"
                >
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $paleta['bg_icono'] }} text-lg shadow-2xs group-hover:scale-105 transition-transform">
                                <i class="ph-bold {{ $mod['icono'] }}"></i>
                            </span>

                            <span class="p-1.5 rounded-lg text-apoyo group-hover:text-boton-acento group-hover:bg-fondo-panel transition">
                                <i class="ph-bold ph-arrow-up-right text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                            </span>
                        </div>

                        <div class="space-y-1">
                            <h3 class="text-sm font-black text-titulo group-hover:text-boton-acento transition-colors leading-snug">
                                {{ $mod['titulo'] }}
                            </h3>
                            <p class="text-[11.5px] text-apoyo leading-relaxed line-clamp-2">
                                {{ $mod['subtitulo'] }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-3 mt-1 border-t border-borde/60 flex items-center justify-between text-[10.5px]">
                        <span class="font-bold text-apoyo uppercase tracking-wider text-[9.5px]">
                            Acceder
                        </span>
                        <span class="font-black text-boton-acento opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                            <span>Ingresar</span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- MENSAJE CUANDO NO HAY COINCIDENCIAS --}}
    <div
        x-show="search.trim() !== '' && !document.querySelectorAll('[x-show*=\'matches\']:not([style*=\'display: none\'])').length"
        x-cloak
        class="p-8 rounded-2xl border border-dashed border-borde bg-fondo-hover/50 text-center space-y-2"
    >
        <div class="mx-auto h-10 w-10 rounded-xl bg-fondo-card border border-borde flex items-center justify-center text-apoyo text-lg">
            <i class="ph-bold ph-magnifying-glass"></i>
        </div>
        <h4 class="text-xs font-bold text-titulo">No se encontraron módulos coincidentes</h4>
        <p class="text-[11px] text-apoyo">Intenta buscar con otro término como "Medicación", "Camas", "Preadmisiones" o "Usuarios".</p>
        <button
            type="button"
            @click="search = ''"
            class="mt-2 px-3 py-1 rounded-lg bg-boton-acento text-white text-xs font-bold hover:opacity-90 transition cursor-pointer"
        >
            Restablecer búsqueda
        </button>
    </div>
</section>
