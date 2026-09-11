@if($mostrarFormulario)
 <div class="fixed inset-0 z-[2147483646] flex items-center justify-center bg-boton-principal/50 backdrop-blur-sm px-3 sm:px-4 transition-all duration-300">
 <div class="relative z-[2147483647] w-full max-w-4xl max-h-[92vh] overflow-hidden rounded-[24px] border border-borde-suave bg-fondo-app shadow-[0_20px_50px_rgba(0,0,0,0.5)] animate-in fade-in zoom-in duration-300 flex flex-col">
 
 {{-- HEADER CON PROGRESO --}}
 <header class="relative border-b border-borde-suave bg-fondo-panel px-4 py-3 backdrop-blur-xl shrink-0">
 <div class="flex items-center justify-between gap-4 mb-2">
 <div class="flex items-center gap-3">
 <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-principal text-inverso shadow-lg">
 <i class="ph-bold {{ $isEdit ? 'ph-pencil-simple' : 'ph-user-plus' }} text-lg"></i>
 </div>
 <div>
 <h2 class="text-base font-extrabold text-parrafo">
 {{ $isEdit ? 'Actualizar' : 'Registro de' }} <span class="text-boton-acento">Personal</span>
 </h2>
 </div>
 </div>
 <button type="button" wire:click="cerrarFormulario" class="group flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-app text-parrafo transition-all hover:bg-boton-acento hover:text-inverso active:scale-90 shadow-sm">
 <i class="ph-bold ph-x text-base transition group-hover:rotate-90"></i>
 </button>
 </div>
 {{-- BARRA DE PASOS VISUAL MEJORADA --}}
 <div class="relative px-4 py-4 sm:px-8 bg-fondo-panel border-b border-borde/10 hidden sm:block">
 <div class="relative flex items-center justify-between max-w-3xl mx-auto">
 {{-- Línea de fondo --}}
 <div class="absolute top-1/2 left-0 w-full h-1 bg-fondo-panel -translate-y-1/2 rounded-full"></div>
 {{-- Línea de progreso activa --}}
 <div class="absolute top-1/2 left-0 h-1 bg-boton-acento -translate-y-1/2 rounded-full transition-all duration-700 ease-out shadow-[0_0_8px_rgba(226,125,96,0.5)]" 
 style="width: {{ (($pasoFormulario - 1) / 4) * 100 }}%"></div>
 
 {{-- Pasos --}}
 @php
 $pasosUsu = [
 1 => ['i' => 'ph-user-circle', 'l' => 'Identidad'],
 2 => ['i' => 'ph-phone-call', 'l' => 'Contacto'],
 3 => ['i' => 'ph-briefcase', 'l' => 'Perfil'],
 4 => ['i' => 'ph-shield-check', 'l' => 'Seguridad'],
 5 => ['i' => 'ph-check-square', 'l' => 'Finalizar']
 ];
 @endphp

 @foreach($pasosUsu as $s => $p)
 <div class="relative flex flex-col items-center group">
 <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl border-2 transition-all duration-500
 {{ $pasoFormulario > $s ? 'bg-estado-exitoBg border-estado-exitoBorde text-inverso' : 
 ($pasoFormulario == $s ? 'bg-fondo-card border-borde-focus text-boton-acento shadow-lg scale-110' : 
 'bg-fondo-app border-borde text-parrafo/30') }}">
 
 <i class="ph-bold {{ $p['i'] }} text-base transition-all duration-500 {{ $pasoFormulario == $s ? 'scale-110' : '' }}"></i>
 
 @if($pasoFormulario > $s)
 <div class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-boton-principal text-inverso shadow-sm border border-borde-suave">
 <i class="ph-bold ph-check text-[8px]"></i>
 </div>
 @endif
 </div>
 <span class="absolute -bottom-7 whitespace-nowrap text-[8px] font-black uppercase tracking-tighter transition-all duration-500 
 {{ $pasoFormulario >= $s ? 'text-parrafo opacity-100' : 'text-parrafo/30 opacity-60' }} {{ $pasoFormulario == $s ? 'text-boton-acento -translate-y-0.5' : '' }}">
 {{ $p['l'] }}
 </span>
 </div>
 @endforeach
 </div>
 </div>
 </header>

 {{-- CONTENIDO --}}
 <div class="flex-1 max-h-[62vh] overflow-y-auto custom-scrollbar px-4 py-3">
 
 {{-- PASO 1: IDENTIDAD --}}
 @if($pasoFormulario === 1)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-identification-card text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Información Personal</h3>
 </div>
 
 {{-- Contenedor de Fotografía y Carga --}}
 <div class="flex flex-col sm:flex-row items-center gap-5 bg-fondo-card/40 p-4 rounded-2xl border border-borde-suave">
 <div class="relative group">
 @if($foto_de_perfil_upload)
 <img src="{{ $foto_de_perfil_upload->temporaryUrl() }}" 
 class="h-24 w-24 rounded-[1.35rem] object-cover ring-4 ring-[#E27D60] shadow-md">
 @elseif($isEdit && $cod_usu && \App\Models\User::find($cod_usu)?->foto_de_perfil)
 <img src="{{ asset('storage/' . \App\Models\User::find($cod_usu)->foto_de_perfil) }}" 
 class="h-24 w-24 rounded-[1.35rem] object-cover ring-4 ring-[#2F3E5C]/30 shadow-md">
 @else
 <div class="flex h-24 w-24 items-center justify-center rounded-[1.35rem] bg-boton-principal text-3xl font-black text-inverso ring-4 ring-[#2F3E5C]/10 shadow-md uppercase">
 {{ mb_substr($nombres ?? 'U', 0, 1) }}{{ mb_substr($ap_paterno ?? 'I', 0, 1) }}
 </div>
 @endif
 <div wire:loading wire:target="foto_de_perfil_upload" class="absolute inset-0 flex items-center justify-center bg-boton-principal/60 rounded-[1.35rem]">
 <i class="ph-bold ph-circle-notch animate-spin text-inverso text-xl"></i>
 </div>
 </div>
 <div class="flex-1 text-center sm:text-left space-y-1">
 <h4 class="text-xs font-bold text-parrafo uppercase tracking-wider">Fotografía Institucional</h4>
 <p class="text-[10px] text-apoyo font-semibold leading-relaxed">
 Formatos permitidos: JPG, JPEG, PNG, WEBP. Tamaño máximo: 4MB.
 </p>
 <label class="inline-flex items-center gap-2 px-3 py-1.5 bg-boton-principal hover:bg-boton-acento text-inverso rounded-lg text-[9px] font-bold uppercase tracking-wider cursor-pointer shadow transition active:scale-95">
 <i class="ph-bold ph-upload-simple"></i> Seleccionar foto
 <input type="file" wire:model="foto_de_perfil_upload" class="hidden" accept="image/*">
 </label>
 @error('foto_de_perfil_upload') 
 <span class="block text-[9px] font-bold text-boton-acento uppercase mt-1">{{ $message }}</span> 
 @enderror
 </div>
 </div>

 <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
 <div class="md:col-span-2 lg:col-span-3">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nombres *</label>
 <input type="text" wire:model="nombres" placeholder="Ej. Carla Valeria"
 class="w-full h-10 rounded-xl border {{ $errors->has('nombres') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 @error('nombres') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Paterno *</label>
 <input type="text" wire:model="ap_paterno" placeholder="Paterno"
 class="w-full h-10 rounded-xl border {{ $errors->has('ap_paterno') ? 'border-borde-focus' : 'border-borde' }} focus:border-borde-fuerte bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 @error('ap_paterno') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Materno</label>
 <input type="text" wire:model="ap_materno" placeholder="Materno"
 class="w-full h-10 rounded-xl border border-borde focus:border-borde-fuerte bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Género *</label>
 <select wire:model="genero" class="w-full h-10 rounded-xl border {{ $errors->has('genero') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE...</option>
 <option value="FEMENINO">FEMENINO</option>
 <option value="MASCULINO">MASCULINO</option>
 </select>
 @error('genero') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <div class="flex justify-between items-center mb-1">
 <label class="block text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha Nacimiento *</label>
 @if($edad !== null)
 <span class="text-[9px] font-bold text-boton-acento uppercase tracking-wider">Edad: {{ $edad }} años</span>
 @endif
 </div>
 <input type="date" wire:model.live="fecha_nacimiento"
 class="w-full h-10 rounded-xl border {{ $errors->has('fecha_nacimiento') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 @error('fecha_nacimiento') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">País Emisor *</label>
 <select wire:model.live="pais_documento" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 @foreach(array_keys($paisesConfig) as $pName)
 <option value="{{ mb_strtoupper($pName, 'UTF-8') }}">{{ mb_strtoupper($pName, 'UTF-8') }}</option>
 @endforeach
 </select>
 </div>
 <div class="md:col-span-2 lg:col-span-3 grid gap-3 {{ (mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI') ? 'grid-cols-12' : 'grid-cols-3' }}">
 <div class="{{ (mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI') ? 'col-span-3' : 'col-span-1' }} {{ mb_strtoupper((string) $pais_documento, 'UTF-8') !== 'OTRO' ? 'pointer-events-none opacity-60' : '' }}">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Tipo *</label>
 <select wire:model.live="tipo_documento" tabindex="-1" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
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
 <div class="{{ (mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI') ? 'col-span-6' : 'col-span-2' }}">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Número Doc *</label>
 <input type="text" wire:model="numero_documento" placeholder="Ej. 1234567"
 class="w-full h-10 rounded-xl border {{ $errors->has('numero_documento') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 </div>
 @if(mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI')
 <div class="col-span-3">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-boton-acento">Expedido (EXP) *</label>
 <select wire:model="expedido" class="w-full h-10 rounded-xl border {{ $errors->has('expedido') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <option value="">SELECCIONE...</option>
 @foreach(['LP','CB','SC','OR','PT','CH','TJ','BN','PD'] as $e)
 <option value="{{ $e }}">{{ $e }}</option>
 @endforeach
 </select>
 </div>
 @endif
 @error('numero_documento') <div class="col-span-full"><span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span></div> @enderror
 @error('expedido') <div class="col-span-full"><span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span></div> @enderror
 @if(mb_strtoupper((string) $pais_documento, 'UTF-8') !== 'OTRO')
 <div class="col-span-full mt-1">
 <p class="text-[9px] font-semibold text-meta italic">
 * El tipo de documento se bloquea y pre-asigna automáticamente según el país emisor seleccionado.
 </p>
 </div>
 @endif
 </div>
 </div>
 </div>
 @endif

 {{-- PASO 2: CONTACTO Y DOMICILIO --}}
 @if($pasoFormulario === 2)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-phone-call text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Contacto y Domicilio</h3>
 </div>
 <div class="grid gap-4 md:grid-cols-2">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Correo Institucional *</label>
 <input type="email" wire:model.live="correo" placeholder="ejemplo@jardindelosrecuerdos.org"
 class="w-full h-10 rounded-xl border {{ $errors->has('correo') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 @error('correo') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <div class="grid grid-cols-12 gap-2">
 <div class="col-span-5">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">País Celular</label>
 <select wire:model.live="pais_telefono" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-1 py-2 text-[9px] font-bold text-parrafo outline-none transition">
 <option value="">-- Seleccionar --</option>
 @foreach($paisesConfig as $pName => $pData)
 <option value="{{ $pName }}">{{ $pName }} ({{ $pData['codigo'] }})</option>
 @endforeach
 </select>
 </div>
 <div class="col-span-7">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Celular *</label>
 <div class="flex gap-2">
 <span class="inline-flex items-center justify-center h-10 px-2 rounded-xl bg-fondo-panel border border-borde text-xs font-bold text-parrafo">
 {{ $codigo_telefono ?: '+??' }}
 </span>
 <input type="text" wire:model="telefono" 
 placeholder="{{ $pais_telefono && isset($paisesConfig[$pais_telefono]) ? $paisesConfig[$pais_telefono]['placeholder'] : 'Seleccione país...' }}"
 class="flex-1 h-10 rounded-xl border {{ $errors->has('telefono') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 </div>
 @error('telefono') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 {{-- Domicilio --}}
 <div class="md:col-span-2 border-t border-borde-suave pt-3">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest mb-3">Dirección de Domicilio</h4>
 </div>

 <!-- 1. Departamento -->
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Departamento de Domicilio *</label>
 <select wire:model.live="departamento_domicilio"
 class="w-full h-10 rounded-xl border {{ $errors->has('departamento_domicilio') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE DEPARTAMENTO...</option>
 @foreach(array_keys($catalogDepartamentos) as $dept)
 <option value="{{ $dept }}">{{ $dept }}</option>
 @endforeach
 </select>
 @error('departamento_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <!-- Especifique Departamento (si aplica) -->
 @if($departamento_domicilio === 'OTRO')
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Especifique Departamento *</label>
 <input type="text" wire:model="otro_departamento" placeholder="Especifique el Departamento..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otro_departamento') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otro_departamento') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 @else
 <div></div> <!-- Mantiene alineado el grid si no se muestra -->
 @endif

 <!-- 2. Municipio / Ciudad -->
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Municipio / Localidad *</label>
 @if($departamento_domicilio === 'OTRO')
 <input type="text" wire:model="otro_municipio" placeholder="Especifique Municipio..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otro_municipio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otro_municipio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @else
 <select wire:model.live="municipio_domicilio"
 class="w-full h-10 rounded-xl border {{ $errors->has('municipio_domicilio') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE MUNICIPIO...</option>
 @if($departamento_domicilio && isset($catalogDepartamentos[$departamento_domicilio]))
 @foreach($catalogDepartamentos[$departamento_domicilio] as $muni)
 <option value="{{ $muni }}">{{ $muni }}</option>
 @endforeach
 @endif
 </select>
 @error('municipio_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @endif
 </div>

 <!-- 3. Zona / Barrio -->
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Zona / Barrio *</label>
 @if(isset($catalogZonas[$municipio_domicilio]))
 <select wire:model.live="zona_domicilio"
 class="w-full h-10 rounded-xl border {{ $errors->has('zona_domicilio') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE ZONA...</option>
 @foreach($catalogZonas[$municipio_domicilio] as $z)
 <option value="{{ $z }}">{{ $z }}</option>
 @endforeach
 </select>
 @error('zona_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @else
 <input type="text" wire:model="otra_zona" placeholder="Ej. Sopocachi"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otra_zona') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otra_zona') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @endif
 </div>

 <!-- 4. Especifique municipio/ciudad (si aplica) -->
 @if($municipio_domicilio === 'OTRO' && $departamento_domicilio !== 'OTRO')
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Especifique Municipio *</label>
 <input type="text" wire:model="otro_municipio" placeholder="Especifique el Municipio..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otro_municipio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otro_municipio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 @else
 <div></div> <!-- Mantiene alineado el grid -->
 @endif

 <!-- 5. Especifique zona/barrio (si aplica) -->
 @if(isset($catalogZonas[$municipio_domicilio]) && $zona_domicilio === 'OTRO')
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Especifique Zona / Barrio *</label>
 <input type="text" wire:model="otra_zona" placeholder="Especifique la Zona / Barrio..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otra_zona') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otra_zona') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 @else
 <div></div> <!-- Mantiene alineado el grid -->
 @endif

 <!-- 6 & 7. Calle / Avenida y Número de domicilio -->
 <div class="md:col-span-2 grid grid-cols-12 gap-3">
 <div class="col-span-8">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Calle / Avenida *</label>
 <input type="text" wire:model="calle" placeholder="Ej. Av. Arce o Calle Murillo"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('calle') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('calle') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="col-span-4">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nro. Domicilio *</label>
 <input type="text" wire:model="nro_domicilio" placeholder="Ej. 1234 o S/N"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('nro_domicilio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('nro_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 <!-- 8. Referencia de domicilio -->
 <div class="md:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Referencia de Domicilio</label>
 <input type="text" wire:model="referencia_domicilio" placeholder="Ej. Frente al centro de salud"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('referencia_domicilio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('referencia_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 {{-- Emergencia --}}
 <div class="md:col-span-2 border-t border-borde-suave pt-3">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest mb-3">Contacto de Emergencia</h4>
 </div>

 <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-3">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nombres Emergencia *</label>
 <input type="text" wire:model="contacto_emergencia" placeholder="Ej. María Teresa"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('contacto_emergencia') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('contacto_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Paterno *</label>
 <input type="text" wire:model="ap_paterno_emergencia" placeholder="Ej. López"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('ap_paterno_emergencia') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('ap_paterno_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Materno</label>
 <input type="text" wire:model="ap_materno_emergencia" placeholder="Ej. Quispe"
 class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('ap_materno_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Parentesco / Relación</label>
 <select wire:model="parentesco_emergencia" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE...</option>
 <option value="PADRE">PADRE</option>
 <option value="MADRE">MADRE</option>
 <option value="CONYUGUE">CONYUGUE</option>
 <option value="HIJO/A">HIJO/A</option>
 <option value="HERMANO/A">HERMANO/A</option>
 <option value="OTRO">OTRO</option>
 </select>
 @error('parentesco_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Celular de Emergencia</label>
 <input type="text" wire:model="celular_emergencia" placeholder="Ej. 70098765"
 class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('celular_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 </div>
 @endif

 {{-- PASO 3: PERFIL INSTITUCIONAL --}}
 @if($pasoFormulario === 3)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-briefcase text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Tipo de usuario / rol institucional</h3>
 </div>
 <div class="grid gap-4 md:grid-cols-2 max-w-3xl">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Tipo de usuario / rol institucional *</label>
 <select wire:model.live="rol" class="w-full h-10 rounded-xl border {{ $errors->has('rol') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE TIPO DE USUARIO...</option>
 @foreach($roles as $rolDisponible)
 @php
 $nombreParaSelect = match($rolDisponible->name) {
 'FAMILIAR' => 'Familiar / Responsable',
 'VOLUNTARIO' => 'Voluntario',
 default => mb_convert_case(str_replace('_', ' ', $rolDisponible->name), MB_CASE_TITLE, 'UTF-8')
 };
 @endphp
 <option value="{{ $rolDisponible->name }}">{{ $nombreParaSelect }}</option>
 @endforeach
 </select>
 @error('rol') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Área Institucional Operativa</label>
 <select wire:model="cod_area" class="w-full h-10 rounded-xl border {{ $errors->has('cod_area') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE ÁREA...</option>
 @foreach($areas as $ar)
 @if($ar->cod_area !== 'ARE_0009') {{-- Ocultar Admin del Sistema --}}
 @php
 $esSugerida = false;
 if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && str_contains(strtolower($ar->nombre), 'salud')) $esSugerida = true;
 elseif (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && str_contains(strtolower($ar->nombre), 'atención médica')) $esSugerida = true;
 elseif (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && str_contains(strtolower($ar->nombre), 'psicología')) $esSugerida = true;
 elseif (in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']) && str_contains(strtolower($ar->nombre), 'admin')) $esSugerida = true;
 elseif ($rol === 'VOLUNTARIO' && str_contains(strtolower($ar->nombre), 'voluntariado')) $esSugerida = true;
 @endphp
 <option value="{{ $ar->cod_area }}">
 {{ $ar->nombre }} {{ $esSugerida ? '⭐ (Recomendada)' : '' }}
 </option>
 @endif
 @endforeach
 </select>
 @error('cod_area') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror

 @if($rol)
 @php
 $sugeridaTxt = match($rol) {
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'Área de Atención Médica o Área de Psicología',
 'superadministrador', 'administrador' => 'Área Administrativa y Registro Institucional',
 'VOLUNTARIO' => 'Voluntariado y Relaciones Institucionales',
 'FAMILIAR' => 'No requiere vinculación a áreas internas',
 default => null
 };
 @endphp
 @if($sugeridaTxt)
 <p class="mt-1 text-[9px] font-bold uppercase text-boton-acento tracking-wider flex items-center gap-1 animate-pulse">
 <i class="ph-bold ph-sparkle"></i> Recomendación: se sugiere vincular a <span class="underline font-extrabold">{{ $sugeridaTxt }}</span>
 </p>
 @endif
 @endif
 </div>

 {{-- Perfil de Salud --}}
 @if(in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']))
 <div class="md:col-span-2 grid gap-4 md:grid-cols-2 border-t border-borde-suave pt-3 animate-in fade-in duration-300">
 <div class="md:col-span-2">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Información Profesional Médica</h4>
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-boton-acento">Especialidad Médica *</label>
 <select wire:model.live="especialidad_salud" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="">SELECCIONE ESPECIALIDAD...</option>
 @foreach($especialidades as $esp)
 <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
 @endforeach
 </select>
 @error('especialidad_salud') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Fecha de Ingreso *</label>
 <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }} class="w-full h-10 rounded-xl border border-borde bg-fondo-panel {{ !$isEdit ? 'opacity-70 pointer-events-none' : '' }} px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('fecha_ingreso') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="md:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Institución de Formación</label>
 <input type="text" wire:model="institucion_formacion" placeholder="Ej. Universidad Mayor de San Andrés" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('institucion_formacion') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="md:col-span-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-[10px] font-bold leading-relaxed text-parrafo">
 La matricula profesional y documentos de respaldo se gestionaran desde el modulo de documentacion del usuario.
 </div>
 </div>
 @endif

 {{-- Perfil Admin --}}
 @if(in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']))
 <div class="md:col-span-2 grid gap-4 md:grid-cols-2 border-t border-borde-suave pt-3 animate-in fade-in duration-300">
 <div class="md:col-span-2">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Información de Cargo Administrativo</h4>
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Cargo Administrativo *</label>
 @if($cargosAdmin->isEmpty())
 <div class="bg-estado-peligroBg border border-borde-focus rounded-xl p-3 text-[10px] font-semibold text-boton-acento leading-normal">
 ⚠️ No hay cargos administrativos registrados. Por favor, registre cargos administrativos primero o contacte con soporte técnico.
 </div>
 @else
 <select wire:model.live="cargo_administrativo" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <option value="">SELECCIONE CARGO...</option>
 @foreach($cargosAdmin as $cargo)
 <option value="{{ $cargo->cod_cargo_admin }}">{{ $cargo->nombre }}</option>
 @endforeach
 </select>
 @error('cargo_administrativo') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @endif
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Fecha de Ingreso *</label>
 <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }} class="w-full h-10 rounded-xl border border-borde bg-fondo-panel {{ !$isEdit ? 'opacity-70 pointer-events-none' : '' }} px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('fecha_ingreso') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 {{-- Perfil Voluntario --}}
 @if($rol === 'VOLUNTARIO')
 <div class="md:col-span-2 grid gap-4 md:grid-cols-2 border-t border-borde-suave pt-3 animate-in fade-in duration-300">
 <div class="md:col-span-2">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Perfil de Voluntariado</h4>
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Disponibilidad Inicial</label>
 <input type="text" wire:model="disponibilidad_inicial" placeholder="Ej. Fines de semana / Tardes" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('disponibilidad_inicial') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Área de Apoyo Preferente</label>
 <input type="text" wire:model="area_apoyo_preferente" placeholder="Ej. Recreación / Terapia" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('area_apoyo_preferente') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Fecha de Ingreso *</label>
 <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }} class="w-full h-10 rounded-xl border border-borde bg-fondo-panel {{ !$isEdit ? 'opacity-70 pointer-events-none' : '' }} px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('fecha_ingreso') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 {{-- Perfil Familiar --}}
 @if($rol === 'FAMILIAR')
 <div class="md:col-span-2 grid gap-6 border-t border-borde-suave pt-4 animate-in fade-in duration-300">
 
 {{-- Encabezado de la Sección --}}
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-borde-suave pb-2">
 <div class="flex items-center gap-2">
 <i class="ph-fill ph-users-three text-lg text-boton-acento"></i>
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Vinculacion con adulto mayor</h4>
 </div>
 <button type="button" wire:click="$toggle('mostrarQuickRegAdulto')"
 class="px-3 py-1 rounded-lg border border-borde-focus text-boton-acento text-[8px] font-black uppercase tracking-wider transition hover:bg-boton-acento hover:text-inverso active:scale-95 inline-flex items-center gap-1 shadow-sm">
 <i class="ph-bold {{ $mostrarQuickRegAdulto ? 'ph-caret-left' : 'ph-user-plus' }} text-xs"></i>
 {{ $mostrarQuickRegAdulto ? 'Volver a Selección' : 'Registrar Nuevo Adulto Mayor' }}
 </button>
 </div>

 {{-- 1. FORMULARIO DE REGISTRO RÁPIDO (INLINE) --}}
 @if($mostrarQuickRegAdulto)
 <div class="p-4 rounded-2xl bg-fondo-panel border border-borde-suave space-y-4 animate-in slide-in-from-top-4 duration-300">
 <div class="flex items-center gap-2 border-b border-borde-suave pb-1.5">
 <i class="ph-bold ph-plus-circle text-boton-acento text-sm"></i>
 <h5 class="text-[9px] font-bold uppercase tracking-widest text-parrafo">Registro Rápido de Adulto Mayor</h5>
 </div>
 
 <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Nombres *</label>
 <input type="text" wire:model="quick_nombres" placeholder="Nombres" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_nombres') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Apellido Paterno *</label>
 <input type="text" wire:model="quick_ap_paterno" placeholder="Paterno" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_ap_paterno') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Apellido Materno</label>
 <input type="text" wire:model="quick_ap_materno" placeholder="Materno" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_ap_materno') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">CI / Documento *</label>
 <input type="text" wire:model="quick_ci" placeholder="Ej. 1234567" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_ci') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Género *</label>
 <select wire:model="quick_genero" class="w-full h-8 rounded-lg border border-borde bg-fondo-card px-2 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="MASCULINO">MASCULINO</option>
 <option value="FEMENINO">FEMENINO</option>
 <option value="OTRO">OTRO</option>
 </select>
 @error('quick_genero') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Fecha Nacimiento *</label>
 <input type="date" wire:model="quick_fecha_nac" class="w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_fecha_nac') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 <div class="flex justify-end gap-2 border-t border-borde/10 pt-2">
 <button type="button" wire:click="$set('mostrarQuickRegAdulto', false)" class="px-4 py-1.5 rounded-lg bg-fondo-panel text-parrafo text-[8px] font-black uppercase tracking-widest transition hover:bg-fondo-panel">Cancelar</button>
 <button type="button" wire:click="registrarYVincularAdulto" class="px-5 py-1.5 rounded-lg bg-boton-acento text-inverso text-[8px] font-black uppercase tracking-widest transition hover:bg-boton-principal shadow-sm">Registrar y Vincular</button>
 </div>
 </div>

 {{-- 2. SELECCIÓN DE ADULTO MAYOR EXISTENTE --}}
 @else
 <div class="p-4 rounded-2xl bg-fondo-panel border border-borde-suave space-y-4">
 <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4 items-end">
 <div class="sm:col-span-2">
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Seleccionar Adulto Mayor *</label>
 <select wire:model="selected_cod_am" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="">-- Seleccionar Adulto Mayor Disponible --</option>
 @foreach(\App\Models\AdultoMayor::where('cod_est_adul', 1)->orderBy('ap_paterno')->orderBy('nombres')->get() as $am)
 @php
 $adultoNombre = trim(($am->nombres ?? '') . ' ' . ($am->ap_paterno ?? '') . ' ' . ($am->ap_materno ?? ''));
 $adultoDocumento = $am->ci ? 'CI ' . trim(($am->ci ?? '') . ' ' . ($am->expedicion_ci ?? '')) : 'SIN DOCUMENTO REGISTRADO';
 $adultoEdad = $am->fecha_nac ? ' — ' . \Carbon\Carbon::parse($am->fecha_nac)->age . ' AÑOS' : '';
 @endphp
 <option value="{{ $am->cod_am }}">{{ mb_strtoupper($adultoNombre . ' — ' . $adultoDocumento . $adultoEdad, 'UTF-8') }}</option>
 @endforeach
 </select>
 @error('selected_cod_am') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Parentesco / Vínculo *</label>
 <select wire:model="selected_parentesco" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="HIJO/A">HIJO/A</option>
 <option value="CONYUGE">CONYUGE</option>
 <option value="NIETO/A">NIETO/A</option>
 <option value="HERMANO/A">HERMANO/A</option>
 <option value="SOBRINO/A">SOBRINO/A</option>
 <option value="TUTOR">TUTOR</option>
 <option value="OTRO">OTRO</option>
 </select>
 @error('selected_parentesco') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="flex flex-col gap-1.5 justify-center pl-2 pt-1">
 <label class="relative inline-flex items-center cursor-pointer select-none">
 <input type="checkbox" wire:model="selected_es_responsable" class="sr-only peer">
 <div class="w-7 h-4 bg-fondo-panel rounded-full peer peer-focus:ring-2 peer-focus:ring-[#E27D60]/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-fondo-card after:border-borde-suave after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-estado-exitoBg"></div>
 <span class="ml-2 text-[8px] font-black uppercase tracking-wider text-parrafo">Resp. Principal</span>
 </label>
 <label class="relative inline-flex items-center cursor-pointer select-none">
 <input type="checkbox" wire:model="selected_responsable_salud" class="sr-only peer">
 <div class="w-7 h-4 bg-fondo-panel rounded-full peer peer-focus:ring-2 peer-focus:ring-[#E27D60]/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-fondo-card after:border-borde-suave after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-fondo-panel"></div>
 <span class="ml-2 text-[8px] font-black uppercase tracking-wider text-parrafo">Resp. Salud</span>
 </label>
 <label class="relative inline-flex items-center cursor-pointer select-none">
 <input type="checkbox" wire:model="selected_responsable_economico" class="sr-only peer">
 <div class="w-7 h-4 bg-fondo-panel rounded-full peer peer-focus:ring-2 peer-focus:ring-[#E27D60]/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-fondo-card after:border-borde-suave after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-fondo-panel"></div>
 <span class="ml-2 text-[8px] font-black uppercase tracking-wider text-parrafo">Resp. Económico</span>
 </label>
 </div>
 </div>
 <div class="grid gap-3 sm:grid-cols-4 items-end">
 <div class="sm:col-span-3">
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Notas / Observaciones del Vínculo</label>
 <input type="text" wire:model="selected_observaciones" placeholder="Ej. A cargo del seguimiento médico semanal" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 </div>
 <button type="button" wire:click="vincularAdultoMayor" 
 class="w-full h-10 rounded-xl bg-boton-principal text-inverso text-[8px] font-black uppercase tracking-widest transition hover:bg-boton-acento shadow-md flex items-center justify-center gap-1.5 active:scale-95">
 <i class="ph-bold ph-plus-circle text-xs"></i> Vincular Adulto
 </button>
 </div>
 </div>
 @endif

 {{-- 3. LISTADO DE ADULTOS MAYORES VINCULADOS --}}
 <div class="space-y-2">
 <h5 class="text-[9px] font-bold uppercase tracking-widest text-parrafo/80 flex items-center gap-1.5">
 <i class="ph-bold ph-link text-boton-acento"></i> Adultos Mayores Vinculados a este Familiar 
 <span class="px-2 py-0.5 rounded-full bg-estado-peligroBg text-boton-acento text-[8px] font-black">
 {{ count($vinculosFamiliar) }}
 </span>
 </h5>

 @if(count($vinculosFamiliar) === 0)
 <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-borde-suave rounded-2xl bg-fondo-panel text-center">
 <i class="ph-bold ph-link-break text-xl text-meta mb-1"></i>
 <p class="text-[9px] font-bold text-apoyo">Sin vinculaciones. Agrega al menos un adulto mayor de la lista superior.</p>
 </div>
 @else
 <div class="overflow-x-auto rounded-xl border border-borde-suave bg-fondo-card">
 <table class="w-full border-collapse text-left">
 <thead>
 <tr class="bg-fondo-panel text-[8px] font-black uppercase tracking-widest text-parrafo/75 border-b border-borde-suave">
 <th class="px-3 py-2">Adulto Mayor</th>
 <th class="px-3 py-2">Vínculo/Parentesco</th>
 <th class="px-3 py-2 text-center">Responsabilidades</th>
 <th class="px-3 py-2">Observaciones</th>
 <th class="px-3 py-2 text-center">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/20">
 @foreach($vinculosFamiliar as $i => $v)
 <tr class="text-[9px] font-bold text-parrafo/85 hover:bg-fondo-panel transition">
 <td class="px-3 py-2">
 <span class="font-black text-parrafo">{{ $v['nombres_completos'] }}</span>
 </td>
 <td class="px-3 py-2">
 <span class="px-2 py-0.5 rounded bg-fondo-panel text-parrafo text-[8px] font-black uppercase">{{ $v['parentesco_vinculo'] }}</span>
 </td>
 <td class="px-3 py-2 text-center space-y-1">
 <div class="flex flex-col gap-1 items-center">
 @if($v['es_responsable'] === 'SI')
 <span class="px-1.5 py-0.5 rounded bg-estado-exitoBg text-estado-exito text-[8px] font-black uppercase block">PRINCIPAL</span>
 @endif
 @if(($v['responsable_salud'] ?? 'NO') === 'SI')
 <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[8px] font-black uppercase block">SALUD</span>
 @endif
 @if(($v['responsable_economico'] ?? 'NO') === 'SI')
 <span class="px-1.5 py-0.5 rounded bg-yellow-50 text-yellow-600 text-[8px] font-black uppercase block">ECONÓMICO</span>
 @endif
 @if($v['es_responsable'] !== 'SI' && ($v['responsable_salud'] ?? 'NO') !== 'SI' && ($v['responsable_economico'] ?? 'NO') !== 'SI')
 <span class="px-1.5 py-0.5 rounded bg-fondo-panel text-apoyo text-[8px] font-black uppercase block">NINGUNO</span>
 @endif
 </div>
 </td>
 <td class="px-3 py-2 text-apoyo">
 {{ $v['observaciones'] ?: 'Sin observaciones adicionales' }}
 </td>
 <td class="px-3 py-2 text-center">
 <button type="button" wire:click="desvincularAdultoMayor({{ $i }})"
 class="h-6 w-6 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-inverso transition inline-flex items-center justify-center active:scale-90 shadow-sm"
 title="Eliminar vinculación">
 <i class="ph-bold ph-trash text-xs"></i>
 </button>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endif
 </div>

 {{-- Campo General de Observaciones --}}
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Observación General de Vinculación Familiar</label>
 <input type="text" wire:model="observacion_vinculo" placeholder="Ej. Hijo tutor legal de adulto mayor" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('observacion_vinculo') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 </div>
 </div>
 @endif

 {{-- PASO 4: SEGURIDAD --}}
 @if($pasoFormulario === 4)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300" x-data="{ showPass: false, showConfirm: false }">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-shield-check text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Seguridad de Acceso</h3>
 </div>
 <div class="grid gap-4 md:grid-cols-2 max-w-2xl">
 <div class="md:col-span-2">
 @if(!$isEdit)
 @if(false) {{-- Ocultar el texto estático antiguo --}}
 <div class="bg-fondo-panel border-l-4 border-borde-fuerte p-4 rounded-r-xl space-y-2">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-key text-parrafo text-lg"></i>
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Contraseña Temporal y Notificación</h4>
 </div>
 <p class="text-[10px] font-bold text-parrafo/75 leading-relaxed">
 Se generará una contraseña temporal de alta seguridad compleja (11 caracteres aleatorios, incluyendo mayúsculas, minúsculas, números y símbolos especiales) de manera totalmente automática al confirmar el registro.
 </p>
 <p class="text-[10px] font-bold text-boton-acento uppercase leading-relaxed">
 ⚠️ Se le enviará automáticamente un correo electrónico de bienvenida con sus credenciales de acceso inicial y una directiva obligatoria de cambio de contraseña al ingresar por primera vez.
 </p>
 </div>
 @endif

 <div class="space-y-4">
 <div class="bg-fondo-panel border-l-4 border-borde-fuerte p-4 rounded-r-xl space-y-2">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-key text-parrafo text-lg"></i>
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Contraseña Temporal de Acceso</h4>
 </div>
 <p class="text-[10px] font-bold text-parrafo/75 leading-relaxed">
 Esta es la contraseña temporal de alta seguridad generada por el sistema para el nuevo usuario. Puede copiarla o regenerar una nueva si lo desea.
 </p>
 </div>

 <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-fondo-panel border border-borde-suave p-4 rounded-2xl" x-data="{ showGenPass: false }">
 <div class="relative flex-1">
 <input :type="showGenPass ? 'text' : 'password'" 
 value="{{ $passwordTemporalVisual }}" 
 readonly
 class="w-full h-11 rounded-xl border border-borde bg-fondo-card/70 pl-4 pr-24 py-2 text-sm font-mono font-black tracking-widest text-parrafo outline-none">
 
 <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
 <!-- Toggle eye button -->
 <button type="button" 
 @click="showGenPass = !showGenPass" 
 class="h-8 w-8 flex items-center justify-center rounded-lg text-apoyo hover:text-parrafo hover:bg-fondo-panel transition"
 title="Mostrar/Ocultar contraseña">
 <i class="ph-bold text-base" :class="showGenPass ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>

 <!-- Clipboard copy button -->
 <button type="button" 
 onclick="navigator.clipboard.writeText('{{ $passwordTemporalVisual }}'); Swal.fire({ icon: 'success', title: 'Copiado', text: 'Contraseña temporal copiada al portapapeles.', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-[1.5rem]' } })"
 class="h-8 w-8 flex items-center justify-center rounded-lg text-apoyo hover:text-parrafo hover:bg-fondo-panel transition"
 title="Copiar al portapapeles">
 <i class="ph-bold ph-copy text-base"></i>
 </button>
 </div>
 </div>

 <!-- Regenerate button -->
 <button type="button" 
 wire:click="regenerarPasswordTemporal"
 class="h-11 px-5 inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento text-[10px] font-bold uppercase text-inverso shadow-md shadow-[#E27D60]/20 hover:bg-fondo-panel active:scale-95 transition">
 <i class="ph-bold ph-arrows-clockwise text-sm"></i>
 Regenerar contraseña temporal
 </button>
 </div>

 <div class="bg-estado-peligroBg border-l-4 border-borde-focus p-4 rounded-r-xl">
 <p class="text-[10px] font-bold text-boton-acento uppercase leading-relaxed">
 ⚠️ NOTA INSTITUCIONAL: Se le enviará automáticamente un correo electrónico de bienvenida con sus credenciales de acceso inicial y una directiva obligatoria de cambio de contraseña al ingresar por primera vez.
 </p>
 </div>
 </div>
 @else
 @if($usuarioId === auth()->id())
 <div class="bg-estado-peligroBg border-l-4 border-borde-focus p-4 rounded-r-xl">
 <p class="text-[10px] font-bold text-boton-acento leading-relaxed">
 Deje en blanco la contraseña si no desea cambiar su contraseña actual. Al registrar una nueva contraseña, se actualizará su acceso de forma inmediata.
 </p>
 </div>
 @else
 <div class="bg-fondo-panel border-l-4 border-borde-fuerte p-4 rounded-r-xl">
 <p class="text-[10px] font-bold text-parrafo leading-relaxed">
 No se puede editar directamente la contraseña de otro usuario para mantener el cumplimiento de las políticas de privacidad y seguridad institucional.
 </p>
 </div>
 @endif
 @endif
 </div>

 @if($isEdit)
 @if($usuarioId === auth()->id())
 <div class="md:col-span-2 grid gap-4 md:grid-cols-3">
 <div x-data="{ showActual: false }">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-boton-acento">Contraseña Actual *</label>
 <div class="relative">
 <input :type="showActual ? 'text' : 'password'" wire:model="password_actual" placeholder="••••••••"
 class="w-full h-10 rounded-xl border {{ $errors->has('password_actual') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card pl-4 pr-10 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <button type="button" @click="showActual = !showActual" class="absolute right-3 top-1/2 -translate-y-1/2 text-meta hover:text-parrafo transition focus:outline-none">
 <i class="ph-bold text-base" :class="showActual ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 @error('password_actual') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nueva Contraseña</label>
 <div class="relative">
 <input :type="showPass ? 'text' : 'password'" wire:model="password" placeholder="••••••••"
 class="w-full h-10 rounded-xl border {{ $errors->has('password') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card pl-4 pr-10 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-meta hover:text-parrafo transition focus:outline-none">
 <i class="ph-bold text-base" :class="showPass ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 @error('password') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Confirmar Contraseña</label>
 <div class="relative">
 <input :type="showConfirm ? 'text' : 'password'" wire:model="password_confirmation" placeholder="••••••••"
 class="w-full h-10 rounded-xl border border-borde bg-fondo-card pl-4 pr-10 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-meta hover:text-parrafo transition focus:outline-none">
 <i class="ph-bold text-base" :class="showConfirm ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 </div>
 </div>
 @else
 <div class="md:col-span-2 flex flex-col items-center justify-center p-6 bg-fondo-panel border border-borde-fuerte rounded-2xl space-y-3">
 <div class="flex h-12 w-12 items-center justify-center rounded-full bg-estado-peligroBg text-boton-acento shadow-sm">
 <i class="ph-bold ph-key text-xl animate-bounce"></i>
 </div>
 <div class="text-center max-w-md">
 <h4 class="text-xs font-bold text-parrafo uppercase tracking-wider">Restablecimiento de Credenciales</h4>
 <p class="mt-1 text-[10px] font-semibold text-parrafo/65 leading-relaxed">
 Para mantener altos estándares de seguridad, no se puede ver ni editar directamente la contraseña actual de otro usuario.
 Presione el botón para generar una clave temporal de 11 caracteres que se notificará de forma automatizada por correo electrónico.
 </p>
 </div>
 <button type="button"
 wire:click="restablecerPasswordUsuario('{{ $usuarioId }}')"
 wire:confirm="¿Está seguro de que desea restablecer la contraseña de este usuario? Se generará una clave temporal y se le enviará por correo."
 class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-[9px] font-bold uppercase tracking-wider text-inverso shadow-md hover:bg-boton-acento active:scale-95 transition-all duration-300">
 <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i> Restablecer Contraseña Temporal
 </button>
 </div>
 @endif
 @endif
 </div>
 </div>
 @endif

 {{-- PASO 5: CONFIRMACIÓN --}}
 @if($pasoFormulario === 5)
 <div class="space-y-4 animate-in zoom-in duration-300" x-data="{ showPassSummary: false }">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-check-square text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Resumen de Registro</h3>
 </div>
 <div class="grid gap-4 lg:grid-cols-2">
 <div class="rounded-xl bg-fondo-card/50 p-5 border border-borde-suave space-y-4">
 <div class="flex items-center gap-4">
 @if($foto_de_perfil_upload)
 <img src="{{ $foto_de_perfil_upload->temporaryUrl() }}" 
 class="h-16 w-16 rounded-[1.1rem] object-cover ring-2 ring-[#E27D60] shadow">
 @elseif($isEdit && $cod_usu && \App\Models\User::find($cod_usu)?->foto_de_perfil)
 <img src="{{ asset('storage/' . \App\Models\User::find($cod_usu)->foto_de_perfil) }}" 
 class="h-16 w-16 rounded-[1.1rem] object-cover ring-2 ring-[#2F3E5C]/30 shadow">
 @else
 <div class="flex h-16 w-16 items-center justify-center rounded-[1.1rem] bg-boton-principal text-xl font-extrabold text-inverso shadow uppercase">
 {{ mb_substr($nombres ?? 'U', 0, 1) }}{{ mb_substr($ap_paterno ?? 'I', 0, 1) }}
 </div>
 @endif
 <div>
 <h4 class="text-sm font-bold text-parrafo uppercase leading-tight">{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</h4>
 <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full bg-estado-peligroBg text-boton-acento text-[8px] font-black uppercase tracking-widest border border-borde-focus">
 {{ str_replace('_', ' ', $rol) }}
 </span>
 </div>
 </div>
 <div class="grid grid-cols-2 gap-3 border-t border-borde-suave pt-3">
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Documento Identidad</p>
 <p class="text-[10px] font-bold text-parrafo uppercase mt-0.5">{{ $numero_documento }} {{ $expedido }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Nacionalidad / Emisor</p>
 <p class="text-[10px] font-bold text-parrafo uppercase mt-0.5">{{ $pais_documento }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Celular de Contacto</p>
 <p class="text-[10px] font-bold text-parrafo mt-0.5">{{ $codigo_telefono }} {{ $telefono }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Correo Institucional</p>
 <p class="text-[10px] font-bold text-parrafo lowercase mt-0.5 truncate">{{ $correo }}</p>
 </div>
 <div class="col-span-2 border-t border-borde/10 pt-2">
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Dirección Domicilio</p>
 <p class="text-[10px] font-bold text-parrafo mt-0.5 leading-tight">
 {{ $direccion ?: 'No registrada' }} {{ $zona ? '('.$zona.')' : '' }} {{ $ciudad ? '- '.$ciudad : '' }}
 </p>
 </div>
 </div>
 </div>

 <div class="rounded-xl bg-fondo-card/50 p-5 border border-borde-suave flex flex-col justify-between space-y-4">
 <div class="space-y-3">
 @if(!$isEdit && $passwordTemporalVisual)
 <div class="p-3 bg-estado-peligroBg border border-borde-focus rounded-xl space-y-1 text-center">
 <span class="text-[8px] font-black text-boton-acento uppercase tracking-widest block">Contraseña Temporal Generada:</span>
 <div class="flex items-center justify-center gap-2 mt-1">
 <div class="text-sm font-mono font-black text-parrafo bg-fondo-card border border-borde-suave px-3 py-1.5 rounded-lg select-all cursor-pointer inline-flex items-center gap-2" title="Click para copiar">
 <span x-text="showPassSummary ? '{{ $passwordTemporalVisual }}' : '••••••••••••'"></span>
 </div>
 <button type="button" @click="showPassSummary = !showPassSummary" class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card border border-borde-suave text-meta hover:text-parrafo transition shadow-sm active:scale-95">
 <i class="ph-bold" :class="showPassSummary ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 <p class="text-[8px] text-meta font-bold leading-normal">
 Esta clave se enviará al correo y no se volverá a mostrar en el panel por razones de seguridad.
 </p>
 </div>
 @endif

 <div class="space-y-1">
 <span class="text-[8px] font-black text-meta uppercase tracking-widest block">Próximos Pasos de Cumplimiento:</span>
 <div class="bg-fondo-card/80 p-3 rounded-xl border border-borde-suave text-[9px] font-bold text-parrafo/75 space-y-2">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-square text-boton-acento"></i>
 <span>Asignar Turnos y Horarios Semanales</span>
 </div>
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-square text-boton-acento"></i>
 <span>Validación de Carpeta de Documentación Obligatoria</span>
 </div>
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-square text-boton-acento"></i>
 <span>Primer Acceso con Cambio de Clave Obligatorio</span>
 </div>
 </div>
 </div>
 </div>
 <p class="text-[9px] font-bold text-parrafo/45 text-center leading-relaxed italic">
 Al confirmar, se guardará de manera definitiva esta ficha de personal institucional.
 </p>
 </div>
 </div>
 </div>
 @endif
 </div>

 {{-- FOOTER FIJO --}}
 <footer class="border-t border-borde-suave bg-fondo-panel px-4 py-2.5 backdrop-blur-xl shrink-0 flex flex-col-reverse sm:flex-row items-center justify-between gap-2">
 <button type="button" wire:click="cerrarFormulario" 
 class="w-full sm:w-auto px-6 py-2.5 rounded-xl border-2 border-borde-fuerte text-meta text-[9px] font-bold uppercase tracking-widest transition hover:bg-boton-principal hover:text-inverso active:scale-95 shadow-sm">
 Cancelar
 </button>
 
 <div class="flex items-center gap-2 w-full sm:w-auto">
 @if($pasoFormulario > 1)
 <button type="button" wire:click="anteriorPaso" 
 class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl border-2 border-borde-fuerte text-parrafo text-[9px] font-bold uppercase tracking-widest transition hover:bg-boton-principal hover:text-inverso active:scale-95">
 Anterior
 </button>
 @endif

 @if($pasoFormulario < 5)
 <button type="button" wire:click="siguientePaso" 
 class="flex-1 sm:flex-none px-10 py-2.5 rounded-xl bg-boton-principal text-inverso text-[9px] font-bold uppercase tracking-widest shadow-xl shadow-[#2F3E5C]/20 transition hover:bg-boton-acento active:scale-95">
 Continuar <i class="ph-bold ph-arrow-right ml-1"></i>
 </button>
 @else
 <button type="button" wire:click="guardarUsuario" 
 wire:loading.attr="disabled" 
 wire:target="guardarUsuario"
 class="flex-1 sm:flex-none px-12 py-2.5 rounded-xl bg-boton-acento text-inverso text-[9px] font-bold uppercase tracking-widest shadow-xl shadow-[#E27D60]/20 transition hover:bg-boton-principal active:scale-95 disabled:opacity-70 inline-flex items-center justify-center gap-2 min-w-[140px]">
 
 <!-- Spinner de Carga SVG Premium -->
 <span wire:loading wire:target="guardarUsuario" class="animate-spin h-3.5 w-3.5 text-inverso">
 <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 </span>

 <!-- Icono de Confirmación Normal (oculto al cargar) -->
 <span wire:loading.remove wire:target="guardarUsuario">
 <i class="ph-bold {{ $usuarioId ? 'ph-floppy-disk' : 'ph-check' }} text-xs"></i>
 </span>

 <span>{{ $usuarioId ? 'Actualizar' : 'Confirmar' }}</span>
 </button>
 @endif
 </div>
 </footer>
 </div>
 </div>
 @endif
