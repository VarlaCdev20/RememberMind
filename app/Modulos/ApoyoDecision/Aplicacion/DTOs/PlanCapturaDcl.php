<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use DomainException;
use MotorExperto\instrumentos\GuiaAdquisicionDcl;
use MotorExperto\inferencia\EvaluarPetersenInstitucional;

/** Proyecta una salida ya autorizada; no consulta fuentes, decide estados ni concede escritura. */
final class PlanCapturaDcl
{
    public function desdePetersen(array $petersen): array
    {
        if (($petersen['contrato'] ?? null) !== EvaluarPetersenInstitucional::CONTRATO) {
            throw new DomainException('El plan de captura requiere una salida institucional de Petersen.');
        }
        $guia = new GuiaAdquisicionDcl;
        $elementos = ($petersen['dominios'] ?? []) + ($petersen['requisitos'] ?? []);
        $pendientes = [];
        foreach ($guia->secciones() as $seccion) {
            $porRevisar = [];
            foreach ($seccion['elementos'] as $codigo) {
                $estado = $elementos[$codigo]['estado'] ?? null;
                $admitidos = str_starts_with($codigo, 'COG-') ? ['A', 'P', 'U', 'I', 'X'] : ['S', 'N', 'U', 'I', 'X'];
                if (! in_array($estado, $admitidos, true)) {
                    throw new DomainException('La salida institucional está incompleta o contiene un estado incompatible.');
                }
                if (in_array($estado, ['U', 'I', 'X'], true)) {
                    $porRevisar[] = ['elemento' => $codigo, 'estado' => $estado,
                        'accion' => match ($estado) {
                            'X' => 'Revisar registros contradictorios y conservar su procedencia.',
                            'I' => 'Revisar las condiciones que impiden interpretar el dato.',
                            default => 'Consultar qué valoración o soporte original falta.',
                        }];
                }
            }
            if ($porRevisar !== []) {
                $pendientes[] = $seccion + ['por_revisar' => $porRevisar];
            }
        }

        return ['contrato' => 'PLAN-CAPTURA-DCL-1', 'version_guia' => GuiaAdquisicionDcl::VERSION,
            'pendientes' => $pendientes, 'fuentes' => $guia->fuentes(),
            'estado' => $pendientes === [] ? 'SIN_FALTANTES_DE_ESTADO_EN_ESTA_SALIDA' : 'REVISAR_FUENTES_ORIGINALES',
            'aviso' => 'Esta ayuda organiza faltantes del cálculo actual. No es un diagnóstico, una orden de estudios ni una confirmación de guardado.',
            'concede_permiso_registro' => false];
    }
}
