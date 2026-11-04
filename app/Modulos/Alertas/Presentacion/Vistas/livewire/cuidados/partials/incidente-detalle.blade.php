<x-ui.modal-livewire id="incidente-detalle" wire:model="modalVer" title="Detalle del incidente" :subtitle="$incidenteDetalle->fecha_hora->format('d/m/Y · H:i')" maxWidth="lg" closeMethod="cerrarModalVer" :showValidation="false">
    <x-slot:icon><i class="ph-bold ph-clipboard-text" aria-hidden="true"></i></x-slot:icon>
    <div class="rm-incident-detail">
        <section class="rm-incident-detail__context" aria-label="Residente y evento">
            <div><span>Residente</span><h3>{{ $incidenteDetalle->residente?->nombre_completo ?? 'Residente no disponible' }}</h3><p>{{ $incidenteDetalle->residente?->ubicacion_formateada ?? 'Sin ubicación' }}</p></div>
            <div><span>Fecha y hora del evento</span><strong>{{ $incidenteDetalle->fecha_hora->format('d/m/Y · H:i') }}</strong><p>{{ $incidenteDetalle->lugar ?: 'Lugar no especificado' }}</p></div>
        </section>
        <div class="rm-incident-detail__classification">
            <span class="rm-incident__type">{{ $incidenteDetalle->tipo_incidente }}</span>
            <x-ui.status-badge :estado="$incidenteDetalle->gravedad" :variant="match($incidenteDetalle->gravedad) { 'ALTA', 'CRITICA' => 'danger', 'MEDIA' => 'warning', default => 'neutral' }" :label="'Gravedad: '.($incidenteDetalle->gravedad === 'CRITICA' ? 'crítica' : strtolower($incidenteDetalle->gravedad))" />
            <x-ui.status-badge :estado="$incidenteDetalle->estado" :label="ucfirst(str_replace('_', ' ', strtolower($incidenteDetalle->estado)))" :variant="match($incidenteDetalle->estado) { 'CERRADO' => 'success', 'EN_SEGUIMIENTO' => 'info', 'ANULADO' => 'neutral', default => 'warning' }" />
        </div>
        @if($alertaVinculada)
            <section class="rm-incidents-notice" data-tone="danger" aria-label="Alerta vinculada"><i class="ph-bold ph-bell-ringing" aria-hidden="true"></i><div><strong>Alerta vinculada</strong><p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alertaVinculada->titulo) }}</p><p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alertaVinculada->descripcion) }}</p><p>Prioridad: {{ $alertaVinculada->prioridad }}</p></div></section>
        @endif
        <div class="rm-incident-detail__narrative">
            <section><h3><i class="ph-bold ph-note-pencil" aria-hidden="true"></i>Qué ocurrió</h3><p>{{ $incidenteDetalle->descripcion }}</p></section>
            <section><h3><i class="ph-bold ph-first-aid" aria-hidden="true"></i>Medida inmediata</h3><p>{{ $incidenteDetalle->medida_inmediata ?: 'Sin medida inmediata registrada.' }}</p></section>
        </div>
        <dl class="rm-incident-detail__requirements">
            <div><dt>Valoración médica requerida</dt><dd>{{ $incidenteDetalle->requiere_medico ? 'Sí' : 'No' }}</dd></div>
            <div><dt>Derivación requerida</dt><dd>{{ $incidenteDetalle->requiere_derivacion ? 'Sí' : 'No' }}</dd></div>
        </dl>
        @if($incidenteDetalle->observacion)
            <section class="rm-incident-detail__observations"><h3><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i>Observaciones y cambios de estado</h3><p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($incidenteDetalle->observacion) }}</p></section>
        @endif
        <section class="rm-incident-detail__contacts">
            <h3><i class="ph-bold ph-phone" aria-hidden="true"></i>Contactos de emergencia y responsables</h3>
            @forelse($contactosEmergencia as $contacto)
                <div class="rm-incident-detail__contact"><div><strong>{{ $contacto['nombre'] }}</strong><p>{{ $contacto['parentesco'] }} · {{ $contacto['telefono'] }}</p>@if($contacto['es_emergencia'])<span>Contacto de emergencia</span>@endif</div>
                    @if($contacto['telefono'] !== 'Sin teléfono')<a class="rm-btn rm-btn-secondary rm-btn-md" href="tel:{{ preg_replace('/[^0-9+]/', '', $contacto['telefono']) }}"><i class="ph-bold ph-phone" aria-hidden="true"></i>Llamar</a>@endif
                </div>
            @empty
                <p>No hay contactos de emergencia o responsables registrados para este residente.</p>
            @endforelse
        </section>
    </div>
    <x-slot:footer>
        <p class="rm-incident-detail__author">Registrado por: <strong>{{ $incidenteDetalle->personal?->usuario?->name ?? 'Profesional no disponible' }}</strong></p>
        <x-ui.action-button variant="secondary" wire:click="cerrarModalVer">Cerrar detalle</x-ui.action-button>
    </x-slot:footer>
</x-ui.modal-livewire>
