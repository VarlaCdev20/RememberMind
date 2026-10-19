<x-ui.section-card title="Resultados de la evaluación" subtitle="Instantánea temporal del motor · modalidad experimental" id="lab-resultados">
    <dl class="rm-lab-meta">
        <div><dt>Caso ficticio</dt><dd>{{ $informe['escenario'] }} · {{ $informe['caso'] }}</dd></div>
        <div><dt>Fecha de ejecución</dt><dd>{{ $informe['fecha'] }}</dd></div>
        <div><dt>Fecha de corte</dt><dd>{{ $informe['corte'] }}</dd></div>
        <div><dt>Estado de ejecución</dt><dd>{{ $informe['estado'] }}</dd></div>
    </dl>
    <details class="rm-expert-source mt-4"><summary>Versiones utilizadas e identidad de la ejecución</summary>
        @include('livewire.superadministrador.sistema-experto.datos', ['datos' => $informe['versiones'] + ['evaluacion_motor' => $informe['evaluacion'], 'instantanea_sesion' => $informe['ejecucion']]])
    </details>
</x-ui.section-card>

<section class="rm-lab-conclusion" aria-labelledby="lab-resultado-titulo" aria-live="polite">
    <p class="rm-expert-eyebrow">Resultado experimental principal</p>
    <h2 id="lab-resultado-titulo" class="rm-expert-section-title">{{ $informe['titulo'] }}</h2>
    @if($informe['contradicciones'])<p class="font-semibold mb-3">Evidencias contradictorias pendientes de revisión: {{ implode(', ', array_map(fn ($id) => $dominios[$id] ?? $id, $informe['contradicciones'])) }}.</p>@endif
    <p class="rm-expert-prose">{{ $informe['interpretacion'] }}</p>
    <p class="mt-3 text-sm">{{ $informe['informe_petersen']['limitaciones'] }}</p>
</section>

<x-ui.section-card title="Perfil integral de cinco dominios" subtitle="Evaluabilidad, resultado descriptivo y estado Petersen se mantienen separados.">
    <div class="rm-lab-domains">
        @foreach($informe['dominios'] as $codigo => $d)
            <article class="rm-lab-domain" aria-labelledby="lab-dominio-{{ $codigo }}">
                <h3 id="lab-dominio-{{ $codigo }}" class="font-semibold">{{ $d['nombre'] }}</h3>
                <dl class="rm-lab-data mt-3">
                    <div><dt>Evaluabilidad</dt><dd>{{ $d['evaluabilidad_texto'] }} <span class="text-sm text-rm-secondary">({{ $d['evaluabilidad'] }})</span></dd></div>
                    <div><dt>Resultado descriptivo</dt><dd>{{ $d['resultado_texto'] }}</dd></div>
                    <div><dt>Evidencias</dt><dd>{{ $d['admitidas'] }} admitidas · {{ $d['excluidas'] }} excluidas</dd></div>
                    <div><dt>Reglas con soporte</dt><dd>{{ $d['reglas_con_soporte'] }}</dd></div>
                    <div><dt>Estado Petersen</dt><dd>{{ $informe['petersen']['traza']['dominios'][$codigo] }} · {{ $d['referencia'] }}</dd></div>
                </dl>
                <a href="#fundamento-{{ $codigo }}" class="rm-btn rm-btn-secondary mt-4">Consultar fundamento<span class="sr-only"> de {{ $d['nombre'] }}</span></a>
            </article>
        @endforeach
    </div>
</x-ui.section-card>

<x-ui.section-card title="Interpretación narrativa" subtitle="Texto del constructor de explicaciones del motor, sin agregar inferencias.">
    <p class="rm-expert-prose">{{ $informe['interpretacion'] }}</p>
    <p class="rm-expert-prose mt-3">Se examinaron {{ count($informe['evidencias']) }} fuentes instrumentales sintéticas. Los hallazgos por subcomponente se conservan por separado; una regla cumplida no equivale por sí misma a una clasificación Petersen. Consulte las fuentes excluidas y los soportes de cada requisito.</p>
    <p class="rm-expert-prose mt-3">{{ $informe['petersen']['limitaciones'] }} {{ $informe['limitaciones'][0] }}</p>
</x-ui.section-card>

<x-ui.section-card title="Evidencias y fuentes" subtitle="Procedencia y admisibilidad reales de esta ejecución. Las respuestas originales no se envían al navegador.">
    <table class="rm-lab-table">
        <caption class="sr-only">Fuentes, mapeos y motivos de exclusión</caption>
        <thead><tr><th scope="col">Origen y fecha</th><th scope="col">Componente y rol</th><th scope="col">Admisibilidad y mapeo</th><th scope="col">Criterio</th><th scope="col">Detalle autorizado</th></tr></thead>
        <tbody>
            @forelse($informe['evidencias'] as $e)
                <tr wire:key="evidencia-{{ $informe['ejecucion'] }}-{{ $e['id'] }}">
                    <td data-label="Origen y fecha">{{ $e['origen'] }}<p class="text-sm text-rm-secondary">{{ $e['fecha'] }}</p></td>
                    <td data-label="Componente y rol">{{ $e['componente'] }}<p class="text-sm">{{ $e['rol'] }}</p></td>
                    <td data-label="Admisibilidad y mapeo"><strong>{{ $e['estado'] }}</strong><p>{{ $e['mapeo'] }}</p>@foreach($e['motivos'] as $motivo)<p>{{ $motivo }}</p>@endforeach</td>
                    <td data-label="Criterio">{{ $dominios[$e['dominio']] ?? $e['dominio'] }}</td>
                    <td data-label="Detalle autorizado"><details><summary>Consultar procedencia</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $e['detalle']])</details></td>
                </tr>
            @empty
                <tr><td colspan="5">No hay fuentes instrumentales en esta ejecución.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-ui.section-card>

<x-ui.section-card title="Reglas examinadas" subtitle="Cumplidas, no cumplidas y no evaluadas conservan estados distintos.">
    @foreach($informe['reglas'] as $id => $r)
        <details class="rm-expert-source mb-3">
            <summary>{{ $id }} · {{ $dominios[$r['dominio']] ?? $r['dominio'] }} · {{ \App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarResultadoLaboratorio::texto($r['estado']) }}</summary>
            <p class="text-sm my-3">Versión: {{ $r['version'] }}. {{ $r['nombre'] ?? (\MotorExperto\investigacion\g09\CatalogoReglasG09Experimental::todas()[$id]['descripcion'] ?? 'Regla del contrato descriptivo de memoria') }}</p>
            @include('livewire.superadministrador.sistema-experto.datos', ['datos' => $r])
        </details>
    @endforeach
</x-ui.section-card>

<x-ui.section-card title="Contradicciones y limitaciones" subtitle="La falta de una evaluación válida no significa conservación de la función cognitiva.">
    @if($informe['contradicciones'])
        <div class="rm-alert rm-alert-warning" role="note"><div><strong>Evidencias contradictorias</strong><p>Dominios identificados por el motor: {{ implode(', ', $informe['contradicciones']) }}. Se conservan sus soportes y motivos sin seleccionar una conclusión automáticamente.</p></div></div>
    @endif
    <ul class="list-disc pl-5 space-y-2 my-4">@foreach($informe['limitaciones'] as $limitacion)<li>{{ $limitacion }}</li>@endforeach</ul>
    <details class="rm-expert-source"><summary>Información faltante y condiciones no habilitadas</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $informe['faltantes']])</details>
    <details class="rm-expert-source mt-3"><summary>Evidencias descartadas y motivos exactos</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $informe['exclusiones']])</details>
    <details class="rm-expert-source mt-3"><summary>Comparabilidad longitudinal</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $informe['longitudinal']])</details>
</x-ui.section-card>

<x-ui.section-card title="Petersen experimental" subtitle="Patrón experimental candidato o abstención según la salida efectiva del clasificador.">
    <h3 class="font-semibold">{{ $informe['titulo'] }}</h3>
    @if($informe['petersen']['patron_candidato'])<p class="mt-2"><strong>Patrón experimental candidato:</strong> {{ $informe['petersen']['patron_candidato'] }} · {{ $informe['petersen']['nombre_patron'] }}</p>@endif
    <p class="rm-expert-prose mt-3">{{ $informe['interpretacion'] }}</p>
    <dl class="rm-lab-data mt-4">
        @foreach($informe['requisitos'] as $id => $nombre)
            <div><dt>{{ $id }} · {{ $nombre }}</dt><dd>{{ \App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarResultadoLaboratorio::texto($informe['petersen']['traza']['requisitos'][$id]) }} <span class="text-sm">({{ $informe['petersen']['traza']['requisitos'][$id] }})</span></dd></div>
        @endforeach
    </dl>
    <div class="mt-4"><strong>Motivos exactos del clasificador</strong><ul class="list-disc pl-5">@foreach($informe['petersen']['motivos'] as $motivo)<li>{{ \App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarResultadoLaboratorio::texto($motivo) }} <span class="text-sm text-rm-secondary">({{ $motivo }})</span></li>@endforeach</ul></div>
    <details class="rm-expert-source mt-4"><summary>Soportes de requisitos y estados de dominio</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $informe['soportes_petersen']])</details>
    <details class="rm-expert-source mt-3"><summary>Traza del clasificador y versiones</summary>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $informe['petersen']['traza'] + ['version' => $informe['petersen']['version']]])</details>
    <p class="mt-4 text-sm">{{ $informe['informe_petersen']['limitaciones'] }} Revisión profesional y aprobación clínica pendientes.</p>
</x-ui.section-card>

<x-ui.section-card title="Fundamento por dominio y trazabilidad" subtitle="Fuente → registro original (identificador) → mapeo → hecho → condición → regla → consecuencia → resultado. Las respuestas protegidas no se muestran.">
    @foreach($informe['dominios'] as $codigo => $d)
        <details class="rm-expert-source mb-4" id="fundamento-{{ $codigo }}">
            <summary>{{ $d['nombre'] }} · {{ $d['evaluabilidad_texto'] }}</summary>
            <p class="rm-expert-prose my-3">{{ $d['interpretacion'] }}</p>
            <h4 class="font-semibold mt-4">Subcomponentes y hallazgos específicos</h4>
            @foreach($d['subcomponentes'] as $id => $s)
                <div class="border-b border-rm py-3"><strong>{{ $s['nombre'] }}</strong><p>{{ $s['evaluado'] ? 'Examinado por el motor' : 'Sin evaluación de tarea' }}</p>
                    @if($s['motivo_no_emision'])<p>{{ \App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarResultadoLaboratorio::texto($s['motivo_no_emision']) }}</p>@endif
                    @include('livewire.superadministrador.sistema-experto.datos', ['datos' => ['hallazgos' => $s['resultado'], 'reglas' => $s['reglas']]])
                </div>
            @endforeach
            <h4 class="font-semibold mt-4">Contexto y confusores</h4>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $d['confusores']])
            <h4 class="font-semibold mt-4">Condiciones, evidencias y rutas de soporte</h4>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $d['traza']])
            <p class="mt-3 text-sm">El historial comparable no está habilitado para estos casos. Consulte las instantáneas de sesión sin interpretarlas como seguimiento clínico.</p>
        </details>
    @endforeach
</x-ui.section-card>
@include('livewire.superadministrador.sistema-experto.historial-sesion')
<x-ui.section-card title="Exportación y conservación">
    <p>La exportación web de informes históricos requiere un resultado durable y autorizado. El archivo experimental actual solo está habilitado en consola/testing y base desechable; esta pantalla conserva instantáneas temporales de sesión. No se habilita una descarga que aparente conservación institucional.</p>
</x-ui.section-card>
