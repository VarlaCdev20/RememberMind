<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

final class MotivoPetersenVisible
{
    private const CONDICIONES = [
        'alcance_dominio' => 'qué área cognitiva se evaluó',
        'contexto_interpretado' => 'las condiciones en que se interpretó la medición',
        'cambio_respecto_previo' => 'el cambio respecto del funcionamiento previo',
        'corroboracion_independiente' => 'una corroboración independiente del cambio',
        'revision_integrada' => 'la revisión conjunta de la autonomía funcional',
        'oportunidad_adecuada' => 'la oportunidad real de realizar la actividad',
        'atribucion_revisada' => 'la causa de la ayuda funcional',
        'revision_explicita' => 'la revisión de posibles causas alternativas',
        'factores_pertinentes_revisados' => 'los factores clínicos pertinentes',
        'suficiencia_conjunto' => 'la suficiencia del conjunto de evidencias',
        'condiciones_revisadas' => 'las condiciones de aplicación',
        'exclusiones_revisadas' => 'las evidencias excluidas y sus motivos',
    ];

    private const TEXTOS = [
        'ABSTENCION_CONTRADICCION' => 'Hay interpretaciones contradictorias que deben aclararse.',
        'ABSTENCION_EVALUACION_DIFERENCIAL' => 'La revisión de posibles causas alternativas no permite completar la clasificación.',
        'ABSTENCION_INFORMACION_INSUFICIENTE' => 'Faltan valoraciones originales o condiciones de interpretación suficientes.',
        'ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE' => 'No hay una versión vigente del conocimiento para establecer este patrón.',
        'ABSTENCION_SIN_REGLA_PETERSEN_HABILITADA' => 'La regla correspondiente no está habilitada.',
        'SIN_PATRON_CLASIFICABLE_POR_REQUISITO' => 'Un requisito no está acreditado; no se establece un patrón orientativo.',
        'SIN_VALORACION_ORIGINAL_ADMISIBLE' => 'No hay una valoración original admisible para esta área o requisito.',
        'SIN_CINCO_DOMINIOS_INTERPRETABLES' => 'Falta una interpretación válida de una o más áreas cognitivas.',
        'SIN_PERFIL_OBJETIVO_SUFICIENTE' => 'Faltan mediciones objetivas suficientes de las áreas cognitivas.',
        'SIN_SOPORTES_ORIGINALES' => 'La valoración no tiene registros originales que la respalden.',
        'SIN_MEDICION_OBJETIVA_DEL_DOMINIO' => 'Falta una medición cognitiva objetiva original para esta área.',
        'MEDICION_SIN_CAPTURA_ORIGINAL_COMPATIBLE' => 'La medición no puede vincularse con una aplicación original completa y compatible.',
        'MEDICION_NO_INTERPRETABLE' => 'La medición original no pudo interpretarse con el método registrado.',
        'APLICACION_FUERA_DEL_ALCANCE_60_MAS' => 'La referencia de la medición no corresponde a la población mayor de 60 años.',
        'SIN_CAMBIO_CORROBORADO_INDEPENDIENTEMENTE' => 'El cambio cognitivo no cuenta con corroboración de un episodio independiente.',
        'SIN_IADL_ORIGINAL' => 'Falta una valoración original de actividades instrumentales de la vida diaria.',
        'IADL_SIN_CONTEXTO_INTERPRETABLE' => 'La valoración funcional no documenta contexto suficiente para interpretar la autonomía.',
        'IADL_CON_DATOS_DOCUMENTALES_INCOMPATIBLES' => 'Los datos de desempeño y ayuda funcional se contradicen.',
        'SIN_REVISION_ORIGINAL_PERTINENTE' => 'Falta la revisión original requerida para este requisito.',
        'REVISION_ORIGINAL_SIN_CONTENIDO' => 'La revisión original no contiene una justificación verificable.',
        'SIN_CONCLUSION_DIFERENCIAL_EXPLICITA' => 'Falta una conclusión explícita sobre posibles causas alternativas.',
        'DOCUMENTO_ORIGINAL_NO_VERIFICABLE' => 'No se pudo verificar el documento original.',
        'DOCUMENTO_ORIGINAL_FUERA_DE_CORTE' => 'El documento original es posterior a la información considerada.',
        'FUENTE_SIN_INSTANTE_CLINICO_IDENTIFICABLE' => 'La fuente no tiene una fecha clínica identificable.',
        'SOPORTE_NO_DISPONIBLE_O_INCOMPATIBLE' => 'El registro de respaldo no está disponible o no corresponde a esta valoración.',
        'PROCEDENCIA_ORIGINAL_INCOMPATIBLE' => 'La procedencia del registro original no corresponde a esta valoración.',
        'CONTRATO_INSTRUMENTAL_ORIGINAL_NO_RECUPERABLE' => 'No se pudo recuperar la aplicación instrumental original completa.',
        'ANULACION_ORIGINAL' => 'La valoración original fue anulada.',
        'RECTIFICADA_AL_CORTE' => 'La valoración ya estaba rectificada en la fecha considerada.',
        'FUENTE_FUERA_DE_CORTE_O_NO_VIGENTE' => 'La fuente no estaba vigente en la fecha considerada.',
        'SOPORTE_COMPARTIDO_NO_INDEPENDIENTE' => 'Dos valoraciones utilizan el mismo registro y no constituyen soportes independientes.',
        'C05_NO_IDENTIFICA_VALORACION_REVISADA' => 'La revisión de calidad no identifica con precisión una de las valoraciones cognitivas.',
        'EPISODIO_INCOMPATIBLE_CON_LA_CAPTURA' => 'El episodio indicado no corresponde a la aplicación instrumental original.',
        'CONDICION_ORIGINAL_NO_ACREDITADA' => 'Una condición obligatoria de la valoración original no está documentada.',
    ];

    public static function explicar(string $motivo): string
    {
        [$clave, $detalle] = array_pad(explode(':', $motivo, 2), 2, null);

        if ($clave === 'CONDICION_ORIGINAL_NO_ACREDITADA' && $detalle !== null && isset(self::CONDICIONES[$detalle])) {
            return 'Falta documentar '.self::CONDICIONES[$detalle].' en la valoración original.';
        }

        return self::TEXTOS[$clave] ?? 'Esta valoración requiere revisar su registro original y las condiciones de interpretación.';
    }
}
