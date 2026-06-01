<div class="relative mx-auto max-w-7xl space-y-5 p-3 sm:p-5 lg:p-6">
 <div class="mb-2">
 <a href="{{ route('admin.salud-seguimiento.ficha.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-fondo-panel px-4 py-2.5 text-[10px] font-bold uppercase tracking-wider text-parrafo transition-all hover:bg-fondo-panel hover:shadow-sm">
 <i class="ph-bold ph-arrow-left text-sm"></i>
 Volver a Fichas Médicas
 </a>
 </div>
 
 <div class="space-y-4">
 @php
 $patologias = [
 ['cond' => $fichaActiva?->hipertension, 'label' => 'Hipertensión', 'icon' => 'ph-heartbeat'],
 ['cond' => $fichaActiva?->diabetes, 'label' => 'Diabetes', 'icon' => 'ph-drop'],
 ['cond' => $fichaActiva?->problemas_cardiacos, 'label' => 'Prob. cardíacos', 'icon' => 'ph-heart'],
 ['cond' => $fichaActiva?->acv, 'label' => 'ACV', 'icon' => 'ph-brain'],
 ['cond' => $fichaActiva?->parkinson, 'label' => 'Parkinson', 'icon' => 'ph-person-simple-walk'],
 ['cond' => $fichaActiva?->epilepsia, 'label' => 'Epilepsia', 'icon' => 'ph-pulse'],
 ['cond' => $fichaActiva?->alzheimer_diagnosticado, 'label' => 'Alzheimer', 'icon' => 'ph-brain'],
 ['cond' => $fichaActiva?->depresion, 'label' => 'Depresión', 'icon' => 'ph-cloud-rain'],
 ['cond' => $fichaActiva?->ansiedad, 'label' => 'Ansiedad', 'icon' => 'ph-warning-circle'],
 ['cond' => $fichaActiva?->problemas_sueno, 'label' => 'Sueño', 'icon' => 'ph-moon'],
 ['cond' => $fichaActiva?->problemas_visuales, 'label' => 'Visual', 'icon' => 'ph-eye'],
 ['cond' => $fichaActiva?->problemas_auditivos, 'label' => 'Auditivo', 'icon' => 'ph-ear'],
 ['cond' => $fichaActiva?->dolor_cronico, 'label' => 'Dolor crónico', 'icon' => 'ph-first-aid'],
 ];
 $patologiasActivas = collect($patologias)->filter(fn($pat) => $pat['cond']);
 @endphp

 {{-- 1. CABECERA CLÍNICA --}}
 <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
 <div class="flex items-center gap-3">
 <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-borde/55 bg-fondo-panel text-boton-acento shadow-sm">
 <i class="ph-bold ph-stethoscope text-2xl"></i>
 </span>
 <div>
 <h2 class="text-xl font-extrabold tracking-tight text-parrafo">Ficha Médica</h2>
 <p class="mt-1 text-xs font-bold leading-relaxed text-parrafo/62">
 Registro clínico base, antecedentes, alergias, condiciones y cuidados médicos del adulto mayor
 </p>
 </div>
 </div>

 <div class="flex flex-wrap items-center gap-3">
 @if($adulto)
 @if(!$fichaActiva)
 @can('salud.ficha.crear')
 <button wire:click="openModalGeneral" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-5 py-2.5 text-[11px] font-bold uppercase tracking-wider text-inverso shadow-[0_10px_22px_rgba(226,125,96,0.24)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-plus-circle text-sm"></i> Registrar Ficha Médica
 </button>
 @endcan
 @else
 @can('salud.ficha.editar')
 <button wire:click="openModalAntecedentes" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-app active:scale-95">
 <i class="ph-bold ph-activity text-sm"></i> Agregar Antecedente
 </button>
 <button wire:click="openModalAlergias" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-app active:scale-95">
 <i class="ph-bold ph-warning-circle text-sm"></i> Agregar Alergia
 </button>
 @endcan
 @endif
 @endif
 </div>
 </div>
 </section>

 {{-- 2. SELECCIONAR ADULTO MAYOR --}}
 <section class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
 <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Seleccionar adulto mayor</label>
 <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
 <div>
 <div class="relative">
 <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
 <input type="text" wire:model.live.debounce.350ms="buscarPaciente" placeholder="Buscar por nombre o apellido..." class="w-full rounded-xl border border-borde/70 bg-fondo-card py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition placeholder:text-meta focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 </div>
 </div>
 <div>
 <div class="relative w-full">
 <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
 <select wire:model="adultoSeleccionado" class="w-full rounded-xl border border-borde/70 bg-fondo-card py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition appearance-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Seleccione un adulto mayor</option>
 @foreach($pacientesSelector as $paciente)
 @php $nombrePaciente = trim("{$paciente->nombres} {$paciente->ap_paterno} {$paciente->ap_materno}"); @endphp
 <option value="{{ $paciente->cod_am }}">{{ $nombrePaciente ?: 'Adulto mayor' }}</option>
 @endforeach
 </select>
 </div>
 </div>
 <div>
 <button wire:click="buscarPacienteAction" type="button" class="inline-flex h-full w-full items-center justify-center gap-2 rounded-xl bg-boton-principal px-6 text-[11px] font-bold uppercase tracking-wider text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-magnifying-glass"></i> Buscar
 </button>
 </div>
 </div>
 </section>

 @if($adulto)
 <div class="grid gap-4 lg:grid-cols-[1fr_2.5fr]">
 
 {{-- 3. PANEL DE ESTADO Y PACIENTE --}}
 <div class="space-y-4">
 <section class="rounded-[1.6rem] border border-estado-exitoBorde bg-estado-exitoBg p-4 shadow-sm backdrop-blur-xl relative overflow-hidden">
 <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-estado-exitoBg blur-3xl"></div>
 <div class="flex items-center gap-4">
 <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border-2 border-borde-suave bg-boton-principal shadow-sm">
 @if($adulto->foto)
 <img src="{{ Storage::url($adulto->foto) }}" alt="Foto" class="h-full w-full object-cover">
 @else
 <div class="flex h-full w-full items-center justify-center text-lg font-extrabold text-inverso">
 {{ strtoupper(substr($adulto->nombres, 0, 1)) }}
 </div>
 @endif
 </div>
 <div>
 <h3 class="text-sm font-bold text-parrafo">{{ trim("{$adulto->nombres} {$adulto->ap_paterno} {$adulto->ap_materno}") }}</h3>
 <p class="mt-0.5 text-[11px] font-bold text-apoyo">
 {{ $adulto->fecha_nac ? Carbon\Carbon::parse($adulto->fecha_nac)->age . ' años' : 'Edad N/D' }}
 </p>
 </div>
 </div>
 </section>

 <section class="rounded-[1.6rem] border {{ $fichaActiva ? 'border-estado-exitoBorde bg-fondo-panel' : 'border-borde bg-fondo-app' }} p-5 shadow-sm">
 <div class="flex items-center gap-3">
 <i class="ph-bold ph-clipboard-text text-3xl {{ $fichaActiva ? 'text-estado-exito' : 'text-parrafo' }}"></i>
 <div>
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Estado Clínico</p>
 @if($fichaActiva)
 <h4 class="text-sm font-bold text-estado-exito">Ficha registrada</h4>
 <p class="text-[10px] font-bold text-apoyo mt-1">Actualizada: {{ $fichaActiva->updated_at?->format('d/m/Y') }}</p>
 @else
 <h4 class="text-sm font-bold text-parrafo">Sin ficha médica</h4>
 <p class="text-[10px] font-bold text-apoyo mt-1">Requiere registro urgente</p>
 @endif
 </div>
 </div>
 </section>
 
 {{-- 9. ACCESOS RELACIONADOS --}}
 <section class="rounded-[1.6rem] border border-borde/45 bg-fondo-panel p-4 shadow-sm">
 <h4 class="mb-3 text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Accesos Relacionados</h4>
 <div class="flex flex-col gap-2">
 <a href="{{ route('admin.salud-seguimiento.medicacion', $adulto->cod_am) }}" class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-card px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-app">
 <i class="ph-bold ph-pills text-boton-acento"></i> Ver medicación
 </a>
 <a href="{{ route('admin.salud-seguimiento.signos', $adulto->cod_am) }}" class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-card px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-app">
 <i class="ph-bold ph-heartbeat text-boton-acento"></i> Ver signos vitales
 </a>
 </div>
 </section>
 </div>

 {{-- ZONA DERECHA: CARDS MEDICAS Y TABS --}}
 <div class="space-y-4">
 
 {{-- 4. CARDS MEDICAS PRINCIPALES --}}
 <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
 <div class="rounded-2xl border border-borde/45 bg-fondo-panel p-3 shadow-sm flex flex-col justify-between h-full">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-warning-circle text-xl {{ $fichaActiva?->alergias ? 'text-parrafo' : 'text-parrafo/30' }}"></i>
 <span class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-wider {{ $fichaActiva?->alergias ? 'bg-fondo-panel text-parrafo' : 'bg-fondo-panel text-apoyo' }}">
 {{ $fichaActiva?->alergias ? 'ATENCIÓN' : 'SIN DATOS' }}
 </span>
 </div>
 <div class="mt-2">
 <p class="text-[9px] font-bold uppercase tracking-[0.1em] text-meta">Alergias</p>
 <p class="text-xs font-bold text-parrafo truncate">{{ $fichaActiva?->alergias ? 'Registradas' : 'Ninguna' }}</p>
 </div>
 </div>
 <div class="rounded-2xl border border-borde/45 bg-fondo-panel p-3 shadow-sm flex flex-col justify-between h-full">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-heartbeat text-xl text-boton-acento"></i>
 <span class="rounded-full bg-estado-peligroBg px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-boton-acento">
 ACTIVAS
 </span>
 </div>
 <div class="mt-2">
 <p class="text-[9px] font-bold uppercase tracking-[0.1em] text-meta">Condiciones</p>
 <p class="text-xs font-bold text-parrafo truncate">{{ $patologiasActivas->count() }} registradas</p>
 </div>
 </div>
 <div class="rounded-2xl border border-borde/45 bg-fondo-panel p-3 shadow-sm flex flex-col justify-between h-full">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-activity text-xl text-estado-info"></i>
 <span class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-wider {{ ($fichaActiva?->cirugias || $fichaActiva?->hospitalizaciones) ? 'bg-fondo-panel text-estado-info' : 'bg-fondo-panel text-apoyo' }}">
 {{ ($fichaActiva?->cirugias || $fichaActiva?->hospitalizaciones) ? 'CON HISTORIAL' : 'SIN DATOS' }}
 </span>
 </div>
 <div class="mt-2">
 <p class="text-[9px] font-bold uppercase tracking-[0.1em] text-meta">Antecedentes</p>
 <p class="text-xs font-bold text-parrafo truncate">Quirúrgicos / Hosp.</p>
 </div>
 </div>
 <div class="rounded-2xl border border-borde/45 bg-fondo-panel p-3 shadow-sm flex flex-col justify-between h-full">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-fork-knife text-xl {{ $fichaActiva?->restricciones_alimentarias ? 'text-estado-advertencia' : 'text-parrafo/30' }}"></i>
 <span class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-wider {{ $fichaActiva?->restricciones_alimentarias ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-fondo-panel text-apoyo' }}">
 {{ $fichaActiva?->restricciones_alimentarias ? 'RESTRINGIDO' : 'NORMAL' }}
 </span>
 </div>
 <div class="mt-2">
 <p class="text-[9px] font-bold uppercase tracking-[0.1em] text-meta">Dieta / Cuidados</p>
 <p class="text-xs font-bold text-parrafo truncate">{{ $fichaActiva?->restricciones_alimentarias ? 'Especial' : 'Ninguno' }}</p>
 </div>
 </div>
 </div>

 @if($fichaActiva)
 {{-- 5. TABS DE FICHA MEDICA --}}
 <div x-data="{ tab: 'resumen' }" class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl min-h-[400px]">
 
 <nav class="flex flex-wrap gap-2 mb-6 border-b border-borde/35 pb-4">
 <button @click="tab = 'resumen'" :class="tab === 'resumen' ? 'bg-boton-principal text-inverso shadow-sm' : 'bg-transparent text-apoyo hover:bg-fondo-panel'" class="rounded-xl px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition">Resumen Base</button>
 <button @click="tab = 'datos'" :class="tab === 'datos' ? 'bg-boton-principal text-inverso shadow-sm' : 'bg-transparent text-apoyo hover:bg-fondo-panel'" class="rounded-xl px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition">Datos Generales</button>
 <button @click="tab = 'antecedentes'" :class="tab === 'antecedentes' ? 'bg-boton-principal text-inverso shadow-sm' : 'bg-transparent text-apoyo hover:bg-fondo-panel'" class="rounded-xl px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition">Antecedentes</button>
 <button @click="tab = 'alergias'" :class="tab === 'alergias' ? 'bg-boton-principal text-inverso shadow-sm' : 'bg-transparent text-apoyo hover:bg-fondo-panel'" class="rounded-xl px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition">Alergias</button>
 <button @click="tab = 'condiciones'" :class="tab === 'condiciones' ? 'bg-boton-principal text-inverso shadow-sm' : 'bg-transparent text-apoyo hover:bg-fondo-panel'" class="rounded-xl px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition">Condiciones</button>
 <button @click="tab = 'restricciones'" :class="tab === 'restricciones' ? 'bg-boton-principal text-inverso shadow-sm' : 'bg-transparent text-apoyo hover:bg-fondo-panel'" class="rounded-xl px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition">Cuidados & Restr.</button>
 <button @click="tab = 'observaciones'" :class="tab === 'observaciones' ? 'bg-boton-principal text-inverso shadow-sm' : 'bg-transparent text-apoyo hover:bg-fondo-panel'" class="rounded-xl px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition">Observaciones</button>
 </nav>

 {{-- A. Resumen Médico --}}
 <div x-show="tab === 'resumen'" x-transition.opacity.duration.300ms>
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-estado-exito">Resumen Médico</h3>
 @can('salud.ficha.editar')
 <button wire:click="openModalGeneral" type="button" class="text-[10px] font-bold uppercase tracking-wider text-boton-acento hover:underline">
 Editar Resumen General
 </button>
 @endcan
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider">Estado de Ficha</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $fichaActiva->estado }}</p>
 </div>
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider">Última Actualización</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $fichaActiva->updated_at?->format('d/m/Y H:i') }}</p>
 </div>
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider">Responsable del Registro</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $fichaActiva->registrador->name ?? 'Sistema' }}</p>
 </div>
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider">Patologías Activas</p>
 <p class="mt-1 text-sm font-bold text-boton-acento">{{ $patologiasActivas->count() }} patologías marcadas</p>
 </div>
 </div>
 </div>

 {{-- B. Datos Clínicos Generales --}}
 <div x-show="tab === 'datos'" x-transition.opacity.duration.300ms style="display: none;">
 <h3 class="text-sm font-bold uppercase tracking-wider text-estado-exito mb-4">Datos Clínicos Generales</h3>
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4 flex items-center gap-4">
 <i class="ph-bold ph-drop text-2xl text-parrafo"></i>
 <div>
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider">Grupo Sanguíneo</p>
 <p class="mt-0.5 text-base font-extrabold text-parrafo">{{ $adulto->grupo_sanguineo ?? 'No registrado' }}</p>
 </div>
 </div>
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4 flex items-center gap-4">
 <i class="ph-bold ph-plus-minus text-2xl text-parrafo"></i>
 <div>
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider">Factor RH</p>
 <p class="mt-0.5 text-base font-extrabold text-parrafo">{{ $adulto->factor_rh ?? 'No registrado' }}</p>
 </div>
 </div>
 <div class="sm:col-span-2 mt-2">
 <p class="text-[10px] font-bold text-apoyo italic">Nota: Otros datos antropométricos base (peso, talla) se gestionan desde los módulos correspondientes de Valoración o Signos Vitales.</p>
 </div>
 </div>
 </div>

 {{-- C. Antecedentes --}}
 <div x-show="tab === 'antecedentes'" x-transition.opacity.duration.300ms style="display: none;">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-estado-exito">Antecedentes Quirúrgicos e Hospitalarios</h3>
 @can('salud.ficha.editar')
 <button wire:click="openModalAntecedentes" type="button" class="text-[10px] font-bold uppercase tracking-wider text-boton-acento hover:underline">
 Editar Antecedentes
 </button>
 @endcan
 </div>
 <div class="space-y-4">
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider mb-2">Cirugías Previas</p>
 <p class="text-xs font-bold leading-relaxed text-parrafo whitespace-pre-wrap">{{ $fichaActiva->cirugias ?: 'Sin datos registrados.' }}</p>
 </div>
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-4">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider mb-2">Hospitalizaciones</p>
 <p class="text-xs font-bold leading-relaxed text-parrafo whitespace-pre-wrap">{{ $fichaActiva->hospitalizaciones ?: 'Sin datos registrados.' }}</p>
 </div>
 </div>
 </div>

 {{-- D. Alergias --}}
 <div x-show="tab === 'alergias'" x-transition.opacity.duration.300ms style="display: none;">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-estado-exito">Alergias Registradas</h3>
 @can('salud.ficha.editar')
 <button wire:click="openModalAlergias" type="button" class="text-[10px] font-bold uppercase tracking-wider text-boton-acento hover:underline">
 Editar Alergias
 </button>
 @endcan
 </div>
 <div class="rounded-xl border {{ $fichaActiva->alergias ? 'border-borde bg-fondo-panel' : 'border-borde/45 bg-fondo-panel' }} p-5">
 <div class="flex gap-4">
 <i class="ph-bold ph-warning-circle text-3xl {{ $fichaActiva->alergias ? 'text-parrafo' : 'text-parrafo/30' }}"></i>
 <div>
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider mb-2">Alergias Medicamentosas / Alimentarias / Otras</p>
 <p class="text-sm font-bold leading-relaxed {{ $fichaActiva->alergias ? 'text-parrafo' : 'text-parrafo' }} whitespace-pre-wrap">{{ $fichaActiva->alergias ?: 'No se han reportado alergias.' }}</p>
 </div>
 </div>
 </div>
 </div>

 {{-- E. Condiciones --}}
 <div x-show="tab === 'condiciones'" x-transition.opacity.duration.300ms style="display: none;">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-estado-exito">Condiciones Médicas</h3>
 @can('salud.ficha.editar')
 <button wire:click="openModalCondiciones" type="button" class="text-[10px] font-bold uppercase tracking-wider text-boton-acento hover:underline">
 Editar Condiciones
 </button>
 @endcan
 </div>
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-5">
 <div class="flex flex-wrap gap-2.5">
 @forelse($patologiasActivas as $pat)
 <span class="inline-flex items-center gap-1.5 rounded-xl border border-borde-focus bg-estado-peligroBg px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-boton-acento shadow-sm">
 <i class="ph-bold {{ $pat['icon'] }}"></i>
 {{ $pat['label'] }}
 </span>
 @empty
 <span class="inline-flex items-center gap-1.5 rounded-xl border border-borde/45 bg-fondo-panel px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-meta">
 <i class="ph-bold ph-check"></i>
 Ninguna condición médica registrada en este expediente.
 </span>
 @endforelse
 </div>
 <p class="mt-4 text-[9px] font-bold text-apoyo italic">El sistema no diagnostica; solo registra condiciones informadas por personal autorizado.</p>
 </div>
 </div>

 {{-- F. Restricciones --}}
 <div x-show="tab === 'restricciones'" x-transition.opacity.duration.300ms style="display: none;">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-estado-exito">Restricciones y Cuidados</h3>
 @can('salud.ficha.editar')
 <button wire:click="openModalObservaciones" type="button" class="text-[10px] font-bold uppercase tracking-wider text-boton-acento hover:underline">
 Editar Restricciones
 </button>
 @endcan
 </div>
 <div class="space-y-4">
 <div class="rounded-xl border {{ $fichaActiva->restricciones_alimentarias ? 'border-estado-advertenciaBorde bg-estado-advertenciaBg' : 'border-borde/45 bg-fondo-panel' }} p-4">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-wider mb-2">Restricciones Alimentarias / Dieta Especial</p>
 <p class="text-xs font-bold leading-relaxed {{ $fichaActiva->restricciones_alimentarias ? 'text-estado-advertencia' : 'text-parrafo' }} whitespace-pre-wrap">{{ $fichaActiva->restricciones_alimentarias ?: 'No hay restricciones registradas.' }}</p>
 </div>
 <div class="mt-2">
 <p class="text-[9px] font-bold text-apoyo italic">Nota: Más información de cuidados específicos por agregar en futuras versiones del sistema médico integral.</p>
 </div>
 </div>
 </div>

 {{-- G. Observaciones --}}
 <div x-show="tab === 'observaciones'" x-transition.opacity.duration.300ms style="display: none;">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-estado-exito">Observaciones Médicas</h3>
 @can('salud.ficha.editar')
 <button wire:click="openModalObservaciones" type="button" class="text-[10px] font-bold uppercase tracking-wider text-boton-acento hover:underline">
 Editar Observaciones
 </button>
 @endcan
 </div>
 <div class="rounded-xl border border-borde/45 bg-fondo-panel p-5 min-h-[120px]">
 <p class="text-xs font-bold leading-relaxed text-parrafo whitespace-pre-wrap">{{ $fichaActiva->observacion_medica ?: 'No hay observaciones clínicas adicionales.' }}</p>
 </div>
 </div>

 </div>
 @else
 <section class="rounded-[1.6rem] border border-dashed border-borde/75 bg-fondo-panel p-10 text-center shadow-inner mt-4">
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-borde-focus bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-file-dashed text-3xl"></i>
 </div>
 <h3 class="mt-4 text-lg font-extrabold text-parrafo">No existe ficha médica registrada.</h3>
 <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-relaxed text-apoyo">
 Regístrela para completar el seguimiento clínico base, antecedentes y patologías.
 </p>
 @can('salud.ficha.crear')
 <button wire:click="openModalGeneral" type="button" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-6 py-3 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_10px_22px_rgba(226,125,96,0.24)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-plus-circle"></i> Registrar ficha médica
 </button>
 @endcan
 </section>
 @endif
 </div>
 </div>

 {{-- 6. TABLA / HISTORIAL DE FICHAS --}}
 @if(count($historialFichas) > 0)
 <section class="rounded-[1.6rem] border border-borde bg-fondo-panel p-5 shadow-sm backdrop-blur-xl mt-4">
 <h3 class="mb-4 flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">
 <i class="ph-bold ph-clock-counter-clockwise text-boton-acento"></i>
 Historial de Fichas Médicas (Archivadas / Anuladas)
 </h3>
 <div class="overflow-x-auto">
 <table class="w-full text-left text-xs text-parrafo">
 <thead class="border-b border-borde/35 bg-fondo-panel text-[9px] font-bold uppercase tracking-wider text-apoyo">
 <tr>
 <th class="px-4 py-3">Estado</th>
 <th class="px-4 py-3">Fecha de Actualización</th>
 <th class="px-4 py-3">Responsable</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/25">
 @foreach($historialFichas as $hist)
 <tr class="hover:bg-fondo-panel transition">
 <td class="px-4 py-3">
 <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-[9px] font-bold uppercase text-apoyo">{{ $hist->estado }}</span>
 </td>
 <td class="px-4 py-3 font-bold">{{ \Carbon\Carbon::parse($hist->updated_at)->format('d/m/Y - H:i') }}</td>
 <td class="px-4 py-3">{{ $hist->registrador->name ?? 'Sistema' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </section>
 @endif

 @else
 <section class="rounded-[1.6rem] border border-dashed border-borde/65 bg-fondo-panel p-12 text-center shadow-inner">
 <i class="ph-bold ph-user-focus text-4xl text-parrafo/20 mb-3 block"></i>
 <h3 class="text-base font-extrabold text-parrafo">Seleccione un adulto mayor para visualizar su ficha médica.</h3>
 <p class="mt-1 text-xs font-bold text-apoyo">Busque y seleccione un adulto mayor en el panel superior para cargar la información clínica base.</p>
 </section>
 @endif

 {{-- ========================================== --}}
 {{-- FORMULARIOS FLOTANTES / MODALES POR SECCION --}}
 {{-- ========================================== --}}

 {{-- MODAL GENERAL (Usado para crear ficha nueva con todo, o editar lo principal) --}}
 <x-ui.modal-livewire wire:model="modalGeneral" title="{{ $fichaActiva ? 'Actualizar Resumen de Ficha' : 'Registrar Ficha Médica Completa' }}" maxWidth="2xl" closeMethod="closeModal">
 <x-slot name="icon"><i class="ph-bold ph-file-text text-boton-acento"></i></x-slot>
 <form wire:submit.prevent="save" id="formGeneral">
 <p class="mb-4 text-xs font-bold text-apoyo">Se iniciará el expediente clínico base. Posteriormente podrá usar las pestañas para actualizar secciones específicas.</p>
 
 <div class="grid gap-4 md:grid-cols-2">
 <div class="md:col-span-2">
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Observación Médica General</label>
 <textarea wire:model="observacion_medica" rows="4" class="w-full rounded-xl border border-borde bg-fondo-card p-3 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 </div>
 @if(!$fichaActiva)
 <div class="md:col-span-2 rounded-xl border border-borde-focus bg-estado-peligroBg p-3">
 <p class="text-[10px] font-bold text-boton-acento uppercase tracking-wider"><i class="ph-bold ph-info"></i> Info</p>
 <p class="text-xs font-bold text-apoyo mt-1">Al guardar, se creará el expediente. Las Alergias, Condiciones y Antecedentes los podrá añadir en sus respectivas pestañas y botones.</p>
 </div>
 @endif
 </div>
 </form>
 <x-slot name="footer">
 <button type="button" wire:click="closeModal" class="rounded-xl border border-borde/70 bg-fondo-card px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-app active:scale-95">Cancelar</button>
 <button type="submit" form="formGeneral" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_8px_16px_rgba(226,125,96,0.2)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">Guardar Ficha Médica</button>
 </x-slot>
 </x-ui.modal-livewire>

 {{-- MODAL ANTECEDENTES --}}
 <x-ui.modal-livewire wire:model="modalAntecedentes" title="Agregar / Editar Antecedentes" maxWidth="2xl" closeMethod="closeModal">
 <x-slot name="icon"><i class="ph-bold ph-activity text-estado-info"></i></x-slot>
 <form wire:submit.prevent="save" id="formAntecedentes">
 <div class="grid gap-4">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Antecedentes Quirúrgicos (Cirugías previas)</label>
 <textarea wire:model="cirugias" rows="4" placeholder="Ej. Apendicectomía en 2010..." class="w-full rounded-xl border border-borde bg-fondo-card p-3 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 </div>
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Hospitalizaciones</label>
 <textarea wire:model="hospitalizaciones" rows="4" placeholder="Detalles de hospitalizaciones anteriores..." class="w-full rounded-xl border border-borde bg-fondo-card p-3 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 </div>
 </div>
 </form>
 <x-slot name="footer">
 <button type="button" wire:click="closeModal" class="rounded-xl border border-borde/70 bg-fondo-card px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo">Cancelar</button>
 <button type="submit" form="formAntecedentes" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:bg-fondo-panel">Actualizar Antecedentes</button>
 </x-slot>
 </x-ui.modal-livewire>

 {{-- MODAL ALERGIAS --}}
 <x-ui.modal-livewire wire:model="modalAlergias" title="Agregar / Editar Alergias" maxWidth="xl" closeMethod="closeModal">
 <x-slot name="icon"><i class="ph-bold ph-warning-circle text-parrafo"></i></x-slot>
 <form wire:submit.prevent="save" id="formAlergias">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Alergias (Medicamentosas, alimentarias u otras)</label>
 <textarea wire:model="alergias" rows="5" placeholder="Especifique las alergias o deje en blanco si no hay." class="w-full rounded-xl border border-borde bg-fondo-card p-3 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 </div>
 </form>
 <x-slot name="footer">
 <button type="button" wire:click="closeModal" class="rounded-xl border border-borde/70 bg-fondo-card px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo">Cancelar</button>
 <button type="submit" form="formAlergias" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:bg-fondo-panel">Actualizar Alergias</button>
 </x-slot>
 </x-ui.modal-livewire>

 {{-- MODAL CONDICIONES --}}
 <x-ui.modal-livewire wire:model="modalCondiciones" title="Agregar / Editar Condiciones Médicas" maxWidth="2xl" closeMethod="closeModal">
 <x-slot name="icon"><i class="ph-bold ph-heartbeat text-boton-acento"></i></x-slot>
 <form wire:submit.prevent="save" id="formCondiciones">
 <p class="mb-4 text-xs font-bold text-apoyo">Marque las condiciones clínicas confirmadas en el adulto mayor.</p>
 <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
 @foreach([
 'hipertension' => 'Hipertensión',
 'diabetes' => 'Diabetes',
 'problemas_cardiacos' => 'Prob. cardíacos',
 'acv' => 'ACV',
 'alzheimer_diagnosticado' => 'Alzheimer',
 'depresion' => 'Depresión',
 'parkinson' => 'Parkinson',
 'epilepsia' => 'Epilepsia',
 'ansiedad' => 'Ansiedad',
 'problemas_sueno' => 'Problemas de sueño',
 'problemas_visuales' => 'Problemas visuales',
 'problemas_auditivos' => 'Problemas auditivos',
 'dolor_cronico' => 'Dolor crónico',
 ] as $field => $label)
 <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-borde/45 bg-fondo-panel p-2.5 transition hover:border-borde-focus hover:bg-fondo-panel">
 <span class="relative flex items-center justify-center">
 <input type="checkbox" wire:model="{{ $field }}" class="peer h-5 w-5 appearance-none rounded-lg border-2 border-borde bg-fondo-card transition checked:border-borde-focus checked:bg-boton-acento focus:outline-none">
 <i class="ph-bold ph-check pointer-events-none absolute text-sm text-inverso opacity-0 transition peer-checked:opacity-100"></i>
 </span>
 <span class="text-xs font-bold uppercase tracking-wider text-parrafo transition group-hover:text-boton-acento">{{ $label }}</span>
 </label>
 @endforeach
 </div>
 </form>
 <x-slot name="footer">
 <button type="button" wire:click="closeModal" class="rounded-xl border border-borde/70 bg-fondo-card px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo">Cancelar</button>
 <button type="submit" form="formCondiciones" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:bg-fondo-panel">Guardar Condiciones</button>
 </x-slot>
 </x-ui.modal-livewire>

 {{-- MODAL OBSERVACIONES Y RESTRICCIONES --}}
 <x-ui.modal-livewire wire:model="modalObservaciones" title="Restricciones, Dietas y Observaciones" maxWidth="2xl" closeMethod="closeModal">
 <x-slot name="icon"><i class="ph-bold ph-fork-knife text-estado-advertencia"></i></x-slot>
 <form wire:submit.prevent="save" id="formObservaciones">
 <div class="grid gap-4">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Restricciones Alimentarias / Dieta Especial</label>
 <textarea wire:model="restricciones_alimentarias" rows="3" placeholder="Ej. Dieta blanda, baja en sodio..." class="w-full rounded-xl border border-borde bg-fondo-card p-3 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 </div>
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Observaciones Generales de la Ficha</label>
 <textarea wire:model="observacion_medica" rows="4" placeholder="Notas médicas y de cuidado..." class="w-full rounded-xl border border-borde bg-fondo-card p-3 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 </div>
 </div>
 </form>
 <x-slot name="footer">
 <button type="button" wire:click="closeModal" class="rounded-xl border border-borde/70 bg-fondo-card px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo">Cancelar</button>
 <button type="submit" form="formObservaciones" class="inline-flex items-center justify-center gap-2 rounded-xl bg-estado-advertenciaBg px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:bg-fondo-panel">Guardar Datos</button>
 </x-slot>
 </x-ui.modal-livewire>
</div>
</div>
