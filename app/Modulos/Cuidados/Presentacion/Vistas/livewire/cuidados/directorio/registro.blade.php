@php
    $esSelectorRegistro = $drawerPaso === 'register-selector';
    $esFormularioClinico = in_array($registroTipo, ['dolor', 'alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true) && ! $esSelectorRegistro;
    $tituloClinico = ['dolor' => $dolorCodOrigen ? 'Reevaluación del dolor' : 'Valoración de dolor', 'alimentacion' => 'Registro de ingesta', 'hidratacion' => 'Hidratación', 'eliminacion' => 'Registro de eliminación', 'movilidad' => 'Registro de movilidad'][$registroTipo] ?? 'Nuevo registro';
    $esResultadoDolor = $drawerPaso === 'register-result' && $registroTipo === 'dolor' && $dolorResultado !== [];
    $esResultadoMovilidad = $drawerPaso === 'register-result' && $registroTipo === 'movilidad' && $movResultado !== [];
    $esResultadoEliminacion = $drawerPaso === 'register-result' && $registroTipo === 'eliminacion' && $elimResultado !== [];
    $esResultadoHidratacion = $drawerPaso === 'register-result' && $registroTipo === 'hidratacion' && $hidratacionResultado !== [];
    $esResultadoIngesta = $drawerPaso === 'register-result' && $registroTipo === 'alimentacion' && $ingestaResultado !== [];
    $esResultadoSignos = $drawerPaso === 'register-result' && $registroTipo === 'signos' && $signosResultadoRegistro !== [];
    $hayCriticoSignos = collect($signosEvaluacion['resultados'] ?? [])->contains(fn (array $resultado) => ($resultado['severidad'] ?? null) === 'CRITICO' || ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR');
    $generaraAlertaSignos = collect($signosEvaluacion['resultados'] ?? [])->contains(fn (array $resultado) => ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR');
    $volverRegistro = $confirmarDescarte ? null : ($signosConfirmacionPendiente ? 'cancelarConfirmacionSignos' : ($drawerPaso === 'register-form' ? 'volverPanelDetalle' : null));
    $guardarMetodo = match ($registroTipo) {
        'hidratacion' => 'guardarHidratacion', 'signos' => 'guardarSignos', 'medicacion' => 'guardarMed', 'dolor' => 'guardarDolor',
        'eliminacion' => 'guardarEliminacion', 'alimentacion', 'movilidad' => 'guardarCuidado',
        'seguimiento' => 'guardarSeguimiento', 'procedimiento' => 'guardarProcedimiento',
        'alerta' => 'guardarAlerta', default => null,
    };
@endphp
@if($registroTipo === 'heridas' && $mostrarSelectorModal && $detalleResidente)
    <livewire:app.frontend.livewire.enfermeria.cuidados.registros-enfermeria :cod-residente="$detalleResidente['cod_residente']" :emergente="true" :key="'heridas-popup-'.$detalleResidente['cod_residente']" />
@elseif($registroTipo === 'cognicion' && $mostrarSelectorModal && $detalleResidente)
    <livewire:cuidados.observacion-cognitiva :cod-residente="$detalleResidente['cod_residente']" :key="'cognicion-popup-'.$detalleResidente['cod_residente']" />
@elseif($registroTipo === 'conducta' && $mostrarSelectorModal && $detalleResidente)
    <livewire:cuidados.registro-conducta :cod-residente="$detalleResidente['cod_residente']" :emergente="true" :key="'conducta-popup-'.$detalleResidente['cod_residente']" />
@else

<x-ui.quick-register-modal
    id="resident-register"
    model="mostrarSelectorModal"
    class="rm-resident-directory__register-modal {{ $esFormularioClinico ? 'rm-clinical-form-modal' : '' }} {{ $registroTipo === 'dolor' ? 'rm-resident-directory__register-modal--dolor' : '' }} {{ $registroTipo === 'alimentacion' ? 'rm-resident-directory__register-modal--ingesta' : '' }} {{ $registroTipo === 'eliminacion' ? 'rm-resident-directory__register-modal--eliminacion' : '' }} {{ $registroTipo === 'movilidad' ? 'rm-resident-directory__register-modal--movilidad' : '' }} {{ $registroTipo === 'hidratacion' ? 'rm-resident-directory__register-modal--hidratacion' : '' }} {{ $esResultadoSignos ? 'rm-resident-directory__register-modal--signos-result' : '' }} {{ $esSelectorRegistro ? 'rm-resident-directory__register-modal--selector' : ($registroTipo === 'signos' ? 'rm-resident-directory__register-modal--signos' : 'rm-resident-directory__register-modal--form') }} {{ $signosConfirmacionPendiente ? 'rm-resident-directory__register-modal--signos-confirm' : '' }}"
    :title="$esSelectorRegistro && $detalleResidente ? 'Registrar para '.\Illuminate\Support\Str::title(mb_strtolower($detalleResidente['nombre_completo'])) : (($esResultadoSignos || $esResultadoDolor || $esResultadoIngesta || $esResultadoHidratacion || $esResultadoEliminacion || $esResultadoMovilidad) ? 'Resultado del registro' : ($signosConfirmacionPendiente ? ($hayCriticoSignos ? 'Revisión de medición crítica' : 'Revisión de presión arterial') : ($registroTipo === 'signos' ? 'Signos vitales' : ($esFormularioClinico ? $tituloClinico : 'Nuevo registro'))))"
    :subtitle="$registroTipo === 'movilidad' && !$esResultadoMovilidad ? 'Registra la movilidad observada, los apoyos utilizados y la respuesta del residente.' : ($registroTipo === 'eliminacion' && !$esResultadoEliminacion ? 'Registra una eliminación observada durante el cuidado del residente.' : ($registroTipo === 'hidratacion' && !$esResultadoHidratacion ? 'Registro de aporte de líquidos del residente' : ($esSelectorRegistro ? 'Selecciona qué deseas registrar' : (($esResultadoSignos || $esResultadoDolor || $esResultadoIngesta || $esResultadoHidratacion || $esResultadoEliminacion || $esResultadoMovilidad) ? 'Seguimiento clínico actualizado' : ($signosConfirmacionPendiente ? 'Comprueba las lecturas señaladas' : ($registroTipo === 'signos' ? 'Registro clínico del residente' : ($registroTipo === 'alimentacion' ? 'Registra la alimentación y tolerancia observada durante el turno.' : ($registroTipo === 'dolor' ? ($dolorCodOrigen ? 'Registra una nueva valoración sin modificar la historia anterior.' : 'Registra intensidad, localización y características observadas del dolor.') : 'Registro del residente seleccionado'))))))))"
    close-method="cerrarSelectorRegistro"
    :back-method="($esResultadoSignos || $esResultadoDolor || $esResultadoIngesta || $esResultadoHidratacion || $esResultadoEliminacion || $esResultadoMovilidad) ? null : $volverRegistro"
    :back-label="$signosConfirmacionPendiente ? 'Volver y revisar' : 'Volver al selector de registros'">
    <x-slot:icon>
        @if($esSelectorRegistro)
            <span class="rm-quick-register__header-icon" aria-hidden="true"><i class="ph-bold ph-plus-circle"></i></span>
        @elseif($registroTipo === 'dolor')
            <span class="rm-clinical-form__icon" aria-hidden="true"><i class="ph-bold ph-person"></i></span>
        @elseif($registroTipo === 'alimentacion')
            <span class="rm-ingesta__header-icon" aria-hidden="true"><i class="ph-bold ph-bowl-food"></i></span>
        @elseif($registroTipo === 'hidratacion')
            <span class="rm-hidratacion__header-icon" aria-hidden="true"><i class="ph-bold ph-drop"></i></span>
        @elseif($esFormularioClinico)
            <i class="ph-bold {{ ['dolor' => 'ph-person', 'alimentacion' => 'ph-bowl-food', 'eliminacion' => 'ph-drop', 'movilidad' => 'ph-person-simple-walk'][$registroTipo] }}" aria-hidden="true"></i>
        @elseif($registroTipo === 'signos')
            <span class="rm-signos__header-icon-box" aria-hidden="true">
                <i class="ph-bold ph-heartbeat rm-signos__header-icon"></i>
            </span>
        @endif
    </x-slot:icon>
    <x-slot:context>
        @include('cuidados::livewire.cuidados.directorio.registro-contexto')
    </x-slot:context>
    @include('cuidados::livewire.cuidados.directorio.registro-contenido')
    @if(in_array($drawerPaso, ['register-form', 'register-result'], true) || $confirmarDescarte)
    <x-slot:footer>
        @include('cuidados::livewire.cuidados.directorio.registro-acciones')
    </x-slot:footer>
    @endif
</x-ui.quick-register-modal>
@endif
<livewire:medicacion.administracion-medicacion-modal :key="'administracion-directorio'" />
