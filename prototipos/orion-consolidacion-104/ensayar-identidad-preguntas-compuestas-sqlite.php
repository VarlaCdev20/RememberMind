<?php

declare(strict_types=1);

// Ensayo aislado de B02: una aplicación original citada en ambas ramas.
// Solo datos sintéticos y SQLite en memoria; no modifica la BDD institucional.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require dirname(__DIR__, 2).'/tests/Unit/InstantaneaEvaluacionInstitucionalTest.php';

use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as Canonica;

function exigirIdentidad(bool $condicion, string $mensaje): void
{
    if (! $condicion) {
        throw new RuntimeException($mensaje);
    }
}

function debeRechazarIdentidad(PDO $pdo, string $sql): void
{
    try {
        $pdo->exec($sql);
    } catch (PDOException) {
        return;
    }

    throw new RuntimeException('Se aceptó una referencia o escritura inválida: '.$sql);
}

$test = new \Tests\Unit\InstantaneaEvaluacionInstitucionalTest('test_ausencias_no_se_rellenan_durante_el_replay');
$dto = (new ReflectionMethod($test, 'crear'))->invoke($test, ['COG-MEM'], false, false, true);
$instrumental = $dto->contenido['instrumental']['contenido'];
$petersen = $dto->contenido['petersen']['contenido'];

$aplicaciones = [];
foreach ($instrumental['capturas']['capturas'] as $captura) {
    $aplicaciones[$captura['cod_aplicacion']] = $captura;
}
$referenciasPetersen = [];
foreach ($petersen['valoraciones'] as $valoracion) {
    foreach ($valoracion['soportes'] as $soporte) {
        $captura = $soporte['datos_originales']['captura_original'] ?? null;
        if ($captura !== null && isset($aplicaciones[$captura['cod_aplicacion']])) {
            $adquirida = $aplicaciones[$captura['cod_aplicacion']];
            foreach (['cod_aplicacion','cod_residente','cod_instrumento','cod_personal','fecha_hora'] as $campo) {
                exigirIdentidad($adquirida[$campo] === $captura[$campo],
                    'La identidad original discrepa entre ramas: '.$campo);
            }
            exigirIdentidad(Canonica::ordenar($adquirida['preguntas_declarativas_originales'])
                === Canonica::ordenar($captura['preguntas_declarativas_originales']),
                'Las preguntas declarativas originales discrepan entre ramas.');
            // La rama instrumental canoniza decimales; Petersen conserva la forma capturada.
            // Compartir identidad no autoriza a colapsar ambas representaciones históricas.
            exigirIdentidad(Canonica::ordenar($adquirida['respuestas_originales'])
                !== Canonica::ordenar($captura['respuestas_originales']),
                'El caso ya no ejercita la divergencia de representación entre ramas.');
            $referenciasPetersen[] = [$valoracion['elemento'], $captura['cod_aplicacion']];
        }
    }
}
exigirIdentidad($referenciasPetersen !== [], 'El DTO compuesto ya no comparte aplicaciones originales.');
foreach ($aplicaciones as $captura) {
    exigirIdentidad(count($captura['preguntas_declarativas_originales']) === 1,
        'El DTO compuesto ya no incluye una pregunta sin respuesta por aplicación.');
    $codPregunta = array_values($captura['preguntas_declarativas_originales'])[0]['cod_pregunta'];
    exigirIdentidad(! in_array($codPregunta, array_column($captura['respuestas_originales'], 'cod_pregunta'), true),
        'La pregunta declarativa ya tiene una respuesta.');
}

$pdo = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->exec(<<<'SQL'
CREATE TABLE evaluaciones (
 codigo TEXT PRIMARY KEY, residente TEXT NOT NULL, cerrada INTEGER NOT NULL DEFAULT 0 CHECK(cerrada IN (0,1))
);
CREATE TABLE aplicaciones_adquiridas (
 evaluacion TEXT NOT NULL, residente TEXT NOT NULL, aplicacion TEXT NOT NULL,
 instrumento TEXT NOT NULL, orden INTEGER NOT NULL,
 PRIMARY KEY(evaluacion,aplicacion),
 UNIQUE(evaluacion,residente,aplicacion), UNIQUE(evaluacion,orden),
 FOREIGN KEY(evaluacion) REFERENCES evaluaciones(codigo)
);
CREATE TABLE referencias_rama (
 evaluacion TEXT NOT NULL, residente TEXT NOT NULL,
 rama TEXT NOT NULL CHECK(rama IN ('INSTRUMENTAL','PETERSEN')),
 juicio TEXT NOT NULL, aplicacion TEXT NOT NULL,
 PRIMARY KEY(evaluacion,rama,juicio,aplicacion),
 FOREIGN KEY(evaluacion,residente,aplicacion)
  REFERENCES aplicaciones_adquiridas(evaluacion,residente,aplicacion)
);
CREATE TABLE originales_pregunta (
 pregunta TEXT NOT NULL, instrumento TEXT NOT NULL,
 PRIMARY KEY(pregunta,instrumento)
);
CREATE TABLE entradas_perfil_instrumental (
 evaluacion TEXT NOT NULL, aplicacion TEXT NOT NULL, instrumento TEXT NOT NULL,
 orden INTEGER NOT NULL CHECK(orden>=0), tipo_clave TEXT NOT NULL CHECK(tipo_clave IN ('TEXTO','ENTERO')),
 clave TEXT NOT NULL, pregunta TEXT NOT NULL,
 mascara INTEGER NOT NULL CHECK(mascara BETWEEN 0 AND 7),
 enunciado TEXT, tipo_respuesta TEXT, estado TEXT,
 PRIMARY KEY(evaluacion,aplicacion,tipo_clave,clave),
 UNIQUE(evaluacion,aplicacion,orden),
 FOREIGN KEY(evaluacion,aplicacion) REFERENCES aplicaciones_adquiridas(evaluacion,aplicacion),
 FOREIGN KEY(pregunta,instrumento) REFERENCES originales_pregunta(pregunta,instrumento),
 CHECK((mascara & 1)!=0 OR enunciado IS NULL),
 CHECK((mascara & 2)!=0 OR tipo_respuesta IS NULL),
 CHECK((mascara & 4)!=0 OR estado IS NULL)
);
CREATE TRIGGER aplicacion_mismo_residente BEFORE INSERT ON aplicaciones_adquiridas
WHEN NOT EXISTS(SELECT 1 FROM evaluaciones e WHERE e.codigo=NEW.evaluacion AND e.residente=NEW.residente AND e.cerrada=0)
BEGIN SELECT RAISE(ABORT,'residente o estado inválido'); END;
CREATE TRIGGER referencia_cerrada BEFORE INSERT ON referencias_rama
WHEN EXISTS(SELECT 1 FROM evaluaciones e WHERE e.codigo=NEW.evaluacion AND e.cerrada=1)
BEGIN SELECT RAISE(ABORT,'evaluación cerrada'); END;
CREATE TRIGGER aplicacion_inmutable BEFORE UPDATE ON aplicaciones_adquiridas
BEGIN SELECT RAISE(ABORT,'aplicación inmutable'); END;
CREATE TRIGGER aplicacion_no_borrable BEFORE DELETE ON aplicaciones_adquiridas
BEGIN SELECT RAISE(ABORT,'aplicación inmutable'); END;
CREATE TRIGGER referencia_inmutable BEFORE UPDATE ON referencias_rama
BEGIN SELECT RAISE(ABORT,'referencia inmutable'); END;
CREATE TRIGGER referencia_no_borrable BEFORE DELETE ON referencias_rama
BEGIN SELECT RAISE(ABORT,'referencia inmutable'); END;
CREATE TRIGGER pregunta_mismo_instrumento BEFORE INSERT ON entradas_perfil_instrumental
WHEN NOT EXISTS(SELECT 1 FROM aplicaciones_adquiridas a WHERE a.evaluacion=NEW.evaluacion
 AND a.aplicacion=NEW.aplicacion AND a.instrumento=NEW.instrumento)
BEGIN SELECT RAISE(ABORT,'instrumento ajeno'); END;
CREATE TRIGGER pregunta_cerrada BEFORE INSERT ON entradas_perfil_instrumental
WHEN EXISTS(SELECT 1 FROM evaluaciones e WHERE e.codigo=NEW.evaluacion AND e.cerrada=1)
BEGIN SELECT RAISE(ABORT,'evaluación cerrada'); END;
CREATE TRIGGER pregunta_inmutable BEFORE UPDATE ON entradas_perfil_instrumental
BEGIN SELECT RAISE(ABORT,'pregunta inmutable'); END;
CREATE TRIGGER pregunta_no_borrable BEFORE DELETE ON entradas_perfil_instrumental
BEGIN SELECT RAISE(ABORT,'pregunta inmutable'); END;
SQL);

$evaluacion = 'E-COMPUESTA-B02';
$residente = $instrumental['contexto']['residente'];
$pdo->beginTransaction();
try {
    $pdo->prepare('INSERT INTO evaluaciones(codigo,residente) VALUES (?,?)')->execute([$evaluacion,$residente]);
    $guardarAplicacion = $pdo->prepare('INSERT INTO aplicaciones_adquiridas VALUES (?,?,?,?,?)');
    $guardarReferencia = $pdo->prepare('INSERT INTO referencias_rama VALUES (?,?,?,?,?)');
    foreach (array_values($aplicaciones) as $orden => $captura) {
        $guardarAplicacion->execute([$evaluacion,$residente,$captura['cod_aplicacion'],$captura['cod_instrumento'],$orden]);
        $guardarReferencia->execute([$evaluacion,$residente,'INSTRUMENTAL','CAPTURA-'.$orden,$captura['cod_aplicacion']]);
    }
    foreach ($referenciasPetersen as [$juicio,$aplicacion]) {
        $guardarReferencia->execute([$evaluacion,$residente,'PETERSEN',$juicio,$aplicacion]);
    }

    // Preguntas sin respuesta del DTO compuesto verificado, no inyectadas tras su emisión.
    $aplicacionComun = $referenciasPetersen[0][1];
    $instrumentoComun = $aplicaciones[$aplicacionComun]['cod_instrumento'];
    $guardarOriginal = $pdo->prepare('INSERT INTO originales_pregunta VALUES (?,?)');
    $guardarPregunta = $pdo->prepare('INSERT INTO entradas_perfil_instrumental VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($aplicaciones as $aplicacion => $captura) {
        $orden = 0;
        foreach ($captura['preguntas_declarativas_originales'] as $clave => $pregunta) {
            exigirIdentidad(array_diff(array_keys($pregunta),
                ['cod_pregunta','cod_instrumento','enunciado','tipo_respuesta','estado']) === [],
                'El fixture incluye metadatos que este ensayo aún no representa.');
            $guardarOriginal->execute([$pregunta['cod_pregunta'],$pregunta['cod_instrumento']]);
            $mascara = (array_key_exists('enunciado',$pregunta) ? 1 : 0)
                | (array_key_exists('tipo_respuesta',$pregunta) ? 2 : 0)
                | (array_key_exists('estado',$pregunta) ? 4 : 0);
            $guardarPregunta->execute([$evaluacion,$aplicacion,$captura['cod_instrumento'],$orden,
                is_int($clave) ? 'ENTERO' : 'TEXTO',(string)$clave,
                $pregunta['cod_pregunta'],$mascara,$pregunta['enunciado'] ?? null,$pregunta['tipo_respuesta'] ?? null,
                $pregunta['estado'] ?? null]);
            $orden++;
        }
    }
    $primera = array_values($aplicaciones[$aplicacionComun]['preguntas_declarativas_originales'])[0];
    $clavePrimera = array_key_first($aplicaciones[$aplicacionComun]['preguntas_declarativas_originales']);
    $preguntaComun = $primera['cod_pregunta'];
    debeRechazarIdentidad($pdo, "INSERT INTO referencias_rama VALUES ('$evaluacion','OTRO','PETERSEN','AJENO','$aplicacionComun')");
    debeRechazarIdentidad($pdo, "INSERT INTO entradas_perfil_instrumental VALUES ('$evaluacion','$aplicacionComun','OTRO',9,'TEXTO','AJENA','$preguntaComun',0,NULL,NULL,NULL)");
    debeRechazarIdentidad($pdo, "INSERT INTO entradas_perfil_instrumental VALUES ('$evaluacion','$aplicacionComun','$instrumentoComun',9,'TEXTO','$clavePrimera','$preguntaComun',2,NULL,'TEXTO',NULL)");
    $pdo->prepare('UPDATE evaluaciones SET cerrada=1 WHERE codigo=?')->execute([$evaluacion]);
    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $error;
}

$leer = $pdo->prepare('SELECT DISTINCT q.* FROM entradas_perfil_instrumental q
 JOIN referencias_rama r ON r.evaluacion=q.evaluacion AND r.aplicacion=q.aplicacion
 WHERE r.evaluacion=? AND r.rama=? AND r.aplicacion=? ORDER BY q.orden');
foreach ($aplicaciones as $aplicacion => $captura) {
    foreach (['INSTRUMENTAL','PETERSEN'] as $rama) {
        $leer->execute([$evaluacion,$rama,$aplicacion]);
        $recuperadas = [];
        foreach ($leer->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $pregunta = ['cod_pregunta'=>$fila['pregunta'],'cod_instrumento'=>$fila['instrumento']];
            foreach (['enunciado'=>1,'tipo_respuesta'=>2,'estado'=>4] as $campo=>$bit) {
                if (((int)$fila['mascara'] & $bit) !== 0) {
                    $pregunta[$campo] = $fila[$campo];
                }
            }
            $clave = $fila['tipo_clave']==='ENTERO' ? (int)$fila['clave'] : $fila['clave'];
            $recuperadas[$clave] = $pregunta;
        }
        exigirIdentidad(Canonica::ordenar($recuperadas)===Canonica::ordenar($captura['preguntas_declarativas_originales']),
            'Preguntas distintas en '.$rama.' / '.$aplicacion);
    }
}
exigirIdentidad((int)$pdo->query("SELECT count(*) FROM entradas_perfil_instrumental")->fetchColumn()===5,
    'Las dos ramas duplicaron las preguntas adquiridas.');
debeRechazarIdentidad($pdo, "INSERT INTO entradas_perfil_instrumental VALUES ('$evaluacion','$aplicacionComun','$instrumentoComun',10,'TEXTO','TARDE','$preguntaComun',0,NULL,NULL,NULL)");
debeRechazarIdentidad($pdo, "INSERT INTO referencias_rama VALUES ('$evaluacion','$residente','PETERSEN','TARDIO','$aplicacionComun')");
debeRechazarIdentidad($pdo, "UPDATE entradas_perfil_instrumental SET enunciado='ALTERADO' WHERE clave='$clavePrimera'");
debeRechazarIdentidad($pdo, "DELETE FROM entradas_perfil_instrumental WHERE clave='$clavePrimera'");
debeRechazarIdentidad($pdo, "UPDATE aplicaciones_adquiridas SET instrumento='ALTERADO' WHERE aplicacion='$aplicacionComun'");
debeRechazarIdentidad($pdo, "DELETE FROM referencias_rama WHERE aplicacion='$aplicacionComun'");

echo 'PASS B02 compartido: '.count($aplicaciones).' aplicaciones originales; '.count($referenciasPetersen)
    .' referencias Petersen; cinco preguntas sin respuesta del DTO válido recuperadas por ambas ramas, '
    .'con NULL/ausencia, tipo de clave, FK, deduplicación y cierre.'.PHP_EOL;
echo 'LÍMITE: el SQL solo representa B02 del DTO compuesto; no prueba las otras 50 colecciones ni la migración.'.PHP_EOL;
