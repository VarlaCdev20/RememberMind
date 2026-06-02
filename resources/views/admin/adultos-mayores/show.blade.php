@use('Carbon\Carbon')
@use('Illuminate\Support\Facades\Storage')

<x-sistema-layout>
 @php
 $adultoObj = is_object($adulto ?? null) ? $adulto : null;

 $idAdulto = optional($adultoObj)->cod_am;

 $nombreCompleto = trim(
 (optional($adultoObj)->nombres ?? '') . ' ' .
 (optional($adultoObj)->ap_paterno ?? '') . ' ' .
 (optional($adultoObj)->ap_materno ?? '')
 );

 $fechaNacimiento = optional($adultoObj)->fecha_nac;
 $fechaIngreso = optional($adultoObj)->fecha_ing;

 $edad = $fechaNacimiento
 ? Carbon::parse($fechaNacimiento)->age
 : null;

 $fechaNacimientoFormateada = $fechaNacimiento
 ? Carbon::parse($fechaNacimiento)->format('d/m/Y')
 : 'No registrada';

 $fechaIngresoFormateada = $fechaIngreso
 ? Carbon::parse($fechaIngreso)->format('d/m/Y')
 : 'No registrada';

 /*
 |--------------------------------------------------------------------------
 | Normalización del estado
 |--------------------------------------------------------------------------
 | Evita que salga JSON como en la captura.
 */
 $estadoFuente = optional($adultoObj)->estado_adulto ?? optional($adultoObj)->estado ?? null;

 if (is_object($estadoFuente)) {
 $estadoTexto = $estadoFuente->estado ?? 'Activo';
 } elseif (is_array($estadoFuente)) {
 $estadoTexto = $estadoFuente['estado'] ?? 'Activo';
 } else {
 $estadoTexto = $estadoFuente ?: 'Activo';
 }

 $estadoTexto = strtoupper((string) $estadoTexto);

 $fotoAdulto = optional($adultoObj)->foto ?? optional($adultoObj)->imagen ?? null;

 $fotoUrl = $fotoAdulto
 ? Storage::url($fotoAdulto)
 : null;

 $iniciales = strtoupper(
 substr(optional($adultoObj)->nombres ?? 'A', 0, 1) .
 substr(optional($adultoObj)->ap_paterno ?? '', 0, 1)
 );

 $ci = optional($adultoObj)->ci ?? 'No registrado';
 $genero = optional($adultoObj)->genero ?? 'No registrado';
 $tieneCelular = optional($adultoObj)->tiene_celular ?? true;
 $celular = optional($adultoObj)->celular ?? 'No registrado';
 $sabeUsarWhatsapp = optional($adultoObj)->sabe_usar_whatsapp ?? false;
 $telefonoFijo = optional($adultoObj)->telefono_fijo ?? 'No registrado';
 $zona = optional($adultoObj)->zona ?? 'No registrada';
 $calle = optional($adultoObj)->calle ?? 'No registrada';

 // Listas para los forelse inferiores (Datos Activos e Inactivos)
 $familiaresLista = is_iterable($familiaresActivos ?? null) ? collect($familiaresActivos) : collect();
 $familiaresInactivosLista = is_iterable($familiaresInactivos ?? null) ? collect($familiaresInactivos) : collect();

 $observacionesLista = is_iterable($observacionesActivas ?? null) ? collect($observacionesActivas) : collect();
 $observacionesAnuladasLista = is_iterable($observacionesAnuladas ?? null) ? collect($observacionesAnuladas) : collect();

 $atencionesLista = is_iterable($atencionesActivas ?? null) ? collect($atencionesActivas) : collect();
 $atencionesAnuladasLista = is_iterable($atencionesAnuladas ?? null) ? collect($atencionesAnuladas) : collect();

 $actividadesLista = is_iterable($actividadesActivas ?? null) ? collect($actividadesActivas) : collect();
 $actividadesAnuladasLista = is_iterable($actividadesAnuladas ?? null) ? collect($actividadesAnuladas) : collect();

 $documentosLista = is_iterable($documentosActivos ?? null) ? collect($documentosActivos) : collect();
 $documentosArchivadosLista = is_iterable($documentosArchivados ?? null) ? collect($documentosArchivados) : collect();

 $evaluacionesLista = is_iterable($evaluacionesActivas ?? null) ? collect($evaluacionesActivas) : collect();
 $evaluacionesAnuladasLista = is_iterable($evaluacionesAnuladas ?? null) ? collect($evaluacionesAnuladas) : collect();

 $asignacionesLista = is_iterable($asignaciones ?? null) ? collect($asignaciones) : collect();

 $fichasMedicasLista = is_iterable($fichasMedicas ?? null) ? collect($fichasMedicas) : collect();
 $medicacionesLista = is_iterable($medicaciones ?? null) ? collect($medicaciones) : collect();
 $administracionesMedicacionLista = is_iterable($administracionesMedicacion ?? null) ? collect($administracionesMedicacion) : collect();
 $signosVitalesLista = is_iterable($signosVitales ?? null) ? collect($signosVitales) : collect();
 $valoracionesFuncionalesLista = is_iterable($valoracionesFuncionales ?? null) ? collect($valoracionesFuncionales) : collect();

 $historialEstadosLista = is_iterable($historialEstados ?? null) ? collect($historialEstados) : collect();
 $bitacoraLista = is_iterable($bitacora ?? null) ? collect($bitacora) : collect();
 $eventosFiltroLista = is_iterable($eventosFiltro ?? null) ? collect($eventosFiltro) : collect();

 // Conteos para indicadores
 $totalFamiliares = count($familiaresLista);
 $totalObservaciones = count($observacionesLista);
 $totalAtenciones = count($atencionesLista);
 $totalActividades = count($actividadesLista);
 $totalDocumentos = count($documentosLista);

 $totalEvaluaciones = count($evaluacionesLista);
 $totalAsignaciones = count($asignacionesLista);

 $tabInicial = request('tab', 'identificacion');
@endphp

 <div
 x-data="{
 tab: 'resumen',
 modal: null,
 isEditing: false,
 isViewing: false,
 recordData: {},
 abrir(nombre, data = null, edit = false, view = false) { 
 this.modal = nombre;
 this.recordData = data || {};
 this.isEditing = edit;
 this.isViewing = view;
 },
 cerrar() { 
 this.modal = null;
 this.recordData = {};
 this.isEditing = false;
 this.isViewing = false;
 }
 }"
 class="relative mx-auto max-w-7xl space-y-4 px-4 py-6 sm:px-6 lg:px-8"
 >
 {{-- 1. Encabezado del expediente --}}
 @include('admin.adultos-mayores.show._cabecera-expediente')

 {{-- 2. Estado del Expediente --}}
 <div class="mb-6 rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
 <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-apoyo">Estado de completitud del expediente</h3>
 <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
 <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Identificación</span>
 <span class="text-xs font-bold text-parrafo">Completo</span>
 </div>
 <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Red de Apoyo</span>
 <span class="text-xs font-bold {{ $totalFamiliares > 0 ? 'text-parrafo' : 'text-amber-600' }}">{{ $totalFamiliares > 0 ? 'Registrada' : 'Pendiente' }}</span>
 </div>
 <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Documentos</span>
 <span class="text-xs font-bold {{ $totalDocumentos > 0 ? 'text-parrafo' : 'text-amber-600' }}">{{ $totalDocumentos > 0 ? 'Registrados' : 'Pendientes' }}</span>
 </div>
 <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Salud</span>
 <span class="text-xs font-bold {{ $fichasMedicas->isNotEmpty() ? 'text-parrafo' : 'text-amber-600' }}">{{ $fichasMedicas->isNotEmpty() ? 'Con datos' : 'Sin datos' }}</span>
 </div>
 <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Cognitivo</span>
 <span class="text-xs font-bold {{ $totalEvaluaciones > 0 ? 'text-parrafo' : 'text-apoyo' }}">{{ $totalEvaluaciones > 0 ? 'Con datos' : 'Sin datos' }}</span>
 </div>
 <div class="flex flex-col gap-1 rounded-xl bg-fondo-panel p-3 border border-borde">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Participación</span>
 <span class="text-xs font-bold {{ $totalActividades > 0 ? 'text-parrafo' : 'text-apoyo' }}">{{ $totalActividades > 0 ? 'Con datos' : 'Sin datos' }}</span>
 </div>
 </div>
 </div>

 {{-- 3 y 4. Layout 2 Columnas: Carpetas y Contenido --}}
 <div class="flex flex-col lg:flex-row gap-6">
 
 {{-- Columna Izquierda: Menú de Carpetas --}}
 <div class="w-full lg:w-[280px] shrink-0">
 <div class="rounded-[24px] border border-borde bg-fondo-card p-3 shadow-sm flex flex-col gap-1">
 <h3 class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-apoyo">Índice del Expediente</h3>
 
  @php
 $carpetas = [
 ['id' => 'resumen', 'icon' => 'ph-identification-card', 'label' => 'Resumen', 'status' => 'Completo'],
 ['id' => 'familiares', 'icon' => 'ph-users-three', 'label' => 'Familia y red de apoyo', 'status' => $totalFamiliares.' vinculados'],
 ['id' => 'seguimiento', 'icon' => 'ph-clipboard-text', 'label' => 'Seguimiento institucional', 'status' => ($totalObservaciones + $totalAtenciones).' registros'],
 ['id' => 'salud', 'icon' => 'ph-heartbeat', 'label' => 'Resumen clínico', 'status' => $fichasMedicas->isNotEmpty() ? 'Con datos' : 'Sin datos'],
 ['id' => 'evaluaciones', 'icon' => 'ph-brain', 'label' => 'Evaluaciones geriátricas', 'status' => $totalEvaluaciones > 0 ? $totalEvaluaciones.' pruebas' : 'Sin pruebas'],
 ['id' => 'historial', 'icon' => 'ph-clock-counter-clockwise', 'label' => 'Historial individual', 'status' => 'Disponible'],
 ['id' => 'preventivo', 'icon' => 'ph-shield-check', 'label' => 'Resultados preventivos', 'status' => 'Pendiente'],
 ['id' => 'documentos', 'icon' => 'ph-folder-open', 'label' => 'Documentos', 'status' => $totalDocumentos.' archivos'],
 ['id' => 'reportes', 'icon' => 'ph-file-pdf', 'label' => 'Reportes', 'status' => 'Disponibles'],
 ];
 @endphp

 @foreach($carpetas as $carpeta)
 <button type="button" @click="tab = '{{ $carpeta['id'] }}'" 
 class="flex items-center justify-between rounded-xl px-4 py-3 text-left transition"
 :class="tab === '{{ $carpeta['id'] }}' ? 'bg-fondo-panel shadow-sm border border-borde-suave text-titulo' : 'text-apoyo hover:bg-fondo-panel border border-transparent'">
 <div class="flex items-center gap-3">
 <i class="ph-bold {{ $carpeta['icon'] }} text-lg" :class="tab === '{{ $carpeta['id'] }}' ? 'text-boton-acento' : ''"></i>
 <span class="text-xs font-bold">{{ $carpeta['label'] }}</span>
 </div>
 <span class="text-[10px] font-bold text-apoyo">{{ $carpeta['status'] }}</span>
 </button>
 @endforeach
 </div>
 </div>

 {{-- Columna Derecha: Contenido de la Carpeta Activa --}}
  <div class="flex-1 min-w-0">
 <div x-show="tab === 'resumen'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._resumen')
 </div>
 
 <div x-show="tab === 'familiares'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._familiares')
 </div>

  <div x-show="tab === 'seguimiento'" x-transition style="display: none;" class="space-y-6">
 @include('admin.adultos-mayores.show._seguimiento-observaciones')
 @include('admin.adultos-mayores.show._atenciones')
 @include('admin.adultos-mayores.show._actividades')
 </div>

 <div x-show="tab === 'salud'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._salud-medica')
 </div>

 <div x-show="tab === 'evaluaciones'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._evaluaciones-cognitivas')
 </div>

 <div x-show="tab === 'historial'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._historial-individual')
 </div>

 <div x-show="tab === 'preventivo'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._resultados-preventivos')
 </div>

 <div x-show="tab === 'documentos'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._documentos')
 </div>

 <div x-show="tab === 'reportes'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._reportes')
 </div>
 </div>
 </div>
 </div>

 {{-- Modales Antiguos --}}
 @include('admin.adultos-mayores.show._modales-existentes')

 </div> {{-- fin x-data principal --}}
</x-sistema-layout>
