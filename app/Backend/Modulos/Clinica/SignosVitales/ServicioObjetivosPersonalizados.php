<?php

namespace App\Backend\Modulos\Clinica\SignosVitales;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\ComportamientoAlerta;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica;
use App\Models\ObjetivoSignoVital;
use Illuminate\Support\Collection;

final class ServicioObjetivosPersonalizados
{
    /** @return list<ResultadoReglaClinica> */
    public function evaluar(string $codResidente, array $mediciones, ?Collection $objetivosPrecargados = null): array
    {
        $objetivos = $objetivosPrecargados ?? ObjetivoSignoVital::query()->where('cod_residente', $codResidente)
            ->where('estado', 'VIGENTE')->where('vigente_desde', '<=', now())
            ->whereNull('vigente_hasta')->get();
        $resultados = [];
        foreach ($objetivos as $objetivo) {
            if ($objetivo->vigente_hasta !== null || $objetivo->vigente_desde->isFuture()) {
                continue;
            }
            $valor = $mediciones[$objetivo->parametro] ?? null;
            if ($valor === null) {
                continue;
            }
            $critico = ($objetivo->min_critico !== null && $valor <= (float) $objetivo->min_critico)
                || ($objetivo->max_critico !== null && $valor >= (float) $objetivo->max_critico);
            $fuera = ($objetivo->min_objetivo !== null && $valor < (float) $objetivo->min_objetivo)
                || ($objetivo->max_objetivo !== null && $valor > (float) $objetivo->max_objetivo);
            $severidad = $critico ? SeveridadClinica::CRITICO
                : ($fuera ? SeveridadClinica::ADVERTENCIA : SeveridadClinica::OBJETIVO_PERSONALIZADO);
            $rango = ($objetivo->min_objetivo ?? '—').'–'.($objetivo->max_objetivo ?? '—');
            $resultados[] = new ResultadoReglaClinica(
                $objetivo->parametro, (string) $valor, $this->unidad($objetivo->parametro),
                $severidad, 'OBJETIVO_'.$objetivo->parametro,
                'Objetivo médico '.$objetivo->cod_objetivo_signo,
                $rango,
                $critico ? 'La lectura alcanza un límite crítico definido por el médico.'
                    : ($fuera ? 'La lectura está fuera del objetivo individual vigente.'
                        : 'La lectura está dentro del objetivo individual vigente.'),
                $critico ? 'Repetir la medición y seguir el protocolo institucional.' : null,
                $critico ? ComportamientoAlerta::AUTOMATICA_AL_CONFIRMAR : ComportamientoAlerta::NINGUNA,
                'OBJETIVO_MEDICO',
            );
        }

        return $resultados;
    }

    private function unidad(string $parametro): string
    {
        return match ($parametro) {
            'presion_sistolica', 'presion_diastolica' => 'mmHg',
            'frecuencia_cardiaca' => 'lpm',
            'frecuencia_respiratoria' => 'rpm',
            'temperatura' => '°C',
            'saturacion_oxigeno' => '%',
            'glucemia' => 'mg/dL',
        };
    }
}
