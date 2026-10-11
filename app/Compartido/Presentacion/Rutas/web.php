<?php

use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_001' => static function (): void {
        Route::get('/', function () {
            return view('pages.welcome');
        })->name('welcome');
    },
];
