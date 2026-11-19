@php
    $ri = $resultadoInstitucional['contenido'] ?? null;
    $contratoRI = \App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado::class;
    $tituloRI = $contratoRI::PATRONES[$ri['patron'] ?? ''] ?? 'Aún no se puede establecer un perfil cognitivo';
@endphp
@if($ri)
<div class="rm-stored-expert" wire:key="resultado-guardado-{{ $ri['id'] }}">
    <section class="rm-card rm-stored-expert__hero" aria-labelledby="resultado-guardado-titulo">
        <div class="rm-stored-expert__identity">
            <span class="rm-stored-expert__symbol" aria-hidden="true"><i class="ph ph-brain"></i></span>
            <div><p class="rm-stored-expert__eyebrow">Sistema experto · Evaluación guardada</p><h2>{{ $residente->nombre_completo }}</h2></div>
            <div class="rm-stored-expert__date"><span>Información considerada hasta</span><strong>{{ \MotorExperto\conservacion\CodecInstantanea::leerFecha('corte', (object) $ri['fecha_corte'])->format('d/m/Y · H:i') }}</strong></div>
        </div>
        <h1 id="resultado-guardado-titulo">{{ $tituloRI }}</h1>
        <p class="rm-stored-expert__lead">
            @if(count($resumenGuardado['alteradas']))
                Alteración sustentada en {{ implode(', ', $resumenGuardado['alteradas']) }}.
            @else
                Esta evaluación no conserva áreas con alteración sustentada.
            @endif
            @if($resumenGuardado['sin_conclusion'])
                {{ $resumenGuardado['sin_conclusion'] }} área(s) sin una conclusión interpretable; consulte sus estados abajo.
            @endif
        </p>
        <p class="rm-stored-expert__notice"><i class="ph ph-info" aria-hidden="true"></i>Resultado orientativo. No constituye un diagnóstico médico confirmado.</p>
    </section>

    <div class="rm-stored-expert__charts">
        @include('apoyodecision::livewire.medico.resultados-experto.grafico-areas', ['areasGrafico' => $resumenGuardado['areas'], 'graficoId' => 'guardado-distribucion', 'graficoTitulo' => 'Panorama cognitivo', 'graficoSubtitulo' => 'Cómo se distribuyen las cinco áreas evaluadas.', 'modoGrafico' => 'dona'])
        @include('apoyodecision::livewire.medico.resultados-experto.grafico-areas', ['areasGrafico' => $resumenGuardado['areas'], 'graficoId' => 'guardado-mapa', 'graficoTitulo' => 'Estado de cada área', 'graficoSubtitulo' => 'Seleccione un área para destacar su resultado.', 'modoGrafico' => 'radial'])
    </div>

    <x-ui.section-card title="Fundamento de la clasificación" subtitle="Requisitos conservados al emitir esta evaluación." icon="ph-list-checks" class="rm-stored-expert__requirements">
        <dl class="rm-stored-expert__criteria">
            @foreach($resumenGuardado['requisitos'] as $requisito)
                <div><dt>{{ $requisito['nombre'] }}</dt><dd class="rm-cognitive-status-chart__badge" data-status="{{ $requisito['grupo'] }}"><i class="ph {{ $ri['requisitos'][$requisito['codigo']] === 'S' ? 'ph-check-circle' : 'ph-minus-circle' }}" aria-hidden="true"></i>{{ $requisito['estado'] }}</dd></div>
            @endforeach
        </dl>
    </x-ui.section-card>

    <section class="rm-card rm-stored-expert__sources" aria-labelledby="guardado-fuentes-titulo">
        <div><h2 id="guardado-fuentes-titulo">Fuentes del resultado</h2><p>Registros originales que sustentan esta evaluación.</p></div>
        <dl><div><dt>Aplicaciones instrumentales</dt><dd>{{ $resumenGuardado['aplicaciones'] }}</dd></div><div><dt>Respuestas instrumentales</dt><dd>{{ $resumenGuardado['respuestas'] }}</dd></div><div><dt>Valoraciones originales</dt><dd>{{ $resumenGuardado['juicios'] }}</dd></div></dl>
    </section>

    <div class="rm-card rm-stored-expert__details">
        <details class="rm-expert-supplement"><summary>Explicación original y limitaciones</summary><p class="rm-stored-expert__narrative">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\ResultadoInstitucionalVisible::narrativa($ri['explicacion']) }}</p></details>
        <details class="rm-expert-supplement rm-stored-expert__evidence" x-data="rmStoredEvidence(@js($resumenGuardado['hechos']))">
            <summary>Consultar los datos originales <span>{{ count($resumenGuardado['hechos']) }} datos</span></summary>
            <p class="rm-stored-expert__hint">Valores de esta evaluación guardada, sin completar con el expediente actual.</p>
            <div class="rm-stored-expert__filters">
                <label class="rm-field" for="guardado-buscar">Buscar dato o valor<input id="guardado-buscar" class="rm-filter-control" type="search" x-model="busqueda" @input="filtrar" placeholder="Ej. memoria" /></label>
                <label class="rm-field" for="guardado-fuente">Fuente<select id="guardado-fuente" class="rm-filter-control" x-model="tipo" @change="filtrar"><option value="">Todas las fuentes</option>@foreach($resumenGuardado['tipos_hechos'] as $tipo)<option>{{ $tipo }}</option>@endforeach</select></label>
            </div>
            <div class="rm-table-surface"><table class="rm-table rm-stored-expert__table"><caption class="sr-only">Valores originales usados en la evaluación</caption><thead><tr><th scope="col">Registro</th><th scope="col">Dato</th><th scope="col">Valor original</th><th scope="col">Fecha</th></tr></thead><tbody>
                <template x-for="h in visibles" :key="h.id"><tr><td data-label="Registro" x-text="h.tipo"></td><td data-label="Dato" x-text="h.dato"></td><td data-label="Valor original" x-text="h.valor"></td><td data-label="Fecha" x-text="h.fecha"></td></tr></template>
            </tbody></table></div>
            <p x-show="filtradas.length === 0" class="rm-stored-expert__hint" role="status">No se encontraron datos con estos filtros.</p>
            <nav class="rm-stored-expert__pager" aria-label="Páginas de datos originales"><span role="status" x-text="rango"></span><div><button class="rm-btn rm-btn-secondary" type="button" @click="cambiarPagina(-1)" :disabled="pagina <= 1" aria-label="Datos anteriores"><i class="ph ph-caret-left" aria-hidden="true"></i></button><span x-text="pagina + ' / ' + paginas"></span><button class="rm-btn rm-btn-secondary" type="button" @click="cambiarPagina(1)" :disabled="pagina >= paginas" aria-label="Datos siguientes"><i class="ph ph-caret-right" aria-hidden="true"></i></button></div></nav>
        </details>
        <details class="rm-expert-supplement"><summary>Reglas y condiciones evaluadas <span>{{ $resumenGuardado['reglas'] }} reglas</span></summary><p class="rm-stored-expert__hint">Se muestran los estados guardados al emitir el resultado.</p>
            @foreach($ri['criterios'] as $c)<div class="rm-stored-expert__rule-group"><h3>{{ $resultadoInstitucional['nombres_criterios'][$c['criterio']] ?? 'Área evaluada' }}</h3>@foreach($c['reglas'] as $r)<details><summary>{{ $resultadoInstitucional['nombres_reglas'][$r['regla']] ?? 'Regla evaluada' }} · {{ ['CUMPLE' => 'Condiciones satisfechas', 'NO_CUMPLE' => 'Condiciones no acreditadas', 'INHABILITADA' => 'Regla no habilitada'][$r['estado']] ?? 'No evaluable' }}</summary><ul>@foreach($r['condiciones'] as $condicion)<li>Condición {{ $loop->iteration }}: {{ $condicion['estado'] === 'CUMPLE' ? 'Acreditada' : 'No acreditada' }}</li>@endforeach</ul></details>@endforeach</div>@endforeach
            <p class="rm-stored-expert__hint">Se conservan el resultado, sus hechos, reglas y explicación. Este contrato no incluye la reproducción completa del ejecutor histórico.</p>
        </details>
    </div>
</div>
@if($revisiones && !$enfermeria)
    @include('apoyodecision::livewire.medico.resultados-experto.revision-profesional', ['esRevisionInstitucional' => true])
@endif
@else
    <section class="rm-card p-5" role="alert"><h1 class="text-xl font-bold">No se pudo verificar el resultado guardado</h1><p class="mt-3">La evaluación no superó su comprobación de integridad. No se mostrará como válida ni se permitirá revisarla. Vuelva a la consulta actual y comunique esta incidencia a soporte.</p></section>
@endif
