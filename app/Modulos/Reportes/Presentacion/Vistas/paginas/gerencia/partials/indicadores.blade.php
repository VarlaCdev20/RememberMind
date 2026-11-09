<div class="rm-management-overview" aria-label="Personal, ocupación y avisos documentales">
    <x-ui.metric-card icon="ph-identification-badge" label="Personal activo" :value="$reporte['personal']" :description="($compacto ?? false) ? ($reporte['personal'] === null ? 'Fuente no autorizada' : '') : 'Trabajadores con estado laboral activo'" :href="isset($enlaces) ? ($enlaces['personal'] ?? null) : null" />
    <x-ui.metric-card icon="ph-bed" label="Camas ocupadas" :value="$reporte['ocupadas']" :description="$reporte['camas'] === null ? 'Fuente no autorizada' : 'De '.$reporte['camas'].' camas registradas'" :href="isset($enlaces) ? ($enlaces['ocupacion'] ?? null) : null" />
    <x-ui.metric-card icon="ph-bell-ringing" label="Avisos documentales" :value="$reporte['avisos_documentales']" :description="$reporte['avisos_documentales'] === null ? 'Consulta no disponible' : (($compacto ?? false) ? '' : 'Pendientes de documentación laboral')" :href="$enlaces['alertas'] ?? null" />
</div>
