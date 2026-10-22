<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\NotaClinica;
use App\Models\Residente;
use App\Models\SeguimientoPedagogico;
use App\Models\User;
use App\Models\ValoracionFuncional;
use App\Models\ValoracionNutricional;
use App\Models\ValoracionPsicologica;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarContextoEspecialidadAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarResumenEquipoAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarTrazaAlerta;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AlertasFlujosPorRolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(10, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function actor(string $rol): User
    {
        $usuario = User::factory()->create(['nombres' => 'Profesional de prueba', 'apellido_paterno' => 'Aislada', 'estado' => 'ACTIVO']);
        $usuario->assignRole($rol);
        $this->actingAs($usuario);

        return $usuario;
    }

    private function alerta(Residente $residente, array $datos = []): Alerta
    {
        return Alerta::create($datos + ['cod_residente' => $residente->getKey(), 'estado' => 'ABIERTA', 'tipo' => 'SEGUIMIENTO', 'prioridad' => 'MEDIA', 'fecha_hora' => now(), 'modulo' => 'MANUAL', 'generacion' => 'MANUAL']);
    }

    public function test_cinco_especialidades_presentan_fuentes_y_composiciones_distintas_sin_leer_fuentes_sin_permiso(): void
    {
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $alerta = $this->alerta($residente);
        $area = Area::create(['nombre' => 'Área de prueba aislada', 'estado' => 'ACTIVA']);
        foreach ([
            ['MEDICO GENERAL/GERIATRA', NotaClinica::class, 'notas_clinicas.ver', ['tipo_nota' => 'EVOLUCION', 'contenido' => 'Hallazgos médicos de prueba'], 'rm-alert-desk__note', 'Hallazgos médicos de prueba'],
            ['PSICOLOGO/A', ValoracionPsicologica::class, 'valoraciones_psicologicas.ver', ['estado_animo' => 'Ánimo documentado en prueba'], 'rm-alert-desk__observations', 'Ánimo documentado en prueba'],
            ['NUTRICIONISTA', ValoracionNutricional::class, 'valoraciones_nutricionales.ver', ['apetito' => 'Apetito documentado en prueba', 'requerimiento_hidrico' => '1500.25'], 'rm-alert-desk__hydration', '1.500,25 mL por día'],
            ['FISIOTERAPEUTA', ValoracionFuncional::class, 'valoraciones_funcionales.ver', ['marcha' => 'Marcha documentada en prueba', 'necesita_supervision' => false], 'rm-alert-desk__autonomy', 'Marcha documentada en prueba'],
            ['PEDAGOGO', SeguimientoPedagogico::class, 'seguimientos_pedagogicos.ver', ['participacion' => 'Participación documentada', 'cambio_desempeno' => false], 'rm-alert-desk__activity', 'Participación documentada'],
        ] as [$rol, $modelo, $permiso, $campos, $composicion, $texto]) {
            $autor = $this->actor($rol);
            $alerta->update(['cod_personal_responsable' => $autor->personal->getKey()]);
            $contexto = ['cod_residente' => $residente->getKey(), 'cod_personal' => $autor->personal->getKey(), 'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE'];
            if ($rol !== 'PEDAGOGO') {
                $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $autor->personal->getKey(), 'cod_area' => $area->getKey(), 'tipo_atencion' => 'Atención de prueba', 'fecha_hora' => now(), 'estado' => 'ABIERTA']);
                $contexto['cod_atencion'] = $atencion->getKey();
            }
            $modelo::create($contexto + $campos);
            $historico = $contexto;
            $historico['fecha_hora'] = now();
            $historico['estado'] = 'ANULADA';
            $camposAnulados = array_map(fn ($v) => is_string($v) && ! is_numeric($v) ? 'Contenido anulado de prueba' : $v, $campos);
            $modelo::create($historico + $camposAnulados);
            Livewire::flushState();
            $panel = Livewire::test(AlertasPanel::class)->assertSee($composicion, false)->assertSee($texto)
                ->assertDontSee('Contenido anulado de prueba')->assertDontSee('<select ', false);
            $traza = $panel->viewData('traza');
            $this->assertSame($panel->viewData('perfil')['foco'], $traza['foco']);
            $panel->assertSee('data-map-theme="'.$traza['foco'].'"', false)->assertDontSee('Grafo de');
            $this->assertNotEmpty(array_filter($traza['nodos'], fn ($nodo) => isset($nodo['seccion'])));
            $ids = array_column($traza['nodos'], 'id');
            $this->assertContains('contexto-profesional', $ids);
            $this->assertContains('autor-profesional', $ids);
            $this->assertCount(count(array_unique($ids)), $ids);
            $this->assertFalse($traza['fuenteDisponible']);
            $this->assertSame('contexto-profesional', $traza['inicio']);
            foreach ($traza['aristas'] as $arista) {
                $this->assertContains($arista['desde'], $ids);
                $this->assertContains($arista['hasta'], $ids);
                if ($arista['hasta'] === 'contexto-profesional') {
                    $this->assertSame('residente', $arista['desde']);
                    $this->assertSame('contexto', $arista['tipo']);
                }
            }
            if ($rol !== 'PEDAGOGO') {
                $this->assertContains('atencion-profesional', $ids);
            }
            $this->assertSame('Profesional de prueba Aislada', substr($panel->viewData('contextoEspecialidad')['autor'], 0, strlen('Profesional de prueba Aislada')));
            if (in_array($rol, ['FISIOTERAPEUTA', 'PEDAGOGO'], true)) {
                $campoBooleano = $rol === 'FISIOTERAPEUTA' ? 'necesita_supervision' : 'cambio_desempeno';
                $this->assertSame('No', $panel->viewData('contextoEspecialidad')['campos'][$campoBooleano]['valor']);
            }
            $autor->roles()->firstOrFail()->revokePermissionTo($permiso);
            $autor->unsetRelation('roles');
            DB::enableQueryLog();
            DB::flushQueryLog();
            $sinPermiso = app(ConsultarContextoEspecialidadAlerta::class)->consultar($autor, $alerta->getKey());
            $consultas = DB::getQueryLog();
            DB::disableQueryLog();
            $this->assertFalse($sinPermiso['autorizado']);
            $this->assertFalse($sinPermiso['hayRegistro']);
            $this->assertCount(0, array_filter($consultas, fn ($q) => str_contains($q['query'], (new $modelo)->getTable())));
        }
        $this->assertDatabaseCount('eventos_alerta', 0);
        $this->assertDatabaseHas('alertas', ['cod_alerta' => $alerta->getKey(), 'estado' => 'ABIERTA', 'prioridad' => 'MEDIA']);
    }

    public function test_seleccion_de_caso_respeta_residente_filtros_y_ausencia_de_datos(): void
    {
        $nutricionista = $this->actor('NUTRICIONISTA');
        $uno = Residente::factory()->create(['nombres' => 'Residente Alfa', 'estado' => 'ADMITIDO']);
        $dos = Residente::factory()->create(['nombres' => 'Residente Beta', 'estado' => 'ADMITIDO']);
        $alertaUno = $this->alerta($uno, ['cod_personal_responsable' => $nutricionista->personal->getKey()]);
        $alertaDos = $this->alerta($dos, ['cod_personal_responsable' => $nutricionista->personal->getKey()]);
        $contextoOtroResidente = app(ConsultarContextoEspecialidadAlerta::class)->consultar($nutricionista, $alertaDos->getKey());
        try {
            app(ConsultarTrazaAlerta::class)->consultar($nutricionista, $alertaUno->getKey(), $contextoOtroResidente);
            $this->fail('El grafo no debe mezclar el contexto de otro residente.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        $panel = Livewire::test(AlertasPanel::class)->call('seleccionarTraza', $alertaDos->getKey())
            ->assertDispatched('alertas-caso-seleccionado')->assertSee('Sin valoración nutricional vigente');
        $this->assertSame($dos->nombre_completo, $panel->viewData('contextoEspecialidad')['residente']);
        $panel->set('search', 'Alfa');
        $this->assertSame($alertaUno->getKey(), $panel->viewData('traza')['codigo']);
        $this->assertSame($uno->nombre_completo, $panel->viewData('contextoEspecialidad')['residente']);
        $panel->set('search', 'sin coincidencias')->assertSee('rm-alert-desk__hydration', false)
            ->assertSee('Sin caso seleccionado')->assertDontSee('Inspección del registro');
        $this->assertNull($panel->viewData('contextoEspecialidad'));
        $this->assertNull($panel->viewData('traza'));
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_administracion_filtra_colas_gerencia_equipo_y_superadministracion_fuentes(): void
    {
        $coordinador = $this->actor('ADMINISTRADOR');
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $conResponsable = $this->alerta($residente, ['cod_personal_responsable' => $coordinador->personal->getKey(), 'fecha_hora_limite' => now()->subMinute()]);
        $sinResponsable = $this->alerta($residente, ['fecha_hora_limite' => now()->addDay()]);
        $this->alerta($residente, ['estado' => 'CERRADA', 'fecha_hora_limite' => now()->subDay(), 'cod_personal_responsable' => $coordinador->personal->getKey()]);
        $this->alerta($residente, ['estado' => 'ACTIVO', 'fecha_hora_limite' => now()->subDay()]);
        $panel = Livewire::test(AlertasPanel::class)->assertSee('¿Qué necesita coordinación?')
            ->call('filtrarResumen', 'sin_responsable');
        $this->assertSame([$sinResponsable->getKey()], $panel->viewData('alertas')->pluck('cod_alerta')->all());
        $panel->call('filtrarResumen', 'fuera_plazo');
        $this->assertSame([$conResponsable->getKey()], $panel->viewData('alertas')->pluck('cod_alerta')->all());
        $gerente = $this->actor('GERENTE');
        Livewire::flushState();
        $grupos = app(ConsultarResumenEquipoAlertas::class)->consultar($gerente)['responsables'];
        $this->assertSame(2, array_sum(array_column($grupos, 'total')));
        $panel = Livewire::test(AlertasPanel::class)->assertSee('Responsabilidad de las alertas activas')
            ->set('search', 'sin coincidencias')->set('filtroEstado', 'CERRADA')
            ->call('filtrarEquipo', 'PERSONAL:'.$coordinador->personal->getKey())->assertSet('search', '')->assertSet('filtroEstado', 'ACTIVAS');
        $this->assertSame([$conResponsable->getKey()], $panel->viewData('alertas')->pluck('cod_alerta')->all());
        $panel->call('filtrarEquipo');
        $this->assertSame(2, $panel->viewData('alertas')->total());
        $superadmin = $this->actor('SUPERADMINISTRADOR');
        $superadmin->assignRole('ENFERMEROS');
        Livewire::flushState();
        $panel = Livewire::test(AlertasPanel::class)->assertSee('Procedencia de las alertas');
        $this->assertSame('auditoria', $panel->viewData('perfil')['foco']);
        $this->assertSame(4, array_sum(array_column($panel->viewData('resumenEquipo')['fuentes'], 'total')));
        $this->assertNull($panel->viewData('contextoEspecialidad'));
        $this->assertSame([], app(ConsultarResumenEquipoAlertas::class)->consultar($this->actor('NUTRICIONISTA')));
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_previsualizacion_permite_explorar_grafo_y_ver_campos_bloqueados_sin_guardar(): void
    {
        $superadmin = $this->actor('SUPERADMINISTRADOR');
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $alerta = $this->alerta($residente);
        $enfermera = $this->actor('ENFERMEROS');
        $alerta->update(['cod_personal_responsable' => $enfermera->personal->getKey()]);
        $this->actingAs($superadmin);
        $rolesOriginales = $superadmin->getRoleNames()->all();
        $this->withSession([RolePreviewService::SESSION_KEY => 'ENFERMEROS']);
        $panel = Livewire::test(AlertasPanel::class)->assertSee('Ruta del cuidado')
            ->assertSee('data-graph-readonly="true"', false)->call('verDetalle', $alerta->getKey())
            ->assertSee('data-readonly-role-fields="turno"', false)->assertSee('Nota de intervención')->assertSee('Justificación del cierre');
        $this->assertStringContainsString('<fieldset disabled', $panel->html());
        $panel->call('guardarAtencion')->assertForbidden();
        Livewire::flushState();
        $panel = Livewire::test(AlertasPanel::class)->call('abrirCrear')
            ->assertSee('Tipo de alerta')->assertSee('Origen del evento')->assertSee('Prioridad registrada')
            ->assertSee('Registro del aviso bloqueado · solo lectura')->assertSet('solicitudAlta', null);
        $this->assertStringContainsString('<fieldset disabled', $panel->html());
        $panel->call('guardarAlerta')->assertForbidden();
        $this->withSession([RolePreviewService::SESSION_KEY => 'NUTRICIONISTA']);
        Livewire::flushState();
        $panel = Livewire::test(AlertasPanel::class)->call('abrirRegistroEspecialidad', $alerta->getKey())
            ->assertSee('Requerimiento hídrico (mL por día)')->assertSee('Guardado bloqueado · solo lectura')
            ->assertSee('Tipo de atención realizada')->assertSee('Motivo de la atención');
        $this->assertStringContainsString('<fieldset disabled', $panel->html());
        $panel->call('guardarRegistroEspecialidad')->assertForbidden();
        $this->assertSame($rolesOriginales, $superadmin->fresh()->getRoleNames()->all());
        $this->assertDatabaseCount('eventos_alerta', 0);
        $this->assertDatabaseCount('valoraciones_nutricionales', 0);
        $this->assertDatabaseCount('notas_clinicas', 0);
    }
}
