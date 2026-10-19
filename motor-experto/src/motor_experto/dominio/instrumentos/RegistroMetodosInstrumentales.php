<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/** Métodos explícitos investigados; no identifica el instrumento aplicado por similitud de nombres. */
final class RegistroMetodosInstrumentales
{
    public function metodos(): array
    {
        return [NormalizadorHvltBolivia2015::METODO => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es',
            'identidad' => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es'],
            'entradas' => NormalizadorHvltBolivia2015::ENTRADAS,
            'componentes' => ['MEM-S01', 'MEM-S02'], 'fuente' => NormalizadorHvltBolivia2015::FUENTE,
            'edad_minima' => 18, 'edad_maxima' => 95, 'escolaridad_minima' => 1,
            'lectoescritura_requerida' => true, 'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE']]
            + (new NormalizadorBateriaBolivia2015)->metodos()
            + (new NormalizadorReferenciasEspana)->metodos()
            + (new NormalizadorReferenciaColombia2019)->metodos()
            + [NormalizadorReferenciaTolF2019::METODO => (new NormalizadorReferenciaTolF2019)->metadatos()]
            + [NormalizadorReferenciaTokenColombia2017::METODO => (new NormalizadorReferenciaTokenColombia2017)->metadatos()]
            + [CalculadorReconocimientoHvlt::METODO => (new CalculadorReconocimientoHvlt)->metadatos(),
                CalculadorLenguajeWabCastellano2000::METODO => (new CalculadorLenguajeWabCastellano2000)->metadatos(),
                CalculadorRecuerdoConClavesFcsrt::METODO => (new CalculadorRecuerdoConClavesFcsrt)->metadatos()];
    }

    public function ejecutar(string $metodo, array $captura): array
    {
        if (! isset($this->metodos()[$metodo])) {
            throw new DomainException('No existe un método instrumental registrado con esa identidad.');
        }

        // Las referencias nuevas conservan identidad y contexto; no reemplazan los métodos previos.
        if (isset((new NormalizadorReferenciaColombia2019)->metodos()[$metodo])) {
            return (new ExplicacionMatematicaInstrumental)->agregar((new NormalizadorReferenciaColombia2019)->normalizar($metodo, $captura));
        }
        if ($metodo === NormalizadorReferenciaTolF2019::METODO) {
            return (new ExplicacionMatematicaInstrumental)->agregar((new NormalizadorReferenciaTolF2019)->normalizar($captura));
        }
        if ($metodo === NormalizadorReferenciaTokenColombia2017::METODO) {
            return (new ExplicacionMatematicaInstrumental)->agregar((new NormalizadorReferenciaTokenColombia2017)->normalizar($captura));
        }
        if (isset((new NormalizadorReferenciasEspana)->metodos()[$metodo])) {
            return (new ExplicacionMatematicaInstrumental)->agregar((new NormalizadorReferenciasEspana)->normalizar($metodo, $captura));
        }

        // El complemento descriptivo no usa normas de recuerdo para reconocimiento.
        $salida = match ($metodo) {
            NormalizadorHvltBolivia2015::METODO => (new NormalizadorHvltBolivia2015)->normalizar($captura),
            CalculadorReconocimientoHvlt::METODO => (new CalculadorReconocimientoHvlt)->calcular($captura),
            CalculadorLenguajeWabCastellano2000::METODO => (new CalculadorLenguajeWabCastellano2000)->calcular($captura),
            CalculadorRecuerdoConClavesFcsrt::METODO => (new CalculadorRecuerdoConClavesFcsrt)->calcular($captura),
            default => (new NormalizadorBateriaBolivia2015)->normalizar($metodo, $captura),
        };

        return (new ExplicacionMatematicaInstrumental)->agregar($salida);
    }
}
