<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarInstitucionHttp;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

$actor = User::where('correo', 'admincasaamandita@gmail.com')->sole();
$cuenta = User::where('correo', 'enfermeria@remembermind.com')->sole();
$personal = Personal::where('cod_usuario', $cuenta->getKey())->sole();
$area = Area::where('nombre', 'Enfermería integral')->where('estado', 'ACTIVA')->sole();
if ($actor->estado !== 'ACTIVO' || $cuenta->estado !== 'ACTIVO' || $personal->estado !== 'ACTIVO'
    || ! $cuenta->hasRole('ENFERMEROS') || mb_strtoupper(trim($personal->nombres.' '.$personal->apellido_paterno)) !== 'ROSA MAMANI') {
    throw new RuntimeException('Identidad, rol o estado no compatibles con la solicitud confirmada.');
}
foreach (['turnos.gestionar', 'jornadas.gestionar', 'asignaciones_personal.gestionar'] as $permiso) {
    if (! $actor->checkPermissionTo($permiso, 'web')) {
        throw new RuntimeException('El actor no tiene el permiso explícito requerido: '.$permiso);
    }
}

$inicio = CarbonImmutable::now('America/La_Paz')->startOfMinute();
$fin = $inicio->addHours(72);
$anteriores = AsignacionPersonal::with('jornada.turno')->where('cod_personal', $personal->getKey())
    ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
    ->whereHas('jornada', fn ($q) => $q->whereBetween('fecha_jornada', [$inicio->subDay()->toDateString(), $fin->toDateString()]))->get();
$ventanas = [];
foreach ($anteriores as $a) {
    $j = $a->jornada;
    $t = $j->turno;
    $desde = CarbonImmutable::parse($j->fecha_jornada->format('Y-m-d').' '.$t->hora_inicio, 'America/La_Paz');
    $hasta = CarbonImmutable::parse($j->fecha_jornada->format('Y-m-d').' '.$t->hora_cierre, 'America/La_Paz');
    if ($hasta->lessThanOrEqualTo($desde)) {
        $hasta = $hasta->addDay();
    }
    $ventanas[] = ['id' => $a->getKey(), 'inicio' => $desde->toIso8601String(), 'fin' => $hasta->toIso8601String()];
    if ($desde->lt($fin) && $hasta->gt($inicio)) {
        throw new RuntimeException('Hay una asignación que se solapa: '.$a->getKey());
    }
}

if (($argv[1] ?? '') !== '--aplicar') {
    echo json_encode(['modo' => 'CONSULTA_SIN_ESCRITURAS', 'actor' => $actor->getKey(), 'personal' => $personal->getKey(),
        'nombre' => trim($personal->nombres.' '.$personal->apellido_paterno.' '.$personal->apellido_materno), 'area' => $area->nombre,
        'inicio_propuesto' => $inicio->toIso8601String(), 'fin_propuesto' => $fin->toIso8601String(), 'duracion_horas' => 72,
        'asignaciones_cercanas_sin_solapamiento' => $ventanas], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    exit;
}

$solicitud = 'ROSA-72H-20261019';
$resultado = DB::transaction(function () use ($actor, $cuenta, $personal, $area, $inicio, $fin, $solicitud): array {
    Personal::whereKey($personal->getKey())->lockForUpdate()->sole();
    foreach (AsignacionPersonal::with('jornada.turno')->where('cod_personal', $personal->getKey())
        ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
        ->whereHas('jornada', fn ($q) => $q->whereBetween('fecha_jornada', [$inicio->subDay()->toDateString(), $fin->toDateString()]))->lockForUpdate()->get() as $actual) {
        $j = $actual->jornada;
        $desde = CarbonImmutable::parse($j->fecha_jornada->format('Y-m-d').' '.$j->turno->hora_inicio, 'America/La_Paz');
        $hasta = CarbonImmutable::parse($j->fecha_jornada->format('Y-m-d').' '.$j->turno->hora_cierre, 'America/La_Paz');
        if ($hasta->lte($desde)) { $hasta = $hasta->addDay(); }
        if ($desde->lt($fin) && $hasta->gt($inicio)) {
            throw new RuntimeException('Se detectó una asignación concurrente que se solapa.');
        }
    }
    if (AsignacionPersonal::where('cod_personal', $personal->getKey())->where('observacion', 'like', '%'.$solicitud.'%')->exists()) {
        throw new RuntimeException('La solicitud ya fue registrada. No se duplica.');
    }
    $gestor = app(GestionarInstitucionHttp::class);
    $turnos = [];
    for ($b = 0; $b < 2; $b++) {
        $desde = $inicio->addHours($b * 12);
        $turnos[$b] = $gestor->guardarTurno($actor, [
            'nombre' => 'Rosa 72h 19 Oct - Bloque '.($b + 1),
            'hora_inicio' => $desde->format('H:i'), 'hora_cierre' => $desde->addHours(12)->format('H:i'),
            'orden' => 20 + $b, 'estado' => 'ACTIVO',
            'observacion' => 'Tramo de la asignación temporal de 72 horas solicitada por la propietaria para Rosa Mamani. '.$solicitud,
        ]);
        activity('planificacion_personal')->causedBy($actor)->performedOn($turnos[$b])->event('turno_temporal_creado')
            ->withProperties(['solicitud' => $solicitud, 'cod_personal' => $personal->getKey()])->log('Horario necesario para la guardia continua solicitada.');
    }
    $tramos = [];
    for ($i = 0; $i < 6; $i++) {
        $desde = $inicio->addHours($i * 12);
        $hasta = $desde->addHours(12);
        $jornada = $gestor->abrirJornada($actor, ['cod_turno' => $turnos[$i % 2]->getKey(), 'fecha_jornada' => $desde->toDateString()]);
        $asignacion = $gestor->asignarPersonal($actor, ['cod_personal' => $personal->getKey(), 'cod_area' => $area->getKey(),
            'funcion' => $personal->profesion, 'tipo_asignacion' => 'TEMPORAL'], $jornada);
        $asignacion->observacion = 'Guardia continua de 72 horas por instrucción expresa de la propietaria. '.$solicitud.'. Inicio '.$inicio->toIso8601String().'; fin '.$fin->toIso8601String().'. Tramo '.($i + 1).' de 6. No modifica residentes ni asignaciones previas.';
        $asignacion->save();
        activity('planificacion_personal')->causedBy($actor)->performedOn($asignacion)->event('guardia_continua_asignada')
            ->withProperties(['solicitud' => $solicitud, 'cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(),
                'inicio' => $desde->toIso8601String(), 'fin' => $hasta->toIso8601String(), 'horas' => 12])
            ->log('Tramo temporal de guardia continua de 72 horas asignado por solicitud confirmada.');
        $tramos[] = ['asignacion' => $asignacion->getKey(), 'jornada' => $jornada->getKey(), 'turno' => $turnos[$i % 2]->getKey(),
            'inicio' => $desde->toIso8601String(), 'fin' => $hasta->toIso8601String(), 'estado' => $asignacion->estado];
    }
    $guardadas = AsignacionPersonal::with('jornada.turno')->whereIn('cod_asignacion_personal', array_column($tramos, 'asignacion'))->get();
    if ($guardadas->count() !== 6) {
        throw new RuntimeException('No se conservaron los seis tramos.');
    }
    foreach ($tramos as $i => $tramo) {
        $fila = $guardadas->firstWhere('cod_asignacion_personal', $tramo['asignacion']);
        $desde = CarbonImmutable::parse($fila->jornada->fecha_jornada->format('Y-m-d').' '.$fila->jornada->turno->hora_inicio, 'America/La_Paz');
        $hasta = CarbonImmutable::parse($fila->jornada->fecha_jornada->format('Y-m-d').' '.$fila->jornada->turno->hora_cierre, 'America/La_Paz');
        if ($hasta->lte($desde)) { $hasta = $hasta->addDay(); }
        if (! $desde->equalTo($inicio->addHours($i * 12)) || ! $hasta->equalTo($inicio->addHours(($i + 1) * 12))
            || $fila->cod_area !== $area->getKey() || $fila->cod_personal !== $personal->getKey()) {
            throw new RuntimeException('Los tramos almacenados no conservan la continuidad exacta.');
        }
    }
    return ['solicitud' => $solicitud, 'nombre' => 'ROSA MAMANI', 'area' => $area->nombre, 'inicio' => $inicio->toIso8601String(),
        'fin' => $fin->toIso8601String(), 'horas_verificadas' => $inicio->diffInSeconds($fin)/3600, 'tramos' => $tramos,
        'auditoria' => 'Activitylog, seis asignaciones y dos horarios de tramo; actor explícito.'];
});
echo json_encode($resultado, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
