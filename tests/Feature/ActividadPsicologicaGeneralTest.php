<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPsicologica;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ActividadPsicologicaGeneralTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_tres_listados_muestran_solo_registros_del_profesional_y_abren_la_ficha(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->travelTo(now()->startOfDay()->addHours(12));

        $usuario = $this->psicologo();
        $otro = $this->psicologo();
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Clara', 'apellido_paterno' => 'Mamani']);
        $area = Area::firstOrCreate(['cod_area' => 'ARE_PSI'], ['nombre' => 'Psicología', 'estado' => 'ACTIVA']);

        $proxima = $this->atencion($residente, $usuario->personal, $area, 'PROGRAMADA', now()->addDay(), 'Entrevista de adaptación');
        $this->atencion($residente, $otro->personal, $area, 'PROGRAMADA', now()->addDay(), 'Sesión de otro profesional');
        $pasada = $this->atencion($residente, $usuario->personal, $area, 'FINALIZADA', now()->subDay(), 'Seguimiento individual');
        $this->atencion($residente, $otro->personal, $area, 'FINALIZADA', now()->subDay(), 'Seguimiento ajeno');

        ValoracionPsicologica::create([
            'cod_residente' => $residente->getKey(),
            'cod_personal' => $usuario->personal->getKey(),
            'cod_atencion' => $pasada->getKey(),
            'fecha_hora' => now()->subDay(),
            'conclusion' => 'Conclusión psicológica documentada',
            'estado' => 'VIGENTE',
        ]);

        $this->actingAs($usuario);
        $this->get(route('admin.psicologia.agenda'))->assertOk()
            ->assertSee('Entrevista de adaptación')->assertDontSee('Sesión de otro profesional')
            ->assertSee(route('admin.psicologia.residente.ficha', ['residente' => $residente, 'tab' => 'seguimiento']), false);
        $this->get(route('admin.psicologia.seguimiento'))->assertOk()
            ->assertSee('Seguimiento individual')->assertDontSee('Seguimiento ajeno')
            ->assertDontSee('Entrevista de adaptación');
        $this->get(route('admin.psicologia.informes'))->assertOk()
            ->assertSee('Conclusión psicológica documentada')
            ->assertSee(route('admin.psicologia.residente.ficha', ['residente' => $residente, 'tab' => 'informes']), false);
    }

    public function test_una_cuenta_sin_rol_o_sin_permiso_no_puede_consultar_los_listados(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');
        $this->actingAs($usuario);

        foreach (['agenda', 'seguimiento', 'informes'] as $seccion) {
            $this->get(route('admin.psicologia.'.$seccion))->assertForbidden();
        }

        $psicologo = $this->psicologo();
        $psicologo->roles()->firstOrFail()->revokePermissionTo('valoraciones_psicologicas.ver');
        $this->actingAs($psicologo);
        $this->get(route('admin.psicologia.informes'))->assertForbidden();
    }

    private function psicologo(): User
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('PSICOLOGO/A');
        Personal::create([
            'cod_usuario' => $usuario->getKey(),
            'nombres' => 'Profesional',
            'apellido_paterno' => 'Psicología',
            'numero_documento' => 'PSI-'.uniqid(),
            'profesion' => 'PSICOLOGIA',
            'estado' => 'ACTIVO',
        ]);

        return $usuario;
    }

    private function atencion(Residente $residente, Personal $personal, Area $area, string $estado, $fecha, string $motivo): Atencion
    {
        return Atencion::create([
            'cod_residente' => $residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(),
            'tipo_atencion' => 'SEGUIMIENTO',
            'fecha_hora' => $fecha,
            'estado' => $estado,
            'motivo' => $motivo,
        ]);
    }
}
