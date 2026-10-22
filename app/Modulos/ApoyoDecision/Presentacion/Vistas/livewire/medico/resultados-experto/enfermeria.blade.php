@php($texto = [\App\Modulos\ApoyoDecision\Aplicacion\DTOs\ConsultaCognitivaEnfermeria::class, 'texto'])
<div class="rm-cognitive-reading" x-data="{
    contextoFuera: false,
    contextoObserver: null,
    init() { this.$nextTick(() => {
        this.contextoObserver = new IntersectionObserver(([entry]) => { this.contextoFuera = !entry.isIntersecting && entry.boundingClientRect.top < 0; });
        this.contextoObserver.observe(this.$refs.residenteContexto);
    }); },
    destroy() { this.contextoObserver?.disconnect(); }
}">
    <header class="rm-expert-heading">
        <div><p class="rm-expert-eyebrow">Mis residentes / {{ $residente->nombre_completo }} / Evaluación cognitiva</p><h1>Evaluación cognitiva y cuidados</h1><p>Consulta de resultados, evidencias y seguimiento del residente.</p></div>
        <a class="rm-expert-back" href="{{ route('admin.enfermeria.pacientes', ['residente' => $codResidente]) }}"><i class="ph ph-arrow-left" aria-hidden="true"></i>Volver a Mis residentes</a>
    </header>
    <section class="rm-card rm-cognitive-identity" aria-label="Contexto del residente" x-ref="residenteContexto">
        <div class="rm-cognitive-identity-header">
            <div class="rm-cognitive-person"><span class="rm-cognitive-avatar" aria-hidden="true">{{ mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno ?? '', 0, 1) }}</span><div><p class="rm-cognitive-person-label">Residente en consulta</p><h2>{{ $residente->nombre_completo }}</h2><p>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }} · {{ $residente->ubicacion_formateada }}</p></div></div>
            <span class="rm-orion-state"><i class="ph ph-eye" aria-hidden="true"></i>Solo lectura · Enfermería</span>
        </div>
        <dl class="rm-cognitive-meta"><div><dt>Fecha de evaluación</dt><dd>{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($datos['evaluacion']['fecha'] ?? null, 'Sin evaluación disponible') }}</dd></div><div><dt>Tipo de consulta</dt><dd>{{ $simulacion ? 'Demostración técnica' : 'Resultados disponibles del expediente' }}</dd></div><div><dt>Acceso</dt><dd>Lectura de Enfermería</dd></div></dl>
    </section>
    <div class="rm-cognitive-context" x-show="contextoFuera" x-cloak aria-label="Residente activo durante la consulta"><strong>{{ $residente->nombre_completo }}</strong><span>Solo lectura · Enfermería</span></div>
    @if($errorLectura)
        <section class="rm-card rm-orion-panel" role="alert"><h2>No fue posible consultar la información en este momento.</h2><button type="button" class="rm-btn rm-btn-secondary" wire:click="reintentar">Reintentar</button></section>
    @elseif(! $consultaEnfermeria)
        <section class="rm-card rm-orion-panel space-y-4" aria-labelledby="sin-evaluacion-titulo">
            <div class="flex items-start gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] shrink-0">
                    <i class="ph ph-brain text-xl"></i>
                </span>
                <div class="space-y-1">
                    <h2 id="sin-evaluacion-titulo" class="text-base font-bold text-[var(--rm-text-primary)]">Sin evaluación neurocognitiva formal registrada</h2>
                    <p class="text-xs text-[var(--rm-text-secondary)] leading-relaxed">
                        Medicina y Psicología no han emitido aún una evaluación neurocognitiva formal para este residente. El equipo de Enfermería debe mantener la observación de orientación, conducta y autonomía durante las actividades de la vida diaria.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] space-y-1">
                    <div class="flex items-center gap-2 font-semibold text-xs text-[var(--rm-text-primary)]">
                        <i class="ph ph-compass text-sm text-[var(--rm-action-primary)]"></i>
                        <span>Orientación y Rutina</span>
                    </div>
                    <p class="text-xs text-[var(--rm-text-secondary)]">Reforzar amablemente el día, la hora y el nombre de los cuidadores durante el aseo y la comida.</p>
                </div>
                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] space-y-1">
                    <div class="flex items-center gap-2 font-semibold text-xs text-[var(--rm-text-primary)]">
                        <i class="ph ph-hand-heart text-sm text-[var(--rm-action-primary)]"></i>
                        <span>Preservar Autonomía</span>
                    </div>
                    <p class="text-xs text-[var(--rm-text-secondary)]">Fomentar la realización de tareas sencillas según sus capacidades sin apresurar sus tiempos.</p>
                </div>
                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] space-y-1">
                    <div class="flex items-center gap-2 font-semibold text-xs text-[var(--rm-text-primary)]">
                        <i class="ph ph-warning text-sm text-amber-600"></i>
                        <span>Cambios a Reportar</span>
                    </div>
                    <p class="text-xs text-[var(--rm-text-secondary)]">Notificar a Medicina cualquier desorientación súbita, agitación inusual o somnolencia diurna excesiva.</p>
                </div>
            </div>

            @if(isset($cuidadosPendientesHoy) && $cuidadosPendientesHoy->isNotEmpty())
                <div class="mt-4 pt-3 border-t border-[var(--rm-border)] space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Cuidados prioritarios del turno hoy</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($cuidadosPendientesHoy->take(4) as $cuidado)
                            <div class="p-2.5 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-center justify-between text-xs">
                                <span class="font-medium text-[var(--rm-text-primary)] truncate">{{ $cuidado['nombre'] ?? $cuidado['descripcion'] ?? 'Cuidado programado' }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-semibold border border-amber-200 shrink-0">Pendiente</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-2 flex flex-wrap gap-2">
                <a href="{{ route('admin.enfermeria.agenda') }}" class="rm-btn rm-btn-primary text-xs">
                    <i class="ph ph-calendar-check mr-1.5"></i>Ir a la Agenda de Cuidados
                </a>
                <a href="{{ route('admin.enfermeria.pacientes', ['residente' => $codResidente]) }}" class="rm-btn rm-btn-secondary text-xs">
                    <i class="ph ph-arrow-left mr-1.5"></i>Volver a Mis residentes
                </a>
            </div>
        </section>
    @else
        @php($narrativa = $informe['narrativa'])
        @php($fuentesArea = array_values(array_filter($narrativa['fuentes'] ?? [], fn ($f) => $f['area'] === $criterio)))
        @php($fuentesResultado = array_values(array_filter($narrativa['fuentes'] ?? [], fn ($f) => $f['area'] === 'COG-MEM')))
        @if($errorInstrumental)<section class="rm-orion-note" role="alert"><strong>No fue posible consultar las mediciones instrumentales.</strong><p>La información no pudo verificarse. Esto no significa que no existan registros.</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="reintentar">Reintentar</button></section>@endif
        @if($simulacion)<p class="rm-orion-note" role="note"><strong>Entorno de pruebas · Resultado de demostración · Sin uso asistencial.</strong></p>@endif
        <section class="rm-card rm-orion-result" aria-labelledby="orion-resultado-titulo">
            <div class="rm-orion-section-heading"><h2 class="rm-expert-section-title">¿Qué encontró el sistema?</h2><span class="rm-orion-state">{{ $consultaEnfermeria['estado_principal'] }}</span></div>
            <h3 id="orion-resultado-titulo">{{ $consultaEnfermeria['titulo'] }}</h3><p class="text-sm">Alcance del resultado disponible: {{ mb_strtolower($consultaEnfermeria['area_principal']) }}.</p><p class="rm-expert-prose" @if($consultaEnfermeria['integridad']) role="alert" @endif>{{ $consultaEnfermeria['resumen'] }}</p>
            <dl class="rm-cognitive-summary">
                <div><dt><i class="ph ph-files" aria-hidden="true"></i>Datos utilizados</dt><dd>
                    @if($simulacion)
                        Referencias de la demostración conservada; consulta su detalle en Registros.
                    @else
                        {{ count(array_filter($fuentesResultado, fn ($f) => $f['utilizada'])) }} evidencias de soporte del resultado · {{ count($fuentesResultado) }} fuentes conservadas
                    @endif
                </dd></div>
                <div><dt><i class="ph ph-brain" aria-hidden="true"></i>Cobertura evaluada</dt><dd>
                    @if($simulacion)
                        Perfil de demostración; no acredita evaluación clínica.
                    @else
                        {{ count(array_filter($informe['areas'], fn ($a) => $a['evaluada'])) }} de 5 áreas con resultado disponible
                    @endif
                </dd></div>
                <div><dt><i class="ph ph-info" aria-hidden="true"></i>Límites de interpretación</dt><dd>{{ $texto($informe['limite_global']) }}</dd></div>
            </dl>
            <div class="rm-orion-toolbar"><button type="button" class="rm-btn rm-btn-primary" wire:click="abrirSignificado" wire:loading.attr="disabled" wire:target="abrirSignificado">¿Qué significa este resultado?</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsultaResultado('interpretacion')">¿Por qué se obtuvo este resultado?</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirEvolucion" wire:loading.attr="disabled" wire:target="abrirEvolucion"><i class="ph ph-chart-line-up" aria-hidden="true"></i>Ver evolución de registros</button></div>
        </section>
        <section class="rm-card rm-orion-panel" aria-labelledby="cognitive-profile-heading"><h2 id="cognitive-profile-heading" class="rm-expert-section-title">Estado de las áreas cognitivas</h2><p>La gráfica muestra cuántas áreas tienen un resultado publicable. Las observaciones disponibles se consultan por separado y no cuentan como mediciones instrumentales.</p>
            @php($areasGrafico = collect($informe['areas'])->map(fn ($area) => ['nombre' => $area['nombre'], 'estado' => $area['estado'], 'grupo' => $area['evaluada'] ? 'disponible' : 'sin_resultado'])->values()->all())
            @include('apoyodecision::livewire.medico.resultados-experto.grafico-areas', [
                'graficoId' => 'enfermeria-dominios',
                'graficoTitulo' => 'Cobertura del resultado publicado',
                'graficoSubtitulo' => 'Cinco áreas cognitivas de esta evaluación; cada segmento representa un área.',
            ])
            <p>Selecciona un área para conocer sus resultados y los registros disponibles.</p>
            <div class="rm-orion-domain-grid">
                @foreach($informe['areas'] as $codigo => $area)
                    <button type="button" class="rm-orion-domain" wire:click="seleccionarCriterio(@js($codigo))" wire:loading.attr="disabled" wire:target="seleccionarCriterio(@js($codigo))" aria-pressed="{{ $criterio === $codigo ? 'true' : 'false' }}" aria-controls="cognitive-selected-area" wire:key="cognitive-area-{{ $codigo }}"><i class="ph {{ $area['icono'] }}" aria-hidden="true"></i><strong>{{ $area['nombre'] }}</strong><span>{{ $area['estado'] }}</span><span class="rm-orion-domain-link">{{ $criterio === $codigo ? 'Seleccionada' : 'Explorar área' }} <i class="ph {{ $criterio === $codigo ? 'ph-check-circle' : 'ph-arrow-right' }}" aria-hidden="true"></i></span></button>
                @endforeach
            </div>
        </section>
        <div class="rm-orion-area-layout">
            <section class="rm-card rm-orion-panel" id="cognitive-selected-area" aria-live="polite"><p wire:loading wire:target="seleccionarCriterio" role="status">Consultando el área…</p><div class="rm-orion-area-heading"><span class="rm-orion-area-icon"><i class="ph {{ $informe['actual']['icono'] }}" aria-hidden="true"></i></span><h2>Área seleccionada: {{ $informe['actual']['nombre'] }}</h2><span class="rm-orion-state">{{ $informe['actual']['estado'] }}</span></div>
                <h3 class="rm-expert-section-title">Interpretación para Enfermería</h3><p class="rm-expert-prose">{{ $texto($informe['actual']['significado']) }}</p><h3 class="rm-expert-section-title mt-4">¿Qué se observó?</h3><p>{{ $texto($informe['actual']['conclusion']) }}</p>
                <div class="rm-orion-toolbar"><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirDetalleArea">Ver detalle del área</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirEvolucion">Ver evolución</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('registro')">Ver evidencias</button></div>
            </section>
            <aside class="rm-card rm-orion-panel" aria-label="Consulta del residente"><h2 class="rm-expert-section-title">Consulta del residente</h2><p>Explora el contexto y continúa hacia los cuidados de tu turno.</p><div class="rm-cognitive-accesses"><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('interpretacion')"><i class="ph ph-book-open" aria-hidden="true"></i>Interpretación</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('registro')"><i class="ph ph-files" aria-hidden="true"></i>Registros</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('cuidados')"><i class="ph ph-heart" aria-hidden="true"></i>Cuidados</button></div><p class="rm-orion-note">{{ $texto($narrativa['autonomia']['resumen']) }}</p></aside>
        </div>
        @include('apoyodecision::livewire.medico.resultados-experto.enfermeria-ventanas')
    @endif
</div>
