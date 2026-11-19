<section class="rm-card rm-expert-history {{ $resultadoInstitucional ? 'rm-expert-history--stored' : '' }} p-4 mb-4 space-y-3" aria-labelledby="historia-institucional-titulo">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h2 id="historia-institucional-titulo" class="font-bold">{{ $resultadoInstitucional ? 'Evaluaciones guardadas' : 'Evaluaciones guardadas de '.$residente->nombre_completo }}</h2>@unless($resultadoInstitucional)<p class="text-sm text-rm-secondary">Resultados originales y revisiones independientes.</p>@endunless</div>
        @if($resultadoInstitucional)
            <button type="button" wire:click="volverAConsultaActual" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Volver a la consulta actual</button>
        @elseif($puedeGuardarInstitucional)
            <button type="button" wire:click="guardarResultadoInstitucional" wire:loading.attr="disabled" class="rm-btn rm-btn-primary"><i class="ph ph-floppy-disk" aria-hidden="true"></i><span wire:loading.remove wire:target="guardarResultadoInstitucional">Evaluar y guardar resultado</span><span wire:loading wire:target="guardarResultadoInstitucional">Evaluando y guardando…</span></button>
        @endif
    </div>
    @if($mensajeGuardadoInstitucional)<p role="status" class="text-sm">{{ $mensajeGuardadoInstitucional }}</p>
@endif
    @error('guardadoInstitucional')<p role="alert" class="text-sm text-rm-danger">{{ $message }}</p>@enderror
    @if($historialInstitucional['total'] > 0)
        <details><summary class="cursor-pointer py-3">Abrir una evaluación anterior</summary><ul class="space-y-2 mt-2">
            @foreach($historialInstitucional['items'] as $h)<li><button type="button" wire:click="abrirResultadoInstitucional(@js($h['id']))" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary w-full text-left">{{ $h['fecha'] }} · {{ $h['resultado'] }}</button></li>
@endforeach
        </ul><nav class="flex flex-wrap items-center gap-3 mt-3" aria-label="Páginas de evaluaciones guardadas">
            <button type="button" wire:click="cambiarPaginaHistoriaInstitucional({{ $historialInstitucional['pagina'] - 1 }})" @disabled($historialInstitucional['pagina'] <= 1) wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Anteriores</button>
            <span class="text-sm">Página {{ $historialInstitucional['pagina'] }} de {{ $historialInstitucional['ultima'] }} · {{ $historialInstitucional['total'] }} evaluaciones</span>
            <button type="button" wire:click="cambiarPaginaHistoriaInstitucional({{ $historialInstitucional['pagina'] + 1 }})" @disabled($historialInstitucional['pagina'] >= $historialInstitucional['ultima']) wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Siguientes</button>
        </nav></details>
    @else
        <p class="text-sm text-rm-secondary">Todavía no hay un resultado conjunto guardado. Las observaciones y capturas originales conservan su propio historial.</p>
    @endif
</section>
