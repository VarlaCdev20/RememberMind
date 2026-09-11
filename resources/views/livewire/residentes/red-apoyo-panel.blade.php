<div
 class="w-full max-w-full min-w-0 overflow-x-hidden min-h-[calc(100vh-8rem)] bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
 x-data="{
 hoveredNode: null,
 selectedNode: null,
 selectNode(node) {
 this.selectedNode = node;
 },
 isNodeActive(node) {
 return this.hoveredNode === node || this.selectedNode === node;
 },
 isNodeDimmed(node) {
 const activeNode = this.hoveredNode || this.selectedNode;
 if (activeNode === 'adulto') return false;
 return activeNode && !this.isNodeActive(node);
 },
 isInstitutionalActive() {
 const activeNode = this.hoveredNode || this.selectedNode;
 return activeNode && activeNode.startsWith('voluntario-');
 },
 confirmarRed(id, accion) {
 const opciones = {
 responsable: {
 metodo: 'marcarResponsable',
 title: '¿Marcar como responsable principal?',
 text: 'Esta persona quedará como contacto principal de apoyo del adulto mayor.',
 icon: 'question',
 confirmButtonText: 'Sí, marcar'
 },
 emergencia: {
 metodo: 'marcarContactoEmergencia',
 title: '¿Marcar como contacto de emergencia?',
 text: 'Se actualizará el contacto de emergencia del adulto mayor con esta persona.',
 icon: 'warning',
 confirmButtonText: 'Sí, marcar'
 },
 desactivar: {
 metodo: 'desactivarVinculo',
 title: '¿Desactivar vínculo?',
 text: 'El vínculo no será eliminado; quedará conservado en el historial institucional.',
 icon: 'warning',
 confirmButtonText: 'Sí, desactivar'
 },
 activar: {
 metodo: 'activarVinculo',
 title: '¿Reactivar vínculo?',
 text: 'La persona volverá a formar parte activa de la red de apoyo.',
 icon: 'question',
 confirmButtonText: 'Sí, reactivar'
 }
 };

 const config = opciones[accion];
 if (!config) return;

 if (!window.SwalAmandita) {
 if (confirm(config.title)) $wire[config.metodo](id);
 return;
 }

 window.SwalAmandita.fire({
 title: config.title,
 text: config.text,
 icon: config.icon,
 showCancelButton: true,
 confirmButtonText: config.confirmButtonText,
 cancelButtonText: 'Cancelar',
 customClass: { popup: 'rounded-[1.5rem]' }
 }).then((result) => {
 if (result.isConfirmed) $wire[config.metodo](id);
 });
 }
 }"
>
 @php
 $adultoNombre = $adulto ? trim(implode(' ', array_filter([$adulto->nombres, $adulto->ap_paterno, $adulto->ap_materno]))) : '';
 $adultoIniciales = $adulto ? strtoupper(mb_substr($adulto->nombres ?? 'A', 0, 1) . mb_substr($adulto->ap_paterno ?? 'M', 0, 1)) : 'AM';
 $adultoEdad = $adulto && $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->age . ' años' : 'Edad no registrada';
 $familiaPosiciones = [
 ['x' => 50, 'y' => 12],
 ['x' => 27, 'y' => 20],
 ['x' => 73, 'y' => 20],
 ['x' => 17, 'y' => 50],
 ['x' => 83, 'y' => 50],
 ['x' => 27, 'y' => 78],
 ['x' => 73, 'y' => 78],
 ['x' => 50, 'y' => 88],
 ];
 $voluntarioPosiciones = [
 ['x' => 90, 'y' => 22],
 ['x' => 92, 'y' => 40],
 ['x' => 92, 'y' => 60],
 ['x' => 89, 'y' => 78],
 ];
 @endphp

 @once
 <style>

{!! file_get_contents(resource_path('frontend/styles/modules/livewire-residentes-red-apoyo-panel.css')) !!}
</style>
 @endonce

 <div class="mx-auto w-full max-w-[1480px] min-w-0 space-y-5">
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-[0_16px_46px_rgba(47,62,92,0.12)] backdrop-blur-xl">
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-4 p-5 xl:flex-row xl:items-end xl:justify-between">
 <div class="max-w-3xl">
 <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 <i class="ph-bold ph-graph text-sm"></i>
 Familia y Social
 </span>
 <h1 class="mt-2 text-2xl font-black tracking-tight text-titulo sm:text-3xl">Red de apoyo</h1>
 <p class="mt-1 max-w-3xl text-sm font-bold leading-relaxed text-apoyo">
 Mapa interactivo de familiares, responsables, contactos de apoyo y voluntarios relacionados al adulto mayor.
 </p>
 </div>

 <div class="flex flex-wrap gap-2">
 @can('familiares.crear')
 <button
 type="button"
 wire:click="abrirVincular"
 class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-[11px] font-bold uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(226,125,96,0.22)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95"
 >
 <i class="ph-bold ph-plus-circle text-sm"></i>
 Vincular familiar
 </button>
 @endcan

 <button
 type="button"
 wire:click="actualizarRed"
 class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-[11px] font-bold uppercase tracking-wider text-titulo shadow-sm transition hover:-translate-y-0.5 hover:border-estado-exitoBorde"
 >
 <i class="ph-bold ph-arrows-clockwise text-sm"></i>
 Actualizar red
 </button>

 @if($rutas['resumen'])
 <a
 href="{{ $rutas['resumen'] }}"
 class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-[11px] font-bold uppercase tracking-wider text-apoyo transition hover:-translate-y-0.5 hover:border-borde-focus"
 >
 <i class="ph-bold ph-arrow-u-up-left text-sm"></i>
 Volver al resumen
 </a>
 @endif
 </div>
 </div>
 </section>

 <section class="grid gap-3 rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl lg:grid-cols-[1.1fr_1fr_auto] lg:items-end w-full max-w-full min-w-0">
 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-apoyo">Buscar adulto mayor</label>
 <div class="relative">
 <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
 <input
 type="search"
 wire:model.live.debounce.350ms="buscarAdulto"
 class="h-11 w-full rounded-xl border border-borde-suave bg-fondo-panel pl-10 pr-3 text-sm font-bold text-titulo outline-none transition placeholder:text-apoyo focus:border-borde-focus"
 placeholder="Buscar por nombre o apellido"
 >
 </div>
 </div>

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-apoyo">Seleccionar adulto mayor</label>
 <select
 wire:model.live="adultoSeleccionado"
 class="h-11 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus"
 >
 <option value="">Seleccione un adulto mayor</option>
 @foreach($adultos as $opcion)
 <option value="{{ $opcion['cod_am'] }}">{{ $opcion['nombre'] }} · {{ $opcion['edad'] }} · {{ $opcion['estado'] }}</option>
 @endforeach
 </select>
 </div>

 <button
 type="button"
 wire:click="limpiarSeleccion"
 class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-card/35 px-4 text-xs font-bold text-titulo transition hover:border-borde-focus"
 >
 <i class="ph-bold ph-broom"></i>
 Limpiar
 </button>
 </section>



 <div class="w-full min-w-0">
 <section class="w-full max-w-full min-w-0 overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="flex flex-col gap-3 border-b border-borde-suave px-4 py-3 xl:flex-row xl:items-center xl:justify-between">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Árbol genealógico y social</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Adulto mayor en la raíz, familiares directos y secundarios en ramas, apoyo institucional separado. Haga clic para detalles.</p>
 </div>
 <div class="flex flex-wrap gap-2">
 <span class="rounded-full bg-boton-principal px-3 py-1 text-[11px] font-bold text-inverso">Adulto mayor</span>
 <span class="rounded-full bg-estado-peligroBg px-3 py-1 text-[11px] font-bold text-parrafo">Responsable</span>
 <span class="rounded-full bg-estado-exitoBg px-3 py-1 text-[11px] font-bold text-parrafo">Familia</span>
 <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-bold text-sky-700">Apoyo institucional</span>
 </div>
 </div>

 @if(!$adulto)
 <div class="flex min-h-[360px] items-center justify-center p-6 text-center">
 <div>
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-app text-apoyo">
 <i class="ph-bold ph-tree-structure text-3xl"></i>
 </div>
 <h3 class="mt-4 text-base font-extrabold text-titulo">Seleccione un adulto mayor para visualizar su árbol de red de apoyo.</h3>
 <p class="mx-auto mt-1 max-w-md text-sm font-bold text-apoyo">El árbol jerárquico cargará familiares y voluntarios vinculados.</p>
 </div>
 </div>
 @else
 <div x-data="window.redApoyoTree()" 
 x-init="init()"
 x-ref="container"
 class="relative w-full min-h-[420px] max-h-[520px] rounded-2xl border border-slate-200/70 bg-fondo-panel shadow-sm overflow-y-auto overflow-x-hidden px-2 py-6 md:p-6 flex flex-col items-center justify-start"
 >
 <!-- Capa SVG para líneas conectadas -->
 <svg class="absolute inset-0 w-full h-full pointer-events-none z-0">
 <template x-for="(line, index) in lines" :key="index">
 <path :d="line.d" 
 fill="none" 
 :stroke="line.color" 
 :stroke-width="line.stroke" 
 stroke-linecap="round" 
 :stroke-dasharray="line.dashed ? '6,6' : 'none'" 
 class="transition-all duration-300"
 />
 </template>
 </svg>

 <!-- Capa HTML Nodos -->
 <div class="relative z-10 flex flex-col items-center w-full max-w-full pb-8">
 
 <!-- Nivel 0: Adulto Mayor -->
 <button type="button" id="nodo-adulto" 
 wire:click.stop="abrirDetalleVinculo('adulto', '{{ $adulto->cod_am }}')"
 @click="seleccionarNodo('adulto', '{{ $adulto->cod_am }}')"
 class="flex flex-col items-center group cursor-pointer transition-transform hover:scale-105 border-none bg-transparent"
 :class="{ 'opacity-100 scale-105': nodoActivo === 'adulto-{{ $adulto->cod_am }}', 'opacity-50': nodoActivo && nodoActivo !== 'adulto-{{ $adulto->cod_am }}' }"
 >
 <div class="flex h-16 w-16 items-center justify-center rounded-full bg-boton-principal border-[3px] border-borde text-inverso shadow-md">
 <span class="text-xl font-extrabold">{{ $adultoIniciales }}</span>
 </div>
 <div class="mt-2 text-center bg-fondo-card/80 px-2 py-1 rounded-lg backdrop-blur-sm">
 <p class="text-sm font-bold text-titulo">{{ Str::limit($adultoNombre, 20) }}</p>
 <span class="mt-1 inline-block rounded-full bg-fondo-panel px-2.5 py-0.5 text-[10px] font-bold text-titulo">Adulto Mayor</span>
 </div>
 </button>

 @php
 $sinFamiliares = empty($gruposFamiliares['hijos']) && empty($gruposFamiliares['hermanos']) && empty($gruposFamiliares['conyuge']) && empty($gruposFamiliares['otros']);
 @endphp

 @if($sinFamiliares)
 <div class="text-center rounded-xl bg-fondo-card/80 p-4 border border-slate-200 shadow-sm max-w-sm mt-8 mx-auto relative z-10">
 <i class="ph-bold ph-users text-2xl text-slate-400 mb-2"></i>
 <p class="text-xs font-bold text-slate-500">Este adulto mayor aún no tiene familiares o contactos vinculados en su red de apoyo.</p>
 </div>
 @else
 <!-- Nivel 1 & 2: Familiares Agrupados -->
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 w-full mt-10 max-w-full lg:max-w-6xl relative z-10 px-1 md:px-4">
 
 <!-- Grupo 1: Familia Directa (Cónyuge, Hijos -> Nietos) -->
 <div class="flex flex-col items-center justify-start gap-4 border-t-[3px] border-slate-300/60 pt-6 relative mt-4 md:mt-0 min-w-0 w-full">
 <span class="absolute -top-3 bg-fondo-panel px-3 py-0.5 text-[9px] font-bold text-slate-500 rounded-full border border-slate-200/60 shadow-sm">FAMILIA DIRECTA</span>
 
 <div class="flex flex-wrap justify-center gap-3 max-w-full w-full">
 @foreach(array_merge($gruposFamiliares['conyuge'], $gruposFamiliares['hijos']) as $fam)
 @include('livewire.residentes.partials._nodo-familiar', ['fam' => $fam])
 @endforeach
 @if(empty($gruposFamiliares['conyuge']) && empty($gruposFamiliares['hijos']))
 <p class="text-[9px] font-bold text-slate-400 bg-fondo-card/60 px-3 py-1 rounded-full border border-slate-100">Sin familia directa</p>
 @endif
 </div>

 @if(!empty($gruposFamiliares['nietos']))
 <div class="w-full flex flex-col items-center mt-3 pt-4 border-t border-dashed border-slate-200/80 relative">
 <span class="text-[8px] font-black text-slate-400 mb-3 absolute -top-2 bg-fondo-panel px-2 rounded-full">NIETOS/AS</span>
 <div class="flex flex-wrap justify-center gap-3 max-w-full w-full">
 @foreach($gruposFamiliares['nietos'] as $fam)
 @include('livewire.residentes.partials._nodo-familiar', ['fam' => $fam])
 @endforeach
 </div>
 </div>
 @endif
 </div>

 <!-- Grupo 2: Hermanos -> Sobrinos -->
 <div class="flex flex-col items-center justify-start gap-4 border-t-[3px] border-slate-300/60 pt-6 relative mt-6 md:mt-0 min-w-0 w-full">
 <span class="absolute -top-3 bg-fondo-panel px-3 py-0.5 text-[9px] font-bold text-slate-500 rounded-full border border-slate-200/60 shadow-sm">HERMANOS/AS</span>
 
 <div class="flex flex-wrap justify-center gap-3 max-w-full w-full">
 @foreach($gruposFamiliares['hermanos'] as $fam)
 @include('livewire.residentes.partials._nodo-familiar', ['fam' => $fam])
 @endforeach
 @if(empty($gruposFamiliares['hermanos']))
 <p class="text-[9px] font-bold text-slate-400 bg-fondo-card/60 px-3 py-1 rounded-full border border-slate-100">Sin hermanos/as</p>
 @endif
 </div>

 @if(!empty($gruposFamiliares['sobrinos']))
 <div class="w-full flex flex-col items-center mt-3 pt-4 border-t border-dashed border-slate-200/80 relative">
 <span class="text-[8px] font-black text-slate-400 mb-3 absolute -top-2 bg-fondo-panel px-2 rounded-full">SOBRINOS/AS</span>
 <div class="flex flex-wrap justify-center gap-3 max-w-full w-full">
 @foreach($gruposFamiliares['sobrinos'] as $fam)
 @include('livewire.residentes.partials._nodo-familiar', ['fam' => $fam])
 @endforeach
 </div>
 </div>
 @endif
 </div>

 <!-- Grupo 3: Otros Contactos -->
 <div class="flex flex-col items-center justify-start gap-4 border-t-[3px] border-slate-300/60 pt-6 relative mt-6 md:mt-0 min-w-0 w-full">
 <span class="absolute -top-3 bg-fondo-panel px-3 py-0.5 text-[9px] font-bold text-slate-500 rounded-full border border-slate-200/60 shadow-sm">OTROS / CONTACTOS</span>
 
 <div class="flex flex-wrap justify-center gap-3 max-w-full w-full">
 @foreach($gruposFamiliares['otros'] as $fam)
 @include('livewire.residentes.partials._nodo-familiar', ['fam' => $fam])
 @endforeach
 @if(empty($gruposFamiliares['otros']))
 <p class="text-[9px] font-bold text-slate-400 bg-fondo-card/60 px-3 py-1 rounded-full border border-slate-100">Sin otros contactos</p>
 @endif
 </div>
 </div>

 </div>
 @endif

 <!-- Nivel 2: Rama Institucional -->
 @if($voluntariosMapa->isNotEmpty() || $familiares->isNotEmpty())
 <div class="flex flex-col items-center mt-6 w-full">
 <div id="nodo-inst" class="flex flex-col items-center bg-fondo-panel border-2 border-borde rounded-xl px-4 py-1.5 shadow-sm z-10 backdrop-blur-sm">
 <div class="flex items-center gap-1.5">
 <i class="ph-bold ph-hand-heart text-parrafo text-base"></i>
 <p class="text-[11px] font-bold text-parrafo">Apoyo Institucional</p>
 </div>
 </div>

 @if($voluntariosMapa->isNotEmpty())
 <div class="flex flex-row flex-wrap justify-center items-start gap-4 md:gap-8 mt-10 w-full px-2">
 @foreach($voluntariosMapa as $vol)
 <div class="nodo-voluntario flex flex-col items-center group cursor-pointer transition-transform hover:scale-105"
 @click="seleccionarNodo('VOLUNTARIO', '{{ $vol['cod_vol'] }}'); $wire.verDetalle('VOLUNTARIO', '{{ $vol['cod_vol'] }}')"
 :class="{ 'opacity-100 scale-105': nodoActivo === 'voluntario-{{ $vol['cod_vol'] }}', 'opacity-50': nodoActivo && nodoActivo !== 'voluntario-{{ $vol['cod_vol'] }}' }"
 >
 <div class="flex h-10 w-10 items-center justify-center rounded-full bg-fondo-panel border-2 border-borde text-parrafo shadow-sm">
 <span class="text-sm font-bold">{{ $vol['iniciales'] }}</span>
 </div>
 <div class="mt-1.5 text-center bg-fondo-card/80 px-2 py-1 rounded-lg backdrop-blur-sm max-w-[100px]">
 <p class="text-[11px] font-bold text-slate-700 leading-tight">{{ Str::limit($vol['nombre'], 15) }}</p>
 <span class="mt-1 inline-block rounded-full bg-fondo-panel px-1.5 py-0.5 text-[8.5px] font-bold text-parrafo">Voluntariado</span>
 </div>
 </div>
 @endforeach
 </div>
 @else
 <div class="mt-8 rounded-full bg-slate-100/80 border border-slate-200 px-3 py-1">
 <p class="text-[10px] font-bold text-slate-400">Sin voluntarios vinculados</p>
 </div>
 @endif
 </div>
 @endif

 </div>
 </div>
 @endif
 </section>

 </section>
 </div>

 <section class="w-full max-w-full min-w-0 overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
 <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Vista administrativa de vínculos</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Consulta, filtros y acciones seguras sobre la red seleccionada.</p>
 </div>

 <div class="grid gap-2 sm:grid-cols-3 w-full max-w-full min-w-0">
 <div class="relative">
 <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
 <input type="search" wire:model.live.debounce.300ms="buscarPersona" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel pl-9 pr-3 text-xs font-bold outline-none focus:border-borde-focus" placeholder="Buscar persona">
 </div>
 <select wire:model.live="filtroTipo" class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
 <option value="">Todos los vínculos</option>
 <option value='FAMILIAR'>Familiares</option>
 <option value="responsable">Responsables</option>
 <option value="emergencia">Emergencia</option>
 <option value='VOLUNTARIO'>Voluntarios</option>
 <option value="incompleto">Incompletos</option>
 </select>
 <select wire:model.live="filtroEstado" class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
 <option value="">Todos los estados</option>
 <option value="ACTIVO">Activos</option>
 <option value="INACTIVO">Inactivos</option>
 </select>
 </div>
 </div>

 @if(!$adulto)
 <div class="rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-8 text-center">
 <i class="ph-bold ph-list-magnifying-glass text-3xl text-apoyo"></i>
 <p class="mt-2 text-sm font-bold text-titulo">Seleccione un adulto mayor para consultar vínculos.</p>
 </div>
 @else
 <div class="w-full overflow-x-auto rounded-2xl border border-borde-suave">
 <table class="min-w-[1060px] w-full text-left text-sm table-fixed">
 <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
 <tr>
 <th class="px-4 py-3">Persona vinculada</th>
 <th class="px-4 py-3">Tipo</th>
 <th class="px-4 py-3">Parentesco / rol</th>
 <th class="px-4 py-3">Celular</th>
 <th class="px-4 py-3">Responsable</th>
 <th class="px-4 py-3">Emergencia</th>
 <th class="px-4 py-3">Estado</th>
 <th class="px-4 py-3">Última actualización</th>
 <th class="px-4 py-3 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/45 bg-fondo-card/25">
 @forelse($personasListado as $persona)
 <?php $personaNodeKey = $persona['tipo'] === 'FAMILIAR' ? 'familiar-' . $persona['cod_fam'] : 'voluntario-' . $persona['cod_vol']; ?>
 <tr class="transition hover:bg-fondo-card/45">
 <td class="px-4 py-3">
 <div class="flex items-center gap-3">
 <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $persona['tipo'] === 'VOLUNTARIO' ? 'bg-sky-100 text-sky-700' : 'bg-estado-exitoBg text-parrafo' }} text-xs font-bold">
 {{ $persona['iniciales'] }}
 </span>
 <div class="min-w-0">
 <p class="truncate text-sm font-bold text-titulo">{{ $persona['nombre'] }}</p>
 <p class="truncate text-[11px] font-bold text-apoyo">{{ $persona['correo'] }}</p>
 </div>
 </div>
 </td>
 <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $persona['tipo'] === 'VOLUNTARIO' ? 'Voluntario' : 'Familiar' }}</td>
 <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $persona['parentesco'] }}</td>
 <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $persona['celular'] }}</td>
 <td class="px-4 py-3">
 <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $persona['responsable'] ? 'bg-estado-peligroBg text-parrafo' : 'bg-fondo-app text-apoyo' }}">{{ $persona['responsable'] ? 'Sí' : 'No' }}</span>
 </td>
 <td class="px-4 py-3">
 <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $persona['emergencia'] ? 'bg-amber-100 text-amber-700' : 'bg-fondo-app text-apoyo' }}">{{ $persona['emergencia'] ? 'Sí' : 'No' }}</span>
 </td>
 <td class="px-4 py-3">
 <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $persona['estado'] === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $persona['estado_badge'] }}</span>
 </td>
 <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $persona['actualizado'] }}</td>
 <td class="px-4 py-3">
 <div class="flex justify-end gap-1.5">
 <button type="button" wire:click.stop="abrirDetalleVinculo('{{ $persona['tipo'] }}', {{ $persona['tipo'] === 'FAMILIAR' ? $persona['cod_fam'] : $persona['cod_vol'] }})" @click="selectNode('{{ $personaNodeKey }}')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso" title="Ver detalle">
 <i class="ph-bold ph-eye"></i>
 </button>

 @if($persona['tipo'] === 'FAMILIAR')
 @can('familiares.editar')
 <button type="button" wire:click="editarVinculo({{ $persona['vinculo_id'] }})" class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso" title="Editar vínculo">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 @if(!$persona['responsable'])
 <button type="button" @click="confirmarRed({{ $persona['vinculo_id'] }}, 'responsable')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-estado-peligroBg text-parrafo transition hover:bg-boton-acento hover:text-inverso" title="Marcar responsable">
 <i class="ph-bold ph-user-focus"></i>
 </button>
 @endif
 @if(!$persona['emergencia'])
 <button type="button" @click="confirmarRed({{ $persona['vinculo_id'] }}, 'emergencia')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700 transition hover:bg-amber-500 hover:text-inverso" title="Marcar emergencia">
 <i class="ph-bold ph-phone-call"></i>
 </button>
 @endif
 @endcan

 @if($persona['estado'] === 'ACTIVO')
 @can('familiares.anular')
 <button type="button" @click="confirmarRed({{ $persona['vinculo_id'] }}, 'desactivar')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700 transition hover:bg-rose-600 hover:text-inverso" title="Desactivar vínculo">
 <i class="ph-bold ph-user-minus"></i>
 </button>
 @endcan
 @else
 @can('familiares.editar')
 <button type="button" @click="confirmarRed({{ $persona['vinculo_id'] }}, 'activar')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 transition hover:bg-emerald-600 hover:text-inverso" title="Reactivar vínculo">
 <i class="ph-bold ph-user-plus"></i>
 </button>
 @endcan
 @endif
 @endif
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="9" class="px-4 py-10 text-center">
 <i class="ph-bold ph-users-three text-3xl text-apoyo"></i>
 <p class="mt-2 text-sm font-bold text-titulo">No se encontraron vínculos con los filtros seleccionados.</p>
 <p class="mt-1 text-xs font-bold text-apoyo">Ajuste los filtros o vincule una persona a la red de apoyo.</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 @endif
 </section>
 </div>

 @if($mostrarFormulario)
 <div class="fixed inset-0 z-[70] flex items-center justify-center bg-fondo-panel p-4 backdrop-blur-sm" wire:key="red-apoyo-modal">
 <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-app shadow-2xl">
 <div class="flex items-start justify-between gap-3 border-b border-borde-suave p-5">
 <div>
 <h2 class="text-lg font-extrabold text-titulo">{{ $modoEdicion ? 'Editar vínculo' : 'Vincular familiar o contacto de apoyo' }}</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Gestione parentesco, contacto, responsable y estado del vínculo.</p>
 </div>
 <button type="button" wire:click="cerrarFormulario" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-app text-titulo transition hover:bg-boton-acento hover:text-inverso">
 <i class="ph-bold ph-x"></i>
 </button>
 </div>

 <form wire:submit.prevent="guardarVinculo" class="overflow-y-auto p-5">
 @if($adulto)
 <div class="mb-4 rounded-2xl border border-borde-suave bg-fondo-card/35 p-3">
 <p class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Adulto mayor seleccionado</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $adultoNombre }}</p>
 </div>
 @endif

 <div class="grid gap-4 md:grid-cols-2">
 @if(!$modoEdicion)
 <div class="md:col-span-2">
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Familiar registrado existente</label>
 <select wire:model.live="form.cod_fam" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
 <option value="">Crear nuevo familiar/contacto</option>
 @foreach($familiaresDisponibles as $familiarDisponible)
 <option value="{{ $familiarDisponible['cod_fam'] }}">{{ $familiarDisponible['nombre'] }} · {{ $familiarDisponible['parentesco'] }}</option>
 @endforeach
 </select>
 @error('form.cod_fam') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>
 @endif

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Nombres</label>
 <input wire:model="form.nombres" type="text" class="w-full rounded-xl border {{ $errors->has('form.nombres') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus" placeholder="Ej. María Elena">
 @error('form.nombres') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Apellido paterno</label>
 <input wire:model="form.ap_paterno" type="text" class="w-full rounded-xl border {{ $errors->has('form.ap_paterno') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus" placeholder="Ej. Pérez">
 @error('form.ap_paterno') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Apellido materno</label>
 <input wire:model="form.ap_materno" type="text" class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus" placeholder="Opcional">
 @error('form.ap_materno') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Parentesco / rol</label>
 <select wire:model="form.parentesco_vinculo" class="w-full rounded-xl border {{ $errors->has('form.parentesco_vinculo') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
 <option value="">Seleccione parentesco</option>
 <option value="Hijo/a">Hijo/a</option>
 <option value="Cónyuge">Cónyuge</option>
 <option value="Hermano/a">Hermano/a</option>
 <option value="Nieto/a">Nieto/a</option>
 <option value="Sobrino/a">Sobrino/a</option>
 <option value="Tutor legal">Tutor legal</option>
 <option value="Contacto de apoyo">Contacto de apoyo</option>
 <option value="Otro">Otro</option>
 </select>
 @error('form.parentesco_vinculo') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Celular</label>
 <input wire:model="form.telefono" type="text" class="w-full rounded-xl border {{ $errors->has('form.telefono') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus" placeholder="Ej. 70000000">
 @error('form.telefono') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Correo</label>
 <input wire:model="form.correo" type="email" class="w-full rounded-xl border {{ $errors->has('form.correo') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus" placeholder="Opcional">
 @error('form.correo') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div class="md:col-span-2">
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Dirección</label>
 <input wire:model="form.direccion" type="text" class="w-full rounded-xl border {{ $errors->has('form.direccion') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus" placeholder="Zona, calle o referencia">
 @error('form.direccion') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div>
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Estado del vínculo</label>
 <select wire:model="form.estado" class="w-full rounded-xl border {{ $errors->has('form.estado') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
 <option value="ACTIVO">Activo</option>
 <option value="INACTIVO">Inactivo</option>
 </select>
 @error('form.estado') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>

 <div class="grid gap-2">
 <label class="flex items-center justify-between rounded-xl border border-borde-suave bg-fondo-card/35 px-4 py-3 text-sm font-bold text-titulo">
 Responsable principal
 <input wire:model="form.es_responsable" type="checkbox" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-[#E27D60]">
 </label>
 <label class="flex items-center justify-between rounded-xl border border-borde-suave bg-fondo-card/35 px-4 py-3 text-sm font-bold text-titulo">
 Contacto de emergencia
 <input wire:model="form.es_contacto_emergencia" type="checkbox" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-[#E27D60]">
 </label>
 </div>

 <div class="md:col-span-2">
 <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Observación administrativa</label>
 <textarea wire:model="form.observaciones" rows="3" class="w-full rounded-xl border {{ $errors->has('form.observaciones') ? 'border-rose-500 bg-rose-50' : 'border-borde-suave bg-fondo-panel' }} px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus" placeholder="Restricciones de visita, referencia familiar, horarios de contacto u observaciones relevantes."></textarea>
 @error('form.observaciones') <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="mt-5 flex flex-col-reverse gap-2 border-t border-borde-suave pt-4 sm:flex-row sm:justify-end">
 <button type="button" wire:click="cerrarFormulario" class="inline-flex h-10 items-center justify-center rounded-xl border border-borde-suave bg-fondo-card/35 px-4 text-xs font-bold text-titulo transition hover:bg-fondo-card/60">
 Cancelar
 </button>
 <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-5 text-xs font-bold text-inverso shadow-[0_8px_18px_rgba(226,125,96,0.22)] transition hover:bg-fondo-panel">
 <i class="ph-bold ph-floppy-disk"></i>
 {{ $modoEdicion ? 'Guardar cambios' : 'Vincular persona' }}
 </button>
 </div>
 </form>
 </div>
 </div>
 @endif

 <!-- MODAL DE CONSULTA DE DETALLE DEL VÍNCULO -->
 @if($modalDetalleVinculo && $detalleVinculo)
 <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
 <div class="w-full max-w-3xl max-h-[85vh] overflow-hidden rounded-2xl bg-fondo-panel shadow-2xl flex flex-col border border-slate-200/60" @click.away="$wire.cerrarDetalleVinculo()">
 <div class="flex items-center justify-between border-b border-slate-200/70 bg-fondo-card px-5 py-4">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-identification-card text-xl text-titulo"></i>
 <h2 class="text-base font-extrabold text-titulo">Detalle de Persona</h2>
 </div>
 <button type="button" wire:click="cerrarDetalleVinculo" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition focus:outline-none">
 <i class="ph-bold ph-x text-lg"></i>
 </button>
 </div>
 
 <div class="overflow-y-auto p-5 sm:p-6 bg-fondo-panel">
 <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
 <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[1.25rem] shadow-sm text-2xl font-black 
 {{ $detalleVinculo['tipo'] === 'VOLUNTARIO' ? 'bg-sky-100 text-sky-700 border-2 border-sky-200' : 
 ($detalleVinculo['tipo'] === 'adulto' ? 'bg-boton-principal text-inverso border-2 border-borde' : 
 'bg-estado-peligroBg text-parrafo border-2 border-borde-focus') }}">
 {{ strtoupper(mb_substr($detalleVinculo['nombre_completo'], 0, 1)) }}
 </div>
 
 <div class="flex-1 text-center sm:text-left min-w-0">
 <h3 class="text-lg font-extrabold text-titulo truncate max-w-full">{{ $detalleVinculo['nombre_completo'] }}</h3>
 <div class="mt-1 flex flex-wrap items-center justify-center sm:justify-start gap-2">
 <span class="rounded-full bg-fondo-card border border-slate-200 px-2.5 py-0.5 text-[11px] font-bold text-slate-600 shadow-sm">
 {{ $detalleVinculo['parentesco'] ?? ($detalleVinculo['tipo'] === 'adulto' ? 'Adulto Mayor' : 'Voluntariado') }}
 </span>
 @if($detalleVinculo['tipo'] === 'adulto' && isset($detalleVinculo['edad']))
 <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600">
 {{ $detalleVinculo['edad'] }}
 </span>
 @endif
 <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ ($detalleVinculo['estado'] ?? '') === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
 {{ $detalleVinculo['estado_badge'] ?? ($detalleVinculo['estado'] ?? 'ACTIVO') }}
 </span>
 </div>
 </div>
 </div>

 <div class="mt-6 space-y-4">
 @if($detalleVinculo['tipo'] === 'adulto')
 <div class="rounded-xl border border-slate-200/70 bg-fondo-card p-4 shadow-sm">
 <h4 class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-2">Sección 3: Rol dentro de la red</h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Responsable Principal</p>
 <p class="mt-0.5 font-black text-titulo">{{ $metricas['responsable'] ?? 'No definido' }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Contacto de Emergencia</p>
 <p class="mt-0.5 font-black text-titulo">{{ ($metricas['emergencias'] ?? 0) > 0 ? 'Sí' : 'No definido' }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Familiares Vinculados</p>
 <p class="mt-0.5 font-black text-titulo">{{ $metricas['familiares'] ?? 0 }} personas</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Voluntarios Relacionados</p>
 <p class="mt-0.5 font-black text-titulo">{{ $metricas['voluntarios'] ?? 0 }} personas</p>
 </div>
 </div>
 </div>
 @else
 <!-- Sección 2: Contacto -->
 <div class="rounded-xl border border-slate-200/70 bg-fondo-card p-4 shadow-sm">
 <h4 class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-2">Sección 2: Contacto</h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-6 text-sm">
 <div class="col-span-1 sm:col-span-2 lg:col-span-3">
 <p class="text-[10px] font-bold uppercase text-slate-400">Dirección</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['direccion'] ?? 'No registrada' }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Celular</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['celular'] ?? 'No registrado' }}</p>
 </div>
 <div class="min-w-0 sm:col-span-2">
 <p class="text-[10px] font-bold uppercase text-slate-400">Correo Electrónico</p>
 <p class="mt-0.5 font-black text-titulo truncate max-w-full" title="{{ $detalleVinculo['correo'] ?? 'No registrado' }}">{{ $detalleVinculo['correo'] ?? 'No registrado' }}</p>
 </div>
 </div>
 </div>

 <!-- Sección 3: Rol dentro de la red -->
 <div class="rounded-xl border border-slate-200/70 bg-fondo-card p-4 shadow-sm">
 <h4 class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-2">Sección 3: Rol dentro de la red</h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
 @if($detalleVinculo['tipo'] === 'VOLUNTARIO')
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Asignaciones / Horas</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['asignaciones'] ?? 0 }} turnos</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Última Participación</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['ultima_participacion'] ?? 'Sin registro' }}</p>
 </div>
 @else
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Responsable Principal</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['responsable_principal'] ? 'Sí' : 'No' }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Contacto de Emergencia</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['contacto_emergencia'] ? 'Sí' : 'No' }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Prioridad de contacto</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['responsable_principal'] ? 'Alta (Responsable)' : ($detalleVinculo['contacto_emergencia'] ? 'Media (Emergencia)' : 'Baja (Regular)') }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Autorizado / Informativo</p>
 <p class="mt-0.5 font-black text-titulo">Sí, perfil activo</p>
 </div>
 @endif
 </div>
 </div>

 <!-- Sección 4: Relación con el adulto mayor -->
 <div class="rounded-xl border border-slate-200/70 bg-fondo-card p-4 shadow-sm">
 <h4 class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-2">Sección 4: Relación con el adulto mayor</h4>
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
 <div class="col-span-1 sm:col-span-2">
 <p class="text-[10px] font-bold uppercase text-slate-400">Adulto mayor vinculado</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['adulto_mayor'] }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Fecha de registro / actualización</p>
 <p class="mt-0.5 font-black text-titulo">{{ $detalleVinculo['ultima_actualizacion'] }}</p>
 </div>
 <div>
 <p class="text-[10px] font-bold uppercase text-slate-400">Registrado por</p>
 <p class="mt-0.5 font-black text-titulo">Administración institucional</p>
 </div>
 <div class="col-span-1 sm:col-span-2 mt-2">
 <p class="text-[10px] font-bold uppercase text-slate-400">Observación del vínculo</p>
 <p class="mt-1 rounded-xl bg-slate-50 border border-slate-100 p-3 text-sm font-bold leading-relaxed text-titulo">
 {{ $detalleVinculo['observacion'] }}
 </p>
 </div>
 </div>
 </div>
 @endif

 <!-- Sección 5: Estado institucional -->
 <div class="rounded-xl border border-slate-200/70 bg-fondo-panel p-4 shadow-sm text-center">
 <h4 class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Sección 5: Estado institucional</h4>
 @if(($detalleVinculo['estado'] ?? '') === 'ACTIVO')
 @if(($detalleVinculo['responsable_principal'] ?? false) || ($detalleVinculo['tipo'] === 'adulto' && ($metricas['responsable'] ?? 'No') === 'Sí'))
 <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Contacto principal definido</span>
 @elseif(($detalleVinculo['celular'] ?? 'No registrado') === 'No registrado')
 <span class="inline-block rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Sin teléfono registrado</span>
 @elseif($detalleVinculo['incompleto'] ?? false)
 <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Información incompleta</span>
 @else
 <span class="inline-block rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">Perfil en orden</span>
 @endif
 @else
 <span class="inline-block rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-600">Revisar datos de contacto (Inactivo)</span>
 @endif
 </div>
 </div>
 </div>
 
 <div class="bg-fondo-card border-t border-slate-200/70 p-4 flex justify-between gap-3 items-center">
 <div>
 @if($detalleVinculo['tipo'] === 'FAMILIAR' && auth()->user()->can('familiares.editar'))
 <button type="button" wire:click="editarVinculo({{ $detalleVinculo['vinculo_id'] }})" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-100 border border-slate-200 px-4 text-xs font-bold text-slate-700 transition hover:bg-slate-200">
 <i class="ph-bold ph-pencil-simple text-sm"></i>
 Editar vínculo
 </button>
 @endif
 </div>
 <button type="button" wire:click="cerrarDetalleVinculo" class="inline-flex h-10 items-center justify-center rounded-xl bg-boton-principal px-6 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel">
 Cerrar consulta
 </button>
 </div>
 </div>
 </div>
 @endif
</div>
