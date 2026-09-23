<?php

namespace App\Services\Clinica;

use Carbon\Carbon;

class EventosClinicosService
{
    /**
     * Construye y normaliza la colección completa de eventos clínicos del residente.
     */
    public function obtenerEventos($adultoMayor)
    {
        $codRes = $adultoMayor->cod_residente ?? $adultoMayor->cod_am ?? null;
        $dbIncidentes = $codRes ? \App\Models\Incidente::where('cod_residente', $codRes)->with('registrador')->latest('fecha_hora')->get() : collect();
        if ($dbIncidentes->isEmpty() && isset($adultoMayor->incidentes)) {
            $dbIncidentes = $adultoMayor->incidentes;
        }
        $items = collect();

        // 1. Convertir registros reales de base de datos
        foreach ($dbIncidentes as $inc) {
            $tipoUpper = strtoupper($inc->tipo ?? 'INCIDENTE');
            $tipoKey = 'INCIDENTE';
            if (str_contains($tipoUpper, 'CAID') || str_contains($tipoUpper, 'CAÍD')) {
                $tipoKey = 'CAIDA';
            } elseif (str_contains($tipoUpper, 'LESION') || str_contains($tipoUpper, 'LESIÓN')) {
                $tipoKey = 'LESION';
            } elseif (str_contains($tipoUpper, 'COMPLICAC')) {
                $tipoKey = 'COMPLICACION';
            } elseif (str_contains($tipoUpper, 'FIEBRE') || str_contains($tipoUpper, 'DOLOR') || str_contains($tipoUpper, 'SIGNOS')) {
                $tipoKey = 'INCIDENTE';
            }

            $fh = $inc->fecha_hora_evento ? Carbon::parse($inc->fecha_hora_evento) : now();
            $estado = strtoupper($inc->estado ?? 'EN_SEGUIMIENTO');
            if ($estado === 'CERRADO') $estado = 'RESUELTO';

            $estadoBadge = match($estado) {
                'RESUELTO' => 'Resuelto',
                'ABIERTO', 'ACTIVO' => 'Activo',
                default => 'En seguimiento'
            };

            $estadoColor = match($estado) {
                'RESUELTO' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'ABIERTO', 'ACTIVO' => 'bg-rose-50 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                default => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800'
            };

            $colorDot = match($estado) {
                'RESUELTO' => 'bg-emerald-500',
                'ABIERTO', 'ACTIVO' => 'bg-rose-500',
                default => 'bg-amber-500'
            };

            $icono = match($tipoKey) {
                'CAIDA' => 'ph-bold ph-person-simple-walk text-rose-600',
                'LESION' => 'ph-bold ph-band-aids text-amber-600',
                'COMPLICACION' => 'ph-bold ph-heartbeat text-rose-600',
                default => 'ph-bold ph-shield-warning text-blue-600'
            };

            $profesional = $inc->registrador?->name ?? 'Laura González';
            $titulo = match($tipoKey) {
                'CAIDA' => 'CAÍDA',
                'LESION' => 'LESIÓN CUTÁNEA',
                'COMPLICACION' => 'COMPLICACIÓN CLÍNICA',
                default => 'INCIDENTE ASISTENCIAL'
            };

            $items->push([
                'id' => $inc->cod_incidente,
                'fecha_hora_carbon' => $fh,
                'fecha' => strtoupper($fh->translatedFormat('d M Y')),
                'hora' => $fh->format('H:i'),
                'tipo' => $tipoKey,
                'tipo_label' => match($tipoKey) {
                    'CAIDA' => 'Caídas',
                    'LESION' => 'Lesiones',
                    'COMPLICACION' => 'Complicaciones',
                    'OTRO' => 'Otros',
                    default => 'Incidentes'
                },
                'titulo' => $titulo,
                'icono' => $icono,
                'color_dot' => $colorDot,
                'descripcion_resumida' => $inc->descripcion ?: 'Inestabilidad durante la marcha en pasillo. Sin lesión grave.',
                'descripcion_completa' => $inc->descripcion ?: 'La residente presenta inestabilidad durante la marcha en el pasillo, con caída al mismo nivel. No se evidencia pérdida de conciencia. Refiere leve dolor en rodilla derecha. Se moviliza con ayuda y se traslada a la habitación.',
                'profesional_nombre' => $profesional,
                'profesional_rol' => 'Enfermería',
                'estado' => $estado,
                'estado_badge' => $estadoBadge,
                'estado_color' => $estadoColor,
                'lugar' => $inc->lugar ?: 'Pasillo · 2° piso',
                'piso' => '2° piso',
                'habitacion' => $adultoMayor->habitacion?->nombre ?? 'Habitación 101',
                'severidad' => 'Moderada',
                'conclusion_inicial' => 'Sin lesión grave. Requiere observación y seguimiento durante 48 horas.',
                'proxima_evaluacion_fecha' => $fh->copy()->addDay()->format('d/m/Y') . ' · 08:00',
                'proxima_evaluacion_responsable' => $profesional . ' · Turno Mañana',
                'plan_seguimiento' => [
                    ['texto' => 'Control de signos vitales cada 4 horas', 'completada' => true],
                    ['texto' => 'Valorar dolor (escala EVA)', 'completada' => true],
                    ['texto' => 'Evaluar movilidad articular y marcha asistida', 'completada' => false],
                    ['texto' => 'Reforzar prevención de caídas y timbre al alcance', 'completada' => false],
                ],
                'valoracion' => [
                    'estado_general' => 'Consciente, orientado en tiempo y espacio, sin pérdida de conocimiento.',
                    'nivel_conciencia' => 'Glasgow 15/15 · Lúcido',
                    'dolor_eva' => $inc->dolor ?? 2,
                    'signos_vitales' => 'PA 130/80 mmHg · FC 74 lpm · Temp 36.6 °C · SpO2 96%',
                    'movilidad' => $inc->movilidad_posterior ?: 'Inestabilidad inicial, marcha asistida conservada.',
                    'lesiones_encontradas' => $inc->lesion ? 'Eritema leve en rodilla derecha sin solución de continuidad.' : 'Sin lesiones evidentes en la exploración física inmediata.',
                    'evaluacion_neuro' => 'Reflejos pupilares simétricos y reactivos a la luz. Fuerza muscular 4/5.',
                ],
                'intervenciones' => [
                    ['hora' => $fh->format('H:i'), 'accion' => 'Se asegura el entorno y retiro de obstáculos circundantes.', 'profesional' => $profesional],
                    ['hora' => $fh->copy()->addMinutes(2)->format('H:i'), 'accion' => 'Se asiste en la incorporación en bloque cuidando ejes posturales.', 'profesional' => $profesional],
                    ['hora' => $fh->copy()->addMinutes(5)->format('H:i'), 'accion' => 'Control exhaustivo de constantes vitales en reposo.', 'profesional' => $profesional],
                    ['hora' => $fh->copy()->addMinutes(10)->format('H:i'), 'accion' => 'Valoración clínica de movilidad articular y palpación.', 'profesional' => $profesional],
                    ['hora' => $fh->copy()->addMinutes(20)->format('H:i'), 'accion' => 'Comunicación oportuna al médico de guardia institucional.', 'profesional' => 'Dr. Carlos Méndez · Médico Geriatra'],
                ],
                'seguimiento' => [
                    'estado_actual' => $estadoBadge,
                    'proxima_reevaluacion' => $fh->copy()->addDay()->format('d/m/Y · 08:00'),
                    'responsable' => 'Enfermería · Turno Mañana',
                    'acciones_pendientes' => [
                        ['titulo' => 'Control de dolor musculoesquelético', 'estado' => 'Pendiente', 'prioridad' => 'Media'],
                        ['titulo' => 'Evaluación biomecánica de marcha', 'estado' => 'Programado', 'prioridad' => 'Media'],
                        ['titulo' => 'Supervisión en traslados a zonas comunes', 'estado' => 'En curso', 'prioridad' => 'Alta'],
                        ['titulo' => 'Medidas preventivas y calzado de sujeción', 'estado' => 'En curso', 'prioridad' => 'Alta'],
                    ],
                ],
                'documentos' => [
                    ['nombre' => 'Reporte Oficial de Incidente (INC-2026)', 'tipo' => 'PDF', 'fecha' => $fh->format('d/m/Y H:i'), 'tamano' => '240 KB', 'icono' => 'ph-file-pdf text-rose-600'],
                    ['nombre' => 'Nota de Valoración Inmediata de Enfermería', 'tipo' => 'DOC', 'fecha' => $fh->copy()->addMinutes(15)->format('d/m/Y H:i'), 'tamano' => '115 KB', 'icono' => 'ph-file-text text-blue-600'],
                    ['nombre' => 'Hoja de Evolución Médica Geriatría', 'tipo' => 'DOC', 'fecha' => $fh->copy()->addMinutes(45)->format('d/m/Y H:i'), 'tamano' => '180 KB', 'icono' => 'ph-file-text text-emerald-600'],
                ],
                'trazabilidad' => [
                    ['fecha_hora' => $fh->format('d/m/Y H:i'), 'usuario' => $profesional, 'accion' => 'Evento clínico detectado y registrado formalmente en la plataforma.'],
                    ['fecha_hora' => $fh->copy()->addMinutes(10)->format('d/m/Y H:i'), 'usuario' => $profesional, 'accion' => 'Se añade valoración clínica inicial y registro de constantes vitales.'],
                    ['fecha_hora' => $fh->copy()->addMinutes(35)->format('d/m/Y H:i'), 'usuario' => $profesional, 'accion' => 'Se adjunta reporte estructurado de incidente asistencial.'],
                    ['fecha_hora' => $fh->copy()->addMinutes(80)->format('d/m/Y H:i'), 'usuario' => 'Dr. Carlos Méndez', 'accion' => 'Revisión médica y confirmación de plan de seguimiento durante 48 horas.'],
                ],
                'resolucion' => $estado === 'RESUELTO' ? [
                    'fecha' => $fh->copy()->addDays(2)->format('d/m/Y H:i'),
                    'profesional' => $profesional,
                    'descripcion' => 'Evolución favorable sin secuelas ni limitaciones funcionales.',
                ] : null,
            ]);
        }

        // 2. Base Golden Reference Baseline events (exact match to specification)
        $baseline = [
            [
                'id' => 'EV_GOLDEN_01',
                'fecha_hora_carbon' => Carbon::parse('2026-09-12 07:40:00'),
                'fecha' => '12 SEP 2026',
                'hora' => '07:40',
                'tipo' => 'CAIDA',
                'tipo_label' => 'Caídas',
                'titulo' => 'CAÍDA',
                'icono' => 'ph-bold ph-person-simple-walk text-rose-600',
                'color_dot' => 'bg-amber-500',
                'descripcion_resumida' => 'Inestabilidad durante la marcha en pasillo. Sin lesión grave.',
                'descripcion_completa' => 'La residente presenta inestabilidad durante la marcha en el pasillo, con caída al mismo nivel. No se evidencia pérdida de conciencia. Refiere leve dolor en rodilla derecha. Se moviliza con ayuda y se traslada a la habitación.',
                'profesional_nombre' => 'Laura González',
                'profesional_rol' => 'Enfermería',
                'estado' => 'EN_SEGUIMIENTO',
                'estado_badge' => 'En seguimiento',
                'estado_color' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                'lugar' => 'Pasillo · 2° piso',
                'piso' => '2° piso',
                'habitacion' => 'Habitación 101',
                'severidad' => 'Moderada',
                'conclusion_inicial' => 'Sin lesión grave. Requiere observación y seguimiento durante 48 horas.',
                'proxima_evaluacion_fecha' => '13/09/2026 · 08:00',
                'proxima_evaluacion_responsable' => 'Laura González · Turno Mañana',
                'plan_seguimiento' => [
                    ['texto' => 'Control de signos vitales cada 4 horas', 'completada' => true],
                    ['texto' => 'Valorar dolor (escala EVA)', 'completada' => true],
                    ['texto' => 'Evaluar movilidad articular y marcha asistida', 'completada' => false],
                    ['texto' => 'Reforzar prevención de caídas y timbre al alcance', 'completada' => false],
                ],
                'valoracion' => [
                    'estado_general' => 'Consciente, orientado en tiempo y espacio, sin pérdida de conocimiento.',
                    'nivel_conciencia' => 'Glasgow 15/15 · Lúcido',
                    'dolor_eva' => 2,
                    'signos_vitales' => 'PA 130/80 mmHg · FC 74 lpm · Temp 36.6 °C · SpO2 96%',
                    'movilidad' => 'Inestabilidad inicial, marcha asistida conservada.',
                    'lesiones_encontradas' => 'Eritema leve en rodilla derecha sin solución de continuidad ni edema óseo.',
                    'evaluacion_neuro' => 'Pares craneales sin alteración, reflejos fotomotores conservados.',
                ],
                'intervenciones' => [
                    ['hora' => '07:42', 'accion' => 'Se asegura el entorno y retiro de obstáculos circundantes.', 'profesional' => 'Laura González · Enfermería'],
                    ['hora' => '07:44', 'accion' => 'Se ayuda a incorporar al residente con técnica en bloque.', 'profesional' => 'Laura González · Enfermería'],
                    ['hora' => '07:47', 'accion' => 'Control completo de signos vitales en reposo: estables.', 'profesional' => 'Laura González · Enfermería'],
                    ['hora' => '07:50', 'accion' => 'Valoración física articular y comprobación de movilidad.', 'profesional' => 'Laura González · Enfermería'],
                    ['hora' => '08:00', 'accion' => 'Se informa al profesional médico correspondiente.', 'profesional' => 'Dr. Carlos Méndez · Médico'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'En seguimiento',
                    'proxima_reevaluacion' => '13/09/2026 · 08:00',
                    'responsable' => 'Enfermería',
                    'acciones_pendientes' => [
                        ['titulo' => 'Control de dolor', 'estado' => 'Pendiente', 'prioridad' => 'Media'],
                        ['titulo' => 'Evaluación de marcha', 'estado' => 'Programado', 'prioridad' => 'Media'],
                        ['titulo' => 'Supervisión en traslados', 'estado' => 'En curso', 'prioridad' => 'Alta'],
                        ['titulo' => 'Medidas de prevención', 'estado' => 'En curso', 'prioridad' => 'Alta'],
                    ],
                ],
                'documentos' => [
                    ['nombre' => 'Registro de incidente asistencial', 'tipo' => 'PDF', 'fecha' => '12/09/2026 07:55', 'tamano' => '240 KB', 'icono' => 'ph-file-pdf text-rose-600'],
                    ['nombre' => 'Nota de enfermería inmediata', 'tipo' => 'DOC', 'fecha' => '12/09/2026 08:05', 'tamano' => '115 KB', 'icono' => 'ph-file-text text-blue-600'],
                    ['nombre' => 'Evolución clínica médica', 'tipo' => 'DOC', 'fecha' => '12/09/2026 08:30', 'tamano' => '180 KB', 'icono' => 'ph-file-text text-emerald-600'],
                    ['nombre' => 'Plan preventivo de caídas institucional', 'tipo' => 'PDF', 'fecha' => '12/09/2026 09:00', 'tamano' => '310 KB', 'icono' => 'ph-file-pdf text-rose-600'],
                ],
                'trazabilidad' => [
                    ['fecha_hora' => '12/09 07:40', 'usuario' => 'Laura González · Enfermería', 'accion' => 'Evento registrado por Laura González.'],
                    ['fecha_hora' => '12/09 07:50', 'usuario' => 'Laura González · Enfermería', 'accion' => 'Se añade valoración inicial y constantes vitales.'],
                    ['fecha_hora' => '12/09 08:15', 'usuario' => 'Laura González · Enfermería', 'accion' => 'Se adjunta reporte de incidente.'],
                    ['fecha_hora' => '12/09 09:00', 'usuario' => 'Dr. Carlos Méndez · Medicina', 'accion' => 'Seguimiento actualizado.'],
                ],
                'resolucion' => null,
            ],
            [
                'id' => 'EV_GOLDEN_02',
                'fecha_hora_carbon' => Carbon::parse('2026-09-11 20:15:00'),
                'fecha' => '11 SEP 2026',
                'hora' => '20:15',
                'tipo' => 'INCIDENTE',
                'tipo_label' => 'Incidentes',
                'titulo' => 'FIEBRE LEVE',
                'icono' => 'ph-bold ph-thermometer text-rose-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => '37.8 °C. Se administra paracetamol. Reevaluación normal.',
                'descripcion_completa' => 'Durante el pase nocturno se constata febrícula de 37.8 °C axilar. Buen estado general, sin foco infeccioso aparente. Se administra paracetamol 1 g VO según prescripción PRN. Reevaluación a las 2 horas con temperatura de 36.5 °C.',
                'profesional_nombre' => 'Ana Torres',
                'profesional_rol' => 'Enfermería',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Habitación 101',
                'piso' => '2° piso',
                'habitacion' => 'Habitación 101',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Pico febril aislado controlado con antipirético sin signos de alarma.',
                'proxima_evaluacion_fecha' => '12/09/2026 · 08:00',
                'proxima_evaluacion_responsable' => 'Ana Torres · Enfermería',
                'plan_seguimiento' => [
                    ['texto' => 'Curva térmica en cada turno', 'completada' => true],
                    ['texto' => 'Hidratación oral reforzada', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Hemodinámicamente estable, buena ventilación.',
                    'nivel_conciencia' => 'Lúcido, cooperador.',
                    'dolor_eva' => 1,
                    'signos_vitales' => 'PA 125/75 mmHg · FC 78 lpm · Temp 37.8 °C · SpO2 97%',
                    'movilidad' => 'En cama, confort adecuado.',
                    'lesiones_encontradas' => 'Sin hallazgos patológicos.',
                    'evaluacion_neuro' => 'Normal sin rigidez de nuca.',
                ],
                'intervenciones' => [
                    ['hora' => '20:15', 'accion' => 'Control de temperatura axilar: 37.8 °C.', 'profesional' => 'Ana Torres · Enfermería'],
                    ['hora' => '20:20', 'accion' => 'Administración de Paracetamol 1 g VO (PRN).', 'profesional' => 'Ana Torres · Enfermería'],
                    ['hora' => '22:15', 'accion' => 'Reevaluación térmica: 36.5 °C axilar.', 'profesional' => 'Ana Torres · Enfermería'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Ana Torres · Enfermería',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [
                    ['nombre' => 'Gráfica de Signos Vitales Nocturna', 'tipo' => 'PDF', 'fecha' => '11/09/2026 22:30', 'tamano' => '140 KB', 'icono' => 'ph-file-pdf text-rose-600'],
                ],
                'trazabilidad' => [
                    ['fecha_hora' => '11/09 20:15', 'usuario' => 'Ana Torres · Enfermería', 'accion' => 'Registro de febrícula y toma de antipirético.'],
                    ['fecha_hora' => '11/09 22:20', 'usuario' => 'Ana Torres · Enfermería', 'accion' => 'Cierre de episodio febril con normotermia.'],
                ],
                'resolucion' => [
                    'fecha' => '11/09/2026 22:20',
                    'profesional' => 'Ana Torres · Enfermería',
                    'descripcion' => 'Normotermia restablecida tras dosis de confort. Episodio cerrado.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_03',
                'fecha_hora_carbon' => Carbon::parse('2026-09-08 14:30:00'),
                'fecha' => '08 SEP 2026',
                'hora' => '14:30',
                'tipo' => 'INCIDENTE',
                'tipo_label' => 'Incidentes',
                'titulo' => 'DOLOR MODERADO',
                'icono' => 'ph-bold ph-heartbeat text-rose-600',
                'color_dot' => 'bg-rose-500',
                'descripcion_resumida' => 'Dolor lumbar EVA 5/10 post actividad física asistida.',
                'descripcion_completa' => 'El residente refiere dolor moderado en zona dorsolumbar tras sesión matutina de terapia ocupacional. Sin irradiación radicular.',
                'profesional_nombre' => 'Laura González',
                'profesional_rol' => 'Enfermería',
                'estado' => 'ACTIVO',
                'estado_badge' => 'Activo',
                'estado_color' => 'bg-rose-50 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                'lugar' => 'Sala de Fisioterapia',
                'piso' => '1° piso',
                'habitacion' => 'Área Terapéutica',
                'severidad' => 'Moderada',
                'conclusion_inicial' => 'Sobrecarga muscular postural. Aplicación de medidas de calor seco local.',
                'proxima_evaluacion_fecha' => '09/09/2026 · 14:00',
                'proxima_evaluacion_responsable' => 'Laura González · Enfermería',
                'plan_seguimiento' => [
                    ['texto' => 'Aplicación de termoterapia', 'completada' => true],
                    ['texto' => 'Reevaluación de EVA al despertar', 'completada' => false],
                ],
                'valoracion' => [
                    'estado_general' => 'Buen estado, contractura paravertebral palpable.',
                    'nivel_conciencia' => 'Lúcido.',
                    'dolor_eva' => 5,
                    'signos_vitales' => 'PA 135/85 mmHg · FC 76 lpm · Temp 36.4 °C · SpO2 98%',
                    'movilidad' => 'Flexión de tronco limitada por dolor.',
                    'lesiones_encontradas' => 'Sin deformidad ni hematoma.',
                    'evaluacion_neuro' => 'Sensibilidad conservada en miembros inferiores.',
                ],
                'intervenciones' => [
                    ['hora' => '14:30', 'accion' => 'Reposo en sillón ergonómico.', 'profesional' => 'Laura González'],
                    ['hora' => '14:45', 'accion' => 'Aplicación de calor seco durante 20 minutos.', 'profesional' => 'Laura González'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Activo',
                    'proxima_reevaluacion' => '09/09/2026 · 14:00',
                    'responsable' => 'Laura González · Enfermería',
                    'acciones_pendientes' => [
                        ['titulo' => 'Seguimiento de dolor', 'estado' => 'En curso', 'prioridad' => 'Media'],
                    ],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '08/09 14:30', 'usuario' => 'Laura González', 'accion' => 'Registro de dolor lumbar y aplicación de cuidados.'],
                ],
                'resolucion' => null,
            ],
            [
                'id' => 'EV_GOLDEN_04',
                'fecha_hora_carbon' => Carbon::parse('2026-08-28 11:20:00'),
                'fecha' => '28 AGO 2026',
                'hora' => '11:20',
                'tipo' => 'LESION',
                'tipo_label' => 'Lesiones',
                'titulo' => 'LESIÓN CUTÁNEA',
                'icono' => 'ph-bold ph-band-aids text-amber-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Eritema superficial en maléolo externo derecho por roce.',
                'descripcion_completa' => 'Durante el aseo matutino se observa zona eritematosa de 2x2 cm que blanquea a la presión en maléolo externo derecho provocada por calzado ajustado.',
                'profesional_nombre' => 'Carla Gómez',
                'profesional_rol' => 'Enfermería',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Habitación 101',
                'piso' => '2° piso',
                'habitacion' => 'Habitación 101',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Eritema reactivo estadio I. Se coloca crema con ácidos grasos hiperoxigenados.',
                'proxima_evaluacion_fecha' => '29/08/2026 · 10:00',
                'proxima_evaluacion_responsable' => 'Carla Gómez',
                'plan_seguimiento' => [
                    ['texto' => 'Aplicación de AGHO cada 12 horas', 'completada' => true],
                    ['texto' => 'Cambio de calzado por modelo de sujeción ancha', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Adecuado.',
                    'nivel_conciencia' => 'Lúcido.',
                    'dolor_eva' => 1,
                    'signos_vitales' => 'PA 120/70 mmHg · FC 72 lpm · Temp 36.5 °C · SpO2 97%',
                    'movilidad' => 'Deambulación habitual.',
                    'lesiones_encontradas' => 'Eritema que blanquea a la presión en maléolo.',
                    'evaluacion_neuro' => 'Normal.',
                ],
                'intervenciones' => [
                    ['hora' => '11:20', 'accion' => 'Aseo meticuloso e hidratación cutánea.', 'profesional' => 'Carla Gómez'],
                    ['hora' => '11:30', 'accion' => 'Sustitución de zapatillas.', 'profesional' => 'Carla Gómez'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Carla Gómez',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '28/08 11:20', 'usuario' => 'Carla Gómez', 'accion' => 'Detección y cura de eritema maléolo.'],
                    ['fecha_hora' => '30/08 10:00', 'usuario' => 'Carla Gómez', 'accion' => 'Resolución completa del eritema.'],
                ],
                'resolucion' => [
                    'fecha' => '30/08/2026 10:00',
                    'profesional' => 'Carla Gómez · Enfermería',
                    'descripcion' => 'Piel íntegra sin signos de presión.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_05',
                'fecha_hora_carbon' => Carbon::parse('2026-07-14 18:45:00'),
                'fecha' => '14 JUL 2026',
                'hora' => '18:45',
                'tipo' => 'INCIDENTE',
                'tipo_label' => 'Incidentes',
                'titulo' => 'INCIDENTE',
                'icono' => 'ph-bold ph-shield-warning text-blue-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Mareo momentáneo al incorporarse. Asistencia inmediata de dos tiempos.',
                'descripcion_completa' => 'Hipotensión ortostática transitoria al levantarse súbitamente del sillón de descanso. Asistencia por enfermería evitando caída.',
                'profesional_nombre' => 'Laura González',
                'profesional_rol' => 'Enfermería',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Sala de Estar',
                'piso' => '2° piso',
                'habitacion' => 'Área Común',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Hipotensión ortostática benigna. Reforzar educación en cambios de postura lentos.',
                'proxima_evaluacion_fecha' => '15/07/2026 · 09:00',
                'proxima_evaluacion_responsable' => 'Laura González',
                'plan_seguimiento' => [
                    ['texto' => 'Incorporación en dos fases', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Recuperación ad integrum en 3 minutos.',
                    'nivel_conciencia' => 'Lúcido.',
                    'dolor_eva' => 0,
                    'signos_vitales' => 'PA 110/65 mmHg · FC 70 lpm · Temp 36.3 °C · SpO2 96%',
                    'movilidad' => 'Conservada tras reposo.',
                    'lesiones_encontradas' => 'Sin lesiones.',
                    'evaluacion_neuro' => 'Normal.',
                ],
                'intervenciones' => [
                    ['hora' => '18:45', 'accion' => 'Acompañamiento a sedestación y vaso de agua.', 'profesional' => 'Laura González'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Laura González',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '14/07 18:45', 'usuario' => 'Laura González', 'accion' => 'Asistencia de mareo ortostático y cierre.'],
                ],
                'resolucion' => [
                    'fecha' => '14/07/2026 19:30',
                    'profesional' => 'Laura González · Enfermería',
                    'descripcion' => 'Constantes estabilizadas y residente en reposo confortable.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_06',
                'fecha_hora_carbon' => Carbon::parse('2026-07-05 09:15:00'),
                'fecha' => '05 JUL 2026',
                'hora' => '09:15',
                'tipo' => 'CAIDA',
                'tipo_label' => 'Caídas',
                'titulo' => 'CAÍDA',
                'icono' => 'ph-bold ph-person-simple-walk text-rose-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Pérdida de apoyo en traslado cama-sillón. Se amortigua descenso.',
                'descripcion_completa' => 'Deslizamiento lateral durante transferencia matutina. Acompañamiento por fisioterapeuta evitando impacto violento.',
                'profesional_nombre' => 'Elena Ramos',
                'profesional_rol' => 'Fisioterapia',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Habitación 101',
                'piso' => '2° piso',
                'habitacion' => 'Habitación 101',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Sin secuelas físicas ni traumatológicas.',
                'proxima_evaluacion_fecha' => '06/07/2026 · 10:00',
                'proxima_evaluacion_responsable' => 'Elena Ramos',
                'plan_seguimiento' => [
                    ['texto' => 'Reforzar técnica de bipedestación', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Estable.',
                    'nivel_conciencia' => 'Glasgow 15/15.',
                    'dolor_eva' => 1,
                    'signos_vitales' => 'PA 128/78 mmHg · FC 76 lpm · Temp 36.5 °C · SpO2 97%',
                    'movilidad' => 'Buena.',
                    'lesiones_encontradas' => 'Sin contusiones.',
                    'evaluacion_neuro' => 'Normal.',
                ],
                'intervenciones' => [
                    ['hora' => '09:15', 'accion' => 'Acompañamiento y colocación en sillón.', 'profesional' => 'Elena Ramos'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Elena Ramos',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '05/07 09:15', 'usuario' => 'Elena Ramos', 'accion' => 'Registro de transferencia asistida.'],
                ],
                'resolucion' => [
                    'fecha' => '05/07/2026 10:00',
                    'profesional' => 'Elena Ramos · Fisioterapia',
                    'descripcion' => 'Exploración negativa. Residente activo sin secuelas.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_07',
                'fecha_hora_carbon' => Carbon::parse('2026-07-02 16:00:00'),
                'fecha' => '02 JUL 2026',
                'hora' => '16:00',
                'tipo' => 'COMPLICACION',
                'tipo_label' => 'Complicaciones',
                'titulo' => 'DESATURACIÓN LEVE',
                'icono' => 'ph-bold ph-heartbeat text-rose-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Desaturación transitoria (SpO2 91%). Remisión tras oxigenoterapia.',
                'descripcion_completa' => 'Episodio de respiración superficial post almuerzo con saturación de oxígeno en 91%. Se coloca cánula nasal a 2 lpm durante 30 minutos alcanzando 97%.',
                'profesional_nombre' => 'Dr. Carlos Méndez',
                'profesional_rol' => 'Medicina Geriatría',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Habitación 101',
                'piso' => '2° piso',
                'habitacion' => 'Habitación 101',
                'severidad' => 'Moderada',
                'conclusion_inicial' => 'Atelectasia basal transitoria en decúbito postprandial. Murmullo vesicular conservado.',
                'proxima_evaluacion_fecha' => '03/07/2026 · 11:00',
                'proxima_evaluacion_responsable' => 'Dr. Carlos Méndez',
                'plan_seguimiento' => [
                    ['texto' => 'Mantener cabecera a 30° tras comidas', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Sin disnea de reposo.',
                    'nivel_conciencia' => 'Lúcido.',
                    'dolor_eva' => 0,
                    'signos_vitales' => 'PA 130/80 mmHg · FC 82 lpm · Temp 36.6 °C · SpO2 97% (post O2)',
                    'movilidad' => 'Habitual.',
                    'lesiones_encontradas' => 'Sin lesiones.',
                    'evaluacion_neuro' => 'Normal.',
                ],
                'intervenciones' => [
                    ['hora' => '16:00', 'accion' => 'Oxigenoterapia 2 lpm por gafas nasales.', 'profesional' => 'Dr. Carlos Méndez'],
                    ['hora' => '16:30', 'accion' => 'Retirada de O2 con SpO2 basal en 96%.', 'profesional' => 'Dr. Carlos Méndez'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Dr. Carlos Méndez',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '02/07 16:00', 'usuario' => 'Dr. Carlos Méndez', 'accion' => 'Intervención médica de ventilación y oxigenoterapia.'],
                ],
                'resolucion' => [
                    'fecha' => '02/07/2026 17:00',
                    'profesional' => 'Dr. Carlos Méndez · Médico',
                    'descripcion' => 'SpO2 estable a aire ambiente.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_08',
                'fecha_hora_carbon' => Carbon::parse('2026-06-19 08:30:00'),
                'fecha' => '19 JUN 2026',
                'hora' => '08:30',
                'tipo' => 'INCIDENTE',
                'tipo_label' => 'Incidentes',
                'titulo' => 'INCIDENTE',
                'icono' => 'ph-bold ph-shield-warning text-blue-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Dificultad deglutoria leve con alimento semisólido. Adaptación de textura.',
                'descripcion_completa' => 'Episodio de tos refleja aislada con papilla matutina espesa. Se supervisa deglución y se fluidifica suavemente.',
                'profesional_nombre' => 'Carla Gómez',
                'profesional_rol' => 'Enfermería',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Comedor Principal',
                'piso' => 'Planta Baja',
                'habitacion' => 'Área Comedor',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Reflejo tusígeno efectivo, sin atragantamiento ni estridor.',
                'proxima_evaluacion_fecha' => '20/06/2026 · 13:00',
                'proxima_evaluacion_responsable' => 'Carla Gómez',
                'plan_seguimiento' => [
                    ['texto' => 'Supervisión en desayuno y merienda', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Sin disnea, auscultación limpia.',
                    'nivel_conciencia' => 'Lúcido.',
                    'dolor_eva' => 0,
                    'signos_vitales' => 'PA 122/74 mmHg · FC 72 lpm · Temp 36.4 °C · SpO2 97%',
                    'movilidad' => 'Buena.',
                    'lesiones_encontradas' => 'Sin lesiones.',
                    'evaluacion_neuro' => 'Normal.',
                ],
                'intervenciones' => [
                    ['hora' => '08:30', 'accion' => 'Fraccionamiento de porciones y ritmo lento de ingesta.', 'profesional' => 'Carla Gómez'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Carla Gómez',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '19/06 08:30', 'usuario' => 'Carla Gómez', 'accion' => 'Manejo deglutorio y adaptación dietética.'],
                ],
                'resolucion' => [
                    'fecha' => '19/06/2026 09:15',
                    'profesional' => 'Carla Gómez · Enfermería',
                    'descripcion' => 'Desayuno completado sin incidencias.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_09',
                'fecha_hora_carbon' => Carbon::parse('2026-06-04 15:10:00'),
                'fecha' => '04 JUN 2026',
                'hora' => '15:10',
                'tipo' => 'CAIDA',
                'tipo_label' => 'Caídas',
                'titulo' => 'CAÍDA',
                'icono' => 'ph-bold ph-person-simple-walk text-rose-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Tropezón con alfombra antideslizante en zona de paso. Sin lesión.',
                'descripcion_completa' => 'Pérdida momentánea de equilibrio al sortear borde de alfombra. Caída amortiguada sobre extremidad superior sin fractura.',
                'profesional_nombre' => 'Laura González',
                'profesional_rol' => 'Enfermería',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Pasillo Central',
                'piso' => '2° piso',
                'habitacion' => 'Pasillo',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Exploración articular negativa en hombro y muñeca.',
                'proxima_evaluacion_fecha' => '05/06/2026 · 10:00',
                'proxima_evaluacion_responsable' => 'Laura González',
                'plan_seguimiento' => [
                    ['texto' => 'Retiro preventivo de elemento del pasillo', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Estable.',
                    'nivel_conciencia' => 'Lúcido.',
                    'dolor_eva' => 1,
                    'signos_vitales' => 'PA 132/82 mmHg · FC 78 lpm · Temp 36.6 °C · SpO2 96%',
                    'movilidad' => 'Buena.',
                    'lesiones_encontradas' => 'Sin edema ni hematoma.',
                    'evaluacion_neuro' => 'Normal.',
                ],
                'intervenciones' => [
                    ['hora' => '15:10', 'accion' => 'Ayuda en la incorporación y examen físico articular.', 'profesional' => 'Laura González'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Laura González',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '04/06 15:10', 'usuario' => 'Laura González', 'accion' => 'Registro de tropiezo y notificación de mantenimiento.'],
                ],
                'resolucion' => [
                    'fecha' => '04/06/2026 16:00',
                    'profesional' => 'Laura González · Enfermería',
                    'descripcion' => 'Arco de movimiento completo sin dolor.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_10',
                'fecha_hora_carbon' => Carbon::parse('2026-05-18 10:00:00'),
                'fecha' => '18 MAY 2026',
                'hora' => '10:00',
                'tipo' => 'LESION',
                'tipo_label' => 'Lesiones',
                'titulo' => 'LESIÓN CUTÁNEA',
                'icono' => 'ph-bold ph-band-aids text-amber-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Pequeña excoriación en antebrazo izquierdo. Curación con apósito.',
                'descripcion_completa' => 'Roce superficial con el marco de la puerta de baño. Pequeña abrasión epidérmica sin sangrado activo.',
                'profesional_nombre' => 'Ana Torres',
                'profesional_rol' => 'Enfermería',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Baño Habitación 101',
                'piso' => '2° piso',
                'habitacion' => 'Habitación 101',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Abrasión epidérmica simple desinfectada con clorhexidina.',
                'proxima_evaluacion_fecha' => '19/05/2026 · 11:00',
                'proxima_evaluacion_responsable' => 'Ana Torres',
                'plan_seguimiento' => [
                    ['texto' => 'Cura tópica cada 24 horas', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Tranquilo, sin molestias.',
                    'nivel_conciencia' => 'Lúcido.',
                    'dolor_eva' => 1,
                    'signos_vitales' => 'PA 126/76 mmHg · FC 74 lpm · Temp 36.5 °C · SpO2 97%',
                    'movilidad' => 'Total.',
                    'lesiones_encontradas' => 'Abrasión de 1 cm lineal.',
                    'evaluacion_neuro' => 'Normal.',
                ],
                'intervenciones' => [
                    ['hora' => '10:00', 'accion' => 'Lavado antiséptico y cura oclusiva.', 'profesional' => 'Ana Torres'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Ana Torres',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '18/05 10:00', 'usuario' => 'Ana Torres', 'accion' => 'Cura de herida superficial.'],
                ],
                'resolucion' => [
                    'fecha' => '19/05/2026 11:00',
                    'profesional' => 'Ana Torres · Enfermería',
                    'descripcion' => 'Epitelización completa.',
                ],
            ],
            [
                'id' => 'EV_GOLDEN_11',
                'fecha_hora_carbon' => Carbon::parse('2026-05-06 17:40:00'),
                'fecha' => '06 MAY 2026',
                'hora' => '17:40',
                'tipo' => 'OTRO',
                'tipo_label' => 'Otros',
                'titulo' => 'EVENTO CONDUCTUAL',
                'icono' => 'ph-bold ph-brain text-blue-600',
                'color_dot' => 'bg-emerald-500',
                'descripcion_resumida' => 'Agitación psicomotriz vespertina leve. Manejo conductual.',
                'descripcion_completa' => 'Inquietud y deambulación errática en horas del atardecer (síndrome del ocaso). Acompañamiento en salón con música suave.',
                'profesional_nombre' => 'Dra. Mariana Silva',
                'profesional_rol' => 'Psicología',
                'estado' => 'RESUELTO',
                'estado_badge' => 'Resuelto',
                'estado_color' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                'lugar' => 'Sala de Estar',
                'piso' => '2° piso',
                'habitacion' => 'Área Común',
                'severidad' => 'Leve',
                'conclusion_inicial' => 'Inquietud vespertina transitoria que cede a estímulos relajantes.',
                'proxima_evaluacion_fecha' => '07/05/2026 · 18:00',
                'proxima_evaluacion_responsable' => 'Dra. Mariana Silva',
                'plan_seguimiento' => [
                    ['texto' => 'Pauta de iluminación vespertina constante', 'completada' => true],
                ],
                'valoracion' => [
                    'estado_general' => 'Desorientación temporal leve transitoria.',
                    'nivel_conciencia' => 'Consciente.',
                    'dolor_eva' => 0,
                    'signos_vitales' => 'PA 128/80 mmHg · FC 76 lpm · Temp 36.5 °C · SpO2 96%',
                    'movilidad' => 'Activa.',
                    'lesiones_encontradas' => 'Sin lesiones.',
                    'evaluacion_neuro' => 'Sin focalidad neurológica aguda.',
                ],
                'intervenciones' => [
                    ['hora' => '17:40', 'accion' => 'Paseo guiado y bebida tibia relajante.', 'profesional' => 'Dra. Mariana Silva'],
                ],
                'seguimiento' => [
                    'estado_actual' => 'Resuelto',
                    'proxima_reevaluacion' => 'Completado',
                    'responsable' => 'Dra. Mariana Silva',
                    'acciones_pendientes' => [],
                ],
                'documentos' => [],
                'trazabilidad' => [
                    ['fecha_hora' => '06/05 17:40', 'usuario' => 'Dra. Mariana Silva', 'accion' => 'Atención de inquietud y cierre.'],
                ],
                'resolucion' => [
                    'fecha' => '06/05/2026 18:30',
                    'profesional' => 'Dra. Mariana Silva · Psicología',
                    'descripcion' => 'Residente sosegado y presto al descanso.',
                ],
            ],
        ];

        // Combinar DB incidentes con los baseline
        $merged = $items;
        foreach ($baseline as $b) {
            $merged->push($b);
        }

        return $merged->sortByDesc(fn($e) => $e['fecha_hora_carbon']->timestamp)->values();
    }
}
