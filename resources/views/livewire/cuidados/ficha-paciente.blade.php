<div class="space-y-6" x-data="{ activeTab: @entangle('tabActivo') }">
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

    {{-- CABECERA INSTITUCIONAL CLÍNICA --}}
    @include('livewire.cuidados.ficha.cabecera')
    <x-residentes.navegacion-ficha :adulto="$adultoMayor" />

    {{-- PANELES MODULARES SEGÚN PESTAÑA ACTIVA --}}
    {{-- 1. Resumen Clínico --}}
    <div x-show="activeTab === 'resumen'">
        @include('livewire.cuidados.ficha.tab-resumen')
    </div>

    {{-- 2. Signos Vitales --}}
    <div x-show="activeTab === 'signos'" x-cloak>
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
    <div x-show="activeTab === 'seguimiento'" x-cloak>
        @include('livewire.cuidados.ficha.tab-seguimiento')
    </div>

    {{-- 6. Alertas --}}
    <div x-show="activeTab === 'alertas'" x-cloak>
        @include('livewire.cuidados.ficha.tab-alertas')
    </div>

    {{-- 7. Historial 360° --}}
    <div x-show="activeTab === 'historial'" x-cloak>
        @include('livewire.cuidados.ficha.tab-historial')
    </div>

    {{-- MODALES CLÍNICOS OPERATIVOS --}}
    @include('livewire.cuidados.ficha.modales')
</div>
