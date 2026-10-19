<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;

/**
 * A01–A14 y G10 como atestaciones explícitas de laboratorio, previas a D-130.
 * No deduce confusores ni su efecto desde síntomas, puntajes o narrativa clínica.
 * Las verificaciones estructurales del lector/inspector siguen siendo obligatorias.
 */
final class ControlesTareaG09Experimental
{
    public const VERSION = 'ADM-G09-TEST-1';

    public const CONTROLES = ['A01', 'A02', 'A03', 'A04', 'A05', 'A06', 'A07',
        'A08', 'A09', 'A10', 'A11', 'A12', 'A13', 'A14'];

    /** Cantidades/códigos de los contratos particulares G09; no reglas causales. */
    private const CONFUSORES = ['COG-ATE' => 7, 'COG-EJE' => 9, 'COG-LEN' => 9, 'COG-VIS' => 8];

    public function revisar(string $dominio, ?array $declaracion): array
    {
        if (! isset(self::CONFUSORES[$dominio])) {
            throw new DomainException('Dominio sin contrato de controles G09.');
        }
        if ($declaracion !== null && (($declaracion['contrato'] ?? null) !== self::VERSION
            || ! is_array($declaracion['atestaciones'] ?? null) || ! is_array($declaracion['confusores'] ?? null)
            || ! array_is_list($declaracion['confusores'])
            || array_diff(array_keys($declaracion['atestaciones']), self::CONTROLES))) {
            throw new DomainException('Declaración de controles de tarea incompatible.');
        }
        $estados = $motivos = $advertencias = [];
        foreach (self::CONTROLES as $codigo) {
            $valor = $declaracion['atestaciones'][$codigo] ?? null;
            if ($valor !== null && ! is_bool($valor)) {
                throw new DomainException('Atestación de tarea mal tipada: '.$codigo);
            }
            $estados[$codigo] = $valor;
            if ($valor !== true) {
                $motivos[] = $codigo.($valor === null ? '_DESCONOCIDO' : '_NO_ACREDITADO');
            }
        }
        $ids = [];
        foreach ($declaracion['confusores'] ?? [] as $confusor) {
            if (! is_array($confusor) || ! is_string($confusor['codigo'] ?? null)
                || ! preg_match('/^CONF-'.substr($dominio, 4).'-0([1-9])$/D', $confusor['codigo'], $coincidencia)
                || (int) $coincidencia[1] > self::CONFUSORES[$dominio]
                || ! array_key_exists('presente', $confusor)
                || ($confusor['presente'] !== null && ! is_bool($confusor['presente']))
                || ! in_array($confusor['efecto'] ?? null, ['INVALIDANTE', 'ADVERTENCIA', 'NO_INVALIDANTE', 'NO_DETERMINADO'], true)
                || ! is_string($confusor['fuente'] ?? null) || ! str_starts_with($confusor['fuente'], 'SIM-')
                || ! is_string($confusor['justificacion'] ?? null) || trim($confusor['justificacion']) === '') {
                throw new DomainException('Confusor de tarea sin código, tipo o procedencia explícitos.');
            }
            $codigo = $confusor['codigo'];
            if (isset($ids[$codigo])) {
                throw new DomainException('Confusor duplicado en la misma tarea.');
            }
            $ids[$codigo] = true;
            if ($confusor['presente'] === null) {
                $motivos[] = $codigo.'_PRESENCIA_DESCONOCIDA';
            } elseif ($confusor['presente']) {
                if (in_array($confusor['efecto'], ['INVALIDANTE', 'NO_DETERMINADO'], true)) {
                    $motivos[] = $codigo.'_'.$confusor['efecto'];
                } elseif ($confusor['efecto'] === 'ADVERTENCIA') {
                    $advertencias[] = $codigo;
                }
            }
        }

        return ['contrato' => self::VERSION, 'dominio' => $dominio, 'atestaciones' => $estados,
            'confusores_declarados' => $declaracion['confusores'] ?? [], 'admisible' => $motivos === [],
            'motivos' => $motivos, 'advertencias' => $advertencias,
            'modificador' => $advertencias ? 'MOD-'.match ($dominio) {
                'COG-ATE' => 'CA', 'COG-EJE' => 'CE', 'COG-LEN' => 'CL', 'COG-VIS' => 'CV',
            }.'-1' : null];
    }
}
