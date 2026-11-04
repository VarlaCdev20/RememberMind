{{-- Dashboard operativo de Enfermería: jerarquía de lectura y teclado alineadas. --}}
<section class="rm-nursing-dashboard rm-dashboard-composition font-sans" @unless(app(\App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService::class)->isActive(auth()->user())) wire:poll.60s="refrescarTurno" @endunless aria-label="Dashboard de Enfermería">
        <x-ui.dashboard-welcome-header
        class="rm-nursing-dashboard__welcome"
        :usuario="Auth::user()"
        :estado="$dashboard['estado'] ?? null"
        :modo="$dashboard['modo'] ?? null"
        image="images/dashboard/enfermera-manos.webp"
        :scope="isset($dashboard['jornada']['nombre']) ? implode(' · ', array_filter([$dashboard['jornada']['nombre'], $dashboard['jornada']['hora_inicio'] ?? null, $dashboard['jornada']['hora_fin'] ?? null, ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Solo consulta' : null])) : 'Sin jornada activa'"
    />
    <x-ui.dashboard-divider />

    @php
        $enTurno = ($dashboard['modo'] ?? '') === 'EN_TURNO';
        $pacientesKpi = $stats['pacientes'] ?? null;
        $medicacionTurno = $enTurno ? ($dashboard['medicacion_turno'] ?? null) : null;
        $medicacionPendiente = $medicacionTurno['pendientes'] ?? null;
        $medicacionVariante = ($medicacionTurno['retrasadas'] ?? 0) > 0
            ? 'critical'
            : ($medicacionPendiente > 0 ? 'coral' : 'mint');
        $totalSeguimiento = array_sum($distribucionPacientes);
        $accionesTurno = $dashboard['estado_tareas'] ?? [];
        $accionesRealizadas = (int) ($accionesTurno['realizadas'] ?? 0);
        $accionesPendientes = (int) ($accionesTurno['pendientes'] ?? 0);
        $accionesRetrasadas = (int) ($accionesTurno['retrasadas'] ?? 0);
    @endphp

    @php
        $alertasPendientes = $dashboard['alertas_prioritarias'] ?? [];
        $pacientesTurno = $dashboard['pacientes_turno'] ?? [];
        $puedeVerAlertas = Route::has('admin.enfermeria.alertas');
        $puedeVerPacientes = Route::has('admin.enfermeria.pacientes');
    @endphp

    @include('cuidados::livewire.cuidados.dashboard.indicadores')
    @include('cuidados::livewire.cuidados.dashboard.alertas')

    @include('cuidados::livewire.cuidados.dashboard.agenda')

    @include('cuidados::livewire.cuidados.dashboard.controles')

    @include('cuidados::livewire.cuidados.dashboard.residentes')

    @include('cuidados::livewire.cuidados.dashboard.tareas')

    @include('cuidados::livewire.cuidados.dashboard.conducta')

<section class="rm-nursing-dashboard__analytics" aria-label="Resumen visual del turno">
    @include('cuidados::livewire.cuidados.dashboard.incidentes')

    @include('cuidados::livewire.cuidados.dashboard.seguimiento')

    @include('cuidados::livewire.cuidados.dashboard.actividad')

</section>
    @include('cuidados::livewire.cuidados.dashboard.ubicaciones')
</section>
