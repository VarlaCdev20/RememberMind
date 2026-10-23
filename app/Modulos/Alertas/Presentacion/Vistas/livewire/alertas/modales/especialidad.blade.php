@if($modalEspecialidad && $detalle && $definicionEspecialidad)
<x-ui.modal-livewire class="rm-alert-dialog" :title="$definicionEspecialidad['titulo']" subtitle="Registro profesional en el expediente del residente" id="modalRegistroEspecialidad" wire:model="modalEspecialidad" maxWidth="lg" badge="Alertas · continuidad por especialidad" closeMethod="solicitarCerrarModales" alpineBusy="guardados > 0">
    <div class="space-y-4">
        <x-ui.callout variant="info" :title="$perfil['titulo']">{{ $perfil['descripcion'] }}<p>Este formulario registra lo efectivamente observado o realizado en tu especialidad. No confirma una solicitud, derivación ni cambio de estado por abrirlo.</p></x-ui.callout>
        <section class="rm-alert-detail__description"><p class="rm-help">Residente</p><h3>{{ $detalle->residente?->nombre_completo }}</h3>@unless($preview)<p>Profesional que registra: {{ \App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::responsable(auth()->user()->personal) }}</p>@endunless @if($areaEspecialidad)<p>Área de la jornada: {{ $areaEspecialidad->nombre }}</p>@endif<p class="rm-help">Se conserva el registro original y se añade un evento al historial de la alerta. Esta valoración no cambia por sí sola su estado.</p></section>
        @if($preview)<x-ui.callout variant="info" title="Consulta de campos de la especialidad">Este modo muestra el formulario correspondiente al rol. No permite registrar ni atribuye al usuario una competencia profesional.</x-ui.callout>
        @elseif($errorContextoEspecialidad)<x-ui.callout variant="warning" title="Falta contexto profesional para registrar">{{ $errorContextoEspecialidad }} Solicita a coordinación que revise la asignación de área de tu jornada.</x-ui.callout>
        @elseif(!$puedeGuardarEspecialidad)<x-ui.callout variant="info" title="Formulario en consulta">El registro requiere permiso de la especialidad, personal activo, contexto institucional y un residente admitido.</x-ui.callout>@endif
        @error('capturaEspecialidad')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
        <fieldset @disabled(!$puedeGuardarEspecialidad) class="space-y-4">
            @if($definicionEspecialidad['requiereAtencion'])
            <x-ui.form-section title="Atención que respalda el registro" :columns="1" icon="ph-folder-user">
                <fieldset class="rm-field"><legend class="rm-label rm-label-required">Selecciona una atención propia o registra una nueva</legend><div class="rm-alert-choice-grid">
                    @foreach($atencionesEspecialidad as $atencion)<x-ui.choice-card name="atencionEspecialidad" :value="$atencion->getKey()" :label="$atencion->getRawOriginal('tipo_atencion')" :description="($atencion->fecha_hora?->format('d/m/Y H:i') ?: 'Fecha no registrada').' · '.$atencion->estado" icon="ph-folder-user" wire:model.live="atencionEspecialidad" />@endforeach
                    @if(auth()->user()->can('atenciones.crear') || $preview)<x-ui.choice-card name="atencionEspecialidad" value="NUEVA" label="Registrar una nueva atención" description="Se crea junto con la valoración, con tu autoría y área vigente" icon="ph-plus" wire:model.live="atencionEspecialidad" />@endif
                </div>@error('atencionEspecialidad')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</fieldset>
                @if($atencionEspecialidad === 'NUEVA' || !$puedeGuardarEspecialidad)
                    <x-ui.field label="Tipo de atención realizada" for="especialidad-tipo-atencion" required error="tipoAtencionEspecialidad"><input id="especialidad-tipo-atencion" class="rm-input" wire:model="tipoAtencionEspecialidad" maxlength="60" aria-invalid="{{ $errors->has('tipoAtencionEspecialidad') ? 'true' : 'false' }}" aria-describedby="especialidad-tipo-atencion-error"></x-ui.field>
                    <x-ui.field label="Motivo de la atención" for="especialidad-motivo-atencion" error="motivoAtencionEspecialidad"><textarea id="especialidad-motivo-atencion" class="rm-textarea" wire:model="motivoAtencionEspecialidad" rows="2" maxlength="10000"></textarea></x-ui.field>
                @endif
                @if($atencionesEspecialidad->isEmpty() && !$preview)<p class="rm-help">No hay atenciones propias disponibles en esta consulta. Registra una nueva con el tipo de atención efectivamente realizada.</p>@endif
            </x-ui.form-section>
            @endif
            <p class="rm-help">Registra únicamente lo observado o valorado. Los campos sin información quedan sin registrar.</p>
            @foreach($definicionEspecialidad['secciones'] as $tituloSeccion=>$camposSeccion)
            <x-ui.form-section :title="$tituloSeccion" :columns="2" :step="$loop->iteration" icon="ph-notebook">
                @foreach($camposSeccion as $campo)
                    @php([$etiqueta,$tipoCampo] = $definicionEspecialidad['campos'][$campo])
                    @if($tipoCampo === 'booleano')
                        <fieldset class="rm-field"><legend class="rm-label">{{ $etiqueta }}</legend><div class="rm-alert-chip-group"><div>@foreach([''=>'Sin registrar','1'=>'Sí','0'=>'No'] as $valor=>$opcion)<label class="rm-alert-radio"><input type="radio" name="especialidad-{{ $campo }}" wire:model="capturaEspecialidad.{{ $campo }}" value="{{ $valor }}"><span>{{ $opcion }}</span></label>@endforeach</div></div>@error('capturaEspecialidad.'.$campo)<p class="rm-error" role="alert">{{ $message }}</p>@enderror</fieldset>
                    @else
                        <x-ui.field :label="$etiqueta" :for="'especialidad-'.$campo" :error="'capturaEspecialidad.'.$campo" :required="in_array($campo, $definicionEspecialidad['obligatorios'], true)" :class="$tipoCampo === 'texto' ? 'md:col-span-2' : ''">
                            @if($tipoCampo === 'texto')<textarea id="especialidad-{{ $campo }}" class="rm-textarea" wire:model="capturaEspecialidad.{{ $campo }}" rows="3" maxlength="10000" aria-invalid="{{ $errors->has('capturaEspecialidad.'.$campo) ? 'true' : 'false' }}" aria-describedby="especialidad-{{ $campo }}-error"></textarea>
                            @elseif($tipoCampo === 'decimal')<input id="especialidad-{{ $campo }}" type="number" inputmode="decimal" step="0.01" min="0" class="rm-input" wire:model="capturaEspecialidad.{{ $campo }}" aria-invalid="{{ $errors->has('capturaEspecialidad.'.$campo) ? 'true' : 'false' }}" aria-describedby="especialidad-{{ $campo }}-error">
                            @else<input id="especialidad-{{ $campo }}" type="text" class="rm-input" wire:model="capturaEspecialidad.{{ $campo }}" maxlength="{{ $tipoCampo }}" aria-invalid="{{ $errors->has('capturaEspecialidad.'.$campo) ? 'true' : 'false' }}" aria-describedby="especialidad-{{ $campo }}-error">@endif
                        </x-ui.field>
                    @endif
                @endforeach
            </x-ui.form-section>
            @endforeach
        </fieldset>
    </div>
    <x-slot name="footer"><div class="w-full space-y-3">@include('livewire.alertas.modales.confirmar-descarte')<div class="flex flex-wrap justify-end gap-3"><button type="button" class="rm-btn rm-btn-secondary" wire:click="solicitarCerrarModales">{{ $puedeGuardarEspecialidad ? 'Cancelar' : 'Volver a la bandeja' }}</button>@if($puedeGuardarEspecialidad)<x-ui.action-button wire:click="guardarRegistroEspecialidad" loading="guardarRegistroEspecialidad" icono="ph-check">Guardar registro profesional</x-ui.action-button>@else<button type="button" class="rm-btn rm-btn-primary" disabled><i class="ph-bold ph-lock-simple" aria-hidden="true"></i>Guardado bloqueado · solo lectura</button>@endif</div></div></x-slot>
</x-ui.modal-livewire>
@endif
