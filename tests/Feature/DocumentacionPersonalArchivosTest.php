<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\DocumentacionPersonalPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentacionPersonalArchivosTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;
    private User $titular;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
        $this->gerente = $this->persona('GERENTE', 'Dirección', 'Prueba');
        $this->titular = $this->persona('ENFERMEROS', 'Lucía', 'Referencia');
        $this->actingAs($this->gerente);
    }

    private function persona(string $rol, string $nombre, string $apellido): User
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole($rol);
        Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => $nombre, 'apellido_paterno' => $apellido,
            'numero_documento' => 'PRUEBA-'.bin2hex(random_bytes(5)), 'profesion' => $rol, 'estado' => 'ACTIVO']);

        return $u;
    }

    private function documento(User $usuario, string $nombre = 'Certificado de ejemplo', string $estado = 'CARGADO', string $extension = 'pdf'): Documento
    {
        $ruta = 'documentos_personal_privados/'.bin2hex(random_bytes(8)).'.'.$extension;
        Storage::disk('local')->put($ruta, $extension === 'pdf' ? "%PDF-1.4\n%%EOF\n" : base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII='));

        return Documento::create(['cod_usuario' => $usuario->getKey(), 'nombre' => $nombre, 'tipo_documento' => 'OTROS', 'tipo_archivo' => $extension === 'pdf' ? 'application/pdf' : 'application/octet-stream', 'hash_archivo' => hash('sha256', Storage::disk('local')->get($ruta)), 'estado' => $estado, 'ruta_archivo' => $ruta]);
    }

    public function test_archivos_de_alta_se_muestran_sin_solicitud_ni_ampliacion_y_no_inventan_fechas(): void
    {
        $documento = $this->documento($this->titular);
        Livewire::test(DocumentacionPersonalPanel::class)
            ->assertSet('vista', 'archivos')->assertSee('Archivos del expediente')
            ->assertDontSeeHtml('wire:click="nuevaSolicitud"')
            ->assertSee('Certificado de ejemplo')->assertSee('Lucía Referencia')
            ->assertSee('Sin fecha conservada')->assertDontSee('Sin solicitudes en esta consulta')
            ->call('verArchivo', $documento->getKey())->assertSet('vistaPreviaAbierta', true)
            ->assertSee('Vista previa PDF')->assertSee('Ficha del documento')
            ->assertDontSee('documentos_personal_privados/')
            ->call('cerrarVistaPrevia')->assertSet('documentoSeleccionado', null);
        $this->assertDatabaseCount('documentos', 1);
        $this->assertSame('CARGADO', $documento->fresh()->estado);
    }

    public function test_busca_persona_con_varias_palabras_filtra_estado_y_conserva_historia(): void
    {
        $this->documento($this->titular, 'Título de prueba', 'VALIDADO');
        $this->documento($this->titular, 'Título anterior', 'REEMPLAZADO');
        $otro = $this->persona('PSICOLOGO/A', 'María', 'Referencia');
        $this->documento($otro, 'Archivo ajeno al filtro');
        Livewire::test(DocumentacionPersonalPanel::class)->set('busqueda', 'Lucía Referencia')
            ->assertSee('Título de prueba')->assertSee('Título anterior')->assertDontSee('Archivo ajeno al filtro')
            ->set('estadoArchivo', 'VALIDADO')->assertSee('Título de prueba')->assertDontSee('Título anterior')
            ->call('limpiarFiltros')->assertSet('estadoArchivo', '')->assertSet('busqueda', '')
            ->assertSee('Título anterior')->assertSee('Reemplazado');
        $this->assertDatabaseCount('documentos', 3);
    }

    public function test_desplegables_combinan_persona_rol_formato_y_estado_sin_ampliar_acceso(): void
    {
        $this->documento($this->titular, 'Constancia PDF', 'VALIDADO');
        $this->documento($this->titular, 'Imagen de respaldo', 'CARGADO', 'png');
        $otro = $this->persona('PSICOLOGO/A', 'Elena', 'Prueba');
        $this->documento($otro, 'Informe de psicología', 'VALIDADO');
        $oculto = $this->persona('SUPERADMINISTRADOR', 'Persona reservada', 'Prueba');
        $this->documento($oculto, 'Archivo reservado');

        Livewire::test(DocumentacionPersonalPanel::class)
            ->assertSee('Trabajador')->assertSee('Formato del archivo')->assertDontSee('Persona reservada')
            ->set('personaArchivo', $this->titular->getKey())->assertSee('Constancia PDF')->assertDontSee('Informe de psicología')
            ->set('formatoArchivo', 'IMAGEN')->assertSee('Imagen de respaldo')->assertDontSee('Constancia PDF')
            ->set('estadoArchivo', 'VALIDADO')->assertSee('No hay archivos con estos filtros')
            ->call('limpiarFiltros')->assertSet('personaArchivo', '')->assertSet('formatoArchivo', '')
            ->set('personaArchivo', $this->titular->getKey())->set('rolArchivo', 'PSICOLOGO/A')
            ->assertSet('personaArchivo', '')->assertSee('Informe de psicología')->assertDontSee('Constancia PDF')
            ->set('personaArchivo', $oculto->getKey())->assertSee('No hay archivos con estos filtros')->assertDontSee('Archivo reservado');
    }

    public function test_formato_otros_y_filtros_manipulados_no_exponen_documentos_ajenos(): void
    {
        $this->documento($this->titular, 'Archivo de texto', 'CARGADO', 'docx');
        $this->documento($this->titular, 'Respaldo PDF');
        Livewire::test(DocumentacionPersonalPanel::class)->set('formatoArchivo', 'OTROS')
            ->assertSee('Archivo de texto')->assertDontSee('Respaldo PDF');
        $this->actingAs($this->titular);
        Livewire::test(DocumentacionPersonalPanel::class)->set('personaArchivo', $this->gerente->getKey())
            ->assertSee('No hay archivos con estos filtros')->assertDontSee('Archivo de texto');
        Livewire::test(DocumentacionPersonalPanel::class)->set('formatoArchivo', 'SQL')->assertStatus(422);
    }

    public function test_trabajador_solo_consulta_archivos_propios_y_no_un_recurso_manipulado(): void
    {
        $propio = $this->documento($this->titular, 'Archivo propio');
        $ajeno = $this->documento($this->persona('ENFERMEROS', 'Otra', 'Persona'), 'Archivo reservado');
        $this->actingAs($this->titular);
        Livewire::test(DocumentacionPersonalPanel::class)->assertSee('Archivo propio')->assertDontSee('Archivo reservado')
            ->call('verArchivo', $propio->getKey())->assertSet('vistaPreviaAbierta', true)
            ->call('verArchivo', $ajeno->getKey())->assertNotFound();
        $this->get(route('admin.personal.documento.archivo', $ajeno))->assertForbidden();
        $this->assertDatabaseCount('documentos', 2);
    }

    public function test_gerencia_no_lista_cuentas_fuera_del_alcance_y_superadmin_si_puede_consultar(): void
    {
        $supervisor = $this->persona('SUPERADMINISTRADOR', 'Supervisor', 'Referencia');
        $protegido = $this->documento($supervisor, 'Archivo de supervisión');
        $mixto = $this->persona('ENFERMEROS', 'Mixto', 'Referencia');
        $mixto->assignRole('GERENTE');
        $this->documento($mixto, 'Archivo de cuenta mixta');
        $this->documento($this->titular, 'Archivo del equipo');
        Livewire::test(DocumentacionPersonalPanel::class)->assertSee('Archivo del equipo')
            ->assertDontSee('Archivo de supervisión')->assertDontSee('Archivo de cuenta mixta')
            ->call('verArchivo', $protegido->getKey())->assertNotFound();
        $this->actingAs($supervisor);
        Livewire::test(DocumentacionPersonalPanel::class)->assertSee('Archivo de cuenta mixta')->assertSee('Archivo del equipo');
    }

    public function test_familiar_y_cuenta_revocada_no_consultan_y_no_hay_escrituras(): void
    {
        $documento = $this->documento($this->titular);
        $pantalla = Livewire::test(DocumentacionPersonalPanel::class)->call('verArchivo', $documento->getKey());
        $this->gerente->update(['estado' => 'INACTIVO']);
        $pantalla->call('$refresh')->assertForbidden();
        $familiar = User::factory()->create(['estado' => 'ACTIVO']);
        $familiar->assignRole('FAMILIAR');
        $this->actingAs($familiar);
        Livewire::test(DocumentacionPersonalPanel::class)->assertForbidden();
        $this->get(route('admin.personal.documento.archivo', $documento))->assertForbidden();
        $this->assertDatabaseCount('documentos', 1);
    }

    public function test_archivo_ausente_y_formato_sin_preview_se_distinguen(): void
    {
        $documento = $this->documento($this->titular);
        Storage::disk('local')->delete($documento->ruta_archivo);
        Livewire::test(DocumentacionPersonalPanel::class)->call('verArchivo', $documento->getKey())
            ->assertSee('No se encontró el archivo original')->assertDontSee('<iframe', false);
        $word = $this->documento($this->titular, 'Documento de texto', 'CARGADO', 'docx');
        Livewire::test(DocumentacionPersonalPanel::class)->call('verArchivo', $word->getKey())
            ->assertSee('Vista previa no disponible para este formato')->assertSee('Abrir original');
    }

    public function test_la_consulta_y_la_policy_coinciden_sin_consultas_por_documento(): void
    {
        foreach (range(1, 15) as $numero) $this->documento($this->titular, 'Archivo '.$numero);
        $documentos = app(ConsultarDocumentacionPersonal::class)->archivos($this->gerente)->get();
        $policy = app(\App\Modulos\Identidad\Presentacion\Policies\DocumentoLaboralPolicy::class);
        $permisos = $policy->permisosDocumentos($this->gerente, $documentos);
        $this->assertCount(15, $permisos);
        foreach ($documentos as $documento) {
            $this->assertTrue($permisos[$documento->getKey()]['ver']);
            $this->assertTrue($documento->relationLoaded('usuario'));
            $this->assertTrue($documento->usuario->relationLoaded('personal'));
        }
    }
    public function test_carpeta_agrupa_todos_los_documentos_de_la_persona_y_pagina_el_expediente(): void
    {
        foreach (range(1,13) as $n) $this->documento($this->titular, 'Archivo '.$n);
        $otro = $this->persona('PSICOLOGO/A','Otra','Persona'); $ajeno = $this->documento($otro,'Documento de otra persona');
        $panel = Livewire::test(DocumentacionPersonalPanel::class)
            ->assertViewHas('carpetasUsuarios',fn ($p)=>$p->total()===2 && (int)$p->firstWhere('cod_usuario',$this->titular->getKey())->total_documentos===13)
            ->call('abrirCarpetaUsuario',$this->titular->getKey())->assertSet('carpetaUsuario',$this->titular->getKey())
            ->assertViewHas('archivos',fn ($p)=>$p->total()===13 && $p->count()===12 && $p->every(fn ($d)=>$d->cod_usuario===$this->titular->getKey()))
            ->assertSee('Volver a carpetas')->assertDontSee('Documento de otra persona')
            ->call('gotoPage',2,'archivosPage')->assertViewHas('archivos',fn ($p)=>$p->count()===1)
            ->call('cerrarCarpetaUsuario')->assertSet('carpetaUsuario',null)->assertDispatched('personal-carpeta-cerrada');
        $this->assertDatabaseCount('documentos',14);
        Livewire::test(DocumentacionPersonalPanel::class)->call('abrirCarpetaUsuario',$this->titular->getKey())
            ->call('verArchivo',$ajeno->getKey())->assertNotFound();
    }

    public function test_carpeta_de_otro_trabajador_y_permiso_revocado_no_se_pueden_consultar(): void
    {
        $this->documento($this->titular); $otro = $this->persona('ENFERMEROS','Otro','Trabajador'); $this->documento($otro);
        $this->actingAs($this->titular);
        Livewire::test(DocumentacionPersonalPanel::class)->assertViewHas('carpetasUsuarios',fn ($p)=>$p->total()===1)
            ->call('abrirCarpetaUsuario',$otro->getKey())->assertNotFound();
        $this->actingAs($this->gerente);
        $panel = Livewire::test(DocumentacionPersonalPanel::class)->call('abrirCarpetaUsuario',$this->titular->getKey());
        $this->gerente->forceFill(['estado'=>'SUSPENDIDO'])->save();
        $panel->call('abrirCarpetaUsuario',$otro->getKey())->assertForbidden();
        $this->assertDatabaseCount('documentos',2);
    }

    public function test_filtro_de_documentos_se_aplica_al_conteo_y_contenido_de_las_carpetas(): void
    {
        $this->documento($this->titular,'Certificado aprobado','VALIDADO'); $this->documento($this->titular,'Documento cargado');
        $otro = $this->persona('ENFERMEROS','Otro','Trabajador'); $this->documento($otro);
        Livewire::test(DocumentacionPersonalPanel::class)->set('estadoArchivo','VALIDADO')
            ->assertViewHas('carpetasUsuarios',fn ($p)=>$p->total()===1 && (int)$p->first()->total_documentos===1)
            ->call('abrirCarpetaUsuario',$this->titular->getKey())->assertViewHas('archivos',fn ($p)=>$p->total()===1)
            ->assertSee('Certificado aprobado')->assertDontSee('Documento cargado');
    }

    private function instalarSeguimiento(): void
    {
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        $this->titular->forceFill(['fecha_hora_alta' => now()])->save();
    }

    private function solicitud(User $titular, string $nombre): \App\Models\DestinatarioSolicitudDocumental
    {
        return app(\App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales::class)->emitir($this->gerente, [
            'rol' => $titular->roles->sole()->name, 'tipo_documento' => 'OTROS', 'nombre_documento' => $nombre,
            'motivo' => 'Seguimiento sintético de carpeta', 'personas' => [$titular->personal->getKey()],
            'origen_plazo' => 'ALTA', 'clave_solicitud' => (string) \Illuminate\Support\Str::uuid(),
        ])->destinatarios->sole();
    }

    public function test_carpeta_sin_archivos_muestra_pendiente_plazo_y_seguimiento_sin_cambiar_de_vista(): void
    {
        $this->instalarSeguimiento();
        $destino = $this->solicitud($this->titular, 'Certificado solicitado');
        Livewire::test(DocumentacionPersonalPanel::class)
            ->assertViewHas('carpetasUsuarios', fn ($p) => $p->total() === 1 && (int) $p->first()->total_documentos === 0)
            ->call('abrirCarpetaUsuario', $this->titular->getKey())
            ->assertSee('Pendientes de entrega')->assertSee('Certificado solicitado')->assertSee('Pendiente de entrega')
            ->assertSee(today()->addDays(7)->format('d/m/Y'))
            ->set('estadoArchivo', 'VALIDADO')->assertSee('Certificado solicitado')
            ->call('verPendienteCarpeta', $destino->getKey())->assertSet('vista', 'archivos')->assertSet('detalleAbierto', true)
            ->call('cerrarDetalle')->assertSet('carpetaUsuario', $this->titular->getKey());
        $this->assertDatabaseCount('documentos', 0);
        $this->assertDatabaseCount('entregas_solicitud_documental', 0);
    }

    public function test_pendientes_usan_plazo_prorrogado_y_no_confunden_entrega_con_revision(): void
    {
        $this->instalarSeguimiento();
        $pendiente = $this->solicitud($this->titular, 'Respaldo sin entregar');
        app(\App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales::class)
            ->prorrogar($this->gerente, $pendiente->getKey(), 'Ampliación sintética del expediente');
        foreach (['CARGADO', 'PENDIENTE', 'VALIDADO', 'OBSERVADO', 'ANULADO', 'REEMPLAZADO'] as $estado) {
            $destino = $this->solicitud($this->titular, 'Solicitud '.$estado);
            $documento = $this->documento($this->titular, 'Archivo '.$estado, $estado);
            \App\Models\EntregaSolicitudDocumental::create(['cod_destinatario_documental' => $destino->getKey(),
                'cod_documento' => $documento->getKey(), 'cod_usuario_registro' => $this->gerente->getKey(), 'fecha_hora_registro' => now()]);
        }
        Livewire::test(DocumentacionPersonalPanel::class)->call('abrirCarpetaUsuario', $this->titular->getKey())
            ->assertViewHas('pendientesCarpeta', fn ($p) => $p->total() === 4 && $p->every(fn ($f) => $f['estado']['limite']->toDateString() === today()->addDays(14)->toDateString()))
            ->assertSee('Plazo prorrogado')->assertSee('Pendiente de corrección')
            ->assertDontSee('Solicitud CARGADO')->assertDontSee('Solicitud PENDIENTE')->assertDontSee('Solicitud VALIDADO');
        $this->assertNull($destino->fresh()->fecha_limite_prorrogada);
    }

    public function test_carpeta_pendiente_no_expone_otro_titular_ni_cuentas_fuera_de_alcance(): void
    {
        $this->instalarSeguimiento();
        $propio = $this->solicitud($this->titular, 'Solicitud propia');
        $otro = $this->persona('ENFERMEROS', 'Otra', 'Persona');
        $otro->forceFill(['fecha_hora_alta' => now()])->save();
        $ajeno = $this->solicitud($otro, 'Solicitud ajena');
        $otro->assignRole('GERENTE');
        Livewire::test(DocumentacionPersonalPanel::class)->assertViewHas('carpetasUsuarios', fn ($p) => $p->total() === 1)
            ->call('abrirCarpetaUsuario', $otro->getKey())->assertNotFound();
        $this->actingAs($this->titular);
        Livewire::test(DocumentacionPersonalPanel::class)->call('abrirCarpetaUsuario', $this->titular->getKey())
            ->assertSee('Solicitud propia')->assertDontSee('Solicitud ajena')
            ->call('verPendienteCarpeta', $ajeno->getKey())->assertNotFound();
        Livewire::test(DocumentacionPersonalPanel::class)->call('abrirCarpetaUsuario', $otro->getKey())->assertNotFound();
        $this->assertDatabaseCount('destinatarios_solicitud_documental', 2);
    }

}
