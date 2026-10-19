<x-ui.section-card title="Historial de esta sesión" subtitle="Hasta seis instantáneas temporales propias. No es el archivo histórico institucional.">
    <div class="space-y-3">
        @forelse($historial as $item)
            <div class="rm-lab-history">
                <div class="min-w-0"><strong>{{ $item['salida']['escenario'] }}</strong><p class="text-sm text-rm-secondary">{{ $item['fecha'] }} · {{ $item['salida']['resultado']['manifiesto_versiones']['G09'] }}</p><p class="text-sm">{{ $item['salida']['resultado']['resumen']['petersen']['titulo'] }}</p></div>
                <button type="button" wire:click="abrirEjecucion('{{ $item['id'] }}')" class="rm-btn rm-btn-secondary" aria-label="Consultar ejecución de {{ $item['salida']['escenario'] }} del {{ $item['fecha'] }}">Consultar resultado</button>
            </div>
        @empty
            <x-ui.empty-state compact title="Sin ejecuciones en esta sesión" description="Ejecute un caso sintético para disponer de una instantánea consultable. No se cargarán evaluaciones de residentes." />
        @endforelse
    </div>
</x-ui.section-card>
