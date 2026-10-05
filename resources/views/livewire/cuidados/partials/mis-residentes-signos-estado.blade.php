@foreach($evaluaciones as $resultado)
    @php
        $nivel = $resultado['severidad'] ?? null;
        $tono = match ($nivel) {
            'CRITICO' => 'danger',
            'ALTO', 'ADVERTENCIA' => 'warning',
            'NORMAL', 'OBJETIVO_PERSONALIZADO' => 'success',
            default => ($resultado['comportamiento_alerta'] ?? '') === 'SUGERIR' ? 'warning' : 'neutral',
        };
        $etiqueta = match ($nivel) {
            'CRITICO' => 'Crítico',
            'ALTO' => 'Alto',
            'ADVERTENCIA' => 'Advertencia',
            'NORMAL' => 'Normal',
            'OBJETIVO_PERSONALIZADO' => 'En objetivo',
            default => ($resultado['comportamiento_alerta'] ?? '') === 'SUGERIR' ? 'Revisar contexto' : 'Sin clasificación definida',
        };
    @endphp
    <div class="rm-signos__reading-state" data-tone="{{ $tono }}" aria-live="polite">
        <span class="rm-signos__reading-badge">
            <i class="ph-bold {{ $tono === 'success' ? 'ph-check-circle' : ($tono === 'neutral' ? 'ph-info' : 'ph-warning-circle') }}" aria-hidden="true"></i>
            {{ $etiqueta }}
        </span>
        @if(filled($resultado['rango_o_umbral'] ?? null))
            <span class="rm-signos__reading-reference">{{ match($resultado['fuente_evaluacion'] ?? null) {
                'OBJETIVO_MEDICO' => 'Objetivo individual',
                'UMBRAL_CRITICO' => 'Umbral de seguridad',
                default => 'Referencia utilizada'
            } }} · {{ $resultado['rango_o_umbral'] }}</span>
        @endif
        <span class="rm-signos__reading-message">{{ $resultado['explicacion'] }}</span>
        @if(($resultado['comportamiento_alerta'] ?? '') === 'AUTOMATICA_AL_CONFIRMAR')
            <small>Al confirmar el registro se generará la alerta correspondiente.</small>
        @endif
    </div>
@endforeach
