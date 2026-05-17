<?php
$file = 'c:/laragon/www/RememberMind_F1/resources/views/admin/adultos-mayores/show.blade.php';
$lines = file($file);
$content = implode("", $lines);

$slices = [
    '_cabecera-expediente' => ['{{-- HEADER COMPACTO', '{{-- TAB RESUMEN'],
    '_resumen' => ['{{-- TAB RESUMEN', '{{-- TAB FAMILIARES'],
    '_familiares' => ['{{-- TAB FAMILIARES', '{{-- TAB SEGUIMIENTO MEJORADO'],
    '_seguimiento-observaciones' => ['{{-- TAB SEGUIMIENTO MEJORADO', '{{-- Tabla de Atenciones'],
    '_atenciones' => ['{{-- Tabla de Atenciones', '{{-- TAB SALUD MÉDICA'],
    '_salud-medica' => ['{{-- TAB SALUD MÉDICA', '{{-- TAB EVALUACIONES COGNITIVAS'],
    '_evaluaciones-cognitivas' => ['{{-- TAB EVALUACIONES COGNITIVAS', '{{-- TAB ACTIVIDADES MEJORADO'],
    '_actividades' => ['{{-- TAB ACTIVIDADES MEJORADO', '{{-- TAB DOCUMENTOS'],
    '_documentos' => ['{{-- TAB DOCUMENTOS', '{{-- TAB HISTORIAL'],
    '_historial-estados' => ['{{-- TAB HISTORIAL', '<template x-if="modal">'],
    '_modales-existentes' => ['<template x-if="modal">', '</div>' . PHP_EOL . '</x-app-layout>'],
];

$dir = 'c:/laragon/www/RememberMind_F1/resources/views/admin/adultos-mayores/show/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$newContent = "";
$currentPos = 0;

foreach ($slices as $name => $bounds) {
    $startStr = $bounds[0];
    $endStr = $bounds[1];

    $startPos = strpos($content, $startStr, $currentPos);
    if ($startPos === false) {
        die("Start string not found for $name: $startStr\n");
    }

    if ($currentPos == 0) {
        $newContent .= substr($content, 0, $startPos);
    }

    $endPos = strpos($content, $endStr, $startPos);
    if ($endPos === false) {
        die("End string not found for $name: $endStr\n");
    }

    $sliceContent = substr($content, $startPos, $endPos - $startPos);
    file_put_contents($dir . $name . '.blade.php', $sliceContent);
    
    // Si estamos en _atenciones, hay que tener cuidado porque el tag </section> pertenece al final de atenciones.
    // _seguimiento-observaciones NO cierra el section, y _atenciones SI lo cierra.
    // Asi que las incluiremos una tras otra.
    
    $newContent .= "            @include('admin.adultos-mayores.show." . $name . "')\n";
    
    $currentPos = $endPos;
}

$newContent .= substr($content, $currentPos);
file_put_contents('c:/laragon/www/RememberMind_F1/resources/views/admin/adultos-mayores/show-refactored.blade.php', $newContent);

echo "Success!\n";
