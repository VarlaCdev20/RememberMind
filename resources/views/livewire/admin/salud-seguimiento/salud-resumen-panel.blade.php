<div class="salud-resumen-scope space-y-6 animate-[fadeIn_0.3s_ease-out]">
    <style>
        .salud-resumen-scope .bg-white {
            background-color: rgba(243, 236, 228, 0.78) !important;
        }
        .salud-resumen-scope .shadow-sm {
            box-shadow: 0 12px 32px rgba(47, 62, 92, 0.08) !important;
        }
        .salud-resumen-scope .border-\[\#C7B5A3\]\/40,
        .salud-resumen-scope .border-\[\#C7B5A3\]\/30 {
            border-color: rgba(199, 181, 163, 0.62) !important;
        }
        .salud-resumen-scope .rounded-2xl {
            border-radius: 1.35rem !important;
        }
        .salud-resumen-scope a.bg-white,
        .salud-resumen-scope .bg-\[\#F7F5F2\] {
            background-color: rgba(230, 221, 211, 0.58) !important;
        }
    </style>
    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="p-5">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">Resumen individual</span>
            <h2 class="mt-1 text-xl font-black tracking-tight text-[#2F3E5C]">Panel clínico-asistencial</h2>
            <p class="mt-1 max-w-2xl text-xs font-bold leading-relaxed text-[#2F3E5C]/62">
                Vista consolidada del estado de salud, alertas, acciones rápidas y últimos registros del adulto mayor.
            </p>
        </div>
    </section>
        <div class="grid gap-6 lg:grid-cols-12">

            {{-- ═══ COLUMNA IZQUIERDA (4 cols) ═══════════════════════════ --}}
            <div class="lg:col-span-4 space-y-5">

                {{-- Tarjeta de perfil --}}
                <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm">
                    <div class="h-20 w-full bg-gradient-to-br from-terracota/80 to-azul-profundo/80 relative">
                        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                    </div>
                    <div class="relative px-5 pb-5 pt-10 flex flex-col items-center">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2">
                            <div class="h-20 w-20 overflow-hidden rounded-full border-4 border-white bg-white shadow-md">
                                @if($adulto->foto)
                                    <img src="{{ Storage::url($adulto->foto) }}"
                                         alt="{{ $adulto->nombres }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#E6DDD3] to-[#C7B5A3]">
                                        <span class="text-2xl font-black text-azul-profundo/40">
                                            {{ mb_strtoupper(mb_substr($adulto->nombres, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h2 class="text-base font-black text-azul-profundo text-center leading-tight">
                            {{ trim($adulto->nombres . ' ' . $adulto->ap_paterno . ' ' . ($adulto->ap_materno ?? '')) }}
                        </h2>

                        <p class="text-xs font-bold text-terracota mt-1">
                            @if($adulto->fecha_nac)
                                {{ \Carbon\Carbon::parse($adulto->fecha_nac)->age }} años
                                <span class="text-azul-profundo/40 mx-1">•</span>
                            @endif
                            {{ $adulto->genero ?? '—' }}
                        </p>

                        <div class="mt-4 w-full space-y-2 text-xs">
                            <div class="flex justify-between border-b border-[#E6DDD3] pb-2">
                                <span class="font-bold text-azul-profundo/50 uppercase tracking-wide text-[10px]">Código</span>
                                <span class="font-black text-azul-profundo">{{ $adulto->cod_am }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#E6DDD3] pb-2">
                                <span class="font-bold text-azul-profundo/50 uppercase tracking-wide text-[10px]">CI</span>
                                <span class="font-bold text-azul-profundo">
                                    {{ $adulto->ci ?? '—' }}
                                    {{ $adulto->complemento_ci ? '-' . $adulto->complemento_ci : '' }}
                                    {{ $adulto->expedicion_ci ? ' (' . $adulto->expedicion_ci . ')' : '' }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-[#E6DDD3] pb-2">
                                <span class="font-bold text-azul-profundo/50 uppercase tracking-wide text-[10px]">Estado</span>
                                <span class="font-black text-azul-profundo">{{ $estadoTexto }}</span>
                            </div>
                            @if($adulto->fecha_ing)
                            <div class="flex justify-between">
                                <span class="font-bold text-azul-profundo/50 uppercase tracking-wide text-[10px]">Ingreso</span>
                                <span class="font-bold text-azul-profundo">
                                    {{ \Carbon\Carbon::parse($adulto->fecha_ing)->format('d/m/Y') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Alertas orientativas --}}
                <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4 border-b border-[#C7B5A3]/20 pb-3">
                        <i class="ph-bold ph-warning-circle text-amber-500 text-base"></i>
                        <h3 class="text-xs font-black uppercase tracking-widest text-azul-profundo">
                            Alertas de Seguimiento
                        </h3>
                        @if(count($alertas) > 0)
                            <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black text-amber-700">
                                {{ count($alertas) }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @forelse($alertas as $alerta)
                            @php
                                $esReq = $alerta['nivel'] === 'requiere_revision';
                                $esAt  = $alerta['nivel'] === 'atencion';
                                $bg    = $esReq ? 'bg-orange-50 border-orange-200' : ($esAt ? 'bg-amber-50 border-amber-200' : 'bg-blue-50 border-blue-200');
                                $icon  = $esReq ? 'ph-warning-octagon text-orange-500' : ($esAt ? 'ph-warning text-amber-500' : 'ph-info text-blue-500');
                                $text  = $esReq ? 'text-orange-700' : ($esAt ? 'text-amber-700' : 'text-blue-700');
                            @endphp
                            <div class="rounded-xl p-3 border {{ $bg }}">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <i class="ph-fill {{ $icon }} text-sm"></i>
                                    <span class="text-[10px] font-black uppercase tracking-wider {{ $text }}">
                                        {{ $alerta['tipo'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] font-bold text-azul-profundo/80 leading-relaxed">
                                    {{ $alerta['mensaje'] }}
                                </p>
                            </div>
                        @empty
                            <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3 text-center">
                                <i class="ph-bold ph-check-circle text-xl text-emerald-500 mb-1 block"></i>
                                <p class="text-xs font-black text-emerald-700">Sin alertas de seguimiento activas</p>
                            </div>
                        @endforelse

                        <p class="text-[9px] text-azul-profundo/40 italic text-center mt-1 leading-relaxed">
                            Estos avisos son orientativos y no constituyen diagnóstico médico.
                        </p>
                    </div>
                </div>

                {{-- Acciones rápidas --}}
                <div class="rounded-2xl border border-[#C7B5A3]/40 bg-[#F7F5F2] p-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-azul-profundo mb-3 border-b border-[#C7B5A3]/20 pb-3">
                        Acciones Rápidas
                    </h3>

                    <div class="space-y-1.5">
                        @can('salud.ficha.ver')
                        <a href="{{ route('admin.salud-seguimiento.ficha', $adulto) }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-file-text text-terracota text-sm"></i>
                                Ficha Médica
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                        @endcan

                        @can('salud.medicacion.ver')
                        <a href="{{ route('admin.salud-seguimiento.medicacion', $adulto) }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-pill text-terracota text-sm"></i>
                                Medicación
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                        @endcan

                        @can('salud.signos.ver')
                        <a href="{{ route('admin.salud-seguimiento.signos', $adulto) }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-activity text-terracota text-sm"></i>
                                Signos Vitales
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                        @endcan

                        @can('salud.valoracion.ver')
                        <a href="{{ route('admin.salud-seguimiento.valoracion', $adulto) }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-person-simple-walk text-terracota text-sm"></i>
                                Valoración Funcional
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                        @endcan

                        <a href="{{ route('admin.salud-seguimiento.alertas') }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-amber-600 hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-bell text-amber-500 text-sm"></i>
                                Ver Alertas
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>

                        @can('reportes.ver')
                        <a href="{{ route('admin.salud-seguimiento.reportes') }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-verde-salud hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-chart-bar text-verde-salud text-sm"></i>
                                Reporte de Salud
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                        @endcan
                    </div>

                    {{-- Historial complementario --}}
                    <div class="mt-4 pt-3 border-t border-[#C7B5A3]/20 space-y-1.5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/40 mb-2">
                            Historial Complementario
                        </p>
                        <a href="{{ route('admin.adultos-mayores.observaciones.index', $adulto) }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-eye text-terracota text-sm"></i>
                                Observaciones
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                        <a href="{{ route('admin.adultos-mayores.atenciones.index', $adulto) }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-stethoscope text-terracota text-sm"></i>
                                Atenciones
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                        <a href="{{ route('admin.adultos-mayores.evaluaciones.index', $adulto) }}"
                           class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2">
                                <i class="ph-bold ph-brain text-terracota text-sm"></i>
                                Evaluaciones Cognitivas
                            </span>
                            <i class="ph-bold ph-caret-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

            </div>
            {{-- /COLUMNA IZQUIERDA --}}

            {{-- ═══ COLUMNA DERECHA (8 cols) ══════════════════════════════ --}}
            <div class="lg:col-span-8 space-y-5">

                {{-- 1. Ficha Médica Base --}}
                <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden">
                    <div class="bg-azul-profundo px-5 py-3.5 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-wider text-white flex items-center gap-2">
                            <i class="ph-bold ph-file-text text-terracota text-base"></i>
                            Ficha Médica Base
                        </h3>
                        @can('salud.ficha.ver')
                        <a href="{{ route('admin.salud-seguimiento.ficha', $adulto) }}"
                           class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-black text-white hover:bg-white/20 transition-colors">
                            Gestionar Ficha
                        </a>
                        @endcan
                    </div>
                    <div class="p-5">
                        @if($fichaMedica)
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-2">
                                        Condiciones Registradas
                                    </p>
                                    @php
                                        $condiciones = array_filter([
                                            'Hipertensión'     => $fichaMedica->hipertension,
                                            'Diabetes'         => $fichaMedica->diabetes,
                                            'Prob. Cardíacos'  => $fichaMedica->problemas_cardiacos,
                                            'ACV'              => $fichaMedica->acv,
                                            'Parkinson'        => $fichaMedica->parkinson,
                                            'Epilepsia'        => $fichaMedica->epilepsia,
                                            'Alzheimer'        => $fichaMedica->alzheimer_diagnosticado,
                                            'Depresión'        => $fichaMedica->depresion,
                                            'Ansiedad'         => $fichaMedica->ansiedad,
                                            'Prob. de Sueño'   => $fichaMedica->problemas_sueno,
                                            'Prob. Visuales'   => $fichaMedica->problemas_visuales,
                                            'Prob. Auditivos'  => $fichaMedica->problemas_auditivos,
                                            'Dolor Crónico'    => $fichaMedica->dolor_cronico,
                                        ]);
                                    @endphp
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse(array_keys($condiciones) as $nombre)
                                            <span class="rounded-md bg-rose-50 border border-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">
                                                {{ $nombre }}
                                            </span>
                                        @empty
                                            <span class="text-xs font-bold text-azul-profundo/40 italic">
                                                Ninguna registrada en sistema
                                            </span>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-1">Alergias</p>
                                        <p class="text-xs font-bold text-azul-profundo bg-[#F7F5F2] px-3 py-2 rounded-lg border border-[#C7B5A3]/30">
                                            {{ $fichaMedica->alergias ?: 'Ninguna registrada' }}
                                        </p>
                                    </div>
                                    @if($fichaMedica->observacion_medica)
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-1">Observación Médica</p>
                                        <p class="text-xs font-bold text-azul-profundo bg-[#F7F5F2] px-3 py-2 rounded-lg border border-[#C7B5A3]/30">
                                            {{ \Illuminate\Support\Str::limit($fichaMedica->observacion_medica, 120) }}
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="py-6 text-center">
                                <i class="ph-fill ph-file-dashed text-3xl text-[#C7B5A3]/60 mb-2 block"></i>
                                <p class="text-sm font-black text-azul-profundo/50">Sin ficha médica activa registrada</p>
                                @can('salud.ficha.crear')
                                <a href="{{ route('admin.salud-seguimiento.ficha', $adulto) }}"
                                   class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-azul-profundo px-3 py-1.5 text-xs font-black text-white hover:opacity-90 transition-opacity">
                                    <i class="ph-bold ph-plus"></i> Registrar Ficha Médica
                                </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 2. Tratamiento Farmacológico --}}
                <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden">
                    <div class="bg-azul-profundo px-5 py-3.5 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-wider text-white flex items-center gap-2">
                            <i class="ph-bold ph-pill text-terracota text-base"></i>
                            Tratamiento Farmacológico
                            @if($medicacionActiva->isNotEmpty())
                                <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-black">
                                    {{ $medicacionActiva->count() }} activa{{ $medicacionActiva->count() !== 1 ? 's' : '' }}
                                </span>
                            @endif
                        </h3>
                        <div class="flex gap-2">
                            @can('salud.administracion.ver')
                            <a href="{{ route('admin.salud-seguimiento.administracion', $adulto) }}"
                               class="rounded-lg bg-terracota px-3 py-1.5 text-[10px] uppercase font-black text-white hover:opacity-90 transition-opacity">
                                Registrar Toma
                            </a>
                            @endcan
                            @can('salud.medicacion.ver')
                            <a href="{{ route('admin.salud-seguimiento.medicacion', $adulto) }}"
                               class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-black text-white hover:bg-white/20 transition-colors">
                                Ver Medicación
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div>
                        <table class="w-full text-left text-xs text-azul-profundo">
                            <thead class="bg-[#F7F5F2] text-[10px] font-black uppercase text-azul-profundo/60">
                                <tr>
                                    <th class="px-5 py-3">Medicamento</th>
                                    <th class="px-5 py-3">Dosis · Frecuencia</th>
                                    <th class="px-5 py-3">Vía</th>
                                    <th class="px-5 py-3 text-right">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/20">
                                @forelse($medicacionActiva->take(5) as $med)
                                    <tr class="hover:bg-[#F7F5F2]/50 transition-colors">
                                        <td class="px-5 py-3 font-bold">{{ $med->nombre_medicamento }}</td>
                                        <td class="px-5 py-3 text-azul-profundo/70">
                                            {{ $med->dosis ?? '—' }}
                                            @if($med->frecuencia)
                                                <span class="text-azul-profundo/40">·</span>
                                                {{ $med->frecuencia }}
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-azul-profundo/70">{{ $med->via_administracion ?? '—' }}</td>
                                        <td class="px-5 py-3 text-right">
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-700">
                                                Activo
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-6 text-center text-xs font-bold text-azul-profundo/40 italic">
                                            Sin tratamientos farmacológicos activos registrados
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($ultimaAdministracion)
                        <div class="px-5 py-2.5 bg-[#F7F5F2] border-t border-[#C7B5A3]/20 flex items-center gap-2">
                            <i class="ph-bold ph-clock text-azul-profundo/40 text-xs"></i>
                            <p class="text-[11px] font-bold text-azul-profundo/60">
                                Última administración:
                                {{ \Carbon\Carbon::parse($ultimaAdministracion->fecha)->format('d/m/Y') }}
                                @if($ultimaAdministracion->hora_real)
                                    a las {{ \Carbon\Carbon::parse($ultimaAdministracion->hora_real)->format('H:i') }}
                                @endif
                                —
                                @if($ultimaAdministracion->administrado)
                                    <span class="text-emerald-600">Administrada</span>
                                @else
                                    <span class="text-orange-600">No administrada</span>
                                    @if($ultimaAdministracion->motivo_omision)
                                        ({{ \Illuminate\Support\Str::limit($ultimaAdministracion->motivo_omision, 40) }})
                                    @endif
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- 3. Signos Vitales | Valoración Funcional --}}
                <div class="grid gap-5 md:grid-cols-2">

                    {{-- Signos Vitales --}}
                    <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden">
                        <div class="bg-azul-profundo px-5 py-3.5 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                                <i class="ph-bold ph-activity text-terracota text-sm"></i>
                                Últimos Signos Vitales
                            </h3>
                            @can('salud.signos.ver')
                            <a href="{{ route('admin.salud-seguimiento.signos', $adulto) }}"
                               class="rounded-lg bg-white/10 px-2 py-1 text-[10px] font-black uppercase text-white hover:bg-white/20 transition-colors"
                               title="Registrar signos vitales">
                                <i class="ph-bold ph-plus"></i>
                            </a>
                            @endcan
                        </div>
                        <div class="p-5">
                            @if($ultimoSigno)
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">P. Arterial</p>
                                        <p class="mt-1 text-base font-black text-azul-profundo leading-tight">
                                            {{ $ultimoSigno->presion_arterial ?? '—' }}
                                        </p>
                                        <p class="text-[9px] text-azul-profundo/40">mmHg</p>
                                    </div>
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">F. Cardíaca</p>
                                        <p class="mt-1 text-base font-black text-terracota leading-tight">
                                            {{ $ultimoSigno->frecuencia_cardiaca ?? '—' }}
                                        </p>
                                        <p class="text-[9px] text-azul-profundo/40">bpm</p>
                                    </div>
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">Saturación</p>
                                        <p class="mt-1 text-base font-black text-blue-600 leading-tight">
                                            {{ $ultimoSigno->saturacion !== null ? $ultimoSigno->saturacion . '%' : '—' }}
                                        </p>
                                        <p class="text-[9px] text-azul-profundo/40">SpO₂</p>
                                    </div>
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">Temperatura</p>
                                        <p class="mt-1 text-base font-black text-amber-600 leading-tight">
                                            {{ $ultimoSigno->temperatura !== null ? $ultimoSigno->temperatura . '°C' : '—' }}
                                        </p>
                                        <p class="text-[9px] text-azul-profundo/40">°C</p>
                                    </div>
                                    @if($ultimoSigno->peso || $ultimoSigno->imc)
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">Peso</p>
                                        <p class="mt-1 text-base font-black text-azul-profundo leading-tight">
                                            {{ $ultimoSigno->peso !== null ? $ultimoSigno->peso . ' kg' : '—' }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">IMC</p>
                                        <p class="mt-1 text-base font-black text-azul-profundo leading-tight">
                                            {{ $ultimoSigno->imc !== null ? number_format($ultimoSigno->imc, 1) : '—' }}
                                        </p>
                                    </div>
                                    @endif
                                </div>
                                <p class="text-center text-[10px] font-bold text-azul-profundo/40 mt-3 pt-3 border-t border-[#C7B5A3]/20">
                                    Registrado el {{ \Carbon\Carbon::parse($ultimoSigno->fecha)->format('d/m/Y') }}
                                    @if($ultimoSigno->hora)
                                        a las {{ \Carbon\Carbon::parse($ultimoSigno->hora)->format('H:i') }}
                                    @endif
                                </p>
                            @else
                                <div class="py-8 text-center">
                                    <i class="ph-bold ph-activity text-3xl text-[#C7B5A3]/40 mb-2 block"></i>
                                    <p class="text-xs font-black text-azul-profundo/40">Sin registros de signos vitales</p>
                                    @can('salud.signos.crear')
                                    <a href="{{ route('admin.salud-seguimiento.signos', $adulto) }}"
                                       class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-azul-profundo px-3 py-1.5 text-xs font-black text-white hover:opacity-90 transition-opacity">
                                        <i class="ph-bold ph-plus"></i> Registrar
                                    </a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Valoración Funcional --}}
                    <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden">
                        <div class="bg-azul-profundo px-5 py-3.5 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                                <i class="ph-bold ph-person-simple-walk text-terracota text-sm"></i>
                                Valoración Funcional
                            </h3>
                            @can('salud.valoracion.ver')
                            <a href="{{ route('admin.salud-seguimiento.valoracion', $adulto) }}"
                               class="rounded-lg bg-white/10 px-2 py-1 text-[10px] font-black uppercase text-white hover:bg-white/20 transition-colors"
                               title="Registrar valoración funcional">
                                <i class="ph-bold ph-plus"></i>
                            </a>
                            @endcan
                        </div>
                        <div class="p-5">
                            @if($valoracionFuncional)
                                @php
                                    $riesgo = strtoupper((string) $valoracionFuncional->riesgo_caida);
                                    $riesgoBadge = match($riesgo) {
                                        'ALTO'  => 'bg-rose-100 text-rose-700 border-rose-200',
                                        'MEDIO' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    };
                                    $depLabels = [
                                        'INDEPENDIENTE'        => 'Independiente',
                                        'DEPENDENCIA_PARCIAL'  => 'Dependencia parcial',
                                        'ALTA_DEPENDENCIA'     => 'Alta dependencia',
                                        'SUPERVISION_PERMANENTE' => 'Supervisión permanente',
                                    ];
                                    $depTexto = $depLabels[$valoracionFuncional->nivel_dependencia] ?? ($valoracionFuncional->nivel_dependencia ?? '—');
                                    $barthel  = $valoracionFuncional->indice_barthel;
                                    $barthelTexto = match(true) {
                                        $barthel === null          => null,
                                        $barthel <= 20             => 'Dependencia total',
                                        $barthel <= 40             => 'Dependencia severa',
                                        $barthel <= 60             => 'Dependencia moderada',
                                        $barthel <= 90             => 'Dependencia leve',
                                        $barthel < 100             => 'Dependencia escasa',
                                        default                    => 'Independiente',
                                    };
                                @endphp
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50">Riesgo de Caída</p>
                                        <span class="rounded-full border px-3 py-0.5 text-xs font-black uppercase {{ $riesgoBadge }}">
                                            {{ $valoracionFuncional->riesgo_caida ?? '—' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-1">Nivel de Dependencia</p>
                                        <p class="text-sm font-bold text-azul-profundo">{{ $depTexto }}</p>
                                    </div>
                                    @if($barthel !== null)
                                    <div class="bg-[#F7F5F2] rounded-xl p-3 border border-[#C7B5A3]/20">
                                        <div class="flex items-center justify-between mb-1">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50">Índice de Barthel</p>
                                            <span class="text-lg font-black text-azul-profundo">{{ $barthel }}<span class="text-xs text-azul-profundo/40">/100</span></span>
                                        </div>
                                        @if($barthelTexto)
                                        <div class="w-full bg-[#C7B5A3]/30 rounded-full h-1.5 mt-2">
                                            <div class="h-1.5 rounded-full {{ $barthel < 40 ? 'bg-rose-500' : ($barthel < 61 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                 style="width: {{ min($barthel, 100) }}%"></div>
                                        </div>
                                        <p class="text-[10px] text-azul-profundo/60 font-bold mt-1.5">{{ $barthelTexto }}</p>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                <p class="text-[10px] font-bold text-azul-profundo/40 mt-4 pt-3 border-t border-[#C7B5A3]/20">
                                    Valoración del {{ \Carbon\Carbon::parse($valoracionFuncional->fecha_valoracion)->format('d/m/Y') }}
                                </p>
                            @else
                                <div class="py-8 text-center">
                                    <i class="ph-bold ph-person-simple-walk text-3xl text-[#C7B5A3]/40 mb-2 block"></i>
                                    <p class="text-xs font-black text-azul-profundo/40">Sin valoración funcional vigente</p>
                                    @can('salud.valoracion.crear')
                                    <a href="{{ route('admin.salud-seguimiento.valoracion', $adulto) }}"
                                       class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-azul-profundo px-3 py-1.5 text-xs font-black text-white hover:opacity-90 transition-opacity">
                                        <i class="ph-bold ph-plus"></i> Registrar
                                    </a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                {{-- /Signos | Valoración --}}

                {{-- 4. Historial Complementario: Atenciones | Evaluaciones | Observaciones --}}
                <div class="grid gap-5 md:grid-cols-3">

                    {{-- Atenciones Recientes --}}
                    <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden">
                        <div class="bg-[#2A9D8F] px-4 py-3 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-1.5">
                                <i class="ph-bold ph-stethoscope text-sm"></i>
                                Atenciones
                            </h3>
                            <a href="{{ route('admin.adultos-mayores.atenciones.index', $adulto) }}"
                               class="text-white/70 hover:text-white transition-colors text-[10px] font-black">
                                Ver todas →
                            </a>
                        </div>
                        <div class="p-4">
                            @forelse($atenciones as $atencion)
                                <div class="mb-3 pb-3 border-b border-[#E6DDD3] last:mb-0 last:pb-0 last:border-0">
                                    <p class="text-[10px] font-black text-azul-profundo/40 uppercase tracking-wide">
                                        {{ \Carbon\Carbon::parse($atencion->fecha)->format('d/m/Y') }}
                                        @if($atencion->hora)
                                            {{ \Carbon\Carbon::parse($atencion->hora)->format('H:i') }}
                                        @endif
                                    </p>
                                    <p class="text-xs font-bold text-azul-profundo mt-0.5 leading-snug">
                                        {{ \Illuminate\Support\Str::limit($atencion->obs ?? '—', 70) }}
                                    </p>
                                </div>
                            @empty
                                <div class="py-4 text-center">
                                    <p class="text-xs font-bold text-azul-profundo/40 italic">Sin registros recientes</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Evaluaciones Cognitivas --}}
                    <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden">
                        <div class="bg-[#7A68B0] px-4 py-3 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-1.5">
                                <i class="ph-bold ph-brain text-sm"></i>
                                Evaluación Cog.
                            </h3>
                            <a href="{{ route('admin.adultos-mayores.evaluaciones.index', $adulto) }}"
                               class="text-white/70 hover:text-white transition-colors text-[10px] font-black">
                                Ver todas →
                            </a>
                        </div>
                        <div class="p-4">
                            @if($evaluacionCognitiva)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[10px] font-black text-azul-profundo/40 uppercase tracking-wide">
                                            {{ \Carbon\Carbon::parse($evaluacionCognitiva->fecha_eval)->format('d/m/Y') }}
                                        </p>
                                        @if($evaluacionCognitiva->nivel_riesgo)
                                        <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase border
                                            {{ strtoupper($evaluacionCognitiva->nivel_riesgo) === 'ALTO'
                                                ? 'bg-rose-50 text-rose-700 border-rose-200'
                                                : (strtoupper($evaluacionCognitiva->nivel_riesgo) === 'MEDIO'
                                                    ? 'bg-amber-50 text-amber-700 border-amber-200'
                                                    : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                                            {{ $evaluacionCognitiva->nivel_riesgo }}
                                        </span>
                                        @endif
                                    </div>
                                    @if($evaluacionCognitiva->puntaje_total !== null)
                                    <p class="text-xs font-bold text-azul-profundo">
                                        Puntaje:
                                        <span class="text-azul-profundo font-black">{{ $evaluacionCognitiva->puntaje_total }}</span>
                                        @if($evaluacionCognitiva->puntaje_maximo)
                                            <span class="text-azul-profundo/40">/{{ $evaluacionCognitiva->puntaje_maximo }}</span>
                                        @endif
                                    </p>
                                    @endif
                                    @if($evaluacionCognitiva->resultado_interpretacion)
                                    <p class="text-xs font-bold text-azul-profundo/70 leading-snug bg-[#F7F5F2] rounded-lg p-2 border border-[#C7B5A3]/20">
                                        {{ \Illuminate\Support\Str::limit($evaluacionCognitiva->resultado_interpretacion, 80) }}
                                    </p>
                                    @endif
                                </div>
                            @else
                                <div class="py-4 text-center">
                                    <p class="text-xs font-bold text-azul-profundo/40 italic">Sin registros recientes</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Observaciones Recientes --}}
                    <div class="rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden">
                        <div class="bg-[#D4843A] px-4 py-3 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-1.5">
                                <i class="ph-bold ph-eye text-sm"></i>
                                Observaciones
                            </h3>
                            <a href="{{ route('admin.adultos-mayores.observaciones.index', $adulto) }}"
                               class="text-white/70 hover:text-white transition-colors text-[10px] font-black">
                                Ver todas →
                            </a>
                        </div>
                        <div class="p-4">
                            @forelse($observaciones as $obs)
                                <div class="mb-3 pb-3 border-b border-[#E6DDD3] last:mb-0 last:pb-0 last:border-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <p class="text-[10px] font-black text-azul-profundo/40 uppercase tracking-wide">
                                            {{ \Carbon\Carbon::parse($obs->fecha)->format('d/m/Y') }}
                                        </p>
                                        @if($obs->tipo_obs)
                                        <span class="text-[9px] font-bold text-azul-profundo/50 bg-[#F7F5F2] px-1.5 py-0.5 rounded">
                                            {{ $obs->tipo_obs }}
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-bold text-azul-profundo mt-0.5 leading-snug">
                                        {{ \Illuminate\Support\Str::limit($obs->descripcion ?? '—', 70) }}
                                    </p>
                                </div>
                            @empty
                                <div class="py-4 text-center">
                                    <p class="text-xs font-bold text-azul-profundo/40 italic">Sin registros recientes</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
                {{-- /Historial Complementario --}}

            </div>
            {{-- /COLUMNA DERECHA --}}

        </div>
        {{-- /GRID PRINCIPAL --}}
</div>
