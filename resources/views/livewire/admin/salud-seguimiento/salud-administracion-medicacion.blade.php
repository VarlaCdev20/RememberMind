<div class="min-h-screen py-8 font-sans antialiased text-azul-profundo">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ═══════════════════════════════════════════════════
             CABECERA DEL PANEL
        ════════════════════════════════════════════════════ --}}
        @include('livewire.admin.salud-seguimiento._parciales.cabecera-panel', [
            'titulo'    => 'Administración de Medicación',
            'subtitulo' => 'Registro diario de tomas: administradas, omitidas y rechazadas.',
            'icono'     => 'ph-clipboard-text',
            'rutaVolver'=> route('admin.salud-seguimiento.resumen', $adulto->cod_am),
            'adulto'    => $adulto,
        ])

        {{-- ═══════════════════════════════════════════════════
             MÉTRICAS RÁPIDAS
        ════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <x-ui.metric-card etiqueta="Administradas hoy" valor="—" icono="ph-check"       color-valor="text-emerald-600" />
            <x-ui.metric-card etiqueta="Omitidas hoy"      valor="—" icono="ph-minus-circle" color-valor="text-amber-600" />
            <x-ui.metric-card etiqueta="Rechazadas hoy"    valor="—" icono="ph-x-circle"    color-valor="text-rose-600" />
            <x-ui.metric-card etiqueta="Pendientes"        valor="—" icono="ph-clock"        color-valor="text-azul-profundo/60" />
        </div>

        {{-- ═══════════════════════════════════════════════════
             ÁREA DE CONTENIDO PRINCIPAL
             TODO (Fase siguiente): Tabla de administraciones del día,
             formulario rápido de registro de toma, filtro por fecha.
        ════════════════════════════════════════════════════ --}}
        <div class="rm-card p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-base font-black uppercase tracking-wider text-azul-profundo">
                    Registro de administraciones
                </h2>
                @can('salud.administracion.crear')
                    <button type="button" disabled
                        class="rm-btn-terracota opacity-60 cursor-not-allowed"
                        title="Disponible en la próxima versión">
                        <i class="ph-bold ph-plus"></i> Registrar toma
                    </button>
                @endcan
            </div>

            <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/30">
                <table class="rm-table">
                    <thead class="rm-table-header">
                        <tr>
                            <th>Medicamento</th>
                            <th>Hora programada</th>
                            <th>Hora real</th>
                            <th>Estado</th>
                            <th>Observación</th>
                            <th>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <x-ui.empty-state
                                    icono="ph-clipboard-text"
                                    titulo="Módulo en preparación"
                                    texto="El registro de administraciones diarias de medicación estará disponible próximamente."
                                    class="border-0 py-0 bg-transparent shadow-none"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════
             HISTORIAL DE ADMINISTRACIONES
        ════════════════════════════════════════════════════ --}}
        <div class="rm-card p-6">
            <h2 class="text-sm font-black uppercase tracking-wider text-azul-profundo/60 mb-4">
                Historial de administraciones anteriores
            </h2>
            <x-ui.empty-state
                icono="ph-list-bullets"
                titulo="Sin historial disponible"
                texto="Las administraciones anteriores aparecerán aquí una vez que se comience a registrar."
                class="border-0 bg-[#F7F5F2] py-10 shadow-none"
            />
        </div>

    </div>
</div>
