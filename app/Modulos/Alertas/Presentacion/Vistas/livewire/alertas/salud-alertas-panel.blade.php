<div class="space-y-6">
 <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[var(--rm-warning)] to-[#8DA280]"></div>
 <div class="grid gap-4 p-5 lg:grid-cols-[1fr_auto] lg:items-end">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">Monitoreo preventivo</span>
 <h2 class="mt-1 text-xl font-extrabold tracking-tight text-parrafo">Alertas de salud</h2>
 <p class="mt-1 max-w-2xl text-xs font-bold leading-relaxed text-parrafo/62">
 Priorización cálida de registros pendientes, controles fuera de rango y seguimiento funcional.
 </p>
 </div>

 <div class="grid grid-cols-3 gap-2 sm:min-w-[360px]">
 <div class="rounded-2xl border border-borde/45 bg-fondo-panel p-3 text-center">
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/45">Total</p>
 <p class="mt-1 text-xl font-extrabold text-parrafo">{{ $conteos['total'] ?? count($alertas) }}</p>
 </div>
 <div class="rounded-2xl border border-borde-focus bg-estado-peligroBg p-3 text-center">
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/45">Críticas</p>
 <p class="mt-1 text-xl font-extrabold text-boton-acento">{{ $conteos['criticas'] ?? 0 }}</p>
 </div>
 <div class="rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 text-center">
 <p class="text-[8px] font-black uppercase tracking-wider text-parrafo/45">Preventivas</p>
 <p class="mt-1 text-xl font-extrabold text-parrafo">{{ $conteos['preventivas'] ?? 0 }}</p>
 </div>
 </div>
 </div>
 </section>

     <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Filtro Tipo de alerta --}}
            <div class="lg:col-span-6">
                <select wire:model.live="filtroTipo"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los tipos de alerta</option>
                    <option value="Ficha Médica">Ficha médica</option>
                    <option value="Medicación">Medicación</option>
                    <option value="Signos Vitales">Signos vitales</option>
                    <option value="Valoración">Valoración funcional</option>
                </select>
            </div>
        </div>

        @if(!empty($filtroTipo))
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                        <span>Tipo: {{ $filtroTipo }}</span>
                        <button type="button" wire:click="$set('filtroTipo', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                    </span>
                </div>
                <button type="button" wire:click="$set('filtroTipo', '')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

 <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
 @forelse($alertas as $alerta)
 @php
 $esCritica = $alerta['nivel'] === 'critica';
 $tipo = $alerta['tipo'];
 $icono = match($tipo) {
 'Ficha Médica', 'Ficha Médica' => 'ph-file-dashed',
 'Medicación', 'Medicación' => 'ph-pill',
 'Signos Vitales' => 'ph-activity',
 'Valoración', 'Valoración' => 'ph-person-simple-walk',
 default => 'ph-warning-circle',
 };
 $panel = $esCritica
 ? ['border' => 'border-borde-focus', 'bg' => 'bg-estado-peligroBg', 'text' => 'text-boton-acento', 'badge' => 'Crítica', 'iconBg' => 'bg-estado-peligroBg']
 : ['border' => 'border-estado-advertenciaBorde', 'bg' => 'bg-estado-advertenciaBg', 'text' => 'text-parrafo', 'badge' => 'Preventiva', 'iconBg' => 'bg-estado-advertenciaBg'];
 @endphp

 <article class="group overflow-hidden rounded-[1.55rem] border {{ $panel['border'] }} {{ $panel['bg'] }} shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_38px_rgba(47,62,92,0.14)]">
 <div class="flex items-start gap-4 border-b border-borde-suave bg-fondo-panel p-5">
 <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $panel['iconBg'] }} {{ $panel['text'] }}">
 <i class="ph-bold {{ $icono }} text-2xl"></i>
 </span>
 <div class="min-w-0 flex-1">
 <div class="flex flex-wrap items-center gap-2">
 <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-[8px] font-black uppercase tracking-wider text-apoyo">{{ $tipo }}</span>
 <span class="rounded-full px-2.5 py-1 text-[8px] font-black uppercase tracking-wider {{ $panel['iconBg'] }} {{ $panel['text'] }}">{{ $panel['badge'] }}</span>
 </div>
 <h3 class="mt-2 truncate text-base font-extrabold text-parrafo">
 {{ $alerta['adulto']->nombres }} {{ $alerta['adulto']->ap_paterno }}
 </h3>
 <p class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-parrafo/42">{{ $alerta['adulto']->edad ?? 'Edad no registrada' }}{{ $alerta['adulto']->edad ? ' años' : '' }}</p>
 </div>
 </div>

 <div class="space-y-4 p-5">
 <p class="text-sm font-bold leading-relaxed text-parrafo/78">{{ $alerta['mensaje'] }}</p>
 <div class="rounded-2xl border border-borde/35 bg-fondo-panel px-4 py-3">
 <p class="text-[9px] font-bold uppercase tracking-widestá text-parrafo/45">Acción sugerida</p>
 <p class="mt-1 text-xs font-bold uppercase text-parrafo">{{ $alerta['accion'] }}</p>
 </div>
 <a href="{{ $alerta['ruta'] }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-[10px] font-bold uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrow-right"></i>
 Atender alerta
 </a>
 </div>
 </article>
 @empty
 <div class="col-span-full rounded-[1.6rem] border border-estado-exitoBorde bg-estado-exitoBg p-12 text-center shadow-sm">
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg text-estado-exito">
 <i class="ph-bold ph-check-circle text-4xl"></i>
 </div>
 <h3 class="mt-4 text-lg font-extrabold text-parrafo">Sin alertas activas</h3>
 <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-relaxed text-apoyo">
 Los expedientes revisados se encuentran al día según los criterios actuales.
 </p>
 </div>
 @endforelse
 </section>
</div>
