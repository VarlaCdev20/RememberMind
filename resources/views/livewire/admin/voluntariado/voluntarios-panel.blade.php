<div
 class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
 x-data="{
 confirmarEstado(id, estado) {
 const activo = estado === 'ACTIVO';
 const titulo = activo ? '¿Reactivar voluntario?' : '¿Desactivar voluntario?';
 const texto = activo
 ? 'El voluntario volverá a estar disponible para el flujo operativo.'
 : 'El voluntario no será eliminado. Se conservará su historial de asignaciones y asistencia.';

 if (!window.SwalAmandita) {
 if (confirm(titulo)) $wire.cambiarEstado(id, estado);
 return;
 }

 window.SwalAmandita.fire({
 title: titulo,
 text: texto,
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: activo ? 'Sí, reactivar' : 'Sí, desactivar',
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) $wire.cambiarEstado(id, estado);
 });
 },
 confirmarArchivo(id) {
 const titulo = '¿Archivar voluntario?';
 const texto = 'El voluntario no será eliminado. Su historial institucional quedará conservado.';

 if (!window.SwalAmandita) {
 if (confirm(titulo)) $wire.archivar(id);
 return;
 }

 window.SwalAmandita.fire({
 title: titulo,
 text: texto,
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: 'Sí, archivar',
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) $wire.archivar(id);
 });
 },
 confirmarGuardar() {
 const estadosSensibles = ['INACTIVO', 'SUSPENDIDO', 'RETIRADO'];

 if (!$wire.isEdit || !estadosSensibles.includes($wire.estado)) {
 $wire.guardar();
 return;
 }

 if (!window.SwalAmandita) {
 if (confirm('¿Cambiar estado del voluntario?')) $wire.guardar();
 return;
 }

 window.SwalAmandita.fire({
 title: '¿Cambiar estado del voluntario?',
 text: 'El voluntario no será eliminado. Se conservará su historial de asignaciones y asistencia.',
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: 'Sí, guardar cambios',
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) $wire.guardar();
 });
 }
 }"
>
 <div class="mx-auto max-w-7xl space-y-6">
 <section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-5 p-5 sm:p-7 lg:flex-row lg:items-end lg:justify-between">
 <div class="max-w-3xl">
 <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">
 <i class="ph-bold ph-users-three text-sm"></i>
 Voluntariado
 </span>
 <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">Voluntarios</h1>
 <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
 Registro y administración de personas voluntarias que apoyan actividades, acompañamiento y servicios institucionales.
 </p>
 </div>

 <div class="flex flex-wrap gap-2">
 @can('voluntarios.crear')
 <button
 type="button"
 wire:click="abrirCrear"
 class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_10px_24px_rgba(226,125,96,0.25)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95"
 >
 <i class="ph-bold ph-user-plus text-sm"></i>
 Registrar voluntario
 </button>
 @endcan

 <a
 href="{{ $linksCabecera['disponibilidad'] }}"
 class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wider text-titulo shadow-sm transition hover:border-borde-focus hover:text-boton-acento active:scale-95"
 >
 <i class="ph-bold ph-calendar-dots text-sm"></i>
 Ver disponibilidad
 </a>

 <a
 href="{{ $linksCabecera['resumen'] }}"
 class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wider text-titulo shadow-sm transition hover:bg-fondo-panel active:scale-95"
 >
 <i class="ph-bold ph-arrow-left text-sm"></i>
 Volver al resumen
 </a>
 </div>
 </div>
 </section>

 @php
 $tonoClases = [
 'azul' => ['icono' => 'bg-fondo-panel text-titulo', 'valor' => 'text-titulo', 'linea' => 'bg-boton-principal', 'badge' => 'bg-fondo-panel text-titulo'],
 'verde' => ['icono' => 'bg-estado-exitoBg text-estado-exito', 'valor' => 'text-estado-exito', 'linea' => 'bg-estado-exitoBg', 'badge' => 'bg-estado-exitoBg text-estado-exito'],
 'terracota' => ['icono' => 'bg-estado-peligroBg text-boton-acento', 'valor' => 'text-boton-acento', 'linea' => 'bg-boton-acento', 'badge' => 'bg-estado-peligroBg text-boton-acento'],
 'dorado' => ['icono' => 'bg-estado-advertenciaBg text-estado-advertencia', 'valor' => 'text-estado-advertencia', 'linea' => 'bg-estado-advertenciaBg', 'badge' => 'bg-estado-advertenciaBg text-estado-advertencia'],
 'neutro' => ['icono' => 'bg-fondo-panel text-meta', 'valor' => 'text-meta', 'linea' => 'bg-fondo-panel', 'badge' => 'bg-fondo-panel text-meta'],
 ];
 @endphp

 <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
 @foreach($metricas as $metrica)
 @php
 $tono = $tonoClases[$metrica['tono']] ?? $tonoClases['azul'];
 @endphp
 <article class="relative min-h-[132px] overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:border-borde-focus hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)]">
 <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
 <div class="flex items-start justify-between gap-3">
 <span class="rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $tono['badge'] }}">
 {{ $metrica['subtitulo'] }}
 </span>
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
 <i class="ph-bold {{ $metrica['icono'] }} text-xl"></i>
 </span>
 </div>
 <p class="mt-4 text-3xl font-black leading-none {{ $tono['valor'] }}">{{ number_format($metrica['valor']) }}</p>
 <p class="mt-2 text-xs font-bold uppercase leading-snug tracking-[0.12em] text-apoyo">{{ $metrica['label'] }}</p>
 </article>
 @endforeach
 </section>

 <section class="rounded-[1.5rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-4 flex items-center justify-between gap-3">
 <div>
 <span class="text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">Flujo del voluntario</span>
 <h2 class="mt-1 text-lg font-extrabold text-titulo">Punto de partida del apoyo institucional</h2>
 </div>
 <i class="ph-bold ph-flow-arrow text-2xl text-boton-acento"></i>
 </div>

 <div class="grid gap-3 md:grid-cols-5">
 @foreach([
 ['label' => 'Registrar voluntario', 'icono' => 'ph-user-plus'],
 ['label' => 'Definir disponibilidad', 'icono' => 'ph-calendar-dots'],
 ['label' => 'Crear asignación', 'icono' => 'ph-handshake'],
 ['label' => 'Registrar asistencia', 'icono' => 'ph-clipboard-text'],
 ['label' => 'Reportar participación', 'icono' => 'ph-chart-bar'],
 ] as $paso)
 <div class="relative rounded-2xl border border-borde-suave bg-fondo-panel px-4 py-4">
 <div class="flex items-center gap-3">
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-titulo">
 <i class="ph-bold {{ $paso['icono'] }} text-lg"></i>
 </span>
 <p class="text-xs font-bold leading-snug text-titulo">{{ $paso['label'] }}</p>
 </div>
 @if(! $loop->last)
 <span class="absolute -right-2 top-1/2 z-10 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full border border-borde-suave bg-fondo-app text-boton-acento md:flex">
 <i class="ph-bold ph-caret-right text-xs"></i>
 </span>
 @endif
 </div>
 @endforeach
 </div>
 </section>

 <section class="rounded-[1.5rem] border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl sm:p-5">
 <div class="grid gap-3 lg:grid-cols-[1.3fr_0.8fr_0.8fr_0.8fr_0.8fr_auto]">
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Buscar por nombre, cédula o correo</span>
 <span class="relative block">
 <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
 <input
 type="text"
 wire:model.live.debounce.300ms="search"
 placeholder="Nombre, apellido, CI..."
 class="h-11 w-full rounded-xl border border-borde-suave bg-fondo-panel pl-10 pr-4 text-xs font-bold text-titulo outline-none transition placeholder:text-apoyo focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"
 >
 </span>
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Estado</span>
 <select wire:model.live="estadoFiltro" class="h-11 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Todos</option>
 @foreach($estados as $key => $label)
 <option value="{{ $key }}">{{ $label }}</option>
 @endforeach
 <option value="ARCHIVADO">Archivado</option>
 </select>
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Área de apoyo</span>
 <select wire:model.live="areaFiltro" class="h-11 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Todas</option>
 @foreach($areas as $area)
 <option value="{{ $area }}">{{ $area }}</option>
 @endforeach
 </select>
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Tipo</span>
 <select wire:model.live="tipoFiltro" class="h-11 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Todos</option>
 <option value="VOLUNTARIADO">Voluntariado</option>
 <option value="CONVENIO">Convenio</option>
 <option value="OTRO">Otro</option>
 </select>
 </label>

 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Disponibilidad</span>
 <select wire:model.live="disponibilidadFiltro" class="h-11 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Todas</option>
 <option value="si">Registrada</option>
 <option value="no">Sin registrar</option>
 </select>
 </label>

 <div class="flex items-end">
 <button
 type="button"
 wire:click="limpiarFiltros"
 class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wider text-titulo transition hover:bg-fondo-panel active:scale-95 lg:w-auto"
 >
 <i class="ph-bold ph-broom"></i>
 Limpiar
 </button>
 </div>
 </div>
 </section>

 <section class="overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="overflow-x-auto">
 <table class="min-w-[1120px] w-full text-left">
 <thead class="border-b border-borde-suave bg-fondo-panel">
 <tr class="text-xs font-bold uppercase tracking-[0.12em] text-apoyo">
 <th class="px-4 py-3">Voluntario</th>
 <th class="px-4 py-3">Cédula</th>
 <th class="px-4 py-3">Contacto</th>
 <th class="px-4 py-3">Tipo</th>
 <th class="px-4 py-3">Área de apoyo</th>
 <th class="px-4 py-3">Estado</th>
 <th class="px-4 py-3">Disponibilidad</th>
 <th class="px-4 py-3">Asignaciones</th>
 <th class="px-4 py-3">Última asistencia</th>
 <th class="px-4 py-3 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/35">
 @forelse($voluntarios as $vol)
 @php
 $nombre = trim(($vol->nombres ?? '') . ' ' . ($vol->ap_paterno ?? '') . ' ' . ($vol->ap_materno ?? ''));
 $estadoKey = $vol->archivado_en ? 'ARCHIVADO' : strtoupper($vol->estado ?? 'INACTIVO');
 $estadoClase = match ($estadoKey) {
 'ACTIVO' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
 'SUSPENDIDO' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
 'RETIRADO', 'ARCHIVADO' => 'bg-fondo-panel text-meta border-borde-suave',
 default => 'bg-slate-100 text-slate-600 border-slate-200',
 };
 @endphp
 <tr class="bg-fondo-panel align-top transition hover:bg-fondo-panel">
 <td class="px-4 py-4">
 <button type="button" wire:click="verPerfil({{ $vol->cod_vol }})" class="group flex min-w-0 items-center gap-3 text-left">
 <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-boton-principal text-sm font-bold text-inverso shadow-sm transition group-hover:bg-boton-acento">
 {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($vol->nombres ?? 'V', 0, 1) . \Illuminate\Support\Str::substr($vol->ap_paterno ?? '', 0, 1)) }}
 </span>
 <span class="min-w-0">
 <span class="block max-w-[220px] truncate text-sm font-bold text-titulo group-hover:text-boton-acento">{{ $nombre ?: 'Voluntario sin nombre' }}</span>
 <span class="mt-0.5 block text-xs font-bold uppercase tracking-wide text-apoyo">VOL-{{ str_pad($vol->cod_vol, 4, '0', STR_PAD_LEFT) }}</span>
 </span>
 </button>
 </td>
 <td class="px-4 py-4 text-xs font-bold text-apoyo">
 {{ $vol->numero_documento ?: 'S/D' }}
 @if($vol->expedido)
 <span class="block text-xs uppercase text-apoyo">{{ $vol->expedido }}</span>
 @endif
 </td>
 <td class="px-4 py-4">
 <p class="text-xs font-bold text-titulo">{{ $vol->telefono ?: 'Sin celular' }}</p>
 <p class="mt-0.5 max-w-[180px] truncate text-xs font-semibold text-apoyo">{{ $vol->correo }}</p>
 </td>
 <td class="px-4 py-4">
 <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-titulo">
 {{ $vol->tipo_vinculacion ?: 'VOLUNTARIADO' }}
 </span>
 </td>
 <td class="px-4 py-4 text-xs font-bold text-apoyo">{{ $vol->area_apoyo ?: 'General' }}</td>
 <td class="px-4 py-4">
 <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $estadoClase }}">
 {{ $estadoKey === 'ARCHIVADO' ? 'Archivado' : \Illuminate\Support\Str::headline(\Illuminate\Support\Str::lower($estadoKey)) }}
 </span>
 </td>
 <td class="px-4 py-4">
 @if((int) $vol->disponibilidad_count > 0)
 <span class="inline-flex items-center gap-1.5 rounded-full bg-estado-exitoBg px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-estado-exito">
 <i class="ph-bold ph-check-circle"></i>
 {{ $vol->disponibilidad_count }} registrada(s)
 </span>
 @else
 <span class="inline-flex items-center gap-1.5 rounded-full bg-estado-peligroBg px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-boton-acento">
 <i class="ph-bold ph-calendar-x"></i>
 Sin registrar
 </span>
 @endif
 </td>
 <td class="px-4 py-4 text-xs font-bold text-titulo">{{ $vol->asignaciones_activas_count }}</td>
 <td class="px-4 py-4 text-xs font-bold text-apoyo">
 {{ $vol->ultima_asistencia ? \Carbon\Carbon::parse($vol->ultima_asistencia)->format('d/m/Y') : 'Sin asistencias' }}
 </td>
 <td class="px-4 py-4">
 <div class="flex justify-end gap-1.5">
 <button type="button" wire:click="verPerfil({{ $vol->cod_vol }})" class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso" title="Ver perfil">
 <i class="ph-bold ph-eye"></i>
 </button>
 @can('voluntarios.editar')
 <button type="button" wire:click="editar({{ $vol->cod_vol }})" class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia transition hover:bg-estado-advertenciaBg hover:text-inverso" title="Editar">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 @endcan
 @can('voluntarios.cambiar_estado')
 @if($estadoKey === 'ACTIVO')
 <button type="button" @click="confirmarEstado({{ $vol->cod_vol }}, 'INACTIVO')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-600 hover:text-inverso" title="Desactivar">
 <i class="ph-bold ph-power"></i>
 </button>
 @else
 <button type="button" @click="confirmarEstado({{ $vol->cod_vol }}, 'ACTIVO')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito transition hover:bg-estado-exitoBg hover:text-inverso" title="Activar">
 <i class="ph-bold ph-check-circle"></i>
 </button>
 @endif
 @if(! $vol->archivado_en)
 <button type="button" @click="confirmarArchivo({{ $vol->cod_vol }})" class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento transition hover:bg-boton-acento hover:text-inverso" title="Archivar">
 <i class="ph-bold ph-archive-box"></i>
 </button>
 @endif
 @endcan
 <a href="{{ route('admin.voluntariado.disponibilidad.index', ['VOLUNTARIO' => $vol->cod_vol]) }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-panel text-apoyo transition hover:bg-fondo-panel hover:text-titulo" title="Ver disponibilidad">
 <i class="ph-bold ph-calendar-dots"></i>
 </a>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="10" class="px-6 py-12">
 <div class="rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-8 text-center">
 <i class="ph-bold ph-users-three text-4xl text-apoyo"></i>
 <h3 class="mt-3 text-sm font-bold text-titulo">
 {{ $search || $estadoFiltro || $areaFiltro || $tipoFiltro || $disponibilidadFiltro ? 'No se encontraron voluntarios con los filtros seleccionados.' : 'No hay voluntarios registrados.' }}
 </h3>
 <p class="mt-1 text-xs font-bold text-apoyo">
 {{ $search || $estadoFiltro || $areaFiltro || $tipoFiltro || $disponibilidadFiltro ? 'Limpia los filtros o ajusta la búsqueda.' : 'Registra el primer voluntario para iniciar el flujo de disponibilidad y asignaciones.' }}
 </p>
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($voluntarios->hasPages())
 <div class="border-t border-borde-suave bg-fondo-panel px-4 py-3">
 {{ $voluntarios->links() }}
 </div>
 @endif
 </section>
 </div>

 @if($mostrarFormulario)
 <div class="fixed inset-0 z-[90] flex justify-end">
 <div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm" wire:click="cerrarFormulario"></div>
 <aside class="relative flex h-full w-full max-w-4xl flex-col overflow-hidden border-l border-borde-suave bg-fondo-app shadow-[-22px_0_60px_rgba(47,62,92,0.26)] sm:rounded-l-[2rem]">
 <header class="flex items-center justify-between gap-4 border-b border-borde-suave bg-fondo-panel px-5 py-4 sm:px-7">
 <div>
 <span class="text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">{{ $isEdit ? 'Editar registro' : 'Nuevo registro' }}</span>
 <h2 class="mt-1 text-xl font-extrabold text-titulo">{{ $isEdit ? 'Editar voluntario' : 'Registrar voluntario' }}</h2>
 </div>
 <button type="button" wire:click="cerrarFormulario" class="flex h-10 w-10 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-titulo transition hover:bg-boton-acento hover:text-inverso">
 <i class="ph-bold ph-x"></i>
 </button>
 </header>

 <div class="flex-1 overflow-y-auto p-5 sm:p-7">
 <div class="grid gap-5 lg:grid-cols-2">
 <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Datos personales</h3>
 <div class="mt-4 grid gap-4 sm:grid-cols-2">
 <label class="block sm:col-span-2">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Nombres</span>
 <input type="text" wire:model="nombres" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('nombres') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Apellido paterno</span>
 <input type="text" wire:model="ap_paterno" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('ap_paterno') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Apellido materno</span>
 <input type="text" wire:model="ap_materno" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('ap_materno') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Cédula</span>
 <input type="text" wire:model="numero_documento" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('numero_documento') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Expedido</span>
 <input type="text" wire:model="expedido" placeholder="LP, CB, SC..." class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('expedido') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Fecha de nacimiento</span>
 <input type="date" wire:model="fecha_nacimiento" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('fecha_nacimiento') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Género</span>
 <select wire:model="genero" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 <option value="">Sin especificar</option>
 <option value="FEMENINO">Femenino</option>
 <option value="MASCULINO">Masculino</option>
 <option value="OTRO">Otro</option>
 </select>
 @error('genero') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Celular</span>
 <input type="text" wire:model="telefono" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('telefono') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Correo</span>
 <input type="email" wire:model="correo" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('correo') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block sm:col-span-2">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Dirección</span>
 <input type="text" wire:model="direccion" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('direccion') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 </div>
 </section>

 <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
 <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Datos institucionales</h3>
 <div class="mt-4 grid gap-4">
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Fecha de registro</span>
 <input type="date" wire:model="fecha_ing" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('fecha_ing') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Área de apoyo</span>
 <select wire:model="area_apoyo" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @foreach($areasSugeridas as $areaSugerida)
 <option value="{{ $areaSugerida }}">{{ $areaSugerida }}</option>
 @endforeach
 </select>
 @error('area_apoyo') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Disponibilidad inicial</span>
 <input type="text" wire:model="disponibilidad_inicial" placeholder="Ej. Lunes y miércoles por la tarde" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('disponibilidad_inicial') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Estado</span>
 <select wire:model="estado" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @foreach($estados as $key => $label)
 <option value="{{ $key }}">{{ $label }}</option>
 @endforeach
 </select>
 @error('estado') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 <label class="block">
 <span class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-apoyo">Observaciones administrativas / habilidades</span>
 <textarea wire:model="observaciones" rows="7" class="w-full rounded-xl border border-borde-suave bg-fondo-card/70 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
 @error('observaciones') <p class="mt-1 text-xs font-bold text-boton-acento">{{ $message }}</p> @enderror
 </label>
 </div>
 </section>
 </div>
 </div>

 <footer class="flex flex-col-reverse gap-2 border-t border-borde-suave bg-fondo-panel px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
 <button type="button" wire:click="cerrarFormulario" class="inline-flex h-11 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wider text-titulo transition hover:bg-fondo-panel">
 Cancelar
 </button>
 <button type="button" @click="confirmarGuardar()" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-boton-principal px-5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_10px_24px_rgba(47,62,92,0.18)] transition hover:bg-boton-acento active:scale-95">
 <i class="ph-bold ph-floppy-disk"></i>
 {{ $isEdit ? 'Guardar cambios' : 'Registrar voluntario' }}
 </button>
 </footer>
 </aside>
 </div>
 @endif

 @if($mostrarPerfil && $perfil)
 @php
 $p = $perfil['VOLUNTARIO'];
 $nombrePerfil = trim(($p->nombres ?? '') . ' ' . ($p->ap_paterno ?? '') . ' ' . ($p->ap_materno ?? ''));
 $estadoPerfil = $p->archivado_en ? 'ARCHIVADO' : strtoupper($p->estado ?? 'INACTIVO');
 @endphp
 <div class="fixed inset-0 z-[80] flex justify-end">
 <div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm" wire:click="cerrarPerfil"></div>
 <aside class="relative flex h-full w-full max-w-5xl flex-col overflow-hidden border-l border-borde-suave bg-fondo-app shadow-[-22px_0_60px_rgba(47,62,92,0.26)] sm:rounded-l-[2rem]">
 <header class="flex items-start justify-between gap-4 border-b border-borde-suave bg-fondo-panel px-5 py-4 sm:px-7">
 <div class="min-w-0">
 <span class="inline-flex items-center gap-2 rounded-full bg-estado-peligroBg px-3 py-1 text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">
 <i class="ph-bold ph-user-focus"></i>
 Perfil del voluntario
 </span>
 <h2 class="mt-2 truncate text-2xl font-black text-titulo">{{ $nombrePerfil ?: 'Voluntario' }}</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">VOL-{{ str_pad($p->cod_vol, 4, '0', STR_PAD_LEFT) }} · {{ $p->area_apoyo ?: 'Apoyo institucional' }}</p>
 </div>
 <button type="button" wire:click="cerrarPerfil" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-titulo transition hover:bg-boton-acento hover:text-inverso">
 <i class="ph-bold ph-x"></i>
 </button>
 </header>

 <div class="flex-1 overflow-y-auto p-5 sm:p-7">
 <div class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
 <section class="space-y-5">
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-5">
 <div class="flex items-center justify-between gap-3">
 <div>
 <p class="text-xs font-bold uppercase tracking-[0.12em] text-apoyo">Estado operativo</p>
 <p class="mt-1 text-lg font-extrabold text-titulo">{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::lower($estadoPerfil)) }}</p>
 </div>
 <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-fondo-panel text-titulo">
 <i class="ph-bold ph-hand-heart text-2xl"></i>
 </span>
 </div>
 <div class="mt-4 grid grid-cols-3 gap-2">
 <div class="rounded-xl bg-fondo-panel p-3 text-center">
 <p class="text-xl font-extrabold text-titulo">{{ $perfil['stats']['disponibilidades'] }}</p>
 <p class="text-xs font-bold uppercase text-apoyo">Disponibilidad</p>
 </div>
 <div class="rounded-xl bg-fondo-panel p-3 text-center">
 <p class="text-xl font-extrabold text-estado-exito">{{ $perfil['stats']['asignaciones_activas'] }}</p>
 <p class="text-xs font-bold uppercase text-apoyo">Asignaciones</p>
 </div>
 <div class="rounded-xl bg-fondo-panel p-3 text-center">
 <p class="text-xl font-extrabold text-boton-acento">{{ $perfil['stats']['asistencias'] }}</p>
 <p class="text-xs font-bold uppercase text-apoyo">Asistencias</p>
 </div>
 </div>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-5">
 <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Datos principales</h3>
 <dl class="mt-4 space-y-3 text-xs">
 <div><dt class="font-black uppercase tracking-wider text-apoyo">Cédula</dt><dd class="font-bold text-titulo">{{ $p->numero_documento ?: 'S/D' }}</dd></div>
 <div><dt class="font-black uppercase tracking-wider text-apoyo">Celular</dt><dd class="font-bold text-titulo">{{ $p->telefono ?: 'Sin celular' }}</dd></div>
 <div><dt class="font-black uppercase tracking-wider text-apoyo">Correo</dt><dd class="break-all font-bold text-titulo">{{ $p->correo }}</dd></div>
 <div><dt class="font-black uppercase tracking-wider text-apoyo">Fecha de registro</dt><dd class="font-bold text-titulo">{{ $p->fecha_ing ? \Carbon\Carbon::parse($p->fecha_ing)->format('d/m/Y') : 'S/D' }}</dd></div>
 </dl>
 </div>

 <div class="grid gap-2 sm:grid-cols-3">
 <a href="{{ $perfil['links']['disponibilidad'] }}" class="rounded-xl border border-borde-suave bg-fondo-panel p-3 text-center text-xs font-bold uppercase tracking-wide text-titulo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-calendar-dots mb-1 block text-lg"></i>
 Disponibilidad
 </a>
 <a href="{{ $perfil['links']['asignaciones'] }}" class="rounded-xl border border-borde-suave bg-fondo-panel p-3 text-center text-xs font-bold uppercase tracking-wide text-titulo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-handshake mb-1 block text-lg"></i>
 Asignaciones
 </a>
 <a href="{{ $perfil['links']['asistencia'] }}" class="rounded-xl border border-borde-suave bg-fondo-panel p-3 text-center text-xs font-bold uppercase tracking-wide text-titulo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-clipboard-text mb-1 block text-lg"></i>
 Asistencia
 </a>
 </div>
 </section>

 <section class="space-y-5">
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-5">
 <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Disponibilidad resumida</h3>
 <div class="mt-4 space-y-2">
 @forelse($perfil['disponibilidades'] as $disp)
 <div class="flex items-center justify-between rounded-xl bg-fondo-panel px-3 py-2 text-xs font-bold text-titulo">
 <span>{{ $disp->dia_semana }}</span>
 <span class="text-apoyo">{{ $disp->hora_inicio ?: 'S/H' }} - {{ $disp->hora_fin ?: 'S/H' }}</span>
 </div>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave bg-fondo-panel p-5 text-center text-xs font-bold text-apoyo">
 Este voluntario aún no tiene disponibilidad registrada.
 </div>
 @endforelse
 </div>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-5">
 <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Asignaciones activas</h3>
 <div class="mt-4 space-y-2">
 @forelse($perfil['asignaciones'] as $asig)
 <div class="rounded-xl bg-fondo-panel px-3 py-3">
 <p class="text-xs font-bold text-titulo">{{ trim(($asig->nombres ?? '') . ' ' . ($asig->ap_paterno ?? '') . ' ' . ($asig->ap_materno ?? '')) ?: 'Adulto mayor asignado' }}</p>
 <p class="mt-1 text-xs font-bold text-apoyo">{{ \Carbon\Carbon::parse($asig->fecha_asig)->format('d/m/Y') }} · {{ $asig->estado ?: 'Vigente' }}</p>
 </div>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave bg-fondo-panel p-5 text-center text-xs font-bold text-apoyo">
 Este voluntario aún no tiene asignaciones.
 </div>
 @endforelse
 </div>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-5">
 <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Últimas asistencias</h3>
 <div class="mt-4 space-y-2">
 @forelse($perfil['asistencias'] as $asis)
 <div class="rounded-xl bg-fondo-panel px-3 py-3">
 <div class="flex items-center justify-between gap-2">
 <p class="text-xs font-bold text-titulo">{{ \Carbon\Carbon::parse($asis->fecha)->format('d/m/Y') }}</p>
 <span class="rounded-full bg-estado-exitoBg px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-estado-exito">{{ $asis->estado }}</span>
 </div>
 <p class="mt-1 text-xs font-bold text-apoyo">{{ $asis->actividad_realizada ?: 'Participación registrada' }}</p>
 </div>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave bg-fondo-panel p-5 text-center text-xs font-bold text-apoyo">
 Este voluntario aún no tiene asistencias registradas.
 </div>
 @endforelse
 </div>
 </div>

 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-5">
 <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Observaciones administrativas</h3>
 <p class="mt-3 whitespace-pre-line text-xs font-bold leading-relaxed text-apoyo">{{ $p->observaciones ?: 'Sin observaciones administrativas registradas.' }}</p>
 </div>
 </section>
 </div>
 </div>
 </aside>
 </div>
 @endif
</div>
