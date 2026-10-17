<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;

/**
 * Motor de pruebas sin Laravel, base de datos, pacientes ni efectos laterales.
 * Todos los atributos de evidencia son atestaciones sintéticas TEST_ONLY;
 * ninguna escala, norma ni punto de corte se infiere en esta clase.
 */
final class EvaluadorG09Experimental
{
    private const ESTADOS = [
        'SIN_EVIDENCIA_ADMISIBLE', 'SOLO_SENAL_CONTEXTO',
        'EVALUABLE_SIN_SOPORTE', 'HALLAZGO_ACOTADO',
        'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION',
        'EVIDENCIA_CONTRADICTORIA',
    ];

    public function evaluar(array $entrada): array
    {
        if (($entrada['uso'] ?? null) !== 'TEST_ONLY' || ($entrada['aislado'] ?? null) !== true) {
            throw new DomainException('G09 no admite invocación asistencial ni datos no sintéticos.');
        }
        foreach (['residente_ficticio', 'evaluacion', 'version', 'criterio', 'fecha_corte'] as $campo) {
            if (!isset($entrada[$campo]) || !is_string($entrada[$campo]) || $entrada[$campo] === '') {
                throw new DomainException('Falta el identificador obligatorio: '.$campo);
            }
        }
        if ($entrada['version'] !== CatalogoReglasG09Experimental::VERSION) {
            throw new DomainException('Versión de conocimiento incompatible.');
        }
        if (!in_array($entrada['criterio'], ['COG-ATE','COG-EJE','COG-LEN','COG-VIS'], true)) {
            throw new DomainException('Criterio experimental desconocido.');
        }

        $observaciones = (bool) ($entrada['senal_contextual'] ?? false);
        $contexto = ($entrada['contexto_suficiente'] ?? false) === true;
        $aceptadas = [];
        $excluidas = [];
        $origenes = [];
        foreach ($entrada['evidencias'] ?? [] as $e) {
            if (!is_array($e)) {
                throw new DomainException('Evidencia con estructura inválida.');
            }
            $id = $e['id'] ?? null;
            if (!is_string($id) || $id === '' || isset($origenes[$id])) {
                throw new DomainException('Identificador de evidencia inválido o duplicado.');
            }
            $origenes[$id] = true;
            $regla = CatalogoReglasG09Experimental::obtener((string)($e['regla'] ?? ''));
            $valida = ($e['residente_ficticio'] ?? null) === $entrada['residente_ficticio']
                && ($e['evaluacion'] ?? null) === $entrada['evaluacion']
                && ($e['version'] ?? null) === $entrada['version']
                && ($e['fecha'] ?? '9999') <= $entrada['fecha_corte']
                && $regla['criterio'] === $entrada['criterio']
                && ($e['procedencia_verificada'] ?? false) === true
                && ($e['instrumento_test_only'] ?? false) === true
                && ($e['tarea_atribuible'] ?? false) === true
                && ($e['confusor_invalidante'] ?? true) === false
                && ($e['episodio'] ?? '') !== ''
                && ($e['fuente'] ?? '') !== '';
            if (!$valida) {
                $excluidas[] = ['id' => $id, 'motivo' => 'INADMISIBLE_O_NO_INTERPRETABLE'];
                continue;
            }
            $aceptadas[] = $e;
        }

        $evaluabilidad = !$aceptadas ? ($observaciones ? 1 : 0) : ($contexto ? 2 : 1);
        $soportes = [];
        $traza = [];
        if ($evaluabilidad === 2) {
            foreach ($aceptadas as $e) {
                $regla = CatalogoReglasG09Experimental::obtener($e['regla']);
                $condiciones = [
                    'C1' => ($e['dificultad_especifica_test_only'] ?? false) === true,
                    'C2' => ($e['atribucion_test_only'] ?? false) === true,
                    'C3' => ($e['contexto_regla_suficiente'] ?? false) === true,
                ];
                $cumple = !in_array(false, $condiciones, true);
                $traza[] = ['regla' => $e['regla'], 'evidencia' => $e['id'], 'condiciones' => $condiciones, 'cumple' => $cumple];
                if ($cumple) {
                    $soportes[$regla['consecuencia']][] = $e;
                }
            }
        }
        $resultados = [];
        $episodiosUsados = [];
        foreach ($soportes as $consecuencia => $evidencias) {
            foreach ($evidencias as $e) {
                // Dos lecturas de un mismo episodio/tarea son corroboración, no soporte independiente.
                $clave = $e['episodio'].'|'.$e['regla'];
                if (!isset($episodiosUsados[$clave])) {
                    $episodiosUsados[$clave] = true;
                    $resultados[$consecuencia][] = $e['id'];
                }
            }
        }
        $estado = $evaluabilidad === 0 ? 'SIN_EVIDENCIA_ADMISIBLE'
            : ($evaluabilidad === 1 ? 'SOLO_SENAL_CONTEXTO'
            : (count($resultados) === 0 ? 'EVALUABLE_SIN_SOPORTE'
            : (count($resultados) === 1 ? 'HALLAZGO_ACOTADO' : 'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION')));
        if (($entrada['contradiccion_documentada'] ?? false) === true && $evaluabilidad === 2) {
            $estado = 'EVIDENCIA_CONTRADICTORIA';
            $resultados = [];
        }
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new DomainException('Estado no contemplado.');
        }
        ksort($resultados);
        usort($traza, fn($a,$b) => strcmp($a['regla'].'|'.$a['evidencia'],$b['regla'].'|'.$b['evidencia']));
        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'version' => CatalogoReglasG09Experimental::VERSION,
            'criterio' => $entrada['criterio'],
            'evaluabilidad' => match ($entrada['criterio']) {
                'COG-ATE' => 'EV-CA-', 'COG-EJE' => 'EV-CE-',
                'COG-LEN' => 'EV-CL-', 'COG-VIS' => 'EV-CV-',
            }.$evaluabilidad,
            'estado_tecnico' => $estado,
            'consecuencias_candidatas' => $resultados,
            'excluidas' => $excluidas,
            'motivos_g08' => $entrada['motivos_g08'] ?? [],
            'traza' => $traza,
            'explicacion' => $estado === 'HALLAZGO_ACOTADO'
                ? 'El hallazgo experimental se sustenta exclusivamente en las condiciones positivas C1, C2 y C3 de las reglas listadas en la traza.'
                : 'Sin conclusión clínica: revisar evaluabilidad, soportes, límites y conflictos de la traza.',
        ];
    }
}
