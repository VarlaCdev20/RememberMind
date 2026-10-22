@php($petersen = $perfilInstrumental['petersen_institucional'])
@php($nombresDominios = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas', 'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Función visuoespacial'])
@php($iconosDominios = ['COG-MEM' => 'ph-brain', 'COG-ATE' => 'ph-target', 'COG-EJE' => 'ph-tree-structure', 'COG-LEN' => 'ph-chat-circle-text', 'COG-VIS' => 'ph-eye'])
@php($nombresRequisitos = ['C01' => 'Cambio corroborado', 'C02' => 'Alteración objetiva', 'C03' => 'Independencia instrumental relativa', 'C04' => 'Evaluación diferencial', 'C05' => 'Calidad e interpretabilidad'])
@php($patrones = ['PET-01' => 'amnésico de dominio único', 'PET-02' => 'amnésico multidominio', 'PET-03' => 'no amnésico de dominio único', 'PET-04' => 'no amnésico multidominio'])
@php($medicionesPorDominio = ['COG-MEM' => 'aprendizaje y recuerdo', 'COG-ATE' => 'atención', 'COG-EJE' => 'planificación, flexibilidad o inhibición', 'COG-LEN' => 'denominación, fluidez o comprensión', 'COG-VIS' => 'construcción y procesamiento visuoespacial'])
@php($explicarMotivo = fn ($motivo) => \App\Modulos\ApoyoDecision\Presentacion\Soporte\MotivoPetersenVisible::explicar($motivo))
@php($estadosGrafico = ['A' => ['Alteración sustentada', 'alterado'], 'P' => ['Rendimiento esperado sustentado', 'esperado'], 'U' => ['Información insuficiente', 'insuficiente'], 'I' => ['No interpretable', 'no_interpretable'], 'X' => ['Información contradictoria', 'contradictorio']])
@php($areasGrafico = collect($nombresDominios)->map(fn ($nombre, $codigo) => ['nombre' => $nombre, 'estado' => $estadosGrafico[$petersen['dominios'][$codigo]['estado'] ?? 'U'][0], 'grupo' => $estadosGrafico[$petersen['dominios'][$codigo]['estado'] ?? 'U'][1]])->values()->all())
@php($senalesSeguimiento = collect($hallazgosObservacionales['resumen'] ?? [])->filter(fn ($item) => ($item['estado'] ?? null) === 'CUMPLE'))
@php($resumenPetersen = \App\Modulos\ApoyoDecision\Presentacion\Soporte\ResumenPetersenVisible::presentar($petersen, $senalesSeguimiento->isNotEmpty()))
@php($requisitosPendientes = collect($nombresRequisitos)->filter(fn ($nombre, $codigo) => ($petersen['requisitos'][$codigo]['estado'] ?? 'U') === 'U'))
@php($hayDominiosValorados = collect($petersen['dominios'])->contains(fn ($dominio) => ($dominio['estado'] ?? 'U') !== 'U'))

@if($psicologia)
    @include('apoyodecision::livewire.medico.resultados-experto.petersen-psicologia')
    <details id="orion-petersen-detalle" class="rm-psychology-technical">
        <summary>Consultar reglas, soportes y criterios en detalle</summary>
@endif
<x-ui.section-card title="Detalle del resultado cognitivo" subtitle="Valoraciones, reglas y soportes utilizados" icon="ph-brain" class="rm-orion-petersen-card">
    @unless($psicologia)
    {{-- Banner de síntesis clínica orientativa --}}
    <div class="rm-orion-petersen-hero {{ $petersen['patron'] ? 'has-pattern' : 'is-limited' }}">
        <div class="rm-orion-petersen-hero-grid">
            <div class="rm-orion-petersen-hero-main">
                <span class="rm-orion-petersen-icon" aria-hidden="true"><i class="ph ph-brain"></i></span>
                <div class="min-w-0">
                    <p class="rm-orion-petersen-kicker">Resultado de la consulta actual</p>
                    <span class="rm-orion-petersen-status">{{ $resumenPetersen['estado'] }}</span>
                    <h2 class="rm-orion-petersen-result" id="orion-resultado-actual">
                        {{ $petersen['patron'] ? 'Perfil compatible con posible DCL '.$patrones[$petersen['patron']] : $resumenPetersen['titulo'] }}
                    </h2>
                    <p class="rm-orion-petersen-explanation">
                        {{ $resumenPetersen['explicacion'] }}
                    </p>
                    <div class="rm-orion-petersen-hero-actions">
                        <a href="#orion-petersen-criterios" class="rm-btn rm-btn-primary"><i class="ph ph-list-checks" aria-hidden="true"></i>Por qué este resultado</a>
                        @if($datos && $informe)<button type="button" wire:click="abrirConsulta('registro')" class="rm-btn rm-btn-secondary"><i class="ph ph-files" aria-hidden="true"></i>Ver evidencias</button>@endif
                    </div>
                </div>
            </div>
            <aside class="rm-orion-petersen-guidance" aria-label="Cómo interpretar el resultado">
                <h3><i class="ph ph-warning-circle" aria-hidden="true"></i>Resultado orientativo, no diagnóstico</h3>
                @if(count($petersen['impedimentos']))
                    @if($resumenPetersen['faltan_mediciones'] && $resumenFuentes && $resumenFuentes['instrumentales'] === 0)
                        <p class="rm-orion-petersen-guidance-reason">Hay registros de seguimiento, pero no se ha registrado una aplicación cognitiva instrumental para interpretar las cinco áreas.</p>
                    @else
                        <p class="rm-orion-petersen-guidance-reason">{{ $explicarMotivo($petersen['impedimentos'][0]) }}</p>
                    @endif
                @else
                    <p>Las cinco áreas cognitivas y los requisitos de valoración explican qué pudo comprobarse.</p>
                @endif
                <p>Es una orientación basada en la información disponible. Debe interpretarse con la historia y el contexto funcional del residente. No constituye un diagnóstico médico confirmado.</p>
            </aside>
        </div>
        <p class="rm-orion-petersen-meta">Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($petersen['fecha_corte'] ?? null, 'momento de la consulta') }}. Este cálculo actual todavía no está guardado como evaluación histórica completa y no admite revisión profesional.</p>

        @if(! $petersen['patron'] && ($petersen['requisitos']['C02']['estado'] ?? null) === 'N')
            <div class="rm-expert-no-pattern mt-3" role="status">
                <i class="ph ph-smiley" aria-hidden="true"></i>
                <span><strong>Las cinco áreas muestran rendimiento esperado documentado.</strong> En esta evaluación no se identificó un patrón clasificable de posible DCL. Consulte los controles de seguimiento para revisar otros cambios registrados.</span>
            </div>
        @endif

        @if(count($petersen['impedimentos']) > 1)
            <details class="rm-orion-petersen-detail-panel mt-3">
                <summary>Ver todos los motivos que impiden clasificar</summary>
                <ul class="list-disc pl-5 mt-2 space-y-1">
                    @foreach($petersen['impedimentos'] as $motivo)<li>{{ $explicarMotivo($motivo) }}</li>@endforeach
                </ul>
            </details>
        @endif
    </div>

    @endunless
    @if($petersen['gobierno']['disponible'] ?? false)
        <details class="mt-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] p-3.5">
            <summary class="cursor-pointer min-h-11 font-semibold text-xs text-[var(--rm-text-primary)] flex items-center gap-2">
                <i class="ph ph-scales text-sm text-[var(--rm-action-primary)]"></i>
                <span>Reglas de Petersen aplicadas</span>
            </summary>
            <p class="text-xs text-[var(--rm-text-secondary)] mt-2">Estas reglas corresponden al cálculo actual. La consulta aún no está guardada como evaluación histórica completa.</p>
            @foreach($petersen['gobierno']['reglas'] as $regla)
                <div class="mt-2.5 pt-2.5 border-t border-[var(--rm-border)]/60 text-xs">
                    <p class="font-medium text-[var(--rm-text-primary)]">{{ $patrones[$regla['codigo']] }}: {{ !$regla['habilitada'] ? ($regla['estado'] !== 'ACTIVO' ? 'Archivada' : 'Rechazada') : ($regla['cumple'] ? 'Condiciones cumplidas' : 'Condiciones sin cumplir') }}. Revisión: {{ $regla['estado_revision'] ?? 'Sin revisión registrada' }}.</p>
                    <ul class="list-disc pl-5 mt-1 space-y-0.5 text-[11px] text-[var(--rm-text-secondary)]">
                        @foreach($regla['condiciones'] as $condicion)
                            <li class="break-words">{{ $nombresRequisitos[$condicion['condicion']] ?? 'Requisito de valoración' }}: {{ $condicion['estado'] === 'CUMPLE' ? 'acreditado' : 'no acreditado' }}.</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </details>
    @endif

    @unless($psicologia)
    <div id="orion-petersen-criterios" class="rm-expert-profile-grid" tabindex="-1">
        <section class="rm-expert-profile-grid__domains" aria-label="Perfil cognitivo por dominios">
            @include('apoyodecision::livewire.medico.resultados-experto.grafico-areas', [
                'graficoId' => 'petersen-dominios',
                'graficoTitulo' => 'Perfil cognitivo por dominios',
                'graficoSubtitulo' => 'Estado documentado de las cinco áreas; no es una puntuación ni una escala de gravedad.',
                'modoGrafico' => 'radial',
            ])
        </section>
        <section class="rm-expert-profile-grid__criteria" aria-labelledby="criterios-resumen-titulo">
            <h3 id="criterios-resumen-titulo">Criterios revisados</h3>
            <p>Qué información respalda o limita el perfil orientativo.</p>
            <ul>
                @foreach($nombresRequisitos as $codigo => $nombre)
                    @php($estadoCodigo = $petersen['requisitos'][$codigo]['estado'] ?? 'U')
                    <li data-state="{{ $estadoCodigo }}"><span>{{ $nombre }}</span><strong>{{ ['S' => 'Sustentado', 'N' => 'No acreditado', 'X' => 'Contradictorio', 'U' => 'Pendiente'][$estadoCodigo] ?? 'No interpretable' }}</strong></li>
                @endforeach
            </ul>
            <a href="#orion-petersen-detalle" class="rm-btn rm-btn-secondary">Ver el detalle de los criterios</a>
        </section>
    </div>

    {{-- Estados reales: no se traducen a puntuaciones ni se colorean como diagnóstico. --}}
    <div id="orion-petersen-detalle">
        <h2 class="sr-only" tabindex="-1">Estado de los cinco criterios Petersen</h2>
        @if($requisitosPendientes->isNotEmpty() && $requisitosPendientes->count() < count($nombresRequisitos))
            <p class="rm-orion-note mt-2">{{ $requisitosPendientes->count() }} {{ $requisitosPendientes->count() === 1 ? 'criterio sigue pendiente' : 'criterios siguen pendientes' }}. Puede consultar cada motivo en el detalle.</p>
        @endif
    </div>
    @endunless

    <details class="rm-orion-petersen-detail-panel">
    <summary>Ver las cinco áreas y {{ $requisitosPendientes->isNotEmpty() ? 'los criterios pendientes' : 'los criterios evaluados' }}</summary>
    <div class="rm-orion-petersen-details">
    {{-- Cinco dominios --}}
    <div class="rm-orion-petersen-column space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-sm text-[var(--rm-text-primary)]">Cinco dominios</h2>
            <span class="text-xs text-[var(--rm-text-secondary)]">Abra cada área para ver qué información falta</span>
        </div>
        <div class="rm-orion-petersen-list">
            @foreach($petersen['dominios'] as $codigo => $d)
                @php($estadoDominio = ['A' => 'Alteración objetiva con soporte', 'P' => 'Rendimiento esperado con soporte', 'U' => 'Pendiente de medición', 'I' => 'No interpretable', 'X' => 'Contradictorio'][$d['estado']] ?? 'No evaluado')
                <details class="{{ $d['estado'] === 'U' ? 'border-b border-[var(--rm-border)] px-1 py-2' : 'rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3.5 shadow-sm' }} min-w-0">
                    <summary class="cursor-pointer min-h-11 font-semibold text-xs text-[var(--rm-text-primary)] flex items-center justify-between gap-2">
                        <span>{{ $nombresDominios[$codigo] }} · {{ $estadoDominio }}</span>
                    </summary>
                    <div class="mt-2.5 pt-2.5 border-t border-[var(--rm-border)]/60 space-y-2 text-xs">
                        @forelse($d['valoraciones'] as $valoracion)
                            <div class="p-2 rounded-lg bg-[var(--rm-surface-soft)] space-y-1">
                                <p class="break-words font-medium text-[var(--rm-text-primary)]">{{ $valoracion['justificacion'] }} · Profesional {{ str_starts_with($valoracion['origen']['autor'] ?? '', 'COD_') ? 'identificado en el registro original' : ($valoracion['origen']['autor'] ?? 'no identificado') }} · {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($valoracion['origen']['fecha'] ?? null) }}.</p>
                                @if(!empty($valoracion['soportes']))
                                    <ul class="list-disc pl-4 text-[11px] text-[var(--rm-text-secondary)]">
                                        @foreach($valoracion['soportes'] as $soporte)
                                            <li class="break-words">Documento de respaldo · {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($soporte['fecha'] ?? null) }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @empty
                            <p class="text-[11px] text-[var(--rm-text-secondary)] italic">Sin valoraciones originales asociadas a este dominio.</p>
                        @endforelse
                        @if(in_array($d['estado'], ['U', 'I'], true))
                            <p class="text-[11px] text-[var(--rm-text-primary)] bg-[var(--rm-surface-soft)] p-2 rounded">Para valorar {{ strtolower($nombresDominios[$codigo]) }} se necesita una medición original de {{ $medicionesPorDominio[$codigo] }}: instrumento y edición identificados, idioma, resultados de las tareas, condiciones de aplicación e interpretación profesional con referencia aplicable a la edad y escolaridad. Revise qué parte de ese conjunto falta en las fuentes originales.</p>
                        @endif
                        @foreach($d['motivos'] as $motivo)
                            <p class="text-[11px] text-[var(--rm-text-secondary)] bg-[var(--rm-surface-soft)] p-1.5 rounded">{{ $explicarMotivo($motivo) }}</p>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>
    </div>

    {{-- Criterios Petersen --}}
    <div class="rm-orion-petersen-column space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-sm text-[var(--rm-text-primary)]">Criterios Petersen</h2>
            <span class="text-xs text-[var(--rm-text-secondary)]">Estado de los cinco requisitos</span>
        </div>
        <div class="rm-orion-petersen-list">
            @foreach($nombresRequisitos as $codigo => $nombre)
                @php($requisito = $petersen['requisitos'][$codigo])
                @php($estadoReq = ['S' => 'Acreditado', 'N' => 'No acreditado', 'U' => ($codigo === 'C02' ? 'Pendiente de valorar las áreas' : 'Pendiente de valoración'), 'X' => 'Contradictorio'][$requisito['estado']] ?? 'No evaluado')
                <details class="{{ $requisito['estado'] === 'U' ? 'border-b border-[var(--rm-border)] px-1 py-2' : 'rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3.5 shadow-sm' }} min-w-0">
                    <summary class="cursor-pointer min-h-11 font-semibold text-xs text-[var(--rm-text-primary)] flex items-center justify-between gap-2">
                        <span>{{ $nombre }}: {{ $estadoReq }}</span>
                    </summary>
                    <div class="mt-2.5 pt-2.5 border-t border-[var(--rm-border)]/60 space-y-2 text-xs">
                        @if($codigo === 'C02')
                            <p class="text-[11px] text-[var(--rm-text-secondary)] italic">Este requisito se calcula a partir de los cinco dominios; no tiene formulario propio.</p>
                        @endif
                        @forelse($requisito['valoraciones'] as $valoracion)
                            <div class="p-2 rounded-lg bg-[var(--rm-surface-soft)] space-y-1">
                                <p class="break-words font-medium text-[var(--rm-text-primary)]">{{ $valoracion['justificacion'] }} · Profesional {{ str_starts_with($valoracion['origen']['autor'] ?? '', 'COD_') ? 'identificado en el registro original' : ($valoracion['origen']['autor'] ?? 'no identificado') }} · {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($valoracion['origen']['fecha'] ?? null) }}.</p>
                                @if(!empty($valoracion['soportes']))
                                    <ul class="list-disc pl-4 text-[11px] text-[var(--rm-text-secondary)]">
                                        @foreach($valoracion['soportes'] as $soporte)
                                            <li class="break-words">Documento de respaldo · {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($soporte['fecha'] ?? null) }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @empty
                            @if($codigo !== 'C02')
                                <p class="text-[11px] text-[var(--rm-text-secondary)] italic">Sin juicios clínicos registrados para este criterio.</p>
                            @endif
                        @endforelse
                        @foreach($requisito['motivos'] as $motivo)
                            <p class="text-[11px] text-[var(--rm-text-secondary)] bg-[var(--rm-surface-soft)] p-1.5 rounded">{{ $explicarMotivo($motivo) }}</p>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>
    </div>

    </div>
    </details>

    {{-- Explicabilidad y justificaciones --}}
    <details class="mt-6 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] p-4">
        <summary class="cursor-pointer min-h-11 font-semibold text-xs text-[var(--rm-text-primary)] flex items-center gap-2">
            <i class="ph ph-magnifying-glass-plus text-sm text-[var(--rm-action-primary)]"></i>
            <span>¿Por qué se obtuvo este resultado? Juicios, soportes y exclusiones</span>
        </summary>
        <div class="space-y-4 mt-3">
            @foreach(array_merge($petersen['dominios'], $petersen['requisitos']) as $codigo => $elemento)
                <div class="border-t border-[var(--rm-border)]/70 pt-3">
                    <h3 class="font-semibold text-xs text-[var(--rm-text-primary)]">{{ $nombresDominios[$codigo] ?? $nombresRequisitos[$codigo] }}</h3>
                    @foreach($elemento['valoraciones'] as $valoracion)
                        <p class="text-xs text-[var(--rm-text-primary)] mt-1.5">{{ $valoracion['justificacion'] }}</p>
                        <p class="text-[11px] text-[var(--rm-text-secondary)] break-words">Profesional responsable: {{ str_starts_with($valoracion['origen']['autor'] ?? '', 'COD_') ? 'identificado en el registro original' : ($valoracion['origen']['autor'] ?? 'no identificado') }} · {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($valoracion['origen']['fecha'] ?? null) }}</p>
                        <ul class="list-disc pl-5 mt-1 text-[11px] text-[var(--rm-text-secondary)]">
                            @foreach($valoracion['comprobaciones'] as $campo => $estado)
                                <li class="break-words">{{ ucfirst(strtolower(str_replace('_', ' ', $campo))) }}: {{ $estado === true ? 'Sí, documentado' : ($estado === false ? 'No, documentado' : 'Sin información documentada') }}</li>
                            @endforeach
                        </ul>
                        <ul class="list-disc pl-5 mt-1 text-[11px] text-[var(--rm-text-secondary)]">
                            @foreach($valoracion['soportes'] as $soporte)
                                <li class="break-words">Documento de respaldo · {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($soporte['fecha'] ?? null) }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                    @foreach($elemento['motivos'] as $motivo)
                        <p class="text-[11px] text-[var(--rm-text-secondary)] break-words mt-1">{{ $explicarMotivo($motivo) }}</p>
                    @endforeach
                    @if(isset($elemento['derivado_de']))
                        <p class="text-[11px] text-[var(--rm-text-secondary)] italic mt-1.5">Calculado automáticamente a partir de los cinco dominios cognitivos; no se introduce como valoración manual.</p>
                    @endif
                </div>
            @endforeach
            @foreach($petersen['valoraciones_excluidas'] as $excluida)
                <p class="text-[11px] text-amber-800 bg-amber-50 p-2 rounded break-words">Se excluyó una valoración de {{ $nombresDominios[$excluida['elemento']] ?? $nombresRequisitos[$excluida['elemento']] ?? 'la evaluación' }}: {{ implode(' ', array_map($explicarMotivo, $excluida['motivos'])) }}</p>
            @endforeach
            @foreach($petersen['adquisicion']['exclusiones'] as $excluida)
                <p class="text-[11px] text-amber-800 bg-amber-50 p-2 rounded break-words">Una valoración de {{ $nombresDominios[$excluida['elemento']] ?? $nombresRequisitos[$excluida['elemento']] ?? 'la evaluación' }} no se utilizó: {{ $explicarMotivo($excluida['motivo']) }}</p>
            @endforeach
        </div>
    </details>
</x-ui.section-card>
@if($psicologia)
    </details>
@endif
