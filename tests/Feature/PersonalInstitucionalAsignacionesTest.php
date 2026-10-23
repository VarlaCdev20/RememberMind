<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Regresión del directorio con asignaciones V2: el turno pertenece a la jornada. */
class PersonalInstitucionalAsignacionesTest extends TestCase
{
    use RefreshDatabase;

    /** La lectura conserva ambos estados activos que ya admite el panel. */
    public static function estadosActivos(): array
    {
        return [['ACTIVA'], ['ACTIVO']];
    }

    #[DataProvider('estadosActivos')]
    public function test_directorio_carga_y_muestra_el_turno_de_la_jornada_sin_escrituras(string $estado): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $gerente = User::factory()->create(['nombres' => 'Dirección', 'ap_paterno' => 'Sintética']);
        $gerente->assignRole('GERENTE');
        $asignado = User::factory()->create(['nombres' => 'Personal asignado', 'ap_paterno' => 'Sintético', 'correo' => 'asignado@example.test']);
        $asignado->assignRole('ENFERMEROS');
        $sinAsignacion = User::factory()->create(['nombres' => 'Personal sin jornada', 'ap_paterno' => 'Sintético', 'correo' => 'sinjornada@example.test']);
        $sinAsignacion->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Turno sintético mañana', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => now()->toDateString(), 'estado' => 'ABIERTA']);
        $asignacion = AsignacionPersonal::create(['cod_personal' => $asignado->personal->getKey(),
            'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'estado' => $estado]);
        $original = $asignacion->fresh()->getRawOriginal();
        $this->actingAs($gerente);

        $this->get(route('admin.personal-institucional'))->assertOk();
        $panel = Livewire::test(PersonalInstitucionalPanel::class)
            ->assertSee($asignado->correo)->assertSee($sinAsignacion->correo)
            ->assertSee('<dd>Turno sintético mañana</dd>', false)
            ->assertSee('Sin turno asignado');
        $panel->call('cambiarVistaListadoResumen', 'tarjetas')
            ->assertSee('<dd>Turno sintético mañana</dd>', false);
        $panel->call('setTab', 'salud')->assertSee($asignado->correo)
            ->assertSee('Turno sintético mañana');
        $panel->call('setTab', 'reportes')->assertHasNoErrors();
        // Los reportes conservan su filtro vigente de asignaciones ACTIVA.
        if ($estado === 'ACTIVA') {
            $panel->assertSee('Turno sintético mañana');
        }

        self::assertSame($original, $asignacion->fresh()->getRawOriginal());
        self::assertDatabaseCount('asignaciones_personal', 1);
        self::assertDatabaseCount('jornadas', 1);
    }

    /** La corrección de lectura no amplía el acceso al directorio institucional. */
    public function test_ruta_rechaza_cuenta_sin_permiso_de_personal(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.personal-institucional'))->assertForbidden();
        self::assertDatabaseCount('asignaciones_personal', 0);
    }
}
