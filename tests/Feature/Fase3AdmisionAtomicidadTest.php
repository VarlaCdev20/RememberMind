<?php

namespace Tests\Feature;

use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use App\Models\Admision;
use App\Models\Cama;
use App\Models\Consentimiento;
use App\Models\Contacto;
use App\Models\Documento;
use App\Models\Habitacion;
use App\Models\HistorialEstadoResidente;
use App\Models\OcupacionCama;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\SeguroResidente;
use App\Models\SignoVital;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class Fase3AdmisionAtomicidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_consentimiento_no_acepta_contacto_relacional_de_otro_residente(): void
    {
        $usuario = User::factory()->create();
        $propio = Residente::factory()->create();
        $ajeno = Residente::factory()->create();
        $contacto = Contacto::create(['cod_contacto' => 'CTO_F3_CON', 'nombres' => 'Contacto sintético', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        $vinculo = ResidenteContacto::create(['cod_residente_contacto' => 'RCO_F3_CON', 'cod_residente' => $ajeno->cod_residente,
            'cod_contacto' => $contacto->cod_contacto, 'parentesco' => 'RESPONSABLE', 'responsable_principal' => true,
            'contacto_emergencia' => true, 'autoriza_informacion' => true, 'autoriza_salida' => false, 'estado' => 'ACTIVO']);
        try {
            Consentimiento::create(['cod_consentimiento' => 'CON_F3_AJENO', 'cod_residente' => $propio->cod_residente,
                'cod_residente_contacto' => $vinculo->cod_residente_contacto, 'cod_usuario_registro' => $usuario->cod_usuario,
                'tipo_consentimiento' => 'ADMISION', 'firma_residente' => false, 'fecha_consentimiento' => now(), 'estado' => 'VIGENTE']);
            $this->fail('El firmante relacional debe corresponder al residente.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cod_residente_contacto', $e->errors());
        }
        $this->assertDatabaseCount('consentimientos', 0);
        $this->assertDatabaseCount('residentes_contactos', 1);
    }

    public function test_expediente_solo_vincula_documentos_del_mismo_residente(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        $this->actingAs($usuario);
        Storage::fake('local');

        $residente = Residente::factory()->create();
        $otro = Residente::factory()->create();
        Admision::create([
            'cod_residente' => $residente->getKey(), 'cod_usuario_registro' => $usuario->getKey(),
            'fecha_hora_admision' => now(), 'motivo_ingreso' => 'Ingreso sintético', 'estado' => 'ACTIVA',
        ]);
        $datosDocumento = [
            'nombre' => 'Documento sintético', 'tipo_documento' => 'ADMINISTRATIVO',
            'ruta_archivo' => 'documentos/sintetico.pdf', 'tipo_archivo' => 'application/pdf',
            'hash_archivo' => str_repeat('a', 64), 'estado' => 'PENDIENTE',
        ];
        $propio = Documento::create($datosDocumento + ['cod_residente' => $residente->getKey()]);
        $ajeno = Documento::create($datosDocumento + ['cod_residente' => $otro->getKey()]);

        $consentimientos = route('admin.residentes.consentimientos.store', $residente);
        $firma = ['tipo_consentimiento' => 'ADMISION', 'firma_residente' => true];
        $this->postJson($consentimientos, $firma + ['cod_documento' => $ajeno->getKey()])
            ->assertUnprocessable()->assertJsonValidationErrors('cod_documento');
        $this->assertDatabaseCount('consentimientos', 0);
        $this->postJson($consentimientos, $firma + ['cod_documento' => $propio->getKey()])
            ->assertCreated()->assertJsonPath('cod_documento', $propio->getKey());

        $documentos = route('admin.documentos.store', $residente);
        $carga = ['nombre' => 'Nueva versión', 'tipo_documento' => 'ADMINISTRATIVO'];
        $this->postJson($documentos, $carga + ['archivo' => UploadedFile::fake()->create('ajeno.pdf'),
            'cod_documento_anterior' => $ajeno->getKey()])
            ->assertUnprocessable()->assertJsonValidationErrors('cod_documento_anterior');
        $this->assertDatabaseCount('documentos', 2);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $nuevo = $this->postJson($documentos, $carga + ['archivo' => UploadedFile::fake()->create('propio.pdf'),
            'cod_documento_anterior' => $propio->getKey()])
            ->assertCreated()->assertJsonPath('cod_documento_anterior', $propio->getKey());
        Storage::disk('local')->assertExists($nuevo->json('ruta_archivo'));
        $this->assertSame('PENDIENTE', $propio->fresh()->estado);
    }

    public function test_borrado_ordinario_de_signo_preserva_historia(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Autor sintético']);
        $residente = Residente::factory()->create();
        $signo = SignoVital::create(['cod_residente' => $residente->cod_residente,
            'cod_personal' => $usuario->personal->cod_personal, 'fecha_hora' => now(), 'temperatura' => 36.8, 'estado' => 'ACTIVO']);
        $original = $signo->fresh()->getAttributes();
        try {
            $signo->delete();
            $this->fail('El borrado físico ordinario debe rechazarse.');
        } catch (\LogicException $e) {
            $this->assertStringContainsString('borrado físico', $e->getMessage());
        }
        $this->assertSame($original, $signo->fresh()->getAttributes());
        $this->assertDatabaseCount('signos_vitales', 1);
    }

    public static function puntosDeFallo(): array
    {
        return array_map(fn ($modelo) => [$modelo], [Contacto::class, Residente::class, Admision::class,
            ResidenteContacto::class, OcupacionCama::class, HistorialEstadoResidente::class,
            Consentimiento::class, SeguroResidente::class, Activity::class]);
    }

    #[DataProvider('puntosDeFallo')]
    public function test_fallo_intermedio_revierte_toda_la_admision(string $modelo): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create();
        $usuario->assignRole('ADMINISTRADOR');
        $habitacion = Habitacion::create(['cod_habitacion' => 'HAB_F3', 'codigo' => 'Sintética', 'capacidad' => 1, 'estado' => 'ACTIVA']);
        $cama = Cama::create(['cod_cama' => 'CAM_F3', 'cod_habitacion' => $habitacion->cod_habitacion,
            'codigo' => 'Sintética', 'estado' => 'ACTIVA']);
        $solicitud = Preadmision::create(['cod_preadmision' => 'PRE_F3', 'cod_usuario_registro' => $usuario->cod_usuario,
            'nombres' => 'Residente sintético', 'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01',
            'motivo_ingreso' => 'Prueba de atomicidad', 'fecha_solicitud' => now(), 'estado' => 'APROBADA']);
        $tablas = ['contactos', 'residentes', 'admisiones', 'residentes_contactos', 'ocupaciones_cama',
            'historial_estados_residente', 'consentimientos', 'seguros_residente', 'activity_log'];
        $antes = array_combine($tablas, array_map(fn ($tabla) => DB::table($tabla)->count(), $tablas));
        $modelo::creating(fn () => throw new \RuntimeException('Fallo sintético de atomicidad'));
        try {
            app(FormalizarAdmision::class)->ejecutar($solicitud, [
                'cod_cama' => $cama->cod_cama, 'contacto' => ['nombres' => 'Contacto sintético', 'apellido_paterno' => 'Prueba'],
                'seguro_entidad' => 'Entidad sintética',
            ], $usuario);
            $this->fail('La admisión debe fallar en el punto inyectado.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo sintético de atomicidad', $e->getMessage());
        }
        foreach ($antes as $tabla => $cantidad) {
            $this->assertDatabaseCount($tabla, $cantidad);
        }
        $this->assertSame('APROBADA', $solicitud->fresh()->estado);
        $this->assertSame('ACTIVA', $cama->fresh()->estado);
    }
}
