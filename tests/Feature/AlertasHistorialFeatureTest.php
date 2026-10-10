<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Features\Alertas\HistorialAlerta;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AlertasHistorialFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function preparar(array $permisos): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create(['nombres' => 'Carlos', 'ap_paterno' => 'Enfermero']);
        foreach ($permisos as $p) {
            $user->givePermissionTo(Permission::findOrCreate($p, 'web'));
        }
        $this->actingAs($user);
        $adulto = Residente::factory()->create(['estado' => 'ACTIVO']);
        return [$user, $adulto];
    }

    private function prepararEnfermero(): array
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        $user->assignRole('ENFERMEROS');

        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_ALERTAS',
            'nombre' => 'Turno Alertas',
            'hora_inicio' => '00:00:00',
            'hora_cierre' => '23:59:59',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_ALERTAS',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ACTIVA',
        ]);
        AsignacionResidenteJornada::query()->create([
            'cod_asignacion' => 'ARJ_ALERTAS',
            'cod_residente' => $adulto->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $user->personal->cod_personal,
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        return [$user, $adulto];
    }

    public function test_usuario_sin_permiso_no_puede_ver_historial(): void
    {
        [$user, $adulto] = $this->preparar([]);

        $alerta = Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'REVISION',
            'descripcion' => 'Prueba sin permiso',
            'estado' => 'ABIERTA',
        ]);

        Livewire::test(HistorialAlerta::class, ['alertaId' => $alerta->cod_alerta])
            ->assertStatus(403);
    }

    public function test_usuario_con_permiso_ve_estado_actual_y_timeline_cronologico(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver']);
        $user->assignRole('ADMINISTRADOR');

        $alerta = Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'REVISION',
            'titulo' => 'Seguimiento de hidratación',
            'descripcion' => 'Alerta de prueba para trazabilidad',
            'estado' => 'ABIERTA',
            'prioridad' => 'MEDIO',
        ]);

        $alerta->eventos()->create([
            'cod_evento_alerta' => 'EVA_TEST0001',
            'cod_usuario' => $user->cod_usuario,
            'tipo_evento' => 'CREACION',
            'estado_anterior' => null,
            'estado_nuevo' => 'ABIERTA',
            'fecha_hora' => now()->subHours(2),
            'descripcion' => 'Creación inicial del evento',
        ]);

        $alerta->eventos()->create([
            'cod_evento_alerta' => 'EVA_TEST0002',
            'cod_usuario' => $user->cod_usuario,
            'tipo_evento' => 'SEGUIMIENTO',
            'estado_anterior' => 'ABIERTA',
            'estado_nuevo' => 'ABIERTA',
            'fecha_hora' => now()->subHour(),
            'descripcion' => 'Revisión presencial efectuada',
        ]);

        Livewire::test(HistorialAlerta::class, ['alertaId' => $alerta->cod_alerta])
            ->assertStatus(200)
            ->assertSee('REVISION')
            ->assertSee('Alerta de prueba para trazabilidad')
            ->assertSee('Creación inicial del evento')
            ->assertSee('Revisión presencial efectuada')
            ->assertViewHas('alerta', fn ($actual) => $actual->estado === 'ABIERTA')
            ->assertSee('Abierta')
            ->assertSee('Historial de Intervenciones');
    }

    public function test_usuario_con_permiso_gestionar_puede_registrar_accion_en_eventos_alerta(): void
    {
        [$user, $adulto] = $this->prepararEnfermero();

        $alerta = Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'REVISION',
            'descripcion' => 'Alerta para gestión',
            'estado' => 'ABIERTA',
        ]);

        Livewire::test(HistorialAlerta::class, ['alertaId' => $alerta->cod_alerta])
            ->set('accion', 'Nota clínica de seguimiento registrada.')
            ->call('guardarAccion')
            ->assertHasNoErrors()
            ->assertDispatched('evento-alerta-registrado');

        $this->assertDatabaseHas('eventos_alerta', [
            'cod_alerta' => $alerta->cod_alerta,
            'cod_usuario' => $user->cod_usuario,
            'descripcion' => 'Nota clínica de seguimiento registrada.',
        ]);

        $this->assertSame('ABIERTA', $alerta->fresh()->estado);
    }

    public function test_permiso_sin_turno_de_enfermeria_no_autoriza_mutacion_clinica(): void
    {
        [, $adulto] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        $alerta = Alerta::query()->create([
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'REVISION',
            'descripcion' => 'Alerta protegida por contexto asistencial',
            'estado' => 'ABIERTA',
        ]);

        Livewire::test(HistorialAlerta::class, ['alertaId' => $alerta->cod_alerta])
            ->assertForbidden();

        $this->assertDatabaseMissing('eventos_alerta', [
            'cod_alerta' => $alerta->cod_alerta,
            'descripcion' => 'Intento fuera de un turno autorizado.',
        ]);
    }

    public function test_no_se_pueden_agregar_acciones_a_una_alerta_cerrada(): void
    {
        [$user, $adulto] = $this->prepararEnfermero();

        $alerta = Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'REVISION',
            'descripcion' => 'Alerta ya concluida',
            'estado' => 'CERRADA',
        ]);

        Livewire::test(HistorialAlerta::class, ['alertaId' => $alerta->cod_alerta])
            ->set('accion', 'Intento de modificar alerta cerrada.')
            ->call('guardarAccion')
            ->assertStatus(409);
    }
}
