<div class="rm-clinical-form" x-data="rmClinicalCapture(@js($registroInicial))" aria-label="Registro de movilidad">
    <x-validation-errors />
    <p class="rm-clinical-form__note"><i class="ph-bold ph-lock" aria-hidden="true"></i> Fecha y hora automáticas al guardar. Registra lo observado y el apoyo utilizado.</p>
    @php
        $gruposMovilidad = [
            ['Movilidad y apoyo', 'ph-person-simple-walk', [
                ['marcha', 'movMarcha', 'Movilidad observada', \App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::MOVILIDAD_OBSERVADA],
                ['traslado', 'movTraslado', 'Modalidad de traslado', \App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::TRASLADOS],
                ['tipo_apoyo', 'movTipoApoyo', 'Nivel de ayuda', \App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::NIVELES_AYUDA],
            ]],
            ['Respuesta observada', 'ph-clipboard-text', [
                ['equilibrio', 'movEquilibrio', 'Equilibrio', \App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::EQUILIBRIOS],
                ['fatiga', 'movFatiga', 'Fatiga observada', \App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::FATIGAS],
                ['riesgo_caida', 'movRiesgoCaida', 'Riesgo de caída', \App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::RIESGOS_CAIDA],
            ]],
        ];
        $etiquetasMovilidad = ['SUPERVISION' => 'Supervisión', 'SILLA_RUEDAS' => 'Silla de ruedas', 'GRUA' => 'Grúa', 'AYUDA_UNA_PERSONA' => 'Ayuda de una persona', 'AYUDA_DOS_PERSONAS' => 'Ayuda de dos personas'];
    @endphp
    @foreach($gruposMovilidad as [$titulo, $icono, $campos])
        <x-ui.form-section :title="$titulo" :icon="$icono" :columns="2" class="rm-clinical-form__section">
            @foreach($campos as [$errorCampo, $modelo, $etiqueta, $opciones])
                <x-ui.field :label="$etiqueta" :for="'movilidad-'.$errorCampo" :error="$errorCampo" :required="$errorCampo === 'marcha'" :class="$errorCampo === 'marcha' ? 'rm-clinical-form__full' : ''">
                    <select id="movilidad-{{ $errorCampo }}" class="rm-select" wire:model="{{ $modelo }}" @if($errorCampo === 'marcha') required @endif aria-invalid="{{ $errors->has($errorCampo) ? 'true' : 'false' }}" @if($errors->has($errorCampo)) aria-describedby="movilidad-{{ $errorCampo }}-error" @endif>
                        <option value="">{{ $errorCampo === 'marcha' ? 'Seleccionar' : 'No registrado' }}</option>
                        @foreach($opciones as $valor)<option value="{{ $valor }}">{{ $etiquetasMovilidad[$valor] ?? mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') }}</option>@endforeach
                    </select>
                </x-ui.field>
            @endforeach
        </x-ui.form-section>
    @endforeach
    <x-ui.form-section title="Observaciones e incidencias" icon="ph-note-pencil" :columns="1" class="rm-clinical-form__section">
        <x-ui.field label="Detalles adicionales" for="movilidad-observacion" error="observacion" help="Describe el dispositivo utilizado y la respuesta del residente, si corresponde.">
            <textarea id="movilidad-observacion" class="rm-textarea" wire:model="movObservacion" rows="3" maxlength="5000" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" aria-describedby="movilidad-observacion-help @error('observacion') movilidad-observacion-error @enderror"></textarea>
        </x-ui.field>
        <p class="rm-clinical-form__note">Si ocurrió un incidente, utiliza su registro específico. Guardar movilidad no registra un incidente.</p>
        @can('incidentes.crear')<a class="rm-clinical-form__history-link" href="{{ route('admin.enfermeria.incidentes') }}" target="_blank" rel="noopener">Registrar incidente <span class="sr-only">en otra pestaña</span><i class="ph-bold ph-arrow-square-out" aria-hidden="true"></i></a>@endcan
    </x-ui.form-section>
</div>
