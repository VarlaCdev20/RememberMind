<?php

declare(strict_types=1);

// Compara evidencia sintética exportada por la prueba. No arranca Laravel ni abre BDD.
if ($argc !== 3) {
    throw new RuntimeException('Uso: php scripts/comparar-cohorte30.php informe-sqlite.json informe-postgres.json');
}
$leer = static function (string $ruta): array {
    $d = json_decode(file_get_contents($ruta), true, 512, JSON_THROW_ON_ERROR);
    if (($d['version'] ?? null) !== 'COHORTE-INFERENCIA-30-V1' || ($d['sintetico'] ?? null) !== true
        || ($d['validacion_clinica'] ?? null) !== false || count($d['casos'] ?? []) !== 30) {
        throw new RuntimeException('El informe no corresponde a la cohorte sintética completa.');
    }

    return $d;
};
$sqlite = $leer($argv[1]);
$postgres = $leer($argv[2]);
if (isset($sqlite['manifiesto']) || isset($postgres['manifiesto'])) {
    if (($sqlite['manifiesto'] ?? null) !== ($postgres['manifiesto'] ?? null)) {
        throw new RuntimeException('Las ejecuciones utilizan manifiestos diferentes.');
    }
    foreach ([$sqlite, $postgres] as $informe) {
        if (($informe['manifiesto']['datos_individuales_importados'] ?? null) !== false
            || ($informe['retroalimentacion']['satisfactorio'] ?? false) !== true
            || ($informe['retroalimentacion']['discrepancias'] ?? null) !== []
            || ($informe['retroalimentacion']['reglas_modificadas'] ?? null) !== false) {
            throw new RuntimeException('Retroalimentación incompleta o discrepancias pendientes.');
        }
    }
}
if ($sqlite['motor'] !== 'sqlite' || $postgres['motor'] !== 'pgsql' || $sqlite['tablas'] !== $postgres['tablas']) {
    throw new RuntimeException('Motores o cantidades SQL incompatibles.');
}
foreach ($sqlite['casos'] as $i => $caso) {
    $otro = $postgres['casos'][$i];
    // Huellas distintas entre cargas: identidades/UUID propios, no se sustituyen por alias.
    foreach (['caso', 'sintetico', 'variante', 'edad', 'caida_contextual', 'diagnostico_contextual', 'patron_calculado',
        'estado', 'dominios', 'requisitos', 'impedimentos', 'capturas_sql', 'replay_memoria_identico', 'conservacion_conjunta_sql'] as $campo) {
        if (! array_key_exists($campo, $caso) || ! array_key_exists($campo, $otro) || $caso[$campo] !== $otro[$campo]) {
            throw new RuntimeException('Discrepancia entre motores: '.$caso['caso'].' / '.$campo);
        }
    }
    foreach ([$caso, $otro] as $c) {
        if (! preg_match('/^[a-f0-9]{64}$/D', $c['huella_dto'] ?? '') || $c['replay_memoria_identico'] !== true
            || $c['conservacion_conjunta_sql'] !== false) {
            throw new RuntimeException('Evidencia de replay o estado de conservación incompatibles.');
        }
    }
}
echo "PASS: 30 casos, estados, motivos y cantidades SQL equivalentes en SQLite/PostgreSQL. Replay individual idéntico; guardado conjunto pendiente.\n";
