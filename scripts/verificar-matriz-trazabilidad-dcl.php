<?php

declare(strict_types=1);

use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;

// Verificador documental offline: sin arranque Laravel, .env, SQL, casos o inferencias.
$raiz = dirname(__DIR__);
require $raiz.'/vendor/autoload.php';

function celdaMatriz(string $texto): string
{
    return str_replace(["\r", "\n", '|'], ['', ' ', '&#124;'], $texto);
}

function filaMatriz(array $celdas): string
{
    return '| '.implode(' | ', array_map(celdaMatriz(...), $celdas))." |\n";
}

function listaMatriz(array $elementos): string
{
    return implode('<br>', array_map(static fn (string $s): string => '`'.$s.'`', $elementos));
}

function anexoTrazabilidad(string $raiz): string
{
    $fuente = json_decode(file_get_contents($raiz.'/app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'), true, flags: JSON_THROW_ON_ERROR);
    $red = (new PerfilConceptualCognitivo)->construir($fuente);
    $metodos = (new RegistroMetodosInstrumentales)->metodos();
    $reglas = PaqueteCognitivoInstitucional::reglasG09();
    $componentes = [];
    foreach ($red['dominios'] as $dominio) {
        $componentes += $dominio['componentes'];
    }
    if (count($componentes) !== 19 || count($reglas) !== 15 || count($metodos) !== 23) {
        throw new DomainException('Cambió el alcance 19 componentes/15 reglas específicas/23 métodos: revisar la matriz y su decisión de alcance.');
    }
    foreach ($metodos as $metodo) {
        if (array_diff($metodo['componentes'], array_keys($componentes)) !== []) {
            throw new DomainException('Un método declara un componente ajeno a la red vigente.');
        }
    }
    $s = "### A. Los diecinueve componentes y sus métodos registrados\n\n";
    $s .= "Esta es cobertura del código, no una batería obligatoria, un catálogo instalado o un perfil habilitado. Las cuatro filas de memoria comparten las dos reglas agregadas; no existen cuatro reglas específicas de memoria en este paquete.\n\n";
    $s .= filaMatriz(['Componente', 'Significado en la red', 'Métodos técnicos que lo declaran', 'Regla de resultado del paquete']);
    $s .= "|---|---|---|---|\n";
    foreach ($componentes as $id => $componente) {
        $propios = array_keys(array_filter($metodos, static fn (array $m): bool => in_array($id, $m['componentes'], true)));
        if ($propios === []) {
            throw new DomainException('Un componente carece de método registrado; debe describirse la brecha expresamente.');
        }
        $s .= filaMatriz(['`'.$id.'`', $componente['nombre'], listaMatriz($propios),
            isset($reglas[$id]) ? '`R-'.$id.'-P01` → `'.$reglas[$id].'`' : 'Agregadas: `MEM-DIFICULTAD` / `MEM-SIN-DIFICULTAD`; alcance específico pendiente.']);
    }
    $s .= "\n### B. Entradas tipadas de los veintitrés métodos\n\n";
    $s .= "Los identificadores siguientes son claves del método, no columnas nuevas ni preguntas ya instaladas. Cada entrada requiere un selector explícito de una pregunta real y su respuesta. Edad en aplicación y contexto conservan su procedencia; los tipos no autorizan un punto de corte. Los campos y rangos completos permanecen en el [diccionario de adquisición](DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md).\n\n";
    $s .= filaMatriz(['Método', 'Entradas declaradas y tipo', 'Referencia técnica declarada']);
    $s .= "|---|---|---|\n";
    foreach ($metodos as $id => $metodo) {
        $entradas = [];
        foreach ($metodo['entradas'] as $campo => $tipo) {
            $entradas[] = $campo.':'.$tipo;
        }
        $url = $metodo['fuente'] ?? '';
        if (! is_string($url) || ! str_starts_with($url, 'https://')) {
            throw new DomainException('Un método no identifica su referencia HTTPS.');
        }
        $s .= filaMatriz(['`'.$id.'`', listaMatriz($entradas), '[Fuente declarada]('.$url.')']);
    }
    $s .= "\n### C. Antecedentes y salidas específicas existentes\n\n";
    $s .= "C1/C2/C3 son antecedentes instrumentales de cada componente; C01–C05 son requisitos del caso. Unas claves no sustituyen a las otras. Las dos reglas agregadas de memoria se describen en la sección5.\n\n";
    $s .= filaMatriz(['Regla', 'Antecedentes', 'Salida / alcance']);
    $s .= "|---|---|---|\n";
    foreach ($reglas as $componente => $resultado) {
        $s .= filaMatriz(['`R-'.$componente.'-P01`', '`VAR-'.$componente.'-C1`, `-C2`, `-C3` = `ACREDITADA`, conjuntamente y desde la misma aplicación.', '`'.$resultado.'`; no equivale a un dominio A/P ni a DCL.']);
    }
    foreach (['PET-01', 'PET-02', 'PET-03', 'PET-04'] as $patron) {
        $condiciones = [];
        foreach (PaquetePetersenInstitucional::condiciones($patron) as $campo => $valor) {
            $condiciones[] = $campo.'='.$valor;
        }
        $s .= filaMatriz(['`'.$patron.'`', listaMatriz($condiciones), 'Patrón orientativo; sujeto además a admisibilidad, precedencia y abstención del caso.']);
    }
    foreach (EvaluadorObservaciones::REGLAS as $id => $regla) {
        $s .= filaMatriz(['`'.$id.'`', '`controles_cognitivos.'.$regla['campo'].' = true`, registro admisible al corte.', $regla['hallazgo'].' No acredita C02.']);
    }
    $s .= "\n### D. Soportes y comprobaciones de los requisitos y dominios\n\n";
    $s .= "Las FK nombradas ya pertenecen al contrato de soportes; no son relaciones nuevas. El mapa no elimina las comprobaciones de residente, autor, episodio, estado, contenido o fecha.\n\n";
    $s .= filaMatriz(['Elemento', 'Comprobaciones originales admitidas', 'Tipos y FK de soporte admitidos']);
    $s .= "|---|---|---|\n";
    foreach (ContratoFuentesPetersen::mapa() as $id => $elemento) {
        $soportes = [];
        foreach ($elemento['soportes'] as $tipo => $meta) {
            $soportes[] = $tipo.': '.implode(', ', $meta['fuentes_admitidas']);
        }
        $s .= filaMatriz(['`'.$id.'`', listaMatriz($elemento['comprobaciones']) ?: 'Derivado de los cinco dominios; sin formulario original.',
            implode('<br>', $soportes) ?: 'Sin soportes originales propios; conserva `derivado_de`.']);
    }
    $config = require $raiz.'/config/sistema_experto.php';
    $s .= "\n### E. Configuración base del repositorio\n\n";
    $s .= "Conteos del archivo de configuración, sin cargar `.env` ni consultar un despliegue. No acreditan estados de la BDD ni permisos de una sesión.\n\n";
    $s .= filaMatriz(['Registro base', 'Cantidad']);
    $s .= "|---|---|\n";
    foreach (['perfiles_instrumentales', 'perfiles_interpretativos', 'contratos_conocimiento', 'lectura_clinica'] as $clave) {
        $s .= filaMatriz(['`'.$clave.'`', (string) count($config[$clave])]);
    }
    $s .= "\nLos contratos institucionales incorporados pueden resolverse desde la versión vigente aunque estas listas estén vacías. Una lista vacía no significa ausencia de motor; tampoco crea conexiones instrumentales.\n";

    return rtrim($s);
}

try {
    $modo = $argv[1] ?? '--check';
    if (count($argv) > 2 || ! in_array($modo, ['--check', '--actualizar'], true)) {
        throw new InvalidArgumentException('Uso: php scripts/verificar-matriz-trazabilidad-dcl.php [--check|--actualizar]');
    }
    $archivo = $raiz.'/docs/sistema-experto/MATRIZ_TRAZABILIDAD_DCL.md';
    $texto = file_get_contents($archivo);
    if (! is_string($texto)) {
        throw new RuntimeException('No se pudo leer la matriz documental.');
    }
    $inicio = '<!-- INICIO_ANEXO_TRAZABILIDAD -->';
    $fin = '<!-- FIN_ANEXO_TRAZABILIDAD -->';
    if (substr_count($texto, $inicio) !== 1 || substr_count($texto, $fin) !== 1 || strpos($texto, $inicio) > strpos($texto, $fin)) {
        throw new DomainException('La matriz debe contener exactamente un anexo delimitado.');
    }
    $patron = '/'.preg_quote($inicio, '/').'.*?'.preg_quote($fin, '/').'/s';
    $bloque = $inicio."\n\n".anexoTrazabilidad($raiz)."\n\n".$fin;
    if (str_contains($texto, "\r\n")) {
        $bloque = str_replace("\n", "\r\n", $bloque);
    }
    $esperado = preg_replace_callback($patron, static fn (): string => $bloque, $texto, count: $reemplazos);
    if (! is_string($esperado) || $reemplazos !== 1) {
        throw new DomainException('No se pudo identificar un único bloque de actualización.');
    }
    preg_match_all('/^\| (D\d{2}) \|/m', $esperado, $datos);
    if ($datos[1] !== array_map(static fn (int $i): string => sprintf('D%02d', $i), range(1, 27))) {
        throw new DomainException('Las filas D01–D27 deben estar completas, ordenadas y sin duplicados.');
    }
    preg_match_all('/^\| `(MEM-(?:SIN-)?DIFICULTAD)` \|/m', $esperado, $memoria);
    if ($memoria[1] !== ['MEM-DIFICULTAD', 'MEM-SIN-DIFICULTAD']) {
        throw new DomainException('Deben documentarse las dos reglas agregadas de memoria; su semántica requiere revisión independiente.');
    }
    preg_match_all('/\]\(([^)]+)\)/', $esperado, $enlaces);
    foreach ($enlaces[1] as $enlace) {
        if (preg_match('~^https?://~', $enlace) || str_starts_with($enlace, '#')) {
            continue;
        }
        $ruta = explode('#', $enlace, 2)[0];
        if (! is_file(dirname($archivo).'/'.$ruta)) {
            throw new DomainException('Enlace local inexistente: '.$ruta);
        }
    }
    if ($modo === '--actualizar') {
        if (file_put_contents($archivo, $esperado, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo actualizar la matriz documental.');
        }
    } elseif (str_replace("\r\n", "\n", $texto) !== str_replace("\r\n", "\n", $esperado)) {
        throw new DomainException('El anexo difiere del código. Revisar los cambios y regenerarlo con --actualizar.');
    }
    echo "PASS: 27 datos; 19 componentes; 23 métodos; 15 reglas específicas + 4 Petersen + 3 observacionales; 2 agregadas documentadas manualmente; soportes y enlaces locales. Sin BDD ni inferencia.\n";
} catch (InvalidArgumentException|DomainException|RuntimeException|JsonException $e) {
    fwrite(STDERR, 'FAIL: '.$e->getMessage()."\n");
    exit(1);
}
