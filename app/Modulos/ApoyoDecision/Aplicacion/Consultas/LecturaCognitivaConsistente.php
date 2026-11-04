<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use Closure;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Misma vista SQL para las fuentes y autorización actual antes de entregar la lectura. */
final class LecturaCognitivaConsistente
{
    public function consultar(User $usuario, Residente $residente, Closure $adquirir, bool $exigirOriginales = false): mixed
    {
        $conexion = DB::connection();
        if ($conexion->transactionLevel() !== 0 || $conexion->getPdo()->inTransaction()) {
            throw new DomainException('La lectura cognitiva requiere una transacción propia para adquirir y reautorizar sus fuentes.');
        }
        if (! in_array($conexion->getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new DomainException('No se ha verificado la adquisición cognitiva para este motor de base de datos.');
        }
        [$resultado, $accesos] = $conexion->transaction(function () use ($conexion, $usuario, $residente, $adquirir, $exigirOriginales): array {
            if ($conexion->getDriverName() === 'pgsql') {
                $conexion->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            }
            [$cuenta, $caso, $accesos] = $this->autorizar($usuario, $residente, $exigirOriginales);

            return [$adquirir($cuenta, $caso), $accesos];
        });

        // Fuera de RR: no entregar datos tras una baja, pérdida de alcance o revocación.
        [, , $actuales] = $this->autorizar($usuario, $residente, $exigirOriginales);
        foreach ($accesos as $capacidad => $permitida) {
            abort_if($permitida && ! $actuales[$capacidad], 403);
        }

        return $resultado;
    }

    private function autorizar(User $usuario, Residente $residente, bool $exigirOriginales): array
    {
        $cuenta = User::query()->findOrFail($usuario->getKey());
        $caso = Residente::query()->findOrFail($residente->getKey());
        $gate = Gate::forUser($cuenta);
        $gate->authorize('consultarResultados', [EvaluacionExperta::class, $caso]);
        if ($exigirOriginales) {
            $gate->authorize('consultarOriginales', [ValoracionPetersenOriginal::class, $caso]);
        }
        $permisos = array_unique(array_merge(CapturasInstrumentalesResidente::PERMISOS_LECTURA,
            array_column(ContratoFuentesPetersen::FUENTES, 1)));
        $accesos = [];
        foreach ($permisos as $permiso) {
            $accesos[$permiso] = $cuenta->checkPermissionTo($permiso, 'web');
        }
        $accesos['policy:funcionalidad'] = $gate->allows('consultarFuncionalidad', [EvaluacionExperta::class, $caso]);
        $accesos['policy:originales_petersen'] = $gate->allows('consultarOriginales', [ValoracionPetersenOriginal::class, $caso]);

        return [$cuenta, $caso, $accesos];
    }
}
