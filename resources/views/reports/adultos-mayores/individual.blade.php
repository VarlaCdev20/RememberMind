@extends('reports.layouts.report-layout')

@section('title', 'Expediente Clínico y Administrativo Integral')
@section('report_title', 'Expediente Integral de Residente')

@section('content')
    <div class="section-title">Información de Identidad y Admisión</div>
    
    <div class="details-box">
        <table class="details-table">
            <tr>
                <td class="label-detail">Nombre Completo:</td>
                <td class="value-detail"><strong>{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</strong></td>
            </tr>
            <tr>
                <td class="label-detail">RUT / Identificación:</td>
                <td class="value-detail">{{ $adulto->rut ?? 'No Registrado' }}</td>
            </tr>
            <tr>
                <td class="label-detail">Edad / Nacimiento:</td>
                <td class="value-detail">{{ $adulto->edad ?? 'No calculada' }} años ({{ $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->format('d/m/Y') : 'Sin fecha' }})</td>
            </tr>
            <tr>
                <td class="label-detail">Género:</td>
                <td class="value-detail">{{ $adulto->genero ?? 'No Definido' }}</td>
            </tr>
            <tr>
                <td class="label-detail">Estado Clínico-Residencial:</td>
                <td class="value-detail">
                    <strong>{{ $adulto->estado ? $adulto->estado->estado : 'Sin Estado' }}</strong>
                </td>
            </tr>
            <tr>
                <td class="label-detail">Observaciones de Ingreso:</td>
                <td class="value-detail">{{ $adulto->motivo_archivado ?? 'Ingreso regular sin observaciones especiales.' }}</td>
            </tr>
        </table>
    </div>

    <!-- Módulo Familiar -->
    <div class="section-title">Vínculos Familiares y Contactos de Emergencia</div>
    @if ($familiares && $familiares->count() > 0)
        <table class="table-institutional">
            <thead>
                <tr>
                    <th style="width: 35%;">Familiar / Contacto</th>
                    <th style="width: 25%;">Parentesco</th>
                    <th style="width: 20%;">Teléfono</th>
                    <th style="width: 20%;">Es Tutor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($familiares as $f)
                    <tr>
                        <td><strong>{{ $f->usuario ? $f->usuario->name : 'Sin Nombre' }}</strong></td>
                        <td>{{ $f->parentesco ?? 'Contacto General' }}</td>
                        <td>{{ $f->telefono ?? 'Sin número' }}</td>
                        <td>{{ $f->pivot && $f->pivot->es_tutor ? 'SÍ (Tutor Legal)' : 'NO' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 10px; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 4px; text-align: center; color: #7C7168; font-size: 9px; margin-bottom: 15px;">
            No se encuentran familiares o contactos vinculados actualmente.
        </div>
    @endif

    <!-- Módulo Médico & Salud -->
    <div class="section-title">Último Control de Signos Vitales</div>
    @if ($signosVitales && $signosVitales->count() > 0)
        @php $sv = $signosVitales->first(); @endphp
        <div class="details-box">
            <table class="details-table">
                <tr>
                    <td class="label-detail">Fecha del Control:</td>
                    <td class="value-detail"><strong>{{ \Carbon\Carbon::parse($sv->fecha)->format('d/m/Y') }} a las {{ $sv->hora }}</strong></td>
                </tr>
                <tr>
                    <td class="label-detail">Presión Arterial:</td>
                    <td class="value-detail">{{ $sv->presion_sistolica }}/{{ $sv->presion_diastolica }} mmHg</td>
                </tr>
                <tr>
                    <td class="label-detail">Frecuencia Cardíaca:</td>
                    <td class="value-detail">{{ $sv->frecuencia_cardiaca }} lpm</td>
                </tr>
                <tr>
                    <td class="label-detail">Temperatura Corporal:</td>
                    <td class="value-detail">{{ $sv->temperatura }} °C</td>
                </tr>
                <tr>
                    <td class="label-detail">Saturación de Oxígeno:</td>
                    <td class="value-detail">{{ $sv->saturacion_oxigeno }}%</td>
                </tr>
            </table>
        </div>
    @else
        <div style="padding: 10px; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 4px; text-align: center; color: #7C7168; font-size: 9px; margin-bottom: 15px;">
            No se han registrado controles de signos vitales recientemente.
        </div>
    @endif

    <!-- Medicaciones Activas -->
    <div class="section-title">Medicaciones y Prescripciones Activas</div>
    @if ($medicaciones && $medicaciones->count() > 0)
        <table class="table-institutional">
            <thead>
                <tr>
                    <th style="width: 30%;">Medicamento</th>
                    <th style="width: 20%;">Dosis</th>
                    <th style="width: 25%;">Frecuencia</th>
                    <th style="width: 25%;">Vía de Adm.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($medicaciones as $m)
                    <tr>
                        <td><strong>{{ $m->nombre_med }}</strong></td>
                        <td>{{ $m->dosis }}</td>
                        <td>{{ $m->frecuencia ?? 'Según indicación médica' }}</td>
                        <td>{{ $m->via_administracion ?? 'ORAL' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 10px; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 4px; text-align: center; color: #7C7168; font-size: 9px; margin-bottom: 15px;">
            No se registran prescripciones ni medicamentos activos en la ficha.
        </div>
    @endif

    <!-- Evaluaciones Cognitivas -->
    <div class="section-title">Historial de Evaluaciones Cognitivas</div>
    @if ($evaluaciones && $evaluaciones->count() > 0)
        <table class="table-institutional">
            <thead>
                <tr>
                    <th style="width: 25%;">Fecha</th>
                    <th style="width: 25%;">Tipo de Evaluación</th>
                    <th style="width: 20%;">Puntaje Obtenido</th>
                    <th style="width: 30%;">Nivel de Riesgo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($evaluaciones as $ev)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($ev->fecha_eval)->format('d/m/Y') }}</td>
                        <td>{{ $ev->tipoEvaluacion ? $ev->tipoEvaluacion->nombre : 'Sin especificar' }}</td>
                        <td><strong>{{ (float) $ev->puntaje_total }}</strong> / {{ (float) $ev->puntaje_maximo }}</td>
                        <td>
                            <span class="badge-status {{ $ev->nivel_riesgo === 'BAJO' ? 'badge-active' : 'badge-inactive' }}">
                                {{ $ev->nivel_riesgo ?? 'MEDIO' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 10px; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 4px; text-align: center; color: #7C7168; font-size: 9px; margin-bottom: 15px;">
            No se registran evaluaciones cognitivas (MoCA, MMSE) asociadas a este expediente.
        </div>
    @endif

    <div class="alert-box">
        <strong>Aviso Legal y Confidencialidad:</strong> Toda la información clínica contenida en este expediente está sujeta a secreto profesional. Prohibida su copia o distribución física y digital por fuera de la dirección médica de Casa Amandita.
    </div>
@endsection
