<div x-data="{ isDirty: false }" x-on:input="isDirty = true" x-on:change="isDirty = true" x-on:abrir-pdf-generado.window="try{ let u = $event.detail.url || ($event.detail[0] && $event.detail[0].url); if(u) window.open(u, '_blank'); }catch(e){console.error(e)}" class="max-w-5xl mx-auto w-full max-h-[85vh] flex flex-col">
@if(!$esEdicion)
<div class="p-3 md:p-4 bg-white flex flex-col h-full rounded-xl shadow-xl overflow-hidden">
    <!-- Header Compacto -->
    <div class="flex items-start justify-between mb-3 border-b border-borde/50 pb-2">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-inner border border-boton-acento/20">
                <i class="ph-fill ph-user-plus text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-titulo leading-tight">Registrar Personal Institucional</h3>
                <p class="text-xs font-semibold text-apoyo mt-0.5">Complete los pasos para dar de alta un nuevo trabajador</p>
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
        " class="w-6 h-6 flex items-center justify-center rounded-full bg-fondo text-apoyo hover:bg-estado-peligroBg hover:text-estado-peligro transition-colors">
            <i class="ph-bold ph-x text-lg"></i>
        </button>
    </div>

    @php
        $pasoActualsLista = [
            1 => 'Acceso',
            2 => 'Datos',
            3 => 'Dirección',
            4 => 'Rol',
            5 => 'Docs',
            6 => 'Inst.',
            7 => 'Resumen'
        ];

        $pasosErrores = [
            1 => $errors->has('correo') || $errors->has('estado') || $errors->has('fecha_registro') || $errors->has('hora_registro'),
            2 => $errors->has('nombres') || $errors->has('ap_paterno') || $errors->has('ap_materno') || $errors->has('numero_documento') || $errors->has('expedido') || $errors->has('genero') || $errors->has('fecha_nacimiento') || $errors->has('foto_perfil'),
            3 => $errors->has('telefono') || $errors->has('telefono_alternativo') || $errors->has('ciudad_id') || $errors->has('municipio_id') || $errors->has('zona_id') || $errors->has('otra_zona') || $errors->has('calle_id') || $errors->has('otra_calle') || $errors->has('nro_casa'),
            4 => $errors->has('roles_seleccionados') || $errors->has('cod_esp') || $errors->has('anios_exp') || $errors->has('matricula_prof') || $errors->has('cod_cargo_admin') || $errors->has('nivel_responsabilidad'),
            5 => collect($errors->keys())->contains(fn($k) => str_starts_with($k, 'archivos_temporales')),
            6 => $errors->has('documentos_generales'),
            7 => false
        ];
        $progresoPorcentaje = round((($pasoActual - 1) / 6) * 100);
    @endphp

    <!-- Stepper Compacto -->
    <div class="mb-3">
        <div class="relative flex items-center justify-between w-full pb-2">
            <!-- Línea de fondo -->
            <div class="absolute left-4 right-4 top-3 transform -translate-y-1/2 h-[3px] bg-borde/40 rounded-full z-0"></div>
            <!-- Línea de progreso -->
            <div class="absolute left-4 top-3 transform -translate-y-1/2 h-[3px] bg-boton-acento rounded-full z-0 transition-all duration-500 ease-out shadow-[0_0_8px_rgba(63,125,90,0.4)]" style="width: calc({{ (($pasoActual - 1) / 6) * 100 }}% - 2rem)"></div>
            
            @foreach($pasoActualsLista as $num => $nombre)
                <div class="relative z-10 flex flex-col items-center group cursor-pointer" wire:click="gotoStep({{ $num }})" @if($num > $pasoActual && !($pasoActual > $num)) disabled @endif>
                    @php
                        $hasError = $pasosErrores[$num] ?? false;
                        $isCompleted = $pasoActual > $num;
                        $isActive = $pasoActual == $num;
                    @endphp
                    
                    <button type="button" @if($num > $pasoActual && !$isCompleted) disabled @endif class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-[9px] transition-all duration-300 {{ 
                        $hasError 
                            ? 'bg-estado-peligroBg text-estado-peligro border-2 border-estado-peligro' 
                            : ($isActive 
                                ? 'bg-boton-acento text-white ring-4 ring-boton-acento/20 scale-105 shadow-md' 
                                : ($isCompleted 
                                    ? 'bg-boton-acento text-white hover:bg-boton-acentoHover' 
                                    : 'bg-white text-apoyo/40 border-2 border-borde/60 hover:border-apoyo/30'))
                    }}">
                        @if($hasError)
                            <i class="ph-bold ph-warning text-sm"></i>
                        @elseif($isCompleted)
                            <i class="ph-bold ph-check text-sm"></i>
                        @else
                            {{ $num }}
                        @endif
                    </button>
                    
                    <span class="absolute top-7 text-[9px] font-bold uppercase tracking-wider whitespace-nowrap transition-colors duration-300 {{ 
                        $hasError 
                            ? 'text-estado-peligro' 
                            : ($isActive 
                                ? 'text-boton-acento font-black scale-105 origin-top' 
                                : ($isCompleted 
                                    ? 'text-titulo/70' 
                                    : 'text-apoyo/40')) 
                    }} hidden sm:block">{{ $nombre }}</span>
                </div>
            @endforeach
        </div>
        <div class="text-center sm:hidden mt-2">
            <span class="text-[10px] font-black text-boton-acento uppercase tracking-wider bg-boton-acento/10 px-3 py-1.5 rounded-full">Paso {{ $pasoActual }}: {{ $pasoActualsLista[$pasoActual] }}</span>
        </div>
    </div>

    <!-- Wizard Form Body -->
    <form wire:submit.prevent="guardar" class="flex flex-col flex-1 min-h-0 overflow-hidden" x-on:keydown.enter="
        if ($event.target.tagName === 'INPUT' && ['text', 'email', 'date', 'number', 'tel'].includes($event.target.type)) {
            $event.preventDefault();
            $wire.avanzarPaso();
        }
    ">
        <div class="flex-1 overflow-y-auto overflow-x-hidden pr-2 space-y-2 pb-2 custom-scrollbar">
            @if($pasoActual == 1)
                <!-- Paso 1: Usuario de acceso -->
                <div class="space-y-2 animate-fade-in">
                    <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-key text-boton-acento"></i> 1. Acceso al sistema
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Correo Institucional o Personal <span class="text-estado-peligro">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="ph-bold ph-envelope-simple text-apoyo text-lg"></i>
                                    </div>
                                    <input type="email" wire:model.live="correo" x-on:input="$el.value = $el.value.toLowerCase().replace(/\s/g, '')" x-init="$nextTick(() => $el.focus())" class="pl-10 w-full rounded-xl {{ $errors->has('correo') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5" placeholder="ejemplo@institucion.com">
                                </div>
                                @error('correo') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Fecha de Registro</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="ph-bold ph-calendar text-apoyo"></i>
                                        </div>
                                        <input type="text" value="{{ \Carbon\Carbon::parse($fecha_registro)->format('d/m/Y') }}" class="pl-10 w-full rounded-xl {{ $errors->has('fecha_registro') ? 'border-estado-peligro' : 'border-input-borde' }} bg-fondo-hover text-sm font-bold py-1.5 text-titulo opacity-80 cursor-not-allowed" readonly>
                                    </div>
                                    @error('fecha_registro') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Hora</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="ph-bold ph-clock text-apoyo"></i>
                                        </div>
                                        <input type="text" value="{{ \Carbon\Carbon::parse($hora_registro)->format('H:i') }}" class="pl-10 w-full rounded-xl {{ $errors->has('hora_registro') ? 'border-estado-peligro' : 'border-input-borde' }} bg-fondo-hover text-sm font-bold py-1.5 text-titulo opacity-80 cursor-not-allowed" readonly>
                                    </div>
                                    @error('hora_registro') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Estado de Acceso</label>
                                <div class="flex items-center gap-2 p-2.5 bg-estado-exitoBg border border-estado-exito/30 rounded-xl">
                                    <i class="ph-fill ph-check-circle text-estado-exito text-xl"></i>
                                    <span class="text-sm font-bold text-estado-exito tracking-wider">ACTIVO</span>
                                    <input type="hidden" wire:model="estado">
                                </div>
                                @error('estado') <span class="text-xs text-estado-peligro font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <!-- Contraseña temporal autogenerada -->
                                <div x-data="{ visible: false }">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Contraseña Temporal Autogenerada</label>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 relative">
                                            <input :type="visible ? 'text' : 'password'" wire:model="contrasena_temporal" class="w-full rounded-xl border-input-borde bg-input-bg text-xs py-1.5 font-mono pr-10 cursor-not-allowed opacity-80" readonly>
                                            <button type="button" @click="visible = !visible" class="absolute right-2 top-1/2 -translate-y-1/2 text-apoyo hover:text-titulo">
                                                <i class="ph-bold" :class="visible ? 'ph-eye-slash' : 'ph-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-apoyo mt-1">Se enviará al correo del trabajador. No se volverá a mostrar tras confirmar.</p>
                                </div>

                                <!-- Opciones de acceso -->
                                <div class="space-y-2 p-3 bg-fondo/60 border border-borde/60 rounded-xl">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="mostrar_credenciales" class="rounded text-boton-acento focus:ring-boton-acento h-4 w-4">
                                        <span class="text-xs font-bold text-titulo">Mostrar credenciales al finalizar el registro</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="forzar_cambio_password" class="rounded text-boton-acento focus:ring-boton-acento h-4 w-4">
                                        <span class="text-xs font-bold text-titulo">Forzar cambio de contraseña al primer inicio de sesión</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($pasoActual == 2)
                <!-- Paso 2: Identificación Personal -->
                <div class="space-y-2">
                    <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-identification-card text-boton-acento"></i> 2. Datos Personales
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <!-- Columna Foto -->
                        <div class="md:col-span-1 flex flex-col items-center justify-center rounded-lg p-4 bg-fondo/30 border border-borde text-center">
                            <div class="relative group cursor-pointer mb-5">
                                @if($foto_perfil && !is_string($foto_perfil))
                                    <img src="{{ $foto_perfil->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-full shadow-lg border-4 border-white ring-4 ring-boton-acento/10 transition-transform group-hover:scale-105">
                                @elseif($foto_perfil && is_string($foto_perfil))
                                    <img src="{{ asset('storage/' . $foto_perfil) }}" class="w-24 h-24 object-cover rounded-full shadow-lg border-4 border-white ring-4 ring-boton-acento/10 transition-transform group-hover:scale-105">
                                @else
                                    <div class="w-24 h-24 bg-fondo-hover rounded-full flex items-center justify-center text-apoyo/50 border-4 border-white shadow-md ring-4 ring-borde/50 transition-all group-hover:ring-boton-acento/30">
                                        <i class="ph-fill ph-user text-[4rem]"></i>
                                    </div>
                                @endif
                                
                                <label class="absolute bottom-0 right-0 w-14 h-14 bg-boton-acento text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:bg-boton-acentoHover transition-colors border-2 border-white">
                                    <span wire:loading.remove wire:target="foto_perfil"><i class="ph-bold ph-camera text-lg"></i></span>
                                    <span wire:loading wire:target="foto_perfil"><i class="ph-bold ph-spinner animate-spin text-lg"></i></span>
                                    <input type="file" wire:model="foto_perfil" class="hidden" accept=".jpg,.jpeg,.png,.webp">
                                </label>
                            </div>
                            
                            <h6 class="text-sm font-bold text-titulo mb-0.5">Fotografía de Perfil</h6>
                            <p class="text-[10px] text-apoyo bg-white px-3 py-1.5 rounded-full border border-borde/50 shadow-sm inline-block">JPG, PNG o WEBP <span class="font-bold text-estado-peligro/70">(Máx. 2MB)</span></p>
                            @error('foto_perfil') <span class="text-xs text-estado-peligro mt-2 block font-bold bg-estado-peligroBg px-2 py-1.5 rounded">{{ $message }}</span> @enderror
                        </div>

                        <!-- Columna Datos Personales -->
                        <div class="md:col-span-2 space-y-2">
                            <h5 class="text-xs font-bold text-apoyo uppercase tracking-wider border-b border-borde pb-1">Datos de Identidad</h5>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Nombres <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.blur="nombres" x-init="$nextTick(() => $el.focus())" class="w-full rounded-xl {{ $errors->has('nombres') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5 uppercase">
                                    @error('nombres') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Ap. Paterno</label>
                                    <input type="text" wire:model.blur="ap_paterno" class="w-full rounded-xl {{ $errors->has('ap_paterno') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5 uppercase">
                                    @error('ap_paterno') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Ap. Materno</label>
                                    <input type="text" wire:model.blur="ap_materno" class="w-full rounded-xl {{ $errors->has('ap_materno') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5 uppercase">
                                    @error('ap_materno') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Género <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="genero" class="w-full rounded-xl {{ $errors->has('genero') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5">
                                        <option value="">Seleccione...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                    @error('genero') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-2">
                                <div class="col-span-3">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Nro. Documento / CI <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.blur="numero_documento" class="w-full rounded-xl {{ $errors->has('numero_documento') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5" placeholder="Ej: 1234567">
                                    @error('numero_documento') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Exp. <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="expedido" class="w-full rounded-xl {{ $errors->has('expedido') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5">
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

                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Fecha Nacimiento <span class="text-estado-peligro">*</span></label>
                                    <input type="date" wire:model.live="fecha_nacimiento" class="w-full rounded-xl {{ $errors->has('fecha_nacimiento') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5">
                                    @error('fecha_nacimiento') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Edad (Años)</label>
                                    <input type="text" wire:model="edad" class="w-full rounded-xl border-input-borde bg-fondo-hover text-sm font-bold py-1.5 text-titulo opacity-80 cursor-not-allowed text-center" readonly placeholder="-">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            @elseif($pasoActual == 3)
                <!-- Paso 3: Dirección -->
                <div class="space-y-2">
                    <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-phone text-boton-acento"></i> 3. Dirección y Contacto
                    </h4>
                    
                    <div class="space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Celular <span class="text-estado-peligro">*</span></label>
                                <input type="text" wire:model="telefono" x-init="$nextTick(() => $el.focus())" class="w-full rounded-xl {{ $errors->has('telefono') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5" placeholder="Ej: 77712345">
                                @error('telefono') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Teléfono Alternativo</label>
                                <input type="text" wire:model="telefono_alternativo" class="w-full rounded-xl {{ $errors->has('telefono_alternativo') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5">
                                @error('telefono_alternativo') <span class="text-xs text-estado-peligro block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Correo Registrado</label>
                                <input type="email" wire:model="correo" class="w-full rounded-xl border-input-borde bg-fondo-hover text-sm text-apoyo cursor-not-allowed" disabled readonly>
                            </div>
                        </div>

                        <!-- Dirección Jerárquica -->
                        <div class="bg-fondo/30 p-5 rounded-lg border border-borde/60 space-y-2">
                            <h5 class="text-xs font-bold text-apoyo uppercase tracking-wider border-b border-borde pb-1 flex items-center gap-1.5"><i class="ph-bold ph-map-pin text-boton-acento"></i> Dirección de Residencia</h5>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <!-- Departamento / Ciudad -->
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Departamento / Ciudad <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="ciudad_id" class="w-full rounded-xl {{ $errors->has('ciudad_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5">
                                        <option value="">Seleccione Departamento...</option>
                                        @foreach($departamentos_list as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('ciudad_id') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Municipio -->
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Municipio <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="municipio_id" class="w-full rounded-xl {{ $errors->has('municipio_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5" {{ empty($ciudad_id) ? 'disabled' : '' }}>
                                        <option value="">Seleccione Municipio...</option>
                                        @foreach($municipios_list as $mun)
                                            <option value="{{ $mun->id }}">{{ $mun->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('municipio_id') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <!-- Zona / Barrio -->
                                <div>
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Zona / Barrio <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="zona_id" class="w-full rounded-xl {{ $errors->has('zona_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5" {{ empty($municipio_id) ? 'disabled' : '' }}>
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
                                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Avenida / Calle <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="calle_id" class="w-full rounded-xl {{ $errors->has('calle_id') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5" {{ empty($zona_id) ? 'disabled' : '' }}>
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
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-yellow-50/50 p-4 rounded-xl border border-yellow-200">
                                    @if($zona_id === 'OTRA')
                                        <div>
                                            <label class="block text-xs font-bold text-estado-advertencia uppercase tracking-wider mb-0.5">Especificar Otra Zona <span class="text-estado-peligro">*</span></label>
                                            <input type="text" wire:model.blur="otra_zona" class="w-full rounded-xl border-yellow-300 bg-white text-sm uppercase focus:ring-yellow-400 focus:border-yellow-400" placeholder="Ej: VILLA ARMONÍA">
                                            @error('otra_zona') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    @if($calle_id === 'OTRA')
                                        <div>
                                            <label class="block text-xs font-bold text-estado-advertencia uppercase tracking-wider mb-0.5">Especificar Otra Calle/Avenida <span class="text-estado-peligro">*</span></label>
                                            <input type="text" wire:model.blur="otra_calle" class="w-full rounded-xl border-yellow-300 bg-white text-sm uppercase focus:ring-yellow-400 focus:border-yellow-400" placeholder="Ej: CALLE 15 DE SEPTIEMBRE">
                                            @error('otra_calle') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Dirección Complementaria (Edificio, Nro, etc) <span class="text-estado-peligro">*</span></label>
                                <input type="text" wire:model.blur="nro_casa" class="w-full rounded-xl {{ $errors->has('nro_casa') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-xs py-1.5 uppercase" placeholder="Ej: EDIFICIO TORRE AZUL, PISO 3, DEPTO 302">
                                @error('nro_casa') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($pasoActual == 4)
                <!-- Paso 4: Rol y Perfil Operativo -->
                <div class="space-y-2 animate-fade-in">
                    <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                        <i class="ph-bold ph-shield text-boton-acento"></i> 4. Rol y Perfil Operativo
                    </h4>

                    <!-- Selección de Rol -->
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($roles as $rol)
                            <label class="flex items-center gap-2 p-2.5 border rounded-xl cursor-pointer hover:bg-fondo-hover transition-colors {{ $rol->name === $rol_seleccionado ? 'border-boton-acento bg-boton-acento/5 shadow-sm' : 'border-borde bg-white' }}">
                                <input type="radio" wire:model.live="rol_seleccionado" name="rol_institucional" value="{{ $rol->name }}" class="rounded-full text-boton-acento focus:ring-boton-acento h-4 w-4 shrink-0">
                                <span class="text-sm font-bold text-titulo leading-tight">{{ $rol->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles_seleccionados') <p class="text-xs text-estado-peligro font-bold">{{ $message }}</p> @enderror

                    @if(!empty($rol_seleccionado))
                        @php
                            $clsf = $clasificacion_derivada;
                        @endphp

                        @if($clsf)
                            <!-- Clasificación derivada -->
                            <div class="p-4 {{ $clsf['tipo_personal'] === 'salud' ? 'bg-estado-infoBg border-estado-infoBorde' : 'bg-estado-advertenciaBg border-estado-advertenciaBorde' }} border rounded-xl space-y-2 animate-fade-in">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <h5 class="text-[10px] font-bold uppercase tracking-wider {{ $clsf['tipo_personal'] === 'salud' ? 'text-estado-info' : 'text-estado-advertencia' }}">Clasificación Derivada Automáticamente</h5>
                                        <p class="text-sm font-bold text-titulo">{{ $clsf['tipo_label'] }} — {{ $clsf['rol_label'] }}</p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1.5 text-[9px] font-black text-titulo border border-borde shadow-sm uppercase tracking-wider">
                                        <i class="ph-bold ph-check-circle text-boton-acento"></i> Auto
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <div class="rounded-xl border border-borde bg-white p-2.5 shadow-sm">
                                        <span class="block text-[9px] font-bold uppercase tracking-wider text-apoyo mb-0.5">Tipo</span>
                                        <span class="text-xs font-black text-titulo">{{ $clsf['tipo_label'] }}</span>
                                    </div>
                                    <div class="rounded-xl border border-borde bg-white p-2.5 shadow-sm">
                                        <span class="block text-[9px] font-bold uppercase tracking-wider text-apoyo mb-0.5">Rol Operativo</span>
                                        <span class="text-xs font-black text-titulo">{{ $clsf['rol_label'] }}</span>
                                    </div>
                                    <div class="rounded-xl border border-borde bg-white p-2.5 shadow-sm col-span-2 sm:col-span-1">
                                        <span class="block text-[9px] font-bold uppercase tracking-wider text-apoyo mb-0.5">Área</span>
                                        <span class="text-xs font-black text-titulo">{{ $clsf['area_nombre'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Los campos del perfil operativo fueron trasladados a la matriz documental (Paso 5) -->
                        @else
                            <div class="p-4 bg-estado-advertenciaBg/30 border border-estado-advertenciaBorde rounded-xl text-sm font-bold text-estado-advertencia text-center">
                                <i class="ph-bold ph-warning-circle mr-1"></i> El rol seleccionado no tiene clasificación institucional asignada.
                            </div>
                        @endif
                    @endif
                </div>

            @elseif($pasoActual == 5)
                <!-- Paso 5: Documentos del Trabajador -->
                <div class="space-y-2 animate-fade-in">
                    <div class="flex justify-between items-center border-b border-borde pb-2">
                        <h4 class="text-md font-black text-titulo flex items-center gap-2">
                            <i class="ph-bold ph-folder-user text-boton-acento"></i> 5. Documentos del Trabajador
                        </h4>
                        <span class="px-2.5 py-1.5 rounded-full bg-estado-advertenciaBg text-estado-advertencia font-bold text-xs border border-estado-advertenciaBorde">
                            Obligatorios: presentar hoy
                        </span>
                    </div>

                    @php
                        $docsAgrupados = collect($this->documentos_configurados)->groupBy('tipo');
                    @endphp

                    @if(empty($rol_seleccionado))
                        <div class="p-4 bg-estado-advertenciaBg/20 border-2 border-dashed border-estado-advertencia rounded-2xl text-center space-y-2">
                            <div class="w-14 h-14 bg-estado-advertenciaBg text-estado-advertencia rounded-full flex items-center justify-center mx-auto shadow-sm">
                                <i class="ph-bold ph-folder-dashed text-xl"></i>
                            </div>
                            <div>
                                <h5 class="text-lg font-black text-titulo mb-0.5">Falta Matriz Documental</h5>
                                <p class="text-sm font-bold text-apoyo">Primero seleccione un rol en el Paso 4 para determinar qué documentos se requieren.</p>
                            </div>
                            <button type="button" wire:click="gotoStep(4)" class="px-5 py-2 bg-estado-advertencia text-white text-sm font-bold rounded-xl hover:bg-estado-advertencia/80 transition-colors shadow-md">
                                <i class="ph-bold ph-arrow-left mr-1"></i> Volver al Paso 4
                            </button>
                        </div>
                    @else
                        <!-- Documentación del Trabajador (Ingresante) -->
                            <div class="space-y-2 bg-fondo/30 p-3 md:p-4 rounded-lg border border-borde/60">
                                <div class="flex items-center gap-2 pb-3 border-b border-borde/50">
                                    <div class="w-7 h-7 rounded-full bg-boton-acento/10 text-boton-acento flex items-center justify-center">
                                        <i class="ph-bold ph-folder-user text-lg"></i>
                                    </div>
                                    <h5 class="text-sm font-bold text-titulo uppercase tracking-wider">Archivos Personales y Respaldos</h5>
                                </div>
                                <div class="space-y-2">
                                    @if(isset($docsAgrupados['ingresante']) && count($docsAgrupados['ingresante']) > 0)
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                        @foreach($docsAgrupados['ingresante'] as $doc)
                                            @php
                                                $docSubido = isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && in_array($estado_documentos[$doc['id']], ['CARGADO', 'VALIDADO']));
                                                $docObservado = isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO';
                                            @endphp
                                            <div wire:key="doc-per-{{ $doc['id'] }}" class="flex items-center justify-between p-2.5 border rounded-xl bg-white shadow-sm transition-all hover:border-boton-acento/40 {{ ($doc['obligatorio_inmediato'] && !$docSubido && (!isset($estado_documentos[$doc['id']]) || $estado_documentos[$doc['id']] === 'PENDIENTE')) ? 'border-estado-peligro/30 bg-estado-peligroBg/10' : 'border-borde' }}">
                                                <div class="flex items-center gap-2 overflow-hidden flex-1">
                                                    <div class="w-7 h-7 rounded-full {{ $docSubido ? 'bg-estado-exitoBg text-estado-exito' : ($docObservado ? 'bg-estado-peligroBg text-estado-peligro' : 'bg-fondo text-apoyo') }} flex items-center justify-center shrink-0 border border-borde/40">
                                                        <i class="ph-fill {{ $docSubido ? 'ph-check-circle' : ($docObservado ? 'ph-warning-circle' : 'ph-file-text') }} text-lg"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h6 class="text-[11px] font-bold text-titulo truncate flex items-center gap-1.5">
                                                            {{ $doc['nombre'] }}
                                                        </h6>
                                                        <div class="flex items-center gap-1.5 mt-0.5">
                                                            @if($doc['obligatorio_inmediato'])
                                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase tracking-wider">Obligatorio</span>
                                                            @elseif($doc['permite_plazo'])
                                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Plazo 48h</span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-fondo text-apoyo border border-borde uppercase tracking-wider">Opcional</span>
                                                            @endif
                                                            <span class="text-[9px] text-apoyo truncate hidden sm:inline">{{ $doc['desc'] }}</span>
                                                        </div>
                                                        @if($docObservado)
                                                            <p class="text-[9px] text-estado-peligro font-bold truncate mt-0.5">Obs: {{ $observacion_documentos[$doc['id']] ?? 'Documento incorrecto.' }}</p>
                                                        @endif
                                                        @error("archivos_temporales." . $doc['id'])
                                                            <p class="text-[9px] text-estado-peligro font-bold truncate mt-0.5">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1 shrink-0 ml-2">
                                                    @if($docSubido)
                                                        @php
                                                            $previewUrl = '#';
                                                            if (isset($archivos_temporales[$doc['id']])) {
                                                                $fileOrPath = $archivos_temporales[$doc['id']];
                                                                if (is_string($fileOrPath)) {
                                                                    $previewUrl = asset('storage/' . $fileOrPath);
                                                                } elseif (is_object($fileOrPath) && method_exists($fileOrPath, 'temporaryUrl')) {
                                                                    try {
                                                                        $previewUrl = $fileOrPath->temporaryUrl();
                                                                    } catch (\Exception $e) {
                                                                        $previewUrl = '#';
                                                                    }
                                                                }
                                                            } elseif (isset($documentos_cargados_rutas[$doc['id']])) {
                                                                $previewUrl = asset('storage/' . $documentos_cargados_rutas[$doc['id']]);
                                                            }
                                                        @endphp
                                                        @if($previewUrl !== '#')
                                                            <button type="button" @click="$dispatch('abrir-pdf-generado', { url: '{{ $previewUrl }}' })" class="p-1.5 text-boton-acento hover:bg-boton-acento/10 rounded transition-colors" title="Ver Archivo">
                                                                <i class="ph-bold ph-eye text-lg"></i>
                                                            </button>
                                                        @endif
                                                        <span class="px-2 py-1.5 text-[10px] font-bold text-estado-exito bg-estado-exitoBg rounded border border-estado-exito/20">Subido</span>
                                                        <button type="button" wire:click.prevent="removerDocumento('{{ $doc['id'] }}')" class="p-1.5 text-estado-peligro hover:bg-estado-peligro hover:text-white rounded transition-colors" title="Eliminar">
                                                            <i class="ph-bold ph-trash"></i>
                                                        </button>
                                                    @else
                                                        @if(!isset($estado_documentos[$doc['id']]) || $estado_documentos[$doc['id']] !== 'OBSERVADO')
                                                            <label class="cursor-pointer px-2.5 py-1.5 text-[10px] font-bold text-boton-acento border border-boton-acento rounded hover:bg-boton-acento hover:text-white transition-all">
                                                                <i class="ph-bold ph-upload-simple"></i> Subir
                                                                <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                            </label>
                                                        @else
                                                            <label class="cursor-pointer px-2.5 py-1.5 text-[10px] font-bold text-estado-peligro border border-estado-peligro rounded hover:bg-estado-peligro hover:text-white transition-all">
                                                                <i class="ph-bold ph-upload-simple"></i> Resubir
                                                                <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                            </label>
                                                            <button type="button" wire:click.prevent="removerDocumento('{{ $doc['id'] }}')" class="p-1.5 text-apoyo hover:bg-fondo-hover rounded transition-colors" title="Deshacer">
                                                                <i class="ph-bold ph-arrow-u-up-left"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 text-center text-sm font-bold text-apoyo border border-dashed border-borde rounded-xl">
                                        No hay documentos personales requeridos para este rol.
                                    </div>
                                @endif
                            </div>

                        @error('archivos_temporales')
                            <div class="p-3 bg-estado-peligroBg text-estado-peligro border border-estado-peligro/30 rounded-xl text-sm font-bold mt-2 flex items-center gap-2">
                                <i class="ph-bold ph-warning"></i> {{ $message }}
                            </div>
                        @enderror
                    @endif
                </div>

            @elseif($pasoActual == 6)
                <!-- Paso 6: Documentación Institucional -->
                <div class="space-y-2 animate-fade-in">
                    <div class="flex justify-between items-center border-b border-borde pb-2">
                        <h4 class="text-md font-black text-titulo flex items-center gap-2">
                            <i class="ph-bold ph-briefcase text-boton-acento"></i> 6. Documentación Institucional
                        </h4>
                        <span class="px-2.5 py-1.5 rounded-full bg-estado-advertenciaBg text-estado-advertencia font-bold text-xs border border-estado-advertenciaBorde">
                            Genere, imprima y suba cada documento firmado
                        </span>
                    </div>

                    @if(empty($rol_seleccionado))
                        <div class="p-4 bg-estado-advertenciaBg/20 border-2 border-dashed border-estado-advertencia rounded-2xl text-center space-y-2">
                            <div class="w-14 h-14 bg-estado-advertenciaBg text-estado-advertencia rounded-full flex items-center justify-center mx-auto shadow-sm">
                                <i class="ph-bold ph-folder-dashed text-xl"></i>
                            </div>
                            <h5 class="text-lg font-black text-titulo">Falta Matriz Documental</h5>
                            <p class="text-sm font-bold text-apoyo">Seleccione un rol en el Paso 4 primero.</p>
                            <button type="button" wire:click="gotoStep(4)" class="px-5 py-2 bg-estado-advertencia text-white text-sm font-bold rounded-xl hover:bg-estado-advertencia/80 transition-colors shadow-md">
                                <i class="ph-bold ph-arrow-left mr-1"></i> Volver al Paso 4
                            </button>
                        </div>
                    @else
                        @php
                            $docsInstitucionales = collect($this->documentos_configurados)->where('tipo', 'institucional')->values();
                        @endphp
                        <div class="space-y-2">
                            @forelse($docsInstitucionales as $doc)
                                @php
                                    $estadoActual = $estado_documentos[$doc['id']] ?? 'PENDIENTE';
                                    $instSubido = $estadoActual === 'FIRMADO_SUBIDO' || isset($archivos_temporales[$doc['id']]);
                                    $instObservado = $estadoActual === 'OBSERVADO';
                                @endphp
                                <div wire:key="doc-inst-{{ $doc['id'] }}" class="flex items-center justify-between p-2.5 border rounded-xl bg-white shadow-sm transition-all {{ $instSubido ? 'border-estado-exito/30' : ($instObservado ? 'border-estado-peligro/30 bg-estado-peligroBg/10' : 'border-borde hover:border-boton-acento/40') }}">
                                    <div class="flex items-center gap-2 overflow-hidden flex-1">
                                        <div class="w-7 h-7 rounded-full flex-shrink-0 {{ $instSubido ? 'bg-estado-exitoBg text-estado-exito' : ($instObservado ? 'bg-estado-peligroBg text-estado-peligro' : ($estadoActual === 'GENERADO' ? 'bg-estado-infoBg text-estado-info' : 'bg-fondo text-apoyo')) }} flex items-center justify-center border border-borde/40">
                                            <i class="ph-fill {{ $instSubido ? 'ph-check-circle' : ($instObservado ? 'ph-warning-circle' : ($estadoActual === 'GENERADO' ? 'ph-file-pdf' : 'ph-file-text')) }} text-lg"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h6 class="text-[11px] font-bold text-titulo truncate flex items-center gap-2">
                                                {{ $doc['nombre'] }}
                                            </h6>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($estadoActual === 'PENDIENTE')
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-fondo text-apoyo border border-borde uppercase">Pendiente</span>
                                                @elseif($estadoActual === 'GENERADO')
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-estado-infoBg text-estado-info border border-estado-infoBorde uppercase">Generado</span>
                                                @elseif($estadoActual === 'FIRMADO_SUBIDO' || $instSubido)
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-estado-exitoBg text-estado-exito border border-estado-exito/20 uppercase">Firmado</span>
                                                @elseif($instObservado)
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase">Observado</span>
                                                @endif
                                                <span class="text-[9px] text-apoyo truncate hidden sm:inline">{{ $doc['desc'] }}</span>
                                            </div>
                                            @if($instObservado)
                                                <p class="text-[9px] text-estado-peligro font-bold truncate mt-0.5">Obs: {{ $observacion_documentos[$doc['id']] ?? 'Documento incorrecto.' }}</p>
                                            @endif
                                            @error("archivos_temporales." . $doc['id'])
                                                <p class="text-[9px] text-estado-peligro font-bold truncate mt-0.5">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0 ml-2">
                                        @if($estadoActual === 'PENDIENTE')
                                            <button type="button" wire:key="btn-gen-{{ $doc['id'] }}"
                                                wire:click.prevent="prepararGeneracionPdfInstitucional('{{ $doc['id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="prepararGeneracionPdfInstitucional('{{ $doc['id'] }}')"
                                                class="px-3 py-1.5 text-[10px] font-bold text-white bg-boton-acento rounded hover:bg-boton-acentoHover transition-colors shadow-sm disabled:opacity-60 flex items-center gap-1">
                                                <span wire:loading.remove wire:target="prepararGeneracionPdfInstitucional('{{ $doc['id'] }}')">
                                                    <i class="ph-bold ph-file-pdf"></i> Generar PDF
                                                </span>
                                                <span wire:loading wire:target="prepararGeneracionPdfInstitucional('{{ $doc['id'] }}')">
                                                    <i class="ph-bold ph-spinner animate-spin"></i>
                                                </span>
                                            </button>
                                        @elseif($estadoActual === 'GENERADO' || $estadoActual === 'OBSERVADO')
                                            <button type="button" wire:key="btn-desc-{{ $doc['id'] }}"
                                                wire:click.prevent="descargarPdfInstitucional('{{ $doc['id'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="descargarPdfInstitucional('{{ $doc['id'] }}')"
                                                class="px-3 py-1.5 text-[10px] font-bold text-boton-acento bg-boton-acento/10 border border-boton-acento/20 rounded hover:bg-boton-acento/20 transition-colors flex items-center gap-1">
                                                <span wire:loading.remove wire:target="descargarPdfInstitucional('{{ $doc['id'] }}')">
                                                    <i class="ph-bold ph-download-simple"></i> Descargar PDF
                                                </span>
                                                <span wire:loading wire:target="descargarPdfInstitucional('{{ $doc['id'] }}')">
                                                    <i class="ph-bold ph-spinner animate-spin"></i>
                                                </span>
                                            </button>
                                            <label class="cursor-pointer px-3 py-1.5 text-[10px] font-bold text-white bg-boton-acento border border-boton-acento rounded hover:bg-boton-acentoHover transition-all flex items-center gap-1 shadow-sm">
                                                <i class="ph-bold ph-upload-simple"></i> Subir Firmado
                                                <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            </label>
                                        @elseif($instSubido)
                                            @php
                                                $previewUrlInst = '#';
                                                if (isset($archivos_temporales[$doc['id']])) {
                                                    $fileOrPathInst = $archivos_temporales[$doc['id']];
                                                    if (is_string($fileOrPathInst)) {
                                                        $previewUrlInst = asset('storage/' . $fileOrPathInst);
                                                    } elseif (is_object($fileOrPathInst) && method_exists($fileOrPathInst, 'temporaryUrl')) {
                                                        try {
                                                            $previewUrlInst = $fileOrPathInst->temporaryUrl();
                                                        } catch (\Exception $e) {}
                                                    }
                                                } elseif (isset($documentos_cargados_rutas[$doc['id']])) {
                                                    $previewUrlInst = asset('storage/' . $documentos_cargados_rutas[$doc['id']]);
                                                }
                                            @endphp
                                            @if($previewUrlInst !== '#')
                                                <button type="button" @click="$dispatch('abrir-pdf-generado', { url: '{{ $previewUrlInst }}' })" class="p-1.5 text-boton-acento hover:bg-boton-acento/10 rounded transition-colors" title="Ver Archivo">
                                                    <i class="ph-bold ph-eye text-lg"></i>
                                                </button>
                                            @endif
                                            <span class="px-2 py-1.5 text-[10px] font-bold text-estado-exito bg-estado-exitoBg rounded border border-estado-exito/20">Firmado</span>
                                            <button type="button" wire:click.prevent="removerDocumento('{{ $doc['id'] }}')" class="p-1.5 text-estado-peligro hover:bg-estado-peligro hover:text-white rounded transition-colors" title="Eliminar">
                                                <i class="ph-bold ph-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-sm font-bold text-apoyo border border-dashed border-borde rounded-xl">
                                    No hay documentos institucionales configurados para este rol.
                                </div>
                            @endforelse
                        </div>

                        @error('documentos_generales')
                            <div class="p-3 bg-estado-peligroBg text-estado-peligro border border-estado-peligro/30 rounded-xl text-sm font-bold mt-2 flex items-center gap-2">
                                <i class="ph-bold ph-warning"></i> {{ $message }}
                            </div>
                        @enderror
                    @endif
                </div>

            @elseif($pasoActual == 7)
                <!-- Paso 7: Resumen y Confirmación -->
                <div class="space-y-2">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-boton-acento/10 text-boton-acento mb-3">
                            <i class="ph-bold ph-check-square-offset text-lg"></i>
                        </div>
                        <h4 class="text-lg font-bold text-titulo">Confirmación de Registro</h4>
                        <p class="text-sm text-apoyo">Revise el resumen de la información ingresada antes de procesar el alta en el sistema</p>
                    </div>
                    @php $clasificacionResumen = $clasificacion_derivada; @endphp

                    <!-- Resumen por Secciones -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-left">
                        <!-- Sección: Acceso -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-2">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-key text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Datos de Acceso</h5>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-apoyo">Correo:</span> <span class="font-semibold text-titulo">{{ $correo }}</span></div>
                                <div class="flex justify-between"><span class="text-apoyo">Estado:</span> 
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $estado === 'COMPLETO' ? 'bg-estado-exitoBg text-estado-exito border border-estado-exito/20' : ($estado === 'DOCUMENTACIÓN PENDIENTE' ? 'bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde' : 'bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20') }}">
                                        {{ $estado }}
                                    </span>
                                </div>
                                <div class="flex justify-between"><span class="text-apoyo">Contraseña:</span> <span class="font-semibold text-estado-info font-mono">Autogenerada</span></div>
                            </div>
                        </div>

                        <!-- Sección: Identificación -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-2">
                            <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                <i class="ph-bold ph-user text-boton-acento text-lg"></i>
                                <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Identificación</h5>
                            </div>
                            <div class="flex gap-2">
                                @if($foto_perfil && !is_string($foto_perfil))
                                    <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 border border-borde bg-fondo">
                                        <img src="{{ $foto_perfil->temporaryUrl() }}" class="w-full h-full object-cover">
                                    </div>
                                @elseif($foto_perfil && is_string($foto_perfil))
                                    <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 border border-borde bg-fondo">
                                        <img src="{{ asset('storage/' . $foto_perfil) }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-14 h-14 rounded-xl bg-fondo shrink-0 border border-borde flex items-center justify-center text-apoyo">
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
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-2">
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
                        <div class="bg-white border border-borde rounded-[1.25rem] p-5 shadow-sm space-y-2">
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

                        <!-- La sección de Datos Laborales se ha integrado en la revisión de documentos del ingresante -->

                        <!-- Sección: Documentación del Ingresante -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-3 md:p-4 shadow-sm space-y-2 col-span-1 md:col-span-2">
                            <div class="flex items-center gap-2 pb-3 border-b border-borde/50">
                                <div class="w-7 h-7 rounded-full bg-boton-acento/10 text-boton-acento flex items-center justify-center">
                                    <i class="ph-bold ph-folder-open text-lg"></i>
                                </div>
                                <h5 class="text-sm font-bold text-titulo uppercase tracking-wider">Documentación del Ingresante</h5>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $docsIngresante = array_filter($this->documentos_configurados, function($d) {
                                        return $d['tipo'] === 'ingresante';
                                    });
                                @endphp
                                @foreach($docsIngresante as $doc)
                                    @php
                                        $uploaded = isset($archivos_temporales[$doc['id']]);
                                        $isRequired = $doc['obligatorio_inmediato'] ?? false;
                                    @endphp
                                    <div class="flex items-center justify-between p-3.5 border rounded-xl {{ $uploaded ? 'bg-estado-exitoBg/30 border-estado-exito/30' : 'bg-fondo/30 border-borde/60' }} transition-colors">
                                        <div class="flex flex-col text-left gap-0.5">
                                            <span class="text-xs font-bold text-titulo">{{ $doc['nombre'] }}</span>
                                            <span class="text-[10px] text-apoyo/80">
                                                @if($isRequired)
                                                    <span class="text-estado-peligro font-bold">Obligatorio</span>
                                                @else
                                                     Recomendado (Plazo 48h)
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            @if($uploaded)
                                                <span class="px-2.5 py-1.5 rounded-md text-[10px] font-bold bg-estado-exito text-white shadow-sm uppercase tracking-wider">Cargado</span>
                                            @else
                                                <span class="px-2.5 py-1.5 rounded-md text-[10px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sección: Documentación Institucional -->
                        <div class="bg-white border border-borde rounded-[1.25rem] p-3 md:p-4 shadow-sm space-y-2 col-span-1 md:col-span-2">
                            <div class="flex items-center gap-2 pb-3 border-b border-borde/50">
                                <div class="w-7 h-7 rounded-full bg-boton-acento/10 text-boton-acento flex items-center justify-center">
                                    <i class="ph-bold ph-file-pdf text-lg"></i>
                                </div>
                                <h5 class="text-sm font-bold text-titulo uppercase tracking-wider">Documentación Institucional Firmada</h5>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @php
                                    $docsInstResumen = array_filter($this->documentos_configurados, function($d) {
                                        return $d['tipo'] === 'institucional';
                                    });
                                @endphp
                                @foreach($docsInstResumen as $doc)
                                    @php
                                        $estadoActual = $estado_documentos[$doc['id']] ?? 'PENDIENTE';
                                        $uploaded = $estadoActual === 'FIRMADO_SUBIDO' || isset($archivos_temporales[$doc['id']]);
                                        $isRequired = $doc['obligatorio_inmediato'] ?? false;
                                    @endphp
                                    <div class="flex items-center justify-between p-2.5 border rounded-xl {{ $uploaded ? 'bg-estado-exitoBg/30 border-estado-exito/30' : 'bg-fondo/30 border-borde/60' }} transition-colors">
                                        <div class="flex flex-col text-left gap-0.5">
                                            <span class="text-[11px] font-bold text-titulo">{{ $doc['nombre'] }}</span>
                                            <span class="text-[9px] text-apoyo/80">
                                                @if($isRequired)
                                                    <span class="text-estado-peligro font-bold">Obligatorio</span>
                                                @else
                                                     Opcional
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            @if($uploaded)
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-estado-exito text-white shadow-sm uppercase tracking-wider">Cargado</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>


                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Observaciones Iniciales (Opcional)</label>
                        <textarea wire:model="observaciones" rows="2" x-init="$nextTick(() => $el.focus())" class="w-full rounded-xl border-input-borde bg-input-bg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus" placeholder="Ej: Ingreso por periodo de prueba..."></textarea>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between pt-3 mt-2 border-t border-borde/60 bg-white sticky bottom-0 z-10 pb-1">
            @if($pasoActual > 1)
                <button type="button" wire:click="retrocederPaso" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-bold border-2 border-borde rounded-xl text-titulo hover:bg-fondo-hover hover:border-apoyo/30 transition-all disabled:opacity-50 flex items-center gap-2">
                    <span wire:loading.remove wire:target="retrocederPaso"><i class="ph-bold ph-arrow-left"></i> Atrás</span>
                    <span wire:loading wire:target="retrocederPaso"><i class="ph-bold ph-spinner animate-spin"></i></span>
                </button>
            @else
                <button type="button" x-on:click="
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
                " class="px-4 py-2 text-sm font-bold text-apoyo hover:text-estado-peligro hover:bg-estado-peligroBg rounded-xl transition-all">
                    Cancelar
                </button>
            @endif

            @if($pasoActual < $totalPasos)
                <button type="button" wire:click="avanzarPaso" wire:loading.attr="disabled" class="bg-boton-acento hover:bg-boton-acentoHover text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-boton-acento/20 transition-all flex items-center gap-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="avanzarPaso">Siguiente <i class="ph-bold ph-arrow-right"></i></span>
                    <span wire:loading wire:target="avanzarPaso"><i class="ph-bold ph-spinner animate-spin"></i> Procesando...</span>
                </button>
            @else
                <button type="button" wire:click="preGuardar" wire:loading.attr="disabled" class="bg-estado-exito hover:bg-estado-exito/90 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-estado-exito/30 transition-all flex items-center gap-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="preGuardar"><i class="ph-bold ph-check-circle text-lg"></i> Confirmar Registro</span>
                    <span wire:loading wire:target="preGuardar"><i class="ph-bold ph-spinner animate-spin text-lg"></i> Guardando...</span>
                </button>
            @endif
        </div>
        </div>
    </form>
</div>

@else
<form wire:submit.prevent="guardar" class="space-y-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <!-- Columna Izquierda: Datos de Usuario -->
        <div class="space-y-2">
            <h3 class="text-lg font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                <i class="ph-bold ph-user"></i> Datos Personales
            </h3>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Nombres <span class="text-estado-peligro">*</span></label>
                    <input type="text" wire:model="nombres" class="w-full rounded-xl border-input-borde bg-input-bg text-sm uppercase focus:ring-input-ringFocus focus:border-input-bordeFocus" required>
                    @error('nombres') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Ap. Paterno <span class="text-estado-peligro">*</span></label>
                    <input type="text" wire:model="ap_paterno" class="w-full rounded-xl border-input-borde bg-input-bg text-sm uppercase focus:ring-input-ringFocus focus:border-input-bordeFocus" required>
                    @error('ap_paterno') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Ap. Materno</label>
                    <input type="text" wire:model="ap_materno" class="w-full rounded-xl border-input-borde bg-input-bg text-sm uppercase focus:ring-input-ringFocus focus:border-input-bordeFocus">
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Documento <span class="text-estado-peligro">*</span></label>
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

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Correo Electrónico <span class="text-estado-peligro">*</span></label>
                    <input type="email" wire:model="correo" class="w-full rounded-xl border-input-borde bg-input-bg text-sm lowercase" required>
                    @error('correo') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Teléfono</label>
                    <input type="text" wire:model="telefono" class="w-full rounded-xl border-input-borde bg-input-bg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Estado</label>
                    <select wire:model="estado" class="w-full rounded-xl border-input-borde bg-input-bg text-sm">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="SUSPENDIDO">SUSPENDIDO</option>
                        <option value="RETIRADO">RETIRADO</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Roles y Permisos</label>
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
        <div class="space-y-2">
            @php($clasificacion = $clasificacion_derivada)

            <h3 class="text-lg font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
                <i class="ph-bold ph-briefcase"></i> Clasificación Institucional
            </h3>

            @if($clasificacion)
                <div class="p-4 {{ $clasificacion['tipo_personal'] === 'salud' ? 'bg-estado-infoBg border-estado-infoBorde' : 'bg-estado-advertenciaBg border-estado-advertenciaBorde' }} border rounded-xl space-y-2">
                    <div>
                        <h5 class="text-xs font-bold uppercase tracking-wider {{ $clasificacion['tipo_personal'] === 'salud' ? 'text-estado-info' : 'text-estado-advertencia' }}">Derivada del rol</h5>
                        <p class="text-sm font-bold text-titulo">{{ $clasificacion['rol_sistema'] }}</p>
                    </div>

                    <div class="space-y-2">
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
        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Observaciones / Notas</label>
        <textarea wire:model="observaciones" rows="3" class="w-full rounded-xl border-input-borde bg-input-bg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus"></textarea>
    </div>

    <div class="flex items-center justify-end gap-2 pt-6 border-t border-borde">
        <button type="button" x-on:click="$wire.dispatch('cerrarModalGestion')" class="px-4 py-2 text-sm font-bold text-apoyo hover:text-titulo transition-colors">
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
                // Un solo dispatch: cerrarModal() re-renderiza el panel (actualiza tabla automáticamente)
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

    $wire.on('mostrarAlerta', (e) => {
        const d = e[0];
        const iconMap = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
        Swal.fire({
            title: d.title ?? '',
            text: d.message ?? '',
            icon: iconMap[d.type] ?? 'info',
            confirmButtonColor: '#3F7D5A',
            timer: d.type === 'success' ? 3000 : undefined,
            timerProgressBar: d.type === 'success',
        });
    });

    $wire.on('abrirPdfGenerado', (e) => {
        const data = e[0] ?? e;
        if (data && data.url) {
            window.open(data.url, '_blank');
        }
    });
</script>
@endscript
