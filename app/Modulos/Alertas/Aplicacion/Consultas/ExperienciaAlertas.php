<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;

final class ExperienciaAlertas
{
    public function paraUsuario(User $usuario): array
    {
        $preview = app(RolePreviewService::class)->activeRole($usuario);
        $rol = $preview ?? collect(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'GERENTE', 'ADMINISTRADOR', 'SUPERADMINISTRADOR'])
            ->first(fn ($rol) => $usuario->hasRole($rol));
        [$titulo, $descripcion, $foco] = match ($rol) {
            'ENFERMEROS' => ['Alertas y pendientes de mi turno', 'Revisa las prioridades de tus residentes, registra la actuación y conserva la continuidad del cuidado.', 'turno'],
            'MEDICO GENERAL/GERIATRA' => ['Alertas clínicas y seguimiento médico', 'Consulta el origen y la trayectoria. Documenta la decisión profesional en el expediente y los procesos médicos autorizados.', 'clinico'],
            'PSICOLOGO/A' => ['Alertas y seguimiento psicológico', 'Consulta alertas registradas y continúa en las valoraciones e instrumentos autorizados. Una alerta no equivale a un diagnóstico.', 'psicologia'],
            'GERENTE' => ['Supervisión institucional de alertas', 'Supervisa pendientes, responsabilidad y evolución de la gestión. Los indicadores no sustituyen una valoración clínica.', 'supervision'],
            'ADMINISTRADOR' => ['Coordinación de alertas y continuidad operativa', 'Reconoce avisos, coordina responsables y documenta el seguimiento operativo. La atención clínica corresponde al personal autorizado.', 'coordinacion'],
            default => ['Supervisión y trazabilidad de alertas', 'Consulta estados, fuentes y eventos. Las facultades técnicas no conceden escritura clínica.', 'auditoria'],
        };

        return compact('rol', 'titulo', 'descripcion', 'foco');
    }
}
