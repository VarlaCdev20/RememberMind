<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\SignoVital;
use App\Modulos\Clinica\Dominio\Reglas\ClasificacionSignosPanel;
use Illuminate\Support\Collection;

final class AnalisisSignosPanel
{
    public function __construct(private readonly ClasificacionSignosPanel $clasificacion = new ClasificacionSignosPanel) {}

    public function detectarAlertas(array $entrada): array
    {
        $alertas = [];

        $this->clasificacion->agregarAlerta($alertas, $this->clasificacion->clasificarPresion($entrada['presion_sistolica'], $entrada['presion_diastolica']), 'Presión arterial');
        $this->clasificacion->agregarAlerta($alertas, $this->clasificacion->clasificarNumero($entrada['frecuencia_cardiaca'], 60, 100, null, null, 'lpm'), 'Frecuencia cardíaca');
        $this->clasificacion->agregarAlerta($alertas, $this->clasificacion->clasificarNumero($entrada['saturacion'], 95, null, 92, 94, '%'), 'Saturación de oxígeno');
        $this->clasificacion->agregarAlerta($alertas, $this->clasificacion->clasificarNumero($entrada['temperatura'], 36.0, 37.5, null, null, '°C'), 'Temperatura');
        $this->clasificacion->agregarAlerta($alertas, $this->clasificacion->clasificarNumero($entrada['frecuencia_respiratoria'], 12, 20, null, null, 'rpm'), 'Frecuencia respiratoria');

        if ($entrada['glucosa'] !== null && $entrada['glucosa'] !== '') {
            $glucosa = (float) $entrada['glucosa'];
            if ($glucosa < 70 || $glucosa >= 180) {
                $alertas[] = [
                    'campo' => 'Glucosa',
                    'mensaje' => 'Valor fuera del rango referencial interno. Verifique antes de guardar.',
                ];
            }
        }

        return $alertas;
    }

    public function construirMetricas(Collection $signos): array
    {
        return [
            $this->metricaPresion($signos),
            $this->metricaNumerica($signos, 'frecuencia_cardiaca', 'Frecuencia cardíaca', 'lpm', 'ph-pulse', 3, fn ($valor) => $this->clasificacion->clasificarNumero($valor, 60, 100, null, null, 'lpm')),
            $this->metricaNumerica($signos, 'saturacion', 'Saturación de oxígeno', '%', 'ph-wave-sine', 1, fn ($valor) => $this->clasificacion->clasificarNumero($valor, 95, null, 92, 94, '%')),
            $this->metricaNumerica($signos, 'temperatura', 'Temperatura', '°C', 'ph-thermometer', 0.2, fn ($valor) => $this->clasificacion->clasificarNumero($valor, 36.0, 37.5, null, null, '°C'), 1),
            $this->metricaNumerica($signos, 'frecuencia_respiratoria', 'Frecuencia respiratoria', 'rpm', 'ph-wind', 1, fn ($valor) => $this->clasificacion->clasificarNumero($valor, 12, 20, null, null, 'rpm')),
            $this->metricaNumerica($signos, 'peso', 'Peso registrado', 'kg', 'ph-scales', 0.3, fn () => ['key' => 'normal', 'label' => 'Evolución', 'message' => 'Seguimiento antropométrico'], 1),
            $this->metricaNumerica($signos, 'imc', 'IMC calculado', 'kg/m²', 'ph-chart-line-up', 0.3, fn () => ['key' => 'normal', 'label' => 'Referencial', 'message' => 'Interpretar con contexto clínico'], 1),
        ];
    }

    private function metricaPresion(Collection $signos): array
    {
        $registros = $signos
            ->filter(fn ($signo) => $signo->presion_sistolica !== null && $signo->presion_diastolica !== null)
            ->values();

        $ultimo = $registros->first();
        $previo = $registros->get(1);

        if (! $ultimo) {
            return $this->metricaSinDatos('Presión arterial', 'mmHg', 'ph-heartbeat');
        }

        $estado = $this->clasificacion->clasificarPresion($ultimo->presion_sistolica, $ultimo->presion_diastolica);

        return [
            'key' => 'presion',
            'label' => 'Presión arterial',
            'value' => "{$ultimo->presion_sistolica}/{$ultimo->presion_diastolica}",
            'unit' => 'mmHg',
            'icon' => 'ph-heartbeat',
            'date' => $this->formatearFechaHora($ultimo),
            'status_key' => $estado['key'],
            'status_label' => $estado['label'],
            'variation' => $this->variacionPresion($ultimo, $previo),
            'avg' => $this->promedioPresion($registros),
            'min' => $this->minMaxPresion($registros, 'min'),
            'max' => $this->minMaxPresion($registros, 'max'),
        ];
    }

    private function metricaNumerica(Collection $signos, string $campo, string $label, string $unidad, string $icono, float $tolerancia, callable $clasificador, int $decimales = 0): array
    {
        $registros = $signos
            ->filter(fn ($signo) => $signo->{$campo} !== null && $signo->{$campo} !== '')
            ->values();

        $ultimo = $registros->first();
        $previo = $registros->get(1);

        if (! $ultimo) {
            return $this->metricaSinDatos($label, $unidad, $icono);
        }

        $valor = (float) $ultimo->{$campo};
        $estado = $clasificador($valor);
        $valores = $registros->pluck($campo)->map(fn ($item) => (float) $item);

        return [
            'key' => $campo,
            'label' => $label,
            'value' => number_format($valor, $decimales),
            'unit' => $unidad,
            'icon' => $icono,
            'date' => $this->formatearFechaHora($ultimo),
            'status_key' => $estado['key'],
            'status_label' => $estado['label'],
            'variation' => $this->variacionNumerica($ultimo->{$campo}, $previo?->{$campo}, $tolerancia, $unidad, $decimales),
            'avg' => $valores->isNotEmpty() ? number_format($valores->avg(), $decimales)." {$unidad}" : 'S/D',
            'min' => $valores->isNotEmpty() ? number_format($valores->min(), $decimales)." {$unidad}" : 'S/D',
            'max' => $valores->isNotEmpty() ? number_format($valores->max(), $decimales)." {$unidad}" : 'S/D',
        ];
    }

    private function metricaSinDatos(string $label, string $unidad, string $icono): array
    {
        return [
            'key' => str($label)->slug('_')->toString(),
            'label' => $label,
            'value' => 'S/D',
            'unit' => $unidad,
            'icon' => $icono,
            'date' => 'Sin mediciones',
            'status_key' => 'sin_datos',
            'status_label' => 'Sin datos',
            'variation' => 'Información insuficiente',
            'avg' => 'S/D',
            'min' => 'S/D',
            'max' => 'S/D',
        ];
    }

    public function construirResumenPeriodo(Collection $signos, Collection $baseSignos): array
    {
        $analisis = $signos->map(fn ($signo) => $this->analizarRegistro($signo));
        $normales = $analisis->where('key', 'normal')->count();
        $fueraRango = $analisis->whereIn('key', ['fuera_rango', 'requiere_revision'])->count();
        $mesActual = $baseSignos
            ->where('estado', 'VIGENTE')
            ->filter(fn ($signo) => $signo->fecha && $signo->fecha->isSameMonth(now()))
            ->count();

        return [
            'mediciones_periodo' => $signos->count(),
            'mediciones_mes' => $mesActual,
            'fuera_rango' => $fueraRango,
            'porcentaje_normales' => $signos->count() > 0 ? round(($normales / $signos->count()) * 100) : 0,
            'ultima_medicion' => $baseSignos->firstWhere('estado', 'VIGENTE')
                ? $this->formatearFechaHora($baseSignos->firstWhere('estado', 'VIGENTE'))
                : 'Sin registros',
            'sin_medicion_reciente' => ! $baseSignos->firstWhere('estado', 'VIGENTE')
                || $baseSignos->firstWhere('estado', 'VIGENTE')->fecha->lt(now()->subDays(7)),
        ];
    }

    public function construirInterpretacion(Collection $signos): array
    {
        $conteos = [
            'normal' => 0,
            'observacion' => 0,
            'fuera_rango' => 0,
            'requiere_revision' => 0,
            'sin_datos' => 0,
        ];

        foreach ($signos as $signo) {
            $estado = $this->analizarRegistro($signo)['key'];
            if (array_key_exists($estado, $conteos)) {
                $conteos[$estado]++;
            }
        }

        $prioridad = collect(['requiere_revision', 'fuera_rango', 'observacion', 'normal', 'sin_datos'])
            ->first(fn ($key) => ($conteos[$key] ?? 0) > 0) ?? 'sin_datos';

        $mensajes = [
            'normal' => 'Los registros filtrados se mantienen dentro de rangos referenciales normales.',
            'observacion' => 'Existen valores en observación. Conviene confirmar tendencia con próximos controles.',
            'fuera_rango' => 'Hay valores fuera del rango referencial. Requieren revisión del personal de salud.',
            'requiere_revision' => 'Se detectan controles que requieren revisión prioritaria del personal de salud.',
            'sin_datos' => 'Información insuficiente para análisis estadístico.',
        ];

        return [
            'conteos' => $conteos,
            'estado_principal' => $prioridad,
            'mensaje' => $mensajes[$prioridad],
        ];
    }

    public function analizarRegistro(SignoVital $signo): array
    {
        if ($signo->estado === 'ANULADO') {
            return [
                'key' => 'anulado',
                'label' => 'Anulado',
                'message' => 'Registro clínico anulado, conservado por trazabilidad.',
                'motivos' => ['Registro anulado'],
            ];
        }

        $evaluaciones = [];

        $this->clasificacion->agregarEvaluacion($evaluaciones, $this->clasificacion->clasificarPresion($signo->presion_sistolica, $signo->presion_diastolica), 'Presión arterial');
        $this->clasificacion->agregarEvaluacion($evaluaciones, $this->clasificacion->clasificarNumero($signo->frecuencia_cardiaca, 60, 100, null, null, 'lpm'), 'Frecuencia cardíaca');
        $this->clasificacion->agregarEvaluacion($evaluaciones, $this->clasificacion->clasificarNumero($signo->saturacion, 95, null, 92, 94, '%'), 'Saturación de oxígeno');
        $this->clasificacion->agregarEvaluacion($evaluaciones, $this->clasificacion->clasificarNumero($signo->temperatura, 36.0, 37.5, null, null, '°C'), 'Temperatura');
        $this->clasificacion->agregarEvaluacion($evaluaciones, $this->clasificacion->clasificarNumero($signo->frecuencia_respiratoria, 12, 20, null, null, 'rpm'), 'Frecuencia respiratoria');

        if (empty($evaluaciones)) {
            return [
                'key' => 'sin_datos',
                'label' => 'Sin datos',
                'message' => 'Sin parámetros clasificables en este registro.',
                'motivos' => [],
            ];
        }

        $motivos = collect($evaluaciones)
            ->filter(fn ($item) => in_array($item['key'], ['observacion', 'fuera_rango', 'requiere_revision'], true))
            ->map(fn ($item) => "{$item['campo']}: {$item['message']}")
            ->values()
            ->all();

        if (collect($evaluaciones)->contains('key', 'requiere_revision')) {
            return [
                'key' => 'requiere_revision',
                'label' => 'Requiere revisión',
                'message' => 'Valor fuera del rango referencial con prioridad de revisión.',
                'motivos' => $motivos,
            ];
        }

        if (collect($evaluaciones)->contains('key', 'fuera_rango')) {
            return [
                'key' => 'fuera_rango',
                'label' => 'Fuera de rango',
                'message' => 'Uno o más parámetros están fuera del rango referencial.',
                'motivos' => $motivos,
            ];
        }

        if (collect($evaluaciones)->contains('key', 'observacion')) {
            return [
                'key' => 'observacion',
                'label' => 'En observación',
                'message' => 'Uno o más parámetros requieren seguimiento.',
                'motivos' => $motivos,
            ];
        }

        return [
            'key' => 'normal',
            'label' => 'Normal',
            'message' => 'Parámetros dentro de rangos referenciales.',
            'motivos' => [],
        ];
    }

    private function variacionPresion(?SignoVital $ultimo, ?SignoVital $previo): string
    {
        if (! $ultimo || ! $previo || $previo->presion_sistolica === null) {
            return 'Sin dato previo';
        }

        $delta = $ultimo->presion_sistolica - $previo->presion_sistolica;

        if (abs($delta) <= 3) {
            return 'Estable respecto al último registro';
        }

        return $delta > 0
            ? "Subió {$delta} mmHg en sistólica"
            : 'Bajó '.abs($delta).' mmHg en sistólica';
    }

    private function variacionNumerica($actual, $previo, float $tolerancia, string $unidad, int $decimales): string
    {
        if ($previo === null || $previo === '') {
            return 'Sin dato previo';
        }

        $delta = (float) $actual - (float) $previo;

        if (abs($delta) <= $tolerancia) {
            return 'Estable respecto al último registro';
        }

        $deltaFormateado = number_format(abs($delta), $decimales);

        return $delta > 0
            ? "Subió {$deltaFormateado} {$unidad}"
            : "Bajó {$deltaFormateado} {$unidad}";
    }

    private function promedioPresion(Collection $registros): string
    {
        if ($registros->isEmpty()) {
            return 'S/D';
        }

        return round($registros->avg('presion_sistolica')).'/'.round($registros->avg('presion_diastolica')).' mmHg';
    }

    private function minMaxPresion(Collection $registros, string $modo): string
    {
        if ($registros->isEmpty()) {
            return 'S/D';
        }

        $sistolica = $modo === 'min' ? $registros->min('presion_sistolica') : $registros->max('presion_sistolica');
        $diastolica = $modo === 'min' ? $registros->min('presion_diastolica') : $registros->max('presion_diastolica');

        return "{$sistolica}/{$diastolica} mmHg";
    }

    public function formatearFechaHora(SignoVital $signo): string
    {
        return $signo->fecha->format('d/m/Y').' '.$signo->hora_formateada;
    }
}
