@props(['registros' => []])

<section class="rm-card p-5">
 <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
 <div>
 <span class="rm-caption text-boton-acento">
 Auditoría
 </span>
 <h2 class="rm-section-title">Bitácora del sistema</h2>
 <p class="rm-caption">Últimas acciones registradas mediante trazabilidad institucional.</p>
 </div>

 @can('bitacora.ver')
 <a href="{{ route('admin.bitacora.index') }}" class="rm-btn rm-btn-secondary text-xs py-1.5 px-3">
 <i class="ph-bold ph-list-magnifying-glass mr-1"></i>
 Ver bitácora completa
 </a>
 @endcan
 </div>

 <div class="overflow-x-auto rounded-2xl border border-borde-suave">
 <table class="rm-data-table min-w-full text-left text-sm rm-table">
 <thead class="rm-table-header">
 <tr>
 <th class="px-4 py-3 rm-table-head">Fecha</th>
 <th class="px-4 py-3 rm-table-head">Usuario</th>
 <th class="px-4 py-3 rm-table-head">Acción</th>
 <th class="px-4 py-3 rm-table-head">Módulo</th>
 </tr>
 </thead>

 <tbody class="divide-y divide-borde-suave bg-fondo-tabla">
 @forelse(array_slice($registros, 0, 5) as $log)
 <tr class="rm-table-row">
 <td class="px-4 py-3 rm-caption">
 {{ $log['fecha'] ?? '-' }}
 </td>

 <td class="px-4 py-3 rm-table-cell">
 {{ $log['usuario'] ?? 'Sistema' }}
 </td>

 <td class="px-4 py-3 rm-table-cell">
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

 <td class="px-4 py-3 rm-table-cell">
 {{ $log['modulo'] ?? 'General' }}
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center rm-table-cell">
 No hay registros recientes en la bitácora.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</section>
