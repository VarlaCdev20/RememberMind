<?php

namespace App\Modulos\Medicacion\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\User;

final class RegistrarAdministracionFichaHttp
{
    public function ejecutar(User $actor, Residente $residente, array $datos, RegistrarAdministracionMedicacionService $servicio): string
    {
        $registro = $servicio->registrarProgramada(
            $actor, $residente->cod_residente, $datos['cod_prescripcion'],
            $datos['hora_programada'], (bool) $datos['administrado'],
            $datos['motivo_omision'] ?? null, $datos['observacion'] ?? null,
            efectoObservado: $datos['efecto_observado'] ?? null,
            dosisAdministrada: $datos['dosis_administrada'] ?? null,
            reaccionAdversa: $datos['reaccion_adversa'] ?? null,
            fechaHoraAdministracion: ! empty($datos['hora_real']) ? $datos['fecha'].' '.$datos['hora_real'].':00' : null,
            codHorario: $datos['cod_horario_prescripcion'] ?? null,
        );

        $tipoEvento = $registro->administrado
            ? 'Se registró administración de medicación'
            : 'Se registró omisión de medicación';

        return $tipoEvento;
    }
}
