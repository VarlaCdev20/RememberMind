<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas;
use App\Models\Admision;
use App\Models\Area;
use App\Models\Consentimiento;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class NormalizacionConsentimientosCuidadosTest extends TestCase
{
    use EsquemaSinTransaccion;

    private function escenario(): array
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'FN5-CUIDADO', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $r = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $turno = Turno::create(['nombre' => 'FN5', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'estado' => 'ACTIVO']);
        $j = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        $area = Area::create(['nombre' => 'Cuidado sintético', 'estado' => 'ACTIVA']);
        $plan = PlanCuidado::create(['cod_residente' => $r->getKey(), 'cod_area' => $area->getKey(), 'cod_personal' => $p->getKey(),
            'tipo_plan' => 'CUIDADO', 'nombre' => 'Plan original', 'objetivo_general' => 'Cuidado registrado', 'fecha_hora_apertura' => now(), 'estado' => 'ACTIVO']);
        $i = IntervencionCuidado::create(['cod_plan' => $plan->getKey(), 'nombre' => 'Intervención', 'descripcion' => 'Registro sintético', 'estado' => 'ACTIVA']);
        $a = Admision::create(['cod_residente' => $r->getKey(), 'cod_usuario_registro' => $u->getKey(), 'fecha_hora_admision' => now(), 'motivo_ingreso' => 'Registro sintético', 'estado' => 'ACTIVA']);
        $e = EjecucionCuidado::create(['cod_intervencion' => $i->getKey(), 'cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(),
            'cod_jornada' => $j->getKey(), 'fecha_hora_programada' => now(), 'estado' => 'PENDIENTE']);
        $c = Consentimiento::create(['cod_admision' => $a->getKey(), 'cod_residente' => $r->getKey(), 'cod_usuario_registro' => $u->getKey(),
            'tipo_consentimiento' => 'INGRESO', 'firma_residente' => true, 'fecha_consentimiento' => now(), 'estado' => 'VIGENTE']);

        return compact('u', 'p', 'r', 'otro', 'j', 'plan', 'i', 'a', 'e', 'c');
    }

    public function test_lectura_filtros_y_relaciones_conservan_el_residente_sin_duplicarlo(): void
    {
        $d = $this->escenario();
        foreach (['consentimientos' => $d['c'], 'ejecuciones_cuidado' => $d['e']] as $tabla => $modelo) {
            self::assertFalse(Schema::hasColumn($tabla, 'cod_residente'));
            self::assertSame($d['r']->getKey(), $modelo->fresh()->cod_residente);
            self::assertSame($d['r']->getKey(), $modelo->fresh()->residente->getKey());
            self::assertSame(1, $modelo::query()->where('cod_residente', $d['r']->getKey())->count());
            self::assertSame(0, $modelo::query()->where('cod_residente', $d['otro']->getKey())->count());
            self::assertSame($d['r']->getKey(), ColumnasRelacionadas::consulta(DB::connection(), $tabla)->sole()->cod_residente);
        }
        $n = EjecucionCuidado::count();
        try {
            EjecucionCuidado::create(['cod_intervencion' => $d['i']->getKey(), 'cod_residente' => $d['otro']->getKey(),
                'cod_personal' => $d['p']->getKey(), 'cod_jornada' => $d['j']->getKey(), 'estado' => 'PENDIENTE']);
            self::fail('El residente indicado no corresponde al plan.');
        } catch (ValidationException $error) {
            self::assertArrayHasKey('cod_intervencion', $error->errors());
        }
        self::assertSame($n, EjecucionCuidado::count());
    }

    public function test_sql_directo_no_traslada_historia_ni_por_el_padre_ni_por_el_hijo(): void
    {
        $d = $this->escenario();
        $a2 = Admision::create(['cod_residente' => $d['otro']->getKey(), 'cod_usuario_registro' => $d['u']->getKey(),
            'fecha_hora_admision' => now(), 'motivo_ingreso' => 'Otro ingreso sintético', 'estado' => 'ACTIVA']);
        $p2 = $d['plan']->replicate();
        $p2->cod_residente = $d['otro']->getKey();
        $p2->save();
        $i2 = IntervencionCuidado::create(['cod_plan' => $p2->getKey(), 'nombre' => 'Otra intervención', 'descripcion' => 'Registro sintético', 'estado' => 'ACTIVA']);
        foreach ([
            fn () => DB::table('planes_cuidado')->where('cod_plan', $d['plan']->getKey())->update(['cod_residente' => $d['otro']->getKey()]),
            fn () => DB::table('admisiones')->where('cod_admision', $d['a']->getKey())->update(['cod_residente' => $d['otro']->getKey()]),
            fn () => DB::table('intervenciones_cuidado')->where('cod_intervencion', $d['i']->getKey())->update(['cod_plan' => $p2->getKey()]),
            fn () => DB::table('ejecuciones_cuidado')->where('cod_ejecucion', $d['e']->getKey())->update(['cod_intervencion' => $i2->getKey()]),
            fn () => DB::table('consentimientos')->where('cod_consentimiento', $d['c']->getKey())->update(['cod_admision' => $a2->getKey()]),
        ] as $operacion) {
            try {
                DB::transaction($operacion);
                self::fail('La operación no debe trasladar el contexto histórico.');
            } catch (QueryException $error) {
                self::assertContains($error->getCode(), ['23000', '23514']);
            }
            self::assertSame($d['r']->getKey(), $d['e']->fresh()->cod_residente);
            self::assertSame($d['r']->getKey(), $d['c']->fresh()->cod_residente);
        }
    }

    public function test_postgres_serializa_el_contexto_entre_conexiones_independientes(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            self::markTestSkipped('La concurrencia se verifica exclusivamente en PostgreSQL desechable.');
        }
        $d = $this->escenario();
        config(['database.connections.fn5_concurrente' => DB::connection()->getConfig()]);
        $principal = DB::connection();
        $otra = DB::connection('fn5_concurrente');
        try {
            $otra->statement("SET lock_timeout='300ms'");
            $principal->beginTransaction();
            Consentimiento::create(['cod_admision' => $d['a']->getKey(), 'cod_residente' => $d['r']->getKey(),
                'cod_usuario_registro' => $d['u']->getKey(), 'tipo_consentimiento' => 'ATENCION',
                'firma_residente' => true, 'fecha_consentimiento' => now(), 'estado' => 'VIGENTE']);
            EjecucionCuidado::create(['cod_intervencion' => $d['i']->getKey(), 'cod_residente' => $d['r']->getKey(),
                'cod_personal' => $d['p']->getKey(), 'cod_jornada' => $d['j']->getKey(), 'estado' => 'PENDIENTE']);
            $mutaciones = [
                ['admisiones', 'cod_admision', $d['a']->getKey()],
                ['planes_cuidado', 'cod_plan', $d['plan']->getKey()],
            ];
            foreach ($mutaciones as [$tabla, $pk, $id]) {
                try {
                    $otra->table($tabla)->where($pk, $id)->update(['cod_residente' => $d['otro']->getKey()]);
                    self::fail('El padre debe esperar a la escritura clínica de otra conexión.');
                } catch (QueryException $e) {
                    self::assertSame('55P03', $e->errorInfo[0]);
                }
            }
            $principal->commit();
            foreach ($mutaciones as [$tabla, $pk, $id]) {
                try {
                    $otra->table($tabla)->where($pk, $id)->update(['cod_residente' => $d['otro']->getKey()]);
                    self::fail('Una vez confirmado, el residente histórico tampoco puede cambiar.');
                } catch (QueryException $e) {
                    self::assertSame('23514', $e->errorInfo[0]);
                }
            }
            self::assertSame(2, Consentimiento::where('cod_residente', $d['r']->getKey())->count());
            self::assertSame(2, EjecucionCuidado::where('cod_residente', $d['r']->getKey())->count());
            self::assertSame(0, EjecucionCuidado::where('cod_residente', $d['otro']->getKey())->count());
        } finally {
            while ($principal->transactionLevel() > 0) {
                $principal->rollBack();
            }
            DB::purge('fn5_concurrente');
        }
    }

    public function test_reversion_poblada_recupera_todos_los_valores_y_la_reaplicacion_no_los_cambia(): void
    {
        $d = $this->escenario();
        $tablas = Schema::getTableListing();
        $antes = [];
        foreach (['consentimientos', 'ejecuciones_cuidado'] as $t) {
            $antes[$t] = DB::table($t)->get()->toArray();
        }
        $m = require database_path('migrations/2026_11_20_000001_derive_consent_and_care_resident.php');
        $m->down();
        foreach (['consentimientos', 'ejecuciones_cuidado'] as $t) {
            self::assertSame($d['r']->getKey(), DB::table($t)->sole()->cod_residente);
        }
        $m->up();
        foreach ($antes as $t => $filas) {
            self::assertEquals($filas, DB::table($t)->get()->toArray());
        }
        self::assertEqualsCanonicalizing($tablas, Schema::getTableListing());
        if (DB::getDriverName() === 'sqlite') {
            self::assertSame([], DB::select('PRAGMA foreign_key_check'));
        }
    }

    public function test_la_migracion_aborta_ante_contexto_incoherente_sin_corregirlo_por_suposion(): void
    {
        $d = $this->escenario();
        $m = require database_path('migrations/2026_11_20_000001_derive_consent_and_care_resident.php');
        $m->down();
        DB::table('consentimientos')->where('cod_consentimiento', $d['c']->getKey())->update(['cod_residente' => $d['otro']->getKey()]);
        try {
            $m->up();
            self::fail('Debe rechazar el contexto anterior incoherente.');
        } catch (\RuntimeException $error) {
            self::assertStringContainsString('residente incompatible', $error->getMessage());
        }
        self::assertTrue(Schema::hasColumn('consentimientos', 'cod_residente'));
        self::assertSame($d['otro']->getKey(), DB::table('consentimientos')->sole()->cod_residente);
        self::assertSame(1, DB::table('ejecuciones_cuidado')->count());
    }
}
