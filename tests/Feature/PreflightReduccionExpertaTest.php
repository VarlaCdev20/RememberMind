<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\EvaluarReduccionConservacion;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ProyeccionReduccionExperta as P;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionReduccionConservacion;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\EjecutorSimulacionLocal;
use DateTimeImmutable;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CohorteSimulacionLocal;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

final class PreflightReduccionExpertaTest extends TestCase
{
    use CohorteSimulacionLocal;

    private bool $basePostgresPropia = false;

    protected function setUp(): void
    {
        parent::setUp();
        // Tests\TestCase ya confirmó :memory: o el PostgreSQL desechable propio.
        // El preflight exige una transacción propia y los down de historia rechazan
        // retirar snapshots: preparar exclusivamente esta base de pruebas sin rollback de esquema.
        if (DB::getDriverName() === 'pgsql') {
            $db = DB::connection();
            if (! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
                throw new \RuntimeException('El preflight de prueba requiere el runner PostgreSQL desechable propio.');
            }
            $this->basePostgresPropia = true;
            // Mismo aislamiento que las pruebas de concurrencia existentes: fresh no retira funciones.
            DB::statement('DROP SCHEMA public CASCADE');
            DB::statement('CREATE SCHEMA public');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        RefreshDatabaseState::$migrated = false;
    }

    protected function tearDown(): void
    {
        if ($this->basePostgresPropia) {
            DB::statement('DROP SCHEMA public CASCADE');
            DB::statement('CREATE SCHEMA public');
        }
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    public function test_cinco_casos_con_datos_reales_sinteticos_se_proyectan_y_reconstruyen_sin_escribir(): void
    {
        $datos = $this->prepararCohorte();
        (new EjecutorSimulacionLocal(new ContextoSimulacionLocal(config('database.default'))))->ejecutar(
            $datos['autor']->getKey(), $datos['personal']->getKey(), new DateTimeImmutable('2026-10-17T12:00:00-04:00'));
        $antes = DB::table('secuencias_codigos')->orderBy('entidad')->get()->map(static fn ($f) => (array) $f)->all();
        $consultas = [];
        DB::listen(static function ($q) use (&$consultas): void {
            $consultas[] = $q->sql;
        });
        $r = (new EvaluarReduccionConservacion)->evaluar(DB::connection());
        self::assertTrue($r['igualdad_filas']);
        self::assertFalse($r['ddl_ejecutado']);
        self::assertFalse($r['dto_tras_migracion_verificado']);
        self::assertFalse($r['recalculo_institucional_completo_verificado']);
        self::assertSame(5, $r['filas_por_tabla'][P::FUNCIONALIDAD]);
        self::assertGreaterThan(0, $r['filas_por_tabla'][P::COGNITIVAS]);
        self::assertGreaterThan(0, $r['filas_por_tabla'][P::IADL]);
        self::assertGreaterThan(0, $r['filas_por_tabla'][P::SINTETICOS]);
        self::assertSame($antes, DB::table('secuencias_codigos')->orderBy('entidad')->get()->map(static fn ($f) => (array) $f)->all());
        foreach ($consultas as $sql) {
            self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|alter|create|drop|truncate)\b/i', $sql);
        }
        $this->artisan('app:auditar-base-datos-remember-mind', ['--evaluar-reduccion' => true])
            ->expectsOutputToContain('PASS_RECONSTRUCCION_MODELO_REDUCIDO')
            ->expectsOutputToContain('Sin DDL; no acredita migración ni recálculo institucional completo.')
            ->assertSuccessful();
        // La base anterior no tiene UNIQUE por evaluación/orden cognitivo.
        // La transición debe detectar una colección alterada, no normalizarla al copiar.
        (new TransicionReduccionConservacion)->bajar();
        $id = DB::table(P::COGNITIVAS)->value('cod_evidencia_cognitiva_instantanea');
        DB::table(P::COGNITIVAS)->where('cod_evidencia_cognitiva_instantanea', $id)->update(['orden' => 999]);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('saltos o duplicados');
        (new EvaluarReduccionConservacion)->evaluar(DB::connection());
    }

    public function test_referencia_entrante_fuera_del_mapa_bloquea_el_plan(): void
    {
        (new TransicionReduccionConservacion)->bajar();
        Schema::create('prueba_fk_consolidacion', function (Blueprint $t): void {
            $t->string('cod_cognitiva', 20)->nullable();
            $t->foreign('cod_cognitiva')->references('cod_evidencia_cognitiva_instantanea')->on(P::COGNITIVAS);
        });
        try {
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('FK entrante');
            (new EvaluarReduccionConservacion)->evaluar(DB::connection());
        } finally {
            Schema::drop('prueba_fk_consolidacion');
        }
    }

    public function test_no_declara_lectura_repetible_dentro_de_una_transaccion_ajena(): void
    {
        DB::beginTransaction();
        try {
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('transacción propia');
            (new EvaluarReduccionConservacion)->evaluar(DB::connection());
        } finally {
            DB::rollBack();
        }
    }

    public function test_fk_entrante_desde_otro_esquema_postgres_bloquea_la_reduccion(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('El caso de esquemas separados requiere PostgreSQL.');
        }
        (new TransicionReduccionConservacion)->bajar();
        DB::statement('CREATE SCHEMA prueba_fk_externa');
        try {
            DB::statement('CREATE TABLE prueba_fk_externa.origen (cod_cognitiva varchar(20) REFERENCES public.evidencias_cognitivas_instantanea(cod_evidencia_cognitiva_instantanea))');
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('otro esquema');
            (new EvaluarReduccionConservacion)->evaluar(DB::connection());
        } finally {
            DB::statement('DROP TABLE IF EXISTS prueba_fk_externa.origen');
            DB::statement('DROP SCHEMA prueba_fk_externa');
        }
    }
}
