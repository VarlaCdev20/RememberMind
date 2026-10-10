<?php

namespace Tests\Feature;

use App\Backend\Modulos\Documentos\Servicios\DocumentacionUsuarioService;
use App\Backend\Modulos\Identidad\Servicios\GeneradorPlanillaEnfermeriaService;
use App\Exports\AdultoIndividualExport;
use App\Frontend\Livewire\Administracion\Identidad\TurnosAsignacionesPanel;
use App\Models\Residente;
use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Contacto;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\ResidenteContacto;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class IntegracionAdaptadoresV2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_documentacion_de_usuario_se_guarda_en_documentos_v2(): void
    {
        Storage::fake('public');
        $usuario = User::where('correo', 'admincasaamandita@gmail.com')->firstOrFail();
        $this->actingAs($usuario);

        $documento = app(DocumentacionUsuarioService::class)->subirDocumento(
            $usuario,
            'CI',
            UploadedFile::fake()->create('identidad.pdf', 32, 'application/pdf'),
        );

        $this->assertDatabaseHas('documentos', [
            'cod_documento' => $documento->cod_documento,
            'cod_usuario' => $usuario->cod_usuario,
            'tipo_documento' => 'CI',
            'estado' => 'CARGADO',
        ]);
        Storage::disk('public')->assertExists($documento->ruta_archivo);
    }

    public function test_contactos_y_vinculos_familiares_usan_relaciones_v2(): void
    {
        $usuario = User::where('correo', 'admincasaamandita@gmail.com')->firstOrFail();
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_TEST_V2', 'nombres' => 'Julia', 'apellido_paterno' => 'Flores',
            'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO',
        ]);
        $contacto = Contacto::create([
            'cod_contacto' => 'CON_TEST_V2', 'cod_usuario' => null, 'nombres' => 'Ana',
            'apellido_paterno' => 'Rojas', 'estado' => 'ACTIVO',
        ]);
        ResidenteContacto::create([
            'cod_residente_contacto' => 'RCO_TEST_V2', 'cod_residente' => $residente->cod_residente,
            'cod_contacto' => $contacto->cod_contacto, 'parentesco' => 'HIJA',
            'responsable_principal' => true, 'contacto_emergencia' => true,
            'autoriza_informacion' => true, 'autoriza_salida' => false, 'estado' => 'ACTIVO',
        ]);

        $this->assertTrue($contacto->adultosMayores()->whereKey($residente->cod_residente)->exists());
        $this->assertSame('HIJA', $contacto->adultosMayores()->firstOrFail()->pivot->parentesco_vinculo);
        $this->assertNotNull($usuario->documentos());
    }

    public function test_planilla_lee_plazas_desde_asignaciones_personal_v2(): void
    {
        $enfermero = User::where('correo', 'admincasaamandita@gmail.com')->firstOrFail();
        $enfermero->assignRole('ENFERMEROS');
        $turno = Turno::create([
            'cod_turno' => 'TUR_0001', 'nombre' => 'TURNO MAÑANA', 'hora_inicio' => '07:00:00',
            'hora_cierre' => '15:00:00', 'orden' => 1, 'estado' => 'ACTIVO',
        ]);
        $area = Area::create(['cod_area' => 'ARE_TEST', 'nombre' => 'ENFERMERÍA', 'estado' => 'ACTIVA']);
        $jornada = Jornada::firstOrCreate(
            ['cod_turno' => $turno->cod_turno, 'fecha_jornada' => today()->toDateString()],
            ['cod_jornada' => 'JOR_'.strtoupper(Str::random(10)), 'estado' => 'ACTIVA'],
        );
        AsignacionPersonal::create([
            'cod_jornada' => $jornada->cod_jornada, 'cod_personal' => $enfermero->personal->cod_personal,
            'cod_area' => $area->cod_area, 'funcion' => 'PLAZA:E01', 'tipo_asignacion' => 'TITULAR',
            'fecha_asignacion' => now(), 'estado' => 'ACTIVA',
        ]);

        $resultado = app(GeneradorPlanillaEnfermeriaService::class)->generar([
            'fecha_inicio' => today()->startOfWeek(), 'cantidad_semanas' => 1, 'usar_usuarios_reales' => true,
        ]);

        $plaza = collect($resultado['enfermeros'])->firstWhere('codigo', 'E01');
        $this->assertSame($enfermero->cod_usuario, $plaza['cod_usuario']);
    }

    public function test_exportacion_cognitiva_consulta_relaciones_y_columnas_canonicas_v2(): void
    {
        $personal = Personal::query()->where('cod_personal', 'PER_0001')->firstOrFail();
        $residenteCreado = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_EXPORT_V2',
            'nombres' => 'Elena',
            'apellido_paterno' => 'Mamani',
            'fecha_nacimiento' => '1942-04-10',
            'estado' => 'ADMITIDO',
        ]);
        $residente = Residente::findOrFail($residenteCreado->cod_residente);
        $instrumento = Instrumento::create([
            'cod_instrumento' => 'INS_EXPORT_V2',
            'codigo' => 'COG-EXPORT-V2',
            'nombre' => 'Evaluación cognitiva V2',
            'tipo' => 'COGNITIVO',
            'puntaje_maximo' => 30,
            'estado' => 'ACTIVO',
        ]);
        AplicacionInstrumento::create([
            'cod_aplicacion' => 'APL_EXPORT_V2',
            'cod_instrumento' => $instrumento->cod_instrumento,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => '2026-09-26 10:30:00',
            'puntaje_total' => 24,
            'puntaje_maximo' => 30,
            'clasificacion' => 'PREVENTIVO',
            'interpretacion' => 'Requiere seguimiento',
            'estado' => 'COMPLETADA',
        ]);

        $filas = (new AdultoIndividualExport($residente))->sheets()[4]->collection();

        $this->assertSame('26/09/2026', $filas->first()[0]);
        $this->assertSame('Evaluación cognitiva V2', $filas->first()[1]);
        $this->assertSame('Requiere seguimiento', $filas->first()[4]);
        $this->assertSame('PREVENTIVO', $filas->first()[5]);
        $this->assertSame($personal->nombres, $filas->first()[6]);
    }

    public function test_asignacion_de_enfermeria_rechaza_un_area_arbitraria(): void
    {
        $usuario = User::factory()->create();
        $personal = Personal::create([
            'cod_personal' => 'PER_AREA_EXPLICITA',
            'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Quispe',
            'numero_documento' => 'CI-AREA-EXPLICITA',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);
        Area::create([
            'cod_area' => 'ARE_NO_ENFERMERIA',
            'nombre' => 'Administración institucional',
            'estado' => 'ACTIVA',
        ]);

        $metodo = new \ReflectionMethod(TurnosAsignacionesPanel::class, 'resolverAreaAsignacionEnfermeria');

        try {
            $metodo->invoke(new TurnosAsignacionesPanel, $personal);
            $this->fail('La asignación no debe elegir la primera área disponible.');
        } catch (\ReflectionException $excepcion) {
            throw $excepcion;
        } catch (\Throwable $excepcion) {
            $causa = $excepcion instanceof \ReflectionException ? $excepcion : ($excepcion->getPrevious() ?? $excepcion);
            $this->assertInstanceOf(HttpException::class, $causa);
            $this->assertSame(422, $causa->getStatusCode());
        }
    }

    public function test_valoracion_inicial_es_dato_estructurado_de_preadmision(): void
    {
        $usuario = User::query()->firstOrFail();
        $preadmision = Preadmision::create([
            'cod_preadmision' => 'PRE_TEST_V2', 'cod_usuario_registro' => $usuario->cod_usuario,
            'nombres' => 'Marta', 'apellido_paterno' => 'Flores', 'fecha_nacimiento' => '1940-01-01',
            'motivo_ingreso' => 'Evaluación integral', 'fecha_solicitud' => now(), 'estado' => 'PENDIENTE',
            'valoracion_enfermeria' => ['estado_general' => 'ESTABLE', 'riesgo_caida' => 'BAJO'],
        ]);

        $this->assertSame('ESTABLE', $preadmision->fresh()->valoracion_enfermeria['estado_general']);
        $this->assertDatabaseHas('valoraciones_enfermeria_preadmision', [
            'cod_preadmision' => $preadmision->cod_preadmision,
            'estado_general' => 'ESTABLE',
            'riesgo_caida' => 'BAJO',
        ]);
        $this->assertFalse(Schema::hasColumn('preadmisiones', 'valoracion_enfermeria'));
        $this->assertFalse(Schema::hasColumn('prescripciones', 'cod_med_adulto'));
    }

    public function test_todos_los_permisos_de_ruta_existen_en_el_catalogo(): void
    {
        $registrados = Permission::pluck('name');
        $usados = collect(app('router')->getRoutes())
            ->flatMap(fn ($ruta) => collect($ruta->gatherMiddleware())
                ->filter(fn (string $middleware) => str_starts_with($middleware, 'permission:'))
                ->flatMap(fn (string $middleware) => explode('|', substr($middleware, 11))))
            ->unique();

        $this->assertSame([], $usados->diff($registrados)->values()->all(), 'Hay rutas con permisos no registrados.');
    }

    public function test_todos_los_permisos_explicitos_de_codigo_y_vistas_existen(): void
    {
        $usados = collect();

        foreach ([app_path(), resource_path('views')] as $directorio) {
            foreach (File::allFiles($directorio) as $archivo) {
                $contenido = File::get($archivo->getPathname());

                preg_match_all('/(?:->|\?->)can\(\s*[\'\"]([^\'\"]+)[\'\"]|@can(?:not)?\(\s*[\'\"]([^\'\"]+)[\'\"]/m', $contenido, $simples, PREG_SET_ORDER);
                foreach ($simples as $coincidencia) {
                    $usados->push($coincidencia[1] ?: $coincidencia[2]);
                }

                preg_match_all('/(?:canAny|@canany)\(\s*\[([^\]]*)\]/s', $contenido, $listas);
                foreach ($listas[1] as $lista) {
                    preg_match_all('/[\'\"]([a-z_]+(?:\.[a-z_]+)+)[\'\"]/', $lista, $permisos);
                    $usados->push(...$permisos[1]);
                }
            }
        }

        // Las habilidades de Policy (por ejemplo, viewAny) no son permisos Spatie.
        $faltantes = $usados->filter(fn ($permiso) => is_string($permiso) && str_contains($permiso, '.'))
            ->unique()->diff(Permission::pluck('name'))->values()->all();
        $this->assertSame([], $faltantes, 'Hay comprobaciones con permisos no registrados.');
    }
}
