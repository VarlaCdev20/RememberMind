<div class="rm-cognicion__filters">
    <label for="cognicion-history-period">Periodo<select id="cognicion-history-period" wire:model.live="dias" class="rm-select"><option value="7">Últimos 7 días</option><option value="30">Últimos 30 días</option><option value="90">Últimos 90 días</option><option value="0">Personalizado</option></select></label>
    <label for="cognicion-history-domain">Dominio<select id="cognicion-history-domain" wire:model.live="dominioHistorial" class="rm-select"><option value="">Todos los dominios</option>@foreach($dominios as $clave => $label)<option value="{{ $clave }}">{{ $label }}</option>@endforeach</select></label>
    <label for="cognicion-history-order">Orden<select id="cognicion-history-order" wire:model.live="ordenHistorial" class="rm-select"><option value="desc">Más recientes primero</option><option value="asc">Más antiguos primero</option></select></label>
</div>
@if($dias === 0)<div class="rm-cognicion__filters"><x-ui.field label="Desde" for="cognicion-history-from" error="desde"><input id="cognicion-history-from" type="date" class="rm-input" wire:model.live="desde" max="{{ today()->toDateString() }}"></x-ui.field><x-ui.field label="Hasta" for="cognicion-history-to" error="hasta"><input id="cognicion-history-to" type="date" class="rm-input" wire:model.live="hasta" max="{{ today()->toDateString() }}"></x-ui.field></div>@endif
<p wire:loading role="status">Cargando historial…</p>
@if($errorConsulta)<p role="alert">{{ $errorConsulta }}</p><button type="button" class="rm-btn-secondary" wire:click="reintentarConsulta">Reintentar consulta</button>
@elseif(empty($contexto['historial']))<p class="rm-cognicion__empty">{{ $contexto['hayHistoria'] ? 'No se encontraron registros para los filtros seleccionados.' : 'Todavía no existen observaciones cognitivas registradas para este residente.' }}</p>
@else
    @foreach($contexto['historial'] as $registro)
        <article class="rm-cognicion__card" wire:key="cognicion-history-{{ $registro['codigo'] }}"><header><i class="ph-bold ph-brain" aria-hidden="true"></i><div><strong>{{ $registro['fecha'] }} · {{ $registro['hora'] }}</strong><p>{{ $registro['profesional'] }} · {{ $registro['estado'] }}</p></div></header>
            <p>{{ collect($registro['respuestas'])->filter(fn($v) => $v !== null)->count() }} indicadores registrados</p>
            <p>{{ $registro['observacion'] ?: 'Sin observación textual.' }}</p>
            <details><summary>Ver detalle del registro</summary><dl class="rm-cognicion__answers">@foreach($campos as $campo => [$label])<div><dt>{{ $label }}</dt><dd>{{ \App\Modulos\Cuidados\Dominio\ObservacionCognitiva::etiqueta($campo, $registro['respuestas'][$campo]) }}</dd></div>@endforeach</dl></details>
        </article>
    @endforeach
    <nav class="rm-cognicion__actions" aria-label="Paginación del historial"><button type="button" class="rm-btn-secondary" wire:click="cambiarPagina({{ $contexto['pagina'] - 1 }})" @disabled($contexto['pagina'] <= 1) wire:loading.attr="disabled">Anterior</button><span>Página {{ $contexto['pagina'] }} de {{ $contexto['paginas'] }} · {{ $contexto['total'] }} registros</span><button type="button" class="rm-btn-secondary" wire:click="cambiarPagina({{ $contexto['pagina'] + 1 }})" @disabled($contexto['pagina'] >= $contexto['paginas']) wire:loading.attr="disabled">Siguiente</button></nav>
@endif
