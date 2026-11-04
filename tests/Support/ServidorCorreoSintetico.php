<?php

// Router privado de pruebas E2E: base temporal vacía, autenticación sintética y correo sin red.
declare(strict_types=1);
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

$archivo = getenv('RM_CORREO_QA_DB') ?: '';
$token = getenv('RM_CORREO_QA_TOKEN') ?: '';
$preparar = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--preparar';
if (getenv('APP_ENV') !== 'testing' || strlen($token) !== 48
    || ! preg_match('/^remembermind-correo-[a-f0-9]{16}_testing\.sqlite$/D', basename($archivo))
    || realpath(dirname($archivo)) !== realpath(sys_get_temp_dir()) || ! is_file($archivo)
    || (! $preparar && ! hash_equals($token, $_SERVER['HTTP_X_RM_CORREO_QA'] ?? ''))) {
    http_response_code(403);
    exit('Entorno de prueba no autorizado.');
}
$raiz = dirname(__DIR__, 2);
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (! $preparar) {
    $estatico = realpath($raiz.'/public'.$ruta);
    if ($estatico && str_starts_with($estatico, realpath($raiz.'/public').DIRECTORY_SEPARATOR) && is_file($estatico)) {
        return false;
    }
    if ($ruta !== '/admin/usuarios/correos/historial' && ! preg_match('~^/livewire-[a-f0-9]+/(?:livewire(?:\.min)?\.js|update)$~D', $ruta)) {
        http_response_code(403);
        exit('Ruta fuera del ensayo.');
    }
}
foreach (['APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8=',
    'APP_CONFIG_CACHE' => $archivo.'.config.php', 'APP_ROUTES_CACHE' => $archivo.'.routes.php',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $archivo, 'DB_URL' => '',
    'SESSION_DRIVER' => 'file', 'SESSION_COOKIE' => 'correo_qa_'.substr($token, 0, 8),
    'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'database', 'MAIL_MAILER' => 'array'] as $clave => $valor) {
    putenv($clave.'='.$valor);
    $_ENV[$clave] = $_SERVER[$clave] = $valor;
}
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
if ($preparar) {
    if (filesize($archivo) !== 0) {
        throw new RuntimeException('El ensayo solo prepara una base temporal nueva y vacía.');
    }
    $app->make(Kernel::class)->bootstrap();
    Artisan::call('migrate', ['--force' => true]);
    foreach (['usuarios.ver', 'usuarios.editar'] as $permiso) {
        Permission::findOrCreate($permiso, 'web');
    }
    $actor = User::factory()->create(['cod_usuario' => 'QA_CORREO_AUTOR', 'nombres' => 'Administración sintética', 'estado' => 'ACTIVO']);
    $actor->givePermissionTo(['usuarios.ver', 'usuarios.editar']);
    $destino = User::factory()->create(['nombres' => 'Destinataria sintética de nombre extenso', 'ap_paterno' => 'Verificación', 'ap_materno' => 'Correo', 'estado' => 'ACTIVO']);
    $envios = app(EnviosCorreoDocumental::class);
    foreach (['ACEPTADO', 'FALLIDO', 'INDETERMINADO', 'REINTENTO', 'PENDIENTE', 'PREPARANDO', 'ENVIANDO'] as $i => $estado) {
        $id = (string) Str::uuid();
        $envios->registrar($actor, $destino, $id, $i % 2 ? 'paquete' : 'ficha', 'PENDIENTE');
        if ($estado !== 'PENDIENTE') {
            $envios->registrar($actor, $destino, $id, $i % 2 ? 'paquete' : 'ficha', $estado, 1, $estado === 'REINTENTO' ? 30 : null);
        }
    }
    echo 'PREPARADO';
    exit;
}
final class AutenticarCorreoSintetico
{
    public function handle($request, Closure $next)
    {
        Auth::onceUsingId('QA_CORREO_AUTOR');

        return $next($request);
    }
}
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Vite::useHotFile($archivo.'.hot');
$kernel->pushMiddleware(AutenticarCorreoSintetico::class);
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
