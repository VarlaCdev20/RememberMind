{{-- Vista: Gestión de Turnos de Enfermería --}}
@php
    $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20';
    $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1';
    $errCls   = 'mt-1 text-[10px] font-bold text-boton-acento';
@endphp
<div class="min-h-screen bg-fondo-panel px-4 py-5 sm:px-6 lg:px-8" x-data @keydown.window.escape="$wire.cerrarModales()">
<div class="mx-auto max-w-4xl space-y-5">
<section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-lg">
    <div class="h-1.5 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
    <div class="flex items-center justify-between p-5 sm:p-7">
        <div>
            <h1 class="text-2xl font-black text-titulo">Turnos de Enfermería</h1>
            <p class="mt-1 text-sm font-bold text-apoyo">Configuración de turnos institucionales: Mañana, Tarde, Noche, Madrugada.</p>
        </div>
        @can('turnos_enfermeria.crear')
        <button wire:click="abrirCrear" class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold text-inverso shadow-sm hover:shadow-md transition active:scale-95">
            <i class="ph-bold ph-plus text-sm"></i> Nuevo turno
        </button>
        @endcan
    </div>
</section>
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="border-b border-borde-suave px-5 py-3.5">
        <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">Turnos configurados</h2>
    </div>
    @if($turnos->isEmpty())
    <div class="flex flex-col items-center gap-4 py-14 text-center">
        <i class="ph-bold ph-clock text-4xl text-apoyo"></i>
        <p class="text-sm font-bold text-apoyo">Sin turnos registrados. Ejecute el seeder TurnosEnfermeriaSeeder.</p>
    </div>
    @else
    <div class="divide-y divide-[#C7B5A3]/20">
        @foreach($turnos as $t)
        <div class="flex items-center justify-between px-5 py-4 hover:bg-fondo-panel transition">
            <div class="flex items-center gap-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $t->estado === 'ACTIVO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-fondo-panel text-apoyo' }}">
                    <i class="ph-bold ph-clock text-xl"></i>
                </span>
                <div>
                    <p class="font-black text-titulo">{{ $t->nombre }}</p>
                    <p class="text-[11px] font-bold text-apoyo">{{ substr($t->hora_inicio,0,5) }} — {{ substr($t->hora_fin,0,5) }} · Orden: {{ $t->orden }}</p>
                    @if($t->observacion)<p class="text-[10px] text-apoyo">{{ $t->observacion }}</p>@endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold {{ $t->estado === 'ACTIVO' ? 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito' : 'border-borde-suave bg-fondo-panel text-apoyo' }}">
                    {{ $t->estado }}
                </span>
                @can('turnos_enfermeria.editar')
                <button wire:click="abrirEditar({{ $t->cod_turno }})"
                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia hover:bg-estado-advertenciaBg transition">
                    <i class="ph-bold ph-pencil text-xs"></i>
                </button>
                @endcan
            </div>
        </div>
        @endforeach
    </div>
    @endif
</section>
</div>
@if($modalTurno)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="w-full max-w-md overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <h3 class="text-sm font-bold text-titulo">{{ $editandoId ? 'Editar turno' : 'Nuevo turno' }}</h3>
            <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition"><i class="ph-bold ph-x text-sm"></i></button>
        </div>
        <form wire:submit.prevent="guardar" class="space-y-3 p-5">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Nombre <span class="text-boton-acento">*</span></label>
                    <input wire:model="nombre" type="text" maxlength="50" placeholder="MAÑANA" class="{{ $inputCls }}" />
                    @error('nombre') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Orden <span class="text-boton-acento">*</span></label>
                    <input wire:model="orden" type="number" min="1" max="10" class="{{ $inputCls }}" />
                    @error('orden') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Hora inicio <span class="text-boton-acento">*</span></label>
                    <input wire:model="horaInicio" type="time" class="{{ $inputCls }}" />
                    @error('horaInicio') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Hora fin <span class="text-boton-acento">*</span></label>
                    <input wire:model="horaFin" type="time" class="{{ $inputCls }}" />
                    @error('horaFin') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Estado</label>
                    <select wire:model="estado" class="{{ $inputCls }}">
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Observación</label>
                    <input wire:model="observacion" type="text" maxlength="100" class="{{ $inputCls }}" />
                </div>
            </div>
            <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-3">
                <button type="button" wire:click="cerrarModales" class="rounded-xl border border-borde-suave px-4 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">Cancelar</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-bold text-inverso shadow-sm hover:shadow-md active:scale-95 transition">
                    <i class="ph-bold ph-floppy-disk text-sm"></i> {{ $editandoId ? 'Guardar' : 'Registrar' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif
</div>
