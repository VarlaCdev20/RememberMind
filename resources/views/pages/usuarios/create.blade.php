<x-app-layout>
 <div class="relative min-h-screen overflow-hidden bg-fondo-app px-4 py-5 font-outfit text-titulo sm:px-6" x-data="userRegistration()">
 
 {{-- Script SweetAlert2 --}}
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
 {{-- Fondo con trama institucional (Coherencia con Adulto Mayor) --}}
 <div class="pointer-events-none fixed inset-0 opacity-[0.045] z-0"
 style="background-image: radial-gradient(#2F3E5C 1.2px, transparent 1.2px); background-size: 28px 28px;">
 </div>

 <main class="relative z-10 mx-auto max-w-5xl space-y-6">
 
 {{-- HEADER INSTITUCIONAL --}}
 <header class="rounded-2xl border border-borde-suave bg-fondo-panel p-6 shadow-[0_14px_32px_rgba(47,62,92,0.12)] backdrop-blur-xl">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div>
 <nav class="mb-2 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-terracota">
 <a href="{{ route('admin.usuarios.index') }}" class="transition hover:text-titulo">Usuarios</a>
 <i class="ph-bold ph-caret-right text-[8px]"></i>
 <span>Registro Institucional</span>
 </nav>
 <h1 class="text-2xl font-black text-titulo">Registro de <span class="text-terracota">Personal</span></h1>
 <p class="mt-1 text-sm font-bold text-titulo/60">Asistente de registro institucional para personal administrativo y de salud.</p>
 </div>
 <div class="flex items-center gap-4">
 {{-- Indicador de completitud --}}
 <div class="flex flex-col items-end">
 <span class="text-[10px] font-bold uppercase tracking-tighter text-titulo/40">Completitud</span>
 <div class="flex items-center gap-2">
 <span class="text-lg font-extrabold text-terracota" x-text="completionPercentage + '%'"></span>
 <div class="h-2 w-24 overflow-hidden rounded-full bg-fondo-panel">
 <div class="h-full bg-boton-acento transition-all duration-500" :style="'width: ' + completionPercentage + '%'"></div>
 </div>
 </div>
 </div>
 <a href="{{ route('admin.usuarios.index') }}" 
 class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-app text-titulo transition hover:bg-boton-principal hover:text-inverso active:scale-95">
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
 :class="step === n ? 'border-terracota bg-boton-acento text-inverso shadow-lg shadow-terracota/20' : 
 (step > n ? 'border-estado-exitoBorde bg-estado-exitoBg text-inverso' : 'border-borde-suave bg-fondo-card/50 text-meta')">
 <span class="text-xs font-bold" x-text="n"></span>
 </div>
 <div x-show="n < 6" class="h-1 flex-1 mx-2 rounded-full transition-all duration-500"
 :class="step > n ? 'bg-estado-exitoBg' : 'bg-fondo-panel'"></div>
 </div>
 </template>
 </div>
 <div class="mt-2 flex justify-between px-1 text-[9px] font-bold uppercase tracking-widest text-titulo/40">
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
 <p class="text-xs font-bold uppercase tracking-wide">{{ session('error') }}</p>
 </div>
 </div>
 @endif

 {{-- PASO 1: IDENTIDAD PERSONAL Y FOTO --}}
 <section x-show="step === 1" x-transition.opacity.duration.400ms class="grid gap-6 lg:grid-cols-3">
 {{-- Mini-Ficha Preview --}}
 <div class="lg:col-span-1">
 <div class="sticky top-6 rounded-3xl border border-borde-suave bg-fondo-panel p-6 shadow-sm backdrop-blur-xl">
 <div class="flex flex-col items-center text-center">
 <div class="relative mb-4">
 <template x-if="!fotoPreview">
 <div class="flex h-32 w-32 items-center justify-center rounded-[2.5rem] bg-boton-principal text-5xl font-black text-inverso shadow-xl">
 <span x-text="initials()"></span>
 </div>
 </template>
 <template x-if="fotoPreview">
 <img :src="fotoPreview" class="h-32 w-32 rounded-[2.5rem] object-cover border-4 border-white shadow-xl">
 </template>
 <label class="absolute -bottom-2 -right-2 flex h-10 w-10 cursor-pointer items-center justify-center rounded-2xl bg-boton-acento text-inverso shadow-lg transition hover:scale-110 active:scale-95">
 <i class="ph-bold ph-camera"></i>
 <input type="file" name="foto_perfil" class="hidden" accept=".jpg,.jpeg,.png,.webp" @change="handleFotoChange">
 </label>
 </div>
 <h3 class="text-lg font-extrabold leading-tight text-titulo" x-text="fullName() || 'Nombre del Usuario'"></h3>
 <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-terracota" x-text="rolDisplay()"></p>
 
 <div class="mt-6 w-full space-y-3 border-t border-borde-suave pt-6">
 <div class="flex justify-between text-[10px] font-bold">
 <span class="text-titulo/40 uppercase">Género</span>
 <span class="text-titulo font-black" x-text="genero || '---'"></span>
 </div>
 <div class="flex justify-between text-[10px] font-bold">
 <span class="text-titulo/40 uppercase">Documento</span>
 <span class="text-titulo font-black" x-text="documento || '---'"></span>
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
 <div class="rounded-3xl border border-borde-suave bg-fondo-panel p-8 shadow-sm">
 <div class="mb-6 flex items-center gap-3">
 <i class="ph-fill ph-user-circle text-2xl text-terracota"></i>
 <h2 class="text-lg font-extrabold text-titulo">Datos de Identidad</h2>
 </div>

 <div class="grid gap-5 md:grid-cols-2">
 <div class="md:col-span-2">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Nombre Completo *</label>
 <input type="text" name="nombres" x-model="nombres" @input="clearError('nombres')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold uppercase transition focus:border-borde-focus focus:ring-4 focus:ring-borde-focus/10 outline-none"
 placeholder="Ej. Carla Valeria" :class="{'border-red-400 bg-red-50/50 ring-red-400/20': errors.nombres}">
 <p x-show="errors.nombres" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.nombres"></p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Apellido Paterno</label>
 <input type="text" name="ap_paterno" x-model="apPaterno" @input="clearError('apellidos')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold uppercase transition focus:border-borde-focus outline-none"
 placeholder="Ej. Encinas" :class="{'border-red-400 bg-red-50/50': errors.apellidos}">
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Apellido Materno</label>
 <input type="text" name="ap_materno" x-model="apMaterno" @input="clearError('apellidos')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold uppercase transition focus:border-borde-focus outline-none"
 placeholder="Ej. Cano" :class="{'border-red-400 bg-red-50/50': errors.apellidos}">
 </div>

 <div class="md:col-span-2" x-show="errors.apellidos">
 <p class="text-[10px] font-bold text-red-500" x-text="errors.apellidos"></p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Fecha Nacimiento *</label>
 <input type="date" name="fecha_nacimiento" x-model="fechaNac" @input="clearError('fecha_nacimiento')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
 :class="{'border-red-400 bg-red-50/50': errors.fecha_nacimiento}">
 <p x-show="errors.fecha_nacimiento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.fecha_nacimiento"></p>
 <p class="mt-1 text-[9px] font-bold text-titulo/40 italic">Mínimo 18 años, máximo 100 años.</p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Edad Calculada</label>
 <div class="flex items-center gap-3 w-full rounded-2xl border border-borde-suave bg-fondo-panel px-5 py-3 text-sm font-bold text-titulo/70 shadow-inner">
 <i class="ph-bold ph-calendar text-terracota"></i>
 <span x-text="calculateAgeText()"></span>
 </div>
 <p class="mt-1.5 text-[9px] font-bold text-titulo/40 italic">La edad se autogenera desde la fecha de nacimiento.</p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Sexo *</label>
 <select name="genero" x-model="genero" @change="clearError('genero')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
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
 <div class="rounded-3xl border border-borde-suave bg-fondo-panel p-8 shadow-sm">
 <div class="mb-6 flex items-center gap-3">
 <i class="ph-fill ph-identification-card text-2xl text-terracota"></i>
 <h2 class="text-lg font-extrabold text-titulo">Documentación Oficial</h2>
 </div>

 <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">País Emisor *</label>
 <select name="pais_documento" x-model="paisDoc" @change="clearError('pais_documento')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
 :class="{'border-red-400 bg-red-50/50': errors.pais_documento}">
 <template x-for="(tipos, pais) in paisesDoc" :key="pais">
 <option :value="pais" x-text="pais"></option>
 </template>
 </select>
 <p x-show="errors.pais_documento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.pais_documento"></p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Tipo Documento *</label>
 <select name="tipo_documento" x-model="tipoDoc" @change="clearError('tipo_documento')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
 :class="{'border-red-400 bg-red-50/50': errors.tipo_documento}">
 <template x-for="tipo in paisesDoc[paisDoc]" :key="tipo">
 <option :value="tipo" x-text="tipo"></option>
 </template>
 </select>
 <p x-show="errors.tipo_documento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.tipo_documento"></p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">N° Documento *</label>
 <input type="text" name="numero_documento" x-model="documento" @input="clearError('numero_documento')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-borde-focus"
 :placeholder="documentoPlaceholder()" :class="{'border-red-400 bg-red-50/50': errors.numero_documento}">
 <p x-show="errors.numero_documento" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.numero_documento"></p>
 </div>

 <div x-show="paisDoc === 'Bolivia' && tipoDoc === 'CI'">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Expedido *</label>
 <select name="expedido" x-model="expedido" @change="clearError('expedido')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
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
 <div class="rounded-3xl border border-borde-suave bg-fondo-panel p-8 shadow-sm">
 <div class="mb-6 flex items-center gap-3">
 <i class="ph-fill ph-envelope-simple-open text-2xl text-terracota"></i>
 <h2 class="text-lg font-extrabold text-titulo">Canales de Contacto</h2>
 </div>

 <div class="grid gap-5 md:grid-cols-2">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Correo Electrónico *</label>
 <input type="email" name="correo" x-model="correo" @input="clearError('correo'); correo = correo.toLowerCase();"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold lowercase outline-none transition focus:border-borde-focus"
 placeholder="ejemplo@jardindelosrecuerdos.org" :class="{'border-red-400 bg-red-50/50': errors.correo}">
 <p x-show="errors.correo" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.correo"></p>
 </div>

 <div class="grid grid-cols-3 gap-2">
 <div class="col-span-1">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">País</label>
 <select name="pais_telefono" x-model="paisTel" @change="clearError('telefono')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-2 py-3 text-xs font-bold outline-none transition focus:border-borde-focus">
 <template x-for="(cod, pais) in codigosTel" :key="pais">
 <option :value="pais" x-text="pais"></option>
 </template>
 </select>
 </div>
 <div class="col-span-2">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Número de Celular *</label>
 <div class="flex items-center">
 <input type="text" name="codigo_telefono" :value="codigosTel[paisTel]" readonly
 class="w-16 rounded-l-2xl border-y border-l border-borde-suave bg-fondo-panel py-3 text-center text-xs font-bold text-titulo/60 outline-none">
 <input type="text" name="telefono" x-model="telefono" @input="clearError('telefono')"
 class="w-full rounded-r-2xl border border-borde-suave bg-fondo-card/50 px-4 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
 placeholder="70012345" :class="{'border-red-400 bg-red-50/50': errors.telefono}">
 </div>
 <p x-show="errors.telefono" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.telefono"></p>
 <p class="mt-1 text-[9px] font-bold text-titulo/40 italic" x-text="'Ej. ' + paisTel + ': ' + telefonoEjemplo()"></p>
 </div>
 </div>
 </div>
 </div>
 </section>

 {{-- PASO 4: ROL E INSTITUCIONAL --}}
 <section x-show="step === 4" x-transition.opacity.duration.400ms class="space-y-6">
 <div class="rounded-3xl border border-borde-suave bg-fondo-panel p-8 shadow-sm">
 <div class="mb-6 flex items-center gap-3">
 <i class="ph-fill ph-briefcase text-2xl text-terracota"></i>
 <h2 class="text-lg font-extrabold text-titulo">Perfil Institucional</h2>
 </div>

 <div class="grid gap-5 md:grid-cols-2">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Rol Institucional *</label>
 <select name="rol" x-model="rol" @change="clearError('rol')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
 :class="{'border-red-400 bg-red-50/50': errors.rol}">
 <option value="">SELECCIONE ROL...</option>
 @foreach($roles as $r)
 @php
 $displayName = match($r->name) {
 'FAMILIAR' => 'Familiar / Responsable',
 'VOLUNTARIO' => 'Voluntario',
 default => mb_convert_case(str_replace('_', ' ', $r->name), MB_CASE_TITLE, 'UTF-8')
 };
 @endphp
 <option value="{{ $r->name }}">{{ $displayName }}</option>
 @endforeach
 </select>
 <p x-show="errors.rol" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.rol"></p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Fecha Ingreso</label>
 <input type="date" name="fecha_ingreso" x-model="fechaIng"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 </div>

 <div x-show="['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'].includes(rol)" x-transition>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Especialidad Médica *</label>
 <select name="especialidad_salud" x-model="especialidad" @change="clearError('especialidad')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
 :class="{'border-red-400 bg-red-50/50': errors.especialidad}">
 <option value="">SELECCIONE...</option>
 @foreach($especialidades as $esp)
 <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
 @endforeach
 </select>
 <p x-show="errors.especialidad" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.especialidad"></p>
 </div>

 <div x-show="['SUPERADMINISTRADOR', 'ADMINISTRADOR'].includes(rol)" x-transition>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Función Administrativa *</label>
 <select name="cargo_administrativo" x-model="cargo" @change="clearError('cargo_administrativo')"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus"
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
 <div class="rounded-3xl border border-borde-suave bg-fondo-panel p-8 shadow-sm">
 <div class="mb-6 flex items-center gap-3">
 <i class="ph-fill ph-shield-check text-2xl text-terracota"></i>
 <h2 class="text-lg font-extrabold text-titulo">Seguridad y Acceso</h2>
 </div>

 <div class="grid gap-5 md:grid-cols-2">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Estado Perfil Inicial</label>
 <div class="flex items-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel px-5 py-3 text-sm font-bold text-estado-exito shadow-inner">
 <i class="ph-bold ph-check-circle"></i>
 <span>ACTIVO</span>
 <input type="hidden" name="estado" value="ACTIVO">
 </div>
 <p class="mt-1.5 text-[9px] font-bold text-titulo/40 italic">Todo nuevo registro institucional inicia en estado ACTIVO.</p>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Acceso Sistema Inicial</label>
 <div class="flex items-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel px-5 py-3 text-sm font-bold text-titulo shadow-inner">
 <i class="ph-bold ph-lock-key-open text-terracota"></i>
 <span>HABILITADO</span>
 <input type="hidden" name="acceso_sistema" value="HABILITADO">
 </div>
 <p x-show="errors.acceso_sistema" class="mt-1.5 text-[10px] font-bold text-red-500" x-text="errors.acceso_sistema"></p>
 </div>

 <div class="md:col-span-2 rounded-2xl border border-borde-suave bg-boton-principal/5 p-6">
 <div class="flex items-center justify-between mb-4">
 <span class="text-[10px] font-bold uppercase tracking-widest text-titulo/60">Contraseña Inicial Autogenerada</span>
 <span class="rounded-full bg-boton-acento/10 px-3 py-1 text-[9px] font-bold text-terracota">BASADA EN IDENTIDAD</span>
 </div>
 <div class="flex items-center gap-4">
 <div class="flex-1 rounded-xl bg-fondo-card px-5 py-4 text-center">
 <span class="text-2xl font-black tracking-[0.3em] text-terracota" x-text="passwordPreview()"></span>
 </div>
 <div class="h-14 w-14 flex items-center justify-center rounded-xl bg-boton-principal text-inverso shadow-lg">
 <i class="ph-bold ph-lock-key text-2xl"></i>
 </div>
 </div>
 <p class="mt-4 text-[10px] font-bold text-titulo/50 leading-relaxed italic">
 <i class="ph-bold ph-info mr-1"></i>
 Indique al usuario que deberá cambiar esta contraseña tras su primer ingreso exitoso. No se almacena en bitácoras.
 </p>
 </div>
 </div>
 </div>
 </section>

 {{-- PASO 6: RESUMEN Y FINALIZAR --}}
 <section x-show="step === 6" x-transition.opacity.duration.400ms class="space-y-6">
 <div class="rounded-3xl border border-borde-suave bg-fondo-panel p-8 shadow-sm">
 <div class="mb-6 flex items-center gap-3">
 <i class="ph-fill ph-check-square text-2xl text-terracota"></i>
 <h2 class="text-lg font-extrabold text-titulo">Confirmación de Registro</h2>
 </div>

 <div class="grid gap-8 lg:grid-cols-2">
 {{-- Resumen visual --}}
 <div class="rounded-2xl bg-fondo-card/40 p-6 border border-borde-suave">
 <h3 class="mb-4 text-[11px] font-bold uppercase tracking-widest text-titulo/60 border-b border-borde-suave pb-2">Resumen de Ficha</h3>
 <div class="space-y-4">
 <div class="flex items-center gap-4">
 <div class="h-16 w-16 overflow-hidden rounded-2xl bg-boton-principal flex items-center justify-center text-inverso">
 <template x-if="!fotoPreview"><span class="text-xl font-extrabold" x-text="initials()"></span></template>
 <template x-if="fotoPreview"><img :src="fotoPreview" class="h-full w-full object-cover"></template>
 </div>
 <div>
 <p class="text-sm font-bold text-titulo" x-text="fullName()"></p>
 <p class="text-[10px] font-bold text-terracota" x-text="rolDisplay()"></p>
 </div>
 </div>
 <div class="grid grid-cols-2 gap-x-4 gap-y-3 pt-2">
 <div>
 <p class="text-[9px] font-bold uppercase text-titulo/40">N° Documento</p>
 <p class="text-xs font-bold text-titulo" x-text="documento"></p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase text-titulo/40">Correo</p>
 <p class="text-xs font-bold text-titulo truncate" x-text="correo"></p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase text-titulo/40">Teléfono</p>
 <p class="text-xs font-bold text-titulo" x-text="telefono ? codigosTel[paisTel] + ' ' + telefono : '---'"></p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase text-titulo/40">Completitud</p>
 <p class="text-xs font-bold text-titulo" x-text="completionPercentage + '%'"></p>
 </div>
 </div>
 </div>
 </div>

 {{-- Notas finales --}}
 <div class="space-y-4">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Observaciones Administrativas</label>
 <textarea name="observaciones" rows="4" x-model="observaciones"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/50 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-borde-focus"
 placeholder="NOTAS ADICIONALES..."></textarea>
 </div>
 <div class="rounded-xl bg-boton-acento/10 p-4 border border-terracota/20">
 <p class="text-[10px] font-bold text-terracota text-center leading-relaxed">
 Al presionar"Registrar Usuario" se crearán las credenciales y el acceso institucional.
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
 class="flex items-center justify-center gap-2 rounded-full border-2 border-azul-profundo px-10 py-3 text-[10px] font-bold uppercase tracking-widest text-titulo transition hover:bg-boton-principal hover:text-inverso active:scale-95">
 <i class="ph-bold ph-arrow-left"></i> Anterior
 </button>
 <div x-show="step === 1" class="w-full sm:w-auto"></div> {{-- Espaciador --}}
 
 <div class="flex gap-4">
 <button type="button" x-show="step < 6" @click="nextStep()"
 class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-full bg-boton-principal px-12 py-3 text-[10px] font-bold uppercase tracking-widest text-inverso shadow-xl transition hover:bg-boton-acento active:scale-95">
 Siguiente Paso <i class="ph-bold ph-arrow-right"></i>
 </button>
 <button type="submit" x-show="step === 6" :disabled="isSubmitting"
 class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-full bg-boton-acento px-14 py-3 text-[10px] font-bold uppercase tracking-widest text-inverso shadow-xl transition hover:bg-boton-principal active:scale-95 disabled:opacity-50">
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
{!! view()->file(resource_path('frontend/scripts/modules/pages-usuarios-create.js.blade.php'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render() !!}
</script>
</x-app-layout>
