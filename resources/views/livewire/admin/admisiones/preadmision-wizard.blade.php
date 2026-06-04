<div class="relative mx-auto max-w-6xl space-y-4">
    {{-- Header --}}
    <section class="rm-surface-glass p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-widest text-terracota">
                    Módulo Admisiones
                </span>
                <h1 class="mt-1 text-2xl font-black leading-tight text-titulo">
                    Preadmisión de Adulto Mayor
                </h1>
                <p class="mt-1 max-w-2xl text-sm font-bold leading-5 text-titulo/60">
                    Complete los pasos obligatorios para registrar al paciente y asignarlo a una valoración inicial.
                </p>
            </div>

            <a href="{{ route('admin.admisiones.index') }}" class="rm-btn-secondary">
                <i class="ph-bold ph-arrow-left"></i>
                Volver
            </a>
        </div>

        {{-- Barra de progreso --}}
        <div class="mt-5">
            <div class="mb-1.5 flex justify-between text-[11px] font-bold text-titulo/55">
                <span>Paso {{ $paso }} de {{ $totalPasos }}</span>
                <span>{{ round(($paso / $totalPasos) * 100) }}%</span>
            </div>

            <div class="h-1.5 overflow-hidden rounded-full bg-fondo-panel">
                <div class="h-full rounded-full bg-gradient-to-r from-[#D9A27C] to-terracota transition-all duration-500 ease-out"
                    style="width: {{ ($paso / $totalPasos) * 100 }}%">
                </div>
            </div>

            <div class="mt-3 grid grid-cols-5 gap-2 text-center text-[10px] font-bold">
                <div class="truncate rounded-lg px-2 py-1.5 transition {{ $paso >= 1 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45' }}">Datos Base</div>
                <div class="truncate rounded-lg px-2 py-1.5 transition {{ $paso >= 2 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45' }}">Docs Básicos</div>
                <div class="truncate rounded-lg px-2 py-1.5 transition {{ $paso >= 3 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45' }}">Institucional</div>
                <div class="truncate rounded-lg px-2 py-1.5 transition {{ $paso >= 4 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45' }}">Asignación</div>
                <div class="truncate rounded-lg px-2 py-1.5 transition {{ $paso >= 5 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45' }}">Confirmar</div>
            </div>
        </div>
    </section>

    {{-- Resumen de Errores Globales --}}
    @if ($errors->any())
        <section class="rounded-xl border border-terracota/30 bg-boton-acento/10 p-4 text-sm font-bold text-terracota">
            <p class="mb-2 font-black"><i class="ph-bold ph-warning-circle mr-1"></i> Hay errores en el formulario:</p>
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="rm-surface-glass p-6">
        {{-- Paso 1: Datos Obligatorios --}}
        @if ($paso == 1)
            <div class="animate-fade-in">
                <div class="mb-6 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-acento/10 text-terracota">
                        <i class="ph-fill ph-identification-card text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 1: Datos Obligatorios</h2>
                        <p class="text-xs font-bold text-titulo/55">Información básica del adulto mayor y contacto de emergencia.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-3">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-2 border-b border-borde pb-1">Identidad Adulto Mayor</h3>
                    </div>
                    
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Nombres *</label>
                        <input type="text" wire:model.blur="nombres" placeholder="Ej. Juan" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Apellido Paterno *</label>
                        <input type="text" wire:model.blur="ap_paterno" placeholder="Ej. Pérez" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Apellido Materno</label>
                        <input type="text" wire:model.blur="ap_materno" placeholder="Ej. Gómez" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">C.I. *</label>
                        <input type="text" wire:model.blur="ci" placeholder="Ej. 1234567" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Expedición C.I. *</label>
                        <select wire:model.blur="expedicion_ci" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            <option value="LP">La Paz (LP)</option>
                            <option value="SC">Santa Cruz (SC)</option>
                            <option value="CB">Cochabamba (CB)</option>
                            <option value="OR">Oruro (OR)</option>
                            <option value="PT">Potosí (PT)</option>
                            <option value="CH">Chuquisaca (CH)</option>
                            <option value="TJ">Tarija (TJ)</option>
                            <option value="BE">Beni (BE)</option>
                            <option value="PA">Pando (PA)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Fecha de Nacimiento *</label>
                        <input type="date" wire:model.blur="fecha_nac" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Género *</label>
                        <select wire:model.blur="genero" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            <option value="MASCULINO">Masculino</option>
                            <option value="FEMENINO">Femenino</option>
                            <option value="OTRO">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 mt-6">
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-2 border-b border-borde pb-1">Familiar Responsable (Emergencia)</h3>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Nombre Completo *</label>
                        <input type="text" wire:model.blur="contacto_emergencia_nombre" placeholder="Nombre del familiar" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Parentesco *</label>
                        <select wire:model.blur="contacto_emergencia_parentesco" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            <option value="HIJO/A">Hijo/a</option>
                            <option value="CÓNYUGE">Cónyuge</option>
                            <option value="HERMANO/A">Hermano/a</option>
                            <option value="NIETO/A">Nieto/a</option>
                            <option value="SOBRINO/A">Sobrino/a</option>
                            <option value="OTRO">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Celular de Contacto *</label>
                        <input type="text" wire:model.blur="contacto_emergencia_celular" placeholder="Ej. 70012345" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Dirección Actual</label>
                        <input type="text" wire:model.blur="contacto_emergencia_direccion" placeholder="Av. Siempre Viva 123" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                    </div>
                </div>
            </div>
        @endif

        {{-- Paso 2: Documentos Obligatorios --}}
        @if ($paso == 2)
            <div class="animate-fade-in">
                <div class="mb-6 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="ph-fill ph-file-pdf text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 2: Documentos Obligatorios</h2>
                        <p class="text-xs font-bold text-titulo/55">Cargue los documentos requeridos para la preadmisión.</p>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-titulo/70">Fotocopia CI Adulto Mayor *</label>
                        <input type="file" wire:model="doc_ci_adulto" class="block w-full text-sm font-bold text-titulo file:mr-4 file:rounded-xl file:border-0 file:bg-boton-principal file:px-4 file:py-2 file:text-xs file:font-black file:text-inverso file:transition hover:file:bg-boton-acento focus:outline-none">
                        <div wire:loading wire:target="doc_ci_adulto" class="text-[10px] text-blue-500 mt-1 font-bold">Cargando...</div>
                        @if ($doc_ci_adulto)
                            <p class="text-[10px] text-emerald-600 mt-1 font-bold"><i class="ph-bold ph-check-circle"></i> Archivo listo.</p>
                        @endif
                    </div>
                    
                    <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-titulo/70">Fotocopia CI Familiar Responsable *</label>
                        <input type="file" wire:model="doc_ci_familiar" class="block w-full text-sm font-bold text-titulo file:mr-4 file:rounded-xl file:border-0 file:bg-boton-principal file:px-4 file:py-2 file:text-xs file:font-black file:text-inverso file:transition hover:file:bg-boton-acento focus:outline-none">
                        <div wire:loading wire:target="doc_ci_familiar" class="text-[10px] text-blue-500 mt-1 font-bold">Cargando...</div>
                        @if ($doc_ci_familiar)
                            <p class="text-[10px] text-emerald-600 mt-1 font-bold"><i class="ph-bold ph-check-circle"></i> Archivo listo.</p>
                        @endif
                    </div>

                    <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 shadow-sm md:col-span-2">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-titulo/70">Croquis de Domicilio (Opcional)</label>
                        <input type="file" wire:model="doc_croquis" class="block w-full text-sm font-bold text-titulo file:mr-4 file:rounded-xl file:border-0 file:bg-boton-principal file:px-4 file:py-2 file:text-xs file:font-black file:text-inverso file:transition hover:file:bg-boton-acento focus:outline-none">
                        <div wire:loading wire:target="doc_croquis" class="text-[10px] text-blue-500 mt-1 font-bold">Cargando...</div>
                        @if ($doc_croquis)
                            <p class="text-[10px] text-emerald-600 mt-1 font-bold"><i class="ph-bold ph-check-circle"></i> Archivo listo.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Paso 3: Documentos Institucionales --}}
        @if ($paso == 3)
            <div class="animate-fade-in">
                <div class="mb-6 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <i class="ph-fill ph-files text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 3: Documentos Institucionales</h2>
                        <p class="text-xs font-bold text-titulo/55">Al confirmar la preadmisión se generarán automáticamente estos documentos.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex items-start gap-4 rounded-xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                        <div class="mt-1 text-emerald-500"><i class="ph-bold ph-file-text text-2xl"></i></div>
                        <div>
                            <h4 class="text-sm font-bold text-titulo">Contrato de Prestación de Servicios</h4>
                            <p class="text-xs text-apoyo">Se generará con los datos de {{ $nombres }} y del responsable {{ $contacto_emergencia_nombre }}.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                        <div class="mt-1 text-emerald-500"><i class="ph-bold ph-file-text text-2xl"></i></div>
                        <div>
                            <h4 class="text-sm font-bold text-titulo">Consentimiento Informado</h4>
                            <p class="text-xs text-apoyo">Documento legal sobre las condiciones médicas de ingreso.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                        <div class="mt-1 text-emerald-500"><i class="ph-bold ph-file-text text-2xl"></i></div>
                        <div>
                            <h4 class="text-sm font-bold text-titulo">Hoja de Admisión</h4>
                            <p class="text-xs text-apoyo">Ficha resumida con datos para el expediente físico.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Paso 4: Selección de Enfermero --}}
        @if ($paso == 4)
            <div class="animate-fade-in">
                <div class="mb-6 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="ph-fill ph-user-nurse text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 4: Valoración Inicial</h2>
                        <p class="text-xs font-bold text-titulo/55">Seleccione al enfermero/a encargado de realizar la valoración inicial obligatoria.</p>
                    </div>
                </div>

                <div class="max-w-md">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-titulo/70">Enfermero de Turno / Disponible *</label>
                    <div class="grid gap-3">
                        @foreach ($enfermeros as $enfermero)
                            <label class="flex items-center gap-4 cursor-pointer rounded-xl border {{ $enfermero_id == $enfermero->cod_usu ? 'border-boton-acento bg-boton-acento/5 ring-1 ring-boton-acento/50' : 'border-borde-suave bg-fondo-panel' }} p-4 transition-all hover:border-boton-acento hover:bg-boton-acento/5">
                                <input type="radio" wire:model.live="enfermero_id" value="{{ $enfermero->cod_usu }}" class="h-4 w-4 text-boton-acento border-borde-suave focus:ring-boton-acento">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-boton-acento/10 text-boton-acento font-black">
                                        {{ substr($enfermero->nombres, 0, 1) }}{{ substr($enfermero->ap_paterno, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-titulo">{{ $enfermero->nombres }} {{ $enfermero->ap_paterno }}</p>
                                        <p class="text-xs font-semibold text-apoyo">Rol: Enfermería</p>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Paso 5: Confirmación --}}
        @if ($paso == 5)
            <div class="animate-fade-in">
                <div class="mb-6 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-estado-exitoBg text-parrafo">
                        <i class="ph-fill ph-check-circle text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 5: Confirmación de Preadmisión</h2>
                        <p class="text-xs font-bold text-titulo/55">Revise y finalice el proceso.</p>
                    </div>
                </div>

                <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5">
                    <h3 class="text-sm font-bold text-titulo mb-3">Resumen:</h3>
                    <ul class="text-xs font-bold text-titulo/70 space-y-2">
                        <li><i class="ph-bold ph-caret-right text-terracota mr-1"></i> <strong>Adulto Mayor:</strong> {{ $nombres }} {{ $ap_paterno }}</li>
                        <li><i class="ph-bold ph-caret-right text-terracota mr-1"></i> <strong>Responsable:</strong> {{ $contacto_emergencia_nombre }} ({{ $contacto_emergencia_parentesco }})</li>
                        <li><i class="ph-bold ph-caret-right text-terracota mr-1"></i> <strong>Estado a aplicar:</strong> PENDIENTE DE VALORACIÓN INICIAL</li>
                        @php
                            $enfSelec = $enfermeros->where('cod_usu', $enfermero_id)->first();
                        @endphp
                        <li><i class="ph-bold ph-caret-right text-terracota mr-1"></i> <strong>Enfermero Asignado:</strong> {{ $enfSelec ? $enfSelec->nombres . ' ' . $enfSelec->ap_paterno : 'Ninguno' }}</li>
                    </ul>
                    
                    <div class="mt-5 rounded-lg bg-amber-50 p-3 border border-amber-200">
                        <p class="text-[11px] font-bold text-amber-700">
                            <i class="ph-bold ph-info"></i> Al confirmar, el adulto mayor aparecerá en el dashboard del enfermero seleccionado para que inicie la valoración de enfermería requerida.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Botones de acción --}}
    <div class="mt-4 flex justify-between items-center rm-surface-glass p-4 rounded-xl">
        <div>
            @if ($paso > 1)
                <button type="button" wire:click="anterior" class="rm-btn-secondary">
                    <i class="ph-bold ph-arrow-left"></i> Atrás
                </button>
            @endif
        </div>
        <div>
            @if ($paso < $totalPasos)
                <button type="button" wire:click="siguiente" class="rm-btn-primary group">
                    <span wire:loading.remove wire:target="siguiente">Siguiente <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i></span>
                    <span wire:loading wire:target="siguiente"><i class="ph-bold ph-spinner animate-spin"></i> Validando...</span>
                </button>
            @else
                <button type="button" wire:click="confirmarPreadmision" class="rm-btn-accent group">
                    <span wire:loading.remove wire:target="confirmarPreadmision"><i class="ph-bold ph-check-circle"></i> Confirmar Preadmisión</span>
                    <span wire:loading wire:target="confirmarPreadmision"><i class="ph-bold ph-spinner animate-spin"></i> Procesando...</span>
                </button>
            @endif
        </div>
    </div>
</div>

@script
<script>
    $wire.on('swal', (data) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: data[0].title,
                text: data[0].text,
                icon: data[0].icon,
                confirmButtonColor: '#D9A27C',
                confirmButtonText: 'Entendido'
            });
        } else {
            alert(data[0].title + '\n' + data[0].text);
        }
    });
</script>
@endscript
