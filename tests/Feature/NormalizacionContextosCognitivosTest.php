<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas;
use App\Compartido\Infraestructura\Persistencia\ContextosResidente;
use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Instrumento;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class NormalizacionContextosCognitivosTest extends TestCase
{
    use EsquemaSinTransaccion;

    private const TABLAS = ['aplicaciones_instrumento', 'controles_cognitivos', 'controles_funcionales_iadl'];

    private function escenario(): array
    {
        $u = User::factory()->create();
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Autor', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'FN5-COGNITIVO', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $r = Residente::factory()->create();
        $otro = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Valoración sintética', 'estado' => 'ACTIVA']);
        $at = Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'cod_area' => $area->getKey(), 'fecha_hora' => now(), 'tipo_atencion' => 'VALORACION', 'estado' => 'VIGENTE']);
        $instrumento = Instrumento::create(['codigo' => 'FN5-ARTIFICIAL', 'nombre' => 'Instrumento artificial', 'tipo' => 'TECNICO', 'version' => '1', 'estado' => 'ACTIVO']);
        $base = ['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'fecha_hora' => now()];
        $registros = [];
        foreach ([null, $at->getKey()] as $atencion) {
            $registros[] = AplicacionInstrumento::create($base + ['cod_atencion' => $atencion, 'cod_instrumento' => $instrumento->getKey(), 'puntaje_total' => 0, 'puntaje_maximo' => 10, 'estado' => 'COMPLETA']);
            $registros[] = ControlCognitivo::create($base + ['cod_atencion' => $atencion, 'memoria_reciente' => 'OBSERVACION_SINTETICA', 'estado' => 'VIGENTE']);
            $registros[] = ControlFuncionalIadl::create($base + ['cod_atencion' => $atencion, 'cod_usuario_registro' => $u->getKey(), 'registrado_en' => now(),
                'actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA', 'desempeno' => 'CON_APOYO', 'tipo_ayuda' => 'RECORDATORIO', 'causa_ayuda' => 'NO_DETERMINADA',
                'nivel_previo' => 'DESCONOCIDO', 'fuente' => 'OBSERVACION_DIRECTA']);
        }

        return compact('u', 'p', 'r', 'otro', 'at', 'base', 'registros');
    }

    public function test_modelos_conservan_contexto_proyecciones_originales_y_reglas_de_inmutabilidad(): void
    {
        $d = $this->escenario();
        foreach ($d['registros'] as $registro) {
            $actual = $registro->fresh();
            self::assertSame($d['r']->getKey(), $actual->cod_residente);
            self::assertSame($d['r']->getKey(), $actual->getRawOriginal('cod_residente'));
            self::assertSame($d['r']->getKey(), $actual->residente->getKey());
            self::assertTrue($actual::whereKey($actual->getKey())->where('cod_residente', $d['r']->getKey())->exists());
            self::assertFalse($actual::whereKey($actual->getKey())->where('cod_residente', $d['otro']->getKey())->exists());
            self::assertArrayNotHasKey('cod_contexto_directo', $actual->toArray());
            self::assertSame($d['r']->getKey(), $actual::whereKey($actual->getKey())->get([$actual->getKeyName(), 'cod_residente'])->sole()->getRawOriginal('cod_residente'));
        }
        foreach (self::TABLAS as $tabla) {
            [$pk,$directa] = ContextosResidente::CONTRATOS[$tabla];
            self::assertFalse(Schema::hasColumn($tabla, 'cod_residente'));
            self::assertSame(1, DB::table($directa)->count());
            self::assertTrue(ContextosResidente::estructuraDisponible(DB::connection(), $tabla));
        }
        $app = $d['registros'][0];
        $app->update(['estado' => 'ANULADA']);
        self::assertSame('ANULADA', $app->fresh()->estado);
        try {
            $app->update(['puntaje_total' => 2]);
            self::fail('La aplicación original no se sobrescribe.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('aplicacion', $e->errors());
        }
        try {
            $d['registros'][2]->update(['observacion' => 'Sobrescritura']);
            self::fail('IADL es inmutable.');
        } catch (\LogicException $e) {
            self::assertStringContainsString('inmutable', $e->getMessage());
        }
        self::assertSame('0.00', $app->fresh()->puntaje_total);
        self::assertNull($d['registros'][2]->fresh()->observacion);
    }

    public function test_fuente_cruzada_rechazada_sin_registro_ni_asociacion_parcial(): void
    {
        $d = $this->escenario();
        foreach ([$d['registros'][0], $d['registros'][1], $d['registros'][2]] as $i => $original) {
            $datos = array_diff_key($original->fresh()->getAttributes(), array_fill_keys([$original->getKeyName(), 'cod_contexto_directo'], true));
            $datos['cod_residente'] = $d['otro']->getKey();
            $datos['cod_atencion'] = $d['at']->getKey();
            $datos[$original->getKeyName()] = 'CRUCE_COGNITIVO_'.$i;
            try {
                $original::create($datos);
                self::fail('El contexto explícito debe corresponder a la atención.');
            } catch (ValidationException $e) {
                self::assertArrayHasKey('cod_residente', $e->errors());
            }
            [$pk,$directa] = ContextosResidente::CONTRATOS[$original->getTable()];
            self::assertSame(0, DB::table($original->getTable())->where($pk, $datos[$pk])->count());
            self::assertSame(0, DB::table($directa)->where($pk, $datos[$pk])->count());
        }
    }

    public function test_reversion_poblada_conserva_todos_los_campos_y_las_guardas_originales(): void
    {
        $this->escenario();
        $antes = [];
        $columnas = [];
        foreach (self::TABLAS as $tabla) {
            [$pk] = ContextosResidente::CONTRATOS[$tabla];
            $columnas[$tabla] = array_values(array_diff(Schema::getColumnListing($tabla), ['cod_contexto_directo']));
            $antes[$tabla] = ColumnasRelacionadas::consulta(DB::connection(), $tabla)->select([...$columnas[$tabla], 'cod_residente'])->orderBy($pk)->get()->all();
        }
        $m = require database_path('migrations/2026_11_20_000005_separate_optional_cognitive_residents.php');
        $m->down();
        foreach (self::TABLAS as $tabla) {
            [$pk,$directa] = ContextosResidente::CONTRATOS[$tabla];
            self::assertFalse(Schema::hasTable($directa));
            self::assertEquals($antes[$tabla], DB::table($tabla)->select([...$columnas[$tabla], 'cod_residente'])->orderBy($pk)->get()->all());
        }
        $m->up();
        foreach (self::TABLAS as $tabla) {
            [$pk] = ContextosResidente::CONTRATOS[$tabla];
            self::assertEquals($antes[$tabla], ColumnasRelacionadas::consulta(DB::connection(), $tabla)->select([...$columnas[$tabla], 'cod_residente'])->orderBy($pk)->get()->all());
        }
        self::assertSame(2, ControlFuncionalIadl::count());
    }

    public function test_postgres_bloquea_registros_sin_intentar_bloquear_los_lados_nullable_del_contexto(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            self::markTestSkipped('El bloqueo relacional se comprueba en PostgreSQL.');
        }
        $d = $this->escenario();
        $registros = [...$d['registros'],
            SignoVital::create($d['base'] + ['temperatura' => 36.5, 'estado' => 'VIGENTE']),
            SignoVital::create($d['base'] + ['cod_atencion' => $d['at']->getKey(), 'temperatura' => 36.6, 'estado' => 'VIGENTE']),
        ];
        DB::transaction(function () use ($d, $registros): void {
            foreach ($registros as $registro) {
                self::assertSame($d['r']->getKey(), $registro::whereKey($registro->getKey())->sharedLock()->sole()->getRawOriginal('cod_residente'));
                self::assertSame($d['r']->getKey(), $registro::whereKey($registro->getKey())->lockForUpdate()->sole()->getRawOriginal('cod_residente'));
            }
        });
    }
}
