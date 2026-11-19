<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

/** Resultado emitido y hechos/traza originales. No representa el DTO completo ni promete replay. */
final class ResultadoInstitucionalConservado
{
    public const VERSION = 'RESULTADO-INSTITUCIONAL-1';
    public const PERMISO = 'evaluaciones_expertas.ejecutar_institucional';
    public const DOMINIOS = ['COG-MEM' => 'memoria', 'COG-ATE' => 'atencion', 'COG-EJE' => 'ejecutiva', 'COG-LEN' => 'lenguaje', 'COG-VIS' => 'visuoespacial'];
    public const NOMBRES = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas', 'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Habilidades visuoespaciales',
        'C01' => 'Cambio cognitivo corroborado', 'C02' => 'Alteración objetiva', 'C03' => 'Autonomía instrumental', 'C04' => 'Revisión diferencial', 'C05' => 'Calidad del conjunto'];
    public const ESTADOS = ['A' => 'Alteración sustentada', 'P' => 'Rendimiento esperado sustentado', 'U' => 'Información insuficiente', 'I' => 'No interpretable', 'X' => 'Datos contradictorios', 'S' => 'Cumple', 'N' => 'No cumple'];
    public const PATRONES = ['PET-01' => 'Posible DCL amnésico de dominio único', 'PET-02' => 'Posible DCL amnésico multidominio',
        'PET-03' => 'Posible DCL no amnésico de dominio único', 'PET-04' => 'Posible DCL no amnésico multidominio'];
}
