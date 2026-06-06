<?php
$schema = json_decode(file_get_contents('schema_dump.json'), true);

$tablesCount = count($schema);
$inventory = "";
$relations = "";
$inconsistencies = "";
$risks = "";

// 1. Inventario
$inventory .= "### 1. Inventario de Tablas ($tablesCount tablas)\n\n";
foreach ($schema as $tableName => $data) {
    $inventory .= "- **$tableName** (Columnas: " . count($data['columns']) . ")\n";
}

// 2. Relaciones
$relations .= "### 2. Tabla de Relaciones Principales\n\n";
$relations .= "| Tabla Origen | Clave Foránea | Tabla Destino | Claves Destino |\n";
$relations .= "|---|---|---|---|\n";
foreach ($schema as $tableName => $data) {
    foreach ($data['foreign_keys'] as $fk) {
        $relations .= "| $tableName | " . implode(', ', $fk['columns']) . " | " . $fk['foreign_table'] . " | " . implode(', ', $fk['foreign_columns']) . " |\n";
    }
}

// 3. Inconsistencias
// Find inconsistencies: like missing PKs, or weird column names.
$inconsistencies .= "### 3. Inconsistencias Detectadas\n\n";
$pkCount = 0;
foreach ($schema as $tableName => $data) {
    // Check if there's an 'id' or 'cod_' column
    $hasPk = false;
    foreach ($data['columns'] as $col => $type) {
        if ($col === 'id' || strpos($col, 'cod_') === 0) {
            $hasPk = true;
            break;
        }
    }
    if (!$hasPk && !in_array($tableName, ['password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'job_batches', 'failed_jobs', 'migrations', 'model_has_permissions', 'model_has_roles', 'role_has_permissions'])) {
        $inconsistencies .= "- **$tableName**: No tiene una clave primaria estándar detectada (`id` o `cod_...`).\n";
    }
    // Check missing timestamps
    if (!isset($data['columns']['created_at']) && !in_array($tableName, ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'job_batches', 'model_has_permissions', 'model_has_roles', 'role_has_permissions'])) {
        $inconsistencies .= "- **$tableName**: Falta columna `created_at`/`updated_at`.\n";
    }
}

// Write the whole MD
$report = "# Auditoría de Base de Datos - RememberMind\n\n";
$report .= $inventory . "\n" . $relations . "\n" . $inconsistencies . "\n";
file_put_contents('auditoria.md', $report);
echo "Report generated.\n";
