<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\ControlFuncionalIadl;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResidentesSistemaExperto;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\MisResidentesPsicologia;
use Carbon\CarbonImmutable;
use Database\Seeders\CohorteCognitivaInvestigacionSeeder as CohorteInferencia30;
use Database\Seeders\Datos\ManifiestoCognitivo30;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use LogicException;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use Tests\Support\EsquemaSinTransaccion;
use Tests\Support\ExportarPantallaSintetica;
use Tests\TestCase;

/** El cálculo procede del motor real y fuentes SQL; ninguna salida esperada se inserta en BDD. */
final class CohorteInferencia30Test extends TestCase
{
    use EsquemaSinTransaccion;

    public function test_treinta_admisiones_fuentes_originales_dos_ramas_y_cuatro_perfiles_reproducibles(): void
    {
        $configAnterior = config('sistema_experto.perfiles_instrumentales');
        $simulacionAnterior = config('sistema_experto.simulacion_local');
        $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00:00'));
        $carga = new CohorteInferencia30;
        $destinoAnterior = getenv('RM_COHORTE30_DESTINO');
        try {
            putenv('RM_COHORTE30_DESTINO='.DB::connection()->getDatabaseName());
            $this->app->instance(CohorteInferencia30::class, $carga);
            $this->artisan('db:seed', ['--class' => CohorteInferencia30::class, '--force' => true])->assertExitCode(0);
            $residentes = $carga->residentes;
            $manifiesto = CohorteInferencia30::manifiesto();
            // Petición nueva: los bindings deben recuperarse de SQL, sin heredar la config del seeder.
            config(['sistema_experto.perfiles_instrumentales' => [], 'sistema_experto.simulacion_local' => [
                'habilitada' => true, 'base_permitida' => DB::connection()->getDatabaseName()]]);
            $this->app->detectEnvironment(fn () => 'production');
            self::assertSame([], app(CohorteCognitivaLocal::class)->perfiles($residentes['S01']));
            $this->app->detectEnvironment(fn () => 'local');
            config(['sistema_experto.simulacion_local.base_permitida' => 'otra_base']);
            self::assertSame([], app(CohorteCognitivaLocal::class)->perfiles($residentes['S01']));
            config(['sistema_experto.simulacion_local.base_permitida' => DB::connection()->getDatabaseName()]);
            self::assertSame($carga->perfiles, app(CohorteCognitivaLocal::class)->perfiles($residentes['S01']));
            $ajeno = clone $residentes['S01'];
            $ajeno->numero_documento = 'NO-PERTENECE-AL-LOTE';
            self::assertSame([], app(CohorteCognitivaLocal::class)->perfiles($ajeno));
            $this->actingAs($carga->psicologia);
            Livewire::test(MisResidentesPsicologia::class)->set('search', 'entrenamiento')
                ->assertViewHas('residentes', fn ($p) => $p->total() === 30)->assertSee('Alvarado');
            Livewire::test(ResidentesSistemaExperto::class)->set('busqueda', 'entrenamiento')
                ->assertViewHas('residentes', fn ($p) => $p->total() === 30)->assertSee('Alvarado');
            $otroNombre = clone $residentes['S01'];
            $otroNombre->nombres = 'Nombre administrativo corregido';
            self::assertSame($carga->perfiles, app(CohorteCognitivaLocal::class)->perfiles($otroNombre));
            self::assertSame(0, DB::transactionLevel());
            self::assertCount(30, $residentes);
            self::assertSame(0, DB::table('usuarios')->where('correo', 'not like', '%@example.invalid')->count());
            self::assertDatabaseCount('admisiones', 30);
            self::assertDatabaseCount('ocupaciones_cama', 30);
            self::assertDatabaseCount('residentes_contactos', 30);
            self::assertDatabaseCount('diagnosticos', 30);
            self::assertDatabaseCount('antecedentes_clinicos', 30);
            self::assertDatabaseCount('incidentes', 15);
            self::assertDatabaseCount('aplicaciones_instrumento', 149);
            self::assertDatabaseCount('controles_cognitivos', 480);
            foreach (ControlFuncionalIadl::query()->get() as $iadl) {
                foreach (['actividad' => ControlFuncionalIadl::ACTIVIDADES, 'oportunidad' => ControlFuncionalIadl::OPORTUNIDADES,
                    'desempeno' => ControlFuncionalIadl::DESEMPENOS, 'tipo_ayuda' => ControlFuncionalIadl::AYUDAS,
                    'causa_ayuda' => ControlFuncionalIadl::CAUSAS, 'nivel_previo' => ControlFuncionalIadl::NIVELES_PREVIOS,
                    'fuente' => ControlFuncionalIadl::FUENTES] as $campo => $catalogo) {
                    self::assertContains($iadl->$campo, $catalogo);
                }
            }
            self::assertSame(0, DB::table('ocupaciones_cama')->select('cod_residente')->groupBy('cod_residente')->havingRaw('count(*) > 1')->get()->count());
            self::assertSame(0, DB::table('ocupaciones_cama')->select('cod_cama')->groupBy('cod_cama')->havingRaw('count(*) > 1')->get()->count());
            self::assertSame(0, DB::table('admisiones as a')->join('preadmisiones as p', 'p.cod_preadmision', '=', 'a.cod_preadmision')
                ->whereColumn('p.fecha_revision', '>', 'a.fecha_hora_admision')->count());
            self::assertSame(0, ValoracionPetersenOriginal::query()->where('elemento', 'C02')->count());
            self::assertSame(0, DB::table('residentes')->where('estado', '!=', 'ADMITIDO')->count());
            self::assertSame(0, DB::table('respuestas_instrumento as r')->join('preguntas_instrumento as p', 'p.cod_pregunta', '=', 'r.cod_pregunta')
                ->join('aplicaciones_instrumento as a', 'a.cod_aplicacion', '=', 'r.cod_aplicacion')->whereColumn('p.cod_instrumento', '!=', 'a.cod_instrumento')->count());
            self::assertSame(0, AplicacionInstrumento::query()->where('cod_personal', '!=', $carga->psicologia->personal->getKey())->count());
            self::assertSame(0, ValoracionPetersenOriginal::query()->where('cod_usuario_registro', '!=', $carga->psicologia->getKey())->count());
            // El rol solo no concede lectura de la fuente psicológica. Se prueba primero
            // la denegación y después una cuenta explícitamente autorizada, solo en esta BDD sintética.
            self::assertFalse($carga->medicina->checkPermissionTo('valoraciones_psicologicas.ver', 'web'));
            $this->actingAs($carga->medicina);
            Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residentes['S01']])->assertForbidden();
            $carga->medicina->givePermissionTo('valoraciones_psicologicas.ver');
            $this->actingAs($carga->psicologia);
            $escrituras = [];
            DB::listen(static function ($q) use (&$escrituras): void {
                if (preg_match('/^\s*(insert|update|delete|replace|alter|create|drop|truncate)\b/i', $q->sql)) {
                    $escrituras[] = strtok(ltrim($q->sql), ' ');
                }
            });
            $resultados = [];
            foreach (CohorteInferencia30::casos() as $i => $caso) {
                $dto = app(PrepararEvaluacionInstitucional::class)->preparar($carga->psicologia, $residentes[$caso['caso']], CarbonImmutable::now());
                $p = $dto->contenido['petersen']['contenido']['salida'];
                $aceptacion = $manifiesto['aceptacion'][$caso['caso']];
                $esperado = $aceptacion['patron_esperado'];
                self::assertSame($esperado, $p['patron'], $caso['caso'].' '.json_encode(['dominios' => $p['dominios'], 'requisitos' => $p['requisitos'], 'impedimentos' => $p['impedimentos']], JSON_UNESCAPED_UNICODE));
                self::assertSame($aceptacion['estado_esperado'], $p['estado'], $caso['caso']);
                self::assertCount(5, $p['dominios']);
                self::assertCount(5, $p['requisitos']);
                $c02Esperado = match (true) {
                    $i >= 12 && $i < 18 => 'N', $caso['variante'] === 'contradiccion_memoria' => 'X',
                    $caso['variante'] === 'contexto_no_interpretado' => 'U', default => 'S',
                };
                self::assertSame($c02Esperado, $p['requisitos']['C02']['estado'], $caso['caso']);
                self::assertFalse($dto->contenido['persistido']);
                $capturas = $dto->contenido['instrumental']['contenido']['capturas']['capturas'];
                self::assertCount($i === 23 ? 4 : 5, $capturas);
                foreach ($capturas as $captura) {
                    self::assertGreaterThanOrEqual(60, $captura['edad_en_aplicacion']);
                    self::assertSame([], $captura['motivos']);
                    self::assertNotEmpty($captura['extraccion']);
                    self::assertContains($captura['extraccion']['metodo'], array_values(CohorteInferencia30::METODOS));
                    self::assertSame([], $captura['extraccion']['motivos_captura']);
                    foreach ($captura['extraccion']['normalizacion']['componentes'] as $componente) {
                        self::assertSame('NORMALIZADO', $componente['estado']);
                        self::assertNull($componente['clasificacion_clinica']);
                    }
                    self::assertNotEmpty($captura['respuestas_originales']);
                    self::assertSame($residentes[$caso['caso']]->getKey(), $captura['cod_residente']);
                }
                $replay = InstantaneaEvaluacionInstitucional::verificar($dto->contenido, $dto->huella);
                self::assertSame($dto->contenido, $replay->contenido);
                self::assertSame($dto->huella, $replay->huella);
                if ($i < 12) {
                    self::assertSame([], $p['impedimentos']);
                    self::assertSame('PATRON_ORIENTATIVO_CON_SOPORTE', $p['estado']);
                } elseif ($i < 18) {
                    self::assertSame('SIN_PATRON_CLASIFICABLE_POR_REQUISITO', $p['estado']);
                    self::assertSame(['P', 'P', 'P', 'P', 'P'], array_values(array_column($p['dominios'], 'estado')));
                } else {
                    $this->comprobarAbstencion($caso['variante'], $p);
                }
                $resultados[] = ['caso' => $caso['caso'], 'sintetico' => true, 'variante' => $caso['variante'],
                    'edad' => $caso['edad'], 'caida_contextual' => $caso['caida'], 'diagnostico_contextual' => $caso['diagnostico_contextual'],
                    'patron_calculado' => $p['patron'], 'estado' => $p['estado'], 'dominios' => array_map(fn ($d) => $d['estado'], $p['dominios']),
                    'requisitos' => array_map(fn ($d) => $d['estado'], $p['requisitos']), 'impedimentos' => $p['impedimentos'],
                    'capturas_sql' => count($capturas), 'huella_dto' => $dto->huella, 'replay_memoria_identico' => true,
                    'conservacion_conjunta_sql' => false];
            }
            self::assertSame([], ManifiestoCognitivo30::comparar($manifiesto, $resultados)['discrepancias']);
            self::assertSame([], $escrituras, 'La consulta y replay no deben cambiar fuentes ni resultados.');
            self::assertDatabaseCount('evaluaciones_expertas', 0);
            $this->assertDatabaseMissing('valoraciones_petersen_originales', ['elemento' => 'C02']);
            $salidas = array_count_values(array_filter(array_column($resultados, 'patron_calculado')));
            self::assertSame(['PET-01' => 3, 'PET-02' => 3, 'PET-03' => 3, 'PET-04' => 3], $salidas);
            foreach (['S01' => 'amnésico de dominio único', 'S04' => 'amnésico multidominio',
                'S07' => 'no amnésico de dominio único', 'S10' => 'no amnésico multidominio'] as $caso => $etiqueta) {
                $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residentes[$caso]])
                    ->call('analizarEvidencia')->assertHasNoErrors()->assertSet('analisisEjecutado', true)
                    ->assertSee($etiqueta)->assertSee('Resultado calculado para esta consulta:')->assertSee('Residente de entrenamiento.');
                if ($directorio = getenv('RM_COHORTE30_PANTALLAS')) {
                    ExportarPantallaSintetica::guardar($directorio, $caso, $pagina->html());
                    $copia = file_get_contents($directorio.'/'.$caso.'.html');
                    self::assertStringContainsString('DATOS SINTÉTICOS · CASO FICTICIO', $copia);
                    self::assertStringContainsString('Perfil compatible con posible DCL '.$etiqueta, $copia);
                    self::assertStringNotContainsString('wire:snapshot', $copia);
                    self::assertStringNotContainsString('<script', $copia);
                }
            }
            $this->actingAs($carga->medicina);
            Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residentes['S01']])
                ->assertOk()->call('analizarEvidencia')->assertOk()->assertHasNoErrors()->assertSee('amnésico de dominio único');
            $this->actingAs($carga->psicologia);
            self::assertDatabaseCount('evaluaciones_expertas', 0);
            self::assertSame([], $escrituras, 'La vista no debe guardar resultados ni modificar registros originales.');
            $this->exportar($resultados);
            $this->comprobarFuenteAjena($carga, $residentes);
            $conteo = Residente::count();
            $this->app->detectEnvironment(fn () => 'testing');
            try {
                $carga->cargar();
                self::fail('No debe duplicar una cohorte ya cargada.');
            } catch (LogicException $e) {
                self::assertStringContainsString('ya existe', $e->getMessage());
            }
            self::assertSame($conteo, Residente::count());
        } finally {
            putenv($destinoAnterior === false ? 'RM_COHORTE30_DESTINO' : 'RM_COHORTE30_DESTINO='.$destinoAnterior);
            $this->app->detectEnvironment(fn () => 'testing');
            config(['sistema_experto.simulacion_local' => $simulacionAnterior]);
            config(['sistema_experto.perfiles_instrumentales' => $configAnterior]);
        }
    }

    private function comprobarAbstencion(string $variante, array $p): void
    {
        $motivo = match ($variante) {
            'c01_negativo', 'c03_negativo', 'c05_negativo' => 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO',
            'c04_negativo', 'impedimento_diferencial' => 'ABSTENCION_EVALUACION_DIFERENCIAL',
            'contradiccion_memoria' => 'ABSTENCION_CONTRADICCION', default => 'ABSTENCION_INFORMACION_INSUFICIENTE',
        };
        self::assertSame($motivo, $p['estado'], $variante.' '.json_encode($p['requisitos'], JSON_UNESCAPED_UNICODE));
        self::assertContains($motivo, $p['impedimentos']);
        [$grupo, $elemento, $estado] = match ($variante) {
            'c01_negativo' => ['requisitos', 'C01', 'N'], 'c03_negativo' => ['requisitos', 'C03', 'N'],
            'c04_negativo' => ['requisitos', 'C04', 'N'], 'c05_negativo' => ['requisitos', 'C05', 'N'],
            'sin_visuoespacial' => ['dominios', 'COG-VIS', 'U'], 'visuoespacial_no_interpretable' => ['dominios', 'COG-VIS', 'I'],
            'contexto_no_interpretado' => ['dominios', 'COG-MEM', 'U'], 'sin_corroboracion' => ['requisitos', 'C01', 'U'],
            'sin_oportunidad_iadl' => ['requisitos', 'C03', 'U'], 'contradiccion_memoria' => ['dominios', 'COG-MEM', 'X'],
            'revision_calidad_desactualizada' => ['requisitos', 'C05', 'U'],
            'impedimento_diferencial' => ['requisitos', 'C04', 'S'],
            default => throw new LogicException('Variante negativa no cubierta: '.$variante),
        };
        self::assertSame($estado, $p[$grupo][$elemento]['estado'], $variante);
        if ($variante === 'sin_oportunidad_iadl') {
            $excluidas = array_filter($p['valoraciones_excluidas'], fn ($v) => $v['elemento'] === 'C03');
            self::assertContains('IADL_SIN_CONTEXTO_INTERPRETABLE', array_merge(...array_column($excluidas, 'motivos')));
        }
        if ($variante === 'contradiccion_memoria') {
            self::assertSame('X', $p['dominios']['COG-MEM']['estado']);
        }
        if ($variante === 'revision_calidad_desactualizada') {
            self::assertSame('A', $p['dominios']['COG-MEM']['estado']);
            self::assertSame('U', $p['requisitos']['C05']['estado']);
        }
    }

    private function comprobarFuenteAjena(CohorteInferencia30 $carga, array $residentes): void
    {
        $antes = ValoracionPetersenOriginal::count();
        $soportesAntes = DB::table('soportes_valoracion_petersen')->count();
        $auditoriaAntes = DB::table('activity_log')->count();
        $ajena = AplicacionInstrumento::query()->where('cod_residente', $residentes['S02']->getKey())->firstOrFail();
        try {
            app(RegistrarValoracionPetersenOriginal::class)->ejecutar($carga->psicologia, $residentes['S01'], [
                'solicitud_uuid' => (string) Str::uuid(), 'elemento' => 'COG-MEM', 'estado_original' => 'A',
                'justificacion' => 'Intento sintético con fuente de otro residente; debe rechazarse sin efectos.',
                'fecha_hora' => now()->format('Y-m-d H:i:s'), 'tipo_registro' => 'REGISTRO',
                'alcance_dominio' => true, 'contexto_interpretado' => true,
                'soportes' => [['tipo_soporte' => 'MEDICION', 'cod_aplicacion' => $ajena->getKey()]]]);
            self::fail('No debe aceptar una fuente ajena.');
        } catch (ModelNotFoundException) {
            self::assertSame($antes, ValoracionPetersenOriginal::count());
            self::assertSame($soportesAntes, DB::table('soportes_valoracion_petersen')->count());
            self::assertSame($auditoriaAntes, DB::table('activity_log')->count());
        }
    }

    private function exportar(array $resultados): void
    {
        // Exportación explícita, exclusivamente sintética; nunca vuelca conexiones o datos institucionales.
        $destino = getenv('RM_COHORTE30_INFORME');
        if (! is_string($destino) || $destino === '') {
            return;
        }
        $tablas = ['residentes', 'admisiones', 'ocupaciones_cama', 'atenciones', 'diagnosticos', 'antecedentes_clinicos',
            'incidentes', 'signos_vitales', 'notas_clinicas', 'valoraciones_dolor', 'instrumentos', 'preguntas_instrumento',
            'aplicaciones_instrumento', 'respuestas_instrumento', 'controles_cognitivos', 'controles_funcionales_iadl',
            'valoraciones_psicologicas', 'valoraciones_petersen_originales', 'soportes_valoracion_petersen', 'evaluaciones_expertas'];
        $informe = ['version' => CohorteInferencia30::VERSION, 'sintetico' => true, 'motor' => DB::getDriverName(),
            'captura' => 'Actions institucionales, SQL y métodos existentes', 'consulta_sin_escrituras' => true,
            'validacion_clinica' => false, 'tablas' => array_combine($tablas, array_map(fn ($t) => DB::table($t)->count(), $tablas)),
            'manifiesto' => CohorteInferencia30::manifiesto(),
            'retroalimentacion' => ManifiestoCognitivo30::comparar(CohorteInferencia30::manifiesto(), $resultados),
            'casos' => $resultados];
        if (file_put_contents($destino, json_encode($informe, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL) === false) {
            throw new LogicException('No se pudo exportar la evidencia sintética solicitada.');
        }
    }

    public function test_rechaza_entorno_local_antes_de_escribir(): void
    {
        $antes = Residente::count();
        $this->app->detectEnvironment(fn () => 'local');
        try {
            (new CohorteInferencia30)->cargar();
            self::fail('La carga sintética no debe admitir el entorno operativo local.');
        } catch (LogicException $e) {
            self::assertStringContainsString('desechable de testing', $e->getMessage());
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
        }
        self::assertSame($antes, Residente::count());
    }

    public function test_seeder_artisan_rechaza_destino_no_confirmado_sin_escrituras(): void
    {
        $anterior = getenv('RM_COHORTE30_DESTINO');
        $escrituras = [];
        DB::listen(static function ($q) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete)\b/i', $q->sql)) {
                $escrituras[] = $q->sql;
            }
        });
        try {
            putenv('RM_COHORTE30_DESTINO=destino_no_autorizado');
            (new CohorteInferencia30)->run();
            self::fail('El seeder debe exigir confirmación exacta.');
        } catch (LogicException $e) {
            self::assertStringContainsString('RM_COHORTE30_DESTINO', $e->getMessage());
        } finally {
            putenv($anterior === false ? 'RM_COHORTE30_DESTINO' : 'RM_COHORTE30_DESTINO='.$anterior);
        }
        self::assertSame([], $escrituras);
        self::assertDatabaseCount('residentes', 0);
    }

    public function test_fallo_parcial_revierte_admision_fuentes_auditoria_y_correlativos(): void
    {
        $perfiles = config('sistema_experto.perfiles_instrumentales');
        $carga = new CohorteInferencia30;
        $fallar = true;
        DB::listen(static function ($q) use (&$fallar): void {
            if ($fallar && preg_match('/^insert into ["`]?respuestas_instrumento/i', $q->sql)) {
                $fallar = false;
                throw new LogicException('Fallo de carga sintético deliberado.');
            }
        });
        try {
            $carga->cargar();
            self::fail('El fallo de carga debe propagarse.');
        } catch (LogicException $e) {
            self::assertSame('Fallo de carga sintético deliberado.', $e->getMessage());
        }
        foreach (['residentes', 'admisiones', 'ocupaciones_cama', 'aplicaciones_instrumento', 'respuestas_instrumento',
            'valoraciones_petersen_originales', 'soportes_valoracion_petersen', 'activity_log', 'secuencias_codigos'] as $tabla) {
            self::assertSame(0, DB::table($tabla)->count(), $tabla);
        }
        self::assertSame($perfiles, config('sistema_experto.perfiles_instrumentales'));
        self::assertSame(0, DB::transactionLevel());
        app(RolesAndPermissionsSeeder::class)->run();
        $gobierno = User::factory()->create(['estado' => 'ACTIVO']);
        $gobierno->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $gobierno->getKey(), 'nombres' => 'Gobierno de ensayo',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'GOB-COHORTE', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($gobierno);
        app(RegistrarConocimientoPetersen::class)->ejecutar($gobierno);
        $tablasGobierno = ['versiones_modelo_experto', 'reglas_expertas', 'roles', 'permissions', 'role_has_permissions'];
        $huellasGobierno = fn () => array_map(fn ($t) => hash('sha256', json_encode(DB::table($t)->get()->toArray(), JSON_THROW_ON_ERROR)), $tablasGobierno);
        $huellasAntes = $huellasGobierno();
        $simulacion = config('sistema_experto.simulacion_local');
        $destino = getenv('RM_COHORTE30_DESTINO');
        $local = getenv('RM_COHORTE30_CARGA_LOCAL');
        try {
            $this->app->detectEnvironment(fn () => 'local');
            config(['sistema_experto.simulacion_local' => ['habilitada' => true, 'base_permitida' => DB::connection()->getDatabaseName()]]);
            putenv('RM_COHORTE30_DESTINO='.DB::connection()->getDatabaseName());
            putenv('RM_COHORTE30_CARGA_LOCAL=1');
            self::assertCount(30, $carga->cargar());
            self::assertCount(5, $carga->perfiles, 'El reintento no debe conservar selectores de instrumentos revertidos.');
            self::assertDatabaseCount('aplicaciones_instrumento', 149);
            self::assertDatabaseCount('residentes', 30);
            self::assertSame($huellasAntes, $huellasGobierno(), 'La carga local no cambia reglas, versiones ni permisos instalados.');
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
            config(['sistema_experto.simulacion_local' => $simulacion]);
            putenv($destino === false ? 'RM_COHORTE30_DESTINO' : 'RM_COHORTE30_DESTINO='.$destino);
            putenv($local === false ? 'RM_COHORTE30_CARGA_LOCAL' : 'RM_COHORTE30_CARGA_LOCAL='.$local);
            config(['sistema_experto.perfiles_instrumentales' => $perfiles]);
        }
    }
}
