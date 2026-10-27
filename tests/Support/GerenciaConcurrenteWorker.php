<?php

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales;
use App\Modulos\Identidad\Aplicacion\Acciones\GuardarPersonalInstitucional;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use App\Modulos\Identidad\Aplicacion\Acciones\RegistrarMovimientoLaboral;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarHorariosPersonal;
use App\Modulos\Social\Aplicacion\Acciones\GestionarRedApoyo;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\Support\EntornoPruebas;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$db = DB::connection();
if (! app()->environment('testing') || $db->getDriverName() !== 'pgsql'
    || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
    throw new RuntimeException('Worker exclusivo de PostgreSQL desechable.');
}
$root = realpath($argv[1]);
if (! $root || dirname($root) !== realpath(sys_get_temp_dir()) || ! preg_match('/^remembermind-gerencia-race-[a-f0-9]{32}$/D', basename($root))) {
    throw new RuntimeException('Barrera ajena al ensayo.');
}
$datos = json_decode(file_get_contents($root.'/datos.json'), true, flags: JSON_THROW_ON_ERROR);
if (isset($datos['aplicacion'])) {
    DB::select("select set_config('application_name',?,false)", [$datos['aplicacion'].(! empty($datos['identificar_workers']) ? '_'.$argv[2] : '')]);
}
$actor = User::findOrFail($datos['actor']);
Auth::onceUsingId($actor->getKey());
app('request')->setMethod('POST');
if (in_array($datos['modo'], ['alta_personal', 'sustituir_documento'], true)) Mail::fake();
file_put_contents($root.'/listo-'.$argv[2], '1');
$inicio = microtime(true);
while (empty($datos['sin_barrera']) && (! is_file($root.'/listo-1') || ! is_file($root.'/listo-2'))) {
    if (microtime(true) - $inicio > 15) {
        throw new RuntimeException('La otra solicitud no alcanzó la barrera.');
    }
    usleep(20000);
}
try {
    $resultado = match ($datos['modo']) {
        'alta_personal' => (function () use ($datos, $root, $actor, $argv) {
            $entrada = $datos['entradas'][(int) $argv[2] - 1];
            $entrada['contrasena_temporal'] = bin2hex(random_bytes(12));
            $entrada['archivos_temporales'] = ['CI' => new UploadedFile($root.'/entrada.pdf', 'ci.pdf', null, null, true),
                'CV' => new UploadedFile($root.'/entrada.pdf', 'cv.pdf', null, null, true),
                'FOTO_DOC' => new UploadedFile($root.'/foto.jpg', 'foto.jpg', null, null, true)];

            return app(GuardarPersonalInstitucional::class)->guardar($actor, $entrada)['usuario']->getKey();
        })(),
        'sustituir_documento' => app(DocumentacionUsuarioService::class)->subirDocumento(
            User::findOrFail($datos['titular']), 'CI', new UploadedFile($root.'/entrada.pdf', 'correccion.pdf', null, null, true),
            null, null, 'Corrección concurrente de prueba', $actor, $datos['referencia'])->getKey(),
        'emitir' => app(GestionarSolicitudesDocumentales::class)->emitir($actor, $datos['entrada'])->getKey(),
        'prorrogar' => app(GestionarSolicitudesDocumentales::class)->prorrogar($actor, $datos['destinatario'], 'Prórroga concurrente de prueba')->getKey(),
        'prorrogar_expediente' => app(GestionarSolicitudesDocumentales::class)->prorrogar($actor, $datos['destinatarios'][(int) $argv[2] - 1], 'Prórroga concurrente del expediente')->getKey(),
        'laboral' => app(RegistrarMovimientoLaboral::class)->ejecutar($actor, $datos['personal'], $datos['entrada'])->getKey(),
        'vinculo' => app(GestionarRedApoyo::class)->vincular($actor, $datos['residente'], $datos['contacto'], $datos['entrada'])->getKey(),
        'horario' => (function () use ($datos) {
            app(GestionarHorariosPersonal::class)->guardar($datos['entrada']);

            return 'GUARDADO';
        })(),
        default => throw new RuntimeException('Operación fuera del ensayo.'),
    };
    echo json_encode(['estado' => 'CONFIRMADO', 'codigo' => $resultado,
        'bienvenidas' => $datos['modo'] === 'alta_personal' ? Mail::sent(\App\Mail\PersonalInstitucionalBienvenidaMail::class)->count() : 0], JSON_THROW_ON_ERROR);
} catch (ValidationException $error) {
    echo json_encode(['estado' => 'RECHAZADO', 'campos' => array_keys($error->errors()), 'bienvenidas' => 0], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    if ($error instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $error->getStatusCode() === 403) {
        echo json_encode(['estado' => 'DENEGADO', 'bienvenidas' => 0], JSON_THROW_ON_ERROR);
        exit(0);
    }
    fwrite(STDERR, json_encode(['estado' => 'ERROR', 'tipo' => get_class($error),
        'origen' => basename($error->getFile()), 'linea' => $error->getLine(),
        'detalle' => substr(strtok($error->getMessage(), "\n"), 0, 350)], JSON_THROW_ON_ERROR));
    exit(1);
}
