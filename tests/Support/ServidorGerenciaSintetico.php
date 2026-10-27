<?php

declare(strict_types=1);

use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;

// Router privado de QA. Jamás se registra desde public/ ni utiliza la BDD institucional.
$archivo = getenv('RM_GERENCIA_QA_DB') ?: '';
$token = getenv('RM_GERENCIA_QA_TOKEN') ?: '';
$preparar = PHP_SAPI === 'cli' && ($argv[1] ?? '') === '--preparar';
if (getenv('APP_ENV') !== 'testing' || strlen($token) !== 48
    || !preg_match('/^remembermind-gerencia-[a-f0-9]{16}_testing\.sqlite$/D', basename($archivo))
    || realpath(dirname($archivo)) !== realpath(sys_get_temp_dir()) || !is_file($archivo)
    || (!$preparar && !hash_equals($token, $_SERVER['HTTP_X_RM_GERENCIA_QA'] ?? ''))) {
    http_response_code(403); exit('Ensayo no autorizado.');
}
$raiz = dirname(__DIR__, 2);
if (!$preparar) {
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($ruta === '/storage/imagenes/LOGO.png') {
        header('Content-Type: image/png'); readfile($raiz.'/public/storage/imagenes/LOGO.png'); exit;
    }
    $estatico = realpath($raiz.'/public'.rawurldecode($ruta));
    if ($estatico && str_starts_with($estatico, realpath($raiz.'/public').DIRECTORY_SEPARATOR) && is_file($estatico)) return false;
    if (!in_array($ruta, ['/dashboard','/cambiar-contrasena-inicial','/admin/personal-institucional','/admin/personal-documentacion','/admin/turnos-asignaciones','/admin/areas-institucionales','/admin/cobertura-equipo','/admin/residentes','/admin/alertas-clinicas','/admin/galeria'], true)
        && !str_starts_with($ruta, '/admin/reportes/institucional')
        && $ruta !== '/admin/familia-social/red-apoyo'
        && !str_starts_with($ruta, '/admin/galeria/fotografias/')
        && !str_starts_with($ruta, '/admin/personal-documentacion/archivos/')
        && !(getenv('RM_GERENCIA_QA_CORRECCION') === '1' && $ruta === '/admin/usuarios/QA_DOC_TITULAR')
        && !preg_match('~^/livewire-[a-f0-9]+/(?:livewire(?:\.min)?\.js|update|upload-file|preview-file/.*)$~D', $ruta)) {
        http_response_code(403); exit('Ruta fuera del ensayo.');
    }
}
foreach (['APP_ENV'=>'testing','APP_DEBUG'=>'false','APP_URL'=>getenv('APP_URL'),
    'APP_KEY'=>'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8=',
    'APP_CONFIG_CACHE'=>$archivo.'.config.php','APP_ROUTES_CACHE'=>$archivo.'.routes.php',
    'VIEW_COMPILED_PATH'=>$archivo.'.views', 'DB_CONNECTION'=>'sqlite','DB_DATABASE'=>$archivo,'DB_URL'=>'',
    'SESSION_DRIVER'=>'file','SESSION_COOKIE'=>'gerencia_qa_'.substr($token,0,8),
    'CACHE_STORE'=>'array','MAIL_MAILER'=>'array','QUEUE_CONNECTION'=>'sync'] as $clave=>$valor) {
    putenv($clave.'='.$valor); $_ENV[$clave] = $_SERVER[$clave] = $valor;
}
if (!is_dir($archivo.'.views')) mkdir($archivo.'.views');
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
if ($preparar) {
    if (filesize($archivo) !== 0) throw new RuntimeException('Solo se prepara una base temporal nueva y vacía.');
    $app->make(Kernel::class)->bootstrap();
    Artisan::call('migrate', ['--force'=>true]);
    if (getenv('RM_GERENCIA_QA_AMPLIACION')==='1') (require $raiz.'/database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php')->up();
    if (getenv('RM_GERENCIA_QA_HISTORIA')==='1') (require $raiz.'/database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php')->up();
    Artisan::call('db:seed', ['--class'=>Database\Seeders\RolesAndPermissionsSeeder::class,'--force'=>true]);
    $actor = User::factory()->create(['cod_usuario'=>'QA_GERENCIA','correo'=>'direccion@example.test','password'=>'TemporalGerencia#123']);
    Personal::create(['cod_usuario'=>$actor->getKey(),'nombres'=>'Dirección','apellido_paterno'=>'Sintética','numero_documento'=>'9100000','profesion'=>'GERENCIA','estado'=>'ACTIVO']);
    $actor->assignRole('GERENTE');
    if (getenv('RM_GERENCIA_QA_CORRECCION') === '1') {
        $actor->givePermissionTo(['usuarios.ver', 'documentos.subir']);
        $titularDoc = User::factory()->create(['cod_usuario' => 'QA_DOC_TITULAR', 'correo' => 'titular-doc@example.test']);
        $titularDoc->assignRole('ADMINISTRADOR');
        Personal::create(['cod_usuario' => $titularDoc->getKey(), 'nombres' => 'Titular documental', 'apellido_paterno' => 'Sintético',
            'numero_documento' => '9199999', 'profesion' => 'ADMINISTRADOR', 'estado' => 'ACTIVO']);
    }
    if(getenv('RM_GERENCIA_QA_AMPLIACION')==='1') $actor->forceFill(['fecha_hora_alta'=>now(),'cambio_password_pendiente'=>true])->save();
    $area = Area::create(['nombre'=>'Acompañamiento y cuidados','estado'=>'ACTIVA']);
    $turno = Turno::create(['nombre'=>'Mañana','hora_inicio'=>'08:00:00','hora_cierre'=>'16:00:00','estado'=>'ACTIVO','orden'=>1]);
    $jornada = Jornada::create(['cod_turno'=>$turno->getKey(),'fecha_jornada'=>today(),'estado'=>'ABIERTA']);
    foreach (['Enfermería','Psicología','Administración'] as $i=>$funcion) {
        $persona = User::factory()->create(['correo'=>'equipo'.($i+1).'@example.test']);
        $persona->assignRole(['ENFERMEROS','PSICOLOGO/A','ADMINISTRADOR'][$i]);
        if (getenv('RM_GERENCIA_QA_PRORROGA') === '1') $persona->forceFill(['fecha_hora_alta'=>now()])->save();
        $personal = Personal::create(['cod_usuario'=>$persona->getKey(),'nombres'=>'Profesional de prueba '.($i+1),'apellido_paterno'=>'Nombre extenso','numero_documento'=>(string)(9100001+$i),'profesion'=>$funcion,'estado'=>'ACTIVO']);
        AsignacionPersonal::create(['cod_jornada'=>$jornada->getKey(),'cod_area'=>$area->getKey(),'cod_personal'=>$personal->getKey(),'estado'=>'ACTIVA','funcion'=>$funcion]);
    }
    $habitacion = Habitacion::create(['codigo'=>'Habitación A','piso'=>'1','estado'=>'ACTIVA']);
    Cama::create(['cod_habitacion'=>$habitacion->getKey(),'codigo'=>'Cama A','estado'=>'DISPONIBLE']);
    $residentes = collect([
        ['nombres'=>'Persona de prueba','apellido_paterno'=>'Primer apellido','apellido_materno'=>'Segundo apellido'],
        ['nombres'=>'Otra persona','apellido_paterno'=>'Apellido extenso','apellido_materno'=>'Prueba'],
        ['nombres'=>'Residente','apellido_paterno'=>'Sintético','apellido_materno'=>'Verificación'],
    ])->map(fn ($datos) => Residente::factory()->create([...$datos,'estado'=>'ADMITIDO']));
    foreach (['ABIERTA','EN_ATENCION','CERRADA'] as $i=>$estado) {
        Alerta::create(['cod_residente'=>$residentes[$i]->getKey(),'tipo'=>'Seguimiento de prueba','titulo'=>'Aviso sintético',
            'descripcion'=>'Registro de prueba para comprobar lectura, filtros y detalle.','prioridad'=>['ALTO','MEDIO','BAJO'][$i],
            'estado'=>$estado,'fecha_hora'=>now()->subHours($i+1),'modulo'=>'MANUAL','generacion'=>'MANUAL']);
    }
    $contacto=App\Models\Contacto::create(['nombres'=>'Contacto de prueba','apellido_paterno'=>'Identidad sintética','correo'=>'contacto@example.test','estado'=>'ACTIVO']);
    App\Models\ResidenteContacto::create(['cod_residente_contacto'=>'QA_VINCULO_FAMILIAR','cod_residente'=>$residentes[0]->getKey(),'cod_contacto'=>$contacto->getKey(),'parentesco'=>'Hija','responsable_principal'=>false,'contacto_emergencia'=>false,'autoriza_informacion'=>true,'autoriza_salida'=>false,'estado'=>'ACTIVO']);
    echo 'PREPARADO'; exit;
}
final class AutenticarGerenciaSintetica
{
    public function handle($request, Closure $next)
    {
        Auth::onceUsingId('QA_GERENCIA');
        return $next($request);
    }
}
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Vite::useHotFile($archivo.'.hot');
config(['filesystems.disks.local.root'=>$archivo.'.storage','filesystems.disks.public.root'=>$archivo.'.storage/public']);
config(['filesystems.disks.tmp-for-tests'=>[
    'driver'=>'local', 'root'=>$archivo.'.storage/tmp-livewire', 'throw'=>true,
]]);
config(['logging.default'=>'single','logging.channels.single.path'=>$archivo.'.log']);
$kernel->pushMiddleware(AutenticarGerenciaSintetica::class);
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
