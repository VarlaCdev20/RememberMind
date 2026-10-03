<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-5">
 @php
 $puedeActuarComoMedico = app(\App\Backend\Modulos\Clinica\Servicios\AccesoClinicoTemporalService::class)
     ->tieneRol(auth()->user(), ['MEDICO GENERAL/GERIATRA']);
 @endphp
 @if($adulto)
 <x-residentes.navegacion-ficha :adulto="$adulto" />
 @endif
 {{-- 1. CABECERA Y ACCIONES DEL MÓDULO --}}
 <x-ui.page-header
  title="Gestión y Prescripción de Medicación"
  :subtitle="$adulto
   ? 'Tratamientos y tomas activas para ' . $adulto->nombres . ' ' . $adulto->ap_paterno . ' ' . $adulto->ap_materno
   : 'Control integral de fármacos, horarios y recetas para todos los residentes del centro.'"
  overline="Módulo clínico farmacológico"
  icon="ph-pill">
 @if($puedeActuarComoMedico && auth()->user()?->can('prescripciones.crear'))
  <x-ui.action-button
   variant="primary"
   size="sm"
   :icono="$mostrarFormularioCrear ? 'ph-x' : 'ph-plus-circle'"
   wire:click="toggleFormularioCrear">
   {{ $mostrarFormularioCrear ? 'Cerrar formulario' : 'Prescribir medicamento' }}
  </x-ui.action-button>
 @endif

 @if($adulto)
  <x-ui.action-button variant="secondary" size="sm" icono="ph-bed" wire:click="verUbicacion('{{ $adulto->cod_residente }}')" title="Ver ficha y ubicación del residente">
   Ficha y ubicación
  </x-ui.action-button>
  <x-ui.action-button variant="secondary" size="sm" icono="ph-chart-line-up" wire:click="verGraficos('{{ $adulto->cod_residente }}')" title="Ver gráficos clínicos">
   Gráficos clínicos
  </x-ui.action-button>
 @endif
 </x-ui.page-header>

 {{-- MENSAJE DE CONFIRMACIÓN --}}
 @if (session()->has('mensaje_exito'))
 <x-ui.callout variant="success" title="Medicación actualizada">
  {{ session('mensaje_exito') }}
 </x-ui.callout>
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
   Adulto Mayor / Residente <span class="text-[var(--rm-danger)]">*</span>
   </label>
   <div class="relative">
   <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <select wire:model="nuevo_cod_residente" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition appearance-none focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
   <option value="">-- Seleccione un residente --</option>
   @foreach($adultos as $ad)
    <option value="{{ $ad->cod_residente }}">{{ $ad->nombres }} {{ $ad->ap_paterno }} ({{ $ad->cod_residente }})</option>
   @endforeach
   </select>
   </div>
   @error('nuevo_cod_residente') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- NOMBRE DEL FÁRMACO --}}
   <div class="sm:col-span-2">
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Nombre del Medicamento / Principio Activo <span class="text-[var(--rm-danger)]">*</span>
   </label>
   <div class="relative">
   <i class="ph-bold ph-first-aid-kit absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <input type="text" list="sugerencias-farmacos" wire:model="nuevo_nombre" placeholder="Ej: Paracetamol, Losartán, Enalapril, Omeprazol..." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-[var(--rm-text-muted)] focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
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
   @error('nuevo_nombre') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- DOSIS / CONCENTRACIÓN --}}
   <div>
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Dosis / Concentración <span class="text-[var(--rm-danger)]">*</span>
   </label>
   <div class="relative">
   <i class="ph-bold ph-eyedropper absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <input type="text" list="sugerencias-dosis" wire:model="nuevo_dosis" placeholder="Ej: 500 mg, 1 tableta, 10 ml..." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-[var(--rm-text-muted)] focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
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
   @error('nuevo_dosis') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- VÍA DE ADMINISTRACIÓN --}}
   <div>
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Vía de Administración <span class="text-[var(--rm-danger)]">*</span>
   </label>
   <div class="relative">
   <i class="ph-bold ph-syringe absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
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
   @error('nuevo_via') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- FRECUENCIA / POSOLOGÍA --}}
   <div>
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Frecuencia / Pauta <span class="text-[var(--rm-danger)]">*</span>
   </label>
   <div class="relative">
   <i class="ph-bold ph-repeat absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <input type="text" list="sugerencias-frecuencia" wire:model="nuevo_frecuencia" placeholder="Ej: Cada 8 horas, Cada 12 horas..." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-[var(--rm-text-muted)] focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
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
   @error('nuevo_frecuencia') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- HORA PROGRAMADA INICIAL --}}
   <div>
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Hora Programada (Toma Inicial) <span class="text-[var(--rm-danger)]">*</span>
   </label>
   <div class="relative">
   <i class="ph-bold ph-clock absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <input type="time" wire:model="nuevo_hora" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
   </div>
   @error('nuevo_hora') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- FECHA DE INICIO --}}
   <div>
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Fecha de Inicio <span class="text-[var(--rm-danger)]">*</span>
   </label>
   <div class="relative">
   <i class="ph-bold ph-calendar-check absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <input type="date" wire:model="nuevo_fecha_inicio" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
   </div>
   @error('nuevo_fecha_inicio') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- FECHA DE FIN --}}
   <div>
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Fecha de Fin / Conclusión (Opcional)
   </label>
   <div class="relative">
   <i class="ph-bold ph-calendar-x absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <input type="date" wire:model="nuevo_fecha_fin" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
   </div>
   @error('nuevo_fecha_fin') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- MÉDICO PRESCRIPTOR --}}
   <div class="sm:col-span-2">
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Médico Tratante / Prescriptor
   </label>
   <div class="relative">
   <i class="ph-bold ph-stethoscope absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
   <input type="text" wire:model="nuevo_medico" placeholder="Ej: Dr. Roberto Mendoza" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-[var(--rm-text-muted)] focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
   </div>
   @error('nuevo_medico') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
   </div>

   {{-- OBSERVACIONES / INDICACIONES --}}
   <div class="sm:col-span-2">
   <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-body)]/70">
   Indicaciones Especiales / Observaciones Clínicas
   </label>
   <div class="relative">
   <i class="ph-bold ph-notebook absolute left-3.5 top-3 text-[var(--rm-text-muted)]"></i>
   <textarea wire:model="nuevo_observacion" rows="2" placeholder="Ej: Administrar con abundante agua después de los alimentos. Monitorear presión arterial previa." class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:text-[var(--rm-text-muted)] focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20"></textarea>
   </div>
   @error('nuevo_observacion') <span class="mt-1 block text-[10px] font-bold text-[var(--rm-danger)]">{{ $message }}</span> @enderror
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
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-success-soft)] text-[var(--rm-success)]">
  <i class="ph-bold ph-check-circle text-base"></i>
 </span>
 </div>
 <p class="mt-3 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['activas'] }}</p>
 <span class="text-[10px] font-bold text-[var(--rm-success)]">Prescripciones en curso</span>
 </div>

 <div class="group relative overflow-hidden rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-5 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-md">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Suspendidas</span>
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-danger-soft)] text-boton-acento">
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
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]">
  <i class="ph-bold ph-archive text-base"></i>
 </span>
 </div>
 <p class="mt-3 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['archivadas'] }}</p>
 <span class="text-[10px] font-bold text-[var(--rm-text-muted)]">Histórico en expediente</span>
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
  <i class="ph-bold ph-user-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
  <select wire:model.live="cod_residente" class="w-full rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] py-2.5 pl-10 pr-4 text-xs font-bold text-[var(--rm-text-body)] outline-none transition appearance-none focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/20">
  <option value="">-- Todos los Residentes --</option>
  @foreach($adultos as $ad)
  <option value="{{ $ad->cod_residente }}">{{ $ad->nombres }} {{ $ad->ap_paterno }}</option>
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
  <span class="font-black text-[var(--rm-success)]">{{ $stats['activas'] }}</span>
  </div>

  @if($puedeActuarComoMedico && auth()->user()?->can('prescripciones.crear'))
  <button type="button" wire:click="abrirFormularioPara('{{ $adulto->cod_residente }}')" class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-[var(--rm-primary)] bg-[var(--rm-primary)] px-3 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover active:scale-95">
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
   'ADMINISTRADA' => 'border-[var(--rm-success)] bg-[var(--rm-success-soft)] text-[var(--rm-success)]',
   'OMITIDA', 'VENCIDA' => 'border-[var(--rm-border)]-focus bg-[var(--rm-danger-soft)] text-boton-acento',
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
 {{-- FILTROS DE BÚSQUEDA FORMATO ALERTAS --}}
 <x-ui.filter-bar class="mb-4">
  <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
   {{-- Búsqueda textual --}}
   <div class="lg:col-span-6 relative flex items-center">
    <span class="rm-filter-search-icon absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
     <i class="ph-bold ph-magnifying-glass text-base"></i>
    </span>
    <input type="text"
     wire:model.live.debounce.300ms="search"
     placeholder="Buscar medicamento..."
     class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
    @if($search !== '')
     <button type="button"
      wire:click="$set('search', '')"
      class="rm-filter-clear absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-danger)] cursor-pointer"
      title="Limpiar búsqueda">
      <i class="ph-bold ph-x-circle text-base"></i>
     </button>
    @endif
   </div>

   {{-- Filtro Estado --}}
   <div class="lg:col-span-3">
    <select wire:model.live="filtroEstado"
     class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
     <option value="">Todos los estados</option>
     <option value="ACTIVO">Activos</option>
     <option value="SUSPENDIDO">Suspendidos</option>
     <option value="FINALIZADO">Finalizados</option>
     <option value="ARCHIVADO">Archivados (Histórico)</option>
    </select>
   </div>

   {{-- Filtro Vía --}}
   <div class="lg:col-span-3">
    <select wire:model.live="filtroVia"
     class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
     <option value="">Todas las vías</option>
     @foreach($viasDisponibles as $via)
      <option value="{{ $via }}">{{ $via }}</option>
     @endforeach
    </select>
   </div>
  </div>

  {{-- Fila de chips de filtros activos formato alertas con scroll horizontal y colorcitos --}}
  @php
   $hasFiltrosMed = !empty($search) || !empty($filtroEstado) || !empty($filtroVia);
  @endphp
  @if($hasFiltrosMed)
   <div class="rm-filter-bar__active">
    <div class="rm-filter-scroll">
     <span class="rm-filter-bar__active-label">
      <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
     </span>
     @if(!empty($search))
      <span class="rm-filter-chip rm-filter-chip--search">
       <i class="ph-bold ph-magnifying-glass text-xs"></i>
       <span>B?squeda: "{{ Str::limit($search, 16) }}"</span>
       <button type="button" wire:click="$set('search', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
      </span>
     @endif
     @if(!empty($filtroEstado))
      <span class="rm-filter-chip {{ $filtroEstado === 'ACTIVO' ? 'rm-filter-chip--success' : ($filtroEstado === 'SUSPENDIDO' ? 'rm-filter-chip--danger' : 'rm-filter-chip--warning') }}">
       <span class="w-1.5 h-1.5 rounded-full {{ $filtroEstado === 'ACTIVO' ? 'bg-[var(--rm-action-primary)]' : ($filtroEstado === 'SUSPENDIDO' ? 'bg-[var(--rm-danger)] animate-pulse' : 'bg-[var(--rm-status-high)]') }}"></span>
       <span>Estado: {{ $filtroEstado }}</span>
       <button type="button" wire:click="$set('filtroEstado', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
      </span>
     @endif
     @if(!empty($filtroVia))
      <span class="rm-filter-chip rm-filter-chip--clinical">
       <i class="ph-bold ph-pill text-xs"></i>
       <span>V?a: {{ $filtroVia }}</span>
       <button type="button" wire:click="$set('filtroVia', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
      </span>
     @endif
     <button type="button"
      wire:click="limpiarFiltros"
      class="rm-filter-bar__clear-btn">
      <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
      <span>Limpiar filtros</span>
     </button>
    </div>
   </div>
  @endif
 </x-ui.filter-bar>

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
  @if($puedeActuarComoMedico && auth()->user()?->can('prescripciones.crear'))
  <button type="button" wire:click="toggleFormularioCrear" class="inline-flex items-center gap-2 rounded-xl border border-[var(--rm-primary)] bg-[var(--rm-primary)] px-4 py-2.5 text-xs font-bold text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover active:scale-95">
  <i class="ph-bold ph-plus-circle text-base"></i>
  <span>Prescribir Primer Medicamento</span>
  </button>
  @endif
  </div>
 @else
  <div class="overflow-x-auto">
  <table class="rm-data-table rm-data-table--actions w-full text-left text-sm text-[var(--rm-text-body)]">
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
   $estado = strtoupper($med->estado ?? 'ACTIVA');
   $estadoClases = match($estado) {
   'ACTIVO', 'ACTIVA' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border-[var(--rm-success)]',
   'PAUSADO' => 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning)] border-[var(--rm-warning)]',
   'SUSPENDIDO', 'SUSPENDIDA' => 'bg-[var(--rm-danger-soft)] text-boton-acento border-[var(--rm-border)]-focus',
   'FINALIZADO', 'FINALIZADA' => 'bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)] border-[var(--rm-border)]-fuerte',
   'ARCHIVADO', 'ARCHIVADA' => 'bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)] border-[var(--rm-border)]-suave',
   default => 'bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)] border-[var(--rm-border)]/45',
   };
   $esInactivo = in_array($estado, ['FINALIZADO', 'FINALIZADA', 'ARCHIVADO', 'ARCHIVADA']);
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
   @if(in_array($estado, ['ACTIVO', 'ACTIVA'])) <i class="ph-bold ph-check-circle"></i>
   @elseif($estado === 'PAUSADO') <i class="ph-bold ph-warning"></i>
   @elseif(in_array($estado, ['SUSPENDIDO', 'SUSPENDIDA'])) <i class="ph-bold ph-pause-circle"></i>
   @elseif(in_array($estado, ['FINALIZADO', 'FINALIZADA'])) <i class="ph-bold ph-check-square-offset"></i>
   @elseif(in_array($estado, ['ARCHIVADO', 'ARCHIVADA'])) <i class="ph-bold ph-archive"></i>
   @endif
   {{ $estado }}
   </span>
   </td>
   <td class="px-5 py-4 text-right">
   <div class="flex items-center justify-end gap-1.5">
   @if(in_array($estado, ['ACTIVO', 'ACTIVA']))
    <button type="button" @click="$dispatch('abrirModalAdministracion', { cod_residente: '{{ $med->cod_residente }}', cod_prescripcion: '{{ $med->cod_prescripcion }}' })" class="inline-flex items-center gap-1.5 rounded-lg bg-[var(--rm-primary)] px-2.5 py-1.5 text-[9px] font-bold uppercase text-inverso shadow-sm transition hover:bg-[var(--rm-primary)]Hover active:scale-95" title="Registrar toma de medicación">
    <i class="ph-bold ph-check-square text-xs"></i>
    Toma
    </button>
   @endif

   @if($puedeActuarComoMedico && auth()->user()?->can('prescripciones.editar'))
    <button type="button" @click="$dispatch('abrirModalMedicacion', { cod_residente: '{{ $med->cod_residente }}', id_med: '{{ $med->cod_prescripcion }}' })" class="inline-flex items-center justify-center rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] p-1.5 text-[var(--rm-text-muted)] transition hover:bg-[var(--rm-bg-app)] hover:text-[var(--rm-text-body)] active:scale-95" title="Editar prescripción">
    <i class="ph-bold ph-pencil-simple"></i>
    </button>
   @endif

   @if(in_array($estado, ['ACTIVO', 'ACTIVA']) && $puedeActuarComoMedico && auth()->user()?->can('prescripciones.suspender'))
    <button type="button"
    wire:click="suspenderMedicamento('{{ $med->cod_prescripcion }}')"
    wire:confirm="¿Seguro que desea suspender la medicación '{{ $med->nombre_medicamento }}'?"
    class="inline-flex items-center justify-center rounded-lg border border-[var(--rm-border)]-focus bg-[var(--rm-danger-soft)] p-1.5 text-boton-acento transition hover:bg-boton-acento hover:text-inverso active:scale-95 cursor-pointer"
    title="Suspender medicamento">
    <i class="ph-bold ph-pause-circle"></i>
    </button>
    <button type="button"
    wire:click="finalizarMedicamento('{{ $med->cod_prescripcion }}')"
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
 <livewire:medicacion.medicacion-adulto-modal :cod_residente="$adulto->cod_residente" :key="'med-modal-'.$adulto->cod_residente" />
 <livewire:medicacion.administracion-medicacion-modal :cod_residente="$adulto->cod_residente" :key="'admin-modal-'.$adulto->cod_residente" />
 @else
 <livewire:medicacion.medicacion-adulto-modal cod_residente="" />
 <livewire:medicacion.administracion-medicacion-modal cod_residente="" />
 @endif

 {{-- Paneles Laterales Desplegables (Drawers) --}}
 @include('livewire.alertas.modales.drawer-graficos')
 @include('livewire.alertas.modales.drawer-ubicacion')
</div>
