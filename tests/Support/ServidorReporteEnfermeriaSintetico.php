<?php

declare(strict_types=1);
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;

// Router exclusivo para QA con base temporal, nunca cargado por public/index.php.
$db = getenv('RM_REPORTE_DB') ?: '';
$token = getenv('RM_REPORTE_TOKEN') ?: '';
$preparar = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--preparar';
if (getenv('APP_ENV') !== 'testing' || strlen($token) !== 48
    || ! preg_match('/^remembermind-reporte-[a-f0-9]{16}_testing\.sqlite$/D', basename($db))
    || realpath(dirname($db)) !== realpath(sys_get_temp_dir()) || ! is_file($db)
    || (! $preparar && ! hash_equals($token, $_SERVER['HTTP_X_RM_REPORTE_QA'] ?? ''))) {
    http_response_code(403);
    exit('Ensayo no autorizado.');
}
$raiz = dirname(__DIR__, 2);
if (! $preparar) {
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($ruta === '/storage/imagenes/LOGO.png') {
        header('Content-Type: image/png');
        readfile($raiz.'/public/storage/imagenes/LOGO.png');
        exit;
    }
    $estatico = realpath($raiz.'/public'.rawurldecode($ruta));
    if ($estatico && str_starts_with($estatico, realpath($raiz.'/public').DIRECTORY_SEPARATOR) && is_file($estatico)) {
        return false;
    }
    if (! in_array($ruta, ['/admin/enfermeria/reportes', '/admin/enfermeria/reportes/vista-previa', '/admin/enfermeria/reportes/pdf'], true)
        && ! preg_match('~^/livewire-[a-f0-9]+/(?:update|livewire(?:\.min)?\.js)$~D', $ruta)) {
        http_response_code(403);
        exit('Ruta fuera del ensayo.');
    }
}
foreach (['APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_KEY' => 'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8=',
    'APP_CONFIG_CACHE' => $db.'.config.php', 'APP_ROUTES_CACHE' => $db.'.routes.php', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $db, 'DB_URL' => '',
    'SESSION_DRIVER' => 'file', 'SESSION_COOKIE' => 'reporte_qa_'.substr($token, 0, 8), 'CACHE_STORE' => 'array', 'MAIL_MAILER' => 'array',
    'AUTH_REMEMBER_ENABLED' => 'false'] as $clave => $valor) {
    putenv($clave.'='.$valor);
    $_ENV[$clave] = $_SERVER[$clave] = $valor;
}
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
Carbon::setTestNow('2026-10-27 18:00:00');
if ($preparar) {
    if (filesize($db) !== 0) {
        throw new RuntimeException('Solo se admite una base temporal vacía.');
    }
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);
    $usuario = User::factory()->create(['cod_usuario' => 'QA_REPORTE', 'correo' => 'reporte@example.test', 'estado' => 'ACTIVO']);
    $usuario->assignRole('ENFERMEROS');
    Personal::create(['cod_personal' => 'PER_QA_RPT', 'cod_usuario' => $usuario->getKey(), 'nombres' => 'Lucía', 'apellido_paterno' => 'Sintética', 'numero_documento' => 'QA-RPT-01', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
    Turno::create(['cod_turno' => 'TUR_QA_RPT', 'nombre' => 'Turno sintético', 'hora_inicio' => '06:00:00', 'hora_cierre' => '14:00:00', 'orden' => 1, 'estado' => 'ACTIVO']);
    Jornada::create(['cod_jornada' => 'JOR_QA_RPT', 'cod_turno' => 'TUR_QA_RPT', 'fecha_jornada' => today(), 'estado' => 'CERRADA']);
    Residente::crearDesdeAdmision(['cod_residente' => 'RES_QA_RPT', 'nombres' => 'Alicia', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1940-01-01', 'genero' => 'FEMENINO', 'estado' => 'ADMITIDO']);
    AsignacionResidenteJornada::create(['cod_asignacion' => 'ASG_QA_RPT', 'cod_residente' => 'RES_QA_RPT', 'cod_personal' => 'PER_QA_RPT', 'cod_jornada' => 'JOR_QA_RPT', 'nivel_supervision' => 'ESTANDAR', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
    for ($i = 0; $i < 23; $i++) {
        SignoVital::create(['cod_signo' => 'SIG_QA_'.$i, 'cod_residente' => 'RES_QA_RPT', 'cod_personal' => 'PER_QA_RPT', 'cod_jornada' => 'JOR_QA_RPT', 'fecha_hora' => '2026-10-27 10:00:00', 'presion_sistolica' => 120, 'presion_diastolica' => 80, 'estado' => 'VIGENTE']);
    }
    echo 'PREPARADO';
    exit;
}
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Auth::setUser(User::findOrFail('QA_REPORTE'));
Vite::useHotFile($db.'.hot');
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
