<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento;

use DomainException;

/** Comprueba el recorrido variable→componente→dominio sin crear reglas, ponderaciones o equivalencias clínicas. */
final class CompiladorPerfilCognitivo
{
    public function compilar(PaqueteConocimiento $p): array
    {
        $porCodigo = array_column($p->tabla('nodos_semanticos'), null, 'codigo_semantico');
        $dominios = $componentes = $rutas = [];
        foreach (PerfilConceptualCognitivo::CANTIDADES as $codigo => $cantidad) {
            $nodo = $porCodigo[$codigo] ?? throw new DomainException('Falta un dominio del perfil cognitivo.');
            if ($nodo['tipo_nodo'] !== 'CRITERIO') {
                throw new DomainException('Dominio cognitivo de tipo incorrecto.');
            }
            $criterio = $nodo['cod_nodo_semantico'];
            $p->dominioCriterio($criterio);
            $dominios[$codigo] = $criterio;
            foreach (range(1, $cantidad) as $i) {
                $codigoComponente = substr($codigo, 4).'-S0'.$i;
                $sub = $porCodigo[$codigoComponente] ?? throw new DomainException('Falta un subcomponente del perfil cognitivo.');
                $pertenencias = array_values(array_filter($p->red->salientes($sub['cod_nodo_semantico'], 'SUBCOMPONENTE_DE'),
                    static fn ($r) => $r['cod_nodo_destino'] === $criterio));
                if ($sub['tipo_nodo'] !== 'SUBCOMPONENTE' || count($pertenencias) !== 1
                    || count($p->red->salientes($sub['cod_nodo_semantico'], 'SUBCOMPONENTE_DE')) !== 1) {
                    throw new DomainException('Pertenencia cognitiva ausente, cruzada o ambigua.');
                }
                $componentes[$codigoComponente] = ['nodo' => $sub['cod_nodo_semantico'], 'dominio' => $codigo,
                    'estado' => $sub['estado'], 'relacion' => $pertenencias[0]['cod_relacion_semantica']];
            }
        }
        $tablas = $p->declaracion()['tablas'];
        foreach ($p->tabla('reglas_expertas') as $regla) {
            if ($regla['estado'] !== 'ACTIVO' || ($regla['estado_revision'] ?? null) === 'RECHAZADA' || $regla['tipo_regla'] !== $p->tipoReglaResultado) {
                continue;
            }
            foreach ($p->tabla('consecuencias_regla_experta') as $salida) {
                if ($salida['cod_regla_experta'] !== $regla['cod_regla_experta']) {
                    continue;
                }
                $criterio = $p->fila('criterios_dominios_resultado', $salida['cod_criterio_dominio_resultado'])['cod_nodo_criterio'];
                if (! in_array($criterio, $dominios, true)) {
                    continue;
                }
                foreach ($p->tabla('condiciones_regla_experta') as $condicion) {
                    if ($condicion['cod_regla_experta'] !== $regla['cod_regla_experta']) {
                        continue;
                    }
                    $variable = $condicion['cod_variable_experta'];
                    $ruta = (new PertenenciaVariableCognitiva)->verificar($tablas, $variable, $criterio);
                    if (! $p->variableParticipa($variable, $criterio)) {
                        throw new DomainException('Una condición cognitiva carece de participación inferencial explícita.');
                    }
                    $rutas[$regla['cod_regla_experta']][] = $ruta + ['variable' => $variable,
                        'condicion' => $condicion['cod_condicion_regla'], 'criterio' => $criterio,
                        'consecuencia' => $salida['cod_consecuencia_regla']];
                }
            }
        }

        return ['version' => $p->version, 'dominios' => $dominios, 'componentes' => $componentes, 'rutas_reglas' => $rutas];
    }
}
