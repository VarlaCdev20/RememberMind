<?php

namespace App\Modulos\Alertas\Presentacion\Soporte;

/** Solo compone datos de presentación previamente autorizados; no consulta ni persiste. */
final class ComponerVistaCasoAlerta
{
    public function componer(?array $traza, string $foco): array
    {
        [$tipo, $titulo, $pregunta, $accion] = match ($foco) {
            'turno' => ['cronologia', 'Continuidad del cuidado', '¿Qué ocurrió y qué actuaciones están guardadas?', 'Revisar intervención y seguimiento'],
            'clinico' => ['contraste', 'Evidencia y revisión médica', '¿Qué respalda el aviso y qué nota clínica está disponible?', 'Abrir nota clínica'],
            'psicologia' => ['red', 'Bienestar y relaciones', '¿Qué observaciones de ánimo, conducta e interacción constan?', 'Abrir valoración psicológica'],
            'nutricion' => ['panel-nutricional', 'Alimentación y apoyo hídrico', '¿Qué valoración alimentaria e hídrica está guardada?', 'Abrir valoración nutricional'],
            'funcional' => ['matriz', 'Capacidades y autonomía', '¿Qué capacidad, asistencia y supervisión se ha descrito?', 'Abrir valoración funcional'],
            'pedagogia' => ['recorrido', 'Participación en la actividad', '¿Qué participación, desempeño y cambios se documentaron?', 'Abrir seguimiento pedagógico'],
            'coordinacion' => ['tablero', 'Situación de la coordinación', '¿En qué situación está el aviso y quién continúa su seguimiento?', 'Abrir coordinación'],
            'supervision' => ['arbol', 'Responsabilidad y equipo', '¿Quién está a cargo y qué vínculos profesionales constan?', 'Consultar responsabilidad'],
            default => ['procedencia', 'Fuente, autoría e historial', '¿Qué registros respaldan este caso?', 'Inspeccionar detalle'],
        };
        $nodos = collect($traza['nodos'] ?? []);
        $origen = $nodos->where('grupo', 'origen')->values()->all();
        $caso = $nodos->firstWhere('id', 'alerta');
        $responsable = $nodos->firstWhere('id', 'responsable');
        $estado = $nodos->firstWhere('id', 'estado');
        $contexto = $nodos->firstWhere('id', 'contexto-profesional');
        $autor = $nodos->firstWhere('id', 'autor-profesional');
        $atencion = $nodos->firstWhere('id', 'atencion-profesional');
        $secciones = $nodos->filter(fn ($n) => isset($n['seccion']))->sortBy('seccion')->keyBy('seccion')->all();
        $eventos = $nodos->where('grupo', 'eventos')->values()->all();
        $especialidades = $nodos->filter(fn ($n) => str_starts_with($n['id'], 'especialidad-'))->values()->all();
        $etapas = match ($foco) {
            'turno' => ['Hecho y registro de origen', 'Actuaciones documentadas', 'Responsabilidad de continuidad'],
            'clinico' => ['Fuente del aviso', 'Nota clínica del residente'],
            'psicologia' => ['Ánimo y afectividad', 'Conducta y relación', 'Conclusión y continuidad'],
            'nutricion' => ['Alimentación y estado nutricional', 'Apoyo, hidratación y restricciones', 'Conclusión y continuidad'],
            'funcional' => ['Movilidad y capacidad funcional', 'Autonomía en el cuidado diario', 'Conclusión y continuidad'],
            'pedagogia' => ['Observación durante la actividad', 'Cambio y continuidad'],
            'coordinacion' => ['Por atender', 'En atención', 'Por cerrar', 'Finalizada', 'Registro anterior'],
            'supervision' => ['Responsable del seguimiento', 'Caso supervisado', 'Vínculos profesionales'],
            default => ['Caso consultado', 'Registro de origen', 'Responsabilidad y eventos'],
        };
        $estadoCodigo = $traza['estadoCodigo'] ?? '';
        $columna = match ($estadoCodigo) {
            'ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE' => 0,
            'EN_ATENCION' => 1, 'ATENDIDA' => 2, 'CERRADA', 'ANULADA' => 3, default => 4,
        };

        return compact('tipo', 'titulo', 'pregunta', 'accion', 'foco', 'origen', 'caso', 'responsable', 'estado',
            'contexto', 'autor', 'atencion', 'secciones', 'eventos', 'especialidades', 'etapas', 'columna') + ['conCaso' => $traza !== null];
    }
}
