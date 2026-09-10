<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <title>Expediente - {{ $adulto->cod_am }}</title>
 <style>
 body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 11pt; color: #1a1a1a; margin: 2cm; }
 h1 { font-size: 18pt; color: #2F3E5C; text-align: center; border-bottom: 2pt solid #2F3E5C; padding-bottom: 6pt; margin-bottom: 14pt; }
 h2 { font-size: 13pt; color: #2F3E5C; background: #E8EDF4; padding: 4pt 8pt; margin-top: 18pt; margin-bottom: 6pt; border-left: 4pt solid #2F3E5C; }
 h3 { font-size: 11pt; color: #E27D60; margin-top: 12pt; margin-bottom: 4pt; }
 .sub-header { text-align: center; color: #666; font-size: 9pt; margin-bottom: 20pt; }
 table { width: 100%; border-collapse: collapse; margin-bottom: 12pt; font-size: 10pt; }
 th { background: #2F3E5C; color: #fff; padding: 5pt 8pt; text-align: left; font-size: 9pt; text-transform: uppercase; }
 td { padding: 4pt 8pt; border-bottom: 0.5pt solid #ddd; vertical-align: top; }
 tr:nth-child(even) td { background: #F8F6F3; }
 .label { color: #888; font-size: 9pt; font-weight: bold; text-transform: uppercase; width: 38%; }
 .value { font-weight: bold; }
 .badge { display: inline-block; background: #617453; color: #fff; padding: 1pt 6pt; border-radius: 3pt; font-size: 9pt; }
 .badge-red { background: #E27D60; }
 .badge-blue { background: #5B5F97; }
 .badge-amber { background: #E2A45F; }
 .footer { margin-top: 30pt; border-top: 0.5pt solid #ccc; padding-top: 10pt; font-size: 8pt; color: #999; text-align: center; }
 .signature-row { width: 100%; margin-top: 40pt; }
 .sig-box { width: 45%; display: inline-block; text-align: center; vertical-align: top; }
 .sig-line { border-top: 0.5pt solid #333; width: 80%; margin: 30pt auto 4pt; }
 .sig-label { font-size: 8pt; font-weight: bold; text-transform: uppercase; }
 .section-info { background: #FDFBFA; border: 0.5pt solid #ddd; padding: 8pt; border-radius: 3pt; margin-bottom: 8pt; }
 .empty-note { color: #aaa; font-style: italic; font-size: 9pt; }
 .two-col { width: 48%; display: inline-block; vertical-align: top; margin-right: 3%; }
 </style>
</head>
<body>

{{-- Encabezado --}}
<h1>FICHA TÉCNICA INDIVIDUAL<br><small style="font-size: 14pt;">{{ strtoupper($adulto->nombres . ' ' . $adulto->ap_paterno . ' ' . $adulto->ap_materno) }}</small></h1>
<div class="sub-header">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS · RememberMind · Expediente: <strong>{{ $adulto->cod_am }}</strong> · Generado: {{ now()->format('d/m/Y H:i') }}<br>
 <em>Documento confidencial — uso institucional exclusivo</em>
</div>

{{-- Datos personales --}}
<h2>1. Identificación Personal</h2>
<table>
 <tr><td class="label">Código de expediente</td><td class="value">{{ $adulto->cod_am }}</td>
 <td class="label">Estado actual</td><td class="value"><span class="badge">{{ $adulto->estado?->estado ?? 'ACTIVO' }}</span></td></tr>
 <tr><td class="label">Nombres</td><td class="value" colspan="3">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</td></tr>
 <tr><td class="label">C.I.</td><td class="value">{{ $adulto->ci }}{{ $adulto->complemento_ci ? '-'.$adulto->complemento_ci : '' }} ({{ $adulto->expedicion_ci }})</td>
 <td class="label">Estado civil</td><td class="value">{{ $adulto->estado_civil ?? '—' }}</td></tr>
 <tr><td class="label">Fecha de nacimiento</td><td class="value">{{ $adulto->fecha_nac ? $adulto->fecha_nac->format('d/m/Y') : '—' }}</td>
 <td class="label">Edad</td><td class="value">{{ $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->age : '—' }} años</td></tr>
 <tr><td class="label">Género</td><td class="value">{{ $adulto->genero ?? '—' }}</td>
 <td class="label">Grupo sanguíneo</td><td class="value">{{ $adulto->grupo_sanguineo ?? '—' }}</td></tr>
 <tr><td class="label">Seguro de salud</td><td class="value">{{ $adulto->seguro_salud ?? '—' }}</td>
 <td class="label">Nivel educativo</td><td class="value">{{ $adulto->nivel_educat ?? '—' }}</td></tr>
 <tr><td class="label">Alergias</td><td class="value" colspan="3">{{ $adulto->alergias ?? '—' }}</td></tr>
</table>

<h2>2. Domicilio y Contacto</h2>
<table>
 <tr><td class="label">Departamento</td><td class="value">{{ $adulto->departamento_residencia ?? '—' }}</td>
 <td class="label">Ciudad / Municipio</td><td class="value">{{ $adulto->ciudad_municipio ?? '—' }}</td></tr>
 <tr><td class="label">Zona / Barrio</td><td class="value">{{ $adulto->zona ?? '—' }}</td>
 <td class="label">Calle / Avenida</td><td class="value">{{ $adulto->calle ?? '—' }}</td></tr>
 <tr><td class="label">Celular</td><td class="value">{{ $adulto->celular ?? '—' }}</td>
 <td class="label">Teléfono fijo</td><td class="value">{{ $adulto->telefono_fijo ?? '—' }}</td></tr>
</table>

<h2>3. Contacto de Emergencia</h2>
<table>
 <tr><td class="label">Nombre</td><td class="value">{{ $adulto->contacto_emergencia_nombre ?? '—' }}</td>
 <td class="label">Parentesco</td><td class="value">{{ $adulto->contacto_emergencia_parentesco ?? '—' }}</td></tr>
 <tr><td class="label">Celular</td><td class="value">{{ $adulto->contacto_emergencia_celular ?? '—' }}</td>
 <td class="label">Responsable principal</td><td class="value">{{ $adulto->responsable_principal ? 'Sí' : 'No' }}</td></tr>
 <tr><td class="label">Dirección</td><td class="value" colspan="3">{{ $adulto->contacto_emergencia_direccion ?? '—' }}</td></tr>
 <tr><td class="label">Autorizado inf. médica</td><td class="value" colspan="3">{{ $adulto->autorizado_informacion_medica ? 'Sí' : 'No' }}</td></tr>
</table>

<h2>4. Datos Institucionales de Ingreso</h2>
<table>
 <tr><td class="label">Fecha de ingreso</td><td class="value">{{ $adulto->fecha_ing ? $adulto->fecha_ing->format('d/m/Y') : '—' }}</td>
 <td class="label">Hora de ingreso</td><td class="value">{{ $adulto->hora_ing ? substr($adulto->hora_ing, 0, 5) : '—' }}</td></tr>
 <tr><td class="label">Tipo de ingreso</td><td class="value">{{ $adulto->tipo_ing ?? '—' }}</td>
 <td class="label">Permanencia</td><td class="value">{{ $adulto->permanencia ?? '—' }}</td></tr>
 <tr><td class="label">Observaciones de ingreso</td><td class="value" colspan="3">{{ $adulto->observaciones ?? '—' }}</td></tr>
</table>

{{-- Ficha Médica --}}
@if(isset($fichasMedicas) && $fichasMedicas->isNotEmpty())
<h2>5. Ficha Médica</h2>
@foreach($fichasMedicas->take(1) as $ficha)
<div class="section-info">
@php
 $condiciones = array_filter([
 $ficha->hipertension ? 'Hipertensión' : null,
 $ficha->diabetes ? 'Diabetes' : null,
 $ficha->problemas_cardiacos? 'Problemas cardíacos' : null,
 $ficha->acv ? 'ACV' : null,
 $ficha->parkinson ? 'Parkinson' : null,
 $ficha->epilepsia ? 'Epilepsia' : null,
 $ficha->alzheimer_diagnosticado ? 'Alzheimer diagnosticado' : null,
 $ficha->depresion ? 'Depresión' : null,
 $ficha->ansiedad ? 'Ansiedad' : null,
 $ficha->problemas_sueno ? 'Trastornos del sueño': null,
 $ficha->problemas_visuales ? 'Problemas visuales' : null,
 $ficha->problemas_auditivos? 'Problemas auditivos' : null,
 $ficha->dolor_cronico ? 'Dolor crónico' : null,
 ]);
@endphp
<strong>Condiciones registradas:</strong>
@if(count($condiciones) > 0)
 {{ implode(', ', $condiciones) }}
@else
 <span class="empty-note">Sin condiciones registradas.</span>
@endif
<br><br>
<strong>Alergias:</strong> {{ $ficha->alergias ?? '—' }}<br>
<strong>Restricciones alimentarias:</strong> {{ $ficha->restricciones_alimentarias ?? '—' }}<br>
<strong>Hospitalizaciones previas:</strong> {{ $ficha->hospitalizaciones ?? '—' }}<br>
<strong>Cirugías previas:</strong> {{ $ficha->cirugias ?? '—' }}<br>
<strong>Observación médica:</strong> {{ $ficha->observacion_medica ?? '—' }}
</div>
@endforeach
@endif

{{-- Últimos Signos Vitales --}}
@if(isset($signosVitales) && $signosVitales->isNotEmpty())
<h2>6. Últimos Signos Vitales</h2>
<table>
 <tr>
 <th>Fecha</th><th>Presión</th><th>Pulso</th><th>Temp.</th><th>Sat.</th><th>Glucosa</th><th>Peso</th><th>IMC</th>
 </tr>
 @foreach($signosVitales->where('estado', 'VIGENTE')->take(10) as $sv)
 <tr>
 <td>{{ $sv->fecha ? $sv->fecha->format('d/m/Y') : '—' }}</td>
 <td>{{ $sv->presion_sistolica ?? '—' }}/{{ $sv->presion_diastolica ?? '—' }}</td>
 <td>{{ $sv->frecuencia_cardiaca ?? '—' }} lpm</td>
 <td>{{ $sv->temperatura ?? '—' }} °C</td>
 <td>{{ $sv->saturacion ?? '—' }} %</td>
 <td>{{ $sv->glucosa ?? '—' }}</td>
 <td>{{ $sv->peso ?? '—' }} kg</td>
 <td>{{ $sv->imc ?? '—' }}</td>
 </tr>
 @endforeach
</table>
@endif

{{-- Medicación Activa --}}
@if(isset($medicaciones) && $medicaciones->isNotEmpty())
<h2>7. Medicación</h2>
<table>
 <tr><th>Medicamento</th><th>Dosis</th><th>Frecuencia</th><th>Vía</th><th>Desde</th><th>Estado</th></tr>
 @foreach($medicaciones as $med)
 <tr>
 <td>{{ $med->nombre_medicamento }}</td>
 <td>{{ $med->dosis }}</td>
 <td>{{ $med->frecuencia }}</td>
 <td>{{ $med->via_administracion }}</td>
 <td>{{ $med->fecha_inicio ? $med->fecha_inicio->format('d/m/Y') : '—' }}</td>
 <td><span class="badge {{ $med->estado === 'ACTIVO' ? '' : 'badge-amber' }}">{{ $med->estado }}</span></td>
 </tr>
 @endforeach
</table>
@endif

{{-- Valoración Funcional --}}
@if(isset($valoracionesFuncionales) && $valoracionesFuncionales->isNotEmpty())
<h2>8. Valoración Funcional</h2>
@php $ultimaVal = $valoracionesFuncionales->first(); @endphp
<table>
 <tr><td class="label">Última valoración</td><td class="value">{{ $ultimaVal->fecha_valoracion ? $ultimaVal->fecha_valoracion->format('d/m/Y') : '—' }}</td>
 <td class="label">Índice Barthel</td><td class="value">{{ $ultimaVal->indice_barthel ?? '—' }} / 100</td></tr>
 <tr><td class="label">Nivel dependencia</td><td class="value"><span class="badge badge-blue">{{ $ultimaVal->nivel_dependencia ?? '—' }}</span></td>
 <td class="label">Riesgo de caída</td><td class="value">{{ $ultimaVal->riesgo_caida ?? '—' }}</td></tr>
</table>
@endif

{{-- Evaluaciones Cognitivas --}}
@if(isset($evaluaciones) && $evaluaciones->isNotEmpty())
<h2>9. Evaluaciones Cognitivas</h2>
<table>
 <tr><th>Fecha</th><th>Tipo</th><th>Puntaje</th><th>Máximo</th><th>Interpretación</th></tr>
 @foreach($evaluaciones as $ev)
 <tr>
 <td>{{ $ev->fecha_eval ? $ev->fecha_eval->format('d/m/Y') : '—' }}</td>
 <td>{{ $ev->tipoEvaluacion?->nombre ?? '—' }}</td>
 <td><strong>{{ $ev->puntaje_total ?? '—' }}</strong></td>
 <td>{{ $ev->puntaje_maximo ?? '—' }}</td>
 <td>{{ $ev->resultado_interpretacion ?? '—' }}</td>
 </tr>
 @endforeach
</table>
@endif

{{-- Familiares --}}
@if(isset($familiares) && $familiares->isNotEmpty())
<h2>10. Red de Apoyo Familiar</h2>
<table>
 <tr><th>Nombre</th><th>Parentesco</th><th>Estado</th></tr>
 @foreach($familiares as $fam)
 <tr>
 <td>{{ $fam->nombre_completo ?? ($fam->nombres ?? '—') }}</td>
 <td>{{ $fam->pivot?->parentesco_vinculo ?? '—' }}</td>
 <td>{{ $fam->pivot?->estado ?? 'ACTIVO' }}</td>
 </tr>
 @endforeach
</table>
@endif

{{-- Observaciones recientes --}}
@if(isset($observaciones) && $observaciones->isNotEmpty())
<h2>11. Observaciones Institucionales Recientes</h2>
<table>
 <tr><th>Fecha</th><th>Observación</th></tr>
 @foreach($observaciones->take(10) as $obs)
 <tr>
 <td style="white-space: nowrap;">{{ $obs->fecha ? \Carbon\Carbon::parse($obs->fecha)->format('d/m/Y') : '—' }}</td>
 <td>{{ $obs->observacion ?? $obs->descripcion ?? '—' }}</td>
 </tr>
 @endforeach
</table>
@endif

{{-- Pie de firma --}}
<table class="signature-row">
 <tr>
 <td class="sig-box" style="width:45%; text-align: center;">
 <div class="sig-line"></div>
 <div class="sig-label">Responsable del Expediente</div>
 </td>
 <td style="width:10%;"></td>
 <td class="sig-box" style="width:45%; text-align: center;">
 <div class="sig-line"></div>
 <div class="sig-label">Director/a CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</div>
 </td>
 </tr>
</table>

<div class="footer">
 Documento generado por RememberMind · CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS · {{ now()->format('d/m/Y H:i') }} · Expediente {{ $adulto->cod_am }}<br>
 Este documento es confidencial y de uso institucional. Prohibida su reproducción sin autorización.
</div>

</body>
</html>
