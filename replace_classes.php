<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/\.blade\.php$/', RegexIterator::MATCH);

foreach ($files as $file) {
    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    // 1. Exact strings
    $content = str_replace('bg-white', 'bg-fondo-card', $content);
    $content = str_replace('bg-terracota-dark', 'bg-boton-acentoHover', $content);
    $content = str_replace('hover:bg-terracota-dark', 'hover:bg-boton-acentoHover', $content);
    $content = str_replace('bg-terracota', 'bg-boton-acento', $content);
    $content = str_replace('bg-azul-profundo', 'bg-boton-principal', $content);
    $content = str_replace('text-azul-profundo', 'text-titulo', $content);
    $content = str_replace('text-white', 'text-inverso', $content);

    // 2. Text colors with hex
    $content = preg_replace_callback('/text-\[#([a-fA-F0-9]{6})\](?:\/([0-9]+))?/', function ($matches) {
        $hex = strtoupper($matches[1]);
        $opacity = isset($matches[2]) ? $matches[2] : null;

        if ($hex === '2F3E5C') return $opacity ? 'text-apoyo' : 'text-titulo';
        if ($hex === 'E27D60') return 'text-boton-acento';
        if (in_array($hex, ['8DA280', '63775B'])) return 'text-estado-exito';
        if (in_array($hex, ['D9A05B', '9A6B2E'])) return 'text-estado-advertencia';
        if (in_array($hex, ['D5C7B9', '7C7168', 'C7B5A3'])) return 'text-meta';
        if ($hex === 'FFFDF9' || $hex === 'FFFFFF') return $opacity ? 'text-inverso opacity-70' : 'text-inverso';
        return $opacity ? 'text-apoyo' : 'text-parrafo';
    }, $content);

    // 3. Background colors with hex
    $content = preg_replace_callback('/bg-\[#([a-fA-F0-9]{6})\](?:\/([0-9]+))?/', function ($matches) {
        $hex = strtoupper($matches[1]);
        $opacity = isset($matches[2]) ? $matches[2] : null;

        if ($hex === '2F3E5C') return $opacity ? 'bg-fondo-panel' : 'bg-boton-principal';
        if ($hex === 'E27D60') return $opacity ? 'bg-estado-peligroBg' : 'bg-boton-acento';
        if ($hex === '8DA280') return 'bg-estado-exitoBg';
        if ($hex === 'D9A05B') return 'bg-estado-advertenciaBg';
        if (in_array($hex, ['F8F3ED', 'E6DDD3', 'F3ECE4', 'D5C7B9', 'FAF6EF', 'F7F5F2', 'F9F9F9'])) return $opacity ? 'bg-fondo-panel' : 'bg-fondo-app';
        if ($hex === 'FFFDF9') return 'bg-fondo-card';
        return 'bg-fondo-panel';
    }, $content);

    // 4. Border colors with hex
    $content = preg_replace_callback('/border-\[#([a-fA-F0-9]{6})\](?:\/([0-9]+))?/', function ($matches) {
        $hex = strtoupper($matches[1]);
        if ($hex === 'E27D60') return 'border-borde-focus';
        if ($hex === '2F3E5C') return 'border-borde-fuerte';
        if (in_array($hex, ['8DA280', '63775B'])) return 'border-estado-exitoBorde';
        if (in_array($hex, ['D9A05B', '9A6B2E'])) return 'border-estado-advertenciaBorde';
        if (in_array($hex, ['D5C7B9', 'C7B5A3', 'E6DDD3'])) return 'border-borde-suave';
        return 'border-borde';
    }, $content);

    // 5. Generic gray rules
    $content = preg_replace('/\btext-gray-[0-9]+\b/', 'text-apoyo', $content);
    $content = preg_replace('/\bbg-gray-[0-9]+\b/', 'bg-fondo-panel', $content);
    $content = preg_replace('/\bborder-gray-[0-9]+\b/', 'border-borde-suave', $content);

    // Forms focus
    $content = str_replace('focus:border-terracota', 'focus:border-borde-focus', $content);
    $content = str_replace('focus:ring-terracota', 'focus:ring-borde-focus', $content);

    // Print specific fixes
    $content = str_replace('print:bg-fondo-card', 'print:bg-white', $content);
    $content = str_replace('print:text-inverso', 'print:text-black', $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: $path\n";
    }
}
