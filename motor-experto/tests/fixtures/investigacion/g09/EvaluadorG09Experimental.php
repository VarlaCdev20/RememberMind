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
            if (! isset($entrada[$campo]) || ! is_string($entrada[$campo]) || $entrada[$campo] === '') {
                throw new DomainException('Falta el identificador obligatorio: '.$campo);
            }
        }
        if ($entrada['version'] !== CatalogoReglasG09Experimental::VERSION) {
            throw new DomainException('Versión de conocimiento incompatible.');
        }
        if (! str_starts_with($entrada['residente_ficticio'], 'SINTETICO-')
            || (! str_starts_with($entrada['evaluacion'], 'SIM-') && ! str_starts_with($entrada['evaluacion'], 'EXP-') && ! str_starts_with($entrada['evaluacion'], 'SINTETICO-'))
            || ! $this->fechaValida($entrada['fecha_corte'])) {
            throw new DomainException('Identidad o corte fuera del contrato de laboratorio.');
        }
        if (! in_array($entrada['criterio'], ['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], true)) {
            throw new DomainException('Criterio experimental desconocido.');
        }

        foreach (['senal_contextual', 'contexto_suficiente', 'contradiccion_documentada'] as $bandera) {
            if (isset($entrada[$bandera]) && ! is_bool($entrada[$bandera])) {
                throw new DomainException('Atestación técnica no booleana: '.$bandera);
            }
        }
        $observaciones = ($entrada['senal_contextual'] ?? false) === true;
        $contexto = ($entrada['contexto_suficiente'] ?? false) === true;
        $aceptadas = [];
        $excluidas = [];
        $origenes = [];
        foreach ($entrada['evidencias'] ?? [] as $e) {
            if (! is_array($e)) {
                throw new DomainException('Evidencia con estructura inválida.');
            }
            $id = $e['id'] ?? null;
            if (! is_string($id) || $id === '' || isset($origenes[$id])) {
                throw new DomainException('Identificador de evidencia inválido o duplicado.');
            }
            $origenes[$id] = true;
            $regla = CatalogoReglasG09Experimental::obtener((string) ($e['regla'] ?? ''));
            $fechaFuente = $e['fecha'] ?? null;
            $corte = $entrada['fecha_corte'];
            // Un corte de día incluye toda la fecha; un corte horario requiere
            // un instante registrado en la evidencia y una comparación exacta.
            $fechaValida = $this->fechaValida($fechaFuente) && $this->fechaValida($corte);
            $fechaEnCorte = $fechaValida
                && (strlen($corte) === 10
                    ? substr($fechaFuente, 0, 10) <= $corte
                    : (strlen($fechaFuente) === 19 && $fechaFuente <= $corte));
            $valida = ($e['residente_ficticio'] ?? null) === $entrada['residente_ficticio']
                && ($e['evaluacion'] ?? null) === $entrada['evaluacion']
                && ($e['version'] ?? null) === $entrada['version']
                && $fechaEnCorte
                && $regla['criterio'] === $entrada['criterio']
                && ($e['procedencia_verificada'] ?? false) === true
                && ($e['instrumento_test_only'] ?? false) === true
                && ($e['tarea_atribuible'] ?? false) === true
                && ($e['confusor_invalidante'] ?? true) === false
                && is_string($e['episodio'] ?? null) && trim($e['episodio']) !== ''
                && is_string($e['fuente'] ?? null) && trim($e['fuente']) !== '';
            if (! $valida) {
                $excluidas[] = ['id' => $id, 'motivo' => 'INADMISIBLE_O_NO_INTERPRETABLE'];

                continue;
            }
            $aceptadas[] = $e;
        }

        $evaluabilidad = ! $aceptadas ? ($observaciones ? 1 : 0) : ($contexto ? 2 : 1);
        $soportes = [];
        $traza = [];
        if ($evaluabilidad === 2) {
            foreach ($aceptadas as $e) {
                $regla = CatalogoReglasG09Experimental::obtener($e['regla']);
                $condiciones = [
                    'C1' => $e['dificultad_especifica_test_only'] ?? null,
                    'C2' => $e['atribucion_test_only'] ?? null,
                    'C3' => $e['contexto_regla_suficiente'] ?? null,
                ];
                foreach ($condiciones as $condicion) {
                    if ($condicion !== null && ! is_bool($condicion)) {
                        throw new DomainException('Condición técnica no booleana.');
                    }
                }
                $cumple = ! in_array(false, $condiciones, true) && ! in_array(null, $condiciones, true);
                $traza[] = ['regla' => $e['regla'], 'evidencia' => $e['id'], 'condiciones' => $condiciones, 'cumple' => $cumple,
                    'estados_condiciones' => array_map(static fn ($c) => $c === null ? 'NO_EVALUABLE' : ($c ? 'CUMPLE' : 'NO_CUMPLE'), $condiciones),
                    'fecha_fuente' => $e['fecha'], 'episodio_fuente' => $e['episodio']];
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
                if (! isset($episodiosUsados[$clave])) {
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
        if (! in_array($estado, self::ESTADOS, true)) {
            throw new DomainException('Estado no contemplado.');
        }
        ksort($resultados);
        usort($traza, fn ($a, $b) => strcmp($a['regla'].'|'.$a['evidencia'], $b['regla'].'|'.$b['evidencia']));

        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'contexto_origen' => array_intersect_key($entrada, array_flip(['uso', 'aislado', 'residente_ficticio',
                'evaluacion', 'version', 'criterio', 'fecha_corte', 'senal_contextual', 'contexto_suficiente', 'contradiccion_documentada'])),
            'version' => CatalogoReglasG09Experimental::VERSION,
            'criterio' => $entrada['criterio'],
            'evaluabilidad' => match ($entrada['criterio']) {
                'COG-ATE' => 'EV-CA-', 'COG-EJE' => 'EV-CE-',
                'COG-LEN' => 'EV-CL-', 'COG-VIS' => 'EV-CV-',
            }.$evaluabilidad,
            'estado_tecnico' => $estado,
            'consecuencias_candidatas' => $resultados,
            'excluidas' => $excluidas,
            'fuentes_examinadas' => $entrada['evidencias'] ?? [],
            'evidencias_admitidas' => $aceptadas,
            'motivos_g08' => $entrada['motivos_g08'] ?? [],
            'traza' => $traza,
            'explicacion' => $estado === 'HALLAZGO_ACOTADO'
                ? 'El hallazgo experimental se sustenta exclusivamente en las condiciones positivas C1, C2 y C3 de las reglas listadas en la traza.'
                : 'Sin conclusión clínica: revisar evaluabilidad, soportes, límites y conflictos de la traza.',
        ];
    }

    /** Revalida identidad, corte y traza antes de usar el resultado en otro consumidor técnico. */
    public function validarResultadoFuente(array $base, array $resultado): void
    {
        $origen = $resultado['contexto_origen'] ?? null;
        if (! is_array($origen) || ! is_array($resultado['fuentes_examinadas'] ?? null)
            || ! is_array($resultado['motivos_g08'] ?? null)) {
            throw new DomainException('La ejecución exige el contexto y las fuentes originales de G09.');
        }
        foreach (['uso', 'aislado', 'residente_ficticio', 'evaluacion', 'version', 'fecha_corte'] as $campo) {
            if (! array_key_exists($campo, $base) || ! array_key_exists($campo, $origen) || $base[$campo] !== $origen[$campo]) {
                throw new DomainException('El resultado fuente G09 pertenece a otro contexto de ejecución.');
            }
        }
        if (isset($base['criterio']) && $base['criterio'] !== ($origen['criterio'] ?? null)) {
            throw new DomainException('El criterio fuente G09 no corresponde al contexto de ejecución.');
        }
        // La revisión declarativa es independiente de la versión fuente G09.
        $verificado = $this->evaluar($origen + ['evidencias' => $resultado['fuentes_examinadas'],
            'motivos_g08' => $resultado['motivos_g08']]);
        if ($verificado !== $resultado) {
            throw new DomainException('La traza G09 no coincide con sus fuentes y contexto originales.');
        }
    }

    private function fechaValida(mixed $fecha): bool
    {
        if (! is_string($fecha)) {
            return false;
        }
        $formato = strlen($fecha) === 10 ? 'Y-m-d' : 'Y-m-d H:i:s';
        $f = \DateTimeImmutable::createFromFormat('!'.$formato, $fecha);

        return $f !== false && $f->format($formato) === $fecha;
    }
}
