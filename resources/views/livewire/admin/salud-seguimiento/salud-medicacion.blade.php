<div class="space-y-6" x-data>
    <style>
        .salud-hidden-livewire-modals > div > div.rounded-\[24px\] {
            display: none !important;
        }
    </style>

    {{-- 1. CABECERA DEL SUBMÓDULO --}}
    <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-borde/55 bg-fondo-panel text-boton-acento shadow-sm">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </span>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-parrafo">Medicación</h2>
                    <p class="mt-1 text-xs font-bold leading-relaxed text-parrafo/62">
                        Control de medicamentos, prescripciones, horarios y administración del adulto mayor.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @can('salud.medicacion.crear')
                    <button type="button" @click="$dispatch('abrirModalMedicacion', { cod_am: '{{ $adulto ? $adulto->cod_am : '' }}' })" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-5 py-2.5 text-xs font-black uppercase tracking-wider text-inverso shadow-[0_10px_22px_rgba(226,125,96,0.24)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
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
            ['label' => 'Medicaciones Activas', 'valor' => $stats['activas'] ?? 0, 'icon' => 'ph-check-circle', 'color' => 'text-estado-exito', 'bg' => 'bg-estado-exitoBg', 'border' => 'border-estado-exitoBorde'],
            ['label' => 'Suspendidas', 'valor' => $stats['suspendidas'] ?? 0, 'icon' => 'ph-pause-circle', 'color' => 'text-boton-acento', 'bg' => 'bg-estado-peligroBg', 'border' => 'border-borde-focus'],
            ['label' => 'Finalizadas', 'valor' => $stats['finalizadas'] ?? 0, 'icon' => 'ph-check-square-offset', 'color' => 'text-parrafo', 'bg' => 'bg-fondo-panel', 'border' => 'border-borde/45'],
            ['label' => 'Archivadas', 'valor' => $stats['archivadas'] ?? 0, 'icon' => 'ph-archive', 'color' => 'text-parrafo', 'bg' => 'bg-fondo-panel', 'border' => 'border-borde/45'],
        ] as $item)
            <div class="relative overflow-hidden rounded-2xl border {{ $item['border'] }} {{ $item['bg'] }} p-4 shadow-sm backdrop-blur-md transition hover:-translate-y-0.5">
                <i class="ph-bold {{ $item['icon'] }} absolute right-3 top-3 text-2xl text-parrafo/10"></i>
                <p class="pr-7 text-[9px] font-black uppercase tracking-[0.15em] text-parrafo/55">{{ $item['label'] }}</p>
                <p class="mt-2 text-2xl font-black leading-none {{ $item['color'] }}">{{ $item['valor'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- 3. SELECTOR DE ADULTO MAYOR Y PANELES --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <aside class="lg:col-span-1 space-y-6">
            <section class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-apoyo">Seleccionar Adulto Mayor</label>
                <div class="relative">
                    <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                    <select wire:model.live="cod_am" class="w-full rounded-xl border border-borde/70 bg-fondo-panel py-3 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition appearance-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">-- Todos los adultos mayores --</option>
                        @foreach($adultosDisponibles as $ad)
                            <option value="{{ $ad->cod_am }}">{{ $ad->nombres }} {{ $ad->ap_paterno }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            @if($adulto)
                {{-- CARD DEL ADULTO MAYOR --}}
                <section class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-borde-suave pb-4 mb-4">
                        <div class="h-12 w-12 overflow-hidden rounded-xl border-2 border-borde-suave bg-boton-principal shadow-sm">
                            @if($adulto->foto)
                                <img src="{{ Storage::url($adulto->foto) }}" alt="Foto" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-sm font-black text-inverso">
                                    {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-parrafo">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
                            <span class="inline-block mt-0.5 rounded bg-fondo-panel px-2 py-0.5 text-[9px] font-black uppercase text-apoyo">ID: {{ $adulto->cod_am }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3 text-xs text-apoyo">
                        <div class="flex justify-between items-center rounded-lg bg-fondo-panel px-3 py-2 border border-borde-suave">
                            <span class="font-bold">Medicaciones Activas</span>
                            <span class="font-black text-estado-exito">{{ $stats['activas'] }}</span>
                        </div>
                    </div>
                </section>
                
                {{-- 6. PANEL PRÓXIMA TOMA --}}
                <section class="rounded-[1.6rem] border border-borde-focus bg-estado-peligroBg p-5 shadow-sm backdrop-blur-xl relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-estado-peligroBg blur-xl"></div>
                    <h3 class="mb-3 text-[10px] font-black uppercase tracking-widest text-boton-acento flex items-center gap-1.5">
                        <i class="ph-bold ph-clock"></i> Próxima Toma
                    </h3>
                    
                    <div class="text-center py-4">
                        <p class="text-xs font-bold text-apoyo mb-2">No existen tomas programadas para el horario actual.</p>
                        <i class="ph-bold ph-check-circle text-3xl text-boton-acento"></i>
                    </div>
                </section>
            @else
                <section class="rounded-[1.6rem] border border-dashed border-borde/65 bg-fondo-panel p-6 text-center shadow-inner">
                    <i class="ph-bold ph-hand-pointing text-3xl text-parrafo/20"></i>
                    <p class="mt-2 text-xs font-bold text-apoyo">Seleccione un adulto mayor para registrar y administrar medicamentos.</p>
                </section>
            @endif
        </aside>

        <div class="lg:col-span-2 space-y-6">
            {{-- 7. FILTROS --}}
            <section class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-4 shadow-sm backdrop-blur-xl sm:p-5">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-parrafo">Filtros de Búsqueda</h3>
                    </div>
                    @if($search !== '' || $filtroEstado !== '' || $filtroVia !== '')
                        <button wire:click="limpiarFiltros" type="button" class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-boton-acento transition hover:text-parrafo">
                            <i class="ph-bold ph-x-circle"></i>
                            Limpiar filtros
                        </button>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-parrafo/55">Medicamento</label>
                        <div class="relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar medicamento..." class="w-full rounded-xl border border-borde/70 bg-fondo-panel py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition placeholder:text-meta focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        </div>
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-parrafo/55">Estado</label>
                        <div class="relative">
                            <i class="ph-bold ph-funnel absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                            <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-borde/70 bg-fondo-panel py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition appearance-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Todos los estados</option>
                                <option value="ACTIVO">Activos</option>
                                <option value="SUSPENDIDO">Suspendidos</option>
                                <option value="FINALIZADO">Finalizados</option>
                                <option value="ARCHIVADO">Archivados (Histórico)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-parrafo/55">Vía</label>
                        <div class="relative">
                            <i class="ph-bold ph-flask absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                            <select wire:model.live="filtroVia" class="w-full rounded-xl border border-borde/70 bg-fondo-panel py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition appearance-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
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
            <section class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl overflow-hidden relative">
                <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-parrafo">
                        @if($adulto) 
                            Medicación de {{ $adulto->nombres }}
                        @else
                            Listado Global de Medicación
                        @endif
                    </h3>
                    <span class="inline-flex w-max items-center gap-2 rounded-full bg-fondo-panel px-3 py-1 text-[10px] font-black uppercase tracking-wider text-parrafo/65">
                        <i class="ph-bold ph-list-checks"></i>
                        {{ $medicaciones->total() }} registros
                    </span>
                </div>

                @if($medicaciones->isEmpty())
                    <div class="p-10 text-center">
                        <i class="ph-bold ph-pill text-4xl text-parrafo/25"></i>
                        <h3 class="mt-3 text-base font-black text-parrafo">No se encontraron medicamentos</h3>
                        <p class="mx-auto mt-1 max-w-md text-xs font-bold text-parrafo/55">
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
                        <table class="w-full text-left text-sm text-parrafo">
                            <thead class="bg-fondo-panel text-[9px] font-black uppercase tracking-widest text-apoyo border-b border-borde-suave">
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
                            <tbody class="divide-y divide-[#C7B5A3]/30 bg-fondo-card/40">
                                @foreach($medicaciones as $med)
                                    @php
                                        $estado = strtoupper($med->estado ?? 'ACTIVO');
                                        $estadoClases = match($estado) {
                                            'ACTIVO' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
                                            'PAUSADO' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
                                            'SUSPENDIDO' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
                                            'FINALIZADO' => 'bg-fondo-panel text-apoyo border-borde-fuerte',
                                            'ARCHIVADO' => 'bg-fondo-panel text-meta border-borde-suave',
                                            default => 'bg-fondo-panel text-apoyo border-borde/45',
                                        };
                                        $esInactivo = in_array($estado, ['FINALIZADO', 'ARCHIVADO']);
                                    @endphp
                                    <tr class="transition-colors hover:bg-fondo-panel {{ $esInactivo ? 'opacity-70' : '' }}">
                                        @if(!$adulto)
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-parrafo">{{ $med->adultoMayor->nombres ?? 'S/D' }}</p>
                                            <p class="mt-0.5 text-[9px] font-bold text-meta uppercase">{{ $med->adultoMayor->cod_am ?? '' }}</p>
                                        </td>
                                        @endif
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-parrafo">{{ $med->nombre_medicamento }}</p>
                                            @if($med->medico_indica)
                                                <p class="mt-0.5 text-[9px] font-bold text-meta uppercase tracking-wide">
                                                    Dr. {{ $med->medico_indica }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-parrafo">{{ $med->dosis ?: 'S/D' }}</p>
                                            <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-fondo-app px-2 py-0.5 text-[9px] font-bold text-apoyo border border-borde-suave">
                                                {{ $med->via_administracion ?: 'S/D' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-[10px] font-bold text-apoyo">
                                                <i class="ph-bold ph-clock mr-0.5 text-boton-acento"></i> {{ $med->frecuencia ?: 'S/D' }}
                                            </p>
                                            @if($med->hora_programada)
                                            <p class="text-[9px] font-black text-apoyo mt-1">
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
                                                    <button type="button" @click="$dispatch('abrirModalAdministracion', { cod_am: '{{ $med->cod_am }}', cod_med_adulto: {{ $med->cod_med_adulto }} })" class="inline-flex items-center gap-1.5 rounded-lg bg-estado-exitoBg px-2.5 py-1.5 text-[9px] font-black uppercase text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95" title="Registrar toma">
                                                        <i class="ph-bold ph-check-square text-xs"></i>
                                                        Toma
                                                    </button>
                                                @endif

                                                @can('salud.medicacion.editar')
                                                    <button type="button" @click="$dispatch('abrirModalMedicacion', { cod_am: '{{ $med->cod_am }}', id_med: {{ $med->cod_med_adulto }} })" class="inline-flex items-center justify-center rounded-lg border border-borde bg-fondo-panel p-1.5 text-apoyo transition hover:bg-boton-principal hover:text-inverso active:scale-95" title="Editar">
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
                                                                    popup: 'rounded-2xl border border-borde-suave bg-fondo-app',
                                                                    title: 'text-parrafo font-black',
                                                                    htmlContainer: 'text-apoyo',
                                                                    confirmButton: 'rounded-xl text-sm font-black uppercase',
                                                                    cancelButton: 'rounded-xl text-sm font-black uppercase text-parrafo'
                                                                }
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    $el.submit();
                                                                }
                                                            })
                                                        ">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg p-1.5 text-boton-acento transition hover:bg-boton-acento hover:text-inverso active:scale-95" title="Suspender">
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
                                                                    popup: 'rounded-2xl border border-borde-suave bg-fondo-app',
                                                                    title: 'text-parrafo font-black',
                                                                    htmlContainer: 'text-apoyo',
                                                                    confirmButton: 'rounded-xl text-sm font-black uppercase',
                                                                    cancelButton: 'rounded-xl text-sm font-black uppercase text-parrafo'
                                                                }
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    $el.submit();
                                                                }
                                                            })
                                                        ">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-borde-fuerte bg-fondo-panel p-1.5 text-parrafo/80 transition hover:bg-boton-principal hover:text-inverso active:scale-95" title="Finalizar">
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
                        <div class="border-t border-borde-suave bg-fondo-panel px-5 py-4">
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
