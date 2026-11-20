<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Compartido\Dominio\Calendario\SeleccionSemanal as S;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\IntervencionCuidado;
use App\Models\Medicamento;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Prescripcion;
use App\Models\ProgramacionCuidado;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class NormalizacionSeleccionSemanalTest extends TestCase
{
    use EsquemaSinTransaccion;

    private function escenario(): array
    {
        $u = User::factory()->create();
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'FN5-SEMANA', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $r = Residente::factory()->create();
        $a = Area::create(['nombre' => 'Consulta sintética', 'estado' => 'ACTIVA']);
        $at = Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'cod_area' => $a->getKey(), 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        $m = Medicamento::create(['nombre_generico' => 'Catálogo sintético', 'control_especial' => false, 'estado' => 'ACTIVO']);
        $rx = Prescripcion::create(['cod_atencion' => $at->getKey(), 'cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'cod_medicamento' => $m->getKey(), 'via_administracion' => 'ORAL', 'segun_necesidad' => false, 'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
        $plan = PlanCuidado::create(['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'cod_area' => $a->getKey(), 'tipo_plan' => 'CUIDADO', 'nombre' => 'Plan', 'objetivo_general' => 'Cuidado registrado', 'fecha_hora_apertura' => now(), 'estado' => 'ACTIVO']);
        $i = IntervencionCuidado::create(['cod_plan' => $plan->getKey(), 'nombre' => 'Intervención', 'descripcion' => 'Registro sintético', 'estado' => 'ACTIVA']);
        $h = HorarioPrescripcion::create(['cod_prescripcion' => $rx->getKey(), 'hora_programada' => '08:00', 'dias_semana' => 'LUNES,MIÉRCOLES', 'estado' => 'ACTIVO']);
        $c = ProgramacionCuidado::create(['cod_intervencion' => $i->getKey(), 'frecuencia' => 'SEMANAL', 'dias_semana' => '3,1', 'fecha_activacion' => today(), 'estado' => 'ACTIVA']);

        return compact('rx', 'i', 'h', 'c');
    }

    public function test_seleccion_relacional_compartida_conserva_los_dias_y_el_defecto_todos(): void
    {
        $d = $this->escenario();
        foreach (['horarios_prescripcion', 'programaciones_cuidado'] as $t) {
            self::assertFalse(Schema::hasColumn($t, 'dias_semana'));
        }
        self::assertSame($d['h']->cod_patron_semanal, $d['c']->cod_patron_semanal);
        self::assertSame('LUNES,MIERCOLES', $d['h']->fresh()->dias_semana);
        self::assertSame([1, 3], $d['h']->fresh()->patronSemanal->dias->pluck('dia_semana')->all());
        $cargado = $d['h']->fresh();
        self::assertSame('LUNES,MIERCOLES', $cargado->dias_semana);
        $cargado->cod_patron_semanal = S::identidad([2]);
        self::assertSame('MARTES', $cargado->dias_semana);
        foreach (['horarios_prescripcion', 'programaciones_cuidado'] as $tabla) {
            self::assertTrue(collect(Schema::getIndexes($tabla))->contains(fn ($indice) => $indice['columns'] === ['cod_patron_semanal']));
        }
        self::assertSame(129, DB::table('patrones_semanales')->count());
        self::assertSame(448, DB::table('dias_patron_semanal')->count());
        $d['c']->update(['dias_semana' => null]);
        self::assertNull($d['c']->fresh()->dias_semana);
        self::assertSame(S::TODOS, $d['c']->fresh()->cod_patron_semanal);
        $todos = HorarioPrescripcion::create(['cod_prescripcion' => $d['rx']->getKey(), 'hora_programada' => '09:00', 'estado' => 'ACTIVO']);
        self::assertNull($todos->fresh()->dias_semana);
    }

    public function test_el_catalogo_no_permite_cambiar_los_dias_de_ordenes_ya_guardadas(): void
    {
        $d = $this->escenario();
        foreach ([
            fn () => DB::table('dias_patron_semanal')->where('cod_patron_semanal', $d['h']->cod_patron_semanal)->delete(),
            fn () => DB::table('patrones_semanales')->where('cod_patron_semanal', S::TODOS)->update(['todos_los_dias' => false]),
            fn () => DB::table('patrones_semanales')->insert(['cod_patron_semanal' => 'AJENO', 'todos_los_dias' => true]),
        ] as $op) {
            try {
                DB::transaction($op);
                self::fail('Un cambio de catálogo no puede alterar los horarios históricos.');
            } catch (QueryException $e) {
                self::assertContains($e->getCode(), ['23000', '23514']);
            }
        }
        self::assertSame('LUNES,MIERCOLES', $d['h']->fresh()->dias_semana);
        self::assertSame(448, DB::table('dias_patron_semanal')->count());
        try {
            DB::transaction(fn () => HorarioPrescripcion::create(['cod_prescripcion' => $d['rx']->getKey(), 'hora_programada' => '08:00', 'dias_semana' => '3,1', 'estado' => 'ACTIVO']));
            self::fail('El mismo conjunto activo no debe duplicarse por usar otro texto.');
        } catch (QueryException $e) {
            self::assertContains($e->getCode(), ['23000', '23505']);
        }
        self::assertSame(1, HorarioPrescripcion::count());
    }

    public function test_reversion_y_reaplicacion_conservan_selecciones_y_datos_del_horario(): void
    {
        $d = $this->escenario();
        $m = require database_path('migrations/2026_11_20_000002_normalize_weekday_selections.php');
        $antes = DB::table('horarios_prescripcion')->get()->toArray();
        $m->down();
        self::assertFalse(Schema::hasTable('patrones_semanales'));
        self::assertSame('1,3', DB::table('horarios_prescripcion')->sole()->dias_semana);
        $m->up();
        self::assertEquals($antes, DB::table('horarios_prescripcion')->get()->toArray());
        self::assertSame('LUNES,MIERCOLES', $d['c']->fresh()->dias_semana);
    }

    public function test_una_seleccion_historica_desconocida_detiene_la_transicion_sin_perderla(): void
    {
        $this->escenario();
        $m = require database_path('migrations/2026_11_20_000002_normalize_weekday_selections.php');
        $m->down();
        DB::table('horarios_prescripcion')->update(['dias_semana' => 'DESCONOCIDO']);
        try {
            $m->up();
            self::fail('No se deben adivinar los días desconocidos.');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('no reconocido', $e->getMessage());
        }
        self::assertFalse(Schema::hasTable('patrones_semanales'));
        self::assertSame('DESCONOCIDO', DB::table('horarios_prescripcion')->sole()->dias_semana);
    }
}
