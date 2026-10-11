<?php

namespace App\Modulos\Identidad\Dominio\Reglas;

final class DocumentosPersonalInstitucional
{
    public static function paraRol(string $rol): array
    {

        $docs = [];

        // --- BLOQUE: DOCUMENTACIÓN DEL INGRESANTE ---

        // Cédula de Identidad (Obligatorio inmediato para todos)
        $docs[] = [
            'id' => 'CI',
            'cod_tipo_doc' => 'TDU_0001',
            'nombre' => 'Cédula de Identidad (Anverso y Reverso)',
            'desc' => 'Copia legible del documento de identidad',
            'obligatorio_inmediato' => true,
            'permite_plazo' => false,
            'tipo' => 'ingresante',
        ];

        // Fotografía actual (Obligatorio inmediato para todos)
        $docs[] = [
            'id' => 'FOTO_DOC',
            'cod_tipo_doc' => 'TDU_0003',
            'nombre' => 'Fotografía Actual',
            'desc' => 'Fotografía formal fondo blanco',
            'obligatorio_inmediato' => true,
            'permite_plazo' => false,
            'tipo' => 'ingresante',
        ];

        // Hoja de Vida
        $docs[] = [
            'id' => 'CV',
            'cod_tipo_doc' => 'TDU_0006',
            'nombre' => 'Hoja de Vida / CV',
            'desc' => 'Curriculum Vitae actualizado y documentado',
            'obligatorio_inmediato' => true,
            'permite_plazo' => false,
            'tipo' => 'ingresante',
        ];

        // Roles específicos
        if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])) {
            $docs[] = [
                'id' => 'TITULO',
                'cod_tipo_doc' => 'TDU_0008',
                'nombre' => 'Título o Certificado de Formación',
                'desc' => 'Título profesional o certificado académico/técnico',
                'obligatorio_inmediato' => true,
                'permite_plazo' => false,
                'tipo' => 'ingresante',
            ];

            if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
                $docs[] = [
                    'id' => 'MATRICULA',
                    'cod_tipo_doc' => 'TDU_0010',
                    'nombre' => 'Matrícula Profesional',
                    'desc' => 'Registro profesional vigente (Obligatorio para médicos)',
                    'obligatorio_inmediato' => $rol === 'MEDICO GENERAL/GERIATRA',
                    'permite_plazo' => $rol !== 'MEDICO GENERAL/GERIATRA',
                    'tipo' => 'ingresante',
                ];
            }

            if ($rol === 'MEDICO GENERAL/GERIATRA') {
                $docs[] = [
                    'id' => 'CERT_ESP',
                    'cod_tipo_doc' => 'TDU_0011',
                    'nombre' => 'Certificado de Especialidad',
                    'desc' => 'Requerido si se registra como especialista (Ej. Geriatra)',
                    'obligatorio_inmediato' => false,
                    'permite_plazo' => true,
                    'tipo' => 'ingresante',
                ];
            }
        }

        // Con Plazo (48 horas)
        $docs[] = [
            'id' => 'DOMICILIO',
            'nombre' => 'Referencia o Comprobante de Domicilio',
            'desc' => 'Factura de luz, agua o croquis',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante',
        ];

        $docs[] = [
            'id' => 'EXP_LABORAL',
            'nombre' => 'Certificados de Experiencia',
            'desc' => 'Respaldo de la experiencia laboral',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante',
        ];

        $docs[] = [
            'id' => 'CAPACITACION',
            'cod_tipo_doc' => 'TDU_0018',
            'nombre' => 'Capacitaciones Específicas',
            'desc' => 'Certificados relevantes al cargo',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante',
        ];

        $docs[] = [
            'id' => 'ANTECEDENTES',
            'cod_tipo_doc' => 'TDU_0007',
            'nombre' => 'Certificado de Antecedentes',
            'desc' => 'FELCC, FELCN, REJAP (si la institución lo exige)',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante',
        ];

        // Opcionales
        $docs[] = [
            'id' => 'RECOMENDACION',
            'nombre' => 'Cartas de Recomendación',
            'desc' => 'Referencias laborales opcionales',
            'obligatorio_inmediato' => false,
            'permite_plazo' => false,
            'tipo' => 'ingresante',
        ];

        $docs[] = [
            'id' => 'OTROS',
            'nombre' => 'Otros Respaldos',
            'desc' => 'Documentación adicional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => false,
            'tipo' => 'ingresante',
        ];

        // --- BLOQUE: DOCUMENTACIÓN INSTITUCIONAL ---
        $docs = array_merge($docs, self::institucionales($rol));

        return $docs;
    }

    public static function institucionales(string $rol): array
    {
        $docs = [];
        $esAdmin = in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']);

        // 1. Ficha institucional del personal
        $docs[] = [
            'id' => 'FICHA',
            'nombre' => 'Ficha Institucional del Personal',
            'desc' => 'Resumen de datos y relación con la institución',
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE',
        ];

        // 2. Contrato laboral o prestación profesional
        $docs[] = [
            'id' => 'CONTRATO',
            'cod_tipo_doc' => 'TDU_0004',
            'nombre' => $esAdmin ? 'Contrato Laboral' : 'Contrato Laboral o Prestación Profesional',
            'desc' => 'Acuerdo formal de relación laboral o servicios',
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE',
        ];

        // 3. Declaración de confidencialidad
        $docs[] = [
            'id' => 'CONFIDENCIALIDAD',
            'cod_tipo_doc' => 'TDU_0002',
            'nombre' => 'Declaración de Confidencialidad y Manejo de Información Sensible',
            'desc' => 'Compromiso de resguardo de información clínica/administrativa',
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE',
        ];

        // 4. Acta de asignación de funciones
        $descFunciones = match ($rol) {
            'SUPERADMINISTRADOR', 'ADMINISTRADOR' => 'Funciones administrativas, gestión institucional y documental',
            'ENFERMEROS' => 'Atención al adulto mayor, signos vitales, bioseguridad, medicamentos',
            'MEDICO GENERAL/GERIATRA' => 'Valoración médica, indicaciones, derivaciones y registro clínico',
            'PSICOLOGO/A' => 'Evaluaciones cognitivas/emocionales y observaciones psicológicas',
            'NUTRICIONISTA' => 'Valoración nutricional, dietas y restricciones alimentarias',
            'FISIOTERAPEUTA' => 'Movilidad segura, prevención de caídas y sesiones',
            'PEDAGOGO' => 'Estimulación cognitiva/social y acompañamiento socioeducativo',
            default => 'Asignación general de funciones'
        };

        $docs[] = [
            'id' => 'FUNCIONES',
            'nombre' => 'Acta de Asignación de Funciones y Horario',
            'desc' => $descFunciones,
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE',
        ];

        return $docs;
    }
}
