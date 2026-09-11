@if($drawerGrafico && $adultoDrawer)
@php
    $ultSigno = $signosDrawer->first();
    $historialSignos = $signosDrawer->take(8);
@endphp
<div class="fixed inset-0 z-50 overflow-hidden font-sans"
     x-data
     x-on:keydown.escape.window="$wire.cerrarDrawer()">
    <!-- Backdrop oscuro con blur suave -->
    <div class="fixed inset-0 bg-[var(--color-modal-overlay)] backdrop-blur-sm transition-opacity"
         wire:click="cerrarDrawer"></div>

    <!-- Contenedor Deslizante Lateral (Barra Derecha) -->
    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="rm-drawer pointer-events-auto flex h-full w-screen max-w-2xl transform flex-col overflow-hidden transition duration-300 ease-in-out">
            
            <!-- Encabezado del Drawer con color institucional -->
            <div class="flex items-center justify-between border-b border-borde bg-fondo-hover px-6 py-4 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-600 text-white shadow-md shadow-purple-600/30">
                        <i class="ph-bold ph-chart-line-up text-xl"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-titulo">Gráficos de Evolución Clínica</h3>
                        <p class="text-xs text-apoyo">Tendencias de signos vitales registradas</p>
                    </div>
                </div>
                <button type="button"
                    wire:click="cerrarDrawer"
                    class="rm-btn-icon h-8 w-8 rounded-lg" aria-label="Cerrar panel">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Cuerpo del Drawer con Scroll y Fondo enriquecido -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-thin">
                
                <!-- 1. Tarjeta del residente y ubicación -->
                <div class="flex items-center justify-between p-4 rounded-2xl bg-white/90 dark:bg-slate-800/80 border border-purple-200/70 dark:border-purple-900/50 shadow-sm backdrop-blur-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 font-extrabold text-base shadow-sm">
                            {{ substr($adultoDrawer->nombres ?? 'A', 0, 1) }}{{ substr($adultoDrawer->ap_paterno ?? 'M', 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 dark:text-white">
                                {{ $adultoDrawer->nombres }} {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }}
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                Código: <span class="font-bold">{{ $adultoDrawer->cod_am }}</span>
                                @if($adultoDrawer->fecha_nacimiento)
                                    ? {{ $adultoDrawer->fecha_nacimiento->age }} años
                                @elseif($adultoDrawer->edad)
                                    ? {{ $adultoDrawer->edad }} años
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-xs font-bold shadow-sm">
                            <i class="ph-bold ph-door text-sm"></i>
                            <span>{{ $adultoDrawer->habitacion?->nombre ?? ($adultoDrawer->habitacion?->codigo ?? 'Sin hab.') }}</span>
                            <span>?</span>
                            <i class="ph-bold ph-bed text-sm"></i>
                            <span>{{ $adultoDrawer->cama?->codigo ?? 'Sin cama' }}</span>
                        </div>
                        @if($adultoDrawer->habitacion?->ubicacion)
                            <p class="text-[11px] text-slate-400 mt-1">{{ $adultoDrawer->habitacion->ubicacion }}</p>
                        @endif
                    </div>
                </div>

                <!-- 2. Tarjetas KPI con fondos diferenciados por par?metro -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Último Control Asistencial ({{ $ultSigno?->fecha ? \Carbon\Carbon::parse($ultSigno->fecha)->format('d/m/Y') : 'Hoy' }} {{ $ultSigno?->hora ?? '' }})
                        </span>
                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Monitoreo Activo
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <!-- Presi?n Arterial -->
                        <div class="p-3.5 rounded-2xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-900/50 shadow-sm">
                            <div class="flex items-center justify-between text-rose-500 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Presión Art.</span>
                                <i class="ph-bold ph-heartbeat text-rose-600 text-base"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800 dark:text-white font-mono">
                                {{ $ultSigno?->presion_arterial ?? ($ultSigno?->presion_sistolica ? $ultSigno->presion_sistolica.'/'.$ultSigno->presion_diastolica : '120/80') }}
                            </span>
                            <span class="block text-[10px] text-slate-400 font-medium">mmHg</span>
                        </div>

                        <!-- Frecuencia Cardíaca -->
                        <div class="p-3.5 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/50 shadow-sm">
                            <div class="flex items-center justify-between text-blue-500 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Pulso / FC</span>
                                <i class="ph-bold ph-activity text-blue-600 text-base"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800 dark:text-white font-mono">
                                {{ $ultSigno?->frecuencia_cardiaca ?? '72' }}
                            </span>
                            <span class="block text-[10px] text-slate-400 font-medium">lpm</span>
                        </div>

                        <!-- Saturación O2 -->
                        <div class="p-3.5 rounded-2xl bg-cyan-50/80 dark:bg-cyan-950/40 border border-cyan-200/80 dark:border-cyan-900/50 shadow-sm">
                            <div class="flex items-center justify-between text-cyan-500 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">SpO2</span>
                                <i class="ph-bold ph-drop text-cyan-600 text-base"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800 dark:text-white font-mono">
                                {{ $ultSigno?->saturacion ?? '97' }}%
                            </span>
                            <span class="block text-[10px] text-slate-400 font-medium">oxígeno</span>
                        </div>

                        <!-- Temperatura -->
                        <div class="p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/50 shadow-sm">
                            <div class="flex items-center justify-between text-amber-500 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Temperatura</span>
                                <i class="ph-bold ph-thermometer text-amber-600 text-base"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800 dark:text-white font-mono">
                                {{ $ultSigno?->temperatura ?? '36.5' }}?C
                            </span>
                            <span class="block text-[10px] text-slate-400 font-medium">axilar</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Curva Gráfica Visual de Evolución Clínica (SVG Vectorial) -->
                <div class="p-5 rounded-2xl bg-white/95 dark:bg-slate-800/80 border border-purple-200/70 dark:border-purple-900/50 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-chart-line text-purple-600 text-sm"></i>
                                Curva de Presión Arterial y Tendencias
                            </h4>
                            <p class="text-[11px] text-slate-400">Evolución cronológica de los últimos registros clínicos</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-bold">
                            <span class="flex items-center gap-1 text-rose-600 dark:text-rose-400 text-[11px]">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Sistólica
                            </span>
                            <span class="flex items-center gap-1 text-blue-600 dark:text-blue-400 text-[11px]">
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> Diastólica
                            </span>
                        </div>
                    </div>

                    @php
                        $signosOrdenados = $signosDrawer->sortBy('fecha')->values();
                        $countPuntos = $signosOrdenados->count();
                    @endphp

                    @if($countPuntos > 0)
                        <div class="relative w-full h-48 bg-slate-50 dark:bg-slate-900 rounded-xl p-3 border border-slate-200/70 dark:border-slate-700/80">
                            <svg class="w-full h-full overflow-visible" viewBox="0 0 500 140" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="gradSistolicaDrawer" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#E11D48" stop-opacity="0.3"/>
                                        <stop offset="100%" stop-color="#E11D48" stop-opacity="0.0"/>
                                    </linearGradient>
                                    <linearGradient id="gradDiastolicaDrawer" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#2563EB" stop-opacity="0.2"/>
                                        <stop offset="100%" stop-color="#2563EB" stop-opacity="0.0"/>
                                    </linearGradient>
                                </defs>

                                <line x1="0" y1="20" x2="500" y2="20" stroke="currentColor" class="text-slate-200 dark:text-slate-800" stroke-dasharray="4"/>
                                <line x1="0" y1="60" x2="500" y2="60" stroke="currentColor" class="text-slate-200 dark:text-slate-800" stroke-dasharray="4"/>
                                <line x1="0" y1="100" x2="500" y2="100" stroke="currentColor" class="text-slate-200 dark:text-slate-800" stroke-dasharray="4"/>

                                @php
                                    $minVal = 50;
                                    $maxVal = 190;
                                    $rango = $maxVal - $minVal;
                                    $stepX = $countPuntos > 1 ? 460 / ($countPuntos - 1) : 230;

                                    $puntosSis = [];
                                    $puntosDia = [];
                                    foreach($signosOrdenados as $i => $s) {
                                        $x = 20 + ($i * $stepX);
                                        $valSis = max(50, min(190, (float)($s->presion_sistolica ?? 120)));
                                        $valDia = max(50, min(190, (float)($s->presion_diastolica ?? 80)));
                                        $ySis = 120 - (($valSis - $minVal) / $rango * 100);
                                        $yDia = 120 - (($valDia - $minVal) / $rango * 100);
                                        $puntosSis[] = ['x' => $x, 'y' => $ySis, 'val' => $valSis, 'fecha' => $s->fecha ? \Carbon\Carbon::parse($s->fecha)->format('d/m') : ''];
                                        $puntosDia[] = ['x' => $x, 'y' => $yDia, 'val' => $valDia];
                                    }

                                    $pathSis = '';
                                    $pathDia = '';
                                    foreach($puntosSis as $idx => $pt) {
                                        $pathSis .= ($idx === 0 ? 'M' : 'L') . " {$pt['x']} {$pt['y']} ";
                                    }
                                    foreach($puntosDia as $idx => $pt) {
                                        $pathDia .= ($idx === 0 ? 'M' : 'L') . " {$pt['x']} {$pt['y']} ";
                                    }
                                @endphp

                                @if(count($puntosSis) > 1)
                                    <path d="{{ $pathSis }} L {{ end($puntosSis)['x'] }} 130 L {{ $puntosSis[0]['x'] }} 130 Z" fill="url(#gradSistolicaDrawer)" />
                                    <path d="{{ $pathDia }} L {{ end($puntosDia)['x'] }} 130 L {{ $puntosDia[0]['x'] }} 130 Z" fill="url(#gradDiastolicaDrawer)" />
                                @endif

                                <path d="{{ $pathSis }}" fill="none" stroke="#E11D48" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="{{ $pathDia }}" fill="none" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

                                @foreach($puntosSis as $idx => $pt)
                                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#E11D48" stroke="#FFFFFF" stroke-width="2"/>
                                    <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 8 }}" font-size="9" font-weight="bold" fill="#E11D48" text-anchor="middle">{{ round($pt['val']) }}</text>
                                @endforeach

                                @foreach($puntosDia as $idx => $pt)
                                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#2563EB" stroke="#FFFFFF" stroke-width="2"/>
                                    <text x="{{ $pt['x'] }}" y="{{ $pt['y'] + 12 }}" font-size="9" font-weight="bold" fill="#2563EB" text-anchor="middle">{{ round($pt['val']) }}</text>
                                @endforeach
                            </svg>
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-slate-400">
                            No se registran suficientes puntos de signos vitales para trazar la curva.
                        </div>
                    @endif
                </div>

                <!-- 4. Tabla Unificada de Historial Clínico -->
                <div class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                        Registros Históricos Recientes
                    </span>

                    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 overflow-hidden bg-white/95 dark:bg-slate-900 shadow-sm">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-700 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th class="py-2.5 px-3">Fecha y Hora</th>
                                    <th class="py-2.5 px-3">P. Arterial</th>
                                    <th class="py-2.5 px-3">Pulso</th>
                                    <th class="py-2.5 px-3">SpO2</th>
                                    <th class="py-2.5 px-3">Temp.</th>
                                    <th class="py-2.5 px-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                @forelse($historialSignos as $s)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-2 px-3 font-bold text-slate-800 dark:text-white">
                                            {{ $s->fecha ? \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') : '-' }} <span class="text-[10px] text-slate-400">{{ $s->hora }}</span>
                                        </td>
                                        <td class="py-2 px-3 font-mono font-bold text-rose-600 dark:text-rose-400">
                                            {{ $s->presion_arterial ?? ($s->presion_sistolica ? $s->presion_sistolica.'/'.$s->presion_diastolica : '-') }}
                                        </td>
                                        <td class="py-2 px-3 font-mono">
                                            {{ $s->frecuencia_cardiaca ? $s->frecuencia_cardiaca.' lpm' : '-' }}
                                        </td>
                                        <td class="py-2 px-3 font-mono font-bold text-cyan-600 dark:text-cyan-400">
                                            {{ $s->saturacion ? $s->saturacion.'%' : '-' }}
                                        </td>
                                        <td class="py-2 px-3 font-mono">
                                            {{ $s->temperatura ? $s->temperatura.'?C' : '-' }}
                                        </td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                Registrado
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-400">
                                            No hay registros de signos vitales disponibles.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pie del Drawer -->
            <div class="px-6 py-4 border-t border-purple-200/70 dark:border-purple-900/50 bg-gradient-to-r from-purple-50/80 via-white/90 dark:via-slate-900/90 to-indigo-50/80 flex items-center justify-between">
                <button type="button"
                    wire:click="verUbicacion('{{ $adultoDrawer->cod_am }}')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-blue-700 hover:text-blue-800 bg-blue-100/80 hover:bg-blue-200 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 border border-blue-300 dark:border-blue-800 transition cursor-pointer shadow-sm active:scale-95">
                    <i class="ph-bold ph-bed text-sm"></i>
                    <span>Ver Ubicación y Habitación</span>
                </button>

                <button type="button"
                    wire:click="cerrarDrawer"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer shadow-sm active:scale-95">
                    Cerrar Panel
                </button>
            </div>
        </div>
    </div>
</div>
@endif
