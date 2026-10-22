<?php

declare(strict_types=1);

namespace Database\Seeders\Datos;

/** Bibliografía y oráculo de aceptación; nunca se insertan como hechos de un residente. */
final class ManifiestoCognitivo30
{
    public const VERSION = 'INVESTIGACION-COGNITIVA-30-20261022-V1';

    /** Identidades ficticias verosímiles; no proceden de personas ni de participantes de datasets. */
    public const NOMBRES = [
        ['Carmen', 'Salvatierra', 'Rueda'], ['Ernesto', 'Villarroel', 'Céspedes'], ['Lucía', 'Arandia', 'Mercado'],
        ['Beatriz', 'Montellano', 'Ruiz'], ['Ricardo', 'Castañeda', 'Vargas'], ['Amelia', 'Rocabado', 'Serrano'],
        ['Héctor', 'Saavedra', 'Molina'], ['Isabel', 'Valcárcel', 'Robles'], ['Alfonso', 'Bustamante', 'Leiva'],
        ['Matilde', 'Pacheco', 'Luján'], ['Ramón', 'Escalante', 'Núñez'], ['Inés', 'Velasco', 'Benavides'],
        ['Jorge', 'Mendizábal', 'Durán'], ['Cecilia', 'Quiroga', 'Valverde'], ['Arturo', 'Figueroa', 'Salcedo'],
        ['Leonor', 'Benavente', 'Cruz'], ['Mario', 'Orellana', 'Vidal'], ['Pilar', 'Cordero', 'Espinoza'],
        ['Tomás', 'Alvarado', 'Salinas'], ['Susana', 'Ledesma', 'Cabrera'], ['Enrique', 'Peñaranda', 'Vela'],
        ['Clara', 'Barrientos', 'Solano'], ['Julián', 'Amat', 'Flores'], ['Alicia', 'Treviño', 'Prado'],
        ['Samuel', 'Baldivieso', 'Rojas'], ['Graciela', 'Montalvo', 'Aguirre'], ['Adela', 'Gamarra', 'Villalobos'],
        ['Rubén', 'Santillán', 'Arce'], ['Victoria', 'Iturralde', 'Méndez'], ['Manuel', 'Segovia', 'Cárdenas'],
    ];

    public static function construir(array $casos, array $metodos): array
    {
        $referencias = [
            'PETERSEN-2004' => ['titulo' => 'Mild cognitive impairment as a diagnostic entity',
                'autor' => 'Petersen RC', 'anio' => 2004, 'doi' => '10.1111/j.1365-2796.2004.01388.x',
                'url' => 'https://doi.org/10.1111/j.1365-2796.2004.01388.x',
                'uso' => 'Fundamento conceptual de los cuatro perfiles; no aporta historias individuales ni un umbral universal.'],
            'NIA-AA-2011' => ['titulo' => 'The diagnosis of mild cognitive impairment due to Alzheimer’s disease: Recommendations from the NIA-AA workgroups',
                'autor' => 'Albert MS et al.', 'anio' => 2011, 'doi' => '10.1016/j.jalz.2011.03.008',
                'url' => 'https://pmc.ncbi.nlm.nih.gov/articles/PMC3312027/',
                'uso' => 'Cambio, evidencia cognitiva, independencia relativa y revisión clínica; no demuestra etiología de los casos.'],
            'HVLT-2015' => ['titulo' => 'Hopkins Verbal Learning Test–Revised: Normative data for the Latin American Spanish speaking adult population',
                'autor' => 'Arango-Lasprilla JC et al.', 'anio' => 2015, 'doi' => '10.3233/NRE-151286',
                'url' => 'https://journals.sagepub.com/doi/10.3233/NRE-151286'],
            'BTA-2015' => ['titulo' => 'Brief Test of Attention: Normative data for the Latin American Spanish speaking adult population',
                'autor' => 'Rivera D et al.', 'anio' => 2015, 'doi' => '10.3233/NRE-151283',
                'url' => 'https://journals.sagepub.com/doi/10.3233/NRE-151283'],
            'TMT-2015' => ['titulo' => 'Trail Making Test: Normative data for the Latin American Spanish speaking adult population',
                'autor' => 'Arango-Lasprilla JC et al.', 'anio' => 2015, 'doi' => '10.3233/NRE-151284',
                'url' => 'https://pubmed.ncbi.nlm.nih.gov/26639932/'],
            'BNT-2015' => ['titulo' => 'Standard form of the Boston Naming Test: Normative data for the Latin American Spanish speaking adult population',
                'autor' => 'Olabarrieta-Landa L et al.', 'anio' => 2015, 'doi' => '10.3233/NRE-151278',
                'url' => 'https://pubmed.ncbi.nlm.nih.gov/26639925/'],
            'ROCF-2015' => ['titulo' => 'Rey–Osterrieth Complex Figure – copy and immediate recall: Normative data for the Latin American Spanish speaking adult population',
                'autor' => 'Rivera D et al.', 'anio' => 2015, 'doi' => '10.3233/NRE-151285',
                'url' => 'https://journals.sagepub.com/doi/10.3233/NRE-151285'],
        ];
        $porDominio = ['COG-MEM' => 'HVLT-2015', 'COG-ATE' => 'BTA-2015', 'COG-EJE' => 'TMT-2015',
            'COG-LEN' => 'BNT-2015', 'COG-VIS' => 'ROCF-2015'];
        $aceptacion = [];
        foreach ($casos as $i => $caso) {
            $dominios = [];
            foreach (array_keys($porDominio) as $dominio) {
                $dominios[$dominio] = match (true) {
                    $dominio === 'COG-VIS' && $caso['variante'] === 'sin_visuoespacial' => 'U',
                    $dominio === 'COG-VIS' && $caso['variante'] === 'visuoespacial_no_interpretable' => 'I',
                    $dominio === 'COG-MEM' && $caso['variante'] === 'contexto_no_interpretado' => 'U',
                    $dominio === 'COG-MEM' && $caso['variante'] === 'contradiccion_memoria' => 'X',
                    default => in_array($dominio, $caso['alterados'], true) ? 'A' : 'P',
                };
            }
            $patron = match (true) {
                $i < 3 => 'PET-01', $i < 6 => 'PET-02', $i < 9 => 'PET-03', $i < 12 => 'PET-04', default => null,
            };
            $estado = match (true) {
                $i < 12 => 'PATRON_ORIENTATIVO_CON_SOPORTE',
                $i < 18 => 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO',
                default => match ($caso['variante']) {
                    'c01_negativo', 'c03_negativo', 'c05_negativo' => 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO',
                    'c04_negativo', 'impedimento_diferencial' => 'ABSTENCION_EVALUACION_DIFERENCIAL',
                    'contradiccion_memoria' => 'ABSTENCION_CONTRADICCION',
                    default => 'ABSTENCION_INFORMACION_INSUFICIENTE',
                },
            };
            $aceptacion[$caso['caso']] = ['patron_esperado' => $patron, 'estado_esperado' => $estado,
                'c02_esperado' => match (true) {
                    $i >= 12 && $i < 18 => 'N', $caso['variante'] === 'contradiccion_memoria' => 'X',
                    $caso['variante'] === 'contexto_no_interpretado' => 'U', default => 'S',
                },
                'variante' => $caso['variante'], 'dominios_esperados' => $dominios,
                'dominios_alterados_de_entrada' => $caso['alterados'],
                'referencias' => ['PETERSEN-2004', 'NIA-AA-2011', ...array_values($porDominio)],
                'contrato' => 'CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md'];
        }

        return ['version' => self::VERSION, 'fecha_revision_bibliografica' => '2026-10-22',
            'procedencia' => 'CASOS_SINTETICOS_DE_INGENIERIA_CON_BIBLIOGRAFIA', 'datos_individuales_importados' => false,
            'aprobacion_clinica' => false, 'referencias' => $referencias,
            'datasets_revisados_no_importados' => [
                'ADNI' => ['fuente' => 'https://adni.loni.usc.edu/wp-content/themes/adni_2023/documents/ADNI_Data_Use_Agreement.pdf',
                    'estado' => 'SIN_ACCESO_A_DATOS_INDIVIDUALES',
                    'limite' => 'Acceso y uso sujetos al DUA; restricciones sobre redistribución y recursos de IA. No atribuir sus participantes a residentes.'],
                'OASIS' => ['fuente' => 'https://bpb-us-e2.wpmucdn.com/sites.wustl.edu/dist/6/4383/files/2025/07/Data-Use-Agreement_July2025.pdf',
                    'version_condiciones' => '2025-07', 'estado' => 'SIN_ACCESO_A_DATOS_INDIVIDUALES',
                    'limite' => 'Requiere solicitud/DUA según proyecto. Un CDR o una etiqueta de demencia no equivale a los cuatro perfiles del contrato local.'],
            ],
            'poblacion_ensayo' => ['edad_minima' => 61, 'edad_maxima' => 89, 'escolaridad_anios' => [4, 8, 12],
                'pais_normativo' => 'BO', 'idioma' => 'es', 'lectoescritura' => true,
                'limite' => 'Casos seleccionados para cubrir contratos; no son una muestra representativa ni estiman prevalencia.'],
            'metodos' => array_map(static fn ($d, $m) => ['dominio' => $d, 'metodo' => $m,
                'referencia' => $porDominio[$d], 'reactivos_reproducidos' => false,
                'limite' => 'Normalización numérica independiente del juicio original A/P/I; sin umbral clínico nuevo.'], array_keys($metodos), array_values($metodos)),
            'aceptacion' => $aceptacion];
    }

    /** Retroalimentación técnica: informa discrepancias, nunca cambia hechos, reglas ni aprobaciones. */
    public static function comparar(array $manifiesto, array $resultados): array
    {
        $pendientes = $manifiesto['aceptacion'];
        $discrepancias = [];
        foreach ($resultados as $resultado) {
            $id = $resultado['caso'] ?? '';
            if (! isset($pendientes[$id])) {
                $discrepancias[] = ['caso' => $id, 'campo' => 'identidad', 'motivo' => 'CASO_DESCONOCIDO_O_REPETIDO'];

                continue;
            }
            $esperado = $pendientes[$id];
            unset($pendientes[$id]);
            foreach (['patron_calculado', 'estado', 'requisitos', 'dominios'] as $campo) {
                if (! array_key_exists($campo, $resultado)) {
                    $discrepancias[] = ['caso' => $id, 'campo' => $campo, 'motivo' => 'SALIDA_AUSENTE'];
                }
            }
            foreach (['patron_esperado' => $resultado['patron_calculado'] ?? null,
                'estado_esperado' => $resultado['estado'] ?? null,
                'c02_esperado' => $resultado['requisitos']['C02'] ?? null] as $campo => $valor) {
                if ($esperado[$campo] !== $valor) {
                    $discrepancias[] = ['caso' => $id, 'campo' => $campo, 'esperado' => $esperado[$campo], 'obtenido' => $valor];
                }
            }
            foreach ($esperado['dominios_esperados'] as $dominio => $estado) {
                if (($resultado['dominios'][$dominio] ?? null) !== $estado) {
                    $discrepancias[] = ['caso' => $id, 'campo' => 'dominios.'.$dominio,
                        'esperado' => $estado, 'obtenido' => $resultado['dominios'][$dominio] ?? null];
                }
            }
            if (count($resultado['dominios'] ?? []) !== 5) {
                $discrepancias[] = ['caso' => $id, 'campo' => 'dominios', 'motivo' => 'CONJUNTO_DE_DOMINIOS_INCOMPLETO_O_EXCEDIDO'];
            }
        }
        foreach (array_keys($pendientes) as $id) {
            $discrepancias[] = ['caso' => $id, 'campo' => 'identidad', 'motivo' => 'CASO_NO_EJECUTADO'];
        }

        return ['version' => self::VERSION, 'satisfactorio' => $discrepancias === [],
            'casos_esperados' => count($manifiesto['aceptacion']), 'casos_ejecutados' => count($resultados),
            'discrepancias' => $discrepancias, 'validez_clinica_demostrada' => false,
            'reglas_modificadas' => false];
    }
}
