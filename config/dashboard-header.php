<?php

// Identidad visual y texto de apoyo. Esta configuración no concede accesos.
return [
    'variants' => [
        'SUPERADMINISTRADOR' => [
            'tone' => 'superadmin', 'label' => 'Superadministración',
            'subtitle' => 'Supervisión general del centro y sus áreas operativas.',
            'scope' => 'Sistema · Institución · Residencia · Operación · Clínica',
            'image_label' => 'Supervisión institucional',
        ],
        'GERENTE' => [
            'tone' => 'manager', 'label' => 'Gerencia',
            'subtitle' => 'Dirección y planificación institucional.',
            'scope' => 'Personal · Áreas · Turnos · Cobertura',
            'image_label' => 'Dirección institucional',
        ],
        'ADMINISTRADOR' => [
            'tone' => 'admin', 'label' => 'Administración',
            'subtitle' => 'Coordinación institucional, residencial y administrativa.',
            'scope' => 'Personal · Admisiones · Residentes · Camas · Documentos',
            'image_label' => 'Gestión institucional',
        ],
        'MEDICO GENERAL/GERIATRA' => [
            'tone' => 'doctor', 'label' => 'Medicina geriátrica',
            'subtitle' => 'Seguimiento clínico y valoración integral de residentes.',
            'scope' => 'Diagnósticos · Estudios · Prescripciones · Planes · Alertas',
            'image_label' => 'Atención geriátrica integral',
        ],
        'ENFERMEROS' => [
            'tone' => 'nursing', 'label' => 'Enfermería',
            'subtitle' => 'Cuidados continuos y seguimiento asistencial del turno.',
            'scope' => 'Signos · Medicación · Cuidados · Incidentes · Pases',
            'image_label' => 'Cuidado continuo',
        ],
        'PSICOLOGO/A' => [
            'tone' => 'psychology', 'label' => 'Psicología',
            'subtitle' => 'Bienestar emocional, cognición y seguimiento psicológico.',
            'scope' => 'Cognición · Conducta · Instrumentos · Intervenciones',
            'image_label' => 'Bienestar emocional',
        ],
        'NUTRICIONISTA' => [
            'tone' => 'nutrition', 'label' => 'Nutrición',
            'subtitle' => 'Seguimiento nutricional y evolución antropométrica.',
            'scope' => 'Antropometría · Ingesta · Hidratación · Plan nutricional',
            'image_label' => 'Cuidado nutricional',
        ],
        'FISIOTERAPEUTA' => [
            'tone' => 'physio', 'label' => 'Fisioterapia',
            'subtitle' => 'Movilidad, funcionalidad y rehabilitación del residente.',
            'scope' => 'Movilidad · Dolor · Dispositivos · Valoraciones funcionales',
            'image_label' => 'Rehabilitación funcional y movilidad',
        ],
        'PEDAGOGO' => [
            'tone' => 'pedagogy', 'label' => 'Pedagogía',
            'subtitle' => 'Estimulación, participación y acompañamiento pedagógico.',
            'scope' => 'Actividades · Cognición · Conducta · Seguimiento',
            'image_label' => 'Estimulación y participación',
        ],
        'FAMILIAR' => [
            'tone' => 'family', 'label' => 'Familiar',
            'subtitle' => 'Mantente al día con la información autorizada de tu familiar.',
            'scope' => 'Información y actividades autorizadas',
            'image_label' => 'Acompañamiento familiar',
        ],
    ],
];
