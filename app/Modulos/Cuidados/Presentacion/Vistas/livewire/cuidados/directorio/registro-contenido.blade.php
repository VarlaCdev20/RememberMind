@if($detalleResidente)
    @if($registroTipo === 'dolor' && $drawerPaso === 'register-form')
        <div wire:key="pain-capture-{{ $detalleResidente['cod_residente'] }}-{{ $dolorCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
            @include('livewire.cuidados.partials.mis-residentes-dolor')
        </div>
    @endif
    @if($registroTipo === 'alimentacion' && $drawerPaso === 'register-form')
        <div wire:key="intake-capture-{{ $detalleResidente['cod_residente'] }}-{{ $registroCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
            @include('livewire.cuidados.partials.mis-residentes-ingesta')
        </div>
    @endif
    @if($registroTipo === 'hidratacion' && $drawerPaso === 'register-form')
        <div wire:key="hydration-capture-{{ $detalleResidente['cod_residente'] }}-{{ $registroCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
            @include('livewire.cuidados.partials.mis-residentes-hidratacion')
        </div>
    @endif
    @if($registroTipo === 'movilidad' && $drawerPaso === 'register-form')
        <div wire:key="mobility-capture-{{ $registroCapturaVersion }}">@include('livewire.cuidados.partials.mis-residentes-movilidad')</div>
    @endif
    @if($registroTipo === 'eliminacion' && $drawerPaso === 'register-form')
        <div wire:key="elimination-capture-{{ $detalleResidente['cod_residente'] }}-{{ $registroCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
            @include('livewire.cuidados.partials.mis-residentes-eliminacion')
        </div>
    @endif
    @if($confirmarDescarte)
        <section class="rm-resident-directory__discard" role="alert" aria-labelledby="resident-discard-title">
            <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
            <h4 id="resident-discard-title">¿Salir sin guardar?</h4>
            <p>Tienes cambios sin registrar. Si confirmas, se descartarán los datos introducidos. El historial guardado permanecerá intacto.</p>
        </section>
    @elseif($esResultadoDolor)
        <x-ui.resultado-operacion-clinica :title="($dolorResultado['reevaluacion'] ?? false) ? 'Reevaluación registrada' : 'Valoración de dolor registrada'" message="La valoración se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$dolorResultado['fecha_hora']" :professional="$dolorResultado['profesional']" :measurements="$dolorResultado['mediciones']" />
        @can('valoraciones_dolor.ver')
            <div class="rm-dolor" wire:key="pain-result-{{ $dolorResultado['codigo'] }}" x-data="rmDolorRegistro(@js($dolorHistorial), { eva: '' }, '', { persisted: true, origin: @js($dolorCodOrigen), episode: @js($dolorEpisodio) })">
                <x-ui.clinical-consultation-bar><button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="dolor-grafica-popup" :aria-expanded="trendOpen">Ver evolución</button></x-ui.clinical-consultation-bar>
                @include('livewire.cuidados.partials.mis-residentes-dolor-grafica')
            </div>
        @endcan
    @elseif($esResultadoIngesta)
        <x-ui.resultado-operacion-clinica title="Ingesta registrada" message="La comida observada se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$ingestaResultado['fecha_hora']" :professional="$ingestaResultado['profesional']" :measurements="$ingestaResultado['mediciones']" />
        @can('registros_ingesta.ver')
            <div class="rm-ingesta" wire:key="intake-result-{{ $ingestaResultado['codigo'] }}" x-data="rmIngestaRegistro(@js($ingestaHistorial), {}, '', @js(config('enfermeria.porcentaje_baja_ingesta', 50)), true)" :data-graph-open="trendOpen ? 'true' : 'false'">
                <x-ui.clinical-consultation-bar><button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="ingesta-grafica-popup" :aria-expanded="trendOpen">Ver evolución</button></x-ui.clinical-consultation-bar>
                @include('livewire.cuidados.partials.mis-residentes-ingesta-grafica')
            </div>
        @endcan
    @elseif($esResultadoHidratacion)
        <x-ui.resultado-operacion-clinica title="Hidratación registrada" message="El aporte quedó incorporado al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$hidratacionResultado['fecha_hora']" :professional="$hidratacionResultado['profesional']" :measurements="$hidratacionResultado['mediciones']" />
        @can('registros_hidratacion.ver')
            <div class="rm-hidratacion" wire:key="hydration-result-{{ $hidratacionResultado['codigo'] }}" x-data="rmHidratacionRegistro(@js($hidratacionHistorial), {}, '', true)" :data-graph-open="trendOpen ? 'true' : 'false'">
                <x-ui.clinical-consultation-bar><button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="hidratacion-grafica-popup" :aria-expanded="trendOpen">Ver evolución</button></x-ui.clinical-consultation-bar>
                @include('livewire.cuidados.partials.mis-residentes-hidratacion-grafica')
            </div>
        @endcan
    @elseif($esResultadoMovilidad)
        <x-ui.resultado-operacion-clinica title="Movilidad registrada" message="El evento se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$movResultado['fecha_hora']" :professional="$movResultado['profesional']" :measurements="$movResultado['mediciones']" />
        @can('registros_movilidad.ver')
            <div class="rm-movilidad" wire:key="mobility-result-{{ $movResultado['codigo'] }}" x-data="rmMovilidadRegistro(@js($movHistorial), {}, true)"><button type="button" class="rm-btn-secondary" @click="openTrend($event.currentTarget)" aria-controls="movilidad-historial-popup" :aria-expanded="trendOpen">Ver historial</button>@include('livewire.cuidados.partials.mis-residentes-movilidad-historial')</div>
        @endcan
    @elseif($esResultadoEliminacion)
        <x-ui.resultado-operacion-clinica title="Eliminación registrada" message="El evento se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$elimResultado['fecha_hora']" :professional="$elimResultado['profesional']" :measurements="$elimResultado['mediciones']" />
        @can('registros_eliminacion.ver')
            <div class="rm-eliminacion" wire:key="elimination-result-{{ $elimResultado['codigo'] }}" x-data="rmEliminacionRegistro(@js($elimHistorial), {}, true)" :data-history-open="trendOpen ? 'true' : 'false'">
                <x-ui.clinical-consultation-bar><button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="eliminacion-historial-popup" :aria-expanded="trendOpen">Ver historial</button></x-ui.clinical-consultation-bar>
                @include('livewire.cuidados.partials.mis-residentes-eliminacion-historial')
            </div>
        @endcan
    @elseif($esResultadoSignos)
        <x-ui.resultado-operacion-clinica
            :variant="($signosResultadoRegistro['hay_critico'] ?? false) ? 'critical' : (count($signosResultadoRegistro['advertencias'] ?? []) ? 'warning' : 'success')"
            :title="($signosResultadoRegistro['hay_critico'] ?? false) ? 'Registro guardado · Atención requerida' : 'Registro guardado correctamente'"
            message="Los datos ingresados se validaron y quedaron guardados en el seguimiento clínico del residente."
            :resident="$detalleResidente['nombre_completo']"
            :date-time="$signosResultadoRegistro['fecha_hora'] ?? null"
            :professional="$signosResultadoRegistro['profesional'] ?? null"
            :measurements="$signosResultadoRegistro['mediciones'] ?? []"
            :alert-code="$signosResultadoRegistro['cod_alerta'] ?? null"
            :warnings="$signosResultadoRegistro['advertencias'] ?? []"
        />
    @elseif($signosConfirmacionPendiente && $registroTipo === 'signos')
        <section class="rm-signos__confirm" data-tone="{{ $hayCriticoSignos ? 'danger' : 'warning' }}" role="group" aria-labelledby="signos-confirm-title" aria-describedby="signos-confirm-description">
            <h4 id="signos-confirm-title"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> {{ $hayCriticoSignos ? ($signosPasoConfirmacion === 'final' ? 'Confirmar valor crítico' : 'Revisión de medición crítica') : 'Revisar lectura atípica' }}</h4>
            <p id="signos-confirm-description">{{ $signosPasoConfirmacion === 'final' ? ($hayCriticoSignos ? 'La lectura fue revisada. Al continuar se guardará un nuevo registro, se volverá a evaluar y, si corresponde, se generará una alerta abierta vinculada a la medición.' : 'La lectura fue revisada. Al continuar se guardará el registro sin clasificar esta relación entre presiones como crítica por sí sola.') : 'Comprueba la transcripción, la técnica y el contexto clínico. Sigue el protocolo institucional vigente antes de continuar.' }}</p>
            <ul>
                @foreach(($signosEvaluacion['resultados'] ?? []) as $resultado)
                    @if(($resultado['severidad'] ?? null) === 'CRITICO' || ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR')
                        <li><strong>{{ str_replace('_', ' ', ucfirst($resultado['variable'])) }}</strong><span>{{ $resultado['valor'] }} {{ $resultado['unidad'] }}</span><em>{{ $resultado['severidad'] ?? 'Requiere confirmación' }}</em>
                            @if(filled($resultado['rango_o_umbral'] ?? null))<small>{{ ($resultado['fuente_evaluacion'] ?? null) === 'UMBRAL_CRITICO' ? 'Umbral utilizado' : 'Referencia utilizada' }}: {{ $resultado['rango_o_umbral'] }}</small>@endif
                            @if(filled($resultado['explicacion'] ?? null))<small>{{ $resultado['explicacion'] }}</small>@endif
                        </li>
                    @endif
                @endforeach
                @if(is_numeric($signoSis) && is_numeric($signoDia) && (float)$signoSis <= (float)$signoDia)
                    <li><strong>Presión arterial atípica</strong><span>{{ $signoSis }} / {{ $signoDia }} mmHg</span><em>Revisar lectura</em></li>
                @endif
            </ul>
            @if($signosPasoConfirmacion === 'revision')
                <p>Los datos aún no se han guardado. Antes de continuar:</p>
                <ul class="rm-signos__review-checks" aria-label="Pasos de revisión"><li>Verifica la transcripción.</li><li>Verifica la técnica utilizada.</li><li>Revisa el contexto clínico.</li><li>Sigue el protocolo institucional.</li></ul>
            @endif
        </section>
    @elseif(in_array($drawerPaso, ['register-selector', 'register-form'], true) && !in_array($registroTipo, ['dolor', 'alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true))
        <div wire:key="register-capture-{{ $registroTipo }}-{{ $registroCapturaVersion }}" @if(in_array($registroTipo, ['medicacion', 'seguimiento', 'procedimiento', 'alerta'], true)) x-data="rmClinicalCapture(@js($registroInicial))" @endif>
        @include('livewire.cuidados.partials.mis-residentes-registro-contenido')
        </div>
    @endif
@endif
