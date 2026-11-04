<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Incidente;
use App\Models\Instrumento;
use App\Models\ObjetivoSignoVital;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Models\ValoracionFuncional;
use App\Modulos\Clinica\Aplicacion\Consultas\ConsultarEvolucionResidente;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\FichaPaciente;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/** Casos sintéticos de ventanas, procedencia, comparabilidad y acceso de la Fase 2. */
class EvolucionComparadaResidenteTest extends TestCase
{
    use RefreshDatabase;

    protected User $medico;

    protected Residente $residente;

    protected Personal $personal;

    /** Configura un médico autorizado y un residente sintético en SQLite en memoria. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        $this->personal = Personal::create(['cod_personal' => 'PER_EVOL_TEST', 'cod_usuario' => $this->medico->getKey(),
            'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'EVOL-TEST',
            'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create();
        $this->actingAs($this->medico);
    }

    /** Ejecuta la consulta real, incluidas autorizaciones y reglas institucionales. */
    protected function evolucion(string $periodo = '30d'): array
    {
        return app(ConsultarEvolucionResidente::class)->consultar($this->residente->fresh(), $periodo);
    }

    /** Registra un incidente original, nunca una tarjeta ni una alerta sustitutiva. */
    protected function incidente(array $datos = []): Incidente
    {
        return Incidente::create($datos + ['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'tipo_incidente' => 'CAIDA',
            'fecha_hora' => now()->subDay(), 'descripcion' => 'Incidente sintético.',
            'estado' => 'ABIERTO', 'requiere_medico' => false, 'requiere_derivacion' => false]);
    }

    /** Crea un control original con valores explícitos y estado vigente por defecto. */
    protected function signo(array $datos = []): SignoVital
    {
        return SignoVital::create($datos + ['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'fecha_hora' => now()->subDay(), 'estado' => 'VIGENTE']);
    }

    /** Prepara una atención coherente con el residente para las fuentes profesionales. */
    protected function atencion(?Residente $residente = null): Atencion
    {
        $area = Area::firstOrCreate(['nombre' => 'Área sintética de evolución'], ['estado' => 'ACTIVA']);

        return Atencion::create(['cod_residente' => ($residente ?? $this->residente)->getKey(),
            'cod_personal' => $this->personal->getKey(), 'cod_area' => $area->getKey()]);
    }

    /** Crea una valoración con columnas funcionales reales, sin puntajes derivados de texto. */
    protected function funcional(array $datos = []): ValoracionFuncional
    {
        return ValoracionFuncional::create($datos + ['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'cod_atencion' => $this->atencion()->getKey(),
            'fecha_hora' => now()->subDay(), 'estado' => 'VIGENTE']);
    }

    /** Registra una aplicación de prueba con instrumento explícito y máximo persistido. */
    protected function aplicacion(Instrumento $instrumento, array $datos = []): AplicacionInstrumento
    {
        return AplicacionInstrumento::create($datos + ['cod_instrumento' => $instrumento->getKey(),
            'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'fecha_hora' => now()->subDay(), 'estado' => 'COMPLETA', 'puntaje_total' => 20, 'puntaje_maximo' => 30]);
    }

    /** Catálogo sintético sin reactivos ni contenidos protegidos. */
    protected function instrumento(string $codigo = 'QA_COGNITIVO'): Instrumento
    {
        return Instrumento::create(['codigo' => $codigo, 'nombre' => 'Instrumento sintético '.$codigo,
            'tipo' => 'COGNITIVO', 'version' => 'Versión de prueba', 'puntaje_maximo' => 30, 'estado' => 'ACTIVO']);
    }

    /** Períodos disponibles en el selector existente, con duración fija en días. */
    public static function periodos(): array
    {
        return [['7d', 7], ['30d', 30], ['90d', 90]];
    }

    /** Las dos ventanas comparten exactamente un corte y tienen idéntica duración. */
    #[DataProvider('periodos')]
    public function test_ventanas_equivalentes_con_fechas_explicitas(string $periodo, int $dias): void
    {
        $evolucion = $this->evolucion($periodo);
        $this->assertSame($dias * 86400, $evolucion['actual']['duracion_segundos']);
        $this->assertSame($evolucion['actual']['duracion_segundos'], $evolucion['anterior']['duracion_segundos']);
        $this->assertSame($evolucion['actual']['desde_iso'], $evolucion['anterior']['hasta_iso']);
        $this->assertFalse($evolucion['anterior']['incluye_final']);
        $this->assertTrue($evolucion['actual']['incluye_final']);
        $this->assertSame([], $evolucion['cronologia']);
        $this->assertSame([], $evolucion['presion']);
    }

    /** Tres caídas actuales y una anterior se justifican con cuatro incidentes distintos. */
    public function test_aumento_de_caidas_con_evidencia_y_sin_duplicacion_por_alertas(): void
    {
        $previo = $this->incidente(['fecha_hora' => now()->subDays(45)]);
        $actual = $this->incidente();
        $this->incidente(['fecha_hora' => now()->subDays(2)]);
        $this->incidente(['fecha_hora' => now()->subDays(3)]);
        foreach (range(1, 2) as $i) {
            Alerta::create(['cod_residente' => $this->residente->getKey(), 'titulo' => 'Alerta sintética '.$i,
                'modulo' => 'INCIDENTES', 'cod_registro' => $actual->getKey()]);
        }
        $evolucion = $this->evolucion();
        $caidas = collect($evolucion['comparaciones'])->firstWhere('clave', 'caidas');
        $this->assertSame('Aumentó el número de caídas registradas.', $caidas['titulo']);
        $this->assertSame('Durante los últimos 30 días se registraron 3 caídas, mientras que durante los 30 días anteriores se documentó una caída.', $caidas['descripcion']);
        $this->assertSame(3, $caidas['actual']);
        $this->assertSame(1, $caidas['anterior']);
        $this->assertSame($previo->getKey(), $caidas['registros_anteriores'][0]['codigo']);
        $this->assertSame([['tipo' => 'CAIDA', 'actual' => 3, 'anterior' => 1]], $evolucion['incidentes_por_tipo']);
        $this->assertCount(6, $evolucion['cronologia']);
        $this->assertDatabaseCount('incidentes', 4);
        $this->assertDatabaseCount('alertas', 2);
        $this->assertStringNotContainsString('neurológico', $caidas['titulo']);
    }

    /** El límite compartido pertenece solo al actual; no se incluyen futuros ni anulados. */
    public function test_limites_temporales_disjuntos_y_estados_de_incidentes(): void
    {
        $limite = $this->incidente(['fecha_hora' => now()->subDays(30)]);
        $inicio = $this->incidente(['fecha_hora' => now()->subDays(60)]);
        $corte = $this->incidente(['fecha_hora' => now()]);
        $this->incidente(['fecha_hora' => now()->subDays(60)->subSecond()]);
        $this->incidente(['fecha_hora' => now()->addSecond()]);
        $this->incidente(['estado' => 'ANULADO']);
        $evolucion = $this->evolucion();
        $caidas = collect($evolucion['comparaciones'])->firstWhere('clave', 'caidas');
        $this->assertSame(2, $caidas['actual']);
        $this->assertSame(1, $caidas['anterior']);
        $this->assertEqualsCanonicalizing([$limite->getKey(), $corte->getKey()], array_column($caidas['registros_actuales'], 'codigo'));
        $this->assertSame([$inicio->getKey()], array_column($caidas['registros_anteriores'], 'codigo'));
        $this->assertCount(3, $evolucion['cronologia']);
    }

    /** PA usa la clasificación institucional y representa solo valores dentro de las dos ventanas. */
    public function test_presion_nueva_para_revision_con_clasificacion_y_puntos_originales(): void
    {
        $anterior = $this->signo(['fecha_hora' => now()->subDays(45), 'presion_sistolica' => 120, 'presion_diastolica' => 75]);
        $actual = $this->signo(['presion_sistolica' => 200, 'presion_diastolica' => 120]);
        $this->signo(['fecha_hora' => now()->subDays(61), 'presion_sistolica' => 250, 'presion_diastolica' => 130]);
        $this->signo(['fecha_hora' => now()->addHour(), 'presion_sistolica' => 250, 'presion_diastolica' => 130]);
        $this->signo(['estado' => 'ANULADO', 'presion_sistolica' => 250, 'presion_diastolica' => 130]);
        $evolucion = $this->evolucion();
        $signos = collect($evolucion['comparaciones'])->firstWhere('clave', 'signos');
        $this->assertSame('Se registraron nuevos valores de presión arterial que requieren revisión.', $signos['titulo']);
        $this->assertSame([$anterior->getKey(), $actual->getKey()], array_column($evolucion['presion'], 'codigo'));
        $this->assertSame([120.0, 200.0], array_column($evolucion['presion'], 'sistolica'));
        $this->assertSame('CRITICO', $evolucion['presion'][1]['clasificacion']);
        $this->assertSame(1, $evolucion['actual']['resumen']['presion_revision']);
        $this->assertSame(0, $evolucion['anterior']['resumen']['presion_revision']);
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('diagnosticos', 0);
    }

    /** Un objetivo histórico reemplazado se aplica a su toma y no convierte ausencia en normalidad. */
    public function test_objetivo_historico_y_medicion_incompleta_sin_interpolar(): void
    {
        ObjetivoSignoVital::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'parametro' => 'presion_sistolica', 'min_objetivo' => 100, 'max_objetivo' => 150,
            'vigente_desde' => now()->subDays(55), 'vigente_hasta' => now()->subDays(35),
            'estado' => 'REEMPLAZADO', 'motivo' => 'Objetivo sintético histórico.']);
        $this->signo(['fecha_hora' => now()->subDays(45), 'presion_sistolica' => 140, 'presion_diastolica' => 90]);
        $this->signo(['presion_sistolica' => 130]);
        $evolucion = $this->evolucion();
        $this->assertSame('OBJETIVO_PERSONALIZADO', $evolucion['presion'][0]['clasificacion']);
        $this->assertNull($evolucion['presion'][1]['diastolica']);
        $this->assertSame(0, $evolucion['anterior']['resumen']['presion_revision']);
        $this->assertDatabaseCount('alertas', 0);
    }

    /** Las valoraciones históricas legítimas se conservan y los campos se comparan literalmente. */
    public function test_funcional_historica_compara_campos_sin_inferir_mejoria_o_gravedad(): void
    {
        $previa = $this->funcional(['fecha_hora' => now()->subDays(45), 'estado' => 'HISTORICA',
            'marcha' => 'CON_APOYO', 'necesita_supervision' => false, 'conclusion' => 'Barthel 70/100.']);
        $actual = $this->funcional(['marcha' => 'INDEPENDIENTE', 'necesita_supervision' => true, 'conclusion' => 'Barthel 90/100.']);
        $evolucion = $this->evolucion();
        $this->assertTrue($evolucion['funcional']['comparable']);
        $this->assertSame($previa->getKey(), $evolucion['funcional']['anterior']['codigo']);
        $this->assertSame($actual->getKey(), $evolucion['funcional']['actual']['codigo']);
        $marcha = collect($evolucion['funcional']['campos'])->firstWhere('etiqueta', 'Marcha');
        $this->assertSame('Valores registrados diferentes', $marcha['comparacion']);
        $this->assertSame('CON_APOYO', $marcha['anterior']);
        $this->assertSame('No', $evolucion['funcional']['anterior']['campos']['Necesita supervisión']);
        $this->assertArrayNotHasKey('Barthel', $evolucion['funcional']['actual']['campos']);
    }

    /** Una valoración antigua fuera de ventana no rellena el período anterior faltante. */
    public function test_funcionales_no_comparables_anulados_y_ajenos_al_periodo(): void
    {
        $this->funcional(['fecha_hora' => now()->subDays(61), 'marcha' => 'CON_APOYO']);
        $this->funcional(['fecha_hora' => now()->subDays(45), 'estado' => 'ANULADA', 'marcha' => 'CON_APOYO']);
        $this->funcional(['fecha_hora' => now()->addHour(), 'marcha' => 'INDEPENDIENTE']);
        $this->funcional(['marcha' => 'INDEPENDIENTE']);
        $evolucion = $this->evolucion();
        $this->assertFalse($evolucion['funcional']['comparable']);
        $this->assertNull($evolucion['funcional']['anterior']);
        $this->assertSame('Sin dato comparable', $evolucion['funcional']['campos'][0]['comparacion']);
    }

    /** Dos ausencias textuales no constituyen evidencia de igualdad funcional. */
    public function test_funcionales_vacios_o_solo_espacios_no_son_comparables(): void
    {
        $this->funcional(['fecha_hora' => now()->subDays(45), 'marcha' => '', 'equilibrio' => '   ']);
        $actual = $this->funcional(['marcha' => '', 'equilibrio' => '']);
        $evolucion = $this->evolucion();
        $this->assertFalse($evolucion['funcional']['comparable']);
        $this->assertSame([], $evolucion['funcional']['campos']);
        $actual->update(['marcha' => 'INDEPENDIENTE']);
        $evolucion = $this->evolucion();
        $this->assertFalse($evolucion['funcional']['comparable']);
        $this->assertSame('Sin dato comparable', $evolucion['funcional']['campos'][0]['comparacion']);
        $this->assertSame('No registrado', $evolucion['funcional']['campos'][0]['anterior']);
    }

    /** Solo el mismo instrumento y máximo permiten expresar una diferencia aritmética. */
    public function test_instrumentos_comparables_sin_afirmar_evolucion_cognitiva(): void
    {
        $instrumento = $this->instrumento();
        $previa = $this->aplicacion($instrumento, ['fecha_hora' => now()->subDays(45), 'puntaje_total' => 20]);
        $actual = $this->aplicacion($instrumento, ['puntaje_total' => 15]);
        $evolucion = $this->evolucion();
        $grupo = $evolucion['instrumentos']['grupos'][0];
        $this->assertTrue($grupo['comparable']);
        $this->assertSame(-5.0, $grupo['diferencia']);
        $this->assertSame($previa->getKey(), $grupo['anterior']['codigo']);
        $this->assertSame($actual->getKey(), $grupo['actual']['codigo']);
        $this->assertSame('No se dispone de evaluaciones comparables suficientes para determinar una evolución cognitiva objetiva.', $evolucion['mensaje_cognitivo']);
        $this->assertStringContainsString('versión histórica', $evolucion['limite_cognitivo']);
    }

    /** Denominadores distintos, ausentes y otro instrumento no se emparejan artificialmente. */
    public function test_instrumentos_con_maximos_distintos_o_incompletos_no_se_comparan(): void
    {
        $primero = $this->instrumento('QA_UNO');
        $segundo = $this->instrumento('QA_DOS');
        $this->aplicacion($primero, ['fecha_hora' => now()->subDays(45), 'puntaje_maximo' => 30]);
        $this->aplicacion($primero, ['puntaje_maximo' => 100]);
        $this->aplicacion($segundo, ['puntaje_maximo' => null]);
        $evolucion = $this->evolucion();
        $this->assertCount(2, $evolucion['instrumentos']['grupos']);
        foreach ($evolucion['instrumentos']['grupos'] as $grupo) {
            $this->assertFalse($grupo['comparable']);
            $this->assertNull($grupo['diferencia']);
        }
    }

    /** Las aplicaciones anuladas, futuras y antiguas no completan un par ausente. */
    public function test_aplicaciones_invalidas_o_fuera_de_ventana_no_aparecen_en_el_fundamento(): void
    {
        $instrumento = $this->instrumento();
        $actual = $this->aplicacion($instrumento);
        $this->aplicacion($instrumento, ['fecha_hora' => now()->subDays(45), 'estado' => 'ANULADA']);
        $this->aplicacion($instrumento, ['fecha_hora' => now()->subDays(61)]);
        $this->aplicacion($instrumento, ['fecha_hora' => now()->addHour()]);
        $evolucion = $this->evolucion();
        $this->assertFalse($evolucion['instrumentos']['grupos'][0]['comparable']);
        $this->assertNull($evolucion['instrumentos']['grupos'][0]['anterior']);
        $this->assertSame([$actual->getKey()], array_column($evolucion['cronologia'], 'codigo'));
    }

    /** Las fuentes profesionales de otro residente quedan fuera aunque estén dentro de las fechas consultadas. */
    public function test_aislamiento_por_residente_y_atencion_de_origen(): void
    {
        $otro = Residente::factory()->create();
        $ajena = $this->atencion($otro);
        $this->incidente(['cod_residente' => $otro->getKey()]);
        $this->signo(['cod_residente' => $otro->getKey(), 'presion_sistolica' => 200, 'presion_diastolica' => 120]);
        $this->funcional(['cod_residente' => $otro->getKey(), 'cod_atencion' => $ajena->getKey(), 'marcha' => 'DATO_AJENO']);
        $this->aplicacion($this->instrumento(), ['cod_residente' => $otro->getKey(), 'cod_atencion' => $ajena->getKey()]);
        $this->assertSame([], $this->evolucion()['cronologia']);
    }

    /** Añadir la comparación no amplía los permisos existentes de sus fuentes profesionales. */
    public function test_respeta_permisos_de_valoraciones_y_aplicaciones(): void
    {
        $this->assertTrue($this->medico->can('valoracion_enfermeria.ver'));
        $this->assertTrue($this->medico->can('aplicaciones_instrumento.ver'));
        $this->funcional(['marcha' => 'CONTENIDO_FUNCIONAL_RESTRINGIDO']);
        $this->aplicacion($this->instrumento(), ['observacion' => 'CONTENIDO_INSTRUMENTO_RESTRINGIDO']);
        Role::findByName('MEDICO GENERAL/GERIATRA')->revokePermissionTo(['valoraciones_funcionales.ver', 'valoracion_enfermeria.ver', 'aplicaciones_instrumento.ver']);
        $this->medico->unsetRelation('roles');
        $this->medico->unsetRelation('permissions');
        $evolucion = $this->evolucion();
        $this->assertFalse($evolucion['funcional']['autorizada']);
        $this->assertFalse($evolucion['instrumentos']['autorizada']);
        $this->assertSame([], $evolucion['cronologia']);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('Su cuenta no tiene permiso para consultar valoraciones funcionales.')
            ->assertDontSee('CONTENIDO_FUNCIONAL_RESTRINGIDO')->assertDontSee('CONTENIDO_INSTRUMENTO_RESTRINGIDO');
    }

    /** Reautoriza la lectura y no produce efectos clínicos cuando se rechaza una cuenta. */
    public function test_acceso_no_autorizado_a_la_consulta_comparativa(): void
    {
        $this->incidente();
        $this->medico->update(['estado' => 'INACTIVO']);
        try {
            $this->evolucion();
            $this->fail('La cuenta inactiva no debe recibir comparaciones.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertDatabaseCount('incidentes', 1);
        $this->assertDatabaseCount('alertas', 0);
    }

    /** Cambiar el selector actualiza ambas ventanas y sus fundamentos, sin mezclar el filtro de signos antiguo. */
    public function test_livewire_actualiza_comparaciones_y_cronologia(): void
    {
        $this->incidente(['fecha_hora' => now()->subDays(20)]);
        $this->incidente(['fecha_hora' => now()->subDays(45)]);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('Evolución del residente')->assertSee('Se registró el mismo número de caídas en ambos períodos.')
            ->call('setPeriodoEstado', '7d')->assertSee('No se registraron caídas en ninguno de los dos períodos.')
            ->assertSee('No se registraron datos autorizados en ninguno de los períodos.')
            ->call('setPeriodoEstado', '90d')->assertSee('Aumentó el número de caídas registradas.')
            ->call('setPeriodoEstado', 'todos')->assertHasErrors(['periodoEstado'])->assertSet('periodoEstado', '90d');
    }
}
