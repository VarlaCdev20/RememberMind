<?php

declare(strict_types=1);

/** Ensayo B01 aislado: contratos y modalidades de dos ramas, solo SQLite en memoria y datos sintéticos. */
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');

function exigirContrato(bool $ok, string $mensaje): void
{
    if (! $ok) {
        throw new RuntimeException($mensaje);
    }
}

function rechazarContrato(PDO $db, string $sql): void
{
    try {
        $db->exec($sql);
    } catch (PDOException) {
        return;
    }

    throw new RuntimeException('El esquema admitió un contrato inválido.');
}

$db->exec(<<<'SQL'
CREATE TABLE evaluaciones_expertas (cod_evaluacion_experta TEXT PRIMARY KEY,
 estado TEXT NOT NULL CHECK(estado IN ('ABIERTA','CERRADA')));
CREATE TABLE conocimientos_evaluacion (
 cod_evaluacion_experta TEXT NOT NULL REFERENCES evaluaciones_expertas(cod_evaluacion_experta),
 rama TEXT NOT NULL CHECK(rama IN ('INSTRUMENTAL','PETERSEN')),
 PRIMARY KEY(cod_evaluacion_experta,rama));
INSERT INTO evaluaciones_expertas VALUES ('E-1','ABIERTA');
INSERT INTO conocimientos_evaluacion VALUES ('E-1','INSTRUMENTAL'),('E-1','PETERSEN');
SQL);

$crear = <<<'SQL'
CREATE TABLE contratos_relaciones_instantanea (
 cod_contrato_fila TEXT PRIMARY KEY CHECK(length(cod_contrato_fila) BETWEEN 1 AND 20),
 cod_evaluacion_experta TEXT NOT NULL,
 rama TEXT NOT NULL,
 clase TEXT NOT NULL CHECK(clase IN ('CONTRATO','MODALIDAD')),
 cod_contrato_padre TEXT,
 orden_contrato INTEGER,
 tipo_relacion TEXT,
 tipo_origen TEXT,
 tipo_destino TEXT,
 habilita_presente INTEGER,
 habilita_valor INTEGER,
 orden_modalidad INTEGER,
 modalidad_nula INTEGER,
 modalidad TEXT,
 UNIQUE(cod_contrato_fila,cod_evaluacion_experta,rama),
 FOREIGN KEY(cod_evaluacion_experta,rama)
   REFERENCES conocimientos_evaluacion(cod_evaluacion_experta,rama) ON DELETE RESTRICT,
 FOREIGN KEY(cod_contrato_padre,cod_evaluacion_experta,rama)
   REFERENCES contratos_relaciones_instantanea(cod_contrato_fila,cod_evaluacion_experta,rama) ON DELETE RESTRICT,
 CHECK ((clase='CONTRATO' AND cod_contrato_padre IS NULL AND orden_contrato>=0
   AND tipo_relacion IS NOT NULL AND tipo_origen IS NOT NULL AND tipo_destino IS NOT NULL
   AND habilita_presente IN (0,1)
   AND ((habilita_presente=0 AND habilita_valor IS NULL)
     OR (habilita_presente=1 AND habilita_valor IN (0,1)))
   AND orden_modalidad IS NULL AND modalidad_nula IS NULL AND modalidad IS NULL)
  OR (clase='MODALIDAD' AND cod_contrato_padre IS NOT NULL AND orden_contrato IS NULL
   AND tipo_relacion IS NULL AND tipo_origen IS NULL AND tipo_destino IS NULL
   AND habilita_presente IS NULL AND habilita_valor IS NULL AND orden_modalidad>=0
   AND modalidad_nula IN (0,1)
   AND ((modalidad_nula=1 AND modalidad IS NULL)
     OR (modalidad_nula=0 AND modalidad IS NOT NULL))))
);
CREATE UNIQUE INDEX contrato_orden ON contratos_relaciones_instantanea
 (cod_evaluacion_experta,rama,orden_contrato) WHERE clase='CONTRATO';
CREATE UNIQUE INDEX modalidad_orden ON contratos_relaciones_instantanea
 (cod_contrato_padre,orden_modalidad) WHERE clase='MODALIDAD';
CREATE TRIGGER modalidad_padre_valido BEFORE INSERT ON contratos_relaciones_instantanea
 WHEN NEW.clase='MODALIDAD' AND NOT EXISTS(
  SELECT 1 FROM contratos_relaciones_instantanea p WHERE p.cod_contrato_fila=NEW.cod_contrato_padre
   AND p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rama=NEW.rama AND p.clase='CONTRATO')
 BEGIN SELECT RAISE(ABORT,'La modalidad requiere contrato de su rama'); END;
CREATE TRIGGER contrato_cerrado BEFORE INSERT ON contratos_relaciones_instantanea
 WHEN EXISTS(SELECT 1 FROM evaluaciones_expertas e
  WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND e.estado='CERRADA')
 BEGIN SELECT RAISE(ABORT,'La colección histórica está cerrada'); END;
CREATE TRIGGER contrato_inmutable_update BEFORE UPDATE ON contratos_relaciones_instantanea
 BEGIN SELECT RAISE(ABORT,'El contrato histórico es inmutable'); END;
CREATE TRIGGER contrato_inmutable_delete BEFORE DELETE ON contratos_relaciones_instantanea
 BEGIN SELECT RAISE(ABORT,'El contrato histórico es inmutable'); END;
SQL;

$original = [
    'INSTRUMENTAL' => [
        ['tipo_relacion' => 'APORTA_EVIDENCIA', 'tipo_origen' => 'VARIABLE', 'tipo_destino' => 'CRITERIO',
            'habilita_participacion' => true, 'modalidades' => [null, 'DIFERIDA']],
        ['tipo_relacion' => 'CONTEXTUALIZA', 'tipo_origen' => 'VARIABLE', 'tipo_destino' => 'CRITERIO',
            'habilita_participacion' => false, 'modalidades' => []],
    ],
    'PETERSEN' => [
        ['tipo_relacion' => 'SUBCOMPONENTE_DE', 'tipo_origen' => 'SUBCOMPONENTE',
            'tipo_destino' => 'CRITERIO', 'modalidades' => ['PRIMARIA']],
    ],
];

function guardarContratos(PDO $db, array $original): void
{
    $insertar = $db->prepare('INSERT INTO contratos_relaciones_instantanea VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $indice = 0;
    foreach ($original as $rama => $contratos) {
        foreach ($contratos as $orden => $contrato) {
            $id = 'CO-'.(++$indice);
            $presente = array_key_exists('habilita_participacion', $contrato);
            $insertar->execute([$id, 'E-1', $rama, 'CONTRATO', null, $orden,
                $contrato['tipo_relacion'], $contrato['tipo_origen'], $contrato['tipo_destino'],
                (int) $presente, $presente ? (int) $contrato['habilita_participacion'] : null,
                null, null, null]);
            foreach ($contrato['modalidades'] as $ordenModalidad => $modalidad) {
                $insertar->execute(['MO-'.(++$indice), 'E-1', $rama, 'MODALIDAD', $id,
                    null, null, null, null, null, null, $ordenModalidad,
                    (int) ($modalidad === null), $modalidad]);
            }
        }
    }
}

$db->beginTransaction();
try {
    $db->exec($crear);
    guardarContratos($db, $original);
    throw new RuntimeException('FALLO_INYECTADO');
} catch (RuntimeException $error) {
    exigirContrato($error->getMessage() === 'FALLO_INYECTADO', 'Falló el ensayo de reversión.');
    $db->rollBack();
}
exigirContrato((int) $db->query("SELECT count(*) FROM sqlite_master WHERE name='contratos_relaciones_instantanea'")->fetchColumn() === 0,
    'La reversión dejó la tabla de prueba.');

$db->beginTransaction();
$db->exec($crear);
guardarContratos($db, $original);
$db->commit();

$recuperado = ['INSTRUMENTAL' => [], 'PETERSEN' => []];
$filas = $db->query('SELECT * FROM contratos_relaciones_instantanea ORDER BY rama,orden_contrato,orden_modalidad')->fetchAll(PDO::FETCH_ASSOC);
$porId = [];
foreach ($filas as $fila) {
    if ($fila['clase'] !== 'CONTRATO') {
        continue;
    }
    $contrato = ['tipo_relacion' => $fila['tipo_relacion'], 'tipo_origen' => $fila['tipo_origen'],
        'tipo_destino' => $fila['tipo_destino']];
    if ((int) $fila['habilita_presente'] === 1) {
        $contrato['habilita_participacion'] = (bool) $fila['habilita_valor'];
    }
    $contrato['modalidades'] = [];
    $recuperado[$fila['rama']][(int) $fila['orden_contrato']] = $contrato;
    $porId[$fila['cod_contrato_fila']] = [$fila['rama'], (int) $fila['orden_contrato']];
}
foreach ($filas as $fila) {
    if ($fila['clase'] !== 'MODALIDAD') {
        continue;
    }
    [$rama, $orden] = $porId[$fila['cod_contrato_padre']];
    $recuperado[$rama][$orden]['modalidades'][(int) $fila['orden_modalidad']] =
        (int) $fila['modalidad_nula'] === 1 ? null : $fila['modalidad'];
}
foreach ($recuperado as &$contratos) {
    ksort($contratos);
    $contratos = array_values($contratos);
    foreach ($contratos as &$contrato) {
        ksort($contrato['modalidades']);
        $contrato['modalidades'] = array_values($contrato['modalidades']);
    }
    unset($contrato);
}
unset($contratos);
exigirContrato($recuperado === $original, 'La lectura no reconstruyó exactamente ambas ramas.');

rechazarContrato($db, "INSERT INTO contratos_relaciones_instantanea VALUES ('MO-X','E-1','PETERSEN','MODALIDAD','CO-1',NULL,NULL,NULL,NULL,NULL,NULL,5,0,'AJENA')");
rechazarContrato($db, "INSERT INTO contratos_relaciones_instantanea VALUES ('MO-X','E-1','INSTRUMENTAL','MODALIDAD','CO-1',NULL,NULL,NULL,NULL,NULL,NULL,0,0,'DUPLICADA')");
rechazarContrato($db, "INSERT INTO contratos_relaciones_instantanea VALUES ('CO-X','E-1','INSTRUMENTAL','CONTRATO',NULL,3,'X','X','X',0,1,NULL,NULL,NULL)");
$db->exec("UPDATE evaluaciones_expertas SET estado='CERRADA' WHERE cod_evaluacion_experta='E-1'");
rechazarContrato($db, "INSERT INTO contratos_relaciones_instantanea VALUES ('CO-X','E-1','INSTRUMENTAL','CONTRATO',NULL,3,'X','X','X',0,NULL,NULL,NULL,NULL)");
rechazarContrato($db, "UPDATE contratos_relaciones_instantanea SET modalidad='ALTERADA' WHERE cod_contrato_fila='MO-2'");
rechazarContrato($db, "DELETE FROM contratos_relaciones_instantanea WHERE cod_contrato_fila='MO-2'");
exigirContrato($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK inválida.');

echo "PASS B01 SQLite desechable: dos ramas, tres contratos, tres modalidades, NULL, lista vacía, ausencia/false, orden, rollback, cierre y checks.\n";
echo "LÍMITE: no cubre DTO compuesto, PostgreSQL, cierre concurrente, 4FN independiente ni autorización estructural.\n";
