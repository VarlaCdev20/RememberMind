@extends('layouts.reportes.reporte-base')

@section('titulo', 'Reporte General Institucional — CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS')

@section('contenido')

{{-- ── Barra de acciones (solo en vista HTML) ──────────────────────────── --}}
@unless($esPdf ?? false)
<div class="rm-acciones">
 <button onclick="window.print()" class="rm-btn rm-btn-print">Imprimir</button>
 <a href="{{ route('admin.reportes.institucional.pdf') }}" class="rm-btn rm-btn-pdf">Descargar PDF</a>
 <a href="{{ route('admin.reportes.institucional.excel') }}" class="rm-btn rm-btn-excel">Descargar Excel</a>
</div>
@endunless

{{-- ── 1. Encabezado institucional ───────────────────────────────────────── --}}
<div class="rm-header">
 <div class="rm-header-logo">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</div>
 <div class="rm-header-sub">RememberMind &nbsp;&middot;&nbsp; Sistema de Gestión Institucional</div>
 <div class="rm-header-badge">Reporte General Institucional</div>
 <div class="rm-header-meta">
 Generado el {{ $generadoEn }}
 &nbsp;&nbsp;|&nbsp;&nbsp;
 Usuario: <strong>{{ $usuario['nombre'] ?? 'Sistema' }}</strong>
 &nbsp;&nbsp;|&nbsp;&nbsp;
 Rol: {{ $usuario['rolLegible'] ?? 'Sin rol' }}
 </div>
</div>

{{-- ── 2. Resumen general (KPIs) ──────────────────────────────────────────── --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo">1. Resumen General Institucional</div>

 @php
 $coloresKpi = [
 'azul-profundo' => 'rm-kpi-valor-azul',
 'naranja' => 'rm-kpi-valor-naranja',
 'verde-salud' => 'rm-kpi-valor-verde',
 'morado-cog' => 'rm-kpi-valor-morado',
 'terracota' => '',
 'verde-olivo' => 'rm-kpi-valor-olivo',
 ];
 $kpisChunks = array_chunk($kpis ?? [], 3);
 @endphp

 <table class="rm-kpi-wrap">
 @foreach($kpisChunks as $fila)
 <tr class="rm-kpi-row">
 @foreach($fila as $kpi)
 @php $cls = $coloresKpi[$kpi['color']] ?? ''; @endphp
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor {{ $cls }}">{{ $kpi['valor'] }}</div>
 <div class="rm-kpi-titulo">{{ $kpi['titulo'] }}</div>
 <div class="rm-kpi-sub">{{ $kpi['subtitulo'] }}</div>
 </td>
 @endforeach
 {{-- Relleno si la fila tiene menos de 3 items --}}
 @for($i = count($fila); $i < 3; $i++)
 <td class="rm-kpi-cell" style="background:#ffffff; border-color:#ffffff;"></td>
 @endfor
 </tr>
 @endforeach
 </table>
</div>

{{-- ── 3. Salud y seguimiento ─────────────────────────────────────────────── --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo-verde">2. Salud y Seguimiento Clínico-Asistencial</div>

 <table class="rm-metrics-row">
 <tr>
 <td class="rm-metric-left">
 <div class="rm-metric-item">
 <div class="rm-metric-label">Fichas médicas activas</div>
 <div class="rm-metric-val rm-metric-val-terracota">{{ $salud['fichasActivas'] ?? 0 }}</div>
 </div>
 <div class="rm-metric-item {{ ($salud['adultosSinFicha'] ?? 0) > 0 ? 'border-left: 3px solid #E97A5F;' : '' }}">
 <div class="rm-metric-label">Sin ficha médica activa</div>
 <div class="rm-metric-val {{ ($salud['adultosSinFicha'] ?? 0) > 0 ? 'rm-metric-val-terracota' : '' }}">
 {{ $salud['adultosSinFicha'] ?? 0 }}
 </div>
 </div>
 <div class="rm-metric-item">
 <div class="rm-metric-label">Medicaciones activas</div>
 <div class="rm-metric-val rm-metric-val-verde">{{ $salud['medicacionesActivas'] ?? 0 }}</div>
 </div>
 <div class="rm-metric-item">
 <div class="rm-metric-label">Signos vitales (últ. 7 das)</div>
 <div class="rm-metric-val">{{ $salud['signosVitales7d'] ?? 0 }}</div>
 </div>
 <div class="rm-metric-item">
 <div class="rm-metric-label">Administraciones medicación hoy</div>
 <div class="rm-metric-val">{{ $salud['adminMedicacionHoy'] ?? 0 }}</div>
 </div>
 </td>
 <td class="rm-metric-right">
 <div class="rm-metric-item">
 <div class="rm-metric-label">Atenciones médicas (mes en curso)</div>
 <div class="rm-metric-val">{{ $salud['atencionesMes'] ?? 0 }}</div>
 </div>
 <div class="rm-metric-item">
 <div class="rm-metric-label">Valoraciones funcionales (últ. 30 das)</div>
 <div class="rm-metric-val">{{ $salud['valoracionesRecientes'] ?? 0 }}</div>
 </div>
 <div class="rm-metric-item {{ ($salud['altaDependencia'] ?? 0) > 0 ? '' : '' }}">
 <div class="rm-metric-label">Alta dependencia funcional</div>
 <div class="rm-metric-val {{ ($salud['altaDependencia'] ?? 0) > 0 ? 'rm-metric-val-naranja' : '' }}">
 {{ $salud['altaDependencia'] ?? 0 }}
 </div>
 </div>
 <div class="rm-metric-item">
 <div class="rm-metric-label">Evaluaciones cognitivas (últ. 30 das)</div>
 <div class="rm-metric-val">{{ $salud['evalCognitivas30d'] ?? 0 }}</div>
 </div>
 <div class="rm-metric-item {{ ($salud['riesgoCaidaAlto'] ?? 0) > 0 ? '' : '' }}">
 <div class="rm-metric-label">Riesgo de caída alto</div>
 <div class="rm-metric-val {{ ($salud['riesgoCaidaAlto'] ?? 0) > 0 ? 'rm-metric-val-naranja' : '' }}">
 {{ $salud['riesgoCaidaAlto'] ?? 0 }}
 </div>
 </div>
 </td>
 </tr>
 </table>
</div>

{{-- ── 4. Red familiar ────────────────────────────────────────────────────── --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo-naranja">3. Red Familiar</div>

 <table class="rm-table">
 <thead>
 <tr>
 <th>Indicador</th>
 <th style="width:100px; text-align:center;">Total</th>
 </tr>
 </thead>
 <tbody>
 <tr>
 <td>Familiares registrados en el sistema</td>
 <td style="text-align:center; font-weight:bold;">{{ $redFamiliar['totalFamiliares'] ?? 0 }}</td>
 </tr>
 <tr>
 <td>Adultos mayores con familiar vinculado</td>
 <td style="text-align:center; font-weight:bold;">{{ $redFamiliar['adultosConFamiliar'] ?? 0 }}</td>
 </tr>
 <tr>
 <td>Adultos activos sin familiar vinculado</td>
 <td style="text-align:center; font-weight:bold; {{ ($redFamiliar['adultosSinFamiliar'] ?? 0) > 0 ? 'color:#E97A5F;' : '' }}">
 {{ $redFamiliar['adultosSinFamiliar'] ?? 0 }}
 </td>
 </tr>
 <tr>
 <td>Responsables familiares registrados</td>
 <td style="text-align:center; font-weight:bold;">{{ $redFamiliar['responsables'] ?? 0 }}</td>
 </tr>
 </tbody>
 </table>

 @if(!empty($redFamiliar['parentescos']))
 <table class="rm-table" style="margin-top:6px;">
 <thead>
 <tr>
 <th>Parentesco</th>
 <th style="width:80px; text-align:center;">Cantidad</th>
 </tr>
 </thead>
 <tbody>
 @foreach($redFamiliar['parentescos'] as $p)
 <tr>
 <td>{{ $p['parentesco'] ?? 'Sin parentesco' }}</td>
 <td style="text-align:center; font-weight:bold;">{{ $p['total'] }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @endif
</div>

{{-- ── 5. Equipo institucional ─────────────────────────────────────────────── --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo-morado">4. Equipo Institucional</div>

 @php
 $ps = $equipo['personal_salud'] ?? [];
 $pa = $equipo['personal_admin'] ?? [];
 $vol = $equipo['voluntarios'] ?? [];
 @endphp

 <table class="rm-table">
 <thead>
 <tr>
 <th>Área</th>
 <th style="width:80px; text-align:center;">Total</th>
 <th style="width:80px; text-align:center;">Activos</th>
 <th>Detalle adicional</th>
 </tr>
 </thead>
 <tbody>
 <tr>
 <td><strong>Personal de Salud</strong></td>
 <td style="text-align:center; font-weight:bold;">{{ $ps['total'] ?? 0 }}</td>
 <td style="text-align:center;">{{ $ps['activos'] ?? 0 }}</td>
 <td>{{ $equipo['especialidades_total'] ?? 0 }} especialidad(es) registradas</td>
 </tr>
 <tr>
 <td><strong>Personal Administrativo</strong></td>
 <td style="text-align:center; font-weight:bold;">{{ $pa['total'] ?? 0 }}</td>
 <td style="text-align:center;">{{ $pa['activos'] ?? 0 }}</td>
 <td>{{ count($pa['cargos'] ?? []) }} cargo(s) identificados</td>
 </tr>
 <tr>
 <td><strong>Voluntarios</strong></td>
 <td style="text-align:center; font-weight:bold;">{{ $vol['total'] ?? 0 }}</td>
 <td style="text-align:center;">{{ $vol['activos'] ?? 0 }}</td>
 <td>{{ $vol['asignados'] ?? 0 }} con asignación activa</td>
 </tr>
 </tbody>
 </table>

 @if(!empty($ps['especialidades']))
 <p style="font-size:8.5pt; font-weight:bold; color:#7A68B0; margin:8px 0 4px;">Especialidades de salud principales:</p>
 <table class="rm-table">
 <thead>
 <tr><th>Especialidad</th><th style="width:80px; text-align:center;">Personal</th></tr>
 </thead>
 <tbody>
 @foreach(array_slice($ps['especialidades'], 0, 6) as $esp)
 <tr>
 <td>{{ $esp['nombre'] }}</td>
 <td style="text-align:center;">{{ $esp['total'] }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @endif

 @if(!empty($vol['areas']))
 <p style="font-size:8.5pt; font-weight:bold; color:#63775B; margin:8px 0 4px;">Áreas de voluntariado:</p>
 <table class="rm-table">
 <thead>
 <tr><th>Área de apoyo</th><th style="width:80px; text-align:center;">Voluntarios</th></tr>
 </thead>
 <tbody>
 @foreach($vol['areas'] as $area)
 <tr>
 <td>{{ $area['area'] }}</td>
 <td style="text-align:center;">{{ $area['total'] }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @endif
</div>

{{-- ── 6. Alertas ──────────────────────────────────────────────────────────── --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo-terracota">5. Alertas Administrativas</div>

 @foreach($alertas as $alerta)
 @php
 $nivel = $alerta['nivel'] ?? 'INFORMATIVA';
 $clsAlerta = $nivel === 'OK' ? 'ok' : ($nivel === 'INFORMATIVA' ? 'info' : '');
 @endphp
 <div class="rm-alerta {{ $clsAlerta }}">
 <div class="rm-alerta-nivel">{{ $nivel }}</div>
 <div>{{ $alerta['descripcion'] ?? '' }}</div>
 @if(!empty($alerta['accion']))
 <div style="font-size:8pt; color:#888888; margin-top:2px;">Acción sugerida: {{ $alerta['accion'] }}</div>
 @endif
 </div>
 @endforeach
</div>

{{-- ── 7. Bitácora ─────────────────────────────────────────────────────────── --}}
@if(!empty($bitacora))
<div class="rm-seccion">
 <div class="rm-seccion-titulo">6. Últimas Acciones en Bitácora</div>

 <table class="rm-table">
 <thead>
 <tr>
 <th style="width:130px;">Fecha</th>
 <th style="width:140px;">Usuario</th>
 <th>Acción</th>
 <th style="width:100px;">Módulo</th>
 </tr>
 </thead>
 <tbody>
 @foreach($bitacora as $log)
 <tr>
 <td style="font-size:8.5pt;">{{ $log['fecha'] ?? '-' }}</td>
 <td style="font-weight:bold; font-size:8.5pt;">{{ $log['usuario'] ?? 'Sistema' }}</td>
 <td style="font-size:8.5pt;">{{ $log['accion'] ?? '-' }}</td>
 <td style="font-size:8.5pt;">{{ $log['modulo'] ?? 'General' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
</div>
@endif

{{-- ── Nota legal ──────────────────────────────────────────────────────────── --}}
<div class="rm-nota-legal">
 Las alertas y métricas de salud son orientativas y no constituyen diagnóstico médico.
 Este reporte es de uso institucional exclusivo de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
</div>

{{-- ── Footer ──────────────────────────────────────────────────────────────── --}}
<div class="rm-footer">
 RememberMind &nbsp;&middot;&nbsp; CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS &nbsp;&middot;&nbsp;
 Generado el {{ $generadoEn }} &nbsp;&middot;&nbsp;
 Confidencial &mdash; solo para uso institucional
</div>

@endsection
