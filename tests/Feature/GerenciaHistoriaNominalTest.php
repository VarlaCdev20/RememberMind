<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\ConservarEventoAsignacion;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarHorariosPersonal;
use App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios;
use App\Modulos\Social\Aplicacion\Acciones\GestionarRedApoyo;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class GerenciaHistoriaNominalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_versiones_recuperan_antes_despues_sin_inventar_autor_historico(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $accion = app(GestionarRedApoyo::class);
        $contacto = $accion->guardarContacto($actor, ['nombres' => 'Persona de prueba', 'apellido_paterno' => 'Sintética']);
        $v = $accion->vincular($actor, $residente->getKey(), $contacto->getKey(), ['parentesco' => 'Hija', 'responsable_principal' => false, 'contacto_emergencia' => false]);
        $accion->cambiarVigencia($actor, $residente->getKey(), $v->getKey(), 'INACTIVO', 'Motivo de cambio registrado');
        $historia = DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $v->getKey())->orderBy('numero_version')->get();
        $this->assertCount(2, $historia);
        $this->assertSame('ACTIVO', $historia[0]->estado);
        $this->assertSame('INACTIVO', $historia[1]->estado);
        $this->assertSame($historia[0]->cod_version_vinculo, $historia[1]->cod_version_anterior);
        $this->assertSame($actor->getKey(), $historia[1]->cod_usuario_acto);
        $this->assertSame('Motivo de cambio registrado', $historia[1]->motivo);
        $otro = $accion->guardarContacto($actor, ['nombres' => 'Contacto anterior', 'apellido_paterno' => 'Sintético']);
        $antiguo = ResidenteContacto::create(['cod_residente_contacto' => 'QA_VINCULO_ANTERIOR', 'cod_residente' => $residente->getKey(), 'cod_contacto' => $otro->getKey(), 'parentesco' => 'Hijo', 'estado' => 'ACTIVO', 'responsable_principal' => false, 'contacto_emergencia' => false, 'autoriza_informacion' => false, 'autoriza_salida' => false]);
        $accion->cambiarVigencia($actor, $residente->getKey(), $antiguo->getKey(), 'INACTIVO', 'Corrección de historia desconocida');
        $base = DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $antiguo->getKey())->orderBy('numero_version')->first();
        $this->assertNull($base->cod_usuario_acto);
        $this->assertNull($base->fecha_hora_acto);
        $this->assertNotNull($base->fecha_hora_conservacion);
    }

    public function test_eventos_de_ocurrencia_recuperan_fecha_y_no_reescriben_jornadas_anteriores(): void
    {
        request()->setMethod('POST');
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $persona = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Sintética']);
        $persona->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $entrada = ['usuarioId' => $persona->getKey(), 'f_cod_area' => $area->getKey(), 'f_cod_turno' => $turno->getKey(), 'f_dias_semana' => ['LUNES', 'MARTES'], 'f_fecha_inicio' => '2026-10-26', 'f_tipo_asignacion' => 'TEMPORAL', 'f_observaciones' => 'Motivo original', 'modoEdicion' => false];
        app(GestionarHorariosPersonal::class)->guardar($entrada);
        $filas = AsignacionPersonal::with('jornada')->where('cod_personal', $persona->personal->getKey())->get()->sortBy(fn ($a) => $a->jornada->fecha_jornada);
        $this->assertCount(2, $filas);
        $this->assertSame('Motivo original', $filas->first()->observacion);
        $segunda = $filas->last();
        app(GestionarHorariosPersonal::class)->confirmarFinalizar(['usuarioId' => $persona->getKey(), 'codAsignacionFinalizar' => $segunda->getKey(), 'f_fecha_fin_finalizar' => '2026-10-27']);
        $this->assertSame('ACTIVO', $filas->first()->fresh()->estado);
        $this->assertSame('INACTIVO', $segunda->fresh()->estado);
        $eventos = DB::table('eventos_asignacion_personal')->where('cod_asignacion_personal', $segunda->getKey())->orderBy('numero_evento')->get();
        $this->assertCount(2, $eventos);
        $this->assertSame('ALTA', $eventos[0]->tipo_evento);
        $this->assertSame('FINALIZACION', $eventos[1]->tipo_evento);
        $this->assertSame($eventos[0]->cod_evento_asignacion, $eventos[1]->cod_evento_anterior);
        $this->assertSame('2026-10-26 23:59:59', substr($eventos[1]->fecha_hora_efecto, 0, 19));
        $this->assertSame($actor->getKey(), $eventos[1]->cod_usuario_registro);
        $detalle = Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $persona->getKey()])
            ->call('verDetalle', $segunda->getKey());
        $this->assertSame(Carbon::parse($eventos[0]->fecha_hora_registro)->format('d/m/Y H:i'), $detalle->get('detalleAsignacion')['created_at']);
    }

    public function test_inventario_y_reversion_sin_datos_deja_modelo_original(): void
    {
        $antes = Schema::getTableListing();
        $this->assertCount(118, $antes);
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->down();
        $this->assertCount(116, Schema::getTableListing());
    }

    public function test_historia_guardada_no_se_sobrescribe_ni_se_retiра_con_rollback(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $a = app(GestionarRedApoyo::class);
        $contacto = $a->guardarContacto($actor, ['nombres' => 'Contacto', 'apellido_paterno' => 'Prueba']);
        $v = $a->vincular($actor, $residente->getKey(), $contacto->getKey(), ['parentesco' => 'Hija', 'responsable_principal' => false, 'contacto_emergencia' => false]);
        $this->expectException(QueryException::class);
        try {
            DB::transaction(fn () => DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $v->getKey())->update(['parentesco' => 'Alterado']));
        } finally {
            $this->assertSame('Hija', DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $v->getKey())->value('parentesco'));
            try {
                (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->down();
                $this->fail('No debe retirar historia.');
            } catch (\LogicException $error) {
                $this->assertStringContainsString('historia poblada', $error->getMessage());
            }
        }
    }

    public function test_fk_impide_encadenar_version_de_otro_vinculo_y_fallo_revierte_correccion(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $a = app(GestionarRedApoyo::class);
        $c1 = $a->guardarContacto($actor, ['nombres' => 'Primera persona', 'apellido_paterno' => 'Prueba']);
        $c2 = $a->guardarContacto($actor, ['nombres' => 'Segunda persona', 'apellido_paterno' => 'Prueba']);
        $datos = ['parentesco' => 'Hija', 'responsable_principal' => false, 'contacto_emergencia' => false];
        $v1 = $a->vincular($actor, $residente->getKey(), $c1->getKey(), $datos);
        $v2 = $a->vincular($actor, $residente->getKey(), $c2->getKey(), $datos);
        $original = (array) DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $v1->getKey())->first();
        $ajena = DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $v2->getKey())->value('cod_version_vinculo');
        try {
            DB::transaction(fn () => DB::table('versiones_vinculo_contacto')->insert([...$original, 'cod_version_vinculo' => 'QA_VERSION_CRUZADA', 'numero_version' => 2, 'cod_version_anterior' => $ajena]));
            $this->fail('La FK debe rechazar otro vínculo.');
        } catch (QueryException $error) {
            $this->assertNotEmpty($error->getCode());
        }
        $this->assertDatabaseCount('versiones_vinculo_contacto', 2);
        try {
            DB::transaction(function () use ($a, $actor, $residente, $v1) {
                $a->cambiarVigencia($actor, $residente->getKey(), $v1->getKey(), 'INACTIVO', 'Cambio que debe revertirse');
                throw new \RuntimeException('Fallo posterior de ensayo');
            });
        } catch (\RuntimeException $error) {
            $this->assertSame('Fallo posterior de ensayo', $error->getMessage());
        }
        $this->assertSame('ACTIVO', $v1->fresh()->estado);
        $this->assertDatabaseCount('versiones_vinculo_contacto', 2);
    }

    public function test_sql_rechaza_autoenlace_salto_y_rama_en_ambas_historias(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $accion = app(GestionarRedApoyo::class);
        $contacto = $accion->guardarContacto($actor, ['nombres' => 'Contacto', 'apellido_paterno' => 'Prueba']);
        $vinculo = $accion->vincular($actor, $residente->getKey(), $contacto->getKey(), ['parentesco' => 'Hija', 'responsable_principal' => false, 'contacto_emergencia' => false]);
        $accion->cambiarVigencia($actor, $residente->getKey(), $vinculo->getKey(), 'INACTIVO', 'Cambio válido conservado');
        $persona = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Prueba']);
        $persona->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_cierre' => '16:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'ABIERTA']);
        $asignacion = AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $persona->personal->getKey(), 'cod_area' => $area->getKey(), 'estado' => 'ACTIVO']);
        $eventos = app(ConservarEventoAsignacion::class);
        $eventos->conservar($asignacion, $actor, 'ALTA', null, now(), 'Alta de ensayo');
        $asignacion->update(['estado' => 'INACTIVO']);
        $eventos->conservar($asignacion, $actor, 'FINALIZACION', 'ACTIVO', now(), 'Cierre de ensayo');
        foreach ([['versiones_vinculo_contacto', 'cod_version_vinculo', 'cod_version_anterior', 'numero_version'],
            ['eventos_asignacion_personal', 'cod_evento_asignacion', 'cod_evento_anterior', 'numero_evento']] as [$tabla, $pk, $previo, $numero]) {
            $base = (array) DB::table($tabla)->where($numero, 1)->first();
            foreach (['autoenlace', 'salto', 'rama'] as $modo) {
                $clave = 'QA_'.strtoupper($modo);
                $fila = [...$base, $pk => $clave, $numero => $modo === 'salto' ? 4 : 3,
                    $previo => $modo === 'autoenlace' ? $clave : $base[$pk]];
                try {
                    DB::transaction(fn () => DB::table($tabla)->insert($fila));
                    $this->fail('Debe rechazarse '.$modo.' en '.$tabla);
                } catch (QueryException $error) {
                    $this->assertStringContainsString('secuencia', $error->getMessage());
                }
                $this->assertDatabaseCount($tabla, 2);
            }
        }
    }

    public function test_detalle_anterior_no_infiere_fecha_ni_autor_desde_jornada(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $persona = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Prueba']);
        $persona->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_cierre' => '16:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2025-01-01', 'estado' => 'ABIERTA']);
        $asignacion = AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $persona->personal->getKey(), 'cod_area' => $area->getKey(), 'estado' => 'ACTIVO']);
        $detalle = Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $persona->getKey()])
            ->call('verDetalle', $asignacion->getKey())->assertSee('Autor y fecha original de registro no constan.');
        $this->assertNull($detalle->get('detalleAsignacion')['creado_por']);
        $this->assertNull($detalle->get('detalleAsignacion')['created_at']);
    }
}
