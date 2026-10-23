<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Compone un informe de lectura a partir del lector autorizado; no consulta fuentes ni infiere. */
final class PresentarInformeCognitivo
{
    private const NOMBRES = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas',
        'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Habilidades visuoespaciales'];

    /** Presenta solo la narrativa de una instantánea ficticia íntegra, sin habilitar publicación clínica. */
    public function presentarSimulacion(array $lectura, string $criterio): array
    {
        $sim = $lectura['simulacion_local'] ?? [];
        $narrativa = $sim['narrativa'] ?? [];
        if (! app()->environment(['local', 'testing']) || ! config('sistema_experto.simulacion_local.habilitada')
            || ($lectura['evaluacion']['version'] ?? null) !== 'SIM-ORION-LOCAL-1'
            || ($lectura['prueba_tecnica'] ?? false) !== true || ($sim['conservacion_completa'] ?? false) !== true
            || ($narrativa['experimental'] ?? false) !== true || ($narrativa['publicable_clinicamente'] ?? true) !== false
            || ($narrativa['advertencia'] ?? null) !== InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL) {
            throw new DomainException('La consulta requiere una instantánea local íntegra y habilitada.');
        }
        $areas = [];
        foreach ($lectura['perfil'] as $codigo => $detalle) {
            $areas[$codigo] = ['nombre' => self::NOMBRES[$codigo], 'icono' => $detalle['icono'],
                'estado' => $detalle['estado'], 'conclusion' => $narrativa['funciones'][$codigo]['frase'] ?? $detalle['interpretacion'],
                'significado' => $detalle['interpretacion'], 'por_que' => 'Lectura de la instantánea de simulación verificada; sin uso asistencial.',
                'limites' => ['Resultado de demostración. No es una valoración clínica institucional.'],
                'respaldo' => ['Instantánea local de simulación conservada y verificada.'], 'evaluada' => false, 'tono' => 'neutral'];
        }

        return ['titulo' => 'Evaluación neurocognitiva', 'narrativa' => $narrativa, 'areas' => $areas, 'actual' => $areas[$criterio],
            'historial' => iterator_to_array($lectura['historial']),
            // Cambia solo la etiqueta visible; la advertencia original sigue verificada en la instantánea.
            'procedencia_visible' => 'Entorno de pruebas · Resultado de demostración · Sin uso asistencial.',
            'limite_global' => 'El patrón conservado es candidato para revisión; no constituye un diagnóstico clínico.',
            'limite_fuente' => 'Los datos de simulación no se consultan como fuentes clínicas actuales.',
            'limite_revision' => 'Los resultados de demostración no admiten confirmación ni rechazo profesional en el expediente.'];
    }

    /** Resume dictámenes opcionales sin confundir ausencia o estructura incompleta con una firma. */
    public function resumirRevision(?array $revisiones, bool $hayEvaluacion, bool $simulacion): string
    {
        if ($simulacion) {
            return 'Resultado conservado';
        }
        if ($revisiones === null) {
            return $hayEvaluacion ? 'No habilitada para esta valoración' : 'Pendiente de una valoración disponible';
        }
        $estados = ['PENDIENTE' => 'pendiente', 'CONFIRMADA' => 'confirmada', 'RECHAZADA' => 'rechazada'];

        return 'Psicología: '.($estados[$revisiones['PSICOLOGIA']['estado'] ?? ''] ?? 'estado no disponible')
            .'. Medicina: '.($estados[$revisiones['MEDICINA']['estado'] ?? ''] ?? 'estado no disponible').'.';
    }

    /** Conserva la lectura original y produce un DTO plano por área y evaluación histórica. */
    public function presentar(array $lectura, string $criterio): array
    {
        if (($lectura['prueba_tecnica'] ?? false) || isset($lectura['simulacion_local'])) {
            throw new DomainException('La pantalla de valoración requiere una lectura operativa.');
        }
        $areas = [];
        foreach ($lectura['perfil'] as $codigo => $detalle) {
            $areas[$codigo] = $this->area($codigo, $detalle,
                $codigo === 'COG-MEM' ? count($lectura['registros_cognitivos']) : 0);
        }
        $actual = $areas[$criterio];
        $historial = [];
        foreach ($lectura['historial'] as $h) {
            $historial[] = $h;
        }

        return ['titulo' => 'Evaluación neurocognitiva', 'areas' => $areas, 'actual' => $actual, 'historial' => $historial,
            'narrativa' => (new ExplicarResultadoNeurocognitivo)->explicar($lectura, $areas),
            'limite_global' => 'Los resultados se limitan a los componentes examinados. No establecen por sí solos un diagnóstico ni describen la cognición global.',
            'sin_evaluacion' => 'No hay una evaluación experta conservada disponible para consulta. Las observaciones generales no equivalen a un área evaluada.',
            'complemento' => 'Información documental y observaciones operativas. Su presencia no acredita evaluación instrumental, hallazgo objetivo ni resultado integrado.',
            'complemento_textos' => [
                'controles' => 'La observación registrada se conserva; todavía no se ha generado una valoración integrada de aprendizaje y memoria.',
                'instrumentos' => 'Estas aplicaciones no acreditan por sí solas un componente de memoria autorizado.',
                'documental' => 'La definición es documental. No acredita que esta área haya sido evaluada en el residente ni autoriza una conclusión clínica.',
                'observaciones' => 'Son controles operativos del residente, fuera de una evaluación experta integrada. Un indicador no marcado no demuestra ausencia de dificultad.',
            ],
            'limite_fuente' => 'Se consulta el dato fuente actual; no sustituye la evidencia histórica utilizada en la evaluación.',
            'limite_revision' => 'Psicología y Medicina confirman o rechazan por separado las valoraciones habilitadas para revisión profesional.'];
    }

    /**
     * Diferencia ausencia, preliminar, insuficiencia, bloqueo de publicación e integridad.
     * Solo COG-MEM tiene contrato de resultado en este lector. Un código G09 o
     * una etiqueta observacional nunca se publica como un resultado de memoria.
     */
    private function area(string $codigo, array $d, int $observaciones): array
    {
        $soportes = array_values(array_filter($d['evidencias'], fn ($e) => $e['papel'] === 'Soporte de inferencia'));
        $valor = $d['resultado']['codigo'] ?? null;
        $conclusion = LenguajeResultadosExperto::conclusionMemoria($valor);
        $publicable = $codigo === 'COG-MEM' && $d['autorizado'] && ! $d['integridad']
            && $conclusion !== null && $soportes && $d['traza'] !== null;
        $estado = $d['estado'];
        $tono = 'neutral';
        $significado = 'La información disponible todavía no permite establecer una interpretación cognitiva integrada.';
        $limites = [];
        if ($d['integridad']) {
            $conclusion = 'No se puede publicar una conclusión: la evidencia o el fundamento conservados requieren revisión.';
            $estado = 'Integridad pendiente';
            $limites = $d['integridad'];
        } elseif ($publicable) {
            $tono = LenguajeResultadosExperto::presentacion($valor)['tono'];
            $significado = LenguajeResultadosExperto::RESULTADOS[$valor]['interpretacion'];
            $limites[] = 'El resultado se limita a los componentes evaluados; no equivale a normalidad global ni a diagnóstico.';
        } elseif (! $d['criterio_registrado']) {
            $estado = $observaciones ? 'Observaciones generales disponibles' : 'Sin evaluación disponible';
            $conclusion = $observaciones ? 'Hay controles cognitivos registrados, pero no una evaluación integrada de memoria.'
                : 'No hay una evaluación conservada de esta área.';
            $limites[] = 'No se presenta esta área como evaluada a partir de observaciones generales o de resultados de otras áreas.';
        } elseif (! $d['autorizado']) {
            $estado = 'Publicación clínica no habilitada';
            $conclusion = 'La evaluación conservada no tiene autorización de publicación clínica para esta área y versión.';
            $limites[] = 'La existencia de registros o de una versión activa no autoriza una conclusión clínica.';
        } else {
            $conclusion = match ($d['evaluabilidad_codigo']) {
                'EV-CM-0' => 'La evidencia disponible es insuficiente para concluir sobre memoria.',
                'EV-CM-1' => 'La información de memoria es preliminar y requiere ampliación.',
                default => 'No hay un resultado verificable disponible para esta área.',
            };
            $limites[] = 'La ausencia de resultado no demuestra ausencia de dificultad.';
        }
        if ($d['modificador']) {
            $limites[] = $d['modificador'];
        }
        if ($d['motivo']) {
            $limites[] = $d['motivo'];
        }
        $respaldo = $publicable ? array_map(fn ($e) => $e['nombre'].' · '.$e['fuente'], $soportes)
            : ($d['evidencias'] ? ['Hay evidencias conservadas; su presencia no acredita un resultado publicable.']
                : ($observaciones ? ['Controles cognitivos operativos con fecha y responsable, fuera de una inferencia integrada.']
                    : ['No hay soporte de un resultado integrado disponible para esta área.']));

        return ['nombre' => self::NOMBRES[$codigo], 'icono' => $d['icono'], 'estado' => $estado,
            'conclusion' => $conclusion, 'significado' => $significado, 'limites' => array_values(array_unique($limites)),
            'respaldo' => $respaldo, 'tono' => $tono, 'evaluada' => $publicable,
            'por_que' => $publicable ? 'La conclusión procede del resultado conservado y de las condiciones satisfechas con sus evidencias de soporte. La consulta no vuelve a ejecutar el motor.'
                : 'El registro disponible no reúne una conclusión publicable con soporte verificable para esta área. Consulte las fuentes y los límites conservados.'];
    }

    /**
     * Presenta únicamente un informe G09 ya construido en una base desechable de testing.
     * No existe ruta asistencial que invoque este método ni se importa el motor experimental.
     */
    public function presentarG09Prueba(array $informe): array
    {
        $c = DB::connection();
        if (! app()->environment('testing') || ! BaseTecnicaDesechable::permite(
            $c->getDriverName(), $c->getDatabaseName(), $c->getConfig('username'), $c->getConfig('host'))
            || ($informe['uso'] ?? null) !== 'PRUEBA_TECNICA_NO_CLINICA'
            || ($informe['version'] ?? null) !== 'G09-EXP-20261017'
            || ! in_array($informe['criterio'] ?? null, ['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], true)) {
            throw new DomainException('La presentación G09 requiere un informe técnico aislado; no admite residentes asistenciales.');
        }
        $texto = LenguajeResultadosExperto::estadoG09($informe['estado_tecnico'] ?? '');
        if ($texto === null) {
            throw new DomainException('Estado G09 sin contrato de presentación.');
        }

        return $texto + ['estado_original' => $informe['estado_tecnico'], 'uso' => $informe['uso'],
            'publicable' => false, 'limites' => 'Datos sintéticos. Sin diagnóstico, gravedad, pronóstico ni clasificación Petersen.'];
    }
}
