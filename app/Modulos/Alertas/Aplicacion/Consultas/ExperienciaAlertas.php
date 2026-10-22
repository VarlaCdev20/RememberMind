<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;

final class ExperienciaAlertas
{
    public function paraUsuario(User $usuario): array
    {
        $preview = app(RolePreviewService::class)->activeRole($usuario);
        $rol = $preview ?? collect(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO', 'FAMILIAR'])
            ->first(fn ($rol) => $usuario->hasRole($rol));

        return $this->paraRol($rol);
    }

    public function paraRol(?string $rol): array
    {
        [$titulo, $descripcion, $foco] = match ($rol) {
            'ENFERMEROS' => ['Alertas y pendientes de mi turno', 'Revisa las prioridades de tus residentes, registra la actuación y conserva la continuidad del cuidado.', 'turno'],
            'MEDICO GENERAL/GERIATRA' => ['Alertas clínicas y seguimiento médico', 'Consulta el origen y la trayectoria. Documenta la decisión profesional en el expediente y los procesos médicos autorizados.', 'clinico'],
            'PSICOLOGO/A' => ['Alertas y seguimiento psicológico', 'Consulta alertas registradas y continúa en las valoraciones e instrumentos autorizados. Una alerta no equivale a un diagnóstico.', 'psicologia'],
            'NUTRICIONISTA' => ['Alertas y continuidad nutricional', 'Revisa la situación alimentaria e hídrica y documenta la valoración nutricional en su fuente clínica.', 'nutricion'],
            'FISIOTERAPEUTA' => ['Alertas y seguimiento funcional', 'Consulta el contexto y registra marcha, equilibrio, traslados y respuesta funcional desde tu competencia.', 'funcional'],
            'PEDAGOGO' => ['Alertas y acompañamiento pedagógico', 'Documenta atención, comprensión, participación y cambios observados durante las actividades.', 'pedagogia'],
            'GERENTE' => ['Supervisión institucional de alertas', 'Supervisa pendientes, responsabilidad y evolución de la gestión. Los indicadores no sustituyen una valoración clínica.', 'supervision'],
            'ADMINISTRADOR' => ['Coordinación de alertas y continuidad operativa', 'Reconoce avisos, coordina responsables y documenta el seguimiento operativo. La atención clínica corresponde al personal autorizado.', 'coordinacion'],
            'FAMILIAR' => ['Información autorizada para la familia', 'Consulta únicamente la información compartida de tus residentes vinculados. El centro clínico no publica alertas a la familia automáticamente.', 'familia'],
            default => ['Supervisión y trazabilidad de alertas', 'Consulta estados, fuentes y eventos. Las facultades técnicas no conceden escritura clínica.', 'auditoria'],
        };

        $claves = match ($foco) {
            'turno' => ['Observación y momento del hecho', 'Intervención realizada', 'Respuesta y continuidad'],
            'clinico' => ['Atención médica propia', 'Hallazgos y valoración', 'Conducta y continuidad médica'],
            'psicologia' => ['Ánimo, afecto y conducta', 'Comunicación e interacción', 'Conclusión y recomendación psicológica'],
            'nutricion' => ['Apetito y deglución', 'Asistencia y restricciones', 'Requerimiento hídrico y valoración nutricional'],
            'funcional' => ['Marcha, equilibrio y traslado', 'Fuerza, resistencia y autonomía', 'Supervisión y recomendación funcional'],
            'pedagogia' => ['Atención y comprensión', 'Ejecución y participación', 'Cambio observado y continuidad'],
            'coordinacion' => ['Reconocimiento del aviso', 'Responsable del seguimiento', 'Coordinación y cierre operativo'],
            'supervision' => ['Pendientes del equipo', 'Asignación de responsabilidad', 'Supervisión institucional'],
            'familia' => ['Vínculo autorizado', 'Información compartida', 'Contacto institucional'],
            default => ['Fuentes y autoría', 'Estados e historial', 'Supervisión técnica y operativa'],
        };

        [$icono, $bandeja, $seleccionar, $indicadores] = match ($foco) {
            'turno' => ['ph-heartbeat', 'Pendientes para el cuidado del turno', 'Continuidad del cuidado', ['urgentes', 'por_atender', 'en_atencion']],
            'clinico' => ['ph-stethoscope', 'Casos para revisión médica', 'Abrir revisión médica', ['urgentes', 'por_atender', 'en_atencion']],
            'psicologia' => ['ph-brain', 'Casos para valoración psicológica', 'Revisar observación psicológica', ['por_atender', 'en_atencion', 'cerradas']],
            'nutricion' => ['ph-bowl-food', 'Casos para valoración nutricional', 'Revisar contexto nutricional', ['por_atender', 'urgentes', 'todas']],
            'funcional' => ['ph-person-simple-walk', 'Casos para seguimiento funcional', 'Consultar autonomía y movilidad', ['por_atender', 'en_atencion', 'cerradas']],
            'pedagogia' => ['ph-book-open', 'Casos para acompañamiento pedagógico', 'Revisar participación y actividad', ['por_atender', 'en_atencion', 'todas']],
            'coordinacion' => ['ph-clipboard-text', 'Cola de coordinación diaria', 'Revisar coordinación', ['sin_responsable', 'fuera_plazo', 'por_atender']],
            'supervision' => ['ph-users-three', 'Alertas del equipo', 'Supervisar el caso', ['urgentes', 'sin_responsable', 'fuera_plazo']],
            'familia' => ['ph-house-line', 'Información compartida', 'Consultar información autorizada', []],
            default => ['ph-git-branch', 'Registros para inspección', 'Inspeccionar relaciones', ['todas', 'anteriores', 'cerradas']],
        };

        return compact('rol', 'titulo', 'descripcion', 'foco', 'claves', 'icono', 'bandeja', 'seleccionar', 'indicadores');
    }
}
