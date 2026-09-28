<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\Area;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use LogicException;

class AutorizacionClinicaService
{
    public function __construct(
        private readonly ContextoClinicoService $contexto,
        private readonly TurnoEnfermeriaService $turnos,
    ) {}

    /** @param array<int, string> $rolesPermitidos */
    public function autorizarMutacion(
        ?User $usuario,
        Residente $residente,
        string $permiso,
        array $rolesPermitidos,
    ): Personal {
        abort_unless($usuario && strtoupper(trim((string) $usuario->estado)) === 'ACTIVO', 403,
            'La operación clínica requiere una cuenta de usuario activa.');
        abort_unless($usuario->hasAnyRole($rolesPermitidos), 403,
            'La profesión del usuario no está autorizada para esta operación clínica.');
        abort_unless($usuario->can($permiso), 403,
            'No cuenta con el permiso requerido para esta operación clínica.');
        abort_unless(in_array(strtoupper((string) $residente->estado), ['ACTIVO', 'ADMITIDO', 'EST_001'], true), 403,
            'El residente no se encuentra admitido.');

        if ($usuario->hasRole('ENFERMEROS')) {
            $this->turnos->autorizarMutacionEnfermeria($residente, $permiso, $usuario);
        }

        try {
            $personal = $this->contexto->personalActivo($usuario);
            $this->contexto->areaAtencion($personal);
        } catch (LogicException $excepcion) {
            abort(403, $excepcion->getMessage());
        }

        return $personal;
    }

    public function areaActiva(Personal $personal): Area
    {
        try {
            return $this->contexto->areaAtencion($personal);
        } catch (LogicException $excepcion) {
            abort(403, $excepcion->getMessage());
        }
    }
}
