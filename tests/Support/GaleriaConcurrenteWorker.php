<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
if (! app()->environment('testing') || $db->getDriverName() !== 'pgsql'
    || ! Tests\Support\EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
    throw new RuntimeException('Worker exclusivo de PostgreSQL desechable.');
}
$root = realpath($argv[1]);
if (! $root || dirname($root) !== realpath(sys_get_temp_dir()) || ! preg_match('/^remembermind-gallery-race-[a-f0-9]{32}$/D', basename($root))) {
    throw new RuntimeException('Directorio de barrera ajeno a la prueba.');
}
config(['filesystems.disks.local.root' => $root.'/archivos']);
app()->bind(App\Modulos\Institucion\Infraestructura\Fotografias\AlmacenamientoFotografias::class,
    fn () => new class($root, $argv[2]) extends App\Modulos\Institucion\Infraestructura\Fotografias\AlmacenamientoFotografias {
        public function __construct(private string $root, private string $worker) {}
        public function preparar(object $archivo): array
        {
            $datos = parent::preparar($archivo);
            file_put_contents($this->root.'/listo-'.$this->worker, '1');
            $inicio = microtime(true);
            while (! is_file($this->root.'/listo-1') || ! is_file($this->root.'/listo-2')) {
                if (microtime(true) - $inicio > 20) {
                    $this->compensar([$datos['ruta_archivo'], $datos['ruta_miniatura']]);
                    throw new RuntimeException('La otra subida no alcanzó la barrera.');
                }
                usleep(25000);
            }
            return $datos;
        }
    });
$usuario = App\Models\User::findOrFail($argv[3]);
$resultado = app(App\Modulos\Institucion\Aplicacion\Acciones\GestionarFotografiasInstitucionales::class)->subir($usuario,
    new Illuminate\Http\UploadedFile($root.'/entrada.png', 'entrada.png', 'image/png', null, true),
    ['titulo' => 'Fotografía concurrente sintética', 'usar_en_bienvenida' => false]);
echo json_encode(['nueva' => $resultado['nueva'], 'codigo' => $resultado['foto']->getKey()]);
