<div class="space-y-6">
    <section class="rounded-xl border border-borde bg-fondo-card p-5 shadow-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Admisiones</p>
                <h1 class="mt-1 text-2xl font-bold text-titulo">
                    {{ $soloRechazadas ? 'Preadmisiones rechazadas' : 'Preadmisiones' }}
                </h1>
                <p class="mt-1 max-w-3xl text-sm text-apoyo">
                    {{ $soloRechazadas ? 'Solicitudes rechazadas conservadas para trazabilidad.' : 'La preadmisión registra una solicitud. El residente institucional solo se crea después de aprobar y formalizar el ingreso con una cama disponible.' }}
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
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-5">
        @foreach ([
            ['label' => 'Total', 'value' => $metricas['total']],
            ['label' => 'Pendientes', 'value' => $metricas['pendientes']],
            ['label' => 'Aprobadas', 'value' => $metricas['aprobadas']],
            ['label' => 'Admitidas', 'value' => $metricas['admitidas']],
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
        <div class="grid gap-4 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-apoyo">Buscar</label>
                <input type="text" wire:model.live.debounce.350ms="search" placeholder="Nombre, cédula o familiar responsable" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto placeholder-input-placeholder focus:border-input-bordeFocus focus:ring-input-ringFocus">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-apoyo">Estado</label>
                <select wire:model.live="estado" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
                    <option value="">Todos</option>
                    <option value="PENDIENTE">Pendiente de revisión</option>
                    <option value="PREADMISION_ASIGNADA">Preadmision asignada</option>
                    <option value="PENDIENTE_VALORACION_MEDICA">Pendiente de valoración médica</option>
                    <option value="VALORACION_MEDICA_FINALIZADA">Valoración médica finalizada</option>
                    <option value="APROBADA">Aprobada</option>
                    <option value="ADMITIDA">Admitida</option>
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
            <table class="w-full min-w-[940px] text-left text-sm">
                <thead class="border-b border-tabla-headerBorde bg-tabla-header text-xs font-bold uppercase text-tabla-headerTexto">
                    <tr>
                        <th class="px-4 py-3">Solicitante</th>
                        <th class="px-4 py-3">CI</th>
                        <th class="px-4 py-3">Responsable</th>
                        <th class="px-4 py-3">Caso</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Documentos</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Decision</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tabla-rowBorde bg-fondo-tabla">
                    @forelse ($preadmisiones as $preadmision)
                        <tr class="text-texto-principal transition hover:bg-tabla-rowHover">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-titulo">{{ $preadmision->nombre_completo }}</div>
                                <div class="text-xs text-apoyo">{{ $preadmision->ciudad_municipio }} {{ $preadmision->zona ? '- ' . $preadmision->zona : '' }}</div>
                                <button type="button" wire:click="verDetalle('{{ $preadmision->cod_pre }}')" class="mt-1 text-xs font-bold text-boton-acento hover:underline">Ver expediente de solicitud</button>
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
                                        'APROBADA', 'ADMITIDA' => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
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
                            <td class="px-4 py-3 text-xs text-texto-secundario">
                                {{ optional($preadmision->fecha_solicitud)->format('d/m/Y') ?? optional($preadmision->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if (! in_array($preadmision->estado, ['APROBADA', 'ADMITIDA', 'RECHAZADA']))
                                        @if(auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'ADMINISTRADOR', 'SUPERADMINISTRADOR']))
                                            <button wire:click="aprobar('{{ $preadmision->cod_pre }}')" wire:confirm="¿Confirma que revisó la solicitud y desea aprobarla? La ficha de residente todavía no será creada." class="inline-flex items-center gap-1 rounded-lg border border-estado-exitoBorde bg-estado-exitoBg px-2 py-1 text-[11px] font-bold text-estado-exito transition hover:opacity-85"><i class="ph ph-check-circle"></i> Aprobar</button>
                                            <button wire:click="abrirModalRechazo('{{ $preadmision->cod_pre }}')" class="inline-flex items-center gap-1 rounded-lg border border-estado-peligroBorde bg-estado-peligroBg px-2 py-1 text-[11px] font-bold text-estado-peligro transition hover:opacity-85"><i class="ph ph-x-circle"></i> Rechazar</button>
                                        @else
                                            <span class="text-xs font-semibold text-apoyo">Pendiente de revisión profesional</span>
                                        @endif
                                    @elseif ($preadmision->estado === 'APROBADA')
                                        @if($preadmision->cod_am_generado)
                                            <a wire:navigate href="{{ route('admin.adultos-mayores.show', $preadmision->cod_am_generado) }}" class="inline-flex items-center gap-1 rounded-lg bg-boton-acento px-3 py-1.5 text-[11px] font-bold text-boton-acentoTexto">Ver ficha</a>
                                        @else
                                            @if(auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']))
                                                <button wire:click="abrirAdmision('{{ $preadmision->cod_pre }}')" class="inline-flex items-center gap-1 rounded-lg bg-boton-acento px-3 py-1.5 text-[11px] font-bold text-boton-acentoTexto"><i class="ph ph-door-open"></i> Formalizar admisión</button>
                                            @else
                                                <span class="text-xs font-semibold text-apoyo">Pendiente de ingreso administrativo</span>
                                            @endif
                                        @endif
                                    @elseif ($preadmision->estado === 'ADMITIDA')
                                        <a wire:navigate href="{{ route('admin.adultos-mayores.show', $preadmision->cod_am_generado) }}" class="inline-flex items-center gap-1 rounded-lg bg-boton-acento px-3 py-1.5 text-[11px] font-bold text-boton-acentoTexto">Ver ficha del residente</a>
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
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-apoyo">
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

    <x-ui.drawer-livewire wire:model="modalDocumentos" title="Documentos de preadmisión" close-method="cerrarModalDocumentos" width="max-w-2xl">
            <div class="space-y-2">
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
                    <div class="flex items-center gap-3 rounded-xl border border-borde bg-fondo-card p-3">
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

    </x-ui.drawer-livewire>

    <x-ui.modal-livewire wire:model="modalRechazo" title="Registrar rechazo de preadmisión" close-method="cerrarModalRechazo" max-width="xl">
        <form wire:submit="rechazar" class="space-y-4">
            <p class="rounded-xl border border-borde bg-fondo-hover p-3 text-sm text-apoyo">La decisión se conservará en el expediente para trazabilidad institucional.</p>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-apoyo">Motivo de rechazo</label>
                    <select wire:model="motivo_rechazo" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus">
                        <option value="">Seleccionar</option>
                        <option value="DOCUMENTACION_INSUFICIENTE">Documentación insuficiente</option>
                        <option value="CRITERIO_MEDICO">Criterio médico</option>
                        <option value="CRITERIO_INSTITUCIONAL">Criterio institucional</option>
                        <option value="DATOS_INCONSISTENTES">Datos inconsistentes</option>
                        <option value="DESISTIMIENTO_FAMILIAR">Desistimiento familiar</option>
                        <option value="OTRO">Otro</option>
                    </select>
                    @error('motivo_rechazo') <div class="mt-1 text-xs text-estado-peligro">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-apoyo">Observación profesional</label>
                    <textarea wire:model="observacion_rechazo" rows="4" class="w-full rounded-lg border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus"></textarea>
                    @error('observacion_rechazo') <div class="mt-1 text-xs text-estado-peligro">{{ $message }}</div> @enderror
                </div>
            <div class="flex justify-end gap-2 border-t border-borde px-5 py-4">
                <button type="button" wire:click="cerrarModalRechazo" class="rm-btn-secondary">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg border border-estado-peligroBorde bg-estado-peligroBg px-4 py-2 text-sm font-semibold text-estado-peligro transition hover:opacity-85">
                    Confirmar rechazo
                </button>
            </div>
        </form>
    </x-ui.modal-livewire>

    <x-ui.drawer-livewire wire:model="modalDetalle" title="Expediente de preadmisión" close-method="cerrarDetalle" width="max-w-2xl">
        @if($solicitudDetalle)
            <div class="space-y-5">
                <section class="rounded-2xl border border-borde bg-fondo-hover p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Persona solicitante</p>
                            <h3 class="mt-1 text-xl font-black text-titulo">{{ $solicitudDetalle->nombre_completo }}</h3>
                            <p class="text-sm text-apoyo">Cédula {{ $solicitudDetalle->ci }} {{ $solicitudDetalle->expedicion_ci }}</p>
                        </div>
                        <span class="rounded-full border border-borde bg-fondo-card px-3 py-1 text-xs font-black text-titulo">
                            {{ str_replace('_', ' ', $solicitudDetalle->estado) }}
                        </span>
                    </div>
                </section>

                <section>
                    <h4 class="mb-3 text-xs font-black uppercase tracking-wider text-apoyo">Datos personales y contacto</h4>
                    <dl class="grid gap-3 sm:grid-cols-2">
                        @foreach([
                            'Fecha de nacimiento' => optional($solicitudDetalle->fecha_nac)->format('d/m/Y'),
                            'Género' => $solicitudDetalle->genero,
                            'Estado civil' => $solicitudDetalle->estado_civil,
                            'Teléfono' => $solicitudDetalle->celular ?: $solicitudDetalle->telefono,
                            'Residencia' => trim(($solicitudDetalle->departamento_residencia ?? '').' '.($solicitudDetalle->ciudad_municipio ?? '')),
                            'Dirección' => trim(($solicitudDetalle->zona ?? '').' '.($solicitudDetalle->calle ?? '')),
                        ] as $etiqueta => $valor)
                            <div class="rounded-xl border border-borde bg-fondo-card p-3">
                                <dt class="text-[11px] font-bold uppercase text-apoyo">{{ $etiqueta }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-titulo">{{ $valor ?: 'No registrado' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section>
                    <h4 class="mb-3 text-xs font-black uppercase tracking-wider text-apoyo">Responsable principal</h4>
                    <div class="rounded-2xl border border-borde bg-fondo-card p-4 text-sm">
                        <p class="font-black text-titulo">{{ $solicitudDetalle->familiar_completo }}</p>
                        <p class="mt-1 text-apoyo">{{ $solicitudDetalle->familiar_parentesco }} · {{ $solicitudDetalle->familiar_celular }}</p>
                        <p class="text-apoyo">{{ $solicitudDetalle->familiar_correo ?: 'Sin correo registrado' }}</p>
                        <p class="mt-2 text-texto-secundario">{{ $solicitudDetalle->familiar_direccion ?: 'Sin dirección registrada' }}</p>
                    </div>
                </section>

                <section>
                    <h4 class="mb-3 text-xs font-black uppercase tracking-wider text-apoyo">Motivo y condiciones solicitadas</h4>
                    <div class="space-y-3 rounded-2xl border border-borde bg-fondo-card p-4 text-sm">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <p><span class="block text-xs font-bold text-apoyo">Motivo</span><span class="font-semibold text-titulo">{{ str_replace('_', ' ', $solicitudDetalle->motivo_ingreso) }}</span></p>
                            <p><span class="block text-xs font-bold text-apoyo">Procedencia</span><span class="font-semibold text-titulo">{{ str_replace('_', ' ', $solicitudDetalle->procedencia_ingreso) }}</span></p>
                            <p><span class="block text-xs font-bold text-apoyo">Tipo de ingreso</span><span class="font-semibold text-titulo">{{ str_replace('_', ' ', $solicitudDetalle->tipo_ingreso) }}</span></p>
                            <p><span class="block text-xs font-bold text-apoyo">Permanencia</span><span class="font-semibold text-titulo">{{ str_replace('_', ' ', $solicitudDetalle->permanencia) }}</span></p>
                        </div>
                        <p><span class="block text-xs font-bold text-apoyo">Descripción del caso</span><span class="text-texto-secundario">{{ $solicitudDetalle->descripcion_caso ?: 'Sin descripción' }}</span></p>
                    </div>
                </section>

                <section class="rounded-2xl border border-borde bg-fondo-card p-4">
                    <h4 class="text-xs font-black uppercase tracking-wider text-apoyo">Progreso institucional</h4>
                    <ol class="mt-4 space-y-3 text-sm">
                        <li class="flex gap-3"><i class="ph-fill ph-check-circle text-estado-exito"></i><span><strong class="text-titulo">Solicitud registrada</strong><span class="block text-xs text-apoyo">No genera una ficha de residente.</span></span></li>
                        <li class="flex gap-3"><i class="ph-fill {{ in_array($solicitudDetalle->estado, ['APROBADA','ADMITIDA']) ? 'ph-check-circle text-estado-exito' : 'ph-circle text-apoyo' }}"></i><span><strong class="text-titulo">Revisión y decisión</strong><span class="block text-xs text-apoyo">Aprobar, rechazar o mantener pendiente.</span></span></li>
                        <li class="flex gap-3"><i class="ph-fill {{ $solicitudDetalle->cod_am_generado ? 'ph-check-circle text-estado-exito' : 'ph-circle text-apoyo' }}"></i><span><strong class="text-titulo">Admisión y cama</strong><span class="block text-xs text-apoyo">Aquí se habilita al residente institucional.</span></span></li>
                    </ol>
                </section>
            </div>
        @endif
    </x-ui.drawer-livewire>

    <x-ui.modal-livewire wire:model="modalAdmision" title="Formalizar admisión institucional" close-method="cerrarAdmision" max-width="4xl">
        @if($solicitudAdmision)
            <form wire:submit="formalizarAdmision" class="space-y-5">
                <div class="rounded-2xl border border-estado-infoBorde bg-estado-infoBg p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-estado-info">Solicitud aprobada</p>
                    <h4 class="mt-1 text-lg font-black text-titulo">{{ $solicitudAdmision->nombre_completo }}</h4>
                    <p class="text-sm text-apoyo">Complete los datos de ingreso. La ficha de residente se creará al confirmar esta operación.</p>
                </div>

                @error('solicitud') <p class="rounded-xl bg-estado-peligroBg p-3 text-sm font-semibold text-estado-peligro">{{ $message }}</p> @enderror
                @error('admision') <p class="rounded-xl bg-estado-peligroBg p-3 text-sm font-semibold text-estado-peligro">{{ $message }}</p> @enderror

                <x-ui.form-section title="Ingreso y ocupación" description="La cama es obligatoria y se bloqueará al confirmar." icon="ph-bed">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-3">
                            <label class="mb-2 block text-xs font-bold text-apoyo">1. Seleccione una habitación <span class="text-estado-peligro">*</span></label>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @forelse($habitacionesAdmision as $habitacion)
                                    @php
                                        $habilitada = ! in_array($habitacion->estado, ['MANTENIMIENTO', 'BLOQUEADA'], true) && $habitacion->camas_disponibles_count > 0;
                                        $faltanRegistrar = max(0, $habitacion->capacidad - $habitacion->camas_count);
                                        $motivo = match (true) {
                                            $habitacion->estado === 'MANTENIMIENTO' => 'Habitación en mantenimiento',
                                            $habitacion->estado === 'BLOQUEADA' => 'Habitación bloqueada',
                                            $habitacion->camas_count === 0 => 'No tiene camas registradas',
                                            $habitacion->camas_disponibles_count === 0 => 'Sin camas libres',
                                            default => $habitacion->camas_disponibles_count.' cama(s) libre(s)',
                                        };
                                    @endphp
                                    <label class="relative rounded-2xl border p-3 transition {{ $habitacion_id === $habitacion->cod_habitacion ? 'border-input-bordeFocus bg-estado-infoBg' : 'border-borde bg-fondo-card' }} {{ $habilitada ? 'cursor-pointer hover:bg-fondo-hover' : 'cursor-not-allowed opacity-70' }}">
                                        <input type="radio" wire:model.live="habitacion_id" value="{{ $habitacion->cod_habitacion }}" class="sr-only" @disabled(!$habilitada)>
                                        <span class="block text-sm font-black text-titulo">{{ $habitacion->nombre }}</span>
                                        <span class="mt-1 block text-xs text-apoyo">{{ str_replace('_', ' ', $habitacion->tipo_habitacion) }}{{ $habitacion->ubicacion ? ' · '.$habitacion->ubicacion : '' }}</span>
                                        <span class="mt-2 block text-xs font-semibold {{ $habilitada ? 'text-estado-exito' : 'text-estado-advertencia' }}">{{ $motivo }}</span>
                                        <span class="mt-1 block text-[11px] text-apoyo">Capacidad {{ $habitacion->capacidad }} · Ocupadas {{ $habitacion->camas_ocupadas_count }} · Fuera de servicio {{ $habitacion->camas_mantenimiento_count + $habitacion->camas_bloqueadas_count }}</span>
                                        @if($faltanRegistrar > 0)<span class="mt-1 block text-[11px] text-apoyo">{{ $faltanRegistrar }} espacio(s) aún sin cama registrada</span>@endif
                                    </label>
                                @empty
                                    <p class="col-span-full rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 text-sm text-estado-advertencia">No existen habitaciones registradas. Configure habitaciones y camas antes de admitir.</p>
                                @endforelse
                            </div>
                            @error('habitacion_id') <p class="mt-1 text-xs font-semibold text-estado-peligro">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-2 block text-xs font-bold text-apoyo">2. Seleccione la cama <span class="text-estado-peligro">*</span></label>
                            @if(!$habitacion_id)
                                <p class="rounded-xl border border-borde bg-fondo-hover p-3 text-sm text-apoyo">Seleccione primero una habitación con disponibilidad.</p>
                            @else
                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($camasHabitacion as $cama)
                                        @php
                                            $asignacion = $cama->asignacionesActivas->first();
                                            $libre = $cama->estado === 'DISPONIBLE' && !$asignacion;
                                        @endphp
                                        <label class="rounded-xl border p-3 {{ $cama_id === $cama->cod_cama ? 'border-input-bordeFocus bg-estado-infoBg' : 'border-borde bg-fondo-card' }} {{ $libre ? 'cursor-pointer' : 'cursor-not-allowed opacity-70' }}">
                                            <input type="radio" wire:model="cama_id" value="{{ $cama->cod_cama }}" class="sr-only" @disabled(!$libre)>
                                            <span class="block text-sm font-black text-titulo">Cama {{ $cama->codigo ?: $cama->numero }}</span>
                                            <span class="mt-1 block text-xs {{ $libre ? 'text-estado-exito' : 'text-estado-advertencia' }}">
                                                {{ $libre ? 'Disponible para ingreso' : ($asignacion?->adultoMayor ? 'Ocupada por '.trim($asignacion->adultoMayor->nombres.' '.$asignacion->adultoMayor->ap_paterno.' '.$asignacion->adultoMayor->ap_materno) : str_replace('_', ' ', $cama->estado)) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @if($camasHabitacion->isEmpty())<p class="rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 text-sm text-estado-advertencia">Esta habitación no tiene camas registradas.</p>@endif
                            @endif
                            @error('cama_id') <p class="mt-1 text-xs font-semibold text-estado-peligro">{{ $message }}</p> @enderror
                        </div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Fecha de ingreso *</label><input type="date" wire:model="fecha_ingreso" max="{{ now()->toDateString() }}" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('fecha_ingreso')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Hora de ingreso *</label><input type="time" wire:model="hora_ingreso" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('hora_ingreso')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Nivel educativo</label><input type="text" wire:model="nivel_educativo" placeholder="Ej. Primaria completa" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></div>
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Información clínica de seguridad" description="Registre información conocida; cuando no exista, seleccione o escriba “Desconocido”." icon="ph-first-aid">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Grupo sanguíneo</label><select wire:model="grupo_sanguineo" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"><option value="">Seleccione</option>@foreach(['A','B','AB','O','DESCONOCIDO'] as $grupo)<option value="{{ $grupo }}">{{ $grupo }}</option>@endforeach</select></div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Factor RH</label><select wire:model="factor_rh" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"><option value="">Seleccione</option><option value="+">Positivo</option><option value="-">Negativo</option><option value="DESCONOCIDO">Desconocido</option></select></div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Seguro de salud *</label><input type="text" wire:model="seguro_salud" placeholder="Nombre del seguro o Sin seguro" class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm">@error('seguro_salud')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
                        <div class="md:col-span-2"><label class="mb-1 block text-xs font-bold text-apoyo">Alergias conocidas *</label><textarea wire:model="alergias" rows="3" placeholder="Describa alergias a medicamentos, alimentos u otras. Si no se conocen, escriba “Ninguna conocida”." class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea>@error('alergias')<p class="mt-1 text-xs text-estado-peligro">{{ $message }}</p>@enderror</div>
                        <div class="md:col-span-2">
                            <p class="mb-2 text-xs font-bold text-apoyo">Antecedentes y condiciones diagnosticadas</p>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach([
                                    'HIPERTENSION' => 'Hipertensión', 'DIABETES' => 'Diabetes', 'PROBLEMAS_CARDIACOS' => 'Problemas cardíacos',
                                    'ACV' => 'Antecedente de ACV', 'PARKINSON' => 'Parkinson', 'EPILEPSIA' => 'Epilepsia',
                                    'ALZHEIMER' => 'Alzheimer diagnosticado', 'DEPRESION' => 'Depresión', 'ANSIEDAD' => 'Ansiedad',
                                    'PROBLEMAS_SUENO' => 'Problemas de sueño', 'PROBLEMAS_VISUALES' => 'Problemas visuales',
                                    'PROBLEMAS_AUDITIVOS' => 'Problemas auditivos', 'DOLOR_CRONICO' => 'Dolor crónico'
                                ] as $valor => $etiqueta)
                                    <label class="flex items-center gap-2 rounded-xl border border-borde bg-fondo-card p-2.5 text-sm text-titulo">
                                        <input type="checkbox" wire:model="antecedentes" value="{{ $valor }}" class="rounded border-borde text-boton-acento">
                                        <span>{{ $etiqueta }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Restricciones alimentarias</label><textarea wire:model="restricciones_alimentarias" rows="3" placeholder="Dieta, intolerancias o restricciones indicadas." class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea></div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Hospitalizaciones previas</label><textarea wire:model="hospitalizaciones" rows="3" placeholder="Fecha aproximada, institución y motivo." class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea></div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Cirugías y procedimientos</label><textarea wire:model="cirugias" rows="3" placeholder="Procedimiento y fecha aproximada." class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea></div>
                        <div><label class="mb-1 block text-xs font-bold text-apoyo">Resumen de revisión médica</label><textarea wire:model="observacion_medica" rows="3" placeholder="Hallazgos, riesgos y recomendaciones para el equipo de atención." class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea></div>
                        <div class="md:col-span-2"><label class="mb-1 block text-xs font-bold text-apoyo">Observaciones para el ingreso</label><textarea wire:model="observaciones_admision" rows="3" placeholder="Movilidad, ayudas técnicas, dieta, medicación traída, riesgos u otra indicación relevante." class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-sm"></textarea></div>
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Autorizaciones" description="Confirme la información con la persona responsable." icon="ph-shield-check">
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 rounded-xl border border-borde bg-fondo-card p-3"><input type="checkbox" wire:model="usa_whatsapp" class="mt-1 rounded border-borde text-boton-acento"><span class="text-sm text-titulo">El contacto principal utiliza WhatsApp.</span></label>
                        <label class="flex items-start gap-3 rounded-xl border border-borde bg-fondo-card p-3"><input type="checkbox" wire:model="autoriza_informacion_medica" class="mt-1 rounded border-borde text-boton-acento"><span class="text-sm text-titulo">Autoriza compartir información médica con el responsable registrado. *</span></label>
                        @error('autoriza_informacion_medica')<p class="text-xs text-estado-peligro">{{ $message }}</p>@enderror
                        <label class="flex items-start gap-3 rounded-xl border border-borde bg-fondo-card p-3"><input type="checkbox" wire:model="consentimiento_datos" class="mt-1 rounded border-borde text-boton-acento"><span class="text-sm text-titulo">Confirma el consentimiento para tratamiento de datos personales y clínicos. *</span></label>
                        @error('consentimiento_datos')<p class="text-xs text-estado-peligro">{{ $message }}</p>@enderror
                    </div>
                </x-ui.form-section>

                <div class="flex flex-col-reverse gap-2 border-t border-borde pt-4 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarAdmision" class="rm-btn-secondary">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="formalizarAdmision" class="rm-btn-primary">
                        <span wire:loading.remove wire:target="formalizarAdmision"><i class="ph-bold ph-check-circle"></i> Confirmar admisión</span>
                        <span wire:loading wire:target="formalizarAdmision">Procesando ingreso…</span>
                    </button>
                </div>
            </form>
        @endif
    </x-ui.modal-livewire>

    @script
    <script>

{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-admisiones-preadmision-wizard.js')) !!}
</script>
    @endscript
</div>
