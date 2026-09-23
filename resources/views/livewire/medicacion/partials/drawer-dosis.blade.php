{{-- DRAWER LATERAL REDUCIDO — PALETA INSTITUCIONAL UNIFICADA --}}
<div 
    x-cloak 
    x-show="$wire.drawerDosisAbierto" 
    class="fixed inset-0 z-40 overflow-hidden font-sans" 
    aria-labelledby="slide-over-title" 
    role="dialog" 
    aria-modal="true">
    
    {{-- Backdrop suave --}}
    <div 
        x-show="$wire.drawerDosisAbierto"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:click="cerrarDrawerDosis"
        class="fixed inset-0 bg-[#1A1816]/60 dark:bg-black/70 backdrop-blur-[2px] transition-opacity">
    </div>

    {{-- Panel Deslizante (Ancho óptimo 390-420px) --}}
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-6">
        <div 
            x-show="$wire.drawerDosisAbierto"
            x-transition:enter="transform transition ease-out duration-250"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen max-w-[410px] bg-[#F0E8DE] dark:bg-[#2C2924] border-l border-[#C7B9AA] dark:border-[#494139] shadow-[0_8px_30px_rgba(48,64,96,0.12)] dark:shadow-[0_8px_30px_rgba(0,0,0,0.5)] flex flex-col justify-between transition-colors">
            
            {{-- Header Drawer --}}
            <div class="px-5 py-4 border-b border-[#C7B9AA] dark:border-[#494139] flex items-center justify-between bg-[#E4D8CC] dark:bg-[#211F1B] shrink-0">
                <div>
                    <h3 class="text-[17px] font-[800] text-[#304060] dark:text-[#EFE5DA] tracking-tight" id="slide-over-title">
                        Administrar medicación
                    </h3>
                    <p class="text-[12px] text-[#677084] dark:text-[#BDAE9F] mt-0.5">
                        Detalle de la Dosis seleccionada
                    </p>
                </div>
                <button 
                    type="button" 
                    wire:click="cerrarDrawerDosis" 
                    class="rounded-[8px] p-1.5 text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-white hover:bg-[#DED1C3] dark:hover:bg-[#38342E] transition cursor-pointer"
                    title="Cerrar panel">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            {{-- Contenido Abierto --}}
            <div class="overflow-y-auto flex-1 px-5 py-4 space-y-4 custom-scrollbar text-xs divide-y divide-[#C7B9AA]/70 dark:divide-[#494139]">
                @if(!empty($dosisDetalle))

                    {{-- RESIDENTE --}}
                    <div class="flex items-center gap-3 pb-3">
                        <div class="w-[42px] h-[42px] rounded-full bg-[#E4D8CC] dark:bg-[#332F29] border border-[#C7B9AA] dark:border-[#494139] flex items-center justify-center font-[700] text-sm text-[#304060] dark:text-[#EFE5DA] shrink-0">
                            {{ $dosisDetalle['residente']['iniciales'] ?? 'MG' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-[700] text-[14.5px] text-[#304060] dark:text-[#EFE5DA] truncate">
                                {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Mario Gutiérrez Mendoza' }}
                            </div>
                            <div class="text-[11.5px] text-[#677084] dark:text-[#BDAE9F] flex items-center gap-2 mt-0.5 flex-wrap">
                                <span>{{ $dosisDetalle['residente']['edad'] ?? 79 }} años</span>
                                <span>·</span>
                                <span>{{ $dosisDetalle['residente']['habitacion'] ?? 'Habitación 101' }}</span>
                                <span>·</span>
                                <span class="font-mono">NHC: {{ $dosisDetalle['residente']['nhc'] ?? '10234' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- MEDICAMENTO --}}
                    <div class="pt-3 pb-1">
                        <div class="text-[10.5px] font-[800] uppercase tracking-wider text-[#A35A44] dark:text-[#E5A898] mb-1.5">
                            Medicamento
                        </div>
                        <div class="p-3 rounded-[12px] bg-[#F3EAE1] dark:bg-[#25221F] border border-[#C7B9AA] dark:border-[#494139] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-[8px] bg-[#A35A44] text-white flex items-center justify-center shrink-0">
                                    <i class="ph ph-pill text-base"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-[700] text-[13.5px] text-[#304060] dark:text-[#EFE5DA] truncate">
                                        {{ $dosisDetalle['medicamento']['nombre_generico'] ?? 'Omeprazol' }} {{ $dosisDetalle['medicamento']['concentracion'] ?? '20 mg' }}
                                    </div>
                                    <div class="text-[11px] text-[#677084] dark:text-[#BDAE9F] truncate">
                                        {{ $dosisDetalle['medicamento']['nombre_comercial'] ?? 'Normon®' }} · {{ $dosisDetalle['medicamento']['forma_farmaceutica'] ?? 'Cápsula' }}
                                    </div>
                                </div>
                            </div>
                            <button 
                                type="button"
                                wire:click="abrirModalMedicamento('{{ $dosisDetalle['cod_medicamento'] ?? 'OMEPRAZOL' }}')"
                                class="shrink-0 px-2.5 py-1 text-xs font-[600] rounded-[8px] bg-[#E4D8CC] dark:bg-[#332F29] hover:bg-[#DED1C3] dark:hover:bg-[#3B362F] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] transition shadow-2xs">
                                Ver ficha
                            </button>
                        </div>
                    </div>

                    {{-- PRESCRIPCIÓN MÉDICA --}}
                    <div class="pt-3 pb-1 space-y-2">
                        <div class="text-[10.5px] font-[800] uppercase tracking-wider text-[#A35A44] dark:text-[#E5A898]">
                            Prescripción Médica
                        </div>
                        
                        <div class="grid grid-cols-2 gap-y-1.5 text-xs py-1">
                            <span class="text-[#677084] dark:text-[#BDAE9F]">Dosis:</span>
                            <span class="font-[700] text-[#304060] dark:text-[#EFE5DA] text-right font-mono">{{ $dosisDetalle['prescripcion']['dosis'] ?? '20 mg' }}</span>
                            
                            <span class="text-[#677084] dark:text-[#BDAE9F]">Vía:</span>
                            <span class="font-[600] text-[#304060] dark:text-[#EFE5DA] text-right">{{ $dosisDetalle['prescripcion']['via'] ?? 'Oral' }}</span>
                            
                            <span class="text-[#677084] dark:text-[#BDAE9F]">Frecuencia:</span>
                            <span class="font-[600] text-[#304060] dark:text-[#EFE5DA] text-right">{{ $dosisDetalle['prescripcion']['frecuencia'] ?? 'Cada 24 horas' }}</span>
                        </div>

                        {{-- Indicación médica --}}
                        <div class="pt-1 text-xs">
                            <span class="text-[#677084] dark:text-[#BDAE9F] block text-[11px] font-medium">Indicación:</span>
                            <p class="text-[#304060] dark:text-[#EFE5DA] mt-0.5 leading-relaxed bg-[#E4D8CC]/60 dark:bg-[#211F1B]/60 p-2 rounded-[8px] border border-[#C7B9AA]/40">
                                {{ $dosisDetalle['prescripcion']['indicacion'] ?? 'Tomar 1 cápsula en ayunas 30 minutos antes del desayuno.' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-y-1.5 text-xs pt-1">
                            <span class="text-[#677084] dark:text-[#BDAE9F]">Prescrito por:</span>
                            <span class="font-[600] text-[#304060] dark:text-[#EFE5DA] text-right truncate">{{ $dosisDetalle['prescripcion']['prescriptor'] ?? 'Dra. Carla Encinas' }}</span>

                            <span class="text-[#677084] dark:text-[#BDAE9F]">Programación del Horario:</span>
                            <span class="font-mono font-[700] text-[#304060] dark:text-[#EFE5DA] text-right">{{ $dosisDetalle['programacion']['hora_programada'] ?? $dosisDetalle['hora'] ?? '08:00' }}</span>
                        </div>
                    </div>

                    {{-- SEGURIDAD Y ALERGIAS --}}
                    <div class="pt-3 pb-1 space-y-2">
                        <div class="text-[10.5px] font-[800] uppercase tracking-wider text-[#A35A44] dark:text-[#E5A898]">
                            Seguridad y Alergias
                        </div>

                        {{-- 5 Correctos --}}
                        <div class="grid grid-cols-2 gap-1.5 text-[11.5px] py-1 font-[600] text-[#71876A] dark:text-[#91A287]">
                            <div class="flex items-center gap-1.5">
                                <i class="ph ph-check-circle text-[#71876A]"></i>
                                <span>Paciente correcto</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="ph ph-check-circle text-[#71876A]"></i>
                                <span>Medicamento correcto</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="ph ph-check-circle text-[#71876A]"></i>
                                <span>Dosis correcta</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="ph ph-check-circle text-[#71876A]"></i>
                                <span>Vía correcta</span>
                            </div>
                            <div class="flex items-center gap-1.5 col-span-2">
                                <i class="ph ph-check-circle text-[#71876A]"></i>
                                <span>Hora correcta</span>
                            </div>
                        </div>

                        {{-- Alergias --}}
                        <div>
                            @if(empty($dosisDetalle['seguridad']['alergias']) || str_contains($dosisDetalle['seguridad']['alergias'], 'Sin alergias'))
                                <div class="p-2 rounded-[8px] bg-[#E3EBE0] dark:bg-[#71876A]/20 text-[#71876A] dark:text-[#A4B89D] border border-[#C5D6C0] dark:border-[#71876A]/40 text-[11px] font-[600] flex items-center gap-1.5">
                                    <i class="ph ph-shield-check text-[#71876A] text-sm"></i>
                                    <span>Sin alergias medicamentosas registradas</span>
                                </div>
                            @else
                                <div class="p-2 rounded-[8px] bg-[#F3DDDA] dark:bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E5A898] border border-[#E5BDB5] dark:border-[#C85D52]/40 text-[11px] font-[600] flex items-center gap-1.5">
                                    <i class="ph ph-warning-octagon text-[#C85D52] text-sm"></i>
                                    <span>{{ $dosisDetalle['seguridad']['alergias'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SEGUIMIENTO CLÍNICO --}}
                    <div class="pt-3 pb-1 text-xs">
                        <div class="flex items-center justify-between text-[11.5px] text-[#677084] dark:text-[#BDAE9F]">
                            <span>Último Seguimiento:</span>
                            <span class="font-medium text-[#304060] dark:text-[#EFE5DA]">
                                {{ $dosisDetalle['seguimiento']['ultima_admin'] ?? '13/04/2025 - 10:00' }}
                            </span>
                        </div>
                    </div>

                    {{-- OBSERVACIÓN DE ENFERMERÍA --}}
                    @if(empty($dosisDetalle['ya_registrada']))
                        <div class="pt-3">
                            <h4 class="text-[10.5px] font-[800] uppercase tracking-wider text-[#A35A44] dark:text-[#E5A898] mb-1.5">
                                Observación de Enfermería
                            </h4>
                            <div class="relative">
                                <textarea 
                                    wire:model="observacionEnfermeria" 
                                    maxlength="200"
                                    rows="3" 
                                    placeholder="Añadir observación de administración (opcional)..."
                                    class="w-full h-[76px] text-xs rounded-[10px] border border-[#C7B9AA] dark:border-[#494139] bg-[#F0E8DE] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:ring-1 focus:ring-[#A35A44] p-2.5 resize-none shadow-2xs"></textarea>
                                <span class="absolute bottom-2 right-2.5 text-[10px] text-[#677084] dark:text-[#BDAE9F] font-mono pointer-events-none">
                                    {{ strlen($observacionEnfermeria) }}/200
                                </span>
                            </div>
                        </div>
                    @endif

                @else
                    <div class="py-14 text-center text-[#677084] dark:text-[#BDAE9F]">
                        Cargando detalle de la dosis...
                    </div>
                @endif
            </div>

            {{-- Footer Drawer con Botones de Acción --}}
            <div class="p-3.5 sm:px-5 sm:py-3.5 bg-[#E4D8CC] dark:bg-[#211F1B] border-t border-[#C7B9AA] dark:border-[#494139] shrink-0">
                @if(!empty($dosisDetalle) && empty($dosisDetalle['ya_registrada']))
                    <div class="grid grid-cols-2 gap-2.5">
                        
                        {{-- Registrar Omisión (Terracota) --}}
                        <button 
                            type="button"
                            wire:click="abrirModalOmision"
                            class="h-[40px] px-3 rounded-[10px] bg-[#F3DDDA] dark:bg-[#C85D52]/20 border border-[#E5BDB5] dark:border-[#C85D52]/40 text-[#C85D52] dark:text-[#E5A898] hover:bg-[#E5BDB5] font-[700] text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                            <i class="ph ph-warning-circle text-base"></i>
                            <span>Registrar omisión</span>
                        </button>

                        {{-- Administrar (Sage) --}}
                        <button 
                            type="button"
                            wire:click="abrirModalAdministrar"
                            wire:loading.attr="disabled"
                            class="h-[40px] px-3 rounded-[10px] bg-[#71876A] hover:bg-[#5B7054] text-white font-[700] text-xs transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="ph ph-check-circle text-base" wire:loading.remove wire:target="administrarDosisConfirmada"></i>
                            <span wire:loading.remove wire:target="administrarDosisConfirmada">Administrar</span>
                            <span wire:loading wire:target="administrarDosisConfirmada">Guardando...</span>
                        </button>

                    </div>
                @elseif(!empty($dosisDetalle) && !empty($dosisDetalle['ya_registrada']))
                    <div class="p-2.5 rounded-[10px] bg-[#E3EBE0] dark:bg-[#71876A]/20 text-[#71876A] dark:text-[#A4B89D] text-center text-xs font-[700] border border-[#C5D6C0] dark:border-[#71876A]/40">
                        Esta dosis ya ha sido registrada en el sistema.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
