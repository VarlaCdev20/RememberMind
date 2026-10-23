<?php

declare(strict_types=1);

use App\Models\AplicacionInstrumento;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use Carbon\CarbonImmutable;
use Database\Seeders\CohorteCognitivaInvestigacionSeeder;
use Database\Seeders\Datos\ManifiestoCognitivo30;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$base = $argv[1] ?? '';
$verificar = ($argv[2] ?? '') === '--verificar';
$nombrar = ($argv[2] ?? '') === '--nombrar';
$db = DB::connection();
if (PHP_SAPI !== 'cli' || ! CohorteCognitivaLocal::habilitada() || $db->getDriverName() !== 'pgsql'
    || $base === '' || $base !== $db->getDatabaseName() || ($argc > 2 && ! $verificar && ! $nombrar) || $argc > 3) {
    throw new RuntimeException('Uso local optativo: php scripts/cargar-cohorte-local.php nombre-exacto-base [--verificar|--nombrar]. Requiere simulación local autorizada para esa base.');
}
$privado = storage_path('app/private/auditorias-locales/cohorte30');
if (! is_dir($privado) && ! mkdir($privado, 0770, true) && ! is_dir($privado)) {
    throw new RuntimeException('No se pudo preparar el directorio privado de respaldo.');
}

/** Huellas de filas anteriores: no vuelca datos personales, respuestas ni textos clínicos. */
$huellas = static function (array $residentes): array {
    $tablas = DB::table('information_schema.columns')->where('table_schema', 'public')->where('column_name', 'cod_residente')
        ->orderBy('table_name')->pluck('table_name')->all();
    $consultas = [];
    foreach ($tablas as $tabla) {
        if (! preg_match('/^[a-z_]+$/D', $tabla)) {
            throw new RuntimeException('Identificador de tabla no admitido para la verificación.');
        }
        $consultas[$tabla] = DB::table($tabla)->whereIn('cod_residente', $residentes);
    }
    $aplicaciones = AplicacionInstrumento::query()->whereIn('cod_residente', $residentes)->pluck('cod_aplicacion');
    $consultas['respuestas_instrumento'] = DB::table('respuestas_instrumento')->whereIn('cod_aplicacion', $aplicaciones);
    foreach (['versiones_modelo_experto', 'reglas_expertas', 'roles', 'permissions', 'role_has_permissions', 'migrations'] as $tabla) {
        $consultas[$tabla] = DB::table($tabla);
    }
    $resultados = [];
    foreach ($consultas as $tabla => $query) {
        $filas = (clone $query)->selectRaw('md5(row_to_json("'.$tabla.'")::text) as huella_fila');
        $resultados[$tabla] = ['filas' => (clone $query)->count(), 'huella' => DB::query()->fromSub($filas, 'f')
            ->selectRaw("md5(COALESCE(string_agg(huella_fila, '' ORDER BY huella_fila), '')) as huella")->value('huella')];
    }

    return $resultados;
};

$bloqueo = DB::selectOne("select pg_try_advisory_lock(hashtext('remembermind:cohorte30')) as obtenido");
if (! filter_var($bloqueo->obtenido, FILTER_VALIDATE_BOOL)) {
    throw new RuntimeException('Otra carga de la cohorte está en curso; no se inicia una segunda.');
}
$destinoAnterior = getenv('RM_COHORTE30_DESTINO');
$localAnterior = getenv('RM_COHORTE30_CARGA_LOCAL');
$perfilesAnteriores = config('sistema_experto.perfiles_instrumentales');
try {
    $anteriores = Residente::query()->where(fn ($q) => $q->whereNull('numero_documento')->orWhere('numero_documento', 'not like', 'COH30-%'))
        ->orderBy('cod_residente')->pluck('cod_residente')->all();
    $existentes = Residente::query()->where('numero_documento', 'like', 'COH30-%')->count();
    $respaldo = null;
    $huellasAntes = null;
    $nombresActualizados = 0;
    if ($existentes !== 0 && $existentes !== 30) {
        throw new RuntimeException('Existe un lote parcial o incompatible. No se elimina ni sobrescribe automáticamente.');
    }
    if ($existentes === 0) {
        if ($verificar || $nombrar) {
            throw new RuntimeException('La cohorte todavía no está cargada. El modo de verificación no crea datos.');
        }
        $binario = PHP_OS_FAMILY === 'Windows'
            ? (getenv('ProgramFiles') ?: 'C:/Program Files').'/PostgreSQL/18/bin/pg_dump.exe' : '/usr/bin/pg_dump';
        if (! is_file($binario)) {
            throw new RuntimeException('No se encontró pg_dump; no se inicia la carga sin respaldo.');
        }
        $respaldo = $privado.'/antes-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.dump';
        $dump = new Process([$binario, '--host='.$db->getConfig('host'), '--port='.$db->getConfig('port'),
            '--username='.$db->getConfig('username'), '--dbname='.$base, '--format=custom', '--file='.$respaldo],
            dirname(__DIR__), ['PGPASSWORD' => (string) $db->getConfig('password')]);
        $dump->setTimeout(120);
        $dump->run();
        if (! $dump->isSuccessful() || ! is_file($respaldo) || filesize($respaldo) === 0) {
            throw new RuntimeException('No se confirmó el respaldo PostgreSQL; no se cargó la cohorte.');
        }
        putenv('RM_COHORTE30_DESTINO='.$base);
        putenv('RM_COHORTE30_CARGA_LOCAL=1');
        DB::transaction(function () use ($huellas, $anteriores, &$huellasAntes): void {
            DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $huellasAntes = $huellas($anteriores);
            (new CohorteCognitivaInvestigacionSeeder)->run();
            if ($huellasAntes !== $huellas($anteriores)) {
                throw new RuntimeException('La carga alteró información anterior o gobierno. Se revierte toda la operación.');
            }
        });
        $auditoria = ['fecha' => now()->toIso8601String(), 'version' => CohorteCognitivaInvestigacionSeeder::VERSION,
            'residentes_anteriores' => count($anteriores), 'residentes_agregados' => 30, 'esquema_modificado' => false,
            'huellas_anteriores_preservadas' => $huellasAntes, 'respaldo' => basename($respaldo), 'respaldo_sha256' => hash_file('sha256', $respaldo)];
        if (file_put_contents($privado.'/carga.json', json_encode($auditoria, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL) === false) {
            throw new RuntimeException('La carga se confirmó, pero falló el informe privado; no repita la carga. Ejecute --verificar.');
        }
    }

    if ($nombrar) {
        DB::transaction(function () use ($huellas, $anteriores, $privado, &$nombresActualizados): void {
            $registros = CohorteCognitivaLocal::limitarLote(Residente::query())->orderBy('cod_residente')->lockForUpdate()->get()->keyBy('numero_documento');
            if ($registros->count() !== 30) {
                throw new RuntimeException('No se identificó el lote íntegro por sus documentos y admisiones de entrenamiento.');
            }
            $otrasIdentidades = Residente::query()->whereIn('cod_residente', $anteriores)->get(['nombres', 'apellido_paterno', 'apellido_materno'])
                ->map(fn ($r) => mb_strtolower(trim(implode(' ', [$r->nombres, $r->apellido_paterno, $r->apellido_materno]))))->all();
            foreach (CohorteCognitivaInvestigacionSeeder::casos() as $caso) {
                if (in_array(mb_strtolower(implode(' ', $caso['identidad'])), $otrasIdentidades, true)) {
                    throw new RuntimeException('Un nombre propuesto coincide con un residente anterior; no se renombra el lote.');
                }
            }
            $originales = $huellas($anteriores);
            $fuentes = $huellas($registros->pluck('cod_residente')->all());
            unset($fuentes['residentes']);
            $respaldoNombres = $privado.'/nombres-antes-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
            if (file_put_contents($respaldoNombres, json_encode($registros->values()->map->only(['cod_residente', 'numero_documento',
                'nombres', 'apellido_paterno', 'apellido_materno'])->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL) === false) {
                throw new RuntimeException('No se pudo guardar la correspondencia anterior; no se cambia ningún nombre.');
            }
            foreach (CohorteCognitivaInvestigacionSeeder::casos() as $caso) {
                $r = $registros->get('COH30-'.$caso['caso']);
                $identidadAnterior = $r->only(['nombres', 'apellido_paterno', 'apellido_materno']);
                $otrosAtributos = array_diff_key($r->getRawOriginal(), $identidadAnterior);
                $r->fill($caso['identidad']);
                if (! $r->isDirty()) {
                    continue;
                }
                $r->saveOrFail();
                if ($otrosAtributos !== array_diff_key($r->fresh()->getRawOriginal(), $caso['identidad'])) {
                    throw new RuntimeException('El cambio afectó campos ajenos al nombre; se revierte la operación.');
                }
                $auditado = activity('cohorte_entrenamiento')->performedOn($r)->event('identidad_ficticia_renombrada')
                    ->withProperties(['origen' => 'CLI_LOCAL_AUTORIZADA', 'anterior' => $identidadAnterior, 'nuevo' => $caso['identidad']])
                    ->log('Nombres ficticios actualizados por solicitud de la propietaria; conserva fuentes, identidad y condición de entrenamiento.');
                if (! $auditado) {
                    throw new RuntimeException('No se confirmó la auditoría; se revierte el cambio de nombres.');
                }
                $nombresActualizados++;
            }
            $fuentesDespues = $huellas($registros->pluck('cod_residente')->all());
            unset($fuentesDespues['residentes']);
            if ($originales !== $huellas($anteriores) || $fuentes !== $fuentesDespues) {
                throw new RuntimeException('Se alteraron fuentes o residentes ajenos; se revierte el cambio de nombres.');
            }
        });
    }

    // Petición nueva: no depende de los objetos ni de la configuración en memoria del escritor.
    config(['sistema_experto.perfiles_instrumentales' => $perfilesAnteriores]);
    $actor = User::query()->where('correo', 'coh30.psicologia@example.invalid')->sole();
    $resultados = [];
    foreach (CohorteCognitivaInvestigacionSeeder::casos() as $caso) {
        $r = Residente::query()->where('numero_documento', 'COH30-'.$caso['caso'])->sole();
        if (! CohorteCognitivaLocal::esResidente($r)) {
            throw new RuntimeException('Identidad o procedencia del residente de entrenamiento incompatible.');
        }
        $dto = app(PrepararEvaluacionInstitucional::class)->preparar($actor, $r, CarbonImmutable::now());
        $salida = $dto->contenido['petersen']['contenido']['salida'];
        $resultados[] = ['caso' => $caso['caso'], 'cod_residente' => $r->getKey(), 'nombre' => $r->nombre_completo,
            'patron_calculado' => $salida['patron'], 'estado' => $salida['estado'],
            'dominios' => array_map(fn ($d) => $d['estado'], $salida['dominios']),
            'requisitos' => array_map(fn ($d) => $d['estado'], $salida['requisitos']),
            'ruta' => '/admin/psicologia/residente/'.$r->getKey().'/resultados-experto'];
    }
    $comparacion = ManifiestoCognitivo30::comparar(CohorteCognitivaInvestigacionSeeder::manifiesto(), $resultados);
    $informe = ['fecha' => now()->toIso8601String(), 'base_local' => $base, 'sintetico' => true, 'carga_nueva' => $existentes === 0,
        'residentes_anteriores' => count($anteriores), 'residentes_actuales' => Residente::count(),
        'comparacion' => $comparacion, 'casos' => $resultados];
    if (file_put_contents($privado.'/verificacion.json', json_encode($informe, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL) === false) {
        throw new RuntimeException('No se pudo guardar la verificación privada.');
    }
    if (! $comparacion['satisfactorio']) {
        throw new RuntimeException('La cohorte está cargada, pero hay discrepancias de inferencia. Consulte el informe privado; no se informa éxito.');
    }
    echo json_encode(['residentes_agregados' => $existentes === 0 ? 30 : 0, 'cohorte_disponible' => 30,
        'residentes_actuales' => $informe['residentes_actuales'], 'perfiles' => array_count_values(array_filter(array_column($resultados, 'patron_calculado'))),
        'discrepancias' => 0, 'nombres_actualizados' => $nombresActualizados,
        'comprobacion_originales_durante_carga' => $huellasAntes !== null ? 'PRESERVADOS' : 'NO_APLICA_SOLO_VERIFICACION',
        'listado' => 'http://127.0.0.1:8000/admin/psicologia/evaluaciones-asignadas?buscar=entrenamiento',
        'primer_resultado' => 'http://127.0.0.1:8000'.$resultados[0]['ruta']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
} finally {
    config(['sistema_experto.perfiles_instrumentales' => $perfilesAnteriores]);
    putenv($destinoAnterior === false ? 'RM_COHORTE30_DESTINO' : 'RM_COHORTE30_DESTINO='.$destinoAnterior);
    putenv($localAnterior === false ? 'RM_COHORTE30_CARGA_LOCAL' : 'RM_COHORTE30_CARGA_LOCAL='.$localAnterior);
    DB::select("select pg_advisory_unlock(hashtext('remembermind:cohorte30'))");
}
