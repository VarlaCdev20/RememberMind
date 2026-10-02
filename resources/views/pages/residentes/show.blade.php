<x-sistema-layout>
    <div class="rm-page-layout">
        <x-ui.page-header :titulo="trim($residente->nombres.' '.$residente->apellido_paterno.' '.$residente->apellido_materno)" subtitulo="Contexto del residente y registros disponibles para tu función." :breadcrumb="[['label' => 'Inicio', 'url' => route('dashboard')], ['label' => 'Residentes', 'url' => auth()->user()->can('viewAny', App\Models\Residente::class) ? route('admin.residentes.index') : route('dashboard')], ['label' => $residente->cod_residente]]">
            @can('viewAny', App\Models\Residente::class)<a class="rm-btn rm-btn-secondary" href="{{ route('admin.residentes.index') }}">Volver a residentes</a>@endcan
            @if(auth()->user()->can('atenciones.ver') && auth()->user()->can('prescripciones.ver'))<a class="rm-btn rm-btn-secondary" href="{{ route('admin.reportes.residente', $residente) }}">Descargar expediente PDF</a>@endif
        </x-ui.page-header>

        <section class="rm-card rm-card-body" aria-label="Contexto del residente">
            <div class="rm-resident-context">
                <div><span class="rm-context-label">Código</span><span class="rm-context-value">{{ $residente->cod_residente }}</span></div>
                <div><span class="rm-context-label">Estado</span><x-ui.status-badge :estado="$residente->estado" /></div>
                <div><span class="rm-context-label">Habitación</span><span class="rm-context-value">{{ $residente->ocupacionActiva?->cama?->habitacion?->codigo ?? 'Sin habitación activa' }}</span></div>
                <div><span class="rm-context-label">Cama</span><span class="rm-context-value">{{ $residente->ocupacionActiva?->cama?->codigo ?? 'Sin cama activa' }}</span></div>
            </div>
        </section>

        @can('signos_vitales.ver')
            <section class="rm-section" aria-labelledby="signos-vitales-titulo">
                <div class="rm-section-heading"><h2 id="signos-vitales-titulo">Signos vitales recientes</h2><p>Valores registrados, sin interpretación clínica automática.</p></div>
                @if($vitalStats->isNotEmpty())
                    <div class="rm-vitals-grid">
                        @foreach($vitalStats as $stat)
                            <x-ui.vital-stat :label="$stat['label']" :current="$stat['current']" :previous="$stat['previous']" :unit="$stat['unit']" :recorded-at="$stat['recordedAt']" />
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state icono="ph-heartbeat" titulo="Todavía no hay signos vitales" texto="Cuando se registre la primera medición, aparecerá aquí con su fecha y hora." />
                @endif
            </section>
        @endcan

        @if(! auth()->user()->hasRole('FAMILIAR') && auth()->user()->can('residentes_contactos.ver'))
            <section class="rm-section" aria-labelledby="contactos-titulo">
                <div class="rm-section-heading"><h2 id="contactos-titulo">Contactos vinculados</h2></div>
                <div class="rm-card rm-card-body">
                    @if($residente->vinculosContacto->isEmpty())
                        <p class="rm-page-subtitle">Todavía no hay contactos vinculados a este residente.</p>
                    @else
                        <ul class="rm-data-list">
                            @foreach($residente->vinculosContacto as $vinculo)
                                <li><strong>{{ trim($vinculo->contacto->nombres.' '.$vinculo->contacto->apellido_paterno) }}</strong> · {{ $vinculo->parentesco }} @if($vinculo->autoriza_informacion)<x-ui.status-badge estado="Autorizado" variant="success" />@endif</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        @endif

        @if(auth()->user()->can('atenciones.ver') || auth()->user()->can('prescripciones.ver'))
            <livewire:residentes.expediente-panel :residente="$residente" />
        @endif
    </div>
</x-sistema-layout>
