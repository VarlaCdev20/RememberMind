<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\Residente;
use App\Models\User;
use App\Models\EvaluacionExperta;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;

/** Preparación backend autorizada; no es una operación de guardado ni un endpoint para descargar historia. */
final readonly class PrepararEvaluacionInstitucional
{
    public function __construct(private ConsultarConocimientoCognitivo $instrumental, private ConsultarPetersenInstitucional $petersen) {}

    public function preparar(User $usuario, Residente $residente, CarbonImmutable $corte): InstantaneaEvaluacionInstitucional
    {
        $conexion = DB::connection();
        if ($conexion->transactionLevel() !== 0 || $conexion->getPdo()->inTransaction()) {
            throw new DomainException('La preparación compuesta requiere una transacción propia para adquirir y reautorizar sus fuentes.');
        }
        if (! in_array($conexion->getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new DomainException('No se ha verificado la adquisición compuesta para este motor de base de datos.');
        }
        [$instantanea, $accesos] = $conexion->transaction(function () use ($conexion, $usuario, $residente, $corte): array {
            if ($conexion->getDriverName() === 'pgsql') {
                // Antes del primer SELECT: los SAVEPOINT de ambas consultas heredan esta vista.
                $conexion->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            }
            $accesos = $this->autorizar($usuario, $residente);
            $instrumental = $this->instrumental->preparar($usuario, $residente, $corte);
            $petersen = $this->petersen->preparar($usuario, $residente, $corte, $instrumental->contenido['contexto']['version'], $instrumental);

            return [InstantaneaEvaluacionInstitucional::crear($instrumental, $petersen), $accesos];
        });

        // Fuera de la instantánea: una revocación concurrente no debe quedar oculta por RR.
        $actuales = $this->autorizar($usuario, $residente);
        foreach ($accesos as $capacidad => $permitida) {
            abort_if($permitida && ! $actuales[$capacidad], 403);
        }

        return $instantanea;
    }

    /** Solo comprueba capacidades existentes; no concede permisos ni adquiere fuentes clínicas. */
    private function autorizar(User $usuario, Residente $residente): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        $gate = Gate::forUser($usuario);
        $gate->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $gate->authorize('consultarOriginales', [ValoracionPetersenOriginal::class, $residente]);
        $permisos = array_unique(array_merge(CapturasInstrumentalesResidente::PERMISOS_LECTURA,
            array_column(ContratoFuentesPetersen::FUENTES, 1)));
        $accesos = [];
        foreach ($permisos as $permiso) {
            $accesos[$permiso] = $usuario->checkPermissionTo($permiso, 'web');
        }
        $accesos['policy:funcionalidad'] = $gate->allows('consultarFuncionalidad', [EvaluacionExperta::class, $residente]);

        return $accesos;
    }
}
