@php
    $capturaIngesta = ['meal' => $ingestaTipoComida, 'percentage' => $ingestaPorcentaje,
        'tolerance' => $ingestaTolerancia, 'swallowing' => $ingestaDificultadDeglucion,
        'liquids' => $ingestaRegistrarLiquidos, 'quantity' => $ingestaCantidadMl, 'observation' => $ingestaObservacion];
@endphp
<div class="rm-clinical-form rm-ingesta" x-data="rmIngestaRegistro(@js($ingestaHistorial), @js($capturaIngesta), @js($ingestaMomento), @js(config('enfermeria.porcentaje_baja_ingesta', 50)))" :data-graph-open="trendOpen ? 'true' : 'false'" aria-label="Registro de ingesta">
    <x-validation-errors class="rm-clinical-form__errors" />
    <p class="rm-clinical-form__note rm-ingesta__timestamp"><i class="ph-bold ph-clock" aria-hidden="true"></i> Registro: {{ \Carbon\Carbon::parse($ingestaMomento)->format('d/m/Y H:i') }} · La hora definitiva se asigna al guardar.</p>
    <div class="rm-ingesta__layout">
        <div class="rm-ingesta__column">
            <x-ui.form-section title="Tipo de comida" icon="ph-bowl-food" :columns="1" class="rm-clinical-form__section">
                <p class="rm-help" id="ingesta-comida-help">Selecciona la comida observada. Obligatorio.</p>
                <div class="rm-ingesta__choices rm-ingesta__choices--meals" role="group" aria-label="Tipo de comida" aria-describedby="ingesta-comida-help @error('tipo_comida') ingesta-comida-error @enderror">
                    @foreach(['DESAYUNO' => ['Desayuno', 'ph-sun-horizon'], 'MEDIA_MANANA' => ['Media mañana', 'ph-coffee'], 'ALMUERZO' => ['Almuerzo', 'ph-bowl-food'], 'MERIENDA' => ['Merienda', 'ph-cookie'], 'CENA' => ['Cena', 'ph-moon'], 'COLACION' => ['Colación', 'ph-apple-logo']] as $value => [$label, $icon])
                        <x-ui.clinical-capture-choice field="meal" :value="$value" :label="$label" :icon="$icon" :data-meal="$value" />
                    @endforeach
                </div>
                @error('tipo_comida')<p id="ingesta-comida-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
            </x-ui.form-section>
            <x-ui.form-section title="Tolerancia" icon="ph-first-aid" :columns="1" class="rm-clinical-form__section">
                <div class="rm-ingesta__choices" role="group" aria-label="Tolerancia" @error('tolerancia') aria-describedby="ingesta-tolerancia-error" @enderror>
                    @foreach(['BUENA' => 'Buena', 'REGULAR' => 'Regular', 'MALA' => 'Mala', 'NAUSEAS' => 'Náuseas', 'VOMITO' => 'Vómito', '' => 'Sin registrar'] as $value => $label)
                        <x-ui.clinical-capture-choice field="tolerance" :value="$value" :label="$label" data-tone="{{ match($value) { 'REGULAR' => 'amber', 'MALA', 'VOMITO' => 'coral', 'NAUSEAS' => 'violet', '' => 'neutral', default => 'sage' } }}" />
                    @endforeach
                </div>
                @error('tolerancia')<p id="ingesta-tolerancia-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
            </x-ui.form-section>
            @can('registros_hidratacion.crear')
                <x-ui.form-section title="Líquidos consumidos" icon="ph-drop" :columns="1" class="rm-clinical-form__section">
                    <div class="rm-ingesta__choices" role="group" aria-label="Registrar aporte de líquidos">
                        <x-ui.clinical-capture-choice field="liquids" :value="false" label="No registrar aporte" />
                        <x-ui.clinical-capture-choice field="liquids" :value="true" label="Registrar aporte" />
                    </div>
                    <div x-show="values.liquids" x-cloak>
                        <x-ui.field label="Cantidad consumida" for="ingesta-liquidos" error="cantidad_ml" help="Se guardará también un registro de hidratación.">
                            <div class="rm-clinical-form__unit"><input id="ingesta-liquidos" class="rm-input" type="text" inputmode="decimal" x-model="values.quantity" @input="setValue('quantity', $event.target.value)" :disabled="!values.liquids" aria-describedby="ingesta-liquidos-help @error('cantidad_ml') ingesta-liquidos-error @enderror" aria-invalid="{{ $errors->has('cantidad_ml') ? 'true' : 'false' }}"><span>mL</span></div>
                        </x-ui.field>
                    </div>
                </x-ui.form-section>
            @endcan
        </div>
        <div class="rm-ingesta__column">
            <x-ui.form-section title="Porcentaje consumido" icon="ph-chart-pie-slice" :columns="1" class="rm-clinical-form__section rm-ingesta__percentage">
                <div class="rm-ingesta__value" aria-live="polite"><strong x-text="current() === null ? '—' : current()"></strong><span>%</span><small>De la comida ofrecida</small></div>
                <label class="sr-only" for="ingesta-slider">Ajustar porcentaje consumido</label>
                <input id="ingesta-slider" type="range" min="0" max="100" step="0.01" :value="current() ?? 0" :style="'--intake-fill:' + (current() ?? 0) + '%'" :aria-valuetext="current() === null ? 'Sin registrar; ajusta para capturar' : current() + ' por ciento'" @input="setValue('percentage', $event.target.value)">
                <div class="rm-ingesta__shortcuts" role="group" aria-label="Porcentajes frecuentes">
                    @foreach([0, 25, 50, 75, 100] as $value)<button class="rm-clinical-choice" type="button" :aria-pressed="current() === {{ $value }}" @click="setValue('percentage', '{{ $value }}')">{{ $value }} %</button>@endforeach
                </div>
                <x-ui.field label="Valor exacto" for="ingesta-porcentaje" error="porcentaje_consumido" help="Opcional · 0 a 100 %, hasta dos decimales. Vacío significa sin registrar.">
                    <div class="rm-clinical-form__unit"><input id="ingesta-porcentaje" class="rm-input" type="text" inputmode="decimal" x-model="values.percentage" @input="setValue('percentage', $event.target.value)" :aria-invalid="percentageInvalid() || {{ $errors->has('porcentaje_consumido') ? 'true' : 'false' }}" aria-describedby="ingesta-porcentaje-help ingesta-porcentaje-local-error @error('porcentaje_consumido') ingesta-porcentaje-error @enderror"><span>%</span></div>
                    <p id="ingesta-porcentaje-local-error" class="rm-error" x-show="percentageInvalid()" x-cloak>Introduce un número entre 0 y 100, con hasta dos decimales.</p>
                    <button type="button" class="rm-ingesta__clear-value" @click="setValue('percentage', '')">Dejar sin registrar</button>
                </x-ui.field>
                <p class="rm-ingesta__notice" x-show="lowPreview()" x-cloak role="status"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> Vista previa: por debajo del umbral institucional (<span x-text="threshold"></span> %). Al guardar se evaluará la alerta de baja ingesta.</p>
            </x-ui.form-section>
            <x-ui.form-section title="Deglución" icon="ph-user" :columns="1" class="rm-clinical-form__section">
                <p class="rm-help" id="ingesta-deglucion-help">Indica lo observado. Obligatorio; no implica un diagnóstico.</p>
                <div class="rm-ingesta__choices rm-ingesta__choices--stack" role="group" aria-label="Deglución" aria-describedby="ingesta-deglucion-help @error('dificultad_deglucion') ingesta-deglucion-error @enderror">
                    <x-ui.clinical-capture-choice field="swallowing" :value="false" label="Sin dificultad observada" icon="ph-check-circle" />
                    <x-ui.clinical-capture-choice field="swallowing" :value="true" label="Se observó dificultad para deglutir" icon="ph-info" data-tone="amber" />
                </div>
                @error('dificultad_deglucion')<p id="ingesta-deglucion-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
            </x-ui.form-section>
        </div>
        <aside class="rm-ingesta__column rm-ingesta__continuity" aria-label="Continuidad de ingesta">
            <x-ui.form-section title="Continuidad" icon="ph-clock-counter-clockwise" :columns="1" class="rm-clinical-form__section">
                @can('registros_ingesta.ver')
                    <div class="rm-ingesta__previous"><span>Último registro guardado</span><strong x-text="previous()?.meal ?? 'Sin registros anteriores'"></strong><time x-text="previous()?.date ?? ''"></time><b x-text="previous()?.value === null || !previous() ? 'Porcentaje sin registrar' : previous().value + ' %'"></b><span x-text="previous()?.tolerance ?? ''"></span><span x-text="previous()?.swallowing ?? ''"></span></div>
                @else
                    <p class="rm-help">Sin permiso para consultar las ingestas anteriores.</p>
                @endcan
                <div class="rm-ingesta__comparison" aria-live="polite"><span>Actual · Sin guardar</span><strong x-text="current() === null ? 'Sin registrar' : current() + ' %'"></strong><span>Diferencia descriptiva</span><b x-text="comparison()"></b></div>
                @can('registros_ingesta.ver')
                    <button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="ingesta-grafica-popup" :aria-expanded="trendOpen"><i class="ph-bold ph-chart-line" aria-hidden="true"></i> Ver evolución</button>
                    <p class="rm-help">Últimos {{ count($ingestaHistorial) }} registros. La vista previa no modifica la historia.</p>
                    <details class="rm-ingesta__recent">
                        <summary>Últimos registros</summary>
                        <p x-show="!history.length">Sin registros anteriores.</p>
                        <ol><template x-for="(row, index) in [...history].reverse().slice(0, 3)" :key="row.code || index"><li><time x-text="row.date"></time><strong x-text="row.meal"></strong><span x-text="row.value === null ? 'Porcentaje sin registrar' : row.value + ' %'"></span></li></template></ol>
                    </details>
                @endcan
            </x-ui.form-section>
        </aside>
    </div>
    <x-ui.form-section title="Observaciones" icon="ph-note-pencil" :columns="1" class="rm-clinical-form__section">
        <x-ui.field label="Asistencia y detalles observados" for="ingesta-observacion" error="observacion" help="Describe la ayuda para comer y otras dificultades, si las hubo. Opcional · máximo 5000 caracteres.">
            <textarea id="ingesta-observacion" class="rm-textarea" x-model="values.observation" @input="setValue('observation', $event.target.value)" rows="2" maxlength="5000" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" aria-describedby="ingesta-observacion-help @error('observacion') ingesta-observacion-error @enderror"></textarea>
        </x-ui.field>
    </x-ui.form-section>
    @can('registros_ingesta.ver')@include('livewire.cuidados.partials.mis-residentes-ingesta-grafica')@endcan
</div>
