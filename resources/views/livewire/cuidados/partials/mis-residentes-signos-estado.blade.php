@foreach($evaluaciones as $resultado)
    @php
        $nivel = $resultado['severidad'] ?? null;
        $tono = match ($nivel) {
            'CRITICO' => 'danger',
            'ALTO' => 'high',
            'ADVERTENCIA' => 'warning',
            'NORMAL' => 'success',
            'OBJETIVO_PERSONALIZADO' => 'target',
            default => ($resultado['comportamiento_alerta'] ?? '') === 'SUGERIR' ? 'warning' : 'neutral',
        };
    @endphp
    <div class="rm-signos__reading-state" data-tone="{{ $tono }}" x-show="hasEntered(@js($claveTarjeta)) && evaluationCurrent(@js($claveTarjeta)) && !hasCardError(@js($claveTarjeta))" aria-live="polite">
        @if($tono === 'neutral')<span class="rm-signos__reading-reference">Sin clasificación adicional aplicable</span>@endif
        @if(filled($resultado['rango_o_umbral'] ?? null))
            <span class="rm-signos__reading-reference">{{ match($resultado['fuente_evaluacion'] ?? null) {
                'OBJETIVO_MEDICO' => 'Objetivo individual',
                'UMBRAL_CRITICO' => 'Umbral de seguridad',
                default => 'Referencia utilizada'
            } }} · {{ $resultado['rango_o_umbral'] }}</span>
        @endif
    </div>
@endforeach
<span class="rm-signos__empty-reading" x-show="!hasEntered(@js($claveTarjeta))">Sin registrar</span>
