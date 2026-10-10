<div class="rm-modal-shell rm-admin-wizard-shell fixed inset-0 flex items-center justify-center p-4">
 <div x-data="{ isDirty: false }" x-trap.inert.noscroll="true" x-effect="if ($wire.guardadoExitoso) isDirty = false" x-on:input="isDirty = true" x-on:change="isDirty = true" role="dialog" aria-modal="true" aria-labelledby="admin-preadmission-title" class="rm-modal-panel rm-admin-wizard relative mx-auto flex w-full max-w-6xl flex-col overflow-hidden">
 <div id="rm-wizard-control-layer"></div>
 <!-- Header -->
 <div class="rm-modal-header rm-admin-wizard__header flex items-start justify-between">
  <div class="flex items-center gap-3">
  <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-inner border border-boton-acento/20">
   <i class="ph-fill ph-file-plus text-xl"></i>
  </div>
  <div>
   <h3 id="admin-preadmission-title" class="rm-modal-panel__title leading-tight">Registrar Preadmisión</h3>
   <p class="text-xs font-semibold text-apoyo mt-0.5">Registre los datos de la persona solicitante y su responsable. El residente se creará únicamente al formalizar la admisión.</p>
  </div>
  </div>
  <button type="button" @click="
  if (isDirty) {
   Swal.fire({
   title: '¿Salir sin guardar?',
   text: 'Hay cambios sin guardar en el formulario. Si sale, perdera todos los datos.',
   icon: 'warning',
   showCancelButton: true,
   confirmButtonColor: 'var(--estado-peligro)',
   cancelButtonColor: 'var(--boton-acento)',
   confirmButtonText: 'Si, salir',
   cancelButtonText: 'Permanecer',
   background: 'var(--fondo-card)',
   color: 'var(--texto-principal)'
   }).then((result) => {
   if (result.isConfirmed) {
    if (window.Livewire?.navigate) {
    window.Livewire.navigate('{{ route('admin.admisiones.preadmisiones') }}');
    } else {
    window.location.href = '{{ route('admin.admisiones.preadmisiones') }}';
    }
   }
   });
  } else {
   if (window.Livewire?.navigate) {
   window.Livewire.navigate('{{ route('admin.admisiones.preadmisiones') }}');
   } else {
   window.location.href = '{{ route('admin.admisiones.preadmisiones') }}';
   }
  }
  " class="rm-btn-icon rm-modal-panel__close" aria-label="Cerrar formulario de preadmisión">
  <i class="ph-bold ph-x text-xl"></i>
  </button>
 </div>

 @php
  $pasoActualsLista = [
  1 => 'Identidad',
  2 => 'Dirección',
  3 => 'Familiar',
  4 => 'Caso',
  5 => 'Documentos'
  ];

  // Errores reales de validación por paso
  $pasosErrores = [
  1 => $errors->has('nombres') || $errors->has('ap_paterno') || $errors->has('ap_materno') || $errors->has('ci') || $errors->has('expedicion_ci') || $errors->has('fecha_nac') || $errors->has('genero') || $errors->has('estado_civil') || $errors->has('telefono') || $errors->has('celular'),
  2 => $errors->has('departamento_residencia') || $errors->has('ciudad_municipio') || $errors->has('zona') || $errors->has('calle') || $errors->has('direccion_referencia'),
  3 => $errors->has('familiar_nombres') || $errors->has('familiar_ap_paterno') || $errors->has('familiar_ap_materno') || $errors->has('familiar_ci') || $errors->has('familiar_parentesco') || $errors->has('familiar_celular') || $errors->has('familiar_correo') || $errors->has('familiar_direccion'),
  4 => $errors->has('motivo_ingreso') || $errors->has('procedencia_ingreso') || $errors->has('tipo_ingreso') || $errors->has('permanencia') || $errors->has('prioridad') || $errors->has('descripcion_caso'),
  5 => $errors->has('doc_ci_adulto') || $errors->has('doc_ci_familiar') || $errors->has('doc_solicitud_ingreso') || $errors->has('documentos')
  ];
 @endphp

 <!-- Avance del formulario -->
 <div class="rm-wizard-flow">
 <aside class="rm-wizard-guide" aria-label="Avance de la preadmisión">
  <p class="rm-wizard-guide__eyebrow">UN NUEVO COMIENZO</p>
  <h4>Una solicitud,<br>paso a paso.</h4>
  <p>Completa la información para acompañar una decisión bien informada.</p>
  <div class="rm-wizard-progress"><span>Paso {{ $paso }} de {{ $totalPasos }}</span><strong>{{ round($paso / $totalPasos * 100) }}%</strong><progress value="{{ $paso }}" max="{{ $totalPasos }}">{{ $paso }}/{{ $totalPasos }}</progress></div>
  <nav aria-label="Pasos del formulario">
  @foreach($pasoActualsLista as $num => $nombre)
   <button type="button" wire:click="volverPaso({{ $num }})" @disabled($num >= $paso) @if($num === $paso) aria-current="step" @endif class="{{ $pasosErrores[$num] ? 'has-error' : '' }} {{ $num < $paso ? 'is-complete' : '' }}">
    <span><i class="ph-bold {{ $pasosErrores[$num] ? 'ph-warning' : ($num < $paso ? 'ph-check' : ([1 => 'ph-user', 2 => 'ph-map-pin', 3 => 'ph-users', 4 => 'ph-note', 5 => 'ph-files'][$num])) }}" aria-hidden="true"></i></span>
    <div><strong>{{ $nombre }}</strong><small>{{ [1 => 'Conocemos al postulante', 2 => 'Su lugar de referencia', 3 => 'Su red de apoyo', 4 => 'El motivo de la solicitud', 5 => 'Revisar y registrar'][$num] }}</small></div>
   </button>
  @endforeach
  </nav>
  <div class="rm-wizard-guide__note"><i class="ph-bold ph-shield-check" aria-hidden="true"></i><p>Registrar esta solicitud <strong>no crea un residente</strong>. El ingreso se formaliza después de la aprobación.</p></div>
 </aside>
 <!-- Form Body -->
 <div class="rm-modal-body rm-admin-wizard__body flex-1 min-h-0 overflow-y-auto overflow-x-hidden space-y-2 custom-scrollbar">
 @if($guardadoExitoso)
  <div class="space-y-3 rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-5">
  <div class="flex items-start gap-3">
   <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-card text-estado-exito shadow-sm">
   <i class="ph-bold ph-check-circle text-2xl"></i>
   </div>
   <div class="flex-1">
   <h4 class="text-base font-black text-titulo">Preadmision registrada</h4>
   <p class="mt-1 text-sm text-titulo/80">
    La solicitud fue registrada correctamente y quedó pendiente de revisión institucional.
   </p>
   <p class="mt-1 text-xs font-semibold text-apoyo">
    Todavía no existe una ficha de residente ni una asignación de habitación o cama.
   </p>
   </div>
  </div>
  <div class="flex flex-wrap gap-2 pt-2">
   <a wire:navigate href="{{ route('admin.admisiones.preadmisiones') }}" class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-sm font-bold text-inverso shadow-sm transition hover:bg-boton-acentoHover">
   <i class="ph-bold ph-list"></i>
   Ir al panel
   </a>
   <button type="button" wire:click="nuevaPreadmision" class="inline-flex items-center gap-2 rounded-xl border-2 border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-titulo transition hover:bg-fondo-hover">
   <i class="ph-bold ph-plus-circle"></i>
   Nueva preadmision
   </button>
  </div>
  </div>
 @else
 @if ($paso === 1)
  <div class="space-y-2 animate-fade-in">
  <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
   <i class="ph-bold ph-user text-boton-acento"></i> 1. Identificación y datos personales
  </h4>
  <div class="grid gap-2 md:grid-cols-3">
   @foreach ([
   'nombres' => 'Nombres *',
   'ap_paterno' => 'Apellido paterno *',
   'ap_materno' => 'Apellido materno',
   'ci' => 'CI *',
   ] as $field => $label)
   <div>
    <label for="pre-{{ $field }}" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">{{ $label }}</label>
<input type="text" wire:model.blur="{{ $field }}" id="pre-{{ $field }}" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has($field),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has($field),
 ])>
    @error($field) <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   @endforeach
   <div>
   <label for="pre-expedicion_ci" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Expedicion CI *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Expedición CI" wire:model.blur="expedicion_ci" id="pre-expedicion_ci">
    <option value="">Seleccionar</option>
    @foreach (['LP','SC','CB','OR','PT','CH','TJ','BE','PA'] as $dep)
    <option value="{{ $dep }}">{{ $dep }}</option>
    @endforeach
   </x-ui.selector>
    @error('expedicion_ci') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-fecha_nac" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Fecha de nacimiento *</label>
<x-ui.calendario teleport="#rm-wizard-control-layer" label="Fecha de nacimiento" wire:model.blur="fecha_nac" id="pre-fecha_nac" :max="now()->subYears(60)->toDateString()" />
    @error('fecha_nac') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-genero" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Genero *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Género" wire:model.blur="genero" id="pre-genero">
    <option value="">Seleccionar</option>
    <option value="MASCULINO">Masculino</option>
    <option value="FEMENINO">Femenino</option>
    <option value="OTRO">Otro</option>
   </x-ui.selector>
    @error('genero') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-estado_civil" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Estado civil</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Estado civil" wire:model.blur="estado_civil" id="pre-estado_civil">
    @foreach (['NO ESPECIFICADO','SOLTERO/A','CASADO/A','VIUDO/A','DIVORCIADO/A','UNION LIBRE'] as $estadoCivil)
    <option value="{{ $estadoCivil }}">{{ $estadoCivil }}</option>
    @endforeach
   </x-ui.selector>
    @error('estado_civil') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-telefono" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Telefono</label>
<input type="text" wire:model.blur="telefono" id="pre-telefono" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('telefono'),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('telefono'),
 ])>
    @error('telefono') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-celular" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Celular</label>
<input type="text" wire:model.blur="celular" id="pre-celular" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('celular'),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('celular'),
 ])>
    @error('celular') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
  </div>
  </div>
 @elseif ($paso === 2)
  <div class="space-y-2 animate-fade-in">
  <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
   <i class="ph-bold ph-map-pin text-boton-acento"></i> 2. Dirección de referencia
  </h4>
  <div class="grid gap-2 md:grid-cols-2">
   <div>
   <label for="pre-departamento_residencia" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Departamento *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Departamento" wire:model.blur="departamento_residencia" id="pre-departamento_residencia">
    <option value="">Seleccionar</option>
    @foreach (['LA PAZ','SANTA CRUZ','COCHABAMBA','ORURO','POTOSI','CHUQUISACA','TARIJA','BENI','PANDO'] as $dep)
    <option value="{{ $dep }}">{{ $dep }}</option>
    @endforeach
   </x-ui.selector>
    @error('departamento_residencia') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   @foreach ([
   'ciudad_municipio' => 'Ciudad / municipio *',
   'zona' => 'Zona *',
   'calle' => 'Calle / avenida *',
   ] as $field => $label)
   <div>
    <label for="pre-{{ $field }}" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">{{ $label }}</label>
<input type="text" wire:model.blur="{{ $field }}" id="pre-{{ $field }}" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has($field),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has($field),
 ])>
    @error($field) <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   @endforeach
   <div class="md:col-span-2">
   <label for="pre-direccion_referencia" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Referencia de direccion</label>
<textarea wire:model.blur="direccion_referencia" id="pre-direccion_referencia" rows="3" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('direccion_referencia'),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('direccion_referencia'),
 ])></textarea>
    @error('direccion_referencia') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
  </div>
  </div>
 @elseif ($paso === 3)
  <div class="space-y-2 animate-fade-in">
  <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
   <i class="ph-bold ph-users text-boton-acento"></i> 3. Familiar responsable
  </h4>
  <div class="grid gap-2 md:grid-cols-3">
   @foreach ([
   'familiar_nombres' => 'Nombres *',
   'familiar_ap_paterno' => 'Apellido paterno *',
   'familiar_ap_materno' => 'Apellido materno',
   'familiar_ci' => 'CI familiar *',
   ] as $field => $label)
   <div>
    <label for="pre-{{ $field }}" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">{{ $label }}</label>
<input type="text" wire:model.blur="{{ $field }}" id="pre-{{ $field }}" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has($field),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has($field),
 ])>
    @error($field) <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   @endforeach
   <div>
   <label for="pre-familiar_parentesco" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Parentesco *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Parentesco" wire:model.blur="familiar_parentesco" id="pre-familiar_parentesco">
    <option value="">Seleccionar</option>
    @foreach (['HIJO/A','ESPOSO/A','HERMANO/A','SOBRINO/A','NIETO/A','TUTOR/A','APODERADO/A','OTRO'] as $parentesco)
    <option value="{{ $familiar_parentesco }}">{{ $familiar_parentesco }}</option>
    @endforeach
   </x-ui.selector>
    @error('familiar_parentesco') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-familiar_celular" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Celular *</label>
<input type="text" wire:model.blur="familiar_celular" id="pre-familiar_celular" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('familiar_celular'),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('familiar_celular'),
 ])>
    @error('familiar_celular') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-familiar_correo" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Correo</label>
<input type="email" wire:model.blur="familiar_correo" id="pre-familiar_correo" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('familiar_correo'),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('familiar_correo'),
 ])>
    @error('familiar_correo') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div class="md:col-span-2">
   <label for="pre-familiar_direccion" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Direccion familiar</label>
<textarea wire:model.blur="familiar_direccion" id="pre-familiar_direccion" rows="3" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('familiar_direccion'),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('familiar_direccion'),
 ])></textarea>
    @error('familiar_direccion') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
  </div>
  </div>
 @elseif ($paso === 4)
  <div class="space-y-2 animate-fade-in">
  <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
   <i class="ph-bold ph-file-text text-boton-acento"></i> 4. Datos del caso
  </h4>
  <div class="grid gap-2 md:grid-cols-2">
   <div>
   <label for="pre-motivo_ingreso" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Motivo de ingreso *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Motivo de ingreso" wire:model.blur="motivo_ingreso" id="pre-motivo_ingreso">
    <option value="">Seleccionar</option>
    @foreach (['CUIDADO_PERMANENTE','CUIDADO_TEMPORAL','CONTROL_MEDICACION','RIESGO_CAIDAS','DEPENDENCIA_FUNCIONAL','SOLEDAD_FAMILIAR','RECUPERACION_POST_HOSPITALARIA','OTRO'] as $motivo)
    <option value="{{ $motivo }}">{{ str_replace('_', ' ', $motivo) }}</option>
    @endforeach
   </x-ui.selector>
    @error('motivo_ingreso') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-procedencia_ingreso" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Procedencia *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Procedencia" wire:model.blur="procedencia_ingreso" id="pre-procedencia_ingreso">
    <option value="">Seleccionar</option>
    @foreach (['DOMICILIO_FAMILIAR','HOSPITAL','OTRO_CENTRO_GERIATRICO','INSTITUCION_SOCIAL','CONSULTA_MEDICA_EXTERNA','OTRO'] as $procedencia)
    <option value="{{ $procedencia }}">{{ str_replace('_', ' ', $procedencia) }}</option>
    @endforeach
   </x-ui.selector>
    @error('procedencia_ingreso') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-tipo_ingreso" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Tipo de ingreso *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Tipo de ingreso" wire:model.blur="tipo_ingreso" id="pre-tipo_ingreso">
    <option value="REGULAR">Regular</option>
    <option value="URGENTE">Urgente</option>
    <option value="DERIVACION">Derivacion</option>
   </x-ui.selector>
    @error('tipo_ingreso') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-permanencia" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Permanencia *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Permanencia" wire:model.blur="permanencia" id="pre-permanencia">
    <option value="PERMANENTE">Permanente</option>
    <option value="TEMPORAL">Temporal</option>
    <option value="OBSERVACION">Observacion</option>
   </x-ui.selector>
    @error('permanencia') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div>
   <label for="pre-prioridad" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Prioridad *</label>
<x-ui.selector teleport="#rm-wizard-control-layer" label="Prioridad" wire:model.blur="prioridad" id="pre-prioridad">
    @foreach (['BAJA','MEDIA','ALTA','CRITICA'] as $nivel)
    <option value="{{ $nivel }}">{{ $nivel }}</option>
    @endforeach
   </x-ui.selector>
    @error('prioridad') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
   <div class="md:col-span-2">
   <label for="pre-descripcion_caso" class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Descripcion del caso</label>
<textarea wire:model.blur="descripcion_caso" id="pre-descripcion_caso" rows="4" @class([
 'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
 'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('descripcion_caso'),
 'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('descripcion_caso'),
 ])></textarea>
    @error('descripcion_caso') <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span> @enderror
   </div>
  </div>
  </div>
 @elseif ($paso === 5)
  <div class="space-y-2 animate-fade-in">
  <div class="flex justify-between items-center border-b border-borde pb-2">
   <h4 class="text-sm font-bold text-titulo flex items-center gap-2">
   <i class="ph-bold ph-folder-open text-boton-acento"></i> 5. Documentos iniciales e institucionales
   </h4>
   <span class="px-2.5 py-1.5 rounded-full bg-estado-advertenciaBg text-estado-advertencia font-bold text-xs border border-estado-advertenciaBorde">
   Obligatorios: presentar hoy
   </span>
  </div>

  <section class="rm-wizard-review" aria-label="Revisa los datos antes de registrar">
   <header><h5>Revisa antes de registrar</h5><p>Corrige cualquier dato desde su paso. La solicitud quedará pendiente de revisión.</p></header>
   <div>
    <article><i class="ph-bold ph-user" aria-hidden="true"></i><span><small>Postulante</small><strong>{{ trim($nombres.' '.$ap_paterno.' '.$ap_materno) }}</strong><small>CI {{ $ci }} {{ $expedicion_ci }}</small></span><button type="button" wire:click="volverPaso(1)" aria-label="Editar datos del postulante"><i class="ph-bold ph-pencil-simple" aria-hidden="true"></i></button></article>
    <article><i class="ph-bold ph-users" aria-hidden="true"></i><span><small>Responsable</small><strong>{{ trim($familiar_nombres.' '.$familiar_ap_paterno.' '.$familiar_ap_materno) }}</strong><small>{{ $familiar_parentesco }}</small></span><button type="button" wire:click="volverPaso(3)" aria-label="Editar datos del responsable"><i class="ph-bold ph-pencil-simple" aria-hidden="true"></i></button></article>
    <article><i class="ph-bold ph-note" aria-hidden="true"></i><span><small>Solicitud</small><strong>{{ str_replace('_', ' ', $motivo_ingreso) }}</strong><small>{{ str_replace('_', ' ', $permanencia) }}</small></span><button type="button" wire:click="volverPaso(4)" aria-label="Editar datos de la solicitud"><i class="ph-bold ph-pencil-simple" aria-hidden="true"></i></button></article>
   </div>
  </section>
  {{-- Documentos del Solicitante --}}
  <div class="rm-admin-wizard__group space-y-2 p-3 md:p-4">
   <div class="flex items-center gap-2 pb-3 border-b border-borde/50">
   <div class="w-7 h-7 rounded-full bg-boton-acento/10 text-boton-acento flex items-center justify-center">
    <i class="ph-bold ph-folder-user text-lg"></i>
   </div>
   <h5 class="text-sm font-bold text-titulo uppercase tracking-wider">Archivos del Solicitante</h5>
   </div>
   <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
   @foreach ($docsSolicitante as $docConf)
    @php
    $propiedad = $docConf['propiedad'];
    $docSubido = ! empty($$propiedad);
    $es48hPend = in_array($docConf['tipo'], $docs_pendientes_48h);
    $bloqueante = $docConf['bloquea_avance'];
    $permite48h = $docConf['permite_48h'];
    @endphp
    <div wire:key="doc-{{ $docConf['tipo'] }}"
     class="flex flex-col p-2.5 border rounded-xl bg-fondo-card shadow-sm transition-all
     {{ $docSubido ? 'border-estado-exito/40' : ($bloqueante ? 'border-estado-peligro/40 bg-estado-peligroBg/5' : 'border-estado-advertencia/40 bg-estado-advertenciaBg/5') }}">
    <div class="flex items-start gap-2">
     <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 border border-borde/40
     {{ $docSubido ? 'bg-estado-exitoBg text-estado-exito' : ($es48hPend ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-fondo text-apoyo') }}">
     <i class="ph-fill {{ $docSubido ? 'ph-check-circle' : ($es48hPend ? 'ph-clock' : 'ph-file-text') }} text-lg"></i>
     </div>
     <div class="min-w-0 flex-1">
     <h6 class="text-xs font-bold text-titulo">{{ $docConf['nombre'] }}</h6>
     <p class="text-xs text-apoyo mt-0.5">{{ $docConf['descripcion'] }}</p>
     <div class="flex flex-wrap items-center gap-1 mt-1">
      @if($bloqueante)
      <span class="px-1.5 py-0.5 rounded text-xs font-black bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase">Obligatorio hoy</span>
      @elseif($permite48h)
      <span class="px-1.5 py-0.5 rounded text-xs font-black bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase">Permite 48 h</span>
      @endif
      @if($docConf['requiere_firma'])
      <span class="px-1.5 py-0.5 rounded text-xs font-bold bg-estado-infoBg text-estado-info border border-estado-infoBorde uppercase">Requiere firma</span>
      @endif
     </div>
     @error($propiedad)
      <p class="text-xs text-estado-peligro font-bold mt-0.5">{{ $message }}</p>
     @enderror
     </div>
    </div>

    <div class="flex items-center gap-1 mt-2 pt-2 border-t border-borde/30">
     @if($docSubido)
     <span class="flex-1 text-center px-2 py-1 text-xs font-bold text-estado-exito bg-estado-exitoBg rounded border border-estado-exito/20">
      <i class="ph-bold ph-check"></i> Subido
     </span>
     @else
     <label class="flex-1 text-center cursor-pointer px-2 py-1 text-xs font-bold text-boton-acento border border-boton-acento rounded hover:bg-boton-acento hover:text-inverso transition-all">
      <i class="ph-bold ph-upload-simple"></i> Subir
      <input type="file" wire:model="{{ $propiedad }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
     </label>
     @if($permite48h && ! $docSubido)
      @if($es48hPend)
      <span class="px-2 py-1 text-xs font-bold text-estado-advertencia bg-estado-advertenciaBg border border-estado-advertenciaBorde rounded">
       <i class="ph-bold ph-clock"></i> Pend. 48 h
      </span>
      @else
      <span class="px-2 py-1 text-xs text-apoyo bg-fondo border border-borde rounded">
       <i class="ph-bold ph-info"></i> Opcional hoy
      </span>
      @endif
     @endif
     @endif
     <div wire:loading wire:target="{{ $propiedad }}" class="text-xs text-estado-info">
     <i class="ph-bold ph-spinner animate-spin"></i>
     </div>
    </div>
    </div>
   @endforeach
   </div>

   {{-- Aviso 48h si hay documentos que permiten plazo --}}
   @php $hayPermite48h = collect($docsSolicitante)->contains('permite_48h', true); @endphp
   @if($hayPermite48h)
   <div class="mt-2 flex items-start gap-2 p-2.5 bg-estado-advertenciaBg/40 border border-estado-advertenciaBorde rounded-xl text-xs text-estado-advertencia font-semibold">
    <i class="ph-bold ph-clock text-base shrink-0 mt-0.5"></i>
    <span>Los documentos marcados <strong>"Permite 48 h"</strong> pueden ser presentados después de confirmar. Se notificará al familiar responsable con el plazo exacto.</span>
   </div>
   @endif
  </div>

  {{-- Documentos Institucionales Autogenerados --}}
  <div class="rm-admin-wizard__group space-y-2 p-3 md:p-4">
   <div class="flex items-center gap-2 pb-3 border-b border-borde/50">
   <div class="w-7 h-7 rounded-full bg-estado-infoBg text-estado-info flex items-center justify-center">
    <i class="ph-bold ph-file-pdf text-lg"></i>
   </div>
   <h5 class="text-sm font-bold text-titulo uppercase tracking-wider">Documentos Institucionales</h5>
   <span class="ml-auto px-2 py-1 rounded-full bg-estado-infoBg text-estado-info text-xs font-bold border border-estado-infoBorde uppercase tracking-wider">Autogenerados al confirmar</span>
   </div>
   <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
   @foreach ($docsInstitucionales as $docConf)
    <div wire:key="inst-{{ $docConf['tipo'] }}" class="flex items-center justify-between p-2.5 border rounded-xl bg-fondo-card shadow-sm border-borde">
    <div class="flex items-center gap-2 overflow-hidden flex-1">
     <div class="w-7 h-7 rounded-full bg-estado-infoBg text-estado-info flex items-center justify-center shrink-0 border border-borde/40">
     <i class="ph-fill ph-file-pdf text-lg"></i>
     </div>
     <div class="min-w-0 flex-1">
     <h6 class="text-xs font-bold text-titulo truncate">{{ $docConf['nombre'] }}</h6>
     <div class="flex items-center gap-1 mt-0.5">
      <span class="text-xs text-apoyo">PDF generado automáticamente</span>
      @if($docConf['requiere_firma'])
      <span class="px-1 py-0.5 rounded text-xs font-bold bg-estado-infoBg text-estado-info border border-estado-infoBorde uppercase">Firma requerida</span>
      @endif
     </div>
     </div>
    </div>
    <span class="px-2 py-1 text-xs font-bold text-estado-info bg-estado-infoBg rounded border border-estado-infoBorde shrink-0 ml-2">
     <i class="ph-bold ph-gear-fine"></i> Al confirmar
    </span>
    </div>
   @endforeach
   </div>
  </div>
  </div>
 @endif
 @endif
 </div>

 </div>
 <!-- Botonera -->
 <div class="rm-modal-footer rm-admin-wizard__footer flex items-center justify-between">
 <div>
  @if ($paso > 1)
  <button type="button" wire:click="anterior" wire:loading.attr="disabled" class="rm-btn-secondary flex items-center gap-2">
   <i class="ph-bold ph-arrow-left"></i>
   Atras
  </button>
  @else
  <button type="button" x-on:click="
   if (isDirty) {
   Swal.fire({
    title: '¿Salir sin guardar?',
    text: 'Hay cambios sin guardar en el formulario. Si sale, perdera todos los datos.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: 'var(--estado-peligro)',
    cancelButtonColor: 'var(--boton-acento)',
    confirmButtonText: 'Si, salir',
    cancelButtonText: 'Permanecer',
    background: 'var(--fondo-card)',
    color: 'var(--texto-principal)'
   }).then((result) => {
    if (result.isConfirmed) {
    if (window.Livewire?.navigate) {
     window.Livewire.navigate('{{ route('admin.admisiones.preadmisiones') }}');
    } else {
     window.location.href = '{{ route('admin.admisiones.preadmisiones') }}';
    }
    }
   });
   } else {
   if (window.Livewire?.navigate) {
    window.Livewire.navigate('{{ route('admin.admisiones.preadmisiones') }}');
   } else {
    window.location.href = '{{ route('admin.admisiones.preadmisiones') }}';
   }
   }
  " class="rm-btn-secondary">
   Cancelar
  </button>
  @endif
 </div>

 @if ($guardadoExitoso)
  <a wire:navigate href="{{ route('admin.admisiones.preadmisiones') }}" class="rm-btn-primary flex items-center gap-2">
  <i class="ph-bold ph-arrow-square-out"></i>
  Volver al panel
  </a>
 @elseif ($paso < $totalPasos)
  <button type="button" wire:click="siguiente" wire:loading.attr="disabled" class="rm-btn-primary flex items-center gap-2">
  <span wire:loading.remove wire:target="siguiente">Siguiente <i class="ph-bold ph-arrow-right"></i></span>
  <span wire:loading wire:target="siguiente"><i class="ph-bold ph-spinner animate-spin"></i> Validando...</span>
  </button>
 @else
  <button type="button" wire:click="confirmarPreadmision" wire:loading.attr="disabled" class="rm-btn-success flex items-center gap-2">
  <span wire:loading.remove wire:target="confirmarPreadmision"><i class="ph-bold ph-check-circle text-lg"></i> Confirmar preadmision</span>
  <span wire:loading wire:target="confirmarPreadmision"><i class="ph-bold ph-spinner animate-spin text-lg"></i> Guardando...</span>
  </button>
 @endif
 </div>

 @script
 <script>

{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-admisiones-preadmision-wizard.js')) !!}
</script>
    @endscript
    </div>
</div>
