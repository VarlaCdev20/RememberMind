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
        @if($tono === 'neutral')<span class="rm-signos__reading-reference">{{ $claveTarjeta === 'sat' ? 'Clasificación pendiente · Sin objetivo médico vigente' : 'Sin clasificación adicional aplicable' }}</span>@endif
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
<button type="button" class="rm-signos__measurement-chart" @click="openTrend(@js($claveTarjeta), $event.currentTarget)" :aria-expanded="trendOpen && active === @js($claveTarjeta)" aria-controls="signos-grafica-popup" aria-haspopup="dialog" :aria-label="'Ver gráfica de ' + meta[@js($claveTarjeta)].label"><i class="ph-bold ph-chart-line" aria-hidden="true"></i> Ver gráfica</button>
