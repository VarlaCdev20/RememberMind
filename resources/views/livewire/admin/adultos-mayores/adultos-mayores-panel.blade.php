<div class="relative mx-auto max-w-7xl space-y-4">
    
    {{-- MODAL DE FORMULARIO --}}
    @livewire('admin.adultos-mayores.adulto-mayor-form-modal')

    {{-- HEADER --}}
    <section class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-5 shadow-[0_14px_32px_rgba(47,62,92,0.12)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-widest text-[#E27D60]">
                    Gestión institucional
                </span>
                <h1 class="mt-1 text-2xl font-black text-[#2F3E5C]">Adultos mayores</h1>
                <p class="mt-1 max-w-2xl text-sm font-bold leading-5 text-[#2F3E5C]/60">
                    Panel general administrativo para consultar registros, listados y métricas.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D5C7B9] px-4 py-2.5 text-xs font-black text-[#2F3E5C] transition hover:bg-[#C7B5A3] active:scale-95">
                    <i class="ph-bold ph-arrow-left"></i> Volver
                </a>

                <button type="button" wire:click="crearAdultoMayor" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black text-white shadow-[0_8px_18px_rgba(233,122,95,0.25)] transition hover:-translate-y-0.5 hover:bg-[#D96F58] active:scale-95">
                    <i class="ph-bold ph-plus-circle"></i> Nuevo registro
                </button>

                <a href="{{ route('admin.adultos-mayores.reporte-general') }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2.5 text-xs font-black text-white shadow-[0_8px_18px_rgba(47,62,92,0.15)] transition hover:-translate-y-0.5 hover:bg-[#5B5F97] active:scale-95">
                    <i class="ph-bold ph-file-pdf"></i> Reporte General
                </a>
            </div>
        </div>
    </section>

    {{-- MÉTRICAS --}}
    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Total registrados</p>
            <h3 class="mt-1 text-xl font-black text-[#2F3E5C]">{{ $totales['total'] ?? 0 }}</h3>
        </div>
        <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Activos</p>
            <h3 class="mt-1 text-xl font-black text-[#63775B]">{{ $totales['activos'] ?? 0 }}</h3>
        </div>
        <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Archivados</p>
            <h3 class="mt-1 text-xl font-black text-[#967B66]">{{ $totales['archivados'] ?? 0 }}</h3>
        </div>
        <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Sin seguimiento</p>
            <h3 class="mt-1 text-xl font-black text-[#E27D60]">{{ $totales['sin_seguimiento'] ?? 0 }}</h3>
        </div>
    </section>

    <div x-data="{ tab: 'tarjetas' }" class="space-y-4">
        {{-- NAVEGACIÓN TABS --}}
        <nav class="flex space-x-2 rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-2 shadow-sm overflow-x-auto">
            <button @click="tab = 'tarjetas'" :class="tab === 'tarjetas' ? 'bg-[#2F3E5C] text-white' : 'text-[#2F3E5C] hover:bg-[#D5C7B9]'" class="rounded-xl px-4 py-2 text-xs font-black transition whitespace-nowrap">
                <i class="ph-bold ph-cards mr-1"></i> Vista Tarjetas
            </button>
            <button @click="tab = 'tabla'" :class="tab === 'tabla' ? 'bg-[#2F3E5C] text-white' : 'text-[#2F3E5C] hover:bg-[#D5C7B9]'" class="rounded-xl px-4 py-2 text-xs font-black transition whitespace-nowrap">
                <i class="ph-bold ph-table mr-1"></i> Tabla General
            </button>
            <button @click="tab = 'archivados'" :class="tab === 'archivados' ? 'bg-[#2F3E5C] text-white' : 'text-[#2F3E5C] hover:bg-[#D5C7B9]'" class="rounded-xl px-4 py-2 text-xs font-black transition whitespace-nowrap">
                <i class="ph-bold ph-archive mr-1"></i> Archivados
            </button>
            <button @click="tab = 'alertas'" :class="tab === 'alertas' ? 'bg-[#2F3E5C] text-white' : 'text-[#2F3E5C] hover:bg-[#D5C7B9]'" class="rounded-xl px-4 py-2 text-xs font-black transition whitespace-nowrap">
                <i class="ph-bold ph-warning mr-1"></i> Alertas Administrativas
            </button>
        </nav>

        {{-- FILTROS FUNCIONALES --}}
        <section class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm" x-show="tab !== 'alertas'">
            <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-8 items-end">
                <div class="md:col-span-2 xl:col-span-2">
                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Buscar</label>
                    <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Ficha, CI, Nombres..." class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-3 py-2 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/20">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Estado</label>
                    <select wire:model.live="estado" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-3 py-2 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/20">
                        <option value="">Todos</option>
                        @foreach($estadosAdulto ?? [] as $est)
                            <option value="{{ $est->estado }}">{{ $est->estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Género</label>
                    <select wire:model.live="genero" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-3 py-2 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/20">
                        <option value="">Todos</option>
                        <option value="MASCULINO">Masculino</option>
                        <option value="FEMENINO">Femenino</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Permanencia</label>
                    <select wire:model.live="permanencia" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-3 py-2 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/20">
                        <option value="">Todas</option>
                        <option value="PERMANENTE">Permanente</option>
                        <option value="TEMPORAL">Temporal</option>
                        <option value="EVENTUAL">Eventual</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Desde (Ingreso)</label>
                    <input type="date" wire:model.live="fecha_desde" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-3 py-2 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/20">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Hasta</label>
                    <input type="date" wire:model.live="fecha_hasta" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-3 py-2 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/20">
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="$refresh" class="w-full rounded-xl bg-[#2F3E5C] px-3 py-2 text-xs font-black text-white hover:bg-[#5B5F97] transition">
                        <i class="ph-bold ph-arrows-counter-clockwise"></i>
                    </button>
                    <button type="button" wire:click="$set('buscar', ''); $set('estado', ''); $set('genero', ''); $set('permanencia', ''); $set('fecha_desde', ''); $set('fecha_hasta', '');" class="flex w-full items-center justify-center rounded-xl bg-[#D5C7B9] px-3 py-2 text-xs font-black text-[#2F3E5C] hover:bg-[#C7B5A3] transition">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>
            </div>
        </section>

        {{-- VISTA TARJETAS --}}
        <section x-show="tab === 'tarjetas'">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @php $hayTarjetasActivas = false; @endphp
                @foreach($adultos as $adulto)
                    @php
                        $estado = strtoupper($adulto->estado_adulto);
                        $esArchivado = $estado === 'ARCHIVADO' || $estado === 'INACTIVO';
                        $colorBg = $esArchivado ? 'bg-[#E6DDD3]/50 border-[#C7B5A3]/50' : 'bg-[#E6DDD3]/90 border-[#C7B5A3]';
                        $colorEstado = $esArchivado ? 'bg-[#967B66]/20 text-[#7A604B]' : 'bg-[#8DA280]/20 text-[#5F7357]';
                        $fotoUrl = $adulto->foto ? Storage::url($adulto->foto) : null;
                    @endphp
                    @if(!$esArchivado)
                        @php $hayTarjetasActivas = true; @endphp
                        <div class="rounded-2xl border {{ $colorBg }} p-0 shadow-sm transition hover:-translate-y-1 hover:shadow-md flex flex-col justify-between overflow-hidden relative">
                            {{-- Línea superior de acento (Tipo ficha) --}}
                            <div class="h-1.5 w-full {{ $esArchivado ? 'bg-[#967B66]/50' : 'bg-[#E27D60]' }}"></div>
                            
                            <div class="p-5 flex flex-col items-center border-b border-[#C7B5A3]/30 bg-gradient-to-b from-white/40 to-transparent">
                                <span class="absolute top-4 right-4 rounded-lg {{ $colorEstado }} px-2 py-1 text-[9px] font-black uppercase tracking-wider">{{ $estado }}</span>
                                <span class="absolute top-4 left-4 text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 bg-white/50 px-2 py-1 rounded-md backdrop-blur-sm shadow-sm border border-white/50"><i class="ph-bold ph-hash mr-0.5"></i>{{ $adulto->cod_am }}</span>
                                
                                @if($fotoUrl)
                                    <img src="{{ $fotoUrl }}" class="mt-4 mb-3 h-28 w-28 rounded-[24px] object-cover border-4 border-white shadow-sm">
                                @else
                                    <div class="mt-4 mb-3 h-28 w-28 rounded-[24px] bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] flex items-center justify-center text-white font-black text-3xl shadow-sm border-4 border-white">
                                        {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
                                    </div>
                                @endif
                                
                                <h3 class="text-[17px] font-black text-[#2F3E5C] text-center leading-tight">
                                    {{ $adulto->nombres }}<br>
                                    <span class="text-sm text-[#2F3E5C]/70">{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</span>
                                </h3>
                            </div>
                            
                            <div class="p-4 px-5">
                                <div class="space-y-2.5">
                                    <div class="flex justify-between items-center text-xs border-b border-[#C7B5A3]/30 pb-2">
                                        <span class="font-bold text-[#2F3E5C]/60 flex items-center"><i class="ph-bold ph-identification-card mr-1.5"></i> C.I.</span>
                                        <span class="font-black text-[#2F3E5C]">{{ $adulto->ci }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs border-b border-[#C7B5A3]/30 pb-2">
                                        <span class="font-bold text-[#2F3E5C]/60 flex items-center"><i class="ph-bold ph-calendar-blank mr-1.5"></i> Edad</span>
                                        <span class="font-black text-[#2F3E5C]">{{ $adulto->edad }} años</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs pb-1">
                                        <span class="font-bold text-[#2F3E5C]/60 flex items-center"><i class="ph-bold ph-sign-in mr-1.5"></i> Ingreso</span>
                                        <span class="font-black text-[#2F3E5C]">{{ optional($adulto->fecha_ing)->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 pt-1 mt-auto">
                                <div class="flex gap-1.5">
                                    <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_am) }}" class="flex-1 flex justify-center items-center rounded-xl bg-[#2F3E5C] p-2 text-white hover:bg-[#5B5F97] transition" title="Ver ficha">
                                        <i class="ph-bold ph-eye text-[13px] mr-1"></i> <span class="text-[9px] font-bold uppercase tracking-wider">Ver</span>
                                    </a>
                                    <button type="button" wire:click="editarAdultoMayor('{{ $adulto->cod_am }}')" class="flex-1 flex justify-center items-center rounded-xl bg-[#E27D60] p-2 text-white hover:bg-[#D96F58] transition" title="Editar">
                                        <i class="ph-bold ph-pencil-simple text-[13px] mr-1"></i> <span class="text-[9px] font-bold uppercase tracking-wider">Editar</span>
                                    </button>
                                    <div x-data="{ open: false }" class="relative flex-1">
                                        <button @click="open = !open" @click.away="open = false" type="button" class="flex w-full h-full justify-center items-center rounded-xl bg-[#C7B5A3] p-2 text-[#2F3E5C] hover:bg-[#B5A391] transition" title="Cambiar Estado">
                                            <i class="ph-bold ph-arrows-left-right text-[13px] mr-1"></i> <span class="text-[9px] font-bold uppercase tracking-wider">Estado</span>
                                        </button>
                                        <div x-cloak x-show="open" class="absolute bottom-full mb-2 right-0 bg-white rounded-xl shadow-lg border border-[#C7B5A3] p-1.5 min-w-[120px] z-50">
                                            <p class="text-[9px] font-black uppercase text-[#2F3E5C]/50 px-2 py-1">Cambiar a:</p>
                                            @foreach($estadosAdulto as $est)
                                                @if(strtoupper($est->estado) !== $estado)
                                                <form method="POST" action="{{ route('admin.adultos-mayores.estado', $adulto->cod_am) }}" onsubmit="confirmarAccion(event, '¿Cambiar estado a {{ $est->estado }}?', 'Se registrará en el historial clínico y bitácora.')">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="cod_est_adul" value="{{ $est->cod_est_adul }}">
                                                    <button type="submit" class="w-full text-left px-2 py-1.5 text-[10px] font-bold text-[#2F3E5C] hover:bg-[#E6DDD3] rounded-lg transition">{{ $est->estado }}</button>
                                                </form>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$hayTarjetasActivas)
                    <div class="col-span-full rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/50 p-8 text-center">
                        <p class="text-sm font-bold text-[#2F3E5C]/60">No se encontraron registros activos.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- TABLA GENERAL --}}
        <section x-show="tab === 'tabla'" class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 shadow-sm overflow-hidden" style="display: none;">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/50 text-[10px] uppercase tracking-widest text-[#2F3E5C]/70">
                        <tr>
                            <th class="px-4 py-3">Ficha</th>
                            <th class="px-4 py-3">Nombre Completo</th>
                            <th class="px-4 py-3">CI</th>
                            <th class="px-4 py-3">Edad</th>
                            <th class="px-4 py-3">Ingreso</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Últ. Act.</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C7B5A3]/50">
                        @foreach($adultos as $adulto)
                            <tr class="hover:bg-[#F2EBE3]/50 transition">
                                <td class="px-4 py-3 font-black">{{ $adulto->cod_am }}</td>
                                <td class="px-4 py-3 font-bold">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</td>
                                <td class="px-4 py-3">{{ $adulto->ci }}</td>
                                <td class="px-4 py-3">{{ $adulto->edad }}</td>
                                <td class="px-4 py-3">{{ optional($adulto->fecha_ing)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-lg px-2 py-1 text-[9px] font-black uppercase {{ strtoupper($adulto->estado_adulto) === 'ACTIVO' ? 'bg-[#8DA280]/20 text-[#5F7357]' : 'bg-[#967B66]/20 text-[#7A604B]' }}">
                                        {{ $adulto->estado_adulto }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-[10px] font-bold">{{ $adulto->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_am) }}" class="rounded bg-[#2F3E5C] p-1.5 text-white hover:bg-[#5B5F97]">
                                            <i class="ph-bold ph-eye"></i>
                                        </a>
                                        @if(strtoupper($adulto->estado_adulto) !== 'ARCHIVADO' && strtoupper($adulto->estado_adulto) !== 'INACTIVO')
                                            <button type="button" wire:click="editarAdultoMayor('{{ $adulto->cod_am }}')" class="rounded bg-[#E27D60] p-1.5 text-white hover:bg-[#D96F58]">
                                                <i class="ph-bold ph-pencil-simple"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.adultos-mayores.archivar', $adulto->cod_am) }}" onsubmit="confirmarAccion(event, '¿Archivar adulto mayor?', 'Se archivará la ficha del paciente.')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="rounded bg-[#9A7B60] p-1.5 text-white hover:bg-[#7A604B]" title="Archivar ficha"><i class="ph-bold ph-archive"></i></button>
                                            </form>
                                        @else
                                            <button disabled class="rounded bg-[#D5C7B9]/50 p-1.5 text-[#2F3E5C]/30 cursor-not-allowed">
                                                <i class="ph-bold ph-pencil-simple"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.adultos-mayores.restaurar', $adulto->cod_am) }}" onsubmit="confirmarAccion(event, '¿Restaurar adulto mayor?', 'La ficha volverá a estar activa.')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="rounded bg-[#8DA280] p-1.5 text-white hover:bg-[#6F8566]"><i class="ph-bold ph-arrow-counter-clockwise"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if(count($adultos) === 0)
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm font-bold text-[#2F3E5C]/60">No se encontraron registros.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ARCHIVADOS --}}
        <section x-show="tab === 'archivados'" style="display: none;">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @php $hayArchivados = false; @endphp
                @foreach($adultos as $adulto)
                    @if(strtoupper($adulto->estado_adulto) === 'ARCHIVADO' || strtoupper($adulto->estado_adulto) === 'INACTIVO')
                        @php $hayArchivados = true; @endphp
                        <div class="rounded-2xl border border-[#C7B5A3]/50 bg-[#E6DDD3]/50 p-4 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="rounded-lg bg-[#967B66]/20 px-2 py-1 text-[9px] font-black uppercase text-[#7A604B]">{{ $adulto->estado_adulto }}</span>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">{{ $adulto->cod_am }}</p>
                                <h3 class="text-base font-black text-[#2F3E5C]/70 leading-tight">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
                                <p class="mt-2 text-xs font-bold text-[#2F3E5C]/50"><i class="ph-fill ph-identification-card mr-1"></i> {{ $adulto->ci }}</p>
                            </div>
                            <div class="p-4 pt-1 mt-4">
                                <div class="flex gap-1.5">
                                    <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_am) }}" class="flex-1 flex justify-center items-center rounded-xl bg-[#2F3E5C] p-2 text-white hover:bg-[#5B5F97] transition" title="Ver ficha">
                                        <i class="ph-bold ph-eye text-[13px] mr-1"></i> <span class="text-[9px] font-bold uppercase tracking-wider">Ver</span>
                                    </a>
                                    <div x-data="{ open: false }" class="relative flex-1">
                                        <button @click="open = !open" @click.away="open = false" type="button" class="flex w-full h-full justify-center items-center rounded-xl bg-[#8DA280] p-2 text-white hover:bg-[#6F8566] transition" title="Restaurar / Cambiar Estado">
                                            <i class="ph-bold ph-arrow-counter-clockwise text-[13px] mr-1"></i> <span class="text-[9px] font-bold uppercase tracking-wider">Restaurar</span>
                                        </button>
                                        <div x-cloak x-show="open" class="absolute bottom-full mb-2 right-0 bg-white rounded-xl shadow-lg border border-[#C7B5A3] p-1.5 min-w-[120px] z-50">
                                            <p class="text-[9px] font-black uppercase text-[#2F3E5C]/50 px-2 py-1">Cambiar a:</p>
                                            @foreach($estadosAdulto as $est)
                                                @if(strtoupper($est->estado) !== strtoupper($adulto->estado_adulto))
                                                <form method="POST" action="{{ route('admin.adultos-mayores.estado', $adulto->cod_am) }}" onsubmit="confirmarAccion(event, '¿Cambiar estado a {{ $est->estado }}?', 'Se registrará en el historial.')">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="cod_est_adul" value="{{ $est->cod_est_adul }}">
                                                    <button type="submit" class="w-full text-left px-2 py-1.5 text-[10px] font-bold text-[#2F3E5C] hover:bg-[#E6DDD3] rounded-lg transition">{{ $est->estado }}</button>
                                                </form>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$hayArchivados)
                    <div class="col-span-full rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/50 p-8 text-center">
                        <p class="text-sm font-bold text-[#2F3E5C]/60">No hay registros archivados.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ALERTAS ADMINISTRATIVAS --}}
        <section x-show="tab === 'alertas'" style="display: none;">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @php $hayAlertas = false; @endphp
                @foreach($adultos as $adulto)
                    @php
                        $alertas = [];
                        if(!$adulto->foto) $alertas[] = "Falta fotografía en perfil";
                        if($adulto->fam_total == 0) $alertas[] = "No tiene familiares registrados";
                        if($adulto->obs_total == 0) $alertas[] = "No se le han realizado observaciones";
                    @endphp
                    @if(count($alertas) > 0 && strtoupper($adulto->estado_adulto) !== 'ARCHIVADO')
                        @php $hayAlertas = true; @endphp
                        <div class="rounded-2xl border border-[#E27D60]/30 bg-[#E27D60]/5 p-4 shadow-sm flex flex-col justify-between transition hover:-translate-y-0.5">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-base font-black text-[#2F3E5C] leading-tight">{{ $adulto->cod_am }}<br><span class="text-xs">{{ $adulto->nombres }}</span></h3>
                                    <span class="rounded-full bg-[#E27D60]/10 p-1.5 text-[#E27D60]"><i class="ph-bold ph-warning"></i></span>
                                </div>
                                <ul class="mt-3 space-y-2">
                                    @foreach($alertas as $alerta)
                                        <li class="text-[11px] font-bold text-[#E27D60] flex items-center"><i class="ph-fill ph-circle text-[6px] mr-2"></i> {{ $alerta }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_am) }}" class="mt-4 flex justify-center rounded-xl bg-[#2F3E5C] p-2 text-white hover:bg-[#5B5F97] transition text-xs font-bold w-full">
                                Revisar ficha
                            </a>
                        </div>
                    @endif
                @endforeach
                @if(!$hayAlertas)
                    <div class="col-span-full rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-8 text-center">
                        <i class="ph-bold ph-check-circle text-4xl text-[#8DA280] mb-2"></i>
                        <h3 class="text-lg font-black text-[#2F3E5C]">Todo en orden</h3>
                        <p class="text-sm font-bold text-[#2F3E5C]/60">No se detectaron alertas administrativas.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- PAGINACIÓN --}}
        <div class="mt-6 flex justify-center">
            {{ $adultos->links() }}
        </div>
    </div>
</div>
