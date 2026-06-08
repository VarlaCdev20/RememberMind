@php
    $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20';
    $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1';
    $errCls   = 'mt-1 text-[10px] font-bold text-boton-acento';
    $estadoClases = [
        'DISPONIBLE'   => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
        'OCUPADA'      => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',
        'MANTENIMIENTO'=> 'border-borde-suave bg-fondo-panel text-apoyo',
        'BLOQUEADA'    => 'border-borde-focus bg-estado-peligroBg text-boton-acento',
    ];
@endphp

<div class="min-h-screen bg-fondo-panel px-4 py-5 sm:px-6 lg:px-8"
     x-data @keydown.window.escape="$wire.cerrarModales()">
<div class="mx-auto max-w-7xl space-y-5">

{{-- CABECERA --}}
<section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)]">
    <div class="h-1.5 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
    <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-[#8DA280]/40 bg-[#8DA280]/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-[#4A6043]">
                <i class="ph-bold ph-bed text-sm"></i> CENTRO GERIÁTRICO — Infraestructura
            </span>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo">Habitaciones y Camas</h1>
            <p class="mt-1.5 text-sm font-bold text-apoyo">Gestión de habitaciones y disponibilidad de camas del centro.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('habitaciones.crear')
            <button wire:click="abrirCrearHabitacion"
                class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold text-inverso shadow-sm hover:shadow-md active:scale-95 transition">
                <i class="ph-bold ph-plus text-sm"></i> Nueva habitación
            </button>
            @endcan
        </div>
    </div>
</section>

{{-- MÉTRICAS --}}
<div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @foreach([
        ['label'=>'Habitaciones','valor'=>$stats['total'],'icono'=>'ph-door','cls'=>'text-titulo'],
        ['label'=>'Disponibles','valor'=>$stats['disponibles'],'icono'=>'ph-check-circle','cls'=>'text-estado-exito'],
        ['label'=>'Ocupadas','valor'=>$stats['ocupadas'],'icono'=>'ph-user','cls'=>'text-estado-advertencia'],
        ['label'=>'Mantenimiento','valor'=>$stats['mantenimiento'],'icono'=>'ph-wrench','cls'=>'text-apoyo'],
        ['label'=>'Camas total','valor'=>$stats['camas_total'],'icono'=>'ph-bed','cls'=>'text-titulo'],
        ['label'=>'Camas libres','valor'=>$stats['camas_libres'],'icono'=>'ph-check','cls'=>'text-estado-exito'],
    ] as $m)
    <article class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 shadow-sm">
        <div class="flex items-start justify-between gap-1.5">
            <p class="text-[9px] font-bold uppercase tracking-[0.12em] text-apoyo">{{ $m['label'] }}</p>
            <i class="ph-bold {{ $m['icono'] }} text-base {{ $m['cls'] }} shrink-0"></i>
        </div>
        <p class="mt-2 text-2xl font-black {{ $m['cls'] }}">{{ $m['valor'] }}</p>
    </article>
    @endforeach
</div>

{{-- FILTROS --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[180px] flex-1">
            <label class="{{ $labelCls }}">Buscar</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Código o nombre..."
                    class="{{ $inputCls }} pl-8" />
            </div>
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Tipo</label>
            <select wire:model.live="filtroTipo" class="{{ $inputCls }}">
                <option value="">Todos</option>
                <option value="INDIVIDUAL">Individual</option>
                <option value="COMPARTIDA">Compartida</option>
                <option value="UCI">UCI</option>
                <option value="OBSERVACION">Observación</option>
            </select>
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Estado</label>
            <select wire:model.live="filtroEstado" class="{{ $inputCls }}">
                <option value="">Todos</option>
                <option value="DISPONIBLE">Disponible</option>
                <option value="OCUPADA">Ocupada</option>
                <option value="MANTENIMIENTO">Mantenimiento</option>
                <option value="BLOQUEADA">Bloqueada</option>
            </select>
        </div>
    </div>
</section>

{{-- TABLA HABITACIONES --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="border-b border-borde-suave px-5 py-3.5">
        <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">
            <i class="ph-bold ph-list-bullets mr-2 text-apoyo"></i>Habitaciones registradas
        </h2>
    </div>
    @if($habitaciones->isEmpty())
    <div class="flex flex-col items-center gap-4 py-14 text-center">
        <i class="ph-bold ph-door text-4xl text-apoyo"></i>
        <p class="text-sm font-bold text-titulo">Sin habitaciones registradas</p>
        @can('habitaciones.crear')
        <button wire:click="abrirCrearHabitacion"
            class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso hover:shadow-md transition">
            <i class="ph-bold ph-plus"></i> Registrar primera habitación
        </button>
        @endcan
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] text-xs">
            <thead>
                <tr class="border-b border-borde-suave">
                    @foreach(['Código','Nombre','Tipo','Ubicación','Capacidad','Estado','Camas','Acciones'] as $h)
                    <th class="px-4 pb-2.5 pt-3 text-left font-bold uppercase tracking-[0.12em] text-apoyo">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-[#C7B5A3]/20">
                @foreach($habitaciones as $hab)
                @php $ecls = $estadoClases[$hab->estado] ?? 'border-borde-suave bg-fondo-panel text-apoyo'; @endphp
                <tr class="hover:bg-fondo-panel transition">
                    <td class="px-4 py-3 font-black text-titulo">{{ $hab->codigo }}</td>
                    <td class="px-4 py-3 text-apoyo">{{ $hab->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-apoyo">{{ $hab->tipo_habitacion }}</td>
                    <td class="px-4 py-3 text-apoyo">{{ $hab->ubicacion ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-bold text-titulo">{{ $hab->capacidad }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold {{ $ecls }}">
                            {{ $hab->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold text-estado-exito">{{ $hab->camas_disponibles_count }}</span>
                        <span class="text-apoyo">/{{ $hab->camas_count }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            @can('habitaciones.editar')
                            <button wire:click="abrirEditarHabitacion({{ $hab->cod_habitacion }})"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia hover:bg-estado-advertenciaBg transition">
                                <i class="ph-bold ph-pencil text-xs"></i>
                            </button>
                            @endcan
                            @can('camas.crear')
                            <button wire:click="abrirCrearCama({{ $hab->cod_habitacion }})"
                                title="Agregar cama"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-exitoBorde bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg transition">
                                <i class="ph-bold ph-plus text-xs"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($habitaciones->hasPages())
    <div class="border-t border-borde-suave px-5 py-3">{{ $habitaciones->links() }}</div>
    @endif
    @endif
</section>

</div>

{{-- MODAL: HABITACIÓN --}}
@if($modalHabitacion)
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
     style="background: rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl my-6">
        <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#8DA280]/15 text-[#4A6043]">
                    <i class="ph-bold ph-door text-lg"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Habitación</p>
                    <h3 class="text-sm font-bold text-titulo">{{ $editandoHabitacionId ? 'Editar habitación' : 'Registrar habitación' }}</h3>
                </div>
            </div>
            <button wire:click="cerrarModales"
                class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
        <form wire:submit.prevent="guardarHabitacion" class="space-y-3 p-5">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Código <span class="text-boton-acento">*</span></label>
                    <input wire:model="codigo" type="text" maxlength="20" placeholder="Ej: HAB-01" class="{{ $inputCls }}" />
                    @error('codigo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Nombre</label>
                    <input wire:model="nombre" type="text" maxlength="100" placeholder="Ej: Habitación Norte" class="{{ $inputCls }}" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Tipo <span class="text-boton-acento">*</span></label>
                    <select wire:model="tipoHabitacion" class="{{ $inputCls }}">
                        <option value="">Seleccione...</option>
                        <option value="INDIVIDUAL">Individual</option>
                        <option value="COMPARTIDA">Compartida</option>
                        <option value="UCI">UCI</option>
                        <option value="OBSERVACION">Observación</option>
                    </select>
                    @error('tipoHabitacion') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Capacidad <span class="text-boton-acento">*</span></label>
                    <input wire:model="capacidad" type="number" min="1" max="50" class="{{ $inputCls }}" />
                    @error('capacidad') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Ubicación</label>
                    <input wire:model="ubicacion" type="text" maxlength="100" placeholder="Planta, ala..." class="{{ $inputCls }}" />
                </div>
                <div>
                    <label class="{{ $labelCls }}">Estado</label>
                    <select wire:model="estadoHab" class="{{ $inputCls }}">
                        <option value="DISPONIBLE">Disponible</option>
                        <option value="OCUPADA">Ocupada</option>
                        <option value="MANTENIMIENTO">Mantenimiento</option>
                        <option value="BLOQUEADA">Bloqueada</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="{{ $labelCls }}">Observación</label>
                <input wire:model="observacion" type="text" maxlength="200" class="{{ $inputCls }}" />
            </div>
            <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-3">
                <button type="button" wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-4 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-bold text-inverso shadow-sm hover:shadow-md active:scale-95 transition">
                    <i class="ph-bold ph-floppy-disk text-sm"></i>
                    {{ $editandoHabitacionId ? 'Guardar cambios' : 'Registrar' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL: CAMA --}}
@if($modalCama)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="w-full max-w-md overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <h3 class="text-sm font-bold text-titulo"><i class="ph-bold ph-bed mr-2"></i>Registrar cama</h3>
            <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
        <form wire:submit.prevent="guardarCama" class="space-y-3 p-5">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Código cama <span class="text-boton-acento">*</span></label>
                    <input wire:model="codigoCama" type="text" maxlength="20" placeholder="Ej: C1, B2..." class="{{ $inputCls }}" />
                    @error('codigoCama') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Estado</label>
                    <select wire:model="estadoCama" class="{{ $inputCls }}">
                        <option value="DISPONIBLE">Disponible</option>
                        <option value="OCUPADA">Ocupada</option>
                        <option value="MANTENIMIENTO">Mantenimiento</option>
                        <option value="BLOQUEADA">Bloqueada</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="{{ $labelCls }}">Observación</label>
                <input wire:model="observacionCama" type="text" maxlength="200" class="{{ $inputCls }}" />
            </div>
            <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-3">
                <button type="button" wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-4 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-bold text-inverso shadow-sm hover:shadow-md active:scale-95 transition">
                    <i class="ph-bold ph-floppy-disk text-sm"></i> Registrar cama
                </button>
            </div>
        </form>
    </div>
</div>
@endif

</div>
