<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

/** Plantillas deterministas sobre la lectura validada. No interpreta puntajes ni acredita requisitos. */
final class ExplicarResultadoNeurocognitivo
{
    public const FUNCIONES = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas',
        'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Capacidad visuoespacial'];

    public function explicar(array $lectura, array $areas): array
    {
        $fundamentos = $faltantes = $fuentes = $funciones = $exclusiones = [];
        foreach (self::FUNCIONES as $codigo => $nombre) {
            $area = $areas[$codigo];
            $d = $lectura['perfil'][$codigo];
            $funciones[$codigo] = ['nombre' => $nombre, 'frase' => $area['evaluada'] || $d['criterio_registrado'] ? $area['conclusion']
                : 'No contamos con una evaluación interpretable y autorizada para concluir sobre '.mb_strtolower($nombre).'.',
                'evaluada' => $area['evaluada'], 'evidencia' => []];
            if ($area['evaluada']) {
                $fundamentos[] = ['titulo' => $nombre, 'texto' => $area['conclusion']];
            } else {
                $faltantes[] = ['titulo' => 'Completar la valoración de '.mb_strtolower($nombre),
                    'texto' => $d['integridad'] ? 'El resultado y sus fuentes necesitan comprobarse antes de interpretarlos.'
                        : ($d['criterio_registrado'] && ! $d['autorizado']
                            ? 'La evaluación guardada todavía no está habilitada para su interpretación clínica.'
                            : ($d['evaluabilidad_codigo'] === 'EV-CM-1'
                                ? 'La información registrada es preliminar. Debe ampliarse antes de emitir una conclusión integrada.'
                                : 'Falta una evaluación específica interpretable, con sus condiciones de aplicación y su revisión profesional.'))];
            }
            foreach ($d['evidencias'] as $id => $e) {
                $clave = $lectura['grupos_origen'][$id] ?? (($e['tabla'] ?? '').'|'.($e['registro'] ?? $id));
                $admisible = in_array($e['admisibilidad'] ?? '', ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true);
                $uso = $area['evaluada'] && ($e['papel'] ?? '') === 'Soporte de inferencia';
                if (! isset($fuentes[$clave])) {
                    $fuentes[$clave] = ['id' => $id, 'area' => $codigo, 'nombre' => $e['nombre'], 'fuente' => $e['fuente'],
                        'fecha' => $e['fecha'], 'uso' => $uso ? 'Sustenta el resultado de esta área.'
                            : ($admisible ? 'Información conservada; no establece por sí sola una alteración objetiva.'
                                : 'Esta fuente no se utilizó para concluir. Su admisibilidad requiere revisión.'),
                        'utilizada' => $uso, 'excluida' => ! $admisible];
                }
                if ($uso) {
                    $funciones[$codigo]['evidencia'][$clave] = $e['nombre'].' · '.$e['fuente'];
                }
                if (! $admisible) {
                    $exclusiones[$clave] = ['titulo' => $e['nombre'],
                        'texto' => 'La fuente «'.$e['fuente'].'» no se utilizó para concluir. '.self::motivoNatural($e['motivo'] ?? null)];
                }
            }
        }
        $observaciones = $lectura['registros_cognitivos'] ?? [];
        if ($observaciones) {
            $olvidos = collect($observaciones)->contains(fn ($r) => collect($r['mediciones'])->contains(fn ($m) => in_array($m['nombre'], ['Repetición de preguntas', 'Olvido de indicaciones'], true) && $m['valor'] === 'Marcado en el registro'));
            $fundamentos[] = ['titulo' => 'Observaciones del expediente', 'texto' => $olvidos
                ? 'Hay controles que registran repetición de preguntas u olvido de indicaciones. Estas observaciones justifican ampliar la valoración, pero no confirman por sí solas una alteración objetiva de memoria.'
                : 'Hay controles cognitivos con fecha y responsable. Su presencia no equivale a una evaluación objetiva de las funciones cognitivas.'];
        }
        if (! $fundamentos) {
            $fundamentos[] = ['titulo' => 'Sin evidencias suficientes', 'texto' => 'No hay un resultado con fuentes verificables disponible para esta consulta. No se atribuyen hallazgos al residente.'];
        }
        $autonomia = $this->autonomia($lectura['autonomia'] ?? ['disponible' => false, 'actividades' => [], 'motivo' => 'No disponible.']);
        $contradiccionFuncional = collect($autonomia['actividades'])->contains(fn ($a) => ! empty($a['variantes']));
        if ($contradiccionFuncional) {
            $faltantes[] = ['titulo' => 'Revisar descripciones discrepantes de actividades cotidianas',
                'texto' => 'Hay registros simultáneos que difieren en las condiciones de la actividad, la ayuda o el funcionamiento anterior. Las descripciones y sus responsables se conservan por separado en la sección de autonomía.'];
        }
        $faltantes[] = ['titulo' => 'Conocer los cambios en las actividades cotidianas', 'texto' => $autonomia['actividades']
            ? 'Las observaciones disponibles describen actividades concretas. Falta una valoración integrada de la capacidad anterior y actual, la oportunidad de realizar cada actividad y las causas de la ayuda.'
            : 'No disponemos de una valoración funcional interpretable que permita establecer si necesita más ayuda que antes. La ayuda institucional no demuestra pérdida de autonomía cognitiva.'];
        $faltantes[] = ['titulo' => 'Revisión de factores que afectan el rendimiento',
            'texto' => 'No hay una revisión diferencial acreditada vinculada a este resultado. Corresponde comprobar las condiciones de aplicación y los factores médicos que puedan limitar su interpretación.'];
        $memoria = $areas['COG-MEM'];
        $mixtos = $memoria['evaluada'] && ($lectura['perfil']['COG-MEM']['resultado']['codigo'] ?? null) === 'HALLAZGOS_MIXTOS';
        $conclusion = $memoria['evaluada'] ? $memoria['conclusion'] : 'Todavía no podemos establecer una conclusión cognitiva completa.';
        $significado = $memoria['evaluada'] ? $memoria['significado']
            : 'La información disponible todavía no permite establecer una interpretación cognitiva integrada. La ausencia de una evaluación válida no demuestra que una función esté conservada.';
        if ($mixtos) {
            $faltantes[] = ['titulo' => 'Revisar los resultados discrepantes de memoria',
                'texto' => 'Se conservan hallazgos diferentes en tareas con soportes independientes. Revise las fuentes, fechas y condiciones de aplicación; no se elige automáticamente una de las conclusiones.'];
        }

        return ['conclusion' => $conclusion, 'resumen' => $memoria['evaluada']
            ? 'El hallazgo se limita a los componentes de memoria efectivamente evaluados. La información disponible no permite extenderlo a toda la cognición ni establecer un patrón de deterioro cognitivo leve.'
            : ($observaciones ? 'Contamos con observaciones del expediente, pero falta completar las evaluaciones específicas y la valoración de las actividades cotidianas.'
                : 'No hay registros interpretables suficientes para emitir un resultado. Complete las fuentes señaladas antes de valorar el perfil cognitivo.'),
            'funciones' => $funciones, 'fundamentos' => $fundamentos, 'fuentes' => array_values($fuentes),
            'faltantes' => [...$faltantes, ...array_values($exclusiones)],
            'significado' => $significado, 'autonomia' => $autonomia,
            'revision' => array_values(array_unique(array_column($faltantes, 'titulo'))),
            'completa' => false, 'contradiccion' => $mixtos || $contradiccionFuncional, 'experimental' => false, 'advertencia' => null];
    }

    /** Conserva razones legibles, sin publicar tokens internos como explicación clínica. */
    public static function motivoNatural(?string $motivo): string
    {
        if (! $motivo || preg_match('/[{}]|[A-Z]{2,}_[A-Z_]+|(?:DCL|COG|EV|PET|SIM)-[A-Z0-9]/', $motivo)) {
            return 'El motivo específico no está disponible en lenguaje interpretable. Deben revisarse las condiciones de uso y el registro original.';
        }

        return trim($motivo);
    }

    /** Traduce cada observación funcional sin resumirla como independencia global. */
    private function autonomia(array $lectura): array
    {
        $nombres = ['MEDICACION' => 'Organización de medicación', 'PERTENENCIAS' => 'Manejo de pertenencias',
            'RUTINAS' => 'Seguimiento de rutinas', 'CITAS' => 'Organización de citas', 'COMUNICACION' => 'Comunicación',
            'FINANZAS' => 'Administración de dinero', 'COMPRAS' => 'Compras', 'TRANSPORTE' => 'Transporte'];
        $actividades = [];
        foreach ($lectura['actividades'] as $a) {
            if ($a['discrepancia']) {
                $variantes = $this->autonomia(['actividades' => $a['variantes'], 'motivo' => 'Descripciones discrepantes.']);
                $actividades[] = ['nombre' => $nombres[$a['actividad']] ?? 'Actividad registrada',
                    'texto' => 'Hay registros del mismo momento con descripciones diferentes. Ninguno se elige como conclusión; corresponde revisar las fuentes siguientes.',
                    'fecha' => $a['fecha'], 'autor' => 'Responsables de los registros indicados', 'observacion' => null,
                    'variantes' => $variantes['actividades']];

                continue;
            }
            $frase = match (true) {
                in_array($a['oportunidad'], ['SIN_OPORTUNIDAD', 'NO_HABITUAL'], true) => 'No se pudo observar esta actividad en condiciones habituales. No permite concluir incapacidad.',
                $a['desempeno'] === 'INDEPENDIENTE' => 'El registro describe que realiza esta actividad de forma independiente.',
                $a['desempeno'] === 'CON_APOYO' => 'El registro describe que recibe ayuda para esta actividad.',
                default => 'El desempeño no es interpretable con la información registrada.',
            };
            $ayuda = match ($a['ayuda']) {
                'RECORDATORIO' => 'Necesita recordatorios según el registro.', 'SUPERVISION' => 'Recibe supervisión según el registro.',
                'SUSTITUCION' => 'Otra persona realiza la actividad según el registro.', default => null,
            };
            $causa = match ($a['causa']) {
                'INSTITUCIONAL' => 'La ayuda se atribuye al funcionamiento institucional; no demuestra incapacidad del residente.',
                'COGNITIVA' => 'El registro atribuye la ayuda a factores cognitivos; esta descripción no acredita por sí sola la causa.',
                'FISICA' => 'La ayuda se atribuye en el registro a factores físicos.', 'SENSORIAL' => 'La ayuda se atribuye en el registro a factores sensoriales.',
                'MIXTA' => 'El registro atribuye la ayuda a varios factores.', 'NO_DETERMINADA' => 'La causa de la ayuda no está determinada.', default => null,
            };
            $previo = match ($a['nivel_previo']) {
                'INDEPENDIENTE' => 'Anteriormente se registró independencia para esta actividad.', 'CON_APOYO' => 'Anteriormente también recibía apoyo.',
                'NO_HABITUAL' => 'La actividad no formaba parte de su funcionamiento habitual.', default => 'No se conoce suficientemente su capacidad anterior.',
            };
            $actividades[] = ['nombre' => $nombres[$a['actividad']] ?? 'Actividad sin descripción autorizada',
                'texto' => implode(' ', array_filter([$frase, $ayuda, $causa, $previo])),
                'fecha' => $a['fecha'], 'autor' => $a['autor'], 'observacion' => $a['observacion']];
        }

        return ['resumen' => $lectura['motivo'], 'actividades' => $actividades,
            'limite' => 'Estas observaciones describen actividades concretas. No acreditan automáticamente independencia funcional relativa para clasificar un patrón cognitivo.'];
    }
}
