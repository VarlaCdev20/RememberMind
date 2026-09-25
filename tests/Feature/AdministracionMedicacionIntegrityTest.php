<?php

namespace Tests\Feature;

use App\Models\AdministracionMedicacion;
use App\Models\Jornada;
use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AdministracionMedicacionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_does_not_fabricate_responsible_staff(): void
    {
        try {
            AdministracionMedicacion::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_jornada' => 'JOR_INEXISTENTE',
            ]);
            $this->fail('La administración sin personal debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La administración requiere el personal responsable.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Personal)->getTable(), 0);
        $this->assertDatabaseCount((new AdministracionMedicacion)->getTable(), 0);
    }

    public function test_administration_does_not_fabricate_a_shift_or_workday(): void
    {
        try {
            AdministracionMedicacion::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_personal' => 'PER_INEXISTENTE',
            ]);
            $this->fail('La administración sin jornada debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La administración requiere una jornada clínica activa.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Jornada)->getTable(), 0);
        $this->assertDatabaseCount((new AdministracionMedicacion)->getTable(), 0);
    }
}
