<section class="rm-card rm-expert-clinical-block" aria-labelledby="hallazgos-observacionales-titulo">
    <h2 id="hallazgos-observacionales-titulo" class="rm-expert-section-title">Hallazgos observacionales del seguimiento</h2>
    <p class="text-sm mt-2">{{ $hallazgosObservacionales['limite'] }}</p>
    @if($hallazgosObservacionales['persistido'])
        <p class="text-sm text-rm-secondary mt-2">Resultado conservado del {{ $hallazgosObservacionales['fecha'] }}. Se consulta su instantánea original; los cambios posteriores del expediente no modifican estos hallazgos.</p>
    @else
        <p class="text-sm text-rm-secondary mt-2">Cálculo de consulta sobre los controles actuales, separado de la evaluación histórica. La traza todavía no se conserva como una nueva evaluación.</p>
    @endif
    @if($mensajeObservacional)<p role="status" class="text-sm mt-3">{{ $mensajeObservacional }}</p>@endif
    @error('observaciones')<p role="alert" class="text-sm mt-3">{{ $message }}</p>@enderror
    @if($puedeEjecutarObservaciones)
        <button type="button" wire:click="ejecutarObservaciones" wire:loading.attr="disabled" class="rm-btn rm-btn-primary mt-3">Ejecutar y guardar hallazgos observacionales</button>
        <p class="text-sm mt-2">Incluye todos los controles disponibles hasta el momento de la ejecución, no solo esta página. Para una nueva ejecución después de guardar, abra de nuevo la consulta.</p>
    @endif
    <details class="rm-expert-supplement mt-3">
        <summary>Reglas aplicadas y alcance</summary>
        <p class="text-sm mt-2">Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($hallazgosObservacionales['fecha_corte'] ?? null, 'momento de la consulta') }}.</p>
        <ul class="text-sm mt-2 space-y-2">@foreach($hallazgosObservacionales['resumen'] as $codigoRegla => $resumenRegla)
            <li>{{ $resumenRegla['nombre'] ?? \MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones::REGLAS[$codigoRegla]['nombre'] }} · {{ ['CUMPLE' => 'Cumplió', 'NO_CUMPLE' => 'No cumplió', 'NO_EVALUABLE' => 'No evaluable'][$resumenRegla['estado']] }}@if($resumenRegla['hallazgo']): {{ $resumenRegla['hallazgo'] }}@endif</li>
        @endforeach</ul>
        @if($hallazgosObservacionales['resumen'] === [])<p class="text-sm mt-2">Esta versión no tiene reglas observacionales elegibles. Los registros se conservan sin emitir hallazgos.</p>@endif
    </details>
    @forelse($hallazgosObservacionales['resultados'] as $hallazgo)
        <details class="rm-expert-source mt-3">
            <summary>{{ $hallazgo['nombre'] }} · {{ ['CUMPLE' => 'Cumplió', 'NO_CUMPLE' => 'No cumplió', 'NO_EVALUABLE' => 'No evaluable'][$hallazgo['estado']] }}</summary>
            @if($hallazgo['hallazgo'])<p class="rm-expert-prose mt-2">{{ $hallazgo['hallazgo'] }}</p>@endif
            <p class="rm-expert-prose mt-2">{{ $hallazgo['condicion']['motivo'] }}</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 text-sm">
                <div><dt>Fuente</dt><dd>Control cognitivo del {{ $hallazgo['fuente']['fecha_visible'] }}</dd></div>
                <div><dt>Responsable del registro</dt><dd>{{ $hallazgo['fuente']['nombre_autor'] }}</dd></div>
                <div><dt>Condición</dt><dd>El indicador debe estar marcado afirmativamente.</dd></div>
                <div><dt>Valor registrado</dt><dd>{{ $hallazgo['condicion']['observado'] === null ? 'No registrado o no interpretable' : ($hallazgo['condicion']['observado'] ? 'Marcado' : 'No marcado') }}</dd></div>
            </dl>
        </details>
    @empty
        <p class="rm-expert-prose mt-3">No hay decisiones observacionales para mostrar en esta ejecución. La ausencia de hallazgos no demuestra ausencia de dificultad.</p>
    @endforelse
    <p class="text-sm mt-3">{{ $hallazgosObservacionales['registros']->total() }} controles disponibles. Se muestran hasta 10 controles por página.</p>
    @if($hallazgosObservacionales['registros']->hasPages())<div class="mt-4">{{ $hallazgosObservacionales['registros']->links() }}</div>@endif
    @if($hallazgosObservacionales['persistido'])
        <details class="rm-expert-supplement mt-4"><summary>Historial de ejecuciones observacionales</summary>
            <div class="space-y-2 mt-3">@foreach($hallazgosObservacionales['historial'] as $anteriorObservacion)
                <button type="button" wire:click="seleccionarObservacion(@js($anteriorObservacion->getKey()))" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Consultar ejecución del {{ $anteriorObservacion->fecha_hora_inicio->format('d/m/Y H:i') }}</button>
            @endforeach</div>
            @if($hallazgosObservacionales['historial']->hasPages()){{ $hallazgosObservacionales['historial']->links() }}@endif
        </details>
    @endif
</section>
