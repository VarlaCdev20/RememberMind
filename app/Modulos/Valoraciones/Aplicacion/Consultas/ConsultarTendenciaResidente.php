<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Consultas;

use App\Models\AplicacionInstrumento;
use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Models\ValoracionPsicologica;
use Carbon\Carbon;

/**
 * Consulta de tendencias longitudinales del residente:
 * Genera un contrato plano y desacoplado para visualización temporal de evolución clínica
 * (cognitiva, afectiva o funcional IADL) diferenciando datos guardados, mediciones basales y ausencia de datos.
 */
final class ConsultarTendenciaResidente
{
    /**
     * @return array{
     *     metrica: string,
     *     periodoMeses: int,
     *     tieneDatos: bool,
     *     suficienteParaTendencia: bool,
     *     totalRegistros: int,
     *     labels: list<string>,
     *     datasets: list<array{label: string, data: list<float|int|null>, tone: string, max?: float|int}>,
     *     resumen: array{
     *         totalEvaluaciones: int,
     *         primerFecha: ?string,
     *         ultimaFecha: ?string,
     *         primerValor: ?float,
     *         ultimoValor: ?float,
     *         variacion: ?float,
     *         direccion: string,
     *         interpretacion: string
     *     },
     *     tablaDetalle: list<array{
     *         fecha: string,
     *         fecha_hora: string,
     *         indicador: string,
     *         valor: string,
     *         evaluador: string,
     *         detalle: string
     *     }>
     * }
     */
    public function ejecutar(Residente $residente, string $metrica = 'cognitivo', int $periodoMeses = 6): array
    {
        $codResidente = $residente->getKey();
        $desde = $periodoMeses > 0 ? Carbon::now()->subMonths($periodoMeses)->startOfDay() : null;

        return match ($metrica) {
            'afectivo' => $this->consultarAfectivo($codResidente, $desde, $periodoMeses),
            'iadl' => $this->consultarIadl($codResidente, $desde, $periodoMeses),
            default => $this->consultarCognitivo($codResidente, $desde, $periodoMeses),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function consultarCognitivo(string $codResidente, ?Carbon $desde, int $periodoMeses): array
    {
        $query = AplicacionInstrumento::where('cod_residente', $codResidente)
            ->whereNotNull('fecha_hora')
            ->whereHas('instrumento', fn ($q) => $q->whereIn('tipo', ['COGNITIVO', 'NEUROPSICOLOGICO', 'OBSERVACIONAL']))
            ->with(['instrumento', 'evaluador.usuario'])
            ->orderBy('fecha_hora', 'asc');

        if ($desde !== null) {
            $query->where('fecha_hora', '>=', $desde);
        }

        $aplicaciones = $query->get();

        if ($aplicaciones->isEmpty()) {
            return $this->respuestaVacia('cognitivo', $periodoMeses, 'No se registran instrumentos cognitivos aplicados en el período seleccionado.');
        }

        $labels = [];
        $tablaDetalle = [];
        $instrumentos = [];

        foreach ($aplicaciones as $app) {
            $fecha = $app->fecha_hora->format('d/m/Y');
            if (!in_array($fecha, $labels, true)) {
                $labels[] = $fecha;
            }

            $nombreInst = $app->instrumento->nombre ?? ($app->instrumento->codigo ?? 'Instrumento Cognitivo');
            $puntaje = $app->puntaje_total !== null ? (float) $app->puntaje_total : null;
            $maximo = $app->puntaje_maximo !== null ? (float) $app->puntaje_maximo : null;

            $instrumentos[$nombreInst][] = [
                'fecha' => $fecha,
                'puntaje' => $puntaje,
                'maximo' => $maximo,
            ];

            $evaluador = $app->evaluador?->usuario?->nombre_completo
                ?? ($app->evaluador?->nombres ? $app->evaluador->nombres . ' ' . $app->evaluador->apellido_paterno : 'Personal');

            $tablaDetalle[] = [
                'fecha' => $fecha,
                'fecha_hora' => $app->fecha_hora->format('d/m/Y H:i'),
                'indicador' => $nombreInst,
                'valor' => $puntaje !== null ? ($maximo ? "{$puntaje} / {$maximo}" : "{$puntaje} pts") : 'Sin puntaje',
                'evaluador' => $evaluador,
                'detalle' => $app->interpretacion ?? ($app->clasificacion ?? 'Evaluación estandarizada'),
            ];
        }

        // Construir datasets alineados por fechas
        $datasets = [];
        foreach ($instrumentos as $nombre => $puntos) {
            $data = [];
            $maxPuntaje = null;
            foreach ($labels as $label) {
                $matching = collect($puntos)->firstWhere('fecha', $label);
                $data[] = $matching ? $matching['puntaje'] : null;
                if ($matching && $matching['maximo'] !== null) {
                    $maxPuntaje = $matching['maximo'];
                }
            }

            $datasets[] = [
                'label' => $nombre,
                'data' => $data,
                'tone' => 'cognitive',
                'max' => $maxPuntaje,
            ];
        }

        // Resumen del primer instrumento principal
        $resumen = $this->calcularResumen($datasets[0]['data'] ?? [], $labels, 'cognitivo');

        return [
            'metrica' => 'cognitivo',
            'periodoMeses' => $periodoMeses,
            'tieneDatos' => true,
            'suficienteParaTendencia' => count($labels) >= 2,
            'totalRegistros' => $aplicaciones->count(),
            'labels' => $labels,
            'datasets' => $datasets,
            'resumen' => $resumen,
            'tablaDetalle' => $tablaDetalle,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function consultarAfectivo(string $codResidente, ?Carbon $desde, int $periodoMeses): array
    {
        $query = ValoracionPsicologica::where('cod_residente', $codResidente)
            ->whereNotNull('fecha_hora')
            ->with(['personal'])
            ->orderBy('fecha_hora', 'asc');

        if ($desde !== null) {
            $query->where('fecha_hora', '>=', $desde);
        }

        $valoraciones = $query->get();

        if ($valoraciones->isEmpty()) {
            return $this->respuestaVacia('afectivo', $periodoMeses, 'No se registran valoraciones afectivas en el período seleccionado.');
        }

        $labels = [];
        $tablaDetalle = [];
        $mapaSintoma = [
            'AUSENTE' => 0,
            'LEVE' => 1,
            'MODERADA' => 2,
            'SEVERA' => 3,
        ];

        $serieAnsiedad = [];
        $serieApatia = [];

        foreach ($valoraciones as $val) {
            $fecha = $val->fecha_hora->format('d/m/Y');
            if (!in_array($fecha, $labels, true)) {
                $labels[] = $fecha;
            }

            $nivelAnsiedad = isset($mapaSintoma[$val->ansiedad]) ? $mapaSintoma[$val->ansiedad] : null;
            $nivelApatia = isset($mapaSintoma[$val->apatia]) ? $mapaSintoma[$val->apatia] : null;

            $serieAnsiedad[$fecha] = $nivelAnsiedad;
            $serieApatia[$fecha] = $nivelApatia;

            $evaluador = $val->personal?->nombres
                ? $val->personal->nombres . ' ' . $val->personal->apellido_paterno
                : 'Psicología';

            $tablaDetalle[] = [
                'fecha' => $fecha,
                'fecha_hora' => $val->fecha_hora->format('d/m/Y H:i'),
                'indicador' => 'Ánimo: ' . ($val->estado_animo ?? 'No registrado'),
                'valor' => "Ansiedad: {$val->ansiedad} | Apatía: {$val->apatia}",
                'evaluador' => $evaluador,
                'detalle' => $val->conclusion ?? ($val->afecto ? "Afecto: {$val->afecto}" : 'Valoración psicológica'),
            ];
        }

        $dataAnsiedad = array_map(fn ($l) => $serieAnsiedad[$l] ?? null, $labels);
        $dataApatia = array_map(fn ($l) => $serieApatia[$l] ?? null, $labels);

        $datasets = [
            [
                'label' => 'Nivel de Ansiedad (0=Ausente, 3=Severa)',
                'data' => $dataAnsiedad,
                'tone' => 'alert',
                'max' => 3,
            ],
            [
                'label' => 'Nivel de Apatía (0=Ausente, 3=Severa)',
                'data' => $dataApatia,
                'tone' => 'clinical',
                'max' => 3,
            ],
        ];

        $resumen = $this->calcularResumen($dataAnsiedad, $labels, 'afectivo');

        return [
            'metrica' => 'afectivo',
            'periodoMeses' => $periodoMeses,
            'tieneDatos' => true,
            'suficienteParaTendencia' => count($labels) >= 2,
            'totalRegistros' => $valoraciones->count(),
            'labels' => $labels,
            'datasets' => $datasets,
            'resumen' => $resumen,
            'tablaDetalle' => $tablaDetalle,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function consultarIadl(string $codResidente, ?Carbon $desde, int $periodoMeses): array
    {
        $query = ControlFuncionalIadl::where('cod_residente', $codResidente)
            ->whereNotNull('fecha_hora')
            ->orderBy('fecha_hora', 'asc');

        if ($desde !== null) {
            $query->where('fecha_hora', '>=', $desde);
        }

        $controles = $query->get();

        if ($controles->isEmpty()) {
            return $this->respuestaVacia('iadl', $periodoMeses, 'No se registran observaciones de actividades cotidianas IADL en el período seleccionado.');
        }

        $agrupados = $controles->groupBy(fn ($c) => $c->fecha_hora->format('d/m/Y'));
        $labels = [];
        $dataAutonomia = [];
        $tablaDetalle = [];

        foreach ($agrupados as $fecha => $items) {
            $labels[] = $fecha;
            $total = $items->count();
            $independientes = $items->where('desempeno', 'INDEPENDIENTE')->count();
            $pct = $total > 0 ? (float) round(($independientes / $total) * 100, 1) : null;
            $dataAutonomia[] = $pct;

            foreach ($items as $item) {
                $tablaDetalle[] = [
                    'fecha' => $fecha,
                    'fecha_hora' => $item->fecha_hora->format('d/m/Y H:i'),
                    'indicador' => 'IADL: ' . $item->actividad,
                    'valor' => $item->desempeno,
                    'evaluador' => 'Personal asistencial',
                    'detalle' => "Oportunidad: {$item->oportunidad} | Ayuda: {$item->tipo_ayuda}",
                ];
            }
        }

        $datasets = [
            [
                'label' => 'Autonomía Instrumental (% Actividades Independientes)',
                'data' => $dataAutonomia,
                'tone' => 'care',
                'max' => 100,
            ],
        ];

        $resumen = $this->calcularResumen($dataAutonomia, $labels, 'iadl');

        return [
            'metrica' => 'iadl',
            'periodoMeses' => $periodoMeses,
            'tieneDatos' => true,
            'suficienteParaTendencia' => count($labels) >= 2,
            'totalRegistros' => $controles->count(),
            'labels' => $labels,
            'datasets' => $datasets,
            'resumen' => $resumen,
            'tablaDetalle' => $tablaDetalle,
        ];
    }

    /**
     * @param list<float|int|null> $valores
     * @param list<string> $labels
     * @return array{
     *     totalEvaluaciones: int,
     *     primerFecha: ?string,
     *     ultimaFecha: ?string,
     *     primerValor: ?float,
     *     ultimoValor: ?float,
     *     variacion: ?float,
     *     direccion: string,
     *     interpretacion: string
     * }
     */
    private function calcularResumen(array $valores, array $labels, string $tipo): array
    {
        $valoresValidos = array_values(array_filter($valores, fn ($v) => $v !== null));
        $count = count($valoresValidos);

        if ($count === 0) {
            return [
                'totalEvaluaciones' => 0,
                'primerFecha' => null,
                'ultimaFecha' => null,
                'primerValor' => null,
                'ultimoValor' => null,
                'variacion' => null,
                'direccion' => 'INSUFICIENTE',
                'interpretacion' => 'Sin datos cuantitativos para calcular tendencia.',
            ];
        }

        $primerFecha = $labels[0] ?? null;
        $ultimaFecha = $labels[count($labels) - 1] ?? null;
        $primerValor = (float) $valoresValidos[0];
        $ultimoValor = (float) $valoresValidos[$count - 1];

        if ($count === 1) {
            return [
                'totalEvaluaciones' => 1,
                'primerFecha' => $primerFecha,
                'ultimaFecha' => $ultimaFecha,
                'primerValor' => $primerValor,
                'ultimoValor' => $ultimoValor,
                'variacion' => 0.0,
                'direccion' => 'BASAL',
                'interpretacion' => "Medición basal única el {$primerFecha} ({$primerValor}). Se requiere una segunda evaluación para calcular la pendiente temporal.",
            ];
        }

        $variacion = (float) round($ultimoValor - $primerValor, 2);

        if (abs($variacion) < 0.01) {
            $direccion = 'ESTABLE';
            $interpretacion = "Estabilidad en las evaluaciones ({$primerValor} pts). No se aprecian cambios significativos entre {$primerFecha} y {$ultimaFecha}.";
        } elseif ($variacion > 0) {
            $direccion = $tipo === 'afectivo' ? 'INCREMENTO_SINTOMAS' : 'MEJORIA';
            $textoVariacion = "+{$variacion}";
            $interpretacion = $tipo === 'afectivo'
                ? "Incremento de {$textoVariacion} en el nivel reportado entre {$primerFecha} y {$ultimaFecha}."
                : "Evolución favorable con aumento de {$textoVariacion} puntos entre {$primerFecha} y {$ultimaFecha}.";
        } else {
            $direccion = $tipo === 'afectivo' ? 'REDUCCION_SINTOMAS' : 'DESCENSO';
            $interpretacion = $tipo === 'afectivo'
                ? "Disminución sintomática de {$variacion} entre {$primerFecha} y {$ultimaFecha}."
                : "Descenso de {$variacion} puntos entre {$primerFecha} y {$ultimaFecha}. Recomendable seguimiento clínico estrecho.";
        }

        return [
            'totalEvaluaciones' => $count,
            'primerFecha' => $primerFecha,
            'ultimaFecha' => $ultimaFecha,
            'primerValor' => $primerValor,
            'ultimoValor' => $ultimoValor,
            'variacion' => $variacion,
            'direccion' => $direccion,
            'interpretacion' => $interpretacion,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function respuestaVacia(string $metrica, int $periodoMeses, string $mensaje): array
    {
        return [
            'metrica' => $metrica,
            'periodoMeses' => $periodoMeses,
            'tieneDatos' => false,
            'suficienteParaTendencia' => false,
            'totalRegistros' => 0,
            'labels' => [],
            'datasets' => [],
            'resumen' => [
                'totalEvaluaciones' => 0,
                'primerFecha' => null,
                'ultimaFecha' => null,
                'primerValor' => null,
                'ultimoValor' => null,
                'variacion' => null,
                'direccion' => 'SIN_DATOS',
                'interpretacion' => $mensaje,
            ],
            'tablaDetalle' => [],
        ];
    }
}
