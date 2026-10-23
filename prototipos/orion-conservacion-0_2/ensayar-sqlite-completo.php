<?php

declare(strict_types=1);

/** Referencia F02 en SQLite :memory:, opcionalmente sobre las migraciones reales. No es DDL institucional ni candidato <=110. */

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use OrionFase02\CasoCanonico;
use OrionFase02\EntradasCompuestas;
use OrionFase02\ProyeccionTipada;
use OrionFase02\VariantesCanonicas;

$institucional = in_array('--base-institucional', $argv ?? [], true);
if ($institucional) {
    // Antes del bootstrap se anula toda posibilidad de abrir la base configurada por el proyecto.
    foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => '',
        'RM_TEST_PROFILE' => 'sqlite', 'APP_KEY' => 'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8='] as $clave => $valor) {
        putenv($clave.'='.$valor);
        $_ENV[$clave] = $valor;
        $_SERVER[$clave] = $valor;
    }
}
$raiz = dirname(__DIR__, 2);
require $raiz.'/vendor/autoload.php';
foreach (['CasoCanonico', 'EntradasCompuestas', 'ProyeccionTipada', 'VariantesCanonicas'] as $clase) {
    require_once __DIR__.'/'.$clase.'.php';
}

if ($institucional) {
    $app = require $raiz.'/bootstrap/app.php';
    if ($app->configurationIsCached()) {
        throw new RuntimeException('El ensayo institucional exige configuración sin caché.');
    }
    $app->make(Kernel::class)->bootstrap();
    if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:'
        || DB::connection()->getDatabaseName() !== ':memory:') {
        throw new RuntimeException('El ensayo institucional solo admite SQLite en memoria.');
    }
    if (Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]) !== 0) {
        throw new RuntimeException('No se pudo migrar la copia desechable: '.Artisan::output());
    }
    $db = DB::connection()->getPdo();
} else {
    $db = new PDO('sqlite::memory:');
}
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys=ON');
if ($db->query('PRAGMA database_list')->fetch(PDO::FETCH_ASSOC)['file'] !== '') {
    throw new RuntimeException('El ensayo solo admite SQLite en memoria.');
}
$mapa = require __DIR__.'/mapa-cerrado.php';
// B01 comparte grano y propietario entre ramas; el rol de colección conserva la rama.
// Las demás parejas no se fusionan: poseen propietarios físicos diferentes.
$mapa['modelos']['m015']['tabla'] = $mapa['modelos']['m008']['tabla'];
$mapa['modelos']['m016']['tabla'] = $mapa['modelos']['m009']['tabla'];
$cabecera = $institucional ? 'evaluaciones_expertas' : 'evaluaciones_compuestas';
$huellaColumna = $institucional ? 'huella_compuesta' : 'huella';
$estadoColumna = $institucional ? 'estado_conservacion' : 'estado';
if ($institucional) {
    $base = (int) $db->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchColumn();
    if ($base !== 120) {
        throw new RuntimeException("La copia desechable no reproduce las 120 tablas instaladas: $base.");
    }
    $db->exec('ALTER TABLE evaluaciones_expertas ADD COLUMN huella_compuesta TEXT');
    $db->exec("ALTER TABLE evaluaciones_expertas ADD COLUMN estado_conservacion TEXT CHECK(estado_conservacion IN ('ABIERTA','CERRADA'))");
} else {
    $db->exec("CREATE TABLE evaluaciones_compuestas (cod_evaluacion_experta TEXT PRIMARY KEY, huella TEXT NOT NULL, estado TEXT NOT NULL CHECK(estado IN ('ABIERTA','CERRADA')))");
}
$columnasCabecera = $db->query("PRAGMA table_info($cabecera)")->fetchAll(PDO::FETCH_ASSOC);
$camposInmutables = array_map(static fn (array $columna): string => 'NEW.'.$columna['name'].' IS NOT OLD.'.$columna['name'],
    array_filter($columnasCabecera, static fn (array $columna): bool => $columna['name'] !== $estadoColumna));
$cambioIdentidad = implode(' OR ', $camposInmutables);
$db->exec("CREATE TRIGGER evaluaciones_cierre BEFORE UPDATE ON $cabecera
    WHEN OLD.$estadoColumna IS NOT NULL AND (OLD.$estadoColumna<>'ABIERTA' OR NEW.$estadoColumna<>'CERRADA'
      OR $cambioIdentidad)
    BEGIN SELECT RAISE(ABORT,'Transición histórica inválida'); END");
$db->exec("CREATE TRIGGER evaluaciones_sin_borrado BEFORE DELETE ON $cabecera
    WHEN OLD.$estadoColumna IS NOT NULL BEGIN SELECT RAISE(ABORT,'No se elimina historia'); END");

$columnas = static function (array $nodo, string $ruta, array &$resultado) use ($mapa, &$columnas): void {
    $nombre = $mapa['columnas'][$ruta];
    if ($nodo['clase'] === 'escalar') {
        foreach (['_presente INTEGER NOT NULL CHECK('.$nombre.'_presente IN (0,1))', '_tipo TEXT NOT NULL CHECK('.$nombre."_tipo IN ('NULL','STRING','INT','FLOAT','BOOL'))",
            '_texto TEXT', '_entero INTEGER', '_real_ieee754 TEXT', '_logico INTEGER'] as $sufijo) {
            $resultado[] = $nombre.$sufijo;
        }

        return;
    }
    $resultado[] = $nombre.'_estado TEXT NOT NULL CHECK('.$nombre."_estado IN ('AUSENTE','NULL','PRESENTE'))";
    if ($nodo['clase'] === 'coleccion') {
        $resultado[] = $nombre.'_cantidad INTEGER NOT NULL CHECK('.$nombre.'_cantidad>=0)';

        return;
    }
    foreach ($nodo['campos'] as $campo => $hijo) {
        $columnas($hijo, $ruta.'/'.$campo, $resultado);
    }
};

$tablasCreadas = [];
foreach ($mapa['modelos'] as $id => $modelo) {
    $def = ['cod_fila TEXT PRIMARY KEY', 'cod_evaluacion_experta TEXT NOT NULL'];
    $restricciones = [
        'UNIQUE(cod_evaluacion_experta,cod_fila)',
        'FOREIGN KEY(cod_evaluacion_experta) REFERENCES '.$cabecera.'(cod_evaluacion_experta) ON DELETE RESTRICT'];
    if ($modelo['padre'] !== null) {
        $padre = $mapa['modelos'][$modelo['padre']]['tabla'];
        array_push($def, 'cod_propietario TEXT NOT NULL', 'orden INTEGER NOT NULL CHECK(orden>=0)',
            'coleccion TEXT NOT NULL', "clave_tipo TEXT NOT NULL CHECK(clave_tipo IN ('NULL','STRING','INT'))",
            'clave_texto TEXT');
        array_push($restricciones, "CHECK((clave_tipo='NULL')=(clave_texto IS NULL))",
            'UNIQUE(cod_propietario,coleccion,orden)',
            'FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES '.$padre.'(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT');
    }
    $columnas($modelo['estructura'], '', $def);
    $ddl = implode(',', array_merge($def, $restricciones));
    if (isset($tablasCreadas[$modelo['tabla']])) {
        if ($tablasCreadas[$modelo['tabla']] !== $ddl) {
            throw new RuntimeException('Fusión B01 incompatible con el grano físico: '.$id);
        }

        continue;
    }
    $tablasCreadas[$modelo['tabla']] = $ddl;
    $db->exec('CREATE TABLE '.$modelo['tabla'].' ('.$ddl.')');
    foreach (['INSERT' => 'NEW', 'UPDATE' => 'OLD', 'DELETE' => 'OLD'] as $operacion => $referencia) {
        $db->exec('CREATE TRIGGER '.$modelo['tabla'].'_'.strtolower($operacion).'_cierre BEFORE '.$operacion.' ON '.$modelo['tabla']
            .' WHEN (SELECT '.$estadoColumna.' FROM '.$cabecera.' WHERE cod_evaluacion_experta='.$referencia.'.cod_evaluacion_experta)=\'CERRADA\''
            ." BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END");
    }
    if ($modelo['padre'] !== null) {
        $db->exec('CREATE UNIQUE INDEX '.$modelo['tabla'].'_mapa ON '.$modelo['tabla']."(cod_propietario,coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL'");
    } else {
        $db->exec('CREATE UNIQUE INDEX '.$modelo['tabla'].'_raiz_unica ON '.$modelo['tabla'].'(cod_evaluacion_experta)');
    }
}

$temporal = sys_get_temp_dir().'/orion-f02-sqlite-'.bin2hex(random_bytes(8));
mkdir($temporal, 0700, true);
try {
    $zip = $temporal.'/motor.zip';
    $release = (new ReleaseMotorInstrumental)->crear($raiz, $zip, str_repeat('a', 40));
    $fuente = json_decode(file_get_contents($raiz.'/app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'), true, flags: JSON_THROW_ON_ERROR);
    $proyeccion = new ProyeccionTipada($db, $mapa, '');
    $casos = ['PET01' => (new CasoCanonico)->crear(['COG-MEM'], identidad: 'PET01'),
        'SIN_C03' => (new CasoCanonico)->crear(sinC03: true, identidad: 'SIN_C03')];
    $variantes = VariantesCanonicas::casos();
    foreach (['SIN_GOBIERNO', 'AUSENCIAS_NULL_ORDEN', 'SEIS_FUENTES_Y_DOCUMENTOS', 'VINCULO_INTERPRETATIVO'] as $nombre) {
        $casos[$nombre] = $variantes[$nombre];
    }
    $usuario = $personal = $residente = null;
    if ($institucional) {
        $usuario = User::factory()->create(['cod_usuario' => 'USUARIO-TEST', 'estado' => 'ACTIVO']);
        $personal = Personal::create(['cod_personal' => 'AUTOR-TEST', 'cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'F02-INST-1', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $residente = Residente::factory()->create(['cod_residente' => 'RES-TEST', 'estado' => 'ADMITIDO']);
        DB::table('versiones_modelo_experto')->insert(['cod_version_modelo' => 'VERSION-TEST', 'codigo_version' => 'F02-SINTETICO',
            'nombre' => 'Ensayo de conservación', 'estado' => 'INACTIVO', 'fecha_hora_creacion' => now(),
            'cod_usuario_creacion' => $usuario->getKey()]);
    }
    $insertarCabecera = static function (string $codigo, string $huella) use ($db, $institucional, $personal, $residente): void {
        if ($institucional) {
            $s = $db->prepare("INSERT INTO evaluaciones_expertas(cod_evaluacion_experta,cod_residente,cod_version_modelo,cod_personal_solicitante,
                origen_activacion,fecha_hora_inicio,estado_ejecucion,huella_compuesta,estado_conservacion)
                VALUES (?,?,?,?,?,?,?,?, 'ABIERTA')");
            $s->execute([$codigo, $residente->getKey(), 'VERSION-TEST', $personal->getKey(), 'PRUEBA_TECNICA',
                now()->format('Y-m-d H:i:s'), 'PREPARADA', $huella]);

            return;
        }
        $db->prepare("INSERT INTO evaluaciones_compuestas(cod_evaluacion_experta,huella,estado) VALUES (?,?,'ABIERTA')")
            ->execute([$codigo, $huella]);
    };
    $primeraFila = $primeraRaiz = null;
    foreach ($casos as $nombre => $sinRelease) {
        $dto = $sinRelease->vincularRelease($zip, $release['huella']);
        $entrada = EntradasCompuestas::proyectar($dto, $fuente);
        $contexto = $dto->contenido['instrumental']['contenido']['contexto'];
        $codigo = $contexto['evaluacion'];
        if ($institucional && ($contexto['residente'] !== $residente->getKey()
            || $contexto['version'] !== 'VERSION-TEST' || $contexto['usuario_ejecucion'] !== $usuario->getKey())) {
            throw new RuntimeException("La cabecera institucional no coincide con el contexto original de $nombre.");
        }
        $db->beginTransaction();
        try {
            $insertarCabecera($codigo, $dto->huella);
            $filas = $proyeccion->proyectar($entrada, $codigo);
            $proyeccion->insertar($filas);
            $primeraRaiz ??= $filas[0];
            $primeraFila ??= $filas[1];
            $recuperada = $proyeccion->recuperar($codigo);
            if (CapturaInstrumentalCanonica::ordenar($recuperada) !== CapturaInstrumentalCanonica::ordenar($entrada)) {
                throw new RuntimeException("La entrada SQL de $nombre no coincide con las 51 colecciones.");
            }
            $db->prepare("UPDATE $cabecera SET $estadoColumna='CERRADA' WHERE cod_evaluacion_experta=?")->execute([$codigo]);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
        $replay = EntradasCompuestas::reproducir($proyeccion->recuperar($codigo), $fuente, $zip, $release['huella']);
        if ($replay->contenido !== $dto->contenido || $replay->huella !== $dto->huella) {
            throw new RuntimeException("Replay SQLite divergente: $nombre.");
        }
        $actual = $db->prepare("SELECT $huellaColumna AS huella,$estadoColumna AS estado FROM $cabecera WHERE cod_evaluacion_experta=?");
        $actual->execute([$codigo]);
        $guardada = $actual->fetch(PDO::FETCH_ASSOC);
        if ($guardada !== ['huella' => $dto->huella, 'estado' => 'CERRADA']
            || CapturaInstrumentalCanonica::ordenar($proyeccion->recuperar($codigo)) !== CapturaInstrumentalCanonica::ordenar($entrada)) {
            throw new RuntimeException("Relectura histórica divergente: $nombre.");
        }
    }
    $cerrada = $primeraFila;
    $cerrada['fila']['cod_fila'] = bin2hex(random_bytes(10));
    try {
        $proyeccion->insertar([$cerrada]);
        throw new RuntimeException('La historia cerrada aceptó una fila tardía.');
    } catch (PDOException $e) {
        if (! str_contains($e->getMessage(), 'Evaluación cerrada')) {
            throw $e;
        }
    }
    try {
        $db->prepare("UPDATE $cabecera SET $huellaColumna='ALTERADA' WHERE cod_evaluacion_experta=?")
            ->execute([$cerrada['fila']['cod_evaluacion_experta']]);
        throw new RuntimeException('La cabecera cerrada pudo alterarse.');
    } catch (PDOException $e) {
        if (! str_contains($e->getMessage(), 'Transición histórica inválida')) {
            throw $e;
        }
    }
    $db->beginTransaction();
    $insertarCabecera('ROLLBACK', str_repeat('0', 64));
    $db->rollBack();
    if ((int) $db->query("SELECT count(*) FROM $cabecera WHERE cod_evaluacion_experta='ROLLBACK'")->fetchColumn() !== 0) {
        throw new RuntimeException('Rollback parcial dejó cabecera.');
    }
    $db->beginTransaction();
    try {
        $insertarCabecera('CIERRE_MUTABLE', str_repeat('2', 64));
        $campoAlterado = $institucional ? 'origen_activacion' : $huellaColumna;
        $db->prepare("UPDATE $cabecera SET $estadoColumna='CERRADA', $campoAlterado='ALTERADO'
            WHERE cod_evaluacion_experta='CIERRE_MUTABLE'")->execute();
        throw new RuntimeException('El cierre permitió modificar la identidad histórica en el mismo UPDATE.');
    } catch (PDOException $e) {
        if (! str_contains($e->getMessage(), 'Transición histórica inválida')) {
            throw $e;
        }
    } finally {
        $db->rollBack();
    }
    $db->beginTransaction();
    try {
        $insertarCabecera('IDEMPOTENTE', str_repeat('3', 64));
        $insertarCabecera('IDEMPOTENTE', str_repeat('3', 64));
        throw new RuntimeException('La misma identidad de evaluación se guardó dos veces.');
    } catch (PDOException $e) {
        if (! str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
            throw $e;
        }
    } finally {
        $db->rollBack();
    }
    if ((int) $db->query("SELECT count(*) FROM $cabecera WHERE cod_evaluacion_experta IN ('CIERRE_MUTABLE','IDEMPOTENTE')")->fetchColumn() !== 0) {
        throw new RuntimeException('Una transacción rechazada dejó una evaluación parcial.');
    }
    $db->beginTransaction();
    try {
        $insertarCabecera('DOBLE_RAIZ', str_repeat('4', 64));
        $raizDuplicada = $primeraRaiz;
        $raizDuplicada['fila']['cod_evaluacion_experta'] = 'DOBLE_RAIZ';
        $raizDuplicada['fila']['cod_fila'] = bin2hex(random_bytes(10));
        $proyeccion->insertar([$raizDuplicada]);
        $raizDuplicada['fila']['cod_fila'] = bin2hex(random_bytes(10));
        $proyeccion->insertar([$raizDuplicada]);
        throw new RuntimeException('La evaluación aceptó dos raíces compuestas.');
    } catch (PDOException $e) {
        if (! str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
            throw $e;
        }
    } finally {
        $db->rollBack();
    }
    $filasAntes = (int) $db->query('SELECT count(*) FROM '.$primeraFila['tabla'])->fetchColumn();
    $db->beginTransaction();
    try {
        $insertarCabecera('PARCIAL', str_repeat('1', 64));
        $hijaAislada = $primeraFila;
        $hijaAislada['fila']['cod_fila'] = bin2hex(random_bytes(10));
        $hijaAislada['fila']['cod_evaluacion_experta'] = 'PARCIAL';
        $hijaAislada['fila']['cod_propietario'] = 'PROPIETARIO-AJENO';
        $proyeccion->insertar([$hijaAislada]);
        throw new RuntimeException('Una fila hija sin su propietario fue aceptada.');
    } catch (PDOException $e) {
        $db->rollBack();
        if (! str_contains($e->getMessage(), 'FOREIGN KEY constraint failed')) {
            throw $e;
        }
    }
    if ((int) $db->query("SELECT count(*) FROM $cabecera WHERE cod_evaluacion_experta='PARCIAL'")->fetchColumn() !== 0
        || (int) $db->query('SELECT count(*) FROM '.$primeraFila['tabla'])->fetchColumn() !== $filasAntes) {
        throw new RuntimeException('El fallo referencial dejó escritura parcial.');
    }
    $tablas = (int) $db->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchColumn();
    if ($tablas !== count($tablasCreadas) + ($institucional ? 120 : 1)) {
        throw new RuntimeException('Inventario F02 SQLite incompleto.');
    }
    echo 'PASS F02 SQLite'.($institucional ? ' sobre 104 tablas instaladas' : ' con fusión B01').': '.count($casos)
        ." casos, $tablas tablas físicas; 39 modelos y 51 colecciones; entrada, DTO y huella recuperados/reproducidos con release temporal; cierre, FK y rollback. NO cumple el máximo 110.\n";
} finally {
    foreach (glob($temporal.'/*') ?: [] as $archivo) {
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }
    rmdir($temporal);
}
