<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida/o a Casa Amandita</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
        .container { max-w-2xl: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #3F7D5A; color: white; padding: 30px 20px; text-align: center; }
        .content { padding: 30px; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .card { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-top: 15px; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .subtitle { font-size: 16px; margin-bottom: 20px; color: #4b5563; }
        .section-title { font-size: 18px; font-weight: bold; color: #3F7D5A; margin-top: 25px; margin-bottom: 10px; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background-color: #def7ec; color: #03543f; border: 1px solid #31c48d; }
        .badge-warning { background-color: #fdf6b2; color: #723b13; border: 1px solid #e3a008; }
        .badge-danger { background-color: #fde8e8; color: #9b1c1c; border: 1px solid #f98080; }
        ul { margin: 0; padding-left: 20px; }
        li { margin-bottom: 5px; }
        .credentials-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .password { font-family: monospace; font-size: 20px; font-weight: bold; letter-spacing: 2px; color: #166534; background: #fff; padding: 8px 15px; border-radius: 6px; border: 1px dashed #4ade80; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">Casa Amandita</div>
            <div style="font-size: 14px; opacity: 0.9;">Acceso Institucional RememberMind</div>
        </div>
        
        <div class="content">
            <h2 style="margin-top: 0;">¡Hola, {{ $nombre_completo }}!</h2>
            <p class="subtitle">
                @if($estado_general === 'ACTIVO')
                    Tu registro institucional ha sido completado satisfactoriamente.
                @elseif($estado_general === 'DOCUMENTACION_PENDIENTE' || count($documentos_pendientes) > 0)
                    Tu registro ha sido creado, pero cuentas con <strong>documentación pendiente</strong> que debes regularizar a la brevedad.
                @else
                    Tu registro institucional ha sido procesado.
                @endif
            </p>

            @if($plainPassword)
                <div class="credentials-box">
                    <h3 style="margin-top: 0; color: #166534;">Tus Credenciales de Acceso</h3>
                    <p style="margin: 5px 0;"><strong>Usuario/Correo:</strong> {{ $correo }}</p>
                    <p style="margin: 5px 0 0 0;"><strong>Contraseña Temporal:</strong></p>
                    <div class="password">{{ $plainPassword }}</div>
                    <p style="font-size: 12px; color: #166534; margin-top: 15px; margin-bottom: 0;">
                        <em>* Por seguridad, el sistema te solicitará cambiar esta contraseña en tu primer ingreso. No compartas estas credenciales.</em>
                    </p>
                </div>
            @else
                <div class="card">
                    <p style="margin: 0;">Tu cuenta ha sido vinculada exitosamente. Puedes acceder utilizando tu <strong>contraseña habitual</strong> asociada al correo <strong>{{ $correo }}</strong>.</p>
                </div>
            @endif

            <h3 class="section-title">Resumen de tu Perfil Institucional</h3>
            <div class="card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0; color: #6b7280; width: 40%;"><strong>Rol Asignado:</strong></td>
                        <td style="padding: 5px 0; font-weight: bold;">{{ $rol_label }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #6b7280;"><strong>Área / Clasificación:</strong></td>
                        <td style="padding: 5px 0; font-weight: bold;">{{ $area_nombre }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #6b7280;"><strong>Estado de Acceso:</strong></td>
                        <td style="padding: 5px 0;">
                            @if($estado_general === 'ACTIVO')
                                <span class="badge badge-success">ACTIVO</span>
                            @elseif($estado_general === 'DOCUMENTACION_PENDIENTE' || $estado_general === 'INSTITUCIONAL_PENDIENTE')
                                <span class="badge badge-warning">{{ str_replace('_', ' ', $estado_general) }}</span>
                            @else
                                <span class="badge badge-danger">{{ $estado_general }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <h3 class="section-title">Documentación del Trabajador</h3>
            <div class="card">
                @if(count($documentos_subidos) > 0)
                    <p style="margin-top: 0; font-weight: bold; color: #3F7D5A;">Documentos Recibidos:</p>
                    <ul style="margin-bottom: 15px;">
                        @foreach($documentos_subidos as $doc)
                            <li>{{ $doc }} <span style="color: #31c48d; font-size: 12px;">(Cargado)</span></li>
                        @endforeach
                    </ul>
                @endif

                @if(count($documentos_pendientes) > 0)
                    <p style="margin-top: 0; font-weight: bold; color: #9b1c1c;">Documentos Pendientes (Plazo 48h):</p>
                    <ul style="margin-bottom: 15px; color: #723b13;">
                        @foreach($documentos_pendientes as $doc)
                            <li>{{ $doc }}</li>
                        @endforeach
                    </ul>
                    <div style="background-color: #fdf6b2; padding: 10px; border-radius: 4px; font-size: 12px; color: #723b13;">
                        <strong>Importante:</strong> Tienes plazo hasta el <strong>{{ $fecha_limite }}</strong> para subir la documentación restante a través de la plataforma. El incumplimiento suspenderá temporalmente tu acceso.
                    </div>
                @else
                    <p style="margin: 0; color: #31c48d;"><strong>¡Excelente!</strong> No tienes documentación personal pendiente.</p>
                @endif
            </div>

            <h3 class="section-title">Documentación Institucional</h3>
            <div class="card">
                <p style="margin-top: 0; font-size: 13px;">Se han generado y/o adjuntado los siguientes documentos institucionales a este correo. Deberás imprimirlos, firmarlos y subirlos al sistema:</p>
                @if(count($documentos_institucionales_generados) > 0)
                    <ul>
                        @foreach($documentos_institucionales_generados as $doc)
                            <li>{{ $doc }}</li>
                        @endforeach
                    </ul>
                @else
                    <p style="margin-bottom: 0; font-style: italic; color: #6b7280;">No se generaron documentos institucionales adjuntos en este momento.</p>
                @endif
            </div>

            <p style="margin-top: 30px; font-size: 14px; text-align: center;">
                Si tienes alguna duda sobre tu proceso de incorporación, por favor contacta con Recursos Humanos o Administración.
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 0;">Este es un mensaje generado automáticamente por <strong>RememberMind</strong> para <strong>Casa Amandita</strong>.</p>
            <p style="margin: 5px 0 0 0;">Por favor, no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
