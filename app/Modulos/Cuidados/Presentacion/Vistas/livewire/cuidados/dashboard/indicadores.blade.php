<section class="rm-nursing-dashboard__overview" aria-label="Atención de este turno">
<x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--risk"
        icon="ph-shield-warning" variant="neutral" :value="$enTurno ? $stats['alertas_activas'] : null" label="Alertas del turno"
        :description="$enTurno ? 'Abiertas o en atención' : 'Sin turno activo'" href="#nursing-alerts-title" />
<x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--medication"
        icon="ph-pill" :variant="$medicacionVariante" :value="$medicacionPendiente"
        label="Medicamentos pendientes"
        :description="$medicacionPendiente === null ? 'Sin métrica disponible' : 'Del turno actual'"
        :href="$enTurno && Route::has('admin.enfermeria.medicacion') && auth()->user()?->can('enfermeria.ver_dashboard') ? route('admin.enfermeria.medicacion') : null" />
<x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--beds"
        icon="ph-list-checks" variant="sky" :value="$enTurno ? $accionesPendientes : null"
        label="Cuidados pendientes"
        :description="$enTurno ? 'Acciones programadas del turno' : 'Sin turno activo'" href="#nursing-schedule-title" />
<x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--patients"
        icon="ph-users-three" variant="mint" :value="$pacientesKpi" label="Residentes"
        :description="$enTurno ? 'Asignados a mi turno' : 'Mis residentes · Solo consulta'"
        :href="Route::has('admin.enfermeria.pacientes') && auth()->user()?->can('enfermeria.ver_pacientes_asignados') ? route('admin.enfermeria.pacientes') : null" />
</section>
