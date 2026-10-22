<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Consultas\ExperienciaAlertas;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/** Render real con fixtures: capturas optativas fuera del repositorio y de la BD institucional. */
class AlertasExperienciaVisualTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_diez_roles_conservan_una_entrada_proporcional_a_sus_permisos(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $residente = Residente::factory()->create(['nombres' => 'Elena Sintética', 'apellido_paterno' => 'Demostración']);
        $alertas = collect(['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'])
            ->map(fn ($estado) => Alerta::create(['cod_residente' => $residente->getKey(), 'estado' => $estado,
                'tipo' => 'Seguimiento de prueba', 'prioridad' => $estado === 'ABIERTA' ? 'CRITICO' : 'MEDIO',
                'titulo' => 'Caso sintético', 'descripcion' => 'Registro exclusivamente sintético para verificar la bandeja y sus filtros.',
                'modulo' => 'MANUAL', 'generacion' => 'MANUAL', 'fecha_hora' => now()->subMinutes(15)]));
        foreach (['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'GERENTE', 'ADMINISTRADOR', 'SUPERADMINISTRADOR', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO', 'FAMILIAR'] as $index => $rol) {
            $actor = User::factory()->create(['correo' => 'qa-'.($index + 1).'@example.test', 'nombres' => 'Profesional Sintético', 'apellido_paterno' => 'Demostración']);
            $actor->assignRole($rol);
            $actor->personal->update(['profesion' => match ($rol) {
                'MEDICO GENERAL/GERIATRA' => 'MEDICINA', 'PSICOLOGO/A' => 'PSICOLOGIA',
                'NUTRICIONISTA' => 'NUTRICION', 'FISIOTERAPEUTA' => 'FISIOTERAPIA', 'PEDAGOGO' => 'PEDAGOGIA', default => 'ENFERMERIA',
            }]);
            if ($rol === 'ENFERMEROS') {
                $alertas[2]->update(['cod_personal_responsable' => $actor->personal->getKey()]);
                $alertas[3]->update(['cod_personal_responsable' => $actor->personal->getKey()]);
                $alertas[0]->eventos()->create(['cod_usuario' => $actor->getKey(), 'tipo_evento' => 'CREACION',
                    'estado_anterior' => null, 'estado_nuevo' => 'ABIERTA', 'fecha_hora' => now()->subMinutes(15),
                    'descripcion' => 'Aviso sintético registrado para revisar el seguimiento del residente.']);
                $alertas[0]->eventos()->create(['cod_usuario' => $actor->getKey(), 'tipo_evento' => 'SEGUIMIENTO',
                    'estado_anterior' => 'ABIERTA', 'estado_nuevo' => 'ABIERTA', 'fecha_hora' => now()->subMinutes(5),
                    'descripcion' => 'Se conserva el aviso pendiente de la actuación autorizada. Caso exclusivamente sintético.']);
                $area = Area::create(['nombre' => 'Enfermería sintética', 'estado' => 'ACTIVA']);
                $turno = Turno::create(['nombre' => 'Mañana sintética', 'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'estado' => 'ACTIVO', 'orden' => 1]);
                $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
                AsignacionPersonal::create(['cod_personal' => $actor->personal->getKey(), 'cod_area' => $area->getKey(), 'cod_jornada' => $jornada->getKey(), 'tipo_asignacion' => 'RESPONSABLE', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
                AsignacionResidenteJornada::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $actor->personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'nivel_supervision' => 'DIRECTA', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
            }
            $this->actingAs($actor);
            $slug = sprintf('%02d', $index + 1).'-'.str_replace(['/', ' '], '-', mb_strtolower($rol));
            if ($actor->can('alertas.ver') && $rol !== 'FAMILIAR') {
                Livewire::flushState();
                $response = $this->get(route($rol === 'ENFERMEROS' ? 'admin.enfermeria.alertas' : 'admin.alertas-clinicas.index'))->assertOk();
                $response->assertSee('<h1>'.app(ExperienciaAlertas::class)->paraRol($rol)['titulo'].'</h1>', false);
                $response->assertSeeText('Especialidad vinculada')->assertSeeText('Buscar alertas');
                $this->exportar($slug, $response->getContent());
                $panel = Livewire::test(AlertasPanel::class)->call('verDetalle', $alertas->first()->getKey())->assertHasNoErrors();
                $panel->assertViewHas('puedeIntervenir', $rol === 'ENFERMEROS')
                    ->assertViewHas('puedeCoordinar', in_array($rol, ['ADMINISTRADOR', 'SUPERADMINISTRADOR'], true));
                if ($rol === 'ENFERMEROS') {
                    $panel->call('atenderAlerta', $alertas->first()->getKey())->assertSee('Nota de intervención')
                        ->call('guardarAtencion')->assertHasErrors('accionTomada')->call('cerrarModales');
                }
                if ($actor->can('alertas.gestionar')) {
                    $panel->call('abrirCrear')->assertSee('Tipo de alerta')->assertSee('Origen del evento')
                        ->assertSee('Prioridad registrada')->call('guardarAlerta')
                        ->assertHasErrors(['codResidente', 'tipoAlerta', 'motivo'])->call('cerrarModales');
                }
                Livewire::flushState();
                $detalle = $this->get(route($rol === 'ENFERMEROS' ? 'admin.enfermeria.alertas' : 'admin.alertas-clinicas.index', ['alerta' => $alertas->first()->getKey()]))->assertOk();
                $this->exportar($slug.'-detalle', $detalle->getContent());
            } else {
                $this->get(route('admin.alertas-clinicas.index'))->assertForbidden();
            }
            Livewire::flushState();
            $dashboard = $this->followingRedirects()->get('/dashboard')->assertOk();
            $this->exportar($slug.'-inicio', $dashboard->getContent());
        }
        $this->assertDatabaseCount('eventos_alerta', 2);
        $this->assertDatabaseCount('alertas', 7);
        if (getenv('RM_ALERTAS_QA_DIR')) {
            $schema = DB::select("SELECT name, type, sql FROM sqlite_master WHERE type IN ('table', 'index') AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL ORDER BY type DESC");
            $tables = [];
            foreach ($schema as $object) {
                if ($object->type === 'table') {
                    $tables[$object->name] = DB::table($object->name)->get()->all();
                }
            }
            // Solo fixtures del proceso de prueba: permite QA autenticado en una copia desechable.
            $this->exportar('base-sintetica', json_encode(['schema' => $schema, 'tables' => $tables], JSON_THROW_ON_ERROR));
        }
    }

    private function exportar(string $nombre, string $html): void
    {
        $directorio = getenv('RM_ALERTAS_QA_DIR');
        if (! $directorio) {
            return;
        }
        $ruta = realpath($directorio);
        if ($ruta === false || str_contains($ruta, base_path()) || ! str_contains($ruta, 'RememberMind-respaldos')) {
            throw new \RuntimeException('Las capturas sintéticas requieren un directorio privado de QA existente fuera del repositorio.');
        }
        file_put_contents($ruta.DIRECTORY_SEPARATOR.$nombre.'.html', $html);
    }
}
