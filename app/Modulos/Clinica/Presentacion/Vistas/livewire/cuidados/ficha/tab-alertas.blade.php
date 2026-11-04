<div class="rm-emergency-workspace space-y-5">
@if(auth()->user()->can('alertas.ver') && !auth()->user()->hasRole('FAMILIAR'))
    @php
        $grupos = ['Alertas activas' => $adultoMayor->alertas->whereIn('estado', \App\Models\Alerta::ESTADOS_ACTIVOS), 'Historial de alertas' => $adultoMayor->alertas->whereNotIn('estado', \App\Models\Alerta::ESTADOS_ACTIVOS)];
    @endphp
    @foreach($grupos as $titulo => $registros)
        <section class="space-y-3"><h2 class="rm-section-title">{{ $titulo }} · {{ $registros->count() }}</h2>
        @forelse($registros->sortByDesc('fecha_hora') as $al)
            <article class="rm-alert-trace" data-priority="{{ $al->prioridad }}"><header><h3>{{ $al->tipo }}</h3><div class="flex flex-wrap gap-2"><span class="rm-badge rm-alert-severity {{ in_array($al->prioridad,['CRITICO','CRITICA']) ? 'is-critical' : (in_array($al->prioridad,['ALTO','ALTA']) ? 'is-high' : '') }}">Prioridad {{ $al->prioridad }}</span><x-ui.status-badge :estado="$al->estado" /></div></header><p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($al->descripcion) }}</p><p>{{ $al->fecha_hora?->format('d/m/Y H:i') ?: 'Sin fecha registrada' }} · {{ $al->modulo ?: 'Sin origen registrado' }} · {{ $al->getKey() }}</p><a class="rm-btn rm-btn-secondary" href="{{ route(auth()->user()->hasRole('ENFERMEROS') ? 'admin.enfermeria.alertas' : 'admin.alertas-clinicas.index', ['alerta'=>$al->getKey()]) }}">Ver detalle y seguimiento</a></article>
        @empty<x-ui.empty-state compact icono="ph-bell-slash" :titulo="$titulo === 'Alertas activas' ? 'Sin alertas activas registradas' : 'Sin historial de alertas'" texto="La ausencia de alertas no determina el estado clínico del residente." />@endforelse
        </section>
    @endforeach
@else
    <x-ui.callout variant="info" title="Consulta restringida">Esta sección requiere autorización para consultar alertas internas.</x-ui.callout>
@endif
</div>
