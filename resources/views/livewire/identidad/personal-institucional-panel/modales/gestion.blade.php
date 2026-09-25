@if($modalGestionAbierto)
        @if(!$usuarioSeleccionadoId)
            <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 px-4 backdrop-blur-sm sm:p-6">
                <div class="relative my-8 flex w-full max-w-3xl flex-col overflow-hidden rounded-[2rem] border border-borde bg-fondo-card shadow-2xl">
                    <livewire:identidad.personal-institucional-form :usuario-id="null" wire:key="wizard-nuevo" />
                </div>
            </div>
        @else
            <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 px-4 backdrop-blur-sm sm:p-6">
                <div class="relative flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-[2rem] border border-borde bg-fondo-card shadow-2xl">
                    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-borde bg-fondo-card px-8 py-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-inner">
                                <i class="ph-fill ph-user-circle-gear text-2xl"></i>
                            </div>

                            <div>
                                <h3 class="text-xl font-black text-titulo">Ficha Operativa de Personal</h3>
                                <p class="mt-0.5 text-xs font-semibold text-apoyo">
                                    Administra datos, horarios y carga operativa del empleado.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="cerrarModal"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-borde bg-fondo-hover text-apoyo transition-colors hover:bg-estado-peligroBg hover:text-estado-peligro"
                        >
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <div x-data="{ tabInterna: @js($modalTabInicial) }" class="flex flex-1 flex-col overflow-hidden bg-fondo-card md:flex-row">
                        <div class="flex w-full flex-col gap-2 overflow-y-auto border-b border-borde bg-fondo-card p-4 md:w-64 md:border-b-0 md:border-r">
                            <div class="mb-2 px-3 text-[10px] font-black uppercase tracking-wider text-apoyo">
                                Navegación del perfil
                            </div>

                            <button
                                type="button"
                                @click="tabInterna = 'informacion'"
                                :class="tabInterna === 'informacion' ? 'bg-boton-acento/10 border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:bg-fondo-hover hover:text-titulo'"
                                class="group flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-left text-sm font-bold transition-all"
                            >
                                <i class="ph-fill ph-identification-card text-lg transition-transform group-hover:scale-110"></i>
                                Información base
                            </button>

                            <button
                                type="button"
                                @click="tabInterna = 'horarios'"
                                :class="tabInterna === 'horarios' ? 'bg-boton-acento/10 border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:bg-fondo-hover hover:text-titulo'"
                                class="group flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-left text-sm font-bold transition-all"
                            >
                                <i class="ph-fill ph-calendar-check text-lg transition-transform group-hover:scale-110"></i>
                                Horarios y turnos
                            </button>

                            <div class="my-2 border-t border-borde"></div>

                            <button
                                type="button"
                                @click="tabInterna = 'reportes'"
                                :class="tabInterna === 'reportes' ? 'bg-boton-acento/10 border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:bg-fondo-hover hover:text-titulo'"
                                class="group flex items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-left text-sm font-bold transition-all"
                            >
                                <i class="ph-fill ph-chart-polar text-lg transition-transform group-hover:scale-110"></i>
                                Carga operativa
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto bg-fondo-card p-6 lg:p-8">
                            <div
                                x-show="tabInterna === 'informacion'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                            >
                                <livewire:identidad.personal-institucional-form :usuario-id="$usuarioSeleccionadoId" wire:key="form-{{ $usuarioSeleccionadoId }}" />
                            </div>

                            <div
                                x-show="tabInterna === 'horarios'"
                                style="display: none;"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                            >
                                <livewire:identidad.personal-institucional-horarios
                                    :usuario-id="$usuarioSeleccionadoId"
                                    :abrir-formulario-inicial="$abrirFormularioHorarioInicial"
                                    wire:key="horarios-{{ $usuarioSeleccionadoId }}-{{ $abrirFormularioHorarioInicial ? 'nuevo' : 'gestion' }}"
                                />
                            </div>

                            <div
                                x-show="tabInterna === 'reportes'"
                                style="display: none;"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                            >
                                <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-borde bg-fondo-card p-12 text-apoyo shadow-sm">
                                    <i class="ph-fill ph-chart-line-up mb-4 text-6xl text-boton-acento/30"></i>
                                    <h4 class="text-xl font-black text-titulo">Desempeño y carga</h4>
                                    <p class="mt-2 max-w-md text-center text-sm font-semibold">
                                        Estadísticas de pacientes atendidos, asistencia y carga institucional del empleado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
