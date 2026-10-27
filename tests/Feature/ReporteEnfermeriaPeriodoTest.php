<?php

namespace Tests\Feature;

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
        foreach (['admin.enfermeria.reportes.preview', 'admin.enfermeria.reportes.pdf'] as $ruta) {
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
    }

    public function test_medicacion_prn_y_fecha_efectiva_sin_sustituir_dosis_faltante(): void
    {
        DB::table('areas')->insert(['cod_area' => 'ARE_MED_RPT', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        DB::table('atenciones')->insert(['cod_atencion' => 'ATE_RPT', 'cod_residente' => $this->residente->getKey(), 'cod_area' => 'ARE_MED_RPT',
            'cod_personal' => $this->personal->getKey(), 'tipo_atencion' => 'PRUEBA', 'fecha_hora' => '2026-10-26 10:00:00', 'estado' => 'FINALIZADA']);
        DB::table('medicamentos')->insert(['cod_medicamento' => 'MED_RPT', 'nombre_generico' => 'Medicamento sintético', 'control_especial' => false, 'estado' => 'ACTIVO']);
        DB::table('prescripciones')->insert(['cod_prescripcion' => 'PRE_RPT', 'cod_residente' => $this->residente->getKey(), 'cod_atencion' => 'ATE_RPT',
            'cod_medicamento' => 'MED_RPT', 'cod_personal' => $this->personal->getKey(), 'via_administracion' => 'ORAL', 'unidad_dosis' => 'mg',
            'segun_necesidad' => true, 'fecha_hora_prescripcion' => '2026-10-26 10:00:00', 'estado' => 'ACTIVA']);
        $base = ['cod_prescripcion' => 'PRE_RPT', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_jornada' => $this->jornada->getKey(), 'resultado' => 'ADMINISTRADA', 'estado' => 'FINALIZADO'];
        // Inserción nominal del registro PRN: evita el fallback legacy de programación del modelo.
        DB::table('administraciones_medicacion')->insert($base + ['cod_administracion' => 'ADM_PRN_RPT', 'fecha_hora_administracion' => '2026-10-27 08:00:00', 'fecha_hora_programada' => null]);
        DB::table('administraciones_medicacion')->insert($base + ['cod_administracion' => 'ADM_FUERA_RPT', 'fecha_hora_administracion' => '2026-10-28 01:00:00', 'fecha_hora_programada' => '2026-10-27 20:00:00']);
        $datos = app(ConsultarReporteEnfermeria::class)->consultar($this->enfermera, $this->filtros(['tipo' => 'MEDICACION']));
        $this->assertCount(1, $datos['filas']);
        $this->assertSame('Administración registrada', $datos['filas'][0]['fecha_tipo']);
        $this->assertSame('Medicamento sintético', $datos['filas'][0]['detalle']);
    }
}
