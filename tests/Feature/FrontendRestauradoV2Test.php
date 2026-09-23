<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Residente;
use App\Models\Instrumento;
use App\Models\Personal;
use App\Livewire\Admisiones\PreadmisionWizard;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Valoraciones\EvaluacionGeriatricaModal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\TestCase;

class FrontendRestauradoV2Test extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_portada_y_login_originales_siguen_disponibles(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_superadministrador_puede_cargar_el_dashboard_original_sobre_bdd_v2(): void
    {
        $usuario = $this->superadministrador();

        $this->actingAs($usuario)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Centro de Mando');
    }

    public function test_modulos_visuales_principales_no_responden_con_error_de_servidor(): void
    {
        $usuario = $this->superadministrador();

        $this->actingAs($usuario);
        $this->withoutExceptionHandling();

        $rutas = [
            '/admin/usuarios',
            '/admin/admisiones/preadmisiones',
            '/admin/adultos-mayores',
            '/admin/areas-institucionales',
            '/admin/turnos-asignaciones',
            '/admin/habitaciones',
            '/admin/enfermeria/dashboard',
            '/admin/medico/dashboard',
            '/admin/psicologia/dashboard',
            '/admin/salud-seguimiento',
            '/admin/reportes/institucional',
        ];

        $errores = [];

        foreach ($rutas as $ruta) {
            try {
                $estado = $this->get($ruta)->getStatusCode();
                if ($estado >= 500) {
                    $errores[$ruta] = (string) $estado;
                }
            } catch (\Throwable $excepcion) {
                $marcoAplicacion = collect($excepcion->getTrace())
                    ->first(fn (array $marco) => isset($marco['file']) && str_contains($marco['file'], DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR));
                $origen = $marcoAplicacion
                    ? $marcoAplicacion['file'].':'.($marcoAplicacion['line'] ?? '?')
                    : $excepcion->getFile().':'.$excepcion->getLine();
                $errores[$ruta] = $excepcion->getMessage().' @ '.$origen;
            }
        }

        $this->assertSame([], $errores, 'Hay módulos visuales que aún fallan al consultar la BDD V2.');
    }

    public function test_pantallas_admin_sin_parametros_no_fallan_por_dependencias_legacy(): void
    {
        $usuario = $this->superadministrador();
        $this->actingAs($usuario);
        $this->withoutExceptionHandling();

        $rutas = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($ruta) => in_array('GET', $ruta->methods(), true))
            ->map(fn ($ruta) => $ruta->uri())
            ->filter(fn (string $uri) => str_starts_with($uri, 'admin/') && !str_contains($uri, '{'))
            ->reject(fn (string $uri) => str_contains($uri, '/pdf') || str_contains($uri, '/csv') || str_contains($uri, '/excel'))
            ->unique()
            ->values();

        $errores = [];
        foreach ($rutas as $uri) {
            try {
                $respuesta = $this->get('/'.$uri);
                if ($respuesta->getStatusCode() >= 500) {
                    $errores[$uri] = $respuesta->getStatusCode();
                }
            } catch (\Throwable $excepcion) {
                $errores[$uri] = $excepcion->getMessage();
            }
        }

        $this->assertSame([], $errores, 'Persisten pantallas sin parámetros que consultan estructuras legacy.');
    }

    public function test_wizard_original_registra_preadmision_contacto_y_documentos_en_v2(): void
    {
        Storage::fake('public');
        $usuario = $this->superadministrador();
        $this->actingAs($usuario);

        $componente = app(PreadmisionWizard::class);
        $componente->mount();
        foreach ([
                'nombres' => 'Rosa',
                'ap_paterno' => 'Mamani',
                'ci' => 'PRE-778899',
                'expedicion_ci' => 'LP',
                'fecha_nac' => '1945-05-10',
                'genero' => 'FEMENINO',
                'estado_civil' => 'SOLTERA',
                'celular' => '70000001',
                'departamento_residencia' => 'LA PAZ',
                'ciudad_municipio' => 'LA PAZ',
                'zona' => 'CENTRO',
                'calle' => 'CALLE 1',
                'familiar_nombres' => 'Ana',
                'familiar_ap_paterno' => 'Mamani',
                'familiar_ci' => 'FAM-778899',
                'familiar_parentesco' => 'HIJA',
                'familiar_celular' => '70000002',
                'familiar_direccion' => 'CALLE 2',
                'motivo_ingreso' => 'CUIDADO INTEGRAL',
                'procedencia_ingreso' => 'FAMILIAR',
                'descripcion_caso' => 'Requiere acompañamiento y cuidados institucionales.',
                'doc_ci_adulto' => UploadedFile::fake()->image('ci-adulto.jpg'),
                'doc_ci_familiar' => UploadedFile::fake()->image('ci-familiar.jpg'),
                'doc_solicitud_ingreso' => UploadedFile::fake()->create('solicitud.pdf', 10, 'application/pdf'),
            ] as $propiedad => $valor) {
            $componente->{$propiedad} = $valor;
        }
        $componente->confirmarPreadmision();

        $this->assertTrue($componente->guardadoExitoso);

        $this->assertDatabaseHas('preadmisiones', [
            'numero_documento' => 'PRE-778899',
            'cod_usuario_registro' => $usuario->cod_usuario,
            'estado' => 'PENDIENTE',
        ]);
        $this->assertDatabaseHas('contactos', ['numero_documento' => 'FAM-778899']);
        $this->assertDatabaseCount('documentos', 7);
    }

    public function test_pestanas_clinicas_originales_cargan_un_residente_v2(): void
    {
        $usuario = $this->superadministrador();
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_TABS_V2',
            'nombres' => 'Maria',
            'apellido_paterno' => 'Choque',
            'fecha_nacimiento' => '1941-08-12',
            'estado' => 'ADMITIDO',
        ]);
        $this->actingAs($usuario);
        $this->withoutExceptionHandling();

        $rutas = [
            route('admin.salud-seguimiento.resumen', ['adulto' => $residente->cod_residente]),
            route('admin.salud-seguimiento.ficha', ['adulto' => $residente->cod_residente]),
            route('admin.salud-seguimiento.signos', ['adulto' => $residente->cod_residente]),
            route('admin.salud-seguimiento.medicacion', ['adulto' => $residente->cod_residente]),
            route('admin.salud-seguimiento.valoracion', ['adulto' => $residente->cod_residente]),
            route('admin.salud-seguimiento.evaluaciones-geriatricas', ['adulto' => $residente->cod_residente]),
            route('admin.salud-seguimiento.administracion', ['adulto' => $residente->cod_residente]),
            route('admin.enfermeria.pacientes.ficha', ['adulto' => $residente->cod_residente]),
        ];
        $errores = [];

        foreach ($rutas as $ruta) {
            try {
                $respuesta = $this->get($ruta);
                if ($respuesta->getStatusCode() >= 500) {
                    $errores[$ruta] = $respuesta->getStatusCode();
                }
            } catch (\Throwable $excepcion) {
                $errores[$ruta] = $excepcion->getMessage();
            }
        }

        $this->assertSame([], $errores, 'Hay pestañas clínicas restauradas que aún consultan la BDD legacy.');
    }

    public function test_modal_original_guarda_aplicacion_de_instrumento_v2(): void
    {
        $usuario = $this->superadministrador();
        Personal::create([
            'cod_personal' => 'PER_EVAL_V2',
            'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'Elena',
            'apellido_paterno' => 'Rojas',
            'numero_documento' => 'CI-EVAL-V2',
            'profesion' => 'PSICOLOGA',
            'estado' => 'ACTIVO',
        ]);
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_EVAL_V2',
            'nombres' => 'Juana',
            'apellido_paterno' => 'Flores',
            'fecha_nacimiento' => '1944-02-01',
            'estado' => 'ADMITIDO',
        ]);
        Instrumento::create([
            'cod_instrumento' => 'INS_EVAL_V2',
            'codigo' => 'MMSE-V2',
            'nombre' => 'Mini examen cognitivo',
            'tipo' => 'CUANTITATIVO',
            'puntaje_maximo' => 30,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($usuario);
        Livewire::test(EvaluacionGeriatricaModal::class)
            ->call('abrir', $residente->cod_residente)
            ->set('cod_area', 'INSTRUMENTOS_V2')
            ->set('cod_instrumento', 'INS_EVAL_V2')
            ->set('puntaje_total', 24)
            ->set('categoria_resultado', 'Seguimiento preventivo')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('aplicaciones_instrumento', [
            'cod_residente' => $residente->cod_residente,
            'cod_instrumento' => 'INS_EVAL_V2',
            'cod_personal' => 'PER_EVAL_V2',
            'puntaje_total' => 24,
        ]);
    }

    public function test_ficha_original_guarda_documento_administrativo_v2(): void
    {
        Storage::fake('local');
        $usuario = $this->superadministrador();
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_DOC_V2',
            'nombres' => 'Rita',
            'apellido_paterno' => 'Mendoza',
            'fecha_nacimiento' => '1940-06-15',
            'estado' => 'ADMITIDO',
        ]);
        $this->actingAs($usuario);

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->set('nuevoDocNombre', 'Documento de identidad')
            ->set('nuevoDocTipo', 'IDENTIDAD')
            ->set('nuevoDocCategoria', 'PERSONAL')
            ->set('nuevoDocFecha', today()->toDateString())
            ->set('nuevoDocArchivo', UploadedFile::fake()->create('identidad.pdf', 10, 'application/pdf'))
            ->call('guardarNuevoDocumento')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documentos', [
            'cod_residente' => $residente->cod_residente,
            'cod_usuario' => $usuario->cod_usuario,
            'nombre' => 'Documento de identidad',
            'estado' => 'ACTIVO',
        ]);
    }

    private function superadministrador(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $rol = Role::findOrCreate('SUPERADMINISTRADOR', 'web');
        $usuario = User::factory()->create();
        $usuario->assignRole($rol);

        return $usuario;
    }
}
