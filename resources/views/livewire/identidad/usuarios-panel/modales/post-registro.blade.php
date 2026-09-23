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
 $docServicePR = app(\App\Services\Documentos\DocumentosUsuarioService::class);
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
