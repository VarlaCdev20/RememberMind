<div class="rm-management-overview" aria-label="Personal, ocupación y alertas">
    <x-ui.metric-card icon="ph-identification-badge" label="Personal activo" :value="$reporte['personal']" description="Trabajadores con estado laboral activo" :href="isset($enlaces) ? ($enlaces['personal'] ?? null) : null" />
    <x-ui.metric-card icon="ph-bed" label="Camas ocupadas" :value="$reporte['ocupadas']" :description="$reporte['camas'] === null ? 'Fuente no autorizada' : 'De '.$reporte['camas'].' camas registradas'" :href="isset($enlaces) ? ($enlaces['ocupacion'] ?? null) : null" />
    <x-ui.metric-card icon="ph-bell-ringing" label="Alertas activas" :value="$reporte['alertas']" description="Avisos persistidos, sin contar eventos duplicados" :href="isset($enlaces) ? ($enlaces['alertas'] ?? null) : null" />
</div>
