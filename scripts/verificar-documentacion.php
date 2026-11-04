<?php

declare(strict_types=1);

// Comprueba documentación y archivos; no ejecuta seeders ni modifica datos.
$raiz = dirname(__DIR__);
chdir($raiz);
if (array_diff(array_slice($argv, 1), ['--check']) !== []) {
    fwrite(STDERR, "Uso: php scripts/verificar-documentacion.php --check\n");
    exit(2);
}

$errores = [];
$excluidas = ['.git', '.agents', '.codex', 'vendor', 'node_modules', 'output', 'work', 'storage', 'public'];
$recorrer = static function (string $directorio, string $sufijo) use ($excluidas, $raiz): array {
    $filtro = new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($directorio, FilesystemIterator::SKIP_DOTS),
        static fn (SplFileInfo $archivo): bool => ! $archivo->isDir() || ! (realpath($archivo->getPath()) === realpath($raiz)
            && in_array($archivo->getFilename(), $excluidas, true)),
    );
    $archivos = [];
    foreach (new RecursiveIteratorIterator($filtro) as $archivo) {
        if ($archivo->isFile() && str_ends_with($archivo->getFilename(), $sufijo)) {
            $archivos[] = str_replace('\\', '/', $archivo->getPathname());
        }
    }
    sort($archivos);

    return $archivos;
};
$leer = static fn (string $archivo): string => (string) file_get_contents($archivo);
$relativa = static fn (string $archivo): string => ltrim(str_replace(str_replace('\\', '/', $raiz), '', str_replace('\\', '/', $archivo)), '/');
$documentos = $recorrer($raiz, '.md');
$anclas = [];
foreach ($documentos as $documento) {
    $texto = $leer($documento);
    preg_match_all('/\bid=["\']([^"\']+)/', $texto, $ids);
    $validas = array_fill_keys($ids[1], true);
    $repetidas = [];
    preg_match_all('/^#{1,6}\s+(.+?)[ \t]*#*[ \t]*$/m', $texto, $titulos);
    foreach ($titulos[1] as $titulo) {
        $titulo = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $titulo);
        $slug = preg_replace('/[^\p{L}\p{N}_\- ]/u', '', mb_strtolower(trim(strip_tags($titulo)), 'UTF-8'));
        $slug = str_replace(' ', '-', $slug);
        $numero = $repetidas[$slug] ?? 0;
        $repetidas[$slug] = $numero + 1;
        $validas[$slug.($numero === 0 ? '' : '-'.$numero)] = true;
    }
    $anclas[realpath($documento)] = $validas;
}
$enlaces = 0;
foreach ($documentos as $documento) {
    $texto = $leer($documento);
    preg_match_all('~(?<!!)\[[^\]]*\]\(([^)]+)\)~u', $texto, $coincidencias, PREG_OFFSET_CAPTURE);
    foreach ($coincidencias[1] as [$destino, $posicion]) {
        $destino = trim($destino);
        $destino = str_starts_with($destino, '<')
            ? explode('>', substr($destino, 1), 2)[0]
            : preg_split('/\s+["\']/', $destino, 2)[0];
        if ($destino === '' || preg_match('~^(?:[a-z]+:|//)~i', $destino)) {
            continue;
        }
        $ruta = preg_replace('/:\d+$/', '', rawurldecode(preg_split('/[?#]/', $destino, 2)[0]));
        $objetivo = $ruta === '' ? $documento : dirname($documento).'/'.$ruta;
        $enlaces++;
        if (! file_exists($objetivo)) {
            $linea = substr_count(substr($texto, 0, $posicion), "\n") + 1;
            $errores[] = $relativa($documento).':'.$linea.' → '.$destino;
        } elseif (str_contains($destino, '#')) {
            $fragmento = rawurldecode(explode('#', $destino, 2)[1]);
            $reales = $anclas[realpath($objetivo)] ?? null;
            if ($fragmento !== '' && $reales !== null && ! isset($reales[$fragmento])) {
                $linea = substr_count(substr($texto, 0, $posicion), "\n") + 1;
                $errores[] = $relativa($documento).':'.$linea.' → ancla ausente '.$destino;
            }
        }
    }
}

$inventario = $leer($raiz.'/docs/sistema/INVENTARIO_ACTUAL.md');
$comparar = static function (string $etiqueta, int $actual) use ($inventario, &$errores): void {
    preg_match('/^\| '.preg_quote($etiqueta, '/').' \| (\d+) \|$/m', $inventario, $fila);
    if (! isset($fila[1]) || (int) $fila[1] !== $actual) {
        $errores[] = 'Inventario desactualizado: '.$etiqueta.'; actual='.$actual;
    }
};
$modulos = array_values(array_filter(glob($raiz.'/app/Modulos/*'), 'is_dir'));
$comparar('Módulos Laravel', count($modulos));
$comparar('Archivos de modelos Eloquent', count(glob($raiz.'/app/Models/*.php')));
$compartidas = $recorrer($raiz.'/resources/views', '.blade.php');
$modulares = $recorrer($raiz.'/app/Modulos', '.blade.php');
$comparar('Plantillas Blade modulares', count($modulares));
$comparar('Plantillas Blade compartidas', count($compartidas));
$comparar('Plantillas Blade totales', count($compartidas) + count($modulares));
$motor = array_filter($recorrer($raiz.'/motor-experto', '.php'), static fn (string $ruta): bool => ! str_contains($ruta, '/tests/'));
$comparar('Archivos PHP productivos del motor', count($motor));
preg_match('/\$rolesActivos\s*=\s*\[(.*?)\];/s', $leer($raiz.'/database/seeders/RolesAndPermissionsSeeder.php'), $listaRoles);
preg_match_all("/'([^']+)'/", $listaRoles[1] ?? '', $roles);
$comparar('Roles definidos en el seeder', count($roles[1]));

$slugs = ['Admisiones' => 'admisiones', 'Residentes' => 'residentes', 'Identidad' => 'identidad',
    'Institucion' => 'institucion', 'Clinica' => 'clinica', 'Cuidados' => 'cuidados', 'Medicacion' => 'medicacion',
    'Valoraciones' => 'valoraciones', 'Social' => 'social', 'Alertas' => 'alertas', 'Reportes' => 'reportes',
    'ApoyoDecision' => 'apoyo-decision', 'Compartidas' => 'compartidas'];
foreach ($slugs as $modulo => $slug) {
    $base = $modulo === 'Compartidas' ? $raiz.'/resources/views' : $raiz.'/app/Modulos/'.$modulo.'/Presentacion/Vistas';
    $esperadas = $recorrer($base, '.blade.php');
    $indice = $leer($raiz.'/docs/especificacion-funcional/vistas/'.$slug.'/INVENTARIO.md');
    preg_match_all('/^\| \[([^\]]+\.blade\.php)\]\([^\n]+?\) \| (\d+) \| (\d+) \| (\d+) \|$/m', $indice, $filas, PREG_SET_ORDER);
    $declaradas = [];
    foreach ($filas as $fila) {
        $archivo = $raiz.'/'.$fila[1];
        $declaradas[] = str_replace('\\', '/', $archivo);
        if (! is_file($archivo)) {
            continue;
        }
        foreach (['button', 'form', 'table'] as $numero => $etiqueta) {
            preg_match_all('/<'.$etiqueta.'\b/i', $leer($archivo), $controles);
            if (count($controles[0]) !== (int) $fila[$numero + 2]) {
                $errores[] = 'Recuento de <'.$etiqueta.'> desactualizado: '.$fila[1];
            }
        }
    }
    sort($declaradas);
    if ($esperadas !== $declaradas) {
        $errores[] = 'Inventario Blade incompleto o ajeno al módulo: '.$modulo;
    }
}

$composer = json_decode($leer($raiz.'/composer.lock'), true, flags: JSON_THROW_ON_ERROR);
$versiones = [];
foreach (array_merge($composer['packages'], $composer['packages-dev']) as $paquete) {
    $versiones[$paquete['name']] = $paquete['version'];
}
$npm = json_decode($leer($raiz.'/package-lock.json'), true, flags: JSON_THROW_ON_ERROR);
foreach (['tailwindcss', 'vite', 'puppeteer'] as $nombre) {
    $versiones[$nombre] = $npm['packages']['node_modules/'.$nombre]['version'];
}
preg_match_all('/^\| `([^`]+)` \| `([^`]+)` \|$/m', $inventario, $locks, PREG_SET_ORDER);
foreach ($locks as $lock) {
    if (($versiones[$lock[1]] ?? null) !== $lock[2]) {
        $errores[] = 'Versión de lock desactualizada: '.$lock[1];
    }
}

$colores = $leer($raiz.'/resources/frontend/styles/design-system/tokens/colors.css');
foreach (['docs/frontend/CONTRATO_VISUAL_UX_UI.md', 'resources/frontend/styles/design-system/README.md'] as $guia) {
    preg_match_all('/^\| `(--rm-palette-[a-z-]+)` \| `(#[A-Fa-f0-9]{6})` \|/m', $leer($raiz.'/'.$guia), $tokens, PREG_SET_ORDER);
    foreach ($tokens as $token) {
        preg_match_all('/'.preg_quote($token[1], '/').':\s*(#[A-Fa-f0-9]{6});/', $colores, $valores);
        if ($valores[1] === [] || strcasecmp(end($valores[1]), $token[2]) !== 0) {
            $errores[] = 'Token documentado distinto del código: '.$guia.' '.$token[1];
        }
    }
}

$proceso = proc_open([PHP_BINARY, 'artisan', 'route:list', '--json'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $raiz);
if (! is_resource($proceso)) {
    $errores[] = 'No se pudo registrar el listado de rutas.';
} else {
    fclose($pipes[0]);
    $salida = stream_get_contents($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $rutas = json_decode($salida, true);
    if (proc_close($proceso) !== 0 || ! is_array($rutas)) {
        $errores[] = 'El listado de rutas no terminó correctamente; no se acreditan sus recuentos.';
    } else {
        $modulares = count(array_filter($rutas, static fn (array $ruta): bool => str_contains($ruta['action'], 'App\\Modulos\\')));
        $comparar('Definiciones de ruta registradas', count($rutas));
        $comparar('Rutas con acción modular', $modulares);
        $comparar('Otras rutas registradas', count($rutas) - $modulares);
    }
}

$catalogo = $leer($raiz.'/docs/INDICE_DOCUMENTAL.md');
preg_match_all('/^\| \[([^\]]+\.md)\]\(/m', $catalogo, $entradas);
$indexados = $entradas[1];
$reales = array_map($relativa, $documentos);
sort($indexados);
sort($reales);
if ($indexados !== $reales) {
    $errores[] = 'El catálogo documental tiene archivos ausentes, repetidos o sin indexar.';
}

if ($errores !== []) {
    fwrite(STDERR, "FAIL documental\n- ".implode("\n- ", $errores)."\n");
    exit(1);
}
echo 'PASS: '.count($documentos).' documentos, '.$enlaces." enlaces/anclas locales, inventarios Blade, locks, colores y rutas coherentes.\n";
