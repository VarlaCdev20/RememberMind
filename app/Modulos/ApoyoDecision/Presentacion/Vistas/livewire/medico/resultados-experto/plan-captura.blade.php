@if(isset($perfilInstrumental['plan_captura']))
    @php($planCaptura = $perfilInstrumental['plan_captura'])
    <details class="rm-orion-petersen-detail-panel my-4" data-dcl-capture-plan>
        <summary class="min-h-11 cursor-pointer font-semibold">Qué información conviene completar o revisar</summary>
        <p class="mt-2 text-sm text-[var(--rm-text-secondary)]">{{ $planCaptura['aviso'] }}</p>
        @forelse($planCaptura['pendientes'] as $pendiente)
            <section class="mt-4 min-w-0 rounded-xl bg-[var(--rm-surface-soft)] p-3 sm:p-4">
                <h3 class="font-semibold text-[var(--rm-text-primary)]">{{ $pendiente['titulo'] }}</h3>
                <ul class="mt-2 space-y-2 text-sm text-[var(--rm-text-primary)]">
                    @foreach($pendiente['por_revisar'] as $revision)
                        <li><strong>{{ ($nombresDominios + $nombresRequisitos)[$revision['elemento']] }}:</strong> {{ $revision['accion'] }}</li>
                    @endforeach
                </ul>
                <details class="mt-2">
                    <summary class="min-h-11 cursor-pointer text-sm font-medium">Guía de registro y fundamento</summary>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach($pendiente['registrar'] as $orientacion)<li>{{ $orientacion }}</li>@endforeach
                    </ul>
                    <p class="mt-3 text-sm"><strong>Dónde registrarlo:</strong> {{ $pendiente['ubicacion'] }}</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach($pendiente['fuentes'] as $fuente)
                            <li><a class="inline-flex min-h-11 max-w-full items-center break-words underline" href="{{ $planCaptura['fuentes'][$fuente]['url'] }}" target="_blank" rel="noopener noreferrer">{{ $planCaptura['fuentes'][$fuente]['titulo'] }} (abre otra pestaña)</a></li>
                        @endforeach
                    </ul>
                </details>
            </section>
        @empty
            <p class="mt-3 text-sm">Las áreas y requisitos tienen un estado definido en esta consulta. Esto incluye resultados negativos y no confirma por sí mismo un patrón de DCL ni su guardado.</p>
        @endforelse
    </details>
@endif
