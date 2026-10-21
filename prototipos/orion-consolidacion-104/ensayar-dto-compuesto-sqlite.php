<?php

declare(strict_types=1);

// Ensayo de dos granos del DTO compuesto real (sintético) en SQLite :memory:.
// No instala ni modifica la BDD RememberMind.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require dirname(__DIR__, 2).'/tests/Unit/InstantaneaEvaluacionInstitucionalTest.php';

use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;

function exigir(bool $condicion, string $mensaje): void
{
    if (! $condicion) {
        throw new RuntimeException($mensaje);
    }
}

$test = new \Tests\Unit\InstantaneaEvaluacionInstitucionalTest('test_ausencias_no_se_rellenan_durante_el_replay');
$dto = (new ReflectionMethod($test, 'crear'))->invoke($test, ['COG-MEM'], false, false, true);
$instrumental = $dto->contenido['instrumental']['contenido'];
$petersen = $dto->contenido['petersen']['contenido'];
$contratos = [
    'INSTRUMENTAL' => $instrumental['conocimiento']['contratos_relaciones'],
    'PETERSEN' => $petersen['gobierno']['conocimiento']['contratos_relaciones'],
];
$membresias = $petersen['membresias_soportes'];

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->exec(<<<'SQL'
CREATE TABLE evaluaciones (codigo TEXT PRIMARY KEY, cerrada INTEGER NOT NULL DEFAULT 0 CHECK (cerrada IN (0,1)));
CREATE TABLE contratos_relaciones_instantanea (
    codigo TEXT PRIMARY KEY,
    evaluacion TEXT NOT NULL REFERENCES evaluaciones(codigo),
    rama TEXT NOT NULL CHECK (rama IN ('INSTRUMENTAL','PETERSEN')),
    variante TEXT NOT NULL CHECK (variante IN ('CONTRATO','MODALIDAD')),
    orden INTEGER NOT NULL CHECK (orden >= 0),
    padre TEXT,
    tipo_relacion TEXT, tipo_origen TEXT, tipo_destino TEXT,
    habilita_presente INTEGER CHECK (habilita_presente IN (0,1)),
    habilita_valor INTEGER CHECK (habilita_valor IN (0,1)),
    modalidad TEXT,
    UNIQUE (evaluacion,rama,codigo),
    FOREIGN KEY (evaluacion,rama,padre) REFERENCES contratos_relaciones_instantanea(evaluacion,rama,codigo),
    CHECK ((variante='CONTRATO' AND padre IS NULL AND tipo_relacion IS NOT NULL
            AND tipo_origen IS NOT NULL AND tipo_destino IS NOT NULL AND habilita_presente IS NOT NULL
            AND ((habilita_presente=1 AND habilita_valor IS NOT NULL)
                OR (habilita_presente=0 AND habilita_valor IS NULL)))
        OR (variante='MODALIDAD' AND padre IS NOT NULL AND tipo_relacion IS NULL
            AND tipo_origen IS NULL AND tipo_destino IS NULL AND habilita_presente IS NULL AND habilita_valor IS NULL))
);
CREATE UNIQUE INDEX contrato_orden ON contratos_relaciones_instantanea(evaluacion,rama,orden) WHERE variante='CONTRATO';
CREATE UNIQUE INDEX modalidad_orden ON contratos_relaciones_instantanea(evaluacion,rama,padre,orden) WHERE variante='MODALIDAD';
CREATE TABLE valoraciones_adquiridas (
    evaluacion TEXT NOT NULL REFERENCES evaluaciones(codigo),
    valoracion TEXT NOT NULL,
    orden INTEGER NOT NULL,
    PRIMARY KEY (evaluacion, valoracion),
    UNIQUE (evaluacion,orden)
);
CREATE TABLE evidencias_criterio_evaluacion (
    codigo TEXT PRIMARY KEY,
    evaluacion TEXT NOT NULL REFERENCES evaluaciones(codigo),
    variante TEXT NOT NULL CHECK (variante IN ('CRITERIO','CONDICION','MEMBRESIA_PETERSEN')),
    orden INTEGER NOT NULL CHECK (orden >= 0),
    valoracion TEXT,
    soporte TEXT,
    criterio TEXT,
    condicion TEXT,
    evidencia TEXT,
    FOREIGN KEY (evaluacion,valoracion) REFERENCES valoraciones_adquiridas(evaluacion,valoracion),
    CHECK ((variante='MEMBRESIA_PETERSEN' AND valoracion IS NOT NULL AND soporte IS NOT NULL
            AND criterio IS NULL AND condicion IS NULL AND evidencia IS NULL)
        OR (variante='CRITERIO' AND criterio IS NOT NULL AND evidencia IS NOT NULL
            AND valoracion IS NULL AND soporte IS NULL AND condicion IS NULL)
        OR (variante='CONDICION' AND condicion IS NOT NULL AND evidencia IS NOT NULL
            AND valoracion IS NULL AND soporte IS NULL AND criterio IS NULL))
);
CREATE UNIQUE INDEX membresia_orden ON evidencias_criterio_evaluacion(evaluacion,valoracion,orden) WHERE variante='MEMBRESIA_PETERSEN';
CREATE UNIQUE INDEX soporte_unico ON evidencias_criterio_evaluacion(evaluacion,soporte) WHERE soporte IS NOT NULL;
CREATE TRIGGER modalidad_padre_valido BEFORE INSERT ON contratos_relaciones_instantanea
WHEN NEW.variante='MODALIDAD' AND NOT EXISTS (
    SELECT 1 FROM contratos_relaciones_instantanea p WHERE p.codigo=NEW.padre
    AND p.evaluacion=NEW.evaluacion AND p.rama=NEW.rama AND p.variante='CONTRATO'
) BEGIN SELECT RAISE(ABORT,'padre incompatible'); END;
CREATE TRIGGER contrato_cerrado BEFORE INSERT ON contratos_relaciones_instantanea
WHEN (SELECT cerrada FROM evaluaciones WHERE codigo=NEW.evaluacion)=1 BEGIN SELECT RAISE(ABORT,'evaluacion cerrada'); END;
CREATE TRIGGER membresia_cerrada BEFORE INSERT ON evidencias_criterio_evaluacion
WHEN (SELECT cerrada FROM evaluaciones WHERE codigo=NEW.evaluacion)=1 BEGIN SELECT RAISE(ABORT,'evaluacion cerrada'); END;
CREATE TRIGGER contrato_inmutable BEFORE UPDATE ON contratos_relaciones_instantanea
BEGIN SELECT RAISE(ABORT,'contrato inmutable'); END;
CREATE TRIGGER contrato_no_borrable BEFORE DELETE ON contratos_relaciones_instantanea
BEGIN SELECT RAISE(ABORT,'contrato inmutable'); END;
CREATE TRIGGER membresia_inmutable BEFORE UPDATE ON evidencias_criterio_evaluacion
BEGIN SELECT RAISE(ABORT,'membresia inmutable'); END;
CREATE TRIGGER membresia_no_borrable BEFORE DELETE ON evidencias_criterio_evaluacion
BEGIN SELECT RAISE(ABORT,'membresia inmutable'); END;
CREATE TRIGGER cierre_irreversible BEFORE UPDATE OF cerrada ON evaluaciones WHEN OLD.cerrada=1
BEGIN SELECT RAISE(ABORT,'cierre irreversible'); END;
SQL);

$pdo->beginTransaction();
try {
    $pdo->exec("INSERT INTO evaluaciones(codigo) VALUES ('EVAL-COMPUESTA')");
    $insertarContrato = $pdo->prepare('INSERT INTO contratos_relaciones_instantanea
        (codigo,evaluacion,rama,variante,orden,padre,tipo_relacion,tipo_origen,tipo_destino,habilita_presente,habilita_valor,modalidad)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($contratos as $rama => $lista) {
        foreach ($lista as $orden => $contrato) {
            $codigo = 'C-'.$rama.'-'.$orden;
            $presente = array_key_exists('habilita_participacion', $contrato);
            $insertarContrato->execute([$codigo,'EVAL-COMPUESTA',$rama,'CONTRATO',$orden,null,
                $contrato['tipo_relacion'],$contrato['tipo_origen'],$contrato['tipo_destino'],(int) $presente,
                $presente ? (int) $contrato['habilita_participacion'] : null,null]);
            foreach ($contrato['modalidades'] as $indice => $modalidad) {
                $insertarContrato->execute(['M-'.$rama.'-'.$orden.'-'.$indice,'EVAL-COMPUESTA',$rama,'MODALIDAD',
                    $indice,$codigo,null,null,null,null,null,$modalidad]);
            }
        }
    }
    $insertarJuicio = $pdo->prepare('INSERT INTO valoraciones_adquiridas(evaluacion,valoracion,orden) VALUES (?,?,?)');
    foreach ($petersen['procedencia_valoraciones'] as $orden => $juicio) {
        $insertarJuicio->execute(['EVAL-COMPUESTA',$juicio['cod_valoracion_petersen'],$orden]);
    }
    $insertarMembresia = $pdo->prepare('INSERT INTO evidencias_criterio_evaluacion
        (codigo,evaluacion,variante,orden,valoracion,soporte) VALUES (?,?,?,?,?,?)');
    foreach ($membresias as $orden => $membresia) {
        foreach ($membresia['soportes'] as $indice => $soporte) {
            $insertarMembresia->execute(['S-'.$orden.'-'.$indice,'EVAL-COMPUESTA','MEMBRESIA_PETERSEN',
                $indice,$membresia['valoracion'],$soporte]);
        }
    }
    foreach ([
        "INSERT INTO contratos_relaciones_instantanea(codigo,evaluacion,rama,variante,orden,padre,modalidad)
            VALUES ('M-CRUZ','EVAL-COMPUESTA','PETERSEN','MODALIDAD',99,'C-INSTRUMENTAL-0',NULL)" => 'padre incompatible',
        "INSERT INTO evidencias_criterio_evaluacion(codigo,evaluacion,variante,orden,valoracion,soporte)
            VALUES ('S-AJENO','EVAL-COMPUESTA','MEMBRESIA_PETERSEN',99,'OTRA-VALORACION','SV-AJENO')" => 'FOREIGN KEY constraint failed',
    ] as $sql => $errorEsperado) {
        try {
            $pdo->exec($sql);
            throw new RuntimeException('Se aceptó un vínculo de otra rama o valoración.');
        } catch (PDOException $e) {
            exigir(str_contains($e->getMessage(), $errorEsperado), 'El cruce falló por otra razón.');
        }
    }
    $pdo->exec("UPDATE evaluaciones SET cerrada=1 WHERE codigo='EVAL-COMPUESTA'");
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}

$leerContratos = $pdo->prepare("SELECT * FROM contratos_relaciones_instantanea WHERE evaluacion='EVAL-COMPUESTA'
    AND rama=? AND variante='CONTRATO' ORDER BY orden");
$leerModalidades = $pdo->prepare("SELECT modalidad FROM contratos_relaciones_instantanea WHERE evaluacion='EVAL-COMPUESTA'
    AND rama=? AND variante='MODALIDAD' AND padre=? ORDER BY orden");
foreach ($contratos as $rama => $original) {
    $leerContratos->execute([$rama]);
    $recuperados = [];
    foreach ($leerContratos->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $leerModalidades->execute([$rama,$fila['codigo']]);
        $contrato = ['tipo_relacion' => $fila['tipo_relacion'], 'tipo_origen' => $fila['tipo_origen'],
            'tipo_destino' => $fila['tipo_destino'],
            'modalidades' => array_column($leerModalidades->fetchAll(PDO::FETCH_ASSOC), 'modalidad')];
        if ((int) $fila['habilita_presente'] === 1) {
            $contrato['habilita_participacion'] = (bool) $fila['habilita_valor'];
        }
        $recuperados[] = $contrato;
    }
    exigir(C::ordenar($original) === C::ordenar($recuperados), 'Contratos distintos en '.$rama);
}

$padres = $pdo->query("SELECT * FROM valoraciones_adquiridas WHERE evaluacion='EVAL-COMPUESTA' ORDER BY orden")->fetchAll(PDO::FETCH_ASSOC);
$leerSoportes = $pdo->prepare("SELECT soporte FROM evidencias_criterio_evaluacion WHERE variante='MEMBRESIA_PETERSEN' AND valoracion=? ORDER BY orden");
$recuperadas = [];
foreach ($padres as $padre) {
    $leerSoportes->execute([$padre['valoracion']]);
    $recuperadas[] = ['valoracion' => $padre['valoracion'], 'soportes' => array_column($leerSoportes->fetchAll(PDO::FETCH_ASSOC), 'soporte')];
}
exigir(C::ordenar($membresias) === C::ordenar($recuperadas), 'Membresías distintas.');

foreach ([
    "INSERT INTO contratos_relaciones_instantanea(codigo,evaluacion,rama,variante,orden,padre,modalidad)
        VALUES ('M-TARDE','EVAL-COMPUESTA','PETERSEN','MODALIDAD',99,'C-PETERSEN-0',NULL)",
    "INSERT INTO evidencias_criterio_evaluacion(codigo,evaluacion,variante,orden,valoracion,soporte)
        VALUES ('S-TARDE','EVAL-COMPUESTA','MEMBRESIA_PETERSEN',99,'REV-COG-MEM','SV-TARDE')",
    "UPDATE contratos_relaciones_instantanea SET tipo_destino='OTRO' WHERE codigo='C-PETERSEN-0'",
    "DELETE FROM evidencias_criterio_evaluacion WHERE codigo='S-0-0'",
    "UPDATE evaluaciones SET cerrada=0 WHERE codigo='EVAL-COMPUESTA'",
] as $sql) {
    try {
        $pdo->exec($sql);
        throw new RuntimeException('Se aceptó un vínculo cruzado o una escritura tardía.');
    } catch (PDOException) {
        // Cierre de evaluación debe rechazarlo.
    }
}

echo 'PASS DTO compuesto sintético: '.count($contratos['INSTRUMENTAL']).' contratos instrumentales, '
    .count($contratos['PETERSEN']).' Petersen y '.count($membresias).' juicios con soportes recuperados exactamente;'
    .' ausencia/false, orden, ramas y cierre.'.PHP_EOL;
echo 'LÍMITE: el DTO contiene preguntas B02, pero este SQL solo representa B01 y B03. No prueba la proyección íntegra ni PostgreSQL.'.PHP_EOL;
