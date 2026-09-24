<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Contacto;
use App\Models\Jornada;
use App\Models\Preadmision;
use App\Models\ResidenteContacto;
use App\Models\Turno;
use App\Models\User;
use App\Services\Documentos\DocumentacionUsuarioService;
use App\Services\Identidad\GeneradorPlanillaEnfermeriaService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
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
        $residente = \App\Models\Residente::crearDesdeAdmision([
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
        $this->assertSame($enfermero->cod_usuario, $plaza['cod_usu']);
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

        $faltantes = $usados->filter()->unique()->diff(Permission::pluck('name'))->values()->all();
        $this->assertSame([], $faltantes, 'Hay comprobaciones con permisos no registrados.');
    }
}
