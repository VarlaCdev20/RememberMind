<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\Admision;
use App\Models\Alergia;
use App\Models\Contacto;
use App\Models\ResidenteContacto;
use App\Models\SeguroResidente;
use App\Models\SignoVital;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FichaResumenClinicoTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->usuario = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Rojas',
        ]);
        $this->usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($this->usuario);
    }

    public function test_resumen_muestra_estado_vacio_sin_inventar_signos_vitales(): void
    {
        $residente = AdultoMayor::factory()->create();

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'resumen')
            ->assertSee('Sin datos clínicos para graficar')
            ->assertSee('No existen controles recientes')
            ->assertDontSee('120/80')
            ->assertDontSee('36.5°C')
            ->assertDontSee('68.5 kg');
    }

    public function test_resumen_muestra_exclusivamente_el_ultimo_control_persistido(): void
    {
        $residente = AdultoMayor::factory()->create();

        SignoVital::query()->create([
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'fecha_hora' => now(),
            'presion_sistolica' => 138,
            'presion_diastolica' => 84,
            'frecuencia_cardiaca' => 77,
            'frecuencia_respiratoria' => 18,
            'temperatura' => 37.1,
            'saturacion_oxigeno' => 95,
            'estado' => 'VIGENTE',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'resumen')
            ->assertSee('138/84')
            ->assertSee('77')
            ->assertSee('95%')
            ->assertSee('37.1 °C')
            ->assertDontSee('120/80');
    }

    public function test_ficha_lee_datos_de_identidad_clinica_desde_tablas_v2_normalizadas(): void
    {
        $residente = AdultoMayor::factory()->create([
            'grupo_sanguineo' => 'AB',
            'factor_rh' => '-',
        ]);
        $personal = $this->usuario->personal;

        Alergia::query()->create([
            'cod_alergia' => 'ALE_FICHA_V2',
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'tipo' => 'MEDICAMENTO',
            'sustancia' => 'Penicilina',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);
        SeguroResidente::query()->create([
            'cod_seguro' => 'SEG_FICHA_V2',
            'cod_residente' => $residente->cod_residente,
            'entidad' => 'Seguro Clínico V2',
            'estado' => 'ACTIVO',
        ]);
        Admision::query()->create([
            'cod_admision' => 'ADM_FICHA_V2',
            'cod_residente' => $residente->cod_residente,
            'cod_usuario_registro' => $this->usuario->cod_usuario,
            'fecha_hora_admision' => '2026-08-15 09:30:00',
            'motivo_ingreso' => 'Ingreso programado',
            'estado' => 'ACTIVA',
        ]);
        Contacto::query()->create([
            'cod_contacto' => 'CON_FICHA_V2',
            'nombres' => 'María',
            'apellido_paterno' => 'Quispe',
            'celular' => '70000001',
            'estado' => 'ACTIVO',
        ]);
        ResidenteContacto::query()->create([
            'cod_residente_contacto' => 'RCO_FICHA_V2',
            'cod_residente' => $residente->cod_residente,
            'cod_contacto' => 'CON_FICHA_V2',
            'parentesco' => 'HIJA',
            'responsable_principal' => true,
            'contacto_emergencia' => true,
            'autoriza_informacion' => true,
            'autoriza_salida' => false,
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->assertSee('Penicilina')
            ->assertSee('Seguro Clínico V2')
            ->assertSee('AB -')
            ->assertSee('María Quispe')
            ->assertSee('15/08/2026')
            ->assertDontSee('Familiar de Referencia')
            ->assertDontSee('Dieta normal blanda');
    }
}
