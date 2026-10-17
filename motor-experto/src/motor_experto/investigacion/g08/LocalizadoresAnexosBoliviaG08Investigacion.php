<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g08;

use DomainException;

/**
 * Índice de tablas concretas publicadas para BOLIVIA, sin importar valores.
 * No contiene percentiles, ecuaciones, puntuaciones ni puntos de corte.
 * Índice bibliográfico ≠ autorización de aplicación clínica.
 */
final class LocalizadoresAnexosBoliviaG08Investigacion
{
    public const VERSION = 'G08-ANEXOS-BO-20261017-01';

    // Un componente puede tener diversas tareas: no son intercambiables.
    private const ANEXOS = [
        'ATE-S03' => [
            'BTA_AUDIO' => ['instrumento'=>'BTA','metrica'=>'BTA_TOTAL','pmid'=>'26639928','doi'=>'10.3233/NRE-151283','tabla'=>'A2','ajuste_publicado'=>'EDAD_Y_ESCOLARIDAD'],
        ],
        'EJE-S01' => [
            'STROOP_INTERFERENCIA' => ['instrumento'=>'STROOP','metrica'=>'STROOP_INTERFERENCIA','pmid'=>'26639926','doi'=>'10.3233/NRE-151281','tabla'=>'A35','ajuste_publicado'=>'SIN_ESTRATIFICACION_EN_TABLA_BOLIVIA'],
        ],
        'EJE-S02' => [
            'TMT_B' => ['instrumento'=>'TMT','metrica'=>'TIEMPO_TMT_B','pmid'=>'26639932','doi'=>'10.3233/NRE-151284','tabla'=>'A14','ajuste_publicado'=>'EDAD_Y_ESCOLARIDAD'],
            'MWCST_CATEGORIAS' => ['instrumento'=>'MWCST','metrica'=>'NUMERO_CATEGORIAS','pmid'=>'26639931','doi'=>'10.3233/NRE-151280','tabla'=>'A2','ajuste_publicado'=>'EDAD'],
            'MWCST_PERSEVERACIONES' => ['instrumento'=>'MWCST','metrica'=>'ERRORES_PERSEVERATIVOS','pmid'=>'26639931','doi'=>'10.3233/NRE-151280','tabla'=>'A13','ajuste_publicado'=>'EDAD'],
            'MWCST_ERRORES_TOTALES' => ['instrumento'=>'MWCST','metrica'=>'ERRORES_TOTALES','pmid'=>'26639931','doi'=>'10.3233/NRE-151280','tabla'=>'A24','ajuste_publicado'=>'EDAD'],
        ],
        'LEN-S01' => [
            'BNT_FORMA_ESTANDAR' => ['instrumento'=>'BNT_ESTANDAR','metrica'=>'BNT_ESTANDAR_TOTAL','pmid'=>'26639925','doi'=>'10.3233/NRE-151278','tabla'=>'A2','ajuste_publicado'=>'ESCOLARIDAD'],
        ],
        'LEN-S02' => [
            'FLUIDEZ_LETRA_F' => ['instrumento'=>'FLUIDEZ_VERBAL','metrica'=>'LETRA_F','pmid'=>'26639930','doi'=>'10.3233/NRE-151279','tabla'=>'A2','ajuste_publicado'=>'EDAD_Y_ESCOLARIDAD'],
            'FLUIDEZ_LETRA_A' => ['instrumento'=>'FLUIDEZ_VERBAL','metrica'=>'LETRA_A','pmid'=>'26639930','doi'=>'10.3233/NRE-151279','tabla'=>'A13','ajuste_publicado'=>'EDAD_Y_ESCOLARIDAD'],
            'FLUIDEZ_LETRA_S' => ['instrumento'=>'FLUIDEZ_VERBAL','metrica'=>'LETRA_S','pmid'=>'26639930','doi'=>'10.3233/NRE-151279','tabla'=>'A24','ajuste_publicado'=>'EDAD_Y_ESCOLARIDAD'],
            'FLUIDEZ_ANIMALES' => ['instrumento'=>'FLUIDEZ_VERBAL','metrica'=>'ANIMALES','pmid'=>'26639930','doi'=>'10.3233/NRE-151279','tabla'=>'A35','ajuste_publicado'=>'EDAD_Y_ESCOLARIDAD'],
        ],
        'VIS-S02' => [
            'ROCF_COPIA' => ['instrumento'=>'REY_OSTERRIETH','metrica'=>'ROCF_COPIA','pmid'=>'26639929','doi'=>'10.3233/NRE-151285','tabla'=>'A2','ajuste_publicado'=>'EDAD_Y_ESCOLARIDAD'],
        ],
    ];

    public static function todos(): array
    {
        $r=[];
        foreach (self::ANEXOS as $componente => $tareas) {
            foreach ($tareas as $tarea => $fila) {
                $r[$componente][$tarea] = [
                    'componente'=>$componente,'tarea'=>$tarea,'pais'=>'BOLIVIA',
                    'publicacion'=>'https://doi.org/'.$fila['doi'],
                    'alcance'=>'SOLO_LOCALIZADOR_BIBLIOGRAFICO',
                    'aprobacion_g08'=>false,
                    ...$fila,
                ];
            }
        }
        return $r;
    }

    public static function buscar(string $componente,string $tarea): array
    {
        return self::todos()[$componente][$tarea]
            ?? throw new DomainException('G08: tabla boliviana no localizada para ese componente y esa tarea.');
    }

    /**
     * Comprueba IDENTIDAD de la tabla citada, no aplicabilidad clínica.
     * Aunque coincida, no autoriza norma, percentil ni interpretación A/P.
     */
    public function contrastar(string $componente,string $tarea,array $cita): array
    {
        $n=self::buscar($componente,$tarea);
        $faltantes=[];
        foreach (['instrumento','metrica','pmid','doi','tabla','pais'] as $campo) {
            if (($cita[$campo] ?? null) !== $n[$campo]) {
                $faltantes[]='DISCREPANCIA_'.strtoupper($campo);
            }
        }
        if (($cita['uso'] ?? null)!=='REVISION_DOCUMENTAL_TEST_ONLY') {
            $faltantes[]='FUERA_DE_REVISION_DOCUMENTAL';
        }
        return [
            'estado'=>'COTEJO_BIBLIOGRAFICO_NO_CLINICO',
            'componente'=>$componente,'tarea'=>$tarea,
            'fuente'=>$n['publicacion'],'tabla'=>$n['tabla'],
            'ajuste_tabla_boliviana'=>$n['ajuste_publicado'],
            'cita_identificada'=> $faltantes === [],
            'faltantes'=>$faltantes,
            'perfil_g08_autorizado'=>false,
            'puede_interpretar_puntuacion'=>false,
            'puede_emitir_A_P'=>false,
            'limitacion'=>'La tabla identificada no equivale a manual, baremo aprobado, algoritmo ni permiso de uso.',
        ];
    }
}
