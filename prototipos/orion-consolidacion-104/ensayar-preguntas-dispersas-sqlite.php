<?php

declare(strict_types=1);

// Ensayo B02 ampliado: forma histórica dispersa, clave de mapa y NULL explícito.
// Usa un DTO sintético vigente de tests; SQLite vive solo en memoria.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require dirname(__DIR__, 2).'/tests/Unit/ReproduccionFuenteNominalTest.php';
require dirname(__DIR__, 2).'/tests/Unit/InstantaneaInstrumentalCompletaTest.php';

use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as Canonica;

function exigirDispersa(bool $ok, string $mensaje): void
{
    if (! $ok) {
        throw new RuntimeException($mensaje);
    }
}

$instantanea = \Tests\Unit\ReproduccionFuenteNominalTest::ausenciaHistorica();
$historicas = $instantanea->contenido['capturas']['capturas'][0]['preguntas_declarativas_originales'];
exigirDispersa(count($historicas) === 1, 'La muestra histórica cambió.');
$ampliadas = $historicas + [
    'ALIAS-Q2' => ['cod_pregunta' => 'PREG-2', 'cod_instrumento' => 'INSTR-OBS',
        'tipo_respuesta' => 'TEXTO', 'enunciado' => null],
    0 => ['cod_pregunta' => 'PREG-3', 'cod_instrumento' => 'INSTR-OBS', 'tipo_respuesta' => 'OPCION'],
];
$testV3 = new \Tests\Unit\InstantaneaInstrumentalCompletaTest('test_recalcula_brutos_normalizacion_red_y_cinco_dominios_desde_un_solo_dto');
$dtoV3 = (new ReflectionMethod($testV3, 'crear'))->invoke($testV3, [
    'preguntas_declarativas_originales' => ['MAP-Q' => [
        'cod_pregunta' => 'Q-UNANSWERED', 'cod_instrumento' => 'INSTR-NUMERICO', 'tipo_respuesta' => 'OPCION',
    ]],
]);
$preguntasV3 = $dtoV3->contenido['capturas']['capturas'][1]['preguntas_declarativas_originales'];
exigirDispersa(count($preguntasV3) === 1, 'La muestra institucional V3 cambió.');
$campos = ['cod_pregunta','cod_instrumento','codigo','enunciado','dominio',
    'tipo_respuesta','puntaje_maximo','orden','estado'];

$pdo = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->exec(<<<'SQL'
CREATE TABLE evaluaciones(codigo TEXT PRIMARY KEY, cerrada INTEGER NOT NULL DEFAULT 0 CHECK(cerrada IN (0,1)));
CREATE TABLE aplicaciones(evaluacion TEXT NOT NULL REFERENCES evaluaciones(codigo), codigo TEXT NOT NULL,
 PRIMARY KEY(evaluacion,codigo));
CREATE TABLE originales(cod_pregunta TEXT NOT NULL, cod_instrumento TEXT NOT NULL,
 PRIMARY KEY(cod_pregunta,cod_instrumento));
CREATE TABLE entradas_perfil_instrumental (
 codigo TEXT PRIMARY KEY,
 evaluacion TEXT NOT NULL,
 cod_aplicacion TEXT NOT NULL,
 tipo_entrada TEXT NOT NULL CHECK(tipo_entrada='PREGUNTA_DECLARATIVA'),
 tipo_clave TEXT NOT NULL CHECK(tipo_clave IN ('TEXTO','ENTERO')),
 clave_mapa TEXT NOT NULL,
 mascara_presencia INTEGER NOT NULL CHECK(mascara_presencia BETWEEN 0 AND 511),
 orden_conservacion INTEGER NOT NULL CHECK(orden_conservacion>=0),
 cod_pregunta TEXT NOT NULL,
 cod_instrumento TEXT NOT NULL,
 codigo_pregunta TEXT,
 enunciado TEXT,
 dominio TEXT,
 tipo_respuesta TEXT,
 puntaje_maximo TEXT,
 orden_pregunta INTEGER,
 estado TEXT,
 UNIQUE(evaluacion,cod_aplicacion,tipo_clave,clave_mapa),
 UNIQUE(evaluacion,cod_aplicacion,orden_conservacion),
 FOREIGN KEY(evaluacion,cod_aplicacion) REFERENCES aplicaciones(evaluacion,codigo),
 FOREIGN KEY(cod_pregunta,cod_instrumento) REFERENCES originales(cod_pregunta,cod_instrumento),
 CHECK ((mascara_presencia & 3)=3),
 CHECK ((mascara_presencia & 4)!=0 OR codigo_pregunta IS NULL),
 CHECK ((mascara_presencia & 8)!=0 OR enunciado IS NULL),
 CHECK ((mascara_presencia & 16)!=0 OR dominio IS NULL),
 CHECK ((mascara_presencia & 32)!=0 OR tipo_respuesta IS NULL),
 CHECK ((mascara_presencia & 64)!=0 OR puntaje_maximo IS NULL),
 CHECK ((mascara_presencia & 128)!=0 OR orden_pregunta IS NULL),
 CHECK ((mascara_presencia & 256)!=0 OR estado IS NULL)
);
CREATE TRIGGER entrada_cerrada BEFORE INSERT ON entradas_perfil_instrumental
WHEN (SELECT cerrada FROM evaluaciones WHERE codigo=NEW.evaluacion)=1
BEGIN SELECT RAISE(ABORT,'evaluacion cerrada'); END;
CREATE TRIGGER entrada_inmutable BEFORE UPDATE ON entradas_perfil_instrumental
BEGIN SELECT RAISE(ABORT,'entrada inmutable'); END;
CREATE TRIGGER entrada_no_borrable BEFORE DELETE ON entradas_perfil_instrumental
BEGIN SELECT RAISE(ABORT,'entrada inmutable'); END;
INSERT INTO evaluaciones(codigo) VALUES ('E-SINTETICA'),('E-V3');
INSERT INTO aplicaciones(evaluacion,codigo) VALUES ('E-SINTETICA','APP-AUSENCIA'),('E-V3','APP-NUMERICA');
INSERT INTO originales(cod_pregunta,cod_instrumento) VALUES
 ('PREG-AUSENTE','INSTR-OBS'),('PREG-2','INSTR-OBS'),('PREG-3','INSTR-OBS'),
 ('Q-UNANSWERED','INSTR-NUMERICO');
SQL);

$insertar = $pdo->prepare('INSERT INTO entradas_perfil_instrumental VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
// La clave del mapa puede diferir del código de pregunta; el orden se conserva aparte.
foreach ([
    ['E-SINTETICA','APP-AUSENCIA',$ampliadas],
    ['E-V3','APP-NUMERICA',$preguntasV3],
] as [$evaluacion,$aplicacion,$preguntas]) {
    $orden = 0;
    foreach ($preguntas as $clave => $pregunta) {
        $mascara = 0;
        foreach ($campos as $bit => $campo) {
            if (array_key_exists($campo, $pregunta)) {
                $mascara |= 1 << $bit;
            }
        }
        $insertar->execute([$evaluacion.'-Q-'.$orden,$evaluacion,$aplicacion,'PREGUNTA_DECLARATIVA',
            is_int($clave) ? 'ENTERO' : 'TEXTO',(string) $clave,$mascara,$orden,$pregunta['cod_pregunta'],$pregunta['cod_instrumento'],
            $pregunta['codigo'] ?? null,$pregunta['enunciado'] ?? null,$pregunta['dominio'] ?? null,
            $pregunta['tipo_respuesta'] ?? null,$pregunta['puntaje_maximo'] ?? null,
            $pregunta['orden'] ?? null,$pregunta['estado'] ?? null]);
        $orden++;
    }
}

$leer = static function (PDO $pdo, array $campos, string $evaluacion): array {
    $r = [];
    $consulta = $pdo->prepare('SELECT * FROM entradas_perfil_instrumental WHERE evaluacion=? ORDER BY orden_conservacion');
    $consulta->execute([$evaluacion]);
    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $q = [];
        $valores = [$fila['cod_pregunta'],$fila['cod_instrumento'],$fila['codigo_pregunta'],
            $fila['enunciado'],$fila['dominio'],$fila['tipo_respuesta'],$fila['puntaje_maximo'],
            $fila['orden_pregunta'],$fila['estado']];
        foreach ($campos as $bit => $campo) {
            if (((int) $fila['mascara_presencia'] & (1 << $bit)) !== 0) {
                $q[$campo] = $campo === 'orden' && $valores[$bit] !== null ? (int) $valores[$bit] : $valores[$bit];
            }
        }
        $clave = $fila['tipo_clave'] === 'ENTERO' ? (int) $fila['clave_mapa'] : $fila['clave_mapa'];
        $r[$clave] = $q;
    }

    return $r;
};
exigirDispersa(Canonica::ordenar($leer($pdo,$campos,'E-SINTETICA')) === Canonica::ordenar($ampliadas), 'Se perdió una clave, ausencia, NULL o tipo.');
exigirDispersa(Canonica::ordenar($leer($pdo,$campos,'E-V3')) === Canonica::ordenar($preguntasV3), 'La pregunta V3 no se recuperó.');
exigirDispersa(Canonica::ordenar($leer($pdo,$campos,'E-SINTETICA')['PREG-AUSENTE']) === Canonica::ordenar($historicas['PREG-AUSENTE']),
    'El DTO histórico real no se reconstruyó.');
exigirDispersa(! array_key_exists('enunciado',$historicas['PREG-AUSENTE'])
    && array_key_exists('enunciado',$ampliadas['ALIAS-Q2']) && $ampliadas['ALIAS-Q2']['enunciado'] === null,
    'La prueba no distingue ausencia y NULL.');
exigirDispersa(array_key_exists(0,$leer($pdo,$campos,'E-SINTETICA')), 'Se perdió la clave entera.');

$pdo->exec("UPDATE evaluaciones SET cerrada=1 WHERE codigo='E-SINTETICA'");
foreach ([
    "INSERT INTO entradas_perfil_instrumental(codigo,evaluacion,cod_aplicacion,tipo_entrada,tipo_clave,clave_mapa,mascara_presencia,orden_conservacion,cod_pregunta,cod_instrumento) VALUES ('Q-TARDE','E-SINTETICA','APP-AUSENCIA','PREGUNTA_DECLARATIVA','TEXTO','TARDE',3,9,'PREG-2','INSTR-OBS')",
    "UPDATE entradas_perfil_instrumental SET enunciado='CAMBIO' WHERE codigo='E-SINTETICA-Q-0'",
    "DELETE FROM entradas_perfil_instrumental WHERE codigo='E-SINTETICA-Q-0'",
] as $sql) {
    try {
        $pdo->exec($sql);
        throw new RuntimeException('Se modificó la instantánea después del cierre.');
    } catch (PDOException) {
        // Triggers de cierre e inmutabilidad.
    }
}

echo "PASS B02 disperso SQLite: preguntas V1/V3 sin respuesta, claves de texto/entera, ausente/NULL y cierre.\n";
echo "LÍMITE: no demuestra el DTO institucional completo, PostgreSQL ni la migración de 104 tablas.\n";
