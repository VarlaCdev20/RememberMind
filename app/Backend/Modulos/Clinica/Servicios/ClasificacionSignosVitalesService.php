<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Backend\Modulos\Clinica\SignosVitales\EvaluadorSignosVitales;
use App\Backend\Modulos\Clinica\SignosVitales\Resultados\EvaluacionSignosVitales;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica;
use App\Models\SignoVital;

/** Adaptador de lectura para las vistas existentes; no define umbrales clínicos. */
final class ClasificacionSignosVitalesService
{
    public static function leyendaAvisos(): array
    {
        return [
            'pa' => 'PA: referencia <120/<80; atención ≤90 sistólica; crítico >180 sistólica o >120 diastólica',
            'fc' => 'Pulso: referencia 51–90 lpm; crítico ≤40 o ≥131',
            'fr' => 'Respiración: referencia 12–20 rpm; crítico ≤8 o ≥25',
            'temperatura' => 'Temperatura: referencia 36,1–37,7 °C; crítico ≤35,0 o ≥39,1',
            'saturacion' => 'SpO₂: objetivo médico individual cuando exista; sin umbral universal automático',
            'glucosa' => 'Glucemia: <70 requiere atención; <54 crítico; una lectura alta aislada requiere contexto',
        ];
    }

    public static function evaluarRegistro(SignoVital $signo): array
    {
        return self::adaptar(app(EvaluadorSignosVitales::class)->evaluar([
            'presion_sistolica' => $signo->presion_sistolica,
            'presion_diastolica' => $signo->presion_diastolica,
            'frecuencia_cardiaca' => $signo->frecuencia_cardiaca,
            'frecuencia_respiratoria' => $signo->frecuencia_respiratoria,
            'temperatura' => $signo->temperatura,
            'saturacion_oxigeno' => $signo->saturacion_oxigeno,
            'glucemia' => $signo->glucemia,
        ], $signo->cod_residente,
            $signo->relationLoaded('residente') && $signo->residente?->relationLoaded('objetivosSignosVitales')
                ? $signo->residente->objetivosSignosVitales : null,
            false));
    }

    public static function evaluar(
        ?float $sistolica, ?float $diastolica, ?float $frecuenciaCardiaca,
        ?float $frecuenciaRespiratoria, ?float $temperatura,
        ?float $saturacion, ?float $glucosa,
    ): array {
        return self::adaptar(app(EvaluadorSignosVitales::class)->evaluar([
            'presion_sistolica' => $sistolica, 'presion_diastolica' => $diastolica,
            'frecuencia_cardiaca' => $frecuenciaCardiaca,
            'frecuencia_respiratoria' => $frecuenciaRespiratoria,
            'temperatura' => $temperatura, 'saturacion_oxigeno' => $saturacion,
            'glucemia' => $glucosa,
        ]));
    }

    private static function adaptar(EvaluacionSignosVitales $evaluacion): array
    {
        $niveles = array_fill_keys(['pa', 'fc', 'fr', 'temperatura', 'saturacion', 'glucosa'], 'sin_dato');
        foreach ($evaluacion->resultados as $resultado) {
            $clave = match ($resultado->variable) {
                'presion_arterial', 'presion_sistolica', 'presion_diastolica' => 'pa',
                'frecuencia_cardiaca' => 'fc',
                'frecuencia_respiratoria' => 'fr',
                'temperatura' => 'temperatura',
                'saturacion_oxigeno' => 'saturacion',
                'glucemia' => 'glucosa',
            };
            $nivel = self::nivel($resultado->severidad);
            $prioridad = ['sin_dato' => 0, 'normal' => 1, 'advertencia' => 2, 'critico' => 3];
            if ($prioridad[$nivel] > $prioridad[$niveles[$clave]]) {
                $niveles[$clave] = $nivel;
            }
        }
        $niveles['global'] = self::nivel($evaluacion->severidadGlobal());

        return $niveles;
    }

    private static function nivel(?SeveridadClinica $nivel): string
    {
        return match ($nivel) {
            SeveridadClinica::CRITICO => 'critico',
            SeveridadClinica::ALTO, SeveridadClinica::ADVERTENCIA => 'advertencia',
            SeveridadClinica::NORMAL, SeveridadClinica::OBJETIVO_PERSONALIZADO => 'normal',
            null => 'sin_dato',
        };
    }
}
