<div class="rm-dashboard-composition space-y-6 pb-8">
    @php
        $tituloMedico = match ($seccion) {
            'valoraciones' => 'Valoraciones pendientes',
            'decisiones' => 'Decisiones de admisión',
            default => 'Cuidar con evidencia',
        };
    @endphp
    <x-ui.role-dashboard-hero
        role="Medicina"
        :date="now()->locale('es')->translatedFormat('D d M Y')"
        scope="Atención clínica y seguimiento"
        eyebrow="CENTRO GERIÁTRICO LOS ALMENDROS"
        :title="$tituloMedico"
        highlight="es decidir con humanidad"
        description="Prioriza tus atenciones, revisa estudios y alertas, y consulta los registros clínicos recientes."
        :image="asset('images/FOTOS CENTRO DE ADULTOS MAYORES/593542266_1360929526044966_7396297662771591420_n.jpg')"
        rotation-context="medico"
        image-alt="Profesional de salud acompañando a residentes durante una actividad"
        quote="La atención clínica también empieza por escuchar"
        :meta="[['icon' => 'ph-calendar-blank', 'label' => now()->locale('es')->translatedFormat('d M Y')], ['icon' => 'ph-stethoscope', 'label' => 'Medicina']]"
    >
        <a href="{{ route('admin.medico.pacientes.observacion') }}" wire:navigate class="rm-btn-primary min-h-9 px-3.5 text-xs">
            <i class="ph-bold ph-users-three" aria-hidden="true"></i><span>Ver seguimiento</span>
        </a>
        <button type="button" wire:click="$refresh" class="rm-btn-secondary min-h-9 px-3.5 text-xs">
            <i class="ph-bold ph-arrows-clockwise" aria-hidden="true"></i><span>Actualizar</span>
        </button>
    </x-ui.role-dashboard-hero>

    @if($dashboardMetrics)
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores de medicina">
            @foreach($dashboardMetrics as $metric)
                <x-ui.metric-card :icon="$metric['icon']" :value="$metric['value']" :label="$metric['label']" :description="$metric['description']" :variant="$metric['variant']" />
            @endforeach
        </section>
    @endif

    <div class="rm-dashboard-data-grid" aria-label="Seguimiento médico">
        @foreach($dashboardPanels as $panel)
            <x-ui.dashboard-data-panel :panel="$panel" />
        @endforeach
    </div>

    @if($valoracionesPendientes->isNotEmpty())
        <section class="rm-medical-queue" aria-labelledby="medical-queue-title">
            <h2 id="medical-queue-title">Cola de valoraciones y decisiones</h2>
            <ul>
                @foreach($valoracionesPendientes as $residente)
                    <li>
                        <span><strong>{{ $residente->nombre_completo }}</strong><small>{{ str_replace('_', ' ', (string) $residente->estado) }}</small></span>
                        <span class="rm-medical-queue__actions">
                            @if($residente->estado === 'VALORACION_MEDICA')
                                <button type="button" wire:click="iniciarValoracionMedica('{{ $residente->cod_residente }}')">Valorar</button>
                            @elseif($residente->estado === 'DECISION_ADMISION')
                                <button type="button" wire:click="abrirDecisionAdmision('{{ $residente->cod_residente }}')">Dictamen</button>
                            @endif
                            <a href="{{ route('admin.medico.paciente.ficha', $residente->cod_residente) }}" wire:navigate aria-label="Abrir ficha de {{ $residente->nombre_completo }}"><i class="ph-bold ph-folder-open" aria-hidden="true"></i></a>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @livewire('valoraciones.valoracion-medica-modal')
    @livewire('admisiones.decision-admision-modal')
</div>
