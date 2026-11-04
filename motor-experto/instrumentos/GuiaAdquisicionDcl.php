<?php

declare(strict_types=1);

namespace MotorExperto\instrumentos;

/** Ayuda de captura propia. Bibliografía y preguntas guía no son reglas diagnósticas. */
final class GuiaAdquisicionDcl
{
    public const VERSION = 'GUIA-ADQUISICION-DCL-1';

    public function fuentes(): array
    {
        return [
            'NIA_AA_2011' => ['titulo' => 'Albert et al. — criterios clínicos de DCL',
                'url' => 'https://pmc.ncbi.nlm.nih.gov/articles/PMC3312027/'],
            'DETECD_HISTORIA' => ['titulo' => 'Alzheimer’s Association — evaluación diagnóstica, historia y contexto',
                'url' => 'https://pmc.ncbi.nlm.nih.gov/articles/PMC12173843/'],
            'DETECD_INSTRUMENTOS' => ['titulo' => 'Alzheimer’s Association — instrumentos de evaluación clínica',
                'url' => 'https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/'],
            'MOCA' => ['titulo' => 'MoCA Cognition — administración e interpretación',
                'url' => 'https://mocacognition.com/faq/'],
            'RUDAS' => ['titulo' => 'Dementia Australia — RUDAS',
                'url' => 'https://www.dementia.org.au/professionals/assessment-and-diagnosis-dementia/rowland-universal-dementia-assessment-scale-rudas'],
            'RUDAS_PE' => ['titulo' => 'Custodio et al. 2020 — RUDAS en adultos mayores de Lima',
                'url' => 'https://www.frontiersin.org/journals/neurology/articles/10.3389/fneur.2020.00374/full'],
            'FAQ' => ['titulo' => 'Hartford Institute — funcionamiento instrumental y FAQ',
                'url' => 'https://hign.org/sites/default/files/2020-06/Try_This_Dementia_13.pdf'],
        ];
    }

    /** Orienta las notas y campos existentes; no añade requisitos de captura ni puntuaciones. */
    public function secciones(): array
    {
        return [
            'CONTEXTO' => ['titulo' => 'Contexto de la evaluación', 'elementos' => [],
                'registrar' => ['Edad en la fecha de aplicación, escolaridad en años, lectura y escritura referidas.',
                    'Idioma principal, apoyos de comunicación y condiciones de visión y audición.',
                    'Informante principal, vínculo, conocimiento del nivel previo y discrepancias con el relato del residente.'],
                'ubicacion' => 'Atención vinculada y notas de la sesión; no completar datos desconocidos por defecto.',
                'fuentes' => ['DETECD_HISTORIA', 'DETECD_INSTRUMENTOS']],
            'CAMBIO' => ['titulo' => 'Cambio respecto al funcionamiento previo', 'elementos' => ['C01'],
                'registrar' => ['Qué ha cambiado, desde cuándo y cómo ha evolucionado; anotar ejemplos concretos y quién los refiere.',
                    'Distinguir lo relatado por el residente, lo referido por el informante y lo observado por el profesional.'],
                'ubicacion' => 'Entrevista y valoración original de cambio, con sus soportes.',
                'fuentes' => ['NIA_AA_2011', 'DETECD_HISTORIA']],
            'COGNICION' => ['titulo' => 'Mediciones e interpretación por área',
                'elementos' => ['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'],
                'registrar' => ['Instrumento, edición, forma, idioma, fecha clínica y profesional que lo aplicó.',
                    'Resultados originales, unidades, ayudas, interrupciones y referencia del método utilizado.',
                    'Separar el total de cribado de la interpretación sustentada de cada área.'],
                'ubicacion' => 'Aplicación instrumental y valoración original de dominio; la alteración objetiva se deriva del conjunto, no se captura por separado.',
                'fuentes' => ['DETECD_INSTRUMENTOS', 'MOCA', 'RUDAS', 'RUDAS_PE']],
            'FUNCION' => ['titulo' => 'Funcionamiento cotidiano', 'elementos' => ['C03'],
                'registrar' => ['Qué actividades realizaba antes y cuáles puede realizar ahora, con ejemplos.',
                    'Oportunidad real de hacer la actividad, ayuda recibida y motivo de la ayuda.',
                    'Distinguir dificultad cognitiva, limitación física y tareas que la residencia realiza por organización.'],
                'ubicacion' => 'Formulario propio de funcionamiento cotidiano y valoración de independencia relativa.',
                'fuentes' => ['NIA_AA_2011', 'FAQ']],
            'DIFERENCIAL' => ['titulo' => 'Posibles explicaciones alternativas', 'elementos' => ['C04'],
                'registrar' => ['Inicio agudo o fluctuante, enfermedad intercurrente, ánimo, sueño y condiciones sensoriales relevantes.',
                    'Medicamentos y otras condiciones que el profesional considere que pueden afectar el rendimiento.',
                    'Evaluaciones o estudios revisados, hallazgos y aspectos todavía no resueltos.'],
                'ubicacion' => 'Atención profesional, antecedentes, diagnósticos o documento clínico enlazado.',
                'fuentes' => ['DETECD_HISTORIA', 'DETECD_INSTRUMENTOS']],
            'CALIDAD' => ['titulo' => 'Interpretabilidad de la información', 'elementos' => ['C05'],
                'registrar' => ['Si la administración y la referencia de interpretación corresponden al instrumento y al contexto.',
                    'Qué resultados no son interpretables y por qué; no convertir ausencias en cero o normalidad.',
                    'Las valoraciones exactas de las cinco áreas que se revisaron para este conjunto.'],
                'ubicacion' => 'Condiciones de aplicación y valoración original de calidad con sus soportes.',
                'fuentes' => ['DETECD_INSTRUMENTOS']],
        ];
    }

    public function contrato(): array
    {
        return ['version' => self::VERSION, 'fecha_consulta_fuentes' => '2026-10-31',
            'alcance' => 'AYUDA_DESCRIPTIVA_DE_CAPTURA_NO_INFERENCIA',
            'poblacion_objetivo' => 'PERSONAS_MAYORES_DE_60_ANIOS',
            'secciones' => $this->secciones(), 'fuentes' => $this->fuentes(),
            'limites' => ['La guía no reproduce tareas de pruebas ni sustituye sus instrucciones oficiales.',
                'MoCA y RUDAS son alternativas de cribado; sus totales no acreditan cinco dominios ni un diagnóstico.',
                'Las ocho actividades del formulario propio no son el FAQ ni producen su puntuación.',
                'La bibliografía no modifica las reglas institucionales C01–C05 ni prescribe estudios o tratamientos.',
                'No se exige un plazo fijo de seguimiento para desarrollar o ejecutar el motor.']];
    }
}
