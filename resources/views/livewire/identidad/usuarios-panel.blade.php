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
 $documentacionService = app(\App\Services\Documentos\DocumentacionUsuarioService::class);
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
 $docService = app(\App\Services\Documentos\DocumentosUsuarioService::class);
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
 @include('livewire.identidad.usuarios-panel.modales.formulario')

 {{-- VISTA COMPLETA FLOTANTE (Modal Amplio) --}}
 @include('livewire.identidad.usuarios-panel.modales.vista-completa')

 {{-- FICHA RÁPIDA FLOTANTE --}}
 @include('livewire.identidad.usuarios-panel.modales.ficha-rapida')

 {{-- MODAL DE ÉXITO POST-REGISTRO --}}
 @include('livewire.identidad.usuarios-panel.modales.post-registro')

 {{-- MODAL SUBIR DOCUMENTACIÓN --}}
 @include('livewire.identidad.usuarios-panel.modales.subir-documento')

 <style>

{!! file_get_contents(resource_path('frontend/styles/modules/livewire-identidad-usuarios-panel.css')) !!}
</style>
 <script>

{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-identidad-usuarios-panel-2.js')) !!}
</script>
</div>
