<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\CatalogoConservacionExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ProyeccionReduccionExperta as P;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;
use Tests\Support\CorromperConservacionParaPrueba;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

final class ReduccionConcurrenciaPostgresTest extends TestCase
{
    use CorromperConservacionParaPrueba;

    private bool $propia = false;

    protected function setUp(): void
    {
        parent::setUp();
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La concurrencia requiere PostgreSQL desechable.');
        }
        if (! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Destino de concurrencia no permitido.');
        }
        $this->propia = true;
        DB::statement('DROP SCHEMA public CASCADE');
        DB::statement('CREATE SCHEMA public');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        EscenarioRepositorios::preparar();
        CatalogoConservacionExperta::preparar('pgsql', 'SIM-V1', 'SIM-USU');
    }

    protected function tearDown(): void
    {
        if ($this->propia) {
            DB::statement('DROP SCHEMA public CASCADE');
            DB::statement('CREATE SCHEMA public');
        }
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    public static function carreras(): array
    {
        return [['clasificacion'], ['iadl'], ['orden'], ['mismo_hecho'], ['padre']];
    }

    #[DataProvider('carreras')]
    public function test_serializa_escrituras_sin_perder_originales(string $modo): void
    {
        // Proporciona una evidencia institucional sintética para ejercitar ambas ramas del vínculo.
        EscenarioRepositorios::evidencia();
        $almacen = new AlmacenInstantaneas('pgsql');
        $almacen->guardar(E::perfil()->cognicion);
        $datos = ['modo' => $modo, 'conexion' => DB::connection()->getConfig()];
        if ($modo !== 'clasificacion') {
            $almacen->guardarClasificacion((new ClasificadorPetersenService)->clasificar(E::perfil()));
            if ($modo === 'iadl') {
                $h = (array) DB::table(P::HECHOS)->whereNotNull('cod_evidencia_iadl_instantanea')->first();
                $bloque = array_intersect_key($h, array_flip(array_values(P::IAD)));
                $datos['hecho'] = $h['cod_hecho_instantanea'];
                $this->corromperConservacion(fn () => DB::table(P::HECHOS)->where('cod_hecho_instantanea', $datos['hecho'])->update(array_fill_keys(array_values(P::IAD), null)));
                foreach ([1, 2] as $i) {
                    $datos['cambios'][$i] = array_replace($bloque, ['cod_evidencia_iadl_instantanea' => 'EIX-CARRERA-'.$i]);
                }
                $baseAntes = array_diff_key($h, $bloque);
            } else {
                $hs = DB::table(P::HECHOS)->orderBy('orden')->limit(2)->get();
                $objetivo = $modo === 'mismo_hecho' ? $hs[0] : $hs[1];
                $this->corromperConservacion(fn () => DB::table(P::HECHOS)->where('cod_hecho_instantanea', $objetivo->cod_hecho_instantanea)
                    ->update(['cod_evidencia_evaluacion' => 'SIM-EVID', 'tipo_origen' => 'D137', 'cod_atencion' => 'SIM-AT']));
                $v = (array) DB::table(P::VINCULOS)->first();
                $criterio = $v['cod_evaluacion_criterio'];
                DB::table(P::VINCULOS)->where('cod_evaluacion_criterio', $criterio)->delete();
                $datos['vinculos'][1] = array_replace($v, ['cod_evidencia_criterio' => 'EVC-CARRERA-1', 'cod_soporte_sintetico' => 'SSX-CARRERA',
                    'cod_hecho_instantanea' => $hs[0]->cod_hecho_instantanea, 'cod_evidencia_evaluacion' => null, 'fecha_hora_vinculacion' => null, 'orden_conservacion' => 0]);
                $datos['vinculos'][2] = array_replace($v, ['cod_evidencia_criterio' => 'EVC-CARRERA-2', 'cod_soporte_sintetico' => null,
                    'cod_hecho_instantanea' => null, 'cod_evidencia_evaluacion' => 'SIM-EVID', 'fecha_hora_vinculacion' => '2026-10-20 10:00:00', 'orden_conservacion' => $modo === 'orden' ? 0 : 1]);
                if ($modo === 'padre') {
                    $cab = (array) DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', 'SIM-EVAL')->first();
                    $cab['cod_evaluacion_experta'] = 'SIM-EVAL-OTRA';
                    foreach (['id_evaluacion_dominio', 'version_conservacion', 'huella_memoria_cognicion', 'fecha_corte_utc', 'fecha_corte_zona', 'fecha_corte_offset'] as $columna) {
                        $cab[$columna] = null;
                    }
                    DB::table('evaluaciones_expertas')->insert($cab);
                    $datos['criterio'] = $criterio;
                }
            }
        }
        $salidas = $this->correr($datos);
        self::assertSame($modo === 'clasificacion' ? ['guardado', 'guardado'] : ['guardado', 'rechazado'], $salidas);
        self::assertSame(1, DB::table(P::CLASIFICACIONES)->count());
        if ($modo === 'iadl') {
            $h = (array) DB::table(P::HECHOS)->where('cod_hecho_instantanea', $datos['hecho'])->first();
            self::assertSame($baseAntes, array_diff_key($h, $bloque));
            self::assertContains($h['cod_evidencia_iadl_instantanea'], ['EIX-CARRERA-1', 'EIX-CARRERA-2']);
        } elseif ($modo === 'padre') {
            $q = DB::table(P::VINCULOS.' as v')->join('evaluacion_criterios as c', 'v.cod_evaluacion_criterio', '=', 'c.cod_evaluacion_criterio')
                ->join(P::HECHOS.' as h', 'h.cod_hecho_instantanea', '=', 'v.cod_hecho_instantanea')->where('v.cod_evaluacion_criterio', $criterio);
            self::assertSame(0, (clone $q)->whereColumn('c.cod_evaluacion_experta', '<>', 'h.cod_evaluacion_experta')->count());
            self::assertSame(DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', $criterio)->value('cod_evaluacion_experta') === 'SIM-EVAL' ? 1 : 0, $q->count());
        } elseif ($modo !== 'clasificacion') {
            self::assertSame(1, DB::table(P::VINCULOS)->where('cod_evaluacion_criterio', $criterio)->count());
        }
    }

    private function correr(array $datos): array
    {
        $dir = sys_get_temp_dir().'/remembermind-reduccion-race-'.bin2hex(random_bytes(8));
        File::makeDirectory($dir, 0700, true);
        $datos['directorio'] = $dir;
        $archivo = $dir.'/contexto.json';
        file_put_contents($archivo, json_encode($datos, JSON_THROW_ON_ERROR));
        $barrera = fopen($dir.'/barrera', 'c+');
        flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $i) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/reduccion_concurrencia_worker.php'), $archivo, (string) $i], base_path());
                $p->setTimeout(30)->start();
                $procesos[] = $p;
            }
            $limite = microtime(true) + 20;
            while ((! is_file($dir.'/listo-1') || ! is_file($dir.'/listo-2')) && microtime(true) < $limite) {
                clearstatcache();
                usleep(10000);
            }
            self::assertFileExists($dir.'/listo-1');
            self::assertFileExists($dir.'/listo-2');
            flock($barrera, LOCK_UN);
            $salidas = [];
            foreach ($procesos as $p) {
                self::assertSame(0, $p->wait(), $p->getErrorOutput());
                $salidas[] = $p->getOutput();
            }
            sort($salidas);

            return $salidas;
        } finally {
            flock($barrera, LOCK_UN);
            fclose($barrera);
            foreach ($procesos as $p) {
                if ($p->isRunning()) {
                    $p->stop(1);
                }
            }
            if (dirname(realpath($dir)) === realpath(sys_get_temp_dir()) && ! is_link($dir)
                && preg_match('/^remembermind-reduccion-race-[a-f0-9]{16}$/D', basename($dir))) {
                File::deleteDirectory($dir);
            }
        }
    }
}
