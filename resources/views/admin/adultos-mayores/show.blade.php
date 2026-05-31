@use('Carbon\Carbon')
@use('Illuminate\Support\Facades\Storage')

<x-sistema-layout>
    @php
    $adultoObj = is_object($adulto ?? null) ? $adulto : null;

    $idAdulto = optional($adultoObj)->cod_am;

    $nombreCompleto = trim(
        (optional($adultoObj)->nombres ?? '') . ' ' .
        (optional($adultoObj)->ap_paterno ?? '') . ' ' .
        (optional($adultoObj)->ap_materno ?? '')
    );

    $fechaNacimiento = optional($adultoObj)->fecha_nac;
    $fechaIngreso = optional($adultoObj)->fecha_ing;

    $edad = $fechaNacimiento
        ? Carbon::parse($fechaNacimiento)->age
        : null;

    $fechaNacimientoFormateada = $fechaNacimiento
        ? Carbon::parse($fechaNacimiento)->format('d/m/Y')
        : 'No registrada';

    $fechaIngresoFormateada = $fechaIngreso
        ? Carbon::parse($fechaIngreso)->format('d/m/Y')
        : 'No registrada';

    /*
    |--------------------------------------------------------------------------
    | Normalización del estado
    |--------------------------------------------------------------------------
    | Evita que salga JSON como en la captura.
    */
    $estadoFuente = optional($adultoObj)->estado_adulto ?? optional($adultoObj)->estado ?? null;

    if (is_object($estadoFuente)) {
        $estadoTexto = $estadoFuente->estado ?? 'Activo';
    } elseif (is_array($estadoFuente)) {
        $estadoTexto = $estadoFuente['estado'] ?? 'Activo';
    } else {
        $estadoTexto = $estadoFuente ?: 'Activo';
    }

    $estadoTexto = strtoupper((string) $estadoTexto);

    $fotoAdulto = optional($adultoObj)->foto ?? optional($adultoObj)->imagen ?? null;

    $fotoUrl = $fotoAdulto
        ? Storage::url($fotoAdulto)
        : null;

    $iniciales = strtoupper(
        substr(optional($adultoObj)->nombres ?? 'A', 0, 1) .
        substr(optional($adultoObj)->ap_paterno ?? '', 0, 1)
    );

    $ci = optional($adultoObj)->ci ?? 'No registrado';
    $genero = optional($adultoObj)->genero ?? 'No registrado';
    $tieneCelular = optional($adultoObj)->tiene_celular ?? true;
    $celular = optional($adultoObj)->celular ?? 'No registrado';
    $sabeUsarWhatsapp = optional($adultoObj)->sabe_usar_whatsapp ?? false;
    $telefonoFijo = optional($adultoObj)->telefono_fijo ?? 'No registrado';
    $zona = optional($adultoObj)->zona ?? 'No registrada';
    $calle = optional($adultoObj)->calle ?? 'No registrada';

    // Listas para los forelse inferiores (Datos Activos)
    $familiaresLista = is_iterable($familiaresActivos ?? null) ? collect($familiaresActivos) : collect();
    $observacionesLista = is_iterable($observacionesActivas ?? null) ? collect($observacionesActivas) : collect();
    $atencionesLista = is_iterable($atencionesActivas ?? null) ? collect($atencionesActivas) : collect();
    $actividadesLista = is_iterable($actividadesActivas ?? null) ? collect($actividadesActivas) : collect();
    $documentosLista = is_iterable($documentosActivos ?? null) ? collect($documentosActivos) : collect();
    $evaluacionesLista = is_iterable($evaluacionesActivas ?? null) ? collect($evaluacionesActivas) : collect();
    $asignacionesLista = is_iterable($asignaciones ?? null) ? collect($asignaciones) : collect();
    $bitacoraLista = is_iterable($bitacora ?? null) ? collect($bitacora) : collect();
    $eventosFiltroLista = is_iterable($eventosFiltro ?? null) ? collect($eventosFiltro) : collect();

    // Conteos para indicadores
    $totalFamiliares = count($familiaresLista);
    $totalObservaciones = count($observacionesLista);
    $totalAtenciones = count($atencionesLista);
    $totalActividades = count($actividadesLista);
    $totalDocumentos = count($documentosLista);

    $totalEvaluaciones = count($evaluacionesLista);
    $totalAsignaciones = count($asignacionesLista);

    $tabInicial = request('tab', 'identificacion');
@endphp

    <div
        x-data="{
            carpetaActiva: 'identificacion',
            modal: null,
            isEditing: false,
            isViewing: false,
            recordData: {},
            abrir(nombre, data = null, edit = false, view = false) { 
                this.modal = nombre;
                this.recordData = data || {};
                this.isEditing = edit;
                this.isViewing = view;
            },
            cerrar() { 
                this.modal = null;
                this.recordData = {};
                this.isEditing = false;
                this.isViewing = false;
            }
        }"
        class="relative mx-auto max-w-7xl space-y-4 px-4 py-6 sm:px-6 lg:px-8"
    >
        {{-- 1. Encabezado del expediente --}}
        @include('admin.adultos-mayores.show._cabecera-expediente')

        {{-- 2. Estado del Expediente --}}
        <div class="mb-6 rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <h3 class="mb-4 text-xs font-black uppercase tracking-widest text-apoyo">Estado de completitud del expediente</h3>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
                    <span class="text-xs font-black uppercase tracking-wide text-apoyo">Identificación</span>
                    <span class="text-xs font-black text-parrafo">Completo</span>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
                    <span class="text-xs font-black uppercase tracking-wide text-apoyo">Red de Apoyo</span>
                    <span class="text-xs font-black {{ $totalFamiliares > 0 ? 'text-parrafo' : 'text-amber-600' }}">{{ $totalFamiliares > 0 ? 'Registrada' : 'Pendiente' }}</span>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
                    <span class="text-xs font-black uppercase tracking-wide text-apoyo">Documentos</span>
                    <span class="text-xs font-black {{ $totalDocumentos > 0 ? 'text-parrafo' : 'text-amber-600' }}">{{ $totalDocumentos > 0 ? 'Registrados' : 'Pendientes' }}</span>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
                    <span class="text-xs font-black uppercase tracking-wide text-apoyo">Salud</span>
                    <span class="text-xs font-black {{ $fichasMedicas->isNotEmpty() ? 'text-parrafo' : 'text-amber-600' }}">{{ $fichasMedicas->isNotEmpty() ? 'Con datos' : 'Sin datos' }}</span>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
                    <span class="text-xs font-black uppercase tracking-wide text-apoyo">Cognitivo</span>
                    <span class="text-xs font-black {{ $totalEvaluaciones > 0 ? 'text-parrafo' : 'text-apoyo' }}">{{ $totalEvaluaciones > 0 ? 'Con datos' : 'Sin datos' }}</span>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
                    <span class="text-xs font-black uppercase tracking-wide text-apoyo">Participación</span>
                    <span class="text-xs font-black {{ $totalActividades > 0 ? 'text-parrafo' : 'text-apoyo' }}">{{ $totalActividades > 0 ? 'Con datos' : 'Sin datos' }}</span>
                </div>
            </div>
        </div>

        {{-- 3 y 4. Layout 2 Columnas: Carpetas y Contenido --}}
        <div class="flex flex-col lg:flex-row gap-6">
            
            {{-- Columna Izquierda: Menú de Carpetas --}}
            <div class="w-full lg:w-[280px] shrink-0">
                <div class="rounded-[24px] border border-borde bg-fondo-card p-3 shadow-sm flex flex-col gap-1">
                    <h3 class="px-3 py-2 text-xs font-black uppercase tracking-wide text-apoyo">Índice del Expediente</h3>
                    
                    @php
                        $carpetas = [
                            ['id' => 'identificacion', 'icon' => 'ph-identification-card', 'label' => 'Identificación', 'status' => 'Completa'],
                            ['id' => 'red_apoyo', 'icon' => 'ph-users-three', 'label' => 'Red de apoyo', 'status' => $totalFamiliares.' vinculados'],
                            ['id' => 'documentos', 'icon' => 'ph-folder-open', 'label' => 'Documentos', 'status' => $totalDocumentos.' archivos'],
                            ['id' => 'salud', 'icon' => 'ph-heartbeat', 'label' => 'Salud resumida', 'status' => $fichasMedicas->isNotEmpty() ? 'Ficha registrada' : 'Sin datos'],
                            ['id' => 'cognitivo', 'icon' => 'ph-brain', 'label' => 'Cognitivo resumido', 'status' => $totalEvaluaciones > 0 ? $totalEvaluaciones.' pruebas' : 'Sin pruebas'],
                            ['id' => 'participacion', 'icon' => 'ph-handshake', 'label' => 'Participación', 'status' => $totalActividades > 0 ? $totalActividades.' actividades' : 'Sin actividad'],
                            ['id' => 'historial', 'icon' => 'ph-clock-counter-clockwise', 'label' => 'Historial inst.', 'status' => $totalObservaciones.' movimientos'],
                            ['id' => 'reportes', 'icon' => 'ph-file-pdf', 'label' => 'Reportes', 'status' => 'Disponibles'],
                        ];
                    @endphp

                    @foreach($carpetas as $carpeta)
                    <button type="button" @click="carpetaActiva = '{{ $carpeta['id'] }}'" 
                            class="flex items-center justify-between rounded-xl px-4 py-3 text-left transition"
                            :class="carpetaActiva === '{{ $carpeta['id'] }}' ? 'bg-fondo-panel shadow-sm border border-borde-suave text-titulo' : 'text-apoyo hover:bg-fondo-panel border border-transparent'">
                        <div class="flex items-center gap-3">
                            <i class="ph-bold {{ $carpeta['icon'] }} text-lg" :class="carpetaActiva === '{{ $carpeta['id'] }}' ? 'text-boton-acento' : ''"></i>
                            <span class="text-xs font-black">{{ $carpeta['label'] }}</span>
                        </div>
                        <span class="text-[10px] font-bold text-apoyo">{{ $carpeta['status'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Columna Derecha: Contenido de la Carpeta Activa --}}
            <div class="flex-1 min-w-0">
                <div x-show="carpetaActiva === 'identificacion'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._identificacion')
                </div>
                
                <div x-show="carpetaActiva === 'red_apoyo'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._red-apoyo')
                </div>

                <div x-show="carpetaActiva === 'documentos'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._documentos')
                </div>

                <div x-show="carpetaActiva === 'salud'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._salud')
                </div>

                <div x-show="carpetaActiva === 'cognitivo'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._cognitivo')
                </div>

                <div x-show="carpetaActiva === 'participacion'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._participacion')
                </div>

                <div x-show="carpetaActiva === 'historial'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._historial')
                </div>

                <div x-show="carpetaActiva === 'reportes'" x-transition style="display: none;">
                    @include('admin.adultos-mayores.show.carpetas._reportes')
                </div>
            </div>
        </div>

        {{-- Modales Antiguos --}}
        @include('admin.adultos-mayores.show._modales-existentes')

    </div> {{-- fin x-data principal --}}
</x-sistema-layout>
