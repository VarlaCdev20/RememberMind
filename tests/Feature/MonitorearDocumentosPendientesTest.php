<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Mail\DocumentoVencidoMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MonitorearDocumentosPendientesTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    public function test_expired_documents_are_marked_as_vencido_and_alert_email_is_sent(): void
    {
        Mail::fake();

        // 1. Crear un usuario de prueba
        $user = new User();
        $user->cod_usu = 'USU-' . Str::random(8);
        $user->nombres = 'TEST';
        $user->ap_paterno = 'USER';
        $user->correo = 'test.scheduler@remembermind.com';
        $user->password = bcrypt('password');
        $user->estado = 'DOCUMENTACION_PENDIENTE';
        $user->save();

        // 2. Insertar un documento vencido (>48 horas)
        $docVencidoId = 'DOC-' . Str::random(8);
        DB::table('documentos_usuarios')->insert([
            'cod_doc_usu' => $docVencidoId,
            'cod_usu' => $user->cod_usu,
            'tipo_documento' => 'INSTITUCIONAL',
            'nombre_documento' => 'Contrato o Acuerdo de Prestación de Servicios',
            'archivo' => 'PENDIENTE',
            'estado' => 'PENDIENTE',
            'created_at' => now()->subHours(49),
            'updated_at' => now()->subHours(49),
        ]);

        // 3. Insertar un documento no vencido (<48 horas)
        $docNoVencidoId = 'DOC-' . Str::random(8);
        DB::table('documentos_usuarios')->insert([
            'cod_doc_usu' => $docNoVencidoId,
            'cod_usu' => $user->cod_usu,
            'tipo_documento' => 'PERSONAL',
            'nombre_documento' => 'Título o Certificado Profesional',
            'archivo' => 'PENDIENTE',
            'estado' => 'PENDIENTE',
            'created_at' => now()->subHours(10),
            'updated_at' => now()->subHours(10),
        ]);

        // 4. Ejecutar el comando de Artisan
        $this->artisan('app:monitorear-documentos-pendientes')
            ->assertExitCode(0);

        // 5. Verificar que el documento de más de 48 horas se actualizó a VENCIDO
        $this->assertDatabaseHas('documentos_usuarios', [
            'cod_doc_usu' => $docVencidoId,
            'estado' => 'VENCIDO',
        ]);

        // 6. Verificar que el documento reciente sigue como PENDIENTE
        $this->assertDatabaseHas('documentos_usuarios', [
            'cod_doc_usu' => $docNoVencidoId,
            'estado' => 'PENDIENTE',
        ]);

        // 7. Verificar que se envió la alerta de correo electrónico
        Mail::assertSent(DocumentoVencidoMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->correo) && 
                   in_array('Contrato o Acuerdo de Prestación de Servicios', $mail->documentos);
        });
    }
}
