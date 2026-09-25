@props(['registros' => []])

<section class="rm-card p-5">
 <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
 <div>
 <span class="text-[11px] font-bold uppercase tracking-widest text-boton-acento">
 Auditoría
 </span>
 <h2 class="text-xl font-extrabold text-titulo">Bitácora del sistema</h2>
 <p class="text-xs font-bold text-meta">Últimas acciones registradas mediante trazabilidad institucional.</p>
 </div>

 @can('bitacora.ver')
 <a href="{{ route('admin.bitacora.index') }}" class="rm-btn rm-btn-secondary text-xs py-1.5 px-3">
 <i class="ph-bold ph-list-magnifying-glass mr-1"></i>
 Ver bitácora completa
 </a>
 @endcan
 </div>

 <div class="overflow-x-auto rounded-[1.4rem] border border-borde-suave">
 <table class="min-w-full text-left text-sm rm-table">
 <thead class="rm-table-header">
 <tr>
 <th class="px-4 py-3 font-black">Fecha</th>
 <th class="px-4 py-3 font-black">Usuario</th>
 <th class="px-4 py-3 font-black">Acción</th>
 <th class="px-4 py-3 font-black">Módulo</th>
 </tr>
 </thead>

 <tbody class="divide-y divide-borde-suave bg-fondo-tabla">
 @forelse(array_slice($registros, 0, 5) as $log)
 <tr class="rm-table-row">
 <td class="px-4 py-3 text-xs font-bold text-meta">
 {{ $log['fecha'] ?? '-' }}
 </td>

 <td class="px-4 py-3 font-bold text-sm text-titulo">
 {{ $log['usuario'] ?? 'Sistema' }}
 </td>

 <td class="px-4 py-3">
 @php
 $color = match($log['accion'] ?? '') {
 'Registro creado' => 'rm-badge-success',
 'Registro actualizado' => 'rm-badge-info',
 'Registro eliminado' => 'rm-badge-danger',
 'Inicio de sesión', 'Acceso al sistema' => 'rm-badge-neutral',
 default => 'rm-badge-neutral',
 };
 @endphp
 <span class="rm-badge {{ $color }}">{{ $log['accion'] ?? 'Acción' }}</span>
 </td>

 <td class="px-4 py-3 text-xs font-bold text-apoyo">
 {{ $log['modulo'] ?? 'General' }}
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center text-sm font-bold text-meta">
 No hay registros recientes en la bitácora.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</section>
