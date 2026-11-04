    {{-- 1. CABECERA DEL MÓDULO RESULTADOS Y ESTUDIOS CLÍNICOS                     --}}
    {{-- ========================================================================= --}}
    <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <div class="h-11 w-11 rounded-2xl bg-[var(--rm-action-primary)]/10 dark:bg-[var(--rm-info)] text-[var(--rm-action-primary)] dark:text-[var(--rm-info-strong)] flex items-center justify-center shrink-0 border border-[var(--rm-action-primary)]/20">
                    <i class="ph-bold ph-flask text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm sm:text-base font-bold text-[var(--rm-text-title)] tracking-tight uppercase">
                            RESULTADOS Y ESTUDIOS CLÍNICOS
                        </h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] dark:bg-[var(--rm-info-soft)] dark:text-[var(--rm-info-strong)] border border-[var(--rm-info-strong)] dark:border-[var(--rm-info-strong)]">
                            Diagnóstico y Seguimiento
                        </span>
                    </div>
                    <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                        Seguimiento de pruebas diagnósticas, resultados de laboratorio, estudios de imagen y otros exámenes clínicos.
                    </p>
                </div>
            </div>

            {{-- Acción secundaria / Estado del módulo --}}
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-muted)] flex items-center gap-1.5">
                    <i class="ph-bold ph-check-circle text-[var(--rm-success-strong)]"></i>
                    <span>Expediente al día</span>
                </span>
            </div>
        </div>

        {{-- SUBTABS PRINCIPALES DE FILTRADO CLÍNICO --}}
        <div class="mt-4 pt-3.5 border-t border-[var(--rm-border)]/70 flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
            @php
                $subtabs = [
                    'TODOS' => ['label' => 'Todos', 'icon' => 'ph-list-bullets'],
                    'LABORATORIO' => ['label' => 'Laboratorio', 'icon' => 'ph-flask'],
                    'IMAGEN' => ['label' => 'Imágenes', 'icon' => 'ph-film-strip'],
                    'CARDIOLOGICO' => ['label' => 'Estudios cardiológicos', 'icon' => 'ph-heartbeat'],
                    'OTROS' => ['label' => 'Otros estudios', 'icon' => 'ph-clipboard-text'],
                ];
            @endphp

            @foreach($subtabs as $sKey => $sData)
                @php
                    $activo = $subtabEstudio === $sKey;
                    $conteo = $conteoCategorias[$sKey] ?? 0;
                @endphp
                <button type="button"
                        wire:click="setSubtabEstudio('{{ $sKey }}')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition cursor-pointer shrink-0 {{ $activo ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] border border-[var(--rm-border)]' }}">
                    <i class="ph-bold {{ $sData['icon'] }} text-xs"></i>
                    <span>{{ $sData['label'] }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono {{ $activo ? 'bg-[var(--rm-surface-soft)]/20 text-[var(--rm-text-on-primary)]' : 'bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)]' }}">
                        {{ $conteo }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ========================================================================= --}}
