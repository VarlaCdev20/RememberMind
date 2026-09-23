{{-- MODAL FLOTANTE ARRASTRABLE — FICHA DEL MEDICAMENTO --}}
<div 
    x-data="{ 
        openModal: @entangle('modalMedicamentoAbierto'),
        isDragging: false,
        hasMoved: false,
        posX: 0,
        posY: 0,
        startX: 0,
        startY: 0,
        initialX: 0,
        initialY: 0,

        init() {
            this.$watch('openModal', (val) => {
                if (val) {
                    this.resetPosition();
                }
            });
        },

        resetPosition() {
            this.posX = 0;
            this.posY = 0;
            this.hasMoved = false;
            this.isDragging = false;
        },

        startDrag(e) {
            if (window.innerWidth < 1024) return;
            if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) {
                return;
            }
            if (e.button !== undefined && e.button !== 0) return;

            this.isDragging = true;
            this.startX = e.clientX;
            this.startY = e.clientY;
            this.initialX = this.posX;
            this.initialY = this.posY;

            if (e.currentTarget.setPointerCapture) {
                try { e.currentTarget.setPointerCapture(e.pointerId); } catch (_) {}
            }

            document.body.style.userSelect = 'none';
            document.body.style.webkitUserSelect = 'none';
        },

        onDrag(e) {
            if (!this.isDragging) return;

            const deltaX = e.clientX - this.startX;
            const deltaY = e.clientY - this.startY;

            let nextX = this.initialX + deltaX;
            let nextY = this.initialY + deltaY;

            const modalEl = this.$refs.modalCard;
            if (modalEl) {
                const rect = modalEl.getBoundingClientRect();
                const baseLeft = rect.left - this.posX;
                const baseTop = rect.top - this.posY;

                const minX = -baseLeft + 12;
                const maxX = window.innerWidth - (baseLeft + rect.width) - 12;
                const minY = -baseTop + 12;
                const maxY = window.innerHeight - (baseTop + 54);

                nextX = Math.max(minX, Math.min(nextX, maxX));
                nextY = Math.max(minY, Math.min(nextY, maxY));
            }

            this.posX = nextX;
            this.posY = nextY;
            this.hasMoved = true;
        },

        stopDrag(e) {
            if (!this.isDragging) return;
            this.isDragging = false;

            if (e.currentTarget.releasePointerCapture) {
                try { e.currentTarget.releasePointerCapture(e.pointerId); } catch (_) {}
            }

            document.body.style.userSelect = '';
            document.body.style.webkitUserSelect = '';
        }
    }"
    x-show="openModal"
    class="fixed inset-0 z-50 pointer-events-none font-sans" 
    style="display: none;">
    
    {{-- Overlay en móvil/tablet para cerrar al tocar fondo --}}
    <div 
        x-show="openModal"
        @click="$wire.cerrarModalMedicamento()"
        class="fixed inset-0 bg-[#1A1816]/60 dark:bg-black/70 pointer-events-auto lg:hidden transition-opacity">
    </div>

    {{-- Modal flotante y arrastrable --}}
    <div 
        x-ref="modalCard"
        x-show="openModal"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 lg:translate-x-3 lg:translate-y-0"
        x-transition:enter-end="opacity-100 translate-y-0 lg:translate-x-0"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 lg:translate-x-0"
        x-transition:leave-end="opacity-0 translate-y-3 lg:translate-x-3 lg:translate-y-0"
        :style="hasMoved && window.innerWidth >= 1024 ? 'transform: translate3d(' + posX + 'px, ' + posY + 'px, 0); will-change: transform;' : ''"
        :class="isDragging ? '!transition-none' : 'transition-colors duration-200'"
        class="fixed z-50 pointer-events-auto bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA] dark:border-[#494139] rounded-[16px] shadow-[0_16px_40px_rgba(75,55,40,0.18)] dark:shadow-[0_16px_40px_rgba(0,0,0,0.5)] flex flex-col overflow-hidden w-[90vw] sm:w-[360px] max-w-[370px] h-[85vh] max-h-[620px] inset-x-4 mx-auto top-[8%] lg:mx-0 lg:top-[84px] lg:right-[430px] lg:bottom-auto lg:h-[calc(100vh-104px)]">
        
        <div class="flex flex-col h-full">

            {{-- Header Modal Arrastrable --}}
            <div 
                @pointerdown="startDrag($event)"
                @pointermove="onDrag($event)"
                @pointerup="stopDrag($event)"
                @pointercancel="stopDrag($event)"
                :class="isDragging ? 'cursor-grabbing select-none' : 'lg:cursor-grab'"
                class="h-[52px] px-4 border-b border-[#C7B9AA] dark:border-[#494139] flex items-center justify-between bg-[#E4D8CC] dark:bg-[#211F1B] shrink-0 touch-none transition-colors">
                
                <div class="flex items-center gap-2 pointer-events-none select-none">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#A35A44] text-white shadow-2xs">
                        <i class="ph ph-file-text text-sm"></i>
                    </span>
                    <h3 class="text-[15.5px] font-bold text-[#304060] dark:text-[#EFE5DA] leading-tight">
                        Ficha del medicamento
                    </h3>
                </div>

                <div class="flex items-center gap-1">
                    <span class="hidden lg:inline-flex text-[#677084] dark:text-[#7D7367] mr-1 pointer-events-none" title="Arrastrar ventana">
                        <i class="ph-bold ph-dots-six-vertical text-base"></i>
                    </span>

                    <button 
                        type="button" 
                        wire:click="cerrarModalMedicamento" 
                        class="rounded-lg p-1 text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-[#EFE5DA] hover:bg-[#DED1C3] dark:hover:bg-[#38342E] transition cursor-pointer"
                        title="Cerrar ficha">
                        <i class="ph ph-x text-base"></i>
                    </button>
                </div>
            </div>

            {{-- Cuerpo Scrollable --}}
            <div class="overflow-y-auto flex-1 p-4 space-y-3.5 custom-scrollbar text-xs">
                
                {{-- Foto Medicamento --}}
                <div class="h-[170px] w-full rounded-[10px] bg-[#E4D8CC] dark:bg-[#201E1C] border border-[#C7B9AA] dark:border-[#494139] flex items-center justify-center p-3 overflow-hidden relative shadow-2xs">
                    @php
                        $codMed = $dosisDetalle['cod_medicamento'] ?? '';
                        $rutaWebp = 'storage/imagenes/medicamentos/' . $codMed . '.webp';
                        $existeWebp = $codMed && file_exists(public_path($rutaWebp));
                    @endphp

                    @if($existeWebp)
                        <img src="{{ asset($rutaWebp) }}" 
                             alt="{{ $medicamentoFicha['nombre_generico'] ?? 'Medicamento' }}"
                             class="max-h-full max-w-full object-contain rounded-md">
                    @else
                        {{-- Placeholder institucional farmacéutico --}}
                        <div class="flex flex-col items-center justify-center text-center p-2 select-none">
                            <div class="w-12 h-12 rounded-full bg-[#DED1C3] dark:bg-[#71876A]/25 text-[#A35A44] dark:text-[#E5A898] flex items-center justify-center mb-1.5 shadow-2xs">
                                <i class="ph ph-pill text-2xl"></i>
                            </div>
                            <span class="text-[12.5px] font-bold text-[#304060] dark:text-[#EFE5DA] uppercase tracking-wide">
                                {{ $medicamentoFicha['nombre_generico'] ?? 'LOSARTÁN' }}
                            </span>
                            <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F] font-medium mt-0.5">
                                {{ $medicamentoFicha['concentracion'] ?? '50 mg' }} · Imagen institucional
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Información Medicamento --}}
                <div class="space-y-0.5 divide-y divide-[#C7B9AA]/60 dark:divide-[#494139] text-xs">
                    
                    {{-- Nombre genérico --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Nombre genérico:</span>
                        <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-right">{{ $medicamentoFicha['nombre_generico'] ?? 'Losartán' }}</span>
                    </div>

                    {{-- Nombre comercial --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Nombre comercial:</span>
                        <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-right">{{ $medicamentoFicha['nombre_comercial'] ?? 'Cozaar®' }}</span>
                    </div>

                    {{-- Concentración --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Concentración:</span>
                        <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-right">{{ $medicamentoFicha['concentracion'] ?? '50 mg' }}</span>
                    </div>

                    {{-- Forma farmacéutica --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Forma farmacéutica:</span>
                        <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-right">{{ $medicamentoFicha['forma_farmaceutica'] ?? 'Comprimido' }}</span>
                    </div>

                    {{-- Unidad --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Unidad:</span>
                        <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-right">{{ $medicamentoFicha['unidad'] ?? 'Comprimido' }}</span>
                    </div>

                    {{-- Vía predeterminada --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Vía predeterminada:</span>
                        <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-right">{{ $medicamentoFicha['via_predeterminada'] ?? 'Oral' }}</span>
                    </div>

                    {{-- Control especial --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Control especial:</span>
                        <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-right">{{ !empty($medicamentoFicha['control_especial']) ? 'Sí' : 'No' }}</span>
                    </div>

                    {{-- Estado --}}
                    <div class="grid grid-cols-[45%_55%] py-2 items-center">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Estado:</span>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#E3EBE0] dark:bg-[#71876A]/20 text-[#71876A] dark:text-[#91A287]">
                                {{ $medicamentoFicha['estado'] ?? 'Activo' }}
                            </span>
                        </div>
                    </div>

                    {{-- Observación --}}
                    <div class="grid grid-cols-[45%_55%] py-2">
                        <span class="text-[#677084] dark:text-[#BDAE9F] font-medium">Observación:</span>
                        <span class="text-[#677084] dark:text-[#BDAE9F] text-right leading-snug font-medium">
                            {{ $medicamentoFicha['observacion'] ?? 'Información farmacológica ampliada no registrada.' }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- Footer Botón Cerrar --}}
            <div class="p-3 bg-[#E4D8CC] dark:bg-[#211F1B] border-t border-[#C7B9AA] dark:border-[#494139] shrink-0">
                <button 
                    type="button" 
                    wire:click="cerrarModalMedicamento" 
                    class="w-full h-8 rounded-lg text-xs font-bold bg-[#F0E8DE] dark:bg-[#332F29] hover:bg-[#DED1C3] dark:hover:bg-[#403A32] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] transition shadow-2xs cursor-pointer">
                    Cerrar
                </button>
            </div>

        </div>

    </div>
</div>
