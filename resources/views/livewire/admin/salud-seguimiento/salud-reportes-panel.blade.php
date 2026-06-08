<div class="space-y-6">
 <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="p-5">
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">Documentación institucional</span>
 <h2 class="mt-1 text-xl font-extrabold tracking-tight text-parrafo">Reportes de salud</h2>
 <p class="mt-1 max-w-2xl text-xs font-bold leading-relaxed text-parrafo/62">
 Genere reportes clínico-asistenciales con selección de adulto mayor, rango de fechas y tipo de informe.
 </p>
 </div>
 </section>

 <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
 @foreach([
 ['key' => 'completo', 'tag' => 'General', 'title' => 'Censo de salud', 'text' => 'Resumen institucional de todos los expedientes.', 'icon' => 'ph-files', 'color' => 'text-parrafo', 'bg' => 'bg-fondo-panel'],
 ['key' => 'alertas', 'tag' => 'Control', 'title' => 'Alertas preventivas', 'text' => 'Pendientes críticos y preventivos del módulo.', 'icon' => 'ph-warning-circle', 'color' => 'text-boton-acento', 'bg' => 'bg-estado-peligroBg'],
 ['key' => 'medicacion', 'tag' => 'Farmacia', 'title' => 'Tratamientos', 'text' => 'Medicaciones activas e historial de control.', 'icon' => 'ph-pill', 'color' => 'text-estado-exito', 'bg' => 'bg-estado-exitoBg'],
 ['key' => 'signos', 'tag' => 'Diario', 'title' => 'Signos vitales', 'text' => 'Controles fisiológicos recientes y evolución.', 'icon' => 'ph-activity', 'color' => 'text-parrafo', 'bg' => 'bg-estado-advertenciaBg'],
 ] as $card)
 <button type="button" wire:click="$set('tipoReporte', '{{ $card['key'] }}')" class="group rounded-[1.45rem] border p-5 text-left shadow-sm backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-[0_16px_34px_rgba(47,62,92,0.12)] {{ $tipoReporte === $card['key'] ? 'border-borde-focus bg-fondo-panel' : 'border-borde bg-fondo-panel' }}">
 <div class="flex items-start justify-between gap-3">
 <span class="rounded-full px-2.5 py-1 text-[8px] font-black uppercase tracking-wider {{ $card['bg'] }} {{ $card['color'] }}">{{ $card['tag'] }}</span>
 <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $card['bg'] }} {{ $card['color'] }} transition group-hover:scale-105">
 <i class="ph-bold {{ $card['icon'] }} text-xl"></i>
 </span>
 </div>
 <h3 class="mt-4 text-base font-extrabold text-parrafo">{{ $card['title'] }}</h3>
 <p class="mt-1 text-[11px] font-bold leading-relaxed text-parrafo/58">{{ $card['text'] }}</p>
 </button>
 @endforeach
 </section>

 <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="grid lg:grid-cols-[240px_1fr]">
 <aside class="border-b border-borde/45 bg-fondo-panel p-6 lg:border-b-0 lg:border-r">
 <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-borde/55 bg-fondo-panel text-boton-acento shadow-sm">
 <i class="ph-bold ph-printer text-3xl"></i>
 </div>
 <h3 class="mt-4 text-lg font-extrabold text-parrafo">Generador</h3>
 <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">
 Configure parámetros antes de generar la vista previa o imprimir el documento.
 </p>
 <div class="mt-5 rounded-2xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-parrafo/45">Estado</p>
 <p class="mt-1 text-xs font-bold text-estado-exito">Disponible para vista previa</p>
 </div>
 </aside>

 <div class="p-5 sm:p-7">
 <form wire:submit.prevent="generarVistaPrevia" class="space-y-5">
 <div class="grid gap-4 md:grid-cols-2">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Adulto mayor</label>
 <select wire:model="adultoSeleccionado" class="w-full rounded-xl border border-borde/65 bg-fondo-panel px-3 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Seleccione un paciente...</option>
 <option value="TODOS">Todos los pacientes activos</option>
 @foreach($adultos as $adulto)
 <option value="{{ $adulto->cod_am }}">{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }} {{ $adulto->nombres }}</option>
 @endforeach
 </select>
 @error('adultoSeleccionado') <span class="mt-1 flex items-center text-[10px] font-bold text-boton-acento"><i class="ph-bold ph-warning mr-1"></i>{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Tipo de reporte</label>
 <select wire:model="tipoReporte" class="w-full rounded-xl border border-borde/65 bg-fondo-panel px-3 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Seleccione el tipo...</option>
 <option value="individual">Ficha de salud individual</option>
 <option value="medicacion">Control de tratamientos</option>
 <option value="signos">Historial de signos vitales</option>
 <option value="valoracion">Matriz de valoración funcional</option>
 <option value="alertas">Reporte de alertas críticas</option>
 <option value="completo">Censo completo de salud</option>
 </select>
 @error('tipoReporte') <span class="mt-1 flex items-center text-[10px] font-bold text-boton-acento"><i class="ph-bold ph-warning mr-1"></i>{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="grid gap-4 border-t border-borde/35 pt-5 md:grid-cols-2">
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Fecha inicio</label>
 <input type="date" wire:model="fechaInicio" class="w-full rounded-xl border border-borde/65 bg-fondo-panel px-3 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 </div>
 <div>
 <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-apoyo">Fecha fin</label>
 <input type="date" wire:model="fechaFin" class="w-full rounded-xl border border-borde/65 bg-fondo-panel px-3 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 </div>
 </div>

 <div class="flex flex-col-reverse gap-2 border-t border-borde/35 pt-5 sm:flex-row sm:justify-end">
 <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde/70 bg-fondo-panel px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-eye text-sm"></i>
 Vista previa
 </button>
 <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-download-simple text-sm"></i>
 Generar documento
 </button>
 </div>
 </form>
 </div>
 </div>
 </section>

 <section class="hidden print:block rounded-2xl border border-borde-suave bg-fondo-card p-8 shadow-sm">
 <div class="mb-8 border-b border-borde-suave pb-5 text-center">
 <h2 class="text-2xl font-black uppercase tracking-tight text-parrafo">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</h2>
 <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-boton-acento">Reporte institucional de salud</p>
 </div>
 <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-borde bg-fondo-panel py-12 text-center">
 <i class="ph-bold ph-file-pdf mb-4 text-4xl text-meta"></i>
 <h3 class="text-base font-extrabold text-parrafo">Documento en espera</h3>
 <p class="mt-1 text-xs font-bold text-apoyo">Configure los parámetros y genere la vista previa.</p>
 </div>
 </section>
</div>
