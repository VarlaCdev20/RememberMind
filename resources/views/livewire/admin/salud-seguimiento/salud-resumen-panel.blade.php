<div class="min-h-screen py-8 font-sans antialiased text-[#2F3E5C]">
    <div class="mx-auto max-w-[1540px] px-4 sm:px-6 lg:px-8">
        {{-- ENCABEZADO Y VOLVER --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-[#C7B5A3]/50 pb-6 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.salud-seguimiento.index') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm transition-all hover:bg-terracota hover:text-white border border-[#C7B5A3]/40">
                    <i class="ph-bold ph-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight text-azul-profundo">
                        Expediente de Salud
                    </h1>
                    <p class="mt-1 text-sm font-bold text-azul-profundo/60">
                        {{ $adulto->nombres }} {{ $adulto->ap_paterno }} • {{ $adulto->cod_am }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-emerald-700 shadow-sm border border-emerald-200">
                    {{ $adulto->estado->estado ?? 'Desconocido' }}
                </span>
                <a href="{{ route('admin.adultos-mayores.show', $adulto) }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-[#C7B5A3]/40 px-4 py-2 text-xs font-black uppercase tracking-wider text-azul-profundo shadow-sm transition-all hover:bg-azul-profundo hover:text-white">
                    <i class="ph-bold ph-identification-card"></i>
                    Ficha Integral
                </a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            
            {{-- COLUMNA IZQUIERDA: PERFIL Y ALERTAS (4 columnas) --}}
            <div class="lg:col-span-4 space-y-6">
                
                {{-- Tarjeta Perfil --}}
                <div class="overflow-hidden rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm">
                    <div class="h-24 w-full bg-gradient-to-br from-terracota/80 to-azul-profundo/80 relative">
                        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                    </div>
                    <div class="relative px-6 pb-6 pt-12 flex flex-col items-center">
                        <div class="absolute -top-12 left-1/2 -translate-x-1/2">
                            <div class="h-24 w-24 overflow-hidden rounded-full border-4 border-white bg-white shadow-md">
                                @if($adulto->foto_perfil)
                                    <img src="{{ Storage::url($adulto->foto_perfil) }}" alt="{{ $adulto->nombres }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#E6DDD3] to-[#C7B5A3]">
                                        <span class="text-3xl font-black text-azul-profundo/40">{{ substr($adulto->nombres, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <h2 class="text-xl font-black text-azul-profundo">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h2>
                        <p class="text-xs font-bold text-terracota mt-1">{{ \Carbon\Carbon::parse($adulto->fecha_nac)->age }} años • {{ $adulto->genero }}</p>
                    </div>
                </div>

                {{-- Alertas Clínico-Funcionales --}}
                <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4 border-b border-[#C7B5A3]/20 pb-3">
                        <h3 class="text-xs font-black uppercase tracking-widest text-azul-profundo flex items-center gap-2">
                            <i class="ph-bold ph-warning-circle text-amber-500 text-base"></i>
                            Alertas de Salud
                        </h3>
                    </div>
                    <div class="space-y-3">
                        @forelse($alertas as $alerta)
                            <div class="rounded-2xl p-4 border {{ $alerta['nivel'] === 'critica' ? 'bg-rose-50 border-rose-200' : ($alerta['nivel'] === 'atencion' ? 'bg-amber-50 border-amber-200' : 'bg-blue-50 border-blue-200') }}">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="ph-fill {{ $alerta['nivel'] === 'critica' ? 'ph-warning-octagon text-rose-500' : ($alerta['nivel'] === 'atencion' ? 'ph-warning text-amber-500' : 'ph-info text-blue-500') }}"></i>
                                    <span class="text-[10px] font-black uppercase tracking-wider {{ $alerta['nivel'] === 'critica' ? 'text-rose-700' : ($alerta['nivel'] === 'atencion' ? 'text-amber-700' : 'text-blue-700') }}">
                                        {{ $alerta['tipo'] }}
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-azul-profundo/80 leading-relaxed">{{ $alerta['mensaje'] }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4 text-center">
                                <i class="ph-bold ph-check-circle text-2xl text-emerald-500 mb-2"></i>
                                <p class="text-xs font-black text-emerald-700">Sin alertas clínicas activas</p>
                            </div>
                        @endforelse
                        <p class="text-[9px] text-azul-profundo/40 italic text-center mt-2">Estos avisos son orientativos y no constituyen diagnóstico médico.</p>
                    </div>
                </div>

                {{-- Accesos Rápidos a Módulos Originales --}}
                <div class="rounded-3xl border border-[#C7B5A3]/40 bg-[#F7F5F2] p-6 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-azul-profundo mb-4 border-b border-[#C7B5A3]/20 pb-3">
                        Historial Complementario
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('admin.adultos-mayores.observaciones.index', $adulto) }}" class="flex items-center justify-between rounded-xl bg-white px-4 py-3 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2"><i class="ph-bold ph-eye text-terracota"></i> Observaciones Diarias</span>
                            <i class="ph-bold ph-caret-right"></i>
                        </a>
                        <a href="{{ route('admin.adultos-mayores.atenciones.index', $adulto) }}" class="flex items-center justify-between rounded-xl bg-white px-4 py-3 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2"><i class="ph-bold ph-stethoscope text-terracota"></i> Atenciones e Incidentes</span>
                            <i class="ph-bold ph-caret-right"></i>
                        </a>
                        <a href="{{ route('admin.adultos-mayores.evaluaciones.index', $adulto) }}" class="flex items-center justify-between rounded-xl bg-white px-4 py-3 text-xs font-bold text-azul-profundo shadow-sm hover:text-terracota hover:shadow-md transition-all">
                            <span class="flex items-center gap-2"><i class="ph-bold ph-brain text-terracota"></i> Evaluaciones Cognitivas</span>
                            <i class="ph-bold ph-caret-right"></i>
                        </a>
                    </div>
                </div>

            </div>

            {{-- COLUMNA DERECHA: PANELES DE SALUD (8 columnas) --}}
            <div class="lg:col-span-8 space-y-6">
                
                {{-- 1. Ficha Médica Resumen --}}
                <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden flex flex-col">
                    <div class="bg-azul-profundo px-6 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-wider text-white flex items-center gap-2">
                            <i class="ph-bold ph-file-text text-terracota text-lg"></i>
                            Ficha Médica Base
                        </h3>
                        @can('salud.ficha.ver')
                            <a href="{{ route('admin.salud-seguimiento.ficha', $adulto) }}" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-black text-white hover:bg-white/20 transition-colors">
                                Gestionar Ficha
                            </a>
                        @endcan
                    </div>
                    <div class="p-6">
                        @if($fichaMedica)
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-2">Enfermedades Diagnosticadas</p>
                                    <div class="flex flex-wrap gap-2">
                                        @php
                                            $enfermedades = [
                                                'Hipertensión' => $fichaMedica->hipertension,
                                                'Diabetes' => $fichaMedica->diabetes,
                                                'Cardiacos' => $fichaMedica->problemas_cardiacos,
                                                'ACV' => $fichaMedica->acv,
                                                'Parkinson' => $fichaMedica->parkinson,
                                                'Epilepsia' => $fichaMedica->epilepsia,
                                                'Alzheimer' => $fichaMedica->alzheimer_diagnosticado,
                                            ];
                                        @endphp
                                        @foreach(array_filter($enfermedades) as $nombre => $tiene)
                                            <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                                                {{ $nombre }}
                                            </span>
                                        @endforeach
                                        @if(count(array_filter($enfermedades)) === 0)
                                            <span class="text-xs font-bold text-azul-profundo/50 italic">Ninguna registrada en sistema</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-2">Alergias</p>
                                    <p class="text-sm font-bold text-azul-profundo bg-[#F7F5F2] p-3 rounded-xl border border-[#C7B5A3]/30 min-h-[46px]">
                                        {{ $fichaMedica->alergias ?: 'Ninguna registrada' }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center">
                                <i class="ph-fill ph-file-dashed text-4xl text-[#C7B5A3]/60 mb-2"></i>
                                <p class="text-sm font-black text-azul-profundo">No hay ficha médica activa</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 2. Medicación y Administraciones --}}
                <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden flex flex-col">
                    <div class="bg-azul-profundo px-6 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-wider text-white flex items-center gap-2">
                            <i class="ph-bold ph-pill text-terracota text-lg"></i>
                            Tratamiento Farmacológico
                        </h3>
                        <div class="flex gap-2">
                            @can('salud.administracion.ver')
                                <a href="{{ route('admin.salud-seguimiento.administracion', $adulto) }}" class="rounded-lg bg-terracota px-3 py-1.5 text-[10px] uppercase font-black text-white hover:bg-terracota-dark transition-colors">
                                    Registrar Toma
                                </a>
                            @endcan
                            @can('salud.medicacion.ver')
                                <a href="{{ route('admin.salud-seguimiento.medicacion', $adulto) }}" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-black text-white hover:bg-white/20 transition-colors">
                                    Ver Medicamentos
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left text-sm text-azul-profundo">
                            <thead class="bg-[#F7F5F2] text-[10px] font-black uppercase text-azul-profundo/60">
                                <tr>
                                    <th class="px-6 py-3">Medicamento</th>
                                    <th class="px-6 py-3">Dosis y Frec.</th>
                                    <th class="px-6 py-3 text-right">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/20">
                                @forelse($medicacionActiva->take(4) as $med)
                                    <tr class="hover:bg-[#F7F5F2]/50 transition-colors">
                                        <td class="px-6 py-4 font-bold">{{ $med->nombre_medicamento }}</td>
                                        <td class="px-6 py-4 text-xs">{{ $med->dosis }} • {{ $med->frecuencia }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-black uppercase text-emerald-700">Activa</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-xs font-bold text-azul-profundo/50 italic">
                                            No hay tratamientos farmacológicos activos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. Signos Vitales y Valoración --}}
                <div class="grid gap-6 md:grid-cols-2">
                    
                    {{-- Signos Vitales --}}
                    <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden flex flex-col">
                        <div class="bg-azul-profundo px-6 py-4 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                                <i class="ph-bold ph-activity text-terracota text-base"></i>
                                Últimos Signos Vitales
                            </h3>
                            @can('salud.signos.ver')
                                <a href="{{ route('admin.salud-seguimiento.signos', $adulto) }}" class="rounded-lg bg-white/10 px-2 py-1 text-[10px] font-black uppercase text-white hover:bg-white/20 transition-colors">
                                    <i class="ph-bold ph-plus"></i>
                                </a>
                            @endcan
                        </div>
                        <div class="p-6">
                            @if($ultimosSignos->isNotEmpty())
                                @php $sv = $ultimosSignos->first(); @endphp
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">P. Arterial</p>
                                        <p class="mt-1 text-lg font-black text-azul-profundo">{{ $sv->presion_sistolica }}/{{ $sv->presion_diastolica }}</p>
                                    </div>
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">F. Cardiaca</p>
                                        <p class="mt-1 text-lg font-black text-terracota">{{ $sv->frecuencia_cardiaca }} <span class="text-[10px] text-azul-profundo/50">bpm</span></p>
                                    </div>
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">Saturación</p>
                                        <p class="mt-1 text-lg font-black text-blue-600">{{ $sv->saturacion_oxigeno }}%</p>
                                    </div>
                                    <div class="rounded-xl bg-[#F7F5F2] p-3 text-center border border-[#C7B5A3]/20">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/50">Temperatura</p>
                                        <p class="mt-1 text-lg font-black text-amber-600">{{ $sv->temperatura }}°C</p>
                                    </div>
                                </div>
                                <p class="text-center text-[10px] font-bold text-azul-profundo/50 mt-4">
                                    Registrado el {{ \Carbon\Carbon::parse($sv->fecha)->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($sv->hora)->format('H:i') }}
                                </p>
                            @else
                                <div class="py-12 text-center">
                                    <p class="text-sm font-black text-azul-profundo/40">Sin registros vitales</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Valoración Funcional --}}
                    <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden flex flex-col">
                        <div class="bg-azul-profundo px-6 py-4 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                                <i class="ph-bold ph-person-simple-walk text-terracota text-base"></i>
                                Valoración Funcional
                            </h3>
                            @can('salud.valoracion.ver')
                                <a href="{{ route('admin.salud-seguimiento.valoracion', $adulto) }}" class="rounded-lg bg-white/10 px-2 py-1 text-[10px] font-black uppercase text-white hover:bg-white/20 transition-colors">
                                    <i class="ph-bold ph-plus"></i>
                                </a>
                            @endcan
                        </div>
                        <div class="p-6">
                            @if($valoracionFuncional)
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-1">Riesgo de Caída</p>
                                        <span class="rounded-full px-3 py-1 text-xs font-black uppercase 
                                            {{ strtoupper($valoracionFuncional->riesgo_caida) === 'ALTO' ? 'bg-rose-100 text-rose-700' : (strtoupper($valoracionFuncional->riesgo_caida) === 'MEDIO' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            {{ $valoracionFuncional->riesgo_caida }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-1">Nivel de Dependencia</p>
                                        <p class="text-sm font-bold text-azul-profundo">{{ $valoracionFuncional->nivel_dependencia }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-1">Movilidad</p>
                                        <p class="text-sm font-bold text-azul-profundo">{{ $valoracionFuncional->movilidad }}</p>
                                    </div>
                                </div>
                                <p class="text-left text-[10px] font-bold text-azul-profundo/50 mt-4 border-t border-[#C7B5A3]/20 pt-3">
                                    Registrado el {{ \Carbon\Carbon::parse($valoracionFuncional->fecha)->format('d/m/Y') }}
                                </p>
                            @else
                                <div class="py-12 text-center">
                                    <p class="text-sm font-black text-azul-profundo/40">Sin valoración vigente</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
