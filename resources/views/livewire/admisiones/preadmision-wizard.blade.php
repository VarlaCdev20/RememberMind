<div class="fixed inset-0 z-[100] flex items-center justify-center bg-[var(--color-modal-overlay)] p-4 backdrop-blur-sm transition-opacity">
    <div x-data="{ isDirty: false }" x-on:input="isDirty = true" x-on:change="isDirty = true" class="max-w-4xl mx-auto w-full max-h-[90vh] flex flex-col p-4 bg-fondo-card rounded-2xl shadow-2xl border border-borde/30 relative overflow-hidden">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4 border-b border-borde/50 pb-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-inner border border-boton-acento/20">
                    <i class="ph-fill ph-file-plus text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-titulo leading-tight">Registrar Preadmisión</h3>
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
            " class="w-8 h-8 flex items-center justify-center rounded-full bg-fondo text-apoyo hover:bg-estado-peligroBg hover:text-estado-peligro transition-colors">
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
            
            // Simular errores para el stepper
            $pasosErrores = [
                1 => $errors->has('nombres') || $errors->has('ap_paterno') || $errors->has('ap_materno') || $errors->has('ci') || $errors->has('expedicion_ci') || $errors->has('fecha_nac') || $errors->has('genero') || $errors->has('estado_civil') || $errors->has('telefono') || $errors->has('celular'),
                2 => $errors->has('departamento_residencia') || $errors->has('ciudad_municipio') || $errors->has('zona') || $errors->has('calle') || $errors->has('direccion_referencia'),
                3 => $errors->has('familiar_nombres') || $errors->has('familiar_ap_paterno') || $errors->has('familiar_ap_materno') || $errors->has('familiar_ci') || $errors->has('familiar_parentesco') || $errors->has('familiar_celular') || $errors->has('familiar_correo') || $errors->has('familiar_direccion'),
                4 => $errors->has('motivo_ingreso') || $errors->has('procedencia_ingreso') || $errors->has('tipo_ingreso') || $errors->has('permanencia') || $errors->has('prioridad') || $errors->has('descripcion_caso'),
                5 => $errors->has('doc_ci_adulto') || $errors->has('doc_ci_familiar') || $errors->has('doc_solicitud_ingreso') || $errors->has('documentos')
            ];
        @endphp

        <!-- Stepper Compacto -->
        <div class="mb-3">
            <div class="relative flex items-center justify-between w-full pb-3">
                <!-- Linea de fondo -->
                <div class="absolute left-5 right-5 top-4 transform -translate-y-1/2 h-[3px] bg-borde/40 rounded-full z-0"></div>
                <!-- Linea de progreso -->
                <div class="absolute left-5 top-4 transform -translate-y-1/2 h-[3px] bg-boton-acento rounded-full z-0 transition-all duration-500 ease-out" style="width: calc({{ (($paso - 1) / 4) * 100 }}% - 2.5rem)"></div>
                
                @foreach($pasoActualsLista as $num => $nombre)
                    <div class="relative z-10 flex flex-col items-center group">
                        @php
                            $hasError = $pasosErrores[$num] ?? false;
                            $isCompleted = $paso > $num;
                            $isActive = $paso == $num;
                        @endphp
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 {{
                            $hasError
                                ? 'bg-estado-peligroBg text-estado-peligro border-2 border-estado-peligro'
                                : ($isActive
                                    ? 'bg-boton-acento text-inverso ring-4 ring-boton-acento/20 scale-105 shadow-md'
                                    : ($isCompleted
                                        ? 'bg-boton-acento text-inverso hover:bg-boton-acentoHover'
                                        : 'bg-fondo-card text-apoyo/40 border-2 border-borde/60 hover:border-apoyo/30'))
                        }}">
                            @if($hasError)
                                <i class="ph-bold ph-warning text-sm"></i>
                            @elseif($isCompleted)
                                <i class="ph-bold ph-check text-sm"></i>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="absolute top-9 text-[9px] font-bold uppercase tracking-wider whitespace-nowrap transition-colors duration-300 {{
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
                <span class="text-[11px] font-black text-boton-acento uppercase tracking-wider bg-boton-acento/10 px-3 py-1.5 rounded-full">Paso {{ $paso }}: {{ $pasoActualsLista[$paso] }}</span>
            </div>
        </div>

        <!-- Form Body -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden pr-2 space-y-2 pb-2 custom-scrollbar">
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
                            <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">{{ $label }}</label>
                            <input type="text" wire:model.blur="{{ $field }}" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has($field),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has($field),
    ])>
                            @error($field) <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Expedicion CI *</label>
                        <select wire:model.blur="expedicion_ci" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('expedicion_ci'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('expedicion_ci'),
    ])>
                            <option value="">Seleccionar</option>
                            @foreach (['LP','SC','CB','OR','PT','CH','TJ','BE','PA'] as $dep)
                                <option value="{{ $dep }}">{{ $dep }}</option>
                            @endforeach
                        </select>
                            @error('expedicion_ci') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Fecha de nacimiento *</label>
                        <input type="date" wire:model.blur="fecha_nac" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('fecha_nac'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('fecha_nac'),
    ])>
                            @error('fecha_nac') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Genero *</label>
                        <select wire:model.blur="genero" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('genero'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('genero'),
    ])>
                            <option value="">Seleccionar</option>
                            <option value="MASCULINO">Masculino</option>
                            <option value="FEMENINO">Femenino</option>
                            <option value="OTRO">Otro</option>
                        </select>
                            @error('genero') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Estado civil</label>
                        <select wire:model.blur="estado_civil" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('estado_civil'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('estado_civil'),
    ])>
                            @foreach (['NO ESPECIFICADO','SOLTERO/A','CASADO/A','VIUDO/A','DIVORCIADO/A','UNION LIBRE'] as $estadoCivil)
                                <option value="{{ $estadoCivil }}">{{ $estadoCivil }}</option>
                            @endforeach
                        </select>
                            @error('estado_civil') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Telefono</label>
                        <input type="text" wire:model.blur="telefono" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('telefono'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('telefono'),
    ])>
                            @error('telefono') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Celular</label>
                        <input type="text" wire:model.blur="celular" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('celular'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('celular'),
    ])>
                            @error('celular') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
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
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Departamento *</label>
                        <select wire:model.blur="departamento_residencia" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('departamento_residencia'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('departamento_residencia'),
    ])>
                            <option value="">Seleccionar</option>
                            @foreach (['LA PAZ','SANTA CRUZ','COCHABAMBA','ORURO','POTOSI','CHUQUISACA','TARIJA','BENI','PANDO'] as $dep)
                                <option value="{{ $dep }}">{{ $dep }}</option>
                            @endforeach
                        </select>
                            @error('departamento_residencia') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    @foreach ([
                        'ciudad_municipio' => 'Ciudad / municipio *',
                        'zona' => 'Zona *',
                        'calle' => 'Calle / avenida *',
                    ] as $field => $label)
                        <div>
                            <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">{{ $label }}</label>
                            <input type="text" wire:model.blur="{{ $field }}" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has($field),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has($field),
    ])>
                            @error($field) <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Referencia de direccion</label>
                        <textarea wire:model.blur="direccion_referencia" rows="3" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('direccion_referencia'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('direccion_referencia'),
    ])></textarea>
                            @error('direccion_referencia') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
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
                        'familiar_ap_paterno' => 'Apellido paterno',
                        'familiar_ap_materno' => 'Apellido materno',
                        'familiar_ci' => 'CI familiar',
                    ] as $field => $label)
                        <div>
                            <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">{{ $label }}</label>
                            <input type="text" wire:model.blur="{{ $field }}" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has($field),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has($field),
    ])>
                            @error($field) <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Parentesco *</label>
                        <select wire:model.blur="familiar_parentesco" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('familiar_parentesco'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('familiar_parentesco'),
    ])>
                            <option value="">Seleccionar</option>
                            @foreach (['HIJO/A','ESPOSO/A','HERMANO/A','SOBRINO/A','NIETO/A','TUTOR/A','APODERADO/A','OTRO'] as $parentesco)
                                <option value="{{ $parentesco }}">{{ $parentesco }}</option>
                            @endforeach
                        </select>
                            @error('familiar_parentesco') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Celular *</label>
                        <input type="text" wire:model.blur="familiar_celular" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('familiar_celular'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('familiar_celular'),
    ])>
                            @error('familiar_celular') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Correo</label>
                        <input type="email" wire:model.blur="familiar_correo" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('familiar_correo'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('familiar_correo'),
    ])>
                            @error('familiar_correo') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Direccion familiar</label>
                        <textarea wire:model.blur="familiar_direccion" rows="3" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('familiar_direccion'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('familiar_direccion'),
    ])></textarea>
                            @error('familiar_direccion') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
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
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Motivo de ingreso *</label>
                        <select wire:model.blur="motivo_ingreso" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('motivo_ingreso'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('motivo_ingreso'),
    ])>
                            <option value="">Seleccionar</option>
                            @foreach (['CUIDADO_PERMANENTE','CUIDADO_TEMPORAL','CONTROL_MEDICACION','RIESGO_CAIDAS','DEPENDENCIA_FUNCIONAL','SOLEDAD_FAMILIAR','RECUPERACION_POST_HOSPITALARIA','OTRO'] as $motivo)
                                <option value="{{ $motivo }}">{{ str_replace('_', ' ', $motivo) }}</option>
                            @endforeach
                        </select>
                            @error('motivo_ingreso') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Procedencia *</label>
                        <select wire:model.blur="procedencia_ingreso" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('procedencia_ingreso'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('procedencia_ingreso'),
    ])>
                            <option value="">Seleccionar</option>
                            @foreach (['DOMICILIO_FAMILIAR','HOSPITAL','OTRO_CENTRO_GERIATRICO','INSTITUCION_SOCIAL','CONSULTA_MEDICA_EXTERNA','OTRO'] as $procedencia)
                                <option value="{{ $procedencia }}">{{ str_replace('_', ' ', $procedencia) }}</option>
                            @endforeach
                        </select>
                            @error('procedencia_ingreso') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Tipo de ingreso *</label>
                        <select wire:model.blur="tipo_ingreso" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('tipo_ingreso'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('tipo_ingreso'),
    ])>
                            <option value="REGULAR">Regular</option>
                            <option value="URGENTE">Urgente</option>
                            <option value="DERIVACION">Derivacion</option>
                        </select>
                            @error('tipo_ingreso') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Permanencia *</label>
                        <select wire:model.blur="permanencia" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('permanencia'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('permanencia'),
    ])>
                            <option value="PERMANENTE">Permanente</option>
                            <option value="TEMPORAL">Temporal</option>
                            <option value="OBSERVACION">Observacion</option>
                        </select>
                            @error('permanencia') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Prioridad *</label>
                        <select wire:model.blur="prioridad" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('prioridad'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('prioridad'),
    ])>
                            @foreach (['BAJA','MEDIA','ALTA','CRITICA'] as $nivel)
                                <option value="{{ $nivel }}">{{ $nivel }}</option>
                            @endforeach
                        </select>
                            @error('prioridad') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-0.5">Descripcion del caso</label>
                        <textarea wire:model.blur="descripcion_caso" rows="4" @class([
        'w-full rounded-xl border bg-input-bg py-1.5 text-xs text-input-texto',
        'border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro' => $errors->has('descripcion_caso'),
        'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus' => !$errors->has('descripcion_caso'),
    ])></textarea>
                            @error('descripcion_caso') <span class="mt-1 block text-[11px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
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

                {{-- Documentos del Solicitante --}}
                <div class="space-y-2 bg-fondo/30 p-3 md:p-4 rounded-lg border border-borde/60">
                    <div class="flex items-center gap-2 pb-3 border-b border-borde/50">
                        <div class="w-7 h-7 rounded-full bg-boton-acento/10 text-boton-acento flex items-center justify-center">
                            <i class="ph-bold ph-folder-user text-lg"></i>
                        </div>
                        <h5 class="text-sm font-bold text-titulo uppercase tracking-wider">Archivos del Solicitante</h5>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
                        @foreach ($docsSolicitante as $docConf)
                            @php
                                $propiedad  = $docConf['propiedad'];
                                $docSubido  = ! empty($$propiedad);
                                $es48hPend  = in_array($docConf['tipo'], $docs_pendientes_48h);
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
                                        <h6 class="text-[11px] font-bold text-titulo">{{ $docConf['nombre'] }}</h6>
                                        <p class="text-[9px] text-apoyo mt-0.5">{{ $docConf['descripcion'] }}</p>
                                        <div class="flex flex-wrap items-center gap-1 mt-1">
                                            @if($bloqueante)
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase">Obligatorio hoy</span>
                                            @elseif($permite48h)
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase">Permite 48 h</span>
                                            @endif
                                            @if($docConf['requiere_firma'])
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-estado-infoBg text-estado-info border border-estado-infoBorde uppercase">Requiere firma</span>
                                            @endif
                                        </div>
                                        @error($propiedad)
                                            <p class="text-[9px] text-estado-peligro font-bold mt-0.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 mt-2 pt-2 border-t border-borde/30">
                                    @if($docSubido)
                                        <span class="flex-1 text-center px-2 py-1 text-[10px] font-bold text-estado-exito bg-estado-exitoBg rounded border border-estado-exito/20">
                                            <i class="ph-bold ph-check"></i> Subido
                                        </span>
                                    @else
                                        <label class="flex-1 text-center cursor-pointer px-2 py-1 text-[10px] font-bold text-boton-acento border border-boton-acento rounded hover:bg-boton-acento hover:text-inverso transition-all">
                                            <i class="ph-bold ph-upload-simple"></i> Subir
                                            <input type="file" wire:model="{{ $propiedad }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                        </label>
                                        @if($permite48h && ! $docSubido)
                                            @if($es48hPend)
                                                <span class="px-2 py-1 text-[9px] font-bold text-estado-advertencia bg-estado-advertenciaBg border border-estado-advertenciaBorde rounded">
                                                    <i class="ph-bold ph-clock"></i> Pend. 48 h
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-[9px] text-apoyo bg-fondo border border-borde rounded">
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
                        <div class="mt-2 flex items-start gap-2 p-2.5 bg-estado-advertenciaBg/40 border border-estado-advertenciaBorde rounded-xl text-[10px] text-estado-advertencia font-semibold">
                            <i class="ph-bold ph-clock text-base shrink-0 mt-0.5"></i>
                            <span>Los documentos marcados <strong>"Permite 48 h"</strong> pueden ser presentados después de confirmar. Se notificará al familiar responsable con el plazo exacto.</span>
                        </div>
                    @endif
                </div>

                {{-- Documentos Institucionales Autogenerados --}}
                <div class="space-y-2 bg-fondo/30 p-3 md:p-4 rounded-lg border border-borde/60">
                    <div class="flex items-center gap-2 pb-3 border-b border-borde/50">
                        <div class="w-7 h-7 rounded-full bg-estado-infoBg text-estado-info flex items-center justify-center">
                            <i class="ph-bold ph-file-pdf text-lg"></i>
                        </div>
                        <h5 class="text-sm font-bold text-titulo uppercase tracking-wider">Documentos Institucionales</h5>
                        <span class="ml-auto px-2 py-1 rounded-full bg-estado-infoBg text-estado-info text-[9px] font-bold border border-estado-infoBorde uppercase tracking-wider">Autogenerados al confirmar</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($docsInstitucionales as $docConf)
                            <div wire:key="inst-{{ $docConf['tipo'] }}" class="flex items-center justify-between p-2.5 border rounded-xl bg-fondo-card shadow-sm border-borde">
                                <div class="flex items-center gap-2 overflow-hidden flex-1">
                                    <div class="w-7 h-7 rounded-full bg-estado-infoBg text-estado-info flex items-center justify-center shrink-0 border border-borde/40">
                                        <i class="ph-fill ph-file-pdf text-lg"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h6 class="text-[11px] font-bold text-titulo truncate">{{ $docConf['nombre'] }}</h6>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="text-[9px] text-apoyo">PDF generado automáticamente</span>
                                            @if($docConf['requiere_firma'])
                                                <span class="px-1 py-0.5 rounded text-[8px] font-bold bg-estado-infoBg text-estado-info border border-estado-infoBorde uppercase">Firma requerida</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-[10px] font-bold text-estado-info bg-estado-infoBg rounded border border-estado-infoBorde shrink-0 ml-2">
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

        <!-- Botonera -->
        <div class="flex items-center justify-between pt-3 mt-3 border-t border-borde/60 bg-fondo-card sticky bottom-0 z-10 pb-1">
        <div>
            @if ($paso > 1)
                <button type="button" wire:click="anterior" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-bold border-2 border-borde rounded-xl text-titulo hover:bg-fondo-hover hover:border-apoyo/30 transition-all disabled:opacity-50 flex items-center gap-2">
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
                " class="px-4 py-2 text-sm font-bold text-apoyo hover:text-estado-peligro hover:bg-estado-peligroBg rounded-xl transition-all">
                    Cancelar
                </button>
            @endif
        </div>

        @if ($guardadoExitoso)
            <a wire:navigate href="{{ route('admin.admisiones.preadmisiones') }}" class="bg-boton-acento hover:bg-boton-acentoHover text-inverso px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-boton-acento/20 transition-all flex items-center gap-2">
                <i class="ph-bold ph-arrow-square-out"></i>
                Volver al panel
            </a>
        @elseif ($paso < $totalPasos)
            <button type="button" wire:click="siguiente" wire:loading.attr="disabled" class="bg-boton-acento hover:bg-boton-acentoHover text-inverso px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-boton-acento/20 transition-all flex items-center gap-2 disabled:opacity-50">
                <span wire:loading.remove wire:target="siguiente">Siguiente <i class="ph-bold ph-arrow-right"></i></span>
                <span wire:loading wire:target="siguiente"><i class="ph-bold ph-spinner animate-spin"></i> Validando...</span>
            </button>
        @else
            <button type="button" wire:click="confirmarPreadmision" wire:loading.attr="disabled" class="bg-estado-exito hover:bg-estado-exito/90 text-inverso px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-estado-exito/30 transition-all flex items-center gap-2 disabled:opacity-50">
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
