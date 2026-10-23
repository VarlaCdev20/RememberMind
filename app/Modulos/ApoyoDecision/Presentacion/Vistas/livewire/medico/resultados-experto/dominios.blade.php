<section class="rm-card rm-expert-domain-matrix" aria-labelledby="expert-domains-heading">
    <div class="rm-orion-section-heading"><div><p class="rm-expert-eyebrow">Disponibilidad por dominio</p><h2 id="expert-domains-heading" class="rm-expert-section-title">Áreas cognitivas</h2></div><span class="rm-orion-state">{{ count($informe['areas']) }} áreas · consulta documental</span></div>
    <p class="text-sm text-rm-secondary">Seleccione un área para consultar su información. La ausencia de resultado no acredita una capacidad preservada.</p>
    <div class="rm-expert-domain-options">
        @foreach($informe['areas'] as $codigo => $area)
<<<<<<< HEAD
            <button type="button" wire:click="seleccionarCriterio(@js($codigo))" wire:loading.attr="disabled" class="rm-orion-domain" aria-pressed="{{ $criterio === $codigo ? 'true' : 'false' }}" wire:key="expert-domain-{{ $codigo }}"><i class="ph {{ $area['icono'] }}" aria-hidden="true"></i><strong>{{ $area['nombre'] }}</strong><span>{{ $area['estado'] }}</span><span class="rm-orion-domain-link">{{ $criterio === $codigo ? 'Área seleccionada' : 'Consultar área' }} <i class="ph ph-arrow-right" aria-hidden="true"></i></span></button>
=======
            <button type="button" wire:click="seleccionarCriterio(@js($codigo))" wire:loading.attr="disabled" class="rm-orion-domain" aria-pressed="{{ $criterio === $codigo ? 'true' : 'false' }}" wire:key="expert-domain-{{ $codigo }}"><i class="ph {{ $area['icono'] }}" aria-hidden="true"></i><strong>{{ $area['nombre'] }}</strong><small>{{ $codigo }}</small><span>{{ $area['estado'] }}</span><span class="rm-orion-domain-link">{{ $criterio === $codigo ? 'Área seleccionada' : 'Consultar área' }} <i class="ph ph-arrow-right" aria-hidden="true"></i></span></button>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        @endforeach
    </div>
    <p wire:loading.delay wire:target="seleccionarCriterio" role="status">Consultando el área seleccionada…</p>
    <div class="rm-expert-domain-current" aria-live="polite"><strong>{{ $informe['actual']['nombre'] }}</strong><p>{{ $informe['actual']['conclusion'] }}</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirDetalleArea">Ver detalle del área</button></div>
</section>
