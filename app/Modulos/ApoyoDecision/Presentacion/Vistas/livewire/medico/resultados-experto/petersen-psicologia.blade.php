@php
    $hayPatron = (bool) $petersen['patron'];
    $vistaMultidominio = in_array($petersen['patron'] ?? null, ['PET-02', 'PET-04'], true);
    $areasEsperadasDocumentadas = ! $hayPatron && count($petersen['dominios']) === 5
        && collect($petersen['dominios'])->every(fn ($dominio) => ($dominio['estado'] ?? null) === 'P');
    $dominiosPendientesVista = collect($petersen['dominios'])->filter(fn ($dominio) => in_array($dominio['estado'] ?? 'U', ['U', 'I', 'X'], true));
    $mesesActividad = collect($actividadCognitiva)->groupBy(fn ($punto) => substr($punto['fecha'], 0, 7))
        ->map(fn ($puntos, $mes) => [
            'mes' => \Carbon\CarbonImmutable::parse($mes.'-01')->locale('es')->translatedFormat('M Y'),
            'cantidad' => $puntos->sum('valor'),
        ])->values();
    $maximoActividad = max(1, (int) $mesesActividad->max('cantidad'));
@endphp

<div class="rm-psychology-overview {{ $vistaMultidominio ? 'is-multidomain' : 'is-compact-profile' }}" x-data="{ ayudaAbierta: false, ayudaTema: 'resultado' }">
    @if($hayPatron)
        <div class="rm-psychology-profile-head {{ $vistaMultidominio ? '' : 'rm-psychology-profile-head--single' }}">
            <section class="rm-psychology-resident rm-psychology-resident--pattern" aria-label="Resultado del residente">
                <div class="rm-psychology-resident__identity">
                    <span class="rm-expert-avatar" aria-hidden="true">@if($residente->foto)<img src="{{ \Illuminate\Support\Facades\Storage::url($residente->foto) }}" alt="" loading="lazy" decoding="async">@else<i class="ph ph-user" aria-hidden="true"></i>@endif</span>
                    <div><h2>{{ $residente->nombre_completo }}</h2><p>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }} · Consulta actual</p><p>Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($petersen['fecha_corte'] ?? null, 'momento de la consulta') }}</p></div>
                </div>
                @if($vistaMultidominio)
                <div class="rm-psychology-pattern-result">
                    <i class="ph ph-brain" aria-hidden="true"></i>
                    <div><span>Resultado orientativo</span><h2 id="orion-resultado-actual">Perfil compatible con posible DCL {{ $patrones[$petersen['patron']] }}</h2><p>{{ $resumenPetersen['explicacion'] }}</p></div>
                </div>
                @endif
            </section>
            @if($vistaMultidominio)
            <aside class="rm-psychology-warning" aria-label="Límite de interpretación">
                <i class="ph ph-warning" aria-hidden="true"></i><div><h2>Resultado orientativo, no diagnóstico</h2><p>Este resultado debe revisarse junto con la historia del residente, su autonomía y otras posibles causas.</p><strong>No constituye un diagnóstico médico confirmado.</strong><button type="button" wire:click="irASeccion('orion-petersen-detalle')" class="rm-psychology-link">Ver fundamento y límites <i class="ph ph-arrow-right" aria-hidden="true"></i></button></div>
            </aside>
            @else
            <div class="rm-psychology-resident__date"><strong>Consulta actual</strong><span>Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($petersen['fecha_corte'] ?? null, 'momento de la consulta') }}</span></div>
            @endif
        </div>
        @unless($vistaMultidominio)
        <section class="rm-psychology-pending rm-psychology-pending--pattern" aria-labelledby="orion-resultado-actual">
            <div class="rm-psychology-pending__main"><span class="rm-psychology-pending__status"><i class="ph ph-brain" aria-hidden="true"></i>Resultado orientativo</span><h2 id="orion-resultado-actual">Perfil compatible con posible DCL {{ $patrones[$petersen['patron']] }}</h2><p>{{ $resumenPetersen['explicacion'] }}</p><p class="rm-psychology-inline-warning">Requiere interpretación profesional; no es un diagnóstico confirmado.</p></div>
            <div class="rm-psychology-pending__needs"><button type="button" class="rm-btn rm-btn-secondary" x-on:click="ayudaTema = 'resultado'; ayudaAbierta = true"><i class="ph ph-info" aria-hidden="true"></i>¿Por qué este resultado?</button><button type="button" class="rm-btn rm-btn-primary" wire:click="irASeccion('fuentes-disponibles')"><i class="ph ph-files" aria-hidden="true"></i>Ver evidencias</button></div>
        </section>
        @endunless
    @else
        <section class="rm-psychology-resident" aria-label="Contexto del residente">
            <div class="rm-psychology-resident__identity">
                <span class="rm-expert-avatar" aria-hidden="true">@if($residente->foto)<img src="{{ \Illuminate\Support\Facades\Storage::url($residente->foto) }}" alt="" loading="lazy" decoding="async">@else<i class="ph ph-user" aria-hidden="true"></i>@endif</span>
                <div><h2>{{ $residente->nombre_completo }}</h2><p>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }}</p></div>
            </div>
            <div class="rm-psychology-resident__date"><strong>Consulta actual</strong><span>Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($petersen['fecha_corte'] ?? null, 'momento de la consulta') }}</span></div>
            <span class="rm-psychology-resident__state"><i class="ph ph-warning-circle" aria-hidden="true"></i>Sin evaluación histórica completa</span>
        </section>
        <section class="rm-psychology-pending {{ $areasEsperadasDocumentadas ? 'is-expected' : '' }}" aria-labelledby="orion-resultado-actual">
            <div class="rm-psychology-pending__main"><span class="rm-psychology-pending__status"><i class="ph {{ $areasEsperadasDocumentadas ? 'ph-smiley' : 'ph-warning-circle' }}" aria-hidden="true"></i>{{ $resumenPetersen['estado'] }}</span><h2 id="orion-resultado-actual">{{ $resumenPetersen['titulo'] }}</h2><p>{{ $resumenPetersen['explicacion'] }}</p><button type="button" class="rm-psychology-help-link" x-on:click="ayudaTema = 'resultado'; ayudaAbierta = true">¿Qué significa este resultado?</button></div>
            <div class="rm-psychology-pending__needs">
            @if($areasEsperadasDocumentadas)
                <h3>Continuar el seguimiento</h3>
                <p>El rendimiento esperado en las cinco áreas no sustituye los controles cotidianos. Revise los registros disponibles si aparecen cambios nuevos.</p>
                <button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-btn rm-btn-primary">Ver registros <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
            @else
            <h3>Qué debe revisarse</h3><ul>
                @if($dominiosPendientesVista->isNotEmpty())<li><i class="ph ph-clipboard-text" aria-hidden="true"></i>Mediciones interpretables de {{ $dominiosPendientesVista->count() }} {{ $dominiosPendientesVista->count() === 1 ? 'área cognitiva' : 'áreas cognitivas' }}</li>@endif
                @if(($petersen['requisitos']['C01']['estado'] ?? 'U') !== 'S')<li><i class="ph ph-users-three" aria-hidden="true"></i>Cambio cognitivo y corroboración independiente</li>@endif
                @if(($petersen['requisitos']['C03']['estado'] ?? 'U') !== 'S')<li><i class="ph ph-person-simple-walk" aria-hidden="true"></i>Autonomía en actividades instrumentales</li>@endif
                @if(($petersen['requisitos']['C04']['estado'] ?? 'U') !== 'S')<li><i class="ph ph-file-search" aria-hidden="true"></i>Revisión de otras posibles causas</li>@endif
                @if(($petersen['requisitos']['C05']['estado'] ?? 'U') !== 'S')<li><i class="ph ph-list-checks" aria-hidden="true"></i>Calidad del conjunto de evidencias</li>@endif
                @if($dominiosPendientesVista->isEmpty() && collect($petersen['requisitos'])->every(fn ($requisito) => ($requisito['estado'] ?? 'U') === 'S'))<li><i class="ph ph-list-checks" aria-hidden="true"></i>Consulte el motivo documentado del resultado</li>@endif
            </ul><button type="button" class="rm-btn rm-btn-primary" x-on:click="ayudaTema = 'faltantes'; ayudaAbierta = true">Entender los requisitos <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
            @endif
            </div>
        </section>
    @endif

    <div id="orion-petersen-criterios" class="rm-psychology-dashboard {{ $vistaMultidominio ? 'rm-psychology-dashboard--multidomain' : 'rm-psychology-dashboard--compact' }} {{ $hayPatron ? 'has-pattern' : '' }}" tabindex="-1">
        <div class="rm-psychology-dashboard__main">
            <section class="rm-psychology-panel" aria-labelledby="psicologia-dominios-titulo">
                <header><div><h2 id="psicologia-dominios-titulo">{{ $vistaMultidominio ? 'Perfil cognitivo por dominios' : 'Rendimiento por dominios cognitivos' }}</h2><p>Resultado documentado en cada área; no son porcentajes de deterioro.</p></div><button type="button" class="rm-psychology-help" x-on:click="ayudaTema = 'dominios'; ayudaAbierta = true" aria-label="Explicar los cinco dominios cognitivos"><i class="ph ph-question" aria-hidden="true"></i></button></header>
                @if($vistaMultidominio)
                    @include('apoyodecision::livewire.medico.resultados-experto.grafico-areas', [
                        'graficoId' => 'psicologia-dominios',
                        'graficoTitulo' => 'Mapa de las cinco áreas',
                        'graficoSubtitulo' => 'Cada punto tiene la misma distancia al centro; el color y la tabla indican el estado.',
                        'modoGrafico' => 'radial',
                    ])
                @else
                    <ul class="rm-psychology-status-list">
                        @foreach($nombresDominios as $codigo => $nombre)
                            @php($estado = $petersen['dominios'][$codigo]['estado'] ?? 'U')
                            <li data-state="{{ $estado }}"><span><i class="ph {{ $iconosDominios[$codigo] }}" aria-hidden="true"></i>{{ $nombre }}</span><strong data-state="{{ $estado }}">{{ ['A' => 'Alteración sustentada', 'P' => 'Rendimiento esperado sustentado', 'U' => 'Sin medición interpretable', 'I' => 'No interpretable', 'X' => 'Información contradictoria'][$estado] ?? 'Pendiente' }}</strong></li>
                        @endforeach
                    </ul>
                @endif
                <button type="button" wire:click="irASeccion('orion-petersen-detalle')" class="rm-psychology-link">Ver el detalle de las áreas <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
            </section>

            <section class="rm-psychology-panel" aria-labelledby="psicologia-evolucion-titulo">
                <header><div><h2 id="psicologia-evolucion-titulo">Controles registrados</h2><p>Cantidad por mes durante los últimos 90 días; no mide mejoría ni deterioro.</p></div><button type="button" class="rm-psychology-help" x-on:click="ayudaTema = 'controles'; ayudaAbierta = true" aria-label="Explicar la gráfica de controles"><i class="ph ph-question" aria-hidden="true"></i></button></header>
                @if($mesesActividad->isNotEmpty())
                    <div class="rm-psychology-month-chart" role="img" aria-label="Número de controles cognitivos por mes durante los últimos 90 días">
                        @foreach($mesesActividad as $mes)<div><strong>{{ $mes['cantidad'] }}</strong><span class="rm-psychology-month-chart__bar" style="--rm-bar-height: {{ round($mes['cantidad'] * 100 / $maximoActividad) }}%"></span><span>{{ $mes['mes'] }}</span></div>@endforeach
                    </div>
                    <table class="rm-psychology-chart-table"><caption>Controles cognitivos por mes en los últimos 90 días</caption><thead><tr><th scope="col">Mes</th><th scope="col">Registros</th></tr></thead><tbody>@foreach($mesesActividad as $mes)<tr><td>{{ $mes['mes'] }}</td><td>{{ $mes['cantidad'] }}</td></tr>@endforeach</tbody></table>
                @else
                    <p class="rm-psychology-empty">{{ $errorEvolucion ? 'No se pudo consultar la serie de controles en este momento.' : 'No hay controles cognitivos vigentes en los últimos 90 días para mostrar una gráfica.' }}</p>
                @endif
            </section>
            @if($senalesSeguimiento->isNotEmpty())
                <section class="rm-psychology-panel" aria-labelledby="psicologia-observaciones-titulo">
                    <header><div><h2 id="psicologia-observaciones-titulo">Observaciones del seguimiento</h2><p>Hechos registrados que conviene revisar en su fuente original.</p></div></header>
                    <ul class="rm-psychology-observation-list">@foreach($senalesSeguimiento as $codigoRegla => $senal)<li><strong>{{ $senal['nombre'] ?? \MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones::REGLAS[$codigoRegla]['nombre'] }}</strong>@if($senal['hallazgo'])<span>{{ $senal['hallazgo'] }}</span>@endif</li>@endforeach</ul>
                    <p class="rm-psychology-panel-note">Estas observaciones no son una medición instrumental ni establecen un diagnóstico.</p>
                    <button type="button" wire:click="irASeccion('hallazgos-observacionales')" class="rm-psychology-link">Ver controles originales <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                </section>
            @endif
        </div>
        <div class="rm-psychology-dashboard__aside">
            @if($vistaMultidominio)
                <section class="rm-psychology-panel rm-psychology-interpretation" aria-labelledby="psicologia-interpretacion-titulo">
                    <header><div><h2 id="psicologia-interpretacion-titulo">Interpretación del perfil</h2></div><button type="button" class="rm-psychology-help" x-on:click="ayudaTema = 'resultado'; ayudaAbierta = true" aria-label="Explicar la interpretación del perfil"><i class="ph ph-question" aria-hidden="true"></i></button></header>
                    <p>{{ $resumenPetersen['explicacion'] }}</p><p>El perfil es orientativo y se interpreta junto con las mediciones originales, la autonomía y el contexto clínico.</p>
                    <button type="button" wire:click="irASeccion('aplicaciones-instrumentales')" class="rm-btn rm-btn-secondary">Ver mediciones y método <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                </section>
            @else
                <section class="rm-psychology-panel rm-psychology-process {{ $hayPatron ? '' : 'is-incomplete' }}" aria-labelledby="psicologia-proceso-titulo">
                    <header><div><h2 id="psicologia-proceso-titulo">Proceso de evaluación</h2></div></header>
                    <ol><li><i class="ph ph-file-text" aria-hidden="true"></i><span>Registro original</span></li><li><i class="ph ph-chart-bar" aria-hidden="true"></i><span>Valoración</span></li><li><i class="ph ph-list-checks" aria-hidden="true"></i><span>Criterios</span></li><li><i class="ph ph-brain" aria-hidden="true"></i><span>Perfil orientativo</span></li></ol>
                    @unless($hayPatron)<p>La consulta se detiene cuando falta una valoración o condición necesaria.</p>@endunless
                    <button type="button" wire:click="irASeccion('orion-petersen-detalle')" class="rm-btn rm-btn-secondary">{{ $hayPatron ? 'Ver cómo se obtuvo' : 'Ver qué falta' }} <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                </section>
            @endif
            <section class="rm-psychology-panel" aria-labelledby="psicologia-fuentes-titulo">
                <header><div><h2 id="psicologia-fuentes-titulo">Evidencias y registros disponibles</h2><p>Inventario del expediente; cada registro se consulta en su fuente.</p></div><button type="button" class="rm-psychology-help" x-on:click="ayudaTema = 'fuentes'; ayudaAbierta = true" aria-label="Explicar las fuentes disponibles"><i class="ph ph-question" aria-hidden="true"></i></button></header>
                <div class="rm-psychology-evidence-table-wrap"><table class="rm-psychology-evidence-table"><caption class="sr-only">Cantidad de registros por tipo de fuente</caption><thead><tr><th scope="col">Tipo de registro</th><th scope="col">Cantidad</th></tr></thead><tbody><tr><td>Observaciones por componente</td><td>{{ $resumenFuentes['observacionales'] ?? 'No consultadas' }}</td></tr><tr><td>Aplicaciones instrumentales</td><td>{{ $resumenFuentes['instrumentales'] ?? 'No consultadas' }}</td></tr><tr><td>Valoraciones funcionales</td><td>{{ $resumenFuentes['funcionales'] ?? 'No consultadas' }}</td></tr><tr><td>Controles cognitivos</td><td>{{ $resumenFuentes['controles'] ?? 'No consultados' }}</td></tr></tbody></table></div>
                <button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-psychology-link">Ver registros originales <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
            </section>
            <section class="rm-psychology-panel" aria-labelledby="psicologia-criterios-titulo">
                <header><div><h2 id="psicologia-criterios-titulo">Qué se comprobó</h2><p>Condiciones necesarias para orientar el perfil.</p></div><button type="button" class="rm-psychology-help" x-on:click="ayudaTema = 'criterios'; ayudaAbierta = true" aria-label="Explicar las condiciones de valoración"><i class="ph ph-question" aria-hidden="true"></i></button></header>
                <ul class="rm-psychology-status-list rm-psychology-status-list--criteria">
                    @foreach($nombresRequisitos as $codigo => $nombre)
                        @php($estado = $petersen['requisitos'][$codigo]['estado'] ?? 'U')
                        <li><span>{{ $nombre }}</span><strong data-state="{{ $estado }}">{{ ['S' => 'Sustentado', 'N' => 'No acreditado', 'U' => 'Pendiente', 'X' => 'Contradictorio'][$estado] ?? 'Pendiente' }}</strong></li>
                    @endforeach
                </ul>
                <button type="button" wire:click="irASeccion('orion-petersen-detalle')" class="rm-psychology-link">Ver criterios y soportes <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
            </section>
        </div>
    </div>

    <x-ui.modal-livewire id="ayuda-resultados-psicologia" title="Ayuda para leer esta evaluación" :subtitle="$residente->nombre_completo" max-width="md" surface="reading" alpine-model="ayudaAbierta" alpine-close="ayudaAbierta = false" :show-validation="false">
        <div class="rm-psychology-help-content">
            <section x-show="ayudaTema === 'resultado'"><h3>Qué significa el resultado</h3><p>{{ $hayPatron ? 'El sistema encontró un perfil orientativo respaldado por las valoraciones disponibles. Debe leerse junto con la historia y la revisión profesional.' : ($areasEsperadasDocumentadas ? 'Las cinco áreas cuentan con una valoración de rendimiento esperado. No se identificó un patrón cognitivo clasificable en esta consulta; esto no reemplaza el seguimiento habitual.' : 'Con la información disponible aún no se puede asignar un perfil cognitivo. Esto no significa que se haya demostrado ausencia o presencia de deterioro.') }}</p><p>Las observaciones del seguimiento se mantienen visibles, aunque por sí solas no sustituyen una medición interpretable.</p></section>
            <section x-show="ayudaTema === 'faltantes'"><h3>Qué debe revisarse</h3><p>Para orientar el resultado se necesitan valoraciones originales y condiciones documentadas. Estas condiciones faltan, no quedaron acreditadas o presentan contradicciones:</p><ul>
                @if($dominiosPendientesVista->isNotEmpty())<li>Mediciones e interpretación de {{ $dominiosPendientesVista->count() }} {{ $dominiosPendientesVista->count() === 1 ? 'área cognitiva' : 'áreas cognitivas' }}.</li>@endif
                @foreach($nombresRequisitos as $codigo => $nombre)@if(($petersen['requisitos'][$codigo]['estado'] ?? 'U') !== 'S')<li>{{ $nombre }}: {{ ['N' => 'no acreditado con la información revisada', 'X' => 'hay información contradictoria', 'U' => 'falta información'][$petersen['requisitos'][$codigo]['estado'] ?? 'U'] ?? 'requiere revisión' }}.</li>@endif @endforeach
            </ul><p>Abra «Criterios y soportes» para consultar el motivo documentado de cada condición.</p></section>
            <section x-show="ayudaTema === 'dominios'"><h3>Qué son las cinco áreas</h3><p>Memoria, atención, funciones ejecutivas, lenguaje y capacidad visuoespacial se revisan por separado. «Sin medición interpretable» indica que falta una fuente original o información necesaria para interpretarla; no equivale a rendimiento normal.</p><p>El mapa visual muestra estados cualitativos. La distancia de los puntos no representa una puntuación ni la magnitud de un deterioro.</p></section>
            <section x-show="ayudaTema === 'fuentes'"><h3>Qué cuentan las fuentes</h3><p>Los números muestran cuántos registros de cada tipo se encontraron. Un control u observación ayuda a seguir cambios cotidianos; una aplicación instrumental necesita instrumento, versión, respuestas y método verificables para aportar una medición interpretable.</p><p>La cantidad de registros no indica por sí misma cómo está el residente.</p></section>
            <section x-show="ayudaTema === 'criterios'"><h3>Cómo se llega a un perfil</h3><p>Se comprueban el cambio cognitivo, la evidencia objetiva, la autonomía en actividades instrumentales, otras posibles causas y la calidad del conjunto. Si alguna pieza indispensable falta o se contradice, el sistema explica la limitación y puede abstenerse de clasificar.</p><p>Una orientación automática no equivale a un diagnóstico confirmado.</p></section>
            <section x-show="ayudaTema === 'controles'"><h3>Qué muestra la gráfica</h3><p>Cada barra cuenta controles cognitivos vigentes registrados en un mes de los últimos 90 días. Una barra más alta significa que se registraron más controles, no que el estado cognitivo mejoró o empeoró.</p><p>Los mismos valores aparecen en la tabla debajo de la gráfica.</p></section>
        </div>
        <x-slot:footer><button type="button" class="rm-btn rm-btn-primary" x-on:click="ayudaAbierta = false">Cerrar ayuda</button></x-slot:footer>
    </x-ui.modal-livewire>
</div>
