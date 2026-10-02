<x-sistema-layout>
    <div class="rm-page-layout">
        <x-ui.page-header titulo="Panel institucional" subtitulo="Información disponible según tu función y tus permisos." :breadcrumb="[['label' => 'Inicio']]" />

        @if(count($resumen))
            @php
                $metricas = [
                    'residentes_admitidos' => ['Residentes admitidos', 'ph-users-three', auth()->user()->can('viewAny', App\Models\Residente::class) ? route('admin.residentes.index') : null],
                    'preadmisiones_pendientes' => ['Preadmisiones pendientes', 'ph-clipboard-text', route('admin.preadmisiones.index')],
                    'admisiones_activas' => ['Admisiones activas', 'ph-house-line', null],
                    'camas_ocupadas' => ['Camas ocupadas', 'ph-bed', null],
                    'alertas_abiertas' => ['Alertas abiertas', 'ph-bell', '#alertas'],
                ];
            @endphp
            <section class="rm-section" aria-labelledby="resumen-titulo">
                <div class="rm-section-heading"><h2 id="resumen-titulo">Resumen actual</h2></div>
                <div class="rm-kpi-grid">
                    @foreach($resumen as $clave => $valor)
                        <x-ui.metric-card :etiqueta="$metricas[$clave][0]" :valor="$valor" :icono="$metricas[$clave][1]" :href="$metricas[$clave][2]" />
                    @endforeach
                </div>
            </section>
        @else
            <x-ui.empty-state icono="ph-squares-four" titulo="Sin indicadores disponibles" texto="Tu cuenta todavía no tiene indicadores asignados para este panel." />
        @endif

        @isset($residentesVinculados)
            @if($residentesVinculados->isNotEmpty())
                <section class="rm-section" aria-labelledby="residentes-vinculados-titulo">
                    <div class="rm-section-heading"><h2 id="residentes-vinculados-titulo">Residentes vinculados</h2></div>
                    <div class="rm-card rm-card-body">
                        <ul class="rm-data-list">
                            @foreach($residentesVinculados as $residente)
                                <li><a class="rm-inline-link" href="{{ route('admin.residentes.show', $residente) }}">{{ trim($residente->nombres.' '.$residente->apellido_paterno.' '.$residente->apellido_materno) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif
        @endisset

        @can('alertas.ver')
            <section id="alertas" class="rm-section" aria-labelledby="alertas-titulo">
                <div class="rm-section-heading"><h2 id="alertas-titulo">Alertas abiertas</h2><p>Seguimiento operativo pendiente de resolución.</p></div>
                <livewire:alertas.panel-alertas />
            </section>
        @endcan
    </div>
</x-sistema-layout>
