<div
 class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
 x-data="{
 confirmarNoDisponible(id) {
 const titulo = '¿Marcar como no disponible?';
 const texto = 'El registro no será eliminado. Se conservará como historial operativo.';

 if (!window.SwalAmandita) {
 if (confirm(titulo)) $wire.marcarNoDisponible(id);
 return;
 }

 window.SwalAmandita.fire({
 title: titulo,
 text: texto,
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: 'Sí, marcar',
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) $wire.marcarNoDisponible(id);
 });
 },
 confirmarReactivar(id) {
 const titulo = '¿Reactivar disponibilidad?';
 const texto = 'El horario volverá a figurar como disponible para el flujo de asignaciones.';

 if (!window.SwalAmandita) {
 if (confirm(titulo)) $wire.reactivarDisponibilidad(id);
 return;
 }

 window.SwalAmandita.fire({
 title: titulo,
 text: texto,
 icon: 'question',
 showCancelButton: true,
 confirmButtonText: 'Sí, reactivar',
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) $wire.reactivarDisponibilidad(id);
 });
 }
 }"
>
 <div class="mx-auto max-w-7xl space-y-5">
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-[0_16px_46px_rgba(47,62,92,0.12)] backdrop-blur-xl">
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-end lg:justify-between">
 <div class="max-w-3xl">
 <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">
 <i class="ph-bold ph-calendar-dots text-sm"></i>
 Voluntariado
 </span>
 <h1 class="mt-2 text-2xl font-black tracking-tight text-titulo sm:text-3xl">Disponibilidad</h1>
 <p class="mt-1 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
 Planificación de días, fechas, turnos y horarios disponibles de los voluntarios.
 </p>
 </div>

 <div class="flex flex-wrap gap-2">
 @can('voluntarios.crear')
 <button
 type="button"
 wire:click="abrirCrear"
 class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-xs font-bold uppercase tracking-wide text-inverso shadow-[0_8px_18px_rgba(226,125,96,0.22)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95"
 >
 <i class="ph-bold ph-plus-circle text-sm"></i>
 Registrar disponibilidad
 </button>
 @endcan

 <a href="{{ $linksCabecera['voluntarios'] }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wide text-titulo shadow-sm transition hover:border-borde-focus hover:text-boton-acento active:scale-95">
 <i class="ph-bold ph-users-three text-sm"></i>
 Ver voluntarios
 </a>

 <a href="{{ $linksCabecera['resumen'] }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wide text-titulo shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrow-left text-sm"></i>
 Volver al resumen
 </a>
 </div>
 </div>
 </section>

 @php
 $tonoClases = [
 'azul' => ['icono' => 'bg-fondo-panel text-titulo', 'valor' => 'text-titulo', 'linea' => 'bg-boton-principal'],
 'verde' => ['icono' => 'bg-estado-exitoBg text-estado-exito', 'valor' => 'text-estado-exito', 'linea' => 'bg-estado-exitoBg'],
 'terracota' => ['icono' => 'bg-estado-peligroBg text-boton-acento', 'valor' => 'text-boton-acento', 'linea' => 'bg-boton-acento'],
 'dorado' => ['icono' => 'bg-estado-advertenciaBg text-estado-advertencia', 'valor' => 'text-estado-advertencia', 'linea' => 'bg-estado-advertenciaBg'],
 ];
 $estadoClases = [
 'Disponible' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
 'No disponible' => 'bg-fondo-panel text-meta border-borde-suave',
 'Pendiente' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
 'Suspendido' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
 ];
 @endphp

 <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
 @foreach($metricas as $metrica)
 @php
 $tono = $tonoClases[$metrica['tono']] ?? $tonoClases['azul'];
 @endphp
 <article class="relative min-h-[104px] overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:border-borde-focus">
 <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
 <div class="flex items-start justify-between gap-2">
 <p class="text-xs font-bold uppercase leading-snug tracking-[0.12em] text-apoyo">{{ $metrica['label'] }}</p>
 <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
 <i class="ph-bold {{ $metrica['icono'] }} text-base"></i>
 </span>
 </div>
 <p class="mt-2 text-2xl font-black leading-none {{ $tono['valor'] }}">{{ number_format($metrica['valor']) }}</p>
 <p class="mt-1 truncate text-xs font-bold text-apoyo">{{ $metrica['subtitulo'] }}</p>
 </article>
 @endforeach
 </section>

 <section class="rounded-[1.35rem] border border-borde-suave bg-fondo-panel p-3.5 shadow-sm backdrop-blur-xl">
 <div class="grid gap-3 lg:grid-cols-[1.3fr_0.75fr_0.75fr_0.75fr_0.75fr_auto_auto]">
 <label class="block">
 <span class="mb-1 block text-xs font-bold uppercase tracking-widest text-apoyo">Buscar voluntario</span>
 <span class="relative block">
 <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
 <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nombre, apellido o CI..." class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel pl-10 pr-3 text-xs font-bold text-titulo outline-none transition placeholder:text-apoyo focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 </span>
 </label>

 <label class="block">
 <span class="mb-1 block text-xs font-bold uppercase tracking-widest text-apoyo">Fecha</span>
 <input type="date" wire:model.live="fechaFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 </label>

 <label class="block">
 <span class="mb-1 block text-xs font-bold uppercase tracking-widest text-apoyo">Día</span>
 <select wire:model.live="diaFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Todos</option>
 @foreach($diasSemana as $dia)
 <option value="{{ $dia }}">{{ $dia }}</option>
 @endforeach
 </select>
 </label>

 <label class="block">
 <span class="mb-1 block text-xs font-bold uppercase tracking-widest text-apoyo">Turno</span>
 <select wire:model.live="turnoFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Todos</option>
 @foreach($turnos as $turno)
 <option value="{{ $turno }}">{{ $turno }}</option>
 @endforeach
 </select>
 </label>

 <label class="block">
 <span class="mb-1 block text-xs font-bold uppercase tracking-widest text-apoyo">Estado</span>
 <select wire:model.live="estadoFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Todos</option>
 @foreach($estados as $estado)
 <option value="{{ $estado }}">{{ $estado }}</option>
 @endforeach
 </select>
 </label>

 <div class="flex items-end">
 <button type="button" wire:click="$refresh" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 text-xs font-bold uppercase tracking-wide text-inverso transition hover:bg-boton-acento active:scale-95 lg:w-auto">
 <i class="ph-bold ph-magnifying-glass"></i>
 Buscar
 </button>
 </div>

 <div class="flex items-end">
 <button type="button" wire:click="limpiarFiltros" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wide text-titulo transition hover:bg-fondo-panel active:scale-95 lg:w-auto">
 <i class="ph-bold ph-broom"></i>
 Limpiar
 </button>
 </div>
 </div>
 </section>

 <section class="grid gap-5 xl:grid-cols-[1.9fr_1fr]">
 <div class="rounded-[1.45rem] border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
 <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
 <div>
 <span class="text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">Calendario de disponibilidad</span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">{{ $rangoSemana }}</h2>
 </div>
 <div class="flex gap-1.5">
 <button type="button" wire:click="semanaAnterior" class="flex h-9 w-9 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-titulo transition hover:bg-fondo-panel" title="Semana anterior">
 <i class="ph-bold ph-caret-left"></i>
 </button>
 <button type="button" wire:click="irHoy" class="inline-flex h-9 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold uppercase tracking-wide text-titulo transition hover:bg-fondo-panel">
 Hoy
 </button>
 <button type="button" wire:click="semanaSiguiente" class="flex h-9 w-9 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-titulo transition hover:bg-fondo-panel" title="Semana siguiente">
 <i class="ph-bold ph-caret-right"></i>
 </button>
 </div>
 </div>

 <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-7">
 @foreach($calendario as $dia)
 <article class="min-h-[168px] rounded-2xl border border-borde-suave bg-fondo-panel p-3">
 <header class="mb-2 flex items-start justify-between gap-2">
 <div>
 <h3 class="text-xs font-bold uppercase tracking-wider text-titulo">{{ $dia['dia'] }}</h3>
 <p class="text-xs font-bold text-apoyo">{{ $dia['fecha']->format('d/m') }}</p>
 </div>
 <span class="rounded-full bg-fondo-panel px-2 py-0.5 text-xs font-bold text-apoyo">{{ $dia['items']->count() }}</span>
 </header>

 <div class="space-y-1.5">
 @forelse($dia['items']->take(4) as $item)
 @php
 $estadoClase = $estadoClases[$item->estado_operativo] ?? $estadoClases['Disponible'];
 @endphp
 <button type="button" wire:click="editar({{ $item->cod_hor_vol }})" class="block w-full rounded-xl border px-2.5 py-2 text-left text-xs transition hover:-translate-y-0.5 hover:shadow-sm {{ $estadoClase }}">
 <span class="block font-black leading-tight">{{ substr((string) $item->hora_inicio, 0, 5) }} - {{ substr((string) $item->hora_fin, 0, 5) }}</span>
 <span class="mt-0.5 block truncate font-bold">{{ $item->nombre_voluntario ?: 'Voluntario' }}</span>
 <span class="mt-1 inline-flex rounded-full bg-fondo-card/55 px-1.5 py-0.5 font-black uppercase tracking-wider">{{ $item->turno }}</span>
 </button>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave bg-fondo-panel p-4 text-center">
 <p class="text-xs font-bold leading-relaxed text-apoyo">Sin disponibilidad registrada</p>
 </div>
 @endforelse

 @if($dia['items']->count() > 4)
 <p class="pt-1 text-center text-xs font-bold uppercase tracking-wide text-apoyo">
 +{{ $dia['items']->count() - 4 }} horarios más
 </p>
 @endif
 </div>
 </article>
 @endforeach
 </div>
 </div>

 <aside class="rounded-[1.45rem] border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
 <div class="mb-3 flex items-center justify-between gap-3">
 <div>
 <span class="text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">Listado breve</span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">Horarios registrados</h2>
 </div>
 <i class="ph-bold ph-list-checks text-2xl text-estado-exito"></i>
 </div>

 <div class="space-y-2.5">
 @forelse($registros as $registro)
 @php
 $estadoClase = $estadoClases[$registro->estado_operativo] ?? $estadoClases['Disponible'];
 @endphp
 <article class="rounded-2xl border border-borde-suave bg-fondo-panel p-3">
 <div class="flex items-start justify-between gap-3">
 <div class="min-w-0">
 <p class="truncate text-xs font-bold text-titulo">{{ $registro->nombre_voluntario ?: 'Voluntario' }}</p>
 <p class="mt-0.5 text-xs font-bold text-apoyo">{{ $registro->dia_semana }} · {{ $registro->fecha_referencia }}</p>
 </div>
 <span class="rounded-full border px-2 py-0.5 text-xs font-bold uppercase tracking-wide {{ $estadoClase }}">{{ $registro->estado_operativo }}</span>
 </div>

 <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs font-bold">
 <span class="rounded-full bg-fondo-panel px-2 py-1 text-titulo">{{ substr((string) $registro->hora_inicio, 0, 5) }} - {{ substr((string) $registro->hora_fin, 0, 5) }}</span>
 <span class="rounded-full bg-estado-advertenciaBg px-2 py-1 text-estado-advertencia">{{ $registro->turno }}</span>
 <span class="rounded-full bg-estado-exitoBg px-2 py-1 text-estado-exito">{{ (int) $registro->asignaciones_activas_count > 0 ? 'Asignado' : 'Sin asignación' }}</span>
 </div>

 @if($registro->observaciones)
 <p class="mt-2 line-clamp-2 text-xs font-bold leading-relaxed text-apoyo">{{ str_replace('[NO DISPONIBLE]', '', $registro->observaciones) }}</p>
 @endif

 <div class="mt-3 flex justify-end gap-1.5">
 @can('voluntarios.editar')
 <button type="button" wire:click="editar({{ $registro->cod_hor_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia transition hover:bg-estado-advertenciaBg hover:text-inverso" title="Editar">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 @if($registro->estado_operativo === 'No disponible')
 <button type="button" @click="confirmarReactivar({{ $registro->cod_hor_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito transition hover:bg-estado-exitoBg hover:text-inverso" title="Reactivar">
 <i class="ph-bold ph-check-circle"></i>
 </button>
 @else
 <button type="button" @click="confirmarNoDisponible({{ $registro->cod_hor_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento transition hover:bg-boton-acento hover:text-inverso" title="Marcar no disponible">
 <i class="ph-bold ph-prohibit"></i>
 </button>
 @endif
 @endcan
 <a href="{{ route('admin.voluntariado.asignaciones.index', ['VOLUNTARIO' => $registro->cod_vol]) }}" class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso" title="Ver asignaciones">
 <i class="ph-bold ph-handshake"></i>
 </a>
 </div>
 </article>
 @empty
 <div class="rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-7 text-center">
 <i class="ph-bold ph-calendar-blank text-4xl text-apoyo"></i>
 <h3 class="mt-3 text-sm font-bold text-titulo">
 {{ $search || $diaFiltro || $turnoFiltro || $estadoFiltro ? 'No se encontraron voluntarios disponibles con los filtros seleccionados.' : 'No hay disponibilidad registrada para esta semana.' }}
 </h3>
 <p class="mt-1 text-xs font-bold text-apoyo">Registra horarios disponibles para preparar el flujo hacia asignaciones.</p>
 </div>
 @endforelse
 </div>

 @if($registros->hasPages())
 <div class="mt-4">
 {{ $registros->links() }}
 </div>
 @endif
 </aside>
 </section>
 </div>

 @if($mostrarFormulario)
 <div class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6">
 <div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm" wire:click="cerrarFormulario"></div>
 <section class="relative flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-app shadow-[0_24px_70px_rgba(47,62,92,0.28)]">
 <header class="flex items-start justify-between gap-4 border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div>
 <span class="text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">{{ $isEdit ? 'Editar horario' : 'Nuevo horario' }}</span>
 <h2 class="mt-1 text-xl font-extrabold text-titulo">{{ $isEdit ? 'Editar disponibilidad' : 'Registrar disponibilidad' }}</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Defina el día y horario disponible del voluntario.</p>
 </div>
 <button type="button" wire:click="cerrarFormulario" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-titulo transition hover:bg-boton-acento hover:text-inverso">
 <i class="ph-bold ph-x"></i>
 </button>
 </header>

 <div class="flex-1 overflow-y-auto p-5">
 <div class="grid gap-4 sm:grid-cols-2">
 <label class="block sm:col-span-2">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Voluntario</span>
 <select wire:model="cod_vol" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Seleccionar voluntario</option>
 @foreach($voluntariosActivos as $voluntario)
 <option value="{{ $voluntario->cod_vol }}">{{ $voluntario->nombre }}{{ $voluntario->numero_documento ? ' · CI ' . $voluntario->numero_documento : '' }}</option>
 @endforeach
 </select>
 @error('cod_vol') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Día</span>
 <select wire:model="dia_semana" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Seleccionar día</option>
 @foreach($diasSemana as $dia)
 <option value="{{ $dia }}">{{ $dia }}</option>
 @endforeach
 </select>
 @error('dia_semana') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Turno calculado</span>
 <div class="flex h-11 items-center rounded-xl border border-borde-suave bg-fondo-panel px-3 text-sm font-bold text-apoyo">
 {{ $hora_inicio ? ($hora_inicio < '12:00' ? 'Mañana' : ($hora_inicio < '18:00' ? 'Tarde' : 'Noche')) : 'Flexible' }}
 </div>
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Hora inicio</span>
 <input type="time" wire:model.live="hora_inicio" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('hora_inicio') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Hora fin</span>
 <input type="time" wire:model="hora_fin" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('hora_fin') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>

 <label class="block sm:col-span-2">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Observación</span>
 <textarea wire:model="observaciones" rows="4" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 @error('observaciones') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 </div>
 </div>

 <footer class="flex flex-col-reverse gap-2 border-t border-borde-suave bg-fondo-panel px-5 py-4 sm:flex-row sm:justify-end">
 <button type="button" wire:click="cerrarFormulario" class="inline-flex h-10 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wide text-titulo transition hover:bg-fondo-panel">
 Cancelar
 </button>
 <button type="button" wire:click="guardar" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-principal px-5 text-xs font-bold uppercase tracking-wide text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-boton-acento active:scale-95">
 <i class="ph-bold ph-floppy-disk"></i>
 {{ $isEdit ? 'Guardar cambios' : 'Registrar disponibilidad' }}
 </button>
 </footer>
 </section>
 </div>
 @endif
</div>
