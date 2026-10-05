{{-- Vista: Gestión de Turnos de Enfermería --}}
@php
    $inputCls = 'rm-input w-full text-xs font-medium';
    $labelCls = 'block text-xs font-bold text-[var(--rm-text-secondary)] mb-1';
    $errCls   = 'mt-1 text-xs font-bold text-[var(--rm-danger)]';
@endphp
<div class="space-y-6 max-w-5xl mx-auto font-sans" x-data @keydown.window.escape="$wire.cerrarModales()">
    <x-ui.page-header
    title="Turnos de Enfermería"
    subtitle="Configuración de turnos institucionales: Mañana, Tarde, Noche, Madrugada."
    overline="Gestión de turnos"
    icon="ph-clock"
    :date="now()">
    @can('turnos.gestionar')
    <button type="button" wire:click="abrirCrear" class="rm-btn rm-btn-primary">
        <i class="ph-bold ph-plus-circle text-base"></i>
        <span>Nuevo turno</span>
    </button>
    @endcan
</x-ui.page-header>

    <section class="overflow-hidden rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-sm">
        <div class="border-b border-[var(--rm-border)] px-5 py-3.5 bg-[var(--rm-surface-soft)]">
            <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-[var(--rm-text-primary)]">Turnos configurados</h2>
        </div>
        @if($turnos->isEmpty())
        <div class="flex flex-col items-center gap-4 py-14 text-center">
            <i class="ph-bold ph-clock text-4xl text-[var(--rm-text-muted)]"></i>
            <p class="text-sm font-bold text-[var(--rm-text-secondary)]">Sin turnos registrados. Ejecute el seeder TurnosEnfermeriaSeeder.</p>
        </div>
        @else
        <div class="divide-y divide-[var(--rm-border)]/40">
            @foreach($turnos as $t)
            <div class="flex items-center justify-between px-5 py-4 hover:bg-[var(--rm-surface-soft)]/50 transition">
                <div class="flex items-center gap-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $t->estado === 'ACTIVO' ? 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)]' }}">
                        <i class="ph-bold ph-clock text-xl"></i>
                    </span>
                    <div>
                        <p class="font-black text-sm text-[var(--rm-text-primary)]">{{ $t->nombre }}</p>
                        <p class="text-xs font-semibold text-[var(--rm-text-secondary)]">{{ substr($t->hora_inicio,0,5) }} — {{ substr($t->hora_fin,0,5) }} · Orden: {{ $t->orden }}</p>
                        @if($t->observacion)<p class="text-xs text-[var(--rm-text-muted)]">{{ $t->observacion }}</p>@endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $t->estado === 'ACTIVO' ? 'border-[var(--rm-action-primary)]/40 bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]' : 'border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)]' }}">
                        {{ $t->estado }}
                    </span>
                    @can('turnos.gestionar')
                    <button wire:click="abrirEditar('{{ $t->cod_turno }}')"
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] hover:border-[var(--rm-border-hover)] transition" title="Editar turno">
                        <i class="ph-bold ph-pencil text-sm"></i>
                    </button>
                    @endcan
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    @if($modalTurno)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" wire:click.self="cerrarModales">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xl">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] px-5 py-4">
                <h3 class="text-sm font-bold text-[var(--rm-text-primary)]">{{ $editandoId ? 'Editar turno' : 'Nuevo turno' }}</h3>
                <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-[var(--rm-border)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] transition"><i class="ph-bold ph-x text-sm"></i></button>
            </div>
            <form wire:submit.prevent="guardar" class="space-y-4 p-5">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelCls }}">Nombre <span class="text-[var(--rm-danger)]">*</span></label>
                        <input wire:model="nombre" type="text" maxlength="50" placeholder="MAÑANA" class="{{ $inputCls }}" />
                        @error('nombre') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Orden <span class="text-[var(--rm-danger)]">*</span></label>
                        <input wire:model="orden" type="number" min="1" max="10" class="{{ $inputCls }}" />
                        @error('orden') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelCls }}">Hora inicio <span class="text-[var(--rm-danger)]">*</span></label>
                        <input wire:model="horaInicio" type="time" class="{{ $inputCls }}" />
                        @error('horaInicio') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Hora fin <span class="text-[var(--rm-danger)]">*</span></label>
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
                <div class="flex justify-end gap-2.5 border-t border-[var(--rm-border)] pt-4">
                    <button type="button" wire:click="cerrarModales" class="rounded-xl border border-[var(--rm-border)] px-4 py-2 text-xs font-bold text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] transition">Cancelar</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-5 py-2 text-xs font-bold text-white shadow-sm transition active:scale-[0.98]">
                        <i class="ph-bold ph-floppy-disk text-sm"></i> {{ $editandoId ? 'Guardar' : 'Registrar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>