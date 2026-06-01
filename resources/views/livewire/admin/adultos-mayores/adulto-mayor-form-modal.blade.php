<div>
 @if($mostrar)
 {{-- Overlay"Indestructible" --}}
 <div class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-slate-900/40 p-4 backdrop-blur-sm sm:p-6"
 x-data x-init="document.body.style.overflow = 'hidden'" x-on:destroy="document.body.style.overflow = 'auto'">
 
 {{-- Modal Container --}}
 <div class="relative w-full max-w-4xl rounded-[24px] border border-borde-suave bg-fondo-app shadow-[0_32px_64px_-12px_rgba(0,0,0,0.5)] flex flex-col max-h-[94vh]">
 
 {{-- Header --}}
 <div class="flex items-center justify-between border-b border-borde-suave p-4 sm:px-8">
 <div class="flex items-center gap-3">
 <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-boton-acento/10 text-terracota shadow-inner">
 <i class="ph-fill ph-identification-card text-2xl"></i>
 </div>
 <div>
 <span class="text-[9px] font-bold uppercase tracking-widest text-terracota/70">Expediente Clínico</span>
 <h2 class="text-lg font-extrabold text-titulo">{{ $isEdit ? 'Actualizar Ficha: '.$ci : 'Admisión Nuevo Ingreso' }}</h2>
 </div>
 </div>
 <button type="button" wire:click="cerrar" class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card/50 text-titulo transition hover:bg-boton-acento hover:text-inverso active:scale-95 shadow-sm">
 <i class="ph-bold ph-x text-lg"></i>
 </button>
 </div>

 {{-- Barra de progreso mejorada --}}
 <div class="bg-fondo-panel px-6 py-4 sm:px-10 border-b border-borde-suave">
 <div class="relative flex items-center justify-between max-w-3xl mx-auto">
 {{-- Línea de fondo --}}
 <div class="absolute top-1/2 left-0 w-full h-1 bg-fondo-panel -translate-y-1/2 rounded-full"></div>
 {{-- Línea de progreso activa --}}
 <div class="absolute top-1/2 left-0 h-1 bg-boton-acento -translate-y-1/2 rounded-full transition-all duration-700 ease-out shadow-[0_0_8px_rgba(226,125,96,0.5)]" 
 style="width: {{ (($paso - 1) / ($totalPasos - 1)) * 100 }}%"></div>
 
 {{-- Pasos --}}
 @php
 $pasosInfo = [
 1 => ['icon' => 'ph-user-focus', 'label' => 'Identificación'],
 2 => ['icon' => 'ph-first-aid', 'label' => 'Datos personales'],
 3 => ['icon' => 'ph-map-pin-line', 'label' => 'Contacto y dirección'],
 4 => ['icon' => 'ph-users-three', 'label' => 'Familiar responsable'],
 5 => ['icon' => 'ph-hospital', 'label' => 'Ingreso institucional'],
 6 => ['icon' => 'ph-check-circle', 'label' => 'Consentimiento'],
 ];
 @endphp

 @foreach($pasosInfo as $idx => $info)
 <div class="relative flex flex-col items-center group">
 {{-- Ovalo/Circulo --}}
 <div class="relative z-10 flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-xl transition-all duration-500 border-2 
 {{ $paso > $idx ? 'bg-boton-acento border-terracota text-inverso shadow-[0_4px_12px_rgba(226,125,96,0.3)]' : 
 ($paso == $idx ? 'bg-fondo-card border-terracota text-terracota shadow-[0_8px_20px_rgba(226,125,96,0.2)] scale-110' : 
 'bg-fondo-app border-borde-suave text-titulo/40') }}">
 
 <i class="ph-bold {{ $info['icon'] }} text-base sm:text-lg transition-all duration-500 {{ $paso == $idx ? 'scale-110 rotate-[360deg]' : '' }}"></i>
 
 {{-- Checkmark para completados --}}
 @if($paso > $idx)
 <div class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-boton-principal text-inverso shadow-md border-2 border-borde-suave animate-in zoom-in duration-500">
 <i class="ph-bold ph-check text-[8px]"></i>
 </div>
 @endif
 </div>
 
 {{-- Etiqueta --}}
 <span class="absolute -bottom-6 whitespace-nowrap text-[9px] sm:text-[10px] font-bold uppercase tracking-tight transition-all duration-500
 {{ $paso >= $idx ? 'text-titulo opacity-100' : 'text-titulo/30 opacity-60' }} {{ $paso == $idx ? 'scale-110 -translate-y-0.5 text-terracota' : '' }}">
 {{ $info['label'] }}
 </span>
 </div>
 @endforeach
 </div>
 {{-- Espaciador inferior para las etiquetas --}}
 <div class="h-6"></div>
 </div>

 {{-- Contenido Scrollable --}}
 <div class="flex-1 overflow-y-auto p-5 sm:px-8 sm:py-6 custom-scrollbar">
 <div class="mb-5 rounded-2xl border border-azul-profundo/10 bg-boton-principal/5 px-4 py-3">
 <div class="flex items-start gap-3">
 <div class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-card text-titulo">
 <i class="ph-bold ph-info text-sm"></i>
 </div>
 <p class="text-xs font-bold leading-relaxed text-titulo/80">
 Los textos del formulario se normalizan automáticamente a MAYÚSCULAS. Los campos de C.I. y teléfonos aceptan solo dígitos.
 </p>
 </div>
 </div>
 
 {{-- Paso 1: Identidad --}}
 @if($paso == 1)
 <div class="space-y-8 animate-fade-in">
 <div class="flex flex-col items-center justify-center gap-8 lg:flex-row lg:items-start lg:justify-start">
 {{-- Preview de Foto --}}
 <div class="relative group">
 <div class="h-32 w-32 overflow-hidden rounded-3xl border-4 border-white bg-fondo-card shadow-2xl transition-transform group-hover:scale-105">
 @if($foto)
 <img src="{{ $foto->temporaryUrl() }}" class="h-full w-full object-cover">
 @elseif($fotoExistente)
 <img src="{{ Storage::url($fotoExistente) }}" class="h-full w-full object-cover">
 @else
 <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-azul-profundo/5 to-azul-profundo/10 text-titulo/20">
 <i class="ph-fill ph-user text-6xl"></i>
 <span class="mt-2 text-[10px] font-bold uppercase tracking-widest">Sin foto</span>
 </div>
 @endif
 {{-- Loading overlay --}}
 <div wire:loading wire:target="foto" class="absolute inset-0 flex items-center justify-center bg-boton-principal/40 backdrop-blur-sm">
 <i class="ph-bold ph-circle-notch animate-spin text-3xl text-inverso"></i>
 </div>
 </div>
 <label class="absolute -bottom-2 -right-2 flex h-12 w-12 cursor-pointer items-center justify-center rounded-2xl bg-boton-acento text-inverso shadow-xl transition hover:scale-110 hover:bg-boton-acento/90 active:scale-95">
 <i class="ph-bold ph-camera text-xl"></i>
 <input type="file" wire:model="foto" class="hidden" accept="image/*">
 </label>
 </div>

 <div class="flex-1 space-y-6 w-full">
 <div class="grid gap-4 md:grid-cols-2">
 <div class="space-y-1.5">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Nombres *</label>
 <input type="text" wire:model.live.debounce.250ms="nombres" placeholder="Ej. María Elena" class="w-full rounded-xl border-2 {{ $errors->has('nombres') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-4 py-2.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <p class="text-xs font-bold text-titulo/45">Se guardará en MAYÚSCULAS.</p>
 @error('nombres') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-1.5">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Apellido Paterno *</label>
 <input type="text" wire:model.live.debounce.250ms="ap_paterno" placeholder="Ej. Mamani" class="w-full rounded-xl border-2 {{ $errors->has('ap_paterno') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-4 py-2.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('ap_paterno') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-1.5">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Apellido Materno</label>
 <input type="text" wire:model.live.debounce.250ms="ap_materno" placeholder="Ej. Quispe" class="w-full rounded-xl border-2 {{ $errors->has('ap_materno') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-4 py-2.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('ap_materno') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="grid grid-cols-3 gap-2">
 <div class="col-span-2 space-y-1.5">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">C.I. *</label>
 <input type="text" wire:model.live.debounce.250ms="ci" inputmode="numeric" maxlength="9" placeholder="1234567" class="w-full rounded-xl border-2 {{ $errors->has('ci') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-4 py-2.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 </div>
 <div class="space-y-1.5">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Comp.</label>
 <input type="text" wire:model.live.debounce.250ms="complemento_ci" maxlength="2" placeholder="1A" class="w-full rounded-xl border-2 {{ $errors->has('complemento_ci') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-4 py-2.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 </div>
 </div>
 @error('ci') <div class="col-span-full"><span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span></div> @enderror
 </div>
 </div>
 </div>

 <div class="grid gap-4 md:grid-cols-2">
 <div class="space-y-1.5">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Expedición *</label>
 <select wire:model="expedicion_ci" class="w-full rounded-xl border-2 {{ $errors->has('expedicion_ci') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-4 py-2.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 @foreach(['LP'=>'La Paz', 'SC'=>'Santa Cruz', 'CB'=>'Cochabamba', 'OR'=>'Oruro', 'PT'=>'Potosí', 'CH'=>'Chuquisaca', 'TJ'=>'Tarija', 'BE'=>'Beni', 'PA'=>'Pando'] as $val => $text)
 <option value="{{ $val }}">{{ $text }} ({{ $val }})</option>
 @endforeach
 </select>
 @error('expedicion_ci') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-1.5">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Estado Civil *</label>
 <select wire:model="estado_civil" class="w-full rounded-xl border-2 {{ $errors->has('estado_civil') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-4 py-2.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 <option value="SOLTERO/A">Soltero/a</option>
 <option value="CASADO/A">Casado/a</option>
 <option value="VIUDO/A">Viudo/a</option>
 <option value="DIVORCIADO/A">Divorciado/a</option>
 <option value="UNIÓN LIBRE">Unión Libre</option>
 <option value="NO ESPECIFICADO">No especificado</option>
 </select>
 @error('estado_civil') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 </div>
 </div>
 @endif

 {{-- Paso 2: Datos personales --}}
 @if($paso == 2)
 <div class="grid gap-8 md:grid-cols-3 animate-fade-in">
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Fecha Nacimiento *</label>
 <input type="date" wire:model.live="fecha_nac"
 max="{{ now()->format('Y-m-d') }}"
 min="{{ now()->subYears(120)->format('Y-m-d') }}"
 class="w-full rounded-2xl border-2 {{ $errors->has('fecha_nac') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <p class="text-xs font-bold text-titulo/45">Edad mínima para admisión inicial: 60 años.</p>
 @error('fecha_nac') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Edad Estimada</label>
 @if($this->edad !== null && $this->edad < 60)
 <div class="flex h-[48px] w-full items-center gap-2 rounded-2xl border-2 border-terracota/30 bg-boton-acento/5 px-5 text-sm font-bold text-terracota">
 <i class="ph-fill ph-warning text-base"></i>
 {{ $this->edad }} años — menor de 60
 </div>
 @else
 <div class="flex h-[48px] w-full items-center rounded-2xl border-2 border-dashed border-borde-suave bg-fondo-card/30 px-5 text-sm font-bold text-titulo/40">
 {{ $this->edad ? $this->edad . ' años' : '--' }}
 </div>
 @endif
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Género *</label>
 <select wire:model="genero" class="w-full rounded-2xl border-2 {{ $errors->has('genero') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 <option value="MASCULINO">Masculino</option>
 <option value="FEMENINO">Femenino</option>
 <option value="OTRO">Otro</option>
 </select>
 @error('genero') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>

 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Grupo Sanguíneo *</label>
 <select wire:model="grupo_sanguineo" class="w-full rounded-2xl border-2 {{ $errors->has('grupo_sanguineo') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
 <option value="{{ $gs }}">{{ $gs }}</option>
 @endforeach
 </select>
 @error('grupo_sanguineo') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Seguro de Salud *</label>
 <select wire:model="seguro_salud" class="w-full rounded-2xl border-2 {{ $errors->has('seguro_salud') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 @foreach(['SUS','CAJA NACIONAL CNS','CAJA PETROLERA','SEGURO PRIVADO','NINGUNO','OTRO'] as $seguro)
 <option value="{{ $seguro }}">{{ $seguro }}</option>
 @endforeach
 </select>
 @error('seguro_salud') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Nivel Educativo *</label>
 <select wire:model="nivel_educat" class="w-full rounded-2xl border-2 {{ $errors->has('nivel_educat') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 @foreach(['ANALFABETO','PRIMARIA','SECUNDARIA','TÉCNICO','UNIVERSITARIO','POSTGRADO','NO ESPECIFICADO'] as $nivel)
 <option value="{{ $nivel }}">{{ ucfirst(strtolower($nivel)) }}</option>
 @endforeach
 </select>
 @error('nivel_educat') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>

 <div class="col-span-full space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Alergias Conocidas *</label>
 <textarea wire:model="alergias" rows="3" placeholder="Especifique o deje 'Ninguna'..." class="w-full resize-none rounded-3xl border-2 {{ $errors->has('alergias') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-6 py-4 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10"></textarea>
 @error('alergias') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 {{-- Paso 3: Contacto y dirección --}}
 @if($paso == 3)
 <div class="grid gap-8 md:grid-cols-2 animate-fade-in">
 <div class="col-span-full flex items-center gap-4 rounded-[28px] bg-boton-principal/5 p-6 border-2 border-azul-profundo/10 shadow-inner">
 <div class="relative inline-flex items-center cursor-pointer">
 <input type="checkbox" wire:model.live="tiene_celular" class="sr-only peer">
 <div class="w-14 h-8 bg-fondo-panel peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-fondo-card after:border-borde-suave after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-boton-acento"></div>
 </div>
 <div>
 <h4 class="text-sm font-bold text-titulo">¿Cuenta con dispositivo móvil personal?</h4>
 <p class="text-[10px] font-bold text-titulo/50">Activar para habilitar los campos de telefonía celular y redes sociales.</p>
 </div>
 </div>

 @if($tiene_celular)
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Número de Celular *</label>
 <input type="text" wire:model.live.debounce.250ms="celular" inputmode="numeric" maxlength="8" placeholder="70012345" class="w-full rounded-2xl border-2 {{ $errors->has('celular') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('celular') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="flex items-center gap-3 pt-6">
 <input type="checkbox" wire:model="sabe_usar_whatsapp" id="ws_check" class="h-6 w-6 rounded-lg border-2 border-borde-suave bg-fondo-card text-terracota transition focus:ring-borde-focus/30">
 <label for="ws_check" class="text-sm font-bold text-titulo/70 cursor-pointer">Sabe utilizar WhatsApp activamente</label>
 </div>
 @endif

 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Teléfono Fijo / Referencia</label>
 <input type="text" wire:model.live.debounce.250ms="telefono_fijo" inputmode="numeric" maxlength="8" placeholder="2223344" class="w-full rounded-2xl border-2 {{ $errors->has('telefono_fijo') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('telefono_fijo') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Dpto. Residencia *</label>
 <select wire:model="departamento_residencia" class="w-full rounded-2xl border-2 {{ $errors->has('departamento_residencia') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 @foreach(['LA PAZ', 'SANTA CRUZ', 'COCHABAMBA', 'ORURO', 'POTOSÍ', 'CHUQUISACA', 'TARIJA', 'BENI', 'PANDO'] as $dep)
 <option value="{{ $dep }}">{{ $dep }}</option>
 @endforeach
 </select>
 @error('departamento_residencia') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Ciudad / Municipio *</label>
 <input type="text" wire:model="ciudad_municipio" placeholder="Ej. El Alto" class="w-full rounded-2xl border-2 {{ $errors->has('ciudad_municipio') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('ciudad_municipio') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Zona / Barrio *</label>
 <input type="text" wire:model="zona" placeholder="Ej. Miraflores" class="w-full rounded-2xl border-2 {{ $errors->has('zona') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('zona') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2 md:col-span-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Calle / Avenida / Referencia Visual *</label>
 <input type="text" wire:model="calle" placeholder="Ej. Av. Saavedra esq. Villalobos, edificio blanco..." class="w-full rounded-2xl border-2 {{ $errors->has('calle') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('calle') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 {{-- Paso 4: Familiar responsable --}}
 @if($paso == 4)
 <div class="grid gap-8 md:grid-cols-2 animate-fade-in">
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Nombre Completo del Responsable *</label>
 <input type="text" wire:model="contacto_emergencia_nombre" placeholder="Nombre de un familiar directo" class="w-full rounded-2xl border-2 {{ $errors->has('contacto_emergencia_nombre') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('contacto_emergencia_nombre') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Parentesco *</label>
 <select wire:model="contacto_emergencia_parentesco" class="w-full rounded-2xl border-2 {{ $errors->has('contacto_emergencia_parentesco') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 @foreach(['HIJO/A','CÓNYUGE','NIETO/A','SOBRINO/A','HERMANO/A','TUTOR LEGAL','OTRO'] as $par)
 <option value="{{ $par }}">{{ ucfirst(strtolower($par)) }}</option>
 @endforeach
 </select>
 @error('contacto_emergencia_parentesco') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Celular de Contacto *</label>
 <input type="text" wire:model.live.debounce.250ms="contacto_emergencia_celular" inputmode="numeric" maxlength="8" placeholder="70098765" class="w-full rounded-2xl border-2 {{ $errors->has('contacto_emergencia_celular') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('contacto_emergencia_celular') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-6 pt-6">
 <div class="flex items-center gap-3">
 <input type="checkbox" wire:model="responsable_principal" id="resp_main" class="h-6 w-6 rounded-lg border-2 border-borde-suave bg-fondo-card text-terracota transition focus:ring-borde-focus/30">
 <label for="resp_main" class="text-sm font-bold text-titulo/70 cursor-pointer">Es responsable principal (Firma legal)</label>
 </div>
 <div class="flex items-center gap-3">
 <input type="checkbox" wire:model="autorizado_informacion_medica" id="aut_med" class="h-6 w-6 rounded-lg border-2 border-borde-suave bg-fondo-card text-terracota transition focus:ring-borde-focus/30">
 <label for="aut_med" class="text-sm font-bold text-titulo/70 cursor-pointer">Autorizado para recibir informes médicos</label>
 </div>
 </div>
 <div class="col-span-full space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Dirección del Contacto</label>
 <textarea wire:model="contacto_emergencia_direccion" rows="2" placeholder="Opcional. Si vive en otro domicilio..." class="w-full resize-none rounded-2xl border-2 {{ $errors->has('contacto_emergencia_direccion') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10"></textarea>
 @error('contacto_emergencia_direccion') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 {{-- Paso 5: Ingreso institucional --}}
 @if($paso == 5)
 <div class="grid gap-8 md:grid-cols-4 animate-fade-in">
 <div class="md:col-span-2 space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Fecha de Ingreso Institucional *</label>
 <input type="date" wire:model="fecha_ing" class="w-full rounded-2xl border-2 {{ $errors->has('fecha_ing') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 @error('fecha_ing') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="md:col-span-2 space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Hora de Ingreso</label>
 <input type="time" wire:model="hora_ing" class="w-full rounded-2xl border-2 border-transparent bg-fondo-card px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 </div>
 <div class="md:col-span-2 space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Tipo de Ingreso *</label>
 <select wire:model="tipo_ing" class="w-full rounded-2xl border-2 {{ $errors->has('tipo_ing') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 <option value="REGULAR">Regular</option>
 <option value="DERIVADO">Derivado</option>
 <option value="VOLUNTARIO">Voluntario</option>
 <option value="EMERGENCIA">Emergencia</option>
 <option value="OTRO">Otro</option>
 </select>
 @error('tipo_ing') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="md:col-span-2 space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Permanencia *</label>
 <select wire:model="permanencia" class="w-full rounded-2xl border-2 {{ $errors->has('permanencia') ? 'border-terracota/50 bg-boton-acento/5' : 'border-transparent bg-fondo-card' }} px-5 py-3.5 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10">
 <option value="">Seleccionar</option>
 <option value="PERMANENTE">Permanente</option>
 <option value="TEMPORAL">Temporal</option>
 <option value="EVENTUAL">Eventual</option>
 </select>
 @error('permanencia') <span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span> @enderror
 </div>
 <div class="col-span-full space-y-2">
 <label class="text-xs font-bold uppercase tracking-widest text-titulo/50">Observaciones de Ingreso</label>
 <textarea wire:model="observaciones" rows="3" placeholder="Anotaciones administrativas o clínicas preliminares..." class="w-full resize-none rounded-3xl border-2 border-transparent bg-fondo-card px-6 py-4 text-sm font-bold text-titulo shadow-sm outline-none transition focus:border-borde-focus/30 focus:ring-4 focus:ring-borde-focus/10"></textarea>
 </div>
 </div>
 @endif

 {{-- Paso 6: Consentimiento y confirmación --}}
 @if($paso == 6)
 <div class="max-w-2xl mx-auto space-y-10 animate-fade-in text-center py-4">
 <div class="flex flex-col items-center">
 <div class="h-24 w-24 rounded-full bg-estado-exitoBg text-parrafo flex items-center justify-center mb-4">
 <i class="ph-fill ph-shield-check text-5xl"></i>
 </div>
 <h3 class="text-2xl font-black text-titulo">¿Toda la información es correcta?</h3>
 <p class="text-sm font-bold text-titulo/50 mt-2">Revise detalladamente los pasos anteriores. Al guardar se generará el expediente digital oficial.</p>
 </div>

 <div class="rounded-[32px] bg-fondo-card p-8 shadow-inner border-2 border-borde-suave space-y-6">
 <div class="flex items-center gap-4 text-left">
 <div class="h-12 w-12 rounded-2xl bg-boton-principal/5 text-titulo flex items-center justify-center shrink-0">
 <i class="ph-bold ph-user-focus text-2xl"></i>
 </div>
 <div class="flex-1 min-w-0">
 <p class="text-[10px] font-bold uppercase text-titulo/30">Titular del expediente</p>
 <h4 class="text-lg font-extrabold text-titulo truncate">{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</h4>
 </div>
 </div>

 <div class="flex items-center gap-4 text-left">
 <div class="h-12 w-12 rounded-2xl bg-boton-principal/5 text-titulo flex items-center justify-center shrink-0">
 <i class="ph-bold ph-identification-card text-2xl"></i>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-titulo/30">Documento Identidad</p>
 <h4 class="text-lg font-extrabold text-titulo">{{ $ci }} {{ $complemento_ci }} - {{ $expedicion_ci }}</h4>
 </div>
 </div>

 <div class="flex items-start gap-3 mt-8 pt-6 border-t border-borde-suave">
 <input type="checkbox" wire:model="consentimiento_datos" id="cons_check" class="h-6 w-6 mt-1 rounded-lg border-2 border-borde-suave bg-fondo-card text-terracota transition focus:ring-borde-focus/30 cursor-pointer">
 <label for="cons_check" class="text-sm font-bold text-titulo/70 text-left cursor-pointer">
 Confirmo que el adulto mayor (o su responsable) otorga su consentimiento para el registro, almacenamiento y tratamiento de sus datos personales y clínicos en RememberMind conforme a las leyes de protección de datos vigentes. *
 </label>
 </div>
 @error('consentimiento_datos') <div class="text-left"><span class="text-xs font-bold text-terracota uppercase tracking-tight">{{ $message }}</span></div> @enderror
 </div>
 </div>
 @endif

 </div>

 {{-- Footer con Botones --}}
 <div class="border-t border-borde-suave p-4 sm:px-8 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4">
 <button type="button" wire:click="anterior" wire:loading.attr="disabled" @if($paso == 1) disabled @endif
 class="flex h-11 items-center justify-center gap-2 rounded-xl bg-fondo-card px-6 text-xs font-bold text-titulo shadow-sm transition hover:bg-boton-principal hover:text-inverso disabled:opacity-30 disabled:cursor-not-allowed active:scale-95">
 <i class="ph-bold ph-arrow-left"></i> Anterior
 </button>

 <div class="flex gap-3">
 <button type="button" wire:click="cerrar" wire:loading.attr="disabled" class="h-11 items-center justify-center rounded-xl bg-fondo-app px-5 text-xs font-bold text-titulo transition hover:bg-boton-acento hover:text-inverso active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed hidden sm:flex">
 Cancelar
 </button>

 @if($paso < $totalPasos)
 @php
 $bloquearSiguiente = $paso === 2 && $this->edad !== null && $this->edad < 60;
 @endphp
 <button type="button" wire:click="siguiente" wire:loading.attr="disabled" wire:target="siguiente"
 @disabled($bloquearSiguiente)
 class="flex h-11 items-center justify-center gap-2 rounded-xl bg-boton-principal px-8 text-xs font-bold text-inverso shadow-[0_12px_24px_-8px_rgba(47,62,92,0.4)] transition hover:-translate-y-1 hover:bg-fondo-panel active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
 <i wire:loading wire:target="siguiente" class="ph-bold ph-circle-notch animate-spin"></i>
 <i wire:loading.remove wire:target="siguiente" class="ph-bold ph-arrow-right"></i>
 <span wire:loading.remove wire:target="siguiente">{{ $bloquearSiguiente ? 'Corrija fecha de nacimiento' : 'Siguiente paso' }}</span>
 <span wire:loading wire:target="siguiente">Verificando...</span>
 </button>
 @else
 <button type="button" wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar"
 class="flex h-11 items-center justify-center gap-2 rounded-xl bg-boton-acento px-8 text-xs font-bold text-inverso shadow-[0_12px_24px_-8px_rgba(226,125,96,0.4)] transition hover:-translate-y-1 hover:bg-fondo-panel active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
 <i wire:loading wire:target="guardar" class="ph-bold ph-circle-notch animate-spin"></i>
 <i wire:loading.remove wire:target="guardar" class="ph-bold ph-floppy-disk"></i>
 <span wire:loading.remove wire:target="guardar">{{ $isEdit ? 'Actualizar' : 'Finalizar' }}</span>
 <span wire:loading wire:target="guardar">Guardando...</span>
 </button>
 @endif
 </div>
 </div>

 </div>
 </div>
 @endif

 <style>
 @keyframes fade-in {
 from { opacity: 0; transform: translateY(10px); }
 to { opacity: 1; transform: translateY(0); }
 }
 .animate-fade-in {
 animation: fade-in 0.4s ease-out forwards;
 }
 .custom-scrollbar::-webkit-scrollbar {
 width: 8px;
 }
 .custom-scrollbar::-webkit-scrollbar-track {
 background: transparent;
 }
 .custom-scrollbar::-webkit-scrollbar-thumb {
 background: #C7B5A3;
 border-radius: 10px;
 }
 .custom-scrollbar::-webkit-scrollbar-thumb:hover {
 background: #B5A391;
 }
 </style>
</div>
