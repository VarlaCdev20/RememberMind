<?php

declare(strict_types=1);
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Vite;

// Entrada exclusiva de pruebas. No se carga desde public/ ni admite una base instalada.
$archivo = getenv('RM_RECUPERACION_DB') ?: '';
$tokenEnsayo = getenv('RM_RECUPERACION_TOKEN') ?: '';
$preparar = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--preparar';
if (getenv('APP_ENV') !== 'testing' || strlen($tokenEnsayo) !== 48
    || ! preg_match('/^remembermind-recuperacion-[a-f0-9]{16}_testing\.sqlite$/D', basename($archivo))
    || realpath(dirname($archivo)) !== realpath(sys_get_temp_dir()) || ! is_file($archivo)
    || (! $preparar && ! hash_equals($tokenEnsayo, $_SERVER['HTTP_X_RM_RECUPERACION_QA'] ?? ''))) {
    http_response_code(403);
    exit('Ensayo no autorizado.');
}
$raiz = dirname(__DIR__, 2);
if (! $preparar) {
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($ruta === '/storage/imagenes/LOGO.png') {
        // Asset institucional público exacto; no habilita archivos del storage clínico.
        header('Content-Type: image/png');
        readfile($raiz.'/public/storage/imagenes/LOGO.png');
        exit;
    }
    $estatico = realpath($raiz.'/public'.rawurldecode($ruta));
    if ($estatico && str_starts_with($estatico, realpath($raiz.'/public').DIRECTORY_SEPARATOR) && is_file($estatico)) {
        return false;
    }
    if (! in_array($ruta, ['/login', '/forgot-password', '/reset-password'], true)
        && ! (getenv('RM_RECORDAR_ENSAYO') === '1' && in_array($ruta, ['/_ensayo-recordarme', '/logout'], true))
        && ! str_starts_with($ruta, '/reset-password/')
        && ! preg_match('~^/livewire-[a-f0-9]+/livewire(?:\.min)?\.js$~D', $ruta)) {
        http_response_code(403);
        exit('Ruta fuera del ensayo.');
    }
}
foreach (['APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8=',
    'APP_CONFIG_CACHE' => $archivo.'.config.php', 'APP_ROUTES_CACHE' => $archivo.'.routes.php',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $archivo, 'DB_URL' => '',
    'SESSION_DRIVER' => 'file', 'SESSION_COOKIE' => 'recuperacion_qa_'.substr($tokenEnsayo, 0, 8),
    'CACHE_STORE' => 'array', 'MAIL_MAILER' => 'array', 'MAIL_FROM_ADDRESS' => 'ensayo@example.test',
    'MAIL_FROM_NAME' => 'RememberMind - ensayo',
    'AUTH_REMEMBER_ENABLED' => getenv('RM_RECORDAR_ENSAYO') === '1' ? 'true' : 'false',
    'AUTH_REMEMBER_MINUTES' => '43200'] as $clave => $valor) {
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
    User::factory()->create(['cod_usuario' => 'QA_RECUPERACION', 'correo' => 'recuperacion@example.test', 'password' => 'AnteriorSegura123!', 'estado' => 'ACTIVO']);
    echo 'PREPARADO';
    exit;
}
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
if (getenv('RM_RECORDAR_ENSAYO') === '1') {
    \Illuminate\Support\Facades\Route::middleware('web')->get('/_ensayo-recordarme', fn () => response()->json([
        'autenticado' => \Illuminate\Support\Facades\Auth::check(),
        'recordado' => \Illuminate\Support\Facades\Auth::viaRemember(), 'csrf' => csrf_token(),
    ])->header('Cache-Control', 'no-store'));
}
Vite::useHotFile($archivo.'.hot');
Notification::fake();
$request = Request::capture();
$response = $kernel->handle($request);
$usuario = User::findOrFail('QA_RECUPERACION');
$notificacion = Notification::sent($usuario, ResetPasswordNotification::class)->last();
if ($notificacion) {
    // Solo token sintético, fuera del repositorio y borrado al cerrar la prueba.
    file_put_contents($archivo.'.enlace.json', json_encode(['url' => $notificacion->toMail($usuario)->actionUrl], JSON_THROW_ON_ERROR));
}
$response->send();
$kernel->terminate($request, $response);
