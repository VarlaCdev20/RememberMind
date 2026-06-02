<x-sistema-layout>
    @php
        $adultoObj = is_object($adulto ?? null) ? $adulto : null;
        $idAdulto = optional($adultoObj)->cod_am;

        $fotoAdulto = optional($adultoObj)->foto ?? null;
        $fotoUrl = $fotoAdulto ? \Illuminate\Support\Facades\Storage::url($fotoAdulto) : null;
    @endphp

    <div x-data="adultoMayorFormEdit()" class="relative mx-auto max-w-6xl space-y-4">

        {{-- Header --}}
        <section class="rm-surface-glass p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-terracota">
                        Edición de Expediente
                    </span>
                    <h1 class="mt-1 text-2xl font-black leading-tight text-titulo">
                        Actualizar Ficha: {{ $idAdulto }}
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm font-bold leading-5 text-titulo/60">
                        Modifique los datos administrativos básicos. Los campos obligatorios están marcados con (*).
                    </p>
                </div>

                <a href="{{ route('admin.adultos-mayores.show', $idAdulto) }}"
                   class="rm-btn-secondary">
                    <i class="ph-bold ph-arrow-left"></i>
                    Volver al Expediente
                </a>
            </div>

            {{-- Barra de progreso --}}
            <div class="mt-5">
                <div class="mb-1.5 flex justify-between text-[11px] font-bold text-titulo/55">
                    <span>Paso <span x-text="paso"></span> de <span x-text="total"></span></span>
                    <span x-text="Math.round((paso / total) * 100) + '%'"></span>
                </div>

                <div class="h-1.5 overflow-hidden rounded-full bg-fondo-panel">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#D9A27C] to-terracota transition-all duration-500 ease-out"
                         :style="`width: ${(paso / total) * 100}%`">
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2 text-center text-[10px] font-bold sm:grid-cols-6">
                    <div class="truncate rounded-lg px-2 py-1.5 transition" :class="paso >= 1 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45'">Personales</div>
                    <div class="truncate rounded-lg px-2 py-1.5 transition" :class="paso >= 2 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45'">Identidad</div>
                    <div class="truncate rounded-lg px-2 py-1.5 transition" :class="paso >= 3 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45'">Contacto</div>
                    <div class="truncate rounded-lg px-2 py-1.5 transition" :class="paso >= 4 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45'">Ingreso</div>
                    <div class="truncate rounded-lg px-2 py-1.5 transition" :class="paso >= 5 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45'">Adicional</div>
                    <div class="truncate rounded-lg px-2 py-1.5 transition" :class="paso >= 6 ? 'bg-boton-acento text-inverso shadow-sm' : 'bg-fondo-panel text-titulo/45'">Confirmar</div>
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

        <form method="POST" action="{{ route('admin.adultos-mayores.update', $idAdulto) }}" enctype="multipart/form-data" onsubmit="procesarFormulario(event)">
            @csrf
            @method('PUT')

            {{-- Paso 1: Datos Personales --}}
            <section x-show="paso === 1" x-transition.opacity.duration.250ms class="bg-transparent pt-4 border-t border-borde-suave">
                <div x-show="Object.keys(errors).length > 0" x-cloak
                     class="mb-4 rounded-xl border border-borde-focus bg-estado-peligroBg px-4 py-3 text-xs font-bold text-parrafo">
                    <i class="ph-bold ph-warning-circle mr-1"></i>
                    Revise los campos marcados antes de continuar.
                </div>
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-acento/10 text-terracota">
                        <i class="ph-fill ph-user text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 1: Datos Personales</h2>
                        <p class="text-xs font-bold text-titulo/55">Información demográfica fundamental.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Nombres *</label>
                        <input name="nombres" value="{{ old('nombres', optional($adultoObj)->nombres) }}" placeholder="Ej. MARÍA ELENA" oninput="this.value = this.value.toUpperCase()"
                               @input="clearError('nombres')" @blur="touch('nombres')"
                               :class="fieldClass('nombres')"
                               class="w-full rounded-xl px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-4">
                        <p x-show="errors.nombres" x-text="errors.nombres" x-cloak class="mt-1 text-xs font-bold text-parrafo"></p>
                        @error('nombres') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Apellido Paterno *</label>
                        <input name="ap_paterno" value="{{ old('ap_paterno', optional($adultoObj)->ap_paterno) }}" placeholder="Ej. MAMANI" oninput="this.value = this.value.toUpperCase()"
                               @input="clearError('ap_paterno')" @blur="touch('ap_paterno')"
                               :class="fieldClass('ap_paterno')"
                               class="w-full rounded-xl px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-4">
                        <p x-show="errors.ap_paterno" x-text="errors.ap_paterno" x-cloak class="mt-1 text-xs font-bold text-parrafo"></p>
                        @error('ap_paterno') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Apellido Materno</label>
                        <input name="ap_materno" value="{{ old('ap_materno', optional($adultoObj)->ap_materno) }}" placeholder="Ej. QUISPE" oninput="this.value = this.value.toUpperCase()"
                               class="w-full rounded-xl border {{ $errors->has('ap_materno') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('ap_materno') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Fecha Nacimiento *</label>
                        <input type="date" name="fecha_nac" x-model="fecha_nac"
                               class="w-full rounded-xl border {{ $errors->has('fecha_nac') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('fecha_nac') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Edad Calculada</label>
                        <div class="flex h-[42px] w-full items-center rounded-xl border border-borde-suave bg-fondo-panel px-3.5 text-sm font-bold text-titulo/70">
                            <span x-text="edad !== null ? edad + ' años' : '--'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Género *</label>
                        <select name="genero"
                                class="w-full rounded-xl border {{ $errors->has('genero') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            <option value="MASCULINO" @selected(old('genero', optional($adultoObj)->genero) === 'MASCULINO')>Masculino</option>
                            <option value="FEMENINO" @selected(old('genero', optional($adultoObj)->genero) === 'FEMENINO')>Femenino</option>
                            <option value="OTRO" @selected(old('genero', optional($adultoObj)->genero) === 'OTRO')>Otro</option>
                        </select>
                        @error('genero') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Estado Civil *</label>
                        <select name="estado_civil"
                                class="w-full rounded-xl border {{ $errors->has('estado_civil') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            <option value="SOLTERO/A" @selected(old('estado_civil', optional($adultoObj)->estado_civil) === 'SOLTERO/A')>Soltero/a</option>
                            <option value="CASADO/A" @selected(old('estado_civil', optional($adultoObj)->estado_civil) === 'CASADO/A')>Casado/a</option>
                            <option value="VIUDO/A" @selected(old('estado_civil', optional($adultoObj)->estado_civil) === 'VIUDO/A')>Viudo/a</option>
                            <option value="DIVORCIADO/A" @selected(old('estado_civil', optional($adultoObj)->estado_civil) === 'DIVORCIADO/A')>Divorciado/a</option>
                            <option value="UNIÓN LIBRE" @selected(old('estado_civil', optional($adultoObj)->estado_civil) === 'UNIÓN LIBRE')>Unión Libre</option>
                            <option value="NO ESPECIFICADO" @selected(old('estado_civil', optional($adultoObj)->estado_civil) === 'NO ESPECIFICADO')>No especificado</option>
                        </select>
                        @error('estado_civil') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-3" x-show="edad !== null && edad < 60" x-transition>
                        <div class="flex items-start gap-2 rounded-xl border border-borde bg-fondo-panel p-3 text-parrafo">
                            <i class="ph-fill ph-warning-circle text-lg text-terracota"></i>
                            <div>
                                <p class="text-sm font-bold text-terracota">Paciente menor a 60 años.</p>
                                <p class="text-xs font-bold opacity-80 text-terracota/80">Revise la fecha de nacimiento. La admisión en geriatría requiere 60 años cumplidos.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Paso 2: Identificación --}}
            <section x-cloak x-show="paso === 2" x-transition.opacity.duration.250ms class="bg-transparent pt-4 border-t border-borde-suave">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-estado-exitoBg text-parrafo">
                        <i class="ph-fill ph-identification-card text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 2: Identificación</h2>
                        <p class="text-xs font-bold text-titulo/55">Documentos legales.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">C.I. *</label>
                        <input name="ci" value="{{ old('ci', optional($adultoObj)->ci) }}" placeholder="Ej. 1234567"
                               @input="clearError('ci')" @blur="touch('ci')"
                               :class="fieldClass('ci')"
                               class="w-full rounded-xl px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-4">
                        <p class="mt-1 text-[10px] font-bold text-titulo/45">Solo números, sin espacios.</p>
                        <p x-show="errors.ci" x-text="errors.ci" x-cloak class="mt-1 text-xs font-bold text-parrafo"></p>
                        @error('ci') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Complemento</label>
                        <input name="complemento_ci" value="{{ old('complemento_ci', optional($adultoObj)->complemento_ci) }}" placeholder="Ej. 1A" oninput="this.value = this.value.toUpperCase()"
                               class="w-full rounded-xl border {{ $errors->has('complemento_ci') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('complemento_ci') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Expedición *</label>
                        <select name="expedicion_ci"
                                class="w-full rounded-xl border {{ $errors->has('expedicion_ci') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            @foreach(['LP'=>'La Paz', 'SC'=>'Santa Cruz', 'CB'=>'Cochabamba', 'OR'=>'Oruro', 'PT'=>'Potosí', 'CH'=>'Chuquisaca', 'TJ'=>'Tarija', 'BE'=>'Beni', 'PA'=>'Pando'] as $val => $text)
                                <option value="{{ $val }}" @selected(old('expedicion_ci', optional($adultoObj)->expedicion_ci) === $val)>{{ $text }} ({{ $val }})</option>
                            @endforeach
                        </select>
                        @error('expedicion_ci') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            {{-- Paso 3: Contacto y Dirección --}}
            <section x-cloak x-show="paso === 3" x-transition.opacity.duration.250ms class="bg-transparent pt-4 border-t border-borde-suave">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-principal/10 text-titulo">
                        <i class="ph-fill ph-map-pin text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 3: Contacto y Ubicación</h2>
                        <p class="text-xs font-bold text-titulo/55">Datos para comunicarse con el adulto mayor.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="col-span-1 md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer mb-2">
                            <input type="checkbox" name="tiene_celular" value="1" x-model="tiene_celular"
                                   class="h-5 w-5 rounded border-borde-suave bg-fondo-app text-terracota focus:ring-borde-focus/30">
                            <span class="text-sm font-bold text-titulo">El adulto mayor cuenta con celular propio</span>
                        </label>
                        @error('tiene_celular') <span class="text-xs font-bold text-terracota block">{{ $message }}</span> @enderror
                    </div>

                    <div x-show="tiene_celular" x-transition.opacity.duration.250ms>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Celular *</label>
                        <input name="celular" value="{{ old('celular', optional($adultoObj)->celular) }}" placeholder="Ej. 70012345"
                               class="w-full rounded-xl border {{ $errors->has('celular') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        <p class="mt-1 text-[10px] font-bold text-titulo/45">8 dígitos, comenzando con 6 o 7.</p>
                        @error('celular') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div x-show="tiene_celular" x-transition.opacity.duration.250ms class="flex items-center mt-2 md:mt-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="sabe_usar_whatsapp" value="1" @checked(old('sabe_usar_whatsapp', optional($adultoObj)->sabe_usar_whatsapp))
                                   class="h-5 w-5 rounded border-borde-suave bg-fondo-app text-terracota focus:ring-borde-focus/30">
                            <span class="text-sm font-bold text-titulo">Sabe utilizar WhatsApp</span>
                        </label>
                        @error('sabe_usar_whatsapp') <span class="text-xs font-bold text-terracota block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Teléfono Fijo</label>
                        <input name="telefono_fijo" value="{{ old('telefono_fijo', optional($adultoObj)->telefono_fijo) }}" placeholder="Ej. 2223344"
                               class="w-full rounded-xl border {{ $errors->has('telefono_fijo') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('telefono_fijo') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Dpto. de Residencia *</label>
                        <select name="departamento_residencia"
                                class="w-full rounded-xl border {{ $errors->has('departamento_residencia') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            @foreach(['LA PAZ', 'SANTA CRUZ', 'COCHABAMBA', 'ORURO', 'POTOSÍ', 'CHUQUISACA', 'TARIJA', 'BENI', 'PANDO'] as $dep)
                                <option value="{{ $dep }}" @selected(old('departamento_residencia', optional($adultoObj)->departamento_residencia) === $dep)>{{ $dep }}</option>
                            @endforeach
                        </select>
                        @error('departamento_residencia') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Ciudad / Municipio *</label>
                        <input name="ciudad_municipio" value="{{ old('ciudad_municipio', optional($adultoObj)->ciudad_municipio) }}" placeholder="EJ. EL ALTO" oninput="this.value = this.value.toUpperCase()"
                               class="w-full rounded-xl border {{ $errors->has('ciudad_municipio') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('ciudad_municipio') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Zona *</label>
                        <input name="zona" value="{{ old('zona', optional($adultoObj)->zona) }}" placeholder="EJ. MIRAFLORES" oninput="this.value = this.value.toUpperCase()"
                               class="w-full rounded-xl border {{ $errors->has('zona') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('zona') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Calle / Avenida y Nro *</label>
                        <input name="calle" value="{{ old('calle', optional($adultoObj)->calle) }}" placeholder="EJ. AV. SAAVEDRA NRO 123" oninput="this.value = this.value.toUpperCase()"
                               class="w-full rounded-xl border {{ $errors->has('calle') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('calle') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            {{-- Paso 4: Ingreso Institucional --}}
            <section x-cloak x-show="paso === 4" x-transition.opacity.duration.250ms class="bg-transparent pt-4 border-t border-borde-suave">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
                        <i class="ph-fill ph-clipboard-text text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 4: Datos Institucionales</h2>
                        <p class="text-xs font-bold text-titulo/55">Detalles administrativos del ingreso.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Fecha de Ingreso *</label>
                        <input type="date" name="fecha_ing" value="{{ old('fecha_ing', optional($adultoObj)->fecha_ing) }}"
                               class="w-full rounded-xl border {{ $errors->has('fecha_ing') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('fecha_ing') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Hora Ingreso</label>
                        <input type="time" name="hora_ing" value="{{ old('hora_ing', optional($adultoObj)->hora_ing ? substr(optional($adultoObj)->hora_ing, 0, 5) : null) }}"
                               class="w-full rounded-xl border {{ $errors->has('hora_ing') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('hora_ing') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Tipo de Ingreso *</label>
                        <select name="tipo_ing"
                                class="w-full rounded-xl border {{ $errors->has('tipo_ing') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            <option value="REGULAR" @selected(old('tipo_ing', optional($adultoObj)->tipo_ing) === 'REGULAR')>Regular</option>
                            <option value="DERIVADO" @selected(old('tipo_ing', optional($adultoObj)->tipo_ing) === 'DERIVADO')>Derivado</option>
                            <option value="VOLUNTARIO" @selected(old('tipo_ing', optional($adultoObj)->tipo_ing) === 'VOLUNTARIO')>Voluntario</option>
                            <option value="EMERGENCIA" @selected(old('tipo_ing', optional($adultoObj)->tipo_ing) === 'EMERGENCIA')>Emergencia</option>
                            <option value="OTRO" @selected(old('tipo_ing', optional($adultoObj)->tipo_ing) === 'OTRO')>Otro</option>
                        </select>
                        @error('tipo_ing') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Permanencia *</label>
                        <select name="permanencia"
                                class="w-full rounded-xl border {{ $errors->has('permanencia') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            <option value="PERMANENTE" @selected(old('permanencia', optional($adultoObj)->permanencia) === 'PERMANENTE')>Permanente</option>
                            <option value="TEMPORAL" @selected(old('permanencia', optional($adultoObj)->permanencia) === 'TEMPORAL')>Temporal</option>
                            <option value="EVENTUAL" @selected(old('permanencia', optional($adultoObj)->permanencia) === 'EVENTUAL')>Eventual</option>
                        </select>
                        @error('permanencia') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Estado Institucional Inicial *</label>
                        <select name="cod_est_adul"
                                class="w-full rounded-xl border {{ $errors->has('cod_est_adul') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar estado</option>
                            @foreach($estadosAdulto ?? [] as $estado)
                                @php $estadoObj = is_object($estado) ? $estado : null; @endphp
                                @if($estadoObj)
                                    <option value="{{ $estadoObj->cod_est_adul }}" @selected(old('cod_est_adul', optional($adultoObj)->cod_est_adul) == $estadoObj->cod_est_adul)>
                                        {{ $estadoObj->estado }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('cod_est_adul') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            {{-- Paso 5: Información Complementaria --}}
            <section x-cloak x-show="paso === 5" x-transition.opacity.duration.250ms class="bg-transparent pt-4 border-t border-borde-suave">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
                        <i class="ph-fill ph-plugs text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 5: Información Complementaria</h2>
                        <p class="text-xs font-bold text-titulo/55">Fotografía, contacto de emergencia y datos administrativos requeridos.</p>
                    </div>
                </div>

                <div class="mb-6 flex flex-col items-center justify-center gap-4 sm:flex-row sm:justify-start">
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[20px] border-2 border-dashed border-borde-suave bg-fondo-panel text-titulo/30">
                        <template x-if="!fotoPreview">
                            <i class="ph-fill ph-image text-4xl"></i>
                        </template>
                        <template x-if="fotoPreview">
                            <img :src="fotoPreview" class="h-full w-full object-cover">
                        </template>
                    </div>
                    <div class="w-full sm:w-auto flex-1">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Actualizar Fotografía (Opcional)</label>
                        <input type="file" name="foto" accept="image/png,image/jpeg,image/jpg,image/webp" @change="previewImage"
                               class="block w-full text-sm font-bold text-titulo file:mr-4 file:rounded-xl file:border-0 file:bg-boton-principal file:px-4 file:py-2 file:text-xs file:font-black file:text-inverso file:transition hover:file:bg-boton-acento focus:outline-none">
                        <p class="mt-1 text-[10px] font-bold text-titulo/45">JPG, PNG o WEBP. Máximo 2MB. Si no selecciona una, se mantendrá la actual.</p>
                        @error('foto') <span class="text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Grupo Sanguíneo *</label>
                        <select name="grupo_sanguineo"
                                class="w-full rounded-xl border {{ $errors->has('grupo_sanguineo') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
                                <option value="{{ $gs }}" @selected(old('grupo_sanguineo', optional($adultoObj)->grupo_sanguineo) === $gs)>{{ $gs }}</option>
                            @endforeach
                        </select>
                        @error('grupo_sanguineo') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Seguro de Salud *</label>
                        <select name="seguro_salud"
                                class="w-full rounded-xl border {{ $errors->has('seguro_salud') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            @foreach(['SUS','CAJA NACIONAL CNS','CAJA PETROLERA','SEGURO PRIVADO','NINGUNO','OTRO'] as $seguro)
                                <option value="{{ $seguro }}" @selected(old('seguro_salud', optional($adultoObj)->seguro_salud) === $seguro)>{{ $seguro }}</option>
                            @endforeach
                        </select>
                        @error('seguro_salud') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Nivel Educativo *</label>
                        <select name="nivel_educat"
                                class="w-full rounded-xl border {{ $errors->has('nivel_educat') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            @foreach(['ANALFABETO','PRIMARIA','SECUNDARIA','TÉCNICO','UNIVERSITARIO','POSTGRADO','NO ESPECIFICADO'] as $nivel)
                                <option value="{{ $nivel }}" @selected(old('nivel_educat', optional($adultoObj)->nivel_educat) === $nivel)>{{ ucfirst(strtolower($nivel)) }}</option>
                            @endforeach
                        </select>
                        @error('nivel_educat') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="lg:col-span-3">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Alergias Conocidas *</label>
                        <input name="alergias" value="{{ old('alergias', optional($adultoObj)->alergias ?? 'Ninguna') }}" oninput="this.value = this.value.toUpperCase()"
                               class="w-full rounded-xl border {{ $errors->has('alergias') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('alergias') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="lg:col-span-3 my-2 border-t border-borde-suave"></div>

                    <div class="lg:col-span-3">
                        <h3 class="text-sm font-bold text-titulo mb-3">Contacto de Emergencia</h3>
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Nombre del Contacto *</label>
                        <input name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre', optional($adultoObj)->contacto_emergencia_nombre) }}" placeholder="NOMBRE COMPLETO" oninput="this.value = this.value.toUpperCase()"
                               class="w-full rounded-xl border {{ $errors->has('contacto_emergencia_nombre') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('contacto_emergencia_nombre') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Parentesco *</label>
                        <select name="contacto_emergencia_parentesco"
                                class="w-full rounded-xl border {{ $errors->has('contacto_emergencia_parentesco') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                            <option value="">Seleccionar</option>
                            @foreach(['HIJO/A','CÓNYUGE','NIETO/A','SOBRINO/A','HERMANO/A','TUTOR LEGAL','OTRO'] as $par)
                                <option value="{{ $par }}" @selected(old('contacto_emergencia_parentesco', optional($adultoObj)->contacto_emergencia_parentesco) === $par)>{{ ucfirst(strtolower($par)) }}</option>
                            @endforeach
                        </select>
                        @error('contacto_emergencia_parentesco') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Celular del Contacto *</label>
                        <input name="contacto_emergencia_celular" value="{{ old('contacto_emergencia_celular', optional($adultoObj)->contacto_emergencia_celular) }}" placeholder="Ej. 70098765"
                               class="w-full rounded-xl border {{ $errors->has('contacto_emergencia_celular') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        <p class="mt-1 text-[10px] font-bold text-titulo/45">Diferente al del adulto mayor.</p>
                        @error('contacto_emergencia_celular') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="lg:col-span-3">
                        <div class="flex justify-between">
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Dirección Contacto</label>
                            <span class="text-[10px] font-bold text-titulo/45" x-text="direccion_emergencia.length + '/200'"></span>
                        </div>
                        <input name="contacto_emergencia_direccion" value="{{ old('contacto_emergencia_direccion', optional($adultoObj)->contacto_emergencia_direccion) }}" placeholder="DIRECCIÓN DEL CONTACTO..." x-model="direccion_emergencia" maxlength="200" oninput="this.value = this.value.toUpperCase()"
                                  class="w-full rounded-xl border {{ $errors->has('contacto_emergencia_direccion') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10">
                        @error('contacto_emergencia_direccion') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>

                    <div class="lg:col-span-3 flex flex-col sm:flex-row gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="responsable_principal" value="1" @checked(old('responsable_principal', optional($adultoObj)->responsable_principal))
                                   class="h-5 w-5 rounded border-borde-suave bg-fondo-app text-terracota focus:ring-borde-focus/30">
                            <span class="text-sm font-bold text-titulo">Responsable Principal (Firma)</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="autorizado_informacion_medica" value="1" @checked(old('autorizado_informacion_medica', optional($adultoObj)->autorizado_informacion_medica))
                                   class="h-5 w-5 rounded border-borde-suave bg-fondo-app text-terracota focus:ring-borde-focus/30">
                            <span class="text-sm font-bold text-titulo">Autorizado Inf. Médica</span>
                        </label>
                    </div>

                    <div class="lg:col-span-3 my-2 border-t border-borde-suave"></div>

                    <div class="lg:col-span-3">
                        <div class="flex justify-between">
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-titulo/55">Observaciones Generales</label>
                            <span class="text-[10px] font-bold text-titulo/45" x-text="observaciones.length + '/1500'"></span>
                        </div>
                        <textarea name="observaciones" rows="2" x-model="observaciones" maxlength="1500" placeholder="Anotaciones de ingreso..." oninput="this.value = this.value.toUpperCase()"
                                  class="w-full resize-none rounded-xl border {{ $errors->has('observaciones') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-borde-focus/10"></textarea>
                        @error('observaciones') <span class="mt-1 text-xs font-bold text-terracota">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            {{-- Paso 6: Confirmación --}}
            <section x-cloak x-show="paso === 6" x-transition.opacity.duration.250ms class="bg-transparent pt-4 border-t border-borde-suave">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
                        <i class="ph-fill ph-check-circle text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Paso 6: Confirmación de Actualización</h2>
                        <p class="text-xs font-bold text-titulo/55">Revise que todos los datos modificados sean correctos.</p>
                    </div>
                </div>

                <div class="rm-card-soft p-4 mb-5">
                    <h3 class="text-sm font-bold text-titulo mb-2">Resumen</h3>
                    <ul class="text-xs font-bold text-titulo/70 space-y-1">
                        <li><i class="ph-bold ph-caret-right text-terracota mr-1"></i> La actualización quedará registrada en el sistema.</li>
                        <li><i class="ph-bold ph-caret-right text-terracota mr-1"></i> Los campos médicos y de estado mantendrán su historial íntegro.</li>
                    </ul>
                </div>
                
                @if(!optional($adultoObj)->consentimiento_datos)
                <div class="rounded-xl border {{ $errors->has('consentimiento_datos') ? 'border-terracota bg-boton-acento/5' : 'border-borde-suave bg-fondo-card/50' }} p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <div class="mt-1 flex items-center h-5">
                            <input type="checkbox" name="consentimiento_datos" value="1" @checked(old('consentimiento_datos'))
                                   class="h-5 w-5 rounded border-borde-suave text-terracota focus:ring-borde-focus/30">
                        </div>
                        <div>
                            <span class="text-sm font-bold text-titulo">Acepto la política de tratamiento de datos *</span>
                            <p class="text-xs font-bold text-titulo/60 mt-0.5">Parece que el consentimiento aún no estaba firmado en el sistema.</p>
                        </div>
                    </label>
                    @error('consentimiento_datos') <span class="mt-2 block text-xs font-bold text-terracota"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</span> @enderror
                </div>
                @endif
            </section>

            {{-- Botones de Navegación --}}
            <div x-show="erroresPaso.length > 0" x-transition class="rounded-xl border border-terracota/30 bg-boton-acento/10 p-4 text-sm text-terracota">
                <p class="mb-2 font-black flex items-center gap-1">
                    <i class="ph-bold ph-warning-circle"></i>
                    Corrija los siguientes errores antes de continuar:
                </p>
                <ul class="list-inside list-disc space-y-1 text-xs font-bold">
                    <template x-for="error in erroresPaso" :key="error">
                        <li x-text="error"></li>
                    </template>
                </ul>
            </div>

            <section class="rm-surface-glass p-4 mt-6">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" @click="anterior()" x-show="paso > 1"
                            class="rm-btn-secondary">
                        <i class="ph-bold ph-arrow-left"></i> Atrás
                    </button>
                    
                    <a href="{{ route('admin.adultos-mayores.show', $idAdulto) }}" x-show="paso === 1"
                       class="rm-btn-secondary">
                        Cancelar
                    </a>

                    <div class="flex justify-end w-full sm:w-auto">
                        <button type="button" x-show="paso < total" @click="siguiente()"
                                class="rm-btn-primary">
                            Siguiente <i class="ph-bold ph-arrow-right"></i>
                        </button>

                        <button type="submit" x-show="paso === total"
                                class="rm-btn-accent">
                            <i class="ph-bold ph-floppy-disk"></i> Actualizar Ficha
                        </button>
                    </div>
                </div>
            </section>

        </form>
    </div>

    <script>
        function adultoMayorFormEdit() {
            return {
                paso: 1,
                total: 6,
                errors: {},
                touched: {},
                edad: null,
                fotoPreview: @js($fotoUrl),
                tiene_celular: @js(old('tiene_celular', optional($adultoObj)->tiene_celular ?? true)),
                fecha_nac: @js(old('fecha_nac', optional($adultoObj)->fecha_nac ?? '')),
                observaciones: @js(old('observaciones', optional($adultoObj)->observaciones ?? '')),
                direccion_emergencia: @js(old('contacto_emergencia_direccion', optional($adultoObj)->contacto_emergencia_direccion ?? '')),
                erroresPaso: [],

                init() {
                    @foreach($errors->keys() as $key)
                        this.errors['{{ $key }}'] = @js($errors->first($key));
                    @endforeach
                    this.$watch('fecha_nac', () => this.calcularEdad());
                    if (this.fecha_nac) this.calcularEdad();
                },

                calcularEdad() {
                    if (!this.fecha_nac) { this.edad = null; return; }
                    const hoy = new Date(); const nac = new Date(this.fecha_nac);
                    let e = hoy.getFullYear() - nac.getFullYear();
                    const m = hoy.getMonth() - nac.getMonth();
                    if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) e--;
                    this.edad = e >= 0 ? e : 0;
                },

                previewImage(event) {
                    const file = event.target.files[0];
                    if (!file) { this.fotoPreview = @js($fotoUrl); return; }
                    if (file.size > 2 * 1024 * 1024) alert('La imagen supera los 2MB.');
                    this.fotoPreview = URL.createObjectURL(file);
                },

                setError(f, m) { this.errors = { ...this.errors, [f]: m }; this.erroresPaso.push(m); },
                clearError(f) { if (this.errors[f]) { const e = {...this.errors}; delete e[f]; this.errors = e; } },
                hasError(f) { return Boolean(this.errors[f]); },
                touch(f) { this.touched = { ...this.touched, [f]: true }; },

                fieldClass(f) {
                    if (this.hasError(f)) return 'border-borde-focus bg-estado-peligroBg ring-4 ring-[#E27D60]/10';
                    if (this.touched[f]) return 'border-borde bg-fondo-panel';
                    return 'border-borde-suave bg-fondo-panel';
                },

                isEmpty(v) { return v === null || v === undefined || String(v).trim() === ''; },
                onlyLetters(v) { return /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'\-]+$/.test(String(v).trim()); },
                isCI(v) { return /^[0-9]{5,9}$/.test(String(v).trim()); },
                isPhone(v) { return /^[67][0-9]{7}$/.test(String(v).trim()); },
                isFixedPhone(v) { if (this.isEmpty(v)) return true; return /^[2-4][0-9]{6,7}$/.test(String(v).trim()); },
                isBeforeEqual60(v) { 
                    if (this.isEmpty(v)) return false; 
                    const nac = new Date(v);
                    const limite = new Date();
                    limite.setFullYear(limite.getFullYear() - 60);
                    nac.setHours(0,0,0,0); limite.setHours(0,0,0,0); 
                    return nac <= limite; 
                },
                isMaxToday(v) { if (this.isEmpty(v)) return false; const d=new Date(v),t=new Date(); d.setHours(0,0,0,0); t.setHours(0,0,0,0); return d <= t; },

                getValue(f) {
                    const el = document.querySelector('[name="' + f + '"]');
                    if (!el) return '';
                    return el.type === 'checkbox' ? (el.checked ? '1' : '') : el.value;
                },

                focusFirstError() {
                    const f = Object.keys(this.errors)[0];
                    if (!f) return;
                    const el = document.querySelector('[name="' + f + '"]');
                    if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(() => el.focus(), 250); }
                },

                validarPasoPersonales() {
                    const n = this.getValue('nombres'), ap = this.getValue('ap_paterno'),
                          am = this.getValue('ap_materno'), ec = this.getValue('estado_civil'),
                          g = this.getValue('genero'), fn = this.getValue('fecha_nac');
                    if (this.isEmpty(n)) this.setError('nombres', 'Ingrese los nombres.');
                    if (this.isEmpty(ap) && this.isEmpty(am)) this.setError('ap_paterno', 'Ingrese al menos un apellido.');
                    if (this.isEmpty(fn)) this.setError('fecha_nac', 'Ingrese la fecha de nacimiento.');
                    else if (!this.isBeforeEqual60(fn)) this.setError('fecha_nac', 'El adulto mayor debe tener al menos 60 años cumplidos.');
                    if (this.isEmpty(g)) this.setError('genero', 'Seleccione el género.');
                    if (this.isEmpty(ec)) this.setError('estado_civil', 'Seleccione el estado civil.');
                },

                validarPasoIdentificacion() {
                    const ci = this.getValue('ci'), exp = this.getValue('expedicion_ci');
                    if (this.isEmpty(ci)) this.setError('ci', 'Ingrese el número de CI.');
                    else if (!this.isCI(ci)) this.setError('ci', 'El CI debe tener entre 5 y 9 dígitos numéricos.');
                    if (this.isEmpty(exp)) this.setError('expedicion_ci', 'Seleccione la expedición del CI.');
                },

                validarPasoContacto() {
                    const cel = this.getValue('celular'), fijo = this.getValue('telefono_fijo'),
                          dep = this.getValue('departamento_residencia'), ciu = this.getValue('ciudad_municipio'),
                          zon = this.getValue('zona'), cal = this.getValue('calle');
                    if (this.tiene_celular) {
                        if (this.isEmpty(cel)) this.setError('celular', 'Ingrese un celular válido.');
                        else if (!this.isPhone(cel)) this.setError('celular', 'El celular debe tener 8 dígitos y comenzar con 6 o 7.');
                    }
                    if (!this.isFixedPhone(fijo)) this.setError('telefono_fijo', 'Ingrese un teléfono fijo válido.');
                    if (this.isEmpty(dep)) this.setError('departamento_residencia', 'Seleccione el departamento.');
                    if (this.isEmpty(ciu)) this.setError('ciudad_municipio', 'Ingrese la ciudad/municipio.');
                    if (this.isEmpty(zon)) this.setError('zona', 'Ingrese la zona.');
                    if (this.isEmpty(cal)) this.setError('calle', 'Ingrese la calle/avenida.');
                },

                validarPasoInstitucionales() {
                    const fi = this.getValue('fecha_ing'), ti = this.getValue('tipo_ing'),
                          pe = this.getValue('permanencia'), es = this.getValue('cod_est_adul');
                    if (this.isEmpty(fi)) this.setError('fecha_ing', 'Ingrese la fecha de ingreso.');
                    else if (!this.isMaxToday(fi)) this.setError('fecha_ing', 'La fecha de ingreso no puede ser futura.');
                    if (this.isEmpty(ti)) this.setError('tipo_ing', 'Seleccione el tipo de ingreso.');
                    if (this.isEmpty(pe)) this.setError('permanencia', 'Seleccione la permanencia.');
                    if (this.isEmpty(es)) this.setError('cod_est_adul', 'Seleccione el estado institucional.');
                },

                validarPasoComplementaria() {
                    const ss = this.getValue('seguro_salud'), gs = this.getValue('grupo_sanguineo'),
                          ne = this.getValue('nivel_educat'),
                          nom = this.getValue('contacto_emergencia_nombre'),
                          par = this.getValue('contacto_emergencia_parentesco'),
                          celC = this.getValue('contacto_emergencia_celular'), celA = this.getValue('celular');
                    if (this.isEmpty(gs)) this.setError('grupo_sanguineo', 'Seleccione el grupo sanguíneo.');
                    if (this.isEmpty(ss)) this.setError('seguro_salud', 'Seleccione el seguro de salud.');
                    if (this.isEmpty(ne)) this.setError('nivel_educat', 'Seleccione el nivel educativo.');
                    if (this.isEmpty(nom)) this.setError('contacto_emergencia_nombre', 'Ingrese el contacto de emergencia.');
                    if (this.isEmpty(par)) this.setError('contacto_emergencia_parentesco', 'Seleccione el parentesco.');
                    if (this.isEmpty(celC)) this.setError('contacto_emergencia_celular', 'Ingrese el celular del contacto.');
                    else if (!this.isPhone(celC)) this.setError('contacto_emergencia_celular', 'El celular debe tener 8 dígitos (6 o 7).');
                    else if (!this.isEmpty(celA) && celC === celA) this.setError('contacto_emergencia_celular', 'El celular no puede ser el mismo del adulto.');
                },

                validarPasoCierre() {
                    if (this.getValue('consentimiento_datos') !== '1') {
                        // En edit, solo validamos si existe el checkbox
                        const cb = document.querySelector('[name="consentimiento_datos"]');
                        if(cb) {
                            this.setError('consentimiento_datos', 'Debe aceptar el consentimiento.');
                        }
                    }
                },

                validarPaso() {
                    this.errors = {};
                    this.erroresPaso = [];
                    if (this.paso === 1) this.validarPasoPersonales();
                    if (this.paso === 2) this.validarPasoIdentificacion();
                    if (this.paso === 3) this.validarPasoContacto();
                    if (this.paso === 4) this.validarPasoInstitucionales();
                    if (this.paso === 5) this.validarPasoComplementaria();
                    if (this.paso === 6) this.validarPasoCierre();
                    if (Object.keys(this.errors).length > 0) { this.focusFirstError(); return false; }
                    return true;
                },

                siguiente() {
                    if (!this.validarPaso()) return;
                    if (this.paso < this.total) { this.paso++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
                },

                anterior() {
                    if (this.paso > 1) { this.errors = {}; this.erroresPaso = []; this.paso--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
                }
            };
        }
    </script>
</x-sistema-layout>