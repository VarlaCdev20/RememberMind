<?php

namespace Tests\Feature\SistemaExperto;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Backend\Modulos\SistemaExperto\Conocimiento\InventarioExperto;
use App\Frontend\Livewire\Superadministrador\SistemaExperto\ConocimientoPanel;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Support\SistemaExperto\PruebaConBaseDesechable;

class ConocimientoPanelTest extends PruebaConBaseDesechable
{
    private function usuario(string $rol = 'SUPERADMINISTRADOR', string $estado = 'ACTIVO'): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $u = User::create(['cod_usuario' => 'USR_PANEL', 'correo' => 'inspector@example.test',
            'contrasena' => Hash::make(bin2hex(random_bytes(20))), 'estado' => $estado]);
        $u->assignRole($rol);

        return $u;
    }

    public function test_consulta_vacia_y_casos_sinteticos_no_escriben_en_bdd(): void
    {
        $u = $this->usuario();
        $this->actingAs($u)->get(route('admin.sistema-experto.index'))->assertOk()->assertSee('Aún no hay conocimiento cargado');
        Livewire::actingAs($u)->test(ConocimientoPanel::class)
            ->assertSee('DIFICULTAD_EVIDENCIADA')
            ->call('seleccionarCaso', 'mixto')->assertSee('HALLAZGOS_MIXTOS')
            ->call('seleccionarCaso', 'contexto')->assertSee('EV-CM-1')
            ->call('seleccionarCaso', 'cobertura')->assertSee('Inconsistencia de cobertura detectada');
        foreach (InventarioExperto::TABLAS as $tabla) {
            $this->assertDatabaseCount($tabla, 0);
        }
        $this->assertDatabaseCount('residentes', 0);
    }

    public function test_permiso_explicito_es_obligatorio_aun_con_bypass_global_de_lectura(): void
    {
        $u = $this->usuario();
        $u->roles->first()->revokePermissionTo('auditoria.ver');
        $u->unsetRelation('roles');
        $this->assertTrue($u->can('auditoria.ver'));
        $this->assertFalse($u->checkPermissionTo('auditoria.ver', 'web'));
        $this->actingAs($u)->get(route('admin.sistema-experto.index'))->assertForbidden();
        Livewire::actingAs($u)->test(ConocimientoPanel::class)->assertForbidden();
    }

    public function test_otro_rol_con_permiso_no_obtiene_consulta_experta(): void
    {
        $u = $this->usuario('ENFERMEROS');
        $u->givePermissionTo('auditoria.ver');
        $this->actingAs($u)->get(route('admin.sistema-experto.index'))->assertForbidden();
        Livewire::actingAs($u)->test(ConocimientoPanel::class)->assertForbidden();
    }

    public function test_cuenta_inactiva_y_preview_no_entran_al_inspector(): void
    {
        $u = $this->usuario(estado: 'INACTIVO');
        Livewire::actingAs($u)->test(ConocimientoPanel::class)->assertForbidden();
        $u->estado = 'ACTIVO';
        $u->save();
        $this->withSession([RolePreviewService::SESSION_KEY => 'ENFERMEROS'])->actingAs($u)
            ->get(route('admin.sistema-experto.index'))->assertForbidden();
    }

    public function test_revocacion_despues_del_montaje_bloquea_siguiente_accion(): void
    {
        $u = $this->usuario();
        $componente = Livewire::actingAs($u)->test(ConocimientoPanel::class)->assertOk();
        $u->roles->first()->revokePermissionTo('auditoria.ver');
        $u->unsetRelation('roles');
        $componente->call('seleccionarCaso', 'mixto')->assertForbidden();
    }

    public function test_seleccion_consulta_solo_las_filas_de_la_version_elegida(): void
    {
        $u = $this->usuario();
        foreach (['A', 'B'] as $sufijo) {
            DB::table('versiones_modelo_experto')->insert(['cod_version_modelo' => 'VER_'.$sufijo, 'codigo_version' => $sufijo,
                'nombre' => 'Versión '.$sufijo, 'estado' => 'INACTIVO', 'fecha_hora_creacion' => '2026-10-08 10:00:00', 'cod_usuario_creacion' => $u->cod_usuario]);
            DB::table('nodos_semanticos')->insert(['cod_nodo_semantico' => 'NOD_'.$sufijo, 'cod_version_modelo' => 'VER_'.$sufijo,
                'codigo_semantico' => 'CONCEPTO_'.$sufijo, 'tipo_nodo' => 'CRITERIO', 'nombre' => 'Concepto '.$sufijo,
                'definicion' => 'Definición artificial '.$sufijo, 'estado' => 'INACTIVO',
                'fecha_hora_creacion' => '2026-10-08 10:00:00', 'cod_usuario_creacion' => $u->cod_usuario]);
        }
        Livewire::actingAs($u)->test(ConocimientoPanel::class)
            ->call('seleccionarVersion', 'VER_A')->assertSee('Definición artificial A')->assertDontSee('Definición artificial B')
            ->call('seleccionarVersion', 'VER_B')->assertSee('Definición artificial B')->assertDontSee('Definición artificial A');
        $this->assertDatabaseCount('versiones_modelo_experto', 2);
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_version_inexistente_y_caso_no_permitido_se_rechazan(): void
    {
        $u = $this->usuario();
        Livewire::actingAs($u)->test(ConocimientoPanel::class)->call('seleccionarVersion', 'NO_EXISTE')->assertNotFound();
        Livewire::actingAs($u)->test(ConocimientoPanel::class)->call('seleccionarCaso', 'ejecutar_clinico')->assertStatus(422);
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }
}
