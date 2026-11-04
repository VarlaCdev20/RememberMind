<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DomainException;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;
use MotorExperto\investigacion\g09\ControlesTareaG09Experimental;
use MotorExperto\investigacion\g09\PerfilInstrumentalG08Experimental as Perfil;

/** Genera entradas de laboratorio explícitas; ninguna salida del motor ni patrón esperado forma parte del caso. */
final class CasoSinteticoCincoDominios
{
    public static function crear(array $estados = [], string $respuesta = 'FALLO_SIMULADO', string $memoria = 'literal_A'): array
    {
        if (array_diff(array_keys($estados), ClasificadorPetersenExperimental::DOMINIOS)
            || array_diff($estados, ['A', 'P', 'U']) || ! in_array($respuesta, ['FALLO_SIMULADO', 'SIN_HALLAZGO_SIMULADO'], true)) {
            throw new DomainException('Configuración sintética inválida.');
        }
        $contexto = ['uso' => 'TEST_ONLY', 'aislado' => true, 'evaluacion' => 'SIM-INTEGRAL-01',
            'residente_ficticio' => 'SINTETICO-01', 'fecha_corte' => '2026-10-17', 'version' => Catalogo::VERSION,
            'contexto_suficiente' => true, 'confusor_invalidante' => false,
            'contexto_memoria' => ['componente_validado' => true, 'condiciones_aplicacion_conocidas' => true,
                'confusores_revisados' => true, 'invalidacion_necesaria' => false]];
        $capturas = $tareas = [];
        foreach (Catalogo::todas() as $regla => $meta) {
            $perfil = Perfil::paraRegla($regla);
            $pregunta = array_key_first($perfil['preguntas_requeridas']);
            $app = 'SIM-'.$meta['componente'];
            $capturas['instrumentos'][] = ['cod_instrumento' => $perfil['instrumento'], 'version' => $perfil['version_instrumento']];
            $capturas['aplicaciones_instrumento'][] = ['cod_aplicacion' => $app, 'cod_instrumento' => $perfil['instrumento'],
                'cod_residente' => $contexto['residente_ficticio'], 'cod_personal' => 'SIM-AUTOR-01',
                'fecha_hora' => '2026-10-16 11:00:00', 'estado' => 'COMPLETA'];
            $capturas['preguntas_instrumento'][] = ['cod_pregunta' => $pregunta, 'cod_instrumento' => $perfil['instrumento'], 'tipo_respuesta' => 'TEXTO'];
            $capturas['respuestas_instrumento'][] = ['cod_respuesta' => 'SIM-R-'.$meta['componente'], 'cod_aplicacion' => $app,
                'cod_pregunta' => $pregunta, 'valor_texto' => $respuesta];
            $tareas[] = ['regla' => $regla, 'aplicacion' => $app];
            $contexto['controles_tareas'][$app] = ['contrato' => ControlesTareaG09Experimental::VERSION,
                'atestaciones' => array_fill_keys(ControlesTareaG09Experimental::CONTROLES, true), 'confusores' => []];
        }
        $referencias = $estados === [] ? null : ['uso' => 'REFERENCIAS_SINTETICAS_INDEPENDIENTES',
            'version_puente' => AdaptadorEstadosPetersenExperimental::VERSION, 'dominios' => [], 'criterios' => []];
        foreach ($estados as $codigo => $estado) {
            $referencias['dominios'][$codigo] = $estado === 'U' ? [] : [[
                'uso' => 'REFERENCIA_INDEPENDIENTE_SINTETICA', 'id' => 'SIM-REF-'.$codigo, 'episodio' => 'SIM-EP-'.$codigo,
                'codigo' => $codigo, 'id_evaluacion' => $contexto['evaluacion'], 'residente_ficticio' => $contexto['residente_ficticio'],
                'version_conocimiento' => $codigo === 'COG-MEM' ? 'VER_TEST' : Catalogo::VERSION,
                'fecha' => '2026-10-16', 'origen' => 'REFERENCIA_EXTERNA_SINTETICA_PREDEFINIDA',
                'interpretacion' => $estado === 'A' ? 'ALTERACION_OBJETIVA_SIMULADA' : 'RENDIMIENTO_ESPERADO_SIMULADO',
                'evaluabilidad' => 'INTERPRETABLE', 'confusores' => 'REVISADOS', 'procedencia' => 'VERIFICADA',
                'norma_aplicable' => 'SIMULADA_VERIFICABLE', 'instrumento' => 'SIM-PRUEBA-'.$codigo,
                'edicion' => 'SIM-EDICION-01', 'subprueba' => 'SIM-SUBPRUEBA-'.$codigo, 'norma_id' => 'SIM-BAREMO-SINTETICO',
                'idioma' => 'SIM-ES', 'poblacion' => 'SIM-POBLACION-SINTETICA', 'revisor_independiente' => 'SIM-REVISOR-01',
            ]];
        }
        if ($referencias !== null) {
            foreach (['DCL-C01' => 'CAMBIO_LONGITUDINAL_SIMULADO', 'DCL-C02' => 'OBJETIVIDAD_SIMULADA',
                'DCL-C03' => 'IADL_FUNCIONAL_SIMULADA', 'DCL-C04' => 'DIFERENCIAL_SIMULADO', 'DCL-C05' => 'EVALUABILIDAD_SIMULADA'] as $codigo => $tipo) {
                $referencias['criterios'][$codigo] = ['uso' => 'REFERENCIA_INDEPENDIENTE_SINTETICA', 'id' => 'SIM-REF-'.$codigo,
                    'episodio' => 'SIM-EP-'.$codigo, 'codigo' => $codigo, 'id_evaluacion' => $contexto['evaluacion'],
                    'residente_ficticio' => $contexto['residente_ficticio'], 'version_conocimiento' => ClasificadorPetersenExperimental::VERSION,
                    'fecha' => '2026-10-16', 'origen' => 'REFERENCIA_EXTERNA_SINTETICA_PREDEFINIDA',
                    'tipo_revision' => $tipo, 'procedencia' => 'VERIFICADA', 'revisor_independiente' => 'SIM-REVISOR-02',
                    'estado' => 'S', 'iadl_observadas' => $codigo === 'DCL-C03'];
            }
        }

        return compact('contexto', 'capturas', 'tareas', 'memoria', 'referencias');
    }
}
