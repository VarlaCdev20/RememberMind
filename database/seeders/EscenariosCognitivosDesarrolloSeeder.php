<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Models\Area;
use App\Models\ControlCognitivo;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Carga retrospectiva explícita de desarrollo: no crea residentes ni activa conocimiento.
 * Conserva observaciones simuladas en sus columnas originales, sin diagnósticos ni baremos.
 * No se registra en DatabaseSeeder. Repetir verifica el lote completo y nunca lo sobrescribe.
 */
final class EscenariosCognitivosDesarrolloSeeder extends Seeder
{
    public const VERSION = 'SIM-SE-20261017-V1';

    public const HASTA = '2026-10-16';

    /** Los documentos pertenecen al conjunto sintético historical2026, no a personas reales. */
    public const CASOS = [
        '99000001' => ['perfil' => 'OLVIDOS', 'descripcion' => 'Olvidos y preguntas repetidas, con otras capacidades observadas conservadas.', 'caidas' => [29]],
        '99000010' => ['perfil' => 'PERSISTENTE', 'descripcion' => 'Dificultades persistentes de memoria y atención, con sueño interrumpido en algunas noches.', 'caidas' => [17, 43]],
        '99000005' => ['perfil' => 'MAYOR_AFECTACION', 'descripcion' => 'Mayor dificultad observada en memoria, orientación y comprensión; apoyos físicos y caídas repetidas.', 'caidas' => [8, 21, 34, 48, 57]],
        '99000009' => ['perfil' => 'COMPARACION', 'descripcion' => 'Comparación: oportunidades de observación sin dificultades cognitivas registradas.', 'caidas' => []],
        '99000003' => ['perfil' => 'INCOMPLETO', 'descripcion' => 'Comparación: controles completos alternados con observaciones no valorables, sin rellenar ausencias.', 'caidas' => []],
    ];

    /** Entrada optativa de Artisan; todas las identidades y el destino deben ser explícitos. */
    public function run(): void
    {
        $resultado = $this->ejecutar(
            (string) env('ESCENARIOS_EXPERTO_ALLOW_DATABASE', ''),
            (string) env('ESCENARIOS_EXPERTO_ENFERMERO', ''),
            (string) env('ESCENARIOS_EXPERTO_MEDICO', ''),
            (string) env('ESCENARIOS_EXPERTO_FISIOTERAPEUTA', ''),
            (string) env('ESCENARIOS_EXPERTO_AREA_MEDICA', ''),
            (string) env('ESCENARIOS_EXPERTO_AREA_FUNCIONAL', ''),
            (string) env('ESCENARIOS_EXPERTO_TURNO_24H', ''),
        );
        $this->command?->info(self::VERSION.': '.$resultado['nuevos'].' registros añadidos; '.$resultado['existentes']
            .' verificados; '.$resultado['rectificaciones_nuevas'].' rectificaciones nuevas y '.$resultado['jornadas_nuevas'].' jornadas históricas simuladas nuevas.');
        $this->command?->table(['Tabla', 'Registros del lote'], array_map(
            static fn (string $tabla, int $cantidad): array => [$tabla, $cantidad],
            array_keys($resultado['tablas']), array_values($resultado['tablas']),
        ));
    }

    /**
     * Añade 60 días para cinco residentes ficticios existentes dentro del alcance del enfermero.
     * Los responsables médicos/fisioterapéuticos son explícitos y deben tener la competencia.
     * Las jornadas históricas del fixture se identifican en la auditoría como simuladas.
     *
     * @return array{version: string, desde: string, hasta: string, nuevos: int, existentes: int, jornadas_nuevas: int, rectificaciones_nuevas: int, rectificaciones_totales: int, tablas: array, casos: array}
     *
     * @throws RuntimeException Si el destino, profesional, residente, historia o lote no es coherente.
     */
    public function ejecutar(string $basePermitida, string $enfermeroId, string $medicoId, string $fisioterapeutaId, string $areaMedicaId, string $areaFuncionalId, string $turnoId): array
    {
        $this->comprobarDestino($basePermitida);

        return DB::transaction(function () use ($enfermeroId, $medicoId, $fisioterapeutaId, $areaMedicaId, $areaFuncionalId, $turnoId): array {
            $enfermero = $this->profesional($enfermeroId, 'ENFERMEROS', 'ENFERMERIA');
            $medico = $this->profesional($medicoId, 'MEDICO GENERAL/GERIATRA', 'MEDICINA');
            $fisio = $this->profesional($fisioterapeutaId, 'FISIOTERAPEUTA', 'FISIOTERAPIA');
            $area = Area::query()->whereKey($areaMedicaId)->whereIn('estado', ['ACTIVA', 'ACTIVO'])->sole();
            if (! str_contains(mb_strtoupper($area->nombre), 'MEDICINA')) {
                throw new RuntimeException('El área médica explícita no corresponde a Medicina.');
            }
            $areaFuncional = Area::query()->whereKey($areaFuncionalId)->whereIn('estado', ['ACTIVA', 'ACTIVO'])->sole();
            if (! str_contains(mb_strtoupper($areaFuncional->nombre), 'FISIOTERAPIA')) {
                throw new RuntimeException('El área funcional explícita no corresponde a Fisioterapia.');
            }
            $desde = CarbonImmutable::parse(self::HASTA, config('app.timezone'))->subDays(59);
            if (CarbonImmutable::parse(self::HASTA, config('app.timezone'))->endOfDay()->isFuture()) {
                throw new RuntimeException('La carga retrospectiva no puede incluir fechas futuras.');
            }
            $residentes = Residente::query()->whereIn('numero_documento', array_keys(self::CASOS))
                ->orderBy('cod_residente')->lockForUpdate()->get()->keyBy('numero_documento');
            if ($residentes->count() !== count(self::CASOS)) {
                throw new RuntimeException('Faltan residentes sintéticos: no se crearán como sustitución.');
            }
            foreach ($residentes as $r) {
                $admitido = $r->admisiones()->where('fecha_hora_admision', '<=', $desde->startOfDay())->exists();
                $asignado = $r->asignacionesJornada()->where('cod_personal', $enfermero->getKey())->where('estado', 'ACTIVA')
                    ->whereHas('jornada', fn ($q) => $q->whereDate('fecha_jornada', now()->toDateString()))->exists();
                if ($r->estado !== 'ADMITIDO' || ! $admitido || ! $asignado) {
                    throw new RuntimeException('Residente sin admisión previa o fuera del alcance actual del enfermero: '.$r->getKey());
                }
            }
            $turno = Turno::query()->whereKey($turnoId)->where('estado', 'ACTIVO')->sole();
            if ($turno->hora_inicio !== '00:00:00' || $turno->hora_cierre !== '23:59:59') {
                throw new RuntimeException('Especifique el turno existente de guardia continua 24H para el historial simulado.');
            }
            $jornadas = Jornada::query()->where('cod_turno', $turnoId)
                ->whereDate('fecha_jornada', '>=', $desde->toDateString())->whereDate('fecha_jornada', '<=', self::HASTA)
                ->get()->keyBy(fn ($j): string => substr((string) $j->getRawOriginal('fecha_jornada'), 0, 10));
            $nuevas = 0;
            for ($i = 0; $i < 60; $i++) {
                $fecha = $desde->addDays($i)->toDateString();
                if (! isset($jornadas[$fecha])) {
                    $jornadas[$fecha] = Jornada::create(['cod_turno' => $turnoId, 'fecha_jornada' => $fecha,
                        'cod_usuario_apertura' => $enfermero->cod_usuario, 'cod_usuario_cierre' => $enfermero->cod_usuario, 'estado' => 'CERRADA']);
                    $nuevas++;
                }
            }
            $plan = $this->plan($residentes->all(), $enfermero, $medico, $fisio, $areaMedicaId, $areaFuncionalId, $desde,
                $jornadas->map(fn ($j) => $j->getKey())->all());

            return app(GeneradorCodigo::class)->serializar('controles_cognitivos', function () use ($plan, $residentes, $enfermero, $desde, $nuevas, $turnoId): array {
                $cantidades = array_map(count(...), $plan);
                $guardados = [];
                foreach ($plan as $tabla => $grupo) {
                    $campo = $this->campoMarca($tabla);
                    $originales = DB::table($tabla)->where($campo, 'like', '['.self::VERSION.':%')->get();
                    $guardados[$tabla] = $originales
                        ->keyBy(function (object $fila) use ($campo): string {
                            return substr($fila->$campo, 1, strpos($fila->$campo, ']') - 1);
                        })->all();
                    if (count($guardados[$tabla]) !== $originales->count()) {
                        throw new RuntimeException('Identidades de lote duplicadas en '.$tabla.'.');
                    }
                }
                $existe = array_sum(array_map(count(...), $guardados)) > 0;
                $ids = [];
                foreach ($plan as $tabla => $grupo) {
                    if ($existe && count($guardados[$tabla]) !== count($grupo)) {
                        throw new RuntimeException('Lote parcial o alterado en '.$tabla.'; no se completará ni sobrescribirá.');
                    }
                    $pk = CatalogoCodigos::ENTIDADES[$tabla][0];
                    foreach ($grupo as $clave => $datos) {
                        if ($existe && ! isset($guardados[$tabla][$clave])) {
                            throw new RuntimeException('Falta una observación original del lote: '.$clave);
                        }
                        $ids[$clave] = $existe ? $guardados[$tabla][$clave]->$pk : app(GeneradorCodigo::class)->siguiente($tabla);
                    }
                }
                foreach ($plan as $tabla => $grupo) {
                    $filas = [];
                    foreach ($grupo as $clave => $datos) {
                        foreach ($datos as $campo => $valor) {
                            if (is_string($valor) && str_starts_with($valor, '@')) {
                                $datos[$campo] = $ids[substr($valor, 1)] ?? throw new RuntimeException('Referencia interna inexistente.');
                            }
                        }
                        if ($existe) {
                            $this->comparar($tabla, $guardados[$tabla][$clave], $datos);
                        } else {
                            $filas[] = [CatalogoCodigos::ENTIDADES[$tabla][0] => $ids[$clave]] + $datos;
                        }
                    }
                    foreach (array_chunk($filas, 150) as $lote) {
                        DB::table($tabla)->insert($lote);
                    }
                }
                $rectificaciones = $this->rectificarCoincidencias($enfermero);
                if (! $existe) {
                    activity('Desarrollo')->causedBy($enfermero->usuario)
                        ->withProperties(['dataset' => self::VERSION, 'residentes' => $residentes->pluck('cod_residente')->all(),
                            'tablas' => $cantidades, 'turno_retrospectivo_simulado' => $turnoId, 'jornadas_nuevas' => $nuevas])
                        ->log('Carga retrospectiva ficticia para comprobar pantallas y límites del sistema experto.');
                }

                return ['version' => self::VERSION, 'desde' => $desde->toDateString(), 'hasta' => self::HASTA,
                    'nuevos' => $existe ? 0 : array_sum($cantidades), 'existentes' => $existe ? array_sum($cantidades) : 0,
                    'jornadas_nuevas' => $nuevas, 'rectificaciones_nuevas' => $rectificaciones,
                    'rectificaciones_totales' => ControlCognitivo::query()->where('observacion', 'like', '[COR-'.self::VERSION.':%')->count(),
                    'tablas' => $cantidades, 'casos' => $residentes->map(fn ($r) => [
                        'residente' => $r->getKey(), 'nombre' => $r->nombre_completo,
                        'perfil' => self::CASOS[$r->numero_documento]['perfil'],
                    ])->values()->all()];
            });
        });
    }

    /** Ningún entorno o nombre genérico habilita por sí solo una carga. */
    private function comprobarDestino(string $permitida): void
    {
        $c = DB::connection();
        if (! app()->environment(['local', 'testing']) || $permitida === '' || $permitida !== $c->getDatabaseName()) {
            throw new RuntimeException('Carga sintética prohibida: confirme la base exacta local/testing.');
        }
        if (app()->environment('testing') && ! BaseTecnicaDesechable::permite($c->getDriverName(), $c->getDatabaseName(), $c->getConfig('username'), $c->getConfig('host'))) {
            throw new RuntimeException('La base testing debe ser desechable.');
        }
        if (app()->environment('local') && ($c->getDriverName() !== 'pgsql'
            || ! in_array($c->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)
            || preg_match('/prod(uction|uccion)?/i', $permitida))) {
            throw new RuntimeException('La carga local requiere PostgreSQL de desarrollo en este equipo.');
        }
    }

    /** Cuenta y competencia explícitas; no toma el primer profesional ni crea un reemplazo. */
    private function profesional(string $id, string $rol, string $profesion): Personal
    {
        $p = Personal::query()->with('usuario.roles')->whereKey($id)->lockForUpdate()->first();
        if (! $p || $p->estado !== 'ACTIVO' || $p->usuario?->estado !== 'ACTIVO'
            || ! $p->usuario->hasRole($rol) || ! str_contains(mb_strtoupper((string) $p->profesion), $profesion)) {
            throw new RuntimeException('Profesional explícito sin cuenta activa o competencia '.$rol.': '.$id);
        }

        return $p;
    }

    /** Genera observaciones reproducibles; las variaciones son datos ficticios, nunca umbrales. */
    private function plan(array $residentes, Personal $nurse, Personal $doctor, Personal $fisio, string $area, string $areaFuncional, CarbonImmutable $desde, array $jornadas): array
    {
        $plan = array_fill_keys(['atenciones', 'notas_clinicas', 'valoraciones_funcionales', 'controles_cognitivos',
            'signos_vitales', 'registros_sueno', 'registros_ingesta', 'registros_hidratacion',
            'registros_eliminacion', 'registros_movilidad', 'registros_conductuales', 'valoraciones_dolor', 'incidentes'], []);
        foreach (self::CASOS as $documento => $caso) {
            $r = $residentes[$documento];
            $alto = $caso['perfil'] === 'MAYOR_AFECTACION';
            $persistente = $caso['perfil'] === 'PERSISTENTE';
            $olvidos = $caso['perfil'] === 'OLVIDOS';
            $clinico = ['cod_residente' => $r->getKey(), 'cod_personal' => $nurse->getKey(), 'estado' => 'VIGENTE'];
            $cuidados = $clinico + ['cod_jornada' => null];
            $visitas = array_unique(array_merge(range(0, 56, 7), [59], $caso['caidas']));
            sort($visitas);
            foreach ($visitas as $i) {
                $fecha = $desde->addDays($i)->toDateString();
                $clave = self::VERSION.':'.$documento.':MED:'.$fecha;
                $plan['atenciones'][$clave] = ['cod_residente' => $r->getKey(), 'cod_personal' => $doctor->getKey(),
                    'cod_area' => $area, 'tipo_atencion' => 'CONSULTA_MEDICA', 'motivo' => 'Seguimiento retrospectivo simulado de memoria, autonomía y antecedentes.',
                    'fecha_hora' => $fecha.' 13:30:00', 'estado' => 'FINALIZADA',
                    'observacion' => '['.$clave.'] '.$caso['descripcion'].' Sin diagnóstico DCL asignado; no se han adjudicado los cinco requisitos ni realizado evaluación instrumental.'];
                $this->agregar($plan, 'notas_clinicas', $documento, $fecha, 'MED',
                    ['cod_residente' => $r->getKey(), 'cod_personal' => $doctor->getKey(), 'cod_atencion' => '@'.$clave,
                        'cod_nota_anterior' => null, 'tipo_nota' => 'EVOLUCION', 'fecha_hora' => $fecha.' 13:40:00',
                        'motivo_correccion' => null, 'estado' => 'VIGENTE'],
                    $caso['descripcion'].' Se contrastan los controles descriptivos y el apoyo cotidiano. Caso ficticio; pendiente evaluación independiente de los requisitos DCL.');
                if (in_array($i, [0, 14, 28, 42, 59], true)) {
                    $claveFuncional = self::VERSION.':'.$documento.':FIS:'.$fecha;
                    $plan['atenciones'][$claveFuncional] = ['cod_residente' => $r->getKey(), 'cod_personal' => $fisio->getKey(),
                        'cod_area' => $areaFuncional, 'tipo_atencion' => 'FISIOTERAPIA', 'motivo' => 'Observación funcional retrospectiva simulada.',
                        'fecha_hora' => $fecha.' 14:00:00', 'estado' => 'FINALIZADA',
                        'observacion' => '['.$claveFuncional.'] SIMULACIÓN DE DESARROLLO. Actividades básicas y apoyos observados; no es una valoración IADL.'];
                    $this->agregar($plan, 'valoraciones_funcionales', $documento, $fecha, 'FUNC',
                        ['cod_residente' => $r->getKey(), 'cod_personal' => $fisio->getKey(), 'cod_atencion' => '@'.$claveFuncional,
                            'fecha_hora' => $fecha.' 14:10:00', 'marcha' => $alto ? 'ASISTIDA' : 'INDEPENDIENTE',
                            'equilibrio' => $alto ? 'INESTABLE' : 'ESTABLE', 'traslado' => $alto ? 'ASISTIDO' : 'INDEPENDIENTE',
                            'fuerza_funcional' => $alto ? 'DISMINUIDA' : 'CONSERVADA', 'resistencia' => $alto ? 'LIMITADA' : 'CONSERVADA',
                            'alimentacion_autonoma' => 'INDEPENDIENTE', 'bano_autonomo' => $alto ? 'ASISTIDO' : 'INDEPENDIENTE',
                            'vestido_autonomo' => $alto ? 'ASISTIDO' : 'INDEPENDIENTE', 'higiene_autonoma' => 'INDEPENDIENTE',
                            'continencia' => 'CONTINENTE', 'movilidad_autonoma' => $alto ? 'ASISTIDO' : 'INDEPENDIENTE',
                            'necesita_supervision' => $alto, 'nivel_dependencia' => $alto ? 'PARCIAL' : 'INDEPENDIENTE',
                            'recomendacion' => 'Comparar apoyos y actividades observadas; esta valoración básica no acredita IADL ni C03.', 'estado' => 'VIGENTE'],
                        'Registro simulado de actividades básicas y apoyos físicos. No determina la causa cognitiva de una limitación.');
                }
            }
            for ($i = 0; $i < 60; $i++) {
                $fecha = $desde->addDays($i)->toDateString();
                $cuidados['cod_jornada'] = $jornadas[$fecha];
                $dificultad = $alto || ($persistente && $i >= 14) || ($olvidos && $i % 3 !== 0);
                $incompleto = $caso['perfil'] === 'INCOMPLETO' && $i % 4 === 3;
                $sueno = $persistente && $i % 5 < 2;
                $this->agregar($plan, 'controles_cognitivos', $documento, $fecha, 'COG', $cuidados + [
                    'cod_atencion' => null, 'fecha_hora' => $fecha.' 09:40:00', 'orientacion_persona' => 'ORIENTADO',
                    'orientacion_lugar' => $alto ? 'PARCIALMENTE_ORIENTADO' : 'ORIENTADO',
                    'orientacion_tiempo' => $alto ? 'DESORIENTADO' : ($persistente && $dificultad ? 'PARCIALMENTE_ORIENTADO' : 'ORIENTADO'),
                    'memoria_reciente' => $incompleto ? null : ($alto ? 'ALTERADA' : ($dificultad ? 'ALTERACION_LEVE' : 'CONSERVADA')),
                    'memoria_remota' => $incompleto ? 'NO_VALORABLE' : 'CONSERVADA',
                    'atencion' => $incompleto ? null : ($alto || ($persistente && $i % 3 === 0) ? 'DISMINUIDA' : 'CONSERVADA'),
                    'comprension' => $alto ? 'ALTERADA' : 'CONSERVADA', 'lenguaje' => $alto ? 'ALTERADO' : 'CONSERVADO',
                    'sigue_instrucciones' => ! $alto, 'repite_preguntas' => $incompleto ? null : $dificultad,
                    'olvida_indicaciones' => $incompleto ? null : $dificultad, 'reconoce_personas' => true,
                    'reconoce_entorno' => ! $alto, 'confusion' => false, 'cambio_cognitivo' => $dificultad,
                ], $incompleto ? 'No se completó la observación de memoria reciente y atención; se conservan NULL y NO_VALORABLE.'
                    : ($dificultad ? 'Repite una pregunta tras la explicación y requiere recordar la indicación. '.$caso['descripcion']
                        : 'Durante esta oportunidad identifica al personal y sigue la explicación sin repetición observada. No acredita preservación de un dominio completo.'));
                foreach (['08:20:00', '18:20:00'] as $hora) {
                    $this->agregar($plan, 'signos_vitales', $documento, $fecha, 'SV'.$hora, $cuidados + [
                        'cod_atencion' => null, 'fecha_hora' => $fecha.' '.$hora, 'presion_sistolica' => 112 + $i % 18,
                        'presion_diastolica' => 66 + $i % 12, 'frecuencia_cardiaca' => 64 + $i % 17,
                        'frecuencia_respiratoria' => 16 + $i % 3, 'temperatura' => round(36.2 + ($i % 6) / 10, 1),
                        'saturacion_oxigeno' => 96 + $i % 3, 'glucemia' => 92 + $i % 32,
                    ], 'Mediciones ficticias en reposo para históricos; no se usan como baremo cognitivo.');
                }
                $this->agregar($plan, 'registros_sueno', $documento, $fecha, 'SUE', $cuidados + [
                    'fecha' => $fecha, 'horas_sueno' => $sueno ? 4.5 : 6.5 + ($i % 3) / 2,
                    'despertares' => $sueno ? 3 : $i % 2, 'insomnio' => $sueno, 'somnolencia_diurna' => $sueno,
                    'agitacion_nocturna' => false, 'calidad' => $sueno ? 'INTERRUMPIDO' : 'NORMAL',
                ], 'Resumen nocturno simulado; la coincidencia temporal no demuestra causa del olvido.');
                foreach (['DESAYUNO' => '08:30:00', 'ALMUERZO' => '12:30:00', 'CENA' => '18:30:00'] as $comida => $hora) {
                    $this->agregar($plan, 'registros_ingesta', $documento, $fecha, $comida, $cuidados + [
                        'fecha_hora' => $fecha.' '.$hora, 'tipo_comida' => $comida,
                        'porcentaje_consumido' => $alto ? 70 + $i % 21 : 85 + $i % 16,
                        'apetito' => $alto ? 'PARCIAL' : 'COMPLETA', 'tolerancia' => 'BUENA', 'dificultad_deglucion' => false,
                    ], $alto ? 'Precisa recordatorios para continuar la comida; porcentaje observado ficticio.' : 'Comida y tolerancia observadas en el escenario ficticio.');
                }
                foreach (['09:00:00', '11:00:00', '15:00:00', '17:00:00'] as $hora) {
                    $this->agregar($plan, 'registros_hidratacion', $documento, $fecha, 'HID'.$hora, $cuidados + [
                        'fecha_hora' => $fecha.' '.$hora, 'cantidad_ml' => 180 + ($i % 4) * 20,
                        'tipo_liquido' => 'AGUA', 'tolerancia' => 'ADECUADA',
                    ], 'Ingesta oral ficticia medida en esta oportunidad; no representa el balance diario completo.');
                }
                foreach (['URINARIA', 'INTESTINAL'] as $tipo) {
                    $this->agregar($plan, 'registros_eliminacion', $documento, $fecha, $tipo, $cuidados + [
                        'fecha_hora' => $fecha.($tipo === 'URINARIA' ? ' 10:00:00' : ' 10:30:00'), 'tipo_eliminacion' => $tipo,
                        'cantidad' => $tipo === 'URINARIA' ? '250 ml' : 'Una deposición',
                        'caracteristica' => $tipo === 'URINARIA' ? 'Amarilla clara' : 'Formada', 'continencia' => 'CONTINENTE',
                        'cantidad_cualitativa' => 'HABITUAL', 'volumen_ml' => $tipo === 'URINARIA' ? 250 : null,
                        'color_orina' => $tipo === 'URINARIA' ? 'AMARILLO_CLARO' : null,
                        'aspecto_orina' => $tipo === 'URINARIA' ? 'CLARO' : null,
                        'olor_orina' => $tipo === 'URINARIA' ? 'HABITUAL' : null,
                        'tipo_miccion' => $tipo === 'URINARIA' ? 'ESPONTANEA' : null,
                        'tipo_bristol' => $tipo === 'INTESTINAL' ? 4 : null,
                        'color_heces' => $tipo === 'INTESTINAL' ? 'MARRON' : null,
                        'esfuerzo_defecacion' => $tipo === 'INTESTINAL' ? 'SIN_ESFUERZO' : null,
                        'presencia_sangre' => false, 'presencia_moco' => $tipo === 'INTESTINAL' ? false : null,
                        'molestia_eliminacion' => false, 'descripcion_molestia' => null,
                    ], 'Registro ficticio de eliminación sin molestias observadas en esta oportunidad.');
                }
                $this->agregar($plan, 'registros_movilidad', $documento, $fecha, 'MOV', $cuidados + [
                    'cod_atencion' => null, 'fecha_hora' => $fecha.' 09:20:00', 'marcha' => $alto ? 'ASISTIDA' : 'INDEPENDIENTE',
                    'equilibrio' => $alto ? 'INESTABLE' : 'ESTABLE', 'traslado' => $alto ? 'AYUDA_UNA_PERSONA' : 'INDEPENDIENTE',
                    'tipo_apoyo' => $alto ? 'UNA_PERSONA' : 'INDEPENDIENTE', 'dispositivo' => $alto ? 'ANDADOR' : 'NINGUNO',
                    'fatiga' => $alto ? 'LEVE' : 'SIN_FATIGA', 'riesgo_caida' => null,
                    'motivo_registro' => 'CONTROL_DIARIO', 'actividad_realizada' => 'CAMINAR_PASILLO',
                    'distancia_metros' => ($alto ? 18 : 55) + $i % 12, 'dolor_movilidad' => $alto,
                    'mareo' => false, 'disnea' => false, 'debilidad' => $alto,
                    'cambio_habitual' => 'SIN_CAMBIOS', 'tolerancia_movilidad' => $alto ? 'PARCIAL' : 'BUENA',
                ], 'Trayecto simulado por pasillo, '.($alto ? 'con apoyo físico y pausas.' : 'sin ayuda observada.').' No se deriva un riesgo ni un diagnóstico desde el seeder.');
                $this->agregar($plan, 'registros_conductuales', $documento, $fecha, 'CON', $cuidados + [
                    'cod_atencion' => null, 'fecha_hora' => $fecha.' 09:10:00', 'estado_animo' => $sueno ? 'ANSIOSO' : 'TRANQUILO',
                    'apatia' => false, 'agitacion' => false, 'agresividad' => false, 'ansiedad' => $sueno,
                    'aislamiento' => false, 'deambulacion' => false, 'participacion' => $alto ? 'PARCIAL' : 'ACTIVA',
                    'cambio_conducta' => $sueno, 'intervencion' => 'Explicación y acompañamiento simulados.', 'respuesta' => 'Acepta el acompañamiento.',
                ], 'Interacción ficticia documentada; no equivale a un criterio cognitivo.');
                $this->agregar($plan, 'valoraciones_dolor', $documento, $fecha, 'DOL', $clinico + [
                    'cod_atencion' => null, 'fecha_hora' => $fecha.' 09:00:00', 'intensidad' => $alto ? 1 + $i % 3 : 0,
                    'ubicacion' => $alto ? 'Rodilla derecha' : null, 'tipo_dolor' => $alto ? 'MOLESTIA' : null,
                    'duracion' => $alto ? 'Durante el trayecto simulado' : null, 'desencadenante' => $alto ? 'Movimiento' : null,
                    'intervencion' => 'Observación y posición de descanso simuladas.', 'respuesta' => $alto ? 'Refiere menor molestia al reposar.' : 'No refiere dolor en esta oportunidad.',
                ], 'Valoración ficticia, sin prescripción ni administración de tratamiento.');
                if (in_array($i, $caso['caidas'], true)) {
                    $this->agregar($plan, 'incidentes', $documento, $fecha, 'CAIDA', [
                        'cod_residente' => $r->getKey(), 'cod_personal' => $nurse->getKey(), 'cod_jornada' => null,
                        'tipo_incidente' => 'CAIDA', 'gravedad' => 'LEVE', 'lugar' => $i % 2 ? 'Pasillo' : 'Habitación',
                        'fecha_hora' => $fecha.' 11:10:00', 'descripcion' => 'Caída ficticia durante un traslado; sin lesión documentada en el escenario.',
                        'medida_inmediata' => 'Acompañamiento y revisión médica simulada de la misma fecha, conservada en atenciones.',
                        'requiere_medico' => true, 'requiere_derivacion' => false, 'estado' => 'CERRADO',
                    ], 'Incidente simulado: no constituye evidencia de DCL y no crea una alerta experta.');
                }
            }
        }

        return $plan;
    }

    /** Marca legible de procedencia del fixture; los datos permanecen en columnas relacionales. */
    private function agregar(array &$plan, string $tabla, int|string $documento, string $fecha, string $tipo, array $datos, string $texto): void
    {
        $clave = self::VERSION.':'.$documento.':'.$tipo.':'.$fecha;
        $campo = $this->campoMarca($tabla);
        $datos[$campo] = '['.$clave.'] SIMULACIÓN DE DESARROLLO. '.($datos[$campo] ?? '').' '.$texto;
        $plan[$tabla][$clave] = $datos;
    }

    private function campoMarca(string $tabla): string
    {
        return match ($tabla) {
            'notas_clinicas' => 'contenido', 'valoraciones_funcionales' => 'conclusion',
            'registros_conductuales' => 'descripcion', 'valoraciones_dolor' => 'intervencion', default => 'observacion',
        };
    }

    /**
     * Corrige solo la hora ficticia del lote cuando coincide con historia ajena.
     * El original conserva fecha, valores y texto; cambia a RECTIFICADO y se crea
     * un sucesor a las 09:45 con referencia al original y auditoría de cada par.
     */
    private function rectificarCoincidencias(Personal $autor): int
    {
        $originales = ControlCognitivo::query()->where('observacion', 'like', '['.self::VERSION.':%')
            ->where('estado', 'VIGENTE')->orderBy('cod_control_cognitivo')->lockForUpdate()->get();
        $cantidad = 0;
        foreach ($originales as $original) {
            $coincide = ControlCognitivo::query()->where('cod_residente', $original->cod_residente)
                ->where('fecha_hora', $original->getRawOriginal('fecha_hora'))->whereKeyNot($original->getKey())
                ->where('estado', 'VIGENTE')->where(fn ($q) => $q->whereNull('observacion')
                ->orWhere('observacion', 'not like', '['.self::VERSION.':%'))->exists();
            if (! $coincide) {
                continue;
            }
            $datos = $this->datosRectificacion($original->getAttributes());
            if (ControlCognitivo::query()->where('cod_residente', $original->cod_residente)->where('fecha_hora', $datos['fecha_hora'])->exists()) {
                throw new RuntimeException('La hora prevista para rectificar también está ocupada; no se sustituirá historia.');
            }
            $corregido = ControlCognitivo::create($datos);
            $original->update(['estado' => 'RECTIFICADO']);
            activity('Desarrollo')->causedBy($autor->usuario)->performedOn($original)
                ->withProperties(['dataset' => self::VERSION, 'original' => $original->getKey(), 'sucesor' => $corregido->getKey(),
                    'motivo' => 'Hora ficticia coincidente con una observación anterior; se conserva el original.'])
                ->log('Rectificación trazable de hora en escenario cognitivo simulado.');
            $cantidad++;
        }

        return $cantidad;
    }

    private function datosRectificacion(array $original): array
    {
        $id = $original['cod_control_cognitivo'];
        unset($original['cod_control_cognitivo']);
        $original['fecha_hora'] = substr($original['fecha_hora'], 0, 10).' 09:45:00';
        $original['estado'] = 'VIGENTE';
        $original['observacion'] = '[COR-'.self::VERSION.':'.$id.'] SIMULACIÓN. Rectifica la hora ficticia del original '.$id
            .'; no es otra corroboración independiente. '.$original['observacion'];

        return $original;
    }

    /** Compara sin confundir NULL con cero/false, admitiendo los tipos numéricos de PDO. */
    private function comparar(string $tabla, object $fila, array $esperados): void
    {
        foreach ($esperados as $campo => $valor) {
            $original = $fila->$campo;
            if ($tabla === 'controles_cognitivos' && $campo === 'estado' && $valor === 'VIGENTE' && $original === 'RECTIFICADO') {
                $datos = $this->datosRectificacion((array) $fila);
                $sucesores = DB::table($tabla)->where('observacion', $datos['observacion'])->get();
                if ($sucesores->count() !== 1) {
                    throw new RuntimeException('Rectificación del lote incompleta o duplicada.');
                }
                $this->comparar($tabla, $sucesores->sole(), $datos);

                continue;
            }
            $igual = match (true) {
                $valor === null => $original === null,
                is_bool($valor) => in_array($original, $valor ? [true, 1, '1'] : [false, 0, '0'], true),
                is_int($valor), is_float($valor) => is_numeric($original) && (float) $original === (float) $valor,
                default => $original === $valor,
            };
            if (! $igual) {
                throw new RuntimeException('Contenido del lote alterado en '.$tabla.'.'.$campo.'; no se sobrescribirá.');
            }
        }
    }
}
