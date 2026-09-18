{{-- CABECERA CLÍNICA CONFORME A LA REFERENCIA VISUAL OFICIAL --}}
<div class="space-y-4">

    {{-- B. ENCABEZADO PRINCIPAL Y C. ACCIONES SUPERIORES DERECHA --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            {{-- Breadcrumb pequeño --}}
            <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                <span>{{ (Auth::user()?->hasRole('MEDICO GENERAL/GERIATRA') || Auth::user()?->can('valoracion_medica.ver')) ? 'ÁREA MÉDICA Y CLÍNICA' : 'ENFERMERÍA Y CUIDADOS' }}</span>
                <span class="text-[var(--rm-border)]">•</span>
                <span>{{ \Carbon\Carbon::now()->translatedFormat('l j \d\e F') }}</span>
            </div>
            {{-- Título grande y subtítulo --}}
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[var(--rm-text-title)] mt-0.5">
                Ficha Médica del Residente
            </h1>
            <p class="text-xs sm:text-sm text-[var(--rm-text-body)]">
                Una visión completa para un cuidado más humano
            </p>
        </div>

        {{-- C. Acciones superiores derecha --}}
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            {{-- Botón Imprimir --}}
            <button type="button"
                    onclick="window.print()"
                    class="rm-btn-secondary h-10 px-3.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition shadow-xs">
                <i class="ph-bold ph-printer text-base text-[var(--rm-text-muted)]"></i>
                <span>Imprimir</span>
            </button>

            {{-- Botón Exportar (Modal en página conforme al Design System) --}}
            <button type="button"
                    @click="modalExportar = true"
                    class="rm-btn-secondary h-10 px-3.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition shadow-xs cursor-pointer">
                <i class="ph-bold ph-download-simple text-base text-[#1E3A8A]"></i>
                <span>Exportar</span>
            </button>

            {{-- Botón ícono de más opciones [...] --}}
            <div class="relative" x-data="{ openMas: false }" @click.outside="openMas = false">
                <button type="button"
                        @click="openMas = !openMas"
                        class="rm-btn-secondary h-10 w-10 p-0 rounded-xl inline-flex items-center justify-center transition shadow-xs"
                        title="Más opciones">
                    <i class="ph-bold ph-dots-three text-lg text-[var(--rm-text-muted)]"></i>
                </button>

                <div x-show="openMas"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                     x-cloak
                     class="absolute right-0 mt-1.5 w-56 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-beige)] p-1.5 shadow-xl z-50 text-xs">
                    <button type="button"
                            @click="openMas = false; drawerExpediente = true"
                            class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-soft)] font-medium transition cursor-pointer">
                        <i class="ph-bold ph-identification-card text-blue-600 text-sm"></i>
                        <span>Expediente Integral</span>
                    </button>
                    <button type="button"
                            @click="openMas = false; drawerFamilia = true"
                            class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-soft)] font-medium transition cursor-pointer">
                        <i class="ph-bold ph-users-three text-emerald-600 text-sm"></i>
                        <span>Red de apoyo familiar</span>
                    </button>
                    <button type="button"
                            @click="openMas = false; activeTab = 'documentos'; $wire.cambiarTab('documentos')"
                            class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-soft)] font-medium transition cursor-pointer">
                        <i class="ph-bold ph-folder-open text-amber-600 text-sm"></i>
                        <span>Gestión documental</span>
                    </button>
                    <button type="button"
                            @click="openMas = false; modalExportar = true"
                            class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-soft)] font-medium transition cursor-pointer">
                        <i class="ph-bold ph-file-pdf text-rose-600 text-sm"></i>
                        <span>Informes clínicos PDF</span>
                    </button>
                </div>
            </div>

            {{-- Botón principal azul oscuro: [ + REGISTRAR ATENCIÓN ] (Abre Modal Central Clínico) --}}
            <div>
                <button type="button"
                        @click="modalSelectorAtencion = true"
                        class="bg-[#1E3A8A] hover:bg-[#172554] text-white shadow-sm font-black text-xs rounded-xl px-4 py-2.5 h-10 inline-flex items-center gap-2 transition focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    <span>+ REGISTRAR ATENCIÓN</span>
                </button>
            </div>
        </div>
    </div>

    {{-- 2. CARD SUPERIOR DEL RESIDENTE (FRANJA CLÍNICA ÚNICA CON DOS DIVISIONES SUAVES) --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-beige)] p-4 sm:p-5 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">

            {{-- LADO IZQUIERDO (Datos Biométricos y de Ubicación) --}}
            <div class="lg:col-span-7 flex items-start gap-4 min-w-0">
                {{-- Foto / Avatar --}}
                @if($adultoMayor->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($adultoMayor->foto))
                    <img src="{{ asset('storage/' . $adultoMayor->foto) }}"
                         alt="{{ $adultoMayor->nombres }}"
                         class="h-16 w-16 sm:h-[72px] sm:w-[72px] rounded-2xl object-cover border-2 border-[var(--rm-border)] shadow-sm shrink-0">
                @else
                    <div class="flex h-16 w-16 sm:h-[72px] sm:w-[72px] items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-[#1E3A8A] dark:text-blue-300 border-2 border-blue-200 dark:border-blue-900/60 font-black text-xl shadow-sm shrink-0">
                        {{ strtoupper(substr($adultoMayor->nombres, 0, 1) . substr($adultoMayor->ap_paterno, 0, 1)) }}
                    </div>
                @endif

                <div class="min-w-0 space-y-1">
                    {{-- Nombre Completo --}}
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--rm-text-title)] truncate">
                        {{ $adultoMayor->nombre_completo }}
                    </h2>

                    {{-- Edad, Fecha de Nacimiento, Código AM, Habitación, Cama --}}
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-[var(--rm-text-body)]">
                        <span><strong>{{ $adultoMayor->edad_texto }}</strong> ({{ $adultoMayor->fecha_nac ? \Carbon\Carbon::parse($adultoMayor->fecha_nac)->format('d/m/Y') : 'Sin fecha nac.' }})</span>
                        <span class="text-[var(--rm-border)]">•</span>
                        <span>Código AM: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->cod_am }}</strong></span>
                        <span class="text-[var(--rm-border)]">•</span>
                        <span>Habitación: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->habitacion_texto }}</strong></span>
                        <span class="text-[var(--rm-border)]">•</span>
                        <span>Cama: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->cama_texto }}</strong></span>
                    </div>

                    {{-- Badges o chips debajo del nombre --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        {{-- Badge Estado / Vigilancia --}}
                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 text-xs font-bold text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            <span>{{ $adultoMayor->estado_humano ?: 'Vigilancia' }}</span>
                        </span>

                        {{-- Nivel de cuidado --}}
                        <span class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-soft)] px-2.5 py-0.5 text-xs font-semibold text-[var(--rm-text-title)] border border-[var(--rm-border)]">
                            <i class="ph-bold ph-shield-check text-blue-600"></i>
                            <span>Nivel de cuidado: {{ $adultoMayor->nivel_cuidado ?: 'Intermedio' }}</span>
                        </span>

                        {{-- Contador de alertas activas --}}
                        @php $totalAlertasAct = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(); @endphp
                        @if($totalAlertasAct > 0)
                            <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 px-2.5 py-0.5 text-xs font-bold text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60">
                                <i class="ph-bold ph-warning"></i>
                                <span>{{ $totalAlertasAct }} alertas activas</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/60">
                                <i class="ph-bold ph-check-circle"></i>
                                <span>0 alertas activas</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- LADO DERECHO DEL MISMO BLOQUE (Cuatro Mini Bloques de Información) --}}
            <div class="lg:col-span-5 lg:border-l lg:border-[var(--rm-border)] lg:pl-5">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    {{-- Mini bloque 1: Alergias --}}
                    <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Alergias</span>
                        <span class="text-xs font-bold text-rose-700 dark:text-rose-400 mt-0.5 truncate block" title="{{ $adultoMayor->alergias ?: 'Sin alergias' }}">
                            {{ $adultoMayor->alergias ?: 'Sin alergias conocidas' }}
                        </span>
                    </div>

                    {{-- Mini bloque 2: Grupo sanguíneo --}}
                    <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Grupo sanguíneo</span>
                        <span class="text-xs font-black text-[var(--rm-text-title)] mt-0.5 block">
                            {{ $adultoMayor->grupo_sanguineo ? $adultoMayor->grupo_sanguineo . ($adultoMayor->factor_rh ?: '+') : 'No def.' }}
                        </span>
                    </div>

                    {{-- Mini bloque 3: Seguro de salud --}}
                    <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Seguro de salud</span>
                        <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 truncate block" title="{{ $adultoMayor->seguro_salud ?: 'Particular' }}">
                            {{ $adultoMayor->seguro_salud ?: 'Particular / No reg.' }}
                        </span>
                    </div>

                    {{-- Mini bloque 4: Contacto de emergencia --}}
                    <div class="p-2.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">Contacto de emergencia</span>
                        <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 truncate block" title="{{ $adultoMayor->contacto_emergencia_nombre }} ({{ $adultoMayor->contacto_emergencia_celular }})">
                            {{ $adultoMayor->contacto_emergencia_nombre ? $adultoMayor->contacto_emergencia_nombre . ' (' . ($adultoMayor->contacto_emergencia_celular ?: 's/n') . ')' : 'Sin contacto' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- 3. BARRA DE TABS PRINCIPAL (UNA SOLA FILA, RESALTADO CLARO CON LÍNEA AZUL) --}}
    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar border-b border-[var(--rm-border)] pb-1 text-xs">
        {{-- 1. Resumen clínico --}}
        <button type="button"
                @click="activeTab = 'resumen'; $wire.cambiarTab('resumen')"
                :class="activeTab === 'resumen' ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0">
            <i class="ph-bold ph-clipboard-text text-sm"></i>
            <span>Resumen clínico</span>
        </button>

        {{-- 2. Evolución --}}
        <button type="button"
                @click="activeTab = 'seguimiento'; $wire.cambiarTab('seguimiento'); window.dispatchEvent(new CustomEvent('render-graficos-seguimiento'));"
                :class="activeTab === 'seguimiento' ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0">
            <i class="ph-bold ph-chart-line-up text-sm"></i>
            <span>Evolución</span>
            <span class="hidden">Seguimiento</span>
        </button>

        {{-- 3. Signos vitales --}}
        <button type="button"
                @click="activeTab = 'signos'; $wire.cambiarTab('signos'); window.dispatchEvent(new CustomEvent('render-graficos-signos'));"
                :class="activeTab === 'signos' ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0">
            <i class="ph-bold ph-heartbeat text-sm"></i>
            <span>Signos vitales</span>
        </button>

        {{-- 4. Medicaciones --}}
        <button type="button"
                @click="activeTab = 'medicacion'; $wire.cambiarTab('medicacion')"
                :class="activeTab === 'medicacion' ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0">
            <i class="ph-bold ph-pill text-sm"></i>
            <span>Medicaciones</span>
            <span class="hidden">Medicación</span>
        </button>

        {{-- 5. Cuidados --}}
        <button type="button"
                @click="activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                :class="(activeTab === 'cuidados' || activeTab === 'cuidado') ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0">
            <i class="ph-bold ph-hand-heart text-sm"></i>
            <span>Cuidados</span>
        </button>

        {{-- 6. Eventos clínicos --}}
        <button type="button"
                @click="activeTab = 'eventos'; $wire.cambiarTab('eventos'); window.dispatchEvent(new CustomEvent('render-graficos-eventos'));"
                :class="(activeTab === 'eventos' || activeTab === 'alertas') ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0 cursor-pointer">
            <i class="ph-bold ph-shield-warning text-sm"></i>
            <span>Eventos clínicos</span>
            <span class="sr-only">Alertas</span>
        </button>

        {{-- 7. Resultados y estudios (reemplaza Valoración integral) --}}
        <button type="button"
                @click="activeTab = 'estudios'; $wire.cambiarTab('estudios'); window.dispatchEvent(new CustomEvent('render-graficos-estudios'));"
                :class="(activeTab === 'estudios' || activeTab === 'historial' || activeTab === 'resultados') ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0 cursor-pointer">
            <i class="ph-bold ph-flask text-sm"></i>
            <span>Resultados y estudios</span>
            <span class="sr-only">Historial 360°</span>
        </button>

        {{-- 8. Documentos --}}
        <button type="button"
                @click="activeTab = 'documentos'; $wire.cambiarTab('documentos')"
                :class="activeTab === 'documentos' ? 'border-b-2 border-[#1E3A8A] text-[#1E3A8A] dark:text-blue-400 font-bold bg-[var(--rm-surface-beige)] shadow-xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium hover:bg-[var(--rm-surface-beige)]/50'"
                class="flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 transition whitespace-nowrap shrink-0 cursor-pointer">
            <i class="ph-bold ph-folder text-sm"></i>
            <span>Documentos</span>
        </button>
    </div>

</div>
