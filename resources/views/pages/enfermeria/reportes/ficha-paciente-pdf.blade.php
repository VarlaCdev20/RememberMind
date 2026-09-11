<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha Operativa del Paciente</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #1e3a8a; margin: 0; }
        .subtitle { font-size: 12px; color: #64748b; margin-top: 5px; }
        
        .section { margin-bottom: 15px; }
        .section-title { font-size: 14px; font-weight: bold; background-color: #f1f5f9; padding: 5px; margin-bottom: 10px; border-left: 4px solid #3b82f6; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 6px; border: 1px solid #e2e8f0; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #475569; }
        
        .timeline { width: 100%; display: table; margin-bottom: 20px; }
        .timeline-step { display: table-cell; text-align: center; font-size: 10px; padding: 5px; border-top: 2px solid #e2e8f0; }
        .timeline-step.active { border-top-color: #10b981; color: #047857; font-weight: bold; }
        
        .badges { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-green { background-color: #d1fae5; color: #065f46; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-yellow { background-color: #fef3c7; color: #92400e; }
        
        .row { width: 100%; }
        .col-half { width: 48%; display: inline-block; vertical-align: top; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Ficha Operativa de Enfermería</h1>
        <p class="subtitle">Reporte Clínico-Operativo Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Timeline -->
    <div class="section">
        <div class="timeline">
            @foreach($timeline as $step => $isCompleted)
                <div class="timeline-step {{ $isCompleted ? 'active' : '' }}">
                    {{ $step }}<br>
                    {!! $isCompleted ? '&#10003;' : '-' !!}
                </div>
            @endforeach
        </div>
    </div>

    <!-- Datos Generales -->
    <div class="section">
        <div class="section-title">Datos Generales del Paciente</div>
        <table>
            <tr>
                <th>Paciente</th>
                <td>{{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }}</td>
                <th>CI</th>
                <td>{{ $adultoMayor->ci }}</td>
            </tr>
            <tr>
                <th>Edad</th>
                <td>{{ $adultoMayor->fecha_nac?->age ?? 'N/D' }} años</td>
                <th>Ubicación</th>
                <td>Hab. {{ $habitacionActual->codigo ?? 'N/A' }} / Cama {{ $camaActual->codigo ?? $camaActual->numero ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <span class="badges {{ $adultoMayor->estadoTexto === 'ACTIVO' ? 'badge-green' : 'badge-yellow' }}">
                        {{ $adultoMayor->estadoTexto }}
                    </span>
                </td>
                <th>Turno Actual</th>
                <td>
                    @if($adultoMayor->asignacionTurnoActiva)
                        {{ $adultoMayor->asignacionTurnoActiva->turno->nombre }} (Enf: {{ $adultoMayor->asignacionTurnoActiva->enfermero->nombres }})
                    @else
                        <span class="badges badge-red">Sin Asignar</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="row">
        <div class="col-half">
            <!-- Plan de Cuidado -->
            <div class="section">
                <div class="section-title">Plan de Cuidado</div>
                <table>
                    @if($adultoMayor->planCuidadoActivo)
                    <tr>
                        <th>Nivel</th>
                        <td>{{ $adultoMayor->planCuidadoActivo->nivel_cuidado }}</td>
                    </tr>
                    <tr>
                        <th>Versión</th>
                        <td>V.{{ $adultoMayor->planCuidadoActivo->version }}</td>
                    </tr>
                    @else
                    <tr>
                        <td colspan="2" style="text-align: center; color: #991b1b;">Plan Pendiente</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <!-- Resumen de Tareas -->
            <div class="section">
                <div class="section-title">Resumen de Tareas (Plan Activo)</div>
                <table>
                    <tr>
                        <th>Realizadas</th>
                        <td><span class="badges badge-green">{{ $tareasRealizadas }}</span></td>
                    </tr>
                    <tr>
                        <th>Pendientes</th>
                        <td><span class="badges badge-yellow">{{ $tareasPendientes }}</span></td>
                    </tr>
                    <tr>
                        <th>Omitidas</th>
                        <td><span class="badges badge-red">{{ $tareasOmitidas }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-half" style="margin-left: 2%;">
            <!-- Alertas Activas -->
            <div class="section">
                <div class="section-title">Alertas Activas</div>
                @if($adultoMayor->alertasAbiertas->isEmpty())
                    <p style="text-align: center; color: #065f46;">No hay alertas activas.</p>
                @else
                    <table>
                        <tr>
                            <th>Tipo</th>
                            <th>Prioridad</th>
                            <th>Fecha</th>
                        </tr>
                        @foreach($adultoMayor->alertasAbiertas->take(5) as $alerta)
                        <tr>
                            <td>{{ $alerta->tipo_alerta }}</td>
                            <td>
                                <span class="badges {{ in_array($alerta->nivel, ['ALTO', 'CRITICO']) ? 'badge-red' : ($alerta->nivel === 'MEDIO' ? 'badge-yellow' : 'badge-blue') }}">
                                    {{ $alerta->nivel }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($alerta->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        </div>
    </div>

    <!-- Signos Vitales -->
    <div class="section">
        <div class="section-title">Últimos Signos Vitales Registrados</div>
        @if($adultoMayor->signosVitales->isEmpty())
            <p>No hay registros de signos vitales recientes.</p>
        @else
            <table>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>PA (mmHg)</th>
                    <th>FC (lpm)</th>
                    <th>FR (rpm)</th>
                    <th>Temp (°C)</th>
                    <th>SatO2 (%)</th>
                </tr>
                @foreach($adultoMayor->signosVitales->take(5) as $signo)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($signo->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($signo->hora)->format('H:i') }}</td>
                    <td>{{ $signo->presion_formateada ?? 'N/D' }}</td>
                    <td>{{ $signo->frecuencia_cardiaca }}</td>
                    <td>{{ $signo->frecuencia_respiratoria }}</td>
                    <td>{{ $signo->temperatura }}</td>
                    <td>{{ $signo->saturacion }}</td>
                </tr>
                @endforeach
            </table>
        @endif
    </div>

    <!-- Próxima Medicación -->
    <div class="section">
        <div class="section-title">Próxima Medicación (Pendiente)</div>
        @if($adultoMayor->administracionesMedicacion->isEmpty())
            <p>No hay medicación pendiente programada.</p>
        @else
            <table>
                <tr>
                    <th>Fecha y Hora Prog.</th>
                    <th>Medicamento</th>
                    <th>Dosis / Vía</th>
                </tr>
                @foreach($adultoMayor->administracionesMedicacion as $admin)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($admin->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($admin->hora_programada)->format('H:i') }}</td>
                    <td>{{ $admin->medicacion->nombre_medicamento ?? 'N/A' }}</td>
                    <td>{{ $admin->medicacion->dosis ?? '' }} - {{ $admin->medicacion->via_administracion ?? '' }}</td>
                </tr>
                @endforeach
            </table>
        @endif
    </div>

    <!-- Seguimientos Diarios -->
    <div class="section">
        <div class="section-title">Últimos Seguimientos Diarios</div>
        @if($adultoMayor->seguimientosDiarios->isEmpty())
            <p>No hay registros de seguimiento reciente.</p>
        @else
            <table>
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Turno</th>
                    <th>Estado General</th>
                    <th>Observaciones</th>
                </tr>
                @foreach($adultoMayor->seguimientosDiarios as $seg)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($seg->fecha)->format('d/m/Y') }} {{ $seg->hora_inicio ? \Carbon\Carbon::parse($seg->hora_inicio)->format('H:i') : '' }}</td>
                    <td>{{ $seg->turno->nombre ?? 'N/A' }}</td>
                    <td>{{ $seg->estado_general }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($seg->observacion, 50) }}</td>
                </tr>
                @endforeach
            </table>
        @endif
    </div>

    <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #94a3b8;">
        Documento de uso interno - {{ config('app.name', 'Institución') }}<br>
        Generado por: {{ auth()->user()->nombres ?? 'Sistema' }}
    </div>
</body>
</html>
