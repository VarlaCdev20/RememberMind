<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ContratoDocumentacionUsuarioTest extends TestCase
{
    use RefreshDatabase;

    private function actor(string $permiso, string $estado = 'ACTIVO'): User
    {
        Permission::findOrCreate($permiso, 'web');
        $actor = User::factory()->create(['estado' => $estado]);
        $actor->givePermissionTo($permiso);

        return $actor;
    }

    private function documento(User $propietario, string $estado = 'CARGADO'): Documento
    {
        return Documento::create(['cod_documento' => 'DOC_CONTRATO', 'cod_usuario' => $propietario->getKey(),
            'tipo_documento' => 'CI', 'nombre' => 'Documento sintético', 'ruta_archivo' => 'synthetic/no-file.pdf',
            'tipo_archivo' => 'application/pdf', 'hash_archivo' => hash('sha256', 'synthetic'), 'estado' => $estado]);
    }

    public function test_actor_sin_permiso_no_valida_ni_crea_auditoria(): void
    {
        $actor = User::factory()->create();
        $documento = $this->documento(User::factory()->create());
        $antes = Activity::count();
        try {
            app(DocumentacionUsuarioService::class)->validarDocumento($documento, $actor);
            $this->fail('Debe rechazarse la validación sin permiso.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame('CARGADO', $documento->fresh()->estado);
        $this->assertNull($documento->fresh()->cod_usuario_validacion);
        $this->assertSame($antes, Activity::count());
    }

    public function test_cuenta_inactiva_con_permiso_no_valida(): void
    {
        $actor = $this->actor('documentos.gestionar', 'INACTIVO');
        $documento = $this->documento(User::factory()->create());
        try {
            app(DocumentacionUsuarioService::class)->validarDocumento($documento, $actor);
            $this->fail('Debe rechazarse la cuenta inactiva.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame('CARGADO', $documento->fresh()->estado);
    }

    public function test_validacion_autorizada_conserva_actor_fecha_y_auditoria(): void
    {
        $actor = $this->actor('documentos.gestionar');
        $propietario = User::factory()->create();
        $documento = $this->documento($propietario);
        app(DocumentacionUsuarioService::class)->validarDocumento($documento, $actor, $propietario->getKey());
        $this->assertSame('VALIDADO', $documento->fresh()->estado);
        $this->assertSame($actor->getKey(), $documento->fresh()->cod_usuario_validacion);
        $this->assertNotNull($documento->fresh()->fecha_validacion);
        $this->assertDatabaseHas('activity_log', ['causer_id' => $actor->getKey(), 'subject_id' => $propietario->getKey(), 'event' => 'documentacion']);
    }

    public function test_propietario_cruzado_no_modifica_documento(): void
    {
        $actor = $this->actor('documentos.gestionar');
        $documento = $this->documento(User::factory()->create());
        try {
            app(DocumentacionUsuarioService::class)->validarDocumento($documento, $actor, User::factory()->create()->getKey());
            $this->fail('Debe rechazarse el propietario cruzado.');
        } catch (HttpException $error) {
            $this->assertSame(404, $error->getStatusCode());
        }
        $this->assertSame('CARGADO', $documento->fresh()->estado);
    }

    public function test_documento_anulado_no_se_revalida(): void
    {
        $actor = $this->actor('documentos.gestionar');
        $documento = $this->documento(User::factory()->create(), 'ANULADO');
        try {
            app(DocumentacionUsuarioService::class)->validarDocumento($documento, $actor);
            $this->fail('No debe revivirse un documento anulado.');
        } catch (HttpException $error) {
            $this->assertSame(422, $error->getStatusCode());
        }
        $this->assertSame('ANULADO', $documento->fresh()->estado);
    }

    public function test_subida_sin_permiso_no_crea_archivo_ni_registro(): void
    {
        Storage::fake('public');
        $actor = User::factory()->create();
        try {
            app(DocumentacionUsuarioService::class)->subirDocumento($actor, 'CI', UploadedFile::fake()->create('identidad.pdf', 10, 'application/pdf'), subidoPor: $actor);
            $this->fail('Debe rechazarse la subida sin permiso.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_reemplazo_preserva_archivo_anterior_y_sucesion(): void
    {
        Storage::fake('public');
        $actor = $this->actor('documentos.subir');
        $service = app(DocumentacionUsuarioService::class);
        $anterior = $service->subirDocumento($actor, 'CI', UploadedFile::fake()->create('identidad.pdf', 10, 'application/pdf'), subidoPor: $actor);
        Permission::findOrCreate('documentos.gestionar', 'web');
        $actor->givePermissionTo('documentos.gestionar');
        $service->validarDocumento($anterior, $actor);
        $nuevo = $service->subirDocumento($actor, 'CI', UploadedFile::fake()->create('identidad.pdf', 10, 'application/pdf'), subidoPor: $actor);
        $this->assertSame('REEMPLAZADO', $anterior->fresh()->estado);
        $this->assertSame($anterior->getKey(), $nuevo->cod_documento_anterior);
        $this->assertNotSame($anterior->ruta_archivo, $nuevo->ruta_archivo);
        $checklist = collect($service->obtenerChecklistUsuario($actor))->firstWhere('cod_tipo_doc', 'CI');
        $this->assertSame($nuevo->getKey(), $checklist['documento']->getKey());
        $this->assertSame(0, $service->calcularAvanceDocumental($actor)['validados']);
        Storage::disk('public')->assertExists([$anterior->ruta_archivo, $nuevo->ruta_archivo]);
    }

    public function test_fallo_de_auditoria_revierte_sql_y_compensa_solo_archivo_nuevo(): void
    {
        Storage::fake('public');
        $actor = $this->actor('documentos.subir');
        $service = app(DocumentacionUsuarioService::class);
        $anterior = $service->subirDocumento($actor, 'CI', UploadedFile::fake()->create('identidad.pdf', 10, 'application/pdf'), subidoPor: $actor);
        $auditoriaAntes = Activity::count();
        $dispatcher = Activity::getEventDispatcher();
        Activity::setEventDispatcher(clone $dispatcher);
        try {
            Activity::creating(fn () => throw new RuntimeException('Fallo sintético de auditoría.'));
            try {
                $service->subirDocumento($actor, 'CI', UploadedFile::fake()->create('identidad.pdf', 10, 'application/pdf'), subidoPor: $actor);
                $this->fail('La operación debe informar el fallo.');
            } catch (RuntimeException $error) {
                $this->assertSame('Fallo sintético de auditoría.', $error->getMessage());
            }
        } finally {
            Activity::setEventDispatcher($dispatcher);
        }
        $this->assertDatabaseCount('documentos', 1);
        $this->assertSame('CARGADO', $anterior->fresh()->estado);
        $this->assertSame($auditoriaAntes, Activity::count());
        $this->assertSame([$anterior->ruta_archivo], Storage::disk('public')->allFiles());
    }
}
