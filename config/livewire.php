<?php

return [
    // Evitar que una configuración global pública exponga cargas clínicas temporales.
    'temporary_file_upload' => ['disk' => 'local'],
];
