<?php
$lines = file('c:/laragon/www/RememberMind_F1/resources/views/admin/adultos-mayores/show.blade.php');
foreach ($lines as $i => $line) {
    if (
        strpos($line, '{{-- TAB ') !== false || 
        strpos($line, 'x-show="tab ===') !== false || 
        strpos($line, '<!-- Navegación') !== false || 
        strpos($line, '{{-- HEADER COMPACTO') !== false || 
        strpos($line, '<template x-if') !== false || 
        strpos($line, '{{-- Tabla de Atenciones') !== false ||
        strpos($line, '<!-- Scripts') !== false ||
        strpos($line, '<!-- Modales') !== false
    ) {
        echo ($i + 1) . ': ' . trim($line) . PHP_EOL;
    }
}
