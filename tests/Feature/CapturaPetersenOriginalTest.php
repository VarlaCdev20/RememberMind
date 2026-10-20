<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPetersenInstitucional;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\CapturaPetersenPanel;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Unit\PetersenInstitucionalTest;

/** Todas las filas son sintéticas y efímeras; se comprueban persistencia, autoría e historia reales. */
final class CapturaPetersenOriginalTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = $this->profesional();
        $this->actingAs($this->usuario);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
    }

    private function profesional(string $rol = 'PSICOLOGO/A'): User
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole($rol);
        Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'PET-'.Str::random(10), 'profesion' => $rol === 'MEDICO GENERAL/GERIATRA' ? 'MEDICINA' : 'PSICOLOGIA', 'estado' => 'ACTIVO']);

        return $u;
    }

    private function entrada(array $cambios = []): array
    {
        return array_replace(['solicitud_uuid' => (string) Str::uuid(), 'elemento' => 'C04', 'estado_original' => 'N',
            'justificacion' => 'Revisión diferencial incompleta documentada en caso sintético.', 'fecha_hora' => '2026-10-18 10:00:00',
            'tipo_registro' => 'REGISTRO', 'soportes' => []], $cambios);
    }

    private function registrar(array $e): ValoracionPetersenOriginal
    {
        return app(RegistrarValoracionPetersenOriginal::class)->ejecutar($this->usuario, $this->residente, $e);
    }

    public function test_guarda_autoria_de_la_sesion_y_reintento_idempotente_sin_inventar_suficiencia(): void
    {
        $e = $this->entrada();
        $a = $this->registrar($e);
        self::assertSame($a->getKey(), $this->registrar($e)->getKey());
        self::assertSame($this->usuario->personal->getKey(), $a->cod_personal);
        self::assertSame($this->usuario->getKey(), $a->cod_usuario_registro);
        self::assertDatabaseCount('valoraciones_petersen_originales', 1);
        self::assertSame(1, DB::table('activity_log')->where('event', 'valoracion_original_petersen_registrada')->count());
        $r = app(ConsultarPetersenInstitucional::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertNull($r['patron']);
        self::assertSame('U', $r['requisitos']['C04']['estado']);
        self::assertFalse($r['persistido']);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_misma_solicitud_con_otro_contenido_falla_sin_otra_fila(): void
    {
        $e = $this->entrada();
        $guardada = $this->registrar($e);
        try {
            $this->registrar(array_replace($e, ['justificacion' => 'Otro contenido de la misma solicitud.']));
            self::fail('Debió rechazar el conflicto.');
        } catch (DomainException $ex) {
            self::assertStringContainsString('otro contenido', $ex->getMessage());
        }
        self::assertDatabaseCount('valoraciones_petersen_originales', 1);
    }

    public function test_precision_de_microsegundos_conserva_reintento_y_corte_tecnico(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:00:00.500000-04:00'));
        $e = $this->entrada(['fecha_hora' => '2026-10-18T10:00:00.123456-04:00']);
        $a = $this->registrar($e);
        self::assertSame('123456', $a->fecha_hora->format('u'));
        self::assertSame('500000', $a->registrado_en->format('u'));
        self::assertSame($a->getKey(), $this->registrar($e)->getKey());
        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:00:01-04:00'));
        $consulta = app(ConsultarPetersenInstitucional::class);
        self::assertSame(0, $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::parse('2026-10-19T12:00:00.250000-04:00'), 'VERSION-TEST')['adquisicion']['valoraciones_al_corte']);
        self::assertSame(1, $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::parse('2026-10-19T12:00:00.750000-04:00'), 'VERSION-TEST')['adquisicion']['valoraciones_al_corte']);
    }

    public function test_rectificacion_y_anulacion_conservan_original_y_corte_tecnico(): void
    {
        $a = $this->registrar($this->entrada());
        $corte = CarbonImmutable::now();
        $this->travel(1)->minutes();
        $b = $this->registrar($this->entrada(['tipo_registro' => 'RECTIFICACION', 'cod_valoracion_anterior' => $a->getKey(),
            'justificacion' => 'Rectificación propia del juicio, preservando fecha clínica.']));
        $servicio = app(ConsultarPetersenInstitucional::class);
        self::assertSame(1, $servicio->consultar($this->usuario, $this->residente, $corte, 'VERSION-TEST')['adquisicion']['valoraciones_al_corte']);
        $this->travel(1)->minutes();
        $this->registrar($this->entrada(['tipo_registro' => 'ANULACION', 'estado_original' => null, 'cod_valoracion_anterior' => $b->getKey(),
            'justificacion' => 'Anulación propia explícita.']));
        self::assertDatabaseCount('valoraciones_petersen_originales', 3);
        self::assertSame($a->justificacion, $a->fresh()->justificacion);
        $r = $servicio->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertCount(3, $r['adquisicion']['exclusiones']);
        self::assertNull($r['patron']);
    }

    public function test_otro_profesional_no_rectifica_una_cadena_ajena(): void
    {
        $a = $this->registrar($this->entrada());
        $otro = $this->profesional('MEDICO GENERAL/GERIATRA');
        try {
            app(RegistrarValoracionPetersenOriginal::class)->ejecutar($otro, $this->residente,
                $this->entrada(['tipo_registro' => 'ANULACION', 'estado_original' => null, 'cod_valoracion_anterior' => $a->getKey()]));
            self::fail('No debe anular una cadena ajena.');
        } catch (AuthorizationException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 1);
        }
    }

    public static function actoresDenegados(): array
    {
        return [['SUPERADMINISTRADOR'], ['ENFERMEROS'], ['GERENTE'], ['ADMINISTRADOR'], ['FAMILIAR']];
    }

    #[DataProvider('actoresDenegados')]
    public function test_roles_sin_competencia_no_guardan_aunque_reciban_el_permiso(string $rol): void
    {
        $u = $this->profesional($rol);
        $u->givePermissionTo(['valoraciones_petersen.ver', 'valoraciones_petersen.crear']);
        try {
            app(RegistrarValoracionPetersenOriginal::class)->ejecutar($u, $this->residente, $this->entrada());
            self::fail('No debe guardar.');
        } catch (AuthorizationException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        }
    }

    public function test_cuenta_revocada_y_autor_manipulado_no_producen_efectos(): void
    {
        try {
            $this->registrar($this->entrada(['cod_personal' => 'AUTOR-AJENO']));
            self::fail('Debe rechazar autor manipulado.');
        } catch (DomainException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        }
        DB::table('usuarios')->where('cod_usuario', $this->usuario->getKey())->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        $this->registrar($this->entrada());
    }

    public function test_soporte_de_otro_residente_falla_sin_dejar_cabecera_parcial(): void
    {
        $control = ControlCognitivo::create(['cod_residente' => Residente::factory()->create()->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'fecha_hora' => '2026-10-17 09:00:00', 'estado' => 'VIGENTE', 'observacion' => 'Fuente sintética ajena.']);
        try {
            $this->registrar($this->entrada(['elemento' => 'C01', 'estado_original' => 'S',
                'soportes' => [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $control->getKey()]]]));
            self::fail('No debe enlazar otro residente.');
        } catch (ModelNotFoundException) {
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
            self::assertDatabaseCount('soportes_valoracion_petersen', 0);
        }
    }

    public function test_sql_directo_no_sobrescribe_ni_borra_historia(): void
    {
        $a = $this->registrar($this->entrada());
        foreach (['update', 'delete'] as $operacion) {
            try {
                DB::transaction(function () use ($operacion, $a): void {
                    $q = DB::table('valoraciones_petersen_originales')->where('cod_valoracion_petersen', $a->getKey());
                    $operacion === 'update' ? $q->update(['justificacion' => 'Sobrescritura prohibida.']) : $q->delete();
                });
                self::fail('Debe conservar la historia también por SQL.');
            } catch (QueryException) {
                self::assertSame($a->justificacion, $a->fresh()->justificacion);
            }
        }
    }

    public function test_anular_no_cambia_fecha_y_rectificar_fecha_exige_razon_explicita(): void
    {
        $a = $this->registrar($this->entrada());
        foreach (['ANULACION', 'RECTIFICACION'] as $tipo) {
            try {
                $this->registrar($this->entrada(['tipo_registro' => $tipo, 'cod_valoracion_anterior' => $a->getKey(),
                    'estado_original' => $tipo === 'ANULACION' ? null : 'N', 'fecha_hora' => '2026-10-17 10:00:00']));
                self::fail('No debe cambiar la fecha sin su contrato.');
            } catch (DomainException $ex) {
                self::assertStringContainsString('fecha del acto', $ex->getMessage());
                self::assertDatabaseCount('valoraciones_petersen_originales', 1);
            }
        }
        $b = $this->registrar($this->entrada(['tipo_registro' => 'RECTIFICACION', 'cod_valoracion_anterior' => $a->getKey(),
            'fecha_hora' => '2026-10-17 10:00:00', 'error_fecha_original' => 'El original indica día 17; se digitó día 18.']));
        self::assertStringContainsString('Corrección de fecha:', $b->justificacion);
        self::assertSame('2026-10-18', $a->fresh()->fecha_hora->format('Y-m-d'));
    }

    public function test_documento_en_soporte_verifica_bytes_al_guardar_y_al_consultar(): void
    {
        $this->usuario->givePermissionTo(Permission::findOrCreate('documentos_clinicos.ver', 'web'));
        Storage::fake('local');
        $doc = DocumentoClinico::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'fecha_hora' => '2026-10-17 09:00:00', 'tipo_documento' => 'INFORME', 'titulo' => 'Revisión original sintética',
            'descripcion' => 'Revisión diferencial documentada.', 'ruta_archivo' => 'original-petersen.txt',
            'formato' => 'text/plain', 'hash_archivo' => hash('sha256', 'original'), 'estado' => 'VIGENTE']);
        $e = $this->entrada(['estado_original' => 'S', 'revision_explicita' => true, 'factores_pertinentes_revisados' => true,
            'impedimento_no_resuelto' => false, 'soportes' => [['tipo_soporte' => 'DIFERENCIAL', 'cod_documento_clinico' => $doc->getKey()]]]);
        try {
            $this->registrar($e);
            self::fail('Falta el archivo original.');
        } catch (DomainException $ex) {
            self::assertStringContainsString('no es verificable', $ex->getMessage());
            self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        }
        Storage::disk('local')->put('original-petersen.txt', 'original');
        $guardada = $this->registrar($e);
        $consulta = app(ConsultarPetersenInstitucional::class);
        self::assertSame('S', $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST')['requisitos']['C04']['estado']);
        Storage::disk('local')->put('original-petersen.txt', 'alterado');
        self::assertSame($guardada->getKey(), $this->registrar($e)->getKey());
        $r = $consulta->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame('U', $r['requisitos']['C04']['estado']);
        self::assertSame('DOCUMENTO_ORIGINAL_NO_VERIFICABLE', $r['adquisicion']['exclusiones'][0]['motivo']);
    }

    /** Recorre SQL → captura → juicios → cuatro patrones; sin sembrar una clasificación esperada. */
    #[DataProviderExternal(PetersenInstitucionalTest::class, 'patrones')]
    public function test_caso_completo_se_clasifica_desde_mediciones_y_soportes_originales(array $alterados, string $patron): void
    {
        $area = Area::create(['nombre' => 'Área de ensayo sintético', 'estado' => 'ACTIVA']);
        $atenciones = [];
        foreach (range(0, 9) as $i) {
            $atenciones[] = Atencion::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_area' => $area->getKey(), 'tipo_atencion' => 'VALORACION', 'fecha_hora' => $i === 5 ? '2026-10-10 09:00:00' : '2026-10-15 09:00:00', 'estado' => 'FINALIZADA']);
        }
        $metodos = ['COG-MEM' => 'HVLT-R-BO-2015-F5', 'COG-ATE' => 'BTA-BO-2015-NL', 'COG-EJE' => 'TMT-B-BO-2015',
            'COG-LEN' => 'BNT-BO-2015-60', 'COG-VIS' => 'ROCF-BO-2015-COPIA-A'];
        $valores = ['ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4, 'demora_minutos' => 22,
            'escolaridad_anios' => 4, 'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7, 'segundos_parte_b' => 180.5,
            'errores_observados' => 0, 'aciertos_espontaneos' => 40, 'aciertos_clave_semantica' => 2, 'puntaje_copia' => 22.5, 'elementos_revisados' => 18];
        DB::table('residentes')->where('cod_residente', $this->residente->getKey())->update(['fecha_nacimiento' => '1950-01-01']);
        $perfiles = $mediciones = [];
        foreach ($metodos as $dominio => $metodo) {
            $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
            $i = count($perfiles);
            $inst = Instrumento::create(['codigo' => 'PET-TEST-'.$i, 'nombre' => 'Instrumento sintético '.$i, 'tipo' => 'COGNITIVO', 'version' => 'TEST-1', 'estado' => 'ACTIVO']);
            $app = AplicacionInstrumento::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_atencion' => $atenciones[$i]->getKey(), 'cod_instrumento' => $inst->getKey(), 'fecha_hora' => '2026-10-15 09:00:00', 'estado' => 'COMPLETA']);
            $selectores = [];
            foreach ($meta['entradas'] as $campo => $tipo) {
                $q = PreguntaInstrumento::create(['cod_instrumento' => $inst->getKey(), 'codigo' => 'ENTRADA-'.count($selectores),
                    'enunciado' => 'Medida sintética '.$campo, 'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO', 'orden' => count($selectores), 'estado' => 'ACTIVA']);
                $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
                $selectores[$campo] = ['cod_pregunta' => $q->getKey(), 'campo' => $columna];
                RespuestaInstrumento::create(['cod_aplicacion' => $app->getKey(), 'cod_pregunta' => $q->getKey(),
                    $columna => $tipo === 'LOGICO' ? true : $valores[$campo]]);
            }
            $perfiles[] = ['id' => 'PET-PERFIL-'.$i, 'cod_instrumento' => $inst->getKey(), 'codigo_instrumento' => $inst->codigo,
                'version_instrumento' => 'TEST-1', 'metodo' => $metodo, 'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];
            $mediciones[$dominio] = $app->getKey();
        }
        config(['sistema_experto.perfiles_instrumentales' => $perfiles]);
        $revisados = [];
        foreach ($mediciones as $dominio => $aplicacion) {
            $juicio = $this->registrar($this->entrada(['elemento' => $dominio, 'estado_original' => in_array($dominio, $alterados, true) ? 'A' : 'P',
                'alcance_dominio' => true, 'contexto_interpretado' => true,
                'soportes' => [['tipo_soporte' => 'MEDICION', 'cod_aplicacion' => $aplicacion]]]));
            $revisados[] = ['tipo_soporte' => 'DOMINIO_REVISADO', 'cod_valoracion_revisada' => $juicio->getKey()];
        }
        $cambios = [];
        foreach ([['2026-10-10 09:00:00', 5], ['2026-10-15 09:00:00', 6]] as [$fecha, $i]) {
            $cambios[] = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_atencion' => $atenciones[$i]->getKey(), 'fecha_hora' => $fecha, 'observacion' => 'Funcionamiento y cambio corroborados, caso sintético.', 'estado' => 'VIGENTE']);
        }
        $this->registrar($this->entrada(['elemento' => 'C01', 'estado_original' => 'S', 'cambio_respecto_previo' => true, 'corroboracion_independiente' => true,
            'soportes' => [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $cambios[0]->getKey()],
                ['tipo_soporte' => 'CORROBORACION', 'cod_control_cognitivo' => $cambios[1]->getKey()]]]));
        $iadl = ControlFuncionalIadl::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'cod_usuario_registro' => $this->usuario->getKey(), 'cod_atencion' => $atenciones[7]->getKey(), 'fecha_hora' => '2026-10-15 09:00:00', 'registrado_en' => '2026-10-15 10:00:00',
            'actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA', 'desempeno' => 'INDEPENDIENTE', 'tipo_ayuda' => 'NINGUNA', 'causa_ayuda' => 'NINGUNA',
            'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO']);
        $this->registrar($this->entrada(['elemento' => 'C03', 'estado_original' => 'S', 'revision_integrada' => true, 'oportunidad_adecuada' => true, 'atribucion_revisada' => true,
            'soportes' => [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $iadl->getKey()]]]));
        foreach (['C04', 'C05'] as $elemento) {
            $psico = ValoracionPsicologica::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
                'cod_atencion' => $elemento === 'C04' ? $atenciones[8]->getKey() : $atenciones[9]->getKey(),
                'fecha_hora' => '2026-10-15 09:00:00', 'conclusion' => 'Revisión original explícita del conjunto, caso sintético.', 'estado' => 'VIGENTE']);
            $condiciones = $elemento === 'C04' ? ['revision_explicita' => true, 'factores_pertinentes_revisados' => true, 'impedimento_no_resuelto' => false]
                : ['suficiencia_conjunto' => true, 'condiciones_revisadas' => true, 'exclusiones_revisadas' => true];
            $this->registrar($this->entrada(['elemento' => $elemento, 'estado_original' => 'S', ...$condiciones,
                'soportes' => [['tipo_soporte' => $elemento === 'C04' ? 'DIFERENCIAL' : 'CALIDAD', 'cod_valoracion_psicologica' => $psico->getKey()],
                    ...($elemento === 'C05' ? $revisados : [])]]));
        }
        $r = app(ConsultarPetersenInstitucional::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame($patron, $r['patron'], json_encode(['dominios' => array_column($r['dominios'], 'estado'),
            'requisitos' => array_column($r['requisitos'], 'estado'), 'adquisicion' => $r['adquisicion']], JSON_UNESCAPED_UNICODE));
        self::assertSame([], $r['impedimentos']);
        self::assertDatabaseCount('valoraciones_petersen_originales', 9);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        config(['sistema_experto.perfiles_instrumentales' => []]);
        $sinPerfil = app(ConsultarPetersenInstitucional::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertNull($sinPerfil['patron']);
        self::assertSame('U', $sinPerfil['dominios']['COG-MEM']['estado']);
    }

    public function test_formulario_conectado_guarda_y_conserva_errores_sin_fingir_exito(): void
    {
        $area = Area::create(['nombre' => 'Psicología sintética', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'VALORACION', 'fecha_hora' => '2026-10-18 10:00:00', 'estado' => 'FINALIZADA']);
        $this->get(route('admin.sistema-experto.petersen-original', $this->residente))->assertOk()->assertSee('Valoraciones originales para Petersen');
        $panel = Livewire::test(CapturaPetersenPanel::class, ['residente' => $this->residente])->call('nuevo', 'C04')
            ->assertSee('Referencia del acto original')->set('form.cod_atencion', $atencion->getKey())
            ->set('form.cod_documento_clinico', '')->set('form.localizador_fuente', '')
            ->set('form.estado_original', 'N')->set('form.fecha_hora', '2026-10-18T10:00:00')
            ->call('guardar')->assertHasErrors('justificacion');
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        $panel->set('form.justificacion', 'Valoración original incompleta registrada explícitamente.')->call('guardar')
            ->assertHasNoErrors()->assertSee('Valoración original registrada')->assertSet('solicitud', null);
        self::assertDatabaseCount('valoraciones_petersen_originales', 1);
        self::assertDatabaseHas('valoraciones_petersen_originales', ['cod_atencion' => $atencion->getKey(), 'cod_documento_clinico' => null, 'localizador_fuente' => null]);
    }

    public function test_rectificacion_iadl_respeta_el_corte_y_se_consulta_en_lote(): void
    {
        $comunes = ['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'cod_usuario_registro' => $this->usuario->getKey(), 'fecha_hora' => '2026-10-15 09:00:00', 'registrado_en' => '2026-10-15 10:00:00',
            'oportunidad' => 'OBSERVADA', 'desempeno' => 'INDEPENDIENTE', 'tipo_ayuda' => 'NINGUNA', 'causa_ayuda' => 'NINGUNA',
            'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO'];
        $a = ControlFuncionalIadl::create($comunes + ['actividad' => 'MEDICACION']);
        $b = ControlFuncionalIadl::create($comunes + ['actividad' => 'PERTENENCIAS']);
        $this->registrar($this->entrada(['elemento' => 'C03', 'estado_original' => 'S', 'revision_integrada' => true,
            'oportunidad_adecuada' => true, 'atribucion_revisada' => true,
            'soportes' => [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $a->getKey()], ['tipo_soporte' => 'IADL', 'cod_control_iadl' => $b->getKey()]]]));
        ControlFuncionalIadl::create(array_replace($comunes, ['actividad' => 'MEDICACION', 'cod_control_anterior' => $a->getKey(),
            'estado' => 'RECTIFICACION', 'registrado_en' => '2026-10-19 12:01:00']));
        $lector = app(ConsultarPetersenInstitucional::class);
        $antes = $lector->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame('S', $antes['requisitos']['C03']['estado']);

        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:02:00-04:00'));
        $consultasIadl = 0;
        DB::listen(static function ($consulta) use (&$consultasIadl): void {
            if (preg_match('/^select .*from "controles_funcionales_iadl"/s', $consulta->sql)) {
                $consultasIadl++;
            }
        });
        $despues = $lector->consultar($this->usuario, $this->residente, CarbonImmutable::now(), 'VERSION-TEST');
        self::assertSame('U', $despues['requisitos']['C03']['estado']);
        self::assertNull($despues['patron']);
        self::assertSame(2, $consultasIadl, 'La lectura original y la sucesión se resuelven en lote, sin una consulta por soporte.');
        self::assertSame('REGISTRADO', $a->fresh()->estado);
    }

    public static function metadatosManipulados(): array
    {
        return [['solicitud_uuid', '00000000-0000-4000-8000-000000000000'], ['tipo_registro', 'ANULACION'], ['cod_valoracion_anterior', 'CADENA-AJENA']];
    }

    #[DataProvider('metadatosManipulados')]
    public function test_formulario_no_sustituye_identidad_tipo_ni_cadena_bloqueados(string $campo, string $valor): void
    {
        Livewire::test(CapturaPetersenPanel::class, ['residente' => $this->residente])->call('nuevo', 'C04')
            ->set('form.estado_original', 'N')->set('form.fecha_hora', '2026-10-18T10:00:00')
            ->set('form.justificacion', 'Intento sintético con metadata manipulada.')
            ->set('form.'.$campo, $valor)->call('guardar')->assertHasErrors('registro');
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        self::assertDatabaseCount('soportes_valoracion_petersen', 0);
    }
}
