<div class="relative mx-auto max-w-7xl space-y-5">
 
 {{-- MODAL DE FORMULARIO DE REGISTRO / EDICIÓN --}}
 @livewire('residentes.adulto-mayor-form-modal')

 {{-- ENCABEZADO CON ESTILO PREMIUM --}}
 <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-6 shadow-[0_18px_45px_rgba(47,62,92,0.1)] backdrop-blur-xl relative overflow-hidden">
 <div class="absolute -right-16 -top-16 h-36 w-36 rounded-full bg-gradient-to-br from-[#E27D60]/10 to-transparent blur-2xl"></div>
 <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between relative z-10">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Gestión Operativa
 </span>
 <h1 class="mt-1.5 text-3xl font-black text-titulo tracking-tight">Centro de Adultos Mayores</h1>
 <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-apoyo">
 Gestión integral, seguimiento y consulta de adultos mayores registrados en CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
 </p>
 </div>

 <div class="flex flex-wrap items-center gap-2">
 <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel border border-borde-suave px-4 py-2.5 text-xs font-bold text-titulo transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrow-left text-sm"></i> Panel de Inicio
 </a>

 <a wire:navigate href="{{ route('admin.admisiones.preadmision') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold text-inverso shadow-[0_8px_20px_rgba(233,122,95,0.22)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-plus-circle text-sm"></i> Nueva preadmisión
 </a>

 <a href="{{ route('admin.adultos-mayores.reporte-general') }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold text-inverso shadow-[0_8px_20px_rgba(47,62,92,0.12)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-file-pdf text-sm"></i> Censo en PDF
 </a>
 </div>
 </div>
 </section>

 {{-- 6 INDICADORES SUPERIORES CON DATOS REALES --}}
 <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm relative overflow-hidden transition hover:shadow-md">
 <span class="absolute right-3 top-3 text-apoyo text-3xl"><i class="ph-bold ph-users-four"></i></span>
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo leading-none">Total registrados</p>
 <h3 class="mt-2 text-2xl font-black text-titulo leading-none">{{ $totales['total'] ?? 0 }}</h3>
 </div>
 
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm relative overflow-hidden transition hover:shadow-md">
 <span class="absolute right-3 top-3 text-estado-exito text-3xl"><i class="ph-bold ph-user-circle-gear"></i></span>
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo leading-none">Activos</p>
 <h3 class="mt-2 text-2xl font-black text-estado-exito leading-none">{{ $totales['activos'] ?? 0 }}</h3>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm relative overflow-hidden transition hover:shadow-md">
 <span class="absolute right-3 top-3 text-boton-acento text-3xl"><i class="ph-bold ph-heartbeat"></i></span>
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo leading-none">En seguimiento</p>
 <h3 class="mt-2 text-2xl font-black text-boton-acento leading-none">{{ $totales['seguimiento'] ?? 0 }}</h3>
 </div>
 
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm relative overflow-hidden transition hover:shadow-md">
 <span class="absolute right-3 top-3 text-apoyo text-3xl"><i class="ph-bold ph-archive"></i></span>
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo leading-none">Archivados</p>
 <h3 class="mt-2 text-2xl font-black text-parrafo leading-none">{{ $totales['archivados'] ?? 0 }}</h3>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-estado-peligroBg p-4 shadow-sm relative overflow-hidden transition hover:shadow-md">
 <span class="absolute right-3 top-3 text-boton-acento text-3xl"><i class="ph-bold ph-brain"></i></span>
 <p class="text-[9px] font-bold uppercase tracking-widest text-boton-acento leading-none">Sin eval. cognitiva</p>
 <h3 class="mt-2 text-2xl font-black text-boton-acento leading-none">{{ $totales['sin_evaluacion'] ?? 0 }}</h3>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm relative overflow-hidden transition hover:shadow-md">
 <span class="absolute right-3 top-3 text-apoyo text-3xl"><i class="ph-bold ph-files"></i></span>
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo leading-none">Docs pendientes</p>
 <h3 class="mt-2 text-2xl font-black text-titulo leading-none">{{ $totales['docs_pendientes'] ?? 0 }}</h3>
 </div>
 </section>

 <div x-data="{ tab: 'tarjetas' }" class="space-y-4">
 {{-- TABS NAVEGATIVOS DEL PANEL --}}
 <nav class="flex space-x-2 rounded-2xl border border-borde-suave bg-fondo-panel p-2 shadow-sm overflow-x-auto">
 <button @click="tab = 'tarjetas'" :class="tab === 'tarjetas' ? 'bg-boton-principal text-inverso shadow-sm' : 'text-titulo hover:bg-fondo-app'" class="rounded-xl px-4 py-2.5 text-xs font-bold transition whitespace-nowrap">
 <i class="ph-bold ph-cards mr-1 text-sm"></i> Vista Tarjetas
 </button>
 <button @click="tab = 'tabla'" :class="tab === 'tabla' ? 'bg-boton-principal text-inverso shadow-sm' : 'text-titulo hover:bg-fondo-app'" class="rounded-xl px-4 py-2.5 text-xs font-bold transition whitespace-nowrap">
 <i class="ph-bold ph-table mr-1 text-sm"></i> Tabla General
 </button>
 <button @click="tab = 'archivados'" :class="tab === 'archivados' ? 'bg-boton-principal text-inverso shadow-sm' : 'text-titulo hover:bg-fondo-app'" class="rounded-xl px-4 py-2.5 text-xs font-bold transition whitespace-nowrap">
 <i class="ph-bold ph-archive mr-1 text-sm"></i> Expedientes Archivados
 </button>
 <button @click="tab = 'alertas'" :class="tab === 'alertas' ? 'bg-boton-principal text-inverso shadow-sm' : 'text-titulo hover:bg-fondo-app'" class="rounded-xl px-4 py-2.5 text-xs font-bold transition whitespace-nowrap">
 <i class="ph-bold ph-warning mr-1 text-sm"></i> Alertas y Pendientes Básicos
 </button>
 </nav>

 {{-- SECCIÓN DE FILTROS AVANZADOS --}}
    {{-- SECCIÓN DE FILTROS AVANZADOS UNIFICADA FORMATO ALERTAS --}}
    <section class="rm-filter-bar" x-show="tab !== 'alertas'">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscador Principal --}}
            <div class="lg:col-span-3 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#677084] dark:text-[#9A9084]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="buscar"
                    placeholder="Ficha, CI, Nombre, Teléfono..."
                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#26221F] py-2 pl-9 pr-8 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] placeholder-[#677084] dark:placeholder-[#8C8276] focus:border-[#A35A44] focus:outline-none h-[38px]">
                @if($buscar !== '')
                    <button type="button"
                        wire:click="$set('buscar', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[#677084] hover:text-[#A35A44] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Estado --}}
            <div class="lg:col-span-2">
                <select wire:model.live="estado" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="">Todos los estados</option>
                    @foreach($estadosAdulto ?? [] as $est)
                        <option value="{{ $est->estado }}">{{ $est->estado }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Género --}}
            <div class="lg:col-span-1">
                <select wire:model.live="genero" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-2 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="">Género</option>
                    <option value="MASCULINO">Masc.</option>
                    <option value="FEMENINO">Fem.</option>
                </select>
            </div>

            {{-- Rango de Edad --}}
            <div class="lg:col-span-2">
                <select wire:model.live="rango_edad" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="">Cualquier edad</option>
                    <option value="60-70">60 a 70 años</option>
                    <option value="70-80">70 a 80 años</option>
                    <option value="80+">Mayores a 80 años</option>
                </select>
            </div>

            {{-- Permanencia --}}
            <div class="lg:col-span-2">
                <select wire:model.live="permanencia" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="">Permanencia (Todas)</option>
                    <option value="PERMANENTE">Permanente</option>
                    <option value="TEMPORAL">Temporal</option>
                    <option value="EVENTUAL">Eventual</option>
                </select>
            </div>

            {{-- Ciudad / Muni --}}
            <div class="lg:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="ciudad_municipio" placeholder="Ciudad/Muni..." class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
            </div>
        </div>

        {{-- Fila de chips de filtros activos --}}
        @php
            $hasFiltrosActivos = !empty($buscar) || !empty($estado) || !empty($genero) || !empty($rango_edad) || !empty($permanencia) || !empty($ciudad_municipio) || !empty($fecha_desde);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($buscar))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] dark:border-[#4E463E] text-[11px] font-semibold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Búsqueda: "{{ Str::limit($buscar, 16) }}"</span>
                            <button type="button" wire:click="$set('buscar', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($estado))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#D2A45E]/15 border border-[#D2A45E]/30 text-[11px] font-bold text-[#8C6422] dark:text-[#E2BD7E]">
                            <span>Estado: {{ $estado }}</span>
                            <button type="button" wire:click="$set('estado', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($genero))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#304060]/10 dark:bg-[#F3EAE1]/10 border border-[#C7B9AA] text-[11px] font-bold text-[#304060] dark:text-[#F3EAE1]">
                            <span>{{ $genero === 'MASCULINO' ? 'Masc.' : 'Fem.' }}</span>
                            <button type="button" wire:click="$set('genero', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($rango_edad))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#71876A]/15 border border-[#71876A]/30 text-[11px] font-bold text-[#495B44] dark:text-[#9FB897]">
                            <span>Edad: {{ $rango_edad }}</span>
                            <button type="button" wire:click="$set('rango_edad', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($permanencia))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#304060]/10 dark:bg-[#F3EAE1]/10 border border-[#C7B9AA] text-[11px] font-bold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Perm.: {{ $permanencia }}</span>
                            <button type="button" wire:click="$set('permanencia', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[#304060]/10 dark:bg-[#F3EAE1]/10 text-[#304060] dark:text-[#F3EAE1]">
                        {{ $adultos->total() ?? count($adultos) }} coincidentes
                    </span>
                    <button type="button"
                        wire:click="$set('buscar', ''); $set('estado', ''); $set('genero', ''); $set('permanencia', ''); $set('ciudad_municipio', ''); $set('rango_edad', ''); $set('fecha_desde', ''); $set('fecha_hasta', '');"
                        class="inline-flex items-center gap-1 rounded-xl bg-[#A35A44]/15 hover:bg-[#A35A44]/25 text-[#A35A44] dark:text-[#D58C79] py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </section>

 {{-- VISTA TARJETAS (CARDS RESPONSIVAS PREMIUM) --}}
 <section x-show="tab === 'tarjetas'">
 <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
 @php $hayTarjetasActivas = false; @endphp
 @foreach($adultos as $adulto)
 @php
 $estado = strtoupper($adulto->estado_adulto);
 $esArchivado = $estado === 'ARCHIVADO' || $estado === 'INACTIVO';
 $colorBg = $esArchivado ? 'bg-fondo-panel border-borde-suave' : 'bg-fondo-card border-borde-suave';
 $colorEstado = $esArchivado ? 'bg-fondo-panel text-parrafo' : 'bg-estado-exitoBg text-parrafo';
 $fotoUrl = $adulto->foto ? Storage::url($adulto->foto) : null;
 
 // CÁLCULOS DINÁMICOS DE RELACIONES EAGER-LOADED
 $famPrincipal = $adulto->familiares->first(fn($f) => $f->pivot->es_responsable);
 if (!$famPrincipal) {
 $famPrincipal = $adulto->familiares->first();
 }
 
 $ultAtencion = collect($adulto->atenciones)->sortByDesc('fecha')->first();
 $ultEval = collect($adulto->evaluacionesGeriatricas)->sortByDesc('fecha_eval')->first();
 @endphp
 @if(!$esArchivado)
 @php $hayTarjetasActivas = true; @endphp
 <div class="rounded-2xl border {{ $colorBg }} p-0 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex flex-col justify-between overflow-hidden relative">
 {{-- Línea superior de acento --}}
 <div class="h-1.5 w-full bg-boton-acento"></div>
 
 {{-- FOTO Y ENCABEZADO TARJETA --}}
 <div class="p-5 flex flex-col items-center border-b border-borde-suave bg-gradient-to-b from-[#E6DDD3]/10 to-transparent">
 <span class="absolute top-4 right-4 rounded-lg {{ $colorEstado }} px-2 py-1 text-[9px] font-bold uppercase tracking-wider">{{ $estado }}</span>
 <span class="absolute top-4 left-4 text-[9px] font-bold uppercase tracking-widest text-apoyo bg-fondo-panel px-2 py-1 rounded-md border border-borde-suave shadow-sm">{{ $adulto->edad ?? 'Edad no registrada' }}{{ $adulto->edad ? ' años' : '' }}</span>
 
 @if($fotoUrl)
 <img src="{{ $fotoUrl }}" class="mt-4 mb-3 h-24 w-24 rounded-2xl object-cover border-4 border-white shadow-sm">
 @else
 <div class="mt-4 mb-3 h-24 w-24 rounded-2xl bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] flex items-center justify-center text-inverso font-black text-2xl shadow-sm border-4 border-white">
 {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
 </div>
 @endif
 
 <h3 class="text-base font-extrabold text-titulo text-center leading-tight">
 {{ $adulto->nombres }}<br>
 <span class="text-xs text-apoyo font-bold">{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</span>
 </h3>
 </div>
 
 {{-- DATOS RÁPIDOS Y RELACIONES --}}
 <div class="p-4 px-5 space-y-2.5">
 <div class="flex justify-between items-center text-xs border-b border-borde-suave pb-2">
 <span class="font-bold text-apoyo flex items-center"><i class="ph-bold ph-identification-card mr-1.5 text-sm"></i> C.I.</span>
 <span class="font-black text-titulo">{{ $adulto->ci }}</span>
 </div>
 <div class="flex justify-between items-center text-xs border-b border-borde-suave pb-2">
 <span class="font-bold text-apoyo flex items-center"><i class="ph-bold ph-calendar-blank mr-1.5 text-sm"></i> Edad</span>
 <span class="font-black text-titulo">{{ $adulto->edad }} años</span>
 </div>
 
 {{-- Familiar Principal --}}
 <div class="flex justify-between items-start text-xs border-b border-borde-suave pb-2">
 <span class="font-bold text-apoyo flex items-center"><i class="ph-bold ph-user mr-1.5 text-sm"></i> Familiar resp.</span>
 <span class="font-black text-titulo text-right truncate max-w-[120px]">
 @if($famPrincipal)
 {{ $famPrincipal->usuario->name ?? $famPrincipal->usuario->nombres ?? 'Familiar' }}
 @else
 <span class="text-boton-acento uppercase text-[9px] font-bold"><i class="ph-bold ph-warning mr-0.5"></i>Sin registrar</span>
 @endif
 </span>
 </div>

 {{-- Última Atención --}}
 <div class="flex justify-between items-center text-xs border-b border-borde-suave pb-2">
 <span class="font-bold text-apoyo flex items-center"><i class="ph-bold ph-clock-counter-clockwise mr-1.5 text-sm"></i> Últ. Atención</span>
 <span class="font-black text-titulo">
 {{ $ultAtencion ? \Carbon\Carbon::parse($ultAtencion->fecha)->format('d/m/Y') : 'Ninguna' }}
 </span>
 </div>

 {{-- Última Evaluación Cognitiva --}}
 <div class="flex justify-between items-center text-xs pb-1">
 <span class="font-bold text-apoyo flex items-center"><i class="ph-bold ph-brain mr-1.5 text-sm"></i> Últ. Eval. Cog.</span>
 <span class="font-black text-titulo">
 {{ $ultEval ? \Carbon\Carbon::parse($ultEval->fecha_eval)->format('d/m/Y') : 'Sin evaluar' }}
 </span>
 </div>
 </div>

 {{-- ACCIONES DE FICHA Y CAJÓN RÁPIDO --}}
 <div class="p-4 pt-1 mt-auto">
 <div class="flex gap-2">
 <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_residente) }}" class="flex-1 flex justify-center items-center rounded-xl bg-boton-principal py-2 text-inverso hover:bg-fondo-panel transition shadow-sm" title="Ver Ficha Integral">
 <i class="ph-bold ph-eye text-sm mr-1"></i> <span class="text-[9px] font-bold uppercase tracking-wider">Ficha Integral</span>
 </a>
 <button type="button" wire:click="editarAdultoMayor('{{ $adulto->cod_residente }}')" class="flex h-9 w-9 justify-center items-center rounded-xl bg-estado-peligroBg border border-borde-focus text-boton-acento hover:bg-boton-acento hover:text-inverso transition" title="Editar Ficha">
 <i class="ph-bold ph-pencil-simple text-base"></i>
 </button>
 <div x-data="{ open: false }" class="relative">
 <button @click="open = !open" @click.away="open = false" type="button" class="flex h-9 w-9 justify-center items-center rounded-xl bg-fondo-app text-titulo hover:bg-fondo-panel transition" title="Cambiar Estado Rápido">
 <i class="ph-bold ph-arrows-left-right text-base"></i>
 </button>
 <div x-cloak x-show="open" class="absolute bottom-full mb-2 right-0 bg-fondo-card rounded-xl shadow-lg border border-borde-suave p-1.5 min-w-[130px] z-50">
 <p class="text-[9px] font-bold uppercase text-apoyo px-2 py-1 border-b border-borde-suave mb-1">Cambiar Estado:</p>
 @foreach($estadosAdulto as $est)
 @if(strtoupper($est->estado) !== $estado)
 <form method="POST" action="{{ route('admin.adultos-mayores.estado', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Cambiar estado a {{ $est->estado }}?', 'Se registrará en el historial de estados de forma automática.')">
 @csrf @method('PATCH')
 <input type="hidden" name="cod_est_adul" value="{{ $est->cod_est_adul }}">
 <button type="submit" class="w-full text-left px-2.5 py-1.5 text-[10px] font-bold text-titulo hover:bg-fondo-panel rounded-lg transition">{{ $est->estado }}</button>
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
 <div class="col-span-full rounded-2xl border border-borde-suave bg-fondo-panel p-5 text-center">
 <i class="ph-bold ph-users-three text-3xl text-apoyo mb-2"></i>
 <p class="text-sm font-bold text-apoyo">No se encontraron adultos mayores activos con los filtros aplicados.</p>
 </div>
 @endif
 </div>
 </section>

 {{-- TABLA GENERAL (HÍBRIDA DESKTOP / RESPONSIVA) --}}
 <section x-show="tab === 'tabla'" class="rounded-2xl border border-borde-suave bg-fondo-card shadow-sm overflow-hidden" style="display: none;">
 <div class="overflow-x-auto">
 <table class="w-full text-left text-sm text-titulo">
 <thead class="bg-fondo-panel text-[9px] font-bold uppercase tracking-widest text-apoyo border-b border-borde-suave">
 <tr>
 <th class="px-5 py-4">Nombre Completo</th>
 <th class="px-5 py-4">Carnet Identidad</th>
 <th class="px-5 py-4">Edad</th>
 <th class="px-5 py-4">F. Ingreso</th>
 <th class="px-5 py-4">Estado</th>
 <th class="px-5 py-4">Familiar Resp.</th>
 <th class="px-5 py-4 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/25">
 @foreach($adultos as $adulto)
 @php
 $famPrincipal = $adulto->familiares->first(fn($f) => $f->pivot->es_responsable);
 if (!$famPrincipal) {
 $famPrincipal = $adulto->familiares->first();
 }
 @endphp
 <tr class="hover:bg-fondo-panel transition">
 <td class="px-5 py-3.5 font-bold text-xs">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</td>
 <td class="px-5 py-3.5 text-xs">{{ $adulto->ci }} {{ $adulto->complemento_ci }}</td>
 <td class="px-5 py-3.5 text-xs font-bold">{{ $adulto->edad }} años</td>
 <td class="px-5 py-3.5 text-xs">{{ optional($adulto->fecha_ing)->format('d/m/Y') }}</td>
 <td class="px-5 py-3.5">
 <span class="rounded-lg px-2 py-0.5 text-[9px] font-bold uppercase {{ in_array(strtoupper($adulto->estado_adulto), ['ACTIVO', 'ADMITIDO']) ? 'bg-estado-exitoBg text-parrafo' : 'bg-estado-peligroBg text-boton-acento' }}">
 {{ $adulto->estado_adulto }}
 </span>
 </td>
 <td class="px-5 py-3.5 text-xs">
 @if($famPrincipal)
 <span class="font-bold">{{ $famPrincipal->usuario->name ?? $famPrincipal->usuario->nombres ?? 'Familiar' }}</span>
 @else
 <span class="text-xs text-apoyo italic">Ninguno</span>
 @endif
 </td>
 <td class="px-5 py-3.5">
 <div class="flex justify-end gap-1.5">
 <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_residente) }}" class="rounded-xl bg-boton-principal p-2 text-inverso hover:bg-fondo-panel transition" title="Ver Ficha Integral">
 <i class="ph-bold ph-eye text-sm"></i>
 </a>
 @if(strtoupper($adulto->estado_adulto) !== 'ARCHIVADO' && strtoupper($adulto->estado_adulto) !== 'INACTIVO')
 <button type="button" wire:click="editarAdultoMayor('{{ $adulto->cod_residente }}')" class="rounded-xl bg-boton-acento p-2 text-inverso hover:bg-fondo-panel transition" title="Editar">
 <i class="ph-bold ph-pencil-simple text-sm"></i>
 </button>
 <form method="POST" action="{{ route('admin.adultos-mayores.archivar', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Archivar expediente de {{ $adulto->nombres }}?', 'El expediente pasará a la sección de archivados/inactivos.')">
 @csrf @method('PATCH')
 <button type="submit" class="rounded-xl bg-fondo-panel text-parrafo border border-borde p-2 hover:bg-fondo-panel hover:text-inverso transition" title="Archivar expediente"><i class="ph-bold ph-archive text-sm"></i></button>
 </form>
 @else
 <button disabled class="rounded-xl bg-fondo-panel p-2 text-apoyo cursor-not-allowed border border-borde-suave">
 <i class="ph-bold ph-pencil-simple text-sm"></i>
 </button>
 <form method="POST" action="{{ route('admin.adultos-mayores.restaurar', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Restaurar expediente de {{ $adulto->nombres }}?', 'El expediente volverá a ser catalogado como ACTIVO.')">
 @csrf @method('PATCH')
 <button type="submit" class="rounded-xl bg-estado-exitoBg p-2 text-inverso hover:bg-fondo-panel transition" title="Restaurar expediente"><i class="ph-bold ph-arrow-counter-clockwise text-sm"></i></button>
 </form>
 @endif
 </div>
 </td>
 </tr>
 @endforeach
 @if(count($adultos) === 0)
 <tr>
 <td colspan="8" class="px-5 py-10 text-center text-sm font-bold text-apoyo">No se encontraron registros en el censo.</td>
 </tr>
 @endif
 </tbody>
 </table>
 </div>
 </section>

 {{-- EXPEDIENTES ARCHIVADOS (INACTIVOS / HISTÓRICOS) --}}
 <section x-show="tab === 'archivados'" style="display: none;">
 <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
 @php $hayArchivados = false; @endphp
 @foreach($adultos as $adulto)
 @if(strtoupper($adulto->estado_adulto) === 'ARCHIVADO' || strtoupper($adulto->estado_adulto) === 'INACTIVO')
 @php $hayArchivados = true; @endphp
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-5 shadow-sm flex flex-col justify-between transition hover:shadow-md">
 <div>
 <div class="mb-3 flex items-center justify-between">
 <span class="rounded-lg bg-fondo-panel px-2.5 py-1 text-[9px] font-bold uppercase text-parrafo tracking-wider border border-borde shadow-sm">{{ $adulto->estado_adulto }}</span>
 <span class="text-[10px] font-bold text-apoyo">{{ $adulto->ci ? 'CI '.$adulto->ci : 'Documento no registrado' }}</span>
 </div>
 <h3 class="text-base font-extrabold text-apoyo leading-tight">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
 <p class="text-xs text-apoyo font-bold mt-1">{{ $adulto->ap_materno }}</p>
 
 <div class="mt-4 space-y-2 border-t border-borde-suave pt-3 text-xs">
 <div class="flex justify-between"><span class="text-apoyo font-bold">Carnet:</span><span class="font-bold">{{ $adulto->ci }}</span></div>
 <div class="flex justify-between"><span class="text-apoyo font-bold">Archivado en:</span><span class="font-bold">{{ $adulto->archivado_en ? \Carbon\Carbon::parse($adulto->archivado_en)->format('d/m/Y') : 'No registrada' }}</span></div>
 <div class="flex flex-col mt-2 gap-1 bg-fondo-card/40 p-2 rounded-xl border border-borde-suave"><span class="text-apoyo text-[10px] font-bold uppercase leading-none">Motivo:</span><span class="font-semibold text-[11px] text-apoyo mt-1">{{ $adulto->motivo_archivado ?: 'Archivado administrativamente.' }}</span></div>
 </div>
 </div>
 
 <div class="mt-5 pt-3 border-t border-borde-suave flex gap-2">
 <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_residente) }}" class="flex-1 flex justify-center items-center rounded-xl bg-fondo-panel py-2 text-inverso hover:bg-boton-principal transition text-xs font-bold shadow-sm" title="Ver Expediente Completo">
 <i class="ph-bold ph-eye mr-1 text-sm"></i> Ver Ficha
 </a>
 <div x-data="{ open: false }" class="relative flex-1">
 <button @click="open = !open" @click.away="open = false" type="button" class="flex w-full justify-center items-center rounded-xl bg-estado-exitoBg py-2 text-inverso hover:bg-fondo-panel transition text-xs font-bold" title="Restaurar / Cambiar Estado">
 <i class="ph-bold ph-arrow-counter-clockwise mr-1 text-sm"></i> Restaurar
 </button>
 <div x-cloak x-show="open" class="absolute bottom-full mb-2 right-0 bg-fondo-card rounded-xl shadow-lg border border-borde-suave p-1.5 min-w-[130px] z-50">
 <p class="text-[9px] font-bold uppercase text-apoyo px-2 py-1 border-b border-borde-suave mb-1">Cambiar Estado:</p>
 @foreach($estadosAdulto as $est)
 @if(strtoupper($est->estado) !== strtoupper($adulto->estado_adulto))
 <form method="POST" action="{{ route('admin.adultos-mayores.estado', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Restaurar y cambiar estado a {{ $est->estado }}?', 'El expediente pasará nuevamente al censo activo.')">
 @csrf @method('PATCH')
 <input type="hidden" name="cod_est_adul" value="{{ $est->cod_est_adul }}">
 <button type="submit" class="w-full text-left px-2.5 py-1.5 text-[10px] font-bold text-titulo hover:bg-fondo-panel rounded-lg transition">{{ $est->estado }}</button>
 </form>
 @endif
 @endforeach
 </div>
 </div>
 </div>
 </div>
 @endif
 @endforeach
 @if(!$hayArchivados)
 <div class="col-span-full rounded-2xl border border-borde-suave bg-fondo-panel p-5 text-center">
 <i class="ph-bold ph-archive text-3xl text-apoyo mb-2"></i>
 <p class="text-sm font-bold text-apoyo">No existen expedientes archivados en este censo.</p>
 </div>
 @endif
 </div>
 </section>

 {{-- ALERTAS Y PENDIENTES BÁSICOS --}}
 <section x-show="tab === 'alertas'" style="display: none;">
 <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
 @php $hayAlertas = false; @endphp
 @foreach($adultos as $adulto)
 @php
 $alertas = [];
 if(!$adulto->foto) $alertas[] ="Falta fotografía digital de perfil";
 if($adulto->fam_total == 0) $alertas[] ="Falta registrar red de apoyo familiar";
 if(!$adulto->contacto_emergencia_nombre) $alertas[] ="Falta registrar contacto de emergencia";
 if($adulto->obs_total == 0) $alertas[] ="Sin observaciones diarias";
 
 $tieneEval = collect($adulto->evaluacionesGeriatricas)->count() > 0;
 if(!$tieneEval) $alertas[] ="Falta realizar evaluación cognitiva inicial";
 @endphp
 @if(count($alertas) > 0 && strtoupper($adulto->estado_adulto) !== 'ARCHIVADO')
 @php $hayAlertas = true; @endphp
 <div class="rounded-2xl border border-borde-focus bg-fondo-card p-5 shadow-sm flex flex-col justify-between transition hover:-translate-y-0.5 hover:shadow-md relative overflow-hidden">
 <div class="absolute right-0 top-0 h-1.5 w-full bg-boton-acento"></div>
 <div>
 <div class="flex justify-between items-start mb-3">
 <div>
 <span class="text-[9px] font-bold text-boton-acento bg-estado-peligroBg px-2 py-0.5 rounded-full border border-borde-focus tracking-widest uppercase">{{ $adulto->ci ? 'CI '.$adulto->ci : 'Documento pendiente' }}</span>
 <h3 class="text-base font-extrabold text-titulo leading-tight mt-1">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
 </div>
 <span class="rounded-full bg-estado-peligroBg p-2 text-boton-acento border border-borde-focus shadow-inner"><i class="ph-bold ph-warning text-base"></i></span>
 </div>
 
 <ul class="mt-4 space-y-2.5">
 @foreach($alertas as $alerta)
 <li class="text-[11px] font-bold text-boton-acento flex items-start"><span class="h-1.5 w-1.5 rounded-full bg-boton-acento mt-1.5 mr-2 shrink-0 shadow-[0_0_6px_rgba(226,125,96,0.6)]"></span> {{ $alerta }}</li>
 @endforeach
 </ul>
 </div>
 
 <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_residente) }}" class="mt-5 flex justify-center items-center rounded-xl bg-boton-principal py-2 text-inverso hover:bg-fondo-panel transition text-xs font-bold w-full shadow-sm">
 <i class="ph-bold ph-folder-open text-base mr-1.5"></i> Completar Expediente
 </a>
 </div>
 @endif
 @endforeach
 @if(!$hayAlertas)
 <div class="col-span-full rounded-2xl border border-borde-suave bg-fondo-card p-5 text-center flex flex-col items-center">
 <div class="h-14 w-14 rounded-full bg-estado-exitoBg text-estado-exito border border-estado-exitoBorde shadow-inner flex items-center justify-center mb-3">
 <i class="ph-bold ph-check-circle text-3xl"></i>
 </div>
 <h3 class="text-lg font-extrabold text-titulo">Expedientes Completos</h3>
 <p class="text-sm font-semibold text-apoyo mt-1 max-w-sm">No se detectaron expedientes con alertas o campos prioritarios vacíos. ¡Excelente gestión!</p>
 </div>
 @endif
 </div>
 </section>

 {{-- PAGINACIÓN CON ESTILOS CONSISTENTES --}}
 <div class="mt-6 flex justify-center">
 {{ $adultos->links() }}
 </div>
 </div>
</div>
