<?php

namespace Tests\Feature;

use App\Mail\PersonalInstitucionalBienvenidaMail;
use App\Models\DestinatarioSolicitudDocumental;
use App\Models\SolicitudDocumentalPersonal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GuardarPersonalInstitucional;
use App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AltaPersonalDocumentalTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-11-05 10:30:00'));
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        (require base_path('database/propuestas/gerencia/2026_11_04_000004_pais_documento_telefono_personal.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public'); Storage::fake('local'); Mail::fake();
        $this->gerente = User::factory()->create(); $this->gerente->assignRole('GERENTE'); $this->actingAs($this->gerente);
    }

    private function entrada(array $cambios = []): array
    {
        return $cambios + ['esEdicion'=>false, 'roles_seleccionados'=>['ADMINISTRADOR'], 'nombres'=>'Persona', 'ap_paterno'=>'Sintética',
            'pais_documento'=>'BO', 'tipo_documento'=>'CI', 'pais_telefono'=>'BO', 'numero_documento'=>'87654321', 'expedido'=>'LP',
            'fecha_nacimiento'=>'1990-01-01', 'genero'=>'F', 'telefono'=>'77712345', 'direccion'=>'DIRECCIÓN SINTÉTICA',
            'correo'=>'alta-documental@remembermind.test', 'contrasena_temporal'=>'TemporalSintetica#123',
            'archivos_temporales'=>['CI'=>UploadedFile::fake()->create('identidad.pdf',10,'application/pdf'),
                'FOTO_DOC'=>UploadedFile::fake()->image('foto.jpg'), 'CV'=>UploadedFile::fake()->create('cv.pdf',10,'application/pdf')]];
    }

    public function test_alta_con_plazo_elegido_correo_al_destinatario_y_cambio_inmediato_de_password(): void
    {
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($this->gerente, $this->entrada([
            'documentos_con_plazo'=>['DOMICILIO'], 'fecha_limite_documentacion'=>'2026-11-20']));
        $usuario = $resultado['usuario'];
        $this->assertTrue($resultado['correo_enviado']);
        $this->assertSame('2026-11-05', $usuario->personal->fecha_ingreso->toDateString());
        $this->assertSame('2026-11-05 10:30:00', $usuario->fecha_hora_alta->format('Y-m-d H:i:s'));
        $this->assertTrue($usuario->cambio_password_pendiente);
        $destino = DestinatarioSolicitudDocumental::sole();
        $this->assertSame($usuario->personal->getKey(), $destino->cod_personal);
        $this->assertSame('2026-11-20', $destino->fecha_limite_original->toDateString());
        $this->assertSame('DOMICILIO', $destino->solicitud->tipo_documento);
        Mail::assertSent(PersonalInstitucionalBienvenidaMail::class, fn ($mail)=>$mail->hasTo($usuario->correo)
            && $mail->plainPassword==='TemporalSintetica#123' && $mail->fecha_limite==='20/11/2026' && count($mail->documentos_pendientes)===1);
        $mail = Mail::sent(PersonalInstitucionalBienvenidaMail::class)->sole();
        $this->assertStringContainsString('CREAR MI CONTRASEÑA AHORA', $mail->render());
        $this->assertStringContainsString('documentación pendiente', $mail->render());
        $token = basename(parse_url($mail->urlCambioPassword, PHP_URL_PATH));
        parse_str(parse_url($mail->urlCambioPassword, PHP_URL_QUERY), $query);
        $this->assertSame($usuario->correo, $query['correo']);
        $this->assertTrue(Password::broker(config('fortify.passwords'))->tokenExists($usuario, $token));
        Auth::logout();
        $this->get($mail->urlCambioPassword)->assertOk();
        $this->post('/reset-password', ['token'=>$token, 'correo'=>$usuario->correo, 'password'=>'PersonalNueva#12345', 'password_confirmation'=>'PersonalNueva#12345'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('PersonalNueva#12345', $usuario->fresh()->getAuthPassword()));
        $this->assertFalse($usuario->fresh()->cambio_password_pendiente);
        $this->assertFalse(Password::broker(config('fortify.passwords'))->tokenExists($usuario, $token));
    }

    public function test_plazo_no_posterga_minimo_ni_admite_fecha_vencida_o_entrega_ya_adjunta(): void
    {
        foreach ([['documentos_con_plazo'=>['CI'],'fecha_limite_documentacion'=>'2026-11-20'],
            ['documentos_con_plazo'=>['DOMICILIO'],'fecha_limite_documentacion'=>'2026-11-04'],
            ['documentos_con_plazo'=>['NO_CONFIGURADO'],'fecha_limite_documentacion'=>'2026-11-20']] as $cambio) {
            try { app(GuardarPersonalInstitucional::class)->guardar($this->gerente, $this->entrada($cambio)); $this->fail('No debe aceptarse la solicitud inválida.'); }
            catch (ValidationException $error) { $this->assertNotEmpty($error->errors()); }
        }
        $sinIdentidad = $this->entrada(['documentos_con_plazo'=>['DOMICILIO'],'fecha_limite_documentacion'=>'2026-11-20']);
        unset($sinIdentidad['archivos_temporales']['CI']);
        try { app(GuardarPersonalInstitucional::class)->guardar($this->gerente, $sinIdentidad); $this->fail('El mínimo sigue siendo inmediato.'); }
        catch (ValidationException $error) { $this->assertArrayHasKey('archivos_temporales.CI',$error->errors()); }
        $this->assertDatabaseMissing('usuarios',['correo'=>'alta-documental@remembermind.test']);
        $this->assertDatabaseCount('solicitudes_documentales_personal',0); Mail::assertNothingSent();
    }

    public function test_fallo_del_expediente_revierte_alta_archivos_y_correo(): void
    {
        Event::listen('eloquent.creating: '.SolicitudDocumentalPersonal::class, fn ()=>throw new \RuntimeException('Fallo sintético del expediente.'));
        try { app(GuardarPersonalInstitucional::class)->guardar($this->gerente,$this->entrada(['documentos_con_plazo'=>['DOMICILIO'],'fecha_limite_documentacion'=>'2026-11-20'])); $this->fail('Debe revertirse el alta completa.'); }
        catch (\RuntimeException $error) { $this->assertSame('Fallo sintético del expediente.',$error->getMessage()); }
        $this->assertDatabaseMissing('usuarios',['correo'=>'alta-documental@remembermind.test']);
        $this->assertDatabaseCount('documentos',0); $this->assertDatabaseCount('destinatarios_solicitud_documental',0);
        $this->assertSame([],Storage::disk('local')->allFiles()); $this->assertSame([],Storage::disk('public')->allFiles()); Mail::assertNothingSent();
    }

    public function test_formulario_separa_matricula_mapa_y_fecha_laboral_por_defecto(): void
    {
        $form = Livewire::test(PersonalInstitucionalForm::class)->assertSet('fecha_ingreso','2026-11-05')
            ->set('direccion','CALLE A & B, LA PAZ, BOLIVIA')->assertSee('BUSCAR EN GOOGLE MAPS');
        parse_str(parse_url($form->instance()->getGoogleMapsUrlProperty(),PHP_URL_QUERY),$mapa);
        $this->assertSame(['api'=>'1','query'=>'CALLE A & B, LA PAZ, BOLIVIA'],$mapa);
        $form->set('rol_seleccionado','MEDICO GENERAL/GERIATRA')->set('pasoActual',2)->assertDontSeeHtml('id="trabajador-matricula"')
            ->set('pasoActual',4)->assertSeeHtml('id="trabajador-matricula"')
            ->set('documentos_con_plazo',['DOMICILIO'])->assertSet('fecha_limite_documentacion','2026-11-12')
            ->set('fecha_limite_documentacion','2026-11-23')->assertHasNoErrors(['fecha_limite_documentacion'])
            ->set('archivos_temporales.DOMICILIO',UploadedFile::fake()->create('domicilio.pdf',10,'application/pdf'))
            ->assertSet('documentos_con_plazo',[])->assertSet('fecha_limite_documentacion',null);
    }

    public function test_fecha_del_ingreso_se_deriva_del_registro_y_no_del_cliente(): void
    {
        $usuario = app(GuardarPersonalInstitucional::class)->guardar($this->gerente,$this->entrada(['fecha_ingreso'=>'1999-01-01']))['usuario'];
        $this->assertSame('2026-11-05',$usuario->personal->fecha_ingreso->toDateString());
        $usuario->personal->update(['fecha_ingreso'=>'2020-03-01']);
        $editado = app(GuardarPersonalInstitucional::class)->guardar($this->gerente,$this->entrada([
            'esEdicion'=>true,'usuarioId'=>$usuario->getKey(),'fecha_ingreso'=>'2099-01-01','archivos_temporales'=>[], 'contrasena_temporal'=>'']))['usuario'];
        $this->assertSame('2020-03-01',$editado->personal->fecha_ingreso->toDateString());
    }

    public function test_reintento_de_solicitud_con_otro_plazo_no_sobrescribe_la_fecha(): void
    {
        app(GuardarPersonalInstitucional::class)->guardar($this->gerente,$this->entrada(['documentos_con_plazo'=>['DOMICILIO'],'fecha_limite_documentacion'=>'2026-11-20']));
        $solicitud = SolicitudDocumentalPersonal::sole();
        $entrada = ['rol'=>'ADMINISTRADOR','tipo_documento'=>$solicitud->tipo_documento,'nombre_documento'=>$solicitud->nombre_documento,
            'personas'=>[$solicitud->destinatarios->sole()->cod_personal],'origen_plazo'=>'ALTA','fecha_limite'=>null,
            'motivo'=>$solicitud->motivo,'clave_solicitud'=>$solicitud->clave_solicitud];
        $accion=app(\App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales::class);
        $this->assertSame($solicitud->getKey(),$accion->emitir($this->gerente,$entrada,'2026-11-20')->getKey());
        try {$accion->emitir($this->gerente,$entrada,'2026-11-21');$this->fail('La fecha original no se reemplaza.');}
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {$this->assertSame(409,$error->getStatusCode());}
        $this->assertSame('2026-11-20',DestinatarioSolicitudDocumental::sole()->fecha_limite_original->toDateString());
    }

    public function test_solicitudes_adicionales_del_alta_conservan_el_plazo_del_expediente(): void
    {
        $usuario=app(GuardarPersonalInstitucional::class)->guardar($this->gerente,$this->entrada(['documentos_con_plazo'=>['DOMICILIO'],'fecha_limite_documentacion'=>'2026-11-20']))['usuario'];
        $solicitud=app(\App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales::class)->emitir($this->gerente,[
            'rol'=>'ADMINISTRADOR','tipo_documento'=>'EXP_LABORAL','nombre_documento'=>'Certificados de experiencia',
            'personas'=>[$usuario->personal->getKey()],'origen_plazo'=>'ALTA','fecha_limite'=>null,
            'motivo'=>'Solicitud documental complementaria.','clave_solicitud'=>(string) \Illuminate\Support\Str::uuid()]);
        $this->assertSame('2026-11-20',$solicitud->destinatarios->sole()->fecha_limite_original->toDateString());
        $this->assertSame(1,DestinatarioSolicitudDocumental::pluck('fecha_limite_original')->unique()->count());
    }
}
