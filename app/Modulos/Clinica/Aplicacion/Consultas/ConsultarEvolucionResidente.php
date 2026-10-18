<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\Personal;
use App\Models\Residente;
use App\Modulos\Alertas\Aplicacion\Consultas\TendenciaIncidentesService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/** Compara hechos guardados en ventanas equivalentes sin inferencia diagnóstica ni O.R.I.O.N. */
final class ConsultarEvolucionResidente
{
    public const CAMPOS_FUNCIONALES = [
        'marcha' => 'Marcha', 'equilibrio' => 'Equilibrio', 'traslado' => 'Traslado',
        'fuerza_funcional' => 'Fuerza funcional', 'resistencia' => 'Resistencia',
        'alimentacion_autonoma' => 'Alimentación autónoma', 'bano_autonomo' => 'Baño autónomo',
        'vestido_autonomo' => 'Vestido autónomo', 'higiene_autonoma' => 'Higiene autónoma',
        'continencia' => 'Continencia', 'movilidad_autonoma' => 'Movilidad autónoma',
        'necesita_supervision' => 'Necesita supervisión', 'nivel_dependencia' => 'Nivel de dependencia',
    ];

    /** Recibe coordinadores y adaptadores existentes; no introduce reglas clínicas nuevas. */
    public function __construct(
        private readonly ConsultarResumenIntegralResidente $integral,
        private readonly ResumenLongitudinalResidente $longitudinal,
        private readonly ResumenSeguimientoResidente $seguimiento,
        private readonly TendenciaIncidentesService $incidentes,
    ) {}

    /**
     * Valida el selector y fija un único corte T para [T−2D,T−D) y [T−D,T].
     * La lectura se reautoriza en cada fuente; no reutiliza últimos globales ni
     * relaciones precargadas limitadas. Devuelve comparaciones, puntos y originales planos.
     */
    public function consultar(Residente $residente, string $periodo = '30d'): array
    {
        Validator::make(['periodoEstado' => $periodo], ['periodoEstado' => 'required|in:7d,30d,90d'])->validate();
        $dias = ConsultarResumenIntegralResidente::PERIODOS[$periodo];
        $corte = CarbonImmutable::now()->startOfSecond();
        $inicio = $corte->subDays($dias);
        $inicioAnterior = $inicio->subDays($dias);
        $actual = $this->integral->consultarVentana($residente, $inicio, $corte);
        $anterior = $this->integral->consultarVentana($residente, $inicioAnterior, $inicio, false);
        $fuentesActuales = $this->seguimiento->evaluacionesDelPeriodo($residente, $inicio, $corte);
        $fuentesAnteriores = $this->seguimiento->evaluacionesDelPeriodo($residente, $inicioAnterior, $inicio, false);
        $a = collect($actual['hallazgos'])->keyBy('clave');
        $p = collect($anterior['hallazgos'])->keyBy('clave');

        $comparaciones = [];
        foreach (['caidas' => 'Caídas registradas', 'incidentes' => 'Otros incidentes',
            'alertas' => 'Alertas institucionales', 'observaciones' => 'Notas clínicas'] as $clave => $categoria) {
            $n = $a[$clave]['total'];
            $previo = $p[$clave]['total'];
            $nombre = match ($clave) {
                'caidas' => 'caídas', 'incidentes' => 'otros incidentes',
                'alertas' => 'alertas', default => 'notas clínicas'
            };
            $singular = match ($clave) {
                'caidas' => 'caída', 'incidentes' => 'incidente de otro tipo',
                'alertas' => 'alerta institucional', default => 'nota clínica',
            };
            $articulo = $clave === 'incidentes' ? 'un' : 'una';
            $titulo = $n > $previo ? "Aumentó el número de {$nombre} registradas."
                : ($n < $previo ? "Disminuyó el número de {$nombre} registradas." : "Se registró el mismo número de {$nombre} en ambos períodos.");
            if ($clave === 'incidentes') {
                $titulo = str_replace('registradas', 'registrados', $titulo);
            }
            if ($n === 0 && $previo === 0) {
                $titulo = "No se registraron {$nombre} en ninguno de los dos períodos.";
            }
            $comparaciones[] = ['clave' => $clave, 'categoria' => $categoria, 'titulo' => $titulo,
                'descripcion' => "Durante los últimos {$dias} días ".($n === 1 ? "se registró {$articulo} {$singular}" : "se registraron {$n} {$nombre}")
                    .", mientras que durante los {$dias} días anteriores ".($previo === 1 ? "se documentó {$articulo} {$singular}." : "se documentaron {$previo} {$nombre}."),
                'actual' => $n, 'anterior' => $previo,
                'fundamento' => $clave === 'caidas' || $clave === 'incidentes'
                    ? 'Se cuentan incidentes distintos por su código original. Las alertas asociadas no añaden incidentes. La diferencia no determina riesgo ni gravedad.'
                    : 'Se comparan registros existentes según su fecha de registro y estado actual. Un recuento diferente no equivale a un cambio clínico.',
                'registros_actuales' => $a[$clave]['evidencias'], 'registros_anteriores' => $p[$clave]['evidencias']];
        }

        $comparaciones[] = ['clave' => 'signos', 'categoria' => 'Signos vitales y presión arterial',
            'titulo' => $actual['presion_revision'] > 0
                ? ($anterior['presion_revision'] === 0 ? 'Se registraron nuevos valores de presión arterial que requieren revisión.'
                    : 'Se registraron valores de presión arterial que requieren revisión en ambos períodos.')
                : 'Comparación descriptiva de los controles de signos vitales registrados.',
            'descripcion' => 'Controles de signos vitales: '.$actual['total_signos'].' actuales y '.$anterior['total_signos'].' anteriores. Controles con presión arterial para revisión: '
                .$actual['presion_revision'].' actuales y '.$anterior['presion_revision'].' anteriores.',
            'actual' => $actual['total_signos'], 'anterior' => $anterior['total_signos'],
            'fundamento' => 'La clasificación procede de las reglas institucionales y del objetivo médico aplicable a la fecha de cada toma. Se muestran valores originales sin promedios ni interpretación longitudinal añadida. Sin controles previos no puede afirmarse un cambio respecto de una normalidad anterior.',
            'registros_actuales' => $a['signos']['evidencias'], 'registros_anteriores' => $p['signos']['evidencias']];
        $funcional = $this->compararFuncional($fuentesActuales, $fuentesAnteriores);
        $instrumentos = $this->compararInstrumentos($fuentesActuales, $fuentesAnteriores);

        $cronologia = collect();
        foreach (['actual' => $actual, 'anterior' => $anterior] as $ventana => $resumen) {
            foreach ($resumen['hallazgos'] as $hallazgo) {
                foreach ($hallazgo['evidencias'] as $original) {
                    $cronologia->push($original + ['periodo' => $ventana]);
                }
            }
        }
        foreach (['actual' => $fuentesActuales, 'anterior' => $fuentesAnteriores] as $ventana => $fuentes) {
            foreach ($fuentes['funcionales'] as $registro) {
                $cronologia->push($this->originalFuncional($registro) + ['periodo' => $ventana]);
            }
            foreach ($fuentes['instrumentos'] as $registro) {
                $cronologia->push($this->originalInstrumento($registro) + ['periodo' => $ventana]);
            }
        }
        $filasIncidentes = fn ($resumen) => collect($resumen['hallazgos'])->whereIn('clave', ['caidas', 'incidentes'])
            ->flatMap(fn ($h) => $h['evidencias'])->map(fn ($r) => ['codigo' => $r['codigo'], 'tipo' => $r['campos']['Tipo de incidente']])->all();

        return ['dias' => $dias,
            'actual' => $this->ventana($inicio, $corte, true) + ['resumen' => $actual],
            'anterior' => $this->ventana($inicioAnterior, $inicio, false) + ['resumen' => $anterior],
            'comparaciones' => $comparaciones,
            'incidentes_por_tipo' => $this->incidentes->compararRegistros($filasIncidentes($actual), $filasIncidentes($anterior)),
            'presion' => $this->longitudinal->seriePresionComparada($a['signos']['evidencias'], $p['signos']['evidencias']),
            'rango_presion' => ['desde' => $inicioAnterior->toIso8601String(), 'hasta' => $corte->toIso8601String()],
            'funcional' => $funcional, 'instrumentos' => $instrumentos,
            'mensaje_cognitivo' => 'No se dispone de evaluaciones comparables suficientes para determinar una evolución cognitiva objetiva.',
            'limite_cognitivo' => 'Las aplicaciones no conservan una versión histórica independiente del instrumento. Se presentan resultados descriptivos; las observaciones aisladas no permiten deducir progresión cognitiva.',
            'cronologia' => $cronologia->unique(fn ($r) => $r['origen'].'|'.$r['codigo'])->sortByDesc('fecha_iso')->values()->all()];
    }

    /** Explicita fechas, duración idéntica y pertenencia del límite compartido. */
    private function ventana(CarbonImmutable $desde, CarbonImmutable $hasta, bool $incluyeFinal): array
    {
        return ['desde' => $desde->format('d/m/Y H:i:s'), 'hasta' => $hasta->format('d/m/Y H:i:s'),
            'desde_iso' => $desde->toIso8601String(), 'hasta_iso' => $hasta->toIso8601String(),
            'duracion_segundos' => (int) $desde->diffInSeconds($hasta), 'incluye_final' => $incluyeFinal];
    }

    /** Compara campos literales de la última valoración de cada ventana, sin interpretar dirección clínica. */
    private function compararFuncional(array $actual, array $anterior): array
    {
        $a = $actual['funcionales']->first();
        $p = $anterior['funcionales']->first();
        $campos = [];
        foreach (self::CAMPOS_FUNCIONALES as $campo => $etiqueta) {
            $va = $this->normalizarAusencia($a?->getAttribute($campo));
            $vp = $this->normalizarAusencia($p?->getAttribute($campo));
            if ($va === null && $vp === null) {
                continue;
            }
            $campos[] = ['etiqueta' => $etiqueta, 'actual' => $this->literal($va), 'anterior' => $this->literal($vp),
                'comparacion' => $va === null || $vp === null ? 'Sin dato comparable'
                    : ($va === $vp ? 'Mismo valor registrado' : 'Valores registrados diferentes')];
        }

        return ['autorizada' => $actual['funcional_autorizada'], 'comparable' => $a !== null && $p !== null && collect($campos)->contains(fn ($c) => $c['comparacion'] !== 'Sin dato comparable'),
            'actual' => $a ? $this->originalFuncional($a) : null, 'anterior' => $p ? $this->originalFuncional($p) : null,
            'campos' => $campos, 'total_actual' => $actual['funcionales']->count(), 'total_anterior' => $anterior['funcionales']->count()];
    }

    /** Empareja el mismo instrumento y denominador positivo; la diferencia es solo aritmética descriptiva. */
    private function compararInstrumentos(array $actual, array $anterior): array
    {
        $a = $actual['instrumentos']->groupBy('cod_instrumento');
        $p = $anterior['instrumentos']->groupBy('cod_instrumento');
        $grupos = $a->keys()->merge($p->keys())->unique()->sort()->map(function ($codigo) use ($a, $p) {
            $ra = $a->get($codigo)?->first();
            $rp = $p->get($codigo)?->first();
            $comparable = $ra && $rp && $ra->puntaje_total !== null && $rp->puntaje_total !== null
                && $ra->puntaje_maximo !== null && $rp->puntaje_maximo !== null
                && (float) $ra->puntaje_maximo > 0 && $ra->puntaje_maximo === $rp->puntaje_maximo;
            $instrumento = ($ra ?? $rp)->instrumento;

            return ['codigo' => $codigo, 'nombre' => $instrumento?->nombre ?? 'Instrumento sin nombre registrado',
                'tipo' => $instrumento?->tipo, 'version_catalogo' => $instrumento?->version,
                'comparable' => (bool) $comparable,
                'diferencia' => $comparable ? round((float) $ra->puntaje_total - (float) $rp->puntaje_total, 2) : null,
                'actual' => $ra ? $this->originalInstrumento($ra) : null,
                'anterior' => $rp ? $this->originalInstrumento($rp) : null];
        })->values()->all();

        return ['autorizada' => $actual['instrumentos_autorizados'], 'grupos' => $grupos];
    }

    /** Conserva únicamente columnas originales de la valoración funcional, sin accessors que inventen estados. */
    private function originalFuncional(Model $registro): array
    {
        $campos = [];
        foreach (self::CAMPOS_FUNCIONALES as $campo => $etiqueta) {
            $campos[$etiqueta] = $this->literal($registro->getAttribute($campo));
        }

        return $this->original($registro, 'Valoración funcional', $registro->registradoPor,
            $campos + ['Conclusión registrada' => $registro->conclusion, 'Recomendación registrada' => $registro->recomendacion,
                'Atención de origen' => $registro->cod_atencion]);
    }

    /** Muestra puntajes y clasificación persistidos con su instrumento, sin calcular una interpretación nueva. */
    private function originalInstrumento(Model $registro): array
    {
        return $this->original($registro, 'Aplicación de instrumento', $registro->evaluador, [
            'Instrumento' => $registro->instrumento?->nombre, 'Código del instrumento' => $registro->cod_instrumento,
            'Tipo registrado' => $registro->instrumento?->tipo, 'Versión del catálogo actual' => $registro->instrumento?->version,
            'Puntaje registrado' => $registro->puntaje_total, 'Máximo registrado' => $registro->puntaje_maximo,
            'Clasificación registrada' => $registro->clasificacion, 'Interpretación registrada' => $registro->interpretacion,
            'Observación' => $registro->observacion, 'Atención de origen' => $registro->cod_atencion,
        ]);
    }

    /** Produce evidencia plana con código, autoría, fecha y estado, sin exponer relaciones a frontend. */
    private function original(Model $registro, string $origen, ?Personal $personal, array $campos): array
    {
        return ['codigo' => $registro->getKey(), 'origen' => $origen,
            'fecha' => $registro->fecha_hora->format('d/m/Y H:i'), 'fecha_iso' => $registro->fecha_hora->toIso8601String(),
            'estado' => $registro->estado, 'codigo_responsable' => $registro->cod_personal,
            'ancla' => 'original-'.hash('xxh64', $origen.'|'.$registro->getKey()),
            'responsable' => $personal ? trim($personal->nombres.' '.$personal->apellido_paterno.' '.$personal->apellido_materno) : 'No registrado',
            'campos' => $campos];
    }

    /** Distingue ausencia, afirmación y negación; nunca rellena el dato faltante. */
    private function literal(mixed $valor): string
    {
        return $this->normalizarAusencia($valor) === null ? 'No registrado' : (is_bool($valor) ? ($valor ? 'Sí' : 'No') : (string) $valor);
    }

    /** Un texto vacío o compuesto solo por espacios es ausencia, no un valor comparable. */
    private function normalizarAusencia(mixed $valor): mixed
    {
        return is_string($valor) && trim($valor) === '' ? null : $valor;
    }
}
