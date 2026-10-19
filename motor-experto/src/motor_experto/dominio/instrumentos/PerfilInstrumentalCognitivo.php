<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Integración de mediciones instrumentales.
 * Reúne mediciones por su componente primario y preserva todas las aplicaciones.
 * No transforma puntuaciones z, respuestas nominales ni falta de medición en A/P o DCL.
 */
final class PerfilInstrumentalCognitivo
{
    public function construir(array $red, array $captura): array
    {
        if (($captura['disponible'] ?? false) !== true) {
            throw new DomainException('El perfil instrumental requiere acceso autorizado a sus fuentes.');
        }
        $mediciones = [];
        foreach ($captura['capturas'] as $aplicacion) {
            if ($aplicacion['extraccion'] === null) {
                continue;
            }
            $e = $aplicacion['extraccion'];
            foreach ($e['normalizacion']['componentes'] as $componente => $resultado) {
                foreach ($resultado['medidas'] ?? [$resultado] as $medida) {
                    $mediciones[$componente][] = ['aplicacion' => $aplicacion['cod_aplicacion'], 'fecha' => $aplicacion['fecha_hora'],
                        'autor' => $aplicacion['cod_personal'], 'perfil' => $e['perfil'], 'metodo' => $e['metodo'], 'referencia' => $e['normalizacion']['referencia'],
                        'fuentes_entradas' => $e['fuentes_entradas'], 'captura' => $e['captura'], 'resultado' => $medida];
                }
            }
        }
        $dominios = [];
        $diccionario = (new CatalogoCapturaCognitiva)->componentes();
        foreach ($red['dominios'] as $codigo => $dominio) {
            $componentes = [];
            foreach ($dominio['componentes'] as $id => $meta) {
                $datos = $mediciones[$id] ?? [];
                unset($mediciones[$id]);
                $normalizadas = count(array_filter($datos, fn ($m) => $m['resultado']['estado'] === 'NORMALIZADO'));
                $componentes[$id] = $meta + ['captura_requerida' => $diccionario[$id],
                    'estado_medicion' => $normalizadas > 0 ? 'MEDICIONES_DISPONIBLES' : 'SIN_MEDICION_NORMALIZADA',
                    'mediciones' => $datos, 'resultado_clinico' => null, 'motivos' => $datos === []
                        ? ['SIN_CAPTURA_VINCULADA_AL_COMPONENTE', 'SIN_CONTRATO_DE_INTERPRETACION_CLINICA']
                        : ['SIN_CONTRATO_DE_INTERPRETACION_CLINICA']];
            }
            $dominios[$codigo] = ['nombre' => $dominio['nombre'], 'componentes' => $componentes, 'resultado_clinico' => null,
                'estado' => 'NO_INTERPRETADO', 'motivos' => ['SIN_CONTRATO_DE_INTEGRACION_CLINICA']];
        }
        if ($mediciones !== []) {
            throw new DomainException('Una medición pretende contribuir a un componente ajeno a la red.');
        }

        return ['contrato' => 'PERFIL-INSTRUMENTAL-1', 'red' => $red, 'dominios' => $dominios,
            'capturas_originales' => $captura, 'persistido' => false, 'diagnostico' => null, 'revision_profesional' => 'PENDIENTE'];
    }
}
