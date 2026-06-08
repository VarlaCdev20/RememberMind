<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/\.blade\.php$/', RegexIterator::MATCH);

foreach ($files as $file) {
    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    // Fix global wrappers that encompass entire views with a white/solid background.
    // e.g. <div class="bg-fondo-card border border-borde rounded-3xl">
    $content = str_replace('<div class="min-h-screen bg-fondo-panel py-8 antialiased text-parrafo print:bg-white print:py-0"', '<div class="relative z-10 space-y-6 py-8 antialiased text-parrafo print:bg-white print:py-0"', $content);
    
    // Replace giant white wrappers in other common patterns:
    $content = preg_replace('/<div class="bg-white rounded-3xl shadow-sm p-8">/', '<div class="relative z-10 space-y-6">', $content);
    $content = preg_replace('/<div class="bg-fondo-card border border-borde rounded-3xl">/', '<div class="relative z-10 space-y-6">', $content);
    $content = preg_replace('/<div class="bg-fondo-panel rounded-3xl p-6">/', '<div class="relative z-10 space-y-6">', $content);

    // Replace filters
    $content = str_replace('mb-8 rounded-3xl border border-borde-suave bg-fondo-card p-6 shadow-sm', 'mb-8 rm-card-soft p-6', $content);
    $content = str_replace('rounded-3xl border border-borde-suave bg-fondo-card p-6 shadow-sm', 'rm-card p-6', $content);
    
    // Replace KPIs
    $content = str_replace('rounded-2xl border border-borde-suave bg-fondo-card p-4 shadow-sm', 'rm-metric-card p-4', $content);
    $content = str_replace('rounded-2xl border border-borde-suave bg-fondo-card p-5 shadow-sm', 'rm-card-soft p-5', $content);

    // Some specific ones in reportes-institucionales
    $content = str_replace('rounded-3xl border border-borde bg-fondo-card shadow-sm', 'rm-card shadow-none', $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: $path\n";
    }
}
