@php
    $estadoBadge = [
        'normal' => 'border-[#8DA280]/35 bg-[#8DA280]/18 text-[#63775B]',
        'observacion' => 'border-[#D9A05B]/40 bg-[#D9A05B]/16 text-[#9A6A2F]',
        'fuera_rango' => 'border-[#E27D60]/35 bg-[#E27D60]/12 text-[#C45F4B]',
        'requiere_revision' => 'border-[#5B5F97]/35 bg-[#5B5F97]/12 text-[#4E528A]',
        'sin_datos' => 'border-[#C7B5A3]/55 bg-[#E6DDD3]/65 text-[#7C7168]',
        'anulado' => 'border-[#967B66]/35 bg-[#967B66]/12 text-[#7A604B]',
    ];

    $estadoIcono = [
        'normal' => 'ph-check-circle',
        'observacion' => 'ph-warning-circle',
        'fuera_rango' => 'ph-warning-octagon',
        'requiere_revision' => 'ph-first-aid-kit',
        'sin_datos' => 'ph-minus-circle',
        'anulado' => 'ph-prohibit',
    ];

    $graficaOpciones = [
        'presion' => 'Presión arterial',
        'frecuencia_cardiaca' => 'Frecuencia cardíaca',
        'saturacion' => 'Saturación',
        'temperatura' => 'Temperatura',
        'frecuencia_respiratoria' => 'Frecuencia respiratoria',
        'peso' => 'Peso e IMC',
    ];
@endphp

<div class="space-y-4">
    {{-- A. CABECERA DEL SUBMÓDULO --}}
    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/70 text-[#E27D60] shadow-sm">
                    <i class="ph-bold ph-activity text-2xl"></i>
                </span>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-[#2F3E5C]">Signos Vitales</h2>
                    <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/62">
                        Monitoreo, control y análisis de parámetros vitales del adulto mayor.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.salud-seguimiento.reportes') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/60 bg-[#E6DDD3]/70 px-4 py-2 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#D5C7B9] active:scale-95">
                    <i class="ph-bold ph-file-chart text-sm"></i>
                    Generar Reporte
                </a>
                @can('salud.signos.crear')
                    <button type="button" wire:click="abrirFormularioNuevo" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-[0_10px_22px_rgba(226,125,96,0.24)] transition hover:-translate-y-0.5 hover:bg-[#D96F58] active:scale-95">
                        <i class="ph-bold ph-plus-circle text-sm"></i>
                        Registrar Signos Vitales
                    </button>
                @endcan
            </div>
        </div>
    </section>

    {{-- B. SELECCIONAR PACIENTE --}}
    <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
        <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Seleccionar Paciente</label>
        <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
            <div>
                <div class="relative">
                    <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                    <input type="text" wire:model.live.debounce.350ms="buscarPaciente" placeholder="Buscar por nombre o apellido..." class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white py-3 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition placeholder:text-[#2F3E5C]/40 focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                </div>
            </div>
            <div>
                <div class="relative w-full">
                    <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                    <select wire:model="adultoSeleccionado" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white py-3 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition appearance-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Seleccione un adulto mayor</option>
                        @foreach($pacientesSelector as $paciente)
                            @php $nombrePaciente = trim("{$paciente->nombres} {$paciente->ap_paterno} {$paciente->ap_materno}"); @endphp
                            <option value="{{ $paciente->cod_am }}">{{ $nombrePaciente ?: 'Adulto mayor' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <button wire:click="buscarPacienteAction" type="button" class="inline-flex h-full w-full items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-6 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#5B5F97] active:scale-95">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    Buscar
                </button>
            </div>
        </div>
    </section>

    @if($adulto)
        {{-- C. CARD DEL PACIENTE SELECCIONADO --}}
        <section class="rounded-[1.6rem] border border-[#8DA280]/30 bg-[#8DA280]/10 p-4 shadow-sm backdrop-blur-xl relative overflow-hidden">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[#8DA280]/20 blur-3xl"></div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 overflow-hidden rounded-2xl border-2 border-[#E6DDD3] bg-[#2F3E5C] shadow-sm">
                        @if($adulto->foto)
                            <img src="{{ Storage::url($adulto->foto) }}" alt="Foto" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-sm font-black text-white">
                                {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2F3E5C]">{{ $pacienteResumen['nombre'] }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-block rounded bg-[#E6DDD3]/70 px-2 py-0.5 text-[9px] font-black uppercase text-[#2F3E5C]/60">{{ $pacienteResumen['edad'] }}</span>
                            @if($pacienteResumen['estado'])
                            <span class="inline-block rounded bg-[#8DA280]/20 px-2 py-0.5 text-[9px] font-black uppercase text-[#63775B]">{{ $pacienteResumen['estado'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Registros Totales</p>
                    <p class="text-xl font-black text-[#63775B]">{{ $pacienteResumen['vigentes'] }}</p>
                    <p class="text-[9px] font-bold text-[#2F3E5C]/50 mt-1">Último: {{ $pacienteResumen['ultimo_control'] }}</p>
                </div>
            </div>
        </section>

        {{-- D. CARDS DE SIGNOS VITALES --}}
        <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @forelse($metricas as $metrica)
                <div class="relative overflow-hidden rounded-[1.4rem] border {{ $estadoBadge[$metrica['status_key']] ?? 'bg-white' }} p-3 shadow-sm backdrop-blur-md">
                    <i class="ph-bold {{ $metrica['icon'] }} absolute right-3 top-3 text-2xl opacity-10"></i>
                    <p class="pr-7 text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/70">{{ $metrica['label'] }}</p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-2xl font-black {{ $estadoBadge[$metrica['status_key']]['text'] ?? 'text-[#2F3E5C]' }}">
                            {{ $metrica['value'] !== '-' ? $metrica['value'] : '--' }}
                        </span>
                        @if($metrica['value'] !== '-')
                            <span class="text-[10px] font-bold text-[#2F3E5C]/60">{{ $metrica['unit'] }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 border-t border-[#C7B5A3]/20 pt-2 text-[9px] font-bold">
                        <i class="ph-bold {{ $estadoIcono[$metrica['status_key']] ?? 'ph-minus' }} {{ $estadoBadge[$metrica['status_key']]['text'] ?? 'text-[#2F3E5C]/60' }}"></i>
                        <span class="{{ $estadoBadge[$metrica['status_key']]['text'] ?? 'text-[#2F3E5C]/60' }}">
                            {{ $metrica['status_label'] ?? str_replace('_', ' ', Str::title($metrica['status_key'])) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-[1.4rem] border border-dashed border-[#C7B5A3]/65 bg-[#F3ECE4]/40 p-6 text-center text-[#2F3E5C]/50">
                    <i class="ph-bold ph-heartbeat text-3xl mb-2"></i>
                    <p class="text-xs font-bold">No hay mediciones registradas.</p>
                </div>
            @endforelse
        </section>

        {{-- F. GRÁFICA Y REPORTE LADO A LADO --}}
        <section class="grid gap-4 lg:grid-cols-[1.8fr_1fr]">
            <!-- Gráfica Principal -->
            <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-white p-4 shadow-sm"
                 x-data="{ chartKey: '{{ $chartKey }}', payload: @js($chartData) }"
                 x-init="$watch('chartKey', () => window.rmSignosVitalesMainChart($el, payload)); window.rmSignosVitalesMainChart($el, payload);">
                
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Evolución Clínica</h3>
                    <select wire:model.live="grafica" class="rounded-xl border border-[#C7B5A3]/70 bg-[#F8F3ED] py-1.5 pl-3 pr-8 text-xs font-bold text-[#2F3E5C] outline-none">
                        @foreach($graficaOpciones as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if(!empty($chartData['main']['datasets']))
                    <div class="relative h-56 w-full">
                        <canvas data-chart="main"></canvas>
                    </div>
                @else
                    <div class="flex h-56 w-full flex-col items-center justify-center rounded-xl border border-dashed border-[#C7B5A3]/50 bg-[#F8F3ED]/50 text-[#2F3E5C]/40">
                        <i class="ph-bold ph-chart-line text-3xl"></i>
                        <p class="mt-2 text-[10px] font-black uppercase tracking-widest">Información insuficiente para graficar</p>
                    </div>
                @endif
            </div>

            <!-- Panel de Reporte -->
            <div class="rounded-[1.6rem] border border-[#5B5F97]/20 bg-[#5B5F97]/5 p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-[#5B5F97] mb-4 flex items-center gap-2">
                        <i class="ph-bold ph-stethoscope text-lg"></i> Interpretación Referencial
                    </h3>

                    @if($reporteClinico)
                        <div class="rounded-xl border border-[#C7B5A3]/30 bg-white/60 p-4 mb-4">
                            <p class="text-xs font-bold text-[#2F3E5C]/80 leading-relaxed">{{ $reporteClinico['mensaje'] }}</p>
                        </div>
                        
                        <div class="space-y-3 text-[11px] font-bold text-[#2F3E5C]/70">
                            <div class="flex justify-between border-b border-[#C7B5A3]/20 pb-2">
                                <span>Último valor ({{ $reporteClinico['metrica']['label'] ?? '' }})</span>
                                <span class="font-black text-[#5B5F97]">{{ $reporteClinico['metrica']['value'] ?? '--' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#C7B5A3]/20 pb-2">
                                <span>Promedio mensual</span>
                                <span class="font-black text-[#5B5F97]">{{ $reporteClinico['metrica']['avg'] ?? '--' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#C7B5A3]/20 pb-2">
                                <span>Rango detectado</span>
                                <span class="font-black text-[#5B5F97]">{{ $reporteClinico['metrica']['min'] ?? '--' }} - {{ $reporteClinico['metrica']['max'] ?? '--' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6 text-[#2F3E5C]/40">
                            <i class="ph-bold ph-file-dashed text-3xl mb-2"></i>
                            <p class="text-xs font-bold">Sin registros para interpretar</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- SECCIÓN DE REGISTRO POR PARTES --}}
        <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-white p-5 shadow-sm backdrop-blur-xl">
            <h3 class="mb-4 text-sm font-black uppercase tracking-wider text-[#2F3E5C] flex items-center gap-2">
                <i class="ph-bold ph-plus-circle text-lg text-[#E27D60]"></i> Registrar control por partes
            </h3>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                <button type="button" wire:click="abrirFormularioNuevo" class="flex flex-col items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] p-3 text-center text-white transition hover:bg-[#5B5F97] active:scale-95">
                    <i class="ph-bold ph-list-plus text-2xl"></i>
                    <span class="text-[9px] font-black uppercase tracking-wider">General</span>
                </button>
                <button type="button" wire:click="abrirFormularioPresion" class="flex flex-col items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/50 bg-[#F3ECE4]/50 p-3 text-center text-[#2F3E5C] transition hover:bg-[#E6DDD3] active:scale-95">
                    <i class="ph-bold ph-heartbeat text-2xl text-[#E27D60]"></i>
                    <span class="text-[9px] font-black uppercase tracking-wider">Presión Art.</span>
                </button>
                <button type="button" wire:click="abrirFormularioCardiaca" class="flex flex-col items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/50 bg-[#F3ECE4]/50 p-3 text-center text-[#2F3E5C] transition hover:bg-[#E6DDD3] active:scale-95">
                    <i class="ph-bold ph-pulse text-2xl text-[#E27D60]"></i>
                    <span class="text-[9px] font-black uppercase tracking-wider">FC / SatO2</span>
                </button>
                <button type="button" wire:click="abrirFormularioTemperatura" class="flex flex-col items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/50 bg-[#F3ECE4]/50 p-3 text-center text-[#2F3E5C] transition hover:bg-[#E6DDD3] active:scale-95">
                    <i class="ph-bold ph-thermometer text-2xl text-[#D9A05B]"></i>
                    <span class="text-[9px] font-black uppercase tracking-wider">Temp / Resp</span>
                </button>
                <button type="button" wire:click="abrirFormularioPeso" class="flex flex-col items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/50 bg-[#F3ECE4]/50 p-3 text-center text-[#2F3E5C] transition hover:bg-[#E6DDD3] active:scale-95">
                    <i class="ph-bold ph-scales text-2xl text-[#8DA280]"></i>
                    <span class="text-[9px] font-black uppercase tracking-wider">Peso / IMC</span>
                </button>
                <button type="button" wire:click="abrirFormularioObservacion" class="flex flex-col items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/50 bg-[#F3ECE4]/50 p-3 text-center text-[#2F3E5C] transition hover:bg-[#E6DDD3] active:scale-95">
                    <i class="ph-bold ph-note-pencil text-2xl text-[#5B5F97]"></i>
                    <span class="text-[9px] font-black uppercase tracking-wider">Observación</span>
                </button>
            </div>
        </section>

        {{-- E. TABLA / HISTORIAL --}}
        <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-white shadow-sm overflow-hidden relative">
            <div class="border-b border-[#C7B5A3]/30 bg-[#E6DDD3]/40 px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C] flex items-center gap-2">
                    <i class="ph-bold ph-list-numbers"></i> Historial de Registros
                </h3>
                
                <!-- Filtros simples si los hay -->
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="rounded-lg border border-[#C7B5A3]/70 bg-white py-1 pl-2 pr-6 text-[10px] font-bold text-[#2F3E5C] outline-none">
                        <option value="5">5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#F8F3ED] text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60 border-b border-[#C7B5A3]/40">
                        <tr>
                            <th class="px-4 py-3">Fecha y Hora</th>
                            <th class="px-4 py-3">PA (mmHg)</th>
                            <th class="px-4 py-3">FC (lpm)</th>
                            <th class="px-4 py-3">Sat O2 (%)</th>
                            <th class="px-4 py-3">Temp (°C)</th>
                            <th class="px-4 py-3">Resp (rpm)</th>
                            <th class="px-4 py-3">Peso (kg) / IMC</th>
                            <th class="px-4 py-3">Registrado por</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C7B5A3]/30 bg-white">
                        @forelse($historial as $signo)
                            @php
                                $estadoClases = $signo->estado === 'VIGENTE' ? '' : 'opacity-60 bg-[#F8F3ED]';
                            @endphp
                            <tr class="transition-colors hover:bg-[#F8F3ED]/70 {{ $estadoClases }}">
                                <td class="px-4 py-2.5">
                                    <p class="text-xs font-black text-[#2F3E5C]">{{ $signo->fecha->format('d/m/Y') }}</p>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/60">{{ $signo->hora_formateada }}</p>
                                    @if($signo->estado === 'ANULADO')
                                        <span class="inline-block mt-0.5 rounded bg-red-100 px-1.5 py-0.5 text-[8px] font-black uppercase text-red-700">Anulado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs font-bold text-[#2F3E5C]">
                                    {{ $signo->presion_sistolica && $signo->presion_diastolica ? $signo->presion_sistolica . '/' . $signo->presion_diastolica : '--' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs font-bold text-[#2F3E5C]">{{ $signo->frecuencia_cardiaca ?: '--' }}</td>
                                <td class="px-4 py-2.5 text-xs font-bold text-[#2F3E5C]">{{ $signo->saturacion ?: '--' }}</td>
                                <td class="px-4 py-2.5 text-xs font-bold text-[#2F3E5C]">{{ $signo->temperatura ?: '--' }}</td>
                                <td class="px-4 py-2.5 text-xs font-bold text-[#2F3E5C]">{{ $signo->frecuencia_respiratoria ?: '--' }}</td>
                                <td class="px-4 py-2.5 text-xs font-bold text-[#2F3E5C]">
                                    {{ $signo->peso ?: '--' }} <br>
                                    <span class="text-[9px] text-[#2F3E5C]/50">{{ $signo->imc ? 'IMC: '.$signo->imc : '' }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-[10px] font-bold text-[#2F3E5C]/70">
                                    {{ $signo->registradoPor->name ?? 'Sistema' }}
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    @if($signo->estado === 'VIGENTE')
                                        <div class="flex items-center justify-end gap-1.5">
                                            @can('salud.signos.editar')
                                                <button type="button" wire:click="abrirFormularioEditar({{ $signo->cod_signo }})" class="inline-flex items-center justify-center rounded-lg border border-[#C7B5A3]/60 bg-[#E6DDD3]/70 p-1.5 text-[#2F3E5C]/70 transition hover:bg-[#2F3E5C] hover:text-white active:scale-95" title="Editar">
                                                    <i class="ph-bold ph-pencil-simple"></i>
                                                </button>
                                                <button type="button" onclick="window.dispatchEvent(new CustomEvent('signos-confirmar-anulacion', { detail: { id: {{ $signo->cod_signo }} } }))" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 p-1.5 text-red-600 transition hover:bg-red-600 hover:text-white active:scale-95" title="Anular">
                                                    <i class="ph-bold ph-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center">
                                    <i class="ph-bold ph-folder-open text-4xl text-[#2F3E5C]/20"></i>
                                    <p class="mt-2 text-xs font-bold text-[#2F3E5C]/50">No hay registros de signos vitales para mostrar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($historial, 'hasPages') && $historial->hasPages())
                <div class="border-t border-[#C7B5A3]/30 bg-[#F8F3ED] px-4 py-3">
                    {{ $historial->links() }}
                </div>
            @endif
        </section>

    @else
        <section class="rounded-[1.6rem] border border-dashed border-[#C7B5A3]/65 bg-[#F3ECE4]/40 p-12 text-center shadow-inner">
            <i class="ph-bold ph-hand-pointing text-4xl text-[#2F3E5C]/20 mb-3 block"></i>
            <h3 class="text-base font-black text-[#2F3E5C]">Ningún paciente seleccionado</h3>
            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Busque y seleccione un adulto mayor para visualizar o registrar sus signos vitales.</p>
        </section>
    @endif

    {{-- MODAL FORMULARIO DE REGISTRO / EDICIÓN --}}
    <x-ui.modal-livewire wire:model="modalFormulario" title="{{ $signoId ? 'Editar Signos Vitales' : 'Registrar Signos Vitales' }}" maxWidth="2xl" closeMethod="cerrarModal">
        <x-slot name="icon">
            <i class="ph-bold ph-heartbeat text-[#E27D60]"></i>
        </x-slot>

        <form wire:submit.prevent="guardar" id="formSignos" x-data="{ paso: 1 }">
            <!-- Barra de progreso -->
            <div class="mb-6 flex items-center justify-between border-b border-[#C7B5A3]/30 pb-4">
                <template x-for="i in 5" :key="i">
                    <div class="flex-1 text-center">
                        <button type="button" @click="paso = i" class="text-[10px] font-black uppercase tracking-wider transition-colors" :class="paso >= i ? 'text-[#E27D60]' : 'text-[#2F3E5C]/40'">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border-2 text-xs" :class="paso >= i ? 'border-[#E27D60] bg-[#E27D60]/10' : 'border-[#C7B5A3]/50 bg-white'">
                                <span x-text="i"></span>
                            </span>
                            <span class="mt-1 hidden sm:block" x-text="['Paciente', 'P. Arterial', 'Parámetros', 'Antropometría', 'Confirmación'][i-1]"></span>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Paso 1: Paciente y Fecha -->
            <div x-show="paso === 1" x-transition.opacity.duration.300ms>
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                    <h4 class="mb-4 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">1. Paciente, Fecha y Hora</h4>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Paciente *</label>
                            @if($adulto)
                                <div class="rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-black text-[#2F3E5C]">{{ $pacienteResumen['nombre'] ?? 'Adulto seleccionado' }}</div>
                            @else
                                <div class="rounded-xl border border-red-500 bg-red-50 px-3 py-2.5 text-xs font-black text-red-700">Debe seleccionar un paciente en la pantalla principal.</div>
                            @endif
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha *</label>
                            <input type="date" wire:model="fecha" class="w-full rounded-xl border {{ $errors->has('fecha') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('fecha') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora *</label>
                            <input type="time" wire:model="hora" class="w-full rounded-xl border {{ $errors->has('hora') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('hora') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="button" @click="paso = 2" class="rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white transition hover:bg-[#5B5F97]">Siguiente Paso <i class="ph-bold ph-arrow-right ml-1"></i></button>
                </div>
            </div>

            <!-- Paso 2: Presión Arterial -->
            <div x-show="paso === 2" x-transition.opacity.duration.300ms style="display: none;">
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                    <h4 class="mb-4 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">2. Presión Arterial</h4>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Sistólica (mmHg)</label>
                            <input type="number" wire:model.live.debounce.400ms="presion_sistolica" min="60" max="250" placeholder="Ej. 120" class="w-full rounded-xl border {{ $errors->has('presion_sistolica') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('presion_sistolica') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Diastólica (mmHg)</label>
                            <input type="number" wire:model.live.debounce.400ms="presion_diastolica" min="40" max="160" placeholder="Ej. 80" class="w-full rounded-xl border {{ $errors->has('presion_diastolica') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('presion_diastolica') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-between">
                    <button type="button" @click="paso = 1" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C]"><i class="ph-bold ph-arrow-left mr-1"></i> Anterior</button>
                    <button type="button" @click="paso = 3" class="rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white transition hover:bg-[#5B5F97]">Siguiente Paso <i class="ph-bold ph-arrow-right ml-1"></i></button>
                </div>
            </div>

            <!-- Paso 3: Parámetros Vitales -->
            <div x-show="paso === 3" x-transition.opacity.duration.300ms style="display: none;">
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                    <h4 class="mb-4 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">3. Frecuencias y Temperatura</h4>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Frecuencia Cardíaca (lpm)</label>
                            <input type="number" wire:model.live.debounce.400ms="frecuencia_cardiaca" min="30" max="220" placeholder="Ej. 72" class="w-full rounded-xl border {{ $errors->has('frecuencia_cardiaca') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('frecuencia_cardiaca') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Saturación O2 (%)</label>
                            <input type="number" wire:model.live.debounce.400ms="saturacion" min="0" max="100" placeholder="Ej. 97" class="w-full rounded-xl border {{ $errors->has('saturacion') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('saturacion') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Temperatura (°C)</label>
                            <input type="number" step="0.1" wire:model.live.debounce.400ms="temperatura" min="30" max="45" placeholder="Ej. 36.5" class="w-full rounded-xl border {{ $errors->has('temperatura') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('temperatura') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Frecuencia Resp. (rpm)</label>
                            <input type="number" wire:model.live.debounce.400ms="frecuencia_respiratoria" min="5" max="60" placeholder="Ej. 18" class="w-full rounded-xl border {{ $errors->has('frecuencia_respiratoria') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('frecuencia_respiratoria') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-between">
                    <button type="button" @click="paso = 2" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C]"><i class="ph-bold ph-arrow-left mr-1"></i> Anterior</button>
                    <button type="button" @click="paso = 4" class="rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white transition hover:bg-[#5B5F97]">Siguiente Paso <i class="ph-bold ph-arrow-right ml-1"></i></button>
                </div>
            </div>

            <!-- Paso 4: Antropometría -->
            <div x-show="paso === 4" x-transition.opacity.duration.300ms style="display: none;">
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                    <h4 class="mb-4 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">4. Peso y Talla</h4>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Peso (kg)</label>
                            <input type="number" step="0.1" wire:model.live.debounce.400ms="peso" min="20" max="250" placeholder="Ej. 65.0" class="w-full rounded-xl border {{ $errors->has('peso') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('peso') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Talla (m o cm)</label>
                            <input type="number" step="0.01" wire:model.live.debounce.400ms="talla" min="0.5" max="250" placeholder="Ej. 1.65" class="w-full rounded-xl border {{ $errors->has('talla') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('talla') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">IMC Calculado</label>
                            <input type="text" wire:model="imc" readonly class="w-full rounded-xl border border-[#C7B5A3]/35 bg-[#D5C7B9]/42 px-3 py-2.5 text-xs font-black text-[#2F3E5C] outline-none">
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-between">
                    <button type="button" @click="paso = 3" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C]"><i class="ph-bold ph-arrow-left mr-1"></i> Anterior</button>
                    <button type="button" @click="paso = 5" class="rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white transition hover:bg-[#5B5F97]">Siguiente Paso <i class="ph-bold ph-arrow-right ml-1"></i></button>
                </div>
            </div>

            <!-- Paso 5: Confirmación -->
            <div x-show="paso === 5" x-transition.opacity.duration.300ms style="display: none;">
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                    <h4 class="mb-4 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">5. Observaciones Finales</h4>
                    
                    @if(!empty($alertasFormulario))
                        <div class="mb-4 rounded-xl border border-red-300 bg-red-50 p-4">
                            <p class="mb-2 text-xs font-black uppercase tracking-wider text-red-700"><i class="ph-fill ph-warning text-sm"></i> Valores fuera del rango referencial</p>
                            <ul class="grid gap-1 sm:grid-cols-2">
                                @foreach($alertasFormulario as $alerta)
                                    <li class="text-[11px] font-bold text-red-600"><span class="font-black">{{ $alerta['campo'] }}:</span> {{ $alerta['mensaje'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid gap-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Observación / Nota (Opcional)</label>
                            <textarea wire:model="observacion" rows="3" placeholder="Contexto de la medición, detalles adicionales..." class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
                            @error('observacion') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-between">
                    <button type="button" @click="paso = 4" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C]"><i class="ph-bold ph-arrow-left mr-1"></i> Anterior</button>
                    <!-- Formulario será enviado automáticamente por Submit de X-UI-MODAL-LIVEWIRE en el footer -->
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F8F3ED] active:scale-95">
                Cancelar
            </button>
            <button type="submit" form="formSignos" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-[0_8px_16px_rgba(226,125,96,0.2)] transition hover:-translate-y-0.5 hover:bg-[#D96F58] active:scale-95 disabled:opacity-50" wire:loading.attr="disabled" wire:target="guardar,confirmarGuardarConAlertas">
                <i wire:loading wire:target="guardar,confirmarGuardarConAlertas" class="ph-bold ph-spinner animate-spin"></i>
                <span>{{ $signoId ? 'Actualizar Signos Vitales' : 'Guardar Signos Vitales' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
    {{-- MODAL PRESIÓN ARTERIAL --}}
    <x-ui.modal-livewire wire:model="modalPresion" title="Registrar Presión Arterial" maxWidth="md" closeMethod="cerrarModal">
        <x-slot name="icon"><i class="ph-bold ph-heartbeat text-[#E27D60]"></i></x-slot>
        <form wire:submit.prevent="guardar" id="formPresion">
            <div class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha *</label>
                        <input type="date" wire:model="fecha" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora *</label>
                        <input type="time" wire:model="hora" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Sistólica (mmHg)</label>
                        <input type="number" wire:model.live.debounce.400ms="presion_sistolica" min="60" max="250" placeholder="120" class="w-full rounded-xl border {{ $errors->has('presion_sistolica') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2">
                        @error('presion_sistolica') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Diastólica (mmHg)</label>
                        <input type="number" wire:model.live.debounce.400ms="presion_diastolica" min="40" max="160" placeholder="80" class="w-full rounded-xl border {{ $errors->has('presion_diastolica') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2">
                        @error('presion_diastolica') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Observación (Opcional)</label>
                    <textarea wire:model="observacion" rows="2" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2"></textarea>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F8F3ED] active:scale-95">Cancelar</button>
            <button type="submit" form="formPresion" class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#D96F58] active:scale-95">Guardar</button>
        </x-slot>
    </x-ui.modal-livewire>

    {{-- MODAL FC Y SATURACION --}}
    <x-ui.modal-livewire wire:model="modalCardiaca" title="FC y Saturación" maxWidth="md" closeMethod="cerrarModal">
        <x-slot name="icon"><i class="ph-bold ph-pulse text-[#E27D60]"></i></x-slot>
        <form wire:submit.prevent="guardar" id="formCardiaca">
            <div class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha *</label>
                        <input type="date" wire:model="fecha" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora *</label>
                        <input type="time" wire:model="hora" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Frecuencia (lpm)</label>
                        <input type="number" wire:model.live.debounce.400ms="frecuencia_cardiaca" min="30" max="220" placeholder="72" class="w-full rounded-xl border {{ $errors->has('frecuencia_cardiaca') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                        @error('frecuencia_cardiaca') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Saturación (%)</label>
                        <input type="number" wire:model.live.debounce.400ms="saturacion" min="0" max="100" placeholder="97" class="w-full rounded-xl border {{ $errors->has('saturacion') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                        @error('saturacion') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Observación (Opcional)</label>
                    <textarea wire:model="observacion" rows="2" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]"></textarea>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F8F3ED] active:scale-95">Cancelar</button>
            <button type="submit" form="formCardiaca" class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#D96F58] active:scale-95">Guardar</button>
        </x-slot>
    </x-ui.modal-livewire>

    {{-- MODAL TEMPERATURA Y RESP --}}
    <x-ui.modal-livewire wire:model="modalTemperatura" title="Temp y Respiración" maxWidth="md" closeMethod="cerrarModal">
        <x-slot name="icon"><i class="ph-bold ph-thermometer text-[#D9A05B]"></i></x-slot>
        <form wire:submit.prevent="guardar" id="formTemperatura">
            <div class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha *</label>
                        <input type="date" wire:model="fecha" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora *</label>
                        <input type="time" wire:model="hora" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Temperatura (°C)</label>
                        <input type="number" step="0.1" wire:model.live.debounce.400ms="temperatura" min="30" max="45" placeholder="36.5" class="w-full rounded-xl border {{ $errors->has('temperatura') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                        @error('temperatura') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Resp. (rpm)</label>
                        <input type="number" wire:model.live.debounce.400ms="frecuencia_respiratoria" min="5" max="60" placeholder="18" class="w-full rounded-xl border {{ $errors->has('frecuencia_respiratoria') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                        @error('frecuencia_respiratoria') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Observación (Opcional)</label>
                    <textarea wire:model="observacion" rows="2" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]"></textarea>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F8F3ED] active:scale-95">Cancelar</button>
            <button type="submit" form="formTemperatura" class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#D96F58] active:scale-95">Guardar</button>
        </x-slot>
    </x-ui.modal-livewire>

    {{-- MODAL PESO E IMC --}}
    <x-ui.modal-livewire wire:model="modalPeso" title="Peso e IMC" maxWidth="md" closeMethod="cerrarModal">
        <x-slot name="icon"><i class="ph-bold ph-scales text-[#8DA280]"></i></x-slot>
        <form wire:submit.prevent="guardar" id="formPeso">
            <div class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha *</label>
                        <input type="date" wire:model="fecha" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora *</label>
                        <input type="time" wire:model="hora" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Peso (kg)</label>
                        <input type="number" step="0.1" wire:model.live.debounce.400ms="peso" min="20" max="250" placeholder="65" class="w-full rounded-xl border {{ $errors->has('peso') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                        @error('peso') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Talla (m)</label>
                        <input type="number" step="0.01" wire:model.live.debounce.400ms="talla" min="0.5" max="250" placeholder="1.65" class="w-full rounded-xl border {{ $errors->has('talla') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                        @error('talla') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">IMC Calculado</label>
                    <input type="text" wire:model="imc" readonly class="w-full rounded-xl border border-[#C7B5A3]/35 bg-[#D5C7B9]/42 px-3 py-2.5 text-xs font-black text-[#2F3E5C] outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Observación (Opcional)</label>
                    <textarea wire:model="observacion" rows="2" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]"></textarea>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F8F3ED] active:scale-95">Cancelar</button>
            <button type="submit" form="formPeso" class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#D96F58] active:scale-95">Guardar</button>
        </x-slot>
    </x-ui.modal-livewire>

    {{-- MODAL OBSERVACION --}}
    <x-ui.modal-livewire wire:model="modalObservacion" title="Registrar Observación" maxWidth="md" closeMethod="cerrarModal">
        <x-slot name="icon"><i class="ph-bold ph-note-pencil text-[#5B5F97]"></i></x-slot>
        <form wire:submit.prevent="guardar" id="formObservacion">
            <div class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha *</label>
                        <input type="date" wire:model="fecha" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora *</label>
                        <input type="time" wire:model="hora" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Observación Clínica *</label>
                    <textarea wire:model="observacion" rows="4" class="w-full rounded-xl border {{ $errors->has('observacion') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C]"></textarea>
                    @error('observacion') <span class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3]/70 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F8F3ED] active:scale-95">Cancelar</button>
            <button type="submit" form="formObservacion" class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#D96F58] active:scale-95">Guardar</button>
        </x-slot>
    </x-ui.modal-livewire>
</div>

<script>
    window.rmSignosVitalesMainChart = function(root, payload) {
        if (!root || typeof Chart === 'undefined') return;

        if (root.__rmChart && typeof root.__rmChart.destroy === 'function') {
            root.__rmChart.destroy();
        }

        const canvas = root.querySelector('[data-chart="main"]');
        if (!canvas || !payload.main) return;

        root.__rmChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: payload.main.labels || [],
                datasets: (payload.main.datasets || []).map((dataset) => ({
                    label: dataset.label,
                    data: dataset.data || [],
                    borderColor: dataset.color,
                    backgroundColor: dataset.fill ? dataset.color + '22' : 'transparent',
                    tension: 0.32,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    fill: !!dataset.fill,
                    spanGaps: true
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    datalabels: { display: false },
                    legend: {
                        position: 'bottom',
                        labels: { color: '#2F3E5C', boxWidth: 10, font: { size: 11, weight: 'bold' } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(47, 62, 92, 0.92)',
                        titleColor: '#F3ECE4',
                        bodyColor: '#F3ECE4',
                        padding: 12,
                        cornerRadius: 12
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#2F3E5C', font: { size: 10, weight: 'bold' } } },
                    y: {
                        grid: { color: 'rgba(47, 62, 92, 0.08)' },
                        ticks: { color: '#2F3E5C', font: { size: 10, weight: 'bold' } },
                        title: { display: true, text: payload.main.unit || '', color: '#2F3E5C', font: { weight: 'bold' } }
                    }
                }
            }
        });
    };

    (() => {
        const componentId = @js($this->getId());
        window.rmSignosConfirmListeners = window.rmSignosConfirmListeners || {};
        if (window.rmSignosConfirmListeners[componentId]) return;
        window.rmSignosConfirmListeners[componentId] = true;

        window.addEventListener('signos-confirmar-alertas', function(event) {
            const data = event.detail?.[0] || event.detail || {};
            const confirmar = () => {
                const component = window.Livewire && window.Livewire.find(componentId);
                if (component) component.call('confirmarGuardarConAlertas');
            };

            if (window.SwalAmandita) {
                window.SwalAmandita.fire({
                    title: data.titulo || 'Valores fuera del rango referencial',
                    text: data.texto || 'Verifique la información antes de guardar.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar verificado',
                    cancelButtonText: 'Revisar datos'
                }).then(result => {
                    if (result.isConfirmed) confirmar();
                });
            } else if (confirm(data.texto || 'Se detectaron valores fuera de rango. ¿Desea guardar?')) {
                confirmar();
            }
        });

        window.addEventListener('signos-confirmar-anulacion', function(event) {
            const data = event.detail?.[0] || event.detail || {};
            const enviar = (motivo) => {
                const component = window.Livewire && window.Livewire.find(componentId);
                if (component) component.call('anularConMotivo', data.id, motivo);
            };

            if (window.SwalAmandita) {
                window.SwalAmandita.fire({
                    title: 'Anular registro',
                    text: 'Indique el motivo. El registro no será eliminado.',
                    input: 'textarea',
                    inputPlaceholder: 'Motivo de anulación...',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Anular registro',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        if (!value || value.trim().length < 10) return 'El motivo debe tener al menos 10 caracteres.';
                    }
                }).then(result => {
                    if (result.isConfirmed) enviar(result.value);
                });
            } else {
                const motivo = prompt('Motivo de anulación');
                if (motivo) enviar(motivo);
            }
        });
    })();
</script>
