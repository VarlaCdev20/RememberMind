<?php

namespace App\Modulos\Medicacion\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\User;

final class RegistrarAdministracionFichaHttp
{
    public function ejecutar(User $actor, Residente $adulto_mayor, array $datos, RegistrarAdministracionMedicacionService $servicio): string
    {
        $registro = $servicio->registrarProgramada(
            $actor, $adulto_mayor->cod_residente, $datos['cod_prescripcion'],
            $datos['hora_programada'], (bool) $datos['administrado'],
            $datos['motivo_omision'] ?? null, $datos['observacion'] ?? null,
        );

        $tipoEvento = $registro->administrado
            ? 'Se registró administración de medicación'
            : 'Se registró omisión de medicación';

        activity('Administración Medicación')
            ->causedBy($actor)
            ->performedOn($registro)
            ->event($registro->administrado ? 'administrado' : 'omitido')
            ->withProperties([
                'cod_prescripcion' => $registro->cod_prescripcion,
                'administrado' => $registro->administrado,
                'fecha' => $registro->fecha,
            ])
            ->log("{$tipoEvento} para el adulto mayor {$adulto_mayor->cod_residente}.");

        return $tipoEvento;
    }
}
