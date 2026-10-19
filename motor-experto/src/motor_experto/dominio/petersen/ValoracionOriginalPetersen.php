<?php

declare(strict_types=1);

namespace MotorExperto\dominio\petersen;

use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Interpretación original, anterior a la clasificación.
 * Conserva el juicio y sus soportes; no acepta un patrón DCL esperado como entrada.
 * C02 se obtiene del perfil y no puede declararse como valoración independiente.
 * Las comprobaciones son condiciones documentadas de la valoración original, no firmas posteriores.
 */
final readonly class ValoracionOriginalPetersen
{
    public const COMPROBACIONES = [
        'DOMINIO' => ['alcance_dominio', 'contexto_interpretado'],
        'C01' => ['cambio_respecto_previo', 'corroboracion_independiente'],
        'C03' => ['revision_integrada', 'oportunidad_adecuada', 'atribucion_revisada'],
        'C04' => ['revision_explicita', 'factores_pertinentes_revisados', 'impedimento_no_resuelto'],
        'C05' => ['suficiencia_conjunto', 'condiciones_revisadas', 'exclusiones_revisadas'],
    ];

    public function __construct(public string $elemento, public string $estado, public string $justificacion,
        public FuentePetersenInstitucional $origen, public array $soportes, public array $comprobaciones, public array $dominiosRevisados = [])
    {
        $dominio = in_array($elemento, EvaluarPetersenInstitucional::DOMINIOS, true);
        if ((! $dominio && ! in_array($elemento, ['C01', 'C03', 'C04', 'C05'], true))
            || ! in_array($estado, $dominio ? ['A', 'P', 'I'] : ['S', 'N'], true)
            || trim($justificacion) === '' || $origen->tipo !== 'VALORACION_ORIGINAL' || ! array_is_list($soportes)) {
            throw new DomainException('Valoración original Petersen fuera de contrato.');
        }
        $ids = [];
        foreach ($soportes as $soporte) {
            if (! $soporte instanceof FuentePetersenInstitucional || isset($ids[$soporte->identidad()]) || $soporte->identidad() === $origen->identidad()) {
                throw new DomainException('Una valoración necesita soportes originales distintos y sin duplicados.');
            }
            $ids[$soporte->identidad()] = true;
        }
        $permitidas = self::COMPROBACIONES[$dominio ? 'DOMINIO' : $elemento];
        if (array_diff(array_keys($comprobaciones), $permitidas)
            || array_filter($comprobaciones, static fn ($v) => ! is_bool($v) && $v !== null)) {
            throw new DomainException('Las condiciones documentadas requieren booleanos explícitos o NULL.');
        }
        if (($elemento !== 'C05' && $dominiosRevisados !== []) || array_diff(array_keys($dominiosRevisados), EvaluarPetersenInstitucional::DOMINIOS)
            || array_filter($dominiosRevisados, static fn ($id) => ! is_string($id) || $id === '')) {
            throw new DomainException('Solo C05 identifica los juicios originales de dominio revisados.');
        }
    }
}
