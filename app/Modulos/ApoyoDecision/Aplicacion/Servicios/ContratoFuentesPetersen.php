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
use MotorExperto\inferencia\EvaluarPetersenInstitucional;
use MotorExperto\hechos\ValoracionOriginalPetersen;

/** Matriz cerrada de fuentes del diccionario aprobado; ningún código recibido selecciona una tabla arbitraria. */
final class ContratoFuentesPetersen
{
    public const TIPOS_SOPORTE = ['MEDICION' => 'Medición original', 'HISTORIA_PREVIA' => 'Funcionamiento previo', 'CORROBORACION' => 'Corroboración',
        'IADL' => 'Actividad instrumental', 'DIFERENCIAL' => 'Revisión diferencial', 'CALIDAD' => 'Condiciones y calidad', 'DOMINIO_REVISADO' => 'Valoración de dominio revisada'];

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

    /** Mapa del contrato existente, sin consultas, nuevas fuentes o valoraciones clínicas. */
    public static function mapa(): array
    {
        $mapa = [];
        $elementos = [...EvaluarPetersenInstitucional::DOMINIOS,
            ...array_diff(array_keys(ValoracionOriginalPetersen::COMPROBACIONES), ['DOMINIO'])];
        foreach ($elementos as $elemento) {
            $tipos = [];
            foreach (self::TIPOS_SOPORTE as $tipo => $nombre) {
                $fuentes = self::admitidas($elemento, $tipo);
                if ($fuentes !== []) {
                    $tipos[$tipo] = ['nombre' => $nombre, 'fuentes_admitidas' => $fuentes];
                }
            }
            $esDominio = in_array($elemento, EvaluarPetersenInstitucional::DOMINIOS, true);
            $mapa[$elemento] = ['origen' => 'VALORACION_ORIGINAL_CON_SOPORTES',
                'comprobaciones' => ValoracionOriginalPetersen::COMPROBACIONES[$esDominio ? 'DOMINIO' : $elemento],
                'soportes' => $tipos];
        }
        $mapa['C02'] = ['origen' => 'DERIVADO_DE_DOMINIOS',
            'derivado_de' => EvaluarPetersenInstitucional::DOMINIOS, 'comprobaciones' => [], 'soportes' => []];

        return $mapa;
    }

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
