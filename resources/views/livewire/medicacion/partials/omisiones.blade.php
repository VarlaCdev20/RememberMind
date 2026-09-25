{{-- OMISIONES — PALETA INSTITUCIONAL UNIFICADA --}}
<div class="bg-[#DED1C3] dark:bg-[#25221F] rounded-[16px] border border-[#C7B9AA] dark:border-[#494139] shadow-sm overflow-hidden transition-colors font-sans">
    
    {{-- Header de Sección --}}
    <div class="px-5 py-4 border-b border-[#C7B9AA] dark:border-[#494139] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-[#DED1C3] dark:bg-[#25221F]">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-[17px] font-[800] text-[#304060] dark:text-[#EFE5DA] tracking-tight">
                    Registro de Omisiones del Turno
                </h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[10.5px] font-[600] bg-[#F3DDDA] dark:bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E5A898] border border-[#E5BDB5] dark:border-[#C85D52]/40">
                    Justificaciones clínicas
                </span>
            </div>
            <p class="text-[12px] text-[#677084] dark:text-[#BDAE9F] mt-0.5">
                Dosis no administradas con justificación clínica o rechazo documentado
            </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-[8px] text-xs font-[600] bg-[#F0E8DE] dark:bg-[#2C2924] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139] self-start sm:self-center">
            {{ count($omisiones) }} omisiones registradas
        </span>
    </div>

    {{-- Tabla Continua con Proporciones Estables y Scroll Horizontal Responsivo --}}
    <div class="w-full overflow-hidden">
        <table class="w-full table-auto text-left border-collapse">
            <thead>
                <tr class="bg-[#E4D8CC] dark:bg-[#211F1B] text-[11px] font-[700] text-[#677084] dark:text-[#BDAE9F] uppercase tracking-wider border-b border-[#C7B9AA] dark:border-[#494139]">
                    <th class="px-2.5 py-2.5 w-[65px] text-center">Hora</th>
                    <th class="px-3 py-2.5">Residente</th>
                    <th class="px-3 py-2.5">Medicamento</th>
                    <th class="px-2 py-2.5 w-[70px] whitespace-nowrap">Dosis</th>
                    <th class="px-3 py-2.5">Motivo</th>
                    <th class="px-2.5 py-2.5 w-[110px] whitespace-nowrap">Profesional</th>
                    <th class="px-2 py-2.5 w-[55px] text-right whitespace-nowrap">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#C7B9AA]/60 dark:divide-[#494139] bg-[#F0E8DE] dark:bg-[#2C2924] text-xs">
                @forelse($omisiones as $omision)
                    @php
                        $nomRes = $omision->residente ? trim("{$omision->residente->nombres} {$omision->residente->apellido_paterno}") : 'Residente';
                        $partes = explode(' ', $nomRes);
                        $iniciales = strtoupper(substr($partes[0] ?? 'R', 0, 1) . substr($partes[1] ?? 'M', 0, 1));
                        
                        $codPresc = $omision->cod_prescripcion ?? ($omision->prescripcion?->cod_prescripcion ?? 'PRS_0001');
                        $horaStr = $omision->fecha_hora_programada?->format('H:i') ?? '08:00';
                        $codRes = $omision->cod_residente ?? ($omision->residente?->cod_residente ?? 'RES_0001');
                    @endphp
                    <tr 
                        wire:key="omision-{{ $omision->cod_administracion ?? $loop->index }}"
                        wire:click="abrirDrawerDosis('{{ $codPresc }}', '{{ $horaStr }}', '{{ $codRes }}')"
                        class="cursor-pointer transition-colors duration-150 hover:bg-[#E4D8CC]/50 dark:hover:bg-white/[0.03]">
                        
                        {{-- Hora --}}
                        <td class="px-2.5 py-2.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] bg-[#E4D8CC] dark:bg-[#211F1B] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] font-mono text-[11px] font-[700]">
                                {{ $horaStr }}
                            </span>
                        </td>

                        {{-- Residente --}}
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-[#E4D8CC] dark:bg-[#332F29] border border-[#C7B9AA] dark:border-[#494139] flex items-center justify-center font-[700] text-[10.5px] text-[#304060] dark:text-[#EFE5DA] shrink-0">
                                    {{ $iniciales }}
                                </div>
                                <span class="font-[700] text-[13px] text-[#304060] dark:text-[#EFE5DA] leading-snug break-words whitespace-normal">
                                    {{ $nomRes }}
                                </span>
                            </div>
                        </td>

                        {{-- Medicamento --}}
                        <td class="px-3 py-2.5">
                            <span class="font-[600] text-[13px] text-[#304060] dark:text-[#EFE5DA] leading-snug break-words whitespace-normal">
                                {{ $omision->prescripcion?->medicamento?->nombre_generico ?? ($omision->prescripcion?->nombre_medicamento ?? 'Medicamento') }}
                            </span>
                        </td>

                        {{-- Dosis --}}
                        <td class="px-2 py-2.5 text-[#677084] dark:text-[#BDAE9F] font-mono text-[11.5px] whitespace-nowrap">
                            {{ $omision->prescripcion?->dosis ?? '1' }} {{ $omision->prescripcion?->unidad_dosis ?? 'comp' }}
                        </td>

                        {{-- Motivo con Tag de Omisión --}}
                        <td class="px-3 py-2.5">
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[10.5px] font-[700] bg-[#F3DDDA] dark:bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E5A898] border border-[#E5BDB5]">
                                    <i class="ph-bold ph-warning text-xs"></i>
                                    {{ $omision->motivo_omision ?? 'Ayuno médico programado' }}
                                </span>
                                @if(!empty($omision->observacion))
                                    <p class="text-[11px] text-[#677084] dark:text-[#BDAE9F] truncate max-w-[220px]">
                                        {{ $omision->observacion }}
                                    </p>
                                @endif
                            </div>
                        </td>

                        {{-- Profesional --}}
                        <td class="px-2.5 py-2.5 text-[#304060] dark:text-[#EFE5DA] whitespace-nowrap font-medium text-xs">
                            {{ $omision->personal?->nombres ?? ($omision->registrador?->nombres ?? 'Personal de enfermería') }}
                        </td>

                        {{-- Acción: Botón Ver --}}
                        <td class="px-2.5 py-2.5 text-right whitespace-nowrap" @click.stop>
                            <button 
                                type="button" 
                                wire:click="abrirDrawerDosis('{{ $codPresc }}', '{{ $horaStr }}', '{{ $codRes }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-[8px] bg-[#E4D8CC] dark:bg-[#211F1B] hover:bg-[#DED1C3] dark:hover:bg-[#38332D] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] transition shadow-2xs cursor-pointer"
                                title="Ver ficha del paciente">
                                <span>Ver</span>
                                <i class="ph ph-arrow-right text-xs text-[#A35A44]"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-[#677084] dark:text-[#BDAE9F]">
                            <div class="w-12 h-12 mx-auto rounded-full bg-[#E4D8CC] dark:bg-[#211F1B] text-[#A35A44] flex items-center justify-center text-xl shadow-2xs mb-2">
                                <i class="ph ph-check-circle"></i>
                            </div>
                            <p class="text-xs font-semibold text-[#304060] dark:text-[#EFE5DA]">
                                No hay omisiones registradas en el turno actual
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
