<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Paquete Documental</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mt-4 { margin-top: 1rem; }
        .w-full { width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        .bg-gray { background-color: #f3f4f6; }
        .header-logo { width: 120px; margin-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .signature-box { margin-top: 50px; text-align: center; width: 45%; display: inline-block; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        .border-box { border: 2px solid #000; padding: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    @foreach($vistas_paquete as $vista)
        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            <!-- Se incluye el partial de cada documento, pasando las variables requeridas -->
            @include($vista)
        </div>
    @endforeach
</body>
</html>
