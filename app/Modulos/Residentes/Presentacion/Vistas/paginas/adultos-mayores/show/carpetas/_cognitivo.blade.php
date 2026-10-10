<x-ui.section-card title="Evaluación cognitiva" subtitle="Aplicaciones registradas y resultados del sistema experto" icon="ph-brain">
    @can('consultarResultados', [\App\Models\EvaluacionExperta::class, $adulto])
        <a class="rm-btn rm-btn-secondary min-h-11 mb-5 whitespace-normal" href="{{ route(auth()->user()->hasRole('ENFERMEROS') && !auth()->user()->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR']) ? 'admin.enfermeria.pacientes.resultados-experto' : 'admin.medico.residente.resultados-experto', ['residente' => $adulto->cod_residente]) }}">Consultar resultados del sistema experto</a>
    @endcan
    <div class="space-y-3">
        @forelse($evaluacionesLista as $aplicacion)
            <article class="rounded-xl border border-rm p-4">
                <h3 class="font-semibold">{{ $aplicacion->instrumento?->nombre ?? 'Instrumento sin descripción disponible' }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm mt-3">
                    <div><dt class="text-rm-secondary">Fecha de aplicación</dt><dd>{{ $aplicacion->fecha_hora?->format('d/m/Y H:i') ?? 'No disponible' }}</dd></div>
                    <div><dt class="text-rm-secondary">Puntaje guardado</dt><dd>{{ $aplicacion->puntaje_total ?? 'No registrado' }}@if($aplicacion->puntaje_maximo !== null) / {{ $aplicacion->puntaje_maximo }}@endif</dd></div>
                    <div><dt class="text-rm-secondary">Clasificación guardada</dt><dd>{{ $aplicacion->clasificacion ?? 'No registrada' }}</dd></div>
                    <div><dt class="text-rm-secondary">Interpretación guardada</dt><dd>{{ $aplicacion->interpretacion ?? 'No registrada' }}</dd></div>
                </dl>
            </article>
        @empty
            <p class="text-sm text-rm-secondary">No existen aplicaciones cognitivas registradas.</p>
        @endforelse
    </div>
    <p class="text-sm text-rm-secondary mt-4">Cada aplicación conserva su instrumento y contexto. Esta lista no establece deterioro, normalidad global ni una tendencia clínica.</p>
</x-ui.section-card>
