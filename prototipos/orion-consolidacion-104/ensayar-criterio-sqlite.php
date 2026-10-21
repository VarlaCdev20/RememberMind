<?php

declare(strict_types=1);

/**
 * Ensayo físico desechable de dos consolidaciones 0..1; nunca abre Laravel ni la BDD operativa.
 * Ejecutar: php prototipos/orion-consolidacion-104/ensayar-criterio-sqlite.php
 */

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');

function exigir(bool $condicion, string $mensaje): void
{
    if (! $condicion) {
        throw new RuntimeException($mensaje);
    }
}

function filas(PDO $db, string $tabla, string $orden): array
{
    return $db->query("SELECT * FROM $tabla ORDER BY $orden")->fetchAll(PDO::FETCH_ASSOC);
}

function falloSql(PDO $db, string $sql): void
{
    try {
        $db->exec($sql);
    } catch (Throwable) {
        return;
    }

    throw new RuntimeException('La restricción aceptó una fila inválida: '.$sql);
}

$db->exec(<<<'SQL'
CREATE TABLE valores_semanticos (cod_valor_semantico TEXT PRIMARY KEY);
CREATE TABLE evaluaciones_expertas (cod_evaluacion_experta TEXT PRIMARY KEY);
CREATE TABLE nodos_semanticos (cod_nodo_semantico TEXT PRIMARY KEY);
CREATE TABLE reglas_expertas (cod_regla_experta TEXT PRIMARY KEY);
CREATE TABLE evaluacion_criterios (
    cod_evaluacion_criterio TEXT PRIMARY KEY,
    cod_evaluacion_experta TEXT NOT NULL REFERENCES evaluaciones_expertas(cod_evaluacion_experta) ON DELETE RESTRICT,
    cod_nodo_criterio TEXT NOT NULL REFERENCES nodos_semanticos(cod_nodo_semantico) ON DELETE RESTRICT,
    estado_evaluabilidad TEXT NOT NULL,
    codigo_modificador_interpretacion TEXT,
    motivo_evaluabilidad TEXT,
    fecha_hora_determinacion TEXT NOT NULL,
    UNIQUE(cod_evaluacion_experta, cod_nodo_criterio)
);
CREATE TABLE resultados_criterio (
    cod_resultado_criterio TEXT PRIMARY KEY,
    cod_evaluacion_criterio TEXT NOT NULL UNIQUE REFERENCES evaluacion_criterios(cod_evaluacion_criterio) ON DELETE RESTRICT,
    cod_valor_semantico TEXT NOT NULL REFERENCES valores_semanticos(cod_valor_semantico) ON DELETE RESTRICT,
    fecha_hora_determinacion TEXT NOT NULL,
    justificacion_original TEXT
);
CREATE TABLE trazas_inferencia (
    cod_traza_inferencia TEXT PRIMARY KEY,
    cod_evaluacion_criterio TEXT NOT NULL UNIQUE REFERENCES evaluacion_criterios(cod_evaluacion_criterio) ON DELETE RESTRICT,
    estado_inferencia TEXT NOT NULL,
    fecha_hora_inicio TEXT NOT NULL,
    fecha_hora_fin TEXT
);
CREATE TABLE evaluaciones_reglas (
    cod_evaluacion_regla TEXT PRIMARY KEY,
    cod_traza_inferencia TEXT NOT NULL REFERENCES trazas_inferencia(cod_traza_inferencia) ON DELETE RESTRICT,
    cod_regla_experta TEXT NOT NULL REFERENCES reglas_expertas(cod_regla_experta) ON DELETE RESTRICT,
    estado_regla TEXT NOT NULL,
    fecha_hora_evaluacion TEXT NOT NULL,
    UNIQUE(cod_traza_inferencia, cod_regla_experta)
);
INSERT INTO valores_semanticos VALUES ('VAL-1');
INSERT INTO evaluaciones_expertas VALUES ('E-1');
INSERT INTO nodos_semanticos VALUES ('N-1'),('N-2'),('N-3'),('N-4'),('N-5');
INSERT INTO reglas_expertas VALUES ('G09-01'),('G09-02'),('G09-03');
INSERT INTO evaluacion_criterios VALUES
 ('C-1','E-1','N-1','INSUFICIENTE',NULL,NULL,'2026-01-01 10:00:00'),
 ('C-2','E-1','N-2','EVALUABLE',NULL,'texto exacto','2026-01-01 10:01:00'),
 ('C-3','E-1','N-3','EVALUABLE','MOD-1',NULL,'2026-01-01 10:02:00'),
 ('C-4','E-1','N-4','EVALUABLE',NULL,NULL,'2026-01-01 10:03:00');
INSERT INTO resultados_criterio VALUES
 ('R-2','C-2','VAL-1','2026-01-01 10:11:00',NULL),
 ('R-4','C-4','VAL-1','2026-01-01 10:13:00','motivo literal');
INSERT INTO trazas_inferencia VALUES
 ('T-3','C-3','EN_CURSO','2026-01-01 09:55:00',NULL),
 ('T-4','C-4','FINALIZADA','2026-01-01 09:56:00','2026-01-01 10:14:00');
INSERT INTO evaluaciones_reglas VALUES
 ('ER-3','T-3','G09-01','ABSTENCION','2026-01-01 10:04:00'),
 ('ER-4','T-4','G09-02','ACTIVA','2026-01-01 10:05:00');
SQL);

$originales = [
    'evaluacion_criterios' => filas($db, 'evaluacion_criterios', 'cod_evaluacion_criterio'),
    'resultados_criterio' => filas($db, 'resultados_criterio', 'cod_resultado_criterio'),
    'trazas_inferencia' => filas($db, 'trazas_inferencia', 'cod_traza_inferencia'),
    'evaluaciones_reglas' => filas($db, 'evaluaciones_reglas', 'cod_evaluacion_regla'),
];

$definicion = <<<'SQL'
CREATE TABLE evaluacion_criterios_v2 (
    cod_evaluacion_criterio TEXT PRIMARY KEY,
    cod_evaluacion_experta TEXT NOT NULL REFERENCES evaluaciones_expertas(cod_evaluacion_experta) ON DELETE RESTRICT,
    cod_nodo_criterio TEXT NOT NULL REFERENCES nodos_semanticos(cod_nodo_semantico) ON DELETE RESTRICT,
    estado_evaluabilidad TEXT NOT NULL,
    codigo_modificador_interpretacion TEXT,
    motivo_evaluabilidad TEXT,
    fecha_hora_determinacion TEXT NOT NULL,
    cod_resultado_criterio TEXT UNIQUE,
    cod_valor_resultado TEXT REFERENCES valores_semanticos(cod_valor_semantico) ON DELETE RESTRICT,
    fecha_hora_resultado TEXT,
    justificacion_resultado_original TEXT,
    cod_traza_inferencia TEXT UNIQUE,
    estado_traza TEXT,
    fecha_hora_inicio_traza TEXT,
    fecha_hora_fin_traza TEXT,
    UNIQUE(cod_evaluacion_experta, cod_nodo_criterio),
    CHECK ((cod_resultado_criterio IS NULL AND cod_valor_resultado IS NULL AND fecha_hora_resultado IS NULL AND justificacion_resultado_original IS NULL)
        OR (cod_resultado_criterio IS NOT NULL AND cod_valor_resultado IS NOT NULL AND fecha_hora_resultado IS NOT NULL)),
    CHECK ((cod_traza_inferencia IS NULL AND estado_traza IS NULL AND fecha_hora_inicio_traza IS NULL AND fecha_hora_fin_traza IS NULL)
        OR (cod_traza_inferencia IS NOT NULL AND estado_traza IS NOT NULL AND fecha_hora_inicio_traza IS NOT NULL))
);
SQL;

$copiar = <<<'SQL'
INSERT INTO evaluacion_criterios_v2
SELECT c.cod_evaluacion_criterio,c.cod_evaluacion_experta,c.cod_nodo_criterio,c.estado_evaluabilidad,
 c.codigo_modificador_interpretacion,c.motivo_evaluabilidad,c.fecha_hora_determinacion,
 r.cod_resultado_criterio,r.cod_valor_semantico,r.fecha_hora_determinacion,r.justificacion_original,
 t.cod_traza_inferencia,t.estado_inferencia,t.fecha_hora_inicio,t.fecha_hora_fin
FROM evaluacion_criterios c
LEFT JOIN resultados_criterio r ON r.cod_evaluacion_criterio=c.cod_evaluacion_criterio
LEFT JOIN trazas_inferencia t ON t.cod_evaluacion_criterio=c.cod_evaluacion_criterio;
SQL;

// La falla inyectada prueba que la proyección no deja media transición persistida.
$db->beginTransaction();
try {
    $db->exec($definicion);
    $db->exec($copiar);
    throw new RuntimeException('FALLO_INYECTADO');
} catch (RuntimeException $error) {
    exigir($error->getMessage() === 'FALLO_INYECTADO', 'Fallo de ensayo inesperado.');
    $db->rollBack();
}
exigir(filas($db, 'resultados_criterio', 'cod_resultado_criterio') === $originales['resultados_criterio'], 'Rollback modificó los resultados.');
exigir(filas($db, 'trazas_inferencia', 'cod_traza_inferencia') === $originales['trazas_inferencia'], 'Rollback modificó las trazas.');
exigir($db->query("SELECT count(*) FROM sqlite_master WHERE name='evaluacion_criterios_v2'")->fetchColumn() === 0, 'Quedó tabla parcial tras rollback.');

// En SQLite los FK se comprueban al final de la reconstrucción; PostgreSQL exige un ensayo distinto.
$db->exec('PRAGMA foreign_keys = OFF');
$db->beginTransaction();
try {
    $db->exec($definicion);
    $db->exec($copiar);
    $db->exec(<<<'SQL'
CREATE TABLE evaluaciones_reglas_v2 (
    cod_evaluacion_regla TEXT PRIMARY KEY,
    cod_traza_inferencia TEXT NOT NULL REFERENCES evaluacion_criterios_v2(cod_traza_inferencia) ON DELETE RESTRICT,
    cod_regla_experta TEXT NOT NULL REFERENCES reglas_expertas(cod_regla_experta) ON DELETE RESTRICT,
    estado_regla TEXT NOT NULL,
    fecha_hora_evaluacion TEXT NOT NULL,
    UNIQUE(cod_traza_inferencia, cod_regla_experta)
);
INSERT INTO evaluaciones_reglas_v2 SELECT * FROM evaluaciones_reglas;
DROP TABLE evaluaciones_reglas;
DROP TABLE resultados_criterio;
DROP TABLE trazas_inferencia;
DROP TABLE evaluacion_criterios;
ALTER TABLE evaluacion_criterios_v2 RENAME TO evaluacion_criterios;
ALTER TABLE evaluaciones_reglas_v2 RENAME TO evaluaciones_reglas;
SQL);
    exigir($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK rota tras la transición.');
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
} finally {
    $db->exec('PRAGMA foreign_keys = ON');
}

$reconstruccion = [
    'evaluacion_criterios' => [],
    'resultados_criterio' => [],
    'trazas_inferencia' => [],
    'evaluaciones_reglas' => filas($db, 'evaluaciones_reglas', 'cod_evaluacion_regla'),
];
foreach (filas($db, 'evaluacion_criterios', 'cod_evaluacion_criterio') as $fila) {
    $reconstruccion['evaluacion_criterios'][] = array_slice($fila, 0, 7, true);
    if ($fila['cod_resultado_criterio'] !== null) {
        $reconstruccion['resultados_criterio'][] = [
            'cod_resultado_criterio' => $fila['cod_resultado_criterio'],
            'cod_evaluacion_criterio' => $fila['cod_evaluacion_criterio'],
            'cod_valor_semantico' => $fila['cod_valor_resultado'],
            'fecha_hora_determinacion' => $fila['fecha_hora_resultado'],
            'justificacion_original' => $fila['justificacion_resultado_original'],
        ];
    }
    if ($fila['cod_traza_inferencia'] !== null) {
        $reconstruccion['trazas_inferencia'][] = [
            'cod_traza_inferencia' => $fila['cod_traza_inferencia'],
            'cod_evaluacion_criterio' => $fila['cod_evaluacion_criterio'],
            'estado_inferencia' => $fila['estado_traza'],
            'fecha_hora_inicio' => $fila['fecha_hora_inicio_traza'],
            'fecha_hora_fin' => $fila['fecha_hora_fin_traza'],
        ];
    }
}
foreach (array_keys($originales) as $tabla) {
    exigir($reconstruccion[$tabla] === $originales[$tabla], "No reconstruyó exactamente $tabla.");
}

falloSql($db, "INSERT INTO evaluacion_criterios(cod_evaluacion_criterio,cod_evaluacion_experta,cod_nodo_criterio,estado_evaluabilidad,fecha_hora_determinacion,cod_resultado_criterio) VALUES ('C-MAL','E-1','N-5','EVALUABLE','2026-01-01','R-MAL')");
falloSql($db, "INSERT INTO evaluacion_criterios(cod_evaluacion_criterio,cod_evaluacion_experta,cod_nodo_criterio,estado_evaluabilidad,fecha_hora_determinacion,cod_resultado_criterio,cod_valor_resultado,fecha_hora_resultado) VALUES ('C-MAL','E-1','N-5','EVALUABLE','2026-01-01','R-2','VAL-1','2026-01-01')");
falloSql($db, "INSERT INTO evaluacion_criterios(cod_evaluacion_criterio,cod_evaluacion_experta,cod_nodo_criterio,estado_evaluabilidad,fecha_hora_determinacion,cod_resultado_criterio,cod_valor_resultado,fecha_hora_resultado) VALUES ('C-MAL','E-1','N-5','EVALUABLE','2026-01-01','R-MAL','VAL-AJENO','2026-01-01')");
falloSql($db, "INSERT INTO evaluaciones_reglas VALUES ('ER-MAL','T-AUSENTE','G09-03','ACTIVA','2026-01-01')");
exigir($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK rota tras controles negativos.');

echo "PASS SQLite desechable: rollback, 4 criterios, 2 resultados, 2 trazas, 2 reglas, reconstrucción exacta y FK/checks.\n";
echo "LÍMITE: solo ensayo de dos consolidaciones; no prueba PostgreSQL, concurrencia, 4FN independiente ni el DTO compuesto. No autoriza DDL institucional.\n";
