<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\EjecucionCuidado;
use App\Models\Incidente;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\PlanCuidado;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarOrigenOperativoAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarTrazaAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\VinculosProfesionalesAlertas;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AlertasOrigenUtilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_incidente_referenciado_muestra_hecho_medida_y_detalle_sin_inferir_plan(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Profesional de prueba']);
        $usuario->assignRole('ADMINISTRADOR');
        $this->actingAs($usuario);
        $residente = Residente::factory()->create();
        $incidente = Incidente::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $usuario->personal->getKey(),
            'tipo_incidente' => 'CAIDA', 'gravedad' => 'LEVE', 'lugar' => 'Patio de prueba', 'fecha_hora' => now(),
            'descripcion' => 'Hecho ficticio para comprobar el origen.', 'medida_inmediata' => 'Actuación ficticia documentada.',
            'requiere_medico' => true, 'requiere_derivacion' => false, 'estado' => 'EN_SEGUIMIENTO']);
        $alerta = Alerta::create(['cod_residente' => $residente->getKey(), 'modulo' => 'incidentes', 'cod_registro' => $incidente->getKey(),
            'tipo' => 'INCIDENTE', 'prioridad' => 'ALTA', 'estado' => 'CERRADA']);
        $traza = app(ConsultarTrazaAlerta::class)->consultar($usuario, $alerta->getKey());
        $this->assertTrue($traza['fuenteDisponible']);
        $this->assertSame('origen-operativo', $traza['inicio']);
        $nodos = collect($traza['nodos'])->keyBy('id');
        $this->assertSame('Hecho ficticio para comprobar el origen.', $nodos['origen-operativo']['texto']);
        $this->assertContains('Patio de prueba', array_column($nodos['origen-operativo']['detalles'], 'valor'));
        $this->assertSame('Actuación ficticia documentada.', $nodos['medida-origen']['texto']);
        $this->assertSame(['Sí', 'No', 'Sin registrar'], array_column($nodos['medida-origen']['detalles'], 'valor'));
        $this->assertArrayNotHasKey('plan-origen', $nodos->all());
        Livewire::test(AlertasPanel::class)->call('verDetalle', $alerta->getKey())->assertSee('Patio de prueba')
            ->assertSee('Actuación ficticia documentada.')->assertDontSee('Registro de origen no disponible en esta consulta');
        $otra = Alerta::create(['cod_residente' => Residente::factory()->create()->getKey(), 'modulo' => 'INCIDENTES', 'cod_registro' => $incidente->getKey()]);
        $this->assertFalse(app(ConsultarOrigenOperativoAlerta::class)->consultar($usuario, $otra->getKey())['disponible']);
        $usuario->roles()->firstOrFail()->revokePermissionTo('incidentes.ver');
        $usuario->unsetRelation('roles');
        DB::enableQueryLog();
        DB::flushQueryLog();
        $oculto = app(ConsultarOrigenOperativoAlerta::class)->consultar($usuario, $alerta->getKey());
        $consultas = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertFalse($oculto['disponible']);
        $this->assertCount(0, array_filter($consultas, fn ($q) => str_contains($q['query'], '"incidentes"')));
        $this->assertDatabaseCount('eventos_alerta', 0);
        $this->assertSame('CERRADA', $alerta->fresh()->estado);
        $this->assertSame('EN_SEGUIMIENTO', $incidente->fresh()->estado);
    }

    public function test_cuidado_recorre_plan_intervencion_y_ejecucion_real_con_omision_conservada(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Profesional de prueba']);
        $usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($usuario);
        $residente = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno de prueba', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        $fisioterapeuta = User::factory()->create(['nombres' => 'Profesional funcional de prueba']);
        $fisioterapeuta->assignRole('FISIOTERAPEUTA');
        $plan = PlanCuidado::create(['cod_residente' => $residente->getKey(), 'cod_area' => $area->getKey(), 'cod_personal' => $fisioterapeuta->personal->getKey(),
            'tipo_plan' => 'ENFERMERIA', 'nombre' => 'Plan de prueba de movilidad', 'objetivo_general' => 'Objetivo ficticio explícito', 'estado' => 'ACTIVO']);
        $intervencion = IntervencionCuidado::create(['cod_plan' => $plan->getKey(), 'nombre' => 'Acompañamiento de prueba', 'descripcion' => 'Indicación ficticia de cuidado', 'objetivo_especifico' => 'Objetivo específico de prueba', 'estado' => 'ACTIVA']);
        $ejecucion = EjecucionCuidado::create(['cod_residente' => $residente->getKey(), 'cod_intervencion' => $intervencion->getKey(), 'cod_personal' => $usuario->personal->getKey(),
            'cod_jornada' => $jornada->getKey(), 'fecha_hora_programada' => now()->subHour(), 'estado' => 'OMITIDA', 'motivo_omision' => 'Motivo ficticio documentado']);
        $alerta = Alerta::create(['cod_residente' => $residente->getKey(), 'modulo' => 'PLAN', 'cod_registro' => $ejecucion->getKey(), 'estado' => 'ABIERTA']);
        $traza = app(ConsultarTrazaAlerta::class)->consultar($usuario, $alerta->getKey());
        $this->assertTrue($traza['fuenteDisponible']);
        $nodos = collect($traza['nodos'])->keyBy('id');
        $this->assertSame('Área de prueba', $nodos['area-origen']['texto']);
        $vinculador = app(VinculosProfesionalesAlertas::class);
        $this->assertSame([$alerta->getKey()], $vinculador->aplicar(app(BandejaAlertas::class)->consulta($usuario), $usuario, 'FISIOTERAPEUTA')->pluck('cod_alerta')->all());
        $this->assertSame(['FISIOTERAPEUTA'], array_column($traza['especialidades'], 'rol'));
        $this->assertSame('Plan de prueba de movilidad', $nodos['plan-origen']['label']);
        $this->assertSame('Acompañamiento de prueba', $nodos['intervencion-origen']['label']);
        $this->assertContains('Motivo ficticio documentado', array_column($nodos['origen-operativo']['detalles'], 'valor'));
        $this->assertContains('Sin registrar', array_column($nodos['origen-operativo']['detalles'], 'valor'));
        $this->assertContains(['desde' => 'plan-origen', 'hasta' => 'intervencion-origen', 'label' => 'incluye intervención', 'tipo' => 'relacion'], $traza['aristas']);
        $this->assertContains(['desde' => 'intervencion-origen', 'hasta' => 'origen-operativo', 'label' => 'tiene ejecución registrada', 'tipo' => 'relacion'], $traza['aristas']);
        $this->assertSame('OMITIDA', $ejecucion->fresh()->estado);
        $this->assertNull($ejecucion->fresh()->fecha_hora_ejecucion);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }
}
