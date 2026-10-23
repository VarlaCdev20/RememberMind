<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAsignacionesJornada;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$actor = User::where('correo', 'admincasaamandita@gmail.com')->sole();
$cuenta = User::where('correo', 'enfermeria@remembermind.com')->sole();
$rosa = Personal::where('cod_usuario', $cuenta->getKey())->sole();
abort_unless($actor->estado === 'ACTIVO' && $actor->can('turnos.asignar')
    && $actor->hasRole('SUPERADMINISTRADOR') && $cuenta->estado === 'ACTIVO'
    && $cuenta->hasRole('ENFERMEROS') && $rosa->estado === 'ACTIVO'
    && $rosa->getKey() === 'COD_PER_000003', 403);

$fuentes = AsignacionResidenteJornada::with('residente')->where('cod_personal', $rosa->getKey())
    ->where('cod_jornada', 'COD_JOR_000582')->whereIn('estado', ['ACTIVA', 'ACTIVO'])->get();
$esperados = ['COD_RES_000007', 'COD_RES_000010', 'COD_RES_000019', 'COD_RES_000028', 'COD_RES_000030'];
if ($fuentes->pluck('cod_residente')->sort()->values()->all() !== $esperados) {
    throw new RuntimeException('El conjunto anterior requiere revisión.');
}
$horario = json_decode(file_get_contents(__DIR__.'/rosa-72h-20261019-resultado.json'), true, flags: JSON_THROW_ON_ERROR);
$destinos = $horario['tramos'];
$validar = function () use ($fuentes, $destinos, $rosa): void {
    foreach ($fuentes as $fuente) {
        $residente = Residente::query()->lockForUpdate()->findOrFail($fuente->cod_residente);
        if (! in_array($residente->estado, ['ADMITIDO', 'ACTIVO', 'EST_001'], true)
            || ! in_array($fuente->nivel_supervision, ['MINIMO', 'ESTANDAR', 'INTENSIVO', 'CRITICO'], true)) {
            throw new RuntimeException('Estado o supervisión anterior no válidos.');
        }
    }
    foreach ($destinos as $tramo) {
        $jornada = Jornada::query()->lockForUpdate()->findOrFail($tramo['jornada']);
        if ($jornada->cod_turno !== $tramo['turno']
            || ! in_array($jornada->estado, ['ABIERTA', 'ACTIVA', 'EN_CURSO'], true)
            || Jornada::where('cod_turno', $jornada->cod_turno)->whereDate('fecha_jornada', $jornada->fecha_jornada)->count() !== 1
            || ! $rosa->asignaciones()->where('cod_jornada', $jornada->getKey())->whereIn('estado', ['ACTIVA', 'ACTIVO'])->exists()) {
            throw new RuntimeException('La jornada destino no coincide con la guardia de Rosa.');
        }
    }
};

if (! in_array('--aplicar', $argv, true)) {
    DB::transaction($validar);
    echo json_encode(['residentes' => $fuentes->map(fn ($a) => ['codigo' => $a->cod_residente,
        'nombre' => $a->residente->nombre_completo, 'supervision' => $a->nivel_supervision]),
        'jornadas_destino' => count($destinos), 'asignaciones_previstas' => $fuentes->count() * count($destinos)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$creadas = DB::transaction(function () use ($validar, $actor, $cuenta, $rosa, $fuentes, $destinos): int {
    Personal::query()->lockForUpdate()->findOrFail($rosa->getKey());
    $validar();
    $creadas = 0;
    foreach ($destinos as $tramo) {
        $jornada = Jornada::findOrFail($tramo['jornada']);
        foreach ($fuentes as $fuente) {
            $existentes = AsignacionResidenteJornada::where('cod_jornada', $jornada->getKey())
                ->where('cod_residente', $fuente->cod_residente)->whereIn('estado', ['ACTIVA', 'ACTIVO'])->lockForUpdate()->get();
            if ($existentes->isNotEmpty()) {
                if ($existentes->count() === 1 && $existentes->first()->cod_personal === $rosa->getKey()
                    && $existentes->first()->nivel_supervision === $fuente->nivel_supervision) {
                    continue;
                }
                throw new RuntimeException('Existe una asignación incompatible en la jornada destino.');
            }
            app(GestionarAsignacionesJornada::class)->guardar($actor, [
                'codResidente' => $fuente->cod_residente,
                'codTurno' => $jornada->cod_turno,
                'codEnfermero' => $cuenta->getKey(),
                'fechaInicio' => $jornada->fecha_jornada->format('Y-m-d'),
                'nivelSupervision' => $fuente->nivel_supervision,
                'motivoAsignacion' => 'Solicitud expresa del propietario: devolver a Rosa Mamani sus residentes durante la guardia continua de 72 horas. Referencia anterior '.$fuente->getKey().'; se conserva supervisión registrada. ROSA-RESIDENTES-72H-20261019.',
            ]);
            $creadas++;
        }
        $guardadas = AsignacionResidenteJornada::where('cod_personal', $rosa->getKey())->where('cod_jornada', $jornada->getKey())
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])->get();
        if ($guardadas->pluck('cod_residente')->sort()->values()->all() !== $fuentes->pluck('cod_residente')->sort()->values()->all()) {
            throw new RuntimeException('La comprobación de residentes por jornada falló.');
        }
    }
    return $creadas;
});

$consulta = app(TurnoEnfermeriaService::class);
$actuales = $consulta->obtenerPacientesAsignadosIds($cuenta);
sort($actuales);
if ($actuales !== $esperados) {
    throw new RuntimeException('Guardado realizado; la consulta actual requiere revisión.');
}
foreach ($esperados as $codigo) {
    if (! $consulta->esPacienteAsignado($codigo, $cuenta)) {
        throw new RuntimeException('Guardado realizado; el contexto actual de atención requiere revisión.');
    }
}
echo json_encode(['creadas' => $creadas, 'residentes' => $fuentes->map(fn ($a) => $a->residente->nombre_completo),
    'jornadas_cubiertas' => count($destinos), 'visibles_en_turno_actual' => count($actuales),
    'inicio' => $horario['inicio'], 'fin' => $horario['fin'], 'auditoria' => 'Activitylog, actor explícito'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
