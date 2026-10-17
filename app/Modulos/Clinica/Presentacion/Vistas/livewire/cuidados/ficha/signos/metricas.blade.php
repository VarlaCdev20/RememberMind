    {{-- 3. SEGUNDA FILA EXACTA: 6 CARDS CON SPARKLINES (1 FILA COMPLETA)           --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

        {{-- CARD 1: Presión arterial --}}
        <div @click="seleccionarMetrica('PA')"
             :class="metricaActiva === 'PA'
                ? 'border-[var(--rm-info-strong)] ring-2 ring-[var(--rm-focus-ring-color)] bg-[var(--rm-info-soft)] shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-[var(--rm-info-strong)]'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)] border border-[var(--rm-danger-strong)] flex items-center justify-center text-xs">
                        <i class="ph-bold ph-heartbeat"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Presión art.</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
                      :class="metricasInfo['PA']?.estado_badge || 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]'"
                      x-text="metricasInfo['PA']?.estado || 'Sin datos'">Sin datos</span>
            </div>
            <div>
                <span class="text-base font-bold text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['PA']?.ultimo || '—'">—</span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">mmHg</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasPA" x-ref="sparklinePA" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 2: Frecuencia cardíaca --}}
        <div @click="seleccionarMetrica('FC')"
             :class="metricaActiva === 'FC'
                ? 'border-[var(--rm-info-strong)] ring-2 ring-[var(--rm-focus-ring-color)] bg-[var(--rm-info-soft)] shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-[var(--rm-info-strong)]'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)] border border-[var(--rm-danger-strong)] flex items-center justify-center text-xs">
                        <i class="ph-bold ph-heart"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Frec. card.</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
                      :class="metricasInfo['FC']?.estado_badge || 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]'"
                      x-text="metricasInfo['FC']?.estado || 'Sin datos'">Sin datos</span>
            </div>
            <div>
                <span class="text-base font-bold text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['FC']?.ultimo || '—'">—</span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">lpm</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasFC" x-ref="sparklineFC" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 3: Saturación O₂ --}}
        <div @click="seleccionarMetrica('SPO2')"
             :class="metricaActiva === 'SPO2'
                ? 'border-[var(--rm-info-strong)] ring-2 ring-[var(--rm-focus-ring-color)] bg-[var(--rm-info-soft)] shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-[var(--rm-info-strong)]'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border border-[var(--rm-success-strong)] flex items-center justify-center text-xs">
                        <i class="ph-bold ph-drop"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Saturación O₂</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
                      :class="metricasInfo['SPO2']?.estado_badge || 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]'"
                      x-text="metricasInfo['SPO2']?.estado || 'Sin datos'">Sin datos</span>
            </div>
            <div>
                <span class="text-base font-bold text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['SPO2']?.ultimo || '—'">—</span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">% SpO₂</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasSPO2" x-ref="sparklineSPO2" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 4: Temperatura --}}
        <div @click="seleccionarMetrica('TEMP')"
             :class="metricaActiva === 'TEMP'
                ? 'border-[var(--rm-info-strong)] ring-2 ring-[var(--rm-focus-ring-color)] bg-[var(--rm-info-soft)] shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-[var(--rm-info-strong)]'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] border border-[var(--rm-warning-strong)] flex items-center justify-center text-xs">
                        <i class="ph-bold ph-thermometer"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Temperatura</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
                      :class="metricasInfo['TEMP']?.estado_badge || 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]'"
                      x-text="metricasInfo['TEMP']?.estado || 'Sin datos'">Sin datos</span>
            </div>
            <div>
                <span class="text-base font-bold text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['TEMP']?.ultimo || '—'">
                    —
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">°C</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasTEMP" x-ref="sparklineTEMP" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 5: Frecuencia respiratoria --}}
        <div @click="seleccionarMetrica('FR')"
             :class="metricaActiva === 'FR'
                ? 'border-[var(--rm-info-strong)] ring-2 ring-[var(--rm-focus-ring-color)] bg-[var(--rm-info-soft)] shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-[var(--rm-info-strong)]'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-[var(--rm-info-soft)] text-[var(--rm-info-strong)] border border-[var(--rm-info-strong)] flex items-center justify-center text-xs">
                        <i class="ph-bold ph-wind"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Frec. resp.</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
                      :class="metricasInfo['FR']?.estado_badge || 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]'"
                      x-text="metricasInfo['FR']?.estado || 'Sin datos'">Sin datos</span>
            </div>
            <div>
                <span class="text-base font-bold text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['FR']?.ultimo || '—'">—</span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">rpm</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasFR" x-ref="sparklineFR" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 6: Dolor EVA --}}
        <div @click="seleccionarMetrica('DOLOR')"
             :class="metricaActiva === 'DOLOR'
                ? 'border-[var(--rm-info-strong)] ring-2 ring-[var(--rm-focus-ring-color)] bg-[var(--rm-info-soft)] shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-[var(--rm-info-strong)]'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-border-strong)] flex items-center justify-center text-xs">
                        <i class="ph-bold ph-smiley-meh"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Dolor EVA</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border"
                      :class="metricasInfo['DOLOR']?.estado_badge || 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]'"
                      x-text="metricasInfo['DOLOR']?.estado || 'Leve'">
                    Leve
                </span>
            </div>
            <div>
                <span class="text-base font-bold text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['DOLOR']?.ultimo || '2/10'">
                    2/10
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">Escala 0-10</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasDOLOR" x-ref="sparklineDOLOR" class="w-full h-full"></canvas>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
