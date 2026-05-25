<div class="space-y-6" x-data>
    <style>
        .salud-hidden-livewire-modals > div > div.rounded-\[24px\] {
            display: none !important;
        }
    </style>

    {{-- 1. CABECERA DEL SUBMÓDULO --}}
    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/70 text-[#E27D60] shadow-sm">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </span>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-[#2F3E5C]">Medicación</h2>
                    <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/62">
                        Control de medicamentos, prescripciones, horarios y administración del adulto mayor.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @can('salud.medicacion.crear')
                    <button type="button" @click="$dispatch('abrirModalMedicacion', { cod_am: '{{ $adulto ? $adulto->cod_am : '' }}' })" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-[0_10px_22px_rgba(226,125,96,0.24)] transition hover:-translate-y-0.5 hover:bg-[#D96F58] active:scale-95">
                        <i class="ph-bold ph-plus-circle text-sm"></i>
                        Registrar Medicación
                    </button>
                @endcan
            </div>
        </div>
    </section>

    {{-- 2. RESUMEN GENERAL DE MEDICACIÓN --}}
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach([
            ['label' => 'Medicaciones Activas', 'valor' => $stats['activas'] ?? 0, 'icon' => 'ph-check-circle', 'color' => 'text-[#63775B]', 'bg' => 'bg-[#8DA280]/14', 'border' => 'border-[#8DA280]/30'],
            ['label' => 'Suspendidas', 'valor' => $stats['suspendidas'] ?? 0, 'icon' => 'ph-pause-circle', 'color' => 'text-[#E27D60]', 'bg' => 'bg-[#E27D60]/10', 'border' => 'border-[#E27D60]/25'],
            ['label' => 'Finalizadas', 'valor' => $stats['finalizadas'] ?? 0, 'icon' => 'ph-check-square-offset', 'color' => 'text-[#2F3E5C]', 'bg' => 'bg-[#E6DDD3]/62', 'border' => 'border-[#C7B5A3]/45'],
            ['label' => 'Archivadas', 'valor' => $stats['archivadas'] ?? 0, 'icon' => 'ph-archive', 'color' => 'text-[#9A7B60]', 'bg' => 'bg-[#D5C7B9]/55', 'border' => 'border-[#C7B5A3]/45'],
        ] as $item)
            <div class="relative overflow-hidden rounded-2xl border {{ $item['border'] }} {{ $item['bg'] }} p-4 shadow-sm backdrop-blur-md transition hover:-translate-y-0.5">
                <i class="ph-bold {{ $item['icon'] }} absolute right-3 top-3 text-2xl text-[#2F3E5C]/10"></i>
                <p class="pr-7 text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/55">{{ $item['label'] }}</p>
                <p class="mt-2 text-2xl font-black leading-none {{ $item['color'] }}">{{ $item['valor'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- 3. SELECTOR DE ADULTO MAYOR Y PANELES --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <aside class="lg:col-span-1 space-y-6">
            <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Seleccionar Adulto Mayor</label>
                <div class="relative">
                    <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                    <select wire:model.live="cod_am" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 py-3 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition appearance-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">-- Todos los adultos mayores --</option>
                        @foreach($adultosDisponibles as $ad)
                            <option value="{{ $ad->cod_am }}">{{ $ad->nombres }} {{ $ad->ap_paterno }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            @if($adulto)
                {{-- CARD DEL ADULTO MAYOR --}}
                <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-[#C7B5A3]/30 pb-4 mb-4">
                        <div class="h-12 w-12 overflow-hidden rounded-xl border-2 border-[#E6DDD3] bg-[#2F3E5C] shadow-sm">
                            @if($adulto->foto)
                                <img src="{{ Storage::url($adulto->foto) }}" alt="Foto" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-sm font-black text-white">
                                    {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-[#2F3E5C]">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
                            <span class="inline-block mt-0.5 rounded bg-[#D5C7B9]/70 px-2 py-0.5 text-[9px] font-black uppercase text-[#2F3E5C]/60">ID: {{ $adulto->cod_am }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3 text-xs text-[#2F3E5C]/70">
                        <div class="flex justify-between items-center rounded-lg bg-[#E6DDD3]/50 px-3 py-2 border border-[#C7B5A3]/30">
                            <span class="font-bold">Medicaciones Activas</span>
                            <span class="font-black text-[#63775B]">{{ $stats['activas'] }}</span>
                        </div>
                    </div>
                </section>
                
                {{-- 6. PANEL PRÓXIMA TOMA --}}
                <section class="rounded-[1.6rem] border border-[#E27D60]/30 bg-[#E27D60]/5 p-5 shadow-sm backdrop-blur-xl relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-[#E27D60]/10 blur-xl"></div>
                    <h3 class="mb-3 text-[10px] font-black uppercase tracking-widest text-[#E27D60] flex items-center gap-1.5">
                        <i class="ph-bold ph-clock"></i> Próxima Toma
                    </h3>
                    
                    <div class="text-center py-4">
                        <p class="text-xs font-bold text-[#2F3E5C]/60 mb-2">No existen tomas programadas para el horario actual.</p>
                        <i class="ph-bold ph-check-circle text-3xl text-[#E27D60]/20"></i>
                    </div>
                </section>
            @else
                <section class="rounded-[1.6rem] border border-dashed border-[#C7B5A3]/65 bg-[#F3ECE4]/40 p-6 text-center shadow-inner">
                    <i class="ph-bold ph-hand-pointing text-3xl text-[#2F3E5C]/20"></i>
                    <p class="mt-2 text-xs font-bold text-[#2F3E5C]/60">Seleccione un adulto mayor para registrar y administrar medicamentos.</p>
                </section>
            @endif
        </aside>

        <div class="lg:col-span-2 space-y-6">
            {{-- 7. FILTROS --}}
            <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-4 shadow-sm backdrop-blur-xl sm:p-5">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Filtros de Búsqueda</h3>
                    </div>
                    @if($search !== '' || $filtroEstado !== '' || $filtroVia !== '')
                        <button wire:click="limpiarFiltros" type="button" class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-[#E27D60] transition hover:text-[#D96F58]">
                            <i class="ph-bold ph-x-circle"></i>
                            Limpiar filtros
                        </button>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Medicamento</label>
                        <div class="relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar medicamento..." class="w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 py-2.5 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition placeholder:text-[#2F3E5C]/40 focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        </div>
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Estado</label>
                        <div class="relative">
                            <i class="ph-bold ph-funnel absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                            <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 py-2.5 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition appearance-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Todos los estados</option>
                                <option value="ACTIVO">Activos</option>
                                <option value="SUSPENDIDO">Suspendidos</option>
                                <option value="FINALIZADO">Finalizados</option>
                                <option value="ARCHIVADO">Archivados (Histórico)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Vía</label>
                        <div class="relative">
                            <i class="ph-bold ph-flask absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                            <select wire:model.live="filtroVia" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 py-2.5 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition appearance-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Todas las vías</option>
                                @foreach($viasDisponibles as $via)
                                    <option value="{{ $via }}">{{ $via }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 8. TABLA DE MEDICACIONES --}}
            <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 shadow-sm backdrop-blur-xl overflow-hidden relative">
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/30 bg-[#E6DDD3]/50 px-5 py-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">
                        @if($adulto) 
                            Medicación de {{ $adulto->nombres }}
                        @else
                            Listado Global de Medicación
                        @endif
                    </h3>
                    <span class="inline-flex w-max items-center gap-2 rounded-full bg-[#2F3E5C]/8 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/65">
                        <i class="ph-bold ph-list-checks"></i>
                        {{ $medicaciones->total() }} registros
                    </span>
                </div>

                @if($medicaciones->isEmpty())
                    <div class="p-10 text-center">
                        <i class="ph-bold ph-pill text-4xl text-[#2F3E5C]/25"></i>
                        <h3 class="mt-3 text-base font-black text-[#2F3E5C]">No se encontraron medicamentos</h3>
                        <p class="mx-auto mt-1 max-w-md text-xs font-bold text-[#2F3E5C]/55">
                            @if($search !== '' || $filtroEstado !== '' || $filtroVia !== '')
                                No hay resultados que coincidan con los filtros aplicados.
                            @elseif($adulto)
                                Registre la primera prescripción para este adulto mayor.
                            @else
                                No hay medicación activa en el sistema.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-[#2F3E5C]">
                            <thead class="bg-[#D5C7B9]/40 text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60 border-b border-[#C7B5A3]/40">
                                <tr>
                                    @if(!$adulto)
                                    <th class="px-5 py-4">Adulto Mayor</th>
                                    @endif
                                    <th class="px-5 py-4">Medicamento</th>
                                    <th class="px-5 py-4">Dosis / Vía</th>
                                    <th class="px-5 py-4">Frecuencia</th>
                                    <th class="px-5 py-4">Estado</th>
                                    <th class="px-5 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/30 bg-white/40">
                                @foreach($medicaciones as $med)
                                    @php
                                        $estado = strtoupper($med->estado ?? 'ACTIVO');
                                        $estadoClases = match($estado) {
                                            'ACTIVO' => 'bg-[#8DA280]/18 text-[#63775B] border-[#8DA280]/30',
                                            'PAUSADO' => 'bg-[#D9A05B]/18 text-[#D9A05B] border-[#D9A05B]/30',
                                            'SUSPENDIDO' => 'bg-[#E27D60]/12 text-[#E27D60] border-[#E27D60]/25',
                                            'FINALIZADO' => 'bg-[#2F3E5C]/8 text-[#2F3E5C]/70 border-[#2F3E5C]/15',
                                            'ARCHIVADO' => 'bg-[#C7B5A3]/20 text-[#2F3E5C]/50 border-[#C7B5A3]/30',
                                            default => 'bg-[#D5C7B9]/70 text-[#2F3E5C]/60 border-[#C7B5A3]/45',
                                        };
                                        $esInactivo = in_array($estado, ['FINALIZADO', 'ARCHIVADO']);
                                    @endphp
                                    <tr class="transition-colors hover:bg-[#F8F3ED]/70 {{ $esInactivo ? 'opacity-70' : '' }}">
                                        @if(!$adulto)
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-[#2F3E5C]">{{ $med->adultoMayor->nombres ?? 'S/D' }}</p>
                                            <p class="mt-0.5 text-[9px] font-bold text-[#2F3E5C]/50 uppercase">{{ $med->adultoMayor->cod_am ?? '' }}</p>
                                        </td>
                                        @endif
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-[#2F3E5C]">{{ $med->nombre_medicamento }}</p>
                                            @if($med->medico_indica)
                                                <p class="mt-0.5 text-[9px] font-bold text-[#2F3E5C]/50 uppercase tracking-wide">
                                                    Dr. {{ $med->medico_indica }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-[#2F3E5C]">{{ $med->dosis ?: 'S/D' }}</p>
                                            <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-[#F3ECE4] px-2 py-0.5 text-[9px] font-bold text-[#2F3E5C]/70 border border-[#C7B5A3]/40">
                                                {{ $med->via_administracion ?: 'S/D' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-[10px] font-bold text-[#2F3E5C]/70">
                                                <i class="ph-bold ph-clock mr-0.5 text-[#E27D60]"></i> {{ $med->frecuencia ?: 'S/D' }}
                                            </p>
                                            @if($med->hora_programada)
                                            <p class="text-[9px] font-black text-[#2F3E5C]/60 mt-1">
                                                Hora: {{ \Carbon\Carbon::parse($med->hora_programada)->format('H:i') }}
                                            </p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider inline-flex items-center gap-1 {{ $estadoClases }}">
                                                @if($estado === 'ACTIVO') <i class="ph-bold ph-check-circle"></i>
                                                @elseif($estado === 'PAUSADO') <i class="ph-bold ph-warning"></i>
                                                @elseif($estado === 'SUSPENDIDO') <i class="ph-bold ph-pause-circle"></i>
                                                @elseif($estado === 'FINALIZADO') <i class="ph-bold ph-check-square-offset"></i>
                                                @elseif($estado === 'ARCHIVADO') <i class="ph-bold ph-archive"></i>
                                                @endif
                                                {{ $estado }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($estado === 'ACTIVO')
                                                    <button type="button" @click="$dispatch('abrirModalAdministracion', { cod_am: '{{ $med->cod_am }}', cod_med_adulto: {{ $med->cod_med_adulto }} })" class="inline-flex items-center gap-1.5 rounded-lg bg-[#8DA280] px-2.5 py-1.5 text-[9px] font-black uppercase text-white shadow-sm transition hover:bg-[#63775B] active:scale-95" title="Registrar toma">
                                                        <i class="ph-bold ph-check-square text-xs"></i>
                                                        Toma
                                                    </button>
                                                @endif

                                                @can('salud.medicacion.editar')
                                                    <button type="button" @click="$dispatch('abrirModalMedicacion', { cod_am: '{{ $med->cod_am }}', id_med: {{ $med->cod_med_adulto }} })" class="inline-flex items-center justify-center rounded-lg border border-[#C7B5A3]/60 bg-[#E6DDD3]/70 p-1.5 text-[#2F3E5C]/70 transition hover:bg-[#2F3E5C] hover:text-white active:scale-95" title="Editar">
                                                        <i class="ph-bold ph-pencil-simple"></i>
                                                    </button>
                                                @endcan

                                                @if($estado === 'ACTIVO')
                                                    <form method="POST" action="{{ route('admin.adultos-mayores.medicacion.suspender', ['adulto_mayor' => $med->cod_am, 'medicacion' => $med->cod_med_adulto]) }}" class="inline-block"
                                                        @submit.prevent="
                                                            Swal.fire({
                                                                title: '¿Suspender medicamento?',
                                                                text: 'El medicamento quedará visible en el historial pero no se registrarán más tomas.',
                                                                icon: 'warning',
                                                                showCancelButton: true,
                                                                confirmButtonColor: '#E27D60',
                                                                cancelButtonColor: '#C7B5A3',
                                                                confirmButtonText: 'Sí, suspender',
                                                                cancelButtonText: 'Cancelar',
                                                                customClass: {
                                                                    popup: 'rounded-2xl border border-[#C7B5A3]/50 bg-[#F8F3ED]',
                                                                    title: 'text-[#2F3E5C] font-black',
                                                                    htmlContainer: 'text-[#2F3E5C]/70',
                                                                    confirmButton: 'rounded-xl text-sm font-black uppercase',
                                                                    cancelButton: 'rounded-xl text-sm font-black uppercase text-[#2F3E5C]'
                                                                }
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    $el.submit();
                                                                }
                                                            })
                                                        ">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-[#E27D60]/30 bg-[#E27D60]/10 p-1.5 text-[#E27D60] transition hover:bg-[#E27D60] hover:text-white active:scale-95" title="Suspender">
                                                            <i class="ph-bold ph-pause-circle"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.adultos-mayores.medicacion.finalizar', ['adulto_mayor' => $med->cod_am, 'medicacion' => $med->cod_med_adulto]) }}" class="inline-block"
                                                        @submit.prevent="
                                                            Swal.fire({
                                                                title: '¿Finalizar medicamento?',
                                                                text: 'El tratamiento se marcará como concluido exitosamente.',
                                                                icon: 'info',
                                                                showCancelButton: true,
                                                                confirmButtonColor: '#2F3E5C',
                                                                cancelButtonColor: '#C7B5A3',
                                                                confirmButtonText: 'Sí, finalizar',
                                                                cancelButtonText: 'Cancelar',
                                                                customClass: {
                                                                    popup: 'rounded-2xl border border-[#C7B5A3]/50 bg-[#F8F3ED]',
                                                                    title: 'text-[#2F3E5C] font-black',
                                                                    htmlContainer: 'text-[#2F3E5C]/70',
                                                                    confirmButton: 'rounded-xl text-sm font-black uppercase',
                                                                    cancelButton: 'rounded-xl text-sm font-black uppercase text-[#2F3E5C]'
                                                                }
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    $el.submit();
                                                                }
                                                            })
                                                        ">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-[#2F3E5C]/20 bg-[#2F3E5C]/10 p-1.5 text-[#2F3E5C]/80 transition hover:bg-[#2F3E5C] hover:text-white active:scale-95" title="Finalizar">
                                                            <i class="ph-bold ph-check-square-offset"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($medicaciones->hasPages())
                        <div class="border-t border-[#C7B5A3]/30 bg-[#E6DDD3]/30 px-5 py-4">
                            {{ $medicaciones->links() }}
                        </div>
                    @endif
                @endif
            </section>
        </div>
    </div>

    <div class="salud-hidden-livewire-modals">
        @if($adulto)
            <livewire:admin.adultos-mayores.salud.medicacion-adulto-modal :cod_am="$adulto->cod_am" :key="'med-modal-'.$adulto->cod_am" />
            <livewire:admin.adultos-mayores.salud.administracion-medicacion-modal :cod_am="$adulto->cod_am" :key="'admin-modal-'.$adulto->cod_am" />
        @else
            {{-- We still need the modals mounted with an empty ID in case someone clicks edit globally --}}
            <livewire:admin.adultos-mayores.salud.medicacion-adulto-modal cod_am="" />
            <livewire:admin.adultos-mayores.salud.administracion-medicacion-modal cod_am="" />
        @endif
    </div>
</div>
