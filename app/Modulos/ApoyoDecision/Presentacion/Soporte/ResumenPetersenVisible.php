<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

/** Traduce el estado emitido por el motor; no deriva criterios ni modifica su precedencia. */
final class ResumenPetersenVisible
{
    public static function presentar(array $resultado, bool $hayObservaciones): array
    {
        if ($resultado['patron'] !== null) {
            return ['estado' => 'Patrón orientativo', 'titulo' => null,
                'explicacion' => 'Se identificó un patrón orientativo con los soportes registrados. Debe interpretarse en el contexto clínico del residente.',
                'orientacion' => 'Cómo leer este resultado', 'faltan_mediciones' => false];
        }

        [$estado, $titulo, $explicacion, $orientacion] = match ($resultado['estado']) {
            'SIN_PATRON_CLASIFICABLE_POR_REQUISITO' => ['Sin patrón clasificable', 'No se produjo un patrón orientativo',
                'Los requisitos revisados no permiten asignar uno de los cuatro patrones orientativos.', 'Cómo leer este resultado'],
            'ABSTENCION_CONTRADICCION' => ['Revisar valoraciones', 'Hay valoraciones contradictorias que deben aclararse',
                'Los registros contienen interpretaciones incompatibles. El detalle identifica las áreas o requisitos afectados.', 'Qué impide clasificar'],
            'ABSTENCION_EVALUACION_DIFERENCIAL' => ['Revisión pendiente', 'La valoración requiere revisar posibles causas alternativas',
                'La revisión de posibles causas alternativas no permite completar la clasificación. Consulte su conclusión y los registros de respaldo.', 'Qué impide clasificar'],
            'ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE' => ['Reglas no disponibles', 'No hay una versión vigente de las reglas para clasificar',
                'La información disponible puede consultarse; la clasificación requiere una versión vigente del conocimiento.', 'Qué impide clasificar'],
            'ABSTENCION_SIN_REGLA_PETERSEN_HABILITADA' => ['Regla no habilitada', 'La regla necesaria para este perfil no está habilitada',
                'Consulte el estado de las reglas aplicables. Habilitar una regla requiere el procedimiento de gestión de conocimiento.', 'Qué impide clasificar'],
            'ABSTENCION_INFORMACION_INSUFICIENTE' => ['Perfil pendiente de valoración', $hayObservaciones
                ? 'Hay observaciones cognitivas; el perfil aún no está determinado' : 'El perfil cognitivo aún no está determinado',
                'Los registros y hallazgos disponibles se muestran en esta página. La clasificación requiere otras valoraciones originales; vea a continuación qué falta.', 'Qué falta para clasificar'],
            default => ['Revisar resultado', 'No se pudo interpretar el resultado disponible',
                'Actualice la consulta. Si el resultado sigue sin poder mostrarse, comuníquelo al responsable del sistema.', 'Cómo continuar'],
        };

        return compact('estado', 'titulo', 'explicacion', 'orientacion')
            + ['faltan_mediciones' => $resultado['estado'] === 'ABSTENCION_INFORMACION_INSUFICIENTE'];
    }
}
