<table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #E27D60; padding-bottom: 8px;">
 <tr>
 <td>
 <div style="font-size: 15px; font-weight: 900; color: #2F3E5C; text-transform: uppercase; letter-spacing: 0.08em;">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</div>
 <div style="font-size: 9px; font-weight: bold; color: #967B66; margin-top: 1px;">RememberMind • Gestión Residencial Integral</div>
 </td>
 <td style="text-align: right; font-size: 8px; color: #7C7168; line-height: 1.3;">
 <strong>Reporte: {{ $report_title ?? 'Reporte Técnico' }}</strong><br>
 Fecha: {{ $fecha ?? now()->format('d/m/Y H:i') }}<br>
 Operador: {{ $usuario ?? 'Sistema' }}
 </td>
 </tr>
</table>
