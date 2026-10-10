@php($capturaHidratacion = ['quantity' => $hidratacionCantidad, 'liquid' => $hidratacionTipo, 'tolerance' => $hidratacionTolerancia, 'observation' => $hidratacionObservacion])
<div class="rm-clinical-form rm-hidratacion" x-data="rmHidratacionRegistro(@js($hidratacionHistorial), @js($capturaHidratacion), @js($hidratacionMomento))" :data-graph-open="trendOpen ? 'true' : 'false'" aria-label="Registro de hidratación">
    @if($errors->count() > ($errors->has('hidratacion_guardado') ? 1 : 0))<x-validation-errors class="rm-clinical-form__errors" />@endif
    @error('hidratacion_guardado')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
    <div class="rm-hidratacion__timestamp">
        <i class="ph-bold ph-clock" aria-hidden="true"></i><div><strong>Momento del registro</strong><time>{{ \Carbon\Carbon::parse($hidratacionMomento)->format('d/m/Y · H:i') }}</time></div>
        <span><i class="ph-bold ph-lock" aria-hidden="true"></i> Fecha y hora automáticas · No editables<small>La hora definitiva se asigna al guardar.</small></span>
    </div>
    <div class="rm-hidratacion__layout">
        <div class="rm-hidratacion__column">
            <x-ui.form-section title="Volumen del aporte" icon="ph-drop" :columns="1" class="rm-clinical-form__section">
                <p class="rm-help">Registra el volumen del aporte de líquido realmente observado.</p>
                <div class="rm-hidratacion__value" aria-live="polite"><strong x-text="current() === null ? '—' : current()"></strong><span>mL</span><small>Sin guardar</small></div>
                <x-ui.field label="Volumen en mililitros" for="hidratacion-volumen" error="cantidad_ml" :required="true" help="Entre 1 y 10 000 mL, sin decimales.">
                    <div class="rm-hidratacion__input-unit"><input id="hidratacion-volumen" class="rm-input" type="number" min="1" max="10000" step="1" inputmode="numeric" x-model="values.quantity" @input="setValue('quantity', $event.target.value)" :aria-invalid="quantityInvalid() || @js($errors->has('cantidad_ml'))" aria-describedby="hidratacion-volumen-help hidratacion-volumen-status @error('cantidad_ml') hidratacion-volumen-error @enderror"><span aria-hidden="true">mL</span></div>
                </x-ui.field>
                <p id="hidratacion-volumen-status" class="rm-hidratacion__status" :data-invalid="quantityInvalid()" aria-live="polite" x-text="quantityInvalid() ? 'Ingresa un volumen válido.' : (current() === null ? 'Sin registrar' : 'Volumen válido')"></p>
                <div class="rm-hidratacion__shortcuts" role="group" aria-label="Volúmenes frecuentes">
                    @foreach([100,150,200,250,350,500] as $volume)
                        <button type="button" @click="setValue('quantity', '{{ $volume }}')" :aria-pressed="current() === {{ $volume }}" aria-label="Establecer volumen en {{ $volume }} mililitros">{{ $volume }}<small> mL</small></button>
                    @endforeach
                </div>
            </x-ui.form-section>
            <x-ui.form-section title="Tipo de líquido" icon="ph-coffee" :columns="1" class="rm-clinical-form__section">
                <x-ui.field label="Líquido observado" for="hidratacion-tipo" error="tipo_liquido" help="Opcional · hasta 60 caracteres.">
                    <input id="hidratacion-tipo" class="rm-input" type="text" maxlength="60" placeholder="Ej. Agua, infusión…" x-model="values.liquid" @input="setValue('liquid', $event.target.value)" aria-invalid="{{ $errors->has('tipo_liquido') ? 'true' : 'false' }}" aria-describedby="hidratacion-tipo-help @error('tipo_liquido') hidratacion-tipo-error @enderror">
                </x-ui.field>
            </x-ui.form-section>
            <fieldset class="rm-clinical-form__section rm-hidratacion__tolerance" @error('tolerancia') aria-describedby="hidratacion-tolerancia-error" @enderror>
                <legend><i class="ph-bold ph-first-aid" aria-hidden="true"></i> Tolerancia</legend>
                <p class="rm-help">Selecciona lo observado durante este aporte. Opcional.</p>
                <div class="rm-hidratacion__radios">
                    @foreach(['ADECUADA' => ['Adecuada', 'success', 'ph-check-circle'], 'PARCIAL' => ['Parcial', 'warning', 'ph-circle-half'], 'RECHAZO' => ['Rechazo', 'danger', 'ph-prohibit'], 'NAUSEAS' => ['Náuseas', 'warning', 'ph-warning-circle'], '' => ['Sin registrar', 'neutral', 'ph-minus-circle']] as $value => [$label, $tone, $icon])
                        <label data-tone="{{ $tone }}"><input type="radio" name="hidratacion-tolerancia" value="{{ $value }}" x-model="values.tolerance" @change="setValue('tolerance', $event.target.value)" aria-invalid="{{ $errors->has('tolerancia') ? 'true' : 'false' }}"><span><i class="ph-bold {{ $icon }}" aria-hidden="true"></i>{{ $label }}</span></label>
                    @endforeach
                </div>
                @error('tolerancia')<p id="hidratacion-tolerancia-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
            </fieldset>
            <x-ui.form-section title="Observación" icon="ph-note-pencil" :columns="1" class="rm-clinical-form__section">
                <x-ui.field label="Detalles observados" for="hidratacion-observacion" error="observacion" help="Describe detalles relevantes durante este aporte. Opcional · máximo 5000 caracteres.">
                    <textarea id="hidratacion-observacion" class="rm-textarea" rows="3" maxlength="5000" x-model="values.observation" @input="setValue('observation', $event.target.value)" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" aria-describedby="hidratacion-observacion-help @error('observacion') hidratacion-observacion-error @enderror"></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </div>
        <aside class="rm-hidratacion__column" aria-label="Continuidad de hidratación">
            <x-ui.form-section title="Continuidad del turno" icon="ph-clock-counter-clockwise" :columns="1" class="rm-clinical-form__section">
                <p class="rm-help">Aportes registrados durante la jornada actual.</p>
                @can('registros_hidratacion.ver')
                    <div class="rm-hidratacion__total"><span>Acumulado registrado</span><strong>{{ rtrim(rtrim(number_format($hidratacionContinuidad['acumulado'] ?? 0, 2, ',', '.'), '0'), ',') }} <small>mL</small></strong><span>{{ $hidratacionContinuidad['aportes'] ?? 0 }} {{ ($hidratacionContinuidad['aportes'] ?? 0) === 1 ? 'aporte' : 'aportes' }}</span></div>
                    <div class="rm-hidratacion__previous"><h5>Último aporte</h5><strong x-text="previous() ? previous().value + ' mL' : 'Sin registros anteriores'"></strong><time x-text="previous()?.date ?? ''"></time><span x-text="previous()?.liquid ?? ''"></span><span x-text="previous() ? 'Tolerancia: ' + previous().tolerance : ''"></span></div>
                @else
                    <p class="rm-help">Sin permiso para consultar los aportes anteriores.</p>
                @endcan
                <div class="rm-hidratacion__comparison" aria-live="polite"><h5>Registro actual · Sin guardar</h5><strong x-text="current() === null ? '— mL' : current() + ' mL'"></strong><span x-text="values.liquid.trim() || 'Tipo sin registrar'"></span><span x-text="toleranceLabel(values.tolerance)"></span><small>Diferencia descriptiva</small><b x-text="comparison()"></b></div>
            </x-ui.form-section>
            @can('registros_hidratacion.ver')
                <x-ui.form-section title="Historial reciente" icon="ph-list-bullets" :columns="1" class="rm-clinical-form__section">
                    <p class="rm-help" x-show="!history.length">Sin registros anteriores.</p>
                    <ol class="rm-hidratacion__recent"><template x-for="(row, index) in [...history].reverse()" :key="row.code || index"><li><time x-text="row.date"></time><strong x-text="row.value + ' mL'"></strong><span x-text="row.liquid"></span><small x-text="row.tolerance"></small></li></template></ol>
                    <button class="rm-btn-secondary rm-hidratacion__evolution" type="button" @click="openTrend($event.currentTarget)" aria-controls="hidratacion-grafica-popup" :aria-expanded="trendOpen"><i class="ph-bold ph-chart-line-up" aria-hidden="true"></i> Ver evolución</button>
                </x-ui.form-section>
            @endcan
        </aside>
    </div>
    @can('registros_hidratacion.ver')@include('livewire.cuidados.partials.mis-residentes-hidratacion-grafica')@endcan
</div>
