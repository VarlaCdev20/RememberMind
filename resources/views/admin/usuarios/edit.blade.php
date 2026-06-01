<x-app-layout>
 <div class="relative min-h-screen bg-fondo-app font-outfit text-titulo">
 {{-- Fondo con ruido y puntos --}}
 <div class="dash-noise pointer-events-none fixed inset-0 z-[60] opacity-[0.14] mix-blend-overlay"></div>
 <div class="dash-dots pointer-events-none fixed inset-0 z-0 opacity-[0.03]"></div>
 
 <main class="relative z-10 mx-auto max-w-5xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
 <header class="mb-8 flex items-center justify-between">
 <div>
 <nav class="mb-2 flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-terracota">
 <a href="{{ route('admin.usuarios.index') }}" class="transition hover:text-titulo">Usuarios</a>
 <i class="ph-bold ph-caret-right text-[8px]"></i>
 <span>Edición Institucional</span>
 </nav>
 <h1 class="text-3xl font-black text-titulo sm:text-4xl">
 Editar <span class="text-terracota">Usuario</span>
 </h1>
 <p class="mt-2 text-xs font-bold text-titulo/50">Actualice la información del perfil de {{ $usuario->name }}.</p>
 </div>
 <div class="hidden sm:block">
 <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-boton-principal/5 text-titulo">
 <i class="ph-bold ph-pencil-line text-2xl"></i>
 </div>
 </div>
 </header>

 <form action="{{ route('admin.usuarios.update', $usuario->cod_usu) }}" method="POST" enctype="multipart/form-data"
 x-data="{
 paisDoc: @js(old('pais_documento', $usuario->pais_documento ?? 'Bolivia')),
 tipoDoc: @js(old('tipo_documento', $usuario->tipo_documento ?? 'CI')),
 paisTel: @js(old('pais_telefono', $usuario->pais_telefono ?? 'Bolivia')),
 rol: @js(old('rol', $usuario->getRoleNames()->first() ?? '')),
 fechaNac: @js(old('fecha_nacimiento', $usuario->fecha_nacimiento ? (is_string($usuario->fecha_nacimiento) ? $usuario->fecha_nacimiento : $usuario->fecha_nacimiento->format('Y-m-d')) : '')),
 fotoPreview: null,
 errors: {},
 
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
 this.$watch('paisDoc', (val) => {
 if (!this.paisesDoc[val].includes(this.tipoDoc)) {
 this.tipoDoc = this.paisesDoc[val][0];
 }
 });
 },

 calculateAgeText() {
 if (!this.fechaNac) return 'Pendiente';
 const nac = new Date(this.fechaNac);
 const hoy = new Date();
 if (nac > hoy) return 'Fecha inválida';
 let edad = hoy.getFullYear() - nac.getFullYear();
 const m = hoy.getMonth() - nac.getMonth();
 if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;
 return edad >= 0 ? edad + ' años' : '---';
 },

 documentoPlaceholder() {
 const placeholders = {
 'Bolivia': 'Ej. 10013724', 'Brasil': '11 dígitos', 'Argentina': '8 dígitos',
 'Chile': 'Ej. 12.345.678-K', 'México': 'CURP 18 carac.'
 };
 return placeholders[this.paisDoc] || 'Ingrese documento';
 },

 handleFotoChange(event) {
 const file = event.target.files[0];
 if (!file) return;
 if (!file.type.match('image.*')) return;
 this.fotoPreview = URL.createObjectURL(file);
 }
 }"
 class="space-y-6">
 @csrf
 @method('PUT')

 {{-- Resumen de Errores Globales --}}
 @if ($errors->any())
 <div class="rounded-[2rem] border-2 border-red-200 bg-red-50 p-6 shadow-sm">
 <div class="flex items-center gap-3 mb-3">
 <i class="ph-bold ph-warning-circle text-2xl text-red-500"></i>
 <h3 class="text-sm font-bold uppercase tracking-widest text-red-800">Hay errores en el formulario</h3>
 </div>
 <ul class="grid gap-x-8 gap-y-1 sm:grid-cols-2">
 @foreach ($errors->all() as $error)
 <li class="text-[10px] font-bold text-red-600 flex items-center gap-2">
 <div class="h-1 w-1 rounded-full bg-red-400"></div>
 {{ $error }}
 </li>
 @endforeach
 </ul>
 </div>
 @endif

 {{-- SECCIÓN: DATOS PERSONALES --}}
 <section class="overflow-hidden rounded-[2.5rem] border border-borde-suave bg-fondo-panel p-8 shadow-sm backdrop-blur-xl">
 <div class="mb-6 flex items-center gap-3 border-b border-borde-suave pb-4">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-principal text-inverso">
 <i class="ph-bold ph-identification-card text-lg"></i>
 </div>
 <div>
 <h2 class="text-sm font-bold uppercase tracking-widest text-titulo">Identidad y Datos Personales</h2>
 <p class="text-[10px] font-bold text-titulo/50">Información institucional básica.</p>
 </div>
 </div>

 <div class="grid gap-5 md:grid-cols-2">
 <div class="md:col-span-2">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Nombre Completo *</label>
 <input type="text" name="nombres" value="{{ old('nombres', $usuario->nombres) }}" required
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-borde-focus">
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Apellido Paterno</label>
 <input type="text" name="ap_paterno" value="{{ old('ap_paterno', $usuario->ap_paterno) }}"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-borde-focus">
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Apellido Materno</label>
 <input type="text" name="ap_materno" value="{{ old('ap_materno', $usuario->ap_materno) }}"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-borde-focus">
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Sexo *</label>
 <select name="genero" required class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 <option value="FEMENINO" {{ old('genero', $usuario->genero) == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
 <option value="MASCULINO" {{ old('genero', $usuario->genero) == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
 <option value="OTRO" {{ old('genero', $usuario->genero) == 'OTRO' ? 'selected' : '' }}>OTRO</option>
 <option value="PREFIERE NO ESPECIFICAR" {{ old('genero', $usuario->genero) == 'PREFIERE NO ESPECIFICAR' ? 'selected' : '' }}>PREFIERE NO ESPECIFICAR</option>
 </select>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Fecha de Nacimiento *</label>
 <input type="date" name="fecha_nacimiento" x-model="fechaNac" required
 value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento ? (is_string($usuario->fecha_nacimiento) ? $usuario->fecha_nacimiento : $usuario->fecha_nacimiento->format('Y-m-d')) : '') }}"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Edad Calculada</label>
 <div class="flex h-[46px] items-center gap-3 rounded-2xl border border-borde-suave bg-boton-principal/5 px-5 py-3 text-sm font-bold text-titulo/70 shadow-inner">
 <i class="ph-bold ph-calendar text-terracota"></i>
 <span x-text="calculateAgeText()"></span>
 </div>
 </div>
 </div>
 </section>

 {{-- SECCIÓN: DOCUMENTO DE IDENTIDAD --}}
 <section class="overflow-hidden rounded-[2.5rem] border border-borde-suave bg-fondo-panel p-8 shadow-sm backdrop-blur-xl">
 <div class="mb-6 flex items-center gap-3 border-b border-borde-suave pb-4">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-principal text-inverso">
 <i class="ph-bold ph-file-text text-lg"></i>
 </div>
 <div>
 <h2 class="text-sm font-bold uppercase tracking-widest text-titulo">Documento de Identidad</h2>
 <p class="text-[10px] font-bold text-titulo/50">Información para identificación institucional.</p>
 </div>
 </div>

 <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">País Emisor *</label>
 <select name="pais_documento" x-model="paisDoc" required
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 <template x-for="(tipos, pais) in paisesDoc" :key="pais">
 <option :value="pais" x-text="pais" :selected="pais === paisDoc"></option>
 </template>
 </select>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Tipo Documento *</label>
 <select name="tipo_documento" x-model="tipoDoc" required
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 <template x-for="tipo in paisesDoc[paisDoc]" :key="tipo">
 <option :value="tipo" x-text="tipo" :selected="tipo === tipoDoc"></option>
 </template>
 </select>
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Número de Documento *</label>
 <input type="text" name="numero_documento" value="{{ old('numero_documento', $usuario->numero_documento) }}" required
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-borde-focus focus:ring-4 focus:ring-borde-focus/10"
 :placeholder="documentoPlaceholder()">
 </div>

 <div x-show="paisDoc === 'Bolivia' && tipoDoc === 'CI'">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Expedido en</label>
 <select name="expedido" 
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 <option value="">Seleccionar...</option>
 @foreach(['LP', 'CBBA', 'SCZ', 'OR', 'PT', 'CH', 'TJ', 'BN', 'PD'] as $exp)
 <option value="{{ $exp }}" {{ old('expedido', $usuario->expedido) == $exp ? 'selected' : '' }}>{{ $exp }}</option>
 @endforeach
 </select>
 </div>
 </div>
 </section>

 {{-- SECCIÓN: CONTACTO --}}
 <section class="overflow-hidden rounded-[2.5rem] border border-borde-suave bg-fondo-panel p-8 shadow-sm backdrop-blur-xl">
 <div class="mb-6 flex items-center gap-3 border-b border-borde-suave pb-4">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-principal text-inverso">
 <i class="ph-bold ph-phone-call text-lg"></i>
 </div>
 <div>
 <h2 class="text-sm font-bold uppercase tracking-widest text-titulo">Datos de Contacto</h2>
 <p class="text-[10px] font-bold text-titulo/50">Canales de comunicación institucional.</p>
 </div>
 </div>

 <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
 <div class="lg:col-span-2">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Correo Electrónico Institucional *</label>
 <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold lowercase outline-none transition focus:border-borde-focus">
 </div>

 <div class="grid grid-cols-3 gap-2">
 <div class="col-span-1">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">País</label>
 <select name="pais_telefono" x-model="paisTel" required
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-3 py-3 text-xs font-bold outline-none transition focus:border-borde-focus">
 <template x-for="(cod, pais) in codigosTel" :key="pais">
 <option :value="pais" x-text="pais" :selected="pais === paisTel"></option>
 </template>
 </select>
 </div>
 <div class="col-span-2">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Número de Celular *</label>
 <div class="flex items-center">
 <input type="text" name="codigo_telefono" :value="codigosTel[paisTel]" readonly
 class="w-16 rounded-l-2xl border-y border-l border-borde-suave bg-fondo-panel py-3 text-center text-sm font-bold text-titulo/60 outline-none">
 <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" required
 class="w-full rounded-r-2xl border border-borde-suave bg-fondo-card/40 px-4 py-3 text-sm font-bold outline-none transition focus:border-borde-focus focus:ring-4 focus:ring-borde-focus/10">
 </div>
 </div>
 </div>
 </div>
 </section>

 {{-- SECCIÓN: ROL Y FUNCIÓN INSTITUCIONAL --}}
 <section class="overflow-hidden rounded-[2.5rem] border border-borde-suave bg-fondo-panel p-8 shadow-sm backdrop-blur-xl">
 <div class="mb-6 flex items-center gap-3 border-b border-borde-suave pb-4">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-principal text-inverso">
 <i class="ph-bold ph-briefcase text-lg"></i>
 </div>
 <div>
 <h2 class="text-sm font-bold uppercase tracking-widest text-titulo">Rol y Función Institucional</h2>
 <p class="text-[10px] font-bold text-titulo/50">Definición de permisos y responsabilidades.</p>
 </div>
 </div>

 <div class="grid gap-5 md:grid-cols-2">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Rol Institucional *</label>
 <select name="rol" x-model="rol" required {{ $usuario->cod_usu === 'USU_0001' ? 'disabled' : '' }}
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus focus:ring-4 focus:ring-borde-focus/10 {{ $usuario->cod_usu === 'USU_0001' ? 'opacity-60 cursor-not-allowed' : '' }}">
 <option value="">Seleccione un rol...</option>
 @foreach($roles as $r)
 @php
 $displayName = match($r->name) {
 'personal_salud' => 'PERSONAL DE SALUD',
 'personal_admin' => 'PERSONAL ADMINISTRATIVO',
 'familiar' => 'FAMILIAR / RESPONSABLE',
 'voluntario' => 'VOLUNTARIO',
 default => strtoupper(str_replace('_', ' ', $r->name))
 };
 $selected = old('rol', $usuario->getRoleNames()->first()) == $r->name ? 'selected' : '';
 @endphp
 <option value="{{ $r->name }}" {{ $selected }}>{{ $displayName }}</option>
 @endforeach
 </select>
 @if($usuario->cod_usu === 'USU_0001')
 <input type="hidden" name="rol" value="{{ $usuario->getRoleNames()->first() }}">
 <p class="mt-1 text-[9px] font-bold text-terracota uppercase italic"><i class="ph-bold ph-warning"></i> Perfil de Super Administrador: El rol no puede ser modificado.</p>
 @endif
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Fecha de Ingreso</label>
 <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', $personalSalud?->fecha_ing?->format('Y-m-d') ?? ($personalAdmin?->fecha_ingreso?->format('Y-m-d') ?? '')) }}"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 </div>

 {{-- Condicional: Especialidad Salud --}}
 <div x-show="rol === 'personal_salud'" x-transition>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Especialidad del Personal de Salud *</label>
 <select name="especialidad_salud" :required="rol === 'personal_salud'"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 <option value="">Seleccionar especialidad...</option>
 @foreach($especialidades as $esp)
 <option value="{{ $esp->cod_esp }}" {{ (old('especialidad_salud', $personalSalud?->cod_esp) == $esp->cod_esp) ? 'selected' : '' }}>
 {{ $esp->nombre }}
 </option>
 @endforeach
 </select>
 </div>

 {{-- Condicional: Cargo Administrativo --}}
 <div x-show="rol === 'personal_admin'" x-transition>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Función Administrativa *</label>
 <select name="cargo_administrativo" :required="rol === 'personal_admin'"
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus">
 <option value="">Seleccionar función...</option>
 @foreach($cargosAdmin as $cargo)
 <option value="{{ $cargo->cod_cargo_admin }}" {{ (old('cargo_administrativo', $personalAdmin?->cod_cargo_admin) == $cargo->cod_cargo_admin) ? 'selected' : '' }}>
 {{ $cargo->nombre }}
 </option>
 @endforeach
 </select>
 </div>
 </div>
 </section>

 {{-- SECCIÓN: ACCESO Y SEGURIDAD --}}
 <section class="overflow-hidden rounded-[2.5rem] border border-borde-suave bg-fondo-panel p-8 shadow-sm backdrop-blur-xl">
 <div class="mb-6 flex items-center gap-3 border-b border-borde-suave pb-4">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-principal text-inverso">
 <i class="ph-bold ph-shield-check text-lg"></i>
 </div>
 <div>
 <h2 class="text-sm font-bold uppercase tracking-widest text-titulo">Acceso y Seguridad</h2>
 <p class="text-[10px] font-bold text-titulo/50">Configuración de credenciales y estado del sistema.</p>
 </div>
 </div>

 <div class="grid gap-5 md:grid-cols-2">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Estado del Usuario</label>
 <select name="estado" required {{ $usuario->cod_usu === 'USU_0001' ? 'disabled' : '' }}
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus {{ $usuario->cod_usu === 'USU_0001' ? 'opacity-60 cursor-not-allowed' : '' }}">
 <option value="ACTIVO" {{ old('estado', $usuario->estado) == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
 <option value="INACTIVO" {{ old('estado', $usuario->estado) == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
 <option value="ARCHIVADO" {{ old('estado', $usuario->estado) == 'ARCHIVADO' ? 'selected' : '' }}>ARCHIVADO</option>
 </select>
 @if($usuario->cod_usu === 'USU_0001') <input type="hidden" name="estado" value="{{ $usuario->estado }}"> @endif
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Acceso al Sistema</label>
 <select name="acceso_sistema" required {{ $usuario->cod_usu === 'USU_0001' ? 'disabled' : '' }}
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold outline-none transition focus:border-borde-focus {{ $usuario->cod_usu === 'USU_0001' ? 'opacity-60 cursor-not-allowed' : '' }}">
 <option value="HABILITADO" {{ old('acceso_sistema', $usuario->acceso_sistema) == 'HABILITADO' ? 'selected' : '' }}>HABILITADO</option>
 <option value="BLOQUEADO" {{ old('acceso_sistema', $usuario->acceso_sistema) == 'BLOQUEADO' ? 'selected' : '' }}>BLOQUEADO</option>
 </select>
 @if($usuario->cod_usu === 'USU_0001') <input type="hidden" name="acceso_sistema" value="{{ $usuario->acceso_sistema }}"> @endif
 </div>
 </div>
 </section>

 {{-- SECCIÓN: FOTO Y OBSERVACIONES --}}
 <section class="overflow-hidden rounded-[2.5rem] border border-borde-suave bg-fondo-panel p-8 shadow-sm backdrop-blur-xl">
 <div class="grid gap-6 md:grid-cols-2">
 <div>
 <label class="mb-4 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-titulo/60">
 <i class="ph-bold ph-camera text-base text-terracota"></i> Foto de Perfil Institucional
 </label>
 <div class="flex items-center gap-6">
 <div class="relative">
 {{-- Preview de foto --}}
 <template x-if="fotoPreview">
 <img :src="fotoPreview" class="h-24 w-24 rounded-[2rem] object-cover border-4 border-white shadow-lg">
 </template>
 <template x-if="!fotoPreview">
 <div>
 @if($usuario->foto_de_perfil)
 <img src="{{ asset('storage/'.$usuario->foto_de_perfil) }}" class="h-24 w-24 rounded-[2rem] object-cover border-4 border-white shadow-lg">
 @else
 <div class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-boton-principal text-2xl font-black text-inverso shadow-lg">
 {{ mb_substr($usuario->nombres, 0, 1) }}{{ mb_substr($usuario->ap_paterno ?? '', 0, 1) }}
 </div>
 @endif
 </div>
 </template>
 </div>
 <div class="flex-1">
 <input type="file" name="foto_perfil" accept="image/*" @change="handleFotoChange"
 class="block w-full text-[10px] font-bold text-titulo/40 file:mr-4 file:rounded-xl file:border-0 file:bg-boton-principal file:px-4 file:py-2 file:text-[9px] file:font-black file:uppercase file:text-inverso hover:file:bg-boton-acento transition cursor-pointer">
 <p class="mt-2 text-[9px] font-bold text-titulo/40 italic">Opcional. Si no selecciona una nueva imagen, se conservará la foto actual.</p>
 </div>
 </div>
 </div>
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-titulo/60">Observaciones Institucionales Internas</label>
 <textarea name="observaciones" rows="3" 
 class="w-full rounded-2xl border border-borde-suave bg-fondo-card/40 px-5 py-3 text-sm font-bold uppercase outline-none transition focus:border-borde-focus">{{ old('observaciones', $usuario->observaciones) }}</textarea>
 </div>
 </div>
 </section>

 {{-- BOTONES DE ACCIÓN --}}
 <div class="mt-8 flex flex-col-reverse gap-4 sm:flex-row sm:justify-end">
 <a href="{{ route('admin.usuarios.index') }}" 
 class="flex items-center justify-center gap-2 rounded-full border-2 border-borde-suave px-10 py-4 text-xs font-bold uppercase tracking-widest text-titulo transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-x"></i> Cancelar
 </a>
 <button type="submit" 
 class="flex items-center justify-center gap-2 rounded-full bg-boton-principal px-12 py-4 text-xs font-bold uppercase tracking-widest text-inverso shadow-xl transition hover:bg-boton-acento active:scale-95">
 <i class="ph-bold ph-floppy-disk"></i> Guardar Cambios
 </button>
 </div>
 </form>
 </main>
 </div>
</x-app-layout>
