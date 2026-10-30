<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use DomainException;

/**
 * Estado de preparación de las familias seleccionadas, sin leer casos ni ejecutar perfiles.
 * Referencias candidatas no equivalen a identidad operativa, licencia o método clínico aprobado.
 */
final class PreparacionCircuitoInstrumental
{
    public const CONTRATO = 'PREPARACION-CIRCUITO-INICIAL-1';

    public function resumir(array $perfilesConfigurados, array $metodosRegistrados): array
    {
        foreach ($perfilesConfigurados as $perfil) {
            if (! is_array($perfil)) {
                throw new DomainException('La configuración instrumental requiere perfiles estructurados.');
            }
        }

        $familias = [
            'I01' => ['nombre' => 'MoCA Full en papel', 'posicion' => 'ALTERNATIVA_COGNITIVA',
                'referencia_candidata' => '8.1, español; paquete oficial exacto pendiente de cotejo.',
                'material' => 'PAQUETE_OFICIAL_ES_NO_OBTENIDO',
                'fuentes' => ['https://mocacognition.com/faq/', 'https://mocacognition.com/terms-of-use/'],
                'faltante_especifico' => 'Cerrar paquete oficial, alcance de incorporación en software y protocolo aplicable; el total no se distribuye entre cinco dominios.'],
            'I02' => ['nombre' => 'RUDAS, referencia RUDAS-PE', 'posicion' => 'ALTERNATIVA_COGNITIVA',
                'referencia_candidata' => 'RUDAS-PE, publicación de 2020; no intercambiable con el original inglés.',
                'material' => 'ORIGINAL_EN_COTEJADO_ADAPTACION_ES_PENDIENTE',
                'fuentes' => ['https://www.frontiersin.org/journals/neurology/articles/10.3389/fneur.2020.00374/full',
                    'https://www.dementia.org.au/professionals/assessment-and-diagnosis-dementia/rowland-universal-dementia-assessment-scale-rudas'],
                'faltante_especifico' => 'Cotejar material español final, uso previsto y aplicabilidad a la residencia; un estudio peruano no constituye normas bolivianas.'],
            'I04' => ['nombre' => 'FAQ/Pfeffer, referencia de diez actividades', 'posicion' => 'INFORMACION_FUNCIONAL',
                'referencia_candidata' => 'FAQ original de diez actividades; adaptación española operativa no seleccionada.',
                'material' => 'REFERENCIA_HISTORICA_ES_COTEJADA_NO_ADOPTADA',
                'fuentes' => ['https://hign.org/sites/default/files/2020-06/Try_This_Dementia_13.pdf',
                    'https://files.alz.washington.edu/nacc-permission-form.pdf'],
                'faltante_especifico' => 'Cerrar edición y tratamiento de desconocidos/no aplicables; FAQ no equivale a las ocho actividades propias IADL ni acredita C03 automáticamente.'],
        ];
        foreach ($familias as $id => &$familia) {
            $familia = ['id_familia' => $id, ...$familia,
                'estado_preparacion' => 'CONDICIONES_DE_INCORPORACION_PENDIENTES',
                'edicion_operativa' => null, 'perfil_ejecutable' => null,
                'derechos_uso_previsto' => 'NO_ACREDITADOS',
                'faltantes' => ['MATERIAL_FINAL_Y_DERECHOS_DEL_USO_PREVISTO', 'APLICABILIDAD_Y_PROTOCOLO_DE_EDICION',
                    'IDENTIDAD_CATALOGO_Y_SELECTORES_EXACTOS', 'METODO_Y_PERFIL_VERIFICADOS'],
                'autoriza_inferencia' => false];
        }
        unset($familia);

        return ['contrato' => self::CONTRATO, 'fecha_cotejo' => '2026-10-29',
            'alcance' => 'PREPARACION_DE_FAMILIAS_NO_PERFILES_EJECUTABLES',
            'poblacion_objetivo' => 'PERSONAS_MAYORES_DE_60_ANIOS',
            'familias' => $familias,
            'observacion_tecnica' => ['perfiles_configurados_total' => count($perfilesConfigurados),
                'metodos_registrados_total' => count($metodosRegistrados),
                'conteos_acreditan_derechos_o_aplicabilidad' => false],
            'consulta_datos_clinicos' => false, 'ejecuta_inferencia' => false, 'habilita_perfiles' => false,
            'limites' => ['Una familia seleccionada no identifica una edición ni una PK del catálogo.',
                'La configuración efectiva se cuenta; no se atribuye a estas familias por similitud de nombres.',
                'No se crean instrumentos, preguntas, puntuaciones, permisos o tablas.',
                'Esta comprobación no sustituye los controles del extractor ni valida clínicamente métodos existentes.']];
    }
}
