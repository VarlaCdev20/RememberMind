<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;

/**
 * G08 — Perfil exclusivamente sintético; no contiene normas ni puntajes clínicos.
 * El catálogo G09 delimita los 15 subcomponentes documentados del proyecto.
 */
final class PerfilInstrumentalG08Experimental
{
    public static function paraRegla(string $regla): array
    {
        $meta = CatalogoReglasG09Experimental::obtener($regla);
        $componente = $meta['componente'];
        $pregunta = 'Q-'.$componente;

        return [
            'codigo' => 'SIM-'.$componente,
            'instrumento' => 'SIM-'.$componente,
            'version_instrumento' => 'G08-TEST-20261017',
            'idioma' => 'SIM-ES',
            'poblacion' => 'SIM-POBLACION-DE-INGENIERIA',
            'norma' => 'SIM-PATRON-EXACTO-SIN-BAREMO',
            'licencia_contenido' => 'REACTIVO_ARTIFICIAL_PROPIO_SIN_CONTENIDO_CLINICO',
            'componente' => $componente,
            'metodo' => 'PATRON_EXACTO_SOLO_FIXTURE',
            'origen' => 'SIMULACION_INFORMATICA_NO_INSTRUMENTO_CLINICO',
            'estado_g08' => 'NO_VALIDADO_CLINICAMENTE',
            'solo_pruebas_tecnicas' => true,
            'preguntas_requeridas' => [
                $pregunta => [
                    'campo' => 'valor_texto',
                    'tipo_respuesta' => 'TEXTO',
                    'tipo' => 'string',
                    'valores_permitidos' => ['FALLO_SIMULADO', 'SIN_HALLAZGO_SIMULADO'],
                ],
            ],
            'patrones' => [
                [
                    'respuestas' => [$pregunta => 'FALLO_SIMULADO'],
                    'valor_fuente' => 'DIFICULTAD_SINTETICA',
                ],
                [
                    'respuestas' => [$pregunta => 'SIN_HALLAZGO_SIMULADO'],
                    'valor_fuente' => 'SIN_HALLAZGO_SINTETICO',
                ],
            ],
        ];
    }

    /** No hay perfiles normativos clínicos autorizados en el expediente G08 actual. */
    public static function perfilClinico(string $regla): never
    {
        CatalogoReglasG09Experimental::obtener($regla);
        throw new DomainException('G08: no existe perfil clinico validado para este componente.');
    }
}
