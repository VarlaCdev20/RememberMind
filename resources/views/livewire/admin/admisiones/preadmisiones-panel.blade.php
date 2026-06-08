<div class="space-y-6">
    <section class="rounded-xl border border-borde bg-fondo-card p-5 shadow-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Admisiones</p>
                <h1 class="mt-1 text-2xl font-bold text-titulo">Preadmisiones</h1>
                <p class="mt-1 max-w-3xl text-sm text-apoyo">
                    Solicitudes iniciales registradas antes de crear un adulto mayor activo. La salida operativa es preadmision asignada.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('admisiones.crear')
                    <a href="{{ route('admin.admisiones.preadmision') }}" class="inline-flex items-center gap-2 rounded-lg bg-boton-acento px-4 py-2 text-sm font-semibold text-boton-acentoTexto transition hover:bg-boton-acentoHover">
                        <i class="ph ph-plus-circle"></i>
                        Nueva preadmision
                    </a>
                @endcan
                <button wire:click="exportarReportePdf" class="inline-flex items-center gap-2 rounded-lg border border-borde bg-boton-secundario px-4 py-2 text-sm font-semibold text-boton-secundarioTexto transition hover:bg-boton-secundarioHover">
                    <i class="ph ph-file-pdf"></i>
                    Reporte
                </button>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-5">
        @foreach ([
            ['label' => 'Total', 'value' => $metricas['total']],
            ['label' => 'Asignadas', 'value' => $metricas['asignadas']],
            ['label' => 'Alta prioridad', 'value' => $metricas['alta_prioridad']],
            ['label' => 'Docs completos', 'value' => $metricas['con_documentos']],
            ['label' => 'Sin enfermero', 'value' => $metricas['sin_enfermero']],
        ] as $card)
            <div class="rounded-xl border border-borde bg-fondo-card p-4 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-apoyo">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-bold text-titulo">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="rounded-xl border border-borde bg-fondo-card p-5 shadow-card">
        <div class="grid gap-4 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-apoyo">Buscar</label>
                <input type="text" wire:model.live.debounce.350ms="search" placeholder="Codigo, nombre, CI o familiar" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto placeholder-input-placeholder focus:border-input-bordeFocus focus:ring-input-ringFocus">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-apoyo">Estado</label>
                <select wire:model.live="estado" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
                    <option value="">Todos</option>
                    <option value="PREADMISION_ASIGNADA">Preadmision asignada</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-apoyo">Prioridad</label>
                <select wire:model.live="prioridad" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
                    <option value="">Todas</option>
                    @foreach (['BAJA','MEDIA','ALTA','CRITICA'] as $nivel)
                        <option value="{{ $nivel }}">{{ $nivel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-apoyo">Enfermero</label>
                <select wire:model.live="enfermero_id" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
                    <option value="">Todos</option>
                    @foreach ($enfermeros as $enfermero)
                        <option value="{{ $enfermero->cod_usu }}">{{ $enfermero->nombres }} {{ $enfermero->ap_paterno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="limpiarFiltros" class="w-full rounded-lg border border-borde bg-boton-fantasma px-3 py-2 text-sm font-semibold text-boton-fantasmaTexto transition hover:bg-boton-fantasmaHover">
                    Limpiar
                </button>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-apoyo">Desde</label>
                <input type="date" wire:model.live="fecha_inicio" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-apoyo">Hasta</label>
                <input type="date" wire:model.live="fecha_fin" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-borde bg-fondo-card shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-sm">
                <thead class="border-b border-tabla-headerBorde bg-tabla-header text-xs font-bold uppercase text-tabla-headerTexto">
                    <tr>
                        <th class="px-4 py-3">Codigo</th>
                        <th class="px-4 py-3">Solicitante</th>
                        <th class="px-4 py-3">CI</th>
                        <th class="px-4 py-3">Responsable</th>
                        <th class="px-4 py-3">Caso</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Documentos</th>
                        <th class="px-4 py-3">Enfermeria</th>
                        <th class="px-4 py-3">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tabla-rowBorde bg-fondo-tabla">
                    @forelse ($preadmisiones as $preadmision)
                        <tr class="text-texto-principal transition hover:bg-tabla-rowHover">
                            <td class="px-4 py-3 font-bold text-titulo">{{ $preadmision->cod_pre }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-titulo">{{ $preadmision->nombre_completo }}</div>
                                <div class="text-xs text-apoyo">{{ $preadmision->ciudad_municipio }} {{ $preadmision->zona ? '- ' . $preadmision->zona : '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-texto-secundario">{{ $preadmision->ci }} {{ $preadmision->expedicion_ci }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-titulo">{{ $preadmision->familiar_completo }}</div>
                                <div class="text-xs text-apoyo">{{ $preadmision->familiar_parentesco }} {{ $preadmision->familiar_celular ? '- ' . $preadmision->familiar_celular : '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-titulo">{{ str_replace('_', ' ', $preadmision->motivo_ingreso) }}</div>
                                <div class="text-xs text-apoyo">{{ $preadmision->prioridad }} / {{ $preadmision->permanencia }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border border-estado-infoBorde bg-estado-infoBg px-2 py-1 text-[11px] font-bold uppercase text-estado-info">
                                    {{ str_replace('_', ' ', $preadmision->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-titulo">{{ $preadmision->documentos_count }} docs</div>
                                <div class="text-xs text-apoyo">
                                    {{ $preadmision->documentos_iniciales_completos ? 'Iniciales completos' : 'Iniciales pendientes' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-texto-secundario">
                                {{ $preadmision->enfermero ? $preadmision->enfermero->nombres . ' ' . $preadmision->enfermero->ap_paterno : 'Sin asignar' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-texto-secundario">
                                {{ optional($preadmision->fecha_solicitud)->format('d/m/Y') ?? optional($preadmision->created_at)->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm text-apoyo">
                                No hay preadmisiones registradas con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-borde bg-fondo-card px-4 py-3">
            {{ $preadmisiones->links() }}
        </div>
    </section>

    @script
    <script>
        $wire.on('swal', (data) => {
            const payload = data[0] || data;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: payload.title,
                    text: payload.text,
                    icon: payload.icon,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: 'var(--boton-acento)',
                    background: 'var(--fondo-card)',
                    color: 'var(--texto-principal)'
                });
            } else {
                alert(payload.title + '\n' + payload.text);
            }
        });
    </script>
    @endscript
</div>
