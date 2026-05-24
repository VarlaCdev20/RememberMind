<div class="min-h-screen py-8 font-sans antialiased text-[#2F3E5C]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- ENCABEZADO --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-[#C7B5A3]/50 pb-6 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm border border-[#C7B5A3]/40">
                    <i class="ph-bold {{ $contexto['icono'] }} text-2xl text-terracota"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight text-azul-profundo sm:text-4xl">
                        {{ $contexto['titulo'] }}
                    </h1>
                    <p class="mt-1 text-sm font-bold text-azul-profundo/60">
                        {{ $contexto['descripcion'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- BARRA DE HERRAMIENTAS --}}
        <div class="mb-8 flex items-center justify-between gap-4 rounded-3xl border border-[#C7B5A3]/40 bg-white p-4 shadow-sm">
            <div class="relative w-full max-w-md">
                <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-terracota"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar paciente por nombre, apellido o código..."
                    class="w-full rounded-2xl border border-[#C7B5A3]/40 bg-[#F7F5F2] py-3 pl-11 pr-4 text-sm font-bold text-azul-profundo shadow-inner transition-all placeholder:text-[#2F3E5C]/40 focus:border-terracota focus:outline-none focus:ring-1 focus:ring-terracota"
                >
            </div>
        </div>

        {{-- LISTADO EN GRID --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($adultos as $adulto)
                <div class="group relative flex flex-col overflow-hidden rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-terracota/10">
                    {{-- Banner superior --}}
                    <div class="h-24 w-full bg-gradient-to-br from-[#E6DDD3] to-[#D5C7B9] relative overflow-hidden">
                        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#2F3E5C_1px,transparent_1px)] [background-size:16px_16px]"></div>
                        <div class="absolute top-3 right-3">
                            <span class="rounded-full bg-white/80 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-azul-profundo backdrop-blur-sm shadow-sm">
                                {{ $adulto->estado->estado ?? 'Desconocido' }}
                            </span>
                        </div>
                    </div>

                    {{-- Foto del adulto mayor --}}
                    <div class="absolute left-1/2 top-8 -translate-x-1/2">
                        <div class="h-24 w-24 overflow-hidden rounded-full border-4 border-white bg-white shadow-md">
                            @if($adulto->foto)
                                <img src="{{ Storage::url($adulto->foto) }}" alt="{{ $adulto->nombres }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#E6DDD3] to-[#C7B5A3]">
                                    <span class="text-3xl font-black text-azul-profundo/40">{{ substr($adulto->nombres, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Contenido del card --}}
                    <div class="flex flex-col items-center px-6 pb-6 pt-12 flex-1">
                        <h3 class="text-center text-lg font-black leading-tight text-azul-profundo line-clamp-1">
                            {{ $adulto->nombres }} {{ $adulto->ap_paterno }}
                        </h3>
                        <p class="mt-1 text-[11px] font-bold text-terracota">{{ $adulto->cod_am }}</p>

                        <div class="mt-4 flex w-full flex-col gap-2 rounded-2xl bg-[#F7F5F2] p-4 flex-1">
                            
                            {{-- CONTEXTO DINÁMICO --}}
                            @if(request()->routeIs('admin.salud-seguimiento.ficha.index'))
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Ficha Médica</span>
                                    @if($adulto->fichasMedicas->first())
                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">Activa</span>
                                    @else
                                        <span class="text-[10px] font-bold text-rose-600 bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">Sin registro</span>
                                    @endif
                                </div>
                            @elseif(request()->routeIs('admin.salud-seguimiento.medicacion.index') || request()->routeIs('admin.salud-seguimiento.administracion.index'))
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Tratamientos</span>
                                    @if($adulto->medicaciones->count() > 0)
                                        <span class="text-[10px] font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full border border-blue-200">{{ $adulto->medicaciones->count() }} activos</span>
                                    @else
                                        <span class="text-[10px] font-bold text-azul-profundo/40 bg-white px-2 py-0.5 rounded-full border border-[#C7B5A3]/40">Ninguno</span>
                                    @endif
                                </div>
                            @elseif(request()->routeIs('admin.salud-seguimiento.valoracion.index'))
                                @php $val = $adulto->valoracionesFuncionales->first(); @endphp
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Dependencia</span>
                                    <span class="text-[10px] font-bold text-azul-profundo">{{ $val ? $val->nivel_dependencia : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Riesgo Caída</span>
                                    <span class="text-[10px] font-bold {{ $val && strtoupper($val->riesgo_caida) == 'ALTO' ? 'text-rose-600' : 'text-azul-profundo' }}">{{ $val ? $val->riesgo_caida : 'N/A' }}</span>
                                </div>
                            @else
                                <div class="flex justify-between items-center border-b border-[#C7B5A3]/20 pb-2">
                                    <span class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Edad</span>
                                    <span class="text-xs font-bold text-azul-profundo">{{ \Carbon\Carbon::parse($adulto->fecha_nac)->age }} años</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Ficha</span>
                                    @if($adulto->fichasMedicas->first())
                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">Lista</span>
                                    @else
                                        <span class="text-[10px] font-bold text-rose-600 bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">Pendiente</span>
                                    @endif
                                </div>
                            @endif

                        </div>

                        <a href="{{ route($contexto['ruta_destino'], $adulto->cod_am) }}" class="mt-5 w-full rounded-xl bg-terracota py-3 text-center text-[11px] font-black uppercase tracking-wider text-white shadow-md transition-all hover:bg-terracota-dark active:scale-95 group-hover:shadow-lg group-hover:shadow-terracota/30">
                            {{ $contexto['boton'] }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center rounded-3xl border border-dashed border-[#C7B5A3]/60 bg-white py-20 text-center shadow-sm">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#E6DDD3]/50">
                        <i class="ph-bold ph-users text-4xl text-[#C7B5A3]"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-azul-profundo">No se encontraron pacientes</h3>
                    <p class="mt-1 text-sm font-bold text-[#2F3E5C]/50">Modifica tu búsqueda para ver más resultados.</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-8">
            {{ $adultos->links() }}
        </div>
    </div>
</div>
