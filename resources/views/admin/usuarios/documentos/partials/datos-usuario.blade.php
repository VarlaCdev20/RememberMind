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