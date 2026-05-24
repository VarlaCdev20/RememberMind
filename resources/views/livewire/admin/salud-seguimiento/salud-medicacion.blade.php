<div class="min-h-screen py-8 font-sans antialiased text-azul-profundo">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ═══════════════════════════════════════════════════
             CABECERA DEL PANEL
        ════════════════════════════════════════════════════ --}}
        @include('livewire.admin.salud-seguimiento._parciales.cabecera-panel', [
            'titulo'    => 'Medicación',
            'subtitulo' => 'Tratamientos farmacológicos activos, suspendidos e historial del paciente.',
            'icono'     => 'ph-pill',
            'rutaVolver'=> route('admin.salud-seguimiento.resumen', $adulto->cod_am),
            'adulto'    => $adulto,
        ])

        {{-- ═══════════════════════════════════════════════════
             MÉTRICAS RÁPIDAS (se poblarán con datos reales)
        ════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <x-ui.metric-card etiqueta="Activos"   valor="—" icono="ph-check-circle"  color-valor="text-emerald-600" />
            <x-ui.metric-card etiqueta="Suspendidos" valor="—" icono="ph-pause-circle" color-valor="text-amber-600" />
            <x-ui.metric-card etiqueta="Finalizados" valor="—" icono="ph-x-circle"    color-valor="text-azul-profundo/50" />
            <x-ui.metric-card etiqueta="Archivados"  valor="—" icono="ph-archive"     color-valor="text-azul-profundo/40" />
        </div>

        {{-- ═══════════════════════════════════════════════════
             ÁREA DE CONTENIDO PRINCIPAL
             TODO (Fase siguiente): Implementar tabla de medicaciones,
             modal de registro y acciones de suspender/finalizar
        ════════════════════════════════════════════════════ --}}
        <div class="rm-card p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-base font-black uppercase tracking-wider text-azul-profundo">
                    Tratamientos registrados
                </h2>
                @can('salud.medicacion.crear')
                    <button type="button" disabled
                        class="rm-btn-terracota opacity-60 cursor-not-allowed"
                        title="Disponible en la próxima versión">
                        <i class="ph-bold ph-plus"></i> Nueva medicación
                    </button>
                @endcan
            </div>

            {{-- Tabla vacía con estructura lista --}}
            <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/30">
                <table class="rm-table">
                    <thead class="rm-table-header">
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosis</th>
                            <th>Frecuencia</th>
                            <th>Inicio</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <x-ui.empty-state
                                    icono="ph-pill"
                                    titulo="Módulo en preparación"
                                    texto="La gestión de medicación estará disponible próximamente. Las tablas y relaciones ya están listas."
                                    class="border-0 py-0 bg-transparent shadow-none"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════
             HISTORIAL (sección preparada)
        ════════════════════════════════════════════════════ --}}
        <div class="rm-card p-6">
            <h2 class="text-sm font-black uppercase tracking-wider text-azul-profundo/60 mb-4">
                Historial de medicación
            </h2>
            <x-ui.empty-state
                icono="ph-clock-clockwise"
                titulo="Sin historial disponible"
                texto="El historial de medicaciones suspendidas, finalizadas y archivadas aparecerá aquí."
                class="border-0 bg-[#F7F5F2] py-10 shadow-none"
            />
        </div>

    </div>
</div>
