<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DomainException;

/** Documento DCL 04: explicación de patrones/abstenciones exclusivamente ficticios. */
final class ConstructorInformePetersenExperimental
{
    public function construir(array $r): array
    {
        if (($r['uso'] ?? null) !== 'SIMULACION_ACADEMICA_NO_CLINICA'
            || ($r['compuerta_asistencial'] ?? null) !== 'SOLO_LABORATORIO_NO_EMITIR'
            || ($r['version'] ?? null) !== ClasificadorPetersenExperimental::VERSION
            || ! is_array($r['traza']['dominios'] ?? null)) {
            throw new DomainException('Informe DCL únicamente para simulación versionada con trazas.');
        }
        $titulos = [
            'ABSTENCION_CONTRADICCION' => 'No se puede clasificar: evidencias contradictorias',
            'ABSTENCION_EVALUACION_DIFERENCIAL' => 'No se puede clasificar: diferencial pendiente',
            'ABSTENCION_INFORMACION_INSUFICIENTE' => 'Información insuficiente para clasificar',
            'SIN_PATRON_CLASIFICABLE_POR_REQUISITO' => 'No se acreditó un requisito general',
            'REQUIERE_ADJUDICACION_CONTRATO' => 'Los criterios generales y los dominios requieren conciliación',
        ];
        $nombre = $r['nombre_patron'] ?? null;
        $titulo = $nombre === null
            ? ($titulos[$r['estado'] ?? ''] ?? null)
            : 'Patrón técnico candidato: '.$nombre;
        if ($titulo === null) {
            throw new DomainException('Estado DCL sin explicación válida.');
        }

        $dominios = [];
        foreach ($r['traza']['dominios'] as $id => $estado) {
            $dominios[$id] = match ($estado) {
                'A' => 'Alteración objetiva declarada para la simulación (no acreditación clínica)',
                'P' => 'Evaluación simulada válida sin alteración objetiva (no ausencia de registros)',
                'U' => 'No evaluado / desconocido',
                'I' => 'No interpretable',
                'X' => 'Resultados contradictorios pendientes',
                default => throw new DomainException('Dominio sin estado contractual'),
            };
        }
        $razon = $nombre === null
            ? 'No se asignó patrón. Motivos: '.implode('; ', $r['motivos']).'.'
            : 'En este ejercicio memoria es '.$r['traza']['memoria'].' y existen '.
                $r['traza']['otros_dominios_alterados'].' dominio(s) adicional(es) con A; '.
                'se declararon S los cinco requisitos generales, con soportes sintéticos.';

        return [
            'encabezado' => 'SIMULACIÓN ACADÉMICA — NO USAR CON RESIDENTES',
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'id_evaluacion' => $r['id_evaluacion'],
            'version' => $r['version'],
            'titulo' => $titulo,
            'interpretacion' => $razon,
            'criterios_generales' => $r['traza']['requisitos'],
            'dominios' => $dominios,
            'motivos_no_emision' => $nombre === null ? $r['motivos'] : [],
            'traza' => [
                'regla_candidata' => $r['patron_candidato'],
                'soportes_ficticios' => $r['traza']['soportes_sinteticos'],
                'fecha_corte' => $r['traza']['fecha_corte'],
            ],
            'limitaciones' => 'No es diagnóstico ni clasifica residentes reales; sin etiología, severidad, pronóstico, tratamiento o probabilidad de Alzheimer.',
        ];
    }
}
