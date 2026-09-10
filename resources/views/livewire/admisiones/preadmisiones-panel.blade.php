<div class="space-y-6">
    <section class="rounded-xl border border-borde bg-fondo-card p-5 shadow-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Admisiones</p>
                <h1 class="mt-1 text-2xl font-bold text-titulo">
                    {{ $soloRechazadas ? 'Preadmisiones rechazadas' : 'Preadmisiones' }}
                </h1>
                <p class="mt-1 max-w-3xl text-sm text-apoyo">
                    {{ $soloRechazadas ? 'Casos rechazados conservados para auditoria y seguimiento.' : 'Solicitudes iniciales registradas antes de crear un adulto mayor activo. La salida operativa es preadmision asignada.' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a wire:navigate href="{{ route('admin.admisiones.preadmisiones') }}" class="inline-flex items-center gap-2 rounded-lg border border-borde bg-boton-fantasma px-4 py-2 text-sm font-semibold text-boton-fantasmaTexto transition hover:bg-boton-fantasmaHover">
                    <i class="ph ph-list"></i>
                    Activas
                </a>
                <a wire:navigate href="{{ route('admin.admisiones.preadmisiones.rechazadas') }}" class="inline-flex items-center gap-2 rounded-lg border border-borde bg-boton-fantasma px-4 py-2 text-sm font-semibold text-boton-fantasmaTexto transition hover:bg-boton-fantasmaHover">
                    <i class="ph ph-prohibit-inset"></i>
                    Rechazadas
                </a>
                @can('admisiones.crear')
                    <a wire:navigate href="{{ route('admin.admisiones.preadmision') }}" class="inline-flex items-center gap-2 rounded-lg bg-boton-acento px-4 py-2 text-sm font-semibold text-boton-acentoTexto transition hover:bg-boton-acentoHover">
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
            ['label' => 'Aprobadas', 'value' => $metricas['aprobadas']],
            ['label' => 'Rechazadas', 'value' => $metricas['rechazadas']],
            ['label' => 'Alta prioridad', 'value' => $metricas['alta_prioridad']],
            ['label' => 'Docs completos', 'value' => $metricas['con_documentos']],
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
                    <option value="APROBADA">Aprobada</option>
                    <option value="RECHAZADA">Rechazada</option>
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
                        <th class="px-4 py-3">Decision</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tabla-rowBorde bg-fondo-tabla">
                    @forelse ($preadmisiones as $preadmision)
                        <tr class="text-texto-principal transition hover:bg-tabla-rowHover">
                            <td class="px-4 py-3 font-bold text-titulo">{{ $preadmision->cod_pre }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-titulo">{{ $preadmision->nombre_completo }}</div>
                                <div class="text-xs text-apoyo">{{ $preadmision->ciudad_municipio }} {{ $preadmision->zona ? '- ' . $preadmision->zona : '' }}</div>
                                @if ($preadmision->cod_am_generado)
                                    <div class="mt-1 text-xs font-semibold text-estado-exito">Adulto: {{ $preadmision->cod_am_generado }}</div>
                                @endif
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
                                @php
                                    $estadoClase = match($preadmision->estado) {
                                        'APROBADA' => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
                                        'RECHAZADA' => 'border-estado-peligroBorde bg-estado-peligroBg text-estado-peligro',
                                        default => 'border-estado-infoBorde bg-estado-infoBg text-estado-info',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full border px-2 py-1 text-[11px] font-bold uppercase {{ $estadoClase }}">
                                    {{ str_replace('_', ' ', $preadmision->estado) }}
                                </span>
                                @if ($preadmision->estado === 'RECHAZADA' && $preadmision->motivo_rechazo)
                                    <div class="mt-1 text-xs text-estado-peligro">{{ $preadmision->motivo_rechazo }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-titulo">{{ $preadmision->documentos_count }} docs</div>
                                <div class="text-xs text-apoyo mb-1">
                                    {{ $preadmision->documentos_iniciales_completos ? 'Iniciales completos' : 'Iniciales pendientes' }}
                                </div>
                                <button wire:click="verDocumentos('{{ $preadmision->cod_pre }}')"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold text-estado-info bg-estado-infoBg border border-estado-infoBorde rounded hover:opacity-80 transition">
                                    <i class="ph-bold ph-folder-open"></i> Ver docs
                                </button>
                            </td>
                            <td class="px-4 py-3 text-texto-secundario">
                                {{ $preadmision->enfermero ? $preadmision->enfermero->nombres . ' ' . $preadmision->enfermero->ap_paterno : 'Sin asignar' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-texto-secundario">
                                {{ optional($preadmision->fecha_solicitud)->format('d/m/Y') ?? optional($preadmision->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if (! in_array($preadmision->estado, ['APROBADA', 'RECHAZADA']))
                                        <button wire:click="aprobar('{{ $preadmision->cod_pre }}')" class="inline-flex items-center gap-1 rounded-lg border border-estado-exitoBorde bg-estado-exitoBg px-2 py-1 text-[11px] font-bold text-estado-exito transition hover:opacity-85">
                                            <i class="ph ph-check-circle"></i>
                                            Aprobar
                                        </button>
                                        <button wire:click="abrirModalRechazo('{{ $preadmision->cod_pre }}')" class="inline-flex items-center gap-1 rounded-lg border border-estado-peligroBorde bg-estado-peligroBg px-2 py-1 text-[11px] font-bold text-estado-peligro transition hover:opacity-85">
                                            <i class="ph ph-x-circle"></i>
                                            Rechazar
                                        </button>
                                    @elseif ($preadmision->estado === 'APROBADA')
                                        <div class="text-xs text-estado-exito">
                                            <div>{{ optional($preadmision->fecha_aprobacion)->format('d/m/Y H:i') }}</div>
                                            @if ($preadmision->cod_am_generado)
                                                <div class="font-semibold">{{ $preadmision->cod_am_generado }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-estado-peligro">
                                            {{ optional($preadmision->fecha_rechazo)->format('d/m/Y H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-sm text-apoyo">
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

    {{-- Modal de documentos --}}
    @if($modalDocumentos)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-fondo-card rounded-2xl shadow-2xl border border-borde w-full max-w-2xl max-h-[85vh] flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-borde">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-estado-infoBg text-estado-info flex items-center justify-center">
                        <i class="ph-bold ph-folder-open text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-titulo">Documentos de preadmisión</h3>
                        <p class="text-[10px] text-apoyo">{{ $codPreSeleccionada }}</p>
                    </div>
                </div>
                <button wire:click="cerrarModalDocumentos"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-fondo text-apoyo hover:bg-estado-peligroBg hover:text-estado-peligro transition">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            {{-- Lista de documentos --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                @forelse($documentosModal as $doc)
                    @php
                        $esGenerado    = $doc['es_generado_sistema'] ?? false;
                        $tieneArchivo  = ! empty($doc['archivo_path']);
                        $estado        = $doc['estado'] ?? 'PENDIENTE';
                        $estadoClass   = match($estado) {
                            'RECIBIDO', 'GENERADO'   => 'bg-estado-exitoBg text-estado-exito border-estado-exito/20',
                            'PENDIENTE_48H'          => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
                            'PENDIENTE_FIRMA'        => 'bg-estado-infoBg text-estado-info border-estado-infoBorde',
                            default                  => 'bg-fondo text-apoyo border-borde',
                        };
                    @endphp
                    <div class="flex items-center gap-3 p-3 border rounded-xl bg-white shadow-sm">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                            {{ $esGenerado ? 'bg-estado-infoBg text-estado-info' : 'bg-boton-acento/10 text-boton-acento' }}">
                            <i class="ph-fill {{ $esGenerado ? 'ph-file-pdf' : 'ph-file-text' }} text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold text-titulo truncate">{{ $doc['nombre_documento'] }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border {{ $estadoClass }}">
                                    {{ str_replace('_', ' ', $estado) }}
                                </span>
                                <span class="text-[9px] text-apoyo uppercase">
                                    {{ $doc['grupo_documento'] ?? ($esGenerado ? 'institucional' : 'solicitante') }}
                                </span>
                                @if(! empty($doc['fecha_limite_entrega']))
                                    <span class="text-[9px] text-estado-advertencia font-semibold">
                                        Límite: {{ \Carbon\Carbon::parse($doc['fecha_limite_entrega'])->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        {{-- Botones Ver / Descargar --}}
                        <div class="flex items-center gap-1 shrink-0">
                            @if($tieneArchivo)
                                <a href="{{ asset('storage/' . $doc['archivo_path']) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-estado-info bg-estado-infoBg border border-estado-infoBorde rounded hover:opacity-80 transition">
                                    <i class="ph-bold ph-eye"></i> Ver
                                </a>
                                <a href="{{ asset('storage/' . $doc['archivo_path']) }}" download
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-boton-acento bg-boton-acento/10 border border-boton-acento/30 rounded hover:opacity-80 transition">
                                    <i class="ph-bold ph-download-simple"></i> Descargar
                                </a>
                            @else
                                <span class="px-2.5 py-1.5 text-[10px] font-bold text-apoyo bg-fondo border border-borde rounded">
                                    Sin archivo
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-apoyo">
                        No hay documentos registrados para esta preadmisión.
                    </div>
                @endforelse
            </div>

            <div class="px-5 py-3 border-t border-borde text-right">
                <button wire:click="cerrarModalDocumentos"
                        class="px-4 py-2 text-sm font-bold border border-borde rounded-xl text-titulo hover:bg-fondo-hover transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($modalRechazo)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-xl rounded-2xl border border-borde bg-fondo-card shadow-2xl">
            <div class="border-b border-borde px-5 py-4">
                <h3 class="text-base font-bold text-titulo">Rechazar preadmision</h3>
                <p class="text-sm text-apoyo">{{ $codPreRechazo }}</p>
            </div>
            <div class="space-y-4 px-5 py-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-apoyo">Motivo de rechazo</label>
                    <select wire:model="motivo_rechazo" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
                        <option value="">Seleccionar</option>
                        <option value="DOCUMENTACION_INSUFICIENTE">Documentacion insuficiente</option>
                        <option value="CRITERIO_MEDICO">Criterio medico</option>
                        <option value="CRITERIO_INSTITUCIONAL">Criterio institucional</option>
                        <option value="DATOS_INCONSISTENTES">Datos inconsistentes</option>
                        <option value="DESISTIMIENTO_FAMILIAR">Desistimiento familiar</option>
                        <option value="OTRO">Otro</option>
                    </select>
                    @error('motivo_rechazo') <div class="mt-1 text-xs text-estado-peligro">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-apoyo">Observacion</label>
                    <textarea wire:model="observacion_rechazo" rows="4" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus"></textarea>
                    @error('observacion_rechazo') <div class="mt-1 text-xs text-estado-peligro">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-borde px-5 py-4">
                <button wire:click="cerrarModalRechazo" class="rounded-lg border border-borde bg-boton-secundario px-4 py-2 text-sm font-semibold text-boton-secundarioTexto transition hover:bg-boton-secundarioHover">
                    Cancelar
                </button>
                <button wire:click="rechazar" class="rounded-lg border border-estado-peligroBorde bg-estado-peligroBg px-4 py-2 text-sm font-semibold text-estado-peligro transition hover:opacity-85">
                    Confirmar rechazo
                </button>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>

{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-admisiones-preadmision-wizard.js')) !!}
</script>
    @endscript
</div>
