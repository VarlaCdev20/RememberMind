<?php

namespace Tests\Feature;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\EjecucionCuidado;
use App\Models\Incidente;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReporteEnfermeria;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ReporteEnfermeriaPeriodoTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermera;

    private Personal $personal;

    private Residente $residente;

    private Residente $ajeno;

    private Jornada $jornada;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-27 18:00:00');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermera->assignRole('ENFERMEROS');
        $this->personal = Personal::create(['cod_personal' => 'PER_RPT', 'cod_usuario' => $this->enfermera->getKey(),
            'nombres' => 'Lucia', 'apellido_paterno' => 'Prueba', 'numero_documento' => 'SINT-RPT', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['cod_turno' => 'TUR_RPT', 'nombre' => 'Turno sintético', 'hora_inicio' => '06:00:00', 'hora_cierre' => '14:00:00', 'orden' => 1, 'estado' => 'ACTIVO']);
        $this->jornada = Jornada::create(['cod_jornada' => 'JOR_RPT', 'cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'CERRADA']);
        $this->residente = Residente::crearDesdeAdmision(['cod_residente' => 'RES_RPT', 'nombres' => 'Alicia', 'apellido_paterno' => 'Sintetica', 'fecha_nacimiento' => '1940-01-01', 'genero' => 'FEMENINO', 'estado' => 'ADMITIDO']);
        $this->ajeno = Residente::crearDesdeAdmision(['cod_residente' => 'RES_AJENO_RPT', 'nombres' => 'Persona', 'apellido_paterno' => 'Ajena', 'fecha_nacimiento' => '1940-01-02', 'genero' => 'FEMENINO', 'estado' => 'ADMITIDO']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ASG_RPT', 'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'cod_jornada' => $this->jornada->getKey(), 'nivel_supervision' => 'ESTANDAR', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($this->enfermera);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function filtros(array $extra = []): array
    {
        return array_merge(['desde' => '2026-10-26', 'hasta' => '2026-10-27', 'codResidente' => $this->residente->getKey(), 'tipo' => 'TODOS'], $extra);
    }

    private function signo(string $codigo, string $fecha, ?Residente $residente = null, string $estado = 'VIGENTE'): void
    {
        SignoVital::create(['cod_signo' => $codigo, 'cod_residente' => ($residente ?? $this->residente)->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_jornada' => $this->jornada->getKey(), 'fecha_hora' => $fecha, 'presion_sistolica' => 120, 'presion_diastolica' => 80,
            'temperatura' => 0, 'saturacion_oxigeno' => null, 'estado' => $estado]);
    }

    public function test_consulta_permite_turno_terminado_respeta_limites_dia_y_no_inventa_ceros(): void
    {
        $this->signo('SIG_INICIO', '2026-10-26 00:00:00');
        $this->signo('SIG_FIN', '2026-10-27 23:59:59');
        $this->signo('SIG_FUERA', '2026-10-28 00:00:00');
        $this->signo('SIG_ANUL', '2026-10-27 10:00:00', estado: 'ANULADO');
        $this->signo('SIG_AJENO', '2026-10-27 10:00:00', $this->ajeno);
        $datos = app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros());
        $this->assertCount(2, $datos['filas']);
        $this->assertSame(2, $datos['totales']['CONTROLES']['cantidad']);
        $this->assertStringContainsString('Temperatura: 0 °C', $datos['filas'][0]['detalle']);
        $this->assertStringNotContainsString('Saturación:', $datos['filas'][0]['detalle']);
        $this->assertSame([1, 1], $datos['dias']->pluck('cantidad')->all());
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros()))->assertOk()->assertSee('Alicia Sintetica')->assertDontSee('Persona Ajena');
    }

    public function test_cuidados_pendientes_e_incidentes_originales_se_distinguen_de_alertas(): void
    {
        $area = Area::create(['cod_area' => 'ARE_RPT', 'nombre' => 'Enfermería', 'estado' => 'ACTIVA']);
        $plan = PlanCuidado::create(['cod_plan' => 'PLC_RPT', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_area' => $area->getKey(), 'nombre' => 'Plan sintético', 'objetivo_general' => 'Cuidado documentado', 'fecha_hora_apertura' => now(), 'estado' => 'ACTIVO']);
        $intervencion = IntervencionCuidado::create(['cod_intervencion' => 'INT_RPT', 'cod_plan' => $plan->getKey(), 'nombre' => 'Cuidado sintético', 'descripcion' => 'Registro exclusivo de prueba', 'estado' => 'ACTIVA']);
        EjecucionCuidado::create(['cod_ejecucion' => 'EJE_RPT', 'cod_intervencion' => $intervencion->getKey(), 'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'cod_jornada' => $this->jornada->getKey(), 'fecha_hora_programada' => '2026-10-27 09:00:00', 'estado' => 'PENDIENTE']);
        Incidente::create(['cod_incidente' => 'INC_RPT', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_jornada' => $this->jornada->getKey(), 'tipo_incidente' => 'CAIDA', 'gravedad' => 'LEVE', 'descripcion' => 'Incidente sintético registrado', 'fecha_hora' => '2026-10-27 10:00:00', 'requiere_medico' => false, 'requiere_derivacion' => false, 'estado' => 'REGISTRADO']);
        $datos = app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros());
        $this->assertSame(1, $datos['totales']['INCIDENTES']['cantidad']);
        $this->assertSame(0, $datos['totales']['ALERTAS']['cantidad']);
        $this->assertSame('Pendiente', $datos['filas']->firstWhere('categoria', 'CUIDADOS')['estado']);
    }

    public function test_filtros_invalidos_y_residente_ajeno_no_se_ignoran(): void
    {
        $this->getJson(route('admin.enfermeria.reportes.preview', $this->filtros(['desde' => '2026-10-28'])))->assertUnprocessable()->assertJsonValidationErrors('hasta');
        $this->getJson(route('admin.enfermeria.reportes.pdf', $this->filtros(['tipo' => 'inventado'])))->assertUnprocessable()->assertJsonValidationErrors('tipo');
        foreach (['admin.enfermeria.reportes.preview', 'admin.enfermeria.reportes.pdf', 'admin.enfermeria.reportes.csv'] as $ruta) {
            $this->get(route($ruta, $this->filtros(['codResidente' => $this->ajeno->getKey()])))->assertForbidden();
        }
        $this->get(route('admin.enfermeria.pacientes.ficha.pdf', $this->ajeno))->assertForbidden();
    }

    public function test_livewire_aplica_filtros_y_conserva_ultimo_resumen_valido(): void
    {
        $this->signo('SIG_PANEL', '2026-10-27 10:00:00');
        Livewire::test(ReporteEnfermeria::class)->assertSee('Alicia Sintetica')
            ->set('codResidente', $this->residente->getKey())->set('tipo', 'CONTROLES')->call('aplicar')->assertHasNoErrors()
            ->assertSet('aplicados.tipo', 'CONTROLES')->set('desde', 'incorrecta')->call('aplicar')->assertHasErrors('desde')->assertSet('aplicados.tipo', 'CONTROLES');
    }

    public function test_descarga_pdf_real_mismos_datos_y_auditoria_sin_registros_clinicos(): void
    {
        $this->signo('SIG_PDF', '2026-10-27 10:00:00');
        $vista = $this->get(route('admin.enfermeria.reportes.preview', $this->filtros(['tipo' => 'CONTROLES'])))->assertOk()->assertSee('Presión sistólica: 120');
        $this->assertCount(1, $vista['reporte']['filas']);
        $pdf = $this->get(route('admin.enfermeria.reportes.pdf', $this->filtros(['tipo' => 'CONTROLES'])))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertStringContainsString('no-store', $pdf->headers->get('Cache-Control'));
        $actividad = Activity::where('description', 'Resumen de Enfermería PDF generado.')->sole();
        $this->assertSame('CONTROLES', $actividad->properties['tipo']);
        $this->assertStringNotContainsString('120', $actividad->properties->toJson());
    }

    public function test_revalida_permiso_cuenta_y_personal_inactivos(): void
    {
        $this->personal->update(['estado' => 'INACTIVO']);
        $this->enfermera->unsetRelation('personal');
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros()))->assertForbidden();
        $this->personal->update(['estado' => 'ACTIVO']);
        $this->enfermera->unsetRelation('personal');
        $this->enfermera->syncRoles([]);
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros()))->assertForbidden();
        $this->enfermera->assignRole('ENFERMEROS');
        $this->enfermera->update(['estado' => 'INACTIVO']);
        $this->get(route('admin.enfermeria.reportes.pdf', $this->filtros()))->assertForbidden();
    }

    public function test_paginacion_no_recorta_la_vista_previa_y_escape_de_texto(): void
    {
        for ($i = 0; $i < 23; $i++) {
            $this->signo('SIG_PAG_'.$i, '2026-10-27 10:00:00');
        }
        Livewire::test(ReporteEnfermeria::class)->assertViewHas('registros', fn ($p) => $p->total() === 23 && $p->count() === 20)
            ->call('gotoPage', 2)->assertViewHas('registros', fn ($p) => $p->count() === 3);
        $this->residente->update(['nombres' => '<script>alert(1)</script>']);
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros()))->assertOk()
            ->assertViewHas('reporte', fn ($r) => $r['filas']->count() === 23)
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_fuentes_restringidas_rechazan_el_reporte_completo_y_exportacion(): void
    {
        $this->enfermera->roles->first()->revokePermissionTo('incidentes.ver');
        $this->enfermera->unsetRelation('roles');
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros()))->assertForbidden();
        $this->get(route('admin.enfermeria.reportes.pdf', $this->filtros()))->assertForbidden();
        $this->assertSame(0, Activity::where('description', 'Resumen de Enfermería PDF generado.')->count());
        $this->get(route('admin.enfermeria.reportes.csv', $this->filtros()))->assertForbidden();
        $this->assertSame(0, Activity::where('description', 'Resumen de Enfermería CSV generado.')->count());
    }

    public function test_medicacion_prn_y_fecha_efectiva_sin_sustituir_dosis_faltante(): void
    {
        DB::table('areas')->insert(['cod_area' => 'ARE_MED_RPT', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        DB::table('atenciones')->insert(['cod_atencion' => 'ATE_RPT', 'cod_residente' => $this->residente->getKey(), 'cod_area' => 'ARE_MED_RPT',
            'cod_personal' => $this->personal->getKey(), 'tipo_atencion' => 'PRUEBA', 'fecha_hora' => '2026-10-26 10:00:00', 'estado' => 'FINALIZADA']);
        DB::table('medicamentos')->insert(['cod_medicamento' => 'MED_RPT', 'nombre_generico' => 'Medicamento sintético', 'control_especial' => false, 'estado' => 'ACTIVO']);
        DB::table('prescripciones')->insert(['cod_prescripcion' => 'PRE_RPT', 'cod_atencion' => 'ATE_RPT',
            'cod_medicamento' => 'MED_RPT', 'cod_personal' => $this->personal->getKey(), 'via_administracion' => 'ORAL', 'unidad_dosis' => 'mg',
            'segun_necesidad' => true, 'fecha_hora_prescripcion' => '2026-10-26 10:00:00', 'estado' => 'ACTIVA']);
        $base = ['cod_prescripcion' => 'PRE_RPT', 'cod_personal' => $this->personal->getKey(),
            'cod_jornada' => $this->jornada->getKey(), 'resultado' => 'ADMINISTRADA', 'estado' => 'FINALIZADO'];
        // Inserción nominal del registro PRN: evita el fallback legacy de programación del modelo.
        DB::table('administraciones_medicacion')->insert($base + ['cod_administracion' => 'ADM_PRN_RPT', 'fecha_hora_administracion' => '2026-10-27 08:00:00', 'fecha_hora_programada' => null]);
        DB::table('administraciones_medicacion')->insert($base + ['cod_administracion' => 'ADM_FUERA_RPT', 'fecha_hora_administracion' => '2026-10-28 01:00:00', 'fecha_hora_programada' => '2026-10-27 20:00:00']);
        $datos = app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros(['tipo' => 'MEDICACION']));
        $this->assertCount(1, $datos['filas']);
        $this->assertSame('Administración registrada', $datos['filas'][0]['fecha_tipo']);
        $this->assertSame('Medicamento sintético', $datos['filas'][0]['detalle']);
    }

    public function test_exportacion_revalida_su_permiso_y_no_admite_sesion_anonima(): void
    {
        $this->enfermera->roles->first()->revokePermissionTo('enfermeria.ver_ficha_paciente');
        $this->enfermera->unsetRelation('roles');
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros()))->assertOk();
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros(['imprimir' => 1])))->assertForbidden();
        $this->get(route('admin.enfermeria.reportes.pdf', $this->filtros()))->assertForbidden();
        $this->assertSame(0, Activity::where('description', 'Resumen de Enfermería PDF generado.')->count());
        $this->app['auth']->forgetGuards();
        foreach (['admin.enfermeria.reportes', 'admin.enfermeria.reportes.preview', 'admin.enfermeria.reportes.pdf', 'admin.enfermeria.reportes.csv'] as $ruta) {
            $this->get(route($ruta, $this->filtros()))->assertRedirect(route('login'));
        }
    }

    private function sembrarSecciones(): void
    {
        $base = ['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(), 'estado' => 'VIGENTE'];
        $fecha = ['fecha_hora' => '2026-10-27 10:00:00'];
        $jornada = ['cod_jornada' => $this->jornada->getKey()];
        DB::table('registros_consumo')->insert($base + $fecha + $jornada + ['tipo_evento' => 'INGESTA', 'cod_registro_consumo' => 'ING_RPT', 'tipo_comida' => 'ALMUERZO', 'porcentaje_consumido' => 0, 'dificultad_deglucion' => false, 'observacion' => 'Detalle ingesta sintética']);
        DB::table('registros_consumo')->insert($base + $fecha + $jornada + ['tipo_evento' => 'HIDRATACION', 'cod_registro_consumo' => 'HID_RPT', 'cantidad_ml' => 150, 'tipo_liquido' => 'AGUA', 'observacion' => 'Detalle hidratación sintética']);
        DB::table('registros_consumo')->insert($base + $fecha + $jornada + ['tipo_evento' => 'ELIMINACION', 'cod_registro_consumo' => 'ELI_RPT', 'tipo_eliminacion' => 'URINARIA', 'volumen_ml' => 120, 'presencia_sangre' => false, 'observacion' => 'Detalle eliminación sintética']);
        DB::table('registros_observaciones_cuidado')->insert($base + $fecha + $jornada + ['tipo_evento' => 'MOVILIDAD', 'cod_observacion_cuidado' => 'MOV_RPT', 'marcha' => 'ASISTIDA', 'distancia_metros' => 12, 'mareo' => false, 'observacion' => 'Detalle movilidad sintética']);
        DB::table('registros_observaciones_cuidado')->insert($base + $jornada + ['tipo_evento' => 'SUENO', 'cod_observacion_cuidado' => 'SUE_RPT', 'fecha' => '2026-10-27', 'horas_sueno' => 6, 'despertares' => 0, 'insomnio' => false, 'observacion' => 'Detalle sueño sintético']);
        DB::table('registros_observaciones_cuidado')->insert($base + $fecha + $jornada + ['tipo_evento' => 'CONDUCTA', 'cod_observacion_cuidado' => 'CON_RPT', 'ansiedad' => false, 'estado_animo' => 'TRANQUILO', 'descripcion' => 'Detalle conducta sintética', 'intervencion' => 'Acompañamiento sintético', 'respuesta' => 'Respuesta sintética']);
        RegistrosConsumo::insertar('controles_cognitivos', [$base + $fecha + $jornada + ['cod_control_cognitivo' => 'COG_RPT', 'repite_preguntas' => false, 'observacion' => 'Detalle cognición sintética']]);
        RegistrosConsumo::insertar('valoraciones_dolor', [$base + $fecha + ['cod_valoracion_dolor' => 'DOL_RPT', 'intensidad' => 0, 'ubicacion' => 'Rodilla sintética', 'respuesta' => 'Detalle dolor sintético']]);
        DB::table('heridas')->insert($base + ['cod_herida' => 'HER_RPT', 'tipo_herida' => 'Herida sintética', 'ubicacion' => 'Brazo sintético', 'fecha_hora_identificacion' => '2026-10-27 10:00:00']);
        DB::table('curaciones_herida')->insert(['cod_curacion' => 'CUR_RPT', 'cod_herida' => 'HER_RPT', 'cod_personal' => $this->personal->getKey()] + $fecha + $jornada + ['longitud' => 2, 'ancho' => 1, 'procedimiento' => 'Procedimiento sintético', 'materiales' => 'Material sintético']);
    }

    public function test_reporte_general_y_cada_seccion_incluyen_todas_las_fuentes_originales(): void
    {
        $this->sembrarSecciones();
        $consulta = app(ConsultarReporteEnfermeria::class);
        $general = $consulta->consultar($this->enfermera, $this->filtros());
        $this->assertCount(10, $general['filas']);
        foreach (['DOLOR', 'INGESTA', 'HIDRATACION', 'ELIMINACION', 'MOVILIDAD', 'SUENO', 'CONDUCTA', 'COGNICION', 'HERIDAS', 'CURACIONES'] as $tipo) {
            $datos = $consulta->consultar($this->enfermera, $this->filtros(['tipo' => $tipo]));
            $this->assertCount($tipo === 'HERIDAS' ? 2 : 1, $datos['filas'], $tipo);
            $this->assertCount($tipo === 'HERIDAS' ? 2 : 1, $datos['totales']);
            $this->get(route('admin.enfermeria.reportes.csv', $this->filtros(['tipo' => $tipo])))->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8')->assertSee('Alicia Sintetica');
        }
        $this->assertStringContainsString('Dificultad de deglución: No', $general['filas']->firstWhere('categoria', 'INGESTA')['detalle']);
        $this->assertStringContainsString('Intensidad registrada: 0', $general['filas']->firstWhere('categoria', 'DOLOR')['detalle']);
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros(['tipo' => 'SUENO'])))->assertOk()->assertDontSee('00:00')->assertSee('hora no registrada');
        $this->get(route('admin.enfermeria.reportes.pdf', $this->filtros()))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_csv_no_ejecuta_formulas_ni_rompe_comillas_y_saltos_de_linea(): void
    {
        $this->signo('SIG_CSV', '2026-10-27 10:00:00');
        foreach (['=1+1', '+SUM(1)', '-1+2', '@SUM(1)', "\t=1+1", "Nombre; con \"comillas\"\ny salto"] as $nombre) {
            $this->residente->update(['nombres' => $nombre]);
            $respuesta = $this->get(route('admin.enfermeria.reportes.csv', $this->filtros()))->assertOk();
            $this->assertStringStartsWith("\xEF\xBB\xBF", $respuesta->getContent());
            $archivo = fopen('php://temp', 'w+');
            fwrite($archivo, substr($respuesta->getContent(), 3));
            rewind($archivo);
            $cabecera = fgetcsv($archivo, escape: '');
            $fila = fgetcsv($archivo, separator: ';', escape: '');
            fclose($archivo);
            $this->assertCount(10, $fila);
            $this->assertSame((str_contains($nombre, 'Nombre') ? '' : "'").trim($nombre.' Sintetica'), trim($fila[5]));
        }
    }

    public function test_curaciones_ajenas_y_heridas_anuladas_no_se_exportan(): void
    {
        $this->sembrarSecciones();
        DB::table('heridas')->where('cod_herida', 'HER_RPT')->update(['cod_residente' => $this->ajeno->getKey()]);
        $this->assertCount(0, app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros(['tipo' => 'CURACIONES']))['filas']);
        DB::table('heridas')->where('cod_herida', 'HER_RPT')->update(['cod_residente' => $this->residente->getKey(), 'estado' => 'ANULADA']);
        $this->assertCount(0, app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros(['tipo' => 'HERIDAS']))['filas']);
    }

    public function test_seccion_autorizada_no_exige_permiso_de_otra_area_y_general_no_oculta_restricciones(): void
    {
        $this->enfermera->roles->first()->revokePermissionTo('registros_sueno.ver');
        $this->enfermera->unsetRelation('roles');
        $this->get(route('admin.enfermeria.reportes.preview', $this->filtros(['tipo' => 'CONTROLES'])))->assertOk();
        foreach (['TODOS', 'SUENO'] as $tipo) {
            $this->get(route('admin.enfermeria.reportes.csv', $this->filtros(['tipo' => $tipo])))->assertForbidden();
        }
    }

    public function test_entrada_desde_area_conserva_residente_fechas_y_seccion(): void
    {
        $this->getJson(route('admin.enfermeria.reportes', ['tipo' => ['DOLOR']]))->assertUnprocessable();
        $this->get(route('admin.enfermeria.reportes', $this->filtros(['tipo' => 'DOLOR'])))->assertOk()->assertSee('aria-controls="exportar-enfermeria-reporte"', false)->assertSee('Imprimir');
        $this->get(route('admin.enfermeria.reportes', $this->filtros(['codResidente' => $this->ajeno->getKey()])))->assertForbidden();
        foreach (ConsultarReporteEnfermeria::TIPOS as $tipo => $nombre) {
            $html = Blade::render('<x-ui.nursing-report-link :area="$area" :residente="$residente" />', ['area' => strtolower($tipo), 'residente' => $this->residente->getKey()]);
            $this->assertStringContainsString('tipo='.$tipo, $html);
            $this->assertStringContainsString('name="codResidente" value="'.$this->residente->getKey().'"', $html);
        }
    }

    public function test_pase_dispositivos_y_seguimiento_no_incluyen_atenciones_medicas(): void
    {
        $base = ['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(), 'estado' => 'VIGENTE'];
        RegistrosConsumo::insertar('dispositivos_clinicos', [$base + ['cod_dispositivo' => 'DIS_RPT', 'tipo' => 'SONDA', 'fecha_colocacion' => '2026-10-26 09:00:00', 'descripcion' => 'Dispositivo sintético']]);
        DB::table('areas')->insert(['cod_area' => 'ARE_SEG_RPT', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        foreach (['SEGUIMIENTO_DIARIO', 'CONTROL_MEDICO'] as $i => $tipo) {
            DB::table('atenciones')->insert($base + ['cod_atencion' => 'ATE_SEG_'.$i, 'cod_area' => 'ARE_SEG_RPT', 'tipo_atencion' => $tipo, 'fecha_hora' => '2026-10-27 10:00:00', 'observacion' => $tipo === 'SEGUIMIENTO_DIARIO' ? 'Nota de enfermería sintética' : 'Nota médica que no pertenece al reporte']);
        }
        RegistrosConsumo::insertar('pases_turno', [['cod_pase' => 'PAS_RPT', 'cod_residente' => $this->residente->getKey(),
            'cod_jornada_saliente' => $this->jornada->getKey(), 'cod_jornada_entrante' => $this->jornada->getKey(), 'cod_personal_saliente' => $this->personal->getKey(),
            'fecha_hora' => '2026-10-27 10:00:00', 'estado' => 'ENTREGADO', 'resumen' => 'Pase sintético', 'pendientes' => 'Pendiente sintético']]);
        $datos = app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros());
        $this->assertCount(3, $datos['filas']);
        $this->assertStringContainsString('Nota de enfermería sintética', $datos['filas']->firstWhere('categoria', 'SEGUIMIENTO')['detalle']);
        $this->assertStringNotContainsString('Nota médica', $datos['filas']->pluck('detalle')->implode(' '));
        $this->assertSame('Lucia Prueba', $datos['filas']->firstWhere('categoria', 'PASES')['profesional']);
        $this->assertSame('Colocación registrada', $datos['filas']->firstWhere('categoria', 'DISPOSITIVOS')['fecha_tipo']);
    }

    public function test_csv_con_mil_doscientos_registros_no_se_recorta_y_consulta_sin_n_mas_uno(): void
    {
        $filas = [];
        for ($i = 0; $i < 1200; $i++) {
            $filas[] = ['cod_signo' => 'SIG_VOL_'.$i, 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
                'cod_jornada' => $this->jornada->getKey(), 'fecha_hora' => '2026-10-27 10:00:00', 'presion_sistolica' => 120, 'estado' => 'VIGENTE'];
        }
        foreach (array_chunk($filas, 200) as $lote) {
            DB::table('mediciones_clinicas')->insert(array_map(static fn ($fila) => ['tipo_evento' => 'SIGNOS', 'cod_registro_medicion' => $fila['cod_signo']] + array_diff_key($fila, ['cod_signo' => true]), $lote));
        }
        DB::enableQueryLog();
        $inicio = hrtime(true);
        $datos = app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros(['tipo' => 'CONTROLES']));
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertCount(1200, $datos['filas']);
        $this->assertLessThan(30, $consultas, 'Las relaciones deben cargarse por lote');
        $respuesta = $this->get(route('admin.enfermeria.reportes.csv', $this->filtros(['tipo' => 'CONTROLES'])))->assertOk();
        $this->assertSame(1200, substr_count($respuesta->getContent(), 'Alicia Sintetica'));
        // Evidencia técnica sin nombres, valores ni datos clínicos.
        file_put_contents(storage_path('logs/reporte-volumen-testing.json'), json_encode(['filas' => 1200, 'consultas' => $consultas,
            'segundos_consulta_y_csv' => (hrtime(true) - $inicio) / 1e9, 'motor' => DB::connection()->getDriverName(), 'pico_memoria_bytes' => memory_get_peak_usage(true)]));
    }
}
