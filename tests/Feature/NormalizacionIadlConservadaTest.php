<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ClasificacionDclInstantanea;
use App\Models\HechoInstantaneaExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\CatalogoConservacionExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ContextoIadl;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MotorExperto\conservacion\CodecInstantanea;
use MotorExperto\dominio\Services\Dcl\ClasificadorPetersenService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;
use Tests\Support\CorromperConservacionParaPrueba;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

final class NormalizacionIadlConservadaTest extends TestCase
{
    use CorromperConservacionParaPrueba;
    use EsquemaSinTransaccion;

    private function almacen(): AlmacenInstantaneas
    {
        return new AlmacenInstantaneas(DB::getDefaultConnection());
    }

    private function preparar(): void
    {
        EscenarioRepositorios::preparar();
        CatalogoConservacionExperta::preparar(DB::getDefaultConnection(), 'SIM-V1', 'SIM-USU');
    }

    public static function perfiles(): iterable
    {
        yield 'un dominio' => [['A', 'P', 'P', 'P', 'P'], 'PET-01'];
        yield 'varios dominios' => [['A', 'A', 'P', 'P', 'P'], 'PET-02'];
        yield 'sin clasificacion' => [['U', 'U', 'U', 'U', 'U'], null];
    }

    #[DataProvider('perfiles')]
    public function test_el_motor_conserva_la_misma_clasificacion_sin_repetir_funcionalidad(array $estados, ?string $patron): void
    {
        $this->preparar();
        $perfil = E::perfil($estados);
        $original = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->almacen()->guardar($perfil->cognicion);
        $this->almacen()->guardarClasificacion($original);
        self::assertFalse(Schema::hasColumn('hechos_instantanea_experta', 'iadl_cod_funcionalidad_instantanea'));
        $recuperado = $this->almacen()->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $perfil->versionContrato);
        self::assertSame($patron, $recuperado->patronCandidato?->value);
        self::assertSame(CodecInstantanea::huella($original), CodecInstantanea::huella($recuperado));
        $clasificacion = ClasificacionDclInstantanea::query()->with('evidenciasIadl')->sole();
        $hechos = HechoInstantaneaExperta::query()->with('funcionalidad')->get();
        self::assertNotEmpty($clasificacion->evidenciasIadl);
        foreach ($hechos as $hecho) {
            if ($hecho->cod_evidencia_iadl_instantanea === null) {
                self::assertNull($hecho->iadl_cod_funcionalidad_instantanea);
                self::assertNull($hecho->funcionalidad);
            } else {
                self::assertSame($clasificacion->cod_funcionalidad_instantanea, $hecho->iadl_cod_funcionalidad_instantanea);
                self::assertSame($clasificacion->getKey(), $hecho->funcionalidad->getKey());
            }
        }
    }

    public function test_reversion_poblada_y_reaplicacion_preservan_dto_hechos_y_ausencias(): void
    {
        $this->preparar();
        $perfil = E::perfil();
        $original = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->almacen()->guardar($perfil->cognicion);
        $this->almacen()->guardarClasificacion($original);
        $antes = ContextoIadl::consulta(DB::connection())->orderBy('cod_hecho_instantanea')->get()->all();
        $tablas = Schema::getTables();
        $m = require database_path('migrations/2026_11_20_000003_derive_iadl_functional_context.php');
        $m->down();
        self::assertTrue(Schema::hasColumn('hechos_instantanea_experta', 'iadl_cod_funcionalidad_instantanea'));
        self::assertSame(0, DB::table('hechos_instantanea_experta')->whereNull('cod_evidencia_iadl_instantanea')->whereNotNull('iadl_cod_funcionalidad_instantanea')->count());
        self::assertGreaterThan(0, DB::table('hechos_instantanea_experta')->whereNotNull('cod_evidencia_iadl_instantanea')->whereNotNull('iadl_cod_funcionalidad_instantanea')->count());
        $m->up();
        self::assertEquals($antes, ContextoIadl::consulta(DB::connection())->orderBy('cod_hecho_instantanea')->get()->all());
        self::assertCount(count($tablas), Schema::getTables());
        self::assertSame(CodecInstantanea::huella($original), CodecInstantanea::huella($this->almacen()->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $perfil->versionContrato)));
    }

    public function test_sql_rechaza_iadl_sin_funcionalidad_y_no_conserva_un_anexo_parcial(): void
    {
        $this->preparar();
        $this->almacen()->guardar(E::perfil()->cognicion);
        $hecho = DB::table('hechos_instantanea_experta')->where('id_hecho_dominio', 'SIM-IADL')->sole();
        try {
            DB::transaction(fn () => DB::table('hechos_instantanea_experta')->where('cod_hecho_instantanea', $hecho->cod_hecho_instantanea)->update([
                'cod_evidencia_iadl_instantanea' => 'IADL-SIN-FUNCION', 'iadl_actividad_id_dominio' => 'COMPRAS',
                'iadl_estado' => 'CONSERVADA', 'iadl_oportunidad' => 'OBSERVADA', 'iadl_orden' => 0,
            ]));
            self::fail('No puede anexarse IADL sin la funcionalidad conservada de su propia evaluación.');
        } catch (QueryException $e) {
            self::assertStringContainsString('funcionalidad', $e->getMessage());
        }
        self::assertSame(0, DB::table('hechos_instantanea_experta')->whereNotNull('cod_evidencia_iadl_instantanea')->count());
        self::assertEquals($hecho, DB::table('hechos_instantanea_experta')->where('cod_hecho_instantanea', $hecho->cod_hecho_instantanea)->sole());
    }

    public function test_reinstalar_guardas_tras_un_ensayo_no_rompe_el_anexo_del_motor(): void
    {
        $this->preparar();
        $perfil = E::perfil();
        $original = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->almacen()->guardar($perfil->cognicion);
        $hecho = DB::table('hechos_instantanea_experta')->where('id_hecho_dominio', 'SIM-IADL')->sole();
        $this->corromperConservacion(fn () => DB::table('hechos_instantanea_experta')
            ->where('cod_hecho_instantanea', $hecho->cod_hecho_instantanea)
            ->update(['concepto_dominio' => $hecho->concepto_dominio]));
        $this->almacen()->guardarClasificacion($original);
        self::assertSame(CodecInstantanea::huella($original), CodecInstantanea::huella($this->almacen()->obtenerClasificacion(
            'SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $perfil->versionContrato)));
        self::assertGreaterThan(0, DB::table('hechos_instantanea_experta')->whereNotNull('cod_evidencia_iadl_instantanea')->count());
    }

    public function test_postgres_bloquea_evidencia_antes_de_evaluacion_sin_invertir_el_orden(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! function_exists('pg_send_query_params')) {
            self::markTestSkipped('La comprobación requiere PostgreSQL desechable y su conexión asíncrona.');
        }
        $this->preparar();
        EscenarioRepositorios::evidencia();
        $this->almacen()->guardar(E::perfil()->cognicion);
        $fila = (array) DB::table('hechos_instantanea_experta')->orderBy('cod_hecho_instantanea')->first();
        $fila['cod_hecho_instantanea'] = 'HECHO-CONCURRENT-FN5';
        $fila['id_hecho_dominio'] = 'SIM-CONCURRENCIA-5FN';
        $fila['orden'] = DB::table('hechos_instantanea_experta')->max('orden') + 1;
        $fila['cod_evidencia_evaluacion'] = 'SIM-EVID';
        $fila['tipo_origen'] = 'D137';
        $fila['cod_atencion'] = 'SIM-AT';
        $db = DB::connection();
        $config = $db->getConfig();
        $escapar = static fn ($v) => "'".str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $v)."'";
        $dsn = implode(' ', array_map(fn ($k) => $k.'='.$escapar(match ($k) {
            'dbname' => $config['database'], 'user' => $config['username'], default => $config[$k],
        }), ['host', 'port', 'dbname', 'user', 'password']));
        $nativa = @pg_connect($dsn, PGSQL_CONNECT_FORCE_NEW);
        self::assertNotFalse($nativa, 'No se pudo abrir la conexión independiente de pruebas.');
        config(['database.connections.fn5_observador_iadl' => $config]);
        $observador = DB::connection('fn5_observador_iadl');
        try {
            pg_query($nativa, "SET statement_timeout='8s'");
            $pid = (int) pg_fetch_result(pg_query($nativa, 'SELECT pg_backend_pid()'), 0, 0);
            $db->beginTransaction();
            $db->table('evaluaciones_expertas')->where('cod_evaluacion_experta', $fila['cod_evaluacion_experta'])->lockForUpdate()->first();
            $campos = implode(',', array_map(fn ($c) => $db->getQueryGrammar()->wrap($c), array_keys($fila)));
            $marcadores = implode(',', array_map(fn ($n) => '$'.$n, range(1, count($fila))));
            $parametros = array_map(static fn ($valor) => is_bool($valor) ? ($valor ? 'true' : 'false') : $valor, array_values($fila));
            self::assertTrue(pg_send_query_params($nativa, 'INSERT INTO hechos_instantanea_experta ('.$campos.') VALUES ('.$marcadores.')', $parametros));
            $esperando = false;
            $limite = microtime(true) + 3;
            do {
                $db->select('SELECT pg_stat_clear_snapshot()');
                $esperando = $db->selectOne('SELECT wait_event_type FROM pg_stat_activity WHERE pid=?', [$pid])?->wait_event_type === 'Lock';
                if (! $esperando) {
                    usleep(20000);
                }
            } while (! $esperando && microtime(true) < $limite);
            self::assertTrue($esperando, 'El INSERT debe estar esperando la evaluación bloqueada.');
            try {
                $observador->select('SELECT cod_evidencia_evaluacion FROM evidencias_evaluacion WHERE cod_evidencia_evaluacion=? FOR UPDATE NOWAIT', ['SIM-EVID']);
                self::fail('La inserción debe haber bloqueado primero la evidencia.');
            } catch (QueryException $e) {
                self::assertSame('55P03', $e->errorInfo[0]);
            }
            $db->commit();
            $resultado = pg_get_result($nativa);
            self::assertSame(PGSQL_COMMAND_OK, pg_result_status($resultado), pg_result_error($resultado));
            self::assertSame(1, DB::table('hechos_instantanea_experta')->where('cod_hecho_instantanea', $fila['cod_hecho_instantanea'])->count());
        } finally {
            while ($db->transactionLevel() > 0) {
                $db->rollBack();
            }
            pg_close($nativa);
            DB::purge('fn5_observador_iadl');
        }
    }
}
