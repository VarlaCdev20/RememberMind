<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\Personal;
use App\Models\Residente;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Reúne hechos del expediente autorizado sin diagnosticar ni ejecutar O.R.I.O.N.
 * Los recuentos proceden de registros originales del período, no de las relaciones
 * limitadas de la cabecera. La interpretación reutiliza exclusivamente las reglas vigentes.
 */
final class ConsultarResumenIntegralResidente
{
    public const PERIODOS = ['7d' => 7, '30d' => 30, '90d' => 90];

    /** Recibe los servicios existentes de acceso al expediente y lectura de reglas clínicas. */
    public function __construct(
        private readonly PrepararFichaClinica $ficha,
        private readonly InterpretacionSignosVitales $interpretacion,
    ) {}

    /**
     * Valida el período y reautoriza al actor actual antes de consultar datos.
     * Devuelve fechas, recuentos y hallazgos con evidencia original para Blade.
     * Excluye registros anulados, futuros y ajenos al residente; no persiste cambios.
     */
    public function consultar(Residente $residente, string $periodo = '30d'): array
    {
        Validator::make(['periodoEstado' => $periodo], [
            'periodoEstado' => 'required|in:7d,30d,90d',
        ], ['periodoEstado.in' => 'Seleccione un período de 7, 30 o 90 días.'])->validate();
        $dias = self::PERIODOS[$periodo];
        $hasta = CarbonImmutable::now()->startOfSecond();

        return $this->consultarVentana($residente, $hasta->subDays($dias), $hasta);
    }

    /**
     * Lee una ventana explícita reautorizada. El inicio se incluye; el final puede
     * excluirse para comparar ventanas contiguas sin duplicar el registro limítrofe.
     * No admite ventanas invertidas ni fechas de corte futuras. Mantiene las fuentes de Fase 1.
     */
    public function consultarVentana(Residente $residente, CarbonImmutable $desde, CarbonImmutable $hasta, bool $incluirHasta = true): array
    {
        abort_unless($desde->lt($hasta) && $hasta->lte(now()), 422, 'El período consultado no es válido.');
        $this->ficha->preparar($residente);
        $dias = (int) round($desde->diffInDays($hasta));
        $operadorFinal = $incluirHasta ? '<=' : '<';

        $incidentes = $residente->incidentesClinicos()
            ->where('fecha_hora', '>=', $desde)->where('fecha_hora', $operadorFinal, $hasta)
            ->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO', 'CERRADO'])
            ->with('personal')->orderByDesc('fecha_hora')->orderBy('cod_incidente')->get();
        $caidas = $incidentes->where('tipo_incidente', 'CAIDA')->values();
        $otros = $incidentes->where('tipo_incidente', '!=', 'CAIDA')->values();
        $seguimiento = $incidentes->where('estado', 'EN_SEGUIMIENTO')->values();

        $signos = $residente->signosVitales()->where('fecha_hora', '>=', $desde)->where('fecha_hora', $operadorFinal, $hasta)
            ->whereIn('estado', ['VIGENTE', 'ACTIVO'])->with('registradoPor')
            ->orderByDesc('fecha_hora')->orderBy('cod_signo')->get();
        $interpretaciones = $this->interpretacion->coleccion($signos);
        $presionRevision = $signos->filter(fn ($s) => $this->requiereRevision(
            $interpretaciones[$s->cod_signo]['metricas']['PA']['resultados']
        ))->count();
        $controlesRevision = $signos->filter(fn ($s) => $this->requiereRevision(
            $interpretaciones[$s->cod_signo]['resultados']
        ))->count();

        $alertas = $residente->alertas()->where('fecha_hora', '>=', $desde)->where('fecha_hora', $operadorFinal, $hasta)
            ->whereIn('estado', ['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE', 'EN_ATENCION', 'ATENDIDA', 'CERRADA'])
            ->with('responsable')->orderByDesc('fecha_hora')->orderBy('cod_alerta')->get();
        $notas = $residente->notasClinicas()->where('fecha_hora', '>=', $desde)->where('fecha_hora', $operadorFinal, $hasta)
            ->whereIn('estado', ['VIGENTE', 'ACTIVO', 'ACTIVA'])
            ->whereHas('atencion', fn ($q) => $q->where('cod_residente', $residente->getKey())
                ->whereNotIn('estado', ['ANULADO', 'ANULADA', 'CANCELADO', 'CANCELADA']))
            ->orderByDesc('fecha_hora')->orderBy('cod_nota')->get();
        $autores = Personal::query()->whereIn('cod_personal', $notas->pluck('cod_personal')->unique())
            ->get()->keyBy('cod_personal');

        $evidenciaIncidente = fn ($i) => $this->original($i, 'Incidente', $i->personal, [
            'Tipo de incidente' => $i->tipo_incidente, 'Gravedad registrada' => $i->gravedad,
            'Lugar' => $i->lugar, 'Descripción' => $i->descripcion,
            'Medida inmediata' => $i->medida_inmediata, 'Observación' => $i->observacion,
            'Requiere médico (registro)' => $i->requiere_medico ? 'Sí' : 'No',
            'Requiere derivación (registro)' => $i->requiere_derivacion ? 'Sí' : 'No',
        ]);
        $hallazgos = [
            $this->hallazgo('caidas', 'Caídas registradas', $caidas,
                $caidas->count() === 1 ? "Se registró una caída durante los últimos {$dias} días."
                    : 'Se registraron '.$this->cantidad($caidas->count())." caídas durante los últimos {$dias} días.",
                'Se cuentan incidentes distintos de tipo caída. La gravedad y las medidas adoptadas se muestran por separado para cada registro; las alertas asociadas no suman caídas.',
                'No se registraron caídas en el período seleccionado.', $evidenciaIncidente),
            $this->hallazgo('incidentes', 'Otros incidentes', $otros,
                $otros->count() === 1 ? 'Se registró un incidente de otro tipo en el período.'
                    : 'Se registraron '.$otros->count().' incidentes de otros tipos en el período.',
                'Se muestran las circunstancias documentadas por el personal responsable, sin atribuir causas adicionales.',
                'No se registraron otros incidentes en el período seleccionado.', $evidenciaIncidente),
            $this->hallazgo('signos', 'Controles de signos vitales', $signos,
                $presionRevision > 0 ? 'Se registraron valores de presión arterial que requieren revisión.'
                    : ($controlesRevision > 0 ? 'Se registraron mediciones de signos vitales que requieren revisión.'
                        : ($signos->count() === 1 ? 'Se registró un control de signos vitales en el período.'
                            : 'Se registraron '.$signos->count().' controles de signos vitales en el período.')),
                $controlesRevision > 0
                    ? 'Durante los controles recientes se identificaron mediciones fuera de los valores de referencia aplicables al residente. Cada clasificación procede de la regla institucional o del objetivo médico aplicable a la fecha de la toma.'
                    : 'Cada medición conserva la clasificación de las reglas existentes. Un valor sin interpretación automática no permite concluir normalidad.',
                'No se registraron signos vitales en el período seleccionado.',
                fn ($s) => $this->original($s, 'Control de signos vitales', $s->registradoPor, [
                    'Presión sistólica (mmHg)' => $s->presion_sistolica,
                    'Presión diastólica (mmHg)' => $s->presion_diastolica,
                    'Frecuencia cardíaca (lpm)' => $s->frecuencia_cardiaca,
                    'Frecuencia respiratoria (rpm)' => $s->frecuencia_respiratoria,
                    'Temperatura (°C)' => $s->temperatura, 'Saturación de oxígeno (%)' => $s->saturacion_oxigeno,
                    'Glucemia (mg/dL)' => $s->glucemia, 'Observación' => $s->observacion,
                ]) + ['clasificacion' => $interpretaciones[$s->cod_signo]]),
            $this->hallazgo('alertas', 'Alertas institucionales', $alertas,
                $alertas->count() === 1 ? 'Se registró una alerta institucional en el período.'
                    : 'Se registraron '.$alertas->count().' alertas institucionales en el período.',
                'Son alertas ya existentes. Se indica su estado actual y, cuando existe, el código del registro de origen. Su número no se suma al de incidentes.',
                'No se registraron alertas institucionales en el período seleccionado.',
                fn ($a) => $this->original($a, 'Alerta institucional', $a->responsable, [
                    'Título' => $a->titulo, 'Tipo' => $a->tipo, 'Prioridad registrada' => $a->prioridad,
                    'Descripción' => $a->descripcion, 'Módulo de origen' => $a->modulo,
                    'Registro de origen' => $a->cod_registro, 'Generación' => $a->generacion,
                    'Fecha límite' => $a->fecha_hora_limite?->format('d/m/Y H:i'),
                ])),
            $this->hallazgo('observaciones', 'Observaciones recientes', $notas,
                $notas->count() === 1 ? 'Se registró una nota clínica vigente en el período.'
                    : 'Se registraron '.$notas->count().' notas clínicas vigentes en el período.',
                'Se muestra el contenido original de las notas clínicas y su autoría, sin generar una interpretación adicional.',
                'No se registraron notas clínicas vigentes en el período seleccionado.',
                fn ($n) => $this->original($n, 'Nota clínica', $autores->get($n->cod_personal), [
                    'Tipo de nota' => $n->tipo_nota, 'Contenido' => $n->contenido,
                    'Atención de origen' => $n->cod_atencion, 'Nota anterior' => $n->cod_nota_anterior,
                    'Motivo de corrección' => $n->motivo_correccion,
                ])),
            $this->hallazgo('seguimiento', 'Incidentes en seguimiento', $seguimiento,
                $seguimiento->count() === 1 ? 'Un incidente conserva el estado En seguimiento.'
                    : $seguimiento->count().' incidentes conservan el estado En seguimiento.',
                'Se presenta el seguimiento registrado. Este estado no confirma por sí solo una revisión médica pendiente.',
                'No se registraron incidentes en seguimiento en el período seleccionado.', $evidenciaIncidente),
        ];
        $hallazgos[2]['clasificacion'] = $this->interpretacion->resumir(
            $interpretaciones->flatMap(fn ($lectura) => $lectura['resultados'])->all()
        );

        return ['desde' => $desde->format('d/m/Y H:i'), 'hasta' => $hasta->format('d/m/Y H:i'),
            'dias' => $dias, 'total_incidentes' => $incidentes->count(), 'total_caidas' => $caidas->count(),
            'total_signos' => $signos->count(), 'total_alertas' => $alertas->count(),
            'total_observaciones' => $notas->count(), 'presion_revision' => $presionRevision,
            'controles_revision' => $controlesRevision,
            'hallazgos' => $hallazgos];
    }

    /** Selecciona resultados que el dominio ya marca para atención, sin fijar umbrales propios. */
    private function requiereRevision(array $resultados): bool
    {
        return collect($resultados)->contains(fn ($r) => in_array($r['severidad'], ['ADVERTENCIA', 'ALTO', 'CRITICO'], true)
            || $r['comportamiento_alerta'] === 'SUGERIR');
    }

    /** Compone un hallazgo descriptivo y su evidencia; el vacío solo afirma ausencia de registros. */
    private function hallazgo(string $clave, string $categoria, Collection $registros, string $titulo,
        string $explicacion, string $vacio, callable $evidencia): array
    {
        return ['clave' => $clave, 'categoria' => $categoria, 'total' => $registros->count(),
            'titulo' => $registros->isEmpty() ? $vacio : $titulo,
            'explicacion' => $registros->isEmpty() ? null : $explicacion,
            'evidencias' => $registros->map($evidencia)->values()->all()];
    }

    /** Expone campos explícitos del original autorizado y conserva código, fecha, estado y responsable. */
    private function original(Model $registro, string $origen, ?Personal $personal, array $campos): array
    {
        return ['codigo' => $registro->getKey(), 'origen' => $origen,
            'ancla' => 'original-'.hash('xxh64', $origen.'|'.$registro->getKey()),
            'fecha' => $registro->fecha_hora?->format('d/m/Y H:i'), 'estado' => $registro->estado,
            'fecha_iso' => $registro->fecha_hora?->toIso8601String(),
            'responsable' => $personal ? trim($personal->nombres.' '.$personal->apellido_paterno.' '.$personal->apellido_materno) : 'No registrado',
            'codigo_responsable' => $personal?->getKey() ?? $registro->cod_personal,
            'campos' => $campos];
    }

    /** Usa lenguaje natural para las cantidades pequeñas del relato de caídas. */
    private function cantidad(int $cantidad): string
    {
        return [0 => 'cero', 2 => 'dos', 3 => 'tres', 4 => 'cuatro'][$cantidad] ?? (string) $cantidad;
    }
}
