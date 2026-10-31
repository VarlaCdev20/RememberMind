<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Presentación histórica exclusiva de regresión; fuera del autoload App del producto. */
final class PresentarInformeG09Prueba
{
    public function presentarG09Prueba(array $informe): array
    {
        $c = DB::connection();
        if (! app()->environment('testing') || ! BaseTecnicaDesechable::permite(
            $c->getDriverName(), $c->getDatabaseName(), $c->getConfig('username'), $c->getConfig('host'))
            || ($informe['uso'] ?? null) !== 'PRUEBA_TECNICA_NO_CLINICA'
            || ($informe['version'] ?? null) !== 'G09-EXP-20261017'
            || ! in_array($informe['criterio'] ?? null, ['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], true)) {
            throw new DomainException('La presentación G09 requiere un informe técnico aislado; no admite residentes asistenciales.');
        }
        $texto = self::estado($informe['estado_tecnico'] ?? '');
        if ($texto === null) {
            throw new DomainException('Estado G09 sin contrato de presentación.');
        }

        return $texto + ['estado_original' => $informe['estado_tecnico'], 'uso' => $informe['uso'],
            'publicable' => false, 'limites' => 'Datos sintéticos. Sin diagnóstico, gravedad, pronóstico ni clasificación Petersen.'];
    }

    private static function estado(string $codigo): ?array
    {
        return match ($codigo) {
            'SIN_EVIDENCIA_ADMISIBLE' => ['estado' => 'Sin evidencia admisible', 'alcance' => 'La prueba experimental no dispone de evidencia admisible para emitir un hallazgo.'],
            'SOLO_SENAL_CONTEXTO' => ['estado' => 'Solo señal de contexto', 'alcance' => 'La prueba conserva información preliminar de contexto; no acredita un hallazgo del área.'],
            'EVALUABLE_SIN_SOPORTE' => ['estado' => 'Evaluable sin soporte positivo', 'alcance' => 'La tarea experimental es interpretable, pero no conserva soporte de una regla positiva. No equivale a preservación cognitiva.'],
            'HALLAZGO_ACOTADO' => ['estado' => 'Hallazgo experimental acotado', 'alcance' => 'El hallazgo se limita al componente y a los soportes de la prueba técnica. No constituye un diagnóstico.'],
            'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION' => ['estado' => 'Múltiples hallazgos pendientes de resolución', 'alcance' => 'Se conservan varias rutas experimentales; ninguna se prioriza automáticamente ni se resume como un resultado unitario.'],
            'EVIDENCIA_CONTRADICTORIA' => ['estado' => 'Evidencia contradictoria', 'alcance' => 'Las fuentes de la prueba experimental no permiten una conclusión consistente. Se conserva la contradicción sin elegir un resultado.'],
            default => null,
        };
    }
}
