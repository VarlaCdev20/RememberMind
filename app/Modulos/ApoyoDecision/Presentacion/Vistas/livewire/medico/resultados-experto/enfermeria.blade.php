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
    @include('apoyodecision::livewire.medico.resultados-experto.exploracion-clinica')
    @unless($errorLectura)
        @include('apoyodecision::livewire.medico.resultados-experto.enfermeria-resumen')
    @endunless
    @if($errorLectura)
        <section class="rm-card rm-orion-panel" role="alert"><h2>No fue posible consultar la información en este momento.</h2><button type="button" class="rm-btn rm-btn-secondary" wire:click="reintentar">Reintentar</button></section>
    @elseif(! $consultaEnfermeria)
        <x-ui.empty-state title="Sin evaluación experta disponible" description="No hay un resultado verificable en esta consulta. Consulte la ficha autorizada para revisar los registros disponibles." icon="ph-brain" />
    @else
        <details class="rm-expert-supplement"><summary>Áreas cognitivas, resultado y fundamento disponibles</summary>
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
        </details>
        @include('apoyodecision::livewire.medico.resultados-experto.enfermeria-ventanas')
    @endif
</div>
