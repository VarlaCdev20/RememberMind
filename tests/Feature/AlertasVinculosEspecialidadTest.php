<?php

namespace Tests\Feature;

use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionNutricional;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarTrazaAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\VinculosProfesionalesAlertas;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AlertasVinculosEspecialidadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function actor(string $rol): User
    {
        $actor = User::factory()->create(['nombres' => 'Profesional de prueba aislada']);
        $actor->assignRole($rol);

        return $actor;
    }

    private function alerta(Residente $residente, array $datos = []): Alerta
    {
        return Alerta::create($datos + ['cod_residente' => $residente->getKey(), 'modulo' => 'MANUAL',
            'tipo' => 'SEGUIMIENTO', 'prioridad' => 'MEDIA', 'estado' => 'ABIERTA', 'fecha_hora' => now()]);
    }

    private function ids(User $actor, string $rol): array
    {
        return app(VinculosProfesionalesAlertas::class)->aplicar(app(BandejaAlertas::class)->consulta($actor), $actor, $rol)
            ->pluck('cod_alerta')->all();
    }

    public function test_cada_especialidad_distingue_responsabilidad_sin_clasificar_texto_libre(): void
    {
        $super = $this->actor('SUPERADMINISTRADOR');
        $residente = Residente::factory()->create();
        foreach (VinculosProfesionalesAlertas::ESPECIALIDADES as $rol => $nombre) {
            $profesional = $this->actor($rol);
            $alerta = $this->alerta($residente, ['cod_personal_responsable' => $profesional->personal->getKey()]);
            $esperadas[$rol] = $alerta->getKey();
        }
        $sinVinculo = $this->alerta($residente, ['descripcion' => 'Nutrición, movilidad, ansiedad y memoria: texto ficticio sin vínculo.']);
        foreach ($esperadas as $rol => $codigo) {
            $this->assertSame([$codigo], $this->ids($super, $rol));
        }
        $this->assertSame([$sinVinculo->getKey()], $this->ids($super, 'SIN_VINCULO'));
        $this->assertCount(7, $this->ids($super, ''));
        $metadatos = app(VinculosProfesionalesAlertas::class)->paraAlertas($super, Alerta::all());
        $this->assertSame([], $metadatos[$sinVinculo->getKey()]);
        $this->assertSame(['Responsable del seguimiento'], $metadatos[$esperadas['NUTRICIONISTA']][0]['motivos']);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_origen_y_responsable_pueden_vincular_dos_profesiones_sin_cruzar_residentes_ni_permisos(): void
    {
        $super = $this->actor('SUPERADMINISTRADOR');
        $enfermera = $this->actor('ENFERMEROS');
        $medico = $this->actor('MEDICO GENERAL/GERIATRA');
        $residente = Residente::factory()->create();
        $incidente = Incidente::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $enfermera->personal->getKey(),
            'tipo_incidente' => 'CAIDA', 'fecha_hora' => now(), 'descripcion' => 'Caso ficticio aislado', 'gravedad' => 'LEVE',
            'requiere_medico' => true, 'requiere_derivacion' => false, 'estado' => 'EN_SEGUIMIENTO']);
        $alerta = $this->alerta($residente, ['modulo' => 'incidentes', 'cod_registro' => $incidente->getKey(), 'cod_personal_responsable' => $medico->personal->getKey()]);
        $ajena = $this->alerta(Residente::factory()->create(), ['modulo' => 'INCIDENTES', 'cod_registro' => $incidente->getKey()]);
        $this->assertSame([$alerta->getKey()], $this->ids($super, 'ENFERMEROS'));
        $this->assertSame([$alerta->getKey()], $this->ids($super, 'MEDICO GENERAL/GERIATRA'));
        $this->assertSame([], $this->ids($super, 'NUTRICIONISTA'));
        $this->assertSame([$ajena->getKey()], $this->ids($super, 'SIN_VINCULO'));
        $nutricionista = $this->actor('NUTRICIONISTA');
        $nutricionista->roles->first()->syncPermissions(['alertas.ver']);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->assertSame([], $this->ids($nutricionista, 'ENFERMEROS'));
        $datos = app(VinculosProfesionalesAlertas::class)->paraAlertas($nutricionista, collect([$alerta]));
        $consultas = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertSame(['MEDICO GENERAL/GERIATRA'], array_column($datos[$alerta->getKey()], 'rol'));
        $this->assertFalse(collect($consultas)->contains(fn ($q) => str_contains($q['query'], '"incidentes"')));
        $this->actingAs($super);
        $traza = app(ConsultarTrazaAlerta::class)->consultar($super, $alerta->getKey());
        $this->assertCount(2, $traza['especialidades']);
        $this->assertContains('Enfermería', array_column($traza['nodos'], 'label'));
    }

    public function test_previsualizacion_entra_filtrada_conserva_urgentes_y_no_adjunta_contexto_ajeno(): void
    {
        $super = $this->actor('SUPERADMINISTRADOR');
        $nutricionista = $this->actor('NUTRICIONISTA');
        $medico = $this->actor('MEDICO GENERAL/GERIATRA');
        $residente = Residente::factory()->create();
        $nutricional = $this->alerta($residente, ['cod_personal_responsable' => $nutricionista->personal->getKey()]);
        $urgente = $this->alerta($residente, ['cod_personal_responsable' => $medico->personal->getKey(), 'prioridad' => 'CRITICA']);
        $this->actingAs($super);
        app(RolePreviewService::class)->activate($super, 'NUTRICIONISTA');
        $panel = Livewire::test(AlertasPanel::class)->assertSet('filtroEspecialidad', 'NUTRICIONISTA');
        $this->assertSame([$nutricional->getKey()], $panel->viewData('alertas')->modelKeys());
        $this->assertSame([$urgente->getKey()], $panel->viewData('prioritarias')->modelKeys());
        $this->assertSame(1, $panel->viewData('conteos')['total']);
        $panel->set('mostrarAnalisis', true);
        $this->assertSame(1, array_sum($panel->viewData('chartData')['estados']));
        $panel->call('verDetalle', $urgente->getKey())->assertSet('modalDetalle', true);
        $panel->call('cerrarModales')->call('verPrioritarias')->assertSet('filtroEspecialidad', '');
        $this->assertSame([$urgente->getKey()], $panel->viewData('alertas')->modelKeys());
        $this->assertNull($panel->viewData('contextoEspecialidad'));
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_atencion_referenciada_muestra_area_real_y_no_usa_nombre_del_area_como_rol(): void
    {
        $super = $this->actor('SUPERADMINISTRADOR');
        $medico = $this->actor('MEDICO GENERAL/GERIATRA');
        $residente = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área integral de prueba', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $medico->personal->getKey(), 'cod_area' => $area->getKey(), 'tipo_atencion' => 'REVISION', 'fecha_hora' => now(), 'estado' => 'ABIERTA']);
        $alerta = $this->alerta($residente, ['modulo' => 'SOLICITUD_MEDICA', 'cod_registro' => $atencion->getKey()]);
        $this->assertSame([$alerta->getKey()], $this->ids($super, 'MEDICO GENERAL/GERIATRA'));
        $this->assertSame([], $this->ids($super, 'FISIOTERAPEUTA'));
        $this->actingAs($super);
        $traza = app(ConsultarTrazaAlerta::class)->consultar($super, $alerta->getKey());
        $this->assertSame('Área integral de prueba', collect($traza['nodos'])->firstWhere('id', 'area-origen')['texto']);
        $seguimiento = $this->alerta($residente, ['modulo' => 'INCIDENTE', 'tipo' => 'INCIDENTE EN SEGUIMIENTO', 'cod_registro' => $atencion->getKey()]);
        $tipoAjeno = $this->alerta($residente, ['modulo' => 'INCIDENTE', 'tipo' => 'OTRO TIPO', 'cod_registro' => $atencion->getKey()]);
        $this->assertContains($seguimiento->getKey(), $this->ids($super, 'MEDICO GENERAL/GERIATRA'));
        $this->assertNotContains($tipoAjeno->getKey(), $this->ids($super, 'MEDICO GENERAL/GERIATRA'));
        $datos = app(VinculosProfesionalesAlertas::class)->paraAlertas($super, collect([$seguimiento, $tipoAjeno]));
        $this->assertSame(['MEDICO GENERAL/GERIATRA'], array_column($datos[$seguimiento->getKey()], 'rol'));
        $this->assertSame([], $datos[$tipoAjeno->getKey()]);
    }

    public function test_valoracion_del_residente_no_convierte_una_alerta_ajena_en_nutricional(): void
    {
        $super = $this->actor('SUPERADMINISTRADOR');
        $nutricionista = $this->actor('NUTRICIONISTA');
        $residente = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $nutricionista->personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'VALORACION', 'fecha_hora' => now(), 'estado' => 'ABIERTA']);
        ValoracionNutricional::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $nutricionista->personal->getKey(),
            'cod_atencion' => $atencion->getKey(), 'fecha_hora' => now(), 'apetito' => 'Dato aislado del residente', 'estado' => 'VIGENTE']);
        $alerta = $this->alerta($residente);
        $this->actingAs($super);
        app(RolePreviewService::class)->activate($super, 'NUTRICIONISTA');
        $panel = Livewire::test(AlertasPanel::class)->assertSee('No hay alertas con este vínculo profesional');
        $this->assertSame(0, $panel->viewData('alertas')->total());
        $panel->call('setFiltroRapido', 'filtroEspecialidad', '')->assertDontSee('Dato aislado del residente');
        $this->assertNull($panel->viewData('contextoEspecialidad'));
        $this->assertNotContains('contexto-profesional', array_column($panel->viewData('traza')['nodos'], 'id'));
        $this->assertSame([], $this->ids($super, 'NUTRICIONISTA'));
        $this->assertDatabaseCount('alertas', 1);
    }

    public function test_medicion_y_administracion_muestran_autoria_y_omision_sin_inventar_ejecucion(): void
    {
        $super = $this->actor('SUPERADMINISTRADOR');
        $enfermera = $this->actor('ENFERMEROS');
        $medico = $this->actor('MEDICO GENERAL/GERIATRA');
        $residente = Residente::factory()->create();
        $signo = SignoVital::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $enfermera->personal->getKey(),
            'fecha_hora' => now(), 'temperatura' => 36.5, 'estado' => 'VIGENTE']);
        $alertaSigno = $this->alerta($residente, ['modulo' => 'SIGNOS', 'cod_registro' => $signo->getKey()]);
        $this->assertSame([$alertaSigno->getKey()], $this->ids($super, 'ENFERMEROS'));
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $medico->personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'CONTROL', 'fecha_hora' => now(), 'estado' => 'ABIERTA']);
        $medicamento = Medicamento::create(['nombre_generico' => 'Sustancia ficticia de prueba', 'forma_farmaceutica' => 'Comprimido',
            'unidad' => 'mg', 'control_especial' => false, 'estado' => 'ACTIVO']);
        $prescripcion = Prescripcion::create(['cod_residente' => $residente->getKey(), 'cod_atencion' => $atencion->getKey(),
            'cod_personal' => $medico->personal->getKey(), 'cod_medicamento' => $medicamento->getKey(), 'dosis' => 1,
            'unidad_dosis' => 'mg', 'via_administracion' => 'ORAL', 'frecuencia' => 'Cada 24 horas', 'segun_necesidad' => false,
            'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno de prueba', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        $administracion = AdministracionMedicacion::create(['cod_prescripcion' => $prescripcion->getKey(), 'cod_residente' => $residente->getKey(),
            'cod_personal' => $enfermera->personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'fecha_hora_programada' => now()->subHour(),
            'resultado' => 'OMITIDA', 'motivo_omision' => 'Motivo ficticio registrado', 'estado' => 'FINALIZADO']);
        $alerta = $this->alerta($residente, ['modulo' => 'MEDICACION', 'cod_registro' => $administracion->getKey()]);
        $this->actingAs($super);
        $traza = app(ConsultarTrazaAlerta::class)->consultar($super, $alerta->getKey());
        $origen = collect($traza['nodos'])->firstWhere('id', 'origen-operativo');
        $this->assertSame('OMITIDA', $origen['texto']);
        $this->assertContains('Motivo ficticio registrado', array_column($origen['detalles'], 'valor'));
        $this->assertSame(['ENFERMEROS'], array_column($traza['especialidades'], 'rol'));
        $this->assertNull($administracion->fresh()->fecha_hora_administracion);
        $this->assertSame('ACTIVA', $prescripcion->fresh()->estado);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_seleccionar_especialidad_no_amplia_ambito_ni_da_acceso_a_familia_o_cuenta_inactiva(): void
    {
        $nutricionista = $this->actor('NUTRICIONISTA');
        $this->alerta(Residente::factory()->create(), ['cod_personal_responsable' => $nutricionista->personal->getKey()]);
        $enfermeraSinAsignacion = $this->actor('ENFERMEROS');
        $this->assertSame([], $this->ids($enfermeraSinAsignacion, 'NUTRICIONISTA'));
        $familiar = $this->actor('FAMILIAR');
        $familiar->givePermissionTo('alertas.ver');
        $this->assertSame([], $this->ids($familiar, 'NUTRICIONISTA'));
        $nutricionista->update(['estado' => 'INACTIVO']);
        $this->assertSame([], $this->ids($nutricionista, 'NUTRICIONISTA'));
        $this->assertSame([], $this->ids($nutricionista, ''));
        $this->assertDatabaseCount('eventos_alerta', 0);
    }
}
