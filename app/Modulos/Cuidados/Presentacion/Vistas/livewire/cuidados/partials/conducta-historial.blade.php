@php($compacto = $compacto ?? false)
@if(empty($historial))
    <div class="rm-conducta__empty"><i class="ph-bold ph-notebook" aria-hidden="true"></i><strong>Sin registros de conducta</strong><p>{{ $filtroHistorial ? 'No hay registros que coincidan con este filtro.' : 'El seguimiento aparecerá después de confirmar un registro.' }}</p></div>
@else
    <div class="rm-conducta__history-list">
        @foreach($compacto ? array_slice($historial, 0, 4) : $historial as $item)
        <article class="rm-conducta__history-item" wire:key="conducta-{{ $historyContext ?? ($compacto ? 'recent' : 'full') }}-{{ $item['codigo'] }}">
            <header><time>{{ $item['fecha'] }} · {{ $item['hora'] }}</time><span>{{ $item['profesional'] }}</span></header>
            <dl><div><dt>Ánimo / participación</dt><dd>{{ filled($item['estado_animo']) ? $item['estado_animo'] : 'Sin registrar' }} · {{ filled($item['participacion']) ? $item['participacion'] : 'Sin registrar' }}</dd></div></dl>
            <div class="rm-conducta__history-tags">@foreach([...$indicadores, 'cambio_conducta' => ['Cambio de conducta']] as $campo => [$label])<span @class(['is-observed' => $item[$campo] === true])>{{ $label }}: {{ $item[$campo] === null ? 'Sin registrar' : ($item[$campo] ? 'Sí' : 'No') }}</span>@endforeach</div>
            @if(!$compacto)<dl>@foreach(['descripcion' => 'Descripción', 'intervencion' => 'Intervención', 'respuesta' => 'Respuesta'] as $campo => $label)<div><dt>{{ $label }}</dt><dd>{{ filled($item[$campo]) ? $item[$campo] : 'Sin registrar' }}</dd></div>@endforeach</dl>@endif
        </article>
        @endforeach
    </div>
    @if(!$compacto && ($contexto['paginas'] ?? 1) > 1)
    <nav class="rm-conducta__pagination" aria-label="Paginación del historial"><button type="button" class="rm-btn-secondary" wire:click="cambiarPagina({{ $contexto['pagina'] - 1 }})" @disabled($contexto['pagina'] <= 1)>Anterior</button><span>Página {{ $contexto['pagina'] }} de {{ $contexto['paginas'] }}</span><button type="button" class="rm-btn-secondary" wire:click="cambiarPagina({{ $contexto['pagina'] + 1 }})" @disabled($contexto['pagina'] >= $contexto['paginas'])>Siguiente</button></nav>
    @endif
@endif
