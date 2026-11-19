@php
    $ri = $resultadoInstitucional['contenido'] ?? null;
    $contratoRI = \App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado::class;
    $tituloRI = $contratoRI::PATRONES[$ri['patron'] ?? ''] ?? 'Aún no se puede establecer un perfil cognitivo';
@endphp
@if($ri)
<section class="rm-card p-5 space-y-4" aria-labelledby="resultado-guardado-titulo">
    <p class="text-sm text-rm-secondary">Evaluación guardada · {{ $residente->nombre_completo }} · Información considerada hasta el {{ \MotorExperto\conservacion\CodecInstantanea::leerFecha('corte', (object) $ri['fecha_corte'])->format('d/m/Y H:i') }}</p>
    <h1 id="resultado-guardado-titulo" class="text-2xl sm:text-3xl font-bold">{{ $tituloRI }}</h1>
    <p>Resultado orientativo recuperado de la base de datos. No constituye un diagnóstico médico confirmado.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3" aria-label="Cinco dominios cognitivos">
        @foreach($contratoRI::DOMINIOS as $codigo => $campo)<article class="rm-card p-4"><h2 class="font-bold">{{ $contratoRI::NOMBRES[$codigo] }}</h2><p class="mt-2">{{ $contratoRI::ESTADOS[$ri['dominios'][$codigo]] }}</p></article>
@endforeach
    </div>
    <details class="rm-expert-supplement"><summary>Ver los requisitos de la clasificación</summary><dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
        @foreach($ri['requisitos'] as $codigo => $estado)<div><dt class="font-semibold">{{ $contratoRI::NOMBRES[$codigo] }}</dt><dd>{{ $contratoRI::ESTADOS[$estado] }}@if($codigo === 'C02') · Derivado de los cinco dominios
@endif</dd></div>
@endforeach
    </dl></details>
    <details class="rm-expert-supplement" open><summary>Explicación original y limitaciones</summary><p class="whitespace-pre-line break-words mt-3">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\ResultadoInstitucionalVisible::narrativa($ri['explicacion']) }}</p></details>
    <details class="rm-expert-supplement"><summary>Hechos originales conservados ({{ count($ri['hechos']) }})</summary>
        <p class="text-sm mt-2">Estos valores pertenecen a esta evaluación guardada. No se completan con el expediente actual.</p>
        <div class="overflow-x-auto mt-3"><table class="w-full text-sm"><caption class="sr-only">Valores originales usados en la evaluación</caption><thead><tr><th scope="col" class="text-left p-2">Registro</th><th scope="col" class="text-left p-2">Dato</th><th scope="col" class="text-left p-2">Valor original</th><th scope="col" class="text-left p-2">Fecha</th></tr></thead><tbody>
        @foreach($ri['hechos'] as $h)
            @unless(str_starts_with($h['concepto'], 'lectura:'))<tr><td class="p-2">{{ ['RESPUESTA_INSTRUMENTAL' => 'Respuesta instrumental', 'APLICACION_INSTRUMENTAL' => 'Aplicación instrumental', 'JUICIO_PETERSEN' => 'Valoración original'][$h['tipo']] ?? 'Fuente original' }}</td><td class="p-2">{{ ucfirst(str_replace('_', ' ', $h['concepto'])) }}</td><td class="p-2 break-words">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\ResultadoInstitucionalVisible::valor($h) }}</td><td class="p-2">{{ \MotorExperto\conservacion\CodecInstantanea::leerFecha('fecha', (object) $h['fecha'])->format('d/m/Y H:i') }}</td></tr>
@endunless
        @endforeach
        </tbody></table></div>
    </details>
    <details class="rm-expert-supplement"><summary>Reglas y condiciones realmente evaluadas</summary><p class="text-sm mt-2">Los estados siguientes son los que se guardaron al emitir el resultado.</p>
        @foreach($ri['criterios'] as $c)<div class="mt-3"><h3 class="font-semibold">{{ $resultadoInstitucional['nombres_criterios'][$c['criterio']] ?? 'Área evaluada' }}</h3>@foreach($c['reglas'] as $r)<details class="mt-2"><summary>{{ $resultadoInstitucional['nombres_reglas'][$r['regla']] ?? 'Regla evaluada' }} · {{ ['CUMPLE' => 'Condiciones satisfechas', 'NO_CUMPLE' => 'Condiciones no acreditadas', 'INHABILITADA' => 'Regla no habilitada'][$r['estado']] ?? 'No evaluable' }}</summary><ul class="mt-2">@foreach($r['condiciones'] as $condicion)<li>Condición {{ $loop->iteration }}: {{ $condicion['estado'] === 'CUMPLE' ? 'Acreditada' : 'No acreditada' }}</li>
@endforeach</ul></details>
@endforeach</div>
@endforeach
    </details>
    <p class="text-sm text-rm-secondary">Se conservan el resultado, sus hechos, reglas y explicación. Este contrato no incluye la reproducción completa del DTO ni del ejecutor histórico.</p>
</section>
@if($revisiones && !$enfermeria)
    @include('apoyodecision::livewire.medico.resultados-experto.revision-profesional', ['esRevisionInstitucional' => true])
@endif
@else
    <section class="rm-card p-5" role="alert"><h1 class="text-xl font-bold">No se pudo verificar el resultado guardado</h1><p class="mt-3">La evaluación no superó su comprobación de integridad. No se mostrará como válida ni se permitirá revisarla. Vuelva a la consulta actual y comunique esta incidencia a soporte.</p></section>
@endif
