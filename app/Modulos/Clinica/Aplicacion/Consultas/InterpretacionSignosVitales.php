<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\ObjetivoSignoVital;
use App\Models\SignoVital;
use App\Modulos\Clinica\Aplicacion\Acciones\EvaluadorSignosVitales;
use App\Modulos\Clinica\Dominio\Enumeraciones\SeveridadClinica;
use Illuminate\Support\Collection;

/** Lectura compartida: representa resultados del motor; nunca reclasifica valores. */
final class InterpretacionSignosVitales
{
    public const VARIABLES = [
        'PA' => ['presion_arterial', 'presion_sistolica', 'presion_diastolica'],
        'FC' => ['frecuencia_cardiaca'], 'FR' => ['frecuencia_respiratoria'],
        'TEMP' => ['temperatura'], 'SPO2' => ['saturacion_oxigeno'], 'GLUCOSA' => ['glucemia'],
    ];

    public function __construct(private readonly EvaluadorSignosVitales $evaluador) {}

    /** Precarga todas las versiones, incluidas las reemplazadas válidas en la fecha de la toma. */
    public function coleccion(Collection $signos): Collection
    {
        if ($signos->isEmpty()) {
            return collect();
        }
        $objetivos = ObjetivoSignoVital::query()
            ->whereIn('cod_residente', $signos->pluck('cod_residente')->filter()->unique())->get();

        return $signos->mapWithKeys(fn (SignoVital $signo) => [
            $signo->cod_signo => $this->registro($signo, $objetivos),
        ]);
    }

    public function registro(SignoVital $signo, ?Collection $objetivos = null): array
    {
        $resultados = $this->evaluador->evaluar($signo->only([
            'fecha_hora', 'presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca',
            'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno', 'glucemia',
        ]), $signo->cod_residente, $objetivos, incluirHistorial: false)->toArray()['resultados'];

        $estado = $this->resumir($resultados);
        if ($signo->estado === 'ANULADO') {
            $estado = array_replace($estado, ['key' => 'anulado', 'label' => 'Anulado',
                'message' => 'Registro anulado, conservado por trazabilidad.']);
        }

        return $estado + ['metricas' => collect(self::VARIABLES)->map(
            fn (array $variables) => $this->resumir(array_values(array_filter($resultados,
                fn (array $resultado) => in_array($resultado['variable'], $variables, true))))
        )->all()];
    }

    /** La severidad procede del motor. Una medición sin regla no equivale a normalidad. */
    public function resumir(array $resultados): array
    {
        $ordenados = collect($resultados)->sortByDesc(fn (array $r) =>
            SeveridadClinica::tryFrom($r['severidad'] ?? '')?->prioridad() ?? -1);
        $principal = $ordenados->first();
        $severidad = $principal['severidad'] ?? null;
        $revision = collect($resultados)->contains('comportamiento_alerta', 'SUGERIR');
        $sinInterpretar = collect($resultados)->contains(fn (array $r) => $r['severidad'] === null);
        [$key, $label, $tono] = match (true) {
            $severidad === 'CRITICO' => ['requiere_revision', 'Crítico', 'danger'],
            $severidad === 'ALTO' => ['fuera_rango', 'Alto', 'warning'],
            $severidad === 'ADVERTENCIA' => ['observacion', 'Advertencia', 'warning'],
            $revision => ['observacion', 'Revisión sugerida', 'warning'],
            $severidad === 'OBJETIVO_PERSONALIZADO' => ['objetivo_personalizado', $sinInterpretar ? 'En objetivo médico (parcial)' : 'En objetivo médico', 'success'],
            $severidad === 'NORMAL' => ['normal', $sinInterpretar ? 'En referencia (parcial)' : 'En referencia', 'success'],
            $sinInterpretar => ['sin_interpretacion', 'Sin interpretación automática', 'neutral'],
            default => ['sin_datos', 'Sin datos', 'neutral'],
        };
        $badge = match ($tono) {
            'danger' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)] border-[var(--rm-danger-strong)]',
            'warning' => 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] border-[var(--rm-warning-strong)]',
            'success' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border-[var(--rm-success-strong)]',
            default => 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]',
        };
        $motivos = collect($resultados)->pluck('explicacion')->all();

        return ['key' => $key, 'label' => $label, 'message' => implode(' ', $motivos) ?: 'Sin mediciones registradas.',
            'motivos' => $motivos, 'estado_badge' => $badge, 'severidad' => $severidad, 'resultados' => $resultados];
    }

    public function fila(SignoVital $signo, array $interpretacion): array
    {
        return [
            'id' => $signo->cod_signo, 'fecha' => $signo->fecha_hora?->format('d/m/Y') ?? 'No registrada',
            'hora' => $signo->fecha_hora?->format('H:i') ?? '',
            'pa' => $signo->presion_arterial ?? '--', 'pas' => $signo->presion_sistolica, 'pad' => $signo->presion_diastolica,
            'fc' => $signo->frecuencia_cardiaca ?? '--', 'fr' => $signo->frecuencia_respiratoria ?? '--',
            'spo2' => $signo->saturacion_oxigeno !== null ? "{$signo->saturacion_oxigeno}%" : '--',
            'spo2_val' => $signo->saturacion_oxigeno ?? '--',
            'temp' => $signo->temperatura !== null ? "{$signo->temperatura}°C" : '--',
            'temp_val' => $signo->temperatura ?? '--',
            'glucosa' => $signo->glucemia !== null ? "{$signo->glucemia} mg/dL" : '--',
            'dolor' => '--', 'dolor_val' => '--', 'peso' => '--', 'talla' => '--', 'imc' => '--',
            'posicion' => 'No registrada', 'oxigeno' => 'No registrado en esta toma',
            'criterio' => $interpretacion['message'], 'resultados' => $interpretacion['resultados'],
            'observaciones' => $signo->observacion ?: 'Sin observación registrada.',
            'responsable' => trim(implode(' ', array_filter([
                $signo->registradoPor?->nombres, $signo->registradoPor?->apellido_paterno,
                $signo->registradoPor?->apellido_materno,
            ]))) ?: 'No registrado',
            'estado' => $interpretacion['label'], 'badgeClass' => $interpretacion['estado_badge'],
        ];
    }
}
