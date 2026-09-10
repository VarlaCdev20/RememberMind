<x-sistema-layout>
 <div class="relative mx-auto max-w-7xl space-y-6 py-6">
 {{-- ENCABEZADO --}}
 <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-6 shadow-[0_14px_32px_rgba(47,62,92,0.08)] backdrop-blur-xl">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Seguimiento Prioritario
 </span>
 <h1 class="mt-1.5 text-2xl font-black text-titulo">Alertas y Pendientes</h1>
 <p class="mt-1.5 text-sm font-semibold text-apoyo">
 Consola de alertas cognitivas, documentales y notificaciones críticas de adultos mayores.
 </p>
 </div>
 <div>
 <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold text-inverso shadow-md transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrow-left text-sm"></i> Volver al Centro
 </a>
 </div>
 </div>
 </section>

 {{-- CONTENIDO DEL PLACEHOLDER --}}
 <section class="rounded-2xl border border-borde-suave bg-fondo-card p-12 text-center shadow-sm flex flex-col items-center justify-center min-h-[400px]">
 <div class="h-20 w-20 rounded-full bg-estado-peligroBg text-boton-acento border border-borde-focus shadow-inner flex items-center justify-center mb-5 animate-pulse">
 <i class="ph-bold ph-bell-ringing text-4xl"></i>
 </div>
 
 <h2 class="text-2xl font-black text-titulo tracking-tight">Módulo en Desarrollo Posterior</h2>
 <p class="mt-3 max-w-xl text-sm font-semibold leading-relaxed text-apoyo">
 La consola de <strong>Alertas y Pendientes</strong> se encuentra planificada como parte de la <strong>Fase 3</strong> de la reestructuración del módulo. 
 </p>
 <p class="mt-2 max-w-xl text-xs font-medium leading-relaxed text-apoyo bg-fondo-panel p-3 rounded-xl border border-borde-suave">
 Muy pronto, en este tablero consolidado se listarán en tiempo real las alertas de documentos faltantes (CI/Consentimiento), alertas de familiares responsables sin registrar y controles cognitivos (Mini-mental) con más de 6 meses de antigüedad, permitiendo disparar acciones correctivas con un solo clic.
 </p>

 <div class="mt-8 flex gap-3">
 <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-app border border-borde-suave px-5 py-3 text-xs font-bold text-titulo transition hover:bg-fondo-app active:scale-95">
 <i class="ph-bold ph-users-three text-sm"></i> Ver Adultos Mayores
 </a>
 </div>
 </section>
 </div>
</x-sistema-layout>
