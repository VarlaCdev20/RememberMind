<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas;
use App\Compartido\Infraestructura\Persistencia\ContextosResidente;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\MedicionAntropometrica;
use App\Models\Personal;
use App\Models\RegistroConductual;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Models\ValoracionDolor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class NormalizacionContextosCuidadosTest extends TestCase
{
    use EsquemaSinTransaccion;

    private const TABLAS = [
        'mediciones_clinicas' => ['cod_registro_medicion', 'residentes_mediciones_clinicas'],
        'registros_observaciones_cuidado' => ['cod_observacion_cuidado', 'residentes_observaciones_cuidado'],
    ];

    private function escenario(): array
    {
        $u = User::factory()->create();
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Autor', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'FN5-CONTEXTO', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $r = Residente::factory()->create();
        $otro = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Cuidado sintético', 'estado' => 'ACTIVA']);
        $at = Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'cod_area' => $area->getKey(), 'fecha_hora' => now(), 'tipo_atencion' => 'CUIDADO', 'estado' => 'VIGENTE']);
        $base = ['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'fecha_hora' => now(), 'estado' => 'VIGENTE'];
        $registros = [
            SignoVital::create($base + ['cod_atencion' => $at->getKey(), 'temperatura' => 36.5]),
            SignoVital::create($base + ['temperatura' => 36.7]),
            MedicionAntropometrica::create(array_diff_key($base, ['estado' => true]) + ['peso' => 60, 'talla' => 165, 'imc' => 22.04]),
            ValoracionDolor::create($base + ['intensidad' => 3]),
            RegistroMovilidad::create($base + ['cod_atencion' => $at->getKey(), 'marcha' => 'INDEPENDIENTE']),
            RegistroConductual::create($base + ['descripcion' => 'Observación sintética']),
            RegistroSueno::create(['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'fecha' => today(), 'horas_sueno' => 7, 'estado' => 'VIGENTE']),
        ];

        return compact('u', 'p', 'r', 'otro', 'at', 'base', 'registros');
    }

    public function test_ambas_formas_de_contexto_y_los_seis_tipos_conservan_su_residente(): void
    {
        $d = $this->escenario();
        foreach (self::TABLAS as $tabla => [$pk,$directa]) {
            self::assertFalse(Schema::hasColumn($tabla, 'cod_residente'));
        }
        foreach ($d['registros'] as $registro) {
            $actual = $registro->fresh();
            self::assertSame($d['r']->getKey(), $actual->cod_residente);
            self::assertSame($d['r']->getKey(), $actual->residente->getKey());
            self::assertTrue($registro::where($pk = $registro->getKeyName(), $registro->getKey())->where('cod_residente', $d['r']->getKey())->exists());
            self::assertFalse($registro::where($pk, $registro->getKey())->where('cod_residente', $d['otro']->getKey())->exists());
            [$clave,$directa] = ContextosResidente::CONTRATOS[$registro->getTable()];
            self::assertSame($registro->cod_atencion === null ? 1 : 0, DB::table($directa)->where($clave, $registro->getKey())->count());
        }
        self::assertSame(3, DB::table('residentes_mediciones_clinicas')->count());
        self::assertSame(2, DB::table('residentes_observaciones_cuidado')->count());
        $antropometria = $d['registros'][2]->fresh();
        self::assertSame($antropometria->getKey(), $antropometria->getRawOriginal('cod_medicion_antropometrica'));
        self::assertEquals(22.04, $antropometria->imc);
        $parcial = SignoVital::whereKey($d['registros'][0]->getKey())->get(['cod_registro_medicion', 'fecha_hora'])->sole();
        self::assertSame(['cod_registro_medicion', 'fecha_hora'], array_keys($parcial->getAttributes()));
        self::assertSame($d['r']->getKey(), $parcial->cod_residente);
        self::assertSame([$d['r']->getKey(), $d['r']->getKey()], SignoVital::orderBy('cod_registro_medicion')->pluck('cod_residente')->all());
        $consulta = SignoVital::whereKey($d['registros'][0]->getKey());
        $consulta->get(['cod_registro_medicion']);
        self::assertArrayHasKey('temperatura', $consulta->get()->sole()->getAttributes());
        $consulta->pluck('cod_registro_medicion');
        self::assertArrayHasKey('temperatura', $consulta->get()->sole()->getAttributes());
        self::assertArrayNotHasKey('cod_contexto_directo', $consulta->first()->toArray());
    }

    public function test_rechazos_conservan_el_contrato_y_no_dejan_asociaciones_huerfanas(): void
    {
        $d = $this->escenario();
        $antes = DB::table('mediciones_clinicas')->count();
        try {
            SignoVital::create(array_replace($d['base'], ['cod_residente' => $d['otro']->getKey(), 'cod_atencion' => $d['at']->getKey()]));
            self::fail('No puede atribuirse a otro residente una medición de la atención.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('cod_residente', $e->errors());
        }
        foreach (['SQL_TALLA_5FN', 'SQL_DOBLE_5FN'] as $codigo) {
            try {
                DB::transaction(function () use ($d, $codigo): void {
                    DB::table('residentes_mediciones_clinicas')->insert(['cod_registro_medicion' => $codigo, 'cod_residente' => $d['r']->getKey()]);
                    DB::table('mediciones_clinicas')->insert($codigo === 'SQL_TALLA_5FN'
                        ? ['cod_registro_medicion' => $codigo, 'tipo_evento' => 'ANTROPOMETRIA', 'cod_personal' => $d['p']->getKey(), 'fecha_hora' => now(), 'talla' => 1.65]
                        : ['cod_registro_medicion' => $codigo, 'tipo_evento' => 'SIGNOS', 'cod_personal' => $d['p']->getKey(), 'fecha_hora' => now(), 'estado' => 'VIGENTE', 'cod_atencion' => $d['at']->getKey()]);
                });
                self::fail('El SQL directo debe conservar las restricciones de talla y fuente única.');
            } catch (QueryException $e) {
                self::assertStringContainsString('contrato original', $e->getMessage());
            }
            self::assertSame(0, DB::table('residentes_mediciones_clinicas')->where('cod_registro_medicion', $codigo)->count());
        }
        self::assertSame($antes, DB::table('mediciones_clinicas')->count());
    }

    public function test_reversion_poblada_reconstruye_los_registros_y_sus_autorreferencias(): void
    {
        $d = $this->escenario();
        $dolor = $d['registros'][3];
        $reevaluacion = ValoracionDolor::create($d['base'] + ['cod_atencion' => $d['at']->getKey(), 'cod_valoracion_origen' => $dolor->getKey(), 'intensidad' => 1]);
        $antes = [];
        foreach (self::TABLAS as $tabla => [$pk]) {
            $antes[$tabla] = ColumnasRelacionadas::consulta(DB::connection(), $tabla)->orderBy($pk)->get()->all();
        }
        $tablas = count(Schema::getTables());
        $m = require database_path('migrations/2026_11_20_000004_separate_optional_care_residents.php');
        $m->down();
        foreach (self::TABLAS as $tabla => [$pk,$directa]) {
            self::assertTrue(Schema::hasColumn($tabla, 'cod_residente'));
            self::assertFalse(Schema::hasTable($directa));
            self::assertSame(0, DB::table($tabla)->where('cod_residente', '<>', $d['r']->getKey())->count());
        }
        $m->up();
        foreach (self::TABLAS as $tabla => [$pk]) {
            self::assertEquals($antes[$tabla], ColumnasRelacionadas::consulta(DB::connection(), $tabla)->orderBy($pk)->get()->all());
        }
        self::assertSame($tablas, count(Schema::getTables()));
        self::assertSame($dolor->getKey(), $reevaluacion->fresh()->origen->getKey());
    }

    public function test_postgres_impide_la_doble_fuente_en_dos_transacciones_incluso_repeatable_read(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            self::markTestSkipped('La concurrencia se comprueba en PostgreSQL desechable.');
        }
        $d = $this->escenario();
        config(['database.connections.fn5_contexto_directo' => DB::connection()->getConfig()]);
        $paralela = DB::connection('fn5_contexto_directo');
        try {
            foreach (['READ COMMITTED', 'REPEATABLE READ'] as $i => $aislamiento) {
                $codigo = 'CONTEXTO_CARRERA_'.$i;
                $paralela->beginTransaction();
                $paralela->statement('SET TRANSACTION ISOLATION LEVEL '.$aislamiento);
                $paralela->table('residentes_mediciones_clinicas')->insert(['cod_registro_medicion' => $codigo, 'cod_residente' => $d['r']->getKey()]);
                // Esta conexión no ve la asociación aún no confirmada de la otra transacción.
                DB::table('mediciones_clinicas')->insert(['cod_registro_medicion' => $codigo, 'tipo_evento' => 'SIGNOS', 'cod_personal' => $d['p']->getKey(), 'cod_atencion' => $d['at']->getKey(), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
                try {
                    $paralela->commit();
                    self::fail('La FK nativa debe rechazar una asociación al registro con atención.');
                } catch (\PDOException $e) {
                    self::assertSame('23503', $e->errorInfo[0]);
                    // PostgreSQL abortó COMMIT; sincroniza el contador de la conexión Laravel.
                    $paralela->rollBack();
                }
                self::assertSame(0, DB::table('residentes_mediciones_clinicas')->where('cod_registro_medicion', $codigo)->count());
                self::assertSame($d['r']->getKey(), SignoVital::whereKey($codigo)->sole()->cod_residente);
            }
        } finally {
            while ($paralela->transactionLevel() > 0) {
                $paralela->rollBack();
            }
            DB::purge('fn5_contexto_directo');
        }
    }

    public function test_postgres_rechaza_un_contrato_ajeno_sin_eliminar_sus_restricciones(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            self::markTestSkipped('El manifiesto instalado se contrasta en PostgreSQL desechable.');
        }
        $m = require database_path('migrations/2026_11_20_000004_separate_optional_care_residents.php');
        $m->down();
        DB::statement("ALTER TABLE mediciones_clinicas ADD CONSTRAINT fn5_contrato_ajeno CHECK (cod_residente <> 'RES_NO_PERMITIDO')");
        try {
            try {
                $m->up();
                self::fail('Una restricción ajena debe detener la transición.');
            } catch (\RuntimeException $e) {
                self::assertStringContainsString('contrato PostgreSQL clínico cambió', $e->getMessage());
            }
            self::assertTrue(Schema::hasColumn('mediciones_clinicas', 'cod_residente'));
            self::assertFalse(Schema::hasTable('residentes_mediciones_clinicas'));
            self::assertSame(1,(int) DB::selectOne("SELECT count(*) AS n FROM pg_constraint WHERE conname='fn5_contrato_ajeno'")->n);
        } finally {
            DB::statement('ALTER TABLE mediciones_clinicas DROP CONSTRAINT fn5_contrato_ajeno');
            $m->up();
        }
    }
}
