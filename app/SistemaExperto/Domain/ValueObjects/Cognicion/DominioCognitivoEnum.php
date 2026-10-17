<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Cognicion;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Dominios cognitivos).
 *
 * Identifica los cinco ejes del perfil mediante sus códigos documentales O.R.I.O.N.
 * Relaciona cada eje con su prefijo de evaluabilidad sin asignarle un resultado cognitivo.
 *
 * TÍTULO: Identidad de los cinco dominios cognitivos.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Conservar los cinco ejes separados del perfil cognitivo y su prefijo oficial
 * de evaluabilidad, evitando mezclar resultados entre dominios.
 *
 * ENTRADAS:
 * Uno de los códigos COG-MEM, COG-ATE, COG-EJE, COG-LEN o COG-VIS.
 *
 * SALIDAS:
 * La identidad del dominio y, cuando se solicita, su prefijo EV correspondiente.
 *
 * RESTRICCIONES:
 * No incorpora orientación ni velocidad como sexto dominio, no calcula índices
 * globales y no asigna resultados A/P ni niveles de evaluabilidad.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase A y R6; Documento Maestro O.R.I.O.N.;
 * catálogo local orion-v1-conceptos.json y manifiestos D-108/D-135.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum DominioCognitivoEnum: string
{
    /** COG-MEM: aprendizaje y memoria; prefijo de evaluabilidad EV-CM. */
    case MEMORIA = 'COG-MEM';

    /** COG-ATE: atención; prefijo de evaluabilidad EV-CA. */
    case ATENCION = 'COG-ATE';

    /** COG-EJE: función ejecutiva; prefijo de evaluabilidad EV-CE. */
    case FUNCION_EJECUTIVA = 'COG-EJE';

    /** COG-LEN: lenguaje; prefijo de evaluabilidad EV-CL. */
    case LENGUAJE = 'COG-LEN';

    /** COG-VIS: función visuoespacial; prefijo de evaluabilidad EV-CV. */
    case VISUOESPACIAL = 'COG-VIS';

    /**
     * Obtiene el prefijo documental de evaluabilidad de este dominio.
     *
     * La asociación es explícita porque EV-CA y EV-CE no se deducen copiando
     * el sufijo del código COG. No se interpreta evidencia en esta operación.
     *
     * @return non-empty-string Prefijo sin nivel ni guion final.
     *
     * No recibe parámetros ni lanza excepciones: todos los casos están cubiertos.
     */
    public function prefijoEvaluabilidad(): string
    {
        return match ($this) {
            self::MEMORIA => 'EV-CM',
            self::ATENCION => 'EV-CA',
            self::FUNCION_EJECUTIVA => 'EV-CE',
            self::LENGUAJE => 'EV-CL',
            self::VISUOESPACIAL => 'EV-CV',
        };
    }

}
