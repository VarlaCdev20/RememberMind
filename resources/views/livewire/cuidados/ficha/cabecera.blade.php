{{-- CABECERA INSTITUCIONAL CLÍNICA COMPACTA Y ACCIONES PRINCIPALES --}}
<div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        {{-- Datos Primarios del Residente --}}
        <div class="flex items-start gap-4 min-w-0">
            {{-- Avatar / Foto (72x72) --}}
            @if($adultoMayor->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($adultoMayor->foto))
                <img src="{{ asset('storage/' . $adultoMayor->foto) }}"
                     alt="{{ $adultoMayor->nombres }}"
                     class="h-16 w-16 sm:h-[72px] sm:w-[72px] rounded-2xl object-cover border-2 border-[var(--rm-border)] shadow-sm shrink-0">
            @else
                <div class="flex h-16 w-16 sm:h-[72px] sm:w-[72px] items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-[#1E3A8A] dark:text-blue-300 border-2 border-blue-200 dark:border-blue-900/60 font-black text-xl shadow-sm shrink-0">
                    {{ strtoupper(substr($adultoMayor->nombres, 0, 1) . substr($adultoMayor->ap_paterno, 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0 space-y-1.5">
                {{-- Nombre completo y estado clínico --}}
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[var(--rm-text-title)]">
                        {{ $adultoMayor->nombre_completo }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $adultoMayor->estado_badge_color }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        <span>Estado: {{ $adultoMayor->estado_humano }}</span>
                    </span>
                </div>

                {{-- Biometría y Ubicación (Edad, cod_am, habitación, cama) --}}
                <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-[var(--rm-text-body)]">
                    <span><strong>{{ $adultoMayor->edad_texto }}</strong> ({{ $adultoMayor->fecha_nac ? \Carbon\Carbon::parse($adultoMayor->fecha_nac)->format('d/m/Y') : 'Sin fecha nac.' }})</span>
                    <span class="text-[var(--rm-border)]">•</span>
                    <span>Código: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->cod_am }}</strong></span>
                    <span class="text-[var(--rm-border)]">•</span>
                    <span>Habitación: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->habitacion_texto }}</strong></span>
                    <span class="text-[var(--rm-border)]">•</span>
                    <span>Cama: <strong class="text-[var(--rm-text-title)]">{{ $adultoMayor->cama_texto }}</strong></span>
                </div>

                {{-- Badges Clínicos Esenciales: Nivel de cuidado, Alergias, Grupo sanguíneo, Seguro, Contacto de emergencia --}}
                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                    {{-- Nivel de cuidado --}}
                    <span class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-text-title)] border border-[var(--rm-border)]">
                        <i class="ph-bold ph-shield-check text-blue-600"></i>
                        <span>Nivel: {{ $adultoMayor->nivel_cuidado ?: 'Cuidado Asistido' }}</span>
                    </span>

                    {{-- Alergias --}}
                    @if(!empty($adultoMayor->alergias))
                        <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1 text-xs font-bold text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60" title="Alergias conocidas">
                            <i class="ph-bold ph-warning"></i>
                            <span>Alergias: {{ $adultoMayor->alergias }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs text-[var(--rm-text-muted)] border border-[var(--rm-border)]">
                            <i class="ph-bold ph-check text-emerald-600"></i> Sin alergias registradas
                        </span>
                    @endif

                    {{-- Grupo sanguíneo --}}
                    <span class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-text-title)] border border-[var(--rm-border)]">
                        <i class="ph-bold ph-drop text-rose-600"></i>
                        <span>Grupo: {{ $adultoMayor->grupo_sanguineo ? $adultoMayor->grupo_sanguineo . ($adultoMayor->factor_rh ?: '+') : 'No def.' }}</span>
                    </span>

                    {{-- Seguro --}}
                    <span class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-text-title)] border border-[var(--rm-border)]" title="{{ $adultoMayor->seguro_salud ?: 'Particular' }}">
                        <i class="ph-bold ph-identification-badge text-indigo-600"></i>
                        <span>Seguro: {{ $adultoMayor->seguro_salud ?: 'Particular' }}</span>
                    </span>

                    {{-- Contacto de emergencia --}}
                    @if($adultoMayor->contacto_emergencia_nombre)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-text-title)] border border-[var(--rm-border)]" title="Contacto de emergencia">
                            <i class="ph-bold ph-phone text-emerald-600"></i>
                            <span>Emergencia: {{ $adultoMayor->contacto_emergencia_nombre }} ({{ $adultoMayor->contacto_emergencia_celular ?: 'Sin tel.' }})</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ACCIONES PRINCIPALES Y SECUNDARIAS --}}
        <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0 shrink-0">
            {{-- ACCIÓN PRINCIPAL EN AZUL OSCURO: [ Registrar atención ] --}}
            <div class="relative" x-data="{ openAtencion: false }" @click.outside="openAtencion = false">
                <button type="button"
                        @click="openAtencion = !openAtencion"
                        class="bg-[#1E3A8A] hover:bg-[#172554] text-white shadow-sm font-bold text-xs rounded-xl px-4 py-2.5 h-10 inline-flex items-center gap-2 transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    <span>Registrar atención</span>
                    <i class="ph-bold ph-caret-down text-xs transition-transform duration-150" :class="openAtencion ? 'rotate-180' : ''"></i>
                </button>

                {{-- Dropdown de opciones clínicas --}}
                <div x-show="openAtencion"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                     x-cloak
                     class="absolute right-0 mt-1.5 w-64 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-1.5 shadow-xl z-50 divide-y divide-[var(--rm-border)]/50 text-xs">
                    <div class="p-1 text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                        Registro Asistencial y Clínico
                    </div>
                    <div class="py-1 space-y-0.5">
                        <button type="button"
                                wire:click="abrirModalSignos"
                                @click="openAtencion = false"
                                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                            <i class="ph-bold ph-heartbeat text-rose-500 text-sm"></i>
                            <span>Registrar signos</span>
                        </button>
                        <button type="button"
                                wire:click="abrirModalMedicacion"
                                @click="openAtencion = false"
                                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                            <i class="ph-bold ph-pill text-emerald-600 text-sm"></i>
                            <span>Administrar medicación</span>
                        </button>
                        <button type="button"
                                wire:click="abrirModalSeguimiento"
                                @click="openAtencion = false"
                                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                            <i class="ph-bold ph-clipboard-text text-sky-600 text-sm"></i>
                            <span>Registrar seguimiento</span>
                        </button>
                        <button type="button"
                                wire:click="abrirModalIncidente"
                                @click="openAtencion = false"
                                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 font-bold transition">
                            <i class="ph-bold ph-warning-octagon text-rose-600 text-sm"></i>
                            <span>Reportar incidente</span>
                        </button>
                    </div>
                    <div class="py-1 space-y-0.5">
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'DOLOR']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                            <i class="ph-bold ph-smiley-sad text-amber-500 text-sm"></i>
                            <span>Escala de dolor</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'PROCEDIMIENTO']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                            <i class="ph-bold ph-bandaids text-cyan-500 text-sm"></i>
                            <span>Procedimientos clínicos</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- ACCIONES SECUNDARIAS: [ Imprimir ] [ Exportar ] [ ... ] --}}
            <button type="button"
                    onclick="window.print()"
                    class="rm-btn-secondary h-10 px-3.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition">
                <i class="ph-bold ph-printer text-sm text-[var(--rm-text-muted)]"></i>
                <span>Imprimir</span>
            </button>

            <button type="button"
                    @click="window.open('{{ route('admin.adultos-mayores.show', $adultoMayor->cod_am) }}', '_blank')"
                    class="rm-btn-secondary h-10 px-3.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition">
                <i class="ph-bold ph-download-simple text-sm text-[var(--rm-text-muted)]"></i>
                <span>Exportar</span>
            </button>

            {{-- Menú de más acciones [...] --}}
            <div class="relative" x-data="{ openMas: false }" @click.outside="openMas = false">
                <button type="button"
                        @click="openMas = !openMas"
                        class="rm-btn-secondary h-10 w-10 p-0 rounded-xl inline-flex items-center justify-center transition"
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
                     class="absolute right-0 mt-1.5 w-52 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-1.5 shadow-xl z-50 text-xs">
                    <a href="{{ route('admin.adultos-mayores.show', $adultoMayor->cod_am) }}"
                       class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                        <i class="ph-bold ph-identification-card text-blue-600 text-sm"></i>
                        <span>Expediente integral 360°</span>
                    </a>
                    <a href="{{ route('admin.familia-social.red-apoyo', ['adulto' => $adultoMayor->cod_am]) }}"
                       class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                        <i class="ph-bold ph-users-three text-emerald-600 text-sm"></i>
                        <span>Red de apoyo familiar</span>
                    </a>
                    <a href="{{ route('admin.adultos-mayores.documentos.index', $adultoMayor->cod_am) }}"
                       class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] font-medium transition">
                        <i class="ph-bold ph-folder-open text-amber-600 text-sm"></i>
                        <span>Documentos clínicos</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TABS EN UNA SOLA FILA (8 PESTAÑAS ESTRUCTURALES) --}}
    <div class="border-t border-[var(--rm-border)] pt-2.5 flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs font-semibold">
        {{-- 1. Resumen clínico --}}
        <button type="button"
                @click="activeTab = 'resumen'; $wire.cambiarTab('resumen')"
                :class="activeTab === 'resumen' ? 'bg-[#1E3A8A] text-white shadow-sm font-bold' : 'bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]'"
                class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9">
            <i class="ph-bold ph-clipboard-text text-sm"></i>
            <span>Resumen clínico</span>
        </button>

        {{-- 2. Evolución (Seguimiento) --}}
        <button type="button"
                @click="activeTab = 'seguimiento'; $wire.cambiarTab('seguimiento')"
                :class="activeTab === 'seguimiento' ? 'bg-[#1E3A8A] text-white shadow-sm font-bold' : 'bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]'"
                class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9">
            <i class="ph-bold ph-chart-line-up text-sm"></i>
            <span>Evolución</span>
            <span class="hidden">Seguimiento</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="activeTab === 'seguimiento' ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border border-[var(--rm-border)]'">
                {{ $adultoMayor->seguimientosDiarios->count() }}
            </span>
        </button>

        {{-- 3. Signos vitales --}}
        <button type="button"
                @click="activeTab = 'signos'; $wire.cambiarTab('signos')"
                :class="activeTab === 'signos' ? 'bg-[#1E3A8A] text-white shadow-sm font-bold' : 'bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]'"
                class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9">
            <i class="ph-bold ph-heartbeat text-sm"></i>
            <span>Signos vitales</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="activeTab === 'signos' ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border border-[var(--rm-border)]'">
                {{ $adultoMayor->signosVitales->count() }}
            </span>
        </button>

        {{-- 4. Medicaciones --}}
        <button type="button"
                @click="activeTab = 'medicacion'; $wire.cambiarTab('medicacion')"
                :class="activeTab === 'medicacion' ? 'bg-[#1E3A8A] text-white shadow-sm font-bold' : 'bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]'"
                class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9">
            <i class="ph-bold ph-pill text-sm"></i>
            <span>Medicaciones</span>
            <span class="hidden">Medicación</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="activeTab === 'medicacion' ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border border-[var(--rm-border)]'">
                {{ $adultoMayor->medicaciones->count() }}
            </span>
        </button>

        {{-- 5. Cuidados --}}
        <button type="button"
                @click="activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                :class="(activeTab === 'cuidados' || activeTab === 'cuidado') ? 'bg-[#1E3A8A] text-white shadow-sm font-bold' : 'bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]'"
                class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9">
            <i class="ph-bold ph-hand-heart text-sm"></i>
            <span>Cuidados</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="(activeTab === 'cuidados' || activeTab === 'cuidado') ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border border-[var(--rm-border)]'">
                {{ $adultoMayor->tareasActuales->count() }}
            </span>
        </button>

        {{-- 6. Eventos clínicos (Alertas) --}}
        <button type="button"
                @click="activeTab = 'alertas'; $wire.cambiarTab('alertas')"
                :class="activeTab === 'alertas' ? 'bg-[#1E3A8A] text-white shadow-sm font-bold' : 'bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]'"
                class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9">
            <i class="ph-bold ph-warning-circle text-sm"></i>
            <span>Eventos clínicos</span>
            <span class="hidden">Alertas</span>
            @php $totalAlertasAct = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(); @endphp
            @if($totalAlertasAct > 0)
                <span class="rounded-full px-1.5 py-0.2 text-[10px] font-black bg-rose-500 text-white">
                    {{ $totalAlertasAct }}
                </span>
            @else
                <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                      :class="activeTab === 'alertas' ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border border-[var(--rm-border)]'">
                    0
                </span>
            @endif
        </button>

        {{-- 7. Valoración integral (Historial 360°) --}}
        <button type="button"
                @click="activeTab = 'historial'; $wire.cambiarTab('historial')"
                :class="activeTab === 'historial' ? 'bg-[#1E3A8A] text-white shadow-sm font-bold' : 'bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]'"
                class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9">
            <i class="ph-bold ph-clipboard text-sm"></i>
            <span>Valoración integral</span>
            <span class="hidden">Historial 360°</span>
        </button>

        {{-- 8. Documentos --}}
        <a href="{{ route('admin.adultos-mayores.documentos.index', $adultoMayor->cod_am) }}"
           class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 transition whitespace-nowrap shrink-0 h-9 bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
            <i class="ph-bold ph-folder text-sm"></i>
            <span>Documentos</span>
        </a>
    </div>
</div>
