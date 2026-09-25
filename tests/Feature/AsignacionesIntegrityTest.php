<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AsignacionesIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_assignment_does_not_fabricate_related_records(): void
    {
        try {
            AsignacionPersonal::query()->create([]);
            $this->fail('La asignación sin profesional debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La asignación de personal requiere un profesional explícito.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Personal)->getTable(), 0);
        $this->assertDatabaseCount((new Jornada)->getTable(), 0);
        $this->assertDatabaseCount((new Area)->getTable(), 0);
        $this->assertDatabaseCount((new AsignacionPersonal)->getTable(), 0);
    }

    public function test_resident_assignment_does_not_fabricate_a_workday_or_professional(): void
    {
        try {
            AsignacionResidenteJornada::query()->create(['cod_residente' => 'RES_INEXISTENTE']);
            $this->fail('La asignación sin jornada debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La asignación de residente requiere una jornada explícita.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Jornada)->getTable(), 0);
        $this->assertDatabaseCount((new Personal)->getTable(), 0);
        $this->assertDatabaseCount((new AsignacionResidenteJornada)->getTable(), 0);
    }
}
