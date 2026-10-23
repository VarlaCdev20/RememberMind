<?php

declare(strict_types=1);
use App\Models\AsignacionResidenteJornada;
use App\Models\ControlCognitivo;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use Database\Seeders\CohorteCognitivaInvestigacionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;

// Router privado E2E. Nunca se carga desde public/ ni acepta una base instalada.
$archivo = getenv('RM_EXPERTO_DB') ?: '';
$token = getenv('RM_EXPERTO_TOKEN') ?: '';
$preparar = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--preparar';
$conCohorte = getenv('RM_EXPERTO_COHORTE') === '1';
if (getenv('APP_ENV') !== 'testing' || strlen($token) !== 48
    || ! preg_match('/^remembermind-experto-[a-f0-9]{16}_testing\.sqlite$/D', basename($archivo))
    || realpath(dirname($archivo)) !== realpath(sys_get_temp_dir()) || ! is_file($archivo)
    || (! $preparar && ! hash_equals($token, $_SERVER['HTTP_X_RM_EXPERTO'] ?? ''))) {
    http_response_code(403);
    exit('Entorno sintético no autorizado.');
}
$raiz = dirname(__DIR__, 2);
if (! $preparar) {
    $estatico = realpath($raiz.'/public'.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($estatico && str_starts_with($estatico, realpath($raiz.'/public').DIRECTORY_SEPARATOR) && is_file($estatico)) {
        return false;
    }
}
foreach (['APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8=',
    'APP_CONFIG_CACHE' => $archivo.'.config.php', 'APP_ROUTES_CACHE' => $archivo.'.routes.php',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $preparar && $conCohorte ? ':memory:' : $archivo, 'DB_URL' => '',
    'SESSION_DRIVER' => 'file', 'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync',
    'SUPERADMIN_CLINICAL_WRITE' => 'false', 'SISTEMA_EXPERTO_SIMULACION_LOCAL' => 'false'] as $clave => $valor) {
    putenv($clave.'='.$valor);
    $_ENV[$clave] = $_SERVER[$clave] = $valor;
}
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
if ($preparar) {
    if (filesize($archivo) !== 0) {
        throw new RuntimeException('Solo se prepara un archivo nuevo y vacío.');
    }
    $app->make(Kernel::class)->bootstrap();
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);
    foreach (['SA' => 'SUPERADMINISTRADOR', 'MED' => 'MEDICO GENERAL/GERIATRA', 'ENF' => 'ENFERMEROS'] as $id => $rol) {
        $u = User::factory()->create(['cod_usuario' => 'QA_EXP_'.$id, 'estado' => 'ACTIVO']);
        $u->assignRole($rol);
        if ($id === 'MED' && $conCohorte) {
            // El médico de este ensayo necesita leer todas las fuentes de la
            // cohorte; el permiso existe solo en esta base desechable.
            $u->givePermissionTo(array_values(array_unique(array_column(ContratoFuentesPetersen::FUENTES, 1))));
        }
        if ($id !== 'SA') {
            $personal[$id] = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional',
                'apellido_paterno' => 'Sintético', 'numero_documento' => 'QA-EXP-'.$id,
                'profesion' => $id === 'MED' ? 'MEDICINA' : 'ENFERMERIA', 'estado' => 'ACTIVO']);
        }
    }
    $r = Residente::factory()->create(['cod_residente' => 'RES_QA_EXPERTO', 'nombres' => 'Elena sintética',
        'apellido_paterno' => 'Referencia', 'apellido_materno' => 'Verificación', 'foto' => null, 'estado' => 'ADMITIDO']);
    Residente::factory()->create(['cod_residente' => 'RES_QA_AJENO', 'nombres' => 'Residente no asignado', 'estado' => 'ADMITIDO', 'foto' => null]);
    $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'estado' => 'ACTIVO']);
    $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
    AsignacionResidenteJornada::create(['cod_residente' => $r->getKey(), 'cod_personal' => $personal['ENF']->getKey(),
        'cod_jornada' => $jornada->getKey(), 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
    ControlCognitivo::create(['cod_residente' => $r->getKey(), 'cod_personal' => $personal['MED']->getKey(),
        'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE', 'observacion' => 'Registro sintético para comprobar procedencia y lectura.']);
    if ($conCohorte) {
        // La cohorte solo permite SQLite :memory:. Se exporta después a este archivo temporal vacío.
        $cohorte = new CohorteCognitivaInvestigacionSeeder;
        $residentesCohorte = $cohorte->cargar();
        file_put_contents($archivo.'.cohorte.json', json_encode(['medico' => $cohorte->medicina->getKey(),
            'residente' => $residentesCohorte['S01']->getKey(), 'perfiles' => $cohorte->perfiles], JSON_THROW_ON_ERROR));
        $pdo = DB::connection()->getPdo();
        $pdo->exec('VACUUM INTO '.$pdo->quote($archivo));
    }
    echo 'PREPARADO';
    exit;
}
final class AutenticarExpertoSintetico
{
    public function handle($request, Closure $next)
    {
        $actor = $_SERVER['HTTP_X_RM_ACTOR'] ?? 'SA';
        abort_unless(in_array($actor, ['SA', 'MED', 'ENF'], true), 403);
        $metadata = getenv('RM_EXPERTO_COHORTE') === '1'
            ? json_decode(file_get_contents(getenv('RM_EXPERTO_DB').'.cohorte.json'), true, 512, JSON_THROW_ON_ERROR) : null;
        if ($metadata) {
            config(['sistema_experto.perfiles_instrumentales' => $metadata['perfiles']]);
        }
        Auth::onceUsingId('QA_EXP_'.$actor);

        return $next($request);
    }
}
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
// La prueba sirve los assets compilados desde el mismo host sintético, aunque
// el proyecto local tenga un servidor Vite activo para la sesión de desarrollo.
Vite::useHotFile($archivo.'.hot');
$kernel->pushMiddleware(AutenticarExpertoSintetico::class);
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
