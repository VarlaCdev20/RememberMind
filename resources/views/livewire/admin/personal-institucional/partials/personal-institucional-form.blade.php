<div x-data="{ isDirty: false }" x-on:input="isDirty = true" x-on:change="isDirty = true">
@if(!$esEdicion)
<div class="p-6 bg-white flex flex-col h-full rounded-[2rem]">
    <!-- Header Modal Flotante -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento shadow-inner">
                <i class="ph-fill ph-user-plus text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-titulo">Registrar Personal</h3>
                <p class="text-xs font-semibold text-apoyo">Paso {{ $pasoActual }} de 8</p>
            </div>
        </div>
        <button type="button" @click="
            if (isDirty) {
                Swal.fire({
                    title: '¿Salir sin guardar?',
                    text: 'Hay cambios sin guardar en el formulario. Si sale, perderá todos los datos.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, salir',
                    cancelButtonText: 'Permanecer'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.dispatch('cerrarModalGestion');
                    }
                });
            } else {
                $wire.dispatch('cerrarModalGestion');
            }
        " class="text-apoyo hover:text-estado-peligro transition-colors">
            <i class="ph-bold ph-x text-lg"></i>
        </button>
    </div>

    @php
        $pasoActualsLista = [
            1 => 'Acceso',
            2 => 'Identificación',
            3 => 'Contacto',
            4 => 'Rol',
            5 => 'Clasificación',
            6 => 'Laboral',
            7 => 'Documentación',
            8 => 'Confirmación'
        ];
        
        $pasosErrores = [
            1 => $errors->has('correo') || $errors->has('estado') || $errors->has('fecha_registro') || $errors->has('hora_registro'),
            2 => $errors->has('nombres') || $errors->has('ap_paterno') || $errors->has('ap_materno') || $errors->has('numero_documento') || $errors->has('expedido') || $errors->has('genero') || $errors->has('fecha_nacimiento') || $errors->has('foto_perfil'),
            3 => $errors->has('telefono') || $errors->has('telefono_alternativo') || $errors->has('ciudad_id') || $errors->has('municipio_id') || $errors->has('zona_id') || $errors->has('otra_zona') || $errors->has('calle_id') || $errors->has('otra_calle') || $errors->has('nro_casa'),
            4 => $errors->has('roles_seleccionados'),
            5 => false,
            6 => $errors->has('cod_esp') || $errors->has('anios_exp') || $errors->has('matricula_prof') || $errors->has('institucion_formacion') || $errors->has('subtipo_enfermeria') || $errors->has('cod_cargo_admin'),
            7 => collect($errors->all())->keys()->contains(fn($k) => str_starts_with($k, 'archivos_temporales')) || $errors->has('documentos_generales'),
            8 => false
        ];

        $progresoPorcentaje = round((($pasoActual - 1) / 7) * 100);
    @endphp

    <!-- Progreso Porcentual & Stepper -->
    <div class="mb-8 space-y-6">
        <!-- Barra de progreso -->
        <div class="bg-fondo p-4 rounded-[1.5rem] border border-borde/70 shadow-sm transition-all duration-300">
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-boton-acento animate-pulse"></span>
                    <span class="text-xs font-black text-titulo tracking-wider uppercase">Progreso de Registro</span>
                </div>
                <span class="text-xs font-black text-boton-acento bg-boton-acento/10 px-3 py-1 rounded-full border border-boton-acento/20 select-none">{{ $progresoPorcentaje }}% Completado</span>
            </div>
            <div class="w-full bg-borde/45 h-3 rounded-full overflow-hidden p-[2px]">
                <div class="bg-gradient-to-r from-boton-acento/80 to-boton-acento h-full rounded-full transition-all duration-500 ease-out shadow-[0_0_10px_rgba(63,125,90,0.2)]" style="width: {{ $progresoPorcentaje }}%"></div>
            </div>
        </div>

        <!-- Stepper dinámico -->
        <div class="relative flex items-center justify-between w-full px-2 py-4 overflow-x-auto select-none no-scrollbar">
            <!-- Línea de fondo -->
            <div class="absolute left-6 right-6 top-1/2 transform -translate-y-1/2 h-1 bg-borde/50 z-0"></div>
            <!-- Línea de progreso -->
            <div class="absolute left-6 top-1/2 transform -translate-y-1/2 h-1 bg-boton-acento z-0 transition-all duration-500 ease-out" style="width: calc({{ (($pasoActual - 1) / 7) * 100 }}% - 3rem)"></div>
            
            @foreach($pasoActualsLista as $num => $nombre)
                <div class="relative z-10 flex flex-col items-center shrink-0 mx-2">
                    @php
                        $hasError = $pasosErrores[$num] ?? false;
                        $isCompleted = $pasoActual > $num;
                        $isActive = $pasoActual == $num;
                    @endphp
                    
                    <button type="button" wire:click="gotoStep({{ $num }})" @if($num > $pasoActual && !$isCompleted) disabled @endif class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 {{ 
                        $hasError 
                            ? 'bg-estado-peligroBg text-estado-peligro border-2 border-estado-peligro shadow-[0_0_10px_rgba(239,68,68,0.2)] hover:scale-105' 
                            : ($isActive 
                                ? 'bg-boton-acento text-white ring-4 ring-boton-acento/20 scale-110 shadow-[0_0_15px_rgba(63,125,90,0.3)]' 
                                : ($isCompleted 
                                    ? 'bg-boton-acento/10 text-boton-acento border-2 border-boton-acento/30 hover:bg-boton-acento/20 hover:scale-105' 
                                    : 'bg-fondo-hover text-apoyo/50 border border-borde cursor-not-allowed'))
                    }}">
                        @if($hasError)
                            <i class="ph-bold ph-warning-circle text-lg"></i>
                        @elseif($isCompleted)
                            <i class="ph-bold ph-check text-sm"></i>
                        @else
                            {{ $num }}
                        @endif
                    </button>
                    <span class="absolute top-11 text-[9px] font-black uppercase tracking-wider whitespace-nowrap transition-colors duration-300 {{ 
                        $hasError 
                            ? 'text-estado-peligro' 
                            : ($isActive 
                                ? 'text-boton-acento font-black' 
                                : ($isCompleted 
                                    ? 'text-titulo/80 font-bold' 
                                    : 'text-apoyo/40')) 
                    }} hidden md:block">{{ $nombre }}</span>
                </div>
            @endforeach
        </div>
        <div class="text-center md:hidden bg-fondo p-2.5 border border-borde/60 rounded-xl">
            <span class="text-xs font-black text-boton-acento uppercase tracking-wider">Paso {{ $pasoActual }} de 8: {{ $pasoActualsLista[$pasoActual] }}</span>
        </div>
    </div>

    <!-- Wizard Form -->
    <form wire:submit.prevent="guardar" class="flex flex-col flex-1" x-on:keydown.enter="
        if ($event.target.tagName === 'INPUT' && ['text', 'email', 'date', 'number', 'tel'].includes($event.target.type)) {
            $event.preventDefault();
            $wire.avanzarPaso();
        }
    ">
        <div class="flex-1 space-y-4">
            @if($pasoActual == 1)
                <!-- Paso 1: Usuario de acceso -->
                <div class="space-y-4">
                    <h4 class="text-md font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-key text-boton-acento"></i> 1. Acceso al sistema
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Correo Institucional o Personal <span class="text-estado-peligro">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="ph-bold ph-envelope-simple text-apoyo text-lg"></i>
                                    </div>
                                    <input type="email" wire:model.live="correo" x-on:input="$el.value = $el.value.toLowerCase().replace(/\s/g, '')" x-init="$nextTick(() => $el.focus())" class="pl-10 w-full rounded-xl {{ $errors->has('correo') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" placeholder="ejemplo@institucion.com">
                                </div>
                                @error('correo') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Fecha de Registro</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="ph-bold ph-calendar text-apoyo"></i>
                                        </div>
                                        <input type="text" value="{{ \Carbon\Carbon::parse($fecha_registro)->format('d/m/Y') }}" class="pl-10 w-full rounded-xl {{ $errors->has('fecha_registro') ? 'border-estado-peligro' : 'border-input-borde' }} bg-fondo-hover text-sm font-bold text-titulo opacity-80 cursor-not-allowed" readonly>
                                    </div>
                                    @error('fecha_registro') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Hora</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="ph-bold ph-clock text-apoyo"></i>
                                        </div>
                                        <input type="text" value="{{ \Carbon\Carbon::parse($hora_registro)->format('H:i') }}" class="pl-10 w-full rounded-xl {{ $errors->has('hora_registro') ? 'border-estado-peligro' : 'border-input-borde' }} bg-fondo-hover text-sm font-bold text-titulo opacity-80 cursor-not-allowed" readonly>
                                    </div>
                                    @error('hora_registro') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Estado de Acceso</label>
                                <div class="flex items-center gap-2 p-2.5 bg-estado-exitoBg border border-estado-exito/30 rounded-xl">
                                    <i class="ph-fill ph-check-circle text-estado-exito text-xl"></i>
                                    <span class="text-sm font-bold text-estado-exito tracking-wider">ACTIVO</span>
                                    <input type="hidden" wire:model="estado">
                                </div>
                                @error('estado') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="p-4 bg-estado-infoBg border border-estado-infoBorde rounded-xl">
                                <h5 class="text-xs font-bold text-estado-info uppercase tracking-wider mb-2 flex items-center gap-1"><i class="ph-bold ph-paper-plane-tilt"></i> Notificación Automática</h5>
                                <p class="text-[12px] text-titulo/90 leading-relaxed">
                                    El sistema generará credenciales temporales y enviará automáticamente al correo del personal la bienvenida institucional, su rol, credenciales iniciales, horarios asignados, contrato y documentos pendientes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($pasoActual == 2)
                <!-- Paso 2: Identificación Personal -->
                <div class="space-y-6">
                    <h4 class="text-md font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-identification-card text-boton-acento"></i> 2. Identificación Personal
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Columna Foto -->
                        <div class="md:col-span-1 flex flex-col items-center justify-center border-2 border-dashed border-borde rounded-xl p-4 bg-input-bg text-center">
                            @if($foto_perfil && !is_string($foto_perfil))
                                <img src="{{ $foto_perfil->temporaryUrl() }}" class="w-32 h-32 object-cover rounded-full shadow-md border-4 border-white mb-3">
                            @elseif($foto_perfil && is_string($foto_perfil))
                                <img src="{{ asset('storage/' . $foto_perfil) }}" class="w-32 h-32 object-cover rounded-full shadow-md border-4 border-white mb-3">
                            @else
                                <div class="w-32 h-32 bg-fondo-hover rounded-full flex items-center justify-center text-apoyo mb-3">
                                    <i class="ph-bold ph-user text-4xl"></i>
                                </div>
                            @endif
                            <label class="cursor-pointer bg-white border border-borde text-titulo font-bold text-xs px-4 py-2 rounded-xl hover:bg-fondo-hover transition-colors flex items-center gap-2">
                                <span wire:loading.remove wire:target="foto_perfil"><i class="ph-bold ph-upload-simple"></i> Subir Fotografía</span>
                                <span wire:loading wire:target="foto_perfil" class="flex items-center gap-1"><i class="ph-bold ph-spinner animate-spin"></i> Cargando...</span>
                                <input type="file" wire:model="foto_perfil" class="hidden" accept=".jpg,.jpeg,.png,.webp">
                            </label>
                            <p class="text-[10px] text-apoyo mt-2">JPG, PNG o WEBP (Máx. 2MB)</p>
                            @error('foto_perfil') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Columna Datos Personales -->
                        <div class="md:col-span-2 space-y-4">
                            <h5 class="text-xs font-bold text-apoyo uppercase tracking-wider border-b border-borde pb-1">Datos de Identidad</h5>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Nombres <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.blur="nombres" x-init="$nextTick(() => $el.focus())" class="w-full rounded-xl {{ $errors->has('nombres') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase">
                                    @error('nombres') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Ap. Paterno</label>
                                    <input type="text" wire:model.blur="ap_paterno" class="w-full rounded-xl {{ $errors->has('ap_paterno') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase">
                                    @error('ap_paterno') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Ap. Materno</label>
                                    <input type="text" wire:model.blur="ap_materno" class="w-full rounded-xl {{ $errors->has('ap_materno') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase">
                                    @error('ap_materno') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Género <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="genero" class="w-full rounded-xl {{ $errors->has('genero') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                    @error('genero') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-4">
                                <div class="col-span-3">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Nro. Documento / CI <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.blur="numero_documento" class="w-full rounded-xl {{ $errors->has('numero_documento') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" placeholder="Ej: 1234567">
                                    @error('numero_documento') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Exp. <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="expedido" class="w-full rounded-xl {{ $errors->has('expedido') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                        <option value="">...</option>
                                        <option value="LP">LP</option>
                                        <option value="CB">CB</option>
                                        <option value="SC">SC</option>
                                        <option value="PT">PT</option>
                                        <option value="OR">OR</option>
                                        <option value="TJ">TJ</option>
                                        <option value="CH">CH</option>
                                        <option value="BE">BE</option>
                                        <option value="PD">PD</option>
                                    </select>
                                    @error('expedido') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Fecha Nacimiento <span class="text-estado-peligro">*</span></label>
                                    <input type="date" wire:model.live="fecha_nacimiento" class="w-full rounded-xl {{ $errors->has('fecha_nacimiento') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                    @error('fecha_nacimiento') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Edad (Años)</label>
                                    <input type="text" wire:model="edad" class="w-full rounded-xl border-input-borde bg-fondo-hover text-sm font-bold text-titulo opacity-80 cursor-not-allowed text-center" readonly placeholder="-">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            @elseif($pasoActual == 3)
                <!-- Paso 3: Contacto -->
                <div class="space-y-6">
                    <h4 class="text-md font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-phone text-boton-acento"></i> 3. Datos de Contacto
                    </h4>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Celular <span class="text-estado-peligro">*</span></label>
                                <input type="text" wire:model="telefono" x-init="$nextTick(() => $el.focus())" class="w-full rounded-xl {{ $errors->has('telefono') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" placeholder="Ej: 77712345">
                                @error('telefono') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Teléfono Alternativo</label>
                                <input type="text" wire:model="telefono_alternativo" class="w-full rounded-xl {{ $errors->has('telefono_alternativo') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                @error('telefono_alternativo') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Correo Registrado</label>
                                <input type="email" wire:model="correo" class="w-full rounded-xl border-input-borde bg-fondo-hover text-sm text-apoyo cursor-not-allowed" disabled readonly>
                            </div>
                        </div>

                        <!-- Dirección Jerárquica -->
                        <div class="bg-fondo/30 p-5 rounded-[1.5rem] border border-borde/60 space-y-4">
                            <h5 class="text-xs font-bold text-apoyo uppercase tracking-wider border-b border-borde pb-1 flex items-center gap-1.5"><i class="ph-bold ph-map-pin text-boton-acento"></i> Dirección de Residencia</h5>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Departamento / Ciudad -->
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Departamento / Ciudad <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="ciudad_id" class="w-full rounded-xl {{ $errors->has('ciudad_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                        <option value="">Seleccione Departamento...</option>
                                        @foreach($departamentos_list as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('ciudad_id') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Municipio -->
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Municipio <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="municipio_id" class="w-full rounded-xl {{ $errors->has('municipio_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" {{ empty($ciudad_id) ? 'disabled' : '' }}>
                                        <option value="">Seleccione Municipio...</option>
                                        @foreach($municipios_list as $mun)
                                            <option value="{{ $mun->id }}">{{ $mun->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('municipio_id') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Zona / Barrio -->
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Zona / Barrio <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="zona_id" class="w-full rounded-xl {{ $errors->has('zona_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" {{ empty($municipio_id) ? 'disabled' : '' }}>
                                        <option value="">Seleccione Zona...</option>
                                        @foreach($zonas_list as $z)
                                            <option value="{{ $z->id }}">{{ $z->nombre }}</option>
                                        @endforeach
                                        @if($municipio_id)
                                            <option value="OTRA">OTRA ZONA / NO REGISTRADA</option>
                                        @endif
                                    </select>
                                    @error('zona_id') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Avenida / Calle -->
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Avenida / Calle <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="calle_id" class="w-full rounded-xl {{ $errors->has('calle_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" {{ empty($zona_id) ? 'disabled' : '' }}>
                                        <option value="">Seleccione Avenida/Calle...</option>
                                        @if($zona_id && $zona_id !== 'OTRA')
                                            @foreach($calles_list as $c)
                                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                            @endforeach
                                        @endif
                                        @if($zona_id)
                                            <option value="OTRA">OTRA CALLE / AVENIDA</option>
                                        @endif
                                    </select>
                                    @error('calle_id') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Campos dinámicos para "Otro" -->
                            @if($zona_id === 'OTRA' || $calle_id === 'OTRA')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-yellow-50/50 p-4 rounded-xl border border-yellow-200">
                                    @if($zona_id === 'OTRA')
                                        <div>
                                            <label class="block text-xs font-bold text-estado-advertencia uppercase tracking-wider mb-1">Especificar Otra Zona <span class="text-estado-peligro">*</span></label>
                                            <input type="text" wire:model.blur="otra_zona" class="w-full rounded-xl border-yellow-300 bg-white text-sm uppercase focus:ring-yellow-400 focus:border-yellow-400" placeholder="Ej: VILLA ARMONÍA">
                                            @error('otra_zona') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    @if($calle_id === 'OTRA')
                                        <div>
                                            <label class="block text-xs font-bold text-estado-advertencia uppercase tracking-wider mb-1">Especificar Otra Calle/Avenida <span class="text-estado-peligro">*</span></label>
                                            <input type="text" wire:model.blur="otra_calle" class="w-full rounded-xl border-yellow-300 bg-white text-sm uppercase focus:ring-yellow-400 focus:border-yellow-400" placeholder="Ej: CALLE 15 DE SEPTIEMBRE">
                                            @error('otra_calle') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Número de casa -->
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Número de Casa / Edificio / Dpto <span class="text-estado-peligro">*</span></label>
                                <input type="text" wire:model.blur="nro_casa" class="w-full rounded-xl {{ $errors->has('nro_casa') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase" placeholder="Ej: Nro 1234, Piso 2, Dpto B (ó S/N)">
                                @error('nro_casa') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($pasoActual == 4)
                <!-- Paso 4: Rol del sistema -->
                <div class="space-y-4">
                    <h4 class="text-md font-bold text-titulo border-b border-borde pb-2"><i class="ph-bold ph-shield"></i> 3. Rol del sistema</h4>
                    <p class="text-sm text-apoyo mb-4">Selecciona los roles de acceso y permisos dentro de RememberMind.</p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $rol)
                            <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-fondo-hover transition-colors {{ in_array($rol->name, $roles_seleccionados) ? 'border-boton-acento bg-boton-acento/5' : 'border-borde bg-white' }}">
                                <input type="checkbox" wire:model.live="roles_seleccionados" value="{{ $rol->name }}" @if($loop->first) x-init="$nextTick(() => $el.focus())" @endif class="rounded text-boton-acento focus:ring-boton-acento h-5 w-5">
                                <span class="text-sm font-bold text-titulo">{{ $rol->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles_seleccionados') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror

                    @if(!empty($roles_seleccionados))
                    <div class="mt-4 p-4 bg-estado-infoBg border border-estado-infoBorde rounded-xl">
                        <h5 class="text-xs font-bold text-estado-info uppercase tracking-wider mb-2"><i class="ph-bold ph-eye"></i> Vista Previa de Acceso</h5>
                        <p class="text-sm font-bold text-titulo">Este usuario podrá acceder como: <span class="text-estado-info">{{ implode(', ', $roles_seleccionados) }}</span></p>
                        <p class="text-xs text-apoyo mt-1">El usuario heredará todos los permisos operativos y módulos correspondientes a estos roles.</p>
                    </div>
                    @endif
                </div>

            @elseif($pasoActual == 5)
                <!-- Paso 5: Clasificación Institucional -->
                <div class="space-y-4">
                    <h4 class="text-md font-bold text-titulo border-b border-borde pb-2"><i class="ph-bold ph-briefcase"></i> 5. Clasificación Institucional</h4>
                    <p class="text-sm text-apoyo mb-4">El área institucional y el perfil operativo se derivan del rol seleccionado.</p>

                    @php($clasificacion = $clasificacion_derivada)

                    @if($clasificacion)
                        <div class="p-4 {{ $clasificacion['tipo_personal'] === 'salud' ? 'bg-estado-infoBg border-estado-infoBorde' : 'bg-estado-advertenciaBg border-estado-advertenciaBorde' }} border rounded-xl space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h5 class="text-xs font-bold uppercase tracking-wider {{ $clasificacion['tipo_personal'] === 'salud' ? 'text-estado-info' : 'text-estado-advertencia' }}">Clasificación automática</h5>
                                    <p class="text-sm font-bold text-titulo">{{ $clasificacion['rol_sistema'] }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-black text-titulo border border-borde">
                                    <i class="ph-bold ph-check-circle"></i> Derivado
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="rounded-xl border border-borde bg-white p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Tipo</span>
                                    <span class="text-sm font-black text-titulo">{{ $clasificacion['tipo_label'] }}</span>
                                </div>
                                <div class="rounded-xl border border-borde bg-white p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Rol operativo</span>
                                    <span class="text-sm font-black text-titulo">{{ $clasificacion['rol_label'] }}</span>
                                </div>
                                <div class="rounded-xl border border-borde bg-white p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Área</span>
                                    <span class="text-sm font-black text-titulo">{{ $clasificacion['area_nombre'] }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-estado-peligroBg border border-estado-peligro/30 rounded-xl text-estado-peligro text-sm font-bold">
                            <i class="ph-bold ph-warning-circle"></i> Seleccione un rol institucional válido en el paso anterior.
                        </div>
                    @endif
                    @error('roles_seleccionados') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                </div>

            @elseif($pasoActual == 6)
                <!-- Paso 6: Datos Laborales -->
                <div class="space-y-6">
                    <h4 class="text-md font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-briefcase text-boton-acento"></i> 6. Datos Laborales
                    </h4>
                    
                    @if($tipo_personal === 'salud')
                        <div class="space-y-4">
                            <div class="p-4 bg-boton-acento/5 border border-boton-acento/20 rounded-2xl flex items-center gap-3 mb-2">
                                <i class="ph-bold ph-shield-check text-boton-acento text-xl"></i>
                                <div>
                                    <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Perfil Clínico / Salud</h5>
                                    <p class="text-xs text-apoyo">Complete los datos de la especialidad, experiencia y matrícula profesional.</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Especialidad Médica / de Salud</label>
                                    <select wire:model="cod_esp" class="w-full rounded-xl {{ $errors->has('cod_esp') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                        <option value="">Seleccione Especialidad...</option>
                                        @foreach($especialidades_list as $esp)
                                            <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('cod_esp') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Años de Experiencia <span class="text-estado-peligro">*</span></label>
                                    <input type="number" wire:model.blur="anios_exp" class="w-full rounded-xl {{ $errors->has('anios_exp') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" placeholder="Ej: 5">
                                    @error('anios_exp') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Matrícula Profesional / Respaldo</label>
                                    <input type="text" wire:model.blur="matricula_prof" class="w-full rounded-xl {{ $errors->has('matricula_prof') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase" placeholder="Ej: MP-98765-LP">
                                    @error('matricula_prof') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Universidad / Institución de Formación</label>
                                    <input type="text" wire:model.blur="institucion_formacion" class="w-full rounded-xl {{ $errors->has('institucion_formacion') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase" placeholder="Ej: UMSA">
                                    @error('institucion_formacion') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>

                                @if(($clasificacion_derivada['rol_operativo'] ?? '') === 'ENFERMERO')
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Subtipo de Enfermería <span class="text-estado-peligro">*</span></label>
                                        <select wire:model="subtipo_enfermeria" class="w-full rounded-xl {{ $errors->has('subtipo_enfermeria') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                            <option value="">Seleccione Subtipo...</option>
                                            <option value="GENERAL_ADMISION">Enfermero General de Admisión</option>
                                            <option value="ESPECIALIZADO_TURNO">Enfermero Especializado de Turno</option>
                                        </select>
                                        @error('subtipo_enfermeria') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif($tipo_personal === 'admin')
                        <div class="space-y-4">
                            <div class="p-4 bg-yellow-500/5 border border-yellow-500/20 rounded-2xl flex items-center gap-3 mb-2">
                                <i class="ph-bold ph-shield-star text-yellow-600 text-xl"></i>
                                <div>
                                    <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Perfil Administrativo</h5>
                                    <p class="text-xs text-apoyo">Complete los detalles de su cargo y funciones administrativas.</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Cargo Administrativo Asignado</label>
                                    <select wire:model="cod_cargo_admin" class="w-full rounded-xl {{ $errors->has('cod_cargo_admin') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                        <option value="">Seleccione Cargo...</option>
                                        @foreach($cargos_list as $crg)
                                            <option value="{{ $crg->cod_cargo_admin }}">{{ $crg->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('cod_cargo_admin') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-estado-peligroBg border border-estado-peligro/30 rounded-xl text-estado-peligro text-sm font-bold">
                            <i class="ph-bold ph-warning-circle"></i> Complete la clasificación del rol en el paso anterior.
                        </div>
                    @endif
                </div>

            @elseif($pasoActual == 7)
                <!-- Paso 7: Documentación -->
                <div class="space-y-6">
                    <div class="flex justify-between items-center border-b border-borde pb-2">
                        <h4 class="text-md font-black text-titulo flex items-center gap-2"><i class="ph-bold ph-folder-open text-boton-acento"></i> Gestión de Expediente Digital</h4>
                        <div class="flex items-center gap-2 text-xs text-apoyo">
                            <span class="px-2.5 py-1 rounded-full bg-estado-advertenciaBg text-estado-advertencia font-bold border border-estado-advertenciaBorde">
                                Plazo de regularización: 48 horas
                            </span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @php
                            $docsAgrupados = collect($this->documentos_configurados)->groupBy('tipo');
                        @endphp

                        <!-- BLOQUE 1: Documentación del Ingresante -->
                        <div class="space-y-4 bg-fondo/35 p-5 rounded-[1.5rem] border border-borde/60">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-identification-card text-lg text-boton-acento"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Documentación del Ingresante</h5>
                            </div>
                            
                            <div class="space-y-3">
                                @if(isset($docsAgrupados['ingresante']))
                                    @foreach($docsAgrupados['ingresante'] as $doc)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl bg-white shadow-sm gap-4 transition-all hover:border-boton-acento/40 {{ ($doc['obligatorio'] && !isset($archivos_temporales[$doc['id']]) && !isset($estado_documentos[$doc['id']])) ? 'border-estado-peligro/30 bg-estado-peligroBg/10' : 'border-borde' }}">
                                            <div class="flex items-start gap-3 flex-1">
                                                <div class="w-10 h-10 rounded-full {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'bg-estado-exitoBg text-estado-exito' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'bg-estado-peligroBg text-estado-peligro' : 'bg-fondo text-apoyo') }} flex items-center justify-center shrink-0 border border-borde/40">
                                                    <i class="ph-fill {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'ph-check-circle' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'ph-warning-circle' : 'ph-file-text') }} text-xl"></i>
                                                </div>
                                                <div class="space-y-1">
                                                    <h6 class="text-sm font-bold text-titulo flex items-center flex-wrap gap-2">
                                                        {{ $doc['nombre'] }}
                                                        @if($doc['obligatorio'])
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase tracking-wider">Obligatorio</span>
                                                        @else
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente (48h)</span>
                                                        @endif
                                                    </h6>
                                                    <p class="text-[10px] text-apoyo">{{ $doc['desc'] }}</p>
                                                    
                                                    @if(isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO')
                                                        <div class="mt-2 p-2 bg-estado-peligroBg rounded text-xs text-estado-peligro border border-estado-peligro/20">
                                                            <span class="font-bold">Observación:</span> {{ $observacion_documentos[$doc['id']] }}
                                                        </div>
                                                    @endif
                                                    
                                                    @error("archivos_temporales." . $doc['id'])
                                                        <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                @if(isset($archivos_temporales[$doc['id']]))
                                                    <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-estado-peligro border border-estado-peligro rounded-lg hover:bg-estado-peligroBg transition-colors" title="Eliminar archivo">
                                                        <i class="ph-bold ph-trash"></i> Eliminar
                                                    </button>
                                                @else
                                                    <div x-data="{ observar: false }" class="flex items-center gap-2 relative">
                                                        @if(!isset($estado_documentos[$doc['id']]) || $estado_documentos[$doc['id']] !== 'OBSERVADO')
                                                            <label class="cursor-pointer px-4 py-1.5 text-xs font-bold text-boton-acento border border-boton-acento rounded-lg hover:bg-boton-acento/10 transition-colors">
                                                                <i class="ph-bold ph-upload-simple"></i> Subir Archivo
                                                                <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                            </label>
                                                            <button type="button" @click="observar = !observar" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors" title="Marcar como observado si el documento físico es incorrecto">
                                                                <i class="ph-bold ph-eye"></i> Observar
                                                            </button>
                                                        @else
                                                            <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors">
                                                                Deshacer Observación
                                                            </button>
                                                        @endif
                                                        
                                                        <div x-show="observar" x-cloak class="absolute z-10 bg-white p-3 border border-borde rounded-xl shadow-lg mt-10 right-0 w-64" @click.away="observar = false">
                                                            <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Motivo de la Observación</label>
                                                            <textarea id="obs_{{ $doc['id'] }}" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-xs focus:ring-input-ringFocus focus:border-input-bordeFocus mb-2"></textarea>
                                                            <div class="flex justify-end gap-2">
                                                                <button type="button" @click="observar = false" class="text-xs text-apoyo font-bold px-2 py-1">Cancelar</button>
                                                                <button type="button" @click="$wire.observarDocumento('{{ $doc['id'] }}', document.getElementById('obs_{{ $doc['id'] }}').value); observar = false" class="text-xs text-white bg-estado-peligro font-bold px-3 py-1 rounded-lg">Guardar Obs.</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- BLOQUE 2: Documentación Institucional -->
                        <div class="space-y-4 bg-fondo/35 p-5 rounded-[1.5rem] border border-borde/60">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-briefcase text-lg text-boton-acento"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Documentación Institucional</h5>
                            </div>
                            
                            <div class="space-y-3">
                                @if(isset($docsAgrupados['institucional']))
                                    @foreach($docsAgrupados['institucional'] as $doc)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl bg-white shadow-sm gap-4 transition-all hover:border-boton-acento/40 {{ ($doc['obligatorio'] && !isset($archivos_temporales[$doc['id']]) && !isset($estado_documentos[$doc['id']])) ? 'border-estado-peligro/30 bg-estado-peligroBg/10' : 'border-borde' }}">
                                            <div class="flex items-start gap-3 flex-1">
                                                <div class="w-10 h-10 rounded-full {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'bg-estado-exitoBg text-estado-exito' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'bg-estado-peligroBg text-estado-peligro' : 'bg-fondo text-apoyo') }} flex items-center justify-center shrink-0 border border-borde/40">
                                                    <i class="ph-fill {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'ph-check-circle' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'ph-warning-circle' : 'ph-file-text') }} text-xl"></i>
                                                </div>
                                                <div class="space-y-1">
                                                    <h6 class="text-sm font-bold text-titulo flex items-center flex-wrap gap-2">
                                                        {{ $doc['nombre'] }}
                                                        @if($doc['obligatorio'])
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase tracking-wider">Obligatorio</span>
                                                        @else
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente (48h)</span>
                                                        @endif
                                                    </h6>
                                                    <p class="text-[10px] text-apoyo">{{ $doc['desc'] }}</p>
                                                    
                                                    @if(isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO')
                                                        <div class="mt-2 p-2 bg-estado-peligroBg rounded text-xs text-estado-peligro border border-estado-peligro/20">
                                                            <span class="font-bold">Observación:</span> {{ $observacion_documentos[$doc['id']] }}
                                                        </div>
                                                    @endif
                                                    
                                                    @error("archivos_temporales." . $doc['id'])
                                                        <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                @if(isset($archivos_temporales[$doc['id']]))
                                                    <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-estado-peligro border border-estado-peligro rounded-lg hover:bg-estado-peligroBg transition-colors" title="Eliminar archivo">
                                                        <i class="ph-bold ph-trash"></i> Eliminar
                                                    </button>
                                                @else
                                                    <div x-data="{ observar: false }" class="flex items-center gap-2 relative">
                                                        @if(!isset($estado_documentos[$doc['id']]) || $estado_documentos[$doc['id']] !== 'OBSERVADO')
                                                            <label class="cursor-pointer px-4 py-1.5 text-xs font-bold text-boton-acento border border-boton-acento rounded-lg hover:bg-boton-acento/10 transition-colors">
                                                                <i class="ph-bold ph-upload-simple"></i> Subir Archivo
                                                                <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                            </label>
                                                            <button type="button" @click="observar = !observar" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors" title="Marcar como observado si el documento físico es incorrecto">
                                                                <i class="ph-bold ph-eye"></i> Observar
                                                            </button>
                                                        @else
                                                            <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors">
                                                                Deshacer Observación
                                                            </button>
                                                        @endif
                                                        
                                                        <div x-show="observar" x-cloak class="absolute z-10 bg-white p-3 border border-borde rounded-xl shadow-lg mt-10 right-0 w-64" @click.away="observar = false">
                                                            <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Motivo de la Observación</label>
                                                            <textarea id="obs_{{ $doc['id'] }}" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-xs focus:ring-input-ringFocus focus:border-input-bordeFocus mb-2"></textarea>
                                                            <div class="flex justify-end gap-2">
                                                                <button type="button" @click="observar = false" class="text-xs text-apoyo font-bold px-2 py-1">Cancelar</button>
                                                                <button type="button" @click="$wire.observarDocumento('{{ $doc['id'] }}', document.getElementById('obs_{{ $doc['id'] }}').value); observar = false" class="text-xs text-white bg-estado-peligro font-bold px-3 py-1 rounded-lg">Guardar Obs.</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @error('documentos_generales')
                        <div class="p-3 bg-estado-peligroBg text-estado-peligro border border-estado-peligro/30 rounded-xl text-sm font-bold mt-4 flex items-center gap-2">
                            <i class="ph-bold ph-warning"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

            @elseif($pasoActual == 8)
                <!-- Paso 8: Confirmación -->
                <div class="space-y-6">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-boton-acento/10 text-boton-acento mb-3">
                            <i class="ph-bold ph-check-square-offset text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-titulo">Confirmación de Registro</h4>
                        <p class="text-sm text-apoyo">Revise el resumen de la información ingresada antes de procesar el alta en el sistema</p>
                    </div>
                    @php $clasificacionResumen = $clasificacion_derivada; @endphp

                    <!-- Resumen por Secciones -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                        <!-- Sección: Acceso -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-key text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Datos de Acceso</h5>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-apoyo">Correo:</span> <span class="font-semibold text-titulo">{{ $correo }}</span></div>
                                <div class="flex justify-between"><span class="text-apoyo">Estado:</span> 
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $estado === 'ACTIVO' ? 'bg-estado-exitoBg text-estado-exito border border-estado-exito/20' : ($estado === 'PENDIENTE_INSTITUCIONAL' ? 'bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde' : 'bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20') }}">
                                        {{ $estado }}
                                    </span>
                                </div>
                                <div class="flex justify-between"><span class="text-apoyo">Contraseña:</span> <span class="font-semibold text-estado-info font-mono">Autogenerada</span></div>
                            </div>
                        </div>

                        <!-- Sección: Identificación -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-user text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Identificación</h5>
                            </div>
                            <div class="flex gap-3">
                                @if($foto_perfil && !is_string($foto_perfil))
                                    <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0 border border-borde bg-fondo">
                                        <img src="{{ $foto_perfil->temporaryUrl() }}" class="w-full h-full object-cover">
                                    </div>
                                @elseif($foto_perfil && is_string($foto_perfil))
                                    <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0 border border-borde bg-fondo">
                                        <img src="{{ asset('storage/' . $foto_perfil) }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-16 h-16 rounded-xl bg-fondo shrink-0 border border-borde flex items-center justify-center text-apoyo">
                                        <i class="ph-fill ph-user-circle text-4xl"></i>
                                    </div>
                                @endif
                                <div class="space-y-1 text-xs flex-1">
                                    <p class="font-bold text-titulo text-sm leading-tight">{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</p>
                                    <p class="text-[10px] text-apoyo">C.I.: <span class="font-bold text-titulo">{{ $numero_documento }} {{ $expedido }}</span></p>
                                    <p class="text-[10px] text-apoyo">Edad: <span class="font-semibold text-titulo">{{ $edad }} años</span> (Nacimiento: {{ $fecha_nacimiento }})</p>
                                    <p class="text-[10px] text-apoyo">Género: <span class="font-semibold text-titulo">{{ $genero === 'F' ? 'Femenino' : 'Masculino' }}</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Contacto -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-phone text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Contacto y Ubicación</h5>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-apoyo">Celular:</span> <span class="font-semibold text-titulo">{{ $telefono }}</span></div>
                                <div class="flex justify-between"><span class="text-apoyo">Teléfono Alt:</span> <span class="font-semibold text-titulo">{{ $telefono_alternativo ?: 'Ninguno' }}</span></div>
                                <div class="pt-1 border-t border-borde/40">
                                    <span class="text-[9px] font-bold text-apoyo uppercase block">Dirección:</span>
                                    <p class="text-titulo font-medium leading-relaxed mt-0.5 text-[10px]">{{ $direccion }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Rol & Clasificación -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-shield-check text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Rol &amp; Clasificación</h5>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-apoyo">Roles del Sistema:</span> <span class="font-bold text-titulo">{{ empty($roles_seleccionados) ? 'Ninguno' : implode(', ', $roles_seleccionados) }}</span></div>
                                <div class="flex justify-between"><span class="text-apoyo">Tipo Personal:</span> <span class="font-semibold text-titulo uppercase">{{ $clasificacionResumen['tipo_label'] ?? 'Sin clasificar' }}</span></div>
                                <div class="flex justify-between"><span class="text-apoyo">Rol Operativo:</span> <span class="font-semibold text-titulo">{{ $clasificacionResumen['rol_label'] ?? 'Sin rol operativo' }}</span></div>
                                <div class="flex justify-between"><span class="text-apoyo">Área Asignada:</span> <span class="font-bold text-boton-acento">{{ $clasificacionResumen['area_nombre'] ?? 'Sin área' }}</span></div>
                            </div>
                        </div>

                        <!-- Sección: Datos Laborales -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-3 col-span-1 md:col-span-2">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-briefcase text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Datos Laborales</h5>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                @if($tipo_personal === 'salud')
                                    @php
                                        $esp_nombre = collect($especialidades_list)->firstWhere('cod_esp', $cod_esp)?->nombre ?? 'No asignada';
                                    @endphp
                                    <div class="flex justify-between border-b border-borde/40 pb-1"><span class="text-apoyo">Especialidad:</span> <span class="font-semibold text-titulo">{{ $esp_nombre }}</span></div>
                                    <div class="flex justify-between border-b border-borde/40 pb-1"><span class="text-apoyo">Años Experiencia:</span> <span class="font-semibold text-titulo">{{ $anios_exp }} años</span></div>
                                    <div class="flex justify-between border-b border-borde/40 pb-1"><span class="text-apoyo">Matrícula Prof.:</span> <span class="font-semibold text-titulo uppercase">{{ $matricula_prof ?: 'No declarada' }}</span></div>
                                    <div class="flex justify-between border-b border-borde/40 pb-1"><span class="text-apoyo">Universidad / Formación:</span> <span class="font-semibold text-titulo uppercase">{{ $institucion_formacion ?: 'No declarada' }}</span></div>
                                    @if(($clasificacionResumen['rol_operativo'] ?? '') === 'ENFERMERO')
                                        <div class="flex justify-between col-span-1 sm:col-span-2 pt-1"><span class="text-apoyo font-bold">Subtipo de Enfermería:</span> <span class="font-bold text-boton-acento">{{ $subtipo_enfermeria === 'GENERAL_ADMISION' ? 'Enfermero General de Admisión' : 'Enfermero Especializado de Turno' }}</span></div>
                                    @endif
                                @elseif($tipo_personal === 'admin')
                                    @php
                                        $cargo_nombre = collect($cargos_list)->firstWhere('cod_cargo_admin', $cod_cargo_admin)?->nombre ?? 'No asignado';
                                    @endphp
                                    <div class="flex justify-between col-span-1 sm:col-span-2"><span class="text-apoyo">Cargo Administrativo:</span> <span class="font-semibold text-titulo">{{ $cargo_nombre }}</span></div>
                                @else
                                    <div class="text-apoyo italic col-span-1 sm:col-span-2">Sin información laboral requerida para este rol.</div>
                                @endif
                            </div>
                        </div>

                        <!-- Sección: Documentación del Ingresante -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-3 col-span-1 md:col-span-2">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-folder-open text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Documentación del Ingresante</h5>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @php
                                    $docsIngresante = array_filter($this->documentos_configurados, function($d) {
                                        return $d['tipo'] === 'ingresante';
                                    });
                                @endphp
                                @foreach($docsIngresante as $doc)
                                    @php
                                        $uploaded = isset($archivos_temporales[$doc['id']]);
                                        $isRequired = $doc['obligatorio'];
                                    @endphp
                                    <div class="flex items-center justify-between p-3 border rounded-xl bg-fondo/20 border-borde/50">
                                        <div class="flex flex-col text-left">
                                            <span class="text-xs font-bold text-titulo">{{ $doc['nombre'] }}</span>
                                            <span class="text-[9px] text-apoyo">
                                                @if($isRequired)
                                                    <span class="text-estado-peligro font-bold">Obligatorio</span>
                                                @else
                                                     Recomendado (Plazo 48h)
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            @if($uploaded)
                                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-estado-exitoBg text-estado-exito border border-estado-exito/20 uppercase tracking-wider">Cargado</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente (48h)</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sección: Documentación Institucional -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-3 col-span-1 md:col-span-2">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-file-pdf text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Documentación Institucional Firmada</h5>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @php
                                    $docsInstResumen = [
                                        ['id' => 'CONTRATO', 'nombre' => 'Contrato o Prestación de Servicios', 'obligatorio' => true],
                                        ['id' => 'CONFIDENCIALIDAD', 'nombre' => 'Compromiso de Confidencialidad', 'obligatorio' => true],
                                        ['id' => 'REGLAMENTO', 'nombre' => 'Aceptación de Reglamento Interno', 'obligatorio' => false],
                                        ['id' => 'FUNCIONES', 'nombre' => 'Formulario de Asignación de Funciones', 'obligatorio' => false],
                                    ];
                                @endphp
                                @foreach($docsInstResumen as $doc)
                                    @php
                                        $uploaded = isset($archivos_temporales[$doc['id']]);
                                        $isRequired = $doc['obligatorio'];
                                    @endphp
                                    <div class="flex items-center justify-between p-3 border rounded-xl bg-fondo/20 border-borde/50">
                                        <div class="flex flex-col text-left">
                                            <span class="text-xs font-bold text-titulo">{{ $doc['nombre'] }}</span>
                                            <span class="text-[9px] text-apoyo">
                                                @if($isRequired)
                                                    <span class="text-estado-peligro font-bold">Obligatorio</span>
                                                @else
                                                     Asignado (Plazo 48h)
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            @if($uploaded)
                                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-estado-exitoBg text-estado-exito border border-estado-exito/20 uppercase tracking-wider">Firmado</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente (48h)</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Bloque Documentación Institucional Autogenerada -->
                    <div class="space-y-4 bg-fondo/35 p-5 rounded-[1.5rem] border border-borde/60 text-left">
                        <div class="flex items-center gap-2 pb-2 border-b border-borde">
                            <i class="ph-bold ph-file-pdf text-lg text-boton-acento"></i>
                            <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Documentación Institucional Autogenerada</h5>
                        </div>
                        <p class="text-xs text-apoyo">Descargue los documentos generados con los datos del ingresante, imprímalos para la firma física y luego suba una copia escaneada o fotografía del documento firmado.</p>
                        
                        <div class="space-y-3">
                            @php
                                $docsInst = [
                                    ['id' => 'CONTRATO', 'nombre' => 'Contrato o Acuerdo de Prestación de Servicios', 'desc' => 'Contrato formal generado con datos de clasificación y remuneración.'],
                                    ['id' => 'CONFIDENCIALIDAD', 'nombre' => 'Compromiso de Confidencialidad y Protección de Datos', 'desc' => 'Acuerdo formal de resguardo de información institucional y de los adultos mayores.'],
                                    ['id' => 'REGLAMENTO', 'nombre' => 'Aceptación de Reglamento Interno y Acta de Recepción', 'desc' => 'Aceptación expresa de la normativa y reglamentos institucionales.'],
                                    ['id' => 'FUNCIONES', 'nombre' => 'Formulario de Asignación Inicial de Funciones', 'desc' => 'Declaración firmada de las responsabilidades asignadas al puesto.']
                                ];
                            @endphp

                            @foreach($docsInst as $doc)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl bg-white shadow-sm gap-4 transition-all hover:border-boton-acento/40 border-borde">
                                    <div class="flex items-start gap-3 flex-1">
                                        <div class="w-10 h-10 rounded-full {{ isset($archivos_temporales[$doc['id']]) ? 'bg-estado-exitoBg text-estado-exito' : 'bg-fondo text-apoyo' }} flex items-center justify-center shrink-0 border border-borde/40">
                                            <i class="ph-fill {{ isset($archivos_temporales[$doc['id']]) ? 'ph-check-circle' : 'ph-file-pdf' }} text-xl"></i>
                                        </div>
                                        <div class="space-y-1">
                                            <h6 class="text-sm font-bold text-titulo flex items-center flex-wrap gap-2">
                                                {{ $doc['nombre'] }}
                                                @if(isset($archivos_temporales[$doc['id']]))
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-estado-exitoBg text-estado-exito border border-estado-exito/20 uppercase tracking-wider">Cargado Firmado</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente de Firma</span>
                                                @endif
                                            </h6>
                                            <p class="text-[10px] text-apoyo">{{ $doc['desc'] }}</p>
                                            @error("archivos_temporales." . $doc['id'])
                                                <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <!-- Botón de descarga/visualización -->
                                        <a href="{{ route('admin.personal-institucional.generar-pdf', ['docId' => $doc['id']]) }}" target="_blank" class="px-3 py-1.5 text-xs font-bold text-boton-acento border border-boton-acento rounded-lg hover:bg-boton-acento/10 transition-colors flex items-center gap-1">
                                            <i class="ph-bold ph-printer"></i> Ver / Imprimir
                                        </a>

                                        @if(isset($archivos_temporales[$doc['id']]))
                                            <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-estado-peligro border border-estado-peligro rounded-lg hover:bg-estado-peligroBg transition-colors" title="Eliminar archivo">
                                                <i class="ph-bold ph-trash"></i> Eliminar
                                            </button>
                                        @else
                                            <label class="cursor-pointer px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors">
                                                <i class="ph-bold ph-upload-simple"></i> Subir Firmado
                                                <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Observaciones Iniciales (Opcional)</label>
                        <textarea wire:model="observaciones" rows="2" x-init="$nextTick(() => $el.focus())" class="w-full rounded-xl border-input-borde bg-input-bg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus" placeholder="Ej: Ingreso por periodo de prueba..."></textarea>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between pt-6 mt-6 border-t border-borde">
            @if($pasoActual > 1)
                <button type="button" wire:click="retrocederPaso" wire:loading.attr="disabled" class="px-5 py-2.5 text-sm font-bold border border-borde rounded-xl text-titulo hover:bg-fondo-hover transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="retrocederPaso">Anterior</span>
                    <span wire:loading wire:target="retrocederPaso"><i class="ph-bold ph-spinner animate-spin"></i> Cargando...</span>
                </button>
            @else
                <button type="button" wire:click="$dispatch('cerrarModalGestion')" class="px-5 py-2.5 text-sm font-bold text-apoyo hover:text-titulo transition-colors">
                    Cancelar
                </button>
            @endif

            @if($pasoActual < $totalPasos)
                <button type="button" wire:click="avanzarPaso" wire:loading.attr="disabled" class="rm-btn-primary px-6 rounded-xl h-10 flex items-center gap-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="avanzarPaso">Continuar <i class="ph-bold ph-arrow-right"></i></span>
                    <span wire:loading wire:target="avanzarPaso"><i class="ph-bold ph-spinner animate-spin"></i> Procesando...</span>
                </button>
            @else
                <button type="button" wire:click="preGuardar" wire:loading.attr="disabled" class="bg-[#3F7D5A] hover:bg-[#326649] text-white px-6 rounded-xl h-10 flex items-center gap-2 font-bold transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="preGuardar"><i class="ph-bold ph-check-circle text-lg"></i> Registrar personal</span>
                    <span wire:loading wire:target="preGuardar"><i class="ph-bold ph-spinner animate-spin text-lg"></i> Finalizando...</span>
                </button>
            @endif
        </div>
    </form>
</div>

@else
<form wire:submit.prevent="guardar" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Columna Izquierda: Datos de Usuario -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                <i class="ph-bold ph-user"></i> Datos Personales
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Nombres <span class="text-estado-peligro">*</span></label>
                    <input type="text" wire:model="nombres" class="w-full rounded-xl border-input-borde bg-input-bg text-sm uppercase focus:ring-input-ringFocus focus:border-input-bordeFocus" required>
                    @error('nombres') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Ap. Paterno <span class="text-estado-peligro">*</span></label>
                    <input type="text" wire:model="ap_paterno" class="w-full rounded-xl border-input-borde bg-input-bg text-sm uppercase focus:ring-input-ringFocus focus:border-input-bordeFocus" required>
                    @error('ap_paterno') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Ap. Materno</label>
                    <input type="text" wire:model="ap_materno" class="w-full rounded-xl border-input-borde bg-input-bg text-sm uppercase focus:ring-input-ringFocus focus:border-input-bordeFocus">
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Documento <span class="text-estado-peligro">*</span></label>
                    <div class="flex gap-2">
                        <select wire:model="tipo_documento" class="w-1/3 rounded-xl border-input-borde bg-input-bg text-sm">
                            <option value="CI">CI</option>
                            <option value="PASAPORTE">PAS</option>
                        </select>
                        <input type="text" wire:model="numero_documento" class="w-2/3 rounded-xl border-input-borde bg-input-bg text-sm" required>
                    </div>
                    @error('numero_documento') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Correo Electrónico <span class="text-estado-peligro">*</span></label>
                    <input type="email" wire:model="correo" class="w-full rounded-xl border-input-borde bg-input-bg text-sm lowercase" required>
                    @error('correo') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Teléfono</label>
                    <input type="text" wire:model="telefono" class="w-full rounded-xl border-input-borde bg-input-bg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Estado</label>
                    <select wire:model="estado" class="w-full rounded-xl border-input-borde bg-input-bg text-sm">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="SUSPENDIDO">SUSPENDIDO</option>
                        <option value="RETIRADO">RETIRADO</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Roles y Permisos</label>
                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border border-input-borde rounded-xl bg-input-bg">
                    @foreach($roles as $rol)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="roles_seleccionados" value="{{ $rol->name }}" class="rounded text-boton-acento focus:ring-boton-acento">
                            <span class="text-sm font-semibold text-parrafo">{{ $rol->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Clasificación de Personal -->
        <div class="space-y-4">
            @php($clasificacion = $clasificacion_derivada)

            <h3 class="text-lg font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                <i class="ph-bold ph-briefcase"></i> Clasificación Institucional
            </h3>

            @if($clasificacion)
                <div class="p-4 {{ $clasificacion['tipo_personal'] === 'salud' ? 'bg-estado-infoBg border-estado-infoBorde' : 'bg-estado-advertenciaBg border-estado-advertenciaBorde' }} border rounded-xl space-y-4">
                    <div>
                        <h5 class="text-xs font-bold uppercase tracking-wider {{ $clasificacion['tipo_personal'] === 'salud' ? 'text-estado-info' : 'text-estado-advertencia' }}">Derivada del rol</h5>
                        <p class="text-sm font-bold text-titulo">{{ $clasificacion['rol_sistema'] }}</p>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-xl border border-borde bg-white p-3">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Tipo</span>
                            <span class="text-sm font-black text-titulo">{{ $clasificacion['tipo_label'] }}</span>
                        </div>
                        <div class="rounded-xl border border-borde bg-white p-3">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Rol operativo</span>
                            <span class="text-sm font-black text-titulo">{{ $clasificacion['rol_label'] }}</span>
                        </div>
                        <div class="rounded-xl border border-borde bg-white p-3">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Área</span>
                            <span class="text-sm font-black text-titulo">{{ $clasificacion['area_nombre'] }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 bg-estado-peligroBg border border-estado-peligro/30 rounded-xl text-estado-peligro text-sm font-bold">
                    <i class="ph-bold ph-warning-circle"></i> Seleccione un rol institucional válido.
                </div>
            @endif
            @error('roles_seleccionados') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Observaciones / Notas</label>
        <textarea wire:model="observaciones" rows="3" class="w-full rounded-xl border-input-borde bg-input-bg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus"></textarea>
    </div>

    <div class="flex items-center justify-end gap-3 pt-6 border-t border-borde">
        <button type="button" wire:click="$dispatch('cerrarModalGestion')" class="px-5 py-2.5 text-sm font-bold text-apoyo hover:text-titulo transition-colors">
            Cancelar
        </button>
        <button type="button" wire:click="confirmarActualizacion" class="rm-btn-primary px-6 flex items-center gap-2 h-10 rounded-xl">
            <i class="ph-bold ph-floppy-disk text-lg"></i>
            <span>Actualizar Personal</span>
        </button>
    </div>
</form>
@endif
</div>

@script
<script>
    $wire.on('confirmarRegistroFinal', (e) => {
        const data = e[0];
        let html = '';
        if (data.faltan_documentos) {
            html = `<div class="p-3 bg-estado-peligroBg text-estado-peligro text-sm rounded-xl mb-4 border border-estado-peligro/30 text-left"><i class="ph-bold ph-warning"></i> <b>Advertencia:</b> Faltan documentos obligatorios. No se permite finalizar como ACTIVO. El registro se completará pero el acceso quedará en estado <b>DOCUMENTACION_PENDIENTE</b>.</div>`;
        }
        html += '<p class="text-sm text-apoyo">¿Desea confirmar y procesar el alta final en el sistema?</p>';

        Swal.fire({
            title: 'Confirmar Registro Final',
            html: html,
            icon: data.faltan_documentos ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#3F7D5A',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="ph-bold ph-check"></i> Sí, registrar personal',
            cancelButtonText: 'Revisar datos'
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.guardar();
            }
        });
    });

    $wire.on('credencialesGeneradas', (e) => {
        const data = e[0];
        Swal.fire({
            title: '¡Registro Exitoso!',
            html: data.html + "<div class='mt-4 p-3 bg-estado-advertenciaBg text-estado-advertencia text-xs rounded-lg border border-estado-advertenciaBorde'><i class='ph-bold ph-warning-circle'></i> <b>IMPORTANTE:</b> Copie esta contraseña temporal. Por seguridad, no volverá a mostrarse en el sistema.</div>",
            icon: 'success',
            confirmButtonText: '<i class="ph-bold ph-check"></i> Entendido, credenciales copiadas',
            confirmButtonColor: '#3F7D5A',
            allowOutsideClick: false,
            allowEscapeKey: false,
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.dispatch('actualizarTablaPersonal');
                $wire.dispatch('cerrarModalGestion');
            }
        });
    });

    $wire.on('confirmarCambioRoles', (e) => {
        Swal.fire({
            title: '¿Cambiar Rol del Sistema?',
            html: `Está a punto de modificar los permisos de acceso de este usuario.<br><br>Nuevos roles: <b>${e[0].roles}</b><br><br>¿Desea continuar y actualizar el personal?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3F7D5A',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar roles y actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.call('guardar');
            }
        });
    });
</script>
@endscript
