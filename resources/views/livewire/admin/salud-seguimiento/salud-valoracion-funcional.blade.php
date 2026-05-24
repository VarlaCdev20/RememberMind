<div class="min-h-screen py-8 font-sans antialiased text-azul-profundo">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ═══════════════════════════════════════════════════
             CABECERA DEL PANEL
        ════════════════════════════════════════════════════ --}}
        @include('livewire.admin.salud-seguimiento._parciales.cabecera-panel', [
            'titulo'    => 'Valoración Funcional',
            'subtitulo' => 'Autonomía, nivel de dependencia y riesgo de caída del paciente.',
            'icono'     => 'ph-person-simple-walk',
            'rutaVolver'=> route('admin.salud-seguimiento.resumen', $adulto->cod_am),
            'adulto'    => $adulto,
        ])

        {{-- ═══════════════════════════════════════════════════
             VALORACIÓN VIGENTE — tarjeta resumen
        ════════════════════════════════════════════════════ --}}
        <div class="rm-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-base font-black uppercase tracking-wider text-azul-profundo">
                    Valoración funcional vigente
                </h2>
                @can('salud.valoracion.crear')
                    <button type="button" disabled
                        class="rm-btn-terracota opacity-60 cursor-not-allowed"
                        title="Disponible en la próxima versión">
                        <i class="ph-bold ph-plus"></i> Nueva valoración
                    </button>
                @endcan
            </div>

            {{-- Métricas de la valoración vigente --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 mb-6">
                <x-ui.metric-card etiqueta="Nivel de dependencia" valor="—" icono="ph-wheelchair" color-valor="text-azul-profundo/60" />
                <x-ui.metric-card etiqueta="Riesgo de caída"      valor="—" icono="ph-warning"    color-valor="text-amber-600" />
                <x-ui.metric-card etiqueta="Índice de Barthel"    valor="—" icono="ph-chart-bar"  color-valor="text-azul-profundo/60" />
            </div>

            <x-ui.empty-state
                icono="ph-person-simple-walk"
                titulo="Módulo en preparación"
                texto="La gestión de valoración funcional (escala de Barthel, nivel de dependencia, riesgo de caída) estará disponible próximamente."
                class="border-0 bg-[#F7F5F2] py-12 shadow-none"
            />
        </div>

        {{-- ═══════════════════════════════════════════════════
             HISTORIAL DE VALORACIONES
             TODO (Fase siguiente): Tabla con todas las valoraciones
             históricas, comparación de evolución, gráfico de tendencia.
        ════════════════════════════════════════════════════ --}}
        <div class="rm-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-azul-profundo/60">
                    Historial de valoraciones
                </h2>
            </div>

            <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/30">
                <table class="rm-table">
                    <thead class="rm-table-header">
                        <tr>
                            <th>Fecha</th>
                            <th>Dependencia</th>
                            <th>Riesgo caída</th>
                            <th>Barthel</th>
                            <th>Estado</th>
                            <th>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <x-ui.empty-state
                                    icono="ph-clock-clockwise"
                                    titulo="Sin historial"
                                    texto="Las valoraciones registradas aparecerán aquí."
                                    class="border-0 py-0 bg-transparent shadow-none"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
