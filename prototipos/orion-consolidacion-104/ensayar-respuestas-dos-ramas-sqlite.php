<?php

declare(strict_types=1);

// Ensayo aislado de representación: mismo origen, dos respuestas históricas tipadas.
// No instala ni modifica la BDD RememberMind.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require dirname(__DIR__, 2).'/tests/Unit/InstantaneaEvaluacionInstitucionalTest.php';

use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as Canonica;

function exigirRespuestas(bool $condicion, string $mensaje): void
{
    if (! $condicion) {
        throw new RuntimeException($mensaje);
    }
}

function rechazarRespuestas(PDO $pdo, string $sql): void
{
    try {
        $pdo->exec($sql);
    } catch (PDOException) {
        return;
    }

    throw new RuntimeException('Se aceptó una operación inválida.');
}

$dto = (new ReflectionMethod(new \Tests\Unit\InstantaneaEvaluacionInstitucionalTest(
    'test_ausencias_no_se_rellenan_durante_el_replay'), 'crear'))->invoke(
        new \Tests\Unit\InstantaneaEvaluacionInstitucionalTest('test_ausencias_no_se_rellenan_durante_el_replay'));
$instrumental = $dto->contenido['instrumental']['contenido']['capturas']['capturas'];
$valoraciones = $dto->contenido['petersen']['contenido']['valoraciones'];
$capturas = [];
foreach ($instrumental as $captura) {
    $capturas[$captura['cod_aplicacion']] = $captura;
}
$parejas = [];
foreach ($valoraciones as $valoracion) {
    foreach ($valoracion['soportes'] as $soporte) {
        $original = $soporte['datos_originales']['captura_original'] ?? null;
        if ($original !== null && isset($capturas[$original['cod_aplicacion']])) {
            $parejas[$original['cod_aplicacion']] = [
                'INSTRUMENTAL' => $capturas[$original['cod_aplicacion']]['respuestas_originales'],
                'PETERSEN' => $original['respuestas_originales'],
            ];
        }
    }
}
exigirRespuestas(count($parejas) === 5, 'Cambió el caso sintético compuesto.');

$pdo = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->exec(<<<'SQL'
CREATE TABLE evaluaciones(codigo TEXT PRIMARY KEY, cerrada INTEGER NOT NULL DEFAULT 0 CHECK(cerrada IN(0,1)));
CREATE TABLE aplicaciones_adquiridas(
 evaluacion TEXT NOT NULL REFERENCES evaluaciones(codigo), aplicacion TEXT NOT NULL,
 PRIMARY KEY(evaluacion,aplicacion)
);
CREATE TABLE referencias_rama(
 evaluacion TEXT NOT NULL, aplicacion TEXT NOT NULL,
 rama TEXT NOT NULL CHECK(rama IN('INSTRUMENTAL','PETERSEN')),
 PRIMARY KEY(evaluacion,aplicacion,rama),
 FOREIGN KEY(evaluacion,aplicacion) REFERENCES aplicaciones_adquiridas(evaluacion,aplicacion)
);
CREATE TABLE respuestas_adquiridas(
 evaluacion TEXT NOT NULL, aplicacion TEXT NOT NULL, rama TEXT NOT NULL,
 orden INTEGER NOT NULL CHECK(orden>=0), cod_respuesta TEXT NOT NULL,
 cod_pregunta TEXT NOT NULL, tipo_respuesta TEXT NOT NULL, coherente INTEGER NOT NULL CHECK(coherente IN(0,1)),
 mascara INTEGER NOT NULL CHECK(mascara BETWEEN 0 AND 127),
 numero_tipo TEXT NOT NULL CHECK(numero_tipo IN('AUSENTE','NULO','ENTERO','DECIMAL','TEXTO')),
 numero_lexema TEXT,
 logico INTEGER CHECK(logico IN(0,1)), valor_texto TEXT,
 cod_opcion TEXT, pregunta_actual TEXT, opcion_actual TEXT,
 PRIMARY KEY(evaluacion,aplicacion,rama,orden),
 UNIQUE(evaluacion,aplicacion,rama,cod_respuesta),
 FOREIGN KEY(evaluacion,aplicacion,rama) REFERENCES referencias_rama(evaluacion,aplicacion,rama),
 CHECK((numero_tipo IN('AUSENTE','NULO') AND numero_lexema IS NULL)
   OR (numero_tipo IN('ENTERO','DECIMAL','TEXTO') AND numero_lexema IS NOT NULL)),
 CHECK(((mascara & 1)!=0) = (numero_tipo!='AUSENTE')),
 CHECK((mascara & 2)!=0 OR logico IS NULL),
 CHECK((mascara & 4)!=0 OR valor_texto IS NULL),
 CHECK((mascara & 8)!=0 OR cod_opcion IS NULL),
 CHECK((mascara & 16)!=0 OR pregunta_actual IS NULL),
 CHECK((mascara & 32)!=0 OR opcion_actual IS NULL)
);
CREATE TRIGGER respuesta_cerrada BEFORE INSERT ON respuestas_adquiridas
WHEN EXISTS(SELECT 1 FROM evaluaciones WHERE codigo=NEW.evaluacion AND cerrada=1)
BEGIN SELECT RAISE(ABORT,'evaluación cerrada'); END;
CREATE TRIGGER respuesta_inmutable BEFORE UPDATE ON respuestas_adquiridas
BEGIN SELECT RAISE(ABORT,'respuesta inmutable'); END;
CREATE TRIGGER respuesta_no_borrable BEFORE DELETE ON respuestas_adquiridas
BEGIN SELECT RAISE(ABORT,'respuesta inmutable'); END;
SQL);

$evaluacion = 'E-RESPUESTAS-COMPUESTAS';
$pdo->beginTransaction();
try {
    $pdo->prepare('INSERT INTO evaluaciones(codigo) VALUES (?)')->execute([$evaluacion]);
    $insertarAplicacion = $pdo->prepare('INSERT INTO aplicaciones_adquiridas VALUES (?,?)');
    $insertarReferencia = $pdo->prepare('INSERT INTO referencias_rama VALUES (?,?,?)');
    $insertarRespuesta = $pdo->prepare('INSERT INTO respuestas_adquiridas VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($parejas as $aplicacion => $ramas) {
        $insertarAplicacion->execute([$evaluacion,$aplicacion]);
        foreach ($ramas as $rama => $respuestas) {
            $insertarReferencia->execute([$evaluacion,$aplicacion,$rama]);
            foreach ($respuestas as $orden => $respuesta) {
                $permitidos = ['cod_respuesta','cod_aplicacion','cod_pregunta','tipo_respuesta','coherente',
                    'valor_numero','valor_logico','valor_texto','cod_opcion','pregunta_actual','opcion_actual'];
                exigirRespuestas(array_diff(array_keys($respuesta),$permitidos)===[], 'Campo de respuesta sin representar.');
                exigirRespuestas($respuesta['cod_aplicacion']===$aplicacion, 'Aplicación distinta en respuesta.');
                $opcionales = ['valor_numero','valor_logico','valor_texto','cod_opcion','pregunta_actual','opcion_actual'];
                $mascara = 0;
                foreach ($opcionales as $indice => $campo) {
                    if (array_key_exists($campo,$respuesta)) {
                        $mascara |= 1 << $indice;
                    }
                }
                $numero = $respuesta['valor_numero'] ?? null;
                $tipo = ! array_key_exists('valor_numero',$respuesta) ? 'AUSENTE' : match (true) {
                    $numero === null => 'NULO', is_int($numero) => 'ENTERO',
                    is_float($numero) => 'DECIMAL', is_string($numero) => 'TEXTO',
                    default => throw new RuntimeException('Tipo numérico desconocido.'),
                };
                exigirRespuestas(! is_array($respuesta['pregunta_actual'] ?? null)
                    && ! is_array($respuesta['opcion_actual'] ?? null), 'La muestra exige columnas adicionales de pregunta/opción.');
                $insertarRespuesta->execute([$evaluacion,$aplicacion,$rama,$orden,$respuesta['cod_respuesta'],
                    $respuesta['cod_pregunta'],$respuesta['tipo_respuesta'],(int)$respuesta['coherente'],$mascara,
                    $tipo,$numero === null ? null : (string)$numero,
                    isset($respuesta['valor_logico']) ? (int)$respuesta['valor_logico'] : null,
                    $respuesta['valor_texto'] ?? null,$respuesta['cod_opcion'] ?? null,
                    $respuesta['pregunta_actual'] ?? null,$respuesta['opcion_actual'] ?? null]);
            }
        }
    }
    $pdo->prepare('UPDATE evaluaciones SET cerrada=1 WHERE codigo=?')->execute([$evaluacion]);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}

$leer = $pdo->prepare('SELECT * FROM respuestas_adquiridas WHERE evaluacion=? AND aplicacion=? AND rama=? ORDER BY orden');
$filas = 0;
foreach ($parejas as $aplicacion => $ramas) {
    foreach ($ramas as $rama => $original) {
        $leer->execute([$evaluacion,$aplicacion,$rama]);
        $reconstruidas = [];
        foreach ($leer->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $r = ['cod_respuesta'=>$fila['cod_respuesta'],'cod_aplicacion'=>$aplicacion,
                'cod_pregunta'=>$fila['cod_pregunta'],'tipo_respuesta'=>$fila['tipo_respuesta'],
                'coherente'=>(bool)$fila['coherente']];
            $opcionales = ['valor_numero','valor_logico','valor_texto','cod_opcion','pregunta_actual','opcion_actual'];
            foreach ($opcionales as $indice=>$campo) {
                if (((int)$fila['mascara'] & (1 << $indice))===0) {
                    continue;
                }
                $r[$campo] = match ($campo) {
                    'valor_numero' => match ($fila['numero_tipo']) {
                        'NULO' => null, 'ENTERO' => (int)$fila['numero_lexema'],
                        'DECIMAL' => (float)$fila['numero_lexema'], 'TEXTO' => $fila['numero_lexema'],
                        default => throw new RuntimeException('Tipo numérico inválido al leer.'),
                    },
                    'valor_logico' => $fila['logico'] === null ? null : (bool)$fila['logico'],
                    'valor_texto' => $fila['valor_texto'], 'cod_opcion' => $fila['cod_opcion'],
                    'pregunta_actual' => $fila['pregunta_actual'], 'opcion_actual' => $fila['opcion_actual'],
                };
            }
            $reconstruidas[] = $r;
            $filas++;
        }
        exigirRespuestas(Canonica::ordenar($original)===Canonica::ordenar($reconstruidas),
            'La rama perdió el tipo, presencia u orden de respuestas: '.$aplicacion.' / '.$rama);
    }
}
$primera = array_key_first($parejas);
exigirRespuestas($parejas[$primera]['INSTRUMENTAL'][0]['valor_numero']==='6.0000'
    && $parejas[$primera]['PETERSEN'][0]['valor_numero']===6,
    'La muestra dejó de probar entero frente a decimal canonizado.');
rechazarRespuestas($pdo, "INSERT INTO respuestas_adquiridas VALUES ('$evaluacion','$primera','PETERSEN',99,'TARDE','Q','NUMERICO',1,0,'AUSENTE',NULL,NULL,NULL,NULL,NULL,NULL)");
rechazarRespuestas($pdo, "UPDATE respuestas_adquiridas SET numero_lexema='0' WHERE evaluacion='$evaluacion'");
rechazarRespuestas($pdo, "DELETE FROM respuestas_adquiridas WHERE evaluacion='$evaluacion'");

echo "PASS respuestas de dos ramas: $filas filas tipadas; tipos, presencia, orden y cierre conservados.\n";
echo "LÍMITE: solo respuestas del fixture sintético. No representa otras colecciones, PostgreSQL ni DDL institucional.\n";
