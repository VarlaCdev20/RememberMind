<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Models\User;
use LogicException;

class AccesoClinicoTemporalService
{
    private const RECURSOS_CLINICOS = [
        'atenciones', 'notas_clinicas', 'antecedentes_clinicos', 'diagnosticos',
        'alergias', 'signos_vitales', 'valoraciones_dolor', 'mediciones_antropometricas',
        'estudios_clinicos', 'resultados_estudio', 'informes_estudio', 'documentos_clinicos',
        'derivaciones', 'indicaciones_clinicas', 'incidentes', 'controles_cognitivos',
        'registros_conductuales', 'registros_sueno', 'registros_ingesta',
        'registros_hidratacion', 'registros_eliminacion', 'registros_movilidad',
        'heridas', 'curaciones_herida', 'pases_turno', 'planes_cuidado',
        'intervenciones_cuidado', 'programaciones_cuidado', 'ejecuciones_cuidado',
        'prescripciones', 'horarios_prescripcion', 'administraciones_medicacion',
        'aplicaciones_instrumento', 'valoraciones_psicologicas',
        'valoraciones_nutricionales', 'valoraciones_funcionales',
        'seguimientos_pedagogicos', 'valoracion_enfermeria',
    ];

    public function esPermisoDeEscrituraClinica(string $permiso): bool
    {
        if (str_ends_with($permiso, '.ver')) {
            return false;
        }

        foreach (self::RECURSOS_CLINICOS as $recurso) {
            if (str_starts_with($permiso, $recurso.'.')) {
                return true;
            }
        }

        return false;
    }

    public function sustituyeRol(?User $usuario): bool
    {
        if (! $usuario?->hasRole('SUPERADMINISTRADOR')
            || ! config('remembermind.superadmin_clinical_write', false)
            || app(RolePreviewService::class)->isActive($usuario)) {
            return false;
        }

        try {
            app(ContextoClinicoService::class)->personalActivo($usuario);

            return true;
        } catch (LogicException) {
            return false;
        }
    }

    /** @param array<int, string> $roles */
    public function tieneRol(?User $usuario, array $roles): bool
    {
        return $usuario !== null
            && ($usuario->hasAnyRole($roles) || $this->sustituyeRol($usuario));
    }
}
