<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Consola;

use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\EjecutorSimulacionLocal;
use DateTimeImmutable;
use Illuminate\Console\Command;

/** Entrada técnica explícita para la cohorte ficticia autorizada; jamás se programa automáticamente. */
final class SimularExpertoLocal extends Command
{
    protected $signature = 'experto:simular-local {--autor= : Cuenta superadministradora responsable} {--enfermero= : Personal de Enfermería asignado} {--corte= : Fecha ISO 8601 con zona, sin fracciones}';

    protected $description = 'Conserva cinco casos ficticios con reglas descriptivas y referencias experimentales independientes';

    public function handle(): int
    {
        $autor = $this->option('autor');
        $personal = $this->option('enfermero');
        $corte = $this->option('corte');
        if (! is_string($autor) || ! is_string($personal) || ! is_string($corte)
            || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/D', $corte)
            || ! preg_match('/^COD_USU_[0-9]{6}$/D', $autor) || ! preg_match('/^COD_PER_[0-9]{6}$/D', $personal)) {
            $this->error('Indique --autor, --enfermero y --corte ISO 8601 explícitos.');

            return self::INVALID;
        }
        $fecha = new DateTimeImmutable($corte);
        if (DateTimeImmutable::getLastErrors() !== false) {
            $this->error('La fecha de corte no es válida.');

            return self::INVALID;
        }
        $this->warn('SIMULACIÓN LOCAL: datos ficticios, sin activación ni diagnóstico clínico.');
        $salidas = (new EjecutorSimulacionLocal(new ContextoSimulacionLocal(config('database.default'))))->ejecutar($autor, $personal, $fecha);
        $this->table(['Residente', 'Caso', 'Evaluación', 'Salida experimental', 'Nueva'], array_map(fn ($r) => [
            $r['residente'], $r['caso'], $r['evaluacion'], $r['patron'] ?? implode(', ', $r['motivos']), $r['nueva'] ? 'Sí' : 'No',
        ], $salidas));

        return self::SUCCESS;
    }
}
