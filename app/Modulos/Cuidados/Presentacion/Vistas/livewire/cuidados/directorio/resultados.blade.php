<section class="rm-resident-directory__results animate-fade-in-up" aria-labelledby="residents-results-title" wire:loading.class="is-loading" wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
    <div class="rm-resident-directory__results-header">
        <div>
            <h2 id="residents-results-title">{{ $stats['total'] }} {{ $stats['total'] === 1 ? 'residente' : 'residentes' }} {{ ($esSuperAdmin || $esModoConsulta) ? ($stats['total'] === 1 ? 'disponible' : 'disponibles') : ($stats['total'] === 1 ? 'asignado' : 'asignados') }}</h2>
        </div>
        <span class="rm-resident-directory__shown" aria-live="polite">@if(count($filtrosActivos) > 0) Mostrando {{ $pacientes->total() }} de {{ $stats['total'] }} @endif</span>
    </div>
    <div wire:loading wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros" class="rm-resident-directory__loading" role="status" aria-label="Actualizando residentes">
        @if($vistaModo === 'tarjetas')
            <div class="rm-resident-directory__cards rm-resident-directory__skeletons" aria-hidden="true">
                @for($i = 0; $i < 3; $i++)
                    <div class="rm-resident-directory__skeleton-card"><span class="rm-resident-directory__skeleton-circle"></span><span class="rm-resident-directory__skeleton-line"></span><span class="rm-resident-directory__skeleton-line short"></span><span class="rm-resident-directory__skeleton-block"></span></div>
                @endfor
            </div>
        @else
            <div class="rm-resident-directory__skeleton-rows" aria-hidden="true">
                @for($i = 0; $i < 3; $i++)<span></span>@endfor
            </div>
        @endif
        <span class="sr-only">Actualizando residentes…</span>
    </div>

    @if($errorCarga)
        <div class="rm-resident-directory__empty" role="alert" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
            <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-warning-circle"></i></span>
            <h3>No pudimos cargar los residentes</h3>
            <p>Intenta nuevamente en unos momentos.</p>
            <button type="button" wire:click="$refresh" class="rm-btn-secondary">Reintentar</button>
        </div>
    @elseif($pacientes->isEmpty())
        <div class="rm-resident-directory__empty" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
            <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-users-three"></i></span>
            @if($stats['total'] === 0)
                <h3>{{ $esSuperAdmin ? 'No hay residentes disponibles' : 'No tienes residentes asignados en esta jornada.' }}</h3>
                <p>{{ $esSuperAdmin ? 'No se encontraron residentes en el alcance de esta consulta.' : 'No hay residentes asociados a tu turno actual.' }}</p>
                @unless($esSuperAdmin)<a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn-secondary">Consultar información de mi turno <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endunless
            @else
                <h3>No encontramos residentes con estos filtros.</h3>
                <p>Prueba con otro nombre, habitación o estado.</p>
                <button type="button" wire:click="limpiarFiltros" class="rm-btn-secondary">Limpiar filtros</button>
            @endif
        </div>
    @elseif($vistaModo === 'tarjetas')
        <div class="rm-resident-directory__cards" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
            @foreach($pacientes as $paciente)
                <x-ui.resident-card :resident="$paciente" mode="card" variant="nursing" :selected="$this->residente === $paciente->cod_residente" select-method="seleccionarResidente" context-label="Próximo cuidado" :context-value="$paciente->proxima_atencion_texto" :context-time="$paciente->proxima_atencion_hora" :alert-count="$paciente->alertas_criticas_count" :key="'card-'.$paciente->cod_residente">
                    <x-slot:menu>@include('livewire.cuidados.partials.nursing-resident-menu', ['paciente' => $paciente, 'modoConsulta' => $esModoConsulta || $esSuperAdmin || app(\App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService::class)->isActive(auth()->user())])</x-slot:menu>
                </x-ui.resident-card>
            @endforeach
        </div>
    @else
        <div class="rm-resident-directory__list" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
            <div class="rm-resident-directory__list-heading" aria-hidden="true"><span>Residente</span><span>Ubicación</span><span>Estado</span><span>Próximo cuidado</span><span></span></div>
            @foreach($pacientes as $paciente)
                <x-ui.resident-card :resident="$paciente" mode="row" variant="nursing" :selected="$this->residente === $paciente->cod_residente" select-method="seleccionarResidente" context-label="Próximo cuidado" :context-value="$paciente->proxima_atencion_texto" :context-time="$paciente->proxima_atencion_hora" :status-label="$paciente->estado_label" :status-tone="$paciente->estado_color" :key="'row-'.$paciente->cod_residente" />
            @endforeach
        </div>
    @endif
</section>

@if($pacientes->hasPages())
    <nav class="rm-resident-directory__pagination" aria-label="Páginas de residentes">{{ $pacientes->links() }}</nav>
@endif
