<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Modulos\Clinica\Aplicacion\Consultas\ServicioBasalSignosVitales;
use App\Modulos\Clinica\Aplicacion\Consultas\ServicioObjetivosPersonalizados;
use App\Modulos\Clinica\Dominio\Enumeraciones\SeveridadClinica;
use App\Modulos\Clinica\Dominio\ObjetosValor\EvaluacionSignosVitales;
use App\Modulos\Clinica\Dominio\Reglas\ReglaFrecuenciaCardiaca;
use App\Modulos\Clinica\Dominio\Reglas\ReglaFrecuenciaRespiratoria;
use App\Modulos\Clinica\Dominio\Reglas\ReglaGlucemia;
use App\Modulos\Clinica\Dominio\Reglas\ReglaPresionArterial;
use App\Modulos\Clinica\Dominio\Reglas\ReglaSaturacionOxigeno;
use App\Modulos\Clinica\Dominio\Reglas\ReglaTemperatura;
use Carbon\Carbon;
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
        $fechaMedicion = isset($mediciones['fecha_hora']) ? Carbon::parse($mediciones['fecha_hora']) : now();
        $historial = $codResidente && $incluirHistorial ? $this->basal->lecturasRecientes($codResidente, fechaMedicion: $fechaMedicion) : [];
        $reglas = [
            new ReglaPresionArterial(config('signos_vitales.presion')), new ReglaFrecuenciaCardiaca(config('signos_vitales.pulso')),
            new ReglaFrecuenciaRespiratoria(config('signos_vitales.respiracion')), new ReglaTemperatura(config('signos_vitales.temperatura')),
            new ReglaSaturacionOxigeno, new ReglaGlucemia(config('signos_vitales.glucemia')),
        ];
        $resultados = [];
        foreach ($reglas as $regla) {
            if ($resultado = $regla->evaluar($mediciones)) {
                $resultados[] = $resultado;
            }
        }

        if ($codResidente !== null) {
            $individuales = $this->objetivos->evaluar($codResidente, $mediciones, $objetivosPrecargados, $fechaMedicion);
            foreach ($individuales as $individual) {
                $variableGeneral = str_starts_with($individual->variable, 'presion_')
                    ? 'presion_arterial' : $individual->variable;
                $resultados = array_values(array_filter($resultados,
                    fn ($general) => $general->variable !== $variableGeneral
                        || $general->severidad === SeveridadClinica::CRITICO));
                $resultados[] = $individual;
            }
        }

        return new EvaluacionSignosVitales($resultados, $historial);
    }
}
