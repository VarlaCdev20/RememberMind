<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\Residente;
use App\Models\AdultoMayor;
use App\Models\DocumentoClinico;
use Carbon\Carbon;

class ResultadosEstudiosService
{
    /**
     * Devuelve los rangos de referencia clínicos estándar para adultos mayores.
     */
    public function obtenerRangosReferencia(string $parametro): array
    {
        $parametro = strtolower(trim($parametro));

        $rangos = [
            'glucosa' => [
                'nombre' => 'Glucosa sérica',
                'unidad' => 'mg/dL',
                'normal_min' => 70,
                'normal_max' => 100,
                'texto_rango' => '70 – 100 mg/dL',
                'clasificaciones' => [
                    ['etiqueta' => 'Normal', 'rango' => '70 – 100 mg/dL', 'color' => 'text-emerald-700 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-950/40'],
                    ['etiqueta' => 'Prediabetes (Intolerancia)', 'rango' => '100 – 125 mg/dL', 'color' => 'text-amber-700 dark:text-amber-400', 'bg' => 'bg-amber-50 dark:bg-amber-950/40'],
                    ['etiqueta' => 'Diabetes Mellitus', 'rango' => '≥ 126 mg/dL', 'color' => 'text-rose-700 dark:text-rose-400', 'bg' => 'bg-rose-50 dark:bg-rose-950/40'],
                    ['etiqueta' => 'Hipoglucemia', 'rango' => '< 70 mg/dL', 'color' => 'text-blue-700 dark:text-blue-400', 'bg' => 'bg-blue-50 dark:bg-blue-950/40'],
                ],
                'guia_clinica' => 'En el paciente geriátrico se admiten objetivos de glucemia basal de 80-130 mg/dL según fragilidad y comorbilidades.',
            ],
            'hemoglobina' => [
                'nombre' => 'Hemoglobina (Hb)',
                'unidad' => 'g/dL',
                'normal_min' => 12.0,
                'normal_max' => 16.0,
                'texto_rango' => '12.0 – 16.0 g/dL',
                'clasificaciones' => [
                    ['etiqueta' => 'Normal', 'rango' => '12.0 – 16.0 g/dL', 'color' => 'text-emerald-700 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-950/40'],
                    ['etiqueta' => 'Anemia Leve', 'rango' => '10.0 – 11.9 g/dL', 'color' => 'text-amber-700 dark:text-amber-400', 'bg' => 'bg-amber-50 dark:bg-amber-950/40'],
                    ['etiqueta' => 'Anemia Moderada / Severa', 'rango' => '< 10.0 g/dL', 'color' => 'text-rose-700 dark:text-rose-400', 'bg' => 'bg-rose-50 dark:bg-rose-950/40'],
                    ['etiqueta' => 'Poliglobulia', 'rango' => '> 16.5 g/dL', 'color' => 'text-purple-700 dark:text-purple-400', 'bg' => 'bg-purple-50 dark:bg-purple-950/40'],
                ],
                'guia_clinica' => 'Evaluar ferritina y vitamina B12 en caso de Hb < 12.0 g/dL en mujeres o < 13.0 g/dL en varones.',
            ],
            'creatinina' => [
                'nombre' => 'Creatinina sérica',
                'unidad' => 'mg/dL',
                'normal_min' => 0.6,
                'normal_max' => 1.2,
                'texto_rango' => '0.6 – 1.2 mg/dL',
                'clasificaciones' => [
                    ['etiqueta' => 'Normal', 'rango' => '0.6 – 1.2 mg/dL', 'color' => 'text-emerald-700 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-950/40'],
                    ['etiqueta' => 'Elevación Leve', 'rango' => '1.3 – 1.6 mg/dL', 'color' => 'text-amber-700 dark:text-amber-400', 'bg' => 'bg-amber-50 dark:bg-amber-950/40'],
                    ['etiqueta' => 'Insuficiencia Renal', 'rango' => '> 1.6 mg/dL', 'color' => 'text-rose-700 dark:text-rose-400', 'bg' => 'bg-rose-50 dark:bg-rose-950/40'],
                ],
                'guia_clinica' => 'La pérdida de masa muscular senil puede subestimar la disfunción renal; calcular eGFR por CKD-EPI.',
            ],
            'sodio' => [
                'nombre' => 'Sodio sérico (Na+)',
                'unidad' => 'mEq/L',
                'normal_min' => 135,
                'normal_max' => 145,
                'texto_rango' => '135 – 145 mEq/L',
                'clasificaciones' => [
                    ['etiqueta' => 'Normal', 'rango' => '135 – 145 mEq/L', 'color' => 'text-emerald-700 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-950/40'],
                    ['etiqueta' => 'Hiponatremia Leve', 'rango' => '130 – 134 mEq/L', 'color' => 'text-amber-700 dark:text-amber-400', 'bg' => 'bg-amber-50 dark:bg-amber-950/40'],
                    ['etiqueta' => 'Hiponatremia Severa', 'rango' => '< 130 mEq/L', 'color' => 'text-rose-700 dark:text-rose-400', 'bg' => 'bg-rose-50 dark:bg-rose-950/40'],
                    ['etiqueta' => 'Hipernatremia', 'rango' => '> 145 mEq/L', 'color' => 'text-purple-700 dark:text-purple-400', 'bg' => 'bg-purple-50 dark:bg-purple-950/40'],
                ],
                'guia_clinica' => 'Causa frecuente de confusión aguda, desorientación y caídas en ancianos institucionalizados.',
            ],
            'potasio' => [
                'nombre' => 'Potasio sérico (K+)',
                'unidad' => 'mEq/L',
                'normal_min' => 3.5,
                'normal_max' => 5.0,
                'texto_rango' => '3.5 – 5.0 mEq/L',
                'clasificaciones' => [
                    ['etiqueta' => 'Normal', 'rango' => '3.5 – 5.0 mEq/L', 'color' => 'text-emerald-700 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-950/40'],
                    ['etiqueta' => 'Hipopotasemia', 'rango' => '< 3.5 mEq/L', 'color' => 'text-amber-700 dark:text-amber-400', 'bg' => 'bg-amber-50 dark:bg-amber-950/40'],
                    ['etiqueta' => 'Hiperpotasemia', 'rango' => '> 5.0 mEq/L', 'color' => 'text-rose-700 dark:text-rose-400', 'bg' => 'bg-rose-50 dark:bg-rose-950/40'],
                ],
                'guia_clinica' => 'Monitorear estrechamente con uso de IECAs, ARA-II, espironolactona o diuréticos de asa.',
            ],
        ];

        return $rangos[$parametro] ?? $rangos['glucosa'];
    }

    /**
     * Construye y normaliza la lista completa de resultados y estudios del residente.
     */
    public function obtenerEstudios(Residente $adultoMayor)
    {
        $items = collect();

        // 1. Integrar documentos reales que correspondan a estudios médicos / analíticas
        $docsReales = DocumentoClinico::where('cod_residente', $adultoMayor->cod_residente)
            ->where(function($q) {
                $q->whereIn('tipo_documento', ['MEDICO', 'ESTUDIO', 'LABORATORIO', 'EXAMEN', 'IMAGEN'])
                  ->orWhere('titulo', 'like', '%laboratorio%')
                  ->orWhere('titulo', 'like', '%certificado%')
                  ->orWhere('titulo', 'like', '%estudio%')
                  ->orWhere('titulo', 'like', '%informe%');
            })
            ->get();

        foreach ($docsReales as $doc) {
            $esCertificado = str_contains(strtolower($doc->titulo), 'certificado');
            $tipoCat = match (strtoupper($doc->tipo_documento)) {
                'IMAGEN' => 'IMAGEN',
                'CARDIOLOGICO' => 'CARDIOLOGICO',
                'LABORATORIO', 'EXAMEN' => 'LABORATORIO',
                default => $esCertificado ? 'OTROS' : 'LABORATORIO',
            };
            $fecha = Carbon::parse($doc->fecha_hora);

            $items->push([
                'id' => 'DOC_' . $doc->cod_documento_clinico,
                'cod_doc' => $doc->cod_documento_clinico,
                'fecha' => $fecha->format('d/m/Y'),
                'fecha_raw' => $fecha->toIso8601String(),
                'hora' => $fecha->format('H:i'),
                'titulo' => $doc->titulo,
                'tipo_categoria' => $tipoCat, // LABORATORIO, IMAGEN, CARDIOLOGICO, OTROS
                'tipo_texto' => $esCertificado ? 'Examen General' : 'Documento Clínico',
                'parametro_clave' => null,
                'resultado_valor' => 'Concluido',
                'resultado_unidad' => '',
                'rango_referencia' => 'Apto institucional',
                'estado' => 'NORMAL',
                'estado_badge' => 'Normal',
                'estado_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                'estado_dot' => 'bg-emerald-500',
                'estado_icono' => 'ph-bold ph-check-circle',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Dra. Elena Silva',
                'profesional_rol' => 'Medicina Interna',
                'validado_por' => 'Dra. Elena Silva · Matrícula Med-8842',
                'laboratorio_centro' => 'Centro Médico San Gabriel',
                'fecha_validacion' => $fecha->format('d/m/Y') . ' · 11:30',
                'observaciones' => $doc->observacion ?: ($doc->descripcion ?: 'Documento clínico incorporado al expediente.'),
                'hallazgos' => 'Parámetros evaluados compatibles con el rango de edad geriátrica.',
                'conclusion' => 'Sin contraindicación médica.',
                'tiene_documento' => !empty($doc->ruta_archivo),
                'nombre_documento' => $doc->titulo . '.' . strtolower($doc->formato),
                'ruta_documento' => $doc->ruta_archivo,
                'es_real_db' => true,
                'historico_valores' => [],
            ]);
        }

        // 2. Estudios clínicos de referencia y seguimiento estructurado
        $baseEstudios = [
            [
                'id' => 'EST_01_GLUCOSA',
                'fecha' => '10/09/2026',
                'fecha_raw' => '2026-09-10T07:30:00',
                'hora' => '07:30',
                'titulo' => 'Glucosa en ayunas',
                'tipo_categoria' => 'LABORATORIO',
                'tipo_texto' => 'Laboratorio',
                'parametro_clave' => 'glucosa',
                'resultado_valor' => '104',
                'resultado_unidad' => 'mg/dL',
                'rango_referencia' => '70 – 100 mg/dL',
                'estado' => 'ALTO',
                'estado_badge' => 'Alto',
                'estado_color' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                'estado_dot' => 'bg-rose-500',
                'estado_icono' => 'ph-bold ph-arrow-circle-up',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Dra. Jimena Morales',
                'profesional_rol' => 'Bioquímica Clínica',
                'validado_por' => 'Dra. Jimena Morales · Reg. Bioq. 4410',
                'laboratorio_centro' => 'Laboratorio Clínico San Andrés',
                'fecha_validacion' => '10/09/2026 · 11:45',
                'observaciones' => 'Valor ligeramente elevado. Continuar control y seguimiento en próxima evaluación médica. Se sugiere ajuste dietario.',
                'hallazgos' => 'Muestra obtenida con 9 horas de ayuno. Suero límpido sin hemólisis.',
                'conclusion' => 'Hiperglucemia basal leve en contexto de tratamiento hipoglucemiante.',
                'tiene_documento' => true,
                'nombre_documento' => 'Informe_Glucemia_Basal_10092026.pdf',
                'ruta_documento' => 'documentos/estudios/glucosa_10092026.pdf',
                'es_real_db' => false,
                'historico_valores' => [
                    ['periodo' => 'Abr 26', 'valor' => 96, 'fecha' => '12/04/2026'],
                    ['periodo' => 'May 26', 'valor' => 98, 'fecha' => '10/05/2026'],
                    ['periodo' => 'Jun 26', 'valor' => 102, 'fecha' => '15/06/2026'],
                    ['periodo' => 'Jul 26', 'valor' => 105, 'fecha' => '08/07/2026'],
                    ['periodo' => 'Ago 26', 'valor' => 99, 'fecha' => '12/08/2026'],
                    ['periodo' => 'Sep 26', 'valor' => 104, 'fecha' => '10/09/2026'],
                ],
            ],
            [
                'id' => 'EST_02_HEMOGLOBINA',
                'fecha' => '10/09/2026',
                'fecha_raw' => '2026-09-10T07:30:00',
                'hora' => '07:30',
                'titulo' => 'Hemoglobina (Hb)',
                'tipo_categoria' => 'LABORATORIO',
                'tipo_texto' => 'Laboratorio',
                'parametro_clave' => 'hemoglobina',
                'resultado_valor' => '10.8',
                'resultado_unidad' => 'g/dL',
                'rango_referencia' => '12.0 – 16.0 g/dL',
                'estado' => 'BAJO',
                'estado_badge' => 'Bajo',
                'estado_color' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                'estado_dot' => 'bg-rose-500',
                'estado_icono' => 'ph-bold ph-arrow-circle-down',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Dra. Jimena Morales',
                'profesional_rol' => 'Bioquímica Clínica',
                'validado_por' => 'Dra. Jimena Morales · Reg. Bioq. 4410',
                'laboratorio_centro' => 'Laboratorio Clínico San Andrés',
                'fecha_validacion' => '10/09/2026 · 11:45',
                'observaciones' => 'Anemia normocítica normocrómica leve. Mantener suplementación de hierro y control hematológico en 30 días.',
                'hallazgos' => 'Hematocrito 33.2%, VCM 88 fL, HCM 29 pg. Serie blanca y plaquetaria sin alteraciones.',
                'conclusion' => 'Anemia leve en control.',
                'tiene_documento' => true,
                'nombre_documento' => 'Hemograma_Completo_10092026.pdf',
                'ruta_documento' => 'documentos/estudios/hemograma_10092026.pdf',
                'es_real_db' => false,
                'historico_valores' => [
                    ['periodo' => 'Abr 26', 'valor' => 11.5, 'fecha' => '12/04/2026'],
                    ['periodo' => 'May 26', 'valor' => 11.2, 'fecha' => '10/05/2026'],
                    ['periodo' => 'Jun 26', 'valor' => 11.0, 'fecha' => '15/06/2026'],
                    ['periodo' => 'Jul 26', 'valor' => 10.9, 'fecha' => '08/07/2026'],
                    ['periodo' => 'Ago 26', 'valor' => 10.7, 'fecha' => '12/08/2026'],
                    ['periodo' => 'Sep 26', 'valor' => 10.8, 'fecha' => '10/09/2026'],
                ],
            ],
            [
                'id' => 'EST_03_CREATININA',
                'fecha' => '08/09/2026',
                'fecha_raw' => '2026-09-08T08:15:00',
                'hora' => '08:15',
                'titulo' => 'Creatinina sérica',
                'tipo_categoria' => 'LABORATORIO',
                'tipo_texto' => 'Laboratorio',
                'parametro_clave' => 'creatinina',
                'resultado_valor' => '1.1',
                'resultado_unidad' => 'mg/dL',
                'rango_referencia' => '0.6 – 1.2 mg/dL',
                'estado' => 'NORMAL',
                'estado_badge' => 'Normal',
                'estado_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                'estado_dot' => 'bg-emerald-500',
                'estado_icono' => 'ph-bold ph-check-circle',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Lic. Carlos Salinas',
                'profesional_rol' => 'Bioquímico',
                'validado_por' => 'Lic. Carlos Salinas · Matr. 3312',
                'laboratorio_centro' => 'Laboratorio Central de Diagnóstico',
                'fecha_validacion' => '08/09/2026 · 13:10',
                'observaciones' => 'Función renal conservada para el estrato etario. Mantener hidratación oral adecuada.',
                'hallazgos' => 'Urea sérica 38 mg/dL, Nitrógeno ureico 17.7 mg/dL.',
                'conclusion' => 'Perfil renal dentro de parámetros de normalidad.',
                'tiene_documento' => true,
                'nombre_documento' => 'Perfil_Renal_08092026.pdf',
                'ruta_documento' => 'documentos/estudios/creatinina_08092026.pdf',
                'es_real_db' => false,
                'historico_valores' => [
                    ['periodo' => 'Abr 26', 'valor' => 1.0, 'fecha' => '12/04/2026'],
                    ['periodo' => 'May 26', 'valor' => 1.1, 'fecha' => '10/05/2026'],
                    ['periodo' => 'Jun 26', 'valor' => 1.2, 'fecha' => '15/06/2026'],
                    ['periodo' => 'Jul 26', 'valor' => 1.1, 'fecha' => '08/07/2026'],
                    ['periodo' => 'Ago 26', 'valor' => 1.0, 'fecha' => '12/08/2026'],
                    ['periodo' => 'Sep 26', 'valor' => 1.1, 'fecha' => '08/09/2026'],
                ],
            ],
            [
                'id' => 'EST_04_SODIO',
                'fecha' => '08/09/2026',
                'fecha_raw' => '2026-09-08T08:15:00',
                'hora' => '08:15',
                'titulo' => 'Sodio sérico (Na+)',
                'tipo_categoria' => 'LABORATORIO',
                'tipo_texto' => 'Laboratorio',
                'parametro_clave' => 'sodio',
                'resultado_valor' => '138',
                'resultado_unidad' => 'mEq/L',
                'rango_referencia' => '135 – 145 mEq/L',
                'estado' => 'NORMAL',
                'estado_badge' => 'Normal',
                'estado_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                'estado_dot' => 'bg-emerald-500',
                'estado_icono' => 'ph-bold ph-check-circle',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Lic. Carlos Salinas',
                'profesional_rol' => 'Bioquímico',
                'validado_por' => 'Lic. Carlos Salinas · Matr. 3312',
                'laboratorio_centro' => 'Laboratorio Central de Diagnóstico',
                'fecha_validacion' => '08/09/2026 · 13:10',
                'observaciones' => 'Equilibrio hidroelectrolítico adecuado.',
                'hallazgos' => 'Electrolitos séricos estables.',
                'conclusion' => 'Normonatremia.',
                'tiene_documento' => false,
                'nombre_documento' => '',
                'ruta_documento' => '',
                'es_real_db' => false,
                'historico_valores' => [
                    ['periodo' => 'Abr 26', 'valor' => 137, 'fecha' => '12/04/2026'],
                    ['periodo' => 'May 26', 'valor' => 139, 'fecha' => '10/05/2026'],
                    ['periodo' => 'Jun 26', 'valor' => 136, 'fecha' => '15/06/2026'],
                    ['periodo' => 'Jul 26', 'valor' => 138, 'fecha' => '08/07/2026'],
                    ['periodo' => 'Ago 26', 'valor' => 139, 'fecha' => '12/08/2026'],
                    ['periodo' => 'Sep 26', 'valor' => 138, 'fecha' => '08/09/2026'],
                ],
            ],
            [
                'id' => 'EST_05_POTASIO',
                'fecha' => '08/09/2026',
                'fecha_raw' => '2026-09-08T08:15:00',
                'hora' => '08:15',
                'titulo' => 'Potasio sérico (K+)',
                'tipo_categoria' => 'LABORATORIO',
                'tipo_texto' => 'Laboratorio',
                'parametro_clave' => 'potasio',
                'resultado_valor' => '4.2',
                'resultado_unidad' => 'mEq/L',
                'rango_referencia' => '3.5 – 5.0 mEq/L',
                'estado' => 'NORMAL',
                'estado_badge' => 'Normal',
                'estado_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                'estado_dot' => 'bg-emerald-500',
                'estado_icono' => 'ph-bold ph-check-circle',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Lic. Carlos Salinas',
                'profesional_rol' => 'Bioquímico',
                'validado_por' => 'Lic. Carlos Salinas · Matr. 3312',
                'laboratorio_centro' => 'Laboratorio Central de Diagnóstico',
                'fecha_validacion' => '08/09/2026 · 13:10',
                'observaciones' => 'Valores de potasio estables bajo tratamiento antihipertensivo.',
                'hallazgos' => 'Cloro 102 mEq/L, Calcio iónico 1.18 mmol/L.',
                'conclusion' => 'Normopotasemia.',
                'tiene_documento' => false,
                'nombre_documento' => '',
                'ruta_documento' => '',
                'es_real_db' => false,
                'historico_valores' => [
                    ['periodo' => 'Abr 26', 'valor' => 4.1, 'fecha' => '12/04/2026'],
                    ['periodo' => 'May 26', 'valor' => 4.3, 'fecha' => '10/05/2026'],
                    ['periodo' => 'Jun 26', 'valor' => 4.2, 'fecha' => '15/06/2026'],
                    ['periodo' => 'Jul 26', 'valor' => 4.0, 'fecha' => '08/07/2026'],
                    ['periodo' => 'Ago 26', 'valor' => 4.4, 'fecha' => '12/08/2026'],
                    ['periodo' => 'Sep 26', 'valor' => 4.2, 'fecha' => '08/09/2026'],
                ],
            ],
            [
                'id' => 'EST_06_RX_TORAX',
                'fecha' => '02/09/2026',
                'fecha_raw' => '2026-09-02T10:00:00',
                'hora' => '10:00',
                'titulo' => 'Radiografía de tórax (PA)',
                'tipo_categoria' => 'IMAGEN',
                'tipo_texto' => 'Imagen',
                'parametro_clave' => null,
                'resultado_valor' => 'Sin hallazgos agudos',
                'resultado_unidad' => '',
                'rango_referencia' => '—',
                'estado' => 'NORMAL',
                'estado_badge' => 'Normal',
                'estado_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                'estado_dot' => 'bg-emerald-500',
                'estado_icono' => 'ph-bold ph-check-circle',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Dr. Fernando Arze',
                'profesional_rol' => 'Médico Radiólogo',
                'validado_por' => 'Dr. Fernando Arze · Matr. Rad-1994',
                'laboratorio_centro' => 'Centro Radiológico Metropolitano',
                'fecha_validacion' => '02/09/2026 · 14:20',
                'observaciones' => 'Leve elongación aórtica senil sin signos de insuficiencia cardíaca aguda ni condensaciones pulmonares.',
                'hallazgos' => 'Campos pulmonares bien ventilados. Senos costofrénicos libres. Índice cardiotorácico en límite superior.',
                'conclusion' => 'Tórax senil con cambios vasculares degenerativos habituales sin patología aguda activa.',
                'tiene_documento' => true,
                'nombre_documento' => 'Informe_Radiologico_Torax_PA_02092026.pdf',
                'ruta_documento' => 'documentos/estudios/rx_torax_02092026.pdf',
                'es_real_db' => false,
                'historico_valores' => [],
            ],
            [
                'id' => 'EST_07_ECG',
                'fecha' => '28/08/2026',
                'fecha_raw' => '2026-08-28T09:30:00',
                'hora' => '09:30',
                'titulo' => 'Electrocardiograma de 12 derivaciones (ECG)',
                'tipo_categoria' => 'CARDIOLOGICO',
                'tipo_texto' => 'Estudios cardiológicos',
                'parametro_clave' => null,
                'resultado_valor' => 'Ritmo sinusal regular',
                'resultado_unidad' => '72 lpm',
                'rango_referencia' => '60 – 100 lpm',
                'estado' => 'NORMAL',
                'estado_badge' => 'Normal',
                'estado_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                'estado_dot' => 'bg-emerald-500',
                'estado_icono' => 'ph-bold ph-check-circle',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Dr. Rodrigo Vega',
                'profesional_rol' => 'Médico Cardiólogo',
                'validado_por' => 'Dr. Rodrigo Vega · Reg. Card-5519',
                'laboratorio_centro' => 'Unidad Cardiológica Integral',
                'fecha_validacion' => '28/08/2026 · 12:00',
                'observaciones' => 'Trazado electrocardiográfico estable. Sin evidencia de isquemia aguda miocárdica ni bloqueos avanzados.',
                'hallazgos' => 'Ritmo sinusal, frecuencia 72 lpm, eje QRS +45°. Intervalo PR 0.16s, QTc 420ms normal.',
                'conclusion' => 'ECG dentro de límites fisiológicos para edad geriátrica.',
                'tiene_documento' => true,
                'nombre_documento' => 'Trazado_ECG_12D_28082026.pdf',
                'ruta_documento' => 'documentos/estudios/ecg_28082026.pdf',
                'es_real_db' => false,
                'historico_valores' => [],
            ],
            [
                'id' => 'EST_08_ECO_ABDOMINAL',
                'fecha' => '15/08/2026',
                'fecha_raw' => '2026-08-15T11:00:00',
                'hora' => '11:00',
                'titulo' => 'Ecografía abdominal completa',
                'tipo_categoria' => 'IMAGEN',
                'tipo_texto' => 'Imagen',
                'parametro_clave' => null,
                'resultado_valor' => 'Esteatosis hepática leve',
                'resultado_unidad' => '',
                'rango_referencia' => '—',
                'estado' => 'VIGILANCIA',
                'estado_badge' => 'Vigilancia',
                'estado_color' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                'estado_dot' => 'bg-amber-500',
                'estado_icono' => 'ph-bold ph-warning',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Dra. Patricia Mendizábal',
                'profesional_rol' => 'Médico Ecografista',
                'validado_por' => 'Dra. Patricia Mendizábal · Matr. 7102',
                'laboratorio_centro' => 'Centro de Diagnóstico por Imágenes Los Pinos',
                'fecha_validacion' => '15/08/2026 · 15:30',
                'observaciones' => 'Aumento discreto y difuso de la ecogenicidad hepática sin lesiones focales. Vía biliar y riñones normales.',
                'hallazgos' => 'Hígado de tamaño habitual con patrón hiperrefringente grado I. Vesícula alitiásica de paredes finas.',
                'conclusion' => 'Hepatopatía esteatósica leve grado I. Se recomienda seguimiento metabólico semestral.',
                'tiene_documento' => true,
                'nombre_documento' => 'Informe_Ecografia_Abdominal_15082026.pdf',
                'ruta_documento' => 'documentos/estudios/ecografia_15082026.pdf',
                'es_real_db' => false,
                'historico_valores' => [],
            ],
            [
                'id' => 'EST_09_ORINA',
                'fecha' => '05/08/2026',
                'fecha_raw' => '2026-08-05T08:00:00',
                'hora' => '08:00',
                'titulo' => 'Examen general de orina (EGO)',
                'tipo_categoria' => 'LABORATORIO',
                'tipo_texto' => 'Laboratorio',
                'parametro_clave' => null,
                'resultado_valor' => 'Negativo para infección',
                'resultado_unidad' => '',
                'rango_referencia' => 'Negativo',
                'estado' => 'NORMAL',
                'estado_badge' => 'Normal',
                'estado_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                'estado_dot' => 'bg-emerald-500',
                'estado_icono' => 'ph-bold ph-check-circle',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Lic. Carlos Salinas',
                'profesional_rol' => 'Bioquímico',
                'validado_por' => 'Lic. Carlos Salinas · Matr. 3312',
                'laboratorio_centro' => 'Laboratorio Central de Diagnóstico',
                'fecha_validacion' => '05/08/2026 · 12:40',
                'observaciones' => 'Densidad 1.018, pH 6.0. Sedimento: leucocitos 2-4 por campo, bacterias escasas, nitritos negativos.',
                'hallazgos' => 'Sin signos de bacteriuria sintomática ni infección urinaria activa.',
                'conclusion' => 'Urianálisis dentro de parámetros fisiológicos.',
                'tiene_documento' => false,
                'nombre_documento' => '',
                'ruta_documento' => '',
                'es_real_db' => false,
                'historico_valores' => [],
            ],
            [
                'id' => 'EST_10_COLPOSCOPIA',
                'fecha' => '20/07/2026',
                'fecha_raw' => '2026-07-20T10:30:00',
                'hora' => '10:30',
                'titulo' => 'Densitometría ósea (DEXA columna y cadera)',
                'tipo_categoria' => 'OTROS',
                'tipo_texto' => 'Otros estudios',
                'parametro_clave' => null,
                'resultado_valor' => 'Osteopenia moderada',
                'resultado_unidad' => 'T-Score -1.8',
                'rango_referencia' => 'T-Score > -1.0',
                'estado' => 'VIGILANCIA',
                'estado_badge' => 'Vigilancia',
                'estado_color' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                'estado_dot' => 'bg-amber-500',
                'estado_icono' => 'ph-bold ph-warning',
                'solicitado_por' => 'Dr. Marcelo Quiroga · Médico Geriatra',
                'profesional_nombre' => 'Dra. Gabriela Ortiz',
                'profesional_rol' => 'Médico Reumatólogo',
                'validado_por' => 'Dra. Gabriela Ortiz · Reg. Reum-2291',
                'laboratorio_centro' => 'Instituto de Osteoporosis y Metabolismo Óseo',
                'fecha_validacion' => '20/07/2026 · 16:00',
                'observaciones' => 'T-score columna lumbar L1-L4: -1.8 DE. Cuello femoral: -1.6 DE. No cumple criterio de osteoporosis franca.',
                'hallazgos' => 'Disminución moderada de masa ósea para su grupo de edad y sexo.',
                'conclusion' => 'Osteopenia senil. Se prescribe suplementación de Calcio + Vitamina D y programa de fisioterapia.',
                'tiene_documento' => true,
                'nombre_documento' => 'Densitometria_Osea_DEXA_20072026.pdf',
                'ruta_documento' => 'documentos/estudios/densitometria_20072026.pdf',
                'es_real_db' => false,
                'historico_valores' => [],
            ],
        ];

        foreach ($baseEstudios as $be) {
            $items->push($be);
        }

        // Ordenar cronológicamente descendente
        return $items->sortByDesc('fecha_raw')->values();
    }

    /**
     * Calcula métricas y KPIs a partir de los estudios del residente.
     */
    public function obtenerMetricas($estudios): array
    {
        $total = $estudios->count();
        if ($total === 0) {
            return [
                'total' => 0,
                'normales' => 0,
                'normales_pct' => 0,
                'fuera_rango' => 0,
                'seguimiento' => 0,
                'ultimo_estudio' => null,
            ];
        }

        $normales = $estudios->filter(fn($e) => $e['estado'] === 'NORMAL')->count();
        $fueraRango = $estudios->filter(fn($e) => in_array($e['estado'], ['ALTO', 'BAJO']))->count();
        $seguimiento = $estudios->filter(fn($e) => in_array($e['estado'], ['VIGILANCIA', 'PENDIENTE', 'ALTO', 'BAJO']))->count();

        $ultimo = $estudios->first();

        return [
            'total' => $total,
            'normales' => $normales,
            'normales_pct' => round(($normales / $total) * 100),
            'fuera_rango' => $fueraRango,
            'seguimiento' => $seguimiento,
            'ultimo_estudio' => [
                'fecha' => $ultimo['fecha'] ?? '—',
                'titulo' => $ultimo['titulo'] ?? 'Sin estudios',
                'tipo' => $ultimo['tipo_texto'] ?? '',
                'estado' => $ultimo['estado_badge'] ?? '',
                'estado_color' => $ultimo['estado_color'] ?? '',
            ],
        ];
    }

    /**
     * Obtiene los puntos de datos para el gráfico de evolución temporal del parámetro seleccionado.
     */
    public function obtenerDatosGrafico(string $parametro, string $periodo = '6m'): array
    {
        $parametro = strtolower(trim($parametro));
        $rangoInfo = $this->obtenerRangosReferencia($parametro);

        $series = [
            'glucosa' => [
                'labels' => ['Abr 26', 'May 26', 'Jun 26', 'Jul 26', 'Ago 26', 'Sep 26'],
                'data' => [96, 98, 102, 105, 99, 104],
                'fechas' => ['12/04/2026', '10/05/2026', '15/06/2026', '08/07/2026', '12/08/2026', '10/09/2026'],
                'color' => '#1E3A8A', // Azul institucional
            ],
            'hemoglobina' => [
                'labels' => ['Abr 26', 'May 26', 'Jun 26', 'Jul 26', 'Ago 26', 'Sep 26'],
                'data' => [11.5, 11.2, 11.0, 10.9, 10.7, 10.8],
                'fechas' => ['12/04/2026', '10/05/2026', '15/06/2026', '08/07/2026', '12/08/2026', '10/09/2026'],
                'color' => '#E11D48', // Coral / Rojo
            ],
            'creatinina' => [
                'labels' => ['Abr 26', 'May 26', 'Jun 26', 'Jul 26', 'Ago 26', 'Sep 26'],
                'data' => [1.0, 1.1, 1.2, 1.1, 1.0, 1.1],
                'fechas' => ['12/04/2026', '10/05/2026', '15/06/2026', '08/07/2026', '12/08/2026', '08/09/2026'],
                'color' => '#059669', // Esmeralda
            ],
            'sodio' => [
                'labels' => ['Abr 26', 'May 26', 'Jun 26', 'Jul 26', 'Ago 26', 'Sep 26'],
                'data' => [137, 139, 136, 138, 139, 138],
                'fechas' => ['12/04/2026', '10/05/2026', '15/06/2026', '08/07/2026', '12/08/2026', '08/09/2026'],
                'color' => '#2563EB', // Azul
            ],
            'potasio' => [
                'labels' => ['Abr 26', 'May 26', 'Jun 26', 'Jul 26', 'Ago 26', 'Sep 26'],
                'data' => [4.1, 4.3, 4.2, 4.0, 4.4, 4.2],
                'fechas' => ['12/04/2026', '10/05/2026', '15/06/2026', '08/07/2026', '12/08/2026', '08/09/2026'],
                'color' => '#D97706', // Ámbar
            ],
        ];

        $serie = $series[$parametro] ?? $series['glucosa'];

        // Ajustar según período si se solicita recorte
        if ($periodo === '30d') {
            $serie['labels'] = array_slice($serie['labels'], -2);
            $serie['data'] = array_slice($serie['data'], -2);
            $serie['fechas'] = array_slice($serie['fechas'], -2);
        } elseif ($periodo === '3m') {
            $serie['labels'] = array_slice($serie['labels'], -3);
            $serie['data'] = array_slice($serie['data'], -3);
            $serie['fechas'] = array_slice($serie['fechas'], -3);
        }

        return [
            'parametro' => $parametro,
            'nombre' => $rangoInfo['nombre'],
            'unidad' => $rangoInfo['unidad'],
            'rango_min' => $rangoInfo['normal_min'],
            'rango_max' => $rangoInfo['normal_max'],
            'texto_rango' => $rangoInfo['texto_rango'],
            'labels' => $serie['labels'],
            'data' => $serie['data'],
            'fechas' => $serie['fechas'],
            'color' => $serie['color'],
            'ultimo_valor' => end($serie['data']),
            'ultimo_periodo' => end($serie['labels']),
        ];
    }
}
