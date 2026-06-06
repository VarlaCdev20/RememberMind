<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = Schema::getTables();
$schema = [];
foreach ($tables as $tableInfo) {
    $table = $tableInfo['name'];
    $columns = Schema::getColumns($table);
    $columnDetails = [];
    foreach ($columns as $column) {
        $columnDetails[$column['name']] = $column['type_name'];
    }
    
    $fks = Schema::getForeignKeys($table);
    $foreignKeys = [];
    foreach ($fks as $fk) {
        $foreignKeys[] = [
            'columns' => $fk['columns'],
            'foreign_table' => $fk['foreign_table'],
            'foreign_columns' => $fk['foreign_columns']
        ];
    }

    $schema[$table] = [
        'columns' => $columnDetails,
        'foreign_keys' => $foreignKeys
    ];
}
file_put_contents('schema_dump.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Dumped schema to schema_dump.json\n";
