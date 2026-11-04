<?php

declare(strict_types=1);

// Render aislado de la vista real. No carga catálogos ni historias de la aplicación.
if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'testing'
    || getenv('DB_CONNECTION') !== 'sqlite' || getenv('DB_DATABASE') !== ':memory:') {
    exit(1);
}
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\DB::listen(static function (): never {
    throw new RuntimeException('El render de la guía no puede consultar datos.');
});
$salida = (new MotorExperto\inferencia\EvaluarPetersenInstitucional)->evaluar(
    new MotorExperto\hechos\ContextoEjecucion('GUIA-TEST', 'SINTETICO-GUIA', 'VERSION-GUIA',
        new DateTimeImmutable('2026-10-31T12:00:00-04:00')), []);
echo view('apoyodecision::livewire.medico.resultados-experto.plan-captura', [
    'perfilInstrumental' => ['plan_captura' => (new App\Modulos\ApoyoDecision\Aplicacion\DTOs\PlanCapturaDcl)->desdePetersen($salida)],
    'nombresDominios' => ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas',
        'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Función visuoespacial'],
    'nombresRequisitos' => ['C01' => 'Cambio corroborado', 'C03' => 'Independencia instrumental relativa',
        'C04' => 'Evaluación diferencial', 'C05' => 'Calidad e interpretabilidad'],
])->render();
