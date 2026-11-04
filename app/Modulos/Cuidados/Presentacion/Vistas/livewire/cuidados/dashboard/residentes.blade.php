<x-ui.card class="rm-nursing-dashboard__patients rm-nursing-module" aria-label="Residentes del turno">
    <x-ui.section-header title="Residentes del turno"
        :subtitle="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Asignaciones de Enfermería' : (($dashboard['modo'] ?? '') === 'EN_TURNO' ? 'Asignados a mi turno activo' : 'Mis residentes asignados · solo lectura')"
        icon="ph-users-three" level="2">
        <x-slot:actions>
            @if($puedeVerPacientes)
                <a class="rm-nursing-module__more" href="{{ route('admin.enfermeria.pacientes') }}">Ver todos <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
            @endif
        </x-slot:actions>
    </x-ui.section-header>
    <div class="rm-nursing-module__loading" wire:loading aria-live="polite">
        @for($i = 0; $i < 5; $i++)
            <x-ui.skeleton variant="text" label="Cargando pacientes" />
        @endfor
    </div>
    <div class="rm-nursing-module__content" wire:loading.remove>
        @if(count($pacientesTurno))
            <div class="rm-patient-turn-table" aria-label="Residentes asignados al turno">
                <div class="rm-patient-turn-table__head" aria-hidden="true">
                    <span>Residente y ubicación</span>
                    <span>Información clínica</span>
                    <span>Próxima atención</span>
                </div>
                @foreach($pacientesTurno as $paciente)
                    <x-ui.patient-turn-row :patient="$paciente"
                        :href="$puedeVerPacientes && !empty($paciente['cod_residente']) ? route('admin.enfermeria.pacientes', ['residente' => $paciente['cod_residente']]) : null" />
                @endforeach
            </div>
        @else
            <x-ui.empty-state compact icono="ph-users-three" titulo="Sin residentes asignados para consulta" :texto="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'No tienes asignaciones propias vigentes para consultar hoy.' : 'Revisa las asignaciones de la jornada actual.'" />
        @endif
    </div>
</x-ui.card>
