@if($mostrarFichaRapida && $usuarioFicha)
 <div class="fixed inset-0 z-[2147483640] flex justify-end" x-data x-transition>
 {{-- Overlay --}}
 <div class="absolute inset-0 bg-black/35 backdrop-blur-sm" wire:click="cerrarFichaRapida"></div>

 {{-- Panel lateral --}}
 <aside class="relative z-10 flex h-screen w-full flex-col overflow-hidden border-l border-borde-suave bg-fondo-app shadow-[0_0_60px_rgba(0,0,0,0.3)] sm:max-w-xl lg:max-w-2xl"
 style="animation: slideInRight 0.3s ease-out">

 {{-- Header ficha --}}
 <header class="flex shrink-0 items-center justify-between border-b border-borde-suave bg-fondo-panel px-6 py-4 backdrop-blur-xl">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-boton-principal text-inverso shadow-lg">
 <i class="ph-bold ph-clipboard-text text-lg"></i>
 </div>
 <div>
 <h2 class="text-base font-extrabold text-parrafo">Ficha <span class="text-boton-acento">Rápida</span></h2>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Resumen institucional</p>
 </div>
 </div>
 <button type="button" wire:click="cerrarFichaRapida"
 class="group flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-app text-parrafo transition-all hover:bg-boton-acento hover:text-inverso active:scale-90 shadow-sm">
 <i class="ph-bold ph-x text-base transition group-hover:rotate-90"></i>
 </button>
 </header>

 {{-- Contenido scrollable --}}
 <div class="flex-1 overflow-y-auto custom-scrollbar px-6 py-6 space-y-5">

 {{-- Avatar y nombre --}}
 @php
 $fichaRoleName = $usuarioFicha->getRoleNames()->first() ?? 'sin_rol';
 $fichaRoleKey = strtolower($fichaRoleName);
 $fichaNombreCompleto = trim(($usuarioFicha->nombres ?? '') . ' ' . ($usuarioFicha->ap_paterno ?? '') . ' ' . ($usuarioFicha->ap_materno ?? ''));
 $fichaInicial = mb_substr(trim($usuarioFicha->nombres ?? 'U'), 0, 1);

 $fichaAreaDisplay = $usuarioFicha->areaInstitucional?->nombre ?? match($fichaRoleKey) {
 'super_admin', 'admin' => 'Administración del sistema',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'Área de salud',
 'superadministrador', 'administrador' => 'Área administrativa',
 'FAMILIAR' => 'Familiar autorizado',
 'VOLUNTARIO' => 'Voluntariado',
 default => 'Sin área asignada'
 };

 $fichaPerfilDetalle = match($fichaRoleKey) {
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => $usuarioFicha->personalSalud?->especialidad?->nombre ?? 'Personal de salud',
 'superadministrador', 'administrador' => $usuarioFicha->personalAdmin?->cargoAdmin?->nombre ?? $usuarioFicha->personalAdmin?->cargo ?? 'Personal administrativo',
 'super_admin', 'admin' => 'Administrador del sistema',
 'VOLUNTARIO' => 'Voluntario institucional',
 'FAMILIAR' => 'Familiar autorizado',
 default => strtoupper(str_replace('_', ' ', $fichaRoleName))
 };

 $fichaFoto = null;
 if (!empty($usuarioFicha->foto_de_perfil)) {
 $fichaFoto = \Illuminate\Support\Facades\Storage::url($usuarioFicha->foto_de_perfil);
 } elseif (!empty($usuarioFicha->profile_photo_url)) {
 $fichaFoto = $usuarioFicha->profile_photo_url;
 }
 @endphp

 <div class="flex flex-col items-center text-center">
 <div class="relative">
 @if($fichaFoto)
 <img src="{{ $fichaFoto }}" alt="{{ $fichaNombreCompleto }}"
 class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white shadow-[0_6px_16px_rgba(47,62,92,0.15)]">
 @else
 <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-boton-principal text-2xl font-black text-inverso ring-2 ring-white shadow-[0_6px_16px_rgba(47,62,92,0.15)]">
 {{ strtoupper($fichaInicial) }}
 </div>
 @endif
 <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full border-[3px] border-white {{ $usuarioFicha->estado === 'ACTIVO' ? 'bg-estado-exitoBg' : 'bg-fondo-panel' }}"></span>
 </div>

 <h3 class="mt-4 text-xl font-extrabold uppercase text-parrafo leading-tight">{{ $fichaNombreCompleto }}</h3>
 <p class="mt-1 text-sm font-semibold lowercase text-parrafo/55">{{ $usuarioFicha->correo ?: 'Sin correo' }}</p>

 <div class="mt-3 flex flex-wrap justify-center gap-2">
 <span class="rounded-full {{ $usuarioFicha->estado === 'ACTIVO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-fondo-panel text-meta' }} px-3 py-1 text-[10px] font-bold uppercase">
 {{ $usuarioFicha->estado }}
 </span>
 <span class="rounded-full bg-fondo-panel px-3 py-1 text-[10px] font-bold uppercase text-parrafo">
 {{ match($fichaRoleKey) {
 'superadministrador', 'administrador' => 'PERSONAL ADMINISTRATIVO',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'PERSONAL DE SALUD',
 'VOLUNTARIO' => 'VOLUNTARIO',
 'FAMILIAR' => 'FAMILIAR AUTORIZADO',
 default => strtoupper(str_replace('_', ' ', $fichaRoleName)),
 } }}
 </span>
 </div>
 </div>

 {{-- Datos en bloques --}}
 <div class="space-y-3">
 <div class="rounded-2xl border border-borde-suave bg-fondo-card/45 p-4 space-y-3">
 <h4 class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-buildings text-base"></i> Perfil Institucional
 </h4>
 <div class="grid grid-cols-2 gap-3">
 <div>
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/35">Área</p>
 <p class="text-xs font-bold text-parrafo">{{ $fichaAreaDisplay }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/35">Perfil</p>
 <p class="text-xs font-bold text-parrafo uppercase">{{ $fichaPerfilDetalle }}</p>
 </div>
 </div>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-card/45 p-4 space-y-3">
 <h4 class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-phone-call text-base"></i> Contacto
 </h4>
 <div class="grid grid-cols-2 gap-3">
 <div>
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/35">Teléfono</p>
 <p class="text-xs font-bold text-parrafo">{{ $usuarioFicha->codigo_telefono }} {{ $usuarioFicha->telefono ?? 'Sin registrar' }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/35">Correo</p>
 <p class="text-xs font-bold text-parrafo lowercase break-all">{{ $usuarioFicha->correo ?: 'Sin registrar' }}</p>
 </div>
 </div>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-card/45 p-4 space-y-3">
 <h4 class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-clock text-base"></i> Acceso y registro
 </h4>
 <div class="grid grid-cols-2 gap-3">
 <div>
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/35">Último acceso</p>
 <p class="text-xs font-bold text-parrafo">
 {{ $usuarioFicha->ultimo_acceso ? $usuarioFicha->ultimo_acceso->format('d/m/Y H:i') : 'Sin registro' }}
 </p>
 </div>
 <div>
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/35">Fecha de creación</p>
 <p class="text-xs font-bold text-parrafo">
 {{ $usuarioFicha->created_at ? $usuarioFicha->created_at->format('d/m/Y') : 'Sin dato' }}
 </p>
 </div>
 <div>
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/35">Acceso sistema</p>
 <p class="text-xs font-bold {{ $usuarioFicha->acceso_sistema === 'HABILITADO' ? 'text-estado-exito' : 'text-boton-acento' }}">
 {{ $usuarioFicha->acceso_sistema }}
 </p>
 </div>
 </div>
 </div>
 </div>
 </div>

 {{-- Footer acciones rápidas --}}
 <footer class="shrink-0 border-t border-borde-suave bg-fondo-panel px-6 py-4 backdrop-blur-xl">
 <div class="flex flex-wrap items-center justify-center gap-2">
 <button type="button"
 wire:click="abrirVistaCompleta('{{ $usuarioFicha->cod_usu }}')"
 class="inline-flex items-center gap-2 rounded-full bg-boton-principal px-5 py-2.5 text-[10px] font-bold text-inverso shadow-lg transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-eye"></i> Ver completo
 </button>

 @can('usuarios.editar')
 @if($usuarioFicha->estado === 'ACTIVO')
 <button type="button"
 wire:click="editarUsuario('{{ $usuarioFicha->cod_usu }}')"
 onclick="@this.cerrarFichaRapida()"
 class="inline-flex items-center gap-2 rounded-full bg-boton-acento px-5 py-2.5 text-[10px] font-bold text-inverso shadow-lg transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-pencil-simple"></i> Editar
 </button>
 @endif
 @endcan

 @can('usuarios.cambiar_estado')
 @if($usuarioFicha->cod_usu !== auth()->id())
 <button wire:click="toggleEstado('{{ $usuarioFicha->cod_usu }}')"
 wire:confirm="¿Desea cambiar el estado de este usuario?"
 class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-[10px] font-bold shadow-lg transition active:scale-95
 {{ $usuarioFicha->estado === 'ACTIVO'
 ? 'bg-fondo-panel text-parrafo hover:bg-fondo-panel hover:text-inverso'
 : 'bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg hover:text-inverso' }}">
 <i class="ph-bold {{ $usuarioFicha->estado === 'ACTIVO' ? 'ph-user-minus' : 'ph-user-plus' }}"></i>
 {{ $usuarioFicha->estado === 'ACTIVO' ? 'Inactivar' : 'Activar' }}
 </button>
 @endif
 @endcan
 </div>
 </footer>
 </aside>
 </div>
 @endif
