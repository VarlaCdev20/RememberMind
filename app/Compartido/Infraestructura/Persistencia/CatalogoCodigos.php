<?php

namespace App\Compartido\Infraestructura\Persistencia;

use InvalidArgumentException;

/** Identidad de almacenamiento; no sustituye códigos clínicos o semánticos. */
final class CatalogoCodigos
{
    public const ENTIDADES = [
        'revisiones_evaluacion_experta' => ['cod_revision_experta', 'RVE'],
        'hechos_instantanea_experta' => ['cod_hecho_instantanea', 'HIX'],
        'evidencias_cognitivas_instantanea' => ['cod_evidencia_cognitiva_instantanea', 'ECX'],
        'soportes_sinteticos_criterio' => ['cod_soporte_sintetico', 'SSX'],
        'funcionalidad_instantanea_experta' => ['cod_funcionalidad_instantanea', 'FIX'],
        'evidencias_iadl_instantanea' => ['cod_evidencia_iadl_instantanea', 'EIX'],
        'clasificaciones_dcl_instantanea' => ['cod_clasificacion_dcl_instantanea', 'CIX'],
        'motivos_dcl_instantanea' => ['cod_motivo_dcl_instantanea', 'MIX'],
        'reglas_aplicadas_dcl_instantanea' => ['cod_regla_aplicada_dcl', 'RIX'],
        'condiciones_regla_instantanea' => ['cod_condicion_instantanea', 'COX'],
        'actividades' => ['cod_actividad', 'ACT'],
        'administraciones_medicacion' => ['cod_administracion', 'AMED'],
        'admisiones' => ['cod_admision', 'ADM'],
        'alergias' => ['cod_alergia', 'ALG'],
        'alertas' => ['cod_alerta', 'ALT'],
        'antecedentes_clinicos' => ['cod_antecedente', 'ANT'],
        'aplicaciones_instrumento' => ['cod_aplicacion', 'APL'],
        'areas' => ['cod_area', 'ARE'],
        'asignaciones_personal' => ['cod_asignacion_personal', 'ASP'],
        'asignaciones_residente_jornada' => ['cod_asignacion', 'ARJ'],
        'atenciones' => ['cod_atencion', 'ATN'],
        'camas' => ['cod_cama', 'CAM'],
        'componentes_estudio' => ['cod_componente', 'COM'],
        'condiciones_regla_experta' => ['cod_condicion_regla', 'CRE'],
        'consecuencias_regla_experta' => ['cod_consecuencia_regla', 'CSE'],
        'consentimientos' => ['cod_consentimiento', 'CNS'],
        'contactos' => ['cod_contacto', 'CTO'],
        'controles_cognitivos' => ['cod_control_cognitivo', 'CCO'],
        'controles_funcionales_iadl' => ['cod_control_iadl', 'IAD'],
        'criterios_dominios_resultado' => ['cod_criterio_dominio_resultado', 'CDR'],
        'curaciones_herida' => ['cod_curacion', 'CUR'],
        'derivaciones' => ['cod_derivacion', 'DER'],
        'diagnosticos' => ['cod_diagnostico', 'DIA'],
        'dispositivos_clinicos' => ['cod_dispositivo', 'DIS'],
        'documentos' => ['cod_documento', 'DOC'],
        'documentos_clinicos' => ['cod_documento_clinico', 'DCL'],
        'dominios_valores_expertos' => ['cod_dominio_valores', 'DVE'],
        'ejecuciones_cuidado' => ['cod_ejecucion', 'EJC'],
        'estudios_clinicos' => ['cod_estudio', 'EST'],
        'evaluacion_criterios' => ['cod_evaluacion_criterio', 'ECR'],
        'evaluaciones_condiciones_regla' => ['cod_evaluacion_condicion', 'ECO'],
        'evaluaciones_expertas' => ['cod_evaluacion_experta', 'EEX'],
        'evaluaciones_reglas' => ['cod_evaluacion_regla', 'ERG'],
        'eventos_alerta' => ['cod_evento_alerta', 'EAL'],
        'evidencias_criterio_evaluacion' => ['cod_evidencia_criterio', 'EVC'],
        'evidencias_evaluacion' => ['cod_evidencia_evaluacion', 'EVE'],
        'evidencias_soporte_condicion' => ['cod_evidencia_soporte_condicion', 'ESC'],
        'fuentes_datos_expertas' => ['cod_fuente_dato_experta', 'FDE'],
        'fotografias_institucionales' => ['cod_fotografia', 'FGI'],
        'habitaciones' => ['cod_habitacion', 'HAB'],
        'heridas' => ['cod_herida', 'HER'],
        'historial_estados_residente' => ['cod_historial_estado', 'HES'],
        'horarios_prescripcion' => ['cod_horario_prescripcion', 'HPR'],
        'incidentes' => ['cod_incidente', 'INC'],
        'indicaciones_clinicas' => ['cod_indicacion', 'IND'],
        'informes_estudio' => ['cod_informe_estudio', 'INF'],
        'instrumentos' => ['cod_instrumento', 'INS'],
        'intervenciones_cuidado' => ['cod_intervencion', 'INT'],
        'jornadas' => ['cod_jornada', 'JOR'],
        'mapeos_valores_fuente' => ['cod_mapeo_valor_fuente', 'MVF'],
        'mapeos_variables_fuente' => ['cod_mapeo_variable_fuente', 'MVR'],
        'medicamentos' => ['cod_medicamento', 'MED'],
        'mediciones_antropometricas' => ['cod_medicion', 'MAN'],
        'nodos_semanticos' => ['cod_nodo_semantico', 'NSE'],
        'notas_clinicas' => ['cod_nota', 'NOT'],
        'objetivos_signos_vitales' => ['cod_objetivo_signo', 'OSV'],
        'ocupaciones_cama' => ['cod_ocupacion', 'OCU'],
        'opciones_pregunta' => ['cod_opcion', 'OPC'],
        'participantes_actividad' => ['cod_participante', 'PAC'],
        'pases_turno' => ['cod_pase', 'PAS'],
        'personal' => ['cod_personal', 'PER'],
        'planes_cuidado' => ['cod_plan', 'PLC'],
        'preadmisiones' => ['cod_preadmision', 'PRE'],
        'preguntas_instrumento' => ['cod_pregunta', 'PRG'],
        'prescripciones' => ['cod_prescripcion', 'PRS'],
        'programaciones_cuidado' => ['cod_programacion', 'PRC'],
        'registros_conductuales' => ['cod_registro_conductual', 'RCO'],
        'registros_eliminacion' => ['cod_eliminacion', 'REL'],
        'registros_hidratacion' => ['cod_hidratacion', 'RHI'],
        'registros_ingesta' => ['cod_ingesta', 'RIN'],
        'registros_movilidad' => ['cod_movilidad', 'RMO'],
        'registros_sueno' => ['cod_registro_sueno', 'RSU'],
        'reglas_expertas' => ['cod_regla_experta', 'REX'],
        'relaciones_evidencias_evaluacion' => ['cod_relacion_evidencia', 'REE'],
        'relaciones_semanticas' => ['cod_relacion_semantica', 'RSE'],
        'residentes' => ['cod_residente', 'RES'],
        'residentes_contactos' => ['cod_residente_contacto', 'RCT'],
        'respuestas_instrumento' => ['cod_respuesta', 'RSP'],
        'resultados_criterio' => ['cod_resultado_criterio', 'RCR'],
        'resultados_estudio' => ['cod_resultado_estudio', 'RET'],
        'seguimientos_pedagogicos' => ['cod_seguimiento_pedagogico', 'SPD'],
        'seguros_residente' => ['cod_seguro', 'SEG'],
        'signos_vitales' => ['cod_signo', 'SGN'],
        'tipos_estudio_clinico' => ['cod_tipo_estudio', 'TEC'],
        'trazas_inferencia' => ['cod_traza_inferencia', 'TRI'],
        'turnos' => ['cod_turno', 'TUR'],
        'usuarios' => ['cod_usuario', 'USU'],
        'valoraciones_dolor' => ['cod_valoracion_dolor', 'VDL'],
        'valoraciones_enfermeria_preadmision' => ['cod_valoracion_enfermeria', 'VEN'],
        'valoraciones_funcionales' => ['cod_valoracion_funcional', 'VFU'],
        'valoraciones_nutricionales' => ['cod_valoracion_nutricional', 'VNU'],
        'valoraciones_psicologicas' => ['cod_valoracion_psicologica', 'VPS'],
        'valores_semanticos' => ['cod_valor_semantico', 'VSE'],
        'variables_expertas' => ['cod_variable_experta', 'VEX'],
        'versiones_modelo_experto' => ['cod_version_modelo', 'VME'],
        'visitas' => ['cod_visita', 'VIS'],
    ];

    public static function clave(string $tabla): string
    {
        return self::definicion($tabla)[0];
    }

    public static function formatear(string $tabla, int $numero): string
    {
        if ($numero < 1) {
            throw new InvalidArgumentException('El correlativo debe ser positivo.');
        }
        $codigo = 'COD_'.self::definicion($tabla)[1].'_'.str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
        if (strlen($codigo) > 20) {
            throw new \OverflowException('El correlativo supera la longitud aprobada de la clave.');
        }

        return $codigo;
    }

    private static function definicion(string $tabla): array
    {
        return self::ENTIDADES[$tabla] ?? throw new InvalidArgumentException('Entidad sin contrato de código: '.$tabla);
    }
}
