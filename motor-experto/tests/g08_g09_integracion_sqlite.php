<?php
declare(strict_types=1);

/**
 * Integración sin Laravel: esquema operacional proyectado a SQLite :memory:.
 * Sólo fixtures SIM-/SINTETICO-. Cero reglas clínicas o BDD externa.
 */
spl_autoload_register(static function(string $class): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($class, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    }
});

use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;
use MotorExperto\investigacion\g09\PerfilInstrumentalG08Experimental as Perfil;
use MotorExperto\investigacion\g09\LectorSQLiteG08Experimental as Lector;
use MotorExperto\investigacion\g09\EjecutorPerfilG09Experimental as Ejecutor;

function exigir(bool $condicion, string $mensaje): void {
    if (!$condicion) throw new RuntimeException($mensaje);
}
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Columnas y nombres corresponden a las migraciones operativas 001011, 001057–001060.
foreach ([
    'CREATE TABLE instrumentos (
        cod_instrumento TEXT PRIMARY KEY, codigo TEXT, nombre TEXT, tipo TEXT,
        version TEXT, descripcion TEXT, puntaje_maximo NUMERIC, estado TEXT)',
    'CREATE TABLE aplicaciones_instrumento (
        cod_aplicacion TEXT PRIMARY KEY, cod_instrumento TEXT, cod_residente TEXT,
        cod_personal TEXT, cod_atencion TEXT, fecha_hora TEXT, puntaje_total NUMERIC,
        puntaje_maximo NUMERIC, clasificacion TEXT, interpretacion TEXT,
        estado TEXT, observacion TEXT)',
    'CREATE TABLE preguntas_instrumento (
        cod_pregunta TEXT PRIMARY KEY, cod_instrumento TEXT, codigo TEXT,
        enunciado TEXT, dominio TEXT, tipo_respuesta TEXT, puntaje_maximo NUMERIC,
        orden INTEGER, estado TEXT)',
    'CREATE TABLE opciones_pregunta (
        cod_opcion TEXT PRIMARY KEY, cod_pregunta TEXT, nombre TEXT, valor TEXT,
        puntaje NUMERIC, orden INTEGER, estado TEXT)',
    'CREATE TABLE respuestas_instrumento (
        cod_respuesta TEXT PRIMARY KEY, cod_aplicacion TEXT, cod_pregunta TEXT,
        cod_opcion TEXT, valor_numero NUMERIC, valor_texto TEXT,
        valor_logico INTEGER, puntaje NUMERIC, observacion TEXT)',
] as $sql) $db->exec($sql);

$insertI = $db->prepare("INSERT INTO instrumentos (cod_instrumento, codigo, nombre, tipo, version, estado) VALUES (?,?,?,?,?,?)");
$insertA = $db->prepare("INSERT INTO aplicaciones_instrumento (cod_aplicacion, cod_instrumento, cod_residente, cod_personal, fecha_hora, estado) VALUES (?,?,?,?,?,?)");
$insertP = $db->prepare("INSERT INTO preguntas_instrumento (cod_pregunta,cod_instrumento,codigo,enunciado,dominio,tipo_respuesta,orden,estado) VALUES (?,?,?,?,?,?,?,?)");
$insertR = $db->prepare("INSERT INTO respuestas_instrumento (cod_respuesta,cod_aplicacion,cod_pregunta,valor_texto) VALUES (?,?,?,?)");
$tareas = [];
$pruebas = 0;
foreach (Catalogo::todas() as $id => $meta) {
    $perfil = Perfil::paraRegla($id);
    $componente = $meta['componente'];
    $aplicacion = 'SIM-AP-'.$componente;
    $pregunta = array_key_first($perfil['preguntas_requeridas']);
    $insertI->execute([$perfil['instrumento'], $perfil['instrumento'], 'FICTICIO', 'TEST_ONLY', $perfil['version_instrumento'], 'SIMULADO']);
    $insertA->execute([$aplicacion, $perfil['instrumento'], 'SINTETICO-01', 'SIM-MED', '2026-10-16 10:30:00', 'COMPLETA']);
    $insertP->execute([$pregunta, $perfil['instrumento'], $pregunta, 'PREGUNTA FICTICIA', $componente, 'TEXTO', 1, 'ACTIVA']);
    $insertR->execute(['SIM-R-'.$componente, $aplicacion, $pregunta, 'FALLO_SIMULADO']);
    $tareas[] = ['regla' => $id, 'aplicacion' => $aplicacion];
}

$base = [
    'uso' => 'TEST_ONLY', 'aislado' => true,
    'residente_ficticio' => 'SINTETICO-01',
    'evaluacion' => 'SIM-EV-01',
    'version' => Catalogo::VERSION,
    'fecha_corte' => '2026-10-17',
    'contexto_suficiente' => true,
];

$lector = new Lector($db);
$ejecutor = new Ejecutor($lector);
$perfil = $ejecutor->evaluar($base, $tareas);
exigir(count($perfil['perfil']) === 4, 'No incluye cuatro criterios');
exigir($perfil['clasificacion_dcl'] === null, 'DCL ficticio improcedente');
foreach ($perfil['perfil'] as $criterio => $reporte) {
    exigir($reporte['resultado']['estado_tecnico'] === 'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION', $criterio.': perdió hallazgos simultáneos');
    exigir(count($reporte['resultado']['consecuencias_candidatas']) >= 2, $criterio.': no hay soportes múltiples');
    exigir($reporte['informe']['estado_tecnico'] === $reporte['resultado']['estado_tecnico'], $criterio.': informe incoherente');
    $pruebas += 3;
}
foreach ($tareas as $tarea) {
    $r = $ejecutor->evaluar($base, [$tarea]);
    $criterio = Catalogo::obtener($tarea['regla'])['criterio'];
    exigir($r['perfil'][$criterio]['resultado']['estado_tecnico'] === 'HALLAZGO_ACOTADO', $tarea['regla'].': regla no ejecutada');
    exigir(str_contains($r['perfil'][$criterio]['informe']['como_se_llego'], $tarea['regla']), $tarea['regla'].': traza incompleta');
    $pruebas += 2;
}

$vacío = $ejecutor->evaluar($base, []);
foreach ($vacío['perfil'] as $dom) {
    exigir($dom['resultado']['estado_tecnico'] === 'SIN_EVIDENCIA_ADMISIBLE', 'Ausencia de tareas convertida en evidencia');
    $pruebas++;
}
$primer = $tareas[0];
$primerP = Perfil::paraRegla($primer['regla']);
$pregunta = array_key_first($primerP['preguntas_requeridas']);
$db->prepare("UPDATE respuestas_instrumento SET valor_texto = ? WHERE cod_pregunta = ?")->execute(['SIN_HALLAZGO_SIMULADO', $pregunta]);
$negativo = $ejecutor->evaluar($base, [$primer]);
$criterio = Catalogo::obtener($primer['regla'])['criterio'];
exigir($negativo['perfil'][$criterio]['resultado']['estado_tecnico'] === 'EVALUABLE_SIN_SOPORTE', 'No separó falta de dificultad de ausencia de evidencia');
exigir(count($negativo['perfil'][$criterio]['resultado']['traza']) === 1, 'Tarea interpretable sin traza');
$pruebas += 2;

// La fuente usa fecha_hora; una evaluación del mismo día debe admitirse
// si el corte es diario o si el instante está antes de la hora de corte.
$db->prepare("UPDATE respuestas_instrumento SET valor_texto = ? WHERE cod_pregunta = ?")->execute(['FALLO_SIMULADO', $pregunta]);
$mismoDia = $base; $mismoDia['fecha_corte'] = '2026-10-16';
$r = $ejecutor->evaluar($mismoDia, [$primer]);
exigir($r['perfil'][$criterio]['resultado']['estado_tecnico'] === 'HALLAZGO_ACOTADO', 'Excluyó erróneamente la evidencia del día de corte');
$pruebas++;
$previoHora = $base; $previoHora['fecha_corte'] = '2026-10-16 10:00:00';
$r = $ejecutor->evaluar($previoHora, [$primer]);
exigir($r['perfil'][$criterio]['resultado']['estado_tecnico'] === 'SIN_EVIDENCIA_ADMISIBLE', 'Admitió registro futuro dentro del mismo día');
$pruebas++;
$despuesHora = $base; $despuesHora['fecha_corte'] = '2026-10-16 11:00:00';
$r = $ejecutor->evaluar($despuesHora, [$primer]);
exigir($r['perfil'][$criterio]['resultado']['estado_tecnico'] === 'HALLAZGO_ACOTADO', 'Excluyó registro anterior a hora de corte');
$pruebas++;

$malCorte = $base; $malCorte['fecha_corte'] = '2026-10-15';
$r = $ejecutor->evaluar($malCorte, [$primer]);
exigir($r['perfil'][$criterio]['resultado']['estado_tecnico'] === 'SIN_EVIDENCIA_ADMISIBLE', 'Aceptó registro posterior a corte');
$pruebas++;

try {
    $lector->leer($primer['aplicacion'], 'SINTETICO-OTRO');
    throw new RuntimeException('Se permitió cruce de residentes');
} catch (DomainException $esperada) { $pruebas++; }

try {
    $ejecutor->evaluar(array_merge($base, ['uso'=>'ASISTENCIAL']), [$primer]);
    throw new RuntimeException('Se permitió uso asistencial');
} catch (DomainException $esperada) { $pruebas++; }

try {
    $ejecutor->evaluar($base, [$primer,$primer]);
    throw new RuntimeException('Se permitió reutilizar aplicación');
} catch (DomainException $esperada) { $pruebas++; }

exigir($ejecutor->evaluar($base, []) === $ejecutor->evaluar($base, []), 'No es reproducible');
$pruebas++;
echo 'PASS: '.$pruebas." aserciones G08/G09 SQLite :memory:, 15 reglas/4 criterios, sin uso clínico.\n";
