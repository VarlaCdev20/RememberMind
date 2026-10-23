<?php

declare(strict_types=1);

/** Generador OFFLINE del contrato SQL cerrado. No es invocado por guardar/recuperar. */
require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/CasoCanonico.php';
require __DIR__.'/EntradasCompuestas.php';

use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use OrionFase02\CasoCanonico;
use OrionFase02\EntradasCompuestas;

function mapa(string $path): bool
{
    return (bool) preg_match('~/(vinculos|selectores|preguntas_declarativas_originales|vinculos_preguntas_actuales|dominios_revisados|mediciones_requeridas)$~', $path);
}
function lista(string $path): bool
{
    return (bool) preg_match('~/(capturas/capturas|respuestas_originales|motivos|valoraciones|soportes|procedencia_valoraciones|membresias_soportes|constancias_documentales|exclusiones|contratos_relaciones|modalidades|predicados|tablas/[^/]+)$~', $path);
}
function mezclar(?array $s, mixed $v, string $path): array
{
    if (! is_array($v)) {
        return $s ?? ['clase' => 'escalar'];
    }
    $coleccion = mapa($path) || lista($path);
    $s = isset($s['campos']) || isset($s['item']) ? $s : ($coleccion ? ['clase' => 'coleccion', 'modo' => mapa($path) ? 'mapa' : 'lista', 'item' => null] : ['clase' => 'objeto', 'campos' => []]);
    if ($s['clase'] === 'coleccion') {
        foreach ($v as $elemento) {
            $s['item'] = mezclar($s['item'], $elemento, $path.'/*');
        }
    } else {
        foreach ($v as $k => $valor) {
            $s['campos'][$k] = mezclar($s['campos'][$k] ?? null, $valor, $path.'/'.$k);
        }
    }

    return $s;
}

$fuente = json_decode(file_get_contents(dirname(__DIR__, 2).'/app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'), true, flags: JSON_THROW_ON_ERROR);
$estructura = null;
foreach ([[['COG-MEM'], false, false], [['COG-ATE'], false, false], [['COG-MEM', 'COG-ATE'], true, false], [[], false, true]] as [$dominios, $sinC03, $sinGobierno]) {
    $estructura = mezclar($estructura, EntradasCompuestas::proyectar((new CasoCanonico)->crear($dominios, $sinC03, $sinGobierno), $fuente), '');
}

// Estas declaraciones amplían el mapa estructural desde campos reales del contrato,
// no agregan datos clínicos al corpus ni convierten una fixture en un resultado.
$declaraciones = [];
$metadata = json_decode(file_get_contents($argv[1] ?? throw new RuntimeException('Indique metadata SQL READ ONLY de referencia.')), true, flags: JSON_THROW_ON_ERROR);
foreach (MotorExperto\dominio\conocimiento\PaqueteConocimiento::CLAVES as $tabla => $pk) {
    $row = [];
    foreach ($metadata['columnas'] as $c) {
        if ($c['table_name'] === $tabla) {
            $row[$c['column_name']] = null;
        }
    }
    foreach (['instrumental/conocimiento', 'petersen/gobierno/conocimiento'] as $p) {
        $declaraciones[$p.'/tablas/'.$tabla] = [$row];
    }
}
$q = array_fill_keys(['cod_pregunta', 'cod_instrumento', 'codigo', 'enunciado', 'dominio', 'tipo_respuesta', 'puntaje_maximo', 'orden', 'estado'], null);
$op = array_fill_keys(['cod_opcion', 'cod_pregunta', 'nombre', 'valor', 'puntaje', 'orden', 'estado'], null);
$a = array_fill_keys(['cod_aplicacion', 'cod_residente', 'cod_instrumento', 'codigo_instrumento', 'nombre_instrumento', 'version_instrumento', 'cod_personal', 'autor', 'fecha_hora', 'estado_original', 'edad_en_aplicacion', 'extraccion_adquirida'], null);
$a += ['fuente_edad' => array_fill_keys(['tabla', 'campo', 'registro', 'fecha_nacimiento', 'fecha_aplicacion'], null),
    'procedencia' => array_fill_keys(['cod_personal_aplicador', 'cod_usuario_registro', 'registrado_en', 'cod_documento_clinico', 'localizador_fuente', 'cod_aplicacion_anterior', 'estado_documento'], null),
    'documento_original' => array_fill_keys(['cod_documento_clinico', 'cod_residente', 'cod_personal', 'hash_archivo', 'estado', 'fecha_hora'], null),
    'atencion_original' => ['cod_atencion' => null, 'cod_residente' => null],
    'instrumento_actual' => array_fill_keys(['cod_instrumento', 'codigo', 'nombre', 'tipo', 'version', 'descripcion', 'puntaje_maximo', 'estado'], null),
    'aplicacion_actual' => array_fill_keys(['cod_aplicacion', 'cod_residente', 'cod_instrumento', 'cod_personal', 'cod_atencion', 'fecha_hora', 'puntaje_total', 'puntaje_maximo', 'clasificacion', 'interpretacion', 'estado', 'observacion', 'cod_usuario_registro', 'registrado_en', 'cod_documento_clinico', 'localizador_fuente', 'cod_aplicacion_anterior'], null),
    'perfil_captura_original' => ['id' => null, 'cod_instrumento' => null, 'codigo_instrumento' => null, 'version_instrumento' => null,
        'metodo' => null, 'identidad_instrumento' => array_fill_keys(['instrumento', 'forma', 'edicion_protocolo', 'idioma'], null),
        'selectores' => ['entrada' => ['cod_pregunta' => null, 'campo' => null]]],
    'respuestas_originales' => [array_fill_keys(['cod_respuesta', 'cod_aplicacion', 'cod_pregunta', 'cod_opcion', 'valor_numero', 'valor_texto', 'valor_logico', 'puntaje', 'observacion', 'codigo_pregunta', 'pregunta', 'dominio_original', 'tipo_respuesta', 'estado_pregunta_actual', 'coherente'], null) + ['pregunta_actual' => $q, 'opcion_actual' => $op]],
    'preguntas_declarativas_originales' => ['pregunta' => $q], 'vinculos_preguntas_actuales' => ['pregunta' => ['selector' => ['cod_pregunta' => null, 'campo' => null], 'pertenece_al_instrumento' => null, 'tiene_respuesta' => null, 'pregunta_actual' => $q]], 'motivos' => [null]];
$fuenteP = array_fill_keys(['tabla', 'registro', 'residente', 'autor', 'fecha', 'episodio', 'tipo', 'vigente'], null) + [
    'datos_originales' => array_fill_keys(['descripcion', 'dominio', 'actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente'], null) + ['captura_original' => $a]];
$declaraciones['instrumental/capturas/capturas'] = [$a];
$origenP = $fuenteP;
$origenP['datos_originales'] = [];
$declaraciones['petersen/valoraciones'] = [['elemento' => null, 'estado' => null, 'justificacion' => null, 'origen' => $origenP, 'soportes' => [$fuenteP],
    'comprobaciones' => array_fill_keys(['alcance_dominio', 'contexto_interpretado', 'cambio_respecto_previo', 'corroboracion_independiente', 'revision_integrada', 'oportunidad_adecuada', 'atribucion_revisada', 'revision_explicita', 'factores_pertinentes_revisados', 'impedimento_no_resuelto', 'suficiencia_conjunto', 'condiciones_revisadas', 'exclusiones_revisadas'], null), 'dominios_revisados' => ['COG-MEM' => null]]];
$declaraciones['petersen/adquisicion/exclusiones'] = [['valoracion' => null, 'elemento' => null, 'motivo' => null]];
$declaraciones['petersen/constancias_documentales'] = [array_fill_keys(['valoracion', 'papel', 'cod_documento_clinico', 'cod_residente', 'cod_personal', 'hash_archivo', 'fecha_hora', 'estado', 'localizador_fuente', 'verificado'], null)];
$declaraciones['instrumental/vinculos'] = ['mapeo' => ['cod_instrumento' => null, 'codigo_instrumento' => null, 'version_instrumento' => null, 'cod_pregunta' => null, 'campo' => null, 'componente' => null,
    'perfil_interpretativo' => ['codigo' => null, 'version' => null, 'componente' => null, 'instrumento' => ['codigo' => null, 'version' => null], 'metodo' => null, 'fuente' => null, 'predicados' => [null], 'mediciones_requeridas' => ['medida' => ['cod_pregunta' => null, 'campo' => null]]]]];
foreach (['instrumental/cabecera_conocimiento', 'petersen/gobierno/cabecera'] as $p) {
    $declaraciones[$p] = array_fill_keys(['cod_version_modelo', 'codigo_version', 'nombre', 'descripcion', 'estado', 'fecha_hora_creacion', 'fecha_hora_vigencia', 'fecha_hora_retiro', 'motivo_cambio', 'cod_version_anterior', 'cod_usuario_creacion', 'observacion'], null);
}
foreach (['instrumental/conocimiento/contratos_extraccion', 'petersen/gobierno/conocimiento/contratos_extraccion'] as $p) {
    $declaraciones[$p] = ['RESPUESTA_INSTITUCIONAL' => null, 'RESPUESTA_ORIGINAL' => null];
}
function agregar(array &$n, array $path, mixed $valor, string $base = ''): void
{
    if ($path === []) {
        $n = mezclar($n, $valor, $base);

        return;
    }
    $key = array_shift($path);
    $n['clase'] = 'objeto';
    $n['campos'][$key] ??= ['clase' => 'escalar'];
    agregar($n['campos'][$key], $path, $valor, $base.'/'.$key);
}
foreach ($declaraciones as $p => $v) {
    agregar($estructura, explode('/', $p), $v);
}
$modelos = $columnas = [];
$cuenta = 0;
function columna(string $p): string
{
    $name = trim(str_replace(['/', '-'], '_', $p), '_') ?: 'contenido';

    return strlen($name) > 40 ? substr($name, 0, 29).'_'.substr(hash('sha256', $name), 0, 10) : strtolower($name);
}
function construir(array $n, string $ruta, ?string $padre, string $nombre): string
{
    global $modelos, $columnas, $cuenta;
    // Las doce definiciones se comparten por grano, con rama I/P y FK de la misma evaluación.
    $knowledge = preg_match('~^(instrumental/conocimiento|petersen/gobierno/conocimiento)/tablas/([^/]+)$~', ltrim($ruta, '/'), $match);
    $id = $knowledge ? 'k_'.$match[2] : 'm'.str_pad((string) ++$cuenta, 3, '0', STR_PAD_LEFT);
    $tabla = $knowledge ? 'copia_'.$match[2] : ($padre === null ? 'entradas_compuestas' : columna($nombre).'_'.substr(hash('sha256', $ruta), 0, 6));
    if (isset($modelos[$id])) {
        $modelos[$id]['estructura'] = fusion($modelos[$id]['estructura'], $n);

        return $id;
    }
    $modelos[$id] = ['tabla' => $tabla, 'padre' => $padre, 'ruta' => $ruta, 'estructura' => []];
    $convertir = function (array $s, string $p, string $full) use (&$convertir, $id): array {
        global $columnas;
        $columnas[$p] = columna($p);
        if ($s['clase'] === 'coleccion') {
            $item = $s['item'] ?? ['clase' => 'escalar'];
            $s['modelo'] = construir($item, $full, $id, basename($full));
            $s['identidad'] = str_starts_with(ltrim($full, '/'), 'instrumental/conocimiento/tablas/') ? 'I' : (str_starts_with(ltrim($full, '/'), 'petersen/gobierno/conocimiento/tablas/') ? 'P' : 'C'.substr(hash('sha256', $full), 0, 10));
            unset($s['item']);
        } elseif ($s['clase'] === 'objeto') {
            ksort($s['campos']);
            foreach ($s['campos'] as $k => &$h) {
                $h = $convertir($h, $p.'/'.$k, $full.'/'.$k);
            }
            unset($h);
        }

        return $s;
    };
    $modelos[$id]['estructura'] = $convertir($n, '', $ruta);

    return $id;
}
function fusion(array $a, array $b): array
{
    if (($a['clase'] ?? '') === 'objeto' && ($b['clase'] ?? '') === 'objeto') {
        foreach ($b['campos'] as $k => $v) {
            $a['campos'][$k] = isset($a['campos'][$k]) ? fusion($a['campos'][$k], $v) : $v;
        }
    }

    return $a;
}
$raiz = construir($estructura, '', null, 'entradas');
// Normalizar columnas de modelos compartidos (los campos añadidos son escalares nominados).
foreach ($modelos as &$m) {
    $walk = function (array $n, string $p = '') use (&$walk, &$columnas): void {
        $columnas[$p] = columna($p);
        foreach ($n['campos'] ?? [] as $k => $h) {
            $walk($h, $p.'/'.$k);
        }
    };
    $walk($m['estructura']);
}
unset($m);
$mapa = ['contrato' => 'PROYECCION-COMPUESTA-F02-1', 'raiz' => $raiz, 'modelos' => $modelos, 'columnas' => $columnas];
file_put_contents(__DIR__.'/mapa-cerrado.php', "<?php\n\ndeclare(strict_types=1);\n\n// Contrato cerrado OFFLINE; no contiene registros ni salidas clínicas.\nreturn ".var_export($mapa, true).";\n");
echo count($modelos)." relaciones de entrada tipadas; contrato estático generado.\n";
