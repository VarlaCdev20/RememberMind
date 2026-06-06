<?php

$dir = 'c:\laragon\www\RememberMind_F1\resources\views\admin\usuarios\documentos';
@mkdir($dir, 0777, true);
@mkdir("$dir/partials", 0777, true);
@mkdir("$dir/individuales", 0777, true);

function file_put_contents_ensure($path, $content) {
    file_put_contents($path, $content);
}

// Subtle dot pattern base64 for PDF background
$patternBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAAXNSR0IArs4c6QAAACVJREFUKFNjZCASMDKgC37//v1/FIORhIEYE4gxgRgbYIwwDQDnSggF+6o/lAAAAABJRU5ErkJggg==';

// 1. Partials
file_put_contents_ensure("$dir/partials/encabezado-documento.blade.php", <<<'HTML'
@php
    $logoPath = public_path('storage/images/LOGO.png');
@endphp
<div style="background-color: #2E5C31; padding: 25px; border-radius: 12px 12px 0 0; text-align: center; border-bottom: 5px solid #E27D60; position: relative;">
    <div style="position: absolute; top: 15px; left: 25px;">
        @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="Logo" style="max-height: 60px; filter: brightness(0) invert(1);">
        @endif
    </div>
    <div style="display: inline-block; text-align: right; width: 100%; padding-left: 70px; box-sizing: border-box;">
        <h2 style="margin: 0; color: #FDF1ED; font-family: 'Helvetica', sans-serif; font-size: 20px; font-weight: bold; letter-spacing: 1px;">CENTRO GERIÁTRICO</h2>
        <h2 style="margin: 0; color: #E27D60; font-family: 'Helvetica', sans-serif; font-size: 22px; font-weight: 900; letter-spacing: 1.5px;">JARDÍN DE LOS RECUERDOS</h2>
        <h3 style="margin: 10px 0 0 0; color: #FFFFFF; font-family: 'Helvetica', sans-serif; font-size: 16px; font-weight: 300; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 5px; display: inline-block;">{{ mb_strtoupper($titulo ?? 'DOCUMENTO INSTITUCIONAL', 'UTF-8') }}</h3>
    </div>
</div>
@if(isset($fecha_actual))
    <div style="text-align: right; margin-top: 5px; padding-right: 15px;">
        <span style="color: #6B7280; font-size: 11px; font-weight: bold;">FECHA DE EMISIÓN: <span style="color: #E27D60;">{{ $fecha_actual }}</span></span>
    </div>
@endif
HTML);

file_put_contents_ensure("$dir/partials/pie-documento.blade.php", <<<'HTML'
<div style="position: fixed; bottom: -30px; left: 0px; right: 0px; text-align: center; font-family: 'Helvetica', sans-serif; font-size: 10px; color: #8DA280; border-top: 2px solid #E27D60; padding-top: 10px; background-color: #FAF7F2; padding-bottom: 10px;">
    <p style="margin: 0; font-weight: bold; color: #2E5C31;">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS | Documento de uso interno y confidencial</p>
    <p style="margin: 3px 0 0 0; color: #C7B5A3;">Generado automáticamente por el Sistema de Gestión Institucional el {{ $fecha_actual ?? now()->format('d/m/Y') }}</p>
</div>
HTML);

file_put_contents_ensure("$dir/partials/datos-usuario.blade.php", <<<'HTML'
<div style="margin: 20px 0; font-family: 'Helvetica', sans-serif; font-size: 12px; background-color: #FFFFFF; border: 1px solid #C7B5A3; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
    <div style="background-color: #FDF1ED; padding: 10px 15px; border-bottom: 1px solid #E27D60; font-weight: bold; color: #E27D60; font-size: 13px; text-transform: uppercase;">
        Datos de Identificación del Usuario
    </div>
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; font-weight: bold; width: 25%; background-color: #FAF7F2; color: #2E5C31;">NOMBRE COMPLETO:</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; color: #374151; font-weight: bold;" colspan="3">{{ $nombre_completo }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; font-weight: bold; background-color: #FAF7F2; color: #2E5C31;">CÉDULA DE IDENTIDAD:</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; color: #374151; width: 25%;">{{ $ci }}</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; font-weight: bold; width: 20%; background-color: #FAF7F2; color: #2E5C31;">ROL ASIGNADO:</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; color: #E27D60; font-weight: bold; width: 30%;">{{ $rol }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; font-weight: bold; background-color: #FAF7F2; color: #2E5C31;">TELÉFONO/CELULAR:</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; color: #374151;">{{ $celular }}</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; font-weight: bold; background-color: #FAF7F2; color: #2E5C31;">CORREO ELECTRÓNICO:</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; color: #374151;">{{ strtolower($correo) }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; font-weight: bold; background-color: #FAF7F2; color: #2E5C31;">DIRECCIÓN ACTUAL:</td>
                <td style="padding: 10px 15px; border-bottom: 1px solid #F3F4F6; color: #374151;" colspan="3">{{ $direccion }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 15px; font-weight: bold; background-color: #FAF7F2; color: #2E5C31;">ÁREA/CARGO/DETALLE:</td>
                <td style="padding: 10px 15px; color: #374151;">{{ $area_cargo }}</td>
                <td style="padding: 10px 15px; font-weight: bold; background-color: #FAF7F2; color: #2E5C31;">ESTADO DE ACCESO:</td>
                <td style="padding: 10px 15px; color: #63775B; font-weight: bold;">{{ $estado_acceso }}</td>
            </tr>
        </tbody>
    </table>
</div>
HTML);

file_put_contents_ensure("$dir/partials/firmas.blade.php", <<<'HTML'
<div style="margin-top: 70px; font-family: 'Helvetica', sans-serif; font-size: 12px; display: table; width: 100%; page-break-inside: avoid;">
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: bottom;">
        <div style="border-bottom: 1px solid #2E5C31; width: 70%; margin: 0 auto 10px auto;"></div>
        <strong style="color: #2E5C31; text-transform: uppercase;">Administración / Responsable</strong><br>
        <span style="color: #6B7280; font-size: 10px;">Centro Geriátrico Jardín de los Recuerdos</span>
    </div>
    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: bottom;">
        <div style="border-bottom: 1px solid #2E5C31; width: 70%; margin: 0 auto 10px auto;"></div>
        <strong style="color: #2E5C31; text-transform: uppercase;">Firma del Usuario ({{ $rol }})</strong><br>
        <span style="color: #6B7280; font-size: 10px;">{{ mb_convert_case($nombre_completo, MB_CASE_TITLE, 'UTF-8') }}</span>
    </div>
</div>
HTML);

// 2. Base layout and Package
file_put_contents_ensure("$dir/layout.blade.php", <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documento Institucional - Jardín de los Recuerdos</title>
    <style>
        @page { margin: 100px 50px 80px 50px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 13.5px; 
            color: #374151; 
            line-height: 1.6; 
            margin: 0; 
            padding: 0;
            background-color: #FAF7F2; 
            background-image: url('{$patternBase64}');
            background-repeat: repeat;
        }
        .page-break { page-break-after: always; }
        .document-container { 
            background-color: rgba(255, 255, 255, 0.95); 
            padding: 30px; 
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border: 1px solid rgba(226, 125, 96, 0.2);
        }
        .text-justify { text-align: justify; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        h4 { color: #2E5C31; margin-bottom: 12px; border-bottom: 2px solid #E27D60; display: inline-block; padding-bottom: 4px; }
        ul, ol { margin-top: 8px; margin-bottom: 18px; padding-left: 25px; }
        li { margin-bottom: 8px; }
        strong { color: #2E5C31; }
        .highlight-box {
            background-color: #FDF1ED;
            border-left: 4px solid #E27D60;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
HTML);

file_put_contents_ensure("$dir/paquete-documental.blade.php", <<<'HTML'
@extends('admin.usuarios.documentos.layout')

@section('content')
    @foreach($vistas_paquete as $index => $vista)
        <div class="document-container">
            @include($vista)
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@endsection
HTML);

// 3. Individuales comunes
$individuales_generales = [
    'constancia-registro' => [
        'titulo' => 'Constancia de Registro Institucional',
        'texto' => '<p class="text-justify">Por medio del presente documento, el <strong>Centro Geriátrico Jardín de los Recuerdos</strong> certifica que el usuario <strong style="color: #E27D60;">{{ $nombre_completo }}</strong>, con C.I. <strong>{{ $ci }}</strong>, se encuentra debidamente registrado en nuestra plataforma institucional bajo el rol de <strong>{{ $rol }}</strong>.</p><p class="text-justify">Este registro habilita al usuario para participar en los procesos y actividades vinculadas a su rol, bajo estricto apego a los reglamentos y normativas vigentes en nuestra institución.</p>'
    ],
    'checklist-requeridos' => [
        'titulo' => 'Checklist de Documentos Requeridos',
        'texto' => '<p class="text-justify">Para completar de forma satisfactoria su registro y expediente en el <strong>Centro Geriátrico Jardín de los Recuerdos</strong>, el usuario debe presentar de manera obligatoria los siguientes documentos:</p><div class="highlight-box"><ul>@foreach($requisitos as $req)<li>[ &nbsp; ] {{ $req }}</li>@endforeach</ul></div><p class="text-justify" style="color: #E27D60; font-weight: bold; font-size: 11px;">* La no presentación de estos documentos en los plazos establecidos podría suspender su habilitación en el sistema.</p>'
    ],
    'checklist-institucional' => [
        'titulo' => 'Checklist de Documentos Emitidos',
        'texto' => '<p class="text-justify">El <strong>Centro Geriátrico Jardín de los Recuerdos</strong> ha emitido formalmente los siguientes documentos institucionales para el usuario <strong style="color: #E27D60;">{{ $nombre_completo }}</strong>:</p><div class="highlight-box"><ul>@foreach($documentos_institucionales as $doc)<li style="color: #2E5C31;">[ &nbsp; ] <strong>{{ mb_strtoupper($doc["nombre"], "UTF-8") }}</strong><br><span style="color: #6B7280; font-weight: normal; font-size: 11px;">{{ $doc["descripcion"] }}</span></li>@endforeach</ul></div>'
    ],
    'carta-bienvenida' => [
        'titulo' => 'Carta de Bienvenida Institucional',
        'texto' => '<p class="text-justify">Estimado/a <strong style="color: #E27D60; font-size: 16px;">{{ $nombre_completo }}</strong>:</p><p class="text-justify">A nombre de todo el equipo de trabajo y la directiva del <strong>Centro Geriátrico Jardín de los Recuerdos</strong>, le damos la más cordial y cálida bienvenida a nuestra institución.</p><p class="text-justify">Apreciamos profundamente su vinculación a nuestra comunidad como <strong>{{ $rol }}</strong> y confiamos plenamente en que nuestra colaboración será fructífera, ética y, sobre todo, altamente beneficiosa para la calidad de vida y el bienestar de nuestros adultos mayores residentes.</p><p class="text-justify">Quedamos a su entera disposición para cualquier consulta, apoyo institucional o requerimiento que necesite para el excelente desarrollo de su labor.</p>'
    ],
    'confidencialidad' => [
        'titulo' => 'Compromiso de Confidencialidad y Privacidad',
        'texto' => '<p class="text-justify">Yo, <strong style="color: #E27D60;">{{ $nombre_completo }}</strong>, con C.I. <strong>{{ $ci }}</strong>, en mi calidad de <strong>{{ $rol }}</strong> en el <strong>Centro Geriátrico Jardín de los Recuerdos</strong>, declaro y me comprometo formalmente a:</p><ol><li>Mantener en estricta y absoluta confidencialidad toda información personal, médica, psicológica y financiera de los adultos mayores, sus familiares y del personal de la institución.</li><li>No divulgar, extraer, fotografiar ni compartir datos o documentos de la plataforma institucional con terceros, bajo ningún medio o pretexto.</li><li>Cumplir rigurosamente con las normativas nacionales e institucionales de protección de datos vigentes, respetando el derecho a la privacidad y el trato digno.</li></ol><div class="highlight-box"><p class="text-justify" style="margin:0;"><strong>DECLARACIÓN:</strong> Comprendo a cabalidad que el incumplimiento, parcial o total, de este compromiso de confidencialidad resultará en la inhabilitación inmediata del sistema y podrá derivar en las sanciones administrativas y/o acciones legales pertinentes.</p></div>'
    ],
    'uso-responsable' => [
        'titulo' => 'Compromiso de Uso Responsable del Sistema',
        'texto' => '<p class="text-justify">Mediante la suscripción del presente documento, el usuario asume la responsabilidad total y absoluta sobre el manejo ético y seguro de sus credenciales de acceso al sistema integral del <strong>Centro Geriátrico Jardín de los Recuerdos</strong>.</p><ul><li>La cuenta de usuario y contraseña son de carácter <strong>estrictamente personal e intransferible</strong>.</li><li>El usuario asume la autoría y responsabilidad legal de todas las acciones, registros y modificaciones efectuadas bajo su cuenta en el sistema.</li><li>Queda terminantemente prohibido el uso indebido, la alteración no autorizada de la información clínica/social o el intento de vulnerar los niveles de acceso del sistema.</li><li>Debe reportar de inmediato a la administración cualquier sospecha de acceso no autorizado a su cuenta.</li></ul>'
    ],
];

foreach ($individuales_generales as $slug => $data) {
    $blade = <<<HTML
@extends('admin.usuarios.documentos.layout')
@section('content')
    <div class="document-container">
        @include('admin.usuarios.documentos.partials.encabezado-documento', ['titulo' => '{$data['titulo']}'])
        @include('admin.usuarios.documentos.partials.datos-usuario')
        <div style="margin-top: 25px; padding: 0 10px;">
            {$data['texto']}
        </div>
        @include('admin.usuarios.documentos.partials.firmas')
    </div>
@endsection
HTML;
    file_put_contents_ensure("$dir/individuales/$slug.blade.php", $blade);
}

// 4. Individuales específicos
$especificos = [
    // Admin
    'responsabilidad-administrativa' => 'Acta de Responsabilidad Administrativa',
    'autorizacion-administrativa' => 'Autorización de Acceso Administrativo',
    // Personal Admin
    'responsabilidad-documental' => 'Acta de Responsabilidad Documental',
    'protocolo-documental' => 'Protocolo de Manejo Administrativo y Documental',
    // Personal Salud
    'confidencialidad-clinica' => 'Compromiso de Confidencialidad Clínica y Ética',
    'responsabilidad-clinica' => 'Responsabilidad Clínica y Médica Institucional',
    'protocolo-registro-clinico' => 'Protocolo Básico de Registro Clínico en Sistema',
    // Voluntario
    'compromiso-voluntario' => 'Carta de Compromiso Voluntario',
    'reglamento-voluntariado' => 'Reglamento Básico de Voluntariado Institucional',
    'trato-digno' => 'Protocolo de Trato Digno y Respetuoso al Adulto Mayor',
    // Familiar
    'constancia-familiar' => 'Constancia de Registro como Familiar o Responsable',
    'autorizacion-comunicacion' => 'Autorización de Comunicación Institucional Multicanal',
    'reglamento-visitas' => 'Reglamento General de Visitas al Centro Geriátrico',
    'protocolo-emergencias' => 'Protocolo de Acción y Contacto en Caso de Emergencias'
];

foreach ($especificos as $slug => $titulo) {
    $blade = <<<HTML
@extends('admin.usuarios.documentos.layout')
@section('content')
    <div class="document-container">
        @include('admin.usuarios.documentos.partials.encabezado-documento', ['titulo' => '$titulo'])
        @include('admin.usuarios.documentos.partials.datos-usuario')
        <div style="margin-top: 25px; padding: 0 10px;">
            <p class="text-justify">Este documento establece de forma vinculante las condiciones, lineamientos y compromisos específicos aplicables al desarrollo de su rol como <strong style="color: #E27D60;">{{ mb_strtoupper(\$rol, 'UTF-8') }}</strong> dentro de las instalaciones y el marco normativo del <strong>Centro Geriátrico Jardín de los Recuerdos</strong>.</p>
            <p class="text-justify">Por medio del presente, el usuario declara haber leído, comprendido a cabalidad y aceptado de libre voluntad todas las directrices, protocolos y reglamentos institucionales pertinentes para el correcto ejercicio de sus actividades, comprometiéndose a velar en todo momento por el bienestar, la dignidad y la integridad de nuestros residentes adultos mayores.</p>
            
            @if(isset(\$vinculos) && count(\$vinculos) > 0)
                <div class="highlight-box">
                    <h4 style="margin-top: 0;">ADULTOS MAYORES VINCULADOS A SU RESPONSABILIDAD:</h4>
                    <ul style="margin-bottom: 0;">
                        @foreach(\$vinculos as \$v)
                            <li style="color: #374151;"><strong>{{ \$v['nombre'] }}</strong> <br><span style="color: #E27D60; font-size: 11px;">PARENTESCO / RELACIÓN: {{ \$v['parentesco'] }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        @include('admin.usuarios.documentos.partials.firmas')
    </div>
@endsection
HTML;
    file_put_contents_ensure("$dir/individuales/$slug.blade.php", $blade);
}

echo "Plantillas Blade con nuevo diseño Premium (Verde/Terracota + Trama) generadas correctamente.";
