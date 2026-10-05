<?php

namespace App\Backend\Modulos\Clinica\SignosVitales;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\EvaluacionSignosVitales;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaFrecuenciaCardiaca;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaFrecuenciaRespiratoria;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaGlucemia;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaPresionArterial;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaSaturacionOxigeno;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaTemperatura;
use Illuminate\Support\Collection;

final class EvaluadorSignosVitales
{
    public function __construct(
        private readonly ServicioBasalSignosVitales $basal,
        private readonly ServicioObjetivosPersonalizados $objetivos,
    ) {}

    /** @param array<string, mixed> $mediciones */
    public function evaluar(array $mediciones, ?string $codResidente = null, ?Collection $objetivosPrecargados = null, bool $incluirHistorial = true): EvaluacionSignosVitales
    {
        $historial = $codResidente && $incluirHistorial ? $this->basal->lecturasRecientes($codResidente) : [];
        $reglas = [
            new ReglaPresionArterial, new ReglaFrecuenciaCardiaca,
            new ReglaFrecuenciaRespiratoria, new ReglaTemperatura,
            new ReglaSaturacionOxigeno, new ReglaGlucemia,
        ];
        $resultados = [];
        foreach ($reglas as $regla) {
            if ($resultado = $regla->evaluar($mediciones)) $resultados[] = $resultado;
        }

        if ($codResidente !== null) {
            $individuales = $this->objetivos->evaluar($codResidente, $mediciones, $objetivosPrecargados);
            foreach ($individuales as $individual) {
                $variableGeneral = str_starts_with($individual->variable, 'presion_')
                    ? 'presion_arterial' : $individual->variable;
                $resultados = array_values(array_filter($resultados,
                    fn ($general) => $general->variable !== $variableGeneral
                        || $general->severidad === \App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica::CRITICO));
                $resultados[] = $individual;
            }
        }

        return new EvaluacionSignosVitales($resultados, $historial);
    }
}
