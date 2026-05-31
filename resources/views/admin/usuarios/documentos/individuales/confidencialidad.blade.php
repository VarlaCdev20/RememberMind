@extends('admin.usuarios.documentos.layout')
@section('content')
    <div class="document-container">
        @include('admin.usuarios.documentos.partials.encabezado-documento', ['titulo' => 'Compromiso de Confidencialidad y Privacidad'])
        @include('admin.usuarios.documentos.partials.datos-usuario')
        <div style="margin-top: 25px; padding: 0 10px;">
            <p class="text-justify">Yo, <strong style="color: #E27D60;">{{ $nombre_completo }}</strong>, con C.I. <strong>{{ $ci }}</strong>, en mi calidad de <strong>{{ $rol }}</strong> en el <strong>Centro Geriátrico Jardín de los Recuerdos</strong>, declaro y me comprometo formalmente a:</p><ol><li>Mantener en estricta y absoluta confidencialidad toda información personal, médica, psicológica y financiera de los adultos mayores, sus familiares y del personal de la institución.</li><li>No divulgar, extraer, fotografiar ni compartir datos o documentos de la plataforma institucional con terceros, bajo ningún medio o pretexto.</li><li>Cumplir rigurosamente con las normativas nacionales e institucionales de protección de datos vigentes, respetando el derecho a la privacidad y el trato digno.</li></ol><div class="highlight-box"><p class="text-justify" style="margin:0;"><strong>DECLARACIÓN:</strong> Comprendo a cabalidad que el incumplimiento, parcial o total, de este compromiso de confidencialidad resultará en la inhabilitación inmediata del sistema y podrá derivar en las sanciones administrativas y/o acciones legales pertinentes.</p></div>
        </div>
        @include('admin.usuarios.documentos.partials.firmas')
    </div>
@endsection