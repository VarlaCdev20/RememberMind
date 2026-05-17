<x-app-layout>
    <div class="relative min-h-screen overflow-hidden bg-[#D5C7B9] px-4 py-5 font-outfit text-azul-profundo sm:px-6" x-data="userRegistration()">
        
        {{-- Script SweetAlert2 --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        {{-- Fondo con trama institucional (Coherencia con Adulto Mayor) --}}
        <div class="pointer-events-none fixed inset-0 opacity-[0.045] z-0"
             style="background-image: radial-gradient(#2F3E5C 1.2px, transparent 1.2px); background-size: 28px 28px;">
        </div>

        <main class="relative z-10 mx-auto max-w-5xl space-y-6">
            
            {{-- HEADER INSTITUCIONAL --}}
            <header class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-6 shadow-[0_14px_32px_rgba(47,62,92,0.12)] backdrop-blur-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-terracota">
                            <a href="{{ route('admin.usuarios.index') }}" class="transition hover:text-azul-profundo">Usuarios</a>
                            <i class="ph-bold ph-caret-right text-[8px]"></i>
                            <span>Registro Institucional</span>
                        </nav>
                        <h1 class="text-2xl font-black text-azul-profundo">Registro de <span class="text-terracota">Personal</span></h1>
                        <p class="mt-1 text-sm font-bold text-azul-profundo/60">Asistente de registro institucional para personal administrativo y de salud.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        {{-- Indicador de completitud --}}
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] font-black uppercase tracking-tighter text-azul-profundo/40">Completitud</span>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-terracota" x-text="completionPercentage + '%'"></span>
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-[#C7B5A3]/40">
                                    <div class="h-full bg-terracota transition-all duration-500" :style="'width: ' + completionPercentage + '%'"></div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.usuarios.index') }}" 
                           class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#D5C7B9] text-azul-profundo transition hover:bg-azul-profundo hover:text-white active:scale-95">
                            <i class="ph-bold ph-arrow-left"></i>
                        </a>
                    </div>
                </div>

                {{-- BARRA DE PROGRESO DE PASOS --}}
                <div class="mt-8">
                    <div class="flex items-center justify-between px-2">
                        <template x-for="n in 6" :key="n">
                            <div class="flex items-center" :class="n < 6 ? 'flex-1' : ''">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 transition-all duration-300"
                                     :class="step === n ? 'border-terracota bg-terracota text-white shadow-lg shadow-terracota/20' : 
                                            (step > n ? 'border-[#8DA280] bg-[#8DA280] text-white' : 'border-[#C7B5A3] bg-white/50 text-[#C7B5A3]')">
                                    <span class="text-xs font-black" x-text="n"></span>
                                </div>
                                <div x-show="n < 6" class="h-1 flex-1 mx-2 rounded-full transition-all duration-500"
                                     :class="step > n ? 'bg-[#8DA280]' : 'bg-[#C7B5A3]/30'"></div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-2 flex justify-between px-1 text-[9px] font-black uppercase tracking-widest text-azul-profundo/40">
                        <span :class="step === 1 ? 'text-terracota' : ''">Identidad</span>
                        <span :class="step === 2 ? 'text-terracota' : ''">Documento</span>
                        <span :class="step === 3 ? 'text-terracota' : ''">Contacto</span>
                        <span :class="step === 4 ? 'text-terracota' : ''">Rol</span>
                        <span :class="step === 5 ? 'text-terracota' : ''">Seguridad</span>
                        <span :class="step === 6 ? 'text-terracota' : ''">Confirmar</span>
                    </div>
                </div>
            </header>

            <form action="{{ route('admin.usuarios.store') }}" method="POST" enctype="multipart/form-data" id="registrationForm" @submit.prevent="confirmSubmit()">
                @csrf
                
                {{-- Alertas Globales --}}
                @if(session('error'))
                    <div class="mb-6 rounded-2xl border-2 border-red-200 bg-red-50 p-4 shadow-sm animate-pulse">
                        <div class="flex items-center gap-3 text-red-700">
                            <i class="ph-bold ph-warning-octagon text-2xl"></i>
                            <p class="text-xs font-black uppercase tracking-wide">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                {{-- PASO 1: IDENTIDAD PERSONAL Y FOTO --}}
                <section x-show="step === 1" x-transition.opacity.duration.400ms class="grid gap-6 lg:grid-cols-3">
                    {{-- Mini-Ficha Preview --}}
                    <div class="lg:col-span-1">
                        <div class="sticky top-6 rounded-3xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-6 shadow-sm backdrop-blur-xl">
                            <div class="flex flex-col items-center text-center">
                                <div class="relative mb-4">
                                    <template x-if="!fotoPreview">
                                        <div class="flex h-32 w-32 items-center justify-center rounded-[2.5rem] bg-azul-profundo text-5xl font-black text-white shadow-xl">
                                            <span x-text="initials()"></span>
                                        </div>
                                    </template>
                                    <template x-if="fotoPreview">
                                        <img :src="fotoPreview" class="h-32 w-32 rounded-[2.5rem] object-cover border-4 border-white shadow-xl">
                                    </template>
                                    <label class="absolute -bottom-2 -right-2 flex h-10 w-10 cursor-pointer items-center justify-center rounded-2xl bg-terracota text-white shadow-lg transition hover:scale-110 active:scale-95">
                                        <i class="ph-bold ph-camera"></i>
                                        <input type="file" name="foto_perfil" class="hidden" accept=".jpg,.jpeg,.png,.webp" @change="handleFotoChange">
                                    </label>
                                </div>
                                <h3 class="text-lg font-black leading-tight text-azul-profundo" x-text="fullName() || 'Nombre del Usuario'"></h3>
                                <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-terracota" x-text="rolDisplay()"></p>
                                
                                <div class="mt-6 w-full space-y-3 border-t border-[#C7B5A3]/40 pt-6">
                                    <div class="flex justify-between text-[10px] font-bold">
                                        <span class="text-azul-profundo/40 uppercase">Género</span>
                                        <span class="text-azul-profundo font-black" x-text="genero || '---'"></span>
                                    </div>
                                    <div class="flex justify-between text-[10px] font-bold">
                                        <span class="text-azul-profundo/40 uppercase">Documento</span>
                                        <span class="text-azul-profundo font-black" x-text="documento || '---'"></span>
                                    </div>
                                </div>

                                {{-- Error de foto --}}
                                <div x-show="errors.foto" class="mt-4 rounded-xl bg-red-50 p-2 text-[10px] font-bold text-red-500 border border-red-200">
                                    <i class="ph-bold ph-warning-circle mr-1"></i>
                                    <span x-text="errors.foto"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario Identidad --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-3xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-8 shadow-sm">
                            <div class="mb-6 flex items-center gap-3">
                                <i class="ph-fill ph-user-circle text-2xl text-terracota"></i>
                                <h2 class="text-lg font-black text-azul-profundo">Datos de Identidad</h2>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Nombre Completo *</label>
                                    <input type="text" name="nombres" x-model="nombres" @input="clearError('nombres')"
                                           class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold uppercase transition focus:border-terracota focus:ring-4 focus:ring-terracota/10 outline-none"
                                           placeholder="Ej. Carla Valeria" :class="{'border-red-400 bg-red-50/50 ring-red-400/20': errors.nombres}">
                                    <p x-show="errors.nombres" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.nombres"></p>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Apellido Paterno</label>
                                    <input type="text" name="ap_paterno" x-model="apPaterno" @input="clearError('apellidos')"
                                           class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold uppercase transition focus:border-terracota outline-none"
                                           placeholder="Ej. Encinas" :class="{'border-red-400 bg-red-50/50': errors.apellidos}">
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Apellido Materno</label>
                                    <input type="text" name="ap_materno" x-model="apMaterno" @input="clearError('apellidos')"
                                           class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold uppercase transition focus:border-terracota outline-none"
                                           placeholder="Ej. Cano" :class="{'border-red-400 bg-red-50/50': errors.apellidos}">
                                </div>

                                <div class="md:col-span-2" x-show="errors.apellidos">
                                    <p class="text-[10px] font-bold text-red-500" x-text="errors.apellidos"></p>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Fecha Nacimiento *</label>
                                    <input type="date" name="fecha_nacimiento" x-model="fechaNac" @input="clearError('fecha_nacimiento')"
                                           class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                           :class="{'border-red-400 bg-red-50/50': errors.fecha_nacimiento}">
                                    <p x-show="errors.fecha_nacimiento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.fecha_nacimiento"></p>
                                    <p class="mt-1 text-[9px] font-bold text-azul-profundo/40 italic">Mínimo 18 años, máximo 100 años.</p>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Edad Calculada</label>
                                    <div class="flex items-center gap-3 w-full rounded-2xl border border-[#C7B5A3] bg-[#D5C7B9]/20 px-5 py-3 text-sm font-black text-azul-profundo/70 shadow-inner">
                                        <i class="ph-bold ph-calendar text-terracota"></i>
                                        <span x-text="calculateAgeText()"></span>
                                    </div>
                                    <p class="mt-1.5 text-[9px] font-bold text-azul-profundo/40 italic">La edad se autogenera desde la fecha de nacimiento.</p>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Sexo *</label>
                                    <select name="genero" x-model="genero" @change="clearError('genero')"
                                            class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                            :class="{'border-red-400 bg-red-50/50': errors.genero}">
                                        <option value="">SELECCIONE...</option>
                                        <option value="FEMENINO">FEMENINO</option>
                                        <option value="MASCULINO">MASCULINO</option>
                                        <option value="OTRO">OTRO</option>
                                        <option value="PREFIERE NO ESPECIFICAR">PREFIERE NO ESPECIFICAR</option>
                                    </select>
                                    <p x-show="errors.genero" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.genero"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- PASO 2: DOCUMENTACIÓN --}}
                <section x-show="step === 2" x-transition.opacity.duration.400ms class="space-y-6">
                    <div class="rounded-3xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-8 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <i class="ph-fill ph-identification-card text-2xl text-terracota"></i>
                            <h2 class="text-lg font-black text-azul-profundo">Documentación Oficial</h2>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">País Emisor *</label>
                                <select name="pais_documento" x-model="paisDoc" @change="clearError('pais_documento')"
                                        class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                        :class="{'border-red-400 bg-red-50/50': errors.pais_documento}">
                                    <template x-for="(tipos, pais) in paisesDoc" :key="pais">
                                        <option :value="pais" x-text="pais"></option>
                                    </template>
                                </select>
                                <p x-show="errors.pais_documento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.pais_documento"></p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Tipo Documento *</label>
                                <select name="tipo_documento" x-model="tipoDoc" @change="clearError('tipo_documento')"
                                        class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                        :class="{'border-red-400 bg-red-50/50': errors.tipo_documento}">
                                    <template x-for="tipo in paisesDoc[paisDoc]" :key="tipo">
                                        <option :value="tipo" x-text="tipo"></option>
                                    </template>
                                </select>
                                <p x-show="errors.tipo_documento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.tipo_documento"></p>
                            </div>

                             <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">N° Documento *</label>
                                <input type="text" name="numero_documento" x-model="documento" @input="clearError('numero_documento')"
                                       class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-terracota"
                                       :placeholder="documentoPlaceholder()" :class="{'border-red-400 bg-red-50/50': errors.numero_documento}">
                                <p x-show="errors.numero_documento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.numero_documento"></p>
                            </div>

                            <div x-show="paisDoc === 'Bolivia' && tipoDoc === 'CI'">
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Expedido *</label>
                                <select name="expedido" x-model="expedido" @change="clearError('expedido')"
                                        class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                        :class="{'border-red-400 bg-red-50/50': errors.expedido}">
                                    <option value="">SELECCIONE...</option>
                                    @foreach(['LP', 'CBBA', 'SCZ', 'OR', 'PT', 'CH', 'TJ', 'BN', 'PD'] as $exp)
                                        <option value="{{ $exp }}">{{ $exp }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.expedido" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.expedido"></p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- PASO 3: CONTACTO --}}
                <section x-show="step === 3" x-transition.opacity.duration.400ms class="space-y-6">
                    <div class="rounded-3xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-8 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <i class="ph-fill ph-envelope-simple-open text-2xl text-terracota"></i>
                            <h2 class="text-lg font-black text-azul-profundo">Canales de Contacto</h2>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Correo Electrónico *</label>
                                <input type="email" name="correo" x-model="correo" @input="clearError('correo'); correo = correo.toLowerCase();"
                                       class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold lowercase outline-none transition focus:border-terracota"
                                       placeholder="ejemplo@casaamandita.com" :class="{'border-red-400 bg-red-50/50': errors.correo}">
                                <p x-show="errors.correo" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.correo"></p>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-1">
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">País</label>
                                    <select name="pais_telefono" x-model="paisTel" @change="clearError('telefono')"
                                            class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-2 py-3 text-xs font-black outline-none transition focus:border-terracota">
                                        <template x-for="(cod, pais) in codigosTel" :key="pais">
                                            <option :value="pais" x-text="pais"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Número de Celular *</label>
                                    <div class="flex items-center">
                                        <input type="text" name="codigo_telefono" :value="codigosTel[paisTel]" readonly
                                               class="w-16 rounded-l-2xl border-y border-l border-[#C7B5A3] bg-[#D5C7B9]/40 py-3 text-center text-xs font-black text-azul-profundo/60 outline-none">
                                        <input type="text" name="telefono" x-model="telefono" @input="clearError('telefono')"
                                               class="w-full rounded-r-2xl border border-[#C7B5A3] bg-white/50 px-4 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                               placeholder="70012345" :class="{'border-red-400 bg-red-50/50': errors.telefono}">
                                    </div>
                                    <p x-show="errors.telefono" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.telefono"></p>
                                    <p class="mt-1 text-[9px] font-bold text-azul-profundo/40 italic" x-text="'Ej. ' + paisTel + ': ' + telefonoEjemplo()"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- PASO 4: ROL E INSTITUCIONAL --}}
                <section x-show="step === 4" x-transition.opacity.duration.400ms class="space-y-6">
                    <div class="rounded-3xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-8 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <i class="ph-fill ph-briefcase text-2xl text-terracota"></i>
                            <h2 class="text-lg font-black text-azul-profundo">Perfil Institucional</h2>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Rol Institucional *</label>
                                <select name="rol" x-model="rol" @change="clearError('rol')"
                                        class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                        :class="{'border-red-400 bg-red-50/50': errors.rol}">
                                    <option value="">SELECCIONE ROL...</option>
                                    @foreach($roles as $r)
                                        @php
                                            $displayName = match($r->name) {
                                                'personal_salud' => 'PERSONAL DE SALUD',
                                                'personal_admin' => 'PERSONAL ADMINISTRATIVO',
                                                'familiar' => 'FAMILIAR / RESPONSABLE',
                                                'voluntario' => 'VOLUNTARIO',
                                                default => strtoupper(str_replace('_', ' ', $r->name))
                                            };
                                        @endphp
                                        <option value="{{ $r->name }}">{{ $displayName }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.rol" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.rol"></p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Fecha Ingreso</label>
                                <input type="date" name="fecha_ingreso" x-model="fechaIng"
                                       class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota">
                            </div>

                            <div x-show="rol === 'personal_salud'" x-transition>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Especialidad Médica *</label>
                                <select name="especialidad_salud" x-model="especialidad" @change="clearError('especialidad')"
                                        class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                        :class="{'border-red-400 bg-red-50/50': errors.especialidad}">
                                    <option value="">SELECCIONE...</option>
                                    @foreach($especialidades as $esp)
                                        <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.especialidad" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.especialidad"></p>
                            </div>

                            <div x-show="rol === 'personal_admin'" x-transition>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Función Administrativa *</label>
                                <select name="cargo_administrativo" x-model="cargo" @change="clearError('cargo_administrativo')"
                                        class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-terracota"
                                        :class="{'border-red-400 bg-red-50/50': errors.cargo_administrativo}">
                                    <option value="">SELECCIONE...</option>
                                    @foreach($cargosAdmin as $c)
                                        <option value="{{ $c->cod_cargo_admin }}">{{ $c->nombre }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.cargo_administrativo" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.cargo_administrativo"></p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- PASO 5: SEGURIDAD Y ACCESO --}}
                <section x-show="step === 5" x-transition.opacity.duration.400ms class="space-y-6">
                    <div class="rounded-3xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-8 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <i class="ph-fill ph-shield-check text-2xl text-terracota"></i>
                            <h2 class="text-lg font-black text-azul-profundo">Seguridad y Acceso</h2>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Estado Perfil Inicial</label>
                                <div class="flex items-center gap-2 rounded-2xl border border-[#C7B5A3] bg-[#D5C7B9]/20 px-5 py-3 text-sm font-black text-[#63775B] shadow-inner">
                                    <i class="ph-bold ph-check-circle"></i>
                                    <span>ACTIVO</span>
                                    <input type="hidden" name="estado" value="ACTIVO">
                                </div>
                                <p class="mt-1.5 text-[9px] font-bold text-azul-profundo/40 italic">Todo nuevo registro institucional inicia en estado ACTIVO.</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Acceso Sistema Inicial</label>
                                <div class="flex items-center gap-2 rounded-2xl border border-[#C7B5A3] bg-[#D5C7B9]/20 px-5 py-3 text-sm font-black text-azul-profundo shadow-inner">
                                    <i class="ph-bold ph-lock-key-open text-terracota"></i>
                                    <span>HABILITADO</span>
                                    <input type="hidden" name="acceso_sistema" value="HABILITADO">
                                </div>
                                <p x-show="errors.acceso_sistema" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.acceso_sistema"></p>
                            </div>

                            <div class="md:col-span-2 rounded-2xl border border-[#C7B5A3]/50 bg-azul-profundo/5 p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Contraseña Inicial Autogenerada</span>
                                    <span class="rounded-full bg-terracota/10 px-3 py-1 text-[9px] font-black text-terracota">BASADA EN IDENTIDAD</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex-1 rounded-xl bg-white px-5 py-4 text-center">
                                        <span class="text-2xl font-black tracking-[0.3em] text-terracota" x-text="passwordPreview()"></span>
                                    </div>
                                    <div class="h-14 w-14 flex items-center justify-center rounded-xl bg-azul-profundo text-white shadow-lg">
                                        <i class="ph-bold ph-lock-key text-2xl"></i>
                                    </div>
                                </div>
                                <p class="mt-4 text-[10px] font-bold text-azul-profundo/50 leading-relaxed italic">
                                    <i class="ph-bold ph-info mr-1"></i>
                                    Indique al usuario que deberá cambiar esta contraseña tras su primer ingreso exitoso. No se almacena en bitácoras.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- PASO 6: RESUMEN Y FINALIZAR --}}
                <section x-show="step === 6" x-transition.opacity.duration.400ms class="space-y-6">
                    <div class="rounded-3xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-8 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <i class="ph-fill ph-check-square text-2xl text-terracota"></i>
                            <h2 class="text-lg font-black text-azul-profundo">Confirmación de Registro</h2>
                        </div>

                        <div class="grid gap-8 lg:grid-cols-2">
                            {{-- Resumen visual --}}
                            <div class="rounded-2xl bg-white/40 p-6 border border-[#C7B5A3]/40">
                                <h3 class="mb-4 text-[11px] font-black uppercase tracking-widest text-azul-profundo/60 border-b border-[#C7B5A3]/20 pb-2">Resumen de Ficha</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-16 w-16 overflow-hidden rounded-2xl bg-azul-profundo flex items-center justify-center text-white">
                                            <template x-if="!fotoPreview"><span class="text-xl font-black" x-text="initials()"></span></template>
                                            <template x-if="fotoPreview"><img :src="fotoPreview" class="h-full w-full object-cover"></template>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-azul-profundo" x-text="fullName()"></p>
                                            <p class="text-[10px] font-bold text-terracota" x-text="rolDisplay()"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 pt-2">
                                        <div>
                                            <p class="text-[9px] font-black uppercase text-azul-profundo/40">N° Documento</p>
                                            <p class="text-xs font-black text-azul-profundo" x-text="documento"></p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-black uppercase text-azul-profundo/40">Correo</p>
                                            <p class="text-xs font-black text-azul-profundo truncate" x-text="correo"></p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-black uppercase text-azul-profundo/40">Teléfono</p>
                                            <p class="text-xs font-black text-azul-profundo" x-text="telefono ? codigosTel[paisTel] + ' ' + telefono : '---'"></p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-black uppercase text-azul-profundo/40">Completitud</p>
                                            <p class="text-xs font-black text-azul-profundo" x-text="completionPercentage + '%'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notas finales --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Observaciones Administrativas</label>
                                    <textarea name="observaciones" rows="4" x-model="observaciones"
                                              class="w-full rounded-2xl border border-[#C7B5A3] bg-white/50 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-terracota"
                                              placeholder="NOTAS ADICIONALES..."></textarea>
                                </div>
                                <div class="rounded-xl bg-terracota/10 p-4 border border-terracota/20">
                                    <p class="text-[10px] font-bold text-terracota text-center leading-relaxed">
                                        Al presionar "Registrar Usuario" se crearán las credenciales y el acceso institucional.
                                        Revise que toda la información sea correcta.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- NAVEGACIÓN DE FORMULARIO --}}
                <div class="mt-8 flex flex-col-reverse gap-4 sm:flex-row sm:justify-between">
                    <button type="button" @click="prevStep()" x-show="step > 1"
                            class="flex items-center justify-center gap-2 rounded-full border-2 border-azul-profundo px-10 py-3 text-[10px] font-black uppercase tracking-widest text-azul-profundo transition hover:bg-azul-profundo hover:text-white active:scale-95">
                        <i class="ph-bold ph-arrow-left"></i> Anterior
                    </button>
                    <div x-show="step === 1" class="w-full sm:w-auto"></div> {{-- Espaciador --}}
                    
                    <div class="flex gap-4">
                        <button type="button" x-show="step < 6" @click="nextStep()"
                                class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-full bg-azul-profundo px-12 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-xl transition hover:bg-terracota active:scale-95">
                            Siguiente Paso <i class="ph-bold ph-arrow-right"></i>
                        </button>
                        <button type="submit" x-show="step === 6" :disabled="isSubmitting"
                                class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-full bg-terracota px-14 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-xl transition hover:bg-azul-profundo active:scale-95 disabled:opacity-50">
                            <i x-show="!isSubmitting" class="ph-bold ph-check-circle"></i>
                            <i x-show="isSubmitting" class="ph-bold ph-circle-notch animate-spin"></i>
                            <span x-text="isSubmitting ? 'REGISTRANDO...' : 'REGISTRAR USUARIO'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        function userRegistration() {
            return {
                step: 1,
                isSubmitting: false,
                nombres: @js(old('nombres', '')),
                apPaterno: @js(old('ap_paterno', '')),
                apMaterno: @js(old('ap_materno', '')),
                genero: @js(old('genero', '')),
                fechaNac: @js(old('fecha_nacimiento', '')),
                paisDoc: @js(old('pais_documento', 'Bolivia')),
                tipoDoc: @js(old('tipo_documento', 'CI')),
                documento: @js(old('numero_documento', '')),
                expedido: @js(old('expedido', '')),
                correo: @js(old('correo', '')),
                paisTel: @js(old('pais_telefono', 'Bolivia')),
                telefono: @js(old('telefono', '')),
                rol: @js(old('rol', '')),
                fechaIng: @js(old('fecha_ingreso', now()->format('Y-m-d'))),
                especialidad: @js(old('especialidad_salud', '')),
                cargo: @js(old('cargo_administrativo', '')),
                estado: @js(old('estado', 'ACTIVO')),
                acceso: @js(old('acceso_sistema', 'HABILITADO')),
                observaciones: @js(old('observaciones', '')),
                fotoPreview: null,
                errors: {
                    nombres: '', apellidos: '', genero: '',
                    pais_documento: '', tipo_documento: '', numero_documento: '', expedido: '',
                    correo: '', telefono: '',
                    rol: '', especialidad: '', cargo_administrativo: '',
                    estado: '', acceso_sistema: '', foto: ''
                },
                completionPercentage: 0,

                paisesDoc: {
                    'Bolivia': ['CI'],
                    'Brasil': ['CPF', 'RG', 'PASAPORTE'],
                    'Argentina': ['DNI', 'PASAPORTE'],
                    'Perú': ['DNI', 'PASAPORTE'],
                    'Chile': ['RUN/RUT', 'PASAPORTE'],
                    'Colombia': ['CÉDULA', 'PASAPORTE'],
                    'México': ['CURP', 'PASAPORTE'],
                    'Otro': ['DOCUMENTO NACIONAL', 'PASAPORTE', 'OTRO']
                },
                codigosTel: {
                    'Bolivia': '+591', 'Brasil': '+55', 'Argentina': '+54',
                    'Perú': '+51', 'Chile': '+56', 'Colombia': '+57',
                    'México': '+52', 'Otro': ''
                },

                init() {
                    this.calculateCompletion();
                    this.$watch('cargo', () => this.calculateCompletion());

                    // Watchers de Pais para evitar reseteos involuntarios y actualizar dependencias
                    this.$watch('paisDoc', (val) => {
                        // Solo resetear tipoDoc si el actual no es válido para el nuevo país
                        if (!this.paisesDoc[val].includes(this.tipoDoc)) {
                            this.tipoDoc = this.paisesDoc[val][0];
                        }
                    });

                    this.$watch('paisTel', (val) => {
                        this.codigoTel = this.codigosTel[val] || '';
                    });

                    // Cargar errores de backend si existen
                    @if($errors->any())
                        @foreach($errors->keys() as $key)
                            @php $targetKey = $key === "ap_paterno" || $key === "ap_materno" ? "apellidos" : $key; @endphp
                            this.errors['{{ $targetKey }}'] = '{{ $errors->first($key) }}';
                        @endforeach
                        
                        // Ir al primer paso con error
                        if (this.errors.nombres || this.errors.apellidos || this.errors.genero || this.errors.fecha_nacimiento || this.errors.foto) this.step = 1;
                        else if (this.errors.pais_documento || this.errors.tipo_documento || this.errors.numero_documento || this.errors.expedido) this.step = 2;
                        else if (this.errors.correo || this.errors.telefono) this.step = 3;
                        else if (this.errors.rol || this.errors.especialidad || this.errors.cargo_administrativo) this.step = 4;
                        else if (this.errors.estado || this.errors.acceso_sistema) this.step = 5;
                    @endif
                },

                calculateCompletion() {
                    let fields = [
                        { val: this.nombres, weight: 1 },
                        { val: (this.apPaterno || this.apMaterno), weight: 1 },
                        { val: this.genero, weight: 1 },
                        { val: this.fechaNac, weight: 1 },
                        { val: this.documento, weight: 1 },
                        { val: (this.correo && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.correo)), weight: 1 },
                        { val: (this.telefono && this.telefono.length >= 7), weight: 1 },
                        { val: this.rol, weight: 1 }
                    ];

                    if (this.rol === 'personal_salud') fields.push({ val: this.especialidad, weight: 1 });
                    else if (this.rol === 'personal_admin') fields.push({ val: this.cargo, weight: 1 });

                    let completed = fields.filter(f => f.val).length;
                    this.completionPercentage = Math.round((completed / fields.length) * 100);
                },

                initials() {
                    if (!this.nombres) return 'RM';
                    let parts = this.nombres.trim().split(/\s+/);
                    return parts.map(p => p.charAt(0).toUpperCase()).join('').substring(0, 2);
                },

                fullName() {
                    return `${this.nombres} ${this.apPaterno} ${this.apMaterno}`.trim().toUpperCase();
                },

                rolDisplay() {
                    if (this.rol === 'personal_salud') return 'PERSONAL DE SALUD';
                    if (this.rol === 'personal_admin') return 'PERSONAL ADMINISTRATIVO';
                    if (this.rol === 'familiar') return 'FAMILIAR / RESPONSABLE';
                    if (this.rol === 'voluntario') return 'VOLUNTARIO';
                    return this.rol ? this.rol.replace('_', ' ').toUpperCase() : 'ROL NO ASIGNADO';
                },

                calculateAgeText() {
                    if (!this.fechaNac) return 'Pendiente de fecha';
                    const nac = new Date(this.fechaNac);
                    const hoy = new Date();
                    if (nac > hoy) return 'Fecha inválida (Futura)';
                    
                    let edad = hoy.getFullYear() - nac.getFullYear();
                    const m = hoy.getMonth() - nac.getMonth();
                    if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;
                    
                    if (edad < 0) return 'Fecha inválida';
                    return edad + ' años' + (edad < 18 ? ' (Menor de edad)' : '');
                },

                documentoPlaceholder() {
                    const placeholders = {
                        'Bolivia': 'Ej. 10013724',
                        'Brasil': 'Ej. 12345678901 (11 dígitos)',
                        'Argentina': 'Ej. 12345678',
                        'Perú': 'Ej. 12345678',
                        'Chile': 'Ej. 12345678-K',
                        'Colombia': 'Ej. 1234567890',
                        'México': 'Ej. ABC123456XYZ',
                        'Otro': 'Ingrese documento'
                    };
                    return placeholders[this.paisDoc] || 'Ingrese documento';
                },

                telefonoEjemplo() {
                    const ejemplos = {
                        'Bolivia': '8 dígitos (76543210)',
                        'Brasil': '10-11 dígitos',
                        'Argentina': '10-11 dígitos',
                        'Perú': '9 dígitos',
                        'Chile': '9 dígitos',
                        'Colombia': '10 dígitos',
                        'México': '10 dígitos',
                        'Otro': '6-15 dígitos'
                    };
                    return ejemplos[this.paisTel] || 'Formato local';
                },

                passwordPreview() {
                    if (!this.nombres || (!this.apPaterno && !this.apMaterno) || !this.documento) return '---';
                    let partes = this.nombres.trim().split(/\s+/);
                    if (this.apPaterno) partes.push(this.apPaterno.trim());
                    if (this.apMaterno) partes.push(this.apMaterno.trim());
                    let iniciales = partes.filter(p => p.length > 0).map(p => p.charAt(0).toUpperCase()).join('');
                    let docClean = this.documento.toUpperCase().replace(/[^A-Z0-9]/g, '');
                    return iniciales + docClean;
                },

                handleFotoChange(event) {
                    const file = event.target.files[0];
                    this.errors.foto = '';
                    if (!file) return;

                    if (!file.type.match('image.*')) {
                        this.errors.foto = 'La foto debe ser JPG, PNG o WEBP.';
                        event.target.value = '';
                        this.fotoPreview = null;
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        this.errors.foto = 'La foto no debe superar los 2 MB.';
                        event.target.value = '';
                        this.fotoPreview = null;
                        return;
                    }

                    this.fotoPreview = URL.createObjectURL(file);
                },

                clearError(field) {
                    this.errors[field] = '';
                },

                nextStep() {
                    if (this.validateStep()) {
                        this.step++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        Swal.fire({
                            title: 'Campos Incompletos',
                            text: 'Por favor, revise los errores marcados en rojo antes de continuar.',
                            icon: 'warning',
                            confirmButtonColor: '#2F3E5C'
                        });
                    }
                },

                prevStep() {
                    this.step--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                validateStep() {
                    let stepErrors = {};
                    
                    if (this.step === 1) {
                        if (!this.nombres.trim()) stepErrors.nombres = 'Debe ingresar los nombres.';
                        else if (this.nombres.trim().length < 2) stepErrors.nombres = 'El nombre debe tener al menos 2 caracteres.';
                        
                        if (!this.apPaterno.trim() && !this.apMaterno.trim()) {
                            stepErrors.apellidos = 'Debe ingresar al menos un apellido: paterno o materno.';
                        }
                        
                        if (!this.genero) stepErrors.genero = 'Seleccione el sexo.';

                        if (!this.fechaNac) {
                            stepErrors.fecha_nacimiento = 'La fecha de nacimiento es obligatoria.';
                        } else {
                            const nac = new Date(this.fechaNac);
                            const hoy = new Date();
                            let edad = hoy.getFullYear() - nac.getFullYear();
                            const m = hoy.getMonth() - nac.getMonth();
                            if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;

                            if (edad < 18) stepErrors.fecha_nacimiento = 'El usuario debe tener al menos 18 años.';
                            if (edad > 100) stepErrors.fecha_nacimiento = 'La fecha de nacimiento no puede superar los 100 años.';
                            if (nac > hoy) stepErrors.fecha_nacimiento = 'La fecha de nacimiento no puede ser futura.';
                        }
                    }
                    
                    if (this.step === 2) {
                        if (!this.documento.trim()) stepErrors.numero_documento = 'Debe ingresar el número de documento.';
                        
                        if (this.paisDoc === 'Brasil' && this.tipoDoc === 'CPF') {
                            let cpf = this.documento.replace(/\D/g, '');
                            if (cpf.length !== 11) stepErrors.numero_documento = 'El CPF de Brasil debe tener exactamente 11 dígitos.';
                        }

                        if (this.paisDoc === 'Argentina' && this.tipoDoc === 'DNI') {
                            let dni = this.documento.replace(/\D/g, '');
                            if (dni.length < 7 || dni.length > 9) stepErrors.numero_documento = 'El DNI de Argentina debe tener entre 7 y 9 dígitos.';
                        }

                        if (this.paisDoc === 'Perú' && this.tipoDoc === 'DNI') {
                            let dni = this.documento.replace(/\D/g, '');
                            if (dni.length !== 8) stepErrors.numero_documento = 'El DNI de Perú debe tener exactamente 8 dígitos.';
                        }

                        if (this.paisDoc === 'Colombia' && this.tipoDoc === 'CÉDULA') {
                            let cedula = this.documento.replace(/\D/g, '');
                            if (cedula.length < 6 || cedula.length > 10) stepErrors.numero_documento = 'La Cédula de Colombia debe tener entre 6 y 10 dígitos.';
                        }

                        if (this.paisDoc === 'México' && this.tipoDoc === 'CURP') {
                            if (this.documento.length !== 18) stepErrors.numero_documento = 'El CURP de México debe tener exactamente 18 caracteres.';
                        }

                        if (this.paisDoc === 'Otro') {
                            if (this.documento.length < 5 || this.documento.length > 30) stepErrors.numero_documento = 'El documento debe tener entre 5 y 30 caracteres.';
                        }
                    }
                    
                    if (this.step === 3) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!this.correo.trim()) stepErrors.correo = 'Debe ingresar un correo válido.';
                        else if (!emailRegex.test(this.correo)) stepErrors.correo = 'El formato de correo no es válido.';
                        
                        if (!this.telefono.trim()) {
                            stepErrors.telefono = 'Debe ingresar el número de celular.';
                        } else {
                            let telLimpio = this.telefono.replace(/\D/g, '');
                            if (this.paisTel === 'Bolivia' && telLimpio.length !== 8) {
                                stepErrors.telefono = 'En Bolivia el celular debe tener 8 dígitos.';
                            } else if (this.paisTel === 'Brasil' && (telLimpio.length < 10 || telLimpio.length > 11)) {
                                stepErrors.telefono = 'En Brasil el celular debe tener 10 u 11 dígitos.';
                            } else if (this.paisTel === 'Argentina' && (telLimpio.length < 10 || telLimpio.length > 11)) {
                                stepErrors.telefono = 'En Argentina el celular debe tener 10 u 11 dígitos.';
                            } else if (this.paisTel === 'Perú' && telLimpio.length !== 9) {
                                stepErrors.telefono = 'En Perú el celular debe tener 9 dígitos.';
                            } else if (this.paisTel === 'Chile' && telLimpio.length !== 9) {
                                stepErrors.telefono = 'En Chile el celular debe tener 9 dígitos.';
                            } else if (this.paisTel === 'Colombia' && telLimpio.length !== 10) {
                                stepErrors.telefono = 'En Colombia el celular debe tener 10 dígitos.';
                            } else if (this.paisTel === 'México' && telLimpio.length !== 10) {
                                stepErrors.telefono = 'En México el celular debe tener 10 dígitos.';
                            } else if (telLimpio.length < 6) {
                                stepErrors.telefono = 'El celular debe tener al menos 6 dígitos.';
                            }
                        }
                    }
                    
                    if (this.step === 4) {
                        if (!this.rol) stepErrors.rol = 'Debe asignar un rol al usuario.';
                        if (this.rol === 'personal_salud' && !this.especialidad) {
                            stepErrors.especialidad = 'Debe seleccionar una especialidad para el personal de salud.';
                        }
                        if (this.rol === 'personal_admin' && !this.cargo) {
                            stepErrors.cargo_administrativo = 'Debe seleccionar un cargo administrativo.';
                        }
                    }

                    this.errors = { ...this.errors, ...stepErrors };
                    return Object.keys(stepErrors).length === 0;
                },

                confirmSubmit() {
                    Swal.fire({
                        title: '¿Confirmar Registro?',
                        text: "Se registrará al usuario con las credenciales autogeneradas.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#2F3E5C',
                        cancelButtonColor: '#E27D60',
                        confirmButtonText: 'Sí, Registrar',
                        cancelButtonText: 'Revisar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.isSubmitting = true;
                            document.getElementById('registrationForm').submit();
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>
