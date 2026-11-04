<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

/** Campos existentes de los registradores clínicos; no añade catálogos ni reglas médicas. */
final class CamposRegistroEspecialidad
{
    public function paraRol(?string $rol): ?array
    {
        $definicion = match ($rol) {
            'MEDICO GENERAL/GERIATRA' => ['nota', 'Evolución médica', 'notas_clinicas.crear', [
                'tipo_nota' => ['Tipo de nota', 50], 'contenido' => ['Hallazgos, valoración y conducta médica', 'texto'],
            ]],
            'PSICOLOGO/A' => ['psicologia', 'Valoración psicológica', 'valoraciones_psicologicas.crear', [
                'estado_animo' => ['Estado de ánimo', 50], 'afecto' => ['Afecto', 50], 'ansiedad' => ['Ansiedad', 50], 'apatia' => ['Apatía', 50],
                'percepcion' => ['Percepción', 'texto'], 'conducta' => ['Conducta observada', 'texto'],
                'comunicacion' => ['Comunicación', 'texto'], 'interaccion_social' => ['Interacción social', 'texto'],
                'impresion_cognitiva' => ['Impresión cognitiva profesional', 'texto'],
                'conclusion' => ['Conclusión profesional', 'texto'], 'recomendacion' => ['Recomendación y continuidad', 'texto'],
            ]],
            'NUTRICIONISTA' => ['nutricion', 'Valoración nutricional', 'valoraciones_nutricionales.crear', [
                'estado_nutricional' => ['Estado nutricional valorado', 60], 'apetito' => ['Apetito', 40],
                'deglucion' => ['Deglución', 40], 'riesgo_desnutricion' => ['Riesgo de desnutrición valorado', 40],
                'necesidad_asistencia' => ['Asistencia para alimentarse', 40],
                'requerimiento_hidrico' => ['Requerimiento hídrico (mL por día)', 'decimal'],
                'restricciones_alimentarias' => ['Restricciones alimentarias', 'texto'],
                'conclusion' => ['Conclusión nutricional', 'texto'], 'recomendacion' => ['Recomendación nutricional', 'texto'],
            ]],
            'FISIOTERAPEUTA' => ['funcional', 'Valoración funcional', 'valoraciones_funcionales.crear', [
                'marcha' => ['Marcha', 40], 'equilibrio' => ['Equilibrio', 40], 'traslado' => ['Traslado', 40],
                'fuerza_funcional' => ['Fuerza funcional', 40], 'resistencia' => ['Resistencia', 40],
                'alimentacion_autonoma' => ['Autonomía para alimentarse', 30], 'bano_autonomo' => ['Autonomía para bañarse', 30],
                'vestido_autonomo' => ['Autonomía para vestirse', 30], 'higiene_autonoma' => ['Autonomía para la higiene', 30],
                'continencia' => ['Continencia', 30], 'movilidad_autonoma' => ['Autonomía para desplazarse', 30],
                'necesita_supervision' => ['Necesidad de supervisión', 'booleano'], 'nivel_dependencia' => ['Nivel de dependencia valorado', 40],
                'conclusion' => ['Conclusión funcional', 'texto'], 'recomendacion' => ['Recomendación funcional', 'texto'],
            ]],
            'PEDAGOGO' => ['pedagogia', 'Seguimiento pedagógico', 'seguimientos_pedagogicos.crear', [
                'atencion' => ['Atención durante la actividad', 40], 'comprension_instrucciones' => ['Comprensión de instrucciones', 40],
                'ejecucion_tarea' => ['Ejecución de la tarea', 40], 'reconocimiento' => ['Reconocimiento observado', 40],
                'orientacion' => ['Orientación observada', 40], 'participacion' => ['Participación', 40], 'interaccion' => ['Interacción', 40],
                'cambio_desempeno' => ['Cambio de desempeño observado', 'booleano'], 'observacion' => ['Observación y continuidad pedagógica', 'texto'],
            ]],
            default => null,
        };
        if (! $definicion) {
            return null;
        }
        [$tipo, $titulo, $permiso, $campos] = $definicion;

        $secciones = match ($tipo) {
            'nota' => ['Evolución y decisión médica' => ['tipo_nota', 'contenido']],
            'psicologia' => [
                'Ánimo y afectividad' => ['estado_animo', 'afecto', 'ansiedad', 'apatia'],
                'Conducta y relación' => ['percepcion', 'conducta', 'comunicacion', 'interaccion_social', 'impresion_cognitiva'],
                'Conclusión y continuidad' => ['conclusion', 'recomendacion'],
            ],
            'nutricion' => [
                'Alimentación y estado nutricional' => ['estado_nutricional', 'apetito', 'deglucion', 'riesgo_desnutricion'],
                'Apoyo, hidratación y restricciones' => ['necesidad_asistencia', 'requerimiento_hidrico', 'restricciones_alimentarias'],
                'Conclusión y continuidad' => ['conclusion', 'recomendacion'],
            ],
            'funcional' => [
                'Movilidad y capacidad funcional' => ['marcha', 'equilibrio', 'traslado', 'fuerza_funcional', 'resistencia'],
                'Autonomía en el cuidado diario' => ['alimentacion_autonoma', 'bano_autonomo', 'vestido_autonomo', 'higiene_autonoma', 'continencia', 'movilidad_autonoma', 'necesita_supervision', 'nivel_dependencia'],
                'Conclusión y continuidad' => ['conclusion', 'recomendacion'],
            ],
            'pedagogia' => [
                'Observación durante la actividad' => ['atencion', 'comprension_instrucciones', 'ejecucion_tarea', 'reconocimiento', 'orientacion', 'participacion', 'interaccion'],
                'Cambio y continuidad' => ['cambio_desempeno', 'observacion'],
            ],
        };

        return compact('tipo', 'titulo', 'permiso', 'campos', 'secciones') + [
            'requiereAtencion' => $tipo !== 'pedagogia',
            'obligatorios' => $tipo === 'nota' ? ['tipo_nota', 'contenido'] : [],
        ];
    }
}
