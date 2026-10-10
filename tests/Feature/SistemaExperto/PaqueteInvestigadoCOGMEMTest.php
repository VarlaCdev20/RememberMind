<?php

namespace Tests\Feature\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Acciones\CargarPaqueteInvestigadoCOGMEM;
use App\Backend\Modulos\SistemaExperto\Conocimiento\CargadorConocimiento;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteInvestigadoCOGMEM as P;
use App\Backend\Modulos\SistemaExperto\Servicios\LecturaResultadosExperto;
use App\Backend\Modulos\SistemaExperto\Servicios\PreparacionFuentesCOGMEM;
use App\Frontend\Livewire\Medico\Clinica\ResultadosExpertoResidente;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\SistemaExperto\FixtureLecturaExperta as F;
use Tests\Support\SistemaExperto\PruebaConBaseDesechable;

final class PaqueteInvestigadoCOGMEMTest extends PruebaConBaseDesechable
{
    private function escenario(): array
    {
        [$medico, $residente] = F::crear();
        $autor = User::create(['cod_usuario' => 'USR_MEM_INV_TEST', 'correo' => 'mem-inv@example.test',
            'contrasena' => bin2hex(random_bytes(24)), 'estado' => 'ACTIVO']);
        $autor->assignRole('SUPERADMINISTRADOR');

        return [$autor, $medico, $residente];
    }

    public function test_carga_propuesta_normalizada_inactiva_pendiente_sin_cambiar_historia(): void
    {
        [$autor] = $this->escenario();
        $historia = DB::table('evaluaciones_expertas')->get()->toJson();
        $resultado = (new CargarPaqueteInvestigadoCOGMEM)->ejecutar($autor);
        $this->assertFalse($resultado['activacion_clinica']);
        $this->assertDatabaseHas('versiones_modelo_experto', ['cod_version_modelo' => P::VERSION,
            'estado' => 'INACTIVO', 'fecha_hora_vigencia' => null]);
        $t = (new CargadorConocimiento)->instantanea(P::VERSION);
        $this->assertCount(3, $t['nodos_semanticos']);
        $this->assertCount(2, $t['variables_expertas']);
        $this->assertCount(6, $t['mapeos_valores_fuente']);
        $this->assertEmpty($t['reglas_expertas']);
        foreach ($t as $filas) {
            foreach ($filas as $fila) {
                $this->assertSame('INACTIVO', $fila['estado']);
                if (isset($fila['estado_aprobacion'])) {
                    $this->assertSame('PENDIENTE', $fila['estado_aprobacion']);
                }
            }
        }
        $this->assertSame($historia, DB::table('evaluaciones_expertas')->get()->toJson());
        $this->assertDatabaseCount('controles_cognitivos', 0);
        $this->assertDatabaseHas('activity_log', ['event' => 'paquete_investigado_registrado', 'causer_id' => $autor->getKey()]);
    }

    public function test_idempotencia_y_rechazo_de_version_alterada_sin_sobrescribir(): void
    {
        [$autor] = $this->escenario();
        $carga = new CargarPaqueteInvestigadoCOGMEM;
        $carga->ejecutar($autor);
        $antes = (new CargadorConocimiento)->instantanea(P::VERSION);
        $auditoria = DB::table('activity_log')->count();
        $this->assertTrue($carga->ejecutar($autor)['cargado_previamente']);
        $this->assertSame($antes, (new CargadorConocimiento)->instantanea(P::VERSION));
        $this->assertDatabaseCount('activity_log', $auditoria);
        DB::table('mapeos_valores_fuente')->where('cod_mapeo_valor_fuente', $antes['mapeos_valores_fuente'][0]['cod_mapeo_valor_fuente'])
            ->update(['estado_aprobacion' => 'APROBADO']);
        try {
            $carga->ejecutar($autor);
            $this->fail('Debe rechazar una propuesta distinta.');
        } catch (DomainException) {
            $this->assertDatabaseCount('activity_log', $auditoria);
            $this->assertDatabaseHas('mapeos_valores_fuente', ['cod_mapeo_valor_fuente' => $antes['mapeos_valores_fuente'][0]['cod_mapeo_valor_fuente'],
                'estado_aprobacion' => 'APROBADO']);
        }
    }

    public function test_carga_rechaza_cuenta_clinica_o_inactiva_sin_efectos(): void
    {
        [$autor, $medico] = $this->escenario();
        DB::table('usuarios')->where('cod_usuario', $autor->getKey())->update(['estado' => 'INACTIVO']);
        foreach ([$autor, $medico] as $noAutorizado) {
            try {
                (new CargarPaqueteInvestigadoCOGMEM)->ejecutar($noAutorizado);
                $this->fail('Debe rechazar la carga.');
            } catch (DomainException) {
                $this->assertDatabaseMissing('versiones_modelo_experto', ['cod_version_modelo' => P::VERSION]);
            }
        }
    }

    public function test_preparacion_respeta_corte_estado_y_residente_sin_emitir_evaluacion(): void
    {
        [$autor, $medico, $residente] = $this->escenario();
        (new CargarPaqueteInvestigadoCOGMEM)->ejecutar($autor);
        $otro = F::residenteAdicional('MEM_AJENO');
        foreach (['VIGENTE' => [$residente->getKey(), now()->subHour(), 'VIGENTE'],
            'ANULADO' => [$residente->getKey(), now()->subHour(), 'ANULADO'],
            'FUTURO' => [$residente->getKey(), now()->addDay(), 'VIGENTE'],
            'AJENO' => [$otro->getKey(), now()->subHour(), 'VIGENTE']] as $id => [$r, $fecha, $estado]) {
            DB::table('controles_cognitivos')->insert(['cod_control_cognitivo' => 'CC_INV_'.$id,
                'cod_residente' => $r, 'cod_personal' => 'PER_MED', 'fecha_hora' => $fecha, 'estado' => $estado,
                'memoria_reciente' => 'CONSERVADA']);
        }
        DB::table('instrumentos')->insert(['cod_instrumento' => 'INS_INV_TEST', 'codigo' => 'INST-SINTETICO',
            'nombre' => 'Instrumento funcional sintético', 'tipo' => 'FUNCIONAL', 'version' => '1.0', 'estado' => 'ACTIVO']);
        foreach (['VIGENTE' => [$residente->getKey(), now()->subHour(), 'VIGENTE'],
            'ANULADO' => [$residente->getKey(), now()->subHour(), 'ANULADO'],
            'FUTURO' => [$residente->getKey(), now()->addDay(), 'VIGENTE'],
            'AJENO' => [$otro->getKey(), now()->subHour(), 'VIGENTE']] as $id => [$r, $fecha, $estado]) {
            DB::table('aplicaciones_instrumento')->insert(['cod_aplicacion' => 'APP_INV_'.$id,
                'cod_instrumento' => 'INS_INV_TEST', 'cod_residente' => $r, 'cod_personal' => 'PER_MED',
                'fecha_hora' => $fecha, 'estado' => $estado, 'puntaje_total' => 100]);
        }
        $antes = DB::table('evaluaciones_expertas')->get()->toJson();
        $p = (new PreparacionFuentesCOGMEM)->consultar($medico, $residente);
        $this->assertSame(1, $p['controles']);
        $this->assertSame(1, $p['aplicaciones']);
        $this->assertSame(['Instrumento funcional sintético · 1.0'], $p['instrumentos']);
        $this->assertFalse($p['puede_emitir_resultado']);
        $this->assertNull($p['resultado']);
        $this->assertSame('PREPARACION_NO_INFERENCIA', $p['estado']);
        $this->assertCount(3, $p['pendientes']);
        $this->assertSame($antes, DB::table('evaluaciones_expertas')->get()->toJson());
        DB::table('evaluaciones_expertas')->update(['origen_activacion' => 'PRUEBA_TECNICA']);
        $this->actingAs($medico);
        $d = app(LecturaResultadosExperto::class)->consultar($medico, $residente, null, 'COG-MEM');
        $this->assertSame($p, $d['preparacion_memoria']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residente])
            ->assertSee('Preparación de la valoración de memoria')->assertSee('Controles cognitivos vigentes hasta hoy: 1')
            ->assertSee('pendiente de validación profesional')->assertDontSee('Sin dificultad evidenciada')
            ->call('seleccionarCriterio', 'COG-ATE')->assertDontSee('Preparación de la valoración de memoria');
    }

    public function test_sin_permiso_no_consulta_aplicaciones_y_cuenta_inactiva_no_lee(): void
    {
        [$autor, $medico, $residente] = $this->escenario();
        (new CargarPaqueteInvestigadoCOGMEM)->ejecutar($autor);
        $medico->roles->first()->revokePermissionTo('aplicaciones_instrumento.ver');
        $medico->unsetRelation('roles');
        DB::enableQueryLog();
        $p = (new PreparacionFuentesCOGMEM)->consultar($medico, $residente);
        $consultas = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertNull($p['aplicaciones']);
        $this->assertEmpty($p['instrumentos']);
        foreach ($consultas as $consulta) {
            $this->assertStringNotContainsString('aplicaciones_instrumento', $consulta['query']);
        }
        DB::table('usuarios')->where('cod_usuario', $medico->getKey())->update(['estado' => 'INACTIVO']);
        $medico->refresh();
        $this->expectException(AuthorizationException::class);
        (new PreparacionFuentesCOGMEM)->consultar($medico, $residente);
    }
}
