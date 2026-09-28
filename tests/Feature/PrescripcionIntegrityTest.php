<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Medicamento;
use App\Models\Personal;
use App\Models\Prescripcion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class PrescripcionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescription_does_not_fabricate_a_medication(): void
    {
        try {
            Prescripcion::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_atencion' => 'ATN_INEXISTENTE',
                'cod_personal' => 'PER_INEXISTENTE',
                'via_administracion' => 'ORAL',
            ]);
            $this->fail('La prescripción sin medicamento debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La prescripción requiere un medicamento del catálogo.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Medicamento)->getTable(), 0);
        $this->assertDatabaseCount((new Prescripcion)->getTable(), 0);
    }

    public function test_prescription_does_not_fabricate_clinical_attention(): void
    {
        try {
            Prescripcion::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_medicamento' => 'MED_INEXISTENTE',
                'cod_personal' => 'PER_INEXISTENTE',
                'via_administracion' => 'ORAL',
            ]);
            $this->fail('La prescripción sin atención debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La prescripción requiere una atención clínica existente.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Atencion)->getTable(), 0);
        $this->assertDatabaseCount((new Prescripcion)->getTable(), 0);
    }

    public function test_prescription_does_not_select_an_arbitrary_professional(): void
    {
        try {
            Prescripcion::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_medicamento' => 'MED_INEXISTENTE',
                'cod_atencion' => 'ATN_INEXISTENTE',
                'via_administracion' => 'ORAL',
            ]);
            $this->fail('La prescripción sin profesional debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La prescripción requiere el profesional responsable.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Personal)->getTable(), 0);
        $this->assertDatabaseCount((new Prescripcion)->getTable(), 0);
    }
}
