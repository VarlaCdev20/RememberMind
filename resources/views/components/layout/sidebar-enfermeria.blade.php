@php
    $currentRoute = request()->route()?->getName() ?? '';
    $esMiTurno = request()->routeIs('admin.enfermeria.dashboard*') || request()->routeIs('admin.enfermeria.mi-turno*');
    $esMisResidentes = request()->routeIs('admin.enfermeria.pacientes*') || request()->routeIs('admin.enfermeria.residentes*');
    $esCuidados = request()->routeIs('admin.enfermeria.agenda*') || request()->routeIs('admin.enfermeria.tareas*') || request()->routeIs('admin.enfermeria.cuidados*');
    $esMedicacion = request()->routeIs('admin.enfermeria.medicacion*') || request()->routeIs('admin.salud-seguimiento.medicacion*');
    $esPaseTurno = request()->routeIs('admin.enfermeria.pase-turno*');
    $esIncidentes = request()->routeIs('admin.enfermeria.registros*') || request()->routeIs('admin.enfermeria.incidentes*');
    $esAlertas = request()->routeIs('admin.enfermeria.alertas*');
@endphp

<aside id="sidebar-enfermeria"
       class="fixed inset-y-0 left-0 z-50 flex h-screen flex-col border-r border-[#D5CABE] dark:border-[#494139] bg-[#E4D8CB] dark:bg-[#29251F] text-[#46546E] dark:text-[#C8BFB4] transition-all duration-300 ease-in-out lg:translate-x-0"
       :class="[
           sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full',
           sidebarCollapsed ? 'w-[80px] min-w-[80px]' : 'w-[260px] min-w-[260px]'
       ]"
       aria-label="Navegación principal de ENFERMERÍA">

    {{-- BOTÓN FLOTANTE CIRCULAR CON FLECHA (Colapsar / Expandir) --}}
    {{-- Mismo tamaño del Superadmin, borde terracota, fondo beige, flecha navy, cambia dirección --}}
    <button type="button"
            @click.stop="toggleSidebarCollapse()"
            :title="sidebarCollapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral'"
            :aria-label="sidebarCollapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral'"
            class="hidden lg:flex absolute -right-4 top-[21px] z-50 w-8 h-8 rounded-full bg-[#F0E8DE] dark:bg-[#2D2924] border border-[#A35A44] dark:border-[#C47B63] shadow-[0_2px_8px_rgba(163,90,68,0.25)] text-[#304060] dark:text-[#EFE4D8] hover:bg-[#F7F1EA] hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer items-center justify-center">
        <i class="ph-bold ph-caret-left text-sm text-[#304060] dark:text-[#EFE4D8] transition-transform duration-300"
           :class="sidebarCollapsed ? 'rotate-180' : ''"></i>
    </button>

    {{-- Bloque Superior Institucional --}}
    <div class="h-[74px] shrink-0 flex items-center border-b border-[#D5CABE] dark:border-[#494139] bg-[#E4D8CB] dark:bg-[#29251F] transition-all duration-300"
         :class="sidebarCollapsed ? 'justify-center px-2' : 'justify-between px-[18px]'">
        <a href="{{ route('admin.enfermeria.dashboard') }}"
           class="flex items-center gap-3 transition-opacity duration-200 hover:opacity-95 min-w-0"
           :class="sidebarCollapsed ? 'justify-center' : ''"
           title="Centro Geriátrico Jardín de los Recuerdos">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}"
                 alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                 class="h-9 w-auto object-contain shrink-0"
                 onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            <div class="min-w-0 flex-1 overflow-hidden transition-all duration-300"
                 x-show="!sidebarCollapsed"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-x-2"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-2">
                <span class="block font-[700] text-[12px] leading-[1.08] text-[#304060] dark:text-[#EEE4D8] uppercase font-outfit whitespace-nowrap">
                    CENTRO GERIÁTRICO<br>JARDÍN DE LOS RECUERDOS
                </span>
                <span class="block text-[11px] font-[700] tracking-[0.05em] text-[#71876A] dark:text-[#9CAF93] uppercase font-outfit mt-0.5">
                    REMEMBERMIND
                </span>
            </div>
        </a>

        <button type="button"
                x-show="!sidebarCollapsed"
                @click="sidebarOpen = false"
                class="rounded-lg p-1.5 text-[#677084] dark:text-[#A99A8C] hover:bg-[#D8C9BA] dark:hover:bg-[#38342E] hover:text-[#304060] dark:hover:text-[#EEE4D8] lg:hidden transition-colors shrink-0 ml-1"
                aria-label="Cerrar barra lateral">
            <i class="ph-bold ph-x text-lg"></i>
        </button>
    </div>

    {{-- Buscador Sidebar --}}
    <div class="mx-[14px] mt-[12px] mb-[12px] shrink-0 transition-all duration-300"
         x-show="!sidebarCollapsed"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div class="relative flex items-center">
            <i class="ph-bold ph-magnifying-glass absolute left-3 text-[#677084] dark:text-[#A99A8C] text-[15px] pointer-events-none"></i>
            <input type="text"
                   placeholder="BUSCAR MÓDULO..."
                   class="w-full h-[38px] pl-9 pr-3 text-[12px] font-[600] uppercase placeholder:uppercase rounded-[12px] bg-[#F7F1EA] dark:bg-[#332F29] border border-[#D5CABE] dark:border-[#4A4238] text-[#304060] dark:text-[#EEE4D8] placeholder:text-[#677084] dark:placeholder:text-[#A99A8C] focus:outline-none focus:ring-1 focus:ring-[#71876A] dark:focus:ring-[#9CAF93] shadow-2xs transition-all duration-150">
        </div>
    </div>

    {{-- Área de Navegación --}}
    <nav class="flex-1 px-[12px] pt-1 space-y-1 overflow-y-auto custom-scrollbar transition-all duration-300"
         :class="sidebarCollapsed ? 'px-2' : 'px-[12px]'">

        {{-- MI TURNO --}}
        <a href="{{ route('admin.enfermeria.dashboard') }}"
           title="Mi turno"
           aria-label="Mi turno"
           class="group relative flex h-[48px] items-center rounded-[12px] text-[13.5px] font-[700] uppercase font-outfit transition-all duration-150 {{ $esMiTurno ? 'bg-[#8FA685] dark:bg-[#66785F] text-[#F7F2EC] dark:text-[#F5EEE5] shadow-[0_5px_12px_rgba(80,65,50,0.12)] before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-[3px] before:rounded-full before:bg-[#6F8967] dark:before:bg-[#8FA685]' : 'text-[#46546E] dark:text-[#C8BFB4] hover:bg-[rgba(163,90,68,.06)] dark:hover:bg-[rgba(214,174,134,.07)]' }}"
           :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-[14px]'">
            <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                <i class="ph-bold ph-clock-user text-[21px] shrink-0 {{ $esMiTurno ? 'text-[#F7F2EC] dark:text-[#F5EEE5]' : 'text-[#758098] dark:text-[#B3A99E] group-hover:text-[#46546E] dark:group-hover:text-[#EEE4D8]' }}"></i>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-200">MI TURNO</span>
                <span class="sr-only">Mi turno</span>
            </div>
        </a>

        {{-- MIS RESIDENTES --}}
        <a href="{{ Route::has('admin.enfermeria.pacientes') ? route('admin.enfermeria.pacientes') : '#' }}"
           title="Mis residentes"
           aria-label="Mis residentes"
           class="group relative flex h-[48px] items-center rounded-[12px] text-[13.5px] font-[700] uppercase font-outfit transition-all duration-150 {{ $esMisResidentes ? 'bg-[#8FA685] dark:bg-[#66785F] text-[#F7F2EC] dark:text-[#F5EEE5] shadow-[0_5px_12px_rgba(80,65,50,0.12)] before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-[3px] before:rounded-full before:bg-[#6F8967] dark:before:bg-[#8FA685]' : 'text-[#46546E] dark:text-[#C8BFB4] hover:bg-[rgba(163,90,68,.06)] dark:hover:bg-[rgba(214,174,134,.07)]' }}"
           :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-[14px]'">
            <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                <i class="ph-bold ph-users-three text-[21px] shrink-0 {{ $esMisResidentes ? 'text-[#F7F2EC] dark:text-[#F5EEE5]' : 'text-[#758098] dark:text-[#B3A99E] group-hover:text-[#46546E] dark:group-hover:text-[#EEE4D8]' }}"></i>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-200">MIS RESIDENTES</span>
                <span class="sr-only">Mis residentes</span>
            </div>
        </a>

        {{-- Grupo CUIDADO --}}
        <div class="pt-[14px]" :class="sidebarCollapsed ? 'pt-2' : 'pt-[14px]'">
            <p x-show="!sidebarCollapsed" class="pl-[10px] pb-1.5 text-[11.5px] font-[700] uppercase tracking-[0.06em] text-[#9A7D68] dark:text-[#C99678] font-outfit transition-all duration-200">
                CUIDADO <span class="sr-only">Cuidado</span>
            </p>
            <div x-show="sidebarCollapsed" class="my-2 border-t border-[#CDBEAF]/60 dark:border-[#423A32]/60 mx-2" aria-hidden="true"></div>

            {{-- CUIDADOS --}}
            <a href="{{ Route::has('admin.enfermeria.agenda') ? route('admin.enfermeria.agenda') : '#' }}"
               title="Cuidados"
               aria-label="Cuidados"
               class="group relative flex h-[48px] items-center rounded-[12px] text-[13.5px] font-[700] uppercase font-outfit transition-all duration-150 {{ $esCuidados ? 'bg-[#8FA685] dark:bg-[#66785F] text-[#F7F2EC] dark:text-[#F5EEE5] shadow-[0_5px_12px_rgba(80,65,50,0.12)] before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-[3px] before:rounded-full before:bg-[#6F8967] dark:before:bg-[#8FA685]' : 'text-[#46546E] dark:text-[#C8BFB4] hover:bg-[rgba(163,90,68,.06)] dark:hover:bg-[rgba(214,174,134,.07)]' }}"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-[14px]'">
                <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <i class="ph-bold ph-heartbeat text-[21px] shrink-0 {{ $esCuidados ? 'text-[#F7F2EC] dark:text-[#F5EEE5]' : 'text-[#758098] dark:text-[#B3A99E] group-hover:text-[#46546E] dark:group-hover:text-[#EEE4D8]' }}"></i>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-200">CUIDADOS</span>
                    <span class="sr-only">Cuidados</span>
                </div>
            </a>

            {{-- MEDICACIÓN --}}
            <a href="{{ Route::has('admin.enfermeria.medicacion') ? route('admin.enfermeria.medicacion') : (Route::has('admin.salud-seguimiento.medicacion.index') ? route('admin.salud-seguimiento.medicacion.index') : '#') }}"
               title="Medicación"
               aria-label="Medicación"
               class="group relative flex h-[48px] items-center rounded-[12px] text-[13.5px] font-[700] uppercase font-outfit transition-all duration-150 {{ $esMedicacion ? 'bg-[#8FA685] dark:bg-[#66785F] text-[#F7F2EC] dark:text-[#F5EEE5] shadow-[0_5px_12px_rgba(80,65,50,0.12)] before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-[3px] before:rounded-full before:bg-[#6F8967] dark:before:bg-[#8FA685]' : 'text-[#46546E] dark:text-[#C8BFB4] hover:bg-[rgba(163,90,68,.06)] dark:hover:bg-[rgba(214,174,134,.07)]' }}"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-[14px]'">
                <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <i class="ph-bold ph-pill text-[21px] shrink-0 {{ $esMedicacion ? 'text-[#F7F2EC] dark:text-[#F5EEE5]' : 'text-[#758098] dark:text-[#B3A99E] group-hover:text-[#46546E] dark:group-hover:text-[#EEE4D8]' }}"></i>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-200">MEDICACIÓN</span>
                    <span class="sr-only">Medicación</span>
                </div>
            </a>
        </div>

        {{-- Grupo CONTINUIDAD --}}
        <div class="pt-[18px]" :class="sidebarCollapsed ? 'pt-2' : 'pt-[18px]'">
            <p x-show="!sidebarCollapsed" class="pl-[10px] pb-1.5 text-[11.5px] font-[700] uppercase tracking-[0.06em] text-[#9A7D68] dark:text-[#C99678] font-outfit transition-all duration-200">
                CONTINUIDAD <span class="sr-only">Continuidad</span>
            </p>
            <div x-show="sidebarCollapsed" class="my-2 border-t border-[#CDBEAF]/60 dark:border-[#423A32]/60 mx-2" aria-hidden="true"></div>

            {{-- PASE DE TURNO --}}
            <a href="{{ Route::has('admin.enfermeria.pase-turno') ? route('admin.enfermeria.pase-turno') : '#' }}"
               title="Pase de turno"
               aria-label="Pase de turno"
               class="group relative flex h-[48px] items-center rounded-[12px] text-[13.5px] font-[700] uppercase font-outfit transition-all duration-150 {{ $esPaseTurno ? 'bg-[#8FA685] dark:bg-[#66785F] text-[#F7F2EC] dark:text-[#F5EEE5] shadow-[0_5px_12px_rgba(80,65,50,0.12)] before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-[3px] before:rounded-full before:bg-[#6F8967] dark:before:bg-[#8FA685]' : 'text-[#46546E] dark:text-[#C8BFB4] hover:bg-[rgba(163,90,68,.06)] dark:hover:bg-[rgba(214,174,134,.07)]' }}"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-[14px]'">
                <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <i class="ph-bold ph-arrows-clockwise text-[21px] shrink-0 {{ $esPaseTurno ? 'text-[#F7F2EC] dark:text-[#F5EEE5]' : 'text-[#758098] dark:text-[#B3A99E] group-hover:text-[#46546E] dark:group-hover:text-[#EEE4D8]' }}"></i>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-200">PASE DE TURNO</span>
                    <span class="sr-only">Pase de turno</span>
                </div>
            </a>

            {{-- INCIDENTES --}}
            <a href="{{ Route::has('admin.enfermeria.incidentes') ? route('admin.enfermeria.incidentes') : (Route::has('admin.enfermeria.registros') ? route('admin.enfermeria.registros') : '#') }}"
               title="Incidentes"
               aria-label="Incidentes"
               class="group relative flex h-[48px] items-center rounded-[12px] text-[13.5px] font-[700] uppercase font-outfit transition-all duration-150 {{ $esIncidentes ? 'bg-[#8FA685] dark:bg-[#66785F] text-[#F7F2EC] dark:text-[#F5EEE5] shadow-[0_5px_12px_rgba(80,65,50,0.12)] before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-[3px] before:rounded-full before:bg-[#6F8967] dark:before:bg-[#8FA685]' : 'text-[#46546E] dark:text-[#C8BFB4] hover:bg-[rgba(163,90,68,.06)] dark:hover:bg-[rgba(214,174,134,.07)]' }}"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-[14px]'">
                <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <i class="ph-bold ph-clipboard-text text-[21px] shrink-0 {{ $esIncidentes ? 'text-[#F7F2EC] dark:text-[#F5EEE5]' : 'text-[#758098] dark:text-[#B3A99E] group-hover:text-[#46546E] dark:group-hover:text-[#EEE4D8]' }}"></i>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-200">INCIDENTES</span>
                    <span class="sr-only">Incidentes</span>
                </div>
            </a>

            {{-- ALERTAS --}}
            @php
                $conteoAlertas = $alertasCount ?? 3;
            @endphp
            <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"
               title="Alertas"
               aria-label="Alertas"
               class="group relative flex h-[48px] items-center rounded-[12px] text-[13.5px] font-[700] uppercase font-outfit transition-all duration-150 {{ $esAlertas ? 'bg-[#8FA685] dark:bg-[#66785F] text-[#F7F2EC] dark:text-[#F5EEE5] shadow-[0_5px_12px_rgba(80,65,50,0.12)] before:absolute before:left-0 before:top-2.5 before:bottom-2.5 before:w-[3px] before:rounded-full before:bg-[#6F8967] dark:before:bg-[#8FA685]' : 'text-[#46546E] dark:text-[#C8BFB4] hover:bg-[rgba(163,90,68,.06)] dark:hover:bg-[rgba(214,174,134,.07)]' }}"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-[14px]'">
                <div class="relative flex items-center gap-3 min-w-0" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <i class="ph-bold ph-bell-ringing text-[21px] shrink-0 {{ $esAlertas ? 'text-[#F7F2EC] dark:text-[#F5EEE5]' : 'text-[#758098] dark:text-[#B3A99E] group-hover:text-[#46546E] dark:group-hover:text-[#EEE4D8]' }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate whitespace-nowrap transition-opacity duration-200">ALERTAS</span>
                    <span class="sr-only">Alertas</span>
                    @if($conteoAlertas > 0)
                        <span x-show="sidebarCollapsed" class="absolute -top-1 -right-1.5 w-2.5 h-2.5 rounded-full bg-[#D92525] ring-2 ring-[#E4D8CB] dark:ring-[#29251F]"></span>
                    @endif
                </div>
                @if($conteoAlertas > 0)
                    <span x-show="!sidebarCollapsed" class="inline-flex items-center justify-center w-[21px] h-[21px] rounded-full bg-[#D92525] text-white text-[10.5px] font-[700] shrink-0 ml-auto shadow-2xs">
                        {{ $conteoAlertas }}
                    </span>
                @endif
            </a>
        </div>
    </nav>

    {{-- Área inferior sutil para evitar duplicar perfil (el perfil principal reside en la topbar) --}}
    <div class="mt-auto shrink-0 p-3 bg-transparent">
        <span class="sr-only">ENFERMERÍA</span>
        <div class="h-1 w-full bg-transparent"></div>
    </div>
</aside>
