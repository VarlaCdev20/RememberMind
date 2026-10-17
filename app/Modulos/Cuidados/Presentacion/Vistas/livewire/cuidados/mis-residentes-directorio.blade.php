<div class="rm-resident-directory"
    x-data="rmClinicalFormFeedback()"
    @cuidado-registrado.window="confirmCareFeedback()"
    @dolor-registrado.window="confirmFeedback('dolor')"
    @resident-directory-opened.window="$nextTick(() => $el.querySelector('.rm-drawer-header h2')?.focus())"
    @resident-directory-step-changed.window="feedbackAttempt = null; $nextTick(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus())"
    @resident-directory-selector-opened.window="feedbackAttempt = null; $nextTick(() => requestAnimationFrame(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus()))"
    @resident-directory-selector-closed.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('#resident-register-trigger')?.focus()))">
    @php
        $cuidadoActual = $this->cuidadoSeleccionado();
    @endphp
    @include('cuidados::livewire.cuidados.directorio.cabecera')

    <x-ui.resident-filter-toolbar
        :search="$search"
        :filtro-rapido="$filtroRapido"
        :filtro-alertas="$filtroAlertas"
        :filtro-medicacion="$filtroMedicacion"
        :filtro-cuidados="$filtroCuidados"
        :filtro-habitacion="$filtroHabitacion"
        :orden="$orden"
        :vista-modo="$vistaModo"
        :stats="$stats"
        :habitaciones="$habitaciones"
        :habitaciones-conteo="$habitacionesConteo ?? null"
        :filtros-activos="$filtrosActivos" />

    @include('cuidados::livewire.cuidados.directorio.resultados')

    @include('cuidados::livewire.cuidados.directorio.resumen')

    @include('cuidados::livewire.cuidados.directorio.registro')
    @include('livewire.cuidados.partials.clinical-operation-result', ['clinicalResultResidentCode' => $detalleResidente['cod_residente'] ?? null])
</div>
