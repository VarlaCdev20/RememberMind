<?php

namespace Database\Seeders;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Models\Alerta;
use App\Models\AsignacionPersonal;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\ServicioDecisionAlertaClinica;
use App\Modulos\Clinica\Aplicacion\Acciones\EvaluadorSignosVitales;
use App\Modulos\Clinica\Aplicacion\Validacion\ValidacionSignosVitalesService;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAsignacionesJornada;
use Carbon\CarbonImmutable;
use Database\Seeders\LaPaz\ConservacionDatos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/** Complemento voluntario del censo sintético de desarrollo ya instalado. */
final class ComplementarCensoSinteticoSeeder extends Seeder
{
    public const BASE = 'remembermind_dev';

    private const CORTE = '2026-11-12 17:00:00';

    private const FIN_PLANIFICACION = '2026-12-11';

    private const MARCA = 'Caso sintético · complemento del censo 2026';

    private const GRUPO_ENFERMERIA = ['99120001', '99120002', '99120003', '99120004', '99120005'];

    private const TABLAS = [
        'asignaciones_residente_jornada', 'atenciones', 'notas_clinicas', 'signos_vitales',
        'registros_ingesta', 'registros_hidratacion', 'registros_movilidad', 'registros_conductuales',
        'registros_eliminacion', 'registros_sueno', 'valoraciones_dolor', 'controles_cognitivos',
        'alertas', 'eventos_alerta', 'participantes_actividad',
    ];

    public function run(): void
    {
        $resultado = $this->ejecutar(false);
        $this->command?->info('Ensayo revertido. Filas previstas: '.array_sum($resultado['nuevas_por_tabla']).'.');
    }

    public static function comprobarDestino(): void
    {
        $conexion = DB::connection();
        if (! app()->environment('local') || $conexion->getDriverName() !== 'pgsql'
            || $conexion->getDatabaseName() !== self::BASE
            || ! in_array($conexion->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)) {
            throw new RuntimeException('La carga requiere PostgreSQL local y la base '.self::BASE.'.');
        }
    }

    public function ejecutar(bool $guardar): array
    {
        self::comprobarDestino();

        if (! $guardar) {
            DB::beginTransaction();
        }

        try {
            return $this->cargar();
        } finally {
            if (! $guardar) {
                DB::rollBack();
            }
        }
    }

    private function cargar(): array
    {
        return DB::transaction(function (): array {
            DB::select('select pg_advisory_xact_lock(?)', [2026111201]);
            $antes = $this->conteos();
            $corte = CarbonImmutable::parse(self::CORTE);
            if ($corte->isFuture()) {
                throw new RuntimeException('El corte del conjunto sintético todavía no ha ocurrido.');
            }

            $residentes = $this->residentes();
            $enfermera = User::query()->role('ENFERMEROS')->where('estado', 'ACTIVO')
                ->where('correo', 'enfermeria@remembermind.com')
                ->whereHas('personal', fn ($q) => $q->where('estado', 'ACTIVO'))->sole();
            $gerente = User::query()->role('GERENTE')->where('estado', 'ACTIVO')->sole();

            $grupo = $residentes->whereIn('numero_documento', self::GRUPO_ENFERMERIA)->values();
            $cierres = $this->completarAlertaHistorica($grupo->last()->cod_residente, $enfermera);
            $conservacion = new ConservacionDatos(self::TABLAS);
            $conservacion->capturar();
            $this->cargarEnfermeria($grupo, $enfermera, $gerente, $corte);
            $this->cargarParticipacion($residentes, $corte);

            $despues = $this->conteos();
            return [
                'residentes' => $residentes->count(),
                'residentes_grupo_enfermeria' => $grupo->count(),
                'alertas_historicas_completadas' => $cierres,
                'filas_preexistentes_conservadas' => $conservacion->comprobar(),
                'nuevas_por_tabla' => array_filter(array_combine(
                    array_keys($antes),
                    array_map(fn ($a, $d) => $d - $a, $antes, $despues),
                )),
            ];
        });
    }

    private function residentes(): Collection
    {
        $residentes = DB::table('residentes as r')
            ->join('admisiones as a', 'a.cod_residente', '=', 'r.cod_residente')
            ->where('r.estado', 'ADMITIDO')
            ->whereIn('r.numero_documento', array_map(strval(...), range(99120001, 99120030)))
            ->select('r.cod_residente', 'r.numero_documento', 'a.fecha_hora_admision')
            ->orderBy('r.numero_documento')->get();

        if ($residentes->count() !== 30 || $residentes->unique('numero_documento')->count() !== 30
            || DB::table('residentes')->count() !== 30) {
            throw new RuntimeException('El destino debe contener el censo sintético completo y una admisión por residente.');
        }

        return $residentes;
    }

    private function cargarEnfermeria(Collection $grupo, User $enfermera, User $gerente, CarbonImmutable $corte): void
    {
        $asignaciones = AsignacionPersonal::query()
            ->where('cod_personal', $enfermera->personal->getKey())
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'FINALIZADA'])
            ->whereHas('area', fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']))
            ->with('jornada.turno')->orderBy('cod_asignacion_personal')->get()
            ->filter(fn ($a) => $a->jornada?->turno !== null)
            ->sortBy(fn ($a) => $a->jornada->fecha_jornada->toDateString().' '.$a->jornada->turno->hora_inicio)
            ->unique('cod_jornada');

        if ($asignaciones->isEmpty()) {
            throw new RuntimeException('La enfermera necesita jornadas existentes para vincular sus residentes.');
        }

        $finAnterior = null;
        $casoCritico = false;
        foreach ($asignaciones as $asignacion) {
            $jornada = $asignacion->jornada;
            $fecha = $jornada->fecha_jornada->toDateString();
            $turno = $jornada->turno;
            $inicio = CarbonImmutable::parse($fecha.' '.$turno->hora_inicio);
            $fin = CarbonImmutable::parse($fecha.' '.$turno->hora_cierre);
            if ($fin->lessThanOrEqualTo($inicio)) {
                $fin = $fin->addDay();
            }

            $historica = $fin->lessThanOrEqualTo($corte) && $jornada->estado === 'CERRADA'
                && $asignacion->estado === 'FINALIZADA';
            $planificada = $inicio->greaterThan($corte) && $fecha <= self::FIN_PLANIFICACION
                && $turno->estado === 'ACTIVO'
                && in_array([$turno->hora_inicio, $turno->hora_cierre], [
                    ['07:00:00', '15:00:00'], ['15:00:00', '23:00:00'], ['23:00:00', '07:00:00'],
                ], true)
                && in_array($jornada->estado, ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'], true)
                && in_array($asignacion->estado, ['ACTIVA', 'ACTIVO'], true);

            if ((! $historica && ! $planificada) || ($finAnterior && $inicio->lessThan($finAnterior))) {
                continue;
            }
            $finAnterior = $fin;
            if ($planificada && DB::table('jornadas')->where('cod_turno', $turno->getKey())
                ->whereDate('fecha_jornada', $fecha)->count() !== 1) {
                throw new RuntimeException('El turno planificado tiene jornadas ambiguas.');
            }

            $existentes = RegistrosConsumo::consulta('asignaciones_residente_jornada')->where('cod_jornada', $jornada->getKey())
                ->get()->keyBy('cod_residente');
            $capacidad = max(0, count(self::GRUPO_ENFERMERIA) - $existentes->where('cod_personal', $asignacion->cod_personal)->count());
            foreach ($grupo as $indice => $residente) {
                $id = $residente->cod_residente;
                if ($inicio->lessThan(CarbonImmutable::parse($residente->fecha_hora_admision))) {
                    continue;
                }

                $existente = $existentes->get($id);
                if ($existente && ($existente->cod_personal !== $asignacion->cod_personal || $existente->observacion !== self::MARCA)) {
                    continue;
                }
                if (! $existente) {
                    if ($capacidad === 0) {
                        continue;
                    }
                    $capacidad--;
                    if ($planificada) {
                        app(GestionarAsignacionesJornada::class)->guardar($gerente, [
                            'codResidente' => $id, 'codTurno' => $turno->getKey(),
                            'codEnfermero' => $enfermera->getKey(), 'fechaInicio' => $fecha,
                            'nivelSupervision' => 'ESTANDAR', 'motivoAsignacion' => self::MARCA,
                        ]);
                    } else {
                        $this->insertar('asignaciones_residente_jornada', [
                            'cod_residente' => $id, 'cod_personal' => $asignacion->cod_personal,
                            'cod_jornada' => $jornada->getKey(), 'fecha_hora' => $inicio->toDateTimeString(),
                            'nivel_supervision' => 'ESTANDAR', 'estado' => 'FINALIZADA', 'observacion' => self::MARCA,
                        ]);
                    }
                }
                if ($historica && $inicio->addHour()->lessThan($fin)) {
                    $critico = ! $casoCritico && $indice === 4;
                    $casoCritico = $casoCritico || $critico;
                    $this->registroHistorico($id, $asignacion, $enfermera, $inicio->addHour(), $fin, $indice, $critico);
                }
            }
        }
    }

    private function registroHistorico(string $residente, AsignacionPersonal $a, User $autor,
        CarbonImmutable $hora, CarbonImmutable $fin, int $indice, bool $critico): void
    {
        $contexto = ['cod_residente' => $residente, 'cod_personal' => $a->cod_personal];
        $fecha = $hora->toDateTimeString();
        if (RegistrosConsumo::consulta('signos_vitales')->where($contexto)->where('cod_jornada', $a->cod_jornada)->exists()) {
            return;
        }

        $atencion = $this->insertar('atenciones', $contexto + [
            'cod_area' => $a->cod_area, 'fecha_hora' => $fecha, 'tipo_atencion' => 'SEGUIMIENTO_ENFERMERIA',
            'motivo' => 'Registro retrospectivo de Enfermería', 'observacion' => self::MARCA, 'estado' => 'FINALIZADA',
        ]);
        $clinico = $contexto + ['cod_atencion' => $atencion, 'fecha_hora' => $fecha, 'estado' => 'VIGENTE'];
        $jornada = $contexto + ['cod_jornada' => $a->cod_jornada, 'fecha_hora' => $fecha, 'estado' => 'VIGENTE'];
        $variacion = ((int) $hora->dayOfYear + $indice * 7) % 13;

        $mediciones = Validator::make([
            'fecha_hora' => $fecha, 'presion_sistolica' => 111 + $variacion * 2,
            'presion_diastolica' => 67 + $variacion, 'frecuencia_cardiaca' => $critico ? 38 : 65 + $variacion,
            'frecuencia_respiratoria' => 15 + $variacion % 4, 'temperatura' => 36.2 + ($variacion % 6) / 10,
            'saturacion_oxigeno' => 96 + $variacion % 4, 'observacion' => self::MARCA,
        ], ValidacionSignosVitalesService::reglasRegistro())->validate();
        $evaluacion = app(EvaluadorSignosVitales::class)->evaluar($mediciones, $residente);
        $signo = SignoVital::create($jornada + ['cod_atencion' => $atencion] + $mediciones);
        $alerta = app(ServicioDecisionAlertaClinica::class)->crearSiCorresponde($signo, $evaluacion, $autor);
        if ($critico && $alerta) {
            $this->cerrarEscenarioHistorico($alerta, $autor, $hora);
        }

        $this->insertar('notas_clinicas', $clinico + [
            'tipo_nota' => 'EVOLUCION', 'contenido' => self::MARCA.'. Se registran observaciones del turno.',
        ]);
        $this->insertar('registros_hidratacion', $jornada + [
            'cantidad_ml' => 125 + 25 * ($variacion % 5), 'tipo_liquido' => 'AGUA',
            'tolerancia' => 'ADECUADA', 'observacion' => self::MARCA,
        ]);
        if ($hora->hour >= 7 && $hora->hour < 21) {
            $this->insertar('registros_ingesta', $jornada + [
                'tipo_comida' => $hora->hour < 11 ? 'DESAYUNO' : ($hora->hour < 16 ? 'ALMUERZO' : 'CENA'),
                'porcentaje_consumido' => 60 + ($variacion % 9) * 5, 'apetito' => $variacion < 5 ? 'PARCIAL' : 'COMPLETA',
                'tolerancia' => 'BUENA', 'dificultad_deglucion' => false, 'observacion' => self::MARCA,
            ]);
        }
        $asistida = $indice === 4;
        $this->insertar('registros_movilidad', $jornada + $clinico + [
            'marcha' => $asistida ? 'ASISTIDA' : 'INDEPENDIENTE', 'equilibrio' => $asistida ? 'INESTABLE' : 'ESTABLE',
            'traslado' => $asistida ? 'AYUDA_UNA_PERSONA' : 'INDEPENDIENTE', 'tipo_apoyo' => $asistida ? 'SUPERVISION' : 'NINGUNO',
            'fatiga' => $asistida ? 'LEVE' : 'SIN_FATIGA', 'riesgo_caida' => $asistida ? 'ALTO' : 'BAJO', 'observacion' => self::MARCA,
        ]);
        $this->insertar('registros_conductuales', $jornada + $clinico + [
            'estado_animo' => $variacion < 2 ? 'ANSIOSO' : 'TRANQUILO', 'ansiedad' => $variacion < 2,
            'participacion' => $variacion < 4 ? 'PARCIAL' : 'ACTIVA', 'descripcion' => self::MARCA,
        ]);
        $this->insertar('registros_eliminacion', $jornada + [
            'tipo_eliminacion' => 'URINARIA', 'cantidad' => 'Una micción; volumen no cuantificado',
            'continencia' => 'CONTINENTE', 'observacion' => self::MARCA,
        ]);
        $this->insertar('valoraciones_dolor', $clinico + [
            'intensidad' => $asistida ? 1 + $variacion % 3 : 0, 'ubicacion' => $asistida ? 'Rodilla derecha' : null,
            'intervencion' => self::MARCA,
        ]);
        if ($fin->hour >= 6 && $fin->hour < 10) {
            $fechaDescanso = $fin->toDateString();
            if (! RegistrosConsumo::consulta('registros_sueno')->where('cod_residente', $residente)->where('fecha', $fechaDescanso)->exists()) {
                $this->insertar('registros_sueno', $contexto + [
                    'cod_jornada' => $a->cod_jornada, 'fecha' => $fechaDescanso, 'estado' => 'VIGENTE',
                    'horas_sueno' => 5.5 + 0.5 * ($variacion % 6), 'despertares' => $variacion % 4,
                    'calidad' => $variacion % 4 > 1 ? 'INTERRUMPIDO' : 'NORMAL', 'observacion' => self::MARCA,
                ]);
            }
        }
        if ($hora->hour >= 8 && $hora->hour < 19 && $hora->day % 3 === 0) {
            $orientacionVariable = $indice === 4 && $variacion < 8;
            $this->insertar('controles_cognitivos', $clinico + ['cod_jornada' => $a->cod_jornada,
                'orientacion_persona' => 'ORIENTADO', 'orientacion_lugar' => 'ORIENTADO',
                'orientacion_tiempo' => $orientacionVariable ? 'PARCIALMENTE_ORIENTADO' : 'ORIENTADO',
                'memoria_reciente' => $orientacionVariable ? 'ALTERACION_LEVE' : 'CONSERVADA',
                'observacion' => self::MARCA.'. Observación descriptiva del turno.',
            ]);
        }
    }

    private function completarAlertaHistorica(string $residente, User $autor): int
    {
        $candidatas = Alerta::query()->where('cod_residente', $residente)->where('modulo', 'SIGNOS')
            ->where('cod_personal_responsable', $autor->personal->getKey())->where('estado', 'ABIERTA')
            ->whereIn('cod_registro', RegistrosConsumo::consulta('signos_vitales')->select('cod_registro_medicion')
                ->where('observacion', self::MARCA)->where('frecuencia_cardiaca', 38))
            ->limit(2)->lockForUpdate()->get();
        if ($candidatas->count() > 1) {
            throw new RuntimeException('La alerta histórica del escenario es ambigua.');
        }
        $alerta = $candidatas->first();
        if (! $alerta || $alerta->eventos()->count() !== 1
            || ! $alerta->eventos()->where('tipo_evento', 'CREACION')->exists()) {
            return 0;
        }

        $this->cerrarEscenarioHistorico($alerta, $autor, CarbonImmutable::parse($alerta->fecha_hora));

        return 1;
    }

    private function cerrarEscenarioHistorico(Alerta $alerta, User $autor, CarbonImmutable $medicion): void
    {
        $this->insertar('eventos_alerta', [
            'cod_alerta' => $alerta->getKey(), 'cod_usuario' => $autor->getKey(), 'tipo_evento' => 'INTERVENCION',
            'estado_anterior' => 'ABIERTA', 'estado_nuevo' => 'EN_ATENCION', 'fecha_hora' => now(),
            'descripcion' => self::MARCA.'. Intervención retrospectiva del escenario ('.$medicion->addMinutes(10)->toDateTimeString()
                .'): se verificó el estado general y se dejó constancia de vigilancia de Enfermería.',
        ]);
        $alerta->update(['estado' => 'CERRADA']);
        $this->insertar('eventos_alerta', [
            'cod_alerta' => $alerta->getKey(), 'cod_usuario' => $autor->getKey(), 'tipo_evento' => 'CIERRE',
            'estado_anterior' => 'EN_ATENCION', 'estado_nuevo' => 'CERRADA', 'fecha_hora' => now(),
            'descripcion' => self::MARCA.'. Cierre retrospectivo del escenario con seguimiento documentado.',
        ]);
    }

    private function cargarParticipacion(Collection $residentes, CarbonImmutable $corte): void
    {
        $actividades = DB::table('actividades')->whereIn('estado', ['FINALIZADA', 'REALIZADA'])
            ->where('fecha_hora', '<', $corte)->whereNotNull('cupo')
            ->where('observacion', 'like', '%Registro fuente: actividades/ACTC%')
            ->orderBy('fecha_hora')->orderBy('cod_actividad')->get();

        foreach ($actividades as $indice => $actividad) {
            $existentes = DB::table('participantes_actividad')->where('cod_actividad', $actividad->cod_actividad)
                ->pluck('cod_residente');
            $cantidad = max(0, min((int) $actividad->cupo, 3 + $indice % 5) - $existentes->count());
            $inicio = ($indice * 7) % $residentes->count();
            $candidatos = $residentes->slice($inicio)->concat($residentes->take($inicio))
                ->filter(fn ($r) => $r->fecha_hora_admision <= $actividad->fecha_hora && ! $existentes->contains($r->cod_residente))
                ->values();

            foreach ($candidatos->take($cantidad) as $posicion => $residente) {
                $asistio = ($indice + $posicion) % 6 !== 0;
                $this->insertar('participantes_actividad', [
                    'cod_actividad' => $actividad->cod_actividad, 'cod_residente' => $residente->cod_residente,
                    'asistencia' => $asistio ? 'PRESENTE' : 'AUSENTE',
                    'nivel_participacion' => $asistio ? ['ACTIVA', 'PARCIAL', 'OBSERVADORA'][($indice + $posicion) % 3] : null,
                    'observacion' => self::MARCA,
                ]);
            }
        }
    }

    private function conteos(): array
    {
        return array_combine(self::TABLAS, array_map(fn ($tabla) => RegistrosConsumo::consulta($tabla)->count(), self::TABLAS));
    }

    private function insertar(string $tabla, array $datos): string
    {
        $id = app(CodigosEntidad::class)->siguiente($tabla);
        RegistrosConsumo::insertar($tabla, [[CatalogoCodigos::clave($tabla) => $id] + $datos]);

        return $id;
    }
}
