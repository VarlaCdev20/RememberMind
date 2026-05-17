<?php
$c = file_get_contents('c:/laragon/www/RememberMind_F1/resources/views/admin/adultos-mayores/show-refactored.blade.php');

$search = "                        @include('admin.adultos-mayores.show._cabecera-expediente')
            @include('admin.adultos-mayores.show._resumen')
            @include('admin.adultos-mayores.show._familiares')
            @include('admin.adultos-mayores.show._seguimiento-observaciones')
            @include('admin.adultos-mayores.show._atenciones')
            @include('admin.adultos-mayores.show._salud-medica')
            @include('admin.adultos-mayores.show._evaluaciones-cognitivas')
            @include('admin.adultos-mayores.show._actividades')
            @include('admin.adultos-mayores.show._documentos')
            @include('admin.adultos-mayores.show._historial-estados')
            @include('admin.adultos-mayores.show._modales-existentes')
</div>
</x-app-layout>";

$replace = "            {{-- Cabecera del expediente --}}
            @include('admin.adultos-mayores.show._cabecera-expediente')

            {{-- Contenido de pestañas --}}
            <div class=\"px-5 py-6 sm:px-6\">
                @include('admin.adultos-mayores.show._resumen')
                @include('admin.adultos-mayores.show._familiares')
                @include('admin.adultos-mayores.show._seguimiento-observaciones')
                @include('admin.adultos-mayores.show._atenciones')
                @include('admin.adultos-mayores.show._salud-medica')
                @include('admin.adultos-mayores.show._evaluaciones-cognitivas')
                @include('admin.adultos-mayores.show._actividades')
                @include('admin.adultos-mayores.show._documentos')
                @include('admin.adultos-mayores.show._historial-estados')
            </div>

            {{-- Modales Antiguos --}}
            @include('admin.adultos-mayores.show._modales-existentes')

        </div> {{-- fin max-w-7xl --}}
    </div> {{-- fin x-data principal --}}
</x-app-layout>";

$c = str_replace($search, $replace, $c);
// Fallback in case of newline mismatch:
if (strpos($c, 'fin x-data principal') === false) {
    $c = preg_replace('/(\s*)@include\(\'admin\.adultos-mayores\.show\._cabecera-expediente\'\).*<\/x-app-layout>/s', "\n" . $replace, $c);
}

file_put_contents('c:/laragon/www/RememberMind_F1/resources/views/admin/adultos-mayores/show-refactored.blade.php', $c);
