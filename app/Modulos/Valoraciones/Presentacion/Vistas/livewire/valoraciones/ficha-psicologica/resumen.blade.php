<section aria-labelledby="ficha-registros-titulo" class="rm-resident-file__section">
    <div class="rm-resident-file__section-heading">
        <div><h2 id="ficha-registros-titulo">El expediente de un vistazo</h2><p>Abre una sección para consultar sus registros.</p></div>
        <span class="rm-resident-file__period">Todos los registros</span>
    </div>
    <div class="rm-resident-file__metrics">
        <x-ui.metric-card icon="ph-brain" :value="$totalInstrumentos" label="Aplicaciones registradas" description="Instrumentos y observaciones" :href="route('admin.psicologia.residente.ficha', ['residente' => $residente, 'tab' => 'cognitiva'])" />
        <x-ui.metric-card icon="ph-heart" :value="$totalValoraciones" label="Valoraciones psicológicas" description="Ánimo, afecto y conducta" :href="route('admin.psicologia.residente.ficha', ['residente' => $residente, 'tab' => 'afectiva'])" />
        <x-ui.metric-card icon="ph-person-simple-walk" :value="$totalIadl" label="Registros de autonomía" description="Actividades de la vida diaria" :href="route('admin.psicologia.residente.ficha', ['residente' => $residente, 'tab' => 'funcionamiento'])" />
        <x-ui.metric-card icon="ph-calendar-check" :value="$totalSesiones" label="Atenciones registradas" description="Seguimiento de Psicología" :href="route('admin.psicologia.residente.ficha', ['residente' => $residente, 'tab' => 'seguimiento'])" />
    </div>
</section>

<section class="rm-resident-file__section" aria-labelledby="ficha-reciente-titulo">
    <div class="rm-resident-file__section-heading">
        <div><h2 id="ficha-reciente-titulo">Últimos registros del expediente</h2><p>Información documentada, con su fecha y profesional.</p></div>
    </div>
    <div class="rm-resident-file__recent">
        <x-ui.section-card title="Registro cognitivo más reciente" subtitle="Aplicación u observación documentada" icon="ph-brain">
            @if($ultimaEvaluacionCognitiva)
                <div class="rm-resident-file__entry">
                    <div class="rm-resident-file__entry-heading">
                        <span class="rm-resident-file__kind">{{ $ultimaEvaluacionCognitiva->instrumento?->tipo === 'OBSERVACIONAL' ? 'Observación clínica' : 'Aplicación de instrumento' }}</span>
                        @if($ultimaEvaluacionCognitiva->fecha_hora)<time datetime="{{ $ultimaEvaluacionCognitiva->fecha_hora->toIso8601String() }}">{{ $ultimaEvaluacionCognitiva->fecha_hora->format('d/m/Y · H:i') }}</time>@endif
                    </div>
                    <h3>{{ $ultimaEvaluacionCognitiva->instrumento?->nombre ?? 'Instrumento sin nombre registrado' }}</h3>
                    <p>{{ $ultimaEvaluacionCognitiva->puntaje_total !== null ? 'Puntuación registrada: '.$ultimaEvaluacionCognitiva->puntaje_total.($ultimaEvaluacionCognitiva->puntaje_maximo !== null ? ' de '.$ultimaEvaluacionCognitiva->puntaje_maximo : '') : 'Sin puntuación registrada' }}</p>
                    <p class="rm-resident-file__author"><i class="ph ph-user" aria-hidden="true"></i>{{ trim(($ultimaEvaluacionCognitiva->evaluador?->nombres ?? '').' '.($ultimaEvaluacionCognitiva->evaluador?->apellido_paterno ?? '').' '.($ultimaEvaluacionCognitiva->evaluador?->apellido_materno ?? '')) ?: 'Profesional no documentado' }}</p>
                </div>
            @else
                <div class="rm-resident-file__empty"><i class="ph ph-notebook" aria-hidden="true"></i><div><h3>Aún no hay aplicaciones cognitivas</h3><p>Consulta esta sección para registrar una evaluación o revisar sus observaciones.</p></div></div>
            @endif
            <div class="rm-resident-file__entry-footer"><button type="button" wire:click="cambiarTab('cognitiva')" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Ver evaluación cognitiva<i class="ph ph-arrow-right" aria-hidden="true"></i></button></div>
        </x-ui.section-card>

        <x-ui.section-card title="Estado afectivo y conductual" subtitle="Última valoración psicológica documentada" icon="ph-heart">
            @if($ultimaValoracionPsicologica)
                <div class="rm-resident-file__entry">
                    <div class="rm-resident-file__entry-heading"><span class="rm-resident-file__kind">Valoración psicológica</span>@if($ultimaValoracionPsicologica->fecha_hora)<time datetime="{{ $ultimaValoracionPsicologica->fecha_hora->toIso8601String() }}">{{ $ultimaValoracionPsicologica->fecha_hora->format('d/m/Y · H:i') }}</time>@endif</div>
                    <dl class="rm-resident-file__observations"><div><dt>Estado de ánimo</dt><dd>{{ $ultimaValoracionPsicologica->estado_animo ? \Illuminate\Support\Str::headline(\Illuminate\Support\Str::lower($ultimaValoracionPsicologica->estado_animo)) : 'No documentado' }}</dd></div><div><dt>Ansiedad</dt><dd>{{ $ultimaValoracionPsicologica->ansiedad ? \Illuminate\Support\Str::headline(\Illuminate\Support\Str::lower($ultimaValoracionPsicologica->ansiedad)) : 'No documentada' }}</dd></div></dl>
                    @if($ultimaValoracionPsicologica->conclusion)<p>{{ \Illuminate\Support\Str::limit($ultimaValoracionPsicologica->conclusion, 240) }}</p>@endif
                    <p class="rm-resident-file__author"><i class="ph ph-user" aria-hidden="true"></i>{{ trim(($ultimaValoracionPsicologica->personal?->nombres ?? '').' '.($ultimaValoracionPsicologica->personal?->apellido_paterno ?? '').' '.($ultimaValoracionPsicologica->personal?->apellido_materno ?? '')) ?: 'Profesional no documentado' }}</p>
                </div>
            @else
                <div class="rm-resident-file__empty"><i class="ph ph-heart" aria-hidden="true"></i><div><h3>Aún no hay valoraciones afectivas</h3><p>Registra el estado de ánimo y el contexto emocional desde su sección.</p></div></div>
            @endif
            <div class="rm-resident-file__entry-footer"><button type="button" wire:click="cambiarTab('afectiva')" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Ver valoración afectiva<i class="ph ph-arrow-right" aria-hidden="true"></i></button></div>
        </x-ui.section-card>
    </div>
</section>
