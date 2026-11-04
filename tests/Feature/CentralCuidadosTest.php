<?php

namespace Tests\Feature;

use App\Models\AsignacionResidenteJornada;
use App\Models\ControlCognitivo;
use App\Models\CuracionHerida;
use App\Models\Herida;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\RegistroConductual;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionDolor;
use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultaCentralCuidados;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\CentralCuidados;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CentralCuidadosTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Personal $personal;

    private Residente $residente;

    private Jornada $jornada;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-12 12:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('ENFERMEROS');
        $this->personal = Personal::create(['cod_personal' => 'PER_CENTRAL', 'cod_usuario' => $this->usuario->getKey(),
            'nombres' => 'Enfermera de prueba', 'apellido_paterno' => 'Central', 'numero_documento' => 'TESTCENTRAL',
            'profesion' => 'LICENCIATURA EN ENFERMERIA', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['cod_turno' => 'TUR_CENTRAL', 'nombre' => 'Jornada de prueba', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        $this->jornada = Jornada::create(['cod_jornada' => 'JOR_CENTRAL', 'cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ACTIVA']);
        $this->residente = Residente::factory()->create(['nombres' => 'Residente de prueba', 'apellido_paterno' => 'Central']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ASG_CENTRAL', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(), 'cod_jornada' => $this->jornada->getKey(), 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($this->usuario);
    }

    private function registro(string $clase, array $atributos = [])
    {
        $modelo = new $clase;
        $datos = [
            $modelo->getKeyName() => 'REG_'.strtoupper(bin2hex(random_bytes(5))),
            'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_jornada' => $this->jornada->getKey(), 'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE',
        ];
        if ($clase === ValoracionDolor::class) {
            unset($datos['cod_jornada']);
        }

        return $clase::create(array_merge($datos, $atributos));
    }

    private function filtros(array $extra = []): array
    {
        return array_merge(['tipo' => '', 'residente' => '', 'desde' => '2026-10-06', 'hasta' => '2026-10-12'], $extra);
    }

    public function test_central_reune_los_diez_tipos_y_distingue_heridas_y_curaciones(): void
    {
        $this->registro(SignoVital::class, ['frecuencia_cardiaca' => 72]);
        $this->registro(ValoracionDolor::class, ['intensidad' => 0, 'tipo_dolor' => 'LOCALIZADO']);
        $this->registro(ControlCognitivo::class, ['confusion' => false]);
        $this->registro(RegistroConductual::class, ['descripcion' => 'Observación de prueba']);
        RegistroSueno::create(['cod_registro_sueno' => 'SUE_CENTRAL', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(), 'fecha' => today(), 'horas_sueno' => 7, 'estado' => 'VIGENTE']);
        $this->registro(RegistroIngesta::class, ['tipo_comida' => 'DESAYUNO', 'porcentaje_consumido' => 75]);
        $this->registro(RegistroHidratacion::class, ['cantidad_ml' => 150]);
        $this->registro(RegistroEliminacion::class, ['tipo_eliminacion' => 'URINARIA']);
        $this->registro(RegistroMovilidad::class, ['marcha' => 'INDEPENDIENTE']);
        $herida = Herida::create(['cod_herida' => 'HER_CENTRAL', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(), 'tipo_herida' => 'Herida de prueba', 'ubicacion' => 'Brazo izquierdo', 'fecha_hora_identificacion' => now()->subHour(), 'estado' => 'CERRADA']);
        CuracionHerida::create(['cod_curacion' => 'CUR_CENTRAL', 'cod_herida' => $herida->getKey(), 'cod_personal' => $this->personal->getKey(), 'fecha_hora' => now(), 'tejido' => 'Tejido de prueba', 'procedimiento' => 'Curación de prueba']);
        $consulta = app(ConsultaCentralCuidados::class);
        $datos = $consulta->consultar($this->usuario, $this->filtros());
        $this->assertSame(11, $datos['total']);
        $this->assertCount(10, $datos['resumen']);
        $this->assertSame(2, $datos['resumen']['heridas']['total']);
        $this->assertSame(1, $datos['residentes_con_registros']);
        $sueno = $datos['registros']->getCollection()->firstWhere('origen', 'sueno');
        $this->assertSame('12/10/2026', $sueno['fecha']);
        $cognicion = $datos['registros']->getCollection()->firstWhere('origen', 'cognicion');
        $this->assertContains('No', array_column($cognicion['campos'], 'valor'));
        $detalle = $consulta->detalle($this->usuario, 'curaciones', 'CUR_CENTRAL');
        $this->assertContains('Tejido de prueba', array_column($detalle['campos'], 'valor'));
        $this->assertContains('Herida de prueba · Brazo izquierdo', array_column($detalle['campos'], 'valor'));
        $this->get(route('admin.enfermeria.cuidados'))->assertOk()->assertSee('Resumen general')->assertSee('Resumen por cuidado');
    }

    public function test_filtros_paginacion_y_anulaciones_preservan_historia(): void
    {
        for ($i = 0; $i < 21; $i++) {
            $this->registro(SignoVital::class, ['estado' => $i === 0 ? 'ANULADO' : 'VIGENTE']);
        }
        $this->registro(SignoVital::class, ['fecha_hora' => '2026-09-01 10:00:00']);
        $this->registro(SignoVital::class, ['fecha_hora' => '2026-10-13 10:00:00']);
        $consulta = app(ConsultaCentralCuidados::class);
        $datos = $consulta->consultar($this->usuario, $this->filtros(['tipo' => 'signos']));
        $this->assertSame(21, $datos['total']);
        $this->assertSame(1, $datos['resumen']['signos']['anulados']);
        $this->assertCount(20, $datos['registros']);
        $this->assertCount(1, $consulta->consultar($this->usuario, $this->filtros(['tipo' => 'signos']), 2)['registros']);
        Livewire::test(CentralCuidados::class)->assertSee('1 anulados incluidos')->call('verRegistros', 'signos')->assertSet('vista', 'registros')->assertSee('Ver detalle');
    }

    public function test_permiso_por_tipo_se_revalida_en_detalle_y_filtros(): void
    {
        $registro = $this->registro(ValoracionDolor::class, ['intensidad' => 0]);
        $this->usuario->roles->first()->revokePermissionTo('valoraciones_dolor.ver');
        $this->usuario->refresh();
        $datos = app(ConsultaCentralCuidados::class)->consultar($this->usuario, $this->filtros());
        $this->assertArrayNotHasKey('dolor', $datos['tipos']);
        $this->assertSame(0, $datos['total']);
        Livewire::test(CentralCuidados::class)->call('abrirDetalle', 'dolor', $registro->getKey())->assertForbidden();
        Livewire::test(CentralCuidados::class)->set('tipo', 'dolor')->assertForbidden();
    }

    public function test_residente_ajeno_no_se_filtra_ni_abre_por_id(): void
    {
        $ajeno = Residente::factory()->create(['nombres' => 'Residente ajeno']);
        $registro = $this->registro(RegistroHidratacion::class, ['cod_residente' => $ajeno->getKey(), 'cantidad_ml' => 200]);
        $this->assertSame(0, app(ConsultaCentralCuidados::class)->consultar($this->usuario, $this->filtros())['total']);
        Livewire::test(CentralCuidados::class)->set('residente', $ajeno->getKey())->assertForbidden();
        Livewire::test(CentralCuidados::class)->call('abrirDetalle', 'hidratacion', $registro->getKey())->assertForbidden();
    }

    public function test_familia_inactividad_y_permiso_general_no_permiten_consulta(): void
    {
        $familiar = User::factory()->create(['estado' => 'ACTIVO']);
        $familiar->assignRole('FAMILIAR');
        $familiar->givePermissionTo(['enfermeria.ver_dashboard', 'signos_vitales.ver']);
        $this->actingAs($familiar)->get(route('admin.enfermeria.cuidados'))->assertForbidden();
        $this->usuario->update(['estado' => 'INACTIVO']);
        $this->actingAs($this->usuario)->get(route('admin.enfermeria.cuidados'))->assertForbidden();
        $this->usuario->update(['estado' => 'ACTIVO']);
        $this->usuario->roles->first()->revokePermissionTo('enfermeria.ver_dashboard');
        $this->actingAs($this->usuario->fresh())->get(route('admin.enfermeria.cuidados'))->assertForbidden();
    }

    public function test_fechas_invalidas_muestran_error_y_no_fingen_ausencia_de_datos(): void
    {
        Livewire::test(CentralCuidados::class)->set('desde', '2026-10-12')->set('hasta', '2026-10-01')
            ->assertHasErrors('hasta')->assertSee('Revisa los filtros')->assertDontSee('Sin registros para estos filtros');
    }

    public function test_detalle_abierto_se_reautoriza_si_se_retira_la_asignacion(): void
    {
        $registro = $this->registro(RegistroHidratacion::class, ['cantidad_ml' => 100]);
        $pantalla = Livewire::test(CentralCuidados::class)->call('abrirDetalle', 'hidratacion', $registro->getKey())->assertSet('mostrarDetalle', true);
        AsignacionResidenteJornada::where('cod_residente', $this->residente->getKey())->update(['estado' => 'INACTIVA']);
        $pantalla->call('cerrarRegistro')->assertForbidden();
    }

    public function test_superadministrador_consulta_sin_recibir_registro_clinico(): void
    {
        $supervisor = User::factory()->create(['estado' => 'ACTIVO']);
        $supervisor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($supervisor)->get(route('admin.enfermeria.cuidados'))->assertOk()
            ->assertDontSee('wire:click="abrirRegistro"', false);
        Livewire::actingAs($supervisor)->test(CentralCuidados::class)->call('abrirRegistro')->assertForbidden();
        $this->assertDatabaseCount('signos_vitales', 0);
    }

    public function test_registro_no_ofrece_formularios_con_destino_sin_permiso(): void
    {
        $this->usuario->roles->first()->revokePermissionTo('enfermeria.ver_pacientes_asignados');
        $this->actingAs($this->usuario->fresh());
        Livewire::test(CentralCuidados::class)->call('abrirRegistro')->set('registroTipo', 'signos')
            ->set('registroResidente', $this->residente->getKey())->call('continuarRegistro')->assertForbidden();
    }

    public function test_registrar_conserva_tipo_residente_y_no_persiste_al_navegar(): void
    {
        Livewire::test(CentralCuidados::class)->set('tipo', 'signos')->set('residente', $this->residente->getKey())
            ->call('abrirRegistro')->assertSet('registroTipo', 'signos')->assertSet('registroResidente', $this->residente->getKey())
            ->call('continuarRegistro')->assertRedirect(route('admin.enfermeria.pacientes', ['residente' => $this->residente->getKey(), 'cuidado' => 'signos', 'registrar' => 'cuidado']));
        $this->assertDatabaseCount('signos_vitales', 0);
        $this->get(route('admin.enfermeria.pacientes', ['residente' => $this->residente->getKey(), 'cuidado' => 'signos', 'registrar' => 'cuidado']))->assertOk()->assertSeeHtml('rm-resident-directory__register-modal--signos');
        $this->assertDatabaseCount('signos_vitales', 0);
    }
}
