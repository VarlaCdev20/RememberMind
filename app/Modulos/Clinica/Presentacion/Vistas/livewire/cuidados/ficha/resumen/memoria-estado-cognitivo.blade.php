<section id="memoria-estado-cognitivo" class="rm-clinical-card mb-5 min-w-0 space-y-4 break-words text-[var(--rm-text-primary)]"
    aria-labelledby="memoria-estado-cognitivo-titulo" wire:key="memoria-{{ $adultoMayor->getKey() }}-{{ $periodoEstado }}"
    wire:loading.class="opacity-60" wire:target="setPeriodoEstado">
    <h2 id="memoria-estado-cognitivo-titulo" class="rm-clinical-card__title"><i class="ph-bold ph-brain" aria-hidden="true"></i>Memoria y estado cognitivo</h2>
    @if($resumenCognitivo === null)
        <p class="text-sm">No dispone de autorización para consultar el resumen cognitivo de este residente.</p>
    @else
        <p class="text-sm">Del {{ $resumenCognitivo['desde'] }} al {{ $resumenCognitivo['hasta'] }}.</p>
        <p><span class="inline-flex rounded-lg border px-2 py-1 text-sm font-semibold">{{ $resumenCognitivo['estado'] }}</span></p>
        <p class="font-semibold leading-relaxed">{{ $resumenCognitivo['observacion'] }}</p>
        @if($resumenCognitivo['principal'])
            <p class="text-sm leading-relaxed">Registro: {{ $resumenCognitivo['principal']['fecha'] }} · {{ $resumenCognitivo['principal']['autor'] }} · {{ $resumenCognitivo['principal']['estado'] }}.</p>
            <a href="#{{ $resumenCognitivo['principal']['ancla'] }}" class="inline-flex min-h-11 items-center text-sm font-semibold underline"
                onclick="event.preventDefault(); const control = document.getElementById(this.hash.slice(1)); control.open = true; control.querySelector('summary').focus()">Consultar control original</a>
        @endif
        <div class="grid min-w-0 gap-4 md:grid-cols-2">
            <div class="space-y-1"><h3 class="font-semibold">¿Por qué?</h3><p class="text-sm leading-relaxed">{{ $resumenCognitivo['por_que'] }}</p></div>
            <div class="space-y-1"><h3 class="font-semibold">¿Qué significa?</h3><p class="text-sm leading-relaxed">{{ $resumenCognitivo['alcance'] }}</p></div>
        </div>
        @if($resumenCognitivo['contradicciones'])
            <ul class="list-disc space-y-1 pl-5 text-sm">@foreach($resumenCognitivo['contradicciones'] as $discordancia)<li>{{ $discordancia }}</li>@endforeach</ul>
        @endif
        @if($resumenCognitivo['experto'])
            <div class="space-y-2 border-t border-[var(--rm-border)] pt-3">
                <h3 class="font-semibold">Resultado experto conservado · {{ $resumenCognitivo['experto']['estado'] }}</h3>
                <p class="font-semibold">{{ $resumenCognitivo['experto']['observacion'] }}</p>
                <p class="text-sm">{{ $resumenCognitivo['experto']['fecha'] }} · Solicitante: {{ $resumenCognitivo['experto']['autor'] }}</p>
                <p class="text-sm leading-relaxed">{{ $resumenCognitivo['experto']['alcance'] }}</p>
            </div>
        @else
            <p class="text-sm">No hay un resultado experto de memoria autorizado y verificable para este período. Las observaciones no sustituyen una evaluación instrumental específica de memoria.</p>
        @endif
        <a href="{{ $resumenCognitivo['analisis_url'] }}" class="inline-flex min-h-11 items-center text-sm font-semibold underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--rm-focus-ring-color)]">Análisis cognitivo</a>
        @foreach($resumenCognitivo['registros'] as $control)
            <details id="{{ $control['ancla'] }}" class="border-t border-[var(--rm-border)] py-2" wire:key="{{ $control['ancla'] }}">
                <summary class="min-h-11 cursor-pointer py-3 text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--rm-focus-ring-color)]">Control original · {{ $control['fecha'] }} · {{ $control['autor'] }}</summary>
                <p class="mb-3 text-sm">Estado del registro: {{ $control['estado'] }}</p>
                <dl class="grid min-w-0 gap-3 text-sm sm:grid-cols-2">
                    @foreach($control['campos'] as $etiqueta => $valor)
                        <div><dt class="font-semibold">{{ $etiqueta }}</dt><dd>{{ $valor }}</dd></div>
                    @endforeach
                    <div class="sm:col-span-2"><dt class="font-semibold">Observación original</dt><dd class="whitespace-pre-line [overflow-wrap:anywhere]">{{ filled($control['observacion']) ? $control['observacion'] : 'No registrada' }}</dd></div>
                </dl>
            </details>
        @endforeach
    @endif
</section>
