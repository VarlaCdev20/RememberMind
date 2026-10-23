<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\ReglaExperta;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPetersenInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\CapturaPetersenPanel;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use OrionNominal\MembresiasSQL;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Process\Process;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;
use Tests\Unit\PetersenInstitucionalTest;

/** Todas las filas son sintéticas y efímeras; se comprueban persistencia, autoría e historia reales. */
final class CapturaPetersenOriginalTest extends TestCase
{
    use EsquemaSinTransaccion;

    private User $usuario;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = $this->profesional();
        $this->actingAs($this->usuario);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
    }

    private function profesional(string $rol = 'PSICOLOGO/A'): User
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole($rol);
        Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'PET-'.Str::random(10), 'profesion' => $rol === 'MEDICO GENERAL/GERIATRA' ? 'MEDICINA' : 'PSICOLOGIA', 'estado' => 'ACTIVO']);

        return $u;
    }

    private function entrada(array $cambios = []): array
    {
        return array_replace(['solicitud_uuid' => (string) Str::uuid(), 'elemento' => 'C04', 'estado_original' => 'N',
            'justificacion' => 'Revisión diferencial incompleta documentada en caso sintético.', 'fecha_hora' => '2026-10-18 10:00:00',
            'tipo_registro' => 'REGISTRO', 'soportes' => []], $cambios);
    }

    private function registrar(array $e): ValoracionPetersenOriginal
    {
        return app(RegistrarValoracionPetersenOriginal::class)->ejecutar($this->usuario, $this->residente, $e);
    }

    public function test_guarda_autoria_de_la_sesion_y_reintento_idempotente_sin_inventar_suficiencia(): void
    {
        $e = $this->entrada();
        $a = $this->registrar($e);
        self::assertSame($a->getKey(), $this->registrar($e)->getKey());
        self::assertSame($this->usuario->personal->getKey(), $a->cod_personal);
        self::assertSame($this->usuario->getKey(), $a->cod_usuario_registro);
        self::assertDatabaseCount('valoraciones_petersen_originales', 1);
        self::assertSame(1, DB::table('activity_log')->where('event', 'valoracion_original_petersen_registrada')->count());
        $r = app(ConsultarPetersenInstitucional::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertNull($r['patron']);
        self::assertSame('U', $r['requisitos']['C04']['estado']);
        self::assertFalse($r['persistido']);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_misma_solicitud_con_otro_contenido_falla_sin_otra_fila(): void
    {
        $e = $this->entrada();
        $guardada = $this->registrar($e);
        try {
            $this->registrar(array_replace($e, ['justificacion' => 'Otro contenido de la misma solicitud.']));
            self::fail('Debió rechazar el conflicto.');
        } catch (DomainException $ex) {
            self::assertStringContainsString('otro contenido', $ex->getMessage());
        }
        self::assertDatabaseCount('valoraciones_petersen_originales', 1);
    }

    public function test_captura_vinculos_originales_incluso_de_juicios_rectificados_sin_leer_datos_actuales_al_verificar(): void
    {
        $control = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->usuario->personal->getKey(), 'fecha_hora' => '2026-10-17 09:00:00',
            'estado' => 'VIGENTE', 'observacion' => 'Fuente sintética para comprobar la identidad histórica.']);
        $original = $this->registrar($this->entrada(['elemento' => 'C01', 'estado_original' => 'S',
            'soportes' => [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $control->getKey()]]]));
        $this->registrar($this->entrada(['elemento' => 'C01', 'estado_original' => 'N', 'tipo_registro' => 'RECTIFICACION',
            'cod_valoracion_anterior' => $original->getKey(), 'justificacion' => 'Rectificación sintética posterior.']));
        $dto = app(ConsultarPetersenInstitucional::class)->preparar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame(InstantaneaPetersen::CONTRATO_MEMBRESIAS, $dto->contenido['contrato']);
        $soporte = $original->soportes()->sole();
        self::assertCount(1, $dto->contenido['soportes_originales']);
        self::assertSame($soporte->getKey(), $dto->contenido['soportes_originales'][0]['cod_soporte_petersen']);
        self::assertSame($original->getKey(), $dto->contenido['soportes_originales'][0]['cod_valoracion_petersen']);
        self::assertSame($control->getKey(), $dto->contenido['soportes_originales'][0]['cod_control_cognitivo']);
        self::assertNull($dto->contenido['soportes_originales'][0]['cod_aplicacion']);
        self::assertSame(0, $dto->contenido['soportes_originales'][0]['orden']);
        self::assertSame('RECTIFICADA_AL_CORTE', $dto->contenido['adquisicion']['exclusiones'][0]['motivo']);
        self::assertNotContains($original->getKey(), array_column(array_column($dto->contenido['valoraciones'], 'origen'), 'registro'));
        $control->update(['observacion' => 'Cambio posterior que no debe completar la captura histórica.']);
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            self::assertSame($dto->contenido, InstantaneaPetersen::verificar($dto->contenido, $dto->huella)->contenido);
            self::assertSame([], DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    /** Dos relaciones de ensayo; ningún cambio se registra como migración instalable. */
    private function prepararMembresiasSQL(bool $dosSoportes = false): array
    {
        require_once base_path('prototipos/orion-conservacion-0_2/GuardiaDesechable.php');
        require_once base_path('prototipos/orion-conservacion-nominal/RepositorioNominal.php');
        require_once base_path('prototipos/orion-conservacion-nominal/MembresiasSQL.php');
        $control = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->usuario->personal->getKey(), 'fecha_hora' => '2026-10-17 09:00:00',
            'estado' => 'VIGENTE', 'observacion' => 'Fuente efímera para ensayo de membresías.']);
        $soportes = [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $control->getKey()]];
        if ($dosSoportes) {
            $otro = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(),
                'cod_personal' => $this->usuario->personal->getKey(), 'fecha_hora' => '2026-10-17 10:00:00', 'estado' => 'VIGENTE']);
            $soportes[] = ['tipo_soporte' => 'CORROBORACION', 'cod_control_cognitivo' => $otro->getKey()];
        }
        $original = $this->registrar($this->entrada(['elemento' => 'C01', 'estado_original' => 'S', 'soportes' => $soportes]));
        $vacia = $this->registrar($this->entrada());
        $version = app(RegistrarConocimientoInstitucional::class)->ejecutar($this->usuario);
        $dto = app(ConsultarPetersenInstitucional::class)->preparar($this->usuario, $this->residente, CarbonImmutable::now(), $version);
        DB::table('evaluaciones_expertas')->insert(['cod_evaluacion_experta' => 'MEMBRESIAS-TEST',
            'cod_residente' => $this->residente->getKey(), 'cod_version_modelo' => $version,
            'origen_activacion' => 'PRUEBA_TECNICA', 'estado_ejecucion' => 'PRUEBA_FINALIZADA', 'fecha_hora_inicio' => '2026-10-19 12:00:00']);
        $repo = new MembresiasSQL(DB::getPdo(), new \DateTimeZone(config('app.timezone')));
        $repo->instalar();

        return [$repo, $dto, $original, $vacia];
    }

    public function test_sql_membresias_recupera_identidades_orden_y_vacios_sin_agregar_miembros_posteriores(): void
    {
        [$repo, $dto, $original] = $this->prepararMembresiasSQL();
        self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $dto));
        self::assertFalse($repo->guardar('MEMBRESIAS-TEST', $dto));
        self::assertSame(MembresiasSQL::proyectar($dto), $repo->recuperar('MEMBRESIAS-TEST'));
        self::assertDatabaseCount('juicios_petersen_adquiridos', 2);
        self::assertDatabaseCount('soportes_petersen_adquiridos', 1);
        $otro = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->usuario->personal->getKey(), 'fecha_hora' => '2026-10-17 10:00:00', 'estado' => 'VIGENTE']);
        $original->soportes()->create(['tipo_soporte' => 'CORROBORACION', 'orden' => 1, 'cod_control_cognitivo' => $otro->getKey()]);
        self::assertSame(2, $original->soportes()->count());
        self::assertCount(1, $repo->recuperar('MEMBRESIAS-TEST')['soportes_originales']);
        self::assertSame(MembresiasSQL::proyectar($dto), $repo->recuperar('MEMBRESIAS-TEST'));
        self::assertFalse($repo->guardar('MEMBRESIAS-TEST', $dto));
        // El bloque no sustituye el resultado, no acredita DTO completo ni crea revisión profesional.
        self::assertSame('PRUEBA_FINALIZADA', DB::table('evaluaciones_expertas')->value('estado_ejecucion'));
        self::assertDatabaseCount('revisiones_evaluacion_experta', 0);
        $this->expectException(PDOException::class);
        DB::getPdo()->exec('UPDATE soportes_petersen_adquiridos SET orden_membresia=orden_membresia');
    }

    public function test_sql_membresias_revierte_fallo_parcial_sin_tocar_los_originales(): void
    {
        [$repo, $dto] = $this->prepararMembresiasSQL();
        try {
            $repo->guardar('MEMBRESIAS-TEST', $dto, static fn () => throw new \RuntimeException('Fallo de adquisición sintético.'));
            self::fail('La escritura incompleta debe fallar.');
        } catch (\RuntimeException $e) {
            self::assertSame('Fallo de adquisición sintético.', $e->getMessage());
        }
        self::assertDatabaseCount('juicios_petersen_adquiridos', 0);
        self::assertDatabaseCount('soportes_petersen_adquiridos', 0);
        self::assertDatabaseCount('valoraciones_petersen_originales', 2);
        self::assertDatabaseCount('soportes_valoracion_petersen', 1);
        self::assertNull(DB::table('evaluaciones_expertas')->value('membresias_petersen_estado'));
        self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $dto));
    }

    public function test_sql_membresias_fk_impide_atribuir_un_soporte_a_otro_juicio(): void
    {
        [$repo, $dto, $original, $vacia] = $this->prepararMembresiasSQL();
        try {
            $repo->guardar('MEMBRESIAS-TEST', $dto, static function (PDO $db) use ($vacia): void {
                $q = $db->prepare('UPDATE soportes_petersen_adquiridos SET cod_juicio_adquirido=(SELECT cod_juicio_adquirido FROM juicios_petersen_adquiridos WHERE cod_valoracion_petersen=?)');
                $q->execute([$vacia->getKey()]);
            });
            self::fail('La FK terminal debe impedir cambiar el juicio propietario.');
        } catch (PDOException $e) {
            self::assertContains($e->getCode(), ['23000', '23503']);
        }
        self::assertDatabaseCount('juicios_petersen_adquiridos', 0);
        self::assertDatabaseCount('soportes_petersen_adquiridos', 0);
        self::assertSame(1, $original->soportes()->count());
        self::assertNull(DB::table('evaluaciones_expertas')->value('membresias_petersen_estado'));
    }

    public function test_sql_membresias_cierre_rechaza_orden_discontinuo_y_reversion_del_ensayo_preserva_base_poblada(): void
    {
        [$repo, $dto] = $this->prepararMembresiasSQL();
        try {
            $repo->guardar('MEMBRESIAS-TEST', $dto, static fn (PDO $db) => $db->exec('UPDATE soportes_petersen_adquiridos SET orden_membresia=9'));
            self::fail('El cierre SQL debe rechazar el orden discontinuo.');
        } catch (PDOException $e) {
            self::assertContains($e->getCode(), ['23000', '23514']);
        }
        self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $dto));
        $repo->retirarEnsayo();
        self::assertFalse(DB::getSchemaBuilder()->hasTable('juicios_petersen_adquiridos'));
        self::assertFalse(DB::getSchemaBuilder()->hasTable('soportes_petersen_adquiridos'));
        self::assertFalse(DB::getSchemaBuilder()->hasColumn('evaluaciones_expertas', 'membresias_petersen_estado'));
        self::assertDatabaseCount('valoraciones_petersen_originales', 2);
        self::assertDatabaseCount('soportes_valoracion_petersen', 1);
        self::assertDatabaseCount('evaluaciones_expertas', 1);
    }

    public function test_sql_membresias_no_confunde_orden_global_con_orden_de_la_membresia(): void
    {
        [$repo, $dto] = $this->prepararMembresiasSQL(dosSoportes: true);
        try {
            $repo->guardar('MEMBRESIAS-TEST', $dto, static function (PDO $db): void {
                // Evita una violación UNIQUE intermedia; ambos órdenes finales son continuos pero opuestos.
                $db->exec('UPDATE soportes_petersen_adquiridos SET orden_membresia=orden_membresia+10');
                $db->exec('UPDATE soportes_petersen_adquiridos SET orden_membresia=11-orden_membresia');
            });
            self::fail('Dos ordenaciones distintas no pueden conservar la misma membresía.');
        } catch (DomainException $e) {
            self::assertStringContainsString('no coincide', $e->getMessage());
        }
        self::assertDatabaseCount('juicios_petersen_adquiridos', 0);
        self::assertDatabaseCount('soportes_petersen_adquiridos', 0);
        self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $dto));
        self::assertSame(MembresiasSQL::proyectar($dto), $repo->recuperar('MEMBRESIAS-TEST'));
    }

    public function test_sql_membresias_compara_los_instantes_con_el_juicio_inmutable(): void
    {
        [$repo, $dto] = $this->prepararMembresiasSQL();
        $d = $dto->contenido;
        $d['procedencia_valoraciones'][0]['registrado_en'] = '2026-10-19T11:59:59.999999-04:00';
        // La inferencia y las FK nominales no bastan para detectar esta fecha histórica falsa.
        $alterada = InstantaneaPetersen::verificar($d, \MotorExperto\soporte\conservacion\CodecInstantanea::huella(
            \MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica::ordenar($d)));
        try {
            $repo->guardar('MEMBRESIAS-TEST', $alterada);
            self::fail('La fecha debe coincidir con el registro SQL original.');
        } catch (DomainException $e) {
            self::assertStringContainsString('instante adquirido', $e->getMessage());
        }
        self::assertDatabaseCount('juicios_petersen_adquiridos', 0);
        self::assertDatabaseCount('soportes_petersen_adquiridos', 0);
        self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $dto));
    }

    public function test_sql_membresias_no_permite_cambiar_el_original_del_padre_con_soportes_adquiridos(): void
    {
        [$repo, $dto, $original, $vacia] = $this->prepararMembresiasSQL();
        try {
            $repo->guardar('MEMBRESIAS-TEST', $dto, static function (PDO $db) use ($original, $vacia): void {
                $q = $db->prepare('DELETE FROM juicios_petersen_adquiridos WHERE cod_valoracion_petersen=?');
                $q->execute([$vacia->getKey()]);
                $q = $db->prepare('UPDATE juicios_petersen_adquiridos SET cod_valoracion_petersen=? WHERE cod_valoracion_petersen=?');
                $q->execute([$vacia->getKey(), $original->getKey()]);
            });
            self::fail('El padre no puede trasladar sus soportes a otra valoración original.');
        } catch (PDOException $e) {
            self::assertContains($e->getCode(), ['23000', '23514']);
        }
        self::assertDatabaseCount('juicios_petersen_adquiridos', 0);
        self::assertDatabaseCount('soportes_petersen_adquiridos', 0);
        self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $dto));
    }

    public function test_sql_membresias_no_admite_un_juicio_original_de_otro_residente(): void
    {
        [$repo, $dto, $original] = $this->prepararMembresiasSQL();
        $ajeno = $original->getAttributes();
        $ajeno['cod_valoracion_petersen'] = 'JUICIO-AJENO-TEST';
        $ajeno['solicitud_uuid'] = (string) Str::uuid();
        $ajeno['cod_residente'] = Residente::factory()->create(['estado' => 'ADMITIDO'])->getKey();
        DB::table('valoraciones_petersen_originales')->insert($ajeno);
        try {
            $repo->guardar('MEMBRESIAS-TEST', $dto, static function (PDO $db): void {
                $q = $db->prepare('INSERT INTO juicios_petersen_adquiridos(cod_juicio_adquirido,cod_evaluacion_experta,cod_valoracion_petersen,
                    fecha_hora_iso,registrado_en_iso,orden_procedencia,orden_membresia,cantidad_soportes) VALUES (?,?,?,?,?,?,?,?)');
                $q->execute(['MIEMBRO-AJENO', 'MEMBRESIAS-TEST', 'JUICIO-AJENO-TEST', '2026-10-18T10:00:00-04:00',
                    '2026-10-19T12:00:00-04:00', 2, 2, 0]);
            });
            self::fail('Una FK existente no autoriza un juicio de otro residente.');
        } catch (PDOException $e) {
            self::assertContains($e->getCode(), ['23000', '23514']);
        }
        self::assertDatabaseCount('juicios_petersen_adquiridos', 0);
        self::assertDatabaseCount('soportes_petersen_adquiridos', 0);
        self::assertDatabaseCount('valoraciones_petersen_originales', 3);
    }

    public function test_postgres_membresias_no_confirma_una_adquisicion_abierta_por_sql_directo(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La restricción de cierre diferida requiere PostgreSQL.');
        }
        $this->prepararMembresiasSQL();
        $db = DB::getPdo();
        $db->beginTransaction();
        try {
            $q = $db->prepare("UPDATE evaluaciones_expertas SET membresias_petersen_estado='ABIERTA',membresias_petersen_huella=?,
                membresias_petersen_juicios=0,membresias_petersen_soportes=0 WHERE cod_evaluacion_experta='MEMBRESIAS-TEST'");
            $q->execute([str_repeat('a', 64)]);
            $db->commit();
            self::fail('No debe confirmarse una adquisición parcial.');
        } catch (PDOException $e) {
            self::assertSame('23514', $e->getCode());
        } finally {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }
        self::assertNull(DB::table('evaluaciones_expertas')->value('membresias_petersen_estado'));
        self::assertDatabaseCount('juicios_petersen_adquiridos', 0);
    }

    public function test_sqlite_membresias_vacias_no_se_borran_mediante_replace_sin_triggers_recursivos(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('INSERT OR REPLACE es una operación específica de SQLite.');
        }
        [$repo, $dto] = $this->prepararMembresiasSQL();
        $d = $dto->contenido;
        $d['valoraciones'] = $d['procedencia_valoraciones'] = $d['membresias_soportes'] = $d['soportes_originales'] = [];
        $d['adquisicion']['valoraciones_al_corte'] = 0;
        // Crear una captura vacía legítima ejecuta el motor con cero juicios; no altera una salida ya emitida.
        $vacia = InstantaneaPetersen::crear(new \MotorExperto\dominio\objetos_valor\ContextoEjecucion(
            $d['contexto']['evaluacion'], $d['contexto']['residente'], $d['contexto']['version'],
            \MotorExperto\soporte\conservacion\CodecInstantanea::leerFecha('corte', (object) $d['contexto']['fecha_corte'])),
            [], null, null, $this->usuario->getKey(), $d['adquisicion'], soportesOriginales: []);
        self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $vacia));
        $fila = (array) DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', 'MEMBRESIAS-TEST')->sole();
        foreach (['estado', 'huella', 'juicios', 'soportes'] as $campo) {
            unset($fila['membresias_petersen_'.$campo]);
        }
        DB::getPdo()->exec('PRAGMA recursive_triggers=OFF');
        try {
            $q = DB::getPdo()->prepare('INSERT OR REPLACE INTO evaluaciones_expertas('.implode(',', array_keys($fila)).') VALUES ('.implode(',', array_fill(0, count($fila), '?')).')');
            $q->execute(array_values($fila));
            self::fail('REPLACE no debe borrar una adquisición cerrada vacía.');
        } catch (PDOException $e) {
            self::assertSame('23000', $e->getCode());
        }
        self::assertSame(MembresiasSQL::proyectar($vacia), $repo->recuperar('MEMBRESIAS-TEST'));
        self::assertSame('CERRADA', DB::table('evaluaciones_expertas')->value('membresias_petersen_estado'));
    }

    public function test_postgres_membresias_cierre_reintento_e_insercion_tardia_compiten_en_tres_conexiones(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La carrera exige PostgreSQL con tres conexiones independientes.');
        }
        [$repo, $dto, $original] = $this->prepararMembresiasSQL();
        // Existe en el original, pero no pertenecía a la captura adquirida: la carrera no usa un duplicado UNIQUE.
        $extra = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->usuario->personal->getKey(), 'fecha_hora' => '2026-10-17 10:00:00', 'estado' => 'VIGENTE']);
        $tardio = $original->soportes()->create(['tipo_soporte' => 'CORROBORACION', 'orden' => 1, 'cod_control_cognitivo' => $extra->getKey()]);
        $servidor = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        self::assertNotFalse($servidor);
        $procesos = $sockets = [];
        try {
            self::assertTrue($repo->guardar('MEMBRESIAS-TEST', $dto, function (PDO $db) use ($dto, $original, $tardio, $servidor, &$procesos, &$sockets): void {
                $pids = [];
                foreach (['INSERTAR', 'REINTENTAR'] as $modo) {
                    $p = new Process([PHP_BINARY, base_path('prototipos/orion-conservacion-nominal/worker-membresias.php'),
                        stream_socket_get_name($servidor, false), $modo], base_path(), timeout: 30);
                    $p->start();
                    $procesos[] = $p;
                    $s = stream_socket_accept($servidor, 10);
                    self::assertNotFalse($s, $p->getErrorOutput());
                    stream_set_timeout($s, 25);
                    $sockets[] = $s;
                    $pids[] = json_decode((string) fgets($s), true, flags: JSON_THROW_ON_ERROR)['pid'];
                }
                self::assertNotSame($pids[0], $pids[1]);
                $q = $db->prepare('SELECT cod_juicio_adquirido FROM juicios_petersen_adquiridos WHERE cod_valoracion_petersen=?');
                $q->execute([$original->getKey()]);
                $padre = $q->fetchColumn();
                foreach ($sockets as $i => $s) {
                    $payload = ['evaluacion' => 'MEMBRESIAS-TEST', 'contenido' => $dto->contenido, 'huella' => $dto->huella,
                        'zona_original' => config('app.timezone'),
                        'fila' => ['cod_soporte_adquirido' => 'INSERCION-TARDIA', 'cod_juicio_adquirido' => $padre,
                            'cod_soporte_petersen' => $tardio->getKey(), 'orden_membresia' => 99]];
                    fwrite($s, json_encode($payload, JSON_THROW_ON_ERROR)."\n");
                }
                $q = $db->prepare("SELECT count(*) FROM pg_stat_activity WHERE pid IN (?,?) AND wait_event_type='Lock' AND cardinality(pg_blocking_pids(pid))>0");
                $limite = hrtime(true) + 8_000_000_000;
                do {
                    $db->query('SELECT pg_stat_clear_snapshot()');
                    $q->execute($pids);
                    $esperando = (int) $q->fetchColumn();
                } while ($esperando !== 2 && hrtime(true) < $limite);
                self::assertSame(2, $esperando, 'Ambas conexiones deben esperar al cierre real no confirmado.');
            }));
            $resultados = [];
            foreach ($sockets as $i => $s) {
                $resultados[] = json_decode((string) fgets($s), true, flags: JSON_THROW_ON_ERROR);
                self::assertSame(0, $procesos[$i]->wait(), $procesos[$i]->getErrorOutput());
            }
            self::assertSame(['sqlstate' => '55000'], $resultados[0]);
            self::assertSame(['nuevo' => false], $resultados[1]);
            self::assertSame(MembresiasSQL::proyectar($dto), $repo->recuperar('MEMBRESIAS-TEST'));
            self::assertDatabaseCount('soportes_petersen_adquiridos', 1);
        } finally {
            foreach ($sockets as $s) {
                fclose($s);
            }
            fclose($servidor);
            foreach ($procesos as $p) {
                if ($p->isRunning()) {
                    $p->stop();
                }
            }
        }
    }

    public function test_precision_de_microsegundos_conserva_reintento_y_corte_tecnico(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:00:00.500000-04:00'));
        $e = $this->entrada(['fecha_hora' => '2026-10-18T10:00:00.123456-04:00']);
        $a = $this->registrar($e);
        self::assertSame('123456', $a->fecha_hora->format('u'));
        self::assertSame('500000', $a->registrado_en->format('u'));
        self::assertSame($a->getKey(), $this->registrar($e)->getKey());
        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:00:01-04:00'));
        $consulta = app(ConsultarPetersenInstitucional::class);
        self::assertSame(0, $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::parse('2026-10-19T12:00:00.250000-04:00'), 'VERSION-TEST')['adquisicion']['valoraciones_al_corte']);
        self::assertSame(1, $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::parse('2026-10-19T12:00:00.750000-04:00'), 'VERSION-TEST')['adquisicion']['valoraciones_al_corte']);
    }

    public function test_rectificacion_y_anulacion_conservan_original_y_corte_tecnico(): void
    {
        $a = $this->registrar($this->entrada());
        $corte = CarbonImmutable::now();
        $this->travel(1)->minutes();
        $b = $this->registrar($this->entrada(['tipo_registro' => 'RECTIFICACION', 'cod_valoracion_anterior' => $a->getKey(),
            'justificacion' => 'Rectificación propia del juicio, preservando fecha clínica.']));
        $servicio = app(ConsultarPetersenInstitucional::class);
        self::assertSame(1, $servicio->consultar($this->usuario, $this->residente, $corte, 'VERSION-TEST')['adquisicion']['valoraciones_al_corte']);
        $this->travel(1)->minutes();
        $this->registrar($this->entrada(['tipo_registro' => 'ANULACION', 'estado_original' => null, 'cod_valoracion_anterior' => $b->getKey(),
            'justificacion' => 'Anulación propia explícita.']));
        self::assertDatabaseCount('valoraciones_petersen_originales', 3);
        self::assertSame($a->justificacion, $a->fresh()->justificacion);
        $r = $servicio->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertCount(3, $r['adquisicion']['exclusiones']);
        self::assertNull($r['patron']);
    }

    public function test_otro_profesional_no_rectifica_una_cadena_ajena(): void
    {
        $a = $this->registrar($this->entrada());
        $otro = $this->profesional('MEDICO GENERAL/GERIATRA');
        try {
            app(RegistrarValoracionPetersenOriginal::class)->ejecutar($otro, $this->residente,
                $this->entrada(['tipo_registro' => 'ANULACION', 'estado_original' => null, 'cod_valoracion_anterior' => $a->getKey()]));
            self::fail('No debe anular una cadena ajena.');
        } catch (AuthorizationException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 1);
        }
    }

    public static function actoresDenegados(): array
    {
        return [['SUPERADMINISTRADOR'], ['ENFERMEROS'], ['GERENTE'], ['ADMINISTRADOR'], ['FAMILIAR']];
    }

    #[DataProvider('actoresDenegados')]
    public function test_roles_sin_competencia_no_guardan_aunque_reciban_el_permiso(string $rol): void
    {
        $u = $this->profesional($rol);
        $u->givePermissionTo(['valoraciones_petersen.ver', 'valoraciones_petersen.crear']);
        try {
            app(RegistrarValoracionPetersenOriginal::class)->ejecutar($u, $this->residente, $this->entrada());
            self::fail('No debe guardar.');
        } catch (AuthorizationException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        }
    }

    public function test_cuenta_revocada_y_autor_manipulado_no_producen_efectos(): void
    {
        try {
            $this->registrar($this->entrada(['cod_personal' => 'AUTOR-AJENO']));
            self::fail('Debe rechazar autor manipulado.');
        } catch (DomainException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        }
        DB::table('usuarios')->where('cod_usuario', $this->usuario->getKey())->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        $this->registrar($this->entrada());
    }

    public function test_soporte_de_otro_residente_falla_sin_dejar_cabecera_parcial(): void
    {
        $control = ControlCognitivo::create(['cod_residente' => Residente::factory()->create()->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'fecha_hora' => '2026-10-17 09:00:00', 'estado' => 'VIGENTE', 'observacion' => 'Fuente sintética ajena.']);
        try {
            $this->registrar($this->entrada(['elemento' => 'C01', 'estado_original' => 'S',
                'soportes' => [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $control->getKey()]]]));
            self::fail('No debe enlazar otro residente.');
        } catch (ModelNotFoundException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
            self::assertDatabaseCount('soportes_valoracion_petersen', 0);
        }
    }

    public function test_sql_directo_no_sobrescribe_ni_borra_historia(): void
    {
        $a = $this->registrar($this->entrada());
        foreach (['update', 'delete'] as $operacion) {
            try {
                DB::transaction(function () use ($operacion, $a): void {
                    $q = DB::table('valoraciones_petersen_originales')->where('cod_valoracion_petersen', $a->getKey());
                    $operacion === 'update' ? $q->update(['justificacion' => 'Sobrescritura prohibida.']) : $q->delete();
                });
                self::fail('Debe conservar la historia también por SQL.');
            } catch (QueryException) {
                self::assertSame($a->justificacion, $a->fresh()->justificacion);
            }
        }
    }

    public function test_anular_no_cambia_fecha_y_rectificar_fecha_exige_razon_explicita(): void
    {
        $a = $this->registrar($this->entrada());
        foreach (['ANULACION', 'RECTIFICACION'] as $tipo) {
            try {
                $this->registrar($this->entrada(['tipo_registro' => $tipo, 'cod_valoracion_anterior' => $a->getKey(),
                    'estado_original' => $tipo === 'ANULACION' ? null : 'N', 'fecha_hora' => '2026-10-17 10:00:00']));
                self::fail('No debe cambiar la fecha sin su contrato.');
            } catch (DomainException $ex) {
                self::assertStringContainsString('fecha del acto', $ex->getMessage());
                self::assertDatabaseCount('valoraciones_petersen_originales', 1);
            }
        }
        $b = $this->registrar($this->entrada(['tipo_registro' => 'RECTIFICACION', 'cod_valoracion_anterior' => $a->getKey(),
            'fecha_hora' => '2026-10-17 10:00:00', 'error_fecha_original' => 'El original indica día 17; se digitó día 18.']));
        self::assertStringContainsString('Corrección de fecha:', $b->justificacion);
        self::assertSame('2026-10-18', $a->fresh()->fecha_hora->format('Y-m-d'));
    }

    public function test_documento_en_soporte_verifica_bytes_al_guardar_y_al_consultar(): void
    {
        $this->usuario->givePermissionTo(Permission::findOrCreate('documentos_clinicos.ver', 'web'));
        Storage::fake('local');
        $doc = DocumentoClinico::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'fecha_hora' => '2026-10-17 09:00:00', 'tipo_documento' => 'INFORME', 'titulo' => 'Revisión original sintética',
            'descripcion' => 'Revisión diferencial documentada.', 'ruta_archivo' => 'original-petersen.txt',
            'formato' => 'text/plain', 'hash_archivo' => hash('sha256', 'original'), 'estado' => 'VIGENTE']);
        $e = $this->entrada(['estado_original' => 'S', 'revision_explicita' => true, 'factores_pertinentes_revisados' => true,
            'impedimento_no_resuelto' => false, 'soportes' => [['tipo_soporte' => 'DIFERENCIAL', 'cod_documento_clinico' => $doc->getKey()]]]);
        try {
            $this->registrar($e);
            self::fail('Falta el archivo original.');
        } catch (DomainException $ex) {
            self::assertStringContainsString('no es verificable', $ex->getMessage());
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        }
        Storage::disk('local')->put('original-petersen.txt', 'original');
        $guardada = $this->registrar($e);
        $consulta = app(ConsultarPetersenInstitucional::class);
        self::assertSame('S', $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST')['requisitos']['C04']['estado']);
        Storage::disk('local')->put('original-petersen.txt', 'alterado');
        self::assertSame($guardada->getKey(), $this->registrar($e)->getKey());
        $r = $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame('U', $r['requisitos']['C04']['estado']);
        self::assertSame('DOCUMENTO_ORIGINAL_NO_VERIFICABLE', $r['adquisicion']['exclusiones'][0]['motivo']);
        $dto = $consulta->preparar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertFalse($dto->contenido['constancias_documentales'][0]['verificado']);
        self::assertSame('SOPORTE', $dto->contenido['constancias_documentales'][0]['papel']);
        self::assertNull($dto->contenido['constancias_documentales'][0]['localizador_fuente']);
        self::assertStringNotContainsString('ruta_archivo', serialize($dto->contenido));
    }

    public function test_documento_de_cabecera_conserva_constancia_y_no_admite_alteracion_o_fecha_futura(): void
    {
        $this->usuario->givePermissionTo(Permission::findOrCreate('documentos_clinicos.ver', 'web'));
        Storage::fake('local');
        Storage::disk('local')->put('cabecera-petersen.txt', 'original');
        $doc = DocumentoClinico::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'fecha_hora' => '2026-10-17 09:00:00', 'tipo_documento' => 'INFORME', 'titulo' => 'Original de ingeniería',
            'ruta_archivo' => 'cabecera-petersen.txt', 'formato' => 'text/plain', 'hash_archivo' => hash('sha256', 'original'), 'estado' => 'VIGENTE']);
        $original = $this->registrar($this->entrada(['cod_documento_clinico' => $doc->getKey(), 'localizador_fuente' => 'Página 1']));
        $lector = app(ConsultarPetersenInstitucional::class);
        $dto = $lector->preparar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertTrue($dto->contenido['constancias_documentales'][0]['verificado']);
        self::assertSame('Página 1', $dto->contenido['constancias_documentales'][0]['localizador_fuente']);
        self::assertSame($original->cod_usuario_registro, $dto->contenido['procedencia_valoraciones'][0]['cod_usuario_registro']);
        $doc->update(['fecha_hora' => '2027-01-01 09:00:00']);
        $fuera = $lector->preparar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame('DOCUMENTO_ORIGINAL_FUERA_DE_CORTE', $fuera->contenido['salida']['adquisicion']['exclusiones'][0]['motivo']);
        self::assertTrue($fuera->contenido['constancias_documentales'][0]['verificado']);
        self::assertSame([], $fuera->contenido['valoraciones']);
        Storage::disk('local')->put('cabecera-petersen.txt', 'alterado');
        $alterado = $lector->preparar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertFalse($alterado->contenido['constancias_documentales'][0]['verificado']);
        self::assertSame($dto->contenido, InstantaneaPetersen::verificar($dto->contenido, $dto->huella)->contenido);
    }

    /** Recorre SQL → captura → juicios → cuatro patrones; sin sembrar una clasificación esperada. */
    #[DataProviderExternal(PetersenInstitucionalTest::class, 'patrones')]
    public function test_caso_completo_se_clasifica_desde_mediciones_y_soportes_originales(array $alterados, string $patron): void
    {
        $versionGobierno = app(RegistrarConocimientoPetersen::class)->ejecutar($this->usuario);
        $area = Area::create(['nombre' => 'Área de ensayo sintético', 'estado' => 'ACTIVA']);
        $atenciones = [];
        foreach (range(0, 9) as $i) {
            $atenciones[] = Atencion::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_area' => $area->getKey(), 'tipo_atencion' => 'VALORACION', 'fecha_hora' => $i === 5 ? '2026-10-10 09:00:00' : '2026-10-15 09:00:00', 'estado' => 'FINALIZADA']);
        }
        $metodos = ['COG-MEM' => 'HVLT-R-BO-2015-F5', 'COG-ATE' => 'BTA-BO-2015-NL', 'COG-EJE' => 'TMT-B-BO-2015',
            'COG-LEN' => 'BNT-BO-2015-60', 'COG-VIS' => 'ROCF-BO-2015-COPIA-A'];
        $valores = ['ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4, 'demora_minutos' => 22,
            'escolaridad_anios' => 4, 'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7, 'segundos_parte_b' => 180.5,
            'errores_observados' => 0, 'aciertos_espontaneos' => 40, 'aciertos_clave_semantica' => 2, 'puntaje_copia' => 22.5, 'elementos_revisados' => 18];
        DB::table('residentes')->where('cod_residente', $this->residente->getKey())->update(['fecha_nacimiento' => '1950-01-01']);
        $perfiles = $mediciones = [];
        foreach ($metodos as $dominio => $metodo) {
            $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
            $i = count($perfiles);
            $inst = Instrumento::create(['codigo' => 'PET-TEST-'.$i, 'nombre' => 'Instrumento sintético '.$i, 'tipo' => 'COGNITIVO', 'version' => 'TEST-1', 'estado' => 'ACTIVO']);
            $app = AplicacionInstrumento::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_atencion' => $atenciones[$i]->getKey(), 'cod_instrumento' => $inst->getKey(), 'fecha_hora' => '2026-10-15 09:00:00', 'estado' => 'COMPLETA']);
            $selectores = [];
            foreach ($meta['entradas'] as $campo => $tipo) {
                $q = PreguntaInstrumento::create(['cod_instrumento' => $inst->getKey(), 'codigo' => 'ENTRADA-'.count($selectores),
                    'enunciado' => 'Medida sintética '.$campo, 'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO', 'orden' => count($selectores), 'estado' => 'ACTIVA']);
                $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
                $selectores[$campo] = ['cod_pregunta' => $q->getKey(), 'campo' => $columna];
                RespuestaInstrumento::create(['cod_aplicacion' => $app->getKey(), 'cod_pregunta' => $q->getKey(),
                    $columna => $tipo === 'LOGICO' ? true : $valores[$campo]]);
            }
            $perfiles[] = ['id' => 'PET-PERFIL-'.$i, 'cod_instrumento' => $inst->getKey(), 'codigo_instrumento' => $inst->codigo,
                'version_instrumento' => 'TEST-1', 'metodo' => $metodo, 'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];
            $mediciones[$dominio] = $app->getKey();
        }
        config(['sistema_experto.perfiles_instrumentales' => $perfiles]);
        $revisados = [];
        foreach ($mediciones as $dominio => $aplicacion) {
            $juicio = $this->registrar($this->entrada(['elemento' => $dominio, 'estado_original' => in_array($dominio, $alterados, true) ? 'A' : 'P',
                'alcance_dominio' => true, 'contexto_interpretado' => true,
                'soportes' => [['tipo_soporte' => 'MEDICION', 'cod_aplicacion' => $aplicacion]]]));
            $revisados[] = ['tipo_soporte' => 'DOMINIO_REVISADO', 'cod_valoracion_revisada' => $juicio->getKey()];
        }
        $cambios = [];
        foreach ([['2026-10-10 09:00:00', 5], ['2026-10-15 09:00:00', 6]] as [$fecha, $i]) {
            $cambios[] = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_atencion' => $atenciones[$i]->getKey(), 'fecha_hora' => $fecha, 'observacion' => 'Funcionamiento y cambio corroborados, caso sintético.', 'estado' => 'VIGENTE']);
        }
        $this->registrar($this->entrada(['elemento' => 'C01', 'estado_original' => 'S', 'cambio_respecto_previo' => true, 'corroboracion_independiente' => true,
            'soportes' => [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $cambios[0]->getKey()],
                ['tipo_soporte' => 'CORROBORACION', 'cod_control_cognitivo' => $cambios[1]->getKey()]]]));
        $iadl = ControlFuncionalIadl::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'cod_usuario_registro' => $this->usuario->getKey(), 'cod_atencion' => $atenciones[7]->getKey(), 'fecha_hora' => '2026-10-15 09:00:00', 'registrado_en' => '2026-10-15 10:00:00',
            'actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA', 'desempeno' => 'INDEPENDIENTE', 'tipo_ayuda' => 'NINGUNA', 'causa_ayuda' => 'NINGUNA',
            'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO']);
        $this->registrar($this->entrada(['elemento' => 'C03', 'estado_original' => 'S', 'revision_integrada' => true, 'oportunidad_adecuada' => true, 'atribucion_revisada' => true,
            'soportes' => [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $iadl->getKey()]]]));
        foreach (['C04', 'C05'] as $elemento) {
            $psico = ValoracionPsicologica::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_atencion' => $elemento === 'C04' ? $atenciones[8]->getKey() : $atenciones[9]->getKey(),
                'fecha_hora' => '2026-10-15 09:00:00', 'conclusion' => 'Revisión original explícita del conjunto, caso sintético.', 'estado' => 'VIGENTE']);
            $condiciones = $elemento === 'C04' ? ['revision_explicita' => true, 'factores_pertinentes_revisados' => true, 'impedimento_no_resuelto' => false]
                : ['suficiencia_conjunto' => true, 'condiciones_revisadas' => true, 'exclusiones_revisadas' => true];
            $this->registrar($this->entrada(['elemento' => $elemento, 'estado_original' => 'S', ...$condiciones,
                'soportes' => [['tipo_soporte' => $elemento === 'C04' ? 'DIFERENCIAL' : 'CALIDAD', 'cod_valoracion_psicologica' => $psico->getKey()],
                    ...($elemento === 'C05' ? $revisados : [])]]));
        }
        $r = app(ConsultarPetersenInstitucional::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame($patron, $r['patron'], json_encode(['dominios' => array_column($r['dominios'], 'estado'),
            'requisitos' => array_column($r['requisitos'], 'estado'), 'adquisicion' => $r['adquisicion']], JSON_UNESCAPED_UNICODE));
        self::assertSame([], $r['impedimentos']);
        self::assertSame($versionGobierno, $r['gobierno']['version']);
        self::assertCount(4, $r['gobierno']['reglas']);
        self::assertCount(8, $r['gobierno']['reglas'][0]['condiciones']);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->usuario);
        $dto = app(PrepararEvaluacionInstitucional::class)->preparar($this->usuario, $this->residente, CarbonImmutable::now());
        self::assertSame($patron, $dto->contenido['petersen']['contenido']['salida']['patron']);
        self::assertCount(5, $dto->contenido['instrumental']['contenido']['capturas']['capturas']);
        $regla = ReglaExperta::query()->where('cod_version_modelo', $versionGobierno)->where('codigo_regla', $patron)->sole();
        app(GestionarReglasExpertas::class)->revisar($this->usuario, $regla->getKey(), 'RECHAZADA', 'Ensayo de rechazo sin modificar las fuentes originales.');
        $rechazado = app(ConsultarPetersenInstitucional::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertNull($rechazado['patron']);
        self::assertContains('ABSTENCION_SIN_REGLA_PETERSEN_HABILITADA', $rechazado['impedimentos']);
        self::assertDatabaseCount('valoraciones_petersen_originales', 9);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        config(['sistema_experto.perfiles_instrumentales' => []]);
        $sinPerfil = app(ConsultarPetersenInstitucional::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertNull($sinPerfil['patron']);
        self::assertSame('U', $sinPerfil['dominios']['COG-MEM']['estado']);
        DB::table('respuestas_instrumento')->where('cod_aplicacion', reset($mediciones))->update(['valor_numero' => 0]);
        $consultas = 0;
        DB::listen(static function () use (&$consultas): void {
            $consultas++;
        });
        $replay = InstantaneaEvaluacionInstitucional::verificar($dto->contenido, $dto->huella);
        self::assertSame(0, $consultas);
        self::assertSame($dto->contenido, $replay->contenido);
        self::assertSame($patron, $replay->contenido['petersen']['contenido']['salida']['patron']);
    }

    public function test_formulario_conectado_guarda_y_conserva_errores_sin_fingir_exito(): void
    {
        $area = Area::create(['nombre' => 'Psicología sintética', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'VALORACION', 'fecha_hora' => '2026-10-18 10:00:00', 'estado' => 'FINALIZADA']);
        $this->get(route('admin.sistema-experto.petersen-original', $this->residente))->assertOk()->assertSee('Valoraciones originales para Petersen');
        $panel = Livewire::test(CapturaPetersenPanel::class, ['residente' => $this->residente])->call('nuevo', 'C04')
            ->assertSee('Referencia del acto original')->set('form.cod_atencion', $atencion->getKey())
            ->set('form.cod_documento_clinico', '')->set('form.localizador_fuente', '')
            ->set('form.estado_original', 'N')->set('form.fecha_hora', '2026-10-18T10:00:00')
            ->call('guardar')->assertHasErrors('justificacion');
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        $panel->set('form.justificacion', 'Valoración original incompleta registrada explícitamente.')->call('guardar')
            ->assertHasNoErrors()->assertSee('Valoración original registrada')->assertSet('solicitud', null);
        self::assertDatabaseCount('valoraciones_petersen_originales', 1);
        self::assertDatabaseHas('valoraciones_petersen_originales', ['cod_atencion' => $atencion->getKey(), 'cod_documento_clinico' => null, 'localizador_fuente' => null]);
    }

    public function test_rectificacion_iadl_respeta_el_corte_y_se_consulta_en_lote(): void
    {
        $comunes = ['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'cod_usuario_registro' => $this->usuario->getKey(), 'fecha_hora' => '2026-10-15 09:00:00', 'registrado_en' => '2026-10-15 10:00:00',
            'oportunidad' => 'OBSERVADA', 'desempeno' => 'INDEPENDIENTE', 'tipo_ayuda' => 'NINGUNA', 'causa_ayuda' => 'NINGUNA',
            'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO'];
        $a = ControlFuncionalIadl::create($comunes + ['actividad' => 'MEDICACION']);
        $b = ControlFuncionalIadl::create($comunes + ['actividad' => 'PERTENENCIAS']);
        $this->registrar($this->entrada(['elemento' => 'C03', 'estado_original' => 'S', 'revision_integrada' => true,
            'oportunidad_adecuada' => true, 'atribucion_revisada' => true,
            'soportes' => [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $a->getKey()], ['tipo_soporte' => 'IADL', 'cod_control_iadl' => $b->getKey()]]]));
        ControlFuncionalIadl::create(array_replace($comunes, ['actividad' => 'MEDICACION', 'cod_control_anterior' => $a->getKey(),
            'estado' => 'RECTIFICACION', 'registrado_en' => '2026-10-19 12:01:00']));
        $lector = app(ConsultarPetersenInstitucional::class);
        $antes = $lector->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame('S', $antes['requisitos']['C03']['estado']);

        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:02:00-04:00'));
        $consultasIadl = 0;
        DB::listen(static function ($consulta) use (&$consultasIadl): void {
            if (preg_match('/^select .*from "controles_funcionales_iadl"/s', $consulta->sql)) {
                $consultasIadl++;
            }
        });
        $despues = $lector->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame('U', $despues['requisitos']['C03']['estado']);
        self::assertNull($despues['patron']);
        self::assertSame(2, $consultasIadl, 'La lectura original y la sucesión se resuelven en lote, sin una consulta por soporte.');
        self::assertSame('REGISTRADO', $a->fresh()->estado);
    }

    public static function metadatosManipulados(): array
    {
        return [['solicitud_uuid', '00000000-0000-4000-8000-000000000000'], ['tipo_registro', 'ANULACION'], ['cod_valoracion_anterior', 'CADENA-AJENA']];
    }

    public static function precisionClinica(): array
    {
        return [['000000'], ['123456']];
    }

    #[DataProvider('precisionClinica')]
    public function test_rectificar_y_anular_desde_formulario_preserva_microsegundos_sin_fecha_html_invalida(string $microsegundos): void
    {
        $original = $this->registrar($this->entrada(['fecha_hora' => '2026-10-18T10:00:00.'.$microsegundos.'-04:00']));
        $panel = Livewire::test(CapturaPetersenPanel::class, ['residente' => $this->residente])
            ->call('corregir', $original->getKey())->assertSet('form.fecha_hora', '2026-10-18T10:00:00.'.substr($microsegundos, 0, 3))
            ->set('form.error_fecha_original', 'Se revisó la fecha original; la representación visible permanece igual.')
            ->set('form.justificacion', 'Rectificación sintética sin modificación del instante clínico.')->call('guardar')->assertHasNoErrors();
        $sucesora = ValoracionPetersenOriginal::query()->where('cod_valoracion_anterior', $original->getKey())->sole();
        self::assertSame($microsegundos, $sucesora->fecha_hora->format('u'));
        $panel->assertSee('Registro histórico. Sucesora:')->call('corregir', $original->getKey())->assertHasErrors('historial');
        $panel->call('corregir', $sucesora->getKey(), true)->set('form.justificacion', 'Anulación explícita conservando precisión del acto original.')
            ->call('guardar')->assertHasNoErrors();
        $anulacion = ValoracionPetersenOriginal::query()->where('cod_valoracion_anterior', $sucesora->getKey())->sole();
        self::assertSame('ANULACION', $anulacion->tipo_registro);
        self::assertSame($microsegundos, $anulacion->fecha_hora->format('u'));
        self::assertDatabaseCount('valoraciones_petersen_originales', 3);
    }

    public function test_iadl_exige_competencia_contextual_en_selector_y_lector_antes_de_consultar(): void
    {
        $iadl = ControlFuncionalIadl::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'cod_usuario_registro' => $this->usuario->getKey(), 'fecha_hora' => '2026-10-15 09:00:00', 'registrado_en' => '2026-10-15 10:00:00',
            'actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA', 'desempeno' => 'INDEPENDIENTE', 'tipo_ayuda' => 'NINGUNA',
            'causa_ayuda' => 'NINGUNA', 'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO']);
        $this->registrar($this->entrada(['elemento' => 'C03', 'estado_original' => 'S', 'revision_integrada' => true,
            'oportunidad_adecuada' => true, 'atribucion_revisada' => true,
            'soportes' => [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $iadl->getKey()]]]));
        $incompatible = $this->profesional('MEDICO GENERAL/GERIATRA');
        $incompatible->personal->update(['profesion' => 'ENFERMERIA']);
        self::assertTrue($incompatible->checkPermissionTo('controles_funcionales_iadl.ver', 'web'));
        $consultasIadl = 0;
        DB::listen(static function ($consulta) use (&$consultasIadl): void {
            if (preg_match('/^select .*from ["`]?controles_funcionales_iadl["`]?/s', $consulta->sql)) {
                $consultasIadl++;
            }
        });
        Livewire::actingAs($incompatible)->test(CapturaPetersenPanel::class, ['residente' => $this->residente])
            ->set('form', ['elemento' => 'C03', 'soportes' => []])->set('tipoSoporte', 'IADL')->set('campoFuente', 'cod_control_iadl')
            ->assertViewHas('fuentes', fn ($fuentes) => ! in_array('cod_control_iadl', $fuentes, true))->assertViewHas('opciones', []);
        try {
            app(ConsultarPetersenInstitucional::class)->consultar($incompatible, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
            self::fail('No debe entregar una evaluación Petersen parcial sin competencia IADL.');
        } catch (AuthorizationException $e) {
            self::assertSame('This action is unauthorized.', $e->getMessage());
        }
        self::assertSame(0, $consultasIadl);
    }

    #[DataProvider('metadatosManipulados')]
    public function test_formulario_no_sustituye_identidad_tipo_ni_cadena_bloqueados(string $campo, string $valor): void
    {
        Livewire::test(CapturaPetersenPanel::class, ['residente' => $this->residente])->call('nuevo', 'C04')
            ->set('form.estado_original', 'N')->set('form.fecha_hora', '2026-10-18T10:00:00')
            ->set('form.justificacion', 'Intento sintético con metadata manipulada.')
            ->set('form.'.$campo, $valor)->call('guardar')->assertHasErrors('registro');
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        self::assertDatabaseCount('soportes_valoracion_petersen', 0);
    }
}
