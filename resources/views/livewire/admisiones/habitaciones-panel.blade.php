<div class="space-y-6">
<header class="flex flex-col gap-4 rounded-2xl border border-borde bg-fondo-card p-5 lg:flex-row lg:items-center lg:justify-between">
<div><p class="text-xs font-bold uppercase tracking-wide text-apoyo">Admisiones y ocupación</p><h1 class="mt-1 text-2xl font-black text-titulo">Habitaciones y camas</h1><p class="mt-1 text-sm text-apoyo">Disponibilidad institucional real, capacidad física y residentes asignados.</p></div>
@can('habitaciones.crear')<button type="button" wire:click="abrirCrearHabitacion" class="rm-btn-primary"><i class="ph-bold ph-plus"></i> Registrar habitación</button>@endcan
</header>

<section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
@foreach([['Habitaciones',$stats['total'],'ph-door'],['Capacidad total',$stats['capacidad_total'],'ph-users-three'],['Camas libres',$stats['camas_libres'],'ph-bed'],['Camas ocupadas',$stats['camas_ocupadas'],'ph-user-check'],['Fuera de servicio',$stats['fuera_servicio'],'ph-warning']] as [$etiqueta,$valor,$icono])
<article class="rounded-2xl border border-borde bg-fondo-card p-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-hover text-boton-acento"><i class="ph-bold {{ $icono }}"></i></span><div><p class="text-xs font-semibold text-apoyo">{{ $etiqueta }}</p><p class="text-2xl font-black text-titulo">{{ $valor }}</p></div></div></article>
@endforeach
</section>

    {{-- BARRA DE FILTROS UNIFICADA FORMATO ALERTAS --}}
    <section class="rm-filter-bar">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscador Principal --}}
            <div class="lg:col-span-6 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#677084] dark:text-[#9A9084]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nombre, número, piso o ubicación..."
                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#26221F] py-2 pl-9 pr-8 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] placeholder-[#677084] dark:placeholder-[#8C8276] focus:border-[#A35A44] focus:outline-none h-[38px]">
                @if($search !== '')
                    <button type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[#677084] hover:text-[#A35A44] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Tipo --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroTipo" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="">Tipo (Todas)</option>
                    <option value="INDIVIDUAL">Individual</option>
                    <option value="COMPARTIDA">Compartida</option>
                    <option value="UCI">UCI</option>
                    <option value="OBSERVACION">Observación</option>
                </select>
            </div>

            {{-- Disponibilidad --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="">Disponibilidad (Todas)</option>
                    <option value="DISPONIBLE">Con disponibilidad</option>
                    <option value="OCUPADA">Capacidad completa</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                    <option value="BLOQUEADA">Bloqueada</option>
                </select>
            </div>
        </div>

        {{-- Fila de chips de filtros activos --}}
        @php
            $hasFiltrosActivos = !empty($search) || !empty($filtroTipo) || !empty($filtroEstado);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] dark:border-[#4E463E] text-[11px] font-semibold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
                            <button type="button" wire:click="$set('search', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroTipo))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#304060]/10 dark:bg-[#F3EAE1]/10 border border-[#C7B9AA] text-[11px] font-bold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Tipo: {{ $filtroTipo }}</span>
                            <button type="button" wire:click="$set('filtroTipo', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroEstado))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#D2A45E]/15 border border-[#D2A45E]/30 text-[11px] font-bold text-[#8C6422] dark:text-[#E2BD7E]">
                            <span>Estado: {{ $filtroEstado }}</span>
                            <button type="button" wire:click="$set('filtroEstado', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[#304060]/10 dark:bg-[#F3EAE1]/10 text-[#304060] dark:text-[#F3EAE1]">
                        {{ count($habitaciones) }} habitaciones
                    </span>
                    <button type="button"
                        wire:click="$set('search', ''); $set('filtroTipo', ''); $set('filtroEstado', '');"
                        class="inline-flex items-center gap-1 rounded-xl bg-[#A35A44]/15 hover:bg-[#A35A44]/25 text-[#A35A44] dark:text-[#D58C79] py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </section>

<section class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
@forelse($habitaciones as $habitacion)
@php
$sinRegistrar=max(0,$habitacion->capacidad-$habitacion->camas_count);
$disponible=!in_array($habitacion->estado,['MANTENIMIENTO','BLOQUEADA'],true) && $habitacion->camas_disponibles_count>0;
$motivo=match(true){$habitacion->estado==='MANTENIMIENTO'=>'Fuera de servicio por mantenimiento',$habitacion->estado==='BLOQUEADA'=>'Bloqueada para nuevos ingresos',$habitacion->camas_count===0=>'Sin camas registradas',$habitacion->camas_disponibles_count===0=>'Capacidad completa o sin camas habilitadas',default=>$habitacion->camas_disponibles_count.' cama(s) disponible(s)'};
$porcentaje=$habitacion->capacidad>0?min(100,round(($habitacion->camas_ocupadas_count/$habitacion->capacidad)*100)):0;
@endphp
<article class="flex flex-col rounded-2xl border border-borde bg-fondo-card p-5">
<div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-wide text-apoyo">{{ str_replace('_',' ',$habitacion->tipo_habitacion) }}</p><h2 class="mt-1 text-lg font-black text-titulo">{{ $habitacion->nombre }}</h2><p class="text-sm text-apoyo">{{ $habitacion->ubicacion ?: 'Ubicación no registrada' }}</p></div><span class="rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $disponible?'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito':'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia' }}">{{ $motivo }}</span></div>
<div class="mt-5"><div class="mb-2 flex justify-between text-xs"><span class="font-semibold text-apoyo">Ocupación</span><span class="font-black text-titulo">{{ $habitacion->camas_ocupadas_count }} de {{ $habitacion->capacidad }}</span></div><div class="h-2 overflow-hidden rounded-full bg-fondo-hover"><div class="h-full rounded-full bg-boton-acento" style="width: {{ $porcentaje }}%"></div></div></div>
<dl class="mt-4 grid grid-cols-2 gap-2 text-xs">
<div class="rounded-xl bg-fondo-hover p-3"><dt class="text-apoyo">Libres</dt><dd class="mt-1 text-lg font-black text-estado-exito">{{ $habitacion->camas_disponibles_count }}</dd></div>
<div class="rounded-xl bg-fondo-hover p-3"><dt class="text-apoyo">Ocupadas</dt><dd class="mt-1 text-lg font-black text-titulo">{{ $habitacion->camas_ocupadas_count }}</dd></div>
<div class="rounded-xl bg-fondo-hover p-3"><dt class="text-apoyo">Fuera de servicio</dt><dd class="mt-1 text-lg font-black text-estado-advertencia">{{ $habitacion->camas_mantenimiento_count+$habitacion->camas_bloqueadas_count }}</dd></div>
<div class="rounded-xl bg-fondo-hover p-3"><dt class="text-apoyo">Sin registrar</dt><dd class="mt-1 text-lg font-black text-titulo">{{ $sinRegistrar }}</dd></div>
</dl>
@if($habitacion->observacion)<p class="mt-3 rounded-xl border border-borde bg-fondo-hover p-3 text-xs text-apoyo">{{ $habitacion->observacion }}</p>@endif
<div class="mt-4 flex flex-wrap gap-2 border-t border-borde pt-4">
@can('camas.ver')<button type="button" wire:click="verCamas('{{ $habitacion->cod_habitacion }}')" class="rm-btn-secondary"><i class="ph ph-eye"></i> Ver ocupación</button>@endcan
@can('habitaciones.editar')<button type="button" wire:click="abrirEditarHabitacion('{{ $habitacion->cod_habitacion }}')" class="rm-btn-secondary"><i class="ph ph-pencil"></i> Editar</button>@endcan
@can('camas.crear')<button type="button" wire:click="abrirCrearCama('{{ $habitacion->cod_habitacion }}')" class="rm-btn-secondary" @disabled($sinRegistrar===0) title="{{ $sinRegistrar===0?'La capacidad ya está completamente registrada':'Agregar una cama física' }}"><i class="ph ph-plus"></i> Agregar cama</button>@endcan
</div></article>
@empty
<div class="col-span-full rounded-2xl border border-dashed border-borde bg-fondo-card p-10 text-center"><i class="ph ph-bed text-4xl text-apoyo"></i><h2 class="mt-3 font-black text-titulo">No hay habitaciones para mostrar</h2></div>
@endforelse
</section>
@if($habitaciones->hasPages())<div>{{ $habitaciones->links() }}</div>@endif

<x-ui.modal-livewire wire:model="modalHabitacion" title="{{ $editandoHabitacionId?'Editar habitación':'Registrar habitación' }}" close-method="cerrarModales" max-width="2xl">
<form wire:submit="guardarHabitacion" class="space-y-5">
<div class="rounded-xl border border-borde bg-fondo-hover p-3 text-sm text-apoyo">Defina la capacidad física real. El estado de ocupación se calcula automáticamente desde las camas y asignaciones.</div>
<div class="grid gap-4 sm:grid-cols-2">
<div><label class="mb-1 block text-xs font-bold text-apoyo">Número o referencia *</label><input wire:model="codigo" maxlength="20" placeholder="Ej. 201" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('codigo')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
<div><label class="mb-1 block text-xs font-bold text-apoyo">Nombre visible *</label><input wire:model="nombre" maxlength="100" placeholder="Ej. Habitación 201" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('nombre')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
<div><label class="mb-1 block text-xs font-bold text-apoyo">Tipo *</label><select wire:model="tipoHabitacion" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"><option value="">Seleccione</option><option value="INDIVIDUAL">Individual</option><option value="COMPARTIDA">Compartida</option><option value="UCI">UCI</option><option value="OBSERVACION">Observación</option></select>@error('tipoHabitacion')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
<div><label class="mb-1 block text-xs font-bold text-apoyo">Capacidad de personas *</label><input wire:model="capacidad" type="number" min="1" max="50" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('capacidad')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
<div><label class="mb-1 block text-xs font-bold text-apoyo">Ubicación</label><input wire:model="ubicacion" maxlength="100" placeholder="Piso, ala o sector" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></div>
<div><label class="mb-1 block text-xs font-bold text-apoyo">Disponibilidad institucional</label><select wire:model="estadoHab" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"><option value="DISPONIBLE">Habilitada</option><option value="MANTENIMIENTO">En mantenimiento</option><option value="BLOQUEADA">Bloqueada</option></select>@error('estadoHab')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
<div class="sm:col-span-2"><label class="mb-1 block text-xs font-bold text-apoyo">Observaciones</label><textarea wire:model="observacion" rows="3" maxlength="500" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea></div>
</div><div class="flex justify-end gap-2 border-t border-borde pt-4"><button type="button" wire:click="cerrarModales" class="rm-btn-secondary">Cancelar</button><button type="submit" class="rm-btn-primary"><i class="ph-bold ph-floppy-disk"></i> Guardar habitación</button></div>
</form></x-ui.modal-livewire>

<x-ui.modal-livewire wire:model="modalCama" title="{{ $editandoCamaId?'Actualizar cama':'Registrar cama' }}" close-method="cerrarModales" max-width="lg">
<form wire:submit="guardarCama" class="space-y-4">
@if($habitacionFormularioCama)<div class="rounded-xl border border-borde bg-fondo-hover p-3"><p class="text-xs font-bold uppercase text-apoyo">Habitación</p><p class="mt-1 font-black text-titulo">{{ $habitacionFormularioCama->nombre }}</p><p class="text-xs text-apoyo">Capacidad para {{ $habitacionFormularioCama->capacidad }} persona(s)</p></div>@endif
<div><label class="mb-1 block text-xs font-bold text-apoyo">Número o referencia de cama *</label><input wire:model="codigoCama" maxlength="20" placeholder="Ej. A o Cama 1" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('codigoCama')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
<div><label class="mb-1 block text-xs font-bold text-apoyo">Disponibilidad</label><select wire:model="estadoCama" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm" @disabled($estadoCama==='OCUPADA')><option value="DISPONIBLE">Disponible</option>@if($estadoCama==='OCUPADA')<option value="OCUPADA">Ocupada por residente</option>@endif<option value="MANTENIMIENTO">En mantenimiento</option><option value="BLOQUEADA">Bloqueada</option></select>@error('estadoCama')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
<div><label class="mb-1 block text-xs font-bold text-apoyo">Motivo u observación</label><textarea wire:model="observacionCama" rows="3" maxlength="500" placeholder="Indique el motivo si queda fuera de servicio" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea></div>
<div class="flex justify-end gap-2 border-t border-borde pt-4"><button type="button" wire:click="cerrarModales" class="rm-btn-secondary">Cancelar</button><button type="submit" class="rm-btn-primary">Guardar cama</button></div>
</form></x-ui.modal-livewire>

<x-ui.drawer-livewire wire:model="modalDetalle" title="Ocupación de la habitación" close-method="cerrarModales" width="max-w-2xl">
@if($detalleHabitacion)<div class="space-y-5">
<section class="rounded-2xl border border-borde bg-fondo-hover p-4"><p class="text-xs font-bold uppercase text-apoyo">{{ str_replace('_',' ',$detalleHabitacion->tipo_habitacion) }}</p><h2 class="mt-1 text-xl font-black text-titulo">{{ $detalleHabitacion->nombre }}</h2><p class="text-sm text-apoyo">{{ $detalleHabitacion->ubicacion?:'Ubicación no registrada' }}</p><div class="mt-3 grid grid-cols-3 gap-2 text-center"><div class="rounded-xl bg-fondo-card p-2"><p class="text-lg font-black text-titulo">{{ $detalleHabitacion->capacidad }}</p><p class="text-[11px] text-apoyo">Capacidad</p></div><div class="rounded-xl bg-fondo-card p-2"><p class="text-lg font-black text-estado-exito">{{ $detalleHabitacion->camas_disponibles_count }}</p><p class="text-[11px] text-apoyo">Libres</p></div><div class="rounded-xl bg-fondo-card p-2"><p class="text-lg font-black text-titulo">{{ $detalleHabitacion->camas_ocupadas_count }}</p><p class="text-[11px] text-apoyo">Ocupadas</p></div></div></section>
<section class="space-y-3">@forelse($detalleHabitacion->camas as $cama)@php $asignacion=$cama->asignacionesActivas->first();$residente=$asignacion?->adultoMayor; @endphp
<article class="rounded-2xl border border-borde bg-fondo-card p-4"><div class="flex items-start justify-between gap-3"><div><h3 class="font-black text-titulo">Cama {{ $cama->codigo?:$cama->numero }}</h3><p class="mt-1 text-sm text-apoyo">{{ $residente?trim($residente->nombres.' '.$residente->ap_paterno.' '.$residente->ap_materno):($cama->estado==='DISPONIBLE'?'Disponible para un nuevo ingreso':str_replace('_',' ',$cama->estado)) }}</p>@if($asignacion)<p class="mt-1 text-xs text-apoyo">Asignada desde {{ \Illuminate\Support\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y') }}</p>@endif @if($cama->observacion)<p class="mt-2 text-xs text-apoyo">{{ $cama->observacion }}</p>@endif</div><span class="rounded-full border border-borde bg-fondo-hover px-2 py-1 text-[11px] font-bold text-titulo">{{ $residente?'OCUPADA':$cama->estado }}</span></div>@can('camas.editar')<button type="button" wire:click="editarCama('{{ $cama->cod_cama }}')" class="rm-btn-secondary mt-3"><i class="ph ph-pencil"></i> Actualizar disponibilidad</button>@endcan</article>
@empty<div class="rounded-xl border border-dashed border-borde p-6 text-center text-sm text-apoyo">Todavía no hay camas físicas registradas.</div>@endforelse</section>
</div>@endif
</x-ui.drawer-livewire>
</div>
