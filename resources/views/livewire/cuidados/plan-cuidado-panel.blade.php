<div class="space-y-6">
<x-validation-errors />
    <div class="flex flex-col gap-4 rounded-3xl border border-borde bg-fondo-card p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-meta">Enfermería</p>
            <h1 class="text-2xl font-black text-titulo">Plan de cuidado</h1>
            <p class="text-sm font-semibold text-apoyo">Planes activos, tareas vinculadas y estado de cuidado por adulto mayor.</p>
        </div>
        <button type="button" wire:click="abrirCrear" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-black uppercase tracking-wider text-white shadow-sm">
            Crear plan
        </button>
    </div>

    <div class="grid gap-3 rounded-2xl border border-borde bg-fondo-card p-4 md:grid-cols-2">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar adulto mayor" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
        <select wire:model.live="filtroEstado" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
            <option value="">Todos los estados</option>
            <option value="BORRADOR">Borrador</option>
            <option value="ACTIVO">Activo</option>
            <option value="CERRADO">Cerrado</option>
        </select>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse($planes as $plan)
            <article class="rounded-3xl border border-borde bg-fondo-card p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-titulo">{{ $plan->adultoMayor?->nombres }} {{ $plan->adultoMayor?->ap_paterno }}</h2>
                        <p class="text-xs font-bold uppercase tracking-wider text-meta">{{ $plan->tipo_plan }} v{{ $plan->version }} / {{ $plan->nivel_cuidado }}</p>
                    </div>
                    <span class="rounded-full border border-borde bg-fondo-panel px-3 py-1 text-xs font-black text-parrafo">{{ $plan->estado }}</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-apoyo">{{ $plan->resumen ?: 'Sin resumen clínico registrado.' }}</p>
                <div class="mt-4 grid gap-3 text-xs font-bold text-parrafo sm:grid-cols-3">
                    <div class="rounded-xl bg-fondo-panel p-3"><span class="block text-meta">Inicio</span>{{ optional($plan->fecha_inicio)->format('d/m/Y') }}</div>
                    <div class="rounded-xl bg-fondo-panel p-3"><span class="block text-meta">Fin</span>{{ optional($plan->fecha_fin)->format('d/m/Y') ?: 'Vigente' }}</div>
                    <div class="rounded-xl bg-fondo-panel p-3"><span class="block text-meta">Tareas activas</span>{{ $plan->tareas_activas_count }}</div>
                </div>
                @if($plan->estado === 'BORRADOR') @can('plan_cuidado.editar')<x-secondary-button wire:click="editarBorrador('{{ $plan->cod_plan }}')">Editar / activar borrador</x-secondary-button>@endcan @endif
                @if($plan->estado === 'ACTIVO')
                    <button type="button" wire:click="cerrarPlan('{{ $plan->cod_plan }}')" class="mt-4 rounded-xl border border-borde px-4 py-2 text-xs font-bold text-parrafo">Cerrar plan</button>
                @endif
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-borde bg-fondo-card p-10 text-center text-sm font-bold text-apoyo lg:col-span-2">No hay planes de cuidado registrados.</div>
        @endforelse
    </div>

    {{ $planes->links() }}

    @if($modalForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <form wire:submit.prevent="guardar" class="w-full max-w-3xl space-y-4 rounded-3xl border border-borde bg-fondo-card p-6 shadow-xl">
                <div class="flex items-center justify-between"><h2 class="text-lg font-black text-titulo">Crear plan de cuidado</h2><button type="button" wire:click="cerrarModales" class="text-sm font-bold text-apoyo">Cerrar</button></div>
                <div class="grid gap-3 md:grid-cols-2">
                    <select wire:model="codAm" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"><option value="">Adulto mayor</option>@foreach($adultos as $adulto)<option value="{{ $adulto->cod_am }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>@endforeach</select>
                    <select wire:model="tipoPlan" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"><option value="INICIAL">Inicial</option><option value="AJUSTE">Ajuste</option><option value="REEVALUACION">Reevaluación</option></select>
                    <select wire:model="nivelCuidado" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"><option value="PREVENTIVO">Preventivo</option><option value="ESTANDAR">Estándar</option><option value="INTENSIVO">Intensivo</option><option value="PALIATIVO">Paliativo</option></select>
                    <select wire:model="estadoPlan" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"><option value="BORRADOR">Borrador</option><option value="ACTIVO">Activo</option></select>
                    <input type="date" wire:model="fechaInicio" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm">
                    <input type="date" wire:model="fechaFin" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm">
                </div>
                <textarea wire:model="resumen" rows="4" placeholder="Resumen del plan" class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"></textarea>
                <div class="flex justify-end gap-2"><button type="button" wire:click="cerrarModales" class="rounded-xl border border-borde px-4 py-2 text-xs font-bold">Cancelar</button><button type="submit" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-black text-white">Guardar</button></div>
            </form>
        </div>
    @endif
</div>
