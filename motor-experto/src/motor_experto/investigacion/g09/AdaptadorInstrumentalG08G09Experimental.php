<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;
use MotorExperto\adaptadores\entrada\InspectorComponenteInstrumental;

/**
 * Puente de datos con estructura de aplicaciones_instrumento y respuestas_instrumento
 * hacia G09-EXP. NO consulta BDD ni interpreta pruebas reales.
 */
final class AdaptadorInstrumentalG08G09Experimental
{
    public function preparar(string $regla, array $contexto, array $captura): array
    {
        if (($contexto['uso'] ?? null) !== 'TEST_ONLY' || ($contexto['aislado'] ?? false) !== true
            || ($captura['fixture_sintetico'] ?? false) !== true) {
            throw new DomainException('G08/G09: acceso restringido a fixtures sintéticos aislados.');
        }
        if (($contexto['version'] ?? null) !== CatalogoReglasG09Experimental::VERSION) {
            throw new DomainException('Versión de investigación incompatible.');
        }
        $meta = CatalogoReglasG09Experimental::obtener($regla);
        if (($contexto['criterio'] ?? null) !== $meta['criterio']) {
            throw new DomainException('Criterio no compatible con regla.');
        }
        $perfil = PerfilInstrumentalG08Experimental::paraRegla($regla);
        foreach (['aplicacion','instrumento','preguntas','opciones','respuestas'] as $campo) {
            if (!isset($captura[$campo]) || !is_array($captura[$campo])) {
                throw new DomainException('Captura instrumental incompleta: '.$campo);
            }
        }
        $aplicacion = $captura['aplicacion'];
        $instrumento = $captura['instrumento'];
        if (($instrumento['cod_instrumento'] ?? null) !== $perfil['instrumento']
            || ($instrumento['version'] ?? null) !== $perfil['version_instrumento']
            || ($aplicacion['cod_instrumento'] ?? null) !== $perfil['instrumento']
            || ($aplicacion['estado'] ?? null) !== 'COMPLETA'
            || ($aplicacion['cod_residente'] ?? null) !== ($contexto['residente_ficticio'] ?? null)
            || !isset($aplicacion['cod_aplicacion'])
            || ($aplicacion['cod_aplicacion'] ?? '') === '') {
            return $this->noAdmitida($contexto, 'PERFIL_SINTETICO_O_APLICACION_INCOMPATIBLE');
        }
        $fecha = $aplicacion['fecha_aplicacion'] ?? null;
        $corte = $contexto['fecha_corte'] ?? null;
        if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $fecha)
            || !is_string($corte) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $corte)
            || $fecha > $corte) {
            return $this->noAdmitida($contexto, 'FECHA_FUERA_DEL_CORTE');
        }
        $inspeccion = (new InspectorComponenteInstrumental)->inspeccionar(
            $aplicacion, $instrumento,
            $captura['preguntas'], $captura['opciones'], $captura['respuestas'], $perfil
        );
        if (!$inspeccion['verificado']) {
            return $this->noAdmitida($contexto, implode(',', $inspeccion['motivos']));
        }
        if (($inspeccion['valor'] ?? null) !== 'DIFICULTAD_SINTETICA') {
            return $this->noAdmitida($contexto, 'SIN_SOPORTE_ESPECIFICO');
        }
        $respuestas = $inspeccion['procedencia']['respuestas'];
        sort($respuestas, SORT_STRING);

        $salida = $contexto;
        $salida['evidencias'] = [[
            'id' => 'SIM-EV-'.$aplicacion['cod_aplicacion'].'-'.implode('-', $respuestas),
            'regla' => $regla,
            'residente_ficticio' => $contexto['residente_ficticio'],
            'evaluacion' => $contexto['evaluacion'],
            'version' => CatalogoReglasG09Experimental::VERSION,
            'fecha' => $fecha,
            'fuente' => 'aplicaciones_instrumento:'.$aplicacion['cod_aplicacion'],
            'episodio' => 'SIM-APLICACION-'.$aplicacion['cod_aplicacion'],
            'procedencia_verificada' => true,
            'instrumento_test_only' => true,
            'tarea_atribuible' => true,
            'confusor_invalidante' => ($captura['confusor_invalidante'] ?? false) === true,
            'dificultad_especifica_test_only' => true,
            'atribucion_test_only' => true,
            'contexto_regla_suficiente' => ($contexto['contexto_suficiente'] ?? false) === true,
        ]];
        $salida['motivos_g08'] = [];
        return $salida;
    }

    private function noAdmitida(array $contexto, string $motivo): array
    {
        $contexto['evidencias'] = [];
        $contexto['senal_contextual'] = false;
        $contexto['motivos_g08'] = [$motivo];

        return $contexto;
    }
}
