<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Incidente;
use App\Models\NotaClinica;
use App\Models\ObjetivoSignoVital;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Consultas\ConsultarResumenIntegralResidente;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\FichaPaciente;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/** Verifica hechos, procedencia, período y autorización del resumen sin mutar la historia clínica. */
class EstadoEvolucionResidenteTest extends TestCase
{
    use RefreshDatabase;

    protected User $medico;

    protected Residente $residente;

    /** Prepara únicamente residentes y profesionales sintéticos en SQLite de pruebas. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        Personal::create(['cod_personal' => 'PER_ESTADO_TEST', 'cod_usuario' => $this->medico->getKey(),
            'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'ESTADO-TEST-01', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create();
        $this->actingAs($this->medico);
    }

    /** Crea un incidente original y permite variar fecha, estado o residente para los negativos. */
    protected function incidente(array $datos = []): Incidente
    {
        return Incidente::create($datos + [
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->medico->personal->getKey(),
            'tipo_incidente' => 'CAIDA', 'fecha_hora' => now()->subDay(),
            'descripcion' => 'Caída documentada en un escenario sintético.',
            'lugar' => 'Sala de pruebas', 'medida_inmediata' => 'Medida registrada por el profesional.',
            'gravedad' => 'LEVE', 'estado' => 'ABIERTO',
            'requiere_medico' => false, 'requiere_derivacion' => false,
        ]);
    }

    /** Crea una toma sintética sin activar detección ni persistencia de alertas. */
    protected function signo(array $datos = []): SignoVital
    {
        return SignoVital::create($datos + [
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->medico->personal->getKey(),
            'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE',
        ]);
    }

    /** Consulta el servicio real, incluyendo su reautorización. */
    protected function resumen(string $periodo = '30d'): array
    {
        return app(ConsultarResumenIntegralResidente::class)->consultar($this->residente->fresh(), $periodo);
    }

    /** Obtiene un hallazgo por su clave técnica para comprobar su contenido descriptivo. */
    protected function hallazgo(array $resumen, string $clave): array
    {
        return collect($resumen['hallazgos'])->firstWhere('clave', $clave);
    }

    /** El vacío describe registros ausentes y no atribuye ausencia de riesgo. */
    public function test_sin_datos_presenta_estados_vacios_precisos_en_la_ficha(): void
    {
        $resumen = $this->resumen();
        $this->assertSame(0, $resumen['total_caidas']);
        $this->assertSame(0, $resumen['total_signos']);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('Estado y evolución del residente')
            ->assertSee('No se registraron caídas en el período seleccionado.')
            ->assertSee('No se registraron signos vitales en el período seleccionado.')
            ->assertDontSee('El residente no tiene riesgo de caída');
    }

    /** Una alerta asociada nunca duplica el incidente ni altera su gravedad registrada. */
    public function test_una_caida_con_alertas_asociadas_se_cuenta_una_sola_vez(): void
    {
        $incidente = $this->incidente();
        foreach (range(1, 2) as $i) {
            Alerta::create(['cod_residente' => $this->residente->getKey(), 'modulo' => 'INCIDENTES',
                'cod_registro' => $incidente->getKey(), 'titulo' => 'Alerta sintética '.$i,
                'descripcion' => 'Seguimiento del mismo incidente.', 'estado' => 'ABIERTA']);
        }
        $resumen = $this->resumen();
        $caidas = $this->hallazgo($resumen, 'caidas');
        $this->assertSame(1, $resumen['total_caidas']);
        $this->assertSame(2, $resumen['total_alertas']);
        $this->assertSame('Se registró una caída durante los últimos 30 días.', $caidas['titulo']);
        $this->assertSame($incidente->getKey(), $caidas['evidencias'][0]['codigo']);
        $this->assertSame('LEVE', $caidas['evidencias'][0]['campos']['Gravedad registrada']);
        $this->assertDatabaseCount('incidentes', 1);
        $this->assertDatabaseCount('alertas', 2);
    }

    /** Dos incidentes en el mismo día también son distintos; se cuentan sus claves originales. */
    public function test_varias_caidas_conservan_fechas_lugares_descripciones_y_responsables(): void
    {
        $primera = $this->incidente(['fecha_hora' => now()->subDays(2), 'gravedad' => 'GRAVE']);
        $segunda = $this->incidente(['fecha_hora' => now()->subDay(), 'lugar' => 'Pasillo sintético']);
        $caidas = $this->hallazgo($this->resumen(), 'caidas');
        $this->assertSame('Se registraron dos caídas durante los últimos 30 días.', $caidas['titulo']);
        $this->assertSame([$segunda->getKey(), $primera->getKey()], array_column($caidas['evidencias'], 'codigo'));
        $this->assertSame('Pasillo sintético', $caidas['evidencias'][0]['campos']['Lugar']);
        $this->assertSame('16/10/2026 12:00', $caidas['evidencias'][0]['fecha']);
        $this->assertSame($this->medico->personal->getKey(), $caidas['evidencias'][0]['codigo_responsable']);
        $this->incidente(['fecha_hora' => now()->subDays(2)]);
        $this->assertSame(3, $this->resumen()['total_caidas']);
    }

    /** Estados, límites temporales y pertenencia se aplican a todas las fuentes. */
    public function test_excluye_anulados_fuera_del_periodo_futuros_y_otro_residente(): void
    {
        $this->incidente(['estado' => 'ANULADO']);
        $this->incidente(['fecha_hora' => now()->subDays(30)->subSecond()]);
        $this->incidente(['fecha_hora' => now()->addSecond()]);
        $this->incidente(['cod_residente' => Residente::factory()->create()->getKey()]);
        $incluido = $this->incidente(['fecha_hora' => now()->subDays(30), 'estado' => 'CERRADO']);
        $this->signo(['estado' => 'ANULADO', 'presion_sistolica' => 200, 'presion_diastolica' => 120]);
        $this->signo(['fecha_hora' => now()->addHour()]);
        Alerta::create(['cod_residente' => $this->residente->getKey(), 'estado' => 'ANULADA', 'titulo' => 'Excluida']);
        $resumen = $this->resumen();
        $this->assertSame(1, $resumen['total_caidas']);
        $this->assertSame($incluido->getKey(), $this->hallazgo($resumen, 'caidas')['evidencias'][0]['codigo']);
        $this->assertSame(0, $resumen['total_signos']);
        $this->assertSame(0, $resumen['total_alertas']);
        $this->assertSame(2, $this->resumen('90d')['total_caidas']);
    }

    /** El texto y la evidencia de PA proceden de las reglas existentes sin escribir alertas. */
    public function test_presion_requiere_revision_con_clasificacion_y_valores_del_evaluador(): void
    {
        $signo = $this->signo(['presion_sistolica' => 200, 'presion_diastolica' => 120]);
        $resumen = $this->resumen();
        $signos = $this->hallazgo($resumen, 'signos');
        $this->assertSame(1, $resumen['presion_revision']);
        $this->assertSame('Se registraron valores de presión arterial que requieren revisión.', $signos['titulo']);
        $evidencia = $signos['evidencias'][0];
        $this->assertSame($signo->getKey(), $evidencia['codigo']);
        $this->assertSame('CRITICO', $evidencia['clasificacion']['resultados'][0]['severidad']);
        $this->assertSame('200/120', $evidencia['clasificacion']['resultados'][0]['valor']);
        $this->assertSame('PA_CRITICA', $evidencia['clasificacion']['resultados'][0]['codigo_regla']);
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('signos_vitales', 1);
        $this->assertDatabaseCount('diagnosticos', 0);
    }

    /** Respeta objetivos históricos, incluso reemplazados, y no interpreta valores sin regla. */
    public function test_objetivos_personalizados_historicos_y_valores_sin_regla(): void
    {
        ObjetivoSignoVital::create(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->medico->personal->getKey(), 'parametro' => 'presion_sistolica',
            'min_objetivo' => 100, 'max_objetivo' => 150, 'vigente_desde' => now()->subDays(3),
            'vigente_hasta' => now()->subHours(2), 'estado' => 'REEMPLAZADO', 'motivo' => 'Objetivo sintético previo.']);
        $this->signo(['fecha_hora' => now()->subDay(), 'presion_sistolica' => 140, 'presion_diastolica' => 90]);
        $sinRegla = $this->signo(['presion_sistolica' => 140, 'presion_diastolica' => 90]);
        $resumen = $this->resumen();
        $evidencias = collect($this->hallazgo($resumen, 'signos')['evidencias']);
        $this->assertSame(0, $resumen['presion_revision']);
        $this->assertSame('Sin interpretación automática', $evidencias->firstWhere('codigo', $sinRegla->getKey())['clasificacion']['label']);
        $this->assertSame('OBJETIVO_MEDICO', $evidencias->last()['clasificacion']['resultados'][0]['fuente_evaluacion']);
        $this->assertDatabaseCount('alertas', 0);
    }

    /** La precarga de 60 controles y 25 alertas de la cabecera no limita el relato del período. */
    public function test_no_trunca_los_recuentos_a_los_limites_de_la_cabecera(): void
    {
        foreach (range(1, 61) as $i) {
            $this->signo(['fecha_hora' => now()->subMinutes($i)]);
        }
        foreach (range(1, 26) as $i) {
            Alerta::create(['cod_residente' => $this->residente->getKey(), 'titulo' => 'Alerta sintética '.$i]);
        }
        $resumen = $this->resumen();
        $this->assertSame(61, $resumen['total_signos']);
        $this->assertSame(26, $resumen['total_alertas']);
    }

    /** Reconocer, asignar o atender una alerta no la elimina del relato del período. */
    public function test_conserva_las_alertas_en_todos_los_estados_del_ciclo_existente(): void
    {
        $estados = ['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE', 'EN_ATENCION', 'ATENDIDA', 'CERRADA'];
        foreach ($estados as $estado) {
            Alerta::create(['cod_residente' => $this->residente->getKey(), 'titulo' => 'Alerta sintética '.$estado,
                'estado' => $estado]);
        }
        $resumen = $this->resumen();
        $this->assertSame(7, $resumen['total_alertas']);
        $this->assertEqualsCanonicalizing($estados, array_column($this->hallazgo($resumen, 'alertas')['evidencias'], 'estado'));
    }

    /** Solo muestra contenido vigente; conserva su procedencia y escapa texto clínico en HTML. */
    public function test_notas_originales_y_seguimiento_sin_inventar_revision_medica(): void
    {
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->medico->personal->getKey(), 'cod_area' => $area->getKey()]);
        $nota = NotaClinica::create(['cod_residente' => $this->residente->getKey(), 'cod_atencion' => $atencion->getKey(),
            'cod_personal' => $this->medico->personal->getKey(), 'tipo_nota' => 'EVOLUCION',
            'contenido' => '<script>contenido_sintetico</script>', 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        NotaClinica::create(['cod_residente' => $this->residente->getKey(), 'cod_atencion' => $atencion->getKey(),
            'cod_personal' => $this->medico->personal->getKey(), 'tipo_nota' => 'EVOLUCION',
            'contenido' => 'Nota anulada no visible', 'fecha_hora' => now(), 'estado' => 'ANULADA']);
        $this->incidente(['estado' => 'EN_SEGUIMIENTO']);
        $resumen = $this->resumen();
        $this->assertSame(1, $resumen['total_observaciones']);
        $this->assertSame($nota->getKey(), $this->hallazgo($resumen, 'observaciones')['evidencias'][0]['codigo']);
        $this->assertSame(1, $this->hallazgo($resumen, 'seguimiento')['total']);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSeeHtml('&lt;script&gt;contenido_sintetico&lt;/script&gt;')
            ->assertDontSeeHtml('<script>contenido_sintetico</script>')
            ->assertSee('no confirma por sí solo una revisión médica pendiente')
            ->assertDontSee('Nota anulada no visible');
    }

    /** El selector cambia todas las fuentes y rechaza períodos manipulados sin perder selección. */
    public function test_selector_livewire_y_validacion_de_periodo(): void
    {
        $this->incidente(['fecha_hora' => now()->subDays(20)]);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('Se registró una caída durante los últimos 30 días.')
            ->call('setPeriodoEstado', '7d')->assertSet('periodoEstado', '7d')
            ->assertSee('No se registraron caídas en el período seleccionado.')
            ->call('setPeriodoEstado', 'todos')->assertHasErrors(['periodoEstado'])
            ->assertSet('periodoEstado', '7d')
            ->call('setPeriodoEstado', '90d')->assertHasNoErrors()
            ->assertSee('Se registró una caída durante los últimos 90 días.');
        $this->expectException(ValidationException::class);
        $this->resumen('todos');
    }

    /** El acceso directo y la hidratación vuelven a comprobar el usuario y su estado actual. */
    public function test_cuenta_inactiva_y_usuario_sin_alcance_no_pueden_consultar(): void
    {
        $this->incidente();
        $this->medico->update(['estado' => 'INACTIVO']);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])->assertForbidden();
        $this->actingAs(User::factory()->create(['estado' => 'ACTIVO']));
        try {
            $this->resumen();
            $this->fail('Una cuenta sin alcance no debe recibir evidencia clínica.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertDatabaseCount('incidentes', 1);
        $this->assertDatabaseCount('alertas', 0);
    }

    /** La ruta médica conserva su puerta de permiso y no entrega evidencia al visitante. */
    public function test_ruta_medica_exige_sesion_y_permiso_existente(): void
    {
        $this->incidente(['descripcion' => 'Evidencia privada sintética']);
        $ruta = route('admin.medico.residente.ficha', ['adulto' => $this->residente->getKey()]);
        auth()->forgetGuards();
        $this->get($ruta)->assertRedirect(route('login'))->assertDontSee('Evidencia privada sintética');
        $this->actingAs(User::factory()->create(['estado' => 'ACTIVO']));
        $this->get($ruta)->assertForbidden()->assertDontSee('Evidencia privada sintética');
        $this->assertDatabaseCount('incidentes', 1);
    }

    /** Una cuenta desactivada después de abrir la ficha no puede actualizar ni releer el resumen. */
    public function test_reautoriza_al_cambiar_el_periodo_despues_de_desactivar_la_cuenta(): void
    {
        $componente = Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()]);
        $this->medico->update(['estado' => 'INACTIVO']);
        $componente->call('setPeriodoEstado', '7d')->assertForbidden();
    }

    /** Mantiene la asignación de Enfermería y el límite de expediente para cuentas familiares. */
    public function test_enfermeria_sin_asignacion_y_familiar_no_reciben_evidencia_del_expediente(): void
    {
        $this->incidente(['descripcion' => 'Contenido clínico restringido']);
        foreach (['ENFERMEROS', 'FAMILIAR'] as $rol) {
            $usuario = User::factory()->create(['estado' => 'ACTIVO']);
            $usuario->assignRole($rol);
            $this->actingAs($usuario);
            try {
                $this->resumen();
                $this->fail('No se debe entregar evidencia del expediente fuera del alcance autorizado.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
        $this->assertDatabaseCount('incidentes', 1);
        $this->assertDatabaseCount('alertas', 0);
    }
}
