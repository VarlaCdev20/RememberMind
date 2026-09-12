{{-- TAB FAMILIARES --}}
@php
 use Carbon\Carbon;
 $adultoObj = is_object($adulto ?? null) ? $adulto : null;

 // 1. Detección y clasificación de alertas suaves institucionales
 $alertas = [];
 
 // Alerta: Sin familiares
 if ($familiaresActivos->isEmpty()) {
 $alertas[] = [
 'tipo' => 'warning',
 'titulo' => 'Sin familiares vinculados',
 'icon' => 'ph-heart-break',
 'desc' => 'Se aconseja asociar al menos un contacto familiar para coordinaciones y seguimiento.'
 ];
 }
 
 // Alerta: Sin responsable principal
 $tieneResponsable = false;
 foreach ($familiaresActivos as $fam) {
 $esResp = optional($fam)->es_responsable ?? optional(optional($fam)->pivot)->es_responsable ?? false;
 if ($esResp) {
 $tieneResponsable = true;
 break;
 }
 }
 if ($familiaresActivos->isNotEmpty() && !$tieneResponsable) {
 $alertas[] = [
 'tipo' => 'info',
 'titulo' => 'Sin responsable principal',
 'icon' => 'ph-user-focus',
 'desc' => 'Defina cuál de los familiares actúa como responsable principal para firmas y autorizaciones.'
 ];
 }
 
 // Alerta: Sin contacto de emergencia directo
 $emergenciaNombre = optional($adultoObj)->contacto_emergencia_nombre;
 if (!$emergenciaNombre) {
 $alertas[] = [
 'tipo' => 'danger',
 'titulo' => 'Sin contacto de emergencia',
 'icon' => 'ph-phone-call',
 'desc' => 'Es crucial registrar nombre y celular directo para contingencias médicas o traslados.'
 ];
 }
 
 // Alerta: Falta celular
 $faltaContacto = !$tieneCelular || $celular === 'No registrado';
 if ($faltaContacto && $telefonoFijo === 'No registrado') {
 $alertas[] = [
 'tipo' => 'info',
 'titulo' => 'Sin números de contacto',
 'icon' => 'ph-phone-x',
 'desc' => 'No se tienen registrados números telefónicos del adulto mayor en el sistema.'
 ];
 }
 
 // Alerta: Domicilio incompleto
 $domicilioIncompleto = !$zona || !$calle || $zona === 'No registrada' || $calle === 'No registrada';
 if ($domicilioIncompleto) {
 $alertas[] = [
 'tipo' => 'info',
 'titulo' => 'Domicilio incompleto',
 'icon' => 'ph-map-pin-line',
 'desc' => 'La zona o calle de procedencia no se encuentran completamente especificadas.'
 ];
 }
@endphp

<section
 x-show="tab === 'familiares'"
 x-transition.opacity.duration.250ms
 class="space-y-6"
>
 
 {{-- CENTRO DE ALERTAS INSTITUCIONALES (Si existen) --}}
 @if(count($alertas) > 0)
 <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
 @foreach($alertas as $alerta)
 @php
 $alertaColorClass = match($alerta['tipo']) {
 'danger' => 'border-red-500/30 bg-red-500/5 text-red-700',
 'warning' => 'border-amber-600/30 bg-amber-600/5 text-amber-700',
 default => 'border-blue-600/30 bg-blue-600/5 text-blue-700',
 };
 $alertaIconColor = match($alerta['tipo']) {
 'danger' => 'text-red-600 bg-red-600/10',
 'warning' => 'text-amber-600 bg-amber-600/10',
 default => 'text-blue-600 bg-blue-600/10',
 };
 @endphp
 <div class="flex items-start gap-3 rounded-2xl border p-4.5 transition hover:shadow-sm {{ $alertaColorClass }}">
 <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-base {{ $alertaIconColor }}">
 <i class="ph-bold {{ $alerta['icon'] }}"></i>
 </div>
 <div class="min-w-0">
 <h4 class="text-xs font-bold uppercase tracking-wider leading-none">{{ $alerta['titulo'] }}</h4>
 <p class="mt-1.5 text-[11px] font-bold leading-relaxed opacity-90">{{ $alerta['desc'] }}</p>
 </div>
 </div>
 @endforeach
 </section>
 @endif

 {{-- ORGANIZACIÓN EN DOS COLUMNAS PRINCIPALES --}}
 <div class="grid gap-6 xl:grid-cols-3">
 
 {{-- COLUMNA IZQUIERDA Y CENTRAL: DATOS DEL ADULTO MAYOR (2/3 de ancho) --}}
 <div class="space-y-6 xl:col-span-2">
 
 {{-- Sección A.1: Datos Personales y Demográficos --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 Expediente
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Datos Personales del Adulto Mayor
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-user-circle text-xl"></i>
 </div>
 </div>

 <div class="p-6">
 <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Nombre Completo</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $nombreCompleto ?: 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Estado institucional</p>
 <p class="mt-1 text-sm font-bold text-boton-acento">{{ optional($adultoObj)->estado_adulto ?: 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Documento de Identidad</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ $ci }} 
 @if(optional($adultoObj)->complemento_ci)
 <span class="text-xs font-bold text-apoyo">- {{ optional($adultoObj)->complemento_ci }}</span>
 @endif
 @if(optional($adultoObj)->expedicion_ci)
 <span class="ml-1 rounded bg-fondo-panel px-1 py-0.5 text-[10px] font-bold text-titulo uppercase">{{ optional($adultoObj)->expedicion_ci }}</span>
 @endif
 </p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha de Nacimiento</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $fechaNacimientoFormateada }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Edad Calculada</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $edad ? $edad . ' años' : 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Género</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ ucfirst(strtolower($genero)) }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Estado Civil</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->estado_civil ?? 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Nivel Educativo</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->nivel_educat ?? 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Nacionalidad / Origen</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->nacionalidad ?? 'Boliviana' }}</p>
 </div>
 </div>

 {{-- Observación General --}}
 <div class="mt-4 rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo mb-1.5">Observaciones Generales del Expediente</p>
 <p class="text-xs font-semibold leading-relaxed text-apoyo">
 {{ optional($adultoObj)->observaciones ?: 'Sin observaciones generales de registro en esta ficha.' }}
 </p>
 </div>
 </div>
 </section>

 {{-- Sección A.2: Contacto y Domicilio --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-parrafo">
 Localización
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Contacto y Domicilio de Procedencia
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-map-pin text-xl"></i>
 </div>
 </div>

 <div class="p-6">
 <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Celular del Adulto Mayor</p>
 <p class="mt-1 text-sm font-bold text-titulo flex items-center gap-1.5">
 {{ $celular }}
 @if($sabeUsarWhatsapp && $celular !== 'Sin registrar')
 <span class="inline-flex items-center rounded bg-emerald-500/10 text-emerald-600 px-1 py-0.5 text-[8px] font-black uppercase">WA</span>
 @endif
 </p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Teléfono Fijo</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $telefonoFijo }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Ciudad / Municipio</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->ciudad_municipio ?? 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Departamento de Residencia</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->departamento_residencia ?? 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Zona / Barrio</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $zona }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Calle o Avenida</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $calle }}</p>
 </div>
 </div>

 <div class="mt-4 grid gap-4 sm:grid-cols-2">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Número de Domicilio</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ optional($adultoObj)->num_dom ?? 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3.5">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Referencia de Ubicación</p>
 <p class="mt-1 text-sm font-bold text-titulo truncate">{{ optional($adultoObj)->referencia ?? 'Sin registrar' }}</p>
 </div>
 </div>
 </div>
 </section>

 </div>

 {{-- COLUMNA DERECHA: CONTACTO DE EMERGENCIA DIRECTO (1/3 de ancho) --}}
 <div class="space-y-6">
 
 {{-- Sección A.3: Contacto de Emergencia Directo --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl transition hover:shadow-[0_16px_36px_rgba(47,62,92,0.12)]">
 <div class="flex items-center justify-between border-b border-borde-suave px-6 py-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-red-600">
 Contingencia
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Contacto de Emergencia
 </h2>
 </div>
 <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-600/12 text-red-600">
 <i class="ph-bold ph-phone-call text-xl"></i>
 </div>
 </div>

 <div class="p-6">
 @if($emergenciaNombre)
 <div class="space-y-4">
 <div class="rounded-xl border border-borde-suave bg-red-600/5 p-4">
 <p class="text-[9px] font-bold uppercase tracking-widest text-red-600 mb-1">Nombre Completo</p>
 <p class="text-sm font-bold text-titulo">{{ $emergenciaNombre }}</p>
 </div>

 <div class="grid grid-cols-2 gap-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo mb-1">Parentesco</p>
 <p class="text-xs font-bold text-titulo">{{ optional($adultoObj)->contacto_emergencia_parentesco ?: 'Sin registrar' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-red-600/5 p-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-red-600 mb-1">Celular / Teléfono</p>
 <p class="text-xs font-bold text-red-600 flex items-center gap-1">
 <i class="ph-bold ph-phone"></i> {{ optional($adultoObj)->contacto_emergencia_celular ?: 'Sin registrar' }}
 </p>
 </div>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo mb-1">Dirección del Contacto</p>
 <p class="text-xs font-semibold leading-relaxed text-apoyo">
 {{ optional($adultoObj)->contacto_emergencia_direccion ?: 'Sin registrar' }}
 </p>
 </div>
 </div>
 @else
 <div class="flex flex-col items-center justify-center py-6 text-center">
 <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-600/10 text-red-600">
 <i class="ph-bold ph-warning-octagon text-2xl"></i>
 </div>
 <h3 class="mt-4 text-xs font-bold text-red-700 uppercase tracking-wider">
 Sin contacto de emergencia registrado
 </h3>
 <p class="mt-2 max-w-xs text-[11px] font-bold leading-relaxed text-red-700/70">
 Se requiere actualizar la información médica básica y de contacto para definir un responsable ante contingencias inmediatas.
 </p>
 
 @if($idAdulto)
 <a href="{{ route('admin.adultos-mayores.edit', $idAdulto) }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-inverso transition hover:bg-red-700 hover:-translate-y-0.5 active:scale-95 shadow-md">
 <i class="ph-bold ph-pencil-simple"></i> Registrar Contacto
 </a>
 @endif
 </div>
 @endif
 </div>
 </section>
 
 {{-- Acciones Rápidas del Módulo --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
 <div class="border-b border-borde-suave px-6 py-4">
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-parrafo">
 Gestión
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Acciones Rápidas
 </h2>
 </div>
 
 <div class="p-6 space-y-3">
 @if($idAdulto)
 <a href="{{ route('admin.adultos-mayores.edit', $idAdulto) }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-boton-principal py-3 text-xs font-bold text-inverso shadow-md transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-pencil-simple-line"></i> Editar Ficha Demográfica
 </a>
 @endif

 <button type="button" @click="abrir('FAMILIAR')" class="flex w-full items-center justify-center gap-2 rounded-xl bg-fondo-panel py-3 text-xs font-bold text-inverso shadow-md transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-user-plus"></i> Vincular Familiar a Ficha
 </button>
 </div>
 </section>

 </div>
 </div>

 {{-- BLOQUE B: RED DE APOYO FAMILIAR --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
 <div class="flex flex-col gap-4 border-b border-borde-suave px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-parrafo">
 Familiares Vinculados
 </span>
 <h2 class="mt-0.5 text-base font-extrabold text-titulo">
 Red de Apoyo Familiar Activa
 </h2>
 </div>
 
 <div class="flex items-center gap-2">
 <span class="rounded-full bg-fondo-panel px-3 py-1 text-[10px] font-bold text-parrafo uppercase border border-borde">
 {{ $totalFamiliares }} {{ $totalFamiliares === 1 ? 'Familiar' : 'Familiares' }}
 </span>
 </div>
 </div>

 <div class="p-6">
 {{-- Forelse de Familiares Activos en Formato de Tarjetas Premium --}}
 <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
 @forelse($familiaresActivos as $familiar)
 @php
 $familiarObj = is_object($familiar) ? $familiar : null;
 $codFamiliar = optional($familiarObj)->cod_fam;
 $nombreFamiliar = trim(
 (optional($familiarObj)->nombres ?? optional($familiarObj)->nombre ?? '') . ' ' .
 (optional($familiarObj)->ap_paterno ?? '') . ' ' .
 (optional($familiarObj)->ap_materno ?? '')
 );
 $parentescoFamiliar = optional($familiarObj)->parentesco
 ?? optional($familiarObj)->parentesco_vinculo
 ?? optional(optional($familiarObj)->pivot)->parentesco_vinculo
 ?? 'No definido';
 $telefonoFamiliar = optional($familiarObj)->telefono
 ?? optional($familiarObj)->celular
 ?? 'No registrado';
 $esResponsable = optional($familiarObj)->es_responsable
 ?? optional(optional($familiarObj)->pivot)->es_responsable
 ?? false;
 $estadoVinc = strtoupper(optional(optional($familiarObj)->pivot)->estado ?? 'ACTIVO');
 
 // Verificar si califica como contacto de emergencia
 $esEmergencia = $emergenciaNombre && (
 stripos($nombreFamiliar, $emergenciaNombre) !== false ||
 ($telefonoFamiliar !== 'No registrado' && optional($adultoObj)->contacto_emergencia_celular && stripos($telefonoFamiliar, optional($adultoObj)->contacto_emergencia_celular) !== false)
 );
 @endphp
 
 <div class="relative flex flex-col justify-between rounded-[20px] border border-borde-suave bg-fondo-panel p-5 transition-all duration-300 hover:-translate-y-1 hover:bg-fondo-panel hover:shadow-[0_12px_24px_rgba(47,62,92,0.06)]">
 <div>
 {{-- Fila superior con avatar e iniciales --}}
 <div class="flex items-start justify-between gap-3">
 <div class="flex items-center gap-3">
 <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-fondo-panel text-base font-extrabold text-parrafo border border-borde">
 {{ strtoupper(substr($nombreFamiliar ?: 'F', 0, 1)) }}
 </div>
 <div class="min-w-0">
 <h3 class="text-sm font-bold text-titulo leading-snug truncate" title="{{ $nombreFamiliar }}">
 {{ $nombreFamiliar ?: 'Familiar' }}
 </h3>
 <p class="text-[9px] font-bold text-apoyo uppercase tracking-wider mt-0.5">Familiar #{{ $codFamiliar }}</p>
 </div>
 </div>
 </div>

 {{-- Badges de Categorización del Familiar --}}
 <div class="mt-3 flex flex-wrap gap-1.5">
 <span class="rounded bg-fondo-app px-2 py-0.5 text-[9px] font-bold uppercase text-apoyo">
 {{ $parentescoFamiliar }}
 </span>
 
 @if($esResponsable)
 <span class="inline-flex items-center gap-1 rounded bg-estado-peligroBg px-2 py-0.5 text-[9px] font-bold uppercase text-parrafo">
 <span class="h-1.5 w-1.5 rounded-full bg-boton-acento"></span>
 Responsable principal
 </span>
 @endif

 @if($esEmergencia)
 <span class="inline-flex items-center gap-1 rounded bg-red-600/10 px-2 py-0.5 text-[9px] font-bold uppercase text-red-600">
 <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
 Contacto de emergencia
 </span>
 @endif
 </div>

 {{-- Datos de Contacto y Residencia --}}
 <div class="mt-4.5 space-y-2 border-t border-borde-suave pt-3.5">
 <div class="flex items-center gap-2 text-xs font-bold text-titulo">
 <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-apoyo">
 <i class="ph-bold ph-phone"></i>
 </div>
 <span class="truncate">{{ $telefonoFamiliar }}</span>
 
 @if($telefonoFamiliar !== 'No registrado' && (strlen($telefonoFamiliar) >= 7))
 <a href="https://wa.me/591{{ preg_replace('/\D/', '', $telefonoFamiliar) }}" title="Contactar por WhatsApp"
 class="ml-auto flex h-6 w-6 items-center justify-center rounded-full bg-fondo-panel text-parrafo transition hover:bg-fondo-panel hover:text-inverso">
 <i class="ph-fill ph-whatsapp-logo text-xs"></i>
 </a>
 @endif
 </div>

 @if(optional($familiarObj)->usuario && optional($familiarObj->usuario)->email)
 <div class="flex items-center gap-2 text-xs font-bold text-titulo">
 <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-apoyo">
 <i class="ph-bold ph-envelope"></i>
 </div>
 <span class="truncate" title="{{ $familiarObj->usuario->email }}">{{ $familiarObj->usuario->email }}</span>
 </div>
 @endif

 <div class="flex items-start gap-2 text-xs font-bold text-apoyo">
 <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-apoyo mt-0.5">
 <i class="ph-bold ph-map-pin"></i>
 </div>
 <span class="leading-relaxed line-clamp-2" title="{{ optional($familiarObj)->direccion ?: 'Sin domicilio registrado' }}">
 {{ optional($familiarObj)->direccion ?: 'Sin domicilio registrado' }}
 </span>
 </div>
 </div>
 </div>

 {{-- Barra de Acciones Reales --}}
 <div class="mt-5 flex justify-end gap-1.5 border-t border-borde-suave pt-3">
 <button type="button" @click="abrir('FAMILIAR', @js($familiar), false, true)" title="Ver Ficha Detallada" class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso">
 <i class="ph-bold ph-eye text-sm"></i>
 </button>
 
 <button type="button" @click="abrir('FAMILIAR', @js($familiar), true, false)" title="Editar Familiar" class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso">
 <i class="ph-bold ph-pencil-simple text-sm"></i>
 </button>

 <form action="{{ route('admin.adultos-mayores.familiares.destroy', [$idAdulto, $codFamiliar]) }}" method="POST" onsubmit="confirmarAccion(event, 'Desactivar vínculo familiar', 'El vínculo con este familiar se marcará como inactivo. Se conservará el registro histórico.')">
 @csrf @method('DELETE')
 <button type="submit" title="Desactivar vínculo" class="flex h-8 w-8 items-center justify-center rounded-lg bg-boton-acento/10 text-terracota transition hover:bg-boton-acento hover:text-inverso">
 <i class="ph-bold ph-user-minus text-sm"></i>
 </button>
 </form>
 </div>
 </div>
 @empty
 <div class="col-span-full py-10 text-center">
 <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-heart text-2xl"></i>
 </div>
 <h4 class="mt-4 text-sm font-bold text-titulo">Sin familiares vinculados</h4>
 <p class="mx-auto mt-2 max-w-xs text-xs font-bold text-apoyo">
 Registre a los familiares responsables de apoyo directo para coordinar cuidados y actas.
 </p>
 <button type="button" @click="abrir('FAMILIAR')" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-fondo-panel px-4 py-2 text-xs font-bold text-inverso transition hover:bg-fondo-panel active:scale-95 shadow-md">
 <i class="ph-bold ph-plus"></i> Vincular primer familiar
 </button>
 </div>
 @endforelse
 </div>

 {{-- Vínculos Familiares Desactivados (Historial de Red de Apoyo) --}}
 @if(count($familiaresInactivos) > 0)
 <div class="mt-8 border-t border-borde-suave pt-6">
 <div class="mb-4 flex items-center justify-between">
 <h4 class="text-xs font-bold uppercase tracking-widest text-parrafo flex items-center gap-2">
 <i class="ph-bold ph-archive"></i> Historial de Vínculos Desactivados
 </h4>
 <span class="rounded-full bg-fondo-panel px-2 py-0.5 text-[9px] font-bold text-parrafo uppercase tracking-wider">Historial Institucional</span>
 </div>
 
 <div class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-card/20 backdrop-blur-sm shadow-sm">
 <table class="w-full text-left text-sm text-titulo">
 <thead class="bg-fondo-panel text-[9px] uppercase tracking-widest text-apoyo">
 <tr>
 <th class="px-6 py-3">Familiar / Contacto</th>
 <th class="px-6 py-3">Parentesco anterior</th>
 <th class="px-6 py-3 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#D5C7B9]/30">
 @foreach($familiaresInactivos as $finac)
 @php
 $finacObj = is_object($finac) ? $finac : null;
 $codFinac = optional($finacObj)->cod_fam;
 $nombreFinac = trim(
 (optional($finacObj)->nombres ?? optional($finacObj)->nombre ?? '') . ' ' .
 (optional($finacObj)->ap_paterno ?? '') . ' ' .
 (optional($finacObj)->ap_materno ?? '')
 );
 @endphp
 <tr class="hover:bg-fondo-panel transition grayscale-[60%] opacity-70 hover:grayscale-0 hover:opacity-100">
 <td class="px-6 py-3">
 <div class="flex items-center gap-2">
 <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-fondo-panel text-[10px] font-bold text-parrafo">
 {{ strtoupper(substr($nombreFinac ?: 'F', 0, 1)) }}
 </div>
 <span class="text-xs font-bold text-titulo">{{ $nombreFinac }}</span>
 </div>
 </td>
 <td class="px-6 py-3 text-xs text-apoyo">
 {{ optional($finacObj->pivot)->parentesco_vinculo ?: 'No definido' }}
 </td>
 <td class="px-6 py-3 text-right">
 <form action="{{ route('admin.adultos-mayores.familiares.restore', [$idAdulto, $codFinac]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar vínculo', 'El familiar volverá a estar vinculado activamente al expediente.')">
 @csrf @method('PATCH')
 <button type="submit" title="Restaurar vínculo activo" class="rounded-lg bg-fondo-panel p-1.5 text-parrafo hover:bg-fondo-panel hover:text-inverso transition">
 <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i>
 </button>
 </form>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>
 @endif

 </div>
 </section>

 {{-- Tabla de Voluntarios --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
 <div class="flex items-center justify-between gap-3 border-b border-borde-suave px-6 py-4 bg-fondo-panel">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-hand-heart text-xl"></i>
 </div>
 <div>
 <h3 class="text-base font-extrabold text-titulo">Voluntarios Asignados</h3>
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-widest">Apoyo y acompañamiento externo</p>
 </div>
 </div>
 <button type="button" @click="abrir('VOLUNTARIO')" class="rounded-xl bg-fondo-panel px-4 py-2 text-xs font-bold text-inverso hover:bg-fondo-panel transition">
 <i class="ph-bold ph-user-plus mr-1"></i> Asignar
 </button>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left text-sm text-titulo">
 <thead class="bg-fondo-panel text-[10px] uppercase tracking-widest text-apoyo">
 <tr>
 <th class="px-6 py-3">Voluntario</th>
 <th class="px-6 py-3">Periodo</th>
 <th class="px-6 py-3">Observación</th>
 <th class="px-6 py-3">Estado</th>
 <th class="px-6 py-3 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#D5C7B9]/30">
 @forelse($asignacionesLista ?? collect() as $asignacion)
 @php
 $asignacionObj = is_object($asignacion) ? $asignacion : null;
 $nombreVol = optional($asignacionObj)->voluntario->persona->nombre_completo 
 ?? optional($asignacionObj)->voluntario->name 
 ?? 'Voluntario';
 $estadoAsig = strtoupper(optional($asignacionObj)->estado ?? 'ACTIVO');
 $colorAsig = match($estadoAsig) {
 'ACTIVO' => 'bg-fondo-panel text-parrafo',
 'FINALIZADO' => 'bg-fondo-panel text-titulo',
 default => 'bg-fondo-panel text-parrafo',
 };
 @endphp
 <tr class="group transition hover:bg-fondo-panel">
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="flex items-center gap-2">
 <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-xs font-bold text-parrafo">
 {{ strtoupper(substr($nombreVol, 0, 1)) }}
 </div>
 <span class="text-xs font-bold text-titulo">{{ $nombreVol }}</span>
 </div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <p class="text-[10px] font-bold text-apoyo uppercase tracking-tighter">
 {{ optional($asignacionObj)->fecha_asig ? Carbon::parse($asignacionObj->fecha_asig)->format('d/m/Y') : 'INICIO N/D' }}
 —
 {{ optional($asignacionObj)->fecha_fin ? Carbon::parse($asignacionObj->fecha_fin)->format('d/m/Y') : 'PRESENTE' }}
 </p>
 </td>
 <td class="px-6 py-4">
 <p class="text-[11px] font-semibold text-apoyo line-clamp-1">{{ optional($asignacionObj)->obser ?? 'Sin observación' }}</p>
 </td>
 <td class="px-6 py-4">
 <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-bold uppercase {{ $colorAsig }}">
 {{ $estadoAsig }}
 </span>
 </td>
 <td class="px-6 py-4 text-right">
 <div class="flex justify-end gap-1.5">
 <button type="button" @click="abrir('VOLUNTARIO', @js($asignacion), false, true)" class="rounded-lg bg-fondo-panel p-2 text-titulo hover:bg-boton-principal hover:text-inverso transition">
 <i class="ph-bold ph-eye"></i>
 </button>
 <button type="button" @click="abrir('VOLUNTARIO', @js($asignacion), true, false)" class="rounded-lg bg-fondo-panel p-2 text-titulo hover:bg-boton-principal hover:text-inverso transition">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 <button type="button" class="rounded-lg bg-boton-acento/5 p-2 text-terracota hover:bg-boton-acento hover:text-inverso transition">
 <i class="ph-bold ph-trash-simple"></i>
 </button>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-12 text-center text-xs font-bold text-apoyo">Sin voluntarios asignados.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </section>

</section>
