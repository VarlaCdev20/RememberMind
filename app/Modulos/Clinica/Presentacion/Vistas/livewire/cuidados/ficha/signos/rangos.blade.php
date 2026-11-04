<x-ui.modal-livewire id="signos-referencias" title="Referencias y objetivos" max-width="lg"
    subtitle="Interpretación de la última toma registrada"
    alpine-model="modalRangos" alpine-close="modalRangos = false" :show-validation="false">
    <x-slot:icon><i class="ph-bold ph-info" aria-hidden="true"></i></x-slot:icon>
    <div class="space-y-4">
        <p class="text-sm text-[var(--rm-text-secondary)]">
            El servidor aplica las reglas clínicas aprobadas y el objetivo médico válido en la fecha de la toma.
            Los límites críticos generales tienen prioridad. Los resultados sin regla se muestran sin interpretación automática.
        </p>
        <p class="text-xs text-[var(--rm-text-muted)]">
            Esta consulta no modifica objetivos, registros ni alertas. Los objetivos individuales se gestionan en el apartado médico autorizado.
        </p>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(\App\Modulos\Clinica\Aplicacion\Consultas\InterpretacionSignosVitales::VARIABLES as $clave => $variables)
                <section class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] p-3 space-y-2">
                    <h4 class="font-semibold text-[var(--rm-text-primary)]">{{ $metricasInfo[$clave]['nombre'] ?? $clave }}</h4>
                    <span class="inline-flex rounded-md border px-2 py-1 text-xs {{ $metricasInfo[$clave]['estado_badge'] ?? '' }}">
                        {{ $metricasInfo[$clave]['estado'] ?? 'Sin datos' }}
                    </span>
                    @forelse($metricasInfo[$clave]['resultados'] ?? [] as $resultado)
                        <div class="text-xs text-[var(--rm-text-secondary)] space-y-1">
                            <p class="font-medium">{{ $resultado['referencia_utilizada'] ?? 'Sin referencia aprobada' }}</p>
                            @if($resultado['rango_o_umbral'])<p>{{ $resultado['rango_o_umbral'] }}</p>@endif
                            <p>{{ $resultado['explicacion'] }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-[var(--rm-text-muted)]">Sin medición para este parámetro en la última toma.</p>
                    @endforelse
                </section>
            @endforeach
        </div>
    </div>
    <x-slot:footer>
        <button type="button" @click="modalRangos = false" class="rm-btn rm-btn-secondary">Cerrar</button>
    </x-slot:footer>
</x-ui.modal-livewire>
