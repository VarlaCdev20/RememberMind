@php
    /*
     * Datos clínicos listos para presentación. Las relaciones Eloquent nunca se
     * imprimen directamente: se convierten en etiquetas legibles y consistentes.
     */
    $diagnosticosActivos = collect($adultoMayor->diagnosticos ?? [])
        ->map(fn ($diagnostico) => trim((string) data_get($diagnostico, 'nombre')))
        ->filter()
        ->unique()
        ->values();

    $alergiasConocidas = collect($adultoMayor->alergias ?? [])
        ->map(fn ($alergia) => trim((string) data_get($alergia, 'sustancia')))
        ->filter()
        ->unique()
        ->values();

    $antecedentesRelevantes = collect($adultoMayor->antecedentesClinicos ?? [])
        ->map(fn ($antecedente) => trim((string) data_get($antecedente, 'descripcion')))
        ->filter()
        ->unique()
        ->values();

    $alergiasTexto = $alergiasConocidas->isNotEmpty()
        ? $alergiasConocidas->join(', ')
        : 'Sin alergias activas registradas';

    $seguroActivo = $adultoMayor->seguros->first();
    $familiaresActivos = $adultoMayor->familiares
        ->filter(fn ($contacto) => in_array(data_get($contacto, 'pivot.estado'), ['ACTIVO', 'ACTIVA'], true));
    $contactoEmergencia = $familiaresActivos
        ->first(fn ($contacto) => (bool) data_get($contacto, 'pivot.contacto_emergencia'))
        ?? $familiaresActivos->first(fn ($contacto) => (bool) data_get($contacto, 'pivot.responsable_principal'));
    $admisionInicial = $adultoMayor->admisiones->sortBy('fecha_hora_admision')->first();
    $ultimoPaseTurno = $adultoMayor->pasesTurno->sortByDesc('fecha_hora')->first();
    $asignacionJornadaActiva = $adultoMayor->asignacionesJornada
        ->first(fn ($asignacion) => in_array($asignacion->estado, ['ACTIVA', 'ACTIVO'], true));
    $grupoSanguineo = trim(implode(' ', array_filter([
        $adultoMayor->grupo_sanguineo,
        $adultoMayor->factor_rh,
    ])));
@endphp

<div class="rm-clinical-workspace rm-ficha-detalles" x-data="{ activeTab: @entangle('tabActivo'), modalSelectorAtencion: false, modalExportar: false, drawerExpediente: false, drawerFamilia: false }" @keydown.escape.window="modalSelectorAtencion = false; modalExportar = false; drawerExpediente = false; drawerFamilia = false">

    {{-- CABECERA INSTITUCIONAL CLÍNICA (Encabezado + Card Residente + Tabs) --}}
    @include('livewire.cuidados.ficha.cabecera')

    {{-- Solo se renderiza la pestaña activa: evita consultas, formularios y datos
         clínicos duplicados u ocultos pertenecientes a otras áreas. --}}
    @switch($tabActivo)
        @case('signos')
            @include('livewire.cuidados.ficha.tab-signos')
            @break
        @case('medicacion')
            @include('livewire.cuidados.ficha.tab-medicacion')
            @break
        @case('cuidados')
        @case('cuidado')
            @include('livewire.cuidados.ficha.tab-cuidados')
            @break
        @case('seguimiento')
            @include('livewire.cuidados.ficha.tab-seguimiento')
            @break
        @case('eventos')
            @include('livewire.cuidados.ficha.tab-eventos')
            @break
        @case('alertas')
            @include('livewire.cuidados.ficha.tab-alertas')
            @break
        @case('estudios')
        @case('resultados')
            @include('livewire.cuidados.ficha.tab-estudios')
            @break
        @case('historial')
            @include('livewire.cuidados.ficha.tab-historial')
            @break
        @case('documentos')
            @include('livewire.cuidados.ficha.tab-documentos')
            @break
        @default
            @include('livewire.cuidados.ficha.tab-resumen')
    @endswitch

    {{-- MODALES CLÍNICOS OPERATIVOS --}}
    {{-- MODAL DE PRESCRIPCIÓN MÉDICA --}}
    @livewire('medicacion.medicacion-adulto-modal')

    @include('livewire.cuidados.ficha.modales')
</div>
