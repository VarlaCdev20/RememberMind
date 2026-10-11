<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class CuidadosIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_care_plan_does_not_fabricate_area_or_responsible_staff(): void
    {
        try {
            PlanCuidado::query()->create(['cod_residente' => 'RES_INEXISTENTE']);
            $this->fail('El plan sin área debió ser rechazado.');
        } catch (LogicException $exception) {
            $this->assertSame('El plan de cuidado requiere un área responsable explícita.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Area)->getTable(), 0);
        $this->assertDatabaseCount((new Personal)->getTable(), 0);
        $this->assertDatabaseCount((new User)->getTable(), 0);
        $this->assertDatabaseCount((new PlanCuidado)->getTable(), 0);
    }

    public function test_care_execution_does_not_fabricate_clinical_structure(): void
    {
        try {
            EjecucionCuidado::query()->create(['cod_residente' => 'RES_INEXISTENTE']);
            $this->fail('La ejecución sin intervención debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La ejecución de cuidado requiere una intervención explícita.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new PlanCuidado)->getTable(), 0);
        $this->assertDatabaseCount((new IntervencionCuidado)->getTable(), 0);
        $this->assertDatabaseCount((new Turno)->getTable(), 0);
        $this->assertDatabaseCount((new Jornada)->getTable(), 0);
        $this->assertDatabaseCount((new Personal)->getTable(), 0);
        $this->assertDatabaseCount((new EjecucionCuidado)->getTable(), 0);
    }
}
