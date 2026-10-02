<div class="rm-clinical-sections">
    @can('atenciones.ver')
        <section class="rm-section" aria-labelledby="atenciones-titulo">
            <div class="rm-section-heading"><h2 id="atenciones-titulo">Atenciones recientes</h2></div>
            <div class="rm-card rm-card-body">
                @if($atenciones->isEmpty())
                    <p class="rm-page-subtitle">Todavía no hay atenciones registradas para este residente.</p>
                @else
                    <ol class="rm-data-list">
                        @foreach($atenciones as $atencion)
                            <li>
                                <div class="rm-record-line"><strong>{{ $atencion->tipo_atencion }}</strong><x-ui.status-badge :estado="$atencion->estado" /></div>
                                <small>{{ $atencion->fecha_hora?->format('d/m/Y H:i') ?? 'Sin fecha' }}@if($atencion->personal) · {{ trim($atencion->personal->nombres.' '.$atencion->personal->apellido_paterno) }}@endif</small>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    @endcan

    @can('prescripciones.ver')
        <section class="rm-section" aria-labelledby="prescripciones-titulo">
            <div class="rm-section-heading"><h2 id="prescripciones-titulo">Prescripciones recientes</h2></div>
            <div class="rm-card rm-card-body">
                @if($prescripciones->isEmpty())
                    <p class="rm-page-subtitle">Todavía no hay prescripciones registradas para este residente.</p>
                @else
                    <ol class="rm-data-list">
                        @foreach($prescripciones as $prescripcion)
                            <li>
                                <div class="rm-record-line"><strong>{{ $prescripcion->medicamento?->nombre_generico ?? 'Medicamento sin nombre disponible' }}</strong><x-ui.status-badge :estado="$prescripcion->estado" /></div>
                                <small>{{ $prescripcion->fecha_hora_prescripcion?->format('d/m/Y H:i') ?? 'Sin fecha' }} · Vía: {{ $prescripcion->via_administracion ?: 'Sin especificar' }}</small>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    @endcan
</div>
