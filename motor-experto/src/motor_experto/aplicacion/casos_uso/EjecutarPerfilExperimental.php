<?php

declare(strict_types=1);

namespace MotorExperto\aplicacion\casos_uso;

use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\explicacion\ConstructorResultadoExperimental;
use MotorExperto\investigacion\dcl\ComparadorLongitudinalExperimental;
use MotorExperto\investigacion\dcl\IncorporadorContextoExperimental;
use MotorExperto\investigacion\dcl\IntegradorReferenciasDCLExperimental;
use MotorExperto\investigacion\dcl\OrquestadorCincoDominiosExperimental;
use MotorExperto\investigacion\dcl\PaqueteCincoDominiosExperimental;
use MotorExperto\investigacion\g09\BaseInstrumentalExperimental;
use MotorExperto\investigacion\g09\EjecutorPerfilG09Experimental;
use MotorExperto\investigacion\g09\LectorSQLiteG08Experimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;

/** Entrada integral pura: fuentes ficticias tipadas, cinco dominios, referencias independientes y explicación. */
final class EjecutarPerfilExperimental
{
    public const CONTRATO = 'PERFIL-EXPERIMENTAL-1';

    public function ejecutar(array $contexto, array $capturas, array $tareas, string $memoria, ?array $referencias = null, array $comparaciones = [], array $fuentesComplementarias = [], ?array $conocimientoG09 = null): array
    {
        if (! array_key_exists('contexto_memoria', $contexto) || ! is_array($contexto['contexto_memoria'])) {
            throw new \DomainException('El contrato integral exige contexto de Memoria explícito, independiente de G09.');
        }
        $baseContextual = (new IncorporadorContextoExperimental)->incorporar($contexto, $fuentesComplementarias);
        $paquete = null;
        if ($conocimientoG09 !== null) {
            if (($conocimientoG09['solo_pruebas_tecnicas'] ?? null) !== true
                || ! is_string($conocimientoG09['version'] ?? null)
                || ! is_array($conocimientoG09['tablas'] ?? null)
                || ! is_array($conocimientoG09['contratos_relaciones'] ?? null)
                || ! is_array($conocimientoG09['contratos_extraccion'] ?? null)
                || ! is_string($conocimientoG09['tipo_regla_resultado'] ?? null)) {
                throw new \DomainException('El paquete declarativo requiere un contrato completo y uso TEST_ONLY explícito.');
            }
            $paquete = new PaqueteConocimiento($conocimientoG09['version'], $conocimientoG09['tablas'],
                $conocimientoG09['contratos_relaciones'], true, $conocimientoG09['tipo_regla_resultado'], $conocimientoG09['contratos_extraccion']);
        }
        $integrado = (new PaqueteCincoDominiosExperimental)->integrar($paquete ?? PaqueteSemanticoG09Experimental::crear());
        $paquete = $integrado['paquete'];
        if (isset($integrado['fuentes']['PERFIL']) && $referencias !== null) {
            if (! is_array($referencias['dominios'] ?? null)) {
                throw new \DomainException('Las referencias de dominio requieren una colección explícita.');
            }
            foreach ($referencias['dominios'] ?? [] as $lista) {
                if (! is_array($lista) || ! array_is_list($lista)) {
                    throw new \DomainException('Las referencias de dominio deben ser listas explícitas.');
                }
                foreach ($lista as $referencia) {
                    if (! is_array($referencia)) {
                        throw new \DomainException('Una referencia de dominio debe ser un objeto declarativo.');
                    }
                    if (($referencia['version_conocimiento'] ?? null) !== $paquete->version) {
                        throw new \DomainException('El perfil completo recibido no conserva el manifiesto de sus familias anteriores. Las referencias independientes deben identificar esta revisión explícita.');
                    }
                }
            }
        }
        $db = (new BaseInstrumentalExperimental)->abrir($capturas);
        $orquestador = new OrquestadorCincoDominiosExperimental(new EjecutorPerfilG09Experimental(new LectorSQLiteG08Experimental($db),
            exigirControlesTarea: true, conocimiento: $paquete), $paquete, $integrado['fuentes']);
        // El contrato integral conserva desconocido; el fallback del ejecutor previo es solo compatibilidad de sus fixtures.
        $contextoEvaluacion = $contexto;
        if (! array_key_exists('confusor_invalidante', $contextoEvaluacion)) {
            $contextoEvaluacion['confusor_invalidante'] = null;
        }
        $resultado = $referencias === null ? $orquestador->ejecutar($contextoEvaluacion, $tareas, $memoria)
            : (new IntegradorReferenciasDCLExperimental($orquestador))->evaluar($contextoEvaluacion, $tareas, $memoria, $referencias);
        $longitudinales = [];
        foreach ($comparaciones as $par) {
            if (! is_array($par) || ! array_key_exists('anterior', $par) || ! array_key_exists('actual', $par)
                || ($par['anterior'] !== null && ! is_array($par['anterior'])) || ($par['actual'] !== null && ! is_array($par['actual']))) {
                throw new \DomainException('Comparación longitudinal mal formada.');
            }
            foreach ([$par['anterior'], $par['actual']] as $fuente) {
                if ($fuente !== null && ($fuente['caso'] ?? null) !== $contexto['residente_ficticio']) {
                    throw new \DomainException('Comparación de otro caso.');
                }
            }
            $longitudinales[] = (new ComparadorLongitudinalExperimental)->comparar($par['anterior'], $par['actual'], new \DateTimeImmutable($contexto['fecha_corte'].'T23:59:59-04:00'));
        }

        return ['contrato_salida' => self::CONTRATO,
            'advertencia' => 'SIMULACIÓN TÉCNICA / NO VÁLIDO PARA DECISIONES ASISTENCIALES',
            'estado_tecnico' => 'FINALIZADA', 'persistido' => false,
            'instantanea_entrada' => compact('contexto', 'capturas', 'tareas', 'memoria', 'referencias', 'comparaciones', 'fuentesComplementarias', 'conocimientoG09'),
            'base_contextual' => $baseContextual,
            'comparaciones_longitudinales' => $longitudinales,
            'perfil_experimental' => $resultado,
            'conocimiento_integrado' => ['version' => $paquete->version, 'fuentes' => $integrado['fuentes'],
                'red' => $integrado['red'], 'origenes' => $integrado['origenes']],
            'limitaciones' => ['Perfiles instrumentales artificiales; sin interpretación clínica.',
                'Memoria ejecuta D-131 integrado; no se inventan reglas específicas para sus cuatro subcomponentes.',
                'La instantánea se devuelve en memoria; no se afirma conservación institucional del perfil G09.'],
        ] + (new ConstructorResultadoExperimental)->construir($resultado, $contexto, $referencias);
    }
}
