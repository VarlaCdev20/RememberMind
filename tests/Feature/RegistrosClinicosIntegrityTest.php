<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Incidente;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class RegistrosClinicosIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerta_no_se_asigna_a_un_residente_arbitrario(): void
    {
        Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_ALERTA_AJENO',
            'nombres' => 'Residente',
            'apellido_paterno' => 'Ajeno',
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ACTIVO',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('La alerta requiere un residente explícito.');

        Alerta::query()->create([
            'tipo' => 'CLINICA',
            'titulo' => 'Alerta sin residente',
            'descripcion' => 'No debe atribuirse a otra persona.',
        ]);
    }

    public function test_incidente_no_se_atribuye_al_primer_personal(): void
    {
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_INCIDENTE_TEST',
            'nombres' => 'Residente',
            'apellido_paterno' => 'Prueba',
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ACTIVO',
        ]);
        User::factory()->create([
            'nombres' => 'Personal',
            'ap_paterno' => 'Ajeno',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('El incidente requiere el profesional responsable.');

        Incidente::query()->create([
            'cod_residente' => $residente->cod_residente,
            'tipo_incidente' => 'CAIDA',
            'fecha_hora' => now(),
            'descripcion' => 'Incidente sin responsable explícito.',
            'requiere_medico' => false,
            'requiere_derivacion' => false,
            'estado' => 'ABIERTO',
        ]);
    }

    public function test_signos_vitales_no_fabrican_personal_para_el_usuario(): void
    {
        $usuario = User::factory()->create();
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_SIGNOS_TEST',
            'nombres' => 'Residente',
            'apellido_paterno' => 'Prueba',
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ACTIVO',
        ]);
        $this->actingAs($usuario);

        try {
            SignoVital::query()->create([
                'cod_residente' => $residente->cod_residente,
                'fecha_hora' => now(),
                'temperatura' => 36.5,
                'estado' => 'VIGENTE',
            ]);
            $this->fail('El signo vital sin personal responsable debió ser rechazado.');
        } catch (LogicException $exception) {
            $this->assertSame(
                'El registro de signos vitales requiere personal responsable explícito.',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseCount('personal', 0);
        $this->assertDatabaseCount('signos_vitales', 0);
    }
}
