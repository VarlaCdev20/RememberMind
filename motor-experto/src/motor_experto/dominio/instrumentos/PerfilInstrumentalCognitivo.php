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
    public function construir(array $red, array $captura, ?array $inferencia = null): array
    {
        if (($captura['disponible'] ?? false) !== true) {
            throw new DomainException('El perfil instrumental requiere acceso autorizado a sus fuentes.');
        }
        $mediciones = $comparables60 = [];
        $apps60 = $edadDesconocida = $appsMenores60 = 0;
        foreach ($captura['capturas'] as $aplicacion) {
            $edad = $aplicacion['edad_en_aplicacion'] ?? $aplicacion['extraccion']['captura']['edad_anios'] ?? null;
            if (! is_int($edad) || $edad < 0) {
                $edadDesconocida++;
            } elseif ($edad >= 60) {
                $apps60++;
            } else {
                $appsMenores60++;
            }
            if ($aplicacion['extraccion'] === null) {
                continue;
            }
            $e = $aplicacion['extraccion'];
            foreach ($e['normalizacion']['componentes'] as $componente => $resultado) {
                foreach ($resultado['medidas'] ?? [$resultado] as $medida) {
                    if (is_int($edad) && $edad >= 60 && $medida['estado'] === 'NORMALIZADO') {
                        $comparables60[$componente] = true;
                    }
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
                if ($inferencia !== null) {
                    $componentes[$id]['observaciones'] = $inferencia['observaciones_por_componente'][$id] ?? [];
                }
            }
            $dominios[$codigo] = ['nombre' => $dominio['nombre'], 'componentes' => $componentes, 'resultado_clinico' => null,
                'estado' => 'NO_INTERPRETADO', 'motivos' => ['SIN_CONTRATO_DE_INTEGRACION_CLINICA']];
            if ($inferencia !== null) {
                $dominios[$codigo]['integracion'] = $inferencia['perfil'][$codigo] ?? null;
            }
        }
        if ($mediciones !== []) {
            throw new DomainException('Una medición pretende contribuir a un componente ajeno a la red.');
        }

        return ['contrato' => 'PERFIL-INSTRUMENTAL-1', 'red' => $red, 'dominios' => $dominios,
            'comprobacion_adquisicion' => (new ComprobacionAdquisicionInstrumental)->comprobar($captura),
            'metricas_60_o_mas' => ['aplicaciones_examinadas' => count($captura['capturas']), 'aplicaciones_60_o_mas' => $apps60,
                'aplicaciones_menores_60' => $appsMenores60, 'aplicaciones_edad_desconocida' => $edadDesconocida,
                'componentes_con_comparacion' => count($comparables60), 'componentes_del_perfil' => count($diccionario),
                'porcentaje_cobertura_comparacion' => round(100 * count($comparables60) / count($diccionario), 2),
                'denominador' => 'COMPONENTES_DEL_PERFIL', 'tipo_metrica' => 'COBERTURA_DE_DATOS_NO_VALIDEZ_DIAGNOSTICA'],
            'capturas_originales' => $captura, 'persistido' => false, 'diagnostico' => null, 'revision_profesional' => 'PENDIENTE'];
    }
}
