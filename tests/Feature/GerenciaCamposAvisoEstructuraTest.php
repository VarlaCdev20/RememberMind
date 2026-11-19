<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarHorariosPersonal;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GerenciaCamposAvisoEstructuraTest extends TestCase
{
    use RefreshDatabase;

    private array $original;
    private array $tablasOriginales;
    private $transicion;
    private User $afectado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeTime();
        config(['remembermind.avisos_planificacion_habilitados' => false]);
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(); $actor->assignRole('GERENTE');
        $this->actingAs($actor); request()->setMethod('POST');
        $this->afectado = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Sintética']);
        $this->afectado->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_cierre' => '16:00', 'estado' => 'ACTIVO']);
        app(GestionarHorariosPersonal::class)->guardar(['usuarioId' => $this->afectado->getKey(), 'f_cod_area' => $area->getKey(),
            'f_cod_turno' => $turno->getKey(), 'f_dias_semana' => ['LUNES'], 'f_fecha_inicio' => '2026-11-02', 'f_tipo_asignacion' => 'REGULAR', 'modoEdicion' => false]);
        // Fixture del esquema anterior: la prueba ejercita la extensión histórica del aviso.
        $asignacion = \App\Models\AsignacionPersonal::sole();
        DB::table('eventos_asignacion_personal')->insert([
            'cod_evento_asignacion' => 'QA_EVENTO_ORIGINAL', 'cod_asignacion_personal' => $asignacion->getKey(),
            'numero_evento' => 1, 'cod_evento_anterior' => null, 'tipo_evento' => 'ALTA',
            'estado_anterior' => null, 'estado_nuevo' => $asignacion->estado,
            'fecha_hora_efecto' => now(), 'fecha_hora_registro' => now(),
            'cod_usuario_registro' => $actor->getKey(), 'motivo' => 'Acto anterior sintético',
        ]);
        $this->original = (array) DB::table('eventos_asignacion_personal')->sole();
        $this->tablasOriginales = Schema::getTableListing();
        $this->transicion = require base_path('database/propuestas/gerencia/2026_10_27_000003_campos_aviso_evento_asignacion.php');
        $this->transicion->up();
    }

    public function test_no_crea_tablas_y_reversion_poblada_del_acto_original_conserva_datos(): void
    {
        $this->assertEqualsCanonicalizing($this->tablasOriginales, Schema::getTableListing());
        $fila = (array) DB::table('eventos_asignacion_personal')->sole();
        $this->assertSame($this->original, array_intersect_key($fila, $this->original));
        foreach (array_diff_key($fila, $this->original) as $valor) $this->assertNull($valor);
        $this->transicion->down();
        $this->assertSame($this->original, (array) DB::table('eventos_asignacion_personal')->sole());
        $this->assertEqualsCanonicalizing($this->tablasOriginales, Schema::getTableListing());
        $this->rechazar(fn () => DB::table('eventos_asignacion_personal')->update(['motivo' => 'Reescritura prohibida']));
    }

    public function test_historico_desconocido_y_acto_original_no_se_pueden_reescribir(): void
    {
        $this->rechazar(fn () => DB::table('eventos_asignacion_personal')->update(['estado_anterior' => 'ACTIVO']));
        $this->rechazar(fn () => DB::table('eventos_asignacion_personal')->update(['estado_aviso_planificacion' => 'PENDIENTE', 'numero_intentos_aviso' => 0]));
        $this->rechazar(fn () => DB::table('eventos_asignacion_personal')->delete());
        $this->assertSame($this->original['motivo'], DB::table('eventos_asignacion_personal')->sole()->motivo);
    }

    public function test_solo_metadatos_tecnicos_pueden_progresar_y_fallo_no_acepta_null(): void
    {
        $nuevo = [...$this->original, 'cod_evento_asignacion' => 'QA_EVENTO_NUEVO', 'numero_evento' => 2,
            'cod_evento_anterior' => $this->original['cod_evento_asignacion'], 'cod_usuario_afectado_aviso' => $this->afectado->getKey(),
            'estado_aviso_planificacion' => 'PENDIENTE', 'numero_intentos_aviso' => 0];
        foreach ([['numero_intentos_aviso' => null], ['estado_aviso_planificacion' => null],
            ['cod_usuario_afectado_aviso' => 'CUENTA_NO_EXISTENTE'], ['fecha_hora_disponible_aviso' => now()]] as $parcial) {
            $this->rechazar(fn () => DB::table('eventos_asignacion_personal')->insert([...$nuevo, ...$parcial]));
        }
        DB::table('eventos_asignacion_personal')->insert($nuevo);
        $q = fn () => DB::table('eventos_asignacion_personal')->where('cod_evento_asignacion', 'QA_EVENTO_NUEVO');
        $fallo = ['estado_aviso_planificacion' => 'FALLIDO', 'numero_intentos_aviso' => 1, 'fecha_hora_ultimo_intento_aviso' => now(), 'categoria_fallo_aviso' => null];
        $this->rechazar(fn () => $q()->update($fallo));
        $q()->update([...$fallo, 'categoria_fallo_aviso' => 'FALLO_DISPONIBILIZACION']);
        $this->assertSame('FALLIDO', $q()->sole()->estado_aviso_planificacion);
        $this->rechazar(fn () => $q()->update(['cod_usuario_afectado_aviso' => null]));
        $q()->update(['estado_aviso_planificacion' => 'DISPONIBLE', 'numero_intentos_aviso' => 2,
            'fecha_hora_ultimo_intento_aviso' => now(), 'fecha_hora_disponible_aviso' => now(), 'categoria_fallo_aviso' => null]);
        $this->rechazar(fn () => $q()->update($fallo));
        $this->rechazar(fn () => $q()->update(['motivo' => 'Alteración prohibida']));
        try { $this->transicion->down(); $this->fail('No retirar seguimiento poblado.'); }
        catch (\LogicException) { $this->addToAssertionCount(1); }
        $this->assertSame('DISPONIBLE', $q()->sole()->estado_aviso_planificacion);
        $this->assertEqualsCanonicalizing($this->tablasOriginales, Schema::getTableListing());
    }

    private function rechazar(callable $accion): void
    {
        try { DB::transaction($accion); $this->fail('La restricción SQL debe impedir la operación.'); }
        catch (QueryException) { $this->addToAssertionCount(1); }
    }

    public function test_seguimiento_sin_cuenta_afectada_no_puede_desaparecer_por_null(): void
    {
        DB::table('eventos_asignacion_personal')->insert([...$this->original, 'cod_evento_asignacion' => 'QA_SIN_CUENTA',
            'numero_evento' => 2, 'cod_evento_anterior' => $this->original['cod_evento_asignacion'],
            'estado_aviso_planificacion' => 'PENDIENTE', 'numero_intentos_aviso' => 0]);
        $q = fn () => DB::table('eventos_asignacion_personal')->where('cod_evento_asignacion', 'QA_SIN_CUENTA');
        foreach (['PENDIENTE', 'FALLIDO'] as $estado) {
            if ($estado === 'FALLIDO') $q()->update(['estado_aviso_planificacion' => 'FALLIDO', 'numero_intentos_aviso' => 1,
                'fecha_hora_ultimo_intento_aviso' => now(), 'categoria_fallo_aviso' => 'FALLO_DISPONIBILIZACION']);
            $this->rechazar(fn () => $q()->update(['estado_aviso_planificacion' => null, 'numero_intentos_aviso' => null,
                'fecha_hora_ultimo_intento_aviso' => null, 'fecha_hora_disponible_aviso' => null, 'categoria_fallo_aviso' => null]));
            $this->assertSame($estado, $q()->sole()->estado_aviso_planificacion);
        }
    }
}
