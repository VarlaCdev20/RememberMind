{{-- TAB SALUD Y SEGUIMIENTO (RESUMEN NAVEGACIONAL INTERNO) --}}
<section
 x-show="tab === 'salud'"
 style="display: none;"
 x-transition.opacity.duration.250ms
 x-data="{ vistaSaludActiva: 'resumen' }"
 class="space-y-6"
>
 <!-- HEADER Y NAVEGACIÓN INTERNA -->
 <div class="flex flex-col gap-4 border-b border-borde pb-5">
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-3">
 <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-fill ph-heartbeat text-3xl"></i>
 </div>
 <div>
 <h2 class="text-2xl font-black tracking-tight text-titulo">Resumen de Salud y Seguimiento</h2>
 <p class="text-sm font-semibold text-apoyo">Vistas resumidas de la condición clínica y operativa.</p>
 </div>
 </div>
 <a 
 href="{{ route('admin.salud-seguimiento.resumen', $adulto->cod_am) }}"
 class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm hover:bg-fondo-panel active:scale-95 transition shrink-0"
 >
 <i class="ph-bold ph-arrow-square-out text-md"></i>
 <span>Gestión Médica Completa</span>
 </a>
 </div>

 <!-- TABS INTERNOS -->
 <div class="flex flex-wrap items-center gap-2 mt-2">
 @php
 $tabsSalud = [
 ['id' => 'resumen', 'label' => 'Resumen general', 'icon' => 'ph-squares-four'],
 ['id' => 'ficha_medica', 'label' => 'Ficha Médica', 'icon' => 'ph-folder-user'],
 ['id' => 'signos_vitales', 'label' => 'Signos Vitales', 'icon' => 'ph-activity'],
 ['id' => 'medicacion', 'label' => 'Medicación', 'icon' => 'ph-pill'],
 ['id' => 'administracion', 'label' => 'Administración', 'icon' => 'ph-calendar-check'],
 ['id' => 'valoracion', 'label' => 'Valoración Func.', 'icon' => 'ph-wheelchair'],
 ['id' => 'evaluaciones', 'label' => 'Eval. Geriátricas', 'icon' => 'ph-list-magnifying-glass'],
 ['id' => 'alertas', 'label' => 'Alertas', 'icon' => 'ph-bell-ringing'],
 ['id' => 'reportes', 'label' => 'Reportes', 'icon' => 'ph-chart-line-up'],
 ];
 @endphp
 @foreach($tabsSalud as $ts)
 <button 
 type="button" 
 @click="vistaSaludActiva = '{{ $ts['id'] }}'"
 :class="vistaSaludActiva === '{{ $ts['id'] }}' ? 'bg-boton-acento text-inverso border-borde-focus' : 'bg-fondo-card text-apoyo border-borde hover:bg-fondo-panel'"
 class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-bold uppercase tracking-wider transition-colors"
 >
 <i class="ph-bold {{ $ts['icon'] }}"></i> {{ $ts['label'] }}
 </button>
 @endforeach
 </div>
 </div>

 <!-- CONTENIDO INTERNO: RESUMEN GENERAL -->
 <div x-show="vistaSaludActiva === 'resumen'" x-transition.opacity>
 <div class="grid gap-6 lg:grid-cols-2">
 
 <!-- CARD: FICHA MÉDICA -->
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-between hover:border-borde-focus transition">
 <div>
 <div class="flex items-start justify-between gap-4 mb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-folder-user text-xl"></i>
 </div>
 <div>
 <h3 class="text-lg font-extrabold text-titulo">Resumen de Ficha Médica</h3>
 <p class="text-xs text-apoyo">Antecedentes, alergias y condiciones</p>
 </div>
 </div>
 <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $fichasMedicas->isNotEmpty() ? 'bg-estado-exitoBg text-estado-exito' : 'bg-fondo-panel text-parrafo' }}">
 {{ $fichasMedicas->isNotEmpty() ? 'Registrada' : 'Incompleta' }}
 </span>
 </div>

 @if($fichasMedicas->isNotEmpty())
 @php $latestFicha = $fichasMedicas->first(); @endphp
 <div class="grid grid-cols-2 gap-3 text-sm">
 <div class="rounded-xl bg-fondo-app p-3 border border-borde-suave">
 <span class="block text-[10px] font-bold uppercase tracking-wide text-apoyo mb-1">Alergias</span>
 <span class="text-sm font-bold text-titulo {{ $latestFicha->alergias ? 'text-parrafo' : '' }}">
 {{ $latestFicha->alergias ? 'Sí, registradas' : 'Ninguna reportada' }}
 </span>
 </div>
 <div class="rounded-xl bg-fondo-app p-3 border border-borde-suave">
 <span class="block text-[10px] font-bold uppercase tracking-wide text-apoyo mb-1">Dieta Especial</span>
 <span class="font-bold text-titulo {{ $latestFicha->restricciones_alimentarias ? 'text-estado-advertencia' : '' }}">
 {{ $latestFicha->restricciones_alimentarias ? 'Sí' : 'Normal' }}
 </span>
 </div>
 </div>
 @else
 <p class="text-xs font-bold text-apoyo italic py-2">Falta completar la información clínica base.</p>
 @endif
 </div>
 <div class="mt-5 border-t border-borde-suave pt-4">
 <button type="button" @click="vistaSaludActiva = 'ficha_medica'" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde-focus px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-boton-acento shadow-sm transition hover:bg-estado-peligroBg active:scale-95">
 <i class="ph-bold ph-eye"></i> Ver Resumen Interno
 </button>
 </div>
 </div>

 <!-- CARD: SIGNOS VITALES -->
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-between hover:border-teal-600/40 transition">
 <div>
 <div class="flex items-start justify-between gap-4 mb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100">
 <i class="ph-bold ph-activity text-xl"></i>
 </div>
 <div>
 <h3 class="text-lg font-extrabold text-titulo">Resumen de Signos Vitales</h3>
 <p class="text-xs text-apoyo">Monitoreo de parámetros y medidas</p>
 </div>
 </div>
 </div>
 @if($signosVitales->isNotEmpty())
 @php $latestSigno = $signosVitales->first(); @endphp
 <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
 <div class="bg-blue-50/70 text-blue-900 rounded-xl p-3 border border-blue-100/50 text-center">
 <p class="text-[10px] font-bold uppercase tracking-wide text-blue-700/70 mb-1">P. Arterial</p>
 <p class="text-sm font-bold">{{ $latestSigno->presion_arterial ?: '—' }}</p>
 </div>
 <div class="bg-red-50/70 text-red-950 rounded-xl p-3 border border-red-100/50 text-center">
 <p class="text-[10px] font-bold uppercase tracking-wide text-red-700/75 mb-1">F. Cardaca</p>
 <p class="text-sm font-bold">{{ $latestSigno->frecuencia_cardiaca ?: '—' }}</p>
 </div>
 <div class="bg-orange-50/70 text-orange-950 rounded-xl p-3 border border-orange-100/50 text-center">
 <p class="text-[10px] font-bold uppercase tracking-wide text-orange-800/80 mb-1">Temp.</p>
 <p class="text-sm font-bold">{{ $latestSigno->temperatura ? $latestSigno->temperatura.'°C' : '—' }}</p>
 </div>
 <div class="bg-teal-50/70 text-teal-950 rounded-xl p-3 border border-teal-100/50 text-center">
 <p class="text-[10px] font-bold uppercase tracking-wide text-teal-800/80 mb-1">SpO₂</p>
 <p class="text-sm font-bold">{{ $latestSigno->saturacion ? $latestSigno->saturacion.'%' : '—' }}</p>
 </div>
 </div>
 <p class="text-xs font-bold text-apoyo mt-2 text-right">Último registro: {{ $latestSigno->fecha->format('d/m/Y') }}</p>
 @else
 <p class="text-xs font-bold text-apoyo italic py-2">Sin registros recientes.</p>
 @endif
 </div>
 <div class="mt-5 border-t border-borde-suave pt-4">
 <button type="button" @click="vistaSaludActiva = 'signos_vitales'" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-fondo-card border border-teal-600/30 px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-teal-700 shadow-sm transition hover:bg-teal-50 active:scale-95">
 <i class="ph-bold ph-eye"></i> Ver Resumen Interno
 </button>
 </div>
 </div>

 <!-- CARD: MEDICACIÓN -->
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-between hover:border-estado-exitoBorde transition">
 <div>
 <div class="flex items-start justify-between gap-4 mb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
 <i class="ph-bold ph-pill text-xl"></i>
 </div>
 <div>
 <h3 class="text-lg font-extrabold text-titulo">Resumen de Medicación</h3>
 <p class="text-xs text-apoyo">Tratamiento activo</p>
 </div>
 </div>
 <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-estado-exitoBg text-estado-exito">
 {{ $medicaciones->where('estado', 'ACTIVO')->count() }} Activas
 </span>
 </div>
 @if($medicaciones->where('estado', 'ACTIVO')->count() > 0)
 <div class="flex items-center justify-between rounded-xl bg-fondo-app p-3 border border-borde-suave">
 <div>
 <span class="block text-[10px] font-bold uppercase tracking-wide text-apoyo mb-1">Tomas Recientes</span>
 <span class="text-xs font-bold text-titulo">{{ $administracionesMedicacion->take(5)->where('estado_administracion', 'ADMINISTRADO')->count() }} Completadas</span>
 </div>
 <i class="ph-bold ph-check-square text-xl text-emerald-600/50"></i>
 </div>
 @else
 <p class="text-xs font-bold text-apoyo italic py-2">Sin tratamientos activos.</p>
 @endif
 </div>
 <div class="mt-5 border-t border-borde-suave pt-4 grid grid-cols-2 gap-2">
 <button type="button" @click="vistaSaludActiva = 'medicacion'" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-fondo-card border border-estado-exitoBorde px-2 py-2.5 text-xs font-bold uppercase tracking-wider text-estado-exito shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-pill"></i> Resumen
 </button>
 <button type="button" @click="vistaSaludActiva = 'administracion'" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-fondo-card border border-estado-exitoBorde px-2 py-2.5 text-xs font-bold uppercase tracking-wider text-estado-exito shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-calendar-check"></i> Admin
 </button>
 </div>
 </div>

 <!-- CARD: EVALUACIONES GERIÁTRICAS Y FUNCIONAL -->
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-between hover:border-indigo-400/40 transition">
 <div>
 <div class="flex items-start justify-between gap-4 mb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100">
 <i class="ph-bold ph-wheelchair text-xl"></i>
 </div>
 <div>
 <h3 class="text-lg font-extrabold text-titulo">Resumen Geriátrico</h3>
 <p class="text-xs text-apoyo">Dependencia y funcionalidad</p>
 </div>
 </div>
 </div>
 <div class="grid grid-cols-2 gap-2 text-center">
 <div class="rounded-xl bg-fondo-app p-3 border border-borde-suave">
 <p class="text-[10px] font-bold uppercase tracking-wide text-apoyo mb-1">Autonomía</p>
 <p class="text-xs font-bold text-titulo">{{ $valoracionesFuncionales->first()?->nivel_dependencia ?? 'N/D' }}</p>
 </div>
 <div class="rounded-xl bg-fondo-app p-3 border border-borde-suave">
 <p class="text-[10px] font-bold uppercase tracking-wide text-apoyo mb-1">Riesgo Caídas</p>
 <p class="text-xs font-bold {{ ($valoracionesFuncionales->first()?->riesgo_caida ?? 'N/D') !== 'N/D' ? 'text-amber-600' : 'text-titulo' }}">{{ $valoracionesFuncionales->first()?->riesgo_caida ?? 'N/D' }}</p>
 </div>
 </div>
 </div>
 <div class="mt-5 border-t border-borde-suave pt-4 grid grid-cols-2 gap-2">
 <button type="button" @click="vistaSaludActiva = 'valoracion'" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-fondo-card border border-indigo-300 px-2 py-2.5 text-xs font-bold uppercase tracking-wider text-indigo-700 shadow-sm transition hover:bg-indigo-50 active:scale-95">
 <i class="ph-bold ph-wheelchair"></i> Funcional
 </button>
 <button type="button" @click="vistaSaludActiva = 'evaluaciones'" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-fondo-card border border-indigo-300 px-2 py-2.5 text-xs font-bold uppercase tracking-wider text-indigo-700 shadow-sm transition hover:bg-indigo-50 active:scale-95">
 <i class="ph-bold ph-list-magnifying-glass"></i> Geriátricas
 </button>
 </div>
 </div>

 <!-- CARD: ALERTAS Y REPORTES -->
 <div class="lg:col-span-2 rounded-[24px] border border-borde bg-gradient-to-br from-[#2F3E5C] to-[#455A84] p-6 shadow-sm flex flex-col justify-between text-inverso hover:shadow-md transition">
 <div class="flex items-center gap-4 mb-4">
 <i class="ph-bold ph-warning-circle text-4xl text-inverso/50"></i>
 <div>
 <h3 class="text-lg font-extrabold">Centro Operativo</h3>
 <p class="text-xs text-inverso/70 mt-1">Resumen de alertas, reportes y seguimiento centralizado.</p>
 </div>
 </div>
 <div class="mt-2 grid grid-cols-2 gap-3 w-full">
 <button type="button" @click="vistaSaludActiva = 'alertas'" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card/10 border border-white/20 px-3 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:bg-fondo-card/20">
 <i class="ph-bold ph-bell-ringing"></i> Resumen Alertas
 </button>
 <button type="button" @click="vistaSaludActiva = 'reportes'" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card text-titulo px-3 py-2.5 text-xs font-bold uppercase tracking-wider transition hover:bg-fondo-card/90">
 <i class="ph-bold ph-chart-line-up"></i> Resumen Reportes
 </button>
 </div>
 </div>
 </div>
 </div>

 <!-- CONTENIDO INTERNO: FICHA MÉDICA -->
 <div x-show="vistaSaludActiva === 'ficha_medica'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Ficha Médica</h3>
 @if($fichasMedicas->isNotEmpty())
 @php $ficha = $fichasMedicas->first(); @endphp
 <div class="space-y-4">
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Estado de ficha</span>
 <span class="text-sm font-bold text-emerald-600">Registrada</span>
 <span class="text-xs text-apoyo ml-2">(Actualizado: {{ $ficha->updated_at->format('d/m/Y') }})</span>
 </div>
 <div class="grid sm:grid-cols-2 gap-4 bg-fondo-panel p-4 rounded-xl">
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Alergias Principales</span>
 <p class="text-sm font-bold text-parrafo">{{ $ficha->alergias ?: 'Ninguna registrada' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Condiciones Médicas</span>
 <p class="text-sm font-bold text-titulo">{{ $ficha->enfermedades_preexistentes ?: 'Ninguna registrada' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Restricciones Principales</span>
 <p class="text-sm font-bold text-titulo">{{ $ficha->restricciones_alimentarias ?: 'Ninguna' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Cuidados Especiales</span>
 <p class="text-sm font-bold text-titulo">{{ $ficha->cuidados_especiales ?: 'Ninguno' }}</p>
 </div>
 </div>
 <div class="flex items-center gap-2 pt-4">
 <a href="{{ route('admin.salud-seguimiento.ficha', $adulto->cod_am) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ir a Gestión Completa
 </a>
 </div>
 </div>
 @else
 <div class="text-center py-6">
 <p class="text-sm font-bold text-apoyo">No existe información de salud registrada.</p>
 <a href="{{ route('admin.salud-seguimiento.ficha', $adulto->cod_am) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ir a Gestión Completa
 </a>
 </div>
 @endif
 </div>

 <!-- CONTENIDO INTERNO: SIGNOS VITALES -->
 <div x-show="vistaSaludActiva === 'signos_vitales'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Signos Vitales</h3>
 @if($signosVitales->isNotEmpty())
 <div class="space-y-4">
 <div class="overflow-x-auto">
 <table class="w-full text-left text-sm text-titulo">
 <thead class="bg-fondo-panel text-xs uppercase tracking-wide text-apoyo">
 <tr>
 <th class="px-4 py-2">Fecha/Hora</th>
 <th class="px-4 py-2">P. Arterial</th>
 <th class="px-4 py-2">F. Cardaca</th>
 <th class="px-4 py-2">Temp</th>
 <th class="px-4 py-2">Saturación</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#CBBBAA]/30">
 @foreach($signosVitales->take(4) as $sv)
 <tr>
 <td class="px-4 py-2 text-xs font-bold">{{ $sv->fecha->format('d/m') }} {{ $sv->hora ? substr($sv->hora,0,5) : '' }}</td>
 <td class="px-4 py-2 text-xs">{{ $sv->presion_arterial ?: '-' }}</td>
 <td class="px-4 py-2 text-xs">{{ $sv->frecuencia_cardiaca ?: '-' }}</td>
 <td class="px-4 py-2 text-xs">{{ $sv->temperatura ? $sv->temperatura.'°' : '-' }}</td>
 <td class="px-4 py-2 text-xs">{{ $sv->saturacion ? $sv->saturacion.'%' : '-' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 <div class="flex items-center gap-2 pt-4">
 <a href="{{ route('admin.salud-seguimiento.signos', $adulto->cod_am) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-700 px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-teal-800">
 Ver Gestión Completa
 </a>
 </div>
 </div>
 @else
 <div class="text-center py-6">
 <p class="text-sm font-bold text-apoyo">No existen signos vitales registrados.</p>
 <a href="{{ route('admin.salud-seguimiento.signos', $adulto->cod_am) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-teal-700 px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-teal-800">
 Ver Gestión Completa
 </a>
 </div>
 @endif
 </div>

 <!-- CONTENIDO INTERNO: MEDICACIÓN -->
 <div x-show="vistaSaludActiva === 'medicacion'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Medicación</h3>
 @if($medicaciones->isNotEmpty())
 <div class="space-y-4">
 <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
 <div class="bg-fondo-panel p-3 rounded-xl border border-borde text-center">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Activos</p>
 <p class="text-xl font-extrabold text-estado-exito">{{ $medicaciones->where('estado', 'ACTIVO')->count() }}</p>
 </div>
 <div class="bg-fondo-panel p-3 rounded-xl border border-borde text-center">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Suspendidos</p>
 <p class="text-xl font-extrabold text-apoyo">{{ $medicaciones->where('estado', 'SUSPENDIDO')->count() }}</p>
 </div>
 </div>
 <div class="flex items-center gap-2 pt-4">
 <a href="{{ route('admin.salud-seguimiento.medicacion', $adulto->cod_am) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-fondo-panel px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ver Gestión Completa
 </a>
 </div>
 </div>
 @else
 <div class="text-center py-6">
 <p class="text-sm font-bold text-apoyo">No existe medicación activa.</p>
 <a href="{{ route('admin.salud-seguimiento.medicacion', $adulto->cod_am) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-fondo-panel px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ver Gestión Completa
 </a>
 </div>
 @endif
 </div>

 <!-- CONTENIDO INTERNO: ADMINISTRACIÓN -->
 <div x-show="vistaSaludActiva === 'administracion'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Administración de Medicación</h3>
 @if($administracionesMedicacion->isNotEmpty())
 <div class="space-y-4">
 <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
 <div class="bg-fondo-panel p-3 rounded-xl border border-borde text-center">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Administradas hoy</p>
 <p class="text-xl font-extrabold text-emerald-600">{{ $administracionesMedicacion->where('estado_administracion', 'ADMINISTRADO')->where('fecha', '>=', now()->startOfDay())->count() }}</p>
 </div>
 <div class="bg-fondo-panel p-3 rounded-xl border border-borde text-center">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Pendientes</p>
 <p class="text-xl font-extrabold text-amber-600">{{ $administracionesMedicacion->where('estado_administracion', 'PENDIENTE')->count() }}</p>
 </div>
 <div class="bg-fondo-panel p-3 rounded-xl border border-borde text-center">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Omitidas</p>
 <p class="text-xl font-extrabold text-red-600">{{ $administracionesMedicacion->where('estado_administracion', 'NO_ADMINISTRADO')->count() }}</p>
 </div>
 </div>
 <div class="flex items-center gap-2 pt-4">
 <a href="{{ route('admin.salud-seguimiento.administracion', $adulto->cod_am) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-fondo-panel px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ir a Gestión Completa
 </a>
 </div>
 </div>
 @else
 <div class="text-center py-6">
 <p class="text-sm font-bold text-apoyo">Sin tomas programadas registradas.</p>
 <a href="{{ route('admin.salud-seguimiento.administracion', $adulto->cod_am) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-fondo-panel px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ir a Gestión de Tomas
 </a>
 </div>
 @endif
 </div>

 <!-- CONTENIDO INTERNO: VALORACIÓN FUNCIONAL -->
 <div x-show="vistaSaludActiva === 'valoracion'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Valoración Funcional</h3>
 @if($valoracionesFuncionales->isNotEmpty())
 @php $valFunc = $valoracionesFuncionales->first(); @endphp
 <div class="space-y-4">
 <div class="grid sm:grid-cols-2 gap-4 bg-fondo-panel p-4 rounded-xl border border-borde">
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Nivel de Autonomía</span>
 <p class="text-sm font-bold text-titulo">{{ $valFunc->nivel_dependencia }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Movilidad</span>
 <p class="text-sm font-bold text-titulo">{{ $valFunc->movilidad ?: 'N/D' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Alimentación</span>
 <p class="text-sm font-bold text-titulo">{{ $valFunc->alimentacion ?: 'N/D' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Higiene y Traslado</span>
 <p class="text-sm font-bold text-titulo">{{ $valFunc->higiene_aseo ?: 'N/D' }} / {{ $valFunc->traslado ?: 'N/D' }}</p>
 </div>
 </div>
 <div class="flex items-center gap-2 pt-4">
 <a href="{{ route('admin.salud-seguimiento.valoracion', $adulto->cod_am) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-amber-700">
 Ver Gestión Completa
 </a>
 </div>
 </div>
 @else
 <div class="text-center py-6">
 <p class="text-sm font-bold text-apoyo">No existe valoración funcional registrada.</p>
 <a href="{{ route('admin.salud-seguimiento.valoracion', $adulto->cod_am) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-amber-700">
 Ver Gestión Completa
 </a>
 </div>
 @endif
 </div>

 <!-- CONTENIDO INTERNO: EVALUACIONES GERIÁTRICAS -->
 <div x-show="vistaSaludActiva === 'evaluaciones'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Evaluaciones Geriátricas</h3>
 @if($evaluacionesGeriatricasActivas->isNotEmpty())
 @php $latestGer = $evaluacionesGeriatricasActivas->first(); @endphp
 <div class="space-y-4">
 <div class="bg-fondo-panel p-4 rounded-xl border border-borde text-sm">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Última Evaluación Registrada</span>
 <p class="font-bold text-titulo mb-2">{{ $latestGer->instrumento->nombre }} <span class="text-xs text-apoyo font-normal">({{ $latestGer->fecha_eval->format('d/m/Y') }})</span></p>
 <div class="grid grid-cols-2 gap-4 mt-3">
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Alerta / Riesgo</span>
 <p class="font-bold {{ $latestGer->nivel_alerta === 'NORMAL' ? 'text-emerald-600' : 'text-red-600' }}">{{ $latestGer->nivel_alerta }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo">Puntaje Obtenido</span>
 <p class="font-bold text-titulo">{{ $latestGer->puntaje_total }} pts</p>
 </div>
 </div>
 </div>
 <div class="flex items-center gap-2 pt-4">
 <a href="{{ route('admin.salud-seguimiento.resumen', $adulto->cod_am) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ver Gestión Completa
 </a>
 </div>
 </div>
 @else
 <div class="text-center py-6">
 <p class="text-sm font-bold text-apoyo">No existen evaluaciones geriátricas registradas.</p>
 <a href="{{ route('admin.salud-seguimiento.resumen', $adulto->cod_am) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ver Gestión Completa
 </a>
 </div>
 @endif
 </div>

 <!-- CONTENIDO INTERNO: ALERTAS -->
 <div x-show="vistaSaludActiva === 'alertas'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Alertas de Salud</h3>
 <!-- Actualmente las alertas en dashboard de salud son calculadas desde SaludAlertasPanel. Las mostramos genéricamente -->
 <div class="text-center py-6 bg-fondo-panel rounded-xl">
 <p class="text-sm font-bold text-apoyo">Consulte el panel central para ver las alertas activas procesadas.</p>
 <a href="{{ route('admin.salud-seguimiento.alertas', $adulto->cod_am) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel">
 Ir a Panel de Alertas
 </a>
 </div>
 </div>

 <!-- CONTENIDO INTERNO: REPORTES -->
 <div x-show="vistaSaludActiva === 'reportes'" style="display: none;" x-transition.opacity class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo mb-4 border-b border-borde pb-3">Resumen de Reportes</h3>
 <div class="grid sm:grid-cols-2 gap-4">
 <div class="bg-fondo-panel p-4 rounded-xl border border-borde">
 <i class="ph-bold ph-file-pdf text-2xl text-parrafo mb-2"></i>
 <p class="text-sm font-bold text-titulo">Reporte Consolidado Médico</p>
 <p class="text-xs text-apoyo mb-3">Historial clínico y últimas mediciones.</p>
 <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $adulto->cod_am, 'format' => 'pdf']) }}" class="text-xs font-bold text-parrafo hover:underline">Descargar PDF</a>
 </div>
 <div class="bg-fondo-panel p-4 rounded-xl border border-borde">
 <i class="ph-bold ph-chart-line-up text-2xl text-titulo mb-2"></i>
 <p class="text-sm font-bold text-titulo">Centro de Analítica</p>
 <p class="text-xs text-apoyo mb-3">Generador de reportes especializados.</p>
 <a href="{{ route('admin.salud-seguimiento.reportes', $adulto->cod_am) }}" class="text-xs font-bold text-titulo hover:underline">Ir a Reportes Avanzados</a>
 </div>
 </div>
 </div>

</section>