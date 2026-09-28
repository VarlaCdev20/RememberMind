<?php

namespace Tests\Feature;

use App\Backend\Modulos\Residentes\Servicios\AdultoMayorService;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidenteAuditIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_archivar_y_restaurar_conservan_el_autor_explicito(): void
    {
        User::factory()->create(['cod_usuario' => 'USU_AJENO']);
        $autor = User::factory()->create(['cod_usuario' => 'USU_AUTOR']);
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_AUDITORIA',
            'nombres' => 'Residente',
            'apellido_paterno' => 'Auditable',
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ACTIVO',
        ]);
        $servicio = app(AdultoMayorService::class);

        $servicio->archivar($residente->cod_residente, $autor->cod_usuario, 'Archivo controlado');
        $servicio->restaurar($residente->cod_residente, $autor->cod_usuario);

        $this->assertDatabaseHas('historial_estados_residente', [
            'cod_residente' => $residente->cod_residente,
            'cod_usuario_registro' => $autor->cod_usuario,
            'estado_nuevo' => 'INACTIVO',
        ]);
        $this->assertDatabaseHas('historial_estados_residente', [
            'cod_residente' => $residente->cod_residente,
            'cod_usuario_registro' => $autor->cod_usuario,
            'estado_nuevo' => 'ACTIVO',
        ]);
        $this->assertDatabaseMissing('historial_estados_residente', [
            'cod_residente' => $residente->cod_residente,
            'cod_usuario_registro' => 'USU_AJENO',
        ]);
        $this->assertSame('ACTIVO', $residente->fresh()->estado);
    }
}
