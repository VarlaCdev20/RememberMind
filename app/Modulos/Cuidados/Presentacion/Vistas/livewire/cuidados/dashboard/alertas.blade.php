<x-ui.card class="rm-nursing-dashboard__alerts rm-nursing-module" aria-label="Alertas prioritarias">
    <x-ui.section-header title="Alertas prioritarias" id="nursing-alerts-title" :subtitle="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Seguimiento de Enfermería' : 'Abiertas sin atención'"
        icon="ph-warning-circle" :count="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? null : ($dashboard['alertas_pendientes_count'] ?? 0)" level="2">
        <x-slot:actions>
            @if($puedeVerAlertas)
                <a class="rm-nursing-module__more" href="{{ route('admin.enfermeria.alertas') }}">Ver todas <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
            @endif
        </x-slot:actions>
    </x-ui.section-header>
    <div class="rm-nursing-module__loading" wire:loading aria-live="polite">
        @for($i = 0; $i < 4; $i++)
            <x-ui.skeleton variant="card" label="Cargando alertas" />
        @endfor
    </div>
    <div class="rm-nursing-module__content" wire:loading.remove>
        @forelse($alertasPendientes as $alerta)
            <x-ui.priority-alert :alert="$alerta"
                :href="$puedeVerAlertas ? route('admin.enfermeria.alertas') : null" />
        @empty
            <x-ui.empty-state compact :icono="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'ph-warning-circle' : 'ph-check-circle'" :titulo="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Sin contexto del turno' : 'Sin alertas prioritarias'" :texto="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Consulta Alertas para revisar los registros disponibles.' : 'No hay situaciones que requieran atención inmediata.'" />
        @endforelse
    </div>
</x-ui.card>
