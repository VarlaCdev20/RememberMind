<div class="space-y-3 font-sans">
    {{-- ==================================================
         1. BARRA DE FILTROS COMPACTA ÚNICA
         ================================================== --}}
    <section x-data="{ masFiltros: false }" class="p-3 sm:p-3.5 rounded-[14px] bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA] dark:border-[#494139] shadow-2xs space-y-2.5 transition-colors">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-2 items-center text-xs">
            {{-- 1. Búsqueda rápida: residente o medicamento --}}
            <div class="lg:col-span-3 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-[#677084] dark:text-[#BDAE9F]">
                    <i class="ph ph-magnifying-glass text-sm"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="filtroKardexBusqueda"
                    placeholder="Buscar residente o medicamento..."
                    class="w-full h-9 pl-8 pr-7 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:outline-none focus:ring-1 focus:ring-[#A35A44] transition" />
                @if(!empty($filtroKardexBusqueda))
                    <button type="button"
                        wire:click="limpiarFiltro('filtroKardexBusqueda')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2 text-[#677084] hover:text-[#A35A44] transition cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph ph-x-circle text-sm"></i>
                    </button>
                @endif
            </div>

            {{-- 2. Estado --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroKardexEstado" class="w-full h-9 px-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 focus:ring-[#A35A44] cursor-pointer">
                    <option value="">Todos los estados</option>
                    <option value="PROXIMA">Próximas</option>
                    <option value="PENDIENTE">Pendientes</option>
                    <option value="RETRASADA">Retrasadas</option>
                    <option value="ADMINISTRADA">Administradas</option>
                </select>
            </div>

            {{-- 3. Residente --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroKardexResidente" class="w-full h-9 px-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 focus:ring-[#A35A44] cursor-pointer">
                    <option value="">Todos los residentes</option>
                    @foreach($residentes as $res)
                        <option value="{{ $res->cod_residente }}">
                            {{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Horario --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroKardexHorario" class="w-full h-9 px-2 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 focus:ring-[#A35A44] cursor-pointer">
                    <option value="">Cualquier horario</option>
                    <option value="MANANA">Mañana (06:00 - 13:00)</option>
                    <option value="TARDE">Tarde (13:00 - 19:00)</option>
                    <option value="NOCHE">Noche (19:00 - 06:00)</option>
                    <option value="07:00">07:00</option>
                    <option value="08:00">08:00</option>
                    <option value="12:00">12:00</option>
                    <option value="16:00">16:00</option>
                    <option value="20:00">20:00</option>
                </select>
            </div>

            {{-- 5. Vía --}}
            <div class="lg:col-span-1">
                <select wire:model.live="filtroKardexVia" class="w-full h-9 px-1.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 focus:ring-[#A35A44] cursor-pointer">
                    <option value="">Vía (Todas)</option>
                    <option value="ORAL">Oral</option>
                    <option value="SUBLINGUAL">Sublingual</option>
                    <option value="INTRAVENOSA">Intravenosa</option>
                    <option value="INTRAMUSCULAR">Intramuscular</option>
                    <option value="SUBCUTANEA">Subcutánea</option>
                    <option value="TOPICA">Tópica</option>
                    <option value="INHALATORIA">Inhalatoria</option>
                </select>
            </div>

            {{-- 6. Botón toggle Más Filtros --}}
            <div class="lg:col-span-1 flex items-center justify-end">
                <button type="button" 
                    @click="masFiltros = !masFiltros"
                    class="h-9 px-2 w-full inline-flex items-center justify-center gap-1 text-[11px] font-bold rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] hover:bg-[#DED1C3] transition cursor-pointer"
                    :class="masFiltros ? 'bg-[#A35A44] text-white border-[#A35A44]' : ''"
                    title="Ver más filtros">
                    <i class="ph ph-faders text-xs"></i>
                    <span class="hidden sm:inline">Más</span>
                </button>
            </div>
        </div>

        {{-- Filtros Secundarios Desplegables (PRN, Fecha) --}}
        <div x-show="masFiltros" x-transition class="pt-2 border-t border-[#C7B9AA]/60 dark:border-[#494139] grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 text-xs">
            {{-- PRN --}}
            <div>
                <label class="block text-[10.5px] font-semibold text-[#677084] dark:text-[#BDAE9F] mb-1">Tipo de indicación:</label>
                <select wire:model.live="filtroKardexPrn" class="w-full h-8 px-2 text-xs rounded-[6px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA]">
                    <option value="">PRN (Todos)</option>
                    <option value="PRN">Según necesidad (PRN)</option>
                    <option value="FIJO">Horario fijo</option>
                </select>
            </div>

            {{-- Fecha --}}
            <div>
                <label class="block text-[10.5px] font-semibold text-[#677084] dark:text-[#BDAE9F] mb-1">Fecha de visualización:</label>
                <input type="date"
                    wire:model.live="filtroKardexFecha"
                    class="w-full h-8 px-2 text-xs rounded-[6px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA]" />
            </div>

            {{-- Acciones de reset --}}
            <div class="sm:col-span-2 flex items-end justify-end">
                <button type="button"
                    wire:click="resetFilters"
                    class="text-xs text-[#A35A44] dark:text-[#E5A898] hover:underline cursor-pointer inline-flex items-center gap-1 font-bold">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                    <span>Restablecer todo</span>
                </button>
            </div>
        </div>

        {{-- Fila de chips de filtros activos --}}
        @php
            $chipsActivos = !empty($filtroKardexBusqueda)
                || !empty($filtroKardexEstado)
                || !empty($filtroKardexHorario)
                || !empty($filtroKardexResidente)
                || !empty($filtroKardexVia)
                || !empty($filtroKardexPrn)
                || !empty($filtroKardexFecha);
        @endphp

        @if($chipsActivos)
            <div class="pt-2 border-t border-[#C7B9AA]/60 dark:border-[#494139] flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-[#677084] dark:text-[#BDAE9F]">Filtros activos:</span>

                    @if(!empty($filtroKardexBusqueda))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-[#E4D8CC] dark:bg-[#332F29] text-[11px] font-semibold text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139]">
                            Búsqueda: "{{ $filtroKardexBusqueda }}"
                            <button type="button" wire:click="limpiarFiltro('filtroKardexBusqueda')" class="hover:text-[#A35A44] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
                        </span>
                    @endif

                    @if(!empty($filtroKardexEstado))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-[#E4D8CC] dark:bg-[#332F29] text-[11px] font-semibold text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139]">
                            Estado: {{ $filtroKardexEstado === 'RETRASADA' ? 'Retrasadas' : ($filtroKardexEstado === 'PENDIENTE' ? 'Pendientes' : ($filtroKardexEstado === 'PROXIMA' ? 'Próximas' : 'Administradas')) }}
                            <button type="button" wire:click="limpiarFiltro('filtroKardexEstado')" class="hover:text-[#A35A44] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
                        </span>
                    @endif

                    @if(!empty($filtroKardexPrn))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-[#E4D8CC] dark:bg-[#332F29] text-[11px] font-semibold text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139]">
                            PRN: {{ $filtroKardexPrn === 'PRN' ? 'Según necesidad' : 'Horario fijo' }}
                            <button type="button" wire:click="limpiarFiltro('filtroKardexPrn')" class="hover:text-[#A35A44] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
                        </span>
                    @endif

                    @if(!empty($filtroKardexVia))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] bg-[#E4D8CC] dark:bg-[#332F29] text-[11px] font-semibold text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139]">
                            Vía: {{ ucfirst(strtolower($filtroKardexVia)) }}
                            <button type="button" wire:click="limpiarFiltro('filtroKardexVia')" class="hover:text-[#A35A44] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                        {{ count($dosisHoy) }} coincidencias
                    </span>

                    <button type="button"
                        wire:click="resetFilters"
                        class="text-xs text-[#A35A44] dark:text-[#E5A898] hover:text-[#884A39] cursor-pointer inline-flex items-center gap-1 font-bold transition">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                        <span>Restablecer todo</span>
                    </button>
                </div>
            </div>
        @endif
    </section>

    {{-- ==================================================
         2. TABLA KARDEX DIRECTA (SIN CONTENEDORES ANIDADOS)
         ================================================== --}}
    <div class="bg-[#F0E8DE] dark:bg-[#2C2924] rounded-[14px] border border-[#C7B9AA] dark:border-[#494139] shadow-sm overflow-hidden transition-colors">
        {{-- Header Compacto de la Tabla --}}
        <div class="px-4 py-2.5 border-b border-[#C7B9AA] dark:border-[#494139] flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-[#E4D8CC]/60 dark:bg-[#211F1B]">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-[800] text-[#304060] dark:text-[#EFE5DA] tracking-tight">
                    Dosis de hoy
                </h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-[10.5px] font-[600] bg-[#E4D8CC] dark:bg-[#2C2924] text-[#A35A44] dark:text-[#E5A898] border border-[#C7B9AA] dark:border-[#494139]">
                    Matriz Horaria del Turno
                </span>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center text-xs">
                <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                    Orden: <strong class="text-[#304060] dark:text-[#EFE5DA]">Alertas · Retrasadas · Pendientes · Administradas</strong>
                </span>
                <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-[700] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139]">
                    {{ count($dosisHoy) }} dosis
                </span>
            </div>
        </div>

        {{-- Tabla de Dosis del Turno --}}
        <div class="w-full overflow-x-auto">
            <table class="w-full table-auto text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-[#E4D8CC] dark:bg-[#211F1B] text-[10.5px] font-[700] text-[#677084] dark:text-[#BDAE9F] uppercase tracking-wider border-b border-[#C7B9AA] dark:border-[#494139]">
                        <th class="px-2.5 py-2 w-[65px] text-center">Hora</th>
                        <th class="px-3 py-2">Residente</th>
                        <th class="px-2 py-2 w-[85px] whitespace-nowrap">Hab / Cama</th>
                        <th class="px-3 py-2">Medicamento</th>
                        <th class="px-2 py-2 w-[75px] whitespace-nowrap">Dosis</th>
                        <th class="px-2 py-2 w-[65px] whitespace-nowrap">Vía</th>
                        <th class="px-2 py-2 w-[115px] text-center whitespace-nowrap">Estado</th>
                        <th class="px-2.5 py-2 w-[55px] text-right whitespace-nowrap">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C7B9AA]/60 dark:divide-[#494139] text-xs">
                    @forelse($dosisHoy as $dosis)
                        @php
                            $isSelected = ($selectedPrescripcionId === $dosis['cod_prescripcion'] && $selectedHora === $dosis['hora']);
                            $esAlertaClinica = !empty($dosis['tiene_alerta_clinica']);
                            $esRetrasada = in_array($dosis['estado_raw'] ?? '', ['VENCIDA', 'RETRASADA']);
                            $esAdministrada = in_array($dosis['estado_raw'] ?? '', ['ADMINISTRADA', 'ADMINISTRADO']);

                            // Fila con Alerta Clínica Real vinculada: rojo suave en toda la fila
                            if ($esAlertaClinica) {
                                $rowStyle = 'bg-[#FDF4F3] dark:bg-[#352523] border-l-4 border-l-[#C85D52] hover:bg-[#F9ECE9]';
                                $badgeClasses = 'bg-[#F3DDDA] dark:bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E5A898] border border-[#E5BDB5]';
                            } 
                            // Dosis Retrasada estándar: fondo normal con borde y badge terracota/rojo (no convierte toda la fila en alarma)
                            elseif ($esRetrasada) {
                                $rowStyle = 'bg-[#F0E8DE] dark:bg-[#2C2924] border-l-4 border-l-[#C85D52] hover:bg-[#E4D8CC]/50';
                                $badgeClasses = 'bg-[#F3DDDA] dark:bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E5A898] border border-[#E5BDB5]';
                            }
                            // Dosis Administrada: baja visualmente de importancia
                            elseif ($esAdministrada) {
                                $rowStyle = 'opacity-65 bg-[#F0E8DE]/60 dark:bg-[#2C2924]/60 border-l-4 border-l-[#71876A]/40 hover:opacity-100 transition-opacity';
                                $badgeClasses = 'bg-[#E3EBE0] dark:bg-[#71876A]/20 text-[#55694E] dark:text-[#91A287] border border-[#C5D6C0]';
                            }
                            // Pendiente o Próxima
                            else {
                                $rowStyle = 'bg-[#F0E8DE] dark:bg-[#2C2924] border-l-4 border-l-[#D2A45E] hover:bg-[#E4D8CC]/50';
                                $badgeClasses = 'bg-[#FBF0D9] dark:bg-[#D2A45E]/20 text-[#9E732B] dark:text-[#E5BA79] border border-[#EED7A1]';
                            }

                            if ($isSelected) {
                                $rowStyle .= ' ring-1 ring-[#A35A44] bg-[#E4D8CC]/80';
                            }
                        @endphp
                        <tr 
                            wire:key="dosis-{{ $dosis['id'] ?? $loop->index }}"
                            wire:click="abrirDrawerDosis('{{ $dosis['cod_prescripcion'] }}', '{{ $dosis['hora'] }}', '{{ $dosis['cod_residente'] }}')"
                            class="cursor-pointer transition-colors duration-150 {{ $rowStyle }}">
                            
                            {{-- Hora --}}
                            <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-[5px] bg-[#E4D8CC] dark:bg-[#211F1B] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] font-mono text-[11px] font-[700]">
                                    {{ $dosis['hora'] }}
                                </span>
                            </td>

                            {{-- Residente --}}
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-full bg-[#E4D8CC] dark:bg-[#332F29] border border-[#C7B9AA] dark:border-[#494139] flex items-center justify-center font-[700] text-[10px] text-[#304060] dark:text-[#EFE5DA] shrink-0">
                                        {{ $dosis['iniciales'] ?? 'RM' }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-[700] text-xs text-[#304060] dark:text-[#EFE5DA] block leading-tight">
                                            {{ $dosis['nombre_residente'] }}
                                        </span>
                                        @if($esAlertaClinica)
                                            <span class="inline-flex items-center gap-0.5 text-[9.5px] font-bold text-[#C85D52] dark:text-[#E5A898]">
                                                <i class="ph ph-warning"></i> Alerta clínica
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Hab / Cama --}}
                            <td class="px-2 py-2 text-[#677084] dark:text-[#BDAE9F] font-medium text-xs whitespace-nowrap">
                                {{ $dosis['habitacion'] ?? 'Hab. 101' }}
                            </td>

                            {{-- Medicamento --}}
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-[700] text-xs text-[#304060] dark:text-[#EFE5DA]">
                                        {{ $dosis['medicamento'] }}
                                    </span>
                                    @if(!empty($dosis['es_prn']))
                                        <span class="px-1 py-0.2 rounded text-[9.5px] font-bold bg-[#A35A44]/15 text-[#A35A44] border border-[#A35A44]/30">PRN</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Dosis --}}
                            <td class="px-2 py-2 font-mono text-xs font-[600] text-[#304060] dark:text-[#EFE5DA] whitespace-nowrap">
                                {{ $dosis['dosis'] }}
                            </td>

                            {{-- Vía --}}
                            <td class="px-2 py-2 text-[11px] text-[#677084] dark:text-[#BDAE9F] font-medium whitespace-nowrap">
                                {{ $dosis['via'] }}
                            </td>

                            {{-- Estado --}}
                            <td class="px-2 py-2 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-[6px] text-[10.5px] font-[700] {{ $badgeClasses }}">
                                    {{ $dosis['estado'] }}
                                </span>
                            </td>

                            {{-- Acción --}}
                            <td class="px-2.5 py-2 text-right whitespace-nowrap">
                                <button type="button"
                                    wire:click.stop="abrirDrawerDosis('{{ $dosis['cod_prescripcion'] }}', '{{ $dosis['hora'] }}', '{{ $dosis['cod_residente'] }}')"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold rounded-[6px] bg-[#E4D8CC] dark:bg-[#211F1B] hover:bg-[#DED1C3] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] transition cursor-pointer">
                                    <span>Ver</span>
                                    <i class="ph ph-caret-right text-[10px]"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-xs text-[#677084] dark:text-[#BDAE9F]">
                                <i class="ph ph-check-circle text-2xl text-[#71876A] mb-1.5 block"></i>
                                No hay dosis programadas para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
