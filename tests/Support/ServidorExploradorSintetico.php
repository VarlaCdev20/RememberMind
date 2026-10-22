<?php

declare(strict_types=1);
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

// Router E2E opt-in, fuera de public/. Solo acepta una base nueva, privada y sintética.
$archivo = getenv('RM_EXPLORADOR_DB') ?: '';
$token = getenv('RM_EXPLORADOR_TOKEN') ?: '';
$temporal = realpath(sys_get_temp_dir());
if (getenv('APP_ENV') !== 'testing' || strlen($token) !== 48
    || ! preg_match('/^remembermind-explorador-[a-f0-9]{16}_testing\.sqlite$/D', basename($archivo))
    || realpath(dirname($archivo)) !== $temporal || ! is_file($archivo)) {
    http_response_code(403);
    exit('Entorno sintético no habilitado.');
}

$raiz = dirname(__DIR__, 2);
$preparar = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--preparar';
$comprobar = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--estado';
if (! $preparar && ! $comprobar && ! hash_equals($token, $_SERVER['HTTP_X_RM_EXPLORADOR'] ?? '')) {
    http_response_code(403);
    exit('Acceso de prueba no autorizado.');
}
if (! $preparar && ! $comprobar) {
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $estatico = realpath($raiz.'/public'.$ruta);
    if ($estatico && str_starts_with($estatico, realpath($raiz.'/public').DIRECTORY_SEPARATOR) && is_file($estatico)) {
        return false;
    }
}

$variables = ['APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_KEY' => 'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8=',
    'APP_CONFIG_CACHE' => $archivo.'.config.php', 'APP_ROUTES_CACHE' => $archivo.'.routes.php',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $archivo, 'DB_URL' => '',
    'SESSION_DRIVER' => 'file', 'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync',
    'LARAVEL_STORAGE_PATH' => $archivo.'.storage', 'VIEW_COMPILED_PATH' => $archivo.'.storage/framework/views',
    'SUPERADMIN_CLINICAL_WRITE' => 'false'];
foreach ($variables as $clave => $valor) {
    putenv($clave.'='.$valor);
    $_ENV[$clave] = $_SERVER[$clave] = $valor;
}
foreach (['framework/views', 'framework/sessions', 'framework/cache/data', 'logs'] as $directorio) {
    if (! is_dir($archivo.'.storage/'.$directorio)) {
        mkdir($archivo.'.storage/'.$directorio, 0700, true);
    }
}
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
if ($comprobar) {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo json_encode(['aplicaciones' => App\Models\AplicacionInstrumento::count(),
        'respuestas' => App\Models\RespuestaInstrumento::count(), 'valor_logico' => App\Models\RespuestaInstrumento::value('valor_logico'),
        'evaluaciones' => App\Models\EvaluacionExperta::count(), 'instrumentos' => App\Models\Instrumento::count()], JSON_THROW_ON_ERROR);
    exit;
}
if ($preparar) {
    if (filesize($archivo) !== 0) {
        throw new RuntimeException('No se reutiliza ni se reinicia una base existente.');
    }
    $app->make(Kernel::class)->bootstrap();
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);
    $usuario = User::factory()->create(['cod_usuario' => 'E2E_GUIA', 'estado' => 'ACTIVO']);
    $usuario->assignRole('PSICOLOGO/A');
    $personal = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'E2E-GUIA', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
    foreach (['ARE_COG' => 'Cognitiva', 'ARE_AFE' => 'Afectiva', 'ARE_FUN' => 'Funcionamiento', 'ARE_SOC' => 'Entorno'] as $codigo => $nombre) {
        Area::create(['cod_area' => $codigo, 'nombre' => $nombre, 'estado' => 'ACTIVA']);
    }
    $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
    $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
    AsignacionPersonal::create(['cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => 'ARE_COG', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
    Residente::factory()->create(['cod_residente' => 'RES_E2E_GUIA', 'nombres' => 'Residente sintético', 'apellido_paterno' => 'Explorador', 'apellido_materno' => 'Verificación', 'estado' => 'ADMITIDO']);
    foreach (['OBS' => ['OBSERVACIONAL', 'ACTIVO'], 'FUN' => ['FUNCIONAL', 'ACTIVO'], 'HIS' => ['COGNITIVO', 'INACTIVO']] as $codigo => [$tipo, $estado]) {
        $instrumento = Instrumento::create(['cod_instrumento' => 'E2E_'.$codigo, 'codigo' => 'SINTETICO-'.$codigo, 'nombre' => $codigo === 'FUN' ? 'Registro funcional sintético' : 'Registro de observación sintético '.$codigo, 'version' => 'TEST-1', 'descripcion' => 'Contenido artificial para comprobar la interfaz. No es una escala clínica.', 'tipo' => $tipo, 'estado' => $estado]);
        if ($estado === 'ACTIVO') {
            PreguntaInstrumento::create(['cod_pregunta' => 'E2E_Q_'.$codigo, 'cod_instrumento' => $instrumento->getKey(), 'codigo' => 'SINTETICA', 'enunciado' => 'Dato original sintético', 'tipo_respuesta' => 'LOGICO', 'orden' => 1, 'estado' => 'ACTIVA']);
        }
    }
    echo json_encode(['estado' => 'PREPARADO', 'sintetico' => true], JSON_THROW_ON_ERROR);
    exit;
}

final class AutenticarExploradorSintetico
{
    public function handle($request, Closure $next)
    {
        // Exclusivamente este router de pruebas inyecta el actor sintético; no hay ruta ni login operativo.
        Auth::onceUsingId('E2E_GUIA');

        return $next($request);
    }
}
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->pushMiddleware(AutenticarExploradorSintetico::class);
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
