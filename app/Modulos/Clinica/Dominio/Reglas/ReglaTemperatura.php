<?php

namespace App\Modulos\Clinica\Dominio\Reglas;

use App\Modulos\Clinica\Dominio\Enumeraciones\SeveridadClinica as Nivel;
use App\Modulos\Clinica\Dominio\ObjetosValor\ResultadoReglaClinica;

final class ReglaTemperatura extends ReglaBase
{
    public function __construct(private readonly array $parametros) {}

    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $valor = $mediciones['temperatura'] ?? null;
        if ($valor === null) {
            return null;
        }
        $c = $this->parametros;
        if ($valor <= $c['critico_bajo'] || $valor >= $c['critico_alto']) {
            return $this->resultado('temperatura', (string) $valor, '°C', Nivel::CRITICO, 'TEMP_CRITICA',
                'Umbral crítico de seguridad', '≤'.$c['critico_bajo'].' o ≥'.$c['critico_alto'].' °C',
                'Temperatura de '.$valor.' °C. '.($valor <= $c['critico_bajo']
                    ? 'Alcanza el límite crítico inferior (≤'.$c['critico_bajo'].' °C).'
                    : 'Alcanza el límite crítico superior (≥'.$c['critico_alto'].' °C).'), $this->recomendacionCritica());
        }
        if ($valor >= $c['advertencia_alta']) {
            return $this->resultado('temperatura', (string) $valor, '°C', Nivel::ADVERTENCIA, 'TEMP_ELEVADA',
                'Referencia operativa geriátrica', $c['referencia_baja'].'–'.$c['referencia_alta'].' °C',
                'Temperatura de '.$valor.' °C, por encima del intervalo de referencia. La elevación puede ser relevante en una persona adulta mayor.');
        }
        if ($valor >= $c['referencia_baja'] && $valor <= $c['referencia_alta']) {
            return $this->resultado('temperatura', (string) $valor, '°C', Nivel::NORMAL, 'TEMP_REFERENCIA',
                'Referencia operativa geriátrica', $c['referencia_baja'].'–'.$c['referencia_alta'].' °C',
                'Temperatura de '.$valor.' °C, dentro del intervalo de referencia de '.$c['referencia_baja'].'–'.$c['referencia_alta'].' °C.');
        }

        return $this->sinRegla('temperatura', (string) $valor, '°C');
    }
}
