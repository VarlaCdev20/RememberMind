<?php

declare(strict_types=1);

// Compilación OFFLINE de una referencia nominal completa del corpus. No es DDL institucional.
require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/ContenidoCompleto.php';
require __DIR__.'/CasosCompletos.php';
require dirname(__DIR__).'/orion-conservacion-0_2/CasoCanonico.php';
require dirname(__DIR__).'/orion-conservacion-0_2/VariantesCanonicas.php';

use OrionNominal\CasosCompletos;
use OrionNominal\ContenidoCompleto;

$check = ($argv[1] ?? null) === '--check';
if (count($argv) > 2 || (isset($argv[1]) && ! $check)) {
    throw new RuntimeException('Uso: php compilar-contenido-completo.php [--check]');
}
function modoCompleto(string $ruta): ?string
{
    $nombre = basename($ruta);

    return in_array($nombre, ['perfil', 'dominios', 'componentes', 'conclusiones_candidatas', 'valores_y_evidencias',
        'unidades_por_componente', 'evaluaciones_unidad', 'rutas_reglas', 'preguntas_declarativas_originales',
        'vinculos_preguntas_actuales', 'selectores', 'vinculos', 'dominios_revisados', 'mediciones_requeridas',
        'fuentes_entradas', 'estados_derivados', 'calculos', 'archivos'], true) ? 'mapa' : null;
}
function acumularCompleto(?array $s, mixed $v, string $ruta): array
{
    $s ??= ['clase' => 'vacia'];
    if ($v === null) {
        return $s;
    }
    if (! is_array($v)) {
        if (! in_array($s['clase'], ['vacia', 'escalar'], true)) {
            throw new RuntimeException('Unión de objeto y escalar no declarada: '.$ruta);
        }
        $tipo = match (true) {
            is_string($v) => 'STRING', is_int($v) => 'INT', is_float($v) => 'FLOAT', is_bool($v) => 'BOOL',
            default => throw new RuntimeException('Valor no plano en corpus técnico.'),
        };
        $s = ['clase' => 'escalar', 'tipos' => array_values(array_unique([...($s['tipos'] ?? []), $tipo]))];
        sort($s['tipos']);

        return $s;
    }
    if ($v === [] && $s['clase'] === 'vacia') {
        $s['arreglo_vacio'] = true;

        return $s;
    }
    if ($v === []) {
        return $s;
    }
    $modo = modoCompleto($ruta) ?? (array_is_list($v) ? 'lista' : null);
    $clase = $modo === null ? 'objeto' : 'coleccion';
    if ($s['clase'] === 'vacia') {
        $s = $clase === 'objeto' ? ['clase' => 'objeto', 'campos' => []]
            : ['clase' => 'coleccion', 'modo' => $modo, 'item' => null];
    }
    if ($s['clase'] !== $clase || ($clase === 'coleccion' && $s['modo'] !== $modo)) {
        throw new RuntimeException('Forma no uniforme en referencia nominal: '.$ruta);
    }
    if ($clase === 'coleccion') {
        foreach ($v as $hijo) {
            $s['item'] = acumularCompleto($s['item'], $hijo, $ruta.'/*');
        }
    } else {
        foreach ($v as $k => $hijo) {
            $s['campos'][$k] = acumularCompleto($s['campos'][$k] ?? null, $hijo, $ruta.'/'.$k);
        }
        ksort($s['campos']);
    }

    return $s;
}
function columnaCompleta(string $ruta): string
{
    $ruta = str_replace(['/instrumental/contenido', '/petersen/contenido'], ['/i', '/p'], $ruta);
    $nombre = trim(str_replace(['/', '-'], '_', $ruta), '_') ?: 'contenido';
    $nombre = str_replace(['perfil_instrumental', 'comprobacion_adquisicion', 'cabecera_conocimiento',
        'condiciones_sensoriales_motoras_compatibles', 'normalizadores_referencia_disponibles',
        'calculos_descriptivos_disponibles', 'fecha_hora', 'conocimiento', 'contenido', 'salida', 'resultado', 'codigo_'],
        ['perfil', 'adquisicion', 'cabecera', 'condiciones_sensoriales', 'normalizadores', 'calculos', 'fecha', 'k', 'dto', 's', 'r', 'clave_'], $nombre);
    if (strlen($nombre) > 49) {
        $partes = explode('_', $nombre);
        $nombre = implode('_', array_map(static fn ($s) => strlen($s) > 3 ? substr($s, 0, 3) : $s, $partes));
    }
    if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,48}$/D', $nombre)) {
        throw new RuntimeException('Nombre físico nominal pendiente: '.$ruta);
    }

    return strtolower($nombre);
}
$estructura = null;
foreach (CasosCompletos::casos() as $dto) {
    $estructura = acumularCompleto($estructura, ContenidoCompleto::proyectar($dto->contenido), '');
}
// Forma del manifiesto técnico definida en ReleaseMotorInstrumental, sin crear archivos de pacientes.
$estructura['campos']['release'] = acumularCompleto(null, ['contrato' => '', 'revision_base' => '', 'php_requerido' => '',
    'incluye_datos_clinicos' => false, 'huella' => '', 'archivos' => ['ruta' => '']], '/release');
$modelos = [];
$construir = function (array $s, string $ruta, ?string $padre) use (&$construir, &$modelos): string {
    $id = 'c'.str_pad((string) (count($modelos) + 1), 3, '0', STR_PAD_LEFT);
    $partes = array_values(array_filter(explode('/', $ruta), static fn ($p) => $p !== '' && $p !== '*'));
    $sufijo = implode('_', array_slice($partes, -3));
    $sufijo = strtolower(str_replace('-', '_', $sufijo));
    $sufijo = substr($sufijo, 0, 38);
    $tabla = $padre === null ? 'entradas_institucionales' : 'ret_'.$sufijo.'_'.$id;
    $modelos[$id] = ['tabla' => $tabla, 'padre' => $padre, 'ruta' => $ruta,
        'fk_propietario' => 'cod_propietario', 'estructura' => []];
    $columnas = [];
    $convertir = function (array $n, string $local, string $total) use (&$convertir, &$construir, &$columnas, $id): array {
        if ($n['clase'] === 'vacia') {
            $n = isset($n['arreglo_vacio']) ? ['clase' => 'objeto', 'campos' => []]
                : ['clase' => 'escalar', 'tipos' => ['STRING']];
        }
        $n['columna'] = columnaCompleta($local);
        if (isset($columnas[$n['columna']]) && $columnas[$n['columna']] !== $local) {
            throw new RuntimeException('Colisión nominal: '.$local.' / '.$columnas[$n['columna']]);
        }
        $columnas[$n['columna']] = $local;
        if ($n['clase'] === 'coleccion') {
            $n['modelo'] = $construir($n['item'] ?? ['clase' => 'vacia'], $total.'/*', $id);
            $n['identidad'] = 'COLECCION';
            unset($n['item']);
        } elseif ($n['clase'] === 'objeto') {
            foreach ($n['campos'] as $k => &$hijo) {
                $hijo = $convertir($hijo, $local.'/'.$k, $total.'/'.$k);
            }
            unset($hijo);
        }

        return $n;
    };
    $modelos[$id]['estructura'] = $convertir($s, '', $ruta);

    return $id;
};
$raiz = $construir($estructura, '', null);
$mapa = ['contrato' => 'REFERENCIA-COMPLETA-NOMINAL-1', 'raiz' => $raiz, 'modelos' => $modelos,
    'contrato_sql' => 'REFERENCIA-COMPLETA-NOMINAL-1', 'contenido_completo' => true,
    'instalable' => false, 'alcance' => 'CORPUS_SINTETICO_F02_Y_TRES_NEGATIVOS_CERRADO'];
$texto = "<?php\n\ndeclare(strict_types=1);\n\n// Referencia cerrada compilada OFFLINE. No contiene valores clínicos. No instalable.\nreturn ".var_export($mapa, true).";\n";
$destino = __DIR__.'/mapa-completo.php';
if ($check) {
    if (! is_file($destino) || file_get_contents($destino) !== $texto) {
        throw new RuntimeException('El contrato completo observado necesita regenerarse.');
    }
} elseif (file_put_contents($destino, $texto) !== strlen($texto)) {
    throw new RuntimeException('No se pudo conservar el mapa cerrado.');
}
echo json_encode(['modelos' => count($modelos), 'check' => $check, 'instalable' => false], JSON_THROW_ON_ERROR).PHP_EOL;
