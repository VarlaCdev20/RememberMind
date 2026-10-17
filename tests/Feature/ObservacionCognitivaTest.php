<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarCuidadosHttp;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarObservacionCognitiva;
use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultarObservacionCognitiva;
use App\Modulos\Cuidados\Dominio\ObservacionCognitiva as Contrato;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\MisPacientes;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\ObservacionCognitiva;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class ObservacionCognitivaTest extends TestCase
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
        Personal::create(['cod_personal' => 'PER_COG', 'cod_usuario' => $this->enfermera->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintética', 'numero_documento' => 'COG-SYNTH', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        Area::create(['cod_area' => 'ARE_COG', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        Turno::create(['cod_turno' => 'TUR_COG', 'nombre' => 'Mañana sintética', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        Jornada::create(['cod_jornada' => 'JOR_COG', 'cod_turno' => 'TUR_COG', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_COG', 'cod_personal' => 'PER_COG', 'cod_jornada' => 'JOR_COG', 'cod_area' => 'ARE_COG', 'funcion' => 'ENFERMERA', 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => today(), 'estado' => 'ACTIVO']);
        $this->residente = Residente::crearDesdeAdmision(['cod_residente' => 'RES_COG', 'nombres' => 'Residente', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_COG', 'cod_residente' => 'RES_COG', 'cod_personal' => 'PER_COG', 'cod_jornada' => 'JOR_COG', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($this->enfermera);
    }

    private function registrar(array $datos, ?string $captura = null): ControlCognitivo
    {
        return app(RegistrarObservacionCognitiva::class)->ejecutar($this->enfermera, $this->residente, $datos, $captura);
    }

    private function consulta(array $filtros = []): array
    {
        return app(ConsultarObservacionCognitiva::class)->ejecutar($this->enfermera, 'RES_COG', [...['dias' => 30, 'desde' => null, 'hasta' => null, 'dominio' => '', 'orden' => 'desc', 'pagina' => 1], ...$filtros]);
    }

    private function exportar(string $nombre, $test): void
    {
        if ($directorio = getenv('RM_COGNITION_QA_DIR')) {
            if (! is_dir($directorio)) { mkdir($directorio, 0777, true); }
            file_put_contents($directorio.'/cognicion-'.$nombre.'.html', $test->html());
            file_put_contents($directorio.'/cognicion-'.$nombre.'.state.json', json_encode($test->snapshot, JSON_THROW_ON_ERROR));
        }
    }

    public function test_guarda_los_quince_indicadores_sin_perder_false_y_null_y_deriva_autoria(): void
    {
        $datos = [];
        foreach (Contrato::TEXTOS as $campo) { $datos[$campo] = array_key_first(Contrato::opciones($campo)); }
        foreach (Contrato::SENALES as $i => $campo) { $datos[$campo] = [true, false, null][$i % 3]; }
        $datos += ['cod_personal' => 'FALSO', 'cod_jornada' => 'AJENA', 'cod_residente' => 'OTRO', 'fecha_hora' => '1999-01-01', 'estado' => 'ANULADO', 'observacion' => '  Registro objetivo  '];
        $registro = app(GestionarCuidadosHttp::class)->registrar($this->enfermera, $datos, $this->residente, 'control-cognitivo')->fresh();
        foreach ([...Contrato::TEXTOS, ...Contrato::SENALES] as $campo) { $this->assertSame($datos[$campo], $registro->{$campo}); }
        $this->assertSame('PER_COG', $registro->cod_personal);
        $this->assertSame('JOR_COG', $registro->cod_jornada);
        $this->assertSame('RES_COG', $registro->cod_residente);
        $this->assertSame('VIGENTE', $registro->estado);
        $this->assertSame('Registro objetivo', $registro->observacion);
        $this->assertTrue($registro->fecha_hora->equalTo(now()));
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('diagnosticos', 0);
    }

    public static function invalidos(): array
    {
        return [['observacion', '   '], ['orientacion_persona', 'NORMAL'], ['memoria_reciente', str_repeat('x', 31)], ['sigue_instrucciones', 'quizá'], ['observacion', ['objeto']]];
    }

    #[DataProvider('invalidos')]
    public function test_rechaza_vacio_y_datos_invalidos_sin_escribir(string $campo, mixed $valor): void
    {
        try { $this->registrar([$campo => $valor]); $this->fail('Debe validar'); }
        catch (ValidationException $error) { $this->assertArrayHasKey($campo, $error->errors()); }
        $this->assertDatabaseCount('controles_cognitivos', 0);
    }

    public function test_una_respuesta_no_es_significativa_y_no_observado_es_null(): void
    {
        $registro = $this->registrar(['orientacion_persona' => '', 'sigue_instrucciones' => '0'])->fresh();
        $this->assertNull($registro->orientacion_persona);
        $this->assertFalse($registro->sigue_instrucciones);
        foreach (array_diff(Contrato::SENALES, ['sigue_instrucciones']) as $campo) { $this->assertNull($registro->{$campo}); }
    }

    public function test_captura_repetida_es_idempotente_y_nueva_captura_conserva_historia(): void
    {
        $primero = $this->registrar(['observacion' => 'Original'], 'COD_CCO_000001');
        $repetido = $this->registrar(['observacion' => 'Original'], 'COD_CCO_000001');
        $this->assertSame($primero->getKey(), $repetido->getKey());
        $this->assertDatabaseCount('controles_cognitivos', 1);
        $this->registrar(['observacion' => 'Nuevo seguimiento']);
        $this->assertDatabaseCount('controles_cognitivos', 2);
        $this->assertSame('Original', $primero->fresh()->observacion);
    }

    public function test_atencion_ajena_es_rechazada(): void
    {
        $otro = Residente::crearDesdeAdmision(['cod_residente' => 'RES_OTRA', 'nombres' => 'Otra', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        Atencion::create(['cod_atencion' => 'ATE_OTRA', 'cod_residente' => $otro->getKey(), 'cod_personal' => 'PER_COG', 'cod_area' => 'ARE_COG', 'tipo_atencion' => 'ENFERMERIA', 'fecha_hora' => now(), 'estado' => 'REGISTRADA']);
        try { $this->registrar(['observacion' => 'No debe persistir', 'cod_atencion' => 'ATE_OTRA']); $this->fail('Debe rechazar atención ajena'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('cod_atencion', $e->errors()); }
        $this->assertDatabaseCount('controles_cognitivos', 0);
    }

    public static function denegados(): array
    {
        return [['sin_permiso'], ['sin_asignacion'], ['fuera_turno'], ['inactivo'], ['personal_inactivo'], ['egresado'], ['superadmin']];
    }

    #[DataProvider('denegados')]
    public function test_contexto_de_escritura_se_revalida(string $caso): void
    {
        match ($caso) {
            'sin_permiso' => $this->enfermera->roles->first()->revokePermissionTo('controles_cognitivos.crear'),
            'sin_asignacion' => AsignacionResidenteJornada::whereKey('ARJ_COG')->update(['estado' => 'CERRADA']),
            'fuera_turno' => $this->travelTo(now()->startOfDay()->addHours(22)),
            'inactivo' => $this->enfermera->update(['estado' => 'INACTIVO']),
            'personal_inactivo' => Personal::whereKey('PER_COG')->update(['estado' => 'INACTIVO']),
            'egresado' => $this->residente->update(['estado' => 'EGRESADO']),
            'superadmin' => $this->enfermera->syncRoles(['SUPERADMINISTRADOR']),
        };
        try { $this->registrar(['observacion' => 'Rechazar']); $this->fail('Debe denegar'); }
        catch (HttpExceptionInterface $e) { $this->assertSame(403, $e->getStatusCode()); }
        $this->assertDatabaseCount('controles_cognitivos', 0);
    }

    public function test_lectura_fuera_de_turno_solo_residente_propio_y_superadmin_global(): void
    {
        $this->registrar(['observacion' => 'Propio']);
        $this->travelTo(now()->startOfDay()->addHours(22));
        $this->assertCount(1, $this->consulta()['series']);
        Residente::crearDesdeAdmision(['cod_residente' => 'RES_AJENA', 'nombres' => 'Otra', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        try { app(ConsultarObservacionCognitiva::class)->ejecutar($this->enfermera, 'RES_AJENA'); $this->fail('Debe denegar'); }
        catch (HttpExceptionInterface $e) { $this->assertSame(403, $e->getStatusCode()); }
        $this->enfermera->syncRoles(['SUPERADMINISTRADOR']);
        $this->assertCount(1, $this->consulta()['series']);
        $this->enfermera->syncRoles(['FAMILIAR']);
        $this->enfermera->givePermissionTo('controles_cognitivos.ver');
        try { $this->consulta(); $this->fail('Familia no consulta por defecto'); }
        catch (HttpExceptionInterface $e) { $this->assertSame(403, $e->getStatusCode()); }
    }

    public function test_historia_real_periodo_dominio_orden_y_paginacion_sin_escrituras(): void
    {
        $primero = $this->registrar(['memoria_reciente' => 'CONSERVADA']);
        $this->travel(1)->minutes();
        $segundo = $this->registrar(['confusion' => false]);
        $this->assertSame([$segundo->getKey(), $primero->getKey()], array_column($this->consulta()['historial'], 'codigo'));
        $this->assertSame([$primero->getKey()], array_column($this->consulta(['dominio' => 'memoria'])['historial'], 'codigo'));
        $this->assertSame([$primero->getKey(), $segundo->getKey()], array_column($this->consulta(['orden' => 'asc'])['historial'], 'codigo'));
        $this->assertCount(2, $this->consulta(['dias' => 0, 'desde' => today()->toDateString(), 'hasta' => today()->toDateString()])['series']);
        $this->assertStringContainsString('Conservada', $this->consulta()['resumenDominios']['memoria']['resumen']);
        $this->assertNull($this->consulta()['resumenDominios']['lenguaje']);
        $this->assertDatabaseCount('controles_cognitivos', 2);
        for ($i = 0; $i < 13; $i++) { $this->registrar(['observacion' => 'Registro sintético '.$i]); }
        $this->assertCount(12, $this->consulta()['historial']);
        $this->assertCount(3, $this->consulta(['pagina' => 2])['historial']);
    }

    public function test_permiso_de_lectura_revocado_y_cuenta_inactiva_no_exponen_historia(): void
    {
        $this->registrar(['observacion' => 'Historia protegida']);
        $this->enfermera->roles->first()->revokePermissionTo('controles_cognitivos.ver');
        try { $this->consulta(); $this->fail('Debe denegar permiso revocado'); }
        catch (HttpExceptionInterface $e) { $this->assertSame(403, $e->getStatusCode()); }
        $this->enfermera->givePermissionTo('controles_cognitivos.ver');
        $this->enfermera->update(['estado' => 'INACTIVO']);
        Livewire::test(ObservacionCognitiva::class, ['codResidente' => 'RES_COG'])->assertForbidden();
        $this->assertDatabaseCount('controles_cognitivos', 1);
    }

    public function test_filtros_invalidos_no_borran_captura_y_tienen_recuperacion(): void
    {
        Livewire::test(ObservacionCognitiva::class, ['codResidente' => 'RES_COG'])->set('datos.confusion', false)
            ->set('datos.observacion', 'Descripción pendiente')->set('dias', 0)->set('desde', 'fecha inválida')
            ->assertSee('Revisa los filtros')->assertSet('datos.confusion', false)->assertSet('datos.observacion', 'Descripción pendiente')
            ->set('desde', today()->toDateString())->assertDontSee('Revisa los filtros')->assertHasNoErrors('desde');
        $this->assertDatabaseCount('controles_cognitivos', 0);
    }

    public function test_modal_vacio_un_historico_guardado_limpieza_y_error_conservan_datos(): void
    {
        $vacio = Livewire::test(ObservacionCognitiva::class, ['codResidente' => 'RES_COG'])->assertSee('Observación cognitiva')->assertSee('Todavía no existen observaciones cognitivas');
        $this->exportar('empty', $vacio);
        $this->assertDatabaseCount('controles_cognitivos', 0);
        $vacio->set('datos.observacion', ' ')->call('guardar')->assertHasErrors('datos.observacion');
        $this->registrar(['memoria_reciente' => 'CONSERVADA']);
        $uno = Livewire::test(ObservacionCognitiva::class, ['codResidente' => 'RES_COG'])->assertSee('Un registro guardado')->set('datos.confusion', true)->set('datos.observacion', 'Captura pendiente');
        $this->exportar('one', $uno);
        $uno->call('limpiarCaptura')->assertSet('datos.confusion', null)->assertSet('datos.observacion', null);
        $this->assertDatabaseCount('controles_cognitivos', 1);
        $dispatcher = clone ControlCognitivo::getEventDispatcher();
        ControlCognitivo::created(fn () => throw new \RuntimeException('Fallo controlado sintético'));
        try {
            $uno->set('datos.observacion', 'Conservar texto')->call('guardar')->assertHasErrors('persistencia')->assertSet('datos.observacion', 'Conservar texto')->assertSet('resultado', [])->assertSet('mostrarFormulario', true);
            $this->exportar('error', $uno);
            $this->assertDatabaseCount('controles_cognitivos', 1);
        } finally { ControlCognitivo::setEventDispatcher($dispatcher); }
    }

    public function test_error_real_transaccional_no_deja_fila_ni_exito(): void
    {
        $dispatcher = clone ControlCognitivo::getEventDispatcher();
        ControlCognitivo::created(fn () => throw new \RuntimeException('Fallo después de insert en testing'));
        try {
            Livewire::test(ObservacionCognitiva::class, ['codResidente' => 'RES_COG'])->set('datos.confusion', false)->set('datos.observacion', 'Dato conservado')->call('guardar')->assertHasErrors('persistencia')->assertSet('datos.confusion', false)->assertSet('datos.observacion', 'Dato conservado')->assertSet('resultado', []);
            $this->assertDatabaseCount('controles_cognitivos', 0);
        } finally { ControlCognitivo::setEventDispatcher($dispatcher); }
    }

    public function test_error_de_consulta_se_recupera_sin_perder_captura(): void
    {
        $this->registrar(['memoria_reciente' => 'CONSERVADA']);
        $dispatcher = clone ControlCognitivo::getEventDispatcher();
        ControlCognitivo::retrieved(fn () => throw new \RuntimeException('Fallo de consulta controlado'));
        try {
            $modal = Livewire::test(ObservacionCognitiva::class, ['codResidente' => 'RES_COG'])->assertSee('No se pudo consultar el historial')
                ->set('datos.observacion', 'Pendiente')->set('datos.confusion', false);
        } finally { ControlCognitivo::setEventDispatcher($dispatcher); }
        $modal->call('reintentarConsulta')->assertDontSee('No se pudo consultar el historial')->assertSet('datos.observacion', 'Pendiente')->assertSet('datos.confusion', false);
        $this->assertDatabaseCount('controles_cognitivos', 1);
    }

    public function test_guardado_livewire_confirmado_y_selector_contextual(): void
    {
        $modal = Livewire::test(ObservacionCognitiva::class, ['codResidente' => 'RES_COG'])->set('datos.sigue_instrucciones', false)->call('guardar')->assertDispatched('cognicion-registrada')->assertSee('Observación cognitiva registrada')->assertDontSeeHtml('id="cognicion-form"');
        $this->assertDatabaseCount('controles_cognitivos', 1);
        $modal->assertSee('Registro confirmado')->assertDontSee('Sin guardar · Respuestas seleccionadas')
            ->assertDontSee('Captura pendiente · No forma parte del historial');
        $modal->call('guardar')->assertStatus(409);
        $this->assertDatabaseCount('controles_cognitivos', 1);
        Livewire::test(MisPacientes::class)->call('seleccionarResidente', 'RES_COG')->call('mostrarSelectorRegistro')->call('abrirFormularioRegistro', 'cognicion')->assertSet('registroTipo', 'cognicion')->assertSee('Observación cognitiva')->call('cerrarPopupCognicion')->assertSet('drawerPaso', 'register-selector');
    }
}
