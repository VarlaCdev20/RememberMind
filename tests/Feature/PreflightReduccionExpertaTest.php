<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\EvaluarReduccionConservacion;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ProyeccionReduccionExperta as P;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionReduccionConservacion;
use DateTimeImmutable;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

final class PreflightReduccionExpertaTest extends TestCase
{

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
