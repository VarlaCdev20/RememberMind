<?php

namespace App\Services\Medicacion;

use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AgendaMedicacionService
{
    private const ESTADOS_ACTIVOS = [
        'ACTIVO',
        'ACTIVA',
        'VIGENTE',
    ];

    /**
     * Construye la agenda farmacológica para la fecha contenida en $momento.
     *
     * Importante:
     * - Solo genera ocurrencias de órdenes programadas, no PRN.
     * - No crea administraciones ficticias.
     * - Una administración real registrada reemplaza el estado calculado.
     * - Los intervalos se calculan de forma continua desde la fecha/hora de inicio,
     *   incluso cuando el intervalo no divide exactamente las 24 horas.
     */
    public function paraAdultos(
        array $codigosAdulto,
        ?CarbonInterface $momento = null
    ): Collection {
        $ahora = $momento
            ? Carbon::instance($momento)->copy()
            : now();

        $codigos = collect($codigosAdulto)
            ->filter(fn ($codigo) => filled($codigo))
            ->map(fn ($codigo) => trim((string) $codigo))
            ->unique()
            ->values();

        if ($codigos->isEmpty()) {
            return collect();
        }

        $fecha = $ahora->toDateString();

        $medicaciones = MedicacionAdulto::query()
            ->with([
                'adultoMayor.habitacion',
                'adultoMayor.cama',
            ])
            ->whereIn('cod_am', $codigos->all())
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->where('es_prn', false)
            ->whereNotNull('hora_programada')
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->where(function ($query) use ($fecha) {
                $query
                    ->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $fecha);
            })
            ->orderBy('hora_programada')
            ->get();

        if ($medicaciones->isEmpty()) {
            return collect();
        }

        /**
         * Solo cargamos administraciones de las órdenes que realmente forman
         * parte de la agenda consultada. Esto evita traer registros clínicos
         * innecesarios del mismo residente.
         *
         * Si existiera accidentalmente más de un registro histórico para la
         * misma ocurrencia, se toma el más reciente.
         */
        $codigosMedicacion = $medicaciones
            ->pluck('cod_med_adulto')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $administraciones = AdministracionMedicacion::query()
            ->whereIn('cod_am', $codigos->all())
            ->whereIn('cod_med_adulto', $codigosMedicacion)
            ->whereDate('fecha', $fecha)
            ->whereNotNull('hora_programada')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(
                fn (AdministracionMedicacion $registro) => $this->clave(
                    (string) $registro->cod_med_adulto,
                    $registro->hora_programada
                )
            );

        $minutosProxima = max(
            0,
            (int) config('enfermeria.minutos_proximo_medicacion', 60)
        );

        return $medicaciones
            ->flatMap(function (MedicacionAdulto $medicacion) use (
                $administraciones,
                $ahora,
                $fecha,
                $minutosProxima
            ) {
                return collect(
                    $this->horariosDelDia($medicacion, $ahora)
                )->map(function (string $hora) use (
                    $medicacion,
                    $administraciones,
                    $ahora,
                    $fecha,
                    $minutosProxima
                ) {
                    $programada = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        "{$fecha} {$hora}",
                        $ahora->timezone
                    );

                    $clave = $this->clave(
                        (string) $medicacion->cod_med_adulto,
                        $hora
                    );

                    /** @var AdministracionMedicacion|null $registro */
                    $registro = $administraciones->get($clave)?->first();

                    $minutos = (int) $ahora->diffInMinutes(
                        $programada,
                        false
                    );

                    $estado = $this->resolverEstado(
                        $registro,
                        $programada,
                        $minutos,
                        $minutosProxima
                    );

                    return [
                        'id' => $medicacion->cod_med_adulto.'|'.$fecha.'|'.$hora,
                        'medicacion' => $medicacion,
                        'adulto' => $medicacion->adultoMayor,
                        'hora' => $hora,
                        'programada' => $programada,
                        'registro' => $registro,
                        'estado' => $estado,
                        'minutos' => $minutos,
                    ];
                });
            })
            ->sortBy('programada')
            ->values();
    }

    public function paraAdulto(
        string $codAm,
        ?CarbonInterface $momento = null
    ): Collection {
        return $this->paraAdultos([$codAm], $momento);
    }

    /**
     * Devuelve únicamente ocurrencias que requieren atención operativa.
     */
    public function recordatorios(
        array $codigosAdulto,
        ?CarbonInterface $momento = null
    ): Collection {
        return $this->paraAdultos($codigosAdulto, $momento)
            ->whereIn('estado', ['PROXIMA', 'VENCIDA'])
            ->values();
    }

    private function resolverEstado(
        ?AdministracionMedicacion $registro,
        CarbonInterface $programada,
        int $minutos,
        int $minutosProxima
    ): string {
        if ($registro) {
            return $registro->administrado
                ? 'ADMINISTRADA'
                : 'OMITIDA';
        }

        if ($programada->isPast()) {
            return 'VENCIDA';
        }

        if ($minutos <= $minutosProxima) {
            return 'PROXIMA';
        }

        return 'PENDIENTE';
    }

    private function clave(
        string $codMedicacion,
        mixed $hora
    ): string {
        return $codMedicacion.'|'.$this->normalizarHora($hora);
    }

    /**
     * Unifica hora de modelos, strings H:i y timestamps a H:i.
     */
    private function normalizarHora(mixed $hora): string
    {
        if ($hora instanceof CarbonInterface) {
            return $hora->format('H:i');
        }

        return Carbon::parse($hora)->format('H:i');
    }

    /**
     * Calcula las ocurrencias reales de una orden para el día consultado.
     *
     * Si existe intervalo_horas (o una frecuencia textual "CADA N HORAS"),
     * la secuencia se ancla a fecha_inicio + hora_programada y continúa
     * atravesando los días. Esto evita reiniciar incorrectamente la pauta
     * a medianoche en intervalos como cada 7, 10 o 14 horas.
     */
    private function horariosDelDia(
        MedicacionAdulto $medicacion,
        CarbonInterface $momento
    ): array {
        if (
            !$medicacion->fecha_inicio
            || !$medicacion->hora_programada
        ) {
            return [];
        }

        $zona = $momento->timezone;

        $horaInicial = $this->normalizarHora(
            $medicacion->hora_programada
        );

        $inicioOrden = Carbon::createFromFormat(
            'Y-m-d H:i',
            $medicacion->fecha_inicio->toDateString().' '.$horaInicial,
            $zona
        );

        $inicioDia = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $momento->toDateString().' 00:00:00',
            $zona
        );

        $finDia = $inicioDia->copy()->endOfDay();

        /**
         * La consulta superior ya excluye fechas previas al inicio,
         * pero esta comprobación mantiene este método autoconsistente.
         */
        if ($finDia->lt($inicioOrden)) {
            return [];
        }

        $intervaloHoras = $this->resolverIntervaloHoras(
            $medicacion
        );

        /**
         * Sin intervalo explícito: una ocurrencia diaria a la hora indicada.
         */
        if ($intervaloHoras === null) {
            $ocurrencia = Carbon::createFromFormat(
                'Y-m-d H:i',
                $momento->toDateString().' '.$horaInicial,
                $zona
            );

            if ($ocurrencia->lt($inicioOrden)) {
                return [];
            }

            return [$ocurrencia->format('H:i')];
        }

        $intervaloMinutos = $intervaloHoras * 60;

        /**
         * Encontrar la primera ocurrencia del día sin iterar desde la fecha
         * inicial cuando el tratamiento lleva meses o años activo.
         */
        if ($inicioOrden->gte($inicioDia)) {
            $primera = $inicioOrden->copy();
        } else {
            $minutosTranscurridos = $inicioOrden->diffInMinutes(
                $inicioDia
            );

            $saltos = intdiv(
                $minutosTranscurridos,
                $intervaloMinutos
            );

            $primera = $inicioOrden
                ->copy()
                ->addMinutes($saltos * $intervaloMinutos);

            if ($primera->lt($inicioDia)) {
                $primera->addMinutes($intervaloMinutos);
            }
        }

        $horarios = [];

        for (
            $ocurrencia = $primera->copy();
            $ocurrencia->lte($finDia);
            $ocurrencia->addMinutes($intervaloMinutos)
        ) {
            $horarios[] = $ocurrencia->format('H:i');
        }

        return array_values(
            array_unique($horarios)
        );
    }

    /**
     * intervalos válidos: 1–24 horas.
     * Si no existe valor estructurado, conserva compatibilidad con la pauta
     * textual "Cada N horas".
     */
    private function resolverIntervaloHoras(
        MedicacionAdulto $medicacion
    ): ?int {
        $intervalo = $medicacion->intervalo_horas;

        if ($intervalo === null) {
            $frecuencia = mb_strtoupper(
                trim((string) $medicacion->frecuencia)
            );

            if (
                preg_match(
                    '/\bCADA\s+(\d{1,2})\s+HORAS?\b/u',
                    $frecuencia,
                    $coincidencia
                )
            ) {
                $intervalo = (int) $coincidencia[1];
            }
        }

        if (
            !is_numeric($intervalo)
            || (int) $intervalo < 1
            || (int) $intervalo > 24
        ) {
            return null;
        }

        return (int) $intervalo;
    }
}
