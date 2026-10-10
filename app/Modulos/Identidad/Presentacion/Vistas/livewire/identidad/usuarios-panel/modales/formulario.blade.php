@if($mostrarFormulario)
<div class="fixed inset-0 z-[2147483646] flex items-center justify-center bg-black/50 backdrop-blur-xs px-3 sm:px-4 transition-all duration-300">
 <div class="relative z-[2147483647] w-full max-w-4xl max-h-[92vh] overflow-hidden rounded-[var(--rm-radius-modal,20px)] border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xl flex flex-col font-sans">

 {{-- HEADER CON PROGRESO INSTITUCIONAL --}}
 <header class="relative border-b border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] px-5 py-4 shrink-0">
  <div class="flex items-center justify-between gap-4 mb-2">
  <div class="flex items-center gap-3">
   <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-primary)] text-white shadow-sm">
   <i class="ph-bold {{ $isEdit ? 'ph-pencil-simple' : 'ph-user-plus' }} text-xl"></i>
   </span>
   <div>
   <h2 class="text-base sm:text-lg font-extrabold text-[var(--rm-text-primary)] leading-tight">
    {{ $isEdit ? 'Actualizar' : 'Registro de' }} <span class="text-[var(--rm-primary)]">Personal y Usuarios</span>
   </h2>
   <p class="text-xs text-[var(--rm-text-secondary)] font-medium">
    {{ $isEdit ? 'Modificación de credenciales y datos de expediente' : 'Alta institucional de nuevas cuentas de acceso y personal autorizado' }}
   </p>
   </div>
  </div>
  <button type="button" wire:click="cerrarFormulario"
   class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] hover:border-[var(--rm-primary)] transition cursor-pointer shadow-xs">
   <i class="ph-bold ph-x text-base"></i>
  </button>
  </div>

  {{-- BARRA DE PASOS VISUAL CANÓNICA --}}
  <div class="relative px-2 pt-3 pb-1 hidden sm:block">
  <div class="relative flex items-center justify-between max-w-3xl mx-auto">
   {{-- Línea de fondo --}}
   <div class="absolute top-1/2 left-0 w-full h-1 bg-[var(--rm-surface-alt)] -translate-y-1/2 rounded-full"></div>
   {{-- Línea de progreso activa --}}
   <div class="absolute top-1/2 left-0 h-1 bg-[var(--rm-primary)] -translate-y-1/2 rounded-full transition-all duration-500 ease-out shadow-xs"
    style="width: {{ (($pasoFormulario - 1) / 4) * 100 }}%"></div>

   {{-- Pasos --}}
   @php
   $pasosUsu = [
    1 => ['i' => 'ph-user-circle', 'l' => 'Identidad'],
    2 => ['i' => 'ph-phone-call', 'l' => 'Contacto'],
    3 => ['i' => 'ph-briefcase', 'l' => 'Perfil'],
    4 => ['i' => 'ph-shield-check', 'l' => 'Seguridad'],
    5 => ['i' => 'ph-check-square', 'l' => 'Confirmar']
   ];
   @endphp

   @foreach($pasosUsu as $s => $p)
   <div class="relative flex flex-col items-center">
    <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl border-2 transition-all duration-300
    {{ $pasoFormulario > $s ? 'bg-[var(--rm-success)] border-[var(--rm-success)] text-white shadow-xs' :
     ($pasoFormulario == $s ? 'bg-[var(--rm-surface)] border-[var(--rm-primary)] text-[var(--rm-primary)] shadow-md ring-2 ring-[var(--rm-primary-soft)]' :
     'bg-[var(--rm-surface)] border-[var(--rm-border-soft)] text-[var(--rm-text-secondary)]/50') }}">
    <i class="ph-bold {{ $p['i'] }} text-base {{ $pasoFormulario == $s ? 'scale-110' : '' }}"></i>
    @if($pasoFormulario > $s)
     <div class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[var(--rm-primary)] text-white text-[9px] font-bold">
     <i class="ph-bold ph-check"></i>
     </div>
    @endif
    </div>
    <span class="mt-1.5 whitespace-nowrap text-[11px] font-bold uppercase tracking-tight transition-all duration-300
    {{ $pasoFormulario >= $s ? 'text-[var(--rm-text-primary)] font-extrabold' : 'text-[var(--rm-text-secondary)]/60' }}
    {{ $pasoFormulario == $s ? 'text-[var(--rm-primary)]' : '' }}">
    {{ $p['l'] }}
    </span>
   </div>
   @endforeach
  </div>
  </div>
 </header>

 {{-- CONTENIDO SCROLLABLE --}}
 <div class="flex-1 max-h-[64vh] overflow-y-auto custom-scrollbar p-5 sm:p-6 space-y-6">

  {{-- PASO 1: DATOS PERSONALES / IDENTIDAD --}}
  @if($pasoFormulario === 1)
  <div class="space-y-5 animate-in fade-in duration-200">
   <x-ui.form-section title="FOTOGRAFÍA INSTITUCIONAL" description="Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 4MB." :columns="1">
   <div class="flex flex-col sm:flex-row items-center gap-5 p-2">
    <div class="relative group shrink-0">
    @if($foto_de_perfil_upload)
     <img src="{{ $foto_de_perfil_upload->temporaryUrl() }}"
      class="h-24 w-24 rounded-2xl object-cover ring-3 ring-[var(--rm-primary)] shadow-md">
    @elseif($isEdit && ($cod_usuario ?? $usuarioId) && \App\Models\User::find($cod_usuario ?? $usuarioId)?->foto_de_perfil)
     <img src="{{ asset('storage/' . \App\Models\User::find($cod_usuario ?? $usuarioId)->foto_de_perfil) }}"
      class="h-24 w-24 rounded-2xl object-cover ring-3 ring-[var(--rm-border)] shadow-md">
    @else
     <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-[var(--rm-surface-alt)] text-2xl font-black text-[var(--rm-primary)] ring-3 ring-[var(--rm-border)] shadow-sm uppercase">
     {{ mb_substr($nombres ?? 'U', 0, 1) }}{{ mb_substr($ap_paterno ?? 'I', 0, 1) }}
     </div>
    @endif
    <div wire:loading wire:target="foto_de_perfil_upload" class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-2xl">
     <i class="ph-bold ph-spinner animate-spin text-white text-2xl"></i>
    </div>
    </div>
    <div class="flex-1 text-center sm:text-left space-y-2">
    <label class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--rm-primary)] hover:bg-[var(--rm-primary-hover)] text-white rounded-xl text-xs font-bold uppercase tracking-wider cursor-pointer shadow-xs transition active:scale-95">
     <i class="ph-bold ph-upload-simple text-sm"></i> Seleccionar fotografía
     <input type="file" wire:model="foto_de_perfil_upload" class="hidden" accept="image/*">
    </label>
    @error('foto_de_perfil_upload')
     <p class="rm-error">{{ $message }}</p>
    @enderror
    </div>
   </div>
   </x-ui.form-section>

   <x-ui.form-section title="DATOS PERSONALES" description="Información de filiación y nombres oficiales." :columns="3">
   <div class="md:col-span-3">
    <label class="rm-label rm-label-required">Nombres</label>
    <input type="text" wire:model="nombres" placeholder="Ej. Carla Valeria"
    class="rm-input uppercase {{ $errors->has('nombres') ? 'is-invalid' : '' }}">
    @error('nombres') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label rm-label-required">Apellido Paterno</label>
    <input type="text" wire:model="ap_paterno" placeholder="Apellido paterno"
    class="rm-input uppercase {{ $errors->has('ap_paterno') ? 'is-invalid' : '' }}">
    @error('ap_paterno') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label">Apellido Materno</label>
    <input type="text" wire:model="ap_materno" placeholder="Apellido materno"
    class="rm-input uppercase {{ $errors->has('ap_materno') ? 'is-invalid' : '' }}">
    @error('ap_materno') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label rm-label-required">Género</label>
    <select wire:model="genero" class="rm-select {{ $errors->has('genero') ? 'is-invalid' : '' }}">
    <option value="">SELECCIONE...</option>
    <option value="FEMENINO">FEMENINO</option>
    <option value="MASCULINO">MASCULINO</option>
    </select>
    @error('genero') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <div class="flex justify-between items-center mb-1">
    <label class="rm-label rm-label-required">Fecha Nacimiento</label>
    @if($edad !== null)
     <span class="text-xs font-bold text-[var(--rm-primary)]">Edad: {{ $edad }} años</span>
    @endif
    </div>
    <input type="date" wire:model.live="fecha_nacimiento"
    class="rm-input {{ $errors->has('fecha_nacimiento') ? 'is-invalid' : '' }}">
    @error('fecha_nacimiento') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label rm-label-required">País Emisor Documento</label>
    <select wire:model.live="pais_documento" class="rm-select">
    @foreach(array_keys($paisesConfig) as $pName)
     <option value="{{ mb_strtoupper($pName, 'UTF-8') }}">{{ mb_strtoupper($pName, 'UTF-8') }}</option>
    @endforeach
    </select>
   </div>
   </x-ui.form-section>

   <x-ui.form-section title="DOCUMENTO DE IDENTIDAD" description="Tipo y numeración del documento de identificación oficial." :columns="3">
   <div class="{{ mb_strtoupper((string) $pais_documento, 'UTF-8') !== 'OTRO' ? 'pointer-events-none opacity-75' : '' }}">
    <label class="rm-label rm-label-required">Tipo de Documento</label>
    <select wire:model.live="tipo_documento" tabindex="-1" class="rm-select">
    <option value="CI">CI</option>
    <option value="DNI">DNI</option>
    <option value="PAS">PAS</option>
    <option value="CPF">CPF</option>
    <option value="RUT">RUT</option>
    <option value="Cédula">Cédula</option>
    <option value="INE">INE</option>
    <option value="SSN">SSN</option>
    <option value="Pasaporte">Pasaporte</option>
    </select>
   </div>

   <div>
    <label class="rm-label rm-label-required">Número de Documento</label>
    <input type="text" wire:model="numero_documento" placeholder="Ej. 1234567"
    class="rm-input uppercase {{ $errors->has('numero_documento') ? 'is-invalid' : '' }}">
    @error('numero_documento') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   @if(mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI')
    <div>
    <label class="rm-label rm-label-required">Expedido (EXP)</label>
    <select wire:model="expedido" class="rm-select {{ $errors->has('expedido') ? 'is-invalid' : '' }}">
     <option value="">SELECCIONE...</option>
     @foreach(['LP','CB','SC','OR','PT','CH','TJ','BN','PD'] as $e)
     <option value="{{ $e }}">{{ $e }}</option>
     @endforeach
    </select>
    @error('expedido') <p class="rm-error">{{ $message }}</p> @enderror
    </div>
   @endif

   @if(mb_strtoupper((string) $pais_documento, 'UTF-8') !== 'OTRO')
    <div class="col-span-full">
    <p class="rm-help italic">
     * El tipo de documento se bloquea y pre-asigna automáticamente según el país emisor seleccionado.
    </p>
    </div>
   @endif
   </x-ui.form-section>
  </div>
  @endif

  {{-- PASO 2: CONTACTO Y DOMICILIO --}}
  @if($pasoFormulario === 2)
  <div class="space-y-5 animate-in fade-in duration-200">
   <x-ui.form-section title="COMUNICACIÓN INSTITUCIONAL" description="Canales de contacto oficial y telefonía móvil." :columns="2">
   <div>
    <label class="rm-label rm-label-required">Correo Institucional</label>
    <input type="email" wire:model.live="correo" placeholder="ejemplo@jardindelosrecuerdos.org"
    class="rm-input lowercase {{ $errors->has('correo') ? 'is-invalid' : '' }}">
    @error('correo') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label rm-label-required">Celular Institucional / Personal</label>
    <div class="flex gap-2">
    <select wire:model.live="pais_telefono" class="rm-select w-36 shrink-0">
     <option value="">País</option>
     @foreach($paisesConfig as $pName => $pData)
     <option value="{{ $pName }}">{{ $pName }} ({{ $pData['codigo'] }})</option>
     @endforeach
    </select>
    <input type="text" wire:model="telefono"
     placeholder="{{ $pais_telefono && isset($paisesConfig[$pais_telefono]) ? $paisesConfig[$pais_telefono]['placeholder'] : 'Número celular...' }}"
     class="rm-input flex-1 {{ $errors->has('telefono') ? 'is-invalid' : '' }}">
    </div>
    @error('telefono') <p class="rm-error">{{ $message }}</p> @enderror
   </div>
   </x-ui.form-section>

   <x-ui.form-section title="DIRECCIÓN DE DOMICILIO" description="Ubicación geográfica del domicilio personal del usuario." :columns="2">
   <div>
    <label class="rm-label rm-label-required">Departamento</label>
    <select wire:model.live="departamento_domicilio" class="rm-select {{ $errors->has('departamento_domicilio') ? 'is-invalid' : '' }}">
    <option value="">SELECCIONE DEPARTAMENTO...</option>
    @foreach(array_keys($catalogDepartamentos) as $dept)
     <option value="{{ $dept }}">{{ $dept }}</option>
    @endforeach
    </select>
    @error('departamento_domicilio') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   @if($departamento_domicilio === 'OTRO')
    <div>
    <label class="rm-label rm-label-required">Especifique Departamento</label>
    <input type="text" wire:model="otro_departamento" placeholder="Especifique el Departamento..."
     class="rm-input uppercase {{ $errors->has('otro_departamento') ? 'is-invalid' : '' }}">
    @error('otro_departamento') <p class="rm-error">{{ $message }}</p> @enderror
    </div>
   @endif

   <div>
    <label class="rm-label rm-label-required">Municipio / Localidad</label>
    @if($departamento_domicilio === 'OTRO')
    <input type="text" wire:model="otro_municipio" placeholder="Especifique Municipio..."
     class="rm-input uppercase {{ $errors->has('otro_municipio') ? 'is-invalid' : '' }}">
    @error('otro_municipio') <p class="rm-error">{{ $message }}</p> @enderror
    @else
    <select wire:model.live="municipio_domicilio" class="rm-select {{ $errors->has('municipio_domicilio') ? 'is-invalid' : '' }}">
     <option value="">SELECCIONE MUNICIPIO...</option>
     @if($departamento_domicilio && isset($catalogDepartamentos[$departamento_domicilio]))
     @foreach($catalogDepartamentos[$departamento_domicilio] as $muni)
      <option value="{{ $muni }}">{{ $muni }}</option>
     @endforeach
     @endif
    </select>
    @error('municipio_domicilio') <p class="rm-error">{{ $message }}</p> @enderror
    @endif
   </div>

   <div>
    <label class="rm-label rm-label-required">Zona / Barrio</label>
    @if(isset($catalogZonas[$municipio_domicilio]))
    <select wire:model.live="zona_domicilio" class="rm-select {{ $errors->has('zona_domicilio') ? 'is-invalid' : '' }}">
     <option value="">SELECCIONE ZONA...</option>
     @foreach($catalogZonas[$municipio_domicilio] as $z)
     <option value="{{ $z }}">{{ $z }}</option>
     @endforeach
    </select>
    @error('zona_domicilio') <p class="rm-error">{{ $message }}</p> @enderror
    @else
    <input type="text" wire:model="otra_zona" placeholder="Ej. Sopocachi"
     class="rm-input uppercase {{ $errors->has('otra_zona') ? 'is-invalid' : '' }}">
    @error('otra_zona') <p class="rm-error">{{ $message }}</p> @enderror
    @endif
   </div>

   <div>
    <label class="rm-label rm-label-required">Calle / Avenida</label>
    <input type="text" wire:model="calle" placeholder="Ej. Av. Arce o Calle Murillo"
    class="rm-input uppercase {{ $errors->has('calle') ? 'is-invalid' : '' }}">
    @error('calle') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label rm-label-required">Número Domicilio</label>
    <input type="text" wire:model="nro_domicilio" placeholder="Ej. 1234 o S/N"
    class="rm-input uppercase {{ $errors->has('nro_domicilio') ? 'is-invalid' : '' }}">
    @error('nro_domicilio') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div class="col-span-full">
    <label class="rm-label">Referencia de Domicilio</label>
    <input type="text" wire:model="referencia_domicilio" placeholder="Ej. Frente al centro de salud"
    class="rm-input uppercase {{ $errors->has('referencia_domicilio') ? 'is-invalid' : '' }}">
    @error('referencia_domicilio') <p class="rm-error">{{ $message }}</p> @enderror
   </div>
   </x-ui.form-section>

   <x-ui.form-section title="CONTACTO DE EMERGENCIA" description="Persona de contacto en caso de contingencia o urgencia." :columns="3">
   <div>
    <label class="rm-label rm-label-required">Nombres Emergencia</label>
    <input type="text" wire:model="contacto_emergencia" placeholder="Ej. María Teresa"
    class="rm-input uppercase {{ $errors->has('contacto_emergencia') ? 'is-invalid' : '' }}">
    @error('contacto_emergencia') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label rm-label-required">Apellido Paterno</label>
    <input type="text" wire:model="ap_paterno_emergencia" placeholder="Ej. López"
    class="rm-input uppercase {{ $errors->has('ap_paterno_emergencia') ? 'is-invalid' : '' }}">
    @error('ap_paterno_emergencia') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label">Apellido Materno</label>
    <input type="text" wire:model="ap_materno_emergencia" placeholder="Ej. Quispe"
    class="rm-input uppercase {{ $errors->has('ap_materno_emergencia') ? 'is-invalid' : '' }}">
    @error('ap_materno_emergencia') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label">Parentesco / Relación</label>
    <select wire:model="parentesco_emergencia" class="rm-select">
    <option value="">SELECCIONE...</option>
    <option value="PADRE">PADRE</option>
    <option value="MADRE">MADRE</option>
    <option value="CONYUGUE">CONYUGUE</option>
    <option value="HIJO/A">HIJO/A</option>
    <option value="HERMANO/A">HERMANO/A</option>
    <option value="OTRO">OTRO</option>
    </select>
    @error('parentesco_emergencia') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div class="md:col-span-2">
    <label class="rm-label">Celular de Emergencia</label>
    <input type="text" wire:model="celular_emergencia" placeholder="Ej. 70098765"
    class="rm-input {{ $errors->has('celular_emergencia') ? 'is-invalid' : '' }}">
    @error('celular_emergencia') <p class="rm-error">{{ $message }}</p> @enderror
   </div>
   </x-ui.form-section>
  </div>
  @endif

  {{-- PASO 3: ROL Y PERFIL INSTITUCIONAL --}}
  @if($pasoFormulario === 3)
  <div class="space-y-5 animate-in fade-in duration-200">
   <x-ui.form-section title="ROL Y CONTEXTO INSTITUCIONAL" description="Asignación del perfil operativo y área de desempeño." :columns="2">
   <div>
    <label class="rm-label rm-label-required">Tipo de Usuario / Rol Institucional</label>
    <select wire:model.live="rol" class="rm-select {{ $errors->has('rol') ? 'is-invalid' : '' }}">
    <option value="">SELECCIONE TIPO DE USUARIO...</option>
    @foreach($roles as $rolDisponible)
     @php
     $nombreParaSelect = match($rolDisponible->name) {
      'FAMILIAR' => 'Familiar / Responsable',
      default => mb_convert_case(str_replace('_', ' ', $rolDisponible->name), MB_CASE_TITLE, 'UTF-8')
     };
     @endphp
     <option value="{{ $rolDisponible->name }}">{{ $nombreParaSelect }}</option>
    @endforeach
    </select>
    @error('rol') <p class="rm-error">{{ $message }}</p> @enderror
   </div>

   <div>
    <label class="rm-label">Área Institucional Operativa</label>
    <select wire:model="cod_area" class="rm-select {{ $errors->has('cod_area') ? 'is-invalid' : '' }}">
    <option value="">SELECCIONE ÁREA...</option>
    @foreach($areas as $ar)
     @if($ar->cod_area !== 'ARE_0009')
     <option value="{{ $ar->cod_area }}">{{ $ar->nombre }}</option>
     @endif
    @endforeach
    </select>
    @error('cod_area') <p class="rm-error">{{ $message }}</p> @enderror
   </div>
   </x-ui.form-section>

   {{-- Perfil de Salud --}}
   @if(in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']))
   <x-ui.form-section title="PERFIL ASISTENCIAL DE SALUD" description="Especialidad clínica y fecha de ingreso." :columns="2">
    <div>
    <label class="rm-label rm-label-required">Especialidad Médica / Área de Salud</label>
    <select wire:model.live="especialidad_salud" class="rm-select {{ $errors->has('especialidad_salud') ? 'is-invalid' : '' }}">
     <option value="">SELECCIONE ESPECIALIDAD...</option>
     @foreach($especialidades as $esp)
     <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
     @endforeach
    </select>
    @error('especialidad_salud') <p class="rm-error">{{ $message }}</p> @enderror
    </div>

    <div>
    <label class="rm-label rm-label-required">Fecha de Ingreso</label>
    <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }}
     class="rm-input {{ !$isEdit ? 'opacity-75 pointer-events-none' : '' }} {{ $errors->has('fecha_ingreso') ? 'is-invalid' : '' }}">
    @error('fecha_ingreso') <p class="rm-error">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-full">
    <label class="rm-label">Institución de Formación</label>
    <input type="text" wire:model="institucion_formacion" placeholder="Ej. Universidad Mayor de San Andrés"
     class="rm-input uppercase {{ $errors->has('institucion_formacion') ? 'is-invalid' : '' }}">
    @error('institucion_formacion') <p class="rm-error">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-full p-3 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-xs text-[var(--rm-text-secondary)]">
    La matrícula profesional y documentos de respaldo se gestionarán desde el módulo documental del usuario.
    </div>
   </x-ui.form-section>
   @endif

   {{-- Perfil Admin --}}
   @if(in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']))
   <x-ui.form-section title="CARGO ADMINISTRATIVO" description="Designación operativa dentro de la administración del centro." :columns="2">
    <div>
    <label class="rm-label rm-label-required">Cargo Administrativo</label>
    @if($cargosAdmin->isEmpty())
     <div class="p-3 rounded-xl bg-[var(--rm-danger-soft)]/20 border border-[var(--rm-danger)] text-xs font-semibold text-[var(--rm-danger)]">
     No hay cargos administrativos registrados. Por favor registre cargos previamente.
     </div>
    @else
     <select wire:model.live="cargo_administrativo" class="rm-select {{ $errors->has('cargo_administrativo') ? 'is-invalid' : '' }}">
     <option value="">SELECCIONE CARGO...</option>
     @foreach($cargosAdmin as $cargo)
      <option value="{{ $cargo->cod_cargo_admin }}">{{ $cargo->nombre }}</option>
     @endforeach
     </select>
     @error('cargo_administrativo') <p class="rm-error">{{ $message }}</p> @enderror
    @endif
    </div>

    <div>
    <label class="rm-label rm-label-required">Fecha de Ingreso</label>
    <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }}
     class="rm-input {{ !$isEdit ? 'opacity-75 pointer-events-none' : '' }} {{ $errors->has('fecha_ingreso') ? 'is-invalid' : '' }}">
    @error('fecha_ingreso') <p class="rm-error">{{ $message }}</p> @enderror
    </div>
   </x-ui.form-section>
   @endif

   {{-- Perfil Familiar --}}
   @if($rol === 'FAMILIAR')
   <x-ui.form-section title="VINCULACIÓN CON ADULTO MAYOR" description="Asociación del familiar con residentes admitidos en el centro." :columns="1">
    <div class="p-4 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] space-y-4">
    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 items-end">
     <div class="sm:col-span-2">
     <label class="rm-label rm-label-required">Adulto Mayor</label>
     <select wire:model="selected_cod_residente" class="rm-select">
      <option value="">-- Seleccionar Adulto Mayor Disponible --</option>
      @foreach($residentesDisponibles as $am)
      @php
       $adultoNombre = trim(($am->nombres ?? '') . ' ' . ($am->ap_paterno ?? '') . ' ' . ($am->ap_materno ?? ''));
       $adultoDocumento = $am->ci ? 'CI ' . trim(($am->ci ?? '') . ' ' . ($am->expedicion_ci ?? '')) : 'SIN DOCUMENTO';
       $adultoEdad = $am->fecha_nac ? ' - ' . \Carbon\Carbon::parse($am->fecha_nac)->age . ' AÑOS' : '';
      @endphp
      <option value="{{ $am->cod_residente }}">{{ mb_strtoupper($adultoNombre . ' - ' . $adultoDocumento . $adultoEdad, 'UTF-8') }}</option>
      @endforeach
     </select>
     @error('selected_cod_residente') <p class="rm-error">{{ $message }}</p> @enderror
     </div>

     <div>
     <label class="rm-label rm-label-required">Parentesco / Vínculo</label>
     <select wire:model="selected_parentesco" class="rm-select">
      <option value="HIJO/A">HIJO/A</option>
      <option value="CONYUGE">CONYUGE</option>
      <option value="NIETO/A">NIETO/A</option>
      <option value="HERMANO/A">HERMANO/A</option>
      <option value="SOBRINO/A">SOBRINO/A</option>
      <option value="TUTOR">TUTOR</option>
      <option value="OTRO">OTRO</option>
     </select>
     @error('selected_parentesco') <p class="rm-error">{{ $message }}</p> @enderror
     </div>

     <div class="flex flex-col gap-1.5 justify-center pt-1">
     <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-[var(--rm-text-primary)]">
      <input type="checkbox" wire:model="selected_es_responsable" class="rounded text-[var(--rm-primary)]">
      <span>Resp. Principal</span>
     </label>
     <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-[var(--rm-text-primary)]">
      <input type="checkbox" wire:model="selected_responsable_salud" class="rounded text-[var(--rm-primary)]">
      <span>Resp. Salud</span>
     </label>
     <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-[var(--rm-text-primary)]">
      <input type="checkbox" wire:model="selected_responsable_economico" class="rounded text-[var(--rm-primary)]">
      <span>Resp. Económico</span>
     </label>
     </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-4 items-end">
     <div class="sm:col-span-3">
     <label class="rm-label">Notas / Observaciones del Vínculo</label>
     <input type="text" wire:model="selected_observaciones" placeholder="Ej. A cargo del seguimiento médico semanal"
      class="rm-input uppercase">
     </div>
     <button type="button" wire:click="vincularAdultoMayor"
     class="h-[44px] rounded-xl bg-[var(--rm-primary)] hover:bg-[var(--rm-primary-hover)] text-white text-xs font-bold uppercase tracking-wider transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
     <i class="ph-bold ph-plus-circle text-base"></i> Vincular
     </button>
    </div>
    </div>

    {{-- Tabla de Vinculados --}}
    @if(count($vinculosFamiliar) > 0)
    <div class="overflow-x-auto rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface)] mt-3">
     <table class="rm-data-table rm-data-table--actions w-full text-left text-xs">
     <thead class="bg-[var(--rm-surface-soft)] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)] border-b border-[var(--rm-border-soft)]">
      <tr>
      <th class="px-3 py-2.5">Adulto Mayor</th>
      <th class="px-3 py-2.5">Parentesco</th>
      <th class="px-3 py-2.5 text-center">Responsabilidades</th>
      <th class="px-3 py-2.5">Observaciones</th>
      <th class="px-3 py-2.5 text-center">Acciones</th>
      </tr>
     </thead>
     <tbody class="divide-y divide-[var(--rm-border-soft)]">
      @foreach($vinculosFamiliar as $i => $v)
      <tr class="hover:bg-[var(--rm-surface-soft)] transition">
       <td class="px-3 py-2.5 font-bold text-[var(--rm-text-primary)]">
       {{ $v['nombres_completos'] }}
       </td>
       <td class="px-3 py-2.5">
       <span class="px-2 py-0.5 rounded-full bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] font-bold text-[10px] uppercase">
        {{ $v['parentesco_vinculo'] }}
       </span>
       </td>
       <td class="px-3 py-2.5 text-center">
       <div class="flex items-center justify-center gap-1">
        @if($v['es_responsable'] === 'SI')
        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-success-soft)] text-[var(--rm-success)] font-extrabold text-[9px]">PPAL</span>
        @endif
        @if(($v['responsable_salud'] ?? 'NO') === 'SI')
        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-info-soft)] text-[var(--rm-info)] font-extrabold text-[9px]">SALUD</span>
        @endif
        @if(($v['responsable_economico'] ?? 'NO') === 'SI')
        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-olive-soft)] text-[var(--rm-olive-strong)] font-extrabold text-[9px]">ECON</span>
        @endif
       </div>
       </td>
       <td class="px-3 py-2.5 text-[var(--rm-text-secondary)]">
       {{ $v['observaciones'] ?: 'Sin observaciones' }}
       </td>
       <td class="px-3 py-2.5 text-center">
       <button type="button" wire:click="desvincularAdultoMayor({{ $i }})"
        class="h-7 w-7 rounded-lg bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] hover:bg-[var(--rm-danger)] hover:text-white transition inline-flex items-center justify-center cursor-pointer shadow-xs"
        title="Eliminar vinculación">
        <i class="ph-bold ph-trash text-sm"></i>
       </button>
       </td>
      </tr>
      @endforeach
     </tbody>
     </table>
    </div>
    @endif

    <div class="mt-2">
    <label class="rm-label">Observación General de Vinculación</label>
    <input type="text" wire:model="observacion_vinculo" placeholder="Ej. Tutor legal de los adultos vinculados"
     class="rm-input uppercase">
    @error('observacion_vinculo') <p class="rm-error">{{ $message }}</p> @enderror
    </div>
   </x-ui.form-section>
   @endif
  </div>
  @endif

  {{-- PASO 4: ACCESO AL SISTEMA / SEGURIDAD --}}
  @if($pasoFormulario === 4)
  <div class="space-y-5 animate-in fade-in duration-200" x-data="{ showPass: false, showConfirm: false, showGenPass: false, showActual: false }">
   <x-ui.form-section title="ACCESO AL SISTEMA Y CREDENCIALES" description="Mecanismos de autenticación y claves seguras institucionales." :columns="1">
   @if(!$isEdit)
    <div class="space-y-4">
    <div class="p-4 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] space-y-1">
     <h4 class="font-bold text-xs uppercase tracking-wider text-[var(--rm-text-primary)] flex items-center gap-1.5">
     <i class="ph-bold ph-key text-[var(--rm-primary)]"></i> Contraseña Temporal de Alta Seguridad
     </h4>
     <p class="text-xs text-[var(--rm-text-secondary)]">
     Esta es la contraseña generada automáticamente por el sistema para el nuevo usuario. Puede copiarla o regenerarla antes de confirmar el alta.
     </p>
    </div>

    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-4 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border)]">
     <div class="relative flex-1">
     <input :type="showGenPass ? 'text' : 'password'"
      value="{{ $passwordTemporalVisual }}"
      readonly
      class="rm-input font-mono font-bold tracking-widest text-base pr-20 bg-[var(--rm-input-bg)]">
     <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
      <button type="button" @click="showGenPass = !showGenPass"
      class="h-8 w-8 flex items-center justify-center rounded-lg text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] transition"
      title="Mostrar / Ocultar">
      <i class="ph-bold text-base" :class="showGenPass ? 'ph-eye-slash' : 'ph-eye'"></i>
      </button>
      <button type="button"
      onclick="navigator.clipboard.writeText('{{ $passwordTemporalVisual }}'); Swal.fire({ icon: 'success', title: 'Copiado', text: 'Contraseña temporal copiada al portapapeles.', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-[1.5rem]' } })"
      class="h-8 w-8 flex items-center justify-center rounded-lg text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] transition cursor-pointer"
      title="Copiar">
      <i class="ph-bold ph-copy text-base"></i>
      </button>
     </div>
     </div>

     <button type="button" wire:click="regenerarPasswordTemporal"
     class="h-[44px] px-4 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[var(--rm-primary)] hover:bg-[var(--rm-primary-hover)] text-white text-xs font-bold uppercase tracking-wider transition shadow-xs cursor-pointer shrink-0">
     <i class="ph-bold ph-arrows-clockwise text-base"></i>
     <span>Regenerar clave</span>
     </button>
    </div>

    <div class="p-3.5 rounded-xl bg-[var(--rm-olive-soft)]/20 border border-[var(--rm-olive)]/40 text-xs font-semibold text-[var(--rm-olive-strong)] leading-relaxed">
     NOTA INSTITUCIONAL: Se enviará automáticamente un correo electrónico de bienvenida con las credenciales de acceso inicial y la directiva obligatoria de cambio de contraseña al primer ingreso.
    </div>
    </div>
   @else
    @if($usuarioId === auth()->id())
    <div class="grid gap-4 md:grid-cols-3">
     <div>
     <label class="rm-label rm-label-required">Contraseña Actual</label>
     <div class="relative">
      <input :type="showActual ? 'text' : 'password'" wire:model="password_actual" placeholder="••••••••"
      class="rm-input pr-10 {{ $errors->has('password_actual') ? 'is-invalid' : '' }}">
      <button type="button" @click="showActual = !showActual" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-secondary)]">
      <i class="ph-bold text-base" :class="showActual ? 'ph-eye-slash' : 'ph-eye'"></i>
      </button>
     </div>
     @error('password_actual') <p class="rm-error">{{ $message }}</p> @enderror
     </div>

     <div>
     <label class="rm-label">Nueva Contraseña</label>
     <div class="relative">
      <input :type="showPass ? 'text' : 'password'" wire:model="password" placeholder="••••••••"
      class="rm-input pr-10 {{ $errors->has('password') ? 'is-invalid' : '' }}">
      <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-secondary)]">
      <i class="ph-bold text-base" :class="showPass ? 'ph-eye-slash' : 'ph-eye'"></i>
      </button>
     </div>
     @error('password') <p class="rm-error">{{ $message }}</p> @enderror
     </div>

     <div>
     <label class="rm-label">Confirmar Contraseña</label>
     <div class="relative">
      <input :type="showConfirm ? 'text' : 'password'" wire:model="password_confirmation" placeholder="••••••••"
      class="rm-input pr-10">
      <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-secondary)]">
      <i class="ph-bold text-base" :class="showConfirm ? 'ph-eye-slash' : 'ph-eye'"></i>
      </button>
     </div>
     </div>
    </div>
    @else
    <div class="flex flex-col items-center justify-center p-6 bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] rounded-2xl space-y-3 text-center">
     <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--rm-primary)] text-white shadow-sm">
     <i class="ph-bold ph-key text-xl"></i>
     </div>
     <div class="max-w-md">
     <h4 class="text-sm font-bold text-[var(--rm-text-primary)] uppercase tracking-wider">Restablecimiento de Credenciales</h4>
     <p class="mt-1 text-xs text-[var(--rm-text-secondary)] leading-relaxed">
      Por políticas de privacidad, no se puede visualizar ni editar directamente la contraseña actual de otro usuario.
      Presione el botón para generar una clave temporal que será notificada por correo institucional.
     </p>
     </div>
     <button type="button"
     wire:click="restablecerPasswordUsuario('{{ $usuarioId }}')"
     wire:confirm="¿Está seguro de que desea restablecer la contraseña de este usuario? Se generará una clave temporal y se le enviará por correo."
     class="inline-flex items-center gap-2 rounded-xl bg-[var(--rm-primary)] hover:bg-[var(--rm-primary-hover)] text-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider shadow-sm transition cursor-pointer">
     <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i>
     <span>Restablecer Contraseña Temporal</span>
     </button>
    </div>
    @endif
   @endif
   </x-ui.form-section>
  </div>
  @endif

  {{-- PASO 5: CONFIRMACIÓN Y RESUMEN --}}
  @if($pasoFormulario === 5)
  <div class="space-y-5 animate-in fade-in duration-200" x-data="{ showPassSummary: false }">
   <x-ui.form-section title="RESUMEN DE REGISTRO" description="Verifique los datos institucionales antes de confirmar." :columns="1">
   <div class="grid gap-4 lg:grid-cols-2">
    <div class="rounded-xl bg-[var(--rm-surface-soft)] p-4 border border-[var(--rm-border-soft)] space-y-3">
    <div class="flex items-center gap-4">
     @if($foto_de_perfil_upload)
     <img src="{{ $foto_de_perfil_upload->temporaryUrl() }}"
      class="h-16 w-16 rounded-xl object-cover ring-2 ring-[var(--rm-primary)] shadow-sm">
     @elseif($isEdit && ($cod_usuario ?? $usuarioId) && \App\Models\User::find($cod_usuario ?? $usuarioId)?->foto_de_perfil)
     <img src="{{ asset('storage/' . \App\Models\User::find($cod_usuario ?? $usuarioId)->foto_de_perfil) }}"
      class="h-16 w-16 rounded-xl object-cover ring-2 ring-[var(--rm-border)] shadow-sm">
     @else
     <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-lg font-black text-[var(--rm-primary)] shadow-sm uppercase">
      {{ mb_substr($nombres ?? 'U', 0, 1) }}{{ mb_substr($ap_paterno ?? 'I', 0, 1) }}
     </div>
     @endif
     <div>
     <h4 class="text-sm font-extrabold text-[var(--rm-text-primary)] uppercase leading-tight">
      {{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}
     </h4>
     <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full bg-[var(--rm-primary)] text-white text-[10px] font-bold uppercase tracking-wider">
      {{ str_replace('_', ' ', $rol) }}
     </span>
     </div>
    </div>

    <div class="grid grid-cols-2 gap-2.5 border-t border-[var(--rm-border-soft)] pt-3 text-xs">
     <div>
     <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Documento</p>
     <p class="font-bold text-[var(--rm-text-primary)] uppercase mt-0.5">{{ $numero_documento }} {{ $expedido }}</p>
     </div>
     <div>
     <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">País Emisor</p>
     <p class="font-bold text-[var(--rm-text-primary)] uppercase mt-0.5">{{ $pais_documento }}</p>
     </div>
     <div>
     <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Celular</p>
     <p class="font-bold text-[var(--rm-text-primary)] mt-0.5">{{ $codigo_telefono }} {{ $telefono }}</p>
     </div>
     <div>
     <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Correo</p>
     <p class="font-bold text-[var(--rm-text-primary)] lowercase mt-0.5 truncate">{{ $correo }}</p>
     </div>
    </div>
    </div>

    <div class="rounded-xl bg-[var(--rm-surface-soft)] p-4 border border-[var(--rm-border-soft)] flex flex-col justify-between space-y-3">
    @if(!$isEdit && $passwordTemporalVisual)
     <div class="p-3 bg-[var(--rm-surface)] border border-[var(--rm-border)] rounded-xl text-center space-y-1">
     <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)] block">Clave Temporal Asignada:</span>
     <div class="flex items-center justify-center gap-2 mt-1">
      <div class="text-sm font-mono font-black text-[var(--rm-text-primary)] bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] px-3 py-1.5 rounded-lg select-all">
      <span x-text="showPassSummary ? '{{ $passwordTemporalVisual }}' : '••••••••••••'"></span>
      </div>
      <button type="button" @click="showPassSummary = !showPassSummary"
      class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-secondary)]">
      <i class="ph-bold" :class="showPassSummary ? 'ph-eye-slash' : 'ph-eye'"></i>
      </button>
     </div>
     </div>
    @endif

    <p class="text-xs text-[var(--rm-text-secondary)] text-center leading-relaxed italic">
     Al confirmar, se guardará y sincronizará la cuenta institucional de este usuario en el sistema.
    </p>
    </div>
   </div>
   </x-ui.form-section>
  </div>
  @endif

 </div>

 {{-- FOOTER FIJO CON BOTONES CANÓNICOS --}}
 <footer class="border-t border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] px-5 py-3.5 shrink-0 flex flex-col-reverse sm:flex-row items-center justify-between gap-2.5">
  <button type="button" wire:click="cerrarFormulario"
  class="rm-btn rm-btn-secondary w-full sm:w-auto cursor-pointer">
  Cancelar
  </button>

  <div class="flex items-center gap-2.5 w-full sm:w-auto">
  @if($pasoFormulario > 1)
   <button type="button" wire:click="anteriorPaso"
   class="rm-btn rm-btn-secondary flex-1 sm:flex-none cursor-pointer">
   <i class="ph-bold ph-arrow-left"></i>
   <span>Anterior</span>
   </button>
  @endif

  @if($pasoFormulario < 5)
   <button type="button" wire:click="siguientePaso"
   class="rm-btn rm-btn-accent flex-1 sm:flex-none px-8 cursor-pointer shadow-md">
   <span>Continuar</span>
   <i class="ph-bold ph-arrow-right"></i>
   </button>
  @else
   <button type="button" wire:click="guardarUsuario"
   wire:loading.attr="disabled"
   wire:target="guardarUsuario"
   class="rm-btn rm-btn-accent flex-1 sm:flex-none px-8 cursor-pointer shadow-md inline-flex items-center justify-center gap-2 min-w-[140px]">
   <span wire:loading wire:target="guardarUsuario" class="animate-spin h-4 w-4">
    <i class="ph-bold ph-spinner"></i>
   </span>
   <span wire:loading.remove wire:target="guardarUsuario">
    <i class="ph-bold {{ $usuarioId ? 'ph-floppy-disk' : 'ph-check' }}"></i>
   </span>
   <span>{{ $usuarioId ? 'Actualizar Usuario' : 'Confirmar Registro' }}</span>
   </button>
  @endif
  </div>
 </footer>

 </div>
</div>
@endif
