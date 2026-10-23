<div class="rm-emergency-workspace space-y-4">
    @if(session()->has('mensaje'))<x-ui.callout variant="success" title="Registro confirmado">{{ session('mensaje') }}</x-ui.callout>@endif
    <section class="rm-alert-trace">
        <h3>{{ $alerta->residente?->nombre_completo }}</h3>
        <div class="flex flex-wrap gap-2"><x-ui.status-badge :estado="$alerta->estado" /><span class="rm-badge">Prioridad {{ $alerta->prioridad }}</span></div>
        <dl class="rm-alert-case__facts"><div><dt>Origen registrado</dt><dd>{{ $alerta->modulo ?: 'Sin origen registrado' }}</dd></div><div><dt>Responsable del seguimiento</dt><dd>{{ \App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::responsable($alerta->responsable) }}</dd></div><div><dt>Fecha de registro</dt><dd>{{ $alerta->fecha_hora?->format('d/m/Y H:i') ?: 'Sin fecha registrada' }}</dd></div></dl>
        <h3>Tipo y motivo registrado</h3><strong>{{ $alerta->tipo }}</strong><p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alerta->descripcion) }}</p>
    </section>
    <section class="rm-alert-trace"><h3>Historial de eventos · {{ count($timelineItems) }}</h3><p>Registros conservados en orden cronológico, con su autor y fecha.</p><x-patterns.timeline :items="$timelineItems" emptyMessage="No hay eventos registrados para esta alerta." /></section>
    @if($puedeSeguir)
    <form wire:submit="guardarAccion" class="rm-alert-trace"><x-ui.field label="Nota de seguimiento" for="nuevaIntervencionHistorialInput" required error="accion" hint="5 a 10.000 caracteres" help="Describe hechos realizados según tu responsabilidad. Una nota no equivale al cierre de la alerta."><textarea id="nuevaIntervencionHistorialInput" class="rm-textarea" wire:model="accion" minlength="5" maxlength="10000" rows="4" aria-invalid="{{ $errors->has('accion') ? 'true' : 'false' }}" aria-describedby="nuevaIntervencionHistorialInput-help {{ $errors->has('accion') ? 'nuevaIntervencionHistorialInput-error' : '' }}"></textarea></x-ui.field><x-ui.action-button type="submit" loading="guardarAccion" icono="ph-note-pencil">Registrar seguimiento</x-ui.action-button></form>
    @endif
</div>
