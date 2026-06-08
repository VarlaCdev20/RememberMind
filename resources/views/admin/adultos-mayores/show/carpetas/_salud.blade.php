<div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <div class="mb-5 flex items-center justify-between border-b border-borde pb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-heartbeat text-xl"></i>
 </div>
 <div>
 <h2 class="text-lg font-extrabold text-titulo">Salud y Cuidados Resumida</h2>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Resumen médico institucional</p>
 </div>
 </div>
 </div>

 @if($fichasMedicas->isNotEmpty() || $signosVitales->count() > 0 || $medicaciones->count() > 0 || $valoracionesFuncionales->count() > 0)
 <div class="space-y-6">
 
 {{-- Ficha Médica --}}
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-3">Ficha Médica</span>
 @if($fichasMedicas->isNotEmpty())
 @php $ficha = $fichasMedicas->first(); @endphp
 <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
 <div class="rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Última Act.</span>
 <p class="text-xs font-bold text-titulo">{{ $ficha->updated_at->format('d/m/Y') }}</p>
 </div>
 <div class="rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="block text-xs font-bold uppercase tracking-wide text-red-600/70 mb-1">Alergias</span>
 <p class="text-xs font-bold text-titulo truncate">{{ $ficha->alergias ?: 'Ninguna' }}</p>
 </div>
 <div class="rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Enfermedades</span>
 <p class="text-xs font-bold text-titulo truncate">{{ $ficha->enfermedades_preexistentes ?: 'Ninguna' }}</p>
 </div>
 </div>
 @else
 <p class="text-xs font-bold text-apoyo">No hay ficha médica base registrada.</p>
 @endif
 </div>

 {{-- Signos Vitales --}}
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-3">Último Control de Signos Vitales</span>
 @if($signosVitales->count() > 0)
 @php $ultimoSigno = $signosVitales->first(); @endphp
 <div class="flex flex-wrap items-center gap-4 rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <div>
 <span class="block text-xs font-bold text-apoyo uppercase">Fecha</span>
 <p class="text-sm font-bold text-titulo">{{ \Carbon\Carbon::parse($ultimoSigno->fecha)->format('d/m/Y') }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold text-apoyo uppercase">PA</span>
 <p class="text-sm font-bold text-titulo">{{ $ultimoSigno->presion_arterial ?? '--' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold text-apoyo uppercase">FC</span>
 <p class="text-sm font-bold text-titulo">{{ $ultimoSigno->frecuencia_cardiaca ?? '--' }} <span class="text-xs font-normal">bpm</span></p>
 </div>
 <div>
 <span class="block text-xs font-bold text-apoyo uppercase">FR</span>
 <p class="text-sm font-bold text-titulo">{{ $ultimoSigno->frecuencia_respiratoria ?? '--' }} <span class="text-xs font-normal">rpm</span></p>
 </div>
 <div>
 <span class="block text-xs font-bold text-apoyo uppercase">Temp</span>
 <p class="text-sm font-bold text-titulo">{{ $ultimoSigno->temperatura ?? '--' }} <span class="text-xs font-normal">°C</span></p>
 </div>
 <div>
 <span class="block text-xs font-bold text-apoyo uppercase">SpO2</span>
 <p class="text-sm font-bold text-titulo">{{ $ultimoSigno->saturacion_oxigeno ?? '--' }} <span class="text-xs font-normal">%</span></p>
 </div>
 </div>
 @else
 <p class="text-xs font-bold text-apoyo">No hay controles de signos vitales recientes.</p>
 @endif
 </div>

 {{-- Medicación y Valoración --}}
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-3">Medicación Activa</span>
 @if($medicaciones->count() > 0)
 <div class="rounded-xl border border-borde-suave bg-fondo-card p-4">
 <p class="text-2xl font-black text-parrafo">{{ $medicaciones->count() }} <span class="text-xs font-bold text-apoyo">prescripciones</span></p>
 <p class="text-xs font-bold text-apoyo mt-1">Suministro activo controlado.</p>
 </div>
 @else
 <p class="text-xs font-bold text-apoyo">Sin medicación activa.</p>
 @endif
 </div>

 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-3">Valoración Funcional (Barthel)</span>
 @if($valoracionesFuncionales->count() > 0)
 @php $ultimaVal = $valoracionesFuncionales->first(); @endphp
 <div class="rounded-xl border border-borde-suave bg-fondo-card p-4">
 <p class="text-lg font-extrabold text-amber-600">{{ $ultimaVal->resultado_dependencia ?? 'N/D' }}</p>
 <p class="text-xs font-bold text-apoyo mt-1">Puntaje: {{ $ultimaVal->puntaje_total ?? '--' }}/100</p>
 </div>
 @else
 <p class="text-xs font-bold text-apoyo">Sin valoración funcional.</p>
 @endif
 </div>
 </div>

 </div>
 @else
 <div class="py-10 text-center">
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-fondo-panel text-apoyo mb-4">
 <i class="ph-bold ph-heartbeat text-3xl"></i>
 </div>
 <p class="text-sm font-bold text-apoyo">No existe información de salud registrada.</p>
 <p class="mt-2 text-xs font-bold text-apoyo uppercase tracking-wide">Gestione desde el módulo correspondiente.</p>
 </div>
 @endif
</div>
