<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TendenciaIncidentesService
{
    /**
     * Resume incidentes registrados de los residentes visibles en el turno.
     * La BDD usa datetime local sin zona; los límites siguen config('app.timezone').
     */
    public function resumir(array $codResidentes, ?CarbonImmutable $ahora = null): array
    {
        $hoy = ($ahora ?? CarbonImmutable::now(config('app.timezone')))
            ->setTimezone(config('app.timezone'))->startOfDay();
        $codigos = array_values(array_unique(array_filter($codResidentes)));

        if ($codigos === []) {
            return $this->agrupar(collect(), $hoy);
        }

        $filas = DB::table('incidentes')
            ->whereIn('cod_residente', $codigos)
            ->where('fecha_hora', '>=', $hoy->subDays(13)->toDateTimeString())
            ->where('fecha_hora', '<', $hoy->addDay()->toDateTimeString())
            ->selectRaw('DATE(fecha_hora) AS dia, tipo_incidente, COUNT(*) AS total')
            ->groupByRaw('DATE(fecha_hora), tipo_incidente')
            ->get();

        return $this->agrupar($filas, $hoy);
    }

    /** @param Collection<int, object> $filas Agregados {dia, tipo_incidente, total} de la consulta única. */
    public function agrupar(Collection $filas, CarbonImmutable $hoy): array
    {
        $hoy = $hoy->setTimezone(config('app.timezone'))->startOfDay();
        $inicioActual = $hoy->subDays(6);
        $inicioAnterior = $hoy->subDays(13);
        $totalesDia = [];
        $porTipo = [];
        $totalAnterior = 0;

        foreach ($filas as $fila) {
            $dia = (string) $fila->dia;
            $tipo = mb_strtoupper(trim((string) $fila->tipo_incidente));
            $tipo = $tipo !== '' ? $tipo : 'SIN TIPO REGISTRADO';
            $cantidad = (int) $fila->total;

            if ($dia >= $inicioActual->toDateString() && $dia <= $hoy->toDateString()) {
                $totalesDia[$dia] = ($totalesDia[$dia] ?? 0) + $cantidad;
                $porTipo[$tipo]['total'] = ($porTipo[$tipo]['total'] ?? 0) + $cantidad;
                $porTipo[$tipo]['dias'][$dia] = ($porTipo[$tipo]['dias'][$dia] ?? 0) + $cantidad;
            } elseif ($dia >= $inicioAnterior->toDateString() && $dia < $inicioActual->toDateString()) {
                $totalAnterior += $cantidad;
            }
        }

        $fechas = [];
        $labels = [];
        $labelsCompletas = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $fecha = $hoy->subDays($offset);
            $fechas[] = $fecha->toDateString();
            $labels[] = $offset === 0 ? 'Hoy' : ucfirst($fecha->locale('es')->translatedFormat('D'));
            $labelsCompletas[] = ucfirst($fecha->locale('es')->translatedFormat('l d M'));
        }

        $totalActual = array_sum($totalesDia);
        $tiposOrdenados = array_keys($porTipo);
        usort($tiposOrdenados, fn ($a, $b) => ($porTipo[$b]['total'] <=> $porTipo[$a]['total']) ?: strcmp($a, $b));
        $cantidadTipos = count($tiposOrdenados);
        $visibles = $cantidadTipos > 3 ? array_slice($tiposOrdenados, 0, 2) : $tiposOrdenados;
        $colores = ['var(--rm-coral-500)', 'var(--rm-sky-500)', 'var(--rm-mint-500)'];
        $datasets = [];

        foreach ($visibles as $indice => $tipo) {
            $datasets[] = [
                'label' => mb_convert_case(mb_strtolower($tipo), MB_CASE_TITLE, 'UTF-8'),
                'data' => array_map(fn ($fecha) => $porTipo[$tipo]['dias'][$fecha] ?? 0, $fechas),
                'color' => $colores[$indice],
            ];
        }
        if ($cantidadTipos > 3) {
            $restantes = array_slice($tiposOrdenados, 2);
            $datasets[] = [
                'label' => 'Otros',
                'data' => array_map(function ($fecha) use ($restantes, $porTipo) {
                    return array_sum(array_map(fn ($tipo) => $porTipo[$tipo]['dias'][$fecha] ?? 0, $restantes));
                }, $fechas),
                'color' => $colores[2],
            ];
        }
        if ($datasets === []) {
            $datasets[] = ['label' => 'Incidentes', 'data' => array_fill(0, 7, 0), 'color' => 'var(--rm-sky-500)'];
        }

        $maximo = $tiposOrdenados ? $porTipo[$tiposOrdenados[0]]['total'] : 0;
        $empatados = array_values(array_filter($tiposOrdenados, fn ($tipo) => $porTipo[$tipo]['total'] === $maximo));
        $categoriaPrincipal = $maximo === 0 ? null : (count($empatados) > 1
            ? 'Varias categorías'
            : mb_convert_case(mb_strtolower($empatados[0]), MB_CASE_TITLE, 'UTF-8'));

        $variacion = $totalAnterior > 0
            ? (int) round(($totalActual - $totalAnterior) / $totalAnterior * 100)
            : null;
        $variacionTexto = $totalAnterior === 0
            ? ($totalActual === 0 ? 'Sin cambios' : 'Nuevos incidentes en este periodo')
            : ($variacion === 0 ? 'Sin cambios' : (($variacion > 0 ? '↑ ' : '↓ ').abs($variacion).'%'));

        return [
            'labels' => $labels,
            'labels_completas' => $labelsCompletas,
            'datasets' => $datasets,
            'total_periodo' => $totalActual,
            'total_anterior' => $totalAnterior,
            'variacion' => $variacion,
            'variacion_texto' => $variacionTexto,
            'variacion_tono' => $variacion === null ? 'neutral' : ($variacion > 0 ? 'increase' : ($variacion < 0 ? 'decrease' : 'neutral')),
            'categoria_principal' => $categoriaPrincipal,
            'categoria_principal_total' => $maximo,
            'inicio_periodo' => $inicioActual->toDateString(),
            'fin_periodo' => $hoy->toDateString(),
            'inicio_anterior' => $inicioAnterior->toDateString(),
            'fin_anterior' => $inicioActual->subDay()->toDateString(),
        ];
    }
}
