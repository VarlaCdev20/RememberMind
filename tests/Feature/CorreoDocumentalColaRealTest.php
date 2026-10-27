<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;
use App\Modulos\Identidad\Infraestructura\Notificaciones\PrepararCorreoDocumental;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

class CorreoDocumentalColaRealTest extends TestCase
{
    use EsquemaSinTransaccion;

    public function test_cola_sql_reintenta_tras_30_y_120_segundos_y_acepta_una_sola_vez(): void
    {
        $this->freezeTime();
        config(['queue.default' => 'database', 'queue.connections.database.after_commit' => false]);
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        Permission::findOrCreate('usuarios.ver', 'web');
        $actor->givePermissionTo('usuarios.ver');
        $destino = User::factory()->create(['estado' => 'ACTIVO', 'correo' => 'destino@example.test']);
        $this->actingAs($actor);
        $envios = app(EnviosCorreoDocumental::class);
        $estado = $envios->solicitar($actor, $destino, 'ficha');
        $preparador = Mockery::mock(PrepararCorreoDocumental::class);
        $preparador->shouldReceive('preparar')->times(3)->andReturn((new Mailable)->html('Documento de prueba de cola.'));
        $this->app->instance(PrepararCorreoDocumental::class, $preparador);
        $intentos = 0;
        $mailerReal = app('mailer');
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->times(3)->andReturnUsing(function ($mail) use (&$intentos, $mailerReal) {
            if (++$intentos < 3) {
                throw new TransportException('Rechazo temporal sintético', 451);
            }

            return $mailerReal->to('destino@example.test')->send($mail);
        });
        $worker = app('queue.worker');
        $options = new WorkerOptions(sleep: 0, maxTries: 1);
        foreach ([30, 120] as $demora) {
            $worker->runNextJob('database', EnviosCorreoDocumental::COLA, $options);
            $this->assertSame('REINTENTO', $envios->estado($actor, $destino, $estado['solicitud'])['estado']);
            $this->assertSame(now()->timestamp + $demora, (int) DB::table('jobs')->value('available_at'));
            $this->assertDatabaseCount('failed_jobs', 0);
            $this->travel($demora + 1)->seconds();
        }
        $worker->runNextJob('database', EnviosCorreoDocumental::COLA, $options);
        $this->assertSame('ACEPTADO', $envios->estado($actor, $destino, $estado['solicitud'])['estado']);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertCount(1, $mailerReal->getSymfonyTransport()->messages());
    }
}
