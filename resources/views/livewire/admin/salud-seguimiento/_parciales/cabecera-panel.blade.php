{{--
 Parcial: _parciales/cabecera-panel.blade.php
 Cabecera reutilizable para todos los paneles individuales de Salud y Seguimiento.

 Variables esperadas (vía @include):
 - $titulo : string — nombre del módulo
 - $subtitulo : string — descripción breve
 - $icono : string — clase phosphor (ej. 'ph-pill')
 - $rutaVolver : string — URL del botón volver (usualmente resumen del adulto)
 - $adulto : AdultoMayor — modelo del paciente
--}}
<div class="rm-page-header">
 <div class="flex items-center gap-4">
 {{-- Botón volver al resumen --}}
 <a href="{{ $rutaVolver }}"
 class="rm-btn-icon"
 title="Volver al resumen de salud">
 <i class="ph-bold ph-arrow-left"></i>
 </a>

 {{-- Ícono del módulo --}}
 <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-boton-principal shadow-sm">
 <i class="ph-bold {{ $icono }} text-xl text-inverso"></i>
 </div>

 <div>
 <h1 class="rm-section-title">{{ $titulo }}</h1>
 <p class="rm-section-subtitle">
 {{ $adulto->nombres }} {{ $adulto->ap_paterno }}
 <span class="mx-1.5 text-boton-acento">•</span>
 <span class="font-black text-boton-acento">{{ $adulto->cod_am }}</span>
 </p>
 @if(!empty($subtitulo))
 <p class="mt-0.5 text-xs font-bold text-titulo/45">{{ $subtitulo }}</p>
 @endif
 </div>
 </div>

 {{-- Accesos rápidos al resto del módulo salud --}}
 <div class="flex flex-wrap gap-2">
 <a href="{{ route('admin.salud-seguimiento.resumen', $adulto->cod_am) }}"
 class="rm-btn-ghost text-xs"
 title="Ir al resumen de salud">
 <i class="ph-bold ph-heartbeat"></i>
 <span class="hidden sm:inline">Resumen</span>
 </a>
 <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_am) }}"
 class="rm-btn-ghost text-xs"
 title="Ir a la ficha integral del adulto mayor">
 <i class="ph-bold ph-identification-card"></i>
 <span class="hidden sm:inline">Ficha integral</span>
 </a>
 </div>
</div>
