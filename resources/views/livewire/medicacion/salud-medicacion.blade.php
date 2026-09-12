<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-5">
    @if($adulto)
        <x-residentes.navegacion-ficha :adulto="$adulto" />
    @endif
    {{-- 1. CABECERA Y BOTON DE APARTADO --}}
    <section class="rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 rounded-full border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-pill text-[var(--rm-primary)]"></i>
                    <span>Módulo Clínico Farmacológico</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-[var(--rm-text-body)] sm:text-3xl">
                    Gestión y Prescripción de Medicación
                </h1>
                <p class="text-xs font-bold text-[var(--rm-text-muted)] sm:text-sm">
                    @if($adulto)
                        Tratamientos y tomas activas para <span class="font-black text-[var(--rm-text-body)]">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</span>
                    @else
                        Control integral de fármacos, horarios y recetas para todos los residentes del centro.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if(auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']))
                    <button wire:click="toggleFormularioCrear" type="button" class="inline-flex items-center gap-2 rounded-xl border border-[var(--rm-primary)] bg-[var(--rm-primary)] px-4 py-2.5 text-xs font-black text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover hover:shadow active:scale-95">
                        <i class="ph-bold {{ $mostrarFormularioCrear ? 'ph-x' : 'ph-plus-circle' }} text-base"></i>
                        <span>{{ $mostrarFormularioCrear ? 'Cerrar formulario' : 'Prescribir medicamento' }}</span>
                    </button>
                @endif

                @if($adulto)
                    <button type="button" wire:click="verUbicacion('{{ $adulto->cod_am }}')" class="inline-flex items-center gap-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-4 py-2.5 text-xs font-bold text-[var(--rm-text-muted)] shadow-sm transition hover:bg-fondo-hover hover:text-[var(--rm-text-body)] active:scale-95 cursor-pointer" title="Ver ficha y ubicación del residente">
                        <i class="ph-bold ph-bed text-base text-[var(--rm-primary)]"></i>
                        <span>Ficha y Ubicación</span>
                    </button>
                    <button type="button" wire:click="verGraficos('{{ $adulto->cod_am }}')" class="inline-flex items-center gap-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-4 py-2.5 text-xs font-bold text-[var(--rm-text-muted)] shadow-sm transition hover:bg-fondo-hover hover:text-[var(--rm-text-body)] active:scale-95 cursor-pointer" title="Ver gráficos clínicos">
                        <i class="ph-bold ph-chart-line-up text-base text-boton-acento"></i>
                        <span>Gráficos Clínicos</span>
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- MENSAJE DE CONFIRMACIÓN --}}
    @if (session()->has('mensaje_exito'))
        <div class="flex items-center gap-3 rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-4 text-xs font-bold text-estado-exito shadow-sm">
            <i class="ph-bold ph-check-circle text-xl flex-shrink-0"></i>
            <span class="flex-1">{{ session('mensaje_exito') }}</span>
        </div>
    @endif

    {{-- 2. MODAL FLOTANTE: PRESCRIBIR / AGREGAR MEDICAMENTO --}}
    <x-ui.modal-livewire id="modalPrescribirMedicacion" wire:model="mostrarFormularioCrear" maxWidth="3xl" closeMethod="cancelarCreacion">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-primary)] text-inverso shadow-sm">
                <i class="ph-bold ph-pill text-lg"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div class="flex items-center gap-2">
                <span>Prescribir / Agregar Medicamento</span>
                <span class="rounded-md bg-[var(--rm-primary)]/15 px-2 py-0.5 text-[10px] font-bold text-[var(--rm-primary)] uppercase">
                    Nuevo Tratamiento
                </span>
            </div>
        </x-slot>

        <form wire:submit="guardarNuevoMedicamento" id="formNuevoMedicamentoModal" class="space-y-5">
                                <div class="grid gap-5 sm:grid-cols-2">
                                    {{-- SELECCIÓN DEL RESIDENTE --}}
                                    <div class="sm:col-span-2">
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Adulto Mayor / Residente <span class="text-estado-peligro">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <select wire:model="nuevo_cod_am" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition appearance-none focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                                <option value="">-- Seleccione un residente --</option>
                                                @foreach($adultos as $ad)
                                                    <option value="{{ $ad->cod_am }}">{{ $ad->nombres }} {{ $ad->ap_paterno }} ({{ $ad->cod_am }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('nuevo_cod_am') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- NOMBRE DEL FÁRMACO --}}
                                    <div class="sm:col-span-2">
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Nombre del Medicamento / Principio Activo <span class="text-estado-peligro">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-first-aid-kit absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <input type="text" list="sugerencias-farmacos" wire:model="nuevo_nombre" placeholder="Ej: Paracetamol, Losartán, Enalapril, Omeprazol..." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-meta focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                            <datalist id="sugerencias-farmacos">
                                                <option value="Paracetamol 500 mg">
                                                <option value="Losartán 50 mg">
                                                <option value="Enalapril 10 mg">
                                                <option value="Metformina 850 mg">
                                                <option value="Omeprazol 20 mg">
                                                <option value="Atorvastatina 20 mg">
                                                <option value="Amlodipino 5 mg">
                                                <option value="Levotiroxina 50 mcg">
                                                <option value="Salbutamol Inhalador 100 mcg">
                                                <option value="Sertralina 50 mg">
                                                <option value="Insulina NPH">
                                                <option value="Ácido Fólico 1 mg">
                                                <option value="Complejo B">
                                            </datalist>
                                        </div>
                                        @error('nuevo_nombre') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- DOSIS / CONCENTRACIÓN --}}
                                    <div>
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Dosis / Concentración <span class="text-estado-peligro">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-eyedropper absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <input type="text" list="sugerencias-dosis" wire:model="nuevo_dosis" placeholder="Ej: 500 mg, 1 tableta, 10 ml..." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-meta focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                            <datalist id="sugerencias-dosis">
                                                <option value="1 tableta (500 mg)">
                                                <option value="1 comprimido (50 mg)">
                                                <option value="1 cápsula">
                                                <option value="10 ml (1 cucharada)">
                                                <option value="5 ml (1 cucharadita)">
                                                <option value="2 gotas">
                                                <option value="1 puff / inhalación">
                                                <option value="1 ampolla">
                                            </datalist>
                                        </div>
                                        @error('nuevo_dosis') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- VÍA DE ADMINISTRACIÓN --}}
                                    <div>
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Vía de Administración <span class="text-estado-peligro">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-syringe absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <select wire:model="nuevo_via" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition appearance-none focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                                <option value="ORAL">Oral</option>
                                                <option value="SUBLINGUAL">Sublingual</option>
                                                <option value="INTRAVENOSA">Intravenosa</option>
                                                <option value="INTRAMUSCULAR">Intramuscular</option>
                                                <option value="SUBCUTANEA">Subcutánea</option>
                                                <option value="TOPICA">Tópica / Dérmica</option>
                                                <option value="INHALATORIA">Inhalatoria</option>
                                                <option value="OFTALMICA">Oftálmica</option>
                                                <option value="OTICA">Ótica</option>
                                                <option value="RECTAL">Rectal</option>
                                            </select>
                                        </div>
                                        @error('nuevo_via') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- FRECUENCIA / POSOLOGÍA --}}
                                    <div>
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Frecuencia / Pauta <span class="text-estado-peligro">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-repeat absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <input type="text" list="sugerencias-frecuencia" wire:model="nuevo_frecuencia" placeholder="Ej: Cada 8 horas, Cada 12 horas..." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-meta focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                            <datalist id="sugerencias-frecuencia">
                                                <option value="Cada 8 horas">
                                                <option value="Cada 12 horas">
                                                <option value="Cada 24 horas (Una vez al día)">
                                                <option value="Cada 6 horas">
                                                <option value="En ayunas (Mañanas)">
                                                <option value="Con el almuerzo">
                                                <option value="En la noche antes de dormir">
                                                <option value="Condicional / En caso de dolor">
                                            </datalist>
                                        </div>
                                        @error('nuevo_frecuencia') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- HORA PROGRAMADA INICIAL --}}
                                    <div>
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Hora Programada (Toma Inicial) <span class="text-estado-peligro">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-clock absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <input type="time" wire:model="nuevo_hora" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                        </div>
                                        @error('nuevo_hora') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- FECHA DE INICIO --}}
                                    <div>
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Fecha de Inicio <span class="text-estado-peligro">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-calendar-check absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <input type="date" wire:model="nuevo_fecha_inicio" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                        </div>
                                        @error('nuevo_fecha_inicio') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- FECHA DE FIN --}}
                                    <div>
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Fecha de Fin / Conclusión (Opcional)
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-calendar-x absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <input type="date" wire:model="nuevo_fecha_fin" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                        </div>
                                        @error('nuevo_fecha_fin') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- MÉDICO PRESCRIPTOR --}}
                                    <div class="sm:col-span-2">
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Médico Tratante / Prescriptor
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-stethoscope absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                                            <input type="text" wire:model="nuevo_medico" placeholder="Ej: Dr. Roberto Mendoza" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-meta focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                        </div>
                                        @error('nuevo_medico') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
    
                                    {{-- OBSERVACIONES / INDICACIONES --}}
                                    <div class="sm:col-span-2">
                                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                                            Indicaciones Especiales / Observaciones Clínicas
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-notebook absolute left-3.5 top-3 text-meta"></i>
                                            <textarea wire:model="nuevo_observacion" rows="2" placeholder="Ej: Administrar con abundante agua después de los alimentos. Monitorear presión arterial previa." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-meta focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20"></textarea>
                                        </div>
                                        @error('nuevo_observacion') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                                    </div>
                                </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cancelarCreacion" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-bg-app)] px-5 py-2.5 text-xs font-bold text-[var(--rm-text-muted)] transition hover:bg-[var(--rm-surface)] hover:text-[var(--rm-text-body)] active:scale-95 cursor-pointer">
                Cancelar
            </button>
            <button type="submit" form="formNuevoMedicamentoModal" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl bg-[var(--rm-primary)] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 cursor-pointer">
                <i wire:loading wire:target="guardarNuevoMedicamento" class="ph-bold ph-spinner animate-spin"></i>
                <i wire:loading.remove wire:target="guardarNuevoMedicamento" class="ph-bold ph-check-circle text-base"></i>
                <span>Guardar Medicamento</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>

    {{-- 3. TARJETAS DE ESTADÍSTICAS --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="group relative overflow-hidden rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Activas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-check-circle text-base"></i>
                </span>
            </div>
            <p class="mt-3 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['activas'] }}</p>
            <span class="text-[10px] font-bold text-estado-exito">Prescripciones en curso</span>
        </div>

        <div class="group relative overflow-hidden rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Suspendidas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
                    <i class="ph-bold ph-pause-circle text-base"></i>
                </span>
            </div>
            <p class="mt-3 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['suspendidas'] }}</p>
            <span class="text-[10px] font-bold text-boton-acento">Pausadas por criterio médico</span>
        </div>

        <div class="group relative overflow-hidden rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Finalizadas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-check-square-offset text-base"></i>
                </span>
            </div>
            <p class="mt-3 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['finalizadas'] }}</p>
            <span class="text-[10px] font-bold text-[var(--rm-text-muted)]">Tratamientos concluidos</span>
        </div>

        <div class="group relative overflow-hidden rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Archivadas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-bg-app)] text-meta">
                    <i class="ph-bold ph-archive text-base"></i>
                </span>
            </div>
            <p class="mt-3 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['archivadas'] }}</p>
            <span class="text-[10px] font-bold text-meta">Histórico en expediente</span>
        </div>
    </section>

    {{-- 4. CONTENIDO PRINCIPAL: COLUMNA LATERAL Y LISTADO --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- ASIDE IZQUIERDO: SELECTOR DE ADULTO MAYOR Y RESUMEN --}}
        <aside class="space-y-5">
            <section class="rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl">
                <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
                    Filtrar por Residente
                </label>
                <div class="relative">
                    <i class="ph-bold ph-user-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                    <select wire:model.live="cod_am" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition appearance-none focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                        <option value="">-- Todos los Residentes --</option>
                        @foreach($adultos as $ad)
                            <option value="{{ $ad->cod_am }}">{{ $ad->nombres }} {{ $ad->ap_paterno }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            @if($adulto)
                {{-- CARD FLOTANTE DEL ADULTO MAYOR SELECCIONADO --}}
                <section class="rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-[var(--rm-border)]-suave pb-4 mb-4">
                        <div class="h-12 w-12 overflow-hidden rounded-xl border-2 border-[var(--rm-border)]-suave bg-[var(--rm-primary)] shadow-sm">
                            @if($adulto->foto)
                                <img src="{{ Storage::url($adulto->foto) }}" alt="Foto" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-sm font-black text-inverso">
                                    {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-[var(--rm-text-body)]">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
                            <span class="inline-block mt-0.5 rounded bg-[var(--rm-bg-app)] px-2 py-0.5 text-[9px] font-black uppercase text-[var(--rm-text-muted)] border border-[var(--rm-border)]/50">{{ $adulto->edad ?? 'Edad no registrada' }}{{ $adulto->edad ? ' años' : '' }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3 text-xs text-[var(--rm-text-muted)]">
                        <div class="flex justify-between items-center rounded-lg bg-[var(--rm-bg-app)] px-3 py-2 border border-[var(--rm-border)]-suave">
                            <span class="font-bold">Medicaciones Activas</span>
                            <span class="font-black text-estado-exito">{{ $stats['activas'] }}</span>
                        </div>
                        
                        @if(auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']))
                            <button type="button" wire:click="abrirFormularioPara('{{ $adulto->cod_am }}')" class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-[var(--rm-primary)] bg-[var(--rm-primary)] px-3 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover active:scale-95">
                                <i class="ph-bold ph-plus-circle text-sm"></i>
                                <span>Prescribir para {{ strtok($adulto->nombres, ' ') }}</span>
                            </button>
                        @endif
                    </div>
                </section>
                
                {{-- PANEL PRÓXIMA TOMA --}}
                <section class="rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl relative overflow-hidden">
                    <h3 class="mb-3 text-[10px] font-black uppercase tracking-widest text-[var(--rm-text-body)] flex items-center gap-1.5">
                        <i class="ph-bold ph-clock text-[var(--rm-primary)]"></i> Agenda de hoy
                    </h3>

                    <div class="space-y-2">
                        @forelse($agenda as $toma)
                            @php
                                $claseAgenda = match($toma['estado']) {
                                    'ADMINISTRADA' => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
                                    'OMITIDA', 'VENCIDA' => 'border-[var(--rm-border)]-focus bg-estado-peligroBg text-boton-acento',
                                    'PROXIMA' => 'border-[var(--rm-primary)]/40 bg-[var(--rm-primary)]/10 text-[var(--rm-primary)]',
                                    default => 'border-[var(--rm-border)] bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]',
                                };
                            @endphp
                            <div class="rounded-xl border px-3 py-2 {{ $claseAgenda }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-[11px] font-black">{{ $toma['medicacion']->nombre_medicamento }}</span>
                                    <time class="text-xs font-black">{{ $toma['hora'] }}</time>
                                </div>
                                <p class="mt-1 text-[9px] font-black uppercase tracking-wider">{{ $toma['estado'] === 'PROXIMA' ? 'PRÓXIMA' : $toma['estado'] }}</p>
                            </div>
                        @empty
                            <div class="py-3 text-center">
                                <i class="ph-bold ph-calendar-x text-2xl text-[var(--rm-text-muted)]"></i>
                                <p class="mt-2 text-xs font-bold text-[var(--rm-text-muted)]">No hay tomas activas programadas para hoy.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @else
                <section class="rounded-[1.6rem] border border-dashed border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-6 text-center shadow-inner">
                    <i class="ph-bold ph-hand-pointing text-3xl text-[var(--rm-text-muted)]/40"></i>
                    <p class="mt-2 text-xs font-bold text-[var(--rm-text-muted)]">Seleccione un residente para ver su perfil farmacológico particular o use el botón superior para agregar un medicamento.</p>
                </section>
            @endif
        </aside>
        {{-- COLUMNA PRINCIPAL (FILTROS Y TABLA) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- FILTROS DE BÚSQUEDA --}}
            <section class="rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-4 shadow-sm backdrop-blur-xl sm:p-5">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-body)]">Filtros de Búsqueda</h3>
                    </div>
                    @if($search !== '' || $filtroEstado !== '' || $filtroVia !== '')
                        <button wire:click="limpiarFiltros" type="button" class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-boton-acento transition hover:text-[var(--rm-text-body)]">
                            <i class="ph-bold ph-x-circle"></i>
                            Limpiar filtros
                        </button>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[9px] font-bold uppercase tracking-widest text-[var(--rm-text-body)]/60">Medicamento</label>
                        <div class="relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar medicamento..." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-meta focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                        </div>
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-[9px] font-bold uppercase tracking-widest text-[var(--rm-text-body)]/60">Estado</label>
                        <div class="relative">
                            <i class="ph-bold ph-funnel absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                            <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition appearance-none focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                <option value="">Todos los estados</option>
                                <option value="ACTIVO">Activos</option>
                                <option value="SUSPENDIDO">Suspendidos</option>
                                <option value="FINALIZADO">Finalizados</option>
                                <option value="ARCHIVADO">Archivados (Histórico)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[9px] font-bold uppercase tracking-widest text-[var(--rm-text-body)]/60">Vía</label>
                        <div class="relative">
                            <i class="ph-bold ph-flask absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                            <select wire:model.live="filtroVia" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition appearance-none focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
                                <option value="">Todas las vías</option>
                                @foreach($viasDisponibles as $via)
                                    <option value="{{ $via }}">{{ $via }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            {{-- TABLA DE MEDICACIONES --}}
            <section class="rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] shadow-sm backdrop-blur-xl overflow-hidden relative">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)]-suave bg-[var(--rm-surface)] px-5 py-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-body)]">
                        @if($adulto) 
                            Medicación de {{ $adulto->nombres }} {{ $adulto->ap_paterno }}
                        @else
                            Listado General de Medicación
                        @endif
                    </h3>
                    <span class="inline-flex w-max items-center gap-2 rounded-full bg-[var(--rm-bg-app)] border border-[var(--rm-border)]/60 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-body)]/70">
                        <i class="ph-bold ph-list-checks text-[var(--rm-primary)]"></i>
                        {{ $medicaciones->total() }} prescripciones
                    </span>
                </div>

                @if($medicaciones->isEmpty())
                    <div class="p-10 text-center space-y-4">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]/50 border border-[var(--rm-border)]-suave">
                            <i class="ph-bold ph-pill text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-[var(--rm-text-body)]">No se encontraron medicamentos</h3>
                            <p class="mx-auto mt-1 max-w-md text-xs font-bold text-[var(--rm-text-muted)]">
                                @if($search !== '' || $filtroEstado !== '' || $filtroVia !== '')
                                    No hay resultados que coincidan con los filtros aplicados.
                                @elseif($adulto)
                                    No hay prescripciones registradas para este adulto mayor.
                                @else
                                    No hay medicación registrada en el sistema.
                                @endif
                            </p>
                        </div>
                        @if(auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']))
                        <button type="button" wire:click="toggleFormularioCrear" class="inline-flex items-center gap-2 rounded-xl border border-[var(--rm-primary)] bg-[var(--rm-primary)] px-4 py-2.5 text-xs font-bold text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover active:scale-95">
                            <i class="ph-bold ph-plus-circle text-base"></i>
                            <span>Prescribir Primer Medicamento</span>
                        </button>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-[var(--rm-text-body)]">
                            <thead class="bg-[var(--rm-bg-app)] text-[9px] font-black uppercase tracking-widest text-[var(--rm-text-muted)] border-b border-[var(--rm-border)]-suave">
                                <tr>
                                    @if(!$adulto)
                                        <th class="px-5 py-4">Residente</th>
                                    @endif
                                    <th class="px-5 py-4">Medicamento</th>
                                    <th class="px-5 py-4">Dosis / Vía</th>
                                    <th class="px-5 py-4">Frecuencia</th>
                                    <th class="px-5 py-4">Estado</th>
                                    <th class="px-5 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde-suave/60 bg-[var(--rm-surface)]">
                                @foreach($medicaciones as $med)
                                    @php
                                        $estado = strtoupper($med->estado ?? 'ACTIVO');
                                        $estadoClases = match($estado) {
                                            'ACTIVO' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
                                            'PAUSADO' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
                                            'SUSPENDIDO' => 'bg-estado-peligroBg text-boton-acento border-[var(--rm-border)]-focus',
                                            'FINALIZADO' => 'bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)] border-[var(--rm-border)]-fuerte',
                                            'ARCHIVADO' => 'bg-[var(--rm-bg-app)] text-meta border-[var(--rm-border)]-suave',
                                            default => 'bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)] border-[var(--rm-border)]/45',
                                        };
                                        $esInactivo = in_array($estado, ['FINALIZADO', 'ARCHIVADO']);
                                    @endphp
                                    <tr class="transition-colors hover:bg-[var(--rm-bg-app)]/50 {{ $esInactivo ? 'opacity-70' : '' }}">
                                        @if(!$adulto)
                                            <td class="px-5 py-4">
                                                <p class="text-xs font-black text-[var(--rm-text-body)]">{{ $med->adultoMayor->nombres ?? 'S/D' }} {{ $med->adultoMayor->ap_paterno ?? '' }}</p>
                                                <p class="mt-0.5 text-[9px] font-black text-[var(--rm-text-muted)] uppercase">{{ $med->adultoMayor?->habitacion?->codigo ? 'Habitación '.$med->adultoMayor->habitacion->codigo : 'Habitación sin asignar' }}</p>
                                            </td>
                                        @endif
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-[var(--rm-text-body)]">{{ $med->nombre_medicamento }}</p>
                                            @if($med->medico_indica)
                                                <p class="mt-0.5 text-[9px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wide">
                                                    Dr. {{ $med->medico_indica }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-xs font-black text-[var(--rm-text-body)]">{{ $med->dosis ?: 'S/D' }}</p>
                                            <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-[var(--rm-bg-app)] px-2 py-0.5 text-[9px] font-bold text-[var(--rm-text-muted)] border border-[var(--rm-border)]-suave">
                                                {{ $med->via_administracion ?: 'S/D' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-[10px] font-bold text-[var(--rm-text-muted)]">
                                                <i class="ph-bold ph-clock mr-0.5 text-[var(--rm-primary)]"></i> {{ $med->frecuencia ?: 'S/D' }}
                                            </p>
                                            @if($med->hora_programada)
                                                <p class="text-[9px] font-bold text-[var(--rm-text-muted)] mt-1">
                                                    Hora: {{ \Carbon\Carbon::parse($med->hora_programada)->format('H:i') }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider inline-flex items-center gap-1 {{ $estadoClases }}">
                                                @if($estado === 'ACTIVO') <i class="ph-bold ph-check-circle"></i>
                                                @elseif($estado === 'PAUSADO') <i class="ph-bold ph-warning"></i>
                                                @elseif($estado === 'SUSPENDIDO') <i class="ph-bold ph-pause-circle"></i>
                                                @elseif($estado === 'FINALIZADO') <i class="ph-bold ph-check-square-offset"></i>
                                                @elseif($estado === 'ARCHIVADO') <i class="ph-bold ph-archive"></i>
                                                @endif
                                                {{ $estado }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($estado === 'ACTIVO')
                                                    <button type="button" @click="$dispatch('abrirModalAdministracion', { cod_am: '{{ $med->cod_am }}', cod_med_adulto: '{{ $med->cod_med_adulto }}' })" class="inline-flex items-center gap-1.5 rounded-lg bg-[var(--rm-primary)] px-2.5 py-1.5 text-[9px] font-bold uppercase text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover active:scale-95" title="Registrar toma de medicación">
                                                        <i class="ph-bold ph-check-square text-xs"></i>
                                                        Toma
                                                    </button>
                                                @endif

                                                @if(auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']))
                                                    <button type="button" @click="$dispatch('abrirModalMedicacion', { cod_am: '{{ $med->cod_am }}', id_med: {{ $med->cod_med_adulto }} })" class="inline-flex items-center justify-center rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] p-1.5 text-[var(--rm-text-muted)] transition hover:bg-[var(--rm-bg-app)] hover:text-[var(--rm-text-body)] active:scale-95" title="Editar prescripción">
                                                        <i class="ph-bold ph-pencil-simple"></i>
                                                    </button>
                                                @endif

                                                @if($estado === 'ACTIVO' && auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']))
                                                    <button type="button"
                                                        wire:click="suspenderMedicamento({{ $med->cod_med_adulto }})"
                                                        wire:confirm="¿Seguro que desea suspender la medicación '{{ $med->nombre_medicamento }}'?"
                                                        class="inline-flex items-center justify-center rounded-lg border border-[var(--rm-border)]-focus bg-estado-peligroBg p-1.5 text-boton-acento transition hover:bg-boton-acento hover:text-inverso active:scale-95 cursor-pointer"
                                                        title="Suspender medicamento">
                                                        <i class="ph-bold ph-pause-circle"></i>
                                                    </button>
                                                    <button type="button"
                                                        wire:click="finalizarMedicamento({{ $med->cod_med_adulto }})"
                                                        wire:confirm="¿Seguro que desea finalizar el tratamiento de '{{ $med->nombre_medicamento }}'?"
                                                        class="inline-flex items-center justify-center rounded-lg border border-[var(--rm-border)]-fuerte bg-[var(--rm-surface)] p-1.5 text-[var(--rm-text-body)]/80 transition hover:bg-[var(--rm-primary)] hover:text-inverso active:scale-95 cursor-pointer"
                                                        title="Finalizar tratamiento">
                                                        <i class="ph-bold ph-check-square-offset"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($medicaciones->hasPages())
                        <div class="border-t border-[var(--rm-border)]-suave bg-[var(--rm-surface)] px-5 py-4">
                            {{ $medicaciones->links() }}
                        </div>
                    @endif
                @endif
            </section>
        </div>
    </div>

    {{-- MODALES AUXILIARES --}}
    @if($adulto)
        <livewire:medicacion.medicacion-adulto-modal :cod_am="$adulto->cod_am" :key="'med-modal-'.$adulto->cod_am" />
        <livewire:medicacion.administracion-medicacion-modal :cod_am="$adulto->cod_am" :key="'admin-modal-'.$adulto->cod_am" />
    @else
        <livewire:medicacion.medicacion-adulto-modal cod_am="" />
        <livewire:medicacion.administracion-medicacion-modal cod_am="" />
    @endif

    {{-- Paneles Laterales Desplegables (Drawers) --}}
    @include('livewire.alertas.modales.drawer-graficos')
    @include('livewire.alertas.modales.drawer-ubicacion')
</div>
