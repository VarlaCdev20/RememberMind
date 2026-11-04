<?php

declare(strict_types=1);

/**
 * Ensayo físico aislado de una quinta consolidación candidata.
 * Solo usa SQLite :memory: y datos sintéticos; no modifica Laravel ni la BDD institucional.
 */
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');

function filasVariable(PDO $db, string $tabla, string $orden): array
{
    return $db->query("SELECT * FROM $tabla ORDER BY $orden")->fetchAll(PDO::FETCH_ASSOC);
}

function exigirVariable(bool $condicion, string $mensaje): void
{
    if (! $condicion) {
        throw new RuntimeException($mensaje);
    }
}

function rechazarVariable(PDO $db, string $sql): void
{
    try {
        $db->exec($sql);
    } catch (Throwable) {
        return;
    }

    throw new RuntimeException('Se admitió una variable o referencia inválida.');
}

$db->exec(<<<'SQL'
CREATE TABLE versiones (cod_version_modelo TEXT PRIMARY KEY);
CREATE TABLE usuarios (cod_usuario TEXT PRIMARY KEY);
CREATE TABLE dominios (cod_dominio_valores TEXT, cod_version_modelo TEXT,
    PRIMARY KEY(cod_dominio_valores), UNIQUE(cod_dominio_valores,cod_version_modelo));
CREATE TABLE nodos_semanticos (
    cod_nodo_semantico TEXT PRIMARY KEY, cod_version_modelo TEXT NOT NULL,
    codigo_semantico TEXT NOT NULL, nombre TEXT NOT NULL, tipo_nodo TEXT NOT NULL,
    definicion TEXT NOT NULL, estado TEXT NOT NULL, fecha_hora_creacion TEXT NOT NULL,
    cod_usuario_creacion TEXT NOT NULL, observacion TEXT,
    UNIQUE(cod_nodo_semantico,cod_version_modelo), UNIQUE(cod_version_modelo,codigo_semantico));
CREATE TABLE variables_expertas (
    cod_variable_experta TEXT PRIMARY KEY, cod_version_modelo TEXT NOT NULL,
    cod_nodo_semantico TEXT NOT NULL UNIQUE, cod_nodo_propietario_primario TEXT,
    cod_dominio_valores TEXT, tipo_semantico TEXT NOT NULL, papel_inferencial TEXT NOT NULL,
    estado TEXT NOT NULL, fecha_hora_creacion TEXT NOT NULL,
    cod_usuario_creacion TEXT NOT NULL, observacion TEXT,
    UNIQUE(cod_variable_experta,cod_version_modelo),
    FOREIGN KEY(cod_nodo_semantico,cod_version_modelo)
      REFERENCES nodos_semanticos(cod_nodo_semantico,cod_version_modelo));
CREATE TABLE mapeos_variables_fuente (
    cod_mapeo TEXT PRIMARY KEY, cod_variable_experta TEXT NOT NULL,
    FOREIGN KEY(cod_variable_experta) REFERENCES variables_expertas(cod_variable_experta));
CREATE TABLE condiciones_regla_experta (
    cod_condicion TEXT PRIMARY KEY, cod_variable_experta TEXT NOT NULL,
    FOREIGN KEY(cod_variable_experta) REFERENCES variables_expertas(cod_variable_experta));
INSERT INTO versiones VALUES ('V-1'),('V-2');
INSERT INTO usuarios VALUES ('U-1'),('U-2');
INSERT INTO dominios VALUES ('D-1','V-1');
INSERT INTO nodos_semanticos VALUES
 ('N-1','V-1','MEM','Memoria','CRITERIO','definición','ACTIVO','2026-01-01 08:00:00','U-1',NULL),
 ('N-2','V-1','HECHO','Hecho','VARIABLE','definición','ACTIVO','2026-01-01 08:01:00','U-1',NULL),
 ('N-3','V-2','OTRO','Otro','VARIABLE','definición','ACTIVO','2026-01-01 08:02:00','U-1',NULL);
INSERT INTO variables_expertas VALUES
 ('VAR-1','V-1','N-2','N-1','D-1','OBSERVACIONAL','DESCRIPTIVO','ACTIVA','2026-02-01 09:00:00','U-2',NULL),
 ('VAR-2','V-2','N-3',NULL,NULL,'INSTRUMENTAL','INFERENCIAL','INACTIVA','2026-02-01 09:01:00','U-2','revisión');
INSERT INTO mapeos_variables_fuente VALUES ('M-1','VAR-1');
INSERT INTO condiciones_regla_experta VALUES ('C-1','VAR-1'),('C-2','VAR-2');
SQL);

$original = [
    'nodos' => filasVariable($db, 'nodos_semanticos', 'cod_nodo_semantico'),
    'variables' => filasVariable($db, 'variables_expertas', 'cod_variable_experta'),
    'mapeos' => filasVariable($db, 'mapeos_variables_fuente', 'cod_mapeo'),
    'condiciones' => filasVariable($db, 'condiciones_regla_experta', 'cod_condicion'),
];

$crear = <<<'SQL'
CREATE TABLE nodos_semanticos_v2 (
    cod_nodo_semantico TEXT PRIMARY KEY, cod_version_modelo TEXT NOT NULL,
    codigo_semantico TEXT NOT NULL, nombre TEXT NOT NULL, tipo_nodo TEXT NOT NULL,
    definicion TEXT NOT NULL, estado TEXT NOT NULL, fecha_hora_creacion TEXT NOT NULL,
    cod_usuario_creacion TEXT NOT NULL, observacion TEXT,
    cod_variable_experta TEXT UNIQUE, cod_nodo_propietario_primario TEXT,
    cod_dominio_valores TEXT, tipo_semantico TEXT, papel_inferencial TEXT,
    estado_variable TEXT, fecha_hora_creacion_variable TEXT,
    cod_usuario_creacion_variable TEXT, observacion_variable TEXT,
    UNIQUE(cod_nodo_semantico,cod_version_modelo),
    UNIQUE(cod_variable_experta,cod_version_modelo),
    UNIQUE(cod_version_modelo,codigo_semantico),
    FOREIGN KEY(cod_version_modelo) REFERENCES versiones(cod_version_modelo),
    FOREIGN KEY(cod_usuario_creacion) REFERENCES usuarios(cod_usuario),
    FOREIGN KEY(cod_usuario_creacion_variable) REFERENCES usuarios(cod_usuario),
    FOREIGN KEY(cod_nodo_propietario_primario,cod_version_modelo)
      REFERENCES nodos_semanticos_v2(cod_nodo_semantico,cod_version_modelo),
    FOREIGN KEY(cod_dominio_valores,cod_version_modelo)
      REFERENCES dominios(cod_dominio_valores,cod_version_modelo),
    CHECK ((cod_variable_experta IS NULL AND cod_nodo_propietario_primario IS NULL
      AND cod_dominio_valores IS NULL AND tipo_semantico IS NULL AND papel_inferencial IS NULL
      AND estado_variable IS NULL AND fecha_hora_creacion_variable IS NULL
      AND cod_usuario_creacion_variable IS NULL AND observacion_variable IS NULL)
      OR (cod_variable_experta IS NOT NULL AND tipo_semantico IS NOT NULL
      AND papel_inferencial IS NOT NULL AND estado_variable IS NOT NULL
      AND fecha_hora_creacion_variable IS NOT NULL AND cod_usuario_creacion_variable IS NOT NULL))
);
SQL;

$copiar = <<<'SQL'
INSERT INTO nodos_semanticos_v2
SELECT n.cod_nodo_semantico,n.cod_version_modelo,n.codigo_semantico,n.nombre,n.tipo_nodo,
 n.definicion,n.estado,n.fecha_hora_creacion,n.cod_usuario_creacion,n.observacion,
 v.cod_variable_experta,v.cod_nodo_propietario_primario,v.cod_dominio_valores,
 v.tipo_semantico,v.papel_inferencial,v.estado,v.fecha_hora_creacion,
 v.cod_usuario_creacion,v.observacion
FROM nodos_semanticos n LEFT JOIN variables_expertas v
 ON v.cod_nodo_semantico=n.cod_nodo_semantico AND v.cod_version_modelo=n.cod_version_modelo;
SQL;

$db->beginTransaction();
try {
    $db->exec($crear);
    $db->exec($copiar);
    throw new RuntimeException('FALLO_INYECTADO');
} catch (RuntimeException $error) {
    exigirVariable($error->getMessage() === 'FALLO_INYECTADO', 'Fallo de prueba inesperado.');
    $db->rollBack();
}
exigirVariable((int) $db->query("SELECT COUNT(*) FROM sqlite_master WHERE name='nodos_semanticos_v2'")->fetchColumn() === 0,
    'La transacción fallida dejó estructura parcial.');
exigirVariable(filasVariable($db, 'variables_expertas', 'cod_variable_experta') === $original['variables'],
    'El rollback alteró variables originales.');

$db->exec('PRAGMA foreign_keys = OFF');
$db->beginTransaction();
try {
    $db->exec($crear);
    $db->exec($copiar);
    $db->exec('CREATE TABLE mapeos_v2(cod_mapeo TEXT PRIMARY KEY, cod_variable_experta TEXT NOT NULL
        REFERENCES nodos_semanticos_v2(cod_variable_experta) ON DELETE RESTRICT)');
    $db->exec('CREATE TABLE condiciones_v2(cod_condicion TEXT PRIMARY KEY, cod_variable_experta TEXT NOT NULL
        REFERENCES nodos_semanticos_v2(cod_variable_experta) ON DELETE RESTRICT)');
    $db->exec('INSERT INTO mapeos_v2 SELECT * FROM mapeos_variables_fuente');
    $db->exec('INSERT INTO condiciones_v2 SELECT * FROM condiciones_regla_experta');
    foreach (['mapeos_variables_fuente', 'condiciones_regla_experta', 'variables_expertas', 'nodos_semanticos'] as $tabla) {
        $db->exec("DROP TABLE $tabla");
    }
    foreach (['nodos_semanticos_v2' => 'nodos_semanticos', 'mapeos_v2' => 'mapeos_variables_fuente',
        'condiciones_v2' => 'condiciones_regla_experta'] as $desde => $hacia) {
        $db->exec("ALTER TABLE $desde RENAME TO $hacia");
    }
    exigirVariable($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'La transición dejó FK inválidas.');
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
} finally {
    $db->exec('PRAGMA foreign_keys = ON');
}

$nodos = [];
$variables = [];
foreach (filasVariable($db, 'nodos_semanticos', 'cod_nodo_semantico') as $fila) {
    $nodos[] = array_intersect_key($fila, array_flip(array_keys($original['nodos'][0])));
    if ($fila['cod_variable_experta'] !== null) {
        $variables[] = [
            'cod_variable_experta' => $fila['cod_variable_experta'],
            'cod_version_modelo' => $fila['cod_version_modelo'],
            'cod_nodo_semantico' => $fila['cod_nodo_semantico'],
            'cod_nodo_propietario_primario' => $fila['cod_nodo_propietario_primario'],
            'cod_dominio_valores' => $fila['cod_dominio_valores'],
            'tipo_semantico' => $fila['tipo_semantico'],
            'papel_inferencial' => $fila['papel_inferencial'],
            'estado' => $fila['estado_variable'],
            'fecha_hora_creacion' => $fila['fecha_hora_creacion_variable'],
            'cod_usuario_creacion' => $fila['cod_usuario_creacion_variable'],
            'observacion' => $fila['observacion_variable'],
        ];
    }
}
exigirVariable($nodos === $original['nodos'] && $variables === $original['variables'], 'La reconstrucción cambió nodos o variables.');
exigirVariable(filasVariable($db, 'mapeos_variables_fuente', 'cod_mapeo') === $original['mapeos'], 'Se perdieron mapeos.');
exigirVariable(filasVariable($db, 'condiciones_regla_experta', 'cod_condicion') === $original['condiciones'], 'Se perdieron condiciones.');
rechazarVariable($db, "INSERT INTO mapeos_variables_fuente VALUES ('M-X','VAR-AJENA')");
rechazarVariable($db, "INSERT INTO nodos_semanticos(cod_nodo_semantico,cod_version_modelo,codigo_semantico,nombre,tipo_nodo,definicion,estado,fecha_hora_creacion,cod_usuario_creacion,cod_variable_experta) VALUES ('N-X','V-1','X','X','VARIABLE','X','ACTIVO','2026-01-01','U-1','VAR-X')");
rechazarVariable($db, "UPDATE nodos_semanticos SET cod_nodo_propietario_primario='N-3' WHERE cod_nodo_semantico='N-2'");
exigirVariable($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'Fallaron las FK tras pruebas negativas.');

echo "PASS SQLite desechable: rollback, 3 nodos, 2 variables, 1 mapeo, 2 condiciones, reconstrucción exacta y FK/checks.\n";
echo "LÍMITE: esquema sintético parcial; no prueba FK entrantes restantes, PostgreSQL, concurrencia, 4FN ni uso institucional. No autoriza DDL.\n";
