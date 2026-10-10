<?php

namespace Tests\Support\SistemaExperto;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

abstract class PruebaConBaseDesechable extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $c = DB::connection();
        $segura = ($c->getDriverName() === 'sqlite' && $c->getDatabaseName() === ':memory:')
            || ($c->getDriverName() === 'pgsql' && preg_match('/^remembermind_experto_test_[0-9]{8}_[a-z0-9]+$/D', $c->getDatabaseName()));
        if (! app()->environment('testing') || ! $segura) {
            throw new RuntimeException('La prueba experta exige una BDD desechable dedicada.');
        }
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        $this->artisan('migrate:rollback', ['--force' => true])->assertSuccessful();
        parent::tearDown();
    }
}
