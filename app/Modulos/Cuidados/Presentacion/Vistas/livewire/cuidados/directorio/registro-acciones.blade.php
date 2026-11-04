@if($esResultadoDolor)
    <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeDolor">Volver al residente</button>
    @can('valoraciones_dolor.ver')
        <button type="button" class="rm-btn-secondary" wire:click="abrirReevaluacionDolor('{{ $dolorResultado['codigo'] }}')" wire:loading.attr="disabled" wire:target="abrirReevaluacionDolor">Registrar reevaluación</button>
    @endcan
@elseif($esResultadoIngesta)
    <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeIngesta">Volver al residente</button>
    <button type="button" class="rm-btn-secondary" wire:click="registrarOtraIngesta" wire:loading.attr="disabled">Registrar otra ingesta</button>
@elseif($esResultadoHidratacion)
    <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeHidratacion">Volver al residente</button>
    <button type="button" class="rm-btn-secondary" wire:click="registrarOtroAporte" wire:loading.attr="disabled">Registrar otro aporte</button>
@elseif($esResultadoMovilidad)
    <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeMovilidad">Volver al residente</button>
    <button type="button" class="rm-btn-secondary" wire:click="registrarOtraMovilidad" wire:loading.attr="disabled">Registrar otro evento</button>
@elseif($esResultadoEliminacion)
    <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeEliminacion">Volver al residente</button>
    <button type="button" class="rm-btn-secondary" wire:click="registrarOtraEliminacion" wire:loading.attr="disabled">Registrar otro evento</button>
@elseif($esResultadoSignos)
    <button type="button" class="rm-btn-secondary" wire:click="volverResidenteDesdeSignos">Volver al residente</button>
    @if(auth()->user()?->can('enfermeria.ver_ficha_paciente'))
        <a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente'], 'tab' => 'signos']) }}">Ver registro</a>
    @endif
    @if(filled($signosResultadoRegistro['cod_alerta'] ?? null) && auth()->user()?->can('alertas.ver'))
        <a class="rm-btn-danger" href="{{ route('admin.enfermeria.alertas', ['adulto' => $detalleResidente['cod_residente'], 'alerta' => $signosResultadoRegistro['cod_alerta']]) }}">Atender alerta <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
    @endif
@elseif($confirmarDescarte)
    <button type="button" class="rm-btn-secondary" wire:click="cancelarDescarte" wire:loading.attr="disabled">Seguir editando</button>
    <button type="button" class="rm-btn-danger" wire:click="descartarCambios" wire:loading.attr="disabled">Salir sin guardar</button>
    @error('continuidad_signos')<p class="rm-signos__error" role="alert">{{ $message }}</p>@enderror
@elseif($confirmarLimpiezaRegistro)
    <p class="rm-signos__clear-confirmation" role="status">¿Limpiar los campos sin guardar? Se conservarán el residente y el contexto del registro.</p>
    <button type="button" class="rm-btn-secondary" wire:click="cancelarLimpiezaRegistro" wire:loading.attr="disabled">Seguir editando</button>
    <button type="button" class="rm-btn-secondary" wire:click="limpiarCamposRegistro" wire:loading.attr="disabled">Confirmar limpieza</button>
@elseif($confirmarLimpiezaSignos && $registroTipo === 'signos')
    <p class="rm-signos__clear-confirmation" role="status">¿Limpiar las mediciones y las observaciones sin guardar? Se conservarán el residente, la fecha y la hora.</p>
    <button type="button" class="rm-btn-secondary" wire:click="$set('confirmarLimpiezaSignos', false)">Seguir editando</button>
    <button type="button" class="rm-btn-secondary" wire:click="limpiarCamposSignos" wire:loading.attr="disabled">Confirmar limpieza</button>
@elseif($signosConfirmacionPendiente && $registroTipo === 'signos')
    <button type="button" class="rm-btn-secondary" wire:click="cancelarConfirmacionSignos">Volver y revisar</button>
    <button type="button" class="{{ $hayCriticoSignos ? 'rm-btn-danger' : 'rm-btn-primary' }}" wire:click="guardarSignos" wire:loading.attr="disabled" wire:target="guardarSignos">{{ $signosPasoConfirmacion === 'final' ? ($generaraAlertaSignos ? 'Registrar y generar alerta' : ($hayCriticoSignos ? 'Registrar lectura crítica' : 'Confirmar lectura')) : ($hayCriticoSignos ? 'Confirmar que la lectura es correcta' : 'Confirmar lectura') }}</button>
@elseif($drawerPaso === 'register-form' && $guardarMetodo)
    @if($registroTipo === 'signos')
        <button type="button" class="rm-btn-secondary" wire:click="cerrarSelectorRegistro">Cancelar</button>
    @elseif($esFormularioClinico)
        <button type="button" class="rm-btn-secondary" wire:click="cerrarSelectorRegistro" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}">Cancelar</button>
    @else
        <button type="button" class="rm-btn-secondary" wire:click="volverPanelDetalle">Volver al selector</button>
    @endif
    <button type="button" class="rm-btn-secondary" wire:click="solicitarLimpiezaRegistro" wire:loading.attr="disabled"><i class="ph-bold ph-eraser" aria-hidden="true"></i> Limpiar campos</button>
    @if($registroTipo === 'medicacion')
        @if($medOcurrenciaSeleccionada)
            <button type="button" class="rm-btn-primary" wire:click="guardarMed" wire:loading.attr="disabled" wire:target="guardarMed">
                <i class="ph-bold ph-check" aria-hidden="true"></i>
                <span wire:loading.remove wire:target="guardarMed">Confirmar administración</span>
                <span wire:loading wire:target="guardarMed">Registrando…</span>
            </button>
        @endif
    @elseif($registroTipo === 'signos')
        <button type="button" class="{{ $hayCriticoSignos ? 'rm-btn-danger' : 'rm-btn-primary rm-btn-primary--confirm' }}" wire:click="guardarSignos" wire:loading.attr="disabled" wire:target="guardarSignos" @if($errors->any()) data-has-errors="true" @endif><i class="ph-bold {{ $hayCriticoSignos ? 'ph-warning-circle' : 'ph-check' }}" aria-hidden="true"></i><span wire:loading.remove wire:target="guardarSignos">{{ $errors->has('signos_guardado') ? 'Intentar nuevamente' : ($hayCriticoSignos ? 'Revisar lectura crítica' : 'Confirmar y registrar') }}</span><span wire:loading wire:target="guardarSignos">Evaluando…</span></button>
    @elseif($registroTipo === 'dolor')
        <button type="button" class="rm-btn-primary rm-btn-primary--confirm" wire:click="guardarDolor" wire:loading.attr="disabled" wire:target="guardarDolor"><i class="ph-bold ph-check" aria-hidden="true"></i><span wire:loading.remove wire:target="guardarDolor">Confirmar y registrar</span><span wire:loading wire:target="guardarDolor">Registrando…</span></button>
    @elseif(in_array($registroTipo, ['alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true))
        <button type="button" class="rm-btn-primary rm-btn-primary--confirm" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}"><i class="ph-bold ph-check" aria-hidden="true" wire:loading.remove wire:target="{{ $guardarMetodo }}"></i><span class="rm-btn__spinner" aria-hidden="true" wire:loading.inline-block wire:target="{{ $guardarMetodo }}"></span><span wire:loading.remove wire:target="{{ $guardarMetodo }}">Confirmar y registrar</span><span wire:loading wire:target="{{ $guardarMetodo }}">Registrando…</span></button>
    @elseif($esFormularioClinico)
        <button type="button" class="rm-btn-primary rm-btn-primary--confirm" @click="prepareFeedback(@js($registroTipo))" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}">
            <i class="ph-bold ph-check" aria-hidden="true"></i><span wire:loading.remove wire:target="{{ $guardarMetodo }}">Confirmar y registrar</span><span wire:loading wire:target="{{ $guardarMetodo }}">Registrando…</span>
        </button>
    @else
        <button type="button" class="rm-btn-primary" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}"><i class="ph-bold ph-floppy-disk" aria-hidden="true"></i> Guardar registro</button>
    @endif
@endif
