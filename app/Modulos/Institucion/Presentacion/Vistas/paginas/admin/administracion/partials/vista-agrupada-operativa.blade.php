@php $campoAgrupacion = $vista === 'cobertura' ? 'detalle' : 'estado'; @endphp
<p class="rm-process-board__help"><i class="ph-bold {{ $vista === 'cobertura' ? 'ph-map-trifold' : 'ph-kanban' }}" aria-hidden="true"></i>{{ $vista === 'cobertura' ? 'Cobertura por área' : 'Seguimiento por estado' }} · agrupación de los registros de esta página. Abre una ficha para consultar; esta vista no cambia estados.</p>
<div class="rm-process-board is-{{ $vista }}">
    @foreach($registros->getCollection()->groupBy(fn($registro) => $registro->{$campoAgrupacion} ?: 'Sin registrar') as $grupo => $filas)
        <section><header><span><i class="ph-bold {{ $vista === 'cobertura' ? 'ph-buildings' : 'ph-flag' }}" aria-hidden="true"></i>{{ str_replace('_', ' ', $grupo) }}</span><strong>{{ $filas->count() }} en esta página</strong><a wire:navigate class="rm-btn-secondary" href="{{ $vista === 'cobertura' ? $enlace(['categoria' => $grupo === 'Sin registrar' ? '__sin_categoria__' : $grupo]) : $enlace(['estado' => $grupo === 'Sin registrar' ? '__sin_estado__' : $grupo, 'tab' => $bandejaGeneral, 'categoria' => null]) }}">Ver registros del grupo <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a></header><div class="rm-operations-cards">@foreach($filas as $registro)@include('pages.admin.administracion.partials.tarjeta-operativa')@endforeach</div></section>
    @endforeach
</div>
