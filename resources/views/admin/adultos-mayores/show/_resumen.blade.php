{{-- TAB RESUMEN --}}
@php
 use Carbon\Carbon;
 
 // 1. Cálculo de Antigüedad Institucional
 $fechaIngresoCarbon = optional($adultoObj)->fecha_ing;
 $antiguedadTexto = 'No disponible';
 if ($fechaIngresoCarbon) {
 $ingreso = Carbon::parse($fechaIngresoCarbon);
 $ahora = Carbon::now();
 $anios = $ingreso->diffInYears($ahora);
 $meses = $ingreso->diffInMonths($ahora) % 12;
 if ($anios > 0) {
 $antiguedadTexto = $anios . ($anios === 1 ? ' año' : ' años') . ($meses > 0 ? ' y ' . $meses . ($meses === 1 ? ' mes' : ' meses') : '');
 } else {
 $antiguedadTexto = $meses . ($meses === 1 ? ' mes' : ' meses');
 if ($meses === 0) {
 $dias = $ingreso->diffInDays($ahora);
 $antiguedadTexto = $dias . ($dias === 1 ? ' día' : ' días');
 }
 }
 }

 // 2. Extracción de Familiar / Contacto Principal Responsable
 $familiarPrincipal = null;
 foreach (($familiaresLista ?? collect()) as $fam) {
 $esResp = optional($fam)->es_responsable ?? optional(optional($fam)->pivot)->es_responsable ?? false;
 if ($esResp) {
 $familiarPrincipal = $fam;
 break;
 }
 }
 if (!$familiarPrincipal && ($familiaresLista ?? collect())->isNotEmpty()) {
 $familiarPrincipal = ($familiaresLista ?? collect())->first();
 }

 $nombrePrincipal = $familiarPrincipal ? trim(
 (optional($familiarPrincipal)->nombres ?? optional($familiarPrincipal)->nombre ?? '') . ' ' .
 (optional($familiarPrincipal)->ap_paterno ?? '') . ' ' .
 (optional($familiarPrincipal)->ap_materno ?? '')
 ) : null;

 $parentescoPrincipal = $familiarPrincipal ? (
 optional($familiarPrincipal)->parentesco
 ?? optional($familiarPrincipal)->parentesco_vinculo
 ?? optional(optional($familiarPrincipal)->pivot)->parentesco_vinculo
 ?? 'No definido'
 ) : null;

 $telefonoPrincipal = $familiarPrincipal ? (
 optional($familiarPrincipal)->telefono
 ?? optional($familiarPrincipal)->celular
 ?? 'No registrado'
 ) : null;

 // 3. Obtener últimos movimientos reales
 $ultimaObs = ($observacionesLista ?? collect())->first();
 $ultimaAten = ($atencionesLista ?? collect())->first();
 $ultimaAct = ($actividadesLista ?? collect())->first();
 $ultimaEval = ($evaluacionesGeriatricasActivas ?? collect())->first();

 // 4. Estados de Alerta y Estilo de Estado
 $esActivo = ($estadoTexto ?? 'ACTIVO') === 'ACTIVO';
 $estadoBadgeColor = $esActivo 
 ? 'bg-fondo-panel text-parrafo border border-borde' 
 : 'bg-boton-acento/15 text-terracota border border-terracota/30';

 // 5. Pendientes reales
 $tieneFichaMedica = ($fichasMedicasLista ?? collect())->isNotEmpty();
 $tieneEvaluaciones = ($evaluacionesGeriatricasActivas ?? collect())->isNotEmpty();
 $tieneDocumentos = ($documentosLista ?? collect())->isNotEmpty();
 $expedienteCompleto = $tieneFichaMedica && $tieneEvaluaciones && $tieneDocumentos;

 // 6. Cálculo de Completitud de la Ficha
 $camposBase = [
 optional($adultoObj)->nombres,
 optional($adultoObj)->ap_paterno,
 optional($adultoObj)->ci,
 optional($adultoObj)->fecha_nac,
 optional($adultoObj)->genero,
 optional($adultoObj)->fecha_ing,
 optional($adultoObj)->cod_est_adul,
 ];
 $camposCompletos = collect($camposBase)->filter(fn($valor) => filled($valor))->count();
 $porcentajeFicha = round(($camposCompletos / count($camposBase)) * 100);
@endphp

<section
 
 
 class="grid gap-6 lg:grid-cols-3"
>
 {{-- COLUMNA IZQUIERDA Y CENTRAL (2/3 de ancho) --}}
 <div class="space-y-6 lg:col-span-2">
 
 {{-- Tarjeta 1: Identificación Rápida --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 Identificación Básica
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Perfil del Adulto Mayor
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-identification-card text-xl"></i>
 </div>
 </div>

 <div class="p-6">
 {{-- Bloque superior con foto y código --}}
 <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
 {{-- Fotografía o Iniciales --}}
 <div class="relative shrink-0">
 @if($fotoUrl)
 <img src="{{ $fotoUrl }}" alt="{{ $nombreCompleto }}" class="h-24 w-24 rounded-[20px] object-cover border-2 border-borde-suave shadow-md">
 @else
 <div class="flex h-24 w-24 items-center justify-center rounded-[20px] bg-gradient-to-br from-[#8EA17D]/25 to-[#6873A6]/15 text-2xl font-black text-titulo border border-borde-suave">
 {{ $iniciales }}
 </div>
 @endif
 <span class="absolute -bottom-1 -right-1 flex h-6 px-2 items-center justify-center rounded-lg bg-boton-principal text-[9px] font-bold text-inverso uppercase tracking-wider">
 {{ $idAdulto }}
 </span>
 </div>

 {{-- Nombre y estado principal --}}
 <div class="min-w-0 flex-1">
 <h1 class="text-2xl font-black text-titulo leading-snug">
 {{ $nombreCompleto ?: 'No registrado' }}
 </h1>
 <div class="mt-2 flex flex-wrap gap-2 items-center">
 <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $estadoBadgeColor }}">
 <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $esActivo ? 'bg-fondo-panel' : 'bg-boton-acento' }}"></span>
 {{ $estadoTexto }}
 </span>
 @if(optional($adultoObj)->archivado_en)
 <span class="inline-flex items-center rounded-lg bg-amber-600/10 text-amber-700 border border-amber-600/20 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">
 <i class="ph-bold ph-archive mr-1"></i> Archivado
 </span>
 @endif
 </div>
 </div>
 </div>

 {{-- Rejilla de datos demográficos --}}
 <div class="mt-6 grid gap-4 sm:grid-cols-2 md:grid-cols-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5 transition hover:bg-fondo-panel">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Documento de Identidad</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $ci }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5 transition hover:bg-fondo-panel">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Edad Calculada</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $edad ? $edad . ' años' : 'No disponible' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5 transition hover:bg-fondo-panel">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha de Nacimiento</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $fechaNacimientoFormateada }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5 transition hover:bg-fondo-panel">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Género</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ ucfirst(strtolower($genero)) }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5 transition hover:bg-fondo-panel">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Estado Civil</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->estado_civil ?? 'No registrado' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5 transition hover:bg-fondo-panel">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Nivel Educativo</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->nivel_educat ?? 'No registrado' }}</p>
 </div>
 </div>
 </div>
 </section>

 {{-- Tarjeta 2: Estado y Permanencia Institucional --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-parrafo">
 Estadía
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Permanencia y Ubicación Institucional
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-buildings text-xl"></i>
 </div>
 </div>

 <div class="p-6">
 {{-- Banner de Alerta si el estado no es ACTIVO --}}
 @if(!$esActivo)
 <div class="mb-5 flex items-start gap-3 rounded-xl border border-terracota/30 bg-boton-acento/10 p-4">
 <i class="ph-bold ph-warning-circle shrink-0 text-lg text-terracota"></i>
 <div>
 <p class="text-xs font-bold text-terracota uppercase tracking-wider">
 Expediente Inactivo o Archivado
 </p>
 <p class="mt-0.5 text-[11px] font-bold leading-5 text-terracota/80">
 La ficha de este adulto mayor no se encuentra activa en el flujo institucional regular. Verifique el historial de traslados u observaciones para mayor información.
 </p>
 </div>
 </div>
 @endif

 <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha de Ingreso</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $fechaIngresoFormateada }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Tiempo Registrado</p>
 <p class="mt-1 text-sm font-bold text-parrafo">{{ $antiguedadTexto }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Tipo de Ingreso</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->tipo_ing ?? 'No registrado' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Permanencia</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->permanencia ?? 'No registrada' }}</p>
 </div>
 </div>

 <div class="mt-4 grid gap-4 md:grid-cols-2">
 <div class="flex items-center gap-3 rounded-xl border border-borde-suave bg-fondo-panel p-4.5">
 <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-map-pin text-base"></i>
 </div>
 <div class="min-w-0">
 <p class="text-[9px] font-bold uppercase tracking-wider text-apoyo">Zona de Procedencia</p>
 <p class="mt-0.5 truncate text-xs font-bold text-titulo">{{ optional($adultoObj)->zona ?? 'No registrada' }}</p>
 </div>
 </div>

 <div class="flex items-center gap-3 rounded-xl border border-borde-suave bg-fondo-panel p-4.5">
 <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-road-horizon text-base"></i>
 </div>
 <div class="min-w-0">
 <p class="text-[9px] font-bold uppercase tracking-wider text-apoyo">Calle / Avenida</p>
 <p class="mt-0.5 truncate text-xs font-bold text-titulo">{{ optional($adultoObj)->calle ?? 'No registrada' }}</p>
 </div>
 </div>
 </div>
 </div>
 </section>

 {{-- Tarjeta 3: Movimientos Recientes Registrados --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-parrafo">
 Trazabilidad
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Movimientos Recientes
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-clock-counter-clockwise text-xl"></i>
 </div>
 </div>

 <div class="p-6">
 <div class="grid gap-4 sm:grid-cols-2">
 
 {{-- 1. Última Observación --}}
 <div class="flex flex-col justify-between rounded-2xl border border-borde-suave bg-fondo-panel p-4 transition hover:bg-fondo-panel">
 <div>
 <div class="flex items-center justify-between">
 <span class="inline-flex items-center rounded-lg bg-fondo-panel px-2 py-0.5 text-[9px] font-bold uppercase text-parrafo">
 Bitácora de Turno
 </span>
 @if($ultimaObs)
 <span class="text-[9px] font-bold text-apoyo">
 {{ Carbon::parse($ultimaObs->fecha)->format('d/m/Y') }}
 </span>
 @endif
 </div>
 <h3 class="mt-2.5 text-xs font-bold text-titulo">
 @if($ultimaObs)
 Clasificación: <span class="text-boton-acento">{{ $ultimaObs->tipo_obs }}</span>
 @else
 Seguimiento de Bitácora
 @endif
 </h3>
 <p class="mt-1.5 text-xs font-semibold leading-relaxed text-apoyo line-clamp-2">
 {{ $ultimaObs ? $ultimaObs->descripcion : 'Sin registros recientes' }}
 </p>
 </div>
 @if($ultimaObs)
 <div class="mt-3 border-t border-borde-suave pt-2 text-[10px] font-bold text-parrafo hover:text-parrafo cursor-pointer" @click="tab = 'seguimiento'">
 Ver todas las observaciones &rarr;
 </div>
 @endif
 </div>

 {{-- 2. Última Atención Registrada --}}
 <div class="flex flex-col justify-between rounded-2xl border border-borde-suave bg-fondo-panel p-4 transition hover:bg-fondo-panel">
 <div>
 <div class="flex items-center justify-between">
 <span class="inline-flex items-center rounded-lg bg-emerald-600/10 px-2 py-0.5 text-[9px] font-bold uppercase text-emerald-700">
 Atención Registrada
 </span>
 @if($ultimaAten)
 <span class="text-[9px] font-bold text-apoyo">
 {{ Carbon::parse($ultimaAten->fecha)->format('d/m/Y') }}
 </span>
 @endif
 </div>
 <h3 class="mt-2.5 text-xs font-bold text-titulo">
 @if($ultimaAten)
 Tipo: <span class="text-emerald-700">{{ optional($ultimaAten->tipoAtencion)->tipo ?? 'No definido' }}</span>
 @else
 Seguimiento de Atenciones
 @endif
 </h3>
 <p class="mt-1.5 text-xs font-semibold leading-relaxed text-apoyo line-clamp-2">
 {{ $ultimaAten ? $ultimaAten->obs : 'Sin registros recientes' }}
 </p>
 </div>
 @if($ultimaAten)
 <div class="mt-3 border-t border-borde-suave pt-2 text-[10px] font-bold text-emerald-700 hover:text-emerald-800 cursor-pointer" @click="tab = 'seguimiento'">
 Ver atenciones registradas &rarr;
 </div>
 @endif
 </div>

 {{-- 3. Última Actividad / Participación --}}
 <div class="flex flex-col justify-between rounded-2xl border border-borde-suave bg-fondo-panel p-4 transition hover:bg-fondo-panel">
 <div>
 <div class="flex items-center justify-between">
 <span class="inline-flex items-center rounded-lg bg-purple-600/10 px-2 py-0.5 text-[9px] font-bold uppercase text-purple-700">
 Participación en Actividad
 </span>
 @if($ultimaAct)
 <span class="text-[9px] font-bold text-apoyo">
 {{ Carbon::parse($ultimaAct->fecha)->format('d/m/Y') }}
 </span>
 @endif
 </div>
 <h3 class="mt-2.5 text-xs font-bold text-titulo">
 @if($ultimaAct)
 Área: <span class="text-purple-700">{{ optional($ultimaAct->tipoActividad)->tipo ?? 'No definido' }}</span>
 @else
 Actividades Registradas
 @endif
 </h3>
 <p class="mt-1.5 text-xs font-semibold leading-relaxed text-apoyo line-clamp-2">
 {{ $ultimaAct ? $ultimaAct->obs : 'Sin registros recientes' }}
 </p>
 </div>
 @if($ultimaAct)
 <div class="mt-3 border-t border-borde-suave pt-2 text-[10px] font-bold text-purple-700 hover:text-purple-800 cursor-pointer" @click="tab = 'seguimiento'">
 Ver participación en actividades &rarr;
 </div>
 @endif
 </div>

 {{-- 4. Última Evaluación Geriátrica --}}
 <div class="flex flex-col justify-between rounded-2xl border border-borde-suave bg-fondo-panel p-4 transition hover:bg-fondo-panel">
 <div>
 <div class="flex items-center justify-between">
 <span class="inline-flex items-center rounded-lg bg-blue-600/10 px-2 py-0.5 text-[9px] font-bold uppercase text-blue-700">
 Evaluación Geriátrica
 </span>
 @if($ultimaEval)
 <span class="text-[9px] font-bold text-apoyo">
 {{ $ultimaEval->fecha_eval->format('d/m/Y') }}
 </span>
 @endif
 </div>
 <h3 class="mt-2.5 text-xs font-bold text-titulo">
 @if($ultimaEval)
 {{ $ultimaEval->instrumento->nombre }}
 <span class="text-[10px] font-bold text-apoyo">({{ $ultimaEval->instrumento->siglas }})</span>
 @else
 Instrumentos de Valoración
 @endif
 </h3>
 <div class="mt-2 flex items-center gap-2">
 @if($ultimaEval)
 <span class="text-xs font-bold text-titulo">
 Resultado: <span class="text-blue-700">{{ $ultimaEval->puntaje_total ?? '--' }} pts</span>
 </span>
 @php
 $alerta = strtoupper($ultimaEval->nivel_alerta);
 $alertaColor = match($alerta) {
 'CRITICO' => 'bg-red-500/10 text-red-600 border border-red-500/20',
 'PREVENTIVO' => 'bg-amber-500/10 text-amber-700 border border-amber-500/20',
 default => 'bg-fondo-panel text-parrafo border border-borde',
 };
 $alertaLabel = match($alerta) {
 'CRITICO' => 'Crítico',
 'PREVENTIVO' => 'Preventivo',
 default => 'Normal',
 };
 @endphp
 <span class="rounded px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wider {{ $alertaColor }}">
 {{ $alertaLabel }}
 </span>
 @else
 <p class="text-xs font-semibold text-apoyo">Sin registros recientes</p>
 @endif
 </div>
 </div>
 @if($ultimaEval)
 <div class="mt-3 border-t border-borde-suave pt-2 text-[10px] font-bold text-blue-700 hover:text-blue-800 cursor-pointer" @click="tab = 'evaluaciones'">
 Ver Evaluaciones Geriátricas &rarr;
 </div>
 @endif
 </div>

 </div>
 </div>
 </section>

 </div>

 {{-- COLUMNA DERECHA (1/3 de ancho) --}}
 <div class="space-y-6">
 
 {{-- Tarjeta 4: Red de Apoyo Resumida --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-parrafo">
 Soporte
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Red de Apoyo y Emergencia
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-users-three text-xl"></i>
 </div>
 </div>

 <div class="p-6 space-y-4">
 
 {{-- Familiar Responsable Principal --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo mb-2">
 Responsable / Contacto Principal
 </p>
 
 @if($familiarPrincipal)
 <div class="flex items-start gap-3">
 <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-sm font-bold text-parrafo border border-borde">
 {{ strtoupper(substr($nombrePrincipal ?: 'F', 0, 1)) }}
 </div>
 <div class="min-w-0 flex-1">
 <p class="text-xs font-bold text-titulo truncate">{{ $nombrePrincipal }}</p>
 <p class="text-[10px] font-bold text-apoyo mt-0.5">Parentesco: {{ $parentescoPrincipal }}</p>
 <p class="text-[10px] font-bold text-parrafo mt-1 flex items-center gap-1">
 <i class="ph-bold ph-phone"></i> {{ $telefonoPrincipal }}
 </p>
 </div>
 </div>
 @else
 <div class="flex items-start gap-3 rounded-lg border border-amber-600/25 bg-amber-600/5 p-3">
 <i class="ph-bold ph-warning shrink-0 text-base text-amber-600 mt-0.5"></i>
 <div>
 <p class="text-[11px] font-bold text-amber-700">Sin contacto principal registrado</p>
 <p class="text-[9px] font-bold leading-4 text-amber-700/80 mt-0.5">Vincule al familiar principal desde la sección"Datos y Apoyo" para asegurar el seguimiento administrativo.</p>
 </div>
 </div>
 @endif
 </div>

 {{-- Contacto de Emergencia directo del modelo --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo mb-2">
 Contacto en Caso de Emergencia
 </p>

 @if(optional($adultoObj)->contacto_emergencia_nombre)
 <div class="space-y-2">
 <p class="text-xs font-bold text-titulo flex items-center gap-1.5">
 <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
 {{ optional($adultoObj)->contacto_emergencia_nombre }}
 </p>
 @if(optional($adultoObj)->contacto_emergencia_parentesco)
 <p class="text-[10px] font-bold text-apoyo pl-3">Vínculo: {{ optional($adultoObj)->contacto_emergencia_parentesco }}</p>
 @endif
 @if(optional($adultoObj)->contacto_emergencia_celular)
 <p class="text-[10px] font-bold text-red-600 pl-3 flex items-center gap-1">
 <i class="ph-bold ph-phone-call"></i> {{ optional($adultoObj)->contacto_emergencia_celular }}
 </p>
 @endif
 @if(optional($adultoObj)->contacto_emergencia_direccion)
 <p class="text-[9px] font-bold text-apoyo pl-3 leading-relaxed">
 <i class="ph-bold ph-map-pin"></i> {{ optional($adultoObj)->contacto_emergencia_direccion }}
 </p>
 @endif
 </div>
 @else
 <div class="flex items-start gap-3 rounded-lg border border-red-600/25 bg-red-600/5 p-3">
 <i class="ph-bold ph-warning-circle shrink-0 text-base text-red-600 mt-0.5"></i>
 <div>
 <p class="text-[11px] font-bold text-red-700">Sin datos de emergencia directos</p>
 <p class="text-[9px] font-bold leading-4 text-red-700/80 mt-0.5">Actualice los datos personales de contacto para emergencias y seguimiento.</p>
 </div>
 </div>
 @endif
 </div>

 </div>
 </section>

 {{-- Tarjeta 5: Control de Expediente y Requisitos --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 Expediente
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Completitud y Pendientes
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-clipboard-text text-xl"></i>
 </div>
 </div>

 <div class="p-6 space-y-4">
 {{-- Completitud del Registro General --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <div class="mb-2 flex items-center justify-between">
 <p class="text-xs font-bold text-titulo uppercase tracking-wider">
 Completitud de Ficha Base
 </p>
 <span class="text-xs font-bold text-boton-acento">
 {{ $porcentajeFicha }}%
 </span>
 </div>

 <div class="h-2 overflow-hidden rounded-full bg-fondo-app">
 <div class="h-full rounded-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8EA17D]"
 style="width: {{ $porcentajeFicha }}%;">
 </div>
 </div>
 <p class="mt-2 text-[9px] font-semibold leading-relaxed text-apoyo">
 Determina si el expediente base cuenta con los 7 campos de identificación y permanencia esenciales.
 </p>
 </div>

 {{-- Checklist de Pendientes Reales --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 space-y-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo border-b border-borde-suave pb-1.5">
 Estado de Flujos y Requisitos
 </p>
 
 {{-- Requisito 1: Ficha de Salud y Cuidados --}}
 <div class="flex items-center justify-between text-xs font-bold">
 <span class="text-apoyo flex items-center gap-1.5">
 <i class="ph-bold ph-stethoscope text-base {{ $tieneFichaMedica ? 'text-parrafo' : 'text-amber-600' }}"></i>
 Ficha de Salud y Cuidados
 </span>
 @if($tieneFichaMedica)
 <span class="rounded bg-fondo-panel text-parrafo text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5">Completada</span>
 @else
 <span class="rounded bg-amber-500/15 text-amber-700 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5">Pendiente</span>
 @endif
 </div>

 {{-- Requisito 2: Evaluaciones Geriátricas --}}
 <div class="flex items-center justify-between text-xs font-bold">
 <span class="text-apoyo flex items-center gap-1.5">
 <i class="ph-bold ph-brain text-base {{ $tieneEvaluaciones ? 'text-parrafo' : 'text-amber-600' }}"></i>
 Evaluaciones Geriátricas
 </span>
 @if($tieneEvaluaciones)
 <span class="rounded bg-fondo-panel text-parrafo text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5">Registradas</span>
 @else
 <span class="rounded bg-amber-500/15 text-amber-700 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5">Pendientes</span>
 @endif
 </div>

 {{-- Requisito 3: Expediente Físico --}}
 <div class="flex items-center justify-between text-xs font-bold">
 <span class="text-apoyo flex items-center gap-1.5">
 <i class="ph-bold ph-folder-open text-base {{ $tieneDocumentos ? 'text-parrafo' : 'text-amber-600' }}"></i>
 Archivos Digitales
 </span>
 @if($tieneDocumentos)
 <span class="rounded bg-fondo-panel text-parrafo text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5">Con documentos</span>
 @else
 <span class="rounded bg-amber-500/15 text-amber-700 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5">Sin archivos</span>
 @endif
 </div>

 {{-- Estado final --}}
 @if($expedienteCompleto)
 <div class="mt-4 flex items-center gap-2 rounded-xl bg-fondo-panel border border-borde p-2.5 text-[10px] font-bold text-parrafo justify-center uppercase tracking-wider">
 <i class="ph-bold ph-check-circle text-base"></i>
 ¡Expediente al día y completo!
 </div>
 @else
 <div class="mt-4 flex items-center gap-2 rounded-xl bg-amber-500/10 border border-amber-500/20 p-2.5 text-[10px] font-bold text-amber-700 justify-center uppercase tracking-wider">
 <i class="ph-bold ph-info text-base"></i>
 Pendientes Administrativos
 </div>
 @endif
 </div>
 </div>
 </section>

 {{-- Tarjeta 6: Acciones Rápidas del Expediente --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-parrafo">
 Operaciones
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Acciones Institucionales y Administrativas
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-lightning text-xl"></i>
 </div>
 </div>

 <div class="p-6 grid grid-cols-2 gap-3">
 
 {{-- 1. Agregar Familiar --}}
 <button type="button"
 @click="abrir('familiar')"
 class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 text-center text-xs font-bold text-titulo transition hover:-translate-y-0.5 hover:bg-boton-principal hover:text-inverso hover:border-transparent active:scale-[0.98]">
 <i class="ph-bold ph-users-three text-xl text-boton-acento group-hover:text-inverso"></i>
 Agregar Familiar
 </button>

 {{-- 2. Registrar Observación --}}
 <button type="button"
 @click="abrir('observacion')"
 class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 text-center text-xs font-bold text-titulo transition hover:-translate-y-0.5 hover:bg-boton-principal hover:text-inverso hover:border-transparent active:scale-[0.98]">
 <i class="ph-bold ph-book-open text-xl text-parrafo group-hover:text-inverso"></i>
 Registrar Notas
 </button>

 {{-- 3. Registrar Signos Vitales --}}
 <button type="button"
 @click="abrir('signos-vitales')"
 class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 text-center text-xs font-bold text-titulo transition hover:-translate-y-0.5 hover:bg-boton-principal hover:text-inverso hover:border-transparent active:scale-[0.98]">
 <i class="ph-bold ph-heartbeat text-xl text-emerald-700 group-hover:text-inverso"></i>
 Signos Vitales
 </button>

 {{-- 4. Registrar Evaluación Geriátrica --}}
 <button type="button"
 @click="abrir('evaluacion')"
 class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 text-center text-xs font-bold text-titulo transition hover:-translate-y-0.5 hover:bg-boton-principal hover:text-inverso hover:border-transparent active:scale-[0.98]">
 <i class="ph-bold ph-brain text-xl text-blue-600 group-hover:text-inverso"></i>
 Evaluación Geriátrica
 </button>

 {{-- 5. Subir Documento --}}
 <button type="button"
 @click="abrir('documento')"
 class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 text-center text-xs font-bold text-titulo transition hover:-translate-y-0.5 hover:bg-boton-principal hover:text-inverso hover:border-transparent active:scale-[0.98]">
 <i class="ph-bold ph-upload-simple text-xl text-purple-600 group-hover:text-inverso"></i>
 Subir Archivo
 </button>

 {{-- 6. Generar Reporte PDF --}}
 <a href="{{ (Route::has('admin.adultos-mayores.reporte-individual') ? route('admin.adultos-mayores.reporte-individual', $idAdulto) : '#') }}"
 target="_blank"
 class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 text-center text-xs font-bold text-titulo transition hover:-translate-y-0.5 hover:bg-boton-principal hover:text-inverso hover:border-transparent active:scale-[0.98]">
 <i class="ph-bold ph-file-pdf text-xl text-red-600 group-hover:text-inverso"></i>
 Ficha PDF
 </a>

 </div>
 </section>

 </div>
</section>