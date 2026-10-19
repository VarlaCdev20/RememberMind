<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Diccionario de adquisición cognitiva.
 *
 * Especifica las mediciones de los diecinueve componentes con su fuente y límites.
 * Es metadato de investigación, no un catálogo BDD ni una aplicación a un residente.
 * Una definición de campo nunca aporta por sí misma su valor, edición o autoría.
 */
final class CatalogoCapturaCognitiva
{
    public const VERSION = 'CAPTURA-COGNITIVA-3-20261019';

    public function componentes(): array
    {
        $hvlt = 'https://doi.org/10.3233/NRE-151286';
        $ruff = 'https://www.parinc.com/products/RUFF2AND7';
        $wais = 'https://www.pearsonclinical.es/portals/0/docproductos/13534059942.pdf';
        $wab = 'https://www.pearsonassessments.com/store/en/usd/p/100000194.html';
        $datos = [
            'MEM-S01' => ['HVLT-R forma 5', $hvlt, [
                'ensayo_1' => 'Entero de aciertos, 0–12', 'ensayo_2' => 'Entero de aciertos, 0–12', 'ensayo_3' => 'Entero de aciertos, 0–12'],
                'Total de tres ensayos; no confundir con reconocimiento o recuerdo con claves.'],
            'MEM-S02' => ['HVLT-R forma 5', $hvlt, [
                'recuerdo_diferido' => 'Entero de aciertos, 0–12', 'demora_minutos' => 'Número de minutos efectivos, referencia 20–25'],
                'Recuerdo libre diferido; no aplicar la regresión del total de aprendizaje.'],
            'MEM-S03' => ['FCSRT, versión española de 16 elementos', 'https://doi.org/10.1093/arclin/acp041', [
                'libres_por_ensayo' => 'Tres enteros de aciertos', 'adicionales_con_claves_por_ensayo' => 'Tres enteros, excluyendo los ya recuperados libremente',
                'libre_diferido' => 'Entero de aciertos', 'adicional_con_claves_diferido' => 'Entero de aciertos', 'demora_minutos' => 'Número de minutos efectivos',
                'codificacion_verificada' => 'Lógico con soporte de la aplicación'],
                'En esa versión, libre más adicional con claves no excede 16 por ensayo. La referencia española no es una norma boliviana.'],
            'MEM-S04' => ['HVLT-R, fase de reconocimiento identificada', 'https://www.parinc.com/products/HVLT-R', [
                'aciertos_reconocimiento' => 'Entero, 0–12 objetivos', 'falsas_alarmas_relacionadas' => 'Entero, 0–6',
                'falsas_alarmas_no_relacionadas' => 'Entero, 0–6', 'demora_minutos' => 'Número de minutos efectivos'],
                'Conservar aciertos y falsas alarmas por separado. La norma boliviana incorporada no cubre reconocimiento; no truncar ni normalizar un índice con ella.'],
            'ATE-S01' => ['Ruff 2 y 7', $ruff, [
                'ensayos' => 'Veinte registros: condición, objetivos disponibles, aciertos, errores, omisiones y segundos efectivos'],
                'Diez ensayos por condición. Rango editorial 16–70 años; no extrapolar a mayores de 70. Índices y máximos requieren el manual exacto.'],
            'ATE-S02' => ['Ruff 2 y 7, condiciones separadas', $ruff, [
                'ensayos' => 'Mismos veinte registros originales de detección automática y búsqueda controlada',
                'condicion' => 'Identidad de cada condición; no combinar sus puntuaciones brutas'],
                'La aplicación compartida con atención sostenida no es un segundo soporte independiente. No se ha verificado una norma boliviana geriátrica.'],
            'ATE-S03' => ['BTA, formas N y L de Schretlen 1997', 'https://doi.org/10.3233/NRE-151283', [
                'forma_n_aciertos' => 'Entero, 0–10', 'forma_l_aciertos' => 'Entero, 0–10', 'audicion_compatible' => 'Lógico documentado'],
                'El modelo boliviano compara el total N+L, 0–20. No lo convierte en una medida de atención sostenida.'],
            'EJE-S01' => ['Stroop Golden, protocolo de 45 segundos', 'https://doi.org/10.3233/NRE-151281', [
                'palabras_correctas' => 'Entero de respuestas correctas', 'colores_correctos' => 'Entero de respuestas correctas',
                'palabra_color_correctas' => 'Entero de respuestas correctas', 'segundos_palabras' => 'Número de segundos, referencia 45',
                'segundos_colores' => 'Número de segundos, referencia 45', 'segundos_palabra_color' => 'Número de segundos, referencia 45'],
                'Interferencia = WC − W×C/(W+C), denominador positivo. A35 Bolivia permite intervalos percentilares aproximados; no tiene regresión boliviana ni z.'],
            'EJE-S02' => ['Trail Making Test, parte B', 'https://doi.org/10.3233/NRE-151284', [
                'segundos_parte_b' => 'Número positivo de segundos, hasta 300', 'errores_observados' => 'Entero no negativo', 'finalizada' => 'Lógico documentado'],
                'Menor tiempo indica mejor rendimiento. Una tarea interrumpida se conserva censurada; no se presenta como tiempo exacto de finalización.'],
            'EJE-S03' => ['WAIS, dígitos inversos o secuenciación identificados', $wais, [
                'adaptacion_nacional' => 'Texto de identidad de edición', 'condicion' => 'Directo, inverso o secuenciación, separados',
                'puntajes_por_ensayo' => 'Mediciones originales según rúbrica', 'bruto_subprueba' => 'Puntuación original', 'longitud_maxima' => 'Entero si esa edición lo define'],
                'No transferir máximos o normas entre WAIS-III, WAIS-IV, idiomas o adaptaciones. Directo no sustituye manipulación de memoria de trabajo.'],
            'EJE-S04' => ['Torre de Londres, edición y forma identificadas', 'https://academic.oup.com/acn/article-abstract/24/4/413/3645', [
                'movimientos_por_problema' => 'Enteros no negativos', 'resueltos' => 'Enteros según criterio de esa edición',
                'violaciones_regla' => 'Enteros', 'violaciones_tiempo' => 'Enteros', 'tiempos_por_problema' => 'Segundos de inicio, ejecución y total'],
                'NEURONORMA 2009 y TOL-DX segunda edición son referencias distintas. No se verificó una norma boliviana aplicable.'],
            'EJE-S05' => ['Semejanzas o Matrices, subprueba y edición identificadas', $wais, [
                'subprueba' => 'Identidad de una subprueba concreta', 'puntajes_por_elemento' => 'Mediciones originales según rúbrica', 'bruto_subprueba' => 'Puntuación original'],
                'No sumar Semejanzas y Matrices ni atribuir razonamiento general a categorías de M-WCST. Falta norma aplicable y manual de la edición usada.'],
            'LEN-S01' => ['BNT estándar de 60 elementos, Kaplan 1983', 'https://doi.org/10.3233/NRE-151278', [
                'aciertos_espontaneos' => 'Entero, 0–60', 'aciertos_clave_semantica' => 'Entero adicional; suma hasta 60',
                'ayudas_fonemicas' => 'Registro separado; no se incluye en ese total'],
                'No admite formas de 15 elementos o ediciones distintas. El modelo boliviano no ajusta por edad; la corrección sigue el protocolo publicado.'],
            'LEN-S02' => ['Fluidez F, A, S y animales, 60 segundos por condición', 'https://doi.org/10.3233/NRE-151279', [
                'f_validas' => 'Entero de respuestas válidas F', 'a_validas' => 'Entero de respuestas válidas A',
                's_validas' => 'Entero de respuestas válidas S', 'animales_validas' => 'Entero de respuestas válidas, animales',
                'f_segundos' => 'Número de segundos, referencia 60', 'a_segundos' => 'Número de segundos, referencia 60',
                's_segundos' => 'Número de segundos, referencia 60', 'animales_segundos' => 'Número de segundos, referencia 60',
                'repeticiones_intrusiones' => 'Recuento original separado, según protocolo'],
                'Cuatro modelos propios; no se calcula un z promedio ni un total FAS con la norma de una letra. Una condición ausente no invalida las otras.'],
            'LEN-S03' => ['Token Test, versión y rúbrica identificadas', 'https://doi.org/10.1093/arclin/acp039', [
                'presentaciones_por_elemento' => 'Primera o segunda presentación', 'puntajes_por_elemento' => 'Decimales según rúbrica',
                'puntaje_total' => 'Decimal; máximo 36 solo en versión abreviada correspondiente', 'audicion_vision' => 'Condiciones y apoyos documentados'],
                'No obligar a enteros: algunas versiones admiten medios puntos. La referencia NEURONORMA es española.'],
            'LEN-S04' => ['WAB, repetición; forma completa o bedside identificada', $wab, [
                'forma' => 'Identidad completa o bedside', 'puntajes_por_elemento' => 'Mediciones originales según rúbrica', 'total_repeticion' => 'Puntuación original de esa forma'],
                'No intercambiar formas ni imponer un máximo sin edición. Falta versión española y referencia poblacional compatible verificadas.'],
            'LEN-S05' => ['WAB, muestra de discurso en tarea definida', $wab, [
                'tarea' => 'Identidad de conversación o descripción', 'referencia_muestra' => 'Referencia documental al original',
                'contenido' => 'Puntuación conforme a rúbrica identificada', 'fluidez' => 'Puntuación separada conforme a esa rúbrica'],
                'Una nota narrativa de cuidados no equivale a la subprueba. No se verificó una norma boliviana para esta edición.'],
            'VIS-S01' => ['VOSP o JLO; instrumento y subprueba identificados', 'https://doi.org/10.1093/arclin/acp040', [
                'subprueba_forma' => 'Identidad exacta, sin total VOSP genérico', 'administrados' => 'Entero de elementos puntuados',
                'correctos' => 'Entero de aciertos', 'control_visual' => 'Condición y apoyos documentados'],
                'JLO estándar: 30 elementos puntuados; no atribuir este máximo a VOSP. Normas NEURONORMA españolas, no bolivianas.'],
            'VIS-S02' => ['Rey–Osterrieth, figura A, fase de copia, Rey 2009', 'https://doi.org/10.3233/NRE-151285', [
                'puntaje_copia' => 'Número, 0–36, en medios puntos', 'elementos_revisados' => 'Entero, 18', 'referencia_produccion' => 'Referencia a la producción original'],
                'No intercambiar copia con recuerdo. La rúbrica se aplica al original; el software no inventa un dibujo ni lo puntúa desde una observación.'],
        ];
        $resultado = [];
        $registro = (new RegistroMetodosInstrumentales)->metodos();
        foreach ($datos as $codigo => [$instrumento, $fuente, $campos, $limite]) {
            $metodos = array_filter($registro, fn ($m) => in_array($codigo, $m['componentes'], true));
            // Un cálculo bruto no aumenta la cobertura normativa de los ocho componentes.
            $descriptivos = array_filter($metodos, fn ($m) => ($m['tipo'] ?? null) === 'CALCULO_DESCRIPTIVO');
            $metodos = array_diff_key($metodos, $descriptivos);
            $resultado[$codigo] = ['version' => self::VERSION, 'instrumento_referencia' => $instrumento, 'fuente' => $fuente,
                'campos' => $campos, 'limite' => $limite, 'metodos_disponibles' => array_keys($metodos),
                'calculos_descriptivos_disponibles' => array_keys($descriptivos),
                'estado_metodo' => $metodos === [] ? 'CAPTURA_DOCUMENTADA_SIN_NORMALIZADOR' : 'NORMALIZADOR_DISPONIBLE_CON_CONDICIONES',
                'instrumento_administrado' => null, 'mediciones_residente' => null];
        }

        return $resultado;
    }
}
