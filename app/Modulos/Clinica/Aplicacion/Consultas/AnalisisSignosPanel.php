<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\SignoVital;
use App\Modulos\Clinica\Aplicacion\Acciones\EvaluadorSignosVitales;
use Illuminate\Support\Collection;

final class AnalisisSignosPanel
{
    private array $interpretaciones = [];

    public function __construct(private readonly InterpretacionSignosVitales $interpretacion) {}

    public function preparar(Collection $signos): void
    {
        $this->interpretaciones = $this->interpretacion->coleccion($signos)->all();
    }

    public function detectarAlertas(array $entrada, ?string $codResidente = null): array
    {
        $mediciones = [];
        foreach (['presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca',
            'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno', 'glucemia'] as $campo) {
            $alias = match ($campo) { 'saturacion_oxigeno' => 'saturacion', 'glucemia' => 'glucosa', default => $campo };
            $valor = $entrada[$campo] ?? $entrada[$alias] ?? null;
            $mediciones[$campo] = is_numeric($valor) ? (float) $valor : null;
        }
        if (array_key_exists('fecha_hora', $entrada)) {
            $fecha = $entrada['fecha_hora'];
            if (is_string($fecha) && ! \Illuminate\Support\Facades\Validator::make(
                ['fecha_hora' => $fecha], ['fecha_hora' => 'required|date_format:Y-m-d H:i']
            )->fails()) {
                $mediciones['fecha_hora'] = $fecha;
            } else {
                // Sin fecha válida no se aplica el objetivo de otra vigencia.
                $codResidente = null;
            }
        }
        return collect(app(EvaluadorSignosVitales::class)->evaluar($mediciones, $codResidente,
            incluirHistorial: false)->toArray()['resultados'])
            ->filter(fn (array $r) => in_array($r['severidad'], ['ADVERTENCIA', 'ALTO', 'CRITICO'], true)
                || $r['comportamiento_alerta'] === 'SUGERIR')
            ->map(fn (array $r) => ['campo' => $r['variable'], 'mensaje' => $r['explicacion'], 'severidad' => $r['severidad']])->values()->all();
    }

    public function construirMetricas(Collection $signos): array
    {
        return [
            $this->metricaPresion($signos),
            $this->metricaNumerica($signos, 'frecuencia_cardiaca', 'Frecuencia cardíaca', 'lpm', 'ph-pulse', 3, 'FC'),
            $this->metricaNumerica($signos, 'saturacion', 'Saturación de oxígeno', '%', 'ph-wave-sine', 1, 'SPO2'),
            $this->metricaNumerica($signos, 'temperatura', 'Temperatura', '°C', 'ph-thermometer', 0.2, 'TEMP', 1),
            $this->metricaNumerica($signos, 'frecuencia_respiratoria', 'Frecuencia respiratoria', 'rpm', 'ph-wind', 1, 'FR'),
            $this->metricaNumerica($signos, 'glucosa', 'Glucemia', 'mg/dL', 'ph-drop', 1, 'GLUCOSA'),
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

        $estado = $this->analizarRegistro($ultimo)['metricas']['PA'];

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

    private function metricaNumerica(Collection $signos, string $campo, string $label, string $unidad, string $icono, float $tolerancia, string $metrica, int $decimales = 0): array
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
        $estado = $this->analizarRegistro($ultimo)['metricas'][$metrica];
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
        $normales = $analisis->whereIn('key', ['normal', 'objetivo_personalizado'])->count();
        $fueraRango = $analisis->whereIn('key', ['observacion', 'fuera_rango', 'requiere_revision'])->count();
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
            'sin_datos' => 0, 'sin_interpretacion' => 0, 'objetivo_personalizado' => 0,
        ];

        foreach ($signos as $signo) {
            $estado = $this->analizarRegistro($signo)['key'];
            if (array_key_exists($estado, $conteos)) {
                $conteos[$estado]++;
            }
        }

        $prioridad = collect(['requiere_revision', 'fuera_rango', 'observacion', 'sin_interpretacion', 'objetivo_personalizado', 'normal', 'sin_datos'])
            ->first(fn ($key) => ($conteos[$key] ?? 0) > 0) ?? 'sin_datos';

        $mensajes = [
            'normal' => 'Los parámetros clasificados están en referencia. Consulte los detalles de cada toma.',
            'objetivo_personalizado' => 'Hay parámetros dentro del objetivo médico válido en su fecha de medición.',
            'sin_interpretacion' => 'Hay mediciones sin interpretación automática aprobada. Consulte los detalles.',
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
        return $this->interpretaciones[$signo->cod_signo] ?? $this->interpretacion->registro($signo);
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
