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