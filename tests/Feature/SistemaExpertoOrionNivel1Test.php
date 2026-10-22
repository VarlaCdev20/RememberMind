<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Instrumento;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Carbon\CarbonImmutable;
use Database\Seeders\PermisosLecturaIadlPetersenSeeder;
use Database\Seeders\PermisosPetersenOriginalSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class SistemaExpertoOrionNivel1Test extends TestCase
{
    use EsquemaSinTransaccion;

    private User $medico;

    private User $psicologo;

    private User $enfermero;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-21T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(PermisosPetersenOriginalSeeder::class);
        $this->seed(PermisosLecturaIadlPetersenSeeder::class);

        $this->medico = $this->crearUsuarioConRol('MEDICO GENERAL/GERIATRA', 'MEDICINA');
        $this->psicologo = $this->crearUsuarioConRol('PSICOLOGO/A', 'PSICOLOGIA');
        $this->enfermero = $this->crearUsuarioConRol('ENFERMEROS', 'ENFERMERIA');

        $this->residente = Residente::factory()->create([
            'cod_residente' => 'RES_ORION_N1',
            'nombres' => 'Manuel',
            'apellido_paterno' => 'Salvatierra',
            'apellido_materno' => 'Paz',
            'fecha_nacimiento' => '1950-05-10',
            'estado' => 'ADMITIDO',
        ]);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->medico);
        app(RegistrarConocimientoPetersen::class)->ejecutar($this->medico);
    }

    private function crearUsuarioConRol(string $rol, string $profesion): User
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole($rol);
        Personal::create([
            'cod_usuario' => $u->getKey(),
            'nombres' => 'Profesional',
            'apellido_paterno' => $rol,
            'numero_documento' => 'DOC-'.Str::random(8),
            'profesion' => $profesion,
            'estado' => 'ACTIVO',
        ]);

        if ($rol !== 'ENFERMEROS') {
            $u->givePermissionTo([
                'valoraciones_psicologicas.ver',
                'controles_cognitivos.ver',
                'controles_funcionales_iadl.ver',
                'valoraciones_petersen.ver',
                'valoraciones_petersen.crear',
            ]);
        }

        return $u;
    }

    private function entradaPetersen(array $cambios = []): array
    {
        return array_replace([
            'solicitud_uuid' => (string) Str::uuid(),
            'elemento' => 'C04',
            'estado_original' => 'S',
            'justificacion' => 'Juicio clínico original para caso sintético O.R.I.O.N. Nivel 1.',
            'fecha_hora' => '2026-10-20 10:00:00',
            'tipo_registro' => 'REGISTRO',
            'soportes' => [],
        ], $cambios);
    }

    private function registrarPetersen(User $usuario, array $e): ValoracionPetersenOriginal
    {
        return app(RegistrarValoracionPetersenOriginal::class)->ejecutar($usuario, $this->residente, $e);
    }

    private function prepararCasoSintetico(User $usuario, array $dominiosAlterados = ['COG-MEM'], bool $conIadl = true, bool $impedimentoDiferencial = false): void
    {
        app(RegistrarConocimientoInstitucional::class)->ejecutar($usuario);
        app(RegistrarConocimientoPetersen::class)->ejecutar($usuario);

        $area = Area::firstOrCreate(['cod_area' => 'ARE_ORION'], ['nombre' => 'Área de Valoración Cognitiva', 'estado' => 'ACTIVA']);
        $atenciones = [];
        for ($i = 0; $i < 10; $i++) {
            $atenciones[] = Atencion::create([
                'cod_atencion' => 'ATE_N1_'.Str::padLeft((string) $i, 2, '0'),
                'cod_residente' => $this->residente->getKey(),
                'cod_personal' => $usuario->personal->getKey(),
                'cod_area' => $area->getKey(),
                'tipo_atencion' => 'VALORACION',
                'fecha_hora' => $i === 5 ? '2026-10-10 09:00:00' : '2026-10-18 09:00:00',
                'estado' => 'FINALIZADA',
            ]);
        }

        $metodos = [
            'COG-MEM' => 'HVLT-R-BO-2015-F5',
            'COG-ATE' => 'BTA-BO-2015-NL',
            'COG-EJE' => 'TMT-B-BO-2015',
            'COG-LEN' => 'BNT-BO-2015-60',
            'COG-VIS' => 'ROCF-BO-2015-COPIA-A',
        ];

        $valores = [
            'ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4, 'demora_minutos' => 22,
            'escolaridad_anios' => 4, 'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7, 'segundos_parte_b' => 180.5,
            'errores_observados' => 0, 'aciertos_espontaneos' => 40, 'aciertos_clave_semantica' => 2, 'puntaje_copia' => 22.5, 'elementos_revisados' => 18,
        ];

        $perfiles = [];
        $mediciones = [];
        $idx = 0;
        foreach ($metodos as $dominio => $metodo) {
            $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
            $inst = Instrumento::create([
                'cod_instrumento' => 'INS_N1_'.$idx,
                'codigo' => 'ORION-INS-'.$idx,
                'nombre' => 'Instrumento Sintético '.$dominio,
                'tipo' => 'COGNITIVO',
                'version' => 'NIVEL-1',
                'estado' => 'ACTIVO',
            ]);

            $app = AplicacionInstrumento::create([
                'cod_aplicacion' => 'APP_N1_'.$idx,
                'cod_residente' => $this->residente->getKey(),
                'cod_personal' => $usuario->personal->getKey(),
                'cod_atencion' => $atenciones[$idx]->getKey(),
                'cod_instrumento' => $inst->getKey(),
                'fecha_hora' => '2026-10-18 09:00:00',
                'estado' => 'COMPLETA',
            ]);

            $selectores = [];
            $sIdx = 0;
            foreach ($meta['entradas'] as $campo => $tipo) {
                $q = PreguntaInstrumento::create([
                    'cod_pregunta' => 'PRG_N1_'.$idx.'_'.$sIdx,
                    'cod_instrumento' => $inst->getKey(),
                    'codigo' => 'ENTRADA-'.$sIdx,
                    'enunciado' => 'Medida sintética '.$campo,
                    'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                    'orden' => $sIdx,
                    'estado' => 'ACTIVA',
                ]);
                $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
                $selectores[$campo] = ['cod_pregunta' => $q->getKey(), 'campo' => $columna];
                RespuestaInstrumento::create([
                    'cod_respuesta' => 'RSP_N1_'.$idx.'_'.$sIdx,
                    'cod_aplicacion' => $app->getKey(),
                    'cod_pregunta' => $q->getKey(),
                    $columna => $tipo === 'LOGICO' ? true : $valores[$campo],
                ]);
                $sIdx++;
            }

            $perfiles[] = [
                'id' => 'PERFIL-N1-'.$idx,
                'cod_instrumento' => $inst->getKey(),
                'codigo_instrumento' => $inst->codigo,
                'version_instrumento' => 'NIVEL-1',
                'metodo' => $metodo,
                'identidad_instrumento' => $meta['identidad'],
                'selectores' => $selectores,
            ];
            $mediciones[$dominio] = $app->getKey();
            $idx++;
        }

        config(['sistema_experto.perfiles_instrumentales' => $perfiles]);

        // Registrar juicios originales de los 5 dominios
        $revisados = [];
        foreach ($mediciones as $dominio => $codApp) {
            $estadoOriginal = in_array($dominio, $dominiosAlterados, true) ? 'A' : 'P';
            $j = $this->registrarPetersen($usuario, $this->entradaPetersen([
                'elemento' => $dominio,
                'estado_original' => $estadoOriginal,
                'alcance_dominio' => true,
                'contexto_interpretado' => true,
                'soportes' => [['tipo_soporte' => 'MEDICION', 'cod_aplicacion' => $codApp]],
            ]));
            $revisados[] = ['tipo_soporte' => 'DOMINIO_REVISADO', 'cod_valoracion_revisada' => $j->getKey()];
        }

        // C01: Cambio corroborado
        $c1 = ControlCognitivo::create([
            'cod_control_cognitivo' => 'CC_N1_1',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $usuario->personal->getKey(),
            'cod_atencion' => $atenciones[5]->getKey(),
            'fecha_hora' => '2026-10-10 09:00:00',
            'observacion' => 'Historia previa cognitiva.',
            'estado' => 'VIGENTE',
        ]);
        $c2 = ControlCognitivo::create([
            'cod_control_cognitivo' => 'CC_N1_2',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $usuario->personal->getKey(),
            'cod_atencion' => $atenciones[6]->getKey(),
            'fecha_hora' => '2026-10-18 09:00:00',
            'observacion' => 'Corroboración de cambio.',
            'estado' => 'VIGENTE',
        ]);
        $this->registrarPetersen($usuario, $this->entradaPetersen([
            'elemento' => 'C01',
            'estado_original' => 'S',
            'cambio_respecto_previo' => true,
            'corroboracion_independiente' => true,
            'soportes' => [
                ['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $c1->getKey()],
                ['tipo_soporte' => 'CORROBORACION', 'cod_control_cognitivo' => $c2->getKey()],
            ],
        ]));

        // C03: IADL (opcional para ensayar abstenciones)
        if ($conIadl) {
            $iadl = ControlFuncionalIadl::create([
                'cod_control_iadl' => 'IADL_N1_1',
                'cod_residente' => $this->residente->getKey(),
                'cod_personal' => $usuario->personal->getKey(),
                'cod_usuario_registro' => $usuario->getKey(),
                'cod_atencion' => $atenciones[7]->getKey(),
                'fecha_hora' => '2026-10-18 09:00:00',
                'registrado_en' => '2026-10-18 10:00:00',
                'actividad' => 'MEDICACION',
                'oportunidad' => 'OBSERVADA',
                'desempeno' => 'INDEPENDIENTE',
                'tipo_ayuda' => 'NINGUNA',
                'causa_ayuda' => 'NINGUNA',
                'nivel_previo' => 'INDEPENDIENTE',
                'fuente' => 'OBSERVACION_DIRECTA',
                'estado' => 'REGISTRADO',
            ]);
            $this->registrarPetersen($usuario, $this->entradaPetersen([
                'elemento' => 'C03',
                'estado_original' => 'S',
                'revision_integrada' => true,
                'oportunidad_adecuada' => true,
                'atribucion_revisada' => true,
                'soportes' => [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $iadl->getKey()]],
            ]));
        }

        // C04: Revisión diferencial
        $psico4 = ValoracionPsicologica::create([
            'cod_valoracion_psicologica' => 'PSI_N1_4',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $usuario->personal->getKey(),
            'cod_atencion' => $atenciones[8]->getKey(),
            'fecha_hora' => '2026-10-18 09:00:00',
            'conclusion' => 'Evaluación diferencial clínica.',
            'estado' => 'VIGENTE',
        ]);
        $this->registrarPetersen($usuario, $this->entradaPetersen([
            'elemento' => 'C04',
            'estado_original' => $impedimentoDiferencial ? 'N' : 'S',
            'revision_explicita' => true,
            'factores_pertinentes_revisados' => true,
            'impedimento_no_resuelto' => $impedimentoDiferencial,
            'soportes' => [['tipo_soporte' => 'DIFERENCIAL', 'cod_valoracion_psicologica' => $psico4->getKey()]],
        ]));

        // C05: Calidad e interpretabilidad
        $psico5 = ValoracionPsicologica::create([
            'cod_valoracion_psicologica' => 'PSI_N1_5',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $usuario->personal->getKey(),
            'cod_atencion' => $atenciones[9]->getKey(),
            'fecha_hora' => '2026-10-18 09:00:00',
            'conclusion' => 'Revisión de conjunto de cinco dominios.',
            'estado' => 'VIGENTE',
        ]);
        $this->registrarPetersen($usuario, $this->entradaPetersen([
            'elemento' => 'C05',
            'estado_original' => 'S',
            'suficiencia_conjunto' => true,
            'condiciones_revisadas' => true,
            'exclusiones_revisadas' => true,
            'soportes' => [
                ['tipo_soporte' => 'CALIDAD', 'cod_valoracion_psicologica' => $psico5->getKey()],
                ...$revisados,
            ],
        ]));
    }

    public function test_medico_y_psicologo_pueden_ejecutar_analizar_evidencia_y_muestra_aviso_en_memoria(): void
    {
        $this->actingAs($this->medico);

        $test = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Evaluación cognitiva')
            ->assertSee('Analizar evidencia')
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('Resultado calculado para esta consulta:')
            ->assertSee('Este cálculo aún no está guardado como evaluación histórica completa');

        $this->assertTrue($test->get('analisisEjecutado'));

        // También psicólogo
        $this->actingAs($this->psicologo);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('Resultado calculado para esta consulta:')
            ->assertSee('Rendimiento por dominios cognitivos')
            ->assertSee('Qué se comprobó')
            ->assertSee('Controles registrados')
            ->assertSee('Ayuda para leer esta evaluación')
            ->assertSee('Fuentes y mediciones disponibles');

        // Persistencia compuesta NO inventada
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_enfermero_o_usuario_no_autorizado_es_rechazado_al_intentar_acceder_o_analizar(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertNotFound();
    }

    public function test_residente_sin_datos_muestra_cobertura_19_componentes_y_abstencion_sin_romper(): void
    {
        $this->actingAs($this->medico);

        $test = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('Fuentes y mediciones disponibles');

        // Los 19 componentes y dominios están disponibles
        $test->assertViewHas('perfilInstrumental', fn ($perfil) => count($perfil['dominios']) === 5
            && array_sum(array_map(fn ($dominio) => count($dominio['componentes']), $perfil['dominios'])) === 19);

        // Persistencia intacta
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_caso_sintetico_amnesico_unico_clasifica_pet_01(): void
    {
        $this->actingAs($this->medico);
        $this->prepararCasoSintetico($this->medico, ['COG-MEM']);

        $test = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('Perfil compatible con posible DCL amnésico de dominio único')
            ->assertSee('Cinco dominios')
            ->assertSee('Memoria · Alteración objetiva con soporte')
            ->assertSee('Atención · Rendimiento esperado con soporte')
            ->assertSee('Criterios Petersen')
            ->assertSee('Cambio corroborado: Acreditado')
            ->assertSee('Alteración objetiva: Acreditado')
            ->assertSee('Independencia instrumental relativa: Acreditado')
            ->assertSee('Evaluación diferencial: Acreditado')
            ->assertSee('Calidad e interpretabilidad: Acreditado');

        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_caso_sintetico_amnesico_multidominio_clasifica_pet_02(): void
    {
        $this->actingAs($this->medico);
        $this->prepararCasoSintetico($this->medico, ['COG-MEM', 'COG-EJE']);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('Perfil compatible con posible DCL amnésico multidominio')
            ->assertSee('Memoria · Alteración objetiva con soporte')
            ->assertSee('Funciones ejecutivas · Alteración objetiva con soporte');
    }

    public function test_caso_sintetico_no_amnesico_unico_clasifica_pet_03(): void
    {
        $this->actingAs($this->medico);
        $this->prepararCasoSintetico($this->medico, ['COG-ATE']);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('Perfil compatible con posible DCL no amnésico de dominio único')
            ->assertSee('Memoria · Rendimiento esperado con soporte')
            ->assertSee('Atención · Alteración objetiva con soporte');
    }

    public function test_caso_sintetico_no_amnesico_multidominio_clasifica_pet_04(): void
    {
        $this->actingAs($this->medico);
        $this->prepararCasoSintetico($this->medico, ['COG-ATE', 'COG-LEN']);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('Perfil compatible con posible DCL no amnésico multidominio')
            ->assertSee('Memoria · Rendimiento esperado con soporte')
            ->assertSee('Atención · Alteración objetiva con soporte')
            ->assertSee('Lenguaje · Alteración objetiva con soporte');
    }

    public function test_caso_sintetico_sin_alteracion_objetiva_emite_sin_patron_dcl(): void
    {
        $this->actingAs($this->medico);
        // Ningún dominio alterado (todos 'P')
        $this->prepararCasoSintetico($this->medico, []);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('No se produjo un patrón orientativo')
            ->assertSee('Las cinco áreas muestran rendimiento esperado documentado')
            ->assertSee('Alteración objetiva: No acreditado');
    }

    public function test_abstencion_fundamentada_cuando_falta_iadl_c03(): void
    {
        $this->actingAs($this->medico);
        // Sin IADL
        $this->prepararCasoSintetico($this->medico, ['COG-MEM'], conIadl: false);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('El perfil cognitivo aún no está determinado')
            ->assertSee('Independencia instrumental relativa: Pendiente de valoración');
    }

    public function test_abstencion_fundamentada_cuando_hay_impedimento_diferencial_c04(): void
    {
        $this->actingAs($this->medico);
        // Con impedimento clínico diferencial no resuelto
        $this->prepararCasoSintetico($this->medico, ['COG-MEM'], impedimentoDiferencial: true);

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')
            ->assertHasNoErrors()
            ->assertSee('La valoración requiere revisar posibles causas alternativas')
            ->assertSee('La revisión de posibles causas alternativas no permite completar la clasificación.')
            ->assertSee('Evaluación diferencial: No acreditado');
    }
}
