<div x-data="{ vista: 'cards' }">
 @if($modoVista === 'detalle' && $usuarioDetalle)
 @php
 $rolKey = $usuarioDetalle->roles->first()?->name;
 $rolDisplay = '';
 if (in_array($rolKey, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
 $rolDisplay = 'PERSONAL ADMINISTRATIVO';
 } elseif (in_array($rolKey, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
 $rolDisplay = 'PERSONAL DE SALUD';
 } elseif ($rolKey === 'VOLUNTARIO') {
 $rolDisplay = 'VOLUNTARIO';
 } elseif ($rolKey === 'FAMILIAR') {
 $rolDisplay = 'FAMILIAR AUTORIZADO';
 } else {
 $rolDisplay = mb_strtoupper(str_replace('_', ' ', $rolKey), 'UTF-8');
 }

 // Documentación
 $documentacionService = app(\App\Services\Usuarios\DocumentacionUsuarioService::class);
 $checklist = $documentacionService->obtenerChecklistUsuario($usuarioDetalle);
 $avance = $documentacionService->calcularAvanceDocumental($usuarioDetalle);

 // Dirección
 $partesDireccion = [];
 if (!empty($usuarioDetalle->departamento_domicilio)) {
 $partesDireccion[] ="DEPARTAMENTO:" . mb_strtoupper($usuarioDetalle->departamento_domicilio, 'UTF-8');
 }
 if (!empty($usuarioDetalle->municipio_domicilio)) {
 $partesDireccion[] ="MUNICIPIO:" . mb_strtoupper($usuarioDetalle->municipio_domicilio, 'UTF-8');
 }
 if (!empty($usuarioDetalle->zona_domicilio)) {
 $partesDireccion[] ="ZONA:" . mb_strtoupper($usuarioDetalle->zona_domicilio, 'UTF-8');
 }
 if (!empty($usuarioDetalle->calle)) {
 $partesDireccion[] ="CALLE/AV.:" . mb_strtoupper($usuarioDetalle->calle, 'UTF-8');
 }
 if (!empty($usuarioDetalle->nro_domicilio)) {
 $partesDireccion[] ="NRO.:" . mb_strtoupper($usuarioDetalle->nro_domicilio, 'UTF-8');
 }
 if (!empty($usuarioDetalle->referencia_domicilio)) {
 $partesDireccion[] ="REF.:" . mb_strtoupper($usuarioDetalle->referencia_domicilio, 'UTF-8');
 }
 
 if (empty($partesDireccion)) {
 $direccionCompleta = !empty($usuarioDetalle->direccion) ? mb_strtoupper($usuarioDetalle->direccion, 'UTF-8') : 'SIN DIRECCIÓN REGISTRADA';
 } else {
 $direccionCompleta = implode('; ', $partesDireccion);
 }
 @endphp

 {{-- DETALLE DE FICHA INSTITUCIONAL --}}
 <div class="space-y-6 animate-in fade-in duration-300">
 {{-- CABECERA CON ACCIÓN DE RETORNO --}}
 <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between rounded-[1.6rem] border border-borde/55 bg-fondo-panel px-6 py-5 shadow-sm backdrop-blur-xl">
 <div class="flex items-center gap-3">
 <button type="button" 
 wire:click="volverAlListadoUsuarios" 
 class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-card/80 border border-borde-suave text-parrafo transition hover:bg-boton-principal hover:text-inverso active:scale-95">
 <i class="ph-bold ph-arrow-left text-lg"></i>
 </button>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-boton-acento">Ficha de Personal & Familiares</p>
 <h2 class="text-2xl font-black text-parrafo uppercase">Ficha institucional del usuario</h2>
 </div>
 </div>
 <div class="flex flex-wrap items-center gap-2">
 @can('usuarios.editar')
 <button type="button" 
 wire:click="abrirEdicionDesdeDetalle"
 class="inline-flex h-10 items-center gap-2 rounded-full bg-boton-acento px-5 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-pencil-simple"></i>
 Editar Usuario
 </button>
 @endcan
 <button type="button" 
 wire:click="volverAlListadoUsuarios"
 class="inline-flex h-10 items-center gap-2 rounded-full bg-fondo-card/60 border border-borde-suave px-5 text-xs font-bold text-parrafo transition hover:bg-fondo-card active:scale-95">
 <i class="ph-bold ph-list"></i>
 Volver al Listado
 </button>
 </div>
 </header>

 {{-- TARJETA PRINCIPAL DEL USUARIO --}}
 <div class="rounded-[1.8rem] border border-borde/45 bg-fondo-panel p-6 shadow-sm backdrop-blur-md">
 <div class="flex flex-col md:flex-row items-center gap-6">
 <div class="relative flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-fondo-panel border-2 border-borde-suave">
 @if($usuarioDetalle->foto_de_perfil)
 <img src="{{ asset('storage/' . $usuarioDetalle->foto_de_perfil) }}" alt="Foto de perfil" class="h-full w-full rounded-full object-cover">
 @else
 <i class="ph-bold ph-user text-4xl text-parrafo/35"></i>
 @endif
 </div>
 
 <div class="flex-1 text-center md:text-left space-y-2">
 <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
 <h3 class="text-xl font-extrabold text-parrafo uppercase">{{ $usuarioDetalle->nombres }} {{ $usuarioDetalle->ap_paterno }} {{ $usuarioDetalle->ap_materno }}</h3>
 <span class="inline-block rounded-full bg-boton-principal px-2.5 py-0.5 text-[9px] font-bold tracking-widest text-inverso uppercase">
 {{ $rolDisplay }}
 </span>
 </div>
 
 <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs pt-2">
 <div>
 <span class="font-black text-meta uppercase tracking-wide block">Estado del Perfil:</span>
 <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[9px] font-bold uppercase {{ $usuarioDetalle->estado === 'ACTIVO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $usuarioDetalle->estado }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wide block">Acceso al Sistema:</span>
 <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[9px] font-bold uppercase {{ $usuarioDetalle->acceso_sistema === 'HABILITADO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $usuarioDetalle->acceso_sistema }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wide block">Estado Documental:</span>
 <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[9px] font-bold uppercase {{ $avance['porcentaje_avance'] == 100 ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $avance['porcentaje_avance'] == 100 ? 'COMPLETA' : 'PENDIENTE' }} ({{ $avance['porcentaje_avance'] }}%)
 </span>
 </div>
 @if($usuarioDetalle->created_at)
 <div>
 <span class="font-black text-meta uppercase tracking-wide block">Plazo Documental:</span>
 <span class="font-bold text-boton-acento block mt-1">LÍMITE 48 HORAS</span>
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>

 {{-- BARRA SUPERIOR DE SECCIONES --}}
 <nav class="sticky top-[72px] z-20 -mx-4 px-4 py-3 bg-fondo-panel border-b border-borde-suave backdrop-blur-md overflow-x-auto custom-scrollbar flex gap-2">
 <button type="button"
 wire:click="cambiarSeccionDetalle('resumen')"
 class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl px-4 text-xs font-bold uppercase transition active:scale-95 shadow-sm border-2 {{ $seccionActivaDetalle === 'resumen' ? 'bg-estado-peligroBg border-borde-focus text-boton-acento' : 'bg-fondo-card/60 border-borde-suave text-apoyo hover:bg-fondo-card' }}">
 <i class="ph-bold ph-list-bullets mr-1.5 text-sm"></i>
 Resumen
 </button>
 <button type="button"
 wire:click="cambiarSeccionDetalle('identidad')"
 class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl px-4 text-xs font-bold uppercase transition active:scale-95 shadow-sm border-2 {{ $seccionActivaDetalle === 'identidad' ? 'bg-estado-peligroBg border-borde-focus text-boton-acento' : 'bg-fondo-card/60 border-borde-suave text-apoyo hover:bg-fondo-card' }}">
 <i class="ph-bold ph-identification-card mr-1.5 text-sm"></i>
 Identidad
 </button>
 <button type="button"
 wire:click="cambiarSeccionDetalle('contacto')"
 class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl px-4 text-xs font-bold uppercase transition active:scale-95 shadow-sm border-2 {{ $seccionActivaDetalle === 'contacto' ? 'bg-estado-peligroBg border-borde-focus text-boton-acento' : 'bg-fondo-card/60 border-borde-suave text-apoyo hover:bg-fondo-card' }}">
 <i class="ph-bold ph-phone mr-1.5 text-sm"></i>
 Contacto
 </button>
 <button type="button"
 wire:click="cambiarSeccionDetalle('perfil')"
 class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl px-4 text-xs font-bold uppercase transition active:scale-95 shadow-sm border-2 {{ $seccionActivaDetalle === 'perfil' ? 'bg-estado-peligroBg border-borde-focus text-boton-acento' : 'bg-fondo-card/60 border-borde-suave text-apoyo hover:bg-fondo-card' }}">
 <i class="ph-bold ph-user-gear mr-1.5 text-sm"></i>
 Perfil
 </button>
 <button type="button"
 wire:click="cambiarSeccionDetalle('documentos')"
 class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl px-4 text-xs font-bold uppercase transition active:scale-95 shadow-sm border-2 {{ $seccionActivaDetalle === 'documentos' ? 'bg-estado-peligroBg border-borde-focus text-boton-acento' : 'bg-fondo-card/60 border-borde-suave text-apoyo hover:bg-fondo-card' }}">
 <i class="ph-bold ph-files mr-1.5 text-sm"></i>
 Documentos
 </button>
 <button type="button"
 wire:click="cambiarSeccionDetalle('acceso')"
 class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl px-4 text-xs font-bold uppercase transition active:scale-95 shadow-sm border-2 {{ $seccionActivaDetalle === 'acceso' ? 'bg-estado-peligroBg border-borde-focus text-boton-acento' : 'bg-fondo-card/60 border-borde-suave text-apoyo hover:bg-fondo-card' }}">
 <i class="ph-bold ph-shield-check mr-1.5 text-sm"></i>
 Acceso
 </button>
 @if($rolKey === 'FAMILIAR')
 <button type="button"
 wire:click="cambiarSeccionDetalle('vinculo')"
 class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl px-4 text-xs font-bold uppercase transition active:scale-95 shadow-sm border-2 {{ $seccionActivaDetalle === 'vinculo' ? 'bg-estado-peligroBg border-borde-focus text-boton-acento' : 'bg-fondo-card/60 border-borde-suave text-apoyo hover:bg-fondo-card' }}">
 <i class="ph-bold ph-users-three mr-1.5 text-sm"></i>
 Vínculo
 </button>
 @endif
 </nav>

 {{-- SECCIONES DE INFORMACIÓN --}}
 <div class="space-y-6">
 {{-- SECCION RESUMEN --}}
 @if($seccionActivaDetalle === 'resumen')
 <section id="seccion-resumen" class="animate-in fade-in duration-200 rounded-[1.8rem] border border-borde-suave bg-fondo-card/70 p-6 shadow-sm backdrop-blur-md space-y-4">
 <h4 class="flex items-center gap-2 border-b border-borde-suave pb-2 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-list-bullets text-lg"></i> Resumen del Usuario
 </h4>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Nombre completo:</span>
 <span class="font-bold text-parrafo uppercase text-sm">{{ $usuarioDetalle->nombres }} {{ $usuarioDetalle->ap_paterno }} {{ $usuarioDetalle->ap_materno }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Tipo de usuario:</span>
 <span class="font-bold text-parrafo uppercase text-sm">{{ $rolDisplay }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Correo Electrónico:</span>
 <span class="font-bold text-parrafo text-sm lowercase">{{ $usuarioDetalle->correo ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Celular / Teléfono:</span>
 <span class="font-bold text-parrafo text-sm">{{ $usuarioDetalle->codigo_telefono }} {{ $usuarioDetalle->telefono ?: 'No registrado' }}</span>
 </div>
 <div class="md:col-span-2">
 <span class="font-black text-meta uppercase tracking-wider block">Dirección completa:</span>
 <span class="font-bold text-parrafo text-sm uppercase leading-relaxed">{{ $direccionCompleta }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Estado del Perfil:</span>
 <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $usuarioDetalle->estado === 'ACTIVO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $usuarioDetalle->estado }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Acceso al Sistema:</span>
 <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $usuarioDetalle->acceso_sistema === 'HABILITADO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $usuarioDetalle->acceso_sistema }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Estado Documental:</span>
 <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $avance['porcentaje_avance'] == 100 ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $avance['porcentaje_avance'] == 100 ? 'COMPLETA' : 'PENDIENTE' }} ({{ $avance['porcentaje_avance'] }}%)
 </span>
 </div>
 @if($usuarioDetalle->created_at)
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Fecha Límite Presentación:</span>
 <span class="font-bold text-boton-acento text-sm">
 {{ $usuarioDetalle->created_at->addHours(48)->format('d/m/Y H:i') }} (Límite 48 Horas)
 </span>
 </div>
 @endif
 </div>
 </section>
 @endif

 {{-- SECCION IDENTIDAD --}}
 @if($seccionActivaDetalle === 'identidad')
 <section id="seccion-identidad" class="animate-in fade-in duration-200 rounded-[1.8rem] border border-borde-suave bg-fondo-card/70 p-6 shadow-sm backdrop-blur-md space-y-4">
 <h4 class="flex items-center gap-2 border-b border-borde-suave pb-2 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-identification-card text-lg"></i> Datos de Identidad
 </h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Nombres:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->nombres }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Apellido Paterno:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->ap_paterno ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Apellido Materno:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->ap_materno ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Género:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->genero ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Fecha de Nacimiento:</span>
 <span class="font-bold text-parrafo">
 {{ $usuarioDetalle->fecha_nacimiento ? $usuarioDetalle->fecha_nacimiento->format('d/m/Y') : 'No registrado' }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Edad:</span>
 <span class="font-bold text-parrafo">
 {{ $usuarioDetalle->fecha_nacimiento ? $usuarioDetalle->fecha_nacimiento->age . ' años' : 'No registrado' }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Tipo de Documento:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->tipo_documento ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Número de Documento:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->numero_documento ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">País de Emisión:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->pais_documento ?: 'No registrado' }}</span>
 </div>
 @if($usuarioDetalle->expedido)
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Expedido en:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->expedido }}</span>
 </div>
 @endif
 </div>
 </section>
 @endif

 {{-- SECCION CONTACTO --}}
 @if($seccionActivaDetalle === 'contacto')
 <section id="seccion-contacto" class="animate-in fade-in duration-200 rounded-[1.8rem] border border-borde-suave bg-fondo-card/70 p-6 shadow-sm backdrop-blur-md space-y-4">
 <h4 class="flex items-center gap-2 border-b border-borde-suave pb-2 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-phone text-lg"></i> Contacto y Dirección
 </h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Correo Electrónico:</span>
 <span class="font-bold text-parrafo lowercase break-all">{{ $usuarioDetalle->correo ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Celular / Teléfono:</span>
 <span class="font-bold text-parrafo">
 {{ $usuarioDetalle->codigo_telefono }} {{ $usuarioDetalle->telefono ?: 'No registrado' }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Contacto de Emergencia:</span>
 <span class="font-bold text-parrafo uppercase">
 {{ $usuarioDetalle->contacto_emergencia ?: 'No registrado' }} 
 @if($usuarioDetalle->ap_paterno_emergencia || $usuarioDetalle->ap_materno_emergencia)
 {{ $usuarioDetalle->ap_paterno_emergencia }} {{ $usuarioDetalle->ap_materno_emergencia }}
 @endif
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Vínculo de Emergencia:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->parentesco_emergencia ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Celular de Emergencia:</span>
 <span class="font-bold text-parrafo">{{ $usuarioDetalle->celular_emergencia ?: 'No registrado' }}</span>
 </div>
 <div class="md:col-span-3 border-t border-borde/10 pt-3">
 <span class="font-black text-meta uppercase tracking-wider block">Dirección Domiciliaria Completa:</span>
 <span class="font-bold text-parrafo uppercase leading-relaxed block mt-1">{{ $direccionCompleta }}</span>
 </div>
 </div>
 </section>
 @endif

 {{-- SECCION PERFIL --}}
 @if($seccionActivaDetalle === 'perfil')
 <section id="seccion-perfil" class="animate-in fade-in duration-200 rounded-[1.8rem] border border-borde-suave bg-fondo-card/70 p-6 shadow-sm backdrop-blur-md space-y-4">
 <h4 class="flex items-center gap-2 border-b border-borde-suave pb-2 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-user-gear text-lg"></i> Perfil Institucional
 </h4>
 
 @if(in_array($rolKey, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']))
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Área Institucional:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->areaInstitucional?->nombre ?: 'No asignada' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Cargo Administrativo:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->personalAdmin?->cargoAdmin?->nombre ?: 'No registrado' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Fecha de Ingreso:</span>
 <span class="font-bold text-parrafo">
  @php
   $fechaIngresoAdmin = $usuarioDetalle->personalAdmin->fecha_ingreso ?? $usuarioDetalle->created_at ?? null;
  @endphp
  {{ $fechaIngresoAdmin ? \Carbon\Carbon::parse($fechaIngresoAdmin)->format('d/m/Y') : 'No registrada' }}
 </span>
 </div>
 </div>
 @elseif(in_array($rolKey, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']))
 <div class="space-y-4">
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Área Institucional:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->areaInstitucional?->nombre ?: 'No asignada' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Especialidad de Salud:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->personalSalud?->especialidad?->nombre ?: 'No asignada' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Institución de Formación:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->personalSalud?->institucion_formacion ?: 'No especificada' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Fecha de Ingreso:</span>
 <span class="font-bold text-parrafo">
  @php
   $fechaIngresoSalud = $usuarioDetalle->personalSalud->fecha_ingreso ?? $usuarioDetalle->created_at ?? null;
  @endphp
  {{ $fechaIngresoSalud ? \Carbon\Carbon::parse($fechaIngresoSalud)->format('d/m/Y') : 'No registrada' }}
 </span>
 </div>
 </div>
 <div class="rounded-xl bg-fondo-panel border border-borde-focus p-4">
 <p class="text-xs font-semibold text-boton-acento flex items-center gap-1.5 leading-normal">
 <i class="ph-bold ph-info text-base shrink-0"></i>
 Aviso: La matrícula profesional y documentos de respaldo se gestionan en la sección de documentación.
 </p>
 </div>
 </div>
 @elseif($rolKey === 'VOLUNTARIO')
 @php
 $volDetalle = $usuarioDetalle->voluntarios->first();
 @endphp
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Área de Apoyo:</span>
 <span class="font-bold text-parrafo uppercase">{{ $volDetalle?->area_apoyo_preferente ?: 'No especificada' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Disponibilidad Inicial:</span>
 <span class="font-bold text-parrafo uppercase">{{ $volDetalle?->disponibilidad_inicial ?: 'No registrada' }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Fecha de Ingreso:</span>
 <span class="font-bold text-parrafo">
 {{ $volDetalle?->fecha_ingreso ? \Carbon\Carbon::parse($volDetalle->fecha_ingreso)->format('d/m/Y') : 'No registrada' }}
 </span>
 </div>
 </div>
 @elseif($rolKey === 'FAMILIAR')
 <div class="space-y-2 text-xs font-bold text-apoyo">
 <p class="text-sm font-bold text-parrafo uppercase flex items-center gap-1.5">
 <i class="ph-bold ph-info text-base text-boton-acento"></i>
 Usuario externo autorizado para consulta limitada de información.
 </p>
 <p class="text-xs font-semibold text-meta">
 La relación con el adulto mayor se gestiona en la pestaña Vínculo.
 </p>
 </div>
 @else
 <div class="py-4 text-center">
 <span class="text-xs font-bold text-meta uppercase tracking-widest">Sin datos adicionales de rol</span>
 </div>
 @endif
 </section>
 @endif

 {{-- SECCION DOCUMENTOS --}}
 @if($seccionActivaDetalle === 'documentos')
 <section id="seccion-documentacion" class="animate-in fade-in duration-200 rounded-[1.8rem] border border-borde-suave bg-fondo-card/70 p-6 shadow-sm backdrop-blur-md space-y-4">
 <h4 class="flex items-center gap-2 border-b border-borde-suave pb-2 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-files text-lg"></i> Documentación del Usuario
 </h4>
 
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
 <div class="space-y-3">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Estado Documental:</span>
 <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $avance['porcentaje_avance'] == 100 ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $avance['porcentaje_avance'] == 100 ? 'COMPLETA' : 'PENDIENTE' }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Plazo de Presentación:</span>
 <span class="font-bold text-parrafo">48 HORAS</span>
 </div>
 @if($usuarioDetalle->created_at)
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Fecha Límite de Recepción:</span>
 <span class="font-bold text-boton-acento">{{ $usuarioDetalle->created_at->addHours(48)->format('d/m/Y H:i') }}</span>
 </div>
 @endif
 
 <div class="pt-2 flex flex-col gap-2">
 <div class="pt-2 flex flex-col gap-2">
 <a href="{{ route('admin.usuarios.documentos.pdf', $usuarioDetalle->cod_usu) }}"
 target="_blank"
 class="w-full inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-boton-principal text-inverso text-[10px] font-bold uppercase tracking-wider transition hover:bg-boton-acento active:scale-95 shadow-sm">
 <i class="ph-bold ph-printer"></i>
 Paquete Documental
 </a>
 <button type="button" 
 onclick="enviarPaqueteCorreo('{{ $usuarioDetalle->cod_usu }}', '{{ $usuarioDetalle->correo }}')"
 class="w-full inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-fondo-panel text-[10px] font-bold text-parrafo border border-borde-fuerte transition hover:bg-boton-principal hover:text-inverso active:scale-95 shadow-sm">
 <i class="ph-bold ph-envelope"></i> Enviar Correo
 </button>
 <button type="button"
 @click="Swal.fire({ icon: 'info', title: '¿Cómo subir?', text: 'Seleccione el ícono de subida en la lista de requisitos a la derecha para cargar el archivo correspondiente.', confirmButtonColor: '#2F3E5C', customClass: { popup: 'rounded-[1.5rem]' } })"
 class="w-full inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-fondo-card text-xs font-bold text-boton-acento border border-borde-focus transition hover:bg-boton-acento hover:text-inverso active:scale-95 shadow-md mt-2">
 <i class="ph-bold ph-upload-simple"></i>
 Subir Requisitos
 </button>
 </div>
 </div>
 </div>
 
 <div class="md:col-span-2">
 <span class="font-black text-meta uppercase tracking-wider block mb-2">Lista de Requisitos Obligatorios:</span>
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
 @foreach($checklist as $item)
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl border border-borde/25 bg-fondo-card/40 transition hover:bg-fondo-card/60">
 <div class="min-w-0 flex-1">
 <p class="font-black text-parrafo truncate uppercase leading-tight" title="{{ $item['nombre'] }}">{{ $item['nombre'] }}</p>
 <p class="text-[8px] text-meta font-black uppercase tracking-wider mt-0.5 flex items-center gap-1.5">
 <span>{{ $item['obligatorio'] ? 'Obligatorio' : 'Opcional' }}</span>
 @if($item['requiere_vencimiento'] && $item['cargado'] && $item['documento']->fecha_vencimiento)
 <span class="text-boton-acento">| Vence: {{ \Carbon\Carbon::parse($item['documento']->fecha_vencimiento)->format('d/m/Y') }}</span>
 @endif
 </p>
 </div>
 <div class="flex items-center gap-2 shrink-0">
 <!-- Badge de estado -->
 <div>
 @if($item['cargado'])
 @if($item['estado'] === 'VALIDADO')
 <span class="inline-flex items-center gap-1 text-[8px] font-black text-estado-exito bg-estado-exitoBg px-2 py-0.5 rounded-full uppercase">
 <i class="ph-bold ph-check-circle text-[10px]"></i> Validado
 </span>
 @elseif($item['estado'] === 'OBSERVADO')
 <span class="inline-flex items-center gap-1 text-[8px] font-black text-boton-acento bg-estado-peligroBg px-2 py-0.5 rounded-full uppercase" title="Motivo: {{ $item['documento']->motivo_observacion }}">
 <i class="ph-bold ph-warning-circle text-[10px]"></i> Observado
 </span>
 @else
 <span class="inline-flex items-center gap-1 text-[8px] font-black text-parrafo/75 bg-fondo-panel px-2 py-0.5 rounded-full uppercase">
 <i class="ph-bold ph-clock text-[10px]"></i> Cargado
 </span>
 @endif
 @else
 <span class="inline-flex items-center gap-1 text-[8px] font-black text-meta bg-fondo-panel px-2 py-0.5 rounded-full uppercase">
 <i class="ph-bold ph-minus-circle text-[10px]"></i> Faltante
 </span>
 @endif
 </div>

 <!-- Acciones de archivo -->
 <div class="flex items-center gap-1">
 @if($item['cargado'])
 <!-- Ver documento -->
 <a href="{{ Storage::url($item['documento']->archivo) }}" target="_blank"
 class="flex h-7 w-7 items-center justify-center rounded-lg bg-fondo-panel text-parrafo transition hover:bg-boton-principal hover:text-inverso"
 title="Ver Documento">
 <i class="ph-bold ph-eye text-xs"></i>
 </a>
 
 <!-- Reemplazar documento -->
 <button type="button" wire:click="abrirModalSubirDoc('{{ $item['cod_tipo_doc'] }}')"
 class="flex h-7 w-7 items-center justify-center rounded-lg bg-estado-peligroBg text-boton-acento transition hover:bg-boton-acento hover:text-inverso"
 title="Reemplazar Archivo">
 <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
 </button>
 @else
 <!-- Subir por primera vez -->
 <button type="button" wire:click="abrirModalSubirDoc('{{ $item['cod_tipo_doc'] }}')"
 class="flex h-7 w-7 items-center justify-center rounded-lg bg-estado-exitoBg text-estado-exito transition hover:bg-estado-exitoBg hover:text-inverso font-black"
 title="Subir Documento">
 <i class="ph-bold ph-upload-simple text-xs"></i>
 </button>
 @endif
 </div>
 </div>
 </div>
 @endforeach
 </div>
 </div>
 </div>

 @php
 $docService = app(\App\Services\Usuarios\DocumentosUsuarioService::class);
 $docsInstitucionales = $docService->documentosGeneradosPorRol($rolKey);
 @endphp

 <div class="mt-8 border-t border-borde-suave pt-6">
 <h4 class="flex items-center gap-2 mb-4 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-folder-open text-lg"></i> Documentos Generados
 </h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
 @foreach($docsInstitucionales as $docInst)
 <div class="flex flex-col p-4 rounded-[1.2rem] border border-borde-suave bg-fondo-card shadow-sm hover:shadow-md transition">
 <div class="flex items-start gap-3">
 <div class="flex-shrink-0 h-10 w-10 bg-estado-peligroBg text-boton-acento flex items-center justify-center rounded-xl">
 <i class="ph-fill ph-file-pdf text-xl"></i>
 </div>
 <div class="flex-1 min-w-0">
 <p class="text-xs font-bold text-parrafo uppercase truncate" title="{{ $docInst['nombre'] }}">{{ $docInst['nombre'] }}</p>
 <p class="text-[9px] font-semibold text-apoyo mt-0.5 line-clamp-2">{{ $docInst['descripcion'] }}</p>
 </div>
 </div>
 <div class="mt-auto pt-4 flex items-center gap-2">
 <a href="{{ route('admin.usuarios.documentos.ver', ['user' => $usuarioDetalle->cod_usu, 'documento' => $docInst['slug']]) }}" target="_blank"
 class="flex-1 h-8 bg-fondo-panel hover:bg-boton-principal text-parrafo hover:text-inverso text-[10px] font-bold uppercase rounded-lg flex items-center justify-center gap-1 transition">
 <i class="ph-bold ph-eye"></i> Ver
 </a>
 <a href="{{ route('admin.usuarios.documentos.imprimir', ['user' => $usuarioDetalle->cod_usu, 'documento' => $docInst['slug']]) }}" target="_blank"
 class="flex-1 h-8 bg-fondo-card border border-borde-suave hover:border-borde-focus text-parrafo hover:text-boton-acento text-[10px] font-bold uppercase rounded-lg flex items-center justify-center gap-1 transition">
 <i class="ph-bold ph-printer"></i> Imprimir
 </a>
 <a href="{{ route('admin.usuarios.documentos.documento-pdf', ['user' => $usuarioDetalle->cod_usu, 'documento' => $docInst['slug']]) }}" target="_blank" download
 class="h-8 w-8 bg-boton-principal hover:bg-boton-acento text-inverso rounded-lg flex items-center justify-center transition">
 <i class="ph-bold ph-download-simple"></i>
 </a>
 </div>
 </div>
 @endforeach
 </div>
 </div>
 </section>
 @endif

 {{-- SECCION ACCESO --}}
 @if($seccionActivaDetalle === 'acceso')
 <section id="seccion-seguridad" class="animate-in fade-in duration-200 rounded-[1.8rem] border border-borde-suave bg-fondo-card/70 p-6 shadow-sm backdrop-blur-md space-y-4">
 <h4 class="flex items-center gap-2 border-b border-borde-suave pb-2 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-shield-check text-lg"></i> Seguridad y Acceso
 </h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Rol Asignado:</span>
 <span class="font-bold text-parrafo uppercase">{{ $rolDisplay }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Estado del Perfil:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->estado }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Acceso al Sistema:</span>
 <span class="font-bold text-parrafo uppercase">{{ $usuarioDetalle->acceso_sistema }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Último Acceso al Sistema:</span>
 <span class="font-bold text-parrafo">
 {{ $usuarioDetalle->ultimo_acceso ? $usuarioDetalle->ultimo_acceso->format('d/m/Y H:i') : 'No registrado' }}
 </span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Fecha de Registro:</span>
 <span class="font-bold text-parrafo">
 {{ $usuarioDetalle->created_at ? $usuarioDetalle->created_at->format('d/m/Y H:i') : 'No registrada' }}
 </span>
 </div>
 @if($usuarioDetalle->updated_at)
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Última Actualización:</span>
 <span class="font-bold text-parrafo">
 {{ $usuarioDetalle->updated_at->format('d/m/Y H:i') }}
 </span>
 </div>
 @endif
 </div>
 </section>
 @endif

 {{-- SECCION VINCULACION --}}
 @if($seccionActivaDetalle === 'vinculo' && $rolKey === 'FAMILIAR')
 @php
 $famDetalle = $usuarioDetalle->familiares->first();
 @endphp
 <section id="seccion-vinculacion" class="animate-in fade-in duration-200 rounded-[1.8rem] border border-borde-suave bg-fondo-card/70 p-6 shadow-sm backdrop-blur-md space-y-4">
 <h4 class="flex items-center gap-2 border-b border-borde-suave pb-2 text-xs font-bold uppercase tracking-widest text-boton-acento">
 <i class="ph-bold ph-users-three text-lg"></i> Vinculación Familiar
 </h4>
 
 @if($famDetalle && $famDetalle->adultosMayores->isNotEmpty())
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
 @foreach($famDetalle->adultosMayores as $am)
 @php
 // Parse observations for salud/economico
 $obs = $am->pivot->observaciones;
 $salud = 'NO';
 $economico = 'NO';
 $obsLimpia = $obs;
 if ($obs) {
 if (preg_match('/Salud:\s*(SI|NO)/i', $obs, $m)) {
 $salud = strtoupper($m[1]) === 'SI' ? 'SÍ' : 'NO';
 }
 if (preg_match('/Económico:\s*(SI|NO)/i', $obs, $m)) {
 $economico = strtoupper($m[1]) === 'SI' ? 'SÍ' : 'NO';
 }
 if (preg_match('/Obs:\s*(.*)/i', $obs, $m)) {
 $obsLimpia = trim($m[1]);
 }
 }

 // Format Adulto Mayor CI and Age
 $nombreAm = trim("{$am->nombres} {$am->ap_paterno} {$am->ap_materno}");
 $documentoAm = !empty($am->ci) 
 ?"CI" . $am->ci ."" . ($am->expedicion_ci ?: '') 
 :"SIN DOCUMENTO REGISTRADO";
 $edadAm = $am->fecha_nac 
 ? \Carbon\Carbon::parse($am->fecha_nac)->age ." AÑOS" 
 :"EDAD NO REGISTRADA";
 $amLabel ="{$nombreAm} — {$documentoAm} — {$edadAm}";
 @endphp
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 space-y-3 text-xs">
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Adulto Mayor Vinculado:</span>
 <span class="font-bold text-parrafo uppercase text-sm block mt-0.5 leading-snug">{{ $amLabel }}</span>
 </div>
 <div>
 <span class="font-black text-meta uppercase tracking-wider block">Parentesco / Vínculo:</span>
 <span class="font-bold text-parrafo uppercase">{{ $am->pivot->parentesco_vinculo ?: 'No especificado' }}</span>
 </div>
 <div class="grid grid-cols-3 gap-2 text-center text-[10px] pt-1">
 <div class="bg-fondo-card/70 p-2 rounded-xl border border-borde/10">
 <p class="font-black text-meta uppercase tracking-tight">Principal</p>
 <p class="font-bold uppercase mt-0.5 {{ $am->pivot->es_responsable ? 'text-estado-exito' : 'text-meta' }}">
 {{ $am->pivot->es_responsable ? 'SÍ' : 'NO' }}
 </p>
 </div>
 <div class="bg-fondo-card/70 p-2 rounded-xl border border-borde/10">
 <p class="font-black text-meta uppercase tracking-tight">Salud</p>
 <p class="font-bold uppercase mt-0.5 {{ $salud === 'SÍ' ? 'text-estado-exito' : 'text-meta' }}">
 {{ $salud }}
 </p>
 </div>
 <div class="bg-fondo-card/70 p-2 rounded-xl border border-borde/10">
 <p class="font-black text-meta uppercase tracking-tight">Económico</p>
 <p class="font-bold uppercase mt-0.5 {{ $economico === 'SÍ' ? 'text-estado-exito' : 'text-meta' }}">
 {{ $economico }}
 </p>
 </div>
 </div>
 @if($obsLimpia)
 <div class="bg-fondo-card/30 p-2.5 rounded-xl text-[11px] text-parrafo/80">
 <span class="font-black uppercase text-[8px] text-parrafo/55 block">Observación del Vínculo:</span>
 <p class="font-semibold leading-snug mt-0.5">{{ $obsLimpia }}</p>
 </div>
 @endif
 </div>
 @endforeach
 </div>
 @else
 <div class="py-4 text-center">
 <span class="text-xs font-bold text-meta uppercase tracking-widest">Sin adultos mayores vinculados</span>
 </div>
 @endif
 </section>
 @endif
 </div>\n\n {{-- BOTÓN VOLVER ARRIBA / ACCIONES PIE --}}
 <div class="flex items-center justify-between border-t border-borde-suave pt-4">
 <button type="button" 
 wire:click="volverAlListadoUsuarios" 
 class="inline-flex h-9 items-center gap-2 rounded-xl bg-fondo-panel px-4 text-xs font-bold text-parrafo transition hover:bg-boton-principal hover:text-inverso active:scale-95">
 <i class="ph-bold ph-arrow-left"></i>
 Volver al Listado
 </button>
 <button type="button" 
 onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
 class="inline-flex h-9 items-center gap-2 rounded-xl bg-fondo-card/60 border border-borde-suave px-4 text-xs font-bold text-parrafo transition hover:bg-fondo-card active:scale-95">
 <i class="ph-bold ph-arrow-up"></i>
 Ir arriba
 </button>
 </div>
 </div>

 @else

 {{-- ENCABEZADO LIMPIO Y MÁS DELGADO --}}
 <header class="mb-5 rounded-[1.6rem] border border-borde/55 bg-fondo-panel px-6 py-5 shadow-[0_10px_26px_rgba(47,62,92,0.07)] backdrop-blur-xl">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex items-center gap-4">
 <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-boton-principal text-inverso shadow-lg shadow-[#2F3E5C]/15 sm:flex">
 <i class="ph-bold ph-users-three text-xl"></i>
 </div>

 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-boton-acento">
 Gestión institucional
 </p>

 <h1 class="mt-1 text-3xl font-black tracking-tight text-parrafo md:text-[2.3rem]">
 Usuarios del <span class="text-boton-acento">sistema</span>
 </h1>

 <p class="mt-1 max-w-2xl text-sm font-semibold leading-6 text-parrafo/58">
 Administra cuentas, accesos y perfiles autorizados dentro de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
 </p>
 </div>
 </div>

 @can('usuarios.crear')
 <button type="button"
 wire:click="crearUsuario"
 class="inline-flex items-center justify-center gap-2 rounded-full bg-boton-acento px-6 py-3 text-sm font-bold text-inverso shadow-lg shadow-[#E27D60]/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0 active:scale-95">
 <i class="ph-bold ph-plus-circle text-lg"></i>
 Nuevo usuario
 </button>
 @endcan
 </div>
 </header>

 {{-- MÉTRICAS PRINCIPALES --}}
 <section class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
 <article class="rounded-[1.35rem] border border-borde bg-fondo-panel p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-fondo-panel hover:shadow-md">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-parrafo/42">
 Usuarios registrados
 </p>
 <p class="mt-2 text-2xl font-black text-parrafo">
 {{ method_exists($usuarios, 'total') ? $usuarios->total() : $usuarios->count() }}
 </p>
 </div>

 <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-users text-xl"></i>
 </div>
 </div>
 </article>

 <article class="rounded-[1.35rem] border border-borde bg-fondo-panel p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-fondo-panel hover:shadow-md">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-parrafo/42">
 Activos
 </p>
 <p class="mt-2 text-2xl font-black text-estado-exito">
 {{ $usuarios->filter(fn($u) => $u->estado === 'ACTIVO')->count() }}
 </p>
 </div>

 <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-estado-exitoBg text-estado-exito">
 <i class="ph-bold ph-user-check text-xl"></i>
 </div>
 </div>
 </article>

 <article class="rounded-[1.35rem] border border-borde bg-fondo-panel p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-fondo-panel hover:shadow-md">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-parrafo/42">
 Inactivos
 </p>
 <p class="mt-2 text-2xl font-black text-parrafo">
 {{ $usuarios->filter(fn($u) => $u->estado !== 'ACTIVO')->count() }}
 </p>
 </div>

 <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-user-minus text-xl"></i>
 </div>
 </div>
 </article>
 </section>

 {{-- VISTAS --}}
<section class="mb-4">
 <div class="inline-flex max-w-full flex-wrap items-center gap-2 rounded-[1.3rem] bg-fondo-panel px-2 py-2 shadow-[0_10px_24px_rgba(47,62,92,0.10)] backdrop-blur-md">
 <button type="button"
 @click="vista = 'cards'"
 :class="vista === 'cards'
 ? 'bg-boton-principal text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.20)]'
 : 'text-parrafo/72 hover:bg-fondo-card/45 hover:text-parrafo'"
 class="inline-flex h-9 items-center gap-2 rounded-xl px-4 text-[11px] font-bold transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
 <i class="ph-bold ph-identification-card text-sm"></i>
 Vista tarjetas
 </button>

 <button type="button"
 @click="vista = 'table'"
 :class="vista === 'table'
 ? 'bg-boton-principal text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.20)]'
 : 'text-parrafo/72 hover:bg-fondo-card/45 hover:text-parrafo'"
 class="inline-flex h-9 items-center gap-2 rounded-xl px-4 text-[11px] font-bold transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
 <i class="ph-bold ph-table text-sm"></i>
 Tabla compacta
 </button>
 </div>
</section>

 {{-- FILTROS COMPACTOS --}}
<section class="mb-5 rounded-[1.35rem] bg-fondo-panel px-4 py-3 shadow-[0_12px_28px_rgba(47,62,92,0.11)] backdrop-blur-md">
 <div class="grid items-end gap-3 xl:grid-cols-12">
 <div class="xl:col-span-3">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-[0.18em] text-parrafo/45">
 Buscar
 </label>

 <div class="relative">
 <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
 <i class="ph-bold ph-magnifying-glass text-base text-parrafo/32"></i>
 </div>

 <input type="text"
 wire:model.defer="search"
 placeholder="Nombre o correo..."
 class="h-10 w-full rounded-xl border-0 bg-fondo-panel py-2 pl-10 pr-3 text-xs font-bold text-parrafo shadow-[inset_0_1px_0_rgba(255,255,255,0.45),0_4px_12px_rgba(47,62,92,0.04)] outline-none transition-all placeholder:text-parrafo/35 focus:bg-fondo-card focus:ring-2 focus:ring-[#E27D60]/18">
 </div>
 </div>

 <div class="xl:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-[0.18em] text-parrafo/45">
 Rol
 </label>

 <select wire:model.defer="filtroRol"
 class="h-10 w-full rounded-xl border-0 bg-fondo-panel px-3 text-xs font-bold text-parrafo shadow-[inset_0_1px_0_rgba(255,255,255,0.45),0_4px_12px_rgba(47,62,92,0.04)] outline-none transition-all focus:bg-fondo-card focus:ring-2 focus:ring-[#E27D60]/18">
 <option value="">Todos los roles</option>
 @foreach($roles as $r)
 <option value="{{ $r->name }}">{{ strtoupper(str_replace('_', ' ', $r->name)) }}</option>
 @endforeach
 </select>
 </div>

 <div class="xl:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-[0.18em] text-parrafo/45">
 Área
 </label>

 <select wire:model.defer="filtroArea"
 class="h-10 w-full rounded-xl border-0 bg-fondo-panel px-3 text-xs font-bold text-parrafo shadow-[inset_0_1px_0_rgba(255,255,255,0.45),0_4px_12px_rgba(47,62,92,0.04)] outline-none transition-all focus:bg-fondo-card focus:ring-2 focus:ring-[#E27D60]/18">
 <option value="">Todas las áreas</option>
 @foreach($areas as $ar)
 <option value="{{ $ar->cod_area }}">{{ $ar->nombre }}</option>
 @endforeach
 </select>
 </div>

 <div class="xl:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-[0.18em] text-parrafo/45">
 Estado
 </label>

 <select wire:model.defer="filtroEstado"
 class="h-10 w-full rounded-xl border-0 bg-fondo-panel px-3 text-xs font-bold text-parrafo shadow-[inset_0_1px_0_rgba(255,255,255,0.45),0_4px_12px_rgba(47,62,92,0.04)] outline-none transition-all focus:bg-fondo-card focus:ring-2 focus:ring-[#E27D60]/18">
 <option value="">Todos</option>
 <option value="ACTIVO">Activos</option>
 <option value="INACTIVO">Inactivos</option>
 </select>
 </div>

 <div class="xl:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-[0.18em] text-parrafo/45">
 Género
 </label>

 <select wire:model.defer="filtroGenero"
 class="h-10 w-full rounded-xl border-0 bg-fondo-panel px-3 text-xs font-bold text-parrafo shadow-[inset_0_1px_0_rgba(255,255,255,0.45),0_4px_12px_rgba(47,62,92,0.04)] outline-none transition-all focus:bg-fondo-card focus:ring-2 focus:ring-[#E27D60]/18">
 <option value="">Todos</option>
 <option value="FEMENINO">Femenino</option>
 <option value="MASCULINO">Masculino</option>
 </select>
 </div>

 <div class="flex gap-2 xl:col-span-1">
 <button type="button"
 wire:click="aplicarFiltros"
 class="inline-flex h-10 flex-1 items-center justify-center gap-1.5 rounded-xl bg-boton-principal px-3 text-[10px] font-bold uppercase tracking-wide text-inverso shadow-[0_8px_16px_rgba(47,62,92,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_10px_20px_rgba(47,62,92,0.24)] active:scale-95"
 title="Buscar">
 <i class="ph-bold ph-funnel"></i>
 </button>

 <button type="button"
 wire:click="limpiarFiltros"
 class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-parrafo shadow-[0_6px_14px_rgba(47,62,92,0.08)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-boton-acento hover:text-inverso active:scale-95"
 title="Limpiar filtros">
 <i class="ph-bold ph-x"></i>
 </button>
 </div>
 </div>
</section>

 {{-- CONTENIDO PRINCIPAL --}}
 <section class="relative">
 <div wire:loading.delay wire:target="aplicarFiltros,limpiarFiltros,toggleEstado,abrirFichaRapida,abrirVistaCompleta,editarUsuario" class="absolute inset-0 z-[60] flex items-center justify-center rounded-[2rem] bg-fondo-card/45 backdrop-blur-sm">
 <div class="flex items-center gap-3 rounded-full bg-fondo-card px-5 py-3 shadow-lg">
 <i class="ph-bold ph-spinner animate-spin text-2xl text-boton-acento"></i>
 <span class="text-xs font-bold uppercase tracking-widest text-parrafo">
 Cargando
 </span>
 </div>
 </div>

 {{-- VISTA TARJETAS --}}
 <div x-show="vista === 'cards'" x-transition.opacity.duration.200ms>
 <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
 @forelse($usuarios as $u)
 @php
 $roleName = $u->getRoleNames()->first() ?? 'sin_rol';
 $roleKey = strtolower($roleName);
 $estaInactivo = $u->estado !== 'ACTIVO';

 $nombreCompleto = trim(($u->nombres ?? '') . ' ' . ($u->ap_paterno ?? '') . ' ' . ($u->ap_materno ?? ''));
 $nombreCompleto = $nombreCompleto !== '' ? $nombreCompleto : ($u->correo ?? 'Usuario sin nombre');

 $inicial = mb_substr(trim($u->nombres ?? $nombreCompleto), 0, 1);

 $areaDisplay = $u->areaInstitucional?->nombre ?? match($roleKey) {
 'super_admin', 'admin' => 'Administración del sistema',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'Área de salud',
 'superadministrador', 'administrador' => 'Área administrativa',
 'FAMILIAR' => 'Familiar autorizado',
 'VOLUNTARIO' => 'Voluntariado',
 default => 'Sin área asignada'
 };

 $perfilDetalle = match($roleKey) {
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => data_get($u, 'personalSalud.especialidad.nombre')
 ?? data_get($u, 'personalSalud.especialidad')
 ?? 'Personal de salud',
 'superadministrador', 'administrador' => data_get($u, 'personalAdmin.cargoAdmin.nombre')
 ?? $u->personalAdmin?->cargo
 ?? 'Personal administrativo',
 'super_admin', 'admin' => 'Administrador del sistema',
 'VOLUNTARIO' => 'Voluntario institucional',
 'FAMILIAR' => 'Familiar autorizado',
 default => strtoupper(str_replace('_', ' ', $roleName))
 };

 $areaClass = match($roleKey) {
 'super_admin', 'admin' => 'bg-fondo-panel text-parrafo border-borde-fuerte',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
 'superadministrador', 'administrador' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
 'VOLUNTARIO' => 'bg-fondo-panel text-parrafo border-borde',
 'FAMILIAR' => 'bg-fondo-panel text-parrafo border-borde',
 default => 'bg-fondo-panel text-parrafo/55 border-borde-suave'
 };

 $perfilClass = match($roleKey) {
 'super_admin', 'admin' => 'bg-fondo-panel text-parrafo border-borde-fuerte',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
 'superadministrador', 'administrador' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
 'VOLUNTARIO' => 'bg-fondo-panel text-parrafo border-borde',
 'FAMILIAR' => 'bg-fondo-panel text-parrafo border-borde',
 default => 'bg-fondo-panel text-parrafo/55 border-borde-suave'
 };

 $ultimoAcceso = $u->ultimo_acceso ?? null;

 $fotoUsuario = null;
 if (!empty($u->foto_de_perfil)) {
 $fotoUsuario = \Illuminate\Support\Facades\Storage::url($u->foto_de_perfil);
 } elseif (!empty($u->profile_photo_path)) {
 $fotoUsuario = \Illuminate\Support\Facades\Storage::url($u->profile_photo_path);
 } elseif (!empty($u->profile_photo_url)) {
 $fotoUsuario = $u->profile_photo_url;
 }
 @endphp

 <article wire:key="card-user-{{ $u->cod_usu }}"
 class="group overflow-hidden rounded-[1.45rem] border border-transparent bg-fondo-panel shadow-[0_14px_30px_rgba(47,62,92,0.13)] transition-all duration-300 {{ $estaInactivo ? 'grayscale opacity-70 bg-fondo-panel' : 'hover:-translate-y-1 hover:shadow-[0_20px_38px_rgba(47,62,92,0.17)]' }}">

 <div class="h-1 w-full {{ $estaInactivo ? 'bg-fondo-panel' : 'bg-gradient-to-r from-[#E27D60] via-[#F2A08D] to-[#2F3E5C]/25' }}"></div>

 <div class="p-3">
 {{-- Estado --}}
 <div class="mb-2 flex justify-end">
 @if($u->estado === 'ACTIVO')
 <span class="inline-flex items-center gap-1.5 rounded-full bg-estado-exitoBg px-2.5 py-1 text-[9px] font-bold uppercase text-estado-exito">
 <span class="h-1.5 w-1.5 rounded-full bg-estado-exitoBg"></span>
 Activo
 </span>
 @else
 <span class="inline-flex items-center gap-1.5 rounded-full bg-fondo-panel px-2.5 py-1 text-[9px] font-bold uppercase text-meta">
 <span class="h-1.5 w-1.5 rounded-full bg-fondo-panel"></span>
 Inactivo
 </span>
 @endif
 </div>

 {{-- Foto centrada cuadrada --}}
 <div class="flex flex-col items-center text-center">
 <div class="relative">
 @if($fotoUsuario)
 <img
 src="{{ $fotoUsuario }}"
 alt="Foto de {{ $nombreCompleto }}"
 class="h-24 w-24 rounded-[1.35rem] object-cover ring-[3px] ring-[#E6DDD3]/80 shadow-[0_10px_22px_rgba(47,62,92,0.16)] transition-all duration-300 {{ $estaInactivo ? '' : 'group-hover:scale-105' }}"
 >
 @else
 <div class="flex h-24 w-24 items-center justify-center rounded-[1.35rem] bg-boton-principal text-3xl font-black text-inverso ring-[3px] ring-[#E6DDD3]/80 shadow-[0_10px_22px_rgba(47,62,92,0.16)]">
 {{ strtoupper($inicial) }}
 </div>
 @endif

 <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-[3px] border-borde {{ $u->estado === 'ACTIVO' ? 'bg-estado-exitoBg' : 'bg-fondo-panel' }}"></span>
 </div>

 <h3 class="mt-2.5 line-clamp-2 text-sm font-bold uppercase leading-5 text-parrafo">
 {{ $nombreCompleto }}
 </h3>

 <p class="mt-0.5 max-w-full truncate text-xs font-bold lowercase text-parrafo/55">
 {{ $u->correo ?: 'Sin correo registrado' }}
 </p>
 </div>

 {{-- Info compacta --}}
 <div class="mt-3 space-y-2">
 <div class="rounded-xl border px-3 py-2 {{ $areaClass }}">
 <p class="text-[9px] font-bold uppercase tracking-[0.16em] opacity-60">
 Área
 </p>
 <p class="mt-0.5 truncate text-xs font-bold">
 {{ $areaDisplay }}
 </p>
 </div>

 <div class="rounded-xl border px-3 py-2 {{ $perfilClass }}">
 <p class="text-[9px] font-bold uppercase tracking-[0.16em] opacity-60">
 Perfil institucional
 </p>
 <p class="mt-0.5 truncate text-xs font-bold uppercase">
 {{ $perfilDetalle }}
 </p>
 </div>

 <div class="rounded-xl border border-borde bg-fondo-panel/75 px-3 py-2">
 <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-parrafo/35">
 Último acceso
 </p>
 @if($ultimoAcceso)
 <p class="mt-0.5 text-xs font-bold text-parrafo">
 {{ \Carbon\Carbon::parse($ultimoAcceso)->format('d/m/Y H:i') }}
 </p>
 @else
 <p class="mt-0.5 text-xs font-bold text-parrafo/45">
 Sin registro
 </p>
 @endif
 </div>
 </div>

 {{-- Acciones --}}
 <div class="mt-3 flex items-center justify-center gap-1.5">
 {{-- Grupo principal --}}
 <button type="button"
 wire:click="abrirVistaCompleta('{{ $u->cod_usu }}')"
 class="inline-flex h-8 items-center gap-1.5 rounded-full bg-boton-principal px-2.5 text-[9px] font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95"
 title="Ver ficha institucional">
 <i class="ph-bold ph-eye"></i>
 Ver
 </button>

 @can('usuarios.editar')
 @if($estaInactivo)
 <button type="button"
 disabled
 title="Active el usuario para poder editarlo"
 class="inline-flex h-8 cursor-not-allowed items-center gap-1.5 rounded-full bg-fondo-panel px-2.5 text-[9px] font-bold text-meta">
 <i class="ph-bold ph-lock"></i>
 Editar
 </button>
 @else
 <button type="button"
 wire:click="editarUsuario('{{ $u->cod_usu }}')"
 class="inline-flex h-8 items-center gap-1.5 rounded-full bg-boton-acento px-2.5 text-[9px] font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-pencil-simple"></i>
 Editar
 </button>
 @endif
 @endcan

 @can('usuarios.cambiar_estado')
 @if($u->cod_usu !== auth()->id())
 <button type="button"
 wire:click="toggleEstado('{{ $u->cod_usu }}')"
 wire:confirm="¿Desea cambiar el estado de este usuario?"
 class="inline-flex h-8 items-center gap-1.5 rounded-full px-2.5 text-[9px] font-bold shadow-sm transition active:scale-95
 {{ $u->estado === 'ACTIVO'
 ? 'bg-fondo-panel text-parrafo hover:bg-fondo-panel hover:text-inverso'
 : 'bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg hover:text-inverso' }}">
 <i class="ph-bold {{ $u->estado === 'ACTIVO' ? 'ph-user-minus' : 'ph-user-plus' }}"></i>
 {{ $u->estado === 'ACTIVO' ? 'Inactivar' : 'Activar' }}
 </button>
 @endif
 @endcan

 {{-- Separador + Ficha rápida --}}
 <span class="mx-0.5 h-5 w-px bg-fondo-panel"></span>

 <button type="button"
 wire:click="abrirFichaRapida('{{ $u->cod_usu }}')"
 class="inline-flex h-8 items-center gap-1.5 rounded-full bg-fondo-panel px-2.5 text-[9px] font-bold text-parrafo shadow-sm transition hover:bg-fondo-panel hover:text-inverso active:scale-95"
 title="Ficha rápida">
 <i class="ph-bold ph-clipboard-text"></i>
 Ficha
 </button>
 </div>
 </div>
 </article>
 @empty
 <div class="col-span-full py-8 text-center">
 <div class="mx-auto flex max-w-md flex-col items-center">
 <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-fondo-panel text-parrafo/35">
 <i class="ph-bold ph-users-three text-3xl"></i>
 </div>
 <h3 class="mt-4 text-lg font-extrabold text-parrafo">
 No se encontraron usuarios
 </h3>
 <p class="mt-2 text-sm font-semibold text-parrafo/45">
 Ajusta los filtros o registra un nuevo usuario institucional.
 </p>
 </div>
 </div>
 @endforelse
 </div>
 </div>

 {{-- VISTA TABLA COMPACTA --}}
 <div x-show="vista === 'table'" x-transition.opacity.duration.200ms>
 <div class="overflow-hidden rounded-[1.8rem] bg-fondo-panel border border-borde/45 shadow-[0_14px_30px_rgba(47,62,92,0.12)] backdrop-blur-md">
 <table class="w-full table-fixed text-left text-sm">
 <thead class="bg-fondo-panel text-[10px] uppercase tracking-[0.18em] text-parrafo/55">
 <tr>
 <th class="w-[30%] px-5 py-4 font-black">Usuario</th>
 <th class="w-[28%] px-5 py-4 font-black">Perfil institucional</th>
 <th class="w-[14%] px-5 py-4 font-black">Estado</th>
 <th class="w-[14%] px-5 py-4 font-black">Último acceso</th>
 <th class="w-[14%] px-5 py-4 text-center font-black">Acciones</th>
 </tr>
 </thead>

 <tbody class="divide-y divide-[#E7DDD1] bg-fondo-card/50">
 @forelse($usuarios as $u)
 @php
 $roleName = $u->getRoleNames()->first() ?? 'sin_rol';
 $roleKey = strtolower($roleName);
 $estaInactivo = $u->estado !== 'ACTIVO';

 $nombreCompleto = trim(($u->nombres ?? '') . ' ' . ($u->ap_paterno ?? '') . ' ' . ($u->ap_materno ?? ''));
 $nombreCompleto = $nombreCompleto !== '' ? $nombreCompleto : ($u->correo ?? 'Usuario sin nombre');

 $inicial = mb_substr(trim($u->nombres ?? $nombreCompleto), 0, 1);

 $areaDisplay = $u->areaInstitucional?->nombre ?? match($roleKey) {
 'super_admin', 'admin' => 'Administración del sistema',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'Área de salud',
 'superadministrador', 'administrador' => 'Área administrativa',
 'FAMILIAR' => 'Familiar autorizado',
 'VOLUNTARIO' => 'Voluntariado',
 default => 'Sin área asignada'
 };

 $perfilDetalle = match($roleKey) {
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => data_get($u, 'personalSalud.especialidad.nombre')
 ?? data_get($u, 'personalSalud.especialidad')
 ?? 'Personal de salud',
 'superadministrador', 'administrador' => data_get($u, 'personalAdmin.cargoAdmin.nombre')
 ?? $u->personalAdmin?->cargo
 ?? 'Personal administrativo',
 'super_admin', 'admin' => 'Administrador del sistema',
 'VOLUNTARIO' => 'Voluntario institucional',
 'FAMILIAR' => 'Familiar autorizado',
 default => strtoupper(str_replace('_', ' ', $roleName))
 };

 $areaClass = match($roleKey) {
 'super_admin', 'admin' => 'bg-fondo-panel text-parrafo border-borde-fuerte',
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
 'superadministrador', 'administrador' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
 'VOLUNTARIO' => 'bg-fondo-panel text-parrafo border-borde',
 'FAMILIAR' => 'bg-fondo-panel text-parrafo border-borde',
 default => 'bg-fondo-panel text-parrafo/55 border-borde-suave'
 };

 $ultimoAcceso = $u->ultimo_acceso ?? null;
 @endphp

 <tr class="transition-all duration-200 {{ $estaInactivo ? 'bg-fondo-panel grayscale opacity-70' : 'hover:bg-fondo-panel' }}"
 wire:key="tabla-user-{{ $u->cod_usu }}">
 <td class="px-5 py-4">
 <div class="flex min-w-0 items-center gap-3">
 <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-boton-principal text-sm font-bold text-inverso shadow-sm">
 {{ strtoupper($inicial) }}
 </div>
 <div class="min-w-0">
 <p class="truncate font-black text-parrafo">
 {{ $nombreCompleto }}
 </p>
 <p class="truncate text-xs font-semibold text-meta">
 {{ $u->correo ?: 'Sin correo registrado' }}
 </p>
 </div>
 </div>
 </td>

 <td class="px-5 py-4">
 <div class="space-y-1">
 <span class="inline-flex max-w-full rounded-full border px-3 py-1 text-[10px] font-bold uppercase {{ $areaClass }}">
 {{ $areaDisplay }}
 </span>

 <p class="truncate text-xs font-semibold text-parrafo/55">
 {{ $perfilDetalle }}
 </p>
 </div>
 </td>

 <td class="px-5 py-4">
 @if($u->estado === 'ACTIVO')
 <span class="rounded-full bg-estado-exitoBg px-3 py-1 text-[10px] font-bold uppercase text-estado-exito">
 Activo
 </span>
 @else
 <span class="rounded-full bg-fondo-panel px-3 py-1 text-[10px] font-bold uppercase text-meta">
 Inactivo
 </span>
 @endif
 </td>

 <td class="px-5 py-4">
 @if($ultimoAcceso)
 <div class="leading-4">
 <p class="font-black text-parrafo">
 {{ \Carbon\Carbon::parse($ultimoAcceso)->format('d/m/Y') }}
 </p>
 <p class="text-[10px] font-semibold text-parrafo/45">
 {{ \Carbon\Carbon::parse($ultimoAcceso)->format('H:i') }}
 </p>
 </div>
 @else
 <p class="text-[11px] font-semibold text-parrafo/45">
 Sin registro
 </p>
 @endif
 </td>

 <td class="px-5 py-4">
 <div class="flex items-center justify-center gap-1.5">
 {{-- Grupo principal --}}
 <button type="button"
 wire:click="abrirVistaCompleta('{{ $u->cod_usu }}')"
 class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-panel text-parrafo shadow-sm transition hover:bg-boton-principal hover:text-inverso active:scale-90"
 title="Ver ficha institucional">
 <i class="ph-bold ph-eye"></i>
 </button>

 @can('usuarios.editar')
 @if($estaInactivo)
 <button type="button"
 disabled
 class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl bg-fondo-panel text-meta shadow-sm"
 title="Active el usuario para poder editarlo">
 <i class="ph-bold ph-lock"></i>
 </button>
 @else
 <button type="button"
 wire:click="editarUsuario('{{ $u->cod_usu }}')"
 class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-panel text-boton-acento shadow-sm transition hover:bg-boton-acento hover:text-inverso active:scale-90"
 title="Editar">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 @endif
 @endcan

 @can('usuarios.cambiar_estado')
 @if($u->cod_usu !== auth()->id())
 <button type="button"
 wire:click="toggleEstado('{{ $u->cod_usu }}')"
 wire:confirm="¿Desea cambiar el estado de este usuario?"
 class="flex h-9 w-9 items-center justify-center rounded-xl shadow-sm transition active:scale-90
 {{ $u->estado === 'ACTIVO'
 ? 'bg-fondo-panel text-parrafo hover:bg-fondo-panel hover:text-inverso'
 : 'bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg hover:text-inverso' }}"
 title="{{ $u->estado === 'ACTIVO' ? 'Inactivar usuario' : 'Activar usuario' }}">
 <i class="ph-bold {{ $u->estado === 'ACTIVO' ? 'ph-user-minus' : 'ph-user-plus' }}"></i>
 </button>
 @endif
 @endcan

 {{-- Separador + Ficha --}}
 <span class="mx-0.5 h-5 w-px bg-fondo-panel"></span>

 <button type="button"
 wire:click="abrirFichaRapida('{{ $u->cod_usu }}')"
 class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-panel text-parrafo shadow-sm transition hover:bg-fondo-panel hover:text-inverso active:scale-90"
 title="Ficha rápida">
 <i class="ph-bold ph-clipboard-text"></i>
 </button>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-14 text-center text-parrafo/45 font-bold">
 No se encontraron usuarios.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 @if($usuarios->hasPages())
 <div class="mt-5 flex justify-center">
 <div class="rounded-2xl border border-borde bg-fondo-card/75 px-4 py-3 shadow-sm">
 {{ $usuarios->links() }}
 </div>
 </div>
 @endif
 </section>
 @endif

 {{-- MODAL FUERA DEL CONTENEDOR DEL PANEL --}}
 @if($mostrarFormulario)
 <div class="fixed inset-0 z-[2147483646] flex items-center justify-center bg-boton-principal/50 backdrop-blur-sm px-3 sm:px-4 transition-all duration-300">
 <div class="relative z-[2147483647] w-full max-w-4xl max-h-[92vh] overflow-hidden rounded-[24px] border border-borde-suave bg-fondo-app shadow-[0_20px_50px_rgba(0,0,0,0.5)] animate-in fade-in zoom-in duration-300 flex flex-col">
 
 {{-- HEADER CON PROGRESO --}}
 <header class="relative border-b border-borde-suave bg-fondo-panel px-4 py-3 backdrop-blur-xl shrink-0">
 <div class="flex items-center justify-between gap-4 mb-2">
 <div class="flex items-center gap-3">
 <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-principal text-inverso shadow-lg">
 <i class="ph-bold {{ $isEdit ? 'ph-pencil-simple' : 'ph-user-plus' }} text-lg"></i>
 </div>
 <div>
 <h2 class="text-base font-extrabold text-parrafo">
 {{ $isEdit ? 'Actualizar' : 'Registro de' }} <span class="text-boton-acento">Personal</span>
 </h2>
 </div>
 </div>
 <button type="button" wire:click="cerrarFormulario" class="group flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-app text-parrafo transition-all hover:bg-boton-acento hover:text-inverso active:scale-90 shadow-sm">
 <i class="ph-bold ph-x text-base transition group-hover:rotate-90"></i>
 </button>
 </div>
 {{-- BARRA DE PASOS VISUAL MEJORADA --}}
 <div class="relative px-4 py-4 sm:px-8 bg-fondo-panel border-b border-borde/10 hidden sm:block">
 <div class="relative flex items-center justify-between max-w-3xl mx-auto">
 {{-- Línea de fondo --}}
 <div class="absolute top-1/2 left-0 w-full h-1 bg-fondo-panel -translate-y-1/2 rounded-full"></div>
 {{-- Línea de progreso activa --}}
 <div class="absolute top-1/2 left-0 h-1 bg-boton-acento -translate-y-1/2 rounded-full transition-all duration-700 ease-out shadow-[0_0_8px_rgba(226,125,96,0.5)]" 
 style="width: {{ (($pasoFormulario - 1) / 4) * 100 }}%"></div>
 
 {{-- Pasos --}}
 @php
 $pasosUsu = [
 1 => ['i' => 'ph-user-circle', 'l' => 'Identidad'],
 2 => ['i' => 'ph-phone-call', 'l' => 'Contacto'],
 3 => ['i' => 'ph-briefcase', 'l' => 'Perfil'],
 4 => ['i' => 'ph-shield-check', 'l' => 'Seguridad'],
 5 => ['i' => 'ph-check-square', 'l' => 'Finalizar']
 ];
 @endphp

 @foreach($pasosUsu as $s => $p)
 <div class="relative flex flex-col items-center group">
 <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl border-2 transition-all duration-500
 {{ $pasoFormulario > $s ? 'bg-estado-exitoBg border-estado-exitoBorde text-inverso' : 
 ($pasoFormulario == $s ? 'bg-fondo-card border-borde-focus text-boton-acento shadow-lg scale-110' : 
 'bg-fondo-app border-borde text-parrafo/30') }}">
 
 <i class="ph-bold {{ $p['i'] }} text-base transition-all duration-500 {{ $pasoFormulario == $s ? 'scale-110' : '' }}"></i>
 
 @if($pasoFormulario > $s)
 <div class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-boton-principal text-inverso shadow-sm border border-borde-suave">
 <i class="ph-bold ph-check text-[8px]"></i>
 </div>
 @endif
 </div>
 <span class="absolute -bottom-7 whitespace-nowrap text-[8px] font-black uppercase tracking-tighter transition-all duration-500 
 {{ $pasoFormulario >= $s ? 'text-parrafo opacity-100' : 'text-parrafo/30 opacity-60' }} {{ $pasoFormulario == $s ? 'text-boton-acento -translate-y-0.5' : '' }}">
 {{ $p['l'] }}
 </span>
 </div>
 @endforeach
 </div>
 </div>
 </header>

 {{-- CONTENIDO --}}
 <div class="flex-1 max-h-[62vh] overflow-y-auto custom-scrollbar px-4 py-3">
 
 {{-- PASO 1: IDENTIDAD --}}
 @if($pasoFormulario === 1)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-identification-card text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Información Personal</h3>
 </div>
 
 {{-- Contenedor de Fotografía y Carga --}}
 <div class="flex flex-col sm:flex-row items-center gap-5 bg-fondo-card/40 p-4 rounded-2xl border border-borde-suave">
 <div class="relative group">
 @if($foto_de_perfil_upload)
 <img src="{{ $foto_de_perfil_upload->temporaryUrl() }}" 
 class="h-24 w-24 rounded-[1.35rem] object-cover ring-4 ring-[#E27D60] shadow-md">
 @elseif($isEdit && $cod_usu && \App\Models\User::find($cod_usu)?->foto_de_perfil)
 <img src="{{ asset('storage/' . \App\Models\User::find($cod_usu)->foto_de_perfil) }}" 
 class="h-24 w-24 rounded-[1.35rem] object-cover ring-4 ring-[#2F3E5C]/30 shadow-md">
 @else
 <div class="flex h-24 w-24 items-center justify-center rounded-[1.35rem] bg-boton-principal text-3xl font-black text-inverso ring-4 ring-[#2F3E5C]/10 shadow-md uppercase">
 {{ mb_substr($nombres ?? 'U', 0, 1) }}{{ mb_substr($ap_paterno ?? 'I', 0, 1) }}
 </div>
 @endif
 <div wire:loading wire:target="foto_de_perfil_upload" class="absolute inset-0 flex items-center justify-center bg-boton-principal/60 rounded-[1.35rem]">
 <i class="ph-bold ph-circle-notch animate-spin text-inverso text-xl"></i>
 </div>
 </div>
 <div class="flex-1 text-center sm:text-left space-y-1">
 <h4 class="text-xs font-bold text-parrafo uppercase tracking-wider">Fotografía Institucional</h4>
 <p class="text-[10px] text-apoyo font-semibold leading-relaxed">
 Formatos permitidos: JPG, JPEG, PNG, WEBP. Tamaño máximo: 4MB.
 </p>
 <label class="inline-flex items-center gap-2 px-3 py-1.5 bg-boton-principal hover:bg-boton-acento text-inverso rounded-lg text-[9px] font-bold uppercase tracking-wider cursor-pointer shadow transition active:scale-95">
 <i class="ph-bold ph-upload-simple"></i> Seleccionar foto
 <input type="file" wire:model="foto_de_perfil_upload" class="hidden" accept="image/*">
 </label>
 @error('foto_de_perfil_upload') 
 <span class="block text-[9px] font-bold text-boton-acento uppercase mt-1">{{ $message }}</span> 
 @enderror
 </div>
 </div>

 <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
 <div class="md:col-span-2 lg:col-span-3">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nombres *</label>
 <input type="text" wire:model="nombres" placeholder="Ej. Carla Valeria"
 class="w-full h-10 rounded-xl border {{ $errors->has('nombres') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 @error('nombres') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Paterno *</label>
 <input type="text" wire:model="ap_paterno" placeholder="Paterno"
 class="w-full h-10 rounded-xl border {{ $errors->has('ap_paterno') ? 'border-borde-focus' : 'border-borde' }} focus:border-borde-fuerte bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 @error('ap_paterno') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Materno</label>
 <input type="text" wire:model="ap_materno" placeholder="Materno"
 class="w-full h-10 rounded-xl border border-borde focus:border-borde-fuerte bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Género *</label>
 <select wire:model="genero" class="w-full h-10 rounded-xl border {{ $errors->has('genero') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE...</option>
 <option value="FEMENINO">FEMENINO</option>
 <option value="MASCULINO">MASCULINO</option>
 </select>
 @error('genero') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <div class="flex justify-between items-center mb-1">
 <label class="block text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha Nacimiento *</label>
 @if($edad !== null)
 <span class="text-[9px] font-bold text-boton-acento uppercase tracking-wider">Edad: {{ $edad }} años</span>
 @endif
 </div>
 <input type="date" wire:model.live="fecha_nacimiento"
 class="w-full h-10 rounded-xl border {{ $errors->has('fecha_nacimiento') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 @error('fecha_nacimiento') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">País Emisor *</label>
 <select wire:model.live="pais_documento" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 @foreach(array_keys($paisesConfig) as $pName)
 <option value="{{ mb_strtoupper($pName, 'UTF-8') }}">{{ mb_strtoupper($pName, 'UTF-8') }}</option>
 @endforeach
 </select>
 </div>
 <div class="md:col-span-2 lg:col-span-3 grid gap-3 {{ (mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI') ? 'grid-cols-12' : 'grid-cols-3' }}">
 <div class="{{ (mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI') ? 'col-span-3' : 'col-span-1' }} {{ mb_strtoupper((string) $pais_documento, 'UTF-8') !== 'OTRO' ? 'pointer-events-none opacity-60' : '' }}">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Tipo *</label>
 <select wire:model.live="tipo_documento" tabindex="-1" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <option value="CI">CI</option>
 <option value="DNI">DNI</option>
 <option value="PAS">PAS</option>
 <option value="CPF">CPF</option>
 <option value="RUT">RUT</option>
 <option value="Cédula">Cédula</option>
 <option value="INE">INE</option>
 <option value="SSN">SSN</option>
 <option value="Pasaporte">Pasaporte</option>
 </select>
 </div>
 <div class="{{ (mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI') ? 'col-span-6' : 'col-span-2' }}">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Número Doc *</label>
 <input type="text" wire:model="numero_documento" placeholder="Ej. 1234567"
 class="w-full h-10 rounded-xl border {{ $errors->has('numero_documento') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold uppercase text-parrafo outline-none transition">
 </div>
 @if(mb_strtoupper((string) $pais_documento, 'UTF-8') === 'BOLIVIA' && $tipo_documento === 'CI')
 <div class="col-span-3">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-boton-acento">Expedido (EXP) *</label>
 <select wire:model="expedido" class="w-full h-10 rounded-xl border {{ $errors->has('expedido') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <option value="">SELECCIONE...</option>
 @foreach(['LP','CB','SC','OR','PT','CH','TJ','BN','PD'] as $e)
 <option value="{{ $e }}">{{ $e }}</option>
 @endforeach
 </select>
 </div>
 @endif
 @error('numero_documento') <div class="col-span-full"><span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span></div> @enderror
 @error('expedido') <div class="col-span-full"><span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span></div> @enderror
 @if(mb_strtoupper((string) $pais_documento, 'UTF-8') !== 'OTRO')
 <div class="col-span-full mt-1">
 <p class="text-[9px] font-semibold text-meta italic">
 * El tipo de documento se bloquea y pre-asigna automáticamente según el país emisor seleccionado.
 </p>
 </div>
 @endif
 </div>
 </div>
 </div>
 @endif

 {{-- PASO 2: CONTACTO Y DOMICILIO --}}
 @if($pasoFormulario === 2)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-phone-call text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Contacto y Domicilio</h3>
 </div>
 <div class="grid gap-4 md:grid-cols-2">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Correo Institucional *</label>
 <input type="email" wire:model.live="correo" placeholder="ejemplo@jardindelosrecuerdos.org"
 class="w-full h-10 rounded-xl border {{ $errors->has('correo') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 @error('correo') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <div class="grid grid-cols-12 gap-2">
 <div class="col-span-5">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">País Celular</label>
 <select wire:model.live="pais_telefono" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-1 py-2 text-[9px] font-bold text-parrafo outline-none transition">
 <option value="">-- Seleccionar --</option>
 @foreach($paisesConfig as $pName => $pData)
 <option value="{{ $pName }}">{{ $pName }} ({{ $pData['codigo'] }})</option>
 @endforeach
 </select>
 </div>
 <div class="col-span-7">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Celular *</label>
 <div class="flex gap-2">
 <span class="inline-flex items-center justify-center h-10 px-2 rounded-xl bg-fondo-panel border border-borde text-xs font-bold text-parrafo">
 {{ $codigo_telefono ?: '+??' }}
 </span>
 <input type="text" wire:model="telefono" 
 placeholder="{{ $pais_telefono && isset($paisesConfig[$pais_telefono]) ? $paisesConfig[$pais_telefono]['placeholder'] : 'Seleccione país...' }}"
 class="flex-1 h-10 rounded-xl border {{ $errors->has('telefono') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 </div>
 @error('telefono') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 {{-- Domicilio --}}
 <div class="md:col-span-2 border-t border-borde-suave pt-3">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest mb-3">Dirección de Domicilio</h4>
 </div>

 <!-- 1. Departamento -->
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Departamento de Domicilio *</label>
 <select wire:model.live="departamento_domicilio"
 class="w-full h-10 rounded-xl border {{ $errors->has('departamento_domicilio') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE DEPARTAMENTO...</option>
 @foreach(array_keys($catalogDepartamentos) as $dept)
 <option value="{{ $dept }}">{{ $dept }}</option>
 @endforeach
 </select>
 @error('departamento_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <!-- Especifique Departamento (si aplica) -->
 @if($departamento_domicilio === 'OTRO')
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Especifique Departamento *</label>
 <input type="text" wire:model="otro_departamento" placeholder="Especifique el Departamento..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otro_departamento') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otro_departamento') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 @else
 <div></div> <!-- Mantiene alineado el grid si no se muestra -->
 @endif

 <!-- 2. Municipio / Ciudad -->
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Municipio / Localidad *</label>
 @if($departamento_domicilio === 'OTRO')
 <input type="text" wire:model="otro_municipio" placeholder="Especifique Municipio..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otro_municipio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otro_municipio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @else
 <select wire:model.live="municipio_domicilio"
 class="w-full h-10 rounded-xl border {{ $errors->has('municipio_domicilio') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE MUNICIPIO...</option>
 @if($departamento_domicilio && isset($catalogDepartamentos[$departamento_domicilio]))
 @foreach($catalogDepartamentos[$departamento_domicilio] as $muni)
 <option value="{{ $muni }}">{{ $muni }}</option>
 @endforeach
 @endif
 </select>
 @error('municipio_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @endif
 </div>

 <!-- 3. Zona / Barrio -->
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Zona / Barrio *</label>
 @if(isset($catalogZonas[$municipio_domicilio]))
 <select wire:model.live="zona_domicilio"
 class="w-full h-10 rounded-xl border {{ $errors->has('zona_domicilio') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE ZONA...</option>
 @foreach($catalogZonas[$municipio_domicilio] as $z)
 <option value="{{ $z }}">{{ $z }}</option>
 @endforeach
 </select>
 @error('zona_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @else
 <input type="text" wire:model="otra_zona" placeholder="Ej. Sopocachi"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otra_zona') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otra_zona') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @endif
 </div>

 <!-- 4. Especifique municipio/ciudad (si aplica) -->
 @if($municipio_domicilio === 'OTRO' && $departamento_domicilio !== 'OTRO')
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Especifique Municipio *</label>
 <input type="text" wire:model="otro_municipio" placeholder="Especifique el Municipio..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otro_municipio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otro_municipio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 @else
 <div></div> <!-- Mantiene alineado el grid -->
 @endif

 <!-- 5. Especifique zona/barrio (si aplica) -->
 @if(isset($catalogZonas[$municipio_domicilio]) && $zona_domicilio === 'OTRO')
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Especifique Zona / Barrio *</label>
 <input type="text" wire:model="otra_zona" placeholder="Especifique la Zona / Barrio..."
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('otra_zona') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('otra_zona') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 @else
 <div></div> <!-- Mantiene alineado el grid -->
 @endif

 <!-- 6 & 7. Calle / Avenida y Número de domicilio -->
 <div class="md:col-span-2 grid grid-cols-12 gap-3">
 <div class="col-span-8">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Calle / Avenida *</label>
 <input type="text" wire:model="calle" placeholder="Ej. Av. Arce o Calle Murillo"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('calle') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('calle') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="col-span-4">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nro. Domicilio *</label>
 <input type="text" wire:model="nro_domicilio" placeholder="Ej. 1234 o S/N"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('nro_domicilio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('nro_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 <!-- 8. Referencia de domicilio -->
 <div class="md:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Referencia de Domicilio</label>
 <input type="text" wire:model="referencia_domicilio" placeholder="Ej. Frente al centro de salud"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('referencia_domicilio') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('referencia_domicilio') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 {{-- Emergencia --}}
 <div class="md:col-span-2 border-t border-borde-suave pt-3">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest mb-3">Contacto de Emergencia</h4>
 </div>

 <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-3">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nombres Emergencia *</label>
 <input type="text" wire:model="contacto_emergencia" placeholder="Ej. María Teresa"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('contacto_emergencia') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('contacto_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Paterno *</label>
 <input type="text" wire:model="ap_paterno_emergencia" placeholder="Ej. López"
 class="uppercase w-full h-10 rounded-xl border {{ $errors->has('ap_paterno_emergencia') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('ap_paterno_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Apellido Materno</label>
 <input type="text" wire:model="ap_materno_emergencia" placeholder="Ej. Quispe"
 class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('ap_materno_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Parentesco / Relación</label>
 <select wire:model="parentesco_emergencia" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE...</option>
 <option value="PADRE">PADRE</option>
 <option value="MADRE">MADRE</option>
 <option value="CONYUGUE">CONYUGUE</option>
 <option value="HIJO/A">HIJO/A</option>
 <option value="HERMANO/A">HERMANO/A</option>
 <option value="OTRO">OTRO</option>
 </select>
 @error('parentesco_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Celular de Emergencia</label>
 <input type="text" wire:model="celular_emergencia" placeholder="Ej. 70098765"
 class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('celular_emergencia') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 </div>
 @endif

 {{-- PASO 3: PERFIL INSTITUCIONAL --}}
 @if($pasoFormulario === 3)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-briefcase text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Tipo de usuario / rol institucional</h3>
 </div>
 <div class="grid gap-4 md:grid-cols-2 max-w-3xl">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Tipo de usuario / rol institucional *</label>
 <select wire:model.live="rol" class="w-full h-10 rounded-xl border {{ $errors->has('rol') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE TIPO DE USUARIO...</option>
 @foreach($roles as $rolDisponible)
 @php
 $nombreParaSelect = match($rolDisponible->name) {
 'FAMILIAR' => 'Familiar / Responsable',
 'VOLUNTARIO' => 'Voluntario',
 default => mb_convert_case(str_replace('_', ' ', $rolDisponible->name), MB_CASE_TITLE, 'UTF-8')
 };
 @endphp
 <option value="{{ $rolDisponible->name }}">{{ $nombreParaSelect }}</option>
 @endforeach
 </select>
 @error('rol') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Área Institucional Operativa</label>
 <select wire:model="cod_area" class="w-full h-10 rounded-xl border {{ $errors->has('cod_area') ? 'border-borde-focus ring-4 ring-[#E27D60]/10' : 'border-borde focus:border-borde-fuerte' }} bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition">
 <option value="">SELECCIONE ÁREA...</option>
 @foreach($areas as $ar)
 @if($ar->cod_area !== 'ARE_0009') {{-- Ocultar Admin del Sistema --}}
 @php
 $esSugerida = false;
 if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && str_contains(strtolower($ar->nombre), 'salud')) $esSugerida = true;
 elseif (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && str_contains(strtolower($ar->nombre), 'atención médica')) $esSugerida = true;
 elseif (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && str_contains(strtolower($ar->nombre), 'psicología')) $esSugerida = true;
 elseif (in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']) && str_contains(strtolower($ar->nombre), 'admin')) $esSugerida = true;
 elseif ($rol === 'VOLUNTARIO' && str_contains(strtolower($ar->nombre), 'voluntariado')) $esSugerida = true;
 @endphp
 <option value="{{ $ar->cod_area }}">
 {{ $ar->nombre }} {{ $esSugerida ? '⭐ (Recomendada)' : '' }}
 </option>
 @endif
 @endforeach
 </select>
 @error('cod_area') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror

 @if($rol)
 @php
 $sugeridaTxt = match($rol) {
 'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'Área de Atención Médica o Área de Psicología',
 'superadministrador', 'administrador' => 'Área Administrativa y Registro Institucional',
 'VOLUNTARIO' => 'Voluntariado y Relaciones Institucionales',
 'FAMILIAR' => 'No requiere vinculación a áreas internas',
 default => null
 };
 @endphp
 @if($sugeridaTxt)
 <p class="mt-1 text-[9px] font-bold uppercase text-boton-acento tracking-wider flex items-center gap-1 animate-pulse">
 <i class="ph-bold ph-sparkle"></i> Recomendación: se sugiere vincular a <span class="underline font-extrabold">{{ $sugeridaTxt }}</span>
 </p>
 @endif
 @endif
 </div>

 {{-- Perfil de Salud --}}
 @if(in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']))
 <div class="md:col-span-2 grid gap-4 md:grid-cols-2 border-t border-borde-suave pt-3 animate-in fade-in duration-300">
 <div class="md:col-span-2">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Información Profesional Médica</h4>
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-boton-acento">Especialidad Médica *</label>
 <select wire:model.live="especialidad_salud" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="">SELECCIONE ESPECIALIDAD...</option>
 @foreach($especialidades as $esp)
 <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
 @endforeach
 </select>
 @error('especialidad_salud') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Fecha de Ingreso *</label>
 <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }} class="w-full h-10 rounded-xl border border-borde bg-fondo-panel {{ !$isEdit ? 'opacity-70 pointer-events-none' : '' }} px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('fecha_ingreso') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="md:col-span-2">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Institución de Formación</label>
 <input type="text" wire:model="institucion_formacion" placeholder="Ej. Universidad Mayor de San Andrés" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('institucion_formacion') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="md:col-span-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-[10px] font-bold leading-relaxed text-parrafo">
 La matricula profesional y documentos de respaldo se gestionaran desde el modulo de documentacion del usuario.
 </div>
 </div>
 @endif

 {{-- Perfil Admin --}}
 @if(in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']))
 <div class="md:col-span-2 grid gap-4 md:grid-cols-2 border-t border-borde-suave pt-3 animate-in fade-in duration-300">
 <div class="md:col-span-2">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Información de Cargo Administrativo</h4>
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Cargo Administrativo *</label>
 @if($cargosAdmin->isEmpty())
 <div class="bg-estado-peligroBg border border-borde-focus rounded-xl p-3 text-[10px] font-semibold text-boton-acento leading-normal">
 ⚠️ No hay cargos administrativos registrados. Por favor, registre cargos administrativos primero o contacte con soporte técnico.
 </div>
 @else
 <select wire:model.live="cargo_administrativo" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <option value="">SELECCIONE CARGO...</option>
 @foreach($cargosAdmin as $cargo)
 <option value="{{ $cargo->cod_cargo_admin }}">{{ $cargo->nombre }}</option>
 @endforeach
 </select>
 @error('cargo_administrativo') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 @endif
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Fecha de Ingreso *</label>
 <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }} class="w-full h-10 rounded-xl border border-borde bg-fondo-panel {{ !$isEdit ? 'opacity-70 pointer-events-none' : '' }} px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('fecha_ingreso') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 {{-- Perfil Voluntario --}}
 @if($rol === 'VOLUNTARIO')
 <div class="md:col-span-2 grid gap-4 md:grid-cols-2 border-t border-borde-suave pt-3 animate-in fade-in duration-300">
 <div class="md:col-span-2">
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Perfil de Voluntariado</h4>
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Disponibilidad Inicial</label>
 <input type="text" wire:model="disponibilidad_inicial" placeholder="Ej. Fines de semana / Tardes" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('disponibilidad_inicial') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Área de Apoyo Preferente</label>
 <input type="text" wire:model="area_apoyo_preferente" placeholder="Ej. Recreación / Terapia" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('area_apoyo_preferente') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Fecha de Ingreso *</label>
 <input type="date" wire:model="fecha_ingreso" {{ !$isEdit ? 'readonly tabindex="-1"' : '' }} class="w-full h-10 rounded-xl border border-borde bg-fondo-panel {{ !$isEdit ? 'opacity-70 pointer-events-none' : '' }} px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('fecha_ingreso') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 {{-- Perfil Familiar --}}
 @if($rol === 'FAMILIAR')
 <div class="md:col-span-2 grid gap-6 border-t border-borde-suave pt-4 animate-in fade-in duration-300">
 
 {{-- Encabezado de la Sección --}}
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-borde-suave pb-2">
 <div class="flex items-center gap-2">
 <i class="ph-fill ph-users-three text-lg text-boton-acento"></i>
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Vinculacion con adulto mayor</h4>
 </div>
 <button type="button" wire:click="$toggle('mostrarQuickRegAdulto')"
 class="px-3 py-1 rounded-lg border border-borde-focus text-boton-acento text-[8px] font-black uppercase tracking-wider transition hover:bg-boton-acento hover:text-inverso active:scale-95 inline-flex items-center gap-1 shadow-sm">
 <i class="ph-bold {{ $mostrarQuickRegAdulto ? 'ph-caret-left' : 'ph-user-plus' }} text-xs"></i>
 {{ $mostrarQuickRegAdulto ? 'Volver a Selección' : 'Registrar Nuevo Adulto Mayor' }}
 </button>
 </div>

 {{-- 1. FORMULARIO DE REGISTRO RÁPIDO (INLINE) --}}
 @if($mostrarQuickRegAdulto)
 <div class="p-4 rounded-2xl bg-fondo-panel border border-borde-suave space-y-4 animate-in slide-in-from-top-4 duration-300">
 <div class="flex items-center gap-2 border-b border-borde-suave pb-1.5">
 <i class="ph-bold ph-plus-circle text-boton-acento text-sm"></i>
 <h5 class="text-[9px] font-bold uppercase tracking-widest text-parrafo">Registro Rápido de Adulto Mayor</h5>
 </div>
 
 <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Nombres *</label>
 <input type="text" wire:model="quick_nombres" placeholder="Nombres" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_nombres') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Apellido Paterno *</label>
 <input type="text" wire:model="quick_ap_paterno" placeholder="Paterno" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_ap_paterno') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Apellido Materno</label>
 <input type="text" wire:model="quick_ap_materno" placeholder="Materno" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_ap_materno') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">CI / Documento *</label>
 <input type="text" wire:model="quick_ci" placeholder="Ej. 1234567" class="uppercase w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_ci') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Género *</label>
 <select wire:model="quick_genero" class="w-full h-8 rounded-lg border border-borde bg-fondo-card px-2 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="MASCULINO">MASCULINO</option>
 <option value="FEMENINO">FEMENINO</option>
 <option value="OTRO">OTRO</option>
 </select>
 @error('quick_genero') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Fecha Nacimiento *</label>
 <input type="date" wire:model="quick_fecha_nac" class="w-full h-8 rounded-lg border border-borde bg-fondo-card px-3 py-1 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('quick_fecha_nac') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 <div class="flex justify-end gap-2 border-t border-borde/10 pt-2">
 <button type="button" wire:click="$set('mostrarQuickRegAdulto', false)" class="px-4 py-1.5 rounded-lg bg-fondo-panel text-parrafo text-[8px] font-black uppercase tracking-widest transition hover:bg-fondo-panel">Cancelar</button>
 <button type="button" wire:click="registrarYVincularAdulto" class="px-5 py-1.5 rounded-lg bg-boton-acento text-inverso text-[8px] font-black uppercase tracking-widest transition hover:bg-boton-principal shadow-sm">Registrar y Vincular</button>
 </div>
 </div>

 {{-- 2. SELECCIÓN DE ADULTO MAYOR EXISTENTE --}}
 @else
 <div class="p-4 rounded-2xl bg-fondo-panel border border-borde-suave space-y-4">
 <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4 items-end">
 <div class="sm:col-span-2">
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Seleccionar Adulto Mayor *</label>
 <select wire:model="selected_cod_am" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="">-- Seleccionar Adulto Mayor Disponible --</option>
 @foreach(\App\Models\AdultoMayor::where('cod_est_adul', 1)->orderBy('ap_paterno')->orderBy('nombres')->get() as $am)
 @php
 $adultoNombre = trim(($am->nombres ?? '') . ' ' . ($am->ap_paterno ?? '') . ' ' . ($am->ap_materno ?? ''));
 $adultoDocumento = $am->ci ? 'CI ' . trim(($am->ci ?? '') . ' ' . ($am->expedicion_ci ?? '')) : 'SIN DOCUMENTO REGISTRADO';
 $adultoEdad = $am->fecha_nac ? ' — ' . \Carbon\Carbon::parse($am->fecha_nac)->age . ' AÑOS' : '';
 @endphp
 <option value="{{ $am->cod_am }}">{{ mb_strtoupper($adultoNombre . ' — ' . $adultoDocumento . $adultoEdad, 'UTF-8') }}</option>
 @endforeach
 </select>
 @error('selected_cod_am') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Parentesco / Vínculo *</label>
 <select wire:model="selected_parentesco" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 <option value="HIJO/A">HIJO/A</option>
 <option value="CONYUGE">CONYUGE</option>
 <option value="NIETO/A">NIETO/A</option>
 <option value="HERMANO/A">HERMANO/A</option>
 <option value="SOBRINO/A">SOBRINO/A</option>
 <option value="TUTOR">TUTOR</option>
 <option value="OTRO">OTRO</option>
 </select>
 @error('selected_parentesco') <span class="mt-1 block text-[8px] font-black text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div class="flex flex-col gap-1.5 justify-center pl-2 pt-1">
 <label class="relative inline-flex items-center cursor-pointer select-none">
 <input type="checkbox" wire:model="selected_es_responsable" class="sr-only peer">
 <div class="w-7 h-4 bg-fondo-panel rounded-full peer peer-focus:ring-2 peer-focus:ring-[#E27D60]/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-fondo-card after:border-borde-suave after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-estado-exitoBg"></div>
 <span class="ml-2 text-[8px] font-black uppercase tracking-wider text-parrafo">Resp. Principal</span>
 </label>
 <label class="relative inline-flex items-center cursor-pointer select-none">
 <input type="checkbox" wire:model="selected_responsable_salud" class="sr-only peer">
 <div class="w-7 h-4 bg-fondo-panel rounded-full peer peer-focus:ring-2 peer-focus:ring-[#E27D60]/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-fondo-card after:border-borde-suave after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-fondo-panel"></div>
 <span class="ml-2 text-[8px] font-black uppercase tracking-wider text-parrafo">Resp. Salud</span>
 </label>
 <label class="relative inline-flex items-center cursor-pointer select-none">
 <input type="checkbox" wire:model="selected_responsable_economico" class="sr-only peer">
 <div class="w-7 h-4 bg-fondo-panel rounded-full peer peer-focus:ring-2 peer-focus:ring-[#E27D60]/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-fondo-card after:border-borde-suave after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-fondo-panel"></div>
 <span class="ml-2 text-[8px] font-black uppercase tracking-wider text-parrafo">Resp. Económico</span>
 </label>
 </div>
 </div>
 <div class="grid gap-3 sm:grid-cols-4 items-end">
 <div class="sm:col-span-3">
 <label class="mb-1 block text-[8px] font-black uppercase tracking-widest text-apoyo">Notas / Observaciones del Vínculo</label>
 <input type="text" wire:model="selected_observaciones" placeholder="Ej. A cargo del seguimiento médico semanal" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 </div>
 <button type="button" wire:click="vincularAdultoMayor" 
 class="w-full h-10 rounded-xl bg-boton-principal text-inverso text-[8px] font-black uppercase tracking-widest transition hover:bg-boton-acento shadow-md flex items-center justify-center gap-1.5 active:scale-95">
 <i class="ph-bold ph-plus-circle text-xs"></i> Vincular Adulto
 </button>
 </div>
 </div>
 @endif

 {{-- 3. LISTADO DE ADULTOS MAYORES VINCULADOS --}}
 <div class="space-y-2">
 <h5 class="text-[9px] font-bold uppercase tracking-widest text-parrafo/80 flex items-center gap-1.5">
 <i class="ph-bold ph-link text-boton-acento"></i> Adultos Mayores Vinculados a este Familiar 
 <span class="px-2 py-0.5 rounded-full bg-estado-peligroBg text-boton-acento text-[8px] font-black">
 {{ count($vinculosFamiliar) }}
 </span>
 </h5>

 @if(count($vinculosFamiliar) === 0)
 <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-borde-suave rounded-2xl bg-fondo-panel text-center">
 <i class="ph-bold ph-link-break text-xl text-meta mb-1"></i>
 <p class="text-[9px] font-bold text-apoyo">Sin vinculaciones. Agrega al menos un adulto mayor de la lista superior.</p>
 </div>
 @else
 <div class="overflow-x-auto rounded-xl border border-borde-suave bg-fondo-card">
 <table class="w-full border-collapse text-left">
 <thead>
 <tr class="bg-fondo-panel text-[8px] font-black uppercase tracking-widest text-parrafo/75 border-b border-borde-suave">
 <th class="px-3 py-2">Adulto Mayor</th>
 <th class="px-3 py-2">Vínculo/Parentesco</th>
 <th class="px-3 py-2 text-center">Responsabilidades</th>
 <th class="px-3 py-2">Observaciones</th>
 <th class="px-3 py-2 text-center">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/20">
 @foreach($vinculosFamiliar as $i => $v)
 <tr class="text-[9px] font-bold text-parrafo/85 hover:bg-fondo-panel transition">
 <td class="px-3 py-2">
 <span class="font-black text-parrafo">{{ $v['nombres_completos'] }}</span>
 </td>
 <td class="px-3 py-2">
 <span class="px-2 py-0.5 rounded bg-fondo-panel text-parrafo text-[8px] font-black uppercase">{{ $v['parentesco_vinculo'] }}</span>
 </td>
 <td class="px-3 py-2 text-center space-y-1">
 <div class="flex flex-col gap-1 items-center">
 @if($v['es_responsable'] === 'SI')
 <span class="px-1.5 py-0.5 rounded bg-estado-exitoBg text-estado-exito text-[8px] font-black uppercase block">PRINCIPAL</span>
 @endif
 @if(($v['responsable_salud'] ?? 'NO') === 'SI')
 <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[8px] font-black uppercase block">SALUD</span>
 @endif
 @if(($v['responsable_economico'] ?? 'NO') === 'SI')
 <span class="px-1.5 py-0.5 rounded bg-yellow-50 text-yellow-600 text-[8px] font-black uppercase block">ECONÓMICO</span>
 @endif
 @if($v['es_responsable'] !== 'SI' && ($v['responsable_salud'] ?? 'NO') !== 'SI' && ($v['responsable_economico'] ?? 'NO') !== 'SI')
 <span class="px-1.5 py-0.5 rounded bg-fondo-panel text-apoyo text-[8px] font-black uppercase block">NINGUNO</span>
 @endif
 </div>
 </td>
 <td class="px-3 py-2 text-apoyo">
 {{ $v['observaciones'] ?: 'Sin observaciones adicionales' }}
 </td>
 <td class="px-3 py-2 text-center">
 <button type="button" wire:click="desvincularAdultoMayor({{ $i }})"
 class="h-6 w-6 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-inverso transition inline-flex items-center justify-center active:scale-90 shadow-sm"
 title="Eliminar vinculación">
 <i class="ph-bold ph-trash text-xs"></i>
 </button>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endif
 </div>

 {{-- Campo General de Observaciones --}}
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-parrafo">Observación General de Vinculación Familiar</label>
 <input type="text" wire:model="observacion_vinculo" placeholder="Ej. Hijo tutor legal de adulto mayor" class="uppercase w-full h-10 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 @error('observacion_vinculo') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>
 @endif

 </div>
 </div>
 @endif

 {{-- PASO 4: SEGURIDAD --}}
 @if($pasoFormulario === 4)
 <div class="space-y-4 animate-in slide-in-from-right-4 duration-300" x-data="{ showPass: false, showConfirm: false }">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-shield-check text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Seguridad de Acceso</h3>
 </div>
 <div class="grid gap-4 md:grid-cols-2 max-w-2xl">
 <div class="md:col-span-2">
 @if(!$isEdit)
 @if(false) {{-- Ocultar el texto estático antiguo --}}
 <div class="bg-fondo-panel border-l-4 border-borde-fuerte p-4 rounded-r-xl space-y-2">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-key text-parrafo text-lg"></i>
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Contraseña Temporal y Notificación</h4>
 </div>
 <p class="text-[10px] font-bold text-parrafo/75 leading-relaxed">
 Se generará una contraseña temporal de alta seguridad compleja (11 caracteres aleatorios, incluyendo mayúsculas, minúsculas, números y símbolos especiales) de manera totalmente automática al confirmar el registro.
 </p>
 <p class="text-[10px] font-bold text-boton-acento uppercase leading-relaxed">
 ⚠️ Se le enviará automáticamente un correo electrónico de bienvenida con sus credenciales de acceso inicial y una directiva obligatoria de cambio de contraseña al ingresar por primera vez.
 </p>
 </div>
 @endif

 <div class="space-y-4">
 <div class="bg-fondo-panel border-l-4 border-borde-fuerte p-4 rounded-r-xl space-y-2">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-key text-parrafo text-lg"></i>
 <h4 class="text-[10px] font-bold text-parrafo uppercase tracking-widest">Contraseña Temporal de Acceso</h4>
 </div>
 <p class="text-[10px] font-bold text-parrafo/75 leading-relaxed">
 Esta es la contraseña temporal de alta seguridad generada por el sistema para el nuevo usuario. Puede copiarla o regenerar una nueva si lo desea.
 </p>
 </div>

 <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-fondo-panel border border-borde-suave p-4 rounded-2xl" x-data="{ showGenPass: false }">
 <div class="relative flex-1">
 <input :type="showGenPass ? 'text' : 'password'" 
 value="{{ $passwordTemporalVisual }}" 
 readonly
 class="w-full h-11 rounded-xl border border-borde bg-fondo-card/70 pl-4 pr-24 py-2 text-sm font-mono font-black tracking-widest text-parrafo outline-none">
 
 <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
 <!-- Toggle eye button -->
 <button type="button" 
 @click="showGenPass = !showGenPass" 
 class="h-8 w-8 flex items-center justify-center rounded-lg text-apoyo hover:text-parrafo hover:bg-fondo-panel transition"
 title="Mostrar/Ocultar contraseña">
 <i class="ph-bold text-base" :class="showGenPass ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>

 <!-- Clipboard copy button -->
 <button type="button" 
 onclick="navigator.clipboard.writeText('{{ $passwordTemporalVisual }}'); Swal.fire({ icon: 'success', title: 'Copiado', text: 'Contraseña temporal copiada al portapapeles.', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-[1.5rem]' } })"
 class="h-8 w-8 flex items-center justify-center rounded-lg text-apoyo hover:text-parrafo hover:bg-fondo-panel transition"
 title="Copiar al portapapeles">
 <i class="ph-bold ph-copy text-base"></i>
 </button>
 </div>
 </div>

 <!-- Regenerate button -->
 <button type="button" 
 wire:click="regenerarPasswordTemporal"
 class="h-11 px-5 inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento text-[10px] font-bold uppercase text-inverso shadow-md shadow-[#E27D60]/20 hover:bg-fondo-panel active:scale-95 transition">
 <i class="ph-bold ph-arrows-clockwise text-sm"></i>
 Regenerar contraseña temporal
 </button>
 </div>

 <div class="bg-estado-peligroBg border-l-4 border-borde-focus p-4 rounded-r-xl">
 <p class="text-[10px] font-bold text-boton-acento uppercase leading-relaxed">
 ⚠️ NOTA INSTITUCIONAL: Se le enviará automáticamente un correo electrónico de bienvenida con sus credenciales de acceso inicial y una directiva obligatoria de cambio de contraseña al ingresar por primera vez.
 </p>
 </div>
 </div>
 @else
 @if($usuarioId === auth()->id())
 <div class="bg-estado-peligroBg border-l-4 border-borde-focus p-4 rounded-r-xl">
 <p class="text-[10px] font-bold text-boton-acento leading-relaxed">
 Deje en blanco la contraseña si no desea cambiar su contraseña actual. Al registrar una nueva contraseña, se actualizará su acceso de forma inmediata.
 </p>
 </div>
 @else
 <div class="bg-fondo-panel border-l-4 border-borde-fuerte p-4 rounded-r-xl">
 <p class="text-[10px] font-bold text-parrafo leading-relaxed">
 No se puede editar directamente la contraseña de otro usuario para mantener el cumplimiento de las políticas de privacidad y seguridad institucional.
 </p>
 </div>
 @endif
 @endif
 </div>

 @if($isEdit)
 @if($usuarioId === auth()->id())
 <div class="md:col-span-2 grid gap-4 md:grid-cols-3">
 <div x-data="{ showActual: false }">
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-boton-acento">Contraseña Actual *</label>
 <div class="relative">
 <input :type="showActual ? 'text' : 'password'" wire:model="password_actual" placeholder="••••••••"
 class="w-full h-10 rounded-xl border {{ $errors->has('password_actual') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card pl-4 pr-10 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <button type="button" @click="showActual = !showActual" class="absolute right-3 top-1/2 -translate-y-1/2 text-meta hover:text-parrafo transition focus:outline-none">
 <i class="ph-bold text-base" :class="showActual ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 @error('password_actual') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Nueva Contraseña</label>
 <div class="relative">
 <input :type="showPass ? 'text' : 'password'" wire:model="password" placeholder="••••••••"
 class="w-full h-10 rounded-xl border {{ $errors->has('password') ? 'border-borde-focus' : 'border-borde' }} bg-fondo-card pl-4 pr-10 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-meta hover:text-parrafo transition focus:outline-none">
 <i class="ph-bold text-base" :class="showPass ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 @error('password') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Confirmar Contraseña</label>
 <div class="relative">
 <input :type="showConfirm ? 'text' : 'password'" wire:model="password_confirmation" placeholder="••••••••"
 class="w-full h-10 rounded-xl border border-borde bg-fondo-card pl-4 pr-10 py-2 text-sm font-bold text-parrafo outline-none transition focus:border-borde-fuerte">
 <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-meta hover:text-parrafo transition focus:outline-none">
 <i class="ph-bold text-base" :class="showConfirm ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 </div>
 </div>
 @else
 <div class="md:col-span-2 flex flex-col items-center justify-center p-6 bg-fondo-panel border border-borde-fuerte rounded-2xl space-y-3">
 <div class="flex h-12 w-12 items-center justify-center rounded-full bg-estado-peligroBg text-boton-acento shadow-sm">
 <i class="ph-bold ph-key text-xl animate-bounce"></i>
 </div>
 <div class="text-center max-w-md">
 <h4 class="text-xs font-bold text-parrafo uppercase tracking-wider">Restablecimiento de Credenciales</h4>
 <p class="mt-1 text-[10px] font-semibold text-parrafo/65 leading-relaxed">
 Para mantener altos estándares de seguridad, no se puede ver ni editar directamente la contraseña actual de otro usuario.
 Presione el botón para generar una clave temporal de 11 caracteres que se notificará de forma automatizada por correo electrónico.
 </p>
 </div>
 <button type="button"
 wire:click="restablecerPasswordUsuario('{{ $usuarioId }}')"
 wire:confirm="¿Está seguro de que desea restablecer la contraseña de este usuario? Se generará una clave temporal y se le enviará por correo."
 class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-[9px] font-bold uppercase tracking-wider text-inverso shadow-md hover:bg-boton-acento active:scale-95 transition-all duration-300">
 <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i> Restablecer Contraseña Temporal
 </button>
 </div>
 @endif
 @endif
 </div>
 </div>
 @endif

 {{-- PASO 5: CONFIRMACIÓN --}}
 @if($pasoFormulario === 5)
 <div class="space-y-4 animate-in zoom-in duration-300" x-data="{ showPassSummary: false }">
 <div class="flex items-center gap-3 border-b border-borde-suave pb-2">
 <i class="ph-fill ph-check-square text-xl text-boton-acento"></i>
 <h3 class="text-xs font-bold text-parrafo uppercase tracking-widest">Resumen de Registro</h3>
 </div>
 <div class="grid gap-4 lg:grid-cols-2">
 <div class="rounded-xl bg-fondo-card/50 p-5 border border-borde-suave space-y-4">
 <div class="flex items-center gap-4">
 @if($foto_de_perfil_upload)
 <img src="{{ $foto_de_perfil_upload->temporaryUrl() }}" 
 class="h-16 w-16 rounded-[1.1rem] object-cover ring-2 ring-[#E27D60] shadow">
 @elseif($isEdit && $cod_usu && \App\Models\User::find($cod_usu)?->foto_de_perfil)
 <img src="{{ asset('storage/' . \App\Models\User::find($cod_usu)->foto_de_perfil) }}" 
 class="h-16 w-16 rounded-[1.1rem] object-cover ring-2 ring-[#2F3E5C]/30 shadow">
 @else
 <div class="flex h-16 w-16 items-center justify-center rounded-[1.1rem] bg-boton-principal text-xl font-extrabold text-inverso shadow uppercase">
 {{ mb_substr($nombres ?? 'U', 0, 1) }}{{ mb_substr($ap_paterno ?? 'I', 0, 1) }}
 </div>
 @endif
 <div>
 <h4 class="text-sm font-bold text-parrafo uppercase leading-tight">{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</h4>
 <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full bg-estado-peligroBg text-boton-acento text-[8px] font-black uppercase tracking-widest border border-borde-focus">
 {{ str_replace('_', ' ', $rol) }}
 </span>
 </div>
 </div>
 <div class="grid grid-cols-2 gap-3 border-t border-borde-suave pt-3">
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Documento Identidad</p>
 <p class="text-[10px] font-bold text-parrafo uppercase mt-0.5">{{ $numero_documento }} {{ $expedido }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Nacionalidad / Emisor</p>
 <p class="text-[10px] font-bold text-parrafo uppercase mt-0.5">{{ $pais_documento }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Celular de Contacto</p>
 <p class="text-[10px] font-bold text-parrafo mt-0.5">{{ $codigo_telefono }} {{ $telefono }}</p>
 </div>
 <div>
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Correo Institucional</p>
 <p class="text-[10px] font-bold text-parrafo lowercase mt-0.5 truncate">{{ $correo }}</p>
 </div>
 <div class="col-span-2 border-t border-borde/10 pt-2">
 <p class="text-[8px] font-black text-meta uppercase tracking-wider">Dirección Domicilio</p>
 <p class="text-[10px] font-bold text-parrafo mt-0.5 leading-tight">
 {{ $direccion ?: 'No registrada' }} {{ $zona ? '('.$zona.')' : '' }} {{ $ciudad ? '- '.$ciudad : '' }}
 </p>
 </div>
 </div>
 </div>

 <div class="rounded-xl bg-fondo-card/50 p-5 border border-borde-suave flex flex-col justify-between space-y-4">
 <div class="space-y-3">
 @if(!$isEdit && $passwordTemporalVisual)
 <div class="p-3 bg-estado-peligroBg border border-borde-focus rounded-xl space-y-1 text-center">
 <span class="text-[8px] font-black text-boton-acento uppercase tracking-widest block">Contraseña Temporal Generada:</span>
 <div class="flex items-center justify-center gap-2 mt-1">
 <div class="text-sm font-mono font-black text-parrafo bg-fondo-card border border-borde-suave px-3 py-1.5 rounded-lg select-all cursor-pointer inline-flex items-center gap-2" title="Click para copiar">
 <span x-text="showPassSummary ? '{{ $passwordTemporalVisual }}' : '••••••••••••'"></span>
 </div>
 <button type="button" @click="showPassSummary = !showPassSummary" class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card border border-borde-suave text-meta hover:text-parrafo transition shadow-sm active:scale-95">
 <i class="ph-bold" :class="showPassSummary ? 'ph-eye-slash' : 'ph-eye'"></i>
 </button>
 </div>
 <p class="text-[8px] text-meta font-bold leading-normal">
 Esta clave se enviará al correo y no se volverá a mostrar en el panel por razones de seguridad.
 </p>
 </div>
 @endif

 <div class="space-y-1">
 <span class="text-[8px] font-black text-meta uppercase tracking-widest block">Próximos Pasos de Cumplimiento:</span>
 <div class="bg-fondo-card/80 p-3 rounded-xl border border-borde-suave text-[9px] font-bold text-parrafo/75 space-y-2">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-square text-boton-acento"></i>
 <span>Asignar Turnos y Horarios Semanales</span>
 </div>
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-square text-boton-acento"></i>
 <span>Validación de Carpeta de Documentación Obligatoria</span>
 </div>
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-square text-boton-acento"></i>
 <span>Primer Acceso con Cambio de Clave Obligatorio</span>
 </div>
 </div>
 </div>
 </div>
 <p class="text-[9px] font-bold text-parrafo/45 text-center leading-relaxed italic">
 Al confirmar, se guardará de manera definitiva esta ficha de personal institucional.
 </p>
 </div>
 </div>
 </div>
 @endif
 </div>

 {{-- FOOTER FIJO --}}
 <footer class="border-t border-borde-suave bg-fondo-panel px-4 py-2.5 backdrop-blur-xl shrink-0 flex flex-col-reverse sm:flex-row items-center justify-between gap-2">
 <button type="button" wire:click="cerrarFormulario" 
 class="w-full sm:w-auto px-6 py-2.5 rounded-xl border-2 border-borde-fuerte text-meta text-[9px] font-bold uppercase tracking-widest transition hover:bg-boton-principal hover:text-inverso active:scale-95 shadow-sm">
 Cancelar
 </button>
 
 <div class="flex items-center gap-2 w-full sm:w-auto">
 @if($pasoFormulario > 1)
 <button type="button" wire:click="anteriorPaso" 
 class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl border-2 border-borde-fuerte text-parrafo text-[9px] font-bold uppercase tracking-widest transition hover:bg-boton-principal hover:text-inverso active:scale-95">
 Anterior
 </button>
 @endif

 @if($pasoFormulario < 5)
 <button type="button" wire:click="siguientePaso" 
 class="flex-1 sm:flex-none px-10 py-2.5 rounded-xl bg-boton-principal text-inverso text-[9px] font-bold uppercase tracking-widest shadow-xl shadow-[#2F3E5C]/20 transition hover:bg-boton-acento active:scale-95">
 Continuar <i class="ph-bold ph-arrow-right ml-1"></i>
 </button>
 @else
 <button type="button" wire:click="guardarUsuario" 
 wire:loading.attr="disabled" 
 wire:target="guardarUsuario"
 class="flex-1 sm:flex-none px-12 py-2.5 rounded-xl bg-boton-acento text-inverso text-[9px] font-bold uppercase tracking-widest shadow-xl shadow-[#E27D60]/20 transition hover:bg-boton-principal active:scale-95 disabled:opacity-70 inline-flex items-center justify-center gap-2 min-w-[140px]">
 
 <!-- Spinner de Carga SVG Premium -->
 <span wire:loading wire:target="guardarUsuario" class="animate-spin h-3.5 w-3.5 text-inverso">
 <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 </span>

 <!-- Icono de Confirmación Normal (oculto al cargar) -->
 <span wire:loading.remove wire:target="guardarUsuario">
 <i class="ph-bold {{ $usuarioId ? 'ph-floppy-disk' : 'ph-check' }} text-xs"></i>
 </span>

 <span>{{ $usuarioId ? 'Actualizar' : 'Confirmar' }}</span>
 </button>
 @endif
 </div>
 </footer>
 </div>
 </div>
 @endif

 {{-- VISTA COMPLETA FLOTANTE (Modal Amplio) --}}
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

 {{-- FICHA RÁPIDA FLOTANTE --}}
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

 {{-- MODAL DE ÉXITO POST-REGISTRO --}}
 @if($mostrarPostRegistro && $usuarioPostRegistro)
 <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
 <div class="relative w-full max-w-lg rounded-[2rem] border border-borde-suave bg-fondo-app shadow-2xl p-6 md:p-8 space-y-6">
 <div class="text-center space-y-2">
 <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-estado-exitoBg text-estado-exito">
 <i class="ph-bold ph-check-circle text-4xl animate-bounce"></i>
 </div>
 <h3 class="text-2xl font-black text-parrafo uppercase tracking-wide">¡Usuario Registrado!</h3>
 <p class="text-xs font-bold text-parrafo/75">El usuario institucional ha sido registrado en el sistema.</p>
 </div>

 {{-- Estado del correo --}}
 <div class="rounded-2xl border border-borde-suave bg-fondo-card/40 p-4 space-y-2">
 <div class="flex items-center gap-2">
 @if($correoRequisitosEnviado)
 <i class="ph-bold ph-paper-plane-tilt text-lg text-estado-exito"></i>
 <span class="text-xs font-bold uppercase text-estado-exito">Correo de requisitos enviado</span>
 @else
 <i class="ph-bold ph-warning text-lg text-boton-acento"></i>
 <span class="text-xs font-bold uppercase text-boton-acento">Advertencia de correo</span>
 @endif
 </div>
 <p class="text-xs font-bold text-parrafo/80">
 {{ $mensajeCorreoRequisitos ?: 'Enviando correo con la solicitud documental obligatoria...' }}
 </p>
 </div>

 {{-- Resumen de credenciales --}}
 <div class="rounded-2xl border border-borde-suave bg-fondo-card/40 p-4 space-y-2">
 <span class="text-[9px] font-bold uppercase tracking-widest text-apoyo block">Credenciales de Acceso</span>
 <div class="grid grid-cols-2 gap-3 text-xs">
 <div>
 <span class="font-bold text-meta block">Usuario / Correo:</span>
 <span class="font-black text-parrafo break-all">{{ $usuarioPostRegistro->correo ?: 'Sin correo' }}</span>
 </div>
 <div>
 <span class="font-bold text-meta block">Contraseña Temporal:</span>
 <span class="font-mono font-black text-parrafo bg-fondo-card border border-borde-suave px-2 py-0.5 rounded select-all cursor-pointer" title="Haga clic para copiar">{{ $passwordTemporalPostRegistro ?: 'Autogenerada' }}</span>
 </div>
 </div>
 </div>

 {{-- Documentación pendiente --}}
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 space-y-3">
 <div class="flex justify-between items-center border-b border-borde/25 pb-1">
 <span class="text-[9px] font-bold uppercase tracking-widest text-boton-acento">Requisitos Documentales Obligatorios</span>
 <span class="text-[8px] font-black uppercase tracking-widest text-boton-acento bg-estado-peligroBg px-2 py-0.5 rounded-full">Límite 48 horas</span>
 </div>
 @if(!empty($documentosRequeridos))
 <ul class="space-y-1.5 max-h-[120px] overflow-y-auto custom-scrollbar text-xs font-bold text-parrafo/80">
 @foreach($documentosRequeridos as $docReq)
 <li class="flex items-center gap-2">
 <i class="ph-bold ph-file-text text-sm text-apoyo"></i>
 <span class="uppercase">{{ $docReq }}</span>
 </li>
 @endforeach
 </ul>
 @else
 <p class="text-xs font-bold text-apoyo">Ningún requisito documental obligatorio para este rol.</p>
 @endif
 </div>
 </div>

 {{-- Paquete Documental Institucional --}}
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 space-y-3 shadow-inner">
 <span class="text-[9px] font-bold uppercase tracking-widest text-apoyo block text-center">Gestión de Paquete Documental Institucional</span>
 <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
 <a href="{{ route('admin.usuarios.documentos.pdf', $usuarioPostRegistro->cod_usu) }}" target="_blank"
 class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-fondo-panel text-xs font-bold text-parrafo border border-borde-fuerte transition hover:bg-boton-principal hover:text-inverso active:scale-95 shadow-sm">
 <i class="ph-bold ph-printer"></i> Imprimir
 </a>
 <a href="{{ route('admin.usuarios.documentos.pdf', $usuarioPostRegistro->cod_usu) }}" target="_blank" download="Paquete_Documental.pdf"
 class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-fondo-panel text-xs font-bold text-parrafo border border-borde-fuerte transition hover:bg-boton-principal hover:text-inverso active:scale-95 shadow-sm">
 <i class="ph-bold ph-download-simple"></i> Descargar PDF
 </a>
 <button type="button" 
 onclick="enviarPaqueteCorreo('{{ $usuarioPostRegistro->cod_usu }}', '{{ $usuarioPostRegistro->correo }}')"
 class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-estado-peligroBg text-xs font-bold text-boton-acento border border-borde-focus transition hover:bg-boton-acento hover:text-inverso active:scale-95 shadow-sm">
 <i class="ph-bold ph-envelope"></i> Enviar Correo
 </button>
 </div>
 </div>

 @php
 if($usuarioPostRegistro) {
 $docServicePR = app(\App\Services\Usuarios\DocumentosUsuarioService::class);
 $rolKeyPR = $usuarioPostRegistro->roles->first()?->name ?? 'sin_rol';
 $docsInstitucionalesPR = $docServicePR->documentosGeneradosPorRol($rolKeyPR);
 } else {
 $docsInstitucionalesPR = [];
 }
 @endphp
 @if(!empty($docsInstitucionalesPR))
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 space-y-3 shadow-inner max-h-48 overflow-y-auto custom-scrollbar">
 <span class="text-[9px] font-bold uppercase tracking-widest text-apoyo block">Documentos Individuales Generados</span>
 <div class="grid grid-cols-1 gap-2">
 @foreach($docsInstitucionalesPR as $docInst)
 <div class="flex items-center justify-between p-2 rounded-xl border border-borde-suave bg-fondo-card">
 <div class="flex items-center gap-2">
 <i class="ph-fill ph-file-pdf text-boton-acento text-lg"></i>
 <span class="text-[10px] font-bold text-parrafo uppercase">{{ $docInst['nombre'] }}</span>
 </div>
 <div class="flex gap-1">
 <a href="{{ route('admin.usuarios.documentos.ver', ['user' => $usuarioPostRegistro->cod_usu, 'documento' => $docInst['slug']]) }}" target="_blank"
 class="h-7 w-7 flex items-center justify-center rounded-lg bg-fondo-panel text-parrafo hover:bg-boton-principal hover:text-inverso transition">
 <i class="ph-bold ph-eye"></i>
 </a>
 <a href="{{ route('admin.usuarios.documentos.documento-pdf', ['user' => $usuarioPostRegistro->cod_usu, 'documento' => $docInst['slug']]) }}" target="_blank" download
 class="h-7 w-7 flex items-center justify-center rounded-lg bg-estado-peligroBg text-boton-acento hover:bg-boton-acento hover:text-inverso transition">
 <i class="ph-bold ph-download-simple"></i>
 </a>
 </div>
 </div>
 @endforeach
 </div>
 </div>
 @endif

 {{-- Acciones --}}
 <div class="flex flex-col sm:flex-row gap-2 pt-2">
 <button type="button"
 wire:click="verFichaPostRegistro"
 class="flex-1 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-boton-principal px-5 text-xs font-bold text-inverso shadow-lg transition hover:bg-boton-acento active:scale-95">
 <i class="ph-bold ph-eye text-sm"></i>
 Ver Ficha
 </button>
 <button type="button"
 wire:click="cerrarPostRegistro"
 class="flex-1 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde-suave px-5 text-xs font-bold text-parrafo transition hover:bg-fondo-card/80 active:scale-95">
 <i class="ph-bold ph-x text-sm"></i>
 Cerrar y Volver
 </button>
 </div>
 </div>
 </div>
 @endif

 {{-- MODAL SUBIR DOCUMENTACIÓN --}}
 @if($mostrarModalSubirDoc && $tipoDocSeleccionado)
 <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
 <div class="relative w-full max-w-md rounded-[2rem] border border-borde-suave bg-fondo-app shadow-2xl p-6 space-y-5">
 <header class="flex items-center justify-between border-b border-borde-suave pb-3">
 <div>
 <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-boton-acento">Gestión Documental</span>
 <h3 class="text-base font-extrabold text-parrafo uppercase leading-tight">{{ $tipoDocNombre }}</h3>
 </div>
 <button type="button" wire:click="cerrarModalSubirDoc" class="flex h-8 w-8 items-center justify-center rounded-full bg-fondo-card/60 hover:bg-fondo-card text-parrafo transition active:scale-95">
 <i class="ph-bold ph-x text-lg"></i>
 </button>
 </header>

 <form wire:submit.prevent="guardarDocumento" class="space-y-4">
 {{-- Archivo Temporal --}}
 <div class="space-y-1">
 <label class="block text-[9px] font-bold uppercase tracking-widest text-apoyo">Archivo (PDF, JPG, JPEG, PNG - Max 10MB) *</label>
 <div class="relative flex flex-col items-center justify-center border-2 border-dashed border-borde rounded-xl bg-fondo-card/40 p-4 transition hover:bg-fondo-card/60">
 <input type="file" wire:model="archivoTemporal" id="archivoDoc" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.jpg,.jpeg,.png">
 
 <div class="text-center space-y-1 pointer-events-none">
 <i class="ph-bold ph-cloud-arrow-up text-3xl text-parrafo/45"></i>
 <p class="text-xs font-bold text-parrafo/80">
 @if($archivoTemporal)
 Archivo seleccionado: <span class="text-boton-acento font-black">{{ $archivoTemporal->getClientOriginalName() }}</span>
 @else
 Seleccionar o arrastrar archivo
 @endif
 </p>
 <p class="text-[9px] text-meta">Formatos: PDF, JPG, PNG de hasta 10 MB</p>
 </div>
 </div>
 @error('archivoTemporal') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 {{-- Fechas --}}
 <div class="grid grid-cols-2 gap-3">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha de Emisión</label>
 <input type="date" wire:model="fechaEmisionDoc" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('fechaEmisionDoc') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha de Vencimiento</label>
 <input type="date" wire:model="fechaVencimientoDoc" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('fechaVencimientoDoc') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 {{-- Observaciones --}}
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Observaciones / Detalles</label>
 <textarea wire:model="observacionesDoc" placeholder="Opcional. Escriba algún comentario relevante..." rows="3"
 class="uppercase w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus resize-none"></textarea>
 @error('observacionesDoc') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 {{-- Botones --}}
 <div class="flex items-center gap-2 pt-2">
 <button type="button" wire:click="cerrarModalSubirDoc" class="flex-1 inline-flex h-10 items-center justify-center rounded-xl bg-fondo-card border border-borde-suave text-xs font-bold text-parrafo transition hover:bg-fondo-card/80 active:scale-95">
 Cancelar
 </button>
 <button type="submit" class="flex-1 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-principal text-xs font-bold text-inverso shadow-lg transition hover:bg-estado-exitoBg active:scale-95">
 <i class="ph-bold ph-check"></i>
 Guardar Documento
 </button>
 </div>
 </form>
 </div>
 </div>
 @endif

 <style>
 .custom-scrollbar::-webkit-scrollbar {
 width: 6px;
 }
 .custom-scrollbar::-webkit-scrollbar-track {
 background: rgba(209, 184, 157, 0.2);
 border-radius: 10px;
 }
 .custom-scrollbar::-webkit-scrollbar-thumb {
 background: #C7B5A3;
 border-radius: 10px;
 }
 .custom-scrollbar::-webkit-scrollbar-thumb:hover {
 background: #2F3E5C;
 }
 @keyframes slideInRight {
 from { transform: translateX(100%); opacity: 0.7; }
 to { transform: translateX(0); opacity: 1; }
 }
 </style>
 <script>
 function enviarPaqueteCorreo(codUsu, correo) {
 if(!correo) {
 Swal.fire({
 icon: 'warning',
 title: 'Sin correo',
 text: 'Este usuario no tiene un correo electrónico registrado.',
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 return;
 }

 Swal.fire({
 title: '¿Enviar Paquete Documental?',
 text:"Se enviará el documento adjunto al correo:" + correo,
 icon: 'question',
 showCancelButton: true,
 confirmButtonColor: '#8DA280',
 cancelButtonColor: '#E27D60',
 confirmButtonText: 'Sí, enviar',
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) {
 Swal.fire({
 title: 'Enviando...',
 text: 'Por favor espere un momento',
 allowOutsideClick: false,
 didOpen: () => {
 Swal.showLoading();
 },
 customClass: { popup: 'rounded-[1.5rem]' }
 });

 fetch(`/admin/usuarios/${codUsu}/documentos/enviar`, {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
 }
 })
 .then(response => response.json())
 .then(data => {
 if (data.success) {
 Swal.fire({
 icon: 'success',
 title: '¡Enviado!',
 text: data.message,
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 } else {
 Swal.fire({
 icon: 'error',
 title: 'Error',
 text: data.message,
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 }
 })
 .catch(error => {
 Swal.fire({
 icon: 'error',
 title: 'Error de red',
 text: 'No se pudo completar la solicitud',
 confirmButtonColor: '#2F3E5C',
 customClass: { popup: 'rounded-[1.5rem]' }
 });
 });
 }
 });
 }
 </script>
</div>
