<?php

namespace Tests\Feature;

use App\Models\AdministracionMedicacion;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Medicacion\Aplicacion\Acciones\RegistrarAdministracionMedicacionService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use App\Modulos\Medicacion\Infraestructura\Archivos\ImagenMedicamento;
use App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\AdministracionMedicacionModal;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludAdministracionMedicacionPanel;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class AdministracionMedicacionFormularioTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;

    private Residente $residente;

    private Prescripcion $prescripcion;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-14 09:00:00');
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Vargas',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->residente = Residente::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'Mario',
            'ap_paterno' => 'Gutierrez',
            'ap_materno' => 'Mendoza',
        ]);

        $turno = Turno::create([
            'cod_turno' => 'TUR_TEST_MED',
            'nombre' => 'Turno clínico',
            'orden' => 1,
            'hora_inicio' => '00:00:00',
            'hora_fin' => '23:59:59',
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_TEST_MED',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_TEST_MED',
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nivel_supervision' => 'DIRECTA',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        Area::firstOrCreate(
            ['cod_area' => 'COD_ARE_000001'],
            [
                'nombre' => 'Enfermeria Clinica',
                'descripcion' => 'Area de atencion y cuidados',
                'estado' => 'ACTIVO',
            ]
        );

        $med = Medicamento::firstOrCreate(
            ['cod_medicamento' => 'MED_TEST_01'],
            [
                'nombre_generico' => 'Losartan Potasico',
                'nombre_comercial' => 'Losartan 50mg',
                'concentracion' => '50 mg',
                'forma_farmaceutica' => 'Comprimido',
                'unidad' => 'mg',
                'via_predeterminada' => 'ORAL',
                'control_especial' => false,
                'estado' => 'ACTIVO',
            ]
        );

        $atencion = Atencion::firstOrCreate(
            ['cod_atencion' => 'ATN_TEST_01'],
            [
                'cod_residente' => $this->residente->cod_residente,
                'cod_area' => 'COD_ARE_000001',
                'cod_personal' => $this->enfermero->personal->cod_personal,
                'tipo_atencion' => 'CONTROL_MEDICO',
                'motivo' => 'Control rutinario',
                'fecha_hora' => now(),
                'estado' => 'COMPLETADA',
            ]
        );

        $this->prescripcion = Prescripcion::firstOrCreate(
            ['cod_prescripcion' => 'PRS_TEST_01'],
            [
                'cod_residente' => $this->residente->cod_residente,
                'cod_atencion' => $atencion->cod_atencion,
                'cod_medicamento' => $med->cod_medicamento,
                'cod_personal' => $this->enfermero->personal->cod_personal,
                'dosis' => 50.000,
                'unidad_dosis' => 'mg',
                'via_administracion' => 'Oral',
                'frecuencia' => 'Cada 12 horas',
                'indicacion' => 'Antihipertensivo post-desayuno',
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => now(),
                'estado' => 'ACTIVA',
            ]
        );

        HorarioPrescripcion::firstOrCreate(
            ['cod_horario_prescripcion' => 'HPR_TEST_01'],
            [
                'cod_prescripcion' => 'PRS_TEST_01',
                'hora_programada' => '08:00:00',
                'dosis_programada' => 50.000,
                'dias_semana' => 'TODOS',
                'estado' => 'ACTIVO',
            ]
        );

        $this->actingAs($this->enfermero);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function formulario()
    {
        return Livewire::test(AdministracionMedicacionModal::class)
            ->call('abrirModalAdministracion', $this->residente->getKey());
    }

    public function test_kardex_de_hoy_incluye_ordenes_del_dia_y_usa_la_hora_actual(): void
    {
        $this->prescripcion->update(['fecha_hora_prescripcion' => today()->setTime(7, 0)]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_KARDEX_TARDE',
            'cod_prescripcion' => $this->prescripcion->getKey(),
            'hora_programada' => '20:00:00',
            'dosis_programada' => 50,
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->residente])
            ->assertViewHas('conteoRetrasadas', 1)
            ->assertViewHas('conteoPendientes', 1)
            ->assertViewHas('dosisHoy', fn ($dosis) => $dosis->firstWhere('hora', '08:00')['estado_raw'] === 'VENCIDA');
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    private function exportar(string $nombre, $formulario): void
    {
        if ($directorio = getenv('RM_MEDICATION_QA_DIR')) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0700, true);
            }
            file_put_contents($directorio.'/medicacion-'.$nombre.'.html', $formulario->html());
            file_put_contents($directorio.'/medicacion-'.$nombre.'.state.json', json_encode($formulario->snapshot, JSON_THROW_ON_ERROR));
        }
    }

    public function test_graficas_agenda_usan_las_dosis_autorizadas_y_los_mismos_filtros_del_listado(): void
    {
        $this->prescripcion->update(['fecha_hora_prescripcion' => today()->setTime(7, 0)]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_GRAFICA_TARDE',
            'cod_prescripcion' => $this->prescripcion->getKey(),
            'hora_programada' => '20:00:00',
            'dosis_programada' => 50,
            'estado' => 'ACTIVO',
        ]);

        $panel = Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->residente])
            ->assertViewHas('graficasAgenda', function ($datos) {
                return $datos['fecha'] === '2026-10-14' && $datos['total'] === 2
                    && array_sum(array_column($datos['estados'], 'cantidad')) === 2
                    && collect($datos['estados'])->firstWhere('label', 'Retrasadas')['cantidad'] === 1
                    && $datos['horarios'] === [['label' => '08:00', 'cantidad' => 1], ['label' => '20:00', 'cantidad' => 1]];
            })
            ->assertSee('Estado de las dosis')->assertSee('Dosis por horario')
            ->set('filtroKardexEstado', 'RETRASADA')
            ->assertViewHas('graficasAgenda', fn ($datos) => $datos['total'] === 1
                && array_sum(array_column($datos['estados'], 'cantidad')) === 1
                && $datos['horarios'] === [['label' => '08:00', 'cantidad' => 1]])
            ->assertViewHas('dosisHoy', fn ($dosis) => $dosis->count() === 1);

        $panel->set('filtroKardexBusqueda', 'Sin coincidencias sintéticas')
            ->assertViewHas('graficasAgenda', fn ($datos) => $datos['total'] === 0 && $datos['horarios'] === [])
            ->assertSee('Sin dosis para graficar')
            ->assertDontSee('id="medication-agenda-states"', false)
            ->call('resetFilters')
            ->assertViewHas('graficasAgenda', fn ($datos) => $datos['total'] === 2);

        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_estados_visuales_y_consultas_preservan_la_captura(): void
    {
        $form = $this->formulario();
        $this->exportar('initial', $form);
        $form->call('seleccionarDosis', 'HPR_TEST_01');
        $this->exportar('selected', $form);
        $form->call('verPrescripcion')->assertSet('mostrarPrescripcion', true);
        $this->exportar('prescription', $form);
        $form->call('cerrarPrescripcion')->assertSet('mostrarPrescripcion', false);
        $form->set('resultado', 'ADMINISTRADA')->set('hora_real', '08:10')->set('dosis_administrada', '50');
        $this->exportar('admin', $form);
        $form->call('guardar')->assertHasNoErrors();
        $this->exportar('saved', $form);
        $form->call('abrirHistorial')->call('abrirEvolucion')->assertSet('mostrarHistorial', true)->assertSet('mostrarEvolucion', true);
        $this->exportar('windows', $form);
        HorarioPrescripcion::create(['cod_horario_prescripcion' => 'HPR_VISUAL_02', 'cod_prescripcion' => $this->prescripcion->getKey(), 'hora_programada' => '20:00', 'dosis_programada' => 50, 'estado' => 'ACTIVO']);
        $form = $this->formulario()->call('seleccionarDosis', 'HPR_VISUAL_02')->set('resultado', 'OMITIDA')->set('motivo_omision', 'Rechazo observado');
        $this->exportar('omitted', $form);
        $form->call('abrirHistorial')->call('abrirEvolucion')->assertSet('motivo_omision', 'Rechazo observado');
        $form->call('solicitarSalida', 'volver');
        $this->exportar('discard', $form);
        $form->call('cancelarConfirmacion')->set('motivo_omision', 'no')->call('guardar')->assertHasErrors('motivo_omision');
        $this->exportar('invalid', $form);
    }

    public function test_abre_sin_resultado_y_seleccion_no_persiste_ni_reserva_codigos(): void
    {
        $antes = DB::table('secuencias_codigos')->get()->toJson();
        $this->formulario()->assertSet('resultado', '')->assertSet('cod_horario_prescripcion', null)
            ->assertSee('Administración de medicación')->call('seleccionarDosis', 'HPR_TEST_01')
            ->assertSet('resultado', '')->assertSet('hora_real', '')->assertSet('dosis_administrada', '')
            ->assertSet('orden.dosis_programada', '50.000')->call('guardar')->assertHasErrors('resultado');
        $this->assertDatabaseCount('administraciones_medicacion', 0);
        $this->assertSame($antes, DB::table('secuencias_codigos')->get()->toJson());
    }

    public function test_administra_dosis_exacta_actualiza_lecturas_y_no_modifica_la_orden(): void
    {
        $antes = $this->prescripcion->fresh()->getAttributes();
        $form = $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')
            ->set('resultado', 'ADMINISTRADA')->set('hora_real', '08:10')->set('dosis_administrada', '49.125')
            ->set('efecto_observado', 'Respuesta observada de ejemplo')->set('reaccion_adversa', 'Reacción sospechada documentada')
            ->set('observacion', 'Registro sintético')->call('guardar')->assertHasNoErrors()->assertDispatched('medicacion-guardada')
            ->call('abrirHistorial')->assertSet('historial.total', 1)->call('abrirEvolucion')->assertSet('evolucion.administradas', 1);
        $registro = AdministracionMedicacion::firstOrFail();
        $this->assertSame('HPR_TEST_01', $registro->cod_horario_prescripcion);
        $this->assertSame('49.125', $registro->dosis_administrada);
        $this->assertSame($this->enfermero->personal->getKey(), $registro->cod_personal);
        $this->assertSame('REGISTRADA', $registro->estado);
        $this->assertSame($antes, $this->prescripcion->fresh()->getAttributes());
        $this->assertSame(1, Activity::where('subject_id', $registro->getKey())->where('log_name', 'Administración Medicación')->count());
        $form->set('resultado', 'ADMINISTRADA')->set('hora_real', '08:10')->set('dosis_administrada', '49.125')->call('guardar')->assertHasErrors();
        $this->assertDatabaseCount('administraciones_medicacion', 1);
    }

    public function test_omision_exige_motivo_y_limpia_campos_ocultos(): void
    {
        $form = $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')->set('resultado', 'OMITIDA')
            ->set('motivo_omision', 'no')->call('guardar')->assertHasErrors('motivo_omision')
            ->set('motivo_omision', str_repeat('a', 501))->call('guardar')->assertHasErrors('motivo_omision')
            ->set('hora_real', '08:15')->set('dosis_administrada', '50')->set('efecto_observado', 'Captura anterior')
            ->set('reaccion_adversa', 'Captura anterior')->set('motivo_omision', str_repeat('b', 500))->call('guardar')->assertHasNoErrors();
        $registro = AdministracionMedicacion::firstOrFail();
        $this->assertSame('OMITIDA', $registro->resultado);
        foreach (['dosis_administrada', 'fecha_hora_administracion', 'efecto_observado', 'reaccion_adversa'] as $columna) {
            $this->assertNull($registro->$columna);
        }
    }

    public function test_cambiar_dosis_limpiar_y_salir_conservan_borrador_hasta_confirmar(): void
    {
        HorarioPrescripcion::create(['cod_horario_prescripcion' => 'HPR_TEST_02', 'cod_prescripcion' => $this->prescripcion->getKey(), 'hora_programada' => '08:00', 'dosis_programada' => 25, 'estado' => 'ACTIVO']);
        $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')->set('resultado', 'OMITIDA')->set('motivo_omision', 'Rechazo observado')
            ->call('seleccionarDosis', 'HPR_TEST_02')->assertSet('confirmacion', 'dosis')->assertSet('cod_horario_prescripcion', 'HPR_TEST_01')
            ->call('cancelarConfirmacion')->assertSet('motivo_omision', 'Rechazo observado')
            ->call('solicitarSalida', 'limpiar')->assertSet('confirmacion', 'limpiar')->call('confirmarDescarte')
            ->assertSet('cod_horario_prescripcion', 'HPR_TEST_01')->assertSet('resultado', '')
            ->call('seleccionarDosis', 'HPR_TEST_02')->assertSet('orden.dosis_programada', '25.000')
            ->set('resultado', 'OMITIDA')->call('solicitarSalida', 'volver')->assertSet('showModal', true)
            ->call('confirmarDescarte')->assertSet('showModal', false)->assertDispatched('medicacion-formulario-cerrado');
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_sin_pendientes_conserva_historial_y_no_inventa_dosis(): void
    {
        HorarioPrescripcion::whereKey('HPR_TEST_01')->update(['estado' => 'INACTIVO']);
        $this->formulario()->assertSee('Sin dosis pendiente de registrar')->call('abrirHistorial')->assertSet('historial.total', 0)
            ->call('abrirEvolucion')->assertSet('evolucion.series', [])->call('guardar')->assertHasErrors('cod_horario_prescripcion');
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_dosis_y_fecha_invalidas_rechazadas_por_servicio_directo(): void
    {
        foreach (['0', '-1', '1.2345', '10000000'] as $dosis) {
            try {
                app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true, dosisAdministrada: $dosis, codHorario: 'HPR_TEST_01');
                $this->fail('Debía rechazarse la dosis.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('dosis_administrada', $error->errors());
            }
        }
        try {
            app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true, fechaHoraAdministracion: now()->addHour()->toDateTimeString(), codHorario: 'HPR_TEST_01');
            $this->fail('Debía rechazarse la fecha futura.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('fecha_hora_administracion', $error->errors());
        }
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_consultas_no_exponen_otro_residente_y_cuenta_inactiva_no_abre(): void
    {
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        Livewire::test(AdministracionMedicacionModal::class)->call('abrirModalAdministracion', $otro->getKey())->assertForbidden();
        $this->formulario()->call('verDetalle', 'NO_EXISTE')->assertNotFound();
        $this->enfermero->update(['estado' => 'INACTIVO']);
        Livewire::test(AdministracionMedicacionModal::class)->call('abrirModalAdministracion', $this->residente->getKey())->assertForbidden();
    }

    public function test_permisos_de_lectura_se_verifican_sin_confundir_creacion_con_consulta(): void
    {
        $rol = Role::findByName('ENFERMEROS');
        foreach (['prescripciones.ver', 'horarios_prescripcion.ver'] as $permiso) {
            $rol->revokePermissionTo($permiso);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
            Livewire::test(AdministracionMedicacionModal::class)->call('abrirModalAdministracion', $this->residente->getKey())->assertForbidden();
            $rol->givePermissionTo($permiso);
        }
        $rol->revokePermissionTo('alergias.ver');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $this->formulario()->assertSet('contexto.alergias', [])->assertSee('Consulta de alergias no autorizada.');
        $rol->revokePermissionTo('administraciones_medicacion.ver');
        $this->enfermero->unsetRelation('roles')->unsetRelation('permissions');
        $this->formulario()->call('abrirHistorial')->assertForbidden();
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_dos_horarios_a_la_misma_hora_exigen_identidad_y_dosis_exactas(): void
    {
        HorarioPrescripcion::create(['cod_horario_prescripcion' => 'HPR_SAME_TIME', 'cod_prescripcion' => $this->prescripcion->getKey(), 'hora_programada' => '08:00', 'dosis_programada' => 25, 'estado' => 'ACTIVO']);
        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->residente->getKey());
        $this->assertCount(2, $agenda->pluck('id')->unique());
        try {
            app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true);
            $this->fail('No debe elegirse el primer horario.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('cod_horario_prescripcion', $error->errors());
        }
        $this->formulario()->call('seleccionarDosis', 'HPR_SAME_TIME')->set('resultado', 'ADMINISTRADA')->set('hora_real', '08:10')->set('dosis_administrada', '25')->call('guardar')->assertHasNoErrors();
        $this->assertDatabaseHas('administraciones_medicacion', ['cod_horario_prescripcion' => 'HPR_SAME_TIME', 'dosis_administrada' => 25]);
    }

    public function test_jornada_nocturna_del_dia_anterior_sigue_vigente_tras_medianoche(): void
    {
        Carbon::setTestNow('2026-10-15 01:00:00');
        Turno::whereKey('TUR_TEST_MED')->update(['hora_inicio' => '20:00:00', 'hora_cierre' => '08:00:00']);
        Jornada::whereKey('JOR_TEST_MED')->update(['fecha_jornada' => '2026-10-14', 'estado' => 'EN_CURSO']);
        HorarioPrescripcion::whereKey('HPR_TEST_01')->update(['hora_programada' => '00:30:00']);
        $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')->set('resultado', 'ADMINISTRADA')->set('hora_real', '00:45')->set('dosis_administrada', '50')->call('guardar')->assertHasNoErrors();
        $this->assertDatabaseHas('administraciones_medicacion', ['cod_jornada' => 'JOR_TEST_MED', 'fecha_hora_programada' => '2026-10-15 00:30:00']);
    }

    public function test_fallo_de_auditoria_revierte_administracion_y_reserva(): void
    {
        $antes = DB::table('secuencias_codigos')->get()->toJson();
        $original = Activity::getEventDispatcher();
        Activity::setEventDispatcher(clone $original);
        try {
            Activity::creating(fn () => throw new \RuntimeException('Fallo sintético de auditoría'));
            try {
                app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true, codHorario: 'HPR_TEST_01');
                $this->fail('Debe revertir toda la operación.');
            } catch (\RuntimeException $error) {
                $this->assertSame('Fallo sintético de auditoría', $error->getMessage());
            }
        } finally {
            Activity::setEventDispatcher($original);
        }
        $this->assertDatabaseCount('administraciones_medicacion', 0);
        $this->assertSame($antes, DB::table('secuencias_codigos')->get()->toJson());
    }

    public function test_historial_conserva_estado_y_evolucion_no_cuenta_eventos_anulados(): void
    {
        $registro = app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true, codHorario: 'HPR_TEST_01');
        $registro->update(['estado' => 'ANULADA']);
        $this->formulario()->call('abrirHistorial')->assertSet('historial.total', 1)->assertSet('historial.registros.0.estado', 'ANULADA')
            ->call('abrirEvolucion')->assertSet('evolucion.administradas', 0)->assertSet('evolucion.series', []);
    }

    public function test_fallo_de_refresco_no_oculta_una_administracion_confirmada(): void
    {
        $form = $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')->call('abrirHistorial')
            ->set('resultado', 'ADMINISTRADA')->set('hora_real', '08:10')->set('dosis_administrada', '50');
        $form->set('historiaPeriodo', 'personalizado')->set('historiaDesde', 'fecha inválida');
        $form->call('guardar')->assertHasNoErrors()->assertDispatched('medicacion-guardada')->assertSet('guardado.resultado', 'ADMINISTRADA')
            ->assertSee('El registro está guardado.')->assertSet('mostrarHistorial', false);
        $this->assertDatabaseCount('administraciones_medicacion', 1);
    }

    public function test_calendario_historico_consulta_resultados_sin_reconstruir_dosis(): void
    {
        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true, codHorario: 'HPR_TEST_01');
        Carbon::setTestNow('2026-10-15 09:00:00');
        Jornada::whereKey('JOR_TEST_MED')->update(['fecha_jornada' => '2026-10-15']);
        $this->formulario()->call('cambiarFecha', -1)->assertSet('pestana', 'TODAS')->assertSee('Modo consulta.')
            ->assertViewHas('contadores', fn ($conteos) => $conteos['TODAS'] === 1 && $conteos['PENDIENTES'] === 0)
            ->assertSee('Ver registro')->call('abrirHistorial')->assertSet('historiaPeriodo', 'personalizado')->assertSet('historial.total', 1)
            ->call('guardar')->assertStatus(409);
        $this->assertDatabaseCount('administraciones_medicacion', 1);
    }

    public function test_consultas_abiertas_siguen_la_prescripcion_seleccionada(): void
    {
        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true, codHorario: 'HPR_TEST_01');
        $otra = $this->prescripcion->replicate()->forceFill(['cod_prescripcion' => 'PRE_OTRA']);
        $otra->save();
        HorarioPrescripcion::create(['cod_horario_prescripcion' => 'HPR_OTRO', 'cod_prescripcion' => $otra->getKey(), 'hora_programada' => '20:00', 'dosis_programada' => 25, 'estado' => 'ACTIVO']);
        $this->formulario()->call('abrirHistorial')->call('abrirEvolucion')->assertSet('historial.total', 1)
            ->call('seleccionarDosis', 'HPR_OTRO')->assertSet('historial.total', 0)->assertSet('evolucion.administradas', 0)
            ->assertSet('mostrarHistorial', true)->assertSet('mostrarEvolucion', true);
        $this->assertDatabaseCount('administraciones_medicacion', 1);
    }

    public function test_detalle_del_grafico_respeta_la_agrupacion_y_el_intervalo_consultados(): void
    {
        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada($this->enfermero, $this->residente->getKey(), $this->prescripcion->getKey(), '08:00', true, codHorario: 'HPR_TEST_01');
        $this->formulario()->set('evolucionPeriodo', '7')->set('agrupacion', 'semana')->call('abrirEvolucion')
            ->assertSet('evolucion.series.0.fecha', '2026-10-12')->set('agrupacion', 'mes')
            ->call('verPeriodo', '2026-10-12', 'ADMINISTRADA')->assertSet('historiaDesde', '2026-10-12')
            ->assertSet('historiaHasta', '2026-10-14')->assertSet('historial.total', 1)
            ->call('verPeriodo', '2026-10-01')->assertStatus(422);
        $this->assertDatabaseCount('administraciones_medicacion', 1);
    }

    public function test_imagen_del_catalogo_se_amplia_sin_perder_la_captura(): void
    {
        Storage::fake('public');
        $ruta = 'imagenes/medicamentos/'.$this->prescripcion->cod_medicamento.'.png';
        Storage::disk('public')->put($ruta, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j16kAAAAASUVORK5CYII='));
        $form = $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')
            ->set('resultado', 'OMITIDA')->set('motivo_omision', 'Rechazo observado')
            ->assertSet('orden.imagen', Storage::disk('public')->url($ruta))->assertSee('Ampliar imagen')
            ->call('verImagen')->assertSet('mostrarImagen', true)->assertSet('motivo_omision', 'Rechazo observado');
        $this->exportar('photo', $form);
        if ($directorio = getenv('RM_MEDICATION_QA_DIR')) {
            file_put_contents($directorio.'/medication-test.png', Storage::disk('public')->get($ruta));
        }
        $form->call('cerrarImagen')->assertSet('mostrarImagen', false)->assertSet('showModal', true)->assertSet('resultado', 'OMITIDA');
        Storage::disk('public')->delete($ruta);
        $form->call('verImagen')->assertHasErrors('imagen')->assertSet('mostrarImagen', false)->assertSet('orden.imagen', null);
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_fotografia_ausente_o_no_valida_no_se_presenta_como_producto_real(): void
    {
        Storage::fake('public');
        $codigo = $this->prescripcion->cod_medicamento;
        Storage::disk('public')->put('imagenes/medicamentos/'.$codigo.'.webp', '<script>fake image</script>');
        $this->assertNull(app(ImagenMedicamento::class)->paraCodigo($codigo));
        $this->assertNull(app(ImagenMedicamento::class)->paraCodigo('../archivo'));
        $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')->assertSet('orden.imagen', null)
            ->assertSee('Ilustración farmacéutica')->assertDontSee('Ampliar imagen')->call('verImagen')->assertHasErrors('imagen');
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_hora_actual_es_explicita_y_continuar_no_crea_otro_registro(): void
    {
        $form = $this->formulario()->call('seleccionarDosis', 'HPR_TEST_01')->assertSet('hora_real', '')
            ->set('resultado', 'ADMINISTRADA')->call('usarHoraActual')->assertSet('hora_real', '09:00')->assertSet('dosis_administrada', '')
            ->set('dosis_administrada', '50')->call('guardar')->assertHasNoErrors()->assertSee('Registro confirmado')->assertSee('Elegir otra dosis');
        $this->assertEquals(50, $form->get('guardado.dosis'));
        $form->call('prepararSiguienteDosis')->assertSet('guardado', [])->assertSet('cod_prescripcion', null)
            ->assertSet('cod_horario_prescripcion', null)->assertSet('resultado', '')->assertDispatched('medicacion-elegir-dosis');
        $this->assertDatabaseCount('administraciones_medicacion', 1);
    }

    public function test_ficha_del_catalogo_no_inventa_datos_de_un_medicamento_inexistente(): void
    {
        $this->enfermero->givePermissionTo('medicamentos.ver');
        Livewire::test(SaludAdministracionMedicacionPanel::class)->call('abrirModalMedicamento', $this->prescripcion->cod_medicamento)
            ->assertSet('modalMedicamentoAbierto', true)->assertSet('medicamentoFicha.control_especial', 'No')
            ->call('abrirModalMedicamento', 'MED_INEXISTENTE')->assertSet('modalMedicamentoAbierto', false)
            ->assertSet('medicamentoFicha', [])->assertHasErrors('medicamento');
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_enfermeria_consulta_imagen_desde_su_orden_sin_acceso_global_al_catalogo(): void
    {
        $this->assertFalse($this->enfermero->can('medicamentos.ver'));
        Livewire::test(SaludAdministracionMedicacionPanel::class)->set('selectedPrescripcionId', $this->prescripcion->getKey())
            ->call('abrirModalMedicamento', $this->prescripcion->cod_medicamento)->assertSet('modalMedicamentoAbierto', true);
        Livewire::test(SaludAdministracionMedicacionPanel::class)->call('abrirModalMedicamento', $this->prescripcion->cod_medicamento)->assertStatus(403);
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_ficha_conserva_observacion_y_drawer_y_el_cierre_del_padre_cierra_la_ficha(): void
    {
        \App\Models\Alergia::create([
            'cod_alergia' => 'ALE_DRAWER_ACTIVA', 'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->enfermero->personal->getKey(), 'sustancia' => 'Sustancia sintética',
            'fecha_hora' => now(), 'estado' => 'ACTIVA',
        ]);
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->residente])
            ->call('abrirDrawerDosis', $this->prescripcion->getKey(), '08:00', $this->residente->getKey())
            ->assertSet('dosisDetalle.seguridad.tiene_alergias', true)->assertSee('Sustancia sintética')
            ->set('observacionEnfermeria', 'Observación sintética sin guardar')
            ->call('abrirModalMedicamento', $this->prescripcion->cod_medicamento)
            ->assertSet('modalMedicamentoAbierto', true)->assertSet('drawerDosisAbierto', true)
            ->assertSet('observacionEnfermeria', 'Observación sintética sin guardar')
            ->call('cerrarModalMedicamento')->assertSet('modalMedicamentoAbierto', false)
            ->assertSet('drawerDosisAbierto', true)->assertSet('observacionEnfermeria', 'Observación sintética sin guardar')
            ->call('abrirModalMedicamento', $this->prescripcion->cod_medicamento)
            ->call('cerrarDrawerDosis')->assertSet('modalMedicamentoAbierto', false)->assertSet('drawerDosisAbierto', false);
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_drawer_muestra_todos_los_horarios_activos_indicacion_y_profesional_prescriptor(): void
    {
        $prescriptor = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Camila', 'ap_paterno' => 'Perez', 'ap_materno' => 'Soto']);
        $prescriptor->assignRole('MEDICO GENERAL/GERIATRA');
        $prescriptor->personal->update(['nombres' => 'Camila', 'apellido_paterno' => 'Perez', 'apellido_materno' => 'Soto',
            'profesion' => 'MEDICINA_GENERAL', 'especialidad' => 'Geriatría']);
        $this->prescripcion->update(['cod_personal' => $prescriptor->personal->getKey(), 'frecuencia' => 'DOS_VECES_AL_DIA',
            'indicacion' => 'Indicación médica sintética específica del residente']);
        HorarioPrescripcion::create(['cod_horario_prescripcion' => 'HPR_DRAWER_PM', 'cod_prescripcion' => $this->prescripcion->getKey(),
            'hora_programada' => '20:00', 'dosis_programada' => 50, 'dias_semana' => 'LUNES,MIERCOLES', 'estado' => 'ACTIVO']);
        HorarioPrescripcion::create(['cod_horario_prescripcion' => 'HPR_DRAWER_OFF', 'cod_prescripcion' => $this->prescripcion->getKey(),
            'hora_programada' => '12:00', 'dosis_programada' => 50, 'estado' => 'INACTIVO']);
        $ordenAntes = $this->prescripcion->fresh()->getAttributes();
        $horariosAntes = HorarioPrescripcion::orderBy('cod_horario_prescripcion')->get()->toArray();

        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->residente])
            ->call('abrirDrawerDosis', $this->prescripcion->getKey(), '20:00', $this->residente->getKey())
            ->assertSet('dosisDetalle.programacion.horarios_activos', [
                ['hora' => '08:00', 'dias' => 'Todos los días', 'seleccionado' => false],
                ['hora' => '20:00', 'dias' => 'LUNES,MIERCOLES', 'seleccionado' => true],
            ])->assertSet('dosisDetalle.prescripcion.prescriptor', 'Camila Perez Soto')
            ->assertSet('dosisDetalle.prescripcion.profesion_prescriptor', 'MEDICINA_GENERAL')
            ->assertSet('dosisDetalle.prescripcion.especialidad_prescriptor', 'Geriatría')
            ->assertSee('08:00')->assertSee('20:00')->assertSee('Dosis consultada')
            ->assertSee('Indicación médica sintética específica del residente')->assertSee('Camila Perez Soto')
            ->assertSee('Medicina general')->assertSee('Geriatría');

        $this->assertSame($ordenAntes, $this->prescripcion->fresh()->getAttributes());
        $this->assertSame($horariosAntes, HorarioPrescripcion::orderBy('cod_horario_prescripcion')->get()->toArray());
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_drawer_no_inventa_horarios_ni_indicacion_a_partir_de_frecuencia_u_observacion(): void
    {
        $this->prescripcion->update(['frecuencia' => 'DOS_VECES_AL_DIA', 'indicacion' => null,
            'observacion' => 'Observación administrativa, no una indicación']);
        $panel = Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->residente])
            ->call('abrirDrawerDosis', $this->prescripcion->getKey(), '08:00', $this->residente->getKey())
            ->assertSet('dosisDetalle.programacion.horarios_activos', [
                ['hora' => '08:00', 'dias' => 'Todos los días', 'seleccionado' => true],
            ])->assertSet('dosisDetalle.prescripcion.indicacion', 'Indicación no registrada. Consulta al profesional prescriptor.');
        HorarioPrescripcion::whereKey('HPR_TEST_01')->update(['estado' => 'INACTIVO']);
        $this->prescripcion->update(['frecuencia' => null]);
        $panel->call('abrirDrawerDosis', $this->prescripcion->getKey(), '08:00', $this->residente->getKey())
            ->assertSet('dosisDetalle.programacion.horarios_activos', [])
            ->assertSet('dosisDetalle.prescripcion.frecuencia', 'Frecuencia no registrada')
            ->assertSee('No hay horarios activos registrados');
        $this->assertDatabaseCount('horarios_prescripcion', 1);
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_drawer_rechaza_una_orden_que_dejo_de_estar_asignada_y_un_contexto_incoherente(): void
    {
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        Livewire::test(SaludAdministracionMedicacionPanel::class)
            ->call('abrirDrawerDosis', $this->prescripcion->getKey(), '08:00', $otro->getKey())->assertForbidden();
        AsignacionResidenteJornada::query()->where('cod_residente', $this->residente->getKey())->update(['estado' => 'INACTIVA']);
        Livewire::test(SaludAdministracionMedicacionPanel::class)
            ->call('abrirDrawerDosis', $this->prescripcion->getKey(), '08:00', $this->residente->getKey())->assertForbidden();
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    #[Group('browser')]
    public function test_botones_e_imagen_con_livewire_http_real_y_base_aislada(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite' || ! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Navegador local con copia SQLite y GD; la integración PostgreSQL se prueba por separado.');
        }
        HorarioPrescripcion::create(['cod_horario_prescripcion' => 'HPR_BROWSER_02', 'cod_prescripcion' => $this->prescripcion->getKey(), 'hora_programada' => '20:00', 'dosis_programada' => 50, 'estado' => 'ACTIVO']);
        $root = sys_get_temp_dir().'/remembermind-medication-http-'.bin2hex(random_bytes(8));
        foreach (['', '/views', '/sessions', '/public/imagenes/medicamentos'] as $directorio) {
            File::makeDirectory($root.$directorio, 0700, true);
        }
        $copia = new \PDO('sqlite:'.$root.'/qa.sqlite');
        $copia->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $copia->exec('PRAGMA foreign_keys=OFF');
        foreach (DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'") as $tabla) {
            $copia->exec($tabla->sql);
            $filas = DB::table($tabla->name)->get();
            if ($filas->isEmpty()) {
                continue;
            }
            $quote = fn ($value) => '"'.str_replace('"', '""', $value).'"';
            $columnas = array_keys((array) $filas->first());
            $insertar = $copia->prepare('INSERT INTO '.$quote($tabla->name).' ('.implode(',', array_map($quote, $columnas)).') VALUES ('.implode(',', array_fill(0, count($columnas), '?')).')');
            foreach ($filas as $fila) {
                $insertar->execute(array_values((array) $fila));
            }
        }
        foreach (DB::select("SELECT sql FROM sqlite_master WHERE type IN ('index','trigger','view') AND sql IS NOT NULL") as $estructura) {
            $copia->exec($estructura->sql);
        }
        file_put_contents($root.'/context.json', json_encode(['usuario' => $this->enfermero->getKey(), 'residente' => $this->residente->getKey()], JSON_THROW_ON_ERROR));
        $imagen = imagecreatetruecolor(320, 176);
        imagefill($imagen, 0, 0, imagecolorallocate($imagen, 239, 234, 228));
        $tinta = imagecolorallocate($imagen, 74, 70, 66);
        imagerectangle($imagen, 54, 26, 264, 132, $tinta);
        foreach ([94, 158, 222] as $x) {
            imageellipse($imagen, $x, 60, 26, 36, $tinta);
            imageellipse($imagen, $x, 102, 26, 36, $tinta);
        }
        imagestring($imagen, 3, 65, 148, 'QA - Imagen sintetica', $tinta);
        imagepng($imagen, $root.'/public/imagenes/medicamentos/'.$this->prescripcion->cod_medicamento.'.png');
        imagedestroy($imagen);
        $listener = stream_socket_server('tcp://127.0.0.1:0', $codigo, $mensaje);
        $this->assertNotFalse($listener);
        $host = stream_socket_get_name($listener, false);
        fclose($listener);
        $servidor = new Process([PHP_BINARY, '-S', $host, '-t', public_path(), base_path('tests/Support/medicacion_browser_router.php')], base_path(), ['RM_MEDICATION_HTTP_ROOT' => $root, 'APP_KEY' => config('app.key'), 'APP_URL' => 'http://'.$host]);
        try {
            $servidor->start();
            $listo = false;
            for ($intento = 0; $intento < 50; $intento++) {
                $socket = @stream_socket_client('tcp://'.$host, $errno, $error, .1);
                if ($socket) {
                    fclose($socket);
                    $listo = true;
                    break;
                }
                if (! $servidor->isRunning()) {
                    break;
                }
                usleep(100000);
            }
            $this->assertTrue($listo, $servidor->getErrorOutput());
            $browser = new Process(['node', 'tests/Support/medicacion-browser-qa.mjs'], base_path(), [
                'RM_MEDICATION_HTTP_BASE' => 'http://'.$host,
                'RM_MEDICATION_EVIDENCE_DIR' => (getenv('RM_MEDICATION_QA_DIR') ?: $root.'/evidence').'/browser',
            ]);
            $browser->setTimeout(120);
            $browser->run();
            if ($browser->getExitCode() === 77) {
                $this->markTestSkipped('No hay navegador instalado para la prueba HTTP.');
            }
            $this->assertSame(0, $browser->getExitCode(), $browser->getOutput().$browser->getErrorOutput().substr($servidor->getErrorOutput(), -3000));
            $registro = $copia->query('SELECT * FROM administraciones_medicacion')->fetchAll(\PDO::FETCH_ASSOC);
            $this->assertCount(1, $registro);
            $this->assertEquals(49.125, $registro[0]['dosis_administrada']);
            $this->assertSame($this->enfermero->personal->getKey(), $registro[0]['cod_personal']);
            $this->assertSame('ADMINISTRADA', $registro[0]['resultado']);
            $this->assertSame(1, (int) $copia->query("SELECT COUNT(*) FROM activity_log WHERE subject_type='App\\Models\\AdministracionMedicacion'")->fetchColumn());
            $this->assertDatabaseCount('administraciones_medicacion', 0);
        } finally {
            $servidor->stop(3);
            $copia = null;
            if (dirname(realpath($root)) === realpath(sys_get_temp_dir()) && ! is_link($root)
                && preg_match('/^remembermind-medication-http-[a-f0-9]{16}$/D', basename($root))) {
                File::deleteDirectory($root);
            }
        }
    }
}
