<div class="space-y-4 font-sans bg-[var(--rm-bg-app)] p-3 sm:p-5 rounded-2xl">
 {{-- ==================================================
 1. CABECERA COMPACTA CON MICRO-CONTADORES INTEGRADOS
 ================================================== --}}
 <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-2 border-b border-[var(--rm-border-soft)]/70 ">
 {{-- Título y Subtítulo --}}
 <div>
 <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-action-primary)] block">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS · ENFERMERÍA
 </span>
 <div class="flex items-center gap-2 mt-0.5">
 <h1 class="text-xl sm:text-2xl font-[800] text-[var(--rm-text-primary)] tracking-tight">
  Medicación
 </h1>
 <span class="text-[11px] font-mono text-[var(--rm-text-muted)] bg-[var(--rm-surface-soft)] px-2 py-0.5 rounded-md border border-[var(--rm-border-soft)]">
  {{ $fechaCabecera }}
 </span>
 </div>
 <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
 Administración y seguimiento del turno
 </p>
 </div>

 {{-- Micro-Contadores Integrados (Reemplazo compacto de tarjetas grandes) --}}
 <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 self-start lg:self-center">
 {{-- Total dosis --}}
 <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-[700] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)]" title="Total dosis programadas">
 <i class="ph ph-pill text-[var(--rm-action-primary)]"></i>
 <span>Total dosis</span>
 <span class="ml-0.5 px-1.5 py-0.2 rounded-full bg-[var(--rm-surface-soft)] text-[11px]">{{ count($dosisHoy) }}</span>
 </span>

 {{-- Administradas --}}
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-[600] bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-active)] border border-[var(--rm-action-primary)]/40 dark:border-[var(--rm-action-primary)]/40" title="Dosis administradas">
 <span class="w-1.5 h-1.5 rounded-full bg-[var(--rm-action-primary)]"></span>
 <span>{{ $conteoAdministradas }} administradas</span>
 </span>

 {{-- Pendientes --}}
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-[600] bg-[var(--rm-warning-soft)] dark:bg-[var(--rm-warning)]/20 text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-soft)] border border-[var(--rm-warning-soft)] dark:border-[var(--rm-warning)]/40" title="Dosis pendientes">
 <span class="w-1.5 h-1.5 rounded-full bg-[var(--rm-warning)]"></span>
 <span>{{ $conteoPendientes }} pendientes</span>
 </span>

 {{-- Con retraso --}}
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-[700] {{ $conteoRetrasadas > 0 ? 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)] border border-[var(--rm-border-soft)]' }}" title="Dosis con retraso">
 <span class="w-1.5 h-1.5 rounded-full {{ $conteoRetrasadas > 0 ? 'bg-[var(--rm-danger)] animate-pulse' : 'bg-[var(--rm-text-muted)]' }}"></span>
 <span>{{ $conteoRetrasadas }} retrasada{{ $conteoRetrasadas === 1 ? '' : 's' }}</span>
 </span>

 {{-- Omisiones --}}
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-[600] bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary-active)] border border-[var(--rm-border-soft)]" title="Omisiones justificadas">
 <span class="w-1.5 h-1.5 rounded-full bg-[var(--rm-action-primary-active)]"></span>
 <span>{{ $conteoOmitidas }} omitida{{ $conteoOmitidas === 1 ? '' : 's' }}</span>
 </span>

 {{-- Alergias (chip de seguridad sin alarma si no hay alertas activas) --}}
 @if($residentesConAlergias === 0)
 <span class="hidden sm:inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-semibold bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)] border border-[var(--rm-border-soft)]" title="Seguridad de alergias">
  <i class="ph ph-shield-check text-[var(--rm-action-primary)]"></i>
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
 class="flex items-center justify-between p-3 rounded-xl bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] border border-[var(--rm-action-primary)]/40 dark:border-[var(--rm-action-primary)]/40 text-[var(--rm-action-primary-active)] text-xs font-[600] shadow-2xs">
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
 <section class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 p-2.5 sm:px-3.5 rounded-xl bg-[var(--rm-danger-soft)] border-l-4 border-l-[var(--rm-danger)] border-y border-r border-[var(--rm-danger)]/40 text-xs shadow-2xs">
 <div class="flex items-center gap-2.5">
 <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[var(--rm-danger)] text-white">
  <i class="ph ph-warning-bold text-sm"></i>
 </span>
 <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
  @if($residentesConAlergias > 0)
  <span class="font-[700] text-[var(--rm-danger)] inline-flex items-center gap-1">
  Alergia relevante: {{ $residentesConAlergias }} registrada{{ $residentesConAlergias === 1 ? '' : 's' }}
  </span>
  @endif
  @if($conteoRetrasadas > 0)
  <span class="font-semibold text-[var(--rm-action-primary-active)] inline-flex items-center gap-1">
  · {{ $conteoRetrasadas }} retrasada{{ $conteoRetrasadas === 1 ? '' : 's' }} en este turno
  </span>
  @endif
  <span class="text-[var(--rm-text-muted)] hidden md:inline">
  · Protocolo de seguridad del paciente activo
  </span>
 </div>
 </div>

 <div class="flex items-center gap-1 text-[11px] font-semibold text-[var(--rm-action-primary-active)] self-end sm:self-center">
 <i class="ph ph-shield-check text-xs"></i>
 <span>Atención prioritaria requerida</span>
 </div>
 </section>
 @endif

 {{-- ==================================================
 3. PESTAÑAS PRINCIPALES: KARDEX | HISTORIAL
 (Con accesos rápidos de filtro para Próximas y Omisiones)
 ================================================== --}}
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[var(--rm-border-soft)] pb-0.5">
 {{-- Pestañas Mayores --}}
 <nav class="flex items-center gap-6 text-xs font-[700]">
 {{-- Tab Kardex --}}
 <button
 type="button"
 wire:click="cambiarTab('kardex')"
 class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-1.5 {{ in_array($tabActivo, ['kardex', 'proximas', 'omisiones']) ? 'border-b-2 border-[var(--rm-action-primary)] text-[var(--rm-action-primary)] ' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)]' }}">
 <i class="ph ph-calendar-check text-sm {{ in_array($tabActivo, ['kardex', 'proximas', 'omisiones']) ? 'text-[var(--rm-action-primary)]' : 'text-[var(--rm-text-muted)]' }}"></i>
 <span>Kardex</span>
 <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10.5px] {{ in_array($tabActivo, ['kardex', 'proximas', 'omisiones']) ? 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] ' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)]' }}">
  {{ count($dosisHoy) }}
 </span>
 </button>

 {{-- Tab Historial --}}
 <button
 type="button"
 wire:click="cambiarTab('historial')"
 class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-1.5 {{ $tabActivo === 'historial' ? 'border-b-2 border-[var(--rm-action-primary)] text-[var(--rm-action-primary)] ' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)]' }}">
 <i class="ph ph-clock-counter-clockwise text-sm {{ $tabActivo === 'historial' ? 'text-[var(--rm-action-primary)]' : 'text-[var(--rm-text-muted)]' }}"></i>
 <span>Historial</span>
 <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10.5px] {{ $tabActivo === 'historial' ? 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] ' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)]' }}">
  {{ count($historial) }}
 </span>
 </button>
 </nav>

 {{-- Filtros Rápidos de Kardex (Próximas dosis / Omisiones) --}}
 @if(in_array($tabActivo, ['kardex', 'proximas', 'omisiones']))
 <div class="flex items-center gap-1.5 pb-2 text-xs">
 <span class="text-[11px] text-[var(--rm-text-muted)] font-semibold mr-1 hidden sm:inline">Vista rápida:</span>

 {{-- Todas (Kardex completo) --}}
 <button
  type="button"
  wire:click="cambiarTab('kardex')"
  class="px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer {{ $tabActivo === 'kardex' ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] hover:bg-[var(--rm-surface-soft)]' }}">
  Todas
 </button>

 {{-- Próximas dosis --}}
 <button
  type="button"
  wire:click="cambiarTab('proximas')"
  class="px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer flex items-center gap-1 {{ $tabActivo === 'proximas' ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] hover:bg-[var(--rm-surface-soft)]' }}">
  <i class="ph ph-hourglass-high text-xs"></i>
  <span>Próximas dosis</span>
  @if($conteoPendientes > 0)
  <span class="ml-0.5 px-1 py-0.2 rounded-full text-[10px] {{ $tabActivo === 'proximas' ? 'bg-[var(--rm-surface-raised)]/20 text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary)]' }}">{{ $conteoPendientes }}</span>
  @endif
 </button>

 {{-- Omisiones --}}
 <button
  type="button"
  wire:click="cambiarTab('omisiones')"
  class="px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer flex items-center gap-1 {{ $tabActivo === 'omisiones' ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] hover:bg-[var(--rm-surface-soft)]' }}">
  <i class="ph ph-pause-circle text-xs"></i>
  <span>Omisiones</span>
  @if($conteoOmitidas > 0)
  <span class="ml-0.5 px-1 py-0.2 rounded-full text-[10px] {{ $tabActivo === 'omisiones' ? 'bg-[var(--rm-surface-raised)]/20 text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary-active)]' }}">{{ $conteoOmitidas }}</span>
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
