<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AtencionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_attention_does_not_fabricate_responsible_staff(): void
    {
        try {
            Atencion::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_area' => 'ARE_INEXISTENTE',
                'tipo_atencion' => 'CONSULTA',
            ]);
            $this->fail('La atención sin personal debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La atención requiere el personal responsable.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Personal)->getTable(), 0);
        $this->assertDatabaseCount((new Atencion)->getTable(), 0);
    }

    public function test_attention_does_not_fabricate_an_area(): void
    {
        try {
            Atencion::query()->create([
                'cod_residente' => 'RES_INEXISTENTE',
                'cod_personal' => 'PER_INEXISTENTE',
                'tipo_atencion' => 'CONSULTA',
            ]);
            $this->fail('La atención sin área debió ser rechazada.');
        } catch (LogicException $exception) {
            $this->assertSame('La atención requiere el área responsable.', $exception->getMessage());
        }

        $this->assertDatabaseCount((new Area)->getTable(), 0);
        $this->assertDatabaseCount((new Atencion)->getTable(), 0);
    }
}
