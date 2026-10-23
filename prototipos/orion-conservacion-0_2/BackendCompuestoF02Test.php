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
use App\Models\ReglaExperta;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPetersenInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\CapturaPetersenPanel;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Support\EsquemaSinTransaccion;
use Tests\Unit\PetersenInstitucionalTest;

/** Todas las filas son sintéticas y efímeras; se comprueban persistencia, autoría e historia reales. */
final class BackendCompuestoF02Test extends TestCase
{
    use EsquemaSinTransaccion;

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

    public function test_backend_real_guardado_cambios_posteriores_y_replay(): void
    {
        $alterados=['COG-MEM']; $patron='PET-01';
        $versionGobierno = app(RegistrarConocimientoPetersen::class)->ejecutar($this->usuario);
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
        self::assertSame($versionGobierno, $r['gobierno']['version']);
        self::assertCount(4, $r['gobierno']['reglas']);
        self::assertCount(8, $r['gobierno']['reglas'][0]['condiciones']);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->usuario);
        $this->usuario->givePermissionTo('documentos_clinicos.ver');
        Storage::fake('local');
        Storage::disk('local')->put('documento-f02.txt','Documento sintético adquirido.');
        $doc=DocumentoClinico::create(['cod_residente'=>$this->residente->getKey(),'cod_personal'=>$this->usuario->personal->getKey(),
            'tipo_documento'=>'INFORME','titulo'=>'Documento original F02','ruta_archivo'=>'documento-f02.txt','formato'=>'text/plain',
            'hash_archivo'=>hash('sha256','Documento sintético adquirido.'),'fecha_hora'=>now()->subSecond(),'estado'=>'VIGENTE']);
        DB::table('aplicaciones_instrumento')->where('cod_aplicacion',reset($mediciones))->update(['cod_documento_clinico'=>$doc->getKey(),'localizador_fuente'=>'Página 1']);
        $dto = app(PrepararEvaluacionInstitucional::class)->preparar($this->usuario, $this->residente, CarbonImmutable::now());
        self::assertSame($patron, $dto->contenido['petersen']['contenido']['salida']['patron']);
        self::assertCount(5, $dto->contenido['instrumental']['contenido']['capturas']['capturas']);
        $directorio = getenv('RM_F02_EVIDENCIA');
        self::assertIsString($directorio);
        if (!is_dir($directorio)) mkdir($directorio,0700,true);
        foreach (['GuardiaDesechable','ProyeccionTipada','EntradasCompuestas','RepositorioCompuesto','Artefactos','RuntimeRetenido'] as $clase) require_once __DIR__.'/'.$clase.'.php';
        $pdo=\OrionFase02\GuardiaDesechable::conectar();
        \OrionFase02\GuardiaDesechable::instalar($pdo);
        $zip=$directorio.'/motor.zip';
        $m=(new \MotorExperto\soporte\conservacion\ReleaseMotorInstrumental)->crear(base_path(),$zip,trim(shell_exec('git rev-parse HEAD')));
        $dto=$dto->vincularRelease($zip,$m['huella']);
        $artefactos=new \OrionFase02\Artefactos($directorio.'/artefactos');
        $release=$artefactos->retener($zip,$m['huella']);
        $stmt=$pdo->prepare('INSERT INTO orion_f02.releases_motor ('.implode(',',array_keys($release)).') VALUES ('.implode(',',array_fill(0,count($release),'?')).')');
        $stmt->execute(array_values($release));
        $repo=new \OrionFase02\RepositorioCompuesto($pdo);
        $fuente=(new \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion)->leer();
        self::assertNotSame($dto->contenido['instrumental']['contenido']['contexto']['evaluacion'],$dto->contenido['petersen']['contenido']['contexto']['evaluacion']);
        self::assertTrue($repo->guardar('BACKEND-REAL',$dto,$release,$fuente));
        $antes=$repo->recuperar('BACKEND-REAL');
        $replay=$artefactos->reproducir($antes['entradas'],$release,'primario',$directorio.'/replay-antes');
        self::assertSame($dto->contenido,$replay['contenido']);
        self::assertSame($dto->huella,$replay['huella']);
        $conteos=[];
        $conteos['instrumentos']=DB::table('instrumentos')->update(['nombre'=>'Instrumento modificado DESPUÉS','descripcion'=>'Cambio posterior']);
        $conteos['respuestas']=DB::table('respuestas_instrumento')->update(['valor_numero'=>0]);
        $conteos['preguntas']=DB::table('preguntas_instrumento')->update(['enunciado'=>'Definición posterior']);
        $conteos['documentos']=DB::table('documentos_clinicos')->where('cod_documento_clinico',$doc->getKey())->update(['titulo'=>'Cambio posterior','estado'=>'ANULADO']);
        Storage::disk('local')->put('documento-f02.txt','Bytes sustituidos después.');
        $appOriginal=AplicacionInstrumento::query()->findOrFail(reset($mediciones));
        $qTardia=PreguntaInstrumento::create(['cod_instrumento'=>$appOriginal->cod_instrumento,'codigo'=>'TARDIA-F02','enunciado'=>'Pregunta tardía sintética','tipo_respuesta'=>'NUMERICO','orden'=>90,'estado'=>'ACTIVA']);
        RespuestaInstrumento::create(['cod_aplicacion'=>$appOriginal->getKey(),'cod_pregunta'=>$qTardia->getKey(),'valor_numero'=>99]);
        $conteos['respuesta_insertada_tardia']=1;
        $regla=ReglaExperta::query()->where('cod_version_modelo',$versionGobierno)->where('codigo_regla',$patron)->sole();
        app(GestionarReglasExpertas::class)->revisar($this->usuario,$regla->getKey(),'RECHAZADA','Revisión posterior sintética.');
        $conteos['reglas']=1;
        $j=ValoracionPetersenOriginal::query()->where('cod_residente',$this->residente->getKey())->where('elemento','C04')->sole();
        $this->registrar($this->entrada(['elemento'=>'C04','tipo_registro'=>'ANULACION','cod_valoracion_anterior'=>$j->getKey(),
            'estado_original'=>null,'justificacion'=>'Anulación posterior, conserva original.']));
        $conteos['valoraciones_sucesoras']=1;
        config(['sistema_experto.perfiles_instrumentales'=>[],'sistema_experto.contratos_conocimiento'=>[]]);
        $despues=$repo->recuperar('BACKEND-REAL');
        self::assertSame($antes['entradas'],$despues['entradas']);
        $replay=$artefactos->reproducir($despues['entradas'],$release,'respaldo',$directorio.'/replay-despues');
        self::assertSame($dto->contenido,$replay['contenido']);
        self::assertSame($dto->huella,$replay['huella']);
        self::assertDatabaseCount('valoraciones_petersen_originales',10);
        self::assertDatabaseCount('evaluaciones_expertas',0);
        file_put_contents($directorio.'/resultado-backend.json',json_encode(['igualdad_estricta'=>true,'huella_compuesta'=>$dto->huella,
            'huella_instrumental'=>$dto->contenido['instrumental']['huella'],'huella_petersen'=>$dto->contenido['petersen']['huella'],
            'modificaciones_posteriores'=>$conteos,'ids_ramas_distintos'=>true,'sin_guardado_institucional'=>true],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }
}
