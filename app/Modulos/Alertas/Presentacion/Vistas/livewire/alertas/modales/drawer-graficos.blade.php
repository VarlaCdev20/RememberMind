@if($drawerGrafico && $adultoDrawer)
@php
    $mediciones = $signosDrawer->sortBy('fecha_hora')->values();
    $campos = ['presion_sistolica'=>['Sistólica','mmHg'], 'presion_diastolica'=>['Diastólica','mmHg'], 'frecuencia_cardiaca'=>['Frecuencia cardíaca','lpm'], 'frecuencia_respiratoria'=>['Frecuencia respiratoria','rpm'], 'saturacion_oxigeno'=>['Saturación de oxígeno','%'], 'temperatura'=>['Temperatura','°C'], 'glucemia'=>['Glucemia','mg/dL']];
    $puntos = $mediciones->map(function($s) use ($campos) {
        $punto = ['fecha'=>$s->fecha_hora?->toIso8601String()];
        foreach ($campos as $campo=>$definicion) $punto[$campo] = $s->$campo !== null && is_numeric($s->$campo) && is_finite((float)$s->$campo) ? (float)$s->$campo : null;
        return $punto;
    })->all();
@endphp
<x-ui.drawer-livewire class="rm-alert-dialog" wire:model="drawerGrafico" title="Mediciones del residente" :subtitle="$adultoDrawer->nombre_completo" badge="Alertas · consulta clínica autorizada" icon="ph-chart-line" size="xl" closeMethod="cerrarDrawer" :dismissOnBackdrop="false">
    <div class="rm-alert-measurements">
        <section class="rm-alert-detail__description"><h3>{{ $adultoDrawer->nombre_completo }}</h3><p>{{ $adultoDrawer->ubicacion_formateada }}</p><p class="rm-help">Hasta 10 mediciones registradas, ordenadas por fecha. Cada punto corresponde a un registro; no se interpolan valores ni se asignan rangos clínicos.</p></section>
        @if($mediciones->isEmpty())
            <x-ui.empty-state icono="ph-chart-line" titulo="Sin mediciones registradas para esta consulta" texto="No hay signos vitales disponibles en este historial. No se generan valores de ejemplo." />
        @else
            <section class="rm-chart-card" wire:key="signos-alerta-{{ $adultoDrawer->getKey() }}" x-data="rmSignosAlerta(@js($puntos), @js('alertas-signos-'.$adultoDrawer->getKey()), @js(config('app.timezone')))" aria-label="Gráfica de mediciones registradas">
                <div class="rm-alert-measurements__variables" role="group" aria-label="Medición que quieres consultar">
                    @foreach(['presion'=>'Presión arterial','pulso'=>'Frecuencia cardíaca','respiracion'=>'Respiración','saturacion'=>'Oxígeno','temperatura'=>'Temperatura','glucemia'=>'Glucemia'] as $clave=>$texto)
                        <button type="button" class="rm-btn rm-btn-secondary" @click="variable = @js($clave)" :aria-pressed="variable === @js($clave)">{{ $texto }}</button>
                    @endforeach
                </div>
                <p x-show="sinMediciones" x-cloak role="status" class="rm-callout">Esta variable no tiene valores registrados en los controles mostrados. Selecciona otra o revisa el historial inferior.</p>
                <p x-show="error" x-cloak role="alert" class="rm-callout">No se pudo mostrar la gráfica. Los valores originales siguen disponibles en el historial inferior.</p>
                <div class="rm-chart-body is-md" x-show="!sinMediciones && !error"><canvas x-ref="canvas" role="img" aria-label="Valores de la medición seleccionada; fechas y valores disponibles en el historial inferior"></canvas></div>
            </section>
            <section aria-label="Valores originales de las mediciones" class="rm-alert-measurements__history"><h3>Historial de mediciones</h3>
                @foreach($mediciones as $medicion)
                    <article class="rm-alert-detail__description"><h4><time datetime="{{ $medicion->fecha_hora?->toIso8601String() }}">{{ $medicion->fecha_hora?->format('d/m/Y H:i') ?: 'Fecha no registrada' }}</time></h4>
                        <dl class="rm-alert-case__facts">@foreach($campos as $campo=>[$etiqueta,$unidad])<div><dt>{{ $etiqueta }}</dt><dd>{{ $medicion->$campo !== null ? $medicion->$campo.' '.$unidad : 'Sin valor registrado' }}</dd></div>@endforeach</dl>
                    </article>
                @endforeach
            </section>
        @endif
    </div>
    <x-slot:footer><x-ui.action-button variant="secondary" wire:click="cerrarDrawer" icono="ph-arrow-left">Volver a alertas</x-ui.action-button></x-slot:footer>
</x-ui.drawer-livewire>
@endif
