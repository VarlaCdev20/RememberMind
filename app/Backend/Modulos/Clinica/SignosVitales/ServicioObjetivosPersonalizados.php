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
    public function vigentesEn(string $codResidente, \Carbon\CarbonInterface $fecha): Collection
    {
        return ObjetivoSignoVital::query()->where('cod_residente', $codResidente)
            ->whereIn('estado', ['VIGENTE', 'REEMPLAZADO', 'ANULADO'])
            ->where(fn ($q) => $q->where('estado', 'VIGENTE')->orWhereNotNull('vigente_hasta'))
            ->where('vigente_desde', '<=', $fecha)
            ->where(fn ($q) => $q->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>', $fecha))
            ->with('medico')->get();
    }

    public function evaluar(string $codResidente, array $mediciones, ?Collection $objetivosPrecargados = null, ?\Carbon\CarbonInterface $fechaMedicion = null): array
    {
        $fechaMedicion ??= now();
        $objetivos = $objetivosPrecargados ?? $this->vigentesEn($codResidente, $fechaMedicion);
        $resultados = [];
        foreach ($objetivos as $objetivo) {
            if ($objetivo->cod_residente !== $codResidente || $objetivo->vigente_desde->gt($fechaMedicion)
                || ! in_array($objetivo->estado, ['VIGENTE', 'REEMPLAZADO', 'ANULADO'], true)
                || ($objetivo->vigente_hasta !== null && $objetivo->vigente_hasta->lte($fechaMedicion))
                || ($objetivo->estado !== 'VIGENTE' && $objetivo->vigente_hasta === null)) {
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
            $descripcion = ObjetivoSignoVital::PARAMETROS[$objetivo->parametro].': '.$valor.' '.$this->unidad($objetivo->parametro).'. ';
            if ($critico) {
                $descripcion .= $objetivo->min_critico !== null && $valor <= (float) $objetivo->min_critico
                    ? 'Alcanza el límite crítico inferior indicado por el médico (≤'.$objetivo->min_critico.' '.$this->unidad($objetivo->parametro).').'
                    : 'Alcanza el límite crítico superior indicado por el médico (≥'.$objetivo->max_critico.' '.$this->unidad($objetivo->parametro).').';
            } else {
                $descripcion .= $fuera
                    ? 'Está fuera del objetivo individual vigente de '.$rango.' '.$this->unidad($objetivo->parametro).'.'
                    : 'Está dentro del objetivo individual vigente de '.$rango.' '.$this->unidad($objetivo->parametro).'.';
            }
            $resultados[] = new ResultadoReglaClinica(
                $objetivo->parametro, (string) $valor, $this->unidad($objetivo->parametro),
                $severidad, 'OBJETIVO_'.$objetivo->parametro,
                'Objetivo médico '.$objetivo->cod_objetivo_signo,
                $rango,
                $descripcion,
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
