<div class="space-y-4 font-sans bg-[#E9DFD3] dark:bg-[#1C1A18] p-3 sm:p-5 rounded-[18px]">
    {{-- ==================================================
         1. CABECERA COMPACTA CON MICRO-CONTADORES INTEGRADOS
         ================================================== --}}
    <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-2 border-b border-[#C7B9AA]/70 dark:border-[#494139]">
        {{-- Título y Subtítulo --}}
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#A35A44] dark:text-[#E5A898] block">
                CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS · ENFERMERÍA
            </span>
            <div class="flex items-center gap-2 mt-0.5">
                <h1 class="text-xl sm:text-2xl font-[800] text-[#304060] dark:text-[#EFE5DA] tracking-tight">
                    Medicación
                </h1>
                <span class="text-[11px] font-mono text-[#677084] dark:text-[#BDAE9F] bg-[#F0E8DE] dark:bg-[#2C2924] px-2 py-0.5 rounded-[6px] border border-[#C7B9AA] dark:border-[#494139]">
                    {{ $fechaCabecera }}
                </span>
            </div>
            <p class="text-xs text-[#677084] dark:text-[#BDAE9F] mt-0.5">
                Administración y seguimiento del turno
            </p>
        </div>

        {{-- Micro-Contadores Integrados (Reemplazo compacto de tarjetas grandes) --}}
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 self-start lg:self-center">
            {{-- Total dosis --}}
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[8px] text-xs font-[700] bg-[#F0E8DE] dark:bg-[#2C2924] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139]" title="Total dosis programadas">
                <i class="ph ph-pill text-[#A35A44]"></i>
                <span>Total dosis</span>
                <span class="ml-0.5 px-1.5 py-0.2 rounded-full bg-[#E4D8CC] dark:bg-[#211F1B] text-[11px]">{{ count($dosisHoy) }}</span>
            </span>

            {{-- Administradas --}}
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-xs font-[600] bg-[#E3EBE0] dark:bg-[#71876A]/20 text-[#55694E] dark:text-[#91A287] border border-[#C5D6C0] dark:border-[#71876A]/40" title="Dosis administradas">
                <span class="w-1.5 h-1.5 rounded-full bg-[#71876A]"></span>
                <span>{{ $conteoAdministradas }} administradas</span>
            </span>

            {{-- Pendientes --}}
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-xs font-[600] bg-[#FBF0D9] dark:bg-[#D2A45E]/20 text-[#9E732B] dark:text-[#E5BA79] border border-[#EED7A1] dark:border-[#D2A45E]/40" title="Dosis pendientes">
                <span class="w-1.5 h-1.5 rounded-full bg-[#D2A45E]"></span>
                <span>{{ $conteoPendientes }} pendientes</span>
            </span>

            {{-- Con retraso --}}
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-xs font-[700] {{ $conteoRetrasadas > 0 ? 'bg-[#F3DDDA] dark:bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E5A898] border border-[#E5BDB5] dark:border-[#C85D52]/40' : 'bg-[#F0E8DE] dark:bg-[#2C2924] text-[#677084] dark:text-[#BDAE9F] border border-[#C7B9AA] dark:border-[#494139]' }}" title="Dosis con retraso">
                <span class="w-1.5 h-1.5 rounded-full {{ $conteoRetrasadas > 0 ? 'bg-[#C85D52] animate-pulse' : 'bg-[#677084]' }}"></span>
                <span>{{ $conteoRetrasadas }} retrasada{{ $conteoRetrasadas === 1 ? '' : 's' }}</span>
            </span>

            {{-- Omisiones --}}
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-xs font-[600] bg-[#E4D8CC] dark:bg-[#332F29] text-[#884A39] dark:text-[#BDAE9F] border border-[#C7B9AA] dark:border-[#494139]" title="Omisiones justificadas">
                <span class="w-1.5 h-1.5 rounded-full bg-[#884A39]"></span>
                <span>{{ $conteoOmitidas }} omitida{{ $conteoOmitidas === 1 ? '' : 's' }}</span>
            </span>

            {{-- Alergias (chip de seguridad sin alarma si no hay alertas activas) --}}
            @if($residentesConAlergias === 0)
                <span class="hidden sm:inline-flex items-center gap-1 px-2 py-1 rounded-[8px] text-[11px] font-semibold bg-[#F0E8DE] dark:bg-[#2C2924] text-[#677084] dark:text-[#BDAE9F] border border-[#C7B9AA] dark:border-[#494139]" title="Seguridad de alergias">
                    <i class="ph ph-shield-check text-[#71876A]"></i>
                    <span>Alergia relevante: Sin alertas activas</span>
                </span>
            @endif
        </div>
    </header>

    {{-- Notificación Flash Reactiva --}}
    @if(session()->has('mensaje_exito') || session()->has('mensaje'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-transition 
             class="flex items-center justify-between p-3 rounded-[12px] bg-[#E3EBE0] dark:bg-[#71876A]/20 border border-[#C5D6C0] dark:border-[#71876A]/40 text-[#55694E] dark:text-[#A4B89D] text-xs font-[600] shadow-2xs">
            <div class="flex items-center gap-2">
                <i class="ph ph-check-circle text-base"></i>
                <span>{{ session('mensaje_exito') ?? session('mensaje') }}</span>
            </div>
            <button type="button" @click="show = false" class="text-current opacity-70 hover:opacity-100 cursor-pointer">
                <i class="ph ph-x text-sm"></i>
            </button>
        </div>
    @endif

    {{-- ==================================================
         2. ALERTA CLÍNICA REAL (SOLO SI EXISTE ALERTA ACTIVA)
         ================================================== --}}
    @if($residentesConAlergias > 0 || $conteoRetrasadas > 0)
        <section class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 p-2.5 sm:px-3.5 rounded-[10px] bg-[#FDF4F3] dark:bg-[#332422] border-l-4 border-l-[#C85D52] border-y border-r border-[#E5BDB5] dark:border-[#523330] text-xs shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-[6px] bg-[#C85D52] text-white">
                    <i class="ph ph-warning-bold text-sm"></i>
                </span>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    @if($residentesConAlergias > 0)
                        <span class="font-[700] text-[#C85D52] dark:text-[#E5A898] inline-flex items-center gap-1">
                            Alergia relevante: {{ $residentesConAlergias }} registrada{{ $residentesConAlergias === 1 ? '' : 's' }}
                        </span>
                    @endif
                    @if($conteoRetrasadas > 0)
                        <span class="font-semibold text-[#884A39] dark:text-[#E5A898] inline-flex items-center gap-1">
                            · {{ $conteoRetrasadas }} retrasada{{ $conteoRetrasadas === 1 ? '' : 's' }} en este turno
                        </span>
                    @endif
                    <span class="text-[#677084] dark:text-[#BDAE9F] hidden md:inline">
                        · Protocolo de seguridad del paciente activo
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-1 text-[11px] font-semibold text-[#884A39] dark:text-[#E5A898] self-end sm:self-center">
                <i class="ph ph-shield-check text-xs"></i>
                <span>Atención prioritaria requerida</span>
            </div>
        </section>
    @endif

    {{-- ==================================================
         3. PESTAÑAS PRINCIPALES: KARDEX | HISTORIAL
         (Con accesos rápidos de filtro para Próximas y Omisiones)
         ================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#C7B9AA] dark:border-[#494139] pb-0.5">
        {{-- Pestañas Mayores --}}
        <nav class="flex items-center gap-6 text-xs font-[700]">
            {{-- Tab Kardex --}}
            <button 
                type="button"
                wire:click="cambiarTab('kardex')"
                class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-1.5 {{ in_array($tabActivo, ['kardex', 'proximas', 'omisiones']) ? 'border-b-2 border-[#A35A44] text-[#A35A44] dark:text-[#E5A898]' : 'text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-[#EFE5DA]' }}">
                <i class="ph ph-calendar-check text-sm {{ in_array($tabActivo, ['kardex', 'proximas', 'omisiones']) ? 'text-[#A35A44]' : 'text-[#677084] dark:text-[#BDAE9F]' }}"></i>
                <span>Kardex</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10.5px] {{ in_array($tabActivo, ['kardex', 'proximas', 'omisiones']) ? 'bg-[#A35A44]/15 text-[#A35A44] dark:text-[#E5A898]' : 'bg-[#E4D8CC] dark:bg-[#211F1B] text-[#677084] dark:text-[#BDAE9F]' }}">
                    {{ count($dosisHoy) }}
                </span>
            </button>

            {{-- Tab Historial --}}
            <button 
                type="button"
                wire:click="cambiarTab('historial')"
                class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-1.5 {{ $tabActivo === 'historial' ? 'border-b-2 border-[#A35A44] text-[#A35A44] dark:text-[#E5A898]' : 'text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-[#EFE5DA]' }}">
                <i class="ph ph-clock-counter-clockwise text-sm {{ $tabActivo === 'historial' ? 'text-[#A35A44]' : 'text-[#677084] dark:text-[#BDAE9F]' }}"></i>
                <span>Historial</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10.5px] {{ $tabActivo === 'historial' ? 'bg-[#A35A44]/15 text-[#A35A44] dark:text-[#E5A898]' : 'bg-[#E4D8CC] dark:bg-[#211F1B] text-[#677084] dark:text-[#BDAE9F]' }}">
                    {{ count($historial) }}
                </span>
            </button>
        </nav>

        {{-- Filtros Rápidos de Kardex (Próximas dosis / Omisiones) --}}
        @if(in_array($tabActivo, ['kardex', 'proximas', 'omisiones']))
            <div class="flex items-center gap-1.5 pb-2 text-xs">
                <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F] font-semibold mr-1 hidden sm:inline">Vista rápida:</span>
                
                {{-- Todas (Kardex completo) --}}
                <button 
                    type="button"
                    wire:click="cambiarTab('kardex')"
                    class="px-2.5 py-1 rounded-[8px] font-semibold transition cursor-pointer {{ $tabActivo === 'kardex' ? 'bg-[#A35A44] text-white shadow-2xs' : 'bg-[#F0E8DE] dark:bg-[#2C2924] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139] hover:bg-[#E4D8CC]' }}">
                    Todas
                </button>

                {{-- Próximas dosis --}}
                <button 
                    type="button"
                    wire:click="cambiarTab('proximas')"
                    class="px-2.5 py-1 rounded-[8px] font-semibold transition cursor-pointer flex items-center gap-1 {{ $tabActivo === 'proximas' ? 'bg-[#A35A44] text-white shadow-2xs' : 'bg-[#F0E8DE] dark:bg-[#2C2924] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139] hover:bg-[#E4D8CC]' }}">
                    <i class="ph ph-hourglass-high text-xs"></i>
                    <span>Próximas dosis</span>
                    @if($conteoPendientes > 0)
                        <span class="ml-0.5 px-1 py-0.2 rounded-full text-[10px] {{ $tabActivo === 'proximas' ? 'bg-white/20 text-white' : 'bg-[#E4D8CC] dark:bg-[#211F1B] text-[#A35A44]' }}">{{ $conteoPendientes }}</span>
                    @endif
                </button>

                {{-- Omisiones --}}
                <button 
                    type="button"
                    wire:click="cambiarTab('omisiones')"
                    class="px-2.5 py-1 rounded-[8px] font-semibold transition cursor-pointer flex items-center gap-1 {{ $tabActivo === 'omisiones' ? 'bg-[#A35A44] text-white shadow-2xs' : 'bg-[#F0E8DE] dark:bg-[#2C2924] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139] hover:bg-[#E4D8CC]' }}">
                    <i class="ph ph-pause-circle text-xs"></i>
                    <span>Omisiones</span>
                    @if($conteoOmitidas > 0)
                        <span class="ml-0.5 px-1 py-0.2 rounded-full text-[10px] {{ $tabActivo === 'omisiones' ? 'bg-white/20 text-white' : 'bg-[#E4D8CC] dark:bg-[#211F1B] text-[#884A39]' }}">{{ $conteoOmitidas }}</span>
                    @endif
                </button>
            </div>
        @endif
    </div>

    {{-- ==================================================
         4. CONTENIDO PRINCIPAL SEGÚN PESTAÑA ACTIVA
         (La tabla empieza inmediatamente, sin contenedores redundantes)
         ================================================== --}}
    @if($tabActivo === 'kardex')
        @include('livewire.medicacion.partials.kardex-matriz')
    @elseif($tabActivo === 'proximas')
        @include('livewire.medicacion.partials.proximas-dosis')
    @elseif($tabActivo === 'omisiones')
        @include('livewire.medicacion.partials.omisiones')
    @elseif($tabActivo === 'historial')
        @include('livewire.medicacion.partials.historial')
    @endif

    {{-- ==================================================
         5. OVERLAYS Y MODALES CLÍNICOS
         ================================================== --}}
    @include('livewire.medicacion.partials.drawer-dosis')
    @include('livewire.medicacion.partials.modal-administrar')
    @include('livewire.medicacion.partials.modal-omision')
    @include('livewire.medicacion.partials.modal-medicamento')
</div>
