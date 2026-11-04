<?php

declare(strict_types=1);

/** Ensayo B02 aislado: preguntas originales sin respuesta en una relación de entradas tipadas. */
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');

function exigirPregunta(bool $ok, string $mensaje): void
{
    if (! $ok) {
        throw new RuntimeException($mensaje);
    }
}

function rechazarPregunta(PDO $db, string $sql): void
{
    try {
        $db->exec($sql);
    } catch (PDOException) {
        return;
    }

    throw new RuntimeException('La entrada incompatible fue admitida.');
}

$db->exec(<<<'SQL'
CREATE TABLE evaluaciones_expertas (cod_evaluacion_experta TEXT PRIMARY KEY,
 estado TEXT NOT NULL CHECK(estado IN ('ABIERTA','CERRADA')));
CREATE TABLE aplicaciones_instantanea (cod_aplicacion_instantanea TEXT PRIMARY KEY,
 cod_evaluacion_experta TEXT NOT NULL,
 UNIQUE(cod_aplicacion_instantanea,cod_evaluacion_experta),
 FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_expertas(cod_evaluacion_experta));
CREATE TABLE perfiles_instrumentales_versionados (cod_perfil_instrumental TEXT PRIMARY KEY,
 cod_evaluacion_experta TEXT NOT NULL, tipo TEXT NOT NULL CHECK(tipo IN ('CAPTURA','INTERPRETACION')),
 UNIQUE(cod_perfil_instrumental,cod_evaluacion_experta),
 FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_expertas(cod_evaluacion_experta));
CREATE TABLE preguntas_instrumento (cod_pregunta TEXT PRIMARY KEY, cod_instrumento TEXT NOT NULL,
 codigo TEXT NOT NULL, enunciado TEXT NOT NULL, dominio TEXT, tipo_respuesta TEXT NOT NULL,
 puntaje_maximo TEXT, orden INTEGER NOT NULL, estado TEXT NOT NULL,
 UNIQUE(cod_pregunta,cod_instrumento));
INSERT INTO evaluaciones_expertas VALUES ('E-1','ABIERTA');
INSERT INTO aplicaciones_instantanea VALUES ('A-1','E-1');
INSERT INTO perfiles_instrumentales_versionados VALUES ('P-1','E-1','CAPTURA'),('P-2','E-1','INTERPRETACION');
INSERT INTO preguntas_instrumento VALUES
 ('Q-1','I-1','ITEM-1','Pregunta uno','COG-MEM','NUMERO',NULL,1,'ACTIVA'),
 ('Q-2','I-1','ITEM-2','Pregunta dos',NULL,'TEXTO','2.00',2,'ACTIVA');
SQL);

$crear = <<<'SQL'
CREATE TABLE entradas_perfil_instrumental (
 cod_entrada_perfil TEXT PRIMARY KEY CHECK(length(cod_entrada_perfil) BETWEEN 1 AND 20),
 cod_evaluacion_experta TEXT NOT NULL REFERENCES evaluaciones_expertas(cod_evaluacion_experta),
 tipo_entrada TEXT NOT NULL CHECK(tipo_entrada IN ('SELECTOR','MEDIDA','PREGUNTA_DECLARATIVA')),
 cod_perfil_instrumental TEXT,
 cod_aplicacion_instantanea TEXT,
 nombre TEXT,
 selector_nulo INTEGER,
 identificador_pregunta_declarado TEXT,
 campo TEXT,
 cod_pregunta TEXT,
 cod_instrumento TEXT,
 codigo TEXT,
 enunciado TEXT,
 dominio TEXT,
 tipo_respuesta TEXT,
 puntaje_maximo TEXT,
 orden_pregunta INTEGER,
 estado_pregunta TEXT,
 orden_conservacion INTEGER,
 FOREIGN KEY(cod_perfil_instrumental,cod_evaluacion_experta)
   REFERENCES perfiles_instrumentales_versionados(cod_perfil_instrumental,cod_evaluacion_experta) ON DELETE RESTRICT,
 FOREIGN KEY(cod_aplicacion_instantanea,cod_evaluacion_experta)
   REFERENCES aplicaciones_instantanea(cod_aplicacion_instantanea,cod_evaluacion_experta) ON DELETE RESTRICT,
 FOREIGN KEY(cod_pregunta,cod_instrumento)
   REFERENCES preguntas_instrumento(cod_pregunta,cod_instrumento) ON DELETE RESTRICT,
 CHECK ((tipo_entrada IN ('SELECTOR','MEDIDA') AND cod_perfil_instrumental IS NOT NULL
    AND cod_aplicacion_instantanea IS NULL AND nombre IS NOT NULL AND selector_nulo IN (0,1)
    AND codigo IS NULL AND enunciado IS NULL AND dominio IS NULL AND tipo_respuesta IS NULL
    AND puntaje_maximo IS NULL AND orden_pregunta IS NULL AND estado_pregunta IS NULL
    AND orden_conservacion IS NULL
    AND ((selector_nulo=1 AND identificador_pregunta_declarado IS NULL AND cod_pregunta IS NULL
       AND cod_instrumento IS NULL AND campo IS NULL)
      OR (selector_nulo=0 AND identificador_pregunta_declarado IS NOT NULL
       AND campo IN ('valor_numero','valor_logico'))))
  OR (tipo_entrada='PREGUNTA_DECLARATIVA' AND cod_perfil_instrumental IS NULL
    AND cod_aplicacion_instantanea IS NOT NULL AND nombre IS NULL AND selector_nulo IS NULL
    AND identificador_pregunta_declarado IS NULL AND campo IS NULL
    AND cod_pregunta IS NOT NULL AND cod_instrumento IS NOT NULL
    AND codigo IS NOT NULL AND enunciado IS NOT NULL AND tipo_respuesta IS NOT NULL
    AND orden_pregunta>=0 AND estado_pregunta IS NOT NULL AND orden_conservacion>=0))
);
CREATE UNIQUE INDEX entrada_perfil_nombre ON entradas_perfil_instrumental(cod_perfil_instrumental,tipo_entrada,nombre)
 WHERE tipo_entrada IN ('SELECTOR','MEDIDA');
CREATE UNIQUE INDEX pregunta_aplicacion_codigo ON entradas_perfil_instrumental(cod_aplicacion_instantanea,cod_pregunta)
 WHERE tipo_entrada='PREGUNTA_DECLARATIVA';
CREATE UNIQUE INDEX pregunta_aplicacion_orden ON entradas_perfil_instrumental(cod_aplicacion_instantanea,orden_conservacion)
 WHERE tipo_entrada='PREGUNTA_DECLARATIVA';
CREATE TRIGGER entrada_tipo_perfil BEFORE INSERT ON entradas_perfil_instrumental
 WHEN NEW.tipo_entrada IN ('SELECTOR','MEDIDA') AND NOT EXISTS(
  SELECT 1 FROM perfiles_instrumentales_versionados p WHERE p.cod_perfil_instrumental=NEW.cod_perfil_instrumental
   AND p.cod_evaluacion_experta=NEW.cod_evaluacion_experta
   AND p.tipo=CASE NEW.tipo_entrada WHEN 'SELECTOR' THEN 'CAPTURA' ELSE 'INTERPRETACION' END)
 BEGIN SELECT RAISE(ABORT,'Tipo de perfil incompatible'); END;
CREATE TRIGGER entrada_cerrada BEFORE INSERT ON entradas_perfil_instrumental
 WHEN EXISTS(SELECT 1 FROM evaluaciones_expertas e WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND e.estado='CERRADA')
 BEGIN SELECT RAISE(ABORT,'Colección cerrada'); END;
CREATE TRIGGER entrada_inmutable_update BEFORE UPDATE ON entradas_perfil_instrumental
 BEGIN SELECT RAISE(ABORT,'Entrada histórica inmutable'); END;
CREATE TRIGGER entrada_inmutable_delete BEFORE DELETE ON entradas_perfil_instrumental
 BEGIN SELECT RAISE(ABORT,'Entrada histórica inmutable'); END;
SQL;

$db->beginTransaction();
try {
    $db->exec($crear);
    $db->exec("INSERT INTO entradas_perfil_instrumental(cod_entrada_perfil,cod_evaluacion_experta,tipo_entrada,cod_aplicacion_instantanea,cod_pregunta,cod_instrumento,codigo,enunciado,tipo_respuesta,orden_pregunta,estado_pregunta,orden_conservacion)
        VALUES ('EP-0','E-1','PREGUNTA_DECLARATIVA','A-1','Q-1','I-1','ITEM-1','Pregunta uno','NUMERO',1,'ACTIVA',0)");
    throw new RuntimeException('FALLO_INYECTADO');
} catch (RuntimeException $error) {
    exigirPregunta($error->getMessage() === 'FALLO_INYECTADO', 'Fallo de prueba inesperado.');
    $db->rollBack();
}
exigirPregunta((int) $db->query("SELECT COUNT(*) FROM sqlite_master WHERE name='entradas_perfil_instrumental'")->fetchColumn() === 0,
    'El rollback dejó una tabla parcial.');

$db->exec($crear);
$db->exec(<<<'SQL'
INSERT INTO entradas_perfil_instrumental VALUES
 ('EP-1','E-1','SELECTOR','P-1',NULL,'ensayo_1',0,'Q-1','valor_numero','Q-1','I-1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
 ('EP-2','E-1','MEDIDA','P-2',NULL,'recuerdo',0,'Q-1','valor_numero','Q-1','I-1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
 ('EP-3','E-1','PREGUNTA_DECLARATIVA',NULL,'A-1',NULL,NULL,NULL,NULL,'Q-1','I-1','ITEM-1','Pregunta uno','COG-MEM','NUMERO',NULL,1,'ACTIVA',0),
 ('EP-4','E-1','PREGUNTA_DECLARATIVA',NULL,'A-1',NULL,NULL,NULL,NULL,'Q-2','I-1','ITEM-2','Pregunta dos',NULL,'TEXTO','2.00',2,'ACTIVA',1);
SQL);

$original = [
    'Q-1' => ['cod_pregunta' => 'Q-1', 'cod_instrumento' => 'I-1', 'codigo' => 'ITEM-1',
        'enunciado' => 'Pregunta uno', 'dominio' => 'COG-MEM', 'tipo_respuesta' => 'NUMERO',
        'puntaje_maximo' => null, 'orden' => 1, 'estado' => 'ACTIVA'],
    'Q-2' => ['cod_pregunta' => 'Q-2', 'cod_instrumento' => 'I-1', 'codigo' => 'ITEM-2',
        'enunciado' => 'Pregunta dos', 'dominio' => null, 'tipo_respuesta' => 'TEXTO',
        'puntaje_maximo' => '2.00', 'orden' => 2, 'estado' => 'ACTIVA'],
];
$leer = static function (PDO $db): array {
    $preguntas = [];
    $filas = $db->query("SELECT * FROM entradas_perfil_instrumental WHERE tipo_entrada='PREGUNTA_DECLARATIVA' ORDER BY orden_conservacion")
        ->fetchAll(PDO::FETCH_ASSOC);
    foreach ($filas as $fila) {
        $preguntas[$fila['cod_pregunta']] = [
            'cod_pregunta' => $fila['cod_pregunta'], 'cod_instrumento' => $fila['cod_instrumento'],
            'codigo' => $fila['codigo'], 'enunciado' => $fila['enunciado'], 'dominio' => $fila['dominio'],
            'tipo_respuesta' => $fila['tipo_respuesta'], 'puntaje_maximo' => $fila['puntaje_maximo'],
            'orden' => (int) $fila['orden_pregunta'], 'estado' => $fila['estado_pregunta'],
        ];
    }

    return $preguntas;
};
exigirPregunta($leer($db) === $original, 'La pregunta sin respuesta o sus NULL no se reconstruyeron.');
exigirPregunta((int) $db->query("SELECT COUNT(*) FROM entradas_perfil_instrumental WHERE tipo_entrada='PREGUNTA_DECLARATIVA'")->fetchColumn() === 2,
    'No se conservaron las dos preguntas seleccionadas.');
$db->exec("UPDATE preguntas_instrumento SET enunciado='Edición posterior' WHERE cod_pregunta='Q-1'");
exigirPregunta($leer($db) === $original, 'La edición posterior alteró la pregunta adquirida.');
rechazarPregunta($db, "INSERT INTO entradas_perfil_instrumental(cod_entrada_perfil,cod_evaluacion_experta,tipo_entrada,cod_perfil_instrumental,nombre,selector_nulo,identificador_pregunta_declarado,campo,cod_pregunta,cod_instrumento) VALUES ('EP-X','E-1','MEDIDA','P-1','ajena',0,'Q-1','valor_numero','Q-1','I-1')");
rechazarPregunta($db, "INSERT INTO entradas_perfil_instrumental(cod_entrada_perfil,cod_evaluacion_experta,tipo_entrada,cod_aplicacion_instantanea,cod_pregunta,cod_instrumento,codigo,enunciado,tipo_respuesta,orden_pregunta,estado_pregunta,orden_conservacion) VALUES ('EP-X','E-1','PREGUNTA_DECLARATIVA','A-1','Q-1','I-1','X','X','NUMERO',1,'ACTIVA',2)");
$db->exec("UPDATE evaluaciones_expertas SET estado='CERRADA' WHERE cod_evaluacion_experta='E-1'");
rechazarPregunta($db, "INSERT INTO entradas_perfil_instrumental(cod_entrada_perfil,cod_evaluacion_experta,tipo_entrada,cod_aplicacion_instantanea,cod_pregunta,cod_instrumento,codigo,enunciado,tipo_respuesta,orden_pregunta,estado_pregunta,orden_conservacion) VALUES ('EP-X','E-1','PREGUNTA_DECLARATIVA','A-1','Q-1','I-1','X','X','NUMERO',1,'ACTIVA',2)");
rechazarPregunta($db, "UPDATE entradas_perfil_instrumental SET enunciado='Cambiado' WHERE cod_entrada_perfil='EP-3'");
exigirPregunta($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK inválida.');

echo "PASS B02 SQLite desechable: selector, medida y dos preguntas adquiridas sin respuesta; NULL, orden, original mutado, rollback y cierre.\n";
echo "LÍMITE: prueba de grano parcial; falta el DTO compuesto, PostgreSQL, consumidores, concurrencia y 4FN independiente.\n";
