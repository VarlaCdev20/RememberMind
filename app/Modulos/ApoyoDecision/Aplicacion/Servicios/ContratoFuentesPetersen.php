<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Servicios;

use App\Models\AntecedenteClinico;
use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Diagnostico;
use App\Models\DocumentoClinico;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;

/** Matriz cerrada de fuentes del diccionario aprobado; ningún código recibido selecciona una tabla arbitraria. */
final class ContratoFuentesPetersen
{
    public const FUENTES = [
        'cod_aplicacion' => [AplicacionInstrumento::class, 'aplicaciones_instrumento.ver'],
        'cod_control_cognitivo' => [ControlCognitivo::class, 'controles_cognitivos.ver'],
        'cod_control_iadl' => [ControlFuncionalIadl::class, 'controles_funcionales_iadl.ver'],
        'cod_valoracion_psicologica' => [ValoracionPsicologica::class, 'valoraciones_psicologicas.ver'],
        'cod_antecedente' => [AntecedenteClinico::class, 'antecedentes_clinicos.ver'],
        'cod_diagnostico' => [Diagnostico::class, 'diagnosticos.ver'],
        'cod_documento_clinico' => [DocumentoClinico::class, 'documentos_clinicos.ver'],
        'cod_valoracion_revisada' => [ValoracionPetersenOriginal::class, 'valoraciones_petersen.ver'],
    ];

    public static function admitidas(string $elemento, string $tipo): array
    {
        if (str_starts_with($elemento, 'COG-')) {
            return $tipo === 'MEDICION' ? ['cod_aplicacion'] : [];
        }

        return match ([$elemento, $tipo]) {
            ['C01', 'HISTORIA_PREVIA'], ['C01', 'CORROBORACION'] => ['cod_control_cognitivo', 'cod_valoracion_psicologica', 'cod_documento_clinico'],
            ['C03', 'IADL'] => ['cod_control_iadl'],
            ['C04', 'DIFERENCIAL'] => ['cod_valoracion_psicologica', 'cod_antecedente', 'cod_diagnostico', 'cod_documento_clinico'],
            ['C05', 'CALIDAD'] => ['cod_aplicacion', 'cod_valoracion_psicologica', 'cod_documento_clinico'],
            ['C05', 'DOMINIO_REVISADO'] => ['cod_valoracion_revisada'],
            default => [],
        };
    }
}
