{{--
    Pattern Canónico: patterns/resident-summary
    Banner de Residente 360: Avatar, datos biométricos, badges de vigilancia y 4 bloques clínicos.
    Consume tokens del Design System: --rm-surface, --rm-border, --rm-text-*, --rm-radius-*.
--}}
@props([
    'adultoMayor',
    'totalAlertas' => 0,
])

<div {{ $attributes->merge(['class' => 'rounded-[var(--rm-card-radius,16px)] border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-[var(--rm-shadow-2xs)] space-y-4']) }}>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">

        {{-- Lado Izquierdo: Avatar + Datos biométricos + Badges --}}
        <div class="lg:col-span-7 flex items-start gap-4 min-w-0">
            @if($adultoMayor->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($adultoMayor->foto))
                <img src="{{ asset('storage/' . $adultoMayor->foto) }}"
                     alt="{{ $adultoMayor->nombres }}"
                     class="h-16 w-16 sm:h-[72px] sm:w-[72px] rounded-2xl object-cover border-2 border-[var(--rm-border)] shadow-xs shrink-0">
            @else
                <div class="flex h-16 w-16 sm:h-[72px] sm:w-[72px] items-center justify-center rounded-2xl border-2 border-[var(--rm-border)] bg-[var(--rm-surface-soft)] font-black text-xl text-[var(--rm-text-muted)] shadow-xs shrink-0">
                    {{ strtoupper(substr($adultoMayor->nombres, 0, 1) . substr($adultoMayor->ap_paterno, 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0 space-y-1">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--rm-text-title)] truncate">
                    {{ $adultoMayor->nombre_completo }}
                </h2>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-[var(--rm-text-muted)]">
                    <span><strong>{{ $adultoMayor->edad_texto }}</strong> ({{ $adultoMayor->fecha_nacimiento?->format('d/m/Y') }})</span>
                    <span class="text-[var(--rm-border)]">•</span>
                    <span>Código: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->cod_residente }}</strong></span>
                    <span class="text-[var(--rm-border)]">•</span>
                    <span>Habitación: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->habitacion?->codigo ?? 'Sin asignar' }}</strong></span>
                    <span class="text-[var(--rm-border)]">•</span>
                    <span>Cama: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->cama?->numero ?? 'Sin cama' }}</strong></span>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 px-2 py-0.5 text-[11px] font-bold">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        <span>{{ $adultoMayor->estado_humano ?: 'Vigilancia' }}</span>
                    </span>

                    <span class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-soft)] text-[var(--rm-text-body)] border border-[var(--rm-border)] px-2 py-0.5 text-[11px] font-semibold">
                        <i class="ph-bold ph-shield-check text-[var(--rm-clinical)]"></i>
                        <span>Nivel de cuidado: {{ $adultoMayor->nivel_cuidado ?: 'Intermedio' }}</span>
                    </span>

                    @if($totalAlertas > 0)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 px-2 py-0.5 text-[11px] font-bold">
                            <i class="ph-bold ph-warning"></i>
                            <span>{{ $totalAlertas }} alertas activas</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 text-[11px] font-bold">
                            <i class="ph-bold ph-check-circle"></i>
                            <span>0 alertas activas</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lado Derecho: 4 Mini Bloques Clínicos --}}
        <div class="lg:col-span-5 lg:border-l lg:border-[var(--rm-border)] lg:pl-5">
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border)]">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Alergias</span>
                    <span class="text-xs font-bold text-rose-700 dark:text-rose-400 mt-0.5 truncate block">
                        {{ $adultoMayor->alergias ?: 'Sin alergias conocidas' }}
                    </span>
                </div>

                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border)]">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Grupo Sanguíneo</span>
                    <span class="text-xs font-black text-[var(--rm-text-title)] mt-0.5 block">
                        {{ $adultoMayor->grupo_sanguineo ? $adultoMayor->grupo_sanguineo . ($adultoMayor->factor_rh ?? '') : 'No registrado' }}
                    </span>
                </div>

                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border)]">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Seguro de Salud</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 truncate block">
                        {{ $adultoMayor->seguro_salud ?: 'Particular / No reg.' }}
                    </span>
                </div>

                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border)]">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Contacto Emergencia</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 truncate block">
                        {{ $adultoMayor->contacto_emergencia ?: 'Sin contacto reg.' }}
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
