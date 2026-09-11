@if($mostrarVistaCompleta && $usuarioVista)
 <div class="fixed inset-0 z-[2147483648] flex items-center justify-center bg-boton-principal/60 backdrop-blur-md px-4 py-6 transition-all duration-300" x-data x-transition>
 <div class="relative w-full max-w-5xl max-h-full overflow-hidden rounded-[2rem] border border-borde-suave bg-fondo-app shadow-[0_25px_65px_rgba(0,0,0,0.6)] flex flex-col"
 style="animation: zoomIn 0.3s ease-out">
 
 {{-- Header --}}
 <header class="relative flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-6 py-4 backdrop-blur-xl shrink-0">
 <div class="flex items-center gap-4">
 <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-principal text-inverso shadow-lg">
 <i class="ph-bold ph-user-focus text-2xl"></i>
 </div>
 <div>
 <h2 class="text-xl font-extrabold tracking-tight text-parrafo">Ficha de <span class="text-boton-acento">Usuario</span></h2>
 <p class="text-[10px] font-bold uppercase tracking-widest text-meta">Ficha institucional</p>
 </div>
 </div>
 <button type="button" wire:click="cerrarVistaCompleta" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-app text-parrafo transition-all hover:bg-boton-acento hover:text-inverso active:scale-90 shadow-sm">
 <i class="ph-bold ph-x text-xl transition group-hover:rotate-90"></i>
 </button>
 </header>

 {{-- Contenido Scrollable --}}
 <div class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-8">
 @php
 $vistaRoleName = $usuarioVista->getRoleNames()->first() ?? 'sin_rol';
 $vistaRoleKey = strtolower($vistaRoleName);
 $vistaNombreCompleto = trim(($usuarioVista->nombres ?? '') . ' ' . ($usuarioVista->ap_paterno ?? '') . ' ' . ($usuarioVista->ap_materno ?? ''));
 $vistaInicial = mb_substr(trim($usuarioVista->nombres ?? 'U'), 0, 1);

 $vistaAreaDisplay = $usuarioVista->areaInstitucional?->nombre ?? match($vistaRoleKey) {
 'super_admin', 'admin' => 'Administración del sistema',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'Área de salud',
 'superadministrador', 'administrador' => 'Área administrativa',
 'FAMILIAR' => 'Familiar autorizado',
 'VOLUNTARIO' => 'Voluntariado',
 default => 'Sin área asignada'
 };

 $vistaPerfilDetalle = match($vistaRoleKey) {
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => $usuarioVista->personalSalud?->especialidad?->nombre ?? 'Personal de salud',
 'superadministrador', 'administrador' => $usuarioVista->personalAdmin?->cargoAdmin?->nombre ?? $usuarioVista->personalAdmin?->cargo ?? 'Personal administrativo',
 'super_admin', 'admin' => 'Administrador del sistema',
 'VOLUNTARIO' => 'Voluntario institucional',
 'FAMILIAR' => 'Familiar autorizado',
 default => strtoupper(str_replace('_', ' ', $vistaRoleName))
 };

 $vistaFoto = null;
 if (!empty($usuarioVista->foto_de_perfil)) {
 $vistaFoto = \Illuminate\Support\Facades\Storage::url($usuarioVista->foto_de_perfil);
 } elseif (!empty($usuarioVista->profile_photo_url)) {
 $vistaFoto = $usuarioVista->profile_photo_url;
 }
 @endphp

 <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
 {{-- Columna Izquierda: Perfil Principal --}}
 <div class="lg:col-span-4 flex flex-col items-center space-y-6">
 <div class="relative">
 @if($vistaFoto)
 <img src="{{ $vistaFoto }}" alt="{{ $vistaNombreCompleto }}"
 class="h-24 w-24 rounded-[1.5rem] object-cover ring-4 ring-white shadow-[0_8px_20px_rgba(47,62,92,0.18)]">
 @else
 <div class="flex h-24 w-24 items-center justify-center rounded-[1.5rem] bg-boton-principal text-3xl font-black text-inverso ring-4 ring-white shadow-[0_8px_20px_rgba(47,62,92,0.18)]">
 {{ strtoupper($vistaInicial) }}
 </div>
 @endif
 <span class="absolute bottom-2 right-2 h-7 w-7 rounded-full border-4 border-white {{ $usuarioVista->estado === 'ACTIVO' ? 'bg-estado-exitoBg' : 'bg-fondo-panel' }}"></span>
 </div>

 <div class="text-center w-full">
 <h3 class="text-2xl font-black uppercase text-parrafo leading-tight">{{ $vistaNombreCompleto }}</h3>
 <p class="mt-1 text-sm font-bold text-apoyo lowercase">{{ $usuarioVista->correo ?: 'Sin correo' }}</p>
 
 <div class="mt-4 flex flex-wrap justify-center gap-2">
 <span class="rounded-full {{ $usuarioVista->estado === 'ACTIVO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-fondo-panel text-meta' }} px-4 py-1.5 text-xs font-bold uppercase">
 <i class="ph-bold {{ $usuarioVista->estado === 'ACTIVO' ? 'ph-check-circle' : 'ph-minus-circle' }} mr-1"></i>
 {{ $usuarioVista->estado }}
 </span>
 <span class="rounded-full bg-fondo-panel px-4 py-1.5 text-xs font-bold uppercase text-parrafo">
 <i class="ph-bold ph-shield mr-1"></i>
 {{ match($vistaRoleKey) {
 'superadministrador', 'administrador' => 'PERSONAL ADMINISTRATIVO',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'PERSONAL DE SALUD',
 'VOLUNTARIO' => 'VOLUNTARIO',
 'FAMILIAR' => 'FAMILIAR AUTORIZADO',
 default => strtoupper(str_replace('_', ' ', $vistaRoleName)),
 } }}
 </span>
 </div>
 </div>

 <div class="w-full rounded-2xl bg-fondo-card/40 border border-borde-suave p-5 space-y-4 shadow-sm">
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Acceso al Sistema</p>
 <p class="mt-1 font-black {{ $usuarioVista->acceso_sistema === 'HABILITADO' ? 'text-estado-exito' : 'text-boton-acento' }}">
 {{ $usuarioVista->acceso_sistema ?? 'NO DEFINIDO' }}
 </p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Último Acceso</p>
 <p class="mt-1 font-black text-parrafo">
 {{ $usuarioVista->ultimo_acceso ? $usuarioVista->ultimo_acceso->format('d/m/Y H:i') : 'Sin registro' }}
 </p>
 </div>
 </div>
 </div>

 {{-- Columna Derecha: Detalles Completos --}}
 <div class="lg:col-span-8 space-y-6">
 
 {{-- Identidad --}}
 <div class="rounded-[1.5rem] bg-fondo-card/60 border border-borde-suave p-6 shadow-sm">
 <h4 class="mb-4 flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-identification-card text-lg"></i> Datos de Identidad
 </h4>
 <div class="grid grid-cols-2 md:grid-cols-3 gap-y-5 gap-x-4">
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Documento</p>
 <p class="mt-1 text-sm font-bold text-parrafo">
 {{ $usuarioVista->tipo_documento ?? 'CI' }} {{ $usuarioVista->numero_documento }}
 @if($usuarioVista->expedido) <span class="text-xs text-apoyo">{{ $usuarioVista->expedido }}</span> @endif
 </p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Nacionalidad</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $usuarioVista->pais_documento ?? 'Bolivia' }}</p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Género</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $usuarioVista->genero ?? 'No especificado' }}</p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Nacimiento</p>
 <p class="mt-1 text-sm font-bold text-parrafo">
 {{ $usuarioVista->fecha_nacimiento ? $usuarioVista->fecha_nacimiento->format('d/m/Y') : 'Sin registro' }}
 @if($usuarioVista->fecha_nacimiento)
 <span class="text-xs text-apoyo">({{ $usuarioVista->fecha_nacimiento->age }} años)</span>
 @endif
 </p>
 </div>
 </div>
 </div>

 {{-- Perfil Institucional --}}
 <div class="rounded-[1.5rem] bg-fondo-card/60 border border-borde-suave p-6 shadow-sm">
 <h4 class="mb-4 flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-buildings text-lg"></i> Perfil Institucional
 </h4>
 <div class="grid grid-cols-2 gap-y-5 gap-x-4">
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Área Asignada</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $vistaAreaDisplay }}</p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Cargo / Especialidad</p>
 <p class="mt-1 text-sm font-bold text-parrafo uppercase">{{ $vistaPerfilDetalle }}</p>
 </div>
 @if(in_array($vistaRoleKey, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && $usuarioVista->personalSalud?->fecha_ing)
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Fecha de Ingreso</p>
 <p class="mt-1 text-sm font-bold text-parrafo">
 {{ $usuarioVista->personalSalud->fecha_ing instanceof \Carbon\Carbon ? $usuarioVista->personalSalud->fecha_ing->format('d/m/Y') : \Carbon\Carbon::parse($usuarioVista->personalSalud->fecha_ing)->format('d/m/Y') }}
 </p>
 </div>
 @endif
 @if(in_array($vistaRoleKey, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']) && $usuarioVista->personalAdmin?->fecha_ingreso)
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Fecha de Ingreso</p>
 <p class="mt-1 text-sm font-bold text-parrafo">
 {{ $usuarioVista->personalAdmin->fecha_ingreso instanceof \Carbon\Carbon ? $usuarioVista->personalAdmin->fecha_ingreso->format('d/m/Y') : \Carbon\Carbon::parse($usuarioVista->personalAdmin->fecha_ingreso)->format('d/m/Y') }}
 </p>
 </div>
 @endif
 </div>
 </div>

 {{-- Contacto e Info Extra --}}
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div class="rounded-[1.5rem] bg-fondo-card/60 border border-borde-suave p-6 shadow-sm">
 <h4 class="mb-4 flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-phone-call text-lg"></i> Contacto
 </h4>
 <div class="space-y-4">
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Teléfono</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $usuarioVista->codigo_telefono }} {{ $usuarioVista->telefono ?? 'Sin registrar' }}</p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Correo Electrónico</p>
 <p class="mt-1 text-sm font-bold text-parrafo lowercase break-all">{{ $usuarioVista->correo ?: 'Sin registrar' }}</p>
 </div>
 </div>
 </div>

 <div class="rounded-[1.5rem] bg-fondo-card/60 border border-borde-suave p-6 shadow-sm">
 <h4 class="mb-4 flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-info text-lg"></i> Registro
 </h4>
 <div class="space-y-4">
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Creado en el sistema</p>
 <p class="mt-1 text-sm font-bold text-parrafo">
 {{ $usuarioVista->created_at ? $usuarioVista->created_at->format('d/m/Y H:i') : 'Sin dato' }}
 </p>
 </div>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Última actualización</p>
 <p class="mt-1 text-sm font-bold text-parrafo">
 {{ $usuarioVista->updated_at ? $usuarioVista->updated_at->format('d/m/Y H:i') : 'Sin dato' }}
 </p>
 </div>
 </div>
 </div>
 </div>

 @if($usuarioVista->observaciones)
 <div class="rounded-[1.5rem] bg-fondo-panel border border-borde-focus p-6 shadow-sm">
 <h4 class="mb-2 flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-warning-circle text-lg"></i> Observaciones
 </h4>
 <p class="text-sm font-semibold text-parrafo/80 leading-relaxed">
 {{ $usuarioVista->observaciones }}
 </p>
 </div>
 @endif
 </div>
 </div>
 </div>

 {{-- Footer Acciones --}}
 <footer class="flex items-center justify-end gap-3 border-t border-borde-suave bg-fondo-panel px-6 py-4 backdrop-blur-xl shrink-0">
 <button type="button" wire:click="cerrarVistaCompleta" class="rounded-full bg-fondo-card/50 px-6 py-2.5 text-xs font-bold text-parrafo transition hover:bg-fondo-card active:scale-95 shadow-sm">
 Cerrar
 </button>
 @if($usuarioVista->estado === 'ACTIVO')
 <button type="button" wire:click="editarUsuario('{{ $usuarioVista->cod_usu }}')" class="flex items-center gap-2 rounded-full bg-boton-acento px-6 py-2.5 text-xs font-bold text-inverso transition hover:bg-fondo-panel active:scale-95 shadow-lg">
 <i class="ph-bold ph-pencil-simple text-sm"></i> Editar
 </button>
 @endif
 </footer>
 </div>
 </div>
 @endif
