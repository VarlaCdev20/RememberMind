    {{-- 2. KPIS SUPERIORES (5 CARDS COMPACTAS SEGÚN GOLDEN REFERENCE)             --}}
    {{-- ========================================================================= --}}
    <div class="rm-clinical-category-metrics grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

        {{-- Card 1: Total de documentos (Azul) --}}
        <div class="rounded-2xl border border-[var(--rm-info-strong)] bg-[var(--rm-info-soft)] p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-info-strong)] block truncate">
                    Total de documentos
                </span>
                <span class="text-2xl font-bold text-[var(--rm-info-strong)] tracking-tight mt-0.5 block">
                    {{ $metricas['total'] }}
                </span>
                <span class="text-[10.5px] font-medium text-[var(--rm-info-strong)] block mt-0.5">
                    Todos los registros
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-[var(--rm-info-soft)] border border-[var(--rm-info-strong)] text-[var(--rm-info-strong)] flex items-center justify-center shrink-0">
                <i class="ph-bold ph-folder text-xl"></i>
            </div>
        </div>

        {{-- Card 2: Clínicos (Verde) --}}
        <div class="rounded-2xl border border-[var(--rm-success-strong)] bg-[var(--rm-success-soft)] p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-success-strong)] block truncate">
                    Clínicos
                </span>
                <span class="text-2xl font-bold text-[var(--rm-success-strong)] tracking-tight mt-0.5 block">
                    {{ $metricas['clinicos'] }}
                </span>
                <span class="text-[10.5px] font-medium text-[var(--rm-success-strong)] block mt-0.5">
                    Informes y resultados
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-[var(--rm-success-soft)] border border-[var(--rm-success-strong)] text-[var(--rm-success-strong)] flex items-center justify-center shrink-0">
                <i class="ph-bold ph-heartbeat text-xl"></i>
            </div>
        </div>

        {{-- Card 3: Administrativos (Violeta) --}}
        <div class="rounded-2xl border border-[var(--rm-border-strong)] bg-[var(--rm-action-primary-soft)] p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-action-primary)] block truncate">
                    Administrativos
                </span>
                <span class="text-2xl font-bold text-[var(--rm-action-primary)] tracking-tight mt-0.5 block">
                    {{ $metricas['administrativos'] }}
                </span>
                <span class="text-[10.5px] font-medium text-[var(--rm-action-primary)] block mt-0.5">
                    Formularios y autorizaciones
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-[var(--rm-action-primary-soft)] border border-[var(--rm-border-strong)] text-[var(--rm-action-primary)] flex items-center justify-center shrink-0">
                <i class="ph-bold ph-file-text text-xl"></i>
            </div>
        </div>

        {{-- Card 4: Imágenes (Ámbar) --}}
        <div class="rounded-2xl border border-[var(--rm-warning-strong)] bg-[var(--rm-warning-soft)] p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-warning-strong)] block truncate">
                    Imágenes
                </span>
                <span class="text-2xl font-bold text-[var(--rm-warning-strong)] tracking-tight mt-0.5 block">
                    {{ $metricas['imagenes'] }}
                </span>
                <span class="text-[10.5px] font-medium text-[var(--rm-warning-strong)] block mt-0.5">
                    Estudios y fotografías
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-[var(--rm-warning-soft)] border border-[var(--rm-warning-strong)] text-[var(--rm-warning-strong)] flex items-center justify-center shrink-0">
                <i class="ph-bold ph-camera text-xl"></i>
            </div>
        </div>

        {{-- Card 5: Legales (Azul Institucional) --}}
        <div class="rounded-2xl border border-[var(--rm-border-strong)] bg-[var(--rm-action-primary-soft)] p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition col-span-2 sm:col-span-1">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-action-primary)] block truncate">
                    Legales
                </span>
                <span class="text-2xl font-bold text-[var(--rm-action-primary)] tracking-tight mt-0.5 block">
                    {{ $metricas['legales'] }}
                </span>
                <span class="text-[10.5px] font-medium text-[var(--rm-action-primary)] block mt-0.5">
                    Consentimientos
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-[var(--rm-action-primary-soft)] border border-[var(--rm-border-strong)] text-[var(--rm-action-primary)] flex items-center justify-center shrink-0">
                <i class="ph-bold ph-scales text-xl"></i>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
