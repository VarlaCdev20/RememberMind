@php($claveRevision = ($esRevisionObservacional ?? false) ? 'observacional' : 'cognitiva')
<section class="rm-card rm-expert-clinical-block" @if(! ($esRevisionObservacional ?? false)) id="orion-revision-profesional" @endif aria-labelledby="revision-{{ $claveRevision }}-titulo">
    <h2 id="revision-{{ $claveRevision }}-titulo" class="rm-expert-section-title">{{ ($esRevisionObservacional ?? false) ? 'Hallazgos observacionales · Confirmación profesional' : 'Valoración del sistema experto · Confirmación profesional' }}</h2>
    @if($esRevisionObservacional ?? false)<p class="text-sm mt-2">Esta revisión confirma o rechaza los hallazgos descriptivos de la evaluación observacional conservada.</p>@endif
    <p class="text-sm mt-2">Psicología y Medicina revisan esta valoración por separado. Cada decisión conserva al profesional, la fecha y su justificación.</p>
    @if($mensajeRevision)<p class="text-sm mt-3" role="status">{{ $mensajeRevision }}</p>@endif
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        @foreach($revisiones as $disciplina => $revision)
            <div class="min-w-0">
                <h3 class="font-semibold">{{ $revision['nombre'] }}</h3>
                <p class="text-sm mt-2">{{ ['PENDIENTE' => 'Pendiente de revisión profesional', 'CONFIRMADA' => 'Valoración confirmada', 'RECHAZADA' => 'Valoración rechazada'][$revision['estado']] }}</p>
                @if($revision['puede_firmar'] && !$disciplinaRevision)
                    <button type="button" wire:click="{{ ($esRevisionObservacional ?? false) ? 'prepararRevisionObservacional' : 'prepararRevision' }}(@js($disciplina))" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-3">{{ $revision['cabeza'] ? 'Registrar rectificación' : 'Revisar valoración' }}</button>
                @endif
                @if($revision['historial'])
                    <details class="rm-expert-supplement mt-3"><summary>Consultar decisiones conservadas</summary>
                        @foreach($revision['historial'] as $firma)
                            <div class="text-sm space-y-2 mt-3 break-words"><p class="font-semibold">{{ $firma['profesional'] }} · {{ $firma['fecha'] }}</p><p>{{ $firma['decision'] === 'CONFIRMADA' ? 'Valoración confirmada' : 'Valoración rechazada' }}</p><p>{{ $firma['justificacion'] }}</p>
                            @if($firma['anterior'])<p>Rectifica una revisión anterior conservada en este historial.</p>@endif</div>
                        @endforeach
                    </details>
                @endif
            </div>
        @endforeach
    </div>
    @if($disciplinaRevision && $revisionObservacional === ($esRevisionObservacional ?? false))
        <form wire:submit="guardarRevision" class="space-y-3 mt-5">
            <p class="font-semibold">Revisión de {{ $revisiones[$disciplinaRevision]['nombre'] }}</p>
            <label for="decision-revision" class="block text-sm">Decisión</label>
            <select id="decision-revision" wire:model="decisionRevision" required class="rm-input w-full" aria-invalid="{{ $errors->has('decisionRevision') ? 'true' : 'false' }}" @error('decisionRevision') aria-describedby="decision-revision-error" @enderror><option value="">Seleccione una decisión</option><option value="CONFIRMADA">Confirmar valoración</option><option value="RECHAZADA">Rechazar valoración</option></select>
            @error('decisionRevision')<p id="decision-revision-error" role="alert" class="text-sm">{{ $message }}</p>@enderror
            <label for="justificacion-revision" class="block text-sm">Justificación profesional</label>
            <textarea id="justificacion-revision" wire:model="justificacionRevision" required maxlength="10000" rows="4" class="rm-input w-full" aria-invalid="{{ $errors->has('justificacionRevision') ? 'true' : 'false' }}" @error('justificacionRevision') aria-describedby="justificacion-revision-error" @enderror></textarea>
            @error('justificacionRevision')<p id="justificacion-revision-error" role="alert" class="text-sm">{{ $message }}</p>@enderror
            @error('revision')<p role="alert" class="text-sm">{{ $message }}</p>@enderror
            <p class="text-sm">La decisión se guardará con su cuenta y profesional vinculados. El resultado automático original conservará sus datos.</p>
            <div class="flex flex-wrap gap-3"><button type="submit" wire:loading.attr="disabled" class="rm-btn rm-btn-primary">Guardar decisión profesional</button><button type="button" wire:click="cerrarRevision" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Cancelar</button></div>
        </form>
    @endif
</section>
