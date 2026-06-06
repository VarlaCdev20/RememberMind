<x-app-layout>
 <div class="bg-fondo-card p-4 sm:p-8 min-h-screen font-outfit text-titulo">
 {{-- Encabezado --}}
 <div class="mb-10 flex flex-col sm:flex-row items-center justify-between border-b-2 border-borde-fuerte pb-6">
 <div class="flex items-center gap-4">
 <div class="h-16 w-16 bg-boton-principal flex items-center justify-center rounded-2xl shadow-xl">
 <i class="ph-bold ph-chart-line-up text-inverso text-3xl"></i>
 </div>
 <div>
 <h1 class="text-3xl font-black uppercase tracking-tighter">RememberMind Dashboard</h1>
 <p class="text-xs font-bold uppercase tracking-[0.2em] text-apoyo">Resumen Ejecutivo de Gestión Institucional</p>
 </div>
 </div>
 <div class="mt-4 sm:mt-0 no-print">
 <button onclick="window.print()" class="rounded-full bg-boton-principal px-8 py-3 text-xs font-bold text-inverso shadow-xl transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-printer mr-2"></i> EXPORTAR REPORTE
 </button>
 </div>
 </div>

 {{-- Métricas de Impacto --}}
 <div class="mb-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
 <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm">
 <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-apoyo">Población Total</p>
 <div class="mt-2 flex items-end justify-between">
 <h3 class="text-4xl font-black text-titulo">{{ $stats['total'] }}</h3>
 <div class="text-right">
 <p class="text-xs font-bold text-parrafo">{{ $stats['activos'] }} Activos</p>
 <p class="text-xs font-bold text-parrafo">{{ $stats['archivados'] }} Archivados</p>
 </div>
 </div>
 </div>

 <div class="rounded-3xl border border-borde bg-boton-principal p-6 text-inverso shadow-xl">
 <p class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-40">Impacto en Seguimiento</p>
 <div class="mt-2 grid grid-cols-2 gap-2">
 <div>
 <p class="text-xl font-extrabold">{{ $stats['aten_totales'] }}</p>
 <p class="text-[9px] font-bold uppercase opacity-60">Atenciones</p>
 </div>
 <div>
 <p class="text-xl font-extrabold">{{ $stats['obs_totales'] }}</p>
 <p class="text-[9px] font-bold uppercase opacity-60">Observaciones</p>
 </div>
 </div>
 </div>

 <div class="rounded-3xl border border-borde bg-fondo-card p-6 shadow-sm">
 <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-apoyo">Calidad de Vida</p>
 <div class="mt-2 flex items-end justify-between">
 <div>
 <h3 class="text-4xl font-black text-parrafo">{{ $stats['act_totales'] }}</h3>
 <p class="text-[10px] font-bold uppercase text-apoyo">Actividades</p>
 </div>
 <div class="text-right">
 <p class="text-xl font-extrabold text-parrafo">{{ $stats['eval_totales'] }}</p>
 <p class="text-[10px] font-bold uppercase text-apoyo">Evaluaciones</p>
 </div>
 </div>
 </div>

 <div class="rounded-3xl border border-borde bg-fondo-panel p-6 text-inverso shadow-xl">
 <p class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-40">Alerta de Seguimiento</p>
 <div class="mt-2 flex items-end justify-between">
 <h3 class="text-4xl font-black">{{ $stats['sin_seguimiento'] }}</h3>
 <p class="text-xs font-bold opacity-80 max-w-[100px] text-right">Adultos sin registro en 30 días</p>
 </div>
 </div>
 </div>

 <div class="grid gap-8 lg:grid-cols-3">
 {{-- Listado de Población --}}
 <div class="lg:col-span-2">
 <div class="rounded-[2.5rem] border border-borde bg-fondo-card overflow-hidden shadow-sm">
 <div class="bg-fondo-panel px-8 py-5 border-b border-borde-suave">
 <h3 class="text-sm font-bold uppercase tracking-widest">Censo de Residentes</h3>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left">
 <thead>
 <tr class="text-[10px] font-bold uppercase tracking-widest text-apoyo border-b border-borde">
 <th class="px-8 py-4">Ficha</th>
 <th class="px-8 py-4">Residente</th>
 <th class="px-8 py-4">Edad</th>
 <th class="px-8 py-4 text-center">Estado</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#F2EBE3]">
 @foreach($adultos as $adulto)
 <tr class="hover:bg-fondo-panel transition-colors">
 <td class="px-8 py-4 text-xs font-bold text-apoyo">{{ $adulto->cod_am }}</td>
 <td class="px-8 py-4">
 <p class="text-sm font-bold text-titulo">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</p>
 <p class="text-[10px] font-bold text-apoyo">CI: {{ $adulto->ci }}</p>
 </td>
 <td class="px-8 py-4 text-sm font-bold text-apoyo">{{ \Carbon\Carbon::parse($adulto->fecha_nac)->age }} años</td>
 <td class="px-8 py-4 text-center">
 <span class="inline-block rounded-full px-3 py-1 text-[9px] font-bold uppercase tracking-tighter
 {{ $adulto->cod_est_adul == 1 ? 'bg-fondo-panel text-parrafo' : 'bg-fondo-panel text-parrafo' }}">
 {{ $adulto->estado->estado ?? 'N/A' }}
 </span>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>
 </div>

 {{-- Últimos Registros --}}
 <div class="lg:col-span-1 space-y-8">
 <div class="rounded-[2.5rem] border border-borde bg-fondo-panel p-8">
 <h3 class="text-sm font-bold uppercase tracking-widest mb-6">Nuevos Ingresos</h3>
 <div class="space-y-6">
 @foreach($stats['ultimos_adultos'] as $nuevo)
 <div class="flex items-center gap-4">
 <div class="h-12 w-12 rounded-2xl bg-fondo-card flex items-center justify-center shadow-sm text-titulo">
 <i class="ph-bold ph-user-plus text-xl"></i>
 </div>
 <div>
 <p class="text-sm font-bold">{{ $nuevo->nombres }}</p>
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-widest">Ingreso: {{ $nuevo->fecha_ing->format('d/m/Y') }}</p>
 </div>
 </div>
 @endforeach
 </div>
 </div>

 <div class="rounded-[2.5rem] bg-boton-principal p-8 text-inverso shadow-xl">
 <h3 class="text-sm font-bold uppercase tracking-widest mb-4 opacity-60">Demografía</h3>
 <div class="space-y-6">
 <div>
 <div class="flex justify-between text-xs font-bold uppercase mb-2">
 <span>Hombres</span>
 <span>{{ $stats['hombres'] }}</span>
 </div>
 <div class="h-2 w-full bg-fondo-card/10 rounded-full overflow-hidden">
 <div class="h-full bg-fondo-panel" style="width: {{ $stats['total'] > 0 ? ($stats['hombres']/$stats['total'])*100 : 0 }}%"></div>
 </div>
 </div>
 <div>
 <div class="flex justify-between text-xs font-bold uppercase mb-2">
 <span>Mujeres</span>
 <span>{{ $stats['mujeres'] }}</span>
 </div>
 <div class="h-2 w-full bg-fondo-card/10 rounded-full overflow-hidden">
 <div class="h-full bg-boton-acento" style="width: {{ $stats['total'] > 0 ? ($stats['mujeres']/$stats['total'])*100 : 0 }}%"></div>
 </div>
 </div>
 </div>
 <div class="mt-8 pt-6 border-t border-white/10">
 <p class="text-[10px] font-bold uppercase opacity-40">Edad Promedio</p>
 <p class="text-3xl font-black mt-1">{{ $stats['promedio_edad'] }} <span class="text-sm opacity-40">Años</span></p>
 </div>
 </div>
 </div>
 </div>

 {{-- Pie de Reporte --}}
 <div class="mt-20 flex flex-col items-center gap-4 text-center">
 <div class="h-1 w-20 bg-fondo-panel rounded-full"></div>
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-[0.4em]">Reporte Consolidado por RememberMind System</p>
 <p class="text-[9px] font-bold text-apoyo italic">Fecha de Generación: {{ now()->format('d/m/Y H:i:s') }} · CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS Administración</p>
 </div>
 </div>

 <style>
 @media print {
 body { background: white !important; }
 aside, nav, .no-print { display: none !important; }
 .p-4, .sm\:p-8 { padding: 0 !important; }
 .shadow-xl, .shadow-sm { box-shadow: none !important; }
 .rounded-3xl, .rounded-\[2\.5rem\], .rounded-2xl { border-radius: 0.5rem !important; }
 @page { margin: 1cm; }
 }
 </style>
</x-app-layout>
