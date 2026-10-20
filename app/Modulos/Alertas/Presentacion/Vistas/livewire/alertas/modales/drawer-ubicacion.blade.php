@if($drawerUbicacion && $adultoDrawer)
<x-ui.drawer-livewire wire:model="drawerUbicacion" title="Ubicación actual del residente" :subtitle="$adultoDrawer->nombre_completo" badge="Habitación y cama asignadas" icon="ph-map-pin" size="lg" close-method="cerrarDrawer">
    <div class="space-y-5">
        <p class="rm-help">Consulta la ubicación de la ocupación vigente del residente.</p>
        <dl class="rm-alert-case__facts">
            <div><dt>Habitación</dt><dd>{{ $adultoDrawer->cama?->habitacion?->codigo ?: 'Sin habitación registrada' }}</dd></div>
            <div><dt>Cama</dt><dd>{{ $adultoDrawer->cama?->codigo ?: 'Sin cama asignada actualmente' }}</dd></div>
            <div><dt>Piso o sector registrado</dt><dd>{{ $adultoDrawer->cama?->habitacion?->piso ?: 'Sin sector registrado' }}</dd></div>
            <div><dt>Tipo de habitación</dt><dd>{{ $adultoDrawer->cama?->habitacion?->tipo ?: 'Sin tipo registrado' }}</dd></div>
        </dl>
        @if(!$adultoDrawer->cama)
            <x-ui.callout variant="info" title="Sin ocupación activa registrada">Revisa la asignación institucional de cama en el proceso correspondiente.</x-ui.callout>
        @endif
    </div>
    <x-slot:footer><div class="flex flex-wrap gap-2">
        @if(auth()->user()->can('signos_vitales.ver'))
            <button type="button" wire:click="verGraficos('{{ $adultoDrawer->cod_residente }}')" class="rm-btn rm-btn-secondary">Consultar evolución y constantes</button>
        @endif
        <button type="button" wire:click="cerrarDrawer" class="rm-btn rm-btn-secondary">Volver a la consulta</button>
    </div></x-slot:footer>
</x-ui.drawer-livewire>
@endif
