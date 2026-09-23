<div class="space-y-5" x-data="{ activeTab: @entangle('tabActivo'), modalSelectorAtencion: false, modalExportar: false, drawerExpediente: false, drawerFamilia: false }" @keydown.escape.window="modalSelectorAtencion = false; modalExportar = false; drawerExpediente = false; drawerFamilia = false">
    {{-- MENSAJES DE NOTIFICACIÓN CLÍNICA --}}
    @if(session()->has('success'))
        <div class="flex items-center gap-2 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-xs font-bold text-emerald-800 shadow-sm">
            <i class="ph-bold ph-check-circle text-base text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="flex items-center gap-2 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs font-bold text-rose-800 shadow-sm">
            <i class="ph-bold ph-warning-octagon text-base text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session()->has('warning'))
        <div class="flex items-center gap-2 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs font-bold text-amber-800 shadow-sm">
            <i class="ph-bold ph-warning text-base text-amber-600"></i>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

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
