<?php

namespace Tests\Feature;

use App\Models\Admision;
use App\Models\OcupacionCama;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OcupacionCamaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_occupancy_does_not_fabricate_an_admission(): void
    {
        $user = User::factory()->create();

        try {
            OcupacionCama::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_cama' => 'CAM_INEXISTENTE',
                'cod_usuario_registro' => $user->cod_usuario,
                'estado' => 'ACTIVA',
            ]);
            $this->fail('La ocupación sin admisión debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La ocupación requiere una admisión formal existente.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Admision)->getTable(), 0);
        $this->assertDatabaseCount((new OcupacionCama)->getTable(), 0);
    }

    public function test_occupancy_does_not_select_an_arbitrary_registering_user(): void
    {
        User::factory()->create();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('La ocupación requiere el usuario que registra la asignación.');

        OcupacionCama::query()->create([
            'cod_residente' => 'RES_INEXISTENTE',
            'cod_cama' => 'CAM_INEXISTENTE',
            'cod_admision' => 'ADM_INEXISTENTE',
            'estado' => 'ACTIVA',
        ]);
    }
}
