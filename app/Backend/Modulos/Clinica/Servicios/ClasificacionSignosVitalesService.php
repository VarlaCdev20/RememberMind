<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\SignoVital;

final class ClasificacionSignosVitalesService
{
    private const PA_SISTOLICA_AVISO_BAJA = 90;
    private const PA_SISTOLICA_AVISO_ALTA = 160;
    private const PA_DIASTOLICA_AVISO_ALTA = 100;
    private const FC_AVISO_BAJA = 50;
    private const FC_AVISO_ALTA = 100;
    private const FR_AVISO_BAJA = 12;
    private const FR_AVISO_ALTA = 22;
    private const TEMPERATURA_AVISO_BAJA = 36.0;
    private const TEMPERATURA_AVISO_ALTA = 37.8;
    private const SATURACION_AVISO_BAJA = 95;
    private const GLUCOSA_AVISO_BAJA = 70;
    private const GLUCOSA_AVISO_ALTA = 180;

    /** @return array{pa: string, fc: string, fr: string, temperatura: string, saturacion: string, glucosa: string} */
    public static function leyendaAvisos(): array
    {
        return [
            'pa' => 'PA: sistólica <'.self::PA_SISTOLICA_AVISO_BAJA.' o ≥'.self::PA_SISTOLICA_AVISO_ALTA.'; diastólica ≥'.self::PA_DIASTOLICA_AVISO_ALTA.' mmHg',
            'fc' => 'FC: <'.self::FC_AVISO_BAJA.' o >'.self::FC_AVISO_ALTA.' bpm',
            'fr' => 'FR: <'.self::FR_AVISO_BAJA.' o >'.self::FR_AVISO_ALTA.' rpm',
            'temperatura' => 'Temp: <'.self::TEMPERATURA_AVISO_BAJA.' o ≥'.self::TEMPERATURA_AVISO_ALTA.' °C',
            'saturacion' => 'SpO₂: <'.self::SATURACION_AVISO_BAJA.'%',
            'glucosa' => 'Glucosa: <'.self::GLUCOSA_AVISO_BAJA.' o >'.self::GLUCOSA_AVISO_ALTA.' mg/dL',
        ];
    }

    /** @return array{pa: string, fc: string, fr: string, temperatura: string, saturacion: string, glucosa: string, global: string} */
    public static function evaluarRegistro(SignoVital $signo): array
    {
        return self::evaluar(
            $signo->presion_sistolica,
            $signo->presion_diastolica,
            $signo->frecuencia_cardiaca,
            $signo->frecuencia_respiratoria,
            $signo->temperatura,
            $signo->saturacion_oxigeno,
            $signo->glucemia,
        );
    }

    /** Conserva el criterio preventivo heredado, distinto de la clasificación global. */
    public static function requiereAlertaPreventiva(SignoVital $signo): bool
    {
        return ($signo->temperatura !== null && $signo->temperatura > 37.8)
            || ($signo->saturacion_oxigeno !== null && $signo->saturacion_oxigeno < 92);
    }

    /** @return array{pa: string, fc: string, fr: string, temperatura: string, saturacion: string, glucosa: string, global: string} */
    public static function evaluar(
        ?float $sistolica,
        ?float $diastolica,
        ?float $frecuenciaCardiaca,
        ?float $frecuenciaRespiratoria,
        ?float $temperatura,
        ?float $saturacion,
        ?float $glucosa,
    ): array {
        $niveles = [
            'pa' => self::presionArterial($sistolica, $diastolica),
            'fc' => self::frecuenciaCardiaca($frecuenciaCardiaca),
            'fr' => self::frecuenciaRespiratoria($frecuenciaRespiratoria),
            'temperatura' => self::temperatura($temperatura),
            'saturacion' => self::saturacion($saturacion),
            'glucosa' => self::glucosa($glucosa),
        ];

        $niveles['global'] = match (true) {
            in_array('critico', $niveles, true) => 'critico',
            in_array('advertencia', $niveles, true) => 'advertencia',
            in_array('normal', $niveles, true) => 'normal',
            default => 'sin_dato',
        };

        return $niveles;
    }

    private static function presionArterial(?float $sistolica, ?float $diastolica): string
    {
        if (!$sistolica && !$diastolica) return 'sin_dato';
        if (($sistolica && $sistolica >= 180) || ($diastolica && $diastolica >= 110)) return 'critico';
        if (($sistolica && ($sistolica >= self::PA_SISTOLICA_AVISO_ALTA || $sistolica < self::PA_SISTOLICA_AVISO_BAJA)) || ($diastolica && $diastolica >= self::PA_DIASTOLICA_AVISO_ALTA)) return 'advertencia';
        if (!$sistolica || !$diastolica) return 'sin_dato';
        return 'normal';
    }

    private static function frecuenciaCardiaca(?float $frecuencia): string
    {
        if (!$frecuencia) return 'sin_dato';
        if ($frecuencia > 130 || $frecuencia < 40) return 'critico';
        if ($frecuencia > self::FC_AVISO_ALTA || $frecuencia < self::FC_AVISO_BAJA) return 'advertencia';
        return 'normal';
    }

    private static function frecuenciaRespiratoria(?float $frecuencia): string
    {
        if (!$frecuencia) return 'sin_dato';
        if ($frecuencia > 30 || $frecuencia < 8) return 'critico';
        if ($frecuencia > self::FR_AVISO_ALTA || $frecuencia < self::FR_AVISO_BAJA) return 'advertencia';
        return 'normal';
    }

    private static function temperatura(?float $temperatura): string
    {
        if (!$temperatura) return 'sin_dato';
        if ($temperatura >= 39.0 || $temperatura < 35.0) return 'critico';
        if ($temperatura >= self::TEMPERATURA_AVISO_ALTA || $temperatura < self::TEMPERATURA_AVISO_BAJA) return 'advertencia';
        return 'normal';
    }

    private static function saturacion(?float $saturacion): string
    {
        if (!$saturacion) return 'sin_dato';
        if ($saturacion < 90) return 'critico';
        if ($saturacion < self::SATURACION_AVISO_BAJA) return 'advertencia';
        return 'normal';
    }

    private static function glucosa(?float $glucosa): string
    {
        if (!$glucosa) return 'sin_dato';
        if ($glucosa > 300 || $glucosa < 60) return 'critico';
        if ($glucosa > self::GLUCOSA_AVISO_ALTA || $glucosa < self::GLUCOSA_AVISO_BAJA) return 'advertencia';
        return 'normal';
    }
}
