<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$personal = App\Models\Personal::findOrFail('COD_PER_000003');
$servicio = app(App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService::class);
$resultado = json_decode(file_get_contents(__DIR__.'/rosa-72h-20261019-resultado.json'), true, flags: JSON_THROW_ON_ERROR);
$verificados = [];
foreach ($resultado['tramos'] as $tramo) {
    $asignacion = App\Models\AsignacionPersonal::with('jornada.turno')->findOrFail($tramo['asignacion']);
    if ($asignacion->estado !== 'ACTIVA' || $asignacion->cod_personal !== $personal->getKey()
        || $asignacion->cod_jornada !== $tramo['jornada']) {
        throw new RuntimeException('La asignación guardada no coincide.');
    }
    $medio = Carbon\Carbon::parse($tramo['inicio'])->addHours(6);
    $jornada = $servicio->resolverJornadaActual($personal, $medio);
    if ($jornada?->getKey() !== $tramo['jornada']) {
        throw new RuntimeException('La jornada no se reconoce como vigente en su ventana.');
    }
    $verificados[] = $jornada->getKey();
}
$actual = $servicio->resolverJornadaActual($personal);
echo json_encode(['tramos_vigentes_verificados' => $verificados, 'jornada_actual' => $actual?->getKey(),
    'inicio' => $resultado['inicio'], 'fin' => $resultado['fin'], 'horas' => $resultado['horas_verificadas']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
