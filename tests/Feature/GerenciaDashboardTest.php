<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Reportes\Aplicacion\Consultas\RoleDashboardDataService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GerenciaDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_cobertura_cuenta_personas_unicas_en_asignaciones_activas_y_todas_las_areas(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(10, 0));
        $usuario = User::factory()->create();
        Personal::create(['cod_personal' => 'PER_GER_TEST', 'cod_usuario' => $usuario->getKey(),
            'nombres' => 'Persona', 'apellido_paterno' => 'Sintetica', 'numero_documento' => 'GER-TEST',
            'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        DB::table('turnos')->insert(['cod_turno' => 'TUR_GER_TEST', 'nombre' => 'Turno de prueba',
            'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'orden' => 1, 'estado' => 'ACTIVO']);
        DB::table('jornadas')->insert(['cod_jornada' => 'JOR_GER_TEST', 'cod_turno' => 'TUR_GER_TEST',
            'fecha_jornada' => today(), 'estado' => 'EN_CURSO']);
        for ($i = 1; $i <= 9; $i++) {
            DB::table('areas')->insert(['cod_area' => 'ARE_GER_'.$i, 'nombre' => 'Área de prueba '.$i, 'estado' => 'ACTIVA']);
            DB::table('asignaciones_personal')->insert(['cod_asignacion_personal' => 'ASP_GER_'.$i,
                'cod_personal' => 'PER_GER_TEST', 'cod_jornada' => 'JOR_GER_TEST', 'cod_area' => 'ARE_GER_'.$i,
                'tipo_asignacion' => 'REGULAR', 'fecha_asignacion' => now(),
                'estado' => $i % 2 ? 'ACTIVO' : 'ACTIVA']);
        }

        $service = app(RoleDashboardDataService::class);
        $data = $service->forRole($usuario, 'GERENTE');
        $this->assertSame(1, $data['metrics'][1]['value']);
        $this->assertSame(9, $data['metrics'][2]['value']);
        $this->assertCount(9, $data['panels'][0]['items']);

        DB::table('areas')->where('cod_area', 'ARE_GER_1')->update(['estado' => 'INACTIVA']);
        $this->assertSame(8, $service->forRole($usuario, 'GERENTE')['metrics'][2]['value']);
        DB::table('personal')->where('cod_personal', 'PER_GER_TEST')->update(['estado' => 'INACTIVO']);
        $this->assertSame(0, $service->forRole($usuario, 'GERENTE')['metrics'][1]['value']);
        DB::table('personal')->where('cod_personal', 'PER_GER_TEST')->update(['estado' => 'ACTIVO']);
        DB::table('turnos')->where('cod_turno', 'TUR_GER_TEST')->update(['estado' => 'INACTIVO']);
        $this->assertSame(0, $service->forRole($usuario, 'GERENTE')['metrics'][1]['value']);
        DB::table('turnos')->where('cod_turno', 'TUR_GER_TEST')->update(['estado' => 'ACTIVO']);
        DB::table('jornadas')->where('cod_jornada', 'JOR_GER_TEST')->update(['fecha_jornada' => today()->addDay()]);
        $this->assertSame(0, $service->forRole($usuario, 'GERENTE')['metrics'][1]['value']);
    }

    public function test_gerencia_tiene_accesos_de_planificacion_sin_escritura_clinica(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $gerente = User::factory()->create(['estado' => 'ACTIVO']);
        $gerente->assignRole('GERENTE');
        $response = $this->actingAs($gerente)->get(route('dashboard'))->assertOk();
        foreach (['admin.personal-institucional', 'admin.areas-institucionales.index', 'admin.turnos-asignaciones.index',
            'admin.turnos-enfermeria.index', 'admin.reportes.institucional.preview'] as $route) {
            $response->assertSee(route($route), false);
            $this->get(route($route))->assertOk();
        }
        $response->assertSee('Organización y planificación')->assertSeeText('Gerencia');
        $this->assertFalse($gerente->can('prescripciones.crear'));
        $this->assertFalse($gerente->can('usuarios.gestionar'));
        $this->assertDatabaseCount('asignaciones_personal', 0);
    }

    #[DataProvider('horariosSaludo')]
    public function test_gerencia_muestra_saludo_y_nombre_sin_etiqueta_generica_de_permisos(string $hora, string $saludo): void
    {
        $this->travelTo(Carbon::parse('2026-10-17 '.$hora, config('app.timezone')));
        $this->seed(RolesAndPermissionsSeeder::class);
        $gerente = User::factory()->create([
            'estado' => 'ACTIVO', 'nombres' => 'ALICIA ELENA',
            'ap_paterno' => 'PRUEBA', 'ap_materno' => 'EJEMPLO',
        ]);
        $gerente->assignRole('GERENTE');

        $this->actingAs($gerente)->get(route('dashboard'))->assertOk()
            ->assertSeeText($saludo.', Alicia Elena Prueba Ejemplo')
            ->assertSeeText('Gerencia')
            ->assertDontSeeText('Acceso según permisos');
    }

    public static function horariosSaludo(): array
    {
        return [
            'mañana' => ['11:59:00', 'Buenos días'],
            'tarde' => ['12:00:00', 'Buenas tardes'],
            'noche' => ['19:00:00', 'Buenas noches'],
        ];
    }

    public function test_previsualizacion_no_programa_polling_y_sigue_bloqueando_escrituras(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin)->get(route('dashboard'))->assertOk()
            ->assertSee('wire:poll.visible.60s="verificarAlertas"', false);
        $this->withSession([RolePreviewService::SESSION_KEY => 'GERENTE'])
            ->get(route('dashboard'))->assertOk()->assertSee('Modo previsualización')
            ->assertDontSee('wire:poll.visible.60s="verificarAlertas"', false);
        $this->postJson(Livewire::getUpdateUri(), ['components' => []])->assertForbidden();
        $this->assertSame(['SUPERADMINISTRADOR'], $superadmin->fresh()->getRoleNames()->all());
    }
}
