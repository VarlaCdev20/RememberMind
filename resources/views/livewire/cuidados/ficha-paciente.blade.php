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
        : 'Sin alergias conocidas';
@endphp

<div class="rm-clinical-workspace rm-ficha-detalles" x-data="{ activeTab: @entangle('tabActivo'), modalSelectorAtencion: false, modalExportar: false, drawerExpediente: false, drawerFamilia: false }" @keydown.escape.window="modalSelectorAtencion = false; modalExportar = false; drawerExpediente = false; drawerFamilia = false">
    <x-ui.toast />

    {{-- CABECERA INSTITUCIONAL CLÍNICA (Encabezado + Card Residente + Tabs) --}}
    @include('livewire.cuidados.ficha.cabecera')

    {{-- PANELES MODULARES SEGÚN PESTAÑA ACTIVA --}}
    {{-- 1. Resumen Clínico --}}
    <div x-show="activeTab === 'resumen'">
        @include('livewire.cuidados.ficha.tab-resumen')
    </div>

    {{-- 2. Signos Vitales --}}
    <div x-show="activeTab === 'signos'" x-cloak x-effect="if (activeTab === 'signos') { window.dispatchEvent(new CustomEvent('render-graficos-signos')); }">
        @include('livewire.cuidados.ficha.tab-signos')
    </div>

    {{-- 3. Medicación --}}
    <div x-show="activeTab === 'medicacion'" x-cloak>
        @include('livewire.cuidados.ficha.tab-medicacion')
    </div>

    {{-- 4. Cuidados --}}
    <div x-show="activeTab === 'cuidados' || activeTab === 'cuidado'" x-cloak>
        @include('livewire.cuidados.ficha.tab-cuidados')
    </div>

    {{-- 5. Seguimiento --}}
    <div x-show="activeTab === 'seguimiento'" x-cloak x-effect="if (activeTab === 'seguimiento') { window.dispatchEvent(new CustomEvent('render-graficos-seguimiento')); }">
        @include('livewire.cuidados.ficha.tab-seguimiento')
    </div>

    {{-- 6. Eventos clínicos (Golden Reference) / Alertas --}}
    <div x-show="activeTab === 'eventos' || activeTab === 'alertas'" x-cloak x-effect="if (activeTab === 'eventos' || activeTab === 'alertas') { window.dispatchEvent(new CustomEvent('render-graficos-eventos')); }">
        @include('livewire.cuidados.ficha.tab-eventos')
    </div>

    {{-- 7. Resultados y Estudios Clínicos (Reemplaza Valoración Integral) --}}
    <div x-show="activeTab === 'estudios' || activeTab === 'historial' || activeTab === 'resultados'" x-cloak x-effect="if (activeTab === 'estudios' || activeTab === 'historial' || activeTab === 'resultados') { window.dispatchEvent(new CustomEvent('render-graficos-estudios')); }">
        @include('livewire.cuidados.ficha.tab-estudios')
    </div>

    {{-- 8. Documentación (Golden Reference) --}}
    <div x-show="activeTab === 'documentos'" x-cloak>
        @include('livewire.cuidados.ficha.tab-documentos')
    </div>

    {{-- MODALES CLÍNICOS OPERATIVOS --}}
    {{-- MODAL DE PRESCRIPCIÓN MÉDICA --}}
    @livewire('medicacion.medicacion-adulto-modal')

    {{-- MODALES CLÍNICOS OPERATIVOS --}}
        {{-- TAB ALERTAS --}}
    @if($tabActivo === 'alertas')
        <div x-show="activeTab === 'alertas'">
            @include('livewire.cuidados.ficha.tab-alertas')
        </div>
    @endif

    {{-- TAB HISTORIAL --}}
    @if($tabActivo === 'historial')
        <div x-show="activeTab === 'historial'">
            @include('livewire.cuidados.ficha.tab-historial')
        </div>
    @endif

    @include('livewire.cuidados.ficha.modales')
</div>
