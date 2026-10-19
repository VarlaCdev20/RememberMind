<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DateTimeImmutable;
use DomainException;

/** Incorpora instantáneas de contexto sin interpretación ni atribución cognitiva por texto o ausencia. */
final class IncorporadorContextoExperimental
{
    public const VERSION = 'CTX-FUENTES-TEST-1';

    private const FUENTES = ['controles_cognitivos', 'seguimientos_pedagogicos', 'valoraciones_funcionales',
        'controles_funcionales_iadl', 'valoraciones_psicologicas', 'diagnosticos', 'antecedentes_clinicos', 'registros_sueno'];

    public function incorporar(array $contexto, array $capturas): array
    {
        if (! array_is_list($capturas)) {
            throw new DomainException('Se requiere una lista de fuentes contextuales.');
        }
        $originales = $hechos = $episodios = $duplicados = [];
        foreach ($capturas as $captura) {
            if (! is_array($captura) || ($captura['contrato'] ?? null) !== self::VERSION
                || ($captura['uso'] ?? null) !== 'CAPTURA_CONTEXTO_TEST_ONLY'
                || ($captura['caso'] ?? null) !== $contexto['residente_ficticio']
                || ($captura['evaluacion'] ?? null) !== $contexto['evaluacion']
                || ! in_array($captura['tabla'] ?? null, self::FUENTES, true)
                || ! is_string($captura['registro_original'] ?? null) || ! str_starts_with($captura['registro_original'], 'SIM-')
                || ! is_array($captura['valores'] ?? null) || ! is_array($captura['motivos'] ?? null)
                || ! array_key_exists('estado_utilizable', $captura) || ! in_array($captura['estado_utilizable'], [true, false, null], true)
                || ($captura['procedencia_verificada'] ?? null) !== true
                || ! is_string($captura['autor_original'] ?? null) || trim($captura['autor_original']) === ''
                || ! is_string($captura['estado_original'] ?? null)
                || ! array_key_exists('fecha_original', $captura)
                || ! in_array($captura['precision_fecha'] ?? null, ['DIA', 'INSTANTE_SQL'], true)
                || ($captura['zona_fuente'] ?? null) !== 'America/La_Paz'
                || ! array_key_exists('tipo_episodio_original', $captura)
                || ! in_array($captura['tipo_episodio_original'], [null, 'cod_atencion', 'cod_actividad', 'cod_jornada'], true)
                || ($captura['rol'] ?? null) !== 'CONTEXTO_SIN_INFERENCIA_AUTOMATICA') {
                throw new DomainException('Fuente contextual sin contrato, procedencia o identidad coherentes.');
            }
            $corte = $captura['fecha_corte'] ?? null;
            if (! is_string($corte) || substr($corte, 0, 10) !== $contexto['fecha_corte']) {
                throw new DomainException('La captura contextual tiene otro corte.');
            }
            $fechaCorte = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.uP', $corte);
            if ($fechaCorte === false || $fechaCorte->format('Y-m-d\TH:i:s.uP') !== $corte
                || $fechaCorte->format('H:i:s.uP') !== '23:59:59.000000-04:00') {
                throw new DomainException('El perfil exige un corte diario exacto y coherente.');
            }
            $original = $captura['fecha_original'];
            if ($original !== null) {
                $formato = $captura['precision_fecha'] === 'DIA' ? 'Y-m-d' : 'Y-m-d H:i:s';
                $fecha = is_string($original) ? DateTimeImmutable::createFromFormat('!'.$formato, $original, new \DateTimeZone('America/La_Paz')) : false;
                if ($fecha === false || $fecha->format($formato) !== $original) {
                    throw new DomainException('Fecha contextual original inválida o incompatible con su precisión.');
                }
                if ($captura['precision_fecha'] === 'DIA') {
                    $fecha = $fecha->setTime(23, 59, 59);
                }
                if ($fecha > $fechaCorte && $captura['valores'] !== []) {
                    throw new DomainException('Una fuente posterior al corte no puede aportar valores.');
                }
            } elseif ($captura['valores'] !== []) {
                throw new DomainException('Una fuente sin fecha original no puede aportar valores.');
            }
            $clave = serialize([$captura['tabla'], $captura['registro_original']]);
            if (isset($originales[$clave])) {
                if ($originales[$clave] !== $captura) {
                    throw new DomainException('Relectura incompatible de una fuente contextual; no se sobrescribe.');
                }
                $duplicados[] = ['tabla' => $captura['tabla'], 'registro' => $captura['registro_original']];

                continue;
            }
            $originales[$clave] = $captura;
            $ep = $captura['episodio_original'] ?? null;
            if ($ep !== null && (! is_string($ep) || ! str_starts_with($ep, 'SIM-') || $captura['tipo_episodio_original'] === null)) {
                throw new DomainException('Episodio contextual fuera del laboratorio.');
            }
            foreach ($captura['valores'] as $campo => $valor) {
                if (! is_string($campo) || ! preg_match('/^[a-z][a-z_0-9]*$/D', $campo) || ! is_array($valor)
                    || ! array_key_exists('original', $valor) || ! array_key_exists('tipado', $valor)
                    || (! is_scalar($valor['original']) && $valor['original'] !== null)
                    || (! is_scalar($valor['tipado']) && $valor['tipado'] !== null)
                    || (is_float($valor['original']) && ! is_finite($valor['original']))
                    || (is_float($valor['tipado']) && ! is_finite($valor['tipado']))
                    || ($valor['tipo_original'] ?? null) !== get_debug_type($valor['original'])) {
                    throw new DomainException('Valor contextual sin bruto y tipo originales explícitos.');
                }
                $id = 'SIM-C-'.substr(hash('sha256', serialize([$contexto['evaluacion'], $captura['tabla'], $captura['registro_original'], $campo])), 0, 14);
                $hechos[] = ['id' => $id, 'caso' => $captura['caso'], 'evaluacion' => $captura['evaluacion'],
                    'tabla' => $captura['tabla'], 'registro_original' => $captura['registro_original'], 'campo' => $campo,
                    'valor_original' => $valor['original'], 'tipo_original' => $valor['tipo_original'], 'valor_tipado' => $valor['tipado'],
                    'valor_semantico' => null, 'mapeo_semantico' => null, 'version_extraccion' => self::VERSION,
                    'fecha_original' => $captura['fecha_original'], 'precision_fecha' => $captura['precision_fecha'],
                    'fecha_corte' => $corte, 'autor_original' => $captura['autor_original'], 'estado_original' => $captura['estado_original'],
                    'estado_utilizable' => $captura['estado_utilizable'], 'rol' => $captura['rol'], 'episodio_original' => $ep,
                    'tipo_episodio_original' => $captura['tipo_episodio_original'],
                    'motivos' => [...$captura['motivos'], 'SIN_MAPEO_COGNITIVO_AUTORIZADO']];
                if ($ep !== null) {
                    $claveEpisodio = $captura['tipo_episodio_original'].':'.$ep;
                    $episodios[$claveEpisodio]['ids'][] = $id;
                    $episodios[$claveEpisodio]['id'] = $ep;
                    $episodios[$claveEpisodio]['tipo'] = $captura['tipo_episodio_original'];
                }
            }
        }
        $relaciones = [];
        foreach ($episodios as $episodio) {
            if (count($episodio['ids']) > 1) {
                $relaciones[] = ['tipo' => 'MISMO_EPISODIO_ORIGINAL', 'episodio' => $episodio['id'],
                    'tipo_episodio' => $episodio['tipo'], 'hechos' => $episodio['ids'],
                    'independencia_acreditada' => false];
            }
        }

        return ['version' => self::VERSION, 'fuentes' => array_values($originales), 'hechos' => $hechos,
            'relaciones' => $relaciones, 'duplicados_no_recontados' => $duplicados];
    }
}
