<?php

use Carbon\Carbon;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

// Solo servidor de pruebas en loopback, con una copia SQLite de fixtures sintéticos.
$root = realpath((string) getenv('RM_MEDICATION_HTTP_ROOT'));
if (PHP_SAPI !== 'cli-server' || ! $root || dirname($root) !== realpath(sys_get_temp_dir())
    || ! preg_match('/^remembermind-medication-http-[a-f0-9]{16}$/D', basename($root))
    || ! is_file($root.'/qa.sqlite')) {
    throw new RuntimeException('Se requiere el directorio propio del navegador de pruebas.');
}
$repo = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (str_starts_with($path, '/build/') && is_file($repo.'/public'.$path)) {
    return false;
}
foreach ([
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_CONFIG_CACHE' => $root.'/unused-config.php',
    'APP_ROUTES_CACHE' => $root.'/unused-routes.php', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $root.'/qa.sqlite',
    'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'file', 'LOG_CHANNEL' => 'stderr',
    'VIEW_COMPILED_PATH' => $root.'/views', 'CUENTA_PRINCIPAL_CODIGO' => '',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
define('LARAVEL_START', microtime(true));
require $repo.'/vendor/autoload.php';
$app = require $repo.'/bootstrap/app.php';
$app->booted(function () use ($root) {
    if (! app()->environment('testing') || config('database.default') !== 'sqlite'
        || config('database.connections.sqlite.database') !== $root.'/qa.sqlite') {
        throw new RuntimeException('Conexión de navegador inválida.');
    }
    // El reloj clínico del fixture está congelado; una caducidad absoluta sería pasada para el navegador real.
    config(['session.files' => $root.'/sessions', 'session.secure' => false, 'session.domain' => null, 'session.lifetime' => 1440, 'session.expire_on_close' => true,
        'filesystems.disks.public.root' => $root.'/public', 'filesystems.disks.public.url' => config('app.url').'/qa-media']);
    app(Vite::class)->useHotFile($root.'/unused.hot');
    Carbon::setTestNow('2026-10-14 09:00:00');
    Route::get('/qa-media/imagenes/medicamentos/{archivo}', function (string $archivo) use ($root) {
        abort_unless(preg_match('/\A[A-Za-z0-9_-]+\.png\z/', $archivo), 404);
        $path = $root.'/public/imagenes/medicamentos/'.$archivo;
        abort_unless(is_file($path), 404);

        return response()->file($path);
    });
    Route::middleware('web')->get('/__qa/medication', function () use ($root) {
        $context = json_decode(file_get_contents($root.'/context.json'), true, flags: JSON_THROW_ON_ERROR);
        Auth::loginUsingId($context['usuario']);

        return Blade::render(<<<'BLADE'
<!doctype html><html lang="es" data-theme="light"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Medicación · QA sintético</title>
@vite(['resources/frontend/styles/app.css','resources/frontend/scripts/app.js'])
@livewireStyles
<script defer src="https://unpkg.com/@phosphor-icons/web"></script></head><body class="rm-shell rm-nursing-shell"><main aria-label="QA sintético">
<button id="qa-open" x-data @click="$dispatch('abrirModalAdministracion', {cod_residente: @js($residente)})">Abrir formulario</button>
@livewire(\App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\AdministracionMedicacionModal::class)
</main>@livewireScripts</body></html>
BLADE, ['residente' => $context['residente']]);
    });
});
$app->handleRequest(Request::capture());
