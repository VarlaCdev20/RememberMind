<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\RegistroConductual;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarCuidadosHttp;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarConducta;
use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultarConducta;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\MisPacientes;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\RegistroConducta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class RegistroConductaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermera;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfDay()->addHours(10));
        $this->seed(RolesAndPermissionsSeeder::class);
        config(['remembermind.superadmin_clinical_write' => false]);
        $this->enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermera->assignRole('ENFERMEROS');
        Personal::create(['cod_personal' => 'PER_CONDUCTA', 'cod_usuario' => $this->enfermera->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintética', 'numero_documento' => 'CONDUCTA-SYNTH', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        Area::create(['cod_area' => 'ARE_CONDUCTA', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        Turno::create(['cod_turno' => 'TUR_CONDUCTA', 'nombre' => 'Mañana sintética', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        Jornada::create(['cod_jornada' => 'JOR_CONDUCTA', 'cod_turno' => 'TUR_CONDUCTA', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_CONDUCTA', 'cod_personal' => 'PER_CONDUCTA', 'cod_jornada' => 'JOR_CONDUCTA', 'cod_area' => 'ARE_CONDUCTA', 'funcion' => 'ENFERMERA', 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => today(), 'estado' => 'ACTIVO']);
        $this->residente = Residente::crearDesdeAdmision(['cod_residente' => 'RES_CONDUCTA', 'nombres' => 'Residente', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_CONDUCTA', 'cod_residente' => 'RES_CONDUCTA', 'cod_personal' => 'PER_CONDUCTA', 'cod_jornada' => 'JOR_CONDUCTA', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($this->enfermera);
    }

    private function registrar(array $datos = []): RegistroConductual
    {
        return app(RegistrarConducta::class)->ejecutar($this->enfermera, $this->residente, $datos);
    }

    public function test_registro_minimo_conserva_null_y_metadatos_del_servidor(): void
    {
        $registro = $this->registrar(['cod_personal' => 'FORJADO', 'cod_residente' => 'OTRO', 'cod_jornada' => 'OTRA', 'fecha_hora' => '1999-01-01', 'estado' => 'ANULADO']);
        foreach (RegistrarConducta::INDICADORES as $campo) {
            $this->assertNull($registro->fresh()->{$campo});
        }
        $this->assertDatabaseHas('registros_conductuales', ['cod_personal' => 'PER_CONDUCTA', 'cod_residente' => 'RES_CONDUCTA', 'cod_jornada' => 'JOR_CONDUCTA', 'estado' => 'VIGENTE']);
        $this->assertTrue($registro->fecha_hora->equalTo(now()));
        $this->assertDatabaseCount('registros_conductuales', 1);
        $this->assertDatabaseHas('activity_log', ['subject_id' => $registro->getKey(), 'causer_id' => $this->enfermera->getKey()]);
    }

    public function test_registro_completo_trim_y_booleanos_no_inferidos_desde_animo(): void
    {
        $registro = $this->registrar(['estado_animo' => '  ANSIOSO ', 'participacion' => ' Parcial ', 'apatia' => false, 'agitacion' => true, 'agresividad' => null, 'ansiedad' => false, 'aislamiento' => true, 'deambulacion' => false, 'cambio_conducta' => true, 'descripcion' => ' Contexto real ', 'intervencion' => " Reorientación\nAcompañamiento ", 'respuesta' => ' Sin cambios '])->fresh();
        $this->assertSame('ANSIOSO', $registro->estado_animo);
        $this->assertSame('Parcial', $registro->participacion);
        $this->assertSame('Contexto real', $registro->descripcion);
        $this->assertSame("Reorientación\nAcompañamiento", $registro->intervencion);
        $this->assertSame('Sin cambios', $registro->respuesta);
        $this->assertFalse($registro->ansiedad);
        $this->assertFalse($registro->apatia);
        $this->assertFalse($registro->deambulacion);
        $this->assertNull($registro->agresividad);
        $this->assertTrue($registro->agitacion);
        $this->assertTrue($registro->aislamiento);
        $this->assertTrue($registro->cambio_conducta);
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_cada_confirmacion_crea_historia_nueva_y_http_usa_la_misma_operacion(): void
    {
        $primero = $this->registrar(['descripcion' => 'Registro original']);
        $this->travel(1)->minutes();
        $segundo = app(GestionarCuidadosHttp::class)->registrar($this->enfermera, ['participacion' => 'Activa', 'cambio_conducta' => false], $this->residente, 'conducta');
        $this->assertNotSame($primero->getKey(), $segundo->getKey());
        $this->assertSame('Registro original', $primero->fresh()->descripcion);
        $this->assertSame('Activa', $segundo->participacion);
        $this->assertFalse($segundo->cambio_conducta);
        $this->assertDatabaseCount('registros_conductuales', 2);
        $this->assertSame([$segundo->getKey(), $primero->getKey()], array_column(app(ConsultarConducta::class)->ejecutar($this->enfermera, 'RES_CONDUCTA')['historial'], 'codigo'));
    }

    public static function entradasInvalidas(): array
    {
        return [['estado_animo', str_repeat('x', 41)], ['participacion', str_repeat('x', 31)], ['agitacion', 'quizá'], ['descripcion', ['texto']], ['cambio_conducta', 4]];
    }

    #[DataProvider('entradasInvalidas')]
    public function test_entrada_invalida_no_persiste(string $campo, mixed $valor): void
    {
        try {
            $this->registrar([$campo => $valor]);
            $this->fail('Debe rechazar la captura inválida');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey($campo, $error->errors());
        }
        $this->assertDatabaseCount('registros_conductuales', 0);
    }

    public function test_texto_vacio_y_booleano_vacio_se_normalizan_a_null(): void
    {
        $registro = $this->registrar(['descripcion' => '   ', 'estado_animo' => '', 'agitacion' => '', 'ansiedad' => '0'])->fresh();
        $this->assertNull($registro->descripcion);
        $this->assertNull($registro->estado_animo);
        $this->assertNull($registro->agitacion);
        $this->assertFalse($registro->ansiedad);
    }

    public function test_consulta_no_mezcla_residentes_anulados_ni_fechas_ajenas_al_periodo(): void
    {
        $actual = $this->registrar(['descripcion' => 'Propio visible', 'ansiedad' => true]);
        Residente::crearDesdeAdmision(['cod_residente' => 'RES_AJENA', 'nombres' => 'Otra', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        foreach ([['RCO_AJENO', 'RES_AJENA', now(), 'VIGENTE'], ['RCO_ANULADO', 'RES_CONDUCTA', now(), 'ANULADO'], ['RCO_HISTORICO', 'RES_CONDUCTA', now()->subDays(20), 'VIGENTE']] as [$codigo, $residente, $fecha, $estado]) {
            RegistroConductual::create(['cod_registro_conductual' => $codigo, 'cod_residente' => $residente, 'cod_personal' => 'PER_CONDUCTA', 'fecha_hora' => $fecha, 'estado' => $estado, 'agitacion' => true]);
        }
        $datos = app(ConsultarConducta::class)->ejecutar($this->enfermera, 'RES_CONDUCTA');
        $this->assertSame(1, $datos['resumen']['total']);
        $this->assertCount(1, $datos['series']);
        $this->assertSame([$actual->getKey(), 'RCO_HISTORICO'], array_column($datos['historial'], 'codigo'));
        $this->assertSame(2, app(ConsultarConducta::class)->ejecutar($this->enfermera, 'RES_CONDUCTA', 30)['resumen']['total']);
        for ($i = 1; $i <= 13; $i++) {
            RegistroConductual::create(['cod_registro_conductual' => 'RCO_DENSE_'.$i, 'cod_residente' => 'RES_CONDUCTA', 'cod_personal' => 'PER_CONDUCTA', 'fecha_hora' => now()->subDays($i), 'estado' => 'VIGENTE']);
        }
        $test = Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_CONDUCTA', 'emergente' => true])->set('dias', 30)->assertSeeHtml('rm-conducta__line-path')->assertDontSeeHtml('rm-conducta__chart-bar');
        $this->exportarQa('dense', $test->html(), $test->snapshot);
    }

    public function test_limpiar_captura_no_cambia_historia_guardada(): void
    {
        $registro = $this->registrar(['descripcion' => 'Original']);
        Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_CONDUCTA', 'emergente' => true])
            ->set('datos.descripcion', 'Pendiente')->set('datos.agitacion', true)->call('limpiarCaptura')
            ->assertSet('datos.descripcion', null)->assertSet('datos.agitacion', null)->assertDispatched('conducta-captura-reiniciada');
        $this->assertSame('Original', $registro->fresh()->descripcion);
        $this->assertDatabaseCount('registros_conductuales', 1);
    }

    public function test_atencion_de_otro_residente_no_se_vincula(): void
    {
        Residente::crearDesdeAdmision(['cod_residente' => 'RES_OTRO', 'nombres' => 'Otra', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        Atencion::create(['cod_atencion' => 'ATE_OTRO', 'cod_residente' => 'RES_OTRO', 'cod_personal' => 'PER_CONDUCTA', 'cod_area' => 'ARE_CONDUCTA', 'tipo_atencion' => 'ENFERMERIA', 'fecha_hora' => now(), 'estado' => 'REGISTRADA']);
        try {
            $this->registrar(['cod_atencion' => 'ATE_OTRO']);
            $this->fail('Debe rechazar una atención ajena');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('cod_atencion', $error->errors());
        }
        $this->assertDatabaseCount('registros_conductuales', 0);
    }

    public static function contextosDenegados(): array
    {
        return [['sin_permiso'], ['sin_asignacion'], ['fuera_turno'], ['cuenta_inactiva'], ['personal_inactivo'], ['residente_egresado'], ['superadmin']];
    }

    #[DataProvider('contextosDenegados')]
    public function test_escritura_revalida_contexto_y_no_persiste(string $caso): void
    {
        match ($caso) {
            'sin_permiso' => $this->enfermera->roles->first()->revokePermissionTo('registros_conductuales.crear'),
            'sin_asignacion' => AsignacionResidenteJornada::query()->update(['estado' => 'INACTIVA']),
            'fuera_turno' => $this->travelTo(now()->startOfDay()->addHours(22)),
            'cuenta_inactiva' => $this->enfermera->update(['estado' => 'INACTIVO']),
            'personal_inactivo' => Personal::query()->update(['estado' => 'INACTIVO']),
            'residente_egresado' => $this->residente->update(['estado' => 'EGRESADO']),
            'superadmin' => $this->enfermera->syncRoles(['SUPERADMINISTRADOR']),
        };
        try {
            $this->registrar(['descripcion' => 'No debe guardarse']);
            $this->fail('El contexto no autoriza esta escritura');
        } catch (HttpExceptionInterface $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertDatabaseCount('registros_conductuales', 0);
    }

    public function test_lectura_ajena_denegada_y_fuera_turno_solo_asignados(): void
    {
        Residente::crearDesdeAdmision(['cod_residente' => 'RES_OTRO', 'nombres' => 'Otra', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_OTRO', 'emergente' => true])->assertForbidden();
        $this->travelTo(now()->startOfDay()->addHours(22));
        Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_CONDUCTA', 'emergente' => true])->assertSee('Solo consulta')->assertDontSee('Confirmar y registrar')->call('guardar')->assertForbidden();
        $this->assertDatabaseCount('registros_conductuales', 0);
    }

    public function test_popup_conserva_residente_y_devuelve_al_selector(): void
    {
        Livewire::test(MisPacientes::class)->call('seleccionarResidente', 'RES_CONDUCTA')->call('mostrarSelectorRegistro')
            ->assertSeeHtml("abrirFormularioRegistro('conducta')")
            ->call('abrirFormularioRegistro', 'conducta')->assertSet('registroTipo', 'conducta')->assertSee('Registro de conducta')->assertSee('Sin datos en este periodo')
            ->call('cerrarPopupConducta')->assertSet('registroTipo', null)->assertSet('drawerPaso', 'register-selector');
        $this->assertDatabaseCount('registros_conductuales', 0);
    }

    public function test_volver_al_residente_refresca_el_seguimiento_confirmado(): void
    {
        $test = Livewire::test(MisPacientes::class)->call('seleccionarResidente', 'RES_CONDUCTA')->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', 'conducta');
        $this->registrar(['descripcion' => 'Conducta confirmada sintética']);
        $test->call('cerrarPopupConducta', true)->assertSet('mostrarPanelDetalle', true)->assertSet('mostrarSelectorModal', false)
            ->assertSee('Registro de conducta')->assertSee('Conducta confirmada sintética');
        $this->assertDatabaseCount('registros_conductuales', 1);
    }

    public function test_livewire_validacion_preserva_datos_y_confirmacion_impide_doble_envio(): void
    {
        $test = Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_CONDUCTA', 'emergente' => true])
            ->set('datos.descripcion', 'Captura pendiente')->set('datos.estado_animo', str_repeat('x', 41))->call('guardar')
            ->assertHasErrors('datos.estado_animo')->assertSet('mostrarFormulario', true)->assertSet('resultado', [])->assertSet('datos.descripcion', 'Captura pendiente')->assertNotDispatched('conducta-registrada');
        $this->assertDatabaseCount('registros_conductuales', 0);
        $test->set('datos.estado_animo', 'TRANQUILO')->call('guardar')->assertHasNoErrors()->assertDispatched('conducta-registrada')->assertSee('Conducta registrada correctamente')->assertSet('datos.descripcion', null);
        $this->assertDatabaseCount('registros_conductuales', 1);
        $test->call('guardar')->assertStatus(409);
        $this->assertDatabaseCount('registros_conductuales', 1);
    }

    public function test_fallo_real_de_persistencia_conserva_captura_sin_exito_falso(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE TRIGGER conducta_testing_failure BEFORE INSERT ON registros_conductuales BEGIN SELECT RAISE(ABORT, 'synthetic persistence failure'); END");
        } else {
            DB::statement("CREATE FUNCTION conducta_testing_failure() RETURNS trigger AS $$ BEGIN RAISE EXCEPTION 'synthetic persistence failure'; END; $$ LANGUAGE plpgsql");
            DB::statement('CREATE TRIGGER conducta_testing_failure BEFORE INSERT ON registros_conductuales FOR EACH ROW EXECUTE FUNCTION conducta_testing_failure()');
        }
        try {
            Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_CONDUCTA', 'emergente' => true])
                ->set('datos.descripcion', 'Conservar esta captura')->set('datos.agitacion', true)->call('guardar')
                ->assertHasErrors('persistencia')->assertSet('datos.descripcion', 'Conservar esta captura')->assertSet('datos.agitacion', true)
                ->assertSet('mostrarFormulario', true)->assertSet('resultado', [])->assertNotDispatched('conducta-registrada')->assertSee('Tus datos siguen aquí')->assertDontSee('synthetic persistence failure');
            $this->assertDatabaseCount('registros_conductuales', 0);
            $this->assertDatabaseMissing('activity_log', ['log_name' => 'conducta']);
        } finally {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP TRIGGER conducta_testing_failure');
            } else {
                DB::statement('DROP TRIGGER conducta_testing_failure ON registros_conductuales');
                DB::statement('DROP FUNCTION conducta_testing_failure()');
            }
        }
    }

    public function test_grafica_cero_uno_y_conteos_factuales_sin_puntos_ficticios(): void
    {
        $consulta = app(ConsultarConducta::class);
        $this->assertSame([], $consulta->ejecutar($this->enfermera, 'RES_CONDUCTA')['series']);
        $componente = Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_CONDUCTA', 'emergente' => true])
            ->assertSee('Registros de conducta por fecha')->assertSee('Este conteo no mide intensidad, gravedad ni mejoría');
        $this->exportarQa('empty', $componente->html());
        $this->registrar(['agitacion' => true, 'ansiedad' => false]);
        $datos = $consulta->ejecutar($this->enfermera, 'RES_CONDUCTA');
        $this->assertCount(1, $datos['series']);
        $this->assertSame(['fecha' => today()->toDateString(), 'registros' => 1, 'observadas' => ['Agitación']], $datos['series'][0]);
        $this->assertSame(['total' => 1, 'agitacion' => 1, 'ansiedad' => 0, 'cambios' => 0], $datos['resumen']);
        $this->exportarQa('one', Livewire::test(RegistroConducta::class, ['codResidente' => 'RES_CONDUCTA', 'emergente' => true])->html());
        $this->registrar(['ansiedad' => true, 'cambio_conducta' => true]);
        $this->assertSame(2, $consulta->ejecutar($this->enfermera, 'RES_CONDUCTA')['series'][0]['registros']);
        $this->assertCount(1, $consulta->ejecutar($this->enfermera, 'RES_CONDUCTA', 7, 'ansiedad')['historial']);
        $this->assertCount(2, $consulta->ejecutar($this->enfermera, 'RES_CONDUCTA')['historial']);
    }

    private function exportarQa(string $estado, string $html, ?array $snapshot = null): void
    {
        if ($directorio = getenv('RM_CONDUCTA_QA_DIR')) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            file_put_contents($directorio.'/conducta-'.$estado.'.html', $html);
            if ($snapshot !== null) {
                file_put_contents($directorio.'/conducta-'.$estado.'.state.json', json_encode($snapshot, JSON_THROW_ON_ERROR));
            }
        }
    }
}
