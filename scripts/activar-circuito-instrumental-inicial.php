<?php

declare(strict_types=1);

use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PreparacionCircuitoInstrumental;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\PerfilesInstrumentalesIniciales;
use Database\Seeders\CircuitoInstrumentalInicialSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MotorExperto\instrumentos\RegistroMetodosInstrumentales;

// CLI explícita para la configuración local vigente. Sin migraciones, historias ni usuarios de respaldo.
$modo = $argv[1] ?? '--comprobar';
if (PHP_SAPI !== 'cli' || ! in_array($modo, ['--comprobar', '--activar'], true) || count($argv) > 2) {
    fwrite(STDERR, "Uso: php scripts/activar-circuito-instrumental-inicial.php [--comprobar|--activar]\n");
    exit(1);
}
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$conexion = DB::connection();
if (! in_array($conexion->getDriverName(), ['pgsql', 'sqlite'], true)) {
    throw new RuntimeException('El circuito solo se instala en un motor soportado.');
}
foreach (['instrumentos', 'preguntas_instrumento', 'opciones_pregunta', 'aplicaciones_instrumento', 'respuestas_instrumento'] as $tabla) {
    if (! Schema::hasTable($tabla)) {
        throw new RuntimeException('Falta el esquema institucional de instrumentos. No se crean tablas desde este comando.');
    }
}
$informe = $conexion->transaction(function () use ($modo): array {
    if ($modo === '--comprobar' && DB::getDriverName() === 'pgsql') {
        DB::statement('SET TRANSACTION READ ONLY');
    }
    $antes = ['aplicaciones' => DB::table('aplicaciones_instrumento')->count(), 'respuestas' => DB::table('respuestas_instrumento')->count()];
    if ($modo === '--activar') {
        app(CircuitoInstrumentalInicialSeeder::class)->run();
    }
    $estado = app(PerfilesInstrumentalesIniciales::class)->estado();
    $despues = ['aplicaciones' => DB::table('aplicaciones_instrumento')->count(), 'respuestas' => DB::table('respuestas_instrumento')->count()];
    if ($antes !== $despues) {
        throw new RuntimeException('La activación no debe crear aplicaciones ni respuestas de residentes.');
    }
    if ($modo === '--activar' && count($estado) !== 3) {
        throw new RuntimeException('La instalación no confirmó los tres perfiles; se revierte toda la operación.');
    }

    return ['modo' => $modo, 'fecha' => now()->toIso8601String(), 'perfiles' => $estado,
        'tablas_creadas' => 0, 'permisos_creados' => 0, 'datos_clinicos_modificados' => false,
        'circuito' => (new PreparacionCircuitoInstrumental)->resumir(config('sistema_experto.perfiles_instrumentales', []),
            (new RegistroMetodosInstrumentales)->metodos(), $estado)];
});
echo json_encode($informe, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
