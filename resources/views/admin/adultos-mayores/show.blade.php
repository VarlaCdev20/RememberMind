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
        ? \Carbon\Carbon::parse($fechaNacimiento)->age
        : null;

    $fechaNacimientoFormateada = $fechaNacimiento
        ? \Carbon\Carbon::parse($fechaNacimiento)->format('d/m/Y')
        : 'No registrada';

    $fechaIngresoFormateada = $fechaIngreso
        ? \Carbon\Carbon::parse($fechaIngreso)->format('d/m/Y')
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

    $tabInicial = request('tab', 'resumen');
@endphp

    <div
        x-data="{
            tab: @js($tabInicial),
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
        class="relative mx-auto max-w-7xl space-y-4"
    >
            {{-- Cabecera del expediente --}}
            @include('admin.adultos-mayores.show._cabecera-expediente')

            {{-- Contenido de pestañas --}}
            <div class="px-5 py-6 sm:px-6">
                <!-- 1. Resumen -->
                @include('admin.adultos-mayores.show._resumen')

                <!-- 2. Datos y Red de Apoyo -->
                <div x-show="tab === 'datos'">
                    <div x-data="{ tab: 'familiares' }">
                        @include('admin.adultos-mayores.show._familiares')
                    </div>
                </div>

                <!-- 3. Salud y Cuidados -->
                @include('admin.adultos-mayores.show._salud-medica')

                <!-- 4. Seguimiento -->
                <div x-show="tab === 'seguimiento'" class="space-y-6">
                    @include('admin.adultos-mayores.show._seguimiento-observaciones')
                    
                    {{-- Atenciones (renderizado directo) --}}
                    @include('admin.adultos-mayores.show._atenciones')
                    
                    {{-- Actividades (shadowing tab a actividades) --}}
                    <div x-data="{ tab: 'actividades' }">
                        @include('admin.adultos-mayores.show._actividades')
                    </div>
                </div>

                <!-- 5. Evaluaciones -->
                @include('admin.adultos-mayores.show._evaluaciones-cognitivas')

                <!-- 6. Documentos -->
                @include('admin.adultos-mayores.show._documentos')

                <!-- 7. Historial -->
                @include('admin.adultos-mayores.show._historial-estados')

                <!-- 8. Reportes -->
                @include('admin.adultos-mayores.show._reportes')
            </div>

            {{-- Modales Antiguos --}}
            @include('admin.adultos-mayores.show._modales-existentes')

    </div> {{-- fin x-data principal --}}
</x-sistema-layout>
