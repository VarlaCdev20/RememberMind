<?php

declare(strict_types=1);

namespace OrionNominal;

use MotorExperto\dominio\petersen\FuentePetersenInstitucional as F;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;
use OrionFase02\CasoCanonico;
use OrionFase02\VariantesCanonicas;

/** Extensión sintética de persistencia; utiliza reglas existentes, nunca registros institucionales. */
final class CasosCompletos
{
    public static function casos(): array
    {
        $casos = VariantesCanonicas::casos();
        $casos['CINCO_DOMINIOS_P'] = (new CasoCanonico)->crear([], identidad: 'SIN-ALTERACIONES');
        $casos['DIFERENCIAL_PENDIENTE'] = (new CasoCanonico)->crear(variar: static function (&$a, &$v): void {
            $j = $v[7];
            $v[7] = new V($j->elemento, 'N', 'Impedimento diferencial sintético sin resolver.', $j->origen, $j->soportes,
                ['revision_explicita' => true, 'factores_pertinentes_revisados' => true, 'impedimento_no_resuelto' => true]);
        }, identidad: 'DIFERENCIAL-TEST');
        $casos['CONTRADICCION_PETERSEN'] = (new CasoCanonico)->crear(variar: static function (&$a, &$v, &$p, &$c, &$procedencia, &$miembros, &$adquisicion): void {
            $j = $v[0];
            $f = $j->origen;
            $origen = new F($f->tabla, 'JUICIO-CONTRADICTOR', $f->residente, $f->autor, $f->fecha, $f->episodio, $f->tipo, $f->vigente, $f->datos);
            $v[] = new V($j->elemento, $j->estado === 'A' ? 'P' : 'A', 'Juicio opuesto para probar conservación de contradicciones.',
                $origen, $j->soportes, $j->comprobaciones);
            $original = $procedencia[0];
            $original['cod_valoracion_petersen'] = $origen->registro;
            $procedencia[] = $original;
            $miembros[] = ['valoracion' => $origen->registro, 'soportes' => ['SOPORTE-CONTRADICTOR']];
            $adquisicion['valoraciones_al_corte']++;
        }, identidad: 'CONTRADICCION-TEST');

        return $casos;
    }
}
