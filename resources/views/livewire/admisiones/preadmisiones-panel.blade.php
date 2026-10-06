<div class="rm-admin-preadmissions">
 @include('livewire.admisiones.partials.preadmisiones-workspace')
 <x-ui.modal-livewire wire:model="modalAdmision" title="Formalizar admisión institucional" close-method="cerrarAdmision" max-width="4xl">
 @if($solicitudAdmision)
  <form wire:submit="formalizarAdmision" class="space-y-5">
  <div class="rounded-2xl border border-estado-infoBorde bg-estado-infoBg p-4">
   <p class="text-xs font-bold uppercase tracking-wide text-estado-info">Solicitud aprobada</p>
   <h4 class="mt-1 text-lg font-black text-titulo">{{ $solicitudAdmision->nombre_completo }}</h4>
   <p class="text-sm text-apoyo">Complete los datos de ingreso. La ficha de residente se creará al confirmar esta operación.</p>
  </div>

  @error('solicitud') <p class="rounded-xl bg-estado-peligroBg p-3 text-sm font-semibold text-estado-peligro">{{ $message }}</p> @enderror
  @error('admision') <p class="rounded-xl bg-estado-peligroBg p-3 text-sm font-semibold text-estado-peligro">{{ $message }}</p> @enderror

  <nav class="rm-admin-admission__steps" aria-label="Pasos de admisión">
   @foreach(['Identidad', 'Contacto', 'Documentación', 'Consentimientos', 'Seguro', 'Habitación y cama', 'Confirmación'] as $indice => $etiqueta)
    <button type="button" wire:click="irPasoAdmision({{ $indice + 1 }})" @if($pasoAdmision === $indice + 1) aria-current="step" @endif>
     <span>{{ $indice + 1 }}</span>{{ $etiqueta }}
    </button>
   @endforeach
  </nav>

  @if($pasoAdmision === 1)
   <x-ui.form-section title="Identidad" description="Datos precargados de la preadmisión aprobada." icon="ph-identification-card">
    <dl class="rm-admin-admission__facts">
     <div><dt>Postulante</dt><dd>{{ $solicitudAdmision->nombre_completo }}</dd></div>
     <div><dt>Documento</dt><dd>{{ $solicitudAdmision->numero_documento ?: 'Sin registrar' }}</dd></div>
     <div><dt>Fecha de nacimiento</dt><dd>{{ $solicitudAdmision->fecha_nacimiento?->format('d/m/Y') ?: 'Sin registrar' }}</dd></div>
     <div><dt>Tipo de ingreso</dt><dd>{{ $solicitudAdmision->tipo_ingreso ?: 'Sin registrar' }}</dd></div>
    </dl>
    <label class="rm-admin-admission__field">Nivel educativo <input type="text" wire:model="nivel_educativo" maxlength="100" class="rm-input" placeholder="Opcional"></label>
   </x-ui.form-section>
  @elseif($pasoAdmision === 2)
   <x-ui.form-section title="Contacto responsable" description="Se vinculará al residente al confirmar." icon="ph-address-book">
    @if($solicitudAdmision->contacto)
     <dl class="rm-admin-admission__facts">
      <div><dt>Nombre</dt><dd>{{ $solicitudAdmision->contacto->nombres }} {{ $solicitudAdmision->contacto->apellido_paterno }}</dd></div>
      <div><dt>Teléfono</dt><dd>{{ $solicitudAdmision->contacto->celular ?: $solicitudAdmision->contacto->telefono ?: 'Sin registrar' }}</dd></div>
      <div><dt>Correo</dt><dd>{{ $solicitudAdmision->contacto->correo ?: 'Sin registrar' }}</dd></div>
     </dl>
    @else
     <x-ui.empty-state icono="ph-address-book" titulo="Falta el contacto responsable" texto="La admisión requiere un contacto asociado a la preadmisión." compact />
    @endif
    @error('contacto')<p class="text-xs text-estado-peligro">{{ $message }}</p>@enderror
   </x-ui.form-section>
  @elseif($pasoAdmision === 3)
   <x-ui.form-section title="Documentación" description="Documentos vinculados a la preadmisión." icon="ph-files">
    @forelse($solicitudAdmision->documentos as $documento)
     <div class="rm-admin-admission__document"><span>{{ $documento->nombre }} · {{ $documento->tipo_documento }}</span><x-ui.status-badge :estado="$documento->estado" /></div>
    @empty
     <x-ui.empty-state icono="ph-files" titulo="Sin documentación" texto="No hay documentos asociados a esta preadmisión." compact />
    @endforelse
   </x-ui.form-section>
  @elseif($pasoAdmision === 4)
   <x-ui.form-section title="Consentimientos" description="Confirme las autorizaciones con la persona responsable." icon="ph-shield-check">
    <div class="space-y-3">
     <label class="flex items-start gap-3 rounded-xl border border-borde bg-fondo-card p-3"><input type="checkbox" wire:model="autoriza_informacion_medica" class="mt-1 rounded border-borde text-boton-acento"><span class="text-sm text-titulo">Autoriza compartir información médica con el responsable registrado. *</span></label>
     @error('autoriza_informacion_medica')<p class="text-xs text-estado-peligro">{{ $message }}</p>@enderror
     <label class="flex items-start gap-3 rounded-xl border border-borde bg-fondo-card p-3"><input type="checkbox" wire:model="consentimiento_datos" class="mt-1 rounded border-borde text-boton-acento"><span class="text-sm text-titulo">Confirma el consentimiento para tratamiento de datos personales y clínicos. *</span></label>
     @error('consentimiento_datos')<p class="text-xs text-estado-peligro">{{ $message }}</p>@enderror
    </div>
   </x-ui.form-section>
  @elseif($pasoAdmision === 5)
   <x-ui.form-section title="Seguro" description="Registre la cobertura si el postulante cuenta con una. La póliza se vinculará al residente al confirmar." icon="ph-shield-check">
    <div class="rm-admin-admission__fields">
     <label>Entidad <input type="text" wire:model="seguro_entidad" maxlength="120" class="rm-input"></label>
     <label>Plan <input type="text" wire:model="seguro_plan" maxlength="100" class="rm-input"></label>
     <label>Número de afiliación <input type="text" wire:model="seguro_afiliacion" maxlength="80" class="rm-input"></label>
     <label>Titular <input type="text" wire:model="seguro_titular" maxlength="160" class="rm-input"></label>
     <label class="rm-admin-admission__wide">Cobertura <textarea wire:model="seguro_cobertura" maxlength="3000" rows="2" class="rm-input"></textarea></label>
    </div>
    @error('seguro_entidad')<p class="text-xs text-estado-peligro">{{ $message }}</p>@enderror
   </x-ui.form-section>
  @elseif($pasoAdmision === 6)
  <x-ui.form-section title="Ingreso y ocupación" description="La cama es obligatoria y se bloqueará al confirmar." icon="ph-bed">
   <div class="grid gap-4 md:grid-cols-3">
   <div class="md:col-span-3">
    <label class="mb-2 block text-xs font-bold text-apoyo">1. Seleccione una habitación <span class="text-estado-peligro">*</span></label>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($habitacionesAdmision as $habitacion)
     @php
     $habilitada = ! in_array($habitacion->estado, ['MANTENIMIENTO', 'BLOQUEADA'], true) && $habitacion->camas_disponibles_count > 0;
     $faltanRegistrar = max(0, $habitacion->capacidad - $habitacion->camas_count);
     $motivo = match (true) {
      $habitacion->estado === 'MANTENIMIENTO' => 'Habitación en mantenimiento',
      $habitacion->estado === 'BLOQUEADA' => 'Habitación bloqueada',
      $habitacion->camas_count === 0 => 'No tiene camas registradas',
      $habitacion->camas_disponibles_count === 0 => 'Sin camas libres',
      default => $habitacion->camas_disponibles_count.' cama(s) libre(s)',
     };
     @endphp
     <label class="relative rounded-2xl border p-3 transition {{ $habitacion_id === $habitacion->cod_habitacion ? 'border-input-bordeFocus bg-estado-infoBg' : 'border-borde bg-fondo-card' }} {{ $habilitada ? 'cursor-pointer hover:bg-fondo-hover' : 'cursor-not-allowed opacity-70' }}">
     <input type="radio" wire:model.live="habitacion_id" value="{{ $habitacion->cod_habitacion }}" class="sr-only" @disabled(!$habilitada)>
     <span class="block text-sm font-black text-titulo">{{ $habitacion->nombre }}</span>
     <span class="mt-1 block text-xs text-apoyo">{{ str_replace('_', ' ', $habitacion->tipo_habitacion) }}{{ $habitacion->ubicacion ? ' · '.$habitacion->ubicacion : '' }}</span>
     <span class="mt-2 block text-xs font-semibold {{ $habilitada ? 'text-estado-exito' : 'text-estado-advertencia' }}">{{ $motivo }}</span>
     <span class="mt-1 block text-[11px] text-apoyo">Capacidad {{ $habitacion->capacidad }} · Ocupadas {{ $habitacion->camas_ocupadas_count }} · Fuera de servicio {{ $habitacion->camas_mantenimiento_count + $habitacion->camas_bloqueadas_count }}</span>
     @if($faltanRegistrar > 0)<span class="mt-1 block text-[11px] text-apoyo">{{ $faltanRegistrar }} espacio(s) aún sin cama registrada</span>@endif
     </label>
    @empty
     <p class="col-span-full rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 text-sm text-estado-advertencia">No existen habitaciones registradas. Configure habitaciones y camas antes de admitir.</p>
    @endforelse
    </div>
    @error('habitacion_id') <p class="mt-1 text-xs font-semibold text-estado-peligro">{{ $message }}</p> @enderror
   </div>
   <div class="md:col-span-3">
    <label class="mb-2 block text-xs font-bold text-apoyo">2. Seleccione la cama <span class="text-estado-peligro">*</span></label>
    @if(!$habitacion_id)
    <p class="rounded-xl border border-borde bg-fondo-hover p-3 text-sm text-apoyo">Seleccione primero una habitación con disponibilidad.</p>
    @else
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
     @foreach($camasHabitacion as $cama)
     @php
      $asignacion = $cama->asignacionesActivas->first();
      $libre = $cama->estado === 'DISPONIBLE' && !$asignacion;
     @endphp
     <label class="rounded-xl border p-3 {{ $cama_id === $cama->cod_cama ? 'border-input-bordeFocus bg-estado-infoBg' : 'border-borde bg-fondo-card' }} {{ $libre ? 'cursor-pointer' : 'cursor-not-allowed opacity-70' }}">
      <input type="radio" wire:model="cama_id" value="{{ $cama->cod_cama }}" class="sr-only" @disabled(!$libre)>
      <span class="block text-sm font-black text-titulo">Cama {{ $cama->codigo ?: $cama->numero }}</span>
      <span class="mt-1 block text-xs {{ $libre ? 'text-estado-exito' : 'text-estado-advertencia' }}">
      {{ $libre ? 'Disponible para ingreso' : ($asignacion?->residente ? 'Ocupada por '.trim($asignacion->residente->nombres.' '.$asignacion->residente->apellido_paterno.' '.$asignacion->residente->apellido_materno) : str_replace('_', ' ', $cama->estado)) }}
      </span>
     </label>
     @endforeach
    </div>
    @if($camasHabitacion->isEmpty())<p class="rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 text-sm text-estado-advertencia">Esta habitación no tiene camas registradas.</p>@endif
    @endif
    @error('cama_id') <p class="mt-1 text-xs font-semibold text-estado-peligro">{{ $message }}</p> @enderror
   </div>
   <div><label class="mb-1 block text-xs font-bold text-apoyo">Fecha de ingreso *</label><input type="date" wire:model="fecha_ingreso" max="{{ now()->toDateString() }}" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('fecha_ingreso')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
   <div><label class="mb-1 block text-xs font-bold text-apoyo">Hora de ingreso *</label><input type="time" wire:model="hora_ingreso" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('hora_ingreso')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
   <div><label class="mb-1 block text-xs font-bold text-apoyo">Nivel educativo</label><input type="text" wire:model="nivel_educativo" placeholder="Ej. Primaria completa" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></div>
   </div>
  </x-ui.form-section>
  @else
   <x-ui.form-section title="Confirmación" description="Revise los datos antes de crear el residente y ocupar la cama." icon="ph-check-circle">
    <dl class="rm-admin-admission__facts">
     <div><dt>Postulante</dt><dd>{{ $solicitudAdmision->nombre_completo }}</dd></div>
     <div><dt>Responsable</dt><dd>{{ $solicitudAdmision->contacto ? $solicitudAdmision->contacto->nombres.' '.$solicitudAdmision->contacto->apellido_paterno : 'Falta contacto' }}</dd></div>
     <div><dt>Documentos</dt><dd>{{ $solicitudAdmision->documentos->count() }} registrados</dd></div>
     <div><dt>Consentimientos</dt><dd>{{ $consentimiento_datos && $autoriza_informacion_medica ? 'Confirmados' : 'Pendientes' }}</dd></div>
     <div><dt>Seguro</dt><dd>{{ $seguro_entidad ?: 'Sin seguro registrado' }}</dd></div>
     <div><dt>Habitación</dt><dd>{{ $habitacionesAdmision->firstWhere('cod_habitacion', $habitacion_id)?->codigo ?: 'Sin seleccionar' }}</dd></div>
     <div><dt>Cama</dt><dd>{{ $camasHabitacion->firstWhere('cod_cama', $cama_id)?->codigo ?: 'Sin seleccionar' }}</dd></div>
     <div><dt>Ingreso</dt><dd>{{ $fecha_ingreso }} · {{ $hora_ingreso }}</dd></div>
    </dl>
    @error('cama_id')<p class="text-xs text-estado-peligro">{{ $message }}</p>@enderror
   </x-ui.form-section>
  @endif

  <div class="flex flex-col-reverse gap-2 border-t border-borde pt-4 sm:flex-row sm:justify-end">
   <button type="button" wire:click="cerrarAdmision" class="rm-btn-secondary">Cancelar</button>
   @if($pasoAdmision > 1)<button type="button" wire:click="irPasoAdmision({{ $pasoAdmision - 1 }})" class="rm-btn-secondary">Anterior</button>@endif
   @if($pasoAdmision < 7)
    <button type="button" wire:click="irPasoAdmision({{ $pasoAdmision + 1 }})" class="rm-btn-primary">Continuar</button>
   @else
    <button type="submit" wire:loading.attr="disabled" wire:target="formalizarAdmision" class="rm-btn-primary">
     <span wire:loading.remove wire:target="formalizarAdmision"><i class="ph-bold ph-check-circle"></i> Confirmar admisión</span>
     <span wire:loading wire:target="formalizarAdmision">Procesando admisión…</span>
    </button>
   @endif
  </div>
  </form>
 @endif
 </x-ui.modal-livewire>

 @script
 <script>

{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-admisiones-preadmision-wizard.js')) !!}
</script>
    @endscript
</div>
