<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DomainException;

/** Documento 03 y Matriz 03A: solo ensayo TEST_ONLY, ninguna emisión asistencial. */
final class ClasificadorPetersenExperimental
{
    public const VERSION = 'DCL-PET-20261017-v0.1';

    public const CRITERIOS = ['DCL-C01', 'DCL-C02', 'DCL-C03', 'DCL-C04', 'DCL-C05'];

    public const DOMINIOS = ['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'];

    private const PATRONES = [
        'PET-01' => 'Amnésico de dominio único',
        'PET-02' => 'Amnésico multidominio',
        'PET-03' => 'No amnésico de dominio único',
        'PET-04' => 'No amnésico multidominio',
    ];

    public function clasificar(array $caso): array
    {
        if (($caso['uso'] ?? null) !== 'TEST_ONLY'
            || ($caso['aislado'] ?? null) !== true
            || ($caso['version'] ?? null) !== self::VERSION) {
            throw new DomainException('Clasificación DCL exclusivamente experimental, sin autorización asistencial.');
        }
        foreach (['id_evaluacion', 'residente_ficticio', 'fecha_corte'] as $key) {
            if (! is_string($caso[$key] ?? null) || $caso[$key] === '') {
                throw new DomainException('Identificador obligatorio ausente: '.$key);
            }
        }
        if (! str_starts_with($caso['id_evaluacion'], 'SIM-')
            || ! str_starts_with($caso['residente_ficticio'], 'SINTETICO-')) {
            throw new DomainException('No se admiten identificadores de residentes reales.');
        }
        $fechaCorte = $this->fecha($caso['fecha_corte']);
        if (! is_array($caso['criterios'] ?? null) || ! is_array($caso['dominios'] ?? null)
            || count($caso['criterios']) !== 5 || count($caso['dominios']) !== 5
            || array_diff(array_keys($caso['criterios']), self::CRITERIOS)
            || array_diff(self::CRITERIOS, array_keys($caso['criterios']))
            || array_diff(array_keys($caso['dominios']), self::DOMINIOS)
            || array_diff(self::DOMINIOS, array_keys($caso['dominios']))) {
            throw new DomainException('Se requieren los cinco criterios C01–05 y los cinco dominios.');
        }
        $c = [];
        $d = [];
        $sinSoporte = [];
        $episodios = [];
        $ids = [];
        $solapamientos = [];
        foreach (['criterios' => self::CRITERIOS, 'dominios' => self::DOMINIOS] as $grupo => $codigos) {
            foreach ($codigos as $id) {
                $entrada = $caso[$grupo][$id];
                if (! is_array($entrada)) {
                    throw new DomainException('Entrada incorrecta: '.$id);
                }
                $v = $entrada['estado'] ?? null;
                if (! in_array($v, $grupo === 'criterios' ? ['S', 'N', 'U', 'X', 'B'] : ['A', 'P', 'U', 'I', 'X'], true)
                    || ($v === 'B' && $id !== 'DCL-C04')) {
                    throw new DomainException('Valor no permitido en '.$id);
                }
                if ($grupo === 'criterios') {
                    $c[$id] = $v;
                } else {
                    $d[$id] = $v;
                }
                if (! in_array($v, ['S', 'N', 'A', 'P'], true)) {
                    continue;
                }
                // El soporte es DECLARADO por un caso artificial, jamás calculado
                // a partir de G09, D-131, NULL o un puntaje total de MoCA/MMSE.
                $s = $entrada['soporte'] ?? null;
                if (! is_array($s) || ! is_string($s['id'] ?? null) || $s['id'] === ''
                    || ! is_string($s['episodio'] ?? null) || $s['episodio'] === '') {
                    $sinSoporte[] = $id;

                    continue;
                }
                if (($s['uso'] ?? null) !== 'REFERENCIA_SINTETICA'
                    || ($s['id_evaluacion'] ?? null) !== $caso['id_evaluacion']
                    || ($s['residente_ficticio'] ?? null) !== $caso['residente_ficticio']
                    || ($s['version'] ?? null) !== self::VERSION
                    || ! is_string($s['fecha'] ?? null)
                    || $this->fecha($s['fecha']) > $fechaCorte) {
                    throw new DomainException('Fuente/versión/corte incompatible para '.$id);
                }
                if (isset($ids[$s['id']]) && $ids[$s['id']] !== $id) {
                    $solapamientos[] = $id;
                }
                $ids[$s['id']] = $id;
                if ($grupo === 'dominios') {
                    if (isset($episodios[$s['episodio']]) && $episodios[$s['episodio']] !== $id) {
                        $solapamientos[] = $id;
                    }
                    $episodios[$s['episodio']] = $id;
                }
            }
        }
        $n = count(array_filter(array_slice($d, 1), fn ($v) => $v === 'A'));
        $mem = $d['COG-MEM'];
        $alterados = array_keys(array_filter($d, fn ($v) => $v === 'A'));
        $resultado = '';
        $motivos = [];

        // Orden del contrato 03, precisado por CAS-11 de Matriz 03A.
        if (in_array('X', $c, true) || in_array('X', $d, true) || $solapamientos) {
            $resultado = 'ABSTENCION_CONTRADICCION';
            $motivos = $solapamientos ? ['SOLAPAMIENTO_SOPORTES'] : ['CONTRADICCION_NO_ADJUDICADA'];
        } elseif (in_array($c['DCL-C04'], ['N', 'B'], true) || ($caso['estado_agudo_pendiente'] ?? false) === true) {
            $resultado = 'ABSTENCION_EVALUACION_DIFERENCIAL';
            $motivos = ['DCL_C04_DIFERENCIAL_NO_RESUELTO'];
        } elseif (in_array('U', $c, true) || in_array('U', $d, true)
            || in_array('I', $d, true) || $sinSoporte) {
            $resultado = 'ABSTENCION_INFORMACION_INSUFICIENTE';
            $motivos = $sinSoporte ? array_map(fn ($x) => 'SIN_SOPORTE_'.$x, $sinSoporte) : ['REQUISITO_O_DOMINIO_NO_INTERPRETABLE'];
        } elseif (($c['DCL-C02'] === 'S' && ! $alterados) || ($c['DCL-C02'] === 'N' && $alterados)) {
            $resultado = 'REQUIERE_ADJUDICACION_CONTRATO';
            $motivos = ['DCL_C02_INCOHERENTE_CON_DOMINIOS'];
        } elseif (in_array('N', $c, true)) {
            $resultado = 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO';
            $motivos = ['CRITERIO_GENERAL_NO_ACREDITADO'];
        } elseif (! array_diff($c, ['S'])) {
            $resultado = $mem === 'A'
                ? ($n === 0 ? 'PET-01' : 'PET-02')
                : match (true) {
                    $n === 1 => 'PET-03',
                    $n >= 2 => 'PET-04',
                    default => 'REQUIERE_ADJUDICACION_CONTRATO',
                };
            $motivos = $resultado === 'REQUIERE_ADJUDICACION_CONTRATO'
                ? ['DCL_C02_SIN_SOPORTE_OBJETIVO'] : ['PATRON_PETERSEN_SIMULADO'];
        } else {
            $resultado = 'ABSTENCION_INFORMACION_INSUFICIENTE';
            $motivos = ['REQUISITOS_NO_RESUELTOS'];
        }

        return [
            'uso' => 'SIMULACION_ACADEMICA_NO_CLINICA',
            'compuerta_asistencial' => 'SOLO_LABORATORIO_NO_EMITIR',
            'version' => self::VERSION,
            'id_evaluacion' => $caso['id_evaluacion'],
            'estado' => $resultado,
            'patron_candidato' => isset(self::PATRONES[$resultado]) ? $resultado : null,
            'nombre_patron' => self::PATRONES[$resultado] ?? null,
            'motivos' => $motivos,
            'traza' => [
                'requisitos' => $c,
                'dominios' => $d,
                'memoria' => $mem,
                'otros_dominios_alterados' => $n,
                'dominios_alterados' => $alterados,
                'soportes_sinteticos' => array_keys($ids),
                'sin_soporte' => $sinSoporte,
                'solapamientos' => array_values(array_unique($solapamientos)),
                'fecha_corte' => $caso['fecha_corte'],
            ],
            'limitaciones' => 'No se interpretan instrumentos reales ni se diagnostica DCL, Alzheimer, demencia o pronóstico.',
        ];
    }

    private function fecha(string $valor): string
    {
        foreach (['Y-m-d', 'Y-m-d H:i:s'] as $formato) {
            $t = \DateTimeImmutable::createFromFormat('!'.$formato, $valor);
            if ($t !== false && $t->format($formato) === $valor) {
                return $formato === 'Y-m-d' ? $valor.' 23:59:59' : $valor;
            }
        }
        throw new DomainException('Fecha inválida en contrato DCL.');
    }
}
